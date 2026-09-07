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
//   This file handles the download option for the arcade

function ArcadeDownload($rom = 0)
{
	global $db_prefix, $smcFunc, $context, $boardurl, $arcadeModSettings, $user_info, $txt, $boarddir;

	$arcadeModSettings['pdl_DownMax'] = !empty($arcadeModSettings['pdl_DownMax']) ? (int)$arcadeModSettings['pdl_DownMax'] : 0;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : $rom;
	$action = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : 'arcade';
	$gameData = array();
	$checkIp = !empty($user_info['ip']) ? trim($user_info['ip']) : (!empty($user_info['ip2']) ? trim($user_info['ip2']) : arcade_get_client_ip());
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$context['html_headers'] .= '
	<meta http-equiv="Content-Type" content="text/html; charset=utf8_unicode_ci" />';
	if (empty($arcadeModSettings['gamesUrl']))
		$main = empty($rom) ? 'Games' : 'ArcadeRetroArch/roms';
	else
	{
		$boardurlHttp = stripos($boardurl, 'https') === false && stripos($arcadeModSettings['gamesUrl'], 'https') !== false ? str_ireplace('http', 'https', $boardurl) : (stripos($boardurl, 'https') !== false && stripos($arcadeModSettings['gamesUrl'], 'https') === false ? str_ireplace('https', 'http', $boardurl) : $boardurl);
		$main = preg_replace('~' . $boardurlHttp . '/~', '', (empty($rom) ? $arcadeModSettings['gamesUrl'] : 'ArcadeRetroArch/roms'));
	}

	if (!$user_info['is_guest'])
	{
		$arcadeSettings = loadMyArcadeSettings($user_info['id']);
		$type = !empty($arcadeSettings['archive_type']) ? $arcadeSettings['archive_type'] : 0;
	}
	else
		$type = isset($_SESSION['arcade_download_type']) ? $_SESSION['arcade_download_type'] : (!empty($arcadeModSettings['arcade_gz']) ? (int)$arcadeModSettings['arcade_gz'] : 0);

	switch($type)
	{
		case 0:
			$compress = class_exists('ZipArchive') ? 'zip' : 'tar';
			break;
		case 1:
			$compress = 'tar';
			break;
		case 2:
			$compress = 'tar.gz';
			break;
		case 3:
			$compress = 'rar';
			break;
		default:
			$compress = 'tar';
	}

	if (!empty($rom))
		$compress = 'zip';

	// if zipArchive class is not enabled then load the backup tar & rar classes
	if (!class_exists('ZipArchive') || $compress !== 'zip')
	{
		if (is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'))
		{
			if ($compress != 'tar' && $compress != 'tar.gz')
			{
				// check if rar package is available
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				{
					if (!empty($arcadeModSettings['arcadeDownloadWinRarDir']) && is_dir($arcadeModSettings['arcadeDownloadWinRarDir']))
						@chdir($arcadeModSettings['arcadeDownloadWinRarDir']);
					elseif (is_dir("\Program Files (x86)\WinRAR"))
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

			if (!$checkRar && $compress == 'rar' && version_compare(phpversion(), '5.5.10', '>'))
			{
				// fall back to tar if something is awry
				$compress = 'tar';
			}
		}
	}

	$pdl_arrayx = array(
		'pdl_gameid',
		'download_count',
		'download_disable',
		'latest_day',
		'latest_year',
		'permission',
		'report_id',
		'report_day',
		'report_year',
		'user_id'
	);

	$id_of_game = !empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0;
	if (empty($id_of_game)) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_nogame', false);
	}
	$search1 = 'game.id_game ='. $id_of_game;
	if (!$user_info['is_guest'])
	{
		$search2 = 'id_member ='. $user_info['id'];
		$member = $user_info['id'];
	}
	else
	{
		$search2 = 0;
		$member = 0;
	}

	/* Clear the game archives library if set */
	if (!empty($arcadeModSettings['arcadeDisableArchive']))
	{
		if ($arcadeModSettings['arcadeDisableArchive'] == 1)
			deleteArchives('games_download/', $empty = "true");
	}
	/* Check if download is enabled */
	$arcadeModSettings['arcadeEnableDownload'] = empty($arcadeModSettings['arcadeEnableDownload']) ? false : true;

	if (empty($arcadeModSettings['arcadeEnableDownload']) && !allowedTo('arcade_admin')) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_disable', false);
	}
	/* Check number of posts */
	if (isset($arcadeModSettings['arcadeDownPost']))
	{
		if (($user_info['posts'] < $arcadeModSettings['arcadeDownPost']) && !allowedTo('arcade_admin')) {
			arcadeClearSession();
			$txt['pdl_error3'] = fatal_lang_error( 'pdl_error_post', false);
		}
	}

	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_directory, game.game_file, pdl.latest_day, pdl.latest_year, pdl.permission, rep.report_day, game.id_cat, game.game_rating, game.description, game.internal_name, game.js_insertion, category.cat_dl, category.cat_js, game.cover_icon,
		rep.report_year, rep.user_id, rep.report_id, rep.report_reason, rep.download_count, rep.download_disable, game.thumbnail, game.thumbnail_small, game.extra_data, game.submit_system, game.enabled, game.score_type, game.help, game.download, game.rom_flag, game.rom_system
		FROM {db_prefix}arcade_games AS game
		LEFT JOIN {db_prefix}arcade_pdl1 AS pdl ON (pdl.id_member = {int:member})
		LEFT JOIN {db_prefix}arcade_pdl2 AS rep ON (rep.pdl_gameid = {int:search})
		LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
		WHERE ' . $search1 .'
		ORDER BY game.id_game
		LIMIT 1',
		array('search' => $id_of_game, 'member' => $member)
	);

	while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
	{
		foreach ($pdl_arrayx as $pdlx)
			if (empty($gameInfo[$pdlx]))
				$gameInfo[$pdlx] = 0;

		$rom = !empty($gameInfo['rom_flag']) ? 1 : 0;
		$check = !empty($gameInfo['game_file']) && strlen($gameInfo['game_file']) > 9 && substr($gameInfo['game_file'], -10) == 'index.html' ? true : (!empty($gameInfo['game_file']) && strlen($gameInfo['game_file']) > 8 && substr($gameInfo['game_file'], -9) == 'index.php' ? true : false);
		$subSystem = !empty($gameInfo['submit_system']) && empty($rom) ? $gameInfo['submit_system'] : (!empty($gameInfo['rom_system']) ? $gameInfo['rom_system'] : '');
		$internal = !empty($gameInfo['internal_name']) ? $gameInfo['internal_name'] : '';
		$phpFilex = $check && $subSystem == 'html52' ? $internal . '.php' : (!empty($gameInfo['game_file']) ? str_replace('.swf', '.php', $gameInfo['game_file']) : 'generated_file.php');
		$phpFilex = preg_replace('"\.(htm|html)$"', '.php', $phpFilex);
		$romExt = !empty($gameInfo['game_file']) && !empty($rom) ? mb_substr($gameInfo['game_file'], strrpos($gameInfo['game_file'], '.') + 1) : '';
		$romGameName = !empty($gameInfo['game_file']) && !empty($rom) ? mb_substr($gameInfo['game_file'], 0, -mb_strlen('.' . $romExt)) : 'generated_file.php';
		$phpFilex = empty($rom) ? $phpFilex : (!empty($internal) ? $internal . '.php' : $romGameName);
		$extra = !empty($gameInfo['extra_data']) && arcadeIsSerialized($gameInfo['extra_data']) ? arcade_safe_unserialize($gameInfo['extra_data']) : array();
		$gameData = array(
			'id_game' => !empty($gameInfo['id_game']) ? $gameInfo['id_game'] : 0,
			'enabled' => !empty($gameInfo['enabled']) ? $gameInfo['enabled'] : 0,
			'enable_download' => !empty($gameInfo['download']) && empty($gameInfo['cat_dl']) ? 1 : 0,
			'js_insertion' => !empty($gameInfo['js_insertion']) ? $gameInfo['js_insertion'] : (!empty($gameInfo['cat_js']) ? 1 : 0),
			'score_type' => !empty($gameInfo['score_type']) ? $gameInfo['score_type'] : 0,
			'gamename' => !empty($gameInfo['game_name']) ? $gameInfo['game_name'] : '',
			'internal_name' => !empty($gameInfo['internal_name']) ? $gameInfo['internal_name'] : '',
			'php_file' => !empty($gameInfo['internal_name']) ? $gameInfo['internal_name'] . '.php' : '',
			'help' => !empty($gameInfo['help']) ? stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $gameInfo['help'])) : '',
			'gkeys' => !empty($gameInfo['help']) ? stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $gameInfo['help'])) : '',
			'description' => !empty($gameInfo['description']) ? stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $gameInfo['description'])) : '',
			'game_directory' => !empty($gameInfo['game_directory']) ? $gameInfo['game_directory'] : '',
			'game_file' => !empty($gameInfo['game_file']) ? $gameInfo['game_file'] : 'generated_file.swf',
			'gamephp' => $phpFilex,
			'rom_flag' => !empty($rom) ? 1 : 0,
			'latest_day' => !empty($gameInfo['latest_day']) ? $gameInfo['latest_day'] : '',
			'latest_year' => !empty($gameInfo['latest_year']) ? $gameInfo['latest_year'] : '',
			'permission' => !empty($gameInfo['permission']) ? (int)$gameInfo['permission'] : 0,
			'report_day' => !empty($gameInfo['report_day']) ? $gameInfo['report_day'] : '',
			'report_year' => !empty($gameInfo['report_year']) ? $gameInfo['report_year'] : '',
			'user_id' => !empty($gameInfo['user_id']) ? $gameInfo['user_id'] : 0,
			'report_id' => !empty($gameInfo['report_id']) ? $gameInfo['report_id'] : 0,
			'report_reason' => !empty($gameInfo['report_reason']) ? $gameInfo['report_reason'] : '',
			'thumbnail' => !empty($gameInfo['thumbnail']) ? $gameInfo['thumbnail'] : '',
			'thumbnail_small' => !empty($gameInfo['thumbnail_small']) ? $gameInfo['thumbnail_small'] : '',
			'cover_icon' => !empty($gameInfo['cover_icon']) ? $gameInfo['cover_icon'] : '',
			'extra_data' => $extra,
			'id_cat' => !empty($gameInfo['id_cat']) ? $gameInfo['id_cat'] : 0,
			'submit_system' => !empty($gameInfo['submit_system']) ? $gameInfo['submit_system'] : '',
			'rom_system' => !empty($gameInfo['rom_system']) ? $gameInfo['rom_system'] : '',
			'game_rating' => !empty($gameInfo['game_rating']) ? $gameInfo['game_rating'] : 0,
			'gamefile_name' => !empty($internal) ? ArcadeSpecialChars(trim($internal), 'name') : '',
			'gamesave' => 'games_download',
			'dl_disable' => !empty($gameInfo['download_disable']) ? $gameInfo['download_disable'] : 0,
			'dl_count' => ((int)$gameInfo['download_count'] + 1),
			'report_user' => !empty($gameInfo['user_id']) ? (int)$gameInfo['user_id'] : 0,
		);
	}

	$smcFunc['db_free_result']($request);

	if (empty($gameData)) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_db', false);
	}
	if (empty($gameData['enable_download']) && !allowedTo('arcade_admin')) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_download_disable', false);
	}
	if (!empty($gameData['rom_flag']) && !allowedTo('arcade_view_retro_arch')) {
		arcadeClearSession();
		fatal_lang_error('cannot_arcade_download_gamefile', false);
	}
	if (!allowedTo('arcade_download') && empty($gameData['rom_flag']))
	{
		arcadeClearSession();
		fatal_lang_error('pdl_error_perm', false);
		return 'Unauthorized';
		exit();
	}
	if (!allowedTo('arcade_download_rom') && !empty($gameData['rom_flag']))
	{
		arcadeClearSession();
		fatal_lang_error('pdl_error_perm', false);
		return 'Unauthorized';
		exit();
	}

	$exlink = !empty($gameData['extra_data']['external_link']) ? $gameData['extra_data']['external_link'] : '';
	$prefix = !empty($gameData['submit_system']) && $gameData['submit_system'] == 'html5' ? 'html5_' : 'game_';
	$prefix = $gameData['submit_system'] == 'html53' ? '' : $prefix;
	$prefix = !empty($rom) ? 'rom_' : $prefix;

	// fix file name due to possible sub-directory paths
	list($xx, $gameFilePath, $html5PHP) = array(1, explode('/', str_ireplace(array('.html', '.swf', '.php'), '', $gameData['game_file'])), '');
	$gameFilePath = str_replace('\\', '/', $gameFilePath);
	if (count($gameFilePath) > 1)
	{
		foreach ($gameFilePath as $path)
		{
			$gameData['gamefile_name'] = $xx < count($gameFilePath) ? str_ireplace($path, '', $gameData['gamefile_name']) : $gameData['gamefile_name'];
			$xx++;
		}

		if (empty($gameData['gamefile_name']))
			$gameData['gamefile_name'] = empty($gameData['gamefile_name']) && !empty($internal) ? ArcadeSpecialChars(trim($internal), 'name') : '';
	}

	if (empty($gameData['gamefile_name']) && empty($internal)) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_db', false);
	}
	elseif (empty($gameData)) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_db', false);
	}

	/* Check if zip, tar or rar file already exists and if yes download - if not zip and download */
	$url_array = array(
		$boarddir . '/' . $gameData['gamesave'] . '/' . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $gameData['gamefile_name'] . '.tar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $gameData['gamefile_name'] . '.rar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $gameData['gamefile_name'] . '.tar.gz',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.tar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.rar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.tar.gz',
	);

	/* Check to see if the Administrator has manually denied downloading for this game - password is the file prefix */
	$deny = empty($arcadeModSettings['arcadeDownPass']) ? 'DENY_' : $arcadeModSettings['arcadeDownPass'] . '_';
	$url_skip = array(
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $gameData['gamefile_name'] . '.tar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $gameData['gamefile_name'] . '.rar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $gameData['gamefile_name'] . '.tar.gz',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $prefix . $gameData['gamefile_name'] . '.zip',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $prefix . $gameData['gamefile_name'] . '.tar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $prefix . $gameData['gamefile_name'] . '.rar',
		$boarddir . '/' . $gameData['gamesave'] . '/' . $deny . $prefix . $gameData['gamefile_name'] . '.tar.gz',
	);
	foreach ($url_skip as $skip)
	{
		if ((file_exists($skip)) && !allowedTo('arcade_admin')) {
			arcadeClearSession();
			fatal_lang_error('pdl_error_dl', false);
		}
	}

	/* Check to see if downloading is denied from the game settings  */
	if ($gameData['dl_disable'] > 0  && !allowedTo('arcade_admin')) {
		arcadeClearSession();
		fatal_lang_error('pdl_error_dl', false);
	}

	/* Check for daily download limit  */
	$max = (int)$arcadeModSettings['pdl_DownMax'] - 1;
	$request = $smcFunc['db_query']('', '
		SELECT id_group, download_limit
		FROM {db_prefix}arcade_membergroups
		WHERE id_group > -2',
		array(
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		if (isset($row['download_limit']))
			$membergroupLimits['group' . $row['id_group']] = !empty($row['download_limit']) ? $row['download_limit'] : 0;
	}
	$smcFunc['db_free_result']($request);

	if (!empty($user_info['is_guest']) && isset($membergroupLimits['group-1']))
		$max = $membergroupLimits['group-1'] - 1;
	else {
		// whichever group for the user has the highest limit
		foreach ($user_info['groups'] as $group) {
			if (isset($membergroupLimits['group' . $group]))
				$newMax = $membergroupLimits['group' . $group] -1;
			$max = isset($newMax) && $newMax > $max ? $newMax : $max;
			$max = isset($newMax) && $newMax == 0 ? 0 : $max;
		}
	}

	if ($max > -1) {
		$myzone = date("e");
		date_default_timezone_set('GMT');
		list($count, $day, $year, $user_info['count'], $user_info['day'], $user_info['year'], $membergroupLimits) = array(1, date("z"), date("Y"), 0, false, false, array());

		if (!$user_info['is_guest'] && $search2 !== 0) {
			$request = $smcFunc['db_query']('', '
				SELECT game.id_member, game.count, game.year, game.day
				FROM {db_prefix}arcade_pdl1 AS game
				WHERE ' . $search2 .'
				ORDER BY game.id_member
				LIMIT 1',
				array('search' => $gameData['id_game'],)
			);
		}
		else {
			$request = $smcFunc['db_query']('', '
				SELECT game.online_ip, game.year, game.day, game.count
				FROM {db_prefix}arcade_guest_extra_data AS game
				WHERE game.online_ip LIKE {string:ip}
				ORDER BY game.online_ip
				LIMIT 1',
				array('ip' => $checkIp)
			);
		}

		while ($gameInfo = $smcFunc['db_fetch_assoc']($request))
		{
			if ((int)$gameInfo['count'] > 0)
				list($user_info['count'], $user_info['day'], $user_info['year']) = array((int)$gameInfo['count'], $gameInfo['day'], $gameInfo['year']);
			else
				list($user_info['count'], $user_info['day'], $user_info['year']) = array(0, $day, $year);
		}
		$smcFunc['db_free_result']($request);
		/* Check date */
		if ($day == $user_info['day'] && $year == $user_info['year'])
		{
			$txt['pdl_error_max'] = sprintf($txt['pdl_error_max'], ($max+1));
			if  (($user_info['count'] > $max) && !allowedTo('arcade_admin')) {
				date_default_timezone_set($myzone);
				arcadeClearSession();
				fatal_lang_error('pdl_error_max', false);
			}
			else
				$count = (int)$user_info['count'] + 1;
		}

		/* Update user or guest count */
		if (!$user_info['is_guest'] && $search2 !== 0)
			createxpdlval1($user_info['id'], $count, $year, $day, $gameData['latest_year'], $gameData['latest_day'], $gameData['permission']);
		elseif (!empty($checkIp))
			createxpdlguestval1($checkIp, $count, $year, $day);
		/* reset time zone back to original setting  */
		date_default_timezone_set($myzone);
	}

	/* Update download count */
	createxpdlval2($gameData['id_game'], $gameData['gamename'], $gameData['report_day'], $gameData['report_year'], $gameData['report_user'], $gameData['report_id'], $gameData['report_reason'], $gameData['dl_count'], $gameData['dl_disable'], $rom);
	foreach ($url_array as $url)
	{
		if (file_exists($url))
		{
			$boarddir2 = str_replace('\\', '/', $boarddir);
			$dirs1 = explode('/', $boarddir2);
			$dirs2 = explode('/', parse_url($url, PHP_URL_PATH));
			$count = count($dirs1) -1;

			if ($dirs1[$count] == $dirs2[1])
				$a = array_pop($dirs1);

			$filex = implode('/', $dirs1) . parse_url($url, PHP_URL_PATH);
			if (file_exists($filex))
				unlink($filex);
			if(file_exists($filex . '.tmp'))
				unlink($filex . '.tmp');
		}
	}

	clearstatcache();
	// It's not in your download library, so let's add it...
	$gameDataPath = '';
	$directoryToZip1 = $main . '/' . $gameData['game_directory'];
	$directoryToZip2 = 'arcade/gamedata/' . $gameData['gamefile_name'];
	list($gamefiles, $gamedatafiles, $gfiles, $gdatafiles, $zipName, $ignore, $files) = array(array(), array(), array(), array(), $boarddir . '/games_download/' . $gameData['gamefile_name'], '', array());
	$currentGameFile = basename($gameData['game_file']);
	$ignorePHP = substr($currentGameFile, -5) == '.html' ? substr($currentGameFile, -5) . '.php' : (substr($currentGameFile, -4) == '.swf' ? substr($currentGameFile, -4) . '.php' : 'ignore.php');
	$ignorePHP = ltrim($ignorePHP, 'game_');
	$ignoreArray = array(
		rtrim($arcadeModSettings['gamesDirectory'], '/') . '/' . (!empty($gameData['game_directory']) ? $gameData['game_directory'] . '/' : '') . (in_array($gameData['submit_system'], array('ibp', 'ibp2', 'ibp3', 'ibp32')) ? $gameData['gamephp'] : 'ignorefile.php'),
		rtrim($arcadeModSettings['gamesDirectory'], '/') . '/' . (!empty($gameData['game_directory']) ? $gameData['game_directory'] . '/' : '') . rtrim($gameData['gamephp'], '.php') . '.xml',
		rtrim($arcadeModSettings['gamesDirectory'], '/') . '/' . (!empty($gameData['game_directory']) ? $gameData['game_directory'] . '/' : '') . 'game-info.xml',
	);
	if (strpos($gameData['submit_system'], 'html5') !== false && $gameData['submit_system'] != 'html5')
	{
		$html5PHP = $gameData['php_file'];
		$ignorePHP = $gameData['php_file'];
		$ignoreArray += array(
			rtrim($arcadeModSettings['gamesDirectory'], '/') . '/' . (!empty($gameData['game_directory']) ? $gameData['game_directory'] . '/' : '') . $ignorePHP,
		);
	}

	if (is_dir($boarddir . '/' . $directoryToZip1))
	{
		// no game directory?
		if (empty($gameData['game_directory']))
		{
			$phpFilex = !empty($phpFilex) ? $phpFilex : substr($gameData['game_file'], -4) . '.php';
			if ((!empty($gameData['thumbnail_small'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['thumbnail_small']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['thumbnail_small'];
			if ((!empty($gameData['thumbnail'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['thumbnail']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['thumbnail'];
			if ((!empty($gameData['cover_icon'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['cover_icon']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['cover_icon'];
			if ((!empty($gameData['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_file']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_file'];
			if ((!empty($gameData['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $phpFilex))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $phpFilex;
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2) && strpos($gameData['submit_system'], 'html5') === false && empty($rom))
			{
				$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);
				$gameDataPath = $boarddir . '/' . $directoryToZip2;
			}

			$gamedatafiles = array_diff(array_unique($gdatafiles), array('.', '..', $ignorePHP));
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;

			$ignore = $boarddir . '/' . $main;
		}
		// inside a gamepack or unique directory?
		elseif ($gameData['game_directory'] !== $gameData['gamefile_name'])
		{
			$dirs = str_replace('\\', '/', $gameData['game_directory']);
			$dirsArray = explode('/', $dirs);

			// unique default directory?
			if ($gameData['game_directory'] == $smcFunc['strtolower']($gameData['gamefile_name']))
				$gamefiles = arcade_scan_dir($boarddir . '/' . $directoryToZip1, $ignoreArray, $html5PHP, $compress);
			// smfv1 gamepack directory?
			elseif($gameData['submit_system'] == 'v1game' && count($dirsArray) < 2)
			{
				$phpFilex = !empty($phpFilex) ? $phpFilex : substr($gameData['game_file'], -4) . '.php';
				if ((!empty($gameData['thumbnail_small'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail_small']))
				$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail_small'];
				if ((!empty($gameData['thumbnail'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail']))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['thumbnail'];
				if ((!empty($gameData['cover_icon'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['cover_icon']))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['cover_icon'];
				if ((!empty($gameData['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['game_file']))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $gameData['game_file'];
				if ((!empty($gameData['game_file'])) && file_exists($boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $phpFilex))
					$gamefiles[] = $boarddir . '/' . $main . '/' . $gameData['game_directory'] . '/' . $phpFilex;
				// gamedata files?
				if (is_dir($boarddir . '/' . $directoryToZip2))
					$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);

				$gamedatafiles = array_diff($gdatafiles, array('.', '..', $ignorePHP));
				foreach($gamedatafiles as $key => $value)
					$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;
			}
			else
				$gamefiles = arcade_scan_dir($boarddir . '/' . $main . '/' . $gameData['game_directory'], $ignoreArray, $html5PHP, $compress);

			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2) && strpos($gameData['submit_system'], 'html5') === false)
			{
				$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);
				$gameDataPath = $boarddir . '/' . $directoryToZip2;
			}

			$gamedatafiles = array_diff($gdatafiles, array('.', '..', $ignorePHP));
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;

			$ignore = $boarddir . '/' . $directoryToZip1;
		}
		// proper default subdirectory
		else
		{
			// gamedata files?
			if (is_dir($boarddir . '/' . $directoryToZip2) && strpos($gameData['submit_system'], 'html5') === false && empty($rom))
			{
				$gdatafiles = scandir($boarddir . '/' . $directoryToZip2);
				$gameDataPath = $boarddir . '/' . $directoryToZip2;
			}

			$gamedatafiles = array_diff($gdatafiles, array('.', '..', $ignorePHP));
			foreach($gamedatafiles as $key => $value)
				$gamedatafiles[$key] = $boarddir . '/' . $directoryToZip2 . '/' . $value;

			// the unique game folder may contain subfolders for some games
			$gamefiles = arcade_scan_dir($boarddir . '/' . $directoryToZip1, $ignoreArray, $html5PHP, $compress);
			$ignore = $boarddir . '/' . $directoryToZip1;
		}

		$tmpfiles = arcade_game_down($gameData, $boarddir . '/' . $gameData['gamesave'], $rom);
		$tmpfile = $tmpfiles[0];
		if (count($tmpfiles) > 1) {
			$gamefiles[] = $tmpfiles[1];
			$gameData['game_file'] = basename($tmpfiles[1]);
		}

		// search 2 paths of depth beyond main directory to omit possible duplicate set up files
		foreach ($gamefiles as $key => $filexx)
		{
			if (stripos($filexx, 'game-info.xml') !== false || stripos($filexx, $gameData['gamephp']) !== false)
			{
				$filexx = str_replace($boarddir . '/' . $main . '/', '', $filexx);
				$dir = str_replace('\\', '/', dirname($filexx));
				$dir = str_replace('//', '/', $dir);
				$numPaths = explode('/', $dir);

				if (count($numPaths) < 3)
					unset($gamefiles[$key]);
			}
			elseif (stripos($filexx, $gameData['php_file']) !== false && stripos($gameData['submit_system'], 'html5') !== false && $gameData['submit_system'] != 'html5')
			{
				$filexx = str_replace($boarddir . '/' . $main . '/', '', $filexx);
				$dir = str_replace('\\', '/', dirname($filexx));
				$dir = str_replace('//', '/', $dir);
				$numPaths = explode('/', $dir);

				if (count($numPaths) < 3)
					unset($gamefiles[$key]);
			}
		}

		// create compressed archive
		arcadeCompressArchive($gamefiles, $gamedatafiles, $gameDataPath, $gameData['internal_name'], $boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress, $ignore, $ignoreArray, $tmpfile, $gameData['php_file'], true, $rom, $compress);

		if (!empty($tmpfile) && file_exists($tmpfile)) {
			unlink($tmpfile);
		}
		clearstatcache();
		$chunksize = 8 * (1024 * 1024);
		$size = file_exists($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress) ? filesize($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress) : 0;
		if (is_file($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress) && file_exists($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress) && substr($compress, -3) == '.gz')
		{
			sleep(1);
			@set_time_limit(0);
			@ob_end_clean();
			@ob_start();
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $prefix . $gameData['gamefile_name'] . '.' . $compress . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress));
            if ($size > $chunksize) {
				$handle = fopen($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress, 'rb');
				$buffer = '';
				while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {
					$buffer = fread($handle, $chunksize);
					print $buffer;
					ob_flush();
					flush();
				}
				fclose($handle);
				if (!empty($ajax)) {
					echo "<script>window.close();</script>";
				}
				exit;
            }
			else {
				while (ob_get_level())
					@ob_end_clean();
				@flush();
				readfile($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress);
				exit;
			}
		}
		elseif (is_file($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress) && file_exists($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress))
		{
			sleep(1);
			@set_time_limit(0);
			@ob_end_clean();
			@ob_start();
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $prefix . $gameData['gamefile_name'] . '.' . $compress . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress));
            if ($size > $chunksize) {
				$handle = fopen($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress, 'rb');
				$buffer = '';
				while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {
					$buffer = fread($handle, $chunksize);
					print $buffer;
					ob_flush();
					flush();
				}
				fclose($handle);
				if (!empty($ajax)) {
					echo "<script>window.close();</script>";
				}
				exit;
            }
			else {
				@ob_end_flush();
				@flush();
				if (file_exists($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress))
					readfile($boarddir . '/' . $gameData['gamesave'] . '/' . $prefix . $gameData['gamefile_name'] . '.' . $compress);
				exit;
			}
		}
		else {
			arcadeClearSession();
			fatal_lang_error('pdl_zipfile2', false);
			exit;
		}

		return true;
	}
	arcadeClearSession();
	fatal_lang_error('pdl_error_db', false);
	exit;
}

function arcade_game_down($data, $filepath, $rom = 0)
{
	global $arcadeModSettings, $arcade_version, $txt;
	$word = preg_split('//', 'abcdefghijklmnopqrstuvwxyz_1234567890', -1);
	shuffle($word);
	$fileDate = !empty($rom) ? 'rom_' : '';
	$controls = 'GAME_CONTROL_MOUSE';
	$file = implode('', array_slice($word, 0, 16));
	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$stype = array_merge(array('auto'), $classicGameTypes);
	$romtypes = array_keys($txt['arcade_select_gametype_rom']);
	if (empty($rom)) {
		$savetype = !empty($data['submit_system']) && in_array($data['submit_system'], $stype) ? $data['submit_system'] : 'v1game';
		$romtype = 'none';
	}
	else {
		$romtype = !empty($data['rom_system']) && in_array($data['rom_system'], $romtypes) && $data['rom_system'] != 'all' ? $data['rom_system'] : 'none';
		$savetype = 'rom';
	}

	$data['description'] = stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $data['description']));
	$data['description'] = stripslashes($data['description']);
	$data['description'] = str_replace("&rsquo;", "'", htmlspecialchars_decode(mb_convert_encoding($data['description'], "HTML-ENTITIES", "UTF-8")));
	$data['description'] = htmlspecialchars_decode($data['description']);
	$data['help'] = stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $data['help']));
	$data['help'] = stripslashes($data['help']);
	$data['help'] = str_replace("&rsquo;", "'", htmlspecialchars_decode(mb_convert_encoding($data['help'], "HTML-ENTITIES", "UTF-8")));
	$data['help'] = htmlspecialchars_decode($data['help']);

	// do we really need these alias names on everyone's arcade?
	foreach (array('_origon', '_masodo', '_jvh5') as $trailblazer)
		$data['gamename'] = strlen(mb_substr($data['gamename'], 0, -strlen($trailblazer))) > 0 && mb_substr(mb_strtolower($data['gamename']), -strlen($trailblazer)) == $trailblazer ? mb_substr($data['gamename'], 0, -strlen($trailblazer)) : $data['gamename'];

	$gameinfo = array(
		'active' => $data['enabled'],
		'bgcolor' => !empty($data['extra_data']['background_color']) ? arcade_hex_to_color($data['extra_data']['background_color']) : '',
		'gcat' => $data['id_cat'],
		'gheight' => !empty($data['extra_data']['height']) ? $data['extra_data']['height'] : 500,
		'gwidth' => !empty($data['extra_data']['width']) ? $data['extra_data']['width'] : 500,
		'exlink' => !empty($data['extra_data']['external_link']) ? $data['extra_data']['external_link'] : '',
		'gkeys' => htmlspecialchars($data['help'], ENT_QUOTES | ENT_HTML5),
		'gname' => $data['internal_name'],
		'gdir' => $data['game_directory'],
		'gtitle' => htmlspecialchars($data['gamename'], ENT_QUOTES | ENT_HTML5),
		'gwords' => htmlspecialchars($data['description'], ENT_QUOTES | ENT_HTML5),
		'object' => htmlspecialchars($data['description'], ENT_QUOTES | ENT_HTML5),
		'snggame' => $data['score_type'],
		'savetype' => $savetype,
		'romtype' => $romtype,
		'rom_flag' => !empty($data['rom_flag']) ? 1 : 0,
		'date' => gmdate('D, d M Y H:i:s \G\M\T', time()),
		'thumbnail' => $data['thumbnail'],
		'thumbnail_small' => $data['thumbnail_small'],
		'cover_icon' => $data['cover_icon'],
		'file' => $data['game_file'],
		'flash_version' => !empty($data['extra_data']['flash_version']) ? $data['extra_data']['flash_version'] : 0,
		'type' => (!empty($data['extra_data']['type'])) &&  $data['extra_data']['type'] == 'fullscreen' ? $data['extra_data']['type'] : 'normal',
		'score_type' => !empty($data['score_type']) ? (int)$data['score_type'] : 0,
		'js_insertion' => $data['js_insertion'],
		'force_ibp' => !empty($data['force_php']) ? 1 : 0,
	);
	$thumbnail = !empty($data['thumbnail_small']) ? $data['thumbnail_small'] : $data['thumbnail'];
	$thumbnailNum = $thumbnail == $gameinfo['gname'] . '1' || $thumbnail == $gameinfo['gname'] . '2' ? mb_substr($thumbnail, -1) : '0';
	$covericon = !empty($data['cover_icon']) ? htmlspecialchars(str_replace(array("'", '"'), "", $data['cover_icon']), ENT_QUOTES | ENT_HTML5) : '';
	$helpArray = array(
		'arcade_game_control_mouse_key' => 'GAME_CONTROL_KEYBOARD_MOUSE',
		'arcade_game_control_mouse' => 'GAME_CONTROL_MOUSE',
		'arcade_game_control_key' => 'GAME_CONTROL_KEYBOARD',
		'arcade_game_control_mouse_touch' => 'GAME_CONTROL_MOUSE_TOUCH',
		'arcade_game_control_key_touch' => 'GAME_CONTROL_KEYBOARD_TOUCH'
	);
	foreach($helpArray as $key => $control)
	{
		if (!empty($txt[$key]) && stripos($data['help'], $txt[$key]) !== false)
		{
			$controls = $control;
			break;
		}
	}

	if (in_array($savetype, array('ibp', 'ibp2', 'ibp3', 'ibp32', 'html52')) || !empty($gameinfo['force_ibp']) || !empty($rom))
	{
		$infofile = '<?php
/*---------------------------------------------------------------*/
/* ' . $txt['arcade_download_php']['source'] . ' ' . $arcade_version . '
/* ' . $txt['arcade_download_php'][$fileDate . 'date'] . ' ' . $gameinfo['date'] . '
/*---------------------------------------------------------------*/
$config = array(
	\'active\'	=> \'' . $gameinfo['active'] . '\',
	\'bgcolor\'	=> \'' . $gameinfo['bgcolor'] . '\',
	\'gcat\'		=> \'' . $gameinfo['gcat'] . '\',
	\'gkeyimg\'	=> \'' . $thumbnailNum . '\',
	\'covericon\'	=> \'' . $covericon . '\',
	\'js_insertion\'	=> \'' . $gameinfo['js_insertion'] . '\',
	\'gheight\'	=> \'' . $gameinfo['gheight'] . '\',
	\'gwidth\'	=> \'' . $gameinfo['gwidth'] . '\',
	\'gkeys\'		=> \'' . $gameinfo['gkeys'] . '\',
	\'gname\'		=> \'' . $gameinfo['gname'] . '\',
	\'gtitle\'	=> \'' . $gameinfo['gtitle'] . '\',
	\'gwords\'	=> \'' . $gameinfo['gwords'] . '\',
	\'object\'	=> \'' . $gameinfo['gwords'] . '\',
	\'snggame\'	=> \'' . $gameinfo['snggame'] . '\',
	\'gtype\'		=> \'' . (!empty($gameinfo['type']) &&  $gameinfo['type'] == 'fullscreen' ? 'fullscreen' : 'normal') . '\',
	\'romtype\'	=> \'' . $gameinfo['romtype'] . '\',
	\'romflag\'	=> \'' . $gameinfo['rom_flag'] . '\',
	\'scoring\'	=> \'' . $gameinfo['score_type'] . '\',
	\'savetype\'	=> \'' . $gameinfo['savetype'] . '\',' . (stripos($gameinfo['savetype'], 'html5') !== false && empty($rom) ? '
	\'html5\'		=> \'1\',' : '
	\'html5\'		=> \'0\',') . (!empty($rom) ? '
	\'exlink\'	=> \'' . $gameinfo['exlink'] . '\',' : '') . '
);
?>';
		$ext = '.php';
	}
	elseif ($savetype == 'html53')
	{
		$infofile = '<?php
/*---------------------------------------------------------------*/
/* ' . $txt['arcade_download_php']['source'] . ' ' . $arcade_version . '
/* ' . $txt['arcade_download_php'][$fileDate . 'date'] . ' ' . $gameinfo['date'] . '
/*---------------------------------------------------------------*/
if (!defined(\'IN_PHPBB\') || !defined(\'IN_PHPBB_ARCADE\'))
{
	exit;
}
$game_data = array(
	\'game_height\'		=>	' . $gameinfo['gheight'] . ',
	\'game_width\'		=>	' . $gameinfo['gwidth'] . ',
	\'game_control_desc\'	=>	\'' . $gameinfo['gkeys'] . '\',
	\'game_control\'		=>	' . $controls . ',
	\'game_scorevar\'		=>	\'' . $gameinfo['gname'] . '\',
	\'game_image\'		=>	\'' . $thumbnail . '\',
	\'covericon\'		=> \'' . $covericon . '\',
	\'game_name\'			=>	\'' . $gameinfo['gtitle'] . '\',
	\'game_desc\'			=>	\'' . $gameinfo['gwords'] . '\',
	\'game_scoretype\'	=>	' . (empty($gameinfo['snggame']) ? 'SCORETYPE_HIGH' : 'SCORETYPE_LOW') . ',
	\'game_save_type\'	=>	' . (substr($gameinfo['gname'], -2) == 'RA' ? 'PHPBB_RA_GAME' : 'PHPBBARCADE_GAME') . ',
	\'game_type\'			=>	GAME_TYPE_HTML5,
	\'js_insertion\'		=>	' . $gameinfo['js_insertion'] . ',
	\'game_inherit\'		=>	\'\',
	\'privacy_desc\'		=>	\'\',
	\'privacy_link\'		=>	\'\',
);
?>';
		$ext = '.php';
	}
	else
	{
		$infofile = "<!-- 	" . $txt['arcade_download_php']['source'] . " " . $arcade_version . "				-->
<!-- 	" . $txt['arcade_download_php'][$fileDate . 'date'] . " " . $gameinfo['date'] . "		-->";
		$infofile .= '
<game-info>
	<id>' . $gameinfo['gname'] . '</id>
	<name>' . $gameinfo['gtitle'] . '</name>
	<description>' . $gameinfo['gwords'] . '</description>
	<help>' . $gameinfo['gkeys'] . '</help>
	<thumbnail>' . $gameinfo['thumbnail'] . '</thumbnail>
	<thumbnail-small>' . $gameinfo['thumbnail_small'] . '</thumbnail-small>
	<covericon>' . $covericon . '</covericon>
	<file>' . $gameinfo['file'] . '</file>
	<scoring>' . $gameinfo['score_type'] . '</scoring>
	<js_insertion>' . $gameinfo['js_insertion'] . '</js_insertion>
	<submit>' . $gameinfo['savetype'] . '</submit>
	<flash>
		<version>' . $gameinfo['flash_version'] . '</version>
		<width>' . $gameinfo['gwidth'] . '</width>
		<height>' . $gameinfo['gheight'] . '</height>
		<type>' . (!empty($gameinfo['type']) &&  $gameinfo['type'] == 'fullscreen' ? 'fullscreen' : 'normal') . '</type>
		<bgcolor>' . $gameinfo['bgcolor'] . '</bgcolor>
	</flash>
</game-info>';
		$ext = '.xml';
	}

	$newGameFiles = array();
	if (is_dir($filepath) && !empty($file))
		$fp = fopen($filepath . '/' . $file . $ext, 'wb');
	if (!empty($fp)) {
		fwrite($fp, $infofile, strlen($infofile));
		fclose ($fp);
		$newGameFiles[] = $filepath . '/' . $file . $ext;
		if ((empty($gameinfo['file']) || !file_exists($filepath . '/' . $gameinfo['file'])) && !empty($gameinfo['exlink']) && arcadeCheckExternalArchiveExists($gameinfo['exlink']) && !empty($gameinfo['gdir']) && !empty($rom)) {
			$fp = fopen($arcadeModSettings['romGamesDirectory'] . '/' . $gameinfo['gdir'] . '/' . $gameinfo['gname'] . '.url', 'wb');
			fwrite($fp, "[InternetShortcut]\r\nURL=" . $gameinfo['exlink'] . "\r\n");
			fclose($fp);
			chmod($arcadeModSettings['romGamesDirectory'] . '/' . $gameinfo['gdir'] . '/' . $gameinfo['gname'] . '.url', 0644);
			$gameinfo['file'] = $gameinfo['gname'] . '.url';
			$newGameFiles[] = $arcadeModSettings['romGamesDirectory'] . '/' . $gameinfo['gdir'] . '/' . $gameinfo['gname'] . '.url';
		}
		clearstatcache();
		return $newGameFiles;
	}
	else
		return array('');
}

function arcadeCheckExternalArchiveExists($url)
{
    if(!filter_var($url, FILTER_VALIDATE_URL)){
        return false;
    }
    $curlInit = curl_init($url);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($curlInit);
    curl_close($curlInit);
    return $response ? true : false;
}

function arcade_hex_to_color($hex)
{
	//return sprintf("%x", ($hex[0] << 16) + ($hex[1] << 8) + $hex[2]);
	$hex = !empty($hex) && is_array($hex) ? $hex : (!empty($hex) && is_string($hex) ? arcade_convert_hex($hex) : array());
	$hex = array_map('intval', $hex);
	list($r, $g, $b) = $hex;
	list($r, $g, $b) = array(dechex($r), dechex($g), dechex($b));
	$r = strlen($r)<2 ? '0' . $r : $r;
	$g = strlen($g)<2 ? '0' . $g : $g;
	$b = strlen($b)<2 ? '0' . $b : $b;
    return $r . $g . $b;
}

function arcade_convert_hex($hex, $alpha = false)
{
	$hex = ltrim($hex, '#');
	$length   = strlen($hex);
	$rgb['0'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
	$rgb['1'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
	$rgb['2'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
	if (!empty($alpha)) {
		$rgb['3'] = $alpha;
	}
	return $rgb;
}

function arcade_scan_dir($dir, $ignore, $phpFile = '', $compress = 'zip')
{
	$root = scandir($dir);
	$result = array();
	$ignore = is_array($ignore) ? $ignore : array($ignore);
	foreach($root as $value)
	{
		if ($value === '.' || $value === '..' || in_array($value, $ignore))
			continue;
		elseif (!empty($phpFile) && $value == $phpFile)
			continue;

		if (stripos($compress, 'tar') === false && file_exists($dir . '/' . $value) && !is_dir($dir . '/' . $value))
		{
			$result[] = $dir . '/' . $value;
			continue;
		}
		elseif (file_exists($dir . '/' . $value) && stripos($compress, 'tar') !== false)
		{
			$result[] = $dir . '/' . $value;
			continue;
		}

		$check = arcade_scan_dir($dir . '/' . $value, $ignore, $phpFile, $compress);
		foreach ($check as $value)
			$result[] = $value;
	}

	return $result;
}

function arcade_create_zip($files = array(), $destination = '', $ignore = '', $ignoreArray, $tmpfile = '', $phpfile = '', $overwrite = false, $rom = 0)
{

	if(!empty($destination) && substr($destination, -4) != '.zip') {
		return false;
	}

	if(file_exists($destination) && !$overwrite)
		return false;

	list($valid_files, $pathParts) = array(array(), array());
	if(is_array($files))
	{
		foreach($files as $file)
		{
			$filey = str_replace('%20', ' ', $file);
			$filex = str_replace("\\", "/", $file);
			if (in_array($filex, $ignoreArray))
				continue;

			if(file_exists($file) && basename($file) != 'game-info.xml')
				$valid_files[] = $file;
			elseif(file_exists($filey) && basename($filey) != 'game-info.xml')
				$valid_files[] = $filey;
		}
	}

	sort($valid_files);
	if(count($valid_files))
	{
		if (file_exists($destination) && !is_dir($destination))
		{
			unlink($destination);
			clearstatcache();
		}
		if (file_exists($destination . '.tmp'))
		{
			unlink($destination . '.tmp');
			clearstatcache();
		}

		$zip = new ZipArchive();
		if($zip->open($destination, ZIPARCHIVE::CREATE) !== true)
			return false;

		foreach($valid_files as $file)
		{
			$pathinfo = pathinfo($file);
			$pathParts = explode('/', stripslashes(rtrim($pathinfo['dirname'], '/\\')));
			$count = count($pathParts);
			if ((!empty($pathParts[$count-2])) && $pathParts[$count-2] == 'gamedata' && empty($rom))
				$newfile = $pathParts[$count-2] . '/' . $pathParts[$count-1] . '/' . str_replace($ignore, '', basename($file));
			elseif(!empty($ignore))
				$newfile = str_replace($ignore . '/', '', $file);
			else
				$newfile = $file;

			if (empty($newfile))
				continue;

			$newfile = ltrim($newfile, "/\\");
			$newfile = str_replace('%20', ' ', $newfile);

			if (file_exists($file) && is_file($file)) {
				$zip->addFile($file, $newfile);
			}
		}


		$zip->close();
		if ($zip->open($destination) === TRUE && !empty($tmpfile) && @file_exists($tmpfile))
		{
			if (mb_substr($tmpfile, -4) == '.php')
				$zip->addfile($tmpfile, $phpfile);
			else
				$zip->addfile($tmpfile, 'game-info.xml');
			$zip->close();
		}
		return file_exists($destination);
	}
	else
		return false;
}

function arcade_create_rar($files = array(), $gameDataPath = '', $internal = '', $destination = '', $ignore = '', $ignoreArray, $tmpfile = '', $phpfile = '', $overwrite = false, $rom = 0)
{
	if(file_exists($destination) && !$overwrite)
		return false;

	list($valid_files, $pathParts, $randDir) = array(array(), array(), dirname($destination) . '/' . arcadeUniqPath());
	$windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? true : false;

	if(is_array($files))
	{
		foreach($files as $file)
		{
			$filex = str_replace("\\", "/", $file);
			$filey = str_replace('%20', ' ', $file);
			if (in_array($filex, $ignoreArray))
				continue;

			if(file_exists($file) && basename($file) != 'game-info.xml')
				$valid_files[] = $file;
			elseif(file_exists($filey) && basename($filey) != 'game-info.xml')
				$valid_files[] = $filey;
		}
	}

	sort($valid_files);
	if(count($valid_files))
	{
		if (file_exists($destination))
		{
			unlink($destination);
			clearstatcache();
		}
		if (file_exists($destination . '.tmp'))
		{
			unlink($destination . '.tmp');
			clearstatcache();
		}

		@mkdir($randDir, 0755);
		foreach($valid_files as $file)
		{
			$pathinfo = pathinfo($file);
			$pathParts = explode('/', stripslashes(rtrim($pathinfo['dirname'], '/\\')));
			$count = count($pathParts);
			if ((!empty($pathParts[$count-2])) && $pathParts[$count-2] == 'gamedata')
				$newfile = $pathParts[$count-2] . '/' . $pathParts[$count-1] . '/' . str_replace($ignore, '', basename($file));
			elseif(!empty($ignore))
				$newfile = str_replace($ignore . '/', '', $file);
			else
				$newfile = $file;

			if (empty($newfile))
				continue;
			$newfile = str_replace('\\', '/', $newfile);
			$newfile = str_replace('%20', ' ', $newfile);
			if(!is_dir(dirname($randDir . '/' . $newfile)))
				@mkdir(dirname($randDir . '/' . $newfile), 0755, true);
			if (is_dir($file))
			{
				@mkdir($randDir . '/' . $newfile, 0755, true);
				copyArcadeDownloadDirectory($file, $randDir . '/' . $newfile);
			}
			else
				@copy($file, $randDir . '/' . $newfile);

			if (!empty($gameDataPath) && !empty($internal))
			{
				@mkdir($randDir . '/gamedata', 0755, true);
				copyArcadeDownloadDirectory($gameDataPath, $randDir . '/gamedata/' . $internal . '/');
			}
		}

		if (mb_substr($tmpfile, -4) == '.php')
			@copy($tmpfile, $randDir . '/' . $phpfile);
		else
			@copy($tmpfile, $randDir . '/game-info.xml');

		if (is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'))
		{
			if (!$windows)
			{
				chdir($randDir);
				shell_exec('rar a -ed -ma4 -av -k -rr ' . $destination . ' ./*');
			}
			else
			{
				if (!empty($arcadeModSettings['arcadeDownloadWinRarDir']) && is_dir($arcadeModSettings['arcadeDownloadWinRarDir']))
					@chdir($arcadeModSettings['arcadeDownloadWinRarDir']);
				elseif (is_dir("\Program Files (x86)\WinRAR"))
					@chdir("\Program Files (x86)\WinRAR");
				elseif (is_dir("\Program Files\WinRAR"))
					@chdir("\Program Files\WinRAR");

				shell_exec('rar a -r -u -ep1 -ed -m4 -av -k -rr ' . $destination . ' ' . $randDir . '/*.*');
			}
		}
		else
		{
			$rar = new RarArchiver($destination, RarArchiver::CREATE);
			$rar->buildFromDirectory($randDir);
			unset($rar);
		}

		if (file_exists($tmpfile))
		{
			unlink($tmpfile);
			clearstatcache();
		}
		if (is_dir($randDir))
			arcadeCompressRmdir($randDir, $rom);
		return file_exists($destination);
	}

	return false;
}

function arcade_create_phar($files = array(), $gameDataPath = '', $internal = '', $destination = '', $ignore = '', $ignoreArray, $tmpfile = '', $phpfile = '', $overwrite = false, $rom = 0)
{
	if(file_exists($destination) && !$overwrite)
		return false;

	list($valid_files, $pathParts, $randDir) = array(array(), array(), dirname($destination) . '/' . arcadeUniqPath());

	if(is_array($files))
	{
		foreach($files as $file)
		{
			$filex = str_replace("\\", "/", $file);
			$filey = str_replace('%20', ' ', $file);
			if (in_array($filex, $ignoreArray))
				continue;

			if(file_exists($file) && basename($file) != 'game-info.xml')
				$valid_files[] = $file;
			elseif(file_exists($filey) && basename($filey) != 'game-info.xml')
				$valid_files[] = $filey;
		}
	}

	sort($valid_files);
	if(count($valid_files))
	{
		if (file_exists($destination))
		{
			unlink($destination);
			clearstatcache();
		}
		if (file_exists($destination . '.tmp'))
		{
			unlink($destination . '.tmp');
			clearstatcache();
		}

		@mkdir($randDir, 0755);
		foreach($valid_files as $file)
		{
			$pathinfo = pathinfo($file);
			$pathParts = explode('/', stripslashes(rtrim($pathinfo['dirname'], '/\\')));
			$count = count($pathParts);
			if ((!empty($pathParts[$count-2])) && $pathParts[$count-2] == 'gamedata')
				$newfile = $pathParts[$count-2] . '/' . $pathParts[$count-1] . '/' . str_replace($ignore, '', basename($file));
			elseif(!empty($ignore))
				$newfile = str_replace($ignore . '/', '', $file);
			else
				$newfile = $file;

			if (empty($newfile))
				continue;
			$newfile = str_replace('\\', '/', $newfile);
			$newfile = str_replace('%20', ' ', $newfile);
			if(!is_dir(dirname($randDir . '/' . $newfile)))
				@mkdir(dirname($randDir . '/' . $newfile), 0755, true);
			if (is_dir($file))
			{
				@mkdir($randDir . '/' . $newfile, 0755, true);
				copyArcadeDownloadDirectory($file, $randDir . '/' . $newfile);
			}
			else
				@copy($file, $randDir . '/' . $newfile);

			if (!empty($gameDataPath) && !empty($internal))
			{
				@mkdir($randDir . '/gamedata', 0755, true);
				copyArcadeDownloadDirectory($gameDataPath, $randDir . '/gamedata/' . $internal . '/');
			}
		}

		if (mb_substr($tmpfile, -4) == '.php')
			@copy($tmpfile, $randDir . '/' . $phpfile);
		else
			@copy($tmpfile, $randDir . '/game-info.xml');

		if (mb_substr($destination, -3) == '.gz')
		{
			$destination = mb_substr($destination, -3) == '.gz' ? rtrim($destination, '.gz') : $destination;
			$gz = true;
		}
		$tar = new PharData($destination);
		$tar->buildFromDirectory($randDir);
		unset($tar);
		if (!empty($gz) && file_exists($destination))
		{
			$tar = new PharData($destination);
			$tar->compress(Phar::GZ);
			$destination .= '.gz';
			unset($tar);
		}

		if (file_exists($tmpfile))
		{
			unlink($tmpfile);
			clearstatcache();
		}

		arcadeCompressRmdir($randDir, $rom);
		return file_exists($destination);
	}

	return false;
}

/* START - Delete archives function */
function deleteArchives($directory, $empty = true)
{
	global $arcadeModSettings;

	$action_b = 'DENY_';
	if (!empty($arcadeModSettings['arcadeDownPass']))
		$action_b = $arcadeModSettings['arcadeDownPass'];

	if(mb_substr($directory,-1) == "/")
        $directory = mb_substr($directory,0,-1);

	if(!file_exists($directory) || !is_dir($directory))
        return false;
    elseif(!is_readable($directory))
        return false;
    else
	{
        $directoryHandle = opendir($directory);
        while ($contents = readdir($directoryHandle))
		{
            if($contents != '.' && $contents != '..')
			{
				$path = $directory . "/" . $contents;
				$action_a = $contents;
				$trigger = false;

				if ($contents == '.htaccess' || $contents == 'index.php')
					$trigger = true;

				if ((strstr($action_a,$action_b)))
					$trigger = true;

				if (is_dir($path))
					deleteArchives($path);
                elseif ($trigger == false && @file_exists($path))
				{
                    unlink($path);
					clearstatcache();
				}
            }
        }
        closedir($directoryHandle);
        if($empty == false)
		{
            if(!rmdir($directory))
                return false;
		}
        return true;
    }
}

// Check if the column exists
function checkFieldPDL($tableName, $columnName)
{
	if (check_table_existsPDL($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		if (in_array($columnName, $check))
			return true;
	}

	return false;
}

// Returns amount of columns in a table
function checkTablePDL($tableName)
{
	global $smcFunc;

	if (check_table_existsPDL($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		return !empty($check) ? count($check) : false;
	}
	return false;
}

// Check if table exists
function check_table_existsPDL($table, $check = false)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		$check = true;

	return $check;
}

// Update arcade_pdl1 values
function createxpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl1
		WHERE id_member = {int:userid}',
		array('userid' => $userid,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_pdl1',
		array(
			'id_member' => 'int', 'count' => 'int', 'year' => 'string', 'day' => 'string', 'latest_year' => 'string', 'latest_day' => 'string', 'permission' => 'int',
		),
		array(
			$userid, $count, $year, $day, $latest_year, $latest_day, $permission,
		),
		array('id_member',
		)
	);
}

function createxpdlguestval1($userip, $count, $year, $day)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_guest_extra_data
		WHERE online_ip LIKE {string:userip}',
		array('userip' => $userip,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_guest_extra_data',
		array(
			'online_ip' => 'string', 'count' => 'int', 'year' => 'string', 'day' => 'string',
		),
		array(
			$userip, $count, $year, $day,
		),
		array('online_ip',
		)
	);
}

// Update arcade_pdl2 values
function createxpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $reason, $dl_count, $dl_disable, $rom = 0)
{
	global $smcFunc, $user_info, $user_settings, $context;
	$game_name = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $gamename);
	$dcount = 0;

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:gameid}',
		array('gameid' => $gameid)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_pdl2',
		array(
			'pdl_gameid' => 'int', 'game_name' => 'string', 'report_day' => 'string', 'report_year' => 'string', 'user_id' => 'int', 'report_id' => 'int', 'report_reason' => 'string', 'download_count' => 'int', 'download_disable' => 'int',
		),
		array(
			$gameid, $game_name, $repday, $repyear, $repuser, $repid, $reason, $dl_count, $dl_disable,
		),
		array('pdl_gameid',
		)
	);

	if ($context['user']['is_logged']) {
		$request = $smcFunc['db_query']('', '
			SELECT id_member, download_count
			FROM {db_prefix}arcade_members
			WHERE id_member = {int:memberid}
			LIMIT 1',
			array(
				'memberid' => $user_info['id'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$dcount = empty($row['download_count']) ? 0 : (int)$row['download_count'];
			$dcount++;
		}

		$smcFunc['db_free_result']($request);

		if (!empty($dcount)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_members
				SET download_count = {int:dcount}
				WHERE id_member = {int:memberid}',
				array(
					'memberid' => $user_info['id'],
					'dcount' => $dcount,
				)
			);
		}
		else {
			$request = $smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_members
				WHERE id_member = {int:memberid}',
				array('memberid' => $user_info['id'])
			);

			$smcFunc['db_insert']('replace',
				'{db_prefix}arcade_members',
				array(
					'id_member' => 'int',
					'arena_invite' => 'int',
					'arena_match_end' => 'int',
					'arena_new_round' => 'int',
					'champion_email' => 'int',
					'champion_pm' => 'int',
					'new_game' => 'int',
					'notify_game_reports' => 'int',
					'games_per_page' => 'int',
					'archive_type' => 'int',
					'arcade_gametype' => 'int',
					'arcade_gametype_rom' => 'int',
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
					'download_count' => 'int',
					'arcade_emulatorjs_rom' => 'text',
				),
				array(
					(int)$user_info['id'],
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					0,
					1,
					'',
				),
				array('id_member')
			);
		}
	}
}

function arcadeUniqPath($length = 13)
{
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($length / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
    } else {
        $bytes = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", $length/2)), 0, $length/2);
    }
    return substr(bin2hex($bytes), 0, $length);
}

function arcadeCompressRmdir($dir, $rom = 0)
{
	global $arcadeModSettings, $boarddir;

	// linux/windows compatibility
	$gamesdir = empty($rom) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$boarddirx = str_replace('\\', '/', $boarddir);
	$thisPath = str_replace('\\', '/', $dir);


	$gamesdir = trim($gamesdir, '/');
	$boarddirx = trim($boarddirx, '/');
	$mainPathArray = array('Sources', 'Themes', 'Packages', 'Smileys', 'cache', 'avatars', 'attachments', 'ArcadeSources');
	$thisPath = trim($thisPath, '/');

	// make absolutely sure the deleted path is not an essential parent path
	if ($thisPath == $boarddirx || $thisPath == $boarddirx . '/' . $gamesdir)
		return;

	foreach ($mainPathArray as $path)
	{
		if ($thisPath == $boarddirx . '/' . $path)
			return;
	}

	if (is_dir($dir))
	{
		$objects = scandir($dir);
		foreach ($objects as $object)
		{
			if ($object != '.' && $object != '..')
			{
				if (filetype($dir . '/' . $object) == 'dir')
					arcadeCompressRmdir($dir . '/' . $object, $rom);
				else
				{
					unlink($dir . '/' . $object);
				}
			}
		}

		clearstatcache();
		reset($objects);
		if (count(scandir($dir)) == 2)
			rmdir($dir);
	}
}

function copyArcadeDownloadDirectory($source, $destination)
{
	if (is_dir($source))
	{
		if (!is_dir($destination))
			@mkdir($destination);

		$directory = @dir($source);
		while (FALSE !== ($readdirectory = $directory->read()))
		{
			if ($readdirectory == '.' || $readdirectory == '..')
				continue;

			$PathDir = $source . '/' . $readdirectory;

			if (is_dir($PathDir))
			{
				copyArcadeDownloadDirectory($PathDir, $destination . '/' . $readdirectory);
				continue;
			}
			copy($PathDir, $destination . '/' . $readdirectory);
		}

		$directory->close();
	}
	elseif (@file_exists($source) && !@file_exists($destination))
		@copy($source, $destination);
	else
		return false;

	return true;
}

?>