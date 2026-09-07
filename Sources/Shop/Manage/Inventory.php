<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Manage;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Format;
use Shop\Helper\Log;
use Shop\Helper\Notify;
use Shop\View\Inventory as Search;

if (!defined('SMF'))
	die('No direct access...');

class Inventory extends Dashboard
{
	/**
	 * @var object Send notifications to the user receiving gifts.
	 */
	private $_notify;

	/**
	 * @var object Log any information regading gifts.
	 */
	private $_log;

	/**
	 * Inventory::__construct()
	 *
	 * Create the array of subactions and load necessary extra language files
	 */
	function __construct()
	{
		global $modSettings;

		// Notify
		$this->_notify = new Notify;

		// Prepare to log this information
		$this->_log = new Log;

		// Array of sections
		$this->_subactions = [
			'usercredits' => 'credits',
			'usercredits2' => 'credits2',
			'groupcredits' => 'group',
			'groupcredits2' => 'group2',
			'useritems' => 'items',
			'useritems2' => 'items2',
			'search' => 'search',
			'search2' => 'search2',
			'userinv' => 'view_inventory',
			'delete' => 'delete',
			'restock' => 'restock',
			'restock2' => 'restock2',
		];

		// Disabled sections?
		if (empty($modSettings['Shop_enable_shop']))
		{
			unset($this->_subactions['search']);
			unset($this->_subactions['search2']);
			unset($this->_subactions['userinv']);
			unset($this->_subactions['delete']);
			unset($this->_subactions['restock']);
			unset($this->_subactions['restock2']);
		}
		if (empty($modSettings['Shop_enable_shop']) || empty($modSettings['Shop_enable_gift']))
		{
			unset($this->_subactions['useritems']);
			unset($this->_subactions['useritems2']);
		}

		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'usercredits';
	}

	public function main()
	{
		global $context;

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_inventory'),
			'description' => Shop::getText('tab_inventory_desc'),
			'tabs' => [
				'usercredits' => ['description' => Shop::getText('inventory_usercredits_desc')],
				'groupcredits' => ['description' => Shop::getText('inventory_groupcredits_desc')],
				'useritems' => ['description' => Shop::getText('inventory_useritems_desc')],
				'search' => ['description' => Shop::getText('tab_inventory_desc')],
				'restock' => ['description' => Shop::getText('inventory_restock_desc')],
			],
		];
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa].'#');
	}

	public function credits()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] =  Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_usercredits');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('inventory_usercredits_desc'),
		];
		loadTemplate('Shop/ShopAdmin');
		$context['sub_template'] = 'send_credits';
		$context['template_layers'][] = 'send';

		// Load suggest.js
		loadJavaScriptFile('suggest.js', ['default_theme' => true, 'defer' => false, 'minimize' => true], 'smf_suggest');
	}

	public function credits2()
	{
		global $context, $user_info, $modSettings;

		// Set all the page stuff
		$context['page_title'] =  Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_usercredits');
		$context[$context['admin_menu_name']]['current_subsection'] = 'usercredits';

		// Did we get a member?
		if (empty($_REQUEST['membername']) && empty($_REQUEST['memberid']))
			fatal_error(Shop::getText('user_unable_tofind'), false);

		// You need to send an amount...
		elseif(empty($_REQUEST['amount']))
			fatal_error(Shop::getText('gift_no_amount'), false);

		checkSession();

		$amount = (int) $_REQUEST['amount'];
		$member_query = [];
		$member_parameters = [];

		// Get all the members to be added... taking into account names can be quoted ;)
		$_REQUEST['membername'] = strtr(Database::sanitize($_REQUEST['membername']), ['&quot;' => '"']);
		preg_match_all('~"([^"]+)"~', $_REQUEST['membername'], $matches);
		$member_names = array_unique(array_merge($matches[1], explode(',', preg_replace('~"[^"]+"~', '', $_REQUEST['membername']))));

		foreach ($member_names as $index => $member_name)
		{
			$member_names[$index] = trim(Database::strtolower($member_names[$index]));

			if (strlen($member_names[$index]) == 0)
				unset($member_names[$index]);
		}

		// Any passed by ID?
		$member_ids = [];
		if (!empty($_REQUEST['memberid']))
			foreach ($_REQUEST['memberid'] as $id)
				if ($id > 0)
					$member_ids[] = (int) $id;

		// Construct the query elements.
		if (!empty($member_ids))
		{
			$member_query[] = 'id_member IN ({array_int:member_ids})';
			$member_parameters['member_ids'] = $member_ids;
		}
		if (!empty($member_names))
		{
			$member_query[] = 'LOWER(member_name) IN ({array_string:member_names})';
			$member_query[] = 'LOWER(real_name) IN ({array_string:member_names})';
			$member_parameters['member_names'] = $member_names;
		}

		$receivers = [];
		$members = [];
		if (!empty($member_query))
		{
			// List of users
			$receivers = Database::Get(0, 1000, 'id_member', 'members', ['id_member'], 'WHERE (' . implode(' OR ', $member_query) . ')', false, '', $member_parameters);

			// Nothing...
			if (empty($receivers))
				fatal_error(Shop::getText('user_unable_tofind'), false);

			// Handle the action
			else
			{
				// Tidy up
				foreach ($receivers as $key => $memID)
					$members[$key] = $memID['id_member'];

				// Handle everything
				$this->_log->credits($user_info['id'], $members, $amount, true);

				// Deploy alert?
				if (!empty($modSettings['Shop_noty_credits']))
					$this->_notify->alert($members, 'credits', $user_info['id'], ['item_icon' => 'money_received', 'amount' => Format::cash($amount)]);

				// Redirect to a nice message of success
				redirectexit('action=admin;area=shopinventory;sa=usercredits;updated');
			}
		}
	}

	public function group()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_groupcredits');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('inventory_groupcredits_desc'),
		];
		loadTemplate('Shop/ShopAdmin');
		$context['sub_template'] = 'groups';
		$context['template_layers'][] = 'send';

		// Get all non post-based membergroups
		$context['shop_usergroups'] = Database::Get(0, 10000, 'group_name', 'membergroups', ['id_group AS id', 'group_name AS name'], 'WHERE min_posts = -1 AND id_group <> 3');
	}

	public function group2()
	{
		global $context, $user_info, $modSettings;

		// Keep the tab active
		$context[$context['admin_menu_name']]['current_subsection'] = 'groupcredits';

		// Need to select at least one group
		if (empty($_REQUEST['usergroup']))
			fatal_error(Shop::getText('inventory_groupcredits_nogroups'), false);
		// You need to send an amount...
		elseif(empty($_REQUEST['amount']))
			fatal_error(Shop::getText('Shop_gift_no_amount'), false);

		checkSession();

		// Make sure all IDs are numeric
		foreach ($_REQUEST['usergroup'] as $key => $value)
			$_REQUEST['usergroup'][$key] = (int) $value;

		// Amount to add/ubstract
		$amount = ($_REQUEST['m_action'] == 'sub' ? (-1) : 1) * (int) $_REQUEST['amount'];

		$receivers = [];
		$members = [];
		// Get the list of members
		$receivers = Database::Get(0, 1000, 'id_member', 'members', ['id_member'], 'WHERE id_group IN ({array_int:usergroup}) OR additional_groups IN ({array_int:usergroup})', false, '', ['usergroup' => $_REQUEST['usergroup']]);

		// No members?
		if (empty($receivers))
			fatal_error(Shop::getText('inventory_usergroup_unable_tofind'), false);
		// Let's get the query
		else
		{
			// Make this array less complicated for the log to read it
			foreach ($receivers as $key => $memID)
				$members[$key] = $memID['id_member'];

			// Handle  everything and save it in the log
			$this->_log->credits($user_info['id'], $members, $amount, true);

			// Deploy alert?
			if (!empty($modSettings['Shop_noty_credits']))
				$this->_notify->alert($members, 'credits', $user_info['id'], ['item_icon' => 'money_received', 'amount' => Format::cash($amount), 'allow_self' => true]);

			// Redirect to a nice message of successful
			redirectexit('action=admin;area=shopinventory;sa=groupcredits;success');
		}
	}

	public function search()
	{
		global $context;

		// Check if user is allowed to access this section
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_viewInventory');

		// Load templates
		loadTemplate('Shop/ShopAdmin');
		loadTemplate('Shop/Inventory');

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_inventory') . ' - ' . Shop::getText('inventory_search');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('tab_inventory_desc'),
		];
		$context['template_layers'][] = 'send';
		$context['template_layers'][] = 'shop_inventory_search';
		$context['sub_template'] = 'shop_inventory_search';
		// Form
		$context['form_url'] = '?action=admin;area=shopinventory;sa=search2';

		// Load suggest.js
		loadJavaScriptFile('suggest.js', ['default_theme' => true, 'defer' => false, 'minimize' => true], 'smf_suggest');
	}

	public function search2()
	{
		// This is a nasty and lazy hack
		Search::search_inventory();
	}

	public function view_inventory()
	{
		global $context, $user_info, $memberContext, $sourcedir;

		// Keep the tab active
		$context[$context['admin_menu_name']]['current_subsection'] = 'search';

		loadLanguage('Shop/Shop');

		// Get the actual owner of this inventory
		$memberResult = loadMemberData((isset($_REQUEST['user']) ? $_REQUEST['user'] : ((isset($_REQUEST['u']) ? $_REQUEST['u'] : $user_info['id']))), isset($_REQUEST['user']), 'profile');
	
		// Don't mind me, just checking if it's a valid profile
		if (!$memberResult)
			fatal_error(Shop::getText('user_unable_tofind'), false);

		// If all went well, we have a valid member ID!
		list ($memID) = $memberResult;
		
		// Let's have some information about this member ready, too.
		loadMemberContext($memID);
		$context['member'] = $memberContext[$memID];
		$context['user']['is_owner'] = $memID == $user_info['id'];

		// Set all the page stuff
		require_once($sourcedir . '/Subs-List.php');
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'inventory';
		$context['page_title'] = Shop::getText('tab_inventory') . ' - ' . Shop::getText('inventory_userinv');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('tab_inventory_desc'),
		];
		loadTemplate('Shop/ShopAdmin');
		$context['template_layers'][] = 'send';
		$context['form_url'] = '?action=admin;area=shopinventory;sa=userinv'. (!empty($context['user']['is_owner']) ? '' : ';u='. $context['member']['id']);

		// Load the list
		$listOptions = Search::inventory_list($memberContext[$memID], $context['form_url']);

		// Remove unnecessary stuff from this view
		unset($listOptions['columns']['item_use']);
		unset($listOptions['columns']['item_fav']);
		unset($listOptions['columns']['item_trade']);
		unset($listOptions['additional_rows']['traded']);

		// Delete
		$listOptions['columns']['delete'] = [
			'header' => [
				'value' => Shop::getText('delete', false). ' <input type="checkbox" onclick="invertAll(this, this.form, \'delete[]\');" class="input_check" />',
				'class' => 'centertext',
			],
			'data' => [
				'class' => 'centertext',
				'style' => 'width: 9%',
				'sprintf' => [
					'format' => '<input type="checkbox" name="delete[]" value="%1$d" class="check" />',
					'params' => [
						'id' => false,
					],
				],
			],
		];
		$listOptions['form'] = [
			'href' => '?action=admin;area=shopinventory;sa=delete;u=' . $context['member']['id'],
			'hidden_fields' => [
				$context['session_var'] => $context['session_id'],
			],
		];
		$listOptions['additional_rows']['submit'] = [
			'position' => 'below_table_data',
			'value' => '<input type="submit" size="18" value="'.Shop::getText('delete', false). '" class="button" />',
		];
		$listOptions['additional_rows']['deleted'] = [
			'position' => 'above_column_headers',
			'value' => (isset($_REQUEST['deleted']) ? '<div class="clear"></div><div class="infobox">'.sprintf(Shop::getText('inventory_items_deleted'), $context['member']['name']).'</div>' : ''),
		];

		createList($listOptions);
	}

	public function delete()
	{
		global $context, $user_info;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_inventory') . ' - '. Shop::getText('items_delete');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('items_delete'),
		];

		checkSession();

		// If nothing was chosen to delete
		if (!isset($_REQUEST['delete']))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Make sure all IDs are numeric
		foreach ($_REQUEST['delete'] as $key => $value)
			$_REQUEST['delete'][$key] = (int) $value;

		// Delete selected items
		Database::Delete('stshop_inventory', 'id', $_REQUEST['delete'], ' AND userid = ' .$_REQUEST['u']);

		// Send the user to the items list with a message
		redirectexit('action=admin;area=shopinventory;sa=userinv' . ($user_info['id'] == $_REQUEST['u'] ? '' : ';u='.$_REQUEST['u']) . ';deleted');
	}
	
	public function items()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] =  Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_useritems');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('inventory_useritems_desc'),
		];
		loadTemplate('Shop/ShopAdmin');
		$context['sub_template'] = 'send_items';
		$context['template_layers'][] = 'send';

		// List of items
		$context['shop_items_list'] = Database::Get(0, 100000, 's.name', 'stshop_items AS s', Database::$items, 'WHERE s.status = 1 AND s.stock > 0');

		// Load suggest.js
		loadJavaScriptFile('suggest.js', ['default_theme' => true, 'defer' => false, 'minimize' => true], 'smf_suggest');
	}

	public function items2()
	{
		global $context, $user_info, $modSettings;

		// Set all the page stuff
		$context['page_title'] =  Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_useritems');
		$context[$context['admin_menu_name']]['current_subsection'] = 'useritems';

		// Did we get a member?
		if (empty($_REQUEST['membername']) && empty($_REQUEST['memberid']))
			fatal_error(Shop::getText('user_unable_tofind'), false);

		// You need to send an item...
		elseif (empty($_REQUEST['item']))
			fatal_error(Shop::getText('gift_no_item'), false);

		checkSession();

		$item = (int) $_REQUEST['item'];
		$member_query = [];
		$member_parameters = [];

		// Get item info
		$item_info = Database::Get('', '', '', 'stshop_items AS s', Database::$items, 'WHERE s.itemid = {int:id} AND s.stock > 0', true, '', ['id' => $item]);

		// That item available and didn't empty it's stock?
		if (empty($item_info))
			fatal_error(Shop::getText('item_notfound'), false);

		// Get all the members to be added... taking into account names can be quoted ;)
		$_REQUEST['membername'] = strtr(Database::sanitize($_REQUEST['membername']), ['&quot;' => '"']);
		preg_match_all('~"([^"]+)"~', $_REQUEST['membername'], $matches);
		$member_names = array_unique(array_merge($matches[1], explode(',', preg_replace('~"[^"]+"~', '', $_REQUEST['membername']))));

		foreach ($member_names as $index => $member_name)
		{
			$member_names[$index] = trim(Database::strtolower($member_names[$index]));

			if (strlen($member_names[$index]) == 0)
				unset($member_names[$index]);
		}

		// Any passed by ID?
		$member_ids = [];
		if (!empty($_REQUEST['memberid']))
			foreach ($_REQUEST['memberid'] as $id)
				if ($id > 0)
					$member_ids[] = (int) $id;

		// Construct the query elements.
		if (!empty($member_ids))
		{
			$member_query[] = 'id_member IN ({array_int:member_ids})';
			$member_parameters['member_ids'] = $member_ids;
		}
		if (!empty($member_names))
		{
			$member_query[] = 'LOWER(member_name) IN ({array_string:member_names})';
			$member_query[] = 'LOWER(real_name) IN ({array_string:member_names})';
			$member_parameters['member_names'] = $member_names;
		}

		$receivers = [];
		$members = [];
		if (!empty($member_query))
		{
			// List of users
			$receivers = Database::Get(0, 1000, 'id_member', 'members', ['id_member'], 'WHERE (' . implode(' OR ', $member_query) . ')', false, '', $member_parameters);

			// Nothing...
			if (empty($receivers))
				fatal_error(Shop::getText('user_unable_tofind'), false);

			// Handle the action
			else
			{
				// Tidy up
				foreach ($receivers as $key => $memID)
					$members[$key] = $memID['id_member'];

				// Check the item info
				if ($item_info['stock'] < count($members))
					fatal_error(Shop::getText('inventory_useritems_nostock'), false);

				// Handle everything
				$this->_log->items($user_info['id'], $members, $item_info['itemid'], 0, true);

				// Deploy alert?
				if (!empty($modSettings['Shop_noty_items']))
					$this->_notify->alert($members, 'items', $user_info['id'], ['shop_href' => '?action=shop;sa=inventory', 'item_icon' => 'top_gifts_r']);

				// Redirect to a nice message of success
				redirectexit('action=admin;area=shopinventory;sa=useritems;updated');
			}
		}
	}

	public function restock()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] =  Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_restock');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('inventory_restock_desc'),
		];
		loadTemplate('Shop/ShopAdmin');
		$context['sub_template'] = 'restock';
		$context['template_layers'][] = 'send';

		// List of items
		$context['shop_items_list'] = Database::Get(0, 100000, 's.name', 'stshop_items AS s', Database::$items, 'WHERE s.status = 1');
	}

	public function restock2()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] =  Shop::getText('tab_inventory') . ' - '. Shop::getText('inventory_restock');
		$context[$context['admin_menu_name']]['current_subsection'] = 'restock';

		// If user selected some specific items, we should have at least one...
		if (($_REQUEST['whatitems'] == 'selected') && (!isset($_REQUEST['restockitem']) || empty($_REQUEST['restockitem'])))
			fatal_error(Shop::getText('restock_error_noitems'), false);

		$stock = !empty($_REQUEST['stock']) ? (int) $_REQUEST['stock'] : 0;
		$restock = !empty($_REQUEST['add']) ? (int) $_REQUEST['add'] : 0;

		if ($_REQUEST['whatitems'] == 'selected')
			foreach ($_REQUEST['restockitem'] as $key => $value)
				$_REQUEST['restockitem'][$key] = (int) $value;
		
		// Update the stock
		Database::Update('stshop_items', ['restock' => $restock, 'limit' => $stock, 'ids' => $_REQUEST['restockitem']], 'stock = stock + {int:restock},', 'WHERE'. ($_REQUEST['whatitems'] == 'all' ? ' stock <= {int:limit}' : ' itemid IN ({array_int:ids})'));

		// Success!
		redirectexit('action=admin;area=shopinventory;sa=restock;success');
	}
}