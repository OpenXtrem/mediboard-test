{{* $Id$ *}}

{{*
 * @package Mediboard
 * @subpackage system
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
*}}

{{if $objects|@count}}
  {{if $insertion == "after"}}
	  <tbody class="object-list">
	{{/if}}
	
		{{foreach from=$objects item=_object}}
		<tr>
			<td style="padding-left: 18px;">
        {{if $tag->color}}
          <span style="float: right; border-right: 1em solid #{{$tag->color}};" title="{{$tag}}">&nbsp;</span>
        {{/if}}
				
				<a href="#edit.{{$_object->_guid}}" onclick="MbObject.edit(this)" data-object_guid="{{$_object->_guid}}">
	        <span onmouseover="ObjectTooltip.createEx(this, '{{$_object->_guid}}');">
					  {{$_object}}
					</span>
				</a>
			</td>
			{{foreach from=$columns item=_column}}
			  <td>
			  	{{mb_value object=$_object field=$_column}}
			  </td>
			{{/foreach}}
		</tr>
		{{foreachelse}}
		  {{if $count_children == 0}}
			<tr>
				{{math assign=colspan equation="x+1" x=$columns|@count}}
				
				<td class="empty" colspan="{{$colspan}}">
					<div style="{{if $tag->color}}border-right: 1em solid #{{$tag->color}};{{/if}}">
					  {{tr}}{{$tag->object_class}}.none{{/tr}}
					</div>
				</td>
			</tr>
			{{/if}}
		{{/foreach}}
		
  {{if $insertion == "after"}}
	  </tbody>
	{{/if}}
{{/if}}