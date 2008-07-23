<?php /* $Id: graph_activite.php 4401 2008-07-01 16:52:01Z mytto $ */

/**
 * @package Mediboard
 * @subpackage dPstats
 * @version $Revision: 4401 $
 * @author Romain Ollivier
 */

global $m, $debutact, $finact, $prat_id, $salle_id, $discipline_id, $codes_ccam;

$debutact      = mbGetValueFromGet("debut", mbDate("-1 YEAR"));
$rectif        = mbTransformTime("+0 DAY", $debutact, "%d")-1;
$debutact      = mbDate("-$rectif DAYS", $debutact);

$finact        = mbGetValueFromGet("fin", mbDate());
$rectif        = mbTransformTime("+0 DAY", $finact, "%d")-1;
$finact        = mbDate("-$rectif DAYS", $finact);
$finact        = mbDate("+ 1 MONTH", $finact);
$finact        = mbDate("-1 DAY", $finact);

$prat_id       = mbGetValueFromGet("prat_id");

CAppUI::requireSystemClass("mbGraph");

$total = 0;

$pratSel = new CMediusers;
$pratSel->load($prat_id);

for($i = $debutact; $i <= $finact; $i = mbDate("+1 MONTH", $i)) {
  $nameMonth = mbTransformTime("+0 DAY", $i, "%m/%Y");
  $datax[] = $nameMonth; 
}

$ds = CSQLDataSource::get("std");
$consults = array("data" => array(), "total" => 0);
  
$sql = "SELECT COUNT(consultation.consultation_id) AS total,
  DATE_FORMAT(plageconsult.date, '%m/%Y') AS mois,
  DATE_FORMAT(plageconsult.date, '%Y%m') AS orderitem
  FROM consultation
  INNER JOIN plageconsult
  ON consultation.plageconsult_id = plageconsult.plageconsult_id
  INNER JOIN users_mediboard
  ON plageconsult.chir_id = users_mediboard.user_id
  WHERE plageconsult.date BETWEEN '$debutact' AND '$finact'
  AND consultation.annule = '0'";
if($prat_id) {
  $sql .= "\nAND plageconsult.chir_id = '$prat_id'";
}
$sql .= "\nGROUP BY mois
  ORDER BY orderitem";
$result = $ds->loadlist($sql);
foreach($datax as $x) {
  foreach($result as $totaux) {
    if($x == $totaux["mois"]) {        
      $consults["data"][] = $totaux["total"];
      $consults["total"] += $totaux["total"];
      $total += $totaux["total"];
    }
  }
}

// Set up the title for the graph
$title = "Nombre de consultations";
$subtitle = "- $total consultations -";

if($prat_id) {
  $subtitle .= " Dr $pratSel->_view -";
}

$options = array( "width" => 480,
									"height" => 300,
									"title" => $title,
	                "subtitle" => $subtitle,
									"sizeFontTitle" => 10,
	                "margin" => array(50,50,50,70),
									"sizeFontAxis" => 8,
									"labelAngle" => 50,
									"textTickInterval" => 1,
									"from" => "navy",
									"to" => "#EEEEEE",
									"graphBarColor" => "white",
									"dataBar" => $consults["data"],
									"datax" => $datax );
				
$graph = new CMbGraph();
$graph->selectType("Graph",$options);
$graph->selectPalette($options);
$graph->setupAxis($options);
$graph->addDataBarPlot($options);
$graph->render("out",$options);

?>