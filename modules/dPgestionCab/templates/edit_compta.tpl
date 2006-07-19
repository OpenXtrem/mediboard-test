<script type="text/javascript">
function printRapport() {
  var form = document.selectFrm;

  var url = new Url;
  url.setModuleAction("dPgestionCab", "print_rapport");
  url.addElement(form.date);
  url.addElement(form.datefin);
  url.addElement(form.libelle);
  url.addElement(form.rubrique_id);
  url.addElement(form.mode_paiement_id);
  url.popup(700, 550, "Rapport");
}

function pageMain() {
  regFieldCalendar("editFrm", "date");
  regFieldCalendar("selectFrm", "date");
  regFieldCalendar("selectFrm", "datefin");
}

</script>

<table class="main">
  <tr>
    <td class="halfpane">
      <form name="editFrm" action="index.php?m={{$m}}" method="post" onsubmit="return checkForm(this)">
      <input type="hidden" name="dosql" value="do_gestioncab_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="gestioncab_id" value="{{$gestioncab->gestioncab_id}}" />
      <input type="hidden" name="function_id" value="{{$gestioncab->function_id}}" />
      {{if $gestioncab->gestioncab_id}}
      <a class="buttonnew" href="index.php?m={{$m}}&gestioncab_id=0">Cr�er une nouvelle fiche</a>
      {{/if}}
      <table class="form">
        <tr>
          {{if $gestioncab->gestioncab_id}}
          <th class="title" colspan="4" style="color: #f00;">
            <a style="float:right;" href="javascript:view_log('CGestionCab',{{$gestioncab->gestioncab_id}})">
              <img src="images/history.gif" alt="historique" />
            </a>
            Modification de la fiche {{$gestioncab->_view}}
          </th>
          {{else}}
          <th class="title" colspan="4">Cr�ation d'une nouvelle fiche</th>
          {{/if}}
        </tr>
        <tr>
          <th><label for="libelle" title="Libell� de la fiche">Libell�</label></th>
          <td><input name="libelle" value="{{$gestioncab->libelle}}" title="{{$gestioncab->_props.libelle}}" /></td>
          <th><label for="date" title="Date de paiement de la fiche">Date</label></th>
          <td class="date">
            <div id="editFrm_date_da">
              {{$gestioncab->date|date_format:"%d/%m/%Y"}}
            </div>
            <input type="hidden" name="date" title="{{$gestioncab->_props.date}}|notNull" value="{{$gestioncab->date}}" />
            <img id="editFrm_date_trigger" src="./images/calendar.gif" alt="calendar"/>
          </td>
        </tr>
        <tr>
          <th><label for="rubrique_id" title="Rubrique concern�e">Rubrique</label></th>
          <td>
            <select name="rubrique_id">
            {{foreach from=$listRubriques item=rubrique}}
              <option value="{{$rubrique->rubrique_id}}" {{if $rubrique->rubrique_id == $gestioncab->rubrique_id}}selected="selected"{{/if}}>
                {{$rubrique->nom}}
              </option>
            {{/foreach}}
            </select>
          </td>
          <th><label for="montant" title="Montant de la fiche, utilisez un point (.) pour les centimes">Montant</label></th>
          <td><input name="montant" value="{{$gestioncab->montant}}" title="{{$gestioncab->_props.montant}}" /> �</td>
        </tr>
        <tr>
          <th><label for="mode_paiement_id" title="Mode de paiement de la fiche">Mode de paiement</label></th>
          <td>
            <select name="mode_paiement_id">
            {{foreach from=$listModesPaiement item=mode}}
              <option value="{{$mode->mode_paiement_id}}" {{if $mode->mode_paiement_id == $gestioncab->mode_paiement_id}}selected="selected"{{/if}}>
                {{$mode->nom}}
              </option>
            {{/foreach}}
            </select>
          </td>
          <th rowspan="2"><label for="rques" title="Remarques concernant la fiche">Remarques</label></th>
          <td rowspan="2">
            <textarea name="rques" title="{{$gestioncab->_props.rques}}">{{$gestioncab->rques}}</textarea>
          </td>
        </tr>
        <tr>
          <th><label for="num_facture" title="Numero de la facture">Numero de facture</label></th>
          <td>
            <input name="num_facture" size="5" value="{{$gestioncab->num_facture}}" title="{{$gestioncab->_props.num_facture}}" />
          </td>
        </tr>
        <tr>
          <td class="button" colspan="5">
            {{if $gestioncab->gestioncab_id}}
            <button class="modify" type="submit">Modifier</button>
            <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'la fiche',objName:'{{$gestioncab->_view|escape:javascript}}'})">
              Supprimer
            </button>
            {{else}}
            <button class="submit" type="submit">Cr�er</button>
            {{/if}}
          </td>
        </tr>
      </table>
      </form>
    </td>
    <td class="halfpane">
      <form name="selectFrm" action="index.php" method="get" onSubmit="return checkForm(this)">
      <input type="hidden" name="m" value="{{$m}}" />
      <table class="tbl">
        <tr>
          <th class="title" colspan="5">Recherche de fiches</th>
        </tr>
        <tr>
          <td style="text-align: right; vertical-align: middle;"><label for="date" title="Date de d�but de la recherche">Depuis le</label></td>
          <td class="date" colspan="3">
            <div id="selectFrm_date_da">
              {{$date|date_format:"%d/%m/%Y"}}
            </div>
            <input type="hidden" title="date|notNull" name="date" value="{{$date}}" />
            <img id="selectFrm_date_trigger" src="./images/calendar.gif" alt="calendar" />
          </td>
          <td class="button">
            <button class="print" type="button" onclick="printRapport()">Imprimer</button>
          </td>
        </tr>
        <tr>
          <td style="text-align: right; vertical-align: middle;"><label for="datefin" title="Date de fin de la recherche">Jusqu'au</label></td>
          <td class="date" colspan="3">
            <div id="selectFrm_datefin_da">
              {{$datefin|date_format:"%d/%m/%Y"}}
            </div>
            <input type="hidden" name="datefin" title="date|moreEquals|date|notNull" value="{{$datefin}}" />
            <img id="selectFrm_datefin_trigger" src="./images/calendar.gif" alt="calendar" />
          </td>
          <td class="button">
            <button type="submit" class="search">Afficher</button>
          </td>
        </tr>
        <tr>
          <th class="category">Date</th>
          <th class="category">
            Libell�<br />
            <input type="text" name="libelle" value="{{$libelle}}" size="8" />
          </th>
          <th class="category">
            Rubrique<br />
            <select name="rubrique_id">
              <option value="0">&mdash; Toutes</option>
              {{foreach from=$listRubriques item=rubrique}}
              <option value="{{$rubrique->rubrique_id}}" {{if $rubrique->rubrique_id == $rubrique_id}}selected="selected"{{/if}}>
                {{$rubrique->nom}}
              </option>
              {{/foreach}}
            </select>
          </th>
          <th class="category">
            Mode de paiement<br />
            <select name="mode_paiement_id">
              <option value="0">&mdash; Tous</option>
              {{foreach from=$listModesPaiement item=mode}}
              <option value="{{$mode->mode_paiement_id}}" {{if $mode->mode_paiement_id == $mode_paiement_id}}selected="selected"{{/if}}>
                {{$mode->nom}}
              </option>
              {{/foreach}}
            </select>
          </th>
          <th class="category">Montant</th>
        </tr>
        {{foreach from=$listGestionCab item=fiche}}
        <tr>
          <td>
            <a href="index.php?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{$fiche->date|date_format:"%d/%m/%Y"}}
            </a>
          </td>
          <td>
            <a href="index.php?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{$fiche->libelle}}
            </a>
          </td>
          <td>
            <a href="index.php?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{$fiche->_ref_rubrique->nom}}
            </a>
          </td>
          <td>
            <a href="index.php?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{$fiche->_ref_mode_paiement->nom}}
            </a>
          </td>
          <td>
            <a href="index.php?m={{$m}}&gestioncab_id={{$fiche->gestioncab_id}}">
            {{$fiche->montant}} �
            </a>
          </td>
        </tr>
        {{/foreach}}
      </table>
      </form>
    </td>
  </tr>
</table>