<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Helper;

if (!defined('SMF'))
	die('No direct access...');

class Log
{
	/**
	 * @var array Gift columns.
	 */
	private $_gift;

	/**
	 * @var array Buy columns.
	 */
	private $_buy;

	/**
	 * @var array Inventory columns.
	 */
	private $_inventory;

	/**
	 * @var array Helper array for inserting multiple values.
	 */
	private $_insert_rows = [];

	/**
	 * Log::__construct()
	 *
	 * Set the apropiate types vand values for the columns
	 */
	function __construct()
	{
		// Gift
		$this->_gift = [
			'userid' => 'int',
			'receiver' => 'int',
			'amount' => 'int',
			'itemid' => 'int',
			'invid' => 'int',
			'message' => 'string',
			'is_admin' => 'int',
			'date' => 'int',
		];
		// Buy
		$this->_buy = [
			'itemid' => 'int',
			'invid' => 'int',
			'userid' => 'int',
			'sellerid' => 'int',
			'amount' => 'int',
			'fee' => 'int',
			'date' => 'int',
		];
		// Inventory
		$this->_inventory = [
			'userid' => 'int',
			'itemid' => 'int',
			'trading' => 'int',
			'tradecost' => 'int',
			'date' => 'int',
			'tradedate' => 'int',
			'fav' => 'int',
		];
		// Bank
		$this->_bank = [
			'userid' => 'int',
			'amount' => 'int',
			'fee' => 'int',
			'action' => 'string',
			'type' => 'int',
			'date' => 'int',
		];
		// Games
		$this->_games = [
			'userid' => 'int',
			'amount' => 'int',
			'game' => 'string',
			'date' => 'int',
		];
	}

	public function credits($sender, $users, $amount, $admin = false, $message = '')
	{
		// Send credits over to the user
		Database::Update('members', ['users' => $users, 'credits' => $amount], 'shopMoney = shopMoney + {int:credits},', 'WHERE id_member' . (is_array($users) ? ' IN ({array_int:users})' : ' = {int:users}'));

		// Log the information
		$users = is_array($users) ? $users : [$users];
		foreach ($users as $memID)
		{
			$this->_insert_rows[] = [
				$sender,
				$memID,
				$amount,
				0,
				0,
				$message,
				!empty($admin) ? 1 : 0,
				time()
			];
		}
		Database::Insert('stshop_log_gift', $this->_insert_rows, $this->_gift);

		// Regular user? Deduct these credits
		if (empty($admin))
			Database::Update('members', ['user' => $sender, 'credits' => $amount], 'shopMoney = shopMoney - {int:credits}', 'WHERE id_member = {int:user}');
	}

	public function items($sender, $users, $item, $invid = 0, $admin = false, $message = '')
	{
		// Log the information
		$users = is_array($users) ? $users : [$users];
		foreach ($users as $memID)
		{
			$this->_insert_rows[] = [
				$sender,
				$memID,
				0,
				$item,
				$invid,
				$message,
				!empty($admin) ? 1 : 0,
				time()
			];
		}
		Database::Insert('stshop_log_gift', $this->_insert_rows, $this->_gift);
		unset($this->_insert_rows);

		// Regular user? Just switch the item from one inventory to another
		if (empty($admin))
			Database::Update('stshop_inventory', ['user' => $users[0], 'invid' => $invid], 'userid = {int:user}', 'WHERE id = {int:invid}');
		// Admin? Insert a new item on each inventory, and reduce stock?
		else
		{
			foreach ($users as $memID)
			{
				$this->_insert_rows[] = [
					$memID,
					$item,
					0,
					0,
					time(),
					0,
					0
				];
			}
			Database::Insert('stshop_inventory', $this->_insert_rows, $this->_inventory);
			Database::Update('stshop_items', ['stock' => count($users), 'itemid' => $item], 'stock = stock - {int:stock}', 'WHERE itemid = {int:itemid}');
		}
	}

	public function purchase($itemid, $userid, $amount, $seller = 0, $fee = 0, $invid = 0)
	{
		// Remove the credits from the buyer
		Database::Update('members', ['user' => $userid, 'credits' => $amount], 'shopMoney = shopMoney - {int:credits},', 'WHERE id_member = {int:user}');

		// Is user purchasing an item fro items list?
		if (empty($seller))
		{
			// Insert in inventory
			Database::Insert('stshop_inventory', [
				$userid,
				$itemid,
				0,
				0,
				time(),
				0,
				0
			], $this->_inventory);

			// Discount stock
			Database::Update('stshop_items', ['count' => 1, 'itemid' => $itemid], 'stock = stock - {int:count},', 'WHERE itemid = {int:itemid}');
		}
		// Purchasing at the trade center
		else
		{
			// Move item to buyer inventory
			Database::Update('stshop_inventory', ['user' => $userid, 'invid' => $invid, 'date' => time()], 'userid = {int:user}, trading = 0, tradecost = 0, tradedate = 0, date = {int:date},', 'WHERE id = {int:invid}');

			// Add the credits to the seller
			Database::Update('members', ['user' => $seller, 'paid' => $amount, 'fee' => $fee], 'shopMoney = shopMoney + ({int:paid} - {int:fee}),', 'WHERE id_member = {int:user}');
		}

		// Insert info in the log
		Database::Insert('stshop_log_buy', [
			$itemid,
			$invid,
			$userid,
			$seller,
			$amount,
			$fee,
			time()
		], $this->_buy);
	}

	public function bank($userid, $amount, $trans, $fee = 0, $type = 'deposit')
	{
		// Move forward with the transaction
		Database::Update('members', ['user' => $userid, 'amount' => $amount, 'fee' => $fee], 'shopMoney = shopMoney '. ($trans == 'deposit' ? '-' : '+') .' {int:amount}' .(!empty($fee) && empty($type) ? ' - {int:fee}' : '') . ', shopBank = shopBank '. ($trans == 'withdrawal' ? '-' : '+') .' {int:amount}' .(!empty($fee) && !empty($type) ? ' - {int:fee}' : '') . ',', 'WHERE id_member = {int:user}');

		// Insert information in the log
		Database::Insert('stshop_log_bank', [
			$userid,
			$amount,
			$fee,
			$trans,
			$type,
			time(),
		], $this->_bank);
	}

	public function game($userid, $amount, $game)
	{
		// Update money
		Database::Update('members', ['user' => $userid, 'amount' => $amount], 'shopMoney = shopMoney + {int:amount},', 'WHERE id_member = {int:user}');

		// Log the changes
		Database::Insert('stshop_log_games', [
			$userid,
			$amount,
			$game,
			time(),
		], $this->_games);
	}
}