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

class DecreasePostCount extends Module
{
	/**
	 * @var string The name of the user.
	 */
	private $_membername;

	/**
	 * DecreasePostCount::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('dp_name');
		$this->desc = Shop::getText('dp_desc');
		$this->price = 200;
		$this->require_input = true;
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
					' . Shop::getText('dp_setting1') . '
				</dt>
				<dd>
					<input type="number" min="1" id="info1" name="info1" value="' . $this->item_info[1] . '" />
				</dd>
			</dl>';
	}

	function getUseInput()
	{
		global $context;

		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('inventory_member_name') . '<br />
					<span class="smalltext">' . Shop::getText('dp_find_desc') . '</span>
				</dt>
				<dd>
					<input type="text" name="membername" id="membername" />
					<div id="membernameItemContainer"></div>
				</dd>
			</dl>
			<script>
				var oAddMemberSuggest = new smc_AutoSuggest({
					sSelf: \'oAddMemberSuggest\',
					sSessionId: \''. $context['session_id']. '\',
					sSessionVar: \''. $context['session_var']. '\',
					sSuggestId: \'to_suggest\',
					sControlId: \'membername\',
					sSearchType: \'member\',
					sPostName: \'memberid\',
					sURLMask: \'action=profile;u=%item_id%\',
					sTextDeleteItem: \''. Shop::getText('autosuggest_delete_item', false). '\',
					sItemListContainerId: \'membernameItemContainer\'
				});
			</script>';
	}

	function onUse()
	{
		global $user_info;

		// Make sure we got an user
		if (empty($_REQUEST['membername']) || !isset($_REQUEST['membername']))
			fatal_error(Shop::getText('user_unable_tofind'), false);

		checkSession();
		$member_query = [];
		$member_parameters = [];

		// Get the member name...
		$this->_membername = Database::sanitize($_REQUEST['membername']);

		// Construct the query
		if (!empty($this->_membername))
		{
			$member_query[] = 'LOWER(member_name) = {string:member_name}';
			$member_query[] = 'LOWER(real_name) = {string:member_name}';
			$member_parameters['member_name'] = $this->_membername;
		}

		// Execute
		if (!empty($member_query))
		{
			$memResult = Database::Get(0, 1000, 'id_member', 'members', ['id_member', 'posts'], 'WHERE (' . implode(' OR ', $member_query) . ')', true, '', $member_parameters);

			// We got a result?
			if (empty($memResult))
				fatal_error(Shop::getText('user_unable_tofind'), false);

			// This item is not to use on yourself
			elseif ($memResult['id_member'] == $user_info['id'])
				fatal_error(Shop::getText('cot_dp_yourself'), false);

			// Update post count
			updateMemberData($memResult['id_member'], ['posts' => ($memResult['posts'] - $this->item_info[1])]);

			return '
				<div class="infobox">
					' . sprintf(Shop::getText('dp_success'), $this->_membername, $this->item_info[1]) . '
				</div>';
		}
	}
}