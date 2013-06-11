<?php

/**
 * Event HL7v3
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v3Event
 * Event HL7v3
 */
class CHL7v3Event extends CHL7Event {
  /** @var  CHL7v3MessageXML $dom */
  public $dom;

  /** @var CExchangeHL7v3 */
  public $_exchange_hl7v3;

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    $this->dom = new CHL7v3MessageXML();
  }

  /**
   * Generate exchange HL7v3
   *
   * @return CExchangeHL7v3
   */
  function generateExchange() {
  }

  /**
   * Update exchange HL7v3 with
   *
   * @return CExchangeHL7v3
   */
  function updateExchange() {
  }
}