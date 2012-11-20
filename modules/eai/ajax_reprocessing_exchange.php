<?php 
/**
 * Reprocessing exchange
 *  
 * @category EAI
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

 set_time_limit(240);
set_min_memory_limit("512M");

CCanDo::checkRead();

$exchange_guid = CValue::get("exchange_guid");

// Chargement de l'�change demand�
$exchange = CMbObject::loadFromGuid($exchange_guid);

try {
  $exchange->reprocessing();
}
catch (CMbException $e) {
  $e->stepAjax(UI_MSG_ERROR);
}

if (!$exchange->_id) {
  CAppUI::stepAjax("CExchangeAny-msg-delete", UI_MSG_ALERT);
}

CAppUI::stepAjax("CExchangeDataFormat-reprocessed");

?>