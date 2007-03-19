{{assign var="sejour" value=$object}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      <a style="float:right;" href="#nothing" onclick="view_log('{{$object->_class_name}}', {{$object->_id}})">
        <img src="images/icons/history.gif" alt="historique" title="Voir l'historique" />
      </a>
      <a style="float:left;" href="#nothing"
        onmouseover="ObjectTooltip.create(this, '{{$object->_class_name}}', {{$object->_id}}, { mode: 'notes' })"
        onclick="new Note().create('{{$object->_class_name}}', {{$object->_id}});">
        <img src="images/icons/note_blue.png" alt="Ecrire une note" />
      </a>
      {{$object->_view}}
    </th>
  </tr>

  {{if $sejour->annule == 1}}
  <tr>
    <th class="category cancelled" colspan="4">
    SEJOUR ANNULE
    </th>
  </tr>
  {{/if}}
  
  <tr>
    <td>
      <strong>Etablissement :</strong>
      {{$object->_ref_group->_view}}
    </td>
    <td>
      <strong>Praticien :</strong>
      <i>{{$object->_ref_praticien->_view}}</i>
    </td>
  </tr>

  <tr>
    <td>
      <strong>Entr�e pr�vue :</strong>
      {{mb_value object=$sejour field="entree_prevue"}}
    </td>
    <td>
      <strong>Entr�e reelle :</strong>
      {{mb_value object=$sejour field="entree_reelle"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>Sortie pr�vue :</strong>
      {{mb_value object=$sejour field="sortie_prevue"}}
    </td>
    <td>
      <strong>Sortie reelle :</strong>
      {{mb_value object=$sejour field="sortie_reelle"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>Dur�e pr�vue :</strong>
      {{$sejour->_duree_prevue}} jour(s)
    </td>
    <td>
      <strong>Sortie reelle :</strong>
      {{$sejour->_duree_reelle}} jour(s)
    </td>
  </tr>
  
  {{if $object->rques}}
  <tr>
    <td class="text" colspan="2">
      <strong>Remarques :</strong>
      {{$object->rques|nl2br}}
    </td>
  </tr>
  {{/if}}

  {{if $object->convalescence}}
  <tr>
    <td class="text" colspan="2">
      <strong>Convalescence :</strong>
      {{$object->convalescence|nl2br}}
    </td>
  </tr>
  {{/if}}

  <tr>
    <th class="category" colspan="2">Hospitalisation</th>
  </tr>
  
  <tr>
    <td>
      <strong>Type d'admission</strong>
      {{mb_value object=$sejour field="type"}}
    </td>
    <td>
      <strong>Modalit�</strong>:
      {{mb_value object=$sejour field="modalite"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>ATNC :</strong>
      {{mb_value object=$sejour field="ATNC"}}
    </td>
    <td>
      <strong>Traitement hormonal :</strong>
      {{mb_value object=$sejour field="hormone_croissance"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>Chambre particuli�re :</strong>
      {{mb_value object=$sejour field="chambre_seule"}}
    </td>
    <td>
      <strong>Lit accompagnant :</strong>
      {{mb_value object=$sejour field="lit_accompagnant"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>Repas sans sel :</strong>
      {{mb_value object=$sejour field="repas_sans_sel"}}
    </td>
    <td>
      <strong>Isolement :</strong>
      {{mb_value object=$sejour field="isolement"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>Repas diab�tique :</strong>
      {{mb_value object=$sejour field="repas_diabete"}}
    </td>
    <td>
      <strong>T�l�vision :</strong>
      {{mb_value object=$sejour field="television"}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>Repas sans r�sidu :</strong>
      {{mb_value object=$sejour field="repas_sans_residu"}}
    </td>
  </tr>

</table>

{{include file="../../dPplanningOp/templates/inc_infos_operation.tpl"}}
{{include file="../../dPplanningOp/templates/inc_infos_hospitalisation.tpl"}}
