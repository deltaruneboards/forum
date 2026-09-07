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

function list_getNumGamesInstalled($filter)
{
	global $smcFunc;

	list($where, $type, $alpha) = array('', '', '');
	if (!empty($_SESSION['arcade_manage_sort_select_type']) && $_SESSION['arcade_manage_sort_select_type'] != 'all' && $_SESSION['arcade_manage_sort_select_type'] != 'new')
	{
		$where .= '
			AND g.submit_system = {string:type}';
		$type = $_SESSION['arcade_manage_sort_select_type'];
	}
	if (!empty($_SESSION['arcade_manage_sort_select_type']) && $_SESSION['arcade_manage_sort_select_type'] == 'new')
		$sort = 'g.id_game DESC';
	if (!empty($_SESSION['arcade_manage_sort_select_alpha']) && $_SESSION['arcade_manage_sort_select_alpha'] != 'all')
	{
		$where .= '
			AND (g.game_name LIKE {string:alpha1} OR g.game_name LIKE {string:alpha2})';
		$alpha = $_SESSION['arcade_manage_sort_select_alpha'];
	}

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_games AS g
		WHERE g.id_game > 0' . ($filter == 'disabled' || $filter == 'enabled' ? '
			AND g.rom_flag = {int:rom_flag} AND g.enabled = {int:enabled}' : '
			AND g.rom_flag = {int:rom_flag}') . $where,
		array(
			'enabled' => $filter == 'disabled' ? 0 : 1,
			'type' => $type,
			'alpha1' => mb_strtoupper($alpha) . '%',
			'alpha2' => mb_strtolower($alpha) . '%',
			'rom_flag' => 0,
		)
	);

	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

function list_getGamesInstalled($start, $items_per_page, $sort, $filter)
{
	global $smcFunc, $scripturl, $context, $txt;

	list($where, $type, $alpha) = array('', '', '');
	if (!empty($_SESSION['arcade_manage_sort_select_type']) && $_SESSION['arcade_manage_sort_select_type'] != 'all' && $_SESSION['arcade_manage_sort_select_type'] != 'new')
	{
		$where .= '
			AND g.submit_system = {string:type}';
		$type = $_SESSION['arcade_manage_sort_select_type'];
	}
	if (!empty($_SESSION['arcade_manage_sort_select_type']) && $_SESSION['arcade_manage_sort_select_type'] == 'new')
		$sort = 'g.id_game DESC';
	if (!empty($_SESSION['arcade_manage_sort_select_alpha']) && $_SESSION['arcade_manage_sort_select_alpha'] != 'all')
	{
		$where .= '
			AND (g.game_name LIKE {string:alpha1} OR g.game_name LIKE {string:alpha2})';
		$alpha = $_SESSION['arcade_manage_sort_select_alpha'];
	}
	$request = $smcFunc['db_query']('', '
		SELECT g.game_name, g.internal_name, g.id_game, cat.id_cat, cat.cat_name, g.submit_system
		FROM {db_prefix}arcade_games AS g
		LEFT JOIN {db_prefix}arcade_categories AS cat ON (cat.id_cat = g.id_cat)
		WHERE g.rom_flag = {int:rom_flag} AND g.id_game > 0' . ($filter == 'disabled' || $filter == 'enabled' ? '
			AND g.enabled = {int:enabled}' : '') . $where . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:games_per_page}',
		array(
			'start' => $start,
			'games_per_page' => $items_per_page,
			'sort' => $sort,
			'enabled' => $filter == 'disabled' ? 0 : 1,
			'type' => $type,
			'alpha1' => mb_strtoupper($alpha) . '%',
			'alpha2' => mb_strtolower($alpha) . '%',
			'rom_flag' => 0,
		)
	);

	$return = array();
	$idFile = 0;
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$status = 1; //$status = $row['status'];
		$idFile++;
		$return[] = array(
			'id' => $row['id_game'],
			'id_file' => $idFile,
			'name' => $row['game_name'],
			'submit_system' => $row['submit_system'],
			'href' => $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $row['id_game'],
			'category' => array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
			),
			'error' => $status != 1 ? $txt['arcade_missing_files'] : false,
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function list_getNumGamesInstall()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_files AS f
		WHERE f.status = 10',
		array(
		)
	);

	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

function list_getGamesInstall($start, $items_per_page, $sort)
{
	global $smcFunc, $scripturl, $context, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.game_name, f.status
		FROM {db_prefix}arcade_files AS f
		WHERE f.status = 10
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:games_per_page}',
		array(
			'start' => $start,
			'games_per_page' => $items_per_page,
			'sort' => $sort,
		)
	);

	$return = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$return[] = array(
			'id_file' => $row['id_file'],
			'name' => $row['game_name'],
			'href' => $scripturl . '?action=admin;area=managegames;sa=install2;file=' . $row['id_file'],
		);
	$smcFunc['db_free_result']($request);

	return $return;
}

function deleteGame($id, $remove_files, $gameNameDel = '')
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $arcadeModSettings;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_game_info
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_favorite
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_rates
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);

	if ($remove_files)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_files
			WHERE id_game = {int:game}',
			array(
				'game' => $id,
			)
		);
	else
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_files
			SET id_game = 0, status = 10
			WHERE id_game = {int:game}',
			array(
				'game' => $id,
			)
		);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_games
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:game}',
		array(
			'game' => $id,
		)
	);

	$gameNameDel = !empty($gameNameDel) ? $gameNameDel : $id;
	logAction('arcade_delete_game', array('game' => $gameNameDel));

	return true;
}

// Install games by game cache ids
function installGames($games, $set_category, $move_games = false)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $boarddir, $smcFunc, $sourcedir, $settings;
	loadClassFile('Class-Package.php');

	// SWF Reader will be needed
	require_once($boarddir . '/ArcadeSources/SWFReader.php');
	require_once($sourcedir . '/ArcadeAdmin.php');
	list($swf, $masterGameinfo, $status, $directories, $arcadeModSettings['gamesDirectory'], $upload_directory) = array(new SWFReader(), array(), array(), array(), str_replace('\\', '/', $arcadeModSettings['gamesDirectory']), str_replace('\\', '/', $boarddir . '/games_upload'));
	$arcadeModSettings['gamesDirectory'] = rtrim($arcadeModSettings['gamesDirectory'], '/');
	$rom_directory = str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$rom_directory = rtrim($rom_directory, '/');
	$upload_directory = rtrim($upload_directory, '/');
	$set_category = !empty($set_category) ? $set_category : 0;
	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	@ini_set("gd.jpeg_ignore_warning", 1);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$classicGameTypes2 = $classicGameTypes;
	if (($key = array_search('ibp32', $classicGameTypes2)) !== FALSE) {
		unset($classicGameTypes2['ibp32']);
	}

	$request = $smcFunc['db_query']('', '
		SELECT game_directory
		FROM {db_prefix}arcade_games
		WHERE id_game > 0',
		array(
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$directories[] = !empty($row['game_directory']) ? $row['game_directory'] : '';
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.game_name, f.status, f.game_file, f.game_directory, f.submit_system
		FROM {db_prefix}arcade_files AS f
		WHERE f.id_file IN ({array_int:games})
			AND f.status = 10',
		array(
			'games' => $games,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		unset($game, $gameinfo, $gameOptions, $masterGameinfo, $skip, $html53, $deleteFile);
		list($errors, $failed, $moveFail, $exists, $masterGameinfo, $gameinfo, $game, $numb, $rom_flag, $rom_system) = array(array(), true, false, false, array(), array(), array(), 0, 0, 'none');
		$saveType = !empty($row['submit_system']) ? $row['submit_system'] : '';
		$row['game_directory'] = (!empty($row['game_directory']) ? trim($row['game_directory'], '/') : '');
		$pop = explode('.', $row['game_file']);
		$dirs = !empty($row['game_directory']) ? str_replace('\\', '/', $row['game_directory']) : '';
		$allDirs = explode('/', $dirs);
		$countDirs = count($allDirs)-1;
		$checkz = substr($row['game_file'], -10) == 'index.html' ? 9 : (substr($row['game_file'], -9) == 'index.php' ? 8 : 0);
		$filename = $checkz && strlen($row['game_file']) > $checkz ? basename($dirs) : substr($row['game_file'], 0, -strlen('.' . end($pop)));
		$sourceGamePath = $upload_directory . '/' . (!empty($row['game_directory']) ? $row['game_directory'] . '/' . trim($filename, '/') : trim($filename, '/'));
		$sourceGamePathProper = $upload_directory . '/' . (!empty($row['game_directory']) ? str_replace(' ', '_', $row['game_directory']) . '/' . trim($filename, '/') : trim($filename, '/'));
		$gamePath = ($saveType != 'rom' ? $arcadeModSettings['gamesDirectory'] : $rom_directory) . '/' . (!empty($row['game_directory']) ? $row['game_directory'] . '/' . trim($filename, '/') : trim($filename, '/'));
		$gamePathProper = ($saveType != 'rom' ? $arcadeModSettings['gamesDirectory'] : $rom_directory) . '/' . (!empty($row['game_directory']) ? str_replace(' ', '_', $row['game_directory']) . '/' . trim($filename, '/') : trim($filename, '/'));

		/*
		if ($gamePath != $gamePathProper && file_exists($gamepath))
		{
			die($row['game_directory']);
			@rename($gamepath, $gamePathProper);
			$gamePath = $gamePathProper;

			if (!file_exists($gamepath))
				fatal_lang_error('arcade_file_non_read', false);
		}
		*/
		// sanitize some stuff
		$mainFile = substr($row['game_file'], 0, (strlen ($row['game_file'])) - (strlen (strrchr($row['game_file'],'.'))));
		$sourceDirectory = $upload_directory . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
		$directory = $row['submit_system'] != 'rom' ? $arcadeModSettings['gamesDirectory'] : $rom_directory;
		$directory .= !empty($row['game_directory']) ? '/' . $row['game_directory'] : '';
		$iname = basename($dirs);
		$iname = stripos($iname, 'gamepack') === false && stripos($iname, 'gamespack') === false ? $iname : $mainFile;
		$internal_name = !empty($iname) && $iname != "." && $iname != ".." && !empty($checkz) ? $iname : $mainFile;
		$internal_name = trim(preg_replace('/[^a-zA-Z0-9\-\._]/', '' , $internal_name), '_');
		$initialInternalName = $internal_name;

		//$internal_name = $checkz && strlen($row['game_file']) > $checkz ? trim($dirs, '/') : substr($row['game_file'], 0, -strlen('.' . end($pop)));

		// change internal name if a like one exists but this may cause an issue for saving a score in some circumstances
		$internals = arcadeInternalName($internal_name);
		while(in_array($internal_name, $internals))
		{
			$numb++;
			$internal_name = $numb > 1 ? substr($internal_name, 0, -1) . (string)$numb : $internal_name . (string)$numb;
		}

		// Search for gamedata
		if (is_dir($sourceDirectory))
			chdir($sourceDirectory);

		if (basename(dirname($sourceDirectory)) !== basename($upload_directory) && file_exists(dirname($sourceDirectory) . '/master-info.xml'))
		{
			$masterGameinfo = array();
			$masterGameinfo = readGameInfo(dirname($sourceDirectory) . '/master-info.xml');

			if (!isset($gameinfo['submit']))
				unset($gameinfo);
		}

		if (file_exists($sourceDirectory . '/game-info.xml'))
		{
			$gameinfo = readGameInfo($sourceDirectory . '/game-info.xml');
			if (!isset($gameinfo['id']))
				unset($gameinfo);
		}
		elseif (file_exists($sourceDirectory . '/' . $internal_name . '-game-info.xml'))
		{
			$gameinfo = readGameInfo($sourceDirectory . '/' . $internal_name . '-game-info.xml');

			if (!isset($gameinfo['id']))
				unset($gameinfo);
		}
		elseif (file_exists($sourceDirectory . '/' . $internal_name . '.php'))
		{
			$gameinfo = readPhpGameInfo($row['game_directory'], $internal_name, true);
			$hexColor = !empty($gameinfo['extra_data']['background_color']) && is_array($gameinfo['extra_data']['background_color']) ? $gameinfo['extra_data']['background_color'] : '';
			$rom_system = !empty($gameInfo['rom_system']) && $saveType == 'rom' ? $gameInfo['rom_system'] : 'none';
			$rom_flag = !empty($gameInfo['rom_system']) && $saveType == 'rom' ? 1 : 0;
		}
		else {
			list($xfiles, $xpath) = array(array(), basename($sourceDirectory));
			$xfiles = glob($sourceDirectory . '/*.{php}', GLOB_BRACE);
			foreach ($xfiles as $xfile) {
				$fileTemp = file_get_contents($xfile);
				if (!empty($fileTemp) && stripos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
					$yfile = pathinfo(basename($xfile), PATHINFO_FILENAME);
					$gameinfo = readPhpGameInfo($row['game_directory'], $yfile, true);
					$hexColor = !empty($gameinfo['extra_data']['background_color']) && is_array($gameinfo['extra_data']['background_color']) ? $gameinfo['extra_data']['background_color'] : '';
					$rom_system = !empty($gameInfo['rom_system']) && $saveType == 'rom' ? $gameInfo['rom_system'] : 'none';
					$rom_flag = !empty($gameInfo['rom_system']) && $saveType == 'rom' ? 1 : 0;
					break;
				}
			}
		}

		foreach ($masterGameinfo as $key => $masterSetting)
			if (!empty($masterGameinfo[$key]))
				$gameinfo[$key] = $masterGameinfo[$key];


		$xfiles = glob($sourceDirectory . '/' . $internal_name . '*.{png,gif,jpg}', GLOB_BRACE);
		$sodir = str_replace('\\', '/', $sourceDirectory);
		list($yfiles, $thumbnail, $thumbnailSmall, $covericon) = array(array(), '', '', '');
		if (!empty($xfiles)) {
			foreach ($xfiles as $filex) {
				$filey = str_replace('\\', '/', $filex);
				if ($filex != $sodir . '/' . basename($filey)) {
					continue;
				}
				$yfiles[] = $filex;
			}
		}
		foreach (array('jpg', 'png', 'gif') as $fileType) {
			if (in_array($sodir . '/' . $internal_name . '1.' . $fileType, $yfiles)) {
				$thumbnail = $internal_name . '1.' . $fileType;
				break;
			}
			elseif (in_array($sodir . '/' . $internal_name . '_1.' . $fileType, $yfiles)) {
				@rename($sodir . '/' . $internal_name . '_1.' . $fileType, $sodir . '/' . $internal_name . '1.' . $fileType);
				clearstatcache();
				$thumbnail = file_exists($sodir . '/' . $internal_name . '1.' . $fileType) ? $internal_name . '1.' . $fileType : $internal_name . '_1.' . $fileType;
				break;
			}
		}
		foreach (array('jpg', 'png', 'gif') as $fileType) {
			if (in_array($sodir . '/' . $internal_name . '2.' . $fileType, $yfiles)) {
				$thumbnailSmall = $internal_name . '2.' . $fileType;
				break;
			}
			elseif (in_array($sodir . '/' . $internal_name . '_2.' . $fileType, $yfiles)) {
				@rename($sodir . '/' . $internal_name . '_2.' . $fileType, $sodir . '/' . $internal_name . '2.' . $fileType);
				clearstatcache();
				$thumbnailSmall = file_exists($sodir . '/' . $internal_name . '2.' . $fileType) ? $internal_name . '2.' . $fileType : $internal_name . '_2.' . $fileType;
				break;
			}
		}
		foreach (array('jpg', 'png', 'gif') as $fileType) {
			if (in_array($sodir . '/' . $internal_name . '3.' . $fileType, $yfiles)) {
				$covericon = $internal_name . '3.' . $fileType;
				break;
			}
			elseif (in_array($sodir . '/' . $internal_name . '_3.' . $fileType, $yfiles)) {
				@rename($sodir . '/' . $internal_name . '_3.' . $fileType, $sodir . '/' . $internal_name . '3.' . $fileType);
				clearstatcache();
				$covericon = file_exists($sodir . '/' . $internal_name . '3.' . $fileType) ? $internal_name . '3.' . $fileType : $internal_name . '_3.' . $fileType;
				break;
			}
		}

		foreach ($yfiles as $filey) {
			$ext = pathinfo($filey, PATHINFO_EXTENSION);
			if (empty($thumbnail)) {
				$check = arcadeSanitizeImg(basename($filey));
				if ($check != basename($filey)) {
					@rename($filey, $sodir . '/' . $internal_name . '1.' . $ext);
					if (file_exists($sodir . '/' . $internal_name . '1.' . $ext))
						$thumbnail = $internal_name . '1.' . $ext;
				}
				$thumbnail = !empty($thumbnail) ? $thumbnail : basename($filey);
				continue;
			}
			if (empty($thumbnailSmall)) {
				$check = arcadeSanitizeImg(basename($filey));
				if ($check != basename($filey)) {
					@rename($filey, $sodir . '/' . $internal_name . '2.' . $ext);
					if (file_exists($sodir . '/' . $internal_name . '2.' . $ext))
						$thumbnailSmall = $internal_name . '2.' . $ext;
				}
				$thumbnailSmall = !empty($thumbnailSmall) ? $thumbnailSmall : basename($filey);
				break;
			}
			if (empty($covericon)) {
				$check = arcadeSanitizeImg(basename($filey));
				if ($check != basename($filey)) {
					@rename($filey, $sodir . '/' . $internal_name . '3.' . $ext);
					if (file_exists($sodir . '/' . $internal_name . '3.' . $ext))
						$covericon = $internal_name . '3.' . $ext;
				}
				$covericon = !empty($covericon) ? $covericon : basename($filey);
				continue;
			}
		}

		$gameinfo['thumbnail'] = empty($gameinfo['thumbnail']) ? $thumbnail : $gameinfo['thumbnail'];
		$gameinfo['thumbnail-small'] = empty($gameinfo['thumbnail-small']) ? $thumbnailSmall : $gameinfo['thumbnail-small'];
		$gameinfo['thumbnail_small'] = empty($gameinfo['thumbnail_small']) ? $gameinfo['thumbnail-small'] : $gameinfo['thumbnail_small'];
		$gameinfo['cover_icon'] = empty($gameinfo['cover_icon']) ? $covericon : $gameinfo['cover_icon'];
		$gameinfo['help'] = empty($gameinfo['help']) ? '' : $gameinfo['help'];
		$gameinfo['help'] = empty($gameinfo['help']) && !empty($gameinfo['gkeys']) ? $gameinfo['gkeys'] : $gameinfo['help'];
		if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
			$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];
		if (!empty($gameInfo['romflag'])) {
			$directory = $rom_directory . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
			$gameinfo['submit'] = 'rom';
		}

		// check if game file is mismatched to index.html
		$fileNamex = $row['game_file'];
		$fileTrip = false;
		if (!empty($gameinfo['submit']) && stripos($gameinfo['submit'], 'html') !== FALSE) {
			$internalName = str_replace(array('/', '\\'), array('', ''), trim($internal_name, '.'));
			if (file_exists($sourceDirectory . '/gamedata/' . $internalName . '/' . $internalName . '.html'))
				$fileNamex = 'gamedata/' . $internalName . '/' . $internalName . '.html';
				$row['game_file'] = $fileNamex;
				$fileTrip = true;
		}

		$game = array(
			'id_file' => $row['id_file'],
			'name' => $row['game_name'],
			'directory' => $row['game_directory'],
			'file' => $fileNamex,
			'internal_name' => str_replace(array('/', '\\'), array('', ''), trim($internal_name, '.')),
			'thumbnail' => !empty($thumbnail) ? $thumbnail : (!empty($gameinfo['thumbnail']) ? $gameinfo['thumbnail'] : ''),
			'thumbnail_small' => !empty($thumbnailSmall) ? $thumbnailSmall : (!empty($gameinfo['thumbnail-small']) ? $gameinfo['thumbnail-small'] : ''),
			'cover_icon' => !empty($covericon) ? $covericon : (!empty($gameinfo['cover_icon']) ? $gameinfo['cover_icon'] : ''),
			'extra_data' => array(
				'width' => !empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']) ? $gameinfo['flash']['width'] : '',
				'height' => !empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']) ? $gameinfo['flash']['height'] : '',
				'flash_version' => !empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']) ? $gameinfo['flash']['version'] : 0,
				'type' => !empty($gameinfo['flash']['type']) ? ArcadeSpecialChars($gameinfo['flash']['type'], 'name') : '',
				'background_color' => !empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6 ? array(
					hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
					hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
					hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
				) : (!empty($hexColor) ? $hexColor : array(
					hexdec('00'),
					hexdec('00'),
					hexdec('00')
				)),
			),
			'js_insertion' => isset($gameinfo['js_insertion']) ? $gameinfo['js_insertion'] : 0,
			'help' => isset($gameinfo['help']) ? $gameinfo['help'] : '',
			'description' => isset($gameinfo['description']) ? $gameinfo['description'] : '',
			'submit_system' => $saveType,
			'rom_system' => $rom_system,
			'rom_flag' => $rom_flag,
		);

		unset($thumbnail, $thumbnailSmall, $covericon);

		// Get information from flash
		if (mb_substr($row['game_file'], -4) == '.swf')
		{
			if (file_exists($sourceDirectory . '/' . $row['game_file']))
			{
				$swf->open($sourceDirectory . '/' . $row['game_file']);

				// Add possible flash read values
				if (!$swf->error)
				{
					$gameinfo['flash']['version'] = $swf->header['version'];
					$gameinfo['flash']['bgcolor'] = empty($game['extra_data']['background_color']) ? $swf->header['background'] : $game['extra_data']['background_color'];
					$gameinfo['flash']['width'] = empty($game['extra_data']['width']) ? $swf->header['width'] : $game['extra_data']['width'];
					$gameinfo['flash']['height'] = empty($game['extra_data']['height']) ? $swf->header['height'] : $game['extra_data']['height'];
					$gameinfo['flash']['type'] = empty($game['extra_data']['type']) ? '' : $game['extra_data']['type'];
				}
				else
					$gameinfo['flash']['bgcolor'] = !empty($game['extra_data']['background_color']) ? $game['extra_data']['background_color'] : '000000';
				$swf->close();
			}

			if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
				$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];

			if (isset($gameinfo['flash']))
			{
				if (!empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']))
					$game['extra_data']['width'] = (int)$gameinfo['flash']['width'];
				if (!empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']))
					$game['extra_data']['height'] = (int)$gameinfo['flash']['height'];
				if (!empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']))
					$game['extra_data']['flash_version'] = (int)$gameinfo['flash']['version'];
				if (!empty($gameinfo['flash']['type']))
					$game['extra_data']['type'] = ArcadeSpecialChars($gameinfo['flash']['type'], 'name');
				if (!empty($gameinfo['flash']['bgcolor']) && is_array($gameinfo['flash']['bgcolor']) && count($gameinfo['flash']['bgcolor']) == 3)
				{
					$sCSSString = 'rgba(' . $gameinfo['flash']['bgcolor'][0] . ', ' . $gameinfo['flash']['bgcolor'][1] . ', ' . $gameinfo['flash']['bgcolor'][2] . ')';
					$sRegex = '/rgba?(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3}))/i';
					preg_match($sRegex, $sCSSString, $matches);
					if(count($matches) == 4)
					{
						$iRed   = (int) $matches[1];
						$iGreen = (int) $matches[2];
						$iBlue  = (int) $matches[3];
						if ($iRed > 255 || $iGreen > 255 || $iBlue > 255)
						{
							$game['extra_data']['background_color'] = array(
								hexdec('00'),
								hexdec('00'),
								hexdec('00')
							);
						}
						else
							$game['extra_data']['background_color'] = $gameinfo['flash']['bgcolor'];
					}
				}
				elseif (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
				{
					$game['extra_data']['background_color'] = array(
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
					);
				}
				else
				{
					$game['extra_data']['background_color'] = array(
						hexdec('00'),
						hexdec('00'),
						hexdec('00')
					);
				}
			}
		}

		// Detect submit system
		if (empty($row['submit_system']))
		{
			if (isset($gameinfo['rom_system']) && !in_array($gameinfo['rom_system'], array('none', 'all')))
				$row['submit_system'] = 'rom';
			elseif (isset($gameinfo['submit']))
				$row['submit_system'] = $gameinfo['submit'];
			elseif (mb_substr($row['game_file'], -5) == '.html')
				$row['submit_system'] = 'html5';
			elseif (mb_substr($row['game_file'], -3) == 'php')
				$row['submit_system'] = 'custom_game';
			elseif (mb_substr($row['game_file'], -3) == 'xap')
				$row['submit_system'] = 'silver';
			elseif (file_exists($boarddir . '/arcade/gamedata/' . $internal_name . '/v32game.txt') || file_exists($boarddir . '/arcade/gamedata/' . $mainFile . '/v32game.txt'))
				$row['submit_system'] = 'ibp32';
			elseif (file_exists($boarddir . '/arcade/gamedata/' . $internal_name . '/v3game.txt') || file_exists($boarddir . '/arcade/gamedata/' . $mainFile . '/v3game.txt'))
				$row['submit_system'] = 'ibp3';
			elseif (file_exists($boarddir . '/arcade/gamedata/' . $internal_name . '/v2game.txt') || file_exists($boarddir . '/arcade/gamedata/' . $mainFile . '/v2game.txt'))
				$row['submit_system'] = 'ibp2';
			elseif (file_exists($sourceDirectory . '/' . $internal_name . '.ini') || file_exists($sourceDirectory . '/' . $mainFile . '.ini'))
				$row['submit_system'] = 'pnflash';
			elseif (file_exists($sourceDirectory . '/' . $internal_name . '.php'))
			{
				$file = file_get_contents($sourceDirectory . '/' . $internal_name . '.php');
				list($row['submit_system'], $row['game_file']) = checkArcadeHtmlGameFiles($sourceDirectory, $internal_name);
				$game['file'] = $row['game_file'];
				$game['submit_system'] = $row['submit_system'];
				if (strpos(str_replace(' ', '', $file), '$config=array(') === false && $row['submit_system'] == 'ibp')
					$row['submit_system'] = 'custom_game';
				elseif ($row['submit_system'] == 'html5')
					$skip = $row['game_file'];

				unset($file);
			}
			elseif (file_exists($sourceDirectory . '/' . $mainFile . '.php'))
			{
				$file = file_get_contents($sourceDirectory . '/' . $mainFile . '.php');
				list($row['submit_system'], $row['game_file']) = checkArcadeHtmlGameFiles($sourceDirectory, $internal_name);
				$game['file'] = $row['game_file'];
				$game['submit_system'] = $row['submit_system'];
				$game['submit'] = $row['submit_system'];
				if (strpos(str_replace(' ', '', $file), '$config=array(') === false && $row['submit_system'] == 'ibp')
				{
					if (file_exists($sourceDirectory . '/' . $mainFile . '.swf'))
						$row['submit_system'] = 'ibp2';
					else
						$row['submit_system'] = 'custom_game';
				}
				elseif ($row['submit_system'] == 'html5')
					$skip = $row['game_file'];

				unset($file);
			}
			elseif (file_exists($sourceDirectory . '/' . 'size.txt') && mb_substr($row['game_file'], -3) == 'swf')
				$row['submit_system'] = 'phpbb';
			elseif (!empty($saveType) && in_array($saveType, $classicGameTypes2))
				$row['submit_system'] = $saveType;
			else
				$row['submit_system'] = 'v1game';
		}

		//check for PHP-Quick-Arcade or Origon HTML5 submit type
		if ($row['submit_system'] == 'custom_game' || strpos($row['submit_system'], 'html5') !== false)
		{
			if (empty($skip))
			{
				$fileX = file_exists($sourceDirectory . '/gamedata/' . $internal_name . '/index.html') ? $sourceDirectory . '/gamedata/' . $internal_name . '/index.html' : '';
				$fileX = !$fileX && file_exists($sourceDirectory . '/gamedata/' . $internal_name . '/' . $internal_name . '.html') ? $sourceDirectory . '/gamedata/' . $internal_name . '/' . $internal_name . '.html' : $fileX;
				//$fileX = !$fileX && file_exists($sourceDirectory . '/' . $internal_name . '.html') ? $sourceDirectory . '/' . $internal_name . '.html' : $fileX;
				//$fileX = !$fileX && file_exists($sourceDirectory . '/index.html') ? $sourceDirectory . '/index.html' : $fileX;

				if ($fileX)
				{
					$infoFile = false;
					if(file_exists($sourceDirectory . '/' . $internal_name . '.php'))
					{
						$gameinfo = readPhpGameInfo($sourceDirectory, $internal_name, true);
						$infoFile = true;
					}
					elseif (file_exists($sourceDirectory . '/game-info.xml'))
					{
						$gameinfo = readGameInfo($sourceDirectory . '/game-info.xml');
						$infoFile = true;
					}
					elseif (file_exists($sourceDirectory . '/' . $internal_name . '_config.ini'))
					{
						$txt_file = file_get_contents($sourceDirectory . '/' . $internal_name . '_config.ini');
						$iniInfo = explode("\n", preg_replace('~\r\n?~', "\n", $txt_file));
						$num = 0;
						$gameinfo['flash'] = array();
						$ini = array('name', 'internal', 'width', 'height', 'cheat_check', 'scoring', 'description', 'help', 'bgcolor', 'ignore', 'ignore', 'ignore', 'ignore');

						if (!empty($iniInfo) && is_array($iniInfo))
						{
							foreach ($iniInfo as $info)
							{
								foreach (array('nom', 'variable', 'largeur', 'hauteur', 'anti_triche', 'highscore_type', 'description', 'controle', 'bgcolor', 'nbdecimal', 'size', 'fps', 'jeuxhtml5') as $var)
								{
									if (strpos($info, $var) !== false && $ini[$num] != 'ignore')
									{
										$info = str_replace(array($var, '='), array('', ''), $info);
										$info = str_replace(array("'", '"'), array('', ''), $info);
										$info = trim($info);
										if ($var == 'bgcolor')
										{
											if (strlen($info) == 6)
											{
												$gameinfo['flash']['background_color'] = array(
													hexdec(mb_substr($info, 0, 2)),
													hexdec(mb_substr($info, 2, 2)),
													hexdec(mb_substr($info, 4, 2))
												);
											}
											else
											{
												$gameinfo['flash']['background_color'] = array(
													hexdec('00'),
													hexdec('00'),
													hexdec('00')
												);
											}
										}
										elseif ($var == 'largeur')
											$gameinfo['flash']['width'] = $info;
										elseif ($var == 'hauteur')
											$gameinfo['flash']['height'] = $info;
										elseif($var == 'controle')
										{
											$helpArray = array($txt['arcade_game_control_mouse_key'], $txt['arcade_game_control_mouse'], $txt['arcade_game_control_key']);
											$info = floatval($info);
											$gameinfo['help']  = $info>-1 && $info<3 ? $helpArray[$info] : '';
										}
										else
											$gameinfo[$ini[$num]] = $info;

										$num++;
									}
								}
							}
						}

						$gameinfo['submit'] = 'html52';
						$game['submit_system'] = 'html52';
						$game['file'] = $internal_name . '.html';
						$row['game_file'] = $game['file'];
						$infoFile = true;
						$html53 = true;
					}
					else {
						list($xfiles, $xpath) = array(array(), basename($sourceDirectory));
						$xfiles = glob($sourceDirectory . '/*.{php}', GLOB_BRACE);
						foreach ($xfiles as $xfile) {
							$fileTemp = file_get_contents($xfile);
							if (!empty($fileTemp) && stripos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
								$yfile = pathinfo(basename($xfile), PATHINFO_FILENAME);
								$gameinfo = readPhpGameInfo($row['game_directory'], $yfile, true);
								$infoFile = true;
								break;
							}
						}
					}

					if (!empty($infoFile))
					{
						$gameinfo['help'] = empty($gameinfo['help']) ? '' : $gameinfo['help'];
						$gameinfo['help'] = empty($gameinfo['help']) && !empty($gameinfo['gkeys']) ? $gameinfo['gkeys'] : $gameinfo['help'];
						$game['help'] = isset($gameinfo['help']) ? $gameinfo['help'] : '';
						$game['js_insertion'] = isset($gameinfo['js_insertion']) ? (int)$gameinfo['js_insertion'] : 0;
						$game['description'] = isset($gameinfo['description']) ? $gameinfo['description'] : '';

						if (isset($gameinfo['flash']))
						{
							if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
								$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];
							if (!empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']))
								$game['extra_data']['width'] = (int)$gameinfo['flash']['width'];
							if (!empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']))
								$game['extra_data']['height'] = (int)$gameinfo['flash']['height'];
							if (!empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']))
								$game['extra_data']['flash_version'] = (int)$gameinfo['flash']['version'];
							if (!empty($gameinfo['flash']['type']))
								$game['extra_data']['type'] =  ArcadeSpecialChars($gameinfo['flash']['type'], 'name');
							if (!empty($gameinfo['flash']['bgcolor']) && is_array($gameinfo['flash']['bgcolor']) && count($gameinfo['flash']['bgcolor']) == 3)
							{
								$sCSSString = 'rgba(' . $gameinfo['flash']['bgcolor'][0] . ', ' . $gameinfo['flash']['bgcolor'][1] . ', ' . $gameinfo['flash']['bgcolor'][2] . ')';
								$sRegex = '/rgba?(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3}))/i';
								preg_match($sRegex, $sCSSString, $matches);
								if(count($matches) == 4)
								{
									$iRed   = (int) $matches[1];
									$iGreen = (int) $matches[2];
									$iBlue  = (int) $matches[3];
									if ($iRed > 255 || $iGreen > 255 || $iBlue > 255)
									{
										$game['extra_data']['background_color'] = array(
											hexdec('00'),
											hexdec('00'),
											hexdec('00')
										);
									}
									else
										$game['extra_data']['background_color'] = $gameinfo['flash']['bgcolor'];
								}
							}
							elseif (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
							{
								$game['extra_data']['background_color'] = array(
									hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
									hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
									hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
								);
							}
							else
							{
								$game['extra_data']['background_color'] = array(
									hexdec('00'),
									hexdec('00'),
									hexdec('00')
								);
							}
						}

						if (!empty($gameinfo['submit_phpbb']) && $gameinfo['submit_phpbb'] == 'phpbb')
						{
							$html53 = true;
							$game['submit_system'] = 'html53';
							$row['submit_system'] = 'html53';
						}
					}

					$filez = file_get_contents($fileX);
					$row['submit_system'] = empty($html53) ? 'html52' : 'html53';
				}
				elseif (file_exists($sourceDirectory . '/gamedata/' . $internal_name . '/index.php'))
				{
					$filez = file_get_contents($sourceDirectory . '/gamedata/' . $internal_name . '/index.php');
					if (strpos($filez, 'function ajax2') !== false)
					{
						$row['submit_system'] = 'html52';
						$game['file'] = 'gamedata/' . $game['internal_name'] . '/index.php';
					}
				}
				elseif (!is_dir($sourceDirectory . '/gamedata') && file_exists($sourceDirectory . '/index.html') && file_exists($sourceDirectory . '/' . $internal_name . '.php'))
				{
					// catch HTML5 games that do not have the common directory structure
					$filez = file_get_contents($sourceDirectory . '/' . $internal_name . '.php');
					if (stripos($filez, 'IN_PHPBB_ARCADE') !== false)
					{
						DefinePhpBB_Constants();
						require_once($sourceDirectory . '/' . $internal_name . '.php');
						if (!empty($game_data) && !empty($game_data['game_type']) && stripos($game_data['game_type'], 'html5') !== false)
						{
							$row['submit_system'] = 'html53';
							$game['file'] = 'index.html';
							if (!empty($fileNamex) && !empty($fileTrip))
								$game['file'] = basename($fileNamex);
						}
					}
					else
					{
						$row['submit_system'] = 'html52';
						$game['file'] = 'index.html';
						if (!empty($fileNamex) && !empty($fileTrip))
							$game['file'] = basename($fileNamex);
					}

					$fileX = $sourceDirectory . '/index.html';
					if (!empty($fileNamex) && !empty($fileTrip))
						$fileX = $fileNamex;
					$deleteFile = $sourceDirectory . '/' . $internal_name . '.php';
				}

				$fileX = empty($fileX) ? 'mismatch' : $fileX;
			}
		}

		// check for PHPBB v3Arcade save type ~ game settings file will override some defaults
		if (file_exists($sourceDirectory . '/' . $mainFile . '.game.php'))
		{
			$filez = file($sourceDirectory . '/' . $mainFile . '.game.php', FILE_SKIP_EMPTY_LINES);
			$phpFile = implode('', $filez);
			if (stripos($phpFile, 'v3arcade') !== false || stripos($phpFile, 'v3 arcade') !== false)
			{
				foreach($filez as $line)
				{
					$linex = trim(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $line));
					$linex = preg_replace("#/\*.*?\*/#si", '', $linex);
					if (strpos($linex, '$title=') !== false)
						$game['name'] = str_replace(array('$title=', "'", ';'), array('', '', ''), $linex);

					if (strpos($linex, '$description=') !== false) {
						$game['description'] = str_replace(array('$description=', "'", ";"), array('', '', ''), $linex);
						$game['description'] = arcadeFilterDbInstallText($game['description']);
					}

					if (strpos($linex, '$help=') !== false) {
						$game['help'] = str_replace(array('$help=', "'", ";"), array('', '', ''), $linex);
						$game['help'] = arcadeFilterDbInstallText($game['help']);
					}

					if (strpos($linex, '$game_width=') !== false)
						$game['extra_data']['width'] = str_replace(array('$game_width=', "'", ";"), array('', '', ''), $linex);

					if (strpos($linex, '$game_height=') !== false)
						$game['extra_data']['height'] = str_replace(array('$game_height=', "'", ";"), array('', '', ''), $linex);

					preg_match("/games(.*)values/is",$line, $results);
					$parse = !empty($results) ? $results[0] : '';
					$inserts = str_replace(array('(', ')', '\'', '"'), array('', '', '', ''), $parse);

					preg_match("/values(.*)\)/is",$line, $results);
					$parse = !empty($results) ? $results[0] : '';
					$values = str_replace(array('(', ')', '\'', '"'), array('', '', '', ''), $parse);

					if (!empty($inserts) && !empty($values) && empty($game['description']))
					{
						$insertArray = explode(',', $inserts);
						$valuesArray = explode(',', $values);

						if (count($insertArray) == count($valuesArray))
						{
							for($i=0;$i<count($insertArray)-1;$i++)
							{
								$var = trim($insertArray[$i]);
								$$var = trim($valuesArray[$i]);
							}

							$game['description'] = !empty($descr) ? ArcadeSpecialChars($descr, 'name') : '';
							$game['extra_data']['type'] = !empty($highscore) ? ArcadeSpecialChars($highscore, 'name') : 0;
							$game['js_insertion'] = 0;
							$game['name'] = !empty($title) ? ArcadeSpecialChars($title, 'name') : $row['game_name'];
							$game['thumbnail'] = !empty($stdimage) ? $stdimage : (!empty($game['thumbnail']) ? $game['thumbnail'] : '');
							$game['thumbnail_small'] = !empty($miniimage) ? $miniimage : (!empty($game['thumbnail_small']) ? $game['thumbnail_small'] : '');
							$game['cover_icon'] = !empty($game['cover_icon']) ? $game['cover_icon'] : '';
							$game['extra_data']['width'] = !empty($width) ? $width : (!empty($game['extra_data']['width']) ? $game['extra_data']['width'] : 0);
							$game['extra_data']['height'] = !empty($height) ? $height : (!empty($game['extra_data']['height']) ? $game['extra_data']['height'] : 0);
						}
					}

					$row['submit_system'] = 'v3arcade';
					if (substr($game['file'], -4) == '.swf')
					{
						$swf->open($sourceDirectory . '/' . $game['file']);

						// Add missing values
						if (!$swf->error)
						{
							$game['extra_data']['flash_version'] = $swf->header['version'];
							$game['flash']['bgcolor'] = empty($game['extra_data']['background_color']) ? $swf->header['background'] : $game['extra_data']['background_color'];
							$game['extra_data']['width'] = empty($game['extra_data']['width']) ? $swf->header['width'] : $game['extra_data']['width'];
							$game['extra_data']['height'] = empty($game['extra_data']['height']) ? $swf->header['height'] : $game['extra_data']['height'];
							$game['extra_data']['type'] = empty($game['extra_data']['type']) ? '' : $game['extra_data']['type'];
						}
						else
							$game['flash']['bgcolor'] = !empty($game['extra_data']['background_color']) ? $game['extra_data']['background_color'] : '000000';
						$swf->close();
						if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
							$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];
						if (!empty($gameinfo['flash']['bgcolor']) && is_array($gameinfo['flash']['bgcolor']) && count($gameinfo['flash']['bgcolor']) == 3)
						{
							$sCSSString = 'rgba(' . $gameinfo['flash']['bgcolor'][0] . ', ' . $gameinfo['flash']['bgcolor'][1] . ', ' . $gameinfo['flash']['bgcolor'][2] . ')';
							$sRegex = '/rgba?(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3}))/i';
							preg_match($sRegex, $sCSSString, $matches);
							if(count($matches) == 4)
							{
								$iRed   = (int) $matches[1];
								$iGreen = (int) $matches[2];
								$iBlue  = (int) $matches[3];
								if ($iRed > 255 || $iGreen > 255 || $iBlue > 255)
								{
									$game['extra_data']['background_color'] = array(
										hexdec('00'),
										hexdec('00'),
										hexdec('00')
									);
								}
								else
									$game['extra_data']['background_color'] = $gameinfo['flash']['bgcolor'];
							}
						}
						elseif (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
						{
							$game['extra_data']['background_color'] = array(
								hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
								hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
								hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
							);
						}
						else
						{
							$game['extra_data']['background_color'] = array(
								hexdec('00'),
								hexdec('00'),
								hexdec('00')
							);
						}
					}
				}
			}

			// best to delete this php file after gathering its data
			@unlink($sourceDirectory . '/' . $mainFile . '.game.php');
		}

		$game['submit_system'] = $row['submit_system'];
		$game['score_type'] = !empty($gameinfo) && !empty($gameinfo['scoring']) ? (int)$gameinfo['scoring'] : 0;
		$game['score_type'] = !empty($gameinfo) && !empty($gameinfo['score_type']) ? (int) $gameinfo['score_type'] : $game['score_type'];
		$game['js_insertion'] = !empty($gameinfo) && !empty($gameinfo['js_insertion']) ? (int) $gameinfo['js_insertion'] : 0;

		if (!empty($gameinfo['thumbnail']))
			$game['thumbnail'] = $gameinfo['thumbnail'];
		if (!empty($gameinfo['thumbnail-small']))
			$game['thumbnail_small'] = $gameinfo['thumbnail-small'];
		if (!empty($gameinfo['cover_icon']))
			$game['cover_icon'] = $gameinfo['cover_icon'];


		$game_directory = $game['directory'];

		// Move files if necessary
		if ($game_directory != $internal_name && $move_games)
		{
			if (!is_dir($upload_directory . '/' . $internal_name) && !mkdir($upload_directory . '/' . $internal_name, 0755))
			{
				$moveFail = true;
				$game['error'] = array('directory_make_failed', array($upload_directory . '/' . $internal_name));

				continue;
			}

			if (!is_writable($upload_directory . '/' . $internal_name))
				@chmod($upload_directory . '/' . $internal_name, 0755);

			$renames = array(
				$directory . '/' . $game['file'] => $upload_directory . '/' . $internal_name . '/' . $game['file'],
			);

			if (!empty($game['thumbnail']))
				$renames[$directory . '/' . $game['thumbnail']] = $upload_directory . '/' . $internal_name . '/' . $game['thumbnail'];

			if (!empty($game['thumbnail_small']))
				$renames[$directory . '/' . $game['thumbnail_small']] = $upload_directory . '/' . $internal_name . '/' . $game['thumbnail_small'];

			if (!empty($game['cover_icon']))
				$renames[$directory . '/' . $game['cover_icon']] = $upload_directory . '/' . $internal_name . '/' . $game['cover_icon'];

			foreach ($renames as $from => $to)
			{
				if (!file_exists($from) && file_exists($to))
					continue;

				if (!rename($from, $to))
				{
					$moveFail = true;
					$game['error'] = array('file_move_failed', array($from, $to));
					continue;
				}
			}

			if (!$moveFail)
			{
				$game_directory = $internal_name;
				$directory = $upload_directory . '/' . $game_directory;
			}
		}

		// override some settings if the xml configuration file is available
		if(file_exists($upload_directory . '/' .$game_directory . '/game-info.xml'))
			$mainXmlFileFind = $upload_directory . '/' .$game_directory . '/game-info.xml';
		elseif (file_exists($upload_directory . '/' . $game_directory . '/' . $mainFile . '.xml'))
			$mainXmlFileFind = $upload_directory . '/' . $game_directory . '/' . $mainFile . '.xml';
		elseif(file_exists($upload_directory . '/' . $game_directory . '/' . $game['internal_name'] . '.xml'))
			$mainXmlFileFind = $upload_directory . '/' . $game_directory . '/' . $game['internal_name'] . '.xml';
		else
			$mainXmlFileFind = '';

		// override some settings if the php configuration file is available and the xml file is not available
		if (file_exists($upload_directory . '/' . $game_directory . '/' . $mainFile . '.php'))
			$mainPhpFileFind = $upload_directory . '/' . $game_directory . '/' . $mainFile . '.php';
		elseif(file_exists($upload_directory . '/' . $game_directory . '/' . $game['internal_name'] . '.php'))
			$mainPhpFileFind = $upload_directory . '/' . $game_directory . '/' . $game['internal_name'] . '.php';
		else
			$mainPhpFileFind = '';

		$infoFile = false;
		if (!empty($mainXmlFileFind))
		{
			$gameinfo = readGameInfo($mainXmlFileFind);
			$infoFile = true;
		}
		elseif(!empty($mainPhpFileFind))
		{
			$gameinfo = readPhpGameInfo($game_directory, basename($mainPhpFileFind, ".php"), true);
			$infoFile = true;
			if (!empty($skip))
			{
				$gameinfo['submit'] = 'html5';
				$gameinfo['file'] = $skip;
			}
		}
		else {
			list($xfiles, $xpath) = array(array(), basename($upload_directory . '/' . $game_directory));
			$xfiles = glob($upload_directory . '/' . $game_directory . '/*.{php}', GLOB_BRACE);
			foreach ($xfiles as $xfile) {
				$fileTemp = file_get_contents($xfile);
				if (!empty($fileTemp) && stripos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
					$yfile = pathinfo(basename($xfile), PATHINFO_FILENAME);
					$mainPhpFileFind = $xfile;
					$gameinfo = readPhpGameInfo($game_directory, $yfile, true);
					$hexColor = !empty($gameinfo['extra_data']['background_color']) && is_array($gameinfo['extra_data']['background_color']) ? $gameinfo['extra_data']['background_color'] : '';
					$rom_system = !empty($gameInfo['rom_system']) && $saveType == 'rom' ? $gameInfo['rom_system'] : 'none';
					$rom_flag = !empty($gameInfo['rom_system']) && $saveType == 'rom' ? 1 : 0;
					$infoFile = true;
					if (!empty($skip))
					{
						$gameinfo['submit'] = 'html5';
						$gameinfo['file'] = $skip;
					}
					break;
				}
			}
		}

		if (!empty($infoFile))
		{
			$gameinfo['help'] = empty($gameinfo['help']) ? '' : $gameinfo['help'];
			$gameinfo['help'] = empty($gameinfo['help']) && !empty($gameinfo['gkeys']) ? $gameinfo['gkeys'] : $gameinfo['help'];
			$game['help'] = isset($gameinfo['help']) ? $gameinfo['help'] : '';
			$game['description'] = isset($gameinfo['description']) ? $gameinfo['description'] : '';
			$game['js_insertion'] = isset($gameinfo['js_insertion']) ? (int)$gameinfo['js_insertion'] : 0;
			if (isset($gameinfo['flash']))
			{
				if (!empty($gameinfo['flash']['width']) && is_numeric($gameinfo['flash']['width']))
					$game['extra_data']['width'] = (int)$gameinfo['flash']['width'];
				if (!empty($gameinfo['flash']['height']) && is_numeric($gameinfo['flash']['height']))
					$game['extra_data']['height'] = (int)$gameinfo['flash']['height'];
				if (!empty($gameinfo['flash']['version']) && is_numeric($gameinfo['flash']['version']))
					$game['extra_data']['flash_version'] = (int)$gameinfo['flash']['version'];
				if (!empty($gameinfo['flash']['type']))
					$game['extra_data']['type'] =  ArcadeSpecialChars($gameinfo['flash']['type'], 'name');
				if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
					$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];
				if (!empty($gameinfo['flash']['bgcolor']) && is_array($gameinfo['flash']['bgcolor']) && count($gameinfo['flash']['bgcolor']) == 3)
				{
					$sCSSString = 'rgba(' . $gameinfo['flash']['bgcolor'][0] . ', ' . $gameinfo['flash']['bgcolor'][1] . ', ' . $gameinfo['flash']['bgcolor'][2] . ')';
					$sRegex = '/rgba?(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3}))/i';
					preg_match($sRegex, $sCSSString, $matches);
					if(count($matches) == 4)
					{
						$iRed   = (int) $matches[1];
						$iGreen = (int) $matches[2];
						$iBlue  = (int) $matches[3];
						if ($iRed > 255 || $iGreen > 255 || $iBlue > 255)
						{
							$game['extra_data']['background_color'] = array(
								hexdec('00'),
								hexdec('00'),
								hexdec('00')
							);
						}
						else
							$game['extra_data']['background_color'] = $gameinfo['flash']['bgcolor'];
					}
				}
				elseif (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
				{
					$game['extra_data']['background_color'] = array(
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
					);
				}
				else
				{
					$game['extra_data']['background_color'] = array(
						hexdec('00'),
						hexdec('00'),
						hexdec('00')
					);
				}
			}
		}

		if(!empty($mainPhpFileFind))
		{
			$imageArray = array('gif', 'png', 'jpg');
			/*
			$file = file_get_contents($mainPhpFileFind);
			if (strpos($file, '$config = array(') !== false)
				@require_once($mainPhpFileFind);
			*/

			foreach ($imageArray as $type)
			{
				if (empty($game['thumbnail']))
				{
					if (file_exists($sourceGamePath . '1.' . $type))
						$game['thumbnail'] = $filename . '1.' . $type;
					elseif (file_exists($sourceGamePath . '.' . $type))
						$game['thumbnail'] = $filename . '.' . $type;
				}

				if (empty($game['thumbnail_small'])) {
					if (file_exists($sourceGamePath . '2.' . $type))
						$game['thumbnail_small'] = $filename . '2.' . $type;
					elseif (file_exists($sourceGamePath . '.' . $type))
						$game['thumbnail_small'] = $filename . '.' . $type;
				}

				if (empty($game['cover_icon']))
				{
					if (file_exists($sourceGamePath . '3.' . $type))
						$game['cover_icon'] = $filename . '3.' . $type;
					elseif (file_exists($sourceGamePath . '.' . $type))
						$game['cover_icon'] = $filename . '.' . $type;
				}
			}

			if (substr($game['file'], -4) == '.swf')
			{
				$swf->open($upload_directory . '/' . $game_directory . '/' . $game['file']);
				$game['js_insertion'] = !empty($game['js_insertion']) ? (int)$game['js_insertion'] : 0;
				// Add missing values
				if (!$swf->error)
				{
					$game['extra_data']['flash_version'] = $swf->header['version'];
					$game['flash']['bgcolor'] = empty($game['extra_data']['background_color']) ? $swf->header['background'] : $game['extra_data']['background_color'];
					$game['extra_data']['width'] = empty($game['extra_data']['width']) ? $swf->header['width'] : $game['extra_data']['width'];
					$game['extra_data']['height'] = empty($game['extra_data']['height']) ? $swf->header['height'] : $game['extra_data']['height'];
					$game['extra_data']['type'] = empty($game['extra_data']['type']) ? '' : $game['extra_data']['type'];
				}
				else
					$game['flash']['bgcolor'] = !empty($game['extra_data']['background_color']) ? $game['extra_data']['background_color'] : '000000';
				$swf->close();

				if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
					$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];
				if (!empty($gameinfo['flash']['bgcolor']) && is_array($gameinfo['flash']['bgcolor']) && count($gameinfo['flash']['bgcolor']) == 3)
				{
					$sCSSString = 'rgba(' . $gameinfo['flash']['bgcolor'][0] . ', ' . $gameinfo['flash']['bgcolor'][1] . ', ' . $gameinfo['flash']['bgcolor'][2] . ')';
					$sRegex = '/rgba?(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3}))/i';
					preg_match($sRegex, $sCSSString, $matches);
					if(count($matches) == 4)
					{
						$iRed   = (int) $matches[1];
						$iGreen = (int) $matches[2];
						$iBlue  = (int) $matches[3];
						if ($iRed > 255 || $iGreen > 255 || $iBlue > 255)
						{
							$game['extra_data']['background_color'] = array(
								hexdec('00'),
								hexdec('00'),
								hexdec('00')
							);
						}
						else
							$game['extra_data']['background_color'] = $gameinfo['flash']['bgcolor'];
					}
				}
				elseif (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
				{
					$game['extra_data']['background_color'] = array(
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
						hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
					);
				}
				else
				{
					$game['extra_data']['background_color'] = array(
						hexdec('00'),
						hexdec('00'),
						hexdec('00')
					);
				}
			}
		}

		// Ensure game icons are set if they exist
		$imageArray = array('gif', 'png', 'jpg');
		$imgFileCheck = $upload_directory . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
		$defaultArcIconsDir = str_replace('\\', '/', $settings['default_theme_dir']) . '/images/arc_icons';
		foreach ($imageArray as $type)
		{
			$game['thumbnail_small'] = empty($game['thumbnail_small']) || is_array($game['thumbnail_small']) ? '' : $game['thumbnail_small'];
			$game['thumbnail'] = empty($game['thumbnail']) || is_array($game['thumbnail']) ? '' : $game['thumbnail'];
			$game['cover_icon'] = empty($game['cover_icon']) || is_array($game['cover_icon']) ? '' : $game['cover_icon'];

			if (empty($game['thumbnail']) || !file_exists($imgFileCheck . '/' . $game['thumbnail']))
			{
				if (file_exists($sourceGamePath . '1.' . $type))
					$game['thumbnail'] = $filename . '1.' . $type;
				elseif (file_exists($sourceGamePath . '.' . $type))
					$game['thumbnail'] = $filename . '.' . $type;
				elseif (file_exists($imgFileCheck . '/' . $filename . $type))
					$game['thumbnail'] = $filename . '.' . $type;
			}

			if (empty($game['thumbnail_small']) || !file_exists($imgFileCheck . '/' . $game['thumbnail_small']))
			{
				if (file_exists($sourceGamePath . '2.' . $type))
					$game['thumbnail_small'] = $filename . '2.' . $type;
				elseif (file_exists($imgFileCheck . '/' . $filename . $type))
					$game['thumbnail_small'] = $filename . '.' . $type;
			}

			if (empty($game['cover_icon']) || !file_exists($imgFileCheck . '/' . $game['cover_icon']))
			{
				if (file_exists($sourceGamePath . '3.' . $type))
					$game['cover_icon'] = $filename . '3.' . $type;
				elseif (file_exists($sourceGamePath . '.' . $type))
					$game['cover_icon'] = $filename . '.' . $type;
				elseif (file_exists($imgFileCheck . '/' . $filename . $type))
					$game['cover_icon'] = $filename . '.' . $type;
			}

			// repeat the process but remove any integers at the end of the existing file name
			if (empty($game['thumbnail']) || !file_exists($imgFileCheck . '/' . $game['thumbnail']))
			{
				$newfilename = preg_replace ( '/[0-9]$/' , '' , $sourceGamePath);
				if (file_exists($newfilename . '1.' . $type))
					$game['thumbnail'] = $newfilename . '1.' . $type;
				elseif (file_exists($newfilename . '.' . $type))
					$game['thumbnail'] = $newfilename . '.' . $type;
			}

			if (empty($game['thumbnail_small']) || !file_exists($imgFileCheck . '/' . $game['thumbnail_small']))
			{
				$newfilename = preg_replace ( '/[0-9]$/' , '' , $sourceGamePath);
				if (file_exists($newfilename . '2.' . $type))
					$game['thumbnail_small'] = $newfilename . '2.' . $type;
			}

			if (empty($game['cover_icon']) || !file_exists($imgFileCheck . '/' . $game['cover_icon']))
			{
				$newfilename = preg_replace ( '/[0-9]$/' , '' , $sourceGamePath);
				if (file_exists($newfilename . '3.' . $type))
					$game['cover_icon'] = $newfilename . '3.' . $type;
				elseif (file_exists($newfilename . '.' . $type))
					$game['cover_icon'] = $newfilename . '.' . $type;
			}
		}
		$game['thumbnail'] = is_array($game['thumbnail'])? $game['thumbnail'][0] : $game['thumbnail'];
		$game['thumbnail_small'] = is_array($game['thumbnail_small']) ? $game['thumbnail_small'][0] : $game['thumbnail_small'];
		$game['cover_icon'] = is_array($game['cover_icon'])? $game['cover_icon'][0] : $game['cover_icon'];

		// if they were not set then we can use the default..
		if (empty($game['thumbnail']) || !file_exists($imgFileCheck . '/' . $game['thumbnail']))
		{

			if (file_exists($defaultArcIconsDir . '/game.gif') && !file_exists($imgFileCheck . '/game.gif'))
				@copy($defaultArcIconsDir . '/game.gif', $imgFileCheck . '/game.gif');
			if (file_exists($imgFileCheck . '/game.gif'))
				@chmod($imgFileCheck . '/game.gif', 0644);

			$game['thumbnail'] = 'game.gif';
		}

		if (empty($game['thumbnail_small']) || !file_exists($imgFileCheck . '/' . $game['thumbnail_small']))
		{
			if ($game['thumbnail'] == 'game.gif')
			{
				if (file_exists($defaultArcIconsDir . '/game.gif') && !file_exists($imgFileCheck . '/game.gif'))
					@copy($defaultArcIconsDir . '/game.gif', $imgFileCheck . '/game.gif');
				if (file_exists($imgFileCheck . '/game.gif'))
					@chmod($imgFileCheck . '/game.gif', 0644);

				$game['thumbnail_small'] = '/game.gif';
			}
			elseif (!empty($game['thumbnail']))
				$game['thumbnail_small'] = $game['thumbnail'];
		}

		$newfilename = preg_replace ( '/[0-9]$/' , '' , $sourceGamePath);
		if (empty($game['cover_icon']) || !file_exists($imgFileCheck . '/' . $game['cover_icon']) && !file_exists($imgFileCheck . '/' . $newfilename . '3.jpg'))
		{
			$temp_filename = uniqid('temp', true) . '.jpg';
			if ($game['thumbnail'] != 'game.gif') {
				$ext = pathinfo($imgFileCheck . '/' . $game['thumbnail'], PATHINFO_EXTENSION);
				if ($ext == 'jpeg') {
					copy($imgFileCheck . '/' . $game['thumbnail'], $imgFileCheck . '/' . $temp_filename);
					if (!$source_image = @imagecreatefromjpeg($imgFileCheck . '/' . $temp_filename)) {
						$source_image = imagecreatefromstring(file_get_contents($imgFileCheck . '/' . $temp_filename));
					}

				}
				elseif ($ext != 'jpg') {
					$newfname = arcade_convertImageType($imgFileCheck . '/' . $game['thumbnail'], $imgFileCheck . '/' . $temp_filename);
					if (!$source_image = @imagecreatefromjpeg($newfname)) {
						$source_image = imagecreatefromstring(file_get_contents($newfname));
					}
				}
				elseif (!$source_image = @imagecreatefromjpeg($imgFileCheck . '/' . $game['thumbnail'])) {
					$source_image = imagecreatefromstring(file_get_contents($imgFileCheck . '/' . $game['thumbnail']));
				}
				list($source_imagex, $source_imagey) = array(imagesx($source_image), imagesy($source_image));
				list($dest_imagex, $dest_imagey) = array(150, 197);
				$dest_image = imagecreatetruecolor($dest_imagex, $dest_imagey);
				imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $dest_imagex, $dest_imagey, $source_imagex, $source_imagey);
				imagejpeg($dest_image, $newfilename . '3.jpg');
				$game['cover_icon'] = basename($newfilename) . '3.jpg';
				if (file_exists($imgFileCheck . '/' . $temp_filename)) {
					unlink($imgFileCheck . '/' . $temp_filename);
				}
			}
			else {
				$coverfile = empty($rom_flag) ? 'arcadegamecover.png' : 'romgamecover.gif';
				if (file_exists($defaultArcIconsDir . '/' . $coverfile) && !file_exists($imgFileCheck . '/' . $coverfile))
					@copy($defaultArcIconsDir . '/' . $coverfile, $imgFileCheck . '/' . $coverfile);
				if (file_exists($imgFileCheck . '/' . $coverfile))
					@chmod($imgFileCheck . '/' . $coverfile, 0644);

				$game['cover_icon'] = $coverfile;
			}
		}
		elseif (empty($game['cover_icon']) && file_exists($imgFileCheck . '/' . $newfilename . '3.jpg')) {
			$game['cover_icon'] = $newfilename . '3.jpg';
		}

		// Ensure extra data was set for swf file
		if ((empty($game['extra_data']['width']) || empty($game['extra_data']['height']) || empty($game['extra_data']['version']) || empty($game['extra_data']['background_color'])) && substr($game['file'], -4) == '.swf')
		{
			$dimensions = '';
			$swf->open($sourceGamePath . '.swf');
			if (!$swf->error)
			{
				$game['extra_data'] += array(
					'width' => !empty($game['extra_data']['width']) ? $game['extra_data']['width'] : $swf->header['width'],
					'height' => !empty($game['extra_data']['height']) ? $game['extra_data']['height'] : $swf->header['height'],
					'flash_version' => !empty($game['extra_data']['version']) ? $game['extra_data']['version'] : $swf->header['version'],
					'bgcolor' => !empty($game['flash']['bgcolor']) ? $game['flash']['bgcolor'] : '',
					'type' => !empty($gameinfo['flash']['type']) ? ArcadeSpecialChars($gameinfo['flash']['type'], 'name') : '',
				);
				$gameinfo['flash']['bgcolor'] = !empty($game['extra_data']['background_color']) ? $game['extra_data']['background_color'] : $swf->header['background'];
			}
			else
				$gameinfo['flash']['bgcolor'] = !empty($game['extra_data']['background_color']) ? $game['extra_data']['background_color'] : '000000';

			$swf->close();
			if (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 3)
				$gameinfo['flash']['bgcolor'] = $gameinfo['flash']['bgcolor'] . $gameinfo['flash']['bgcolor'];
			if (!empty($gameinfo['flash']['bgcolor']) && is_array($gameinfo['flash']['bgcolor']) && count($gameinfo['flash']['bgcolor']) == 3)
			{
				$sCSSString = 'rgba(' . $gameinfo['flash']['bgcolor'][0] . ',' . $gameinfo['flash']['bgcolor'][1] . ',' . $gameinfo['flash']['bgcolor'][2] . ')';
				$sRegex = '~rgba?(\s?([0-9]{1,3}),\s?([0-9]{1,3}),\s?([0-9]{1,3}))~i';
				preg_match($sRegex, $sCSSString, $matches);
				if(count($matches) == 4)
				{
					$iRed   = (int) $matches[1];
					$iGreen = (int) $matches[2];
					$iBlue  = (int) $matches[3];
					if ($iRed > 255 || $iGreen > 255 || $iBlue > 255)
					{
						$game['extra_data']['background_color'] = array(
							hexdec('00'),
							hexdec('00'),
							hexdec('00')
						);
					}
					else
						$game['extra_data']['background_color'] = $gameinfo['flash']['bgcolor'];
				}
			}
			elseif (!empty($gameinfo['flash']['bgcolor']) && is_string($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6)
			{
				$game['extra_data']['background_color'] = array(
					hexdec(mb_substr($gameinfo['flash']['bgcolor'], 0, 2)),
					hexdec(mb_substr($gameinfo['flash']['bgcolor'], 2, 2)),
					hexdec(mb_substr($gameinfo['flash']['bgcolor'], 4, 2))
				);
			}
			else
			{
				$game['extra_data']['background_color'] = array(
					hexdec('00'),
					hexdec('00'),
					hexdec('00')
				);
			}
			if(empty($game['extra_data']['width']))
			{
				// Ensure game dimensions were set for phpbb type
				$tihi = array();
				if ($row['submit_system'] == 'phpbb')
				{
					$tihi_file = $upload_directory . '/' . (!empty($row['game_directory']) ? $row['game_directory'] . '/size.txt' : 'size.txt');
					if (file_exists($tihi_file))
					{
						$file = fopen($tihi_file, 'r');
						$dimensions .= fgets($file);
						fclose($file);
						$tihi = explode('x', mb_strtolower($dimensions));
					}
				}

				$width = !empty($tihi) ? preg_replace("/[^0-9]/", '', $tihi[0]) : (!empty($game['extra_data']['width']) ? $game['extra_data']['width'] : 600);
				$height = !empty($tihi) ? preg_replace("/[^0-9]/", '', $tihi[1]) : (!empty($game['extra_data']['height']) ? $game['extra_data']['height'] : 400);
				$game['extra_data'] = array(
					'width' => (int)$width,
					'height' => (int)$height,
					'flash_version' => !empty($game['extra_data']['flash_version']) ? $game['extra_data']['flash_version'] : 0,
					'background_color' => $game['extra_data']['background_color'],
					'type' => !empty($game['extra_data']['type']) ? $game['extra_data']['type'] : '',
				);
			}
		}

		// Final install data
		$game['score_type'] = !empty($gameinfo['scoring']) ? $gameinfo['scoring'] : (!empty($gameinfo['score_type']) ? $gameinfo['score_type'] : 0);
		$game['name'] = !empty($gameinfo['name']) ? $gameinfo['name'] : (!empty($game['name']) ? $game['name'] : '');
		$game['description'] = !empty($gameinfo['description']) ? $gameinfo['description'] : (!empty($game['description']) ? $game['description'] : '');
		$game['js_insertion'] = !empty($gameinfo['js_insertion']) ? (int)$gameinfo['js_insertion'] : 0;
		$gameinfo['help'] = empty($gameinfo['help']) ? '' : $gameinfo['help'];
		$gameinfo['help'] = empty($gameinfo['help']) && !empty($gameinfo['gkeys']) ? $gameinfo['gkeys'] : $gameinfo['help'];
		$gameinfo['help'] = !empty($gameinfo['help']) && $gameinfo['help'] != $game['description'] ? $gameinfo['help'] : '';
		$game['help'] = !empty($gameinfo['help']) ? $gameinfo['help'] : (!empty($game['help']) && $game['help'] != $game['description'] ? $game['help'] : '');
		$game['description'] = str_replace(array('&#039;', '&apos;', '&#034;', '&#34;', '&#038', '&#38;'), array('&#39;', '&#39;', '&quot;', '&quot;', '&amp;', '&amp;'), $game['description']);
		$game['help'] = str_replace(array('&#039;', '&apos;', '&#034;', '&#34;', '&#038', '&#38;'), array('&#39;', '&#39;', '&quot;', '&quot;', '&amp;', '&amp;'), $game['help']);
		$game['category'] = $set_category;
		$game['rom_flag'] = !empty($gameinfo['rom_flag']) ? 1 : 0;
		$game['rom_system'] = !empty($gameinfo['rom_system']) ? $gameinfo['rom_system'] : 'none';
		$game['submit_system'] = !empty($gameinfo['rom_flag']) ? 'rom' : $game['submit_system'];

		// imagick processing
		if (!empty($arcadeModSettings['arcadeFilterImagickAdmin'])) {
			$th = !empty($arcadeModSettings['arcadeCropThumbnails']) ? 1 : 0;
			$cv = !empty($arcadeModSettings['arcadeCropCoverArt']) ? 1 : 0;
			ArcadeImageResize($directory . '/' . $game['thumbnail'], $directory . '/' . $game['thumbnail'], 150, 150, $th);
			ArcadeImageResize($directory . '/' . $game['thumbnail_small'], $directory . '/' . $game['thumbnail_small'], 150, 150, $th);
			ArcadeImageResize($directory . '/' . $game['cover_icon'], $directory . '/' . $game['cover_icon'], 300, 300, $cv);
		}

		if (!empty($gameInfo['romflag']) && !empty($game_directory)) {
			$romTypes = $arcadeModSettings['arcadeRomGameTypes'] . '|' . mb_strtoupper($arcadeModSettings['arcadeRomGameTypes']);
			$dir = $upload_directory . (!empty($game_directory) ? '/' . preg_replace('/^rom_/i', '', $game_directory) : '');
			$files1 = preg_grep('~\.(' . $romTypes . ')$~i', scandir($dir));
			if (count($files1) == 1) {
				$ext1 = pathinfo($files1[0], PATHINFO_EXTENSION);
				if (mb_strtolower($ext1) == 'zip') {
					$zip = new ZipArchive;
					if ($zip->open($dir . '/' . $files1[0]) === TRUE) {
						$zip->extractTo($dir);
						$zip->close();
						$files2 = preg_grep('~\.(' . (str_ireplace('zip|7z|', '', $romTypes)) . ')$~', scandir($dir));
						if (count($files2) == 1) {
							unlink($dir . '/' . $files1[0]);
							$game['file'] = $files2[0];
						}
					}
				}
				else
					$game['file'] = $files1[0];
			}
		}

		$gameOptions = array(
			'internal_name' => $game['internal_name'],
			'initialInternalName' => $initialInternalName,
			'name' => $game['name'],
			'description' => !empty($game['description']) ? $game['description'] : '',
			'thumbnail' => $game['thumbnail'],
			'thumbnail_small' => $game['thumbnail_small'],
			'cover_icon' => !empty($game['cover_icon']) ? $game['cover_icon'] : '',
			'help' => (!empty($game['help']) ? $game['help'] : (!empty($gameinfo['help']) ? $gameinfo['help'] : '')),
			'game_file' => $game['submit_system'] == 'html52' && empty($fileTrip) ? 'gamedata/' . $game['internal_name'] . '/index.html' : $game['file'],
			'game_directory' => $game_directory,
			'submit_system' => empty($skip) ? $game['submit_system'] : 'html5',
			'rom_system' => !empty($game['rom_system']) ? $game['rom_system'] : 'none',
			'rom_flag' => !empty($game['rom_flag']) ? 1 : 0,
			'score_type' => $game['score_type'],
			'js_insertion' => $game['js_insertion'],
			'extra_data' => $game['extra_data'],
			'category' => $set_category,
		);
		if (!empty($game['rom_flag']) && substr($gameOptions['game_file'], -4) == '.php') {
			$gameOptions['game_file'] = $gameinfo['file'];
		}
		$success = false;
		if (!isset($game['error']) && $id_game = createGame($gameOptions))
			$success = true;
		elseif (!in_array($game_directory, $directories))
		{
			$gamefile = $game['submit_system'] == 'html52' || $game['submit_system'] == 'html53' ? $game['internal_name'] . '.php' : $game['file'];
			$files = array_unique(
				array(
					$gamefile,
					!empty($game['thumbnail']) ? $game['thumbnail'] : '',
					!empty($game['thumbnail_small']) ? $game['thumbnail_small'] : '',
					!empty($game['cover_icon']) ? $game['cover_icon'] : '',
					mb_substr($gamefile, 0, -4) . '.php',
					mb_substr($gamefile, 0, -4) . '-game-info.xml',
					mb_substr($gamefile, 0, -4) . '.xap',
					mb_substr($gamefile, 0, -5) . '.html',
					mb_substr($gamefile, 0, -4) . '.ini',
					mb_substr($gamefile, 0, -4) . '.gif',
					mb_substr($gamefile, 0, -4) . '.png',
					mb_substr($gamefile, 0, -4) . '.jpg',
				)
			);
			$dest = $upload_directory . '/' . $game_directory;
			$dest = rtrim($dest, '/');
			$files = array_filter($files);
			foreach ($files as $key => $data)
			{
				if ((!empty($files[$key])) && file_exists($dest . '/' . $files[$key]))
					@unlink($dest . '/' . $files[$key]);
			}

			if ((!empty($dest)) && $dest !== $upload_directory && $dest !== $boarddir)
			{
				if (dirname($dest) !== $upload_directory)
					arcadeRmdir($dest);

				$gfiles = ArcadeAdminScanDir($dest, '');
				if (empty($gfiles) && is_dir($dest))
					arcadeRmdir($dest);
				elseif ((count($gfiles) == 1) && $gfiles[0] == 'master-info.xml')
				{
					@unlink($dest . '/master-info.xml');
					arcadeRmdir($dest);
				}
			}

			if (is_dir($boarddir . '/arcade/gamedata/' . $game['internal_name']))
			{
				$gdfiles = ArcadeAdminScanDir($boarddir . '/arcade/gamedata/' . $game['internal_name'], '');
				foreach ($gdfiles as $file)
					@unlink($file);

				deleteArcadeArchives($boarddir . '/arcade/gamedata/' . $game['internal_name']);
			}

			$game['error'] = array('arcade_install_general_fail', array('path', $game_directory));
		}
		else
		{
			$exists = true;
			$game['error'] = array('arcade_install_exists_fail_del', array('path', $game_directory));
			$gameDir = str_replace('\\', '/', $game_directory);
			$gameDir = rtrim($gameDir, '/');
			if (!empty($gameDir))
				$gameDirx = explode('/', $gameDir);
			else
				$gameDirx[0] = '';

			if (!empty($game_directory) && $upload_directory . '/' !== $upload_directory . '/' . $gameDirx[0])
			{
				foreach (array('.zip', '.tar', '.rar', '.tar.gz', '.ZIP', '.TAR', '.RAR', '.TAR.gz', 'TAR.GZ') as $ext)
					if (file_exists($upload_directory . '/' . $gameDirx[0] . $ext))
						@unlink($upload_directory . '/' . $gameDirx[0] . $ext);
			}
		}

		if (!empty($success))
		{
			// move game folder to main games directory
			if ($sourceDirectory != $upload_directory && $directory != $boarddir && $directory != $arcadeModSettings['gamesDirectory'] && $directory != $arcadeModSettings['romGamesDirectory']) {
				copyArcadeDirectory($sourceDirectory, $directory);
				if (is_dir($sourceDirectory) && is_dir($directory)) {
					arcadeRmdir($sourceDirectory);
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}arcade_files
						WHERE id_file = {int:file} || id_game = {int:gameid}',
						array(
							'file' => $game['id_file'],
							'gameid' => !empty($id_game) ? $id_game : 0,
						)
					);
				}
				elseif (!empty($arcadeModSettings['arcade_log_install_game'])) {
					$error = sprintf($txt['file_move_failed'], $sourceDirectory, $directory);
					log_error($error . ' [1]', 'debug');
				}
			}
		}

		$status[] = array(
			'id' => $id_game,
			'name' => $game['name'],
			'error' => isset($game['error']) ? $game['error'] : false,
		);
		if (!empty($deleteFile) && file_exists($deleteFile))
			@unlink($deleteFile);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($success) && !empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		$gameRelatedCache = array(
			'arcade_games_suggest',
			'arcade_games_recommended',
			'arcade_games_query_small',
			'arcade_games_latestA',
			'arcade_new_gamesA',
			'arcade_thumbsA0',
			'arcade_thumbsA1',
			'arcade_thumbsA2',
			'arcade_thumbsA3',
			'arcade_newestB',
			'arcade_games_latestC',
			'arcade_newestC',
			'arcade_games_nocat',
		);
		foreach ($gameRelatedCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}

	return $status;
}

function arcade_convertImageType($filePath, $dest)
{
	$image = imagecreatefromstring(
	   file_get_contents($filePath)
	);
	ob_start();
	imagejpeg($image);

	$imageData = ob_get_contents();
	ob_end_clean();
	file_put_contents($dest, $imageData);
	clearstatcache();
	if (file_exists($dest)) {
		return $dest;
	}
	else {
		return $filePath;
	}
}

function cleanRomPathName($filename)
{
	global $arcadeModSettings;
	$types = '.' . str_replace('|', '|.', $arcadeModSettings['arcadeRomGameTypes']);
	$romTypes = explode('|', $types);
	foreach ($romTypes as $type) {
		if (strpos($filename, '.') === FALSE)
			break;
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$filename = rtrim($filename, '.' . $ext);
	}
	$clean = str_replace(array(",", ".", "\\", "/", "<", ">", "@", "%", "&", "^", "=", "|"), "", $filename);

	return $clean;
}

function createNewRomGame($gameinfo, $set_category, $game = array())
{
	global $arcadeModSettings, $boarddir;
	// Final install data
	$game['score_type'] = !empty($gameinfo['score_type']) ? intval($gameinfo['score_type']) : (!empty($gameinfo['scoring']) ? intval($gameinfo['scoring']) : 2);
	$game['name'] = !empty($gameinfo['name']) ? $gameinfo['name'] : (!empty($game['name']) ? $game['name'] : '');
	$game['description'] = !empty($gameinfo['description']) ? $gameinfo['description'] : (!empty($game['description']) ? $game['description'] : '');
	$game['description'] = arcadeFilterDbInstallText($game['description']);
	$game['js_insertion'] = !empty($gameinfo['js_insertion']) ? (int)$gameinfo['js_insertion'] : 0;
	$gameinfo['help'] = empty($gameinfo['help']) ? '' : $gameinfo['help'];
	$gameinfo['help'] = empty($gameinfo['help']) && !empty($gameinfo['gkeys']) ? $gameinfo['gkeys'] : $gameinfo['help'];
	$gameinfo['help'] = !empty($gameinfo['help']) && $gameinfo['help'] != $game['description'] ? $gameinfo['help'] : '';
	$game['help'] = !empty($gameinfo['help']) ? $gameinfo['help'] : (!empty($game['help']) && $game['help'] != $game['description'] ? $game['help'] : '');
	$game['help'] = arcadeFilterDbInstallText($game['help']);
	$game['description'] = str_replace(array('&#039;', '&apos;', '&#034;', '&#34;', '&#038', '&#38;'), array('&#39;', '&#39;', '&quot;', '&quot;', '&amp;', '&amp;'), $game['description']);
	$game['help'] = str_replace(array('&#039;', '&apos;', '&#034;', '&#34;', '&#038', '&#38;'), array('&#39;', '&#39;', '&quot;', '&quot;', '&amp;', '&amp;'), $game['help']);
	$game['category'] = $set_category;
	$game['rom_flag'] = !empty($gameinfo['rom_flag']) ? 1 : 0;
	$game['rom_system'] = !empty($gameinfo['rom_system']) ? $gameinfo['rom_system'] : 'none';
	$game['submit_system'] = !empty($gameinfo['rom_flag']) ? 'rom' : $game['submit_system'];
	$game['translate_skip'] = !empty($gameinfo['translate_skip']) ? 1 : 0;
	$width = !empty($gameinfo['flash']['width']) ? intval($gameinfo['flash']['width']) : 960;
	$height = !empty($gameinfo['flash']['height']) ? intval($gameinfo['flash']['height']) : 540;
	$bgcolor = !empty($gameinfo['flash']['bgcolor']) && strlen($gameinfo['flash']['bgcolor']) == 6 ? strval($gameinfo['flash']['bgcolor']) : '000000';
	$exlink = !empty($gameinfo['extra_data']['external_link']) ? $gameinfo['extra_data']['external_link'] : (!empty($gameinfo['flash']['external_link']) ? $gameinfo['flash']['external_link'] : '');
	$game['extra_data'] = array('width' => $width, 'height' => $height, 'flash_version' => 0, 'background_color' => array(hexdec(mb_substr($bgcolor, 0, 2)), hexdec(mb_substr($bgcolor, 2, 2)), hexdec(mb_substr($bgcolor, 4, 2))), 'external_link' => $exlink);
	$game['file'] = $gameinfo['file'];
	$game['thumbnail'] = !empty($gameinfo['thumbnail']) ? $gameinfo['thumbnail'] : '';
	$game['thumbnail_small'] = !empty($gameinfo['thumbnail_small']) ? $gameinfo['thumbnail_small'] : '';
	$game['cover_icon'] = !empty($gameinfo['cover_icon']) ? $gameinfo['cover_icon'] : '';
	$game_directory = cleanRomPathName($game['file']);
	$internal_name = cleanRomPathName($game['file']);
	$numb = 0;
	$internals = arcadeInternalName($game['name']);
	$upload_dir = !empty($game['upload_dir']) ? $game['upload_dir'] : 'games_upload';
	while(in_array($internal_name, $internals))
	{
		$numb++;
		$internal_name = $numb > 1 ? substr($internal_name, 0, -1) . (string)$numb : $internal_name . (string)$numb;
	}
	if (!empty($game_directory)) {
		$files1 = array();
		$romTypes = $arcadeModSettings['arcadeRomGameTypes'] . '|' . mb_strtolower($arcadeModSettings['arcadeRomGameTypes']);
		$dir = $boarddir . '/' . $upload_dir . (!empty($game_directory) ? '/' . preg_replace('~^rom_~i', '', $game_directory) : '');
		$dir = str_replace('\\', '/', $dir);
		$dir = !empty($game['rom_dir']) ? str_replace('/' . $upload_dir . '/', '/' . $arcadeModSettings['romGamesDirectory'] . '/', $dir) : $dir;
		if (is_dir($dir)) {
			$files1 = preg_grep('~\.(' . $romTypes . ')$~', scandir($dir));
		}
		if (count($files1) == 1) {
			$files1 = array_values(array_filter($files1));
			$game['file'] = $files1[0];
			$ext1 = pathinfo($files1[0], PATHINFO_EXTENSION);
			// this won't fire as it is & is left for possible future changes
			if ($ext1 == 'zipper') {
				$zip = new ZipArchive;
				if ($zip->open($dir . '/' . $files1[0]) === TRUE) {
					$zip->extractTo($dir);
					$zip->close();
					$files2 = preg_grep('~\.(' . (str_ireplace('zip|7z|', '', $romTypes)) . ')$~', scandir($dir));
					if (count($files2) == 1) {
						$files2 = array_values(array_filter($files2));
						@unlink($dir . '/' . $files1[0]);
						$game['file'] = $files2[0];
						if ($files2[0] != rom_sanitize_file_name($files2[0], 'file')) {
							$fixedGameFile = rom_sanitize_file_name($files2[0], 'file');
							@rename($dir . '/' . $files2[0], $dir . '/' . $fixedGameFile);
							if (file_exists($dir . '/' . $fixedGameFile))
								$game['file'] = $fixedGameFile;
						}
					}
				}
			}
			elseif ($files1[0] != rom_sanitize_file_name($files1[0], 'file')) {
				$fixedGameFile = rom_sanitize_file_name($files1[0], 'file');
				@rename($dir . '/' . $files1[0], $dir . '/' . $fixedGameFile);
				if (file_exists($dir . '/' . $fixedGameFile))
					$game['file'] = $fixedGameFile;
			}
		}
		if (is_dir($dir) && stripos($arcadeModSettings['romGamesDirectory'] . '/', $dir) !== FALSE && $dir != $boarddir) {
			$innerPaths = array_filter(glob($dir . '/*'), 'is_dir');
			foreach ($innerPaths as $pathY)
				arcadeRmDir($pathY);
		}
	}

	$gameOptions = array(
		'internal_name' => $internal_name,
		'initialInternalName' => $internal_name,
		'name' => $game['name'],
		'description' => !empty($game['description']) ? $game['description'] : '',
		'thumbnail' => $game['thumbnail'],
		'thumbnail_small' => $game['thumbnail_small'],
		'cover_icon' => !empty($game['cover_icon']) ? $game['cover_icon'] : '',
		'help' => (!empty($game['help']) ? $game['help'] : (!empty($gameinfo['help']) ? $gameinfo['help'] : '')),
		'game_file' => !empty($game['file']) ? $game['file'] : '',
		'game_directory' => $game_directory,
		'submit_system' => empty($skip) ? $game['submit_system'] : 'html5',
		'rom_system' => !empty($game['rom_system']) ? $game['rom_system'] : 'none',
		'rom_flag' => !empty($game['rom_flag']) ? 1 : 0,
		'score_type' => $game['score_type'],
		'scoring' => $game['score_type'],
		'js_insertion' => $game['js_insertion'],
		'extra_data' => $game['extra_data'],
		'category' => $set_category,
		'translate_skip' => $game['translate_skip'],
	);
	if (!empty($game['rom_flag']) && substr($gameOptions['game_file'], -4) == '.php') {
		$gameOptions['game_file'] = $gameinfo['file'];
	}
	$success = array();
	if ($id_game = createGame($gameOptions))
		$success = array($id_game, $game_directory);

	return $success;
}

function unpackGames($games, $move_games = false)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $sourcedir, $smcFunc, $boarddir, $settings;

	if (!is_writable($arcadeModSettings['gamesDirectory']) && !chmod($arcadeModSettings['gamesDirectory'], 0755)) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($arcadeModSettings['gamesDirectory']));
	}

	require_once($sourcedir . '/Subs-Package.php');
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? mb_substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$upload_directory = str_replace('\\', '/', $boarddir . '/games_upload');
	list($countz, $saveType, $gameinfo, $_SESSION['arcade_exists'], $status) = array(0, '', array(), array(), array());

	if (!is_writable($upload_directory) && !chmod($upload_directory, 0755)) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($upload_directory));
	}

	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$classicGameTypes2 = $classicGameTypes;
	if (($key = array_search('ibp32', $classicGameTypes2)) !== FALSE) {
		unset($classicGameTypes2['ibp32']);
	}
	$request = $smcFunc['db_query']('', '
		SELECT f.id_file, f.game_file, f.game_directory, f.submit_system
		FROM {db_prefix}arcade_files AS f
		WHERE f.id_file IN ({array_int:games})
			AND (f.status = 10)',
		array(
			'games' => $games,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		list($target, $saveType, $fileTemp, $data) = array('', '', '', '');
		$countz++;
		$row['game_directory'] = trim(str_replace('\\', '/', $row['game_directory']), '/');
		$from = str_replace('\\', '/', $upload_directory) . '/' . (!empty($row['game_directory']) ? str_replace('\\', '/', $row['game_directory']) . '/' : '') . str_replace('\\', '/', $row['game_file']);

		for ($i=0; $i<2; $i++)
		{
			if (empty($row['game_file']))
				continue;
			$ext1 = pathinfo($row['game_file'], PATHINFO_EXTENSION);
			$target = !empty($row['game_directory']) ? basename($row['game_directory']) : preg_replace('~\.' . $ext1 . '$~i', '', $row['game_file']);
		}
		$target = strlen(mb_substr($target, 6)) > 0 && mb_substr(mb_strtolower($target), 0, 5) == 'game_' ? mb_substr($target, 5) : $target;
		$target = strlen(mb_substr($target, 5)) > 0 && mb_substr(mb_strtolower($target), 0, 4) == 'rom_' ? mb_substr($target, 4) : $target;
		$target = strlen(mb_substr($target, 7)) > 0 && mb_substr(mb_strtolower($target), 0, 6) == 'html5_' ? mb_substr($target, 6) : $target;
		$target = strlen(mb_substr($target, 8)) > 0 && mb_substr(mb_strtolower($target), 0, 7) == 'html52_' ? mb_substr($target, 7) : $target;
		$target = strlen(mb_substr($target, 8)) > 0 && mb_substr(mb_strtolower($target), 0, 7) == 'html53_' ? mb_substr($target, 7) : $target;
		$target = strlen(mb_substr($target, 10)) > 0 && mb_substr(mb_strtolower($target), 0, 9) == 'v3arcade_' ? mb_substr($target, 9) : $target;
		$target = substr(mb_strtolower($target), -3) == '.gz' ? substr_replace($target, '', -3) : $target;
		$target = substr(mb_strtolower($target), -4) == '.zip' || substr(mb_strtolower($target), -4) == '.tar' || substr(mb_strtolower($target), -4) == '.rar' ? substr_replace($target, '', -4) : $target;
		$target = str_replace(array(' '), array(''), $target);
		$target = trim($target, "_./");
		$path = rtrim($upload_directory, '/') . '/' . $target;
		$folder = $path . '/' . trim($target);

		// some people like putting their alias after the game name
		/*
		foreach (array('_origon', '_masodo') as $trailblazer)
			$target = strlen(mb_substr($target, strlen($trailblazer)+1)) > 0 && mb_substr(mb_strtolower($target), -strlen($trailblazer)) == '_origon' ? mb_substr($target, 0, -strlen($trailblazer)) : $target;
		*/
		if (file_exists($upload_directory . '/' . $target))
		{
			// if the directory is empty we can still use it otherwise abort the installation
			$dir_iterator = new RecursiveDirectoryIterator($upload_directory . '/' . $target);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
			$dirFiles = array();
			foreach ($iterator as $file)
				if (mb_substr($file, -1) != '.' && mb_substr($file, -2) != '..')
					$dirFiles[] = $file;

			// if directory exists containing files, remove compressed archives that will resolve to the same directory & display an error message
			if (count($dirFiles) > 0)
			{
				$targety = explode('/', trim($target, '/'));
				$targetx = !empty($targety) ? $targety[0] : trim($target, '/');

				foreach (array('.ZIP', '.TAR', '.TAR.GZ', '.RAR', 'TAR.gz') as $xfile)
				{
					foreach (array('GAME_', 'HTML5_', 'HTML52_', 'HTML53_', 'V3ARCADE', 'ROM_') as $prefix)
					{
						list($lowPrefix, $lowXfile) = array(mb_strtolower($prefix), mb_strtolower($xfile));
						if (file_exists($upload_directory . '/' . $prefix . $targetx . $xfile))
							@unlink($upload_directory . '/' . $prefix . $targetx . $xfile);
						if (file_exists($upload_directory . '/' . $lowPrefix . $targetx . $lowXfile))
							@unlink($upload_directory . '/' . $lowPrefix . $targetx . $lowXfile);
						if (file_exists($upload_directory . '/' . $prefix . $targetx . $lowXfile))
							@unlink($upload_directory . '/' . $prefix . $targetx . $lowXfile);
						if (file_exists($upload_directory . '/' . $lowPrefix . $targetx . $xfile))
							@unlink($upload_directory . '/' . $lowPrefix . $targetx . $xfile);
					}
				}

				if (file_exists($from) && (mb_substr(mb_strtolower($from), -4) == '.zip' || mb_substr(mb_strtolower($from), -4) == '.tar' || mb_substr(mb_strtolower($from), -4) == '.rar' || mb_substr(mb_strtolower($from), -7) == '.tar.gz')) {
					@unlink($from);
				}

				$exname = basename($target);
				$_SESSION['arcade_exists'][] = preg_replace('/\\.[^.\\s]{3,4,6}$/', '', $exname);
				$_SESSION['arcade_exists'] = array_filter($_SESSION['arcade_exists']);
				continue;
				//fatal_lang_error('arcade_directory_make_exists', false, $target);
			}
		}

		if (mb_substr(mb_strtolower($row['game_file']) , -4) == '.zip')
		{
			$path = rtrim($upload_directory, '/') . '/' . $target;
			if ($smfVersion === 'v2.1') {
				if (strlen(mb_substr(basename($from), 5)) > 0 && mb_substr(mb_strtolower(basename($from)), 0, 4) == 'rom_') {
					$files = arcadeUnzip($from, $path . '/', false, false);
				}
				else
					$files = arcadeUnzip($from, $path . '/', true, false);
				if (!is_dir($path) && !empty($arcadeModSettings['arcade_log_install_game'])) {
					$error = sprintf($txt['arcade_decompress_error'], $from);
					log_error($error . ' [2]', 'debug');
				}
				elseif (file_exists($path . '/' . basename($path) . '.php')) {
					$fileTemp = file_get_contents($path . '/' . basename($path) . '.php');
					if (!empty($fileTemp) && strpos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
						$phpInfo = readPhpGameInfo(basename($path), basename($path), true, 1);
						if (!empty($phpInfo) && !empty($phpInfo['rom_flag'])) {
							list($saveType, $set_category) = array('rom', isset($_POST['set_category']) ? floatval($_POST['set_category']) : 0);
							$_SESSION['qaction'] = 'install';
							$rom = createNewRomGame($phpInfo, $set_category, array());
							if (!empty($rom)) {
								@rename($path, $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1]);
								if (empty($phpInfo['thumbnail'])) {
									@copy($settings['default_theme_dir'] . '/images/arc_icons/Default.gif', $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1] . '/Default1.gif');
									$phpInfo['thumbnail'] = 'Default1.gif';
								}
								if (empty($phpInfo['thumbnail_small'])) {
									@copy($settings['default_theme_dir'] . '/images/arc_icons/Default.gif', $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1] . '/Default2.gif');
									$phpInfo['thumbnail_small'] = 'Default2.gif';
								}
								if (empty($phpInfo['covericon'])) {
									@copy($settings['default_theme_dir'] . '/images/arc_icons/romgamecover.png', $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1] . '/romgamecover.png');
									$phpInfo['covericon'] = 'romgamecover.png';
								}
								if (!is_dir($path))
									@unlink($path);
								@unlink(rtrim($upload_directory, '/') . '/rom_' . $target . '.zip');
								$status[] = array(
									'id' => $rom[0],
									'name' => '[ROM] ' . $phpInfo['name'],
									'error' => false,
								);
								continue;
							}
						}
					}
				}
				else {
					list($xfiles, $xpath) = array(array(), basename($path));
					$xfiles = glob($path . '/*.{php}', GLOB_BRACE);
					foreach ($xfiles as $xfile) {
						$fileTemp = file_get_contents($xfile);
						if (!empty($fileTemp) && stripos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
							$yfile = pathinfo(basename($xfile), PATHINFO_FILENAME);
							$phpInfo = readPhpGameInfo($xpath, $yfile, true);
							if (!empty($phpInfo) && !empty($phpInfo['rom_flag'])) {
								list($saveType, $set_category) = array('rom', isset($_POST['set_category']) ? floatval($_POST['set_category']) : 0);
								$_SESSION['qaction'] = 'install';
								$rom = createNewRomGame($phpInfo, $set_category, array());
								if (!empty($rom)) {
									@rename($path, $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1]);
									if (empty($phpInfo['thumbnail'])) {
										@copy($settings['default_theme_dir'] . '/images/arc_icons/Default.gif', $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1] . '/Default1.gif');
										$phpInfo['thumbnail'] = 'Default1.gif';
									}
									if (empty($phpInfo['thumbnail_small'])) {
										@copy($settings['default_theme_dir'] . '/images/arc_icons/Default.gif', $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1] . '/Default2.gif');
										$phpInfo['thumbnail_small'] = 'Default2.gif';
									}
									if (empty($phpInfo['covericon'])) {
										@copy($settings['default_theme_dir'] . '/images/arc_icons/romgamecover.png', $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1] . '/romgamecover.png');
										$phpInfo['covericon'] = 'romgamecover.png';
									}

									if (!is_dir($path))
										@unlink($path);
									@unlink(rtrim($upload_directory, '/') . '/rom_' . $target . '.zip');
									$status[] = array(
										'id' => $rom[0],
										'name' => '[ROM] ' . $phpInfo['name'],
										'error' => false,
									);
									continue;
								}
							}
							break;
						}
					}
				}
			}
			else {
				$files = read_tgz_file($from, $path);
				if (!is_dir($path) && !empty($arcadeModSettings['arcade_log_install_game'])) {
					$error = sprintf($txt['arcade_decompress_error'], $from);
					log_error($error . ' [3]', 'debug');
				}
			}

			$check_parents = arcadeReturnPaths($path);
			if (file_exists($path . '/master-info.xml'))
			{
				$gameinfo = readGameInfo($path . '/master-info.xml');
				$saveType = !empty($gameinfo['submit']) && in_array($gameinfo['submit'], $classicGameTypes2) ? $gameinfo['submit'] : '';
			}

			if (!empty($check_parents) && strpos(mb_strtolower($path), 'gamepack') !== false)
			{
				foreach ($check_parents as $parent)
				{
					$fix_parent = mb_strtolower(str_replace(array(' '), array(''), $parent));
					if (!is_dir(rtrim($upload_directory, '/') . '/' . $fix_parent))
						rename($path . '/' . $parent, rtrim($upload_directory, '/') . '/' . $fix_parent);
					else
					{
						for($i=0;$i<7;$i++)
						{
							if ($i == 6)
							{
								deleteArcadeArchives($path . '/' . $parent);
								break;
							}
							if (!is_dir(rtrim($upload_directory, '/') . '/' . $fix_parent . $i))
							{
								@rename($path . '/' . $parent, rtrim($upload_directory, '/') . '/' . $fix_parent . $i);
								break;
							}
						}
					}

					$data = gameCacheInsertPendingGames(getAvailablePendingGames($fix_parent, 'unpack'), $saveType, true);
				}
				$check_parents = arcadeReturnPaths($path);
				if (empty($check_parents))
					arcadeRmdir($path);
			}
			else {
				$data = gameCacheInsertPendingGames(getAvailablePendingGames($target, 'unpack'), $saveType, true);
			}

			if (file_exists($path . '/' . rtrim($row['game_file'], '.swf') . '.game.php'))
				@unlink($path . '/' . rtrim($row['game_file'], '.swf') . '.game.php');

		}

		if(mb_substr(mb_strtolower($row['game_file']) , -4) == '.tar' || mb_substr(mb_strtolower($row['game_file']) , -7) == '.tar.gz')
		{
			$path = rtrim($upload_directory, '/') . '/';
			$out_file_name = mb_substr($path . $row['game_file'], 0, -3);
			$folder = $path . trim($target);

			if (mb_substr(mb_strtolower($row['game_file']) , -3) == '.gz')
			{
				try {
					$phar = new PharData($path . $row['game_file']);
					$phar->decompress();
				}
				catch (Exception $e) {
					$error = sprintf($txt['arcade_game_install_error'], $row['game_file']);
					$err = arcade_html_entity_decode($error, 2, 2);
					if (!empty($arcadeModSettings['arcade_log_install_game'])) {
						log_error($err . ' [4]', 'debug');
					}
					continue;
				}

				if (file_exists($out_file_name))
				{
					if (file_exists($path . $row['game_file']))
						@unlink($path . $row['game_file']);
					$row['game_file'] = mb_substr($row['game_file'], 0, -3);
				}
				elseif (file_exists($out_file_name))
					$row['game_file'] = mb_substr($row['game_file'], 0, -3);
				else {
					arcadeClearSession();
					fatal_lang_error('arcade_file_non_read', false);
				}
			}
			try {
				if (!empty($arcadeModSettings['arcadeArchiveTar']) && class_exists('PEAR', false) && class_exists('Archive_Tar', false) && defined('ARCHIVE_TAR_ATT_SEPARATOR') && defined('ARCHIVE_TAR_END_BLOCK')) {
					$tar = new Archive_Tar($path . $row['game_file']);
					$tar->extract($folder);
					unset($tar);
				}
				else {
					$phar = new PharData($path . $row['game_file']);
					$phar->extractTo($folder, null, true);
					unset($phar);
				}
			}
			catch (Exception $e) {
				// $error = sprintf($txt['arcade_game_install_error'], arcadeFilterVar($e->getMessage()));
				$error = sprintf($txt['arcade_game_install_error'], $row['game_file']);
				$err = arcade_html_entity_decode($error, 2, 2);
				if (!empty($arcadeModSettings['arcade_log_install_game'])) {
					log_error($err . ' [5]', 'debug');
				}
				continue;
			}

			if (is_dir($folder) && file_exists($path . $row['game_file']))
			{
				if (@unlink($path . $row['game_file']))
				{
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}arcade_files
						WHERE id_file = {int:file}',
						array(
							'file' => $row['id_file'],
						)
					);
				}
			}
			elseif (is_dir($folder) && file_exists($path . $row['game_file'] . '.gz'))
			{
				if (@unlink($path . $row['game_file'] . '.gz'))
				{
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}arcade_files
						WHERE id_file = {int:file}',
						array(
							'file' => $row['id_file'],
						)
					);
				}
			}
			elseif (!is_dir($folder)) {
				arcadeClearSession();
				fatal_lang_error('arcade_file_non_read', false);
			}

			$check_parents = arcadeReturnPaths($folder);
			if (file_exists($folder . '/master-info.xml'))
			{
				$gameinfo = readGameInfo($folder . '/master-info.xml');
				$saveType = !empty($gameinfo['submit']) && in_array($gameinfo['submit'], $classicGameTypes2) ? $gameinfo['submit'] : '';
			}

			if (!empty($check_parents) && strpos(mb_strtolower($folder), 'gamepack') !== false)
			{
				foreach ($check_parents as $parent)
				{
					$fix_parent = mb_strtolower(str_replace(array(' '), array(''), $parent));
					if (!is_dir($path . $fix_parent))
						@rename($folder . '/' . $parent, $path . $fix_parent);
					else
					{
						for($i=0;$i<7;$i++)
						{
							if ($i == 6)
							{
								deleteArcadeArchives($folder . '/' . $parent);
								break;
							}
							if (!is_dir($path . $fix_parent . $i))
							{
								@rename($path . $parent, $path . $fix_parent . $i);
								break;
							}
						}
					}

					$data = gameCacheInsertPendingGames(getAvailablePendingGames($fix_parent, 'unpack'), $saveType, true);
				}
				$check_parents = arcadeReturnPaths($folder);
				if (empty($check_parents))
					arcadeRmdir($folder);
			}
			else
				$data = gameCacheInsertPendingGames(getAvailablePendingGames($target, 'unpack'), $saveType, true);

			if (file_exists($path . rtrim($row['game_file'], '.swf') . '.game.php'))
				@unlink($path . rtrim($row['game_file'], '.swf') . '.game.php');
		}

		// decompressing RAR archives will have to rely on the PHP PECL RAR package at this time
		if (mb_substr(mb_strtolower($row['game_file']) , -4) == '.rar' && class_exists('RarArchiver'))
		{
			$path = rtrim($upload_directory, '/') . '/';
			$folder = $path . trim($target);
			if(!file_exists($folder))
				@mkdir($folder, 0755);

			$archive = RarArchive::open($path . $row['game_file']);
			$entries = $archive->getEntries();
			foreach ($entries as $entry)
				$entry->extract($folder);

			$archive->close();

			if (file_exists($path . $row['game_file']))
			{
				unset($rar);
				if (@unlink($path . $row['game_file']))
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}arcade_files
						WHERE id_file = {int:file}',
						array(
							'file' => $row['id_file'],
						)
					);

				if (file_exists($path . rtrim($row['game_file'], '.swf') . '.game.php'))
					@unlink($path . rtrim($row['game_file'], '.swf') . '.game.php');
			}

			$check_parents = arcadeReturnPaths($folder);
			if (file_exists($folder . '/master-info.xml'))
			{
				$gameinfo = readGameInfo($path . 'master-info.xml');
				$saveType = !empty($gameinfo['submit']) && in_array($gameinfo['submit'], $classicGameTypes2) ? $gameinfo['submit'] : '';
			}
			if (!empty($check_parents) && strpos(mb_strtolower($folder), 'gamepack') !== false)
			{
				foreach ($check_parents as $parent)
				{
					$fix_parent = mb_strtolower(str_replace(array(' '), array(''), $parent));
					if (!is_dir($path . $fix_parent))
						@rename($folder . '/' . $parent, $path . $fix_parent);
					else
					{
						for($i=0;$i<7;$i++)
						{
							if ($i == 6)
							{
								deleteArcadeArchives($folder . '/' . $parent);
								break;
							}
							if (!is_dir($path . $fix_parent . $i))
							{
								@rename($path . $parent, $path . $fix_parent . $i);
								break;
							}
						}
					}
					$data = gameCacheInsertPendingGames(getAvailablePendingGames($fix_parent, 'unpack'), $saveType, true);
				}
				$check_parents = arcadeReturnPaths($folder);
				if (empty($check_parents))
					arcadeRmdir($folder);
			}
			else
				$data = gameCacheInsertPendingGames(getAvailablePendingGames($target, 'unpack'), $saveType, true);
		}

		$path = rtrim($upload_directory, '/') . '/' . $target;

		// figure out possible internal name
		$internalName = basename($target);
		unset($arcade_internal_flag_data);
		$internalNoALias = mb_strtolower(str_ireplace(array('_origon', '_masodo'), '', basename($target)));
		$searchFiles = arcadeReturnRootGameFiles($upload_directory . '/' . $target);
		$searchFilesInternal = arcadeReturnRootGameFiles($upload_directory . '/' . $target);
		foreach ($searchFilesInternal as $searchX)
		{
			$search = $searchX;
			$internalCheck = basename($search);
			if (mb_strtolower(substr($internalCheck, -4)) == '.htm')
			{
				@rename($search, $searchX . 'l');
				if (file_exists($searchX . 'l')) {
					$search = $search . 'l';
					$internalCheck = basename($search);
				}
			}

			if (mb_strtolower(substr($internalCheck, -4)) == '.swf')
			{
				$recheckInternal = substr($internalCheck, 0, -4);
				$requestInternal = $smcFunc['db_query']('', '
					SELECT id_game, internal_name
					FROM {db_prefix}arcade_games
					WHERE internal_name = {string:iname}
					LIMIT 1',
					array(
						'iname' => $recheckInternal
					)
				);
				while ($rowGame = $smcFunc['db_fetch_assoc']($requestInternal))
				{
					$arcade_internal_flag_data = array(
						'internal_name' => !empty($rowGame['internal_name']) ? $rowGame['internal_name'] : '',
						'id_game' => !empty($rowGame['id_game']) ? $rowGame['id_game'] : 0,
					);
				}
				$smcFunc['db_free_result']($requestInternal);
				break;
			}
			elseif (mb_strtolower(substr($internalCheck, -5)) == '.html')
			{
				$recheckInternal = substr($internalCheck, 0, -5);
				$requestInternal = $smcFunc['db_query']('', '
					SELECT id_game, internal_name
					FROM {db_prefix}arcade_games
					WHERE internal_name = {string:iname}
					LIMIT 1',
					array(
						'iname' => $recheckInternal
					)
				);
				while ($rowGame = $smcFunc['db_fetch_assoc']($requestInternal))
				{
					$arcade_internal_flag_data = array(
						'internal_name' => !empty($rowGame['internal_name']) ? $rowGame['internal_name'] : '',
						'id_game' => !empty($rowGame['id_game']) ? $rowGame['id_game'] : 0,
					);

				}
				$smcFunc['db_free_result']($requestInternal);
				break;
			}
		}
		foreach ($searchFiles as $search)
		{
			if (mb_strtolower(substr($search, -4)) == '.php')
			{
				$searchFile = substr(mb_strtolower(str_ireplace(array('_origon', '_masodo'), '', $search)), 0, -4);
				if (stripos($internalNoALias, $searchFile) !== false)
				{
					$internalName = substr($search, 0, -4);
					if($target !== $internalName && !is_dir($upload_directory . '/' . $internalName) && rename($upload_directory . '/' . $target, $upload_directory . '/' . $internalName))
					{
						$target = $internalName;
					}
					elseif ($target !== $internalName && !empty($arcadeModSettings['arcade_log_install_game']))
						$removePath = arcadeRmdir($upload_directory . '/' . $target) ? log_error($txt['arcade_folder_rename_failed'] . '<br />' . sprintf($txt['arcade_folder_deletion'], $target) . ' [12]', 'debug') : log_error($txt['arcade_folder_rename_failed'] . ' [13]', 'debug');
				}
				break;
			}
		}

		// record the internal name flagged as a possible duplicate
		if (!empty($arcade_internal_flag_data))
		{
			$smcFunc['db_insert']('insert',
				'{db_prefix}arcade_internal_game_conflicts',
				array('id_game' => 'int', 'internal_id_conflict' => 'int', 'internal_name_conflict' => 'string', 'conflict_directory' => 'string', 'submit_system_flag' => 'int'),
				array($arcade_internal_flag_data['id_game'], 0, $arcade_internal_flag_data['internal_name'], $internalName, 0),
				array('id_conflict')
			);
			unset($arcade_internal_flag_data);
		}

		if (file_exists($upload_directory . '/' . $target . '/' . $internalName . '.php'))
			$fileTemp = file_get_contents($upload_directory . '/' . $target . '/' . $internalName . '.php');

		if (!empty($fileTemp) && strpos(str_replace(' ', '', $fileTemp), '$config=array(') !== false)
		{
			list($saveTypeCheck, $na) = checkArcadeHtmlGameFiles($upload_directory . '/' . $target, $internalName);
			$saveType = $saveTypeCheck == 'html5' ? 'html5' : $saveType;
		}
		elseif (!empty($fileTemp) && strpos(str_replace(' ', '', $fileTemp), '$game_data=array(') !== false)
		{
			list($saveTypeCheck, $na) = checkArcadeHtmlGameFiles($upload_directory . '/' . $target, $internalName);
			$saveType = $saveTypeCheck == 'html5' ? 'html5' : $saveType;
		}

		if (file_exists($path . '/master-info.xml'))
			@unlink($path . '/master-info.xml');

		if (file_exists(dirname($path) . '/master-info.xml'))
			@unlink(dirname($path) . '/master-info.xml');

		//if (file_exists($upload_directory . '/' . $target . '/' . $internalName . '.php'))
			//@unlink($upload_directory . '/' . $target . '/' . $internalName . '.php');

		if (@unlink($from))
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_files
				WHERE id_file = {int:file}',
				array(
					'file' => $row['id_file'],
				)
			);
	}
	$smcFunc['db_free_result']($request);

	$_SESSION['qaction_data'] = !empty($_SESSION['qaction_data']) && is_array($_SESSION['qaction_data']) ? array_combine($_SESSION['qaction_data'], $status) : $status;
	$_SESSION['qaction_data'] = array_filter($_SESSION['qaction_data']);
	return !empty($saveType) ? $saveType : true;

	if ($countz == count($_SESSION['arcade_exists']) && count($_SESSION['arcade_exists']) > 0)
	{
		$targets = '';
		foreach ($_SESSION['arcade_exists'] as $gamex)
			$targets .= ' ' . $gamex . ',';

		$targets = rtrim($targets, ',');
		arcadeClearSession();
		fatal_lang_error('arcade_directory_make_exists', false, $targets);
	}
}

function uninstallGames($games, $delete_files = false)
{
	global $smcFunc, $arcadeModSettings, $boarddir, $sourcedir;
	isAllowedTo('arcade_admin');
	require_once($sourcedir . '/Subs-Package.php');
	require_once($sourcedir . '/RemoveTopic.php');
	require_once($boarddir . '/ArcadeSources/ArcadeDownload.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	@ini_set('memory_limit', '256M');
	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_name, game_directory, game_file, id_cat, game_rating, description, internal_name, id_topic,
			thumbnail, thumbnail_small, cover_icon, extra_data, submit_system, enabled, score_type, help, js_insertion, download
		FROM {db_prefix}arcade_games
		WHERE id_game IN({array_int:games})',
		array(
			'games' => $games
		)
	);

	list($status, $topics) = array(array(), array());

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$idGame = !empty($row['id_game']) ? $row['id_game'] : 0;
		$maindir = rtrim(str_replace('\\', '/', $arcadeModSettings['gamesDirectory']), '/');
		$directory = $arcadeModSettings['gamesDirectory'] . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
		$directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return rtrim($value, '.');}, explode('/', str_replace('\\', '/', $directory)))));
		$gamedir = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $row['game_directory'])))));
		$internal_name = str_replace(array('/', '\\'), array('', ''), trim($row['internal_name'], '.'));
		$directory = rtrim($directory, '/');
		$directory = str_replace('\\', '/', $directory);
		$gameNameDel = !empty($row['game_name']) ? $row['game_name'] : '';

		if (!empty($row['id_topic']))
			$topics[] = $row['id_topic'];

		$files = array_unique(
			array(
				$row['game_file'],
				$row['thumbnail'],
				$row['thumbnail_small'],
				$row['cover_icon'],
				'master-info.xml',
				mb_substr($row['game_file'], 0, -4) . '.php',
				mb_substr($row['game_file'], 0, -4) . '-game-info.xml',
				mb_substr($row['game_file'], 0, -5) . '.html',
				mb_substr($row['game_file'], 0, -4) . '.xap',
				mb_substr($row['game_file'], 0, -4) . '.ini',
				mb_substr($row['game_file'], 0, -4) . '.game.php',
			)
		);

		if (empty($delete_files) && !empty($row['game_directory']))
		{
			$check = !empty($row['game_file']) && strlen($row['game_file']) > 9 && substr($row['game_file'], -10) == 'index.html' ? true : (!empty($row['game_file']) && strlen($row['game_file']) > 8 && substr($row['game_file'], -9) == 'index.php' ? true : false);
			$subSystem = !empty($row['submit_system']) ? $row['submit_system'] : '';
			$internal = !empty($row['internal_name']) ? $row['internal_name'] : '';
			$phpFilex = $check && $subSystem == 'html52' ? $internal . '.php' : (!empty($row['game_file']) ? str_replace('.swf', '.php', $row['game_file']) : 'generated_file.php');
			$phpFilex = preg_replace('"\.(htm|html)$"', '.php', $phpFilex);
			$extra = !empty($row['extra_data']) && arcadeIsSerialized($row['extra_data']) ? arcade_safe_unserialize($row['extra_data']) : array();
			$dataArray = array(
				'id_game' => !empty($idGame) ? $idGame : 0,
				'enabled' => !empty($row['enabled']) ? $row['enabled'] : 0,
				'download' => !empty($row['download']) ? 1 : 0,
				'score_type' => !empty($row['score_type']) ? $row['score_type'] : 0,
				'js_insertion' => !empty($row['js_insertion']) ? $row['js_insertion'] : 0,
				'gamename' => !empty($row['game_name']) ? $row['game_name'] : '',
				'internal_name' => !empty($row['internal_name']) ? $row['internal_name'] : '',
				'php_file' => !empty($row['internal_name']) ? $row['internal_name'] . '.php' : '',
				'help' => !empty($row['help']) ? $row['help'] : '',
				'description' => !empty($row['description']) ? $row['description'] : '',
				'game_directory' => !empty($row['game_directory']) ? $row['game_directory'] : '',
				'game_file' => !empty($row['game_file']) ? $row['game_file'] : 'generated_file.swf',
				'gamephp' => $phpFilex,
				'thumbnail' => !empty($row['thumbnail']) ? $row['thumbnail'] : '',
				'thumbnail_small' => !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : '',
				'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
				'extra_data' => $extra,
				'id_cat' => !empty($row['id_cat']) ? $row['id_cat'] : 0,
				'submit_system' => !empty($row['submit_system']) ? $row['submit_system'] : '',
				'gamefile_name' => !empty($internal) ? ArcadeSpecialChars(trim($internal), 'name') : '',
				'gamesave' => 'games_download',
			);
			$tempFiles = arcade_game_down($dataArray, $directory);
			$ext =  pathinfo($tempFiles[0], PATHINFO_EXTENSION);
			if (strtolower($ext) == 'php')
				@rename($tempFiles[0], $directory . '/' . $dataArray['php_file']);
			else
				@rename($tempFiles[0], $directory . '/game-info.xml');

			list($gameidconf, $id_conflict) = array(0, 0);
			$request_conf = $smcFunc['db_query']('', '
				SELECT id_conflict, id_game, internal_id_conflict, internal_name_conflict, conflict_directory
				FROM {db_prefix}arcade_internal_game_conflicts
				WHERE internal_name_conflict = {string:internal} || conflict_directory = {string:gamedir}
				LIMIT 1',
				array('internal' => $dataArray['internal_name'], 'gamedir' => $dataArray['game_directory'])
			);

			while ($row_conf = $smcFunc['db_fetch_assoc']($request_conf)) {
				$id_conflict = $row_conf['id_conflict'];
				$gameidconf = $row_conf['id_game'];
			}
			$smcFunc['db_free_result']($request_conf);
			if (empty($id_conflict) && !empty($dataArray['id_game'])) {
				$conf_request = $smcFunc['db_query']('', '
					SELECT id_conflict, id_game, internal_id_conflict, internal_name_conflict, conflict_directory
					FROM {db_prefix}arcade_internal_game_conflicts
					WHERE internal_id_conflict = {int:idgame}
					LIMIT 1',
					array('idgame' => $dataArray['id_game'])
				);

				while ($conf_row = $smcFunc['db_fetch_assoc']($conf_request)) {
					$id_conflict = $conf_row['id_conflict'];
					$gameidconf = $conf_row['id_game'];
				}
				$smcFunc['db_free_result']($conf_request);
			}

			if (!empty($id_conflict))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_internal_game_conflicts
					SET internal_id_conflict = {int:idgame}
					WHERE id_conflict = {int:conflict}',
					array(
						'conflict' => $id_conflict,
						'idgame' => 0
					)
				);
			elseif (!empty($dataArray['id_game']))
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_internal_game_conflicts
					WHERE id_game = {int:idgame}',
					array(
						'idgame' => $dataArray['id_game'],
					)
				);

			$upload_directory = str_replace('\\', '/', $boarddir) . '/games_upload';
			copyArcadeDirectory($directory, $upload_directory . '/' . $row['game_directory']);
			if (is_dir($upload_directory. '/' . $row['game_directory']) && $directory !== $boarddir && $directory != $maindir) {
				arcadeRmdir($directory);
			}
			else {
				$error = sprintf($txt['file_move_failed'], $directory, $upload_directory . '/' . $row['game_directory']);
				if (!empty($arcadeModSettings['arcade_log_install_game'])) {
					log_error($error . ' [6]', 'debug');
				}
			}
		}

		// edit file deletion routines below at your own risk
		if ($delete_files)
		{
			$pathInUse = false;
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_internal_game_conflicts
				WHERE id_game = {int:idgame}',
				array(
					'idgame' => $idGame,
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_internal_game_conflicts
				WHERE internal_id_conflict = {int:idgame}',
				array(
					'idgame' => $idGame,
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_internal_game_conflicts
				WHERE internal_name_conflict = {string:internal}',
				array(
					'internal' => $row['internal_name'],
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_internal_game_conflicts
				WHERE conflict_directory = {string:gamedir}',
				array(
					'gamedir' => $row['game_directory'],
				)
			);

			$request2 = $smcFunc['db_query']('', '
				SELECT id_game, game_directory, internal_name
				FROM {db_prefix}arcade_games
				WHERE game_directory = {string:gdir} AND id_game != {int:gameid}
				LIMIT 1',
				array(
					'gdir' => $row['game_directory'],
					'gameid' => $idGame,
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request2)) {
				$pathInUse = true;
				$delete_files = false;
			}

			$smcFunc['db_free_result']($request2);

			if (!empty($row['game_directory']) && empty($pathInUse))
				arcadeRmdir($directory);
			if (empty($pathInUse)) {
				if (basename(dirname($directory)) !== basename($arcadeModSettings['gamesDirectory']) && basename($directory) !== basename($arcadeModSettings['gamesDirectory']))
				{
					//if (is_dir($directory))
						//deleteArcadeArchives($directory);
					$dir = dirname($directory);
					$base = $directory;
					$gd = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
					$bd = str_replace('\\', '/', $boarddir);
					$base = rtrim($base, '/');
					$dir = rtrim($dir, '/');
					$gd = rtrim($gd, '/');
					$bd = rtrim($bd, '/');

					if (is_dir($base) && $base !== $gd && $base !== $bd)
					{
						// additional sub-directories?
						if ($base !== $dir)
						{
							$files = ArcadeAdminScanDir($base, '');
							foreach ($files as $file)
								if (!is_dir($file))
									@unlink($file);

							if (count(scandir($base)) == 2)
								arcadeRmdir($base);

							$paths = arcadeReturnPaths($base);
							if (!empty($paths))
							{
								foreach($paths as $path)
									arcadeRmdir($base . '/' . $path);
							}

							if (empty(arcadeReturnPaths($base)))
							{
								if (file_exists($dir . '/master-info.xml'))
									@unlink($dir . '/master-info.xml');

								arcadeRmdir($base);
							}

							if (empty(arcadeReturnPaths($dir)))
								arcadeRmdir($dir);
						}
						else
						{
							$files = ArcadeAdminScanDir($dir, '');
							foreach ($files as $file)
								if (!is_dir($file))
									@unlink($file);

							if (count(scandir($dir)) == 2)
								arcadeRmdir($dir);

							if (empty(arcadeReturnPaths($dir)))
								arcadeRmdir($dir);
						}
					}


				}
				elseif (basename($gamedir) == $internal_name && $internal_name !== basename($arcadeModSettings['gamesDirectory']))
				{
					if (is_dir($directory) && basename($directory) !== $arcadeModSettings['gamesDirectory'])
					{
						$files = ArcadeAdminScanDir($directory, '');
						$dirs = array($directory);
						foreach ($files as $file)
						{
							@unlink($file);
							if(!in_array(dirname($file), $dirs))
								$dirs[] = dirname($file);
						}
						foreach ($dirs as $dir)
						{
							$dir = rtrim($dir, '/');
							if (is_dir($dir) && $dir !== $directory)
							{
								if ($arcadeModSettings['gamesDirectory'] . '/' !== $arcadeModSettings['gamesDirectory'] . '/' . $dir && $dir !== $arcadeModSettings['gamesDirectory'])
									arcadeRmdir($dir);
							}
						}

						if (is_dir($directory) && $arcadeModSettings['gamesDirectory'] . '/' !== $arcadeModSettings['gamesDirectory'] . '/' . $directory && $directory !== $arcadeModSettings['gamesDirectory'])
							arcadeRmdir($directory);

						if (empty(arcadeReturnPaths($gamedir)))
							arcadeRmdir($gamedir);

						if (empty(arcadeReturnPaths($directory)))
							arcadeRmdir($directory);
					}
				}
				elseif (is_dir($directory . '/gamedata/' . $internal_name) && $directory != $boarddir && in_array($row['submit_system'], array('html5', 'html52', 'html53')) && $directory != $arcadeModSettings['gamesDirectory'])
					arcadeRmdir($directory);
				else
				{
					foreach ($files as $f)
					{
						if ((!empty($f)) && file_exists($directory . '/' . $f))
							@unlink($directory . '/' . $f);
					}

					if (basename($directory) !== basename($arcadeModSettings['gamesDirectory']))
					{
						$check = ArcadeAdminScanDir($directory, '');
						if (empty($check) && $arcadeModSettings['gamesDirectory'] . '/' !== $arcadeModSettings['gamesDirectory'] . '/' . $directory && $directory !== $arcadeModSettings['gamesDirectory'] && is_dir($directory))
							arcadeRmdir($directory);
						elseif (count($check) == 1 && $check[0] == $directory . '/master-info.xml')
						{
							@unlink($directory . '/master-info.xml');

							if ($arcadeModSettings['gamesDirectory'] . '/' !== $arcadeModSettings['gamesDirectory'] . '/' . $directory && $directory !== $arcadeModSettings['gamesDirectory'])
								arcadeRmdir($directory);
						}
					}
				}

				if (is_dir($boarddir . '/arcade/gamedata/' . $internal_name) || file_exists($boarddir . '/arcade/gamedata/' . $internal_name))
					arcadeRmdir($boarddir . '/arcade/gamedata/' . $internal_name);
			}
		}

		$noUnderscore = str_replace('_', '', mb_strtolower(basename($directory)));
		if (mb_strtolower(basename($directory)) == mb_strtolower($internal_name) ||  $noUnderscore == mb_strtolower($internal_name) && !empty(basename($directory)) && $directory != $boarddir && $directory != $maindir)
		{
			if ($delete_files)
			{
				arcadeRmdir($directory);
				arcadeRmdir($directory);
			}
		}

		deleteGame($idGame, $delete_files, $gameNameDel);

		$status[] = array(
			'id' => (!empty($idGame) ? $idGame : 0),
			'name' => (!empty($gameNameDel) ? $gameNameDel : ''),
		);
	}

	$smcFunc['db_free_result']($request);

	// remove related topics if they exist
	if (!empty($topics))
		removeTopics($topics, false, false);

	if (!empty($status)) {
		$status = array_map('array_filter', $status);
		$status = array_filter($status);
	}

	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		$gameRelatedCache = array(
			'arcade_games_suggest',
			'arcade_games_recommended',
			'arcade_games_query_small',
			'arcade_thumbsA0',
			'arcade_thumbsA1',
			'arcade_thumbsA2',
			'arcade_thumbsA3',
			'arcade_cats',
			'arcade_catsB',
			'arcade_games_nocat',
			'arcade_most_played',
			'arcade_games_rating',
			'arcade_games_best',
			'arcade_games_mostactive',
			'arcade_games_longchamps',
			'arcade_games_players',
			'arcade_games_latestScores',
			'arcade_user_counts',
			'arcade_champsA_wins',
			'arcade_champsA_gen',
			'arcade_games_latestA',
			'arcade_new_champsA',
			'arcade_new_gamesA',
			'arcade_popularA',
			'arcade_newestB',
			'arcade_champsC_wins',
			'arcade_champsC_gen',
			'arcade_games_latestC',
			'arcade_newChampsC',
			'arcade_newestC',
			'arcade_popularC',
			'arcade_champsB'
		);
		foreach ($gameRelatedCache as $cache) {
			cache_put_data($cache, null, 0);
		}
	}

	return $status;
}

function moveGames()
{
	global $db_prefix, $arcadeModSettings, $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, game_directory, game_file, thumbnail, thumbnail_small, cover_icon
		FROM {db_prefix}arcade_games');

	if (!is_writable($arcadeModSettings['gamesDirectory'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($arcadeModSettings['gamesDirectory']));
	}

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$from = $arcadeModSettings['gamesDirectory']  . '/' . (!empty($row['game_directory']) ? $row['game_directory'] . '/' : '');
		$to = $arcadeModSettings['gamesDirectory'] . '/' . str_replace(array('/', '\\'), array('', ''), trim($row['internal_name'], '.')) . '/';
		$to = preg_replace('#/+#','/',implode('/', array_map(function($value) {return rtrim($value, '.');}, explode('/', str_replace('\\', '/', $to)))));

		if ($from == $to)
			continue;

		if ((file_exists($to) && !is_dir($to)) || !mkdir($to)) {
			arcadeClearSession();
			fatal_lang_error('arcade_not_writable', false, array($to));
		}

		chdir($from);

		// These should be at least there
		$files = array();
		$files[] = $row['game_file'];
		$files[] = $row['thumbnail'];
		$files[] = $row['thumbnail_small'];
		$files[] = $row['cover_icon'];

		$files = array_filter($files);

		foreach ($files as $file)
		{
			if (file_exists($from . $file)) {
				if (substr($file, -9) == 'index.htm')
					$rename = rename($from . $row['gameFile'], $to . $file . 'l');
				else
					$rename = rename($from . $row['gameFile'], $to . $file);

				if (!$rename) {
					arcadeClearSession();
					fatal_lang_error('arcade_unable_to_move', false, array($file, $from, $to));
				}
			}
		}

		updateGame($row['id_game'], array('game_directory' => $row['internal_name']), false);
	}
	$smcFunc['db_free_result']($request);
}

function updateCategoryStats($rom = 0)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_categories
		SET num_games = {int:num_games}',
		array(
			'num_games' => 0,
		)
	);

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, COUNT(*) as games
		FROM {db_prefix}arcade_games
		GROUP BY id_cat');

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET num_games = {int:num_games}
			WHERE id_cat = {int:category}',
			array(
				'category' => $row['id_cat'],
				'num_games' => $row['games'],
			)
		);
	$smcFunc['db_free_result']($request);

	return true;
}

function readGameInfo($file)
{
	if (!file_exists($file))
		return false;

	$gameinfo = new xmlArray(file_get_contents($file));
	$gameinfo = $gameinfo->path('game-info[0]');
	return $gameinfo->to_array();
}

function readPhpGameInfo($directory, $name='', $upload = true, $rom = 0)
{
	global $arcadeModSettings, $boarddir, $txt;

	@ini_set("gd.jpeg_ignore_warning", 1);
	list($mainPhpFileFind, $game) = array('', array());
	$games_upload = isset($_REQUEST['area']) && $_REQUEST['area'] == 'manageromgames' ? 'games_rom_upload' : 'games_upload';
	$gamesDir = empty($rom) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$classicGameTypes2 = $classicGameTypes;
	if (($key = array_search('ibp32', $classicGameTypes2)) !== FALSE) {
		unset($classicGameTypes2['ibp32']);
	}
	if (empty($name))
	{
		$files = empty($upload) ? array_diff(scandir($gamesDir . '/' . $directory), array('..', '.')) : array_diff(scandir($boarddir . '/' . $games_upload . '/' . $directory), array('..', '.'));
		foreach ($files as $file)
		{
			$info = new SplFileInfo($file);
			$ext = ($info->getExtension());
			if ($ext == 'php')
			{
				$mainPhpFileFind = empty($upload) ? $gamesDir . '/' . $directory . '/' . $file : $boarddir . '/' . $games_upload . '/' . $directory . '/' . $file;
				$name = rtrim($file, '.php');
				break;
			}
		}
	}
	elseif (!empty($upload) && file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.php')) {
		$mainPhpFileFind = $boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.php';
	}
	elseif (file_exists($gamesDir . '/' . $directory . '/' . $name . '.php')) {
		$mainPhpFileFind = $gamesDir . '/' . $directory . '/' . $name . '.php';
	}
	else {
		list($mainPhpFileFind, $xfiles, $xpath) = array('', array(), basename($gamesDir . '/' . $directory));
		$xfiles = glob($gamesDir . '/' . $directory . '/*.{php}', GLOB_BRACE);
		foreach ($xfiles as $xfile) {
			$fileTemp = file_get_contents($xfile);
			$yfile = pathinfo(basename($xfile), PATHINFO_FILENAME);
			$xfile = str_replace('\\', '/', $xfile);
			if (!empty($fileTemp) && stripos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
				if (stripos($gamesDir . '/' . $directory, $yfile) !== FALSE) {
					$mainPhpFileFind = $xfile;
					break;
				}
			}
		}
	}

	if(!empty($mainPhpFileFind) && file_exists($mainPhpFileFind))
	{
		$imageArray = array('gif', 'png', 'jpg', 'jpeg');
		$game_info = array('gname', 'gtitle', 'gwords', 'gkeys', 'type', 'savetype', 'js_insertion', 'scoring', 'romflag', 'romtype');
		$arcade_info = array('file', 'name', 'description', 'help', 'type', 'savetype', 'js_insertion', 'score_type', 'rom_flag', 'rom_system');
		$romFileTypes = explode('|', $arcadeModSettings['arcadeRomGameTypes']);
		$x = 0;
		$phpbb = false;
		$file = file_get_contents($mainPhpFileFind);

		// phpbb game types
		if (strpos(str_replace(' ', '', $file), '$game_data=array(') !== false && strpos(str_replace(' ', '', $file), 'IN_PHPBB_ARCADE') !== false)
		{
			$phpbb = true;
			DefinePhpBB_Constants();

			@require($mainPhpFileFind);
			foreach ($game_data as $key => $info)
			{
				if (!empty($info))
					$config[$key] = un_htmlspecialchars($info);

				$x++;
			}

			$htmlType = isset($config['game_type']) ? $config['game_type'] : '';
			if (!empty($config['romtype']) && $config['romtype'] == 'atari7200')
				$config['romtype'] = 'atari7800';
			if (!empty($config['game_control']))
				$game['help'] = $config['game_control'];
			if (!empty($config['game_control_desc']))
				$game['help'] = !empty($game['help']) ? arcadeFilterDbInstallText($game['help']) . ' | ' . arcadeFilterDbInstallText($config['game_control_desc']) : arcadeFilterDbInstallText($config['game_control_desc']);
			if (!empty($config['game_desc']))
				$game['description'] = arcadeFilterDbInstallText($config['game_desc']);
			if (isset($config['js_insertion']))
				$game['js_insertion'] = $config['js_insertion'];
			if (!empty($config['game_name']))
				$game['name'] = ArcadeSpecialChars($config['game_name'], 'name');
			if (!empty($config['gtitle']))
				$game['name'] = ArcadeSpecialChars($config['gtitle'], 'name');
			if (!empty($config['gname']))
				$game['file'] = ArcadeSpecialChars($config['gname'], 'file');
			if (!empty($config['game_scorevar']))
				$game['id'] = ArcadeSpecialChars($config['game_scorevar'], 'name');
			if (!empty($config['game_width']) && is_numeric($config['game_width']))
				$game['flash']['width'] = (int)$config['game_width'];
			if (!empty($config['game_height']) && is_numeric($config['game_height']))
				$game['flash']['height'] = (int)$config['game_height'];
			if (!empty($config['covericon']) && !empty($upload) && file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $config['covericon']))
				$game['cover_icon'] = $config['covericon'];
			if (!empty($config['game_image']) && !empty($upload) && file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $config['game_image']))
				$game['thumbnail'] = $config['game_image'];
			elseif (!empty($config['game_image']) && file_exists($gamesDir . '/' . $directory . '/' . $config['game_image']))
				$game['thumbnail'] = $config['game_image'];
			$game['flash']['type'] = '';
			if (!empty($config['game_save_type']) && in_array($config['game_save_type'], array_merge(array('auto', 'rom'), $classicGameTypes)))
				$game['submit'] = $config['game_save_type'];
			if (!empty($config['romtype']) && in_array($config['romtype'], array_keys($txt['arcade_select_gametype_rom'])) && $config['romtype'] != 'all')
				$game['rom_system'] = $config['romtype'];
			$game['rom_flag'] = !empty($config['romflag']) ? 1 : 0;
			if ($htmlType == 'html52')
				$game['submit'] = 'html52';
			if ($htmlType == 'html53')
				$game['submit'] = 'html53';
			if (!empty($config['game_save_type']) && $config['game_save_type'] == 'phpbb' && stripos($htmlType, 'html5') !== false)
			{
				$game['submit'] = 'html53';
				$game['submit_phpbb'] = 'phpbb';
			}
			elseif (!empty($config['game_save_type']) && !empty($config['game_type']) && $config['game_save_type'] == 'phpbb' && stripos($config['game_type'], 'html5') !== false)
			{
				$game['submit'] = 'html53';
				$game['submit_phpbb'] = 'phpbb';
			}
			if (!empty($config['game_bgcolor']) && strlen($config['game_bgcolor']) == 6)
			{
				$game['extra_data']['background_color'] = array(
					hexdec(mb_substr($config['game_bgcolor'], 0, 2)),
					hexdec(mb_substr($config['game_bgcolor'], 2, 2)),
					hexdec(mb_substr($config['game_bgcolor'], 4, 2))
				);
				$game['flash']['bgcolor'] = $config['game_bgcolor'];
			}
			elseif (!empty($config['bgcolor']) && strlen($config['bgcolor']) == 6)
			{
				$game['extra_data']['background_color'] = array(
					hexdec(mb_substr($config['bgcolor'], 0, 2)),
					hexdec(mb_substr($config['bgcolor'], 2, 2)),
					hexdec(mb_substr($config['bgcolor'], 4, 2))
				);
				$game['flash']['bgcolor'] = $config['bgcolor'];
			}
			else
			{
				$game['extra_data']['background_color'] = array(
					hexdec('00'),
					hexdec('00'),
					hexdec('00')
				);
				$game['flash']['bgcolor'] = '000000';
			}

			if (!empty($config['game_image']))
				$game['thumbnail'] = $config['game_image'];
			if (!empty($config['game_image']))
				$game['thumbnail_small'] = $config['game_image'];
			if (!empty($config['covericon']))
				$game['cover_icon'] = $config['covericon'];
			$game['id'] = str_replace(array(' '), array('_'), ArcadeSpecialChars($name, 'name'));
			//$game['name'] = !empty($config['name']) ? $config['name'] : ArcadeSpecialChars($name, 'name');
			$scoring = !empty($config['score_type']) ? intval($config['score_type']) : 0;
			$scoring = !empty($config['scoring']) ? intval($config['scoring']) : $scoring;
			$game['score_type'] = $scoring > 0 && $scoring < 3 ? $scoring : 0;
			$game['scoring'] = $game['score_type'];
			$game['js_insertion'] = !empty($game['js_insertion']) ? (int)$game['js_insertion'] : 0;
			if (!empty($config['game_scoretype']))
			{
				$tempType = floatval($config['game_scoretype']);
				if ($tempType == 2)
					$game['scoring'] = 1;
			}
			if (!empty($config['game_save_type']) && $config['game_save_type'] == 'no_scoring')
				$game['scoring'] = 2;

			if (empty($upload)) {
				if (file_exists($gamesDir . '/' . $directory . '/index.html'))
				{
					$game['file'] = 'index.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($gamesDir . '/' . $directory . '/' . $name . '.html'))
				{
					$game['file'] = $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($gamesDir . '/' . $directory . '/gamedata/' . $name . '/index.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/index.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($gamesDir . '/' . $directory . '/gamedata/' . $name . '/' . $name . '.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/' . $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (!empty($config['game_type']) && $config['game_type'] == 'html52')
				{
					$game['file'] = $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (!empty($config['game_type']) && $config['game_type'] == 'rom')
				{
					$romFile = '';
					$game['submit'] = 'rom';
					$game['file'] = $name . '.zip';
					foreach ($romFileTypes as $fileType) {
						if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType)) {
							$romFile = $boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType;
							$game['file'] = $name . '.' . $fileType;
							break;
						}
					}
				}
				else
				{
					$game['file'] = $name . '.swf';
					if (!empty($config['game_swf']))
					{
						$file = ArcadeSpecialChars($config['game_swf'], 'file');
						if (strlen($file) > 4 && substr($file, -4) == '.swf' && file_exists($gamesDir . '/' . $directory . '/' . $file))
							$game['file'] = $file;
					}
				}
			}
			else {
				if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/index.html'))
				{
					$game['file'] = 'index.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.html'))
				{
					$game['file'] = $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/gamedata/' . $name . '/index.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/index.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/gamedata/' . $name . '/' . $name . '.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/' . $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (!empty($config['game_type']) && $config['game_type'] == 'html52')
				{
					$game['file'] = $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (!empty($config['game_type']) && $config['game_type'] == 'rom')
				{
					$romFile = '';
					$game['submit'] = 'rom';
					$game['file'] = $name . '.zip';
					foreach ($romFileTypes as $fileType) {
						if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType)) {
							$romFile = $boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType;
							$game['file'] = $name . '.' . $fileType;
							break;
						}
					}
				}
				elseif (!empty($config['savetype']) && $config['savetype'] == 'rom')
				{
					$romFile = '';
					$game['submit'] = 'rom';
					$game['file'] = $name . '.zip';
					foreach ($romFileTypes as $fileType) {
						if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType)) {
							$romFile = $boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType;
							$game['file'] = $name . '.' . $fileType;
							break;
						}
					}
				}
				else
				{
					$game['file'] = $name . '.swf';
					if (!empty($config['game_swf']))
					{
						$file = ArcadeSpecialChars($config['game_swf'], 'file');
						if (strlen($file) > 4 && substr($file, -4) == '.swf' && file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $file))
							$game['file'] = $file;
					}
				}
			}

			$game['flash']['version'] = 6;
		}

		// ibp game types
		if (!$phpbb && strpos(str_replace(' ', '', $file), '$config=array(') !== false)
		{
			@require($mainPhpFileFind);
			foreach ($game_info as $info)
			{
				if (!empty($config[$info]))
				{
					$config[$info] = un_htmlspecialchars($config[$info]);
				}
				$x++;
			}
			$scoring = !empty($config['score_type']) ? intval($config['score_type']) : 0;
			$scoring = !empty($config['scoring']) ? intval($config['scoring']) : $scoring;
			$scoring = isset($config['scoring']) ? (int)$config['scoring'] : 0;
			$scoring = abs(intval($scoring));
			$game['score_type'] = $scoring > 0 && $scoring < 3 ? $scoring : 0;
			$game['scoring'] = $game['score_type'];
			if (!empty($config['romtype']) && $config['romtype'] == 'atari7200')
				$config['romtype'] = 'atari7800';
			if (!empty($config['gkeys']))
				$game['help'] = arcadeFilterDbInstallText($config['gkeys']);
			if (!empty($config['help']) && empty($game['help']))
				$game['help'] = arcadeFilterDbInstallText($config['help']);
			if (isset($config['js_insertion']))
				$game['js_insertion'] = $config['js_insertion'];
			if (!empty($config['gwords']))
				$game['description'] = arcadeFilterDbInstallText($config['gwords']);
			if (!empty($config['gname']))
				$game['file'] = $config['gname'];
			if (!empty($config['gtitle']))
				$game['name'] = ArcadeSpecialChars($config['gtitle'], 'name');
			if (!empty($config['gwidth']) && is_numeric($config['gwidth']))
				$game['flash']['width'] = (int)$config['gwidth'];
			if (!empty($config['gheight']) && is_numeric($config['gheight']))
				$game['flash']['height'] = (int)$config['gheight'];
			if (!empty($config['gtype']))
				$game['flash']['type'] = ArcadeSpecialChars($config['gtype'], 'name');
			if (!empty($config['savetype']) && in_array($config['savetype'], array_merge(array('auto', 'rom'), $classicGameTypes)))
				$game['submit'] = ArcadeSpecialChars($config['savetype'], 'name');
			if (!empty($config['romtype']) && in_array($config['romtype'], array_keys($txt['arcade_select_gametype_rom'])) && $config['romtype'] != 'all')
				$game['rom_system'] = $config['romtype'];
			$game['rom_flag'] = !empty($config['romflag']) ? 1 : (!empty($config['rom_flag']) ? 1 : 0);

			if (!empty($config['bgcolor']) && strlen($config['bgcolor']) == 6)
			{
				$game['extra_data']['background_color'] = array(
					hexdec(mb_substr($config['bgcolor'], 0, 2)),
					hexdec(mb_substr($config['bgcolor'], 2, 2)),
					hexdec(mb_substr($config['bgcolor'], 4, 2))
				);
				$game['flash']['bgcolor'] = $config['bgcolor'];
			}
			elseif (!empty($config['game_bgcolor']) && strlen($config['game_bgcolor']) == 6)
			{
				$game['extra_data']['background_color'] = array(
					hexdec(mb_substr($config['game_bgcolor'], 0, 2)),
					hexdec(mb_substr($config['game_bgcolor'], 2, 2)),
					hexdec(mb_substr($config['game_bgcolor'], 4, 2))
				);
				$game['flash']['bgcolor'] = $config['game_bgcolor'];
			}
			else
			{
				$game['extra_data']['background_color'] = array(
					hexdec('00'),
					hexdec('00'),
					hexdec('00')
				);
				$game['flash']['bgcolor'] = '000000';
			}
			if (!empty($config['exlink'])) {
				$value = $config['exlink'];
				if (substr($value, 6) == 'ftp://' || substr($value, 6) == 'ssh://')
					$value = substr($value, 6);

				if (substr($value, 0, 7) == 'http://')
					$value = 'https://' . substr($value, 7);

				if (substr($value, 0, 8) != 'https://')
					$value = 'https://' . $value;

				$value = filter_var($value, FILTER_SANITIZE_URL);

				if (filter_var($value, FILTER_VALIDATE_URL) === false) {
					$value = '';
				}
				$game['extra_data']['external_link'] = $value;
				$game['flash']['external_link'] = $value;
			}
			if (!empty($config['thumbnail']))
				$game['thumbnail'] = $config['thumbnail'];
			if (!empty($config['thumbnail_small']))
				$game['thumbnail_small'] = $config['thumbnail_small'];
			if (!empty($config['covericon']))
				$game['cover_icon'] = $config['covericon'];
			$game['id'] = !empty($config['id']) ? $config['id'] : str_replace(array(' '), array('_'), ArcadeSpecialChars($name, 'name'));
			// ?
			$game['name'] = !empty($game['name']) ? $game['name'] : (!empty($config['name']) ? $config['name'] : ArcadeSpecialChars($name, 'name'));
			//$game['scoring'] = !empty($config['scoring']) ? abs(intval($config['scoring'])) : '0';
			$game['js_insertion'] = !empty($game['js_insertion']) ? (int)$game['js_insertion'] : 0;
			if (empty($upload)) {
				if (file_exists($gamesDir . '/' . $directory . '/index.html'))
				{
					$game['file'] = 'index.html';
					$game['submit'] = 'html5';
				}
				elseif (file_exists($gamesDir . '/' . $directory . '/' . $name . '.html'))
				{
					$game['file'] = $name . '.html';
					$game['submit'] = 'html5';
				}
				elseif (file_exists($gamesDir . '/' . $directory . '/gamedata/' . $name . '/index.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/index.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($gamesDir . '/' . $directory . '/gamedata/' . $name . '/' . $name . '.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/' . $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (!empty($game['rom_flag'])) {
					$romFile = '';
					foreach ($romFileTypes as $fileType) {
						if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType)) {
							$romFile = $boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType;
							$game['submit'] = 'rom';
							$game['file'] = $name . '.' . $fileType;
							break;
						}
					}
				}
				else
					$game['file'] = empty($romFile) ? $name . '.swf' : basename($romFile);
			}
			else {
				if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/index.html'))
				{
					$game['file'] = 'index.html';
					$game['submit'] = 'html5';
				}
				elseif (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.html'))
				{
					$game['file'] = $name . '.html';
					$game['submit'] = 'html5';
				}
				elseif (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/gamedata/' . $name . '/index.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/index.html';
					$game['submit'] = 'html52';
				}
				elseif (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/gamedata/' . $name . '/' . $name . '.html'))
				{
					$game['file'] = 'gamedata/' . $name . '/' . $name . '.html';
					$game['submit'] = 'html52';
				}
				elseif (!empty($game['rom_flag'])) {
					$romFile = '';
					foreach ($romFileTypes as $fileType) {
						if (file_exists($boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType)) {
							$romFile = $boarddir . '/' . $games_upload . '/' . $directory . '/' . $name . '.' . $fileType;
							$game['submit'] = 'rom';
							$game['file'] = $name . '.' . $fileType;
							break;
						}
					}
				}
				else
					$game['file'] = empty($romFile) ? $name . '.swf' : basename($romFile);
			}

			$game['flash']['version'] = 6;
		}
	}

	if (empty($game['submit']))
	{
		if (file_exists($directory . '/gamedata/' . $name . '/index.html') || file_exists($directory . '/gamedata/' . $name . '/' . $name . '.html'))
			$game['submit'] = 'html52';
		elseif (file_exists($directory . '/index.html') || file_exists($directory . '/' . $name . '.html'))
			$game['submit'] = 'html5';
		elseif (file_exists($directory . '/gamedata/' . $name . '/v32game.txt'))
			$game['submit'] = 'ibp32';
		elseif (file_exists($directory . '/gamedata/' . $name . '/v3game.txt'))
			$game['submit'] = 'ibp3';
		elseif (file_exists($directory . '/gamedata/' . $name . '/v2game.txt'))
			$game['submit'] = 'ibp2';
		elseif (is_dir($directory . '/gamedata/' . $name) && file_exists($directory . '/gamedata/' . $name . '/' . $name . '.txt'))
			$game['submit'] = 'ibp2';
		elseif (file_exists($directory . '/' . $name . '.xap'))
			$game['submit'] = 'silver';
		elseif (file_exists($directory . '/' . $name . '.ini'))
			$game['submit'] = 'pnflash';
		elseif (file_exists($directory . '/' . 'size.txt'))
			$row['submit_system'] = 'phpbb';
		elseif (file_exists($directory . '/' . $name . '.php'))
		{
			$game['submit'] = 'ibp';
		}
		else
			$game['submit'] = 'v1game';
	}
	if ($game['submit'] == 'html52')
	{
		$check = checkArcadeHtmlGameFiles($directory, $name);
		if ($check[0] == 'html53')
		{
			$game['submit'] = 'html53';
			$game['submit_phpbb'] = 'phpbb';
		}
	}

	// Ensure game icons are set if they exist
	$fileName = !empty($game['file']) ? array(pathinfo($game['file'], PATHINFO_FILENAME), $game['file']) : array('', '');
	$newName = !empty($fileName[0]) ? arcadeSanitizeImg($fileName[0]) : '';
	$game['thumbnail'] = !empty($game['thumbnail']) ? $game['thumbnail'] : '';
	$game['thumbnail_small'] = !empty($game['thumbnail_small']) ? $game['thumbnail_small'] : '';
	$game['cover_icon'] = !empty($game['cover_icon']) ? $game['cover_icon'] : '';
	if (!empty($directory) && (empty($game['thumbnail']) || empty($game['thumbnail_small']) || empty($game['cover_icon']))) {
		$dirs = array($gamesDir . '/' . $directory, $boarddir . '/' . $games_upload . '/' . $directory);
		$scanned_directoryX = array();
		if (empty($game['thumbnail']) || empty($game['thumbnail_small']) || empty($game['cover_icon'])) {
			foreach ($dirs as $dir) {
				if (is_dir($dir)) {
					$dirY = str_replace('\\', '/', $dir);
					$scanned_directory = array();
					$scanned_directoryX = glob($dir . "/*.{jpg,png,gif,jpeg}", GLOB_BRACE);
					foreach ($scanned_directoryX as $scannedFile) {
						$dirX = str_replace('\\', '/', $scannedFile);
						if ($dirY . '/' . basename($scannedFile) != $dirX) {
							continue;
						}
						$scanned_directory[] = $scannedFile;
					}
					foreach (array('jpg', 'jpeg', 'png', 'gif') as $fileType) {
						if (empty($game['cover_icon']) && !empty($newName) && in_array($dirY . '/' . $newName . '3.' . $fileType, $scanned_directory)) {
							$game['cover_icon'] = $newName . '3.' . $fileType;
						}
						if (empty($game['thumbnail']) && !empty($newName) && in_array($dirY . '/' . $newName . '1.' . $fileType, $scanned_directory)) {
							$game['thumbnail'] = $newName . '1.' . $fileType;
						}
						if (empty($game['thumbnail']) && !empty($newName) && in_array($dirY . '/' . $newName . '_1.' . $fileType, $scanned_directory)) {
							@rename($dirY . '/' . $newName . '_1.' . $fileType, $dirY . '/' . $newName . '1.' . $fileType);
							clearstatcache();
							$game['thumbnail'] = file_exists($dirY . '/' . $newName . '1.' . $fileType) ? $newName . '1.' . $fileType : $newName . '_1.' . $fileType;
						}
						if (empty($game['thumbnail_small']) && !empty($newName) && in_array($dirY . '/' . $newName . '2.' . $fileType, $scanned_directory)) {
							$game['thumbnail_small'] = $newName . '2.' . $fileType;
						}
						if (empty($game['thumbnail_small']) && !empty($newName) && in_array($dirY . '/' . $newName . '_2.' . $fileType, $scanned_directory)) {
							@rename($dirY . '/' . $newName . '_2.' . $fileType, $dirY . '/' . $newName . '2.' . $fileType);
							clearstatcache();
							$game['thumbnail_small'] = file_exists($dirY . '/' . $newName . '2.' . $fileType) ? $newName . '2.' . $fileType : $newName . '_2.' . $fileType;
						}
					}
					if (count($scanned_directory) > 0) {
						$scanned_directory = !empty($scanned_directory) ? array_values(array_filter($scanned_directory)) : $scanned_directory;
						sort($scanned_directory, SORT_NATURAL | SORT_FLAG_CASE);
						$game['thumbnail'] = empty($game['thumbnail']) && isset($scanned_directory[0]) ? basename($scanned_directory[0]) : $game['thumbnail'];
						$game['thumbnail_small'] = empty($game['thumbnail_small']) && isset($scanned_directory[1]) ? basename($scanned_directory[1]) : $game['thumbnail_small'];
						if (!empty($game['thumbnail']))
							break;
					}
				}
			}
		}
	}

	$game['thumbnail'] = empty($game['thumbnail']) && !empty($game['thumbnail_small']) ? $game['thumbnail_small'] : $game['thumbnail'];
	$game['thumbnail_small'] = empty($game['thumbnail_small']) && !empty($game['thumbnail']) ? $game['thumbnail'] : $game['thumbnail_small'];
	$game['thumbnail'] = !empty($game['thumbnail']) ? $game['thumbnail'] : $name . '.png';
	$game['thumbnail_small'] = !empty($game['thumbnail_small']) ? $game['thumbnail_small'] : $game['thumbnail'];

	$game['thumbnail'] = is_array($game['thumbnail'])? $game['thumbnail'][0] : $game['thumbnail'];
	$game['thumbnail_small'] = is_array($game['thumbnail_small']) ? $game['thumbnail_small'][0] : $game['thumbnail_small'];
	if (empty($game['cover_icon']) && !empty($game['thumbnail'])) {
		$dirs = array($gamesDir . '/' . $directory, $boarddir . '/' . $games_upload . '/' . $directory);
		$temp_filename = uniqid('temp', true) . '.jpg';
		$coverDir = file_exists($dirs[0] . '/' . $game['thumbnail']) ? $dirs[0] : $dirs[1];
		if ($game['thumbnail'] != 'game.gif' && file_exists($coverDir . '/' . $temp_filename)) {
			$ext = pathinfo($game['thumbnail'], PATHINFO_EXTENSION);
			if ($ext == 'jpeg') {
				copy($coverDir . '/' . $game['thumbnail'], $coverDir . '/' . $temp_filename);
				if (!$source_image = @imagecreatefromjpeg($coverDir . '/' . $temp_filename)) {
					$source_image = imagecreatefromstring(file_get_contents($coverDir . '/' . $temp_filename));
				}
			}
			elseif ($ext != 'jpg') {
				$newfname = arcade_convertImageType($coverDir . '/' . $game['thumbnail'], $coverDir . '/' . $temp_filename);
				if (!$source_image = @imagecreatefromjpeg($newfname)) {
					$source_image = imagecreatefromstring(file_get_contents($newfname));
				}
			}
			elseif (!$source_image = @imagecreatefromjpeg($coverDir . '/' . $game['thumbnail'])) {
				$source_image = imagecreatefromstring(file_get_contents($coverDir . '/' . $game['thumbnail']));
			}
			list($source_imagex, $source_imagey) = array(imagesx($source_image), imagesy($source_image));
			list($dest_imagex, $dest_imagey) = array(150, 197);
			$dest_image = imagecreatetruecolor($dest_imagex, $dest_imagey);
			imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $dest_imagex, $dest_imagey, $source_imagex, $source_imagey);
			imagejpeg($dest_image, $coverDir . '/' . $newName . '3.jpg');
			$game['cover_icon'] = $newName . '3.jpg';
			if (file_exists($coverDir . '/' . $temp_filename)) {
				unlink($coverDir . '/' . $temp_filename);
			}
		}
		else {
			$coverfile = $game['submit'] == 'rom' ? 'romgamecover.jpg' : 'arcadegamecover.gif';
			@copy($settings['default_theme_dir'] . '/images/arc_icons/' . $coverfile, $coverDir . '/' . $coverfile);
			$game['cover_icon'] = $coverfile;
		}
	}

	$game['js_insertion'] = !empty($game['js_insertion']) ? (int)$game['js_insertion'] : 0;
	return $game;
}

function check_empty_folder($folder)
{
	if(is_dir($folder))
	{
		$files = array();
		if ($handle = opendir($folder))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
					$files [] = $file;
			}
			closedir ($handle);
		}
	}

	return (count($files) > 0) ? FALSE : TRUE;
}

function getGameName($internal_name)
{
	global $smcFunc;

	$internal_name = str_replace(array('_', '-'), ' ', $internal_name);

	if (strtolower(mb_substr($internal_name, -2)) == 'ch' || strtolower(mb_substr($internal_name, -2)) == 'gc')
		$internal_name = mb_substr($internal_name, 0, strlen($internal_name) - 2);
	elseif (strtolower(mb_substr($internal_name, -2)) == 'v2')
		$internal_name = mb_substr($internal_name, 0, strlen($internal_name) - 2) . ' v2';

	$internal_name = trim(str_replace(array('/', '\\'), array('', ''), trim($internal_name, '.')));
	return ucwords($internal_name);
}

function getInternalName($file, $directory)
{
	if (is_dir($directory . '/' . $file))
	{
		if (file_exists($directory . '/' . $file . '/game-info.xml'))
		{
			$gameinfo = readGameInfo($directory . '/' . $file . '/game-info.xml');
			return $gameinfo['id'];
		}
		else
			return $file;
	}

	$pos = strrpos($file, '.');

	if ($pos === false)
		return $file;

	return mb_substr($file, 0, $pos);
}

function isGame($file, $directory)
{
	// single file game?
	if (!is_dir($directory . '/' . $file) && mb_substr($file, -3) == 'swf')
		return array(true, $directory, array('file' => $file));

	// game directory?
	if (is_dir($directory . '/' . $file))
	{
		if (file_exists($directory . '/' . $file . '/' . $file . '.htm'))
			@rename($directory . '/' . $file . '/' . $file . '.htm', $directory . '/' . $file . '/' . $file . '.html');

		if (file_exists($directory . '/' . $file . '/' . $file . '.swf'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.swf')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.html'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.html')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.xap'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.xap')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.php') && file_exists($directory . '/' . $file . '/gamedata/' . $file . '/index.html'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => 'gamedata/' . $file . '/index.html')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.php') && file_exists($directory . '/' . $file . '/gamedata/' . $file . '/index.php'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => 'gamedata/' . $file . '/index.php')
			);
		elseif (file_exists($directory . '/' . $file . '/' . $file . '.php'))
			return array(
				true,
				$directory . '/' . $file,
				array('file' => $file . '.php')
			);
		elseif (file_exists($directory . '/' . $file . '/game-info.xml'))
			return array(
				true,
				$directory . '/' . $file,
				readGameInfo($directory . '/' . $file . '/game-info.xml')
			);
	}
	elseif (file_exists($directory . '/' . $file . '.php'))
		return array(
			true,
			$directory,
			array('file' => $file . '.html')
		);
	elseif (file_exists($directory . '/gamedata/' . $file . '.html'))
		return array(
			true,
			$directory,
			array('file' => 'gamedata/' . $file . '.html')
		);
	elseif (file_exists($directory . '/index.html'))
		return array(
			true,
			$directory,
			array('file' => 'index.html')
		);
	elseif (file_exists($directory . '/gamedata/index.htm')) {
		@rename($directory . '/gamedata/index.htm', $directory . '/gamedata/index.html');
		if (file_exists($directory . '/gamedata/index.html'))
			return array(
				true,
				$directory,
				array('file' => 'gamedata/index.html')
			);
	}
	elseif (file_exists($directory . '/gamedata/index.html'))
		return array(
			true,
			$directory,
			array('file' => 'gamedata/index.html')
		);

	if (substr($file, -9) == '.htaccess' || substr($file, -9) == 'index.php')
		return array(false, false, false);

	return array(false, false, false);
}

function getAvailablePendingGames($subpath = '', $recursive = true)
{
	global $arcadeModSettings, $filesDirArc, $smcFunc, $boarddir;

	if (mb_substr($subpath, -1) == '/')
		$subpath = mb_substr($subpath, 0, -1);
	$upload_directory = str_replace('\\', '/', $boarddir) . '/games_upload';
	$directory = $upload_directory . (!empty($subpath) ? '/' . $subpath : '');
	list($games, $gamefiles, $gamedirs) = array(array(), array(), array());
	$filesDirArc = !empty($filesDirArc) ? $filesDirArc : array();
	$request = $smcFunc['db_query']('', '
		SELECT id_file, game_directory, game_file
		FROM {db_prefix}arcade_files
		ORDER BY id_file',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$id = $row['id_file'];
		$gamefiles[$id] = $row['game_file'];
		$gamedirs[$id] = $row['game_directory'];
	}
	$smcFunc['db_free_result']($request);

	if ($subpath != '' && $recursive == 'unpack')
	{
		list ($is_game, $gdir, $extra) = isGame(basename($subpath), dirname($directory));

		if ($is_game)
		{
			$installed = false;
			$gdir_rel = mb_substr($gdir, strlen($upload_directory));
			$file_rel = basename($extra['file']);
			if (mb_substr($gdir_rel, 0, 1) == '/')
				$gdir_rel = mb_substr($gdir_rel, 1);
			if (!empty($gamedirs)) {
				if (in_array($gdir_rel, $gamedirs)) {
					$installed = true;
				}
			}

			$games[] = array(
				'type' => 'game',
				'directory' => $gdir_rel,
				'filename' => $extra['file'],
				'installed' => $installed,
			);
			$filesDirArc[] = $extra['file'];

			return $games;
		}
	}

	$recursive = (bool) $recursive;
	if (is_dir($directory) && $directoryHandle = opendir($directory))
	{
		while ($file = readdir($directoryHandle))
		{
			if ($file == '.' || $file == '..')
				continue;

			list ($is_game, $gdir, $extra) = isGame($file, $directory);

			if ($is_game)
			{
				$installed = false;
				$gdir_rel = mb_substr($gdir, strlen($upload_directory));
				$file_rel = basename($extra['file']);
				if (mb_substr($gdir_rel, 0, 1) == '/')
					$gdir_rel = mb_substr($gdir_rel, 1);
				if (!empty($gamedirs)) {
					if (in_array($gdir_rel, $gamedirs)) {
						$installed = true;
					}
				}

				$games[] = array(
					'type' => 'game',
					'directory' => $gdir_rel,
					'filename' => $extra['file'],
					'installed' => $installed,
				);

				$filesDirArc[] = $extra['file'];
			}
			elseif ($recursive)
			{
				$games = array_merge($games, getAvailablePendingGames((!empty($subpath) ? $subpath . '/' : '') . $file, false));
				$filesDirArc[] = $file;
			}

			unset($is_game, $gdir, $extra);
		}
		closedir($directoryHandle);
	}

	return $games;
}

function getAvailableGames($subpath = '', $recursive = true)
{
	global $arcadeModSettings, $filesDirArc, $smcFunc;

	if (mb_substr($subpath, -1) == '/')
		$subpath = mb_substr($subpath, 0, -1);

	$directory = $arcadeModSettings['gamesDirectory'] . (!empty($subpath) ? '/' . $subpath : '');
	list($games, $gamefiles, $gamedirs) = array(array(), array(), array());
	$filesDirArc = !empty($filesDirArc) ? $filesDirArc : array();
	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_directory, game_file, rom_flag
		FROM {db_prefix}arcade_games
		WHERE rom_flag = {int:rom_flag}
		ORDER BY id_game',
		array(
			'rom_flag' => 0,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (is_dir($directory . '/' . $row['game_directory'])) {
			list ($is_game, $gdir, $extra) = isGame($row['game_file'], $row['game_directory']);
			if ($is_game){
				$gdir_rel = mb_substr($gdir, strlen($arcadeModSettings['gamesDirectory']));
				$file_rel = basename($extra['file']);
				if (mb_substr($gdir_rel, 0, 1) == '/')
					$gdir_rel = mb_substr($gdir_rel, 1);
				if (!empty($gamedirs)) {
					if (in_array($gdir_rel, $gamedirs)) {
						$installed = true;
					}
				}

				$games[] = array(
					'type' => 'game',
					'directory' => $gdir_rel,
					'filename' => $extra['file'],
					'installed' => true,
				);
			}
		}
	}
	$smcFunc['db_free_result']($request);

	return $games;
}

function updateGamePendingCache($saveType = '')
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $boarddir, $smcFunc, $sourcedir;

	// Clear entries
	$smcFunc['db_query']('truncate_table', '
		TRUNCATE {db_prefix}arcade_files'
	);

	require_once($sourcedir . '/Subs-Package.php');
	loadClassFile('Class-Package.php');

	// Try to get more memory
	@ini_set('memory_limit', '256M');

	// Do actual update
	clearstatcache();
	gameCacheInsertPendingGames(getAvailablePendingGames(), $saveType);
	cache_put_data('arcade_cats', null, 604800);
}

function updateGameCache($saveType = '')
{
	global $context, $sourcedir;

	require_once($sourcedir . '/Subs-Package.php');
	loadClassFile('Class-Package.php');

	// Try to get more memory
	@ini_set('memory_limit', '256M');

	clearstatcache();

	@ini_set('memory_limit', '256M');
	$queryGamesCache = array(
		'arcade_games_list',
		'arcade_games_best',
		'arcade_games_longchamps',
		'arcade_games_latestScores',
		'arcade_games_query_small',
		'arcade_champsA_wins',
		'arcade_champsA_gen',
		'arcade_games_latestA',
		'arcade_new_champsA',
		'arcade_champsC_wins',
		'arcade_champsC_gen',
		'arcade_newChampsC',
		'arcade_champsB',
		'arcade_games_nocat',
		'arcade_catsB',
		'arcade_cats',
	);
	foreach ($queryGamesCache as $cache) {
		$rom2 = '_rom';
		cache_put_data($cache, null, 0);
		cache_put_data($cache . $rom2, null, 0);
	}

}

function gameCacheInsertPendingGames($games, $type = '', $return = false)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $boarddir, $smcFunc, $sourcedir, $boardurl;

	list($filesAvailable, $filesKeys, $filesDirArc, $fileComps, $fileName, $fileId) = array(array(), array(), array(), array(), array(), 0);
	$upload_directory = str_replace('\\', '/', $boarddir) . '/games_upload';
	if (is_dir($upload_directory) && $handle = opendir($upload_directory))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && in_array(substr($file, -3), array('rar', 'zip', 'tar', '.gz', 'RAR', 'ZIP', 'TAR', '.GZ')) && !in_array($file, $filesDirArc))
			{
				foreach (array('.gz', '.rar', '.zip', '.tar', '.GZ', '.RAR', '.ZIP', '.TAR') as $ext)
					$internalName = rtrim($file, $ext);

				$fileComps[$fileId] = str_replace('\\', '/', $internalName);
				$fileName[$fileId] = str_replace('\\', '/', $file);
			}
			elseif ($file != "." && $file != ".." && is_dir($upload_directory . '/' . $file)) {
				$strlen = strlen($file);
				if ($strlen > 5 && substr($file, 0, 5) == 'game_') {
					$newPath = substr($file, 5, $strlen-1);
					@rename($upload_directory . '/' . $file, $upload_directory . '/' . $newPath);
					if (!file_exists($upload_directory . '/' . $newPath)) {
						$error = sprintf($txt['file_move_failed'], $upload_directory . '/' . $file, $upload_directory . '/' . $newPath);
						if (!empty($arcadeModSettings['arcade_log_install_game'])) {
							log_error($error . ' [7]', 'debug');
						}
					}
				}
				foreach (array('.gz', '.rar', '.zip', '.tar', '.GZ', '.RAR', '.ZIP', '.TAR') as $ext) {
					if (file_exists($upload_directory . '/' . $file . '/' . $file . $ext)) {
						@copy($upload_directory . '/' . $file . '/' . $file . $ext, $upload_directory . '/' . $file . $ext);
						if (file_exists($upload_directory . '/' . $file . $ext)) {
							arcadeRmdir($upload_directory . '/' . $file);
							$internalName = $file;
							$fileComps[$fileId] = str_replace('\\', '/', $internalName);
							$fileName[$fileId] = str_replace('\\', '/', $file . $ext);
						}
						else {
							$error = sprintf($txt['file_move_failed'], $upload_directory . '/' . $file . '/' . $file . $ext, $upload_directory . '/' . $file . $ext);
							if (!empty($arcadeModSettings['arcade_log_install_game'])) {
								log_error($error . ' [8]', 'debug');
							}
						}
					}
					elseif (file_exists($upload_directory . '/' . $file . '/game_' . $file . $ext)) {
						@copy($upload_directory . '/' . $file . '/game_' . $file . $ext, $upload_directory . '/game_' . $file . $ext);
						if (file_exists($upload_directory . '/game_' . $file . $ext)) {
							arcadeRmdir($upload_directory . '/' . $file);
							$internalName = $file;
							$fileComps[$fileId] = str_replace('\\', '/', $internalName);
							$fileName[$fileId] = str_replace('\\', '/game_', $file . $ext);
						}
						else {
							$error = sprintf($txt['file_move_failed'], $upload_directory . '/' . $file . '/game_' . $file. $ext, $upload_directory . '/game_' . $file . $ext);
							if (!empty($arcadeModSettings['arcade_log_install_game'])) {
								log_error($error . ' [9]', 'debug');
							}
						}
					}
				}
			}
			$fileId++;
		}
		closedir($handle);
	}

	foreach ($games as $id => $game)
	{
		if (!empty($game['directory']) && is_dir(str_replace('\\', '/', $upload_directory . '/' . $game['directory'])))
		{
			$search = str_replace('\\', '/', $game['directory']);
			if (in_array($search, $fileComps))
			{
				$key = array_search ($search, $fileComps);
				$fileDel = str_replace('\\', '/',  $fileName[$key]);
				if (file_exists(str_replace('\\', '/', $upload_directory . '/' . $fileDel)))
					@unlink(str_replace('\\', '/', $upload_directory . '/' . $fileDel));
			}
		}

		if ($game['type'] == 'game')
		{
			if (!empty($game['directory']))
			{
				$game_directory = str_replace('\\', '/', $upload_directory . '/' . $game['directory']);
				$game_file = str_replace('\\', '/', $game_directory . '/' . $game['filename']);
			}
			else
			{
				$game_directory = str_replace('\\', '/', $upload_directory);
				$game_file = str_replace('\\', '/', $game['filename']);
			}

			$filesAvailable[$game_file] = $id;
			$filesKeys[$game_file] = $id;

			// Move gamedata file for IBP arcade games
			if (file_exists($game_directory . '/gamedata') && mb_substr($game['filename'], -3) == 'swf')
			{
				$from = $game_directory . '/gamedata';
				$to = $boarddir . '/arcade/gamedata/' . mb_substr($game['filename'], 0, -4);

				if (!file_exists($boarddir . '/arcade/') && !mkdir($boarddir . '/arcade/', 0755)) {
					arcadeClearSession();
					fatal_lang_error('unable_to_make', false, array($boarddir . '/arcade/'));
				}
				if (!file_exists($boarddir . '/arcade/gamedata/') && !mkdir($boarddir . '/arcade/gamedata/', 0755)) {
					arcadeClearSession();
					fatal_lang_error('unable_to_make', false, array($boarddir . '/arcade/gamedata/'));
				}
				if (!file_exists($to) && !mkdir($to, 0755)) {
					arcadeClearSession();
					fatal_lang_error('unable_to_make', false, array($to));
				}
				elseif (!is_dir($to)) {
					arcadeClearSession();
					fatal_lang_error('unable_to_make', false, array($to));
				}

				if (!is_writable($to)) {
					arcadeClearSession();
					fatal_lang_error('unable_to_chmod', false, array($to));
				}

				copyArcadeDirectory($from, $boarddir . '/arcade/gamedata');

				if (!@is_dir($to)) {
					arcadeClearSession();
					fatal_lang_error('unable_to_move', false, array($from, $to));
				}
				else
					deleteArcadeArchives($from);
			}
			elseif (file_exists($game_directory . '/gamedata'))
			{
				$from = $game_directory . '/gamedata';
				$dirs = str_replace('\\', '/', $game_directory);
				$allDirs = explode('/', $dirs);
				$dir = count($allDirs)-1;
				$to = $boarddir . '/arcade/gamedata/' . $allDirs[$dir];
				if (file_exists($game_directory . '/gamedata/' . $allDirs[$dir] . '/index.htm'))
					@rename($game_directory . '/gamedata/' . $allDirs[$dir] . '/index.htm', $game_directory . '/gamedata/' . $allDirs[$dir] . '/index.html');

				if (file_exists($game_directory . '/gamedata/' . $allDirs[$dir] . '/index.html'))
				{
					$filez = file_get_contents($game_directory . '/gamedata/' . $allDirs[$dir] . '/index.html');
					if (strpos($filez, 'function ajax2') !== false)
					{
						if (!file_exists($boarddir . '/arcade/') && !mkdir($boarddir . '/arcade/', 0755)) {
							arcadeClearSession();
							fatal_lang_error('unable_to_make', false, array($boarddir . '/arcade/'));
						}
						if (!file_exists($boarddir . '/arcade/gamedata/') && !mkdir($boarddir . '/arcade/gamedata/', 0755)) {
							arcadeClearSession();
							fatal_lang_error('unable_to_make', false, array($boarddir . '/arcade/gamedata/'));
						}
						if (!file_exists($to) && !mkdir($to, 0755)) {
							arcadeClearSession();
							fatal_lang_error('unable_to_make', false, array($to));
						}
						elseif (!is_dir($to)) {
							arcadeClearSession();
							fatal_lang_error('unable_to_make', false, array($to));
						}

						if (!is_writable($to)) {
							arcadeClearSession();
							fatal_lang_error('unable_to_chmod', false, array($to));
						}

						copyHtml52ArcadeDirectory($from, $boarddir . '/arcade/gamedata', $to);

						if (!@is_dir($to) && !empty($arcadeModSettings['arcade_log_install_game'])) {
							$error = sprintf($txt['file_move_failed'], $from, $to);
							log_error($error . ' [10]', 'debug');
						}
					}
				}
			}
		}
	}

	// check for compressed archives
	if (is_dir($upload_directory) && $handle = opendir($upload_directory))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != ".." && in_array(substr($file, -3), array('rar', 'zip', 'tar', '.gz')) && !in_array($file, $filesDirArc))
			{
				foreach (array('.gz', '.rar', '.zip', '.tar') as $ext)
					$internalName = rtrim($file, $ext);

				if (!in_array($internalName, $filesDirArc))
				{
					$games[] = array(
						'type' => 'gamepackage-multi',
						'directory' => '',
						'filename' => $file,
						'compressed' => true,
						'installed' => false,
					);
				}
				elseif (file_exists(str_replace('\\', '/', $upload_directory . '/' . $file)))

				$filesDirArc[] = str_replace('\\', '/', $internalName);

			}
		}
		closedir($handle);
	}

	list($rows, $allDirs, $gameXDirs, $gameNewDirs, $checkExists) = array(array(), array(), array(), array(), false);

	// ensure gamepacks now have their own paths
	foreach ($games as $id => $game)
	{
		if (!empty($game['directory']))
		{
			$game_directory = str_replace('\\', '/', $upload_directory . '/' . $game['directory']);
			$game_file = str_replace('\\', '/', $upload_directory . '/' . $game['directory'] . '/' . $game['filename']);
		}
		else
		{
			$game_directory = str_replace('\\', '/', $upload_directory);
			$game_file = str_replace('\\', '/', $upload_directory . '/' . $game['filename']);
		}

		if ($game['type'] == 'game')
		{
			if (in_array($game_directory, $gameXDirs) && substr($game['filename'], -5) != '.html') {
				$gameNewDirs[] = $game_directory;
			}
			elseif (substr($game['filename'], -5) != '.html')
				$gameXDirs[] = $game_directory;
		}
	}

	$resultPaths = array_unique($gameNewDirs);
	list($tempPaths, $globPaths) = array(array(), array());
	foreach ($resultPaths as $pathz) {
		foreach (array('swf', 'php') as $ext) {
			$globPathQ = glob($pathz . '/*.' . $ext);
			foreach ($globPathQ as $globPath) {
				if (!empty($globPath) && !is_array($globPath) && basename($globPath) != 'index.php') {
					$baseGlob = basename($globPath);
					$globGameName = substr($baseGlob, 0, strrpos($baseGlob, '.'));
					$tempPaths[$globGameName] = glob($pathz . '/' . $globGameName . '.*');
				}
			}
		}
	}

	foreach ($tempPaths as $path => $value) {
			$newDir = str_replace('\\', '/', $upload_directory) . '/' . $path;
			foreach ($value as $val) {
			$oldPath = str_replace('\\', '/', $val);
				if (!is_dir($newDir))
					@mkdir($newDir, 0755);
				clearstatcache();
				if (is_dir($newDir) && file_exists($oldPath)) {
					@rename($oldPath, $newDir . '/' . basename($oldPath));
				}
			}
	}

	$checkFiles = glob($upload_directory . '/*', GLOB_ONLYDIR);
	foreach ($checkFiles as $pathz) {
		$mainDir = str_replace('\\', '/', $pathz);
		if ($mainDir != $upload_directory) {
			$checkFiles = glob($mainDir . '/*');
			if (empty($checkFiles))
				arcadeRmdir($mainDir);
		}
	}
	// Last step
	foreach ($games as $id => $game)
	{
		$masterInfo = array();
		if (!empty($game['directory']))
		{
			$game_directory = str_replace('\\', '/', $upload_directory . '/' . $game['directory']);
			$game_file = str_replace('\\', '/', $upload_directory . '/' . $game['directory'] . '/' . $game['filename']);
		}
		else
		{
			$game_directory = str_replace('\\', '/', $upload_directory);
			$game_file = str_replace('\\', '/', $upload_directory . '/' . $game['filename']);
		}

		// Regular game?
		if ($game['type'] == 'game')
		{
			// Use game info if possible
			if (file_exists($game_directory . '/game-info.xml') && !isset($game['gameinfo']))
				$gameinfo = readGameInfo($game_directory . '/game-info.xml');
		}
		// Single zipped game?
		elseif ($game['type'] == 'gamepackage')
		{
			$gameinfo = read_tgz_data(file_get_contents($game_file), 'game-info.xml', true);
			$gameinfo = new xmlArray($gameinfo);
			$gameinfo = $gameinfo->path('game-info[0]');
			$gameinfo = $gameinfo->to_array();
		}
		// Gamepackage
		elseif ($game['type'] == 'gamepackage-multi')
		{
			if (strrpos($game['filename'], 'index.html') !== false || strrpos($game['filename'], 'index.htm') !== false || strrpos($game['filename'], 'index.php') !== false && !empty($game_directory))
				$game['name'] = 'GamePack ' . basename($game_directory);
			else {
				$game['name'] = 'GamePack ' . mb_substr($game['filename'], 0, strrpos(rtrim($game['filename'], '.gz'), '.'));
			}
		}

		if (isset($gameinfo) && !isset($game['name']))
			$game['name'] = $gameinfo['name'];
		elseif (!isset($game['name']))
		{
			if (strrpos($game['filename'], 'index.html') !== false || strrpos($game['filename'], 'index.php') !== false && !empty($game_directory))
				$game['name'] = basename($game_directory);
			else
				$game['name'] = getGameName(getInternalName($game['filename'], $game_directory));
		}

		// Status of game
		$status = 10;
		if (!isset($game['id']))
			$game['id'] = 0;

		if (!isset($game['directory']))
			$game['directory'] = '';

		if (!isset($game['filename']))
			$game['filename'] = '';

		// Escape data to be inserted into database
		$rows[] = array(
			$game['id'],
			$game['type'],
			$game['name'],
			$status,
			$game['filename'],
			$game['directory'],
			!empty($type) ? $type : '',
		);

		unset($gameinfo);
	}

	// check if pending installations failed to be included likely due to file name mismatch or FTP upload (only xml or php config file can fix this)
	$getPaths = arcadeReturnPaths($upload_directory);
	foreach ($rows as $row)
		$allDirs[] = $row[5];

	foreach ($getPaths as $pathX)
	{
		$pathX = trim($pathX, '/');
		$pathX = trim($pathX, '\\');
		$path = arcadeFixPath($pathX);
		$thisx = $path;
		$files = array_diff(scandir($upload_directory . '/' . $thisx), array('..', '.'));
		if (!in_array($thisx, $allDirs))
		{
			$mainFileFind = '';
			foreach ($files as $file)
			{
				$info = new SplFileInfo($file);
				$ext = ($info->getExtension());
				if ($ext == 'php' && $file != 'index.php')
				{
					$mainFileFind = $upload_directory . '/' . $thisx . '/' . $file;
					break;
				}

				if ($ext == 'xml' && $file == 'game-info.xml')
				{
					$mainFileFind = $upload_directory . '/' . $thisx . '/' . $file;
					break;
				}
			}

			if (!empty($mainFileFind) && file_exists($mainFileFind))
			{
				$flag = false;
				$gameinfo = array();
				if ($ext == 'php')
				{
					$file = file_get_contents($mainFileFind);
					if (strpos(str_replace(' ', '', $file), '$config=array(') !== false)
						@require($mainFileFind);
					$name = basename($mainFileFind, '.php');
				}
				else
				{
					$gameinfo = readGameInfo($mainFileFind);
					$name = !isset($gameinfo['id']) ? $gameinfo['id'] : '';
				}

				if (!empty($gameinfo) && $thisx != $name && !is_dir($upload_directory. '/' . $name) && is_dir($upload_directory. '/' . $thisx))
				{
					@chmod($upload_directory . '/' . $thisx, 0755);
					$rename = copyArcadeDirectory($upload_directory . '/' . $thisx, $upload_directory . '/' . $name);
					if (is_dir($upload_directory. '/' . $name))
					{
						arcadeRmdir($upload_directory . '/' . $thisx);
					}
					elseif (!empty($arcadeModSettings['arcade_log_install_game'])) {
						$error = sprintf($txt['file_move_failed'], $upload_directory . '/' . $thisx, $upload_directory . '/' . $name);
						if (!empty($arcadeModSettings['arcade_log_install_game'])) {
							log_error($error . ' [11]', 'debug');
						}
						continue;
					}

					if (substr($mainFileFind, -4) == '.php')
					{
						$gameinfo = readPhpGameInfo($name, $name, true);
					}

					$y = 0;
					foreach($rows as $checkRow)
					{
						if ($checkRow[5] == $thisx)
							$rows[$y] = array();
						$y++;
					}

					$new[] = array(
						0,
						'game',
						$name,
						10,
						$gameinfo['file'],
						arcadeFixPath($name),
						$gameinfo['submit'],
					);

					$checkExists = false;
					$request = $smcFunc['db_query']('', '
						SELECT game_name, status, game_file, game_directory
						FROM {db_prefix}arcade_files
						WHERE game_name = {string:gname} AND status = 10 AND game_file = {string:gfile} AND game_directory = {string:gdir}
						LIMIT 1',
						array('gname' => $name, 'gfile' => $gameinfo['file'], 'gdir' => arcadeFixPath($name))
					);

					while ($row = $smcFunc['db_fetch_assoc']($request))
						$checkExists = true;

					$smcFunc['db_free_result']($request);

					if (!$checkExists) {
						$smcFunc['db_insert']('insert',
							'{db_prefix}arcade_files',
							array(
								'id_game' => 'int',
								'file_type' => 'string-30',
								'game_name' => 'string-255',
								'status' => 'int',
								'game_file' => 'string-255',
								'game_directory' => 'string-255',
								'submit_system' => 'string-255',
							),
							$new,
							array('id_file')
						);
						$count = 0;
						$request = $smcFunc['db_query']('', '
							SELECT id_file
							FROM {db_prefix}arcade_files
							ORDER BY id_file DESC
							LIMIT 1',
							array()
						);

						while ($row = $smcFunc['db_fetch_assoc']($request))
							$count = $row['id_file'];

						$smcFunc['db_free_result']($request);
						$gamesx[] = $count;
						$checkExists = true;
					}
				}
			}
		}
	}

	if (!empty($rows) && empty($checkExists))
		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_files',
			array(
				'id_game' => 'int',
				'file_type' => 'string-30',
				'game_name' => 'string-255',
				'status' => 'int',
				'game_file' => 'string-255',
				'game_directory' => 'string-255',
				'submit_system' => 'string-255',
			),
			$rows,
			array('internal_name')
		);

	if (!empty($gamesx))
	{
		$_POST['file'] = $gamesx;
		$_POST[$context['session_var']] = $context['session_id'];
		ManageGamesInstall2();
		$_POST['file'] = array();
	}
	else
	{
		unset($_SESSION['qaction']);
		unset($_SESSION['qaction_data']);
	}

	if ($return)
		return $rows;
}

function arcadeFixAliasNames($gname)
{
	foreach (array('_origon', '_masodo') as $trailblazer)
		$gname = strlen(mb_substr($gname, 0, -strlen($trailblazer))) > 0 && mb_substr(mb_strtolower($gname), -strlen($trailblazer)) == $trailblazer ? mb_substr($gname, 0, -strlen($trailblazer)) : $gname;

	return $gname;
}

function arcadeGetGroups($selected = array())
{
	global $smcFunc, $txt, $sourcedir, $context;

	require_once($sourcedir . '/Subs-Members.php');

	$return = array();

	// Default membergroups.
	$return = array(
		-2 => array(
			'id' => '-2',
			'name' => $txt['arcade_group_arena'],
			'checked' => $selected == 'all' || in_array('-2', $selected),
			'is_post_group' => false,
		),
		-1 => array(
			'id' => '-1',
			'name' => $txt['guests'],
			'checked' => $selected == 'all' || in_array('-1', $selected),
			'is_post_group' => false,
		),
		0 => array(
			'id' => '0',
			'name' => $txt['regular_members'],
			'checked' => $selected == 'all' || in_array('0', $selected),
			'is_post_group' => false,
		)
	);

	$groups = groupsAllowedTo('arcade_view');

	if (!in_array(-1, $groups['allowed']))
		unset($context['groups'][-1]);
	if (!in_array(0, $groups['allowed']))
		unset($context['groups'][0]);

	// Load membergroups.
	$request = $smcFunc['db_query']('', '
		SELECT mg.group_name, mg.id_group, mg.min_posts
		FROM {db_prefix}membergroups AS mg
		WHERE mg.id_group > 3 OR mg.id_group = 2
			AND mg.id_group IN({array_int:groups})
		ORDER BY mg.min_posts, mg.id_group != 2, mg.group_name',
		array(
			'groups' => $groups['allowed'],
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$return[(int) $row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => trim($row['group_name']),
			'checked' => $selected == 'all' ||  in_array($row['id_group'], $selected),
			'is_post_group' => $row['min_posts'] != -1,
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function copyArcadeDirectory($source, $destination)
{
	if (is_dir($source))
	{
		if (!is_dir($destination))
			@mkdir($destination, 0755);

		$directory = @dir($source);
		while (FALSE !== ($readdirectory = $directory->read()))
		{
			if (in_array(basename($readdirectory), array('..', '.')))
				continue;

			$PathDir = $source . '/' . $readdirectory;

			if (is_dir($PathDir))
			{
				copyArcadeDirectory($PathDir, $destination . '/' . $readdirectory);
				continue;
			}
			elseif (file_exists($PathDir))
				copy($PathDir, $destination . '/' . $readdirectory);
		}

		$directory->close();
	}
	elseif (@file_exists($source) && !@file_exists($destination) && !in_array(basename($source), array('.', '..')))
		@copy($source, $destination);
	else
		return false;

	return true;
}

function copyHtml52ArcadeDirectory($source, $destination, $dest2)
{
	if (is_dir($source))
	{
		$destination = str_replace('\\', '/', $destination);
		$dirs = explode('/', $destination);
		$countDirs = count($dirs)-2;
		if (!is_dir($destination) && $dirs[$countDirs] == 'gamedata')
			@mkdir($destination, 0755);

		$directory = @dir($source);
		while (FALSE !== ($readdirectory = $directory->read()))
		{
			if (in_array(basename($readdirectory), array('..', '.')))
				continue;

			$PathDir = $source . '/' . $readdirectory;

			if (is_dir($PathDir))
			{
				copyHtml52ArcadeDirectory($PathDir, $destination . '/' . $readdirectory, '');
				continue;
			}
			elseif (substr($PathDir, -4) == '.txt')
			{
				$newDir = str_replace('\\', '/', dirname($destination . '/' . $readdirectory));
				if (!is_dir($newDir))
					@mkdir($newDir, 0755);

				if (is_dir($newDir) && file_exists($PathDir))
					copy($PathDir, $destination . '/' . $readdirectory);
			}
		}
		$directory->close();
		$file = @fopen($destination . '/index.html', 'w');
		if ($file)
			@fclose($file);
	}
	elseif (@file_exists($source) && !@file_exists($destination) && substr($source, -4) == '.txt')
		@copy($source, $destination);
	else
		return false;

	return true;
}

function deleteArcadeArchives($directory)
{
	global $boardurl, $boarddir;
	$directory = mb_substr($directory,-1) == "/" ? mb_substr($directory,0,-1) : $directory;

	if (is_dir($directory))
	{
		$directoryHandle = opendir($directory);
		while ($contents = readdir($directoryHandle))
		{
			if($contents != '.' && $contents != '..')
			{
				$path = $directory . "/" . $contents;
				if (is_dir($path))
					deleteArcadeArchives($path);
				elseif (file_exists($path))
					@unlink($path);
			}
		}
		closedir($directoryHandle);
		arcadeRmdir($directory);
	}
	elseif (file_exists($directory))
		@unlink($directory);
	else
		return false;

	return true;
}

function ArcadeAdminScanDir($dir, $ignore = '')
{
	$arrfiles = array();
	if (is_dir($dir))
	{
		if ($handle = opendir($dir))
		{
			chdir($dir);
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
					if (is_dir($file))
					{
						$arr = ArcadeAdminScanDir($file, '');
						if (!empty($arr))
							foreach ($arr as $value)
								$arrfiles[] = $dir . '/' . $value;
						else
							$arrfiles[] = $dir;
                    }
					elseif ((!empty($ignore)) && basename($file) == $ignore)
                        continue;
					else
						$arrfiles[] = $dir . '/' . $file;
				}
			}
			chdir("../");
		}
		closedir($handle);
	}

	return $arrfiles;
}

function ArcadeAdminCategoryDropdown()
{
	// Admin Category drop down menu
	global $scripturl, $smcFunc, $txt, $arcadeModSettings;
	$count = 0;
	$current = !empty($arcadeModSettings['arcadeDefaultCategory']) ? (int)$arcadeModSettings['arcadeDefaultCategory'] : 0;
	$selected = version_compare((!empty($arcadeModSettings['smfVersion']) ? mb_substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	$display = '
		<select name="cat_default" style="font-size: 100%;" onchange="window.location.href=this.value;">
			<option value="' . $scripturl . '?action=admin;area=arcademaintenance;sa=category;cat_default=' . $current . '">' . $txt['arcade_admin_opt_cat'] . '</option>';

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, cat_name, num_games, cat_order
		FROM {db_prefix}arcade_categories
		ORDER BY cat_order',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$count = $row['id_cat'];
		$cat_name[$count] = $row['cat_name'];

		$display .= '
			<option value="' . $scripturl . '?action=admin;area=arcademaintenance;sa=category;cat_default=' . $count . '"'. ($current == $count ? $selected : '') . '>' . $cat_name[$count] . '</option>';
	}

	$smcFunc['db_free_result']($request);

	$display .= '
			<option value="' . $scripturl . '?action=admin;area=arcademaintenance;sa=category;cat_default=all"'. ($current == 0 ? $selected : '') . '>' . $txt['arcade_all'] . '</option>
		</select>';

	return $display;
}

function arcade_return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
	$val = preg_replace("/[^0-9.]/", "", $val);
	$val = intval($val);
    switch ($last) {
        case 'g':
            $val = abs($val)*1024*1024*1024;
			break;
        case 'm':
            $val = abs($val)*1024*1024;
			break;
        case 'k':
            $val = abs($val)*1024;
			break;
		default:
			$val = abs($val);
    }

    return $val;
}

// unpack zipped archives
function arcadeUnzip($src_file, $dest_dir = false, $create_zip_name_dir = true, $overwrite = true)
{
	global $sourcedir;
	require_once($sourcedir . '/Subs-Package.php');

	$dest_dir = !empty($dest_dir) ? rtrim(str_replace('\\', '/', $dest_dir), '/') : false;
	if (class_exists('ZipArchive')) {
		$splitter = ($create_zip_name_dir === true) ? '.' : '/';
		if ($dest_dir === false)
		{
			$dest_dir = mb_substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
			$dest_dir = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $dest_dir);
		}

		$dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
		arcadeCreateDirs($dest_dir);
		$zip = new ZipArchive;
		$res = $zip->open($src_file);
		if ($res === TRUE) {
		  //$zip->extractTo($dest_dir);
			for($i = 0; $i < $zip->numFiles; $i++) {
				@$zip->extractTo($dest_dir, array($zip->getNameIndex($i)));
			}
			$zip->close();
		}
		else
			$zip = false;
	}
	elseif (function_exists('zip_open'))
	{
		if ($zip = zip_open($src_file))
		{
			if ($zip)
			{
				$splitter = ($create_zip_name_dir === true) ? '.' : '/';
				if ($dest_dir === false)
				{
					$dest_dir = mb_substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
					$dest_dir = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $dest_dir);
				}
				$dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
				arcadeCreateDirs($dest_dir);

				while ($zip_entry = zip_read($zip))
				{
					$pos_last_slash = strrpos(zip_entry_name($zip_entry), '/');
					if ($pos_last_slash !== false)
						arcadeCreateDirs($dest_dir . '/' . mb_substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));

					if (zip_entry_open($zip,$zip_entry, 'rb'))
					{
						$file_name = $dest_dir . '/' . zip_entry_name($zip_entry);
						$dir_name = dirname($file_name);

							if ($overwrite === true || ($overwrite === false && !is_file($file_name)))
							{
								$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
								@file_put_contents($file_name, $fstream);
								@chmod($file_name, 0755);
								if (substr($file_name, -4) == '.htm')
									@rename($file_name, $file_name. 'l');
							}

						zip_entry_close($zip_entry);
					}
				}

				zip_close($zip);
			}
		}
		else
			return false;
	}
	else
	{
		$splitter = ($create_zip_name_dir === true) ? '.' : '/';
		if ($dest_dir === false)
		{
			$dest_dir = mb_substr($src_file, 0, strrpos($src_file, $splitter)) . '/';
			$dest_dir = preg_replace( "/^(game)_(.+?)\.(\S+)$/", "\\2",  $dest_dir);
		}
		$dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
		arcadeCreateDirs($dest_dir);
		if (function_exists('read_zip_file'))
			$zip = read_zip_file($src_file, $dest_dir, false, false, null);
		elseif (function_exists('read_tgz_file'))
			$zip = read_tgz_file($src_file, $dest_dir, false, $overwrite, null);
		elseif (class_exists('ZipArchive')) {
			// PHP 8 fallback only if pecl-zip package is installed
			$zip = new ZipArchive;
			$res = $zip->open($src_file);
			if ($res === TRUE) {
				for($i = 0; $i < $zip->numFiles; $i++) {
					$zip->extractTo($dest_dir, array($zip->getNameIndex($i)));
				}
				$zip->close();
			}
			else
				$zip = false;
		}
		else
			$zip = false;

		if (empty($zip))
			return false;
	}

	return true;
}

// create necessary recursive directories
function arcadeCreateDirs($path)
{
	global $boarddir;
	if (!is_dir($path))
	{
		$directory_path = $boarddir . '/';
		$directories = explode("/", $path);
		array_pop($directories);

		foreach($directories as $directory)
		{
			$directory_path .= $directory . '/';
			if (!is_dir($directory_path)) {
				@mkdir($directory_path, 0755);
			}
		}
	}
}

function arcadeRmdir($dir)
{
	global $arcadeModSettings, $boarddir;

	// linux/windows compatibility
	$gamesdir = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$romGamesdir = str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$boarddirx = str_replace('\\', '/', $boarddir);
	$thisPath = str_replace('\\', '/', $dir);


	$gamesdir = trim($gamesdir, '/');
	$romGamesdir = trim($romGamesdir, '/');
	$boarddirx = trim($boarddirx, '/');
	$mainPathArray = array('Sources', 'Themes', 'Packages', 'Smileys', 'cache', 'avatars', 'attachments');
	$thisPath = trim($thisPath, '/');

	// make absolutely sure the deleted path is not an essential parent path
	if ($thisPath == '.' || $thisPath == '..')
		return false;
	if ($thisPath == $boarddirx || $thisPath == $gamesdir || $thisPath == $romGamesdir)
		return false;

	foreach ($mainPathArray as $path)
	{
		if ($thisPath == $boarddirx . '/' . $path)
			return false;
	}

	clearstatcache(false, $dir);
	if (is_dir($dir))
	{
		$objects = scandir($dir);
		foreach ($objects as $object)
		{
			if ($object != '.' && $object != '..')
			{
				clearstatcache(false, $dir . '/' . $object);
				if (is_dir($dir . '/' . $object))
					arcadeRmdir($dir . '/' . $object);
				else
					@unlink($dir . '/' . $object);
			}
		}

		reset($objects);
		clearstatcache();
		if (is_readable($dir) && is_dir($dir)) {
			$scan = @glob($dir . '/*');
			if (empty($scan)) {
				if (@rmdir($dir)) {
					clearstatcache();
					return true;
				}
			}
		}
	}

	return false;
}

function arcadeReturnPaths($path)
{
	$folders = array();

	if ((!empty($path)) && is_dir($path))
	{
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo)
		{
			if ($fileinfo->isDir() && !$fileinfo->isDot())
				$folders[] = $fileinfo->getFilename();
		}
	}

	return $folders;
}

function arcadeReturnRootGameFiles($path)
{
	$files = array();
	if (is_dir($path))
	{
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo)
		{
			if ($fileinfo->getFilename() == '.' || $fileinfo->getFilename() == '..')
				continue;

			$files[] = $fileinfo->getFilename();
		}
	}
	return $files;
}

function arcadeAlterHtmFileNames($gamedir = '')
{
	global $arcadeModSettings;
	$newPath = '';
	$arcadeModSettings['gamesDirectory'] = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$path = $arcadeModSettings['gamesDirectory'] . '/' . $gamedir . '/gamedata/' . $gamedir . '/index.htm';
	if (file_exists($path) && is_file($path))
	{
		$new = $arcadeModSettings['gamesDirectory'] . '/' . $gamedir . '/gamedata/' . $gamedir . '/index.html';
		@rename($filepath, $new);
		if (file_exists($new)) {
			$newPath = 'gamedata/' . $gamedir . '/index.html';
		}
	}
	return $newPath;
}

function arcadeInternalName($internal_name)
{
	global $smcFunc;

	$internals = array();
	$request = $smcFunc['db_query']('', '
		SELECT internal_name
		FROM {db_prefix}arcade_games
		ORDER BY internal_name',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$internals[] = $row['internal_name'];

	$smcFunc['db_free_result']($request);

	return $internals;
}

function arcadeSanitizeImg($filename = '')
{
	$newFile = preg_replace('/[^a-zA-Z0-9\-\._]/', '' , $filename);
	$newFile = trim($filename, '_');
	return $newFile;
}

function arcadeFixPath($path)
{
	$path = str_replace('\\', '/', $path);
	return $path;
}

function checkBoardExistsArcadeAdmin($boardId = 0)
{
	global $smcFunc;

	$boardId = !empty($boardId) ? (int)$boardId : 0;
	$boardName = false;

	if ($boardId < 1)
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT id_board, name
		FROM {db_prefix}boards
		WHERE id_board = {int:boardid}',
		array('boardid' => $boardId)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$boardName = !empty($row['name']) ? true : false;

	$smcFunc['db_free_result']($request);

	return $boardName;
}

function checkUserExistsArcadeAdmin($userId = 0)
{
	global $smcFunc;

	$userId = !empty($userId) ? (int)$userId : 0;
	$userName = false;

	if ($userId < 1)
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name
		FROM {db_prefix}members
		WHERE id_member = {int:userid}',
		array('userid' => $userId)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$userName = !empty($row['member_name']) ? true : false;

	$smcFunc['db_free_result']($request);

	return $userName;
}

function checkArcadeHtmlGameFiles($directory, $internal_name)
{
	global $txt;
	loadLanguage('ArcadeAdmin');
	list($submit_system, $file) = array('ibp', $internal_name . '.swf');
	if (file_exists($directory . '/' . $internal_name . '.html'))
	{
		$submit_system = 'html5';
		$file = $internal_name . '.html';
	}
	elseif (file_exists($directory . '/gamedata/' . $internal_name . '/index.html'))
	{
		$submit_system = 'html52';
		$file = 'gamedata/' . $internal_name . '/index.html';
	}
	elseif (file_exists($directory . '/' . $internal_name . '/gamedata/' . $internal_name . '.html'))
	{
		$submit_system = 'html52';
		$file = $internal_name . '/gamedata/' . $internal_name . '.html';
	}

	if (strpos($submit_system, 'html5') !== false && file_exists($directory . '/' . $internal_name . '.ini'))
		$submit_system = 'html53';

	if (strpos($submit_system, 'html5') !== false && file_exists($directory . '/' . $internal_name . '.php'))
	{
		DefinePhpBB_Constants();
		require_once($directory . '/' . $internal_name . '.php');
		if (!empty($game_data))
		{
			foreach ($game_data as $key => $info)
			{
				if (!empty($info))
					$config[$key] = un_htmlspecialchars($info);
			}
			$htmlType = isset($config['game_type']) ? $config['game_type'] : '';
			if (!empty($config['game_save_type']) && $config['game_save_type'] == 'phpbb' && stripos($htmlType, 'html5') !== false)
			{
				$submit_system = 'html53';
			}
		}
	}

	return array($submit_system, $file);
}

function DefinePhpBB_Constants()
{
	global $txt;
	if (!defined('IN_PHPBB_ARCADE'))
	{
		define('IN_PHPBB_ARCADE', 'true');
		define('IN_PHPBB', 'true');
		define('GAME_TYPE_HTML5', 'html52');
		define('GAME_TYPE_FLASH', 'flash');
		define('GAME_CONTROL_KEYBOARD_MOUSE', $txt['arcade_game_control_mouse_key']);
		define('GAME_CONTROL_KEYBOARD', $txt['arcade_game_control_key']);
		define('GAME_CONTROL_MOUSE', $txt['arcade_game_control_mouse']);
		define('GAME_CONTROL_MOUSE_TOUCH', $txt['arcade_game_control_mouse_touch']);
		define('SCORETYPE_HIGH', '2');
		define('SCORETYPE_LOW', '1');
		define('AMOD_GAME', 'v1game');
		define('IBPRO_GAME', 'ibp');
		define('ARCADELIB_GAME', 'v1game');
		define('V3ARCADE_GAME', 'v3arcade');
		define('IBPROV3_GAME', 'ibp32');
		define('IBPROV2_GAME', 'ibp2');
		define('PHPBB_RA_GAME', 'v1game');
		define('OLYMPUS_GAME', 'v1game');
		define('PHPBBARCADE_GAME', 'phpbb');
		define('NOSCORE_GAME', 'no_scoring');
		define('AR_GAME', 'v1game');
	}

	return true;
}

function arcadeFilterDbInstallText($str)
{
	$str = mb_convert_encoding($str, 'UTF-8');
	$str = strip_tags(stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $str)));
	return $str;
}

function createGame($game)
{
	global $scripturl, $boarddir, $sourcedir, $txt, $db_prefix, $user_info, $smcFunc, $arcadeModSettings;
	require_once($boarddir . '/ArcadeSources/ArcadeNewGame.php');
	$gname = arcadeFixAliasNames($game['name']);
	$game['description'] = !empty($game['description']) ? $game['description'] : '';
	$game['description'] = !empty($arcadeModSettings['arcade_adjust_desc_admin']) && empty($game['translate_skip']) ? arcade_translate($game['description'], $gname, true) : $game['description'];
	$game['description'] = arcadeFilterDbInstallText($game['description']);
	$game['help'] = !empty($arcadeModSettings['arcade_adjust_desc_admin']) && empty($game['translate_skip']) ? arcade_translate($game['help'], $gname, true) : $game['help'];
	$game['help'] = arcadeFilterDbInstallText($game['help']);
	$game['js_insertion'] = !empty($game['js_insertion']) ? (int)$game['js_insertion'] : 0;
	$game['js_insertion'] = $game['js_insertion'] < 0 || $game['js_insertion'] > 2 ? 0 : $game['js_insertion'];
	$game['scoring'] = !empty($game['score_type']) ? intval($game['score_type']) : (!empty($game['scoring']) ? intval($game['scoring']) : 0);
	$flag = false;
	// check for duplicate installation
	if ($game['internal_name'] != $game['initialInternalName'] || empty($game['game_file'])) {
		if (!empty($arcadeModSettings['arcade_log_install_game'])) {
			$error = !empty($arcadeModSettings['arcade_install_duplicate_game']) ? sprintf($txt['arcade_install_exists_fail'], $gname) : sprintf($txt['arcade_install_exists_commence'], $gname);
			$err = arcade_html_entity_decode($error, 2, 2);
			log_error($err . ' [14]', 'debug');
		}
		if (!empty($arcadeModSettings['arcade_install_duplicate_game']))
			return 0;
		else
			$flag = true;
	}
	$smcFunc['db_insert']('ignore',
		'{db_prefix}arcade_games',
		array(
			'id_cat' => 'int',
			'internal_name' => 'string',
			'game_name' => 'string',
			'submit_system' => 'string',
			'rom_system' => 'string',
			'description' => 'string',
			'help' => 'string',
			'thumbnail' => 'string',
			'thumbnail_small' => 'string',
			'cover_icon' => 'string',
			'enabled' => 'int',
			'download' => 'int',
			'js_insertion' => 'int',
			'num_rates' => 'int',
			'num_plays' => 'int',
			'game_file' => 'string',
			'game_directory' => 'string',
			'extra_data' => 'string',
			'score_type' => 'int',
			'rom_flag' => 'int',
		),
		array(
			$game['category'],
			$game['internal_name'],
			$gname,
			$game['submit_system'],
			!empty($game['rom_system']) ? $game['rom_system'] : 'none',
			$game['description'],
			$game['help'],
			!empty($game['thumbnail']) ? arcadeSanitizeImg($game['thumbnail']) : '',
			!empty($game['thumbnail_small']) ? arcadeSanitizeImg($game['thumbnail_small']) : '',
			!empty($game['cover_icon']) ? arcadeSanitizeImg($game['cover_icon']) : '',
			1,
			1,
			$game['js_insertion'],
			0,
			0,
			$game['game_file'],
			$game['game_directory'],
			!empty($game['extra_data']) ? serialize($game['extra_data']) : '',
			$game['scoring'],
			!empty($game['rom_flag']) ? 1 : 0,
		),
		array('id_game')
	);

	$request = $smcFunc['db_query']('', '
		SELECT id_game, submit_system, rom_flag, rom_system
		FROM {db_prefix}arcade_games
		WHERE id_game > 0
		ORDER BY id_game DESC
		LIMIT 1',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		list($idGame, $subSystem, $romSystem, $romFlag) = array(!empty($row['id_game']) ? $row['id_game'] : 0, !empty($row['submit_system']) ? $row['submit_system'] : (!empty($row['rom_flag']) ? 'rom' : ''), !empty($row['rom_system']) ? $row['rom_system'] : 'none', !empty($row['rom_flag']) ? $row['rom_flag'] : 0);
	}

	$smcFunc['db_free_result']($request);

	$request2 = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, submit_system, rom_flag, rom_system
		FROM {db_prefix}arcade_games
		WHERE internal_name = {string:name} || internal_name = {string:initialInternalName}
		ORDER BY id_game DESC',
		array('name' => $game['internal_name'], 'initialInternalName' => $game['initialInternalName'])
	);

	// flag duplicate internal name as a possible conflict
	while ($row = $smcFunc['db_fetch_assoc']($request2)) {
		if (empty($idConflict)) {
			$idConflict = $row['id_game'];
			$internalId = $row['id_game'];
		}
		else {
			$internalId = $row['id_game'];
		}
	}

	$smcFunc['db_free_result']($request2);

	$idConflict = !empty($idConflict) ? $idConflict : 0;
	$internalId = !empty($internalId) ? $internalId : 0;
	$subFlag = 0;
	list($id_game, $subSystem, $romSystem, $romFlag) = array(!empty($idGame) ? (int)$idGame : 0, !empty($subSystem) ? $subSystem : '', !empty($romSystem) ? $romSystem : '', !empty($romFlag) ? intval($romFlag) : 0);
	if (!empty($id_game) && !empty($idConflict) && !empty($flag) && $id_game != $idConflict)
	{
		$request_conf = $smcFunc['db_query']('', '
			SELECT submit_system_flag
			FROM {db_prefix}arcade_internal_game_conflicts
			WHERE internal_id_conflict = {int:conflictid} AND submit_system_flag > {int:zippo}
			LIMIT 1',
			array('gamedir' => $game['game_directory'], 'zippo' => 0, 'conflictid' => $idConflict)
		);

		while ($row_conf = $smcFunc['db_fetch_assoc']($request_conf))
			$subFlag = $row_conf['submit_system_flag'];
		$smcFunc['db_free_result']($request_conf);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_internal_game_conflicts
			WHERE internal_id_conflict = {int:conflictid}',
			array(
				'conflictid' => $idConflict,
			)
		);
		$smcFunc['db_insert']('ignore',
			'{db_prefix}arcade_internal_game_conflicts',
			array(
				'id_game' => 'int',
				'internal_id_conflict' => 'int',
				'internal_name_conflict' => 'string',
				'conflict_directory' => 'string',
				'submit_system_flag' => 'int',
			),
			array(
				$id_game,
				$idConflict,
				$game['internal_name'],
				$game['game_directory'],
				$subFlag,
			),
			array('id_conflict')
		);

	}

	if (empty($id_game))
		return false;

	// fix for improperly configured HTML52 games
	if ($subSystem == 'custom_game') {
		require_once($sourcedir . '/ArcadeMaintenance.php');
		ArcadeMaintenanceFileNamesHtml($id_game);
	}

	// Post message with game info if enabled
	if (!empty($arcadeModSettings['arcadeEnablePosting']) && !empty($arcadeModSettings['gamesBoard']))
		postArcadeGames(false, $game, $id_game, $romFlag);

	// Send notification if enabled in profile
	if (!empty($arcadeModSettings['arcade_newgame_notification'])) {
		arcadeEventNewGame($game, $id_game, $romFlag);
	}

	// Update does the rest...
	//updateGame($id_game, $game);
	$gameNameInst = !empty($game['name']) ? $game['name'] : $id_game;
	logAction('arcade_install_game', array('game' =>$gameNameInst));
	unset($game['internal_name'], $game['name'], $game['submit_system'], $game['game_file'], $game['game_directory'], $gameNameInst);
	return $id_game;
}

function arcade_DeepL_translate_api_limit($key)
{
	$postRequest = array(
		'auth_key' => $key,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api-free.deepl.com/v2/usage');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$result = curl_exec ($ch);
	curl_close ($ch);

    return !empty($result) ? json_decode($result, true) : array('character_count' => 0, 'character_limit' => 0);
}

function arcade_gzcompressfile(string $inFilename, int $level = 9): string
{
    // Is the file gzipped already?
    $extension = pathinfo($inFilename, PATHINFO_EXTENSION);
    if ($extension == "gz") {
        return $inFilename;
    }

    // Open input file
    $inFile = fopen($inFilename, "rb");
    if ($inFile === false) {
        throw new \Exception("Unable to open input file: $inFilename");
    }

    // Open output file
    $gzFilename = $inFilename.".gz";
    $mode = "wb".$level;
    $gzFile = gzopen($gzFilename, $mode);
    if ($gzFile === false) {
        fclose($inFile);
        throw new \Exception("Unable to open output file: $gzFilename");
    }

    // Stream copy
    $length = 512 * 1024; // 512 kB
    while (!feof($inFile)) {
        gzwrite($gzFile, fread($inFile, $length));
    }

    // Close files
    fclose($inFile);
    gzclose($gzFile);

    // Return the new filename
    return $gzFilename;
}

?>