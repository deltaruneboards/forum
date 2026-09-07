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

class Trade
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
	 * @var int The item being traded.
	 */
	private $_trade;

	/**
	 * @var array Information about the item being traded.
	 */
	private $_item;

	/**
	 * @var int Carrying limit.
	 */
	private $_limit;

	/**
	 * @var int The cost/amount for a certain item.
	 */
	private $_amount;

	/**
	 * @var int The fee on the trade.
	 */
	private $_fee;

	/**
	 * @var array Load user data.
	 */
	private $_member = [];

	/**
	 * @var object Trade stats.
	 */
	protected $_stats;

	/**
	 * Trade::__construct()
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

		// The trade center is actually enabled?
		if (empty($modSettings['Shop_enable_trade']))
			fatal_error(Shop::getText('currently_disabled_trade'), false);

		// Is the user is allowed to trade items?
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_canTrade');
	}

	public function main()
	{
		global $context, $scripturl, $user_info;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_trade');
		$context['page_welcome'] = Shop::getText('trade_welcome');
		$context['page_description'] = sprintf(Shop::getText('trade_desc'), $user_info['name']);
		// We want to do something a bit different for the trade center
		$context['template_layers'][] = 'trade';
		// And then just do the same as usual!
		$context['template_layers'][] = 'options';
		$context['sub_template'] = 'stats';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=trade',
			'name' => Shop::getText('main_trade'),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;
		
		// Home stats
		$this->_stats = new Stats(false);
		$context['stats_blocks'] = [
			'most_traded' => [
				'call' => $this->_stats->traded(),
				'enabled' => true,
			],
			'most_expensive' => [
				'call' => $this->_stats->expensive(),
				'enabled' => true,
			],
			'top_profit' => [
				'call' => $this->_stats->profit(),
				'enabled' => true,
			],
			'top_spent' => [
				'call' => $this->_stats->profit(true),
				'enabled' => true,
			],
		];
	}

	public function tabs()
	{
		$this->_tabs = [
			'trade' => [
				'action' => ['trade'],
				'label' => Shop::getText('trade_main'),
			],
			'tradelist' => [
				'action' => ['tradelist', 'trade2', 'trade3', 'traderemove'],
				'label' => Shop::getText('trade_list'),
			],
			'mytrades' => [
				'action' => ['mytrades', 'invtrade'],
				'label' => Shop::getText('trade_myprofile'),
			],
			'tradelog' => [
				'action' => ['tradelog'],
				'label' => Shop::getText('trade_log'),
			],
		];
	}

	public function set()
	{
		global $context, $scripturl;

		// Check if user is allowed to access this section
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_viewInventory');

		// Do we have an item? No? Bad luck...
		if (empty($_REQUEST['id']) || !isset($_REQUEST['id']))
			fatal_error(Shop::getText('item_notfound'), false);

		// Item id
		$this->_trade = (int) $_REQUEST['id'];

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('item_trade_go');
		$context['page_welcome'] = Shop::getText('main_trade') . ' - ' . Shop::getText('item_trade_go');
		$context['page_description'] = Shop::getText('item_trade_desc');
		$context['template_layers'][] = 'trade';
		$context['template_layers'][] = 'options';
		$context['sub_template'] = 'set_trade';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=invtrade;id=' . $this->_trade,
			'name' => Shop::getText('item_trade_go'),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;

		// Validate the info
		$this->validate_set($this->_trade);

		// I want to use the info so the user can remember
		$context['shop_item'] = $this->_item;
	}

	public function set2()
	{
		global $context, $scripturl;

		// Check if user is allowed to access this section
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_viewInventory');

		// Do we have an item? No? Bad luck...
		if (empty($_REQUEST['id']) || !isset($_REQUEST['id']))
			fatal_error(Shop::getText('item_notfound'), false);

		// Item info
		checkSession();
		$this->_trade = (int) $_REQUEST['id'];
		$this->_amount = (int) isset($_REQUEST['tradecost']) ? $_REQUEST['tradecost'] : 0;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('item_trade_go');
		$context['template_layers'][] = 'options';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=invtrade;id=' . $this->_trade,
			'name' => Shop::getText('item_trade_go'),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;

		// Make sure we have a price
		if (empty($this->_amount))
			fatal_error(Shop::getText('item_notprice'), false);
		// No tricks with the price...
		elseif ($this->_amount <= 0)
			fatal_error(Shop::getText('item_price_notnegative'), false);

		// Validate the info
		$this->validate_set($this->_trade);

		// Set the item for trade
		Database::Update('stshop_inventory', ['id' => $this->_trade, 'tradecost' => $this->_amount, 'date' => time()], 'trading = 1, tradecost = {int:tradecost}, tradedate = {int:date}', 'WHERE id = {int:id}');

		// Tell the user that the item was added successfully
		redirectexit('action=shop;sa=inventory;traded');
	}

	public function validate_set($trade)
	{
		global $user_info;

		// Load the info
		$this->_item = Database::Get('', '', '', 'stshop_inventory AS si', Database::$profile_inventory, 'WHERE si.id = {int:tradeid} AND s.status = 1 AND si.userid = {int:mem}', true, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = si.itemid)', ['tradeid' => $trade, 'mem' => $user_info['id']]);

		// No item found
		if (empty($this->_item))
			fatal_error(Shop::getText('item_notfound'), false);
		// It's already on the trade center
		elseif (!empty($this->_item['trading']))
			fatal_error(Shop::getText('item_alreadytraded'), false);
	}

	public function list()
	{
		global $context, $sourcedir, $scripturl, $user_info, $memberContext, $boardurl;

		// Specific member only?
		if (isset($_REQUEST['u']) || isset($_REQUEST['user']))
		{
			$this->_member = loadMemberData((isset($_REQUEST['user']) ? $_REQUEST['user'] : ((isset($_REQUEST['u']) ? $_REQUEST['u'] : $user_info['id']))), isset($_REQUEST['user']), 'profile');

			// Don't mind me, just checking if it's a valid profile
			if (!$this->_member)
				fatal_error(Shop::getText('user_unable_tofind'), false);

			// If all went well, we have a valid member ID!
			list ($memID) = $this->_member;
			loadMemberContext($memID);
		}

		// Set all the page stuff
		require_once($sourcedir . '/Subs-List.php');
		$context['page_title'] = Shop::getText('main_button') . ' - ' . ($_REQUEST['sa'] == 'mytrades' ? Shop::getText('trade_myprofile') : (!isset($_REQUEST['u']) ? Shop::getText('trade_list') : sprintf(Shop::getText('trade_profile'), $memberContext[$memID]['name'])));
		$context['page_welcome'] = Shop::getText('main_trade') . ' - ' . ($_REQUEST['sa'] == 'mytrades' ? Shop::getText('trade_myprofile') : (!isset($_REQUEST['u']) ? Shop::getText('trade_list') : sprintf(Shop::getText('trade_profile'), $memberContext[$memID]['name'])));

		$context['page_description'] = ($_REQUEST['sa'] == 'mytrades' ? Shop::getText('trade_myprofile_desc') : (!isset($_REQUEST['u']) ? Shop::getText('trade_list_desc') : sprintf(Shop::getText('trade_profile_desc'), $memberContext[$memID]['name'])));
		$context['template_layers'][] = 'trade';
		$context['template_layers'][] = 'options';
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'inventory';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=' . $_REQUEST['sa'] . ($_REQUEST['sa'] == 'mytrades' ? '' : (!isset($_REQUEST['u']) ? '' : ';u=' . $_REQUEST['u'])),


			'name' => ($_REQUEST['sa'] == 'mytrades' ? Shop::getText('trade_myprofile') : (!isset($_REQUEST['u']) ? Shop::getText('trade_list') : sprintf(Shop::getText('trade_profile'), $memberContext[$memID]['name']))),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;

		// Is it their own trades?
		if ($_REQUEST['sa'] != 'mytrades')
			$context['section_tabs']['tradelist#searchuser'] = [
				'action' => ['tradelist'],
				'anchor' => true,
				'label' => Shop::getText('inventory_search'),
			];

		// Images...
		$context['items_url'] = $boardurl . Shop::$itemsdir;
		$context['shop_images_list'] = Images::list();
		// ... and categories
		$context['shop_categories_list'] = Database::Get(0, 1000, 'sc.name', 'stshop_categories AS sc', Database::$categories);
		$context['form_url'] = '?action=shop;sa=' . $_REQUEST['sa'] . (!isset($_REQUEST['u']) ? '' : ';u='. $memberContext[$memID]['id']) . (isset($_REQUEST['cat']) && $_REQUEST['cat'] >= 0 ? ';cat='.$_REQUEST['cat'] : '');

		// Load suggest.js
		loadJavaScriptFile('suggest.js', ['default_theme' => true, 'defer' => false, 'minimize' => true], 'smf_suggest');

		// The entire list
		$listOptions = Inventory::inventory_list((isset($_REQUEST['u']) ? $memberContext[$memID] : $user_info), $context['form_url'], ($_REQUEST['sa'] == 'mytrades' || isset($_REQUEST['u']) ? false : true), true);

		// Update default sorting
		$listOptions['default_sort_col'] = 'item_details';

		// Remove unnecessary stuff from this view
		unset($listOptions['columns']['item_category']);
		unset($listOptions['columns']['item_date']);
		unset($listOptions['columns']['item_use']);
		unset($listOptions['columns']['item_fav']);
		unset($listOptions['columns']['item_trade']);
		unset($listOptions['additional_rows']['traded']);

		// Change date
		$listOptions['columns']['item_details'] = [
			'header' => [
				'value' => Shop::getText('item_details'),
				'class' => 'lefttext',
			],
			'data' => [
				'function' => function($row) use ($scripturl)
				{
					// Category
					$details = '<strong>' . Shop::getText('item_category') . ': </strong>' . (!empty($row['catid']) ? $row['category'] : Shop::getText('item_uncategorized'));

					// Date
					$details .= '<br><strong>' . Shop::getText('item_date') . ': </strong>' . timeformat($row['tradedate']);

					// Who owns this
					$details .= '<br><a href="' . $scripturl . '?action=shop;sa=owners;id=' . $row['itemid'] . '">' . Shop::getText('buy_item_who_this') . '</a>';

					return $details;
				},
				'class' => 'lefttext',
				'style' => 'width: 20%',
			],
			'sort' =>  [
				'default' => 'tradedate DESC',
				'reverse' => 'tradedate',
			],
		];

		// Owner
		$listOptions['columns']['item_owner'] = [
			'header' => [
				'value' => Shop::getText('item_member'),
				'class' => 'centertext',
			],
			'data' => [
				'sprintf' => [
					'format' => '<a href="' . $scripturl . '?action=profile;u=%1$d">%2$s</a>',
					'params' => [
						'userid' => false,
						'real_name' => true
					],
				],
				'class' => 'centertext',
			],
			'sort' => [
				'default' => 'real_name DESC',
				'reverse' => 'real_name',
			],
		];
		// Price
		$listOptions['columns']['item_price'] = [
			'header' => [
				'value' => Shop::getText('item_price'),
				'class' => 'centertext',
			],
			'data' => [
				'function' => function($row)
				{
					return Format::cash($row['tradecost']);
				},
				'class' => 'centertext',
			],
			'sort' => [
				'default' => 'tradecost DESC',
				'reverse' => 'tradecost',
			],
		];
		// Purchase it
		$listOptions['columns']['item_buy'] = [
			'header' => [
				'value' => Shop::getText('item_buy'),
				'class' => 'centertext',
			],
			'data' => [
				'function' => function($row) use ($context, $user_info, $scripturl)
				{
					// How much need the user to buy this item?
					if ($user_info['shopMoney'] < $row['tradecost']) 
						return '
							<i>' . Shop::getText('buy_notenough') . '</i>';
					//Enough money? Buy it!
					else
						return '
							<a href="' . $scripturl . '?action=shop;sa=trade2;id=' . $row['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">
								' . Shop::getText('item_buy') . '
							</a>';
				},
				'class' => 'centertext',
			],
			'sort' => [
				'default' => 'id DESC',
				'reverse' => 'id',
			],
		];

		// My trades
		if ($_REQUEST['sa'] == 'mytrades')
		{
			// Remove stuff
			unset($listOptions['columns']['item_owner']);
			unset($listOptions['columns']['item_buy']);

			// Remove it from trarde
			$listOptions['columns']['item_actions'] = [
				'header' => [
					'value' => Shop::getText('trade_mytrades_actions'),
					'class' => 'centertext',
				],
				'data' => [
					'function' => function($row) use ($context, $scripturl)
					{
						return '	
							<a href="' . $scripturl . '?action=shop;sa=traderemove;id=' . $row['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">
								' . Shop::getText('trade_remove_item') . '
							</a>';
					},
					'class' => 'centertext',
				],
				'sort' => [
					'default' => 'id DESC',
					'reverse' => 'id',
				],
			];
			// Success removing it
			$listOptions['additional_rows']['removed'] = [
				'position' => 'above_column_headers',
				'value' => (isset($_REQUEST['removed']) ? '<div class="infobox">' . Shop::getText('trade_removed') . '</div>' : '')
			];
		}

		// Print our multiple lists with one line :o
		createList($listOptions);
	}

	public function remove()
	{
		global $context, $user_info;

		// Make sure id is numeric
		$this->_trade = (int) isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;

		// Check session
		checkSession('request');

		// If nothing was chosen to delete
		if (empty($this->_trade))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Load this item info
		$this->_item = Database::Get('', '', '', 'stshop_inventory AS si', ['si.id', 'si.itemid', 'si.userid'], 'WHERE si.id = {int:id}', true, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = si.itemid)', ['id' => $this->_trade]);

		// We didn't get results?
		if (empty($this->_item))
			fatal_error(Shop::getText('item_delete_error'), false);
		// Is that YOUR item
		if ($this->_item['userid'] != $user_info['id'])
			fatal_error(Shop::getText('item_notown'), false);

		// Remove item from trading
		Database::Update('stshop_inventory', ['id' => $this->_item['id'], 'user' => $user_info['id']], 'trading = 0, tradecost = 0,', 'WHERE id = {int:id} AND userid = {int:user}');

		// Send the user to the items list with a message
		redirectexit('action=shop;sa=mytrades;removed');
	}

	public function transaction()
	{
		global $context, $user_info, $modSettings, $scripturl;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_trade');
		$context['template_layers'][] = 'options';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=trade',
			'name' => Shop::getText('main_trade'),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;

		// Check session
		checkSession('request');

		// You cannot get here without an item
		if (!isset($_REQUEST['id']) || empty($_REQUEST['id']))
			fatal_error(Shop::getText('trade_something'), false);

		// Make sure it's an int
		$this->_trade = (int) $_REQUEST['id'];

		// Item info
		$this->_item = Database::Get('','','', 'stshop_inventory AS si', array_merge(Database::$inventory, Database::$items), 'WHERE si.id = {int:id} AND si.trading = 1 AND s.status = 1', true, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = si.itemid)', ['id' => $this->_trade]);

		// How many of this item does the user own?
		$this->_limit = Database::Count('stshop_inventory AS si', Database::$inventory, 'WHERE itemid = {int:id} AND userid = {int:userid}', '', ['id' => $this->_item['itemid'], 'userid' => $user_info['id']]);

		// Is that id actually valid?
		if (empty($this->_item))
			fatal_error(Shop::getText('item_notfound'), false);
		// Already reached the limit?
		elseif (($this->_item['itemlimit'] != 0) && ($this->_item['itemlimit'] <= $this->_limit))
			fatal_error(Shop::getText('item_limit_reached'), false);
		// Are you really so stupid to buy your own item?
		elseif ($this->_item['userid'] == $user_info['id'])
			fatal_error(Shop::getText('item_notbuy_own'), false);
		// Fine... Does the user have enough money to buy this?
		elseif ($user_info['shopMoney'] < $this->_item['tradecost'])
			fatal_error(sprintf(Shop::getText('buy_item_notenough'), $this->_item['name'], Format::cash($this->_item['tradecost'] - $user_info['shopMoney'])), false);

		// Fee
		$this->_fee = (int) (($this->_item['tradecost'] * $modSettings['Shop_items_trade_fee'])/100);

		// Proceed
		// Handle item purchase and money deduction and log it
		$this->_log->purchase($this->_item['itemid'], $user_info['id'], $this->_item['tradecost'], $this->_item['userid'], $this->_fee, $this->_trade);

		// Send PM
		$this->_notify->pm($this->_item['userid'], Shop::getText('trade_notification_sold_subject'), (!empty($modSettings['Shop_items_trade_fee']) ? sprintf(Shop::getText('trade_notification_sold_message2'), $scripturl . '?action=profile;u='.$user_info['id'], $user_info['name'], $this->_item['name'], Format::cash($this->_item['tradecost']), Format::cash($this->_fee), Format::cash($this->_item['tradecost'] - $this->_fee)) : sprintf(Shop::getText('trade_notification_sold_message1'), $scripturl . '?action=profile;u='.$user_info['id'], $user_info['name'], $this->_item['name'], Format::cash($this->_item['tradecost']), $modSettings['Shop_credits_suffix'])));

		// Alert?
		if (!empty($modSettings['Shop_noty_trade']))
			$this->_notify->alert($this->_item['userid'], 'traded', $this->_item['id'], ['shop_href' => ';sa=inventory', 'item_icon' => $this->_item['image'], 'use_item' => true, 'item_name' => $this->_item['name']]);
			
		// Redirect to the inventory?
		redirectexit('action=shop;sa=inventory;sort=item_date;purchased');
	}

	public function log()
	{
		global $context, $scripturl, $sourcedir, $modSettings, $user_info;

		// Set all the page stuff
		loadlanguage('Shop/ShopAdmin');
		require_once($sourcedir . '/Subs-List.php');
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('trade_log');
		$context['page_welcome'] = Shop::getText('main_trade') . ' - ' . Shop::getText('trade_log');
		$context['page_description'] = Shop::getText('trade_log_desc');
		// We want to do something a bit different for the trade center
		$context['template_layers'][] = 'trade';
		// And then just do the same as usual!
		$context['template_layers'][] = 'options';
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'trade_log';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=trade',
			'name' => Shop::getText('main_trade'),
		];
		// Sub-menu tabs
		$context['section_tabs'] = $this->_tabs;

		// The entire list
		$listOptions = [
			'id' => 'trade_log',
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=shop;sa=tradelog',
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_log_buy AS lb', array_merge(Database::$log_buy, ['seller.real_name AS name_seller', 'buyer.real_name AS name_buyer']), 'WHERE lb.sellerid <> 0 AND (lb.sellerid = {int:user} OR lb.userid = {int:user}) AND s.status = 1', false, 'LEFT JOIN {db_prefix}members AS seller ON (seller.id_member = lb.sellerid) LEFT JOIN {db_prefix}members AS buyer ON (buyer.id_member = lb.userid) LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)', ['user' => $user_info['id']]],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_log_buy AS lb', Database::$log_buy, 'WHERE lb.sellerid <> 0 AND (lb.sellerid = {int:user} OR lb.userid = {int:user}) AND s.status = 1', 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)', ['user' => $user_info['id']]],
			],
			'no_items_label' => Shop::getText('logs_empty'),
			'no_items_align' => 'center',
			'columns' => [
				'item_image' => [
					'header' => [
						'value' => Shop::getText('item_image'),
						'class' => 'centertext',
					],
					'data' => [
						'function' => function($row)
						{
							 return Format::image($row['image'], $row['description']);
						},
						'style' => 'width: 10%',
						'class' => 'centertext',
					],
				],
				'item_name' => [
					'header' => [
						'value' => Shop::getText('item_name'),
						'class' => 'lefttext',
					],
					'data' => [
						'db' => 'name',
						'class' => 'lefttext',
						'style' => 'width: 32%',
					],
					'sort' =>  [
						'default' => 'name DESC',
						'reverse' => 'name',
					],
				],
				'item_user' => [
					'header' => [
						'value' => Shop::getText('user_name'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							global $user_info, $scripturl;

							// Format link depending of buyer/seller
							return '<a href="'. $scripturl . '?action=shop;sa=inventory;u=' . ($row['userid'] == $user_info['id'] ? $row['sellerid'] : $row['userid']) . '">' . ($row['userid'] == $user_info['id'] ? $row['name_seller'] : $row['name_buyer']) . '</a>';
						},
						'style' => 'width: 12%',
					],
					'sort' =>  [
						'default' => 'name_seller DESC, name_buyer DESC',
						'reverse' => 'name_seller, name_buyer',
					],
				],
				'amount' => [
					'header' => [
						'value' => Shop::getText('bank_amount'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							global $user_info;

							// Show a sign and color depending on the user
							return '<span style="color: '. ($row['userid'] == $user_info['id'] ? 'red">- ' : 'green">+ ') . Format::cash($row['amount']) . '</span>';
						},
						'style' => 'width: 15%'
					],
					'sort' =>  [
						'default' => 'amount DESC',
						'reverse' => 'amount',
					],
				],
				'fee' => [
					'header' => [
						'value' => Shop::getText('trade_fee'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							global $user_info;

							// Only show fee if you sold it
							if ($row['sellerid'] == $user_info['id'])
								$fee = $row['fee'];
							else
								$fee = 0;

							// Show a sign and color depending on the fee
							return '<span style="color: '. ($fee > 0 ? 'red">-' : '">') . Format::cash($fee) . '</span>';
						},
						'style' => 'width: 12%'
					],
					'sort' =>  [
						'default' => 'fee DESC',
						'reverse' => 'fee',
					],
				],
				'date' => [
					'header' => [
						'value' => Shop::getText('item_date'),
						'class' => ' lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return timeformat($row['date']);
						},
						'style' => 'width: 25%',
					],
					'sort' =>  [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
			],
		];
		// Let's finishem
		createList($listOptions);
	}
}