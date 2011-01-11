<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage dPetablissement
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

global $AppUI, $can, $m;

$can->needsRead();

// Récupération des fonctions
$group = new CGroups;
$listGroups = $group->loadListWithPerms(PERM_READ);

foreach($listGroups as $key => $value) {
  $listGroups[$key]->loadRefs();
}

// Récupération du groupe selectionné
$usergroup = new CGroups;
$usergroup->load(CValue::getOrSession("group_id", 0));
$usergroup->loadRefs();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("usergroup"   , $usergroup);
$smarty->assign("listGroups"  , $listGroups);

$smarty->display("vw_idx_groups.tpl");

?>