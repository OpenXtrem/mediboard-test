<?php

/**
 * Patient Registry Get Demographics Query Response
 * A patient registry responds to a query with demographic information in the registry for the patient specified in the query
 *
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  SVN: $Id:$
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v3EventPRPAIN201308UV02
 * Patient Registry Get Demographics Query Response
 */
class CHL7v3EventPRPAIN201308UV02 extends CHL7v3AcknowledgmentPRPA implements CHL7EventPRPAST201317UV02 {
  /** @var string */
  public $interaction_id = "IN201308UV02";

  /**
   * Get interaction
   *
   * @return string|void
   */
  function getInteractionID() {
    return "{$this->event_type}_{$this->interaction_id}";
  }

  /**
   * Get acknowledgment status
   *
   * @return string
   */
  function getStatutAcknowledgment() {
    //Valeur fix�e � "AA"
    return "AA";
  }

  function getQueryACK() {
    // controlActProcess/queryAck/queryResponseCode/@code

  }
}