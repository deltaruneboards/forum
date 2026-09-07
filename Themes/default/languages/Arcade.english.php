<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
global $txt, $arcadeModSettings, $boardurl, $scripturl;

// Titles
$txt['arcade_game_list'] = 'Games';
$txt['arcade_game_play'] = 'Arcade - Playing %s';
$txt['arcade_game_list_rom'] = 'ROM Games';
$txt['arcade_game_play_rom'] = 'ROM Arcade - Playing %s';
$txt['arcade_view_highscore'] = 'Arcade - Viewing Highscores of %s';
$txt['arcade_stats_title'] = 'Arcade - Statistics';
$txt['arcade_user_stats_title'] = 'Arcade - Statistics for user %s';
$txt['arcade_arena_challenge_title'] = 'Arcade - Challenge user %s';
$txt['arcade_arena_new_match_title'] = 'Arcade - Create a New Match';
$txt['arcade_arena_view_match_title'] = 'Arcade Arena - Viewing match %s';
$txt['upshrink_description'] = !empty($txt['upshrink_description']) ? $txt['upshrink_description'] : 'Shrink or expand the header.';

// Arcade top box
$txt['arcade_search'] = 'Search';
$txt['arcade_search_placeholder'] = 'Search by Game';
$txt['search_favorites'] = 'From Favorites Only';

// General
$txt['arcade_game_name'] = 'Game';
$txt['arcade_personal_best'] = 'Your Best';
$txt['arcade_champion'] = 'Champion';
$txt['arcade_stats'] = 'Statistics';
$txt['arcade_member'] = 'Member';
$txt['arcade_save'] = 'Save';
$txt['arcade_preview'] = 'Preview';
$txt['arcade_list_none'] = 'None';
$txt['arcade_edit_game'] = 'Edit Game';
$txt['arcade_max_scores'] = 'You may have %d scores at same time';
$txt['arcade'] = 'Arcade';
$txt['arcadeSettings'] = 'Arcade Settings';
$txt['arcadeStats'] = 'Arcade Statistics';
$txt['sendArcadeChallenge'] = 'Arcade Challenge';
$txt['arcade_list_sort'] = 'Ascend / Descend';
$txt['arcade_list_options'] = 'Options';
$txt['arcade_list_popularity'] = 'Popularity';
$txt['arcade_select_gametype'] = 'all|v1game|v2game|phpbb|v3arcade|mochi|ibp|ibp2|ibp3|ibp32|html5|html52|html53|custom_game|rom';
$txt['arcade_select_gametype_english'] = 'All Types|SMF v1|SMF v2|PHPBB|v3Arcade|Mochi|IBP v1|IBP v2|IBP v3|IBP v3.2|HTML5 v1|HTML5 v2|HTML5 v3|Custom|ROM';
$txt['emulator_types'] = 'EmulatorJS|Ruffle';
$txt['arcade_click_info'] = 'Game Info';
$txt['arcade_click_info_title'] = 'Click to see game details';
$txt['arcade_task_unknown'] = 'Task Unknown';
$txt['arcade_rom_switch'] = array(
	'rom' => array('system' => 'rom', 'name' => 'ROM'),
	'arcade' => array('system' => 'arcade', 'name' => 'FLASH/HTML5')
);
// ROM game types --> the key is paired to the full name --> only edit the full name value but do not edit the keys
$txt['arcade_select_gametype_rom'] = array(
	'all' => 'All ROM Games',
	'none' => 'None',
	'n64' => 'Nintendo 64',
	'gb' => 'Nintendo GB',
	'gba' => 'Nintendo GBA',
	'nds' => 'Nintendo DS',
	'nes' => 'NES',
	'snes' => 'Super NES',
	'psx' => 'PlayStation',
	'psp' => 'Playstation Portable',
	'vb' => 'Virtual Boy',
	'segaMD' => 'Sega Mega Drive',
	'segaMS' => 'Sega Genesis',
	'segaCD' => 'Sega CD',
	'sega32x' => 'Sega 32X',
	'segaGG' => 'Sega Game Gear',
	'segaSaturn' => 'Sega Saturn',
	'lynx' => 'Atari Lynx',
	'jaguar' => 'Atari Jaguar',
	'atari2600' => 'Atari 2600',
	'atari7800' => 'Atari 7800',
	'pce' => 'NEC TurboGrafx-16',
	'pcfx' => 'NEC PC-FX',
	'ngp' => 'NeoGeo Pocket',
	'ws' => 'WonderSwan',
	'coleco' => 'ColecoVision',
	'vice_x64sc' => 'Commodore 64',
	'vice_x128' => 'Commodore 128',
	'vice_xvic' => 'Commodore VIC20',
	'vice_xplus4' => 'Commodore Plus/4',
	'vice_xpet' => 'Commodore PET',
	'mame' => 'MAME-PC-ROM',
);

// Arcade online
$txt['arcade_online_title'] = 'Arcade Online';
$txt['arcade_login_title'] = 'Arcade Login';
$txt['arcade_login_top'] = 'Arcade Login';
$txt['arcade_online'] = 'Online';
$txt['arcade_online_unknown'] = 'Nothing, or nothing you can see...';
$txt['arcade_info_who'] = 'Online: %1$d Guest%3$s, %2$d User%4$s';
$txt['who_arcade'] = 'Viewing Arcade index';
$txt['who_arcade_play'] = 'Playing <a href="' . $scripturl . '?action=arcade;sa=play;game=%d">%s</a> on Arcade';
$txt['who_arcade_highscore'] = 'Viewing highscores of <a href="' . $scripturl . '?action=arcade;sa=highscore;game=%d">%s</a> on Arcade';
$txt['who_arcade_match'] = 'Viewing Arcade Arena';
$txt['who_arcade_online'] = 'Viewing Arcade Online';
$txt['who_arcade_view_match'] = 'Viewing a match in the arena';
$txt['who_arcade_new_match'] = 'Starting a new match in the arena';
$txt['who_arcade_stats'] = 'Viewing Arcade Statistics';
$txt['arcade_coalesce'] = 'Sort';
$txt['arcade_no_online_guests'] = 'There are currently no guests in the arcade';
$txt['arcade_no_online_members'] = 'There are currently no members in the arcade';
$txt['who_arcade_action'] = 'Arcade Action';
$txt['arcade_who_show'] = 'Show ';
$txt['arcade_profile_of'] = 'View the profile of';

// Arcade Info Center
$txt['arcade_info_center'] = 'Arcade Info Center';
$txt['arcade_game_highlights'] = 'Did you know?';
$txt['arcade_game_with_longest_champion'] = '%s has been champion of %s for way too long?';
$txt['arcade_game_most_played'] = 'Our most played game is %s, have you played it?';
$txt['arcade_game_best_player'] = 'Did you know that %s has claimed way too many champions?';
$txt['arcade_game_we_have_games'] = 'Total of %s games in the arcade';
$txt['arcade_have_tried_these'] = 'Have you tried these?';
$txt['arcade_game_of_day'] = 'Game Of The Day';
$txt['arcade_latest_scores'] = 'Latest Scores';
$txt['arcade_latest_score_item'] = '%4$s scored %3$s on <a href="%1$s">%2$s</a>';
$txt['arcade_users'] = 'Users In Arcade';


// Game list
$txt['arcade_no_games'] = 'No games available for playing';
$txt['arcade_play'] = 'Play';
$txt['arcade_no_scores'] = 'No Scores Recorded';
$txt['arcade_random_game'] = 'Random game';
$txt['arcade_viewscore'] = 'Highscores';
$txt['arcade_add_favorites'] = 'Add to favorites';
$txt['arcade_remove_favorite'] = 'Remove from favorites';
$txt['arcade_no_highscore'] = 'This game doesn\'t support highscores';
$txt['arcade_show_all'] = 'Show all games';
$txt['arcade_favorite_removed'] = 'Game was removed from favorites!';
$txt['arcade_favorite_added'] = 'Game was added to favorites!';
$txt['arcade_favorites_only'] = 'Show favorites only';
$txt['arcade_number_pages'] = 'Pages:';
$txt['arcade_typeset'] = 'Type';
$txt['arcade_romtypeset'] = 'System';
$txt['arcade_mobile_add_fav'] = 'Add to favorites';
$txt['arcade_mobile_del_fav'] = 'Remove favorite ';
$txt['arcade_gamesavetype'] = array(
	'phpbb' => 'PHPBB',
	'html5' => 'SMF HTML5 v1',
	'html52' => 'IBP HTML5 v2',
	'html53' => 'PHPBB HTML5 v3',
	'silver' => 'Silverlight',
	'ibp' => 'IBP v1',
	'ibp2' => 'IBP v2',
	'ibp3' => 'IBP v3',
	'ibp32' => 'IBP v3.2',
	'pnflash' => 'PN-FLASH',
	'v1game' => 'SMF Arcade v1',
	'custom_game' => 'Custom',
	'v3arcade' => 'vBulletin v3Arcade',
	'v2game' => 'SMF Arcade v2',
	'mochi' => 'Mochi Media',
	'rom' => 'ROM',
);
$txt['arcade_mobile_sort_a2z'] = 'Sort By Alphabetical';
$txt['arcade_mobile_sort_new'] = 'Sort By Age';
$txt['arcade_mobile_sort_dir_asc'] = 'Ascending';
$txt['arcade_mobile_sort_dir_desc'] = 'Descending';

// Play
$txt['arcade_no_flash'] = 'You have not installed Adobe Flash Player, you need to install it before you can play, you also need to have javascript enabled. <a href="https://get.adobe.com/flashplayer/">Install</a>';
$txt['arcade_html5'] = 'Your browser does not support HTML5, you should update or install a browser that will support HTML5 content.';
$txt['arcade_no_javascript'] = 'You need to enable javascript in order to play games.';
$txt['arcade_please_wait'] = 'Please wait while checking session...';
$txt['arcade_session_check_ok'] = 'Session check successful!';
$txt['arcade_save_score'] = 'Save score';
$txt['arcade_noSmfScore'] = 'No score to save!';

// Highscores
$txt['arcade_no_comment'] = 'No comment';
$txt['arcade_enter_comment'] = 'Enter comment';
$txt['arcade_comment'] = 'Comment';
$txt['arcade_score'] = 'Score';
$txt['arcade_position'] = 'Position';
$txt['arcade_position_rank'] = 'Rank';
$txt['arcade_time'] = 'Time';
$txt['arcade_this_is_your_best'] = 'This is your best score!';
$txt['arcade_you_are_now_champion'] = 'You are now champion of this game!';
$txt['arcade_edit'] = 'Edit';
$txt['arcade_submit_score'] = 'Thank you for playing!';
$txt['arcade_submit_score_alert'] = 'Press ENTER to save score...';
$txt['arcade_highscores'] = 'Highscores';
$txt['arcade_score_saved'] = 'Score was saved to database!';
$txt['arcade_rating_saved'] = 'Rating saved';
$txt['arcade_duration'] = 'Duration';
$txt['arcade_enter_name'] = 'Enter you name to save score';
$txt['arcade_when'] = 'Score recorded %s.<br />Time taken: %s';
$txt['arcade_comment_saved'] = 'Comment saved!';
$txt['arcade_comment_guestname'] = 'You must enter a valid guest name!';
$txt['arcade_comment_noguestname'] = 'Only letters and number are allowed!';
$txt['arcade_guest_score_name'] = '[Guest: %s]';
$txt['arcade_guest_score_noname'] = '[Guest]';

// Quick Management
$txt['arcade_delete_selected'] = 'Delete Selected';
$txt['arcade_are_you_sure'] = 'Are you sure you want to do this?';

// Arena
$txt['arcade_arena'] = 'Arena';
$txt['arcade_newMatch'] = 'New Match';

// List of matches
$txt['arcade_no_matches'] = 'There are no matches in Arena';
$txt['match_name'] = 'Name';
$txt['match_opponent'] = 'Opponent';
$txt['match_status'] = 'Status';
$txt['match_players'] = 'Players';
$txt['match_round'] = 'Round';

// Match Status
$txt['arcade_arena_player_invited'] = 'Invited';
$txt['arcade_arena_player_waiting'] = 'Waiting';
$txt['arcade_arena_player_played'] = 'Played';
$txt['arcade_arena_player_knockedout'] = 'Knocked out';
$txt['arcade_arena_waiting_players'] = 'Waiting for Players';
$txt['arcade_arena_started'] = 'In Progress';
$txt['arcade_arena_waiting_other_players'] = 'Waiting for Match to start';
$txt['arcade_arena_not_played'] = 'You haven\'t played yet';
$txt['arcade_arena_not_other_played'] = 'Waiting for other players';
$txt['arcade_arena_dropped'] = 'Knocked out';
$txt['arcade_arena_complete'] = 'Completed';

// View Match
$txt['arcade_startMatch'] = 'Start';
$txt['arcade_cancelMatch'] = 'Cancel';
$txt['arcade_joinMatch'] = 'Join';
$txt['arcade_leaveMatch'] = 'Leave';
$txt['match_not_found'] = 'Match not found';
$txt['arcade_rounds'] = 'Rounds';
$txt['arcade_players'] = 'Players';
$txt['arcade_accept'] = 'Accept';
$txt['arcade_decline'] = 'Decline';

// Invite to a Match
$txt['arcade_invite'] = 'Invite';
$txt['arcade_invite_user'] = 'Invite user to a match';
$txt['invite_to_existing'] = 'Invite to existing match';
$txt['arcade_create_new'] = 'Create a New Match';

// New match
$txt['arcade_new_match'] = 'Create a New Match';
$txt['arcade_match_name'] = 'Name';
$txt['game_mode'] = 'Game Mode';
$txt['game_mode_normal'] = 'Normal';
$txt['game_mode_knockout'] = 'Knockout';
$txt['players'] = 'Players';
$txt['player_add'] = 'Add player';
$txt['player_remove'] = 'Remove Player';
$txt['num_players'] = 'Number of Players';
$txt['num_players_help'] = 'Means how many players can join this match at maximum. Must be greater than or equal to number of players invited here';
$txt['rounds'] = 'Rounds';
$txt['arcade_enter_games'] = 'Games Selected';
$txt['add_game'] = 'Add Game';
$txt['game_remove'] = 'Remove Game';
$txt['arcade_continue'] = 'Continue';

// Ago
$txt['arcade_secs'] = 'seconds';
$txt['arcade_weeks'] = 'weeks';
$txt['arcade_days'] = 'days';
$txt['arcade_hours'] = 'hours';
$txt['arcade_mins'] = 'minutes';
$txt['arcade_under_minute_ago'] = 'Under minute ago';
$txt['arcade_unknown'] = 'unknown';

// Statistics
$txt['arcade_longest_champions'] = 'Longest Champions';
$txt['arcade_best_games'] = 'Best Games (by rating)';
$txt['arcade_best_players'] = 'Best Players (by champions)';
$txt['arcade_most_played'] = 'Most Played Games';
$txt['arcade_most_active'] = 'Most Active Players';
$txt['arcade_member_stats'] = 'Arcade Statistics';
$txt['arcade_champion_in'] = 'Currently Champion in';
$txt['arcade_games'] = 'games';
$txt['arcade_rated_game'] = 'Number Of Games Rated';
$txt['arcade_average_rating'] = 'Average Rating';
$txt['arcade_member_best_scores'] = 'Personal Best Scores';
$txt['arcade_qty'] = 'Qty';
$txt['arcade_avg'] = 'Avg';
$txt['arcade_stats_rating'] = 'Rating';
$txt['arcade_stats_duration'] = 'Duration';
$txt['arcade_stats_game'] = 'Game';
$txt['arcade_stats_qty'] = 'Quantity: %d';
$txt['arcade_stats_stars'] = '%d star rating';

// User settings
$txt['arcade_usersettings_desc'] = 'You can change your Arcade settings from this page.';
$txt['arcade_notifications'] = 'Notifications';
$txt['arcade_emulatorjs_rom'] = 'Default EmulatorJS Settings';
$txt['arcade_user_gamesPerPage'] = 'Games Per Page';
$txt['arcade_user_gamesPerPage_default'] = 'Default (%d)';
$txt['arcade_user_scoresPerPage'] = 'Scores Per Page';
$txt['arcade_user_scoresPerPage_default'] = 'Default (%d)';
$txt['arcade_user_default'] = 'Default (%s)';
$txt['arcade_user_skin'] = 'Select HTML5/Flash Skin';
$txt['arcade_user_list'] = 'Select HTML5/Flash List';
$txt['arcade_user_skin_rom'] = 'Select ROM Skin';
$txt['arcade_user_list_rom'] = 'Select ROM List';
$txt['arcade_user_skin_mobile'] = 'Select Mobile Skin';
$txt['arcade_user_list_mobile'] = 'Select Mobile List';
$txt['arcade_user_archive_type'] = 'Archive: ';
$txt['arcade_user_archive_type_button'] = 'Archive';
$txt['arcade_user_gametype'] = 'Type: ';
$txt['arcade_user_gametype_button'] = 'Type';
$txt['arcade_user_mobile_detection'] = 'Mobile List/Skin Detection';
$txt['arcade_mobile_auto'] = 'Detection Enabled';
$txt['arcade_mobile_desktop'] = 'Detection Disabled';

// EmulatorJS ROM game template Settings (User)
$txt['arcade_user_emulatorjs_volume'] = 'Enable Volume';
$txt['arcade_user_emulatorjs_mute'] = 'Enable Mute';
$txt['arcade_user_emulatorjs_webgl2Enabled'] = 'Enable WebGL2';
$txt['arcade_user_emulatorjs_shader'] = 'Shader Options';
$txt['arcade_profile_shaders'] = array(
	'diabled' => 'Disabled',
	'2xScaleHQ.glslp' => '2xScaleHQ',
    '4xScaleHQ.glslp' => '4xScaleHQ',
    'crt-aperture.glslp' => 'CRT aperture',
    'crt-beam' => 'CRT beam',
    'crt-caligari' => 'CRT caligari',
    'crt-easymode.glslp' => 'CRT easymode',
    'crt-geom.glslp' => 'CRT geom',
    'crt-lottes' => 'CRT lottes',
    'crt-mattias.glslp' => 'CRT mattias',
    'crt-yeetron' => 'CRT yeetron',
    'crt-zfast' => 'CRT zfast',
    'sabr' => 'SABR',
    'bicubic' => 'Bicubic',
    'mix-frames' => 'Mix frames',
);

// Errors
/*  Arcade - PDL Text Variables  */
$arcadeModSettings['arcadeDownPost'] = !empty($arcadeModSettings['arcadeDownPost']) ? $arcadeModSettings['arcadeDownPost'] : false;
$arcadeModSettings['pdl_DownMax'] = !empty($arcadeModSettings['pdl_DownMax']) ? $arcadeModSettings['pdl_DownMax'] : false;
$txt['pdl_error_tar'] = 'Unable to locate PEAR function';
$txt['pdl_error_perm'] = 'No Permission To Download Games';
$txt['pdl_error_disable'] = 'Download Feature Disabled';
$txt['pdl_error_download_disable'] = 'Downloading For This Game Is Disabled';
$txt['pdl_error_post'] = 'Sorry, you need to have at least ' . (!empty($arcadeModSettings['arcadeDownPost']) ? $arcadeModSettings['arcadeDownPost'] : '?') . ' posts in the forum to enable this feature!';
$txt['pdl_error_nogame'] = 'No Game Selected';
$txt['pdl_error_db'] = 'Game not in database';
$txt['pdl_error_dl'] = 'Downloading for this game has been disabled by the administrator.';
$txt['pdl_error_max'] = 'You have exceeded your daily download limit of %s games.';
$txt['arcade_submit_adjust_configure_log'] = 'Error while saving score for game "%s". Attempting to auto adjust submit system to "%s" to fix the error.';
$txt['arcade_submit_error_loop_log'] = 'Error while saving score for game "%s". Score loop detected ~ Please check the score subroutine within the game itself.';
$txt['arcade_submit_error_game_session'] = 'Error while saving score for game id#:"%s". Session was empty or had an incorrect value.';
$txt['pdl_zipfile1'] = 'The download file was NOT SPECIFIED.';
$txt['pdl_zipfile2'] = 'File not found.';
$txt['pdl_zipfile3'] = 'Could not locate the specified directory - ';
$txt['arcade_email_play_error'] = 'You need to log in for the arcade!';
$txt['arcade_email_play_error_msg'] = 'You will be redirected to the arcade after logging in.';
$txt['arcade_email_score_error'] = 'You need to log in to view high scores!';
$txt['arcade_email_score_error_msg'] = 'You will be redirected to the highscores after logging in.';
$txt['arcade_email_hs_error'] = 'You need to log in to view high scores because the Admin has disabled viewing scores for guests.';
$txt['arcade_online_error'] = 'The Arcade Online list has been disabled by the Administrator.';
$txt['arcade_disabled'] = 'Arcade is currently disabled by admin';
$txt['arcade_game_update_error'] = 'Unable to update game data';
$txt['arcade_scores_limit'] = 'Score was not saved because you already have maximum number of scores';
$txt['arcade_no_permission'] = 'Score was not saved because you do not have permission!';
$txt['arcade_saving_error'] = 'Score was not saved due to unknown error!';
$txt['arcade_game_not_found'] = 'Game was not found';
$txt['arcade_game_score_support'] = 'Game does not have score support';
$txt['arcade_submit_error'] = 'An error occurred while saving score';
$txt['arcade_html53_submit_error'] = 'HTML53 decode ~ return data error';
$txt['arcade_submit_error_js'] = 'An error occurred while saving score<script type="text/javascript">window.close();</script>';
$txt['arcade_rate_error'] = 'Unable to save rating';
$txt['arcade_cannot_save'] = 'You are not allowed to save your scores!';
$txt['arcade_submit_error_session'] = 'Score was not saved because session is missing';
$txt['arcade_submit_error_loop'] = 'Score loop detected - session aborted';
$txt['arcade_submit_error_configure_log'] = 'Error while saving score for game "%s". Submit system might be invalid, should be "%s" or user tried to cheat.';
$txt['arcade_notice_post_requirement'] = 'You don\'t meet post requirements to play.';
$txt['arcade_internal_error'] = 'An internal error occurred.';
$txt['arcade_no_invite'] = 'This member cannot be invited to an Arena Match.';
$txt['arena_error_no_name'] = 'Match must have a name';
$txt['arena_error_invalid_game_mode'] = 'Invalid game mode selected.';
$txt['arena_error_name_too_long'] = 'Name for match is too long';
$txt['arena_error_no_rounds'] = 'No rounds added';
$txt['arena_error_not_enough_players'] = 'There is not enough slots for players';
$txt['arena_error_invalid_rounds'] = 'Invalid Games selected for the match';
$txt['arcade_db_mismatch'] = 'Database version mismatch detected';
$txt['arcade_shoutbox_session'] = 'Session verification failed';
$txt['arcade_wrong_savetype'] = 'Wrong save type selected. Please report this game to the administrator.';
$txt['arcade_no_game_permission'] = 'You are not permitted to access this game.';
$txt['arcade_rom_load_error'] = 'Attempting to load ROM Game...';
$txt['arcade_emulatorjs_core_error'] = 'Failed to load EmulatorJS core files, please contact the administrator';
$txt['arcade_ruffle_core_error'] = 'Failed to load Ruffle core files, please contact the administrator';
$txt['arcade_remote_game_file_error'] = 'Remote game file does not exist: [id=%s] %s';
$txt['arcade_imagick_enhance_error'] = 'GD-Image error [2]: %s';
$txt['arcade_imagick_crop_error'] = 'Imagick crop/resize error: %s';

// Email notifications
$txt['arcade_notification_notify_game_reports'] = 'When a game report is submitted';
$txt['arcade_notification_champion_pm'] = 'PM Arcade alerts';
$txt['arcade_notification_champion_email'] = 'Email Arcade alerts';
$txt['arcade_notification_new_champion_own'] = 'When someone takes championship from me';
$txt['arcade_notification_arena_invite_own'] = 'You are invited to join a match';
$txt['arcade_notification_new_champion_any'] = 'When someone takes championship from anyone';
$txt['arcade_notification_arena_invite'] = 'When I\'m invited to join a match';
$txt['arcade_notification_arena_new_round'] = 'When new round begins on a Match';
$txt['arcade_notification_arena_match_end'] = 'When match I\'m participated in is finished';
$txt['arcade_notification_new_game'] = 'When a new game is available';
$txt['arcade_none_played'] = 'No games have been played';
$txt['arcade_title'] = 'Arcade';

// Arcade Advanced
$txt['arcade_download_gameplay'] = 'Download';
$txt['pdl_play'] = '<img src="Themes/default/images/arc_icons/pdl_play.gif" alt="Play" title="Play Game" />';
$txt['pdl_download_game'] = '<img src="Themes/default/images/arc_icons/pdl_download.gif" alt="Download" title="Download" />';
$txt['arcade_download_gameplay'] = '<img src="' . $boardurl . '/Themes/default/images/arc_icons/dl_btn.png" style="width: 70px;height: 18px;" title="Download" alt="Download"></img>';
$txt['pdl_erroricon'] = '<a href="'.$scripturl.'?action=arcade" target="_parent"><img src="'.$boardurl.'/Themes/default/images/arc_icons/arcade_popup_error.gif" alt="SMF ARCADE" title="SMF ARCADE" /></a><br />';
$txt['pdl_arcade_copyright'] = '<div class="centertext smalltext">Powered by: <a href="https://web-develop.ca/index.php?page=arcade_license_BSD2" target="_blank">SMF Arcade ' . (!empty($arcadeModSettings['arcadeVersion']) ? $arcadeModSettings['arcadeVersion'] : '') . '</a> &copy; ' . date('Y') . '</div>';
$txt['pdl_listplay'] = 'PLAY';
$txt['arcade_replay'] = 'Replay';
$txt['arcade_download_game'] = 'Download';
$txt['pdl_unassigned'] = 'Unassigned';
$txt['pdl_unlimited'] = 'Unlimited';
$txt['pdl_na'] = 'N/A';
$txt['pdl_gamedata'] = 'Game Information';
$txt['game_categories'] = 'Game Categories';
$txt['pdl']['help'] = 'Help';
$txt['arcade_post_help'] = 'Navigation: ';
$txt['arcade_post_description'] = 'Description: ';
$txt['pdl_button1'] = 'Download';
$txt['pdl_cat_list'] = 'Category: %s';
$txt['pdl_edit'] = 'Edit';
$txt['pdl_report_reason'] = 'Report Game: ';
$txt['pdl_report'] = 'Report';
$txt['pdl_counter'] = 'Downloads';
$txt['pdl_max_limit'] = 'Limit';
$txt['pdl_disabled'] = 'Arcade is disabled.';
$txt['pdl_notfound'] = 'Game not found in database.';
$txt['pdl_gamedisable'] = 'Game has been disabled.';
$txt['pdl_yes'] = 'Yes';
$txt['view_cat'] = 'View By Category';
$txt['arcade_post'] = 'Try %#@$ from the Arcade';
$txt['pdl_down'] = '<br /><br /><br />';
/* If you want a button for downloads, omit the remark tags from the line below  */
/* $txt['pdl_button1'] = '<img src="' . $boardurl . '/Themes/default/images/arc_icons/dl_btn.png" style="70px;height: 18px;" alt="Download" title="Download" />'; */
$txt['show_pdl_report'] = 'View Report';
$txt['arcade_tour_tour'] = 'Tournament';
$txt['arcade_administrator'] = 'Admin';
$txt['arcade_report_gametype'] = '[ROM]|[HTML5]|[FLASH]';
$txt['arcade_download_php'] = array(
	'source' => 'File Created by SMF Arcade',
	'date' => 'File Generated:',
	'rom_date' => 'ROM Archive Generated:',
);

// Arcade
$txt['arcade'] = 'Arcade';
$txt['rom_arcade'] = 'ROM Arcade';
$txt['arcade_nav_admin'] = 'Arcade Admin';
$txt['arcadeOpenFull'] = 'Open Fullscreen';
$txt['arcadeCloseFull'] = 'Close Fullscreen';
$txt['arcadeFullPopup'] = 'Fullscreen';
$txt['pdl_popplay'] = 'Popup';
$txt['arcade_popplay'] = 'Play in Popup';
$txt['arcade_fullplay'] = 'Play in Fullscreen';
$txt['arcade_change_rgame'] = 'Change';

// Core Features
$txt['core_settings_item_arcade'] = 'Arcade';
$txt['core_settings_item_arcade_desc'] = 'Enable Arcade section which allows users to play games and store their records for others to see.';

// Admin
$txt['arcade_admin'] = 'Arcade';
$txt['arcade_manage_games'] = 'Games';
$txt['arcade_manage_games_edit_games'] = 'Edit Games';
$txt['arcade_manage_games_install'] = 'Install Games';
$txt['arcade_manage_games_upload'] = 'Upload';
$txt['arcade_manage_category'] = 'Categories';
$txt['arcade_manage_category_list'] = 'List';
$txt['arcade_manage_category_new'] = 'New';
$txt['arcade_general'] = 'General';
$txt['arcade_general_information'] = 'Information';
$txt['arcade_general_settings'] = 'Settings';
$txt['arcade_general_permissions'] = 'Permissions';
$txt['arcade_maintenance'] = 'Maintenance';
$txt['arcade_maintenance_main'] = 'Main';
$txt['arcade_maintenance_highscore'] = 'Highscore';
$txt['arcade_admin_general_mode'] = 'Arcade is currently disabled for non-administrators';
$txt['arcade_admin_general_mode_rom'] = 'ROM games are currently disabled for non-administrators';

// Moderation Log
$txt['modlog_ac_arcade_install_game'] = 'Installed Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_update_game'] = 'Updated Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_delete_game'] = 'Deleted Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_remove_scores'] = 'Removed {scores} scores from Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_maintenance'] = 'Performed Maintenance Task: &quot;{task}&quot;';
$txt['modlog_ac_arcade_install_game_rom'] = 'Installed ROM Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_update_game_rom'] = 'Updated ROM Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_delete_game_rom'] = 'Deleted ROM Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_remove_scores_rom'] = 'Removed {scores} scores from ROM Game &quot;{game}&quot;';
$txt['modlog_ac_arcade_maintenance_rom'] = 'Performed Maintenance Task (ROM): &quot;{task}&quot;';

// Profile
$txt['arcadeStats'] = 'Arcade Statistics';
$txt['arcadeChallenge'] = 'Arcade Arena';
$txt['sendArcadeChallenge'] = 'Send Challenge';
$txt['arcadeSettings'] = 'Arcade Settings';
$txt['arcadeProfTitle0'] = 'Email | PM';
$txt['arcadeProfTitle1'] = 'Notification Type';
$txt['arcadeEJS_settings'] = 'EJS Settings';
$txt['arcadeEJS_general_settings'] = 'General Settings';
$txt['arcade_profile_info'] = 'Arcade Info';
$txt['arcade_profile_unknown'] = 'unknown';
$txt['arcade_profile_desc'] = 'Information about this user for the Arcade';
$txt['arcade_profile_downloads'] = 'Game downloads';
$txt['arcade_profile_arcade_online'] = 'Last Arcade visit';
$txt['arcade_profile_online'] = 'Last site login';
$txt['arcade_profile_num_reports'] = 'Error reports';

// Errors if they can't do something
$txt['cannot_arcade_play'] = 'You are not allowed to play games!';
$txt['cannot_arcade_view'] = 'You are not allowed to access the Arcade.';
$txt['cannot_arcade_comment_own'] = 'You are not allowed to comment';
$txt['cannot_arcade_user_stats_any'] = 'You are not allowed to view statistics of any user';
$txt['cannot_arcade_user_stats_own'] = 'You are not allowed to view your statistics';
$txt['cannot_arcade_online'] = 'You are not permitted to view the Arcade online log.';
$txt['arcade_submit_error_neg'] = 'This game requires a score greater than 0.';
$txt['arcade_profile_reports'] = 'Games that were reported by this member';
$txt['cannot_arcade_play_retro_arch'] = 'You are not allowed to play ROM games!';
$txt['arcade_play_retro_arch_disabled'] = 'The administrator has disabled the ROM Arcade!';
$txt['arcade_retro_arch_gameid_none'] = 'No ROM game ID detected';
$txt['arcade_retro_arch_gameid_mismatch'] = 'The game ID selected is not ROM type or does not exist!';
$txt['cannot_arcade_download_gamefile'] = 'You do not have permission to download this game!';
$txt['cannot_arcade_access_denied'] = 'You are not allowed to access this section!';

// Help
$txt['arcade_max_scores_help'] = 'Maximum scores that will be stored per member. (0 means unlimited)';
$txt['arcade_membergroups_help'] = 'These groups will be allowed to play and view highscores. Others will not see this game, only used if permission mode will use game permissions.';

// Defiant specifics
$txt['arcade_topic_talk'] = 'Talk';
$txt['arcade_dviewscore'] = 'View Highscore';
$txt['arcade_you_are_first'] = 'You are champion of';
$txt['arcade_you_are_second'] = 'You have the second best score in';
$txt['arcade_you_are_third'] = 'You have the third best score in';
$txt['arcade_is_guest'] = 'Guest';
$txt['arcade_first'] = 'First';
$txt['arcade_second'] = 'Second';
$txt['arcade_third'] = 'Third';
$txt['arcade_dpages'] = 'Pages';
$txt['arcade_dgo_down'] = 'Go Down';
$txt['arcade_dgo_up'] = 'Go Up';
$txt['arcade_dhelp'] = 'Help';
$txt['arcade_defdescript'] = 'Description';
$txt['arcade_info'] = 'Arcade Information';
$txt['arcade_u_b_1'] = 'Welcome to the Arcade,';
$txt['arcade_shout_scored'] = 'Scored ';
$txt['arcade_shout_on'] = ' on ';
$txt['arcade_shout_pb'] = 'New personal best on ';
$txt['arcade_g_i_b_3'] = 'Most Played';
$txt['arcade_g_i_b_5'] = 'for';
$txt['arcade_g_i_b_6'] = 'Played';
$txt['arcade_g_i_b_7'] = 'times.';
$txt['arcade_g_i_b_8'] = 'Latest Champs';
$txt['arcade_g_i_b_9'] = 'champ of';
$txt['arcade_g_i_b_10'] = 'Most Played Games';
$txt['arcade_g_i_b_11'] = 'Longest Champs';
$txt['arcade_b3pb_1'] = 'Best Players';
$txt['arcade_b3pb_2'] = 'with';
$txt['arcade_b3pb_3'] = 'Wins';
$txt['arcade_u_b_2'] = 'Favorites';
$txt['arcade_list_games'] = '- List games by -';
$txt['arcade_nameAZ'] = 'Name A-Z';
$txt['arcade_nameZA'] = 'Name Z-A';
$txt['arcade_LeastPlayed'] = 'Least Played';
$txt['arcade_LatestList'] = 'Latest - All';
$txt['arcade_LatestListNoCat'] = 'Latest - No Category';
$txt['arcade_LeastPlayedGame'] = 'Least Played Games';
$txt['arcade_RatedGames'] = 'Highest Rated Games';
$txt['arcade_LatestGames'] = 'Latest Games';
$txt['arcade_Gamecategory'] = 'Game Categories';
$txt['arcade_plays'] = 'Plays';
$txt['arcade_play_again'] = 'Play Again';
$txt['arcade_play_other'] = 'Play Something Else';
$txt['arcade_rate_game'] = 'Rate';
$txt['arcade_champions_stats'] = 'Arcade Stats';
$txt['arcade_champions_cho'] = 'Champion of';
$txt['arcade_champions_play'] = 'Play';
$txt['arcade_champions_tro'] = 'Game Trophies';
$txt['arcade_champions_th'] = 'Trophies Held';
$txt['arcade_champions_tgp'] = 'Total Game Plays';
$txt['arcade_champions_tsp'] = 'Time Spent Playing';
$txt['is_champ_of'] = 'is champ of';
$txt['arcade_game'] = 'Game';
$txt['arcade_close'] = 'Close';
$txt['arcade_rating_sort'] = 'Rating';
$txt['arcade_topic_talk'] ='Talk';
$txt['arcade_topic_talk2'] ='Report errors or talk about';
$txt['arcade_quick_search'] = 'Search by Name or List Games';
$txt['arcade_info_fav'] = 'Show favorites';
$txt['arcade_info_nocat'] = 'Unassigned Games';
$txt['arcade_AllGames'] = 'All Games';
$txt['arcade_info_shownocat'] = 'Show unassigned games';
$txt['arcade_info_defavatar'] = 'Default Avatar';
$txt['arcade_info_showcat'] = 'Show %s';
$txt['arcade_guest_na'] = 'N/A';
$txt['arcade_search_text'] = 'Search by Name or List Games';
$txt['arcade_no_links'] = '[nolink]';
$txt['arcade_linkname'] = '[link]';
$txt['arcade_defiant_sort_list'] = 'Game Sorting';
$txt['arcade_defiant_read_more'] = '[Read More]';

// Arcade Shoutbox
$txt['arcade_shouts'] = 'Arcade Shouts';
$txt['ArcadeShoutbox_name'] = 'Arcade Shouts';
$txt['arcade_shout'] = 'Shout!';
$txt['arcade_shouted'] = 'Shouted - ';
$txt['arcade_shout_del'] = 'Delete this shout?';
$txt['arcade_shout_guest_score_text'] = 'Guest scored %s on %s';
$txt['arcade_shout_member_score_text'] = '%s scored %s on %s';
$txt['arcade_shout_arena_score_text'] = 'From the Arena: %s scored %s on %s for round %s';

// ROM EmulatorJS
$txt['arcade_rom_list_title'] = 'Arcade ROM Games List';
$txt['arcade_rom_list_desc'] = 'Here you can choose a ROM game to play.';
$txt['arcade_rom_game_title'] = 'Arcade ROM Emulator';
$txt['arcade_retro_arch'] = 'ROM Games';
$txt['arcade_roms_return'] = '[Quit EmulatorJS]';
$txt['arcade_roms_select'] = 'Select Game To Play';
$txt['arcade_link_disabled'] = 'Disabled for unauthorized members';
$txt['arcade_roms_exit_game'] = 'Are you sure you want to quit playing %s ?';

?>