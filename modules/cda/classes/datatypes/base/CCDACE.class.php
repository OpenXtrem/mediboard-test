<?php

/**
 * $Id$
 *  
 * @category CDA
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @link     http://www.mediboard.org */
 
/**
 * Coded data, consists of a coded value (CV)
 * and, optionally, coded value(s) from other coding systems
 * that identify the same concept. Used when alternative
 * codes may exist.
 */
class CCDACE extends CCDACD {

  
  /**
	 * Get the properties of our class as strings
	 *
	 * @return array
	 */
  function getProps() {
    $props = parent::getProps();
    //$props["qualifier"] = "CCDACR xml|element prohibited";
    return $props;
  }
}
