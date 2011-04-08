{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage forms
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

<style type="text/css">
fieldset {
	margin: 0px;
}
</style>

{{if !@$readonly}}

<script type="text/javascript">
if (window.opener && window.opener !== window && window.opener.ExObject) {
  window.onunload = function(){
    window.opener.ExObject.register.defer("{{$_element_id}}", {
      ex_class_id: "{{$ex_class_id}}", 
      object_guid: "{{$object_guid}}", 
      event: "{{$event}}", 
      _element_id: "{{$_element_id}}"
    });
  }
}

ExObjectFormula.tokenData = {{$formula_token_values|@json}};

Main.add(function(){
  $H(ExObjectFormula.tokenData).each(function(token){
    var field = token.key;
    var data = token.value;
    var formula = data.formula;

    if (!formula) return;

    formula = formula.replace(/[\[\]]/g, "");
    var expr = ExObjectFormula.parser.parse(formula);
    var variables = expr.variables();
    
    var form = getForm("editExObject");
    var fieldElement = form[field];

    ExObjectFormula.tokenData[field].parser = expr;
    ExObjectFormula.tokenData[field].variables = variables;

    function compute(input, target) {
      ExObjectFormula.computeResult(input, target);
    }
    
    variables.each(function(v){
      if (!form[v]) return;
      
      var inputs = ExObjectFormula.getInputElementsArray(form[v]);
      
      inputs.each(function(input){
        if (input.hasClassName("date") || input.hasClassName("dateTime") || input.hasClassName("time")) {
          input.onchange = compute.curry(input, fieldElement);
        }
        else {
				  var callback = compute.curry(input, fieldElement);
          input.observe("change", callback)
					     .observe("ui:change", callback)
							 .observe("click", callback);
        }
      });
    });
  });
});
</script>

{{mb_form name="editExObject" m="system" dosql="do_ex_object_aed" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function(){ window.close() } })"}}
  {{mb_key object=$ex_object}}
  {{mb_field object=$ex_object field=_ex_class_id hidden=true}}
  {{mb_field object=$ex_object field=object_class hidden=true}}
  {{mb_field object=$ex_object field=object_id hidden=true}}
  
  <input type="hidden" name="del" value="0" />
	
	<h2>
	  {{if in_array("IPatientRelated", class_implements($ex_object->object_class))}}
	    {{assign var=_patient value=$ex_object->_ref_object->loadRelPatient()}}
	    <big style="color: #006600; font-weight: bold;" onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}');">
			  {{$_patient}}
			</big>
			&ndash;
	  {{/if}}
		
		{{$ex_object->_ref_ex_class->name}} - {{$object}}
	</h2>
	
	<script type="text/javascript">
		Main.add(function(){
		  Control.Tabs.create("ex_class-groups-tabs");
			document.title = "{{$ex_object->_ref_ex_class->name}} - {{$object}}";
		});
	</script>
	
	<ul id="ex_class-groups-tabs" class="control_tabs">
	{{foreach from=$grid key=_group_id item=_grid}}
	  {{if $groups.$_group_id->_ref_fields|@count}}
	  <li>
	  	<a href="#tab-{{$groups.$_group_id->_guid}}">{{$groups.$_group_id}}</a>
	  </li>
		{{/if}}
	{{/foreach}}
	</ul>
  <hr class="control_tabs" />
	
  <table class="main form">
  	
		{{foreach from=$grid key=_group_id item=_grid}}
		{{if $groups.$_group_id->_ref_fields|@count}}
		<tbody id="tab-{{$groups.$_group_id->_guid}}" style="display: none;">
    {{foreach from=$_grid key=_y item=_line}}
    <tr>
      {{foreach from=$_line key=_x item=_group}}
	      {{if $_group.object}}
	        {{if $_group.object instanceof CExClassField}}
					  {{if $_group.type == "label"}}
						  {{assign var=_field value=$_group.object}}
							{{if $_field->coord_field_x == $_field->coord_label_x+1}}
                <th style="font-weight: bold; vertical-align: middle;">
                  {{mb_label object=$ex_object field=$_field->name}}
                </th>
							{{else}}
                <td style="font-weight: bold; text-align: left;">
                  {{mb_label object=$ex_object field=$_field->name}}
                </td>
							{{/if}}
					  {{elseif $_group.type == "field"}}
		          <td>
                {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_group.object}}
		          </td>
						{{/if}}
          {{elseif $_group.object instanceof CExClassHostField}}
            {{assign var=_host_field value=$_group.object}} 
              {{if $_group.type == "label"}}
                <th style="font-weight: bold; text-align: left;">
                  {{mb_title object=$ex_object->_ref_object field=$_host_field->field}}
                </th>
              {{else}}
                <td>
                  {{mb_value object=$ex_object->_ref_object field=$_host_field->field}}
                </td>
              {{/if}}
					{{else}}
            {{assign var=_message value=$_group.object}} 
					  	{{if $_group.type == "message_title"}}
							  <th style="font-weight: bold; text-align: left;">
					    	  {{$_message->title}}
								</th>
							{{else}}
                <td>
                	<div class="small-{{$_message->type}}">
                    {{mb_value object=$_message field=text}}
									</div>
                </td>
							{{/if}}
					{{/if}}
        {{else}}
          <td></td>
				{{/if}}
      {{/foreach}}
    </tr>
    {{/foreach}}
		
		{{* Out of grid *}}
    {{foreach from=$groups.$_group_id->_ref_fields item=_field}}
      {{assign var=_field_name value=$_field->name}}
			
		  {{if isset($out_of_grid.$_group_id.field.$_field_name|smarty:nodefaults)}}
		    <tr>
		      <th style="font-weight: bold; width: 50%; vertical-align: middle;" colspan="2">
		        {{mb_label object=$ex_object field=$_field->name}}
		      </th>
		      <td colspan="2">
		        {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_field}}
		      </td>
		    </tr>
		  {{/if}}
    {{/foreach}}
    
    </tbody>
		{{/if}}
    {{/foreach}}
		
    <tr>
      <td colspan="4" class="button">
        {{if $ex_object->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax: true, typeName:'', objName:'{{$ex_object->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
		
  </table>

{{/mb_form}}

{{else}}

<table class="main form">
  <tr>
    <th class="title" colspan="4">
      {{$ex_object->_ref_ex_class}} - {{$object}}
    </th>
  </tr>
  
  {{foreach from=$grid key=_y item=_line}}
  <tr>
    {{foreach from=$_line key=_x item=_group}}
      {{if $_group.label}}
        {{assign var=_field value=$_group.label}} 
        <th style="font-weight: bold;">
          {{mb_label object=$ex_object field=$_field->name}}
        </th>
      {{elseif $_group.field}}
        {{assign var=_field value=$_group.field}} 
        <td>
          {{mb_value object=$ex_object field=$_field->name}}
        </td>
      {{else}}
        <td></td>
      {{/if}}
    {{/foreach}}
  </tr>
  {{/foreach}}
</table>
{{/if}}