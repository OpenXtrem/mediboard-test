{{assign var=perfusion_id value=$_perfusion->_id}}
<tr>
  <td style="text-align: center">
   - 
  </td>
 	<td class="text">
 	  <div class="mediuser" style="border-color: #{{$_perfusion->_ref_praticien->_ref_function->color}}">
 	  <div onclick='addCibleTransmission("CPerfusion","{{$_perfusion->_id}}","{{$_perfusion->_view}}");' 
	       class="{{if @$transmissions.CPerfusion.$perfusion_id|@count}}transmission{{else}}transmission_possible{{/if}}">
	    <a href="#{{$_perfusion->_guid}}" onmouseover="ObjectTooltip.createEx(this, '{{$_perfusion->_guid}}')">
	      {{$_perfusion}} 
	    </a>
	  </div>
	  </div>
	</td>
 	<td class="text" style="font-size: 1em;">
 	  <ul>
 	   {{foreach from=$_perfusion->_ref_lines item=_line}}
 	     <li>{{$_line->_view}}</li>
 	   {{/foreach}}
 	  </ul>
 	</td>	  
  {{foreach from=$dates item=date name="foreach_date"}}
    <td style="{{if $date < $_perfusion->_debut|date_format:'%Y-%m-%d' ||  $date > $_perfusion->_fin|date_format:'%Y-%m-%d'}}background-color: #ddd;{{/if}} text-align: center"> 		          
    </td>
  {{/foreach}}
</tr>