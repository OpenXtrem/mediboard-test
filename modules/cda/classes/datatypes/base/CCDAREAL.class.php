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
 * Fractional numbers. Typically used whenever quantities
 * are measured, estimated, or computed from other real
 * numbers.  The typical representation is decimal, where
 * the number of significant decimal digits is known as the
 * precision. Real numbers are needed beyond integers
 * whenever quantities of the real world are measured,
 * estimated, or computed from other real numbers. The term
 * "Real number" in this specification is used to mean
 * that fractional values are covered without necessarily
 * implying the full set of the mathematical real numbers.
 */
class CCDAREAL extends CCDAQTY {


  /**
   * Setter value
   *
   * @param String $value String
   *
   * @return void
   */
  public function setValue($value) {
    if (!$value) {
      $this->value = null;
      return;
    }
    $real = new CCDA_base_real();
    $real->setData($value);
    $this->value = $real;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["value"] = "CCDA_base_real xml|attribute";
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

    $this->setValue("test");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur correcte
     */

    $this->setValue("10.5");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
