<?php /* $Id:  $ */

/**
 * @package Mediboard
 * @subpackage classes
 * @version $Revision: $
 * @author Romain Ollivier
 */

/**
 * Semaphore implementation to deal with concurrency
 */
class CMbSemaphore {
  
  var $key = null;
  
  /**
   * CMbSemaphore Constructor
   * @param string $key semaphore identifier
   */
  function __construct($key) {
    $lockPath = CAppUI::conf("root_dir")."/tmp/locks";
    CMbPath::forceDir($lockPath);
    $this->file = fopen("$lockPath/$file", "w+");
  }
  
  /**
   * Aquire the semaphore by putting a lock on it
   * @param float $timeout the max time in secondes to aquire the semaphore
   * @param float $step the step between each aquire attemp in seconds
   * @return boolean the job is done
   */
  function aquire($timeout = 5, $step = 0.1) {
    $i       = 0;
    $timeout = intval(max($timeout, 10) * 1000000);
    $step    = intval(max($step   , 10) * 1000000);
    while(!flock($this->key, LOCK_EX + LOCK_NB) && $i < $timeout) {
      usleep($step);
      $i += $step;
    }
    if($i >= $timeout) {
      return false;
    }
    return true;
  }
  
  /**
   * Release the lock on the semaphore
   * @return boolean the job is done
   */
  function release() {
    return flock($this->key, LOCK_UN);
  }
}

?>