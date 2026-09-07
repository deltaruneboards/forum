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

class RandomMoney extends Module
{
	/**
	 * @var string The amount of credits user will lose/win.
	 */
	private $_credits;

	/**
	 * RandomMoney::__construct()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('rm_name');
		$this->desc = Shop::getText('rm_desc');
		$this->price = 75;
		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;

		// Minimum default is -200
		$this->item_info[1] = -200;

		// Maximum default is 200
		$this->item_info[2] = 200;
	}

	function getAddInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('rm_setting1') . '
				</dt>
				<dd>
					<input type="number" id="info1" name="info1" value="' . $this->item_info[1] . '" />
				</dd>
				<dt>
					' . Shop::getText('rm_setting2') . '
				</dt>
				<dd>
					<input type="number" min="1" id="info2" name="info2" value="' . $this->item_info[2] . '" />
				</dd>
			</dl>';
	}

	function onUse()
	{
		global $user_info;

		checkSession();

		// Get a random value between our limits
		$this->_credits = mt_rand($this->item_info[1], $this->item_info[2]);

		// Did user lose money?
		if ($this->_credits != 0)
			updateMemberData($user_info['id'], ['shop' . ($user_info['shopMoney'] < abs($this->_credits) && $this->_credits < 0 ? 'Bank' : 'Money') => ($user_info['shop' . ($user_info['shopMoney'] < abs($this->_credits) && $this->_credits < 0 ? 'Bank' : 'Money')] + $this->_credits)]);

		return '
			<div class="' . ($this->_credits > 0 ? 'info' : 'error') . 'box">
				'. ($this->_credits != 0 ? sprintf(Shop::getText(($this->_credits < 0 ? ('rm_lost_' . ($user_info['shopMoney'] < abs($this->_credits) && $this->_credits < 0 ? 'bank' : 'pocket')) : 'rm_success' )), Format::cash(abs($this->_credits))) : Shop::getText('rm_zero')) . '
			</div>';
	}
}