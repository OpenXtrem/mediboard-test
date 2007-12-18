<?php

/**
 *	@package Mediboard
 *	@subpackage dPpatients
 *	@version $Revision: 
 *  @author Alexis Granger
 */

global $AppUI;

$do = new CDoObjectAddEdit("CDossierMedical", "dossier_medical_id");
$do->createMsg = $AppUI->_("msg-CIdDossierMedical-create");
$do->modifyMsg = $AppUI->_("msg-CIdDossierMedical-modify");
$do->deleteMsg = $AppUI->_("msg-CIdDossierMedical-delete");

if($_POST["del"] == 0){
  // calcul de la valeur de l'id du dossier medical du patient
  $_POST["dossier_medical_id"] = CDossierMedical::dossierMedicalId($_POST["object_id"],$_POST["object_class"]);
}

$do->doIt();

?>