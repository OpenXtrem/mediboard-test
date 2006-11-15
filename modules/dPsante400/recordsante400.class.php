<?php

class CRecordSante400 {
  static $dsn = "sante400";
  static $user = null;
  static $pass = null;
  static $dbh = null;
  static $chrono = null;
 
  public $data = array();
  
  function __construct() {
  }

  function connect() {
    if (!self::$dbh) {
      $dsn = self::$dsn;
      global $dbChronos;
      $dbChronos[$dsn] = new Chronometer;
      self::$chrono =& $dbChronos[$dsn];
      self::$dbh = new PDO("odbc:$dsn", self::$user, self::$pass);
    }
  }

  function multipleLoad($sql, $max = 100, $class = "CRecordSante400") {
    if (!is_a(new $class, "CRecordSante400")) {
      trigger_error("instances of '$class' are not instances of 'CRecordSante400'", E_USER_WARNING);
    }
    
    $records = array();
    try {
      self::connect();
      if (null == $sth = self::$dbh->prepare($sql)) {
        throw new PDOException("Couldn't prepare query");
      }
      
      self::$chrono->start();
      $sth->execute();
      self::$chrono->stop();
      while ($data = $sth->fetch(PDO::FETCH_ASSOC) and $max--) {
        $record = new $class;
        $record->data = $data;
        $records[] = $record;
      }
    } catch (PDOException $e) {
      trigger_error("Error querying '$sql' on data source name '$dsn' : " . $e->getMessage(), E_USER_ERROR);
    }
    
    return $records;
  }
  
  function query($sql) {
    $dsn = self::$dsn;

    try {
      self::connect();
      if (null == $sth = self::$dbh->prepare($sql)) {
        throw new PDOException("Couldn't prepare query");
      }
      
      self::$chrono->start();
      $sth->execute();
      self::$chrono->stop();
      $this->data = $sth->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      trigger_error("Error querying '$sql' on data source name '$dsn' : " . $e->getMessage(), E_USER_ERROR);
    }
  }
  
  /**
   * Transforms a DDMMYYYY AS400 date into a YYYY-MM-DD SQL date 
   */
  function consumeDateInverse($valueName) {
    $date = $this->consume($valueName);
    return preg_replace("/(\d{1,2})(\d{2})(\d{4})/i", "$3-$2-$1", $date);
  }
  
  function consume($valueName) {
    if (!is_array($this->data)) {
      throw new Exception("The value '$valueName' doesn't exist in this record, which has NO value");
    }

    if (!array_key_exists($valueName, $this->data)) {
      throw new Exception("The value '$valueName' doesn't exist in this record");
    }
    
    $value = $this->data[$valueName];
    unset($this->data[$valueName]);
    return trim($value);    
  }

  function consumeTel($valueName) {
    $value = $this->consume($valueName);
    $value = preg_replace("/(\D)/", "", $value);
    if ($value) {
      $value = str_pad($value, 10, "0", STR_PAD_LEFT);
    }
    return $value;
  }

  function consumeMulti($valueName1, $valueName2) {
    $value1 = $this->consume($valueName1);
    $value2 = $this->consume($valueName2);
    return $value2 ? "$value1\n$value2" : "$value1";    
  }

  function consumeDate($valueName) {
    $date = $this->consume($valueName);
    if ($date == "0") {
      return null;
    }
    
    $reg = "/(\d{4})(\d{2})(\d{2})/i";
    return preg_replace($reg, "$1-$2-$3", $date);
  }

  function consumeTime($valueName) {
    $time = $this->consume($valueName);
    if (!$time === "0") {
      return null;
    }
    
    if (strlen($time) == 2) {
      $time = "00" . $time;
    }
    
    $reg = "/(\d{0,2})(\d{2})/i";
    return preg_replace($reg, "$1:$2:00", $time);
  }

  function consumeDateTime($dateName, $timeName) {
    $date = $this->consumeDate($dateName);
    $time = $this->consumeTime($timeName);
    
    return $time ? "$date $time" : $date;
  }
}
?>
