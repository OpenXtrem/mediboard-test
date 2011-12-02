/* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPprescription
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */
 
Prescription = {
	hide_header: false,
	
	init: function(options){
    Object.extend(Prescription, options);
  },
	
	// Multiples occurences de la m�me widget
  suffixes: [],
  addEquivalent: function(code, line_id, mode_pharma, mode_protocole){
    var url = new Url("dPprescription", "httpreq_substitute_line");
    url.addParam("code_cip", code);
    url.addParam("line_id", line_id);
    url.addParam("mode_pharma", mode_pharma);
    url.addParam("mode_protocole", mode_protocole);
    url.requestUpdate("systemMsg");
  },
  addLine: function(code) {
    var oForm     = document.forms.addLine;
    var oFormDate = document.forms.selDateLine;
    
    if(oFormDate){
			if(oFormDate.debut && oFormDate.debut.value){
			  $V(oForm.debut, oFormDate.debut.value);  
			}
			if(oFormDate.time_debut && oFormDate.time_debut.value){
			  $V(oForm.time_debut, oFormDate.time_debut.value);
			}
			if(oFormDate.jour_decalage && oFormDate.jour_decalage.value){
        $V(oForm.jour_decalage, oFormDate.jour_decalage.value);
			}
      if(oFormDate.decalage_line && oFormDate.decalage_line.value){
			  $V(oForm.decalage_line, oFormDate.decalage_line.value);
			}
			if(oFormDate.unite_decalage && oFormDate.unite_decalage.value){
			  $V(oForm.unite_decalage, oFormDate.unite_decalage.value);
		  }
			if(oFormDate.operation_id && oFormDate.operation_id.value){
        $V(oForm.operation_id, oFormDate.operation_id.value);
      }
		}
    oForm.code_cip.value = code;
    var mode_pharma = oForm.mode_pharma.value;
    return onSubmitFormAjax(oForm);
  },
  addLineElement: function(element_id, chapitre){
    // Formulaire contenant la categorie courante
    var oForm     = document.forms.addLineElement;
    var oFormDate = document.forms.selDateLine;

		if(oFormDate){
      if(oFormDate.debut && oFormDate.debut.value){
        $V(oForm.debut, oFormDate.debut.value);  
      }
      if(oFormDate.time_debut && oFormDate.time_debut.value){
        $V(oForm.time_debut, oFormDate.time_debut.value);
      }
      if(oFormDate.jour_decalage && oFormDate.jour_decalage.value){
        $V(oForm.jour_decalage, oFormDate.jour_decalage.value);
      }
      if(oFormDate.decalage_line && oFormDate.decalage_line.value){
        $V(oForm.decalage_line, oFormDate.decalage_line.value);
      }
      if(oFormDate.unite_decalage && oFormDate.unite_decalage.value){
        $V(oForm.unite_decalage, oFormDate.unite_decalage.value);
      }
      if(oFormDate.operation_id && oFormDate.operation_id.value){
        $V(oForm.operation_id, oFormDate.operation_id.value);
      }
    }
    if(!chapitre || !Object.isString(chapitre)){
      var chapitre = oForm._chapitre.value;
    }
    oForm.element_prescription_id.value = element_id;
    
    return onSubmitFormAjax(oForm);
    oForm.debut.value = "";
    oForm.duree.value = "";
    oForm.unite_duree.value = "";
  },
  submitPriseElement: function(element_id){
    var oFormElement = document.forms.addLineElement;
    var oForm        = document.forms.addPriseElement;
    oForm.object_id.value = element_id;
    submitFormAjax(oForm, 'systemMsg', { 
      onComplete: function(){
        Prescription.reload(oFormElement.prescription_id.value, element_id, oForm.chapitre.value, null, null, null);
      } 
    });
  },
  submitPriseElementWithoutRefresh: function(element_id){
    var oFormElement = document.forms.addLineElement;
    var oForm        = document.forms.addPriseElement;
    oForm.object_id.value = element_id;
    submitFormAjax(oForm, 'systemMsg');
  },
  addLineElementWithoutRefresh: function(element_id, debut, duree, unite_duree, callback){
    // Formulaire contenant la categorie courante
    var oForm = document.forms.addLineElement;
    if(debut){
      oForm.debut.value = debut;
    }
    if(duree && unite_duree){
      oForm.duree.value = duree;
      oForm.unite_duree.value = unite_duree;
    }
    if(callback){
      oForm.callback.value = callback;
    }
    oForm.element_prescription_id.value = element_id;
    
    submitFormAjax(oForm, 'systemMsg');
    
    oForm.debut.value = "";
    oForm.duree.value = "";
    oForm.unite_duree.value = "";
  },
  delLineWithoutRefresh: function(line_id) {
    var oForm = document.forms.addLine;
    oForm.prescription_line_medicament_id.value = line_id;
    oForm.del.value = 1;
    submitFormAjax(oForm, 'systemMsg');
  },
  delLine: function(line_id, chapitre) {
	  chapitre = chapitre || 'medicament';
    var oForm = document.addLine;
    oForm.prescription_line_medicament_id.value = line_id;
    oForm.del.value = 1;
    var mode_pharma = oForm.mode_pharma.value;
    return onSubmitFormAjax(oForm, { 
      onComplete : function(){ 
        Prescription.reload(oForm.prescription_id.value, '', chapitre,'',mode_pharma);
       } 
    });
  },
  delLineElement: function(line_id, chapitre) {
    var oForm = document.addLineElement;
    oForm.prescription_line_element_id.value = line_id;
    oForm.del.value = 1;
    submitFormAjax(oForm, 'systemMsg', { 
      onComplete : function(){ 
        Prescription.reload(oForm.prescription_id.value, null, chapitre, null, null, null);
      } 
    });
  },
  stopTraitementPerso: function(prescription_id, mode_pharma) {
    var url = new Url("dPprescription", "httpreq_prescription_modif_all_tp");
    url.addParam("prescription_id", prescription_id);
    url.addParam("actionType", "stop");
    if(document.selDateLine){
      url.addParam("date", $V(document.selDateLine.debut));
      url.addParam("time_debut", $V(document.selDateLine.time_debut));
    }
    url.addParam("mode_pharma", mode_pharma);
    url.requestUpdate("systemMsg");
  },
  goTraitementPerso: function(prescription_id, mode_pharma) {
    var url = new Url("dPprescription", "httpreq_prescription_modif_all_tp");
    url.addParam("prescription_id", prescription_id);
    url.addParam("actionType", "go");
    if(document.selDateLine){
      url.addParam("date", $V(document.selDateLine.debut));
      url.addParam("time_debut", $V(document.selDateLine.time_debut));
    }
    if(document.forms.selPraticienLine) {
      url.addParam("praticien_id", $V(document.forms.selPraticienLine.praticien_id));
    }
    url.addParam("mode_pharma", mode_pharma);
    url.requestUpdate("systemMsg");
  },
  reload: function(prescription_id, element_id, chapitre, mode_protocole, mode_pharma, line_id, full_line_guid, hide_old_lines, advanced_prot) {
      var oForm = document.addLine;    

      // Rechargement de la modale de protocole avanc�
      if (advanced_prot) {
        window.save_checkboxes = {};
        var checkboxes = getForm("selLines").select("input[type=checkbox]");
        window.save_checkboxes.ids = checkboxes.pluck("id");
        window.save_checkboxes.checked = checkboxes.pluck("checked");
        window.selectLines.modalObject.container.down(".change").click();
        return;
      }
      
			try {
        window.opener.PrescriptionEditor.refresh(oForm.object_id.value, oForm.object_class.value);
      } catch (e){ }
      var urlPrescription = new Url("dPprescription", "httpreq_vw_prescription");
      urlPrescription.addParam("prescription_id", prescription_id);
      urlPrescription.addParam("element_id", element_id);
      urlPrescription.addParam("chapitre", chapitre);
      urlPrescription.addParam("mode_protocole", mode_protocole);
			urlPrescription.addParam("mode_pharma", mode_pharma);
			urlPrescription.addParam("hide_header", Prescription.hide_header ? 1 : 0);  
			if (hide_old_lines) {
	  	  urlPrescription.addParam("hide_old_lines", hide_old_lines);
	    }
			
      if(mode_pharma == "1"){
          urlPrescription.requestUpdate("div_medicament", { onComplete: function(){ Prescription.testPharma(line_id) } });      
      } else {
	      if(mode_protocole == "1"){
	        urlPrescription.requestUpdate("vw_protocole");
	      } else {
		 	    if(chapitre){
	          if (window[chapitre+'Loaded'] || chapitre == "medicament") {
	            urlPrescription.requestUpdate("div_"+chapitre, { onComplete: function(){
							  if(window.viewListPrescription){
						      viewListPrescription();
						    }
							}} );
	          } else {
	            urlPrescription.requestUpdate("div_"+chapitre);
	          }
	        } else {
	         // Dans le cas de la selection d'un protocole, rafraichissement de toute la prescription
	         urlPrescription.requestUpdate("produits_elements");
	        }
	      }
      }
  },
  testPharma: function(line_id){  
    if(line_id){
	    var oFormAccordPraticien = document.forms["editLineAccordPraticien-"+line_id];
	    if(oFormAccordPraticien.accord_praticien.value == 0){
	      if(confirm("Modifiez vous cette ligne en accord avec le praticien ?")){
	        oFormAccordPraticien.__accord_praticien.checked = true;
	        $V(oFormAccordPraticien.accord_praticien,"1");
	      }
	    }
    }
  },
  reloadPrescSejour: function(prescription_id, sejour_id, praticien_sortie_id, mode_anesth, operation_id, chir_id, anesth_id, full_line_guid, pratSel_id, mode_sejour, praticien_for_prot_id){
    if(!$('prescription_sejour')){
			return;
		}
    
    if(!mode_sejour){
      if(document.mode_affichage){
        mode_sejour = document.mode_affichage.mode_sejour.value;
      }
    }

    // Permet de garder le praticien selectionn� pour l'ajout de ligne et l'application de protocoles
    if(!praticien_for_prot_id){
      if(document.forms.selPraticienLine){
        praticien_for_prot_id = document.forms.selPraticienLine.praticien_id.value;
      }
    }
		
    var url = new Url("dPprescription", "httpreq_vw_prescription");
    url.addParam("prescription_id", prescription_id);
    url.addParam("sejour_id", sejour_id);
    
    if (window.DMI_operation_id) {
      url.addParam("operation_id", window.DMI_operation_id);
    }
		url.addParam("hide_header", Prescription.hide_header ? 1 : 0);	
		url.addParam("chir_id", chir_id);
    url.addParam("anesth_id", anesth_id);
    url.addParam("full_mode", "1");
    url.addParam("praticien_sortie_id", praticien_sortie_id);
    url.addParam("mode_anesth", mode_anesth);
    url.addParam("mode_protocole", "0");
    url.addParam("mode_sejour", mode_sejour);
    url.addParam("pratSel_id", pratSel_id);
    url.addParam("praticien_for_prot_id", praticien_for_prot_id);
    url.requestUpdate("prescription_sejour", { onComplete: function(){
		  if(window.viewListPrescription){
				viewListPrescription();
			}
		} } );
  },
	reloadLine: function(line_guid, mode_protocole, mode_pharma, operation_id, mode_substitution, advanced_prot){
		if (window.modalPrescription) {
			modalPrescription.close();
	  }
		
    $('modalPrescriptionLine').update('');
		
		modalPrescription = modal($('modalPrescriptionLine')/*, {
			afterClose: function() {
				// trigger ex_classes
				ExObject.trigger(line_guid, "fermeture");
			}
		}*/);
		
		var url = new Url("dPprescription", "httpreq_vw_line");
		url.addParam("line_guid", line_guid);
		url.addParam("mode_protocole", mode_protocole);
		url.addParam("mode_pharma", mode_pharma);
		url.addParam("advanced_prot", advanced_prot);
		if (window.DMI_operation_id) {
	  	url.addParam("operation_id", window.DMI_operation_id);
	  }
		url.addParam("mode_substitution", mode_substitution);
		url.requestUpdate("modalPrescriptionLine", { onComplete: function(){ modalPrescription.position(); } });
	},
  reloadPrescPharma: function(prescription_id){
    var url = new Url("dPprescription", "httpreq_vw_prescription");
    url.addParam("prescription_id", prescription_id);
    url.addParam("mode_pharma", "1");
    url.addParam("refresh_pharma", "1");
    url.addParam("mode_protocole", "0");
    url.requestUpdate("prescription_pharma");
  },
  reloadPrescPerf: function(prescription_id, mode_protocole, mode_pharma){
	  Prescription.reload(prescription_id,'','medicament',mode_protocole,mode_pharma);
  },
  reloadAddProt: function(protocole_id) {
    Prescription.reload(protocole_id, '','', '1','0');
    Protocole.refreshList(protocole_id);
  },
  reloadDelProt: function(){
    Prescription.reload('','','','1','0');
  },
  reloadAlertes: function(prescription_id) {
    if(prescription_id){
      var urlAlertes = new Url("dPprescription", "httpreq_alertes_icons");
      urlAlertes.addParam("prescription_id", prescription_id);
      urlAlertes.requestUpdate("alertes");
    } else {
      alert('Pas de prescription en cours');
    }
  },
  printPrescription: function(prescription_id, print, object_id, no_pdf, dci, globale, in_progress) {
    // Select de choix du praticien
    var praticien_sortie_id = "";
    if(document.forms.selPraticienLine && (globale == undefined || globale == 0)){
      praticien_sortie_id = document.forms.selPraticienLine.praticien_id.value;
    }
    if (globale == undefined) {
      globale = 0;
    }
    if (in_progress == undefined) {
      in_progress = 0;
    }
    if(prescription_id){
      var url = new Url("dPprescription", "print_prescription");
      url.addParam("prescription_id", prescription_id);
      if(!object_id && !no_pdf) {
	  	  url.addParam("suppressHeaders", 1);
	    }
      url.addParam("globale", globale);
      url.addParam("dci", dci);
      url.addParam("in_progress", in_progress);
      url.addParam("praticien_sortie_id", praticien_sortie_id);
      url.addParam("print", print);
			url.addParam("no_pdf", no_pdf);
      url.popup(800, 600, "print_prescription");
    }
  },
  viewFullAlertes: function(prescription_id) {
    var url = new Url("dPprescription", "vw_full_alertes");
    url.addParam("prescription_id", prescription_id);
    url.modal();
  },
  onSubmitCommentaire: function(oForm, prescription_id, chapitre){
    return onSubmitFormAjax(oForm, { 
      onComplete: function() { 
        Prescription.reload(prescription_id, null, chapitre)
      } 
    } );
  },
  refreshTabHeader: function(tabName, lineCount, lineCountNonSignee){
    // On cible le bon a href
    var link = $('prescription_tab_group').select("a[href=#"+tabName+"]")[0];

    lineCountNonSignee > 0 ? link.addClassName("wrong") : link.removeClassName("wrong");
		lineCount == 0 ? link.addClassName("empty") : link.removeClassName("empty");
		
    link.select('span')[0].innerHTML = lineCount > 0 ? " ("+lineCount+")" : "";
  },
  viewAllergies: function(prescription_id){
    var url = new Url("dPprescription", "httpreq_vw_allergies_sejour");
    url.addParam("prescription_id", prescription_id);
    url.popup(500, 300, "Allergies");
  },
  viewProduit: function(code_cip, code_ucd, code_cis, fragment){
    var url = new Url("dPmedicament", "vw_produit");
    url.addParam("code_cip", code_cip);
    url.addParam("code_ucd", code_ucd);
    url.addParam("code_cis", code_cis);
    url.setFragment(fragment);
    url.popup(900, 640, "Descriptif produit");
  },
  viewHistorique: function(prescription_id, type){
	  var url = new Url("dPprescription", "view_historique");
	  url.addParam("prescription_id", prescription_id);
	  url.addParam("type", type);
	  url.popup(500, 400, type);
  },
  popup: function(prescription_id, type){
    switch (type) {
      case 'printPrescription':
        Prescription.printPrescription(prescription_id);
        break;
      case 'printOrdonnance':
        Prescription.printPrescription(prescription_id,'ordonnance');
        break;
      case 'viewAlertes':
        Prescription.viewFullAlertes(prescription_id)
        break;
      case 'viewHistorique':
        Prescription.viewHistorique(prescription_id, 'historique');
        break;
      case 'viewSubstitutions':
        Prescription.viewHistorique(prescription_id,'substitutions');
        break;
    }
  },
	popupLabo : function(sejour_id){
    var url = new Url("dPImeds", "httpreq_vw_sejour_results");
    url.addParam("sejour_id", sejour_id);
    url.popup(800,800,"R�sultats Labo");
	},
  viewSubstitutionLines: function(object_id, object_class, mode_pack){
    var url = new Url("dPprescription", "httpreq_add_substitution_line");
    url.addParam("object_id", object_id);
    url.addParam("object_class", object_class);
    url.addParam("mode_pack", mode_pack);
    url.popup(900,600, "Lignes de substitution");
  },
  valideAllLines: function(prescription_id, annulation, praticien_id){
    var url = new Url("dPprescription", "vw_signature_prescription");
    url.addParam("prescription_id", prescription_id);
    url.addParam("annulation", annulation);
    url.addParam("praticien_id", $V(document.forms.selPraticienLine.praticien_id));
    url.popup(400,400,"Signatures des lignes de prescription");
  },
  viewStatPoso: function(object_class, filter, praticien_id){
    var url = new Url("dPprescription", "vw_stat_posologie");
    url.addParam("filter", filter);
    url.addParam("praticien_id", praticien_id);
		url.addParam("object_class", object_class);
    url.popup(800,400, "statistiques d'utilisation des posologies");
  },
	updatePerop: function(sejour_id){
		var url = new Url("dPprescription", "httpreq_vw_perop");
		url.addParam("sejour_id", sejour_id);
		url.addParam("operation_id", window.DMI_operation_id);
		url.requestUpdate("perop");
	},
	updateDebit: function(line_id) {
		var oForm = getForm("editPerf-"+line_id);
    var volume = $V(oForm.volume_debit);
    var duree = $V(oForm.duree_debit);

		if(volume == 0 || duree == 0){
			return;
		}
		
		var debit = (volume / duree).toFixed(2);
	  if (isNaN(debit)) {
      debit = "-";
    } 
    $("debitLineMix-"+line_id).update(debit);
  },
  confirmDelLine: function(view) {
    if (confirm("Voulez-vous vraiment supprimer la ligne : " + view + " ?"))
      return true;
  },
  showLineHistory: function(line_guid){
    var url = new Url("dPprescription", "vw_line_history");
    url.addParam("line_guid", line_guid);
    url.popup(800, 600, "Historique de la ligne");
  },
  mergePrescriptions: function(prescriptions_ids, prescription_base_id) {
    window.selectLines = new Url("dPprescription", "ajax_merge_prescriptions");
    window.selectLines.addParam("prescriptions_ids[]", prescriptions_ids);
    window.selectLines.requestModal(1000,700);
  },
	showFavoris: function(praticien_id, chapitre, prescription_id, mode_protocole, mode_pharma){
	  if(document.forms.selPraticienLine){
			var oFormPraticien = window.document.forms.selPraticienLine;
	    praticien_id = $V(oFormPraticien.praticien_id)
	  }
		
		var url = new Url("dPprescription", "ajax_vw_favoris_prescription");
		url.addParam("praticien_id", praticien_id);
    url.addParam("chapitre", chapitre);
    url.addParam("prescription_id", prescription_id);
		url.addParam("mode_protocole", mode_protocole);
    url.addParam("mode_pharma", mode_pharma);
    
	  url.requestModal(800,600);
	}
};