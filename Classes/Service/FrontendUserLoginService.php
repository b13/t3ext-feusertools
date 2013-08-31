<?php

namespace B13\Feusertools\Service;

/***************************************************************
 *  Copyright notice - MIT License (MIT)
 *
 *  (c) 2013 b:dreizehn GmbH,
 * 		Benjamin Mack <benjamin.mack@b13.de>
 *  All rights reserved
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 ***************************************************************/
/**
 * mighty class to allow login for frontend users
 * for other extensions
 * using switch user functionality
 * the switch User functionality is a hook to send certain paramters with
 * and goes to a more low-level functionality "prepareUserLogin" and "processUserLogin"
 * which can be used separately (e.g. a login after a registration)
 */
class FrontendUserLoginService {

	/**
	 * first, find any user with the same ses_hashlock
	 * if that matches, allow to login via 
	 * 
	 * @param array $parameters
	 * @param TSFE $parentObject the parentObject
	 */
	public function switchUser($parameters, &$parentObject) {
		$frontendUserAuthObject = $parentObject->fe_user;
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('switchuser') && \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('authuser')) {
		
			$userUidToLogIn = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('switchuser');
			$userUidWithValidSession = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('authuser');
			
				// get the hash clause from the dummy record, just create a tempuser/dummy record
			$tempuser = array();
			$dummyRecord = $frontendUserAuthObject->getNewSessionRecord($tempuser);

				// we don't use ->isExistingSessionRecord() as we also want to check the parent ID
				// $this->fe_user->fetchSessionData();
			$authUserSession = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'*',
				'fe_sessions',
				'ses_hashlock=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($dummyRecord['ses_hashlock'], 'fe_sessions')
				. ' AND ses_iplock=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($dummyRecord['ses_iplock'], 'fe_sessions')
				. ' AND ses_userid=' . intval($userUidWithValidSession)
			);

				// auth user exists in the session => logged in and verified
			if (count($authUserSession) > 0) {
				$this->prepareUserLogin($userUidToLogIn);
				$this->processUserLogin(FALSE);
			}
		}
	}

	/**
	 * main function to find a user record
	 * and call the login functionality of the TYPO3 Core frontend
	 *
	 * @param $userUid the user ID of the fe_user table
	 * @return void
	 */	
	public function prepareUserLogin($userUid) {
		$userRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			'fe_users',
			'uid=' . intval($userUid) . ' AND deleted=0 AND hidden=0 AND starttime<=' . time() . ' AND (endtime=0 OR endtime>' . time() . ')'
		);

			// log in to the website
		$loginData = array(
			'logintype' => 'login',
			'user' => $userRecord['username'],
			'pass' => $userRecord['password'],
		);

		// pre 6.0 variables
		unset($GLOBALS['T3_SERVICES']['auth']['tx_rsaauth_sv1']);
		unset($GLOBALS['T3_SERVICES']['auth']['tx_saltedpasswords_sv1']);

		// disable any features that would process 
		unset($GLOBALS['T3_SERVICES']['auth']['TYPO3\\CMS\\Rsaauth\\RsaAuthService']);
		unset($GLOBALS['T3_SERVICES']['auth']['TYPO3\\CMS\\Saltedpasswords\\SaltedPasswordService']);

		$GLOBALS['TSFE']->mergingWithGetVars($loginData);
		$GLOBALS['TSFE']->fe_user->checkPid = TRUE;
		$GLOBALS['TSFE']->fe_user->checkPid_value = $userRecord['pid'];
		$GLOBALS['TSFE']->fe_user->getMethodEnabled = TRUE;
		$GLOBALS['TSFE']->fe_user->security_level = $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] = 'normal';
	}

	/**
	 * initiates the authentication process of the frontend user
	 * and mimics the functionality from TSFE
	 * 
	 * @param $onlyAuthentication if true, the user retreival is not validated, but only the authentication process is run through
	 */
	public function processUserLogin($onlyAuthentication = FALSE) {
		if ($onlyAuthentication) {
			$GLOBALS['TSFE']->fe_user->checkAuthentication();
		} else {
			$GLOBALS['TSFE']->fe_user->start();
		}
		$GLOBALS['TSFE']->fe_user->fetchSessionData();
		$GLOBALS['TSFE']->storeSessionData();
	}

}