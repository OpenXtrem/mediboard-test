Correspondant = {
  edit: function(correspondant_id, patient_id, callback) {
    var url = new Url('dPpatients', 'ajax_form_correspondant');
    url.addParam('correspondant_id', correspondant_id);
    url.addParam("patient_id", patient_id);
    url.requestModal(500);
    if (!Object.isUndefined(callback)) {
      url.modalObject.observe("afterClose", function(){
        callback();
      });
    }
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, { 
      onComplete: function() {
        Control.Modal.close();
      }
    });
  },

  confirmDeletion: function(form) {
    var options = {
      typeName:'correspondant', 
      objName: $V(form.nom),
      ajax: 1
    };
    
    var ajax = {
      onComplete: function() {
        Control.Modal.close();
      }
    };
    
    confirmDeletion(form, options, ajax);
  },
  
  refreshList: function(patient_id) {
    var url = new Url('dPpatients', 'ajax_list_correspondants');
    url.addParam("patient_id", patient_id);
    url.requestUpdate('list-correspondants');
    
    var form = getForm('editFrm');
    if (form && window.refreshInfoTutelle) {
      refreshInfoTutelle($V(form.tutelle));
    }
  }
};