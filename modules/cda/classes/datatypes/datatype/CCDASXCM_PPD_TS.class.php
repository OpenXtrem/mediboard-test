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
 * CCDASXCM_PPD_TS class
 */
class CCDASXCM_PPD_TS extends CCDAPPD_TS {

  /**
   *  A code specifying whether the set component is included
   * (union) or excluded (set-difference) from the set, or
   * other set operations with the current set component and
   * the set as constructed from the representation stream
   * up to the current point.
   *
   * @var CCDASetOperator
   */
  public $operator;

  /**
   * retourne le nom du type CDA
   *
   * @return string
   */
  function getNameClass() {
    $name = get_class($this);
    $name = substr($name, 4);

    return $name;
  }

  /**
   * Setter Operator
   *
   * @param String $operator String
   *
   * @return void
   */
  public function setOperator($operator) {
    if (!$operator) {
      $this->operator = null;
      return;
    }
    $op = new CCDASetOperator();
    $op->setData($operator);
    $this->operator = $op;
  }

  /**
   * Getter Operator
   *
   * @return \CCDASetOperator
   */
  public function getOperator() {
    return $this->operator;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["operator"] = "CCDASetOperator xml|attribute default|I";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = parent::test();

    /**
     * Test avec un operator incorrecte
     */

    $this->setOperator("TESTTEST");
    $tabTest[] = $this->sample("Test avec un operator incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un operator correcte
     */

    $this->setOperator("I");
    $tabTest[] = $this->sample("Test avec un operator correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
