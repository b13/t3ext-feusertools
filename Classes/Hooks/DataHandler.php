<?php

namespace B13\Feusertools\Hooks;

/***************************************************************
 *  Copyright notice - MIT License (MIT)
 *
 *  (c) 2012 - 2013 b:dreizehn GmbH,
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
 * TCEmain hook to update username if email is set
 * and vice versa
 */
class DataHandler {

	/**
	 * checks if a fe_user record is edited. If the email is changed, the username is updated accordingly
	 * same goes if the username was changed, the email is changed as well
	 * very helpful if the site has users which usernames are identical to emails
	 *
	 *
	 */
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
