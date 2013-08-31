<?php

$classesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('feusertools', 'Classes');
return array(
	'B13\\Feusertools\\Service\\frontenduserloginservice' => $classesPath . 'Service/FrontendUserLoginService.php',
	'B13\\Feusertools\\Hooks\\DataHandler' => $classesPath . 'Hooks/DataHandler.php',
);
