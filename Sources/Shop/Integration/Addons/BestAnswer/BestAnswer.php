<?php

/**
 * SMF Best Answer
 * 
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration\Addons\BestAnswer;

use Shop\Shop;
use Shop\Integration\Addons\Addons;
use Shop\Helper\Database;

if (!defined('SMF'))
	die('No direct access...');

class BestAnswer implements Addons
{
	/**
	 * @var string The link to this integration or mod
	 */
	private static $_link = 'https://custom.simplemachines.org/mods/index.php?mod=4274';

	/**
	 * @var bool Check if we want to load the language file in a specific page.
	 */
	private static $_language = false;

	/**
	 * @var array Log data to prevent duplicate content
	 */
	private static $_content;

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
	 * BestAnswer::defineHooks()
	 *
	 * Loads the hooks and languages for this addon
	 */
	public static function defineHooks()
	{
		global $topic;

		// Settings
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'admin' && isset($_REQUEST['area']) && $_REQUEST['area'] == 'shopsettings')
			add_integration_function('integrate_shop_addons_settings', __CLASS__ . '::settings', false);

		// Best Answer hooks
		if (!empty($topic) && !isset($_REQUEST['action']))
			add_integration_function('integrate_sycho_best_answer', __CLASS__ . '::mark_best_answer', false);
	}

	/**
	 * BestAnswer::language()
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
			loadLanguage('Shop/BestAnswer/');
	}

	/**
	 * BestAnswer::settings()
	 *
	 * Loads the hooks and languages for this addon
	 * 
	 * Use array_merge to add your settings into the array
	 */
	public static function settings(&$settings)
	{
		$settings[] = ['title', 'Shop_integration_sycho_best_answer'];
		$settings[] = ['desc', 'Shop_integration_sycho_best_answer_desc', 'label' => sprintf(Shop::getText('integration_settings_desc'), self::$_link, Shop::getText('integration_sycho_best_answer'))];
		$settings[] = ['int', 'Shop_integration_sycho_best_answer_setting', 'subtext' => Shop::getText('integration_sycho_best_answer_setting_desc')];
	}

	/**
	 * BestAnswer::mark_best_answer()
	 *
	 * Gives credits to the best answer
	 * 
	 */
	public static function mark_best_answer($id_msg, $id_user)
	{
		global $modSettings, $user_info;

		// Give credits for getting the best answer, but only if you're not marking it yourself
		if (!empty($modSettings['Shop_integration_sycho_best_answer_setting']) && $user_info['id'] != $id_user && !empty($id_user))
		{
			// Check if we performed this action already
			self::$_content = Database::Get('', '', '', 'stshop_log_content', ['id_member', 'id_msg', 'content'], 'WHERE id_msg = {int:id_msg} AND content = {string:content} AND id_member = {int:member}', true, '', ['id_msg' => $id_msg, 'content' => 'bestanswer', 'member' => $id_user]);

			// Do we send credits??
			if (empty(self::$_content))
			{
				// Update the credits for best answer
				Database::Update('members', ['user' => $id_user, 'credits' => $modSettings['Shop_integration_sycho_best_answer_setting']], 'shopMoney = shopMoney + {int:credits}', 'WHERE id_member = {int:user}');

				// Log the entry
				Database::Insert('stshop_log_content', ['id_msg'=> $id_msg, 'id_member'=> $id_user, 'content' => 'bestanswer'], ['id_msg' => 'int', 'id_member' => 'int', 'content' => 'string']) ;
			}
		}
	}
}