<script language="Javascript" type="text/javascript">
function checkPlage() {
  var form = document.editFrm;
  
  if (!checkForm(form)) {
    return false;
   }
    
  if (form.chir_id.value == "" && form.spec_id.value == "") {
    alert("Merci de choisir un chirurgien ou une sp�cialit�");
    form.chir_id.focus();
    return false;
  }
  
  if (form._heurefin.value < form._heuredeb.value || (form._heurefin.value == form._heuredeb.value && form._minutefin.value <= form._minutedeb.value)) {
    alert("L'heure de d�but doit �tre sup�rieure � la l'heure de fin");
    form._heurefin.focus();
    return false;
  }
  
  return true;
}

Main.add(function () {
  regRedirectFlatCal("{{$date}}", "?m={{$m}}&tab={{$tab}}&date=");
});

function popPlanning(debut) {
  var url = new Url;
  url.setModuleAction("dPbloc", "view_planning");
  url.addParam("_date_min", debut);
  url.addParam("_date_max", debut);
  url.addParam("salle"    , 0);
  url.popup(900, 550, "Planning");
}
</script>
<table class="main">
  <tr>
    <td class="greedyPane" style="text-align:center;">
      <a href="#" onclick="popPlanning('{{$date}}');">
        <strong>{{$date|date_format:"%A %d %B"}}</strong><br />
        <img src="images/icons/print.png" height="15" width="15" alt="imprimer" border="0" />
      </a>
      <table id="planningBloc">
      {{assign var=curr_day value=$date}}
      {{include file="inc_planning_day.tpl"}}
      </table>
      {{if $plagesel->plageop_id}}
      <a class="buttonnew" href="?m=dPbloc&amp;tab=vw_edit_planning&amp;plageop_id=0">
        Cr�er une nouvelle plage op�ratoire
      </a>
      {{/if}}
      {{if $can->edit}}
      <form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return checkPlage()">
      <input type="hidden" name="dosql" value="do_plagesop_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="plageop_id" value="{{$plagesel->plageop_id}}" />

      <table class="form">
        <tr>
          {{if $plagesel->plageop_id}}
          <th class="category modify" colspan="6">
            <a style="float:right;" href="#" onclick="view_log('CPlageOp',{{$plagesel->plageop_id}})">
              <img src="images/icons/history.gif" alt="historique" />
            </a>
            Modifier la plage op�ratoire
          {{else}}
          <th class="category" colspan="6">
            Ajouter une plage op�ratoire
          {{/if}}
          </th>
        </tr>
        <tr>
         <th>{{mb_label object=$plagesel field="chir_id"}}</th>
         <td>
          <select name="chir_id" class="{{$plagesel->_props.chir_id}}">
            <option value="">&mdash; Choisir un chirurgien</option>
            {{foreach from=$specs item=currFct key=keyFct}}
            <optgroup label="{{$currFct->_view}}">
              {{foreach from=$currFct->_ref_users item=currUser}}
              <option class="mediuser" style="border-color: #{{$currFct->color}};" value="{{$currUser->user_id}}" 
              {{if $plagesel->chir_id == $currUser->user_id}}selected="selected"{{/if}}>
                {{$currUser->_view}}
              </option>
              {{/foreach}}
            </optgroup>
            {{/foreach}}
          </select>
        </td>
        <th>{{mb_label object=$plagesel field="salle_id"}}</th>
        <td>
          <select name="salle_id" class="{{$plagesel->_props.salle_id}}">
            <option value="">&mdash; Choisir une salle</option>
            {{foreach from=$listSalles item=salle}}
            <option value="{{$salle->salle_id}}" {{if $plagesel->salle_id == $salle->salle_id}} selected="selected"{{/if}} >
              {{$salle->nom}}
            </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$plagesel field="spec_id"}}</th>
        <td>
          <select name="spec_id" class="{{$plagesel->_props.spec_id}}">
            <option value="">&mdash; Choisir une sp�cialit�</option>
            {{foreach from=$specs item=spec}}
              <option value="{{$spec->function_id}}" class="mediuser" style="border-color: #{{$spec->color}};"
              {{if $spec->function_id == $plagesel->spec_id}}selected="selected"{{/if}}>
                {{$spec->text}}
              </option>
            {{/foreach}}
          </select>
        </td>
        <th>{{mb_label object=$plagesel field="date"}}</th>
        <td class="date">
          {{if $plagesel->plageop_id}}
          <div id="editFrm_date_da">{{$plagesel->date|date_format:"%d/%m/%Y"}}</div>
          <input type="hidden" name="date" value="{{$plagesel->date}}" />
          {{else}}
          <div id="editFrm_date_da">{{$date|date_format:"%d/%m/%Y"}}</div>
          <input type="hidden" name="date" value="{{$date}}" />
          {{/if}}
          <!-- Possibilit� de changer la date ? -->
          <!--img id="editFrm_date_trigger" src="./images/icons/calendar.gif" alt="calendar" title="Choisir une date"/-->
        </td>
      </tr>
      
      <tr>
        <th>{{mb_label object=$plagesel field="anesth_id"}}</th>
        <td>
          <select name="anesth_id">
            <option value="">&mdash; Choisir un anesth�siste</option>
            {{foreach from=$anesths item=anesth}}
            <option value="{{$anesth->user_id}}" {{if $plagesel->anesth_id == $anesth->user_id}} selected="selected" {{/if}} >
              {{$anesth->_view}}
            </option>
            {{/foreach}}
    	  </select>
        </td>
        <th>{{mb_label object=$plagesel field="_heuredeb"}}</th>
        <td>
          <select name="_heuredeb" class="notNull num">
          {{foreach from=$listHours item=heure}}
            <option value="{{$heure|string_format:"%02d"}}" {{if $plagesel->_heuredeb == $heure}} selected="selected" {{/if}} >
              {{$heure|string_format:"%02d"}}
            </option>
          {{/foreach}}
          </select>
          :
          <select name="_minutedeb">
          {{foreach from=$listMins item=minute}}
            <option value="{{$minute|string_format:"%02d"}}" {{if $plagesel->_minutedeb == $minute}} selected="selected" {{/if}} >
              {{$minute|string_format:"%02d"}}
            </option>
          {{/foreach}}
          </select>
        </td>
      </tr>
      
      <tr>
        <th>
          <label for="_repeat" title="Nombre de plages � cr�er">Nombre de plages</label>
        </th>
        <td>
          <input type="text" class="notNull num min|1" name="_repeat" size="1" value="1" />
        </td>
    	  <th>{{mb_label object=$plagesel field="_heurefin"}}</th>
        <td>
          <select name="_heurefin" class="notNull num">
            {{foreach from=$listHours item=heure}}
            <option value="{{$heure|string_format:"%02d"}}" {{if $plagesel->_heurefin == $heure}} selected="selected" {{/if}} >
              {{$heure|string_format:"%02d"}}
            </option>
            {{/foreach}}
          </select>
          :
          <select name="_minutefin">
            {{foreach from=$listMins item=minute}}
            <option value="{{$minute|string_format:"%02d"}}" {{if $plagesel->_minutefin == $minute}} selected="selected" {{/if}} >
              {{$minute|string_format:"%02d"}}
            </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <th>
          <label for="_type_repeat" title="Espacement des plages">Type de r�p�tition</label>
        </th>
        <td>
          <select name="_type_repeat">
            <option value="1">Normale</option>
            <option value="2">Une semaine sur deux</option>
            <option value="3">Une semaine sur trois</option>
          </select>
        </td>
        <th>{{mb_label object=$plagesel field="_min_inter_op"}}</th>
        <td>
          <input type="text" size="2" value="{{if $plagesel->_id}}{{$plagesel->_min_inter_op}}{{else}}15{{/if}}" name="_min_inter_op" class="notNull num pos" />
          min
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label object=$plagesel field="max_intervention"}}
        </th>
        <td>
					{{mb_field object=$plagesel field="max_intervention" size="1"}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="4">
        {{if $plagesel->plageop_id}}
          <button type="submit" class="modify">Modifier</button>
        {{else}}
          <button type="submit" class="new">Ajouter</button>
        {{/if}}
        </td>
      </tr>
    </table>
    </form>

    {{if $plagesel->plageop_id}}
      <form name="removeFrm" action="?m={{$m}}" method="post">
      <input type="hidden" name="dosql" value="do_plagesop_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="plageop_id" value="{{$plagesel->plageop_id}}" /> 
      <table class="form">
        <tr>
          <th class="category" colspan="2">Supprimer la plage op�ratoire</th>
        </tr>  
        <tr>
          <th>Supprimer cette plage pendant</th> 
          <td><input type="text" name="_repeat" size="1" value="1" /> semaine(s)</td>
        </tr>   
        <tr>
          <td class="button" colspan="2">
            <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName:'la plage op�ratoire',objName:'{{$plagesel->_view|smarty:nodefaults|JSAttribute}}'})">
              Supprimer
            </button>
          </td>
        </tr>
      </table>
      </form>
    {{/if}}
    {{/if}}
   </td>
   <td>
     {{include file="inc_legende_planning.tpl"}}
   </td>
  </tr>
</table>