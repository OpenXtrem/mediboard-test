<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage system
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CAppUI::requireModuleClass("system", "ex_list_items_owner");

class CExClassField extends CExListItemsOwner {
  var $ex_class_field_id = null;
  
  var $ex_group_id = null;
  var $name = null; // != object_class, object_id, ex_ClassName_event_id, 
  var $prop = null; 
  var $concept_id = null;
	
  var $coord_label_x = null; 
  var $coord_label_y = null; 
  var $coord_field_x = null; 
  var $coord_field_y = null; 
  
  var $_locale = null;
  var $_locale_desc = null;
  var $_locale_court = null;
  
  var $_ref_ex_group = null;
  var $_ref_ex_class = null;
  var $_ref_translation = null;
	var $_ref_concept = null;
  var $_spec_object = null;
  
  var $_dont_drop_column = null;
  
  static $_indexed_types = array("ref", "date", "dateTime", "time");
  static $_data_type_groups = array(
    array("ipAddress"),
    array("bool"), 
    array("enum"), 
    array("ref"), 
    array("num", "numchar"), 
    array("pct", "float", "currency"),
    array("time"), 
    array("date", "birthDate"),
    array("dateTime"), 
    array("code"), 
    array("email"), 
    array("password", "str"), 
    array("php", "xml", "html", "text"), 
  );

  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "ex_class_field";
    $spec->key   = "ex_class_field_id";
    $spec->uniques["name"] = array("ex_group_id", "name");
    
    // should ignore empty values
    //$spec->uniques["coord_label"] = array("ex_group_id", "coord_label_x", "coord_label_y");
    //$spec->uniques["coord_field"] = array("ex_group_id", "coord_field_x", "coord_field_y");
    return $spec;
  }

  function getProps() {
    $props = parent::getProps();
    $props["ex_group_id"] = "ref class|CExClassFieldGroup cascade";
    $props["concept_id"]  = "ref class|CExConcept autocomplete|name";
    $props["name"]        = "str notNull protected canonical";
    $props["prop"]        = "text notNull";
    
    $props["coord_field_x"] = "num min|0 max|100";
    $props["coord_field_y"] = "num min|0 max|100";
    $props["coord_label_x"] = "num min|0 max|100";
    $props["coord_label_y"] = "num min|0 max|100";
		
    $props["_locale"]     = "str notNull";
    $props["_locale_desc"]  = "str";
    $props["_locale_court"] = "str";
    return $props;
  }
	
  function getBackProps() {
    $backProps = parent::getBackProps();
		$backProps["enum_translations"] = "CExClassFieldEnumTranslation ex_class_field_id";
		$backProps["field_translations"] = "CExClassFieldTranslation ex_class_field_id";
    return $backProps;
  }
  
  function updateFormFields(){
    parent::updateFormFields();
    $this->_view = "$this->name [$this->prop]";
    
    $this->updateTranslation();
  }
  
  function loadRefExGroup($cache = true){
    return $this->_ref_ex_group = $this->loadFwdRef("ex_group_id", $cache);
  }
  
  function loadRefExClass($cache = true){
  	return $this->_ref_ex_class = $this->loadRefExGroup($cache)->loadRefExClass($cache);
  }
  
	/**
	 * CExConcept
	 * 
	 * @param object $cache [optional]
	 * @return 
	 */
  function loadRefConcept($cache = true){
    return $this->_ref_concept = $this->loadFwdRef("concept_id", $cache);
  }
  
  function loadRefTranslation() {
    $trans = new CExClassFieldTranslation;
    $trans->lang = CAppUI::pref("LOCALE");
    $trans->ex_class_field_id = $this->_id;
    $trans->loadMatchingObject();
    $trans->fillIfEmpty($this->name);
    return $this->_ref_translation = $trans;
  }
  
  function loadRefEnumTranslations() {
    $trans = new CExClassFieldEnumTranslation;
    $trans->lang = CAppUI::pref("LOCALE");
    $trans->ex_class_field_id = $this->_id;
    return $trans->loadMatchingList();
  }
  
  function updateTranslation(){
  	$list_owner = $this->getRealListOwner();
		$items = $list_owner->loadRefItems();
		
    global $locales;
		
		$ex_class = $this->loadRefExClass();
		
    $key = $ex_class->getExClassName().".$this->name";
		foreach($items as $_item) {
	    $locales["{$key}.$_item->_id"] = $_item->name;
		}
    
    $trans = $this->loadRefTranslation();
    $this->_locale       = $trans->std;
    $this->_locale_desc  = $trans->desc;
    $this->_locale_court = $trans->court;
    
    $this->_view = $this->_locale;
    
    return $trans;
  }
  
  function getTableName(){
    return $this->loadRefExClass()->getTableName();
  }
  
  function getSpecObject(){
    return $this->_spec_object = @CMbFieldSpecFact::getSpecWithClassName("CExObject", $this->name, $this->prop);
  }
  
  function getSQLSpec($union = true){
  	$spec_obj = $this->getSpecObject();
		$db_spec = $spec_obj->getFullDBSpec();
		
		if ($union) {
			$ds = $this->_spec->ds;
      $db_parsed = CMbFieldSpec::parseDBSpec($db_spec, true);
			
		  if ($db_parsed['type'] === "ENUM") {
				$prop_parsed = $ds->getDBstruct($this->getTableName(), $this->name, true);
				
				if (isset($prop_parsed[$this->name])) {
				  $db_parsed['params'] = array_merge($db_parsed['params'], $prop_parsed['params']);
				}
				
				$db_parsed['params'] = array_unique($db_parsed['params']);
				
        $spec_obj->list = implode("|", $db_parsed['params']);
        $db_spec = $spec_obj->getFullDBSpec();
			}
		}
		
    return $db_spec;
  }
  
  function store(){
    if (!$this->_id && $this->concept_id) {
      $this->prop = $this->loadRefConcept()->prop;
    }
		
    if ($msg = $this->check()) return $msg;
    
    if (!preg_match('/^[a-z0-9_]+$/i', $this->name)) {
      return "Nom de champ invalide ($this->name)";
    }
    
    $ds = $this->_spec->ds;
    
    if (!$this->_id) {
      $table_name = $this->getTableName();
      $sql_spec = $this->getSQLSpec();
      $query = "ALTER TABLE `$table_name` ADD `$this->name` $sql_spec";
      
      if (!$ds->query($query)) {
        return "Le champ '$this->name' n'a pas pu �tre ajout� � la table '$table_name' (".$ds->error().")";
      }
      
      $spec_type = $this->getSpecObject()->getSpecType();
      
      // ajout de l'index
      if (in_array($spec_type, self::$_indexed_types)) {
        $query = "ALTER TABLE `$table_name` ADD INDEX (`$this->name`)";
        
        if (!$ds->query($query)) {
          //return "L'index sur le champ '$this->name' n'a pas pu �tre ajout� (".$ds->error().")";
        }
      }
    }
    
    else if ($this->fieldModified("name") || $this->fieldModified("prop")) {
      $table_name = $this->getTableName();
      $sql_spec = $this->getSQLSpec();
      $query = "ALTER TABLE `$table_name` CHANGE `{$this->_old->name}` `$this->name` $sql_spec";

      if (!$ds->query($query)) {
        return "Le champ '$this->name' n'a pas pu �tre mis � jour (".$ds->error().")";
      }
		}
    
    $locale       = $this->_locale;
    $locale_desc  = $this->_locale_desc;
    $locale_court = $this->_locale_court;
    
    if ($msg = parent::store()) {
      return $msg;
    }
    
    // self translations
    if ($locale || $locale_desc || $locale_court) {
      $trans = $this->loadRefTranslation();
      $trans->std = $locale;
      $trans->desc = $locale_desc;
      $trans->court = $locale_court;
      if ($msg = $trans->store()) {
        mbTrace($msg, get_class($this), true);
      }
    }
  }
  
  function delete(){
    if ($msg = $this->canDeleteEx()) {
      return $msg;
    }
		
    if (!$this->_dont_drop_column) {
      $this->completeField("name");
      
      $table_name = $this->loadRefExClass()->getTableName();
      $query = "ALTER TABLE `$table_name` DROP `$this->name`";
      $ds = $this->_spec->ds;
      
      if (!$ds->query($query)) {
        return "Le champ '$this->name' n'a pas pu �tre supprim� (".$ds->error().")";
      }
    }
    
    return parent::delete();
  }
  
	/**
	 * @return CExListItemsOwner
	 */
  function getRealListOwner(){
    if ($this->concept_id) {
      return $this->loadRefConcept()->getRealListOwner();
    }
    
    return parent::getRealListOwner();
  }
}
