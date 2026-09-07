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

function ArcadeMaintenance()
{
	global $boarddir, $sourcedir, $scripturl, $txt, $arcadeModSettings, $context, $settings;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/ManageServer.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAdmin.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeEmulators.php');
	require_once($boarddir . '/ArcadeSources/ManageRomGames.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeMaintenance.php');

	isAllowedTo('arcade_admin');
	loadArcade('admin', 'arcademaintenance');
	// Template
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_maintenance'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_maintenance_desc'];
	$context['arcadeFilesMax'] = 0;
	$subActions = array(
		'main' => array('ArcadeMaintenanceActions', 'arcade_admin'),
		'highscore' =>  array('ArcadeMaintenanceHighscore', 'arcade_admin'),
		'category' => array('ArcadeMaintenanceCategory', 'arcade_admin'),
		'xframe' => array('ArcadeMaintenanceXFrame', 'arcade_admin'),
		'engine' => ['ArcadeMaintenanceEngineDB', 'arcade_admin'],
		'ruffle' => array('ManageRetroArchCore', 'arcade_admin'),
		'rom_git' => array('ManageRetroArchCore', 'arcade_admin'),
		'rom_git2' => array('ManageRetroArchCore2', 'arcade_admin'),
		'rom_git3' => array('ManageRetroArchCore3', 'arcade_admin'),
		'arch_replace' => array('ManageArchReplace', 'arcade_admin'),
		'cronjobs' => array('ArcadeCronjobOptions', 'admin_forum'),
		'uninstall' => array('ArcadeUninstallOptions', 'admin_forum'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$_SESSION['emulator_admin'] = stripos($_REQUEST['sa'], 'rom_git') !== false || stripos($_REQUEST['sa'], 'arch_replace') !== false ? 'emulatorjs' : 'ruffle';
	$_SESSION['emulator_admin'] = isset($_REQUEST['emulator']) && is_string($_REQUEST['emulator']) && $_REQUEST['emulator'] == 'ruffle' ? 'ruffle' : $_SESSION['emulator_admin'];
	$emulatorTypes = explode('|', $txt['emulator_types']);
	$context['emulator_type_name'] = $_SESSION['emulator_admin'] == 'emulatorjs' ? $emulatorTypes[0] : $emulatorTypes[1];

	if (stripos($_REQUEST['sa'], 'rom') !== FALSE || stripos($_REQUEST['sa'], 'arch_') !== FALSE) {
		$context['sub_template'] = 'ManageRetroArch';
	}

	$context['html_headers'] .= '
	<style type="text/css">
		#arcade_fadeout {
			opacity: 1;
			transition: 1s opacity;
			text-align: center;
			font-size:18px;
		}
	</style>
	<script type="text/javascript">
		addSmfArcadeEvent("load", window.setTimeout(arcadefadeout, 4000), arcadefadeout);
	</script>';

	@set_time_limit(0);
	$subActions[$_REQUEST['sa']][0]();
}

function ArcadeMaintenanceActions()
{
	global $boarddir, $scripturl, $txt, $arcadeModSettings, $context, $settings;
	isAllowedTo('arcade_admin');
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	$subAction = isset($_REQUEST['sa']) && is_string($_REQUEST['sa']) ? $_REQUEST['sa'] : 'main';
	$maintenanceActions = array(
		'fixScores' => array('MaintenanceFixScores'),
		'updateGamecache' => array('MaintenanceGameCache'),
		'clearDbSessions' => array('MaintenanceClearSessions'),
		'onlinePurge' => array('ArcadeMaintenanceOnline'),
		'downloadPurge' => array('ArcadeMaintenanceDownload'),
		'uploadPurge' => array('arcadeRemoveArchives'),
		'uploadPathPurge' => array('arcadeRemoveArchives'),
		'uploadPathPurgeEmpty' => array('arcadeRemoveUploadEmpty'),
		'uploadPathPurgeAll' => array('arcadeRemoveUploadEmpty'),
		'pathPurge' => array('arcadeRemoveUnusedFolders'),
		'rufflePathPurge' => array('arcadeRemoveRuffleArchives'),
		'shoutboxPurge' => array('arcadeTruncateShouts'),
		'iconPurge' => array('arcadeCatIconPurge'),
		'filefix' => array('ArcadeMaintenanceFileNames'),
		'htmlfilefix' => array('ArcadeMaintenanceFileNamesHtml'),
		'dbfix' => array('ArcadeMaintenanceDatabase'),
		'configfix' => array('ArcadeMaintenanceConfigFiles'),
		'jsInsert' => array('ArcadeMaintenanceJsInsert'),
		'romUploadPurge' => array('arcadeRemoveRomArchives'),
		'romUploadPathPurge' => array('arcadeRemoveRomArchives'),
		'romUploadPathPurgeEmpty' => array('arcadeRemoveRomUploadEmpty'),
		'romUploadPathPurgeAll' => array('arcadeRemoveRomUploadEmpty'),
		'romPathPurge' => array('arcadeRemoveUnusedRomFolders'),
		'emulatorPathPurge' => array('arcadeRemoveEmulatorArchives'),
		'gametextfix' => array('ArcadeFixDescHelpText'),
		'gamethumbfix' => array('ArcadeFixThumbnailNames'),
		'adjustIncrement' => array('ArcadeAdjustIncrements'),
		'biosPurge' => array('arcadeTempBiosPathPurge'),
		'biosMainPurge' => array('arcadeMainBiosPathPurge'),
		'retroLibraryGet' => array('arcadeRetroLibraryGet'),
	);

	if (@extension_loaded('imagick')) {
		$maintenanceActions = array_merge($maintenanceActions, array('enhancethumbs' => array('arcadeEnhanceThumbnails')), array('enhancecovers' => array('arcadeEnhanceCovericons')), array('enhanceimages' => array('arcadeEnhanceImages')));
	}
	$context['maintenance_finished'] = false;

	if (!empty($_REQUEST['maintenance']) && isset($maintenanceActions[$_REQUEST['maintenance']]))
	{
		checkSession('request');
		$check = !empty($_REQUEST['confirm']) ? (float)$_REQUEST['confirm'] : 0;
		$action = $_REQUEST['maintenance'];
		if (array_key_exists($action, $maintenanceActions))
		{
			if ($check == 0 && in_array($action, array('enhancethumbs', 'enhancecovers')))
			{
				arcadeJsMultiConfirm('action=admin;area=arcademaintenance;maintenance=' . $action, ($txt['arcade_maintenance_conf_' . $action]) . '<br><br>' . $txt['arcade_confirm_action'], 'action=admin;area=arcademaintenance');
				$context['maintenance_finished'] = false;
				$context['maintenance_task'] = '';
			}
			elseif ($check == 0 && !in_array($action, array('enhanceimages', 'retroLibraryGet')))
			{
				arcadeJsConfirm('action=admin;area=arcademaintenance;maintenance=' . $action, ($txt['arcade_maintenance_conf_' . $action]) . '\n' . $txt['arcade_confirm_action'], 'action=admin;area=arcademaintenance');
				$context['maintenance_finished'] = false;
				$context['maintenance_task'] = '';
			}
			else
			{
				$maintenanceActions[$action][0]();

				$context['maintenance_finished'] = true;
				$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
				$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
				if (!in_array($action, array('enhanceimages', 'retroLibraryGet'))) {
					logAction('arcade_maintenance', array('task' => $task));
				}
			}
		}
	}

	if ($subAction == 'main') {
		$context['arcade_mtasks'] = array("fixScores", "updateGamecache", "clearDbSessions", "configfix", "jsInsert", "dbfix", "biosMainPurge", "biosPurge", "downloadPurge", "shoutboxPurge",
			"iconPurge", "uploadPurge", "uploadPathPurge", "uploadPathPurgeEmpty", "uploadPathPurgeAll", "pathPurge", "rufflePathPurge", "romUploadPurge", "romUploadPathPurge", "retroLibraryGet",
			"romUploadPathPurgeEmpty", "romUploadPathPurgeAll", "romPathPurge", "emulatorPathPurge", "onlinePurge", "adjustIncrement", "gametextfix", "htmlfilefix", "gamethumbfix");
	if (@extension_loaded('imagick')) {
		$context['arcade_mtasks'] = array_merge($context['arcade_mtasks'], array("enhancethumbs", "enhancecovers", "enhanceimages"));
	}
		$context['html_headers'] .= '
	<link href="' . $settings['default_theme_url'] . '/css/arcade-admin-maintenance.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />
	<script>
		$(document).ready(function() {
			function shuffle(array) {
				let places = array.map((item, index) => index);
				return array.map((item, index, array) => {
				  const random_index = Math.floor(Math.random() * places.length);
				  const places_value = places[random_index];
				  places.splice(random_index, 1);
				  return array[places_value];
				})
			}
			function arcadeRandBox(max, min){
				let arr=[];
				for (i = 0; i < max; i++) {
					let x = Math.floor( Math.random() * max) + min;
					if(arr.includes(x) == true){
						i=i-1;
					}else{
						if(x>max==false){
							arr.push(x);
						}
					}
				}
				return shuffle(arr);

			}
			$(".arcade_task_button").css("font-size", "80%");
			$(".arcade_task_button").on(
				"mouseenter", function(el){
					$(this).css("box-shadow", "0 20px 10px rgba(0, 0, 0, 0.2)");
					$(this).css("mix-blend-mode", "multiply");
					$(this).css("cursor", "pointer");
					$(this).css("font-size", "100%");
					$(this).css("transform", "translateY(-3px)");
					$(this).css("z-index", "3");

				}).on(
				"mouseleave", function(el) {
					$(this).css("mix-blend-mode", "normal");
					$(this).css("cursor", "normal");
					$(this).css("font-size", "80%");
					$(this).css("box-shadow", "0 10px 20px rgba(0, 0, 0, 0)");
					$(this).css("z-index", "2");
					$(this).css("flex-basis", "revert-layer");
					$(".arcade_task_button").css("height", "auto");
				}
			);
			const allArcadeBoxes = arcadeRandBox($(".arcade_task_button").length, 0);
			for (i = 0; i < allArcadeBoxes.length; i++) {
				var arcadeElem = document.getElementsByClassName("arcade_task_button")[allArcadeBoxes[i]];
				setTimeout((function(){$(arcadeElem).trigger("mouseenter");}(arcadeElem)), (i*25) + 1000);
				setTimeout((function(){$(arcadeElem).trigger("mouseleave");}(arcadeElem)), (i*200) + 3000);
			}
		});
	</script>';
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance';
}

function ArcadeCronjobOptions()
{
	global $boarddir, $scripturl, $txt, $arcadeModSettings, $context, $settings, $smcFunc;

	isAllowedTo('arcade_admin');

	$context['arcade_schedule'] = !empty($arcadeModSettings['arcadeCronSchedule']) ? intval($arcadeModSettings['arcadeCronSchedule']) : 0;
	$context['arcade_bios_schedule'] = !empty($arcadeModSettings['arcadeCronBiosSchedule']) ? intval($arcadeModSettings['arcadeCronBiosSchedule']) : 0;
	$context['arcadeCronSchedule'] = explode('|', $txt['arcadeCronScheduleSelect']);
	$context['arcadeCronBiosSchedule'] = explode('|', $txt['arcadeCronBiosScheduleSelect']);
	if (!empty($_REQUEST['change_cronjobs']))
	{
		checkSession();
		foreach (array('arcadeCronSchedule', 'cron_emulator_archives', 'cron_game_download', 'cron_game_upload', 'cron_game_romupload', 'cron_game_tempbios') as $setting) {
			$val = !empty($_REQUEST[$setting]) && $setting == 'arcadeCronSchedule' ? intval($_REQUEST[$setting]) : (!empty($_REQUEST[$setting]) && intval($_REQUEST[$setting]) == 1 ? 1 : 0);
			updateArcadeSettings(array($setting => $val));
		}
		$context['maintenance_task'] = $txt['arcadeCronScheduleText'];
		$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
		$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
		logAction('arcade_maintenance', array('task' => $task));

		redirectexit('action=admin;area=arcademaintenance;sa=cronjobs;' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance');
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance_cronjobs';
}

function ArcadeMaintenanceJsInsert()
{
	global $smcFunc, $arcadeModSettings, $context, $txt;

	$arcadeModSettings['arcadeJsInsertDefault'] = !empty($arcadeModSettings['arcadeJsInsertDefault']) && in_array($arcadeModSettings['arcadeJsInsertDefault'], array(0, 1, 2)) ? $arcadeModSettings['arcadeJsInsertDefault'] : 2;
	$games = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_game
		FROM {db_prefix}arcade_games
		ORDER BY id_game',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$games[] = $row['id_game'];

	$smcFunc['db_free_result']($request);

	foreach($games as $game)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_games
			SET js_insertion = {int:jsInsertion}
			WHERE id_game = {int:gameid}',
			array(
				'gameid' => $game,
				'jsInsertion' => $arcadeModSettings['arcadeJsInsertDefault'],
			)
		);
	}
	$context['maintenance_task'] = $txt['arcade_maintenance_jsInsert'];
}

function ArcadeMaintenanceHighscore()
{
	global $boarddir, $scripturl, $txt, $arcadeModSettings, $context, $settings, $smcFunc;

	if (isset($_REQUEST['score_action']))
	{
		checkSession();

		if ($_REQUEST['score_action'] == 'older' && is_numeric($_REQUEST['age']))
		{
			$_REQUEST['age'] = (int)$_REQUEST['age'];
			$_REQUEST['age'] = abs($_REQUEST['age']);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores
				WHERE end_time < {int:time}',
				array(
					'time' => time() - ((int) $_REQUEST['age'] * 86400)
				)
			);
			$context['maintenance_task'] = sprintf($txt['arcade_remove_scores_log_variable'], $_REQUEST['age']);
		}
		elseif ($_REQUEST['score_action'] == 'all')
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores',
				array(
				)
			);

			$context['maintenance_task'] = $txt['arcade_remove_scores_log_all'];
		}
		$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
		$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
		logAction('arcade_maintenance', array('task' => $task));

		redirectexit('action=admin;area=arcademaintenance;maintenance=fixScores;back=score;' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance');
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance_highscore';
}

function MaintenanceFixScores()
{
	global $arcadeModSettings, $smcFunc, $context, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT id_game, score_type, extra_data
		FROM {db_prefix}arcade_games');

	while ($row = $smcFunc['db_fetch_assoc']($request))
		ArcadeFixScores($row['id_game'], $row['score_type']);

	$smcFunc['db_free_result']($request);

	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		$queryGamesCache = array(
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
		);
		foreach ($queryGamesCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}

	if (isset($_REQUEST['back']) && $_REQUEST['back'] == 'score')
		redirectexit('action=admin;area=arcademaintenance;sa=highscore');
	else
		$context['maintenance_task'] = $txt['arcade_maintenance_fixScores'];
}

function MaintenanceClearSessions()
{
	global $context, $txt;

	arcadeClearSession();
	arcadeClearDbSession();
	MaintenanceGameCache();

	$context['maintenance_task'] = $txt['arcade_maintenance_clearDbSessions'];
}

function MaintenanceGameCache()
{
	global $db_prefix, $arcadeModSettings, $smcFunc, $context, $txt;

	loadClassFile('Class-Package.php');
	@ini_set('memory_limit', '256M');
	$entireCache = [
		'arcade_games_nocat',
		'arcade_games_suggest',
		'arcade_online_count',
		'arcade_online_guestcount',
		'arcade_online_list',
		'arcade_online_guestlist',
		'arcade_most_played',
		'arcade_games_players',
		'arcade_games_recommended',
		'arcade_games_latestScores',
		'arcade_user_counts',
		'arcade_games_query_small',
		'arcade_champsA_wins',
		'arcade_champsA_gen',
		'arcade_games_latestA',
		'arcade_new_champsA',
		'arcade_new_gamesA',
		'arcade_popularA',
		'arcade_shouts',
		'arcade_thumbsA0',
		'arcade_thumbsA1',
		'arcade_thumbsA2',
		'arcade_thumbsA3',
		'arcade_newestB',
		'arcade_champsC_wins',
		'arcade_champsC_gen',
		'arcade_games_latestC',
		'arcade_newChampsC',
		'arcade_newestC',
		'arcade_popularC',
		'arcade_champsB',
		'arcade_catsB',
		'arcade_cats',
		'game_of_day',
		'arcade_most_played',
		'arcade_games_rating',
		'arcade_games_best',
		'arcade_games_mostactive',
		'arcade_games_longchamps',
	];

	foreach ($entireCache as $cache) {
		$rom2 = '_rom';
		clean_cache($cache);
		clean_cache($cache . $rom2);
	}

	updateArcadeSettings([
		'game_time' => date('ymd'),
		'game_of_day' => 0,
		'game_time_rom'  => date('ymd'),
		'game_of_day_rom' => 0
	]);

	$context['maintenance_task'] = $txt['arcade_maintenance_updateGamecache'];
}

function ArcadeFixScores($id_game, $score_type)
{
	global $db_prefix, $arcadeModSettings, $smcFunc;

	// This will use a lot of queries so don't use unless necessary ;)
	if ($score_type == 0)
		$order = 'DESC';
	elseif ($score_type == 1)
		$order = 'ASC';
	else
		return false;

	$users = array();
	$position = 1;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS scores, id_member
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
		GROUP BY id_member',
		array(
			'game' => $id_game,
		)
	);

	$removeScores = array();
	$scoreCount = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($arcadeModSettings['arcadeMaxScores']) && $row['scores'] > $arcadeModSettings['arcadeMaxScores'])
		{
			$removeScores[$row['id_member']] = $row['scores'] - $arcadeModSettings['arcadeMaxScores'];
			$scoreCount[$row['id_member']] = $row['scores'] - $arcadeModSettings['arcadeMaxScores'];
		}
		else
		{
			$scoreCount[$row['id_member']] = $row['scores'];
		}
	}
	$smcFunc['db_free_result']($request);

	// Remove some scores
	if (!empty($removeScores))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member, id_score
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:game}
				AND id_member IN({array_int:members})
			ORDER BY score ' . ($score_type == 0 ? 'ASC' : 'DESC'),
			array(
				'game' => $id_game,
				'members' => array_keys($removeScores)
			)
		);

		$removeIds = array();

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($removeScores[$row['id_member']] > 0)
			{
				$removeIds[] = $row['id_score'];
				$removeScores[$row['id_member']]--;
			}
		}
		$smcFunc['db_free_result']($request);

		if (!empty($removeIds)) {
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores
				WHERE id_score IN({array_int:scores})',
				array(
					'scores' => $removeIds,
				)
			);
		}
	}

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET personal_best = 0
		WHERE id_game = {int:game}',
		array(
			'game' => $id_game,
		)
	);

	$request = $smcFunc['db_query']('', '
		SELECT id_score, score, id_member, position
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
		ORDER BY score ' . $order,
		array(
			'game' => $id_game,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		updateGame($id_game, array('champion' => 0, 'champion_score' => 0));

	// Positions and personal best
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$set = array();

		if (!in_array($row['id_member'], $users))
		{
			$users[] = $row['id_member'];
			$set[] = 'personal_best = 1';
		}

		if ($position != $row['position'])
			$set[] = 'position = {int:position}';

		if (count($set) > 0)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET ' . implode(',', $set) . '
				WHERE id_score = {int:score}',
				array(
					'score' => $row['id_score'],
					'position' => $position,
				)
			);

		if ($position == 1)
			updateGame($id_game, array('champion' => $row['id_member'], 'champion_score' => $row['id_score'],));

		$position++;
	}
	$smcFunc['db_free_result']($request);

	// And champion times is still left
	$request = $smcFunc['db_query']('', '
		SELECT id_score, score, end_time
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
		ORDER BY score ' . $order,
		array(
			'game' => $id_game,
		)
	);

	$best = 0;
	$best_id = 0;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (($score_type == 0 && $best <= $row['score']) || ($score_type == 1 && $best >= $row['score']))
		{
			$end = $row['end_time'] - 1;

			if ($best_id > 0)
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_scores
					SET champion_from = end_time, champion_to = {int:champion_to}
					WHERE id_score = {int:best}',
				array(
					'champion_to' => $end,
					'best' => $best_id,
				)
			);

			$best = $row['score'];
			$best_id = $row['id_score'];
		}
	}
	$smcFunc['db_free_result']($request);

	return true;
}

function ArcadeFixThumbnailNames($id_game = 0)
{
	global $smcFunc, $txt, $context, $arcadeModSettings;

	isAllowedTo('arcade_admin');
	list($games, $fileTypes) = array(array(), array('jpg', 'jpeg', 'png', 'gif'));
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$id_game = !empty($id_game) && is_int($id_game) ? intval($id_game) : 0;
	$where = !empty($id_game) ? 'id_game = {int:idgame} LIMIT 1' : 'id_game > {int:idgame} ORDER BY id_game ASC';
	$request = $smcFunc['db_query']('', '
		SELECT id_game, internal_name, thumbnail, thumbnail_small, cover_icon, game_directory, rom_flag
		FROM {db_prefix}arcade_games
		WHERE ' . $where,
		array(
			'idgame' => abs($id_game),
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$games[] = array(
			'id_game' => $row['id_game'],
			'internal_name' => rtrim($row['internal_name'], '_'),
			'game_directory' => !empty($row['game_directory']) ? $row['game_directory'] : '',
			'thumbnail' => !empty($row['thumbnail']) ? $row['thumbnail'] : '',
			'thumbnail_small' => !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : '',
			'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
			'rom_flag' => !empty($row['rom_flag']) ? 1 : 0,
		);
	}
	$smcFunc['db_free_result']($request);
	clearstatcache();
	foreach ($games as $game) {
		list($update, $thumb1, $thumb2) = array('', '', '');
		$path = !empty($game['rom_flag']) ? $romGamesDirectory : $gamesDirectory;
		$path = !empty($game['game_directory']) ? $path . '/' . $game['game_directory'] : $path;
		if (!empty($game['thumbnail']) && !is_dir($path . '/' . $game['thumbnail']) && file_exists($path . '/' . $game['thumbnail'])) {
			$ext = pathinfo($path . '/' . $game['thumbnail'], PATHINFO_EXTENSION);
			if (in_array(strtolower($ext), $fileTypes)) {
				$newFile = arcade_sanitize_file_name($game['internal_name'] . '1.' . $ext, 'file');
				if (!is_dir($path . '/' . $newFile) && $newFile != $game['thumbnail'] && file_exists($path . '/' . $newFile)) {
					@unlink($path . '/' . $newFile);
				}
				@rename($path . '/' . $game['thumbnail'], $path . '/' . $newFile);
				clearstatcache();
				if (!is_dir($path . '/' . $newFile) && file_exists($path . '/' . $newFile)) {
					$update = 'SET thumbnail = {string:thumb1}';
					$thumb1 = $newFile;
					$game['thumbnail'] = $newFile;
				}
			}
		}
		elseif (empty($game['thumbnail']) && !empty($game['thumbnail_small']) && !is_dir($path . '/' . $game['thumbnail_small']) && file_exists($path . '/' . $game['thumbnail_small'])) {
			$ext = pathinfo($path . '/' . $game['thumbnail_small'], PATHINFO_EXTENSION);
			$newFile = arcade_sanitize_file_name($game['internal_name'] . '1.' . $ext, 'file');
			if (in_array(strtolower($ext), $fileTypes)) {
				@copy($path . '/' . $game['thumbnail_small'], $path . '/' . $newFile);
				if (!is_dir($path . '/' . $newFile) && file_exists($path . '/' . $newFile)) {
					$update = 'SET thumbnail = {string:thumb1}';
					$thumb1 = $newFile;
					$game['thumbnail'] = $newFile;
				}
			}
		}
		else {
			list($xfiles, $yfiles, $found) = array(array(), array(), false);
			foreach ($fileTypes as $extType) {
				$newFile = arcade_sanitize_file_name($game['internal_name'] . '1.' . $extType, 'file');
				$newFile2 = arcade_sanitize_file_name($game['internal_name'] . '2.' . $extType, 'file');
				if (file_exists($path . '/' . $newFile) && basename($newFile) != $game['thumbnail_small']) {
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail = {string:thumb1}';
					list($thumb1, $game['thumbnail'], $found) = array($newFile, $newFile, true);
				}
				elseif (file_exists($path . '/' . $newFile) && basename($newFile) == $game['thumbnail_small']) {
					if (!file_exists($path . '/' . $newFile2)) {
						@copy($path . '/' . $newFile, $path . '/' . $newFile2);
						clearstatcache();
					}
					if (file_exists($path . '/' . $newFile2)) {
						$update .= 'thumbnail2 = {string:thumb2}, ';
						list($thumb2, $game['thumbnail_small']) = array($newFile2, $newFile2);
					}
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail = {string:thumb1}';
					list($thumb1, $game['thumbnail'], $found) = array($newFile, $newFile, true);
				}
				elseif (file_exists($path . '/' . $newFile2) && basename($newFile2) == $game['thumbnail_small']) {
					@copy($path . '/' . $newFile2, $path . '/' . $newFile);
					clearstatcache();
					if (file_exists($path . '/' . $newFile)) {
						$update = !empty($update) ? $update . ', ' : 'SET ';
						$update .= 'thumbnail = {string:thumb1}, thumbnail2 = {string:thumb2}';
						list($thumb1, $game['thumbnail'], $thumb2, $game['thumbnail_small'], $found) = array($newFile, $newFile, $newFile2, $newFile2, true);
					}
				}
			}
			if (empty($found)) {
				$xfiles = glob($path . '/*.{' . (implode(',', $fileTypes)) . '}', GLOB_BRACE);
				$imgFile2 = str_replace('\\', '/', $path . '/' . $game['thumbnail_small']);
				$path2 = str_replace('\\', '/', $path);
				foreach ($xfiles as $file) {
					$file = str_replace('\\', '/', $file);
					if ($file == $path2 . '/' . basename($file)) {
						$yfiles[] = $file;
					}
				}
				foreach ($yfiles as $file) {
					$ext = pathinfo($file, PATHINFO_EXTENSION);
					$newFile = arcade_sanitize_file_name($game['internal_name'] . '1.' . $ext, 'file');
					if (!empty($game['thumbnail_small']) && !empty($yfiles) && !in_array($imgFile2, $yfiles)) {
						if (basename($file) != $game['thumbnail'])
							@rename($file, $path2 . '/' . $newFile);
						else
							@copy($file, $path2 . '/' . $newFile);
						clearstatcache();
						if (file_exists($path2 . '/' . $newFile)) {
							$update = !empty($update) ? $update . ', ' : 'SET ';
							$update .= 'thumbnail = {string:thumb1}';
							$thumb1 = $newFile;
							$game['thumbnail'] = $newFile;
							break;
						}
					}
				}
			}
		}

		if (!empty($game['thumbnail_small']) && !is_dir($path . '/' . $game['thumbnail_small']) && file_exists($path . '/' . $game['thumbnail_small'])) {
			$ext = pathinfo($path . '/' . $game['thumbnail_small'], PATHINFO_EXTENSION);
			$newFile = arcade_sanitize_file_name($game['internal_name'] . '2.' . $ext, 'file');
			if (in_array(strtolower($ext), $fileTypes)) {
				if (!is_dir($path . '/' . $newFile) && $newFile != $game['thumbnail_small'] && file_exists($path . '/' . $newFile)) {
					@unlink($path . '/' . $newFile);
				}
				if ($game['thumbnail_small'] != $game['thumbnail'])
					@rename($path . '/' . $game['thumbnail_small'], $path . '/' . $newFile);
				else
					@copy($path . '/' . $game['thumbnail_small'], $path . '/' . $newFile);
				clearstatcache();

				if (!is_dir($path . '/' . $newFile) && file_exists($path . '/' . $newFile)) {
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail_small = {string:thumb2}';
					$thumb2 = $newFile;
					$game['thumbnail_small'] = $newFile;
				}
			}
		}
		elseif (empty($game['thumbnail_small']) && !empty($game['thumbnail']) && !is_dir($path . '/' . $game['thumbnail']) && file_exists($path . '/' . $game['thumbnail'])) {
			$ext = pathinfo($path . '/' . $game['thumbnail'], PATHINFO_EXTENSION);
			$newFile = arcade_sanitize_file_name($game['internal_name'] . '2.' . $ext, 'file');
			if (in_array(strtolower($ext), $fileTypes)) {
				@copy($path . '/' . $game['thumbnail'], $path . '/' . $newFile);
				if (!is_dir($path . '/' . $newFile) && file_exists($path . '/' . $newFile)) {
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail_small = {string:thumb2}';
					$thumb2 = $newFile;
					$game['thumbnail_small'] = $newFile;
				}
			}
		}
		elseif (!empty($game['thumbnail_small']) && !empty($game['thumbnail']) && !is_dir($path . '/' . $game['thumbnail']) && file_exists($path . '/' . $game['thumbnail']) && !file_exists($path . '/' . $game['thumbnail_small'])) {
			$ext = pathinfo($path . '/' . $game['thumbnail'], PATHINFO_EXTENSION);
			$newFile = arcade_sanitize_file_name($game['internal_name'] . '2.' . $ext, 'file');
			if (in_array(strtolower($ext), $fileTypes)) {
				@copy($path . '/' . $game['thumbnail'], $path . '/' . $newFile);
				if (!is_dir($path . '/' . $newFile) && file_exists($path . '/' . $newFile)) {
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail_small = {string:thumb2}';
					$thumb2 = $newFile;
					$game['thumbnail_small'] = $newFile;
				}
			}
		}
		else {
			list($xfiles, $yfiles, $found) = array(array(), array(), false);
			foreach ($fileTypes as $extType) {
				$newFile = arcade_sanitize_file_name($game['internal_name'] . '2.' . $extType, 'file');
				$newFile2 = arcade_sanitize_file_name($game['internal_name'] . '1.' . $extType, 'file');
				if (file_exists($path . '/' . $newFile) && basename($newFile) != $game['thumbnail']) {
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail_small = {string:thumb2}';
					list($thumb2, $game['thumbnail_small'], $found) = array($newFile, $newFile, true);
				}
				elseif (file_exists($path . '/' . $newFile) && basename($newFile) == $game['thumbnail_small']) {
					if (!file_exists($path . '/' . $newFile2)) {
						@copy($path . '/' . $newFile, $path . '/' . $newFile2);
						clearstatcache();
					}
					if (file_exists($path . '/' . $newFile2)) {
						$update .= 'thumbnail2 = {string:thumb2}, ';
						list($thumb2, $game['thumbnail_small']) = array($newFile2, $newFile2);
					}
					$update = !empty($update) ? $update . ', ' : 'SET ';
					$update .= 'thumbnail = {string:thumb1}';
					list($thumb1, $game['thumbnail'], $found) = array($newFile, $newFile, true);
				}
				elseif (file_exists($path . '/' . $newFile2) && basename($newFile2) == $game['thumbnail']) {
					@copy($path . '/' . $newFile2, $path . '/' . $newFile);
					clearstatcache();
					if (file_exists($path . '/' . $newFile)) {
						$update = !empty($update) ? $update . ', ' : 'SET ';
						$update .= 'thumbnail_small = {string:thumb2}';
						list($thumb2, $game['thumbnail_small'], $found) = array($newFile, $newFile, true);
					}
				}
			}
			if (empty($found)) {
				$xfiles = glob($path . '/*.{' . (implode(',', $fileTypes)) . '}', GLOB_BRACE);
				$imgFile1 = str_replace('\\', '/', $path . '/' . $game['thumbnail']);
				$path2 = str_replace('\\', '/', $path);
				foreach ($xfiles as $file) {
					$file = str_replace('\\', '/', $file);
					if ($file == $path2 . '/' . basename($file)) {
						$yfiles[] = $file;
					}
				}
				foreach ($yfiles as $file) {
					$ext = pathinfo($file, PATHINFO_EXTENSION);
					$newFile = arcade_sanitize_file_name($game['internal_name'] . '2.' . $ext, 'file');
					if (!empty($game['thumbnail']) && !empty($yfiles) && !in_array($imgFile1, $yfiles)) {
						if (basename($file) != $game['thumbnail_small'])
							@rename($file, $path2 . '/' . $newFile);
						else
							@copy($file, $path2 . '/' . $newFile);
						clearstatcache();
						if (file_exists($path2 . '/' . $newFile)) {
							$update = !empty($update) ? $update . ', ' : 'SET ';
							$update .= 'thumbnail_small = {string:thumb2}';
							$thumb2 = $newFile;
							$game['thumbnail_small'] = $newFile;
							break;
						}
					}
				}
			}
		}

		if (!empty($update)) {
			$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					' . $update . '
					WHERE id_game = {int:gameid}',
				array(
					'gameid' => $game['id_game'],
					'thumb1' => $thumb1,
					'thumb2' => $thumb2,
				)
			);
		}
	}

	$context['maintenance_task'] = $txt['arcade_maintenance_gamethumbfix'];
	$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
	$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
	logAction('arcade_maintenance', array('task' => $task));
	@ini_set('memory_limit', '256M');
	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		$gameRelatedCache = array(
			'arcade_games_query_small',
			'arcade_thumbsA0',
			'arcade_thumbsA1',
			'arcade_thumbsA2',
			'arcade_thumbsA3',
		);
		foreach ($gameRelatedCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}
	//redirectexit('action=admin;area=arcademaintenance;sa=gametextfix;maintenance=done;');
}

function ArcadeFixDescHelpText()
{
	// filter characters from help & game descriptions
	global $smcFunc, $txt, $context;

	isAllowedTo('arcade_admin');
	$games = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_game, description, help
		FROM {db_prefix}arcade_games
		WHERE id_game > {int:idgame}
		ORDER BY id_game ASC',
		array(
			'idgame' => 0,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$games[] = array(
			'id_game' => $row['id_game'],
			'description' => !empty($row['description']) ? ArcadeFilterDbText($row['description']) : '',
			'help' => !empty($row['help']) ? ArcadeFilterDbText($row['help']) : '',
			'old_help' => !empty($row['help']) ? $row['help'] : '',
			'old_description' => !empty($row['description']) ? $row['description'] : '',
		);
	}
	$smcFunc['db_free_result']($request);

	foreach ($games as $game) {
		if ($game['help'] != $game['old_help'] || $game['description'] != $game['old_description']) {
			$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET description = {string:desc}, help = {string:help}
					WHERE id_game = {int:gameid}',
				array(
					'gameid' => $game['id_game'],
					'desc' => $game['description'],
					'help' => $game['help'],
				)
			);
		}
	}

	$context['maintenance_task'] = $txt['arcade_maintenance_gametextfix'];
	$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
	$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
	logAction('arcade_maintenance', array('task' => $task));
	//redirectexit('action=admin;area=arcademaintenance;sa=gametextfix;maintenance=done;');
}

function ArcadeFilterDbText($str)
{
	$arcade_ascii_only = !empty($_POST['arcade_ascii_only']) && $_POST['arcade_ascii_only'] == 'ascii' ? true : false;
	$str = mb_convert_encoding($str, 'UTF-8');
	$str = strip_tags(stripslashes(preg_replace('/\\\{1,}/u', '\\\\', $str)));

	if (!empty($arcade_ascii_only)) {
		$accepted = 'a-zA-Z0-9\s`~!@#$%^&*()_+-={}|:;<>?,.\/"\'\\\[\]';
		$str = preg_replace('~[^' . $accepted . ']\|\]\[\\\~iu', '', $str);
	}
	return $str;
}

function ArcadeFixCategories($type = 'undefault')
{
	global $db_prefix, $arcadeModSettings, $smcFunc, $context, $txt;

	isAllowedTo('arcade_admin');
	$arcadeModSettings['arcadeDefaultCategory'] = !empty($arcadeModSettings['arcadeDefaultCategory']) ? (int)$arcadeModSettings['arcadeDefaultCategory'] : 0;
	if ($type === 'undefault')
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, IFNULL(cat.cat_name, {string:empty}) AS cn
			FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_categories AS cat ON cat.id_cat = game.id_cat',
			array(
				'empty' => '',
			)
		);

		while ($game = $smcFunc['db_fetch_assoc']($request))
			if ($game['cn'] == '')
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET id_cat = {int:category}
					WHERE id_game = {int:game}',
					array(
						'category' => $arcadeModSettings['arcadeDefaultCategory'],
						'game' => $game['id_game'],
					)
				);

		$smcFunc['db_free_result']($request);
		$context['maintenance_task'] = $txt['arcade_cats_undefault'];
	}
	elseif ($type === 'default')
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game
			FROM {db_prefix}arcade_games AS game
			ORDER BY id_game',
			array()
		);

		while ($game = $smcFunc['db_fetch_assoc']($request))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET id_cat = {int:category}
				WHERE id_game = {int:game}',
				array(
					'category' => $arcadeModSettings['arcadeDefaultCategory'],
					'game' => $game['id_game'],
				)
			);

		$smcFunc['db_free_result']($request);

		$context['maintenance_task'] = $txt['arcade_cats_default'];
	}
	else
		$context['maintenance_task'] = $txt['arcade_cats_peruse'];

	// recount number of games for each category
	$category = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_game, id_cat
		FROM {db_prefix}arcade_games
		WHERE id_game
		ORDER BY id_game',
		array()
	);

	while ($game = $smcFunc['db_fetch_assoc']($request))
	{
		$id = !empty($game['id_cat']) ? (int)$game['id_cat'] : 0;
		$category[$id] = empty($category[$id]) ? 1 : $category[$id] + 1;
	}
	$smcFunc['db_free_result']($request);

	foreach ($category as $catid => $total)
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET num_games = {int:total}
			WHERE id_cat = {int:category}',
			array(
				'category' => $catid,
				'total' => $total,
			)
		);

	$request = $smcFunc['db_query']('', '
		SELECT id_cat
		FROM {db_prefix}arcade_categories
		WHERE id_cat
		ORDER BY id_cat',
		array()
	);

	while ($cat = $smcFunc['db_fetch_assoc']($request))
	{
		$id = !empty($cat['id_cat']) ? (int)$cat['id_cat'] : 0;
		if(empty($category[$id]))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_categories
				SET num_games = {int:total}
				WHERE id_cat = {int:category}',
				array(
					'category' => $id,
					'total' => 0,
				)
			);
	}

	$smcFunc['db_free_result']($request);

	$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
	$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
	logAction('arcade_maintenance', array('task' => $task));
	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		$clearCache = array(
			'arcade_games_query_small',
			'arcade_games_nocat',
			'arcade_catsB',
			'arcade_cats'
		);
		foreach ($clearCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}

	if ($type == 'uninstall')
		return;

	redirectexit('action=admin;area=arcademaintenance;sa=category;maintenance=done;');
}

function ArcadeMaintenanceCategory()
{
	global $boarddir, $scripturl, $txt, $arcadeModSettings, $context, $settings;

	$maintenanceActions = array(
		'ArcadeFixCats' => array('fixCategories'),
	);

	$context['maintenance_finished'] = false;
	$arcadeModSettings['arcadeDefaultCategory'] = !empty($arcadeModSettings['arcadeDefaultCategory']) ? (int)$arcadeModSettings['arcadeDefaultCategory'] : 0;

	if ((isset($_REQUEST['cat_default'])) && $arcadeModSettings['arcadeDefaultCategory'] != (int)$_REQUEST['cat_default'])
	{
		checkSession('request');
		$setArray['arcadeDefaultCategory'] = (int)$_REQUEST['cat_default'];
		updateArcadeSettings($setArray);
		$arcadeModSettings['arcadeDefaultCategory'] = (int)$_REQUEST['cat_default'];
		$context['maintenance_finished'] = true;
	}
	elseif (isset($_REQUEST['cat_action']))
	{
		checkSession('request');
		if ($_REQUEST['cat_action'] == 'peruse')
			ArcadeFixCategories('peruse');
		elseif ($_REQUEST['cat_action'] == 'default')
			ArcadeFixCategories('default');
		else
			ArcadeFixCategories('undefault');

		$context['maintenance_finished'] = true;
	}

	// Template
	$context['sub_template'] = 'arcade_admin_maintenance_category';
}

function ArcadeMaintenanceOnline()
{
	global $smcFunc, $txt, $context, $arcadeModSettings;
	$time = time();

	// Just check we haven't ended up with something theme exclusive somehow.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_member_data
		WHERE {int:now} - online_time > 600',
		array(
			'now' => $time,
		)
	);

	$clearCache = array('arcade_online_count', 'arcade_online_guestcount', 'arcade_online_list', 'arcade_online_guestlist');
	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		foreach ($clearCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}
	$context['maintenance_task'] = $txt['arcade_maintenance_onlinePurge'];
}

function arcadeTruncateShouts()
{
	global  $smcFunc, $txt, $context;
	isAllowedTo('arcade_admin');

	$smcFunc['db_query']('', 'TRUNCATE {db_prefix}arcade_newshouts',array());
	$context['maintenance_task'] = $txt['arcade_maintenance_shoutboxPurge'];
}

function arcadeCatIconPurge()
{
	global $settings, $smcFunc, $txt, $context;
	list($cats, $theme_paths) = array(array(), array());

	// do not delete the default icons
	$ignore = array(
		'1.gif',
		'2.gif',
		'3.gif',
		'arcade.jpg',
		'arcade_edit.gif',
		'arcade_esc.png',
		'arcade_star.gif',
		'arcade_star2.gif',
		'arenabg1.jpg',
		'arenabg2.png',
		'arena_accept.png',
		'arena_decline.png',
		'blank.png',
		'border-image.png',
		'cancel.png',
		'cat_new.gif',
		'collapse.gif',
		'comment.png',
		'contract.png',
		'cup_g.gif',
		'cup_s.gif',
		'cup_b.gif',
		'Default.gif',
		'del1.png',
		'dl_btn_popup.png',
		'download_icon.png',
		'expand.gif',
		'expand.png',
		'favorite.gif',
		'favorite2.gif',
		'file_downloading.gif',
		'game.gif',
		'game_info.png',
		'game_popup_saver.swf',
		'gold.gif',
		'guest_na.gif',
		'icons.png',
		'index.php',
		'input_bg.png',
		'medals.png',
		'modify.png',
		'noavatar.gif',
		'online.gif',
		'pdl_clean.gif',
		'pm_recipient_delete.gif',
		'popup_play_btn.gif',
		'profile_download.png',
		'profile_online.png',
		'profile_reported.png',
		'profile_rlist.png',
		'sort_down.gif',
		'sort_up.gif',
		'star.gif',
		'star2.gif',
		'star3.gif',
		'star4.gif',
		'stats_info.gif',
		'thearcade.png',
		'titlebar_arena_bg.png',
		'trophy.png',
		'trophy_blank.png',
		'Unassigned.gif'
		);

	$request = $smcFunc['db_query']('', '
		SELECT cat_icon
		FROM {db_prefix}arcade_categories
		ORDER BY cat_icon ASC',
		array()
	);

	// do not delete the preset category icons
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($cats['cat_icon']))
			$cats[] = $row['cat_icon'];
	}
	$smcFunc['db_free_result']($request);

	$files = ArcadeScanDir($settings['default_theme_dir'] . '/images/arc_icons', array_merge_recursive($cats, $ignore));

	foreach ($files as $file)
	{
		if ((!empty($file)) && file_exists($file))
			unset($file);
	}

	// remove files leftover in custom themes
	$request = $smcFunc['db_query']('', '
		SELECT id_theme, variable, value
		FROM {db_prefix}themes
		WHERE variable = {string:themedir}',
		array(
			'themedir' => 'theme_dir',
		)
	);
	$theme_paths = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$theme_paths[] = $row['value'];

	$smcFunc['db_free_result']($request);

	foreach ($theme_paths as $path)
	{
		if (is_dir($path))
		{
			$customFiles = ArcadeScanDir($path . '/images/arc_icons', array());

			foreach ($customFiles as $file)
				if ((!empty($file)) && file_exists($file))
					unset($file);

			// just in case something was copied to a custom theme... remove the entire arc_icons directory from that theme if it is empty
			$customFilesCheck = ArcadeScanDir($path . '/images/arc_icons', array());
			if (empty($customFilesCheck) && is_dir($path . '/images/arc_icons'))
				rmdir($path . '/images/arc_icons');
		}
	}

	$context['maintenance_task'] = $txt['arcade_maintenance_iconPurge'];
}

function ArcadeAdjustIncrements()
{
	global $db_type, $smcFunc, $context, $txt;
	isAllowedTo("arcade_admin");
	list($arcade_skins, $arcade_lists, $arcade_mobile_skins, $arcade_mobile_lists) = array(0, 0, 0, 0);

	// reset some increments
	foreach (array('arcade_skins', 'arcade_lists', 'arcade_mobile_skins', 'arcade_mobile_lists') as $table) {
		$type = stripos($table, 'list') !== FALSE ? 'list' : 'skin';
		$request = $smcFunc['db_query']('', '
			SELECT id_{raw:type}
			FROM {db_prefix}{raw:tb}
			ORDER BY id_{raw:type} DESC
			LIMIT 1',
			array('tb' => $table, 'type' => $type)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$$type = $row['id_' . $type];
		}
		$smcFunc['db_free_result']($request);

		if (stripos($db_type, 'mysql') !== FALSE || stripos($db_type, 'mariadb') !== FALSE) {
			$request = $smcFunc['db_query']('', '
				ALTER TABLE {db_prefix}{raw:tb} AUTO_INCREMENT = {int:new}',
				array('tb' => $table, 'new' => $$type+1)
			);
		}
		elseif (stripos($db_type, 'sqlite') !== FALSE) {
			$request = $smcFunc['db_query']('', '
				UPDATE SQLITE_SEQUENCE SET SEQ={int:new} WHERE NAME={db_prefix}{raw:tb}',
				array('tb' => $table, 'new' => $$type+1)
			);
		}
		elseif (stripos($db_type, 'postgres') !== FALSE) {
			$request = $smcFunc['db_query']('', '
				ALTER SEQUENCE {db_prefix}{raw:tb}_{raw:ind}_seq RESTART WITH {int:new}',
				array('tb' => $table, 'new' => $$type+1, 'ind' => 'id_' . $type)
			);
		}
	}

	$context['maintenance_task'] = $txt['arcade_maintenance_adjustIncrement'];
	$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
	$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
	logAction('arcade_maintenance', array('task' => $task));
}

function arcadeRemoveRuffleArchives()
{
	global $settings, $boarddir, $txt, $context;
	$fileTypes = '{zip,gz,tar,rar,7z,ZIP,GZ,TAR,RAR,7Z}';
	$themeDir = str_replace('\\', '/', $settings['default_theme_dir']);
	$dir = rtrim($themeDir, '/') . '/arcade_scripts/ruffle';
	if (empty($dir) || $dir == $boarddir)
		return false;

	foreach (glob($dir . "/*." . $fileTypes, GLOB_BRACE) as $filename)
		unlink($filename);

	arcadeRmDir($dir . '/updated_files');
	clearstatcache();
	$context['maintenance_task'] = str_replace($themeDir . '/', '', $txt['arcade_maintenance_rufflePathPurge']);
}

function ArcadeMaintenanceXFrame()
{
	global $txt, $context, $arcadeModSettings, $settings, $boardurl, $arcade_version, $boarddir;

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$suffixVersion = preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version);
	$serverSoftware = strtolower(!empty($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown');
	$path = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$filepath = rtrim($path, '/');
	$romPath = str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$romFilepath = rtrim($path, '/');
	$path_array = explode('/', $path);
	$context['arcade_filepath'] = array_values(array_slice($path_array, -1))[0];
	$context['arcade_xframe_instruct'] = false;
	$https_protocol = '
	RewriteCond %{QUERY_STRING} ^(.*)\;sslRedirect$
	RewriteRule ^(.*)$ /$1?%1 [L,R=301]
	RewriteCond %{QUERY_STRING} ^(.*)?sslRedirect$
	RewriteRule ^(.*)$ /$1?%1 [L,R=301]';

	if(stripos($serverSoftware, 'apache') !== false) {
		list($context['arcade_server'], $context['arcade_server_title'], $context['arcade_server_software']) = array(1, $txt['arcade_xframe_apache'], ucwords($serverSoftware));
	}
	elseif(stripos($serverSoftware, 'litespeed') !== false) {
		list($context['arcade_server'], $context['arcade_server_title'], $context['arcade_server_software']) = array(1, $txt['arcade_xframe_litespeed'], ucwords($serverSoftware));
	}
	elseif (stripos($serverSoftware, 'microsoft-iis') !== false) {
		list($context['arcade_server'], $context['arcade_server_title'], $context['arcade_server_software']) = array(2, $txt['arcade_xframe_iis'], ucwords($serverSoftware));
	}
	else {
		list($context['arcade_server'], $context['arcade_server_title'], $context['arcade_server_software']) = array(3, $txt['arcade_xframe_other'], ucwords($serverSoftware));
	}

	$context['maintenance_finished'] = false;

	if (isset($_REQUEST['select']) && isset($_REQUEST['create_xframe']) && $_REQUEST['create_xframe'] == 1)
	{
		if (!is_dir($gamesDirectory)) {
			@mkdir($gamesDirectory, 0755);
			@chmod($gamesDirectory, 0755);
		}
		else {
			@chmod($gamesDirectory, 0755);
		}
		if (!is_dir($romGamesDirectory)) {
			@mkdir($romGamesDirectory, 0755);
			@chmod($romGamesDirectory, 0755);
		}
		else {
			@chmod($romGamesDirectory, 0755);
		}

		// create Apache .htaccess file
		checkSession('request');
		ArcadeMaintenanceXFrameDelete();
		$infofile ='#<!-- ' . $txt['arcade_xframe_apache'] . ' File Created by SMF Arcade ' . $arcade_version . ' -->';
		$infofile .= '
<IfModule mod_headers.c>
	<FilesMatch "\.(html)$">
		Header always set X-Frame-Options SAMEORIGIN
	</FilesMatch>
</IfModule>
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{REQUEST_URI} arcade_score\.php
	RewriteRule ./* ../arcade/arcade_score.php?act=arcade&scoreprep=prepscore
	RewriteCond %{REQUEST_URI} highscore=1
	RewriteRule ./* ../arcade/arcade_score.php?scoreprep=1&gameid=45&highscore=1
	RewriteCond %{QUERY_STRING} ^act=Arcade&do=(.*)$
	RewriteRule ./* ../index.php?act=Arcade&do=newscore
</IfModule>
<IfModule mod_security.c>
	<FilesMatch ".(zip|tar|rar|gz)$">
		SecFilterEngine Off
		SecFilterScanPOST Off
	</FilesMatch>
</IfModule>
<Files ^(*.jpeg|*.jpg|*.png|*.gif|*.tiff|*.bmp|*.php|*.html)>
	Order Deny,Allow
	Deny from all
	Allow from localhost
</Files>
AddCharset UTF-8 .php
AddCharset UTF-8 .html
AddCharset UTF-8 .htm
AddCharset UTF-8 .js
RemoveHandler .php3 .phtml .cgi .fcgi .pl .fpl .shtml
';
		$infofile2 = '
<IfModule mod_headers.c>
	<FilesMatch "\.(html)$">
		Header always set X-Frame-Options SAMEORIGIN
	</FilesMatch>
</IfModule>
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{REQUEST_URI} arcade_score\.php
	RewriteRule ./* ../arcade/arcade_score.php?act=arcade&scoreprep=prepscore
	RewriteCond %{REQUEST_URI} highscore=1
	RewriteRule ./* ../arcade/arcade_score.php?scoreprep=1&gameid=45&highscore=1
	RewriteCond %{QUERY_STRING} ^act=Arcade&do=(.*)$
	RewriteRule ./* ../index.php?act=Arcade&do=newscore
</IfModule>
<IfModule mod_security.c>
	<FilesMatch ".(zip|tar|rar|gz)$">
		SecFilterEngine Off
		SecFilterScanPOST Off
	</FilesMatch>
</IfModule>
<Files ^(*.php|*.html)>
	Order Deny,Allow
	Deny from all
	Allow from localhost
</Files>
AddCharset UTF-8 .php
AddCharset UTF-8 .html
AddCharset UTF-8 .htm
AddCharset UTF-8 .js
RemoveHandler .php3 .phtml .cgi .fcgi .pl .fpl .shtml
<Files *>
	Order Deny,Allow
	Deny from all
	Allow from localhost
</Files>
<FilesMatch ".(zip|7z|jpg|jpeg|gif|png|bmp|svg|gd3|gd7|dx2|bsx|cgb|ss|gdi|scd|sgg|al|a52|dat|lst|pcfx|mame|bin|adf|adz|dms|fdi|ipf|raw|hdf|hdz|lha|slave|info|cue|ccd|chd|nrg|mds|iso|uae|m3u|nds|fds|nes|unif|unf|gb|gbc|dmg|col|cv|rom|mdx|md|smd|gen|bms|sms|gg|sg|68k|sgd|lnx|ngp|ngc|pce|img|toc|exe|pbp|ws|wsc|pc2|vb|vboy|gba|n64|v64|z64|u1|ndd|mdf|cbn|32x|elf|cso|prx|a78|smc|sfc|swc|fig|bs|st|a26|d64|d6z|d71|d7z|d80|d81|d82|d8z|g64|g6z|g41|g4z|x64|x6z|nib|nbz|d2m|d4m|t64|tap|tcrt|prg|p00|crt|cmd|vfl|vsf|gz|20|40|60|a0|b0|j64|jag|abs|cof)$">
	Order Deny,Allow
    Allow from all
</FilesMatch>
RemoveHandler .exe .php .php3 .phtml .cgi .fcgi .pl .fpl .shtml';
		if (!file_exists($filepath . '/.htaccess'))
		{
			$fp = fopen($filepath . '/.htaccess', 'w');
			fwrite($fp, $infofile, strlen($infofile));
			fclose ($fp);
		}
		if (!file_exists($romFilepath . '/.htaccess'))
		{
			$fp = fopen($romFilepath . '/.htaccess', 'w');
			fwrite($fp, $infofile2, strlen($infofile2));
			fclose ($fp);
		}

		if (file_exists($boarddir . '/Themes/default/arcade_scripts/ruffle/.htaccess'))
			@unlink($boarddir . '/Themes/default/arcade_scripts/ruffle/.htaccess');

		if (!file_exists($boarddir . '/Themes/default/arcade_scripts/ruffle/.htaccess'))
		{
			$infofile3 = '#<!-- Apache File Created by SMF Arcade ' . $arcade_version . ' -->';
			$infofile3 .= '
<IfModule mod_mime.c>
	AddType application/wasm .wasm
</IfModule>
AddCharset UTF-8 .js';

			$fp = fopen($boarddir . '/Themes/default/arcade_scripts/ruffle/.htaccess', 'w');
			fwrite($fp, $infofile3, strlen($infofile));
			fclose ($fp);

			@chmod($boarddir . '/Themes/default/arcade_scripts/ruffle/.htaccess', 0644);
		}

		$context['maintenance_finished'] = true;
		$context['maintenance_file'] = '.htaccess';
		$context['maintenance_task'] = $txt['arcade_xframe_file'][0];
		$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
		$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
		logAction('arcade_maintenance', array('task' => $task));
	}
	elseif (isset($_REQUEST['create_xframe']) && $_REQUEST['create_xframe'] == 2)
	{
		// create IIS web.config file
		checkSession('request');
		ArcadeMaintenanceXFrameDelete();
		if (!file_exists($filepath . '/web.config'))
		{
			$infofile ='<!-- ' . $txt['arcade_xframe_iis'] . ' File Created by SMF Arcade ' . $arcade_version . ' -->';
			$infofile .= '
<system.webServer>
	<httpProtocol>
		<customHeaders>
			<add name="X-Frame-Options" value="SAMEORIGIN" />
		</customHeaders>
	</httpProtocol>
	<rewrite>
        <rules>
			<rule name="Redirect to HTTPS" stopProcessing="true">
				<match url="(.*)" />
				<conditions>
					<add input="{HTTPS}" pattern="^OFF$" />
				</conditions>
				<action type="Redirect" url="https://{HTTP_HOST}/{R:1}" redirectType="SeeOther" />
			</rule>
            <rule name="SpecificRedirect" stopProcessing="true">
                <match url="^arcade_score.php$" />
                <action type="Redirect" url="/arcade/arcade_score.php?act=arcade&scoreprep=prepscore" />
				<match url="^highscore=1$" />
                <action type="Redirect" url="/arcade/arcade_score.php?scoreprep=1&gameid=45&highscore=1" />
				<match url="act=Arcade&do=$" />
                <action type="Redirect" url="/index.php?act=Arcade&do=newscore" />
            </rule>
        </rules>
    </rewrite>
</system.webServer>
<configuration>
    <system.web>
        <globalization
           requestEncoding="utf-8"
           responseEncoding="utf-8"
        />
    </system.web>
</configuration>';

			$fp = fopen($filepath . '/web.config', 'w');
			fwrite($fp, $infofile, strlen($infofile));
			fclose ($fp);
		}

		if (file_exists($boarddir . '/Themes/default/arcade_scripts/ruffle/web.config'))
			@unlink($boarddir . '/Themes/default/arcade_scripts/ruffle/web.config');

		if (!file_exists($boarddir . '/Themes/default/arcade_scripts/ruffle/web.config'))
		{
			$infofile = '<!-- Windows-IIS File Created by SMF Arcade ' . $arcade_version . ' -->';
			$infofile .= '
<configuration>
   <system.webServer>
      <staticContent>
         <mimeMap fileExtension=".wasm" mimeType="application/wasm" />
      </staticContent>
   </system.webServer>
</configuration>
<configuration>
    <system.web>
        <globalization
           requestEncoding="utf-8"
           responseEncoding="utf-8"
        />
    </system.web>
</configuration>';
			$fp = fopen($boarddir . '/Themes/default/arcade_scripts/ruffle/web.config', 'w');
			fwrite($fp, $infofile, strlen($infofile));
			fclose ($fp);

			@chmod($boarddir . '/Themes/default/arcade_scripts/ruffle/web.config', 0644);
		}

		$context['maintenance_finished'] = true;
		$context['maintenance_file'] = 'web.config';
		$context['maintenance_task'] = $txt['arcade_xframe_file'][0];
		$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
		$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
		logAction('arcade_maintenance', array('task' => $task));

	}
	elseif (isset($_REQUEST['select']) && isset($_REQUEST['create_xframe']) && $_REQUEST['create_xframe'] == 3)
	{
		// display instructions for editing server software config
		$context['html_headers'] .= '
		<link href="' . $settings['default_theme_url'] . '/css/arcade-xframe.css?v' . $suffixVersion . '" rel="stylesheet" type="text/css" />';
		$context['arcade_xframe_instruct'] = 'display';
	}
	elseif (isset($_REQUEST['select']) && isset($_REQUEST['create_xframe']) && $_REQUEST['create_xframe'] == 4)
	{
		// remove configuration file if opted
		checkSession('request');
		ArcadeMaintenanceXFrameDelete();

		$context['maintenance_finished'] = true;
		$context['maintenance_file'] = '.htaccess / web.config';
		$context['maintenance_task'] = $txt['arcade_xframe_file'][1];
		$task = !empty($context['maintenance_task']) ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
		$context['maintenance_task'] = !empty($context['maintenance_task']) ? $context['maintenance_task']  . ' : ' : '';
		logAction('arcade_maintenance', array('task' => $task));
	}

	// Template
	list($_REQUEST['create_xframe'], $_REQUEST['select']) = array('', '');
	$context['sub_template'] = 'arcade_admin_maintenance_xframe';
}

function ArcadeMaintenanceXFrameDelete()
{
	// remove configuration file if opted
	global $arcadeModSettings;

	$path = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$filepath = rtrim($path, '/');
	foreach (array('.htaccess', 'web.config') as $file)
	{
		if (file_exists($filepath . '/' . $file))
			unlink($filepath . '/' . $file);
	}

	clearstatcache();
}

function ArcadeMaintenanceEngineDB()
{
	global $txt, $context, $arcadeModSettings, $settings, $boardurl, $arcade_version, $boarddir, $scripturl;

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$context['maintenance_engine'] = '';
	$context['arcade_db_engine'] = arcadeGetEngineDB('default');
	$context['arcade_smf_db_engine'] = arcadeGetEngineDB('members');
	$context['arcade_maintenance_engine'] = arcadeGetEngineDB('arcade_games');
	$context['maintenance_finished'] = false;
	$context['arcade_db_engines'] = arcadeGetEngineDB('all');
	$knownEngines = ['innodb', 'myisam', 'aria', 'mrg_myisam'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	$bytes = random_bytes(11);
    $hexString = bin2hex($bytes);
	$new = substr($hexString, 0, 11);

	if (isset($_POST['select']) && isset($_POST['change_engine']) && isset($_POST['engine_confirm']))
	{
		checkSession('post');
		$engineSelect = (int)$_POST['change_engine'];
		if (isset($_POST['engine_' . $engineSelect])) {
			$newEngine = $_POST['engine_' . $engineSelect];
			$context['maintenance_engine'] = in_array(strtolower($newEngine), array_map('strtolower', $knownEngines)) ? $newEngine : '';
		}

		if (!empty($context['maintenance_engine'])) {
			$doTask = arcadeSetEngineDB($context['maintenance_engine']);
			$context['arcade_maintenance_engine'] = arcadeGetEngineDB('arcade_games');
			$context['maintenance_task'] = sprintf($txt['arcade_engine_task'], $context['maintenance_engine']);
		}

		$context['maintenance_finished'] = true;

		$task = !empty($context['maintenance_task']) && !empty($doTask) && $context['maintenance_engine'] == $context['arcade_maintenance_engine'] ? $context['maintenance_task'] : $txt['arcade_task_unknown'];
		logAction('arcade_maintenance', array('task' => $task));
	}

	// Template
	list($_POST['change_engine'], $_POST['select']) = ['', ''];
	$options = explode('|', $txt['arcade_maintenance_engine_options']);
	$context['html_headers'] .= '
		<link href="' . $settings['default_theme_url'] . '/css/arcade-prompt.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />
		<script src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-prompt.js"></script>
		<script>
			function confirmArcadeConf2() {
				let formData = [], engineNum = $("input[name=change_engine]:checked").val();
				let engineVal = $("input[name=engine_" + engineNum+ "]").val();
				formData.push({ name: "engine_confirm", value: "1" });
				formData.push({ name: "change_engine", value: engineNum });
				formData.push({ name: "engine_" + engineNum, value: engineVal });
				formData.push({ name: "select", value: "1" });
				formData.push({ name: "' . $context['session_var'] . '", value: "' . $context['session_id'] . '" });
				$.confirm({
					boxWidth: "30%",
					useBootstrap: false,
					title: \'<div><div style="padding-bottom: 0.25rem;text-decoration: underline;font-size: small;font-family: Copperplate;">' . str_replace('"', '\\"', $txt['arcade_maintenance_engine_conf_title']) . '</div></div>\',
					content: \'<div style="padding: 0rem;font-size: small;">' . $txt['arcade_maintenance_engine_conf_notes'] . '</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">' . $txt['arcade_maintenance_engine_conf'] . '</div>\',
					buttons: {
						engine: {
							text: "' . $options[0] . '",
							btnClass: "btn-blue",
							action: function(){
								$.ajax({
									type: "POST",
									url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=engine;#engine_form",
									data: formData,
									success: function(response) {
										console.log("Form submitted successfully:", response);
										setTimeout(function(){

											window.location.replace("' . $scripturl . '?action=admin;area=arcademaintenance;sa=engine;new=' . $new . ';#engine_form");
										},1000);
									},
									error: function(xhr, status, error) {
										console.error("Error submitting form:", error);
									}
								});
							}
						},
						cancel: {
							text: "' . $options[1] . '",
							action: function(){
								setTimeout(function(){
									 window.location.replace("' . $scripturl . '?action=admin;area=arcademaintenance;sa=engine;#engine_form");
								},1000);
							}
						}
					}
				});
			}
			$(document).ready(function() {
				$("#engine_submit").click(function(event) {
					' . (!isset($_POST['engine_confirm']) ? '
					confirmArcadeConf2();
					return false;' : '') . '
				});
			});
		</script>';
	$context['sub_template'] = 'arcade_admin_maintenance_db_engine';
}

function arcadeRemoveUnusedFolders()
{
	global $smcFunc, $arcadeModSettings, $boarddir, $txt, $context;
	$folders = array();
	$dirnames = array();
	$gamesdir = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : '';
	$gamesdir = rtrim($gamesdir, '/');
	$boarddirx = str_replace('\\', '/', $boarddir);

	if (!empty($gamesdir))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, game_directory, rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game AND rom_flag = {int:romflag}
			ORDER BY id_game',
			array(
				'romflag' => 0,
			)
		);

		while ($game = $smcFunc['db_fetch_assoc']($request))
		{
			$id = !empty($game['id_cat']) ? (int)$game['id_cat'] : 0;
			$folders[] = !empty($game['game_directory']) ? trim($game['game_directory'], '/') : '';
		}
		$smcFunc['db_free_result']($request);

		$paths = arcadeReturnPaths($gamesdir);

		foreach ($folders as $folder)
		{
			$folder = str_replace('\\', '/', $folder);
			$check = explode('/', $folder);
			if (count($check) > 1)
				$dirname = dirname($folder);
			else
				$dirname = $folder;

			$dirname = trim($dirname, '/');
			$dirnames[] = $dirname;
		}

		foreach ($paths as $path)
		{
			$parents = explode('/', $path);
			if (!empty($parents))
			{
				$parent = str_replace('\\', '/', $parents[0]);
				if (!in_array($parent, $dirnames))
				{
					$dir = dirname($gamesdir . '/' . $parent);
					$base = $gamesdir . '/' . $parent;
					$gd = $gamesdir;
					$bd = $boarddirx;
					foreach (array('/', '\\') as $sep)
					{
						$base = rtrim($base, $sep);
						$dir = rtrim($dir, $sep);
						$gd = rtrim($gd, $sep);
						$bd = rtrim($bd, $sep);
					}
					if (is_dir($base) && $base != $gd && $base != $bd)
					{
						if ($base != $dir)
						{
							$files = ArcadeAdminScanDir($base, '');
							foreach ($files as $file)
								if (!is_dir($file) && str_replace('\\', '/', $file) != str_replace('\\', '/', $gd) . '/index.php')
									unlink($file);

							if (count(scandir($base)) == 2)
								arcadeRmdir($base);

							$paths = arcadeReturnPaths($base);
							if (!empty($paths))
							{
								foreach($paths as $path)
									if ($base . '/' . $path != $gd)
										arcadeRmdir($base . '/' . $path);
							}

							if (empty(arcadeReturnPaths($base)))
							{
								if (file_exists($dir . '/master-info.xml'))
									unlink($dir . '/master-info.xml');

								if ($base != $gd)
									arcadeRmdir($base);
							}

							if (empty(arcadeReturnPaths($dir)) && $dir != $gd)
								arcadeRmdir($dir);
						}
					}
				}
			}
		}

		clearstatcache();
		$context['maintenance_task'] = str_replace($boarddirx . '/', '', $txt['arcade_maintenance_pathPurge']);
	}
	else
	{
		$context['maintenance_task'] = str_replace($arcadeModSettings['gamesDirectory'] . ' ~ ', $txt['arcade_maintain_none'], $txt['arcade_maintenance_pathPurge']);
	}

}

function arcadeJsConfirm($forward, $message, $return)
{
	global $scripturl, $context;
	if (!empty($message))
		$context['html_headers'] .= '
		<script>
			function confirmArcadeConf() {
				var checkMaintenance = confirm(\'' . str_replace('"', '\\"', $message) . '\');
				if (checkMaintenance == true)
					window.location.replace("' . $scripturl . '?' . $forward . ';confirm=1;' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance");
				else
					window.location.replace("' . $scripturl . '?' . $return . ';confirm=0;#arcade_maintenance");
			}
			$(document).ready(function() {
				confirmArcadeConf();
			});
		</script>';
	else
		redirectexit();

	return false;
}

function arcadeJsMultiConfirm($forward, $message, $return)
{
	global $scripturl, $settings, $arcadeModSettings, $context, $txt;

	$navOptions = explode('|', $txt['arcade_maintenance_conf_options']);
	$strOptions = explode('|', $txt['arcade_maintenance_conf_descript']);
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));

	if (!empty($message))
		$context['html_headers'] .= '
		<link href="' . $settings['default_theme_url'] . '/css/arcade-prompt.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />
		<script src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-prompt.js"></script>
		<script>
			function confirmArcadeConf() {
				$.confirm({
					boxWidth: "30%",
					useBootstrap: false,
					title: \'<div style="padding-bottom: 0.5rem;text-decoration: none;font-size: small;">' . str_replace('"', '\\"', $message) . '</div>\',
					content: \'' . sprintf('<div class="centertext"><div>%s</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">%s</div><div>%s</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">%s</div><div>%s</div></div>', $strOptions[1], $strOptions[0], $strOptions[2], $strOptions[0], $strOptions[3]) . '\',
					buttons: {
						backup: {
							text: "' . $navOptions[0] . '",
							btnClass: "btn-blue",
							action: function(){
								var link = document.createElement("a");
								link.href = "' . $scripturl . '?' . $forward . ';confirm=1;' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance";
								document.body.appendChild(link);
								link.click();
								setTimeout(function(){
									 link.remove();
								},3000);
							}
						},
						nobackup: {
							text: "' . $navOptions[1] . '",
							btnClass: "btn-blue",
							action: function(){
								var link = document.createElement("a");
								link.href = "' . $scripturl . '?' . $forward . ';confirm=2;' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance";
								document.body.appendChild(link);
								link.click();
								setTimeout(function(){
									 link.remove();
								},3000);
							}
						},
						restore: {
							text: "' . $navOptions[2] . '",
							btnClass: "btn-blue",
							action: function(){
								var link = document.createElement("a");
								link.href = "' . $scripturl . '?' . $forward . ';confirm=3;' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance";
								document.body.appendChild(link);
								link.click();
								setTimeout(function(){
									 link.remove();
								},3000);
							}
						},
						cancel: {
							text: "' . $navOptions[3] . '",
							action: function(){
								setTimeout(function(){
									 window.location.replace("' . $scripturl . '?' . $return . ';confirm=0;#arcade_maintenance");
								},3000);
							}
						}
					}
				});
			}
			$(document).ready(function() {
				confirmArcadeConf();
			});
		</script>';
	else
		redirectexit();

	return false;
}

function ArcadeMaintenanceFileNames()
{
	// remove configuration file if opted
	global $smcFunc, $arcadeModSettings, $boarddir, $scripturl, $txt, $context, $smfVersion;

	isAllowedTo('arcade_admin');
	list($games, $folders, $dirnames, $gamecount) = array(array(), array(), array(), 0);
	$boarddirx = str_replace('\\', '/', $boarddir);
	$startAt = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
	$start = abs($startAt);
	$context['maintenance_task'] = $txt['arcade_maintenance_fileFix'];
	$arcadeFilesMax = explode('|', $txt['arcadeFilesMaxOptions']);
	$maxString = !empty($arcadeModSettings['arcadeFilesMax']) ? strval($arcadeFilesMax[$arcadeModSettings['arcadeFilesMax']]) : $arcadeFilesMax[0];
	$arcadeModSettings['arcadeFilesMax'] = !empty($arcadeModSettings['arcadeFilesMax']) ? (int)$arcadeModSettings['arcadeFilesMax'] : 0;
	$max = !empty($arcadeModSettings['arcadeFilesMax']) ? abs(intval($arcadeFilesMax[$arcadeModSettings['arcadeFilesMax']])) : $arcadeFilesMax[0];
	$num = $smcFunc['db_query']('', '
      SELECT count(*)
      FROM {db_prefix}arcade_games
      WHERE id_game > 0 AND rom_flag = 0'
	);

	list ($gamecount) = $smcFunc['db_fetch_row']($num);
	$smcFunc['db_free_result']($num);
	$max = stripos($maxString, 'all') === false ? $max : $gamecount;

	if ($gamecount > 0 && $start <= $gamecount)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, game_directory, game_file, thumbnail, thumbnail_small, cover_icon, rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game > 0
			ORDER BY id_game
			LIMIT {int:limit}, {int:amount}',
			array('limit' => intval($start), 'amount' => $max)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$games[] = array(
				'id_game' => $row['id_game'],
				'game_directory' => $row['game_directory'],
				'game_file' => $row['game_file'],
				'thumbnail' => $row['thumbnail'],
				'thumbnail_small' => $row['thumbnail_small'],
				'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
				'rom_flag' => !empty($row['rom_flag']) ? 1 : 0,
			);
		}
		$smcFunc['db_free_result']($request);

		foreach ($games as $game)
		{
			$gamesdir = empty($game['rom_flag']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
			$filepath = rtrim($gamesdir, '/');
			$dir = !empty($game['game_directory']) ? $game['game_directory'] . '/' : '';
			if (dirname($game['game_file']) != '.')
				$new = dirname($game['game_file']) . '/' . mb_strtolower(basename($game['game_file']));
			else
				$new = mb_strtolower(basename($game['game_file']));

			if (dirname($game['thumbnail']) != '.')
			{
				$newthumb = dirname($game['thumbnail']) . '/' . mb_strtolower(basename($game['thumbnail']));
				$th = !empty($game['thumbnail']) ? basename($game['thumbnail']) : '';
			}
			else
			{
				$newthumb = mb_strtolower($game['thumbnail']);
				$th = !empty($game['thumbnail']) ? $game['thumbnail'] : '';
			}

			if (dirname($game['thumbnail']) != '.')
			{
				$newthumbsmall = dirname($game['thumbnail_small']) . '/' . mb_strtolower(basename($game['thumbnail_small']));
				$thsmall = !empty($game['thumbnail_small']) ? basename($game['thumbnail_small']) : '';
			}
			else
			{
				$newthumbsmall = mb_strtolower($game['thumbnail_small']);
				$thsmall = !empty($game['thumbnail_small']) ? $game['thumbnail_small'] : '';
			}

			if (dirname($game['cover_icon']) != '.')
			{
				$newcovericon = dirname($game['cover_icon']) . '/' . mb_strtolower(basename($game['cover_icon']));
				$covericon = !empty($game['cover_icon']) ? basename($game['cover_icon']) : '';
			}
			else
			{
				$newcovericon = mb_strtolower($game['cover_icon']);
				$covericon = !empty($game['cover_icon']) ? $game['cover_icon'] : '';
			}

			if (basename($game['game_file']) != mb_strtolower(basename($game['game_file'])))
			{
				if (file_exists($filepath . '/' . $dir . $game['game_file']))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_games
						SET game_file = {string:gamefile}
						WHERE id_game = {int:gameid}',
						array(
							'gameid' => $game['id_game'],
							'gamefile' => $new,
						)
					);
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_files
						SET game_file = {string:gamefile}
						WHERE game_directory = {string:directory}',
						array(
							'gameid' => $game['id_game'],
							'gamefile' => $new,
							'directory' => $game['game_directory'],
						)
					);
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_files
						SET id_game = {int:gameid}
						WHERE game_directory = {string:directory}',
						array(
							'gameid' => $game['id_game'],
							'gamefile' => $new,
							'directory' => $game['game_directory'],
						)
					);
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_files
						SET status = 1
						WHERE game_directory = {string:directory}',
						array(
							'gameid' => $game['id_game'],
							'gamefile' => $new,
							'directory' => $game['game_directory'],
						)
					);
					rename($filepath . '/' . $dir . $game['game_file'], $filepath . '/' . $dir . $new);
				}
			}
			else
				$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_files
						SET game_file = {string:gamefile}
						WHERE game_directory = {string:directory}',
						array(
							'gameid' => $game['id_game'],
							'gamefile' => $new,
							'directory' => $game['game_directory'],
						)
					);
			if (!empty($th) && $th != mb_strtolower($th))
			{
				if (file_exists($filepath . '/' . $dir . $game['thumbnail']))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_games
						SET thumbnail = {string:thumb}
						WHERE id_game = {int:gameid}',
						array(
							'gameid' => $game['id_game'],
							'thumb' => $newthumb,
						)
					);
					rename($filepath . '/' . $dir . $game['thumbnail'], $filepath . '/' . $dir . $newthumb);
				}
			}
			if (!empty($thsmall) && $thsmall != mb_strtolower($thsmall))
			{
				if (file_exists($filepath . '/' . $dir . $game['thumbnail_small']))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_games
						SET thumbnail_small = {string:thumbsmall}
						WHERE id_game = {int:gameid}',
						array(
							'gameid' => $game['id_game'],
							'thumbsmall' => $newthumbsmall,
						)
					);
					rename($filepath . '/' . $dir . $game['thumbnail_small'], $filepath . '/' . $dir . $newthumbsmall);
				}
			}
			if (!empty($covericon) && $covericon != mb_strtolower($covericon))
			{
				if (file_exists($filepath . '/' . $dir . $game['cover_icon']))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_games
						SET cover_icon = {string:cover}
						WHERE id_game = {int:gameid}',
						array(
							'gameid' => $game['id_game'],
							'cover' => $newcovericon,
						)
					);
					rename($filepath . '/' . $dir . $game['cover_icon'], $filepath . '/' . $dir . $newcovericon);
				}
			}
		}
		$next = $start + $max;
		if ($next <= $gamecount) {
			$context['maintenance_task'] = $txt['arcade_maintenance_waitMsg'];
			$context['arcade_gameStart'] = $next;
			$context['arcade_gameFinish'] = $gamecount;
			$context['arcade_gameCurrentPercent'] = round(abs((intval($start)/intval($gamecount))*100));
			$context['arcadeFilesMax'] = 1;
		}
		else
			$context['arcadeFilesMax'] = 0;
	}

	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		$queryGamesCache = array(
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
			'arcade_most_played',
			'arcade_games_rating',
			'arcade_games_best',
			'arcade_games_mostactive'
		);
		foreach ($queryGamesCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}
}

function ArcadeMaintenanceFileNamesHtml($idgame = 0)
{
	// remove configuration file if opted
	global $smcFunc, $arcadeModSettings, $boarddir, $scripturl, $txt, $context, $smfVersion;

	isAllowedTo('arcade_admin');
	$path = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$filepath = rtrim($path, '/');
	list($games, $folders, $dirnames, $gamecount) = array(array(), array(), array(), 0);
	$gamesdir = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : '';
	$gamesdir = rtrim($gamesdir, '/');
	$boarddirx = str_replace('\\', '/', $boarddir);
	$startAt = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
	$start = abs($startAt);
	$arcadeFilesMax = explode('|', $txt['arcadeFilesMaxOptions']);
	$maxString = !empty($arcadeModSettings['arcadeFilesMax']) ? strval($arcadeFilesMax[$arcadeModSettings['arcadeFilesMax']]) : $arcadeFilesMax[0];
	$arcadeModSettings['arcadeFilesMax'] = !empty($arcadeModSettings['arcadeFilesMax']) ? (int)$arcadeModSettings['arcadeFilesMax'] : 0;
	$max = !empty($arcadeModSettings['arcadeFilesMax']) ? abs(intval($arcadeFilesMax[$arcadeModSettings['arcadeFilesMax']])) : $arcadeFilesMax[0];
	$context['maintenance_task'] = $txt['arcade_maintenance_htmlfilefix'];
	$context['arcadeFilesMax'] = 0;
	$idgame = (int)$idgame;

	$num = $smcFunc['db_query']('', '
		SELECT count(*)
		FROM {db_prefix}arcade_games
		WHERE rom_flag = 0 AND id_game ' . (!empty($idgame) ? '= ' . abs($idgame) : ' > 0'),
		array()
	);

	list ($gamecount) = $smcFunc['db_fetch_row']($num);
	$smcFunc['db_free_result']($num);
	$max = stripos($maxString, 'all') === false ? $max : $gamecount;

	if (!empty($gamesdir) && $gamecount > 0 && $start <= $gamecount)
	{
		if (!empty($idgame))
			$request = $smcFunc['db_query']('', '
				SELECT id_game, game_directory, game_file, thumbnail, thumbnail_small, cover_icon, internal_name
				FROM {db_prefix}arcade_games
				WHERE rom_flag = 0 AND id_game = {int:idgame}
				ORDER BY id_game',
				array('idgame' => $idgame)
			);
		else
			$request = $smcFunc['db_query']('', '
				SELECT id_game, game_directory, game_file, thumbnail, thumbnail_small, cover_icon, internal_name
				FROM {db_prefix}arcade_games
				WHERE rom_flag = 0 AND id_game > 0
				ORDER BY id_game
				LIMIT {int:limit}, {int:amount}',
				array('limit' => intval($start), 'amount' => $max)
			);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$games[] = array(
				'id_game' => $row['id_game'],
				'game_directory' => $row['game_directory'],
				'game_file' => $row['game_file'],
				'thumbnail' => $row['thumbnail'],
				'thumbnail_small' => $row['thumbnail_small'],
				'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
				'internal_name' => $row['internal_name']
			);
		}
		$smcFunc['db_free_result']($request);

		foreach ($games as $game)
		{
			$dir = !empty($game['game_directory']) ? $game['game_directory'] : '';
			$alterHtm = arcadeAlterHtmFileNames($dir);
			if ($alterHtm)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET game_file = {string:gamefile}
					WHERE id_game = {int:gameid}',
					array(
						'gameid' => $game['id_game'],
						'gamefile' => $alterHtm,
					)
				);
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET submit_system = {string:subsys}
					WHERE id_game = {int:gameid}',
					array(
						'gameid' => $game['id_game'],
						'subsys' => 'html52',
					)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_internal_game_conflicts
					WHERE submit_system_flag = {int:gameid}',
					array(
						'gameid' => $game['id_game'],
					)
				);
				$smcFunc['db_insert']('insert',
					'{db_prefix}arcade_internal_game_conflicts',
					array(
						'id_game' => 'int',
						'internal_id_conflict' => 'int',
						'internal_name_conflict' => 'string',
						'conflict_directory' => 'string',
						'submit_system_flag' => 'int',
					),
					array(
						0,
						0,
						'',
						'',
						$game['id_game']
					),
					array('id_conflict')
				);
				if (file_exists($filepath . '/' . $dir . '/game-info.xml'))
					@unlink($filepath . '/' . $dir . '/game-info.xml');
				if (file_exists($filepath . '/' . $dir . '/' . $game['internal_name'] . '.php'))
					@unlink($filepath . '/' . $dir . '/' . $game['internal_name'] . '.php');
				ArcadeMaintenanceConfigFiles($game['id_game']);

			}
		}
		$next = $start + $max;
		if ($next <= $gamecount) {
			$context['maintenance_task'] = $txt['arcade_maintenance_waitMsg'];
			$context['arcade_gameStart'] = $next;
			$context['arcade_gameFinish'] = $gamecount;
			$context['arcade_gameCurrentPercent'] = round(abs((intval($start)/intval($gamecount))*100));
			$context['arcadeFilesMax'] = 1;
		}
		else
			$context['arcadeFilesMax'] = 0;
	}
	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		$queryGamesCache = array(
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
			'arcade_most_played',
			'arcade_games_rating',
			'arcade_games_best',
			'arcade_games_mostactive'
		);
		foreach ($queryGamesCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}
}

function ArcadeMaintenanceConfigFiles($gameid = 0)
{
	global $boarddir, $sourcedir, $arcadeModSettings, $smcFunc, $context, $scripturl, $txt;

	loadClassFile('Class-Package.php');
	require_once($sourcedir . '/Subs-Package.php');
	require_once($sourcedir . '/RemoveTopic.php');
	require_once($boarddir . '/ArcadeSources/ArcadeDownload.php');
	$arcadeFilesMax = explode('|', $txt['arcadeFilesMaxOptions']);
	$maxString = !empty($arcadeModSettings['arcadeFilesMax']) ? strval($arcadeFilesMax[$arcadeModSettings['arcadeFilesMax']]) : $arcadeFilesMax[0];
	$arcadeModSettings['arcadeFilesMax'] = !empty($arcadeModSettings['arcadeFilesMax']) ? (int)$arcadeModSettings['arcadeFilesMax'] : 0;
	$max = !empty($arcadeModSettings['arcadeFilesMax']) ? abs(intval($arcadeFilesMax[$arcadeModSettings['arcadeFilesMax']])) : $arcadeFilesMax[0];
	$path = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$filepath = rtrim($path, '/');
	list($games, $check, $gamecount) = array(array(), false, 0);
	$gamesdir = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : '';
	$gamesdir = rtrim($gamesdir, '/');
	$boarddirx = str_replace('\\', '/', $boarddir);
	$maindir = rtrim(str_replace('\\', '/', $arcadeModSettings['gamesDirectory']), '/');
	$startAt = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
	$start = abs($startAt);
	$context['maintenance_task'] = $txt['arcade_maintenance_configfix'];
	$context['arcadeFilesMax'] = 0;
	$num = $smcFunc['db_query']('', '
      SELECT count(*)
      FROM {db_prefix}arcade_games
      WHERE id_game > 0', array()
	);

	list ($gamecount) = $smcFunc['db_fetch_row']($num);
	$smcFunc['db_free_result']($num);
	$max = stripos($maxString, 'all') === false ? $max : $gamecount;
	if ($gamecount > 0 && $start <= $gamecount)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, game_name, game_directory, game_file, id_cat, game_rating, description, internal_name, id_topic,
			submit_system, enabled, score_type, help, js_insertion, download, rom_flag, thumbnail, thumbnail_small, cover_icon, extra_data
			FROM {db_prefix}arcade_games
			WHERE enabled = 1
			ORDER BY id_game
			LIMIT {int:limit}, {int:amount}',
			array('limit' => intval($start), 'amount' => $max)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$extra_data = array();
			if (!empty($row['extra_data'])) {
				$extra_data = preg_replace_callback('!s:(\d+):"(.*?)";!',	function($m) {
					return 's:'.strlen($m[2]).':"'.$m[2].'";';
				}, $row['extra_data']);
			}
			$gamesdir = empty($row['rom_flag']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
			$gamesdir = rtrim($gamesdir, '/');
			$check = !empty($row['game_file']) && strlen($row['game_file']) > 9 && substr($row['game_file'], -10) == 'index.html' ? true : (!empty($row['game_file']) && strlen($row['game_file']) > 8 && substr($row['game_file'], -9) == 'index.php' ? true : false);
			$subSystem = !empty($row['submit_system']) ? $row['submit_system'] : '';
			$internal = !empty($row['internal_name']) ? $row['internal_name'] : '';
			$phpFilex = $check && $subSystem == 'html52' ? $internal . '.php' : (!empty($row['game_file']) ? str_replace('.swf', '.php', $row['game_file']) : 'generated_file.php');
			$phpFilex = $phpFilex != 'generated_file.php' ? $phpFilex : (!empty($internal) ? $internal . '.php' : $phpFilex);
			$phpFilex = preg_replace('"\.(htm|html)$"', '.php', $phpFilex);
			$games[] = array(
				'id_game' => $row['id_game'],
				'game_name' => $row['game_name'],
				'game_directory' => $row['game_directory'],
				'game_file' => $row['game_file'],
				'thumbnail' => $row['thumbnail'],
				'thumbnail_small' => $row['thumbnail_small'],
				'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
				'internal_name' => $row['internal_name'],
				'submit_system' => $row['submit_system'],
				'enabled' => !empty($row['enabled']) ? $row['enabled'] : 0,
				'download' => !empty($row['download']) ? 1 : 0,
				'score_type' => !empty($row['score_type']) ? $row['score_type'] : 0,
				'js_insertion' => !empty($row['js_insertion']) ? $row['js_insertion'] : 0,
				'gamename' => !empty($row['game_name']) ? $row['game_name'] : '',
				'php_file' => !empty($row['internal_name']) ? $row['internal_name'] . '.php' : '',
				'help' => !empty($row['help']) ? $row['help'] : '',
				'description' => !empty($row['description']) ? $row['description'] : '',
				'gamephp' => $phpFilex,
				'extra_data' => $extra_data,
				'id_cat' => !empty($row['id_cat']) ? $row['id_cat'] : 0,
				'gamefile_name' => !empty($internal) ? ArcadeSpecialChars(trim($internal), 'name') : '',
				'gamesave' => 'games_download',
				'rom_flag' => !empty($row['rom_flag']) ? 1 : 0,
			);
		}
		$smcFunc['db_free_result']($request);

		foreach ($games as $game)
		{
			$directory = $gamesdir . (!empty($game['game_directory']) ? '/' . $game['game_directory'] : '');
			$directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return rtrim($value, '.');}, explode('/', str_replace('\\', '/', $directory)))));
			$directory = rtrim($directory, '/');

			if ($game['submit_system'] != 'custom_game' && file_exists($gamesdir . '/' . $game['game_directory'] . '/' . $game['internal_name'] . '.php'))
			{
				@unlink($gamesdir . '/' . $game['game_directory'] . '/' . $game['internal_name'] . '.php');
			}
			if (file_exists($gamesdir . '/' . $game['game_directory'] . '/game-info.xml'))
				@unlink($gamesdir . '/' . $game['game_directory'] . '/game-info.xml');

			$tempFiles = arcade_game_down($game, $directory);
			$ext =  pathinfo($tempFiles[0], PATHINFO_EXTENSION);
			if (strtolower($ext) == 'php')
				@rename($tempFiles[0], $directory . '/' . $game['php_file']);
			else
				@rename($tempFiles[0], $directory . '/game-info.xml');
		}
		$next = $start + $max;
		if ($next <= $gamecount) {
			$context['maintenance_task'] = $txt['arcade_maintenance_waitMsg'];
			$context['arcade_gameStart'] = $next;
			$context['arcade_gameFinish'] = $gamecount;
			$context['arcade_gameCurrentPercent'] = round(abs((intval($start)/intval($gamecount))*100));
			$context['arcadeFilesMax'] = 1;
		}
		else {
			$context['arcade_gameCurrentPercent'] = 100;
			$context['arcadeFilesMax'] = 0;
		}
	}

	updateGameCache();
}

function ArcadeMaintenanceDatabase($gameids = array())
{
	global $db_prefix, $boarddir, $sourcedir, $arcadeModSettings, $smcFunc, $context, $txt;

	loadClassFile('Class-Package.php');
	require_once($sourcedir . '/Subs-Package.php');
	require_once($sourcedir . '/RemoveTopic.php');
	require_once($boarddir . '/ArcadeSources/ArcadeDownload.php');
	list($games, $topics, $matches, $delgames, $folders, $dirnames, $duplicates, $game_names) = array(array(), array(), array(), array(), array(), array(), array(), array());
	$boarddirx = str_replace('\\', '/', $boarddir);
	$maintain = isset($_REQUEST['area']) && $_REQUEST['area'] == 'arcademaintenance' ? true : false;
	$maindir = rtrim(str_replace('\\', '/', $arcadeModSettings['gamesDirectory']), '/');

	if (!empty($maintain))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, game_name, game_directory, game_file, internal_name, thumbnail, thumbnail_small, cover_icon, submit_system, rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game > 0
			ORDER BY id_game',
			array()
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$games[] = array(
				'id_game' => $row['id_game'],
				'game_name' => $row['game_name'],
				'game_directory' => $row['game_directory'],
				'game_file' => $row['game_file'],
				'thumbnail' => $row['thumbnail'],
				'thumbnail_small' => $row['thumbnail_small'],
				'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
				'internal_name' => $row['internal_name'],
				'submit_system' => $row['submit_system'],
				'rom_flag' => !empty($row['rom_flag']) ? 1 : 0,
			);
		}
		$smcFunc['db_free_result']($request);

		foreach ($games as $game)
		{
			$gamesdir = empty($game['rom_flag']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
			$gamesdir = rtrim($gamesdir, '/');
			if (empty($game['game_directory']) && !empty($game['game_file']) && !file_exists($gamesdir . '/' . $game['game_file']))
			{
				$delgames[] = $game['id_game'];
			}
			elseif (!empty($game['game_directory']) && !is_dir($gamesdir . '/' . $game['game_directory']))
			{
				$delgames[] = $game['id_game'];
			}
			elseif (in_array($game['game_directory'], $duplicates))
			{
				// assume the first occurrence was the proper entry
				$key = array_search($game['game_directory'], $duplicates);
				if (!empty($game_names[$key]) && $game['game_name'] == $game_names[$key])
					$delgames[] = $game['id_game'];
			}

			$duplicates[] = $game['game_directory'];
			$game_names[] = $game['game_name'];
		}

		if (empty($delgames) && !empty($gameids))
		{
			$gameids = !is_array($gameids) ? array($gameids) : $gameids;
			$delgames = $gameids;
		}

		foreach ($delgames as $delgame)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_topic
				FROM {db_prefix}arcade_games
				WHERE id_game = {int:idgame}',
				array(
					'idgame' => $delgame
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request))
				$topics[] = $row['id_topic'];

			$smcFunc['db_free_result']($request);

			$request = $smcFunc['db_query']('', '
				SELECT id_match
				FROM {db_prefix}arcade_matches_rounds
				WHERE id_game = {int:idgame}',
				array(
					'idgame' => $delgame
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$matches[] = $row['id_topic'];
			}
			$smcFunc['db_free_result']($request);

			foreach ($matches as $match)
			{
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_matches_rounds
					WHERE id_match = {int:matchid}',
					array(
						'matchid' => $match,
					)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_matches
					WHERE id_match = {int:matchid}',
					array(
						'matchid' => $match,
					)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_matches_players
					WHERE id_match = {int:matchid}',
					array(
						'matchid' => $match,
					)
				);
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_matches_results
					WHERE id_match = {int:matchid}',
					array(
						'matchid' => $match,
					)
				);
			}

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_games
				WHERE id_game = {int:gameid}',
				array(
					'gameid' => $delgame,
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_scores
				WHERE id_game = {int:gameid}',
				array(
					'gameid' => $delgame,
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_files
				WHERE id_game = {int:gameid}',
				array(
					'gameid' => $delgame,
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_guest_data
				WHERE current_game = {int:gameid}',
				array(
					'gameid' => $delgame,
				)
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_member_data
				WHERE current_game = {int:gameid}',
				array(
					'gameid' => $delgame,
				)
			);
		}

		if (!empty($topics))
			removeTopics($topics, false, false);
	}
	updateGameCache();
	$context['maintenance_task'] = $txt['arcade_maintenance_dbfix'];
}

function ArcadeUninstallOptions()
{
	global $arcadeModSettings, $boarddir, $sourcedir, $smcFunc;

	$sessCheck = isset($_POST['fileid']) ? $_POST['fileid'] : '';
	if (!allowedTo('admin_forum') || $sessCheck != $_SESSION['arcade_admin_check'])
		exit("Access denied!");
	else
		unset($_SESSION['arcade_admin_check']);

	checkSession('post');
	$dbFlag = isset($_POST['arcade_db']) && $_POST['arcade_db'] == 1 ? 1 : 0;
	$filesFlag = isset($_POST['arcade_files']) && $_POST['arcade_files'] == 1 ? 1 : 0;

	if (empty($dbFlag) && empty($filesFlag))
		exit("Access denied!");

	if (!empty($filesFlag)) {
		$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
		$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
		$dirs = array(
			$gamesDirectory,
			$romGamesDirectory,
			$boarddir . '/arcade',
			$boarddir . '/games_rom_upload',
			$boarddir . '/games_upload',
			$boarddir . '/ArcadeBackups',
		);

		foreach ($dirs as $dir) {
			arcadeRmDir($dir);
			clearstatcache();
			if (is_dir($dir))
				@rmdir($dir);
		}
	}

	if (!empty($dbFlag)) {
		db_extend('packages');
		require_once($sourcedir . '/Subs.php');
		remove_integration_function('integrate_pre_include', '$sourcedir/ArcadeHooks.php');
		remove_integration_function('integrate_pre_load', 'Arcade_load_language');
		remove_integration_function('integrate_actions', 'Arcade_actions');
		remove_integration_function('integrate_core_features', 'Arcade_core_features');
		remove_integration_function('integrate_load_permissions', 'Arcade_load_permissions');
		remove_integration_function('integrate_menu_buttons', 'Arcade_menu_buttons');
		remove_integration_function('integrate_admin_areas', 'Arcade_admin_areas');
		remove_integration_function('integrate_load_theme', 'Arcade_load_theme');
		remove_integration_function('integrate_whos_online', 'Arcade_whos_online');
		remove_integration_function('integrate_load_custom_profile_fields', 'arcade_custom_profile');
		remove_integration_function('integrate_pre_profile_areas', 'Arcade_profile_areas');
		remove_integration_function('integrate_admin_search', 'Arcade_admin_search');
		remove_integration_function('integrate_pre_log_stats', 'Arcade_game_support');
		remove_integration_function('integrate_viewModLog', 'Arcade_viewModLog');
		remove_integration_function('integrate_validateSession', 'Arcade_validate_session');

		$allModSettings = array_merge(
			array('gamesPerPage', 'matchesPerPage', 'scoresPerPage', 'gamesDirectory', 'romGamesDirectory', 'arcadeDBUpdate', 'gamesUrl', 'romGamesUrl', 'gamesNotificationsBulk', 'arcadeEnabled', 'arcadeArenaEnabled', 'arcadeCheckLevel', 'arcadeGamecacheUpdate', 'arcadeMaxScores', 'arcadePermissionMode', 'arcadePostPermission', 'arcadePostsPlay', 'arcadePostsPlayPerDay', 'arcadePostsPlayAverage', 'arcadeEnableFavorites', 'arcadeEnableRatings', 'arcadeShowInfoCenter', 'arcadeCommentLen', 'arcadeUploadSystem', 'arcadeVersion', 'arcadeShowOnline', 'arcadeShowIC', 'arcadeList', 'arcadeSkin', 'arcadeSkinAlt'),
			array('skin_avatar_sizeb_height', 'skin_avatar_sizeb_width', 'skin_avatar_size_height', 'skin_avatar_size_heightA', 'skin_avatar_size_width', 'skin_avatar_size_widthA', 'skin_best_playersB', 'skin_latest_champs', 'skin_latest_champsA', 'skin_latest_champsB', 'skin_latest_games', 'skin_latest_gamesA', 'skin_latest_gamesB', 'skin_latest_rom_games', 'skin_latest_scores', 'skin_latest_scoresA', 'skin_longest_champsB', 'skin_most_popular', 'skin_most_popularA', 'skin_most_popularB', 'skin_most_rom_popular', 'arcadeEmulatorJS_WebDevLink', 'arcadeEmulatorJS_GithubLink', 'arcadeRomGameTypes'),
			array('arcadeRetroArchEnabled', 'arcadeGameTesting', 'arcade_contentSecurityPolicy', 'arcadePosterid', 'arcadeFilesMax', 'arcadeNewRandomId', 'arcade_jQuery_JV', 'arcadeTranslationAPI', 'arcade_adjust_desc_admin', 'arcade_adjust_desc_info', 'arcadeDeepLSubKey', 'arcadeDeepL', 'arcadeDeepLEndpoint', 'arcadeDeepLPath', 'arcadeDeepLPT', 'arcadeDeepLEN', 'arcadeDeepLAutoFill', 'arcadeAzure', 'arcadeAzureEndpoint', 'arcadeAzurePath', 'arcadeAzureRegionCode', 'arcadeAzureSubKey', 'arcadeAzureSubKey2', 'arcadeAzureAutoFill', 'arcadeRuffleExternalLink', 'arcadeRuffleDefaultLink', 'arcadeTypeQuery'),
			array('arcade_decimal', 'arcadeAdjustType', 'arcadeDisableComments', 'arcade_shout_guest_score', 'arcade_shout_member_score', 'arcade_shout_arena_score', 'arcade_phpbb3_support_score', 'arcade_log_savetype', 'arcade_log_scoreloop', 'arcade_log_install_game', 'arcade_log_translate', 'arcadeListMobile', 'arcadeSkinMobile', 'arcadeListHorizontalDivision', 'arcadeIconBorderRadius2', 'arcadeCatsCellWidth', 'arcadeCatsPerLine', 'arcade_catWidth', 'arcade_catHeight', 'arcade_catHideUnused', 'arcade_showListCat', 'arcadeIconBorderRadius1', 'arcadeRuffleVersion', 'arcadeEmulatorJSVersion', 'arcadeMobileList'),
			array('arcadeListGenericExtraBg', 'arcadeListGenericExtraBorder', 'arcadeIconBorderRadius0', 'arcadeDailyGameScoresC', 'arcadeDropCatClassic', 'arcadeGamesNameLength', 'arcadeDownloadWinRarDir', 'arcadeDownloadUnixRarDir', 'arcadeEnableDownload', 'arcade_newgame_notification', 'arcadeEnablePosting', 'arcadeEnableIframe', 'arcadeEnablePostCount', 'gamesBoard', 'gamesMessage', 'arcade_install_duplicate_game', 'arcade_install_clean_db', 'arcadeJsInsertDefault', 'arcadeDownloadHideLink', 'arcadeDisplayType', 'arcadeDisableArchive', 'arcade_gz_user', 'arcadeListSort', 'arcadeDescriptLength', 'arcadeRandomIdVar'),
			array('arcadeDownloadShellEnable', 'arcade_gz', 'arcadeDownPass', 'arcadeDownPost', 'pdl_DownMax', 'arcadeEnableReport', 'arcadeEnableGameDisable', 'arcadeEnableReportNotification', 'arcadeTabs', 'arcade_shoutboxC', 'arcade_shoutboxC_name', 'arcade_shout_heightC', 'arcade_shout_interval', 'arcade_shout_intervalC', 'arcade_shout_widthB', 'arcade_show_shoutsC', 'arcade_flash_emulator', 'arcade_rom_emulator', 'arcadeRomToggle', 'arcadeDropCat', 'arcadeDropCatA', 'arcadeGamesNameLengthB', 'arcadeRomIconRemoteDict', 'arcadeRomIconRemoteIcons', 'arcadeRomIconRemoteQty', 'arcadeMobileSkin'),
		);

		foreach ($allModSettings as $set) {
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}settings
				WHERE variable = {string:var}',
				array('var' => $set)
			);
		}

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}settings
			WHERE variable = {string:varx} AND value = {string:valx}',
			array('varx' => 'admin_features', 'valx' => 'arcade')
		);

		$permissionArray = array('arcade_view', 'arcade_play', 'arcade_submit', 'arcade_admin', 'arcade_comment_own', 'arcade_comment_any', 'arcade_user_stats_own', 'arcade_user_stats_any', 'arcade_view_arena', 'arcade_create_match', 'arcade_join_match', 'arcade_join_invite_match', 'arcade_edit_settings_own', 'arcade_edit_settings_any', 'arcade_report', 'arcade_download', 'arcade_download_type', 'arcade_online');
		foreach ($permissionArray as $perm) {
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}permissions
				WHERE permission = {string:var}',
				array('var' => $perm)
			);
		}

		$arcadeTables = array('arcade_internal_game_conflicts', 'arcade_modsettings', 'arcade_games', 'arcade_scores', 'arcade_categories', 'arcade_favorite', 'arcade_matches', 'arcade_matches_players', 'arcade_matches_rounds', 'arcade_matches_results', 'arcade_rates', 'arcade_game_info', 'arcade_internal_gamedata', 'arcade_files', 'arcade_member_data', 'arcade_guest_data', 'arcade_newshouts', 'arcade_members', 'arcade_membergroups', 'arcade_guest_extra_data', 'arcade_skins', 'arcade_lists', 'arcade_mobile_skins', 'arcade_mobile_lists', 'arcade_pdl1', 'arcade_pdl2');
		foreach ($arcadeTables as $table) {
			if (check_table_existsUninstall($table))
				$smcFunc['db_drop_table']('{db_prefix}' . $table);
		}
	}
}

function check_table_existsUninstall($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}

?>