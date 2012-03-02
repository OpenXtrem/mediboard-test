<?php
/**
 * $Id: $
 * 
 * @package    Mediboard
 * @subpackage board
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    $Revision: $
 */

/**
 * Turn a iso time to a string representation for iCal exports
 * 
 * @param time $time Time to convert
 * 
 * @return string
 */
function ical_time($time) {
  list($hour, $min) = explode(":", $time);
  return "{$hour}h{$min}";
}

CCanDo::checkRead();

// R�cup�ration des param�tres
$prat_id      = CValue::get("prat_id");
$details      = CValue::get("details");
$export       = CValue::get("export");

$weeks_before = CValue::get("weeks_before");
$weeks_after  = CValue::get("weeks_after");

$date         = CValue::get("date", mbDate());
$debut        = mbDate("-$weeks_before week", $date);
$debut        = mbDate("last sunday", $debut);
$fin          = mbDate("+$weeks_after week", $date);
$fin          = mbDate("next sunday", $fin);

// Liste des Salles
$listSalles = new CSalle();
$listSalles = $listSalles->loadGroupList();

// Plages de Consultations
$plageConsult   = new CPlageconsult();
$plageOp        = new CPlageOp();
$listDays       = array();
$plagesConsult  = array();
$plagesOp       = array();
$plagesPerDayOp = array();

for ($i = 0; mbDate("+$i day", $debut)!=$fin ; $i++) {
  $where = array();
  $where["chir_id"] = "= '$prat_id'";
  $date             = mbDate("+$i day", $debut);
  $where["date"]    = "= '$date'";
  
  $plagesPerDayConsult = $plageConsult->loadList($where);
  $nb_oper        = 0;
  $where          = array();
  $where[]        = "chir_id = '$prat_id' OR anesth_id = '$prat_id'";
  $where["date"]  = "= '$date'";
  
  foreach ($listSalles as $salle) {
    $where["salle_id"] = "= '$salle->_id'";
    $plagesPerDayOp[$salle->_id] = $plageOp->loadList($where);
    $nb_oper = $nb_oper + count($plagesPerDayOp[$salle->_id]);
  }
  
  foreach ($plagesPerDayConsult as $value) {
    $value->countPatients();
  }
  
  if (in_array("consult", $export) && count($plagesPerDayConsult)) {
    foreach ($plagesPerDayConsult as $value) {
      $value->loadFillRate();
      
      if ($details) {
        $value->loadRefsConsultations();
      }
    }
    
    $plagesConsult[$date] = $plagesPerDayConsult;
  }
  
  if (in_array("interv", $export) && $nb_oper) {
    foreach ($plagesPerDayOp as $key => $valuePlages) {
      if (!count($valuePlages)) {
        unset($plagesPerDayOp[$key]);
        continue;
      }

      foreach ($valuePlages as $keyPlage=>$value) {
        $value->loadRefSalle();
        $value->_ref_salle->loadRefBloc();
        $value->_ref_salle->_ref_bloc->loadRefGroup();
        if ($details) {
          $value->loadRefsOperations();
        }
        
        $value->getNbOperations();
      }
        
      $plagesOp[$key][$date] = $plagesPerDayOp[$key];
    }
  }
}

// Cr�ation du calendrier
$v = new CMbCalendar("Planning");

// Cr�ation des �v�nements plages de consultation
if (in_array("consult", $export)) {
  foreach ($plagesConsult as $curr_day => $plagesPerDay) {
    foreach ($plagesPerDay as $rdv) {
      $description = "$rdv->_nb_patients patient(s)";
			
      // Ev�nement d�taill�
      if ($details) {
        foreach ($rdv->_ref_consultations as $consult) {
          $when = iCal::time($consult->heure);
          $patient = $consult->loadRefPatient();
          $what = $patient->_id ? $patient->_view : "Pause: $consult->motif"; 
          $description.= "\n$when: $what";
        }
      }
      
      $deb = "$rdv->date $rdv->debut";
      $fin = "$rdv->date $rdv->fin";
      $v->addEvent("", "Consultation - $rdv->libelle", $description, null, $rdv->_guid, $deb, $fin);
    }
  }
}

// Cr�ation des �v�nements plages d'interventions
if (in_array("interv", $export)) {
  foreach ($plagesOp as $salle) {
    foreach ($salle as $curr_day => $plagesPerDay) {
      foreach ($plagesPerDay as $rdv) {
        $description = "$rdv->_nb_operations intervention(s)";
        
        // Ev�nement d�taill�
        if ($details) {
          foreach ($rdv->_ref_operations as $op) {
            $op->loadComplete();
            $duration = iCal::time($op->temp_operation);
            $when     = iCal::time(mbTime($op->_datetime));
            $patient = $op->_ref_patient->_view;
            $description.= "\n$when: $patient (duree: $duration)";
          }
        }
      
        $deb = "$rdv->date $rdv->debut";
        $fin = "$rdv->date $rdv->fin";
        
        $location = $rdv->_ref_salle->_ref_bloc->_ref_group->_view;
        $v->addEvent($location, $rdv->_ref_salle->_view, $description, null, $rdv->_guid, $deb, $fin);          
      }
    }
  }
}

// Conversion du calendrier en champ texte
$str = $v->createCalendar();

//echo "<pre>$str</pre>"; return;

header("Content-disposition: attachment; filename=agenda.ics"); 
header("Content-Type: text/calendar; charset=".CApp::$encoding);
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header("Cache-Control: post-check=0, pre-check=0", false );
header("Content-Length: ".strlen($str));
echo $str;
