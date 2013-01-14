<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 b:dreizehn GmbH Stuttgart // Benjamin Mack (benni@typo3.org)
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
 * TCEmain hook to update username if email is set and vice versa
 */
class tx_feusertools_tcemain {

	public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$pObj) {
		if ($table == 'fe_users') {

				// username is set, but email isn't => set the email as well
			if (isset($incomingFieldArray['username']) && !isset($incomingFieldArray['email'])) {
				$incomingFieldArray['email'] = $incomingFieldArray['username'];

				// email is set, but username isn't => set the username as well
			} elseif (isset($incomingFieldArray['email']) && !isset($incomingFieldArray['username'])) {
				$incomingFieldArray['username'] = $incomingFieldArray['email'];
			}
		
		}
	}

}
