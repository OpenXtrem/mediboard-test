<?php /* $Id$ */

/**
 *	@package Mediboard
 *	@subpackage dPhospi
 *	@version $Revision: $
 *  @author Thomas Despoix
 */

require_once($AppUI->getModuleClass("mediusers"));
require_once($AppUI->getModuleClass("dPplanningOp", "planning"  ));
require_once($AppUI->getModuleClass("dPpatients"  , "patients"  ));
require_once($AppUI->getModuleClass("dPplanningOp", "pathologie"));

// @todo: Put the following in $config_dist;
$dPconfig["dPplanningOp"]["sejour"] = array (
  "heure_deb" => "7",
  "heure_fin" => "20",
  "min_intervalle" => "15"
);

/**
 * Classe CSejour. 
 * @abstract G�re les s�jours en �tablissement
 */
class CSejour extends CMbObject {
  // DB Table key
  var $sejour_id = null;
  
  // DB R�ference
  var $patient_id = null; // remplace $op->pat_id
  var $praticien_id = null; // clone $op->chir_id
  
  // DB Fields
  var $type = null; // remplace $op->type_adm
  var $modalite_hospitalisation = null;
  var $annule = null; // compl�te $op->annule
  var $chambre_seule = null; // remplace $op->chambre
  
  var $entree_prevue = null;
  var $sortie_prevue = null;
  var $entree_reelle = null;
  var $sortie_reelle = null;

  var $venue_SHS = null; // remplace $op->venue_SHS
  var $saisi_SHS = null; // remplace $op->saisie
  var $modif_SHS = null; // remplace $op->modifiee

  var $DP = null; // remplace $operation->CIM10_code
  var $pathologie = null; // remplace $operation->pathologie
  var $septique = null; // remplace $operation->septique
  var $convalescence = null; // remplace $operation->convalescence
  
  var $rques = null;
  
  // Form Fields
  var $_duree_prevue = null;
  
  var $_date_entree_prevue = null;
  var $_date_sortie_prevue = null;
  var $_hour_entree_prevue = null;
  var $_hour_sortie_prevue = null;
  var $_min_entree_prevue = null;
  var $_min_sortie_prevue = null;

  var $_venue_SHS_guess = null;

  // Object References  
  var $_ref_patient = null;
  var $_ref_praticien = null;
  var $_ref_operations = null;
  var $_ref_last_operation = null;
  var $_ref_affectations = null;
  var $_ref_first_affectation = null;
  var $_ref_last_affectation = null;
  var $_ref_GHM = array();
  
	function CSejour() {
		$this->CMbObject("sejour", "sejour_id");
    
    $this->_props["patient_id"]    = "ref|notNull";
    $this->_props["praticien_id"]    = "ref|notNull";
    
    $this->_props["type"] = "enum|comp|ambu|exte";
    $this->_props["modalite_hospitalisation"] = "enum|office|libre|tiers";
    $this->_props["annule"] = "enum|0|1";
    $this->_props["chambre_seule"] = "enum|o|n";

    $this->_props["entree_prevue"] = "dateTime|notNull";
    $this->_props["sortie_prevue"] = "dateTime|moreThan|entree_prevue|notNull";
    $this->_props["entree_reelle"] = "dateTime";
    $this->_props["sortie_reelle"] = "dateTime";
    
    $this->_props["venue_SHS"] = "num|length|8|confidential";
    $this->_props["saisi_SHS"] = "enum|o|n";
    $this->_props["modif_SHS"] = "enum|0|1";

    $this->_props["DP"] = "code|cim10";
    $this->_props["pathologie"] = "str|length|3";
    $this->_props["septique"] = "enum|0|1";
    $this->_props["convalescence"] = "str|confidential";
	}

  function check() {
    $msg = null;
    global $pathos;

    if ($this->pathologie != null && (!in_array($this->pathologie, $pathos->dispo))) {
      $msg.= "Pathologie non disponible<br />";
    }

    return $msg . parent::check();
  }
  
  function canDelete(&$msg, $oid = null) {
    $tables[] = array (
      "label" => "op�rations", 
      "name" => "operations", 
      "idfield" => "operation_id", 
      "joinfield" => "sejour_id"
    );
    
    return CDpObject::canDelete( $msg, $oid, $tables );
  }
  
  function bindToOp($operation_id) {
    $operation = new COperation;
    $operation->load($operation_id);
    $this->load($operation->sejour_id);
    $this->patient_id    = $operation->pat_id;
    $this->praticien_id  = $operation->chir_id;
    $this->type          = $operation->type_adm;
    $this->annule        = $operation->annulee;
    $this->chambre_seule = $operation->chambre;
    $this->entree_prevue = $operation->date_adm." ".$operation->time_adm;
    $this->sortie_prevue = mbDateTime("+".$operation->duree_hospi." DAYS", $this->entree_prevue);
    $this->sortie_reelle = '';
    $this->venue_SHS     = $operation->venue_SHS;
    $this->saisi_SHS     = $operation->saisie;
    $this->modif_SHS     = $operation->modifiee;
    $this->DP            = $operation->CIM10_code;
    $this->pathologie    = $operation->pathologie;
    $this->septique      = $operation->septique;
    $this->convalescence = $operation->convalescence;
  }
  
  function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    if ($this->annule) {
      $this->delAffectations();
    }

    // Cas o� on a une premiere affectation diff�rente de l'heure d'admission
    if ($this->entree_prevue) {
      $this->loadRefsAffectations();
      $affectation =& $this->_ref_first_affectation;
      $admission = $this->entree_prevue;
      if ($affectation->affectation_id && ($affectation->entree != $this->entree_prevue)) {
        $affectation->entree = $this->entree_prevue;
        $affectation->store();
      }
    }

//    // Synchronisation vers les op�rations
//    $this->loadRefsOperations();
//    foreach($this->_ref_operations as $keyOp => $valueOp) {
//      $operation =& $this->_ref_operations[$keyOp];
//      $operation->pat_id = $this->patient_id;
//      $operation->chir_id = $this->praticien_id;
//      $operation->type_adm = $this->type;
//      $operation->annulee = $this->annule;
//      $operation->chambre = $this->chambre_seule;
//      $operation->date_adm = mbDate(null, $this->entree_prevue);
//      $operation->time_adm = mbTime(null, $this->entree_prevue);
//      $operation->duree_hospi = mbDaysRelative($this->entree_prevue, $this->sortie_prevue);
//      $operation->venue_SHS = $this->venue_SHS;
//      $operation->saisie = $this->saisi_SHS;
//      $operation->modifiee = $this->modif_SHS;
//      $operation->pathologie = $this->pathologie;
//      $operation->septique = $this->septique;
//      $operation->convalescence = $this->convalescence;
//      $msgOp = $operation->store();
//    }
  }
  
  function delete() {
    $msg = parent::delete();
    if ($msg == null) {
      // Suppression des affectations
      $this->delAffectations();
    }
    return $msg;
  }
  
  function delAffectations() {
    $this->loadRefsAffectations();
    foreach($this->_ref_affectations as $key => $value) {
      $this->_ref_affectations[$key]->delete();
    }
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    
    $this->_duree_prevue = mbDaysRelative($this->entree_prevue, $this->sortie_prevue);
    
    $this->_date_entree_prevue = mbDate(null, $this->entree_prevue);
    $this->_date_sortie_prevue = mbDate(null, $this->sortie_prevue);
    $this->_hour_entree_prevue = mbTranformTime(null, $this->entree_prevue, "%H");
    $this->_hour_sortie_prevue = mbTranformTime(null, $this->sortie_prevue, "%H");
    $this->_min_entree_prevue = mbTranformTime(null, $this->entree_prevue, "%M");
    $this->_min_sortie_prevue = mbTranformTime(null, $this->sortie_prevue, "%M");
    
    $this->_venue_SHS_guess = mbTranformTime(null, $this->entree_prevue, "%y");
    $this->_venue_SHS_guess .= 
      $this->type == "exte" ? "5" :
      $this->type == "ambu" ? "4" : "0";
    $this->_venue_SHS_guess .="xxxxx";
  }
  
  function updateDBFields() {
    if ($this->_hour_entree_prevue !== null and $this->_min_entree_prevue !== null) {
      $time_entree_prevue = mbTime(null, "$this->_hour_entree_prevue:$this->_min_entree_prevue");
      $this->entree_prevue = mbAddDateTime($time_entree_prevue, $this->_date_entree_prevue);
    }
    
    if ($this->_hour_sortie_prevue !== null and $this->_min_sortie_prevue !== null) {
      $time_sortie_prevue = mbTime(null, "$this->_hour_sortie_prevue:$this->_min_sortie_prevue");
      $this->sortie_prevue = mbAddDateTime($time_sortie_prevue, $this->_date_sortie_prevue);
    }
  }
  
  function loadRefPatient() {
    $where = array (
      "patient_id" => "= '$this->patient_id'"
    );

    $this->_ref_patient = new CPatient;
    $this->_ref_patient->loadObject($where);
  }
  
  function loadRefPraticien() {
    $where = array (
      "user_id" => "= '$this->praticien_id'"
    );

    $this->_ref_praticien = new CMediusers;
    $this->_ref_praticien->loadObject($where);
  }
  
  function loadRefsFwd() {
    $this->loadRefPatient();
    $this->loadRefPraticien();
  }
  
  function loadRefsAffectations() {
    $where = array("sejour_id" => "= '$this->sejour_id'");
    $order = "sortie DESC";
    $this->_ref_affectations = new CAffectation();
    $this->_ref_affectations = $this->_ref_affectations->loadList($where, $order);

    if (count($this->_ref_affectations) > 0) {
      $this->_ref_first_affectation =& end($this->_ref_affectations);
      $this->_ref_last_affectation =& reset($this->_ref_affectations);
    } else {
      $this->_ref_first_affectation =& new CAffectation;
      $this->_ref_last_affectation =& new CAffectation;
    }
  }
  
  function loadRefsOperations() {
    $where = array (
      "sejour_id" => "= '$this->sejour_id'"
    );
    
    $ljoin = array (
      "plagesop" => "plagesop.id = operation.plageop_id"
    );
    
    $order = "plagesop.date DESC";

    $operations = new COperation;
    $this->_ref_operations = $operations->loadList($where);
    
    if (count($this->_ref_operations) > 0) {
      $this->_ref_last_operation =& reset($this->_ref_operations);
    } else {
      $this->_ref_last_operation =& new COperation;
    }
  }
  
  function loadRefsBack() {
    $this->loadRefsAffectations();
    $this->loadRefsOperations();
  }
  
  function loadRefGHM () {
    $this->_ref_GHM = new CGHM;
    $where["sejour_id"] = "= '$this->sejour_id'";
    $this->_ref_GHM->loadObject($where);
    if(!$this->_ref_GHM->ghm_id) {
      $this->_ref_GHM->sejour_id = $this->sejour_id;
      $this->_ref_GHM->loadRefsFwd();
      $this->_ref_GHM->bindInfos();
      $this->_ref_GHM->getGHM();
    }
  }
}
?>