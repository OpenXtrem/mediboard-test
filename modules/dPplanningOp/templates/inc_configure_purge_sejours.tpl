<script type="text/javascript">

function purgeSejours() {
  var url = new Url("dPplanningOp", "ajax_purge_sejours");
  url.addParam("qte", 5);
  url.addParam("date_min", $V($("date_min_purge")));
  url.requestUpdate("purge_sejours", repeatPurge);
}

function repeatPurge() {
  if($V($("check_repeat_purge"))) {
    purgeSejours();
  }
}

</script>

<div class="big-warning">
  La purge des s�jours est une action irreversible qui supprime al�atoirement
  une partie des s�jours de la base de donn�es et toutes les donn�es
  qui y sont associ�es.
  <strong>
    N'utilisez cette fonctionnalit� que si vous savez parfaitement ce que vous faites
  </strong>
</div>
<table class="tbl">
  <tr>
    <th>
      Purge des s�jours (par 5)
      <button type="button" class="tick" onclick="purgeSejours();">
        GO
      </button>
      <br />
      <input type="text" name="date_min" value="{{$today}}" id="date_min_purge"/> Date minimale (YYYY-MM-DD)
      <br />
      <input type="checkbox" name="repeat_purge" id="check_repeat_purge"/> Relancer automatiquement
    </th>
  </tr>
  <tr>
    <td id="purge_sejours">
      <div class="small-info">{{$nb_sejours}} s�jours dans la base depuis {{$today|date_format:$conf.date}}</div>
    </td>
  </tr>
</table>