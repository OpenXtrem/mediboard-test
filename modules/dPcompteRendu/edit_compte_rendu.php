<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPcompteRendu
* @version $Revision$
* @author Romain OLLIVIER
*/

CCanDo::checkEdit();

$compte_rendu_id = CValue::get("compte_rendu_id"   , 0);
$modele_id       = CValue::get("modele_id"         , 0);
$praticien_id    = CValue::get("praticien_id"      , 0);
$type            = CValue::get("type"              , 0);
$pack_id         = CValue::get("pack_id"           , 0);
$object_id       = CValue::get("object_id"         , 0);
$switch_mode     = CValue::get("switch_mode"       , 0);
$target_id       = CValue::get("target_id");
$target_class    = CValue::get("target_class");

// Faire ici le test des diff�rentes variables dont on a besoin

$compte_rendu = new CCompteRendu;

// Modification d'un document
if ($compte_rendu_id) {
  $compte_rendu->load($compte_rendu_id);
  $compte_rendu->loadContent();
  $compte_rendu->loadFile();
}

// Cr�ation � partir d'un mod�le
else {
	$header = null;
	$footer = null;
	
  $compte_rendu->load($modele_id);
	$compte_rendu->loadFile();
  $compte_rendu->loadContent();
  $compte_rendu->_id = null;
  $compte_rendu->chir_id = $praticien_id;
  $compte_rendu->function_id = null;
  $compte_rendu->object_id = $object_id;
  $compte_rendu->_ref_object = null;

  // Utilisation des headers/footers
  if ($compte_rendu->header_id || $compte_rendu->footer_id) {
    $compte_rendu->loadComponents();
    
		$header = $compte_rendu->_ref_header;
    $footer = $compte_rendu->_ref_footer;
  }
  
  // On fournit la cible
  if ($target_id && $target_class){
    $compte_rendu->object_id = $target_id;
    $compte_rendu->object_class = $target_class;
  }
  
  // A partir d'un pack
  if ($pack_id) {
    $pack = new CPack;
    $pack->load($pack_id);
    
    $pack->loadContent();
    $compte_rendu->nom = $pack->nom;
    $compte_rendu->_is_pack = true;
    $compte_rendu->object_class = $pack->object_class;
    $compte_rendu->_source = $pack->_source;
    
    // Parcours des modeles du pack pour trouver le premier header et footer
    foreach($pack->_back['modele_links'] as $mod) {
    	if ($mod->_ref_modele->header_id || $mod->_ref_modele->footer_id) {
    		$mod->_ref_modele->loadComponents();
    	}
    	if (!isset($header)) $header = $mod->_ref_modele->_ref_header;
    	if (!isset($footer)) $footer = $mod->_ref_modele->_ref_footer;
    	if ($header && $footer) break;
    }

    // Marges et format
    $first_modele = reset($pack->_back['modele_links']);
    $compte_rendu->_ref_header   = $header;
    $compte_rendu->_ref_footer   = $footer;
    $compte_rendu->margin_top    = $first_modele->_ref_modele->margin_top;
    $compte_rendu->margin_left   = $first_modele->_ref_modele->margin_left;
    $compte_rendu->margin_right  = $first_modele->_ref_modele->margin_right;
    $compte_rendu->margin_bottom  = $first_modele->_ref_modele->margin_bottom;
    $compte_rendu->page_height   = $first_modele->_ref_modele->page_height;
    $compte_rendu->page_width    = $first_modele->_ref_modele->page_width;

  }
  $compte_rendu->_source = $compte_rendu->generateDocFromModel();
  $compte_rendu->updateFormFields();
}

$compte_rendu->loadRefsFwd();
$compte_rendu->_ref_object->loadRefsFwd();
$object =& $compte_rendu->_ref_object;

// Calcul du user concern�
$user = CAppUI::$user;

// Chargement dans l'ordre suivant pour les listes de choix si null :
// - user courant
// - anesth�siste
// - praticien de la consultation

if (!$user->isPraticien()) {
  if ($object instanceof CConsultAnesth) {
    $object->loadRefOperation();
    $user->_id = $object->_ref_operation->_ref_anesth->user_id;
    if ($user->_id == null)
      $user->_id = $object->_ref_consultation->_praticien_id;
  }
  if ($object instanceof CCodable) {
    $user->_id = $object->_praticien_id;
  }
}

$user->load();
$user->loadRefFunction();

// Chargement des cat�gories
$listCategory = CFilesCategory::listCatClass($compte_rendu->object_class);

// Gestion du template
$templateManager = new CTemplateManager($_GET);
$templateManager->isModele = false;
$object->fillTemplate($templateManager);
$templateManager->document = $compte_rendu->_source;
$templateManager->loadHelpers($user->_id, $compte_rendu->object_class);
$templateManager->loadLists($user->_id);
$templateManager->applyTemplate($compte_rendu);

$where = array();
$where[] = "(
  chir_id = '$user->_id' OR 
  function_id = '$user->function_id' OR 
  group_id = '{$user->_ref_function->group_id}'
)";
$order = "chir_id, function_id, group_id";
$chirLists = new CListeChoix;
$chirLists = $chirLists->loadList($where, $order);
$lists = $templateManager->getUsedLists($chirLists);

// R�cup�ration des �l�ments de destinataires de courrier
$isCourrier = $templateManager->isCourrier();
$destinataires = array();
if($isCourrier) {
  CDestinataire::makeAllFor($object);
  $destinataires = CDestinataire::$destByClass;
}

$noms_textes_libres = array();
// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("listCategory"   , $listCategory);
$smarty->assign("compte_rendu"   , $compte_rendu);
$smarty->assign("modele_id"      , $modele_id);
$smarty->assign("lists"          , $lists);
$smarty->assign("destinataires"  , $destinataires);
$smarty->assign("user_id"        , $user->_id);

$smarty->assign("object_id"    , $object_id);
$smarty->assign('object_class' , CValue::get("object_class"));

preg_match_all("/(:?\[\[Texte libre - ([^\]]*)\]\])/i",$compte_rendu->_source, $matches);
$noms_textes_libres = $matches[2];

if (isset($compte_rendu->_ref_file->_id)) {
  $smarty->assign("file", $compte_rendu->_ref_file);
}

$smarty->assign("noms_textes_libres", $noms_textes_libres);

if (CValue::get("reloadzones") == 1) {
  $smarty->display("inc_zones_fields.tpl");
}
else if ($compte_rendu->fast_edit && !$compte_rendu_id && !$switch_mode) {
  $smarty->assign("_source", $templateManager->document);
	$smarty->assign("object_guid", CValue::get("object_guid"));
  $smarty->assign("unique_id"       , CValue::get("unique_id"));
  $smarty->display("fast_mode.tpl");
}
else {
  $templateManager->initHTMLArea();
  $smarty->assign("switch_mode", CValue::get("switch_mode", 0));
  $smarty->assign("templateManager", $templateManager);
  $smarty->display("edit_compte_rendu.tpl");
}
?>