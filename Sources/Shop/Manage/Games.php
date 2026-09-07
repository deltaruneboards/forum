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

if (!defined('SMF'))
	die('No direct access...');

class Games extends Dashboard
{
	/**
	 * Games::__construct()
	 *
	 * Create the array of subactions and load necessary extra language files
	 */
	function __construct()
	{
		// Load Games language
		loadLanguage('Shop/Games');

		// Array of sections
		$this->_subactions = [
			'slots' => 'slots',
			'lucky2' => 'lucky2',
			'number' => 'number',
			'pairs' => 'pairs',
			'dice' => 'dice',
		];
		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'slots';
	}

	public function main()
	{
		global $context;

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_settings'). ' - ' . Shop::getText('tab_games'),
			'description' => Shop::getText('games_desc'),
			'tabs' => [
				'slots' => ['description' => Shop::getText('settings_slots_desc')],
				'lucky2' => ['description' => Shop::getText('settings_lucky2_desc')],
				'number' => ['description' => Shop::getText('settings_number_desc')],
				'pairs' => ['description' => Shop::getText('settings_pairs_desc')],
				'dice' => ['description' => Shop::getText('settings_dice_desc')],
			],
		];

		// Integrate settings from/for Addons
		call_integration_hook('integrate_shop_games_settings', [&$context[$context['admin_menu_name']]['tab_data']['tabs'], &$this->_subactions]);

		// Load the settings
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa].'#');
	}

	public function slots($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings') . ' - ' . Shop::getText('tab_games') . ' - ' . Shop::getText('games_slots');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];
		$context[$context['admin_menu_name']]['tab_data']['description'] = Shop::getText('settings_slots_desc');

		$config_vars = [
			['int', 'Shop_settings_slots_losing', 'min' => '0'],
			'',
			['int', 'Shop_settings_slots_7', 'min' => '0'],
			['int', 'Shop_settings_slots_bell', 'min' => '0'],
			['int', 'Shop_settings_slots_cherry', 'min' => '0'],
			['int', 'Shop_settings_slots_lemon', 'min' => '0'],
			['int', 'Shop_settings_slots_orange', 'min' => '0'],
			['int', 'Shop_settings_slots_plum', 'min' => '0'],
			['int', 'Shop_settings_slots_dollar', 'min' => '0'],
			['int', 'Shop_settings_slots_melon', 'min' => '0'],
			['int', 'Shop_settings_slots_grapes', 'min' => '0'],
		];
		Database::Save($config_vars, $return_config, 'slots', 'shopgames');
	}

	public function lucky2($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings') . ' - ' . Shop::getText('tab_games') . ' - ' . Shop::getText('games_lucky2');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		$config_vars = [
			['int', 'Shop_settings_lucky2_losing', 'min' => '0'],
			'',
			['int', 'Shop_settings_lucky2_price', 'min' => '0'],
		];
		Database::Save($config_vars, $return_config, 'lucky2', 'shopgames');
	}

	public function number($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings') . ' - ' . Shop::getText('tab_games') . ' - ' . Shop::getText('games_number');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		$config_vars = [
			['int', 'Shop_settings_number_losing', 'min' => '0'],
			'',
			['int', 'Shop_settings_number_complete', 'min' => '0'],
			['int', 'Shop_settings_number_firsttwo', 'min' => '0'],
			['int', 'Shop_settings_number_secondtwo', 'min' => '0'],
			['int', 'Shop_settings_number_firstlast', 'min' => '0'],
		];
		Database::Save($config_vars, $return_config, 'number', 'shopgames');
	}

	public function Pairs($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings') . ' - ' . Shop::getText('tab_games') . ' - ' . Shop::getText('games_pairs');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		$config_vars = [
			['int', 'Shop_settings_pairs_losing', 'min' => '0'],

			['title', 'Shop_settings_pairs_clubs', 'help' => Shop::getText('settings_pairs_price')],
			['int', 'Shop_settings_pairs_clubs_1', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_2', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_3', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_4', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_5', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_6', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_7', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_8', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_9', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_10', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_11', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_12', 'min' => '0'],
			['int', 'Shop_settings_pairs_clubs_13', 'min' => '0'],

			['title', 'Shop_settings_pairs_diamonds', 'help' => Shop::getText('settings_pairs_price')],
			['int', 'Shop_settings_pairs_diam_1', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_2', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_3', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_4', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_5', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_6', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_7', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_8', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_9', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_10', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_11', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_12', 'min' => '0'],
			['int', 'Shop_settings_pairs_diam_13', 'min' => '0'],

			['title', 'Shop_settings_pairs_hearts', 'help' => Shop::getText('settings_pairs_price')],
			['int', 'Shop_settings_pairs_hearts_1', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_2', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_3', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_4', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_5', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_6', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_7', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_8', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_9', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_10', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_11', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_12', 'min' => '0'],
			['int', 'Shop_settings_pairs_hearts_13', 'min' => '0'],

			['title', 'Shop_settings_pairs_spades', 'help' => Shop::getText('settings_pairs_price')],
			['int', 'Shop_settings_pairs_spades_1', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_2', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_3', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_4', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_5', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_6', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_7', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_8', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_9', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_10', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_11', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_12', 'min' => '0'],
			['int', 'Shop_settings_pairs_spades_13', 'min' => '0'],
		];
		Database::Save($config_vars, $return_config, 'pairs', 'shopgames');
	}

	public function Dice($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings') . ' - ' . Shop::getText('tab_games') . ' - ' . Shop::getText('games_dice');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		$config_vars = [
			['int', 'Shop_settings_dice_losing', 'min' => '0'],
			'',
			['int', 'Shop_settings_dice_1', 'min' => '0'],
			['int', 'Shop_settings_dice_2', 'min' => '0'],
			['int', 'Shop_settings_dice_3', 'min' => '0'],
			['int', 'Shop_settings_dice_4', 'min' => '0'],
			['int', 'Shop_settings_dice_5', 'min' => '0'],
			['int', 'Shop_settings_dice_6', 'min' => '0'],
		];
		Database::Save($config_vars, $return_config, 'dice', 'shopgames');
	}
}