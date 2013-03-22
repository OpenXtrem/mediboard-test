<?php /* $Id:acteccam.class.php 8144 2010-02-25 11:05:27Z rhum1 $ */

/**
 *  @package Mediboard
 *  @subpackage mediusers
 *  @version $Revision:8144 $
 *  @author Thomas Despoix
 */

/**
 * Classe servant � g�rer les enregistrements des actes CCAM pendant les
 * interventions
 */

class CActeCCAM extends CActe {
  static $coef_associations = array (
    "1" => 100,
    "2" => 50,
    "3" => 75,
    "4" => 100,
    "5" => 100,
  );
  
  // DB Table key
  public $acte_id;

  // DB Fields
  public $code_acte;
  public $code_activite;
  public $code_phase;
  public $execution;
  public $modificateurs;
  public $motif_depassement;
  public $commentaire;
  public $code_association;
  public $extension_documentaire;
  public $rembourse;
  public $charges_sup;
  public $regle;
  public $regle_dh;
  public $signe;
  public $sent;
  public $exoneration;
  public $lieu;
  public $ald;

  // Form fields
  public $_modificateurs = array();
  public $_rembex;
  public $_anesth;
  public $_anesth_associe;
  public $_linked_actes;
  public $_guess_association;
  public $_guess_regle_asso;

  // Behaviour fields
  public $_adapt_object = false;
  public $_calcul_montant_base = false;
  
  // Object references
  public $_ref_code_ccam;
  
  public $_activite;
  public $_phase;
  
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'acte_ccam';
    $spec->key   = 'acte_id';
    return $spec;
  }
  
  function getProps() {
    $props = parent::getProps();
    $props["code_acte"]              = "code notNull ccam seekable";
    $props["code_activite"]          = "num notNull min|0 max|99";
    $props["code_phase"]             = "num notNull min|0 max|99";
    $props["execution"]              = "dateTime notNull";
    $props["modificateurs"]          = "str maxLength|4";
    $props["motif_depassement"]      = "enum list|d|e|f|n";
    $props["commentaire"]            = "text";
    $props["code_association"]       = "enum list|1|2|3|4|5";
    $props["extension_documentaire"] = "enum list|1|2|3|4|5|6";
    $props["rembourse"]              = "bool default|1";
    $props["charges_sup"]            = "bool";
    $props["regle"]                  = "bool default|0";
    $props["regle_dh"]               = "bool default|0";
    $props["signe"]                  = "bool default|0";
    $props["sent"]                   = "bool default|0";
    $props["lieu"]                   = "enum list|C|D default|C";
    $props["exoneration"]            = "enum list|N|13|17 default|N";
    $props["ald"]                    = "bool";

    $props["_rembex"]             = "bool";
    
    return $props;
  }
  
  /**
   * Check the number of codes compared to the number of actes
   *
   * @return string check-like message
   */
  function checkEnoughCodes() {
    $this->loadTargetObject();
    if (!$this->_ref_object || !$this->_ref_object->_id) {
      return;
    }
    
    $acte = new CActeCCAM();
    $where = array();
    if ($this->_id) {

      // dans le cas de la modification
      $where["acte_id"]     = "<> '$this->_id'";  
    }
    
    $this->completeField("code_acte", "object_class", "object_id", "code_activite", "code_phase");
    $where["code_acte"]     = "= '$this->code_acte'";
    $where["object_class"]  = "= '$this->object_class'";
    $where["object_id"]     = "= '$this->object_id'";
    $where["code_activite"] = "= '$this->code_activite'";
    $where["code_phase"]    = "= '$this->code_phase'";
    
    $this->_ref_siblings = $acte->loadList($where);

    // retourne le nombre de code semblables
    $siblings = count($this->_ref_siblings);
    
    // compteur d'acte prevue ayant le meme code_acte dans l'intervention
    $nbCode = 0;
    foreach ($this->_ref_object->_codes_ccam as $code) {
      // si le code est sous sa forme complete, garder seulement le code
      $code = substr($code, 0, 7);
      if ($code == $this->code_acte) {
        $nbCode++;
      }
    }
    if ($siblings >= $nbCode) {
      return "$this->_class-check-already-coded";
    }
  }

  function canDeleteEx(){
    parent::canDeleteEx();
   
    // Test si la consultation est valid�e
    if ($msg = $this->checkCoded()) {
      return $msg;
    }
  }

  function check() {
    // Test si la consultation est valid�e
    if ($msg = $this->checkCoded()) {
      return $msg;
    }
      
    // Test si on n'a pas d'incompatibilit� avec les autres codes
    if ($msg = $this->checkCompat()) {
      return $msg;
    }
    
    if ($msg = $this->checkEnoughCodes()) {
      // Ajoute le code si besoins � l'objet
      if ($this->_adapt_object || $this->_forwardRefMerging) {
        $this->_ref_object->_codes_ccam[] = $this->code_acte;
        $this->_ref_object->updateDBCodesCCAMField();
        
        /*if ($this->_forwardRefMerging) {
          $this->_ref_object->_merging = true;
        }*/
        
        return $this->_ref_object->store();
      }
      return $msg;
    }
     
    return parent::check(); 
    // datetime_execution: attention � rester dans la plage de l'op�ration
  }
  
  /**
   * CActe redefinition
   *
   * @return string Serialised full code
   */
  function makeFullCode() {
    return $this->_full_code = 
      $this->code_acte.
      "-". $this->code_activite.
      "-". $this->code_phase.
      "-". $this->modificateurs.
      "-". str_replace("-", "*", $this->montant_depassement).
      "-". $this->code_association.
      "-". $this->rembourse.
      "-". $this->charges_sup;
  }

  /**
   * CActe redefinition
   *
   * @param string $code Serialised full code
   *
   * @return void
   */
  function setFullCode($code){
    $details = explode("-", $code);
    if (count($details) > 2) {
      $this->code_acte     = $details[0];
      $this->code_activite = $details[1];
      $this->code_phase    = $details[2];
      
      // Modificateurs
      if (count($details) > 3) {
        $this->modificateurs = $details[3];
      } 
      
      // D�passement
      if (count($details) > 4) {
        $this->montant_depassement = str_replace("*", "-", $details[4]);
      }
      
      // Code association
      if (count($details) > 5) {
        $this->code_association = $details[5];
      }
      
      // Remboursement
      if (count($details) > 6) {
        $this->rembourse = $details[6];
      }
      
      // Remboursement
      if (count($details) > 6) {
        $this->charges_sup = $details[6];
      }
      
      $this->updateFormFields();
    }
  }
  
  
  function getPrecodeReady() {
    return $this->code_acte && $this->code_activite && $this->code_phase !== null;
  }
  
  function updateFormFields() {
    parent::updateFormFields();
    $this->_modificateurs = str_split($this->modificateurs);
    CMbArray::removeValue("", $this->_modificateurs);
    $this->_shortview  = $this->code_acte;
    $this->_view       = "$this->code_acte-$this->code_activite-$this->code_phase-$this->modificateurs";
    $this->_viewUnique = $this->_id ? $this->_id : $this->_view;
    $this->_anesth = ($this->code_activite == 4);
    
    // Remboursement exceptionnel
    $code = CCodeCCAM::get($this->code_acte, CCodeCCAM::LITE);
    $this->_rembex = $this->rembourse && $code->remboursement == 3 ? '1' : '0';
  }
  
  function updateMontantBase() {
    return $this->montant_base = $this->getTarif();  
  }
  
  /**
   * Check if acte is compatible with others already coded
   * @return bool
   */
  function checkCompat() {
    if ($this->object_class == "CConsultation" || $this->_permissive) {
      return;
    }
    $this->loadRefCodeCCAM();
    $this->_ref_code_ccam->getChaps();
    $this->getLinkedActes(false);
    $_acte = new CActeCCAM();
    
    /**
    // Cas du nombre d'actes
    // Cas g�n�ral : 2 actes au plus
    $distinctCodes = array();
    foreach($this->_linked_actes as $_acte) {
      $_acte->loadRefCodeCCAM();
      if(!in_array($_acte->_ref_code_ccam->code, $distinctCodes)) {
        $distinctCodes[] = $_acte->_ref_code_ccam->code;
      }
    }
    if(count($distinctCodes) >= 2) {
      return "Vous ne pouvez pas coder plus de deux actes";
    }
    */
    
    // Cas des incompatibilit�s
    if(CAppUI::conf("dPsalleOp CActeCCAM check_incompatibility")) {
      foreach ($this->_linked_actes as $_acte) {
        $_acte->loadRefCodeCCAM();
        $_acte->_ref_code_ccam->getActesIncomp();
        $incomps = CMbArray::pluck($_acte->_ref_code_ccam->incomps, "code");
        if (in_array($this->code_acte, $incomps)) {
          return "Acte incompatible avec le codage de " . $_acte->_ref_code_ccam->code;
        }
      }
      
      // Cas des associations d'anesth�sie
      if ($this->_ref_code_ccam->chapitres["1"]["rang"] == "18.01.") {
        $asso_possible = false;
        foreach ($this->_linked_actes as $_acte) {
          $_acte->loadRefCodeCCAM();
          $_acte->_ref_code_ccam->getActivites();
          $activites = CMbArray::pluck($_acte->_ref_code_ccam->activites, "numero");
          if (!in_array("4", $activites)) {
            $asso_possible = true;
          }
        }
        if (!$asso_possible) {
          return "Aucun acte cod� ne permet actuellement d'associer une Anesth�sie Compl�mentaire";
        }
      }
    }
  }

  function checkFacturable() {
    $this->completeField("facturable");

    // Si acte non facturable on met le code d'asso � aucun
    if (!$this->facturable) {
      $this->code_association = "";
    }

    // Si on repasse le facturable � 1 on remet � la montant base � la valeur de l'acte et le d�passement � 0
    if ($this->fieldModified("facturable", 1)) {
      $this->montant_depassement  = 0;
      $this->motif_depassement    = "";
      $this->_calcul_montant_base = true;
    }
  }
  
  function store() {
    // On test si l'acte CCAM est facturable
    $this->checkFacturable();

    // Sauvegarde du montant de base
    if ($this->_calcul_montant_base) {
      $this->updateFormFields();
      $this->updateMontantBase();
    }

    // En cas d'une modification autre que signe, on met signe � 0
    if (!$this->signe) {
      // Chargement du oldObject
      $oldObject = new CActeCCAM();
      $oldObject->load($this->_id);
    
      // Parcours des objets pour detecter les modifications
      $_modif = 0;
      foreach ($oldObject->getPlainFields() as $propName => $propValue) {
        if (($this->$propName !== null) && ($propValue != $this->$propName)) {
          $_modif++;
        }
      }
      if ($_modif) {
        $this->signe = 0;
      }
    }
    
    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }
  }
  
  function loadRefObject(){
    $this->loadTargetObject(true);
  }

  function loadRefCodeCCAM() {
    return $this->_ref_code_ccam = CCodeCCAM::get($this->code_acte, CCodeCCAM::FULL);
  }
   
  function loadRefsFwd() {
    parent::loadRefsFwd();

    $this->loadRefExecutant();
    $this->loadRefCodeCCAM();
  }
  
  function getAnesthAssocie() {
    if (!$this->_ref_code_ccam) {
      $this->loadRefsFwd();
    }
    if ($this->code_activite != 4 && !isset($this->_ref_code_ccam->activites[4])) {
      foreach ($this->_ref_code_ccam->assos as $code_anesth) {
        if (substr($code_anesth["code"], 0, 4) == "ZZLP") {
          $this->_anesth_associe = $code_anesth["code"];
          return $this->_anesth_associe;
        }
      }
    }
    return;
  }
  
  function getFavoris($user_id, $class) {
    $condition = ( $class == "" ) ? "executant_id = '$user_id'" : "executant_id = '$user_id' AND object_class = '$class'";
    $sql = "SELECT code_acte, object_class, COUNT(code_acte) as nb_acte
            FROM acte_ccam
            WHERE $condition
            GROUP BY code_acte
            ORDER BY nb_acte DESC
            LIMIT 20";
    $codes = $this->_spec->ds->loadlist($sql);
    return $codes;
  }
  
  function getPerm($permType) {
    if (!$this->_ref_object) {
      $this->loadRefObject();
    }
    return $this->_ref_object->getPerm($permType);
  }
  
  function getLinkedActes($same_executant = true) {
    $acte = new CActeCCAM();
    
    $where = array();
    $where["acte_id"]       = "<> '$this->_id'";
    $where["object_class"]  = "= '$this->object_class'";
    $where["object_id"]     = "= '$this->object_id'";
    $where["facturable"]    = "= '1'";
    //$where["code_activite"] = "= '$this->code_activite'";
    if ($same_executant) {
      $where["executant_id"]  = "= '$this->executant_id'";
    }
    
    $this->_linked_actes = $acte->loadList($where);
    return $this->_linked_actes;
  }
  
  function guessAssociation() {
    /*
     * Calculs initiaux
     */

    // Chargements initiaux
    if (!$this->facturable) {
      $this->_guess_association = "";
      $this->_guess_regle_asso  = "X";

      return $this->_guess_association;
    }

    $this->loadRefCodeCCAM();
    $this->getLinkedActes();
    if (count($this->_linked_actes) > 3) {
      $this->_guess_association = "?";
      $this->_guess_regle_asso  = "?";

      return $this->_guess_association;
    }
    foreach ($this->_linked_actes as &$acte) {
      $acte->loadRefCodeCCAM();
    }
    
    // Nombre d'actes
    $numActes = count($this->_linked_actes) + 1;
    
    // Calcul de la position tarifaire de l'acte
//    $tarif = $this->_ref_code_ccam->activites[$this->code_activite]->phases[$this->code_phase]->tarif;
    $tarif = $this->getTarifSansAssociationNiCharge();
    $orderedActes = array();
    $orderedActes[$this->_id] = $tarif;
    foreach ($this->_linked_actes as $_acte) {
//      $tarif = $acte->_ref_code_ccam->activites[$acte->code_activite]->phases[$acte->code_phase]->tarif;
      $tarif = $acte->getTarifSansAssociationNiCharge();
      $orderedActes[$acte->_id] = $tarif;
    }
    ksort($orderedActes);
    arsort($orderedActes);
    $position = array_search($this->_id, array_keys($orderedActes));
    
    // Nombre d'actes des chap. 12, 13 et 14 (chirurgie membres, tronc et cou)
    $numChap121314 = 0;
    if (in_array($this->_ref_code_ccam->chapitres[0]["db"], array("000012", "000013", "000014"))) {
      $numChap121314++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if (in_array($linkedActe->_ref_code_ccam->chapitres[0]["db"], array("000012", "000013", "000014"))) {
        $numChap121314++;
      }
    }
    
    // Nombre d'actes du chap. 18.01
    $numChap1801 = 0;
    if ($this->_ref_code_ccam->chapitres[0]["db"] == "000018" && $this->_ref_code_ccam->chapitres[1]["db"] == "000001") {
      $numChap1801++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if ($linkedActe->_ref_code_ccam->chapitres[0]["db"] == "000018" && $linkedActe->_ref_code_ccam->chapitres[1]["db"] == "000001") {
        $numChap1801++;
      }
    }
    
    // Nombre d'actes du chap. 18.02
    $numChap1802 = 0;
    if ($this->_ref_code_ccam->chapitres[0]["db"] == "000018" && $this->_ref_code_ccam->chapitres[1]["db"] == "000002") {
      $numChap1802++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if ($linkedActe->_ref_code_ccam->chapitres[0]["db"] == "000018" && $linkedActe->_ref_code_ccam->chapitres[1]["db"] == "000002") {
        $numChap1802++;
      }
    }
    
    // Nombre d'actes du chap. 18
    $numChap18 = $numChap1801 + $numChap1802;
    
    // Nombre d'actes du chap. 19.01
    $numChap1901 = 0;
    if ($this->_ref_code_ccam->chapitres[0]["db"] == "000019" && $this->_ref_code_ccam->chapitres[1]["db"] == "000001") {
      $numChap1901++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if ($linkedActe->_ref_code_ccam->chapitres[0]["db"] == "000019" && $linkedActe->_ref_code_ccam->chapitres[1]["db"] == "000001") {
        $numChap1901++;
      }
    }
    
    // Nombre d'actes du chap. 19.02
    $numChap1902 = 0;
    if ($this->_ref_code_ccam->chapitres[0]["db"] == "000019" && $this->_ref_code_ccam->chapitres[1]["db"] == "000002") {
      $numChap1902++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if ($linkedActe->_ref_code_ccam->chapitres[0]["db"] == "000019" && $linkedActe->_ref_code_ccam->chapitres[1]["db"] == "000002") {
        $numChap1902++;
      }
    }
     
    // Nombre d'actes des chap. 02, 03, 05 � 10, 16, 17
    $numChap02 = 0;
    $listChaps = array("000002", "000003", "000005", "000006", "000007", "000008", "000009", "000010", "000016", "000017");
    if (in_array($this->_ref_code_ccam->chapitres[0]["db"], $listChaps)) {
      $numChap02++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if (in_array($linkedActe->_ref_code_ccam->chapitres[0]["db"], $listChaps)) {
        $numChap02++;
      }
    }
     
    // Nombre d'actes des chap. 01, 04, 11, 15
    $numChap0115 = 0;
    $listChaps = array("000001", "000004", "000011", "000015");
    if (in_array($this->_ref_code_ccam->chapitres[0]["db"], $listChaps)) {
      $numChap0115++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if (in_array($linkedActe->_ref_code_ccam->chapitres[0]["db"], $listChaps)) {
        $numChap0115++;
      }
    }
     
    // Nombre d'actes des chap. 01, 04, 11, 12, 13, 14, 15, 16
    $numChap0116 = 0;
    $listChaps = array("000001", "000004", "000011", "000012", "000013", "000014", "000015", "000016");
    if (in_array($this->_ref_code_ccam->chapitres[0]["db"], $listChaps)) {
      $numChap0116++;
    }
    foreach ($this->_linked_actes as $linkedActe) {
      if (in_array($linkedActe->_ref_code_ccam->chapitres[0]["db"], $listChaps)) {
        $numChap0116++;
      }
    }
    
    // Le praticien est-il un ORL
    $pratORL = false;
    if ($this->object_class == "COperation") {
      $this->loadRefExecutant();
      $this->_ref_executant->loadRefDiscipline();
      if ($this->_ref_executant->_ref_discipline->_compat == "ORL") {
        $pratORL = true;
      }
    }
    
    // Diagnostic principal en S ou T avec l�sions multiples
    // Diagnostic principal en C (carcinologie)
    $DPST = false;
    $DPC  = false;
    $membresDiff = false;
    
    if ($this->object_class == "COperation") {
      $this->loadRefObject();
      $operation =& $this->_ref_object;
      $operation->loadRefSejour();
      $sejour =& $operation->_ref_sejour;
      if ($sejour->DP) {
        if ($sejour->DP[0] == "S" || $sejour->DP[0] == "T") {
          $DPST = true;
          $membresDiff = true;
        }
        if ($sejour->DP[0] == "C") {
          $DPC = true;
        }
      }
      if ($operation->cote == "bilat�ral") {
        $membresDiff = true;
      }
    }
    
    // Association d'1 ex�r�se, d'1 curage et d'1 reconstruction
    $assoEx  = false;
    $assoCur = false;
    $assoRec = false;
    if ($numActes == 3) {
      if (stripos($this->_ref_code_ccam->libelleLong, "ex�r�se")) {
        $assoEx = true;
      }
      if (stripos($this->_ref_code_ccam->libelleLong, "curage")) {
        $assoCur = true;
      }
      if (stripos($this->_ref_code_ccam->libelleLong, "reconstruction")) {
        $assoRec = true;
      }
      foreach ($this->_linked_actes as $linkedActe) {
        if (stripos($linkedActe->_ref_code_ccam->libelleLong, "ex�r�se")) {
          $assoEx = true;
        }
        if (stripos($linkedActe->_ref_code_ccam->libelleLong, "curage")) {
          $assoCur = true;
        }
        if (stripos($linkedActe->_ref_code_ccam->libelleLong, "reconstruction")) {
          $assoRec = true;
        }
      }
    }
    $assoExCurRec = $assoEx && $assoCur && $assoRec;
    
    
    /*
     * Application des r�gles
     */

    if (!$this->_id) {
      $this->_guess_association = "-";
      $this->_guess_regle_asso  = "-";
      return $this->_guess_association;
    }
    
    // Cas d'un seul actes (r�gle A)
    if ($numActes == 1) {
      $this->_guess_association = "";
      $this->_guess_regle_asso  = "A";
      return $this->_guess_association;
    }
    
    // 1 actes + 1 acte du chap. 18.02 ou du chap. 19.02 (r�gles B)
    if ($numActes == 2) {
      // 1 acte + 1 geste compl�mentaire chap. 18.02 (r�gle B)
      if ($numChap1802 == 1) {
        $this->_guess_association = "";
        $this->_guess_regle_asso  = "B";
        return $this->_guess_association;
      }
      // 1 acte + 1 suppl�ment des chap. 19.02 (r�gle B)
      if ($numChap1902 == 1) {
        $this->_guess_association = "";
        $this->_guess_regle_asso  = "B";
        return $this->_guess_association;
      }
    }
    
     
    // 1 acte + 1 ou pls geste compl�mentaire chap. 18.02 + 1 ou pls suppl�ment des chap. 19.02 (r�gle C)
    if ($numActes >= 3 && $numActes - ($numChap1802 + $numChap1902) == 1 && $numChap1802 && $numChap1902) {
      $this->_guess_association = "1";
      $this->_guess_regle_asso  = "C";
      return $this->_guess_association;
    }
    
    // 1 acte + pls suppl�ment des chap. 19.02 (r�gle D)
    if ($numActes >= 3 && $numActes - $numChap1902 == 1) {
      $this->_guess_association = "1";
      $this->_guess_regle_asso  = "D";
      return $this->_guess_association;
    }
    
    // 1 acte + 1 acte des chap. 02, 03, 05 � 10, 16, 17 ou 19.01 (r�gle E)
    if ($numActes == 2 && ($numChap02 == 1 || $numChap1901 == 1)) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "E";
          break;
        case 1 :
          $this->_guess_association = "2";
          $this->_guess_regle_asso  = "E";
          break;
      }
      return $this->_guess_association;
    }
    
    // 1 acte + 1 acte des chap. 02, 03, 05 � 10, 16, 17 ou 19.01 + 1 acte des chap. 18.02 ou 19.02 (r�gle F)
    if ($numActes == 3 && ($numChap02 == 1 || $numChap1901 == 1) && ($numChap1802 == 1 || $numChap1902 == 1)) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "F";
          break;
        case 1 :
          if (($this->_ref_code_ccam->chapitres[0] == "18" || $this->_ref_code_ccam->chapitres[0] == "19") && $this->_ref_code_ccam->chapitres[1] == "02") {
            $this->_guess_association = "1";
            $this->_guess_regle_asso  = "F";
          }
          else {
            $this->_guess_association = "2";
            $this->_guess_regle_asso  = "F";
          }
          break;
        case 2 :
          if (($this->_ref_code_ccam->chapitres[0] == "18" || $this->_ref_code_ccam->chapitres[0] == "19") && $this->_ref_code_ccam->chapitres[1] == "02") {
            $this->_guess_association = "1";
            $this->_guess_regle_asso  = "F";
          }
          else {
            $this->_guess_association = "2";
            $this->_guess_regle_asso  = "F";
          }
          break;
      }
      return $this->_guess_association;
    }
    
    // 2 actes des chap. 01, 04, 11 ou 15 sur des membres diff�rents (r�gle G)
    if ($numActes == 2 && $numChap0115 == 2 && $membresDiff) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "G";
          break;
        case 1 :
          $this->_guess_association = "3";
          $this->_guess_regle_asso  = "G";
          break;
      }
      return $this->_guess_association;
    }
    
    // 2 actes des chap. 12, 13 ou 14 sur des membres diff�rents (r�gle G2)
    if ($numActes == 2 && $numChap121314 == 2 && $membresDiff) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "G2";
          break;
        case 1 :
          $this->_guess_association = "3";
          $this->_guess_regle_asso  = "G2";
          break;
      }
      return $this->_guess_association;
    }
    
    // 3 actes des chap. 12, 13 ou 14 sur des membres diff�rents (r�gle G3)
    if ($numActes == 3 && $numChap121314 == 3 && $membresDiff) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "G3";
          break;
        case 1 :
          $this->_guess_association = "3";
          $this->_guess_regle_asso  = "G3";
          break;
        case 3 :
          $this->_guess_association = "2";
          $this->_guess_regle_asso  = "G3";
          break;
      }
      return $this->_guess_association;
    }
    
    // 3 actes des chap. 01, 04 ou 11 � 16 avec DP en S ou T (l�sions traumatiques multiples) (r�gle H)
    if ($numActes == 3 && $numChap0116 == 3 && $DPST) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "H";
          break;
        case 1 :
          $this->_guess_association = "3";
          $this->_guess_regle_asso  = "H";
          break;
        case 2 :
          $this->_guess_association = "2";
          $this->_guess_regle_asso  = "H";
          break;
      }
    }
    
    // 3 actes, chirurgien ORL, DP en C (carcinologie) et association d'1 ex�r�se, d'1 curage et d'1 reconstruction (r�gle I)
    if ($numActes == 3 && $pratORL && $DPC && $assoExCurRec) {
      switch ($position) {
        case 0 :
          $this->_guess_association = "1";
          $this->_guess_regle_asso  = "I";
          break;
        case 1 :
          $this->_guess_association = "2";
          $this->_guess_regle_asso  = "I";
          break;
        case 2 :
          $this->_guess_association = "2";
          $this->_guess_regle_asso  = "I";
          break;
      }
    }
    
    // Cas g�n�ral pour plusieurs actes (r�gle Z)
    switch ($position) {
      case 0 :
        $this->_guess_association = "1";
        $this->_guess_regle_asso  = "Z";
        break;
      case 1 :
        $this->_guess_association = "2";
        $this->_guess_regle_asso  = "Z";
        break;
      default :
        $this->_guess_association = "X";
        $this->_guess_regle_asso  = "Z";
    }
    
    return $this->_guess_association;
  }
  
  function getTarifSansAssociationNiCharge() {
    // Tarif de base
    $code = $this->loadRefCodeCCAM();
    $phase = $code->activites[$this->code_activite]->phases[$this->code_phase];
    $this->_tarif_sans_asso = $phase->tarif;
    
    // Application des modificateurs
    $forfait     = 0;
    $coefficient = 100;
    foreach ($this->_modificateurs as $modif) {
      $result = $code->getForfait($modif);
      $forfait     += $result["forfait"];
      $coefficient += $result["coefficient"] - 100;
    }
    
    return $this->_tarif_sans_asso = ($this->_tarif_sans_asso * ($coefficient / 100) + $forfait);    
  }
  
  function getTarif() {
    $this->_tarif = $this->getTarifSansAssociationNiCharge();
    
    // Coefficient d'association
    $code = $this->loadRefCodeCCAM();
    $this->_tarif *= ($code->getCoeffAsso($this->code_association) / 100);
    
    // Charges suppl�mentaires
    if ($this->charges_sup) {
      $this->_tarif += $code->_phase->charges;
    }
    
    return $this->_tarif;
  }
  
  static function getNGAP($code) {
    $ds = CSQLDataSource::get("ccamV2");
    $query = $ds->prepare("SELECT * FROM ccam_ngap WHERE code_ccam = %", $code);
    $result = $ds->exec($query);
    
    if ($ds->numRows($result)) {
      $row = $ds->fetchArray($result);
      return array(
        "fd" => array(
          "montant_enfant" => $row["montant_enfant"],
          "montant_adulte" => $row["montant_adulte"]
        ),
        "ngap" => array(
          array("code_ngap_1"   => $row["code_ngap_1"],
                "coefficient_1" => $row["coefficient_1"]),
          array("code_ngap_2"   => $row["code_ngap_2"],
                "coefficient_2" => $row["coefficient_2"]),
          array("code_ngap_3"   => $row["code_ngap_3"],
                "coefficient_3" => $row["coefficient_3"])));
    }
  }
}
