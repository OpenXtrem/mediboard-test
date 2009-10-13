<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage classes
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

class CMbObjectSpec {
  // Specification fields
  public $incremented         = true;
  public $loggable            = true;
  public $nullifyEmptyStrings = true;
  public $dsn                 = 'std';
  public $table               = null;
  public $key                 = null;
  public $measureable         = false;
  public $uniques             = array();
  public $xor                 = array();
  
  // Derivate fields
  public $ds = null;
  
  /**
   * Initialize derivate fields
   */
  public function init() {
    $this->ds = CSQLDataSource::get($this->dsn);
  }
}
