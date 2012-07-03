<?php /* $Id$ */

/**
* @package Mediboard
* @subpackage dPrepas
* @version $Revision$
* @author S�bastien Fillonneau
*/

CCanDo::checkRead();

global $uistyle, $messages, $version;

set_time_limit(90);

$indexFile  = CValue::post("indexFile"  , 0);
$style      = CValue::post("style"      , 0);
$image      = CValue::post("image"      , 0);
$javascript = CValue::post("javascript" , 0);
$lib        = CValue::post("lib"        , 0);
$typeArch   = CValue::post("typeArch"   , "zip");

// Cr�ation du fichier Zip
if(file_exists("tmp/mediboard_repas.zip")){unlink("tmp/mediboard_repas.zip");}
if(file_exists("tmp/mediboard_repas.tar.gz")){unlink("tmp/mediboard_repas.tar.gz");}

if ($typeArch == "zip") {
  $zipFile = new ZipArchive();
  $zipFile->open("tmp/mediboard_repas.zip", ZIPARCHIVE::CREATE);
}
elseif ($typeArch == "tar"){
  $zipFile = new Archive_Tar("tmp/mediboard_repas.tar.gz", true);
}
else {
  return; 
}


if ($indexFile) {
  // Cr�ation du fichier index.html
  $plats     = new CPlat;  
  
  $configOffline = array("urlMediboard" => CAppUI::conf("base_url")."/",
                         "etatOffline"  => 0);
  
  $smarty = new CSmartyDP();
  $smarty->assign("plats" , $plats);
  $smarty->assign("mediboardScriptStorage", mbLoadScriptsStorage());
  
  $smartyStyle = new CSmartyDP();
  
  $smartyStyle->assign("offline"              , true);
  $smartyStyle->assign("localeInfo"           , $locale_info);
  $smartyStyle->assign("mediboardShortIcon"   , CFaviconLoader::loadFile("style/$uistyle/images/icons/favicon.ico"));
  $smartyStyle->assign("mediboardStyle"       , CCSSLoader::loadFiles());
  $smartyStyle->assign("mediboardScript"      , CJSLoader::loadFiles());
  $smartyStyle->assign("messages"             , $messages);
  $smartyStyle->assign("debugMode"            , CAppUI::pref("INFOSYSTEM"));
  $smartyStyle->assign("configOffline"        , $configOffline);
  $smartyStyle->assign("errorMessage"         , CAppUI::getMsg());
  $smartyStyle->assign("uistyle"              , $uistyle);
  
  ob_start();
  $smartyStyle->display("header.tpl");
  $smarty->display("repas_offline.tpl");
  $smartyStyle->display("footer.tpl");
  $indexFile = ob_get_contents();
  ob_end_clean();
  file_put_contents("tmp/index.html", $indexFile);
  
  if ($typeArch == "zip") {
    $zipFile->addFile("tmp/index.html", "index.html");
  }
  elseif ($typeArch == "tar"){
    $zipFile->addModify("tmp/index.html", null, "tmp/");
  }
}

function delSvnAndSmartyDir($action,$fileProps){
  if (preg_match("/.svn/",$fileProps["filename"]) 
   || preg_match("/templates/",$fileProps["filename"]) 
   || preg_match("/templates_c/",$fileProps["filename"])) {
    return false;
  }
  else {
   return true;
  }
}

function addFiles($src, &$zipFile, $typeArch) {
  if ($typeArch == "tar") {
    return $zipFile->add("$src/", array("callback_pre_add" => "delSvnAndSmartyDir"));
  }
  $values = array(".", "..", ".svn", "templates", "templates_c");
  $dir = opendir($src);
  while (false !== ($file = readdir($dir))) {
    if ((!in_array($file, $values))) {
      if (is_dir("$src/$file")) {
        addFiles("$src/$file", $zipFile, $typeArch);
      }
      else {
        $zipFile->addFile("$src/$file", "$src/$file");
      }
    }
  }
}

if ($style) {
  addFiles("style", $zipFile, $typeArch);
}

if ($image) {
  addFiles("images", $zipFile, $typeArch);
}

if ($lib) {
  addFiles("lib/dojo", $zipFile, $typeArch);
  addFiles("lib/datepicker", $zipFile, $typeArch);
  addFiles("lib/scriptaculous", $zipFile, $typeArch);
}

if ($javascript) {
  addFiles("includes/javascript", $zipFile, $typeArch);
  addFiles("modules/dPrepas/javascript", $zipFile, $typeArch);
}

if ($typeArch == "tar") {
  mbTrace($zipFile->listContent(), "Contenu de l'archive");
}
else {
  for( $i = 0; $i < $zipFile->numFiles; $i++ ){ 
    $stat = $zipFile->statIndex( $i ); 
    mbTrace(basename( $stat['name'])); 
  }
}

if ($typeArch == "zip") {
  $zipFile->close();
}

CApp::rip();
?>