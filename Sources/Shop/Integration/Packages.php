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

if (!defined('SMF'))
	die('No direct access...');

class Packages
{
	/**
	 * @var array Will provide the typs of packages we want to add.
	 */
	var $_types;

	/**
	 * @var string Just a handy help var.
	 */
	var $_key;

	/**
	 * Packages::__construct()
	 *
	 * Build our column array with the board columns
	 */
	function __construct()
	{
		// Add the columns when needed
		$this->_types = [
			'shop_modules',
			'shop_games',
		];
	}

	public function package_downupload()
	{
		global $context, $scripturl;

		foreach ($this->_types as $type)
			if ($context['package']['type'] == $type)
				$context['package']['install']['link'] = '<a href="' . $scripturl . '?action=admin;area=packages;sa=install;package=' . $context['package']['filename'] . '">[ ' . Shop::getText('install_' . $type, false) . ' ]</a>';
	}

	public function modification_types()
	{
		global $context;

		foreach ($this->_types as $type)
			$context['modification_types'][] = $type;
	}

	public function packages_sort(&$sort_id, &$packages)
	{
		foreach ($this->_types as $type)
			$sort_id[$type] = 1;
	}
}