<?php
/**
 * $Id:$
 *
 * @package    Mediboard
 * @subpackage dPfacturation
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision:$
 */

CCanDo::checkEdit();
$chir_id    = CValue::getOrSession("chir_id");
$traitement = CValue::get("traitement", 0);
$list       = CValue::get("list", 0);

if ($traitement && $chir_id) {
  CFactureRejet::traitementDossier($chir_id);
}

// Liste des chirurgiens
$user = new CMediusers();
$listChir =  $user->loadPraticiens(PERM_EDIT);

//Listing des fichiers
$count_files = 0;
$files       = array();
$erreur      = null;
$fs_source_reception = CExchangeSource::get("reception-tarmed-CMediusers-$chir_id", "file_system", true, null, false);
if ($fs_source_reception->_id && $fs_source_reception->active) {
  $count_files = CMbPath::countFiles($fs_source_reception->host);
  if ($count_files < 1000) {
    try {
      $files = $fs_source_reception->receive();
    } catch (CMbException $e) {
      $erreur = CAppUI::tr($e->getMessage());
    }
  }
}

$rejet = new CFactureRejet();
$rejet->praticien_id = $chir_id;
$rejet->file_name   = CValue::getOrSession("file_name");
$rejet->num_facture = CValue::getOrSession("num_facture");
$rejet->date        = CValue::getOrSession("date");
$rejet->motif_rejet = CValue::getOrSession("motif_rejet");
$rejet->statut      = CValue::getOrSession("statut");
$rejet->name_assurance= CValue::getOrSession("name_assurance");

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("listChir"            , $listChir);
$smarty->assign("chir_id"             , $chir_id);
$smarty->assign("fs_source_reception" , $fs_source_reception);
$smarty->assign("count_files"         , $count_files);
$smarty->assign("files"               , $files);
$smarty->assign("erreur"              , $erreur);
$smarty->assign("rejet"               , $rejet);

if ($list) {
  $smarty->display("vw_list_file_rejet.tpl");
}
else {
  $smarty->display("vw_rejects_xml.tpl");
}