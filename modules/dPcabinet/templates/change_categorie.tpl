{{*
  * Changement de la catégorie d'une consultation
  *  
  * @category dPcabinet
  * @package  Mediboard
  * @author   SARL OpenXtrem <dev@openxtrem.com>
  * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
  * @version  SVN: $Id:$ 
  * @link     http://www.mediboard.org
*}}

<form name="Edit-Categorie-Consultation" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);"> 
  {{mb_class object=$consult}}
  {{mb_key   object=$consult}}

  <div style="max-height: 400px; text-align: left; padding-left: 2em;">
  {{foreach from=$categories item=_categorie}}
    <div {{if $consult->categorie_id == $_categorie->_id}} style="font-weight: bold;" {{/if}} >
      <input name="categorie_id" value="{{$_categorie->_id}}" type="radio" 
        {{if $consult->categorie_id == $_categorie->_id}} checked="checked" {{/if}}
       />
      <label for="categorie_id_{{$_categorie->_id}}">
        {{mb_include module=cabinet template=inc_icone_categorie_consult
          categorie=$_categorie
          display_name=true
        }}
      </label>
    </div>
  {{/foreach}}  
  </div>

  <table class="form">
    <tr>
      <td class="button" colspan="2">
        <button class="save" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>

</form>
