<?php 

/**
 * $Id$
 *  
 * @category System
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
 */

class CFirstNameAssociativeSex extends CMbObject {
  public $first_name_id;
  public $firstname;
  public $sex;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table       = 'firstname_to_gender';
    $spec->key         = 'first_name_id';
    $spec->loggable = false;
    return $spec;
  }


  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["firstname"] = "str notNull";
    $specs["sex"]       = "enum list|f|m|u notNull default|u";
    return $specs;
  }

  /**
   * return the sex if found for firstname, else return null
   *
   * @param string $firstname the firstname to have
   *
   * @return string|null sex (u = undefined, f = female, m = male, null = not in base)
   */
  static function getSexFor($firstname) {
    $prenom_exploded = preg_split('/[-_ ]+/', $firstname);   // get the first firstname of composed one

    $sex_found = array();
    foreach ($prenom_exploded as $_pre) {
      $object = new self();
      $object->firstname = trim($_pre);
      $object->loadMatchingObject();
      $sex_found[$_pre] = $object->sex;
    }
    CMbArray::removeValue("", $sex_found);

    $found = "u";
    foreach ($sex_found as $_found) {
      if ($_found != "u") {
        if ($found == "u") {
          $found = $_found;
          continue;
        }

        if ($_found != $found) {
          $found = "u";
          continue;
        }
      }
    }
    return $found;
  }
}