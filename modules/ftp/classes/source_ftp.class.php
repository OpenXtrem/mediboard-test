<?php

/**
 * Source FTP
 *  
 * @category FTP
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

class CSourceFTP extends CExchangeSource {
  // DB Table key
  var $source_ftp_id = null;
  
  // DB Fields
  var $port          = null;
  var $timeout       = null;
  var $pasv          = null;
  var $mode          = null;
  var $fileprefix    = null;
  var $fileextension = null;
  var $filenbroll    = null;
  var $fileextension_write_end = null;
  var $counter       = null;
  
  var $_source_file      = null;
  var $_destination_file = null;
  
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'source_ftp';
    $spec->key   = 'source_ftp_id';
    return $spec;
  }

  function getProps() {
    $specs = parent::getProps();
    $specs["port"]       = "num default|21";
    $specs["timeout"]    = "num default|90";
    $specs["pasv"]       = "bool default|0";
    $specs["mode"]       = "enum list|FTP_ASCII|FTP_BINARY default|FTP_ASCII";
    $specs["counter"]    = "str protected";
    $specs["fileprefix"] = "str";
    $specs["fileextension"] = "str";
    $specs["filenbroll"]    = "enum list|1|2|3|4";
    $specs["fileextension_write_end"] = "str";
    
    return $specs;
  }
  
  function send() {
    $ftp = new CFTP();
    $ftp->init($this);
    
    $this->counter++;
      
    $destination_basename = sprintf("%s%0".$this->filenbroll."d", $this->fileprefix, $this->counter % pow(10, $this->filenbroll));
    
    if ($ftp->connect()) {
      $ftp->sendContent($this->_data, "$destination_basename.$this->fileextension");
      if ($this->fileextension_write_end) {
        $ftp->sendContent($this->_data, "$destination_basename.$this->fileextension_write_end");
      }
      $ftp->close();
      
      $this->store();
			
      return true;
    }
  }
  
  function getACQ() {}
  
  function receive() {
    $extension = $this->fileextension;

    $ftp = new CFTP();
    $ftp->init($this);

    try {
      $ftp->connect();
    } catch (CMbException $e) {
      CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING); 
    }
    
    $files = array();
    try {
      $files = $ftp->getListFiles("./".$ftp->fileprefix);
    } catch (CMbException $e) {
      CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING); 
    }
    
    if (empty($files)) {
      CAppUI::stepAjax("Le répertoire ne contient aucun fichier", UI_MSG_ERROR);
    }
    
    foreach($files as $filepath) {
      if (substr($filepath, -(strlen($extension))) == $extension) {
        $filename = basename($filepath);
        
        $file = $ftp->getFile($filepath, "tmp/ftp_files/$filename");
        mbTrace($file);
      }
    }
    
  }
  
  function isReachableSource() {
    $ftp = new CFTP();
    $ftp->init($this);
    
    try {
      $ftp->testSocket();
    } 
    catch (CMbException $e) {
      $this->_reachable = 0;
      $this->_message   = $e->getMessage();
      return false;
    }
    return true;
  }
  
  function isAuthentificate() {
    $ftp = new CFTP();
    $ftp->init($this);
    
    try {
      $ftp->connect();
    } 
    catch (CMbException $e) {
      $this->_reachable = 0;
      $this->_message   = $e->getMessage();
      return false;
    }
    return true;
  }
  
  function getResponseTime() {
    $this->_response_time = url_response_time($this->host, $this->port);
  }
}
?>