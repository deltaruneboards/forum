<?php

/*
 * @package DUMB Interest Mod
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

class DUMBInterest extends Module
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
        $this->name = Shop::getText('Shop_dumb_interest_name'); //todo add these
        $this->desc = Shop::getText('Shop_dumb_interest_desc');
        $this->price = 200;

		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;

        $this->item_info[1] = 0;
    }

    function getAddInput()
    {
        return '
            <dl class="settings">
                <dt>
                    ' . Shop::getText("dumb_interest_setting1") . '
                </dt>
                <dd>
                    <input type="number" id="info1" name="info1" value="' . $this->item_info[1] . '" />
                </dd>
            </dl>';

    }

    function onUse()
    {
		global $smcFunc, $user_info;

        checkSession();

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

        $currentMod += $this->item_info[1];

        $smcFunc['db_query']('', '
            INSERT INTO {db_prefix}interestmod
            VALUES({int:user_id}, {int:new_interest})
            ON DUPLICATE KEY UPDATE interest = {int:new_interest}',
            array(
                'user_id' => $user_info['id'],
                'new_interest' => $currentMod
            ));

        return '
            <div class="infobox">
                ' . sprintf(Shop::getText('Shop_dumb_interest_success', $currentMod), $item_id[1]) .
            '</div>';
    }
}

?>
