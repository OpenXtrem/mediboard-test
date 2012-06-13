<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage system
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

class CExClassFieldPredicate extends CMbObject {
  var $ex_class_field_predicate_id = null;
  
  var $ex_class_field_id   = null;
  var $operator            = null;
  var $value               = null;
  var $_value              = null;
  
  var $_ref_ex_class_field = null;

  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "ex_class_field_predicate";
    $spec->key   = "ex_class_field_predicate_id";
    $spec->uniques["value"] = array("ex_class_field_id", "operator", "value");
    return $spec;
  }

  function getProps() {
    $props = parent::getProps();
    $props["ex_class_field_id"] = "ref notNull class|CExClassField cascade seekable";
    $props["operator"]          = "enum notNull list|=|!=|>|>=|<|<=|startsWith|endsWith|contains default|=";
    $props["value"]             = "str notNull seekable";
    $props["_value"]            = "str";
    return $props;
  }
  
  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps["display_fields"] = "CExClassField predicate_id";
    return $backProps;
  }
  
  function updateFormFields(){
    parent::updateFormFields();
    
    $field = $this->loadRefExClassField();
    
    $ex_object = new CExObject;
    $ex_object->_ex_class_id = $field->loadRefExGroup()->ex_class_id;
    $ex_object->setExClass();
    $ex_object->{$field->name} = $this->value;
    
    $this->_value = $ex_object->getFormattedValue($field->name);
    
    $this->_view = $field->_view." ".$this->_specs["operator"]->_locales[$this->operator]." ".$this->_value;
  }
  
  /**
   * @param bool $cache [optional]
   * @return CExClassField
   */
  function loadRefExClassField($cache = true){
    return $this->_ref_ex_class_field = $this->loadFwdRef("ex_class_field_id", $cache);
  }
  
  function getAutocompleteList($keywords, $where = null, $limit = null, $ljoin = null, $order = null) {
    $ex_class_id = $this->loadRefExClassField()->loadRefExGroup()->ex_class_id;
    
    $where = array_merge($where, array(
      "ex_class_field_group.ex_class_id" => "= '$ex_class_id'",
    ));
    
    $ljoin_new = array(
      "ex_class_field"       => "ex_class_field.ex_class_field_id = ex_class_field_predicate.ex_class_field_id",
      "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
    );
    
    if (is_array($ljoin)) {
      $ljoin = array_merge($ljoin, $ljoin_new);
    }
    else {
      $ljoin = $ljoin_new;
    }
    
    mbLog($where);
    mbLog($ljoin);
    
    $list = $this->loadList($where, null, null, null, $ljoin);
    
    $real_list = array();
    $re = preg_quote($keywords);
    $re = CMbString::allowDiacriticsInRegexp($re);
    $re = str_replace("/", "\\/", $re);
    $re = "/($re)/i";
    
    foreach($list as $_predicate) {
      if ($keywords == "%" || $keywords == "" || preg_match($re, $_predicate->_view)) {
        $real_list[$_predicate->_id] = $_predicate;
      }
    }
    
    $empty = new self;
    $empty->_id = null;
    $empty->_guid = "$this->_class-$this->_id"; // FIXME
    $empty->_view = "Aucune (toujours afficher)";
    array_unshift($real_list, $empty);
    
    $views = CMbArray::pluck($real_list, "_view");
    array_multisort($views, $real_list);
    
    return $real_list;
  }
  
  function checkValue($value){
    return CExClass::compareValues($value, $this->operator, $this->value);
  }
}
