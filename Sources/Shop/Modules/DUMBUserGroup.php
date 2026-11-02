<?php

/*
 * @package DUMB Usergroup Mod
 * @version 0.1
 * @author candycanearter <candy@candyether.space>
 * @copyright Public Domain
 * @license ???
 */

// DUMBie extension: like all of this obviously

namespace Shop\Modules;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Module;

if (!defined('SMF'))
    die('Hacking attempt...');

class DUMBUsergroup extends Module
{
    /**
     * DUMBBadge::getItemDetails()
     * 
     * Set the details and basics of the module, along with default values if needed
     */
    function getItemDetails()
    {
        $this->authorName = 'candycanearter';
        $this->authorWeb = 'candyether.space';
        $this->authorEmail = 'candy@candyether.space';
        $this->name = Shop::getText('dumbgroup_name'); // todo make strings for everything
        $this->desc = Shop::getText('dumbgroup_desc');
        $this->price = 100;

		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
    }

    // copied wholesale from PrimaryMemberGroup lol
    function getAddInput()
    {
		// Get the forum groups, except admin/mod
		$groups = Database::Get(0, 1000, 'm.group_name', 'membergroups AS m', ['m.id_group', 'm.group_name'], 'WHERE m.min_posts = -1 AND m.id_group <> 1 AND m.id_group <> 3');
        $select = '<option value="0">Unassign group</option>';

		// For some reason you are using this module, but have not groups whatsoever
		if (empty($groups))
			return '
			<div class="errorbox">
				' . Shop::getText('pmg_nogroups') . '
			</div>';

		// Show the actual options
		else
		{
			// Loop through the groups
			foreach ($groups AS $group)
				$select .= '<option value="' . $group['id_group'] . '"' . ($group['id_group'] == $this->item_info[1] ? ' selected' : '') . '>' . $group['group_name'] . '</option>';

			return '
			<dl class="settings">
				<dt>
					' . Shop::getText('pmg_setting1') . '<br/>
					<span class="smalltext">' . Shop::getText('pmg_setting1_desc') . '</span>
				<dt>
				<dd>
					<select name="info1">
						' . $select . '
					</select>
				</dd>
			</dl>';
		}
	}

    function onUse()
    {
		global $user_info, $sourcedir, $smcFunc;

		// Required file just in case
		require_once($sourcedir . '/Subs-Membergroups.php');

		// Check sesh
		checkSession();

        // if the current member is not in any membergroups we dont need to do any checks
        // to refund any items

        if ($user_info['groups'][0] > 0) {
            $requestDUMBIE = $smcFunc['db_query']('', '
                SELECT itemid FROM {db_prefix}stshop_items
                WHERE info1 = (SELECT id_group FROM {db_prefix}members WHERE id_member = {int:user_id})
                AND module = (SELECT id FROM {db_prefix}stshop_modules WHERE file = "DUMBUserGroup")
                ',
                ['user_id' => $user_info['id']]
            );

            $title_item = $smcFunc['db_fetch_row']($requestDUMBIE);
            $smcFunc['db_free_result']($requestDUMBIE);

            if (empty($title_item)) {
                return '
                    <div class="infobox">
                        ' . Shop::getText('dumbgroup_noitem') . '
                    </div>';
            }
            else {
                // see me complaining about this in DUMBBadgeRM
                $smcFunc['db_query']('', '
                    INSERT INTO {db_prefix}stshop_inventory
                    VALUES(null, {int:user_id}, {int:item_id}, 0, 0, {int:date}, 0, 0)',
                    array(
                        'item_id' => $title_item[0],
                        'user_id' => $user_info['id'],
                        'date' => time()
                    ));

                $smcFunc['db_query']('', '
                    INSERT INTO {db_prefix}stshop_log_gift
                    VALUES(null, 1, {int:user_id}, {int:count}, {int:item_id}, 0, {string:msg}, 0, {int:date})',
                    array(
                        'item_id' => $title_item[0],
                        'user_id' => $user_info['id'],
                        'count' => 1,
                        'date' => time(),
                        'msg' => 'title removal'
                    ));
                }
            }

		// Add user to the group
        // overriding the existing primary group
        // dear god i really hope this doesnt break somehow
        // and deadmin someon
        if ($this->item_info[1] > 0)
            addMembersToGroup($user_info['id'], $this->item_info[1], 'force_primary', true);
        else
            removeMembersFromGroups($user_info['id'], null, true);
        // hopefully this doesnt break anything in the future??
        // on the other hand idk how mad smf would get about having a member
        // with no primary mgroup but also having subgroups


		// Display message box
        // idrc we cn probably get away with reusing the string from the other one
		return '
			<div class="infobox">
				' . Shop::getText('pmg_success') . '
			</div>';
    }
}

?>
