<?php

global $AppUI;
require_once $AppUI->getModuleClass("dPsante400", "mouvement400");

class CMouvSejourEcap extends CMouvement400 {  
  const STATUS_ETABLISSEMENT = 0;
  const STATUS_FONCTION      = 1;
  const STATUS_PRATICIEN     = 2;
  const STATUS_PATIENT       = 3;
  const STATUS_SEJOUR        = 4;
  const STATUS_OPERATION     = 5;
  const STATUS_ACTES         = 6;
  const STATUS_NAISSANCE     = 7;
  
  public $sejour = null;
  public $etablissement = null;
  public $fonction = null;
  public $patient = null;
  public $praticiens = array();
  public $operations = array();
  public $naissance = null;
  
  protected $id400Sej = null;
  protected $id400EtabECap = null;
  protected $id400Pat = null;
  protected $id400Prats = array();
  protected $id400Opers = array();
  
  function __construct() {
    $this->base = "ECAPFILE";
    $this->table = "TRSJ0";
    $this->prodField = "ETAT";
    $this->idField = "INDEX";
    $this->typeField = "TRACTION";
  }

  function synchronize() {
    if ($this->type == "S") {
      $this->trace("synchronisation annul�e", "Mouvement de type suppression");
      return;
    }

    $this->syncEtablissement();
    $this->syncFonction();
    
    // Praticien du s�jour si aucune DHE
    $this->syncPatient();
    $this->syncSejour();
    $this->syncDHE();
    $this->syncOperations();
    $this->syncNaissance();
  }
  
  function loadExtensionField($table, $field, $id, $exists) {
    if (!$exists) {
      return;
    }
    
    $queryValues = array (
      $this->id400EtabECap->id400,
      $table,
      $field,
      $id
    );
    
    $query = "SELECT * " .
        "\nFROM $this->base.ECTXPF " .
        "\nWHERE TXCIDC = ? " .
        "\nAND TXTABL = ? " .
        "\nAND TXZONE = ?" .
        "\nAND TXCL = ?";
    
    $tx400 = new CRecordSante400;
    $tx400->query($query, $queryValues);
    return $tx400->data["TXTX"];
  }
  
  function syncEtablissement() {
    $CIDC = $this->consume("A_CIDC");
    $etab400 = new CRecordSante400();
    $etab400->query("SELECT * FROM $this->base.ECCIPF WHERE CICIDC = $CIDC");
    $this->etablissement = new CGroups;
    $this->etablissement->text           = $etab400->consume("CIZIDC");
    $this->etablissement->raison_sociale = $this->etablissement->text;
    $this->etablissement->adresse        = $etab400->consumeMulti("CIZAD1", "CIZAD2");
    $this->etablissement->cp             = $etab400->consume("CICPO");
    $this->etablissement->ville          = $etab400->consume("CIZLOC");
    $this->etablissement->tel            = $etab400->consume("CIZTEL");
    $this->etablissement->fax            = $etab400->consume("CIZFAX");
    $this->etablissement->web            = $etab400->consume("CIZWEB");
    $this->etablissement->mail           = $etab400->consume("CIMAIL");
    $this->etablissement->domiciliation  = $etab400->consume("CIFINS");

    $this->id400EtabECap = new CIdSante400();
    $this->id400EtabECap->id400 = $etab400->consume("CICIDC");
    $this->id400EtabECap->bindObject($this->etablissement);

    $id400EtabSHS = new CIdSante400();
    $id400EtabSHS->loadLatestFor($this->etablissement, "SHS");
    $id400EtabSHS->last_update = mbDateTime();
    $id400EtabSHS->id400 =  $etab400->consume("CICSHS");
    $id400EtabSHS->store();
    
    $this->trace($etab400->data, "Donn�es �tablissement non import�es");

    $this->markStatus(self::STATUS_ETABLISSEMENT, 1);
  }
  
  function syncFonction() {
    $this->fonction = new CFunctions();
    $this->fonction->group_id = $this->etablissement->group_id;
    $this->fonction->loadMatchingObject();
    $this->fonction->text = "Import eCap";
    $this->fonction->color = "00FF00";

    $id400Func = new CIdSante400();
    $id400Func->id400 = $this->id400EtabECap->id400;
    $id400Func->bindObject($this->fonction);
    
    $this->markStatus(self::STATUS_FONCTION, 1);
  }
   
  function syncPraticien($CPRT) {
    if (array_key_exists($CPRT, $this->praticiens)) {
      return;
    }
    
    $query = "SELECT * FROM $this->base.ECPRPF " .
        "\nWHERE PRCIDC = ? " .
        "\nAND PRCPRT = ?";
    $queryValues = array (
      $this->id400EtabECap->id400, 
      $CPRT,
    );
     
    $prat400 = new CRecordSante400();
    $prat400->loadOne($query, $queryValues);

    $nomsPraticien     = split(" ", $prat400->consume("PRZNOM"));
    $prenomsPraticiens = split(" ", $prat400->consume("PRZPRE"));

    $praticien = new CMediusers;
    $praticien->_user_type = 3; // Chirurgien
    $praticien->_user_username = substr(strtolower($prenomsPraticiens[0] . $nomsPraticien[0]), 0, 20);
    $praticien->_user_last_name  = join(" ", $nomsPraticien);
    $praticien->_user_first_name = join(" ", $prenomsPraticiens);
    $praticien->_user_email      = $prat400->consume("PRMAIL");
    $praticien->_user_phone      = mbGetValue(
      $prat400->consume("PRZTL1"), 
      $prat400->consume("PRZTL2"), 
      $prat400->consume("PRZTL3"));
    $praticien->_user_adresse    = $prat400->consumeMulti("PRZAD1", "PRZAD2");
    $praticien->_user_cp         = $prat400->consume("PRCPO");
    $praticien->_user_ville      = $prat400->consume("PRZVIL");
    $praticien->adeli            = $prat400->consume("PRCINC");
    $praticien->actif            = $prat400->consume("PRACTI");
    $praticien->deb_activite     = $prat400->consumeDate("PRDTA1");
    $praticien->fin_activite     = $prat400->consumeDate("PRDTA2");
    
    // Import de la sp�cialit� eCap
    $CSPE = $prat400->consume("PRCSPE");
    $spec400 = new CRecordSante400;
    $spec400->query("SELECT * FROM $this->base.ECSPPF WHERE SPCSPE= $CSPE");
    $LISP = $spec400->consume("SPLISP");
    $praticien->commentaires = "Sp�cialit� eCap : $LISP";
    
    // Import des sp�cialit�s � nomenclature officielles
    $CSP = array (
      $CSP1 = $prat400->consume("PRCSP1"),
      $CSP2 = $prat400->consume("PRCSP2"),
      $CSP3 = $prat400->consume("PRCSP3")
    );
    
    $CSP = join(" ", $CSP);
    $praticien->commentaires .= "\nSp�cialit� (Nomenclature) : $CSP";
    
    $pratDefault = new CMediusers;
    $pratDefault->function_id = $this->fonction->function_id;

    // Gestion des id400
    $tag = "CIDC:{$this->id400EtabECap->id400}";
    $id400Prat = new CIdSante400();
    $id400Prat->id400 = $CPRT;
    $id400Prat->tag = $tag;
    $id400Prat->bindObject($praticien, $pratDefault);
    $this->id400Prats[$CPRT] = $id400Prat;
    
    $id400PratSHS = new CIdSante400();
    $id400PratSHS->loadLatestFor($praticien, "SHS $tag");
    $id400PratSHS->last_update = mbDateTime();
    $id400PratSHS->id400 =  $prat400->consume("PRSIH");
    $id400PratSHS->store();
    
    $this->praticiens[$CPRT] = $praticien;
    $this->trace($prat400->data, "Donn�es praticien non import�es");
    $this->markStatus(self::STATUS_PRATICIEN, count($this->praticiens));
  }

  function syncPatient() {
    static $transformSexe = array (
      "1" => "m",
      "2" => "f",
    );
    
    static $transformNationalite = array (
      "" => "local",
      "F" => "local",
      "E" => "etranger",
    );

    $DMED = $this->consume("A_DMED");
    $pat400 = new CRecordSante400();
    $pat400->query("SELECT * FROM $this->base.ECPAPF WHERE PACIDC = ? AND PADMED = ?", array (
      $this->id400EtabECap->id400,
      $DMED));

    $this->patient = new CPatient;
    $this->patient->nom              = $pat400->consume("PAZNOM");
    $this->patient->prenom           = $pat400->consume("PAZPRE");
    $this->patient->nom_jeune_fille  = $pat400->consume("PAZNJF");
    $this->patient->naissance        = $pat400->consumeDate("PADNAI");
    
    $this->patient->sexe             = @$transformSexe[$pat400->consume("PAZSEX")];
    $this->patient->adresse          = $pat400->consumeMulti("PAZAD1", "PAZAD2");
    $this->patient->ville            = $pat400->consume("PAZVIL");
    $this->patient->cp               = $pat400->consume("PACPO");
    $this->patient->tel              = $pat400->consumeTel("PAZTL1");
    $this->patient->tel2             = $pat400->consumeTel("PAZTL2");
    
    $this->patient->matricule         = $pat400->consume("PANSEC") . $pat400->consume("PACSEC");
    $this->patient->rang_beneficiaire = $pat400->consume("PARBEN");;

//    $this->patient->pays              = $pat400->consume("PAZPAY");
    $this->patient->nationalite       = @$transformNationalite[$pat400->consume("PACNAT")];
    $this->patient->lieu_naissance    = null;
    $this->patient->profession        = null;
        
    $this->patient->employeur_nom     = null;
    $this->patient->employeur_adresse = null;
    $this->patient->employeur_ville   = null;
    $this->patient->employeur_cp      = null;
    $this->patient->employeur_tel     = null;
    $this->patient->employeur_urssaf  = null;

    $this->patient->prevenir_nom     = null;
    $this->patient->prevenir_prenom  = null;
    $this->patient->prevenir_adresse = null;
    $this->patient->prevenir_ville   = null;
    $this->patient->prevenir_cp      = null;
    $this->patient->prevenir_tel     = null;
    $this->patient->prevenir_parente = null;
    
    $this->patient->medecin_traitant = null;
    $this->patient->medecin1         = null;
    $this->patient->medecin2         = null;
    $this->patient->medecin3         = null;
    $this->patient->incapable_majeur = null;
    $this->patient->ATNC             = null;
    $this->patient->SHS              = null;
    $this->patient->regime_sante     = null;
    $this->patient->rques            = null;
    $this->patient->listCim10        = null;
    $this->patient->cmu              = null;
    $this->patient->ald              = null;

    
    // Gestion des id400
    $tag = "CIDC:{$this->id400EtabECap->id400}";
    $this->id400Pat = new CIdSante400();
    $this->id400Pat->id400 = $DMED;
    $this->id400Pat->tag = $tag;
    $this->id400Pat->bindObject($this->patient);

    $this->trace($pat400->data, "Donn�es patients non import�es");
    $this->markStatus(self::STATUS_PATIENT, 1);
  }

  function syncDHE() {
    $queryValues = array (
      $this->id400EtabECap->id400,
      $this->id400Sej->id400,
      $this->id400Pat->id400,
    );
    
    $query = "SELECT * " .
        "\nFROM $this->base.ECATPF " .
        "\nWHERE ATCIDC = ? " .
        "\nAND ATNDOS = ? " .
        "\nAND ATDMED = ?";

    // Recherche de la DHE
    $dheECap = new CRecordSante400();
    $dheECap->query($query, $queryValues);
    if (!$dheECap->data) {
      return;
    }
    
    $this->trace($dheECap->data, "DHE Trouv�e"); 

    $NSEJ = null;//$dheECap->consume("ATNSEJ");
    $IDAT = $dheECap->consume("ATIDAT");
    
    // Praticien de la DHE prioritaire
    $CPRT = $dheECap->consume("ATCPRT");
    $this->syncPraticien($CPRT);
    $this->sejour->praticien_id = $this->praticiens[$CPRT]->_id;
    
    // Cration du log de cr�ation du s�jour
    $log = new CUserLog();
    $log->setObject($this->sejour);
    $log->user_id = $this->praticiens[$CPRT]->_id;
    $log->type = "create";
    $log->date = mbDateTime($dheECap->consumeDate("ATDDHE"));
    $log->loadMatchingObject();

    // Motifs d'hospitalisations
    $CMOT = $dheECap->consume("ATCMOT");
    $motECap = new CRecordSante400();
    $motECap->loadOne("SELECT * FROM $this->base.ECMOPF WHERE MOCMOT = ?", array($CMOT));
    $LIMO = $motECap->consume("MOLIMO");
    $this->sejour->rques = "Motif: $LIMO";
    
    // Horodatage
    $entree = $dheECap->consumeDateTime("ATDTEN", "ATHREN");
    $duree = $dheECap->consume("ATDMSJ");
    $sortie = mbDate("+$duree days", $entree);
    
    // Type d'hospitalisation
    $typeHospi = array (
      "0" => "comp",
      "1" => "ambu",
      "2" => "exte",
      "3" => "seances",
      "4" => "ssr",
      "5" => "psy"
    );
    
    $TYHO = $dheECap->consume("ATTYHO");
    $this->sejour->type = $typeHospi[$TYHO];
    
    // Hospitalisation
    $this->sejour->chambre_seule      = $dheECap->consume("ATCHPA");
    $this->sejour->hormone_croissance = $dheECap->consume("ATHOCR");
    $this->sejour->lit_accompagnant   = $dheECap->consume("ATLIAC");
    $this->sejour->isolement          = $dheECap->consume("ATISOL");
    $this->sejour->television         = $dheECap->consume("ATTELE");
    $this->sejour->repas_diabete      = $dheECap->consume("ATDIAB");
    $this->sejour->repas_sans_sel     = $dheECap->consume("ATSASE");
    $this->sejour->repas_sans_residu  = $dheECap->consume("ATSARE");
    
    // Champs �tendus
    $TXCL = "0$IDAT"; // La cl� demande 10 chiffres
    $OBSH = $this->loadExtensionField("ECATPF", "ATOBSH", $TXCL, $dheECap->consume("ATOBSH"));
    $EXBI = $this->loadExtensionField("ECATPF", "ATEXBI", $TXCL, $dheECap->consume("ATEXBI"));
    $TRPE = $this->loadExtensionField("ECATPF", "ATTRPE", $TXCL, $dheECap->consume("ATTRPE"));
    $REM  = $this->loadExtensionField("ECATPF", "ATREM" , $TXCL, $dheECap->consume("ATREM" ));
    
    $remarques = array (
      "Services: $OBSH",
      "Autre: $REM"
    );
    
    
    $this->sejour->rques = join($remarques, "\n");

    $tags[] = "DHE";
    $tags[] = "CIDC:{$this->id400EtabECap->id400}";
    $this->idDHECap = new CIdSante400();
    $this->idDHECap->id400 = $IDAT;
    $this->idDHECap->tag = join(" ", $tags);
    $this->idDHECap->bindObject($this->sejour);

    // $TRPE et $EXBI � g�rer
    
    $this->markStatus(self::STATUS_SEJOUR, 2);
  }
  
  
  function syncOperations() {
    $query = "SELECT * " .
        "\nFROM $this->base.ECINPF " .
        "\nWHERE INCIDC = ? " .
        "\nAND INNDOS = ? " .
        "\nAND INDMED = ?";

    $queryValues = array (
      $this->id400EtabECap->id400,
      $this->id400Sej->id400,
      $this->id400Pat->id400,
    );
    
    // Recherche des op�rations
    $opersECap = CRecordSante400::multipleLoad($query, $queryValues);
    foreach ($opersECap as $operECap) {
      $this->trace($operECap->data, "Op�ration trouv�e"); 

      $operECap->valuePrefix = "IN";
      
      $operation = new COperation;
      $operation->sejour_id = $this->sejour->_id;
      $operation->chir_id = $this->sejour->praticien_id;

      // Entr�e/sortie pr�vue/r�elle
      $entree_prevue = $operECap->consumeDateTime("DTEP", "HREP");
      $sortie_prevue = $operECap->consumeDateTime("DTSP", "HRSP");
      $entree_reelle = $operECap->consumeDateTime("DTER", "HREM");
      $sortie_reelle = $operECap->consumeDateTime("DTSR", "HRSR");

      $duree_prevue = $sortie_prevue > $entree_prevue ? 
        mbTimeRelative($entree_prevue, $sortie_prevue) : 
        "01:00:00"; 
      
      $operation->date = mbDate($entree_prevue);
      $operation->time_operation = mbTime($entree_prevue);
      $operation->temp_operation = $duree_prevue;
      $operation->entree_salle = mbTime($entree_reelle);
      $operation->sortie_salle = mbTime($sortie_reelle);
      
      // Anesth�siste
      if ($CPRT = $operECap->consume("CPRT")) {
        $this->syncPraticien($CPRT);
        $operation->anesth_id = $this->praticiens[$CPRT]->_id;
      }
      
      // Textes
      $operation->libelle = $operECap->consume("CNAT");
      $operation->rques   = $operECap->consume("CCOM");
            
      // Dossier d'anesth�sie
      $CASA = $operECap->consume("CASA"); // A mettre dans une CConsultAnesth
      
//      // Motif d'hospit +/- �quivalent � nos prototoles
//      $CMOT = $operECap->consume("CMOT");
//      $this->trace($CMOT, "Motif");
//      
//      $query = "SELECT *" .
//          "\nFROM $this->base.ECMOPF" .
//          "\nWHERE MOCMOT = ?";
//                
//      $queryValues = array (
//        $CMOT,
//      );
//    
//      $motifECap = new CRecordSante400;
//      $motifECap->query($query, $queryValues);
//      $motifECAP->valuePrefix = "MO";

      // Gestion des id400
      $CINT = $operECap->consume("CINT");
      $tags = array (
        "CINT",
        "CIDC:{$this->id400EtabECap->id400}"
      );
      $id400Oper = new CIdSante400();
      $id400Oper->id400 = $CINT;
      $id400Oper->tag = join($tags, " ");
      $id400Oper->bindObject($operation);
      $this->id400Opers[$CINT] = $id400Oper;      
      
      $this->operations[$CINT] = $operation;
      $this->syncActes($CINT);
    }

    // Status
    $this->markStatus(self::STATUS_OPERATION, count($opersECap));
    if (!count($opersECap)) {
      $this->markStatus(self::STATUS_ACTES, 0);
      
    }
  }

  function syncActes($CINT) {
    $operation = $this->operations[$CINT];
    
    $query = "SELECT * " .
        "\nFROM $this->base.ECACPF " .
        "\nWHERE ACCIDC = ? " .
        "\nAND ACCINT = ? ";

    $queryValues = array (
      $this->id400EtabECap->id400,
      $CINT,
    );

    $actesECap = CRecordSante400::multipleLoad($query, $queryValues);
    
    foreach ($actesECap as $acteECap) {
      $acteECap->valuePrefix = "AC";
      
      $acte = new CActeCCAM;

      // Champs issus de l'op�ration
      $acte->operation_id = $operation->_id;
      $acte->execution = mbDateTime($operation->sortie_salle, $operation->date);
      
      // Praticien ex�cutant
      $CPRT = $acteECap->consume("CPRT");
      $this->syncPraticien($CPRT);
      $acte->executant_id = $this->praticiens[$CPRT]->_id;
      
      // Codage
      $acte->code_acte     = $acteECap->consume("CDAC");
      $acte->code_activite = $acteECap->consume("CACT");
      $acte->code_phase    = $acteECap->consume("CPHA");
      $acte->modificateurs = $acteECap->consume("CMOD");
      $acte->montant_depassement = $acteECap->consume("MDEP");
      
      // Gestion des id400
      $tags = array (
        "CIDC:{$this->id400EtabECap->id400}",
        "CINT:$CINT",
        "CPRT:$CPRT",
        "Acte:$acte->code_acte-$acte->code_activite-$acte->code_phase",
      );

      $id400acte = new CIdSante400();
      $id400acte->id400 = $CINT;
      $id400acte->tag = join($tags, " ");
      $id400acte->bindObject($acte);

      $this->trace($acteECap->data, "Acte trouv�");
      $this->trace($acte, "Acte � sauver");
            
      // Ajout du code dans l'op�ration
      if (!in_array($acte->code_acte, $operation->_codes_ccam)) {
        $operation->_codes_ccam[] = $acte->code_acte;
        $operation->store();
      }
    }

    $this->markStatus(self::STATUS_ACTES, count($actesECap));
  }

  function syncSejour() {
    $CPRT = $this->consume("A_CPRT");
    $this->syncPraticien($CPRT);
    
    $NDOS = $this->consume("A_NDOS");

    $this->sejour = new CSejour;  
    $this->sejour->group_id     = $this->etablissement->_id;
    $this->sejour->patient_id   = $this->patient->_id;
    $this->sejour->praticien_id = $this->praticiens[$CPRT]->_id;
    
    $entree = $this->consumeDateTime("A_DTEN", "A_HREN");
    $sortie = $this->consumeDateTime("A_DTSO", "A_HRSO");
    
    switch ($this->consume("A_PRES")) {
      case "0": // Pr�vu
      $this->sejour->entree_prevue = $entree;
      $this->sejour->sortie_prevue = $sortie;
      break;
    
      case "1": // Pr�sent
      $this->sejour->entree_reelle = $entree;
      $this->sejour->sortie_prevue = $sortie;
      
      case "2": // Sorti
      $this->sejour->entree_reelle = $entree;
      $this->sejour->sortie_reelle = $sortie;
      break;
    }
        
    // Gestion des identifiants
    $tags[] = "CIDC:{$this->id400EtabECap->id400}";
    $tags[] = "DMED:{$this->id400Pat->id400}";
    $this->id400Sej = new CIdSante400();
    $this->id400Sej->id400 = $NDOS;
    $this->id400Sej->tag = join(" ", $tags);
    $this->id400Sej->bindObject($this->sejour);
    
    // Rectifications sur les dates pr�vues
    // Pervents updateFormFields()
    $this->sejour->_hour_entree_prevue = null;
    $this->sejour->_hour_sortie_prevue = null;
    
    $nullDate = "0000-00-00 00:00:00";
    if ($this->sejour->entree_prevue == $nullDate) {
      $this->sejour->entree_prevue = $this->sejour->entree_reelle;
    }
    
    if ($this->sejour->sortie_prevue == $nullDate) {
      $this->sejour->sortie_prevue = 
        $this->sejour->sortie_reelle > $this->sejour->entree_reelle ? 
        $this->sejour->sortie_reelle : // Date de sortie fournie, on l'utilise 
        mbDateTime("+ 1 days", $this->sejour->entree_prevue); // On simule la date de sortie
    }

    // Sauvegarde apr�s rectifications
    if ($msg = $this->sejour->store()) {
      throw new Exception($msg);
    }
    
    
    $this->trace($this->sejour->getProps(), "S�jour sauvegard�");
    $this->markStatus(self::STATUS_SEJOUR, 1);
  }
  
  function syncNaissance() {
    $this->markStatus(self::STATUS_NAISSANCE, 0);
  }
}
?>
