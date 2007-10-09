<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage dPbloc
* @version $Revision: $
* @author Sébastien Fillonneau
*/

global $AppUI, $can, $m, $g;

$date = mbGetValueFromGetOrSession("date", mbDate());
$plageop_id = mbGetValueFromGetOrSession("plageop_id");

// Liste des Salles
$salle = new CSalle();
$where = array();
$where["group_id"] = "= '$g'";
$order = "'nom'";
$listSalles = $salle->loadListWithPerms(PERM_READ, $where, $order);


// Informations sur la plage demandée
$plagesel = new CPlageOp;
$plagesel->load($plageop_id);
if($plagesel->plageop_id){
  $arrKeySalle = array_keys($listSalles);
  if(!in_array($plagesel->salle_id,$arrKeySalle) || $plagesel->date!=$date) {
    $plageop_id = 0;
    $plagesel = new CPlageOp;
  }
}


// Liste des Specialités
$function = new CFunctions;
$specs = $function->loadSpecialites(PERM_READ);
foreach($specs as $key => $spec) {
  $specs[$key]->loadRefsUsers(array("Chirurgien", "Anesthésiste"));
}

// Liste des Anesthésistes
$mediuser = new CMediusers;
$anesths = $mediuser->loadAnesthesistes();


$_temps_inter_op = range(0,59,15);


// Récupération des plages pour le jour demandé
$listPlages = new CPlageOp();
$where = array();
$where["date"] = "= '$date'";
$order = "debut";
$listPlages = $listPlages->loadList($where,$order);

// Détermination des bornes du semainier
$min = CPlageOp::$hours_start.":".reset(CPlageOp::$minutes).":00";
$max = CPlageOp::$hours_stop.":".end(CPlageOp::$minutes).":00";


// Détermination des bornes de chaque plage
foreach($listPlages as &$plage){
  $plage->loadRefsFwd();
  $plage->_ref_chir->loadRefsFwd();
  $plage->getNbOperations();
  
  $plage->fin = min($plage->fin, $max);
  $plage->debut = max($plage->debut, $min);
  
  $plage->updateFormFields();
  $plage->makeView();
  
  if($plage->debut >= $plage->fin){  
    unset($listPlages[$plage->_id]);
  }  
}

// Création du tableau de visualisation vide
$affichages = array();
foreach($listSalles as $keySalle=>$valSalle){
  foreach(CPlageOp::$hours as $keyHours=>$valHours){
    foreach(CPlageOp::$minutes as $keyMins=>$valMins){
      // Initialisation du tableau
      $affichages["$keySalle-$valHours:$valMins:00"] = "empty";
    }
  }
}

// Remplissage du tableau de visualisation
foreach($listPlages as &$plage){
    $plage->_nbQuartHeure = mbTimeCountIntervals($plage->debut, $plage->fin, "00:".CPlageOp::$minutes_interval.":00");
    for($time = $plage->debut; $time < $plage->fin; $time = mbTime("+15 minutes", $time) ){
      $affichages[$plage->salle_id."-".$time] = "full";
    } 
    $affichages[$plage->salle_id."-".$plage->debut] = $plage->_id;
}

// Liste des Spécialités
$listSpec = new CFunctions();
$listSpec = $listSpec->loadSpecialites();

//Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPlages"     ,$listPlages);
$smarty->assign("_temps_inter_op", $_temps_inter_op   );
$smarty->assign("listSalles"     , $listSalles        );
$smarty->assign("listHours"      , CPlageOp::$hours   );
$smarty->assign("listMins"       , CPlageOp::$minutes );
$smarty->assign("affichages"     , $affichages    );
$smarty->assign("date"           , $date              );
$smarty->assign("listSpec"       , $listSpec          );
$smarty->assign("plagesel"       , $plagesel          );
$smarty->assign("specs"          , $specs             );
$smarty->assign("anesths"        , $anesths           );

$smarty->display("vw_edit_planning.tpl");
?>
