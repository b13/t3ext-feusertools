<?php
if (!defined('TYPO3_MODE')) {
 	die ('Access denied.');
}


if (TYPO3_MODE == 'BE') {
	$extensionConfiguration = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['feusertools']);
	
		// add the username/email hook
	if ($extensionConfiguration['usernameEmailSynchronization'] == 1) {
		$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['feusertools'] = 'tx_feusertools_tcemain';
	}
}
