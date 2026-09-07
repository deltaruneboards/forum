<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Antquinox
 * @copyright Copyright (c) 2026, DUMB
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Modules;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Module;

if (!defined('SMF'))
	die('Hacking attempt...');

class TextOnlyItem extends Module
{
    /**
	 * @var string Display text.
	 */
	private $_displayText;

	/**
	 * TextOnlyItem::getItemDetails()
	 *
	 * Item that only displays text when used.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Antquinox';
		$this->authorWeb = '';
		$this->authorEmail = '';
		$this->name = "Text Only";
		$this->desc = "Displays text when used";
		$this->price = 50;
		$this->require_input = false;
		$this->can_use_item = true;
        $this->addInput_editable = true;
	}

    function getAddInput()
	{
		return '
		<dl class="settings">
			<dt>
				<span class="smalltext"> Text: </span>
			</dt>
			<dd>
				<input type="text" id="displayText" name="displayText" size="255" value="' . $_displayText . '"/>
			</dd>
		</dl>';
	}

	function onUse()
	{
		return '
			<div class="infobox">
				' . $_displayText . '
			</div>';
	}
}
