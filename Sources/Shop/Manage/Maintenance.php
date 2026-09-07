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
use Shop\Helper\Import;

if (!defined('SMF'))
	die('No direct access...');

class Maintenance extends Dashboard
{
	/**
	 * @var array Select and check for the columns we need
	 */
	private $_select_shop = [];

	/**
	 * @var string Store the compatible mod to make the import of data
	 */
	private $_convert_from;

	/**
	 * @var string The model we have available to import data
	 */
	private $_import;

	/**
	 * @var object Create a new instance with the desired model
	 */
	private $_importModel;

	/**
	 * Maintenance::__construct()
	 *
	 * Call certain administrative hooks and load the language files
	 */
	function __construct()
	{
		// Array of sections
		$this->_subactions = [
			'import' => 'import',
			'importdo' => 'import_do',
		];
		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'import';

		// Namespace
		$this->_import = 'Shop\\Helper\\Import\\';
	}

	public function main()
	{
		global $context;

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_maint') . ' - ' . Shop::getText('maint_import'),
			'description' => Shop::getText('maint_import_desc'),
			'tabs' => [
				'import' => ['description' => Shop::getText('maint_import_desc')],
			],
		];
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa] . '#');
	}

	public function import()
	{
		global $context;

		// Template
		loadTemplate('Shop/ShopAdmin');

		// Info
		$context['page_title'] = Shop::getText('tab_maint') . ' - ' . Shop::getText('maint_import');
		$context['sub_template'] = 'import';

		// Let's try to find what mod they had installed
		$this->discover();

		// From what mod?
		$context['shop_convert_from'] = $this->_convert_from;
		$context['shop_convert_data'] = $this->_select_shop;
	}

	public function discover()
	{
		// SMF Shop
		$this->_select_shop['smfshop'] = Database::list_columns('shop_items');
		if (!isset($this->_select_shop['smfshop']['stock']) || empty($this->_select_shop['smfshop']))
			unset($this->_select_shop['smfshop']);
		else
			$this->_convert_from = 'SMFShop';
		// ST Shop
		$this->_select_shop['stshop'] = Database::list_columns('shop_items');
		if (!isset($this->_select_shop['stshop']['count']) || empty($this->_select_shop['stshop']))
			unset($this->_select_shop['stshop']);
		else
			$this->_convert_from = 'STShop';
		// SA Shop
		$this->_select_shop['sashop'] = Database::list_columns('shop_item');
		if (empty($this->_select_shop['sashop']))
			unset($this->_select_shop['sashop']);
		else
			$this->_convert_from = 'SAShop';
	}

	public function import_do()
	{
		global $context, $user_info, $modSettings;

		$context['page_title'] = Shop::getText('tab_maint') . ' - '. Shop::getText('maint_import');
		$context[$context['admin_menu_name']]['current_subsection'] = 'import';

		// Make sure only admins
		if (empty($user_info['is_admin']))
			fatal_error(Shop::getText('import_only_admin'), false);

		// You already imported
		if (!empty($modSettings['Shop_importer_success']))
			fatal_error(Shop::getText('error_import_data'), false);

		checkSession();
		validateSession();

		// We got import select?
		$this->_convert_from = isset($_REQUEST['convert_from']) && !empty($_REQUEST['convert_from']) ? $_REQUEST['convert_from'] : 'SMFShop';
		$this->_import .= $this->_convert_from;

		// Create the respective model
		$this->_importModel = new $this->_import;

		// It's valid?
		if (empty($this->_importModel->Verify()))
			fatal_error(Shop::getText('import_empty'), false);
		// Time to delete everything then...
		else
			$this->_importModel->DropTables();

		// We only import if we have items, otherwise it's pointless
		$context['shop_found']['items_total'] = $this->_importModel->countItems();
		$context['shop_imported']['items_total'] = (!empty($context['shop_found']['items_total'])) ? $this->_importModel->importItems() : 0;

		// If we imported items, then we could try to do the rest
		if (!empty($context['shop_found']['items_total']))
		{
			// I'm just gonna use ternaries so I don't have to put a plethora of ifs in the template
			// Categories
			$context['shop_found']['cats_total'] = $this->_importModel->countCategories();
			$context['shop_imported']['cats_total'] = (!empty($context['shop_found']['cats_total'])) ? $this->_importModel->importCategories() : 0;

			// Inventory
			$context['shop_found']['inventory_total'] = $this->_importModel->countInventory();
			$context['shop_imported']['inventory_total'] = (!empty($context['shop_found']['inventory_total'])) ? $this->_importModel->importInventory() : 0;

			// Modules
			$context['shop_found']['modules_total'] = ($this->_convert_from == 'STShop') ? $this->_importModel->countModules() : 0;
			$context['shop_imported']['modules_total'] = (!empty($context['shop_found']['modules_total'])) ? $this->_importModel->importInventory() : 0;

			// Purchase Logs
			$context['shop_found']['logbuy_total'] = ($this->_convert_from != 'SMFShop') ? $this->_importModel->countPurchase() : 0;
			$context['shop_imported']['logbuy_total'] = (!empty($context['shop_found']['logbuy_total'])) ? $this->_importModel->importPurchases() : 0;

			// Bank Logs
			$context['shop_found']['logbank_total'] = ($this->_convert_from == 'STShop') ? $this->_importModel->countBank() : 0;
			$context['shop_imported']['logbank_total'] = (!empty($context['shop_found']['logbank_total'])) ? $this->_importModel->importBank() : 0;

			// Games Logs
			$context['shop_found']['loggames_total'] = ($this->_convert_from == 'STShop') ? $this->_importModel->countGames() : 0;
			$context['shop_imported']['loggames_total'] = (!empty($context['shop_found']['loggames_total'])) ? $this->_importModel->importGames() : 0;

			// Gifts Logs
			$context['shop_found']['loggift_total'] = ($this->_convert_from == 'STShop') ? $this->_importModel->countGifts() : 0;
			$context['shop_imported']['loggift_total'] = (!empty($context['shop_found']['loggifts_total'])) ? $this->_importModel->importGifts() : 0;
		}

		// Do the money next
		$context['shop_imported']['cash_total'] = ($this->_convert_from != 'STShop') ? $this->_importModel->importMoney() : 0;

		// Do the gamespass next
		$context['shop_imported']['gamespass_total'] = ($this->_convert_from == 'SAShop') ? $this->_importModel->importGamesPass() : 0;

		// Convert board settings
		$context['shop_imported']['boards_total'] = ($this->_convert_from != 'STShop') ? $this->_importModel->importBoardSettings() : 0;

		// Convert shop settings
		$context['shop_imported']['settings_total'] = ($this->_convert_from != 'STShop') ? $this->_importModel->importSettings() : 0;

		// Update the settings for the converter
		updateSettings(['Shop_importer_success' => 1], true);

		// Template
		loadTemplate('Shop/ShopAdmin');

		// Info
		$context['page_title'] = Shop::getText('tab_maint') . ' - ' . Shop::getText('maint_import');
		$context['sub_template'] = 'import_results';
	}
}