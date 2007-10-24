<table class="main">
  <tr>
    <td class="halfPane">
      <a class="buttonnew" href="?m=dPmateriel&amp;tab=vw_idx_category&amp;category_id=0">
        Cr�er une nouvelle cat�gorie
      </a>
      <table class="tbl">
        <tr>
          <th>Cat�gorie</th>
        </tr>
        {{foreach from=$listCategory item=curr_category}}
        <tr {{if $curr_category->_id == $category->_id}}class="selected"{{/if}}>
          <td class="text">
            <a href="?m=dPmateriel&amp;tab=vw_idx_category&amp;category_id={{$curr_category->_id}}" title="Modifier le cat�gorie">
              {{$curr_category->category_name}}
            </a>
          </td>
        </tr>
        {{/foreach}}        
      </table>  
    </td>
    <td class="halfPane">
      <form name="editCat" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
      <input type="hidden" name="dosql" value="do_category_aed" />
	  <input type="hidden" name="category_id" value="{{$category->_id}}" />
      <input type="hidden" name="del" value="0" />
      <table class="form">
        <tr>
          {{if $category->_id}}
          <th class="title modify" colspan="2">Modification de la cat�gorie {{$category->_view}}</th>
          {{else}}
          <th class="title" colspan="2">Cr�ation d'une fiche</th>
          {{/if}}
        </tr> 
        <tr>
          <th>{{mb_label object=$category field="category_name"}}</th>
          <td>{{mb_field object=$category field="category_name"}}</td>
        </tr>
        <tr>
          <td class="button" colspan="2">
            <button class="submit" type="submit">Valider</button>
            {{if $category->_id}}
              <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'la cat�gorie',objName:'{{$category->_view|smarty:nodefaults|JSAttribute}}'})">Supprimer</button>
            {{/if}}
          </td>
        </tr>  
      </table>
      </form>
    </td>
  </tr>
</table>