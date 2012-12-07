<?php 
/**
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage classes
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    $Revision$
 */

// Use $dPconfig for both application and install wizard to use it
global $dPconfig, $rootName;
require_once "{$dPconfig['root_dir']}/classes/CMbPath.class.php";

/**
 * Shared Memory interface
 */
interface ISharedMemory {

  /**
   * Initialize the shared memory
   * Returns true if shared memory is available
   *
   * @return bool
   */
  function init();

  /**
   * Get a variable from shared memory
   *
   * @param string $key Key of value to retrieve
   *
   * @return mixed the value, null if failed
   */
  function get($key);

  /**
   * Put a variable into shared memory
   *
   * @param string $key   Key of value to store
   * @param mixed  $value The value
   *
   * @return void
   */
  function put($key, $value);

  /**
   * Remove a variable from shared memory
   *
   * @param string $key Key of value to remove
   *
   * @return bool job-done
   */
  function rem($key);

  /**
   * Clears the shared memory
   *
   * @return bool job-done
   */
  //function clear();

  /**
   * Return the list of keys
   *
   * @param string $prefix The keys' prefix
   *
   * @return array Keys list
   */
  function listKeys($prefix);
}

/**
 * Disk based shared memory
 */
class DiskSharedMemory implements ISharedMemory {
  private $dir = null;

  function __construct() {
    global $dPconfig;
    $this->dir = "{$dPconfig['root_dir']}/tmp/shared/";
  }

  function init() {
    if (!CMbPath::forceDir($this->dir)) {
      trigger_error("Shared memory could not be initialized, ensure that '$this->dir' is writable");
      CApp::rip();
    }
    return true;
  }

  function get($key) {
    if (file_exists($this->dir.$key)) {
      return unserialize(file_get_contents($this->dir.$key));
    }
    return false;
  }

  function put($key, $value) {
    return file_put_contents($this->dir.$key, serialize($value));
  }

  function rem($key) {
    if (is_writable($this->dir.$key)) {
      return unlink($this->dir.$key);
    }

    return false;
  }

  /*function clear() {
    $files = glob($this->dir);
    $ok = true;
     
    foreach ($files as $file)
      unlink($file);
  }*/

  function listKeys($prefix){
    $list = array_map("basename", glob($this->dir.$prefix."*"));
    $len = strlen($prefix);

    foreach ($list as &$_item) {
      $_item = substr($_item, $len);
    }

    return $list;
  }
}

/**
 * Alternative PHP Cache (APC) based Memory class
 */
class APCSharedMemory implements ISharedMemory {
  function init() {
    return function_exists('apc_fetch') &&
           function_exists('apc_store') &&
           function_exists('apc_delete');
  }

  function get($key) {
    return apc_fetch($key);
  }

  function put($key, $value) {
    return apc_store($key, $value);
  }

  function rem($key) {
    return apc_delete($key);
  }

  /*function clear() {
    return apc_clear_cache('user');
  }*/

  function listKeys($prefix) {
    $info = apc_cache_info("user");
    $cache_list = $info["cache_list"];
    $len = strlen($prefix);

    $keys = array();
    foreach ($cache_list as $_cache) {
      $keys[] = substr($_cache["info"], $len);
    }

    return $keys;
  }
}

/** Shared memory container */
abstract class SHM {
  const GZ = "__gz__";

  /**
   * @var ISharedMemory
   */
  static private $engine;

  /**
   * @var string
   */
  static private $prefix;

  /**
   * Available engines
   *
   * @var array
   */
  static $availableEngines = array(
    "disk" => "DiskSharedMemory",
    "apc"  => "APCSharedMemory",
  );

  /**
   * Initialize the shared memory
   *
   * @param string $engine Engine type
   * @param string $prefix Prefix to use
   *
   * @return void
   */
  static function init($engine = "disk", $prefix = "") {
    if (!isset(self::$availableEngines[$engine])) {
      $engine = "disk";
    }

    $engine = new self::$availableEngines[$engine];
    if (!$engine->init()) {
      $engine = new self::$availableEngines["disk"];
      $engine->init();
    }

    self::$prefix = "$prefix-";
    self::$engine = $engine;
  }

  /**
   * Get a value from the shared memory
   *
   * @param string $key The key of the value to get
   *
   * @return mixed
   */
  static function get($key) {
    $value = self::$engine->get(self::$prefix.$key);

    // If data is compressed
    if (is_array($value) && isset($value[self::GZ])) {
      $value = unserialize(gzuncompress($value[self::GZ]));
    }

    return $value;
  }

  /**
   * Save a value in the shared memory
   *
   * @param string $key      The key to pu the value in
   * @param mixed  $value    The value to put in the shared memory
   * @param bool   $compress Compress data
   *
   * @return bool
   */
  static function put($key, $value, $compress = false) {
    if ($compress) {
      $value = array(
        self::GZ => gzcompress(serialize($value))
      );
    }

    return self::$engine->put(self::$prefix.$key, $value);
  }

  /**
   * Remove a valur from the shared memory
   *
   * @param string $key The key to remove
   *
   * @return bool
   */
  static function rem($key) {
    return self::$engine->rem(self::$prefix.$key);
  }

  /**
   * List all the keys in the shared memory
   *
   * @return array
   */
  static function listKeys() {
    return self::$engine->listKeys(self::$prefix);
  }

  /**
   * Remove a list of keys corresponding to a pattern (* is a wildcard)
   *
   * @param string $pattern Pattern with "*" wildcards
   *
   * @return int The number of removed key/value pairs
   */
  static function remKeys($pattern) {
    $list = self::listKeys();

    $char = chr(255);
    $pattern = str_replace("*", $char, $pattern);
    $pattern = preg_quote($pattern);
    $pattern = str_replace($char, ".+", $pattern);
    $pattern = "/^$pattern$/";

    $n = 0;
    foreach ($list as $_key) {
      if (preg_match($pattern, $_key)) {
        self::rem($_key);
        $n++;
      }
    }

    return $n;
  }
}

SHM::init($dPconfig['shared_memory'], $rootName);
