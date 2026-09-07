<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration;

use Shop\Shop;
use Shop\Helper\Format;
use Shop\View\Inventory;

if (!defined('SMF'))
	die('No direct access...');

class Profile
{
	/**
	 * @var object We will create an object for the specified item if needed.
	 */
	private $_inventory;

	/**
	 * Profile::__construct()
	 *
	 * Need to load some stuff first
	 */
	function __construct()
	{
		// Create new instance of inventory
		$this->_inventory = new Inventory;

		// Load template only on topics
		loadTemplate('Shop/Inventory');

		// Load language just in case
		loadLanguage('Shop/Shop');
	}

	 /**
	 * Profile::hookAreas()
	 *
	 * Adding some more links to the profile menu
	 * 
	 * @param array $profile_areas An array with all the profile areas
	 * @return void
	 */
	public function hookAreas(&$profile_areas)
	{
		global $context, $scripturl, $modSettings;

		// Profile information
		$before = 'statistics';
		$temp_buttons = [];
		foreach ($profile_areas['info']['areas'] as $k => $v) {
			if ($k == $before) {
				$temp_buttons['inventory'] = [
					'label' => Shop::getText('inventory_view'),
					'custom_url' => $scripturl . '?action=shop;sa=inventory',
					'file' => 'Shop/Shop-Inventory.php',
					'function' => 'ShopInventory::Main',
					'icon' => 'replies',
					'enabled' => !empty($modSettings['Shop_enable_shop']),
					'permission' => [
						'own' => ['shop_viewInventory', 'shop_canManage'],
						'any' => ['shop_viewInventory', 'shop_canManage'],
					],
				];
				$temp_buttons['mytrades'] = [
					'label' => Shop::getText('trade_mytrades'),
					'custom_url' => $scripturl . '?action=shop;sa=tradelist',
					'file' => 'Shop/Shop-Trade.php',
					'function' => 'ShopTrade::Profile',
					'icon' => 'inbox',
					'enabled' => !empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_enable_trade']),
					'permission' => [
						'own' => ['shop_viewInventory', 'shop_canTrade', 'shop_canManage'],
						'any' => ['shop_viewInventory', 'shop_canTrade', 'shop_canManage'],
					],
				];
			}
			$temp_buttons[$k] = $v;
		}
		$profile_areas['info']['areas'] = $temp_buttons;

		// Clean it
		unset($before);
		unset($temp_buttons);

		// Profile information
		$before = 'report';
		$temp_buttons = [];
		foreach ($profile_areas['profile_action']['areas'] as $k => $v) {
			if ($k == $before) {
				$temp_buttons['gift'] = [
					'label' => Shop::getText('main_gift'),
					'custom_url' => $scripturl . '?action=shop',
					'icon' => 'packages',
					'enabled' => !empty($modSettings['Shop_enable_shop']) && allowedTo('profile_view') && !empty($modSettings['Shop_enable_gift']),
					'subsections' => [
						'gift' => [Shop::getText('gift_send_item'), ['shop_canGift', 'shop_canManage']],
						'sendmoney' => [sprintf(Shop::getText('gift_send_money'), $modSettings['Shop_credits_suffix']), ['shop_canGift', 'shop_canManage']],
					],
					'permission' => [
						'own' => [],
						'any' => ['shop_canGift', 'shop_canManage'],
					],
				];
			}
			$temp_buttons[$k] = $v;
		}
		$profile_areas['profile_action']['areas'] = $temp_buttons;

		// Load more hooks yea
		$this->integrate();
	}

	 /**
	 * Profile::integrate()
	 *
	 * Load some more hooks for alerts and custom fields
	 * @return void
	 */
	public function integrate()
	{
		// More profile stuff
		add_integration_function('integrate_load_profile_fields', __CLASS__ . '::load_profile_fields', false);
		add_integration_function('integrate_setup_profile_context', __CLASS__ . '::setup_profile_context', false);
		add_integration_function('integrate_alert_types', __CLASS__ . '::alert_types', false);
		add_integration_function('integrate_load_custom_profile_fields', __CLASS__ . '::custom_profile_fields#', false);
	}

	 /**
	 * Profile::load_profile_fields()
	 *
	 * Adds shop account and profile options
	 * 
	 * @param array $profile_fields An array containing the profile fields and inputs
	 * @return void
	 */
	public static function load_profile_fields(&$profile_fields)
	{
		global $modSettings;

		// Profile information
		$before = 'posts';
		$temp_buttons = [];
		foreach ($profile_fields as $k => $v) {
			if ($k == $before) {
				$temp_buttons['shopMoney'] = [
					'type' => 'int',
					'label' => Shop::getText('posting_credits_pocket'),
					'log_change' => true,
					'size' => 10,
					'permission' => 'shop_canManage',
					'input_validate' => function(&$value) {
						if (!is_numeric($value))
							return 'digits_only';
						else
							$value = $value != '' ? strtr($value, [',' => '', '.' => '', ' ' => '']) : 0;
						return true;
					},
				];
				$temp_buttons['shopBank'] = [
					'type' => 'int',
					'label' => Shop::getText('posting_credits_bank'),
					'log_change' => true,
					'size' => 10,
					'enabled' => !empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_enable_bank']),
					'permission' => 'shop_canManage',
					'input_validate' => function(&$value) {
						if (!is_numeric($value))
							return 'digits_only';
						else
							$value = $value != '' ? strtr($value, [',' => '', '.' => '', ' ' => '']) : 0;
						return true;
					},
				];
				$temp_buttons['shopInventory_hide'] = [
					'type' => 'check',
					'label' => Shop::getText('inventory_hide'),
					'permission' => 'shop_viewInventory',
					'enabled' => !empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_inventory_allow_hide']) ,
				];
			}
			$temp_buttons[$k] = $v;
		}
		$profile_fields = $temp_buttons;
	}

	 /**
	 * Profile::setup_profile_context()
	 *
	 * Actually include our new shop fields into a specific section
	 * 
	 * @param array $fields An array containing the profile fields that are actually loaded
	 * @return void
	 */
	public static function setup_profile_context(&$fields)
	{
		global $modSettings;

		// Credits in pocket and bank
		if(isset($_REQUEST['area']) && $_REQUEST['area'] == 'account')
		{
			$before = 'posts';
			$temp_buttons = [];
			foreach ($fields as $k) {
				if ($k == $before) {
					array_push($temp_buttons, $before, 'hr', 'shopMoney', 'shopBank');
					continue;
				}
				array_push($temp_buttons, $k);
			}
			$fields = $temp_buttons;
		}

		// Hide inventory
		if(isset($_REQUEST['area']) && $_REQUEST['area'] == 'forumprofile' && !empty($modSettings['Shop_enable_shop']))
		{
			$before = 'personal_text';
			$temp_buttons = [];
			foreach ($fields as $k) {
				if ($k == $before) {
					array_push($temp_buttons, $before, 'hr', 'shopInventory_hide');
					continue;
				}
				array_push($temp_buttons, $k);
			}
			$fields = $temp_buttons;
		}
	}

	 /**
	 * Profile::custom_profile_fields()
	 *
	 * Shop custom profile fields for profile
	 * 
	 * @param int $memID The ID of a user previously loaded by {@link loadMemberData()}
	 * @param array $area An array containing the profile areas
	 * @return void
	 */
	public function custom_profile_fields($memID, $area)
	{
		global $context, $modSettings;

		// Pocket
		if (($area == 'summary') && (($modSettings['Shop_display_pocket'] == 2) || ($modSettings['Shop_display_pocket'] == 3)))
			$context['custom_fields']['shopMoney'] = [
				'name' => Shop::getText('posting_credits_pocket'),
				'colname' => 'Shop_pocket',
				'output_html' => Format::cash($context['member']['shopMoney']),
				'placement' => 0,
			];

		// Bank
		if (($area == 'summary') && (($modSettings['Shop_display_bank'] == 2) || ($modSettings['Shop_display_bank'] == 3)) && !empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_enable_bank']))
			$context['custom_fields']['shopBank'] = [
				'name' => Shop::getText('posting_credits_bank'),
				'colname' => 'Shop_bank',
				'output_html' => Format::cash($context['member']['shopBank']),
				'placement' => 0,
			];

		// Inventory
		if ($area == 'summary' && !empty($modSettings['Shop_inventory_enable']) && !empty($modSettings['Shop_enable_shop']) && empty($context['member']['shopInventory_hide']) && allowedTo('shop_viewInventory'))
			$context['custom_fields']['shop_inventory'] = [
				'name' => Shop::getText('posting_inventory'),
				'colname' => 'Shop_inventory',
				'output_html' => template_shop_inventory($this->_inventory->display($memID), $memID),
				'placement' => 2,
			];
	}

	 /**
	 * Profile::alert_types()
	 *
	 * Add our specific alerts to the profile so user can enable/disable them
	 * 
	 * @param array $alert_types An array containing the types or groups each alert belongs to
	 * @return void
	 */
	public static function alert_types(&$alert_types)
	{
		global $modSettings;

		// Credits
		if (!empty($modSettings['Shop_noty_credits']) && !empty($modSettings['Shop_enable_shop']))
			$alert_types['shop']['shop_usercredits'] = ['alert' => 'yes', 'email' => 'never'];
		// Gifts
		if (!empty($modSettings['Shop_noty_items']) && !empty($modSettings['Shop_enable_gift']) && !empty($modSettings['Shop_enable_shop']))
			$alert_types['shop']['shop_useritems'] = ['alert' => 'yes', 'email' => 'never'];
		// Trades
		if (!empty($modSettings['Shop_noty_trade']) && !empty($modSettings['Shop_enable_trade']) && !empty($modSettings['Shop_enable_shop']))
			$alert_types['shop']['shop_usertraded'] = ['alert' => 'yes', 'email' => 'never'];
	}
}