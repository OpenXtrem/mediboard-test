<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage ecap
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CAppUI::requireModuleClass("dPsante400", "mouvement400");

class CMouvementEcap extends CMouvement400 {

  function __construct() {
    $this->base = "ECAPFILE";
    $this->markField = "ETAT";
    $this->idField = "INDEX";
    $this->typeField = "TRACTION";
  }
  
  function getFilterClause() {
    $group_id = CAppUI::conf("dPsante400 group_id");
    return $group_id ? "\n AND CIDC = '$group_id'" : "";
//    return $group_id ? "\n AND (B_CIDC = '$group_id' OR A_CIDC = '$group_id')" : "";
  }
}
?>
