<script type="text/javascript">

function submitPersonnel(oForm){
  submitFormAjax(oForm, 'systemMsg', { onComplete : reloadPersonnel });
}

</script>
       
<table class="form">
<tr>
  <th class="category" colspan="3">
    Personnel en salle
  </th>
</tr>
<tr>
  <th class="category">Personnel pr�vu</th>
  <th class="category">Personnel ajout�<br />
    <form name="affectationPers" action="?m={{$m}}" method="post">
      <input type="hidden" name="m" value="dPpersonnel" />
      <input type="hidden" name="dosql" value="do_affectation_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="affect_id" value="" />
    
      <input type="hidden" name="object_class" value="COperation" />
      <input type="hidden" name="object_id" value="{{$selOp->_id}}" />
      <input type="hidden" name="realise" value="0" />
    
      <select name="personnel_id" onchange="submitPersonnel(this.form)">
        <option value="">&mdash; Choix aide-op�ratoire</option>
        {{foreach from=$listPersAideOp item="pers"}}
        <option value="{{$pers->_id}}">{{$pers->_ref_user->_view}}</option>
        {{/foreach}}
      </select>
    </form>
    
    <form name="affectationPers" action="?m={{$m}}" method="post">
      <input type="hidden" name="m" value="dPpersonnel" />
      <input type="hidden" name="dosql" value="do_affectation_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="affect_id" value="" />
    
      <input type="hidden" name="object_class" value="COperation" />
      <input type="hidden" name="object_id" value="{{$selOp->_id}}" />
      <input type="hidden" name="realise" value="0" />
    
      <select name="personnel_id" onchange="submitPersonnel(this.form)">
        <option value="">&mdash; Choix panseuse</option>
        {{foreach from=$listPersPanseuse item="pers"}}
        <option value="{{$pers->_id}}">{{$pers->_ref_user->_view}}</option>
        {{/foreach}}
      </select>
    </form>
    
  </th>
</tr>




<tr>
  <td>

 {{foreach from=$tabPersonnel.plage item=affectation}}
    <form name="affectationPersonnel-{{$affectation->_id}}" action="?m={{$m}}" method="post">
    <input type="hidden" name="m" value="dPpersonnel" />
    <input type="hidden" name="dosql" value="do_affectation_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="affect_id" value="{{$affectation->_id}}" />
    <input type="hidden" name="personnel_id" value="{{$affectation->_ref_personnel->_id}}" />
    <input type="hidden" name="object_class" value="COperation" />
    <input type="hidden" name="object_id" value="{{$selOp->_id}}" />
    <input type="hidden" name="realise" value="0" />
    
    {{$affectation->_ref_personnel->_ref_user->_view}} /
    {{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}
    <br />
    {{if $affectation->_debut}}
     {{if $can->edit}}
     <input name="_debut" type="text" size="5"  value="{{$affectation->_debut|date_format:'%H:%M'}}" />
     <button type="button" class="tick notext" onclick="submitPersonnel(this.form)">{{tr}}Save{{/tr}}</button>
     <button type="button" class="cancel notext" onclick="this.form._debut.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{elseif $modif_operation}}
       <select name="_debut" onchange="submitPersonnel(this.form);">
       <option value="">-</option>
       {{assign var="affectation_id" value=$affectation->_id}}
       {{assign var="timing" value=$timingAffect.$affectation_id}}
      {{foreach from=$timing._debut|smarty:nodefaults item=curr_time}}
       <option value="{{$curr_time}}" {{if $curr_time == $affectation->_debut}}selected="selected"{{/if}}>
         {{$curr_time|date_format:"%Hh%M"}}
       </option>
       {{/foreach}}
       </select>
       <button type="button" class="cancel notext" onclick="this.form._debut.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{else}}
       {{$affectation->_debut|date_format:"%Hh%M"}}
     {{/if}}
   {{elseif $can->edit || $modif_operation}}
      <input type="hidden" name="_debut" value="" />
      <button type="button" onclick="this.form._debut.value = 'current'; submitPersonnel(this.form)" class="submit">D�but</button>
    {{else}}-{{/if}}
    {{if $affectation->_fin}}
     {{if $can->edit}}
     <input name="_fin" type="text" size="5"  value="{{$affectation->_fin|date_format:'%H:%M'}}" />
     <button type="button" class="tick notext" onclick="submitPersonnel(this.form)">{{tr}}Save{{/tr}}</button>
     <button type="button" class="cancel notext" onclick="this.form._fin.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{elseif $modif_operation}}
       <select name="_fin" onchange="submitPersonnel(this.form);">
       <option value="">-</option>
    
       {{assign var="affectation_id" value=$affectation->_id}}
       {{assign var="timing" value=$timingAffect.$affectation_id}}
       {{foreach from=$timing._fin|smarty:nodefaults item=curr_time}}
       <option value="{{$curr_time}}" {{if $curr_time == $affectation->_fin}}selected="selected"{{/if}}>
         {{$curr_time|date_format:"%Hh%M"}}
       </option>
       {{/foreach}}
       </select>
       <button type="button" class="cancel notext" onclick="this.form._fin.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{else}}
       {{$affectation->_fin|date_format:"%Hh%M"}}
     {{/if}}
   {{elseif $can->edit || $modif_operation}}
      <input type="hidden" name="_fin" value="" />
      <button type="button" onclick="this.form._fin.value = 'current'; submitPersonnel(this.form)" class="submit">Fin</button>
    {{else}}-{{/if}}
   </form>

 <hr />   
{{/foreach}}
  </td>
  
  <td>
   {{foreach from=$tabPersonnel.operation item=affectation}}
     {{if $can->edit || $modif_operation}}
    <div style="float: right">
    <form name="cancelAffectation" action="?m={{$m}}" method="post">
      <input type="hidden" name="affect_id" value="{{$affectation->_id}}" />
      <input type="hidden" name="m" value="dPpersonnel" />
      <input type="hidden" name="dosql" value="do_affectation_aed" />
      <input type="hidden" name="del" value="1" />
      <button type="button" class="cancel notext" onclick="submitPersonnel(this.form)">{{tr}}Cancel{{/tr}}</button>
    </form>
    </div>
    {{/if}}
    <form name="affectationPersonnel-{{$affectation->_id}}" action="?m={{$m}}" method="post">
    <input type="hidden" name="m" value="dPpersonnel" />
    <input type="hidden" name="dosql" value="do_affectation_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="affect_id" value="{{$affectation->_id}}" />
    <input type="hidden" name="personnel_id" value="{{$affectation->_ref_personnel->_id}}" />
    <input type="hidden" name="object_class" value="COperation" />
    <input type="hidden" name="object_id" value="{{$selOp->_id}}" />
    <input type="hidden" name="realise" value="0" />
    
    {{$affectation->_ref_personnel->_ref_user->_view}} / 
    {{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}
    <br />
    {{if $affectation->_debut}}
     {{if $can->edit}}
     <input name="_debut" type="text" size="5"  value="{{$affectation->_debut|date_format:'%H:%M'}}" />
     <button type="button" class="tick notext" onclick="submitPersonnel(this.form)">{{tr}}Save{{/tr}}</button>
     <button type="button" class="cancel notext" onclick="this.form._debut.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{elseif $modif_operation}}
       <select name="_debut" onchange="submitPersonnel(this.form);">
       <option value="">-</option>
       {{assign var="affectation_id" value=$affectation->_id}}
       {{assign var="timing" value=$timingAffect.$affectation_id}}
      {{foreach from=$timing._debut|smarty:nodefaults item=curr_time}}
       <option value="{{$curr_time}}" {{if $curr_time == $affectation->_debut}}selected="selected"{{/if}}>
         {{$curr_time|date_format:"%Hh%M"}}
       </option>
       {{/foreach}}
       </select>
       <button type="button" class="cancel notext" onclick="this.form._debut.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{else}}
       {{$affectation->_debut|date_format:"%Hh%M"}}
     {{/if}}
   {{elseif $can->edit || $modif_operation}}
      <input type="hidden" name="_debut" value="" />
      <button type="button" onclick="this.form._debut.value = 'current'; submitPersonnel(this.form)" class="submit">D�but</button>
    {{else}}-{{/if}}
    {{if $affectation->_fin}}
     {{if $can->edit}}
     <input name="_fin" type="text" size="5"  value="{{$affectation->_fin|date_format:'%H:%M'}}" />
     <button type="button" class="tick notext" onclick="submitPersonnel(this.form)">{{tr}}Save{{/tr}}</button>
     <button type="button" class="cancel notext" onclick="this.form._fin.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{elseif $modif_operation}}
       <select name="_fin" onchange="submitPersonnel(this.form);">
       <option value="">-</option>
    
       {{assign var="affectation_id" value=$affectation->_id}}
       {{assign var="timing" value=$timingAffect.$affectation_id}}
       {{foreach from=$timing._fin|smarty:nodefaults item=curr_time}}
       <option value="{{$curr_time}}" {{if $curr_time == $affectation->_fin}}selected="selected"{{/if}}>
         {{$curr_time|date_format:"%Hh%M"}}
       </option>
       {{/foreach}}
       </select>
       <button type="button" class="cancel notext" onclick="this.form._fin.value = ''; submitPersonnel(this.form);">{{tr}}Cancel{{/tr}}</button>
     {{else}}
       {{$affectation->_fin|date_format:"%Hh%M"}}
     {{/if}}
   {{elseif $can->edit || $modif_operation}}
      <input type="hidden" name="_fin" value="" />
      <button type="button" onclick="this.form._fin.value = 'current'; submitPersonnel(this.form)" class="submit">Fin</button>
    {{else}}-{{/if}}
   </form>

 <hr />   
{{/foreach}}
</td>
</tr>


</table>

