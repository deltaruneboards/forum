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

class Delete
{
	/**
	 * Delete::item()
	 *
	 * It wipes multiple items from existence
	 */
	public static function items($items, $redirect = NULL)
	{
		// If nothing was chosen to delete (shouldn't happen, but meh)
		if (empty($items))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Make sure all IDs are numeric
		foreach ($items as $key => $value)
			$items[$key] = (int) $value;

		// Delete all the items
		Database::Delete('stshop_items', 'itemid', $items);

		// If anyone owned this item, they don't anymore :P
		Database::Delete('stshop_inventory', 'itemid', $items);

		// Clean gift log
		Database::Delete('stshop_log_gift', 'itemid', $items);

		// Clean buy log
		Database::Delete('stshop_log_buy', 'itemid', $items);

		// Redirect the user
		if ($redirect != NULL)
			redirectexit($redirect);
	}

	/**
	 * Delete::modules()
	 *
	 * Removes modules completely
	 */
	public static function modules($modules, $redirect = NULL)
	{
		global $sourcedir;

		// If nothing was chosen to delete (shouldn't happen, but meh)
		if (empty($modules))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Make sure all IDs are numeric
		foreach ($modules as $key => $value)
			$modules[$key] = (int) $value;

		// Update the items
		Database::Update(
			'stshop_items',
			['delete' => $modules], '
			module = 0,
			input_needed = 0,
			can_use_item = 0,
			delete_after_use = 0,',
			'WHERE module IN ({array_int:delete})'
		);

		// Delete the modules from the database
		Database::Delete('stshop_modules', 'id', $modules);

		// Delete files from directory
		foreach ($_REQUEST['files'] as $key => $file) 
			unlink($sourcedir . Shop::$modulesdir . basename($file. '.php'));

		// Redirect the user
		if ($redirect != NULL)
			redirectexit($redirect);
	}

	/**
	 * Delete::cats()
	 *
	 * It kills kittens (allegedly)
	 */
	public static function cats($cats, $redirect = NULL, $items)
	{
		global $sourcedir;

		// If nothing was chosen to delete (shouldn't happen, but meh)
		if (empty($cats))
			fatal_error(Shop::getText('item_delete_error'), false);

		// Make sure all IDs are numeric
		foreach ($cats as $key => $value)
			$cats[$key] = (int) $value;

		// Make sure all IDs are numeric
		if (!empty($items))
		{
			foreach ($items as $key => $value)
				$items[$key] = (int) $value['itemid'];

			// Delete those items from the database
			self::items($items);
		}

		// Update the items
		Database::Update(
			'stshop_items',
			['delete' => $cats], '
			catid = 0,',
			'WHERE catid IN ({array_int:delete})'
		);

		// Delete the modules from the database
		Database::Delete('stshop_categories', 'catid', $cats);

		// Redirect the user
		if ($redirect != NULL)
			redirectexit($redirect);
	}
}