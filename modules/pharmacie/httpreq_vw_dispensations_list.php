<?php /* $Id: $ */

/**
 *  @package Mediboard
 *  @subpackage pharmacie
 *  @version $Revision: $
 *  @author Alexis Granger
 */

global $g;

$service_id = mbGetValueFromGetOrSession('service_id');

// Calcul de date_max et date_min
$date_min = mbGetValueFromGetOrSession('_date_min');
$date_max = mbGetValueFromGetOrSession('_date_max');

$_date_min = $date_min;
$_date_max = $date_max;

// Recherche des prescriptions dont les dates de sejours correspondent
$where = array();
$ljoin = array();
$ljoin['sejour'] = 'prescription.object_id = sejour.sejour_id';
$ljoin['affectation'] = 'sejour.sejour_id = affectation.sejour_id';
$ljoin['lit'] = 'affectation.lit_id = lit.lit_id';
$ljoin['chambre'] = 'lit.chambre_id = chambre.chambre_id';
$ljoin['service'] = 'chambre.service_id = service.service_id';
$where['prescription.type'] = " = 'sejour'";
$where[] = "(sejour.entree_prevue BETWEEN '$date_min' AND '$date_max') OR 
            (sejour.sortie_prevue BETWEEN '$date_min' AND '$date_max') OR
            (sejour.entree_prevue <= '$date_min' AND sejour.sortie_prevue >= '$date_max')"; 
$where['service.service_id'] = " = '$service_id'";

$dispensations = array();
$delivrances = array();
$prescriptions = array();
$produits = array();
$stocks = array();
$quantites_reference = array();
$quantites = array();
$done = array();
$besoin_patients = array();
$stocks_service = array();
$besoin_patient = array();

$prescription = new CPrescription();
$prescriptions = $prescription->loadList($where, null, null, null, $ljoin);
foreach($prescriptions as $_prescription){
  $date_min = $_date_min;
  $date_max = $_date_max;
  
  // Stockage du sejour de la prescription
  $sejour =& $_prescription->_ref_object;
  if(!$sejour->_ref_patient){
  	$sejour->loadRefPatient();
  }
  $patient = &$sejour->_ref_patient;
  
  // On borne les dates aux dates du sejour si besoin
  $date_min = max($sejour->_entree, $date_min);
  $date_max = min($sejour->_sortie, $date_max);

  if ($date_min > $date_max) continue;
  
  $_prescription->loadRefsLinesMed(1,1);
  foreach($_prescription->_ref_prescription_lines as $_line_med){
    $sejour =& $_prescription->_ref_object;
    $patient = $sejour->_ref_patient;
    
    if (!$_line_med->debut) continue;
    
    $cip = $_line_med->code_cip;

    // On remplit les bornes de la ligne avec les dates du sejour si besoin
    $_line_med->_debut_reel = (!$_line_med->_debut_reel) ? $sejour->_entree : $_line_med->_debut_reel;
    $_line_med->_fin_reelle = (!$_line_med->_fin_reelle) ? $sejour->_sortie : $_line_med->_fin_reelle;

    // Si la ligne n'est pas dans les bornes donn�, on en tient pas compte
    if (!($_line_med->_debut_reel >= $date_min && $_line_med->_debut_reel <= $date_max ||
          $_line_med->_fin_reelle >= $date_min && $_line_med->_fin_reelle <= $date_max ||
          $_line_med->_debut_reel <= $date_min && $_line_med->_fin_reelle >= $date_max)){
      continue;
    }
    
    $patients[$cip][$sejour->_ref_patient->_id] = $sejour->_ref_patient;
    $_line_med->_ref_produit->loadConditionnement();
    
    // Calcul de la quantite totale de la ligne en fonction des prises dans les dates indiqu�es
    $_line_med->calculQuantiteLine($date_min, $date_max);
    
    if($_line_med->_quantite_administration){
      $_line_med->_quantite_dispensation = $_line_med->_quantite_administration * $_line_med->_ratio_administration_dispensation;
      if(!isset($dispensations[$_line_med->code_cip]["quantite_administration"])){
        $dispensations[$_line_med->code_cip]["quantite_administration"] = 0;
      }  
      if(!isset($dispensations[$_line_med->code_cip]["quantite_dispensation"])){
        $dispensations[$_line_med->code_cip]["quantite_dispensation"] = 0;
      }     
      $dispensations[$_line_med->code_cip]["quantite_administration"] += $_line_med->_quantite_administration;
      
	    if(strstr($_line_med->_quantite_dispensation,'.')){
	      $_line_med->_quantite_dispensation = ceil($_line_med->_quantite_dispensation);
	    }
      $dispensations[$_line_med->code_cip]["quantite_dispensation"] += $_line_med->_quantite_dispensation;

      @$besoin_patient[$_line_med->code_cip][$patient->_id]["patient"] = $patient; 
      @$besoin_patient[$_line_med->code_cip][$patient->_id]["quantite_administration"] = $_line_med->_quantite_administration;
      @$besoin_patient[$_line_med->code_cip][$patient->_id]["quantite_dispensation"] = $_line_med->_quantite_dispensation;
      
    }
    if($_line_med->_ref_produit->_unite_dispensation){
      $produits[$_line_med->code_cip] = $_line_med->_ref_produit;
    }
  }
  
  // Chargement des perfusions
  $_prescription->loadRefsPerfusions();
  
  foreach($_prescription->_ref_perfusions as $_perfusion){
    if (!(($_perfusion->_debut >= $date_min) && ($_perfusion->_debut <= $date_max))){
     continue;     
    }
    
    $_perfusion->loadRefsLines();
    foreach($_perfusion->_ref_lines as &$_perf_line){
      $produit = $_perf_line->_ref_produit;
      $produit->loadLibellePresentation();
      
      $poids_ok = 1;
      $_unite_prise = str_replace('/kg','',$_perf_line->unite);
      if($_unite_prise != $_perf_line->unite){
        if(!$patient->_ref_constantes_medicales){
	        $patient->loadRefConstantesMedicales();
	      }
        $poids = $patient->_ref_constantes_medicales->poids;
        if($poids){
		      $_perf_line->quantite *= $poids;
		      $_perf_line->_unite_sans_kg = $_unite_prise;
        } else {
          $poids_ok = 0;
          $_perf_line->quantite = 0;
        } 
      }
      
      if($poids_ok){
        $unite_prise = ($_perf_line->_unite_sans_kg) ? $_perf_line->_unite_sans_kg : $_perf_line->unite;
		    $produit->loadConditionnement();
		    // Gestion des unites de prises exprim�es en libelle de presentation (ex: poche ...)		    
		    if($_perf_line->unite == $produit->libelle_presentation){		        
		      $_perf_line->quantite *= $produit->nb_unite_presentation;
		    }
		    // Gestion des unite autres unite de prescription
		    if(!isset($produit->rapport_unite_prise[$unite_prise][$produit->libelle_unite_presentation])) {
          $coef = 1;
        } else {
          $coef = $produit->rapport_unite_prise[$unite_prise][$produit->libelle_unite_presentation];
        }
        
        $_perf_line->_quantite_with_coef = 1;
		    $_perf_line->quantite *= $coef;
		    
		    $_perf_line->_unite_administration = $produit->libelle_unite_presentation;
		    $_perf_line->_unite_dispensation = $produit->libelle_presentation ? $produit->libelle_presentation : $produit->libelle_unite_presentation;
		    $produit->_unite_dispensation = $_perf_line->_unite_dispensation;
		    $produit->_unite_administration = $_perf_line->_unite_administration;
		    
		    if($_perf_line->_unite_dispensation == $produit->libelle_unite_presentation){
		      $_perf_line->_ratio_administration_dispensation = 1;
		    } else {
		      $_perf_line->_ratio_administration_dispensation = 1 / $produit->nb_unite_presentation;
		    }
		  }
		  @$_perf_line->_quantite_administration += $_perf_line->quantite; 
	
	    if($_perf_line->_quantite_administration){
	      $_perf_line->_quantite_dispensation = $_perf_line->_quantite_administration * $_perf_line->_ratio_administration_dispensation;
	      if(!isset($dispensations[$_perf_line->code_cip]["quantite_administration"])){
	        $dispensations[$_perf_line->code_cip]["quantite_administration"] = 0;
	      }  
	      if(!isset($dispensations[$_perf_line->code_cip]["quantite_dispensation"])){
	        $dispensations[$_perf_line->code_cip]["quantite_dispensation"] = 0;
	      }     
	      $dispensations[$_perf_line->code_cip]["quantite_administration"] += $_perf_line->_quantite_administration;
	      
		    if(strstr($_perf_line->_quantite_dispensation,'.')){
		      $_perf_line->_quantite_dispensation = ceil($_perf_line->_quantite_dispensation);
		    }
	      $dispensations[$_perf_line->code_cip]["quantite_dispensation"] += $_perf_line->_quantite_dispensation;
	
	      @$besoin_patient[$_perf_line->code_cip][$patient->_id]["patient"] = $patient; 
	      @$besoin_patient[$_perf_line->code_cip][$patient->_id]["quantite_administration"] = $_perf_line->_quantite_administration;
	      @$besoin_patient[$_perf_line->code_cip][$patient->_id]["quantite_dispensation"] = $_perf_line->_quantite_dispensation;
	      
	    }
	    if($_perf_line->_ref_produit->_unite_dispensation){
	      $produits[$_perf_line->code_cip] = $_perf_line->_ref_produit;
	    }
    } 
  }
}

foreach($besoin_patient as $code_cip => &$quantites_by_patient){
  foreach($quantites_by_patient as $patient_id => &$quantites){
	  if(strstr($quantites["quantite_administration"],'.')){
	      $quantites["quantite_administration"] = ceil($quantites["quantite_administration"]);
	  }
    if(strstr($quantites["quantite_dispensation"],'.')){
	    $quantites["quantite_dispensation"] = ceil($quantites["quantite_dispensation"]);
	  }
	}
}

// Calcul du nombre de boites (unites de presentation)
foreach($dispensations as $cip => $unites){
  $product = new CProduct();
  $product->code = $cip;
  $product->category_id = CAppUI::conf('dPmedicament CBcbProduitLivretTherapeutique product_category_id');
  
  if ($product->loadMatchingObject()) {
    $stocks[$cip] = new CProductStockGroup();
    $stocks[$cip]->group_id = $g;
    $stocks[$cip]->product_id = $product->_id;
    $stocks[$cip]->loadMatchingObject();
    
    $delivrances[$cip] = new CProductDelivery();
    $delivrances[$cip]->stock_id = $stocks[$cip]->_id;
    $delivrances[$cip]->service_id = $service_id;
    $delivrances[$cip]->loadRefsFwd();
  }
}


// On arrondit la quantite de "boites"
foreach($dispensations as $code_cip => &$_quantites){
  foreach($_quantites as &$_quantite){
    if(strstr($_quantite,'.')){
      $_quantite = ceil($_quantite);
    }
  }

  // Chargement des dispensation d�j� effectu�e
  $where = array();
  $where['product_delivery.date_dispensation'] = "BETWEEN '$_date_min' AND '$_date_max'"; // entre les deux dates
  $where['product.code'] = "= '$code_cip'"; // avec le bon code CIP et seulement les produits du livret th�rapeutique
  $where['product.category_id'] = '= '.CAppUI::conf('dPmedicament CBcbProduitLivretTherapeutique product_category_id');
  $where['product_delivery.patient_id'] = "IS NULL";
  $where['product_delivery.quantity'] = " > 0";
  // Pour faire le lien entre le produit et la delivrance, on utilise le stock etablissement
  $ljoin = array();
  $ljoin['product_stock_group'] = 'product_delivery.stock_id = product_stock_group.stock_id';
  $ljoin['product'] = 'product_stock_group.product_id = product.product_id';
  
  $deliv = new CProductDelivery();
  $list_done = $deliv->loadList($where, null, null, null, $ljoin);
  $done[$code_cip] = array();
  
  if (count($list_done)) {
    $done[$code_cip][0] = 0;
    foreach ($list_done as $d) {
    	$d->loadRefsBack();
      $done[$code_cip][] = $d;
      $done[$code_cip][0] += $d->quantity;
    }
  }
  if(isset($delivrances[$code_cip])) {
    $delivrances[$code_cip]->quantity = max($_quantites["quantite_dispensation"] - (isset($done[$code_cip][0]) ? $done[$code_cip][0] : 0), 0);
  }
  $stocks_service[$code_cip] = CProductStockService::getFromCode($code_cip, $service_id);
}

// Smarty template
$smarty = new CSmartyDP();
//$smarty->assign('warning', $warning);
//$smarty->assign('patients', $patients);
$smarty->assign("besoin_patient", $besoin_patient);
$smarty->assign('dispensations', $dispensations);
$smarty->assign('delivrances', $delivrances);
$smarty->assign('produits'  , $produits);
$smarty->assign('done'  , $done);
$smarty->assign('stocks_service'  , $stocks_service);

$smarty->assign('service_id', $service_id);

$smarty->assign('mode_nominatif', "0");
$smarty->display('inc_dispensations_list.tpl');

?>