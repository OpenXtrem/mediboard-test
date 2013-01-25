<!-- Liste des �tiquettes filtr�es -->
<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      {{tr}}CModeleEtiquette.list{{/tr}}
    </th>
  </tr>
  
  <tr>
    <th class="category">{{tr}}CModeleEtiquette-nom{{/tr}}</th>
    <th class="category">{{tr}}CModeleEtiquette-object_class{{/tr}}</th>
    <th class="category">{{tr}}CModeleEtiquette.dimensions_page{{/tr}}</th>
    <th class="category">{{tr}}CModeleEtiquette.dimensions_etiq{{/tr}}</th>
    <th class="category">{{tr}}CModeleEtiquette.quantites_etiq{{/tr}}</th>
  </tr>
      
  {{foreach from=$liste_modele_etiquette item=_modele_etiq}}
    <tr id='modele_etiq-{{$_modele_etiq->_id}}' class="{{if $_modele_etiq->_id == $modele_etiquette_id}}selected{{/if}}">
      <td>
        <a href="#{{$_modele_etiq->_guid}}'" onclick="ModeleEtiquette.edit('{{$_modele_etiq->_id}}');">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_modele_etiq->_guid}}')">
           {{mb_value object=$_modele_etiq field=nom}}
          </span>
        </a>
      </td>
      <td>
        {{tr}}{{$_modele_etiq->object_class}}{{/tr}}
      </td>
      <td style="text-align: center;">
        {{$_modele_etiq->largeur_page}} cm x {{$_modele_etiq->hauteur_page}} cm
      </td>
      <td style="text-align: center;">
        {{$_modele_etiq->nb_lignes}} x {{$_modele_etiq->nb_colonnes}} =
        {{math equation="lignes*colonnes" lignes=$_modele_etiq->nb_lignes colonnes=$_modele_etiq->nb_colonnes}}
      </td>
      <td style="text-align: center;">
        {{$_modele_etiq->_width_etiq}} cm x {{$_modele_etiq->_height_etiq}} cm 
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">
      {{tr}}CModeleEtiquette.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>