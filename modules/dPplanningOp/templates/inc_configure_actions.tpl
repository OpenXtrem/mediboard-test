<script type="text/javascript">
viewNoPratSejour = function() {
  var url = new Url("dPplanningOp", "vw_resp_no_prat"); 
  url.popup(700, 500, "printFiche");
}

checkSynchroSejour = function(sType) {
  var url = new Url("dPplanningOp", "check_synchro_hours_sejour");
  url.addParam("type", sType);
  url.requestUpdate("resultSynchroSejour");
}

closeSejourConsult = function() {
  var url = new Url("dPplanningOp", "ajax_close_sejour_consult");
  url.requestUpdate("result-close-sejour-consult");
}

</script>

<h2>Actions de maintenances</h2>

<table class="tbl">
  <tr>
    <th style="width: 50%">{{tr}}Action{{/tr}}</th>
    <th style="width: 50%">{{tr}}Status{{/tr}}</th>
  </tr>
  
  <tr>
    <td>
      <button class="change" onclick="viewNoPratSejour()">
        Corriger les praticiens des s�jours
      </button>
    </td>
    <td>
    </td>
  </tr>
  
  <tr>
    <td>
      <button class="search" onclick="checkSynchroSejour('check_entree');">
        Nombre d'heure d'entr�e non conforme
      </button>
			<button class="save" onclick="checkSynchroSejour('fix_entree');">
        Corriger les probl�mes d'entr�e
      </button>
      <br />
      <button class="search" onclick="checkSynchroSejour('check_sortie');">
        Nombre d'heure de sortie non conforme
      </button>
      <button class="save" onclick="checkSynchroSejour('fix_sortie');">
        Corriger les probl�mes de sortie
      </button>
    </td>
    <td id="resultSynchroSejour">
    </td>
  </tr>
	
	<tr>
    <td>
      <button class="change" onclick="closeSejourConsult()">
        {{tr}}close-sejour-consult{{/tr}}
      </button>
    </td>
    <td id="result-close-sejour-consult">
    </td>
  </tr>

</table>