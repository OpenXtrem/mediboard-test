<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage dPpatient
* @version $Revision: $
* @author S�bastien Fillonneau
*/


global $AppUI, $canRead, $canEdit, $m;

do_connect($AppUI->cfg["baseINSEE"]);
$sql = null;

if($pays = @$_GET[$_GET["fieldpays"]]) {
  $sql = "SELECT nom_fr FROM pays" .
      "\nWHERE nom_fr LIKE '$pays%'" .
      "\nORDER BY nom_fr";
} 

if ($canRead && $sql) {
  $result = db_loadList($sql, 30, $AppUI->cfg["baseINSEE"]);
  // Cr�ation du template
  $smarty = new CSmartyDP();

  $smarty->assign("pays"  , $pays);
  $smarty->assign("result", $result);

  $smarty->display("httpreq_do_pays_autocomplete.tpl");
}
?>
