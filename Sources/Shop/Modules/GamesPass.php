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
use Shop\Helper\Format;
use Shop\Helper\Module;

if (!defined('SMF'))
	die('Hacking attempt...');

class GamesPass extends Module
{
	/**
	 * @var int Provide one day in seconds.
	 */
	private $_seconds;

	/**
	 * @var int The amount of days for GamesPass.
	 */
	private $_days;

	/**
	 * @var int The total amount of GamesPass subscription.
	 */
	private $_time;

	/**
	 * @var int Expiration date for GamesPass.
	 */
	private $_expires;

	/**
	 * GamesPass::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Sleepy Arcade';
		$this->authorWeb = 'https://www.simplemachines.org/community/index.php?action=profile;u=84438';
		$this->authorEmail = 'wdm2005@blueyonder.co.uk';
		$this->name = Shop::getText('gp_name');
		$this->desc = Shop::getText('gp_desc');
		$this->price = 50;
		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;

		// By default 30 days
		$this->item_info[1] = 30;

		// 1 day
		$this->_seconds = 86400;
	}

	function getAddInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('gp_setting1') . '
				</dt>
				<dd>
					<input type="number" min="1" id="info1" name="info1" value="' . $this->item_info[1] . '" />
				</dd>
			</dl>';
	}

	function onUse()
	{
		global $user_info;

		// By default 30 days
		if (empty($this->item_info[1]))
			$this->item_info[1] = 30;

		checkSession();

		// Get the time in seconds
		$this->_days = ($this->_seconds * $this->item_info[1]);

		// User still have access?
		if (!empty($user_info['gamesPass']) && $user_info['gamesPass'] > time())
			$this->_time = $user_info['gamesPass'] + $this->_days;
		// Expired.. Then calculate from this moment
		else
			$this->_time = time() + $this->_days;

		// Update the gamespass days
		updateMemberData($user_info['id'], ['gamesPass' => $this->_time]);

		// Give them the exact amount of days user now has
		$this->_expires = Format::gamespass($this->_time);
		
		return '
			<div class="infobox">
				' . sprintf(Shop::getText('gp_success'), $this->_expires) . '
			</div>';
	}
}