<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

 global $arcadeModSettings, $boardurl, $settings, $boarddir;
// General
$txt['arcade_admin_title'] = 'Arcade Admin';
$txt['arcade_html5_path_notation'] = '** Some IBP HTML5 games will require the default for these settings **';
$txt['arcade_new_version'] = 'There is new version, %s or %s';
$txt['arcade_check_website'] = 'check website for details';
$txt['arcade_download_update'] = 'download update';
$txt['pgroups_post_group'] = 'This is post group.';
$txt['regular_members'] = 'Regular Members';
$txt['arcade_group_arena'] = 'Everyone on Arcade Arena';
$txt['arcade_admin'] = 'Arcade Admin';
$txt['arcade_general_information'] = 'Arcade Info';
$txt['arcadeHooksGuide'] = 'Arcade Guide';
$txt['arcade_general_settings'] = 'General Settings';
$txt['arcade_discernible_settings'] = 'Visual Settings';
$txt['arcade_general'] = 'Arcade';
$txt['arcade_general_desc'] = 'Here you can check latest version, and edit settings of Arcade';
$txt['arcadeStats'] = 'Arcade Statistics';
$txt['sendArcadeChallenge'] = 'Arcade Challenge';
$txt['arcade_default_email'] = 'no_reply_' . mt_rand() . '@arcadebot.com';
$txt['arcade_confirm_action'] = 'Are you sure you want to do this action?';
$txt['arcade_general_blank'] = '&nbsp;&nbsp;';

// uninstaller
$txt['arcade_uninstall_database_files'] = 'Remove all SMF-Arcade game files, gamedata files, game directories and database entries.';
$txt['arcade_uninstall_remove_db_files'] = 'Selecting this option will remove all game files, gamedata files, directories and database entries for SMF Arcade.';
$txt['arcade_confirm_remove_db_files'] = 'This action is not reversable.\r\n\r\nPlease make sure you have a copy of all games files, gamedata files and the database prior to using this option!\r\n\r\nAre you sure you want to remove all game files, directories and database entries for the Arcade?';

// Arcade Main Admin Titles
$txt['arcade_title_specific_list'] = '<span style="font: italic bold 12px Georgia, serif;">List Settings</span>';
$txt['arcade_title_specific_skin'] = '<span style="font: italic bold 12px Georgia, serif;">Skin Settings</span>';
$txt['arcade_title_mobile_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Mobile Skin/List Settings</span>';
$txt['arcade_title_path_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Path Settings</span>';
$txt['arcade_title_general_pdl_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Advanced Settings</span>';
$txt['arcade_title_general_enable'] = '<span style="font: italic bold 12px Georgia, serif;">General Settings</span>';
$txt['arcade_title_discernible_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Visual Settings</span>';
$txt['arcade_title_debug_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Debug Logging</span>';
$txt['arcade_title_posting_settings'] = '<span style="font: italic bold 12px Georgia, serif;">New Game Notifications</span>';
$txt['arcade_title_upload_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Game Uploading</span>';
$txt['arcade_title_download_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Game Downloading</span>';
$txt['arcade_title_report_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Game Reporting</span>';
$txt['arcade_title_score_settings'] = '<span style="font: italic bold 12px Georgia, serif;">High Score Settings</span>';
$txt['arcade_title_arena_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Arena Settings</span>';
$txt['arcade_title_cache_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Cache/Cookie Settings</span>';
$txt['arcade_title_api_translate_settings'] = '<span style="font: italic bold 12px Georgia, serif;">API Translation Settings</span>';
$txt['arcade_title_azure_translate_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Azure API V3 Translation Settings</span>';
$txt['arcade_title_deepL_translate_settings'] = '<span style="font: italic bold 12px Georgia, serif;">DeepL API V2 Translation Settings</span>';
$txt['arcade_title_cat_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Systemic Category Settings</span>';
$txt['arcade_title_emulatorjs_gamesettings'] = '<span style="font: italic bold 12px Georgia, serif;">EmulatorJS Game Template Settings</span>';
$txt['arcade_emulatorjs_console_debug_msg'] = '<span style="font: italic bold 12px Georgia, serif;border: 0.1rem dotted;">All EmulatorJS Settings Enabled</span>';
$txt['arcade_emulatorjs_console_debug'] = '<span style="font: italic bold 12px Georgia, serif;border: 0.1rem dotted;">Console Debug Enabled</span>';
$txt['arcade_title_icon_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Systemic Image Settings</span>';
$txt['arcade_title_navigation_settings'] = '<span style="font: italic bold 12px Georgia, serif;">Navigation Settings</span>';

// Information
$txt['arcade_status'] = 'Version Information';
$txt['arcade_latest_news'] = 'Latest news';
$txt['arcade_unable_to_connect'] = 'Unable to connect to %s, for latest news visit site.';
$txt['arcade_installed_version'] = 'Installed version';
$txt['arcade_latest_version'] = 'Latest version';

// Arcade Mobile Settings
$txt['arcadeListMobile'] = 'Select Mobile List Type';
$txt['arcade_list_mobile_generic'] = 'Mobile Selfsame List';
$txt['arcade_list_mobile0'] = 'Mobile Light List';
$txt['arcadeSkinMobile'] = 'Select Mobile Arcade Skin';
$txt['arcade_skin_mobile_generic'] = 'Mobile Selfsame Skin';
$txt['arcade_skin_mobile0'] = 'Mobile Light Skin';

// Category Template Settings
$txt['arcadeCropCatIconsDescript'] = 'Crop category icons during upload';
$txt['arcadeDropCat'] = 'Enable Drop Down Categories';
$txt['arcade_catWidth'] = 'Set Category Icon Width';
$txt['arcade_catHeight'] = 'Set Category Icon Height';
$txt['arcadeDropCat'] = 'Enable Drop Down Categories [Enterprise-C]';
$txt['arcadeDropCatClassic'] = 'Enable Drop Down Categories [Classic]';
$txt['arcade_catHideUnused'] = 'Hide Unused Categories';
$txt['arcade_showListCat'] = 'Show Category In Lists';
$txt['arcadeCatsCellWidth'] = 'Category Cell Width (Percentage)';
$txt['arcadeCatsPerLine'] = 'Categories Per Line (Max: 20)';
$txt['arcadeCropCatIcons'] = 'Crop Category Icons';

// Imagick
$txt['arcadeFilterImagickDescript'] = 'Imagick quality filter for thumbnails & cover art within lists';
$txt['arcadeFilterImagick'] = 'Imagick List Filtering';
$txt['arcadeFilterImagickOpt'] = '[-DISABLE-]|[-JPG/PNG-]|[-JPG/PNG/GIF-]';
$txt['arcadeFilterImagickAdminDescript'] = 'Imagick quality filter for thumbnails & cover art during game installations';
$txt['arcadeFilterImagickAdmin'] = 'Imagick Game Installation Filtering';

// Cover Art Template Settings
$txt['arcadeViewCoversDescript'] = 'Enable game cover art selection for opted game types';
$txt['arcadeCropCoverArtDescript'] = 'Crop cover art during upload';
$txt['arcadeCropThumbnailsDescript'] = 'Crop thumbnails during upload';
$txt['arcadeViewCovers'] = 'Game Cover Art';
$txt['arcadeViewCoversOpt'] = 'Disable|ROM-Arcade|Arcade|All';
$txt['arcadeCropCoverArt'] = 'Crop Game Cover Art';

// Arcade Settings
$txt['arcadeList'] = 'Select List Type';
$txt['arcade_list0'] = 'Generic';
$txt['arcade_list1'] = 'Retro';
$txt['arcade_list2'] = 'Vintage';
$txt['arcadeIconBorderRadius0'] = 'Game Icon Border Radius [Generic]';
$txt['arcadeIconBorderRadius1'] = 'Game Icon Border Radius [Vintage]';
$txt['arcadeIconBorderRadius2'] = 'Game Icon Border Radius [Retro]';
$txt['arcadeListHorizontalDivision'] = 'Enable Horizontal Divider';
$txt['arcadeListGenericExtraBg'] = 'Swap Backgrounds Of Cells [Generic]';
$txt['arcadeListGenericExtraBorder'] = 'Enable Double Border Division [Generic]';
$txt['arcade_list_generic0'] = 'windowbg/windowbg';
$txt['arcade_list_generic1'] = 'windowbg2/windowbg2';
$txt['arcade_list_generic2'] = 'windowbg/windowbg2';
$txt['arcade_list_generic3'] = 'windowbg2/windowbg';
$txt['arcadeTypeQuery'] = 'List Game Type Behavior';
$txt['arcade_type_query0'] = 'Show Only Opted Game Type';
$txt['arcade_type_query1'] = 'Show Opted Game Type First';
$txt['arcade_decimal'] = 'Set Decimal Places';
$txt['arcadeSettings'] = 'Arcade Settings';
$txt['skin_showcatchamps'] = 'Show Category Champs';
$txt['arcade_decimal_recommend'] = '(recommend 3 max)';
$txt['arcade_default'] = 'Classic';
$txt['arcade_mobile_skin_a'] = 'Selfsame Mobile';
$txt['arcade_skin_a'] = 'Enterprise-A';
$txt['arcade_skin_b'] = 'Defiant';
$txt['arcade_skin_c'] = 'Enterprise-C';
$txt['arcadeSkin'] = 'Select Arcade Skin';
$txt['arcade_shoutboxC'] = 'Enable Enterprise-C Shoutbox';
$txt['arcade_shoutboxC_name'] = 'Arcade Shoutbox Name [Enterprise-C]';
$txt['arcade_shout_interval_recommendC'] = '(Recommend 10 seconds)';
$txt['arcade_shout_heightC'] = 'Arcade Shoutbox Height [Enterprise-C]';
$txt['arcade_shout_height_unitsC'] = '(Units are in "em" - recommend 60 - 70)';
$txt['arcade_show_shoutsC'] = 'Set Displayed Shouts';
$txt['arcade_shout_intervalC'] = 'Set Shout Interval';
$txt['arcadeDailyGameScoresC'] = 'Show Game Of Day Scores';
$txt['skin_latest_scores'] = 'Set Enterprise-C Latest Scores';
$txt['skin_latest_champs'] = 'Set Enterprise-C Latest Champs';
$txt['skin_latest_games'] = 'Set Enterprise-C Latest Games [Arcade]';
$txt['skin_most_popular'] = 'Set Enterprise-C Most Played [Arcade]';
$txt['skin_latest_rom_games'] = 'Set Enterprise-C Latest Games [ROM]';
$txt['skin_most_rom_popular'] = 'Set Enterprise-C Most Played [ROM]';
$txt['skin_avatar_size_width'] = 'Set Enterprise-C Avatar Max Width';
$txt['skin_avatar_size_height'] = 'Set Enterprise-C Avatar Max Height';
$txt['avsize_recommend'] = 'Recommend (30) don\'t overdo it';
$txt['arcade_rec_val'] = 'Recommended Value: %s';
$txt['arcade_admin_settings'] = 'Arcade General Settings';
$txt['arcade_admin_visual'] = 'Arcade Visual Settings';
$txt['arcade_admin_discernible'] = 'Arcade Discernible Settings';
$txt['arcade_settings_desc'] = 'Here you can edit general settings of the Arcade';
$txt['arcade_discernible_desc'] = 'Here you can edit discernible settings of the Arcade';
$txt['arcadeEnabled'] = 'Enable SMF Arcade';
$txt['arcadeArenaEnabled'] = 'Enable Arena Matches';
$txt['arcadeEnableFavorites'] = 'Favorite Games';
$txt['arcadeEnableRatings'] = 'Rating Games';
$txt['arcadeDisplayType'] = 'Display HTML5/Flash Game Type';
$txt['arcadeDisplayRomType'] = 'Display ROM Game Type';
$txt['arcadeGamesNameLength'] = 'Max Game Name Char Length [Enterprise-C]';
$txt['arcadeAuxiliaryUserText'] = 'Used for posting games, scores, etc.';
$txt['arcadeDescriptLengthText'] = 'Game descriptions within some lists beyond this character length are visible by a click event.';
$txt['arcadeFilesMax'] = 'File Maintenance Threshold';
$txt['arcadeFilesMaxOptions'] = '500|1000|2500|5000|10000|ALL';
$txt['arcadeFilesMaxText'] = 'The amount of files to process prior to a page refresh for various arcade maintenance tasks.';
$txt['arcadeNewRandomId'] = 'Generate New Random Arcade ID';
$txt['arcadeRandomId'] = 'Current Id: %s';
$txt['arcadeJsInsertDefault'] = 'JavaScript File Insertion';
$txt['arcadeJsInsertDefaultText'] = 'The default setting when installing games';
$txt['arcade_contentSecurityPolicy'] = 'Disable Content-Security-Policy For Arcade Templates';
$txt['arcade_flash_emulator'] = 'Ruffle Flash Emulator';
$txt['arcade_rom_emulator'] = 'EmulatorJS ROM Emulator';
$txt['arcadeFlashEmulator'] = 'Disabling the flash emulator may make up-to-date browsers incapable of supporting flash content';
$txt['arcadeRomEmulator'] = 'Using the CDN for the ROM emulator may result in different user options for the template albeit more up to date';
$txt['arcadeListSort'] = 'Default Game List Sorting';
$txt['arcadeListDefaultSort'] = 'Alphabetical (Asc)|Alphabetical (Desc)|Age (Asc)|Age (Desc)|Most Popular|Least Popular|Champions|Latest Champions|Rating|Favorites';
$txt['gamesPerRowVintage'] = '<span style="font-size: smaller;">( range: 4 - 7 )</span>';
$txt['gamesPerRowVintage'] = 'Games Per Row [Vintage]';
$txt['arcadeEnableStShopPatch'] = 'Enable ST-Shop Patch';
$txt['arcadeRetroLibraryCodeEnable'] = 'Enable I/O Retro Library Code';
$txt['arcadeRetroLibraryCode'] = 'Retro Library Code';
$txt['arcade_lib_val'] = 'Current retro library code: %s';
$txt['arcadeProfileView'] = 'Profile Info Display';
$txt['arcadeProfileViewSelect'] = 'Disable|Icon + Text|Icon';
$txt['arcade_alternateBGC'] = 'Alternate BG Colors For Listed Data';
$txt['arcadeHideDisabled'] = 'Hide Disabled Navigation';
$txt['arcadeArchiveTar'] = 'Enable PEAR/Archive_Tar';
$txt['arcadeArchiveTarText'] = 'SMF-Arcade PEAR plug-in has been detected';

// specific list thumbnail dimensions
$txt['arcade_thumbRange'] = '<span style="font-size: smaller;">( px range: 20 - 120 )</span>';
$txt['arcade_thumbWidthGeneric'] = 'Thumbnail Width [Generic]';
$txt['arcade_thumbHeightGeneric'] = 'Thumbnail Height [Generic]';
$txt['arcade_thumbWidthRetro'] = 'Thumbnail Width [Retro]';
$txt['arcade_thumbHeightRetro'] = 'Thumbnail Height [Retro]';
$txt['arcade_thumbWidthVintage'] = 'Thumbnail Width [Vintage]';
$txt['arcade_thumbHeightVintage'] = 'Thumbnail Height [Vintage]';

// specific list cover art dimensions
$txt['arcade_coverRange'] = '<span style="font-size: smaller;">( px range: 80 - 400 )</span>';
$txt['arcade_coverEnable'] = '<span style="font-size: smaller;">( cover art | thumbnail )</span>';
$txt['arcade_coverFullWidth'] = '<span style="font-size: smaller;">( use entire width of game cell )</span>';
$txt['arcadeResizeCoverArtDescript'] = '<span style="font-size: smaller;">Use actual image dimensions when enabled</span>';
$txt['arcade_coverEnableGeneric'] = 'Enable Cover Art [Generic]';
$txt['arcade_coverWidthGeneric'] = 'Cover Art Width [Generic]';
$txt['arcade_coverHeightGeneric'] = 'Cover Art Height [Generic]';
$txt['arcade_coverFullWidthGeneric'] = 'Cover Art Full Width [Generic]';
$txt['arcadeResizeCoverArtGeneric'] = 'Disregard Resizing Of Cover Art [Generic]';
$txt['arcade_coverEnableRetro'] = 'Enable Cover Art [Retro]';
$txt['arcade_coverWidthRetro'] = 'Cover Art Width [Retro]';
$txt['arcade_coverHeightRetro'] = 'Cover Art Height [Retro]';
$txt['arcade_coverFullWidthRetro'] = 'Cover Art Full Width [Retro]';
$txt['arcadeResizeCoverArtRetro'] = 'Disregard Resizing Of Cover Art [Retro]';
$txt['arcade_coverEnableVintage'] = 'Enable Cover Art [Vintage]';
$txt['arcade_coverWidthVintage'] = 'Cover Art Width [Vintage]';
$txt['arcade_coverHeightVintage'] = 'Cover Art Height [Vintage]';
$txt['arcade_coverFullWidthVintage'] = 'Cover Art Full Width [Vintage]';
$txt['arcadeResizeCoverArtVintage'] = 'Disregard Resizing Of Cover Art [Vintage]';

$txt['arcadeCommentLen'] = 'Max Comment Length';
$txt['arcadeCommentLen_subtext'] = '(0 is unlimited)';

$txt['gamesPerPage'] = 'Games Per Page';
$txt['matchesPerPage'] = 'Matches Per Page';
$txt['scoresPerPage'] = 'Scores Per Page';

$txt['gamesEmail'] = 'Email Address To Send High Scores';
$txt['gamesNotificationsBulk'] = 'Enable Bulk Notification For Emails And PMs';
$txt['gamesUrl'] = 'URL To Games';
$txt['arcadeRuffleExternalLink'] = 'URL to external Ruffle zip archive';
$txt['gamesDirectory'] = 'Path To Games Directory';
$txt['arcadeGamecacheUpdate'] = 'Update Game Cache Automatically';
$txt['arcade_cache_enable'] = 'Cache Applicable Arcade Data';
$txt['arcade_cache_time'] = 'Cache Time Setting ~ Arcade Data';
$txt['arcade_cache_shouttime'] = 'Cache Time Setting ~ Arcade Shoutbox';
$txt['arcade_cache_time_msg'] = '( range: 1 - 1800 seconds )';
$txt['arcadeGameTesting'] = 'Disable Arcade CSS/JavaScript File Caching';
$txt['arcadeGameTestingText'] = 'This will force the server to provide those Arcade files for every page load (ie. for testing)';
$txt['arcadeCookieEncryptionCipher'] = 'Cookie Encryption Cipher';
$txt['arcadeCookieEncryptionCipherTxt'] = '( range: 5 - 30 characters )';
$txt['arcade_suggest_time'] = 'Game Suggestion Delay';
$txt['arcade_suggest_time_msg'] = '( range: 1 - 30 seconds )';

$txt['arcadeCheckLevel'] = 'Cheating Check Mode';
$txt['arcade_check_level0'] = 'Basic (Warning only)';
$txt['arcade_check_level1'] = 'Default (Recommended)';
$txt['arcade_check_level2'] = 'Default Plus (Not recommended)';

$txt['arcadeMaxScores'] = 'Maximum Scores (per player per game)';
$txt['arcadeDisableComments'] = 'Disable Score Comments';
$txt['arcade_shout_arena_score'] = 'Shout Arena Scores';
$txt['arcade_shout_member_score'] = 'Shout Member Scores';
$txt['arcade_shout_guest_score'] = 'Shout Guest Scores';

$txt['arcade_phpbb3_support_score'] = 'PhpBB3 Arcade Compatibility';
$txt['arcade_phpbb3_support_score_subtext'] = 'Warning: These game types may be intrusive';
$txt['arcade_phpbb3_support_score_alert'] = ' Warning: Some PhpBB3 arcade games have been found to contain code that relays website/domain information back to a specific website.\n These games will be installed on your Arcade System as HTML5 v3 save type if they are properly detected.\n Some of the HTML5 v3 games may or may not save score on this Arcade System and are void of support.\n Many of the same games can be found using other supported game types and are recommended.';

$txt['arcade_db_glob_setting'] = 'Set MySQL Global (max_packet_size)';
$txt['arcade_db_glob_settingOptions'] = 'DISABLE|SHOW|SELECT';
$txt['arcade_db_glob_settingOptionsQuery'] = 'Current max_packet_size value: %s';
$txt['arcade_db_glob_settingOptionsQueryNone'] = 'Disabled|Not Detected|PostgreSQL Detected';
$txt['arcade_log_install_game'] = 'Enable game install debug logging';
$txt['arcade_log_savetype'] = 'Enable save type debug logging';
$txt['arcade_log_scoreloop'] = 'Enable score loop debug logging';
$txt['arcade_log_translate'] = 'Enable translation debug logging';
$txt['arcade_log_remote_game'] = 'Enable remote game file debug logging';
$txt['arcade_log_emulatorjs_console'] = 'Enable EmulatorJS console debug logging';
$txt['arcade_log_emulator_core'] = 'Enable Ruffle/EmulatorJS update debug logging';
$txt['arcadeModSecurity'] = 'Using the HTML5 upload script requires mod_security to be disabled';
$txt['arcade_log_imagick'] = 'Enable Imagick debug logging';

$txt['arcade_jQuery_JV'] = 'Load JQuery Library';
$txt['arcade_jQuery_JV_require'] = 'This may be necessary for some PhpBB3 HTML5 games regarding score support';

$txt['arcade_install_duplicate_game'] = 'Ignore/Skip Duplicate Game Installations';
$txt['arcade_install_clean_db'] = 'Flag Duplicate/Matched Database Entries During Game Installations';

$txt['arcade_flash_emulator_opt'] = 'Self Hosted|Remote CDN|Disabled';
$txt['arcade_rom_emulator_opt'] = 'Self Hosted|Remote CDN';
$txt['arcade_php_zip_arhive'] = 'ZipArhive not detected!';
$txt['emulator_types'] = 'EmulatorJS|Ruffle';
$txt['arcadeBehaviorCDN'] = 'Ruffle/EmulatorJS Behavior';
$txt['arcadeBehaviorOptionsCDN'] = 'Fatal|Toggle|Check';
$txt['arcadeBehaviorCDNText'] = 'Option to check if a source is available prior to using it.';

// Translation general settings
$txt['arcadeTranslationAPI'] = 'Translation API';
$txt['arcadeTranslationTypesAPI'] = 'Azure|DeepL';
$txt['arcade_adjust_desc_admin'] = 'Auto Adjust Description/Help Language During Game Installation';
$txt['arcade_adjust_desc_info'] = 'Auto Adjust Description/Help Language During Game Play';
$txt['arcade_adjust_desc_info_msg'] = 'Disabling this setting is recommended as it will use up your monthly character limit.';
$txt['arcade_adjust_desc_info_warn'] = '\r\nEnabling this setting will use up your monthly character limit.\r\n Are you sure you want to enable this setting?';
$txt['arcadeTranslationAPIText'] = 'Choose the translation API to be used for game descriptions';

// Arcade Azure Settings (translation API)
$txt['arcadeAzureEndpoint'] = 'Azure API Endpoint';
$txt['arcadeAzurePath'] = 'Azure API Path';
$txt['arcadeAzureRegionCode'] = 'Microsoft Azure Region Code';
$txt['arcadeAzureSubKey'] = 'Microsoft Azure Subscription Key #1';
$txt['arcadeAzureSubKey2'] = 'Microsoft Azure Subscription Key #2';
$txt['arcadeAzureSubKeyMsg'] = 'Current Azure Key #1 Entry: %s';
$txt['arcadeAzureSubKeyMsg2'] = 'Current Azure Key #2 Entry: %s';
$txt['arcadeAzureEndpointMsg'] = 'Current Azure API Endpoint Entry: %s';
$txt['arcadeAzurePathMsg'] = 'Current Azure API Path Entry: %s';
$txt['arcadeAzureRegionCodeMsg'] = 'Leave this entry blank if you are using the global region setting.';
$txt['arcadeAzure'] = 'Register at <a href="https://azure.microsoft.com/en-us/services/cognitive-services/translator/" style="font-weight: bold;font-style: italic;">Azure Translator Service</a> to use their API.';
$txt['arcadeAzureAutoFill'] = '<span style="font-weight: 580;">[Auto-Fill Default API Endpoint & Path Settings]  --></span>';

// Arcade DeepL settings (translation API)
$txt['arcadeDeepLEndpoint'] = 'DeepL API Endpoint';
$txt['arcadeDeepLPath'] = 'DeepL API Path';
$txt['arcadeDeepLSubKey'] = 'DeepL Subscription Key';
$txt['arcadeDeepLSubKeyMsg'] = 'Current DeepL Key Entry: %s';
$txt['arcadeDeepLEndpointMsg'] = 'Current DeepL API Endpoint Entry: %s';
$txt['arcadeDeepLPathMsg'] = 'Current DeepL API Path Entry: %s';
$txt['arcadeDeepL'] = 'Register at <a href="https://www.deepl.com/pro#developer" style="font-weight: bold;font-style: italic;">DeepL Translator Service</a> (for developers) to use their API.';
$txt['arcadeOptDeepLPT'] = 'ALL|BRAZILIAN';
$txt['arcadeDeepLPTText'] = 'Opt all Portuguese or Brazilian Portuguese';
$txt['arcadeDeepLPT'] = 'Portuguese';
$txt['arcadeOptDeepLEN'] = 'US|BRITISH';
$txt['arcadeDeepLENText'] = 'Opt American English or British English';
$txt['arcadeDeepLEN'] = 'English';
$txt['arcadeDeepLAutoFill'] = '<span style="font-weight: 580;">[Auto-Fill Default API Endpoint & Path Settings]  --></span>';
$txt['arcadeDeepLinvalid'] = 'DeepL Subscription Key Appears To Be Invalid';
$txt['arcadeDeepLText'] = '<span id="arcadeDeepLText">Enter DeepL Subscription Key</span>';
$txt['arcadeDeepLlimits'] = 'Character Count: %s  |  Character Limit: %s';
$txt['arcade_deepL_error'] = 'DeepL Query Returned No Result';

// Arcade Admin permissions
$txt['arcade_general_permissions'] = 'Arcade Permissions';
$txt['arcadePermissionMode'] = 'Permission mode';
$txt['arcade_permission_mode_none'] = 'None';
$txt['arcade_permission_mode_category'] = 'Category Only';
$txt['arcade_permission_mode_game'] = 'Game Only';
$txt['arcade_permission_mode_and_both'] = 'Category and Game';
$txt['arcade_permission_mode_or_both'] = 'Category or Game';

// guests should not have these anyway
if (empty($txt['permissionname_arcade_comment_perm_any'])) {
	$txt['permissionname_arcade_comment_perm_any'] = 'Any';
	$txt['permissionname_arcade_comment_perm_own'] = 'Own';
}

// Post Permissions
$txt['arcadePostPermission'] = 'Enable Post Count / Post Per Day Check';
$txt['arcadePostsPlay'] = 'Cumulative Post Needed To Play';
$txt['arcadePostsLastDay'] = 'Posts In Last 24 Hours Needed To Play';
$txt['arcadePostsPlayAverage'] = 'Average Post Per Day Needed To Play';

$txt['perm_arcade_view'] = 'View Arcade';
$txt['perm_arcade_play'] = 'Play In Arcade';
$txt['perm_arcade_submit'] = 'Save Scores';
$txt['perm_arcade_view_retro_arch'] = 'Play In ROM Arcade';

// ManageGames
$txt['arcade_manage_games_desc'] = 'Here you can edit and install games';
$txt['arcade_manage_games'] = 'Games';
$txt['arcade_manage_rom_games_desc'] = 'Here you can edit and install ROM games';
$txt['arcade_manage_rom_games'] = 'ROM Games';
$txt['arcade_manage_rom_games_list'] = 'ROM Games List';
$txt['arcade_manage_games_list'] = 'Games List';
$txt['arcade_manage_games_list_search'] = 'Search:';
$txt['arcade_manage_games_clear_search'] = 'Click here to clear saved search parameters';
$txt['arcade_manage_sort_select_alpha'] = 'Select search value:';
$txt['arcade_manage_sort_select_type'] = 'Select game type:';
$txt['arcade_manage_rom_sort_select_type'] = 'Select ROM game system:';
$txt['arcade_manage_sort_alphabetical'] = 'Starting with %s';
$txt['arcade_manage_sort_gametype'] = array('Alphabetical', 'Newest to Oldest', 'HTML5 v1 - SMF', 'HTML5 v2 - IBP', 'HTML5 v3 - PHPBB', 'SMF Arcade V1', 'SMF Arcade V2', 'SMF Arcade V3', 'PHPNuke', 'Silver', 'Custom', 'PhpBB v1/v2', 'Mochi Ads', 'VBulletin v3', 'IBP v1', 'IBP v2', 'IBP v3', 'IBP v3.2');

// Filter tabs
$txt['manage_games_filter_all'] = 'Show All';
$txt['manage_games_filter_enabled'] = 'Hide Disabled';
$txt['manage_games_filter_disabled'] = 'Hide Enabled';

$txt['arcade_no_games_filter'] = 'No games found. Try changing filter';
$txt['arcade_no_games_installed'] = 'You haven\'t installed any games yet, you can <a href="%s">install games here</a>.';
$txt['arcade_no_rom_games_installed'] = 'You haven\'t installed any ROM games yet, you can <a href="%s">install ROM games here</a>.';
$txt['arcade_no_games_available_for_install'] = 'No Games available to install, you can <a href="%s">upload games here</a>.';

$txt['arcade_edit'] = '[ Edit ]';
$txt['arcade_install'] = '[ Install ]';
$txt['arcade_uninstall'] = '[ Uninstall ]';
$txt['arcade_admin_play'] = '[ Play ]';
$txt['arcade_unzip'] = '[ Extract ]';
$txt['arcade_missing_files'] = 'Main file missing';

// Quick Actions
$txt['quickmod_change_category'] = 'Change Category Of Selected';
$txt['quickmod_change_romtype'] = 'Change ROM Type';
$txt['quickmod_install_selected'] = 'Install Selected';
$txt['quickmod_delete_selected'] = 'Delete Selected';
$txt['quickmod_uninstall_selected'] = 'Uninstall Selected';

$txt['arcade_install_complete'] = 'Install Complete';
$txt['arcade_install_following_games'] = 'Following games were installed';
$txt['arcade_uninstall_complete'] = 'Uninstall Complete';
$txt['arcade_uninstall_following_games'] = 'Following games were uninstalled';

// Upload
$txt['arcade_upload'] = 'Upload';
$txt['post_max_size'] = 'Maximum Total File Size:';
$txt['arcade_upload_button'] = 'Upload';
$txt['arcade_supported_filetypes'] = 'Supported file types: zip | tar | gz | rar';
$txt['arcade_upload_error'] = 'An error occurred while uploading the file';
$txt['arcade_upload_abort'] = 'The upload has been canceled by the user or the browser dropped the connection';
$txt['arcade_upload_warnsize'] = 'File exceeds PHP upload limits. Adjust ~ upload_max_filesize & post_max_size ~ in your PHP .ini file';
$txt['arcade_upload_msg1'] = 'Drop Here';
$txt['arcade_upload_msg2'] = 'Browse';
$txt['arcade_upload_nofile'] = 'No file selected';
$txt['arcade_upload_complete'] = 'Upload complete: %s';
$txt['arcade_upload_please_wait'] = 'Please wait:';

// Maintenance
$txt['arcade_maintenance'] = 'Arcade Maintenance';
$txt['arcade_maintenance_desc'] = 'Here you can perform maintenance actions on Arcade features.';
$txt['arcade_maintain_done'] = '[~Maintenance finished~]';
$txt['arcade_maintain_pending'] = '[~Maintenance pending~]';
$txt['arcade_maintain_none'] = 'Nothing to do!';
$txt['arcade_maintenance_fixScores'] = 'Repair Score Tables';
$txt['arcade_maintenance_updateGamecache'] = 'Clear Arcade Cache';
$txt['arcade_maintenance_clearDbSessions'] = 'Clear DB Sessions';
$txt['arcade_maintenance_fileFix'] = 'Lowercase Game Files';
$txt['arcade_maintenance_htmlfilefix'] = 'Fix HTML5 Game Files';
$txt['arcade_maintenance_waitMsg'] = 'The page may refresh several times. Please wait while the task is being completed.';
$txt['arcade_maintenance_waitTime'] = '%s% complete.  Commencing in %s seconds';
$txt['arcade_maintenance_configfix'] = 'Adjust Active Game Configuration Files';
$txt['arcade_maintenance_dbfix'] = 'Purge Improper Database Entries';
$txt['arcade_maintenance_onlinePurge'] = 'Purge Arcade Online';
$txt['arcade_maintenance_downloadPurge'] = 'Purge DL Directory';
$txt['arcade_maintenance_uploadPathPurge'] = 'Purge UL Path Of Compressed Archives';
$txt['arcade_maintenance_uploadPathPurgeEmpty'] = 'Purge UL Path Of Empty Folders';
$txt['arcade_maintenance_uploadPathPurgeAll'] = 'Purge UL Path Of All Files/Folders';
$txt['arcade_maintenance_uploadPurge'] = 'Purge Games Path Of Compressed Archives';
$txt['arcade_maintenance_pathPurge'] = 'Purge Games Path Of Unused Game Folders';
$txt['arcade_maintenance_rufflePathPurge'] = 'Purge Ruffle Path Of Compressed Archives';
$txt['arcade_maintenance_shoutboxPurge'] = 'Purge Shouts In Shoutbox';
$txt['arcade_maintenance_iconPurge'] = 'Purge Unused Category Icons';
$txt['arcade_maintenance_jsInsert'] = 'Adjust JS File Insertion Settings';
$txt['arcade_maintenance_gametextfix'] = 'Fix game help and descriptions';
$txt['arcade_maintenance_gamethumbfix'] = 'Fix game thumbnails';
$txt['arcade_maintenance_adjustIncrement'] = 'Adjust Skin/List Increments';
$txt['arcade_maintenance_biosPurge'] = 'Purge Temporary Bios Files';
$txt['arcade_maintenance_biosMainPurge'] = 'Purge Main Bios Files';
$txt['arcade_maintenance_main'] = 'Main';
$txt['arcade_maintenance_highscore'] = 'Highscore';
$txt['arcade_maintenance_category'] = 'Categories';
$txt['arcade_maintenance_xframe'] = 'Server Config';
$txt['arcade_maintenance_ruffle'] = 'Ruffle Update';
$txt['arcade_maintenance_ejs'] = 'EmulatorJS Update';
$txt['arcade_ruffle_upload'] = 'Update Ruffle Flash Emulator';
$txt['ArcadeFixCats'] = 'Fix Categories';
$txt['arcade_cats_default'] = 'Set All Games To Default';
$txt['arcade_cats_undefault'] = 'Set All Unassigned Games To Default';
$txt['arcade_cats_peruse'] = 'Fix Category Game Counts';
$txt['arcade_maintenance_cronjobs'] = 'Scheduled Tasks';
$txt['arcade_maintenance_enhanceimages'] = 'Enhance Images';
$txt['arcade_maintenance_enhancethumbs'] = 'Enhance Thumbnails';
$txt['arcade_maintenance_enhancecovers'] = 'Enhance Cover Art';
$txt['arcade_thumb_percent'] = 'Thumbnail Processing: ';
$txt['arcade_cover_percent'] = 'Cover Art Processing: %s';
$txt['arcade_maintenance_engine'] = 'DB Engine Config';

$txt['arcade_commence_now'] = 'Complete Action';
$txt['arcade_admin_opt_cat'] = 'Opt Category';
$txt['arcade_admin_opt_cat_title'] = 'Default Category:';
$txt['arcade_commence_task'] = 'Perform Task';
$txt['arcade_maintain_configWait'] = 'Fixing game configuration files, please wait...';
$txt['arcade_maintain_configInput'] = 'CONTINUE';
$txt['arcade_maintain_configContinue'] = 'Click to continue file changes:';
$txt['arcade_maintain_configFinished'] = 'Finished configuring files.';
$txt['arcade_maintain_continue'] = 'Please press [CONTINUE]';
// EmulatorJS
$txt['arcade_maintenance_romUploadPathPurge'] = 'Purge ROM UL Path Of Compressed Archives';
$txt['arcade_maintenance_romUploadPathPurgeEmpty'] = 'Purge ROM UL Path Of Empty Folders';
$txt['arcade_maintenance_romUploadPathPurgeAll'] = 'Purge ROM UL Path Of All Files/Folders';
$txt['arcade_maintenance_romUploadPurge'] = 'Purge ROM Path Of Compressed Archives';
$txt['arcade_maintenance_romPathPurge'] = 'Purge ROM Path Of Unused Game Folders';
$txt['arcade_maintenance_emulatorPathPurge'] = 'Purge ROM Path Of Compressed Archives';
$txt['arcade_maintenance_emulatorPathPurgeAll'] = 'Purge Emulator Paths Of Compressed Archives';

// Maintenance confirmation
$txt['arcade_maintenance_conf_fixScores'] = 'This will attempt to repair all score tables.';
$txt['arcade_maintenance_conf_filefix'] = 'This will rename all main game files & game icons to lowercase.';
$txt['arcade_maintenance_conf_htmlfilefix'] = 'This will attempt to rename all main game files from ~.htm to ~.html.';
$txt['arcade_maintenance_conf_dbfix'] = 'This will purge all games in the database that do not have corresponding directories/files. Any related matches/tournaments and/or scores will also be purged in the process.';
$txt['arcade_maintenance_conf_purgeScores'] = 'This will erase all scores in the database.';
$txt['arcade_maintenance_conf_updateGamecache'] = 'This will clear the entire SMF-Arcade cache.';
$txt['arcade_maintenance_conf_clearDbSessions'] = 'This will clear any stored SMF database sessions.';
$txt['arcade_maintenance_conf_onlinePurge'] = 'This will purge the Arcade online log.';
$txt['arcade_maintenance_conf_downloadPurge'] = 'This will purge the Arcade download directory of any files.';
$txt['arcade_maintenance_conf_uploadPurge'] = 'This will purge the Games directory of compressed files.';
$txt['arcade_maintenance_conf_uploadPathPurge'] = 'This will purge the games_upload directory of compressed files.';
$txt['arcade_maintenance_conf_uploadPathPurgeEmpty'] = 'This will purge the games_upload directory of all empty folder paths.';
$txt['arcade_maintenance_conf_uploadPathPurgeAll'] = 'This will purge the games_upload directory of all files and folders.';
$txt['arcade_maintenance_conf_pathPurge'] = 'This will purge the Games directory of game folders not being used by any game in the database.';
$txt['arcade_maintenance_conf_rufflePathPurge'] = 'This will purge the Ruffle directory of any compressed archives.';
$txt['arcade_maintenance_conf_shoutboxPurge'] = 'This will purge the Arcade shoutbox of all shouts.';
$txt['arcade_maintenance_conf_iconPurge'] = 'This will purge the arc_icons folder of any files not currently being used by the arcade.';
$txt['arcade_maintenance_conf_configfix'] = 'This will reconfigure configuration files for all enabled games.';
$txt['arcade_maintenance_conf_jsInsert'] = 'This will adjust the JavaScript insertion setting for all your current games to your selected default.';
$txt['arcade_maintenance_conf_gametextfix'] = 'This will attempt to remove any unusual characters from all game help & descriptions.';
$txt['arcade_maintenance_conf_gamethumbfix'] = 'This will attempt to fix and/or adjust any unusual characters from all game thumbnail file names.';
$txt['arcade_maintenance_conf_adjustIncrement'] = 'This will attempt to adjust the auto-increment values to their next highest progression on the following DB tables:\r\n "arcade_skins", "arcade_lists", "arcade_mobile_skins" & "arcade_mobile_lists".\r\n This action may be warranted if many skins or lists were repeatedly installed and uninstalled but this action is not entirely necessary in most circumstances.';
$txt['arcade_maintenance_conf_biosPurge'] = 'This will manually delete all temporary bios files.';
$txt['arcade_maintenance_conf_biosMainPurge'] = 'This will manually delete all main bios files.';
$txt['arcade_maintenance_conf_enhancethumbs'] = 'This will attempt to improve the quality of all PNG|JPG thumbnails using Imagick.<br><br>You can opt to backup your current images in case of any issues but this will take up extra disk space. Previous backups will not be overwritten.<br><br>If you have a lot of games, want a backup and are worried about ample disk space you should backup all your games using other means prior to commencing this procedure.<br><br>The progress will be displayed in the button itself & it is recommended to be patient and allow the process to complete.';
$txt['arcade_maintenance_conf_enhancecovers'] = 'This will attempt to improve the quality of all PNG|JPG cover art using Imagick.<br><br>You can opt to backup your current images in case of any issues but this will take up extra disk space. Previous backups will not be overwritten.<br><br>If you have a lot of games, want a backup and are worried about ample disk space you should backup all your games using other means prior to commencing this procedure.<br><br>The progress will be displayed in the button itself & it is recommended to be patient and allow the process to complete.';

$txt['arcade_maintenance_conf_options'] = 'BACKUP|NO BACKUP|RESTORE|CANCEL';
$txt['arcade_maintenance_conf_descript'] = '- OR -|[BACKUP] Backup original images during this procedure|[NO BACKUP] Process images without making backups|[RESTORE] Restore all previous image backups';
$txt['arcade_maintenance_engine_options'] = 'EXECUTE|CANCEL';
$txt['arcade_maintenance_engine_conf_title'] = 'DATABASE ENGINE CONFIGURATION';
$txt['arcade_maintenance_engine_conf_notes'] = 'Note: Changes only affect SMF-Arcade related tables.';
$txt['arcade_maintenance_engine_conf'] = 'Recommended procedure:<br>&bull; backup all arcade tables<br>&bull; choose the most suitable option<br>&bull; confirm your choice from this window<br><br>Do you want to proceed with these database changes?';

//EmulatorJS
$txt['arcade_maintenance_conf_romUploadPurge'] = 'This will purge the ROM Games directory of compressed files.';
$txt['arcade_maintenance_conf_romUploadPathPurge'] = 'This will purge the games_rom_upload directory of compressed files.';
$txt['arcade_maintenance_conf_romUploadPathPurgeEmpty'] = 'This will purge the games_rom_upload directory of all empty folder paths.';
$txt['arcade_maintenance_conf_romUploadPathPurgeAll'] = 'This will purge the games_rom_upload directory of all files and folders.';
$txt['arcade_maintenance_conf_romPathPurge'] = 'This will purge the ROM Games directory of game folders not being used by any game in the database.';
$txt['arcade_maintenance_conf_emulatorPathPurge'] = 'This will purge the games_emulator_archives directory of any compressed archives.';


// X-Frame Maintenance
$txt['arcade_xframe_iis'] = 'Microsoft IIS';
$txt['arcade_xframe_apache'] = 'Apache';
$txt['arcade_xframe_litespeed'] = 'LiteSpeed';
$txt['arcade_xframe_other'] = 'Other';
$txt['arcade_xframe_option'] = 'Option';
$txt['arcade_xframe_exit'] = 'Back';
$txt['arcade_xframe_detected'] = '%s has been detected.';
$txt['arcade_xframe_file_task'] = '%s file has been %s';
$txt['arcade_xframe_file'] = array('created', 'deleted');
$txt['arcade_xframe_config'] = 'You should use the %s option, otherwise you can opt to adjust X-Frame and MIME settings within your server control panel.<br>Either way {Mod Headers & MIME} module must be loaded for this to work.';
$txt['arcade_xframe_remove'] = 'There is an option to remove the files if this somehow causes an issue.';
$txt['arcade_xframe_create'] = array('Apache: Create .htaccess files', 'Microsoft IIS: Create web.config files', 'Other: View optional configuration instructions', 'Delete: Remove any configuration files located in the %s directory.');
$txt['arcade_xframe_create_litespeed'] = 'LightSpeed: Create .htaccess files';

// DB-Engine maintenance
$txt['arcade_db_engine_headings'] = ['DATABASE', 'SMF', 'SMF-ARCADE'];
$txt['arcade_db_engine_detected'] = '%s defaut database engine has been detected';
$txt['arcade_smf_engine_detected'] = '%s engine for the SMF members table has been detected';
$txt['arcade_engine_detected'] = '%s engine for the SMF-Arcade games table has been detected';
$txt['arcade_engine_task'] = '%s engine has been applied to SMF-Arcade tables';
$txt['arcade_engine_config'] = 'The default database engine vs. the SMF installation engine may differ.<br>You can opt either one and change it later if there are database errors but should opt the displayed engine if both values are identical.';
$txt['arcade_engine_only'] = 'These changes are only applied to database tables native to SMF-Arcade.';
$txt['arcade_engine_option'] = 'Option';

// Highscore Maintenance
$txt['arcade_remove_scores_older_than'] = 'Remove scores that are older than';
$txt['arcade_remove_scores_days'] = 'days.';
$txt['arcade_remove_all_scores'] = 'Remove all scores.';
$txt['arcade_remove_now'] = 'Remove Now';
$txt['arcade_remove_scores_log_variable'] = 'Removed arcade scores older than %s days';
$txt['arcade_remove_scores_log_all'] = 'Removed all arcade scores';

// Editor
$txt['arcade_manage_games_edit_games'] = 'Edit Games';
$txt['arcade_manage_games_rom_edit_games'] = 'Edit ROM Games';
$txt['arcade_basic_settings'] = 'Basic Settings';
$txt['arcade_thumbnail'] = 'Thumbnail';
$txt['arcade_thumbnail_small'] = 'Thumbnail (small)';
$txt['arcade_enable_game'] = 'Enable Game';
$txt['arcade_description'] = 'Description';
$txt['arcade_help'] = 'Help';
$txt['arcade_membergroups'] = 'Membergroups';
$txt['arcade_category'] = 'Category';
$txt['arcade_no_category'] = '(no category)';
$txt['check_all'] = 'Check All';
$txt['arcade_cover_icon'] = 'Game Cover Icon';

$txt['arcade_advanced'] = 'Advanced Settings';
$txt['arcade_internal_name'] = 'Internal Name';
$txt['arcade_directory'] = 'Directory';
$txt['arcade_file'] = 'File';
$txt['arcade_game_type'] = 'Game Type';
$txt['arcade_jsfile_insertion'] = 'Javascript File Insertion';
$txt['arcade_game_type_legacy'] = 'Legacy (SMFArcade v1, phpBB, IPB)';
$txt['arcade_game_type_v2'] = 'SMFArcade v2';
$txt['arcade_game_type_flash'] = 'Flash';
$txt['arcade_score_type'] = 'Scoring';
$txt['arcade_score_normal'] = 'Normal';
$txt['arcade_score_reverse'] = 'Reverse';
$txt['arcade_score_none'] = 'None';
$txt['arcade_js_insertion'] = 'Auto|Enabled|Disabled';
$txt['arcade_rom_system'] = 'ROM System';

$txt['arcade_extra_options_flash'] = 'Template Settings';
$txt['arcade_extra_options_width'] = 'Width';
$txt['arcade_extra_options_height'] = 'Height';
$txt['arcade_extra_options_version'] = 'Flash Version';
$txt['arcade_extra_options_backgroundcolor'] = 'Background color';
$txt['arcade_extra_options_type'] = 'Fullscreen Mode';
$txt['arcade_extra_options_link'] = 'Optional External Link';
$txt['arcade_extra_data_type_normal'] = 'Normal';
$txt['arcade_extra_data_type_full'] = 'Fullscreen';
$txt['game_info_export'] = 'Export file: <span style="padding-left: 0.3em;font-weight: 800;">%s</span>';
$txt['arcade_icon_position_hide'] = 'Mobile Full Screen Icon';
$txt['arcade_icon_position'] = 'Mobile Icon Position';
$txt['arcade_icon_position_bot_left'] = 'Bottom left';
$txt['arcade_icon_position_bot_right'] = 'Bottom right';
$txt['arcade_icon_position_top_left'] = 'Top left';
$txt['arcade_icon_position_top_right'] = 'Top right';
$txt['arcade_icon_position_hide_click'] = 'Click & Hide';
$txt['arcade_icon_position_hide_enable'] = 'Enable';
$txt['arcade_icon_position_hide_disable'] = 'Disable';
$txt['arcade_enable_download_game'] = 'Allow Download';

$txt['arcade_toggle_compression'] = 'Compress ROM Archive';
$txt['arcade_toggle_decompression'] = 'Decompress ROM Archive';

// Game Installer
$txt['arcade_install_games'] = 'Install Games';
$txt['arcade_manage_games_install'] = 'Manage Installations';
$txt['arcade_manage_games_upload'] = 'Manage Uploads';
$txt['arcade_following_games_install'] = 'Following games will be installed.<br />
To add more games goto list and click install on game you want to install.<br />
To edit game settings click name of game, when you are done press "Install Games" to complete process.';
$txt['install_move_files'] = 'Move each game to its own directory';
$txt['arcade_submit_system'] = 'Submit System';
$txt['arcadeUploadSystem'] = 'HTML5 Chunk-Upload Script';
$txt['install_category'] = 'Optional Category: ';
$txt['arcade_override_limit'] = 'Override Limit';
$txt['arcade_translate_limit'] = 'Note: recommend 15 games max due to translation script or override';

$txt['arcade_install_status'] = 'Status';
$txt['arcade_install_success'] = 'Success';
$txt['arcade_install_failed'] = 'Failed';

$txt['arcade_directory_make_exists'] = 'Game folder already exists and contains files - attempting to remove compressed files ~ installation aborted. Folder(s): %s';
$txt['directory_make_failed'] = 'Failed to make directory: %s';
$txt['arcade_decompress_error'] = 'Installlation aborted due to decompression error for file: %s';
$txt['file_move_failed'] = 'Failed to move file %s to %s';
$txt['arcade_folder_rename_failed'] = 'Game directory exists or renaming folder path failed.';
$txt['arcade_folder_deletion'] = 'Directory automatically deleted: %s';
$txt['file_move_fail_message'] = 'Check file and directory permission and try again and/or move files manually';
$txt['arcade_try_again'] = 'Try Again';
$txt['arcade_rom_db_failure'] = 'Paths created but DB insertion failed for game: ';
$txt['arcadeUploadSystemMsg'] = 'This feature is always enabled by default\r\nIf Mod-Security is shown as detected, you may need to use FTP to upload game archives.\r\nContact SMF-Arcade for more support.';

$txt['arcade_move_games'] = 'Move each game to its own directory';
$txt['arcade_are_you_sure_install'] = 'Are you sure you want to install these games?';
$txt['arcade_are_you_sure_delete'] = 'Are you sure you want to delete these files?';

$txt['arcade_game_control_mouse_key'] = 'Mouse and keyboard control.';
$txt['arcade_game_control_mouse'] = 'Mouse control.';
$txt['arcade_game_control_key'] = 'Keyboard control.';
$txt['arcade_game_control_mouse_touch'] = 'Mouse or touch control.';
$txt['arcade_game_control_key_touch'] = 'Keyboard or touch control.';

// Game uninstall
$txt['arcade_uninstall_games'] = 'Uninstall Games';
$txt['arcade_following_games_uninstall'] = 'The following games will be uninstalled.<br />
All data related to games will be permanently removed and the only way to get it back is to restore from backup.<br />
Press "Uninstall Games" to confirm and complete the process.';
$txt['arcade_games_uninstall_remove_files'] = 'Remove files related to game(s)';
$txt['arcade_games_uninstall_theme_files'] = 'Check all theme file boxes';

// Category editor
$txt['arcade_manage_category'] = 'Manage Category';
$txt['arcade_manage_category_new'] = 'New Category';
$txt['arcade_manage_category_list'] = 'Category List';
$txt['arcade_manage_category_desc'] = 'Here you can create and edit categories for games';
$txt['arcade_categories'] = 'Categories';
$txt['category_name'] = 'Name';
$txt['arcade_save_category'] = 'Edit';
$txt['arcade_unable_to_remove'] = 'Unable to remove category %s';
$txt['arcade_category_no_default'] = 'There is no default category set.';
$txt['arcade_category_permission_allowed'] = 'Groups Allowed To Access';
$txt['arcade_make_default'] = 'Make default';
$txt['arcade_upload_cat'] = 'Upload Category Icon';
$txt['arcade_cat_image_icon'] = 'Image :';
$txt['arcade_cat_image_filename'] = 'Filename :';
$txt['arcade_upload_exists'] = '%s ~ file exists.';
$txt['arcade_upload_path_exists'] = '%s ~ aborted because game already exists.';
$txt['arcade_cat_delete'] = 'Delete';
$txt['arcade_cat_order'] = 'Order';
$txt['arcade_cat_name'] = 'Category Name';
$txt['arcade_cat_image'] = 'Icon';
$txt['arcade_cat_image_na'] = 'N/A';
$txt['arcade_cat_disable_dl'] = 'Disable Game Downloads';
$txt['arcade_cat_js_insert'] = 'Enable JavaScript File Insertion';


/* Arcade - Advanced Text Variables */
$txt['arcade_newgame_notification'] = 'Enable New Game PM/Email Notifications';
$txt['arcadeEnablePosting'] = 'Enable Forum Game Posting';
$txt['gamesBoard'] = 'Board ID Number';
$txt['gamesMessage'] = 'Posting Statement';
$txt['arcadeEnableIframe'] = 'Enable Popup/Fullscreen Inside Post/PM';
$txt['arcadeEnablePostCount'] = 'Enable Post Count';
$txt['arcadePosterid'] = 'Auxiliary User ID';
$txt['arcadeDescriptLength'] = 'Visible Description Length';
$txt['arcadeEnableDownload'] = 'Enable Game Downloading';
$txt['arcadeEnableReport'] = 'Enable Reporting Game Errors';
$txt['arcadeEnableGameDisable'] = 'Disable Games Reported With Errors';
$txt['arcadeEnableReportNotification'] = 'Enable Notifications For Reports';
$txt['arcadeDownPost'] = 'Number Of Posts To Allow Downloading';
$txt['arcadeDownloadPermission'] = 'Groups Allowed To Download Games';
$txt['arcadeDisableArchive'] = 'Disable Saving Archives In The Download Folder';
$txt['arcadeDownPass'] = 'Prefix For Restricted Game Files';
$txt['pdl_DownMax'] = 'Global Daily Download Limit';
$txt['arcade_MembergroupDownMax'] = 'Daily Download Limit For: %s';
$txt['arcadeGroupLimits'] = '<span style="font: italic bold 12px Georgia, serif;">Membergroups Daily Download Limit<br />(Highest Precedence || 0 = Unlimited)</span>';
$txt['pdl_DownMax_Info'] = 'This limit will take effect if no membergroup limit exists.<br />[ 0 = unlimited ]';
$txt['pdl_settings_desc'] = 'Here you can edit SMF Arcade advanced settings.';
$txt['pdl_admin_settings'] = 'SMF Arcade Advanced Settings';
$txt['pdl_reports_desc'] = 'Here you can view and edit reported game errors';
$txt['pdl_admin_reports'] = 'Reported Game Errors';
$txt['arcade_admin_guide'] = 'Arcade Hooks Guide';
$txt['arcadeShowIC'] = 'Info Center';
$txt['arcadeShowOnline'] = 'Arcade Online List';
$txt['arcadeAdjustType'] = 'Auto Adjust Improper Save Types';
$txt['arcade_download'] = 'Download HTML5/Flash games';
$txt['arcade_download_rom'] = 'Download ROM games';
$txt['arcade_general_pdl_reports'] = 'Reports';
$txt['arcade_pdl_reps'] = 'Games With Reported Errors';
$txt['pdl_submit'] = 'Submit';
$txt['pdl_maintain1'] = '<img style="vertical-align: middle;" src="' . $boardurl . '/Themes/default/images/arc_icons/pdl_clean.gif" alt="CLEAN" title="Clear Cache" />';
$txt['pdl_nonenable1'] = '<img style="vertical-align: middle;" src="' . $boardurl . '/Themes/default/images/arc_icons/cancel.png" alt="DISABLE" title="Keep Game Disabled" />';
$txt['pdl_maintain2'] = 'Maintain';
$txt['pdl_nonenable2'] = 'Disabled';
$txt['pdl_test'] = 'Test';
$txt['pdl_reports_id'] = 'Game ID';
$txt['pdl_reports_type'] = 'Type';
$txt['pdl_reports_name'] = 'Game Name';
$txt['pdl_reports_userid'] = 'Reported By';
$txt['pdl_reports_year'] = 'Year';
$txt['pdl_reports_month'] = 'Month';
$txt['pdl_reports_day'] = 'Day';
$txt['pdl_reports_reason'] = 'Reason';
$txt['pdl_report_reason_default'] = 'No reason or input was invalid.';
$txt['pdl_report_reason_name'] = 'Game name: ';
$txt['pdl_report_reason_input'] = 'What is wrong with the game?';
$txt['pdl_reports_repid'] = 'Report &#35;';
$txt['pdl_reports_dcount'] = '&#8681; Count';
$txt['pdl_reports_delete'] = 'Delete';
$txt['show_pdl_report'] = 'Game Error Reported';
$txt['pdl_reports_toggle'] = 'Enable/Disable &#8681;';
$txt['pdl_dl_status'] = '&#8681; Status';
$txt['pdl_dl_enabled'] = 'Enabled';
$txt['pdl_dl_disabled'] = 'Disabled';
$txt['arcade_compression'] = (class_exists('ZipArchive') ? 'zip|' : '') . 'tar|tar.gz|rar';
$txt['arcade_gz'] = 'Default Download Archive Type';
$txt['arcade_gz_user'] = 'Allow User To Change Download Archive Type';
$txt['arcade_adm_board_not_exist'] = 'The selected board does not exist';
$txt['arcade_adm_board_do_exist'] = 'The selected board is currently valid';
$txt['arcade_adm_user_not_exist'] = 'The selected user does not exist';
$txt['arcade_adm_user_do_exist'] = 'The selected user is currently valid';
$txt['arcade_adm_disabled'] = 'Option is currently disabled';
$txt['arcadeDownloadWinRarDir'] = 'WinRAR directory';
$txt['arcadeDownloadWinRarDirSuggest'] = 'Browse: %s';
$txt['arcadeShowOS'] = array('Unix', 'Windows');
$txt['arcadeDownloadUnixRarDir'] = '%s OS detected';
$txt['arcadeDownloadUnixRarDirSuggest'] = 'RAR compression detection: %s';
$txt['arcadeDownloadRarDetected'] = '%s RAR Available';
$txt['arcadeDownloadRarNotDetected'] = '%s RAR Not Available';
$txt['arcadeDownloadShell'] = 'RAR compression disabled';
$txt['arcadeDownloadShellEnable'] = 'Enable RAR Compression Support If Available';
$txt['arcadeDownloadHideLink'] = 'Hide Download Link/Button For No Permissions';
$txt['arcadeButtonPlacement'] = 'Navigation Placement';
$txt['arcadeButtonPlacementExtremity'] = 'Navigation Fringe';
$txt['arcadeButtonSequenceValues'] = 'Before|After';
$txt['arcadeButtonPlacementExtremities'] = 'Specific|First|Last|Index';
$txt['arcadeButtonSequence'] = 'Navigation Sequence';
$txt['arcadeButtonIndex'] = 'Button Index';

// Errors
$txt['arcade_no_games_selected'] = 'No games selected!';
$txt['arcade_no_rom_games_selected'] = 'No ROM games selected!';
$txt['arcade_no_games_selected1'] = '(1) No games selected!';
$txt['arcade_no_games_selected2'] = '(2) No games selected!';
$txt['arcade_no_games_selected3'] = '(3) No games selected!';
$txt['arcade_no_games_selected4'] = '(4) No games selected!';
$txt['arcade_no_games_selected5'] = '(5) No games selected!';
$txt['arcade_no_games_selected6'] = '(6) No games selected!';
$txt['arcade_no_games_selected7'] = '(7) No games selected!';
$txt['arcade_no_games_selected8'] = '(8) No games selected!';
$txt['arcade_not_writable'] = 'Directory %s is not writable and chmod didn\'t succeed. Please make it writable manually.';
$txt['arcade_unable_to_move'] = 'Unable to move %s from %s to %s, please do it manually and reload!';
$txt['arcade_upload_file'] = 'Uploading file failed!';
$txt['arcade_upload_remote_thumbnail_error'] = 'Failed to fetch remote sourced thumbnail or destination directory is incorrect.';
$txt['arcade_generalized_file_error'] = 'Destination: %s';
$txt['arcade_generalized_file_error_unknown'] = 'Unknown';
$txt['arcade_upload_file_size'] = 'Uploading file failed: 0 Byte file';
$txt['arcade_upload_tar'] = 'php.ini settings for upload_max_filesize and/or post_max_size not enough: %s';
$txt['arcade_install_general_fail'] = 'Game installation failed!';
$txt['arcade_install_exists_fail'] = 'Game directory already exists for another game in the database.<br />Game installation failed for: %s';
$txt['arcade_install_exists_commence'] = 'Game directory already exists for another game in the database.<br />Game installation conflict flagged for: %s';
$txt['arcade_install_exists_fail_del'] = 'Game installation failed!<br />Game directory already exists for another game in the database.<br />Attempting to remove compressed files ~ Please try again!';
$txt['unable_to_make'] = 'Unable to make directory "%s". Please do it manually!';
$txt['unable_to_chmod'] = 'Directory "%s" is not writable and chmod failed, please use FTP client to make it writable';
$txt['unable_to_move'] = 'Unable to move directories from "%s" to "%s". Please do it manually!';
$txt['arcade_game_install_error'] = 'Game decompression error for: %s.<br />This file appears to be corrupted.<br />Try uploading the file manually via FTP.';
$txt['arcade_ruffle_install_error'] = 'Ruffle decompression error for: %s.<br />This file appears to be corrupted.<br />Try uploading the decompressed files manually via FTP.';
$txt['arcade_translate_error'] = '[Translation error: %s] [%s -> %s]';
$txt['arcade_ruffle_update_error'] = 'Failed to connect to location: %s';
$txt['arcade_arch_install_error'] = 'ROM decompression error for: %s.<br />This file appears to be corrupted.<br />Try uploading the decompressed files manually via FTP.';
$txt['arcade_arch_update_error'] = 'Failed to connect to location: %s';
$txt['arcade_function_disabled'] = 'An unknown error occurred – please try again later.';
$txt['arcade_upload_romcore_file'] = 'The EmulatorJS archive file being used does not contain the proper data!';
$txt['arcade_upload_rufflecore_file'] = 'The Ruffle archive file being used does not contain the proper data!';
$txt['arcade_upload_romcore_remote_err'] = 'The remote file transfer has failed!<br><br>Please attempt to download the file again or do it manually.';
$txt['arcade_upload_romcore_transfer_err'] = 'The file decompression has failed!<br><br>Please check directory permissions or do it manually.';
$txt['arcade_emulator_core_upload_errors'] = 'Failed to move uploaded file|Failed to open input stream|Failed to open output stream';
$txt['arcade_emulator_main_decompress_error'] = 'Decompress %s ~ unzip failure: %s';
$txt['arcade_emulator_cores_decompress_error'] = 'Decompress emulator archive %s ~ unzip failure: %s';
$txt['arcade_zip_error_unkown'] = 'Unknown ZipArchive error';
$txt['arcade_arch_abort'] = 'Error Detected ~ Aborting Transfer';
$txt['arcade_error_thumbnail'] = 'Error: Thumbnail file does not exist or restricted characters in file name';
$txt['arcade_errorfile_thumbnail'] = 'Error: Uploaded thumbnail file is wrong file type and/or upload failure.';
$txt['arcade_error_thumbnail_small'] = 'Error: Small thumbnail file does not exist or restricted characters in file name';
$txt['arcade_errorfile_thumbnail_small'] = 'Error: Uploaded thumbnail file is wrong file type and/or upload failure.';
$txt['arcade_warning_no_gameimage'] = 'Warning: Using default image due to invalid/no file selected.';
$txt['arcade_error_cover_icon'] = 'Error: Cover icon file does not exist or restricted characters in file name';
$txt['arcade_errorfile_cover_icon'] = 'Error: Uploaded cover icon file is wrong file type and/or upload failure.';
$txt['arcade_emulator_bios_decompress_error'] = 'Decompress bios archive %s ~ unzip failure: %s';
$txt['arcade_generalized_undetected'] = 'Undetected';

$txt['arcade_zip_error'] = [
	ZipArchive::ER_EXISTS => 'File already exists.',
	ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
	ZipArchive::ER_INVAL => 'Invalid argument.',
	ZipArchive::ER_MEMORY => 'Malloc failure.',
	ZipArchive::ER_NOENT => 'No such file.',
	ZipArchive::ER_NOZIP => 'Not a zip archive.',
	ZipArchive::ER_OPEN => "Can't open file.",
	ZipArchive::ER_READ => 'Read error.',
	ZipArchive::ER_SEEK => 'Seek error.',
];

// Custom skins & Lists
$txt['custom_skin_default'] = 'Skin #%s';
$txt['custom_list_default'] = 'List #%s';
$txt['custom_mobile_skin_default'] = 'Mobile Skin #%s';
$txt['custom_mobile_list_default'] = 'Mobile List #%s';

// Defiant Skin
$txt['arcadeTabs'] = 'Enable Toolbar Tabs';
$txt['arcadeAltTemplate'] = '[Click here for custom theme options]';
$txt['arcade_save_alt'] = 'Save Theme Settings';
$txt['arcade_back_alt'] = 'Return To Settings Page';
$txt['arcade_shout_interval'] = 'Shoutbox Refresh Interval';
$txt['arcade_shout_interval_recommend'] = '10 to 40 seconds is recommended | 0 to disable';
$txt['arcade_shout_widthB'] = 'Shoutbox Width';
$txt['arcade_shout_width_percent'] = 'Use a percentage integer value';
$txt['skin_best_playersB'] = 'Set Defiant Best Players';
$txt['skin_latest_gamesB'] = 'Set Defiant Latest Games';
$txt['skin_latest_champsB'] = 'Set Defiant Latest Champions';
$txt['skin_most_popularB'] = 'Set Defiant Most Played';
$txt['skin_longest_champsB'] = 'Set Defiant Longest Champions';
$txt['arcadeGamesNameLengthB'] = 'Max Game Name Char Length (Defiant)';
$txt['skin_avatar_sizeb_width'] = 'Set Defiant Avatar Max Width';
$txt['skin_avatar_sizeb_height'] = 'Set Defiant Avatar Max Height';
$txt['avsizeB_recommend'] = 'Recommend (30) don\'t overdo it';

// Permission names and help
$txt['permissiongroup_arcade'] = 'Arcade';
$txt['permissionname_arcade_view'] = 'View Arcade';
$txt['permissionhelp_arcade_view'] = 'May access Arcade and use basic features like search and view highscores';
$txt['permissionname_arcade_play'] = 'Play on Arcade';
$txt['permissionhelp_arcade_play'] = 'Allows member to play, on Arcade, games which they have rights to';
$txt['permissionname_arcade_admin'] = 'Administrate arcade';
$txt['permissionhelp_arcade_admin'] = 'Arcade Administrator can Install/Edit/Delete game, Delete scores and edit settings for Arcade';
$txt['permissionname_arcade_submit'] = 'Save scores';
$txt['permissionhelp_arcade_submit'] = 'Allows users to save their scores.';
$txt['permissionname_arcade_comment'] = 'Edit Comments';
$txt['permissionhelp_arcade_comment'] = 'Allows user to edit comments, ';
$txt['permissionname_arcade_comment_own'] = 'Own';
$txt['permissionname_arcade_comment_any'] = 'Any';
$txt['permissionname_arcade_user_stats'] = 'View User Statistics';
$txt['permissionname_arcade_user_stats_own'] = 'Own';
$txt['permissionname_arcade_user_stats_any'] = 'Any';
$txt['permissionname_arcade_view_arena'] = 'View Arena';
$txt['permissionname_arcade_create_match'] = 'Create a new match on Arena';
$txt['permissionname_arcade_join_match'] = 'Join match on Arena';
$txt['permissionname_arcade_join_invite_match'] = 'Join match on Arena when invited';
$txt['permissionname_arcade_edit_settings'] = 'Edit Arcade Settings';
$txt['permissionname_arcade_edit_settings_own'] = 'Own';
$txt['permissionname_arcade_edit_settings_any'] = 'Any';
$txt['permissionname_arcade_online'] = 'View Arcade Online';
$txt['permissionname_arcade_download'] = 'Download Games';
$txt['permissionname_arcade_download_rom'] = 'Download ROM games';
$txt['permissionname_arcade_download_type'] = 'Adjust Archive Type';
$txt['permissionname_arcade_new_game'] = 'Allow new game notification';
$txt['permissionname_arcade_report'] = 'Report Game Errors';
$txt['permissionname_arcade_skin'] = 'Change Skin';
$txt['permissionhelp_arcade_skin'] = 'Allow users to change the Skin of the arcade';
$txt['permissionname_arcade_list'] = 'Change List';
$txt['permissionhelp_arcade_list'] = 'Allow users to change the List of the arcade';
$txt['permissionname_arcade_hyperlink'] = 'Shout Hyperlinks';
$txt['permissionhelp_arcade_hyperlink'] = 'Allow users to post hyperlinks in the shoutbox';
$txt['permissionname_arcade_view_hyperlink'] = 'View Hyperlinks';
$txt['permissionhelp_arcade_view_hyperlink'] = 'Allow users to view hyperlinks in the shoutbox';
$txt['perm_arcade_online'] = 'View Arcade Online';
$txt['perm_arcade_download'] = 'Download Games';
$txt['perm_arcade_download_rom'] = 'Download ROM Games';
$txt['perm_arcade_new_game'] = 'Allow new game notification';
$txt['perm_arcade_report'] = 'Report Broken Games';
$txt['permissionname_arcade_gametype_select'] = 'Sort By Game Type';
$txt['permissionhelp_arcade_gametype_select'] = 'Allow users to sort games by game types';
$txt['permissionname_arcade_view_retro_arch'] = 'Play ROM Games';
$txt['permissionhelp_arcade_view_retro_arch'] = 'Allow users to play ROM games';

// Simple permission groups
$txt['permissiongroup_simple_arcade'] = 'Use Arcade';
$txt['permissiongroup_simple_arcade_moderate'] = 'Moderate Arcade';

// Simple permission names
$txt['permissionname_simple_arcade_comment_own'] = 'Edit their own comments';
$txt['permissionname_simple_arcade_comment_any'] = 'Edit any comment';
$txt['permissionname_simple_arcade_user_stats_own'] = 'View their own statistics';
$txt['permissionname_simple_arcade_user_stats_any'] = 'View other people\'s statistics';
$txt['permissionname_simple_arcade_edit_settings_own'] = 'Edit their own Arcade Settings';
$txt['permissionname_simple_arcade_edit_settings_any'] = 'Edit other people\'s Arcade Settings';

// Ruffle info
$txt['arcade_ruffle_info'] = 'A 3rd party application dubbed \'Ruffle\' is used for Flash Player emulation.\r\nRuffle runs on all modern browsers through the use of WebAssembly via a sandbox to avoid the security pitfalls that Flash had a reputation for.\r\nUpdates for the Arcade will always include the latest nightly build of Ruffle.';

// internal name conflict
$txt['arcade_conflict_primary'] = 'internal name conflict [primary]';
$txt['arcade_conflict_subsequent'] = 'internal name conflict [subsequent]';
$txt['arcade_conflict_subsequent_list'] = '[WARNING] Games attempted to use this primary internal name: %s';
$txt['arcade_conflict_primary_game'] = '[WARNING] Internal name amended ~ primary internal name exists: %s';
$txt['arcade_conflict_msg_edit'] = 'edit %s';

// EmulatorJS / Ruffle
$txt['arcadeRetroArchEnabled'] = 'Enable EmulatorJS';
$txt['romGamesDirectory'] = 'Path To ROM Games';
$txt['romGamesUrl'] = 'URL To ROM Games';
$txt['romGamesManage'] = 'Manage ROM Games';
$txt['arcade_manage_rom_games_install'] = 'Manage ROM Game Files';
$txt['arcade_manage_rom_games_upload'] = 'Upload ROM Games';
$txt['arcade_supported_rom_filetypes'] = 'Supported file types:';
$txt['arcade_rom_filetypes'] = !empty($arcadeModSettings['arcadeRomGameTypes']) ? $arcadeModSettings['arcadeRomGameTypes'] : 'zip|7z';
$txt['arcade_manage_rom_games'] = 'ROM Games';
$txt['arcade_manage_rom_games_desc'] = 'Here you can manage and delete ROM games';
$txt['arcade_manage_rom_upload_games_desc'] = 'Here you can upload compressed/uncompressed ROM games';
$txt['arcade_manage_retro_arch_desc'] = 'Here you can manage or install %s';
$txt['arcade_manage_retro_arch_get_archive'] = '<div>The %s plugin is now being remotely downloaded to your website...</div><div><span></span></div><div>When the download has completed, you can install that updated file when prompted.</div>';
$txt['arcade_manage_retro_arch_get_archive_local'] = '<div>The %s plugin is now being downloaded to your computer...</div><div><span></span></div><div>When the download has completed, you can upload that file to your server using the [BROWSE] button below.</div>';
$txt['arcade_no_rom_games_available'] = 'No ROM games have been detected';
$txt['arcade_manage_rom_games_rename'] = 'Install Selected Games';
$txt['arcade_are_you_sure_rom_install'] = 'Are you sure you want to install these ROM game files?';
$txt['arcade_are_you_sure_rom_core_del'] = 'Archive files are being detected in the temporary file directory.\r\nAre you sure you want to empty this directory?';
$txt['arcade_are_you_sure_rom_core_del_auto'] = 'You are about to upload a new %s archive which will automatically remove all older archive files.\r\nPress [OK] to proceed with this option...';
$txt['arcade_retro_arch_update'] = 'Update %s';
$txt['arcade_retro_arch_filetypes'] = 'zip | gz';
$txt['arcade_upload_retro_archive'] = '<-- Commencing File Transfers --><br><br><-- Install your new source files file when prompted -->';
$txt['arcade_arch_external_github'] = 'Retrieve GitHub %s Files';
$txt['arcade_arch_external'] = 'Retrieve WebDev %s Files';
$txt['arcade_emulator_del_core'] = 'Remove Existing %s Files';
$txt['arcade_emulator_del_core_title'] = 'Removal of %s Source Files';
$txt['arcade_emulator_io_title'] = 'Remote Transfer of %s I/O Files';
$txt['arcade_io_get_transfer'] = '<-- Commencing BIOS File Transfers --><br><br><-- You confirmed the legality of using these files at your own risk -->';
$txt['arcade_io_get_transfer_done'] = '<-- BIOS files can be deleted from the main Arcade Maintenance page -->';
$txt['arcade_core_removal_opts'] = 'Confirm|Cancel';
$txt['arcade_core_removal_opts_txt'] = '[CONFIRM] to remove all Ruffle source files (Flash/HTML5 games will remain intact)|[CONFIRM] to remove all RuffleJS source files (ROM games will remain intact)|[CANCEL} to abort this action';
$txt['arcade_io_get_opts_txt'] = '<div style="padding-top: 1rem;">[CONFIRM] to retrieve all console BIOS files using your Retro Library Code</div><div style="padding: 1rem 0rem;font-size: 8pt;">These third party sourced files are not necessary for most consoles & their games</div><div style="font-size: 7pt;">This confirmation acknowledges that you understand the legality of using these files at your own risk</div>|[CANCEL} to abort this action';
$txt['arcade_upload_arch_processing'] = 'Processing Files...';
$txt['arcade_emulator_core_upload_ok'] = 'Upload OK';
$txt['arcadeRomToggle'] = 'Display ROM Games';
$txt['arcadeRomCoreBrowse'] = 'BROWSE';
$txt['arcadeRomCoreBusy'] = 'BUSY';
$txt['arcadeRomCoreBrowseReady'] = 'Select Source Update File';
$txt['arcadeRomCoreSelectFile'] = 'Please select a file to upload';
$txt['arcadeRomToggleText'] = 'ROM games can be displayed independently or within the main games list';
$txt['arcadeRomToggleSelect'] = 'Independent|Combined';
$txt['arcadeRomCoreUploadComplete'] = 'The transferring of %s source files has been completed!';
$txt['arcade_core_file_detected'] = 'Install %s Source Files';
$txt['arcade_core_delete'] = 'Delete %s Archive Files';
$txt['arcade_bios_file_detected'] = 'Install EmulatorJS Bios Archives';
$txt['arcade_bios_delete'] = 'Delete EmulatorJS Bios Archives';
$txt['arcade_core_merge'] = 'Process EmulatorJS Source Files';
$txt['arcade_core_transfer_files'] = 'Attempting to decompress the %s archive & transfer files to the emulator directory path...';
$txt['arcade_core_success'] = 'Archive matches criteria, attempting to overwrite files from: %s';
$txt['arcade_core_download_remote'] = '[LOCAL] to download the file to your computer|- OR -|[DIRECT] to transfer the file directly to your website&#039;s temporary archive path';
$txt['arcade_core_download_options'] = 'LOCAL|DIRECT|CANCEL';
$txt['arcade_core_download'] = '%s-Archive Download Options';
$txt['arcade_ruffle_option'] = 'OR';
$txt['arcade_emulator_current_file_date'] = 'Current file version: %s';
$txt['arcade_emulator_remote_file_date'] = 'Available GitHub file version: %s';
$txt['arcade_emulator_no_file_date'] = '%s not detected ~ Please update to the latest version';
$txt['arcade_ruffle_override'] = 'Force Overwrite';
$txt['arcade_emulator_note'] = 'Note:';
$txt['arcade_emulator_symbolic'] = 'The file should be publicly accessible + redirected download links will fail.';
$txt['install_rom_type'] = 'Override ROM Type:';
$txt['arcadeCDN_Availability'] = 'CDN currently available|CDN currently unavailable';
$txt['arcade_file_success'] = 'Success: %s';
$txt['arcade_core_available'] = 'Update Available';
$txt['arcadeCropThumbnails'] = 'Crop Thumbnails';


// EmulatorJS ROM game template Settings (Admin)
$txt['arcade_emulatorjs_disable_msg'] = 'This checkbox is disabled due to console debugging.';
$txt['arcade_emulatorjs_settings'] = 'Settings';
$txt['arcade_emulatorjs_playpause'] = 'Play/Pause';
$txt['arcade_emulatorjs_restart'] = 'Restart';
$txt['arcade_emulatorjs_fullscreen'] = 'Fullscreen';
$txt['arcade_emulatorjs_record'] = 'Screen Record';
$txt['arcade_emulatorjs_gamepad'] = 'Gamepad';
$txt['arcade_emulatorjs_savestate'] = 'Savestate';
$txt['arcade_emulatorjs_loadstate'] = 'Loadstate';
$txt['arcade_emulatorjs_screenshot'] = 'Screenshot';
$txt['arcade_emulatorjs_loadfiles'] = 'Load Files';
$txt['arcade_emulatorjs_savefiles'] = 'Save Files';
$txt['arcade_emulatorjs_qsave'] = 'Quick Save';
$txt['arcade_emulatorjs_qload'] = 'Quick Load';
$txt['arcade_emulatorjs_cheat'] = 'Game Cheats';
$txt['arcade_emulatorjs_cache'] = 'Cache Manager';

// Cronjob Scheduling Maintenance
$txt['arcade_change_cronjobs'] = 'Save Settings';
$txt['arcadeCronSchedule'] = 'Select Main Schedule';
$txt['arcadeCronBiosSchedule'] = 'Aggressive Temporary Bios Deletion';
$txt['arcadeCronScheduleText'] = 'Schedule Automatic SMF-Arcade Maintenance Tasks';
$txt['arcadeCronScheduleSelect'] = 'Disabled|Daily|Weekly|Monthly';
$txt['arcadeCronBiosScheduleSelect'] = 'Disabled|5 minutes|15 minutes|30 minutes|1 hour|6 hours|12 hours';
$txt['arcade_cron_emulator_archives'] = 'Purge Temporary Emulator Archives Path';
$txt['arcade_cron_download'] = 'Purge Game Downloads Path';
$txt['arcade_cron_upload'] = 'Purge Game Uploads Path';
$txt['arcade_cron_romupload'] = 'Purge ROM Game Uploads Path';
$txt['arcade_cron_tempbios'] = 'Delete Temporary Bios Files';

// Help Text
$txt['arcadeEnableStShopPatchHelp'] = '
<span style="font-size: small;display: block;">This will patch versions of ST-Shop that fail to award credits for game scoring.</span>
<span style="font-size: small;display: block;">If ST-Shop is awarding double credits then this option should be <strong>disabled</strong>.</span>';
$txt['arcadeCronScheduleTextHelp'] = '
<div style="overflow: hidden;">
	<div style="text-decoration: underline;font-weight: bold;padding-bottom: 0.25rem;">SMF-Arcade Maintenance Tasks</div>
	<div style="font-style: italic;font-weight: 600;font-size: 7pt;padding-bottom: 0.25rem;">SMF-Arcade creates archives & decompressed archives in folder paths that hold temporary files.</div>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[FIle Functions]</div>
	<ul style="font-size: 8pt;">
		<li>Temporary BIOS archives <span style="font-weight: bold">(compressed)</span></li>
		<li>Downloaded game arhives <span style="font-weight: bold">(compressed)</span></li>
		<li>Uploaded Flash/HTML5 game archives <span style="font-weight: bold">(compressed)</span></li>
		<li>Uploaded ROM game archives <span style="font-weight: bold">(compressed)</span></li>
		<li>Temporary emulator archives <span style="font-weight: bold">(compressed/decompressed)</span></li>
	</ul>
	<div style="padding-bottom: 1rem;"><span></span></div>
	<div style="font-style: italic;font-weight: 600;font-size: 7pt;padding-bottom: 0.25rem;">The system already has things in place for file deletion to not let these temporary files get out of hand.</div>
	<div style="font-style: italic;font-weight: 600;font-size: 7pt;padding-bottom: 0.25rem;">However it is possible for rogue files to be present at times therefore the Arcade provides scheduled maintenance tasks.</div>
	<div style="font-style: italic;font-weight: 600;font-size: 7pt;padding-bottom: 0.25rem;">If for whatever reason you don\'t want the scheduled task to remove any compressed game archives in your upload folder paths, simply leave those options unchecked.</div>
	<div style="padding-bottom: 1rem;"><span></span></div>
</div>';
$txt['arcadeBehaviorCDNTextHelp'] = '
<div style="overflow: hidden;">
	<div style="text-decoration: underline;font-weight: bold;padding-bottom: 0.25rem;">Ruffle/EmulatorJS Behavior</div>
	<div style="font-style: italic;font-weight: 600;font-size: 7pt;padding-bottom: 0.25rem;">Note: Checking CDN availability may result in game loading times being slightly delayed.</div>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[FATAL]</div>
	<ul style="font-size: 8pt;">
	<li>CDN option will <span style="font-weight: bold">not check</span> availablity</li>
	<li>self-hosted option will <span style="font-weight: bold">check</span> availablity</li>
	<li>failed availability check results in <span style="font-weight: bold">FATAL ERROR</span></li>
	</ul>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[TOGGLE]</div>
	<ul style="font-size: 8pt;">
	<li>CDN option will <span style="font-weight: bold">not check</span> availablity</li>
	<li>self-hosted option will <span style="font-weight: bold">not check</span> availablity</li>
	<li>failed availability results in an <span style="font-weight: bold">empty play screen</span></li>
	</ul>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[CHECK]</div>
	<ul style="font-size: 8pt;">
	<li>CDN option will <span style="font-weight: bold">check</span> availablity</li>
	<li>self-hosted option will <span style="font-weight: bold">check</span> availablity</li>
	<li>failed availability results in <span style="font-weight: bold">FATAL ERROR</span></li>
	</ul>
	<div style="padding-bottom: 1rem;"><span></span></div>
</div>';
$txt['arcade_db_glob_settingHelp'] = 'The default value of this MySQL variable should be 16M. <span style="display: block;">If this variable is at a lower value, you may be receiving errors in your PHP error_log.</span><span style="display: block;">You can opt 1 of 2 different syntax to query the value & attempt to adjust it to 16M if it detects a lower setting.</span>';
$txt['arcade_log_emulatorjs_consoleHelp'] = 'Enabling this option will automatically display all settings on the EmulatorJS game template. You may not want your users to have all those options therefore it is advised to only enable this for debugging purposes. <span style="display: block;">While using this option, the EmulatorJS game options will appear to be rendered not available because they are all in affect.</span>';

// X-Frame Instructions
$txt['arcade_xframe_instruct'] = '
<div id="arcade_xframe">
	<div style="padding-bottom: 15px;font-size: 20px;">X-Frame-Options - HTTP | MDN</div>
	<p>The <strong><code>X-Frame-Options</code></strong> <span>HTTP</span> response header can be used to indicate whether or not a browser should be allowed to render a page in a&nbsp;
	<span title="&lt;frame&gt; is an HTML element which defines a particular area in which another HTML document can be displayed. A frame should be used within a &lt;frameset&gt;."><code>&lt;frame&gt;</code></span>,&nbsp;
	<span title="The HTML &lt;iframe&gt; element represents a nested browsing context, effectively embedding another HTML page into the current page. In HTML 4.01, a document may contain a head and a body or a head and a frameset, but not both a body and a frameset. However, an &lt;iframe&gt; can be used within a normal document body. Each browsing context has its own session history and active document. The browsing context that contains the embedded content is called the parent browsing context. The top-level browsing context (which has no parent) is typically the browser window."><code>&lt;iframe&gt;</code></span> or&nbsp;
	<span title="The HTML &lt;object&gt; element represents an external resource, which can be treated as an image, a nested browsing context, or a resource to be handled by a plugin."><code>&lt;object&gt;</code></span>.<span style="display: block;">Sites can use this to avoid <span title="clickjacking" class="external" rel="noopener">clickjacking</span> attacks, by ensuring that their content is not embedded into other sites.</span></p>
	<p>The added security is only provided if the user accessing the document is using a browser supporting <code>X-Frame-Options</code>.</p>
	<table class="properties">
		<tbody>
			<tr>
				<td>Header type</td>
				<td><span title="Response header: A response header is an HTTP header that can be used in an HTTP response and that doesn\'t relate to the content of the message. Response headers, like Age, Location or Server are used to give a more detailed context of the response." class="glossaryLink">Response header</span></td>
			</tr>
			<tr>
				<td><span title="Forbidden header name: A forbidden header name is an HTTP header name that cannot be modified programmatically; specifically, an HTTP request header name." class="glossaryLink">Forbidden header name</span></td>
				<td><span>no</span></td>
			</tr>
		</tbody>
	</table>
	<h2 id="Syntax">Syntax</h2>
	<p>Directive for <code>X-Frame-Options</code>:</p>
	<pre class="syntaxbox">X-Frame-Options: SAMEORIGIN</pre>
	<h3 id="Directives">Directives</h3>
	<p>If you specify <code>SAMEORIGIN</code>, you can still use the page in a frame as long as the site including it in a frame is the same as the one serving the page.</p>
	<span style="display: block;padding-top: 10px;">&nbsp;</span>
	<dl>
		<dt><code>SAMEORIGIN</code></dt>
		<dd>The page can only be displayed in a frame on the same origin as the page itself. The spec leaves it up to browser vendors to decide whether this option applies to the top level, the parent, or the whole chain.</dd>
	</dl>
	<h2 id="Examples">Examples</h2>
	<div class="arcade_xframe_note">
		<p><strong>Note:</strong> Setting the meta tag is useless! For instance, <code>&lt;meta http-equiv="X-Frame-Options" content="deny"&gt;</code> has no effect. Do not use it! Only by setting through the HTTP header like the examples below, <code>X-Frame-Options</code> will work.</p>
	</div>
	<h3 id="Configuring_Apache">Configuring Apache</h3>
	<p>To configure Apache to send the <code>X-Frame-Options</code> header for all pages, add this to your site\'s configuration:</p>
	<pre>Header always set X-Frame-Options SAMEORIGIN</pre>
	<p>To configure Apache to use <code>.wasm</code> file extensions for all pages, add this to your site\'s configuration:</p>
	<pre>AddType application/wasm .wasm</pre>
	<h3 id="Configuring_nginx">Configuring nginx</h3>
	<p>To configure nginx to send the <code>X-Frame-Options</code> header, add this either to your http, server or location configuration:</p>
	<pre>add_header X-Frame-Options SAMEORIGIN;</pre>
	<p>To configure nginx to use <code>.wasm</code> file extensions, add this to your mime.types configuration file:</p>
	<pre>application/wasm	wasm;</pre>
	<h3 id="Configuring_IIS">Configuring IIS</h3>
	<p>To configure IIS to send the <code>X-Frame-Options</code> header, add this your site\'s <code>Web.config</code> file:</p>
	<pre class="brush: xml">&lt;system.webServer&gt;
	&lt;httpProtocol&gt;
		&lt;customHeaders&gt;
			&lt;add name="X-Frame-Options" value="SAMEORIGIN" /&gt;
		&lt;/customHeaders&gt;
	&lt;/httpProtocol&gt;
&lt;/system.webServer&gt;</pre>
	<p>To configure IIS to use <code>.wasm</code> file extensions, add this your site\'s <code>Web.config</code> file:</p>
	<pre class="brush: xml">&lt;system.webServer&gt;
	&lt;system.webServer&gt;
		&lt;staticContent&gt;
			&lt;mimeMap fileExtension=".wasm" mimeType="application/wasm" /&gt;
		&lt;/staticContent&gt;
	&lt;/system.webServer&gt;
&lt;/system.webServer&gt;</pre>
	<h3 id="Configuring_HAProxy">Configuring HAProxy</h3>
	<p>To configure HAProxy to send the <code>X-Frame-Options</code> header, add this to your front-end, listen, or backend configuration:</p>
	<pre>rspadd X-Frame-Options:\ SAMEORIGIN</pre>
	<h2 id="Specifications">Specifications</h2>
	<div class="table windowbg" style="display: table;width: 100%;">
		<div>
			<div style="display: table-row">
				<div style="display: table-cell;">Specification</div>
				<div style="display: table-cell;padding-left: 1.0em;">Title</div>
			</div>
			<div style="display: table-row">
				<div style="display: table-cell;">RFC 2046</div>
				<div style="display: table-cell;padding-left: 1.0em;">Multipurpose Internet Mail Extensions II - Media Types</div>
			</div>
			<div style="display: table-row">
				<div style="display: table-cell;">RFC 7034</div>
				<div style="display: table-cell;padding-left: 1.0em;">HTTP Header Field X-Frame-Options</div>
			</div>
		</div>
	</div>
</div>';

// Imagick
$txt['arcadeFilterImagickAdminHelp'] = 'SMF-Arcade will attempt to use Imagick for image resizing and to help retain image quality.<br>With this setting disabled, PHP will attempt to use the GD Library.';
$txt['arcadeFilterImagickHelp'] = '
<div style="overflow: hidden;">
	<div style="text-decoration: underline;font-weight: bold;padding-bottom: 0.25rem;">SMF-Arcade Imagick Games List Filter</div>
	<div style="font-style: italic;font-weight: 600;font-size: 7pt;padding-bottom: 0.25rem;">Thumbnails and cover art within game lists can be enhanced using Imagick.</div>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[-JPG/PNG-]</div>
	<ul style="font-size: 8pt;">
	<li>only filters jpg and png image types</li>
	<li>gif image types will not be filtered</li>
	</ul>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[-JPG/PNG/GIF-]</div>
	<ul style="font-size: 8pt;">
	<li>filters all image types</li>
	<li>gif image types are flattened and converted to jpg</li>
	</ul>
	<div style="text-decoration: underline;padding-top: 0.5rem;">[-DISABLED-]</div>
	<ul style="font-size: 8pt;">
	<li>no image types are filtered</li>
	</ul>
	<div style="padding-bottom: 1rem;"><span></span></div>
</div>';
$txt['arcade_adjust_FilterImagick_info_warn'] = '\r\nEnabling this setting will attempt to enhance thumbnails and cover art using Imagick during game installation.\r\nThese will be permanent changes to those image files which may or may not work as intended.\r\nIf you are unsure about this process then it may be best to leave it disabled.\r\n\r\nAre you sure you want to enable this setting?';
$txt['arcade_adjust_FilterImagickTemp_info_warn'] = '\r\nEnabling this setting will attempt to enhance thumbnails and cover art using Imagick within game lists.\r\nYou can always enable or disable this option without any permanent changes to any image files.\r\n\r\nAre you sure you want to enable this setting?';


// Console I/O
$txt['arcadeRetroLibraryCodeEnableHelp'] = '
<span style="font-size: 8pt;display: block;">This will enable the BIOS library cipher setting.</span>
<span style="font-size: 8pt;display: block;">Simply enter the URL for the retro library zip archive in the "Retro Library Code" input box under the "Path Settings" heading to create your code.</span>
<span style="font-size: 8pt;display: block;">This setting only supports the standard retro library path format.</span>
<span style="font-size: 8pt;display: block;padding-top: 0.5rem;">BIOS files are <strong>not necessary</strong> for most ROM console games and you are <strong>highly discouraged</strong> from their usage.</span>
<span style="font-size: 8pt;display: block;">Almost all ROM games will work using the built-in 3rd party console cores.</span>
<span style="font-size: 8pt;display: block;padding-top: 0.5rem;"><strong>This function is provided solely for testing purposes and sources of BIOS files are not provided in this application nor on the SMF-Arcade support website.</strong></span>
<span style="font-size: 8pt;display: block;padding-top: 0.5rem;">If you choose to use this option, you must obtain your own source for any console BIOS files.</span>
<span style="font-size: 8pt;display: block;">In using BIOS files, you confirm the legality of using them at your own risk.</span>';

?>