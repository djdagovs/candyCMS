<?php

/**
 * Create or destroy a session.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 */

namespace CandyCMS\Controller;

use CandyCMS\Controller\Main as Main;
use CandyCMS\Helper\Helper as Helper;
use CandyCMS\Helper\I18n as I18n;
use CandyCMS\Model\Session as Model;
use CandyCMS\Plugin\FacebookCMS as FacebookCMS;

class Session extends Main {

	/**
	 * Include the session model.
	 *
	 * @access public
	 * @override app/controllers/Main.controller.php
	 *
	 */
  public function __init() {
    $this->_oModel = new Model($this->_aRequest, $this->_aSession);
  }

	/**
	 * Create a session or show template instead.
	 * We must override the main method due to a diffent required user right.
	 *
	 * @access public
	 * @return string HTML content
	 * @override app/controllers/Main.controller.php
	 *
	 */
  public function create() {
		return isset($this->_aRequest['create_session']) ? $this->_create() : $this->showCreateSessionTemplate();
	}

	/**
	 * Create a session.
	 *
	 * Check if required data is given or throw an error instead.
	 * If data is given, create session.
	 *
	 * @access protected
	 * @return string|boolean HTML content (string) or returned status of model action (boolean).
	 *
	 */
	private function _create() {
		$this->_setError('email');
		$this->_setError('password');

		if (isset($this->_aError))
			return $this->showCreateSessionTemplate();

		elseif ($this->_oModel->create() === true)
			return Helper::successMessage(I18n::get('success.session.create'), '/');

		else
			return Helper::errorMessage(I18n::get('error.session.create'), '/session/create');
	}

	/**
	 * Build form template to create a session.
	 *
	 * @access public
	 * @return string HTML content
	 *
	 */
  public function showCreateSessionTemplate() {
    if (!empty($this->_aError))
      $this->oSmarty->assign('error', $this->_aError);

		$this->oSmarty->assign('email', isset($this->_aRequest['email']) ? (string) $this->_aRequest['email'] : '');

    $sTemplateDir = Helper::getTemplateDir('sessions', 'create');
    $this->oSmarty->template_dir = $sTemplateDir;
    return $this->oSmarty->fetch(Helper::getTemplateType($sTemplateDir, 'create'));
	}

	/**
	 * Resend password.
	 *
	 * @access public
	 * @return string HTML
	 * @todo better error messages
	 *
	 */
	public function resendPassword() {
		$this->__autoload('Mail');

		# If there is no email request then show form template
		if (!isset($this->_aRequest['email']))
			return $this->_showCreateResendActionsTemplate();

		# Check format of email
		else
			$this->_setError('email');

		if (isset($this->_aError))
			return $this->_showCreateResendActionsTemplate();

		else {
			$sNewPasswordClean = Helper::createRandomChar(10);
			$aData = $this->_oModel->resendPassword(md5(RANDOM_HASH . $sNewPasswordClean));

			if ($aData == true) {
				$sContent = str_replace('%u', $aData['name'], I18n::get('session.password.mail.body'));
				$sContent = str_replace('%p', $sNewPasswordClean, $sContent);

				$bStatus = Mail::send(Helper::formatInput($this->_aRequest['email']),
																									I18n::get('session.password.mail.subject'),
																									$sContent,
																									WEBSITE_MAIL_NOREPLY);

				return $bStatus === true ?
								Helper::successMessage(I18n::get('success.mail.create'), '/session/create') :
								Helper::errorMessage(I18n::get('error.mail.create')) . $this->showCreateSessionTemplate();
			}
			else
				# Replace error message with message, that email could not be found
				return Helper::errorMessage(I18n::get('error.sql'), '/');
		}
	}

	/**
	 * Resend verification.
	 *
	 * @access public
	 * @return string HTML
	 * @todo better error messages
	 *
	 */
	public function resendVerification() {
		$this->__autoload('Mail');

		# If there is no email request then show form template
		if (!isset($this->_aRequest['email']))
			return $this->_showCreateResendActionsTemplate();

		# Check format of email
		else
			$this->_setError('email');

		if (isset($this->_aError))
			return $this->_showCreateResendActionsTemplate();

		else {
			$aData = $this->_oModel->resendVerification();

			if (is_array($aData)) {
				$sVerificationUrl = Helper::createLinkTo('user/' . $aData['verification_code'] . '/verification');

				$sContent = str_replace('%u', $aData['name'], I18n::get('session.verification.mail.body'));
				$sContent = str_replace('%v', $sVerificationUrl, $sContent);

				$bStatus = Mail::send(Helper::formatInput($this->_aRequest['email']),
																									I18n::get('session.verification.mail.subject'),
																									$sContent,
																									WEBSITE_MAIL_NOREPLY);

				return $bStatus === true ?
								Helper::successMessage(I18n::get('success.mail.create'), '/session/create') :
								$this->showCreateSessionTemplate();
			}
			else
				# Replace error message with message, that email could not be found
				return Helper::errorMessage(I18n::get('error.sql'), '/');
		}
	}

	/**
	 * Build form template to resend verification or resend password.
	 *
	 * @access private
	 * @return string HTML content
	 * @todo put into two methods
	 *
	 */
  private function _showCreateResendActionsTemplate() {
		if ($this->_aRequest['action'] == 'password') {
			$this->_setTitle(I18n::get('session.password.title'));
			$this->_setDescription(I18n::get('session.password.info'));
		}
		else {
			$this->_setTitle(I18n::get('session.verification.title'));
			$this->_setDescription(I18n::get('session.verification.info'));
		}

		if (!empty($this->_aError))
      $this->oSmarty->assign('error', $this->_aError);

    $sTemplateDir = Helper::getTemplateDir('sessions', 'resend');
    $this->oSmarty->template_dir = $sTemplateDir;
    return $this->oSmarty->fetch(Helper::getTemplateType($sTemplateDir, 'resend'));
	}

	/**
	 * Destroy user session.
	 *
	 * @access public
	 * @return boolean status of model action
	 * @override app/controllers/Main.controller.php
	 *
	 */
  public function destroy() {
		if ($this->_aSession['userdata']['role'] == 2) {
			$this->_aSession['facebook']->getLogoutUrl();
			session_destroy();
			unset($_SESSION);
			return Helper::successMessage(I18n::get('success.session.destroy'), '/');
		}
		elseif ($this->_oModel->destroy() === true) {
			session_destroy();
			unset($_SESSION);
			return Helper::successMessage(I18n::get('success.session.destroy'), '/');
		}

		else
			return Helper::errorMessage(I18n::get('error.sql'), '/');
	}
}