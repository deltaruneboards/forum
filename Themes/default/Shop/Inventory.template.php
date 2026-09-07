<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

use Shop\Helper\Format;

function template_shop_inventory_search_above()
{
	global $context, $txt, $scripturl;

	// Success messahe on gifts
	if (isset($_REQUEST['success']))
		echo '
		<div class="infobox">', $txt['Shop_gift_' . ($_REQUEST['sa'] == 'sendmoney' ? 'money' : 'item') . '_sent'], '</div>';

	echo '
	<div' . ($_REQUEST['action'] == 'shop' ? ' class="roundframe"' : ''). '>
		<form method="post" action="', $scripturl, $context['form_url'], '">
			<dl class="settings">
				<dt>
					', $txt['Shop_inventory_member_name'], '<br/>
					<span class="smalltext">', $txt['Shop_inventory_member_desc'], '</span>
				</dt>
				<dd>
					<input type="text" name="membername" id="membername"', (isset($_REQUEST['membername']) ? ' value="'.$_REQUEST['membername'].'"' : ''), ' />
				</dd>';
}

function template_shop_inventory_search() {}

function template_shop_inventory_search_below()
{
	global $context, $txt;

	echo '
			</dl>
			<input class="button floatright" type="submit" value="', $txt['go'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>
	<script>
		var oAddMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAddMemberSuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSessionVar: \'', $context['session_var'], '\',
			sSuggestId: \'to_suggest\',
			sControlId: \'membername\',
			sSearchType: \'member\',
			sPostName: \'memberid\',
			sURLMask: \'action=profile;u=%item_id%\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
		});
	</script>';
}

function template_shop_inventory($inventory, $memID, $title = true)
{
	global $scripturl, $txt;

	// We got items?
	if (empty($inventory))
		return;

	$display_items = !empty($title) ? '<strong>' . $txt['Shop_posting_inventory'] . '</strong>:<br />' : '<br />';

	// Format items
	foreach($inventory as $item)
		$display_items .= Format::image($item['image'], $item['description']);

	// Show more
	$display_items .= '<br /><a href="'. $scripturl. '?action=shop;sa=invdisp;id='. $memID. '" onclick="return reqOverlayDiv(this.href, \''. $txt['Shop_posting_inventory']. '\', \'/icons/shop/top_inventories.png\');">'. $txt['Shop_posting_inventory_all']. '</a>';

	return $display_items;
}

function template_shop_inventory_extended()
{
	global $context, $txt, $modSettings, $settings;

	echo '
	<!DOCTYPE html>
	<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '', '>
		<head>
			<meta charset="', $context['character_set'], '">
			<meta name="robots" content="noindex">
			<title>', $context['page_title'], '</title>
			', template_css(), '
			', template_javascript(), '
		</head>
		<body id="shop_inventory_popup">
			<div class="windowbg">';

		// List the items...
		if (!empty($context['inventory_list']))
			foreach($context['inventory_list'] as $item)
				echo Format::image($item['image'], $item['description']);
		else
			echo $txt['Shop_inventory_other_no_items'];

		echo '
				<br class="clear">
				<a href="javascript:self.close();">', $txt['close_window'], '</a>
			</div>
		</body>
	</html>';
}