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
use Shop\Helper\Notify;
use Shop\Helper\Format;

if (!defined('SMF'))
    die('Hacking attempt...');

    
class DUMBLancer extends Module
{
    /**
     * DUMBBadge::getItemDetails()
     * 
     * Set the details and basics of the module, along with default values if needed
     */
     
   	/**
	 * @var object Send notifications to the awarded user
	 */
     private $_notify;
     
    function getItemDetails()
    {
        $this->authorName = 'candycanearter';
        $this->authorWeb = 'candyether.space';
        $this->authorEmail = 'candy@candyether.space';
        $this->name = Shop::getText('dumb_lancer_name'); 
        $this->desc = Shop::getText('dumb_lancer_desc');
        $this->price = 50;
        
        $this->item_info[1] = 75;

		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
		
		$this->_notify = new Notify;
    }

    function getAddInput()
    {
        global $user_info, $sourcedir, $smcFunc;
        require_once($sourcedir . '/Subs-MembersOnline.php');
        
		return '
		
		<dl class="settings">
			<dt>
				' . Shop::getText('dumb_lancer_setting1') . '
			<dt>
			<dd>
				<input type="number" id="info1" name="info1" value="' . $this->item_info[1] . '" />
			</dd>
		</dl>';
	}

    function onUse()
    {
		global $user_info, $sourcedir, $smcFunc;
        require_once($sourcedir . '/Subs-MembersOnline.php');
        

		// Check sesh
		checkSession();
		
		$aliveTable = getMembersOnlineStats(['show_hidden' => true, 'sort' => ''])['users_online'];
        unset($aliveTable['_' . $user_info['username']]);    // you cant just gift yourself!!
        $moneyTarget = $aliveTable[array_rand($aliveTable)];
        
		if (empty($moneyTarget))
            fatal_error(Shop::getText('dumb_lancer_nobody', false));

        $money = $this->item_info[1];
            
        $this->_notify->alert($moneyTarget['id'], 'module_lancer', $user_info['id'],
            ['item_icon' => 'money_recieved', 'amount' => Format::cash(75), 'ignore_prefs' => true, 'language' => 'Modules']
            );
            
        updateMemberData($moneyTarget['id'], ['shopMoney' => $moneyTarget['shopMoney'] + $this->item_info[1]]);
        
		return '
			<div class="infobox">
				' . sprintf(Shop::getText('dumb_lancer_success'), $moneyTarget['name']) . '
			</div>';
    }
}

?>
