<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Tasks;

use Shop\Helper\Database;

class Scheduled
{
	/**
	 * @var int Will help us to figure out if the user has logged in.
	 * @author Zerk
	 */
	var $_login;

	/**
	 * Scheduled::__construct()
	 *
	 * Defines properties with initial values
	 */
	function __construct()
	{
		// Did the user login today? :P
		$this->_login = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
	}

	/**
	 * Scheduled::bank_interest()
	 *
	 * Creates a scheduled task for making money in the bank of every user
	 * @return void
	 */
	public function bank_interest()
	{
		global $modSettings;

		// Create some cash out of nowhere. How? By magical means, of course!
		if (!empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_enable_bank']) && $modSettings['Shop_bank_interest'] > 0)
			Database::Update('members', ['interest' => ($modSettings['Shop_bank_interest'] / 100), 'yesterday' => $this->_login], 'shopBank = shopBank + (shopBank * {float:interest}),', !empty($modSettings['Shop_bank_interest_yesterday']) ? 'WHERE last_login > {int:yesterday}' : '');
	}
}