<?php

/**
 * $Id: $
 *
 * @category Maternit�
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org
 */

/**
 * Gestion des grossesses d'une parturiente
 */

class CGrossesse extends CMbObject {
  // DB Table key
  public $grossesse_id;
  
  // DB References
  public $parturiente_id;
  public $group_id;

  // DB Fields
  public $terme_prevu;
  public $active;
  public $multiple;
  public $allaitement_maternel;
  public $date_fin_allaitement;
  public $date_dernieres_regles;
  public $lieu_accouchement;
  public $fausse_couche;
  public $rques;

  // Timings de l'accouchement, date+heure pour permettre les accouchements sur plusieurs jours (pas comme dans COperation)
  public $datetime_debut_travail;
  public $datetime_accouchement;

  /** @var CPatient */
  public $_ref_parturiente;

  /** @var CNaissance[] */
  public $_ref_naissances;

  /** @var CSejour[] */
  public $_ref_sejours = array();

  /** @var CConsultation[] */
  public $_ref_consultations = array();

  /** @var CConsultAnesth */
  public $_ref_last_consult_anesth;
  
  // Form fields
  public $_praticiens;
  public $_date_fecondation;
  public $_semaine_grossesse;
  public $_terme_vs_operation;
  public $_operation_id;
  public $_allaitement_en_cours;
  public $_last_consult_id;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'grossesse';
    $spec->key   = 'grossesse_id';

    $spec->events = array(
      "suivi" => array(
        "reference1" => array("CConsultation", "_last_consult_id"),
        "reference2" => array("CPatient", "parturiente_id"),
      ),
    );
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["parturiente_id"] = "ref notNull class|CPatient";
    $specs["group_id"]       = "ref class|CGroups";
    $specs["terme_prevu"]    = "date notNull";
    $specs["active"]         = "bool default|1";
    $specs["multiple"]       = "bool default|0";
    $specs["allaitement_maternel"] = "bool default|0";
    $specs["date_fin_allaitement"] = "date";
    if (CAppUI::conf("maternite CGrossesse date_regles_obligatoire")) {
      $specs["date_dernieres_regles"] = "date notNull";
    }
    else {
      $specs["date_dernieres_regles"] = "date";
    }
    $specs["lieu_accouchement"] = "enum list|sur_site|exte default|sur_site";
    $specs["fausse_couche"]     = "enum list|inf_15|sup_15";
    $specs["rques"]             = "text helped";

    $specs["datetime_debut_travail"] = "dateTime";
    $specs["datetime_accouchement"]  = "dateTime";

    $specs["_last_consult_id"]  = "ref class|CConsultation";
    return $specs;
  }

  /**
   * @see parent::getBackProps()
   */
  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps["naissances"] = "CNaissance grossesse_id";
    $backProps["consultations"] = "CConsultation grossesse_id";
    $backProps["sejours"] = "CSejour grossesse_id";
    return $backProps;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefParturiente();
  }

  /**
   * Chargement de la parturiente
   *
   * @return CMediusers
   */
  function loadRefParturiente() {
    return $this->_ref_parturiente = $this->loadFwdRef("parturiente_id", true);
  }

  /**
   * Chargement des naissances associ�es � la grossesse
   *
   * @return CNaissance[]
   */
  function loadRefsNaissances() {
    return $this->_ref_naissances = $this->loadBackRefs("naissances");
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefParturiente();
    $this->_view = "Terme du " . CMbDT::dateToLocale($this->terme_prevu);
    // Nombre de semaines (am�norrh�e = 41, grossesse = 39)
    $this->_date_fecondation = CMbDT::date("-41 weeks", $this->terme_prevu);
    $this->_allaitement_en_cours =
      $this->allaitement_maternel && !$this->active && (!$this->date_fin_allaitement || $this->date_fin_allaitement > CMbDT::date());
    $this->_semaine_grossesse = ceil(CMbDT::daysRelative($this->_date_fecondation, CMbDT::date()) / 7);
  }

  /**
   * Chargement des s�jours associ�s � la grossesse
   *
   * @return CSejour[]
   */
  function loadRefsSejours() {
    return $this->_ref_sejours = $this->loadBackRefs("sejours");
  }

  /**
   * Chargement des consultations associ�es � la grossesse
   *
   * @return CConsultation[]
   */
  function loadRefsConsultations() {
    if ($this->_ref_consultations) {
      return $this->_ref_consultations;
    }
    return $this->_ref_consultations = $this->loadBackRefs("consultations");
  }

  /**
   * Chargement de la derni�re consultation d'anesth�sie pour une grossesse
   *
   * @return CConsultation
   */
  function loadLastConsultAnesth() {
    $consultations = $this->loadRefsConsultations();
    foreach ($consultations as $_consultation) {
      $consult_anesth = $_consultation->loadRefConsultAnesth();
      if ($consult_anesth->_id) {
        return $this->_ref_last_consult_anesth = $_consultation;
      }
    }
    return $this->_ref_last_consult_anesth = new CConsultation();
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();

    $naissances = $this->loadRefsNaissances();
    $sejours = CMbObject::massLoadFwdRef($naissances, "sejour_enfant_id");
    CMbObject::massLoadFwdRef($sejours, "patient_id");
    
    foreach ($naissances as $_naissance) {
      $_naissance->loadRefSejourEnfant()->loadRefPatient();
    }
  }

  /**
   * @see parent::loadComplete()
   */
  function loadComplete(){
    parent::loadComplete();

    $this->loadLastConsult();
  }

  /**
   * Load last consult
   *
   * @return CConsultation|null
   */
  function loadLastConsult(){
    $consultations = $this->loadRefsConsultations();
    $last_consult = end($consultations);

    $this->_last_consult_id = null;

    if ($last_consult && $last_consult->_id) {
      $this->_last_consult_id = $last_consult->_id;
    }

    return $last_consult;
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    $consults = $this->loadRefsConsultations();
    $sejours  = $this->loadRefsSejours();
    
    if ($msg = parent::delete()) {
      return $msg;
    }
    
    $msg = "";
    
    foreach ($consults as $_consult) {
      $_consult->grossesse_id = "";
      if ($_msg = $_consult->store()) {
        $msg .= "\n $_msg";
      }
    }
    
    
    foreach ($sejours as $_sejour) {
      $_sejour->grossesse_id = "";
      if ($_msg = $_sejour->store()) {
        $msg .= "\n $_msg";
      }
    }
    
    if ($msg) {
      return $msg;
    }

    return null;
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id) {
      $this->group_id = CGroups::loadCurrent()->_id;
    }

    return parent::store();
  }
}
