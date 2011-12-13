<?php /* $Id: vw_agenda.php $ */

/**
 * @package Mediboard
 * @subpackage dPbloc
 * @version $Revision: 7878 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

// Récupération des paramètres
$login        = CUser::get()->user_username;
$prat_id      = CUser::get()->_id;

  $url = CAppUI::conf("base_url")."/index.php?";
  $param = array();
  $param["m"]               = "dPboard";
  $param["a"]               = "export_ical";
  $param["suppressHeaders"] = "1";
 
  $url .= http_build_query($param, null, "&");

// Variables de templates
$smarty = new CSmartyDP();

$smarty->assign("prat_id"  , $prat_id);
$smarty->assign("login"       , $login);
$smarty->assign("url"         , $url);

$smarty->display("vw_agenda.tpl");
?>