<?php /* $Id $ */

/**
 * @package Mediboard
 * @subpackage hprim21
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

class CHPrim21Reader {
  
  var $has_header                 = false;
  
  // Champs header
  var $separateur_champ           = null;
  var $separateur_sous_champ      = null;
  var $repetiteur                 = null;
  var $echappement                = null;
  var $separateur_sous_sous_champ = null;
  var $nom_fichier                = null;
  var $mot_de_passe               = null;
  var $id_emetteur                = null;
  var $sous_type                  = null;
  var $tel_emetteur               = null;
  var $carac_trans                = null;
  var $id_recepteur               = null;
  var $commentaire                = null;
  var $mode_traitement            = null;
  var $version                    = null;
  var $type                       = null;
  var $date                       = null;
  
  // Nombre d'�l�ments
   var $nb_patients  = null;
  
  // Log d'erreur
  var $error_log     = array();
  
  var $_echange_hprim21 = null;
  
  function bindEchange($fileName = null) {
    $this->_echange_hprim21->date_production   = mbDateTime($this->date);
    $this->_echange_hprim21->version           = $this->version;
    $this->_echange_hprim21->nom_fichier       = $this->nom_fichier;
    $dest_hprim21 = new CDestinataireHprim21();
    $dest_hprim21->register($this->id_emetteur);
    $this->_echange_hprim21->sender_id         = isset($dest_hprim21->_id) ? $dest_hprim21->_id : 0;
    // Read => Mediboard
    $this->_echange_hprim21->receiver_id       = null;
    $this->_echange_hprim21->sous_type         = $this->sous_type;
    $this->_echange_hprim21->type              = $this->type;
    $this->_echange_hprim21->date_echange      = mbDateTime();
    if ($fileName)
      $this->_echange_hprim21->_message        = file_get_contents($fileName);
    
    return $this->_echange_hprim21;
  }
  
  function readFile($fileName = null, $file = null) {
    if ($fileName) {
      $file = fopen( $fileName, 'rw' );
    }
    
    if (!$file) {
      $this->error_log[] = "Fichier non trouv�";
      return;
    }
    
    $i = 0;
    $lines = array();
    while (!feof($file)){
      if (!$i) {
        $header = trim(fgets($file, 1024));
        $i++;
      } else {
        $_line = trim(fgets($file, 1024));
        if ($_line) {
          // On v�rifie si la ligne est un Addendum
          if (substr($_line, 0, 2) == "A|") {
            $lines[$i-1] .= substr($_line, 2);
          } else {
            $lines[$i] = $_line;
            $i++;
          }
        }
      }
    }

    fclose($file);
    
    // Lecture de l'en-t�te
    if (!$this->segmentH($header)) {
      return;
    }    
    
    // Lecture du message
    switch ($this->sous_type) {
      // De demandeur (d'analyses ou d'actes de radiologie) � ex�cutant
      case "ADM" :
        // Transfert de donn�es d'admission
        return $this->messageADM($lines);
        break;
      case "ORM" :
        // transfert de demandes d'analyses = prescription
        return $this->messageORM($lines);
        break;
      case "REG" :
        // transfert de donn�es de r�glement
        return $this->messageREG($lines);
        break;
      //D'ex�cutant � demandeur 
      case "ORU" :
        // transfert de r�sultats d'analyses
        return $this->messageORU($lines);
        break;
      case "FAC" :
        // transfert de donn�es de facturation
        return $this->messageFAC($lines);
        break;
      // Bidirectionnel
      case "ERR" :
        // transfert de messages d'erreur
        return $this->messageERR($lines);
        break;
      default :
        $this->error_log[] = "Type de message non reconnu";
        return false;
    }
  }
  
  // Fonction de prise en charge des messages
  function messageADM($lines) {
    $nbLine = count($lines);
    $i = 1;
    while ($i <= $nbLine && $this->getTypeLine($lines[$i]) == "P") {
      $patient = new CHprim21Patient();
      if(!$this->segmentP($lines[$i], $patient)) {
        return false;
      }
      $i++;
      if ($i < $nbLine && $this->getTypeLine($lines[$i]) == "AP") {
        if(!$this->segmentAP($lines[$i], $patient)) {
          return false;
        }
        $i++;
        while ($i < $nbLine && $this->getTypeLine($lines[$i]) == "AC") {
          $complementaire = new CHprim21Complementaire();
          if(!$this->segmentAC($lines[$i], $complementaire, $patient)) {
            return false;
          }
          $i++;
        }
      }
    }
    if (!isset($lines[$i]) || $this->getTypeLine($lines[$i]) != "L") {
      $this->error_log[] = "Erreur dans la suite des segments du message ADM";
      return false;
    }
    return $this->segmentL($lines[$i]);
  }
  
  function messageORM($lines) {
    $this->error_log[] = "Message ORM non pris en charge";
    return false;
  }
  
  function messageREG($lines) {
    $this->error_log[] = "Message REG non pris en charge";
    return false;
  }
  
  function messageORU($lines) {
    $this->error_log[] = "Message ORU non pris en charge";
    return false;
  }
  
  function messageFAC($lines) {
    $this->error_log[] = "Message FAC non pris en charge";
    return false;
  }
  
  function messageERR($lines) {
    $this->error_log[] = "Message ERR non pris en charge";
    return false;
  }
  
  // Fonctions de prise en charge des segments
  function getTypeLine($line) {
    $lines = explode($this->separateur_champ, $line);
    $type = reset($lines);
    return $type;
  }
  
  function segmentH($line) {
    if (strlen($line) < 6) {
      $this->error_log[] = "Segment header trop court";
      return false;
    }
    $this->separateur_champ           = $line[1];
    $this->separateur_sous_champ      = $line[2];
    $this->repetiteur                 = $line[3];
    $this->echappement                = $line[4];
    $this->separateur_sous_sous_champ = $line[5];
    $line = substr($line, 7);
    $champs = explode($this->separateur_champ, $line);
    if (count($champs) < 12) {
      $this->error_log[] = "Champs manquant dans le segment header";
      return false;
    }
    $this->nom_fichier       = $champs[0];
    $this->mot_de_passe      = $champs[1];
    $emetteur                = explode($this->separateur_sous_champ, $champs[2]);
    $this->id_emetteur       = $emetteur[0];
    $this->sous_type         = $champs[4];
    $this->carac_trans       = $champs[6];
    $recepteur               = explode($this->separateur_sous_champ, $champs[7]);
    $this->commentaire       = $champs[8];
    $this->mode_traitement   = $champs[9];
    $version_type            = explode($this->separateur_sous_champ, $champs[10]);
    $this->version           = $version_type[0];
    $this->type              = $version_type[1];
    $this->date              = $champs[11];
    $this->has_header        = true;
    
    return true;
  }
  
  function segmentP($line, &$patient) {
    if (!$this->has_header) {
      return false;
    }
   
    if (!$patient->bindToLine($line, $this)) {
      return false;
    }
    $patient->store();
    $medecin = new CHprim21Medecin();
    if ($medecin->bindToLine($line, $this)) {
      if ($medecin->external_id) {
        $medecin->store();
      }
    }
    $sejour = new CHprim21Sejour();
    if ($sejour->bindToLine($line, $this, $patient, $medecin)) {
      if ($sejour->external_id) {
        $sejour->store();
      }
    }
    return true;
  }
  
  function segmentOBR($line) {
    mbTrace($line, "Demande d'analyses ou d'actes");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentOBX($line) {
    mbTrace($line, "R�sultat d'un test");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentC($line) {
    mbTrace($line, "Commentaire");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentL($line) {
    if(!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentA($line) {
    mbTrace($line, "Addendum");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentFAC($line) {
    mbTrace($line, "En-t�te de facture");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentACT($line) {
    mbTrace($line, "Ligne de facture");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentREG($line) {
    mbTrace($line, "El�ment de r�glement");
    if(!$this->has_header) {
      return false;
    }
  }
  
  function segmentAP($line, &$patient) {
    if(!$this->has_header) {
      return false;
    }
    $patient->bindAssurePrimaireToLine($line, $this);
    $patient->store();
    return true;
  }
  
  function segmentAC($line, &$complementaire, $patient) {
    if(!$this->has_header) {
      return false;
    }
    $complementaire->bindToLine($line, $this, $patient);
    $complementaire->store();
    return true;
  }
  
  function segmentERR($line) {
    mbTrace($line, "Message d'erreur");
    if(!$this->has_header) {
      return false;
    }
  }

}

?>
