<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Modules;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Module;

if (!defined('SMF'))
	die('Hacking attempt...');

class ChangeUsername extends Module
{
	/**
	 * @var string The username.
	 */
	private $_username;

	/**
	 * ChangeUsername::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('cu_name');
		$this->desc = Shop::getText('cu_desc');
		$this->price = 50;
	}

	function getUseInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('cu_new_username') . '<br />
					<span class="smalltext">' . Shop::getText('cu_new_username_desc') . '</span>
				</dt>
				<dd>
					<input type="text" id="newusername" name="newusername" size="60" />
				</dd>
			</dl>';
	}

	function onUse()
	{
		global $user_info, $sourcedir;

		// Make sure we got a name
		if (empty($_REQUEST['newusername']) || !isset($_REQUEST['newusername']))
			fatal_error(Shop::getText('cu_error_empty'), false);

		// The new username then
		$this->_username = Database::sanitize($_REQUEST['newusername']);

		checkSession();

		// It's not a matter of size, but that's not good enough
		if (trim($this->_username) == '')
			fatal_error(Shop::getText('cu_error_empty'), false);
		// It's too long! :o
		elseif (Database::strlen($this->_username) > 25)
			fatal_error(Shop::getText('cu_error_long'), false);
		// Well, we wanted a change for once
		elseif ($user_info['username'] == $this->_username)
			fatal_error(Shop::getText('cu_error_same'), false);
		// One last detail
		else
		{
			// Check for reserved name
			require_once($sourcedir . '/Subs-Members.php');

			if (isReservedName($this->_username, $user_info['id'], true))
				fatal_error(Shop::getText('cu_error_taken'), false);
		}

		// Update the username
		updateMemberData($user_info['id'], ['member_name' => $this->_username]);

		return '
			<div class="infobox">
				' . sprintf(Shop::getText('cu_success'), $this->_username) . '
			</div>';
	}
}