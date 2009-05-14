/* $Id$ */

/**
 * @package Mediboard
 * @subpackage includes
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

function main() {
  try {
  	//Session.lock();
    prepareForms();
    SystemMessage.init();
    WaitingMessage.init();
    initPuces();
    Main.init();
  }
  catch (e) {
    Console.debugException(e);
  }
}

document.observe('dom:loaded', main);

/**
 * Main page initialisation scripts
 */
var Main = {
  scripts: [],
  initialized: false,
  
  /**
   * Add a script to be lanuched after onload notification
   * On the fly execution if already page already loaded
   */
  add: function(script) {
    if (this.initialized) {
      script();
    }
    else {
      this.scripts.push(script);
    }
  },
  
  /**
   * Call all Main functions
   */
  init: function() {
    this.scripts.each(function(e) { e() } );
    this.initialized = true;
  }
};

/** To prevent flickering, the body is hidden 
 * and showed only after every Main.add is finished.
 * Doing so, the page is also displayed faster */
/*function showBody() {
  $('body').show();
}
Main.add(function() { showBody.defer(); });*/

/**
 * References manipulation
 */
var References = {
  /**
   * Clean references involved in memory leaks
   */
  clean: function(obj) {
    var elements = obj.descendants();
    for (var j = 0; j < elements.length; j++) {
      var e = elements[j];
      if (e) {
        if (e.attributes) {
          for (var i = 0; i < e.attributes.length ; i++) {
            if (Object.isFunction(e.attributes[i])) e.attributes[i] = null;
          }
        }
        Element.remove(e);
      }
    }
  }
}

var WaitingMessage = {
  init: function() {
    window.onbeforeunload = function () {
      if(FormObserver.checkChanges()) {
        WaitingMessage.show();
      } else {
        return "Vous avez modifi� certaines informations sur cette page sans les sauvegarder. Si vous appuyez sur OK, ces donn�es seront perdues.";
      }
    };
  },
  
  show: function() {
    var eDoc  = document.documentElement;
    var eMask = $('waitingMsgMask');
    var eText = $('waitingMsgText');
    if (!eMask && !eText) {
      return;
    }
  
    // Display waiting text
    var vpd = document.viewport.getDimensions();
    var etd = eText.getDimensions();
    eText.setStyle({
      top: (vpd.height - etd.height)/2 + "px",
      left: (vpd.width  - etd.width) /2 + "px",
      zIndex: 101,
      opacity: 0.8
    }).show();
    
    // Display waiting mask
    eMask.setStyle({
      top: "0",
      left: "0",
      height: eDoc.scrollHeight + "px",
      width: eDoc.scrollWidth + "px",
      zIndex: 100,
      opacity: 0.2
    }).show();
  },
  
  cover: function(element) {    
    element = $(element);
    
    var eDiv = new Element("div", {className: 'ajax-loading'}).hide(),
		    descendant = $(element).down();
    
    /** If the element is a TR, we add the div to the firstChild to avoid a bad page render (a div in a <table> or a <tr>)*/
    if (descendant && descendant.tagName.match(/^tr$/i)) {
			descendant.insert({bottom: eDiv});
		}
	  else {
	    element.insert({bottom: eDiv});
	  }
		
		eDiv.setStyle({
      opacity: 0.3,
      position: 'absolute'
    }).clonePosition(element).show();
  }
}

function createDocument(oSelect, consultation_id) {
  if (modele_id = oSelect.value) {
    var url = new Url;
    url.setModuleAction("dPcompteRendu", "edit_compte_rendu");
    url.addParam("modele_id", modele_id);
    url.addParam("object_id", consultation_id);
    url.popup(700, 700, "Document");
  }
  
  oSelect.value = "";
}

function closeWindowByEscape(e) {
  if(getKeycode(e) == 27){
  	e.stop();
    window.close();
  }
}

var AjaxResponse = {
  onDisconnected: function() {
    if (window.children['login'] && window.children['login'].closed) window.children['login'] = null;

    if (!window.children['login']) {
      loginUrl = new Url;
      loginUrl.addParam("dialog", 1);
      loginUrl.pop(610, 300, "login");
    }
  },
  
  onPerformances: Prototype.emptyFunction
}


/**
 * System message effects
 */
var SystemMessage = {
  id: "systemMsg",
  autohide: null,
  effect: null,

  // Check message type (loading, notice, warning, error) from given div
  checkType: function() {
    this.autohide = $A($(this.id).childNodes).pluck("className").compact().last() == "message";
  },

  // show/hide the div
  doEffect : function (delay, forceFade) {
    var oDiv = $(this.id);
    
    // Cancel current effect
    if (this.effect) {
      this.effect.cancel();
      this.effect = null;
    }
      
    // Ensure visible        
    oDiv.show().setOpacity(1);
    
    // Only hide on type 'message'
    this.checkType();
    if (!this.autohide && !forceFade) {
      return;
    }
    
    // Program fading
    this.effect = new Effect.Fade(this.id, { delay : delay || 5} );
  },
  
  init : function () {
    var oDiv = $(this.id);
    Assert.that(oDiv, "No system message div");
    
    // Hide on onclick
    Event.observe(oDiv, 'click', function(event) {
      SystemMessage.doEffect(0.1, true);
    } );
        
    // Hide empty message immediately
    if (!oDiv.innerHTML.strip()) {
      SystemMessage.doEffect(0.1, true);
      return;
    }
    
    SystemMessage.doEffect();
  }
}

/**
 * Javascript console
 */
var Console = {
  id: "console",

  hide: function() {
    $(this.id).hide();
  },
  
  trace: function(sContent, sClass, nIndent) {
    sClass = sClass || "label";
    
    if(Preferences.INFOSYSTEM == 1) {
      Element.show(this.id);
    }
    var eDiv = new Element("div", {className: sClass});
    eDiv.innerHTML = sContent.toString().escapeHTML();

    if (nIndent) {
      eDiv.setStyle({ marginLeft: nIndent + "em" });
    }

    $(this.id).insert(eDiv);
    Console.scrollDown.defer();
  },
  
  scrollDown: function() {
    $(Console.id).scrollTop = $(Console.id).scrollHeight;
  },
  
  traceValue: function(oValue) {
    if (oValue === null) {
      this.trace("null", "value");
      return;
    }
    
    switch (typeof oValue) {
      case "undefined": 
        this.trace("undefined", "value");
        break;
      
      case "object":
        if (oValue instanceof Array) {
          this.trace(">> [Array]", "value");
        } else {        
          this.trace(">> " + oValue, "value");
        }
        break;

      case "function":
        this.trace(">> Function" + oValue.getSignature(), "value");
        break;

      case "string":
        this.trace("'" + oValue + "'", "value");
        break;

      default:
        this.trace(oValue, "value");
    }
  },
  
  debugException: function(exception) {
    var regexp = /([^@])+@(http[s]?:([^:]+))?:([\d]+)/g;
    exception.stack = exception.stack.match(regexp);
    this.debug(exception, "Exception", { level: 2 } );
  },
  
  debug: function(oValue, sLabel, oOptions) {
    if (Preferences.INFOSYSTEM != "1") {
      return;
    }
  
    sLabel = sLabel || "Value";

    var oDefault = {
      level: 1,
      current: 0
    }
      
    Object.extend(oDefault, oOptions);
  
    if (oDefault.current > oDefault.level) {
      return;
    }
            
    try {
      this.trace(sLabel + ": ", "key", oDefault.current);
      
      if (oValue === null) {
        this.trace("null", "value");
        return;
      }
      
      switch (typeof oValue) {
        case "undefined": 
          this.trace("undefined", "value");
          break;
        
        case "object":
          oDefault.current++;
          if (oValue instanceof Array) {
            this.trace("[Array]", "value");
            oValue.each(function(value) { 
              Console.debug(value, "", oDefault);
            } );
          } else {
            this.trace(oValue, "value");
            $H(oValue).each(function(pair) {
              Console.debug(pair.value, pair.key, oDefault);
              
            } );
          }
          break;
  
        case "function":
          this.trace("[Function] : " + oValue.getSignature(), "value");
          break;
  
        case "string":
          this.trace("'" + oValue + "'", "value");
          break;
  
        default:
          this.trace(oValue, "value");
      }

    }
    catch(e) {
      this.trace("[couldn't get value]", "error");
    }
  },
  
  debugElement: function(oElement, sLabel, oOptions) {
    sLabel = sLabel || "Element";
    
    var oDefault = {
      level: 1,
      current: 0
    }
      
    Object.extend(oDefault, oOptions);
    
    oElement = $(oElement);
    
    var oNoRecursion = { 
      level: oDefault.current, 
      current: oDefault.current
    };
    
    this.debug(oElement, sLabel, oNoRecursion);

    oDefault.current++;

    if (oDefault.current > oDefault.level) {
      return;
    }
            
    oNoRecursion = { 
      level: oDefault.current, 
      current: oDefault.current
    };

    // Text nodes don't have tagName
    if (oElement.tagName) {
      this.debug(oElement.tagName.toLowerCase(), "tagName",  oNoRecursion);
    }
    
    if (oElement instanceof Text) {
      this.debug(oElement.textContent, "textContent", oNoRecursion);
    }
    
    $A(oElement.attributes).each( function(oAttribute) {
      Console.debug(oAttribute.nodeValue, "Attributes." + oAttribute.nodeName, oDefault);
    } );

    $A(oElement.childNodes).each( function(oElement) {
      Console.debugElement(oElement, "Element", oDefault)
    } );
  },
  
  error: function (sMsg) {
    this.trace("Error: " + sMsg, "error");
  },
  
  start: function() {
    this.dStart = new Date;
  },
  
  stop: function() {
    var dStop = new Date;
    this.debug(dStop - this.dStart, "Duration in milliseconds");
    this.dStart = null;
  }
};

// If there is no console object, it uses the Mediboard Console
window.console = window.console || {
  debug: Console.debug,
  log: Console.trace
};

/**
 * Assert utility object
 */ 
 
var Assert = {

  that: function (bPredicate, sMsg) {
    if (!bPredicate) {
      var aArgs = $A(arguments);
      aArgs.shift();
      sMsg = printf.apply(null, aArgs);
      Console.error(sMsg);
    }
  }
};

/**
 * PairEffect Class
 */

var PairEffect = Class.create({

  // Constructor
  initialize: function(idTarget, oOptions) {
    
    var oDefaultOptions = {
      idTarget       : idTarget,
      idTrigger      : idTarget + "-trigger",
      sEffect        : null, // could be null, "appear", "slide", "blind"
      bStartVisible  : false, // Make it visible at start
      bStoreInCookie : true,
      sCookieName    : "effects"
    };

    Object.extend(oDefaultOptions, oOptions);
    
    this.oOptions = oDefaultOptions;
    var oTarget   = $(this.oOptions.idTarget);
    var oTrigger  = $(this.oOptions.idTrigger);

    Assert.that(oTarget, "Target element '%s' is undefined", idTarget);
    Assert.that(oTrigger, "Trigger element '%s' is undefined ", this.oOptions.idTrigger);
  
    // Initialize the effect
    oTrigger.observe("click", this.flip.bind(this));
  
    // Initialize classnames and adapt visibility
    var aCNs = Element.classNames(oTrigger);
    aCNs.add(this.oOptions.bStartVisible ? "triggerHide" : "triggerShow");
    if (this.oOptions.bStoreInCookie) {
      aCNs.load(this.oOptions.sCookieName);
    }
    oTarget.setVisible(!aCNs.include("triggerShow"));   
  },
  
  // Flipper callback
  flip: function() {
    var oTarget = $(this.oOptions.idTarget);
    var oTrigger = $(this.oOptions.idTrigger);
    if (this.oOptions.sEffect && !Prototype.Browser.IE) {
      new Effect.toggle(oTarget, this.oOptions.sEffect);
    } else {
      oTarget.toggle();
    }
  
    var aCNs = Element.classNames(oTrigger);
    aCNs.flip("triggerShow", "triggerHide");
    
    if (this.oOptions.bStoreInCookie) {
      aCNs.save(this.oOptions.sCookieName);
    }
  }
} );

/**
 * PairEffect utiliy function
 */

Object.extend(PairEffect, {
  declaredEffects : {},

  // Initialize a whole group giving the className for all targets
  initGroup: function(sTargetsClass, oOptions) {
    var oDefaultOptions = {
      idStartVisible   : null, // Forces one element to start visible
      bStartAllVisible : false,
      sCookieName      : sTargetsClass
    }
    
    Object.extend(oDefaultOptions, oOptions);
    
    $$('.'+sTargetsClass).each( 
      function(oElement) {
        oDefaultOptions.bStartVisible = oDefaultOptions.bStartAllVisible || (oElement.id == oDefaultOptions.idStartVisible);
        new PairEffect(oElement.id, oDefaultOptions);
      }
    );
  }
});



/**
 * TogglePairEffect Class
 */

var TogglePairEffect = Class.create({
  // Constructor
  initialize: function(idTarget1, idTarget2, oOptions) {
    
    var oDefaultOptions = {
      idFirstVisible : 1,
      idTarget1      : idTarget1,
      idTarget2      : idTarget2,
      idTrigger1     : idTarget1 + "-trigger",
      idTrigger2     : idTarget2 + "-trigger"
    };

    Object.extend(oDefaultOptions, oOptions);
    
    this.oOptions = oDefaultOptions;
    var oTarget1  = $(this.oOptions.idTarget1);
    var oTarget2  = $(this.oOptions.idTarget2);
    var oTrigger1 = $(this.oOptions.idTrigger1);
    var oTrigger2 = $(this.oOptions.idTrigger2);

    Assert.that(oTarget1, "Target1 element '%s' is undefined", idTarget1);
    Assert.that(oTarget2, "Target2 element '%s' is undefined", idTarget2);
    Assert.that(oTrigger1, "Trigger1 element '%s' is undefined ", this.oOptions.idTrigger1);
    Assert.that(oTrigger2, "Trigger2 element '%s' is undefined ", this.oOptions.idTrigger2);
  
    // Initialize the effect
    var fShow = this.show.bind(this);
    oTrigger1.observe("click", function() { fShow(2); } );
    oTrigger2.observe("click", function() { fShow(1); } );
    
    this.show(this.oOptions.idFirstVisible);
  },
  
  show: function(iWhich) {
    var oTarget1  = $(this.oOptions.idTarget1);
    var oTarget2  = $(this.oOptions.idTarget2);
    var oTrigger1 = $(this.oOptions.idTrigger1);
    var oTrigger2 = $(this.oOptions.idTrigger2);
    oTarget1[1 == iWhich ? "show" : "hide"]();
    oTarget2[2 == iWhich ? "show" : "hide"]();
    oTrigger1[1 == iWhich ? "show" : "hide"]();
    oTrigger2[2 == iWhich ? "show" : "hide"]();
  }
  
} );

/**
 * PairEffect utiliy function
 */

Object.extend(TogglePairEffect, {
  declaredEffects : {},

  // Initialize a whole group giving the className for all targets
  initGroup: function(sTargetsClass, oOptions) {
    var oDefaultOptions = {
      idStartVisible   : null, // Forces one element to start visible
      bStartAllVisible : false,
      sCookieName      : sTargetsClass
    }
    
    Object.extend(oDefaultOptions, oOptions);
    
    $A(document.getElementsByClassName(sTargetsClass)).each( 
      function(oElement) {
        oDefaultOptions.bStartVisible = oDefaultOptions.bStartAllVisible || (oElement.id == oDefaultOptions.idStartVisible);
        new PairEffect(oElement.id, oDefaultOptions);
      }
    );
  }
});

/**
 * View port manipulation object
 *   Handle view ported objects
 */

var ViewPort = {
  SetAvlHeight: function (sDivId, iPct) {
    var oDiv = $(sDivId);
    if (!oDiv) {
      return;
    }
    var fYDivPos   = 0;
    var fNavHeight = 0;
    var fDivHeight = 0;
  
    // Position Top de la div, hauteur de la fenetre,
    // puis calcul de la taille de la div
    fYDivPos   = Position.cumulativeOffset(oDiv)[1];
    fNavHeight = window.getInnerDimensions().height;
    fDivHeight = fNavHeight - fYDivPos;
    oDiv.style.overflow = "auto";
    oDiv.style.height = (fDivHeight * iPct - 10) +"px";
  },
  
  SetFrameHeight: function(oFrame, oOptions){
    var oDefaultOptions = {
      iBottomMargin : 15
    }
      
    var fYFramePos        = 0;
    var fNavHeight        = 0;
    var fFrameHeight      = 0;
    var fFrameHeightFinal = 0;
    
    // Calcul de la position top de la frame
    fYFramePos = Position.cumulativeOffset(oFrame)[1];  
    
    // hauteur de la fenetre
    fNavHeight = window.getInnerDimensions().height;
    
    // Calcul de la hauteur de la div
    fFrameHeight = fNavHeight - fYFramePos;
  
    // Ajustement de la hauteur
    fFrameHeightFinal = fFrameHeight - oDefaultOptions.iBottomMargin
    
    oFrame.setAttribute("height", fFrameHeightFinal);
  }
}

Object.extend(Calendar, {
  // This function is bound to date specification
  dateStatus: function(date) {
    var sDate = date.toDATE();
    var aStyles = [];
  
    if (this.limit.start && this.limit.start > sDate ||
        this.limit.stop && this.limit.stop < sDate) {
      aStyles.push("disabled");
    }
  
    if (!(this.current.start && this.current.start > sDate || this.current.stop && this.current.stop < sDate) &&
         (this.current.start || this.current.stop)) {
      aStyles.push("current");
    }
    
    if (this.spots.include(sDate)) {
      aStyles.push("spot");
    }
    
    return aStyles.join(" ");
  },

  prepareDates: function(dates) {
    dates.current.start = Calendar.prepareDate(dates.current.start);
    dates.current.stop  = Calendar.prepareDate(dates.current.stop);
    dates.limit.start = Calendar.prepareDate(dates.limit.start);
    dates.limit.stop  = Calendar.prepareDate(dates.limit.stop);
    dates.spots = dates.spots.map(Calendar.prepareDate);
  },
  
  prepareDate: function(datetime) {
    if (!datetime) {
      return null;
    }
    
    return Date.isDATETIME(datetime) ? Date.fromDATETIME(datetime).toDATE() : datetime;
  },
  
  regField: function(sFormName, sFieldName, bTime, userDates) {
    if (userDates && !Object.isArray(userDates.spots)) {
      userDates.spots = Object.values(userDates.spots);
    }

    var dates = {
      current: {
        start: null,
        stop: null
      },
      limit: {
        start: null,
        stop: null
      },
      spots: []
    };
        
    Object.extend(dates, userDates);

    Calendar.prepareDates(dates);
    

    // Test element existence
    var sInputId = sFormName + "_" + sFieldName, 
        oInput = $(sInputId);
    
    if (!oInput) return;
    
    var field;
    if (oInput.disabled && (field = $(sInputId + "_trigger"))) {
      field.hide();
    }
  
    var cal = Calendar.setup( {
        inputField  : sInputId,
        displayArea : sInputId + "_da",
        ifFormat    : "%Y-%m-%d" + (bTime ? " %H:%M:%S" : ""),
        daFormat    : "%d/%m/%Y" + (bTime ? " %H:%M" : ""),
        button      : sInputId + "_trigger",
        showsTime   : bTime,
				firstDay    : 1,
        dateStatusFunc: Calendar.dateStatus.bind(dates),
        onUpdate    : function () {$(sInputId).fire("ui:change")}
      }
    );
  },
  
  regRedirectPopup: function(sInitDate, sRedirectBase, sContainerId, bTime) {
    if (sContainerId == null) sContainerId = "changeDate";
    if (bTime == null) bTime = false;
    
    Calendar.setup( {
        button      : sContainerId,
        date        : Date.fromDATE(sInitDate),
        showsTime   : bTime,
        firstDay    : 1,
        onUpdate    : function(calendar) { 
          if (calendar.dateClicked) {
            sDate = bTime ? calendar.date.toDATETIME() : calendar.date.toDATE();
            window.location = sRedirectBase + sDate;
          }
        }
      } 
    );
  },
  
  regRedirectFlat: function(sInitDate, sRedirectBase, sContainerId, bTime) {
    if (sContainerId == null) sContainerId = "calendar-container";
    if (bTime == null) bTime = false;
  
    dInit = bTime ? Date.fromDATETIME(sInitDate) : Date.fromDATE(sInitDate);
    
    Calendar.setup( {
        date         : dInit,
        showsTime    : bTime,
        firstDay     : 1,
        flat         : sContainerId,
        flatCallback : function(calendar) { 
          if (calendar.dateClicked) {
            sDate = bTime ? calendar.date.toDATETIME() : calendar.date.toDATE();
            window.location = sRedirectBase + sDate;
          }
        }
      } 
    );
  }
} );


/**
 * Durations expressed in milliseconds
 */
var Duration = {
  // Exact durations
  second: 1000,
  minute: 60 * 1000,
  hour: 60 * 60 * 1000,
  day: 24 * 60 * 60 * 1000,
  week: 7 * 24 * 60 * 60 * 1000,
  
  // Approximative durations
  month: 30 * 24 * 60 * 60 * 1000,
  year: 365 * 24 * 60 * 60 * 1000
}

Object.extend(Date, { 
  isDATETIME: function(sDateTime) {
    return sDateTime.match(/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/);
  },
  
  fromDATE: function(sDate) {
    // sDate must be: YYYY-MM-DD
    var aParts = sDate.split("-");
    Assert.that(aParts.length == 3, "'%s' is not a valid DATE", sDate);
  
    var year  = parseInt(aParts[0], 10);
    var month = parseInt(aParts[1], 10);
    var day   = parseInt(aParts[2], 10);
    
    return new Date(year, month - 1, day); // Js months are 0-11!!
  },

  fromDATETIME : function(sDateTime) {
    // sDateTime must be: YYYY-MM-DD HH:MM:SS
    var aHalves = sDateTime.split(" ");
    Assert.that(aHalves.length == 2, "'%s' is not a valid DATETIME", sDateTime);
  
    var sDate = aHalves[0];
    var date = Date.fromDATE(sDate);
  
    var sTime = aHalves[1];
    var aParts = sTime.split(":");
    Assert.that(aParts.length == 3, "'%s' is not a valid TIME", sTime);
  
    date.setHours  (parseInt(aParts[0], 10));
    date.setMinutes(parseInt(aParts[1], 10));
    date.setSeconds(parseInt(aParts[2], 10));
    
    return date;
  },

  fromLocaleDate : function(sDate) {
    // sDate must be: dd/mm/yyyy
    var aParts = sDate.split("/");
    Assert.that(aParts.length == 3, "'%s' is not a valid display date", sDate);
  
    var year  = parseInt(aParts[2], 10);
    var month = parseInt(aParts[1], 10);
    var day   = parseInt(aParts[0], 10);
    
    return new Date(year, month - 1, day); // Js months are 0-11!!
  },

  fromLocaleDateTime : null
  
  
} );

Class.extend(Date, {
  toDATE: function() {
    var y = this.getFullYear();
    var m = this.getMonth()+1; // Js months are 0-11!!
    var d = this.getDate();
    
    return printf("%04d-%02d-%02d", y, m, d);
  },
  
  toDATETIME: function(useSpace) {
    var h = this.getHours();
    var m = this.getMinutes();
    var s = this.getSeconds();
    
    if(useSpace)
      return this.toDATE() + printf(" %02d:%02d:%02d", h, m, s);
    else
      return this.toDATE() + printf("+%02d:%02d:%02d", h, m, s);
  },
  
  toLocaleDate: function() {
    var y = this.getFullYear();
    var m = this.getMonth()+1; // Js months are 0-11!!
    var d = this.getDate();
    
    return printf("%02d/%02d/%04d", d, m, y);
  },
  
  toLocaleDateTime: function () {
    var h = this.getHours();
    var m = this.getMinutes();
    
    return this.toLocaleDate() + printf(" %02d:%02d", h, m);
  },
  
  addDays: function(iDays) {
    this.setDate(this.getDate() + iDays);
  }
} );

/** Token field used to manage multiple enumerations easily.
 *  @param element The element used to get piped values : token1|token2|token3
 *  @param options Accepts the following keys : onChange, confirm, sProps
 */
var TokenField = Class.create({
  initialize: function(element, options) {
    this.element = element;
    
    this.options = Object.extend({
      onChange: Prototype.emptyFunction,
      confirm : null,
      sProps  : null
    }, options || {});
  },
  onComplete: function(value) {
    if(this.options.onChange != null)
      this.options.onChange(value);
    return true;
  },
  add: function(value, multiple) {
    if (!value) {
      return false;
    }
    if(this.options.sProps) {
      oCode = new Element('input', {value: value, className: this.options.sProps});
      ElementChecker.prepare(oCode);
      ElementChecker.checkElement();
      if(ElementChecker.oErrors.length) {
        alert(ElementChecker.getErrorMessage());
        return false;
      }
    }
    var aToken = this.getValues();
    aToken.push(value);
    if(!multiple) aToken = aToken.uniq();
    
    this.onComplete(this.setValues(aToken));
    return true;
  },
  remove: function(value) {
    if(!value || (this.options.confirm && !confirm(this.options.confirm))) {
      return false;
    }

    this.onComplete(this.setValues(this.getValues().without(value)));
    return true;
  },
  contains: function(value) {
   return (this.getValues().indexOf(value) != -1);
  },
  toggle: function(value, force, multiple) {
    if (!Object.isUndefined(force)) {
      return this[force?"add":"remove"](value, multiple);
    }
    return this[this.contains(value)?"remove":"add"](value);
  },
  getValues: function(asString) {
    if (asString) {
      return this.element.value;
    }
    return this.element.value.split("|").without("");
  },
  setValues: function(values) {
    if (Object.isArray(values)) {
      values = values.join("|");
    }
    this.onComplete(this.element.value = values);
    return values;
  }
});

function view_log(classe, id) {
  url = new Url();
  url.setModuleAction("system", "view_history");
  url.addParam("object_class", classe);
  url.addParam("object_id", id);
  url.addParam("user_id", "");
  url.addParam("type", "");
  url.popup(600, 500, "history");
}

function guid_log(guid) {
  var parts = guid.split("-");
  view_log(parts[0], parts[1]);
}

function view_idsante400(classe, id) {
  url = new Url();
  url.setModuleAction("dPsante400", "view_identifiants");
  url.addParam("object_class", classe);
  url.addParam("object_id", id);
  url.popup(750, 400, "sante400");
}

function guid_ids(guid) {
  var parts = guid.split("-");
  view_idsante400(parts[0], parts[1]);
}

function uploadFile(classe, id, categorie_id, file_rename){
  url = new Url();
  url.setModuleAction("dPfiles", "upload_file");
  url.addParam("object_class", classe);
  url.addParam("object_id", id);
  url.addParam("file_category_id", categorie_id);
  url.addParam("file_rename", file_rename);
  url.popup(600, 200, "uploadfile");
}

var Note = Class.create({
  initialize: function() {
    this.url = new Url();
    this.url.setModuleAction("system", "edit_note");
  },
  create: function (classe, object_id) {
    this.url.addParam("object_class", classe);
    this.url.addParam("object_id", object_id);
    this.popup();
  },
  edit: function(note_id) {
    this.url.addParam("note_id", note_id);
    this.popup();
  },
  popup: function () {
    this.url.popup(600, 300, "note");
  }
});

// *******
var notWhitespace   = /\S/;
Dom = {
  writeElem : function(elem_replace_id,elemReplace){
    elem = $(elem_replace_id);
    while (elem.firstChild) {
      elem.removeChild(elem.firstChild);
    }
    if(elemReplace){
      elem.appendChild(elemReplace);
    }
  },
  
  cloneElemById : function(id,withChildNodes){
    var elem = $(id).cloneNode(withChildNodes);
    elem.removeAttribute("id");
    return elem;
  },
  
  createTd : function(sClassname, sColspan){
    var oTd = document.createElement("td");
    if(sClassname){
      oTd.className = sClassname;
    }
    if(sColspan){
      oTd.setAttribute("colspan" , sColspan); 
    }
    return oTd;
  },
  
  createTh : function(sClassname, sColspan){
    var oTh = document.createElement("th");
    if(sClassname){
      oTh.className = sClassname;
    }
    if(sColspan){
      oTh.setAttribute("colspan" , sColspan); 
    }
    return oTh;
  },
  
  createImg : function(sSrc){
    var oImg = document.createElement("img");
    oImg.setAttribute("src", sSrc);
    return oImg;
  },
  
  createInput : function(sType, sName, sValue){
    var oInput = document.createElement("input");
    oInput.setAttribute("type"  , sType);
    oInput.setAttribute("name"  , sName);
    oInput.setAttribute("value" , sValue);
    return oInput;
  },
  
  createSelect : function(sName){
    var oSelect = document.createElement("select");
    oSelect.setAttribute("name"  , sName);
    return oSelect;
  },
  
  createOptSelect : function(sValue, sName, selected, oInsertInto){
    var oOpt = document.createElement("option");
    oOpt.setAttribute("value" , sValue);
    if(selected && selected == true){
      oOpt.setAttribute("selected" , "selected");
    }
    oOpt.innerHTML = sName;
    if(!oInsertInto){
      return oOpt;
    }
    oInsertInto.appendChild(oOpt);
  },
  
  cleanWhitespace : function(node){
    if(node.hasChildNodes()){
      for(var i=0; i< node.childNodes.length; i++){
        var childNode = node.childNodes[i];
        if((childNode.nodeType == Node.TEXT_NODE) && (!notWhitespace.test(childNode.nodeValue))){
          node.removeChild(node.childNodes[i]);
          i--;
        }else if (Object.isElement(childNode)) {
          Dom.cleanWhitespace(childNode);
        } 
      }
    }
  }
}

/** Levenstein function **/

function levenshtein( str1, str2 ) {
    // http://kevin.vanzonneveld.net
    // +   original by: Carlos R. L. Rodrigues
    // *     example 1: levenshtein('Kevin van Zonneveld', 'Kevin van Sommeveld');
    // *     returns 1: 3
 
    var s, l = (s = str1.split("")).length, t = (str2 = str2.split("")).length, i, j, m, n;
    if(!(l || t)) return Math.max(l, t);
    for(var a = [], i = l + 1; i; a[--i] = [i]);
    for(i = t + 1; a[0][--i] = i;);
    for(i = -1, m = s.length; ++i < m;){
        for(j = -1, n = str2.length; ++j < n;){
            a[(i *= 1) + 1][(j *= 1) + 1] = Math.min(a[i][j + 1] + 1, a[i + 1][j] + 1, a[i][j] + (s[i] != str2[j]));
        }
    }
    return a[l][t];
}

function luhn (code) {
  var code_length = code.length;
  var sum = 0;
  var parity = code_length % 2;
  
  for (var i = code_length - 1; i >= 0; i--) {
    var digit = code.charAt(i);
    
    
    if (i % 2 == parity) {
      digit *= 2;
      
      if (digit > 9) {
        digit -= 9;
      }
    }
    
    sum += parseInt(digit);
  }
  
  return ((sum % 10) == 0);
}


/* Control tabs creation. It saves selected tab into a cookie name TabState */
Object.extend (Control.Tabs, {
  storeTab: function (tabName, tab) {
    new CookieJar().setValue("TabState", tabName, tab);
  },
  loadTab: function (tabName) {
    return new CookieJar().getValue("TabState", tabName);
  },
  create: function (name, storeInCookie) {
    if ($(name)) {
      var tab = new Control.Tabs(name);
      if (storeInCookie) {
        tab.options.afterChange = function (tab, tabName) {
          Control.Tabs.storeTab(name, tab.id);
        }
				var tabName = Control.Tabs.loadTab(name);
				if (tabName) {
					tab.setActiveTab(tabName);
				}
      }
      return tab;
    }
  }
} );


Class.extend (Control.Tabs, {
  changeTabAndFocus: function(iIntexTab, oField) {
    this.setActiveTab(iIntexTab);
    if (oField) {
      oField.focus();
    } else {
      var oForm = $$('form')[0];
      if (oForm) {
        oForm.focusFirstElement();
      }
    }
  }
} );

window.getInnerDimensions = function() {
  return {width: document.documentElement.clientWidth, height: document.documentElement.clientHeight};
}

/** DOM element creator for Prototype by Fabien M�nager
 *  Inspired from Michael Geary 
 *  http://mg.to/2006/02/27/easy-dom-creation-for-jquery-and-prototype
 **/
var DOM = {
  defineTag: function (tag) {
    DOM[tag] = function () {
      return DOM.createNode(tag, arguments);
    };
  },
  
  createNode: function (tag, args) {
    var e;
    try {
      e = new Element(tag, args[0]);
      for (var i = 1; i < args.length; i++) {
        var arg = args[i];
        if (arg == null) continue;
        if (!Object.isArray(arg)) e.insert (arg);
        else {
          for (var j = 0; j < arg.length; j++) e.insert(arg[j]);
        }
      }
    }
    catch (ex) {
      alert('Cannot create <' + tag + '> element:\n' + Object.inspect(args) + '\n' + ex.message);
      e = null;
    }
    return e;
  },
  
  tags: [
    'a', 'br', 'button', 'canvas', 'div', 'fieldset', 'form',
    'h1', 'h2', 'h3', 'h4', 'h5', 'hr', 'img', 'input', 'label', 
    'legend', 'li', 'ol', 'optgroup', 'option', 'p', 'pre', 
    'select', 'span', 'strong', 'table', 'tbody', 'td', 'textarea',
    'tfoot', 'th', 'thead', 'tr', 'tt', 'ul'
  ]
};

DOM.tags.each(function (tag) {
  DOM.defineTag (tag);
});


/** l10n functions */
var l10n = {
  tr: function (token) {
    return locales ? (locales[token] || token) : token;
  }
}

locales = {};

var $T = l10n.tr;

// Replacements for the javascript alert() and confirm()
/*
window.alert = function(message, options) {
  options = Object.extend({
    className: 'modal alert big-warning',
    okLabel: 'OK',
    onValidate: Prototype.emptyFunction,
    closeOnClick: null
  }, options || {});
  
  var html = '<div style="min-height: 3em;">'+message+'</div><div style="text-align: center; margin-left: -3em;"><button class="tick" type="button">'+options.okLabel+'</button></div>';
  var modal = Control.Modal.open(html, options);
  modal.container.select('button.tick').first().observe('click', (function(){this.close(); options.onValidate();}).bind(modal));
}

window.confirm = function(message, options) {
  options = Object.extend({
    className: 'modal confirm big-info',
    yesLabel: 'Oui',
    noLabel: 'Non',
    onValidate: Prototype.emptyFunction,
    closeOnClick: null
  }, options || {});
  
  var html = '<div style="min-height: 3em;">'+message+'</div><div style="text-align: center; margin-left: -3em;">'+
    '<button class="tick" type="button">'+options.yesLabel+'</button>'+
    '<button class="cancel" type="button">'+options.noLabel+'</button>'+
  '</div>';
  var modal = Control.Modal.open(html, options);
  modal.container.select('button.tick').first().observe('click', (function(){this.close(); options.onValidate(true);}).bind(modal));
  modal.container.select('button.cancel').first().observe('click', (function(){this.close(); options.onValidate(false);}).bind(modal));
}

window.open = function(element, title, options) {
  options = Object.extend({
    className: 'modal popup',
    width: 800,
    height: 500,
    iframe: true
  }, options || {});
  
  Control.Modal.open(element, options);
  return false;
}
*/

window.modal = function(container, options) {
  options = Object.extend({
    className: 'modal',
    closeOnClick: null,
    overlayOpacity: 0.5
  }, options || {});
  return Control.Modal.open(container, options);
};

var Session = {
  lock: function(){
    $('main').hide();
    
    var mask = $('sessionLockMask').setStyle({
        opacity: 1,
        backgroundColor: '#000',
        zIndex: 10000,
        position: 'fixed'
      }).show().clonePosition(document.body);
      
    var vpd = document.viewport.getDimensions(),
        win = mask.select('div.window').first(),
        dim = win.getDimensions();
        
    win.setStyle({
      top: (vpd.height - dim.height)/2 + 'px',
      left: (vpd.width - dim.width) /2 + 'px'
    });
  },
  unlock: function(){
    $('sessionLockMask').hide(); 
    $('main').show();
  },
  close: function(){
    document.location.href = '?logout=-1';
  }
}
