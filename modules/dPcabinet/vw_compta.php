<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision$
* @author Thomas Despoix
*/

global $AppUI, $can, $m;

$can->needsEdit();

//$deb = mbDate();
//$fin = mbDate("+ 0 day");

$filter = new CConsultation;

$filter->_date_min = mbDate();
$filter->_date_max = mbDate("+ 0 day");

$filter->_etat_paiement = mbGetValueFromGetOrSession("_etat_paiement", 0);
$filter->type_tarif = mbGetValueFromGetOrSession("type_tarif", 0);
$filter->_type_affichage = mbGetValueFromGetOrSession("_type_affichage", 0);

// Edite t'on un tarif ?
$tarif_id = mbGetValueFromGetOrSession("tarif_id", null);
$tarif = new CTarif;
$tarif->load($tarif_id);

// L'utilisateur est-il praticien ?
$mediuser = new CMediusers();
$mediuser->load($AppUI->user_id);
$mediuser->loadRefFunction();

// Liste des tarifs du praticien
$listeTarifsChir = null;

$is_praticien = 0;

if ($mediuser->isPraticien()) {
  $is_praticien = 1;
  $where = array();
  $where["function_id"] = "IS NULL";
  $where["chir_id"] = "= '$mediuser->user_id'";
  $listeTarifsChir = new CTarif();
  $listeTarifsChir = $listeTarifsChir->loadList($where);
}


// Liste des tarifs de la sp�cialit�
$where = array();
$where["chir_id"] = "IS NULL";
$where["function_id"] = "= '$mediuser->function_id'";
$listeTarifsSpe = new CTarif();
$listeTarifsSpe = $listeTarifsSpe->loadList($where);

// Liste des praticiens du cabinet -> on ne doit pas voir les autres...
$listPrat = in_array($mediuser->_user_type, array("Administrator", "Secr�taire")) ?
  $mediuser->loadPraticiens(PERM_READ) :
  array($mediuser->_id => $mediuser);
  
// Cr�ation du template
$smarty = new CSmartyDP();


$smarty->assign("filter", $filter);
$smarty->assign("mediuser", $mediuser);
$smarty->assign("listeTarifsChir", $listeTarifsChir);
$smarty->assign("listeTarifsSpe", $listeTarifsSpe);
$smarty->assign("tarif", $tarif);
$smarty->assign("is_praticien", $is_praticien);
$smarty->assign("listPrat", $listPrat);

$smarty->display("vw_compta.tpl");

