<?php
/**
 * $Id$
 *
 * @category Password Keeper
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org */

if (empty($_SERVER["HTTPS"])) {
  $msg = "Vous devez utiliser le protocole HTTPS pour utiliser ce module.";
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

CCanDo::checkAdmin();

$password_id = CValue::getOrSession("password_id");
$category_id = CValue::getOrSession("category_id");

// Récupération de la catégorie
$category = new CPasswordCategory();
$category->load($category_id);
$category->loadRefsBack();

// Récupération du mot de passe
$password = new CPasswordEntry();
$password->load($password_id);

$smarty = new CSmartyDP();
$smarty->assign("category"   , $category);
$smarty->assign("password"   , $password);
$smarty->display("inc_edit_password.tpl");