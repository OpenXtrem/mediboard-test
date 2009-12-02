<?php

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision:
* @author Romain Ollivier
*/

global $AppUI, $can, $m, $g;

$can->needsRead();

$mediuser = new CMediusers;
$mediuser->load($AppUI->user_id);

//Initialisations des variables
$cabinet_id   = CValue::getOrSession("cabinet_id", $mediuser->function_id);
$date         = CValue::getOrSession("date", mbDate());
$closed       = CValue::getOrSession("closed", true);
$mode_urgence = CValue::get("mode_urgence", false);

$hour         = mbTime(null);
$board        = CValue::get("board", 1);
$boardItem    = CValue::get("boardItem", 1);
$consult      = new CConsultation();

$cabinets = CMediusers::loadFonctions(PERM_EDIT, $g, "cabinet");

if($mode_urgence){
  $group = CGroups::loadCurrent();
  $cabinet_id = $group->service_urgences_id;
}

// Récupération de la liste des praticiens
$praticiens = array();
if ($cabinet_id) {
  if(CAppUI::pref("pratOnlyForConsult", 1)) {
    $praticiens = $mediuser->loadPraticiens(PERM_READ, $cabinet_id);
  } else {
    $praticiens = $mediuser->loadProfessionnelDeSante(PERM_READ, $cabinet_id);
  }
}

if ($consult->_id) {
  $date = $consult->_ref_plageconsult->date;
  CValue::setSession("date", $date);
}

// Récupération des plages de consultation du jour et chargement des références
$listPlages = array();
foreach($praticiens as $prat) {
  $listPlage = new CPlageconsult();
  $where = array();
  $where["chir_id"] = "= '$prat->_id'";
  $where["date"] = "= '$date'";
  $order = "debut";
  $listPlage = $listPlage->loadList($where, $order);
  if(!count($listPlage)) {
    unset($praticiens[$prat->_id]);
  } 
  else {
    $listPlages[$prat->_id]["prat"] = $prat;
    $listPlages[$prat->_id]["plages"] = $listPlage;
    $listPlages[$prat->_id]["destinations"] = array();    
  }
}

$nb_attente = 0;
foreach ($listPlages as &$infos_by_prat) {
  foreach ($infos_by_prat["plages"] as $plage) {
    $plage->_ref_chir =& $infos_by_prat["prat"];
    $plage->loadRefsConsultations(true, $closed);
    foreach ($plage->_ref_consultations as &$consultation) {
      if ($mode_urgence){
        $consultation->loadRefSejour();
        $consultation->_ref_sejour->loadRefRPU();
        if ($consultation->_ref_sejour->_ref_rpu->_id){
          unset($plage->_ref_consultations[$consultation->_id]);
          continue;
        }
      }
      
      if ($consultation->chrono == CConsultation::PATIENT_ARRIVE) {
        $nb_attente++;
      }
      $consultation->loadRefSejour();
      $consultation->loadRefPatient();
      $consultation->loadRefCategorie();
      $consultation->countDocItems();
    }
  }
}

// Destinations : plages des autres praticiens
foreach ($listPlages as &$infos_by_prat) {
  foreach ($listPlages as &$infos_other_prat) {
    if ($infos_by_prat["prat"]->_id != $infos_other_prat["prat"]->_id) {
      foreach ($infos_other_prat["plages"] as $other_plage) {
        $infos_by_prat["destinations"][] = $other_plage;
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("cabinet_id"    , $cabinet_id);
$smarty->assign("consult"       , $consult);
$smarty->assign("listPlages"    , $listPlages);
$smarty->assign("closed"        , $closed);
$smarty->assign("date"          , $date);
$smarty->assign("hour"          , $hour);
$smarty->assign("praticiens"    , $praticiens);
$smarty->assign("cabinets"      , $cabinets);
$smarty->assign("board"         , $board);
$smarty->assign("boardItem"     , $boardItem);
$smarty->assign("canCabinet"    , CModule::getCanDo("dPcabinet"));
$smarty->assign("mode_urgence"  , $mode_urgence);
$smarty->assign("nb_attente"    , $nb_attente);
$smarty->display("vw_journee.tpl");


?>