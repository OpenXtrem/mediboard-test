<?php /* $Id: ajax_test_dsn.php 6069 2009-04-14 10:17:11Z phenxdesign $ */

/**
 * @package Mediboard
 * @subpackage sip
 * @version $Revision: 6069 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

global $can;

$can->needsAdmin();

// Check params
if (null == $ftpsn = CValue::get("ftpsn")) {
  CAppUI::stepAjax("Aucun FTPSN sp�cifi�", UI_MSG_ERROR);
}

$ftp = new CFTP();
$ftp->init($ftpsn);

if (!$ftp->testSocket()) {
  CAppUI::stepAjax("Connexion au serveur $ftp->hostname' �chou�e", UI_MSG_WARNING);
} else {
  CAppUI::stepAjax("Connect� au serveur $ftp->hostname sur le port $ftp->port");
}

if (!$ftp->connect()) {
  CAppUI::stepAjax("Impossible de se connecter au serveur $ftp->hostname", UI_MSG_ERROR);
} else {
  CAppUI::stepAjax("Connect� au serveur $ftp->hostname et authentifi� en tant que $ftp->username");
}

if($ftp->passif_mode) {
  CAppUI::stepAjax("Activation du mode passif");
}

$list = $ftp->getListFiles();
if (!is_array($list)) {
  CAppUI::stepAjax("Impossible de lister les fichiers", UI_MSG_ERROR);
} else {
  CAppUI::stepAjax("Liste des fichiers du dossier");
  mbTrace($list);
}

?>