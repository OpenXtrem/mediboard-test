<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPhospi
* @version $Revision$
* @author Fabien M�nager
*/

global $can;

$const_id = CValue::get('const_id', 0);
$context_guid = CValue::get('context_guid');
$readonly = CValue::get('readonly');

$constantes = new CConstantesMedicales();
$constantes->load($const_id);
$constantes->loadRefContext();
$constantes->loadRefPatient();

$latest_constantes = CConstantesMedicales::getLatestFor($constantes->patient_id);

// Tableau contenant le nom de tous les graphs
$graphs = array();
foreach(CConstantesMedicales::$list_constantes as $cst) {
  $graphs[] = "constantes-medicales-$cst";
}
                 
// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign('constantes', $constantes);
$smarty->assign('latest_constantes', $latest_constantes);
$smarty->assign('context_guid', $context_guid);
$smarty->assign('graphs', $graphs);
$smarty->assign('readonly', $readonly);
$smarty->display('inc_form_edit_constantes_medicales.tpl');

?>