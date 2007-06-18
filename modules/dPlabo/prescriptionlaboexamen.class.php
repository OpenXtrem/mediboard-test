<?php

/**
* @package Mediboard
* @subpackage dPlabo
* @version $Revision: $
* @author Romain Ollivier
*/

class CPrescriptionLaboExamen extends CMbObject {
  // DB Table key
  var $prescription_labo_examen_id = null;
  
  // DB references
  var $prescription_labo_id = null;
  var $examen_labo_id       = null;
  var $resultat             = null;
  var $date                 = null;
  var $commentaire          = null;
  
  // Forward references
  var $_ref_prescription_labo = null;
  var $_ref_examen_labo       = null;

  // Distant fields
  var $_hors_limite = null;
    
  function CPrescriptionLaboExamen() {
    $this->CMbObject("prescription_labo_examen", "prescription_labo_examen_id");
    
    $this->loadRefModule(basename(dirname(__FILE__)));
  }
  
  function getSpecs() {
    return array (
      "prescription_labo_id" => "ref class|CPrescriptionLabo notNull",
      "examen_labo_id"       => "ref class|CExamenLabo notNull",
      "resultat"             => "str",
      "date"                 => "date",
      "commentaire"          => "text"
    );
  }
  
  function getHelpedFields() {
    return array ( 
      "commentaire"   => null,
    );
  }  
  
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }
    
    // Check unique item
    $other = new CPrescriptionLaboExamen;
    $other->prescription_labo_id = $this->prescription_labo_id;
    $other->examen_labo_id = $this->examen_labo_id;
    $other->loadMatchingObject();
    if ($other->_id && $other->_id != $this->_id) {
      return "$this->_class_name-unique-conflict";
    }
    
    // Get the analysis to checl resultat
    if (!$this->examen_labo_id) {
      $old_object = new CPrescriptionLaboExamen();
      $old_object->load($this->_id);
      $this->examen_labo_id = $old_object->examen_labo_id;
    }
    
    // Check resultat according to type
    $this->loadRefExamen();
    $resultTest = CMbFieldSpecFact::getSpec($this, "resultat", $this->_ref_examen_labo->type);
    return $resultTest->checkPropertyValue($this);
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefExamen();
    $examen =& $this->_ref_examen_labo;
    $this->_hors_limite = $examen->type == "num" && ($examen->min > $this->resultat || $examen->max < $this->resultat);
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
  
  function loadRefsFwd() {
    $this->loadRefPrescription();
    $this->loadRefExamen();
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

?>