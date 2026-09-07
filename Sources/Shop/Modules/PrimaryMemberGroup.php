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
use Shop\Helper\Module;

if (!defined('SMF'))
	die('Hacking attempt...');

class PrimaryMemberGroup extends Module
{
	/**
	 * @var array Forum groups.
	 */
	private $_groups;

	/**
	 * @var string A string with the options (groups) to choose from
	 */
	private $_select = '';

	/**
	 * PrimaryMemberGroup::getItemDetails()
	 *
	 * Item to change a user's primary membergroup.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'https://github.com/Daniel15';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		$this->name = Shop::getText('pmg_name');
		$this->desc = Shop::getText('pmg_desc');
		$this->price = 50000;
		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
	}

	function getAddInput()
	{
		// Get the forum groups, except admin/mod
		$this->_groups = Database::Get(0, 1000, 'm.group_name', 'membergroups AS m', ['m.id_group', 'm.group_name'], 'WHERE m.min_posts = -1 AND m.id_group <> 1 AND m.id_group <> 3');

		// For some reason you are using this module, but have not groups whatsoever
		if (empty($this->_groups))
			return '
			<div class="errorbox">
				' . Shop::getText('pmg_nogroups') . '
			</div>';

		// Show the actual options
		else
		{
			// Loop through the groups
			foreach ($this->_groups AS $group)
				$this->_select .= '<option value="' . $group['id_group'] . '"' . ($group['id_group'] == $this->item_info[1] ? ' selected' : '') . '>' . $group['group_name'] . '</option>';

			return '
			<dl class="settings">
				<dt>
					' . Shop::getText('pmg_setting1') . '<br/>
					<span class="smalltext">' . Shop::getText('pmg_setting1_desc') . '</span>
				<dt>
				<dd>
					<select name="info1">
						' . $this->_select . '
					</select>
				</dd>
			</dl>';
		}
	}

	function onUse()
	{
		global $user_info, $sourcedir;

		// Required file just in case
		require_once($sourcedir . '/Subs-Membergroups.php');

		// Check sesh
		checkSession();

		// Add user to the group
		addMembersToGroup($user_info['id'], $this->item_info[1], 'auto', true);

		// Display message box
		return '
			<div class="infobox">
				' . Shop::getText('pmg_success') . '
			</div>';
	}
}
