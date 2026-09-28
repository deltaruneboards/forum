<?php

/*
 * @package DUMB Text Module
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

class DUMBTextOnly extends Module
{
    function getItemDetails()
    {
        $this->authorName = 'candycanearter';
        $this->authorWeb = 'candyether.space';
        $this->authorEmail = 'candy@candyether.space';
        $this->name = Shop::getText('dumb_texto_name');
        $this->desc = Shop::getText('dumb_texto_desc');
        $this->price = 50;

        $this->require_input = false;
        $this->can_use_item = true;
        $this->addInput_editable = true;
    }

    function getAddInput()
    {
        global $smcFunc;

        $curText = "";

        if (!empty($_REQUEST['id'])) {
            $requestDUMBIE = $smcFunc['db_query']('', '
                SELECT value FROM {db_prefix}itemlongtext
                WHERE ITEM_ID = {int:itemid}',
                array(
                    'itemid' => $_REQUEST['id']
                ));
            $fetched = $smcFunc['db_fetch_row']($requestDUMBIE);
            if (!empty($fetched)) { $curText = $fetched[0]; }
            $smcFunc['db_free_result']($requestDUMBIE);
        }

        return '
            <dl class="settings">
                <dt>
                    ' . Shop::getText('dumb_texto_setting1') . '
                </dt>
                <dd>
                    <textarea id="rawtext" name="rawtext" rows="10" cols="50">' . htmlspecialchars($curText) . '</textarea>
                </dd>

            </dl>';
    }

    function postAddInput()
    {
        global $smcFunc;

        $smcFunc['db_query']('', '
            INSERT INTO {db_prefix}itemlongtext
            VALUES ({int:itemid}, {string:text})
            ON DUPLICATE KEY UPDATE value = {string:text}',
            array(
                'itemid' => $_REQUEST['id'],
                'text' => $_REQUEST['rawtext']
            ));
    }

    function onUse()
    {
        global $smcFunc;

        $dispText = Shop::getText('dumb_texto_error');

        $requestDUMBIE = $smcFunc['db_query']('', '
            SELECT value FROM {db_prefix}itemlongtext
            WHERE ITEM_ID = (
                SELECT itemid FROM {db_prefix}stshop_inventory
                WHERE id = {int:instid}
            )',
            array(
                'instid' => $_REQUEST['id']
            ));

        $fetched = $smcFunc['db_fetch_row']($requestDUMBIE);
        if (!empty($fetched)) { $dispText = $fetched[0]; }
        $smcFunc['db_free_result']($requestDUMBIE);

        return '
            <div class="infobox">
                ' . $dispText . '
            </div>';
    }
}

?>
