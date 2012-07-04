<?php /** $Id:$ **/

/**
 * @category Cli
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  SVN: $Id:$
 * @link     http://www.mediboard.org
 */

require_once "utils.php";
require_once "Task.class.php";

/**
 * Procedure: Enable you to set up a list a treatments to apply to one Task
 */
class Procedure extends Task
{
  /**
   * Constructor
   * 
   * @return 
   */
  function Procedure() {
  }
  
  /**
   * Create a Question
   * 
   * @param object $question Question to ask for
   * @param object $default  [optional] Default value for the answer
   * 
   * @return object
   */
  function createQuestion( $question, $default = null ) {
    return new Question($question, $default);
  }
  
  /**
   * Ask for a Question
   * 
   * @param object $question The Question to ask for
   * 
   * @return string
   */
  function askQuestion( $question ) {
    if ( $question instanceOf Question ) {
      return recup($question->qt, $question->def);
    }
  }
}

?>