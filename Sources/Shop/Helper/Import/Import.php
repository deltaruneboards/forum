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

abstract class Import
{
	/**
	 * @var array Store the tables that we will drop once we begin importing data
	 */
	public $_tables = [
		'stshop_items',
		'stshop_categories',
		'stshop_inventory',
		'stshop_log_bank',
		'stshop_log_buy',
		'stshop_log_games',
		'stshop_log_gift',
	];

	/**
	 * @var array Store the data we want to insert or update
	 */
	public $_insert = [];

	/**
	 * @var array|string Saves the type of information we are converting/importing
	 */
	public $_types;

	/**
	 * @var int The amount of items successfully imported
	 */
	public $_total_imported = 0;

	/**
	 * @var array Stores the items data in an organized array for later inserting it onto the table
	 */
	public $_shop_items = [];

	/**
	 * @var array Stores the categories data in an organized array for later inserting it onto the table
	 */
	public $_shop_categories = [];

	/**
	 * @var array Stores the inventory data in an organized array for later inserting it onto the table
	 */
	public $_shop_inventory = [];

	/**
	 * @var array Stores the modules data in an organized array for later inserting it onto the table
	 */
	public $_shop_modules = [];

	/**
	 * @var array Stores the items log data in an organized array for later inserting it onto the table
	 */
	public $_shop_log = [];

	/**
	 * Import::Verify()
	 * 
	 * Verifies that the items table from the mod we are converting from actually has data on it, because it's the most relevant table
	 * 
	 * @return bool True or False depending on the table having information or not
	 */
	abstract public function Verify();

	/**
	 * Import::importItems()
	 * 
	 * Search for the items and inserts them into a very nice array of data
	 * 
	 * @return array The array of items with their respective information
	 */
	abstract public function importItems();

	/**
	 * Import::countItems()
	 * 
	 * Counts the amount of items found in the table
	 * 
	 * @return int The total amount of items found
	 */
	abstract public function countItems();

	/**
	 * Import::importCategories()
	 * 
	 * Search for the categories and inserts them into a very nice array of data
	 * 
	 * @return array The array of categories with their respective information
	 */
	abstract public function importCategories();

	/**
	 * Import::countCategories()
	 * 
	 * Counts the amount of categories found in the table
	 * 
	 * @return int The total amount of categories found
	 */
	abstract public function countCategories();

	/**
	 * Import::importInventory()
	 * 
	 * Search for the inventory items and inserts them into a very nice array of data
	 * 
	 * @return array The array of inventory items with their respective information
	 */
	abstract public function importInventory();

	/**
	 * Import::countInventory()
	 * 
	 * Counts the amount of inventory items found in the table
	 * 
	 * @return int The total amount of inventory items found
	 */
	abstract public function countInventory();

	/**
	 * Import::DropTables()
	 * 
	 * Will delete the information form the selected tables
	 * 
	 * @return void
	 */
	public function DropTables()
	{
		foreach ($this->_tables as $table)
			Database::Truncate($table);
	}

	/**
	 * Import::insertItems()
	 * 
	 * Will complete the importing of the shop items
	 *
	 * @param array The array of information obatained from the items stored in the old table
	 * @return int The total of items imported
	 */
	public function insertItems($items)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_total_imported = 0;

		foreach($items as $id_item => $item)
			$this->_shop_items[] = [
				'itemid' => (int) $id_item,
				'name' => (string) $item['name'],
				'image' => (string) $item['image'],
				'description' => (string) $item['desc'],
				'price' => (int) $item['price'],
				'stock' => (int) isset($item['stock']) ? $item['stock'] : 0,
				'module' => (int) isset($item['module']) ? $item['module'] : 0,
				'info1' => (int) isset($item['info1']) ? $item['info1'] : 0,
				'info2' => (int) isset($item['info2']) ? $item['info2'] : 0,
				'info3' => (int) isset($item['info3']) ? $item['info3'] : 0,
				'info4' => (int) isset($item['info4']) ? $item['info4'] : 0,
				'input_needed' => (int) isset($item['input']) ? $item['input'] : 0,
				'can_use_item' => (int) isset($item['can']) ? $item['can'] : 0,
				'delete_after_use' => (int) isset($item['delete']) ? $item['delete'] : 0,
				'catid' => (int) isset($item['catid']) ? $item['catid'] : 0,
				'status'=> (int) isset($item['status']) ? $item['status'] : 1,
				'itemlimit' => (int) isset($item['itemlimit']) ? $item['itemlimit'] : 0,
			];

		// Import these items and count them again
		if (!empty($this->_shop_items))
		{
			// Type
			foreach($this->_shop_items[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the items into the database
			Database::Insert('stshop_items', $this->_shop_items, $this->_types, ['itemid'], 'replace');

			// Get our total of items imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::insertCategories()
	 * 
	 * Will complete the importing of the shop categories
	 *
	 * @param array The array of information obatained from the caategories stored in the old table
	 * @return int The total of categories imported
	 */
	public function insertCategories($categories)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_total_imported = 0;

		foreach($categories as $id_cat => $cat)
			$this->_shop_categories[] = [
				'catid' => (int) $id_cat,
				'name' => (string) $cat['name'],
				'image' => (string) isset($cat['image']) ? $cat['image'] : 'blank.gif',
				'description' => (string) isset($cat['desc']) ? $cat['desc'] : '',
			];

		// Import these categories and count them again
		if (!empty($this->_shop_categories))
		{
			// Type
			foreach($this->_shop_categories[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the categories into the database
			Database::Insert('stshop_categories', $this->_shop_categories, $this->_types, ['catid'], 'replace');

			// Get our total of categories imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::insertInventory()
	 * 
	 * Will complete the importing of the shop inventory items
	 *
	 * @param array The array of information obatained from the inventory items stored in the old table
	 * @return int The total of inventory items imported
	 */
	public function insertInventory($inventory)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_total_imported = 0;

		foreach($inventory as $id_inv => $inv)
			$this->_shop_inventory[] = [
				'id' => (int) $id_inv,
				'userid' => (int) $inv['userid'],
				'itemid' => (int) $inv['itemid'],
				'trading' => (int) isset($inv['trading']) ? $inv['trading'] : 0,
				'tradecost' => (int) isset($inv['tradecost']) ? $inv['tradecost'] : 0,
				'date' => (int) isset($inv['date']) ? $inv['date'] : time(),
				'tradedate' => (int) isset($inv['tradedate']) ? $inv['tradedate'] : time(),
				'fav' => (int) isset($inv['fav']) ? $inv['fav'] : 0,
			];

		// Import these items and count them again
		if (!empty($this->_shop_inventory))
		{
			// Type
			foreach($this->_shop_inventory[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the items into the database
			Database::Insert('stshop_inventory', $this->_shop_inventory, $this->_types, ['id']);

			// Get our total of items imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::insertModules()
	 * 
	 * Will complete the importing of the shop categories
	 *
	 * @param array The array of information obatained from the modules stored in the old table
	 * @return int The total of categories imported
	 */
	public function insertModules($modules)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_total_imported = 0;

		foreach($modules as $id_module => $module)
			$this->_shop_modules[] = [
				'id' => (int) $id_module,
				'name' => (string) $module['name'],
				'description' => (string) isset($module['desc']) ? $module['desc'] : '',
				'price' => (int) isset($module['price']) ? $module['price'] : 0,
				'author' => (string) isset($module['author']) ? $module['author'] : '',
				'email' => (string) isset($module['email']) ? $module['email'] : '',
				'require_input' => (int) isset($module['require']) ? $module['require'] : '',
				'can_use_item' => (int) isset($module['can']) ? $module['can'] : '',
				'editable_input' => (int) isset($module['editable']) ? $module['editable'] : '',
				'web' => (string) isset($module['web']) ? $module['web'] : '',
				'file' => (string) isset($module['file']) ? $module['file'] : '',
			];

		// Import these categories and count them again
		if (!empty($this->_shop_modules))
		{
			// Type
			foreach($this->_shop_modules[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the categories into the database
			Database::Insert('stshop_modules', $this->_shop_modules, $this->_types, ['id'], 'replace');

			// Get our total of categories imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::convertMoney()
	 * 
	 * Will complete the importing of the cash
	 *
	 * @param array The columns that have the cash for the users
	 * @return int The total of users that got their cash converted
	 */
	public function convertMoney($money)
	{
		global $smcFunc;

		// Convert the shop money
		$this->_types = 'shopMoney = shopMoney + '.$money[0].', ' . 'shopBank = shopBank + '.$money[1].', ';

		// Update the money for users that used to have money
		Database::Update('members', [], $this->_types, 'WHERE '.$money[0] . ' > 0 OR '.$money[1].' > 0');

		// Users that got back their ca$h
		return $smcFunc['db_affected_rows']();
	}

	/**
	 * Import::convertGamesPass()
	 * 
	 * Will complete the importing of the cash
	 *
	 * @param array The column that includes gamepass
	 * @return int The total of users that got their gamepass days converted
	 */
	public function convertGamespass($gamespass)
	{
		global $smcFunc;

		// Convert the shop money
		$this->_types = 'gamesPass = ' .  $gamespass . ',';

		// Update the money for users that used to have money
		Database::Update('members', [], $this->_types, 'WHERE '.$gamespass . ' > 0');

		// Users that got back their ca$h
		return $smcFunc['db_affected_rows']();
	}

	/**
	 * Import::convertBoardSettings()
	 * 
	 * Will complete the importing of the board settings
	 *
	 * @param array The columns that have the board settings data
	 * @return int The total of boards that got the settings imported
	 */
	public function convertBoardSettings($boards)
	{
		global $smcFunc;

		// Convert the board settings
		$this->_types = 'Shop_credits_count = '.$boards[0].', Shop_credits_topic = '.$boards[1].', Shop_credits_post = '.$boards[2].', Shop_credits_bonus = '.$boards[3].',';

		// Update the board settings
		Database::Update('boards', [], $this->_types, 'WHERE '.$boards[0] . ' > 0 OR '.$boards[1].' > 0 OR '.$boards[2].' > 0 OR '.$boards[3].' > 0');

		// Boards updated with the settings
		return $smcFunc['db_affected_rows']();
	}

	/**
	 * Import::convertSettings()
	 * 
	 * Will import settings from the old data
	 *
	 * @param array The settings and their corresponding values
	 * @return int The total of settings impoorted
	 */
	public function convertSettings($shop_settings)
	{
		// Update the settings
		updateSettings($shop_settings);

		return count($shop_settings);
	}

	/**
	 * Import::insertPurchases()
	 * 
	 * Will complete the importing of the shop inventory items
	 *
	 * @param array The array of information obatained from the inventory items stored in the old table
	 * @return int The total of inventory items imported
	 */
	public function insertPurchases($purchases)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_shop_log = [];
		$this->_total_imported = 0;

		foreach($purchases as $id_log => $log)
			$this->_shop_log[] = [
				'id' => (int) $id_log,
				'itemid' => (int) $log['itemid'],
				'invid' => (int) isset($log['invid']) ? $log['invid'] : 0,
				'userid' => (int) $log['userid'],
				'sellerid' => (int) isset($log['sellerid']) ? $log['sellerid'] : 0,
				'amount' => (int) isset($log['amount']) ? $log['amount'] : 0,
				'fee' => (int) isset($log['fee']) ? $log['fee'] : 0,
				'date' => (int) isset($log['date']) ? $log['date'] : time(),
			];

		// Import these items and count them again
		if (!empty($this->_shop_log))
		{
			// Type
			foreach($this->_shop_log[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the items into the database
			Database::Insert('stshop_log_buy', $this->_shop_log, $this->_types, ['id'], 'replace');

			// Get our total of items imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::insertTransactions()
	 * 
	 * Will complete the importing of the shop inventory items
	 *
	 * @param array The array of information obatained from the inventory items stored in the old table
	 * @return int The total of inventory items imported
	 */
	public function insertTransactions($transactions)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_shop_log = [];
		$this->_total_imported = 0;

		foreach($transactions as $id_log => $log)
			$this->_shop_log[] = [
				'id' => (int) $id_log,
				'userid' => (int) $log['userid'],
				'amount' => (int) isset($log['amount']) ? $log['amount'] : 0,
				'fee' => (int) isset($log['fee']) ? $log['fee'] : 0,
				'action' => (string) isset($log['action']) ? $log['action'] : 'deposit',
				'type' => (int) isset($log['type']) ? $log['type'] : 0,
				'date' => (int) isset($log['date']) ? $log['date'] : time(),
			];

		// Import these items and count them again
		if (!empty($this->_shop_log))
		{
			// Type
			foreach($this->_shop_log[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the items into the database
			Database::Insert('stshop_log_bank', $this->_shop_log, $this->_types, ['id'], 'replace');

			// Get our total of items imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::insertGames()
	 * 
	 * Will complete the importing of the shop inventory items
	 *
	 * @param array The array of information obatained from the inventory items stored in the old table
	 * @return int The total of inventory items imported
	 */
	public function insertGames($games)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_shop_log = [];
		$this->_total_imported = 0;

		foreach($games as $id_log => $log)
			$this->_shop_log[] = [
				'id' => (int) $id_log,
				'userid' => (int) $log['userid'],
				'amount' => (int) isset($log['amount']) ? $log['amount'] : 0,
				'game' => (string) isset($log['game']) ? $log['game'] : 0,
				'date' => (int) isset($log['date']) ? $log['date'] : time(),
			];

		// Import these items and count them again
		if (!empty($this->_shop_log))
		{
			// Type
			foreach($this->_shop_log[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the items into the database
			Database::Insert('stshop_log_games', $this->_shop_log, $this->_types, ['id'], 'replace');

			// Get our total of items imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}

	/**
	 * Import::insertGames()
	 * 
	 * Will complete the importing of the shop inventory items
	 *
	 * @param array The array of information obatained from the inventory items stored in the old table
	 * @return int The total of inventory items imported
	 */
	public function insertGifts($gifts)
	{
		global $smcFunc;

		$this->_insert = [];
		$this->_types = [];
		$this->_shop_log = [];
		$this->_total_imported = 0;

		foreach($gifts as $id_log => $log)
			$this->_shop_log[] = [
				'id' => (int) $id_log,
				'userid' => (int) $log['userid'],
				'receiver' => (int) $log['receiver'],
				'amount' => (int) isset($log['amount']) ? $log['amount'] : 0,
				'itemid' => (int) isset($log['itemid']) ? $log['itemid'] : 0,
				'invid' => (int) isset($log['invid']) ? $log['invid'] : 0,
				'message' => (string) isset($log['message']) ? $log['message'] : '',
				'is_admin' => (int) isset($log['is_admin']) ? $log['is_admin'] : 0,
				'date' => (int) isset($log['date']) ? $log['date'] : time(),
			];

		// Import these items and count them again
		if (!empty($this->_shop_log))
		{
			// Type
			foreach($this->_shop_log[0] as $column => $type)
				$this->_types[$column] = str_replace('integer', 'int', gettype($type));

			// Insert the items into the database
			Database::Insert('stshop_log_gift', $this->_shop_log, $this->_types, ['id'], 'replace');

			// Get our total of items imported
			$this->_total_imported = $smcFunc['db_affected_rows']();
		}

		return $this->_total_imported;
	}
}