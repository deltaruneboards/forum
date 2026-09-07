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

class SMFShop extends Import
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
		's.id',
		's.name',
		's.image',
		's.desc',
		's.price',
		's.stock',
		's.module',
		's.info1',
		's.info2',
		's.info3',
		's.info4',
		's.input_needed',
		's.can_use_item',
		's.delete_after_use',
		's.category',
	];

	/**
	 * @var array The columns for the categories
	 */
	public $_cats_columns = [
		'c.id',
		'c.name',
		'c.count',
	];

	/**
	 * @var array The columns for the inventory
	 */
	public $_inv_columns = [
		'si.id',
		'si.ownerid',
		'si.itemid',
		'si.amtpaid',
		'si.trading',
		'si.tradecost',
	];

	/**
	 * @var array The columns for the cash
	 */
	public $_money = [
		'money',
		'moneyBank',
	];

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
	 * SMFShop::Verify()
	 * 
	 * Verifies that the items table from the mod we are converting from actually has data on it, because it's the most relevant table
	 * 
	 * @return bool True or False depending on the table having information or not
	 */
	public function Verify()
	{
		return (!empty(Database::list_columns('shop_items')) ? true : false);
	}

	/**
	 * SMFShop::importItems()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importItems()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000, 's.id', 'shop_items AS s', $this->_items_columns);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'name' => $row['name'],
				'image' => $row['image'],
				'desc' => $row['desc'],
				'price' => $row['price'],
				'stock' => $row['stock'],
				'catid' => $row['category'],
			];

		return $this->insertItems($this->_insert);
	}

	/**
	 * SMFShop::countItems()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countItems()
	{
		return (!empty(Database::list_columns('shop_items'))) ? Database::Count('shop_items', ['id']) : 0;
	}

	/**
	 * SMFShop::importInventory()
	 * 
	 * Search for the inventory items and inserts them into a very nice array of data
	 * 
	 * @return array The array of inventory items with their respective information
	 */
	public function importInventory()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000, 'si.id', 'shop_inventory AS si', $this->_inv_columns);

		// Put this information into a presentable array
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['ownerid'],
				'itemid' => $row['itemid'],
				'trading' => $row['trading'],
				'tradecost' => $row['tradecost'],
			];

		return $this->insertInventory($this->_insert);
	}

	/**
	 * SMFShop::countInventory()
	 * 
	 * Counts the amount of inventory items found in the table
	 * 
	 * @return int The total amount of inventory items found
	 */
	public function countInventory()
	{
		return (!empty(Database::list_columns('shop_inventory'))) ? Database::Count('shop_inventory', ['id']) : 0;
	}

	/**
	 * SMFShop::importCategories()
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
		$this->_result = Database::Get(0, 100000, 'c.id', 'shop_categories AS c', $this->_cats_columns);

		// Add the information of these categories into an array
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'name' => $row['name'],
			];

		return $this->insertCategories($this->_insert);
	}

	/**
	 * SMFShop::countCategories()
	 * 
	 * Counts the amount of categories found in the table
	 * 
	 * @return int The total amount of categories found
	 */
	public function countCategories()
	{
		return (!empty(Database::list_columns('shop_categories'))) ? Database::Count('shop_categories', ['id']) : 0;
	}

	/**
	 * SMFShop::importMoney()
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
	 * SMFShop::importBoardSettings()
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
	 * SMFShop::importSettings()
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
			'Shop_credits_topic' => !empty($modSettings['shopPointsPerTopic']) ? $modSettings['shopPointsPerTopic'] : $modSettings['Shop_credits_topic'],
			'Shop_credits_post' => !empty($modSettings['shopPointsPerPost']) ? $modSettings['shopPointsPerPost'] : $modSettings['Shop_credits_post'],
			'Shop_credits_word' => !empty($modSettings['shopPointsPerWord']) ? $modSettings['shopPointsPerWord'] : $modSettings['Shop_credits_word'],
			'Shop_credits_character' => !empty($modSettings['shopPointsPerChar']) ? $modSettings['shopPointsPerChar'] : $modSettings['Shop_credits_character'],
			'Shop_credits_limit' => !empty($modSettings['shopPointsLimit']) ? $modSettings['shopPointsLimit'] : $modSettings['Shop_credits_limit'],
			'Shop_enable_bank' => !empty($modSettings['shopBankEnabled']) ? $modSettings['shopBankEnabled'] : $modSettings['Shop_enable_bank'],
			'Shop_bank_interest' => !empty($modSettings['shopInterest']) ? $modSettings['shopInterest'] : $modSettings['Shop_bank_interest'],
			'Shop_bank_withdrawal_fee' => !empty($modSettings['shopFeeWithdraw']) ? $modSettings['shopFeeWithdraw'] : $modSettings['Shop_bank_withdrawal_fee'],
			'Shop_bank_deposit_fee' => !empty($modSettings['shopFeeDeposit']) ? $modSettings['shopFeeDeposit'] : $modSettings['Shop_bank_deposit_fee'],
			'Shop_bank_deposit_min' => !empty($modSettings['shopMinDeposit']) ? $modSettings['shopMinDeposit'] : $modSettings['Shop_bank_deposit_min'],
			'Shop_bank_withdrawal_min' => !empty($modSettings['shopMinWithdraw']) ? $modSettings['shopMinWithdraw'] : $modSettings['Shop_bank_withdrawal_min'],
			'Shop_items_perpage' => !empty($modSettings['shopItemsPerPage']) ? $modSettings['shopItemsPerPage'] : $modSettings['Shop_items_perpage'],
			'Shop_credits_register' => !empty($modSettings['shopRegAmount']) ? $modSettings['shopRegAmount'] : $modSettings['Shop_credits_register'],
			'Shop_credits_prefix' => !empty($modSettings['shopCurrencyPrefix']) ? $modSettings['shopCurrencyPrefix'] : $modSettings['Shop_credits_prefix'],
			'Shop_credits_suffix' => !empty($modSettings['shopCurrencySuffix']) ? $modSettings['shopCurrencySuffix'] : $modSettings['Shop_credits_suffix'],
		];

		return $this->convertSettings($this->_settings);
	}
}