<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration;

use Shop\Shop;
use Shop\Helper\Format;
use Shop\View\Inventory;

if (!defined('SMF'))
	die('No direct access...');

class User
{
	/**
	 * @var object We will create an object for the specified item if needed.
	 */
	private $_inventory;

	/**
	 * @var array Save some user/profile information.
	 */
	private $_profile;

	/**
	 * User::__construct()
	 *
	 * Need to load some stuff first
	 */
	function __construct()
	{
		global $topic;

		// We only need/want these on topics really
		if (!empty($topic))
		{
			// Create new instance of inventory
			$this->_inventory = new Inventory;

			// Load the template just once without disrupting other parts of the logic
			add_integration_function('integrate_load_theme', __CLASS__ . '::template', false);
		}

		// Load language just in case
		loadLanguage('Shop/Shop');
	}

	/**
	 * User::template()
	 *
	 * Helper to load the inventory template
	 */
	public static function template()
	{
		// Load template
		loadTemplate('Shop/Inventory');
	}

	/**
	 * User::load_member_data()
	 *
	 * Include our shop columns in loadMemberData
	 * 
	 * @param string $columns The member columns
	 * @param string $tablws Any additional tables
	 * @param string $set What kind of data to load (normal, profile, minimal)
	 * @return void
	 */
	public function load_member_data(&$columns, &$tables, &$set)
	{
		switch ($set)
		{
			case 'normal':
				$columns .= ', mem.shopMoney, mem.shopBank, mem.shopInventory_hide, mem.gamesPass';
				break;
			case 'profile':
				$columns .= ', mem.shopMoney, mem.shopBank, mem.shopInventory_hide, mem.gamesPass';
				break;
			case 'minimal':
				$columns .= ', mem.shopMoney, mem.shopBank, mem.shopInventory_hide, mem.gamesPass';
				break;
			default:
				trigger_error('loadMemberData(): Invalid member data set \'' . $set . '\'', E_USER_WARNING);
		}
	}

	/**
	 * User::user_info()
	 *
	 * Used for adding new elements to the $user_info array
	 * 
	 * @return void
	 */
	public function user_info()
	{
		global $user_info, $user_settings;

		$user_info['shopMoney'] = isset($user_settings['shopMoney']) ? $user_settings['shopMoney'] : 0;
		$user_info['shopBank'] = isset($user_settings['shopBank']) ? $user_settings['shopBank'] : 0;
		$user_info['shopInventory_hide'] = isset($user_settings['shopInventory_hide']) ? $user_settings['shopInventory_hide'] : 0;
		$user_info['gamesPass'] = isset($user_settings['gamesPass']) ? $user_settings['gamesPass'] : 0;
	}

	/**
	 * User::simple_actions()
	 *
	 * Load new elements to $context['user'] array in case we want to handle stuff from templates
	 * 
	 * @param array $simpleActions Simple actions
	 * @param array $simpleAreas Simple areas
	 * @param array $simpleSubActions Simple sub-actions
	 * @param array $extraParams Additional parameters
	 * @param array $xmlActions XML actions
	 * @return void
	 */
	public function simple_actions(&$simpleActions, &$simpleAreas, &$simpleSubActions, &$extraParams, &$xmlActions)
	{
		global $context, $user_info;

			if (!$context['user']['is_guest'])
			{
				$context['user']['shopMoney'] = $user_info['shopMoney'];
				$context['user']['shopBank'] = $user_info['shopBank'];
				$context['user']['gamesPass'] = $user_info['gamesPass'];
			}
			else
			{
				$context['user']['shopMoney'] = 0;
				$context['user']['shopBank'] = 0;
				$context['user']['gamesPass'] = 0;
			}
	}

	/**
	 * User::member_context()
	 *
	 * Shop custom profile fields
	 * 
	 * @param array $data The monstrous array of user information
	 * @param int $user The ID of a user previously loaded by {@link loadMemberData()}
	 * @param bool $display_custom_fields Whether or not to display custom profile fields
	 * @return void
	 */
	public function member_context(&$data, $user, $display_custom_fields)
	{
		global $user_profile, $modSettings, $topic;

		// Set the data
		$this->_profile = $user_profile[$user];
		$data['shopMoney'] = $this->_profile['shopMoney'];
		$data['shopBank'] = $this->_profile['shopBank'];
		$data['shopInventory_hide'] = $this->_profile['shopInventory_hide'];
		$data['gamesPass'] = $this->_profile['gamesPass'];

		// Pocket credits
		if ($modSettings['Shop_display_pocket'] == 1 || $modSettings['Shop_display_pocket'] == 3)
			$data['custom_fields']['shopMoney'] = [
				'title' => Shop::getText('posting_credits_pocket'),
				'col_name' => 'Shop_pocket',
				'value' => Format::cash($this->_profile['shopMoney'], false, $modSettings['Shop_display_bank_placement']),
				'placement' => $modSettings['Shop_display_pocket_placement'],
			];

		// Bank credits
		if (($modSettings['Shop_display_bank'] == 1) || ($modSettings['Shop_display_bank'] == 3) && !empty($modSettings['Shop_enable_shop']) && !empty($modSettings['Shop_enable_bank']))
			$data['custom_fields']['shopBank'] = [
				'title' => Shop::getText('posting_credits_bank'),
				'col_name' => 'Shop_bank',
				'value' => Format::cash($this->_profile['shopBank'], false, $modSettings['Shop_display_bank_placement']),
				'placement' => $modSettings['Shop_display_bank_placement'],
			];

		// Inventory
		if (empty($this->_profile['shopInventory_hide']) && !empty($modSettings['Shop_inventory_enable']) && !empty($modSettings['Shop_enable_shop']) && !empty($topic) && allowedTo('shop_viewInventory') && !empty($user_field_inventory = $this->_inventory->display($user)))
			$data['custom_fields']['shop_inventory'] = [
				'title' => Shop::getText('posting_inventory'),
				'col_name' => 'Shop_inventory',
				'value' => template_shop_inventory($user_field_inventory, $user, false),
				'placement' => $modSettings['Shop_inventory_placement'],
			];
	}

	/**
	 * User::fetch_alerts()
	 *
	 * Tweaks the final alert sent by a shop action
	 * 
	 * @param array $alerts The array containing the alerts
	 * @param array $formats Some sprintf formats for generating links/strings.
	 * @return void
	 */
	public function fetch_alerts(&$alerts, &$formats)
	{
		global $settings, $boardurl;

		foreach ($alerts as $alert_id => $alert)
			if ($alert['content_type'] == 'shop')
			{
				// Load Modules language file?
				if (!empty($alert['extra']['language']))
					loadLanguage('Shop/' . $alert['extra']['language']);

				$alerts[$alert_id]['icon'] = '<img' .(empty($alert['extra']['use_item']) ? '' : ' style="width:16px; height:16px"') . ' class="alert_icon" src="' . (empty($alert['extra']['use_item']) ? $settings['default_images_url'] : $boardurl . Shop::$itemsdir) . (!empty($alert['extra']['item_icon']) ? ((empty($alert['extra']['use_item']) ? '/icons/shop/' : '') . $alert['extra']['item_icon']) : $alert['content_type']) . (empty($alert['extra']['use_item']) ? '.png' : '') . '">';
				$alerts[$alert_id]['extra']['content_link'] = $alert['extra']['item_href'];
			}
	}
}