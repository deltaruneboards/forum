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

class Images
{
	/**
	 * Images::list()
	 *
	 * It provides the list of images that can be used for items and categories
	 * @return array The list of images
	 */
	public static function list()
	{
		global $boarddir;

		// Start with an empty array
		$imageList = [];
		// Try to open the images directory
		
		if ($handle = opendir($boarddir. Shop::$itemsdir)) {
			// For each file in the directory...
			while (false !== ($file = readdir($handle))) {
				// ...if it's a valid file, add it to the list
				if (!in_array($file, ['.', '..', 'blank.gif']))
					$imageList[] = $file;
			}
			// Sort the list
			sort($imageList);

			return $imageList;
		}
		// Otherwise, if directory inaccessible, show an error
		else
			fatal_error(Shop::getText('cannot_open_images'));
	}
}