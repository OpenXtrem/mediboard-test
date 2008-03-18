<?php

/**
* @package Mediboard
* @subpackage dPmedicament
* @version $Revision: $
* @author Alexis Granger
*/

global $AppUI, $can;

$can->needsAdmin();

set_time_limit(360);
ini_set("memory_limit", "128M");

$sourcePath = "modules/dPprescription/sql/association_moment.tar.gz";
$targetDir = "tmp/prescription/";
$targetTables = "tmp/prescription/association_moment.sql";

// Extract the SQL dump
if (null == $nbFiles = CMbPath::extract($sourcePath, $targetDir)) {
  $AppUI->stepAjax("Erreur, impossible d'extraire l'archive", UI_MSG_ERROR);
}

$AppUI->stepAjax("Extraction de $nbFiles fichier(s)", UI_MSG_OK);

$ds = CSQLDataSource::get("std");

// Cr�ation de la table
if (null == $lineCount = $ds->queryDump($targetTables, true)) {
  $msg = $ds->error();
  $AppUI->stepAjax("Import des tables - erreur de requ�te SQL: $msg", UI_MSG_ERROR);
}
$AppUI->stepAjax("Table import�e", UI_MSG_OK);

?>