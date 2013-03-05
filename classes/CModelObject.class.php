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

/**
 * - Metamodel: properties, class, validation
 * - Observation: handlers
 *
 * @abstract Mediboard model definition
 */
class CModelObject {
  /**
   * @var null
   */
  static $handlers = null; // must be null at the beginning @see self::makeHandlers
  /**
   * @var array
   */
  static $ignoredHandlers = array();
    
  /**
   * The object's class name
   * @var string
   */
  public $_class;
  
  /**
   * The object ID
   * @var integer
   */
  public $_id;
  
  /**
   * The object GUID ("_class-_id")
   * @var string
   */
  public $_guid;
  
  /**
   * The universal object view
   * @var string
   */
  public $_view          = '';
  
  /**
   * The universal object shortview
   * @var string
   */
  public $_shortview     = '';
  
  /**
   * The class specification
   * @var CMbObjectSpec 
   */
  public $_spec;    // Class specification

  /**
   * @var CMbFieldSpec[]
   */
  public $_specs         = array(); // Properties specifications as objects
  /**
   * @var array
   */
  public $_props         = array(); // Properties specifications as string

  /**
   * @var CMbBackSpec[]
   */
  public $_backSpecs     = array(); // Back reference specification as objects
  /**
   * @var array
   */
  public $_backProps     = array(); // Back reference specification as string

  /**
   * @var array
   */
  public $_configs       = array(); // Object configs

  /**
   * @var array
   */
  static $spec          = array();
  /**
   * @var array
   */
  static $props         = array();
  /**
   * @var array
   */
  static $specs         = array();
  /**
   * @var array
   */
  static $backProps     = array();
  /**
   * @var array
   */
  static $backSpecs     = array();

  /**
   * @var array
   */
  static $module_name   = array();
  
  /**
   * @var CModule
   */
  public $_ref_module; // Parent module

  /**
   * Construct
   *
   * @return CModelObject
   */
  function __construct() {
    return $this->initialize();
  }
  
  /**
   * Pre-serialize magic method
   *
   * @return array Property keys to be serialized
   */
  function __sleep() {
    return array_keys($this->_specs);
  }
  
  /**
   * Post-unserialize magic method
   *
   * @return void
   */
  function __wakeup() {
    $this->initialize();
  }
  
  /**
   * To string magic method
   *
   * @return string
   */
  function __toString() {
    return strip_tags($this->_view);
  }
  
  /**
   * Initialization factorisation for construction and unserialization
   *
   * @return void
   */
  function initialize() {
    $class = get_class($this);
    
    $in_cache = isset(self::$spec[$class]);

    if (!$in_cache) {
      self::$spec[$class] = $this->getSpec();
      self::$spec[$class]->init();
      
      if (isset(CApp::$classPaths[$class])) {
        $module = self::getModuleName(CApp::$classPaths[$class]);
      }
      else {
        $reflection = new ReflectionClass($class);
        $module = self::getModuleName($reflection->getFileName());
      }
      self::$module_name[$class] = $module;
    }
    
    $this->_class = $class;
    $this->_spec =& self::$spec[$class];
    
    if ($key = $this->_spec->key) {
      $this->_id =& $this->$key;
    }
    
    if (!$in_cache) {
      self::$props[$class] = $this->getProps();
      $this->_props =& self::$props[$class];

      self::$specs[$class] = $this->getSpecs();
      $this->_specs =& self::$specs[$class];
      
      self::$backProps[$class] = $this->getBackProps();
      $this->_backProps =& self::$backProps[$class];

      // Not prepared since it depends on many other classes
      // Has to be done as a second pass
      self::$backSpecs[$class] = array(); 

    }

    $this->_props     =& self::$props[$class];
    $this->_specs     =& self::$specs[$class];
    $this->_backProps =& self::$backProps[$class];
    $this->_backSpecs =& self::$backSpecs[$class];
    
    $this->_guid = "$this->_class-none";

    // @todo Move up to CStoredObject
    $this->loadRefModule(self::$module_name[$class]);    
  }
  
  /**
   * Get the module name corresponding to given path
   *
   * @param string $path Path name
   *
   * @return string Module name
   */
  private static function getModuleName($path) {
    if ("classes" === basename($path = dirname($path))) {
      $path = dirname($path);
    }
    return basename($path);
  }
  
  
  /**
   * Staticly build object handlers array
   *
   * @return void
   */
  protected static final function makeHandlers() {
    if (is_array(self::$handlers)) {
      return;
    }
    
    // Static initialisations
    self::$handlers = array();
    foreach (CAppUI::conf("object_handlers") as $_class => $_active) {
      if ($_active && !isset(self::$ignoredHandlers[$_class])) {
        if (!class_exists($_class)) {
          trigger_error("Model object handler missing class '$_class'", E_USER_ERROR);
          continue;
        }
        
        self::$handlers[$_class] = new $_class;
      }
    }
  }

  /**
   * Get handlers
   *
   * @return array
   */
  static function getHandlers(){
    return self::$handlers;
  }
  
  /**
   * Ignore a specific handler
   *
   * @param string $handler The handler's class name
   *
   * @return void
   */
  static final function ignoreHandler($handler) {
    self::$ignoredHandlers[$handler] = $handler;
    unset(self::$handlers[$handler]);
  }
  
  /**
   * Initialize object specification
   *
   * @return CMbObjectSpec the spec
   */
  function getSpec() {
    return new CMbObjectSpec();
  }
  
  /**
   * Get properties specifications as strings
   *
   * @return array
   */
  function getProps() {
    return array (
      "_shortview" => "str",
      "_view"      => "str",
      $this->_spec->key => "ref class|$this->_class show|0"
    );
  }
  
  /**
   * Get backward reference specifications
   *
   * @return array Array of form "collection-name" => "class join-field"
   */
  function getBackProps() {
    return array (
      "logs" => "CUserLog object_id",
    );
  }
  
  /**
   * Get the backrefs to export when using CMbObjecExport
   *
   * @return array
   */
  function getExportedBackRefs(){
    return array();
  }

  /**
   * Convert string back specifications to objet specifications
   *
   * @param string $backName The name of the back reference
   *
   * @return CMbBackSpec The back reference specification, null if undefined
   */
  function makeBackSpec($backName) {
    if (array_key_exists($backName, $this->_backSpecs)) {
      return $this->_backSpecs[$backName];
    }

    if ($backSpec = CMbBackSpec::make($this->_class, $backName, $this->_backProps[$backName])) {
      return $this->_backSpecs[$backName] = $backSpec;
    }
  }
  
  /**
   * Makes all the back specs
   *
   * @return void
   */
  function makeAllBackSpecs() {
    foreach ($this->_backProps as $backName => $backProp) {
      $this->makeBackSpec($backName);
    }
  }

  /**
   * Converts properties string specifications to object specifications
   * Optimized version
   *
   * @return CMbFieldSpec[]
   */
  function getSpecs() {
    $specs = array();
    foreach ($this->_props as $name => $prop) {
      $specs[$name] = CMbFieldSpecFact::getSpec($this, $name, $prop);
    }
    return $specs;
  }
    
  /**
   * Decode all string fields (str, text, html)
   *
   * @return void
   */
  function decodeUtfStrings() {
    foreach ($this->_specs as $name => $spec) {
      if (in_array(get_class($spec), array("CStrSpec", "CHtmlSpec", "CTextSpec"))) {
        if (null !== $this->$name) {
          $this->$name = utf8_decode($this->$name);
        }
      }
    }
  }
  
  /**
   * Set default values to properties
   *
   * @return void
   */
  function valueDefaults() {
    $specs  = $this->getSpecs();
    
    $fields = $this->getPlainFields();
    unset($fields[$this->_spec->key]);
    unset($fields["object_id"]);
    foreach ($fields as $_name => $_value) {
      $this->$_name = $specs[$_name]->default;
    }
  }
  
  /**
   * Check a property against its specification
   *
   * @param string $name Name of the property
   *
   * @return string Store-like error message
   */
  function checkProperty($name) {
    $spec = $this->_specs[$name];
    return $spec->checkPropertyValue($this);
  }

  /**
   * Check confidential
   *
   * @param array $specs Specs
   *
   * @return void
   */
  function checkConfidential($specs = null) {
    if (CAppUI::conf("hide_confidential")) {
      if ($specs == null) {
        $specs = $this->_specs;
      }
      foreach ($specs as $name => $_spec) {
        $value =& $this->$name;
        if ($value !== null && $this->_specs[$name]) {
          $this->_specs[$name]->checkConfidential($this);
        }
      }
    }
  }
  
  /**
   * Get object properties, i.e. having specs
   *
   * @param bool $nonEmpty Filter non empty values
   *
   * @return array Associative array
   */
  function getProperties($nonEmpty = false) {
    $values = array();
    
    foreach ($this->_specs as $key => $_spec) {
      $value = $this->$key;
      if (!$nonEmpty || ($value !== null && $value !== "")) {
        $values[$key] = $value;
      }
    }

    return $values;
  }
  
  /**
   * Returns the field's formatted value
   *
   * @param string $field   Field name
   * @param array  $options Format options
   *
   * @return string The field's formatted value
   */
  function getFormattedValue($field, $options = array()) {
    return $this->_specs[$field]->getValue($this, new CSmartyDP, $options);
  }

  /**
   * Returns the field's HTML label element
   *
   * @param string $field   Field name
   * @param array  $options Format options
   *
   * @return string The field's formatted value
   */
  function getLabelElement($field, $options = array()) {
    return $this->_specs[$field]->getLabelElement($this, $options);
  }
  
  /**
   * Returns the field's main locale
   *
   * @param string $field Field name
   *
   * @return string The locale
   */
  function getLocale($field) {
    return CAppui::tr("$this->_class-$field");
  }
  
  /**
   * Trigger a warning with appropriate locale and variatic i18n parameters
   *
   * @param string $suffix Locale suffix
   *
   * @return void
   */
  static function warning($suffix/*, ... */) {
    $args = func_get_args();
    unset($args[0]);
    $backtrace = debug_backtrace();
    $class = $backtrace[1]["class"];
    $message = CAppUI::tr("$class-warning-$suffix", $args);
    trigger_error($message, E_USER_WARNING);
  }

  /**
   * Trigger an error with appropriate locale and variatic i18n parameters
   *
   * @param string $suffix Locale suffix
   *
   * @return void
   */
  static function error($suffix/*, ... */) {
    $args = func_get_args();
    unset($args[0]);
    $backtrace = debug_backtrace();
    $class = $backtrace[1]["class"];
    $message = CAppUI::tr("$class-warning-$suffix", $args);
    trigger_error($message, E_USER_ERROR);
  }
  
  /**
   * Bind an object with an array
   *
   * @param array $hash  associative array of values to match with
   * @param bool  $strip true to strip slashes
   *
   * @return bool
   */
  function bind($hash, $strip = true) {
    bindHashToObject($strip ? stripslashes_deep($hash) : $hash, $this);
    return true;
  }
  
  /**
   * Update the form (derived) fields plain fields
   *
   * @return void
   */
  function updateFormFields() {
    $this->_guid = "$this->_class-$this->_id";
    $this->_view = CAppUI::tr($this->_class) . " " . $this->_id;
    $this->_shortview = "#$this->_id";
  }
  
  /**
   * Get DB fields and there values
   *
   * @todo Rename to plainFields
   *
   * @return array Associative array
   */
  function getPlainFields() {
    $result = array();
    $vars = get_object_vars($this);
    foreach ($vars as $name => $value) {
      if ($name[0] !== '_') {
        $result[$name] = $value;
      }
    }

    return $result;
  }
  
  /**
   * Update the plain fields from the form fields
   *
   * @todo Rename to PlainFields()
   *
   * @return void
   */
  function updatePlainFields() {
    $specs = $this->_specs;
    $fields = $this->getPlainFields();
    
    foreach ($fields as $name => $value) {
      if ($value !== null) {
        $this->$name = $specs[$name]->trim($value);
      }
    }
  }

  /**
   * Merges the fields of an array of objects to $this
   *
   * @param array $objects       An array of CMbObject
   * @param bool  $getFirstValue Get first value ?
   *
   * @return $this or an error
   */
  function mergePlainFields ($objects /*array(<CMbObject>)*/, $getFirstValue = false) {
    $fields = $this->getPlainFields();
    $diffs = $fields;
    foreach ($diffs as &$diff) {
      $diff = false;
    }
    
    foreach ($objects as &$object) {
      foreach ($fields as $name => $value) {
        // Assign the value of the first object
        if ($getFirstValue) {
          if ($this->$name === null) {
            $this->$name = $object->$name;
          }
          continue;
        }
        
        // Try to assign the first not null value among objects
        if ($this->$name === null && !$diffs[$name]) {
          $this->$name = $object->$name;
          continue;
        }
        
        // In case we have different values, rather nullify
        if ($this->$name != $object->$name) {
          $diffs[$name] = true;
          $this->$name  = null;
        }
      }
    }
  }
  
  /**
   * Nullify object fields that are empty strings
   *
   * @todo Rename to plainFields
   *
   * @return void
   */
  function nullifyEmptyFields() {
    foreach ($this->getPlainFields() as $name => $value) {
      if ($value === "") {
        $this->$name = null;
      }
    }
  }
    
  /**
   * Extends object properties with target object (of the same class) properties
   *
   * @param CMbObject $mbObject object to extend with
   * @param bool      $gently   Gently preserve existing non-empty values
   *
   * @return void
   */
  function extendsWith(CMbObject $mbObject, $gently = false) {
    if ($this->_class !== $mbObject->_class) {
      trigger_error(
        printf("Target object has not the same class (%s) as this (%s)", $mbObject->_class, $this->_class),
        E_USER_WARNING
      );

      return;
    }
    
    foreach ($mbObject->getProperties() as $name => $value) {
      if ($value !== null && $value != "") {
        if (!$gently || $this->$name === null || $this->$name === "") {
          $this->$name = $value;
        }
      }
    }
  }

  /**
   * Clone object
   *
   * @param CMbObject $mbObject
   *
   * @return void
   */
  function cloneFrom(CMbObject $mbObject) {
    $this->extendsWith($mbObject);
    $this->_id = null;
  }
  
  /**
   * Subject notification mechanism
   *
   * @param string $message on[Before|After][Store|Merge|Delete|FillLimitedTemplate] which have to get back de CPersistantObject layer
   *
   * @todo Implement to factorise
   *
   * @return void
   */
  function notify($message/*, ... */) {
    // Event Handlers
    self::makeHandlers();
    
    $args = func_get_args();
    array_shift($args); // $message
    array_unshift($args, $this);
    
    foreach (self::getHandlers() as $handler) {
      try {
        call_user_func_array(array($handler, "on$message"), $args);
      } 
      catch (Exception $e) {
        CAppUI::setMsg($e, UI_MSG_ERROR);
      }
    }
  }

  /**
   * Get CSV values for object, i.e. db fields, references excepted
   *
   * @return array Associative array of values
   */
  function getCSVFields() {
    $fields = array();
    foreach ($this->getPlainFields() as $name => $value) {
      if (!$this->_specs[$name] instanceof CRefSpec) {
        $fields[$name] = $value;
      }
    }
    return $fields;
  }

  /**
   * Log shortcut to mbTrace
   *
   * @param string $label Add an optional label
   * @param bool   $log   Log to file or echo data
   *
   * @return int The size of the data written in the log file
   **/
  function log($label = null, $log = true) {
    return mbTrace($this->getPlainFields(), $label, $log);
  }
}
