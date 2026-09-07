<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Helper;

use Shop\Shop;

if (!defined('SMF'))
	die('No direct access...');

class Format
{
	/**
	 * @var string Sets the url for the item images
	 */
	private static $items_url;

	/**
	 * Format::cash()
	 *
	 * It gives the money a format, adding the suffix and prefix set in the admin (if)
	 * @param int $money A string that usually includes the amount of cash/credits
	 * @param bool $formal It will put the suffix at the beginning if enabled
	 * @param bool|int $placement Will fix the format for the title placement in the posts only
	 * @return string A text containing the specified money/string with format
	 */
	public static function cash($money, $formal = false, $placement = true)
	{
		global $modSettings;


		if (empty($formal))
			$disp = (!empty($modSettings['Shop_credits_prefix']) ? $modSettings['Shop_credits_prefix'] : '') . (!is_numeric($money) ? $money : comma_format($money)) . ' ' . (!empty($modSettings['Shop_credits_suffix'] && !empty($placement)) ? $modSettings['Shop_credits_suffix'] : '');
		else
			$disp = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : '') . ': ' . (!empty($modSettings['Shop_credits_prefix']) ? $modSettings['Shop_credits_prefix'] : '') . (!is_numeric($money) ? $money : comma_format($money));

		return $disp;
	}

	/**
	 * Format::item()
	 *
	 * Gives the provided item format with its image
	 * @param string $image The image of an item
	 * @param string $description Optional parameter for including the description in the title/alt
	 * @param string $class Optional parameter for including class and id
	 * @return string A formatted image
	 */
	public static function image($image, $description = '', $class = '')
	{
		global $modSettings, $boardurl;

		// Item images...
		self::$items_url = $boardurl . Shop::$itemsdir;

		$formatname = '<img' . (!empty($class) ? ' class="' . $class . '" id="' . $class . '"' : ''). ' src="' . self::$items_url . $image . '" alt="' . $description . '" title="' . $description . '" style="vertical-align: middle;width:' . $modSettings['Shop_images_width'] . ';height:' . $modSettings['Shop_images_height'] . ';" />';

		return $formatname;
	}

	/**
	 * Format::gamespass()
	 *
	 * Provides a format to the dyas left of gamespass access
	 * @param int $days The days the user has on his subscription to the gamespass
	 * @return string Days left of gamespass
	 */
	public static function gamespass($days)
	{
		return round((($days - time()) / 86400));
	}
}