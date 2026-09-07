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

if (!defined('SMF'))
	die('No direct access...');

class Categories extends Dashboard
{
	/**
	 * Categories::__construct()
	 *
	 * Create the array of subactions and load necessary extra language files
	 */
	function __construct()
	{
		// Array of sections
		$this->_subactions = [
			'index' => 'index',
			'add' => 'set_cat',
			'edit' => 'set_cat',
			'save' => 'save',
			'delete' => 'delete',
			'delete2' => 'delete2',
		];
		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'index';
	}

	public function main()
	{
		global $context;

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_cats'),
			'description' => Shop::getText('tab_cats_desc'),
			'tabs' => [
				'index' => ['description' => Shop::getText('tab_cats_desc')],
				'add' => ['description' => Shop::getText('cats_add_desc')],
			],
		];
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa].'#');
	}

	public function index()
	{
		global $context, $scripturl, $sourcedir, $modSettings, $txt;

		require_once($sourcedir . '/Subs-List.php');
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'catslist';
		$context['page_title'] = $txt['Shop_tab_cats']. ' - ' . $txt['Shop_tab_cats'];

		// The entire list
		$listOptions = [
			'id' => 'catslist',
			'title' => Shop::getText('tab_cats'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => '?action=admin;area=shopcategories;sa=index',
			'default_sort_col' => 'modify',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_categories AS sc', Database::$categories],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_categories AS sc', Database::$categories],
			],
			'no_items_label' => Shop::getText('no_cats'),
			'no_items_align' => 'center',
			'columns' => [
				'item_image' => [
					'header' => [
						'value' => Shop::getText('category_image'),
						'class' => 'centertext',
					],
					'data' => [
						'function' => function($row)
						{
							return Format::image($row['image']);
						},
						'style' => 'width: 4%',
						'class' => 'centertext',
					],
				],
				'item_name' => [
					'header' => [
						'value' => Shop::getText('item_name'),
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
				'description' => [
					'header' => [
						'value' => Shop::getText('item_description'),
					],
					'data' => [
						'db' => 'description',
						'style' => 'width: 18%',
					],
					'sort' =>  [
						'default' => 'description DESC',
						'reverse' => 'description',
					],
				],
				'items_in' => [
						'header' => [
							'value' => Shop::getText('total_items'),
							'class' => 'centertext',
						],
						'data' => [
							'function' => function($row)
							{
								return Database::Count('stshop_items AS s', Database::$items, 'WHERE s.catid = ' . $row['catid']);
							},
							'style' => 'width: 3%',
							'class' => 'centertext',
						],
					],
				'modify' => [
					'header' => [
						'value' => Shop::getText('item_modify'),
						'class' => 'centertext',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="'. $scripturl. '?action=admin;area=shopcategories;sa=edit;id=%1$d">'. Shop::getText('item_modify'). '</a>',
							'params' => [
								'catid' => true,
							],
						],
						'style' => 'width: 5%',
						'class' => 'centertext',
					],
					'sort' => [
						'default' => 'catid DESC',
						'reverse' => 'catid',
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
								'catid' => false,
							],
						],
						'class' => 'centertext',
						'style' => 'width: 2%',
					],
				],
			],
			'form' => [
				'href' => '?action=admin;area=shopcategories;sa=delete',
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
			$listOptions['additional_rows']['updated']['value'] .= Shop::getText('cats_' . (!isset($_REQUEST['deleted']) ? (!isset($_REQUEST['added']) ? (!isset($_REQUEST['updated']) ? '' : 'updated') : 'added') : 'deleted')) . '</div>';
		}
		// Let's finishem
		createList($listOptions);
	}

	public function set_cat()
	{
		global $context, $boardurl;

		// Template...
		loadTemplate('Shop/ShopAdmin');

		// Essential bits
		$context['sub_template'] = 'categories';
		$context[$context['admin_menu_name']]['current_subsection'] = 'add';
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_cats') . ' - '. Shop::getText('cats_add'),
			'description' => Shop::getText('cats_add_desc'),
		];
		// Item
		$context['shop_cat'] = [];
		// Images...
		$context['items_url'] = $boardurl . Shop::$itemsdir;
		$context['shop_images_list'] = Images::list();

		// Edit, or Add?
		if ($_REQUEST['sa'] == 'edit' && isset($_REQUEST['id']))
		{
			// Try to find this item
			if (empty($_REQUEST['id']) || empty(Database::Find('stshop_categories AS sc', 'sc.catid', (int) $_REQUEST['id'])))
				fatal_error(Shop::getText('cat_notfound'), false);

			// Get category
			$context['shop_category'] = Database::Get('', '', '', 'stshop_categories AS sc', Database::$categories, 'WHERE sc.catid = {int:catid}', true, '', ['catid' => (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0)]);

			// Index?
			$context[$context['admin_menu_name']]['current_subsection'] = 'index';
			$context[$context['admin_menu_name']]['tab_data'] = [
				'title' => Shop::getText('tab_cats') . ' - '. Shop::getText('cats_edit'),
				'description' => sprintf(Shop::getText('cats_edit_desc'), $context['shop_category']['name']),
			];
		}
		// Title
		$context['page_title'] = $context[$context['admin_menu_name']]['tab_data']['title'];
	}

	public function save()
	{
		global $context;

		// Data
		$this->_fields_data = [
			'catid' => (int) isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0,
			'name' => (string) isset($_REQUEST['catname']) ? Database::sanitize($_REQUEST['catname']) : '',
			'image' => (string) isset($_REQUEST['caticon']) ? Database::sanitize($_REQUEST['caticon']) : '',
			'description' => (string) isset($_REQUEST['catdesc']) ? Database::sanitize($_REQUEST['catdesc']) : '',
		];

		// Info in case of error
		$context[$context['admin_menu_name']]['current_subsection'] = empty($this->_fields_data['catid']) ? 'add' : 'edit';

		// You need to at least set a name!
		if (empty($this->_fields_data['name']))
			fatal_error(Shop::getText('cat_name_blank'), false);

		checkSession();
		$status = 'updated';

		// Add the item to the shop
		if (empty($this->_fields_data['catid']))
		{
			// Type
			foreach($this->_fields_data as $column => $type)
				$this->_fields_type[$column] = str_replace('integer', 'int', gettype($type));

			// Insert
			Database::Insert('stshop_categories', $this->_fields_data, $this->_fields_type);
			$status = 'added';
		}

		else
		{
			$this->_fields_type = '';
			// Type
			foreach($this->_fields_data as $column => $type)
				$this->_fields_type .= $column . ' = {'.str_replace('integer', 'int', gettype($type)).':'.$column.'}, ';

			// Update
			Database::Update('stshop_categories', $this->_fields_data, $this->_fields_type, 'WHERE catid = ' . $this->_fields_data['catid']);
		}
		redirectexit('action=admin;area=shopcategories;sa=index;'.$status);
	}

	public function delete()
	{
		global $context;

		// Template...
		loadTemplate('Shop/ShopAdmin');

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_cats') . ' - '. Shop::getText('cats_delete');
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => Shop::getText('cats_delete_desc'),
		];
		$context['sub_template'] = 'delete';
		$context['delete_description'] = Shop::getText('cats_delete_desc');

		checkSession();

		// If nothing was chosen to delete
		if (!isset($_REQUEST['delete']) || empty($_REQUEST['delete']))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Make sure all IDs are numeric
		foreach ($_REQUEST['delete'] as $key => $value)
			$_REQUEST['delete'][$key] = (int) $value;

		// We want to delete these items?
		$context['shop_delete'] = Database::Get(0, 1000, 'sc.name', 'stshop_categories AS sc', Database::$categories, 'WHERE sc.catid IN ({array_int:delete})', false, '', ['delete' => $_REQUEST['delete']]);

		// Set the format
		foreach ($context['shop_delete'] as $id => $var)
		{
			$context['shop_delete'][$id]['itemid'] = $var['catid'];
			$context['shop_delete'][$id]['category'] = true;
		}
	}

	public function delete2()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('tab_cats') . ' - '. Shop::getText('catss_delete');
		checkSession();

		// If nothing was chosen to delete (shouldn't happen, but meh)
		if (!isset($_REQUEST['delete']))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Collect the item ids
		if (isset($_REQUEST['deleteitems']) && !empty($_REQUEST['deleteitems']))
			$_REQUEST['deleteitems'] = Database::Get(0, 100000, 's.itemid', 'stshop_items AS s', ['s.itemid', 's.catid'], 'WHERE s.catid IN ({array_int:delete})', false, '', ['delete' => $_REQUEST['deleteitems']]);

		// Items using this module are... no longer using it
		Delete::cats($_REQUEST['delete'], 'action=admin;area=shopcategories;sa=index;deleted', $_REQUEST['deleteitems']);
	}
}