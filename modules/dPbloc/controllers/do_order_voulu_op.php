<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPbloc
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

$plageop_id = CValue::post("plageop_id");

$plageop = new CPlageOp();
$plageop->load($plageop_id);

$plageop->loadRefsOperations(false, "rank, rank_voulu, horaire_voulu", true);

foreach($plageop->_ref_operations as $_id => $_interv) {
  if (!$_interv->rank && 
      !$_interv->rank_voulu && 
      !$_interv->horaire_voulu) {
    unset($plageop->_ref_operations[$_id]);
  }
}

if (!empty($plageop->_ref_operations)) {
  $plageop->reorderOp(CPlageOp::RANK_VALIDATE);
}

CAppUI::stepAjax("Placement effectu�");
CApp::rip();
