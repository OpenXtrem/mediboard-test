<?php

/**
 * @package Mediboard
 * @subpackage system
 * @version $Revision:  $
 * @author Poiron Yohann
 */

global $AppUI, $can, $m;

// only user_type of Administrator (1) can access this page
$can->edit |= ($AppUI->user_type != 1);
$can->needsEdit();

$module = mbGetValueFromGetOrSession("module" , "admin");

$tabClass = mbGetClassByModule($module);

// liste des dossiers modules + common et styles
$modules = array_merge( array("common"=>"common", "styles"=>"styles") ,$AppUI->readDirs("modules"));
mbRemoveValuesInArray(".svn", $modules);
ksort($modules);

// Dossier des traductions
$localesDirs = $AppUI->readDirs("locales");
mbRemoveValuesInArray(".svn",$localesDirs);
mbRemoveValuesInArray("en",$localesDirs);

// R�cup�ration du fichier demand� pour toutes les langues
$translateModule = new CMbConfig;
$translateModule->sourcePath = null;
$contenu_file = array();
foreach($localesDirs as $locale){
  $translateModule->options = array("name" => "locales");
  $translateModule->targetPath = "locales/fr/$modules[$module].php";
  $translateModule->load();
  $contenu_file[$locale] = $translateModule->values;
}

// R�attribution des cl�s et organisation
$trans = array();
foreach($localesDirs as $locale){
	foreach($contenu_file[$locale] as $k=>$v){
		$trans[ (is_int($k) ? $v : $k) ][$locale] = $v;
	}
}

$backSpecs = array();
$backRefs = array();
foreach($tabClass as $selected) {
  $object = new $selected;
  $ref_modules = $object->_specs;
  $classname = $object->_class_name;
  foreach ($object->_props as $keyObjetRefSpec => $valueObjetRefSpec) { 
  	$backSpecs[$object->_class_name][$classname][$classname] = !array_key_exists($classname,$trans) ? '' : $trans[$classname]["fr"];
  	$backSpecs[$object->_class_name][$classname][$classname.".one"] = !array_key_exists($classname.".one",$trans) ? '' : $trans[$classname.".one"]["fr"];
  	$backSpecs[$object->_class_name][$classname][$classname.".more"] = !array_key_exists($classname.".more",$trans) ? '' : $trans[$classname.".more"]["fr"];
  	$backSpecs[$object->_class_name][$classname][$classname.".none"] = !array_key_exists($classname.".none",$trans) ? '' : $trans[$classname.".none"]["fr"];
  	$backSpecs[$object->_class_name][$classname][$classname.".create"] = !array_key_exists($classname.".create",$trans) ? '' : $trans[$classname.".create"]["fr"];
  	$backSpecs[$object->_class_name][$classname][$classname.".modify"] = !array_key_exists($classname.".modify",$trans) ? '' : $trans[$classname.".modify"]["fr"];
  	$backSpecs[$object->_class_name][$keyObjetRefSpec][$classname."-".$keyObjetRefSpec] = !array_key_exists($classname."-".$keyObjetRefSpec,$trans) ? '' : $trans[$classname."-".$keyObjetRefSpec]["fr"];  	
  	$backSpecs[$object->_class_name][$keyObjetRefSpec][$classname."-".$keyObjetRefSpec."-desc"] = !array_key_exists($classname."-".$keyObjetRefSpec."-desc",$trans) ? '' : $trans[$classname."-".$keyObjetRefSpec."-desc"]["fr"];
  	$backSpecs[$object->_class_name][$keyObjetRefSpec][$classname."-".$keyObjetRefSpec."-court"] = !array_key_exists($classname."-".$keyObjetRefSpec."-court",$trans) ? '' : $trans[$classname."-".$keyObjetRefSpec."-court"]["fr"];
  }
  foreach ($object->_enums as $keyObjetEnum => $valueObjetEnum) { 
  	//if(is_a($keyObjetEnum,"CBoolSpec")) { //prise en compte des valeurs booleennes
  		foreach ($valueObjetEnum as $key => $_item) { 
  			$backSpecs[$object->_class_name][$keyObjetEnum][$classname.".".$keyObjetEnum.".".$_item] = !array_key_exists($classname.".".$keyObjetEnum.".".$_item,$trans) ? '' : $trans[$classname.".".$keyObjetEnum.".".$_item]["fr"];
  		}
  	//}
  }
   foreach ($object->_specs as $objetRefSpec) {
    if (is_a($objetRefSpec, 'CRefSpec')) {
        $spec = array();
        $fieldName = $objetRefSpec->fieldName;
      	$backSpecs[$object->_class_name][$classname][$classname."-back-".$fieldName] = !array_key_exists($classname."-back-".$fieldName,$trans) ? '' : $trans[$classname."-back-".$fieldName]["fr"];
    }
  }
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("locales"  		, $localesDirs);
$smarty->assign("modules"  		, $modules);
$smarty->assign("module"   		, $module);
$smarty->assign("trans"    		, $trans);
$smarty->assign("backSpecs"    	, $backSpecs);

$smarty->display("mnt_traduction_classes.tpl");
?>