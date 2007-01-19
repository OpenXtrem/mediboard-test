<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage dPospi
* @version $Revision: $
* @author S�bastien Fillonneau
*/

global $AppUI, $canRead, $canEdit, $m, $g;

if(!$canRead) {
  $AppUI->redirect( "m=system&a=access_denied" );
}

$service_id = mbGetValueFromGetOrSession("service_id", 0); 
$date_suivi = mbGetValueFromGetOrSession("date_suivi", mbDate());
$listOps = array();

// Liste des services
$services = new CService;
$where = array();
$where["group_id"] = "= '$g'";
$order = "nom";
$services = $services->loadListWithPerms(PERM_READ,$where, $order);

if($service_id!=0 && array_key_exists($service_id,$services)){
  $listService = array($service_id);
}else{
  mbSetValueToSession("service_id", 0);
  $service_id = 0;
  $listService = array_keys($services);
}


// Selection des plages op�ratoires de la journ�e
$plages = new CPlageOp;
$where = array();
$where["date"] = "= '$date_suivi'";
$plages = $plages->loadList($where);

// Listes des op�rations
$listOps = new COperation;
$where = array();
$where[] = "`plageop_id` ".db_prepare_in(array_keys($plages))." OR (`plageop_id` IS NULL AND `date` = '$date_suivi')";
$where["annulee"] = "= '0'";
$order = "time_operation";
$listOps = $listOps->loadList($where,$order);
foreach($listOps as $key => $value) {
	$oper =& $listOps[$key];
  // Chargement des infos de l'op�ration
  $oper->loadRefChir();
  $oper->loadRefSejour();
  $oper->_ref_sejour->loadRefsFwd();
  $oper->_ref_sejour->loadRefsAffectations();
  $oper->_ref_sejour->_curr_affectation = null;
  if(!count($oper->_ref_sejour->_ref_affectations)){
    unset($listOps[$key]);
  }
} 

   
$affOper = array();
// Classement pour le/les services
foreach($listService as $currService){
  $affOper[$currService] = $listOps;
  $listOpService =& $affOper[$currService];
  
  //Liste des lits du service
  $table    = "lit";
  $select   = "lit_id";
  $leftjoin = array("chambre"     => "chambre.chambre_id = lit.chambre_id");
  $where    = array("service_id"=>"= '$currService'");  
  $request  = new CRequest();
  $request->addTable($table);
  $request->addSelect($select);
  $request->addLJoin($leftjoin);
  $request->addWhere($where);
  $resultLit = db_loadList($request->getRequest());  
  
  $listLit = array();
  foreach($resultLit as $aLit){
    $listLit[] = $aLit["lit_id"]; 
  }
  
  foreach($listOpService as $key => $value) {
    $oper =& $listOpService[$key];
    foreach($oper->_ref_sejour->_ref_affectations as $keyAff=>$currAff){
      $affect =& $oper->_ref_sejour->_ref_affectations[$keyAff];          
      if(mbDate($affect->entree) <= $date_suivi && mbDate($affect->sortie) >= $date_suivi && in_array($affect->lit_id,$listLit)){
        $affect->loadRefLit();
        $affect->_ref_lit->loadCompleteView();
        $oper->_ref_sejour->_curr_affectation =& $affect;
      }
    }
    if(!$oper->_ref_sejour->_curr_affectation){
      unset($listOpService[$key]);
    }
  }
}


// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("date_suivi"  , $date_suivi);
$smarty->assign("affOper"     , $affOper);
$smarty->assign("services"    , $services);
$smarty->assign("service_id"  , $service_id);

$smarty->display("vw_suivi_bloc.tpl");
?>