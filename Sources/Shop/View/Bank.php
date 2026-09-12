<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\View;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Format;
use Shop\Helper\Log;

if (!defined('SMF'))
	die('No direct access...');

class Bank
{
	/**
	 * @var object Log any information regading bank transactions.
	 */
	private $_log;

	/**
	 * @var int The amount for the transaction.
	 */
	private $_amount;

	/**
	 * @var int Type of fee charge.
	 */
	private $_type;

	/**
	 * @var bool|string Set if we got a transaction
	 */
	private $_trans;

	/**
	 * Bank::__construct()
	 *
	 * Create instance of needed objects
	 */
	function __construct()
	{
		global $modSettings;

		// We need to recycle specific strings
		loadLanguage('Shop/ShopAdmin');

		// Prepare to log the gift
		$this->_log = new Log;

		// By default we got nothing
		$this->_trans = false;

		// What if the bank is disabled?
		if (empty($modSettings['Shop_enable_bank']))
			fatal_error(Shop::getText('currently_disabled_bank'), false);

		// Check if user is allowed to access this section
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_canBank');
	}

	public function main()
	{
		global $context, $scripturl, $modSettings, $user_info, $smcFunc;

        // idk how expensive hitting the database here is but its Probably fine
        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT interest FROM {db_prefix}interestmod
            WHERE USER_ID = {int:user_id}',
            array(
                'user_id' => $user_info['id']
            ));
        $currentMod = $smcFunc['db_fetch_row']($requestDUMBIE);
        $smcFunc['db_free_result']($requestDUMBIE);

        if (empty($currentMod))
            $currentMod = 0;
        else
            $currentMod = $currentMod[0];

        if ($currentMod >= 0)
            $currentMod = "+" . $currentMod;

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_bank');
		$context['page_description'] = sprintf(Shop::getText('bank_desc'), $modSettings['Shop_credits_suffix'], $modSettings['Shop_bank_interest'], $currentMod);
		$context['sub_template'] = 'bank';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=bank',
			'name' => Shop::getText('main_bank'),
		];
	
		// Just a happy message... How many credits do you have and what would you like to do?
		$context['bank']['message'] = sprintf(Shop::getText('bank_youhave'), Format::cash($user_info['shopMoney']), Format::cash($user_info['shopBank']));

		// Deposit
		if (isset($_REQUEST['deposit']))
			$context['Shop']['bank']['success'] = Shop::getText('bank_deposit_successfull');
		// Withdraw
		if (isset($_REQUEST['withdrawal']))
			$context['Shop']['bank']['success'] = Shop::getText('bank_withdraw_successfull');
	}

	public function trans()
	{
		global $user_info, $modSettings;

		// Check session
		checkSession();

		// The desired amount
		$this->_amount = (int) isset($_REQUEST['amount']) ? $_REQUEST['amount'] : 0;

		// You cannot leave empty the amount field
		if (empty($this->_amount) || ($this->_amount <= 0))
			fatal_error(Shop::getText('bank_noamount_not_negative'), false);
		// No type? Something must be wrong
		elseif (!isset($_REQUEST['type']))
			fatal_error(Shop::getText('bank_notype'), false);

		// Deposit
		if ($_REQUEST['type'] == 'deposit')
		{
			// Check if we have an appropiate amount according with the max-min deposit
			if (!empty($modSettings['Shop_bank_deposit_min']) || !empty($modSettings['Shop_bank_deposit_max']))
			{	
				// Max and Min deposit
				if ((!empty($modSettings['Shop_bank_deposit_min']) && !empty($modSettings['Shop_bank_deposit_max'])) && (($this->_amount < $modSettings['Shop_bank_deposit_min']) ||  ($this->_amount > $modSettings['Shop_bank_deposit_max'])))
					fatal_error(Shop::getText('bank_notbt_deposit'), false);
				// Min deposit
				elseif ((!empty($modSettings['Shop_bank_deposit_min']) && empty($modSettings['Shop_bank_deposit_max'])) && ($this->_amount < $modSettings['Shop_bank_deposit_min']))
					fatal_error(sprintf(Shop::getText('bank_notbt_deposit'), $modSettings['Shop_bank_deposit_min']), false);
				// Max deposit
				elseif ((empty($modSettings['Shop_bank_deposit_min']) && !empty($modSettings['Shop_bank_deposit_max'])) && ($this->_amount > $modSettings['Shop_bank_deposit_max']))
					fatal_error(sprintf(Shop::getText('bank_notmax_deposit'), $modSettings['Shop_bank_deposit_max']), false);
			}

			// Fee enabled? Let's see if user can afford both
			if (!empty($modSettings['Shop_bank_deposit_fee']))
				$deposit = (int) (($user_info['shopMoney'] - $this->_amount) - $modSettings['Shop_bank_deposit_fee']);
			else
				$deposit = (int) ($user_info['shopMoney'] - $this->_amount);

			// Okay, we want to check if user actually has enough in their pocket and also check for the fee
			if ($deposit < 0 && ($user_info['shopBank']) < $modSettings['Shop_bank_deposit_fee'])
				fatal_error(sprintf(Shop::getText('bank_notenough_pocket'), $modSettings['Shop_credits_suffix']), false);
			// Alright, so user has enough for the deposit then
			elseif ($deposit >= 0 || $user_info['shopBank'] >= $modSettings['Shop_bank_deposit_fee'])
			{
				// Fee from their pocket
				if ($deposit >= 0)
					$this->_type = 0;
				// Fee from their bank
				else
					$this->_type = 1;
				// We get a deposit
				$this->_trans = 'deposit';
			}
		}
		// Withdraw
		elseif ($_REQUEST['type'] == 'withdraw')
		{
			// Does the user have enough money to pay the withdrawal fee?
			$checkmoney = (int) ($user_info['shopMoney'] - $modSettings['Shop_bank_withdrawal_fee']);

			// Check if we have an appropiate amount according with the max-min withdraw
			if (!empty($modSettings['Shop_bank_withdrawal_min']) || !empty($modSettings['Shop_bank_withdrawal_max']))
			{	
				// Max and Min withdraw
				if ((!empty($modSettings['Shop_bank_withdrawal_min']) && !empty($modSettings['Shop_bank_withdrawal_max'])) && (($this->_amount < $modSettings['Shop_bank_withdrawal_min']) ||  ($this->_amount > $modSettings['Shop_bank_withdrawal_max'])))
					fatal_error(Shop::getText('bank_notbt_withdrawal'), false);
				// Min withdraw
				elseif ((!empty($modSettings['Shop_bank_withdrawal_min']) && empty($modSettings['Shop_bank_withdrawal_max'])) && ($this->_amount < $modSettings['Shop_bank_withdrawal_min']))
					fatal_error(sprintf(Shop::getText('bank_notmin_withdrawal'), $modSettings['Shop_bank_withdrawal_min']), false);
				// Max withdraw
				elseif ((empty($modSettings['Shop_bank_withdrawal_min']) && !empty($modSettings['Shop_bank_withdrawal_max'])) && ($this->_amount > $modSettings['Shop_bank_withdrawal_max']))
					fatal_error(sprintf(Shop::getText('bank_notmax_withdrawal'), $modSettings['Shop_bank_withdrawal_max']), false);
			}
			// Check if fee enabled
			if (!empty($modSettings['Shop_bank_withdrawal_fee']) && ($checkmoney < 0))
				$withdraw = (int) (($user_info['shopBank'] - $this->_amount) - $modSettings['Shop_bank_withdrawal_fee']);
			else
				$withdraw = (int) ($user_info['shopBank'] - $this->_amount);

			// Okay, let's see if we got enough money...
			if ($withdraw < 0)
				fatal_error(sprintf(Shop::getText('bank_notenough_bank'), $modSettings['Shop_credits_suffix']), false);
			// Go ahead with the withdraw
			elseif ($withdraw >= 0)
			{
				// Bank
				if (!empty($modSettings['Shop_bank_withdrawal_fee']) && ($checkmoney < 0))
					$this->_type = 1;
				// Pocket
				else
					$this->_type = 0;
				// We get a withdraw
				$this->_trans = 'withdrawal';
			}
		}
		// We got something unexpected
		else
			fatal_error(Shop::getText('bank_notype'), false);

		// We got a transaction?
		if (!empty($this->_trans))
		{
			// Log the transaction
			$this->_log->bank($user_info['id'], $this->_amount, $this->_trans, $modSettings['Shop_bank_' . $this->_trans . '_fee'], $this->_type);

			// Redirect the user to success
			redirectexit('action=shop;sa=bank;'.$this->_trans);
		}
	}
}
