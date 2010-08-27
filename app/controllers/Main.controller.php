<?php

/*
 * This software is licensed under GPL <http://www.gnu.org/licenses/gpl.html>.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

abstract class Main {
  protected $_aRequest;
  protected $_aSession;
  protected $_aFile;
  protected $_iId;
  protected $_aError;
  private $_aData = array();
  private $_sContent;
  private $_sTitle;
  private $_oModel;

  public function __construct($aRequest, $aSession, $aFile = '') {
    $this->_aRequest	=& $aRequest;
    $this->_aSession	=& $aSession;
    $this->_aFile			=& $aFile;

    $this->_iId = isset($this->_aRequest['id']) ?
                  (int)$this->_aRequest['id'] :
                  '';
  }

  public function __autoload($sClass) {
    require_once('app/controllers/'	.(string)ucfirst($sClass).	'.controller.php');
  }

  /* Manage Page Title */
  protected function _setTitle($sTitle) {
    $this->_sTitle =& $sTitle;
  }

  public function getTitle() {
    if( $this->_sTitle !== '' )
      return $this->_sTitle;
    else
      return '';
  }

  /* Manage Page Content */
  protected function _setContent($sContent) {
    $this->_sContent =& $sContent;
  }

  public function getContent() {
    return $this->_sContent;
  }

  public function search() {
    return $this->show();
  }

  public function show() {
    $this->show();
  }

  public function create($sInputName) {
    if( USER_RIGHT < 3 )
      return Helper::errorMessage(LANG_ERROR_GLOBAL_NO_PERMISSION);
    else {
      if( isset($this->_aRequest[$sInputName]) )
        return $this->_create();
      else
        return $this->_showFormTemplate(false);
    }
  }

  public function update($sInputName) {
    if( USER_RIGHT < 3 )
      return Helper::errorMessage(LANG_ERROR_GLOBAL_NO_PERMISSION);
    else {
      if( isset($this->_aRequest[$sInputName]) )
        return $this->_update();
      else
        return $this->_showFormTemplate(true);
    }
  }

  public function destroy() {
    if( USER_RIGHT < 3 )
      return Helper::errorMessage(LANG_ERROR_GLOBAL_NO_PERMISSION);
    else
      return $this->_destroy();
  }
}