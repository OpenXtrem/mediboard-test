<table class="main">
  <!-- Liste des classes -->
  <tr>
    <td style="text-align: center;">
      <form action="?" name="mntTable" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
  
        <label for="class" title="Veuillez S�lectionner une classe">Choix de la classe</label>
        <select class="notNull str" name="class">
          <option value=""{{if !$class}} selected="selected"{{/if}}>&mdash; Liste des erreurs</option>
          {{foreach from=$installed_classes item=_class}}
          <option value="{{$_class}}" {{if $class == $_class}} selected="selected"{{/if}}>{{$_class}} - {{tr}}{{$_class}}{{/tr}}</option>
          {{/foreach}}
        </select>
        <br />
        
        {{foreach from=$types key=type item=value}}
          <input type="checkbox" name="types[]" value="{{$type}}" {{if $value}}checked="checked"{{/if}} />{{$type}}
        {{/foreach}}
        <br />
        
        <button name="submit" class="search">Filtrer</button>
      </form>
    </td>
  </tr>
	
	<tr>
	  <td>
      <div class="big-info">Pour chaque sp�cification de propri�t� : 
      	<ul>
      	  <li><strong>la premi�re ligne</strong> correspond au mapping objet => relationnel th�orique,</li> 
					<li><strong>la deuxi�me ligne </strong>correspond � ce qui est r�ellement pr�sent dans la base de donn�es.</li>
				</ul>
			</div>
	  </td>
	</tr>
  
  <tr>
    <td>
      <table class="tbl">
        <tr>
          <th rowspan="2">Champ</th>
          <th rowspan="2">Spec object</th>
          <th colspan="8">Base de donn�es</th>
        </tr>
        <tr>
          <th>Type</th>
          <th>Default</th>
          <th>Index</th>
          <th>Extra</th>
        </tr>
        
        {{foreach from=$list_classes key=_class item=_class_details}}
          {{if $list_errors.$_class || $list_classes|@count == 1}}
            {{if $_class_details.suggestion}}
	          <tr>
	            <th colspan="11" class="title">
	              <button id="sugg-{{$_class}}-trigger" class="edit" style="float: left;">
	                {{tr}}Suggestion{{/tr}}
	              </button>
	              {{$_class}} ({{tr}}{{$_class}}{{/tr}})
	            </th>
	          </tr>
	          <tr id="sugg-{{$_class}}">
	            <td colspan="100">
	              <script type="text/javascript">new PairEffect('sugg-{{$_class}}', {bStoreInCookie: false});</script>
	              <pre>{{$_class_details.suggestion}}</pre>
	            </td>
	          </tr>
	          {{/if}}
          {{foreach from=$_class_details.fields key=curr_field_name item=curr_field}}
            
            {{if $list_errors.$_class.$curr_field_name || $_class_details.key == $curr_field_name || $class == $_class}}
            <tr>
              <td {{if $_class_details.key == $curr_field_name}}class="ok"{{/if}}>{{$curr_field_name}}</td>
              
              {{if !$curr_field.object.spec}}
                <td class="warning text">Aucune spec<br />&nbsp;</td>
              {{else}}
                <td class="text" title="{{$curr_field.object.spec}}">{{$curr_field.object.spec|replace:'|':' | '}}<br />&nbsp;</td>
              {{/if}}
              
              <td class="text">
                {{if $curr_field.object.db_spec}}
                    {{$curr_field.object.db_spec.type}}
                    
                    {{if $curr_field.object.db_spec.params|@count > 0}}
                    (
                      {{foreach from=$curr_field.object.db_spec.params item=param name=params}}
                        {{$param}}{{if !$smarty.foreach.params.last}},{{/if}} 
                      {{/foreach}}
                    )
                    {{/if}}
                    
                    {{if $curr_field.object.db_spec.unsigned}}UNSIGNED{{/if}}
                    {{if $curr_field.object.db_spec.zerofill}}ZEROFILL{{/if}}
                  
                    {{if !$curr_field.object.db_spec.null}}NOT NULL{{/if}}
                    {{if $curr_field.object.db_spec.default !== null}}DEFAULT {{$curr_field.object.db_spec.default}}{{/if}}
                {{else}}
                  <div class="error">
                    Pas de spec pour cette colonne
                  </div>
                {{/if}}
                &nbsp;
                <hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                
                {{if !$_class_details.no_table}}
                  {{if $curr_field.db}}
                    <span {{if $curr_field.db.type != $curr_field.object.db_spec.type}}class="warning"{{/if}}>
                      {{$curr_field.db.type}}
                    </span>
                    
                    <span {{if $curr_field.db.params != $curr_field.object.db_spec.params}}class="warning"{{/if}}>
                      {{if $curr_field.db.params|@count > 0}}
                      (
                        {{foreach from=$curr_field.db.params item=param name=params}}
                          {{$param}}{{if !$smarty.foreach.params.last}},{{/if}}
                        {{/foreach}}
                      )
                      {{/if}}
                    </span>
                  
                    <span {{if $curr_field.db.unsigned != $curr_field.object.db_spec.unsigned}}class="warning"{{/if}}>
                      {{if $curr_field.db.unsigned}}UNSIGNED{{/if}}
                    </span>
                    
                    <span {{if $curr_field.db.zerofill != $curr_field.object.db_spec.zerofill}}class="warning"{{/if}}>
                      {{if $curr_field.db.zerofill}}ZEROFILL{{/if}}
                    </span>
                    
                    <span {{if $curr_field.db.null != $curr_field.object.db_spec.null}}class="warning"{{/if}}>
                      {{if !$curr_field.db.null}}NOT NULL{{/if}}
                    </span>
  
                  {{else}}
                    <div class="error">
                      Pas de colonne pour cette spec
                    </div>
                  {{/if}}
                {{else}}
                  <div class="error">
                    Pas de table existante pour cette classe
                  </div>
                {{/if}}
              </td>
              
              <td>
                {{$curr_field.object.db_spec.default}}&nbsp;<hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                <span {{if $curr_field.db.default != $curr_field.object.db_spec.default}}class="warning"{{/if}}>
                  {{$curr_field.db.default}}&nbsp;
                </span>
              </td>
              
              <td>
                {{if $curr_field.object.db_spec.index}}Oui{{else}}Non{{/if}}&nbsp;<hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                <span 
                  {{if $curr_field.object.db_spec.index && !$curr_field.db.index}}
                    class="error"
                  {{elseif !$curr_field.object.db_spec.index && $curr_field.db.index}}
                    class="warning"
                  {{/if}}>
                  {{if $curr_field.db.index}}Oui{{else}}Non{{/if}}&nbsp;
                </span>
              </td>
              
              <td>
                {{$curr_field.object.db_spec.extra}}&nbsp;<hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                <span {{if $curr_field.db.extra != $curr_field.object.db_spec.extra}}class="warning"{{/if}}>
                  {{$curr_field.db.extra}}&nbsp;
                </span>
              </td>
            </tr>
            {{/if}}
          {{/foreach}}
          {{/if}}
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>