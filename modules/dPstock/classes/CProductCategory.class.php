<?php
/**
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage stock
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    $Revision$
 */

/**
 * Product Category
 */
class CProductCategory extends CMbObject {
  public $category_id;
  
  // DB fields
  public $name;
  
  public $_count_products;

  /** @var CProduct[] */
  public $_ref_products;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'product_category';
    $spec->key   = 'category_id';
    return $spec;
  }

  /**
   * @see parent::getBackProps()
   */
  function getBackProps() {
    $backProps = parent::getBackProps();
    $backProps['products'] = 'CProduct category_id';
    return $backProps;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs['name'] = 'str notNull maxLength|50 seekable show|0';
    $specs['_count_products'] = 'num show|1';
    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->name;
  }

  /**
   * @see parent::loadView()
   */
  function loadView(){
    parent::loadView();
    
    $this->countProducts();
  }

  /**
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    $this->_ref_products = $this->loadBackRefs('products');
  }

  /**
   * Count products
   *
   * @return int
   */
  function countProducts(){
    return $this->_count_products = $this->countBackRefs("products");
  }
}
