<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage mediusers
* @version $Revision: $
* @author Sébastien Fillonneau
*/
global $AppUI, $m;


$mediuser = new CMediusers();
$mediuser->load($AppUI->user_id);
$mediuser->loadRefsFwd();

// Récupération des disciplines
$disciplines = new CDiscipline;
$disciplines = $disciplines->loadList();

// Chargement de la liste des banques
$banque = new CBanque();
$banques = $banque->loadList();

// Récupération des spécialités CPAM
$spec_cpam = new CSpecCPAM();
$spec_cpam = $spec_cpam->loadList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("banques"     , $banques                );
$smarty->assign("disciplines" , $disciplines            );
$smarty->assign("spec_cpam"   , $spec_cpam              );
$smarty->assign("user"        ,$mediuser                );
$smarty->assign("fonction"    , $mediuser->_ref_function);

$smarty->display("edit_infos.tpl");
?>