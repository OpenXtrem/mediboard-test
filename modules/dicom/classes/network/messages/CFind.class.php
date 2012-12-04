<?php /** $Id$ **/

/**
 * @package Mediboard
 * @subpackage dicom
 * @version $Revision$
 * @author SARL OpenXtrem
 */

/**
 * The Find message family
 */
class CFind {
  static $evenements = array(
    "C-Find-RQ"         => "CDicomMessageCFindRQ",
    "C-Find-RSP"        => "CDicomMessageCFindRSP",
    "C-Cancel-Find-RQ"  => "CDicomMessageCCancelFindRQ",
    "Datas"             => "CDicomMessageCFindData",
  );

  /**
   * The constructor
   *
   * @return \CFind
   */
  function __construct() {
    $this->type = "Find";
  }
  
  /**
   * Retrieve events list of data format
   * 
   * @return string[] Events list
   */
  function getEvenements() {
    return self::$evenements;
  }
}