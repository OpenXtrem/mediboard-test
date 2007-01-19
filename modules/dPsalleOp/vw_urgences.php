<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage dPsalleOp
* @version $Revision: 331 $
* @author Romain Ollivier
*/

global $AppUI, $canRead, $canEdit, $m;

if (!$canRead) {
  $AppUI->redirect("m=system&a=access_denied");
}

$date  = mbGetValueFromGetOrSession("date", mbDate());

$urgences      = new COperation;
$where         = array();
$where["date"] = "= '".$date."'";
$order         = "salle_id, chir_id";
$urgences      = $urgences->loadList($where, $order);
foreach($urgences as $keyOp => $op) {
  $urgences[$keyOp]->loadRefsFwd();
  $urgences[$keyOp]->_ref_sejour->loadRefPatient();
}

$listSalles = new CSalle;
$order = "nom";
$listSalles = $listSalles->loadList(null, $order);

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->debugging = false;

$smarty->assign("urgences"  , $urgences);
$smarty->assign("listSalles", $listSalles);
$smarty->assign("date",$date);

$smarty->display("vw_urgences.tpl");

?>