<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPfiles
* @version $Revision$
* @author S�bastien Fillonneau
*/

global $AppUI, $can, $m;

$can->needsAdmin();

$file_category_id = CValue::getOrSession("file_category_id");

// Chargement de la cat�gorie demand�
$category = new CFilesCategory;
$category->load($file_category_id);
$category->countDocItems();

// Liste des Cat�gories
$categories = $category->loadList(null, "class, nom");

// Liste des Classes disponibles
CAppUI::getAllClasses();
$listClass = getChildClasses();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("categories"  , $categories);
$smarty->assign("category"    , $category    );
$smarty->assign("listClass"   , $listClass   );

$smarty->display("vw_category.tpl");

?>