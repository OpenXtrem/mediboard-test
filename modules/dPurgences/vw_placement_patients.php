<?php
/**
 * $Id$
 *
 * @package    Mediboard
 * @subpackage Urgences
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 */

CCanDo::checkRead();

// R�cup�ration des param�tres
//$date           = CValue::getOrSession("date", CMbDT::date());
$date  = CMbDT::dateTime();
$date_tolerance = CAppUI::conf("dPurgences date_tolerance");
$date_before    = CMbDT::date("-$date_tolerance DAY", $date);
$date_after     = CMbDT::date("+1 DAY", $date);

//recherche des chambres d'urgences plac�es
$chambre = new CChambre();
$ljoin = array();
$ljoin["service"]     = "service.service_id = chambre.service_id";
$ljoin["emplacement"] = "emplacement.chambre_id = chambre.chambre_id";
$where = array();
$where["annule"]    = "= '0'";
$where["service.urgence"]    = "= '1'";
$where["service.group_id"]  = "= '".CGroups::loadCurrent()->_id."'";
$where["emplacement.plan_x"] = "IS NOT NULL";
$chambres_urgences = $chambre->loadList($where, null, null, "chambre_id", $ljoin);

$where = array();
$where["annule"]    = "= '0'";
$where["service.uhcd"]       = "= '1'";
$where["service.group_id"]  = "= '".CGroups::loadCurrent()->_id."'";
$where["emplacement.plan_x"] = "IS NOT NULL";
$chambres_uhcd = $chambre->loadList($where, null, null, "chambre_id", $ljoin);

$conf_nb_colonnes = CAppUI::conf("dPhospi nb_colonnes_vue_topologique");

$grille = array(
  "urgence" => array_fill(0, $conf_nb_colonnes, array_fill(0, $conf_nb_colonnes, 0)),
  "uhcd"    => array_fill(0, $conf_nb_colonnes, array_fill(0, $conf_nb_colonnes, 0))
);

$listSejours = array(
  "uhcd"    => array(),
  "urgence" => array(),
);

$ljoin = array();
$ljoin["rpu"] = "rpu.sejour_id = sejour.sejour_id";
$temp = array();
$temp["sejour.entree"]    = " BETWEEN '$date_before' AND '$date_after'";
$temp["sejour.sortie_reelle"]    = "IS NULL";
$temp["sejour.annule"]    = " = '0'";
$temp["sejour.group_id"]  = "= '".CGroups::loadCurrent()->_id."'";

for ($num = 0; $num <= 1; $num++) {
  /** @var CChambre[] $chambres */
  if ($num == 0) {
    $chambres = $chambres_uhcd;
    if (!CAppUI::conf("dPurgences view_rpu_uhcd")) {
      $temp["sejour.uhcd"] = " = '1'";
    }
    $nom = "uhcd";
  }
  else {
    $chambres = $chambres_urgences;
    if (!CAppUI::conf("dPurgences view_rpu_uhcd")) {
      $temp["sejour.uhcd"] = " = '0'";
    }
    $nom = "urgence";
  }
  
  foreach ($chambres as $chambre) {
    $chambre->loadRefService();
    $chambre->loadRefsLits();
    if (!count($chambre->_ref_lits)) {
      unset($chambres[$chambre->_id]);
      continue;
    }
    $chambre->loadRefEmplacement();
    $grille[$nom][$chambre->_ref_emplacement->plan_y][$chambre->_ref_emplacement->plan_x] = $chambre;
    $emplacement = $chambre->_ref_emplacement;
    if ($emplacement->hauteur-1) {
      for ($a = 0; $a <= $emplacement->hauteur-1; $a++) {
        if ($emplacement->largeur-1) {
          for ($b = 0; $b <= $emplacement->largeur-1; $b++) {
            if ($b != 0) {
              unset($grille[$nom][$emplacement->plan_y+$a][$emplacement->plan_x+$b]);
            }
            elseif ($a != 0) {
              unset($grille[$nom][$emplacement->plan_y+$a][$emplacement->plan_x+$b]);
            }
          }
        }
        elseif ($a < $emplacement->hauteur-1) {
          $c = $a+1;
          unset($grille[$nom][$emplacement->plan_y+$c][$emplacement->plan_x]);
        }
      }
    }
    elseif ($emplacement->largeur-1) {
      for ($b = 1; $b <= $emplacement->largeur-1; $b++) {
        unset($grille[$nom][$emplacement->plan_y][$emplacement->plan_x+$b]);
      }
    }

    $where = $temp;
    if (CAppUI::conf("dPurgences create_affectation")) {
      $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";
      $ljoin["service"]     = "service.service_id = affectation.service_id";
      $ljoin["lit"]         = "lit.lit_id = affectation.lit_id";
      $ljoin["chambre"]     = "chambre.chambre_id = lit.chambre_id";

      $where[]                     = "'$date' BETWEEN affectation.entree AND affectation.sortie";
      $where["service.urgence"]    = "= '1'";
      $where["chambre.chambre_id"] = "= '$chambre->_id'";
    }
    else {
      $where["rpu.box_id"] = CSQLDataSource::prepareIn(array_keys($chambre->_ref_lits));
    }

    if (!CAppUI::conf("dPurgences create_sejour_hospit")) {
      $where[] = "rpu.mutation_sejour_id IS NULL";
    }

    $sejour = new CSejour();
    /** @var CSejour[] $sejours */
    $sejours = $sejour->loadList($where, null, null, null, $ljoin);
    foreach ($sejours as $sejour) {
      $sejour->loadRefPatient();
      $sejour->loadRefPraticien();
      if (!$sejour->loadRefRPU()->_id) {
        $sejour->_ref_rpu = $sejour->loadUniqueBackRef("rpu_mute");
      }
      $prescription = $sejour->loadRefPrescriptionSejour();

      if ($prescription->_id) {
        if (@CAppUI::conf("object_handlers CPrescriptionAlerteHandler")) {
          $prescription->_count_fast_recent_modif = $prescription->countAlertsNotHandled("medium");
          $prescription->_count_urgence["all"]    = $prescription->countAlertsNotHandled("high");
        }
        else {
          $prescription->countFastRecentModif();
          $prescription->loadRefsLinesMedByCat();
          $prescription->loadRefsLinesElementByCat();
          $prescription->countUrgence(CMbDT::date($date));
        }

        $sejour->countDocItems();
      }
    }
    $listSejours[$nom][$chambre->_id] = $sejours;
  }
  
  //Traitement des lignes vides
  $nb = 0;
  $total = 0;

  foreach ($grille[$nom] as $j => $value) {
    $nb = 0;
    foreach ($value as $i => $valeur) {
      if ($valeur == "0") {
        if ($j == 0 || $j == 9) {
          $nb++;
        }
        else {
          if (
              !isset($grille[$nom][$j-1][$i]) ||
              $grille[$nom][$j-1][$i] == "0" ||
              !isset($grille[$nom][$j+1][$i]) ||
              $grille[$nom][$j+1][$i] == "0"
          ) {
            $nb++;
          }
        }
      }
    }

    //suppression des lignes inutiles
    if ($nb == $conf_nb_colonnes) {
      unset($grille[$nom][$j]);
    }
  }
  
  //Traitement des colonnes vides
  for ($i = 0; $i < $conf_nb_colonnes; $i++) {
    $nb = 0;
    $total = 0;
    for ($j = 0; $j < $conf_nb_colonnes; $j++) {
      $total++;
      if (!isset($grille[$nom][$j][$i]) || $grille[$nom][$j][$i] == "0") {
        if ($i == 0 || $i == 9) {
          $nb++; 
        }
        else {
          if (
              !isset($grille[$nom][$j][$i-1]) ||
              $grille[$nom][$j][$i-1] == "0" ||
              !isset($grille[$nom][$j][$i+1]) ||
              $grille[$nom][$j][$i+1] == "0"
          ) {
            $nb++;
          }
        }
      }
    }
    //suppression des colonnes inutiles
    if ($nb == $total) {
      for ($a = 0; $a < $conf_nb_colonnes; $a++) {
        unset($grille[$nom][$a][$i]);
      }
    }
  }
}
$exist_plan = array("urgence" => count($chambres_urgences), "uhcd" => count($chambres_uhcd));

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("listSejours"    , $listSejours);
$smarty->assign("grilles"        , $grille);
$smarty->assign("date"           , $date);
$smarty->assign("suiv"           , CMbDT::date("+1 day", $date));
$smarty->assign("prec"           , CMbDT::date("-1 day", $date));
$smarty->assign("exist_plan"     , $exist_plan);

$smarty->display("vw_placement_patients.tpl");
