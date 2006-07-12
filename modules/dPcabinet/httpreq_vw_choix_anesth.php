<?php /* $Id: httpreq_vw_consult_anesth.php 23 2006-05-04 15:05:35Z MyttO $ */

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision: 23 $
* @author Romain Ollivier
*/

global $AppUI, $canRead, $canEdit, $m;

require_once( $AppUI->getModuleClass('dPcabinet', 'consultation') );
  
if (!$canEdit) {
  $AppUI->redirect( "m=system&a=access_denied" );
}

// Utilisateur s�lectionn� ou utilisateur courant
$prat_id = mbGetValueFromGetOrSession("chirSel", 0);

$userSel = new CMediusers;
$userSel->load($prat_id ? $prat_id : $AppUI->user_id);
$userSel->loadRefs();

// V�rification des droits sur les praticiens
$listChir = $userSel->loadPraticiens(PERM_EDIT);

if (!$userSel->isPraticien()) {
  $AppUI->setMsg("Vous devez selectionner un praticien", UI_MSG_ALERT);
  $AppUI->redirect("m=dPcabinet&tab=0");
}

if (!$userSel->isAllowed(PERM_EDIT)) {
  $AppUI->setMsg("Vous n'avez pas les droits suffisants", UI_MSG_ALERT);
  $AppUI->redirect("m=dPcabinet&tab=0");
}

$selConsult = mbGetValueFromGetOrSession("selConsult", 0);
if (isset($_GET["date"])) {
  $selConsult = null;
  mbSetValueToSession("selConsult", 0);
}

//Liste des types d'anesth�sie
$anesth = dPgetSysVal("AnesthType");

//Liste des types d'anesth�sie
$anesth = dPgetSysVal("AnesthType");

// Consultation courante
$consult = new CConsultation();
$consult->_ref_chir = $userSel;
$consult->_ref_consult_anesth->consultation_anesth_id = 0;
if ($selConsult) {
  $consult->load($selConsult);
  $consult->loadRefConsultAnesth();
  $consult->loadRefPlageConsult();
  
  // On v�rifie que l'utilisateur a les droits sur la consultation
  $right = false;
  foreach($listChir as $key => $value) {
    if($value->user_id == $consult->_ref_plageconsult->chir_id)
      $right = true;
  }
  if(!$right) {
    $AppUI->setMsg("Vous n'avez pas acc�s � cette consultation", UI_MSG_ALERT);
    $AppUI->redirect( "m=dPpatients&tab=0&id=$consult->patient_id");
  }
  if($consult->_ref_consult_anesth->consultation_anesth_id) {
    $consult->_ref_consult_anesth->loadRefs();
  }

  $consult_anesth =& $consult->_ref_consult_anesth;
  
}

// Cr�ation du template
require_once( $AppUI->getSystemClass ('smartydp' ) );
$smarty = new CSmartyDP;

$smarty->assign('consult_anesth', $consult_anesth);
$smarty->assign('anesth'        , $anesth        );

$smarty->display('inc_type_anesth.tpl');

?>