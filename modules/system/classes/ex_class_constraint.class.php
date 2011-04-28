<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage system
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

class CExClassConstraint extends CMbObject {
  var $ex_class_constraint_id = null;
  
  var $ex_class_id   = null;
  var $field         = null;
  var $operator      = null;
  var $value         = null;
  
  var $_ref_ex_class = null;
  var $_ref_target_object = null;
  var $_ref_target_spec = null;
  
  var $_locale = null;
  var $_locale_desc = null;
  var $_locale_court = null;

  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "ex_class_constraint";
    $spec->key   = "ex_class_constraint_id";
    $spec->uniques["constraint"] = array("ex_class_id", "field");
    return $spec;
  }

  function getProps() {
    $props = parent::getProps();
    $props["ex_class_id"] = "ref notNull class|CExClass";
    $props["field"]       = "str notNull";
    $props["operator"]    = "enum notNull list|=|!=|>|>=|<|<=|startsWith|endsWith|contains default|=";
    $props["value"]       = "str notNull";
    
    $props["_locale"]     = "str";
    $props["_locale_desc"]  = "str";
    $props["_locale_court"] = "str";
    return $props;
  }
  
  function resolveSpec($ref_object){
    if (strpos($this->field, "-") === false) {
      $spec = $ref_object->_specs[$this->field];
    }
    else {
      $parts = explode("-", $this->field);
      $_spec = $ref_object->_specs[$parts[0]];
      
      if (!$_spec->class) {
        return;
      }
      
      $obj = new $_spec->class;
      $spec = $obj->_specs[$parts[1]];
    }
    
    return $spec;
  }
  
  function loadTargetObject(){
    $this->loadRefExClass();
    $this->completeField("field", "value");
    
    $ref_object = new $this->_ref_ex_class->host_class;
    
    if (!$this->_id) {
      return $this->_ref_target_object = new CMbObject;
    }
    
    $spec = $this->resolveSpec($ref_object);
    
    if ($spec instanceof CRefSpec && $this->value) {
      $this->_ref_target_object = CMbObject::loadFromGuid($this->value);
    }
    else {
      // empty object
      $this->_ref_target_object = new CMbObject;
    }
    
    $this->_ref_target_spec = $spec;
    
    return $this->_ref_target_object;
  }
  
  function updateFormFields(){
    parent::updateFormFields();
    
    $this->loadRefExClass();
    
    if (strpos($this->field, "-") === false) {
      $this->_view = CAppUI::tr("{$this->_ref_ex_class->host_class}-{$this->field}");
    }
    else {
      $parts = explode("-", $this->field);
      
      $this->_view = CAppUI::tr("{$this->_ref_ex_class->host_class}-{$parts[0]}");
      
      $ref_object = new $this->_ref_ex_class->host_class;
      $_spec = $ref_object->_specs[$parts[0]];
      
      if (!$_spec->class) {
        return;
      }
      
      $this->_view .= " / ".CAppUI::tr("{$_spec->class}-{$parts[1]}");
    }
  }
  
  function checkConstraint(CMbObject $object) {
    $this->completeField("field", "value");
    $object->completeField($this->field);
    $value = $object->{$this->field};
    $cons = $this->value;
    
    // =|!=|>|>=|<|<=|startsWith|endsWith|contains default|=
    switch ($this->operator) {
      default:
      case "=": 
        if ($value == $cons) return true;
        break;
        
      case "!=": 
        if ($value != $cons) return true;
        break;
        
      case ">": 
        if ($value > $cons) return true;
        break;
        
      case ">=": 
        if ($value >= $cons) return true;
        break;
        
      case "<": 
        if ($value < $cons) return true;
        break;
        
      case "<=": 
        if ($value <= $cons) return true;
        break;
        
      case "startsWith": 
        if (strpos($value, $cons) === 0) return true;
        break;
        
      case "endsWith": 
        if (substr($value, -strlen($cons)) == $cons) return true;
        break;
        
      case "contains": 
        if (strpos($value, $cons) !== false) return true;
        break;
    }
    
    return false;
  }
  
  function loadRefExClass(){
    return $this->_ref_ex_class = $this->loadFwdRef("ex_class_id");
  }
}
