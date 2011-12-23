<?php

/**
 * A11 - Cancel admit/visit notification - HL7
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v2EventADTA11
 * A11 - Cancel admit/visit notification
 */
class CHL7v2EventADTA11 extends CHL7v2EventADT implements CHL7EventADTA09 {
  function __construct($i18n = null) {
    parent::__construct($i18n);
        
    $this->code        = "A11";
    $this->transaction = CPAM::getTransaction($this->code);
    $this->msg_codes   = array (
      array(
        $this->event_type, $this->code, "{$this->event_type}_A09"
      )
    );
  }
  
  function build($sejour) {
    parent::build($sejour);
    
    $patient = $sejour->_ref_patient;
    // Patient Identification
    $this->addPID($patient, $sejour);
    
    // Patient Additional Demographic
    $this->addPD1($patient);
    
    // Doctors
    $this->addROLs($patient);
    
    // Next of Kin / Associated Parties
    $this->addNK1s($patient);
    
    // Patient Visit
    $this->addPV1($sejour);
    
    // Patient Visit - Additionale Info
    $this->addPV2($sejour);
  }
  
}

?>