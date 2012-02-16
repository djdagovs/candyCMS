<?php

/**
 * Handle all blog SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 */

namespace CandyCMS\Model;

use CandyCMS\Helper\AdvancedException as AdvancedException;
use CandyCMS\Helper\Helper as Helper;
use CandyCMS\Helper\Pagination as Pagination;
use CandyCMS\Model\User as User;
use PDO;

require_once PATH_STANDARD . '/app/models/User.model.php';

class Session extends Main {

  /**
   * Fetch all user data of active session.
   *
   * @static
   * @access public
   * @return array $aResult user data
   * @see app/controllers/Index.controller.php
	 *
   */
  public static function getUserDataBySession() {
    if (empty(parent::$_oDbStatic))
      parent::_connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                u.*
                                              FROM
                                                " . SQL_PREFIX . "users AS u
																							LEFT JOIN
																								" . SQL_PREFIX . "sessions AS s
																							ON
																								u.id = s.user_id
                                              WHERE
                                                s.session = :session_id
                                              AND
                                                s.ip = :ip
                                              LIMIT
                                                1");

      $oQuery->bindParam('session_id', session_id(), PDO::PARAM_STR);
      $oQuery->bindParam('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
      $bReturn = $oQuery->execute();

      if ($bReturn == false)
        self::destroy();

      return $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0072 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Create a user session.
   *
   * @access public
	 * @param array $aUser optional user data.
   * @return boolean status of login
	 *
   */
  public function create($aUser = '') {
		if (empty($aUser)) {
			$oModel = new User($this->_aRequest, $this->_aSession);
			$aUser	= $oModel->getLoginData();
		}

    # User did verify and has id, so log in!
    if (isset($aUser['id']) && !empty($aUser['id']) && empty($aUser['verification_code'])) {
			try {
				$oQuery = $this->_oDb->prepare("INSERT INTO
																					" . SQL_PREFIX . "sessions
																					(	user_id,
																						session,
																						ip,
																						date)
																				VALUES
																					( :user_id,
																						:session,
																						:ip,
																						:date)");

				$oQuery->bindParam('user_id', $aUser['id'], PDO::PARAM_INT);
				$oQuery->bindParam('session', session_id(), PDO::PARAM_STR);
				$oQuery->bindParam('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
				$oQuery->bindParam('date', time(), PDO::PARAM_INT);

				return $oQuery->execute();
			}
      catch (\PDOException $p) {
        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth('0073 - ' . $e->getMessage());
        }

        AdvancedException::reportBoth('0074 - ' . $p->getMessage());
        exit('SQL error.');
      }
		}
    else
      return false;
	}

	/**
	 * Resend password.
	 *
	 * @access public
	 * @param string $sPassword new password if we want to resend it
	 * @return boolean|array status of query or user array
	 *
	 */
	public function resendPassword($sPassword = '') {
		$aData = User::getVerificationData($this->_aRequest['email']);
		return empty($aData['name']) ? false : User::setPassword($this->_aRequest['email'], $sPassword);
	}

	/**
	 * Resend verification.
	 *
	 * @access public
	 * @return boolean|array status of query or user array
	 *
	 */
	public function resendVerification() {
		$aData = User::getVerificationData($this->_aRequest['email']);
		return empty($aData['verification_code']) ? false : $aData;
	}

	/**
   * Destroy a user session and logout.
   *
   * @access public
   * @return boolean status of query
	 *
   */
  public function destroy() {
    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "sessions
                                      SET
                                        session = :session_null
                                      WHERE
                                        session = :session_id");

      $sNull = 'NULL';
      $iSessionId = session_id();
      $oQuery->bindParam('session_null', $sNull, PDO::PARAM_NULL);
      $oQuery->bindParam('session_id', $iSessionId, PDO::PARAM_STR);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0075 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0076 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }
}