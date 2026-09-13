<?php

/*
 * @package DUMB Gambler
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

class DUMBGambling extends Module
{
    private $_order;

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
        $this->name = Shop::getText('dumb_gamble_name'); // todo do this
        $this->desc = Shop::getText('dumb_gamble_desc');
        $this->price = 50;

		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
    }

    function getAddInput()
    {
        global $smcFunc;

        $instid = empty($_REQUEST['id']) ? -1 : $_REQUEST['id'];

        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT coalesce(itm.name, ctg.name) AS name, lt.weight, lt.is_category FROM {db_prefix}loottable lt
            LEFT JOIN {db_prefix}stshop_items itm ON itm.itemid = lt.ID_GRANTING
            LEFT JOIN {db_prefix}stshop_categories ctg ON ctg.catid = lt.ID_GRANTING
            WHERE lt.ITEM_ID = {int:instanceid}
            ORDER BY lt.is_category',
            [
                'instanceid' => $instid
            ]);
        $existing_info = $smcFunc['db_fetch_all']($requestDUMBIE);
        $smcFunc['db_free_result']($requestDUMBIE);

        $existing_text = "<ul>";


        foreach ($existing_info as $ex) 
            $existing_text .= "<li>" . $ex["name"] . " (" . $ex["weight"] . " weight" . ($ex["is_category"] == 1 ? " (category)" : "") . ")</li>";

        $existing_text .= "</ul>";


        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT itemid, name FROM {db_prefix}stshop_items
            WHERE status = 1
            ');

        $all_items = $smcFunc['db_fetch_all']($requestDUMBIE);
        $smcFunc['db_free_result']($requestDUMBIE);

        if (empty($all_items))
            $all_items = [];
        array_unshift($all_items, array('itemid' => -1, 'name' => 'add item group'));

        $item_list = "";
        foreach ($all_items as $itm)
            $item_list .= '<option value="' . $itm['itemid'] . '">' . $itm['name'] . '</option>';


        $requestDUMBIE = $smcFunc['db_query']('', 'SELECT catid, name FROM {db_prefix}stshop_categories');
        $all_cats = $smcFunc['db_fetch_all']($requestDUMBIE);
        $smcFunc['db_free_result']($requestDUMBIE);

        $cat_list = "";
        foreach ($all_cats as $itm)
            $cat_list .= '<option value="' . $itm['catid'] . '">' . $itm['name'] . '</option>';

        return '
            <dl class="settings">
                <dt>
                    ' . Shop::getText("dumb_gamble_gambleheader") . '
                    ' . $existing_text . '
                </dt>
                <dt>
                    ' . Shop::getText("dumb_gamble_setting1") . '
                </dt>
                <dd>
                    <select id="grantid" name="grantid">
                        ' . $item_list . '
                    </select>
                    <select id="grantcat" name="grantcat">
                        ' . $cat_list . '
                    </select>
                </dd>
                <dt>
                    ' . Shop::getText("dumb_gamble_setting2") . '
                </dt>
                <dd>
                    <input type="number" id="weight" name="weight" value="1" />
                </dd>

            </dl>';
    }

    // DUMBie extension note: postAddInput is a custom function called after saving an item edit
    function postAddInput()
    {
        global $smcFunc;


        $item_id = $_REQUEST['id'];
        $is_cat = 0;
        $grantid = $_REQUEST['grantid'];
        $weight = $_REQUEST['weight'];

        if ($weight == 0)
            return;

        if ($grantid == "-1") {
            $grantid = $_REQUEST['grantcat'];
            $is_cat = 1;
        }

        if ($weight > 0)
            $smcFunc['db_query']('', '
                INSERT INTO {db_prefix}loottable VALUES({int:itemid}, {int:grantid}, {int:is_cat}, {int:weight})
                ON DUPLICATE KEY UPDATE weight = {int:weight}, is_category = {int:is_cat}',
                array(
                    'itemid' => $item_id,
                    'grantid' => $grantid,
                    'is_cat' => $is_cat,
                    'weight' => $weight
                ));
         else
             $smcFunc['db_query']('', '
                DELETE FROM {db_prefix}loottable
                WHERE ITEM_ID = {int:itemid}
                AND ID_GRANTING = {int:grantid}
                AND is_cat = {int:is_cat}',
                array(
                    'itemid' => $item_id,
                    'grantid' => $grantid,
                    'is_cat' => $is_cat,
                    'weight' => $weight
                ));
    }

    function onUse()
    {
		global $smcFunc, $user_info;

        checkSession();

        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT ID_GRANTING, is_category, weight FROM {db_prefix}loottable
            WHERE ITEM_ID = (
                SELECT itm.itemid FROM {db_prefix}stshop_inventory inv
                INNER JOIN {db_prefix}stshop_items itm ON itm.itemid = inv.itemid
                WHERE inv.id = {int:inst_id}
            )',
            array(
                'inst_id' => $_REQUEST['id']
            ));
        $loottable = $smcFunc['db_fetch_all']($requestDUMBIE);
        $smcFunc['db_free_result']($requestDUMBIE);

        # copied this from stackoverflow
        $weight_sum = 0;
        foreach ($loottable as $lt)
            $weight_sum += $lt['weight'];

        $rtd = rand(1, $weight_sum);
        $candidate = null;

        // this is set up so its IMPOSSIBLE for this to not get SOME item
        // probably better than crashing and burning
        while ($rtd > 0) {
            $candidate = array_pop($loottable);
            if ($rtd > $candidate['weight']) {
                $rtd -= $candidate['weight'];
                continue;
            }
            break;
        }

        $item_granted = $candidate['ID_GRANTING'];
        if ($candidate['is_category'] == 1) {

            // finally, a straightforward db query
            $requestDUMBIE = $smcFunc['db_query']('', '
                SELECT itemid FROM {db_prefix}stshop_items
                WHERE catid = {int:cat_id}',
                array(
                    'cat_id' => $item_granted
                ));

            $candi = $smcFunc['db_fetch_all']($requestDUMBIE);
            $smcFunc['db_free_result']($requestDUMBIE);


            // its probably fine that its not cryptographically secure or wtv
            $item_granted = $candi[array_rand($candi)]['itemid'];
        }


        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT name FROM {db_prefix}stshop_items
            WHERE itemid = {int:item_id}',
            array(
                'item_id' => $item_granted
            ));
        $labelName = $smcFunc['db_fetch_row']($requestDUMBIE)[0];
        $smcFunc['db_free_result']($requestDUMBIE);

        // see me complaining about this in DUMBBadgeRM
        $smcFunc['db_query']('', '
            INSERT INTO {db_prefix}stshop_inventory
            VALUES(null, {int:user_id}, {int:item_id}, 0, 0, {int:date}, 0, 0)',
            array(
                'item_id' => $item_granted,
                'user_id' => $user_info['id'],
                'date' => time()
            ));

        $smcFunc['db_query']('', '
            INSERT INTO {db_prefix}stshop_log_gift
            VALUES(null, 1, {int:user_id}, {int:count}, {int:item_id}, 0, {string:msg}, 0, {int:date})',
            array(
                'item_id' => $item_granted,
                'user_id' => $user_info['id'],
                'count' => 1,
                'date' => time(),
                'msg' => 'gambling!!'
            ));

        return '
            <div class="infobox">
                ' . sprintf(Shop::getText('dumb_gamble_success'), $labelName) .
            '</div>';
    }
}

?>
