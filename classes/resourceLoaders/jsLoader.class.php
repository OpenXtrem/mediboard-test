<?php /* $Id$ */

/**
 * @package Mediboard
 * @subpackage classes
 * @version $Revision$
 * @author SARL OpenXtrem
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CAppUI::loadClass("CHTMLResourceLoader");

abstract class CJSLoader extends CHTMLResourceLoader {
  static $files = array();
  
  /**
   * Loads a javascript file
   */
  static function loadFile($file, $cc = null, $build = null) {
    $tag = self::getTag("script", array(
      "type" => "text/javascript",
      "src"  => "$file?".self::getBuild($build),
    ), null, false);
    return self::conditionalComments($tag, $cc);
  }
  
  static function loadFiles($compress = false) {
    $result = "";
    
    // FIXME: make advanced tests to remove this line
    $compress = false;
    
    /** 
     * There is a speed boost on the page load when using compression 
     * between the top of the head and the dom:loaded event of about 25%.
     * This is because of parse time that is reduced (compare the global __pageLoad variable)
     * The number of requests from a regular page goes down from 100 to 70.
     * The total size of the JS goes down from 300kB to 230kB (gzipped).
     */
    if ($compress) {
      $hash = md5(implode("", self::$files));
      
      $cachefile = "./tmp/$hash.js";
      $uptodate = false;
      $excluded = array();
      
      foreach(self::$files as $file) {
        if (strpos($file, "/tmp/") !== false) {
          $excluded[] = $file;
        }
      }
      
      if (file_exists($cachefile)) {
        $uptodate = true;
        $last_update = filemtime($cachefile);
        foreach(self::$files as $file) {
          if (filemtime($file) > $last_update) {
            $uptodate = false;
            break;
          }
        }
      }
      
      if (!$uptodate) {
        $all_scripts = "";
        foreach(self::$files as $file) {
          if (in_array($file, $excluded)) continue;
          $all_scripts .= file_get_contents($file);
        }
        file_put_contents($cachefile, JSMin::minify($all_scripts));
        $last_update = time();
      }
      
      foreach($excluded as $file) {
        $result .= self::loadFile($file, null, filemtime($file))."\n";
      }
      $result .= self::loadFile($cachefile, null, $last_update)."\n";
    }
    else {
      foreach(self::$files as $file)
        $result .= self::loadFile($file)."\n";
    }
    
    return $result;
  }
  
  static function writeLocaleFile($language = null) {
    global $version, $locales;
    
    $current_locales = $locales;
    
    if (!$language) {
      $languages = array();
      foreach (glob("./locales/*", GLOB_ONLYDIR) as $lng)
        $languages[] = basename($lng);
    }
    else {
      $languages = array($language);
    }
    
    foreach($languages as $language) {
      $localeFiles = array_merge(
        glob("./locales/$language/*.php"), 
        glob("./modules/*/locales/$language.php")
      );
      
      foreach ($localeFiles as $localeFile) {
        if (basename($localeFile) != "meta.php") {
          require $localeFile;
        }
      }
      
      $path = "./tmp/locales.$language.js";
    
      if ($fp = fopen($path, 'w')) {
        // The callback will filter on empty strings (without it, "0" will be removed too).
        $locales = array_filter($locales, "stringNotEmpty");
        // TODO: change the invalid keys (with accents) of the locales to simplify this
        $keys = array_map('utf8_encode', array_keys($locales));
        $values = array_map('utf8_encode', $locales);
        $script = '//'.$version['build']."\nwindow.locales=".json_encode(array_combine($keys, $values)).";";
        fwrite($fp, $script);
        fclose($fp);
      }
    }
    
    $locales = $current_locales;
  }

  static function getLocaleFile() {
    $language = CAppUI::pref("LOCALE");
    $path = "./tmp/locales.$language.js";
  
    if (!is_file($path)) {
      self::writeLocaleFile($language);
    }
    
    return $path;
  }
}
