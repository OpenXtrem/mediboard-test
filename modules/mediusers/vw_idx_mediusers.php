<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage mediusers
* @version $Revision$
* @author Romain Ollivier
*/

global $AppUI, $can, $m, $utypes;

$can->needsRead();

// Récupération du user à ajouter/editer
$mediuserSel = new CMediusers;
$mediuserSel->load(mbGetValueFromGetOrSession("user_id"));

// Chargement des banques
$orderBanque = "nom ASC";
$banque = new CBanque();
$banques = $banque->loadList(null,$orderBanque);

// Récupération des fonctions
$groups = new CGroups;
$order = "text";
$groups = $groups->loadList(null, $order);
foreach ($groups as $key => $group) {
  $groups[$key]->loadRefsBack();
  foreach($groups[$key]->_ref_functions as $keyFct => $function){
    // Récuperation des utilisateurs
    $groups[$key]->_ref_functions[$keyFct]->loadRefs();
  }
}

// Récupération des disciplines
$disciplines = new CDiscipline;
$disciplines = $disciplines->loadList();

// Récupération des spécialités CPAM
$spec_cpam = new CSpecCPAM();
$spec_cpam = $spec_cpam->loadList();
  
// Récupération des profils
$where = array (
  //"user_username" => "LIKE '>> %'"
    "template" => "= '1'"
);
$profiles = new CUser();
$profiles = $profiles->loadList($where);

// Creation du tableau de profil en fonction du type
foreach($profiles as $key => $profil){
  $tabProfil[$profil->user_type][] = $profil->_id;
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("tabProfil"    , $tabProfil    );
$smarty->assign("utypes"       , $utypes       );
$smarty->assign("banques"      , $banques      );
$smarty->assign("mediuserSel"  , $mediuserSel  );
$smarty->assign("profiles"     , $profiles     );
$smarty->assign("groups"       , $groups       );
$smarty->assign("disciplines"  , $disciplines  );
$smarty->assign("spec_cpam"    , $spec_cpam    );

$smarty->display("vw_idx_mediusers.tpl");

?>