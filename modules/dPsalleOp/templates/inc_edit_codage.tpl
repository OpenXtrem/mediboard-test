{{*
 * $Id$
 *  
 * @package    Mediboard
 * @subpackage salleOp
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version    $Revision$
 * @link       http://www.mediboard.org
*}}

<style>
  #test a {
    float: none!important;
  }
</style>

<script type="text/javascript">
  Main.add(function() {
    Control.Tabs.create('rules-tab_codage-{{$codage->_id}}', true);
  });
</script>

<table class="tbl">
  <tr>
    <th class="narrow">{{mb_title class=CActeCCAM field=code_activite}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=_tarif_base}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=facturable}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=code_association}}</th>
    <th>{{mb_title class=CActeCCAM field=modificateurs}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=extension_documentaire}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=_tarif}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=execution}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=montant_depassement}}</th>
    <th class="narrow">{{mb_title class=CActeCCAM field=motif_depassement}}</th>
    <th colspan="2">Actions</th>
  </tr>

  {{assign var=count_codes_codage value=0}}
  {{foreach from=$subject->_ext_codes_ccam item=_code key=_key}}
    {{assign var=display_code value=1}}
    {{foreach from=$_code->activites item=_activite}}
      {{assign var="numero" value=$_activite->numero}}
      {{foreach from=$_activite->phases item=_phase}}
        {{assign var="acte" value=$_phase->_connected_acte}}
        {{assign var="view" value=$acte->_id|default:$acte->_view}}
        {{assign var="key" value="$_key$view"}}
        {{if (!$acte->_id || ($acte->executant_id == $codage->praticien_id && $acte->_id|@array_key_exists:$codage->_ref_actes_ccam)) &&
             (($_activite->numero != '4' && !$codage->activite_anesth) || ($_activite->numero == '4' && $codage->activite_anesth))}}
          {{math assign=count_codes_codage equation="x+1" x=$count_codes_codage}}

          <script type="application/javascript">
            Main.add(function() {
              var dates = {};
              dates.limit = {
                start: '{{$codage->date|date_format:"%Y-%m-%d"}}',
                stop: '{{$codage->date|date_format:"%Y-%m-%d"}}'
              };

              var oForm = getForm("codageActeExecution-{{$view}}");
              if (oForm) {
                Calendar.regField(oForm.execution, dates);
              }
            });
          </script>

          {{if $display_code}}
            {{assign var=display_code value=0}}
            <tr>
              <th colspan="14" style="text-align: left;">
                <span onclick="CodeCCAM.show('{{$_code->code}}', '{{$subject->_class}}')"
                      style="cursor: pointer;{{if $_code->type == 2}} color: #444;{{/if}}">
                  {{$_code->code}} : {{$_code->libelleLong}}
                </span>
                {{if $_code->forfait}}
                  <small style="color: #f00">({{tr}}CDatedCodeCCAM.remboursement.{{$_code->forfait}}{{/tr}})</small>
                {{/if}}
              </th>
            </tr>
          {{/if}}
          <tr {{if !$acte->_id}}class="activite-{{$acte->code_activite}}"{{/if}}>
            <td class="narrow">
              <span class="circled {{if $acte->_id}}ok{{else}}error{{/if}}">
                {{mb_value object=$acte field=code_activite}}-{{mb_value object=$acte field=code_phase}}
              </span>
            </td>
            <td>
              {{mb_value object=$acte field=_tarif_base}}
              {{if $acte->_tarif_base != $acte->_tarif_base2}}
                ({{mb_value object=$acte field=_tarif_base2}})
              {{/if}}
            </td>
            <td>
              <form name="codageActeFacturable-{{$view}}" action="?" method="post" onsubmit="return false;">
                {{mb_field object=$acte field=facturable typeEnum="select" onchange="syncCodageField(this, '$view');"}}
              </form>
            </td>
            <td
              {{if $acte->_id && ($acte->code_association != $acte->_guess_association)}}style="background-color: #fc9"{{/if}}>
              {{if $acte->_id}}
                <form name="codageActeCodeAssociation-{{$view}}" action="?" method="post" onsubmit="return false;">
                  {{mb_field object=$acte field=code_association emptyLabel="CActeCCAM.code_association." onchange="syncCodageField(this, '$view');"}}
                </form>
                {{if $acte->code_association != $acte->_guess_association}}
                  ({{$acte->_guess_association}})
                {{/if}}
              {{/if}}
            </td>
            <td class="greedyPane{{if !$_phase->_modificateurs|@count}} empty{{/if}}">
              {{assign var=nb_modificateurs value=$acte->modificateurs|strlen}}
              {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                <span class="circled {{if $_mod->_state == 'prechecked'}}ok{{elseif $_mod->_checked && in_array($_mod->_state, array('not_recommended', 'forbidden'))}}error{{elseif in_array($_mod->_state, array('not_recommended', 'forbidden'))}}warning{{/if}}"
                      title="{{$_mod->libelle}} ({{$_mod->_montant}})">
                  <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}"
                         {{if $_mod->_checked}}checked="checked"{{elseif $nb_modificateurs == 4 || $_mod->_state == 'forbidden' || (intval($acte->_exclusive_modifiers) > 0 && in_array($_mod->code, array('F', 'U', 'P', 'S'))) || !$acte->facturable}}disabled="disabled"{{/if}}
                         data-acte="{{$view}}" data-code="{{$_mod->code}}" data-double="{{$_mod->_double}}" class="modificateur" onchange="syncCodageField(this, '{{$view}}');" />
                  <label for="modificateur_{{$_mod->code}}{{$_mod->_double}}">
                    {{$_mod->code}}
                  </label>
                </span>

                {{foreachelse}}
                <em>{{tr}}None{{/tr}}</em>
              {{/foreach}}
            </td>
            <td class="narrow">
              {{if $acte->code_activite == 4}}
                <form name="codageActeExtDoc-{{$view}}" action="?" method="post" onsubmit="return false;">
                  {{mb_field object=$acte field=extension_documentaire emptyLabel="CActeCCAM.extension_documentaire." onchange="syncCodageField(this, '$view');" style="width: 13em;"}}
                </form>
              {{/if}}
            </td>
            <td class="narrow" style="text-align: right;{{if $acte->_id && !$acte->facturable}} background-color: #fc9;{{/if}}">
              {{mb_value object=$acte field=_tarif}}
            </td>
            <td>
              <form name="codageActeExecution-{{$view}}" action="?" method="post" onsubmit="return false;">
                {{mb_field object=$acte field=execution form="codageActeExecution-$view" onchange="syncCodageField(this, '$view');"}}
              </form>
            </td>
            <td>
              <form name="codageActeMontantDepassement-{{$view}}" action="?" method="post" onsubmit="return false;">
                {{mb_field object=$acte field=montant_depassement onchange="syncCodageField(this, '$view');" size=4}}
              </form>
            </td>
            <td>
              <form name="codageActeMotifDepassement-{{$view}}" action="?" method="post" onsubmit="return false;">
                {{mb_field object=$acte field=motif_depassement emptyLabel="CActeCCAM-motif_depassement" onchange="syncCodageField(this, '$view');" style="width: 13em;"}}
              </form>
            </td>
            <td>
              <form name="codageActe-{{$view}}" action="?" method="post"
                    onsubmit="return onSubmitFormAjax(this, {onComplete: function() {window.urlCodage.refreshModal()}});">
                <input type="hidden" name="m" value="salleOp" />
                <input type="hidden" name="dosql" value="do_acteccam_aed" />
                <input type="hidden" name="del" value="0" />
                {{mb_key object=$acte}}

                <input type="hidden" name="_calcul_montant_base" value="1" />
                <input type="hidden" name="_edit_modificateurs" value="1"/>

                {{mb_field object=$acte field=object_id hidden=true value=$subject->_id}}
                {{mb_field object=$acte field=object_class hidden=true value=$subject->_class}}
                {{mb_field object=$acte field=code_acte hidden=true}}
                {{mb_field object=$acte field=code_activite hidden=true}}
                {{mb_field object=$acte field=code_phase hidden=true}}
                {{mb_field object=$acte field=code_association hidden=true emptyLabel="None"}}
                {{mb_field object=$acte field=executant_id hidden=true value=$codage->praticien_id}}
                {{mb_field object=$acte field=execution hidden=true}}
                {{mb_field object=$acte field=montant_depassement hidden=true}}
                {{mb_field object=$acte field=motif_depassement hidden=true emptyLabel="CActeCCAM-motif_depassement"}}
                {{mb_field object=$acte field=facturable hidden=true}}
                {{mb_field object=$acte field=extension_documentaire hidden=true}}
                {{mb_field object=$acte field=rembourse hidden=true}}

                {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                  <input type="checkbox" name="modificateur_{{$_mod->code}}{{$_mod->_double}}" {{if $_mod->_checked}}checked="checked"{{/if}} hidden="hidden" />
                {{/foreach}}

                {{if !$acte->_id}}
                  <button class="add notext compact" type="submit" {{if $_activite->anesth_comp && !$_activite->anesth_comp|in_array:$subject->_codes_ccam}}
                          onclick="addActeAnesthComp('{{$_activite->anesth_comp}}', {{'dPccam CCodable add_acte_comp_anesth_auto'|conf}});"{{/if}}>
                    {{tr}}Add{{/tr}}
                  </button>
                {{else}}
                  {{if $codage->codable_class == 'CSejour'}}
                    <button type="button" class="notext copy compact" onclick="duplicateCodage({{$codage->_id}}, {{$acte->_id}});" title="{{tr}}CCodageCCAM-action-duplicate{{/tr}}">
                      {{tr}}CCodageCCAM-action-duplicate{{/tr}}
                    </button>
                  {{/if}}
                  <button class="edit notext compact" type="button" onclick="ActesCCAM.edit({{$acte->_id}})">{{tr}}Edit{{/tr}}</button>
                  <button class="remove notext compact" type="button"
                          onclick="confirmDeletion(this.form,{typeName:'l\'acte',objName:'{{$acte->_view|smarty:nodefaults|JSAttribute}}', ajax: '1'},
                            {onComplete: function() {window.urlCodage.refreshModal()}});">
                    {{tr}}Remove{{/tr}}
                  </button>
                {{/if}}
              </form>
            </td>
            <td class="narrow">
              {{mb_include module=system template=inc_object_history object=$acte}}
            </td>
          </tr>
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
  {{if !$count_codes_codage}}
    <tr>
      <td colspan="12" class="empty">
        {{tr}}CActeCCAM.none{{/tr}}
      </td>
    </tr>
  {{else}}
    <tr>
      <th class="category" colspan="6" style="text-align: right;">
        Montant total
      </th>
      <th class="category" colspan="6" style="text-align: left;">
        {{mb_value object=$codage field=_total}}
      </th>
    </tr>
  {{/if}}
</table>

<br style="margin: 10px;"/>

<ul id="rules-tab_codage-{{$codage->_id}}" class="control_tabs">
  <li><a href="#questionRules_codage-{{$codage->_id}}">Informations m�dicales</a></li>
  <li><a href="#concreteRules_codage-{{$codage->_id}}">R�gles de codage</a></li>
  <li>
    <input type="checkbox" name="_association_mode" value="manuel"
           {{if $codage->association_mode == "user_choice"}}checked="checked"{{/if}}
           onchange="changeCodageMode(this, {{$codage->_id}});"/>
    Mode manuel pour les r�gles d'association
  </li>
</ul>

<hr class="control_tabs" />

<div style="display: none;" id="questionRules_codage-{{$codage->_id}}">
  <form name="questionRulesForm_codage-{{$codage->_id}}" action="?" method="post" onsubmit="return false;">
    <table class="tbl">
      <tr>
        <th class="title" colspan="2">Les actes que vous codez r�pondent-ils � un des crit�res suivants ?</th>
      </tr>
      <tr>
        <th class="category" colspan="2">Pour les interventions chirurgicales</th>
      </tr>
      {{if isset($codage->_possible_rules.EA|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.EA}}ok{{/if}}">
            <input type="radio" name="_association_question" value="EA"
                   {{if $codage->association_rule == "EA"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            Les actes portent sur :
            <ul>
              <li><strong>des membres diff�rents ou</strong></li>
              <li><strong>le tronc et un membre ou</strong></li>
              <li><strong>la t�te et un membre.</strong></li>
            </ul>
          </td>
        </tr>
      {{/if}}
      {{if isset($codage->_possible_rules.EB|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.EB}}ok{{/if}}">
            <input type="radio" name="_association_question" value="EB"
                   {{if $codage->association_rule == "EB"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            Les actes visent � traiter des <strong>l�sions traumatiques multiples et r�centes</strong>
          </td>
        </tr>
      {{/if}}
      {{if isset($codage->_possible_rules.EC|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.EC}}ok{{/if}}">
            <input type="radio" name="_association_question" value="EC"
                   {{if $codage->association_rule == "EC"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            Les actes d�crivent une intervention de <strong>carcinologie ORL</strong> comprenant :
            <ul>
              <li>une ex�r�se et</li>
              <li>un curage et</li>
              <li>une reconstruction.</li>
            </ul>
          </td>
        </tr>
      {{/if}}
      {{if isset($codage->_possible_rules.EH|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.EH}}ok{{/if}}">
            <input type="radio" name="_association_question" value="EH"
                   {{if $codage->association_rule == "EH"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            <strong>Des actes ont pr�c�demment �t� cod�s pour ce patient dans cette journ�e</strong> et les nouveaux actes
            sont effectu�s dans un <strong>temps diff�rent et discontinu</strong> des premiers.
          </td>
        </tr>
      {{/if}}
      <tr>
        <th class="category" colspan="2">Pour les actes d'imagerie</th>
      </tr>
      {{if isset($codage->_possible_rules.ED|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.ED}}ok{{/if}}">
            <input type="radio" name="_association_question" value="ED"
                   {{if $codage->association_rule == "ED"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            Les actes sont des actes d'<strong>�chographie</strong> portant sur <strong>plusieurs r�gions anatomiques</strong>.
          </td>
        </tr>
      {{/if}}
      {{if isset($codage->_possible_rules.EE|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.EE}}ok{{/if}}">
            <input type="radio" name="_association_question" value="EE"
                   {{if $codage->association_rule == "EE"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            Les actes sont des actes d'<strong>�lectromyographie</strong>, de <strong>mesure des vitesses de conduction</strong>, d'<strong>�tude des latences et des r�flexes</strong> portant sur <strong>plusieurs r�gions anatomiques</strong>.
          </td>
        </tr>
      {{/if}}
      {{if isset($codage->_possible_rules.EF|smarty:nodefaults)}}
        <tr>
          <th class="narrow {{if $codage->_possible_rules.EF}}ok{{/if}}">
            <input type="radio" name="_association_question" value="EF"
                   {{if $codage->association_rule == "EF"}}checked="checked"{{/if}}
                   onchange="setRule(this, {{$codage->_id}});"/>
          </th>
          <td>
            Les actes sont des actes de <strong>scanographie</strong> portant sur <strong>plusieurs r�gions anatomiques</strong>.
          </td>
        </tr>
      {{/if}}
    </table>
  </form>
</div>
<div style="display: none;" id="concreteRules_codage-{{$codage->_id}}">
  <form name="formCodageRules_codage-{{$codage->_id}}" action="?" method="post"
        onsubmit="return onSubmitFormAjax(this, {onComplete: function() {window.urlCodage.refreshModal()}});">
    <input type="hidden" name="m" value="ccam" />
    <input type="hidden" name="dosql" value="do_codageccam_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="codage_ccam_id" value="{{$codage->_id}}" />
    <input type="hidden" name="association_mode" value="{{$codage->association_mode}}" />
    <table class="tbl">
      <tr>
        <th class="title" colspan="20">
          R�gles d'association
        </th>
      </tr>
      {{assign var=association_rules value="CCodageCCAM"|static:"association_rules"}}
      {{foreach from=$codage->_possible_rules key=_rulename item=_rule}}
        {{if $_rule || 1}}
          <tr>
            <th class="narrow {{if $_rulename == $codage->association_rule}}ok{{/if}}">
              <input type="radio" name="association_rule" value="{{$_rulename}}"
                     {{if $_rulename == $codage->association_rule}}checked="checked"{{/if}}
                {{if $codage->association_mode == "auto"}}disabled="disabled"{{/if}}
                     onchange="this.form.onsubmit()"/>
            </th>
            <td class="{{if $_rule}}ok{{else}}error{{/if}}">
              {{$_rulename}} {{if $association_rules.$_rulename == 'ask'}}(manuel){{/if}}
            </td>
            <td class="text greedyPane">
              {{tr}}CActeCCAM-regle-association-{{$_rulename}}{{/tr}}
            </td>
          </tr>
        {{/if}}
      {{/foreach}}
    </table>
  </form>
</div>