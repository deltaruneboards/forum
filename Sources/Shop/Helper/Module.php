<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @author Daniel15 <dansoft@dansoftaustralia.net>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @copyright Copyright (c) 2005-2007, DanSoft Australia
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Helper;

if (!defined('SMF'))
	die('No direct access...');

abstract class Module
{
	/**
	 * All the below fields aren't in the item files themselves. They're just defaults
	 * The correct values are filled in in the getItemDetails() function inside the item.
	 * These values are used as fallovers. If any of the variables aren't defined in the item,
	 * it will use the defaults from here.
	 */

	/**
	 * @var string The default name of the item
	 */
	var $name;

	/**
	 * @var string The default description of the item
	 */
	var $desc;

	/**
	 * @var int The default price
	 */
	var $price;

	/**
	 * @var string The name of the author
	 */
	var $authorName;

	/**
	 * @var string The author's web URL
	 */
	var $authorWeb;

	/**
	 * @var string The email address of the author
	 */
	var $authorEmail;

	/**
	 * @var bool Whether the item requires additional input or not
	 * 
	 * SMFShop versions before 1.0 (I think) always accepted input, regardless
	 * Because of this, this value is set to 'true' for backwards compatibility
	 */
	var $require_input = true; 

	/**
	 * @var bool Whether the item is 'usable' or not
	 */
	var $can_use_item;

	/**
	 * @var bool Whether the item will be deleted from the user's inventory upon use
	 */
	var $delete_after_use;

	/**
	 * @var bool Whether this item allows an admin to change values used by getAddInput()
	 * 
	 * When editing the item. To support this, the item must set the value
	 * of the input field to $item_info[1], $item_info[2], etc.
	 */
	var $addInput_editable;

	/**
	 * @var array Some more vars for the info fields
	 */
	var $item_info = [
		1 => '',
		2 => '',
		3 => '', 
		4 => '',
	];

	/**
	 * Module::__construct()
	 * 
	 * We want to load the language file for these special items and initialize some values
	 * Here, you should fill in the $name, $desc and $price variables.
	 */
	function __construct()
	{
		// Modules have their own language file
		loadLanguage('Shop/Modules');

		$this->authorName = '';
		$this->authorWeb = '';
		$this->authorEmail = '';

		$this->name = '';
		$this->desc = '';
		$this->price = 0;
		$this->require_input = true; 

		$this->can_use_item = true;
		$this->delete_after_use = true;
		$this->addInput_editable = false;

		// Load the initial config
		$this->getItemDetails();
	}

	/**
	 * Module::getItemDetails()
	 * 
	 * This is the main function that loads the details from the module and any other initial config
	 * 
	 * @return void
	 */
	abstract function getItemDetails();
	
	/**
	 * Module::getAddInput()
	 * 
	 * This is called when person is adding this item to their SMFShop installation
	 * via the admin panel. If you need any additional fields, then return them here.
	 * Any inputs should be called info1, info2, info3 and info4, and have a value of
	 * $item_info[1] (so that when editing the item, it shows the previous value)
	 * 
	 * Example:
	 * 
	 * return 'Some input that the admin fills in: <input type="text" name="info1" value="' . $item_info[1] . '" />';
	 * 
	 * @return string The properly formatted inputs for the admin settings
	 */
	function getAddInput()
	{
	}

    /**
     * Module::postAddInput (DUMBie extension)
     *
     * Called after saving when editing or adding an item
     * $_REQUESTS is set from the input fields
     */
    function postAddInput()
    {
    }

	/**
	 * Module::getUseInput()
	 * 
	 * This is called when person tries to use item. This is used to get input for the item.
	 * If item needs no input then just return false here (you don't even need this function
	 * if the can_use_item variable is set to false)
	 * Call the fields whatever you want, as they're passed straight to the onUse() function (via the $_POST array)
	 * 
	 * Example:
	 * 
	 * If input needed:
	 * return 'Enter your name: <input type="text" name="name" />';
	 * 
	 * If no input needed:
	 * return false;
	 * 
	 * @return string The properly formatted inputs for using the item
	 */
	function getUseInput()
	{
	}


	/**
	 * Module::onUse()
	 * 
	 * This is called when item is actually used.
	 * The input fields in the getUseInput() function are passed here in the $_POST array
	 * The admin fields in the getAddInput() function are passed in the $item_info array
	 *
	 * @return void
	 */
	abstract function onUse();
}
