<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

global $scripturl, $modSettings;

// Main
$txt['Shop_no_goback'] = 'NO, go back!';
$txt['Shop_no_goback2'] = 'Cancel';

// Errors
$txt['cannot_shop_canAccess'] = ' You\'re not allowed to access the forum shop.';
$txt['cannot_shop_canBuy'] = ' You\'re not allowed to buy items.';
$txt['cannot_shop_playGames'] = ' You\'re not allowed to access to the Games Room.';
$txt['cannot_shop_canTrade'] = ' You\'re not allowed to access the trade center.';
$txt['cannot_shop_canBank'] = ' You\'re not allowed to access the bank.';
$txt['cannot_shop_canGift'] = ' You\'re not allowed to send gifts.';
$txt['cannot_shop_viewInventory'] = ' You\'re not allowed to view inventories.';
$txt['cannot_shop_viewStats'] = ' You\'re not allowed to view the shop stats.';
$txt['cannot_shop_canManage'] = ' You\'re not allowed to manage the shop.';
$txt['Shop_currently_disabled'] = 'Shop is currently disabled by admin! Please come back soon.';
$txt['Shop_currently_maintenance'] = 'Shop is in maintenance mode. Please come back later!';
$txt['Shop_currently_maintenance_warn'] = 'Shop is in Maintenance Mode. Only allowed users can currently access.';
$txt['Shop_currently_maintenance_warn_desc'] = 'You can turn off Maintenance Mode from the <a href="'. $scripturl. '?action=admin;area=shopsettings;sa=general">General Settings</a> area.';
$txt['Shop_not_allowedto_canAccess'] = 'Sorry, you are not allowed to access the Shop.';
$txt['Shop_currently_disabled_bank'] = 'The bank is currently disabled.';
$txt['Shop_currently_disabled_trade'] = 'The trade center is currently disabled.';
$txt['Shop_currently_disabled_gift'] = 'The gifts are currently disabled.';
$txt['Shop_currently_disabled_stats'] = 'The stats are currently disabled.';
$txt['Shop_currently_disabled_games'] = 'The games room is currently disabled.';

$txt['Shop_cannot_open_items'] = 'Cannot open ../Sources/Shop/Modules dir!';
$txt['Shop_cannot_open_images'] = 'Cannot open ../shop_items/items dir!';

$txt['Shop_item_notfound'] = 'Unable to find an item';
$txt['Shop_no_items'] = 'There are no items added!';
$txt['Shop_no_items_trade'] = 'No items being traded!';
$txt['Shop_item_error'] = 'ERROR: Could not create instance of \'%s\' item';
$txt['Shop_item_name_blank'] = 'You need to enter a name for the item.';
$txt['Shop_item_no_module'] = 'This item is missing it\'s module file. <br> Couldn\'t open the file: %s.php';
$txt['Shop_item_delete_error'] = 'Please choose something to delete!';
$txt['Shop_item_notown'] = 'What are you doing? You don\'t own that item, you cannot trade it!';
$txt['Shop_item_notown_use'] = 'What are you doing? You don\'t own that item, you cannot use it!';
$txt['Shop_item_notprice'] = 'You need to specify a price to the item.';
$txt['Shop_item_price_notnegative'] = 'Item price can not be zero or negative.';
$txt['Shop_item_alreadytraded'] = 'That item is already being traded.';
$txt['Shop_item_notbuy_own'] = 'You cannot buy your own items... Duh!';
$txt['Shop_item_currently_traded'] = 'That item is currently on trading';
$txt['Shop_item_limit_reached'] = 'Sorry, you already reached the limit of this item in your inventory.';

$txt['Shop_inventory_no_items'] = 'You don\'t have any items!';
$txt['Shop_inventory_no_owners'] = 'No users have this item on their inventory.';
$txt['Shop_inventory_other_no_items'] = 'This user doesn\'t have any items yet!';
$txt['Shop_inventory_useritems_nostock'] = 'Not enough stock of this item for the number of selected users';

$txt['Shop_module_notfound'] = 'Unable to find that module';
$txt['Shop_no_modules'] = 'There are no modules added!';
$txt['Shop_modules_only_admin'] = 'Only admins can upload modules';
$txt['Shop_modules_invalid'] = 'The uploaded file is not valid as a shop module, check that the file is correct or contact the author.';
$txt['Shop_module_notfound_admin'] = 'The module file for this item couldn\'t be found. All items using it have been disabled.<br>Please inform an admin, thanks.';

$txt['Shop_cat_notfound'] = 'Unable to find a category';
$txt['Shop_no_categories'] = 'There are no categories created!';
$txt['Shop_cat_name_blank'] = 'You need to enter a name for the category.';

$txt['Shop_buy_item_nostock'] = 'Sorry, we don\'t have any remaining \'%s\' items in stock';
$txt['Shop_buy_item_notenough'] = 'You don\'t have enough ' . (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' to buy the item \'%1$s\'. You need %2$s more to buy that item.';
$txt['Shop_buy_item_bought_error'] = 'You probably came here for a mistake, because you have not purchased any item before coming to this section.';

$txt['Shop_user_empty'] = 'Please type a member name.';
$txt['Shop_user_unable_tofind'] = 'Unable to find an user, please try again.';
$txt['Shop_inventory_usergroup_unable_tofind'] = 'Unable to find any members in the selected groups.';
$txt['Shop_inventory_groupcredits_nogroups'] = 'No groups were selected for the action.';
$txt['Shop_inventory_useritems_noitems'] = 'There are no items in the shop';

$txt['Shop_gift_no_item_found'] = 'You did not select any item to send.';
$txt['Shop_gift_no_items'] = 'You do not have any items to send';
$txt['Shop_gift_no_amount'] = 'You did not set any amount to send';
$txt['Shop_gift_not_yourself'] = 'You cannot send gifts to yourself, don\'t be selfish.';
$txt['Shop_gift_not_enough_pocket'] = 'You do not have enough ' . (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' in your pocket for this gift.';
$txt['Shop_gift_not_negative_or_zero'] = 'The amount to send cannot be negative nor zero.';

$txt['Shop_restock_error_noitems'] = 'You didn\'t select any items.';

$txt['Shop_games_invalid'] = 'Your pass for the games room has expired, please buy a new pass at the shop!';

$txt['Shop_error_import_data'] = 'We couldn\'t find any data to import or you already imported the data';
$txt['Shop_import_only_admin'] = 'Only Admins can perform the import/convert of data.';
$txt['Shop_import_empty'] = 'The data you are trying to import appears to be empty.';