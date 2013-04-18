<?php

/**
 * $Id$
 *
 * @category Password Keeper
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @link     http://www.mediboard.org */

/**
 * Manage a password entry
 */
class CPasswordEntry extends CMbObject {
  /** @var  Password ID */
  public $password_id;

  /** @var  Password description */
  public $password_description;

  /** @var  Password */
  public $password;

  /** @var  Datetime when password last changed */
  public $password_last_change;

  /** @var  Random initialisation vector */
  public $iv;

  /** @var  Password comments */
  public $password_comments;

  /** @var  CPasswordCategory reference */
  public $category_id;

  function getSpec() {
    $spec = parent::getSpec();

    $spec->table = 'password_entry';
    $spec->key   = 'password_id';

    return $spec;
  }

  function getBackProps() {
    $backProps = parent::getBackProps();

    return $backProps;
  }

  function getProps() {
    $props = parent::getProps();

    $props["password_description"] = "str notNull maxLength|50";
    $props["password"]             = "password notNull show|0 loggable|0";
    $props["password_last_change"] = "dateTime notNull";
    $props["iv"]                   = "str notNull show|0 loggable|0";
    $props["password_comments"]    = "text";
    $props["category_id"]          = "ref notNull class|CPasswordCategory";

    return $props;
  }

  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->password_description;
  }

  function store() {
    // Si cr�ation : g�n�ration du vecteur d'initialisation
    if (!$this->_id || $this->fieldModified("password")) {
      CAppUI::requireLibraryFile("phpseclib/phpseclib/Crypt/Random");
      $this->iv = bin2hex(crypt_random_string(16));

      // Date de cr�ation du mot de passe
      $this->password_last_change = CMbDT::dateTime();
    }

    $this->password = $this->encrypt();

    if ($msg = parent::store()) {
      return $msg;
    }
  }

  function encrypt() {
    $passphrase = CValue::sessionAbs("passphrase");

    CAppUI::requireLibraryFile("phpseclib/phpseclib/Crypt/AES");

    $cipher = new Crypt_AES(CRYPT_AES_MODE_CTR);
    $cipher->setKey($passphrase);
    $cipher->setIV($this->iv);

    $crypted = rtrim(base64_encode($cipher->encrypt($this->password)), "\0\3");

    return $crypted;
  }

  function getPassword() {
    $passphrase = CValue::sessionAbs("passphrase");

    CAppUI::requireLibraryFile("phpseclib/phpseclib/Crypt/AES");

    $cipher = new Crypt_AES(CRYPT_AES_MODE_CTR);
    $cipher->setKey($passphrase);
    $cipher->setIV($this->iv);

    $decrypted = rtrim(base64_decode($this->password), "\0\3");
    $decrypted = $cipher->decrypt($decrypted);

    return $decrypted;
  }
}
