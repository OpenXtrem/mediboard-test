<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPdeveloppement
* @version $Revision$
* @author S�bastien Fillonneau
*/

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

$listeClasses = CApp::getInstalledClasses();

$result = array();
foreach ($listeClasses as $class){
  $object = new $class;
  if ($object->_spec->measureable) {
	  $sql = "SHOW TABLE STATUS LIKE '{$object->_spec->table}'";
	  $statusTable = $ds->loadList($sql);
	  if ($statusTable) {
	    $result[$class] = $statusTable[0];
	    $result[$class]["Update_relative"] = CMbDate::relative($result[$class]["Update_time"]);
	  }
	}
}

// Pour l'�tablissement courant
$etab = new CGroups;
$nb_etabs = $etab->countList();

if($nb_etabs > 1) {
	$etab = CGroups::loadCurrent();
	$current_group = $etab->_id;
	$res_current_etab = array();
	$where = array();
	$ljoin= array();
	
	// - Nombre de s�jours
	$tag_NDA = CSejour::getTagNDA($current_group);
	$where["tag"] = " = '$tag_NDA'";
	$where["object_class"] = " = 'CSejour'";
	$id400 = new CIdSante400;
	$res_current_etab["CSejour-_NDA"] = $id400->countList($where);
	
	// - Patients IPP
	$tag_ipp = CPatient::getTagIPP($current_group);
	$where["tag"] = " = '$tag_ipp'";
	$where["object_class"] = " = 'CPatient'";
	$id400 = new CIdSante400;
	$res_current_etab["CPatient-_IPP"] = $id400->countList($where);
	
	// - Nombre de consultations
	$where = array();
	$consultation = new CConsultation;
	$ljoin["plageconsult"]        = "consultation.plageconsult_id = plageconsult.plageconsult_id";
	$ljoin["users_mediboard"]     = "plageconsult.chir_id = users_mediboard.user_id";
	$ljoin["functions_mediboard"] = "users_mediboard.function_id = functions_mediboard.function_id";
	$where["functions_mediboard.group_id"] = " = $current_group";
	$res_current_etab["CConsultation"] = $consultation->countList($where, null, $ljoin);
	
	// - Lits
	$ljoin = array();
	$where = array();
	$lit = new CLit;
	$ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";
	$ljoin["service"] = "chambre.service_id = service.service_id";
	$where["service.group_id"] = " = $current_group";
	$res_current_etab["CLit"] = $lit->countList($where, null, $ljoin);
	
	// - Chambres
	$ljoin = array();
	$where = array();
	$chambre = new CChambre;
	$ljoin["service"] = "chambre.service_id = service.service_id";
	$where["service.group_id"] = " = $current_group";
	$res_current_etab["CChambre"] = $chambre->countList($where, null, $ljoin);
	
	// - Utilisateurs
	$ljoin = array();
	$where = array();
	$mediuser = new CMediusers;
	$ljoin["functions_mediboard"]   = "users_mediboard.function_id = functions_mediboard.function_id";
	$where["functions_mediboard.group_id"] = " = $current_group";
	$res_current_etab["CMediusers"] = $mediuser->countList($where, null, $ljoin);
	 
	// - Entr�es de journal
	$ljoin = array();
	$where = array();
	$user_log = new CUserLog;
	$ljoin["users_mediboard"] = "user_log.user_id = users_mediboard.user_id";
	$ljoin["functions_mediboard"] = "users_mediboard.function_id = functions_mediboard.function_id";
	$where["functions_mediboard.group_id"] = " = $current_group";
	$res_current_etab["CUserLog"] = $user_log->countList($where, null, $ljoin);
}

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("result" , $result);
$smarty->assign("etab", $etab);
if ($nb_etabs > 1) {
  $smarty->assign("res_current_etab", $res_current_etab);
}
$smarty->assign("nb_etabs", $nb_etabs);
$smarty->display("view_metrique.tpl");

?>