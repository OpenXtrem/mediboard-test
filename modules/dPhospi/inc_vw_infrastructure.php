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

CCanDo::checkAdmin();

$use_uf         = CValue::get("uf_id");
$uf_id          = CValue::getOrSession("uf_id");
$use_prestation = CValue::get("prestation_id");
$prestation_id  = CValue::getOrSession("prestation_id");
$group = CGroups::loadCurrent();

// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);
$praticiens = CAppUI::$user->loadPraticiens();

if ($use_uf != null) {
  // Chargement de l'uf � ajouter/�diter
  $uf = new CUniteFonctionnelle();
  $uf->group_id = $group->_id;
  $uf->load($uf_id);
  $uf->loadRefUm();
  $uf->loadRefsNotes();

  // R�cup�ration des Unit�s M�dicales (pmsi)
  $ums = array ();
  $ums_infos = array ();
  $um  = new CUniteMedicale();

  if (CSQLDataSource::get("sae") && CModule::getActive("atih")) {
    $um_infos  = new CUniteMedicaleInfos();
    $ums = $um->loadListUm();
    $group = CGroups::loadCurrent();
    $where["group_id"] = " = '$group->_id'";
    $where["mode_hospi"] = " IS NOT NULL";
    $where["nb_lits"] = " IS NOT NULL";
    $ums_infos = $um_infos->loadList($where);
  }

  // R�cup�ration des ufs
  $order = "group_id, code";
  $ufs = $uf->loadList(null, $order);
}

if ($use_prestation != null) {
  // Chargement de la prestation � ajouter/�diter
  $prestation = new CPrestation();
  $prestation->group_id = $group->_id;
  $prestation->load($prestation_id);
  $prestation->loadRefsNotes();
  
  // R�cup�ration des prestations
  $order = "group_id, nom";
  /** @var CPrestation[] $prestations */
  $prestations = $prestation->loadList(null, $order);
  foreach ($prestations as $_prestation) {
    $_prestation->loadRefGroup();
    $_prestation->loadRefsNotes();
  }
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("praticiens"    , $praticiens);
$smarty->assign("etablissements", $etablissements);

if ($use_uf != null) {
  $smarty->assign("uf", $uf);
  $smarty->assign("ums", $ums);
  $smarty->assign("ums_infos", $ums_infos);
  $smarty->display("inc_vw_uf.tpl");
}
elseif ($use_prestation != null) {
  $smarty->assign("prestation", $prestation);
  $smarty->display("inc_vw_prestation.tpl");
}