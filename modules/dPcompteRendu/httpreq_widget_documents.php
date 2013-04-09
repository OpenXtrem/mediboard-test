<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision$
* @author Romain Ollivier
*/

$user = CMediusers::get();

$object_class = CValue::getOrSession("object_class");
$object_id    = CValue::getOrSession("object_id");
$user_id      = CValue::getOrSession("praticien_id");
$only_docs    = CValue::get("only_docs", 0);

// Chargement de l'objet cible
$object = new $object_class;
if (!$object instanceof CMbObject) {
  trigger_error("object_class should be an CMbObject", E_USER_WARNING);
  return;
}

$object->load($object_id);
if (!$object->_id) {
  trigger_error("object of class '$object_class' could not be loaded with id '$object_id'", E_USER_WARNING);
  return;
}

$object->canDo();

// Praticien concern�
if (!$user->isPraticien() && $user_id) {
  $user = new CMediusers();
  $user->load($user_id);
}

$user->loadRefFunction();
$user->_ref_function->loadRefGroup();
$user->canDo();

if ($object->loadRefsDocs()) {
  foreach($object->_ref_documents as $_doc) {
    $_doc->loadRefCategory();
    $_doc->canDo();
  }
}

// Compter les mod�les d'�tiquettes
$modele_etiquette = new CModeleEtiquette;

$where = array();

$where['object_class'] = " = '$object_class'";
$where["group_id"] = " = '".CGroups::loadCurrent()->_id."'";

$nb_modeles_etiquettes = $modele_etiquette->countList($where);

$nb_printers = 0;

if (CModule::getActive("printing")) {
  // Chargement des imprimantes pour l'impression d'�tiquettes 
  $user_printers = CMediusers::get();
  $function      = $user_printers->loadRefFunction();
  $nb_printers   = $function->countBackRefs("printers");
}

$compte_rendu = new CCompteRendu();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("praticien"     , $user);
$smarty->assign("object"        , $object);
$smarty->assign("mode"          , CValue::get("mode"));
$smarty->assign("notext"        , "notext");
$smarty->assign("nb_printers"   , $nb_printers);
$smarty->assign("nb_modeles_etiquettes", $nb_modeles_etiquettes);
$smarty->assign("can_doc", $compte_rendu->loadPermClass());

$smarty->display($only_docs ? "inc_widget_list_documents.tpl" : "inc_widget_documents.tpl");
