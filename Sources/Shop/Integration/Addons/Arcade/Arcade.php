<?php

/**
 * SMF Arcade
 * 
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration\Addons\Arcade;

use Shop\Shop;
use Shop\Integration\Addons\Addons;
use Shop\Helper\Database;
use Shop\Helper\Log;

if (!defined('SMF'))
	die('No direct access...');

class Arcade implements Addons
{
	/**
	 * @var string The link to this integration or mod
	 */
	private static $_link = 'https://web-develop.ca/index.php?action=downloads;area=stable_smf_arcade';

	/**
	 * @var bool Check if we want to load the language file in a specific page.
	 */
	private static $_language = false;

	/**
	 * @var int Store the total credits the user will get
	 */
	private static $_credits = 0;

	/**
	 * @var object Log the user history playing at the arcade.
	 */
	private static $_log;

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

		// Log the credits the user gets
		// self::$_log = new Log;
	}

	/**
	 * Arcade::defineHooks()
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
				case 'arcade':
					add_integration_function('integrate_arcade_score', __CLASS__ . '::score', false);
					break;
			}
		}
	}

	/**
	 * Arcade::language()
	 *
	 * Loads the hooks and languages for this addon
	 */
	public static function language()
	{
		// Actions
		if (isset($_REQUEST['action']))
			switch ($_REQUEST['action'])
			{
				case 'admin':
					if (isset($_REQUEST['area']) && $_REQUEST['area'] == 'shopsettings')
						self::$_language = true;
					break;
				case 'arcade':
					self::$_language = true;
					break;
			}

		// Load it when necessary only
		if (!empty(self::$_language))
			loadLanguage('Shop/Arcade/');
	}

	/**
	 * Arcade::settings()
	 *
	 * Loads the hooks and languages for this addon
	 * 
	 * Use array_merge to add your settings into the array
	 */
	public static function settings(&$settings)
	{
		$settings[] = ['title', 'Shop_integration_arcade'];
		$settings[] = ['desc', 'Shop_integration_arcade_desc', 'label' => sprintf(Shop::getText('integration_settings_desc'), self::$_link, Shop::getText('integration_arcade'))];
		$settings[] = ['int', 'Shop_integration_arcade_score', 'subtext' => Shop::getText('integration_arcade_score_desc')];
		$settings[] = ['int', 'Shop_integration_arcade_personal_best', 'subtext' => Shop::getText('integration_arcade_personal_best_desc')];
		$settings[] = ['int', 'Shop_integration_arcade_new_champion', 'subtext' => Shop::getText('integration_arcade_new_champion_desc')];
	}

	/**
	 * Arcade::score()
	 *
	 * Gives credits for saving a score or for breaking a record
	 * 
	 */
	public static function score($game, $member, $score)
	{
		global $modSettings;

		// We got a score??
		if (!empty($score))
		{
			// Just submitting a score? lame...
			if (!empty($modSettings['Shop_integration_arcade_score']))
				self::$_credits += $modSettings['Shop_integration_arcade_score'];

			// New Personal Best!
			if (!empty($_SESSION['arcade']['highscore']['personalBest']) && !empty($modSettings['Shop_integration_arcade_personal_best']))
				self::$_credits += $modSettings['Shop_integration_arcade_personal_best'];

			// New Champion!!
			if (!empty($_SESSION['arcade']['highscore']['newChampion']) && !empty($modSettings['Shop_integration_arcade_new_champion']))
				self::$_credits += $modSettings['Shop_integration_arcade_new_champion'];

			// Update in a single query if there's any stuff to update
			if (!empty(self::$_credits))
				Database::Update('members', ['user' => $member['id'], 'credits' => self::$_credits], 'shopMoney = shopMoney + {int:credits}', 'WHERE id_member = {int:user}');
		}
	}
}