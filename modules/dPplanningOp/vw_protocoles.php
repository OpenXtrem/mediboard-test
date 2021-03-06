<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage PlanningOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

global $dialog;
if ($dialog) {
  CCanDo::checkRead();
}
else {
  CCanDo::checkEdit();
}

$singleType = CValue::get("singleType");

// L'utilisateur est-il chirurgien ?
$mediuser      = CMediusers::get();
$is_praticien  = $mediuser->isPraticien();
$listPrat      = $mediuser->loadPraticiens(PERM_READ);
$chir_id       = CAppUI::conf("dPplanningOp COperation use_session_praticien")
  ? CValue::getOrSession("chir_id", $is_praticien ? $mediuser->user_id : reset($listPrat)->_id)
  : CValue::get("chir_id", $is_praticien ? $mediuser->user_id : reset($listPrat)->_id);
$function_id   = CValue::getOrSession("function_id");
$_function     = new CFunctions();
$listFunc      = $_function->loadSpecialites(PERM_READ);
$type          = CValue::getOrSession("type", "interv");
$sejour_type   = CValue::get("sejour_type");
$page          = CValue::get("page", array(
    "sejour" => 0,
    "interv" => 0)
);

// Protocoles disponibles
$_prat = new CMediusers();
foreach ($listPrat as $_prat) {
  $_prat->countProtocoles($sejour_type);
}
foreach ($listFunc as $_function) {
  $_function->countProtocoles($sejour_type);
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("singleType" , $singleType);
$smarty->assign("page"        , $page);
$smarty->assign("listPrat"    , $listPrat);
$smarty->assign("listFunc"    , $listFunc);
$smarty->assign("chir_id"     , $chir_id);
$smarty->assign("mediuser"    , $mediuser);
$smarty->assign("sejour_type" , $sejour_type);
$smarty->assign("is_praticien", $is_praticien);
$smarty->assign("function_id" , $function_id);
$smarty->display("vw_protocoles.tpl");
