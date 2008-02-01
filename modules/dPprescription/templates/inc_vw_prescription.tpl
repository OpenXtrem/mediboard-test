      {{if $httpreq}}
      <script type="text/javascript">
        Prescription.reloadAlertes();
      </script>
      {{/if}}

      {{if $prescription->_id}}
      <form action="?m=dPprescription" method="post" name="addLine" onsubmit="return checkForm(this);">
        <input type="hidden" name="m" value="dPprescription" />
        <input type="hidden" name="dosql" value="do_prescription_line_aed" />
        <input type="hidden" name="prescription_line_id" value=""/>
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="prescription_id" value="{{$prescription->_id}}"/>
        <input type="hidden" name="code_cip" value=""/>
      </form>
      <table class="form">
        <tr>
          <th class="title">
            <button type="button" class="cancel" onclick="Prescription.close()" style="float: left">
              Fermer
            </button>
            {{$prescription->_view}}
          </th>
        </tr>
        <tr>
          <td>
            <form action="?" method="get" name="searchProd" onsubmit="return false;">
              <select name="favoris" onchange="Prescription.addLine(this.value); this.value = '';">
                <option value="">&mdash; produits les plus utilis�s</option>
                {{foreach from=$listProduits item=curr_prod}}
                <option value="{{$curr_prod->code_cip}}">
                  {{$curr_prod->libelle}}
                </option>
                {{/foreach}}
              </select>
              <br />
              <input type="text" name="produit" value=""/>
              <div style="display:none;" class="autocomplete" id="produit_auto_complete"></div>
              <button type="button" class="search" onclick="MedSelector.init('produit');">Produits</button>
              <button type="button" class="search" onclick="MedSelector.init('classe');">Classes</button>
              <button type="button" class="search" onclick="MedSelector.init('composant');">Composants</button>
              <button type="button" class="search" onclick="MedSelector.init('DC_search');">DCI</button>
              <script type="text/javascript">
                if(MedSelector.oUrl) {
                  MedSelector.close();
                }
                MedSelector.init = function(onglet){
                  this.sForm = "searchProd";
                  this.sView = "produit";
                  this.sSearch = document.searchProd.produit.value;
                  this.sOnglet = onglet;
                  this.selfClose = false;
                  this.pop();
                }
                MedSelector.set = function(nom, code){
                  Prescription.addLine(code);
                }
              </script>
            </form>
          </td>
        </tr>
      </table>
      <table class="tbl">
        <tr>
          <th colspan="2">Produit</th>
          <th>Alertes</th>
        </tr>
        {{foreach from=$prescription->_ref_prescription_lines item=curr_line}}
        <tbody class="hoverable">
        <tr>
          <td rowspan="2">
            <button type="button" class="trash notext" onclick="Prescription.delLine({{$curr_line->_id}})">
              {{tr}}Delete{{/tr}}
            </button>
          </td>
          <td>
            <a href="#produit{{$curr_line->_id}}" onclick="viewProduit({{$curr_line->_ref_produit->code_cip}})">
              <strong>{{$curr_line->_view}}</strong>
            </a>
          </td>
          <td class="text">
            {{foreach from=$curr_line->_ref_alertes_text key=type item=curr_type}}
              {{if $curr_type|@count}}
                <ul>
                {{foreach from=$curr_type item=curr_alerte}}
                  <li>
                    <strong>{{tr}}CPrescriptionLine-alerte-{{$type}}-court{{/tr}} :</strong>
                    {{$curr_alerte}}
                  </li>
                {{/foreach}}
                </ul>
              {{/if}}
            {{/foreach}}
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <form action="?m=dPprescription" method="post" name="editLine-{{$curr_line->_id}}" onsubmit="return checkForm(this);">
              <input type="hidden" name="m" value="dPprescription" />
              <input type="hidden" name="dosql" value="do_prescription_line_aed" />
              <input type="hidden" name="prescription_line_id" value="{{$curr_line->_id}}"/>
              <input type="hidden" name="del" value="0" />
              <select name="no_poso" onchange="submitFormAjax(this.form, 'systemMsg')">
                <option value="">&mdash; Choisir une posologie</option>
                {{foreach from=$curr_line->_ref_produit->_ref_posologies item=curr_poso}}
                <option value="{{$curr_poso->code_posologie}}"
                  {{if $curr_poso->code_posologie == $curr_line->no_poso}}selected="selected"{{/if}}>
                  {{$curr_poso->_view}}
                </option>
                {{/foreach}}
              </select>
            </form>
          </td>
        </tr>
        </tbody>
        {{/foreach}}
      </table>
      <script type="text/javascript">
        // Preparation du formulaire
        prepareForm(document.addLine);
        prepareForm(document.searchProd);
        // Autocomplete
        urlAuto = new Url();
        urlAuto.setModuleAction("dPmedicament", "httpreq_do_medicament_autocomplete");
        urlAuto.addParam("produit_max", 10);
        urlAuto.autoComplete("searchProd_produit", "produit_auto_complete", {
            minChars: 3,
            updateElement: updateFields
        } );
      </script>
      {{else}}
      <form action="?m=dPprescription" method="post" name="addPrescription" onsubmit="return checkForm(this);">
        <input type="hidden" name="m" value="dPprescription" />
        <input type="hidden" name="dosql" value="do_prescription_aed" />
        <input type="hidden" name="prescription_id" value="" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_class" value="{{$prescription->object_class}}"/>
        <input type="hidden" name="object_id" value="{{$prescription->object_id}}"/>
        <select name="praticien_id">
          {{foreach from=$listPrats item=curr_prat}}
          <option value="{{$curr_prat->_id}}">
            {{$curr_prat->_view}}
          </option>
          {{/foreach}}
        </select>
        <button type="submit" class="new">Cr�er une prescription</button>
      </form>
      {{/if}}