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

class ChangeOtherTitle extends Module
{
	/**
	 * @var string The name of the user.
	 */
	private $_membername;

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
		$this->authorName = 'Diego Andrés';
		$this->authorWeb = 'https://smftricks.com/';
		$this->authorEmail ='admin@smftricks.com';
		$this->name = Shop::getText('cot_name');
		$this->desc = Shop::getText('cot_desc');
		$this->price = 200;
		$this->require_input = true;
		$this->can_use_item = true;
	}

	function getUseInput()
	{
		global $context;

		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('inventory_member_name') . '<br />
					<span class="smalltext">' . Shop::getText('cot_find_desc') . '</span>
				</dt>
				<dd>
					<input type="text" name="membername" id="membername" />
					<div id="membernameItemContainer"></div>
				</dd>
				<dt>
					' . Shop::getText('cot_title') . '
				</dt>
				<dd>
					<input type="text" name="newtitle" size="50" />
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

		// Somehow we missed the title?
		if (!isset($_REQUEST['newtitle']))
			fatal_error(Shop::getText('cot_empty_title'), false);

		checkSession();
		$member_query = [];
		$member_parameters = [];

		// Get the member name...
		$this->_membername = Database::sanitize($_REQUEST['membername']);

		// The title
		$this->_title = Database::sanitize($_REQUEST['newtitle']);

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
			$memResult = Database::Get(0, 1000, 'id_member', 'members', ['id_member'], 'WHERE (' . implode(' OR ', $member_query) . ')', true, '', $member_parameters);

			// We got a result?
			if (empty($memResult))
				fatal_error(Shop::getText('user_unable_tofind'), false);

			// This item is not to use on yourself
			elseif ($memResult['id_member'] == $user_info['id'])
				fatal_error(Shop::getText('cot_notown_title'), false);

			// Update the title
			updateMemberData($memResult['id_member'], ['usertitle' => $this->_title]);

			return '
				<div class="infobox">
					' . sprintf(Shop::getText('cot_success'), $_REQUEST['username'], $this->_title) . '
				</div>';
		}
	}
}