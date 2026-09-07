<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

global  $scripturl, $modSettings;

// Main admin
$txt['Shop_admin_button'] = 'Shop Admin';
$txt['Shop_live_news'] = 'Live from smftricks.com';
$txt['Shop_live_error'] = 'Couldn\'t connect to smftricks.com';
$txt['Shop_version'] = 'Shop version';
$txt['Shop_current_version'] = 'Current version';
$txt['Shop_version_outdated'] = 'Your shop version is not up to date!';
$txt['Shop_donate_title'] = 'Donate to the author';
$txt['Shop_tab_info'] = 'Shop Information';
$txt['Shop_tab_info_desc'] = 'Hello %s, welcome to your ST Shop Admin panel. From here you can edit the shop settings, add items, modules, categories and set the reward in the games room.<br> Additionally you can check the shop logs adn use the maintenance tools to restock items or send items/money to specific users, or remove items from their inventory.';
$txt['Shop_main_credits'] = 'Credits';
$txt['Shop_news_connect'] = 'Attempting to get ST News...';

// Credits
$txt['Shop_dash_devs'] = 'Developers';
$txt['Shop_dash_contributors'] = 'Contributors';
$txt['Shop_dash_icons'] = 'Icons';
$txt['Shop_dash_thanks'] = 'Special Thanks';

// Shop settings
$txt['Shop_settings_general'] = 'General';
$txt['Shop_settings_general_desc'] = 'In this section you can enable or disable the general shop features.';
$txt['Shop_tab_settings'] = 'Shop Settings';
$txt['Shop_enable_shop'] = 'Enable Shop?';
$txt['Shop_enable_shop_desc'] = 'If the shop is not enabled, the shop page won\'t be visible, but core features will still work normally.';
$txt['Shop_enable_games'] = 'Enable Games Room?';
$txt['Shop_enable_bank'] = 'Enable Bank?';
$txt['Shop_enable_gift'] = 'Enable Send Gifts and Credits?';
$txt['Shop_enable_trade'] = 'Enable Trade?';
$txt['Shop_enable_stats'] = 'Enable Stats?';
$txt['Shop_stats_refresh'] = 'Time for refreshing stats';
$txt['Shop_stats_refresh_desc'] = 'The time in seconds that the forum will take to refresh your shop stats stored in cache. This helps you to prevent overloads of data and help to load information faster. <br/>By default is set to <i>900</i> (15 mins).';
$txt['Shop_enable_maintenance'] = 'Enable Maintenance?';
$txt['Shop_enable_maintenance_desc'] = 'This will put the Shop in maintenance, only users who are allowed to manage the Shop have access.';

// Integrations
$txt['Shop_settings_integrations'] = 'Integrations';
$txt['Shop_settings_integrations_desc'] = 'In this section you can change settings for different integrations with other mods or addons';
$txt['Shop_integration_settings_desc'] = '<a href="%1$s">%2$s</a> mod is required.';

// Permissions
$txt['Shop_settings_permissions'] = 'Permissions';
$txt['Shop_settings_permissions_desc'] = 'In this section you can handle the permissions in a very easy way.';
$txt['permissiongroup_shop'] = 'Shop Permissions';
$txt['permissionname_shop_canAccess'] = 'Access Forum Shop';
$txt['groups_shop_canAccess'] = 'Access Forum Shop';
$txt['permissionhelp_shop_canAccess'] = 'If the user can access the shop.';
$txt['permissionname_shop_canBuy'] = 'Buy Items';
$txt['groups_shop_canBuy'] = 'Buy Items';
$txt['permissionhelp_shop_canBuy'] = 'If the user is allowed to buy items.';
$txt['permissionname_shop_playGames'] = 'Access the Games Room';
$txt['groups_shop_playGames'] = 'Access the Games Room';
$txt['permissionhelp_shop_playGames'] = 'If the user can access to the Games Room.';
$txt['permissionname_shop_canTrade'] = 'Access the Trade Center';
$txt['groups_shop_canTrade'] = 'Access the Trade Center';
$txt['permissionhelp_shop_canTrade'] = 'If the user have access to the trade center in the shop.';
$txt['permissionname_shop_canBank'] = 'Can use the Bank';
$txt['groups_shop_canBank'] = 'Can use the Bank';
$txt['permissionhelp_shop_canBank'] = 'If the user is allowed to access the shop bank.';
$txt['permissionname_shop_canGift'] = 'Send Gifts';
$txt['groups_shop_canGift'] = 'Send Gifts';
$txt['permissionhelp_shop_canGift'] = 'If the user is allowed to send gifts and/or money';
$txt['permissionname_shop_viewInventory'] = 'View Inventory';
$txt['groups_shop_viewInventory'] = 'View Inventory';
$txt['permissionname_shop_viewInventory_own'] = 'View Own Inventory';
$txt['permissionname_shop_viewInventory_any'] = 'View Other Users Inventory';
$txt['permissionhelp_shop_viewInventory'] = 'If the user is allowed to view their inventory and other users inventory.';
$txt['permissionname_shop_viewStats'] = 'View Stats';
$txt['groups_shop_viewStats'] = 'View Stats';
$txt['permissionhelp_shop_viewStats'] = 'If the user is allowed to view the shop stats page.';
$txt['permissionname_shop_canManage'] = 'Manage the Shop';
$txt['groups_shop_canManage'] = 'Manage the Shop';
$txt['permissionhelp_shop_canManage'] = 'This permission defines if the user is allowed to manage items, categories and logs of the Shop.';

// Credits settings
$txt['Shop_settings_credits'] = 'Shop Credits';
$txt['Shop_settings_credits_desc'] = 'Here you will be able to set the credits settings for the shop.';
$txt['Shop_credits_register'] = 'Credits upon registration';
$txt['Shop_credits_topic'] = 'Credits received per new topic';
$txt['Shop_credits_post'] = 'Credits received per new post';
$txt['Shop_credits_word'] = 'Credits received per word';
$txt['Shop_credits_likes_post'] = 'Credits received per like on post';
$txt['Shop_credits_character'] = 'Credits received per character';
$txt['Shop_credits_limit'] = 'Limit of credits received in a post';
$txt['Shop_credits_limit_desc'] = 'If you have set an amount per word or characters, here you can set the limit of credits that the user can receive.';
$txt['Shop_bank_settings'] = 'Bank settings';
$txt['Shop_bank_interest'] = 'Bank interest';
$txt['Shop_bank_interest_desc'] = 'Set the percent the users will receive per day.';
$txt['Shop_bank_interest_yesterday'] = 'Only logged interest';
$txt['Shop_bank_interest_yesterday_desc'] = 'With this enabled, the user won\'t receive bank interest if they have not logged in during the last 24 hours.';
$txt['Shop_bank_withdrawal_fee'] = 'Withdrawal fee';
$txt['Shop_bank_deposit_fee'] = 'Deposit fee';
$txt['Shop_bank_max_min_desc'] = 'Set 0 for no limits.';
$txt['Shop_bank_withdrawal_min'] = 'Minimum withdrawal';
$txt['Shop_bank_withdrawal_max'] = 'Maximum withdrawal';
$txt['Shop_bank_deposit_min'] = 'Minimum deposit';
$txt['Shop_bank_deposit_max'] = 'Maximum deposit';
$txt['Shop_credits_general_settings'] = 'General settings';
$txt['Shop_credits_prefix'] = 'Credits prefix';
$txt['Shop_credits_prefix_desc'] = 'The prefix is going to appear BEFORE the amount of credits.';
$txt['Shop_credits_suffix'] = 'Credits suffix';
$txt['Shop_credits_suffix_desc'] = 'The suffix is going to appear AFTER the amount of credits.';
$txt['Shop_images_width'] = 'Images width';
$txt['Shop_images_height'] = 'Images height';
$txt['Shop_items_perpage'] = 'How many items to display per page?';
$txt['Shop_items_perpage_desc'] = 'This will affect every list of the shop, including admin.';
$txt['Shop_items_trade_fee'] = 'Trade fee percent';
$txt['Shop_items_trade_fee_desc'] = 'This will take away a percentage of the trade from sellers.';
$txt['Shop_credits_count'] = 'Enable Shop credits';
$txt['Shop_credits_count_desc'] = 'This settings will allow users to receive credits for topics and posts on this board.';
$txt['Shop_credits_enable_bonus'] = 'Enable bonus';
$txt['Shop_credits_enable_bonus_desc'] = 'This will enable in this board the extra credits per word/character.';
$txt['Shop_credits_custom_override'] = 'This will override the main settings for credits.';
$txt['Shop_credits_viewing_who'] = 'Viewing credits of %s';

// Profile
$txt['Shop_settings_profile'] = 'Posting and Profile';
$txt['Shop_settings_profile_desc'] = 'Here you can find all the settings related to the post display and forum profile.';
$txt['Shop_items_none_select'] = '[NONE]';
$txt['Shop_display_pocket']  = 'Display credits in pocket';
$txt['Shop_display_pocket_placement']  = 'Pocket credits placement (posts)';
$txt['Shop_display_pocket_placement_desc']  = 'If it\'s enabled in posts you can choose different placements.';
$txt['Shop_display_bank']  = 'Display credits in bank';
$txt['Shop_display_bank_placement']  = 'Bank credits placement (posts)';
$txt['Shop_display_bank_placement_desc']  = 'If it\'s enabled in posts you can choose different placements.';
$txt['Shop_display_post']  = 'Only in posts';
$txt['Shop_display_profile']  = 'Only in profile';
$txt['Shop_display_both']  = 'Both (profile and posts)';
$txt['Shop_inventory_enable']  = 'Enable mini-inventory';
$txt['Shop_inventory_items_num'] = 'Max of items to display';
$txt['Shop_inventory_placement']  = 'Items inventory placement (posts)';
$txt['Shop_inventory_allow_hide'] = 'Allow users to hide inventory';
$txt['Shop_inventory_allow_hide_desc'] = 'User will be able to hide their inventory on posts and profile.';
$txt['Shop_inventory_show_same_once'] = 'Show the same item only once';

// Notifications
$txt['Shop_settings_notifications'] = 'Notifications';
$txt['Shop_settings_notifications_desc'] = 'In this area to you activate alerts for the users on certain actions.';
$txt['Shop_noty_credits'] = 'Notify ' . (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' received';
$txt['Shop_noty_credits_desc'] = 'Send an alert when the user received credits or and item as a gift or admin action.';
$txt['Shop_noty_items'] = 'Notify item/gift received';
$txt['Shop_noty_items_desc'] = 'Send an alert when the user receives an item as a gift or admin action.';
$txt['Shop_noty_trade'] = 'Notify successfull trade';
$txt['Shop_noty_trade_desc'] = 'Send an alert to the seller when the item on trade is purchased by someone.';
$txt['Shop_noty_items_desc'] = 'Send an alert when another user robs credits from their pocket.';

// Admin Items
$txt['Shop_tab_items'] = 'Shop Items';
$txt['Shop_tab_items_desc'] = 'In this section you can browse all the items added to the shop, edit them and add new items.';
$txt['Shop_items_list'] = 'Items list';
$txt['Shop_items_add'] = 'Add item';
$txt['Shop_items_add_desc'] = 'Here you can specify either use a premade item (module) or to use the default non-usable item.';
$txt['Shop_items_add_desc2'] = 'You are adding the item \'%s\' to your shop.';
$txt['Shop_items_add_desc_default'] = 'You are adding a \'Default\' item to your shop.';
$txt['Shop_items_added'] = 'The item was added successfully.';
$txt['Shop_items_delete'] = 'Delete items';
$txt['Shop_items_delete_desc'] = 'The following items will be deleted if you continue, make sure you selected them correctly.';
$txt['Shop_items_delete_sure'] = 'Are you sure you want to delete these items?';
$txt['Shop_item_delete_also'] = 'This will also delete those items from the user\'s inventory and every log.';
$txt['Shop_items_deleted'] = 'Selected items were deleted successfully.';
$txt['Shop_item_delete_after'] = 'Delete item after use?';
$txt['Shop_items_upload'] = 'Upload items';
$txt['Shop_items_upload_desc'] = 'In this section you can easily upload new items to your shop images folder.';
$txt['Shop_item_function'] = 'Function';
$txt['Shop_item_module'] = 'Module';
$txt['Shop_item_module_select'] = 'Select a module';
$txt['Shop_item_not_module'] = 'If you don\'t want to use a module, the item will be based on a \'Default\' item with no functions or features.';
$txt['Shop_item_usemodule'] = 'Want to use an item module?';
$txt['Shop_items_edit'] = 'Edit item';
$txt['Shop_items_edit_desc'] = 'You are currently editing the the item \'%s\'.';
$txt['Shop_items_updated'] = 'The item was updated successfully.';
$txt['Shop_item_modify'] = 'Modify';
$txt['Shop_item_save'] = 'Save item';
$txt['Shop_item_status'] = 'Status';
$txt['Shop_item_enable'] = 'Enable item?';
$txt['Shop_item_description_match'] = 'Make sure to change the Name and Description above to reflect the values that are on this area.';
$txt['Shop_item_additional'] = 'Additional item inputs';
$txt['Shop_item_limit_desc'] = 'Set the limit of items that an user can buy/have/carry. Set 0 for no limit.<br /><i>This won\'t take effect on previous bought items (if you\'re editing the item).</i>';
$txt['Shop_item_notice'] = 'Images are stored in ../shop_items/items/. Feel free to add more images!';
$txt['Shop_items_na'] = 'N/A';
$txt['Shop_item_remove_inventory'] = 'Remove items from inventory';
$txt['Shop_item_upload_success'] = 'The item was uploaded successfully.';
$txt['Shop_item_upload_error'] = 'There was an error and the item was not uploaded.';
$txt['Shop_item_uncategorized'] = 'Uncategorized';

// Modules
$txt['Shop_tab_modules'] = 'Modules';
$txt['Shop_tab_modules_desc'] = 'Here you can check all the shop modules (special items) available that were pre-included or uploaded.';
$txt['Shop_modules_list'] = 'Modules list';
$txt['Shop_modules_upload'] = 'Upload modules';
$txt['Shop_modules_upload_desc'] = 'In this section you can easily upload new modules to your shop item modules folder.';
$txt['Shop_modules_delete'] = 'Delete modules';
$txt['Shop_module_class'] = 'Class';
$txt['Shop_module_upload_success'] = 'The module was uploaded succesfully.';
$txt['Shop_module_upload_error'] = 'There was an error and the module was not uploaded.';
$txt['Shop_modules_delete_desc'] = 'The items that are using the modules you selected will be converted to regular items.';
$txt['Shop_modules_delete_sure'] = 'Are you sure you want to delete these modules?';
$txt['Shop_module_delete_also'] = 'This will also delete those modules from the modules directory. <br/> Existing items using this module will be turned into default and regular items (non-usable)';
$txt['Shop_module_added'] = 'The module was uploaded successfully.';
$txt['Shop_module_deleted'] = 'Selected modules were deleted succesfully.';
$txt['Shop_module_cant_instance'] = 'The file uploaded is not adecuate or it\'s not a module for the shop.';
$txt['Shop_module_delete_also'] = 'Deleting these modules will convert every item using them to regular items. It will also delete the module from the database and the file from the server.';

// Admin Categories
$txt['Shop_tab_cats'] = 'Shop Categories';
$txt['Shop_tab_cats_desc'] = 'In this section you can find all the created categories, and also edit them an add new categories.';
$txt['Shop_cats_list'] = 'Categories list';
$txt['Shop_no_cats'] = 'No categories added yet';
$txt['Shop_cats_add'] = 'Add category';
$txt['Shop_cats_add_desc'] = 'You are adding a new category. Here you can specify the name, image and description for the new category.';
$txt['Shop_cats_edit_desc'] = 'You are currently editing the the category \'%s\'.';
$txt['Shop_cats_delete'] = 'Delete categories';
$txt['Shop_cats_edit'] = 'Edit category';
$txt['Shop_categories_delete_sure'] = 'Are you sure you want to delete these categories?';
$txt['Shop_cats_delete_desc'] = 'This will delete the selected categories, and you can also decide if you want the items removed.';
$txt['Shop_cat_delete_also'] = 'Delete all the items on this category';
$txt['Shop_cat_delete_also_desc'] = 'This will completly remove the items that are in those categories. If not, the items will be set as \'uncategorized\'';
$txt['Shop_cats_deleted'] = 'Selected categories were deleted successfully.';
$txt['Shop_cats_added'] = 'The category was added successfully.';
$txt['Shop_cats_updated'] = 'The category was updated successfully.';
$txt['Shop_total_items'] = 'Total items';
$txt['Shop_cats_save'] = 'Save category';

// Games
$txt['Shop_tab_games'] = 'Games Settings';
$txt['Shop_games_desc'] = 'Here you can define how much the user will win or lose in the games';
$txt['Shop_games_slots'] = 'Slots';
$txt['Shop_games_lucky2'] = 'Lucky2';
$txt['Shop_games_number'] = 'Number Slots';
$txt['Shop_games_pairs'] = 'Pairs';
$txt['Shop_games_dice'] = 'Dice';
$txt['Shop_games_bet'] = 'Bet';
$txt['Shop_games_seven'] = 'Seven';
$txt['Shop_games_blackjack'] = 'Blackjack';

// Logs
$txt['Shop_tab_logs'] = 'Shop Logs';
$txt['Shop_tab_logs_desc'] = 'Here you can check all the logs, including sent money, trading, gifts, and more.';
$txt['Shop_logs_admin_money'] = 'Admin Money';
$txt['Shop_logs_admin_money_desc'] = 'Log for the sent %s by admin.';
$txt['Shop_logs_admin_items'] = 'Admin Items';
$txt['Shop_logs_admin_items_desc'] = 'Log for the sent/gifted items by admin.';
$txt['Shop_logs_money'] = 'Money Sent';
$txt['Shop_logs_money_desc'] = 'Log for the sent %s.';
$txt['Shop_logs_items'] = 'Items Sent';
$txt['Shop_logs_items_desc'] = 'Log for the sent or gifted items.';
$txt['Shop_logs_buy'] = 'Purchased items';
$txt['Shop_logs_buy_desc'] = 'Log for the purchased items in the forum shop.';
$txt['Shop_logs_trade'] = 'Traded items';
$txt['Shop_logs_trade_desc'] = 'Log for the purchased items at the trade center.';
$txt['Shop_logs_bank'] = 'Bank Transactions';
$txt['Shop_logs_bank_desc'] = 'Log for bank transactions, deposits, withdraws.';
$txt['Shop_logs_games'] = 'Games Room';
$txt['Shop_logs_games_desc'] = 'Log for the %s obtained in the gaming room.';
$txt['Shop_logs_games_type'] = 'Game';
$txt['Shop_logs_empty'] = 'There are no entries for this log';
$txt['Shop_logs_user_sending'] = 'Sending user';
$txt['Shop_logs_user_receiving'] = 'Receiving user';
$txt['Shop_logs_date'] = 'Date';
$txt['Shop_logs_amount'] = 'Amount';
$txt['Shop_logs_no_entries'] = 'There are no entries available';
$txt['Shop_logs_delete'] = 'Delete selected logs';
$txt['Shop_logs_buyer'] = 'Buyer';
$txt['Shop_logs_seller'] = 'Seller';
$txt['Shop_logs_user'] = 'User';
$txt['Shop_logs_fee'] = 'Fee';
$txt['Shop_logs_fee_type_0'] = ' from pocket';
$txt['Shop_logs_fee_type_1'] = ' from bank';
$txt['Shop_logs_transaction'] = 'Transaction';
$txt['Shop_logs_trans_withdrawal'] = 'Withdrawal';
$txt['Shop_logs_trans_deposit'] = 'Deposit';
$txt['Shop_logs_have_date'] = 'Has it since';
$txt['Shop_logs_updated'] = 'The selected entries were successfully deleted.';

// Admin inventory
$txt['Shop_tab_inventory'] = 'Shop Inventory';
$txt['Shop_tab_inventory_desc'] = 'Here you can search user\'s inventory and delete items from their profile.';
$txt['Shop_inventory_members_desc'] = 'Here you can select the members you wish to send stuff to.';
$txt['Shop_inventory_userinv'] = 'Browse inventory';
$txt['Shop_inventory_search'] = 'Search inventory';
$txt['Shop_inventory_items_deleted'] = 'The selected items were successfully deleted from %s\'s profile';
$txt['Shop_inventory_credits_updated'] = 'The credits of %s were successfully updated';
$txt['Shop_inventory_groupcredits'] = 'Group credits';
$txt['Shop_inventory_groupcredits_desc'] = 'Send credits to specific membergroups.';
$txt['Shop_inventory_groupcredits_success'] = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' were successfully sent to the selected groups.';
$txt['Shop_inventory_groupcredits_membergroup'] = 'Membergroup';
$txt['Shop_inventory_groupcredits_action'] = 'Action:';
$txt['Shop_inventory_groupcredits_add'] = 'Add';
$txt['Shop_inventory_groupcredits_substract'] = 'Substract';
$txt['Shop_inventory_usercredits'] = 'Send credits';
$txt['Shop_inventory_usercredits_desc'] = 'Send credits to specific members in the forum.';
$txt['Shop_inventory_usercredits_success'] = $modSettings['Shop_credits_suffix'] . ' were successfully sent to the selected users.';
$txt['Shop_inventory_useritems'] = 'Send items';
$txt['Shop_inventory_useritems_desc'] = 'Send items to specific members in the forum. If any item has a specific carrying limit it will be ignored, but it will still lower the stock for the item on the number of members selected.';
$txt['Shop_inventory_useritems_success'] = 'Selected item was successfully sent';
$txt['Shop_inventory_restock'] = 'Restock items';
$txt['Shop_inventory_restock_desc'] = 'Here you can reset the stock of specific items, or all of them.';
$txt['Shop_inventory_restock_success'] = 'Selected items were successfully restocked';
$txt['Shop_inventory_restock_what'] = 'Items to restock';
$txt['Shop_inventory_restock_all'] = 'Restock all items';
$txt['Shop_inventory_restock_selected'] = 'Restock only selected items';
$txt['Shop_inventory_restock_select_items'] = 'Select the items you would like to restock';
$txt['Shop_inventory_restock_lessthan'] = 'Restock all items with a stock less or equal to';
$txt['Shop_inventory_restock_lessthan_desc'] = 'If you checked the option to restock only selected items, this option will be ignored.';
$txt['Shop_inventory_restock_amount'] = 'Amount to add to stock';
$txt['Shop_inventory_restock_amount_desc'] = 'It will sum that number with the current stock of the item(s)';

// Files
$txt['Shop_file_already_exists'] = 'Sorry, that file already exists.';
$txt['Shop_file_error_empty'] = 'Please select something to upload.';
$txt['Shop_file_error_type1'] = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
$txt['Shop_file_error_type2'] = 'Sorry, only PHP files are allowed.';
$txt['Shop_file_too_large'] = 'Sorry, your file is too large.';

// Scheduled tasks
$txt['scheduled_task_shop_bank_interest'] = 'Shop Bank interest';
$txt['scheduled_task_desc_shop_bank_interest'] = 'The magic task that will create bank ' . (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' out of nowhere for the users.';

// Packages types
$txt['shop_modules_package'] = 'ST Shop Plugins';
$txt['install_shop_modules'] = 'Install Shop Plugin';
$txt['shop_games_package'] = 'ST Shop Games';
$txt['install_shop_games'] = 'Install Shop Game';

// Maintenance
$txt['Shop_tab_maint'] = 'Shop Maintenance';
$txt['Shop_tab_maint_desc'] = 'Here you can use some maintenance utilities for the shop, like converting from other sho mods.';
$txt['Shop_maint_import'] = 'Import data';
$txt['Shop_maint_import_desc'] = 'On this section you can import data from other shop mods you previously had installed in your forum.';
$txt['Shop_maint_convert'] = 'Importing data';
$txt['Shop_maint_convert_warn'] = 'Once you go forward importing data from a different mod, your current shop items, shop categories, inventories and logs will be deleted. This shouldn\'t be a problem assuming you just installed ST Shop and want to convert from a different mod or 3.x version of ST Shop.<br/>
Shop items that work on a Module (are usable) won\'t carry over as usable and will become regular items. If you are upgrading from ST Shop 3.2 they will keep working as usual if are on default modules.';
$txt['Shop_import_from'] = 'We have detected a previous installation of ';
$txt['Shop_import_from_SMFShop'] = $txt['Shop_import_from'] . '<a href="https://custom.simplemachines.org/mods/index.php?mod=65">SMF Shop</a>';
$txt['Shop_import_from_STShop'] = $txt['Shop_import_from'] . '<a href="https://custom.simplemachines.org/mods/index.php?mod=1794">ST Shop</a>';
$txt['Shop_import_from_SAShop'] = $txt['Shop_import_from'] . '<a href="https://custom.simplemachines.org/mods/index.php?mod=1794">SA Shop</a>';
$txt['Shop_maint_import_go'] = 'Start';
$txt['Shop_maint_import_results'] = 'Results of the import';
$txt['Shop_maint_import_found'] = 'Data found';
$txt['Shop_maint_import_imported'] = 'Data imported';
$txt['Shop_import_found_items'] = 'Items found:';
$txt['Shop_import_found_cats'] = 'Categories found:';
$txt['Shop_import_found_inventories'] = 'Items found in inventories:';
$txt['Shop_import_found_modules'] = 'Modules found:';
$txt['Shop_import_found_log_buy'] = 'Log entries found for purchase history:';
$txt['Shop_import_found_log_bank'] = 'Log entries found for bank history:';
$txt['Shop_import_found_log_gift'] = 'Log entries found for gift history:';
$txt['Shop_import_found_log_games'] = 'Log entries found for games history:';
$txt['Shop_import_imported_items'] = 'Items imported:';
$txt['Shop_import_imported_cats'] = 'Categories imported:';
$txt['Shop_import_imported_inventories'] = 'Inventory items imported:';
$txt['Shop_import_imported_modules'] = 'Modules imported:';
$txt['Shop_import_imported_cash'] = 'User\'s with money imported:';
$txt['Shop_import_imported_gamespass'] = 'User\'s with game days imported:';
$txt['Shop_import_imported_board'] = 'Board settings imported:';
$txt['Shop_import_imported_settings'] = 'Shop settings imported:';
$txt['Shop_import_imported_log_buy'] = 'Purchase log entries imported:';
$txt['Shop_import_imported_log_bank'] = 'Bank log entries imported:';
$txt['Shop_import_imported_log_gift'] = 'Gift log entries imported:';
$txt['Shop_import_imported_log_games'] = 'Game log entries imported:';


