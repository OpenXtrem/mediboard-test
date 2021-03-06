<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage Labo
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

class CPrescriptionLaboExamen extends CMbObject {
  // DB Table key
  public $prescription_labo_examen_id;

  // DB references
  public $prescription_labo_id;
  public $examen_labo_id;
  public $pack_examens_labo_id;
  public $resultat;
  public $date;
  public $commentaire;

  // Forward references
  public $_ref_prescription_labo;
  public $_ref_examen_labo;
  public $_ref_pack;

  // Distant fields
  public $_hors_limite;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'prescription_labo_examen';
    $spec->key   = 'prescription_labo_examen_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specsParent = parent::getProps();
    $specs = array (
      "prescription_labo_id" => "ref class|CPrescriptionLabo notNull",
      "examen_labo_id"       => "ref class|CExamenLabo notNull",
      "pack_examens_labo_id" => "ref class|CPackExamensLabo",
      "resultat"             => "str",
      "date"                 => "date",
      "commentaire"          => "text helped"
    );
    return array_merge($specsParent, $specs);
  }

  /**
   * @see parent::check()
   */
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }

    // Check unique item
    $other = new CPrescriptionLaboExamen;
    $clone = null;
    if ($this->_id) {
      $clone = new CPrescriptionLaboExamen;
      $clone->load($this->_id);
    }
    else {
      $clone = $this;
    }
    $other->prescription_labo_id = $clone->prescription_labo_id;
    $other->examen_labo_id = $clone->examen_labo_id;
    $other->loadMatchingObject();
    if ($other->_id && $other->_id != $this->_id) {
      return "$this->_class-unique-conflict";
    }

    // Check prescription status
    $clone->loadRefPrescription();
    $clone->_ref_prescription_labo->loadRefsBack();
    if ($clone->_ref_prescription_labo->_status >= CPrescriptionLabo::VALIDEE) {
      return "Prescription d�j� valid�e";
    }
    // Get the analysis to check resultat
    if (!$this->examen_labo_id) {
      if (!$clone) {
        $clone = new CPrescriptionLaboExamen;
        $clone->load($this->_id);
      }
      $this->examen_labo_id = $clone->examen_labo_id;
    }

    // Check resultat according to type
    $this->loadRefExamen();
    $resultTest = CMbFieldSpecFact::getSpec($this, "resultat", $this->_ref_examen_labo->type);
    return $resultTest->checkPropertyValue($this);
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefExamen();
    $examen =& $this->_ref_examen_labo;
    $borne_inf = $examen->min && $examen->min > $this->resultat;
    $borne_sup = $examen->max && $examen->max < $this->resultat;
    $this->_hors_limite = $this->resultat && $examen->type == "num" && ( $borne_inf || $borne_sup );
    $this->_shortview = $examen->_shortview;
    $this->_view      = $examen->_view;
  }

  function loadRefPrescription() {
    if (!$this->_ref_prescription_labo) {
      $this->_ref_prescription_labo = new CPrescriptionLabo;
      $this->_ref_prescription_labo->load($this->prescription_labo_id);
    }
  }

  function loadRefExamen() {
    if (!$this->_ref_examen_labo) {
      $this->_ref_examen_labo = new CExamenLabo;
      $this->_ref_examen_labo->load($this->examen_labo_id);
    }
  }

  function loadRefPack() {
    if (!$this->_ref_pack) {
      $this->_ref_pack = new CPackExamensLabo();
      $this->_ref_pack->load($this->pack_examens_labo_id);
    }
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefPrescription();
    $this->loadRefExamen();
    $this->loadRefPack();
  }

  function loadSiblings($limit = 10) {
    return $this->loadResults($this->_ref_prescription_labo->patient_id, $this->examen_labo_id, $limit);
  }

  /**
   * load results items with given patient and exam
   */
  function loadResults($patient_id, $examen_labo_id, $limit = 10) {
    $examen = new CExamenLabo;
    $examen->load($examen_labo_id);

    $order = "date DESC";
    $prescription = new CPrescriptionLabo;
    $prescription->patient_id = $patient_id;
    $prescriptions = $prescription->loadMatchingList($order);

    // Load items for each prescription to preserve prescription date ordering
    $items = array();
    $item = new CPrescriptionLaboExamen;
    foreach ($prescriptions as $_prescription) {
      $item->prescription_labo_id = $_prescription->_id;
      $item->examen_labo_id       = $examen_labo_id;
      foreach ($item->loadMatchingList($order) as $_item) {
        $items[$_item->_id] = $_item;
      }
    }

    foreach ($items as &$item) {
      $item->_ref_prescription_labo =& $prescriptions[$item->prescription_labo_id];
      $item->_ref_examen_labo =& $examen;
    }

    return $items;
  }
}
