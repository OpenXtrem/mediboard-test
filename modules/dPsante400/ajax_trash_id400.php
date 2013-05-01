<?php /* $Id: $ */

/**
 * @package Mediboard
 * @subpackage dPsante400
 * @version $Revision: $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

$idex_id = CValue::get("idex_id");

$idex = new CIdSante400;
$idex->load($idex_id);

$filter = new CIdSante400;
$filter->object_class = $idex->object_class;
$filter->object_id    = $idex->object_id;

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id);
$idexs = $filter->loadMatchingList(CGroups::loadCurrent()->_id);

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id, "tag_dossier_cancel");
$idexs = array_merge($idexs, $filter->loadMatchingList());

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id, "tag_dossier_trash");
$idexs = array_merge($idexs, $filter->loadMatchingList());

$filter->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id, "tag_dossier_pa");
$idexs = array_merge($idexs, $filter->loadMatchingList());

$tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id);

// Chargement de l'objet afin de r�cup�rer l'id400 associ�
$object = new $filter->object_class;
$object->load($filter->object_id);
$object->loadNDA(CGroups::loadCurrent()->_id);

foreach ($idexs as $_idex) {
  // L'identifiant 400 coch�
  if ($_idex->_id == $idex_id) {
    $_idex->tag = CSejour::getTagNDA(CGroups::loadCurrent()->_id);
    if ($msg = $_idex->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR); 
    }
    continue;
  }
  // L'ancien est � mettre en trash
  if ($_idex->id400 == $object->_NDA) {
    $_idex->tag = CAppUI::conf("dPplanningOp CSejour tag_dossier_trash") .$tag;
    if ($msg = $_idex->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR); 
    }
  }
}