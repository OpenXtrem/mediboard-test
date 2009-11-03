<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPqualite
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

$fiche_ei_id = CValue::getOrSession("fiche_ei_id",null);
$catFiche = array();

$fiche = new CFicheEi;
if(!$fiche->load($fiche_ei_id)){
  // Cette fiche n'est pas valide
  $fiche_ei_id = null;
  CValue::setSession("fiche_ei_id");
  $fiche = new CFicheEi;
}else{
  $fiche->loadRefsFwd();
  $fiche->loadRefItems();
  
  // Liste des Cat�gories d'EI
  $listCategories = new CEiCategorie;
  $listCategories = $listCategories->loadList(null, "nom");

  foreach($listCategories as $keyCat=>$valueCat){
    foreach($fiche->_ref_items as $keyItem=>$valueItem){
      if($fiche->_ref_items[$keyItem]->ei_categorie_id==$keyCat){
        if(!isset($catFiche[$listCategories[$keyCat]->nom])){
          $catFiche[$listCategories[$keyCat]->nom] = array();
        }
        $catFiche[$listCategories[$keyCat]->nom][] = $fiche->_ref_items[$keyItem];
      }
    }
  }
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("catFiche" , $catFiche);
$smarty->assign("fiche"    , $fiche);

$smarty->display("print_fiche.tpl");
?>