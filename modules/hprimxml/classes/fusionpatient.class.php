<?php /* $Id $ */

/**
 * @package Mediboard
 * @subpackage hprimxml
 * @version $Revision:$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

CAppUI::requireModuleClass("hprimxml", "evenementspatients");

class CHPrimXMLFusionPatient extends CHPrimXMLEvenementsPatients { 
  var $actions = array(
    'fusion' => "fusion"
  );
  
  function __construct() {        
  	$this->sous_type = "fusionPatient";
  	    
    parent::__construct();
  }
  
  function generateFromOperation($mbPatient, $referent) {  
    $evenementsPatients = $this->documentElement;
    $evenementPatient   = $this->addElement($evenementsPatients, "evenementPatient");
    
    $fusionPatient = $this->addElement($evenementPatient, "fusionPatient");
    $this->addAttribute($fusionPatient, "action", "fusion");
      
    $patient = $this->addElement($fusionPatient, "patient");
    // Ajout du nouveau patient   
    $this->addPatient($patient, $mbPatient, $referent);
      
    $patientElimine = $this->addElement($fusionPatient, "patientElimine");
    // Ajout du patient a eliminer
    $this->addPatient($patientElimine, $mbPatient->_patient_elimine, $referent);
        
    // Traitement final
    $this->purgeEmptyElements();
  }
  
  function getContentsXML() {
    $xpath = new CHPrimXPath($this);

    $query = "/hprim:evenementsPatients/hprim:evenementPatient";

    $evenementPatient = $xpath->queryUniqueNode($query);
    $fusionPatient = $xpath->queryUniqueNode("hprim:fusionPatient", $evenementPatient);

    $data['action'] = $this->getActionEvenement("hprim:fusionPatient", $evenementPatient);

    $data['patient'] = $xpath->queryUniqueNode("hprim:patient", $fusionPatient);
    $data['patientElimine'] = $xpath->queryUniqueNode("hprim:patientElimine", $fusionPatient);

    $data['idSourcePatient'] = $this->getIdSource($data['patient']);
    $data['idCiblePatient']  = $this->getIdCible($data['patient']);
    
    $data['idSourcePatientElimine']= $this->getIdSource($data['patientElimine']);
    $data['idCiblePatientElimine'] = $this->getIdCible($data['patientElimine']);
    
    return $data;
  }
  
  /**
   * Fusion and recording a patient with an IPP in the system
   * @param CHPrimXMLAcquittementsPatients $domAcquittement
   * @param CEchangeHprim $echange_hprim
   * @param CPatient $newPatient
   * @param array $data
   * @return string acquittement 
   **/
  function fusionPatient($domAcquittement, $echange_hprim, $newPatient, $data) {
    // Si CIP
    if (!CAppUI::conf('sip server')) {
      $mbPatientElimine = new CPatient();
      $mbPatient = new CPatient();
     
      $dest_hprim = new CDestinataireHprim();
      $dest_hprim->nom = $data['idClient'];
      $dest_hprim->loadMatchingObject();

      // Acquittement d'erreur : identifiants source et cible non fournis pour le patient / patientElimine
      if (!$data['idSourcePatient'] && !$data['idCiblePatient'] && 
			    !$data['idSourcePatientElimine'] && !$data['idCiblePatientElimine']) {
        $messageAcquittement = $domAcquittement->generateAcquittementsPatients("erreur", "E005", null, $newPatient);
        $doc_valid = $domAcquittement->schemaValidate();
				
        $echange_hprim->setAckError($doc_valid, $messageAcquittement, "erreur");
        return $messageAcquittement;
      }
      
      $id400Patient = new CIdSante400();
      //Param�trage de l'id 400
      $id400Patient->object_class = "CPatient";
      $id400Patient->tag = $dest_hprim->_tag_patient;
      $id400Patient->id400 = $data['idSourcePatient'];
      $id400Patient->loadMatchingObject();
      if ($mbPatient->load($data['idCiblePatient'])) {
        if ($mbPatient->_id != $id400Patient->object_id) {
          $commentaire = "L'identifiant source fait r�f�rence au patient : $id400Patient->object_id et l'identifiant cible au patient : $mbPatient->_id.";
          $messageAcquittement = $domAcquittement->generateAcquittementsPatients("erreur", "E004", $commentaire, $newPatient);
          $doc_valid = $domAcquittement->schemaValidate();
					
          $echange_hprim->setAckError($doc_valid, $messageAcquittement, "erreur");
          return $messageAcquittement;
        }
      } 
      if (!$mbPatient->_id) {
        $mbPatient->load($id400Patient->object_id);
      }
      
      $id400PatientElimine = new CIdSante400();
      //Param�trage de l'id 400
      $id400PatientElimine->object_class = "CPatient";
      $id400PatientElimine->tag = $dest_hprim->_tag_patient;
      $id400PatientElimine->id400 = $data['idSourcePatientElimine'];
      $id400PatientElimine->loadMatchingObject();
      if ($mbPatientElimine->load($data['idCiblePatientElimine'])) {
        if ($mbPatientElimine->_id != $id400PatientElimine->object_id) {
          $commentaire = "L'identifiant source fait r�f�rence au patient : $id400PatientElimine->object_id et l'identifiant cible au patient : $mbPatientElimine->_id.";
          $messageAcquittement = $domAcquittement->generateAcquittementsPatients("erreur", "E041", $commentaire, $newPatient);
          $doc_valid = $domAcquittement->schemaValidate();
					
          $echange_hprim->setAckError($doc_valid, $messageAcquittement, "erreur");
          return $messageAcquittement;
        }
      }
      if (!$mbPatientElimine->_id) {
        $mbPatientElimine->load($id400PatientElimine->object_id);
      }
      
      if (!$mbPatient->_id || !$mbPatientElimine->_id) {
        $commentaire = !$mbPatient->_id ? "Le patient $mbPatient->_id est inconnu dans Mediboard." : "Le patient $mbPatientElimine->_id est inconnu dans Mediboard.";
        $messageAcquittement = $domAcquittement->generateAcquittementsPatients("erreur", "E012", $commentaire, $newPatient);
        $doc_valid = $domAcquittement->schemaValidate();
				
        $echange_hprim->setAckError($doc_valid, $messageAcquittement, "erreur");
        return $messageAcquittement;
      }
      
      // Passage en trash de l'IPP du patient a �liminer
      $id400PatientElimine->tag = CAppUI::conf('dPpatients CPatient tag_ipp_trash').$dest_hprim->_tag_patient;
      $id400PatientElimine->store();
      
      $messages = array();
      $avertissement = null;
            
      $patientsElimine_array = array($mbPatientElimine);
      $first_patient_id = $mbPatient->_id;

      $checkMerge = $mbPatient->checkMerge($patientsElimine_array);
      // Erreur sur le check du merge
      if ($checkMerge) {
        $commentaire = "La fusion de ces deux patients n'est pas possible � cause des probl�mes suivants : $checkMerge";
        $messageAcquittement = $domAcquittement->generateAcquittementsPatients("erreur", "E010", $commentaire, $newPatient);
        $doc_valid = $domAcquittement->schemaValidate();
				
        $echange_hprim->setAckError($doc_valid, $messageAcquittement, "erreur");
        return $messageAcquittement;
      }
      
      if ($msg = $mbPatient->mergeDBFields($patientsElimine_array)) {
        $commentaire = "La fusion des donn�es des patients a �chou� : $msg";
        $messageAcquittement = $domAcquittement->generateAcquittementsPatients("erreur", "E011", $commentaire, $newPatient);
        $doc_valid = $domAcquittement->schemaValidate();
				
        $echange_hprim->setAckError($doc_valid, $messageAcquittement, "erreur");
        return $messageAcquittement;
      }
      
      $mbPatientElimine_id = $mbPatientElimine->_id;
      
      /** @todo mergeDBfields resets the _id */
      $mbPatient->_id = $first_patient_id;
      
      // Notifier les autres destinataires
      $newPatient->_hprim_initiateur_group_id = $dest_hprim->group_id;
            
      $mbPatient->_merging = array_keys($patientsElimine_array);
      $msg = $mbPatient->merge($patientsElimine_array);
      
      $codes = array ($msg ? "A010" : "I010");
        
      if ($msg) {
        $avertissement = $msg." ";
      } else {
        $commentaire = "Le patient $mbPatient->_id a �t� fusionn� avec le patient $mbPatientElimine_id.";
      }
        
      $messageAcquittement = $domAcquittement->generateAcquittementsPatients($avertissement ? "avertissement" : "OK", $codes, $avertissement ? $avertissement : substr($commentaire, 0, 4000), $newPatient); 
      $doc_valid = $domAcquittement->schemaValidate();
      $echange_hprim->acquittement_valide = $doc_valid ? 1 : 0;
        
      $echange_hprim->statut_acquittement = $avertissement ? "avertissement" : "OK";
    }
    $echange_hprim->_acquittement = $messageAcquittement;
    $echange_hprim->date_echange = mbDateTime();
    $echange_hprim->setObjectIdClass("CPatient", $data['idCiblePatient']);
    $echange_hprim->store();

    return $messageAcquittement;
  }
}
?>