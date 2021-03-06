<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage System
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkAdmin();

CModule::loadModules(false);

$setupClasses = CApp::getChildClasses("CSetup");
$mbmodules = array(
  "notInstalled" => array(),
  "installed" => array(),
);
$coreModules = array();
$upgradable = false;

foreach ($setupClasses as $setupClass) {
  if (!class_exists($setupClass)) {
    continue;
  }
  
  $setup = new $setupClass;
  $mbmodule = new CModule();
  $mbmodule->compareToSetup($setup);
  $mbmodule->checkModuleFiles();
  $mbmodule->getUpdateMessages($setup, true);
  $mbmodule->updateFormFields();

  if ($mbmodule->mod_ui_order == 1000) {
    $mbmodules["notInstalled"][$mbmodule->mod_name] = $mbmodule;
  }
  else {
    $mbmodules["installed"][$mbmodule->mod_name] = $mbmodule;
    if ($mbmodule->_upgradable) {
      $upgradable = true;
    }
  }
  if ($mbmodule->mod_type == "core" && $mbmodule->_upgradable) {
    $coreModules[$mbmodule->mod_name] = $mbmodule;
  }
}

foreach ($mbmodules as $typeModules) {
  foreach ($typeModules as $module) {
    foreach ($module->_dependencies as $version => $dependencies) {
      foreach ($dependencies as $dependency) {
        $installed = $mbmodules["installed"];

        $dependency->verified =
          isset($installed[$dependency->module]) &&
          $installed[$dependency->module]->mod_version >= $dependency->revision;

        if (!$dependency->verified) {
          $module->_dependencies_not_verified++;
        }
      }
    }
  }
}


// Ajout des modules install�s dont les fichiers ne sont pas pr�sents
if (count(CModule::$absent)) {
  $mbmodules["installed"] += CModule::$absent;
}

array_multisort(CMbArray::pluck($mbmodules["installed"   ], "mod_ui_order"), SORT_ASC, $mbmodules["installed"]);
array_multisort(CMbArray::pluck($mbmodules["notInstalled"], "_view"       ), SORT_ASC, $mbmodules["notInstalled"]);

$obsoleteLibs = array();
foreach (CLibrary::$all as $library) {
  if ($library->getUpdateState() != 1) {
    $obsoleteLibs[] = $library->name;
  }
}

$servers_ip = preg_split("/\s*,\s*/", CAppUI::conf("servers_ip"), -1, PREG_SPLIT_NO_EMPTY);

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("upgradable"  , $upgradable);
$smarty->assign("mbmodules"   , $mbmodules);
$smarty->assign("coreModules" , $coreModules);
$smarty->assign("obsoleteLibs", $obsoleteLibs);
$smarty->assign("servers_ip"  , $servers_ip);

$smarty->display("view_modules.tpl");