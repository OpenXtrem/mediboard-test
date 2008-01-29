<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision$
* @author Romain Ollivier
*/

// !! Attention, r�gression importante si ajout de type de paiement
global $AppUI, $can, $m;

$ds = CSQLDataSource::get("std");
$today = mbDate();
$compta = mbGetValueFromGet("compta", '0');
$cs = mbGetValueFromGetOrSession("cs");

// R�cup�ration des param�tres
$filter->_date_min = mbGetValueFromGetOrSession("_date_min", mbDate());
$filter->_date_max = mbGetValueFromGetOrSession("_date_max", mbDate());
$filter->_etat_reglement = mbGetValueFromGetOrSession("_etat_reglement");
$filter->_etat_acquittement = mbGetValueFromGetOrSession("_etat_acquittement");

$type = $filter->mode_reglement = mbGetValueFromGetOrSession("mode_reglement", 0);
if($type == null) {
	$type = 0;
}
$aff = $filter->_type_affichage  = mbGetValueFromGetOrSession("_type_affichage" , 1);
//Traduction pour le passage d'un enum en bool pour les requetes sur la base de donnee
if($aff == "complete") {
	$aff = 1;
} elseif ($aff == "totaux"){
	$aff = 0;
}

$chir = mbGetValueFromGetOrSession("chir");
$chirSel = new CMediusers;
$chirSel->load($chir);

// Requ�te sur les plages de consultation consid�r�es
$where = array();
$ljoin = array();
if($compta) {
  $filter->_etat_reglement = "reglee";
  $filter->_etat_acquittement = "";
  $where[] = "consultation.date_reglement >= '$filter->_date_min'";
  $where[] = "consultation.date_reglement <= '$filter->_date_max'";
  $ljoin["consultation"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";
} else {
  $where[] = "date >= '$filter->_date_min'";
  $where[] = "date <= '$filter->_date_max'";
}

// Chargement des plages
$listPrat = new CMediusers();
$listPrat = $listPrat->loadPraticiens(PERM_READ);
$where["chir_id"] = $ds->prepareIn(array_keys($listPrat), $chir);
$listPlage = new CPlageconsult;
$listPlage = $listPlage->loadList($where, "date, debut, chir_id", null, null, $ljoin);

// On charge les r�f�rences des consultations qui nous interessent
$total["cheque"]["valeur"]  = 0;
$total["CB"]["valeur"]      = 0;
$total["especes"]["valeur"] = 0;
$total["tiers"]["valeur"]   = 0;
$total["autre"]["valeur"]   = 0;
$total["cheque"]["nombre"]  = 0;
$total["CB"]["nombre"]      = 0;
$total["especes"]["nombre"] = 0;
$total["tiers"]["nombre"]   = 0;
$total["autre"]["nombre"]   = 0;
$total["secteur1"]          = 0;
$total["secteur2"]          = 0;
$total["tarif"]             = 0;
$total["nombre"]            = 0;
$total["a_regler"]          = 0;
$total["nb_non_regle"]      = 0;
$total["nb_non_acquitte"]   = 0;
$total["somme_non_regle"]   = 0;
$total["somme_non_acquitte"]= 0;
$total["a_regler_caisse"]   = 0;
$total["somme_non_regle_caisse"] = 0;

$total["cheque"]["reglement"]  = 0;
$total["CB"]["reglement"]      = 0;
$total["especes"]["reglement"] = 0;
$total["tiers"]["reglement"]   = 0;
$total["autre"]["reglement"]   = 0;

foreach($listPlage as $key => $value) {
  
  $listPlage[$key]->loadRefsFwd();
  $where = array();
  $where["plageconsult_id"] = "= '".$value->plageconsult_id."'";
  $where["chrono"] = ">= '".CConsultation::TERMINE."'";
  $where["annule"] = "= '0'";
  $where[] = "tarif IS NOT NULL AND tarif <> ''";
  
  // Facture r�gl�e par le patient
  if($filter->_etat_reglement == "non_reglee"){
    $where["date_reglement"] = " IS NULL";
  }
  // Facture non r�gl�e par le patient
  if($filter->_etat_reglement == "reglee"){
    $where["date_reglement"] = " IS NOT NULL";
  }
  
  // Facture non acquittee
  if($filter->_etat_acquittement == "non_acquittee"){
    $where[] = "`reglement_AM` IS NULL OR `reglement_AM` = '0'";
  }
  // Facture acquittee
  if($filter->_etat_acquittement == "acquittee"){
    $where["reglement_AM"] = " = '1'";
  }
  
  if($type){
    $where["mode_reglement"] = "= '$type'";
  }
  
  // Ne pas prendre en compte les prises en charge aux urgences
  $where["sejour_id"] = "IS NULL";
  
  $listConsult = new CConsultation;
  $listConsult = $listConsult->loadList($where, "heure");
  $listPlage[$key]->_ref_consultations = $listConsult;
  $listPlage[$key]->total1 = 0;
  $listPlage[$key]->total2 = 0;
  
  foreach($listPlage[$key]->_ref_consultations as $key2 => $value2) {
    if($cs == "0" && $value2->_somme == 0){
      unset($listPlage[$key]->_ref_consultations[$key2]);
    }
    $value2->loadRefPatient();
     
    if(isset($total[$value2->mode_reglement]["valeur"])){
      $total[$value2->mode_reglement]["valeur"] += $value2->secteur1 + $value2->secteur2;
      $total[$value2->mode_reglement]["reglement"] += $value2->a_regler;
    }
    else
      $total[$value2->mode_reglement]["valeur"] = $value2->secteur1 + $value2->secteur2;
    if(isset($total[$value2->mode_reglement]["nombre"])){
      $total[$value2->mode_reglement]["nombre"]++;
    }
    else {
      $total[$value2->mode_reglement]["nombre"] = 1;
      
      @$total[$value2->mode_reglement]["reglement"] += $value2->a_regler;
    }
      
    $listPlage[$key]->total1 += $value2->secteur1;
    $listPlage[$key]->total2 += $value2->secteur2;
    $listPlage[$key]->a_regler += $value2->a_regler;  
    
    
    if(!$value2->date_reglement){
      $total["nb_non_regle"]++;
      $total["somme_non_regle"] += $value2->a_regler ;
    }
    if(!$value2->reglement_AM){  
      $total["nb_non_acquitte"]++;
      $total["somme_non_acquitte"] += $value2->_somme;
      $total["somme_non_regle_caisse"] += $value2->_somme - $value2->a_regler;
    }    
    $total["a_regler"] += $value2->a_regler;
  }
  // Total des secteur1
  $total["secteur1"] += $listPlage[$key]->total1;
  // Total des secteur2
  $total["secteur2"] += $listPlage[$key]->total2;
  // Total Factur�
  $total["tarif"]    += $listPlage[$key]->total1 + $listPlage[$key]->total2;
  // Nombre de consultations
  $total["nombre"]   += count($listPlage[$key]->_ref_consultations);
  if(!count($listPlage[$key]->_ref_consultations)){
    unset($listPlage[$key]);
  }
}


// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("cs"                 , $cs);
$smarty->assign("compta"             , $compta);
$smarty->assign("today"              , $today);
$smarty->assign("filter"             , $filter);
$smarty->assign("aff"                , $aff);
$smarty->assign("_etat_reglement"    , $filter->_etat_reglement);
$smarty->assign("_etat_acquittement" , $filter->_etat_acquittement);
$smarty->assign("type"               , $type);
$smarty->assign("chirSel"            , $chirSel);
$smarty->assign("listPlage"          , $listPlage);
$smarty->assign("total"              , $total);

$smarty->display("print_rapport.tpl");

?>