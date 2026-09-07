<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\View;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Format;
use Shop\Helper\Images;
use Shop\Helper\Log;
use Shop\Helper\Notify;

if (!defined('SMF'))
	die('No direct access...');

class Gift
{
	/**
	 * @var object Send notifications to the user receiving gifts.
	 */
	private $_notify;

	/**
	 * @var object Log any information regarding gifts.
	 */
	private $_log;
	
	/**
	 * @var array Save the section tabs.
	 */
	protected $_tabs = [];

	/**
	 * @var array Information regarding the gift.
	 */
	private $_gift_info = [];

	/**
	 * @var array Load user data.
	 */
	private $_member = [];

	/**
	 * @var int The inv ID of the gifted item OR the amount to send.
	 */
	private $_gift;

	/**
	 * Gift::__construct()
	 *
	 * Set the tabs for the section and create instance of needed objects
	 */
	function __construct()
	{
		global $modSettings;
	
		// Build the tabs for this section
		$this->tabs();

		// Prepare to log the gift
		$this->_log = new Log;

		// Notify
		$this->_notify = new Notify;

		// What if the Inventories are disabled?
		if (empty($modSettings['Shop_enable_gift']))
			fatal_error(Shop::getText('currently_disabled_gift'), false);

		// Check if user is allowed to access this section
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_canGift');

		// Inventory template
		loadTemplate('Shop/Inventory');
	}

	public function main()
	{
		global $context, $scripturl, $user_info, $modSettings, $memberContext;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_gift');
		$context['template_layers'][] = 'options';
		$context['template_layers'][] = 'shop_inventory_search';
		$context['sub_template'] = 'gift';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=gift',
			'name' => Shop::getText('main_gift'),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;
		// Form
		$context['form_url'] = '?action=shop;sa=gift2'.(isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'sendmoney' ? ';money' : '');

		// Can user view inventories?
		$context['shop']['view_inventory'] = allowedTo('shop_viewInventory');
		// Adding additional linktree
		$context['linktree'][] = [
			'url' => (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'sendmoney' ? $scripturl . '?action=shop;sa=sendmoney' : $scripturl . '?action=shop;sa=sendgift'),
			'name' => (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'sendmoney' ? sprintf(Shop::getText('gift_send_money'), $modSettings['Shop_credits_suffix']) : Shop::getText('gift_send_item')),
		];

		// Items list
		if (isset($_REQUEST['sa']) && ($_REQUEST['sa'] != 'sendmoney'))
			$context['shop_user_items_list'] = Database::Get(0,  100000,'s.name', 'stshop_inventory AS si', array_merge(Database::$inventory, Database::$items), 'WHERE si.userid = {int:user} AND si.trading = 0 AND s.status = 1', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = si.itemid)', ['user' => $user_info['id']]);

		// Send money string
		$context['shop']['send_money'] = sprintf(Shop::getText('gift_send_money'), Shop::getText('posting_credits_pocket'));

		// Do we have an ID already?, Let's find out the name of that user
		if (isset($_REQUEST['u']))
		{
			$userid = (int) $_REQUEST['u'];
			// Find out the member credits...
			$temp = loadMemberData($userid, false, 'profile');
			if (!empty($temp))
			{
				loadMemberContext($userid);
				$membername = $memberContext[$userid]['name'];
				$_REQUEST['membername'] = $membername;
			}
			else
				unset($_REQUEST['membername']);
		}

		// Load suggest.js
		loadJavaScriptFile('suggest.js', ['default_theme' => true, 'defer' => false, 'minimize' => true], 'smf_suggest');
	}

	public function tabs()
	{
		$this->_tabs = [
			'gift' => [
				'action' => ['gift', 'senditem'],
				'label' => Shop::getText('gift_send_item'),
			],
			'sendmoney' => [
				'action' => ['sendmoney'],
				'label' => sprintf(Shop::getText('gift_send_money'), Shop::getText('posting_credits_pocket')),
			],
		];
	}

	public function send()
	{
		global $context, $user_info, $modSettings, $scripturl, $memberContext;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_gift');
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=gift',
			'name' => Shop::getText('main_gift'),
		];

		// Check session
		checkSession();

		// You cannot get here without an item
		if (!isset($_REQUEST['item']) && !isset($_REQUEST['money']))
			fatal_error(Shop::getText('gift_no_item_found'), false);
		// Or an amount if sending money...
		elseif ((!isset($_REQUEST['amount']) || empty($_REQUEST['amount'])) && isset($_REQUEST['money']))
			fatal_error(Shop::getText('gift_no_amount'), false);
		// Anyway, couldn't get so far if for any reason there is no member to send the items/money
		elseif (!isset($_REQUEST['membername']) || empty($_REQUEST['membername']))
			fatal_error(Shop::getText('gift_unable_user'), false);

		$member_query = [];
		$member_parameters = [];
	
		// Got a user?
		if (empty($_REQUEST['membername']) || !isset($_REQUEST['membername']))
			fatal_error(Shop::getText('user_unable_tofind'), false);

		// Get the member name...
		$member_name = Database::sanitize($_REQUEST['membername']);

		// Construct the query
		if (!empty($member_name))
		{
			$member_query[] = 'LOWER(member_name) = {string:member_name}';
			$member_query[] = 'LOWER(real_name) = {string:member_name}';
			$member_parameters['member_name'] = $member_name;
		}

		// Execute
		if (!empty($member_query))
		{
			$this->_member = Database::Get(0, 1000, 'id_member', 'members', ['id_member'], 'WHERE (' . implode(' OR ', $member_query) . ')', true, '', $member_parameters);

			// We got a result?
			if (empty($this->_member))
				fatal_error(Shop::getText('user_unable_tofind'), false);
			else
			{
				// You cannot gift yourself DUH!
				if ($this->_member['id_member'] == $user_info['id'])
					fatal_error(Shop::getText('gift_not_yourself'), false);

				// Did the user leave a message? Nice :)
				$message = Database::sanitize($_REQUEST['message']);
				// The message subject
				$subject = Shop::getText('gift_notification_subject');

				// Gifting an item
				if (!isset($_REQUEST['money']) && isset($_REQUEST['item']))
				{
					// Item id
					$this->_gift = (int) $_REQUEST['item'];

					// Get item info
					$this->_gift_info = Database::Get('', '', '', 'stshop_inventory AS si', array_merge(Database::$inventory, Database::$items), 'WHERE si.id = {int:id} AND si.trading = 0 AND si.userid = {int:user}', true, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = si.itemid)', ['id' => $this->_gift , 'user' => $user_info['id']]);

					// We got valid information?
					if (empty($this->_gift_info) || empty($this->_gift_info['status']) || !empty($this->_gift_info['trading']))
						fatal_error(Shop::getText('item_notfound'), false);

					// PM body
					$body = sprintf(Shop::getText('gift_notification_message1'), $user_info['id'], $user_info['name'], $this->_gift_info['name'], $message);

					// Log the item
					$this->_log->items($user_info['id'], $this->_member['id_member'], $this->_gift_info['itemid'], $this->_gift, false, $message);

					// Send PM
					$this->_notify->pm($this->_member['id_member'], $subject, $body);
					// Deploy alert?
					if (!empty($modSettings['Shop_noty_items']))
						$this->_notify->alert($this->_member['id_member'], 'items', $this->_gift, ['shop_href' => ';sa=inventory', 'item_icon' => 'gifts_received']);
				}
				// Gifting money
				else
				{
					// Set the amount
					$this->_gift = (int) $_REQUEST['amount'];

					// Can the user send this gift?
					if (($user_info['shopMoney'] - $this->_gift) < 0)
						fatal_error(Shop::getText('gift_not_enough_pocket'), false);
					// No trolls please
					elseif ($this->_gift <= 0)
						fatal_error(Shop::getText('gift_not_negative_or_zero'), false);

					// Find out the member credits...
					$temp = loadMemberData($this->_member['id_member'], false, 'profile');
					if (!empty($temp))
					{
						loadMemberContext($this->_member['id_member']);
						$membermoney = $memberContext[$this->_member['id_member']]['shopMoney'];
					}
					else
						fatal_error(Shop::getText('user_unable_tofind'), false);

					// PM body
					$body = sprintf(Shop::getText('gift_notification_message2'), $user_info['id'], $user_info['name'], $modSettings['Shop_credits_suffix'], Format::cash($this->_gift), Format::cash($membermoney + $this->_gift), $message);

					// Log the item
					$this->_log->credits($user_info['id'], $this->_member['id_member'], $this->_gift, false, $message);

					// Send PM
					$this->_notify->pm($this->_member['id_member'], $subject, $body);
					// Deploy alert?
					if (!empty($modSettings['Shop_noty_credits']))
						$this->_notify->alert($this->_member['id_member'], 'credits', $user_info['id'], ['item_icon' => 'money_received', 'amount' => Format::cash($this->_gift)]);
				}

				// If there are no errors, then it was a success?
				redirectexit('action=shop;sa='. (isset($_REQUEST['money']) ? 'sendmoney' : 'gift') . ';success');
			}
		}
	}
}