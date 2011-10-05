<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPpatients
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

$patient_id = CValue::get("patient_id");

$patient = new CPatient;
$patient->load($patient_id);
$patient->loadRefsCorrespondantsPatient();

$smarty = new CSmartyDP;
$smarty->assign("correspondants_by_relation", $patient->_ref_cp_by_relation);
$smarty->assign("nb_correspondants", count($patient->_ref_correspondants_patient));
$smarty->display("inc_list_correspondants.tpl");

?>