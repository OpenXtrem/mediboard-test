<?php /* $Id: $ */

/**
 * @package Mediboard
 * @subpackage admin
 * @version $Revision: 7138 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

// Column 
$columns = array("code_postal", "commune");
$column = CValue::get("column");
if (!in_array($column, $columns)) {
  trigger_error("Column '$column' is invalid");
  return;
}

// Parameters
$ds      = CSQLDataSource::get("INSEE");
$max     = CValue::get("max", 30);
$nbPays  = 0;
$matches = array();

// Needle
$keyword = reset($_POST);
$needle  = $column == "code_postal" ? "$keyword%" : "%$keyword%";

// Query
$where       = "WHERE $column LIKE '$needle'";

// France
if(CAppUI::conf("dPpatients INSEE france")) {
  $nbPays++;
  $queryFrance = "SELECT commune, code_postal, departement, 'France' AS pays FROM communes_france $where";
}

// Suisse
if(CAppUI::conf("dPpatients INSEE suisse")) {
  $nbPays++;
  $querySuisse = "SELECT commune, code_postal, '' AS departement, 'Suisse' AS pays FROM communes_suisse $where";
}

if(CAppUI::conf("dPpatients INSEE france")) {
  $matches += $ds->loadList($queryFrance, intval($max/$nbPays));
}
if(CAppUI::conf("dPpatients INSEE suisse")) {
  $matches += $ds->loadList($querySuisse, intval($max/$nbPays));
}

array_multisort(CMbArray::pluck($matches, "code_postal"), SORT_ASC, CMbArray::pluck($matches, "commune"), SORT_ASC, $matches);

// Template
$smarty = new CSmartyDP();

$smarty->assign("keyword", $keyword);
$smarty->assign("matches", $matches);
$smarty->assign("nodebug", true);
	
$smarty->display("autocomplete_cp_commune.tpl");