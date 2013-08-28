{{mb_script module=admin    script=preferences  ajax=true}}
{{mb_script module=patients script=autocomplete ajax=true}}

<script type="text/javascript">

Main.add(function () {
  Control.Tabs.create('tab_edit_mediuser', true).activeLink.onmouseup();
});
</script>

<ul id="tab_edit_mediuser" class="control_tabs">
  <li>
    <a href="#edit-mediuser" onmouseup="">
      {{tr}}Account{{/tr}}
    </a>
  </li>
    
  <li>
  	<a href="#edit-preferences" onmouseup="Preferences.refresh('{{$user->_id}}')">
  		{{tr}}Preferences{{/tr}}
		</a>
	</li>

  {{if @$modules.dPpersonnel->_can->read}}
    <li>
      {{mb_script module=personnel script=plage}}
      <script>
        PlageConge.refresh = function() {
          PlageConge.content();
          PlageConge.loadUser('{{$user->_id}}', '');
          PlageConge.edit('','{{$user->_id}}');
        }
      </script>
      <a href="#edit-holidays" onmouseup="PlageConge.refresh()">
        {{tr}}Holidays{{/tr}}
      </a>
    </li>
  {{/if}}

  {{if "astreintes"|module_active}}
    <li>
      {{mb_script module=astreintes script=plage}}
      <a href="#edit-astreintes" onmouseup="PlageAstreinte.refreshList('edit-astreintes','{{$user->_id}}');">
        {{tr}}CPlageAstreinte{{/tr}}
      </a>
    </li>
  {{/if}}

  {{if "oxFacturation"|module_active}}
    <script>
      factureUser = function() {
        var url = new Url("oxFacturation" , "vw_factures_user");
        url.requestUpdate('edit-factureox');
      }
    </script>
    <li>
      <a href="#edit-factureox" onmouseup="factureUser();">{{tr}}CFactureOX{{/tr}}</a>
    </li>
  {{/if}}
  
  {{if "ecap"|module_active}}
    <li>
      {{mb_script module=astreintes script=plage}}
      <a href="#support" onmouseup="">
        {{tr}}Support{{/tr}}
        </a>
    </li>
  {{/if}}

</ul>

<hr class="control_tabs" />

<div id="edit-mediuser" style="display: block;">
<table class="main">
  <tr>
    <td class="halfPane">
      {{mb_include template=inc_info_mediuser}}
      {{mb_include template=inc_info_exchange_source}}
    </td>
    
    <td class="halfPane">
      {{mb_include template=inc_info_function}}
    </td>
  </tr>
</table>
</div>

<div id="edit-preferences" style="display: none;">
</div>

{{if "ecap"|module_active}}
<div id="support" style="display: none;">
  {{mb_include module=ecap template=support}}
</div>
{{/if}}

{{if "astreintes"|module_active}}
  <div id="edit-astreintes" style="display: none;">
  </div>
{{/if}}


{{if @$modules.dPpersonnel->_can->read}}
<div id="edit-holidays" style="display: none;">
  <table class="main">
    <tr>
      <td class="halfPane" id = "vw_user">
      </td> 
      <td class="halfPane" id = "edit_plage">
      </td>
    </tr>
    <tr>
      <td colspan="2" id="planningconge"></td>
    </tr>
  </table>
</div>
{{/if}}

{{if "oxFacturation"|module_active}}
  <div id="edit-factureox" style="display: none;">
  </div>
{{/if}}