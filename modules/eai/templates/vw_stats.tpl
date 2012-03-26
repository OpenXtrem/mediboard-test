{{*
 * View exchange data format EAI
 *  
 * @category EAI
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version  SVN: $Id:$ 
 * @link     http://www.mediboard.org
*}}

<script type="text/javascript">
  function checkChildClasses(childClasses, parent) {
    for (var i = 0; i < childClasses.length; i++) {
      $(childClasses[i]).checked = parent.checked;
    }
  }
  
  function uncheckSubClass(subClass) {
    $(subClass).checked = false;
  }
  
  updateGraph = function(form) {
    var url = new Url("eai", "ajax_inc_graph");
    url.addFormData(form);
    url.requestUpdate("inc_graph");
    return false;   
  }

  Main.add(function () {
    PairEffect.initGroup("classEffect", { bStoreInCookie: false });
    
    var form  = getForm('graph-filter');
    updateGraph(form);
    $(form.count).addSpinner({min: 0});
  });
</script>

<form name="graph-filter" action="?" method="get" onsubmit="return updateGraph(this)">
  <input type="hidden" name="m" value="{{$m}}"/>
  
  <table class="main form">
    <tr>
      <th class="category">Affichage</th>
      <th class="category">{{tr}}CExchangeDataFormat{{/tr}}</th>
      <th class="category">Crit�res de filtrage</th>
    </tr>
    <tr>
      <td>
        <table class="form">
          <tr>
            <th>{{mb_label object=$filter field="group_id"}}</th>
            <td>{{mb_field object=$filter field="group_id" canNull=true form="graph-filter" autocomplete="true,1,50,true,true"}}</td>
          </tr>
          <tr>
            <th>Date</th>
            <td>{{mb_field object=$filter field=date_production form=graph-filter register=true prop=dateTime}}</td>
          </tr>
          <tr>
            <th>Sur</th>
            <td>
              <input type="text" name="count" value="{{$count}}" size="2"/>
              <select name="period" onchange="this.form.onsubmit()">
                <option value="DAY">jours</option>
                <option value="WEEK">semaines</option>
                <option value="MONTH">mois</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>Mode</th>
            <td>
              <label>
                <input type="radio" name="mode" value="lines" onchange="" checked="true"/>Lignes
              </label>
              <label>
                <input type="radio" name="mode" value="bars" onchange=""/>Barres
              </label>
            </td>
          </tr>
        </table>  
      </td>

      <td>
        <table class="form">
          {{foreach from=$exchanges_classes key=_sub_class item=_child_classes}}
            <tr id="class{{$_sub_class}}-trigger">
              <td>
                <label>
                  <input type="checkbox" name="selected_subclasses[]" id="{{$_sub_class}}" value="{{$_sub_class}}" 
                         onclick='checkChildClasses({{if $_sub_class == "CExchangeAny"}} {{$_sub_class}} {{else}} {{$_child_classes|@json}} {{/if}}, this);'/>{{tr}}{{$_sub_class}}{{/tr}}
                </label>
              </td>
            </tr>
            <tbody class="classEffect" id="class{{$_sub_class}}">
              {{foreach from=$_child_classes item=_child}}
                {{if $_sub_class != "CExchangeAny"}}
                  <tr>
                    <td>
                      <label style="margin-left: 3em;">
                        <input type="checkbox" name="selected_exchanges[{{$_sub_class}}][]" id="{{$_child}}" value="{{$_child}}" onclick="uncheckSubClass({{$_sub_class}});"/>{{tr}}{{$_child}}{{/tr}}
                      </label>
                    </td>
                  </tr>
                {{else}}
                  <input type="checkbox" name="selected_exchanges[{{$_sub_class}}][]" id="{{$_sub_class}}" value="{{$_sub_class}}" style="display: none;"/>
                {{/if}}
              {{/foreach}}
            </tbody>
          {{/foreach}}
        </table>
      </td>
      
      <td>
        <table class="form">
          {{foreach from=$criteres item=critere}}
            <tr>
              <td>
                <label>
                  <input type="checkbox" name="selected_criteres[{{$critere}}]" value="{{$critere}}"/>{{tr}}CExchange-type-{{$critere}}{{/tr}}
                </label>
              </td>
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="3" class="button">
        <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="main">
  <tr>
    <td id="inc_graph"></td>
  </tr>
</table>
