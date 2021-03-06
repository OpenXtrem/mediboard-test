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

$service = new CService();
$service->group_id = CGroups::loadCurrent()->_id;
$service->cancelled = 0;
$order = "nom ASC";
$services = $service->loadMatchingList($order);

$sejour = new CSejour();
$types_admission = $sejour->_specs["type"]->_locales;

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("types_admission", $types_admission);
$smarty->assign("services"       , $services);
$smarty->display("configure.tpl");

