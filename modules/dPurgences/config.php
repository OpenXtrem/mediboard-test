<?php /* $Id: config.php 8507 2010-04-08 12:42:38Z alexis_granger $ */

/**
 * @package Mediboard
 * @subpackage dPurgences
 * @version $Revision: 8507 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */
  
$dPconfig["dPurgences"] = array (
  "date_tolerance"            => "2",
  "old_rpu"                   => "1",
  "rpu_warning_time"          => "00:20:00",
  "rpu_alert_time"            => "01:00:00",
  "default_view"              => "tous",
  "allow_change_patient"      => "1",
  "motif_rpu_view"            => "1",
  "age_patient_rpu_view"      => "0",
  "responsable_rpu_view"      => "1",
  "diag_prat_view"            => "0",
  "check_cotation"            => "1",
  "check_gemsa"               => "1",
  "check_ccmu"                => "1",
  "check_dp"                  => "1",
  "check_can_leave"           => "1",
  "sortie_prevue"             => "sameday",
  "only_prat_responsable"     => "0",
  "rpu_sender"                => "",
  "rpu_xml_validation"        => "1",
	"gerer_hospi"               => "1",
	"gerer_reconvoc"            => "1",
  "sibling_hours"             => "0",
  "pec_change_prat"           => "1",
  "pec_after_sortie"          => "0",
  "create_sejour_hospit"      => "0",
  "hide_reconvoc_sans_sortie" => "0",
  "show_statut"               => "0",
  "attente_first_part"        => "00:30:00",
  "attente_second_part"       => "02:00:00",
  "attente_third_part"        => "04:00:00",
  "gerer_circonstance"        => "0",
  "valid_cotation_sortie_reelle" => "1",
  "display_regule_par"        => "0"
);


?>