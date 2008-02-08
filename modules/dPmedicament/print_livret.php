<?php

/**
* @package Mediboard
* @subpackage dPmedicament
* @version $Revision: $
* @author Alexis Granger
*/

global $g;

// Chargement de l'etablissement courant
$etablissement = new CGroups();
$etablissement->load($g);

// Chargement des produits du livret therapeutique
$etablissement->loadRefLivretTherapeutique();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("date", mbDate());
$smarty->assign("produits_livret", $etablissement->_ref_produits_livret);

$smarty->display("print_livret.tpl");


?>