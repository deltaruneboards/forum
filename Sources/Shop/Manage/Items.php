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
use Shop\Helper\Delete;
use Shop\Helper\Format;
use Shop\Helper\Images;
use Shop\Modules;

if (!defined('SMF'))
	die('No direct access...');

class Items extends Dashboard
{
	/**
	 * @var array Additional information provided by the item file var or the item row in the database.
	 */
	protected $_item_info = [
		1 => '',
		2 => '',
		3 => '', 
		4 => '',
	];

	/**
	 * @var object We will create an object for the specified item if needed.
	 */
	private $_item_module = 'Shop\\Modules\\';

	/**
	 * Items::__construct()
	 *
	 * Create the array of subactions and load necessary extra language files
	 */
	function __construct()
	{
		// Array of sections
		$this->_subactions = [
			'index' => 'index',
			'add' => 'add',
			'add2' => 'set_item',
			'edit' => 'set_item',
			'save' => 'save',
			'delete' => 'delete',
			'delete2' => 'delete2',
			'upload' => 'upload',
			'upload2' => 'upload2',
		];
		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'index';
	}

	public function main()
	{
		global $context;

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_items'),
			'description' => Shop::getText('tab_items_desc'),
			'tabs' => [
				'index' => ['description' => Shop::getText('tab_items_desc')],
				'add' => ['description' => Shop::getText('items_add_desc')],
				'upload' => ['description' => Shop::getText('items_upload_desc')],
			],
		];
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa].'#');
	}

	public function index()
	{
		global $context, $scripturl, $sourcedir, $modSettings;

		require_once($sourcedir . '/Subs-List.php');
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'itemslist';
		$context['page_title'] = Shop::getText('tab_settings') . ' - ' . Shop::getText('tab_items');

		// The entire list
		$listOptions = [
			'id' => 'itemslist',
			'title' => Shop::getText('tab_items'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shopitems;sa=index',
			'default_sort_col' => 'modify',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_items AS s', array_merge(Database::$items, ['sc.name AS category, sm.file']), '', false, 'LEFT JOIN {db_prefix}stshop_categories AS sc ON (sc.catid = s.catid) LEFT JOIN {db_prefix}stshop_modules AS sm ON (sm.id = s.module)'],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_items AS s', Database::$items],
			],
			'no_items_label' => Shop::getText('no_items'),
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
						'style' => 'width: 4%',
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
						'style' => 'width: 12%',
					],
					'sort' =>  [
						'default' => 'name DESC',
						'reverse' => 'name',
					],
				],
				'price' => [
					'header' => [
						'value' => Shop::getText('item_price'),
						'class' => 'centertext',
					],
					'data' => [
						'db' => 'price',
						'style' => 'width: 4%',
						'class' => 'centertext',
					],
					'sort' =>  [
						'default' => 'price DESC',
						'reverse' => 'price',
					],
				],
				'stock' => [
					'header' => [
						'value' => Shop::getText('item_stock'),
						'class' => 'centertext',
					],
					'data' => [
						'db' => 'stock',
						'style' => 'width: 3%',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'stock DESC',
						'reverse' => 'stock',
					],
				],
				'category' => [
					'header' => [
						'value' => Shop::getText('item_category'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{							
							return (!empty($row['catid']) ? $row['category'] : Shop::getText('item_uncategorized'));
						},
						'style' => 'width: 6%',
					],
					'sort' => [
						'default' => 'category DESC',
						'reverse' => 'category',
					],
				],
				'module' => [
					'header' => [
						'value' => Shop::getText('item_module'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return (!empty($row['module']) ? $row['file'] .'.php' : Shop::getText('items_na'));
						},
						'style' => 'width: 5%',
					],
					'sort' => [
						'default' => 'file DESC',
						'reverse' => 'file',
					],
				],
				'status' => [
					'header' => [
						'value' => Shop::getText('item_status'),
						'class' => 'centertext',
					],
					'data' => [
						'function' => function($row)
						{
							return '<span class="main_icons warning_' . (!empty($row['status']) ? 'watch' : 'mute') . '"></span>';
						},
						'style' => 'width: 1%',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'status DESC',
						'reverse' => 'status',
					],
				],
				'modify' => [
					'header' => [
						'value' => Shop::getText('item_modify'),
						'class' => 'centertext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl. '?action=admin;area=shopitems;sa=edit;id=%1$d">'. Shop::getText('item_modify'). '</a>',
							'params' => [
								'itemid' => true,
							],
						],
						'style' => 'width: 5%',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'itemid DESC',
						'reverse' => 'itemid',
					]
				],
				'delete' => [
					'header' => [
						'value' => Shop::getText('delete', false). ' <input type="checkbox" onclick="invertAll(this, this.form, \'delete[]\');" class="input_check" />',
						'class' => 'centertext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<input type="checkbox" name="delete[]" value="%1$d" class="check" />',
							'params' => [
								'itemid' => false,
							],
						],
						'class' => 'centertext',
						'style' => 'width: 3%',
					],
				],
			],
			'form' => [
				'href' => '?action=admin;area=shopitems;sa=delete',
				'hidden_fields' => [
					$context['session_var'] => $context['session_id'],
				],
				'include_sort' => true,
				'include_start' => true,
			],
			'additional_rows' => [
				'submit' => [
					'position' => 'below_table_data',
					'value' => '<input type="submit" size="18" value="'.Shop::getText('delete', false). '" class="button" />',
				],
			],
		];
		// Info?
		if (isset($_REQUEST['deleted']) || isset($_REQUEST['added']) || isset($_REQUEST['updated']))
		{
			$listOptions['additional_rows']['updated'] = [
				'position' => 'top_of_list',
				'value' => '<div class="infobox">',
			];
			$listOptions['additional_rows']['updated']['value'] .= Shop::getText('items_' . (!isset($_REQUEST['deleted']) ? (!isset($_REQUEST['added']) ? (!isset($_REQUEST['updated']) ? '' : 'updated') : 'added') : 'deleted')) . '</div>';
		}
		// Let's finishem
		createList($listOptions);
	}

	public function add()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_items') . ' - '. Shop::getText('items_add');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];
		loadTemplate('Shop/ShopAdmin');
		$context['sub_template'] = 'items_add';
		$context['shop_modules'] = Database::Get(0, 1000, 'sm.name', 'stshop_modules AS sm', Database::$modules);
	}

	public function set_item()
	{
		global $context, $boardurl, $sourcedir;

		// Template...
		loadTemplate('Shop/ShopAdmin');

		// Essential bits
		$context['sub_template'] = 'items';
		$context[$context['admin_menu_name']]['current_subsection'] = 'index';
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_items') . ' - '. Shop::getText('items_add'),
			'description' => Shop::getText('items_add_desc'),
		];
		// Item
		$context['shop_item'] = [];
		// Images...
		$context['items_url'] = $boardurl . Shop::$itemsdir;
		$context['shop_images_list'] = Images::list();
		// ... and categories
		$context['shop_categories_list'] = Database::Get(0, 1000, 'sc.name', 'stshop_categories AS sc', Database::$categories);

		// Edit, or Add?
		if ($_REQUEST['sa'] == 'edit' && isset($_REQUEST['id']))
		{
			// Get item
			$context['shop_item'] = Database::Get('', '', '', 'stshop_items AS s', array_merge(Database::$items, ['sm.file']), 'WHERE s.itemid = {int:itemid}', true, 'LEFT JOIN {db_prefix}stshop_modules AS sm ON (sm.id = s.module)', ['itemid' => (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0)]);

			// No item
			if (empty($context['shop_item']))
				fatal_error(Shop::getText('item_notfound'), false);

			// We need to grab the extra input required by this item.
			// The actual information.
			$this->_item_info = [
				1 => $context['shop_item']['info1'],
				2 => $context['shop_item']['info2'],
				3 => $context['shop_item']['info3'], 
				4 => $context['shop_item']['info4']
			];

			// Change info
			$context[$context['admin_menu_name']]['tab_data'] = [
				'title' => Shop::getText('tab_items') . ' - '. Shop::getText('items_edit'),
				'description' => sprintf(Shop::getText('items_edit_desc'), $context['shop_item']['name']),
			];
		}
		else
		{
			// User should get here with item data
			checkSession();

			// Find out if the user included a module
			$module = isset($_REQUEST['module']) ? (isset($_REQUEST['item']) ? $_REQUEST['item'] : 0) : 0;

			// Get some info on the item
			$context['shop_item'] = Database::Get('', '', '', 'stshop_modules AS sm', Database::$modules, 'WHERE sm.id = {int:module}', true, '', ['module' => (int) $module]);
			$context['shop_item']['module'] = $module;

			// Change description
			$context[$context['admin_menu_name']]['current_subsection'] = 'add';
			$context[$context['admin_menu_name']]['tab_data']['description'] = sprintf(Shop::getText('items_add_desc' . (!empty($context['shop_item']['name']) ? '2' : '_default')), !empty($context['shop_item']['name']) ? $context['shop_item']['name'] : '');
		}

		// Title
		$context['page_title'] = $context[$context['admin_menu_name']]['tab_data']['title'];

		// Module??
		if (!empty($context['shop_item']['module']))
		{
			// Store it somewhere
			$this->_item_module .= $context['shop_item']['file'];

			// Is the item still there?
			if (!class_exists($this->_item_module))
				fatal_error(sprintf(Shop::getText('item_no_module'), Shop::$modulesdir . $context['shop_item']['file']), false);

			// Create a new object
			$itemModel = new $this->_item_module;

			// Success?
			if ($itemModel === NULL)
				fatal_error(sprintf(Shop::getText('item_error'), Shop::$modulesdir . $context['shop_item']['file']), false);

			// Adding?
			if ($_REQUEST['sa'] === 'edit')
				$itemModel->item_info = $this->_item_info;

			// Can we edit the getAddInput() info?
			if (!empty($itemModel->addInput_editable))
			{
				$context['shop_item']['addInputEditable'] = true;
				$context['shop_item']['addInput'] = $itemModel->getAddInput();
			}
			else
				$context['shop_item']['addInputEditable'] = false;
		}
	}

	public function save()
	{
		global $context;

		// Data
		$this->_fields_data = [
			'itemid' => (int) isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0,
			'name' => (string) isset($_REQUEST['itemname']) ? un_htmlspecialchars(Database::sanitize($_REQUEST['itemname'])) : '',
			'image' => (string) isset($_REQUEST['icon']) ? Database::sanitize($_REQUEST['icon']) : '',
			'description' => (string) isset($_REQUEST['itemdesc']) ? Database::sanitize($_REQUEST['itemdesc']) : '',
			'price' => (int) isset($_REQUEST['itemprice']) && !empty($_REQUEST['itemprice']) ? $_REQUEST['itemprice'] : 0,
			'stock' => (int) isset($_REQUEST['itemstock']) && !empty($_REQUEST['itemstock']) ? $_REQUEST['itemstock'] : 0,
			'module' => (int) isset($_REQUEST['module']) ? $_REQUEST['module'] : 0,
			'info1' => (int) isset($_REQUEST['info1']) && !empty($_REQUEST['info1']) ? $_REQUEST['info1'] : 0,
			'info2' => (int) isset($_REQUEST['info2']) && !empty($_REQUEST['info2']) ? $_REQUEST['info2'] : 0,
			'info3' => (int) isset($_REQUEST['info3']) && !empty($_REQUEST['info3']) ? $_REQUEST['info3'] : 0,
			'info4' => (int) isset($_REQUEST['info4']) && !empty($_REQUEST['info4']) ? $_REQUEST['info4'] : 0,
			'input_needed' => (int) isset($_REQUEST['require_input']) && !empty($_REQUEST['require_input']) ? 1 : 0,
			'can_use_item' => (int) isset($_REQUEST['can_use_item']) && !empty($_REQUEST['can_use_item']) ? 1 : 0,
			'delete_after_use' => (int) isset($_REQUEST['itemdelete']) ? 1 : 0,
			'catid' => (int) isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 0,
			'status' => (int) isset($_REQUEST['itemstatus']) ? 1 : 0,
			'itemlimit' => (int) isset($_REQUEST['itemlimit']) && !empty($_REQUEST['itemlimit']) ? $_REQUEST['itemlimit'] : 0,
		];

		// Info in case of error
		$context[$context['admin_menu_name']]['current_subsection'] = empty($this->_fields_data['itemid']) ? 'add' : 'edit';

		// You need to at least set a name!
		if (empty($this->_fields_data['name']))
			fatal_error(Shop::getText('item_name_blank'), false);

		checkSession();
		$status = 'updated';

		// Add the item to the shop
		if (empty($this->_fields_data['itemid']))
		{
			// Type
			foreach($this->_fields_data as $column => $type)
				$this->_fields_type[$column] = str_replace('integer', 'int', gettype($type));

			// Insert
			Database::Insert('stshop_items', $this->_fields_data, $this->_fields_type);
			$status = 'added';
		}

		else
		{
			$this->_fields_type = '';
			// Remove those that don't require updating
			unset($this->_fields_data['input_needed']);
			unset($this->_fields_data['can_use_item']);
			unset($this->_fields_data['module']);
			unset($this->_fields_data['function']);

			// Type
			foreach($this->_fields_data as $column => $type)
				$this->_fields_type .= $column . ' = {'.str_replace('integer', 'int', gettype($type)).':'.$column.'}, ';

			// Update
			Database::Update('stshop_items', $this->_fields_data, $this->_fields_type, 'WHERE itemid = ' . $this->_fields_data['itemid']);
		}

		redirectexit('action=admin;area=shopitems;sa=index;'.$status);
	}

	public function delete()
	{
		global $context;

		// Template...
		loadTemplate('Shop/ShopAdmin');

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_items') . ' - '. Shop::getText('items_delete');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('items_delete_desc'),
		];
		$context['sub_template'] = 'delete';
		$context['delete_description'] = Shop::getText('item_delete_also');

		checkSession();

		// If nothing was chosen to delete
		if (!isset($_REQUEST['delete']) || empty($_REQUEST['delete']))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Make sure all IDs are numeric
		foreach ($_REQUEST['delete'] as $key => $value)
			$_REQUEST['delete'][$key] = (int) $value;

		// We want to delete these items?
		$context['shop_delete'] = Database::Get(0, 1000, 's.name', 'stshop_items AS s', Database::$items, 'WHERE s.itemid IN ({array_int:delete})', false, '', ['delete' => $_REQUEST['delete']]);
	}

	public function delete2()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_items') . ' - '. Shop::getText('items_delete');
		checkSession();

		// If nothing was chosen to delete (shouldn't happen, but meh)
		if (!isset($_REQUEST['delete']))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Remove all entries of this item from the logs and redirect
		Delete::items($_REQUEST['delete'], 'action=admin;area=shopitems;sa=index;deleted');
	}

	public function upload()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_items') . ' - '. Shop::getText('items_upload');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];
		loadTemplate('Shop/ShopAdmin');
		$context['sub_template'] = 'upload';
	}

	public function upload2()
	{
		global $boarddir, $context;

		// Page stuff
		$context['page_title'] = Shop::getText('tab_items') . ' - '. Shop::getText('items_upload');
		$context[$context['admin_menu_name']]['current_subsection'] = 'upload';

		// No file? That can't be
		if (!isset($_FILES['newitem']) || empty($_FILES['newitem']))
			fatal_error(Shop::getText('file_error_empty'), false);

		checkSession();

		// Get GD
		$getGD = get_extension_funcs('gd');
		$gd = in_array('imagecreatetruecolor', $getGD) && function_exists('imagecreatetruecolor');
		unset($getGD);

		// Process uploaded file
		if (isset($_FILES['newitem']['name']) && $_FILES['newitem']['name'] != '')
		{
			$sizes = @getimagesize($_FILES['newitem']['tmp_name']);
			// No size, then it's probably not a valid pic.
			if ($sizes === false)
			{
				@unlink($_FILES['newitem']['tmp_name']);
				fatal_error(Shop::getText('file_error_type1'), false);
			}

			// Get the filesize
			$filesize = $_FILES['newitem']['size'];
			// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
			$extensions = [
				1 => 'gif',
				2 => 'jpeg',
				3 => 'png',
				6 => 'bmp',
				7 => 'tiff',
				8 => 'tiff',
				9 => 'jpeg',
				14 => 'iff',
			];
			$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';
			$filename = basename($_FILES['newitem']['name']);
			$target_file = $boarddir.Shop::$itemsdir.$filename;

			// Check if file already exists
			if (file_exists($target_file))
			{
				fatal_error(Shop::getText('file_already_exists'), false);
				$uploadOk = 0;
			}
			// Check file size
			if ($_FILES['newitem']['size'] > 10000)
				fatal_error(Shop::getText('file_too_large'), false);

			move_uploaded_file($_FILES['newitem']['tmp_name'], $target_file);
			@chmod($target_file, 0644);

			// Get me out of here
			redirectexit('action=admin;area=shopitems;sa=upload;success');
		}
		// No luck? Sorry...
		else
			redirectexit('action=admin;area=shopitems;sa=upload;error');
	}
}