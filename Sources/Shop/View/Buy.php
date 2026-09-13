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
use Shop\Helper\Images;
use Shop\Helper\Format;
use Shop\Helper\Log;

if (!defined('SMF'))
	die('No direct access...');

class Buy
{
	/**
	 * @var int The item being traded.
	 */
	private $_purchase;

	/**
	 * @var array Information about the item being traded.
	 */
	private $_item;

	/**
	 * @var int Carrying limit.
	 */
	private $_limit;

	/**
	 * Buy::__construct()
	 *
	 * Not tabs on this section, but we still need to create instance of log
	 */
	function __construct()
	{
		// Prepare to log the purchase
		$this->_log = new Log;

		// Check if user is allowed to access this section
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_canBuy');
	}

	public function main()
	{
		global $context, $scripturl, $modSettings, $sourcedir, $boardurl;

		// Set all the page stuff
		require_once($sourcedir . '/Subs-List.php');
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'itemslist';
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_buy');
		$context['template_layers'][] = 'options';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=buy',
			'name' => Shop::getText('main_buy'),
		];
		// Images...
		$context['items_url'] = $boardurl . Shop::$itemsdir;
		$context['shop_images_list'] = Images::list();
		// ... and categories
		$context['shop_categories_list'] = Database::Get(0, 1000, 'sc.name', 'stshop_categories AS sc', Database::$categories);
		$context['form_url'] = '?action=shop;sa=buy'. (isset($_REQUEST['cat']) && $_REQUEST['cat'] >= 0 ? ';cat='.$_REQUEST['cat'] : '');

		// The entire list
		$listOptions = [
			'id' => 'itemslist',
			//'title' => Shop::getText('main_buy'),
			'items_per_page' => !empty($modSettings['Shop_items_perpage']) ? $modSettings['Shop_items_perpage'] : 15,
			'base_href' => $context['form_url'],
			'default_sort_col' => 'item_buy',
			'default_sort_dir' => 'ASC',
			'get_items' => [
				'function' => 'Shop\Helper\Database::Get',
				'params' => ['stshop_items AS s', array_merge(Database::$items, ['sc.name AS category']), 'WHERE s.status = 1 AND s.stock > 0'. (isset($_REQUEST['cat']) && $_REQUEST['cat'] >= 0 ? ' AND s.catid = {int:cat}' : ''), false, 'LEFT JOIN {db_prefix}stshop_categories AS sc ON (s.catid = sc.catid)', ['cat' => isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 0]],
			],
			'get_count' => [
				'function' => 'Shop\Helper\Database::Count',
				'params' => ['stshop_items AS s', Database::$items, 'WHERE s.status = 1'. (isset($_REQUEST['cat']) && $_REQUEST['cat'] >= 0 ? ' AND s.catid = {int:cat}' : ''), '', ['cat' => isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 0]],
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
							return Format::image($row['image'], $row['name']);
						},
						'style' => 'width: 10%',
						'class' => 'centertext',
					],
				],
				'item_name' => [
					'header' => [
						'value' => Shop::getText('item_name'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row)
						{
							return '<span style="font-size:110%">'.$row['name'] .'</span>' . (!empty($row['description']) ? '<br/><span class="smalltext">' . $row['description'] . '</span>' : '');
						},
						'class' => 'lefttext',
						'style' => 'width: 35%',
					],
					'sort' =>  [
						'default' => 'name',
						'reverse' => 'name DESC',
					],
				],
				'item_details' => [
					'header' => [
						'value' => Shop::getText('item_details'),
						'class' => 'lefttext',
					],
					'data' => [
						'function' => function($row) use ($scripturl)
						{
							// Category
							$details = '<strong>' . Shop::getText('item_category') . ': </strong>' . (!empty($row['catid']) ? $row['category'] : Shop::getText('item_uncategorized'));

							// Stock
							$details .= '<br><strong>' . Shop::getText('item_stock') . ': </strong>' . $row['stock'];

							// Who owns this
							$details .= '<br><a href="'. $scripturl. '?action=shop;sa=owners;id='. $row['itemid']. '">'. Shop::getText('buy_item_who_this'). '</a>';

							return $details;
						},
						'class' => 'lefttext',
						'style' => 'width: 20%',
					],
					'sort' =>  [
						'default' => 'stock DESC',
						'reverse' => 'stock',
					],
				],
				'item_options' => [
					'header' => [
						'value' => Shop::getText('item_price'),
						'class' => 'centertext',
					],
					'data' => [
						'function' => function($row)
						{
							return (empty($row['price']) ? '<i>' . Shop::getText('item_free') . '</i>' : Format::cash($row['price']));
						},
						'class' => 'centertext',
					],
					'sort' =>  [
						'default' => 'price DESC',
						'reverse' => 'price',
					],
				],
				'item_buy' => [
					'header' => [
						'value' => Shop::getText('item_buy'),
						'class' => 'centertext',
					],
					'data' => [
						'function' => function($row) use ($context, $scripturl)
						{
							// If we don\'t have stock... Sold out!
							if (empty($row['stock']))
								return Shop::getText('buy_soldout');

							// User doesn't have enough money
							elseif ($context['user']['shopMoney'] < $row['price'])
								return '<i>' . Shop::getText('buy_notenough') . '</i>';

							// Enough money? Buy it!
							else
								return '<a href="'. $scripturl. '?action=shop;sa=buy2;id='. $row['itemid']. ';'. $context['session_var'] .'='. $context['session_id'] .'">'. Shop::getText('item_buy'). '</a>';
						},
						'class' => 'centertext',
					],
					'sort' =>  [
						'default' => 'itemid DESC',
						'reverse' => 'itemid',
					],
				],
			],
		];
		// Let's finishem
		createList($listOptions);
	}

	public function purchase()
	{
		global $context, $user_info, $modSettings, $scripturl;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_buy');
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=buy',
			'name' => Shop::getText('main_buy'),
		];

		checkSession('request');

		// You cannot get here without an item
		if (!isset($_REQUEST['id']) || empty($_REQUEST['id']))
			fatal_error(Shop::getText('buy_something'), false);

		// Make sure is an int
		$this->_purchase = (int) $_REQUEST['id'];

		// Get the item's information
		$this->_item = Database::Get('', '', '', 'stshop_items AS s', Database::$items, 'WHERE s.status = 1 AND s.itemid = {int:itemid}', true, '', ['itemid' => $this->_purchase]);

		// We found and item?
		if (empty($this->_item))
			fatal_error(Shop::getText('item_notfound'), false);

		// How many of this item does the user own?
		$this->_limit = Database::Count('stshop_inventory AS si', Database::$inventory, 'WHERE itemid = {int:id} AND userid = {int:userid}', '', ['id' => $this->_purchase, 'userid' => $user_info['id']]);

		// Already reached the limit?
		if ((!empty($this->_item['itemlimit'])) && ($this->_limit >= $this->_item['itemlimit']))
			fatal_error(Shop::getText('item_limit_reached'), false);
		// Item valid and enabled then... Do we have items in stock?
		elseif (empty($this->_item['stock']))
			fatal_error(sprintf(Shop::getText('buy_item_nostock'), $this->_item['name']), false);
		// Fine... Does the user have enough money to buy this?
		elseif ($user_info['shopMoney'] < $this->_item['price'])
			fatal_error(sprintf(Shop::getText('buy_item_notenough'), $this->_item['name'], Format::cash($this->_item['price'] - $user_info['shopMoney'])), false);

		// Proceed
		// Handle item purchase and money deduction and log it
		$this->_log->purchase($this->_purchase, $user_info['id'], $this->_item['price']);

		// Redirect to the inventory?
		redirectexit('action=shop;sa=inventory;sort=item_date;purchased');
	}
}
