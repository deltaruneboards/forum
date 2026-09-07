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

class Settings extends Dashboard
{
	/**
	 * Settings::__construct()
	 *
	 * Create the array of subactions and load necessary extra language files
	 */
	function __construct()
	{
		// Array of sections
		$this->_subactions = [
			'general' => 'general',
			'credits' => 'credits',
			'integrations' => 'integrations',
			'permissions' => 'permissions',
			'profile' => 'profile',
			'notifications' => 'notifications',
		];
		$this->_sa = isset($_GET['sa'], $this->_subactions[$_GET['sa']]) ? $_GET['sa'] : 'general';
	}

	public function main()
	{
		global $context;

		// Create the tabs for the template.
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => Shop::getText('tab_settings'),
			'description' => Shop::getText('settings_general_desc'),
			'tabs' => [
				'general' => ['description' => Shop::getText('settings_general_desc')],
				'credits' => ['description' => Shop::getText('settings_credits_desc')],
				'integrations' => ['description' => Shop::getText('settings_integrations_desc')],
				'permissions' => ['description' => Shop::getText('settings_permissions_desc')],
				'profile' => ['description' => Shop::getText('settings_profile_desc')],
				'notifications' => ['description' => Shop::getText('settings_notifications_desc')],
			],
		];
		call_helper(__CLASS__ . '::' . $this->_subactions[$this->_sa].'#');
	}

	public function general($return_config = false)
	{
		global $context, $sourcedir, $modSettings;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings'). ' - ' . Shop::getText('settings_general');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];
		$config_vars = [
			['check', 'Shop_enable_shop', 'subtext' => Shop::getText('enable_shop_desc')],
		];
		
		// Shop enabled? Show more options
		if (!empty($modSettings['Shop_enable_shop']))
		{
			$config_vars[] = ['check', 'Shop_enable_games'];
			$config_vars[] = ['check', 'Shop_enable_bank'];
			$config_vars[] = ['check', 'Shop_enable_gift'];
			$config_vars[] = ['check', 'Shop_enable_trade'];
			$config_vars[] = ['check', 'Shop_enable_stats'];
			$config_vars[] = '';
			$config_vars[] = ['check', 'Shop_enable_maintenance', 'subtext' => Shop::getText('enable_maintenance_desc')];
		}

		Database::Save($config_vars, $return_config, 'general');
	}

	public function credits($return_config = false)
	{
		global $context, $sourcedir, $modSettings;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings'). ' - ' . Shop::getText('settings_credits');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		// Credits settings
		$config_vars = [
			['text', 'Shop_credits_prefix', 'subtext' => Shop::getText('credits_prefix_desc')],
			['text', 'Shop_credits_suffix', 'subtext' => Shop::getText('credits_suffix_desc')],
			'',
			['int', 'Shop_credits_register'],
			['int', 'Shop_credits_topic'],
			['int', 'Shop_credits_post'],
			['int', 'Shop_credits_likes_post'],
			'',
			['int', 'Shop_credits_word'],
			['int', 'Shop_credits_character'],
			['int', 'Shop_credits_limit', 'subtext' => Shop::getText('credits_limit_desc')],
		];

		// Shop enabled? Show more options
		if (!empty($modSettings['Shop_enable_shop']))
		{
			$config_vars[] = ['title', 'Shop_bank_settings', 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['float', 'Shop_bank_interest', 'subtext' => Shop::getText('bank_interest_desc'), 'min' => '', 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['check', 'Shop_bank_interest_yesterday', 'subtext' => Shop::getText('bank_interest_yesterday_desc'), 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['int', 'Shop_bank_withdrawal_fee', 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['int', 'Shop_bank_deposit_fee', 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['int', 'Shop_bank_withdrawal_min', 'subtext' => Shop::getText('bank_max_min_desc'), 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['int', 'Shop_bank_withdrawal_max', 'subtext' => Shop::getText('bank_max_min_desc'), 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['int', 'Shop_bank_deposit_min', 'subtext' => Shop::getText('bank_max_min_desc'), 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['int', 'Shop_bank_deposit_max', 'subtext' => Shop::getText('bank_max_min_desc'), 'disabled' => empty($modSettings['Shop_enable_bank'])];
			$config_vars[] = ['title', 'Shop_credits_general_settings'];
			$config_vars[] = ['float', 'Shop_items_trade_fee', 'subtext' => Shop::getText('items_trade_fee_desc'), 'disabled' => empty($modSettings['Shop_enable_stats'])];
			$config_vars[] = ['text', 'Shop_images_width'];
			$config_vars[] = ['text', 'Shop_images_height'];
			$config_vars[] = ['int', 'Shop_items_perpage', 'subtext' => Shop::getText('items_perpage_desc')];
		}

		Database::Save($config_vars, $return_config, 'credits');
	}

	public function integrations($return_config = false)
	{
		global $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings'). ' - ' . Shop::getText('settings_integrations');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];
		$config_vars = [];

		// Integrate settings from/for Addons
		call_integration_hook('integrate_shop_addons_settings', [&$config_vars]);

		Database::Save($config_vars, $return_config, 'integrations');
	}

	public function permissions($return_config = false)
	{
		global $context, $sourcedir, $modSettings;

		// Shop disabled? Go away!
		if (empty($modSettings['Shop_enable_shop']))
			redirectexit('action=admin;area=shopsettings');

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings'). ' - ' . Shop::getText('settings_permissions');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		// Shop do not play nice with guests. Permissions are already hidden for them, let's exterminate any hint of them in this section.
		$context['permissions_excluded'] = [-1];

		$config_vars = [
			['permissions', 'shop_canAccess', 'subtext' => Shop::getText('permissionhelp_shop_canAccess', false)],
			['permissions', 'shop_canBuy', 'subtext' => Shop::getText('permissionhelp_shop_canBuy', false)],
			['permissions', 'shop_viewInventory', 'subtext' => Shop::getText('permissionhelp_shop_viewInventory', false)],
			['permissions', 'shop_canGift', 'subtext' => Shop::getText('permissionhelp_shop_canGift', false), 'disabled' => empty($modSettings['Shop_enable_gift'])],
			['permissions', 'shop_canTrade', 'subtext' => Shop::getText('permissionhelp_shop_canTrade', false), 'disabled' => empty($modSettings['Shop_enable_trade'])],
			['permissions', 'shop_canBank', 'subtext' => Shop::getText('permissionhelp_shop_canBank', false), 'disabled' => empty($modSettings['Shop_enable_bank'])],
			['permissions', 'shop_viewStats', 'subtext' => Shop::getText('permissionhelp_shop_viewStats', false), 'disabled' => empty($modSettings['Shop_enable_stats'])],
			['permissions', 'shop_playGames', 'subtext' => Shop::getText('permissionhelp_shop_playGames', false), 'disabled' => empty($modSettings['Shop_enable_games'])],
			'',
			['permissions', 'shop_canManage', 'subtext' => Shop::getText('permissionhelp_shop_canManage', false)],
		];

		Database::Save($config_vars, $return_config, 'permissions');
	}

	public function profile($return_config = false)
	{
		global $context, $sourcedir, $modSettings;

		require_once($sourcedir . '/ManageServer.php');
		loadLanguage('ManageSettings');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings'). ' - ' . Shop::getText('settings_profile');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];

		$config_vars = [
			['select', 'Shop_display_pocket',
				[
					Shop::getText('items_none_select'),
					Shop::getText('display_post'),
					Shop::getText('display_profile'),
					Shop::getText('display_both')
				],
			],
			['select', 'Shop_display_pocket_placement',
				[
					Shop::getText('custom_profile_placement_standard', false),
					Shop::getText('custom_profile_placement_icons', false),
					Shop::getText('custom_profile_placement_above_signature', false),
					Shop::getText('custom_profile_placement_below_signature', false),
					Shop::getText('custom_profile_placement_below_avatar', false),
					Shop::getText('custom_profile_placement_above_member', false),
					Shop::getText('custom_profile_placement_bottom_poster', false),
				],
			],
			'',
		];

		// Shop enabled? Show more options
		if (!empty($modSettings['Shop_enable_shop']))
		{
			$config_vars[] = ['select', 'Shop_display_bank',
				[
					Shop::getText('items_none_select'),
					Shop::getText('display_post'),
					Shop::getText('display_profile'),
					Shop::getText('display_both')
				],
			];
			$config_vars[] = ['select', 'Shop_display_bank_placement',
				[
					Shop::getText('custom_profile_placement_standard', false),
					Shop::getText('custom_profile_placement_icons', false),
					Shop::getText('custom_profile_placement_above_signature', false),
					Shop::getText('custom_profile_placement_below_signature', false),
					Shop::getText('custom_profile_placement_below_avatar', false),
					Shop::getText('custom_profile_placement_above_member', false),
					Shop::getText('custom_profile_placement_bottom_poster', false),
				],
			];
			$config_vars[] = '';
			$config_vars[] = ['select', 'Shop_inventory_enable',
				[
					Shop::getText('items_none_select'),
					Shop::getText('display_post'),
					Shop::getText('display_profile'),
					Shop::getText('display_both')
				],
			];
			$config_vars[] = ['check', 'Shop_inventory_show_same_once'];
			$config_vars[] = ['int', 'Shop_inventory_items_num'];
			$config_vars[] = ['select', 'Shop_inventory_placement',
				[
					Shop::getText('custom_profile_placement_standard', false),
					Shop::getText('custom_profile_placement_icons', false),
					Shop::getText('custom_profile_placement_above_signature', false),
					Shop::getText('custom_profile_placement_below_signature', false),
					Shop::getText('custom_profile_placement_below_avatar', false),
					Shop::getText('custom_profile_placement_above_member', false),
					Shop::getText('custom_profile_placement_bottom_poster', false),
				],
			];
			$config_vars[] = ['check', 'Shop_inventory_allow_hide', 'subtext' => Shop::getText('inventory_allow_hide_desc')];
		}

		Database::Save($config_vars, $return_config, 'profile');
	}

	public function notifications($return_config = false)
	{
		global $context, $sourcedir, $modSettings;

		require_once($sourcedir . '/ManageServer.php');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Shop::getText('tab_settings'). ' - ' . Shop::getText('settings_notifications');
		$context[$context['admin_menu_name']]['tab_data']['title'] = $context['page_title'];
		$config_vars = [
			['check', 'Shop_noty_credits', 'subtext' => Shop::getText('noty_credits_desc')],
			'',
		];
		
		// Shop enabled? Show more options
		if (!empty($modSettings['Shop_enable_shop']))
		{
			$config_vars[] = ['check', 'Shop_noty_items', 'subtext' => Shop::getText('noty_items_desc')];
			$config_vars[] = ['check', 'Shop_noty_trade', 'subtext' => Shop::getText('noty_trade_desc')];
		}

		Database::Save($config_vars, $return_config, 'notifications');
	}
}