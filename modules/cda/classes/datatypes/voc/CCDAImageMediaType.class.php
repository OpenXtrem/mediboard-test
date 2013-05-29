<?php

/**
 * $Id$
 *
 * @category CDA
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org
 */

/**
 * abstDomain: V14839 (C-0-D14824-V14839-cpt)
 */
class CCDAImageMediaType extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'image/g3fax',
    'image/gif',
    'image/jpeg',
    'image/png',
    'image/tiff',
  );
  public $_union = array (
  );


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|".implode("|", $this->getEnumeration(true));
    return $props;
  }
}