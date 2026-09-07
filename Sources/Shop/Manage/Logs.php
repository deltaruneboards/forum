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

if (!defined('SMF'))
	die('No direct access...');

class Logs extends Dashboard
{
	/**
	 * @var bool Check if the log is for admin actions.
	 */
	private $_is_admin = false;

	/**
	 * Logs::__construct()
	 *
	 * Create the array of subactions and load necessary extra language files
	 */
	function __construct()
	{
		global $modSettings;

		// Array of sections
		$this->_subactions = [
			'admin_money' => 'money',
			'admin_items' => 'items',
			'money' => 'money',
			'items' => 'items',
			'buy' => 'purchase',
			'trade' => 'purchase',
			'bank' => 'bank',
			'games' => 'games',
		];

		// Disabled sections?
		if (empty($modSettings['Shop_enable_shop']))
		{
			unset($this->_subactions['buy']);
			unset($this->_subactions['admin_items']);
		}
		if (empty($modSettings['Shop_enable_shop']) || empty($modSettings['Shop_enable_gift']))
		{
			unset($this->_subactions['money']);
			unset($this->_subactions['items']);
		}
		if (empty($modSettings['Shop_enable_shop']) || empty($modSettings['Shop_enable_trade']))
			unset($this->_subactions['trade']);
		if (empty($modSettings['Shop_enable_shop']) || empty($modSettings['Shop_enable_bank']))
			unset($this->_subactions['bank']);
		if (empty($modSettings['Shop_enable_shop']) || empty($modSettings['Shop_enable_games']))
			unset($this->_subactions['games']);

		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'admin_money';
	}

	public function main()
	{
		global $context, $modSettings, $sourcedir;

		// Everything in here is a list, so...
		require_once($sourcedir . '/Subs-List.php');
		$context['page_title'] = Shop::getText('tab_logs') . ' - ' . Shop::getText('logs_' . (!isset($_REQUEST['sa']) ? 'admin_money' : $_REQUEST['sa']));
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'loglist';

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_logs') . ' - ' . Shop::getText('logs_' . (!isset($_REQUEST['sa']) ? 'admin_money' : $_REQUEST['sa'])),
			'description' => Shop::getText('tab_logs_desc'),
			'tabs' => [
				'admin_money' => ['description' => sprintf(Shop::getText('logs_admin_money_desc'), $modSettings['Shop_credits_suffix'])],
				'admin_items' => ['description' => Shop::getText('logs_admin_items_desc')],
				'money' => ['description' => sprintf(Shop::getText('logs_money_desc'), $modSettings['Shop_credits_suffix'])],
				'items' => ['description' => Shop::getText('logs_items_desc')],
				'buy' => ['description' => Shop::getText('logs_buy_desc')],
				'trade' => ['description' => Shop::getText('logs_trade_desc')],
				'bank' => ['description' => Shop::getText('logs_bank_desc')],
				'games' => ['description' => sprintf(Shop::getText('logs_games_desc'), $modSettings['Shop_credits_suffix'])],
			],
		];
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa] . '#');
	}

	public function money()
	{
		global $scripturl, $modSettings;

		$listOptions = [
			'id' => 'loglist',
			'title' => Shop::getText('logs_' . (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'admin_money' ? 'admin_' : '') . 'money'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shoplogs;sa=' . (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'admin_money' ? 'admin_' : '') . 'money',
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_log_gift AS lg', array_merge(Database::$log_gift, ['m1.real_name AS name_sender', 'm2.real_name AS name_receiver']), 'WHERE lg.itemid = 0 AND lg.amount > 0 AND lg.is_admin = {int:admin}', false, 'LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = lg.userid) LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = lg.receiver)', ['admin' => (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'admin_money' ? 1 : 0)]],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_log_gift AS lg', Database::$log_gift, 'WHERE lg.itemid = 0 AND lg.amount > 0 AND lg.is_admin = {int:admin}', '', ['admin' => (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'admin_money' ? 1 : 0)]],
			],
			'no_items_label' => Shop::getText('logs_empty'),
			'no_items_align' => 'center',
			'columns' => [
				'from_user' => [
					'header' => [
						'value' => Shop::getText('logs_user_sending'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'userid' => false,
								'name_sender' => true,
							],
						],
						'style' => 'width: 22%',
					],
					'sort' =>  [
						'default' => 'name_sender DESC',
						'reverse' => 'name_sender',
					],
				],
				'amount' => [
					'header' => [
						'value' => Shop::getText('logs_amount'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return Format::cash($row['amount']);
						},
						'style' => 'width: 34%'
					],
					'sort' => [
						'default' => 'amount DESC',
						'reverse' => 'amount',
					],
				],
				'for_user' => [
					'header' => [
						'value' => Shop::getText('logs_user_receiving'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'receiver' => false,
								'name_receiver' => true,
							],
						],
						'style' => 'width: 22%',
					],
					'sort' => [
						'default' => 'name_receiver DESC',
						'reverse' => 'name_receiver',
					],
				],
				'date' => [
					'header' => [
						'value' => Shop::getText('logs_date'),
						'class' => ' lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return timeformat($row['date']);
						},
						'style' => 'width: 22%',
					],
					'sort' => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
			],
		];
		// Let's finishem
		createList($listOptions);
	}

	public function items()
	{
		global $scripturl, $modSettings;

		$listOptions = [
			'id' => 'loglist',
			'title' => Shop::getText('logs_' . (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'admin_items' ? 'admin_' : '') . 'items'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shoplogs;sa=' . ($_REQUEST['sa'] == 'admin_items' ? 'admin_' : '') . 'items',
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_log_gift AS lg', array_merge(Database::$log_gift, ['m1.real_name AS name_sender', 'm2.real_name AS name_receiver', 's.itemid', 's.status', 's.name', 's.image', 's.description']), 'WHERE lg.itemid <> 0 AND lg.amount = 0 AND s.status = 1 AND lg.is_admin = {int:admin}', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lg.itemid) LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = lg.userid) LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = lg.receiver)', ['admin' => ($_REQUEST['sa'] == 'admin_items' ? 1 : 0)]],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_log_gift AS lg', array_merge(Database::$log_gift, ['s.itemid', 's.status', 's.name']), 'WHERE lg.itemid <> 0 AND lg.amount = 0 AND s.status = 1 AND lg.is_admin = {int:admin}', 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lg.itemid)', ['admin' => ($_REQUEST['sa'] == 'admin_items' ? 1 : 0)]],
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
						'style' => 'width: 8%',
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
						'style' => 'width: 23%',
					],
					'sort' =>  [
						'default' => 'name DESC',
						'reverse' => 'name',
					],
				],
				'from_user' => [
					'header' => [
						'value' => Shop::getText('logs_user_sending'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'userid' => false,
								'name_sender' => true,
							],
						],
						'style' => 'width: 23%',
					],
					'sort' =>  [
						'default' => 'name_sender DESC',
						'reverse' => 'name_sender',
					],
				],
				'for_user' => [
					'header' => [
						'value' => Shop::getText('logs_user_receiving'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'receiver' => false,
								'name_receiver' => true,
							],
						],
						'style' => 'width: 23%',
					],
					'sort' => [
						'default' => 'name_receiver DESC',
						'reverse' => 'name_receiver',
					],
				],
				'date' => [
					'header' => [
						'value' => Shop::getText('logs_date'),
						'class' => ' lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return timeformat($row['date']);
						},
						'style' => 'width: 23%',
					],
					'sort' => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
			],
		];
		// Let's finishem
		createList($listOptions);
	}

	public function purchase()
	{
		global $scripturl, $modSettings;

		$listOptions = [
			'id' => 'loglist',
			'title' => Shop::getText('logs_' . $_REQUEST['sa']),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shoplogs;sa=' . $_REQUEST['sa'],
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_log_buy AS lb', array_merge(Database::$log_buy, ['m1.real_name AS name_buyer', 'm2.real_name AS name_seller']), 'WHERE lb.sellerid ' . ($_REQUEST['sa'] == 'buy' ? '= 0' : '<>') . '0 AND s.status = 1', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid) LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = lb.userid) LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = lb.sellerid)'],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_log_buy AS lb', Database::$log_buy, 'WHERE lb.sellerid ' . ($_REQUEST['sa'] == 'buy' ? '= 0' : '<>') . '0 AND s.status = 1', 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)'],
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
						'style' => 'width: 8%',
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
						'style' => 'width: 22%',
					],
					'sort' =>  [
						'default' => 'name DESC',
						'reverse' => 'name',
					],
				],
				'buyer' => [
					'header' => [
						'value' => Shop::getText('logs_buyer'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'userid' => false,
								'name_buyer' => true,
							],
						],
						'style' => 'width: 14%',
					],
					'sort' =>  [
						'default' => 'name_buyer DESC',
						'reverse' => 'name_buyer',
					],
				],
				'amount' => [
					'header' => [
						'value' => Shop::getText('logs_amount'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return Format::cash($row['amount']);
						},
						'style' => 'width: 15%'
					],
					'sort' => [
						'default' => 'amount DESC',
						'reverse' => 'amount',
					],
				],
				'fee' => [
					'header' => [
						'value' => Shop::getText('logs_fee'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return Format::cash($row['fee']);
						},
						'style' => 'width: 10%'
					],
					'sort' => [
						'default' => 'fee DESC',
						'reverse' => 'fee',
					],
				],
				'seller' => [
					'header' => [
						'value' => Shop::getText('logs_seller'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'sellerid' => false,
								'name_seller' => true,
							],
						],
						'style' => 'width: 15%',
					],
					'sort' => [
						'default' => 'name_seller DESC',
						'reverse' => 'name_seller',
					],
				],
				'date' => [
					'header' => [
						'value' => Shop::getText('logs_date'),
						'class' => ' lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return timeformat($row['date']);
						},
						'style' => 'width: 16%',
					],
					'sort' => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
			],
		];

		// Remove seller if it's not trade
		if ($_REQUEST['sa'] == 'buy')
		{
			unset($listOptions['columns']['fee']);
			unset($listOptions['columns']['seller']);
		}

		// Let's finishem
		createList($listOptions);
	}

	public function bank()
	{
		global $scripturl, $modSettings;

		$listOptions = [
			'id' => 'loglist',
			'title' => Shop::getText('logs_bank'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shoplogs;sa=bank',
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_log_bank AS lb', array_merge(Database::$log_bank, ['m.real_name']), '', false, 'LEFT JOIN {db_prefix}members AS m ON (m.id_member = lb.userid)'],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_log_bank AS lb', Database::$log_bank],
			],
			'no_items_label' => Shop::getText('logs_empty'),
			'no_items_align' => 'center',
			'columns' => [
				'from_user' => [
					'header' => [
						'value' => Shop::getText('logs_user'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'userid' => false,
								'real_name' => true,
							],
						],
						'style' => 'width: 20%',
					],
					'sort' =>  [
						'default' => 'real_name DESC',
						'reverse' => 'real_name',
					],
				],
				'trans_type' => [
					'header' => [
						'value' => Shop::getText('logs_transaction'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
								return Shop::getText('logs_trans_' . $row['action']);
						},
						'style' => 'width: 14%',
					],
					'sort' => [
						'default' => 'type DESC',
						'reverse' => 'type',
					],
				],
				'amount' => [
					'header' => [
						'value' => Shop::getText('logs_amount'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return '
							<span style="color: ' . ($row['action'] == 'withdrawal' ? 'red">- ' : 'green">+ ') . Format::cash($row['amount']) . '</span>';


							//return Format::cash($row['amount']);
						},
						'style' => 'width: 21%'
					],
					'sort' => [
						'default' => 'amount DESC',
						'reverse' => 'amount',
					],
				],
				'fee' => [
					'header' => [
						'value' => Shop::getText('logs_fee'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return Format::cash($row['fee']) . Shop::getText('logs_fee_type_' . $row['type']);
						},
						'style' => 'width: 24%'
					],
					'sort' => [
						'default' => 'fee DESC',
						'reverse' => 'fee',
					],
				],
				'date' => [
					'header' => [
						'value' => Shop::getText('logs_date'),
						'class' => ' lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return timeformat($row['date']);
						},
						'style' => 'width: 20%',
					],
					'sort' => [
						'default' => 'date DESC',
						'reverse' => 'date',
					],
				],
			],
		];
		// Let's finishem
		createList($listOptions);
	}

	public function games()
	{
		global $scripturl, $modSettings;

		// Games
		loadLanguage('Shop/Games');

		$listOptions = [
			'id' => 'loglist',
			'title' => Shop::getText('logs_games'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shoplogs;sa=games',
			'default_sort_col' => 'date',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_log_games AS lg', array_merge(Database::$log_games, ['m.real_name']), '', false, 'LEFT JOIN {db_prefix}members AS m ON (m.id_member = lg.userid)'],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_log_games AS lg', Database::$log_games],
			],
			'no_items_label' => Shop::getText('logs_empty'),
			'no_items_align' => 'center',
			'columns' => [
				'from_user' => [
					'header' => [
						'value' => Shop::getText('logs_user'),
						'class' => 'lefttext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl . '?action=profile;u=%1$d">%2$s</a>',
							'params' => [
								'userid' => false,
								'real_name' => true,
							],
						],
						'style' => 'width: 25%',
					],
					'sort' =>  [
						'default' => 'real_name DESC',
						'reverse' => 'real_name',
					],
				],
				'game' => [
					'header' => [
						'value' => Shop::getText('logs_games_type'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row) use ($scripturl)
						{
							return '<a href="'. $scripturl . '?action=shop;sa=games;play=' . $row['game'] . '">' . Shop::getText('games_' . $row['game']) . '</a>';
						},
						'style' => 'width: 25%',
					],
					'sort' => [
						'default' => 'type DESC',
						'reverse' => 'type',
					],
				],
				'amount' => [
					'header' => [
						'value' => Shop::getText('logs_amount'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							// Show a sign and color depending on the user
							return '<span style="color: ' . ($row['amount'] <= 0 ? 'red">- ' : 'green">+ ') . Format::cash(abs($row['amount'])) . '</span>';
						},
						'style' => 'width: 25%'
					],
					'sort' => [
						'default' => 'amount DESC',
						'reverse' => 'amount',
					],
				],
				'date' => [
					'header' => [
						'value' => Shop::getText('logs_date'),
						'class' => ' lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return timeformat($row['date']);
						},
						'style' => 'width: 25%',
					],
					'sort' => [
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