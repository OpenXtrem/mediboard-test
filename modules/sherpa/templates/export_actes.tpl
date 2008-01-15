<script type="text/javascript">
Main.add(function() {
  regRedirectPopupCal("{{$date}}", "?m={{$m}}&tab={{$tab}}&date="); 
} );

</script>

<h2>Envoi d'actes Sherpa</h2>

<table class="tbl">
  <tr>
    <th class="title" colspan="10">
      Envoi d'actes pour les s�jours sortis le : 
      {{$date|date_format:"%A %d %B %Y"}}
			<img id="changeDate" src="./images/icons/calendar.gif" title="Choisir la date" alt="calendar" />
    </th>
  </tr>

  <tr>
    <td>{{mb_title object=$acte_ccam field=executant_id}}</td>
    <td>{{mb_title object=$acte_ccam field=execution}}</td>
    <td>{{mb_title object=$acte_ccam field=code_acte}}</td>
    <td>{{mb_title object=$acte_ccam field=code_activite}}</td>
    <td>{{mb_title object=$acte_ccam field=code_phase}}</td>
    <td>{{mb_title object=$acte_ccam field=modificateurs}}</td>
    <td>{{mb_title object=$acte_ccam field=code_association}}</td>
    <td>{{mb_title object=$acte_ccam field=montant_base}}</td>
    <td>{{mb_title object=$acte_ccam field=montant_depassement}}</td>
    <td>Statut de l'envoi</td>
    
  </tr>

	{{foreach from=$sejours item=_sejour}}
  <tr>
    <th colspan="10">
    {{$_sejour->_view}}
    <strong>[{{$_sejour->_num_dossier}}]</strong>
	    &mdash; Dr. {{$_sejour->_ref_praticien->_view}}
    
    </th>
  </tr>
  
  <!-- Actes du s�jour -->
  {{foreach from=$_sejour->_ref_actes_ccam item=_acte_ccam}}
  <tr>
    <td>{{$_acte_ccam->_ref_executant->_view}}</td>
    <td>{{mb_value object=$_acte_ccam field=execution}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_acte}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_activite}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_phase}}</td>
    <td>{{mb_value object=$_acte_ccam field=modificateurs}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_association}}</td>
    <td>{{mb_value object=$_acte_ccam field=montant_base}}</td>
    <td>{{mb_value object=$_acte_ccam field=montant_depassement}}</td>
    <td>
      {{assign var=acte_id value=$_acte_ccam->_id}}
      {{if $exports.$acte_id === null}}
      <div class="warning">Acte non export�</div>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10"><em>Pas d'acte d'hospitalisation</em></td>
  </tr>
  {{/foreach}}

  {{foreach from=$_sejour->_ref_operations item=_operation}}
  <tr>
    <th colspan="10">
	    Op�ration du {{$_operation->_datetime}} 
	    {{if $_operation->libelle}}
	    <em>[{{$_operation->libelle}}]</em>
	    {{/if}}
	    &mdash; Dr. {{$_operation->_ref_chir->_view}}
    </th>
  </tr>
  {{foreach from=$_operation->_ref_actes_ccam item=_acte_ccam}}
  <tr>
    <td>{{$_acte_ccam->_ref_executant->_view}}</td>
    <td>{{mb_value object=$_acte_ccam field=execution}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_acte}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_activite}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_phase}}</td>
    <td>{{mb_value object=$_acte_ccam field=modificateurs}}</td>
    <td>{{mb_value object=$_acte_ccam field=code_association}}</td>
    <td>{{mb_value object=$_acte_ccam field=montant_base}}</td>
    <td>{{mb_value object=$_acte_ccam field=montant_depassement}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10"><em>Pas d'acte d'intervention</em></td>
  </tr>
  {{/foreach}}
  {{/foreach}}


	{{/foreach}}
</table>