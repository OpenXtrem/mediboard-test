<?php
 
/**
 *  @package Mediboard
 *  @subpackage classes
 *  @version $Revision: $
 *  @author Alexis Granger
 */


class CSQLDataSource { 

	static $dataSources     = array();
    
    var $dsn       = null;
    var $link      = null;
    var $chrono    = null;
    var $dbhost    = null;
    var $dbname    = null;
    var $dbuser    = null;
    var $dbpass    = null;
    var $dbport    = null; 
    var $dbpersist = null;
  
  function CDataSource(){
  }
  
  
  function init($dsn) {

    global $AppUI;
    $AppUI->getSystemClass("chrono");
    
    $this->dsn       = $dsn;
    $this->dbhost    = $AppUI->cfg["db"][$dsn]["dbhost"];
    $this->dbname    = $AppUI->cfg["db"][$dsn]["dbname"];
    $this->dbuser    = $AppUI->cfg["db"][$dsn]["dbuser"];
    $this->dbpass    = $AppUI->cfg["db"][$dsn]["dbpass"];
    $this->dbport    = $AppUI->cfg["db"][$dsn]["dbport"];
    $this->dbpersist = $AppUI->cfg["dbpersist"];
    
    $this->link = $this->connect($dsn, $this->dbhost, $this->dbname, $this->dbuser, $this->dbpass,$this->dbport, $this->dbpersist);
  }
  
  
  // Recherche le dataSource s'il existe ou en cr�e un
  function get($dsn) {  	
  	if(!array_key_exists($dsn, self::$dataSources)){
  	  $dataSource = new CMySQLDataSource();
  	  $dataSource->init($dsn);
  	  self::$dataSources[$dsn] = $dataSource;
  	}
  	return self::$dataSources[$dsn];
  }
  
  
  
  function connect($dsn, $dbhost, $dbname, $dbuser, $dbpass, $dbport, $dbpersist){
    return null;	
  }
	  

  /**
   * Returns a link handler for given sourcename
   * @param string $dsn The DB sourcename
   */
  function link($dsn = "std") {
    if (!array_key_exists($dsn, $this->dataSources)) {
      trigger_error( "FATAL ERROR: link to $dsn not found.", E_USER_ERROR );
    }
    return $this->dataSources[$dsn];  
  }

  /**
  * This global function loads the first field of the first row returned by the query.
  *
  * @param string The SQL query
  * @param strong The db identifier
  * @return The value returned in the query or null if the query failed.
  */
  function loadResult($sql, $dsn = "std") {
    $cur = $this->exec($sql, $dsn);
    $cur or exit($this->error());
    $ret = null;
    if ($row = $this->fetchRow($cur)) {
      $ret = $row[0];
    }
    $this->freeResult($cur);
    return $ret;
  }

  
  /**
  * This global function loads the first row of a query into an object 
  *
  * If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
  * If <var>object</var> has a value of null, then all of the returned query fields returned in the object. 
  * @param string The SQL query
  * @param object The address of variable
  */
  function loadObject($sql, &$object) {
    //global $mbCacheObjectCount;
    $class = get_class($object);
    if ($object != null) {
      $hash = array();
      if(!$this->loadHash($sql, $hash)) {
        return false;
      }
      $this->bindHashToObject($hash, $object, false);
      return true;
    } else {
      $cur = $this->exec($sql);
      $cur or exit($this->error());
      if ($object = $this->fetchObject($cur)) {
        $this->freeResult($cur);
        return true;
      } else {
        $object = null;
        return false;
      }
    }
  }

  
  function loadObjectWithOpt($sql, &$object) {
    global $mbCacheObjectCount;
    $class = get_class($object);
    if ($object != null) {
      if($object->_id && isset($object->_objectsTable[$object->_id])) {
        $this->bindObjectToObject($object->_objectsTable[$object->_id], $object);
        $mbCacheObjectCount++;
        return true;
      } else {
        $hash = array();
        if(!$this->loadHash($sql, $hash)) {
          return false;
        }
        $this->bindHashToObject($hash, $object, false);
        return true;
      }
    } else {
      $cur = $this->exec($sql);
      $cur or exit($this->error());
      if ($object = $this->fetchObject($cur)) {
        $this->freeResult($cur);
        return true;
      } else {
        $object = null;
        return false;
      }
    }
  }

  /**
  * This global function return a result row as an associative array 
  *
  * @param string The SQL query
  * @param array An array for the result to be return in
  * @return <b>True</b> is the query was successful, <b>False</b> otherwise
  */
  function loadHash($sql, &$hash) {
    $cur = $this->exec($sql);
    $cur or exit($this->error());
    $hash = $this->fetchAssoc($cur);
    $this->freeResult($cur);
    if ($hash == false) {
      return false;
    } else {
      return true;
    }
  }

  /**
  * Document::loadHashList()
  *
  * { Description }
  *
  * @param string $index
  */
  function loadHashList($sql, $index = "") {
    $cur = $this->exec($sql);
    $cur or exit($this->error());
    $hashlist = array();
    while ($hash = $this->fetchArray($cur)) {
      $hashlist[$hash[$index ? $index : 0]] = $index ? $hash : $hash[1];
    }
    $this->freeResult($cur);
    return $hashlist;
  }

  /**
  * Document::loadList() 
  *
  * return an array of the db lines
  * can connect to any database via $dsn param 
  *
  * @param int $maxrows
  * @param string the db identifier
  * @return array the query result
  */
  function loadList($sql, $maxrows = null, $dsn = "std") {
    //global $AppUI;
    if (!($cur = $this->exec($sql, $dsn))) {;
      $AppUI->setMsg($this->error(), UI_MSG_ERROR);
      return false;
    }
    $list = array();
    $cnt = 0;
    while ($hash = $this->fetchAssoc($cur)) {
      $list[] = $hash;
      if($maxrows && $maxrows == $cnt++) {
        break;
      }
    }
    $this->freeResult($cur);
    return $list;
  }

  /**
  * Document::loadColumn()
  *
  * Loads the first column for a given query
  *
  * @param int $maxrows limit to a maximum nember of rows
  */
  function loadColumn($sql, $maxrows = null, $dsn = "std") {
    global $AppUI;
    if (!($cur = $this->exec($sql, $dsn))) {
      $AppUI->setMsg($this->error($dsn), UI_MSG_ERROR);
      return false;
    }
    $list = array();
    $cnt = 0;
    while ($row = $this->fetchRow($cur)) {
      $list[] = $row[0];
      if($maxrows && $maxrows == $cnt++) {
        break;
      }
    }
    $this->freeResult($cur);
    return $list;
  }

  /* return an array of objects from a SQL SELECT query
   * class must implement the Load() factory, see examples in Webo classes
   * @note to optimize request, only select object oids in $sql
   */
  function loadObjectList($sql, $object, $maxrows = null) {
    global $mbCacheObjectCount;
    $cur = $this->exec($sql);
    $list = array();
    $cnt = 0;
    $class = get_class($object);
    $table_key = $object->_tbl_key;
    while ($row = $this->fetchArray($cur)) {
      $key = $row[$table_key];
      $newObject = new $class();
      $newObject->bind($row, false);
      $newObject->checkConfidential();
      $newObject->updateFormFields();
      $list[$newObject->_id] = $newObject;
      if($maxrows && $maxrows == $cnt++) {
        break;
      }
    }
    $this->freeResult($cur);
    return $list;
  }

  function loadObjectListWithOpt($sql, $object, $maxrows = null) {
    global $mbCacheObjectCount;
    $cur = $this->exec($sql);
    $list = array();
    $cnt = 0;
    $class = get_class($object);
    $table_key = $object->_tbl_key;
    while ($row = $this->fetchArray($cur)) {
      $key = $row[$table_key];
      if(isset($object->_objectsTable[$key])) {
        $list[$key] = $object->_objectsTable[$key];
        $mbCacheObjectCount++;
      } else {
        $newObject = new $class();
        $newObject->bind($row, false);
        $newObject->checkConfidential();
        $newObject->updateFormFields();
        $object->_objectsTable[$newObject->_id] = $newObject;
        $list[$newObject->_id] = $newObject;
      }
      if($maxrows && $maxrows == $cnt++) {
        break;
      }
    }
    $this->freeResult($cur);
    return $list;
  }


  /**
  * Document::insertArray() 
  *
  * { Description }
  *
  * @param [type] $verbose
  */
  function insertArray($table, &$hash, $verbose = false) {
    return null;
  }

  
  /**
  * Document::updateArray() 
  *
  * { Description }
  *
  * @param [type] $verbose
  */
  function updateArray($table, &$hash, $keyName, $verbose = false) {
    return null;
  }

  
  /**
  * Document::delete()  
  *
  * { Description } 
  *
  */
  function delete($table, $keyName, $keyValue) {
    return null;
  }


  /**
  * Document::insertObject() 
  *
  * { Description } 
  *
  * @param [type] $keyName
  * @param [type] $verbose
  */
  function insertObject($table, &$object, $keyName = null, $verbose = false) {
    return null;
  }

  
  
  /**
  * Document::updateObject() 
  *
  * { Description }
  *
  * @param [type] $updateNulls
  */
  function updateObject($table, &$object, $keyName) {
    return null;
  }

  /**
  * Document::dateConvert() 
  *
  * { Description } 
  *
  */
  function dateConvert($src, &$dest, $srcFmt) {
    $result = strtotime($src);
    $dest = $result;
    return ($result != 0);
  }

  /**
  * Document::datetime() 
  *
  * { Description } 
  *
  * @param [type] $timestamp
  */
  function datetime($timestamp = null) {
    if (!$timestamp) {
     return null;
    }
    if (is_object($timestamp)) {
      return $timestamp->toString("%Y-%m-%d %H:%M:%S");
    } else {
      return strftime("%Y-%m-%d %H:%M:%S", $timestamp);
    }
  }

  /**
  * Document::dateTime2locale()  
  *
  * { Description }
  *  
  */
  function dateTime2locale($dateTime, $format) {
    if (intval($dateTime)) {
      $date = new CDate($dateTime);
      return $date->format($format);
    } else {
      return null;
    }
  }

  function stripslashes_deep($value) {
    return is_array($value) ?
      array_map("stripslashes_deep", $value) :
      stripslashes($value);
  }

  /**
  * copy the hash array content into the object as properties
  * only existing properties of object are filled. when undefined in hash, properties wont be deleted
  * @param array the input array
  * @param obj byref the object to fill of any class
  * @param string
  * @param boolean
  * @param boolean
  */
  function bindHashToObject($hash, &$obj, $doStripSlashes = true) {
    is_array($hash) or die("bindHashToObject : hash expected");
    is_object($obj) or die("bindHashToObject : object expected");

    foreach (get_object_vars($obj) as $k => $v) {
      if (isset($hash[$k])) {
        if($doStripSlashes){
          $obj->$k = stripslashes_deep($hash[$k]);
        }else{
          $obj->$k = $hash[$k];
        }
      } 
    }
  }

  function bindObjectToObject($obj1, &$obj) {
    is_object($obj1) or die("bindObjectToObject : object expected");
    is_object($obj) or die("bindObjectToObject : object expected");
    foreach ($obj1->getProps() as $k => $v) {
      $obj->$k = $v;
    }
  }

  function loadTable($table, $dsn = "std") {
    return null;
  }

  function loadField($table, $field, $dsn = "std") {
    return null;
  } 

  
  /**
   * Escapes up to nine values for SQL queries
   * => prepare("INSERT INTO table_name VALUES (%)", $value);
   * => prepare("INSERT INTO table_name VALUES (%1, %2)", $value1, $value2);
   */
  function prepare($sql) {
    $values = func_get_args();
    array_shift($values);
    $trans = array();
    for ($i = 0; $i < count($values); $i++) {
      $escaped = $this->escape($values[$i]);
      $quoted = "'$escaped'";
      if ($i == 0) {
        $trans["%"] = $quoted;
      }
      $key = $i+1;
      $trans["%$key"] = $quoted;
    }
    return strtr($sql, $trans);
  }

  
  /**
   * Prepares an IN where clause with a given array of values
   * Prepares a standard = where clause when alternate value is supplied
   */
  function prepareIn($values, $alternate = null) {
    return null;
  }
  
  

  function error($dsn = "std") {
    return null;
  }

  function errno($dsn = "std") {
    return null;
  }

  function insertId($dsn = "std") {
    return null;
  }

  function exec($sql, $dsn = "std") {
    return null;
  }

  
  function freeResult( $cur ) {
	return null;
  }

  function numRows( $qid ) {
	return null;
  }

  function affectedRows($dsn = "std" ) {
    return null;
  }

  function fetchRow( $cur ) {
	return null;
  }

  function fetchAssoc( $cur ) {
    return null;
  }

  function fetchArray( $cur  ) {
	return null;
  }

  function fetchObject( $cur  ) {
	return null;
  }

  function escape( $str ) {
	return null;
  }

  function version($dsn = "std") {
    return null;
  }


  function unix2dateTime( $time ) {
  	// converts a unix time stamp to the default date format
    return $time > 0 ? date("Y-m-d H:i:s", $time) : null;
  }

  function dateTime2unix( $time ) {
	if ($time == "0000-00-00 00:00:00") {
		return -1;
	}
	if( ! preg_match( "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})(.?)$/", $time, $a ) ) {
		return -1;
	} else {
		return mktime( $a[4], $a[5], $a[6], $a[2], $a[3], $a[1] );
	}
  }
  
}
?>