<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage Hospi
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkAdmin();

$secteur_id    = CValue::getOrSession("secteur_id");
$service_id    = CValue::getOrSession("service_id");
$chambre_id    = CValue::getOrSession("chambre_id");
$lit_id        = CValue::getOrSession("lit_id");
$uf_id         = CValue::getOrSession("uf_id");
$prestation_id = CValue::getOrSession("prestation_id");

$group = CGroups::loadCurrent();

// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);

// Chargement du secteur � ajouter / �diter
$secteur = new CSecteur;
$secteur->group_id = $group->_id;
$secteur->load($secteur_id);
$secteur->loadRefsNotes();
$secteur->loadRefsServices();

// Chargement du service � ajouter / �diter
$service = new CService();
$service->group_id = $group->_id;
$service->load($service_id);
$service->loadRefsNotes();

// R�cup�ration de la chambre � ajouter / �diter
$chambre = new CChambre();
$chambre->load($chambre_id);
$chambre->loadRefsNotes();
$chambre->loadRefService();
foreach ($chambre->loadRefsLits(true) as $_lit) {
  $_lit->loadRefsNotes();
}

if (!$chambre->_id) {
  CValue::setSession("lit_id", 0);
}

// Chargement du lit � ajouter / �diter
$lit = new CLit();
$lit->load($lit_id);
$lit->loadRefChambre();

// R�cup�ration des chambres/services/secteurs
$where = array();
$where["group_id"] = "= '$group->_id'";
$order = "nom";

$nb_chambre = 0;
/** @var CService[] $services */
$services = $service->loadListWithPerms(PERM_READ, $where, $order);
foreach ($services as $_service) {
  foreach ($_service->loadRefsChambres() as $_chambre) {
    $_chambre->loadRefsLits();
    $nb_chambre++;
  }
}

$secteurs = $secteur->loadListWithPerms(PERM_READ, $where, $order);

// Chargement de l'uf � ajouter/�diter
$uf = new CUniteFonctionnelle();
$uf->group_id = $group->_id;
$uf->load($uf_id);
$uf->loadRefUm();
$uf->loadRefsNotes();

// R�cup�ration des ufs
$order = "group_id, code";
$ufs = array(
  "hebergement" => $uf->loadGroupList(array("type" => "= 'hebergement'"), $order),
  "medicale"    => $uf->loadGroupList(array("type" => "= 'medicale'"), $order),
  "soins"       => $uf->loadGroupList(array("type" => "= 'soins'"), $order),
);

// R�cup�ration des Unit�s M�dicales (pmsi)
$ums = new CUniteMedicale();
$ums = $ums->loadList();

// Chargement de la prestation � ajouter/�diter
$prestation = new CPrestation();
$prestation->group_id = $group->_id;
$prestation->load($prestation_id);
$prestation->loadRefsNotes();

// R�cup�ration des prestations
$presta = new CPrestation;
$presta->group_id = $group->_id;
$prestations = $presta->loadMatchingList("nom");

$praticiens = CAppUI::$user->loadPraticiens();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("services"      , $services);
$smarty->assign("service"       , $service);
$smarty->assign("tag_service"   , CService::getTagService($group->_id));
$smarty->assign("secteurs"      , $secteurs);
$smarty->assign("secteur"       , $secteur);
$smarty->assign("chambre"       , $chambre);
$smarty->assign("nb_chambre"    , $nb_chambre);
$smarty->assign("tag_chambre"   , CChambre::getTagChambre($group->_id));
$smarty->assign("lit"           , $lit);
$smarty->assign("tag_lit"       , CLit::getTagLit($group->_id));
$smarty->assign("ufs"           , $ufs);
$smarty->assign("uf"            , $uf);
$smarty->assign("ums"           , $ums);
$smarty->assign("prestations"   , $prestations);
$smarty->assign("prestation"    , $prestation);
$smarty->assign("praticiens"    , $praticiens);
$smarty->assign("etablissements", $etablissements);

$smarty->display("vw_idx_infrastructure.tpl");