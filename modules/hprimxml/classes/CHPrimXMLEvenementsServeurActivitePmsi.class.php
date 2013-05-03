<?php /* $Id $ */

/**
 * @package Mediboard
 * @subpackage hprimxml
 * @version $Revision: 6153 $
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

class CHPrimXMLEvenementsServeurActivitePmsi extends CHPrimXMLEvenements {
  static $evenements = array(
    'evenementPMSI'                => "CHPrimXMLEvenementsPmsi",
    'evenementServeurActe'         => "CHPrimXMLEvenementsServeurActes",
    'evenementServeurEtatsPatient' => "CHPrimXMLEvenementsServeurEtatsPatient",
    'evenementFraisDivers'         => "CHPrimXMLEvenementsFraisDivers",
    'evenementServeurIntervention' => "CHPrimXMLEvenementsServeurIntervention",
  );
  
  static function getHPrimXMLEvenements($messageServeurActivitePmsi) {
    $hprimxmldoc = new CMbXMLDocument();
    $hprimxmldoc->loadXML($messageServeurActivitePmsi);
    
    $xpath = new CMbXPath($hprimxmldoc);
    $event = $xpath->queryUniqueNode("/*/*[2]");

    if ($nodeName = $event->nodeName) {
      return new self::$evenements[$nodeName];
    } 
    
    return new CHPrimXMLEvenementsServeurActivitePmsi();
  }  
  
  function __construct($dirschemaname = null, $schemafilename = null) {
    $this->type = "pmsi";
    
    if (!$this->evenement) {
      return;
    }
    
    $version = CAppUI::conf("hprimxml $this->evenement version");
    // Version 1.01 : schemaPMSI - schemaServeurActe
    if ($version == "1.01") {
      parent::__construct($dirschemaname, $schemafilename."101");
    } 
    // Version 1.04 - 1.05 - 1.06 - 1.07
    else {
      $version = str_replace(".", "", $version);
      parent::__construct("serveurActivitePmsi_v$version", $schemafilename.$version);
    }   
  }
  
  function getEvenements() {
    return self::$evenements;
  }
  
  function mappingServeurActes($data) {
    // Mapping patient
    $patient = $this->mappingPatient($data);
    
    // Mapping actes CCAM
    $actesCCAM = $this->mappingActesCCAM($data);
    
    return array (
      "patient"   => $patient,
      "actesCCAM" => $actesCCAM
    );  
  }
  
  function mappingPatient($data) {
    $node = $data['patient'];
    $xpath = new CHPrimXPath($node->ownerDocument);
    
    $personnePhysique = $xpath->queryUniqueNode("hprim:personnePhysique", $node);
    $prenoms = $xpath->getMultipleTextNodes("hprim:prenoms/*", $personnePhysique);
    $elementDateNaissance = $xpath->queryUniqueNode("hprim:dateNaissance", $personnePhysique);
    
    return array (
      "idSourcePatient" => $data['idSourcePatient'],
      "idCiblePatient"  => $data['idCiblePatient'],
      "nom"             => $xpath->queryTextNode("hprim:nomUsuel", $personnePhysique),
      "prenom"          => $prenoms[0],
      "naissance"       => $xpath->queryTextNode("hprim:date", $elementDateNaissance)
    );
  }
  
  function mappingVenue($node, CSejour $sejour) {
    // On ne r�cup�re que l'entr�e et la sortie 
    $sejour = CHPrimXMLEvenementsPatients::getEntree($node, $sejour);
    $sejour = CHPrimXMLEvenementsPatients::getSortie($node, $sejour);
    
    // On ne check pas la coh�rence des dates des consults/intervs
    $sejour->_skip_date_consistencies = true;
    
    return $sejour;    
  }
  
  function mappingIntervention($data, COperation $operation) {
    // Intervention annul�e ?
    if ($data['action'] == "suppression") {
      $operation->annulee = 1;

      return;
    }

    $node  = $data['intervention'];

    $xpath = new CHPrimXPath($node->ownerDocument);
    
    $debut = $this->getDebutInterv($node);
    $fin   = $this->getFinInterv($node);
    
    // Traitement de la date/heure d�but, et dur�e de l'op�ration
    $operation->temp_operation = CMbDT::subTime(CMbDT::time($debut), CMbDT::time($fin));
    $operation->_hour_op       = null;
    $operation->_min_op        = null;

    // Si une intervention du pass�e    
    if (CMbDT::date($debut) < CMbDT::date()) {
      // On affecte le d�but de l'op�ration
      if (!$operation->debut_op) {
        $operation->debut_op = CMbDT::time($debut);
      } 
      // On affecte la fin de l'op�ration
      if (!$operation->fin_op) {
        $operation->fin_op = CMbDT::time($fin);
      }
    }
    // Si dans le futur
    else {
      $operation->_hour_urgence  = null;
      $operation->_min_urgence   = null;
      $operation->time_operation = CMbDT::time($debut);
    }
    
    $operation->libelle = CMbString::capitalize($xpath->queryTextNode("hprim:libelle", $node));
    $operation->rques   = CMbString::capitalize($xpath->queryTextNode("hprim:commentaire", $node));
    
    // C�t�
    $cote = array (
      "D" => "droit",
      "G" => "gauche",
      "B" => "bilat�ral",
      "T" => "total",
      "I" => "inconnu"
    );
    $code_cote = $xpath->queryTextNode("hprim:cote/hprim:code", $node);
    $operation->cote = isset($cote[$code_cote]) ? $cote[$code_cote] : ($operation->cote ? $operation->cote : "inconnu");
    
    // Conventionn�e ?
    $operation->conventionne = $xpath->queryTextNode("hprim:convention", $node);
    
    // Extemporan�
    $indicateurs = $xpath->query("hprim:indicateurs/*", $node);
    foreach ($indicateurs as $_indicateur) {
      if ($xpath->queryTextNode("hprim:code", $_indicateur) == "EXT") {
        $operation->exam_extempo = true;
      }
    }
    
    // TypeAnesth�sie
    $this->getTypeAnesthesie($node, $operation);
    
    $operation->duree_uscpo = $xpath->queryTextNode("hprim:dureeUscpo", $node);
  }
  
  function getTypeAnesthesie($node, COperation $operation) {
    $xpath = new CHPrimXPath($node->ownerDocument); 
       
    if (!$typeAnesthesie = $xpath->queryTextNode("hprim:typeAnesthesie", $node)) {
      return;
    }
    
    $operation->type_anesth = CIdSante400::getMatch("CTypeAnesth", $this->_ref_sender->_tag_hprimxml, $typeAnesthesie)->object_id;
  }
  
  function mappingPlage($node, COperation $operation) {
    $debut = $this->getDebutInterv($node);

    // Traitement de la date/heure d�but, et dur�e de l'op�ration
    $date_op  = CMbDT::date($debut);
    $time_op  = CMbDT::time($debut);

    // Recherche d'une �ventuelle plageOp avec la salle
    $plageOp           = new CPlageOp();  
    $plageOp->chir_id  = $operation->chir_id;
    $plageOp->salle_id = $operation->salle_id;
    $plageOp->date     = $date_op;
    $plageOps          = $plageOp->loadMatchingList();

    // Si on a pas de plage on recherche �ventuellement une plage dans une autre salle
    if (count($plageOps) == 0) {
      $plageOp->salle_id = null;
      $plageOps          = $plageOp->loadMatchingList();

      // Si on retrouve des plages alors on ne prend pas en compte la salle du flux
      if (count($plageOps) > 0) {
        $operation->salle_id = "";
      }
    }

    foreach ($plageOps as $_plage) {
      // Si notre intervention est dans la plage Mediboard
      if (CMbRange::in($time_op, $_plage->debut, $_plage->fin)) {
        $plageOp = $_plage;
        break;
      }
    }
    
    if ($plageOp->_id) {
      $operation->plageop_id = $plageOp->_id;
    }
    else {
      // Dans le cas o� l'on avait une plage sur l'interv on la supprime
      $operation->plageop_id = "";
      
      $operation->date       = $date_op;
    }
  }
  
  function getDebutInterv($node) {
    $xpath = new CHPrimXPath($node->ownerDocument);
    
    return $this->getDateHeure($xpath->queryUniqueNode("hprim:debut", $node, false));
  }
  
  function getFinInterv($node) {
    $xpath = new CHPrimXPath($node->ownerDocument);
    
    return $this->getDateHeure($xpath->queryUniqueNode("hprim:fin", $node, false));
  } 
  
  function getParticipant($node, CSejour $sejour = null) {
    $xpath = new CHPrimXPath($node->ownerDocument);
    
    $adeli = $xpath->queryTextNode("hprim:participants/hprim:participant/hprim:medecin/hprim:numeroAdeli", $node);
    
    // Recherche du mediuser
    $mediuser = new CMediusers();
    if (!$adeli) {
      return $mediuser;
    }
    
    $where = array(
      "users_mediboard.adeli"        => $mediuser->_spec->ds->prepare("=%", $adeli),
      "functions_mediboard.group_id" => "= '$sejour->group_id'"
    );
    $ljoin = array(
      "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id"
    );
    
    $mediuser->loadObject($where, null, null, $ljoin);
    
    return $mediuser;
  }
  
  function getSalle($node, CSejour $sejour) {
    $xpath = new CHPrimXPath($node->ownerDocument);
    $name = $xpath->queryTextNode("hprim:uniteFonctionnelle/hprim:code", $node);
    
    // Recherche de la salle
    $salle = new CSalle();
    
    $where = array(
      "sallesbloc.nom"           => $salle->_spec->ds->prepare("=%", $name),
      "bloc_operatoire.group_id" => "= '$sejour->group_id'"
    );
    $ljoin = array(
      "bloc_operatoire" => "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id"
    );
    
    $salle->loadObject($where, null, null, $ljoin);
            
    return $salle;
  }
    
  function mappingActesCCAM($data) {
    $node = $data['actesCCAM'];
    $xpath = new CHPrimXPath($node->ownerDocument);
    
    $actesCCAM = array();
    foreach ($node->childNodes as $_acteCCAM) {
      $actesCCAM[] = $this->mappingActeCCAM($_acteCCAM, $data);
    }

    return $actesCCAM;
  }
  
  function mappingActeCCAM($node, $data) {
    $xpath = new CHPrimXPath($node->ownerDocument);
            
    $acteCCAM = new CActeCCAM();
    $acteCCAM->code_acte     = $xpath->queryTextNode("hprim:codeActe", $node);
    $acteCCAM->code_activite = $xpath->queryTextNode("hprim:codeActivite", $node);
    $acteCCAM->code_phase    = $xpath->queryTextNode("hprim:codePhase", $node);
    $acteCCAM->execution     = $xpath->queryTextNode("hprim:execute/hprim:date", $node)." ".CMbDT::transform($xpath->queryTextNode("hprim:execute/hprim:heure", $node), null , "%H:%M:%S");
        
    return array (
      "idSourceIntervention" => $data['idSourceIntervention'],
      "idCibleIntervention"  => $data['idCibleIntervention'],
      "idSourceActeCCAM"     => $data['idSourceActeCCAM'],
      "idCibleActeCCAM"      => $data['idCibleActeCCAM'],
      "acteCCAM"             => $acteCCAM
    );
  }
  
  function handle(CHPrimXMLAcquittementsServeurActivitePmsi $dom_acq, CMbObject $mbObject, $data) {
  }
}