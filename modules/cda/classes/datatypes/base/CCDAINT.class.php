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
 * Integer numbers (-1,0,1,2, 100, 3398129, etc.) are precise
 * numbers that are results of counting and enumerating.
 * Integer numbers are discrete, the set of integers is
 * infinite but countable.  No arbitrary limit is imposed on
 * the range of integer numbers. Two NULL flavors are
 * defined for the positive and negative infinity.
 */
class CCDAINT extends CCDAQTY {

  /**
   * Setter value
   *
   * @param mixed $value mixed
   *
   * @return void
   */
  public function setValue($value) {
    if (!$value && $value !== 0) {
      $this->value = null;
      return;
    }
    $int = new CCDA_base_int();
    $int->setData($value);
    $this->value = $int;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["value"] = "CCDA_base_int xml|attribute";
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
     * Test avec une valeur incorrecte
     */

    $this->setValue("10.25");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur correcte
     */

    $this->setValue("10");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
