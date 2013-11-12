<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage Hospi
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$services_ids   = CValue::getOrSession("services_ids", null);
$readonly       = CValue::get("readonly", 0);
$granularite    = CValue::getOrSession("granularite", "day");
$date           = CValue::getOrSession("date", CMbDT::date());
$mode_vue_tempo = CValue::getOrSession("mode_vue_tempo", "classique");
$readonly       = CValue::getOrSession("readonly", 0);
$prestation_id  = CValue::getOrSession("prestation_id", 0);

if (is_array($services_ids)) {
  CMbArray::removeValue("", $services_ids);
}

if (!$services_ids) {
  $smarty = new CSmartyDP();
  $smarty->display("inc_no_services.tpl");
  CApp::rip();
}

$unite = "";
$period = "";
$datetimes = array();
$change_month = array();
$granularites = array("day", "week", "4weeks");

switch ($granularite) {
  case "day":
    $unite = "hour";
    $nb_unite = 1;
    $nb_ticks = 24;
    $step = "+1 hour";
    $period = "1hour";
    $date_min = CMbDT::dateTime($date);
    $date_before = CMbDT::date("-1 day", $date);
    $date_after  = CMbDT::date("+1 day", $date);
    break;
  case "week":
    $unite = "hour";
    $nb_unite = 6;
    $nb_ticks = 28;
    $step = "+6 hours";
    $period = "6hours";
    $date_min = CMbDT::dateTime("-2 days", $date);
    $date_before = CMbDT::date("-1 week", $date);
    $date_after = CMbDT::date("+1 week", $date);
    break;
  case "4weeks":
    $unite = "day";
    $nb_unite = 1;
    $nb_ticks = 28;
    $step = "+1 day";
    $period = "1day";
    $date_min = CMbDT::dateTime("-1 week", CMbDate::dirac("week", $date));
    $date_before = CMbDT::date("-4 week", $date);
    $date_after = CMbDT::date("+4 week", $date);
}

$current = CMbDate::dirac("hour", CMbDT::dateTime());
$offset = $nb_ticks * $nb_unite;
$date_max = CMbDT::dateTime("+ $offset $unite", $date_min);
$temp_datetime = CMbDT::dateTime(null, $date_min);

for ($i = 0 ; $i < $nb_ticks ; $i++) {
  $offset = $i * $nb_unite;

  $datetime = CMbDT::dateTime("+ $offset $unite", $date_min);
  $datetimes[] = $datetime;

  if ($granularite == "4weeks") {
    if (CMbDT::date($current) == CMbDT::date($temp_datetime) &&
        CMbDT::time($current) >= CMbDT::time($temp_datetime) && CMbDT::time($current) > CMbDT::time($datetime)
    ) {
      $current = $temp_datetime;
    }
    $week_a = CMbDT::transform($temp_datetime, null, "%V");
    $week_b = CMbDT::transform($datetime, null, "%V");

    // les semaines
    $days[$datetime] = $week_b;

    // On stocke le changement de mois s'il advient
    if (CMbDT::transform($datetime, null, "%m") != CMbDT::transform($temp_datetime, null, "%m")) {

      // Entre deux semaines
      if ($i%7 == 0) {
        $change_month[$week_a] = array("right"=>$temp_datetime);
        $change_month[$week_b] = array("left"=>$datetime);
      }
      // Dans la m�me semaine
      else {
        $change_month[$week_b] = array("left" => $temp_datetime, "right" => $datetime);
      }
    }
  }
  else {
    if ($granularite == "week" && CMbDT::date($current) == CMbDT::date($temp_datetime) &&
        CMbDT::time($datetime) >= CMbDT::time($temp_datetime) && CMbDT::time($current) <= CMbDT::time($datetime)
    ) {
      $current = $temp_datetime;
    }
    if ($granularite) {
      // le datetime, pour avoir soit le jour soit l'heure
      $days[] = CMbDT::date($datetime);
    }
  }
  $temp_datetime = $datetime;
}

$days = array_unique($days);

// Cas de la semaine 00
if ($granularite == "4weeks" && count($days) == 5) {
  array_pop($days);
}

// Chargement des lits
$group_id = CGroups::loadCurrent()->_id;
$where = array();
$where["chambre.service_id"] = CSQLDataSource::prepareIn($services_ids);
$where["service.group_id"] = " = '$group_id'";
$where["chambre.annule"] = "= '0'";
$where["lit.annule"] = "= '0'";
$ljoin = array();
$ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";
$ljoin["service"] = "chambre.service_id = service.service_id";
$lit = new CLit();
$lits     = $lit->loadList($where, "chambre.nom", null, null, $ljoin);
$chambres = CMbObject::massLoadFwdRef($lits, "chambre_id");
$services = CMbObject::massLoadFwdRef($chambres, "service_id");

foreach ($lits as $_lit) {
  $_lit->_ref_affectations = array();
  $chambre = $_lit->loadRefChambre();
  $chambre->_ref_lits[$_lit->_id] = $_lit;
  $service = $chambre->loadRefService();
  $service->_ref_chambres[$chambre->_id] = $chambre;
  $liaisons_items = $_lit->loadBackRefs("liaisons_items");
  $items_prestations = CMbObject::massLoadFwdRef($liaisons_items, "item_prestation_id");
  $prestations_ids = CMbArray::pluck($items_prestations, "object_id");

  $_lit->_selected_item = new CItemPrestation();

  if (in_array($prestation_id, $prestations_ids)) {
    $inverse = array_flip($prestations_ids);
    $item_prestation = $items_prestations[$inverse[$prestation_id]];
    if ($item_prestation->_id) {
      $_lit->_selected_item = $item_prestation;
    }
  }
}

array_multisort(CMbArray::pluck($services, "nom"), SORT_ASC, $services);

// Chargement des affectations
$where = array();
$where["lit_id"] = CSQLDataSource::prepareIn(array_keys($lits));
$where["entree"] = "< '$date_max'";
$where["sortie"] = "> '$date_min'";

$affectation = new CAffectation();
$nb_affectations = $affectation->countList($where);
if ($nb_affectations > CAppUI::conf("dPhospi max_affectations_view")) {
  $smarty = new CSmartyDP();
  $smarty->display("inc_vw_max_affectations.tpl");
  CApp::rip();
}

$affectations = $affectation->loadList($where, "parent_affectation_id ASC");

// Ajout des prolongations anormales
// (s�jours avec entr�e r�elle et sortie non confirm�e et sortie < maintenant
$nb_days_prolongation = CAppUI::conf("dPhospi nb_days_prolongation");

if ($nb_days_prolongation) {
  $sejour = new CSejour();
  $max = CMbDT::dateTime();
  $min = CMbDT::date("-$nb_days_prolongation days", $max) . " 00:00:00";
  $where = array(
    "entree_reelle"   => "IS NOT NULL",
    "sortie_reelle"   => "IS NULL",
    "sortie_prevue"   => "BETWEEN '$min' AND '$max'",
    "sejour.confirme" => "= '0'",
    "group_id"        => "= '$group_id'"
  );

  $sejours_prolonges = $sejour->loadList($where);

  $affectations_prolong = array();
  foreach ($sejours_prolonges as $_sejour) {
    $aff = $_sejour->getCurrAffectation($_sejour->sortie);
    if (!$aff->_id || !array_key_exists($aff->lit_id, $lits)) {
      continue;
    }
    $aff->_is_prolong = true;
    $affectations[$aff->_id] = $aff;
  }
}

$sejours  = CMbObject::massLoadFwdRef($affectations, "sejour_id");
$patients = CMbObject::massLoadFwdRef($sejours, "patient_id");
$praticiens = CMbObject::massLoadFwdRef($sejours, "praticien_id");
CMbObject::massLoadFwdRef($praticiens, "function_id");
$operations = array();

$suivi_affectation = false;

loadVueTempo($affectations, $suivi_affectation, $lits, $operations, $date_min, $date_max, $period, $prestation_id);

$dossiers = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "_ref_dossier_medical");
CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");

foreach ($lits as $_lit) {
  $intervals = array();
  if (isset($_lit->_ref_affectations) && count($_lit->_ref_affectations)) {
    foreach ($_lit->_ref_affectations as $_affectation) {
      $intervals[$_affectation->_id] = array(
        "lower" => $_affectation->_entree,
        "upper" => $_affectation->_sortie,
      );
    }
    $_lit->_lines = CMbRange::rearrange($intervals);
  }
}

if (!CAppUI::conf("dPhospi hide_alertes_temporel")) {
  foreach ($lits as $_lit) {
    $_lit->_ref_chambre->checkChambre();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("services"    , $services);
$smarty->assign("affectations", $affectations);
$smarty->assign("date"        , $date);
$smarty->assign("date_min"    , $date_min);
$smarty->assign("date_max"    , $date_max);
$smarty->assign("date_before" , $date_before);
$smarty->assign("date_after"  , $date_after);
$smarty->assign("granularites", $granularites);
$smarty->assign("granularite" , $granularite);
$smarty->assign("nb_ticks"    , $nb_ticks);
$smarty->assign("datetimes"   , $datetimes);
$smarty->assign("days"        , $days);
$smarty->assign("change_month", $change_month);
$smarty->assign("mode_vue_tempo", $mode_vue_tempo);
$smarty->assign("readonly"    , $readonly);
$smarty->assign("nb_affectations", $nb_affectations);
$smarty->assign("readonly"    , $readonly);
$smarty->assign("current"     , $current);
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("suivi_affectation", $suivi_affectation);
$smarty->assign("td_width"    , 84.2 / $nb_ticks);

$smarty->display("inc_vw_mouvements.tpl");
