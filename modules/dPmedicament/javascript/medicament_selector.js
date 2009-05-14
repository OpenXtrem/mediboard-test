/* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPmedicament
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

var MedSelector = {
  sForm     : null,
  sView     : null,
  sCode     : null,
  sSearch   : null,
  sRechercheLivret : null,
  sOnglet   : null,
  sSearchByCIS : null,
  oUrl      : null,
  selfClose : true,
  options : {
    width : 700,
    height: 400
  },
  prepared : {
    code: null,
    nom:null
  },

  pop: function() {
    var oForm = document[this.sForm];
    this.oUrl = new Url();
    if(this.sSearch) {
      this.oUrl.addParam(this.sOnglet, this.sSearch);
    }
    this.oUrl.addParam("onglet_recherche", this.sOnglet);
    this.oUrl.addParam("_recherche_livret", this.sRechercheLivret);
    if(this.sSearchByCIS){
      this.oUrl.addParam("search_by_cis", this.sSearchByCIS);
    }
    this.oUrl.setModuleAction("dPmedicament", "vw_idx_recherche");
    
    this.oUrl.popup(this.options.width, this.options.height, "Medicament Selector");
  },
  set: function(nom, code) {
    this.prepared.nom = nom;
    this.prepared.code = code;  
  
    // Lancement de l'execution du set
    window.setTimeout( window.MedSelector.doSet , 1);
  },
  
  doSet: function(){
    var oForm = document[MedSelector.sForm];
    $V(oForm[MedSelector.sView], MedSelector.prepared.nom);
    $V(oForm[MedSelector.sCode], MedSelector.prepared.code);
    
  },
      
  // Peut �tre appel� sans contexte : ne pas utiliser this
  close: function() {
    MedSelector.oUrl.close();
  }
}
