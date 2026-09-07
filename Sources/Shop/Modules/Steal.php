<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Modules;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Format;
use Shop\Helper\Module;
use Shop\Helper\Notify;

if (!defined('SMF'))
	die('Hacking attempt...');

class Steal extends Module
{
	/**
	 * @var object Send notifications to the user that gets robbed.
	 */
	private $_notify;

	/**
	 * @var int The stolen credits.
	 */
	private $_credits;

	/**
	 * @var int The probability of success.
	 */
	private $_probability;

	/**
	 * @var string The name of the user.
	 */
	private $_membername;

	/**
	 * Steal::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Diego Andrés';
		$this->authorWeb = 'https://smftricks.com/';
		$this->authorEmail ='admin@smftricks.com';
		$this->name = Shop::getText('steal_name');
		$this->desc = Shop::getText('steal_desc');
		$this->price = 50;
		$this->require_input = true;
		$this->can_use_item = true;
		$this->addInput_editable = true;

		// 40% by default
		$this->item_info[1] = 40;

		// PM's disabled by default
		$this->item_info[2] = false;

		// Alerts enabled
		$this->item_info[3] = true;

		// Notify
		$this->_notify = new Notify;
	}

	function getAddInput()
	{
		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('steal_setting1') . '<br/>
					<span class="smalltext">' . Shop::getText('steal_setting1_desc') . '</span>
				</dt>
				<dd>
					<input type="number" min="1" max="100" id="info1" name="info1" value="' . $this->item_info[1] . '" />
				</dd>
				<dt>
					' . Shop::getText('steal_setting2') . '<br/>
					<span class="smalltext">' . Shop::getText('steal_setting2_desc') . '</span>
				</dt>
				<dd>
					<input type="checkbox" id="info2" name="info2" value="1"'. (!empty($this->item_info[2]) ? ' checked' : ''). ' />
				</dd>
				<dt>
					' . Shop::getText('steal_setting3') . '<br/>
					<span class="smalltext">' . Shop::getText('steal_setting3_desc') . '</span>
				</dt>
				<dd>
					<input type="checkbox" id="info3" name="info3" value="1"'. (!empty($this->item_info[3]) ? ' checked' : ''). ' />
				</dd>
			</dl>';
	}

	function getUseInput()
	{
		global $context;

		return '
			<dl class="settings">
				<dt>
					' . Shop::getText('steal_from') . '<br />
					<span class="smalltext">' . Shop::getText('inventory_member_find') . '</span>
				</dt>
				<dd>
					<input type="text" name="stealfrom" id="stealfrom" />
					<div id="membernameItemContainer"></div>
				</dd>
			</dl>
			<script>
				var oAddMemberSuggest = new smc_AutoSuggest({
					sSelf: \'oAddMemberSuggest\',
					sSessionId: \''. $context['session_id']. '\',
					sSessionVar: \''. $context['session_var']. '\',
					sSuggestId: \'to_suggest\',
					sControlId: \'stealfrom\',
					sSearchType: \'member\',
					sPostName: \'memberid\',
					sURLMask: \'action=profile;u=%item_id%\',
					sTextDeleteItem: \''. Shop::getText('autosuggest_delete_item', false). '\',
					sItemListContainerId: \'membernameItemContainer\'
				});
			</script>';
	}

	function onUse()
	{
		global $user_info, $scripturl;

		// Check some inputs
		if (!isset($_REQUEST['stealfrom']) || empty($_REQUEST['stealfrom'])) 
			fatal_error(Shop::getText('user_unable_tofind'), false);

		// Get a random number between 0 and 100
		$this->_probability = mt_rand(0, 100);

		checkSession();

		// If successful
		if ($this->_probability <= $this->item_info[1])
		{
			$member_query = [];
			$member_parameters = [];

			// Get the member name...
			$this->_membername = Database::sanitize($_REQUEST['stealfrom']);

			// Construct the query
			if (!empty($this->_membername))
			{
				$member_query[] = 'LOWER(member_name) = {string:member_name}';
				$member_query[] = 'LOWER(real_name) = {string:member_name}';
				$member_parameters['member_name'] = $this->_membername;
			}

			// Excecute
			if (!empty($member_query))
			{
				$memResult = Database::Get(0, 1000, 'id_member', 'members', ['id_member', 'shopMoney', 'real_name'], 'WHERE (' . implode(' OR ', $member_query) . ')', true, '', $member_parameters);

				// We got a result?
				if (empty($memResult))
					fatal_error(Shop::getText('user_unable_tofind'), false);

				// You can't steal from yourself lol. Unless?
				elseif ($memResult['id_member'] == $user_info['id'])
					fatal_error(Shop::getText('steal_error_yourself'), false);

				// That user's pocket is empty!
				elseif (empty($memResult['shopMoney']))
					fatal_error(Shop::getText('steal_error_zero'), false);

				// Get random amount between 1 and the amount of money stealee has
				$this->_credits = mt_rand(1, $memResult['shopMoney']);

				// Stolen!
				updateMemberData($memResult['id_member'], ['shopMoney' => $memResult['shopMoney'] - $this->_credits]);
				// Robbed!
				updateMemberData($user_info['id'], ['shopMoney' => $user_info['shopMoney'] + $this->_credits]);

				// Send a PM?
				if (!empty($this->item_info[2]))
					$this->_notify->pm($memResult['id_member'], Shop::getText('steal_notification_robbed'), sprintf(Shop::getText('steal_notification_pm'), $scripturl . '?action=profile;u=' . $user_info['id'], $user_info['name'], Format::cash($this->_credits), ($memResult['shopMoney'] - $this->_credits)));

				// Alert??
				if (!empty($this->item_info[3]))
					$this->_notify->alert($memResult['id_member'], 'module_steal', $user_info['id'], ['item_icon' => 'steal', 'amount' => Format::cash($this->_credits), 'ignore_prefs' => true, 'language' => 'Modules']);
			}
			// Success!
			return '
				<div class="infobox">
					' . sprintf(Shop::getText('steal_success'. ($this->_credits < 200 ? '1' : '2')), Format::cash($this->_credits), $memResult['real_name']) . '
				</div>';
		}
		// Unlucky thief!
		else
			return '
			<div class="errorbox">
				' . Shop::getText('steal_error') . '
			</div>';
	}
}