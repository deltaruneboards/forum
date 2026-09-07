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

if (!defined('SMF'))
	die('No direct access...');

class Stats
{
	/**
	 * @var array Load shop stats.
	 */
	public $_stats = [];

	/**
	 * @var array Load home stats.
	 */
	public $_home_stats = [];

	/**
	 * @var array Load trade stats.
	 */
	public $_trade_stats = [];

	/**
	 * @var array Store the results.
	 */
	private $_result = [];

	/**
	 * @var array Formatted array.
	 */
	private $_final = [];

	/**
	 * @var int Max value.
	 */
	private $_max;

	/**
	 * Stats::__construct()
	 *
	 * Set the tabs for the section and create instance of needed objects
	 */
	function __construct($all = true)
	{
		global $modSettings;
	
		// Non-Action object?
		if (!empty($all))
		{
			// What if the stats are disabled?
			if (empty($modSettings['Shop_enable_stats']))
				fatal_error(Shop::getText('currently_disabled_stats'), false);

			// Check if user is allowed to access this section
			if (!allowedTo('shop_canManage'))
				isAllowedTo('shop_viewStats');
		}
	}

	public function home_stats()
	{
		global $modSettings;

		$this->_home_stats = [
			'richest_pocket' => [
				'call' => $this->richest(),
				'enabled' => true,
			],
			'richest_bank' => [
				'call' => $this->richest(true),
				'enabled' => allowedTo('shop_canBank') && !empty($modSettings['Shop_enable_bank']),
			],
		];

		return $this->_home_stats;
	}

	public function full_stats()
	{
		global $modSettings;

		$this->_stats = [
			'most_purchased' => [
				'call' => $this->purchased(),
				'enabled' => allowedTo('shop_canBuy') || allowedTo('shop_canTrade'),
			],
			'top_cats' => [
				'call' => $this->categories(),
				'enabled' => true,
			],
			'top_inventories' => [
				'call' => $this->inventories(),
				'enabled' => allowedTo('shop_canBuy') || allowedTo('shop_viewInventory') || (allowedTo('shop_canTrade') && !empty($modSettings['Shop_enable_trade'])),
			],
			'top_buyers' => [
				'call' => $this->buyers(),
				'enabled' => allowedTo('shop_canBuy') || (allowedTo('shop_canTrade') && !empty($modSettings['Shop_enable_trade'])),
			],
			'gifts_sent' => [
				'call' => $this->gifts(),
				'enabled' => allowedTo('shop_canGift'),
			],
			'gifts_received' => [
				'call' => $this->gifts(false),
				'enabled' => allowedTo('shop_canGift'),
			],
			'money_sent' => [
				'call' => $this->gifts(true, true),
				'enabled' => allowedTo('shop_canGift'),
			],
			'money_received' => [
				'call' => $this->gifts(false, true),
				'enabled' => allowedTo('shop_canGift'),
			],
			'richest_pocket' => [
				'call' => $this->richest(),
				'enabled' => true,
			],
		];
		// Add home stats
		$this->_stats = array_merge($this->_stats, $this->home_stats());

		// Add more stats?
		call_integration_hook('integrate_shop_stats', [&$this->_stats]);

		return $this->_stats;
	}

	public function Main()
	{
		global $context, $scripturl, $modSettings, $txt;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_stats');
		$context['sub_template'] = 'stats';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=stats',
			'name' => Shop::getText('main_stats'),
		];

		// Get the stats
		$context['stats_blocks'] = $this->full_stats();
	}

	/**
	 * Stats::percentage()
	 *
	 * Adds the percentage to the list if needed
	 */
	public function format($link = false)
	{
		$this->_final = [];
		// We actually got something?
		if (!empty($this->_result))
		{
			$this->_max = 1;
			foreach ($this->_result as $row)
			{
				$this->_final[] = [
					'name' => $row['stat_name'],
					'id' => $row['stat_id'],
					'image' => isset($row['image']) ? Format::image($row['image']) : '',
					'link' => $link,
					'count' => isset($row['count']) ? $row['count'] : NULL,
				];
				if (isset($row['count']))
					if ($this->_max < $row['count'])
						$this->_max = $row['count'];
			}
			foreach ($this->_final as $row => $value)
			{
				if ($value['count'] == NULL)
					continue;

				$this->_final[$row]['percent'] = round(($value['count'] * 100) / $this->_max);
				$this->_final[$row]['num'] = comma_format($value['count']);
			}
		}
		else
			$this->_final = false;
	}

	/**
	 * Stats::purchased()
	 *
	 * List with most purchased items
	 */
	public function purchased($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_log_buy AS lb', ['lb.itemid AS stat_id', 'count(*) AS count', 's.name AS stat_name ', 's.image', 's.status'], 'WHERE s.status = 1 GROUP BY stat_id, stat_name, s.image, s.status', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)');
		$this->format();

		return $this->_final;
	}

	/**
	 * Stats::categories()
	 *
	 * List with most populated categories
	 */
	public function categories($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_items AS s', ['s.catid AS stat_id', 'count(*) AS count', 'c.name AS stat_name', 'c.image', 's.status'], 'WHERE s.catid <> 0 AND s.status = 1 GROUP BY stat_id, s.status, stat_name, c.image', false, 'LEFT JOIN {db_prefix}stshop_categories AS c ON (c.catid = s.catid)');
		$this->format();

		return $this->_final;
	}

	/**
	 * Stats::inventories()
	 *
	 * List with most populated inventories
	 */
	public function inventories($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_inventory AS si', ['si.userid AS stat_id', 'count(*) AS count', 'si.trading', 'm.real_name AS stat_name', 's.status'], 'WHERE si.trading = 0 AND s.status = 1 GROUP BY stat_id, s.status, si.trading, stat_name', false, 'LEFT JOIN {db_prefix}members AS m ON (m.id_member = si.userid) LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = si.itemid)');
		$this->format(true);

		return $this->_final;
	}

	/**
	 * Stats::buyers()
	 *
	 * List of users that have purchased the most items, traded or not
	 */
	public function buyers($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_log_buy AS lb', ['lb.userid AS stat_id', 'count(*) AS count', 'm.real_name AS stat_name', 's.status'], 'WHERE s.status = 1 GROUP BY stat_id, s.status, stat_name', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid) LEFT JOIN {db_prefix}members AS m ON (m.id_member = lb.userid)');
		$this->format(true);

		return $this->_final;
	}

	/**
	 * Stats::gifts()
	 *
	 * List of users with the most gifts received/sent
	 */
	public function gifts($sent = true, $money = false, $limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_log_gift AS lg', ['lg.' . (!empty($sent) ? 'userid' : 'receiver') . ' AS stat_id', (!empty($money) ? 'sum(lg.amount)' : 'count(*)') . ' AS count', 'm.real_name AS stat_name', 's.status'], 'WHERE' . (!empty($money) ? '' : ' s.status = 1 AND') . ' lg.is_admin = 0 AND lg.amount ' . (!empty($money) ? '>' : '=') . ' 0 GROUP BY stat_id, s.status, stat_name', false, 'LEFT JOIN {db_prefix}members AS m ON (m.id_member = lg.' . (!empty($sent) ? 'userid' : 'receiver') . ') LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lg.itemid)');
		$this->format(true);

		return $this->_final;
	}

	/**
	 * Stats::richest()
	 *
	 * List of the richest users in the bank or pocket
	 */
	public function richest($bank = false, $limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'm.shop' . (!empty($bank) ? 'Bank' : 'Money'). ' DESC', 'members AS m', ['m.id_member AS stat_id', 'm.shop' . (!empty($bank) ? 'Bank' : 'Money') . ' AS count', 'm.real_name AS stat_name'], 'WHERE m.shop' . (!empty($bank) ? 'Bank' : 'Money') . ' > 0');
		$this->format(true);

		return $this->_final;
	}

	/**
	 * Stats::recent()
	 *
	 * List of the recent items added
	 */
	public function recent($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 's.itemid DESC', 'stshop_items AS s', ['s.itemid AS stat_id', 's.name AS stat_name', 's.image', 's.status'], 'WHERE s.status = 1');
		$this->format();

		return $this->_final;
	}

	/**
	 * Stats::last_purchased()
	 *
	 * List of the recent items purchased or traded
	 */
	public function last_purchased($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'lb.id DESC', 'stshop_log_buy AS lb', ['lb.id AS stat_id', 'lb.itemid', 's.name AS stat_name', 's.image', 's.status'], 'WHERE s.status = 1', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)');
		$this->format();

		return $this->_final;
	}

	/**
	 * Stats::traded()
	 *
	 * List of most traded items
	 */
	public function traded($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_log_buy AS lb', ['lb.itemid AS stat_id', 'count(*) AS count', 's.name AS stat_name', 's.image', 's.status'], 'WHERE s.status = 1 AND lb.sellerid <> 0 GROUP BY lb.itemid, s.name, s.image, s.status', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)');
		$this->format();

		return $this->_final;
	}

	/**
	 * Stats::expensive()
	 *
	 * List of the most expensive items traded
	 */
	public function expensive($limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_log_buy AS lb', ['lb.itemid AS stat_id', 'lb.amount AS count', 's.name AS stat_name', 's.image', 's.status'], 'WHERE s.status = 1 AND lb.sellerid <> 0', false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid)');
		$this->format();

		return $this->_final;
	}

	/**
	 * Stats::profit()
	 *
	 * List of users that have gotten the most money selling
	 */
	public function profit($spent = false, $limit = 5)
	{
		$this->_result = Database::Get(0, $limit, 'count DESC', 'stshop_log_buy AS lb', ['lb.' . (empty($spent) ? 'sellerid' : 'userid') . ' AS stat_id', 'sum(lb.amount) AS count', 'm.real_name AS stat_name', 's.status', 'lb.' . (empty($spent) ? 'userid' : 'sellerid')], 'WHERE s.status = 1 AND lb.sellerid <> 0 GROUP BY stat_id, stat_name, s.status, lb.' . (empty($spent) ? 'userid' : 'sellerid'), false, 'LEFT JOIN {db_prefix}stshop_items AS s ON (s.itemid = lb.itemid) LEFT JOIN {db_prefix}members AS m ON (m.id_member = lb.' . (empty($spent) ? 'sellerid' : 'userid'). ')');
		$this->format();

		return $this->_final;
	}
}