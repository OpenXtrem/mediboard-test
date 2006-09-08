<?php /* $Id$ */

/**
 * 
 * une modification
* @package Mediboard
* @subpackage dPccam
* @version $Revision$
* @author Romain Ollivier
*/

require_once($AppUI->getSystemClass("mbobject"));

/**
 * The CFavoriCCAM Class
 */
class CFavoriCCAM extends CMbObject {
  // DB Table key
	var $favoris_id = NULL;
  
  // DB References
	var $favoris_user = NULL;

  // DB fields
  var $favoris_code = NULL;

	function CFavoriCCAM() {
		$this->CMbObject("ccamfavoris", "favoris_id");
    
    $this->_props["favoris_user"] = "ref|notNull";
    $this->_props["favoris_code"] = "str|notNull";
    
    $this->_seek["favoris_code"] = "equal";
	}

  function check() {
    $sql = "SELECT *" .
      "\nFROM `ccamfavoris`" .
      "\nWHERE `favoris_code` = '$this->favoris_code'" .
      "\nAND `favoris_user` = '$this->favoris_user'";
    $copies = db_loadList($sql);

    if (count($copies))
      return "le favori existe d�j�";
    
     return parent::check();
 }
}
?>