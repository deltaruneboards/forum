<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration\Addons;

if (!defined('SMF'))
	die('No direct access...');

interface Addons
{
	/**
	 * Addons::integration()
	 *
	 * Loads the essentials of the integration for this addon
	 */
	public static function integration(&$theme);

	/**
	 * Addons::defineHooks()
	 *
	 * Loads the hooks for this addon
	 */
	public static function defineHooks();

	/**
	 * Addons::language()
	 *
	 * Loads the languages for this addon
	 */
	public static function language();
}