<?php /* $Id$ */

/**
 *  @package Mediboard
 *  @subpackage classes
 *  @version $Revision: $
 *  @author Sébastien Fillonneau
*/

require_once("./classes/mbFieldSpec.class.php");

class CDateTimeSpec extends CMbFieldSpec {
  
  function getValue($object, $smarty, $params = null) {
    require_once $smarty->_get_plugin_filepath('modifier','date_format');
    $fieldName = $this->fieldName;
    $propValue = $object->$fieldName;
    $format = mbGetValue(@$params["format"], "%d/%m/%Y %H:%M");
    if($propValue && $propValue != "0000-00-00 00:00:00") {
      return smarty_modifier_date_format($propValue, $format);
    } else {
      return "-";
    }
  }
  
  function getSpecType() {
    return("dateTime");
  }
  
  function checkProperty($object){
    $fieldName = $this->fieldName;
    $propValue = $object->$fieldName;
    
    if (!preg_match ("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})[ \+]([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/", $propValue)) {
      return "format de dateTime invalide";
    }
    return null;
  }
  
  function sample(&$object){
    parent::sample($object);
    $fieldName = $this->fieldName;
    $propValue =& $object->$fieldName;
    
    $propValue = "19".$this->randomString(CMbFieldSpec::$nums, 2).
      "-".$this->randomString(CMbFieldSpec::$months, 1).
      "-".$this->randomString(CMbFieldSpec::$days, 1);
    $propValue .= " ".$this->randomString(CMbFieldSpec::$hours, 1).
      ":".$this->randomString(CMbFieldSpec::$mins, 1).
      ":".$this->randomString(CMbFieldSpec::$mins, 1);
  }
  
  function getDBSpec(){
    return "DATETIME";
  }
  
  function getFormHtmlElement($object, $params, $value, $className){
    return $this->getFormElementDateTime($object, $params, $value, $className);
  }
}

?>