<?php 

/**
 * $Id$
 *  
 * @category search
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @link     http://www.mediboard.org */

CCanDo::checkAdmin();

$ds = CSQLDataSource::get("std");
try{
  // r�cup�ration du client
  $client_index   = new CSearch();
  $client_index->createClient();

  // r�cup�ration de l'index, cluster, mapping
  $index      = $client_index->loadIndex();
  $name_index = $index->getName();
  $cluster    = $client_index->_client->getCluster();
  $mapping    = $index->getMapping();

  // r�cup�ration de la taille totale des indexes
  $size = $index->getStats()->get("_all");
  $size = CMbString::toDecaBinary($size["primaries"]["store"]["size_in_bytes"]);

  // r�cup�ration du nombre de docs "index�s" et "� indexer"
  $nbdocs_indexed      = $index->count();
  $query = new CRequest();
  $query->addTable("search_indexing");
  $nbdocs_to_index = $ds->loadResult($query->makeSelectCount());

  // r�cup�ration du statut de la connexion et du cluster
  $status     = $cluster->getHealth()->getStatus();
  $connexion  = "1";

} catch (Exception $e) {
  CAppUI::displayAjaxMsg("Le serveur de recherche n'est pas connect�", UI_MSG_ERROR);
  // valeur par d�faut des variables en cas d'erreur
  $mapping    = "";
  $nbdocs_indexed      = "";
  $nbdocs_to_index = "";
  $status     = "";
  $connexion  = "0";
  $name_index = "";
  $size ="0";
}


// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("mapping", $mapping);
$smarty->assign("mappingjson", json_encode($mapping));
$smarty->assign("nbDocs_indexed", $nbdocs_indexed);
$smarty->assign("nbdocs_to_index", $nbdocs_to_index);
$smarty->assign("status", $status);
$smarty->assign("name_index", $name_index);
$smarty->assign("connexion", $connexion);
$smarty->assign("size", $size);
$smarty->display("vw_cartographie_mapping.tpl");