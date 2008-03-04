<table class="main">
  <tr>
    <td class="halfPane">
      <a href="?m={{$m}}&amp;tab={{$tab}}&amp;category_id=0" class="buttonnew">
        Cr�er une cat�gorie
      </a>
      <table class="tbl">
        {{foreach from=$categories key=chapitre item=_categories}}
          <tr>
            <th colspan="1">
              {{tr}}CCategoryPrescription.chapitre.{{$chapitre}}{{/tr}}
            </th>
          </tr>
          {{foreach from=$_categories item=_cat}}
          <tr>
            <td>
              <a href="?m={{$m}}&amp;tab={{$tab}}&amp;category_id={{$_cat->_id}}">
                {{$_cat->nom}}
              </a>
            </td>
          </tr>
          {{/foreach}}
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">
      <form name="group" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
      <input type="hidden" name="dosql" value="do_category_prescription_aed" />
	    <input type="hidden" name="category_prescription_id" value="{{$category->_id}}" />
      <input type="hidden" name="del" value="0" />
      <table class="form">
        <tr>
          <th class="category" colspan="2">
          {{if $category->_id}}
            <div class="idsante400" id="CCategoryPrescription-{{$category->_id}}"></div>
            <a style="float:right;" href="#" onclick="view_log('CCategoryPrescription',{{$category->_id}})">
              <img src="images/icons/history.gif" alt="historique" />
            </a>
            Modification de la cat�gorie &lsquo;{{$category->nom}}&rsquo;
          {{else}}
            Cr�ation d'une cat�gorie
          {{/if}}
          </th>
        </tr>
        <tr>
          <th>{{mb_label object=$category field="chapitre"}}</th>
          <td>{{mb_field object=$category field="chapitre" defaultOption="&mdash; S�lection d'un chapitre"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$category field="nom"}}</th>
          <td>{{mb_field object=$category field="nom"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$category field="description"}}</th>
          <td>{{mb_field object=$category field="description"}}</td>
        </tr>
        <tr>
          <td class="button" colspan="2">
          {{if $category->_id}}
            <button class="modify" type="submit" name="modify">
              {{tr}}Modify{{/tr}}
            </button>
            <button class="trash" type="button" name="delete" onclick="confirmDeletion(this.form,{typeName:'la cat�gorie',objName:'{{$category->nom|smarty:nodefaults|JSAttribute}}'})">
              {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button class="new" type="submit" name="create">
              {{tr}}Create{{/tr}}
            </button>
          {{/if}}
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>