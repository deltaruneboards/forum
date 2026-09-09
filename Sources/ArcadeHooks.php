<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function arcade_array_insert($input, $key, $insert, $where = 'before', $buttonFringe = 0, $strict = false)
{
	global $arcadeModSettings;

	if ($buttonFringe == 3) {
		$newPos = !empty($arcadeModSettings['arcadeButtonIndex']) ? intval($arcadeModSettings['arcadeButtonIndex']) : 0;
		$keys = array_keys($input);
		if ($newPos == 0) {
			$buttonFringe = 1;
		}
		elseif ($newPos >= count($keys)) {
			$buttonFringe = 2;
		}
		else {
			$key = $keys[$newPos];
			$where = 'before';
		}
	}

	$position = array_search($key, array_keys($input), $strict);

	// Key not found -> insert as last
	if ($position === false || $buttonFringe == 2) {
		$input = array_merge($input, $insert);
		return $input;
	}

	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0 || $buttonFringe == 1)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);

	return $input;
}

function arcadeTokenTxtReplace($stringSubject = '')
{
	global $txt;

	if (empty($stringSubject))
		return '';

	$translatable_tokens = preg_match_all('/{(.*?)}/' , $stringSubject, $matches);
	$toFind = array();
	$replaceWith = array();

	if (!empty($matches[1]))
		foreach ($matches[1] as $token) {
			$toFind[] = '{' . $token . '}';
			$replaceWith[] = isset($txt[$token]) ? $txt[$token] : $token;
		}

	return str_replace($toFind, $replaceWith, $stringSubject);
}

function arcadeClearSession()
{
	$id = !empty($_SESSION['current_game_id']) && is_int($_SESSION['current_game_id']) ? (string)$_SESSION['current_game_id'] : '0';
	$arcadeSessions = array('arcade', 'arcade_shout_session', 'arcade_exists', 'arcade_rom_initiate', 'arcade_highscore_list', 'current_cat', 'arcadeNewSessionCheck', 'arcade_download_type', 'arcade_gametype_select', 'arcade_gametype_select_title',
							'playerName', 'arcade_cat_message', 'arcade_cat_file', 'smfArcadeFile', 'arcadeSelectedPath', 'arcadeFirstLoad', 'arcade_cat_icon', 'emulator_admin', 'arcade_admin_check', 'qaction', 'qaction_data', 'smf_arcade_dict',
							'arcade_manage_sort_select_type', 'arcade_manage_sort_select_alpha', 'arcade_game_start_edit', 'search_game_list', 'current_game_id', 'game_data_backup', 'arcade_play_' . $id, 'arcade_play_extra_' . $id, 'arcade_html5_token',
							'arcade_nocats', 'arcade_game_start', 'arcade_sort_dir', 'arcade_gamesearch', 'arcadeSessionDir', 'arcade_do_join', 'smf_game_rom', 'arcade_manage_rom_sort_rom_select_alpha', 'arcade_rom_files', 'arcade_posts');
	foreach ($arcadeSessions as $sess) {
		if (!empty($_SESSION[$sess])) {
			unset($_SESSION[$sess]);
		}
		if (!empty($_SESSION[$sess . '_rom'])) {
			unset($_SESSION[$sess . '_rom']);
		}
	}

	return true;
}

function Arcade_game_support(&$no_stat_actions)
{
	global $sourcedir;
	// IBPArcade v2.x.x Games support
	if (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade')
	{
		$_REQUEST['action'] = 'arcade';

		if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'newscore')
			$_REQUEST['sa'] = 'ibpsubmit2';

		$no_stat_actions += array(
			'arcade' => true);

		require_once($sourcedir . '/Arcade.php');
		return Arcade();
	}
	// IBPArcade v3.x.x Games support
	elseif (isset($_REQUEST['autocom']) && $_REQUEST['autocom'] == 'arcade')
	{
		$_REQUEST['action'] = 'arcade';
		// patch for HTML52-v3
		if (isset($_POST['gname']) && isset($_POST['gscore']) && !empty($_SESSION['game_data_backup']) && !empty($_SESSION['game_data_backup'][$_POST['gname']]) && substr($_SESSION['game_data_backup'][$_POST['gname']]['submit_system'], 0, 5) == 'html5')
		{
			list($_REQUEST['sa'], $_REQUEST['do'], $_REQUEST['act'], $_POST['score'], $_POST['game'], $_POST['game_name'], $_POST['time'], $_POST['gamesessid'], $_POST['autocom']) = array('ibpsubmit2', 'newscore', 'Arcade', $_POST['gscore'], $_SESSION['game_data_backup'][$_POST['gname']]['id'], $_SESSION['game_data_backup'][$_POST['gname']]['internal_name'], time(), $_SESSION['arcade_html5_token'][1], '');
			require_once($sourcedir . '/Arcade.php');
			return 'Arcade';
		}

		if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'savescore')
			$_REQUEST['sa'] = 'ibpsubmit3';
		elseif (isset($_REQUEST['do']) && $_REQUEST['do'] == 'verifyscore')
			$_REQUEST['sa'] = 'ibpverify';

		$no_stat_actions += array(
			'arcade' => true);

		require_once($sourcedir . '/Arcade.php');
		return Arcade();
	}
}

function Arcade_actions(&$actionArray)
{
	global $arcadeModSettings, $modSettings, $sourcedir;

	if (empty($arcadeModSettings['arcadeEnabled']) && !allowedTo('arcade_admin'))
		return;

	$actionArray['arcade'] = array('Arcade.php', 'Arcade');
	$actionArray['retro_arch'] = array('Arcade.php', 'Arcade');
	$actionArray['ingressarcade'] = array('Arcade.php', 'arcadeLogin');
	$arcadeAction = isset($_REQUEST['action']) && $_REQUEST['action'] == 'arcade' ? true : false;
	$arcadeRetroAction = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0;
	$subAction = isset($_REQUEST['sa']) && is_string($_REQUEST['sa']) ? $_REQUEST['sa'] : '';

	// RC3 patch for the moderation log
	$modSettings['moderatelog_enabled'] = !empty($modSettings['modlog_enabled']) ? $modSettings['modlog_enabled'] : 0;

	if (!empty($arcadeRetroAction) && $subAction == 'suggest') {
		$actionArray['arcade'] = array('Arcade.php', 'Arcade');
		$arcadeAction = true;
		$arcadeRetroAction = 0;
	}
	if ($arcadeAction && isset($_REQUEST['play']) && !isset($_REQUEST['game']))
	{
		$_REQUEST['game'] = $_REQUEST['play'];
		unset($_REQUEST['play']);
		$_REQUEST['sa'] = 'play';
		$_SESSION['arcade_rom_initiate'] = '';
		require_once($sourcedir . '/Arcade.php');
		return Arcade($arcadeRetroAction);
	}
	elseif ($arcadeAction && isset($_REQUEST['highscore']) && !isset($_REQUEST['game']))
	{
		$_REQUEST['game'] = $_REQUEST['highscore'];
		unset($_REQUEST['highscore']);
		$_REQUEST['sa'] = 'highscore';
		$_SESSION['arcade_rom_initiate'] = '';
		require_once($sourcedir . '/Arcade.php');
		return Arcade($arcadeRetroAction);
	}
	elseif ($arcadeAction && ((isset($_REQUEST['game']) || isset($_REQUEST['match'])) && !isset($_REQUEST['action'])))
	{
		require_once($sourcedir . '/Arcade.php');
		$_SESSION['arcade_rom_initiate'] = '';
		return Arcade($arcadeRetroAction);
	}
	elseif ($arcadeRetroAction)
	{
		require_once($sourcedir . '/Arcade.php');
		$_SESSION['arcade_rom_initiate'] = '_rom';
		return Arcade($arcadeRetroAction);
	}
}

function Arcade_core_features(&$core_features)
{
	$core_features['arcade'] = array(
		'url' => 'action=admin;area=arcade',
		'settings' => array(
			'arcadeEnabled' => 1,
		),
	);
}

function Arcade_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context, $modSettings, $arcadeModSettings, $txt;

	$permissionList['membergroup'] += array(
		'arcade_view' => array(false, 'arcade', 'arcade'),
		'arcade_play' => array(false, 'arcade', 'arcade'),
		'arcade_view_retro_arch' => array(false, 'arcade', 'arcade'),
		'arcade_submit' => array(false, 'arcade', 'arcade'),
		'arcade_comment' => array(true, 'arcade', 'arcade', 'arcade_moderate'),
		'arcade_user_stats' => array(true, 'arcade', 'arcade', 'arcade_moderate'),
		'arcade_edit_settings' => array(true, 'arcade', 'arcade', 'arcade_moderate'),
		'arcade_create_match' => array(false, 'arcade', 'arcade'),
		'arcade_join_match' => array(false, 'arcade', 'arcade'),
		'arcade_join_invite_match' => array(false, 'arcade', 'arcade'),
		'arcade_admin' => array(false, 'arcade', 'administrate'),
		'arcade_download' => array(false, 'arcade', 'arcade'),
		'arcade_download_rom' => array(false, 'arcade', 'arcade'),
		'arcade_download_type' => array(false, 'arcade', 'arcade'),
		'arcade_new_game' => array(false, 'arcade', 'arcade'),
		'arcade_report' => array(false, 'arcade', 'arcade'),
		'arcade_online' => array(false, 'arcade', 'arcade'),
		'arcade_skin' => array(false, 'arcade', 'arcade'),
		'arcade_list' => array(false, 'arcade', 'arcade'),
		'arcade_gametype_select' => array(false, 'arcade', 'arcade'),
		'arcade_hyperlink' => array(false, 'arcade', 'arcade'),
		'arcade_view_hyperlink' => array(false, 'arcade', 'arcade'),
	);

	$context['non_guest_permissions'] = array_merge(
		$context['non_guest_permissions'],
		array(
			'arcade_admin',
			'arcade_new_game',
			'arcade_create_match',
			'arcade_join_match',
			'arcade_join_invite_match',
			'arcade_comment',
			'arcade_edit_settings',
			'arcade_user_stats',
			'arcade_skin',
			'arcade_list',
		)
	);

	// SMF 2.1.X behavior will differ
	$version = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	if ($version === 'v2.0')
	{
		$permissionGroups['membergroup']['simple'] += array(
			'arcade',
		);
		$permissionGroups['membergroup']['classic'] += array(
			'arcade',
		);
	}
	else
		$permissionGroups['membergroup'] += array(
			'arcade',
		);
}

function Arcade_profile_areas(&$profile_areas)
{
	global $arcadeModSettings, $txt, $context, $settings;

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$context['html_headers'] .= '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-buttons.css?' . $suffixVersion . '" />';
	$allowArena = allowedTo('arcade_admin') ? true : (!empty($arcadeModSettings['arcadeArenaEnabled']) && !empty($arcadeModSettings['arcadeEnabled']) ? true : false);

	$profile_areas['profile_action']['areas'] += array(
		'arcadeChallenge' => array(
			'label' => $txt['sendArcadeChallenge'],
			'file' => 'Profile-Arcade.php',
			'function' => 'arcadeChallenge',
			'icon' => 'arcade_challenge.png',
			'enabled' => $allowArena,
			'permission' => array(
				'own' => array(),
				'any' => array('arcade_create_match'),
			),
		),
	);

	$profile_areas['info']['areas'] += array(
		'arcadeStats' => array(
			'label' => $txt['arcadeStats'],
			'file' => 'Profile-Arcade.php',
			'function' => 'arcadeStats',
			'icon' => 'arcade_stats.gif',
			'enabled' => !empty($arcadeModSettings['arcadeEnabled']) || allowedTo('arcade_admin') ? true : false,
			'permission' => array(
				'own' => array('arcade_user_stats_any', 'arcade_user_stats_own'),
				'any' => array('arcade_user_stats_any'),
			),
		),
	);

	$profile_areas['edit_profile']['areas'] += array(
		'arcadeSettings' => array(
			'label' => $txt['arcadeSettings'],
			'file' => 'Profile-Arcade.php',
			'function' => 'arcadeSettings',
			'icon' => 'arcade_settings.gif',
			'enabled' => !empty($arcadeModSettings['arcadeEnabled']) || allowedTo('arcade_admin') ? true : false,
			'permission' => array(
				'own' => array('arcade_edit_settings_any', 'arcade_edit_settings_own'),
				'any' => array('arcade_edit_settings_any'),
			),
		),
	);
}

function Arcade_menu_buttons()
{
	global $user_info, $context, $settings, $arcadeModSettings, $scripturl, $txt;

	$menu_buttons = !empty($context['menu_buttons']) ? $context['menu_buttons'] : [];

	list($menuButtons, $allMenuButtons, $count, $num) = [[], [], 0, 0];
	$buttonPlacementKey = !empty($arcadeModSettings['arcadeButtonPlacement']) ? intval($arcadeModSettings['arcadeButtonPlacement']) : 0;
	$buttonFringe = !empty($arcadeModSettings['arcadeButtonPlacementExtremity']) ? intval($arcadeModSettings['arcadeButtonPlacementExtremity']) : 0;
	$buttonSequence = !empty($arcadeModSettings['arcadeButtonSequence']) ? 'after' : 'before';
	foreach ($menu_buttons as $keyx => $button) {
		if (!empty($menu_buttons[$keyx]['show'])) {
			$menuButtons[] = $keyx;
			$count++;
		}
		elseif ($count == 0) {
			$buttonFringe = 1;
		}
		elseif ($num == $buttonPlacementKey) {
			$buttonPlacementKey = $count;
			$buttonSequence = 'after';
		}
		$allMenuButtons[] = $keyx;
		$num++;
	}

	$xaction = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) && in_array($_REQUEST['action'], array('arcade', 'retro_arch')) ? true : false;
	$buttonPlacement = array_key_exists($buttonPlacementKey, $menuButtons) ? $menuButtons[$buttonPlacementKey] : 'search';
	$context['allow_arcade'] = allowedTo('arcade_view') && !empty($arcadeModSettings['arcadeEnabled']) ? true : false;
	$_SESSION['current_cat'] = !empty($_SESSION['current_cat']) ? $_SESSION['current_cat'] : 'all';
	$_SESSION['arcade_sortby'] = !empty($_SESSION['arcade_sortby']) ? $_SESSION['arcade_sortby'] : 'age';
	$_SESSION['arcade_sortby_rom'] = !empty($_SESSION['arcade_sortby_rom']) ? $_SESSION['arcade_sortby_rom'] : 'age';
	$reset = isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'reset' ? true : false;
	$sortArray = array('age', 'nocat', 'a2z', 'z2a', 'plays', 'plays_reverse', 'champion', 'champs', 'rating', 'favorites');
	$subs = allowedTo('arcade_edit_settings_own') || allowedTo('arcade_admin') ? true : false;
	$show = $context['allow_arcade'] || allowedTo('arcade_admin') ? true : false;
	$showRetroArch = allowedTo('arcade_view_retro_arch') ? true : (allowedTo('arcade_admin') ? true : false);
	$retroArchLink = !allowedTo('arcade_view_retro_arch') ? '<span class="alert" title="' . $txt['arcade_link_disabled'] . '">' . $txt['arcade_retro_arch'] . '</span>' : $txt['arcade_retro_arch'];
	$currentAction = isset($context['current_action']) && $context['current_action'] == 'retro_arch' ? 'retro_arch' : 'arcade';
	$action = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : 'arcade';
	$action = !empty($arcadeModSettings['arcadeRetroArchEnabled']) && empty($arcadeModSettings['arcadeEnabled']) && empty($arcadeModSettings['arcadeRomToggle']) ? 'retro_arch' : $action;
	$rom = $action == 'retro_arch' ? '_rom' : '';
	$startAt = !empty($_SESSION['arcade_game_start']) ? (int)$_SESSION['arcade_game_start'] : 0;
	$start = !empty($startAt) ? 'start=' . abs($startAt) : '';
	$startAtRom = !empty($_SESSION['arcade_game_start_rom']) ? (int)$_SESSION['arcade_game_start_rom'] : 0;
	$startRom = !empty($startAtRom) ? 'start=' . abs($startAtRom) : '';
	if (in_array($_SESSION['arcade_sortby'], $sortArray) && !$reset)
		$sort = ';sa=list;sortby=' . $_SESSION['arcade_sortby'] . ';' . $start;
	else {
		$_SESSION['arcade_sortby'] = 'age';
		$sort = ';sa=list;sortby=age;dir=desc;';
	}
	if (in_array($_SESSION['arcade_sortby_rom'], $sortArray) && !$reset)
		$sortRom = ';sa=list;sortby=' . $_SESSION['arcade_sortby_rom'] . ';' . $startRom;
	else {
		$_SESSION['arcade_sortby_rom'] = 'age';
		$sortRom = ';sa=list;sortby=age;dir=desc;';
	}
	$arcadeNav = [
		$currentAction => [
			'title' => $action == 'retro_arch' ? $txt['rom_arcade'] : $txt['arcade'],
			'href' => $scripturl . '?action=' . $action . $sort,
			'show' => $show,
			'icon' => '<img src="' . $settings['default_images_url'] . '/icons/arcade_games.png" />',
			'active_button' => $action == $currentAction ? true : false,
			'sub_buttons' => array(
				'arcade' => array(
					'title' => $txt['arcade'],
					'href' => $scripturl . '?action=arcade' . $sort,
					'show' => ($subs && $show ? true : false),
				),
				/* DUMB change: we don't have ROM games
				'retro_arch' => [
					'title' => $retroArchLink,
					'href' => $scripturl . '?action=retro_arch' . $sortRom,
					'show' => ($showRetroArch && empty($arcadeModSettings['arcadeRomToggle']) && !empty($arcadeModSettings['arcadeRetroArchEnabled'])),
				],
				*/
				'profile' => array(
					'title' => $txt['arcadeSettings'],
					'href' => $scripturl . '?action=profile;area=arcadeSettings;u=' . $user_info['id'],
					'show' => ($subs && $show ? true : false),
				),
				'admin' => array(
					'title' => $txt['arcade_nav_admin'],
					'href' => $scripturl . '?action=admin;area=arcade',
					'show' => (allowedTo('arcade_admin') ? true : false),
					'is_last' => true,
				),
			),
		]
	];

	if ((empty($arcadeModSettings['arcadeRetroArchEnabled']) || !allowedTo('arcade_view_retro_arch')) && !empty($arcadeModSettings['arcadeHideDisabled'])) {
		unset($arcadeNav[$currentAction]['sub_buttons']['retro_arch']);
	}
	if ((empty($arcadeModSettings['arcadeEnabled']) || !allowedTo('arcade_view')) && !empty($arcadeModSettings['arcadeHideDisabled'])) {
		unset($arcadeNav[$currentAction]['sub_buttons']['arcade']);
	}

	$new_buttons = arcade_array_insert($menu_buttons, $buttonPlacement, $arcadeNav, $buttonSequence, $buttonFringe);

	if (!empty($arcadeModSettings['arcadeRetroArchEnabled']) || !empty($arcadeModSettings['arcadeEnabled'])) {
		$context['menu_buttons'] = $new_buttons;

		if (!empty($xaction)) {
			foreach ($context['menu_buttons'] as $act => $button) {
				$context['menu_buttons'][$act]['active_button'] = false;
			}
			$context['menu_buttons'][$currentAction]['active_button'] = true;
			//$context['menu_buttons']['home']['active_button'] = false;
			//$context['menu_buttons']['forum']['active_button'] = false;
		}
		else {
			$context['menu_buttons'][$currentAction]['active_button'] = false;
		}
	}
}

function Arcade_admin_areas(&$admin_areas)
{
	global $context, $arcadeModSettings, $scripturl, $txt;

	// add to end of array for SMF 2.1 (<- beta 3 ~ inserting array with unique icons causing issue)
	$startAt = !empty($_SESSION['arcade_game_start']) ? (int)$_SESSION['arcade_game_start'] : 0;
	$start = !empty($startAt) ? 'start=' . abs($startAt) : '';
	$admin_areas += array(
		'arcade' => array(
			'title' => $txt['arcade_admin'],
			'permission' => array('arcade_admin'),
			'areas' => array(
				'arcade' => array(
					'label' => $txt['arcade_general'],
					'icon' => 'arcade_general.png',
					'file' => 'ArcadeAdmin.php',
					'function' => 'ArcadeAdmin',
					'enabled' => allowedTo('arcade_admin') ? true : false,
					'permission' => array('arcade_admin'),
					'subsections' => array(
						'main' => array($txt['arcade_general_information']),
						'settings' => array($txt['arcade_general_settings']),
						'discernible' => array($txt['arcade_discernible_settings']),
						'guide' => array($txt['arcadeHooksGuide']),
						'permission' => array($txt['arcade_general_permissions']),
					'pdl_reports' => array($txt['arcade_general_pdl_reports']),
					),
				),
				'managegames' => array(
					'label' => $txt['arcade_manage_games'],
					'icon' => 'arcade_settings.png',
					'file' => 'ManageGames.php',
					'function' => 'ManageGames',
					'enabled' => allowedTo('arcade_admin') ? true : false,
					'permission' => array('arcade_admin'),
					'subsections' => array(
						'main' => array($txt['arcade_manage_games_edit_games']),
						'install' => array($txt['arcade_manage_games_install']),
						'upload' => array($txt['arcade_manage_games_upload']),
					),
				),
				'manageromgames' => array(
					'label' => $txt['arcade_manage_rom_games'],
					'icon' => 'arcade_rom.png',
					'file' => 'ManageGames.php',
					'function' => 'ManageGames',
					'enabled' => allowedTo('arcade_admin') ? true : false,
					'permission' => array('arcade_admin'),
					'subsections' => array(
						'rom_main' => array($txt['arcade_manage_games_rom_edit_games']),
						'rom_install' => array($txt['arcade_manage_rom_games_install']),
						'rom_upload' => array($txt['arcade_manage_rom_games_upload']),
					),
				),
				'arcadecategory' => array(
					'label' => $txt['arcade_manage_category'],
					'icon' => 'arcade_categories.png',
					'file' => 'ArcadeAdmin.php',
					'function' => 'ArcadeAdminCategory',
					'enabled' => allowedTo('arcade_admin') ? true : false,
					'permission' => array('arcade_admin'),
					'subsections' => array(
						'list' => array($txt['arcade_manage_category_list']),
						'new' => array($txt['arcade_manage_category_new']),
					),
				),
				'arcademaintenance' => array(
					'label' => $txt['arcade_maintenance'],
					'icon' => 'arcade_maintenance.png',
					'file' => 'ArcadeMaintenance.php',
					'function' => 'ArcadeMaintenance',
					'enabled' => allowedTo('arcade_admin') ? true : false,
					'permission' => array('arcade_admin'),
					'subsections' => array(
						'main' => array($txt['arcade_maintenance_main']),
						'highscore' => array($txt['arcade_maintenance_highscore']),
						'category' => array($txt['arcade_maintenance_category']),
						'xframe' => array($txt['arcade_maintenance_xframe']),
						'engine' => array($txt['arcade_maintenance_engine']),
						'cronjobs' => array($txt['arcade_maintenance_cronjobs']),
						'ruffle' => array($txt['arcade_maintenance_ruffle']),
						'rom_git' => array($txt['arcade_maintenance_ejs']),
					),
				),
			),
		),
	);

	// JS to facilitate adding unique admin icons
	$context['html_headers'] .= '
		<script>
			function arcadelFixAdminIcons() {
				if (document.getElementsByClassName("large_admin_menu_icon_file") && document.getElementsByClassName("large_admin_menu_icon_file")[0]) {
					$("#group_arcade .large_admin_menu_icon_file").after("<br>");
				}' . (!empty($_SESSION['arcade_game_start_edit']) ? '
				var arcadminmenu = document.getElementById("adm_submenus");
				var arcadminlist = arcadminmenu.getElementsByTagName("A")[0];
				if (arcadminlist && arcadminlist.href) {
					if (arcadminlist.href.indexOf("area=managegames;sa=main;"))
						document.getElementById("adm_submenus").getElementsByTagName("A")[0].href = arcadminlist.href.replace("area=managegames;sa=main;", "area=managegames;sa=main;start=' . $_SESSION['arcade_game_start_edit'] . ';");
				}' : '') . '
			}
			if (window.addEventListener)
				window.addEventListener("load", arcadelFixAdminIcons, false);
			else if (window.attachEvent)
				window.attachEvent("onload", arcadelFixAdminIcons);
		</script>';

	// add extra uninstall options here
	if (allowedTo('admin'))
		Arcade_uninstall_options();
}

function Arcade_load_theme()
{
	global $boarddir, $boardurl, $arcadeModSettings, $context, $settings, $user_info, $txt;

	$arcade_version = !empty($arcadeModSettings['arcadeVersion']) ? $arcadeModSettings['arcadeVersion'] : '';
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$currentArcadeAction = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (!empty($_SESSION['arcade_rom_initiate']) ? 'retro_arch' : 'arcade');
	$_SESSION['arcadeNewSessionCheck'] = !empty($_SESSION['arcadeNewSessionCheck']) && !empty($_SESSION['arcade_isMobilePlay']) ? 1 : 0;
	$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-func.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeMobileDetect.js?' . $suffixVersion . '"></script>
	<script type="text/javascript">
		var arcadePhpDetectMobile = ' . (!empty($_SESSION['arcade_isMobilePlay']) ? 'true' : 'false') . ';
		var arcadePhpDetectSession = ' . (!empty($_SESSION['arcadeNewSessionCheck']) ? 'true' : 'false') . ';
		var arcadeDetectGameAction = "' . ($currentArcadeAction) . '";
	</script>';

	// patch for v3Arcade save type
	$siteDir = '/' . basename($boarddir) . '/arcade.php';
	$sessdo = str_replace('\\', '/', $_SERVER['REQUEST_URI']);
	$check = stripos($siteDir, $sessdo) !== false || stripos($sessdo, $siteDir) !== false ? true : false;

	if ($check && isset($_REQUEST['sessdo']))
	{
		$id = !empty($_SESSION['current_game_id']) ? $_SESSION['current_game_id'] : 0;
		$_SESSION['arcade']['play_vb3g'][$id] = $id;

		list($_POST['action'], $_REQUEST['arcade'], $_POST['v3arcade'], $_REQUEST['v3arcade']) = array('arcade', 'arcade', true, 1);

		if (isset($_REQUEST['gamename']))
		{
			$_POST['game'] = $_REQUEST['gamename'];
			$_POST['gamename'] = $id;
		}

		if ($_REQUEST['sessdo'] == 'sessionstart')
			$_POST['sa'] = 'vbSessionStart';
		elseif ($_REQUEST['sessdo'] == 'permrequest')
			$_POST['sa'] = 'vbPermRequest';
		elseif ($_REQUEST['sessdo'] == 'burn')
			$_POST['sa'] = 'vbBurn';

		$_POST['seesdo'] = $_REQUEST['sessdo'];
		require_once($boarddir . '/index.php');
	}

	// Device detection depends on 3rd party PHP classes
	if (!class_exists('Mobile_Detect'))
	{
		require_once($boarddir . '/ArcadeSources/Mobile_Detect.php');
	}
	if (!class_exists('Detect'))
	{
		require_once($boarddir . '/ArcadeSources/Detect.php');
	}

	if (class_exists('Mobile_Detect') && class_exists('Detect'))
	{
		// Boolean ~ output is true or false
		$_SESSION['arcade_isMobile'] = Detect::isMobile();
		$_SESSION['arcade_isMobilePlay'] = $_SESSION['arcade_isMobile'];

		// String ~ output is "Computer", "Phone" or "Tablet"
		$_SESSION['arcade_deviceType'] = Detect::deviceType();
	}

	// SMF-Arcade PEAR / Archive_Tar class plug-in
	if (!empty($arcadeModSettings['arcadeArchiveTar']) && !class_exists('PEAR', false) && file_exists($boarddir . '/ArcadeSources/ArcadePEAR.php')) {
		define('PEAR_ERROR_RETURN',     1);
		define('PEAR_ERROR_PRINT',      2);
		define('PEAR_ERROR_TRIGGER',    4);
		define('PEAR_ERROR_DIE',        8);
		define('PEAR_ERROR_CALLBACK',  16);
		define('PEAR_ERROR_EXCEPTION', 32);

		if (substr(PHP_OS, 0, 3) == 'WIN') {
			define('OS_WINDOWS', true);
			define('OS_UNIX',    false);
			define('PEAR_OS',    'Windows');
		}
		else {
			define('OS_WINDOWS', false);
			define('OS_UNIX',    true);
			define('PEAR_OS',    'Unix');
		}

		$GLOBALS['_PEAR_default_error_mode']     = PEAR_ERROR_RETURN;
		$GLOBALS['_PEAR_default_error_options']  = E_USER_NOTICE;
		$GLOBALS['_PEAR_destructor_object_list'] = array();
		$GLOBALS['_PEAR_shutdown_funcs']         = array();
		$GLOBALS['_PEAR_error_handler_stack']    = array();

		if(function_exists('ini_set')) {
			@ini_set('track_errors', true);
		}

		require_once($boarddir . '/ArcadeSources/ArcadePEAR.php');
	}
	if (!empty($arcadeModSettings['arcadeArchiveTar']) && class_exists('PEAR', false) && !class_exists('Archive_Tar', false) && file_exists($boarddir . '/ArcadeSources/ArcadeTar.php')) {
		define('ARCHIVE_TAR_ATT_SEPARATOR', 90001);
		define('ARCHIVE_TAR_END_BLOCK', pack("a512", ''));
		require_once($boarddir . '/ArcadeSources/ArcadeTar.php');
	}

	return;
}

function Arcade_load_language()
{
	global $boarddir, $arcadeModSettings, $modSettings, $smcFunc, $db_type, $txt;

	// load Arcade mod settings
	loadArcadeModSettings();

	// SMF-Arcade cron tasks
	Arcade_cron_tasks();

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeFormatting.php');
	loadLanguage('Arcade');
	loadLanguage('ArcadeAdmin');
	Arcade_who_fix();
	if (!empty($_SESSION['arcade_admin_check']))
		unset($_SESSION['arcade_admin_check']);

	// EhPortal patch to disable its responsive bootstrap while viewing the game list
	$action = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) ? strtolower($_REQUEST['action']) : '';
	$sa = !empty($_REQUEST['sa']) && is_string($_REQUEST['sa']) ? strtolower($_REQUEST['sa']) : 'list';
	$hookCheck = !empty($modSettings['integrate_pre_log_stats']) ? explode(',', $modSettings['integrate_pre_log_stats']) : array();
	if (in_array($action, array('arcade', 'retro_arch')) && in_array('EhPortal_log_stats', $hookCheck) && $sa == 'list') {
		$modSettings['sp_display_responsive'] = 0;
		$modSettings['sp_portal_mode'] = 0;
	}

	// ensure 16M minimum for MySQL/MariaDB max_allowed_packet size (PostgreSQL has no limit)
	list($trigger, $dbGlobSetting) = array(false, !empty($arcadeModSettings['arcade_db_glob_setting']) ? (int)$arcadeModSettings['arcade_db_glob_setting'] : 0);
	if (stripos($db_type, 'mysql') !== FALSE && !empty($dbGlobSetting)) {
		switch ($dbGlobSetting) {
			case 1:
				$request = $smcFunc['db_query']('', '
					SHOW VARIABLES LIKE {string:pack}',
					array('pack' => 'max_allowed_packet')
				);

				$dbrow = $smcFunc['db_fetch_assoc']($request);
				$trigger = !empty($dbrow['Value']) && (int)$dbrow['Value'] < 16777216 ? true : (empty($dbrow) ? true : false);
				$smcFunc['db_free_result']($request);
				break;
			case 2:
				$request = $smcFunc['db_query']('', '
					SELECT @@global.{string:pack}',
					array('pack' => 'max_allowed_packet')
				);
				$dbrow2 = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);
				$trigger = !empty($dbrow2) && !empty($dbrow2[array_keys($dbrow2)[0]]) && (int)$dbrow2[array_keys($dbrow2)[0]] < 16777216 ? true : (empty($dbrow2) ? true : false);
				break;
			default:
				$trigger = false;
		}

		if (!empty($trigger)) {
			$smcFunc['db_query']('', '
				SET GLOBAL max_allowed_packet = {int:pack}',
				array('pack' => 16777216)
			);
		}
	}
}

function Arcade_uninstall_options()
{
	global $context, $scripturl, $smcFunc, $txt;

	if (isset($_REQUEST['action']) && isset($_REQUEST['area']) && isset($_REQUEST['sa']) && isset($_REQUEST['package']) && allowedTo('admin')) {
		$current = array($_REQUEST['action'], $_REQUEST['area'], $_REQUEST['sa']);
		$check = array_diff(array('admin', 'packages', 'uninstall'), $current);
		if (empty($check) && (stripos($_REQUEST['action'], 'smf_arcade') !== FALSE || stripos($_REQUEST['package'], 'smf-arcade') !== FALSE)) {
			$bytes = openssl_random_pseudo_bytes(16);
			$_SESSION['arcade_admin_check'] = bin2hex($bytes);
			$context['html_headers'] .= '
	<script>
		$(document).ready(function () {
			$("#custom_changes > div:nth-child(1)").after(\'<div class="table_grid" style="display: table;width: 100%;position: relative;margin-left: 1rem;"><div style="display: table-row;"><div style="display: table-cell;width: 4%;vertical-align: middle;"><input id="arcade_uninstall_themes_checkall" style="vertical-align: middle;" type="checkbox" class="floatright" /></div><div style="display: table-cell;width: 96%;vertical-align: middle;padding-left: 0.65rem;">' . $txt['arcade_games_uninstall_theme_files'] . '</div></div></div>\');
			if ($("[id^=dummy_theme_]:checked").length == $("[id^=dummy_theme_]").length) {
				$("input#arcade_uninstall_themes_checkall").prop("checked", true);
			}
			$("[id^=dummy_theme_]").change(function(){
				if ($("[id^=dummy_theme_]:checked").length == $("[id^=dummy_theme_]").length) {
					$("input#arcade_uninstall_themes_checkall").prop("checked", true);
				}
				else {
					$("input#arcade_uninstall_themes_checkall").prop("checked", false);
				}
			});
			$("input#arcade_uninstall_themes_checkall").on("change", function(evt) {
				if($("input#arcade_uninstall_themes_checkall").is(":checked") == true) {
					$("[id^=dummy_theme_]").prop("checked", true);
				}
				else {
					$("[id^=dummy_theme_]").prop("checked", false);
				}
			});
			$(\'input[name="do_db_changes"]\').parent().before("<div style=\'display: flex;align-items: center;\'><input style=\'padding: 0.25rem 3.5rem 0.25rem 3.5rem;vertical-align: middle;\' id=\'remove_arcade_db_files\' type=\'checkbox\' /><label style=\'padding: 0.25rem 0rem 0.25rem 0.5rem;vertical-align: middle;\' for=\'remove_arcade_db\'>' . $txt['arcade_uninstall_database_files'] . '</label></div>");
			$(\'input[name="do_db_changes"]\').css({"display": "none"});
			$("input#remove_arcade_db_files").on("change", function(evt) {
				let option = false;
				if($("input#remove_arcade_db_files").is(":checked") == true) {
					if (confirm("' . $txt['arcade_confirm_remove_db_files'] . '") == true) {
						option = true;
					} else {
						option = false;
						this.checked = false;
					}
				}
				else {
					option = true;
				}
				return option;
			});
			$("#view_package").submit(function() {
				var arcade_db = 0, arcade_files = 0;
				if($("input#remove_arcade_db_files").is(":checked") == true) {
					  arcade_db = 1;
					  arcade_files = 1;
				}
				if (arcade_db == 1 && arcade_files == 1) {
					$.ajax({
						type: "POST",
						url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=uninstall;",
						data: {"arcade_db": arcade_db, "arcade_files": arcade_files, "fileid": "' . $_SESSION['arcade_admin_check'] . '", "' . $context['session_var'] . '": "' . $context['session_id'] . '","delete": "1"},
						success: function(data){console.log(data);}
					});
				}
				return true;
			});
			var changesArray = ["' . $txt['arcade_uninstall_remove_db_files'] . '"];
			$("div#db_changes_div > ul.normallist").empty();
			$.each(changesArray, function( key, value ) {
				$("div#db_changes_div > ul.normallist").append("<li>" + value + "</li>");
			});
		});
	</script>';
		}
	}

	return false;
}

function Arcade_who_fix()
{
	global $smcFunc, $arcade_online;

	if (empty($arcade_online))
	{
		$arcade_online = array();
		$result = $smcFunc['db_query']('', '
			SELECT id_game, game_name, enabled
			FROM {db_prefix}arcade_games
			WHERE enabled = 1
			ORDER BY id_game ASC',
			array()
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$arcade_online[$row['id_game']] = $row['game_name'];
		$smcFunc['db_free_result']($result);
	}
}

function Arcade_admin_search($language_files, $include_files, $settings_search)
{
	$language_files[] = 'ArcadeAdmin';
	$include_files[] = 'ArcadeAdmin';
	$settings_search[] = array('ArcadeAdminSettings', 'area=arcade;sa=settings');
	$settings_search[] = array('ArcadeAdminPermission', 'area=arcade;sa=permission');
}

function Arcade_gameData($conditions = '', $rom = 0)
{
	global $scripturl, $arcadeModSettings, $smcFunc, $context, $txt;

	// example variable assignments
	// 'WHERE enabled=1 ORDER BY id_game DESC LIMIT 0,20'
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? 'rom_flag = {int:rom_flag} AND ' : '';
	if (!empty($conditions) && !empty($whererom) && stripos($conditions, 'where') !== false) {
		$conditions = str_ireplace('where', 'WHERE ' . $whererom, $conditions);
	}
	$conditions = !empty($conditions) ? $conditions : 'WHERE ' . $whererom . 'enabled=1 ORDER BY id_game DESC';
	$gamesList = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, game_name, game_directory, game_file, description, help, submit_system, id_cat, local_permissions, score_type, rom_flag,
		member_groups, game_rating, id_champion, id_champion_score, extra_data, num_plays, num_rates, num_favorites, id_topic, thumbnail, enabled, rom_system, cover_icon
		FROM {db_prefix}arcade_games{raw:conditions}',
		array(
			'conditions' => !empty($conditions) ? ' ' . $conditions : '',
			'rom_flag' => !empty($rom) ? 1 : 0,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$gamesList[] = $row;

	$smcFunc['db_free_result']($request);

	return empty($gamesList) ? array() : $gamesList;
}

function Arcade_whos_online($actions)
{
	global $arcade_online, $txt;

	$action = '';
	if (!empty($actions['action']) && $actions['action'] == 'arcade')
	{
		$actions['game'] = !empty($actions['game']) ? (int)$actions['game'] : 0;
		$id = abs($actions['game']);
		if (!isset($actions['sa']) || $actions['sa'] == 'list')
			$action = $txt['who_arcade'];
		elseif ($actions['sa'] == 'play' && !empty($id) && !empty($arcade_online[$id]))
			$action = sprintf($txt['who_arcade_play'], $id, $arcade_online[$id]);
		elseif ($actions['sa'] == 'highscore' && !empty($id) && !empty($arcade_online[$id]))
			$action = sprintf($txt['who_arcade_highscore'], $id, $arcade_online[$id]);
		else
			$action = $txt['who_arcade'];
	}

	return $action;
}

function Arcade_integrate_skins($type, $flag, $specific = 0)
{
	global $smcFunc, $boarddir;
	$conditions = !empty($flag) && !allowedTo('arcade_admin') ? 'WHERE enabled = 1' : '';
	if (!empty($conditions) && $specific > 0)
		$conditions = $conditions . ' AND id_skin = ' . $specific;
	elseif ($specific > 0)
		$conditions = 'WHERE id_skin = ' . $specific;

	$type = $type == 'mobile' ? $type . '_' : '';
	$arcadeSkins = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_skin, skin_name, skin_source_file, skin_function, admin_function, lang_function, skin_template, enabled
		FROM {db_prefix}arcade_' . $type . 'skins{raw:conditions}
		ORDER BY id_skin ASC',
		array(
			'conditions' => !empty($conditions) ? ' ' . $conditions : '',
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$arcadeSkins[$row['id_skin']] = array(
			'id_skin' => $row['id_skin'],
			'skin_name' => $row['skin_name'],
			'skin_source_file' => $row['skin_source_file'],
			'skin_function' => $row['skin_function'],
			'admin_function' => !empty($row['admin_function']) ? $row['admin_function'] : '',
			'lang_function' => $row['lang_function'],
			'skin_template' => $row['skin_template'],
			'enabled' => $row['enabled']
		);
	}

	$smcFunc['db_free_result']($request);

	return $arcadeSkins;
}

function Arcade_integrate_lists($type, $flag, $specific = 0)
{
	global $smcFunc;
	$conditions = !empty($flag) && !allowedTo('arcade_admin') ? 'WHERE enabled = 1' : '';
	if (!empty($conditions) && $specific > 0)
		$conditions = $conditions . ' AND id_list = ' . $specific;
	elseif ($specific > 0)
		$conditions = 'WHERE id_list = ' . $specific;
	$type = $type == 'mobile' ? $type . '_' : '';
	$arcadeLists = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_list, list_name, list_source_file, list_function, admin_function, lang_function, list_template, enabled
		FROM {db_prefix}arcade_' . $type . 'lists{raw:conditions}
		ORDER BY id_list ASC',
		array(
			'conditions' => !empty($conditions) ? ' ' . $conditions : '',
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$arcadeLists[$row['id_list']] = array(
			'id_list' => $row['id_list'],
			'list_name' => $row['list_name'],
			'list_source_file' => $row['list_source_file'],
			'list_function' => $row['list_function'],
			'admin_function' => !empty($row['admin_function']) ? $row['admin_function'] : '',
			'lang_function' => $row['lang_function'],
			'list_template' => $row['list_template'],
			'enabled' => $row['enabled']
		);
	}

	$smcFunc['db_free_result']($request);

	return $arcadeLists;
}

function Arcade_viewModLog(&$listOptions, &$moderation_menu_name)
{
	global $scripturl, $txt, $smcFunc, $user_info, $context;
	$startAt = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
	list($start, $items_per_page, $sort, $query_string, $query_params, $log_type, $ignore_boards) = array(
		abs($startAt),
		$context['displaypage'],
		$context['order'] == 'time' ? 'lm.log_time' : $context['order'],
		$listOptions['get_items']['params'][0],
		$listOptions['get_items']['params'][1],
		$listOptions['get_items']['params'][2],
		false
	);
	// $start, $items_per_page, $sort, $query_string = '', $query_params = array(), $log_type = 1, $ignore_boards = false
	$modlog_query = allowedTo('admin_forum') || $user_info['mod_cache']['bq'] == '1=1' ? '1=1' : (($user_info['mod_cache']['bq'] == '0=1' || $ignore_boards) ? 'lm.id_board = 0 AND lm.id_topic = 0' : (strtr($user_info['mod_cache']['bq'], array('id_board' => 'b.id_board')) . ' AND ' . strtr($user_info['mod_cache']['bq'], array('id_board' => 't.id_board'))));
	$entries = array();

	if (!isset($context['uneditable_actions']))
		$context['uneditable_actions'] = array();

	// Can they see the IP address?
	$seeIP = allowedTo('moderate_forum');

	// Here we have the query getting the log details.
	$result = $smcFunc['db_query']('', '
		SELECT
			lm.id_action, lm.id_member, lm.ip, lm.log_time, lm.action, lm.id_board, lm.id_topic, lm.id_msg, lm.extra,
			mem.real_name, mg.group_name
		FROM {db_prefix}log_actions AS lm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lm.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = {int:reg_group_id} THEN mem.id_post_group ELSE mem.id_group END)
			LEFT JOIN {db_prefix}boards AS b ON (b.id_board = lm.id_board)
			LEFT JOIN {db_prefix}topics AS t ON (t.id_topic = lm.id_topic)
		WHERE lm.id_log = {int:log_type}
			AND {raw:modlog_query}'
			. (!empty($query_string) ? '
			AND ' . $query_string : '') . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:max}',
		array_merge($query_params, array(
			'reg_group_id' => 0,
			'log_type' => $log_type,
			'modlog_query' => $modlog_query,
			'sort' => $sort,
			'start' => $start,
			'max' => $items_per_page,
		))
	);

	// Arrays for decoding objects into.
	$games = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$row['extra'] = $smcFunc['json_decode']($row['extra'], true);

		// Corrupt?
		$row['extra'] = is_array($row['extra']) ? $row['extra'] : array();

		// A game?
		if (isset($row['extra']['game']))
		{
			$games[(int) $row['extra']['game']][] = $row['id_action'];

			// The array to go to the template. Note here that action is set to a "default" value of the action doesn't match anything in the descriptions. Allows easy adding of logging events with basic details.
			$entries[$row['id_action']] = array(
				'id' => $row['id_action'],
				'ip' => $seeIP ? inet_dtop($row['ip']) : $txt['logged'],
				'position' => empty($row['real_name']) && empty($row['group_name']) ? $txt['guest'] : $row['group_name'],
				'moderator_link' => $row['id_member'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : (empty($row['real_name']) ? ($txt['guest'] . (!empty($row['extra']['member_acted']) ? ' (' . $row['extra']['member_acted'] . ')' : '')) : $row['real_name']),
				'time' => timeformat($row['log_time']),
				'timestamp' => forum_time(true, $row['log_time']),
				'editable' => substr($row['action'], 0, 8) !== 'clearlog' && !in_array($row['action'], $context['uneditable_actions']),
				'extra' => $row['extra'],
				'action' => $row['action'],
				'action_text' => isset($row['action_text']) ? $row['action_text'] : '',
				'name' => !empty($row['real_name']) ? $row['real_name'] : '',
			);
		}
	}
	$smcFunc['db_free_result']($result);

	if (!empty($games))
	{
		$result = $smcFunc['db_query']('', '
			SELECT id_game, game_name
			FROM {db_prefix}arcade_games
			WHERE id_game IN ({array_int:games})
			LIMIT ' . count(array_keys($games)),
			array(
				'games' => array_keys($games),
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			foreach ($games[$row['id_game']] as $action)
			{
				$this_action = &$entries[$action];
				$this_action['extra']['game'] = '<a href="' . $scripturl . '?action=arcade;game=' . $row['id_game'] . '">' . $row['game_name'] . '</a>';
			}
		}
	  $smcFunc['db_free_result']($result);
	}

	// Do some formatting of the action string.
	foreach ($entries as $k => $entry)
	{
		// Make any message info links so its easier to go find that message.
		if (isset($entry['extra']['message']) && (empty($entry['message']) || empty($entry['message']['id'])))
			$entries[$k]['extra']['message'] = '<a href="' . $scripturl . '?msg=' . $entry['extra']['message'] . '">' . $entry['extra']['message'] . '</a>';

		// Mark up any deleted games.
		if (!empty($entry['extra']['game']) && is_numeric($entry['extra']['game']))
			$entries[$k]['extra']['game'] = sprintf($txt['modlog_id'], $entry['extra']['game']);

		if (isset($entry['extra']['report']))
		{
			// Member profile reports go in a different area
			if (stristr($entry['action'], 'user_report'))
				$entries[$k]['extra']['report'] = '<a href="' . $scripturl . '?action=moderate;area=reportedmembers;sa=details;rid=' . $entry['extra']['report'] . '">' . $txt['modlog_report'] . '</a>';
			else
				$entries[$k]['extra']['report'] = '<a href="' . $scripturl . '?action=moderate;area=reportedposts;sa=details;rid=' . $entry['extra']['report'] . '">' . $txt['modlog_report'] . '</a>';
		}

		if (empty($entries[$k]['action_text']))
			$entries[$k]['action_text'] = isset($txt['modlog_ac_' . $entry['action']]) ? $txt['modlog_ac_' . $entry['action']] : $entry['action'];
		$entries[$k]['action_text'] = preg_replace_callback('~\{([A-Za-z\d_]+)\}~i',
			function($matches) use ($entries, $k)
			{
				return isset($entries[$k]['extra'][$matches[1]]) ? $entries[$k]['extra'][$matches[1]] : '';
			}, $entries[$k]['action_text']);
	}

	// Back we go!
	$SMF_Entries = list_getModLogEntries($start, $items_per_page, $sort, $query_string = '', $query_params = array(), $log_type = 1, $ignore_boards = false);

	return !empty($entries) ? array_merge($entries, $SMF_Entries) : $SMF_Entries;
}

function Arcade_validate_session(&$types)
{
	global $modSettings;

	// large files & long tasks may fail unless this is temporarily disabled
	if (isset($_REQUEST['action']) && is_string($_REQUEST['action']) && $_REQUEST['action'] == 'admin') {
		$area = isset($_REQUEST['area']) && is_string($_REQUEST['area']) ? $_REQUEST['area'] : '';
		$areaArray = array('ruffle_upload', 'ruffle_upload2', 'rom_git', 'rom_git2', 'rom_git3');
		if (in_array($area, $areaArray))
			$modSettings['securityDisable'] = 1;
	}
}

function arcade_custom_profile($memID, $area)
{
	global $context, $settings, $txt, $arcadeModSettings, $user_info, $scripturl, $smcFunc;
	$showProfileData = allowedTo('admin_forum') || !empty($arcadeModSettings['arcadeEnableDownload']) ? true : false;
	$show =  !empty($arcadeModSettings['arcadeEnabled']) && allowedTo('arcade_moderate') ? true : false;
	$action = isset($_REQUEST['action']) ? (string)$_REQUEST['action'] : '';
	list($memberReports, $count) = array('', 0);
	if (isset($_REQUEST['area']) || strtolower($action) != 'profile')
		return;

	$memberData = [
		'dcount' => 0,
		'arcade_online_time' => '',
		'online_time' => $txt['arcade_profile_unknown']
	];

	if ($showProfileData && allowedTo('arcade_moderate'))
	{
		$request = $smcFunc['db_query']('', '
			SELECT md.download_count, amd.online_time, mem.last_login
			FROM {db_prefix}arcade_members AS md
			LEFT JOIN {db_prefix}arcade_member_data AS amd ON (amd.id_member = md.id_member)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = md.id_member)
			WHERE md.id_member = {int:memberid}
			LIMIT 1',
			array(
				'memberid' => $memID,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$memberData = [
				'dcount' => empty($row['download_count']) ? 0 : (int)$row['download_count'],
				'arcade_online_time' => empty($row['online_time']) ? '' : timeformat($row['online_time']),
				'online_time' => empty($row['last_login']) ? $txt['arcade_profile_unknown'] : timeformat($row['last_login'])
			];

		}

		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT pd2.report_id, pd2.pdl_gameid, pd2.game_name
			FROM {db_prefix}arcade_pdl2 AS pd2
			WHERE pd2.report_id = {int:memberid}',
			array(
				'memberid' => $memID,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$memberReports .= ' <a href="' . $scripturl. '?action=admin;area=managegames;sa=edit;game=' . $row['pdl_gameid'] . '">' . $row['game_name'] . '</a>,';
			$count++;
		}
		$smcFunc['db_free_result']($request);
		$memberReports = rtrim($memberReports, ',');

		if ((!empty($memberData) || !empty($memberReports)) && !empty($arcadeModSettings['arcadeProfileView'])) {
			$custom_fields = !empty($context['custom_fields']) ? $context['custom_fields'] : array();
			$key = count($custom_fields) > 0 ? count($custom_fields) - 1 : 0;
			$hr = !empty($custom_fields[$key]) ? '<hr />' : '';
			$viewOption = (int)$arcadeModSettings['arcadeProfileView'];
			$new_fields = [
				'name' => $hr . arcadeTokenTxtReplace($txt['arcade_profile_info']),
				'desc' => arcadeTokenTxtReplace($txt['arcade_profile_desc']),
				'title' => arcadeTokenTxtReplace($txt['arcade_profile_desc']),
				'type' => 'html',
				'order' => count($custom_fields)+2,
				'input_html' => '',
				'placement' => 0,
				'output_html' => $hr . '
					<p>
						<div style="line-height: 2em;display: flex;align-items: center;">
							<img title="' . $txt['arcade_profile_downloads'] . '" src="' . $settings['default_images_url'] . '/arc_icons/profile_download.png" alt="" style="vertical-align: middle;width: 20px;height: 20px;border: 0px;" /> ' . ($viewOption == 1 ? $txt['arcade_profile_downloads'] . ': ' : '') . strval($memberData['dcount']) . '
						</div>
						<div style="line-height: 2em;display: flex;align-items: center;">' . (!empty($memberData['arcade_online_time']) ? '
							<img title="' . $txt['arcade_profile_arcade_online'] . '" src="' . $settings['default_images_url'] . '/arc_icons/profile_online.png" alt="" style="vertical-align: middle;width: 20px;height: 20px;border: 0px;" /> ' . ($viewOption == 1 ? $txt['arcade_profile_arcade_online'] . ': ' : '') . $memberData['arcade_online_time'] : '
							<img title="' . $txt['arcade_profile_online'] . '" src="' . $settings['default_images_url'] . '/arc_icons/profile_online.png" alt="" style="vertical-align: middle;width: 20px;height: 20px;border: 0px;" /> ' . ($viewOption == 1 ? $txt['arcade_profile_online'] . ': ' : '') . $memberData['online_time']) . '
						</div>
						<hr />
						<div style="line-height: 2em;display: flex;align-items: center;">
							<img title="' . $txt['arcade_profile_num_reports'] . '" src="' . $settings['default_images_url'] . '/arc_icons/profile_reported.png" alt="" style="vertical-align: middle;width: 20px;height: 20px;border: 0px;" /> ' . ($viewOption == 1 ? $txt['arcade_profile_num_reports'] . ': ' : '') . strval($count) . '
						</div>' . ($count > 0 ? '
						<div style="line-height: 2em;display: flex;align-items: center;">
							<img title="' . $txt['arcade_profile_reports'] . '" src="' . $settings['default_images_url'] . '/arc_icons/profile_rlist.png" alt="" style="vertical-align: middle;width: 20px;height: 20px;border: 0px;" /> ' . ($viewOption == 1 ? $txt['arcade_profile_reports'] . ': ' : '') . '<div style="overflow-wrap: break-word;display: inline;">' . $memberReports . '</div>
						</div>' : '') . '
					</p>',
				'colname' => 'cust_arcade',
				'value' => '',
				'show_reg' => 1,
				'first_item' => 'hr',
			];
			$context['custom_fields'] = array_merge_recursive($custom_fields, array($new_fields));
		}
	}
}

function updateArcadeSettings($changeArray, $update = false)
{
	global $arcadeModSettings, $smcFunc;

	if (empty($changeArray) || !is_array($changeArray))
		return;

	$toRemove = array();
	foreach ($changeArray as $k => $v)
		if ($v === null)
		{
			unset($changeArray[$k]);
			$toRemove[] = $k;
		}

	if (!empty($toRemove))
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_modsettings
			WHERE variable IN ({array_string:remove})',
			array(
				'remove' => $toRemove,
			)
		);

	if ($update)
	{
		$existing = array();
		$request = $smcFunc['db_query']('', '
			SELECT variable
			FROM {db_prefix}arcade_modsettings
			WHERE variable IN ({array_string:vars})',
			array(
				'vars' => array_keys($changeArray),
			)
		);

		while ($row = $smcFunc['db_fetch_row']($request)) {
			if (!empty($row['variable']))
				$existing[] = $row['variable'];
		}
		$smcFunc['db_free_result']($request);
		foreach ($changeArray as $variable => $value)
		{
			if (!in_array($variable, $existing)) {
				$smcFunc['db_insert']('replace',
					'{db_prefix}arcade_modsettings',
					array('variable' => 'string-255', 'value' => 'string-65534'),
					array($variable, $value),
					array('variable')
				);
			}
			else {
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_modsettings
					SET value = {' . ($value === false || $value === true ? 'raw' : 'string') . ':value}
					WHERE variable = {string:variable}',
					array(
						'value' => $value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value),
						'variable' => $variable,
					)
				);
			}
			$arcadeModSettings[$variable] = $value === true ? $arcadeModSettings[$variable] + 1 : ($value === false ? $arcadeModSettings[$variable] - 1 : $value);
		}

		return;
	}

	$replaceArray = array();
	foreach ($changeArray as $variable => $value)
	{
		if (isset($arcadeModSettings[$variable]) && $arcadeModSettings[$variable] == $value)
			continue;
		elseif (!isset($arcadeModSettings[$variable]) && empty($value))
			continue;

		$replaceArray[] = array($variable, $value);

		$arcadeModSettings[$variable] = $value;
	}

	if (empty($replaceArray))
		return;

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_modsettings',
		array('variable' => 'string-255', 'value' => 'string-65534'),
		$replaceArray,
		array('variable')
	);
}

function loadArcadeModSettings()
{
	global $arcadeModSettings, $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT variable, value
		FROM {db_prefix}arcade_modsettings',
		array(
		)
	);
	$arcadeModSettings = array();
	if (!$request)
		display_db_error();
	foreach ($smcFunc['db_fetch_all']($request) as $row)
		$arcadeModSettings[$row['variable']] = $row['value'];
	$smcFunc['db_free_result']($request);
}

function arcadeSaveDBSettings(&$config_vars)
{
	global $sourcedir, $smcFunc;
	static $board_list = null;

	validateToken('admin-dbsc');

	$inlinePermissions = array();
	foreach ($config_vars as $var)
	{
		if (!isset($var[1]) || (!isset($_POST[$var[1]]) && $var[0] != 'check' && $var[0] != 'permissions' && $var[0] != 'boards' && ($var[0] != 'bbc' || !isset($_POST[$var[1] . '_enabledTags']))))
			continue;

		// Checkboxes!
		elseif ($var[0] == 'check')
			$setArray[$var[1]] = !empty($_POST[$var[1]]) ? '1' : '0';
		// Select boxes!
		elseif ($var[0] == 'select' && in_array($_POST[$var[1]], array_keys($var[2])))
			$setArray[$var[1]] = $_POST[$var[1]];
		elseif ($var[0] == 'select' && !empty($var['multiple']) && array_intersect($_POST[$var[1]], array_keys($var[2])) != array())
		{
			// For security purposes we validate this line by line.
			$lOptions = array();
			foreach ($_POST[$var[1]] as $invar)
				if (in_array($invar, array_keys($var[2])))
					$lOptions[] = $invar;

			$setArray[$var[1]] = $smcFunc['json_encode']($lOptions);
		}
		// List of boards!
		elseif ($var[0] == 'boards')
		{
			// We just need a simple list of valid boards, nothing more.
			if ($board_list === null)
			{
				$board_list = array();
				$request = $smcFunc['db_query']('', '
					SELECT id_board
					FROM {db_prefix}boards');

				while ($row = $smcFunc['db_fetch_row']($request))
					$board_list[$row[0]] = true;

				$smcFunc['db_free_result']($request);
			}

			$lOptions = array();

			if (!empty($_POST[$var[1]]))
				foreach ($_POST[$var[1]] as $invar => $dummy)
					if (isset($board_list[$invar]))
						$lOptions[] = $invar;

			$setArray[$var[1]] = !empty($lOptions) ? implode(',', $lOptions) : '';
		}
		// Integers!
		elseif ($var[0] == 'int')
		{
			$setArray[$var[1]] = (int) $_POST[$var[1]];

			// If no min is specified, assume 0. This is done to avoid having to specify 'min => 0' for all settings where 0 is the min...
			$min = isset($var['min']) ? $var['min'] : 0;
			$setArray[$var[1]] = max($min, $setArray[$var[1]]);

			// Do we have a max value for this as well?
			if (isset($var['max']))
				$setArray[$var[1]] = min($var['max'], $setArray[$var[1]]);
		}
		// Floating point!
		elseif ($var[0] == 'float')
		{
			$setArray[$var[1]] = (float) $_POST[$var[1]];

			// If no min is specified, assume 0. This is done to avoid having to specify 'min => 0' for all settings where 0 is the min...
			$min = isset($var['min']) ? $var['min'] : 0;
			$setArray[$var[1]] = max($min, $setArray[$var[1]]);

			// Do we have a max value for this as well?
			if (isset($var['max']))
				$setArray[$var[1]] = min($var['max'], $setArray[$var[1]]);
		}
		// Text!
		elseif (in_array($var[0], array('text', 'large_text', 'color', 'date', 'datetime', 'datetime-local', 'email', 'month', 'time')))
			$setArray[$var[1]] = $_POST[$var[1]];
		// Passwords!
		elseif ($var[0] == 'password')
		{
			if (isset($_POST[$var[1]][1]) && $_POST[$var[1]][0] == $_POST[$var[1]][1])
				$setArray[$var[1]] = $_POST[$var[1]][0];
		}
		// BBC.
		elseif ($var[0] == 'bbc')
		{
			$bbcTags = array();
			foreach (parse_bbc(false) as $tag)
				$bbcTags[] = $tag['tag'];

			if (!isset($_POST[$var[1] . '_enabledTags']))
				$_POST[$var[1] . '_enabledTags'] = array();
			elseif (!is_array($_POST[$var[1] . '_enabledTags']))
				$_POST[$var[1] . '_enabledTags'] = array($_POST[$var[1] . '_enabledTags']);

			$setArray[$var[1]] = implode(',', array_diff($bbcTags, $_POST[$var[1] . '_enabledTags']));
		}
		// Permissions?
		elseif ($var[0] == 'permissions')
			$inlinePermissions[] = $var[1];
	}

	if (!empty($setArray))
		updateArcadeSettings($setArray);

	// If we have inline permissions we need to save them.
	if (!empty($inlinePermissions) && allowedTo('manage_permissions'))
	{
		require_once($sourcedir . '/ManagePermissions.php');
		save_inline_permissions($inlinePermissions);
	}
}

function arcadePrepareDBSettingContext(&$config_vars)
{
	global $txt, $helptxt, $context, $arcadeModSettings, $sourcedir, $smcFunc;

	loadLanguage('Help');

	if (isset($_SESSION['adm-save']))
	{
		if ($_SESSION['adm-save'] === true)
			$context['saved_successful'] = true;
		else
			$context['saved_failed'] = $_SESSION['adm-save'];

		unset($_SESSION['adm-save']);
	}

	$context['config_vars'] = array();
	$inlinePermissions = array();
	$bbcChoice = array();
	$board_list = false;
	foreach ($config_vars as $config_var)
	{
		// HR?
		if (!is_array($config_var))
			$context['config_vars'][] = $config_var;
		else
		{
			// If it has no name it doesn't have any purpose!
			if (empty($config_var[1]))
				continue;

			// Special case for inline permissions
			if ($config_var[0] == 'permissions' && allowedTo('manage_permissions'))
				$inlinePermissions[] = $config_var[1];

			elseif ($config_var[0] == 'permissions')
				continue;

			if ($config_var[0] == 'boards')
				$board_list = true;

			// Are we showing the BBC selection box?
			if ($config_var[0] == 'bbc')
				$bbcChoice[] = $config_var[1];

			// We need to do some parsing of the value before we pass it in.
			if (isset($arcadeModSettings[$config_var[1]]))
			{
				switch ($config_var[0])
				{
					case 'select':
						$value = $arcadeModSettings[$config_var[1]];
						break;
					case 'json':
						$value = $smcFunc['htmlspecialchars']($smcFunc['json_encode']($arcadeModSettings[$config_var[1]]));
						break;
					case 'boards':
						$value = explode(',', $arcadeModSettings[$config_var[1]]);
						break;
					default:
						$value = $smcFunc['htmlspecialchars']($arcadeModSettings[$config_var[1]]);
				}
			}
			else
			{
				// Darn, it's empty. What type is expected?
				switch ($config_var[0])
				{
					case 'int':
					case 'float':
						$value = 0;
						break;
					case 'select':
						$value = !empty($config_var['multiple']) ? $smcFunc['json_encode'](array()) : '';
						break;
					case 'boards':
						$value = array();
						break;
					default:
						$value = '';
				}
			}

			$context['config_vars'][$config_var[1]] = array(
				'label' => isset($config_var['text_label']) ? $config_var['text_label'] : (isset($txt[$config_var[1]]) ? $txt[$config_var[1]] : (isset($config_var[3]) && !is_array($config_var[3]) ? $config_var[3] : '')),
				'help' => isset($helptxt[$config_var[1]]) ? $config_var[1] : '',
				'type' => $config_var[0],
				'size' => !empty($config_var['size']) ? $config_var['size'] : (!empty($config_var[2]) && !is_array($config_var[2]) ? $config_var[2] : (in_array($config_var[0], array('int', 'float')) ? 6 : 0)),
				'data' => array(),
				'name' => $config_var[1],
				'value' => $value,
				'disabled' => false,
				'invalid' => !empty($config_var['invalid']),
				'javascript' => '',
				'var_message' => !empty($config_var['message']) && isset($txt[$config_var['message']]) ? $txt[$config_var['message']] : '',
				'preinput' => isset($config_var['preinput']) ? $config_var['preinput'] : '',
				'postinput' => isset($config_var['postinput']) ? $config_var['postinput'] : '',
			);

			// Handle min/max/step if necessary
			if ($config_var[0] == 'int' || $config_var[0] == 'float')
			{
				// Default to a min of 0 if one isn't set
				if (isset($config_var['min']))
					$context['config_vars'][$config_var[1]]['min'] = $config_var['min'];

				else
					$context['config_vars'][$config_var[1]]['min'] = 0;

				if (isset($config_var['max']))
					$context['config_vars'][$config_var[1]]['max'] = $config_var['max'];

				if (isset($config_var['step']))
					$context['config_vars'][$config_var[1]]['step'] = $config_var['step'];
			}

			// If this is a select box handle any data.
			if (!empty($config_var[2]) && is_array($config_var[2]))
			{
				// If we allow multiple selections, we need to adjust a few things.
				if ($config_var[0] == 'select' && !empty($config_var['multiple']))
				{
					$context['config_vars'][$config_var[1]]['name'] .= '[]';
					$context['config_vars'][$config_var[1]]['value'] = !empty($context['config_vars'][$config_var[1]]['value']) ? $smcFunc['json_decode']($context['config_vars'][$config_var[1]]['value'], true) : array();
				}

				// If it's associative
				if (isset($config_var[2][0]) && is_array($config_var[2][0]))
					$context['config_vars'][$config_var[1]]['data'] = $config_var[2];

				else
				{
					foreach ($config_var[2] as $key => $item)
						$context['config_vars'][$config_var[1]]['data'][] = array($key, $item);
				}
				if (empty($config_var['size']) && !empty($config_var['multiple']))
					$context['config_vars'][$config_var[1]]['size'] = max(4, count($config_var[2]));
			}

			// Finally allow overrides - and some final cleanups.
			foreach ($config_var as $k => $v)
			{
				if (!is_numeric($k))
				{
					if (substr($k, 0, 2) == 'on')
						$context['config_vars'][$config_var[1]]['javascript'] .= ' ' . $k . '="' . $v . '"';
					else
						$context['config_vars'][$config_var[1]][$k] = $v;
				}

				// See if there are any other labels that might fit?
				if (isset($txt['setting_' . $config_var[1]]))
					$context['config_vars'][$config_var[1]]['label'] = $txt['setting_' . $config_var[1]];

				elseif (isset($txt['groups_' . $config_var[1]]))
					$context['config_vars'][$config_var[1]]['label'] = $txt['groups_' . $config_var[1]];
			}

			// Set the subtext in case it's part of the label.
			// @todo Temporary. Preventing divs inside label tags.
			$divPos = strpos($context['config_vars'][$config_var[1]]['label'], '<div');
			if ($divPos !== false)
			{
				$context['config_vars'][$config_var[1]]['subtext'] = preg_replace('~</?div[^>]*>~', '', substr($context['config_vars'][$config_var[1]]['label'], $divPos));
				$context['config_vars'][$config_var[1]]['label'] = substr($context['config_vars'][$config_var[1]]['label'], 0, $divPos);
			}
		}
	}

	// If we have inline permissions we need to prep them.
	if (!empty($inlinePermissions) && allowedTo('manage_permissions'))
	{
		require_once($sourcedir . '/ManagePermissions.php');
		init_inline_permissions($inlinePermissions);
	}

	if ($board_list)
	{
		require_once($sourcedir . '/Subs-MessageIndex.php');
		$context['board_list'] = getBoardList();
	}

	// What about any BBC selection boxes?
	if (!empty($bbcChoice))
	{
		// What are the options, eh?
		$temp = parse_bbc(false);
		$bbcTags = array();
		foreach ($temp as $tag)
			if (!isset($tag['require_parents']))
				$bbcTags[] = $tag['tag'];

		$bbcTags = array_unique($bbcTags);

		// The number of columns we want to show the BBC tags in.
		$numColumns = isset($context['num_bbc_columns']) ? $context['num_bbc_columns'] : 3;

		// Now put whatever BBC options we may have into context too!
		$context['bbc_sections'] = array();
		foreach ($bbcChoice as $bbcSection)
		{
			$context['bbc_sections'][$bbcSection] = array(
				'title' => isset($txt['bbc_title_' . $bbcSection]) ? $txt['bbc_title_' . $bbcSection] : $txt['enabled_bbc_select'],
				'disabled' => empty($arcadeModSettings['bbc_disabled_' . $bbcSection]) ? array() : $arcadeModSettings['bbc_disabled_' . $bbcSection],
				'all_selected' => empty($arcadeModSettings['bbc_disabled_' . $bbcSection]),
				'columns' => array(),
			);

			if ($bbcSection == 'legacyBBC')
				$sectionTags = array_intersect($context['legacy_bbc'], $bbcTags);
			else
				$sectionTags = array_diff($bbcTags, $context['legacy_bbc']);

			$totalTags = count($sectionTags);
			$tagsPerColumn = ceil($totalTags / $numColumns);

			$col = 0;
			$i = 0;
			foreach ($sectionTags as $tag)
			{
				if ($i % $tagsPerColumn == 0 && $i != 0)
					$col++;

				$context['bbc_sections'][$bbcSection]['columns'][$col][] = array(
					'tag' => $tag,
					'show_help' => isset($helptxt['tag_' . $tag]),
				);

				$i++;
			}
		}
	}

	//call_integration_hook('integrate_prepare_db_settings', array(&$config_vars));
	createToken('admin-dbsc');
}

function arcadeCompressArchive($gamefiles, $gamedatafiles, $gameDataPath, $internalName, $archivePath, $ignore, $ignoreArray, $tmpfile, $phpFile, $val, $rom, $type = 'zip')
{
	switch($type) {
		case 'zip':
			$files = array_merge_recursive($gamefiles, $gamedatafiles);
			arcade_create_zip($files, $archivePath, $ignore, $ignoreArray, $tmpfile, $phpFile, $val, $rom);
			break;
		case 'rar':
			arcade_create_rar($gamefiles, $gameDataPath, $internalName, $archivePath, $ignore, $ignoreArray, $tmpfile, $phpFile, $val, $rom);
			break;
		default:
			arcade_create_phar($gamefiles, $gameDataPath, $internalName, $archivePath, $ignore, $ignoreArray, $tmpfile, $phpFile, $val, $rom);
	}
}

function Arcade_cron_tasks()
{
	global $boarddir, $boardurl, $arcadeModSettings, $sourcedir, $user_info, $txt;

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAdmin.php');
	loadLanguage('ArcadeAdmin');
	$lastRecurrentCron = !empty($arcadeModSettings['arcadeRecurrentCronTasks']) ? intval($arcadeModSettings['arcadeRecurrentCronTasks']) : time();
	$lastScheduledCron = !empty($arcadeModSettings['arcadeDailyCronTasks']) ? intval($arcadeModSettings['arcadeDailyCronTasks']) : time();
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] . '/' : $boarddir . '/ArcadeRetroArch/roms/';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
	$schedule = !empty($arcadeModSettings['arcadeCronSchedule']) ? intval($arcadeModSettings['arcadeCronSchedule']) : 0;
	$recurrentSchedule = !empty($arcadeModSettings['arcadeCronBiosSchedule']) ? intval($arcadeModSettings['arcadeCronBiosSchedule']) : 0;

	switch($recurrentSchedule) {
		case 1:
			$recurrent = 300;
			break;
		case 2:
			$recurrent = 900;
			break;
		case 3:
			$recurrent = 1800;
			break;
		case 4:
			$recurrent = 3600;
			break;
		case 5:
			$recurrent = 21600;
			break;
		case 6:
			$recurrent = 43200;
			break;
		default:
			$recurrent = 0;
	}

	switch($schedule) {
		case 1:
			$flag = 86400;
			break;
		case 2:
			$flag = 604800;
			break;
		case 3:
			$flag = 2592000;
			break;
		default:
			$flag = 0;
	}


	if (!empty($flag) && time() - $lastScheduledCron > $flag) {
		clearstatcache();
		require_once($boarddir . '/ArcadeSources/Subs-ArcadeMaintenance.php');

		// temp json files
		if (is_dir($boarddir . '/arcade/arcade_temp')) {
			$files = glob($boarddir . '/arcade/arcade_temp/*.{jpg,png,gif,json}', GLOB_BRACE);
			foreach ($files as $file) {
				if (basename($file) != 'index.php') {
					@unlink($file);
				}
			}
		}

		// emulator archives
		if (!empty($arcadeModSettings['cron_emulator_archives'])) {
			arcadeRemoveEmulatorArchives();
		}

		// upload directories
		if (!empty($arcadeModSettings['cron_game_romupload'])) {
			arcadeRemoveRomArchives(1);
		}
		if (!empty($arcadeModSettings['cron_game_upload'])) {
			arcadeRemoveArchives(1);
		}

		// download directory
		if (!empty($arcadeModSettings['cron_game_download'])) {
			ArcadeMaintenanceDownload();
		}

		// bios files
		if (!empty($arcadeModSettings['cron_game_tempbios'])) {
			$files = glob($romGamesDirectory . '/temp_bios/user-*');
			foreach ($files as $path) {
				if (basename($path) == 'index.php') {
					continue;
				}
				$mtime = @filemtime($path);
				if (is_dir($path)) {
					arcadeAdminRmDir($path);
				}
				elseif (time() - $lastDailyCron > 86400) {
					unlink($path);
					updateArcadeSettings(array('arcadeRecurrentCronTasks' => time()));
				}
			}

			updateArcadeSettings(array('arcadeDailyCronTasks' => time()));
			clearstatcache();
			return true;
		}
	}
	elseif (!empty($recurrent) && time() - $lastRecurrentCron > $recurrent) {
		clearstatcache();
		// check bios path every 10 minutes
		$files = glob($romGamesDirectory . '/temp_bios/user-*');
		foreach ($files as $path) {
			if (basename($path) == 'index.php') {
				continue;
			}
			$mtime = @filemtime($path);
			if (is_dir($path)) {
				arcadeAdminRmDir($path);
			}
			elseif (!empty($mtime) && is_int($mtime) && time() - $mtime > 540) {
				unlink($path);
				updateArcadeSettings(array('arcadeRecurrentCronTasks' => time()));
			}
		}
		clearstatcache();
		return true;
	}

	return false;
}

?>
