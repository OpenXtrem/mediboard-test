{if $curr_adm->annule == 1} {assign var=background value="#f33"}
{elseif $curr_adm->type == 'ambu'} {assign var=background value="#faa"}
{elseif $curr_adm->type == 'comp'} {assign var=background value="#fff"}
{elseif $curr_adm->type == 'exte'} {assign var=background value="#afa"}
{/if}

<td class="text" style="background: {$background}">
  <a name="adm{$curr_adm->operation_id}" href="javascript:printAdmission({$curr_adm->operation_id})">
  {$curr_adm->_ref_patient->_view}
  </a>
</td>

<td class="text" style="background: {$background}">
  <a href="javascript:printAdmission({$curr_adm->operation_id})">
  Dr. {$curr_adm->_ref_praticien->_view}
  </a>
</td>

<td style="background: {$background}">
  <a href="javascript:printAdmission({$curr_adm->operation_id})">
  {$curr_adm->entree_prevue|date_format:"%Hh%M"} ({$curr_adm->type|truncate:1:"":true})
  </a>
</td>

<td class="text" style="background: {$background}">
  {assign var=affectation value=$curr_adm->_ref_first_affectation}
  {if $affectation->affectation_id}
  {$affectation->_ref_lit->_view}
  {else}
  Pas de chambre
  {/if}
    <form name="editChFrm{$curr_adm->sejour_id}" action="index.php" method="post">
    
    <input type="hidden" name="m" value="dPhospi" />
    <input type="hidden" name="otherm" value="dPadmissions" />
    <input type="hidden" name="dosql" value="do_edit_chambre" />
    <input type="hidden" name="id" value="{$curr_adm->sejour_id}" />
    {if $curr_adm->chambre_seule == 'o'}
    <input type="hidden" name="value" value="n" />
    <button type="button" style="background-color: #f55;" onclick="submitAdmission(this.form);">
      <img src="modules/{$m}/images/refresh.png" alt="changer" /> simple
    </button>
    {else}
    <input type="hidden" name="value" value="o" />
    <button type="button" onclick="submitAdmission(this.form);">
      <img src="modules/{$m}/images/refresh.png" alt="changer" /> double
    </button>
    {/if}
    
    </form>
</td>

{if $curr_adm->annule == 1}
<td style="background: {$background}" align="center" colspan=2>
  <strong>ANNULE</strong></td>
{else}
<td style="background: {$background}">
  <form name="editAdmFrm{$curr_adm->sejour_id}" action="index.php" method="post">
  <input type="hidden" name="m" value="{$m}" />
  <input type="hidden" name="dosql" value="do_edit_admis" />
  <input type="hidden" name="id" value="{$curr_adm->sejour_id}" />
  <input type="hidden" name="mode" value="admis_SHS" />
  {if !$curr_adm->entree_reelle}
  <input type="hidden" name="value" value="o" />
  <button type="button" onclick="submitAdmission(this.form);">
    <img src="modules/{$m}/images/tick.png" alt="Admis" /> Admis
  </button>
  {else}
  � {$curr_adm->entree_prevue|date_format:"%Hh%M"}<br />
  <input type="hidden" name="value" value="n" />
  <button type="button" onclick="submitAdmission(this.form);">
    <img src="modules/{$m}/images/cross.png" alt="Annuler" /> Annuler
  </button>
  {/if}
  </form>
</td>

<td style="background: {$background}">
  <form name="editSaisFrm{$curr_adm->sejour_id}" action="index.php" method="post">
  <input type="hidden" name="m" value="{$m}" />
  <input type="hidden" name="dosql" value="do_edit_admis" />
  <input type="hidden" name="id" value="{$curr_adm->sejour_id}" />
  <input type="hidden" name="mode" value="saisi_SHS" />
  {if $curr_adm->saisi_SHS == "n"}
  <input type="hidden" name="value" value="o" />
  <button type="button" onclick="submitAdmission(this.form);">
    <img src="modules/{$m}/images/tick.png" alt="Saisi" /> Saisi
  </button>
  {else}
  <input type="hidden" name="value" value="n" />
  <button type="button" onclick="submitAdmission(this.form);">
    <img src="modules/{$m}/images/cross.png" alt="Annuler" /> Annuler
  </button>
  {/if}
  {if $curr_adm->modif_SHS == 1}
  <img src="images/icons/rc-gui-status-downgr.png" alt="modifi�" />
  {/if}
  </form>
</td>
{/if}

<td style="background: {$background}">
  {foreach from=$curr_adm->_ref_operations item=curr_op}
  {if $curr_op->depassement}
  <!-- Pas de possibilit� d'imprimer les d�passements pour l'instant -->
  <!-- <a href="javascript:printDepassement({$curr_adm->operation_id})"></a> -->
  {$curr_op->depassement} �
  <br />
  {/if}
  {foreachelse}
  -
  {/foreach}
</td>