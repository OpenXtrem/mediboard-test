<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage mediboard
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

require("./includes/compat.php");
require("./includes/magic_quotes_gpc.php");

/* 
 * The order of the keys is important (only the first keys 
 * are displayed in the short view of the Firebug console).
 */
$performance = array(
  // Performance
  "genere" => null,
  "memoire" => null,
  "size" => null,
  "objets" => 0,
  
  // Errors
  "error" => 0,
  "warning" => 0,
  "notice" => 0,
  
  // Cache
  "cachableCount" => null,
  "cachableCounts" => null,
);

if (!is_file("./includes/config.php")) {
  header("Location: install/");
  die("Redirection vers l'assistant d'installation");
}

require("./includes/config_dist.php");
require("./includes/config.php");

$rootName = basename($dPconfig["root_dir"]);

require("./includes/version.php");
require("./classes/sharedmemory.class.php");

// PHP Configuration
foreach($dPconfig["php"] as $key => $value) {
  if ($value === "") continue;
  ini_set($key, $value);
}

if ($dPconfig["offline"]) {
  header("Location: offline.php");
  die("Le syst�me est actuellement en cours de maintenance");
}

// Check that the user has correctly set the root directory
is_file ($dPconfig["root_dir"]."/includes/config.php") 
  or die("ERREUR FATALE: Le r�pertoire racine est probablement mal configur�");

require("./includes/mb_functions.php");
require("./includes/errors.php");

date_default_timezone_set($dPconfig["timezone"]);

// Start chrono
require("./classes/chrono.class.php");
$phpChrono = new Chronometer;
$phpChrono->start();

// Load AppUI from session
require("./classes/ui.class.php");
require("./includes/session.php");

// Register shutdown
register_shutdown_function(array("CApp", "checkPeace"));

require("./classes/sqlDataSource.class.php");
require("./classes/mysqlDataSource.class.php");

if(!CSQLDataSource::get("std")) {
  header("Location: offline.php?reason=bdd");
  die("La base de donn�es n'est pas connect�e");
}

// Write the HTML headers
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
header("Cache-Control: no-cache, no-store, must-revalidate");  // HTTP/1.1
header("Pragma: no-cache");  // HTTP/1.0

require("./includes/autoload.php");

// Load default preferences if not logged in
if (!CAppUI::$instance->user_id) {
  CAppUI::loadPrefs();
}

// Don't output anything. Usefull for fileviewers, popup dialogs, ajax requests, etc.
$wsdl = CValue::request("wsdl");
if (isset($wsdl)) {
  $wsdl = 1;
}

$suppressHeaders = CValue::request("suppressHeaders", $wsdl);

// Output the charset header in case of an ajax request
$ajax = CValue::request("ajax", false);

// Check if we are in the dialog mode
$dialog = CValue::request("dialog");

// check if the user is trying to log in
if (isset($_REQUEST["login"])) {
  require("./locales/core.php");
  if (null == $ok = CAppUI::login()) {
    CAppUI::setMsg("Auth-failed", UI_MSG_ERROR);
  }

  $redirect = CValue::request("redirect");
  parse_str($redirect, $parsed_redirect);
  if ($ok && $dialog && isset($parsed_redirect["login_info"])) {
    $redirect = "m=system&a=login_ok&dialog=1";
  }

  if ($redirect) {
    CAppUI::redirect($redirect);
  }
}

// clear out main url parameters
$m = $a = $u = $g = "";

// load locale settings
require("./locales/core.php");

if (empty($locale_info["names"])){
  $locale_info["names"] = array();
}
setlocale(LC_TIME, $locale_info["names"]);

if (empty($locale_info["charset"])){
  $locale_info["charset"] = "UTF-8";
}

// output the character set header
if (!$suppressHeaders || $ajax) {
  header("Content-type: text/html;charset=".$locale_info["charset"]);
}

// Show errors to admin
ini_set("display_errors", CAppUI::pref("INFOSYSTEM"));

$user = new CMediusers();
if ($user->isInstalled()) {
  $user->load(CAppUI::$instance->user_id);
  $user->getBasicInfo();
  CAppUI::$user = $user;
  CAppUI::$instance->_ref_user =& CAppUI::$user;
}

CAppUI::requireSystemClass("smartydp");

ob_start();

// verifier si Mobile
if(is_file("./mobile/main.php") && preg_match('/mobi/i', $_SERVER['HTTP_USER_AGENT'])||preg_match('/phone/i', $_SERVER['HTTP_USER_AGENT'])||preg_match('/symbian/i', $_SERVER['HTTP_USER_AGENT']) ){
  require("./mobile/main.php");
}
else {
  require("./includes/main.php");
}

require("./includes/access_log.php");

ob_end_flush();

CApp::rip();
