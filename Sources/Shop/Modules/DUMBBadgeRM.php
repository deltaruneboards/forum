<?php

/*
 * @package DUMB Badge Remover
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

class DUMBBadgerm extends Module
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
        $this->name = Shop::getText('Shop_dumbbrm_name');
        $this->desc = Shop::getText('Shop_dumbbrm_desc');
        $this->price = 1;

		$this->require_input = true;
		$this->can_use_item = true;
		$this->addInput_editable = false;
    }

    function getUseInput()
    {
        global $smcFunc, $user_info;

        // retrieve badges the user has by count
        // max name is because group by REQUIRES aggregate function columns lol
        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT a.ITEM_ID, MAX(nfo.name) AS name, COUNT(*) as count
            FROM {db_prefix}awards a
            INNER JOIN {db_prefix}stshop_items nfo
            ON a.ITEM_ID = nfo.itemid
            GROUP BY a.ITEM_ID');
        
        $badge_count = $smcFunc['db_fetch_all']($requestDUMBIE);
        $smcFunc['db_free_result']($requestDUMBIE);

        if (empty($badge_count))
            return '
                <div class="errorbox">
                    ' . Shop::getText('dumbbrm_nobadges') . '
                </div>';

        $select = "";

        foreach ($badge_count as $b)
            $select .= '<option value="' . $b['ITEM_ID'] . '">' . $b['name'] . ' x' . $b['count'] . '</option>';

        return '
            <dl class="settings">
                <dt>
                    ' . Shop::getText('dumbbrm_choose_badge') . '
                </dt>
                <dd>
                    <select name="targetBadge">
                        ' . $select . '
                    </select>
                </dd>
                <dd>
                    <input type="number" min="0" id="killcount" name="killcount" value=0 />
                </dd>
            </dl>';
    }

    function onUse()
    {
		global $smcFunc, $user_info;
        checkSession();

        $item_id = $_REQUEST['targetBadge'];

        $smcFunc['db_query']('', '
            DELETE FROM {db_prefix}awards
            WHERE ITEM_ID = {int:itemid} AND ID_AWARDED_MEMBER = {int:user_id}
            LIMIT {int:kc}',
            array(
                'itemid' => $item_id,
                'user_id' => $user_info['id'], // protecting against using someone elses item
                'kc' => $_REQUEST['killcount'],
            ));
        $refundCount = $smcFunc['db_affected_rows']();  // no negative values to get infinite items THIS TIME hopefully

        if ($refundCount < 1) {
            '<div class="infobox">
                ' . Shop::getText('dumbbrm_no_remove') .
            '</div>';
        }

        // look man the existing addItem function either requires an item to already exist
        // or removes from the shop stock
        // and i want neither
        // also using the number of affected rows to "move" the right number
        foreach (range(1, $refundCount) as $idx)
        {
            $smcFunc['db_query']('', '
                INSERT INTO {db_prefix}stshop_inventory
                VALUES(null, {int:user_id}, {int:item_id}, 0, 0, {int:date}, 0, 0)',
                array(
                    'item_id' => $item_id,
                    'user_id' => $user_info['id'],
                    'date' => time()
                ));
        }

        if ($refundCount > 0)
        {
            $smcFunc['db_query']('', '
                INSERT INTO {db_prefix}stshop_log_gift
                VALUES(null, 1, {int:user_id}, {int:count}, {int:item_id}, 0, {string:msg}, 0, {int:date})',
                array(
                    'item_id' => $item_id,
                    'user_id' => $user_info['id'],
                    'count' => $refundCount,
                    'date' => time(),
                    'msg' => "badge removal"    // sql literal strings are tricky
                ));
        }

        return '
            <div class="infobox">
                ' . sprintf(Shop::getText('dumbbrm_success'), $refundCount) .
            '</div>';
    }
}

?>
