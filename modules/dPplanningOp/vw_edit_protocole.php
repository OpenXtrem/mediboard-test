<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPplanningOp
* @version $Revision$
* @author Romain Ollivier
*/

global $AppUI, $can, $m, $tab, $dPconfig;

$can->needsEdit();

$protocole_id = CValue::getOrSession("protocole_id", 0);
$chir_id      = CValue::getOrSession("chir_id"     , 0);

// L'utilisateur est-il un praticien
$mediuser = new CMediusers;
$mediuser->load($AppUI->user_id);
if ($mediuser->isPraticien() and !$chir_id) {
  $chir_id = $mediuser->user_id;
}

// Chargement du praticien
$chir = new CMediusers;
if ($chir_id) {
  $chir->load($chir_id);
}

// V�rification des droits sur les praticiens
$listPraticiens = $mediuser->loadPraticiens(PERM_EDIT);

$protocole = new CProtocole;
if($protocole_id) {
  $protocole->load($protocole_id);
  $protocole->loadRefs();

  // On v�rifie que l'utilisateur a les droits sur l'operation
  if (!array_key_exists($protocole->chir_id, $listPraticiens)) {
    $AppUI->setMsg("Vous n'avez pas acc�s � ce protocole", UI_MSG_WARNING);
    $AppUI->redirect("m=$m&tab=$tab&protocole_id=0"); 
  }
  $chir =& $protocole->_ref_chir;
}


// Dur�e d'une intervention
$start = CAppUI::conf("dPplanningOp COperation duree_deb");
$stop  = CAppUI::conf("dPplanningOp COperation duree_fin");
$step  = CAppUI::conf("dPplanningOp COperation min_intervalle");

$hours = range($start, $stop);
$mins = range(0,59,$step);

// R�cup�ration des services
$service = new CService();
$where = array();
$where["group_id"] = "= '".CGroups::loadCurrent()->_id."'";
$order = "nom";
$listServices = $service->loadListWithPerms(PERM_READ,$where, $order);


// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("mediuser", $mediuser);
$smarty->assign("protocole", $protocole);
$smarty->assign("chir"     , $chir);

$smarty->assign("listPraticiens", $listPraticiens);
$smarty->assign("listServices"  , $listServices);

$smarty->assign("hours", $hours);
$smarty->assign("mins" , $mins);

$smarty->display("vw_edit_protocole.tpl");

?>