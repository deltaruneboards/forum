<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration;

if (!defined('SMF'))
	die('No direct access...');

class Permissions
{
	var $shop_permissions;

	/**
	 * Permissions::__construct()
	 *
	 * Insert our permissions in the array
	 */
	function __construct()
	{
		$this->shop_permissions = [
			'shop_canAccess',
			'shop_canBuy',
			'shop_viewInventory',
			'shop_canGift',
			'shop_canTrade',
			'shop_canBank',
			'shop_viewStats',
			'shop_playGames',
			'shop_canManage'
		];
	}

	/**
	 * Permissions::load_permissions()
	 *
	 * ST Shop permissions
	 * @param array $permissionGroups An array containing all possible permissions groups.
	 * @param array $permissionList An associative array with all the possible permissions.
	 * @return void
	 */
	public function load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
	{
		global $modSettings;

		// Shop permissions
		$permissionGroups['membergroup'][] = 'shop';
		foreach ($this->shop_permissions as $p)
			$permissionList['membergroup'][$p] = [false, 'shop'];

		// Shop disabled? No permissions then
		if (empty($modSettings['Shop_enable_shop']))
			foreach ($this->shop_permissions as $p)
				$hiddenPermissions[] = $p;
	}

	/**
	 * Permissions::illegal_guest()
	 *
	 * ST Shop Illegal permissions
	 * 
	 */
	public function illegal_guest()
	{
		global $context;

		// Guests do not play nicely with this mod
		foreach ($this->shop_permissions as $permission)
			$context['non_guest_permissions'][] = $permission;
	}

	/**
	 * Permissions::language()
	 *
	 * Loads the admin language file for the help popups in the permissions page
	 * 
	 */
	public static function language()
	{
		loadLanguage('Shop/ShopAdmin');
	}
}