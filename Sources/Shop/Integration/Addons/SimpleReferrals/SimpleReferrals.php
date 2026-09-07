<?php

/**
 * Simple Referrals
 * 
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration\Addons\SimpleReferrals;

use Shop\Shop;
use Shop\Integration\Addons\Addons;
use Shop\Helper\Database;

if (!defined('SMF'))
	die('No direct access...');

class SimpleReferrals implements Addons
{
	/**
	 * @var string The link to this integration or mod
	 */
	private static $_link = 'https://custom.simplemachines.org/index.php?mod=4294';

	/**
	 * @var bool Check if we want to load the language file in a specific page.
	 */
	private static $_language = false;

	/**
	 * Addons::integration()
	 *
	 * Loads the essentials of the integration for this addon
	 */
	public static function integration(&$theme)
	{
		// Language
		self::language();

		// Hooks
		self::defineHooks();
	}

	/**
	 * SimpleReferrals::defineHooks()
	 *
	 * Loads the hooks and languages for this addon
	 */
	public static function defineHooks()
	{

		// Add some hooks by action
		if (isset($_REQUEST['action']))
		{
			switch ($_REQUEST['action'])
			{
				case 'admin': 
					if (isset($_REQUEST['area']) && $_REQUEST['area'] == 'shopsettings')
						add_integration_function('integrate_shop_addons_settings', __CLASS__ . '::settings', false);
					break;
				case 'signup2':
					add_integration_function('integrate_register_after', __CLASS__ . '::referral', false);
					break;
			}
		}
	}

	/**
	 * SimpleReferrals::language()
	 *
	 * Loads the hooks and languages for this addon
	 */
	public static function language()
	{
		// Actions
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'admin' && isset($_REQUEST['area']) && $_REQUEST['area'] == 'shopsettings')
			self::$_language = true;

		// Load it when necessary only
		if (!empty(self::$_language))
			loadLanguage('Shop/SimpleReferrals/');
	}

	/**
	 * SimpleReferrals::settings()
	 *
	 * Loads the hooks and languages for this addon
	 * 
	 * Use array_merge to add your settings into the array
	 */
	public static function settings(&$settings)
	{
		$settings[] = ['title', 'Shop_integration_simple_referrals'];
		$settings[] = ['desc', 'Shop_integration_simple_referrals_desc', 'label' => sprintf(Shop::getText('integration_settings_desc'), self::$_link, Shop::getText('integration_simple_referrals'))];
		$settings[] = ['int', 'Shop_integration_simple_referrals_setting', 'subtext' => Shop::getText('integration_simple_referrals_setting_desc')];
	}

	/**
	 * SimpleReferrals::referral()
	 *
	 * Gives credits to the referral for a new member referred to the forum
	 * 
	 */
	public static function referral($regOptions)
	{
		global $modSettings;

		// Give credits for referring a new user to the forum
		if (!empty($modSettings['Shop_integration_simple_referrals_setting']) && !empty($regOptions['register_vars']['referral']))
		{
			// Update the credits
			Database::Update('members', ['user' => $regOptions['register_vars']['referral'], 'credits' => $modSettings['Shop_integration_simple_referrals_setting']], 'shopMoney = shopMoney + {int:credits}', 'WHERE id_member = {int:user}');
		}
	}
}