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

class ChangeUserTitle extends Module
{
	/**
	 * @var string The desired title.
	 */
	private $_title;

	/**
	 * ChangeOtherTitle::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('cut_name');
		$this->desc = Shop::getText('cut_desc');
		$this->price = 50;
	}

	function getUseInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('cot_title') . '
				</dt>
				<dd>
					<input type="text" name="newtitle" size="50" />
				</dd>
			</dl>';
	}

	function onUse()
	{
		global $user_info;

		// Somehow we missed the title?
		if (!isset($_REQUEST['newtitle']))
			fatal_error(Shop::getText('cot_empty_title'), false);

		checkSession();

		// The title
		$this->_title = Database::sanitize($_REQUEST['newtitle']);

		// Update the information
		updateMemberData($user_info['id'], ['usertitle' => $this->_title]);

		return '
			<div class="infobox">
				' . sprintf(Shop::getText('cut_success'), $this->_title) . '
			</div>';
	}
}