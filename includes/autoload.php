<?php 
/**
 * Autoload strategies
 *
 * PHP version 5.1.x+
 *  
 * @category   Dispatcher
 * @package    Mediboard
 * @subpackage Includes
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    SVN: $Id$ 
 * @link       http://www.mediboard.org
 */

global $performance;
$performance["autoload"] = 0;

// Load class paths in shared memory
if (null == $classPaths = SHM::get("class-paths")) {
  updateClassPathCache();
}

/** 
 * Updates the PHP classes paths cache 
 * 
 * @return void
 */
function updateClassPathCache() {
  // debut de refactoring pour ne pas vider le cache a chaque fois, mais le completer
  /*$classPaths = SHM::get("class-paths");

  // unset obsolete paths
  if ($classPaths) {
    foreach ($classPaths as $className => $path) {
      if (!file_exists($path)) {
        unset($classPaths[$className]);
      }
    }
  }*/
  
  // update paths
  $classNames = CApp::getChildClasses(null);
  foreach ($classNames as $className) {
    $class = new ReflectionClass($className);
    $classPaths[$className] = $class->getFileName();
  }
  
  SHM::put("class-paths", $classPaths);
}

/**
 * Mediboard autoload strategy
 * 
 * @param string $className Class to be loaded
 * 
 * @return bool  
 */
function mbAutoload($className) {
  global $classPaths, $performance;
  
  if (isset($classPaths[$className])) {
    if ($classPaths[$className] === false) return false;
    
    if (file_exists($classPaths[$className])) {
      $performance["autoload"]++;
      return include_once $classPaths[$className];
    }
    else {
      $classPaths[$className] = false;
      SHM::put("class-paths", $classPaths);
    }
  }
  else {
    /*
    $contexts = debug_backtrace();
    foreach($contexts as &$ctx) {
      unset($ctx['args']);
      unset($ctx['object']);
    }
    mbTrace($contexts, $className, true );
    */
    updateClassPathCache();
  }
  
  return true;
}

// nouveau mode
function mbAutoload2($class_name) {
  global $classPaths, $performance;
  
  $file_exists = false;
  
  // entry already in cache
  if (isset($classPaths[$class_name]) && ($file_exists = file_exists($classPaths[$class_name]))) {
    $performance["autoload"]++;
    return include_once $classPaths[$class_name];
  }
  else {
    if (!$file_exists) {
      unset($classPaths[$class_name]);
    }
    
    $dirs = array(
      "classes/$class_name.class.php", 
      "*/*/$class_name.class.php",
      "modules/*/classes/$class_name.class.php", // Require all modules classes
    );
    
    if (preg_match("/^CSetup.+/", $class_name)) {
      $dirs[] = "modules/*/setup.php"; // Require all setup classes
    }
    
    $rootDir = CAppUI::conf("root_dir");
    
    foreach ($dirs as $dir) {
      $files = glob("$rootDir/$dir");
      foreach ($files as $filename) {
        require_once($filename);
      }
    }
    
    // update the cache
    if (class_exists($class_name, false)) {
      $class = new ReflectionClass($class_name);
      $classPaths[$class_name] = $class->getFileName();
      SHM::put("class-paths", $classPaths);
    }
    else {
      return false;
    }
  }
  
  return true;
}

if (function_exists("spl_autoload_register")) {
  //spl_autoload_register("mbAutoload2");
  spl_autoload_register("mbAutoload");
}
else {
  /**
   * Autoload magic function redefinition
   * 
   * @param string $className Class to be loaded
   * 
   * @return bool
   */
  function __autoload($className) {
    return mbAutoload($className);
  }
}
