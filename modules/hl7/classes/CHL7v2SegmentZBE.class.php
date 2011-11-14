<?php

/**
 * Represents an HL7 ZBE message segment (Movement) - HL7
 *  
 * @category HL7
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CHL7v2SegmentZBE
 * ZBE - Represents an HL7 ZBE message segment (Movement)
 */

class CHL7v2SegmentZBE extends CHL7v2Segment {
  static $actions = array(
    "INSERT" => array(
      "A05", "A01", "A14", "A04", "A06", "A07", "A54", "A02", "A15", 
      "A03", "A16", "A21", "A22", "Z80", "Z83", "Z84", "Z86", "Z88"
    ),
    "UPDATE" => array(
      "Z99"
    ),
    "CANCEL" => array(
      "A38", "A11", "A27", "A06", "A07", "A55", "A12", "A26", "A13", 
      "A25", "A52", "A53", "Z81", "Z83", "Z85", "Z87", "Z89"
    ),
  );
  
  var $name   = "ZBE";
  
  /**
   * @var CSejour
   */
  var $sejour = null;
  
  /**
   * @var CUniteFonctionnelle
   */
  var $uf     = null;
  
  function build(CHL7v2Event $event) {
    $data[] = null;

    $sejour = $this->sejour;
    $uf     = $this->uf;
    $uf->loadLastLog();
    
    parent::build($event);

    // ZBE-1: Movement ID (EI) (optional)
    $data[] = array (
      // Entity identifier
      $uf->_ref_last_log->id,
      // Namespace ID
      "Mediboard",
      // Universal ID
      CAppUI::conf("hl7 assigningAuthorityUniversalID"),
      // Universal ID Type
      "OX"
    );
    
    // ZBE-2: Start of Movement Date/Time (TS)
    $data[] = $sejour->entree_prevue;
    
    // ZBE-3: End of Movement Date/Time (TS) (optional)
    $data[] = $sejour->sortie_prevue;
    
    // ZBE-4: Action on the Movement (ID)
    $action_movement = null;
    foreach (self::$actions as $action => $events) {
      if (in_array($event->code, $events)) {
        $action_movement = $action;
      }
    };
    $data[] = $action_movement;
    
    // ZBE-5: Indicator "Historical Movement" (ID) 
    $data[] = "N";
    
    // ZBE-6: Original trigger event code (ID) (optional)
    $data[] = ($action_movement == "UPDATE" || $action_movement == "UPDATE") ? null : null;
    
    // ZBE-7: Ward of medical responsibility in the period starting with this movement (XON) (optional)
    $data[] = array(
      // ZBE-7.1 : Libell� de l'UF
      $uf->libelle,
      // ZBE-7.6 : Identifiant de l'autorit� d'affectation  qui a attribu� l'identifiant de l'UF de responsabilit� m�dicale
      $this->getAssigningAuthority("mediboard"),
      // ZBE-7.7 : La seule valeur utilisable de la table 203 est "UF"
      "UF",
      // ZBE-7.10 : Identifiant de l'UF de responsabilit� m�dicale
      $uf->code
    );
    
    // ZBE-8: Ward of care responsibility in the period starting with this movement (XON) (optional)
    $data[] = null;
    
    // ZBE-9: Nature of this movement (CWE)
    $data[] = null;
    
    $this->fill($data);
  }
}

?>