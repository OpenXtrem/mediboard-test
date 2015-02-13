<?php 

/**
 * $Id$
 *  
 * @category Drawing
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

CCanDo::checkAdmin();

$user = new CMediusers();
$user_id = CValue::get("user_id");
$function_id = CValue::get("function_id");

// current user, if no user & no function
if (!$user_id && !$function_id) {
  $user_id = CMediusers::get()->_id;
}
$user->load($user_id);
$user->_ref_drawing_cat = $user->loadBackRefs('drawing_categories');

/** @var CDrawingCategory $_cat */
foreach ($user->_ref_drawing_cat as $_cat) {
  $_cat->loadRefsFiles();
}


// function
$functions = array();
if (!$function_id && $user->_id) {
  $function_id = $user->function_id;
  $functions = $user->loadRefsSecondaryFunctions();
}
$function = new CFunctions();
$function->load($function_id);
$functions[$function->_id] = $function;
foreach ($functions as $_function) {
  $_function->_ref_drawing_cat = $_function->loadBackRefs('drawing_categories');
  /** @var CDrawingCategory $_cat */
  foreach ($_function->_ref_drawing_cat as $_cat) {
    $_cat->loadRefsFiles();
  }
}


// group
$group = $function->loadRefGroup();
$group->_ref_drawing_cat = $group->loadBackRefs('drawing_categories');
/** @var CDrawingCategory $_cat */
foreach ($group->_ref_drawing_cat as $_cat) {
  $_cat->loadRefsFiles();
}


// smarty
$smarty = new CSmartyDP();
$smarty->assign("user", $user);
$smarty->assign("functions", $functions);
$smarty->assign("group", $group);
$smarty->assign("category", new CDrawingCategory());
$smarty->display("inc_list_ressources.tpl");