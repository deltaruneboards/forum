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

class STShop extends Import
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
		's.module',
		's.count',
		's.info1',
		's.info2',
		's.info3',
		's.info4',
		's.input_needed',
		's.can_use_item',
		's.delete_after_use',
		's.catid',
		's.status',
		's.itemlimit',
	];

	/**
	 * @var array The columns for the categories
	 */
	public $_cats_columns = [
		'c.catid',
		'c.name',
		'c.image',
		'c.description',
	];

	/**
	 * @var array The columns for the inventory
	 */
	public $_inv_columns = [
		'si.id',
		'si.userid',
		'si.itemid',
		'si.trading',
		'si.tradecost',
		'si.date',
		'si.tradedate',
		'si.fav',
	];

	/**
	 * @var array The columns for the modules
	 */
	public $_modules_columns = [
		'm.id',
		'm.name',
		'm.description',
		'm.price',
		'm.author',
		'm.email',
		'm.require_input',
		'm.edtiable_input',
		'm.web',
		'm.file',
		'm.can_use',
	];
	
	/**
	 * @var array The columns for the purchase log
	 */
	public $_log_buy = [
		'lb.id',
		'lb.itemid',
		'lb.invid',
		'lb.userid',
		'lb.sellerid',
		'lb.amount',
		'lb.fee',
		'lb.date',
	];

	/**
	 * @var array The columns for the bank log
	 */
	public $_log_bank = [
		'lb.id',
		'lb.userid',
		'lb.amount',
		'lb.fee',
		'lb.type',
		'lb.date',
	];

	/**
	 * @var array The columns for the games log
	 */
	public $_log_games = [
		'lb.id',
		'lb.userid',
		'lb.amount',
		'lb.game',
		'lb.date',
	];

	/**
	 * @var array The columns for the gifts log
	 */
	public $_log_gifts = [
		'lb.id',
		'lb.userid',
		'lb.receiver',
		'lb.amount',
		'lb.itemid',
		'lb.invid',
		'lb.message',
		'lb.is_admin',
		'lb.date',
	];

	/**
	 * STShop::Verify()
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
	 * STShop::importItems()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importItems()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000, 's.itemid', 'shop_items AS s', $this->_items_columns);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['itemid']] = [
				'id' => $row['itemid'],
				'name' => $row['name'],
				'image' => $row['image'],
				'desc' => $row['description'],
				'price' => $row['price'],
				'stock' => $row['count'],
				'module' => $row['module'],
				'info1' => $row['info1'],
				'info2' => $row['info2'],
				'info3' => $row['info3'],
				'info4' => $row['info4'],
				'input' => $row['input_needed'],
				'can' => $row['can_use_item'],
				'delete' => $row['delete_after_use'],
				'catid' => $row['catid'],
				'status' => $row['status'],
				'itemlimit' => $row['itemlimit'],
			];

		return $this->insertItems($this->_insert);
	}

	/**
	 * STShop::countItems()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countItems()
	{
		return (!empty(Database::list_columns('shop_items'))) ? Database::Count('shop_items', ['itemid']) : 0;
	}

	/**
	 * STShop::importInventory()
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
				'userid' => $row['userid'],
				'itemid' => $row['itemid'],
				'trading' => $row['trading'],
				'tradecost' => $row['tradecost'],
				'date' => $row['date'],
				'tradedate' => $row['tradedate'],
				'fav' => $row['fav'],
			];

		return $this->insertInventory($this->_insert);
	}

	/**
	 * STShop::countInventory()
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
	 * STShop::importCategories()
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
		$this->_result = Database::Get(0, 100000, 'c.catid', 'shop_categories AS c', $this->_cats_columns);

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
	 * STShop::countCategories()
	 * 
	 * Counts the amount of categories found in the table
	 * 
	 * @return int The total amount of categories found
	 */
	public function countCategories()
	{
		return (!empty(Database::list_columns('shop_categories'))) ? Database::Count('shop_categories', ['catid']) : 0;
	}

	/**
	 * STShop::importModules()
	 * 
	 * Search for the modules and inserts them into a very nice array of data
	 * 
	 * @return array The array of modules with their respective information
	 */
	public function importModules()
	{
		$this->_insert = [];
		$this->_result = [];

		// Get the categories stored from the old data
		$this->_result = Database::Get(0, 100000, 'm.id', 'shop_modules AS m', $this->_modules_columns);

		// Add the information of these categories into an array
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'name' => $row['name'],
				'description' => $row['description'],
				'price' => $row['price'],
				'author' => $row['author'],
				'email' => $row['email'],
				'require' => $row['require_input'],
				'editable' => $row['editable_input'],
				'web' => $row['web'],
				'file' => $row['file'],
				'can' => $row['can_use'],
			];

		return $this->insertModules($this->_insert);
	}

	/**
	 * STShop::countModules()
	 * 
	 * Counts the amount of categories found in the table
	 * 
	 * @return int The total amount of categories found
	 */
	public function countModules()
	{
		return (!empty(Database::list_columns('shop_modules'))) ? Database::Count('shop_modules', ['id']) : 0;
	}

	/**
	 * STShop::importPurchases()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importPurchases()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000000, 'lb.id', 'shop_log_buy AS lb', $this->_log_buy);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'itemid' => $row['itemid'],
				'invid' => $row['invid'],
				'amount' => $row['amount'],
				'sellerid'=> $row['sellerid'],
				'fee' => $row['fee'],
				'date' => $row['date'],
			];

		return $this->insertPurchases($this->_insert);
	}

	/**
	 * STShop::countPurchase()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countPurchase()
	{
		return (!empty(Database::list_columns('shop_log_buy'))) ? Database::Count('shop_log_buy', ['id']) : 0;
	}

	/**
	 * STShop::importBank()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importBank()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000000, 'lb.id', 'shop_log_bank AS lb', $this->_log_bank);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'amount' => $row['amount'],
				'fee' => $row['fee'],
				'action' => ($row['type'] > 1) ? 'withdrawal' : 'deposit',
				'type'=> ($row['type'] == 1 || $row['type'] == 2) ? 1 : 0,
				'date' => $row['date'],
			];

		return $this->insertTransactions($this->_insert);
	}

	/**
	 * STShop::countBank()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countBank()
	{
		return (!empty(Database::list_columns('shop_log_bank'))) ? Database::Count('shop_log_bank', ['id']) : 0;
	}

	/**
	 * STShop::importGames()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importGames()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000000, 'lb.id', 'shop_log_games AS lb', $this->_log_games);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'amount' => $row['amount'],
				'game' => $row['game'],
				'date' => $row['date'],
			];

		return $this->insertGames($this->_insert);
	}

	/**
	 * STShop::countGames()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countGames()
	{
		return (!empty(Database::list_columns('shop_log_games'))) ? Database::Count('shop_log_games', ['id']) : 0;
	}

	/**
	 * STShop::importGifts()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	public function importGifts()
	{
		$this->_insert = [];
		$this->_result = [];
		$this->_result = Database::Get(0, 100000000, 'lb.id', 'shop_log_gift AS lb', $this->_log_gifts);

		// Create an array with the obtained data from the items
		foreach ($this->_result as $row)
			$this->_insert[$row['id']] = [
				'id' => $row['id'],
				'userid' => $row['userid'],
				'receiver' => $row['receiver'],
				'amount' => $row['amount'],
				'itemid' => $row['itemid'],
				'invid' => $row['invid'],
				'message' => $row['message'],
				'is_admin' => $row['is_admin'],
				'date' => $row['date'],
			];

		return $this->insertGifts($this->_insert);
	}

	/**
	 * STShop::countGifts()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	public function countGifts()
	{
		return (!empty(Database::list_columns('shop_log_gift'))) ? Database::Count('shop_log_gift', ['id']) : 0;
	}
}