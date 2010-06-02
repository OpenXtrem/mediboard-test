<?php /* $Id:  $ */

/**
 * @package Mediboard
 * @subpackage ssr
 * @version $Revision:  $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

global $AppUI;

$prescription_id = CValue::get("prescription_id");
$category_id = CValue::get("category_id");
$full_line_id = CValue::get("full_line_id");

$line = new CPrescriptionLineElement();
$order = "debut ASC";
$ljoin["element_prescription"] = "prescription_line_element.element_prescription_id = element_prescription.element_prescription_id";
$where["prescription_id"] = " = '$prescription_id'";
$where["element_prescription.category_prescription_id"] = " = '$category_id'";
$lines[$category_id] = $line->loadList($where, $order, null, null, $ljoin);

$can_edit_prescription = $AppUI->_ref_user->isPraticien() || $AppUI->_ref_user->isAdmin();

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("full_line_id", $full_line_id);
$smarty->assign("lines", $lines);
$smarty->assign("category_id", $category_id);
$smarty->assign("nodebug", true);
$smarty->assign("can_edit_prescription", $can_edit_prescription);
$smarty->display("inc_list_lines.tpl");

?>