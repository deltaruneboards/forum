<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

use Shop\Helper\Format;

function template_dashboard()
{
	global $context, $scripturl, $txt;

	// Welcome message for the admin.
	echo '
	<div id="admincenter">
		<div id="admin_main_section">';

	// Display the "live news" from smftricks.com.
	echo '
			<div id="live_news" class="floatleft">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['Shop_live_news'] , '
					</h3>
				</div>
				<div class="windowbg nopadding">
					<div id="smfAnnouncements">
						', $txt['Shop_news_connect'], '
					</div>
				</div>
			</div>';

	// Show the ST Shop version.
	echo '
			<div id="support_info" class="floatright">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['support_title'], '
					</h3>
				</div>
				<div class="windowbg nopadding">
					<div class="content padding">
						<div id="version_details">
							<strong>', $txt['Shop_version'], ':</strong> 
							<span id="yourVersion" style="white-space: nowrap;">', $context['Shop']['version'] , '</span><br />
							<strong>', $txt['Shop_current_version'], ':</strong> 
							<span id="lastestVersion" style="white-space: nowrap;"></span><br />
						</div>
					</div>
					<div class="title_bar">
						<h4 class="titlebg">', $txt['Shop_donate_title'], '</h4>
					</div>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="text-align: center">
						<br>
						<input type="hidden" name="cmd" value="_s-xclick" />
						<input type="hidden" name="hosted_button_id" value="YP3KXRJ2Q3ZJU" />
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal" />
						<img alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					</form>
				</div>
			</div>
			<br class="clear" /><br>
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">', $txt['Shop_main_credits'], '</span>
				</h3>
			</div>
			<div class="windowbg nopadding"><div class="padding">';

	// Print the credits array
	if (!empty($context['Shop']['credits']))
		foreach ($context['Shop']['credits'] as $c)
		{
			echo '
				<dl>
					<dt>
						<strong>', $c['name'], '</strong>
					</dt>';

			foreach ($c['users'] as $u)
				echo '
					<dd>
						<a href="', $u['site'] ,'">', $u['name'] ,'</a>', (isset($u['desc']) ? ' - <span class="smalltext">'. $u['desc']. '</span>' : ''), '
					</dd>';
			echo '
				</dl>';
		}
	echo '
			</div></div>
		</div>
		<br class="clear" />';

	foreach ($context[$context['admin_menu_name']]['sections'] as $area_id => $area)
	{
		// Only shop info...
		if ($area_id != 'shop')
			continue;

		echo '
		<fieldset id="group_', $area_id, '" class="windowbg admin_group">
			<legend>', $area['title'], '</legend>';

		foreach ($area['areas'] as $item_id => $item)
		{
			// Don't show home
			if ($item_id == 'shopinfo')
				continue;

			$url = isset($item['url']) ? $item['url'] : $scripturl . '?action=admin;area=' . $item_id . (!empty($context[$context['admin_menu_name']]['extra_parameters']) ? $context[$context['admin_menu_name']]['extra_parameters'] : '');

			if (!empty($item['icon_file']))
				echo '
				<a href="', $url, '" class="admin_group', !empty($item['inactive']) ? ' inactive' : '', '"><img class="large_admin_menu_icon_file" src="', $item['icon_file'], '" alt="">', $item['label'], '</a>';
			else
				echo '
				<a href="', $url, '"><span class="large_', $item['icon_class'], !empty($item['inactive']) ? ' inactive' : '', '"></span>', $item['label'], '</a>';
		}
		echo '
		</fieldset>';
	}
	echo '
	</div>';
}

function template_items_add()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="windowbg">
		<form method="post" action="' . $scripturl . '?action=admin;area=shopitems;sa=add2">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<dl class="settings">
				<dt>
					', $txt['Shop_item_usemodule'], ':<br>
					<span class="smalltext">', $txt['Shop_item_not_module'], '</span>
				</dt>
				<dd>
					<input class="input_check" type="checkbox" name="module" value="1" onclick="document.getElementById(\'SelectModule\').style.display = this.checked ? \'block\' : \'none\';"', (empty($context['shop_modules']) ? ' disabled="disabled" ' : ''), ' />
				</dd>
			</dl>';

		// Do we actually have any modules in the shop?
		if (!empty($context['shop_modules']))
		{
			echo '
			<fieldset id="SelectModule" style="display: none; border: none; margin: -10px -8px -25px; width: 0;">
				<select name="item">
					<optgroup label="', $txt['Shop_item_module_select'], '">';

				// For every module that's possible to add...
				foreach ($context['shop_modules'] as $module)
				echo '
						<option value="', $module['id'], '">', $module['name'], ' by ', $module['author'], ' &lt;', $module['email'], '&gt;</option>';

			echo '
					</optgroup>
				</select>
			</fieldset>';
		}
		echo '
			<input class="button" type="submit" value="', $txt['Shop_items_add'], '" />
		</form>
	</div>';
}

function template_items()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="windowbg">
		<form method="post" action="', $scripturl, '?action=admin;area=shopitems;sa=save" name="Shopitems">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			', isset($_REQUEST['id']) && !empty($context['shop_item']['itemid']) ? '<input type="hidden" name="id" value="'.$context['shop_item']['itemid'].'">' : '', '
			<dl class="settings">
				<dt>
					<a id="setting_itemname"></a>
					<span><label for="itemname">', $txt['Shop_item_name'], ':</label></span>
				</dt>
				<dd>
					<input name="itemname" id="itemname" type="text" value="', !empty($context['shop_item']['name']) ? $context['shop_item']['name'] : '', '" style="width: 100%" />
				</dd>
				<dt>
					<a id="setting_itemdesc"></a>
					<span><label for="itemdesc">', $txt['Shop_item_description'], ':</label></span>
				</dt>
				<dd>
					<textarea name="itemdesc" id="itemdesc" rows="2" style="width: 100%">', !empty($context['shop_item']['description']) ? $context['shop_item']['description'] : '', '</textarea>
				</dd>
				<dt>
					<a id="setting_itemprice"></a>
					<span><label for="itemprice">', $txt['Shop_item_price'], ':</label></span>
				</dt>
				<dd>
					', Format::cash('<input name="itemprice" id="itemprice" type="number" min="0" value="'.(!empty($context['shop_item']['price']) ? $context['shop_item']['price'] : 0). '" />'), '
				</dd>
				<dt>
					<a id="setting_itemstatus"></a>
					<span><label for="itemstatus">', $txt['Shop_item_enable'], ':</label></span>
				</dt>
				<dd>
					<input class="input_check" type="checkbox" name="itemstatus" id="itemstatus" value="1"', (!empty($context['shop_item']['status']) ? ' checked' : ''), '/>
				</dd>
			</dl>
			<dl class="settings">
				<dt>
					<a id="setting_itemstock"></a>
					<span><label for="itemstock">', $txt['Shop_item_stock'], ':</label></span>
				</dt>
				<dd>
					<input name="itemstock" id="itemstock" type="number" min="0" value="', !empty($context['shop_item']['stock']) ? $context['shop_item']['stock'] : 0, '" />
				</dd>
				<dt>
					<a id="setting_itemlimit"></a>
					<span><label for="itemlimit">', $txt['Shop_item_limit'], ':</label></span><br />
					<span class="smalltext">', $txt['Shop_item_limit_desc'], '</span>
				</dt>
				<dd>
					<input name="itemlimit" id="itemlimit" type="number" min="0" value="', !empty($context['shop_item']['itemlimit']) ? $context['shop_item']['itemlimit'] : 0, '" />
				</dd>
			</dl>
			<dl class="settings">
				<dt>
					<a id="setting_cat"></a>
					<span><label for="cat">', $txt['Shop_item_category'], ':</label></span>
				</dt>
				<dd>
					<select name="cat" id="cat">
						<option value="0">', $txt['Shop_item_uncategorized'], '</option>';

					if (!empty($context['shop_categories_list']))
					{
						echo '
						<optgroup label="', $txt['Shop_item_category_select'], '">';

						// List the categories
						foreach ($context['shop_categories_list'] as $category)
						echo '
							<option value="', $category['catid'], '"', ((!empty($context['shop_item']['catid']) && $context['shop_item']['catid'] == $category['catid']) ? ' selected="selected"' : ''), '>', $category['name'], '</option>';

						echo '
						</optgroup>';
					}

				echo '
					</select>
				</dd>
				<dt>
					<a id="setting_icon"></a>
					<span><label>', $txt['Shop_item_image'], ':</label></span>
				</dt>
				<dd>
					<script>
						function show_image(){
							if (document.Shopitems.icon.value !== "none") { var image_url = "', $context['items_url'], '" + document.Shopitems.icon.value; document.images["icon"].src = image_url; }
							else { document.images["icon"].src = "', $context['items_url'], 'blank.gif"; }
						}
					</script>
					<select name="icon" onchange="show_image()">
						<optgroup label="', $txt['Shop_item_image_select'], '">
							<option value="blank.gif"', ((empty($context['shop_item']['image']) || $context['shop_item']['image'] == 'blank.gif') ? ' selected="selected"' : ''), '>', $txt['Shop_items_none_select'], '</option>';

						// List the images
						foreach ($context['shop_images_list'] as $image)
						echo '
							<option value="', $image, '"', ((!empty($context['shop_item']['image']) && $context['shop_item']['image'] == $image) ? ' selected="selected"' : ''), '>', $image, '</option>';

					echo '
						</optgroup>
					</select>
					&nbsp;&nbsp;', Format::image(!empty($context['shop_item']['image']) ? $context['shop_item']['image'] : 'blank.gif', '', 'icon'), '<br />
					<span class="smalltext">', $txt['Shop_item_notice'], '</span>
				</dd>
			</dl>';

		if (!empty($context['shop_item']['addInputEditable']) && isset($context['shop_item']['addInput']) && !empty($context['shop_item']['addInput']))
		{
			echo '
			<a id="addinput"></a>
			<div class="title_bar">
				<h3 class="titlebg">
					', $txt['Shop_item_additional'], '
				</h3>
			</div>
			<div class="information">
				', $txt['Shop_item_description_match'], '
			</div>
			', $context['shop_item']['addInput'];
		}

		if (isset($context['shop_item']['can_use_item']) && !empty($context['shop_item']['can_use_item']))
		{
			echo '
			<hr>
			<dl class="settings">
				<dt>
					<a id="setting_itemdelete"></a>
					<span><label for="itemdelete">', $txt['Shop_item_delete_after'], ':</label></span>
				</dt>
				<dd>
					<input class="input_check" type="checkbox" name="itemdelete" id="itemdelete" ', (!empty($context['shop_item']['delete_after_use']) ? ' checked' : ''), '/>
				</dd>
			</dl>';
		}

		// Adding?
		if ($_REQUEST['sa'] == 'add2')
		{
			echo '
			<input type="hidden" name="require_input" value="', !empty($context['shop_item']['require_input']) ? $context['shop_item']['require_input'] : '', '" />
			<input type="hidden" name="can_use_item" value="', !empty($context['shop_item']['can_use_item']) ? $context['shop_item']['can_use_item'] : '', '" />
			<input type="hidden" name="module" value="', !empty($context['shop_item']['module']) ? $context['shop_item']['module'] : '', '" />';
		}

		echo '
			<input class="button" type="submit" value="', $txt['save'], '" />
			', ($_REQUEST['sa'] == 'edit' ? '
			<input class="button" type="button" value="'.$txt['Shop_no_goback2'].'" onclick="window.location=\''.$scripturl.'?action=admin;area=shopitems\'" />' : ''), '
		</form>
	</div>';
}

function template_delete()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="windowbg">
		<form method="post" action="', $scripturl, '?action=admin;area=', $_REQUEST['area'], ';sa=delete2">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<h2>', $txt['Shop_'. str_replace('shop', '', $_REQUEST['area']). '_delete_sure'], '</h2>
			<span class="smalltext">', $context['delete_description'], '</span>
			<hr />
			<ul>';

		// Loop through each item chosen to delete...
		foreach ($context['shop_delete'] as $del)
		{
			echo '
				<li class="windowbg">

					<input type="hidden" name="delete[]" value="', $del['itemid'], '" />
					', !empty($del['image']) ? Format::image($del['image']) : '', '&nbsp;&nbsp;&nbsp;', $del['name'], '&nbsp;&nbsp;&nbsp;';

				// Modules
				if (!empty($del['file']))
					echo '
					', $del['file'].'.php)', '
					<input type="hidden" name="files[]" value="', $del['file'], '" />';

				// Categories
				if (!empty($del['category']))
					echo '
					<br/>
					<input type="checkbox"name="deleteitems[]" value="', $del['catid'], '"  />&nbsp;
					<span class="smalltext">', $txt['Shop_cat_delete_also'], '</span><br />';

			echo '
				</li>';
		}
		echo '
			</ul>
			<input class="button" type="submit" value="', $txt['delete'], '" />
			<input class="button" type="button" value="', $txt['Shop_no_goback'], '" onclick="window.location=\'', $scripturl, '?action=admin;area=', $_REQUEST['area'], '\'" />
		</form>
	</div>';
}

function template_upload()
{
	global $txt, $scripturl, $context;
	
	if (isset($_REQUEST['success']) || isset($_REQUEST['error']))
		echo '
		<div class="', (isset($_REQUEST['success']) ? 'infobox' : 'errorbox'), '">
			', $txt['Shop_' . ($_REQUEST['area'] == 'shopitems' ? 'item' : 'module') . '_upload_' . (isset($_REQUEST['success']) ? 'success' : 'error') ], '
		</div>';

	echo '
		<div class="windowbg">
			<form method="post" action="' . $scripturl . '?action=admin;area=', $_REQUEST['area'], ';sa=upload2" name="UploadItem" enctype="multipart/form-data">
				<input type="file" name="newitem" id="newitem" value="newitem" size="40" class="input_file">
				<input type="submit" class="button" value="', $txt['save'], '" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</form>
		</div>';
}

function template_categories()
{
	global $context, $txt, $scripturl, $modSettings, $boardurl;

	echo '
	<div class="windowbg">
		<form method="post" action="', $scripturl, '?action=admin;area=shopcategories;sa=save" name="Shopcategories">
		', ($_REQUEST['sa'] == 'edit' ? '
			<input type="hidden" name="id" value="'.$context['shop_category']['catid'].'" />' : ''), '
			<dl class="settings">
				<dt>
					<a id="setting_catname"></a>
					<span><label for="catname">', $txt['Shop_item_name'], ':</label></span>
				</dt>
				<dd>
					<input name="catname" id="catname" type="text" value="', !empty($context['shop_category']['name']) ? $context['shop_category']['name'] : '', '" style="width: 100%" />
				</dd>

				<dt>
					<a id="setting_catdesc"></a>
					<span><label for="catdesc">', $txt['Shop_item_description'], ':</label></span>
				</dt>
				<dd>
					<textarea name="catdesc" id="catdesc"  rows="2" style="width: 100%">', !empty($context['shop_category']['description']) ? $context['shop_category']['description'] : '', '</textarea>
				</dd>
			</dl>
			<dl class="settings">
				<dt>
					<a id="setting_caticon"></a>
					<span><label for="caticon">', $txt['Shop_item_image'], ':</label></span>
				</dt>
				<dd>
					<script>
						function show_image(){
							if (document.Shopcategories.caticon.value !== "none") { var image_url = "', $context['items_url'], '" + document.Shopcategories.caticon.value; document.images["caticon"].src = image_url; }
							else { document.images["caticon"].src = "', $context['items_url'], 'blank.gif"; }
						}
					</script>
					<select name="caticon" id="caticon" onchange="show_image()">
						<optgroup label="', $txt['Shop_item_image_select'], '">
							<option value="blank.gif"', ((empty($context['shop_category']['image']) || $context['shop_category']['image'] == 'blank.gif') ? ' selected="selected"' : ''), '>', $txt['Shop_items_none_select'], '</option>';

						// List the images
						foreach ($context['shop_images_list'] as $image)
						echo '
							<option value="', $image, '"', ((!empty($context['shop_category']['image']) && $context['shop_category']['image'] == $image) ? ' selected="selected"' : ''), '>', $image, '</option>';

					echo '
						</optgroup>
					</select>
					&nbsp;&nbsp;', Format::image(!empty($context['shop_category']['image']) ? $context['shop_category']['image'] : 'blank.gif', '', 'caticon'), '<br />
					<span class="smalltext">', $txt['Shop_item_notice'], '</span>
				</dd>
			</dl>
			<input class="button floatleft" type="submit" value="', $txt['save'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>';
}

function template_send_above()
{
	global $txt, $scripturl;

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : 'usercredits';

	// Updated message
	if (isset($_REQUEST['updated']))
		echo '
	<div class="infobox">
		', $txt['Shop_inventory_'.$_REQUEST['sa'].'_success'], '
	</div>';
	// Success message
	elseif (isset($_REQUEST['success']))
		echo '
	<div class="infobox">
		', $txt['Shop_inventory_'.$_REQUEST['sa'].'_success'], '
	</div>';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['Shop_inventory_'.$_REQUEST['sa']], '
		</h3>
	</div>
	<div class="windowbg">
		<form method="post" action="', $scripturl,'?action=admin;area=shopinventory;sa='.$_REQUEST['sa'].'2">';
}

function template_send_credits()
{
	global $context, $txt;

	echo '
			<dl class="settings">
				<dt>
					', $txt['Shop_inventory_member_name'], '<br/>
					<span class="smalltext">', $txt['Shop_inventory_members_desc'], '</span>
				</dt>
				<dd>
					<input type="text" name="membername" id="membername" />
					<div id="membernameItemContainer"></div>
				</dd>
				<dt>
					', $txt['Shop_bank_amount'], '
				</dt>
				<dd>
					', Format::cash('<input  type="number" min="0" name="amount" id="amount" value="0" />'), '
				</dd>
			</dl>
			<input class="button" type="submit" value="', $txt['go'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';
}

function template_groups()
{
	global $context, $txt;
	
	echo '
			<dl class="settings">
				<dt>
					', $txt['Shop_inventory_groupcredits_membergroup'], ':
				</dt>
				<dd>';

			// Loop through all available membergroups
			foreach	($context['shop_usergroups'] as $group)
				echo '
					<input type="checkbox" name="usergroup[]" value="', $group['id'], '" />', $group['name'], '<br />';

			echo '
				</dd>
				<dt>
					', $txt['Shop_inventory_groupcredits_action'], '
				</dt>
				<dd>
					<label>
						<input class="input_radio" type="radio" name="m_action" value="add" checked />', $txt['Shop_inventory_groupcredits_add'], '
					</label><br />
					<label>
						<input class="input_radio" type="radio" name="m_action" value="sub" />', $txt['Shop_inventory_groupcredits_substract'], '
					</label>
				</dd>
				<dt>
					', $txt['Shop_logs_amount'], '
				</dt>
				<dd>
					', Format::cash('<input  type="number" min="0" name="amount" id="amount" value="0" size="10" />'), '
				</dd>
			</dl>
			<input class="button" type="submit" value="', $txt['go'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';
}

function template_send_items()
{
	global $context, $txt;

	echo '
			<dl class="settings">
				<dt>
					', $txt['Shop_inventory_member_name'], '<br/>
					<span class="smalltext">', $txt['Shop_inventory_members_desc'], '</span>
				</dt>
				<dd>
					<input type="text" name="membername" id="membername" />
					<div id="membernameItemContainer"></div>
				</dd>
				<dt>
					', $txt['Shop_gift_item_select'], '
				</dt>
				<dd>';
					
				// List the items
				if (!empty($context['shop_items_list']))
				{
					echo '
					<select name="item" id="item">
						<optgroup label="', $txt['Shop_gift_item_select'], '">';

						// List the categories
						foreach ($context['shop_items_list'] as $item)
							echo '
							<option value="', $item['itemid'], '">', $item['name'], '</option>';
					echo '
						</optgroup>
					</select>';
				}
				else
					echo '
					<strong>', $txt['Shop_inventory_useritems_noitems'], '</strong>';

			echo '
				</dd>
			</dl>
			<input class="button" type="submit" value="', $txt['go'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';
}

function template_restock()
{
	global $context, $txt;

	echo '
			<dl class="settings">
				<dt>
					', $txt['Shop_inventory_restock_what'], ':
				</dt>
				<dd>
					<label><input class="input_radio" type="radio" name="whatitems" value="all" checked onclick="document.getElementById(\'SelectItems\').style.display = this.checked ? \'none\' : \'block\';" />', $txt['Shop_inventory_restock_all'], '</label><br />
					<label><input class="input_radio" type="radio" name="whatitems" value="selected" onclick="document.getElementById(\'SelectItems\').style.display = this.checked ? \'block\' : \'none\';" />', $txt['Shop_inventory_restock_selected'], '</label>
				</dd>
			</dl>
			<dl class="settings" id="SelectItems" style="display: none;">
				<dt>
					', $txt['Shop_inventory_restock_select_items'], '
				</dt>
				<dd>
					<div class="profile_user_links">
						<ol>';

					// For every module that's possible to add...
					foreach ($context['shop_items_list'] as $item)
						echo '
							<li><input type="checkbox" name="restockitem[]" value="', $item['itemid'], '" /> ', Format::image($item['image']), ' ', $item['name'], '</li>';

						echo '
						</ol>
					</div>
				</dd>
			</dl>
			<dl class="settings">
				<dt>
					', $txt['Shop_inventory_restock_lessthan'], ':<br />
					<span class="smalltext">', $txt['Shop_inventory_restock_lessthan_desc'], '</span>
				</dt>
				<dd>
					<input type="number" min="0" name="stock" id="stock" value="5" size="10" />
				</dd>
				<dt>
					', $txt['Shop_inventory_restock_amount'], ':<br/>
					<span class="smalltext">', $txt['Shop_inventory_restock_amount_desc'], '</span>
				</dt>
				<dd>
					<input type="number" min="1" name="add" id="add" value="10" size="10" />
				</dd>
			</dl>
			<input class="button floatleft" type="submit" value="', $txt['go'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';
}

function template_send_below()
{
	global $context, $txt;

	echo '
		</form>
	</div>';

	// Don't mess up the other script
	if ($_REQUEST['sa'] != 'search')
		echo '
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
				bItemList: true,
				sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
				sItemListContainerId: \'membernameItemContainer\'
			});
		</script>';
}

function template_import()
{
	global $context, $txt, $modSettings, $scripturl;

	echo '
	<div class="windowbg">
		<div class="title_bar">
			<h4 class="titlebg">
				', $txt['Shop_maint_convert'], '
			</h4>
		</div>
		<div class="information">
			', $txt['Shop_maint_convert_warn'], '
		</div>
	
		<div class="', (!empty($modSettings['Shop_importer_success']) || empty($context['shop_convert_data']) ? 'errorbox' : 'infobox') , '">
			', (!empty($modSettings['Shop_importer_success']) || empty($context['shop_convert_data']) ? $txt['Shop_error_import_data'] : $txt['Shop_import_from_' . $context['shop_convert_from']]) , '
		</div>';

	// Move forward
	if (empty($modSettings['Shop_importer_success']) && !empty($context['shop_convert_data']))
		echo '
		<form method="post" action="', $scripturl,'?action=admin;area=shopmaintenance;sa=importdo">
			<input type="hidden" name="convert_from" value="', $context['shop_convert_from'], '">
			<input class="button floatright" type="submit" value="', $txt['Shop_maint_import_go'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>';

	echo '
	</div>';
}

function template_import_results()
{
	global $context, $txt;

	echo '
	<div class="windowbg">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['Shop_maint_import_results'], '
			</h3>
		</div>
		<div class="half_content">
			<div class="title_bar">
				<h4 class="titlebg">
					', $txt['Shop_maint_import_found'], '
				</h4>
			</div>
			<dl class="settings">
				<dt>
					', $txt['Shop_import_found_items'], '
				</dt>
				<dd>
					', $context['shop_found']['items_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_found_cats'], '
				</dt>
				<dd>
					', $context['shop_found']['cats_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_found_inventories'], '
				</dt>
				<dd>
					', $context['shop_found']['inventory_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_found_modules'], '
				</dt>
				<dd>
					', $context['shop_found']['modules_total'], '
				</dd>
			</dl>
			<dl class="settings">
				<dt>
					', $txt['Shop_import_found_log_buy'], '
				</dt>
				<dd>
					', $context['shop_found']['logbuy_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_found_log_bank'], '
				</dt>
				<dd>
					', $context['shop_found']['logbank_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_found_log_gift'], '
				</dt>
				<dd>
					', $context['shop_found']['loggift_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_found_log_games'], '
				</dt>
				<dd>
					', $context['shop_found']['loggames_total'], '
				</dd>
			</dl>
		</div>
		<div class="half_content">
			<div class="title_bar">
				<h4 class="titlebg">
					', $txt['Shop_maint_import_imported'], '
				</h4>
			</div>
			<dl class="settings">
				<dt>
					', $txt['Shop_import_imported_items'], '
				</dt>
				<dd>
					', $context['shop_imported']['items_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_cats'], '
				</dt>
				<dd>
					', $context['shop_imported']['cats_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_inventories'], '
				</dt>
				<dd>
					', $context['shop_imported']['inventory_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_cash'], '
				</dt>
				<dd>
					', $context['shop_imported']['cash_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_gamespass'], '
				</dt>
				<dd>
					', $context['shop_imported']['gamespass_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_board'], '
				</dt>
				<dd>
					', $context['shop_imported']['boards_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_settings'], '
				</dt>
				<dd>
					', $context['shop_imported']['settings_total'], '
				</dd>
			</dl>
			<dl class="settings">
				<dt>
					', $txt['Shop_import_imported_log_buy'], '
				</dt>
				<dd>
					', $context['shop_imported']['logbuy_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_log_bank'], '
				</dt>
				<dd>
					', $context['shop_imported']['logbank_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_log_gift'], '
				</dt>
				<dd>
					', $context['shop_imported']['loggift_total'], '
				</dd>
				<dt>
					', $txt['Shop_import_imported_log_games'], '
				</dt>
				<dd>
					', $context['shop_imported']['loggames_total'], '
				</dd>
			</dl>
		</div>
	</div>';
}