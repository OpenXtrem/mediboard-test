<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage System
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkRead();

$start     = CValue::get("start", 0);
$date_min  = CValue::getOrSession("date_min", CMbDT::dateTime("-2 MONTH"));
$date_max  = CValue::getOrSession("date_max");
$user_id   = CValue::getOrSession("user_id");
$user_agent_id = CValue::get("user_agent_id");

$auth = new CUserAuthentication();
$ua = new CUserAgent();

$where = array(
  "datetime_login" => ">= '$date_min'"
);
if ($date_max) {
  $where[] = "datetime_login <= '$date_min'";
}
if ($user_id) {
  $where["user_id"] = "<= '$user_id'";
}
if ($user_agent_id) {
  $where["user_agent_id"] = "= '$user_agent_id'";
  $ua->load($user_agent_id);
}

$limit = ((int)$start).",100";

/** @var CUserAuthentication[] $auth_list */
$auth_list = $auth->loadList($where, "datetime_login DESC", $limit);

foreach ($auth_list as $_auth) {
  $_auth->loadRefUser()->loadRefMediuser()->loadRefFunction();
}

$smarty = new CSmartyDP();
$smarty->assign("start", $start);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("user_id", $user_id);
$smarty->assign("auth_list", $auth_list);
$smarty->assign("ua", $ua);
$smarty->display("vw_user_authentications.tpl");
