<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 b:dreizehn GmbH Stuttgart // Benjamin Mack (benni@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * mighty class to allow login for frontend users
 * for other extensions
 * using 
 * switch user functionality
 *
 */
class Tx_FeUserTools_Service_FrontendUserLoginService {

	/**
	 * first, find any user with the same ses_hashlock
	 * if that matches, allow to login via 
	 * 
	 * @param array $parameters
	 * @param TSFE $parentObject the parentObject
	 */
	public function switchUser($parameters, &$parentObject) {
		$frontendUserAuthObject = $parentObject->fe_user;
		if (t3lib_div::_GP('switchuser') && t3lib_div::_GP('authuser')) {
		
			$userUidToLogIn = t3lib_div::_GP('switchuser');
			$userUidWithValidSession = t3lib_div::_GP('authuser');
			
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
	
	
	public function prepareUserLogin($userUid) {
		$userRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			'fe_users',
			'uid=' . intval($userUid)
		);

			// log in to the website
		$loginData = array(
			'logintype' => 'login',
			'user' => $userRecord['username'],
			'pass' => $userRecord['password'],
		);

		unset($GLOBALS['T3_SERVICES']['auth']['tx_rsaauth_sv1']);
		unset($GLOBALS['T3_SERVICES']['auth']['tx_saltedpasswords_sv1']);
		$GLOBALS['TSFE']->mergingWithGetVars($loginData);
		$GLOBALS['TSFE']->fe_user->checkPid = TRUE;
		$GLOBALS['TSFE']->fe_user->checkPid_value = $userRecord['pid'];
		$GLOBALS['TSFE']->fe_user->getMethodEnabled = TRUE;
		$GLOBALS['TSFE']->fe_user->security_level = $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] = 'normal';

	}
	
	
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