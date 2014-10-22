<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage dPstats
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

/**
 * @param null $date_min
 * @param null $date_max
 * @param null $prat_id
 * @param null $salle_id
 * @param null $bloc_id
 * @param null $code_ccam
 * @param null $type_sejour
 * @param bool $hors_plage
 *
 * @return array
 */
function graphOpAnnulees(
  $date_min = null,
  $date_max = null,
  $prat_id = null,
  $salle_id = null,
  $bloc_id = null,
  $code_ccam = null,
  $type_sejour = null,
  $hors_plage = false
) {
  $miner = new COperationWorkflow();
  $miner->warnUsage();

  if (!$date_min) {
    $date_min = CMbDT::date("-1 YEAR");
  }

  if (!$date_max) {
    $date_max = CMbDT::date();
  }

  $date_min = CMbDT::format($date_min, "%Y-%m-01");
  $date_max = CMbDT::transform("+1 MONTH", $date_max, "%Y-%m-01");

  $prat = new CMediusers;
  $prat->load($prat_id);

  $serie_total = array(
    'label' => 'Total',
    'data' => array(),
    'markers' => array('show' => true),
    'bars' => array('show' => false)
  );

  $salles = CSalle::getSallesStats($salle_id, $bloc_id);

  $query = new CRequest();
  $query->addColumn("salle_id");
  $query->addColumn("DATE_FORMAT(date_operation, '%Y-%m')", "mois");
  $query->addColumn("COUNT(DISTINCT(operations.operation_id))", "total");
  $query->addTable("operations");
  $query->addLJoinClause("operation_workflow", "operation_workflow.operation_id = operations.operation_id");
  $query->addWhere("DATE(date_cancellation) = DATE(date_operation)");
  $query->addWhereClause("date_operation", "BETWEEN '$date_min' AND '$date_max'");
  $query->addWhereClause("salle_id", CSQLDataSource::prepareIn(array_keys($salles)));
  $query->addGroup("mois, salle_id");
  $query->addOrder("mois, salle_id");

  // Filtre sur hors plage
  if ($hors_plage) {
    $query->addWhereClause("plageop_id", "IS NOT NULL");
  }

  // Filtre sur le praticien
  if ($prat_id) {
    $query->addWhereClause("operations.chir_id", "= '$prat_id'");
  }

  // Filtre sur les codes CCAM
  if ($code_ccam) {
    $query->addWhereClause("operations.codes_ccam", "LIKE '%$code_ccam%'");
  }

  // Filtre sur le type d'hospitalisation
  if ($type_sejour) {
    $query->addLJoinClause("sejour", "sejour.sejour_id = operations.sejour_id");
    $query->addWhereClause("sejour.type", "= '$type_sejour'");
  }

  // Query result
  $ds = CSQLDataSource::get("std");
  $tree = $ds->loadTree($query->makeSelect());

  // Build horizontal ticks
  $months = array();
  $ticks = array();
  for ($_date = $date_min; $_date < $date_max; $_date = CMbDT::date("+1 MONTH", $_date)) {
    $count_ticks = count($ticks);
    $ticks[] = array($count_ticks, CMbDT::format($_date, "%m/%Y"));
    $months[CMbDT::format($_date, "%Y-%m")] = $count_ticks;
    $serie_total['data'][] = array(count($serie_total['data']), 0);
  }

  // Build series
  $series = array();
  $total = 0;

  foreach ($salles as $_salle) {
    $_serie = array(
      "label" => utf8_encode($bloc_id ? $_salle->nom : $_salle->_view),
    );

    $data = array();
    foreach ($months as $_month => $_tick) {
      $value =  isset($tree[$_salle->_id][$_month]) ? $tree[$_salle->_id][$_month] : 0;
      $data[] = array($_tick, $value);
      $serie_total["data"][$_tick][1] += $value;
      $total += $value;
    }

    $_serie["data"] = $data;
    $series[] = $_serie;
  }

  $series[] = $serie_total;

  // Set up the title for the graph
  $title = "Interventions annul�es le jour m�me";
  $subtitle = "$total interventions";
  if ($prat_id) {
    $subtitle   .= " - Dr $prat->_view";
  }
  if ($salle_id) {
    $salle = reset($salles);
    $subtitle   .= " - $salle->_view";
  }
  if ($code_ccam) {
    $subtitle   .= " - CCAM : $code_ccam";
  }
  if ($type_sejour) {
    $subtitle .= " - ".CAppUI::tr("CSejour.type.$type_sejour");
  }

  mbTrace($ticks);
  mbTrace($series);
  mbTrace($total);

  $options = array(
    'title' => utf8_encode($title),
    'subtitle' => utf8_encode($subtitle),
    'xaxis' => array('labelsAngle' => 45, 'ticks' => $ticks),
    'yaxis' => array('autoscaleMargin' => 1),
    'bars' => array('show' => true, 'stacked' => true, 'barWidth' => 0.8),
    'HtmlText' => false,
    'legend' => array('show' => true, 'position' => 'nw'),
    'grid' => array('verticalLines' => false),
    'spreadsheet' => array(
      'show' => true,
      'csvFileSeparator' => ';',
      'decimalSeparator' => ',',
      'tabGraphLabel' => utf8_encode('Graphique'),
      'tabDataLabel' => utf8_encode('Donn�es'),
      'toolbarDownload' => utf8_encode('Fichier CSV'),
      'toolbarSelectAll' => utf8_encode('S�lectionner tout le tableau')
    )
  );

  return array('series' => $series, 'options' => $options);
}


