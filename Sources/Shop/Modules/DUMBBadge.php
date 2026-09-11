<?php

/*
 * @package DUMB Badge Adder
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

class DUMBBadge extends Module
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
        $this->name = Shop::getText('Shop_dumbb_name');
        $this->desc = Shop::getText('Shop_dumbb_desc');
        $this->price = 50;

		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
    }

    function getAddInput()
    {
        global $smcFunc;

        $existing_info = [0, ''];
        if (!empty($_REQUEST['id'])) {
            $requestDUMBIE = $smcFunc['db_query']('', '
                SELECT ext.sort_order, ext.hover_text
                FROM {db_prefix}stshop_items AS a
                INNER JOIN {db_prefix}awards_extinfo ext ON a.itemid = ext.ITEM_ID
                WHERE a.itemid = {int:instanceid}',
                [
                    'instanceid' => $_REQUEST['id'],
                ]);
            $existing_info = $smcFunc['db_fetch_row']($requestDUMBIE);
            $smcFunc['db_free_result']($requestDUMBIE);
        }


        return '
            <dl class="settings">
                <dt>
                    ' . Shop::getText("dumbb_setting1") . '
                </dt>
                <dd>
                    <input type="number" id="info1" name="order" value="' . $existing_info[0] . '" />
                </dd>
                <dt>
                    ' . Shop::getText("dumbb_setting2") . '
                </dt>
                <dd>
                    <input type="text" id="hover" name="hover" value="' . htmlspecialchars($existing_info[1]) . '" />
                </dd>

            </dl>';

    }

    // DUMBie extension note: postAddInput is a custom function called after saving an item edit
    function postAddInput()
    {
        global $smcFunc;

        $item_id = $_REQUEST['id'];

        // makes sure an entry exists before editing it
        // ignores error if already exists
        $smcFunc['db_query']('', '
            INSERT IGNORE INTO {db_prefix}awards_extinfo VALUES({int:itemid}, null, null)',
            array(
                'itemid' => $item_id
            ));

        if (isset($_REQUEST['order'])) {
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}awards_extinfo
                SET sort_order = {int:order}
                WHERE ITEM_ID = {int:itemid}',
                array(
                    'order' => $_REQUEST['order'],
                    'itemid' => $item_id
                ));
        }

        if (isset($_REQUEST['hover'])) {
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}awards_extinfo
                SET hover_text = {string:hover}
                WHERE ITEM_ID = {int:itemid}',
                array(
                    'hover' => $_REQUEST['hover'],
                    'itemid' => $item_id
                ));
        }
    }

    function onUse()
    {
		global $smcFunc, $user_info;

        $requestDUMBthisisDUMB = $smcFunc['db_query']('', '
            SELECT nfo.itemid, nfo.name
            FROM {db_prefix}stshop_inventory AS a
            INNER JOIN {db_prefix}stshop_items nfo ON a.itemid = nfo.itemid
            WHERE a.id = {int:instanceid} AND userid = {int:uid}',
            array(
                'instanceid' => $_REQUEST['id'],
                'uid' => $user_info['id']   // protecting against using someone elses item
            ));

        $item_id = $smcFunc['db_fetch_row']($requestDUMBthisisDUMB);
        $smcFunc['db_free_result']($requestDUMBthisisDUMB);

        checkSession();

        $smcFunc['db_query']('', '
            INSERT INTO {db_prefix}awards
            VALUES(null, {int:item_id}, {int:user_id}, {int:date}, {int:user_id})',
            array(
                'item_id' => $item_id[0],
                'user_id' => $user_info['id'],
                'date' => time()
            ));

        return '
            <div class="infobox">
                ' . sprintf(Shop::getText('Shop_dumbb_success'), $item_id[1]) .
            '</div>';
    }
}

?>
