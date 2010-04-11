IdentitoVigilance = {
  updater : null,
  guesses: [],

  init: function(frequency) {
	  var url = new Url("dPurgences", "ajax_identito_vigilance");
	  IdentitoVigilance.updater = url.periodicalUpdate('identito_vigilance', { frequency: frequency } );
  },
	
  start: function(delay, frequency) {
    this.stop()
    this.init.delay(delay, frequency);
  },

  stop: function() {
    if (this.updater) {
      this.updater.stop();
    }
  },

  resume: function() {
    if (this.updater) {
      this.updater.resume();
    }
  },

	highlite: function(checkbox) {
	  var checked = checkbox.checked;
	  IdentitoVigilance[checked ? "stop" : "resume"]();
	  
	  // Hide and empty all inputs
	  $$("input[type=radio]").each(function(e) {
	    e.checked = false;
	    e.setVisibility(false);
	  } );
	
	  // Uncheck and unselect all other inputs
	  $$("input[type=checkbox]").each(function(e) {
	    e.checked = false;
	    e.setVisibility(!checked);
	  } )
		
		// Recheck checkbox
	  checkbox.setVisibility(true);
	  if (checked) {
	    checkbox.checked = true;
	  }
	
	  // Show all possible radios
	  var object_class = checkbox.name.split('-')[0];
	  var tbody = $(checkbox).up('tbody');
	  var inputFirst  = "input[name="+object_class+"-first]";
	  var inputSecond = "input[name="+object_class+"-second]";
	
	  // Remove highligths
	  $$(".merge-selected").each(function (e) {
	    e.removeClassName("merge-selected")
	  } )
	  $$(".merge-possible").each(function (e) {
	    e.removeClassName("merge-possible")
	  } )
	  $$(".merge-probable").each(function (e) {
	    e.removeClassName("merge-probable")
	  } )
	
	  if (checked) {
	    if (object_class == "CPatient") {
	      $$(inputSecond).each(function (e) {
	        e.setVisibility(!e.descendantOf(tbody));
	      } );
	      
	      var object_id = checkbox.value;
	      
	      // Show phoning guesses
	      this.guesses[object_id]['phonings'].each( function(phoning_id) {
	        var phoning_guid = object_class+'-'+phoning_id;
	        var div_guessed = $(phoning_guid);
	        if (div_guessed) {
	          div_guessed.addClassName("merge-possible");
	        }
	      } )
	
	      // Show sibling guesses
	      this.guesses[object_id]['siblings'].each( function(sibling_id) {
	        var sibling_guid = object_class+'-'+sibling_id;
	        var div_guessed = $(sibling_guid);
	        if (div_guessed) {
	          div_guessed.addClassName("merge-probable");
	        }
	      } )
	
	    }
	    else {
	      var container = $(checkbox).up();
	      $$(inputSecond).each(function (e) {
	        e.setVisibility(e.descendantOf(tbody) && !e.descendantOf(container));
	      } );
	    }
	  }
	  
	  if (checkbox.checked) {
	    tbody.addClassName("merge-selected");
	  }
	},

	merge: function(radio) {
	  var object_class = radio.name.split('-')[0];
	  var first_id  = $V(document.Merger[object_class+"-first"])[0];
	  var second_id = radio.value;
	  url = new Url("system", "object_merger") .
	    addParam("objects_class", object_class) .
	    addParam("objects_id", [first_id, second_id].join('-'));
	  url.popup(900, 700);
	}	
}
