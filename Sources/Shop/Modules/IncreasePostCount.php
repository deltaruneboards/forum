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

class IncreasePostCount extends Module
{
	/**
	 * IncreasePostCount::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('ip_name');
		$this->desc = Shop::getText('ip_desc');
		$this->price = 50;
		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;

		// 100 posts by default
		$this->item_info[1] = 100;
	}

	function getAddInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('ip_setting1') . '
				</dt>
				<dd>
					<input type="number" min="1" id="info1" name="info1" value="' . $this->item_info[1] . '" />
				</dd>
			</dl>';
	}

	function onUse()
	{
		global $user_info;

		checkSession();

		// Increase posts
		updateMemberData($user_info['id'], ['posts' => ($this->item_info[1] + $user_info['posts'])]);
		
		return '
			<div class="infobox">
				' . sprintf(Shop::getText('ip_success'), $this->item_info[1]) . '
			</div>';
	}
}