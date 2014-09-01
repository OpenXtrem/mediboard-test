<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage developpement
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkRead();

$page = CValue::get("page");
$url = "http://www.mediboard.org/public/$page";
$headers = get_headers($url);
$header = $headers[0];
preg_match('|\d\d\d|', $header, $matches);

switch ($http_code = $matches[0]) {
  case "200": 
  case "302":
    CAppUI::stepAjax("Page %s found (HTTP %s)", UI_MSG_OK, $page, $http_code);
    break;
  
  case "404":
    CAppUI::stepAjax("Page %s not found (HTTP %s)", UI_MSG_ERROR, $page, $http_code);
    break;
    
  default:
    CAppUI::stepAjax("Page %s has other response (HTTP %s)", UI_MSG_WARNING, $page, $http_code);
    break;
}
