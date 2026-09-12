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
    // DUMBIE extension: per-user interest
	public function bank_interest()
	{
		global $modSettings, $smcFunc;

        $interset = $modSettings['Shop_bank_interest'];

		// Create some cash out of nowhere. How? By magical means, of course!
        // im going to "create" a database call that doesnt freaking suck -candy

        // what does this even do again
        $timeyes = "";
        if (!empty($modSettings['Shop_bank_interest_yesterday']))
            $timeyes = ' WHERE last_login > {int:yesterday}'

        $smcFunc['db_query']('', '
            UPDATE {db_prefix}members mbr
            SET mbr.shopBank = mbr.shopBank + (abs(shopBank) * (({float:interest} + coalesce(it.interest, 0)) / 100))
            LEFT JOIN {db_prefix}interestmod it ON mbr.id_member = it.USER_ID' . $timeyes,
            array(
                'interest' = $modSettings['Shop_bank_interest'],
                'yesterday' = $this->_login
            ));
	}
}
