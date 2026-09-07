<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration;

use Shop\Shop;
use Shop\Helper\Database;

if (!defined('SMF'))
	die('No direct access...');

class Who
{
	/**
	 * @var array Receives the database query for different scenarios.
	 */
	var $_query;

	/**
	 * @var array Formats $_query to user the member ID as the key.
	 */
	var $_result;

	/**
	 * @var array Stores the ID's for inventories.
	 */
	var $_inventory_ids = [];

	/**
	 * @var array Stores the ID's for trade center.
	 */
	var $_trade_ids = [];

	/**
	 * Who::who_allowed()
	 *
	 * Used in the who's online action.
	 * 
	 * @param array $allowedActions is the array of actions that require a specific permission, using ACTION as the key.
	 * @return void
	 */
	public static function who_allowed(&$allowedActions)
	{
		$allowedActions['shop'] = ['shop_canAccess', 'shop_canManage'];
		$allowedActions['shopinfo'] = ['shop_canManage'];
		$allowedActions['shopsettings'] = ['shop_canManage'];
		$allowedActions['shopitems'] = ['shop_canManage'];
		$allowedActions['shopmodules'] = ['shop_canManage'];
		$allowedActions['shopcategories'] = ['shop_canManage'];
		$allowedActions['shopgames'] = ['shop_canManage'];
		$allowedActions['shopinventory'] = ['shop_canManage'];
		$allowedActions['shoplogs'] = ['shop_canManage'];
	}

	/**
	 * Who::whos_online_after()
	 *
	 * Used in the who's online action.
	 * @param mixed $urls a single url (string) or an array of arrays, each inner array being (JSON-encoded request data, id_member)
	 * @param array $data Returns the correct strings for each action
	 * @return void
	 */
	public function whos_online_after(&$urls, &$data)
	{
		global $modSettings, $user_info;

		// We do nothing if shop is disabled or user can't see it
		if (empty($modSettings['Shop_enable_shop']) || !allowedTo('shop_canAccess'))
			return;

		// Fix the anomaly where $urls is a string when coming from the profile section.
		if (!is_array($urls))
			$url_list = array(array($urls, $user_info['id']));
		else
			$url_list = $urls;
		foreach ($url_list as $k => $url)
		{
			// Get the request parameters..
			$actions = Database::json_decode($url[0], true);
			if ($actions === false)
				continue;

			// Only shop actions
			if (isset($actions['action']) && $actions['action'] === 'shop' && allowedTo('shop_canAccess'))
			{
				// Subsections
				if (isset($actions['sa']))
				{
					// Buying items
					if ($actions['sa'] == 'buy' && allowedTo('shop_canBuy'))
						$data[$k] = Shop::getText('who_buy');
					// Gift items / Send money
					elseif (($actions['sa'] == 'gift' || $actions['sa'] == 'sendmoney') && allowedTo('shop_canGift') && !empty($modSettings['Shop_enable_gift']))
					{
						// Regular gift
						$data[$k] = Shop::getText('who_gift');

						// Money
						if ($actions['sa'] == 'sendmoney')
							$data[$k] = sprintf(Shop::getText('who_sendmoney'), $modSettings['Shop_credits_suffix']);
					}
					// Bank
					elseif ($actions['sa'] == 'bank' && allowedTo('shop_canBank') && !empty($modSettings['Shop_enable_bank']))
						$data[$k] = Shop::getText('who_bank');
					// Stats
					elseif ($actions['sa'] == 'stats' && allowedTo('shop_viewStats') && !empty($modSettings['Shop_enable_stats']))
						$data[$k] = Shop::getText('who_stats');
					// Games Room
					elseif ($actions['sa'] == 'games' && allowedTo('shop_playGames') && !empty($modSettings['Shop_enable_games']))
					{
						$data[$k] = Shop::getText('who_games');
						// Playing a game?
						if (isset($actions['play']))
							$data[$k] = Shop::getText('who_games_' . $actions['play']);
					}
					// Searching
					elseif ($actions['sa'] == 'search' && allowedTo('shop_viewInventory'))
						$data[$k] = Shop::getText('who_search');
					// Viewing Inventory
					elseif ($actions['sa'] == 'inventory' && allowedTo('shop_viewInventory'))
					{
						// Whose?  Their own?
						if (empty($actions['u']))
							$actions['u'] = $url[1];

						$this->_inventory_ids[(int) $actions['u']][$k] = $actions['u'] == $url[1] ? Shop::getText('who_owninventory') : Shop::getText('who_inventory');
					}
					// Trade center
					elseif ($actions['sa'] == 'trade' && allowedTo('shop_canTrade'))
						$data[$k] = Shop::getText('who_trade');
					// Trade list
					elseif (($actions['sa'] == 'tradelist' || $actions['sa'] == 'mytrades') && allowedTo('shop_canTrade'))
					{
						// Whose?  Their own?
						if (empty($actions['u']))
							$actions['u'] = $url[1];

						$this->_trade_ids[(int) $actions['u']][$k] = $actions['u'] == $url[1] ? Shop::getText('who_tradelist' . ($actions['sa'] == 'mytrades' ? '_own' : '')) : Shop::getText('who_tradelist_other');
					}
					// Trade list
					elseif ($actions['sa'] == 'tradelog' && allowedTo('shop_canTrade'))
						$data[$k] = Shop::getText('who_tradelog');
				}

			}
		}

		// Fix strings for inventory
		$this->inventory($data);
		// Fix strings for trade
		$this->trade($data);
	}

	/**
	 * Who::inventory()
	 *
	 * Formats the inventory actions
	 * 
	 * @param array $data Returns the correct strings for each action
	 * @return void
	 */
	public function inventory(&$data)
	{		
		if (!empty($this->_inventory_ids) && allowedTo('shop_viewInventory'))
		{
			$this->_query = Database::Get(0, count($this->_inventory_ids), 'id_member', 'members', ['id_member', 'real_name'], 'WHERE id_member IN ({array_int:member_list})', false, '', ['member_list' => array_keys($this->_inventory_ids)]);

			// Provide proper key
			foreach ($this->_query as $row)
				$this->_result[$row['id_member']] = $row;

			// Set their action on each - session/text to sprintf.
			foreach ($this->_result as $row)
				foreach ($this->_inventory_ids[$row['id_member']] as $k => $session_text)
					$data[$k] = sprintf($session_text, $row['id_member'], $row['real_name']);

			// Destroy results for this section
			unset($this->_result);
		}
	}

	/**
	 * Who::trade()
	 *
	 * Formats the trade actions
	 * 
	 * @param array $data Returns the correct strings for each action
	 * @return void
	 */
	public function trade(&$data)
	{		
		if (!empty($this->_trade_ids) && allowedTo('shop_canTrade'))
		{
			$this->_query = Database::Get(0, count($this->_trade_ids), 'id_member', 'members', ['id_member', 'real_name'], 'WHERE id_member IN ({array_int:member_list})', false, '', ['member_list' => array_keys($this->_trade_ids)]);

			// Provide proper key
			foreach ($this->_query as $row)
				$this->_result[$row['id_member']] = $row;

			// Set their action on each - session/text to sprintf.
			foreach ($this->_result as $row)
				foreach ($this->_trade_ids[$row['id_member']] as $k => $session_text)
					$data[$k] = sprintf($session_text, $row['id_member'], $row['real_name']);

			// Destroy results for this section
			unset($this->_result);
		}
	}

}
