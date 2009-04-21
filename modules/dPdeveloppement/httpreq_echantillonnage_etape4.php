<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPdeveloppement
* @version $Revision$
* @author S�bastien Fillonneau
*/

global $AppUI, $m;

$_nb_services = mbGetValueFromGet("_nb_services", null);

$list_5 = CMbArray::createRange(1,5, true);
$list_14 = CMbArray::createRange(1,14, true);
$list_20 = CMbArray::createRange(1,20, true);

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("_nb_services" , $_nb_services);
$smarty->assign("list_5"       , $list_5);
$smarty->assign("list_14"      , $list_14);
$smarty->assign("list_20"      , $list_20);

$smarty->display("inc_echantillonnage_etape4.tpl");
?>