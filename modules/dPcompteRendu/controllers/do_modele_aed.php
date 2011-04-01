<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPcabinet
* @version $Revision$
* @author Romain Ollivier
*/

global $AppUI, $m;

if( isset($_POST["_do_empty_pdf"])) {
  $compte_rendu_id = CValue::post("compte_rendu_id");
  $compte_rendu = new CCompteRendu();
  $compte_rendu->load($compte_rendu_id);

	$compte_rendu->loadRefsFiles();
  foreach($compte_rendu->_ref_files as $_file) {
    $_file->file_empty();
  }
  CApp::rip();
}

$do = new CDoObjectAddEdit("CCompteRendu", "compte_rendu_id");
$do->redirectDelete = "m=$m&new=1";

// R�cup�ration des marges du modele en fast mode
if (isset($_POST["fast_edit"]) && $_POST["fast_edit"] == 1 && isset($_POST["object_id"]) && $_POST["object_id"] != '') {
	$compte_rendu = new CCompteRendu;
	$compte_rendu->load($_POST["modele_id"]);
  $do->request["margin_top"]    = $compte_rendu->margin_top;
  $do->request["margin_bottom"] = $compte_rendu->margin_bottom;
  $do->request["margin_left"]   = $compte_rendu->margin_left;
  $do->request["margin_right"]  = $compte_rendu->margin_right;
}

if (isset($_POST["_source"])) {
  $_POST["_source"] = stripslashes($_POST["_source"]);
}

// Remplacement des zones de texte libre
if (isset($_POST["_texte_libre"])) {
  $compte_rendu = new CCompteRendu();
	$fields = array();
	$values = array();

	// Remplacement des \n par des <br>
	foreach($_POST["_texte_libre"] as $key=>$_texte_libre) {
	  if (isset($_POST["_empty_texte_libre"][$key]) || $_POST["_texte_libre"][$key] != '') {
	    $fields[] = "[[Texte libre - " . $_POST["_texte_libre_md5"][$key] . "]]";
	    $values[] = nl2br($_POST["_texte_libre"][$key]);
	  }
	}
  $_POST["_source"] = str_ireplace($fields, $values, $_POST["_source"]);
  $_POST["_texte_libre"] = null;
}

if (isset($_POST["_source"])) {
  // Application des listes de choix
  $fields = array();
  $values = array();
  if (isset($_POST["_CListeChoix"])) {
    $listes = $_POST["_CListeChoix"];
    foreach ($listes as $list_id => $options) {
      $options = array_map('htmlentities', $options);
	    $list = new CListeChoix;
	    $list->load($list_id);
      if (isset($_POST["_empty_list"][$list_id])) {
        $values[] = "";
      }
	    else {
  	    if ($options === array(0 => "undef")) {
          continue;
        }
	      CMbArray::removeValue("undef", $options);
	      $values[] = nl2br(implode(", ", $options));
	    }
	    $nom = str_replace("#039;", "#39;", htmlentities($list->nom, ENT_QUOTES));
	    $fields[] = "[Liste - ".$nom."]";
	  }
  }
  
  $_POST["_source"] = str_ireplace($fields, $values, $_POST["_source"]);

  // Application des destinataires
  $destinataires = array();
  foreach($_POST as $key => $value) {
    // Remplacement des destinataires
    if(preg_match("/_dest_([\w]+)_([0-9]+)/", $key, $dest)) {
      $destinataires[] = $dest;
    }
  }
  
  if(count($destinataires)) {
    $object = new $_POST["object_class"];
    $object->load($_POST["object_id"]);
    CDestinataire::makeAllFor($object);
    $allDest = CDestinataire::$destByClass;
    $bodyTag = '<div id=\"body\">';
    
    // On sort l'en-t�te et le pied de page
    $posBody      = strpos($_POST["_source"], $bodyTag);
    if($posBody) {
      $headerfooter = substr($_POST["_source"], 0, $posBody);
      $body         = substr($_POST["_source"], $posBody+strlen($bodyTag), -strlen("</div>") - 1);
    } else {
      $headerfooter = "";
      $body         = $_POST["_source"];
    }
    // On cr�e les fichiers pour chaque destinataire
    $copyTo = "";
    foreach($destinataires as $curr_dest) {
      $copyTo .= $allDest[$curr_dest[1]][$curr_dest[2]]->nom."; ";
    }
    $allSources = array();
    foreach($destinataires as &$curr_dest) {
      $fields = array(
        htmlentities("[Courrier - nom destinataire]"),
        htmlentities("[Courrier - adresse destinataire]"),
        htmlentities("[Courrier - cp ville destinataire]"),
        htmlentities("[Courrier - copie �]")
      );
      $values = array(
        $allDest[$curr_dest[1]][$curr_dest[2]]->nom,
        $allDest[$curr_dest[1]][$curr_dest[2]]->adresse,
        $allDest[$curr_dest[1]][$curr_dest[2]]->cpville,
        $copyTo
      );
      $allSources[] = str_ireplace($fields, $values, $body);
    }
    // On concat�ne les en-t�te, pieds de page et body's
    if($headerfooter) {
      $_POST["_source"] = $headerfooter;
      $_POST["_source"] .= "<div id=\"body\">";
      $_POST["_source"] .= implode("<hr class=\"pageBreak\" />", $allSources);
      $_POST["_source"] .= "</div>";
    } else {
      $_POST["_source"] = implode("<hr class=\"pageBreak\" />", $allSources);
    }
  }
  
}

$do->doBind();
if (intval(CValue::post("del"))) {
  $do->doDelete();
} else {
  $do->doStore();
  // Pour le fast mode en impression navigateur, on envoie la source du document complet.
  $margins = array($do->_obj->margin_top,
                 $do->_obj->margin_right,
                 $do->_obj->margin_bottom,
                 $do->_obj->margin_left);
  $do->_obj->_entire_doc = CCompteRendu::loadHTMLcontent($do->_obj->_source, "doc",'','','','','',$margins);
}

if($do->ajax){
  $do->doCallback();
}

else {
  // Si c'est un compte rendu
  if($do->_obj->object_id && !intval(CValue::post("del"))) {
    $do->redirect = "m=$m&a=edit_compte_rendu&dialog=1&compte_rendu_id=".$do->_obj->_id;
  } 
  // Si c'est un mod�le de compte rendu
  else { 
    $do->redirect = "m=$m&tab=addedit_modeles&compte_rendu_id=".$do->_obj->_id;
  }
  $do->doRedirect();
}
?>