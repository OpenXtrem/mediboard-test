<?php

/**
 * ITI31 Delegated Handler
 *  
 * @category IHE
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
 */

/**
 * Class CITI31DelegatedHandler 
 * ITI31 Delegated Handler
 */
class CITI31DelegatedHandler extends CITIDelegatedHandler {
  static $handled        = array ("CSejour", "CAffectation");
  protected $profil      = "PAM";
  protected $transaction = "ITI31";

  static $inpatient      = array("comp", "ssr", "psy", "seances", "consult");
  static $outpatient     = array("urg", "ambu", "exte");
  
  static function isHandled(CMbObject $mbObject) {
    return in_array($mbObject->_class, self::$handled);
  }
 
  function onAfterStore(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }

    $receiver = $mbObject->_receiver;
    $receiver->getInternationalizationCode($this->transaction);  

    // Traitement Sejour
    if ($mbObject instanceof CSejour) {
      $sejour = $mbObject;
      $sejour->loadRefPatient();
      
      if ($sejour->_no_synchro) {
        return;
      }
      
      // Si Serveur
      if (CAppUI::conf('smp server')) {} 
      // Si Client
      else {
        $code = $this->getCodeSejour($sejour);
        
        // Cas o� : 
        // * on est l'initiateur du message 
        // * le destinataire ne supporte pas le message
        if ($sejour->_eai_initiateur_group_id || !$this->isMessageSupported($this->transaction, $code, $receiver)) {
          return;
        }
				
        if (!$sejour->_NDA) {
        	// G�n�ration du NDA dans le cas de la cr�ation, ce dernier n'�tait pas cr��
					if ($msg = $sejour->generateNDA()) {
			      CAppUI::setMsg($msg, UI_MSG_ERROR);
			    }
					
					$NDA = new CIdSante400();
        	$NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
					$sejour->_NDA = $NDA->id400;
        }
        
        $current_affectation = null;
        // Cas o� lors de l'entr�e r�elle j'ai une affectation qui n'a pas �t� envoy�e
        if ($sejour->fieldModified("entree_reelle") && !$sejour->_old->entree_reelle) {
          $current_affectation = $sejour->getCurrAffectation();
        }

        $this->createMovement($code, $sejour, $current_affectation);

        // Envoi de l'�v�nement
        $this->sendITI($this->profil, $this->transaction, $code, $sejour);
      }
    }
    
     // Traitement Affectation
    if ($mbObject instanceof CAffectation) {
      $affectation = $mbObject;
      $current_log = $affectation->_ref_current_log;
      if (!$current_log || $affectation->_no_synchro || !in_array($current_log->type, array("create", "store"))) {
        return;
      }
      
      $sejour = $affectation->loadRefSejour();
      if (!$sejour->_id || $sejour->_etat == "preadmission") {
        return;
      }
      
      $code = $this->getCodeAffectation($affectation);

      // Cas o� : 
      // * on est l'initiateur du message 
      // * le destinataire ne supporte pas le message
      if ($affectation->_eai_initiateur_group_id || !$this->isMessageSupported($this->transaction, $code, $receiver)) {
        return;
      }
      
      $sejour->loadRefPatient();
      $sejour->_receiver = $receiver;
      
      $this->createMovement($code, $sejour, $affectation);
   
      // Envoi de l'�v�nement
      $this->sendITI($this->profil, $this->transaction, $code, $sejour);
    }
  }
  
  function createMovement($code, CSejour $sejour, CAffectation $affectation = null) {
    $insert = in_array($code, CHL7v2SegmentZBE::$actions["INSERT"]);
    $update = in_array($code, CHL7v2SegmentZBE::$actions["UPDATE"]);
    $cancel = in_array($code, CHL7v2SegmentZBE::$actions["CANCEL"]);
    
    $movement = new CMovement();
    // Initialise le mouvement 
    $movement->sejour_id     = $sejour->_id;
  
    if ($affectation && $code == "A02") {
      $movement->affectation_id = $affectation->_id;  
    }

    if ($insert) {
      // Dans le cas d'un insert le type correspond n�cessairement au type actuel du s�jour
      $movement->movement_type         = $sejour->getMovementType($code);
      $movement->original_trigger_code = $code;
      $movement->start_of_movement     = $this->getStartOfMovement($code, $sejour);
      $movement->store();
    
      return $sejour->_ref_hl7_movement = $movement;
    }
    elseif ($update) {
      // Dans le cas d'un update le type correspond � celui du trigger
      $movement_type = $sejour->getMovementType($code);

      // Mise � jour entr�e r�elle
      if ($sejour->fieldModified("entree_reelle")) {
        $movement_type = "ADMI";
      }
      
      // Mise � jour sortie r�elle
      if ($sejour->fieldModified("sortie_reelle")) {
        $movement_type = "SORT";
      }

      // Mise � jour d'une affectation
      if ($affectation && $affectation->_ref_current_log->type == "store") {
        $movement_type = "MUTA";
      }
      $movement->movement_type = $movement_type;
      $movement->cancel = 0; 
    }
    elseif ($cancel) {
      $movement->cancel = 0;    
    }

    $order = "affectation_id DESC";
    $movements = $movement->loadMatchingList($order);
    if (!empty($movements)) {
      $movement = reset($movements);
    }

    if ($cancel) {
      $movement->cancel = 1;
    }

    $movement->store();
    
    return $sejour->_ref_hl7_movement = $movement;
  }

  function getStartOfMovement($code, CSejour $sejour) {
    switch ($code) {
      // Admission hospitalis� / externe
      case 'A01' : 
      case 'A04' :
        // Date de l'admission
        return $sejour->entree_reelle;
      // Mutation : changement d'UF h�bergement
      case 'A02':
      // Changement de statut externe ou urgence vers hospitalis�
      case 'A06':
      // Changement de statut hospitalis� ou urgence vers externe
      case 'A07':
      // Changement de m�decin responsable
      case 'A54':
      // Changement d'UF m�dicale
      case 'Z80':
      // Changement d'UF de soins
      case 'Z84':
        // Date du transfert
        return mbDateTime();
      // Sortie d�finitive
      case 'A03':
        // Date de la sortie
        return $sejour->sortie_reelle;
      // Pr�-admission
      case 'A05':
        // Date de la pr�-admission
        return $sejour->entree_prevue;
      // Sortie en attente
      case 'A16':
        // Date de la sortie
        return $sejour->sortie;
    }
  }
  
  function getCodeSejour(CSejour $sejour) {
    $current_log = $sejour->loadLastLog();
    if (!in_array($current_log->type, array("create", "store"))) {
      return null;
    }
    
    $sejour->loadOldObject();
    // Cas d'une pr�-admission
    if ($sejour->_etat == "preadmission") {
      // Cr�ation d'une pr�-admission
      if ($current_log->type == "create") {
        return "A05";
      } 
      // Modification d'une pr�-admission
      // Cas d'une annulation ? 
      if ($sejour->fieldModified("annule", "1")) {
        return "A38";
      }

      // Annulation de l'admission
      if ($sejour->_old->entree_reelle && !$sejour->entree_reelle) {
        return "A11";
      }
      
      // Simple modification ? 
      return $this->getModificationAdmitCode($sejour->_receiver);
    }
    
    // Cas d'un s�jour en cours (entr�e r�elle)
    if ($sejour->_etat == "encours") {
      // Admission faite
      if ($sejour->fieldModified("entree_reelle") && !$sejour->_old->entree_reelle ||
          $sejour->entree_reelle && !$sejour->_old->entree_reelle) {
        // Patient externe
        if (in_array($sejour->type, self::$outpatient)) {
          return "A04";
        } 
        // Admission hospitalis�
        return "A01";
      }
      
      // Confirmation de sortie
      if ($sejour->fieldModified("confirme", "1")) {
        return "A16";
      }
      
      // Annulation confirmation de sortie
      if ($sejour->_old->confirme && $sejour->fieldModified("confirme", "0")) {
        return "A25";
      }
      
      // Bascule externe devient hospitalis� (outpatient > inpatient)
      if ($sejour->fieldModified("type") 
        && (in_array($sejour->type, self::$inpatient)) 
        && in_array($sejour->_old->type, self::$outpatient)) {
        return "A06";
      }
      
      // Bascule d'hospitalis� � externe (inpatient > outpatient)
      if ($sejour->fieldModified("type") 
        && (in_array($sejour->type, self::$outpatient)) 
        && in_array($sejour->_old->type, self::$inpatient)) {
        return "A07";
      }
      
      // Changement du m�decin responsable
      if ($sejour->fieldModified("praticien_id")) {
        /* @todo Mettre en config !!! */
        return "Z99";
        //return "A54";
      }
      
      // Annulation du m�decin responsable
      if ($sejour->fieldModified("praticien_id") && 
         ($sejour->praticien_id != $sejour->_old->praticien_id)) {
        return "A55";
      }
      
      // R�attribution dossier administratif
      if ($sejour->fieldModified("patient_id")) {
        return "A44";
      }

      // Cas d'une annulation
      if ($sejour->fieldModified("annule", "1")) {
        return "A11";
      }
      
      // Annulation de la sortie r�elle
      if ($sejour->_old->sortie_reelle && !$sejour->sortie_reelle) {
        return "A13";
      }
      
      // Simple modification ? 
      return $this->getModificationAdmitCode($sejour->_receiver);
    }
    
    // Cas d'un s�jour cl�tur� (sortie r�elle)
    if ($sejour->_etat == "cloture") {
      // Sortie r�elle renseign�e
      if ($sejour->fieldModified("sortie_reelle") && !$sejour->_old->sortie_reelle) {
        return "A03";
      }
      
      // Modification de l'admission
      // Cas d'une annulation ? 
      if ($sejour->fieldModified("annule", "1")) {
        return "A13";
      }
      
      // Simple modification ? 
      return $this->getModificationAdmitCode($sejour->_receiver);
    }
  }
  
  function getCodeAffectation(CAffectation $affectation) {
    $current_log = $affectation->_ref_current_log;
    if (!in_array($current_log->type, array("create", "store"))) {
      return null;
    }

    // Cr�ation d'une affectation
    if ($current_log->type == "create") {
      return "A02";
    }

    /* Changement d'UF M�dicale */
    if ($affectation->_old->uf_medicale_id && $affectation->fieldModified("uf_medicale_id")) {
      /* @todo G�rer le cas o� : cr�ation d'une nouvelle affectation && UM1 # UM2 */
      //return "Z80";
      //return "Z99";
      return "A02";
    }

    /* Changement d'UF de Soins */
    if ($affectation->_old->uf_soins_id && $affectation->fieldModified("uf_soins_id")) {
      /* @todo G�rer le cas o� : cr�ation d'une nouvelle affectation && US1 # US2 */
      //return "Z84";
      return "A02";
    }
 
    // Modifcation d'une affectation
    return $this->getModificationAdmitCode($affectation->_receiver);
  }
  
  function getModificationAdmitCode(CReceiverIHE $receiver) {
    switch ($receiver->_i18n_code) {
      // Cas de l'extension fran�aise : Z99
      case "FR" :
        $code = "Z99";
        break;
      // Cas internationnal : A08
      default :
        $code = "A08";
        break;
    }
    
    return $code;
  }

  function onBeforeMerge(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
  }
  
  function onAfterMerge(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
  }  
  
  function onAfterDelete(CMbObject $mbObject) {
    if (!$this->isHandled($mbObject)) {
      return false;
    }
    
    $receiver = $mbObject->_receiver;
    $receiver->getInternationalizationCode($this->transaction);  

    // Traitement Affectation
    if ($mbObject instanceof CAffectation) {
      $affectation = $mbObject;
      $current_log = $affectation->_ref_current_log;
      if (!$current_log || $affectation->_no_synchro || !in_array($current_log->type, array("delete"))) {
        return;
      }
      
      $sejour = $affectation->loadRefSejour();
      if (!$sejour->_id || $sejour->_etat == "preadmission") {
        return;
      }
      
      // Annulation d'une affectation
      $code = "A12";

      // Cas o� : 
      // * on est l'initiateur du message 
      // * le destinataire ne supporte pas le message
      if ($affectation->_eai_initiateur_group_id || !$this->isMessageSupported($this->transaction, $code, $receiver)) {
        return;
      }
      
      $sejour->loadRefPatient();
      $sejour->_receiver = $receiver;

      $this->createMovement($code, $sejour, $affectation);
   
      // Envoi de l'�v�nement
      $this->sendITI($this->profil, $this->transaction, $code, $sejour);
    }  
  }  
}
?>