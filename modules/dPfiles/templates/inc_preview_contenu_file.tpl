{{if $includeInfosFile}}
  {{if $file->file_type == "text/plain"}}
    <center>
      <div class="previewfile{{if $stylecontenu}} {{$stylecontenu}}{{/if}}">
        {{if $popup}}
        <a href="index.php?m=dPfiles&amp;a=fileviewer&amp;suppressHeaders=1&amp;file_id={{$file->file_id}}" title="TÚlÚcharger le fichier">
        {{else}}
        <a href="javascript:popFile({{$file->file_id}},{{if $sfn}}{{$sfn}}{{else}}0{{/if}})">
        {{/if}}
        <div>
        {{$includeInfosFile|smarty:nodefaults}}
        </div>
        </a>
      </div>
    </center>
  {{else}}
    {{$includeInfosFile}}
  {{/if}}
{{/if}}