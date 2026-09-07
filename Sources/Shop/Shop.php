<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop;

if (!defined('SMF'))
	die('No direct access...');

class Shop
{
	/**
	 * @var int Version of the mod
	 */
	public static $version;

	/**
	 * @var string Addons directory
	 */
	public static $addonsdir;

	/**
	 * @var string Items directory
	 */
	public static $itemsdir;

	/**
	 * @var string Modules directory
	 */
	public static $modulesdir;

	/**
	 * @var string Games directory
	 */
	public static $gamesdir;

	/**
	 * @var string Suppoort site link
	 */
	public static $supportSite;


	public static function initialize()
	{
		// Version and paths
		self::$version = '4.1.12';
		self::$addonsdir = '/Shop/Integration/Addons/';
		self::$itemsdir = '/shop_items/items/';
		self::$modulesdir = '/Shop/Modules/';
		self::$gamesdir = '/shop_items/games/';
		self::$supportSite = 'https://smftricks.com/index.php?action=.xml;sa=news;board=51;limit=10;type=rss2';

		// Default Values
		self::setDefaults();

		// Hooks
		self::defineHooks();
		self::userHooks();
		self::addonHooks();
	}

	/**
	 * Shop::setDefaults()
	 *
	 * Sets almost every setting to a default value
	 * @return void
	 */
	public static function setDefaults()
	{
		global $modSettings;

		$defaults = [
			'Shop_enable_shop' => 0,
			'Shop_enable_games' => 0,
			'Shop_enable_bank' => 1,
			'Shop_enable_gift' => 1,
			'Shop_enable_trade' => 1,
			'Shop_enable_stats' => 0,
			'Shop_enable_maintenance' => 0,
			'Shop_credits_register' => 5,
			'Shop_credits_topic' => 10,
			'Shop_credits_post' => 2,
			'Shop_credits_likes_post' => 0,
			'Shop_credits_word' => 0,
			'Shop_credits_character' => 0,
			'Shop_credits_limit' => 0,
			'Shop_bank_interest' => 2,
			'Shop_bank_interest_yesterday' => 0,
			'Shop_bank_withdrawal_fee' => 0,
			'Shop_bank_deposit_fee' => 0,
			'Shop_bank_withdrawal_max' => 0,
			'Shop_bank_withdrawal_min' => 0,
			'Shop_bank_deposit_max' => 0,
			'Shop_bank_deposit_min' => 0,
			'Shop_credits_prefix' => '',
			'Shop_credits_suffix' => 'Credits',
			'Shop_images_width' => '32px',
			'Shop_images_height' => '32px',
			'Shop_items_perpage' => 15,
			'Shop_items_trade_fee' => 0,
			'Shop_display_pocket' => 0,
			'Shop_display_pocket_placement' => 0,
			'Shop_display_bank' => 0,
			'Shop_display_bank_placement' => 0,
			'Shop_inventory_enable' => 0,
			'Shop_inventory_show_same_once' => 0,
			'Shop_inventory_items_num' => 5,
			'Shop_inventory_placement' => 0,
			'Shop_inventory_allow_hide' => 0,
			'Shop_settings_slots_losing' => 500,
			'Shop_settings_lucky2_losing' => 500,
			'Shop_settings_numberslots_losing' => 500,
			'Shop_settings_pairs_losing' => 500,
			'Shop_settings_dice_losing' => 500,
			'Shop_settings_slots_7' => 2000,
			'Shop_settings_slots_bell' => 150,
			'Shop_settings_slots_cherry' => 65,
			'Shop_settings_slots_lemon' => 20,
			'Shop_settings_slots_orange' => 75,
			'Shop_settings_slots_plum' => 50,
			'Shop_settings_slots_dollar' => 100,
			'Shop_settings_slots_melon' => 700,
			'Shop_settings_slots_grapes' => 400,
			'Shop_settings_lucky2_price' => 1000,
			'Shop_settings_number_losing' => 100,
			'Shop_settings_number_complete' => 700,
			'Shop_settings_number_firsttwo' => 450,
			'Shop_settings_number_secondtwo' => 250,
			'Shop_settings_number_firstlast' => 100,
			'Shop_settings_pairs_clubs_1' => 2000,
			'Shop_settings_pairs_clubs_2' => 2000,
			'Shop_settings_pairs_clubs_3' => 2000,
			'Shop_settings_pairs_clubs_4' => 2000,
			'Shop_settings_pairs_clubs_5' => 2000,
			'Shop_settings_pairs_clubs_6' => 2000,
			'Shop_settings_pairs_clubs_7' => 2000,
			'Shop_settings_pairs_clubs_8' => 2000,
			'Shop_settings_pairs_clubs_9' => 2000,
			'Shop_settings_pairs_clubs_10' => 2000,
			'Shop_settings_pairs_clubs_11' => 2000,
			'Shop_settings_pairs_clubs_12' => 2000,
			'Shop_settings_pairs_clubs_13' => 2000,
			'Shop_settings_pairs_diam_1' => 150,
			'Shop_settings_pairs_diam_2' => 150,
			'Shop_settings_pairs_diam_3' => 150,
			'Shop_settings_pairs_diam_4' => 150,
			'Shop_settings_pairs_diam_5' => 150,
			'Shop_settings_pairs_diam_6' => 150,
			'Shop_settings_pairs_diam_7' => 150,
			'Shop_settings_pairs_diam_8' => 150,
			'Shop_settings_pairs_diam_9' => 150,
			'Shop_settings_pairs_diam_10' => 150,
			'Shop_settings_pairs_diam_11' => 150,
			'Shop_settings_pairs_diam_12' => 150,
			'Shop_settings_pairs_diam_13' => 150,
			'Shop_settings_pairs_hearts_1' => 50,
			'Shop_settings_pairs_hearts_2' => 50,
			'Shop_settings_pairs_hearts_3' => 50,
			'Shop_settings_pairs_hearts_4' => 50,
			'Shop_settings_pairs_hearts_5' => 50,
			'Shop_settings_pairs_hearts_6' => 50,
			'Shop_settings_pairs_hearts_7' => 50,
			'Shop_settings_pairs_hearts_8' => 50,
			'Shop_settings_pairs_hearts_9' => 50,
			'Shop_settings_pairs_hearts_10' => 50,
			'Shop_settings_pairs_hearts_11' => 50,
			'Shop_settings_pairs_hearts_12' => 50,
			'Shop_settings_pairs_hearts_13' => 50,
			'Shop_settings_pairs_spades_1' => 200,
			'Shop_settings_pairs_spades_2' => 200,
			'Shop_settings_pairs_spades_3' => 200,
			'Shop_settings_pairs_spades_4' => 200,
			'Shop_settings_pairs_spades_5' => 200,
			'Shop_settings_pairs_spades_6' => 200,
			'Shop_settings_pairs_spades_7' => 200,
			'Shop_settings_pairs_spades_8' => 200,
			'Shop_settings_pairs_spades_9' => 200,
			'Shop_settings_pairs_spades_10' => 200,
			'Shop_settings_pairs_spades_11' => 200,
			'Shop_settings_pairs_spades_12' => 200,
			'Shop_settings_pairs_spades_13' => 200,
			'Shop_settings_dice_1' => 150,
			'Shop_settings_dice_2' => 550,
			'Shop_settings_dice_3' => 750,
			'Shop_settings_dice_4' => 900,
			'Shop_settings_dice_5' => 1500,
			'Shop_settings_dice_6' => 2000,
			'Shop_noty_trade' => 0,
			'Shop_noty_credits' => 0,
			'Shop_noty_items' => 0,
			'Shop_importer_success' => 0,
			'Shop_integration_arcade_score' => 0,
		];
		$modSettings = array_merge($defaults, $modSettings);
	}

	/**
	 * Shop::defineHooks()
	 *
	 * Load hooks quietly
	 * @return void
	 */
	public static function defineHooks()
	{
		$hooks = [
			'autoload' => 'autoload',
			'menu_buttons' => 'hookButtons',
			'actions' => 'hookActions',
		];
		foreach ($hooks as $point => $callable)
			add_integration_function('integrate_' . $point, __CLASS__ . '::' . $callable, false);
	}

	/**
	 * Shop::addonHooks()
	 * 
	 * Load hooks from addons/mods
	 * 
	 * @return void
	 */
	public static function addonHooks()
	{
		global $sourcedir;

		// List of addons/mods that we are integrating
		$addons = array_diff(scandir($sourcedir . self::$addonsdir, 1), ['..', '.', 'index.php', 'Addons.php']);

		// Class
		$class = __NAMESPACE__ . '\Integration\Addons\\';

		// Sort
		sort($addons);

		// Load the hooks for these addons
		foreach ($addons as $addon)
		{
			if (is_callable($class . $addon . '\\'. $addon, 'integration'))
				add_integration_function('integrate_pre_load_theme', $class . $addon . '\\'. $addon .'::integration', false);
		}
	}

	/**
	 * Shop::autoload()
	 *
	 * @param array $classMap
	 * @return void
	 */
	public static function autoload(&$classMap)
	{
		$classMap['Shop\\'] = 'Shop/';
	}

	/**
	 * Shop::hookActions()
	 *
	 * Insert the actions needed by this mod
	 * @param array $actions An array containing all possible SMF actions. This includes loading different hooks for certain areas.
	 * @return void
	 */
	public static function hookActions(&$actions)
	{
		// The main action
		$actions['shop'] = ['Shop/View/Home.php', __NAMESPACE__  . '\View\Home::main#'];

		// Feed
		$actions['shopfeed'] = [false, __CLASS__ . '::getFeed'];

		// Need to be somewhere
		if (!isset($_REQUEST['action']) || empty($_REQUEST['action']))
			return;

		// Add some hooks by action
		switch ($_REQUEST['action'])
		{
			// I can simple load the language file, but...
			// I'll load this hook just to flex on using yet another hook
			case 'helpadmin':
				add_integration_function('integrate_helpadmin', __NAMESPACE__ . '\Integration\Permissions::language', false);
				break;
			// Shop Admin
			case 'admin':
				add_integration_function('integrate_admin_areas', __NAMESPACE__ . '\Manage\Dashboard::hookAreas#', false);
				break;
			// Give points/credits on posting
			case 'post':
			case 'post2':
				add_integration_function('integrate_after_create_post', __NAMESPACE__ . '\Integration\Posting::after_create_post#', false);
				break;
			// Who actions
			case 'who':
				add_integration_function('who_allowed', __NAMESPACE__ . '\Integration\Who::who_allowed#', false);
				add_integration_function('whos_online_after', __NAMESPACE__ . '\Integration\Who::whos_online_after#', false);
				break;
			// Profile
			case 'profile':
				add_integration_function('integrate_pre_profile_areas', __NAMESPACE__ . '\Integration\Profile::hookAreas#', false);
				break;
			// Register
			case 'signup':
			case 'signup2':
				add_integration_function('integrate_register', __NAMESPACE__ . '\Integration\Signup::register', false);
				break;
			// Likes
			case 'likes':
				add_integration_function('integrate_issue_like_before', __NAMESPACE__ . '\Integration\Likes::likePost#', false);
				break;
		}
	}

	/**
	 * Shop::userHooks()
	 *
	 * Load member and custom fields hooks
	 * @return void
	 */
	public static function userHooks()
	{
		// Load user and alerts hooks
		$hooks = [
			'load_member_data',
			'user_info',
			'simple_actions',
			'member_context',
			'fetch_alerts',
		];
		foreach ($hooks as $hook)
			add_integration_function('integrate_' . $hook, __NAMESPACE__ . '\Integration\User::' . $hook.'#', false);
	}

	/**
	 * Shop::hookButtons()
	 *
	 * Insert a Shop button on the menu buttons array
	 * @param array $buttons An array containing all possible tabs for the main menu.
	 * @return void
	 */
	public static function hookButtons(&$buttons)
	{
		global $scripturl, $modSettings;

		// Languages
		loadLanguage(__NAMESPACE__ . '/Shop');

		$before = 'mlist';
		$temp_buttons = [];
		foreach ($buttons as $k => $v) {
			if ($k == $before) {
				$temp_buttons['shop'] = [
					'title' => self::getText('main_button'),
					'href' => $scripturl . '?action=shop',
					'icon' => 'icons/shop.png',
					'show' => (allowedTo('shop_canAccess') || allowedTo('shop_canManage')) && !empty($modSettings['Shop_enable_shop']),
					'sub_buttons' => [
						'shopadmin' => [
							'title' => self::getText('admin_button'),
							'href' => $scripturl . '?action=admin;area=shopinfo',
							'show' => allowedTo('shop_canManage'),
						],
					],
				];
			}
			$temp_buttons[$k] = $v;
		}
		$buttons = $temp_buttons;

		// Add the prefix permission to the admin button
		$buttons['admin']['show'] = $buttons['admin']['show']  || allowedTo('shop_canManage');
	}

	/**
	 * Shop::getText()
	 *
	 * Gets a string key, and returns the associated text string.
	 */
	public static function getText($text, $pattern = true)
	{
		global $txt;

		return !empty($pattern) ? (!empty($txt[__NAMESPACE__ . '_' . $text]) ? $txt[__NAMESPACE__ . '_' . $text] : '') : (!empty($txt[$text]) ? $txt[$text] : '');
	}

	/**
	 * Shop::getFeed()
	 *
	 * Proxy function to avoid Cross-origin errors.
	 * @return string
	 * @author Jessica González <suki@missallsunday.com>
	 */
	public static function getFeed()
	{
		global $sourcedir;

		require_once($sourcedir . '/Class-CurlFetchWeb.php');
		$fetch = new \curl_fetch_web_data();
		$fetch->get_url_data(self::$supportSite);
		if ($fetch->result('code') == 200 && !$fetch->result('error'))
			$data = $fetch->result('body');
		else
			exit;
		smf_serverResponse($data, 'Content-type: text/xml');
	}
}