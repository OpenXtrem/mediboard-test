{{if !$conf.dPccam.CCodeCCAM.use_cotation_ccam}}
  {{mb_return}}
{{/if}}
<script>
  Main.add(function(){
    Calendar.regField(getForm('facture_date-'+'{{$facture->_guid}}').ouverture);
  });
</script>
<tr>
  <td colspan="2" style="text-align: center;"><b>{{mb_label object=$facture field=ouverture}}</b></td>
  <td>
    <form name="facture_date-{{$facture->_guid}}" method="post" action="" onsubmit="return editDateFacture(this);">
      {{mb_key object=$facture}}
      {{mb_class object=$facture}}
      <input type="hidden" name="cloture" value=""/>
      {{mb_field object=$facture field="ouverture" class="date notNull"}}
      <button class="save notext" type="submit"></button>
    </form>
  </td>
  <td colspan="3">
    {{if $facture->_is_relancable && $conf.dPfacturation.CRelance.use_relances}}
      <form name="facture_relance" method="post" action="" onsubmit="return Relance.create(this);">
        {{mb_class object=$facture->_ref_last_relance}}
        <input type="hidden" name="relance_id" value=""/>
        <input type="hidden" name="object_id" value="{{$facture->_id}}"/>
        <input type="hidden" name="object_class" value="{{$facture->_class}}"/>
        <button class="add" type="submit">Cr�er une relance</button>
      </form>
    {{/if}}
    {{if $facture->cloture}}
      <button type="button" class="pdf" onclick="printFactureFR('{{$facture->_id}}', '{{$facture->_class}}');" style="float:left;">Facture pdf</button>
    {{/if}}
    {{if $can->admin}}
      <form name="delete_facture" method="post" action="" style="float:right">
        {{mb_key object=$facture}}
        {{mb_class object=$facture}}
        <input type="hidden" name="del" value="1"/>
        <button class="cancel notext" type="reset" title="Supprimer la facture" onclick="return confirmDeletion(this.form,{typeName:'la facture'});"></button>
      </form>
    {{/if}}
  </td>
</tr>

<tr>
  <th class="category narrow">Date</th>
  <th class="category">Code</th>
  <th class="category">Libelle</th>
  <th class="category narrow">Base</th>
  <th class="category narrow">DH</th>
  <th class="category narrow">Montant</th>
</tr>

{{if $facture->_ref_items|@count}}
  {{foreach from=$facture->_ref_items item=item}}
    <tr>
      <td style="text-align:center;width:100px;">
        {{if $facture->_ref_last_sejour->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_last_sejour->_guid}}')">
        {{else}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_last_consult->_guid}}')">
        {{/if}}
        {{mb_value object=$item field="date"}}
        </span>
      </td>
      <td class="acte-{{$item->type}}" style="width:140px;">{{mb_value object=$item field="code"}}</td>
      <td style="white-space: pre-line;" class="compact">{{mb_value object=$item field="libelle"}}</td>
      <td style="text-align:right;">{{mb_value object=$item field="montant_base"}}</td>
      <td style="text-align:right;">{{mb_value object=$item field="montant_depassement"}}</td>
      <td style="text-align:right;">{{$item->montant_base + $item->montant_depassement|string_format:"%0.2f"|currency}}</td>
    </tr>
  {{/foreach}}
{{else}}
  {{foreach from=$facture->_ref_actes_ccam item=_acte_ccam}}
    <tr>
      <td>{{$_acte_ccam->execution|date_format:"%d/%m/%Y"}}</td>
      <td class="acte-{{$_acte_ccam->_class}}">{{$_acte_ccam->code_acte}}</td>
      <td>{{$_acte_ccam->_ref_code_ccam->libelleLong|truncate:70:"...":true}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_ccam field=montant_base}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_ccam field=montant_depassement}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_ccam field=_montant_facture}}</td>
    </tr>
  {{/foreach}}
  
  {{foreach from=$facture->_ref_actes_ngap item=_acte_ngap}}
    <tr>
      <td></td>
      <td class="acte-{{$_acte_ngap->_class}}">{{$_acte_ngap->code}}</td>
      <td>{{$_acte_ngap->_libelle}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_ngap field="montant_base"}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_ngap field="montant_depassement"}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_ngap field=_montant_facture}}</td>
    </tr>
  {{/foreach}}
  {{foreach from=$facture->_ref_actes_divers item=_acte_divers}}
    <tr>
      <td></td>
      <td class="acte-{{$_acte_divers->_class}}">{{$_acte_divers->_ref_type->code}}</td>
      <td>{{$_acte_divers->_ref_type->libelle}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_divers field="montant_base"}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_divers field="montant_depassement"}}</td>
      <td style="text-align: right;">{{mb_value object=$_acte_divers field=_montant_facture}}</td>
    </tr>
  {{/foreach}}
{{/if}}

<tbody class="hoverable">
  <tr>
    <td colspan="3" rowspan="4"></td>
    <td colspan="2">D� Patient</td>
    <td style="text-align:right;">{{mb_value object=$facture field="du_patient"}}</td>
  </tr>
  <tr>
    <td colspan="2">D� Tiers</td>
    <td style="text-align:right;">{{mb_value object=$facture field="du_tiers"}}</td>
  </tr>
  <tr>
    <td colspan="2"><i>Dont TVA ({{$facture->taux_tva}}%)</i></td>
    <td style="text-align:right;"><i>{{mb_value object=$facture field="du_tva"}}</i></td>
  <tr>
  <tr>
    <td colspan="3">
      {{if $facture->numero == 1}}
        <button class="edit notext" style="float:right;" onclick="editRepartition('{{$facture->_id}}', '{{$facture->_class}}');">Modifier la r�partition D� patient/D� tiers</button>
      {{/if}}
    </td>
    <td colspan="2"><b>Montant Total</b></td>
    <td style="text-align:right;"><b>{{mb_value object=$facture field="_montant_avec_remise"}}</b></td>
  <tr>
  {{assign var="classe" value=$facture->_class}}
  {{if !$facture->_reglements_total_patient && !$conf.dPfacturation.$classe.use_auto_cloture}}
    <tr>
      <td colspan="7">
        <form name="change_type_facture" method="post">
          {{mb_class object=$facture}}
          {{mb_key   object=$facture}}
          <input type="hidden" name="facture_class" value="{{$facture->_class}}" />
          <input type="hidden" name="cloture"       value="{{if !$facture->cloture}}{{$date}}{{/if}}" />
          <input type="hidden" name="not_load_banque" value="{{if isset($factures|smarty:nodefaults) && count($factures)}}0{{else}}1{{/if}}" />
          {{if !$facture->cloture}}
            <button class="submit" type="button" onclick="Facture.modifCloture(this.form);" >Cloturer la facture</button>
          {{else}}
            <button class="submit" type="button" onclick="Facture.modifCloture(this.form);" >R�ouvrir la facture</button> Clotur�e le {{$facture->cloture|date_format:"%d/%m/%Y"}}
          {{/if}}
        </form>
      </td>
    </tr>
  {{/if}}
</tbody>