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

CCanDo::checkEdit();

$sejour_id         = CValue::get("sejour_id");
$operation_id      = CValue::get("operation_id");
$operateur_ids     = CValue::get("operateur_ids");

if (!is_array($operateur_ids)) {
  $operateur_ids = explode("-", $operateur_ids);
  CMbArray::removeValue("", $operateur_ids);
}

if (count($operateur_ids)) {
  $operateur = new CMediusers;
  $where = array(
    "user_id" => "IN(" . implode(",", $operateur_ids) . ")",
  );
  $operateurs = $operateur->loadList($where);
}
else {
  $operateurs = array();
}

$poses = array();
if ($operation_id) {
  $interv = new COperation;
  $interv->load($operation_id);
  $poses = $interv->loadRefsPosesDispVasc(true);
}
elseif ($sejour_id) {
  $sejour = new CSejour;
  $sejour->load($sejour_id);
  $poses = $sejour->loadRefsPosesDispVasc(true);
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("poses", $poses);
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("operation_id", $operation_id);
$smarty->assign("operateur_ids", $operateur_ids);

$smarty->display("inc_list_pose_disp_vasc.tpl");
