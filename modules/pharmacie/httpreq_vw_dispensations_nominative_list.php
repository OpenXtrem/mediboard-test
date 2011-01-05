<?php /* $Id$ */

/**
 *	@package Mediboard
 *	@subpackage pharmacie
 *	@version $Revision$
 *  @author SARL OpenXtrem
 *  @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CCanDo::checkRead();

$service_id      = CValue::getOrSession('service_id');
$patient_id      = CValue::getOrSession('patient_id');
$prescription_id = CValue::getOrSession('prescription_id');
$_selected_cis   = CValue::get("_selected_cis");

// Calcul de date_max et date_min
$date_min = CValue::get('_date_min');
$date_max = CValue::get('_date_max');

$borne_min = CValue::getOrSession('borne_min');
$borne_max = CValue::getOrSession('borne_max');

if(!$borne_min){
	$borne_min = "00:00:00";
}
if(!$borne_max){
  $borne_max = "23:00:00";
}

$_borne_min = "$date_min $borne_min";
$_borne_max = "$date_max $borne_max";

if (!$date_min) {
  $date_min = CValue::session('_date_delivrance_min');
}
if (!$date_max) {
  $date_max = CValue::session('_date_delivrance_max');
}

CValue::setSession('_date_delivrance_min', $date_min);
CValue::setSession('_date_delivrance_max', $date_max);

$date_min_orig = $date_min;
$date_max_orig = $date_max;

$lines = array();
$_lines = array();

// Creation du tableau de dates
$dates = array();
if ($date_min != $date_max){
	$date = $date_min;
	while($date <= $date_max){
	  $dates[] = $date;
	  $date = mbDate("+ 1 DAY", $date);
	}
} else {
  $dates[] = $date_min; 
}

$date_min .= " 00:00:00";
$date_max .= " 23:59:59";

if($prescription_id == "undefined"){
	$prescription_id = "";
}

$prescription = new CPrescription();
$dispensations = array();
$delivrances = array();
$prescriptions = array();
$stocks = array();
$done = array();
$done_global = array();
$stocks_service = array();
$stocks_pharmacie = array();
$warning = array();
$produits_cip = array();
$produits_cis = array();
$correction_dispensation = array();

$prescription = new CPrescription();
$prescription->load($prescription_id);

if ($prescription->_id){
 
  // Stockage du sejour de la prescription
  $sejour =& $prescription->_ref_object;
  
  // On borne les dates aux dates du sejour si besoin
  $date_min = max($sejour->_entree, $date_min);
  $date_max = min($sejour->_sortie, $date_max);

  // Chargement des lignes
  $prescription->loadRefsLinesMed("1","1");
  $prescription->loadRefsPrescriptionLineMixes();
  
  $lines_med = array();
  
  // Calcul du plan de soin
  if (strlen($_selected_cis) == 8){
  	// CIS
	  $prescription->calculPlanSoin($dates, 0, 0, 1, null, true, $_selected_cis);
	} else {
		// CIP
	  $prescription->calculPlanSoin($dates, 0, 0, 1, $_selected_cis, true);
	}
  
	$lines_med["medicament"] = $prescription->_ref_prescription_lines;
  
	
  // Parcours des prises prevues pour les medicaments
  foreach($lines_med as $lines_by_type){
    foreach($lines_by_type as $_line_med){
      if ($_selected_cis && ($_line_med->code_cis != $_selected_cis) && ($_line_med->code_cip != $_selected_cis)){
        continue;
      }
			
		  $_line_med->loadRefProduitPrescription();
			
	    $code = $_line_med->code_cis ? $_line_med->code_cis : $_line_med->code_cip;
	    $_lines[$code] = $_line_med;
      if ($_line_med->_quantity_by_date){
        if (!isset($produits_cis[$code])){
          $produits_cis[$code] = $_line_med;
        }
        foreach($_line_med->_quantity_by_date as $type => $quantity_by_date){
			  	foreach($quantity_by_date as $date => $quantity_by_hour){
			  	  if (@$quantity_by_hour['quantites']) { //FIXME: parfois cette valeur est vide
				  	  foreach($quantity_by_hour['quantites'] as $hour => $quantity){
								// Filtre en fonction des borne
							  if ("$date $hour:00:00" < $_borne_min || "$date $hour:00:00" > $_borne_max){
                  continue;
                }
			
				  	  	@$dispensations[$code]["quantite_administration"] += $quantity["total"];
								if ($_line_med->_ref_produit_prescription->_id && ($_line_med->_ref_produit_prescription->unite_prise != $_line_med->_ref_produit_prescription->unite_dispensation)){
								  $quantity["total_disp"] = $quantity["total_disp"] / ($_line_med->_ref_produit_prescription->quantite * $_line_med->_ref_produit_prescription->nb_presentation);
								}
								@$dispensations[$code]["quantite_dispensation"] += $quantity["total_disp"];
				  	  }
			  		}
			  	}
        }
      }
			
      // Gestion des prises planifiees
      if($_line_med->_administrations){
	      foreach($_line_med->_administrations as $unite_prise => &$administrations_by_unite){
			    foreach($administrations_by_unite as $_date => &$administrations_by_date){
					  foreach($administrations_by_date as $_hour => &$administrations_by_hour){
						  if (is_numeric($_hour)){
                // Filtre en fonction des borne
                if ("$_date $_hour:00:00" < $_borne_min || "$_date $_hour:00:00" > $_borne_max){
                  continue;
                }
							
	          		$quantite_planifiee = @$administrations_by_hour["quantite_planifiee"];
		            if($quantite_planifiee){
							    // Calcul de la quantite 
							    @$dispensations[$code]["quantite_administration"] += $quantite_planifiee;
									
									if($_line_med->_ref_produit_prescription->_id && ($_line_med->_ref_produit_prescription->unite_prise != $_line_med->_ref_produit_prescription->unite_dispensation)){
                    $quantite_dispensation = $quantite_planifiee / ($_line_med->_ref_produit_prescription->quantite * $_line_med->_ref_produit_prescription->nb_presentation);
                  } else {
							      $quantite_dispensation = $quantite_planifiee * $_line_med->_ratio_administration_dispensation; 
									}
							    @$dispensations[$code]["quantite_dispensation"] += $quantite_dispensation;
		            }
	            }
	          }
	        }
	      }
      }
    }
  }
  // Parcours des prises prevues pour les prescription_line_mixes
  if ($prescription->_ref_prescription_line_mixes_for_plan){
	  foreach($prescription->_ref_prescription_line_mixes_for_plan as $_prescription_line_mix){
	  	if (is_array($_prescription_line_mix->_prises_prevues)){
		    foreach($_prescription_line_mix->_prises_prevues as $_date => $_prises_by_hour){
		      foreach($_prises_by_hour as $_hour => $_prise){
		        foreach($_prescription_line_mix->_ref_lines as $_perf_line){
							$count_prises_by_hour = count($_prescription_line_mix->_prises_prevues[$_date][$_hour]["real_hour"]);
		        	$_perf_line->loadRefProduitPrescription();
	            $_perf_line->loadRefProduit();
		          $datetime = "$_date $_hour:00:00";
							$code = $_perf_line->code_cis ? $_perf_line->code_cis : $_perf_line->code_cip;
							$_lines[$code] = $_perf_line;
							
		          if ($datetime < $date_min || $datetime > $date_max){
		            continue;
		          }
							
							if ($datetime < $_borne_min || $datetime > $_borne_max){
                continue;
              }

		          if (!isset($produits_cis[$code])){
		           $produits_cis[$code] = $_perf_line;
		          }      
		          
		          if(!isset($dispensations[$code])){
		            $dispensations[$code]["quantite_administration"] = 0;
		            $dispensations[$code]["quantite_dispensation"] = 0;
		          }
		          $dispensations[$code]["quantite_administration"] += ($_perf_line->_quantite_administration * $count_prises_by_hour);
							
					    if ($_perf_line->_ref_produit_prescription->_id && ($_perf_line->_ref_produit_prescription->unite_prise != $_perf_line->_ref_produit_prescription->unite_dispensation)){
	              $quantite_dispensation = $_perf_line->_quantite_dispensation / ($_perf_line->_ref_produit_prescription->quantite * $_perf_line->_ref_produit_prescription->nb_presentation);
	            } else {
	              $quantite_dispensation = $_perf_line->_quantite_dispensation;
	            }
		          $dispensations[$code]["quantite_dispensation"] += ($quantite_dispensation * $count_prises_by_hour);
		        }
		      }
		    }
      }
	  }
  }
}


// Chargement des dispensations prevues
$_produits_from_cis = array();
foreach($dispensations as $code => $quantites){
  if ($dispensations[$code]["quantite_administration"] == 0) {
    unset($dispensations[$code]); continue;
  }

  // Si le code est bien un code CIS
  if (strlen($code) == '8'){
    $_produits_from_cis[$code] = CBcbProduit::getProduitsFromCISInLivret($code); 
  } else {
  	$produit = new CBcbProduit();
		$produit->load($code);
  	$_produits_from_cis[$code] = array(array("CODE_CIP" => $code, "LIBELLE_PRODUIT" => $produit->libelle));
  }
	
  foreach($_produits_from_cis[$code] as $_produit){
		$_code_cip = $_produit["CODE_CIP"];
    $produits_cip[$_code_cip] = $_produit;
    
	  $product = new CProduct();
	  $product->code = $_code_cip;
	  $product->category_id = CAppUI::conf('bcb CBcbProduitLivretTherapeutique product_category_id');
	  
	  if ($product->loadMatchingObject()) {
	    // Chargement du stock
      $product->loadRefStock();
      $stock = $product->_ref_stock_group;

	    // Chargement des ref de la dispensation � effectuer
	    $delivery = new CProductDelivery();
      $delivery->stock_class = $stock->_class_name;
      $delivery->stock_id = $stock->_id;
	    $delivery->service_id = $service_id;
	    $delivery->loadRefsFwd();
	    $delivery->_ref_stock->loadRefsFwd();
	    $delivery->_ref_stock->_ref_product->updateFormFields();
	    $delivrances[$code][$_code_cip] = $delivery;
	    
	    // Calcul du stock de la pharmacie
	  	$stocks_pharmacie[$code][$_code_cip] = $delivery->_ref_stock->quantity;
		  if(!isset($stocks_pharmacie[$code]["total"])){
		    $stocks_pharmacie[$code]["total"] = 0;
		  }
	    $stocks_pharmacie[$code]["total"] += $stocks_pharmacie[$code][$_code_cip];
	  }
  }
}



// Calcul des delivrances deja effectu�es pour un CIS donn� (les delivrances se font en fonction des cip)
foreach($dispensations as $code_cis => $_quantites){
  foreach($_quantites as $type => $_quantite){
		if ($type == "quantite_dispensation" && strstr($_quantite,'.')){
			$dispensations[$code_cis]["quantite_dispensation"] = ceil($_quantite);
    }
	}
	
	// Si le code est bien un code CIS
  if (strlen($code) == '8'){
    $_produits = $_produits_from_cis[$code_cis];
  } else {
    $produit = new CBcbProduit();
    $produit->load($code_cis);
    $_produits = array(array("CODE_CIP" => $code_cis, "LIBELLE_PRODUIT" => $produit->libelle));
  }
    
  foreach($_produits as $_produit){
    $code_cip = $_produit["CODE_CIP"];
    
	  // Chargement des dispensation d�j� effectu�e
	  $where = array();
	  $where['product_delivery.date_dispensation'] = "BETWEEN '$date_min_orig 00:00:00' AND '$date_max_orig 23:59:59'";
	  $where['product.code'] = "= '$code_cip'";
	  $where['product.category_id'] = '= '.CAppUI::conf('bcb CBcbProduitLivretTherapeutique product_category_id');
	  $where['product_delivery.patient_id'] = "= '$patient_id'";
	  $where['product_delivery.quantity'] = " > 0";
    $where['product_delivery.stock_class'] = "= 'CProductStockGroup'";
    
	  // Pour faire le lien entre le produit et la delivrance, on utilise le stock etablissement
	  $ljoin = array();
	  $ljoin['product_stock_group'] = 'product_delivery.stock_id = product_stock_group.stock_id';
	  $ljoin['product'] = 'product_stock_group.product_id = product.product_id';
	  
	  $deliv = new CProductDelivery();
	  $list_done = $deliv->loadList($where, null, null, null, $ljoin);
	
	  $done[$code_cis][$code_cip] = array();
	  $done_global[$code_cis][$code_cip] = array();
	  
	  if (count($list_done)) {
	    if (!isset($done[$code_cis]["total"])){
	      $done[$code_cis]["total"] = 0;
	    }
	 	  foreach ($list_done as $_disp) {
	 	  	$_disp->loadRefsBack();
	 	    $done[$code_cis][$code_cip][] = $_disp;
	 	    $done[$code_cis]["total"] += $_disp->quantity;
	 	  }
	  }
	  
	  $where['product_delivery.patient_id'] = "IS NULL";
	  $list_done_global = $deliv->loadList($where, null, null, null, $ljoin);
	  
		if (count($list_done_global)) {
		  if (!isset($done_global[$code_cis]["total"])){
		    $done_global[$code_cis]["total"] = 0;
		  }
	    $done_global[$code_cis]["total"] = 0;
	 	  foreach ($list_done_global as $d_glob) {
	 	  	$d_glob->loadRefsBack();
	 	    $done_global[$code_cis][$code_cip][] = $d_glob;
	 	    $done_global[$code_cis]["total"] += $d_glob->quantity;
	 	  }
	  }
	 
	  // Calcul du stock des services
	  $stocks_service[$code_cis][$code_cip] = CProductStockService::getFromCode($code_cip, $service_id);
	  if(!isset($stocks_service[$code_cis]["total"])){
	    $stocks_service[$code_cis]["total"] = 0;
	  }
	  $stocks_service[$code_cis]["total"] += $stocks_service[$code_cis][$code_cip]->quantity;
  }
}


// Ajustement de la valeur propos� dans la dispensation en fonction de celles deja effectu�es
foreach($done as $_done_cis => $_done) {
  if (isset($delivrances[$_done_cis])){
	  foreach($delivrances[$_done_cis] as $_delivery_cip => $_delivery){
	    $_quantites = $dispensations[$_done_cis];
	    $_delivery->quantity = max(($_quantites["quantite_dispensation"] - (isset($_done["total"]) ? $_done["total"] : 0)), 0);
	  }
  }
}

// Patch unite de dispensation
foreach($delivrances as $code_cis => $_delivrance){
  $produits = CBcbProduit::getProduitsFromCIS($code_cis);
  foreach($produits as $_produit_bcb){
    if (!array_key_exists($_produit_bcb["CODE_CIP"], $delivrances[$code_cis])){
      continue;
    }
    $produit = new CBcbProduit();
    $produit->load($_produit_bcb["CODE_CIP"]);
    $produit->loadConditionnement();
    $produit->loadLibellePresentation();
    $produit->_unite_administration = $produit->libelle_unite_presentation;
		
    $produit->_unite_dispensation = $produit->libelle_presentation ? $produit->libelle_presentation : $produit->libelle_unite_presentation;
    if ($produit->_unite_dispensation == $produit->libelle_unite_presentation){
      $ratio = 1;
    } else {
      $ratio = 1 / $produit->nb_unite_presentation;
    }
   
    // Calcul de la quantite 
	  $administration = $dispensations[$code_cis]["quantite_administration"];
	  $quantite_dispensation = $ratio*$administration;
    if(strstr($quantite_dispensation,'.')){
      $quantite_dispensation = ceil($quantite_dispensation);
    }
    
	  $correction_dispensation[$code_cis][$produit->code_cip]["produit"] = $produit->libelle;
	  $correction_dispensation[$code_cis][$produit->code_cip]["dispensation"] = $quantite_dispensation;
	  
	  $correction_dispensation[$code_cis]["nb"]["$quantite_dispensation"] = $quantite_dispensation;
  }
}

foreach($correction_dispensation as $code_cis => $_correction){
  if (count($correction_dispensation[$code_cis]["nb"]) ==  1){
    unset($correction_dispensation[$code_cis]);
    continue;
  }  
  foreach($delivrances[$code_cis] as $code_cip => $_delivery){
    $_delivery->quantity = $correction_dispensation[$code_cis][$code_cip]["dispensation"];
  }
}

$prescription->_ref_object->loadRefPatient();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("correction_dispensation", $correction_dispensation);
$smarty->assign("produits_cis"       , $produits_cis);
$smarty->assign("produits_cip"       , $produits_cip);
$smarty->assign("_lines"              , $_lines);
$smarty->assign('delivrances'        , $delivrances);
$smarty->assign('done'               , $done);
$smarty->assign('done_global'        , $done_global);
$smarty->assign('stocks_service'     , $stocks_service);
$smarty->assign('stocks_pharmacie'   , $stocks_pharmacie);
$smarty->assign('service_id'         , $service_id);
$smarty->assign('prescription'       , $prescription);
$smarty->assign('mode_nominatif'     , "1");
$smarty->assign("date_min", $date_min_orig);
$smarty->assign("date_max", $date_max_orig);
$smarty->assign("now", mbDate());
$smarty->assign("borne_min", $borne_min);
$smarty->assign("borne_max", $borne_max);

if ($_selected_cis){
  $smarty->assign("quantites", $dispensations[$_selected_cis]);
  $smarty->assign("code_cis", $_selected_cis);
  $smarty->assign("nodebug", true);
  $smarty->display('inc_dispensation_line.tpl');
} else {
  $smarty->assign('dispensations'      , $dispensations);
  $smarty->display('inc_dispensations_list.tpl');
}

?>