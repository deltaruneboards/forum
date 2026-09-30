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
        $this->name = Shop::getText('dumbb_name');
        $this->desc = Shop::getText('dumbb_desc');
        $this->price = 50;

		$this->require_input = true;
		$this->can_use_item = true;
		$this->addInput_editable = true;

        // by default, not giftable or custom description-able(?)
        $this->item_info[1] = false;
        $this->item_info[2] = false;
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
                    <input type="number" id="order" name="order" value="' . $existing_info[0] . '" />
                </dd>
                <dt>
                    ' . Shop::getText("dumbb_setting2") . '
                </dt>
                <dd>
                    <input type="text" id="hover" name="hover" value="' . htmlspecialchars($existing_info[1]) . '" />
                </dd>
                <dt>
                    ' . Shop::getText("dumbb_setting3") . '
                </dt>
                <dd>
                    <input type="checkbox" id="info1" name="info1" value="1" ' . (empty($this->item_info[1]) ? '' : 'checked') . ' />
                </dd>
                <dt>
                    ' . Shop::getText("dumbb_setting4") . '
                </dt>
                <dd>
                    <input type="checkbox" id="info2" name="info2" value="1" ' . (empty($this->item_info[2]) ? '' : 'checked') . ' />
                </dd>


            </dl>';

    }

    function getUseInput()
    {
        global $context;

        $retr = '<dl class="settings">';
        if (!empty($this->item_info[1])) {
            $retr .= '
                <dt>
                    ' . Shop::getText('dumbb_setuser') . '
                </dt>
                <dd>
                    <input type="text" name="membername" id="membername" />
                    <div id="membernameItemContainer"></div>
                </dd>
                <script>
                    var oAddMemberSuggest = new smc_AutoSuggest({
                        sSelf: \'oAddMemberSuggest\',
                        sSessionId: \''. $context['session_id']. '\',
                        sSessionVar: \''. $context['session_var']. '\',
                        sSuggestId: \'to_suggest\',
                        sControlId: \'membername\',
                        sSearchType: \'member\',
                        sPostName: \'memberid\',
                        sURLMask: \'action=profile;u=%item_id%\',
                        sTextDeleteItem: \''. Shop::getText('autosuggest_delete_item', false). '\',
                        sItemListContainerId: \'membernameItemContainer\'
                    });
                </script>';
        }

        if (!empty($this->item_info[2])) {
            $retr .= '
                <dt>
                    ' . Shop::getText('dumbb_setdesc') . '
                </dt>
                <dd>
                    <input type="text" name="customdesc" size="50" />
                </dd>';
        }

        $retr .= '</dl>';

        return $retr;
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

        // checking a description is set if needed

        if (!empty($this->item_info[2])) {
            if (empty($_REQUEST['customdesc'])) { fatal_error(Shop::getText('cot_empty_title'), false); }
            $setdesc = $_REQUEST['customdesc'];
        }

        // by default, self apply
        $target_member = $user_info['id'];

        // resolving the member name to an id (also its a bit more difficult bc of jammys changse)
        // i think real_name is the display name that should be fine hopefully??

        if (!empty($this->item_info[1])) {
            if (empty($_REQUEST['membername'])) { fatal_error(Shop::getText('user_unable_tofind'), false); }

            $requestDUMBIE = $smcFunc['db_query']('', '
                SELECT id_member FROM {db_prefix}members
                WHERE real_name = {string:member}',
                array(
                    'member' => $_REQUEST['membername']
                ));

            $target_member = $smcFunc['db_fetch_row']($requestDUMBIE)[0];
            if (empty($target_member)) { fatal_error(Shop::getText('user_unable_tofind'), false); }
            $smcFunc['db_free_result']($requestDUMBIE);
        }




        $requestDUMBIE = $smcFunc['db_query']('', '
            INSERT INTO {db_prefix}awards
            VALUES(null, {int:item_id}, {int:user_id}, {int:date}, {int:self_id})
            RETURNING ID_AWARD',
            array(
                'item_id' => $item_id[0],
                'user_id' => $target_member,
                'self_id' => $user_info['id'],
                'date' => time()
            ));

        $badge_id = $smcFunc['db_fetch_row']($requestDUMBIE)[0];
        $smcFunc['db_free_result']($requestDUMBIE);

        if (!empty($setdesc)) {
            $smcFunc['db_query']('', '
                INSERT INTO {db_prefix}awards_overrides(ID_AWARD, hover_text)
                VALUES({int:badge_id}, {string:desc})
                ON DUPLICATE KEY UPDATE hover_text = {string:desc}',
                array(
                    'badge_id' => $badge_id,
                    'desc' => $setdesc
                ));
        }

        return '
            <div class="infobox">
                ' . sprintf(Shop::getText('Shop_dumbb_success'), $item_id[1]) .
            '</div>';
    }
}

?>
