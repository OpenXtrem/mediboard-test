<?php
/**
 * $Id$
 * 
 * @package    Mediboard
 * @subpackage classes
 * @author     SARL OpenXtrem <dev@openxtrem.com>
 * @license    GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 * @version    $Revision$
 */

/**
 * The VMC view class
 * Responsibilities :
 *  - view reflexion
 *  - view helpers
 */
class CView {
  /** @var stdClass Parameters values */
  public static $params;
  /** @var string[] Parameters properties */
  public static $props;

  /**
   * Get a REQUEST parameter
   *
   * @param string $name Name of the parameter
   * @param string $prop Property specification of the parameter
   *
   * @return object
   */
  static public function request($name, $prop) {
    return self::checkParam($name, $prop, CValue::request($name));
  }

  /**
   * Check a parameter
   *
   * @param string $name  Name of the parameter
   * @param string $prop  Property specification of the parameter
   * @param string $value Value of the paramter
   *
   * @return object
   */
  static public function checkParam($name, $prop, $value) {
    self::$params->$name =& $value;

    // Get Specification
    self::$props[$name] = $prop;
    $spec = CMbFieldSpecFact::getSpecWithClassName("stdClass", $name, $prop);

    // Defaults the value when available
    if (empty($value) && $spec->default) {
      $value = $spec->default;
    }

    // Could be null
    if ($value === "" || $value === null) {
      if (!$spec->notNull) {
        return $value;
      }
    }

    // Check the value
    if ($msg = $spec->checkProperty(self::$params)) {
      $truncated = CMbString::truncate($value);
      $error = "View parameter '$name' with spec '$prop' has inproper value '$truncated': $msg";
      trigger_error($error, E_USER_WARNING);
    }

    return $value;
  }

  /**
   * Produce a regression check plan
   *
   * @param string[] $props Properties
   *
   * @return array Complete plan
   */
  static public function sampleCheckPlan($props) {
    return self::flatify((array)$props);

  }

  /**
   * Turn rercursive plan to flat plan
   *
   * @param string[] $props Parameters
   *
   * @return array
   */
  function flatify($props) {
    if (!count($props)) {
      return array(array());
    }

    // Spec for only first item
    $spec = null;
    foreach ($props as $_param => $_prop) {
      $spec = CMbFieldSpecFact::getSpecWithClassName("stdClass", $_param, $_prop);
      break;
    }

    // Shift this item and recurse plan
    array_shift($props);
    $subplan = self::flatify($props);

    // Complete with own values
    $plan = array();
    foreach ($subplan as $_subparts) {
      foreach ($spec->regressionSamples() as $_sample) {
        $parts = $_subparts;
        $parts[$spec->fieldName] = $_sample;
        $plan[] = $parts;
      }
    }
    return $plan;
  }

  /**
   * Close the parameter list definition and provides inspection information on info mode
   *
   * @return void
   */
  static public function checkin() {
    if (!CValue::request("info")) {
      return;
    }

    // Dump properties on raw
    if (CValue::request("raw")) {
      echo json_encode(self::$props);
      CApp::rip();
    }

    // Show properties
    $smarty = new CSmartyDP("modules/system");
    $smarty->assign("props", self::$props);
    $smarty->display("view_info.tpl");
    CApp::rip();
  }
}

CView::$params = new stdClass;