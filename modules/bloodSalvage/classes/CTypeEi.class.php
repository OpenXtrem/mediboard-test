<?php
/**
 * $Id:$
 *
 * @package    Mediboard
 * @subpackage bloodSalvage
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision:$
 */

/**
 * CTypeEi
 */
class CTypeEi extends CMbObject {
  public $type_ei_id;

  //DB Fields
  public $name;
  public $concerne;
  public $desc;
  public $type_signalement;
  public $evenements;

  /** @var array */
  public $_ref_evenement;

  /** @var CEiItem[] */
  public $_ref_items;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'type_ei';
    $spec->key   = 'type_ei_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["name"]     = "str notNull maxLength|30";
    $props["concerne"] = "enum notNull list|pat|vis|pers|med|mat";
    $props["desc"]     = "text";
    $props["type_signalement"] = "enum notNull list|inc|ris";
    $props["evenements"] = "str notNull maxLength|255";
    return $props;
  }

  /**
   * @see parent::getBackProps()
   */
  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps["blood_salvages"] = "CBloodSalvage type_ei_id";
    return $backProps;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->name;

    if ($this->evenements) {
      $this->_ref_evenement = explode("|", $this->evenements);
    } 
  }

  /**
   * Chargement des items
   *
   * @return CEiItem[]
   */
  function loadRefItems() {
    $this->_ref_items = array();

    foreach ($this->_ref_evenement as $evenement) {
      $ext_item = new CEiItem();
      $ext_item->load($evenement);
      $this->_ref_items[] = $ext_item;
    }

    return $this->_ref_items;
  }
}
