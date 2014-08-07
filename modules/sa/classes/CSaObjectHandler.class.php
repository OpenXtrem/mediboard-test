<?php

/**
 * SA Handler
 *
 * @category SA
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  SVN: $Id:$
 * @link     http://www.mediboard.org
 */

/**
 * Class CSaObjectHandler
 * SA Handler
 */

class CSaObjectHandler extends CEAIObjectHandler {
  /**
   * @var array
   */
  static $handled = array ("CSejour", "COperation", "CConsultation");


  /**
   * If object is handled ?
   *
   * @param CMbObject $mbObject Object
   *
   * @return bool
   */
  static function isHandled(CMbObject $mbObject) {
    return in_array($mbObject->_class, self::$handled);
  }

  /**
   * @see parent::onBeforeStore
   */
  function onBeforeStore(CMbObject $mbObject) {
    if (!parent::onBeforeStore($mbObject)) {
      return;
    }
  }

  /**
   * @see parent::onAfterStore
   */
  function onAfterStore(CMbObject $mbObject) {
    if (!parent::onAfterStore($mbObject)) {
      return;
    }

    switch ($mbObject->_class) {
      // CSejour 
      // Envoi des actes / diags soit quand le s�jour est factur�, soit quand le sejour a une sortie r�elle
      // soit quand on a la cl�ture sur le sejour
      case 'CSejour': 
        $sejour = $mbObject;
        
        $send_only_with_type = CAppUI::conf("sa send_only_with_type");
        if ($send_only_with_type && ($send_only_with_type != $sejour->type)) {
          return;  
        }
        
        switch (CAppUI::conf("sa trigger_sejour")) {
          case 'sortie_reelle':
            if ($sejour->fieldModified('sortie_reelle')) {
              $this->sendFormatAction("onAfterStore", $sejour);
            }
            break;
            
          case 'testCloture':
            if ($sejour->testCloture()) {
              $this->sendFormatAction("onAfterStore", $sejour);
            }
            break;
            
          default:
            if ($sejour->fieldModified('facture', 1)) {
              $this->sendFormatAction("onAfterStore", $sejour);
            }
            break;
        }

        if (CAppUI::conf("sa send_actes_consult")) {
          if ($sejour->loadRefsConsultations()) {
            foreach ($sejour->_ref_consultations as $_consultation) {
              if (!$_consultation->sejour_id || !$_consultation->valide) {
                continue;
              }

              $sejour = $_consultation->loadRefSejour();
              $this->sendFormatAction("onAfterStore", $_consultation);
            }
          }
        }

        if (CAppUI::conf("sa send_actes_interv")) {
          if ($sejour->loadRefsOperations()) {
            foreach ($sejour->_ref_operations as $_operation) {
              $this->sendFormatAction("onAfterStore", $_operation);
            }
          }
        }

        break;
      
      // COperation
      // Envoi des actes soit quand l'interv est factur�e, soit quand on a la cl�ture sur l'interv
      case 'COperation':
        $operation = $mbObject;
        
        switch (CAppUI::conf("sa trigger_operation")) {
          case 'testCloture':
            if ($operation->testCloture()) {
              $this->sendFormatAction("onAfterStore", $operation);
            }
            break;

          case 'sortie_reelle':
            break;
            
          default:
            if ($operation->fieldModified('facture', 1)) {
              $this->sendFormatAction("onAfterStore", $operation);
            }
            break;
        }
        break;
      
      // CConsultation
      // Envoi des actes dans le cas de la cl�ture de la cotation
      case 'CConsultation':
        /** @var CConsultation $consultation */
        $consultation = $mbObject;
        
        if (!$consultation->sejour_id) {
          return;
        }

        switch (CAppUI::conf("sa trigger_consultation")) {
          case 'sortie_reelle':
            break;

          case 'facture':
            if ($consultation->fieldModified('facture', 1)) {
              $this->sendFormatAction("onAfterStore", $consultation);
            }
            break;

          default:
            if ($consultation->fieldModified('valide', 1)) {
              $this->sendFormatAction("onAfterStore", $consultation);
            }
            break;
        }

        break;

      default:
        return;
    } 
  }
}
