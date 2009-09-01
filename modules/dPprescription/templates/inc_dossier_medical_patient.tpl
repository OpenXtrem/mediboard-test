{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage dPprescription
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<script type="text/javascript">

transfertLineTP = function(line_id, sejour_id){
  var oForm = document.transfert_line_TP;
  $V(oForm.prescription_line_medicament_id, line_id);
  $V(oForm.sejour_id, sejour_id);
  submitFormAjax(oForm, 'systemMsg', { onComplete: function(){ 
    window.opener.Prescription.reloadPrescSejour('{{$prescription_sejour_id}}','{{$sejour_id}}', null, null, null, null, null, true, {{if $app->user_prefs.mode_readonly}}false{{else}}true{{/if}},'');
  } } );
}


</script>

<form name="transfert_line_TP" action="?" method="post">
  <input type="hidden" name="m" value="dPprescription" />
  <input type="hidden" name="dosql" value="do_transfert_line_tp_aed" />
  <input type="hidden" name="prescription_line_medicament_id" value="" />
  <input type="hidden" name="sejour_id" value="" />
	<input type="hidden" name="user_id" value="{{$user->_id}}" />
</form>

<table class="form">
  <tr>
    <th class="category">Traitements du patient</th>
  </tr>
  <tr>
    <td>
		{{if $dossier_medical->_ref_prescription}}
			<ul>
			{{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line}}
			  <li>
			    {{if $sejour_id && ($user->_is_praticien || $can->admin)}}
		      <button title="{{tr}}Add{{/tr}}" class="add notext" type="button" onclick="transfertLineTP('{{$_line->_id}}','{{$sejour_id}}');">
		        {{tr}}Add{{/tr}}
		      </button>
		      {{/if}}       
		      
		      {{if $_line->fin}}
			      Du {{$_line->debut|date_format:"%d/%m/%Y"}} au {{$_line->fin|date_format:"%d/%m/%Y"}} :
			    {{elseif $_line->debut}}
			      Depuis le {{$_line->debut|date_format:"%d/%m/%Y"}} :
			    {{/if}}
		      <span class="tooltip-trigger" onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}', 'objectView')">
				    {{$_line->_ucd_view}} ({{$_line->_forme_galenique}})
				  </span>
				</li>
			{{foreachelse}}
				Aucun traitement dans le dossier m�dical du patient
			{{/foreach}}
			</ul>
		{{/if}}
		</td>
  </tr>
</table>