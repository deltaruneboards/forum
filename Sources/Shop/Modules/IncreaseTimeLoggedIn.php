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
use Shop\Helper\Module;

if (!defined('SMF'))
	die('Hacking attempt...');

class IncreaseTimeLoggedIn extends Module
{
	/**
	 * @var int Second for 1 hour.
	 */
	private $_day;

	/**
	 * @var int Total time logged in.
	 */
	private $_time;

	/**
	 * IncreaseTimeLoggedIn::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('itli_name');
		$this->desc = Shop::getText('itli_desc');
		$this->price = 50;
		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
		
		// 12 hours by default
		$this->item_info[1] = 12;

		// 1 hour
		$this->_day = 3600;
	}
	
	function getAddInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('itli_setting1') . '
				</dt>
				<dd>
					<input type="number" min="1" id="info1" name="info1" value="' . $this->item_info[1] . '" /> ' . Shop::getText('itli_hours') . '
				</dd>
			</dl>';
	}

	function onUse()
	{
		global $user_info;

		checkSession();

		// Add the time to their current total
		$this->_time = (int) ($user_info['total_time_logged_in'] + ($this->item_info[1] * $this->_day));

		// Update total time logged in
		updateMemberData($user_info['id'], ['total_time_logged_in' => $this->_time]);
		
		return '
			<div class="infobox">
				' . sprintf(Shop::getText('itli_success'), $this->item_info[1]) . '
			</div>';
	}
}