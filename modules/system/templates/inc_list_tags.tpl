{{*
 * $Id$
 *  
 * @category System
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
*}}

{{mb_include module=system template=inc_pagination current=$page step=$limit total=$total change_page=refreshTagList}}
<form name="merge_tags">
  <table class="tbl">
    <tr>
      {{if $tag->_can->edit && $tags|@count}}
        <th class="narrow"><button type="button" class="merge notext" onclick="doMerge(this.form);">{{tr}}Merge{{/tr}}</button></th>
      {{/if}}
      <th>{{mb_title object=$tag field=name}}</th>
      <th>{{mb_title object=$tag field=parent_id}}
      {{if $tag_parent->_id}}
        <a href="#" style="color:red; text-decoration: underline; display: inline" onclick="removeParent();">{{$tag_parent}}</a>
      {{/if}}</th>
      <th class="narrow">{{mb_title object=$tag field=_nb_items}}</th>
    </tr>
    {{foreach from=$tags item=_tag}}
      <tr>
        {{if $tag->_can->edit}}
          <td>
            <input type="checkbox" name="objects_id[]" value="{{$_tag->_id}}"/>
          </td>
        {{/if}}
        <td style="border-left:solid 10px {{if $_tag->color}}#{{$_tag->color}}{{else}}transparent{{/if}}">
          <a href="#{{$_tag->_id}}" onclick="editTag('{{$_tag->_id}}');">{{mb_value object=$_tag field=name}}</a>
        </td>
        <td style="vertical-align: middle">
          {{if $_tag->parent_id}}
            <img src="style/mediboard/images/buttons/search.png" alt="+" onclick="refreshTagList(null, '{{$_tag->parent_id}}');"/>
            <a href="#{{$_tag->parent_id}}" style="display: inline" onclick="editTag('{{$_tag->parent_id}}');">{{$_tag->_ref_parent}}</a>
          {{/if}}
        </td>
        <td {{if !$_tag->_nb_items}}class="empty"{{/if}}>
          {{$_tag->_nb_items}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="{{if $tag->_can->edit}}4{{else}}3{{/if}}" class="empty">{{tr}}CTag.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>
{{mb_include module=system template=inc_pagination current=$page step=$limit total=$total change_page=refreshTagList}}
