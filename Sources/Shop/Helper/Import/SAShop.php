<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Helper\Import;

use Shop\Helper\Database;

if (!defined('SMF'))
	die('No direct access...');

class SAShop extends Import
{
	/**
	 * @var array Store the data we want to insert or update
	 */
	public $_insert = [];

	/**
	 * @var array Store the results for the search queries
	 */
	public $_result = [];

	/**
	 * @var array Store the settings we are importing
	 */
	public $_settings = [];

	/**
	 * @var array The columns for the items
	 */
	public $_items_columns = [
		's.itemid',
		's.name',
		's.image',
		's.description',
		's.price',
		's.count',
		's.module',
		's.info1',
		's.info2',
		's.info3',
		's.info4',
		's.input_needed',
		's.can_use_item',
		's.delete_after_use',
		's.catid',
		's.status',
	];

	/**
	 * @var array The columns for the categories
	 */
	public $_cats_columns = [
		'c.catid',
		'c.name',
		'c.image',
		'c.description',
		'c.count',
	];

	/**
	 * @var array The columns for the inventory
	 */
	public $_inv_columns = [
		'si.id',
		'si.userid',
		'si.itemid',
		'si.amtpaid',
		'si.trading',
		'si.tradecost',
	];
	
	/**
	 * @var array The columns for the purchase log
	 */
	public $_log_buy = [
		'lb.id',
		'lb.userid',
		'lb.itemid',
		'lb.amtpaid',
	];

	/**
	 * @var array The columns for the cash
	 */
	public $_money = [
		'cash',
		'cashBank',
	];

	public $_gamespass = 'games_pass';

	/**
	 * @var array The columns for board settings
	 */
	public $_boards = [
		'countMoney',
		'shop_pertopic',
		'shop_perpost',
		'shop_bonuses',
	];

	/**
	 * SAShop::Verify()
	 * 
	 * Verifies that the items table from the mod we are converting from actually has data on it, because it's the most relevant table
	 * 
	 * @return bool True or False depending on the table having information or not
	 */
	public function Verify()
	{
		return (!empty(Database::list_columns('shop_item')) ? true : false);
	}

	/**
	 * SAShop::importItems()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importItems()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000, 's.itemid', 'shop_item AS s', $this->_items_columns);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['itemid']] = [
				'id' => $row['itemid'],
				'name' => $row['name'],
				'image' => $row['image'],
				'desc' => $row['description'],
				'price' => $row['price'],
				'stock' => $row['count'],
				'catid' => $row['catid'],
			];

		return $this->insertItems($this->_insert);
	}

	/**
	 * SAShop::countItems()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countItems()
	{
		return (!empty(Database::list_columns('shop_item'))) ? Database::Count('shop_item', ['itemid']) : 0;
	}

	/**
	 * SAShop::importInventory()
	 * 
	 * Search for the inventory items and inserts them into a very nice array of data
	 * 
	 * @return array The array of inventory items with their respective information
	 */
	public function importInventory()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000, 'si.id', 'shop_property AS si', $this->_inv_columns);

		// Put this information into a presentable array
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'itemid' => $row['itemid'],
				'trading' => $row['trading'],
				'tradecost' => $row['tradecost'],
			];

		return $this->insertInventory($this->_insert);
	}

	/**
	 * SAShop::countInventory()
	 * 
	 * Counts the amount of inventory items found in the table
	 * 
	 * @return int The total amount of inventory items found
	 */
	public function countInventory()
	{
		return (!empty(Database::list_columns('shop_property'))) ? Database::Count('shop_property', ['id']) : 0;
	}

	/**
	 * SAShop::importCategories()
	 * 
	 * Search for the categories and inserts them into a very nice array of data
	 * 
	 * @return array The array of categories with their respective information
	 */
	public function importCategories()
	{
		$this->_insert = [];
		$this->_result = [];

		// Get the categories stored from the old data
		$this->_result = Database::Get(0, 100000, 'c.catid', 'shop_category AS c', $this->_cats_columns);

		// Add the information of these categories into an array
		foreach ($this->_result as $row)
			$this->_insert[$row['catid']] = [
				'id' => $row['catid'],
				'name' => $row['name'],
				'image' => $row['image'],
				'description' => $row['description'],
			];

		return $this->insertCategories($this->_insert);
	}

	/**
	 * SAShop::countCategories()
	 * 
	 * Counts the amount of categories found in the table
	 * 
	 * @return int The total amount of categories found
	 */
	public function countCategories()
	{
		return (!empty(Database::list_columns('shop_category'))) ? Database::Count('shop_category', ['catid']) : 0;
	}

	/**
	 * SAShop::importMoney()
	 * 
	 * Checks for any cash/money from the old data
	 * 
	 * @return bool The amount of users found with cash or money in either the bank or the pocket
	 */
	public function importMoney()
	{
		$this->_result = true;
		$this->_insert = [];
		$this->_insert = Database::list_columns('members');

		foreach ($this->_money as $key => $value)
			if (!isset($this->_insert[$value]))
				$this->_result = false;

		return (!empty($this->_result) ? $this->convertMoney($this->_money) : 0);
	}

	/**
	 * SAShop::importGamesPass()
	 * 
	 * Checks for any gamespass from the old data
	 * 
	 * @return bool The amount of users found with gamespass days
	 */
	public function importGamesPass()
	{
		$this->_result = true;
		$this->_insert = [];
		$this->_insert = Database::list_columns('members');

		if (!isset($this->_insert[$this->_gamespass]))
			$this->_result = false;

		return (!empty($this->_result) ? $this->convertGamesPass($this->_gamespass) : 0);
	}

	/**
	 * SAShop::importBoardSettings()
	 * 
	 * Checks for any board settings using the old data
	 * 
	 * @return bool The amount of board settings found that have data
	 */
	public function importBoardSettings()
	{
		$this->_result = true;
		$this->_insert = [];
		$this->_insert = Database::list_columns('boards');

		foreach ($this->_boards as $key => $value)
			if (!isset($this->_insert[$value]))
				$this->_result = false;

		return (!empty($this->_result) ? $this->convertBoardSettings($this->_boards) : 0);
	}

	/**
	 * SAShop::importSettings()
	 * 
	 * Builds an array with the old settings and attempts to find data
	 * 
	 * @return array The array with the settings and their respective data
	 */
	public function importSettings()
	{
		global $modSettings;

		// Select the desired settings to convert
		$this->_settings = [
			'Shop_credits_topic' => !empty($modSettings['shopPointTopic']) ? $modSettings['shopPointTopic'] : $modSettings['Shop_credits_topic'],
			'Shop_credits_post' => !empty($modSettings['shopPointPost']) ? $modSettings['shopPointPost'] : $modSettings['Shop_credits_post'],
			'Shop_credits_word' => !empty($modSettings['shopPointWord']) ? $modSettings['shopPointWord'] : $modSettings['Shop_credits_word'],
			'Shop_credits_character' => !empty($modSettings['shopPointChar']) ? $modSettings['shopPointChar'] : $modSettings['Shop_credits_character'],
			'Shop_credits_limit' => !empty($modSettings['shopPointLimit']) ? $modSettings['shopPointLimit'] : $modSettings['Shop_credits_limit'],
			'Shop_enable_games' => !empty($modSettings['shop_Enable_gameroom']) ? $modSettings['shop_Enable_gameroom'] : $modSettings['Shop_enable_games'],
			'Shop_enable_gift' => !empty($modSettings['shop_Enable_Gift']) ? $modSettings['shop_Enable_Gift'] : $modSettings['Shop_enable_gift'],
			'Shop_enable_trade' => !empty($modSettings['shop_Enable_trade']) ? $modSettings['shop_Enable_trade'] : $modSettings['Shop_enable_trade'],
			'Shop_enable_stats' => !empty($modSettings['shop_Enable_Stats']) ? $modSettings['shop_Enable_Stats'] : $modSettings['Shop_enable_stats'],
			'Shop_enable_bank' => !empty($modSettings['shop_Enable_Bank']) ? $modSettings['shop_Enable_Bank'] : $modSettings['Shop_enable_bank'],
			'Shop_bank_interest' => !empty($modSettings['shopBInterest']) ? $modSettings['shopBInterest'] : $modSettings['Shop_bank_interest'],
			'Shop_bank_withdrawal_fee' => !empty($modSettings['shopwfee']) ? $modSettings['shopwfee'] : $modSettings['Shop_bank_withdrawal_fee'],
			'Shop_bank_deposit_fee' => !empty($modSettings['shopdfee']) ? $modSettings['shopdfee'] : $modSettings['Shop_bank_deposit_fee'],
			'Shop_bank_deposit_min' => !empty($modSettings['shopdmin']) ? $modSettings['shopdmin'] : $modSettings['Shop_bank_deposit_min'],
			'Shop_bank_withdrawal_min' => !empty($modSettings['shopwmin']) ? $modSettings['shopwmin'] : $modSettings['Shop_bank_withdrawal_min'],
			'Shop_bank_deposit_max' => !empty($modSettings['shopdmax']) ? $modSettings['shopdmax'] : $modSettings['Shop_bank_deposit_min'],
			'Shop_bank_withdrawal_max' => !empty($modSettings['shopwmax']) ? $modSettings['shopwmax'] : $modSettings['Shop_bank_withdrawal_min'],
			'Shop_items_perpage' => !empty($modSettings['shopItemsPerPage']) ? $modSettings['shopItemsPerPage'] : $modSettings['Shop_items_perpage'],
			'Shop_credits_register' => !empty($modSettings['shopRegAmount']) ? $modSettings['shopRegAmount'] : $modSettings['Shop_credits_register'],
			'Shop_credits_prefix' => !empty($modSettings['shopprefix']) ? $modSettings['shopprefix'] : $modSettings['Shop_credits_prefix'],
			'Shop_credits_suffix' => !empty($modSettings['shopsurfix']) ? $modSettings['shopsurfix'] : $modSettings['Shop_credits_suffix'],
		];

		return $this->convertSettings($this->_settings);
	}

	/**
	 * SAShop::importPurchases()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importPurchases()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000000, 'lb.id', 'shop_purchhis AS lb', $this->_log_buy);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'itemid' => $row['itemid'],
				'amount' => $row['amtpaid'],
			];

		return $this->insertPurchases($this->_insert);
	}

	/**
	 * SAShop::countPurchase()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countPurchase()
	{
		return (!empty(Database::list_columns('shop_purchhis'))) ? Database::Count('shop_purchhis', ['id']) : 0;
	}
}