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

function arcade_get_url($params = array())
{
	global $scripturl, $arcadeModSettings;

	// Running in "standalone" mode WITH rewrite
	if (!empty($arcadeModSettings['arcadeStandalone']) && $arcadeModSettings['arcadeStandalone'] == 2)
	{
		// Main Page? Too easy
		if (empty($params))
			return $arcadeModSettings['arcadeStandaloneUrl'] . '/';

		$query = '';

		foreach ($params as $p => $value)
		{
			if ($value === null)
				continue;

			if (!empty($query))
				$query .= ';';
			else
				$query .= '?';

			if (is_int($p))
				$query .= $value;
			else
				$query .= $p . '=' . $value;
		}

		return $arcadeModSettings['arcadeStandaloneUrl'] . '/' . $query;
	}
	// Running in "standalone" mode without rewrite or standard mode
	else
	{
		$return = '';

		if (empty($params) && empty($arcadeModSettings['arcadeStandaloneUrl']))
			$params['action'] = 'arcade';

		foreach ($params as $p => $value)
		{
			if ($value === null)
				continue;

			if (!empty($return))
				$return .= ';';
			else
				$return .= '?';

			if (is_int($p))
				$return .= $value;
			else
				$return .= $p . '=' . $value;
		}

		if (!empty($arcadeModSettings['arcadeStandaloneUrl']))
			return $arcadeModSettings['arcadeStandaloneUrl'] . $return;
		else
			return $scripturl . $return;
	}
}

function arcadePermissionQuery()
{
	global $scripturl, $arcadeModSettings, $context, $user_info;

	// No need to check for admins
	if (allowedTo('arcade_admin'))
	{
		$see_game = '1=1';
		$see_category = '1=1';
	}
	// Build permission query
	else
	{
		if (!isset($arcadeModSettings['arcadePermissionMode']))
			$arcadeModSettings['arcadePermissionMode'] = 1;

		if ($arcadeModSettings['arcadePermissionMode'] >= 2)
		{
			// Can see game?
			if ($user_info['is_guest'])
				$see_game = '(game.id_cat = 0 AND ' . (allowedTo('arcade_view') ? 1 : 0) . ' = 1) OR (game.local_permissions = 0 OR FIND_IN_SET(-1, game.member_groups))';
			// Registered user.... just the groups in $user_info['groups'].
			else
				$see_game = '(game.local_permissions = 0 OR (FIND_IN_SET(' . implode(', game.member_groups) OR FIND_IN_SET(', $user_info['groups']) . ', game.member_groups)))';
		}

		if ($arcadeModSettings['arcadePermissionMode'] == 1 || $arcadeModSettings['arcadePermissionMode'] >= 3)
		{
			// Can see category?
			if ($user_info['is_guest'])
				$see_category = '(game.id_cat = 0 AND ' . (allowedTo('arcade_view') ? 1 : 0) . ' = 1) OR (FIND_IN_SET(-1, category.member_groups))';
			// Registered user.... just the groups in $user_info['groups'].
			else
				$see_category = '(FIND_IN_SET(' . implode(', category.member_groups) OR FIND_IN_SET(', $user_info['groups']) . ', category.member_groups) OR ISNULL(category.member_groups))';
		}
	}

	$arena_category = '(FIND_IN_SET(-2, category.member_groups) OR ISNULL(category.member_groups))';
	$arena_game = '(game.local_permissions = 0 OR FIND_IN_SET(-2, game.member_groups))';

	// Build final query
	// No game/category permissions used
	if (empty($arcadeModSettings['arcadePermissionMode']))
	{
		$user_info['query_see_game'] = 'enabled = 1';
		$user_info['query_arena_game'] = 'enabled = 1';
	}
	// Only category used
	elseif ($arcadeModSettings['arcadePermissionMode'] == 1)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND $see_category)";
		$user_info['query_arena_game'] = "(enabled = 1 AND $arena_category)";
	}
	// Only category used
	elseif ($arcadeModSettings['arcadePermissionMode'] == 2)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND $see_game)";
		$user_info['query_arena_game'] = "(enabled = 1 AND $arena_game)";
	}
	// Required to have permssion to game and category
	elseif ($arcadeModSettings['arcadePermissionMode'] == 3)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND ($see_category AND $see_game))";
		$user_info['query_arena_game'] = "(enabled = 1 AND ($arena_category AND $arena_game))";
	}
	// Required to have permission to game OR category
	elseif ($arcadeModSettings['arcadePermissionMode'] == 4)
	{
		$user_info['query_see_game'] = "(enabled = 1 AND ($see_category OR $see_game))";
		$user_info['query_arena_game'] = "(enabled = 1 AND ($arena_category OR $arena_game))";
	}

	$user_info['query_see_match'] = "(private_game = 0 OR me.id_member = $user_info[id])";
	$user_info['query_see_game'] = allowedTo('arcade_admin') ? str_replace('enabled', '1', $user_info['query_see_game']) : $user_info['query_see_game'];
}

function PostPermissionCheck()
{
	global $txt, $arcadeModSettings, $context, $user_info, $user_profile, $smcFunc;

	// Is Post permissions enabled or is user all-migty admin?
	if ((allowedTo('arcade_admin') && empty($_REQUEST['pcheck'])) || empty($arcadeModSettings['arcadePostPermission']) || !$context['arcade']['can_play'])
		return;
	// Guests cannot ever pass
	elseif ($user_info['is_guest'])
	{
		$context['arcade']['can_play'] = false;
		$context['arcade']['notice'] = $txt['arcade_notice_post_requirement'];

		return;
	}

	// We don't want to load this data on every page load
	$cacheTime = (int)$arcadeModSettings['arcade_cache_time'] < 360 ? 360 : (int)$arcadeModSettings['arcade_cache_time'];
	if (isset($_SESSION['arcade_posts']) && time() - $_SESSION['arcade_posts']['time'] < $cacheTime && empty($_REQUEST['pcheck']))
		$context['arcade']['posts'] = &$_SESSION['arcade_posts'];
	else
	{
		loadMemberData($user_info['id'], false, 'minimal');

		$days = ceil(time() - $user_profile[$user_info['id']]['date_registered'] / 86400);

		// this should be always at least one day
		if ($days < 1)
			$days = 1;

		$context['arcade']['posts'] = array(
			'cumulative' => $user_profile[$user_info['id']]['posts'],
			'average' => $user_profile[$user_info['id']]['posts'] / $days,
			'last_day' => 0,
			'time' => time(),
		);

		if (!empty($arcadeModSettings['arcadePostsPlayPerDay']))
		{
			$result = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}messages AS m
					LEFT JOIN {db_prefix}boards AS b ON (m.id_board = b.id_board)
				WHERE b.count_posts != 1
					AND m.id_member = {int:member}
					AND m.poster_time >= {int:from}',
				array(
					'member' => $user_info['id'],
					'from' => time() - 86400
				)
			);

			list ($context['arcade']['posts']['last_day']) = $smcFunc['db_fetch_row']($result);
			$smcFunc['db_free_result']($result);
		}
		else
		{
			$context['arcade']['posts']['last_day'] = 0;
		}

		$_SESSION['arcade_posts'] = $context['arcade']['posts'];
	}

	$cumulativePosts = true;
	$averagePosts = true;
	$postsLastDay = true;

	// Enough post to play?
	if (!empty($arcadeModSettings['arcadePostsPlay']))
		$cumulativePosts = $context['arcade']['posts']['cumulative'] >= $arcadeModSettings['arcadePostsPlay'];

	// Enough average posts to play?
	if (!empty($arcadeModSettings['arcadePostsPlayAverage']))
		$averagePosts = $context['arcade']['posts']['average'] >= $arcadeModSettings['arcadePostsPlayAverage'];

	// Enough post today to play?
	if (!empty($arcadeModSettings['arcadePostsPlayPerDay']))
		$postsLastDay = $context['arcade']['posts']['last_day'] >= $arcadeModSettings['arcadePostsLastDay'];

	// Result is
	$context['arcade']['can_play'] = $cumulativePosts && $averagePosts && $postsLastDay;

	// Should we display notice?
	if (!$cumulativePosts || !$averagePosts || !$postsLastDay)
		$context['arcade']['notice'] = $txt['arcade_notice_post_requirement'];
}

function loadArcadeSettings($memID = 0)
{
	global $smcFunc, $user_info, $arcadeModSettings, $boarddir, $sourcedir;

	if ($memID == 0 && $user_info['is_guest'])
		return array();

	list($arcadeSettings, $memberId) = array(array(), ($memID == 0 ? $user_info['id'] : $memID));
	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-Members.php');
	require_once($sourcedir . '/Subs-Notify.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');

	$request = $smcFunc['db_query']('', '
		SELECT id_member, arena_invite, arena_match_end, arena_new_round, champion_email, champion_pm, notify_game_reports, new_game, detect_mobile, games_per_page, arcade_emulatorjs_rom,
		archive_type, arcade_gametype, arcade_gametype_rom, new_champion_any, new_champion_own, scores_per_page, skin, list, skin_rom, list_rom, skin_mobile, list_mobile, download_count
		FROM {db_prefix}arcade_members
		WHERE id_member = {int:member}
		LIMIT 1',
		array(
			'member' => $memberId,
		)
	);
	// pm_email_notify
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$extraRomData = !empty($row['arcade_emulatorjs_rom']) && arcadeIsSerialized($row['arcade_emulatorjs_rom']) ? arcade_safe_unserialize($row['arcade_emulatorjs_rom']) : array('webgl2Enabled' => 0);
		$arcadeSettings = array(
			'id_member' => $row['id_member'],
			'arena_invite' => $row['arena_invite'],
			'arena_match_end' => $row['arena_match_end'],
			'arena_new_round' => $row['arena_new_round'],
			'champion_email' => $row['champion_email'],
			'champion_pm' => $row['champion_pm'],
			'notify_game_reports' => (!empty($row['notify_game_reports']) && memberAllowedTo('arcade_admin', $memberId) ? $row['notify_game_reports'] : 0),
			'new_game' => $row['new_game'],
			'games_per_page' => $row['games_per_page'],
			'archive_type' => $row['archive_type'],
			'arcade_gametype' => $row['arcade_gametype'],
			'arcade_gametype_rom' => $row['arcade_gametype_rom'],
			'new_champion_any' => $row['new_champion_any'],
			'new_champion_own' => $row['new_champion_own'],
			'scores_per_page' => $row['scores_per_page'],
			'skin' => !empty($row['skin']) && memberAllowedTo('arcade_skin', $memberId) ? $row['skin'] : 0,
			'list' => !empty($row['list']) && memberAllowedTo('arcade_list', $memberId) ? $row['list'] : 0,
			'skin_rom' => !empty($row['skin_rom']) && memberAllowedTo('arcade_skin', $memberId) ? $row['skin_rom'] : 0,
			'list_rom' => !empty($row['list_rom']) && memberAllowedTo('arcade_list', $memberId) ? $row['list_rom'] : 0,
			'skin_mobile' => (!empty($row['skin_mobile']) && memberAllowedTo('arcade_skin', $memberId) ? (int)$row['skin_mobile'] : 0),
			'list_mobile' => (!empty($row['list_mobile']) && memberAllowedTo('arcade_list', $memberId) ? (int)$row['list_mobile'] : 0),
			'detect_mobile' => $row['detect_mobile'],
			'download_count' => !empty($row['download_count']) ? $row['download_count'] : 0,
			'arcade_emulatorjs_rom' => $extraRomData,
		);
	}
	$smcFunc['db_free_result']($request);

	// Default
	if (empty($arcadeSettings))
	{
		$arcadeSettings = array(
			'id_member' => $memberId,
			'arena_invite' => 0,
			'arena_match_end' => 0,
			'arena_new_round' => 0,
			'champion_email' => 0,
			'champion_pm' => 0,
			'new_game' => 0,
			'games_per_page' => (!empty($modSetting['gamesPerPage']) ? $modSetting['gamesPerPage'] : 28),
			'archive_type' => 0,
			'arcade_gametype' => 0,
			'arcade_gametype_rom' => 0,
			'new_champion_any' => 0,
			'new_champion_own' => 0,
			'scores_per_page' => (!empty($modSetting['scoresPerPage']) ? $modSetting['scoresPerPage'] : 28),
			'skin' => 0,
			'list' => 0,
			'skin_rom' => 0,
			'list_rom' => 0,
			'skin_mobile' => 0,
			'list_mobile' => 0,
			'detect_mobile' => 0,
			'pm_email_notify' => 0,
			'arcade_emulatorjs_rom' => '',
		);
	}
	else
	{
		$prefs = getNotifyPrefs($memberId, '', $memberId != 0);
		$arcadeSettings+= array('pm_email_notify' => !empty($prefs[$memberId]['pm_new']) && $prefs[$memberId]['pm_new'] == 2 ? 1 : 0);
	}

	foreach ($arcadeSettings as $key => $setting) {
		$user_info['arcade_settings'][$key] = $setting;
	}

	if (array_diff(array('archive_type', 'arcade_gametype'), array_keys($arcadeSettings)))
	{
		$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
		$arcadeModSettings['arcadeList'] = !empty($arcadeModSettings['arcadeList']) ? (int)$arcadeModSettings['arcadeList'] : 0;
		$arcadeModSettings['arcadeSkinMobile'] = !empty($arcadeModSettings['arcadeSkinMobile']) ? (int)$arcadeModSettings['arcadeSkinMobile'] : 0;
		$arcadeModSettings['arcadeListMobile'] = !empty($arcadeModSettings['arcadeListMobile']) ? (int)$arcadeModSettings['arcadeListMobile'] : 0;
		$arcadeModSettings['arcade_gz'] = !empty($arcadeModSettings['arcade_gz']) ? $arcadeModSettings['arcade_gz'] : (class_exists('ZipArchive') ? 0 : 1);
		$changeSettings =  array(
			'archive_type' => !empty($arcadeSettings['archive_type']) ? $arcadeSettings['archive_type'] : $arcadeModSettings['arcade_gz'],
			'arcade_gametype' => !empty($arcadeSettings['arcade_gametype']) ? $arcadeSettings['arcade_gametype'] : -1,
			'arcade_gametype_rom' => !empty($arcadeSettings['arcade_gametype_rom']) ? $arcadeSettings['arcade_gametype_rom'] : -1,
		);

		$newSettings = array_replace($arcadeSettings, $changeSettings);

		foreach ($newSettings as $key => $setting) {
			$user_info['arcade_settings'][$key] = $setting;
		}
		return $newSettings;
	}

	list($customArcadeSkins, $customMobileSkins, $customArcadeLists, $customMobileLists, $default, $custom) = array(array(), array(), array(), array(), array(), array());
	list($default['skin'], $default['skin_rom'], $default['skin_mobile'], $default['list'], $default['list_rom'], $default['list_mobile'], $ds, $dms, $cl, $cml) = array($arcadeModSettings['arcadeSkin']+1, $arcadeModSettings['arcadeSkin']+1, $arcadeModSettings['arcadeSkinMobile']+1, $arcadeModSettings['arcadeList']+1, $arcadeModSettings['arcadeList']+1, $arcadeModSettings['arcadeListMobile']+1, 4, 2, 4, 2);

	if ($default['skin'] > 3 || (int)$arcadeSettings['skin'] > 3 || (int)$arcadeSettings['skin_rom'] > 3) {
		$customArcadeSkins = Arcade_integrate_skins('desktop', true);
	}
	if ($default['skin_mobile'] > 1 || (int)$arcadeSettings['skin_mobile'] > 2) {
		$customMobileSkins = Arcade_integrate_skins('mobile', true);
	}
	if ($default['list'] > 3 || (int)$arcadeSettings['list'] > 3 || (int)$arcadeSettings['list_rom'] > 3) {
		$customArcadeLists = Arcade_integrate_lists('desktop', true);
	}
	if ($default['list_mobile'] > 1 || (int)$arcadeSettings['list_mobile'] > 2) {
		$customMobileLists = Arcade_integrate_lists('mobile', true);
	}
	foreach($customArcadeSkins as $skin) {
		if ($skin['id_skin'] < 4) {
			continue;
		}
		if ($ds == $arcadeModSettings['arcadeSkin']) {
			$default['skin'] = $skin['id_skin'];
			$default['skin_rom'] = $skin['id_skin'];
			$custom['skin_rom'] = $skin;
			$custom['skin'] = $skin;
			break;
		}

		$ds++;
	}
	foreach($customMobileSkins as $skin) {
		if ($skin['id_skin'] < 2) {
			continue;
		}
		if ($dms == $arcadeModSettings['arcadeSkinMobile']) {
			$default['skin_mobile'] = $skin['id_skin'];
			$custom['skin_mobile'] = $skin;
			break;
		}

		$dms++;
	}
	foreach($customArcadeLists as $list)
	{
		if ($list['id_list'] < 4) {
			continue;
		}
		if ($arcadeModSettings['arcadeList'] == $cl) {
			$default['list'] = $list['id_list'];
			$default['list_rom'] = $list['id_list'];
			$custom['list'] = $list;
			$custom['list_rom'] = $list;
			break;
		}

		$cl++;
	}

	foreach($customMobileLists as $list)
	{
		if ($list['id_list'] < 2) {
			continue;
		}
		if ($arcadeModSettings['arcadeList'] == $cl) {
			$default['list_mobile'] = $list['id_list'];
			$custom['list_mobile'] = $list;
			break;
		}

		$cml++;
	}

	foreach(array('skin', 'skin_rom', 'list', 'list_rom', 'skin_mobile', 'list_mobile') as $set) {
		if (empty($arcadeSettings[$set])) {
			$arcadeSettings[$set] = $default[$set];
		}
		if (!empty($custom[$set])) {
			$arcadeSettings['custom_' . $set] = $custom[$set];
		}
	}

	return $arcadeSettings;
}

function getSubmitSystem()
{
	global $context, $arcadeModSettings, $boarddir;

	$ibp = isset($_REQUEST['autocom']) && $_REQUEST['autocom'] == 'arcade';
	$html5 = isset($_POST['savetype']) && $_POST['savetype'] == 'html52' ? 'html52' : (isset($_POST['savetype']) && $_POST['savetype'] == 'html5' ? 'html5' : '');
	$html5 = isset($_POST['savetype']) && $_POST['savetype'] == 'html53' ? 'html53' : $html5;
	$romGame = isset($_POST['savetype']) && $_POST['savetype'] == 'romgame' ? 'rom' : '';

	if (!empty($context['playing_custom']))
		return 'custom_game';
	elseif (isset($_POST['mochi']))
		return 'mochi';
	elseif (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade' && !empty($html5))
		return $html5;
	elseif (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade' && !empty($romGame))
		return $romGame;
	elseif (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade' && isset($_POST['gname']) && strlen($_POST['gname']) > 1 && is_dir($boarddir . '/arcade/gamedata/' . ArcadeSpecialChars($_POST['gname'])))
		return 'ibp2';
	elseif (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade')
		return 'ibp';
	elseif ($ibp && !isset($_REQUEST['arcadegid']))
		return 'ibp3';
	elseif ($ibp && isset($_REQUEST['arcadegid']))
		return 'ibp32';
	/*elseif ($ibp && isset($_REQUEST['p']) && $_REQUEST['p'] == 'sngtour')
		return 'ibp_sng';
	elseif (false)
		return 'pnflash';*/
	elseif (isset($_POST['html52']) && isset($_POST['game_name']))
		return 'html52';
	elseif (isset($_POST['html53']) && isset($_POST['game_name']))
		return 'html53';
	elseif (isset($_POST['html5']) && isset($_POST['game_name']))
		return 'html5';
	elseif (isset($_POST['phpbb']) && isset($_POST['game_name']))
		return 'phpbb';
	elseif ((isset($_POST['v3arcade']) || $_REQUEST['sa'] == 'vbBurn') && (isset($_POST['game_name']) || isset($_POST['id'])))
		return 'v3arcade';
	elseif (isset($_REQUEST['sa']) && substr($_REQUEST['sa'], 0, 3) == 'v2S')
		return 'v2game';
	elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'submit')
		return 'v1game';
	elseif (isset($_POST['romgame']) && isset($_POST['game_name']))
		return 'rom';
	else
		return false;
}

function submitSystemInfo($system = '')
{
	global $arcadeFunc, $context, $boarddir;

	if (empty($system))
		$system = getSubmitSystem();

	if ($system == false)
		$system = 'v1game';

	static $systems = array(
		'v2game' => array(
			'system' => 'v2game',
			'name' => 'SMF Arcade v2 (Actionscript 2)',
			'file' => 'Submit-v2game.php',
			'get_game' => 'ArcadeV2GetGame',
			'info' => 'ArcadeV2Submit',
			'play' => 'ArcadeV2Play',
			'xml_play' => 'ArcadeV2XMLPlay',
			'html' => 'ArcadeV2Html',
		),
		'v1game' => array(
			'system' => 'v1game',
			'name' => 'SMF Arcade v1',
			'file' => 'Submit-v1game.php',
			'get_game' => 'ArcadeV1GetGame',
			'info' => 'ArcadeV1Submit',
			'play' => 'ArcadeV1Play',
			'xml_play' => 'ArcadeV1XMLPlay',
			'html' => 'ArcadeV1Html',
		),
		'custom_game' => array(
			'system' => 'custom_game',
			'name' => 'Custom Game (PHP)',
			'file' => 'Submit-custom.php',
			'get_game' => false,
			'info' => 'ArcadeCustomSubmit',
			'play' => 'ArcadeCustomPlay',
			'xml_play' => 'ArcadeCustomXMLPlay',
			'html' => 'ArcadeCustomHtml',
		),
		'ibp' => array(
			'system' => 'ibp',
			'name' => 'IBP Arcade v1',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBPGetGame',
			'info' => 'ArcadeIBPSubmit',
			'play' => 'ArcadeIBPPlay',
			'xml_play' => 'ArcadeIBPXMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'ibp2' => array(
			'system' => 'ibp2',
			'name' => 'IBP Arcade v2',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBP2GetGame',
			'info' => 'ArcadeIBP2Submit',
			'play' => 'ArcadeIBP2Play',
			'xml_play' => 'ArcadeIBP2XMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'ibp3' => array(
			'system' => 'ibp3',
			'name' => 'IBP Arcade v3',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBP3GetGame',
			'info' => 'ArcadeIBP3Submit',
			'play' => 'ArcadeIBP3Play',
			'xml_play' => 'ArcadeIBP3XMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'ibp32' => array(
			'system' => 'ibp32',
			'name' => 'IBP Arcade v3.2',
			'file' => 'Submit-ibp.php',
			'get_game' => 'ArcadeIBP32GetGame',
			'info' => 'ArcadeIBP32Submit',
			'play' => 'ArcadeIBP3Play',
			'xml_play' => 'ArcadeIBP3XMLPlay',
			'html' => 'ArcadeIBPHtml',
		),
		'v3arcade' => array(
			'system' => 'v3arcade',
			'name' => 'v3 Arcade (vBulletin)',
			'file' => 'Submit-v3arcade.php',
			'get_game' => 'ArcadevbGetGame',
			'info' => 'ArcadeVbSubmit',
			'play' => 'ArcadeVbPlay',
			'xml_play' => 'ArcadeVbXMLPlay',
			'html' => 'ArcadeVbHtml',
		),
		'phpbb' => array(
			'system' => 'phpbb',
			'name' => 'PhpBB (activity mod)',
			'file' => 'Submit-phpbb.php',
			'get_game' => 'ArcadePHPBBGetGame',
			'info' => 'ArcadePHPBBSubmit',
			'play' => 'ArcadePHPBBPlay',
			'xml_play' => 'ArcadePHPBBXMLPlay',
			'html' => 'ArcadePHPBBHtml',
		),
		'html5' => array(
			'system' => 'html5',
			'name' => 'HTML5 v1 (SMF Arcade)',
			'file' => 'Submit-HTML5.php',
			'get_game' => 'ArcadeHTML5GetGame',
			'info' => 'ArcadeHTML5Submit',
			'play' => 'ArcadeHTML5Play',
			'xml_play' => 'ArcadeHTML5XMLPlay',
			'html' => 'ArcadeHTML5Html',
		),
		'html52' => array(
			'system' => 'html52',
			'name' => 'HTML5 v2 (IBP Arcade)',
			'file' => 'Submit-HTML52.php',
			'get_game' => 'ArcadeHTML52GetGame',
			'info' => 'ArcadeHTML52Submit',
			'play' => 'ArcadeHTML52Play',
			'xml_play' => 'ArcadeHTML52XMLPlay',
			'html' => 'ArcadeHTML52Html',
		),
		'html53' => array(
			'system' => 'html53',
			'name' => 'HTML5 v3 (PHPBB Arcade)',
			'file' => 'Submit-HTML53.php',
			'get_game' => 'ArcadeHTML53GetGame',
			'info' => 'ArcadeHTML53Submit',
			'play' => 'ArcadeHTML53Play',
			'xml_play' => 'ArcadeHTML53XMLPlay',
			'html' => 'ArcadeHTML53Html',
		),
		'mochi' => array(
			'system' => 'mochi',
			'name' => 'MochiAds (requires external module)',
			'file' => 'Submit-mochi.php',
			'get_game' => 'ArcadeMochiGetGame',
			'get_settings' => 'ArcadeMochiGetSettings',
			'info' => 'ArcadeMochiSubmit',
			'play' => 'ArcadeMochiPlay',
			'xml_play' => 'ArcadeMochiXMLPlay',
			'html' => 'ArcadeMochiHtml',
		),
		'rom' => array(
			'system' => 'rom',
			'name' => 'ROM',
			'file' => 'Submit-ROM.php',
			'get_game' => 'ArcadeROMGetGame',
			'info' => 'ArcadeROMSubmit',
			'play' => 'ArcadeROMPlay',
			'xml_play' => 'ArcadeROMXMLPlay',
			'html' => 'ArcadeROMHtml',
		),
	);
	static $submit_system_check_done = false;

	// Remove non-installed systems
	if (!$submit_system_check_done)
	{
		foreach ($systems as $id => $temp)
		{
			if (!file_exists($boarddir . '/ArcadeSources/' . $temp['file']))
				unset($systems[$id]);
		}

		$submit_system_check_done = true;
	}

	if ($system == '*')
		return $systems;
	elseif (isset($systems[$system]))
		return $systems[$system];
	else
		return false;
}

function CheatingCheck()
{
	global $scripturl, $arcadeModSettings;

	$error = '';

	// Default check level is 1
	if (!isset($arcadeModSettings['arcadeCheckLevel']))
		$arcadeModSettings['arcadeCheckLevel'] = 1;

	if (!empty($_SERVER['HTTP_REFERER']))
		$referer = parse_url($_SERVER['HTTP_REFERER']);

	$real = parse_url($scripturl);

	// Level 1 Check
	// Checks also HTTP_REFERER if it not is empty
	if ($arcadeModSettings['arcadeCheckLevel'] == 1)
	{
		if (isset($referer) && ($real['host'] != $referer['host'] || $real['scheme'] != $referer['scheme']))
			$error = 'invalid_referer';
	}
	// Level 2 Check
	// Doesn't allow HTTP_REFERER to be empty
	elseif ($arcadeModSettings['arcadeCheckLevel'] == 2)
	{
		if (!isset($referer) || (isset($referer) && ($real['host'] != $referer['host'] || $real['scheme'] != $referer['scheme'])))
			$error = 'invalid_referer';

	}
	// Level 0 check
	else
		$error = '';

	return $error;
}

// Return game of day
function getGameOfDay($rom = 0)
{
	global $db_prefix, $arcadeModSettings;

	// Return 'Game of day'
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$romfix = !empty($rom) ? '_rom' : '';
	if (!isset($arcadeModSettings['game_of_day' . $romfix]) || !is_numeric($arcadeModSettings['game_of_day' . $romfix]) || !isset($arcadeModSettings['game_time' . $romfix]) || $arcadeModSettings['game_time' . $romfix] != date('ymd'))
		return newGameOfDay($rom);

	if (!($game = cache_get_data('game_of_day' . $romfix, 360)))
	{
		if (!($game = getGameInfo($arcadeModSettings['game_of_day' . $romfix], false, $rom)))
			return newGameOfDay($rom);

		cache_put_data('game_of_day' . $romfix, $game, 360);
	}

	return $game;
}

// Generates new game of day
function newGameOfDay($rom = 0)
{
	global $db_prefix, $arcadeModSettings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$romfix = !empty($rom) ? '_rom' : '';
	$game = getGameInfo('random', false, $rom);
	if (empty($game) || empty($game['id']))
		return false;
	updateArcadeSettings(array(
		'game_time' . $romfix => date('ymd'),
		'game_of_day' . $romfix => intval($game['id'])
	));

	cache_put_data('game_of_day' . $romfix, $game, 360);

	return $game;
}

function getRecommendedGames($id_game, $rom = 0)
{
	global $db_prefix, $user_info, $smcFunc, $arcadeModSettings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($players, $recommended) = array(array(), array());
	if (!is_array($id_game))
		$id_game = array($id_game);

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($playerIds = cache_get_data('arcade_games_players' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT sc.id_member, COUNT(*) as plays
			FROM {db_prefix}arcade_scores AS sc
			WHERE sc.id_game IN({array_int:games})
			GROUP BY sc.id_member
			ORDER BY plays DESC
			LIMIT 50',
			array(
				'games' => $id_game,
			)
		);

		$players = array();
		$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$players[] = $row['id_member'];
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_players' . $rom2, json_encode($players, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}

	$players = !empty($playerIds) ? json_decode($playerIds, true) : $players;
	if (empty($players))
		return false;

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($recommendedGames = cache_get_data('arcade_games_recommended' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT sc.id_game, COUNT(*) AS plays, game.id_cat, game.rom_flag
			FROM {db_prefix}arcade_scores AS sc
				LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = sc.id_game)
				LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
			WHERE {raw:query_see_game}
				AND sc.id_member IN({array_int:players})
				AND game.id_game NOT IN({array_int:games})' . $whererom . '
			GROUP BY sc.id_game
			ORDER BY plays DESC
			LIMIT 3',
			array(
				'players' => $players,
				'games' => $id_game,
				'query_see_game' => $user_info['query_see_game'],
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);

		$recommended = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($id_game == $row['id_game'])
				continue;

			$recommended[] = getGameInfo($row['id_game'], false, $rom);
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_recommended' . $rom2, json_encode($recommended, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$recommended = !empty($recommendedGames) ? json_decode($recommendedGames, true) : $recommended;

	return $recommended;
}

// Return Latest scores
function ArcadeLatestScores($count = 5, $start = 0, $rom = 0)
{
	global $scripturl, $txt, $db_prefix, $smcFunc, $arcadeModSettings;
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? 'WHERE game.rom_flag = {int:rom_flag} ' : '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$data = array();
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeGamesLatest = cache_get_data('arcade_games_latestScores' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.rom_flag, score.score, score.position,
				IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, score.player_name) AS real_name, score.end_time
			FROM {db_prefix}arcade_scores AS score
				INNER JOIN {db_prefix}arcade_games AS game ON (game.id_game = score.id_game)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
			' . $whererom . 'ORDER BY end_time DESC
			LIMIT {int:start}, {int:count}',
			array(
				'start' => $start,
				'count' => $count,
				'empty' => '',
				'rom_flag' => !empty($rom) ? 1 : 0,
			)
		);

		$data = array();

		while ($score = $smcFunc['db_fetch_assoc']($request))
			$data[] = array(
				'game_id' => $score['id_game'],
				'name' => $score['game_name'],
				'score' => comma_format($score['score']),
				'id' => $score['id_member'],
				'member' => $score['real_name'],
				'memberLink' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' . $score['real_name'] . '</a>' : $txt['guest'],
				'time' => timeformat($score['end_time']),
			);
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_latestScores' . $rom2, json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}

	$data = !empty($arcadeGamesLatest) ? json_decode($arcadeGamesLatest, true) : $data;

	return $data;
}

// Output
function ArcadeXMLOutput($data, $name = null, $elements = array())
{
	global $context, $arcadeModSettings;

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	header('Content-Type: text/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
<smf>
	', Array2XML($data, $name, $elements), '
</smf>';

	obExit(false);
}

function Array2XML($data, $name = null, $elements = array(), $indent = 1)
{
	if (!is_array($data))
		return;

	$output = array();

	$ind = str_repeat("\t", $indent);

	foreach ($data as $k => $data)
	{
		if (is_numeric($k) && $name != null)
		{
			if (is_array($data) && isset($data[0]) && $data[0] = 'call')
				$output [] = '<' . $name . '><![CDATA[' . call_user_func_array($data[1], $data[2]) . ']]></' . $name . '>';
			else
			{
				$output[] = '<' . $name . '>';
				$output[] = '	' . Array2XML($data, null, $elements, $indent++);
				$output[] = '</' . $name . '>';
			}
		}
		elseif (is_numeric($k)) {
			arcadeClearSession();
			fatal_lang_error('arcade_internal_error', false);
		}
		else
		{
			if (!empty($elements) && !((in_array($k, $elements) && !is_array($data)) || (isset($elements[$k]) && is_array($data))))
				continue;

			if (is_array($data))
			{
				$output[] = '<' . $k . '>';
				$output[] = '	' . Array2XML($data, null, $elements[$k], $indent++);
				$output[] = '</' . $k . '>';
			}
			else
			{
				if ($data === false)
					$data = 0;
				elseif ($data === true)
					$data = 1;

				if (!is_numeric($data))
					$output [] = '<' . $k . '><![CDATA[' . $data . ']]></' . $k . '>';
				else
					$output [] = '<' . $k . '>' . $data . '</' . $k . '>';
			}
		}
	}

	return implode("\n", $output);
}

function memberAllowedTo($permission, $memID)
{
	if (!is_array($permission))
		$permission = array($permission);

	if (!is_array($memID))
	{
		foreach ($permission as $perm)
		{
			if (in_array($memID, membersAllowedTo($perm)))
				return true;
		}

		return false;
	}

	$allowed = array();

	foreach ($permission as $perm)
	{
		$members = membersAllowedTo($perm);

		foreach ($memID as $i => $id)
		{
			if (in_array($id, $members))
			{
				$allowed[] = $id;

				unset($memID[$i]);

				if (empty($memID))
					return $allowed;
			}
		}
	}

	return $allowed;
}

function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return (float) $usec + (float) $sec;
}

function duration_format($seconds, $max = 2)
{
	global $txt;

	if ($seconds < 1)
		return $txt['arcade_unknown'];

	// max: 0 = weeks, 1 = days, 2 = hours, 3 = minutes, 4 = seconds

	if ($max >= 4)
		$max = 3;
	else
		$max--;

	$units = array(
		array(604800, $txt['arcade_weeks']), // Seconds in week
		array(86400, $txt['arcade_days']), // Seconds in day
		array(3600, $txt['arcade_hours']), // Seconds in hour
		array(60, $txt['arcade_mins']), // Seconds in minute
		array(1, $txt['arcade_secs']), // Seconds in minute
	);

	$out = array();

	foreach ($units as $i => $unit)
	{
		if ($max > $i || $seconds < $unit[0])
			continue;

		list ($secs, $text) = $unit;

		$s = floor($seconds / $secs);
		$seconds -= $s * $secs;

		$out[] = $s . ' ' . $text;
	}

	return implode(' ', $out);
}

function arcade_online()
{
	global $smcFunc;

	// count users online
	list($guests, $users) = array(0, 0);
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeUsersCount = cache_get_data('arcade_user_counts' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}arcade_member_data',
			array()
		);
		$users = $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);

		// count guests online
		$request = $smcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}arcade_guest_data',
			array()
		);
		$guests = $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			$total = array($guests, $users);
			cache_put_data('arcade_user_counts' . $rom2, json_encode($total, JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	if(!empty($arcadeUsersCount)) {
		list($guests, $users) = json_decode($arcadeUsersCount, true);
	}

	return array(time(), $guests, $users);
}

function Arcade_DoToolBarStrip($area, $direction, $content = '', $rom = 0)
{
	global $boardurl, $user_info, $arcadeModSettings, $txt, $context, $scripturl, $db_count, $smcFunc;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$rom3 = !empty($rom) ? 'rom_' : '';
	$area = !empty($area) ? $area : 'index';
	$direction = !empty($direction) ? $direction : 'bottom';
	list($countGames, $content, $contentAdd, $javascript, $checkRar, $gametypeContent, $archiveContent, $context['arcade']['buttons_set'], $context['current_arcade_sa'], $context['arcade']['tour']['show'], $x, $select, $selectMobile1, $selectMobile2) = array(0, '', '', '', false, array(), array(), array(), (!empty($_REQUEST['sa']) ? $_REQUEST['sa'] : 'list'), (!empty($context['arcade']['tour']['show']) ? (int)$context['arcade']['tour']['show'] : 0), 0, '', array(), array());
	$_SESSION['current_cat' . $rom2] = !empty($_SESSION['current_cat' . $rom2]) ? $_SESSION['current_cat' . $rom2] : 'all';
	$_SESSION['arcade_sortby' . $rom2] = !empty($_SESSION['arcade_sortby' . $rom2]) ? $_SESSION['arcade_sortby' . $rom2] : 'a2z';
	$sort = ($_SESSION['current_cat' . $rom2] == 0 || $_SESSION['current_cat' . $rom2] == 'all') && $_SESSION['arcade_sortby' . $rom2] == 'a2z' ? '' : ';sortby=reset';
	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;

	$arcadeAction = empty($rom) ? 'arcade' : 'retro_arch';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && empty($rom) && !allowedTo('arcade_download') ? false : (!empty($arcadeModSettings['arcadeDownloadHideLink']) && !empty($rom) && !allowedTo('arcade_download_rom') ? false : $arcadeModSettings['arcadeEnableDownload']);
	if (!$user_info['is_guest'] && !empty($user_info['arcade_settings']))
	{
		$userArchive = !empty($user_info['arcade_settings']['archive_type']) ? $user_info['arcade_settings']['archive_type'] : -1;
		$userGametype = !empty($user_info['arcade_settings']['arcade_gametype' . $rom2]) ? $user_info['arcade_settings']['arcade_gametype' . $rom2] : -1;
	}
	elseif (!$user_info['is_guest'])
	{
		loadArcadeSettings($user_info['id']);
		$userArchive = !empty($user_info['arcade_settings']['archive_type']) ? $user_info['arcade_settings']['archive_type'] : -1;
		$userGametype = !empty($user_info['arcade_settings']['arcade_gametype' . $rom2]) ? $user_info['arcade_settings']['arcade_gametype' . $rom2] : -1;
	}
	else
		list($userArchive, $userGametype) = array(-1, -1);

	if (!empty($arcadeModSettings['arcade_gz_user']) && allowedTo('arcade_download_type') && !empty($enableGameDownload))
	{
		$typeArray = empty($rom) ? explode('|', $txt['arcade_compression']) : array('zip');
		$arcadeModSettings['arcade_gz'] = !empty($arcadeModSettings['arcade_gz']) ? $arcadeModSettings['arcade_gz'] : (class_exists('ZipArchive') ? 0 : 1);
		$arcadeModSettings['arcade_gz'] = $userArchive != -1 ? $userArchive : $arcadeModSettings['arcade_gz'];
		$selected = 'selected';
		$active = 'class="active"';
		$_SESSION['arcade_download_type'] = isset($_SESSION['arcade_download_type']) ? $_SESSION['arcade_download_type'] : $arcadeModSettings['arcade_gz'];
		$archivetype = isset($_REQUEST['archive']) ? $_REQUEST['archive'] : '';
		if (!empty($archivetype))
			ArcadeSelectType($typeArray, $rom);
		$currentUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$locate = stripos($currentUri, 'index.php?action=' . $arcadeAction);
		$currentLink = $locate !== false ? $boardurl . '/' . substr($currentUri, $locate) : $scripturl . '?action=' . $arcadeAction;
		foreach(array_reverse($typeArray) as $typex)
			$currentLink = str_replace(';archive=' . $typex, '', $currentLink);
		$currentLink = str_replace('index.php?action=' . $arcadeAction, 'index.php?action=' . $arcadeAction . ';archive=', $currentLink);
		$archiveContent = array();

		// check if RAR package is available
		if (empty($arcadeModSettings['arcadeDownloadShellEnable']) || !empty($rom))
			$checkRar = false;
		elseif (is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'))
		{
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			{
				if (!empty($arcadeModSettings['arcadeDownloadWinRarDir']) && is_dir($arcadeModSettings['arcadeDownloadWinRarDir']))
					@chdir($arcadeModSettings['arcadeDownloadWinRarDir']);
				if (is_dir("\Program Files (x86)\WinRAR"))
					@chdir("\Program Files (x86)\WinRAR");
				elseif (is_dir("\Program Files\WinRAR"))
					@chdir("\Program Files\WinRAR");

				$checkRar = (`where WinRAR.exe`);
			}
			else
				$checkRar = (`type -P rar`);
		}
		else
			$checkRar = false;

		$select = '
		<div style="margin: 5px 0px 0px 5px;font-size:1.0em;padding: 1em 1em 0em 0em;display: inline;"><div style="display: inline;"><span style="display: none;">&nbsp;</span></div>
			<select class="arcade_select alert" style="border: 0px;outline: 0px;" onchange="window.location = this.options[this.selectedIndex].value.replace(\'archive=\', \'archive=\' + this.options[this.selectedIndex].innerHTML);">';

		foreach ($typeArray as $type)
		{
			if (!$checkRar && $type == 'rar')
				continue;

			$archiveContent[] = array(
				'link' => $scripturl . '?action=' . $arcadeAction . ';archive=' . $type,
				'text' => trim($type)
			);

			$select .= '
			<option class="alert" value="' . $currentLink . '"' . ($x == $_SESSION['arcade_download_type'] ? ' ' . $selected : '') . '>' . $type . '</option>';
			$selectMobile1[] = array('link' => $currentLink, 'text' => $type, 'type' => $x);
			$x++;
		}
		$select .= '
			</select>
		</div>';

		$archivetype = array(
			'text' => 'arcade_user_archive_type_button',
			'image' => 'arcade_user_archive_type.gif',
			'url' => $scripturl . '?action=' . $arcadeAction . ';archive=tar.gz',
			'lang' => true,
			'is_last' => true,
    	);
	}
	else
		$_SESSION['arcade_download_type'] = '';

	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.member_groups, game.id_cat, game.rom_flag, cat.member_groups AS cat_groups,
		IFNULL(cat.member_groups, {string:empty_string}) AS cat_groups
		FROM {db_prefix}arcade_games as game
		LEFT JOIN {db_prefix}arcade_categories AS cat ON (cat.id_cat = game.id_cat)
		WHERE game.id_game > 0' . $whererom,
		array(
			'empty_string' => '',
			'rom_flag' => !empty($rom) ? 1 : 0,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$groups = !empty($row['member_groups']) ? explode(',', $row['member_groups']) : '';
		$cat_groups = explode(',', $row['cat_groups']);
		list($cat_result, $result) = array(array(1), array());
		$result = array_intersect($groups, $user_info['groups']);
		if (!empty($cat_groups) && !empty($row['id_cat']))
			$cat_result = array_intersect($cat_groups, $user_info['groups']);

		if (!empty($result) && !empty($cat_result))
		{
			$countGames = 1;
			break;
		}
		if ($user_info['is_admin'])
		{
			$countGames = 1;
			break;
		}
	}
	$smcFunc['db_free_result']($request);

	if (allowedTo('arcade_gametype_select') && !empty($countGames))
	{
		$typeCheckArray = empty($rom) ? explode('|', $txt['arcade_select_gametype']) : (array_keys($txt['arcade_select_gametype_rom']));
		$langCheckArray = empty($rom) ? explode('|', $txt['arcade_select_gametype_english']) : (array_values($txt['arcade_select_gametype_rom']));
		list($checkSubSystem, $typeArray, $langArray, $x, $y) = array(array(), array('all'), array($langCheckArray[0]), 0, 0);
		foreach ($typeCheckArray as $checkType)
		{
			if (empty($whererom) && empty($rom)) {
				$result = $smcFunc['db_query']('', '
					SELECT submit_system, rom_system, rom_flag
					FROM {db_prefix}arcade_games
					WHERE submit_system = {string:subsystem} OR rom_system = {string:romsystem}
					AND rom_flag = 0
					LIMIT 1',
					array(
					'subsystem' => $checkType,
					'romsystem' => $checkType
					)
				);
			}
			elseif (empty($rom))
				$result = $smcFunc['db_query']('', '
					SELECT submit_system, rom_flag
					FROM {db_prefix}arcade_games
					WHERE submit_system = {string:subsystem}
					AND rom_flag = 0
					LIMIT 1',
					array(
					'subsystem' => $checkType)
				);
			else
				$result = $smcFunc['db_query']('', '
					SELECT rom_system, rom_flag
					FROM {db_prefix}arcade_games
					WHERE rom_system = {string:romsystem}
					AND rom_flag = 1
					LIMIT 1',
					array(
					'romsystem' => $checkType
					)
				);

			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
				$typeArray[] = $checkType;
				$langArray[] = $langCheckArray[$y];
			}
			$smcFunc['db_free_result']($result);
			$y++;
		}
		$selected = ' selected';
		$active = 'class="active"';
		$gamesavetype = isset($_REQUEST['gametype']) ? $_REQUEST['gametype'] : '';
		$_SESSION['arcade_gametype_select' . $rom] = isset($_SESSION['arcade_gametype_select' . $rom]) ? $_SESSION['arcade_gametype_select' . $rom] : (!empty($gamesavetype) ? $gamesavetype : 'all');
		$_SESSION['arcade_gametype_select' . $rom] = $userGametype != -1 && isset($typeArray[$userGametype]) ? $typeArray[$userGametype] : $_SESSION['arcade_gametype_select' . $rom];
		if (!empty($gamesavetype))
			ArcadeSelectGameType($typeArray, $rom);

		$gametypeContent = array();
		$select2 = '
		<div style="margin: 5px 0px 0px 5px;font-size:1.0em;padding: 1em 1em 0em 0em;display: inline;"><div style="display: inline;"><span style="display: none;">&nbsp;</span></div>
			<select class="arcade_select alert" style="border: 0px;outline: 0px;" onchange="location = this.options[this.selectedIndex].value;">';

		foreach ($typeArray as $type)
		{
			$null = empty($gamesavetype) && empty($_SESSION['arcade_gametype_select' . $rom]) && $type == 'all' ? true : false;
			$gametypeContent[] = array(
				'link' => $scripturl . '?action=' . $arcadeAction . ';gametype=' . $typeArray[$x],
				'text' => trim($langArray[$x])
			);

			$select2 .= '
			<option class="alert" value="' . $scripturl . '?action=' . $arcadeAction . ';gametype=' . $typeArray[$x] . '"' . ($type == $_SESSION['arcade_gametype_select' . $rom] || $null ? ' ' . $selected : '') . '>' . $langArray[$x] . '</option>';
			$selectMobile2[] = array('link' => $scripturl . '?action=' . $arcadeAction . ';gametype=' . $typeArray[$x], 'text' => $langArray[$x], 'type' => $type);
			$x++;
		}

		$select2 .= '
			</select>
		</div>';

		$gametype = array(
			'text' => 'arcade_user_gametype_button',
			'image' => 'arcade_user_gametype.gif',
			'url' => $scripturl . '?action=' . $arcadeAction . ';gametype=all',
			'lang' => true,
			'is_last' => true,
    	);
	}

	if ($context['arcade']['tour']['show'] != 0)
        $context['arcadetour']['buttons_set']['newtour'] =  array(
			'text' => 'arcade_tour_new_tour',
			'url' => $scripturl . '?action=' . $arcadeAction . ';sa=tour;ta=new',
			'lang' => true
		);

    if ($context['arcade']['tour']['show'] != 2)
        $context['arcadetour']['buttons_set']['activetour'] =  array(
			'text' => 'arcade_tour_show_active',
    		'url' => $scripturl . '?action=' . $arcadeAction . ';sa=tour',
    		'lang' => true,
    	);

    if ($context['arcade']['tour']['show'] != 1)
	    $context['arcadetour']['buttons_set']['finishedtour'] =  array(
    		'text' => 'arcade_tour_show_finished',
    		'url' => $scripturl . '?action=' . $arcadeAction . ';sa=tour;show=1',
    		'lang' => true,
    	);

	if (empty($arcadeModSettings['arcadeRomToggle']) && allowedTo('arcade_view_retro_arch') && !empty($rom)) {
		$context['arcade']['buttons_set']['romarcade'] =  array(
			'text' => 'rom_arcade',
			'image' => 'arcade.gif',
			'url' => $scripturl . '?action=retro_arch' . $sort,
			'active' => in_array($context['current_arcade_sa'], array('list', 'highscore', 'online', 'play')) ? true : null,
			'lang' => true,
		);

		$context['arcade']['buttons_set']['arcade'] =  array(
			'text' => 'arcade',
			'image' => 'arcade.gif',
			'url' => $scripturl . '?action=arcade' . $sort,
			'active' => in_array($context['current_arcade_sa'], array('list', 'highscore', 'online', 'play')) ? true : null,
			'lang' => true,
		);
	}
	elseif (empty($arcadeModSettings['arcadeRomToggle']) && allowedTo('arcade_view_retro_arch') && empty($rom)) {
		$context['arcade']['buttons_set']['arcade'] =  array(
			'text' => 'arcade',
			'image' => 'arcade.gif',
			'url' => $scripturl . '?action=arcade' . $sort,
			'active' => in_array($context['current_arcade_sa'], array('list', 'highscore', 'online', 'play')) ? true : null,
			'lang' => true,
		);

		$context['arcade']['buttons_set']['romarcade'] =  array(
			'text' => 'rom_arcade',
			'image' => 'arcade.gif',
			'url' => $scripturl . '?action=retro_arch' . $sort,
			'active' => in_array($context['current_arcade_sa'], array('list', 'highscore', 'online', 'play')) ? true : null,
			'lang' => true,
		);
	}
	else {
		$context['arcade']['buttons_set']['arcade'] =  array(
			'text' => 'arcade',
			'image' => 'arcade.gif',
			'url' => $scripturl . '?action=' . $arcadeAction . $sort,
			'active' => in_array($context['current_arcade_sa'], array('list', 'highscore', 'online', 'play')) ? true : null,
			'lang' => true,
		);
	}

	if (!empty($arcadeModSettings['arcadeArenaEnabled']))
		$context['arcade']['buttons_set']['tour'] =  array(
			'text' => 'arcade_arena',
			'image' => 'arcade_arena.gif',
			'url' => $scripturl . '?action=' . $arcadeAction . ';sa=arena;reload=' . mt_rand(0, 9999) . ';#arenamatch',
			'active' => in_array($context['current_arcade_sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')) ? true : null,
			'lang' => true,
		);

	$context['arcade']['buttons_set']['stats'] =  array(
    	'text' => 'arcade_stats',
		'image' => 'arcade_stats.gif',
    	'url' => $scripturl . '?action=' . $arcadeAction . ';sa=stats',
		'active' => in_array($context['current_arcade_sa'], array('stats')) ? true : null,
    	'lang' => true,
    );

    if (allowedTo('admin_arcade'))
       	$context['arcade']['buttons_set']['arcadeadmin'] =  array(
    		'text' => 'arcade_administrator',
			'image' => 'arcade_administrator.gif',
    		'url' => $scripturl . '?action=admin;area=arcade',
    		'lang' => true,
			'is_last' => true,
    	);

	if (!empty($arcadeModSettings['arcade_gz_user']) && allowedTo('arcade_download_type') && !empty($enableGameDownload))
	{
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] != 'stats')
		{
			$context['arcade']['buttons_set']['archivetype'] =  $archivetype;
			$contentAdd .= '&nbsp;|&nbsp;' . $txt['arcade_user_archive_type'] . $select;
		}
	}

	if (allowedTo('arcade_gametype_select') && !empty($countGames))
	{
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] != 'stats')
		{
			$context['arcade']['buttons_set']['gametype'] =  $gametype;
			$contentAdd .= '&nbsp;|&nbsp;' . $txt['arcade_user_gametype'] . $select2;
		}
	}

	$context['arcade']['queries_temp'] = !empty($db_count) ? $db_count : 0;
	$button_strip = (!empty($area)) && $area == 'arena' ? $context['arcadetour']['buttons_set'] : $context['arcade']['buttons_set'];

	if (!empty($arcadeModSettings['arcadeTabs']) && empty($_SESSION['arcade_isMobilePlay']))
	{
		template_button_strip($button_strip, $direction);
		list($content, $contentAdd) = array('', '');
	}
	elseif (!empty($_SESSION['arcade_isMobilePlay']))
	{
		return array($selectMobile1, $selectMobile2);
	}
	else
	{
		foreach ($button_strip as $key => $tab)
		{
			if (in_array($key, array('archivetype', 'gametype')))
				continue;

			$content .= '
			<a href="' . $tab['url'] . '">' . $txt[$tab['text']] . '</a>';

			if (empty($tab['is_last']))
				$content .= '&nbsp;|&nbsp;';
		}
	}

	// the custom select menu for SMF buttons ~ use class for SMF defaults or id for custom
	list($x, $y) = array(0, 0);
	$javascript .= '
	<script type="text/javascript">
		function propertyFromSmfStylesheet(selector, selector2, attribute) {
			var value;
			[].some.call(document.styleSheets, function (sheet) {
				return [].some.call(sheet.rules, function (rule) {
					if (rule.selectorText && selector2 == "" && rule.selectorText.search(selector) != -1) {
						return [].some.call(rule.style, function (style) {
							if (attribute === style) {
								value = rule.style.getPropertyValue(attribute);
								return true;
							}

							return false;
						});
					}
					else if (rule.selectorText && rule.selectorText.search(selector) != -1 && rule.selectorText.search(selector2) != -1) {
						return [].some.call(rule.style, function (style) {
							if (attribute === style) {
								value = rule.style.getPropertyValue(attribute);
								return true;
							}

							return false;
						});
					}
					return false;
				});
			});
			return value;
		}
		function smfArcadeDropDown() {
			var arcadeHover, arcadeNoHover;
			// var arcadeBgColor = propertyFromSmfStylesheet(".event", "", "color");
			var arcadeBgColor = propertyFromSmfStylesheet("button", "", "background-color");
			arcadeBgColor = arcadeBgColor == "undefined" || arcadeBgColor == null ? "gray" : arcadeBgColor;
			arcadeHover = propertyFromSmfStylesheet("buttonlist", ":hover", "color");
			arcadeNoHover = propertyFromSmfStylesheet("buttonlist", "", "color");
			if (arcadeHover == null || arcadeHover == "undefined")
				arcadeHover = propertyFromSmfStylesheet("button", ":hover", "color");
			if (arcadeNoHover == null || arcadeNoHover == "undefined")
				arcadeNoHover = propertyFromSmfStylesheet("button", "", "color");
			arcadeHover = arcadeHover == "undefined" || arcadeHover == null || arcadeHover == arcadeNoHover ? "#CCC" : arcadeHover;
			arcadeNoHover = arcadeNoHover == "undefined" || arcadeNoHover == null ? "#000" : arcadeNoHover;
			var container = document.createElement("div");
			container.style = "display: none;background-color: initial;position: absolute;border-radius: 0.6em;min-width: 7em;overflow: auto;box-shadow: 0.5em 0.5em 1.0em 0.5em rgba(0,0,0,0.2);z-index: 1;overflow: hidden;";
			container.style.backgroundColor = "inherit";
			container.id = "archivetype";
			container.onmouseleave = function() {document.getElementById("archivetype").style.display = "none";}
			var container2 = document.createElement("div");
			container2.style = "display: none;background-color: initial;position: absolute;border-radius: 0.6em;min-width: 7em;overflow: auto;box-shadow: 0.5em 0.5em 1.0em 0.5em rgba(0,0,0,0.2);z-index: 1;overflow: hidden;";
			container2.style.backgroundColor = "inherit";
			container2.id = "gametype";
			container2.onmouseleave = function() {document.getElementById("gametype").style.display = "none";}';

	foreach ($archiveContent as $archive)
	{
		$x++;
		$javascript .= '
			var arcadeLink' . $x . ' = document.createElement("a");
			arcadeLink' . $x . '.id = \'arcadeLinkId' . $x . '\';
			arcadeLink' . $x . '.style = "padding-left: 0.7em;margin-left: 0em;left: -0.3em;text-decoration: none;display: block;border-radius: 0.3em;position: relative;width: 100%;";
			arcadeLink' . $x . '.style.color = arcadeNoHover;
			arcadeLink' . $x . '.onmouseenter = function() {document.getElementById("arcadeLinkId' . $x . '").style.color = arcadeHover;};
			arcadeLink' . $x . '.onmouseleave = function() {document.getElementById("arcadeLinkId' . $x . '").style.color = arcadeNoHover;};
			arcadeLink' . $x . '.href = \'' . $archive['link'] . '\';
			arcadeLink' . $x . '.text = \'' . $archive['text'] . '\';
			arcadeLink' . $x . '.setAttribute("target", "_self");
			container.append(arcadeLink' . $x . ');';
	}

	foreach ($gametypeContent as $gametypes)
	{
		$y++;
		$javascript .= '
			var arcadeLinkz' . $y . ' = document.createElement("a");
			arcadeLinkz' . $y . '.id = \'arcadeLinkzId' . $y . '\';
			arcadeLinkz' . $y . '.style = "padding-left: 0.7em;margin-left: 0em;left: -0.3em;text-decoration: none;display: block;border-radius: 0.3em;position: relative;width: 100%;";
			arcadeLinkz' . $y . '.style.color = arcadeNoHover;
			arcadeLinkz' . $y . '.onmouseenter = function() {document.getElementById("arcadeLinkzId' . $y . '").style.color = arcadeHover;};
			arcadeLinkz' . $y . '.onmouseleave = function() {document.getElementById("arcadeLinkzId' . $y . '").style.color = arcadeNoHover;};
			arcadeLinkz' . $y . '.href = \'' . $gametypes['link'] . '\';
			arcadeLinkz' . $y . '.text = \'' . $gametypes['text'] . '\';
			arcadeLinkz' . $y . '.setAttribute("target", "_self");
			container2.append(arcadeLinkz' . $y . ');';
	}

	$javascript .= '
			var archive, archivesClass, archiveHref, countHref;
			var archiveHrefs = [];
			archivesClass = document.getElementsByClassName("button_strip_archivetype");
			archive = archivesClass != null ? archivesClass[0] : document.getElementById("archivetype");
			if (archive == null)
			{
				archiveHrefs = document.getElementsByTagName("A");
				for(countHref=0;countHref<archiveHrefs.length;countHref++)
				{
					if (archiveHrefs[countHref].href.includes("action=' . $arcadeAction . ';archive=tar.gz"))
					{
						archive = archiveHrefs[countHref];
						break;
					}
				}
			}
			if (archive != null)
			{
				archive.style.position = "relative";
				archive.href = "javascript:void(0)";
				archive.append(container);
				archive.onclick = function() {
					var showMe1 = document.getElementById("archivetype");
					if (showMe1.style.display == "none")
						showMe1.style.display = "block";
					else
						showMe1.style.display = "none";
				}
				archive.onmouseleave = function() {
					document.getElementById("archivetype").style.display = "none";
				}
			}
			var gametype, gametypeClass, gametypeHref, countHref;
			var gametypeHrefs = [];
			gametypeClass = document.getElementsByClassName("button_strip_gametype");
			gametype = gametypeClass != null ? gametypeClass[0] : document.getElementById("gametype");
			if (gametype == null)
			{
				gametypeHrefs = document.getElementsByTagName("A");
				for (countHref = 0;countHref<gametypeHrefs.length;countHref++)
				{
					if (gametypeHrefs[countHref].href.includes("index.php?action=' . $arcadeAction . ';gametype=all"))
					{
						gametype = gametypeHrefs[countHref];
						break;
					}
				}
			}
			if (gametype != null)
			{
				gametype.style.position = "relative";
				gametype.href = "javascript:void(0)";
				gametype.append(container2);
				gametype.onclick = function() {
					var showMe2 = document.getElementById("gametype");
					if (showMe2.style.display == "none")
						showMe2.style.display = "block";
					else
						showMe2.style.display = "none";
				}
				gametype.onmouseleave = function() {
					document.getElementById("gametype").style.display = "none";
				}
			}
		}
		$(document).ready(function() {
			smfArcadeDropDown();
		});
	</script>';
	return $javascript . $content . $contentAdd;
}

function ArcadeSelectType($typeArray, $rom = 0)
{
	global $smcFunc, $user_info, $arcadeModSettings;
	if (isset($_REQUEST['archive']) && !empty($arcadeModSettings['arcade_gz_user']))
	{
		list($key, $val, $req) = array(0, 99, preg_replace('/[^a-zA-Z0-9.]/', '', $_REQUEST['archive']));
		foreach ($typeArray as $value)
		{
			if ($req == $value)
				$val = $key;
			$key ++;
		}

		if ($val > count($typeArray) -1)
			return false;

		if ($user_info['is_guest'])
		{
			$_SESSION['arcade_download_type'] = $val;
			$arcadeModSettings['arcade_gz'] = $val;
		}
		else
		{
			list($_SESSION['arcade_download_type'], $arcadeModSettings['arcade_gz'], $checkEntry) = array($val, $val, 0);
			$request = $smcFunc['db_query']('', '
				SELECT id_member, archive_type
				FROM {db_prefix}arcade_members
				WHERE id_member = {int:member}
				LIMIT 1',
				array(
					'member' => $user_info['id']
				)
			);

			while($row = $smcFunc['db_fetch_assoc']($request))
				$checkEntry = $row['id_member'];
			$smcFunc['db_free_result']($request);

			if (!empty($checkEntry))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_members
					SET	archive_type = {int:archtype}
					WHERE id_member = {int:member}',
					array(
						'archtype' => $val,
						'member' => $user_info['id'],
					)
				);
			else
			{
				$smcFunc['db_insert']('insert',
					'{db_prefix}arcade_members',
					array(
						'id_member' => 'int',
						'arena_invite' => 'int',
						'arena_match_end' => 'int',
						'arena_new_round' => 'int',
						'champion_email' => 'int',
						'champion_pm' => 'int',
						'games_per_page' => 'int',
						'new_champion_any' => 'int',
						'new_champion_own' => 'int',
						'scores_per_page' => 'int',
						'skin' => 'int',
						'list' => 'int',
						'skin_rom' => 'int',
						'list_rom' => 'int',
						'skin_mobile' => 'int',
						'list_mobile' => 'int',
						'detect_mobile' => 'int',
						'new_game' => 'int',
						'arcade_gametype' => 'int',
						'arcade_gametype_rom' => 'int',
						'archive_type' => 'int',
					),
					array(
						$user_info['id'],
						0,
						0,
						0,
						0,
						0,
						$arcadeModSettings['gamesPerPage'],
						0,
						0,
						$arcadeModSettings['scoresPerPage'],
						$arcadeModSettings['arcadeSkin'],
						$arcadeModSettings['arcadeList'],
						$arcadeModSettings['arcadeSkin'],
						$arcadeModSettings['arcadeList'],
						!empty($arcadeModSettings['arcadeSkinMobile']) ? (int)$arcadeModSettings['arcadeSkinMobile'] : 0,
						!empty($arcadeModSettings['arcadeListMobile']) ? (int)$arcadeModSettings['arcadeListMobile'] : 0,
						0,
						0,
						0,
						0,
						$val,
					),
					array('id_member')
				);
			}
		}

		return true;
	}

	return false;
}

function ArcadeSelectGameType($typeArray, $rom = 0)
{
	global $smcFunc, $user_info, $arcadeModSettings;
	if (isset($_REQUEST['gametype']) && allowedTo('arcade_gametype_select'))
	{
		$arcadeModSettings['arcade_gz'] = !empty($arcadeModSettings['arcade_gz']) ? $arcadeModSettings['arcade_gz'] : (class_exists('ZipArchive') ? 0 : 1);
		$romfix = !empty($rom) ? '_rom' : '';
		list($key, $val, $req) = array(0, 99, preg_replace('/[^a-zA-Z0-9.]/', '', $_REQUEST['gametype']));
		foreach ($typeArray as $value)
		{
			if ($req == $value)
				$val = $key;
			$key ++;
		}

		if ($val > count($typeArray) -1)
			return false;

		if ($user_info['is_guest'])
		{
			$_SESSION['arcade_gametype_select' . $rom] = $typeArray[$val];
		}
		else
		{
			list($_SESSION['arcade_gametype_select' . $rom], $checkEntry) = array($typeArray[$val], 0);
			$request = $smcFunc['db_query']('', '
				SELECT id_member, arcade_gametype' . $romfix . '
				FROM {db_prefix}arcade_members
				WHERE id_member = {int:member}
				LIMIT 1',
				array(
					'member' => $user_info['id']
				)
			);

			while($row = $smcFunc['db_fetch_assoc']($request))
				$checkEntry = $row['id_member'];
			$smcFunc['db_free_result']($request);

			if (!empty($checkEntry))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_members
					SET	arcade_gametype' . $romfix . ' = {int:gametype}
					WHERE id_member = {int:member}',
					array(
						'gametype' => $val,
						'member' => $user_info['id'],
					)
				);
			else
			{
				$smcFunc['db_insert']('insert',
					'{db_prefix}arcade_members',
					array(
						'id_member' => 'int',
						'arena_invite' => 'int',
						'arena_match_end' => 'int',
						'arena_new_round' => 'int',
						'champion_email' => 'int',
						'champion_pm' => 'int',
						'games_per_page' => 'int',
						'new_champion_any' => 'int',
						'new_champion_own' => 'int',
						'scores_per_page' => 'int',
						'skin' => 'int',
						'list' => 'int',
						'skin_rom' => 'int',
						'list_rom' => 'int',
						'skin_mobile' => 'int',
						'list_mobile' => 'int',
						'detect_mobile' => 'int',
						'new_game' => 'int',
						'archive_type' => 'int',
						'arcade_gametype' => 'int',
						'arcade_gametype_rom' => 'int',
						'arcade_emulatorjs_rom' => 'string',
					),
					array(
						$user_info['id'],
						0,
						0,
						0,
						0,
						0,
						$arcadeModSettings['gamesPerPage'],
						0,
						0,
						$arcadeModSettings['scoresPerPage'],
						$arcadeModSettings['arcadeSkin'],
						$arcadeModSettings['arcadeList'],
						$arcadeModSettings['arcadeSkin'],
						$arcadeModSettings['arcadeList'],
						!empty($arcadeModSettings['arcadeSkinMobile']) ? (int)$arcadeModSettings['arcadeSkinMobile'] : 0,
						!empty($arcadeModSettings['arcadeListMobile']) ? (int)$arcadeModSettings['arcadeListMobile'] : 0,
						0,
						0,
						!empty($arcadeModSettings['arcade_gz']) ? (int)$arcadeModSettings['arcade_gz'] : 0,
						!empty($rom) ? 0 : $val,
						empty($rom) ? 0 : $val,
						'',
					),
					array('id_member')
				);
			}
		}

		return true;
	}

	return false;
}

function ArcadeSpecialChars($var, $type = 'name')
{
	$pattern = '/&(#)?[a-zA-Z0-9]{0,};/';
	if (is_array($var))
	{
		$out = array();
	    foreach ($var as $key => $v)
			$out[$key] = ArcadeSpecialChars($v, '');
    }
	else
	{
		if ($type == 'file' || $type == 'image')
		{
			$out = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $var);
			$out = mb_ereg_replace("([\.]{2,})", '', $out);
		}
		elseif ($type == 'name')
		{
			$var = trim($var);
			$var = html_entity_decode($var);
			$outx = htmlspecialchars_decode($var);
			$encoding = mb_detect_encoding($outx, "auto");
			$encoding = !empty($encoding) ? $encoding : 'ISO-8859-1';
			$outy = mb_convert_encoding($outx, 'UTF-8', $encoding);
			$outy = stripslashes($outy);
			mb_regex_encoding('UTF-8');
			$out = mb_ereg_replace_callback('[<>"\']', function($match) {return '';}, $outy);
			//$out = htmlspecialchars($outy, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			/*
			while (preg_match($pattern, $out) > 0)
				$out = htmlspecialchars_decode($out, ENT_QUOTES);

			$out = htmlspecialchars(stripslashes(trim($out)), ENT_QUOTES, 'UTF-8', true);
			*/
		}
		else
		{
			$var = trim($var);
			$var = html_entity_decode($var);
			$outx = htmlspecialchars_decode($var);
			$encoding = mb_detect_encoding($outx, "auto");
			$encoding = !empty($encoding) ? $encoding : 'ISO-8859-1';
			$outy = mb_convert_encoding($outx, 'UTF-8', $encoding);
			$outy = stripslashes($outy);
			mb_regex_encoding('UTF-8');
			$out = mb_ereg_replace(array('<', '>', '"', '\''), array('&lt;', '&gt;', '&quot;', '&#39;'), $outy);
		}
	}

	if ($type == 'image')
		return str_replace(' ', '_', $out);

	return $out;
}

function ArcadeSizer($file='', $maxwidth = 50, $maxheight = 50)
{
	global $arcadeModSettings, $boarddir;

	if (!empty($file))
	{
		$file = str_replace(' ', '%20',$file);
		if(list ($width, $height) = url_image_size($file))
		{
			if($height > $maxheight)
			{
				$percentage = ($maxheight / $height);
				$height = round($height * $percentage);
			}

			if($width > $maxwidth)
			{
				$percentage = ($maxwidth / $width);
				$width = round($width * $percentage);
			}

			return array($width, $height);
		}
	}

	return array($maxwidth, $maxheight);
}

function ArcadeCategoryDropdown($rom = 0, $skin = 'enterprisec')
{
	// Game Category drop down menu
	global $scripturl, $smcFunc, $txt, $settings, $context, $arcadeModSettings;
	list($count, $context['categories'], $gameCats, $regCats, $romCats) = array(0, array(), array(), array(), array());
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND rom_flag = {int:rom_flag}' : '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$nowCat = !empty($context['arcade_category' . $rom2]) ? $context['arcade_category' . $rom2] : 'all';
	$where1 = $scripturl . '?action=' . $action . ';category=';
	$mobileSpacing = !empty($_SESSION['arcade_isMobile']) ? 'margin: 5px 0px 0px 0px;padding: 1em 1em 0em 0em;' : ($skin != 'enterprisea' ? 'margin: 5px 0px 0px 5px;padding: 0.5em 1em 0.5em 0em;' : '');
	$requestz = $smcFunc['db_query']('', '
		SELECT id_cat, enabled, rom_flag
		FROM {db_prefix}arcade_games
		WHERE enabled = 1 AND id_cat > 0 AND rom_flag = 0',
		array()
	);
	while ($rowz = $smcFunc['db_fetch_assoc']($requestz)) {
		$regCats[$rowz['id_cat']] = empty($regCats[$rowz['id_cat']]) ? 1 : $regCats[$rowz['id_cat']] = $regCats[$rowz['id_cat']] + 1;
	}
	$smcFunc['db_free_result']($requestz);
	$requestz = $smcFunc['db_query']('', '
		SELECT id_cat, enabled, rom_flag
		FROM {db_prefix}arcade_games
		WHERE enabled = 1 AND id_cat > 0 AND rom_flag = 1',
		array()
	);
	while ($rowz = $smcFunc['db_fetch_assoc']($requestz)) {
		if (empty($arcadeModSettings['arcadeRomToggle']))
			$romCats[$rowz['id_cat']] = empty($romCats[$rowz['id_cat']]) ? 1 : $romCats[$rowz['id_cat']] = $romCats[$rowz['id_cat']] + 1;
		else
			$regCats[$rowz['id_cat']] = empty($regCats[$rowz['id_cat']]) ? 1 : $regCats[$rowz['id_cat']] = $regCats[$rowz['id_cat']] + 1;
	}
	$smcFunc['db_free_result']($requestz);
	$numCats = !empty($rom) ? $romCats : $regCats;

	$display = ($skin != 'enterprisea' ? '
	<form action="' . $scripturl . '?action=' . $action . '" method="post" style="padding: 0;margin: 0;left: 0;position: relative;">
		<div style="' . $mobileSpacing . 'font-size: 1.0em;"' . ($skin == 'enterprisea' ? 'class="buttonlist" ' : '') . '>
			<div><span style="display: none;">&nbsp;</span></div>' : '') . '
			<select class="button button_strip_arcade arcade_select arcade_category_selector" name="category" onchange="window.location.href=$(this).val();">
				<option class="arcade_cat_option" value="' . $scripturl . '?action=' . $action . ';category=' . $nowCat . '">' . $txt['view_cat'] . '</option>';

	$num = $smcFunc['db_query']('', '
      SELECT count(*)
      FROM {db_prefix}arcade_games
      WHERE enabled = 1 AND id_cat = 0' . $whererom,
	  array(
		'rom_flag' => !empty($rom) ? 1 : 0,
	  )
	);

	list ($no_cat) = $smcFunc['db_fetch_row']($num);
	$smcFunc['db_free_result']($num);

	if (!empty($arcadeModSettings['arcade_catHideUnused']))
	{
		if (empty($rom)) {
			$requestx = $smcFunc['db_query']('', '
				SELECT id_cat, rom_flag
				FROM {db_prefix}arcade_games
				WHERE enabled = 1 AND id_cat > 0' . $whererom,
				array(
					'rom_flag' => !empty($rom) ? 1 : 0,
				)
			);
		}
		else {
			$requestx = $smcFunc['db_query']('', '
				SELECT id_cat, rom_flag
				FROM {db_prefix}arcade_games
				WHERE enabled = 1 AND rom_flag = {int:rom_flag}',
				array(
					'rom_flag' => 1,
				)
			);
		}
		while ($rowx = $smcFunc['db_fetch_assoc']($requestx)) {
			$gameCats[] = $rowx['id_cat'];
		}
		$smcFunc['db_free_result']($requestx);

		// WHERE cats.id_cat in(SELECT id_cat from {db_prefix}arcade_games)
		$request = $smcFunc['db_query']('', '
			SELECT cats.id_cat, cats.cat_name, cats.num_games, cats.cat_order, cats.cat_icon
			FROM {db_prefix}arcade_categories as cats
			WHERE cats.id_cat in ({array_int:gameCats})
			ORDER BY cats.cat_order',
			array('gameCats' => !empty($gameCats) ? $gameCats : array(-1))
		);
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name, num_games, cat_order, cat_icon
			FROM {db_prefix}arcade_categories
			WHERE id_cat > 0
			ORDER BY cat_order',
			array()
		);
	}

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($numCats[$row['id_cat']]) && empty($arcadeModSettings['arcadeRomToggle']))
			continue;

		$context['categories'][$row['id_cat']] = array(
			'id' => $row['id_cat'],
			'name' => arcadeFixTagsHTML($row['cat_name'], true),
			'link' => $scripturl . '?action' . $action . ';category=' . $row['id_cat'],
			'drop' => '<a href="' . $scripturl . '?action=' . $action . ';category=' . $row['id_cat'] . '">' . $row['cat_name'] . '</a>',
			'icon' => !empty($row['cat_icon']) ? $settings['default_images_url'] . '/arc_icons/' . $row['cat_icon'] : '',
		);

		$display .= '
				<option class="arcade_cat_option" value="' . $scripturl . '?action=' . $action . ';category=' . $row['id_cat'] . '"' . (!empty($context['arcade_category' . $rom2]) && $context['arcade_category' . $rom2] == $row['id_cat'] ? ' selected' : '') . '>&nbsp;' . arcadeFixTagsHTML($row['cat_name'], true) . '</option>';
	}

	$smcFunc['db_free_result']($request);
	if (!empty($num))
	{
		$idNoCat = count($context['categories']) > 0 ? count($context['categories']) : 1;
		$context['categories'][$idNoCat] = array(
			'id' => 0,
			'name' => $txt['arcade_no_category'],
			'link' => $scripturl . '?action=' . $action . ';category=0',
			'drop' => '<a href="' . $scripturl . '?action=' . $action . ';category=0">' . $txt['arcade_no_category'] . '</a>',
			'icon' => $settings['default_images_url'] . '/arc_icons/Unassigned.gif',
		);
		$display .= '
				<option class="arcade_cat_option" value="' . $scripturl . '?action=' . $action . ';category=nocat"' . (!empty($context['arcade_category' . $rom2]) && $context['arcade_category' . $rom2] == 'nocat' ? ' selected' : '') . '>&nbsp;' . $txt['arcade_no_category'] . '</option>';
	}

	$display .= '
				<option class="arcade_cat_option" value="' . $scripturl . '?action=' . $action . ';category=all">&nbsp;' . $txt['arcade_all'] . '</option>
			</select>' . ($skin != 'enterprisea' ? '
		</div>
	</form>' : '');

	return $display;
}

function small_game_query($condition = '', $rom = 0)
{
	global $scripturl, $smcFunc, $arcadeModSettings, $txt, $user_info, $boardurl, $boarddir, $settings;

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$games = array();
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';

	if (empty($condition) && empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeGamesQuery = cache_get_data('arcade_games_query_small' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.game_rating, game.game_directory, game.thumbnail, game.member_groups, game.thumbnail_small, game.id_cat, game.js_insertion, game.download, game.rom_flag, game.extra_data,
			IFNULL(score.id_score,0) AS id_score, IFNULL(score.score,0) AS champScore,IFNULL(mem.id_member,0) AS id_member, category.cat_icon, info.icon_position, info.icon_position_hide, game.cover_icon,
			IFNULL(mem.real_name,0) AS real_name, IFNULL(info.icon_position,0) AS icon_position, IFNULL(info.icon_position_hide,0) AS icon_position_hide, category.cat_dl, category.cat_js
			FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = game.id_champion_score)
			LEFT JOIN {db_prefix}arcade_game_info AS info ON (info.id_game = game.id_game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
			WHERE ' . $user_info['query_see_game'] . $whererom . ' AND game.enabled = {int:enabled} '. $condition,
			array(
			  'enabled' => 1,
			  'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);
		while ($game = $smcFunc['db_fetch_assoc']($request))
		{
			$game['thumbnail'] = !empty($game['thumbnail']) ? $game['thumbnail'] : 'nofileexist.jpg';
			$game['thumbnail_small'] = !empty($game['thumbnail_small']) ? $game['thumbnail_small'] : 'nofileexist.jpg';
			$game['cover_icon'] = !empty($game['cover_icon']) ? $game['cover_icon'] : '';
			//sort the paths for the thumbnail
			$main = empty($game['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
			$gameDir = !empty($game['game_directory']) ? $main . '/' . $game['game_directory'] : $main;
			$action = empty($game['rom_flag']) ? 'arcade' : 'retro_arch';
			if (!empty($game['game_directory']))
				$gameUrl = empty($game['rom_flag']) ? $arcadeModSettings['gamesUrl'] . '/' . $game['game_directory'] : $arcadeModSettings['romGamesUrl'] . '/' . $game['game_directory'];
			else
				$gameUrl = empty($game['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
			$gameico = !is_dir($gameDir . '/' . $game['thumbnail']) && file_exists($gameDir . '/' . $game['thumbnail']) ? $gameUrl . '/' . $game['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameicosmall = !is_dir($gameDir . '/' . $game['thumbnail_small']) && file_exists($gameDir . '/' . $game['thumbnail_small']) ? $gameUrl . '/' . $game['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameico = $gameico == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameicosmall : $gameico;
			$gameicosmall = $gameicosmall == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameico : $gameicosmall;
			$iconfile = empty($game['rom_flag']) ? 'arcadegamecover.png' : 'romgamecover.gif';
			$covericon = !empty($game['cover_icon']) && file_exists($gameDir . '/' . $game['cover_icon']) ? $gameUrl . '/' . $game['cover_icon'] : $settings['default_images_url'] . '/arc_icons/' . $iconfile;

			$icon_position_int = !empty($game['icon_position']) ? (int)$game['icon_position'] : 0;
			$icon_position_int = abs(intval($icon_position_int));
			$icon_position = 'bottom: 0px;left: 0px;';
			$extra = !empty($game['extra_data']) && arcadeIsSerialized($game['extra_data']) ? arcade_safe_unserialize($game['extra_data']) : array();
			$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
			$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
			$downlink = !empty($extra['external_link']) && strlen(basename($extra['external_link']) > 4) && substr(basename($extra['external_link']), 0, 4) == 'rom_' ? $extra['external_link'] : $scripturl . '?action=' . $action . ';sa=download;game=' . $game['id_game'];
			switch($icon_position_int)
			{
				case 3:
					$icon_position = 'top: 0px;right: 0px;';
					break;
				case 2:
					$icon_position = 'top: 0px;left: 0px;';
					break;
				case 1:
					$icon_position = 'bottom: 0px;right: 0px;';
					break;
				default:
					$icon_position = 'bottom: 0px;left: 0px;';
			}

			//build and return an array of what is needed
			$hide = !empty($game['icon_position_hide']) ? (int)$game['icon_position_hide'] : 0;
			$games[$game['game_name']] = array(
				'id' => $game['id_game'],
				'url' => array(
					'play' => $scripturl . '?action=' . $action . ';sa=play;game=' . $game['id_game'] . ';#playgame',
					),
				'name' => $game['game_name'],
				'cat_icon' => !empty($row['cat_icon']) ? $settings['default_theme_url'] . '/arc_icons/' . $row['cat_icon'] : '',
				'rating' => $game['game_rating'],
				'rating2' => round($game['game_rating']),
				'js_insertion' =>  !empty($game['js_insertion']) ? $game['js_insertion'] : (!empty($game['cat_js']) ? 1 : 0),
				'download' =>  !empty($game['download']) && empty($game['cat_dl']) ? 1 : 0,
				'downlink' => $downlink,
				'rom_flag' => !empty($game['rom_flag']) ? 1 : 0,
				'allowed_download' => (allowedTo('arcade_download') && empty($game['rom_flag']) ? 1 : (allowedTo('arcade_download_rom') && !empty($game['rom_flag']) ? 1 : 0)),
				'thumbnail' => $gameico,
				'thumbnail_small' => $gameicosmall,
				'cover_icon' => $covericon,
				'isChampion' => $game['id_score'] > 0 ? true : false,
				'icon_position_int' => $icon_position_int,
				'icon_position' => $icon_position,
				'icon_position_hide' => abs(intval($hide)),
				'champion' => array(
					'member_id' => $game['id_member'],
					'memberLink' =>  $game['real_name'] != '' ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member'] . ';sa=statPanel">' . $game['real_name'] . '</a>' : $txt['arcade_guest'],
					'score' => round($game['champScore'],3),
				),
			);
		}

		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_query_small' . $rom2, json_encode($games, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}

	$games = !empty($arcadeGamesQuery) ? json_decode($arcadeGamesQuery, true) : $games;


	return $games;
}

function ArcadeCats($highlight='', $rom = 0)
{
	global $smcFunc, $db_prefix, $context, $scripturl, $txt, $boardurl, $arcadeModSettings, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND rom_flag = {int:rom_flag}' : '';
	list($output, $catCellWidth, $catsPerLine) = [
		'',
		(!empty($arcadeModSettings['arcadeCatsCellWidth']) ? intval($arcadeModSettings['arcadeCatsCellWidth']) : 20) . '%',
		(!empty($arcadeModSettings['arcadeCatsPerLine']) ? intval($arcadeModSettings['arcadeCatsPerLine']) : 5),
	];

	/* These are your adjustable variables */
	if (empty($arcadeModSettings['arcade_catWidth']))
		$arcadeModSettings['arcade_catWidth'] = 23;

	if (empty($arcadeModSettings['arcade_catHeight']))
		$arcadeModSettings['arcade_catHeight'] = 20;

	$icon_folder = $settings['default_images_url'] . '/arc_icons/';
	$icon_width = (int)$arcadeModSettings['arcade_catWidth'];
	$icon_height = (int)$arcadeModSettings['arcade_catHeight'];
	list($var, $filter, $gameCats, $regCats, $romCats) = array(array(), array(), array(), array(), array());
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	$requestz = $smcFunc['db_query']('', '
		SELECT id_cat, enabled, rom_flag
		FROM {db_prefix}arcade_games
		WHERE enabled = 1 AND id_cat > 0 AND rom_flag = 0',
		array()
	);
	while ($rowz = $smcFunc['db_fetch_assoc']($requestz)) {
		$regCats[$rowz['id_cat']] = empty($regCats[$rowz['id_cat']]) ? 1 : $regCats[$rowz['id_cat']] = $regCats[$rowz['id_cat']] + 1;
	}
	$smcFunc['db_free_result']($requestz);
	$requestz = $smcFunc['db_query']('', '
		SELECT id_cat, enabled, rom_flag
		FROM {db_prefix}arcade_games
		WHERE enabled = 1 AND id_cat > 0 AND rom_flag = 1',
		array()
	);
	while ($rowz = $smcFunc['db_fetch_assoc']($requestz)) {
		if (empty($arcadeModSettings['arcadeRomToggle']))
			$romCats[$rowz['id_cat']] = empty($romCats[$rowz['id_cat']]) ? 1 : $romCats[$rowz['id_cat']] = $romCats[$rowz['id_cat']] + 1;
		else
			$regCats[$rowz['id_cat']] = empty($regCats[$rowz['id_cat']]) ? 1 : $regCats[$rowz['id_cat']] = $regCats[$rowz['id_cat']] + 1;
	}
	$smcFunc['db_free_result']($requestz);

	if (!empty($arcadeModSettings['arcade_catHideUnused']))
	{
		if (empty($rom)) {
			$requestx = $smcFunc['db_query']('', '
				SELECT id_cat, enabled, rom_flag
				FROM {db_prefix}arcade_games
				WHERE enabled = 1 AND id_cat > 0' . $whererom,
				array(
					'rom_flag' => !empty($rom) ? 1 : 0,
				)
			);
		}
		else {
			$requestx = $smcFunc['db_query']('', '
				SELECT id_cat, enabled, rom_flag
				FROM {db_prefix}arcade_games
				WHERE enabled = 1 AND rom_flag = {int:rom_flag}',
				array(
					'rom_flag' => 1,
				)
			);
		}
		while ($rowx = $smcFunc['db_fetch_assoc']($requestx)) {
			$gameCats[] = $rowx['id_cat'];
		}
		$smcFunc['db_free_result']($requestx);

		// WHERE cats.id_cat in(SELECT id_cat from {db_prefix}arcade_games)
		$result = $smcFunc['db_query']('', '
			SELECT cats.id_cat, cats.cat_name, cats.num_games, cats.cat_order, cats.cat_icon
			FROM {db_prefix}arcade_categories as cats
			WHERE cats.id_cat in({array_int:gameCats})
			ORDER BY cats.cat_order',
			array('gameCats' => !empty($gameCats) ? $gameCats : array(-1))
		);
	}
	else
	{
		$select = $smcFunc['db_query']('', '
			SELECT id_cat, enabled from {db_prefix}arcade_games
			WHERE enabled = 1 AND id_cat > 0' . $whererom,
			array(
				'rom_flag' => !empty($rom) ? 1 : 0,
			)
		);

		while ($game_categories = $smcFunc['db_fetch_assoc']($select))
			$gameCats[] = $game_categories['id_cat'];

		$smcFunc['db_free_result']($select);

		$result = $smcFunc['db_query']('', '
			SELECT cats.cat_name, cats.id_cat, cats.num_games, cats.cat_icon
			FROM {db_prefix}arcade_categories as cats
			WHERE cats.id_cat in({array_int:gameCats})
			ORDER BY cats.cat_order',
			array('gameCats' => !empty($gameCats) ? $gameCats : array(-1))
		);
	}

	list($nocatjs, $nocat, $top) = array('', '', array());
	$numCats = !empty($rom) ? $romCats : $regCats;
	while ($categories = $smcFunc['db_fetch_assoc']($result)) {
		if (empty($numCats[$categories['id_cat']]))
			continue;

		$context['arcade']['cats'][] = array($categories['id_cat'], arcadeFixTagsHTML($categories['cat_name'], true), $numCats[$categories['id_cat']], $categories['cat_icon']);
	}

   $smcFunc['db_free_result']($result);

   $num = $smcFunc['db_query']('', '
      SELECT count(*)
      FROM {db_prefix}arcade_games
      WHERE enabled = 1 AND id_cat = 0' . $whererom,
	  array(
		'rom_flag' => !empty($rom) ? 1 : 0,
	  )
	);

	list ($no_cat) = $smcFunc['db_fetch_row']($num);

	$smcFunc['db_free_result']($num);

	list ($lines, $B_start, $Bstop, $output) = array(1, '', '', '');
	$context['cat_name'] = '';

	if($no_cat)
	{
		if ($highlight == '0')
		{
			$context['cat_name'] = $txt['arcade_no_category'];
			$B_start = '<strong>';
			$B_stop = '</strong>';
		}
		else
		{
			$B_start = '';
			$B_stop = '';
		}

		$gamepic_name = $txt['arcade_info_nocat'];
		$nocatjs .= '
			<script type="text/javascript">
				$(document).ready(function() {
					$("#arcade_drop_cat").hide();
					$(".arcade_cat_span").hover(function() {
						$(".arcade_cat_span").hide();
						$("#arcade_drop_cat").show();
					},
					function() {
						$("#arcade_drop_cat").hide();
						$(".arcade_cat_span").show();
					});
					$("#arcade_drop_cat").hover(function() {
						$(".arcade_cat_span").hide();
						$("#arcade_drop_cat").show();
					},
					function() {
						$("#arcade_drop_cat").hide();
						$(".arcade_cat_span").show();
					});
				});
			</script>';
		$category_pic = '<span style="height: 1.3rem;position: relative;" id="arcade_cat_span_img" class="arcade_cat_span"><img src="' . $icon_folder . 'Unassigned.gif" alt="&nbsp;" title="' . $gamepic_name . '" style="filter:contrast(125%);image-rendering: high-quality;
border: 0px;width: ' . $icon_width . 'px;height: ' . $icon_height . 'px;" /></span>';
		$nocat .= '
			<span style="flex: 1 0 ' . $catCellWidth . ';vertical-align: bottom;padding-top: 15px;border: 0px;width: ' . $catCellWidth . ';max-width: ' . $catCellWidth . ';" class="centertext windowbg2 smalltext">' . $category_pic . '
				<span style="display: block;"></span>
				<span style="margin-top: -2rem;height: 5rem;text-align: center;vertical-align: middle;border: 0px;display: inline-flex;flex-direction: column;justify-content: center;" id="arcade_drop_cat">
					<span style="margin: 4ch 0rem 0rem 1rem;">
						<a href="' . $scripturl . '?action=' . $action . ';sa=list;category=0">
							<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_show_all'] . '">' . $txt['arcade_AllGames'] . '</span>
						</a>
					</span>
					<span style="margin-left: 1rem;">
						<a href="' . $scripturl . '?action=' . $action . ';sa=list;sortby=nocat;">
							<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_info_shownocat'] . '">' . $txt['arcade_info_nocat'] . '</span>
						</a>
					</span>
				</span>
				<span style="height: 1.3rem;" id="arcade_cat_span" class="arcade_cat_span" title="' . $txt['alt_no_cats'] . '" >' . $B_start . sprintf($txt['arcade_no_cats'], $no_cat) . $B_stop . '</span>
			</span>';
	}

	if ($no_cat || !empty($context['arcade']['cats'])) {
		$output .= $nocatjs . '
		<div style=" display: flex;flex-wrap: wrap;width: 100%;justify-content: space-between;align-items: left;flex-direction: row;">' . $nocat;
	}

	if (!empty($context['arcade']['cats']))
	{
		$percent = abs(100 / $catsPerLine) . '%';
		$remainder = !empty($context['arcade']['cats']) ? (count($context['arcade']['cats'])) % $catsPerLine : 0;
		foreach ($context['arcade']['cats'] as $cat)
		{
			$gamepic_name = ArcadeSpecialChars($cat[1], 'image');
			$filter = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=', "&#039;");
			$gamepic_name = !empty($cat[3]) ? $cat[3] : str_replace($filter, "_", $gamepic_name) . '.gif';
			$category_pic = '<a style="display: block;" href="' . $scripturl.'?action=' . $action . ';category=' . $cat[0] . '"><img src="' . $icon_folder . $gamepic_name . '" alt="&nbsp;" title="' . $cat[1] . '" style="filter:contrast(125%);image-rendering: high-quality;border: 0px;width: ' . $icon_width . 'px;height: ' . $icon_height . 'px;" /></a>';

			if ($highlight == $cat[0] )
			{
				$context['cat_name'] = $cat[1];
				$B_start = '<b>';
				$B_stop = '</b>';
			}
			else
			{
				$B_start = '';
				$B_stop = '';
			}

			$output .= '
				<span style="flex: 1 0 ' . $percent . ';vertical-align: bottom;padding-top: 15px;border: 0px;width: ' . $catCellWidth . ';max-width: ' . $catCellWidth . ';min-width: ' . $catCellWidth . ';" class="centertext windowbg2 smalltext">
					' . $category_pic . '
					<a href="' . $scripturl . '?action=' . $action . ';category=' . $cat[0] . '">' . $B_start . $cat[1] . '(' . $cat[2] . ')' . $B_stop . '</a>
				</span>';
		}
		if (!empty($remainder)) {
			for($i=0;$i<$remainder;$i++) {
				$output .= '
					<span style="flex: 1 0 ' . $percent . ';vertical-align: bottom;padding-top: 15px;border: 0px;width: ' . $catCellWidth . ';visibility: hidden;" class="centertext windowbg2 smalltext">
						<span style="border: 0px;width: ' . $icon_width . 'px;height: ' . $icon_height . 'px;">&nbsp;</span>
						<span>&nbsp;</span>
					</span>';
			}
		}
	}

	if ($no_cat || !empty($context['arcade']['cats'])) {
		$output .= '
		</div>';
	}


	return $output;
}

function arcadeDecodeHtmlEnt($str)
{
    $ret = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
    $p2 = -1;
	if (empty($ret) || strlen($ret) < 5)
		return $ret;
    for(;;) {
		if (strlen($ret) < $p2+1)
			break;
        $p = strpos($ret, '&#', $p2+1);
        if ($p === FALSE)
            break;
        $p2 = strpos($ret, ';', $p);
        if ($p2 === FALSE)
            break;

        if (substr($ret, $p+2, 1) == 'x')
            $char = hexdec(substr($ret, $p+3, $p2-$p-3));
        else
            $char = intval(substr($ret, $p+2, $p2-$p-2));

        //echo "$char\n";
        $newchar = iconv(
            'UCS-4', 'UTF-8',
            chr(($char>>24)&0xFF).chr(($char>>16)&0xFF).chr(($char>>8)&0xFF).chr($char&0xFF)
        );
        //echo "$newchar<$p<$p2<<\n";
        $ret = substr_replace($ret, $newchar, $p, 1+$p2-$p);
        $p2 = $p + strlen($newchar);
    }
    return $ret;
}

function getGameInfo($id_game, $raw = false, $rom = 0)
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $arcadeModSettings, $context, $settings, $boarddir;

	$id_game = empty($id_game) ? 0 : $id_game;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeFormatting.php');

	// opt random game id
	if (!is_int($id_game) && stripos($id_game, 'random') !== FALSE) {
		$games = [];
		$romToggle = !empty($arcadeModSettings['arcadeRomToggle']) ? 1 : 0;
		switch($romToggle) {
			case 0:
				$request = $smcFunc['db_query']('', '
					SELECT id_game
					FROM {db_prefix}arcade_games
					WHERE enabled = {int:enabled}
						AND rom_flag = {int:rom_flag}',
					[
						'rom_flag' => $rom,
						'enabled' => 1,
					]
				);
				while($row = $smcFunc['db_fetch_assoc']($request))
					$games[] = $row['id_game'];
				$smcFunc['db_free_result']($request);
				break;
			default:
				$request = $smcFunc['db_query']('', '
					SELECT id_game
					FROM {db_prefix}arcade_games
					WHERE enabled = {int:enabled}',
					[
						'enabled' => 1,
					]
				);
				while($row = $smcFunc['db_fetch_assoc']($request))
					$games[] = $row['id_game'];
				$smcFunc['db_free_result']($request);
		}

		$randomGame = !empty($games) ? array_rand($games, 1) : 0;
		$id_game = !empty($games) ? $games[$randomGame] : 0;
	}

	$id_game = empty($rom) ? loadGame($id_game, false) : loadRomGame($id_game, false);

	if (empty($id_game) || $id_game === false)
		return false;

	if ($raw)
		return $context['arcade']['game_data'][$id_game];

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$game = &$context['arcade']['game_data'][$id_game];
	$main = empty($game['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
	$gameDir = !empty($game['game_directory']) ? $main . '/' . $game['game_directory'] : $main;
	// Is game installed in subdirectory
	if (!empty($game['game_directory']))
		$gameurl = empty($game['rom_flag']) ? $arcadeModSettings['gamesUrl'] . '/' . $game['game_directory'] . '/' : $arcadeModSettings['romGamesUrl'] . '/' . $game['game_directory'] . '/';
	else
		$gameurl = empty($game['rom_flag']) ? $arcadeModSettings['gamesUrl'] . '/' : $arcadeModSettings['romGamesUrl'] . '/';
	$thumbnail = file_exists($gameDir . '/' . $game['thumbnail']) ? $gameurl . '/' . $game['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
	$thumbnail_small = file_exists($gameDir . '/' . $game['thumbnail_small']) ? $gameurl . '/' . $game['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
	$coverfile = empty($rom) ? 'arcadegamecover.png' : 'romgamecover.gif';
	$covericon = !empty($game['cover_icon']) && file_exists($gameDir . '/' . $game['cover_icon']) ? $gameurl . '/' . $game['cover_icon'] : $settings['default_images_url'] . '/arc_icons/' . $coverfile;
	$description = parse_bbc(arcadeDecodeHtmlEnt($game['description']));
	$help = parse_bbc($game['help']);
	$extra = !empty($game['extra_data']) && arcadeIsSerialized($game['extra_data']) ? arcade_safe_unserialize($game['extra_data']) : array();
	$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
	$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
	$extra['type'] = !empty($extra['type']) ? $extra['type'] : 'normal';
	$version = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$path = (empty($game['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory']) . '/' . (!empty($game['game_directory']) ? $game['game_directory'] . '/' : '') . $game['game_file'];
	$icon_position_int = !empty($game['icon_position']) ? (int)$game['icon_position'] : 0;
	$icon_position_int = abs(intval($icon_position_int));
	$icon_position = 'bottom: 0em;left: 0em;';
	$action = empty($game['rom_flag']) ? 'arcade' : 'retro_arch';
	switch($icon_position_int)
	{
		case 3:
			$icon_position = 'top: 0em;right: 0em;';
			break;
		case 2:
			$icon_position = 'top: 0em;left: 0em;';
			break;
		case 1:
			$icon_position = 'bottom: 0em;right: 0em;';
			break;
		default:
			$icon_position = 'bottom: 0em;left: 0em;';
	}

	if (!empty($game['real_name']))
	{
		$player_name = $game['real_name'];
		$guest = empty($game['id_member']);
	}
	else
	{
		$player_name = $txt['guest'];
		$guest = true;
	}

	$subsystem = strpos($game['submit_system'], 'html5') !== false ? true : false;
	$initiateFull = !empty($_SESSION['arcade_isMobile']) || $extra['type'] == 'fullscreen' ? true : false;
	if ($subsystem && $extra['type'] == 'fullscreen' && file_exists($path) && empty($rom))
	{
		$filesdir = dirname($game['game_file']) !== '.' ? dirname($game['game_file']) . '/' : '';
		$reload = isset($_REQUEST['reload']) ? (int)$_REQUEST['reload'] : 0;
		$form = '
		<form id="gameForm" action="' . $scripturl . '?action=' . $action . ';game=' . $game['id_game'] . ';sa=' . $game['submit_system'] . 'Game;" method="post" target="_self">
			<input type="hidden" id="game" name="game" value="' . $game['id_game'] . '" />
			<input type="hidden" id="smfgametime" name="time" value="' . time() . '" />
			<input type="hidden" id="html5" name="html5" value="1" />' . ($game['submit_system'] == 'html52' || $game['submit_system'] == 'html53' ? '
			<input type="hidden" id="html52" name="html52" value="1" />' : '') . ($user_info['is_guest'] && empty($_SESSION['playerName']) ? '
			<input type="hidden" id="guestname" name="guestname" value="1" />' : '
			<input type="hidden" id="guestname" name="guestname" value="0" />') . '
			<input type="hidden" id="smfGameSaveUrl" name="smfGameSaveUrl" value="' . $settings['default_theme_url'] . '/scripts/arcade-html5-save.js" />
			<input type="hidden" id="html5smfGameUrl" name="html5smfGameUrl" value="' . $scripturl . '?action=' . $action . ';game=' . $game['id_game'] . (!empty($reload) ? ';reload=' . $reload : '') . ';#playgame" />
			<input type="hidden" id="gameSmfFullscreen" name="gameSmfFullscreen" value="1" />
			<input type="hidden" id="popup" name="popup" value="0" />
			<input type="hidden" id="gameexit" name="gameexit" value="0" />
			<input type="hidden" id="noSmfScore" name="noSmfScore" value="' . $txt['arcade_noSmfScore'] . '" />
			<input type="hidden" id="gameSmfToken" name="gameSmfToken" value="(SMF_GAME_TOKEN)" />
			<input type="hidden" id="game_name" name="game_name" value="' . $game['internal_name'] . '" />
			<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
		</form>';
		$escUrl = $scripturl . '?action=' . $action . ';sa=highscore;game=' . $game['id_game'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3';
		$esc = '
		<div class="escgamediv" style="position: absolute;top: 20px;right: 0px;z-index: 100;">
			<img class="escgame" id="escbutton" src="' . $settings['default_theme_url'] . '/images/arc_icons/arcade_esc.png' . '" alt="[ESC]" onclick="(function(){ window.location = \'' . ($escUrl) . '\';return false; })();return false;" />
		</div>';

		$addJs = '
		<script type="text/javascript">
			function smfArcadeGameDims999() {
				var bodyX = document.getElementsByTagName("body")[0];
				bodyX.style.overflow = "hidden";
				bodyX.style.width = "100vw";
			}
			function escGameSmf() {
				window.location = "' . $scripturl . '?action=' . $action . ';sa=highscore;game=' . $game['id_game'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
			}
			if (window.addEventListener) {
				window.addEventListener("load", function (){
					smfArcadeGameDims999();
					return true;
				});
			}
			else {
				window.attachEvent("onload", function (){
					smfArcadeGameDims999();
					return true;
				});
			}
		</script>';

		// if one of these is enabled we will use it else fall back on out-of-date DOM parser (atm DOM parses the file as HTML 4.0)
		if (@ini_get('allow_url_fopen'))
			$gamedoc = file_get_contents($path);
		elseif (in_array('curl', get_loaded_extensions()))
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, rtrim($gameurl, '/') . '/' . $game['game_file']);
			$gamedoc = curl_exec($c);
			curl_close($c);
		}
		else
		{
			// DOM ~ suppress warnings, parse the file & finally remove illegal end tags it may add
			libxml_use_internal_errors(true) && libxml_clear_errors();
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->loadHTMLFile($path);
			$gamedoc = $doc->saveHTML();
			$remove = array(
				'</embed>',
				'</track>',
				'</source>',
				'</keygen>',
				'</wbr>',
				'</base>',
			);
			$gamedoc = str_replace($remove, '', $gamedoc);
		}

		// use <!--(BASE_URL)--> directly after <head> if the game does not have a standard <head> element (HTML5 does not require it!)
		$newdoc = str_replace(
			array(
				'<head>',
				'</body>',
			),
			array(
				'<head><base href="' . $gameurl . $filesdir . '" target="_blank">',
				$form . $esc . $addJs . '</body>'
			),
			$gamedoc
		);

		if (stripos($newdoc, '</body>') === false && strpos('<!--(BASE_URL)-->', $gamedoc) === true)
			$newdoc = str_replace('<!--(BASE_URL)-->', '<base href="' . $gameurl . $filesdir . '" target="_blank">', $gamedoc) . $form . $esc . $addJs;
		elseif (stripos($newdoc, '</body>') === false)
			$newdoc .= $form . $esc . $addJs;

		// clean up all unnecessary white-space prior to any output
		$newdoc = rtrim(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $newdoc));
	}

	$hide = !empty($game['icon_position_hide']) ? (int)$game['icon_position_hide'] : 0;
	$extra = !empty($game['extra_data']) && arcadeIsSerialized($game['extra_data']) ? arcade_safe_unserialize($game['extra_data']) : array();
	$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
	$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
	$downlink = !empty($extra['external_link']) && strlen(basename($extra['external_link']) > 4) && substr(basename($extra['external_link']), 0, 4) == 'rom_' ? $extra['external_link'] : $scripturl . '?action=' . $action . ';sa=download;game=' . $game['id_game'];

	return array(
		'id' => $game['id_game'],
		'url' => array(
			'play' => $scripturl . '?action=' . $action . ';sa=play;game=' . $game['id_game'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
			'base_url' => $gameurl,
			'highscore' => $scripturl . '?action=' . $action . ';sa=highscore;game=' . $game['id_game'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3',
			'flash' => $gameurl . $game['game_file'],
			'favorite' => $context['arcade']['can_favorite'] ? $game['is_favorite'] == 0 ? $scripturl . '?action=' . $action . ';sa=favorite;game=' . $game['id_game'] : $scripturl . '?action=' . $action . ';sa=favorite;remove;game=' . $game['id_game'] : '#',
		),
		'extra_data' => $extra,
		'category' => array(
			'id' => $game['id_cat'],
			'name' => arcadeFixTagsHTML($game['cat_name'], true),
			'link' => $scripturl . '?action=' . $action . ';category=' . $game['id_cat'],
			'cat_icon' => !empty($game['cat_icon']) ? $settings['default_images_url'] . '/arc_icons/' . $game['cat_icon'] : '',
		),
		'submit_system' => $game['submit_system'],
		'rom_system' => $game['rom_system'],
		'internal_name' => $game['internal_name'],
		'name' => $game['game_name'],
		'directory' => $game['game_directory'],
		'file' => $game['game_file'],
		'path' => $path,
		'html5' => !empty($newdoc) ? $newdoc : '',
		'description' => !empty($arcadeModSettings['arcade_adjust_desc_info']) ? arcade_translate($description, '', false) : $description,
		'help' => !empty($arcadeModSettings['arcade_adjust_desc_info']) ? arcade_translate($help, '', false) : $help,
		'report_id' => !empty($game['report_id']) ? $game['report_id'] : 0,
		'rating' => $game['game_rating'],
		'rating2' => round($game['game_rating']),
		'thumbnail' => $thumbnail,
		'thumbnail_small' => $thumbnail_small,
		'cover_icon' => $covericon,
		'is_champion' => $game['id_score'] > 0,
		'champion' => array(
			'id' => $game['id_member'],
			'name' => $player_name,
			'score_id' => $game['id_score'],
			'link' =>  !$guest ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member'] . '">' . $player_name . '</a>' : $player_name,
			'score' => comma_format((float) $game['champ_score']),
			'time' => $game['champion_time'],
		),
		'is_personal_best' => !$user_info['is_guest'] && $game['id_pb'] > 0,
		'personal_best' => !$user_info['is_guest'] ? comma_format((float) $game['personal_best']) : 0,
		'personal_best_score' => !$user_info['is_guest'] ? $game['personal_best'] : 0,
		'score_type' => $game['score_type'],
		'highscore_support' => $game['score_type'] != 2,
		'is_favorite' => $context['arcade']['can_favorite'] ? $game['is_favorite'] > 0 : false,
		'favorite' => $game['num_favorites'],
		'member_groups' => isset($game['member_groups']) ? explode(',', $game['member_groups']) : array(),
		'width' => !empty($_SESSION['arcade_isMobile']) ? '100vw' : (!empty($extra['width']) ? (int)$extra['width'] : 400),
		'height' => !empty($_SESSION['arcade_isMobile']) ? '100vh' : (!empty($extra['height']) ? (int)$extra['height'] : 600),
		'type' => !empty($extra['type']) ? trim($extra['type']) : '',
		'smf_version' => $version,
		'js_insertion' => !empty($game['js_insertion']) ? (int)$game['js_insertion'] : 0,
		'download' => !empty($game['download']) ? 1 : 0,
		'allow_download' => (allowedTo('arcade_download') && empty($rom) ? 1 : (allowedTo('arcade_download_rom') && !empty($rom) ? 1 : 0)),
		'downlink' => $downlink,
		'icon_position_int' => $icon_position_int,
		'icon_position' => $icon_position,
		'icon_position_hide' => abs(intval($hide)),
		'rom_flag' => !empty($rom) ? 1 : 0,
	);
}

function arcade_random_xml()
{
	global $arcadeModSettings, $context, $txt;

	$romToggle = !empty($arcadeModSettings['arcadeRomToggle']) ? 1 : 0;
	$rom = isset($_REQUEST['rom']) && $_REQUEST['rom'] == 1 && allowedTo('arcade_view_retro_arch') ? 1 : 0;
	if (!empty($context['session_var']) && !empty($context['session_id'])) {
		$sessionVar = isset($_REQUEST[$context['session_var']]) ? $_REQUEST[$context['session_var']] : '';
	}

	if (!empty($sessionVar) && $sessionVar == $context['session_id']) {
			$gameData = getGameInfo('random', false, $rom);
			$gameData['thumbnail'] = !empty($gameData['thumbnail']) && stripos($gameData['thumbnail'], 'arc_icons/Default.gif') === FALSE ? $gameData['thumbnail'] : $gameData['thumbnail_small'];
			exit('<?xml version="1.0" encoding="UTF-8"?>
<gamexml>
	<id>' . $gameData['id'] . '</id>
	<gamename>' . htmlspecialchars($gameData['name'], ENT_XML1, 'UTF-8') . '</gamename>
	<description>' . htmlspecialchars($gameData['description'], ENT_XML1, 'UTF-8') . '</description>
	<play>' . $gameData['url']['play'] . '</play>
	<thumbnail>' . $gameData['thumbnail'] . '</thumbnail>
	<thumbnail_small>' . $gameData['thumbnail_small'] . '</thumbnail_small>
	<rom_flag>' . $gameData['rom_flag'] . '</rom_flag>
</gamexml>');
	}
	else {
		exit(htmlspecialchars($txt['cannot_arcade_access_denied'], ENT_XML1, 'UTF-8'));
	}
}

function arcade_translate($text, $gameName, $default)
{
	global $context, $language, $user_info;
	$lang =  $user_info['is_guest'] || !empty($default) ?  $language : $context['user']['language'];
	$userLang = strtolower(str_replace('-utf8', '', $lang));
	$text =  arcade_html_entity_decode($text, 2, 2);
	$languagesAll = array(
      "Afrikaans" => "af",
      "Albanian" => "sq",
      "Arabic" => "ar",
      "Armenian" => "hy",
      "Azerbaijani" => "az",
      "Basque" => "eu",
      "Belarusian" => "be",
      "Bengali" => "bn",
      "Bosnian" => "bs",
      "Bulgarian" => "bg",
      "Catalan" => "ca",
      "Cebuano" => "ceb",
      "Chichewa" => "ny",
      "Chinese" => "zh-CN",
      "Croatian" => "hr",
      "Czech" => "cs",
      "Danish" => "da",
      "Dutch" => "nl",
      "English" => "en",
      "Esperanto" => "eo",
      "Estonian" => "et",
      "Filipino" => "tl",
      "Finnish" => "fi",
      "French" => "fr",
      "Galician" => "gl",
      "Georgian" => "ka",
      "German" => "de",
      "Greek" => "el",
      "Gujarati" => "gu",
      "Haitian Creole" => "ht",
      "Hausa" => "ha",
      "Hebrew" => "iw",
      "Hindi" => "hi",
      "Hmong" => "hmn",
      "Hungarian" => "hu",
      "Icelandic" => "is",
      "Igbo" => "ig",
      "Indonesian" => "id",
      "Irish" => "ga",
      "Italian" => "it",
      "Japanese" => "ja",
      "Javanese" => "jw",
      "Kannada" => "kn",
      "Kazakh" => "kk",
      "Khmer" => "km",
      "Korean" => "ko",
      "Lao" => "lo",
      "Latin" => "la",
      "Latvian" => "lv",
      "Lithuanian" => "lt",
      "Macedonian" => "mk",
      "Malagasy" => "mg",
      "Malay" => "ms",
      "Malayalam" => "ml",
      "Maltese" => "mt",
      "Maori" => "mi",
      "Marathi" => "mr",
      "Mongolian" => "mn",
      "Myanmar (Burmese)" => "my",
      "Nepali" => "ne",
      "Norwegian" => "no",
      "Persian" => "fa",
      "Polish" => "pl",
      "Portuguese" => "pt",
      "Punjabi" => "pa",
      "Romanian" => "ro",
      "Russian" => "ru",
      "Serbian" => "sr",
      "Sesotho" => "st",
      "Sinhala" => "si",
      "Slovak" => "sk",
      "Slovenian" => "sl",
      "Somali" => "so",
      "Spanish" => "es",
      "Sundanese" => "su",
      "Swahili" => "sw",
      "Swedish" => "sv",
      "Tajik" => "tg",
      "Tamil" => "ta",
      "Telugu" => "te",
      "Thai" => "th",
      "Turkish" => "tr",
      "Ukrainian" => "uk",
      "Urdu" => "ur",
      "Uzbek" => "uz",
      "Vietnamese" => "vi",
      "Welsh" => "cy",
      "Yiddish" => "yi",
      "Yoruba" => "yo",
      "Zulu" => "zu",
    );

	$languages = array_change_key_case($languagesAll, CASE_LOWER);
	$to_language = array_key_exists($userLang, $languages) ? $languages[$userLang] : 'en';
	$detectedTextLang = arcade_translate_query_lang($text, $gameName);
	$from = !empty($detectedTextLang) ? $detectedTextLang : $to_language;
	$text = !empty($text) ? arcadeStripTags($text) : '';
	if ($to_language != $detectedTextLang && $detectedTextLang != 'xx'){
		$translatedText = arcade_translate_api('https://api.cognitive.microsofttranslator.com/translate?api-version=3.0', $text, $from, $to_language, $gameName, true);
		$translatedText == $text ? arcade_google_translate_api($text, $from, $to_language) : $translatedText;
	}
	elseif ($detectedTextLang == 'xx') {
		$translatedText = arcade_translate_api('https://api.cognitive.microsofttranslator.com/translate?api-version=3.0', $text, '', $to_language, $gameName, true);
		$translatedText == $text ? arcade_google_translate_api($text, '', $to_language) : $translatedText;
	}
	else
		$translatedText = $text;

	return arcade_html_entity_decode($translatedText, 2, 2);
}

function arcade_translate_api($api_url, $text, $from, $to_language, $gameName, $trim)
{
	global $arcadeModSettings, $txt;
	$translateType = empty($arcadeModSettings['arcadeTranslationAPI']) ? 0 : (int)$arcadeModSettings['arcadeTranslationAPI'];

	switch($translateType) {
		case 0:
			if (empty($arcadeModSettings['arcadeAzureSubKey']) && empty($arcadeModSettings['arcadeAzureSubKey2'])) {
				$apiIdArray = array('DB50E2E9FBE2E92B103E696DCF4E3E512A8826FB', '58C40548A812ED699C35664525D8A8104D3006D2');
				foreach ($apiIdArray as $api) {
					$response = arcade_translate_ms_api_v2($api, $text, $from, $to_language);
					if ($response != $text) {
						$translate['translate'] = $response;
						break;
					}
				}
			}
			else {
				$endpoint = !empty($arcadeModSettings['arcadeAzureEndpoint']) && filter_var($arcadeModSettings['arcadeAzureEndpoint'], FILTER_VALIDATE_URL) !== FALSE ? rtrim($arcadeModSettings['arcadeAzureEndpoint'], '/') : 'https://api.cognitive.microsofttranslator.com';
				$region = !empty($arcadeModSettings['arcadeAzureRegionCode']) ? $arcadeModSettings['arcadeAzureRegionCode'] : '';
				$path = !empty($arcadeModSettings['arcadeAzurePath']) ? $arcadeModSettings['arcadeAzurePath'] : '/translate?api-version=3.0';
				$path = '/' . ltrim($path, '/');
				$keys = !empty($arcadeModSettings['arcadeAzureSubKey2']) ? array($arcadeModSettings['arcadeAzureSubKey'], $arcadeModSettings['arcadeAzureSubKey2']) : array($arcadeModSettings['arcadeAzureSubKey']);
				$keys = array_filter($keys);
				$params = !empty($from) ? '&from=' . $from . '&to=' . $to_language : '&to=' . $to_language;
				$requestBody = array(
					array(
						'Text' => $text,
					),
				);

				$content = json_encode($requestBody);
				foreach ($keys as $key) {
					$response = arcade_translate_ms_api_v3($endpoint, $path, $key, $region, $params, $content);
					if (!empty($response) && empty($response['error'])) {
						$json = json_decode(json_encode(json_decode($response), JSON_UNESCAPED_UNICODE), true);
						if (empty($json[0])) {
							$responsArray = json_decode($response, true);
							if (!empty($arcadeModSettings['arcade_log_translate']) && !empty($gameName) && !empty($responsArray['error']) && !empty($responsArray['error']['message'])) {
								$errMsg = sprintf($txt['arcade_translate_error'], $gameName, $from, $to_language) . (!empty($responsArray['error']) ? ' ~ ' . $responsArray['error']['message'] : '');
								$errMsg = arcade_html_entity_decode($errMsg, 2, 2);
								log_error($errMsg, 'debug');
							}
						}
						else
							$translate['translate'] =  $json[0]['translations'][0]['text'];
						break;
					}
				}
			}
			break;
		case 1:
			$endpoint = !empty($arcadeModSettings['arcadeDeepLEndpoint']) && filter_var($arcadeModSettings['arcadeDeepLEndpoint'], FILTER_VALIDATE_URL) !== FALSE ? rtrim($arcadeModSettings['arcadeDeepLEndpoint'], '/') : 'https://api-free.deepl.com';
			$path = !empty($arcadeModSettings['arcadeDeepLPath']) ? $arcadeModSettings['arcadeDeepLPath'] : '/v2/translate';
			$path = '/' . ltrim($path, '/');
			$key = !empty($arcadeModSettings['arcadeDeepLSubKey']) ? $arcadeModSettings['arcadeDeepLSubKey'] : '';
			$response = !empty($key) ? arcade_DeepL_translate_api($endpoint, $path, $key, $text, strtoupper($from), strtoupper($to_language)) : '';
			if (!empty($response)) {
				if (!empty($response['error'])) {
					$translate['error']['message'] = $txt['arcade_deepL_error'];
				}
				else
					$translate['translate'] =  $response['text'];
				break;
			}
			else
				return $text;
			break;
		default:
			return $text;
	}

	if (empty($response) || $response === false || !empty($response['error'])) {
		if (!empty($arcadeModSettings['arcade_log_translate']) && !empty($gameName)) {
			$errMsg = sprintf($txt['arcade_translate_error'], $gameName, $from, $to_language) . (!empty($translate['error']) ? ' ~ ' . $translate['error']['message'] : '');
			$errMsg = arcade_html_entity_decode($errMsg, 2, 2);
			log_error($errMsg, 'debug');
		}
		return $text;
	}

	if (!empty($translate) && is_array($translate)) {
		return !empty($translate['translate']) ? $translate['translate'] : $text;
	}

	return $text;
}

function arcade_translate_query_lang($text, $gname = '')
{
	global $arcadeModSettings;

	if (empty($arcadeModSettings['arcadeAzureSubKey']) && empty($arcadeModSettings['arcadeAzureSubKey2']))
		return arcade_translate_query_lang_v2($text, $gname);

	$endpoint = !empty($arcadeModSettings['arcadeAzureEndpoint']) && filter_var($arcadeModSettings['arcadeAzureEndpoint'], FILTER_VALIDATE_URL) !== FALSE ? rtrim($arcadeModSettings['arcadeAzureEndpoint'], '/') : 'https://api.cognitive.microsofttranslator.com';
	$region = !empty($arcadeModSettings['arcadeAzureRegionCode']) ? $arcadeModSettings['arcadeAzureRegionCode'] : '';
	$path = !empty($arcadeModSettings['arcadeAzurePath']) ? str_replace('translate', 'detect', $arcadeModSettings['arcadeAzurePath']) : '/detect?api-version=3.0';
	$path = '/' . ltrim($path, '/');
	$keys = !empty($arcadeModSettings['arcadeAzureSubKey2']) ? array($arcadeModSettings['arcadeAzureSubKey'], $arcadeModSettings['arcadeAzureSubKey2']) : array($arcadeModSettings['arcadeAzureSubKey']);
	$keys = array_filter($keys);
	$noNameText = !empty($gname) ? str_ireplace($gname, '', $text) : $text;
	$shortzText = preg_replace('/[^a-z \d]+/i', ' ', $noNameText);
	$shortText = preg_replace('/((\w+\W*){'.(8).'}(\w+))(.*)/', '${1}', $shortzText);
	$requestBody = array(
		array(
			'Text' => $shortText,
		),
	);

	$content = json_encode($requestBody);
	foreach ($keys as $key) {
		$response = arcade_translate_query_lang_v3($endpoint, $path, $key, $region, $content);
		if (!empty($response) && empty($response['error'])) {
			$from =  $response;
			break;
		}
	}

	if (!empty($from)) {
		$json = json_decode(json_encode(json_decode($from), JSON_UNESCAPED_UNICODE), true);
		$new =  !empty($json[0]['language']) ? $json[0]['language'] : 'xx';

		if ($new != 'xx')
			return $new;
	}

	return arcade_translate_query_lang_v2($text, $gname);
}

function arcade_translate_query_lang_v2($text, $gname = '')
{
	// use v2 api to scope out the source language
	$apiIdArray = array('DB50E2E9FBE2E92B103E696DCF4E3E512A8826FB', '58C40548A812ED699C35664525D8A8104D3006D2');
	$noNameText = str_ireplace($gname, '', $text);
	$shortText = preg_replace('/((\w+\W*){'.(8).'}(\w+))(.*)/', '${1}', $noNameText);
	foreach ($apiIdArray as $apiId) {
		$api_url = 'https://api.microsofttranslator.com/V2/Ajax.svc/Detect?appid=' . $apiId . '&oncomplete=?&text=' .  urlencode($shortText);
		$curlArray = array(
			CURLOPT_URL => $api_url,
			CURLOPT_HEADER => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_RETURNTRANSFER => 1,
		);

		$ch = curl_init();
		curl_setopt_array($ch, $curlArray);
		$response = curl_exec($ch);

		if (curl_error($ch) || $response === false)
			continue;

		@curl_close($ch);

		$lang = substr($response, 4, -1);
		break;
	}

	if (empty($lang)) {
		return 'xx';
	}

	return trim(strtolower($lang));
}

function arcade_translate_query_lang_v3($endpoint, $path, $key, $region, $content)
{
	$com_create_guid = function() {
		return !function_exists('com_create_guid') ? (sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		)) : com_create_guid();
	};

	// v3 api requires a subscription ~ free subscription allows 2M characters per month
	if (!empty($region)) {
		$headers = [
			'Content-length: ' . strlen($content),
			'Content-Type: application/json; charset=utf-8',
			'Ocp-Apim-Subscription-Key: ' . $key,
			'Ocp-Apim-Subscription-Region: ' . $region,
			'X-ClientTraceId: ' . $com_create_guid()
		];
	}
	else {
		$headers = [
			'Content-length: ' . strlen($content),
			'Content-Type: application/json; charset=utf-8',
			'Ocp-Apim-Subscription-Key: ' . $key,
			'X-ClientTraceId: ' . $com_create_guid()
		];
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint . $path);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec ($ch);
	curl_close ($ch);

    return $result;
}

function arcade_translate_ms_api_v3($endpoint, $path, $key, $region, $params, $content)
{
	global $arcadeModSettings;

	$com_create_guid = function() {
		return !function_exists('com_create_guid') ? (sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		)) : com_create_guid();
	};

	// v3 api requires a subscription ~ free subscription allows 2M characters per month
	if (!empty($arcadeModSettings['arcadeAzureRegionCode'])) {
		$headers = [
			'Content-length: ' . strlen($content),
			'Content-Type: application/json; charset=utf-8',
			'Ocp-Apim-Subscription-Key: ' . $key,
			'Ocp-Apim-Subscription-Region: ' . $region,
			'X-ClientTraceId: ' . $com_create_guid()
		];
	}
	else {
		$headers = [
			'Content-length: ' . strlen($content),
			'Content-Type: application/json; charset=utf-8',
			'Ocp-Apim-Subscription-Key: ' . $key,
			'X-ClientTraceId: ' . $com_create_guid()
		];
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint . $path . $params);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec ($ch);
	curl_close ($ch);

    return $result;
}

function arcade_translate_ms_api_v2($appId, $text, $from, $to)
{
	$bom = pack('CCC', 0xEF, 0xBB, 0xBF);
	$textEncoded = urlencode($text);
	$api_url = 'https://api.microsofttranslator.com/V2/Ajax.svc/Translate?text=' .  $textEncoded . '&appId=' . $appId . (!empty($from) ? '&from=' . $from : '') . '&to=' . $to;
	$curlArray = array(
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $api_url,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);
	$ch = curl_init();
	curl_setopt_array($ch, $curlArray);
	$response = curl_exec($ch);
	curl_close($ch);
	if (substr($response, 0, 3) === $bom)
		$response = substr($response, 3);

	if ($response === false)
		return $text;

	$length = strlen($response);
	if ($length > 2 && $response[0] == '"' && $response[$length-1] == '"')
		return substr($response, 1, -1);

	return  $text;
}

function arcade_google_translate_api($q, $sl, $tl)
{
	$from = !empty($sl) ? '&sl=' . $sl : '';
    $api_url ="https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=t" . $from . "&tl=" . $tl . "&hl=hl&q=" . urlencode($q);
	$curlArray = array(
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_URL => $api_url,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);
	$ch = curl_init();
	curl_setopt_array($ch, $curlArray);
	$res = curl_exec($ch);
	curl_close($ch);


    $array = json_decode($res);
	if (!empty($array) && is_array($array)) {
		$newArray = array();
		foreach ($array as $arr) {
			$arr = is_array($arr) ? array_filter($array) : $arr;
			$newArray[] = $arr;
		}
		$array = array_filter($newArray);
		$new = arcadeFlattenArray($array);
		return !empty($new[0]) ? $new[0] : $q;
	}

	return $q;
}

function arcade_DeepL_translate_api($endpoint, $path, $key, $q, $sl, $tl)
{
	global $arcadeModSettings;

	// some languages require DeepL unique codes/settings
	$pt = !empty($arcadeModSettings['arcadeDeepLPT']) ? 'PT-BR' : 'PT-PT';
	$en = !empty($arcadeModSettings['arcadeDeepLEN']) ? 'EN-GB' : 'EN-US';
	$tl = $tl == 'EN' ? $en : $tl;
	$tl = $tl == 'PT' ? $pt : $tl;
	$sl == 'ZH-CN' ? 'ZH' : $sl;
	$tl == 'ZH-CN' ? 'ZH' : $tl;

	if (empty($sl)) {
		$postRequest = array(
			'auth_key' => $key,
			'text' => $q,
			'target_lang' => $tl
		);
	}
	else {
		$postRequest = array(
			'auth_key' => $key,
			'text' => $q,
			'source_lang' => $sl,
			'target_lang' => $tl
		);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint . $path);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec ($ch);
	curl_close ($ch);

	$result = !empty($result) ? json_decode($result, true) : '';
	if (!empty($result) && is_array($result) && !empty($result['translations']) && !empty($result['translations'][0]) && !empty($result['translations'][0]['text']))
		$deepL = array('error' => '', 'text' => $result['translations'][0]['text']);
	else
		$deepL = array('error' => 'invalid', 'text' => $q);

	return $deepL;
}

function arcadeFlattenArray(array $array)
{
    return iterator_to_array(
         new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array)));
}

function arcadeUtf8tohtml($utf8, $encodeTags = array()) {
    $result = '';
    for ($i = 0; $i < strlen($utf8); $i++) {
        $char = $utf8[$i];
        $ascii = ord($char);
        if ($ascii < 128) {
            // one-byte character
            $result .= ($encodeTags) ? htmlentities($char) : $char;
        } elseif ($ascii < 192) {
            // non-utf8 character or not a start byte
        } elseif ($ascii < 224) {
            // two-byte character
            $result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
            $i++;
        } elseif ($ascii < 240) {
            // three-byte character
            $ascii1 = ord($utf8[$i+1]);
            $ascii2 = ord($utf8[$i+2]);
            $unicode = (15 & $ascii) * 4096 +
                       (63 & $ascii1) * 64 +
                       (63 & $ascii2);
            $result .= "&#$unicode;";
            $i += 2;
        } elseif ($ascii < 248) {
            // four-byte character
            $ascii1 = ord($utf8[$i+1]);
            $ascii2 = ord($utf8[$i+2]);
            $ascii3 = ord($utf8[$i+3]);
            $unicode = (15 & $ascii) * 262144 +
                       (63 & $ascii1) * 4096 +
                       (63 & $ascii2) * 64 +
                       (63 & $ascii3);
            $result .= "&#$unicode;";
            $i += 3;
        }
    }
    return mb_convert_encoding($result, 'UTF-8', 'UTF-8');
}

function arcadeStripTags($html, $allowed_tags=array())
{
	$allowed_tags = array_map('strtolower', $allowed_tags);
	$rhtml = preg_replace_callback('/<\/?([^>\s]+)[^>]*>/i', function ($matches) use (&$allowed_tags) {
		return in_array(strtolower($matches[1]),$allowed_tags)?$matches[0]:'';
	},$html);
	return strip_tags($rhtml);
}

function arcadeFilterVar($string)
{
	// do not abuse the language api for your game list
	return parse_bbc(arcadeUtf8tohtml(arcadeStripTags(hex2bin(str_replace('efbfbd', '', bin2hex($string))))));
}

?>