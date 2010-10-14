{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPurgences
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

{{mb_include_script module=dPurgences script=main_courante}}
{{mb_include_script module=dPurgences script=identito_vigilance}}
{{if $isImedsInstalled}}
  {{mb_include_script module="dPImeds" script="Imeds_results_watcher"}}
{{/if}}

{{if !$group->service_urgences_id}}
  <div class="small-warning">{{tr}}dPurgences-no-service_urgences_id{{/tr}}</div>
{{else}}

<script type="text/javascript">
 
function updateConsultations(frequency) {
  var url = new Url("dPcabinet", "vw_journee");
  url.addParam("date", "{{$date}}");
  url.addParam("mode_urgence", true);
  url.periodicalUpdate('consultations', { frequency: frequency } );
} 
 
onMergeComplete = function() {
  IdentitoVigilance.start(0, 80);
  MainCourante.start(1, 60);
}

Main.add(function () {
  // Delays prevent potential overload with periodical previous updates
  MainCourante.start(0, 60);
	{{if $dPconfig.dPurgences.gerer_reconvoc == "1"}}
  updateConsultations.delay(1, 80);
	{{/if}}
  IdentitoVigilance.start(2,100);

  var tabs = Control.Tabs.create('tab_main_courante', false);
});

</script>

<ul id="tab_main_courante" class="control_tabs">
  <li style="float: right">
    <form action="?" name="FindSejour" method="get">
      <label for="sip_barcode" title="Veuillez doucher le num�ro de dossier sur un document ou bien le saisir � la main">
        Code � barres de dossier
      </label>
        
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <input type="text" size="5" name="sip_barcode" onchange="this.form.submit()" />
      
      <button type="submit" class="search notext">{{tr}}Search{{/tr}}</button>
    </form>
  </li>
  <li><a href="#holder_main_courante">Main courante <small>(&ndash;)</small></a></li>
	{{if $dPconfig.dPurgences.gerer_reconvoc == "1"}}
  <li><a href="#consultations" class="empty">Reconvocations <small>(&ndash; / &ndash;)</small></a></li>
	{{/if}}
  <li><a href="#identito_vigilance" class="empty">Identito-vigilance <small>(&ndash;)</small></a></li>
  <li style="width: 20em; text-align: center">
    <script type="text/javascript">
    Main.add(function() {
      Calendar.regField(getForm("changeDate").date, null, { noView: true } );
    } );
    </script>
    <strong><big>{{$date|date_format:$dPconfig.longdate}}</big></strong>
    
    <form action="?" name="changeDate" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
    </form>
  </li>
</ul>
<hr class="control_tabs" />

<div id="holder_main_courante">
	<table style="width: 100%;">
	  <tr>
	    <td style="white-space: nowrap;" class="narrow">
	      <a class="button new" href="?m=dPurgences&amp;tab=vw_aed_rpu&amp;rpu_id=0">
	        {{tr}}CRPU-title-create{{/tr}}
	      </a>
	    </td>
			
	    <td style="text-align: left; padding-left: 2em;">
        {{mb_include template=inc_hide_missing_rpus}}
        {{mb_include template=inc_hide_previous_rpus}}
			</td>

	    <td style="text-align: right">
	     Affichage
	     <form name="selView" action="?m=dPurgences&amp;tab=vw_idx_rpu" method="post">
		      <select name="selAffichage" onchange="this.form.submit();">
		        <option value="tous" {{if $selAffichage == "tous"}}selected = "selected"{{/if}}>Tous</option>
		        <option value="presents" {{if $selAffichage == "presents"}} selected = "selected" {{/if}}>Pr�sents</option>
		        <option value="prendre_en_charge" {{if $selAffichage == "prendre_en_charge"}} selected = "selected" {{/if}}>A prendre en charge</option>
		        <option value="annule_hospitalise" {{if $selAffichage == "annule_hospitalise"}} selected = "selected" {{/if}}>Annul� et Hospitalis�</option>
		      </select>
		    </form>
	      <a href="#" onclick="MainCourante.print('{{$date}}')" class="button print">Main courante</a>
	      <a href="#" onclick="MainCourante.legend()" class="button search">L�gende</a>
	    </td>
	  </tr>
	</table>
  
	<div id="main_courante"></div>
</div>
{{if $dPconfig.dPurgences.gerer_reconvoc == "1"}}
<div id="consultations" style="display: none;">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>
{{/if}}
<div id="identito_vigilance" style="display: none; margin: 0 5px;">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>


{{/if}}