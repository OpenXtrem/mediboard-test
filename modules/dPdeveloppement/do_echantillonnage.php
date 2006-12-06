<?php /* $Id: $ */

/**
* @package Mediboard
* @subpackage system
* @version $Revision: $
* @author S�bastien Fillonneau
*/

global $AppUI, $canRead, $canEdit, $m;

if(!$canRead) {
    $AppUI->redirect("m=system&a=access_denied");
}

class CEchantillonnage {
  var $class  = null;
  var $object = null;
  var $listObjects = array();
  
  function CEchantillonnage ($class) {
    $this->class = $class;
    $this->object = new $class;
  }
  
  function renew() {
    $this->object = new $this->class;
  }
  
  function loadListArray($tab){
    foreach($tab as $value){
      $this->renew();
      if($this->object->load($value)){
        $this->listObjects[$this->object->_id] = $this->object;
      }
    }
  }
  
  function load($object_id){
    $this->object->load($object_id);
    $this->listObjects[$this->object->_id] = $this->object;
  }
  
  function store(&$log, $logfile = true) {
    if(!$msg = $this->object->store()) {
      $this->listObjects[$this->object->_id] = $this->object;
      if($logfile){
        $log .= $this->object->_class_name.": ".$this->object->_id."\r\n";
      }
    }else{
      return $msg; 
    }
  }
  
  function setField($field, $value) {
    $this->object->$field = $value;
  }
  
  function setManyFields($tabFields) {
    foreach($tabFields as $field => $value) {
      if(is_array($value)) {
        $this->object->$field = $this->getRandValue($value);
      } else {
        $this->object->$field = $value;
      }
    }
  }
  
  function getRandValue($tab, $nb = null) {
    if($nb){
      $listKey = array_rand($tab,$nb);
      if(!is_array($listKey)){$listKey = array($listKey);}
      return $listKey;
    }else{
      if(isset($tab["field"])){
        $field = $tab["field"];
        $aValues =& $tab["values"];
        $object = $aValues[array_rand($aValues)];
        return $object->$field;
      }else{
        return $tab[array_rand($tab)];
      }
    }
  }
  
}

// R�cup�ration des donn�es
$etablissement     = mbGetValueFromPost("etablissement"     ,"Etablissement Test");
$_create_group     = mbGetValueFromPost("_create_group"     , 0);
$groups_selected   = mbGetValueFromPost("groups_selected"   , null);
$debut             = mbGetValueFromPost("debut"             , date("Y-m-d"));
$duree             = mbGetValueFromPost("duree"             , 1);

$_nb_cab           = mbGetValueFromPost("_nb_cab"           , 1);
$_nb_anesth        = mbGetValueFromPost("_nb_anesth"        , 1);
$_nb_salles        = mbGetValueFromPost("_nb_salles"        , 1);
$_nb_services      = mbGetValueFromPost("_nb_services"      , 1);
$services_selected = mbGetValueFromPost("services_selected" , array());
$fct_selected      = mbGetValueFromPost("fct_selected"      , array());
$salles_selected   = mbGetValueFromPost("salles_selected"   , array());

$_nb_pat           = mbGetValueFromPost("_nb_pat"           , 1);
$_nb_prat          = mbGetValueFromPost("_nb_prat"          , 1);
$prat_selected     = mbGetValueFromPost("prat_selected"     , array());

$_nb_plages        = mbGetValueFromPost("_nb_plages"        , 1);
$_nb_consult       = mbGetValueFromPost("_nb_consult"       , 1);
$_nb_plagesop      = mbGetValueFromPost("_nb_plagesop"      , 1);
$_nb_interv        = mbGetValueFromPost("_nb_interv"        , 1);
$_nb_chambre       = mbGetValueFromPost("_nb_chambre"       , 1);
$_nb_lit           = mbGetValueFromPost("_nb_lit"           , 1);

$log_file = "tmp/echantillonnage.log";
$log_text = "##\r\n## Echantillonnage du ".date("d/m/Y � H:i:s")."\r\n##\r\n";

$aNomFamille = array("ADAM" ,"ALBERT" ,"ALEXANDRE" ,"ANDRE" ,"ANTOINE" ,"ARNAUD" ,"AUBERT" ,"AUBRY" ,"BAILLY" ,"BARBE" ,"BARBIER" ,"BARON" ,"BARRE" ,"BARTHELEMY" ,"BENARD" ,"BENOIT" ,"BERGER" ,"BERNARD" ,"BERTIN" ,"BERTRAND" ,"BESNARD" ,"BESSON" ,"BIGOT" ,"BLANC" ,"BLANCHARD" ,"BLANCHET" ,"BONNET" ,"BOUCHER" ,"BOUCHET" ,"BOULANGER" ,"BOURGEOIS" ,"BOUSQUET" ,"BOUVIER" ,"BOYER" ,"BRETON" ,"BRIAND" ,"BRUN" ,"BRUNEL" ,"BRUNET" ,"BUISSON" ,"CAMUS" ,"CARLIER" ,"CARON" ,"CARPENTIER" ,"CARRE" ,"CHARLES" ,"CHARPENTIER" ,"CHARRIER" ,"CHAUVIN" ,"CHEVALIER" ,"CHEVALLIER" ,"CLEMENT" ,"COLAS" ,"COLIN" ,"COLLET" ,"COLLIN" ,"CORDIER" ,"COSTE" ,"COULON" ,"COURTOIS" ,"COUSIN" ,"DA SILVA" ,"DANIEL" ,"DAVID" ,"DELATTRE" ,"DELAUNAY" ,"DELMAS" ,"DENIS" ,"DESCHAMPS" ,"DEVAUX" ,"DIDIER" ,"DUBOIS" ,"DUFOUR" ,"DUMAS" ,"DUMONT" ,"DUPONT" ,"DUPUIS" ,"DUPUY" ,"DURAND" ,"DUVAL" ,"ETIENNE" ,"FABRE" ,"FAURE" ,"FERNANDEZ" ,"FERRAND" ,"FLEURY" ,"FONTAINE" ,"FOURNIER" ,"FRANCOIS" ,"GAILLARD" ,"GARCIA" ,"GARNIER" ,"GAUDIN" ,"GAUTHIER" ,"GAUTIER" ,"GAY" ,"GEORGES" ,"GERARD" ,"GERMAIN" ,"GILBERT" ,"GILLET" ,"GIRARD" ,"GIRAUD" ,"GONZALEZ" ,"GREGOIRE" ,"GRONDIN" ,"GROS" ,"GUERIN" ,"GUICHARD" ,"GUILLAUME" ,"GUILLET" ,"GUILLON" ,"GUILLOT" ,"GUILLOU" ,"GUYOT" ,"HAMON" ,"HARDY" ,"HEBERT" ,"HENRY" ,"HERVE" ,"HOARAU" ,"HUBERT" ,"HUET" ,"HUMBERT" ,"IMBERT" ,"JACOB" ,"JACQUES" ,"JACQUET" ,"JEAN" ,"JOLY" ,"JOUBERT" ,"JULIEN" ,"KLEIN" ,"LACROIX" ,"LAMBERT" ,"LAMY" ,"LANGLOIS" ,"LAPORTE" ,"LAUNAY" ,"LAURENT" ,"LE GALL" ,"LE GOFF" ,"LE ROUX" ,"LEBLANC" ,"LEBON" ,"LEBRETON" ,"LEBRUN" ,"LECLERC" ,"LECLERCQ" ,"LECOMTE" ,"LEDUC" ,"LEFEBVRE" ,"LEFEVRE" ,"LEGER" ,"LEGRAND" ,"LEGROS" ,"LEJEUNE" ,"LELIEVRE" ,"LEMAIRE" ,"LEMAITRE" ,"LEMOINE" ,"LEROUX" ,"LEROY" ,"LESAGE" ,"LEVEQUE" ,"LOPEZ" ,"LOUIS" ,"LUCAS" ,"MAHE" ,"MAILLARD" ,"MAILLOT" ,"MALLET" ,"MARCHAL" ,"MARCHAND" ,"MARECHAL" ,"MARIE" ,"MARTEL" ,"MARTIN" ,"MARTINEZ" ,"MARTY" ,"MASSE" ,"MASSON" ,"MATHIEU" ,"MAURY" ,"MENARD" ,"MERCIER" ,"MEUNIER" ,"MEYER" ,"MICHAUD" ,"MICHEL" ,"MILLET" ,"MONNIER" ,"MOREAU" ,"MOREL" ,"MORIN" ,"MORVAN" ,"MOULIN" ,"MULLER" ,"NICOLAS" ,"NOEL" ,"OLIVIER" ,"OLLIVIER" ,"PARIS" ,"PASCAL" ,"PASQUIER" ,"PAUL" ,"PAYET" ,"PELLETIER" ,"PEREZ" ,"PERRET" ,"PERRIER" ,"PERRIN" ,"PERROT" ,"PETIT" ,"PHILIPPE" ,"PICARD" ,"PICHON" ,"PIERRE" ,"PINEAU" ,"POIRIER" ,"PONS" ,"POULAIN" ,"PREVOST" ,"RAYMOND" ,"RAYNAUD" ,"REGNIER" ,"REMY" ,"RENARD" ,"RENAUD" ,"RENAULT" ,"REY" ,"REYNAUD" ,"RICHARD" ,"RIVIERE" ,"ROBERT" ,"ROBIN" ,"ROCHE" ,"RODRIGUEZ" ,"ROGER" ,"ROLLAND" ,"ROUSSEAU" ,"ROUSSEL" ,"ROUX" ,"ROY" ,"ROYER" ,"SANCHEZ" ,"SAUVAGE" ,"SCHMITT" ,"SCHNEIDER" ,"SIMON" ,"TANGUY" ,"TESSIER" ,"THOMAS" ,"VALLEE" ,"VASSEUR" ,"VERDIER" ,"VIDAL" ,"VINCENT" ,"VOISIN" ,"WEBER" );
$aPrenom_h   = array("Enzo" ,"Hugo" ,"Lucas" ,"Th�o" ,"Math�o" ,"Thomas" ,"Baptiste" ,"L�o" ,"Cl�ment" ,"Louis" ,"Nathan" ,"Alexandre" ,"Quentin" ,"Romain" ,"Tom" ,"Matt�o" ,"Maxime" ,"Antoine" ,"Benjamin" ,"Mathis" ,"Valentin" ,"Robin" ,"Nicolas" ,"Paul" ,"Arthur" ,"Martin" ,"�than" ,"Julien" ,"Noah" ,"Victor" ,"Gabriel" );
$aPrenom_f   = array("Emma" ,"Clara" ,"Manon" ,"Anais" ,"L�a" ,"Chlo�" ,"Lucie" ,"Camille" ,"Marie" ,"Jade" ,"Eva" ,"Louise" ,"Mathilde" ,"Julie" ,"Oc�ane" ,"Laura" ,"Ilona" ,"Charlotte" ,"Emilie" ,"Sarah" ,"Cl�mence" ,"Lilou" ,"Justine" ,"Elisa" ,"Pauline" ,"Lisa" ,"Lena" ,"Lou" ,"Louane" ,"Ma�lis" ,"Perrine" );
$aPrenoms    = array("m"=>$aPrenom_h, "f"=>$aPrenom_f, "j"=>$aPrenom_f);

// Etablissement
$group = new CEchantillonnage("CGroups");
if(!$_create_group && $groups_selected){
  $group->load($groups_selected);
}else{
  $tabFields = array("text"           => $etablissement,
                      "raison_sociale" => "[DEMO]");
  $group->setManyFields($tabFields);
  $group->store($log_text);
}

// Cabinets (fonctions) et Praticiens
$aCabinet = array("Cabinet de sp�cialiste"=> array("nb"       => $_nb_cab,
                                                    "profil"    =>"98",
                                                    "curr_type" => "3"),
                   "Cabinet d'anesth�sie"  => array("nb"       => $_nb_anesth,
                                                    "profil"    =>"101",
                                                    "curr_type" => "4"));
$fonctions  = new CEchantillonnage("CFunctions");
$praticiens = new CEchantillonnage("CMediusers");
if(count($fct_selected)){
 $fonctions->loadListArray($fct_selected); 
}
foreach($aCabinet as $title => $value){
  if($value["nb"]){
    $_profile_id = $value["profil"];
    for($i=1; $i<=$value["nb"]; $i++){
      $fonctions->renew();
      $color_r = dechex(rand(8,15));
      $color_v = dechex(rand(8,15));
      $color_b = dechex(rand(8,15));
      $tabFields = array("text"     => "[DEMO]",
                          "color"    => $color_r.$color_r.$color_v.$color_v.$color_b.$color_b,
                          "group_id" => $group->object->_id,
                          "type"     => "cabinet");
      $fonctions->setManyFields($tabFields);
      $fonctions->store($log_text);
      $fonctions->setField("text", $title." ".$fonctions->object->_id);
      $fonctions->store($log_text, false);
      if($_nb_prat){
        for($iPrat=1; $iPrat<=$_nb_prat; $iPrat++){
          $praticiens->renew();
          $tabFields = array("function_id"      => $fonctions->object->_id,
                              "commentaires"     => "[DEMO]",
                              "_user_first_name" => array_merge($aPrenom_h,$aPrenom_f),
                              "_user_last_name"  => $aNomFamille,
                              "_user_type"       => $value["curr_type"]);
          $praticiens->setManyFields($tabFields);
          $prenom      = $praticiens->object->_user_first_name;
          $nom         = $praticiens->object->_user_last_name;
          $praticiens->setField("_user_username", str_replace(" ","",strtolower(substr($prenom,0,1).$nom)));
          $praticiens->setField("_user_password", strtolower($prenom));
          $praticiens->store($log_text);
          
          $user = new CUser;
          $user->load($praticiens->object->_id);
          $msg = $user->copyPermissionsFrom($_profile_id, true);
          $praticiens->object->insFunctionPermission();
          $praticiens->object->insGroupPermission();
        }
      }
    }
  }
}
// Chargement des praticiens selectionn�s
if(count($prat_selected)){
 $praticiens->loadListArray($prat_selected); 
}


// Salles
$salles = new CEchantillonnage("CSalle");
if(count($salles_selected)){
 $salles->loadListArray($salles_selected); 
}
if($_nb_salles){
  for($i=1; $i<=$_nb_salles; $i++){
    $salles->renew();
    $tabFields = array("group_id" => $group->object->_id,
                        "nom"      => "Salle",
                        "stats"    => 0);
    $salles->setManyFields($tabFields);
    $salles->store($log_text);
    $salles->setField("nom", "Salle ".$salles->object->_id);
    $salles->store($log_text, false);
  }
}


// Services - Chambres - Lits
$services = new CEchantillonnage("CService");
$chambres = new CEchantillonnage("CChambre");
$lits     = new CEchantillonnage("CLit");
if(count($services_selected)){
 $services->loadListArray($services_selected); 
}
if($_nb_services){
  for($i=1; $i<=$_nb_services; $i++){
    $services->renew();  
    $tabFields = array("group_id"    => $group->object->_id,
                        "nom"         => "Service",
                        "description" => "[DEMO]");
    $services->setManyFields($tabFields);
    if(!$services->store($log_text)){
      $services->setField("nom", "Service ".$services->object->_id);
      $services->store($log_text, false);
      for($iChambre=1; $iChambre<=$_nb_chambre; $iChambre++){
        $chambres->renew();
        $tabFields = array("service_id"       => $services->object->_id,
                            "nom"              => "Chambre ".$services->object->_id.str_pad($iChambre, 1, "0", STR_PAD_LEFT),
                            "caracteristiques" => "[DEMO]");
        $chambres->setManyFields($tabFields);
        if(!$chambres->store($log_text)){
          $nb_lit_max = rand(1,$_nb_lit);
          for($iLit=1; $iLit<=$nb_lit_max; $iLit++){
            $lits->renew();
            $tabFields = array("chambre_id"       => $chambres->object->_id,
                                "nom"              => "Lit $iLit");
            $lits->setManyFields($tabFields);
            $lits->store($log_text);
          }
        }
      }
    }   
  }
}

// Cr�ation des patients
$patients = new CEchantillonnage("CPatient");
for($i=1; $i<=$_nb_pat; $i++){
  $patients->renew();
  $sexe = rand(0,2);
  $annee_max = date("Y");
  if($sexe==0){
    $sexe = "m";
  }elseif($sexe==1){
    $sexe = "f";
    $annee_max = $annee_max-18;
  }else{
    $sexe = "j";
  }
  $tabFields = array("sexe"   => $sexe,
                      "prenom" => $aPrenoms[$sexe],
                      "nom"    => $aNomFamille,
                      "_jour"  => rand(1,28),
                      "_mois"  => rand(1,12),
                      "_annee" => rand(1900,$annee_max),
                      "rques"  => "[DEMO]");
  $patients->setManyFields($tabFields);
  $patients->store($log_text);
}


/**********************************************************************************/
/**********************************************************************************/


$aPlages = array();
for($i=1; $i<=7; $i++){
  $aPlages[] = array("jour"=>$i, "meridiem"=>"am");
  $aPlages[] = array("jour"=>$i, "meridiem"=>"pm");
}
$libelle   = array("postop","esthetique","generale","speciale");
$listMins  = array(00,15,30,45);
$listHours = array("am"=>array("debut"=>array(8,9,10),
                                "fin"  =>array(11,12,13)),
                    "pm"=>array("debut"=>array(14,15,16),
                                 "fin"  =>array(17,18,19,20)));
$alistMotif = array(null,"visites postop","analyses","controle");
$listHoursOp = array("am"=>array("debut"=>array(8,9),
                                "fin"  =>array(11,12)),
                      "pm"=>array("debut"=>array(13,14),
                                 "fin"  =>array(18,19)));
$aTypeSejour = array(array("type"=>"ambu","entree_h"=>"8" ,"entree_d"=>"0" ,"sortie_d"=>"0"),
                      array("type"=>"comp","entree_h"=>"18","entree_d"=>"-1","sortie_d"=>null),
                      array("type"=>"comp","entree_h"=>"8" ,"entree_d"=>""  ,"sortie_d"=>null),
                      array("type"=>"exte","entree_h"=>"8" ,"entree_d"=>"0" ,"sortie_d"=>"0"));


$premiere_semaine = mbDate("last sunday", $debut);

for($iDate=0; $iDate<=($duree-1); $iDate++){
  // bouche des semaines
  $debut_semaine = $date = mbDate("+".$iDate." week", $premiere_semaine);
  
  
  // Cr�ation des plages de consultations
  $plageConsult= array();
  $plages = new CEchantillonnage("CPlageconsult");

  foreach($praticiens->listObjects as $prat){
    $plageConsult[$prat->_id] = array();
    $lstplages = $plages->getRandValue($aPlages,$_nb_plages);
    foreach($lstplages as $keyplages){
      $plages->renew();
      $donnees_plage =& $aPlages[$keyplages];
      $plage_heure   =& $listHours[$donnees_plage["meridiem"]];
      $date = mbDate("+".$donnees_plage["jour"]." day", $debut_semaine);
      $tabFields = array("date"      => $date,
                          "_freq"     => 15,
                          "_hour_deb" => $plage_heure["debut"],
                          "_min_deb"  => $listMins,
                          "_hour_fin" => $plage_heure["fin"],
                          "_min_fin"  => $listMins,
                          "libelle"   => $libelle,
                          "chir_id"   => $prat->_id);
      $plages->setManyFields($tabFields);
      
      if(!$plages->store($log_text)){
        $plageConsult[$prat->_id][] = $plages->object;
      } 
    }
  }
  
  
  // Creation de consultations
  $consults = new CEchantillonnage("CConsultation");
  foreach($plageConsult as $keyChir=>$listplages){
    $listPlace = array();
    foreach($listplages as $plage){
      for ($i = 0; $i < $plage->_total; $i++) {
        $minutes = $plage->_freq * $i;
        $listPlace[] = array("plage_id"=>$plage->_id,
                              "heure"=>mbTime("+ $minutes minutes", $plage->debut),
                              "chir_id"=>$plage->chir_id);
      }
    }
    $listKeyPlaces = $consults->getRandValue($listPlace,$_nb_consult);
    foreach($listKeyPlaces as $keyPlace){
      $plage = $listPlace[$keyPlace];
      $consults->renew();
      $premiere    = rand(0,3);
      if($premiere==0){
        $consults->setField("_check_premiere", 1);
      }
      $tabFields = array("heure"           => $plage["heure"],
                          "patient_id"      => array("values"=>$patients->listObjects,"field"=>"_id"),
                          "plageconsult_id" => $plage["plage_id"],
                          "motif"           => $alistMotif);
      $consults->setManyFields($tabFields);
      if(!$consults->store($log_text)){
        $chir_id = $plage["chir_id"];
        $praticiens->listObjects[$chir_id]->updateFormFields();
        if($praticiens->listObjects[$chir_id]->isFromType(array("Anesth�siste"))) {
          // Test Anesth�siste
          $consultsAnesth = new CEchantillonnage("CConsultAnesth");
          $consultsAnesth->setField("consultation_id", $consults->object->_id);
          $consultsAnesth->store($log_text);
        }
      }
    }
  } 
  
  // Creation des plagesOp / Intervention et S�jour
  $plageOp    = array();
  $plagesOp   = new CEchantillonnage("CPlageOp");
  $sejours    = new CEchantillonnage("CSejour");
  $operations = new CEchantillonnage("COperation");
  
  foreach($salles->listObjects as $salle){
    $lstplages = $plages->getRandValue($aPlages,$_nb_plagesop); 
    foreach($lstplages as $keyplages){
      $plagesOp->renew();
      $donnees_plage =& $aPlages[$keyplages];
      $plage_heure   =& $listHoursOp[$donnees_plage["meridiem"]];
      $date = mbDate("+".$donnees_plage["jour"]." day", $debut_semaine);
  
      $tabFields = array("chir_id"       => array("values"=>$praticiens->listObjects,"field"=>"_id"),
                          "salle_id"      => $salle->_id,
                          "date"          => $date,
                          "_heuredeb"     => $plage_heure["debut"],
                          "_minutedeb"    => $listMins,
                          "_heurefin"     => $plage_heure["fin"],
                          "_minutefin"    => $listMins,
                          "_min_inter_op" => 15,
                          "_year"         => date("Y"));
      $plagesOp->setManyFields($tabFields);
      if(!$plagesOp->store($log_text)){
        
        for($i=1; $i<=$_nb_interv; $i++){
          // Cr�ation d'un S�jour
          $aType = $sejours->getRandValue($aTypeSejour);
          $sejours->renew();
          $tabFields = array("patient_id"         => array("values"=>$patients->listObjects,"field"=>"_id"),
                             "praticien_id"        => $plagesOp->object->chir_id,
                             "group_id"            => $group->object->_id,
                             "type"                => $aType["type"],
                             "_hour_entree_prevue" => $aType["entree_h"],
                             "_min_entree_prevue"  => $listMins,
                             "_hour_sortie_prevue" => 18,
                             "_min_sortie_prevue"  => $listMins,
                             "_date_entree_prevue" => mbDate($aType["entree_d"]." day", $date),
                             "rques"               => "[DEMO]");
          $sejours->setManyFields($tabFields);
          if($aType["sortie_d"]){
            $sejours->setField("_date_sortie_prevue", mbDate($aType["sortie_d"]." day", $date));
          }else{
            $nbjour = rand(1,5);
            $sejours->setField("_date_sortie_prevue", mbDate("+".$nbjour." day", $date));
          }
          
          if(!$sejours->store($log_text)){
            // Cr�ation d'une invervention
            $operations->renew();
            $tabFields = array("sejour_id"      => $sejours->object->_id,
                                "chir_id"        => $plagesOp->object->chir_id,
                                "plageop_id"     => $plagesOp->object->_id,
                                "salle_id"       => $salle->_id,
                                "date"           => $date,
                                "rques"          => "[DEMO]",
                                "cote"           => $operations->object->_enums["cote"],
                                "rank"           => $i,
                                "codes_ccam"     => array("HBGD038", "GAMA007", "BFGA004", "HHFE002", "MEMC003",
                                                           "NFFC004", "PAGA011", "BGLB001", "QZFA036", "AHPC001"),
                                "_min_op"        => 0,
                                "_hour_op"       => 1);
            $operations->setManyFields($tabFields);
            $operations->store($log_text);
          }  
        }
      } 
    }
  }
}  


// A Faire : Creation de modeles

$logFile = fopen($log_file, "a+");
fwrite($logFile, $log_text."\r\n");
fclose($logFile);

$AppUI->setMsg("Echantillonnage effectu�",UI_MSG_OK);
?>