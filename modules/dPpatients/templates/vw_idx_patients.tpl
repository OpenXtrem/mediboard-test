{{* $Id$ *}}

{{mb_script module="dPcompteRendu" script="document"}}

<script type="text/javascript">
	
markAsSelected = function(element) {
  removeSelectedTr();
  $(element).up(1).addClassName('selected');
}

removeSelectedTr = function(){
  $("list_patients").select('.selected').each(function (e) {e.removeClassName('selected')});
}

{{if $patient->_id}}
Main.add(function(){
	reloadPatient('{{$patient->_id}}', 0);
});
{{/if}}
	
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      {{include file="inc_list_patient.tpl"}}
    </td>
    <td class="halfPane" id="vwPatient">
      <div class="small-info">
      	Veuillez sélectionner un patient sur la gauche pour pouvoir le visualiser
      </div>
		</td>
  </tr>
</table>