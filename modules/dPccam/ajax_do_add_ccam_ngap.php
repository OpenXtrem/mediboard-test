<?php /* $Id: */

/**
* @package Mediboard
* @subpackage dPcompteRendu
* @version $Revision:$
* @author SARL Openxtrem
*/

CCanDo::checkAdmin();

set_time_limit(360);

$sourcePath = "modules/dPccam/base/ccam_ngap.tar.gz";
$targetDir = "tmp/ccam_ngap";
$targetTables = "tmp/ccam_ngap/ccam_ngap.sql";

// Extract the SQL dump
if (null == $nbFiles = CMbPath::extract($sourcePath, $targetDir)) {
  CAppUI::stepAjax("Erreur, impossible d'extraire l'archive", UI_MSG_ERROR);
} 

CAppUI::stepAjax("Extraction de $nbFiles fichier(s)", UI_MSG_OK);

$ds = CSQLDataSource::get("ccamV2");

// Cr�ation de la table
if (null == $lineCount = $ds->queryDump($targetTables, true)) {
  $msg = $ds->error();
  CAppUI::stepAjax("Import des tables - erreur de requ�te SQL: $msg", UI_MSG_ERROR);
}
CAppUI::stepAjax("Table import�e", UI_MSG_OK);
?>