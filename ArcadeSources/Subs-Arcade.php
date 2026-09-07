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

function loadGame($id_game, $from_admin = false, $rom = 0)
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $arcadeModSettings, $context;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$whererom = !empty($rom) ? ' AND game.rom_flag = {int:rom_flag}' : '';
	if (is_numeric($id_game) && isset($context['arcade']['game_data'][$id_game]))
		return $id_game;
	elseif (isset($context['arcade']['game_ids'][$id_game]))
		return $context['arcade']['game_ids'][$id_game];

	if ($from_admin)
		$where = "game.id_game = {int:game}" . $whererom;
	elseif (is_numeric($id_game))
		$where = "{raw:query_see_game}
			AND game.id_game = {int:game}" . $whererom;
	elseif ($id_game === 'random')
		$where = "{raw:query_see_game}" . $whererom . "
		ORDER BY RAND()";
	else
		$where = "{raw:query_see_game}
			AND game.internal_name = {string:game}" . $whererom;

	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.description, game.game_rating, game.num_plays, category.cat_dl, category.cat_js,
			game.game_file, game.game_directory, game.submit_system, game.internal_name, game.js_insertion, game.rom_flag,
			game.score_type, game.thumbnail, game.thumbnail_small, cover_icon, game.help, game.enabled, game.download, game.rom_system,
			game.member_groups, game.extra_data, game.id_cat, category.cat_icon, pdl2.report_id, info.icon_position, info.icon_position_hide,
			IFNULL(score.id_score,0) AS id_score, IFNULL(score.score, 0) AS champ_score, IFNULL(pdl2.report_id,0) AS report_id,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, score.player_name) AS real_name, IFNULL(info.icon_position,0) AS icon_position,
			IFNULL(score.end_time, 0) AS champion_time, IFNULL(favorite.id_favorite, 0) AS is_favorite, IFNULL(info.icon_position_hide,0) AS icon_position_hide,
			IFNULL(category.id_cat, 0) AS id_cat, IFNULL(category.cat_name, {string:string_empty}) As cat_name,
			IFNULL(pb.id_score, 0) AS id_pb, IFNULL(pb.score, 0) AS personal_best, num_favorites
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = game.id_champion_score)
			LEFT JOIN {db_prefix}arcade_pdl2 AS pdl2 ON (pdl2.pdl_gameid = game.id_game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game AND favorite.id_member = {int:member})
			LEFT JOIN {db_prefix}arcade_scores AS pb ON (pb.id_game = game.id_game AND pb.id_member = {int:member} AND pb.personal_best = 1)
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
			LEFT JOIN {db_prefix}arcade_game_info AS info ON (info.id_game = game.id_game)
		WHERE ' . $where . '
		LIMIT 1',
		array(
			'game' => (int)$id_game,
			'string_empty' => '',
			'member' => $user_info['id'],
			'query_see_game' => $user_info['query_see_game'],
			'rom_flag' => 1,
		)
	);

	$game = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// No game was found
	if (empty($game))
		return false;

	// category overrides
	$game['download'] = !empty($game['cat_dl']) ? 0 : $game['download'];
	$game['js_insertion'] = !empty($game['cat_js']) ? 1 : $game['js_insertion'];
	$game['allowed_download'] = allowedTo('arcade_download') && empty($game['rom_flag']) ? 1 : (allowedTo('arcade_download_rom') && !empty($game['rom_flag']) ? 1 : 0);
	$context['arcade']['game_data'][$game['id_game']] = $game;
	$context['arcade']['game_ids'][$game['internal_name']] = $game['id_game'];

	return $game['id_game'];
}

function loadRomGame($id_game, $from_admin = false)
{
	$loadRomGame = loadGame($id_game, $from_admin, 1);
	return $loadRomGame;
}

// Updates Game
function updateGame($id_game, $gameOptions, $log = false, $rom = 0)
{
	global $scripturl, $boarddir, $sourcedir, $db_prefix, $user_info, $smcFunc;

	if (empty($id_game))
		fatal_error('arcade_game_update_error', false);

	$gameUpdates = array();
	$updateValues = array();

	if (isset($gameOptions['internal_name']))
	{
		$gameUpdates[] = "internal_name = {string:internal_name}";
		$updateValues['internal_name'] = $gameOptions['internal_name'];
	}

	if (isset($gameOptions['name']))
	{
		$gameUpdates[] = "game_name = {string:game_name}";
		$updateValues['game_name'] = $gameOptions['name'];
	}

	if (isset($gameOptions['description']))
	{
		$gameUpdates[] = "description = {string:description}";
		$updateValues['description'] = $gameOptions['description'];
	}

	if (isset($gameOptions['help']))
	{
		$gameUpdates[] = "help = {string:help}";
		$updateValues['help'] = $gameOptions['help'];
	}

	if (isset($gameOptions['thumbnail']))
	{
		$gameUpdates[] = "thumbnail = {string:thumbnail}";
		$updateValues['thumbnail'] = $gameOptions['thumbnail'];
	}

	if (isset($gameOptions['thumbnail_small']))
	{
		$gameUpdates[] = "thumbnail_small = {string:thumbnail_small}";
		$updateValues['thumbnail_small'] = $gameOptions['thumbnail_small'];
	}

	if (isset($gameOptions['cover_icon']))
	{
		$gameUpdates[] = "cover_icon = {string:cover_icon}";
		$updateValues['cover_icon'] = $gameOptions['cover_icon'];
	}

	if (isset($gameOptions['game_file']))
	{
		$gameUpdates[] = "game_file = {string:game_file}";
		$updateValues['game_file'] = $gameOptions['game_file'];
	}

	if (isset($gameOptions['game_directory']))
	{
		$gameUpdates[] = "game_directory = {string:game_directory}";
		$updateValues['game_directory'] = $gameOptions['game_directory'];
	}

	if (isset($gameOptions['submit_system']) && empty($rom))
	{
		$gameUpdates[] = "submit_system = {string:submit_system}";
		$updateValues['submit_system'] = $gameOptions['submit_system'];
	}
	elseif (isset($gameOptions['submit_system'])) {
		$gameUpdates[] = "submit_system = {string:submit_system}";
		$updateValues['submit_system'] = isset($gameOptions['submit_system']) && !empty($gameOptions['switch_system']) ? $gameOptions['submit_system'] : 'rom';
	}

	if (isset($gameOptions['rom_system']))
	{
		$gameUpdates[] = "rom_system = {string:rom_system}";
		$updateValues['rom_system'] = $gameOptions['rom_system'];
	}

	if (isset($gameOptions['rom_flag']))
	{
		$gameUpdates[] = "rom_flag = {int:rom_flag}";
		$updateValues['rom_flag'] = intval($gameOptions['rom_flag']);
	}

	if (isset($gameOptions['member_groups']))
	{
		$gameUpdates[] = "member_groups = {string:member_groups}";
		$updateValues['member_groups'] = implode(',', $gameOptions['member_groups']);
	}

	if (isset($gameOptions['extra_data']))
	{
		$gameUpdates[] = "extra_data = {string:extra_data}";
		$updateValues['extra_data'] = serialize($gameOptions['extra_data']);
	}

	if (isset($gameOptions['score_type']))
	{
		$gameUpdates[] = "score_type = {int:score_type}";
		$updateValues['score_type'] = $gameOptions['score_type'];

		require_once($sourcedir . '/ArcadeMaintenance.php');
		if (empty($rom))
			ArcadeFixScores($id_game, $gameOptions['score_type']);
	}

	if (isset($gameOptions['num_plays']))
	{
		if ($gameOptions['num_plays'] == '+')
		{
			$gameUpdates[] = "num_plays = num_plays + 1";
		}
		else
		{
			$gameUpdates[] = "num_plays = {int:num_plays}";
			$updateValues['num_plays'] = $gameOptions['num_plays'];
		}
	}

	if (isset($gameOptions['num_rates']))
	{
		if ($gameOptions['num_rates'] == '+')
			$gameUpdates[] = "num_rates = num_rates + 1";
		elseif ($gameOptions['num_rates'] == '-')
			$gameUpdates[] = "num_rates = num_rates - 1";
		else
		{
			$gameUpdates[] = "num_rates = {int:num_rates}";
			$updateValues['num_rates'] = $gameOptions['num_rates'];
		}
	}

	if (isset($gameOptions['num_favorites']))
	{
		if ($gameOptions['num_favorites'] == '+')
			$gameUpdates[] = "num_favorites = num_favorites + 1";
		elseif ($gameOptions['num_favorites'] == '-')
			$gameUpdates[] = "num_favorites = num_favorites - 1";
		else
		{
			$gameUpdates[] = "num_favorites = {int:num_favorites}";
			$updateValues['num_favorites'] = $gameOptions['num_favorites'];
		}
	}

	if (isset($gameOptions['rating']))
	{
		$gameUpdates[] = "game_rating = {float:rating}";
		$updateValues['rating'] = $gameOptions['rating'];
	}

	if (isset($gameOptions['category']))
	{
		$gameUpdates[] = "id_cat = {int:category}";
		$updateValues['category'] = $gameOptions['category'];
		$updateCat = true;
	}

	if (isset($gameOptions['champion']))
	{
		$gameUpdates[] = "id_champion = {int:champion}";
		$updateValues['champion'] = $gameOptions['champion'];
	}

	if (isset($gameOptions['champion_score']))
	{
		$gameUpdates[] = "id_champion_score = {int:champion_score}";
		$updateValues['champion_score'] = $gameOptions['champion_score'];
	}

	if (isset($gameOptions['enabled']))
	{
		$gameUpdates[] = "enabled = {int:enabled}";
		$updateValues['enabled'] = $gameOptions['enabled'] ? 1 : 0;
		$updateCat = true;
	}

	if (isset($gameOptions['download']))
	{
		$gameUpdates[] = "download = {int:download}";
		$updateValues['download'] = $gameOptions['download'] ? 1 : 0;
	}

	if (isset($gameOptions['js_insertion']))
	{
		$gameOptions['js_insertion'] = !empty($gameOptions['js_insertion']) && $gameOptions['js_insertion'] > 0 && $gameOptions['js_insertion'] < 3 ? (int)$gameOptions['js_insertion'] : 0;
		$gameUpdates[] = "js_insertion = {int:js_insertion}";
		$updateValues['js_insertion'] = $gameOptions['js_insertion'];
	}

	if (isset($gameOptions['local_permissions']))
	{
		$gameUpdates[] = "local_permissions = {int:local_permissions}";
		$updateValues['local_permissions'] = $gameOptions['local_permissions'];
	}

	if (isset($gameOptions['icon_position']) && empty($rom))
	{
		$gameOptions['icon_position'] = (int)$gameOptions['icon_position'];
		$pos = abs(intval($gameOptions['icon_position']));
		$pos = $pos >= 0 && $pos < 4 ? $pos : 0;
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_game_info
			WHERE id_game = {int:idgame}',
			array(
				'idgame' => $id_game,
			)
		);

		list ($countz) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		if (!empty($countz))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_game_info
				SET icon_position = {int:positionx}
				WHERE id_game = {int:game}',
				 array(
					'game' => $id_game,
					'positionx' => $pos,
				)
			);
		}
		else
		{
			$smcFunc['db_insert']('insert',
				'{db_prefix}arcade_game_info',
				array(
					'id_game' => 'int',
					'icon_position' => 'int',
				),
				array(
					$id_game,
					$pos,
				),
				array('id_game')
			);
		}
	}

	if (isset($gameOptions['icon_position_hide']) && empty($rom))
	{
		$gameOptions['icon_position_hide'] = (int)$gameOptions['icon_position_hide'];
		$pos = abs(intval($gameOptions['icon_position_hide']));
		$pos = $pos >= 0 && $pos < 4 ? $pos : 0;
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_game_info
			WHERE id_game = {int:idgame}',
			array(
				'idgame' => $id_game,
			)
		);

		list ($countz) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		if (!empty($countz))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_game_info
				SET icon_position_hide = {int:positionx}
				WHERE id_game = {int:game}',
				 array(
					'game' => $id_game,
					'positionx' => $pos,
				)
			);
		}
		else
		{
			$smcFunc['db_insert']('insert',
				'{db_prefix}arcade_game_info',
				array(
					'id_game' => 'int',
					'icon_position_hide' => 'int',
				),
				array(
					$id_game,
					$pos,
				),
				array('id_game')
			);
		}
	}

	if (empty($gameUpdates))
		return;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET ' . implode(', ', $gameUpdates) . '
		WHERE id_game = {int:game}',
		array_merge($updateValues, array(
			'game' => $id_game,
		))
	);

	if ($log && empty($rom)) {
		$trace = !empty($gameOptions['name']) ? $gameOptions['name'] : $id_game;
		logAction('arcade_update_game', array('game' => $trace));
	}
	elseif ($log) {
		$trace = !empty($gameOptions['name']) ? 'rom: ' . $gameOptions['name'] : 'rom_' . $id_game;
		logAction('arcade_update_game_rom', array('game' => $trace));
	}

	if (!empty($updateCat))
		updateCategoryStats($rom);

	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		$gameRelatedCache = array(
			'arcade_games_list',
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
		);
		foreach ($gameRelatedCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}

	return true;
}

// Event system
function arcadeGetEventTypes($id = '')
{
	global $arcadeModSettings;

	$events = array(
		'divider0' => array(
			'id' => 'divider0',
			'func' => function() {return false;},
			'notification' => array(
				'divider0' => true,
			)
		),
		'champion_email' => array(
			'id' => 'champion_email',
			'func' => 'arcadeEventChampionEmail',
			'notification' => array(
				'champion_email' => false,
			)
		),
		'champion_pm' => array(
			'id' => 'champion_pm',
			'func' => 'arcadeEventChampionEmail',
			'notification' => array(
				'champion_pm' => false,
			)
		),
		'divider1' => array(
			'id' => 'divider1',
			'func' => function() {return false;},
			'notification' => array(
				'divider1' => true,
			)
		),
	);

	// notifications that may be disabled by the administrator
	if (!empty($arcadeModSettings['arcadeEnableReportNotification']) && allowedTo('arcade_admin')) {
		$events += array(
			'notify_game_reports' => array(
				'id' => 'notify_game_reports',
				'func' => 'arcadeEventGameReports',
				'notification' => array(
					'notify_game_reports' => false,
				)
			),
		);
	}

	$events += array(
		'new_game' => array(
			'id' => 'new_game',
			'func' => 'arcadeNewGameEvent',
			'notification' => array(
				'new_game' => allowedTo('arcade_new_game') && !empty($arcadeModSettings['arcade_newgame_notification']) ? true : false,
			)
		),
		'new_champion' => array(
			'id' => 'new_champion',
			'func' => 'arcadeEventNewChampion',
			'notification' => array(
				'new_champion_own' => true,
				'new_champion_any' => false
			)
		),
		'arena_invite' => array(
			'id' => 'arena_invite',
			'func' => 'arcadeEventArenaGeneral',
			'notification' => array(
				'arena_invite' => true,
			)
		),
		'arena_new_round' => array(
			'id' => 'arena_new_round',
			'func' => 'arcadeEventArenaGeneral',
			'notification' => array(
				'arena_new_round' => true,
			)
		),
		'arena_match_end' => array(
			'id' => 'arena_match_end',
			'func' => 'arcadeEventArenaGeneral',
			'notification' => array(
				'arena_match_end' => true,
			)
		),
	);

	if (empty($id))
		return $events;

	if (isset($events[$id]))
		return $events[$id];

	fatal_error('Hacking attempt...');
}

function arcadeEvent($id_event, $data = array(), $rom = 0)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $user_info, $boarddir, $sourcedir, $arcadeModSettings, $language, $webmaster_email, $mbname, $memberContext;

	$arcadeModSettings['gamesEmail'] = !empty($arcadeModSettings['gamesEmail']) ? $arcadeModSettings['gamesEmail'] : $webmaster_email;
	$notifications = array('new_champion', 'arena_invite', 'match_end', 'new_round');
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-Members.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeNotifications.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	if (filter_var($arcadeModSettings['gamesEmail'], FILTER_VALIDATE_EMAIL) === false)
		$arcadeModSettings['gamesEmail'] = $txt['arcade_default_email'];
	list($arcadeSettings, $emails, $pms, $pvts, $from, $sendEmailData, $sendPmData) = array(array(), array(), array(), array(), array(), false, false);
	$membersAllowedTo = array('arcade_admin' => membersAllowedTo('arcade_admin'), 'arcade_skin' => membersAllowedTo('arcade_skin'), 'arcade_list' => membersAllowedTo('arcade_list'));
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? mb_substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	loadLanguage('Arcade');
	loadLanguage('ArcadeAdmin');

	// change notifications language to the forum default for bulk notifications
	$lang = $language;
	loadLanguage('ArcadeEmail', $lang, false, false);

	if ($id_event == 'get' && empty($data))
		return arcadeGetEventTypes();
	else
		$event = arcadeGetEventTypes($id_event);

	$replacements = array(
		'ARCADE_SETTINGS_URL' => $scripturl . '?action=profile;area=arcadeSettings'
	);

	$event['func']($event, $replacements, $pms, $data);

	// ensure the default for the switch
	if (!in_array($id_event, $notifications))
		return false;

	// set some variables for the arena_invite since they may not be populated by default
	$data['game']['url']['highscore'] = !empty($data['game']['url']['highscore']) ? $data['game']['url']['highscore'] : '';
	$data['game']['name'] = !empty($data['game']['name']) ? $data['game']['name'] : '';
	$data['game']['username'] = !empty($data['game']['username']) ? $data['game']['username'] : '';
	$data['game']['champion']['id'] = !empty($data['game']['champion']['id']) ? $data['game']['champion']['id'] : 0;

	// email variables
	$old_champ = !empty($data['game']['champion']['name']) ? arcade_html_entity_decode($data['game']['champion']['name'], 1, 2) : '';
	$new_champ = arcade_html_entity_decode($user_info['name'], 1, 2);
	$game = '<a href="' . str_replace('action=' . $action, 'action=ingressarcade', $data['game']['url']['highscore']) . '">' . $data['game']['name'] . '</a>';
	$game_pm = '[iurl=' . $data['game']['url']['highscore'] . ']' . $data['game']['name'] . '[/iurl]';
	$newChamp = empty($data['game']['champion']['id']) ? 'New' : '';

	// set the sender as the user ID for posting from arcade settings else use the current user ID
	if ((!empty($arcadeModSettings['arcadePosterid'])) && (int)$arcadeModSettings['arcadePosterid'] !== $user_info['id'])
	{
		$id = (int)$arcadeModSettings['arcadePosterid'];
		loadMemberData($id, false, 'normal');
		loadMemberContext($id);
		$from = array('id' => $id, 'name' => $memberContext[$id]['name'], 'username' => $memberContext[$id]['username']);
	}
	elseif (empty($user_info['is_guest']))
	{
		$id = (int)$user_info['id'];
		$from = array('id' => $id, 'name' => $user_info['name'], 'username' => $user_info['username']);
	}
	else
	{
		$id = $data['game']['champion']['id'];
		$from = array('id' => $id, 'name' => $data['game']['name'], 'username' => $data['game']['username']);
	}

	$from =	empty($from) ? array('id' => $user_info['id'], 'name' => $user_info['name'], 'username' => $user_info['username']) : $from;

	if (empty($user_info['id']))
		$new_champ = !empty($new_champ) ? $new_champ : (!empty($data['member']['name']) ? sprintf($txt['arcade_guest_score_name'], $data['member']['name']) : $txt['arcade_guest_score_noname']);
	else
		$new_champ = !empty($new_champ) ? $new_champ : (!empty($data['member']['name']) ? $data['member']['name'] : $txt['arcade_guest_score_noname']);

	$request = $smcFunc['db_query']('', '
		SELECT id_member, arena_invite, arena_match_end, arena_new_round, champion_email, champion_pm, notify_game_reports, new_game, detect_mobile, games_per_page, arcade_emulatorjs_rom,
		archive_type, arcade_gametype, arcade_gametype_rom, new_champion_any, new_champion_own, scores_per_page, skin, list, skin_rom, list_rom, skin_mobile, list_mobile, download_count
		FROM {db_prefix}arcade_members
		ORDER BY id_member ASC',
		array(
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($row['id_member']))
			continue;
		$memID = $row['id_member'];
		$prefs = getNotifyPrefs($row['id_member'], '', $row['id_member'] != 0);
		$pm_email_notify = !empty($prefs[$row['id_member']]['pm_new']) && $prefs[$row['id_member']]['pm_new'] == 2 ? 1 : 0;
		$extraRomData = !empty($extraRomData) && arcadeIsSerialized($extraRomData) ? arcade_safe_unserialize($extraRomData) : array('webgl2Enabled' => 0);
		$arcadeSettings[$memID] = array(
			'id_member' =>$memID,
			'arena_invite' => $row['arena_invite'],
			'arena_match_end' => $row['arena_match_end'],
			'arena_new_round' => $row['arena_new_round'],
			'champion_email' => $row['champion_email'],
			'champion_pm' => $row['champion_pm'],
			'notify_game_reports' => !empty($row['notify_game_reports']) && in_array($memID, $membersAllowedTo['arcade_admin']) ? $row['notify_game_reports'] : 0,
			'new_game' => $row['new_game'],
			'games_per_page' => $row['games_per_page'],
			'archive_type' => $row['archive_type'],
			'arcade_gametype' => $row['arcade_gametype'],
			'arcade_gametype_rom' => $row['arcade_gametype_rom'],
			'new_champion_any' => $row['new_champion_any'],
			'new_champion_own' => $row['new_champion_own'],
			'scores_per_page' => $row['scores_per_page'],
			'skin' => !empty($row['skin']) && in_array($memID, $membersAllowedTo['arcade_skin']) ? $row['skin'] : $arcadeModSettings['arcadeSkin'],
			'list' => !empty($row['list']) && in_array($memID, $membersAllowedTo['arcade_list']) ? $row['list'] : $arcadeModSettings['arcadeList'],
			'skin_rom' => !empty($row['skin_rom']) && in_array($memID, $membersAllowedTo['arcade_skin']) ? $row['skin_rom'] : $arcadeModSettings['arcadeSkin'],
			'list_rom' => !empty($row['list_rom']) && in_array($memID, $membersAllowedTo['arcade_list']) ? $row['list_rom'] : $arcadeModSettings['arcadeList'],
			'skin_mobile' => (!empty($row['skin_mobile']) && in_array($memID, $membersAllowedTo['arcade_skin']) ? (int)$row['skin_mobile'] : $arcadeModSettings['arcadeSkinMobile']),
			'list_mobile' => (!empty($row['list_mobile']) && in_array($memID, $membersAllowedTo['arcade_list']) ? (int)$row['list_mobile'] : $arcadeModSettings['arcadeSkinMobile']),
			'detect_mobile' => $row['detect_mobile'],
			'pm_email_notify' => !empty($pm_email_notify) ? $pm_email_notify : 0,
			'download_count' => !empty($row['download_count']) ? $row['download_count'] : 0,
			'arcade_emulatorjs_rom' => $extraRomData,
		);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($arcadeSettings))
	{
		$request_member = array();
		$request_member = $smcFunc['db_query']('', '
			SELECT mem.id_member, mem.email_address, mem.lngfile
			FROM {db_prefix}members AS mem
			WHERE mem.id_member IN({array_int:members})
			ORDER BY mem.lngfile',
			array(
				'members' => array_keys($arcadeSettings),
			)
		);

		while ($rowmember = $smcFunc['db_fetch_assoc']($request_member))
		{
			list($sendEmailData, $sendPmData) = array(false, false);
			if ($smfVersion == 'v2.1')
			{
				$memberId = $rowmember['id_member'];
				$prefs = getNotifyPrefs($memberId, '', $memberId != 0);
				$rowmember['pm_email_notify'] = !empty($prefs[$memberId]['pm_new']) && $prefs[$memberId]['pm_new'] == 2 ? 1 : 0;
			}

			// Opt out of a notification depending on certain conditions
			if ($rowmember['id_member'] == $user_info['id'])
				continue;
			elseif (empty($arcadeSettings[$rowmember['id_member']]['new_champion_own']) && empty($arcadeSettings[$rowmember['id_member']]['new_champion_any']))
				continue;
			elseif (empty($arcadeSettings[$rowmember['id_member']]['new_champion_any']) && $data['game']['champion']['id'] != $rowmember['id_member'])
				continue;

			if (!empty($arcadeModSettings['gamesNotificationsBulk']))
			{
				// send email ??
				if (!empty($arcadeSettings[$rowmember['id_member']]['champion_email']))
					$emails[] = $rowmember['email_address'];

				// Now send the PM notification if it is enabled
				if (!empty($arcadeSettings[$rowmember['id_member']]['champion_pm']))
					$pvts[] = $rowmember['id_member'];

				continue;
			}
			else
			{
				// change notifications language for the specific destined user else the forum default
				$lang = empty($rowmember['lngfile']) || empty($arcadeModSettings['userLanguage']) ? $language : $rowmember['lngfile'];
				loadLanguage('ArcadeEmail', $lang, false, false);
				$adj = $data['game']['champion']['id'] == $rowmember['id_member'] ? 'own' : 'any';

				// send email ??
				if (!empty($arcadeSettings[$rowmember['id_member']]['champion_email']))
				{
					switch ($id_event)
					{
						case 'new_champion':
							$message = str_ireplace(array('{MBNAME}', '{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_PROFILE}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($mbname, $old_champ, $game, $new_champ, $old_champ, $txt['arcade_email_profile'], $mbname, $data['score']['score'], '<a href="' . str_replace('action=' . $action, 'action=ingressarcade', $data['game']['url']['play']) . '">' . $txt['arcade_pm_play_game'] . '</a>'), $txt['notification_arcade_new_champion_' . $adj . $newChamp . '_body']);
							$subject = str_ireplace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_' . $adj . $newChamp . '_subject']);
							$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $message . '</div></body></html>';
							$replacements = array(
								'MBNAME' => $mbname,
								'SUBJECT' => $subject,
								'MESSAGE' => $htmlMessage,
								'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
								'READLINK' => str_replace('action=' . $action, 'action=ingressarcade', $data['game']['url']['highscore']),
								'REPLYLINK' => str_replace('action=' . $action, 'action=ingressarcade', $data['game']['url']['play']),
								'TOLIST' => $rowmember['email_address'],
								'old_champion.name' => $old_champ,
								'champion.score' => $data['score']['score'],
								'GAMENAMESUB' => $data['game']['name'],
								'GAMENAME' => $game,
								'play.the.game' => '<a href="' . str_replace('action=' . $action, 'action=ingressarcade', $data['game']['url']['play']) . '">' . $txt['arcade_pm_play_game'] . '</a>',
								'champion.name' => $new_champ,
								'ARCADE_SETTINGS_PROFILE' => $txt['arcade_email_profile'],
								'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
							);
							$email_template = 'notification_arcade_new_champion_' . $adj . $newChamp;
							break;
						default:
							$message = str_replace(array('{MBNAME}', '{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_PROFILE}', '{REGARDS}'), array($mbname, '<a href="' . $data['match_url'] . ';#arenamatch">' . $txt['arcade_pm_join_match'] . '</a>', $data['match_name'], $txt['arcade_email_profile'], $mbname), $txt['notification_arcade_' . $id_event . '_body']);
							$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
							$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $message . '</div></body></html>';
							$replacements = array(
								'MBNAME' => $mbname,
								'SUBJECT' => $subject,
								'MESSAGE' => $htmlMessage,
								'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
								'READLINK' => $data['match_name'],
								'REPLYLINK' => $data['match_url'] . ';arcade_email=1;#arenamatch',
								'TOLIST' => $rowmember['email_address'],
								'MATCHURL' => '<a href="' . $data['match_url'] . ';arcade_email=1;#arenamatch">' . $txt['arcade_pm_join_match'] . '</a>',
								'MATCHNAME' => $data['match_name'],
								'ARCADE_SETTINGS_PROFILE' => $txt['arcade_email_profile'],
								'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
							);
							$email_template = 'notification_arcade_' . $id_event;
					}

					$emailAddress = $rowmember['email_address'];
					$sendEmailData = true;
				}
				// send the PM ??
				if (!empty($arcadeSettings[$rowmember['id_member']]['champion_pm']))
				{
					switch ($id_event)
					{
						case 'new_champion':
							$message = str_replace(array('{MBNAME}', '{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($mbname, $old_champ, $game_pm, $new_champ, $old_champ, '[iurl=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/iurl]', $mbname, $data['score']['score'], '[iurl=' . $data['game']['url']['play'] . ']' . $txt['arcade_pm_play_game'] . '[/iurl]'), $txt['notification_arcade_new_champion_' . $adj . $newChamp . 'PM_body']);
							$message = str_replace("\'", "&#39;", $message);
							$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_' . $adj . $newChamp . 'PM_subject']);
							$subject = str_replace("\'", "&#39;", $subject);
							break;
						default:
							$message = str_replace(array('{MBNAME}', '{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array($mbname, '[iurl=' . $data['match_url'] . ';#arenamatch]' . $txt['arcade_pm_join_match'] . '[/iurl]', $data['match_name'], '[iurl=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/iurl]', $mbname), $txt['notification_arcade_' . $id_event . 'PM_body']);
							$message = str_replace("\'", "&#39;", $message);
							$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
							$subject = str_replace("\'", "&#39;", $subject);
					}

					$sendId = $rowmember['id_member'];
					$sendPmData = true;
				}
			}

			// send the single PM/Email data
			if ($sendEmailData)
			{
				$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
				$emailsSend = arcadeSendmail(array($emailAddress), $emaildata['subject'], $emaildata['body'], $arcadeModSettings['gamesEmail'], false, true, 2, null, true, 'base64');
			}

			if ($sendPmData)
				arcade_sendpm(array('to' => array($sendId), 'bcc' => array()), arcade_html_entity_decode($subject, 2, 2), arcade_html_entity_decode($message, 2, 2), '0', $from, '0');
		}

		$smcFunc['db_free_result']($request_member);
	}

	if (!empty($arcadeModSettings['gamesNotificationsBulk']))
	{
		// bulk Emails
		if (!empty($emails))
		{
			switch ($id_event)
			{
				case 'new_champion':
					$message = str_replace(array('{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_PROFILE}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($old_champ, $game, $new_champ, $old_champ, $txt['arcade_email_profile'], $mbname, $data['score']['score'], '<a href="' . $data['game']['url']['play'] . '">' . $txt['arcade_pm_play_game'] . '</a>'), $txt['notification_arcade_new_champion_any' . $newChamp . '_body']);
					$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_any' . $newChamp . '_subject']);
					$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $subject . '</div></body></html>';
					$replacements = array(
						'MBNAME' => $mbname,
						'SUBJECT' => $subject,
						'MESSAGE' => $htmlMessage,
						'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
						'READLINK' => $data['game']['url']['highscore'] . ';arcade_email=1;hs=1',
						'REPLYLINK' => $data['game']['url']['play'] . ';arcade_email=1',
						'TOLIST' => $emails,
						'old_champion.name' => $old_champ,
						'champion.score' => $data['score']['score'],
						'GAMENAMESUB' => $data['game']['name'],
						'GAMENAME' => $game,
						'play.the.game' => '<a href="' . $data['game']['url']['play'] . ';arcade_email=1">' . $txt['arcade_pm_play_game'] . '</a>',
						'champion.name' => $new_champ,
						'ARCADE_SETTINGS_PROFILE' => $txt['arcade_email_profile'],
						'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
					);
					$email_template = 'notification_arcade_new_champion_any' . $newChamp;
					break;
				default:
					$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_PROFILE}', '{REGARDS}'), array('<a href="' . $data['match_url'] . ';arcade_email=1;#arenamatch">' . $txt['arcade_pm_join_match'] . '</a>', $data['match_name'], $txt['arcade_email_profile'], $mbname), $txt['notification_arcade_' . $id_event . '_body']);
					$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
					$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div>' . $subject . '</div></body></html>';
					$replacements = array(
						'MBNAME' => $mbname,
						'SUBJECT' => $subject,
						'MESSAGE' => $htmlMessage,
						'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
						'READLINK' => $data['match_name'],
						'REPLYLINK' => $data['match_url'] . ';arcade_email=1;#arenamatch',
						'TOLIST' => $rowmember['email_address'],
						'MATCHURL' => '<a href="' . $data['match_url'] . ';arcade_email=1;#arenamatch">' . $txt['arcade_pm_join_match'] . '</a>',
						'MATCHNAME' => $data['match_name'],
						'ARCADE_SETTINGS_PROFILE' => $txt['arcade_email_profile'],
						'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
					);
					$email_template = 'notification_arcade_' . $id_event;
			}

			$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
			$emailsSend = arcadeSendmail($emails, $emaildata['subject'], $emaildata['body'], $arcadeModSettings['gamesEmail'], false, true, 2, null, true, 'base64');
		}

		// bulk PMs
		if (!empty($pvts))
		{
			switch ($id_event)
			{
				case 'new_champion':
					$message = str_replace(array('{MBNAME}', '{old_champion.name}', '{GAMENAME}', '{champion.name}', '{old_champion.name}', '{ARCADE_SETTINGS_URL}', '{REGARDS}', '{champion.score}', '{play.the.game}'), array($mbname, $old_champ, $game_pm, $new_champ, $old_champ, '[iurl=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/iurl]', $mbname, $data['score']['score'], '[iurl=' . $data['game']['url']['play'] . ']' . $txt['arcade_pm_play_game'] . '[/iurl]'), $txt['notification_arcade_new_champion_any' . $newChamp . 'PM_body']);
					$message = str_replace("\'", "&#39;", $message);
					$subject = str_replace(array('{old_champion.name}', '{GAMENAMESUB}'), array($old_champ, $data['game']['name']), $txt['notification_arcade_new_champion_any' . $newChamp . 'PM_subject']);
					$subject = str_replace("\'", "&#39;", $subject);
					break;
				default:
					$message = str_replace(array('{MATCHURL}', '{MATCHNAME}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array('[iurl=' . $data['match_url'] . ']' . $txt['arcade_pm_join_match'] . '[/iurl]', $data['match_name'], '[iurl=' . $scripturl . '?action=profile;area=arcadeSettings;' . ']' . $txt['arcade_pm_profile'] . '[/iurl]', $mbname), $txt['notification_arcade_' . $id_event . '_body']);
					$message = str_replace("\'", "&#39;", $message);
					$subject = str_replace('{MATCHNAME}', $data['match_name'], $txt['notification_arcade_' . $id_event . '_subject']);
					$subject = str_replace("\'", "&#39;", $subject);
			}

			arcade_sendpm(array('to' => $pvts, 'bcc' => array()), arcade_html_entity_decode($subject, 2, 2), arcade_html_entity_decode($message, 2, 2), false, $from, 0);
		}
	}

	return true;
}

function arcadeEventNewChampion($event, &$replaces, &$pms, $data, $rom = 0)
{
	global $smcFunc, $scripturl, $txt, $user_info;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	$score = floatval(preg_replace("/[^-0-9\.]/","", $data['score']['score']));
	$replaces += array(
		'champion.name' => $data['member']['name'],
		'champion.score' => comma_format($score),
		'champion.url' => $scripturl . '?action=profile;u=' . $data['member']['id'],
		'GAMENAME' => $data['game']['name'],
		'GAMEURL' => $scripturl . '?action=' . $action . ';game=' . $data['game']['id'] . ';#playgame',
	);

	if ($data['game']['is_champion'])
	{
		$replaces += array(
			'old_champion.name' => $data['game']['champion']['name'],
			'old_champion.score' => $data['game']['champion']['score'],
			'old_champion.url' => $scripturl . '?action=profile;u=' . $data['member']['id'],
		);

		$send = checkNotificationReceiver($data['game']['champion']['id'], $event, 'new_champion_own');
		$send &= $data['member']['id'] != $data['game']['champion']['id'];

		if ($send)
			$pms[$data['game']['champion']['id']] = 'new_champion_own';

		addNotificationRecievers($pms, $event, 'new_champion_any');
	}
}

function arcadeEventChampionEmail()
{
	// just to satisfy the existing sub-routine of adding parameters to the notifications array
	return false;
}

function arcadeEventGameReports()
{
	// just to satisfy the existing sub-routine of adding parameters to the notifications array
	return false;
}

function arcadeEventArenaGeneral($event, &$replaces, &$pms, $data)
{
	global $db_prefix, $scripturl, $txt, $user_info;

	$replaces += array(
		'MATCHURL' => $data['match_url'] . ';#arenamatch',
		'MATCHNAME' => $data['match_name'],
	);

	if (empty($data['players']))
		return;

	$pms = $pms + checkNotificationReceivers($data['players'], $event, $event['id']);
}

function arcadeNewGameEvent()
{
	return false;
}

// Check
function checkNotificationReceiver($member, $event, $type)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT {raw:type}
		FROM {db_prefix}arcade_members AS arcset
		WHERE arcset.id_member = {int:member}',
		array(
			'type' => $type,
			'member' => $member,
		)
	);

	$numRow = $smcFunc['db_num_rows']($request);
	list ($value) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	if ($numRow == 0)
		return $event['notification'][$type];

	return $value;
}

function checkNotificationReceivers($members, $event, $type)
{
	global $smcFunc;
	/*
	if ($event['notification'][$type])
		$where = '({raw:type} = 1) OR ISNULL(arcset.value))';
	else
		$where = '(arcset.variable = {string:type} AND arcset.value = 1)';
	*/
	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.email_address
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}arcade_members AS arcset ON (arcset.id_member = mem.id_member)
		WHERE {raw:type} = 1
			AND mem.id_member IN({array_int:members})',
		array(
			'type' => 'arcset.' . $type,
			'members' => $members
		)
	);

	$pms = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($pms[$row['id_member']]))
			$pms[$row['id_member']] = $type;
	}
	$smcFunc['db_free_result']($request);

	return $pms;
}

// PM
function addNotificationRecievers(&$pms, $event, $type)
{
	global $db_prefix, $smcFunc;

	/*
	if ($event['notification'][$type])
		$where = '((arcset.variable = {string:type} AND arcset.value = 1) OR ISNULL(arcset.value))';
	else
		$where = '(arcset.variable = {string:type} AND arcset.value = 1)';
	*/

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.email_address
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}arcade_members AS arcset ON (arcset.id_member = mem.id_member)
		WHERE {raw:type} = 1',
		array(
			'type' => 'arcset.' . $type,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($pms[$row['id_member']]))
			$pms[$row['id_member']] = $type;
	}
	$smcFunc['db_free_result']($request);
}

// Send off an email.
function arcadeSendmail($to, $subject, $message, $from = null, $message_id = null, $send_html = false, $priority = 3, $hotmail_fix = null, $is_private = false, $subEncode = '')
{
	$result = arcadeSendmail21($to, $subject, $message, $from, $message_id, $send_html, $priority, $hotmail_fix, $is_private, $subEncode);
	return $result;
}

// Send off a PM.
function arcade_sendpm($recipients, $subject, $message, $store_outbox = false, $from = null, $pm_head = 0)
{
	$log = arcade_sendpm21($recipients, $subject, $message, $store_outbox, $from, $pm_head);
	return $log;
}

// Score saving
function SaveScore(&$game, $member, $score)
{
	global $db_prefix, $arcadeModSettings, $context, $smcFunc, $user_info;

	list($scoreTime, $rom) = array(time(), 0);
	if (!empty($game['id'])) {
		$request = $smcFunc['db_query']('', '
			SELECT rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game = {int:game}
			LIMIT 1',
			array(
				'game' => $game['id'],
			)
		);

		list ($rom) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$rom = !empty($rom) ? 1 : 0;
	}

	if (empty($_SESSION['arcade_score_stop']) || $scoreTime - $_SESSION['arcade_score_stop'] > 5) {
		$_SESSION['arcade_score_stop'] = $scoreTime;
		// score type = 1
		$reverse = !empty($game['score_type']) && $game['score_type'] == 1 ? true : false;

		// No error by default
		$canSave = true;
		$error = '';
		$member['id'] = !empty($member['id']) ? $member['id'] : 0;
		$scoreLimit = 0;
		$score['score'] = floatval($score['score']);

		if (!empty($arcadeModSettings['arcadeMaxScores']))
			$scoreLimit = (int) $arcadeModSettings['arcadeMaxScores'];

		if (!isset($game['id']) || $member['id'] < 0)
			return array();
		elseif (!$user_info['is_guest'] && empty($member['id']))
			return array();

		if (!empty($scoreLimit))
		{
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}arcade_scores
				WHERE id_game = {int:game}
					AND id_member = {int:member}',
				array(
					'game' => $game['id'],
					'member' => $member['id']
				)
			);

			list ($scoreCount) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			if ($scoreCount <= $scoreLimit)
				$canSave = true;
			else
					{
				while ($scoreCount > $scoreLimit)
				{
					$request = $smcFunc['db_query']('', '
						SELECT id_score, score, position
						FROM {db_prefix}arcade_scores
						WHERE id_game = {int:game}
								AND id_member = {int:member}
						ORDER BY score ' . ($reverse ? 'DESC' : 'ASC'),
						array(
							'game' => $game['id'],
							'member' => $member['id']
						)
					);

					list ($old_id_score, $oldScore, $lPosition) = $smcFunc['db_fetch_row']($request);
					$smcFunc['db_free_result']($request);
					$oldScore = floatval($oldScore);

					if (!$reverse)
						$deleteOld = $oldScore < $score['score'];
					else
						$deleteOld = $oldScore > $score['score'];

					if (!$deleteOld)
					{
						$canSave = false;
						$error = 'arcade_scores_limit';

						break;
					}
					else
					{
						$request = $smcFunc['db_query']('', '
							DELETE FROM {db_prefix}arcade_scores
							WHERE id_score = {int:score}',
							array(
								'score' => $old_id_score
							)
						);

						updatePositions($game, $lPosition, '- 1');

						$scoreCount--;
					}
				}
			}
		}

		// Get position
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_scores
			WHERE score ' . ($reverse ? '<=' : '>='). ' {float:score}
				AND id_game = {int:game}',
			array(
				'score' => $score['score'],
				'game' => $game['id']
			)
		);

		list ($position) = $smcFunc['db_fetch_row']($result);
		$position++;
		$smcFunc['db_free_result']($result);

		if ($position == 1)
			$championFrom = $score['endTime'];
		else
			$championFrom = 0;

		if (!$canSave)
			return array('id' => false, 'error' => $error);

		// Update positions
		updatePositions($game, $position, '+ 1');

		$isPersonalBest = false;

		// This is my score
		if ($member['id'] != 0 && $user_info['id'] == $member['id'])
		{
			if (!$reverse)
				$isPersonalBest = $game['personal_best_score'] < $score['score'];
			else
				$isPersonalBest = $game['personal_best_score'] > $score['score'];
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT score
				FROM {db_prefix}arcade_scores
				WHERE id_member = {int:member}
					AND personal_best = 1',
				array(
					'member' => $member['id']
				)
			);

			if ($smcFunc['db_num_rows'] == 0)
				$isPersonalBest = true;
			else
			{
				list ($personalBestScore) =  $smcFunc['db_fetch_row']($request);

				if (!$reverse)
					$isPersonalBest = $personalBestScore < $score['score'];
				else
					$isPersonalBest = $personalBestScore > $score['score'];
			}
			$smcFunc['db_free_result']($request);
		}

		if ($member['id'] != 0 && $game['is_personal_best'] && $isPersonalBest)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET personal_best = 0
				WHERE id_game = {int:game}
					AND id_member = {int:member}',
				array(
					'game' => $game['id'],
					'member' => $member['id']
				)
			);
		}

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_scores',
			array(
				'id_game' => 'int',
				'id_member' => 'int',
				'player_name' => 'string',
				'member_ip' => 'string',
				'score' => 'float',
				'position' => 'int',
				'duration' => 'float',
				'end_time' => 'int',
				'champion_from' => 'int',
				'champion_to' => 'int',
				'comment' => 'string',
				'personal_best' => 'int',
				'score_status' => 'string-30',
				'validate_hash' => 'string-255',
			),
			array(
				intval($game['id']),
				intval($member['id']),
				strval($member['name']),
				strval($member['ip']),
				floatval($score['score']),
				intval($position),
				floatval($score['duration']),
				intval($score['endTime']),
				intval($championFrom),
				0,
				'',
				$isPersonalBest ? 1 : 0,
				strval($score['status']),
				strval($score['hash']),
			),
			array('id_game')
		);

		$id_score = $smcFunc['db_insert_id']('{db_prefix}arcade_scores', 'id_score');
		$score['id'] = $id_score;

		if ($position == 1)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET champion_to = {int:end_time}
				WHERE id_score = {int:score}',
				array(
					'end_time' => $score['endTime'],
					'score' => $game['champion']['score_id'],
				)
			);

			updateGame($game['id'], array('champion' => $member['id'], 'champion_score' => $score['id']), false, $rom);

			$event = array(
				'game' => &$game,
				'member' => $member,
				'score' => $score,
				'time' => $score['endTime']
			);

			arcadeEvent('new_champion', $event, '');
		}

		$_SESSION['arcade_score_stop_id'] = array(
			'id' => $id_score,
			'isPersonalBest' => $isPersonalBest,
			'position' => $position,
			'newChampion' => $position == 1
		);
		return $_SESSION['arcade_score_stop_id'];
	}
	elseif (!empty($_SESSION['arcade_score_stop_id'])) {
		$s = $_SESSION['arcade_score_stop_id'];
		unset($_SESSION['arcade_score_stop_id']);
		return $s;
	}
}

// Delete Scores
function deleteScores(&$game, $id_score)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc, $user_info, $boarddir;

	if (!is_array($id_score) && is_numeric($id_score))
		$id_score = array((int) $id_score);

	if (empty($id_score))
		return true;

	$rom = 0;
	if (!empty($game['id'])) {
		$request = $smcFunc['db_query']('', '
			SELECT rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game = {int:game}
			LIMIT 1',
			array(
				'game' => $game['id'],
			)
		);

		list ($rom) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$rom = !empty($rom) ? 1 : 0;
	}

	$request = $smcFunc['db_query']('', '
		SELECT id_score, id_member, position, score, personal_best
		FROM {db_prefix}arcade_scores
		WHERE id_score IN({array_int:score})
			AND id_game = {int:game}
		ORDER BY position',
		array(
			'game' => $game['id'],
			'score' => $id_score,
		)
	);

	$removeIds = array();
	$personalBest = array();
	$positions = array();
	$championUpdate = false;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['personal_best'])
			$personalBest[] = $row['id_member'];

		$removeIds[] = $row['id_score'];
		$positions[] = $row['position'];

		if ($row['position'] == 1)
			$championUpdate = true;
	}
	$smcFunc['db_free_result']($request);

	$personalBest = array_unique($personalBest);

	$count = -1;

	if (empty($positions))
		return true;

	$startPos = $positions[0];

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_score IN({array_int:scores})',
		array(
			'scores' => $removeIds,
		)
	);

	// Log removed scores
	$trace = !empty($game['gamename']) ? $game['gamename'] : $game['id'];
	logAction('arcade_remove_scores', array('game' => $trace, 'scores' => count($removeIds)));

	for ($i = 0; $i < count($positions); $i++)
	{
		if (isset($positions[$i + 1]) && $positions[$i] + 1 == $positions[$i + 1])
			$count--;
		else
		{
			updatePositions($game, $startPos, $count);

			$startPos = $positions[$i];
			$count = -1;
		}
	}

	if ($championUpdate)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_score, id_member
			FROM {db_prefix}arcade_scores
			WHERE position = 1
				AND id_game = {int:game}
			LIMIT 1',
			array(
				'game' => $game['id'],
			)
		);

		$row = $smcFunc['db_fetch_assoc']($request);

		if (!empty($row) && !empty($game['id']) && !empty($row['id_member']) && !empty($row['id_score']))
			updateGame($game['id'], array('champion' => $row['id_member'], 'champion_score' => $row['id_score']), false, $rom);
		elseif (!empty($game['id']))
			updateGame($game['id'], array('champion' => 0, 'champion_score' => 0), false, $rom);

		$smcFunc['db_free_result']($request);

		if (!empty($row['id_score']))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET champion_from = {int:time}
				WHERE id_score = {int:score}',
				array(
					'time' => time(),
					'score' => $row['id_score'],
				)
			);
		}
	}

	if (empty($personalBest))
		return true;

	$request = $smcFunc['db_query']('', '
		SELECT id_score, id_member
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}
			AND id_member IN({array_int:members})
		ORDER BY position',
		array(
			'game' => $game['id'],
			'members' => $personalBest,
		)
	);

	$newPersonalBest = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($newPersonalBest[$row['id_member']]))
			$newPersonalBest[$row['id_member']] = $row['id_score'];
	}
	$smcFunc['db_free_result']($request);

	if (empty($newPersonalBest))
		return true;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET personal_best = 1
		WHERE id_score IN({array_int:scores})',
		array(
			'scores' => $newPersonalBest,
		)
	);

	return true;
}

// Updates positions. (new score, remove)
function updatePositions(&$game, $start, $how)
{
	global $db_prefix, $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET position = position ' . $how . '
		WHERE id_game = {int:game}
			AND position >= {int:position}',
		array(
			'game' => $game['id'],
			'position' => $start,
		)
	);

	return $smcFunc['db_affected_rows']();
}

function loadMatch($match)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.name, m.private_game, m.created, m.updated, m.status,
			m.num_players, m.current_players, m.num_rounds, m.current_round, m.id_member,
			IFNULL(me.id_member, 0) AS participation, me.status AS my_state
		FROM {db_prefix}arcade_matches AS m
			LEFT JOIN {db_prefix}arcade_matches_players AS me ON (me.id_match = m.id_match
				AND me.id_member = {int:current_member})
		WHERE m.id_match = {int:match}
			AND ' . $user_info['query_see_match'],
		array(
			'match' => $match,
			'current_member' => $user_info['id'],
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!$row) {
		arcadeClearSession();
		fatal_lang_error('match_not_found', false);
	}

	$status = array(
		0 => 'arcade_arena_player_invited',
		1 => 'arcade_arena_player_waiting',
		2 => 'arcade_arena_player_waiting',
		3 => 'arcade_arena_player_played',
		4 => 'arcade_arena_player_knockedout',
		10 => 'arcade_arena_waiting_players',
		11 => 'arcade_arena_waiting_other_players',
		20 => 'arcade_arena_started',
		21 => 'arcade_arena_not_played',
		22 => 'arcade_arena_not_played',
		23 => 'arcade_arena_not_other_played',
		24 => 'arcade_arena_dropped',
		30 => 'arcade_arena_complete',
		31 => 'arcade_arena_complete',
		32 => 'arcade_arena_complete',
		33 => 'arcade_arena_complete',
		34 => 'arcade_arena_complete',
	);

	$context['match'] = array(
		'id' => $row['id_match'],
		'name' => $row['name'],
		'private' => !empty($row['private_game']),
		'created' => timeformat($row['created']),
		'updated' => !empty($row['updated']) ? timeformat($row['updated']) : '',
		'players' => array(),
		'starter' => $row['id_member'],
		'num_players' => $row['current_players'],
		'players_limit' => $row['num_players'],
		'round' => $row['current_round'],
		'rounds' => array(),
		'num_rounds' => $row['num_rounds'],
		//'status' => $status[$row['status']],
		'status' => $status[$row['my_state'] + ($row['status'] * 10 + 10)],
	);

	$can_play = $row['participation'] && ($row['my_state'] == 1 || $row['my_state'] == 2) && $row['status'] == 1;
	$context['can_play_match'] = false;
	$context['can_leave'] = $row['participation'] && $row['status'] == 0 && $row['my_state'] != 0;
	$context['can_accept'] = $row['participation'] && $row['my_state'] == 0 && $row['status'] == 0;
	$context['can_decline'] = $row['participation'] && $row['my_state'] == 0 && $row['status'] == 0;
	$context['can_join_match'] = allowedTo('arcade_join_match') && $row['status'] == 0 && !$row['participation'] && $row['current_players'] < $row['num_players'];
	$context['can_edit_match'] = (allowedTo('arcade_admin') || $context['match']['starter'] == $user_info['id']) && $row['status'] != 2;
	$context['can_start_match'] = $context['can_edit_match'] && $row['status'] == 0 && $row['current_players'] >= 2;

	unset($row);

	// Load players
	$request = $smcFunc['db_query']('', '
		SELECT p.id_member, p.status, p.score, mem.real_name
		FROM {db_prefix}arcade_matches_players AS p
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.id_member)
		WHERE p.id_match = {int:match}
		ORDER BY score DESC',
		array(
			'match' => $context['match']['id']
		)
	);

	$rank = 1;

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['match']['players'][$row['id_member']] = array(
			'id' => $row['id_member'],
			'rank' => $rank++,
			'name' => $row['real_name'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'score' => comma_format(floatval($row['score'])),
			'status' => $txt[$status[$row['status']]],
			'can_accept' => $row['status'] == 0 && $row['id_member'] == $user_info['id'],
			'can_decline' => $row['status'] == 0 && $row['id_member'] == $user_info['id'],
			'can_kick' => $context['can_edit_match'] && $row['id_member'] != $user_info['id'],
			'accept_url' => $scripturl . '?action=arcade;sa=viewMatch;join;match=' . $context['match']['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . ';#arenamatch',
			'decline_url' => $scripturl . '?action=arcade;sa=viewMatch;leave;match=' . $context['match']['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . ';#arenamatch',
			'kick_url' => $scripturl . '?action=arcade;sa=viewMatch;kick;player=' . $row['id_member'] . ';match=' . $context['match']['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . ';#arenamatch',
		);

		$context['can_start_match'] &= $row['status'] != 0;
	}
	$smcFunc['db_free_result']($request);

	// Load rounds
	$request = $smcFunc['db_query']('', '
		SELECT r.round, r.id_game, r.status, game.game_name
		FROM {db_prefix}arcade_matches_rounds As r
			LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = r.id_game)
		WHERE r.id_match = {int:match}
		ORDER BY r.round',
		array(
			'match' => $context['match']['id']
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['match']['rounds'][$row['round']] = array(
			'round' => $row['round'],
			'game' => $row['id_game'],
			'name' => $row['game_name'],
			'status' => $row['status'],
			'select' => !(empty($row['game_name']) || empty($row['id_game'])),
			'can_select' => (empty($row['game_name']) || empty($row['id_game'])) && $context['can_edit_match'],
			'can_play' => !empty($row['id_game']) && $row['round'] == $context['match']['round'] && $can_play,
			'play_url' => $scripturl . '?action=arcade;sa=play;match=' . $context['match']['id'] . ';#arenamatch',
		);

		if ($context['match']['rounds'][$row['round']]['can_play'])
			$context['can_play_match'] = true;
	}
	$smcFunc['db_free_result']($request);
}

// createMatch
function createMatch($matchOptions)
{
	global $smcFunc, $db_prefix, $sourcedir, $scripturl, $user_info, $txt;

	if (empty($matchOptions['created']))
		$matchOptions['created'] = time();

	if (!isset($matchOptions['private_game']))
		$matchOptions['private_game'] = 0;

	if (!isset($matchOptions['starter']))
		$matchOptions['starter'] = $user_info['id'];

	if (!empty($matchOptions['extra']))
		$matchOptions['extra'] = serialize($matchOptions['extra']);
	else
		$matchOptions['extra'] = '';

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_matches',
		array(
			'name' => 'string',
			'id_member' => 'int',
			'private_game' => 'int',
			'status' => 'int',
			'created' => 'int',
			'updated' => 'int',
			'num_players' => 'int',
			'current_players' => 'int',
			'num_rounds' => 'int',
			'current_round' => 'int',
			'match_data' => 'string',
		),
		array(
			$matchOptions['name'],
			$matchOptions['starter'],
			!empty($matchOptions['private_game']) ? 1 : 0,
			0,
			$matchOptions['created'],
			0,
			$matchOptions['num_players'],
			0,
			$matchOptions['num_rounds'],
			0,
			$matchOptions['extra']
		),
		array()
	);

	$id_match = $smcFunc['db_insert_id']('{db_prefix}arcade_matches', 'id_match');

	$rows = array();
	for ($i = 0; $i < $matchOptions['num_rounds']; $i++)
	{
		$rows[] = array(
			$id_match,
			$i + 1,
			isset($matchOptions['games'][$i]) ? $matchOptions['games'][$i] : 0,
			0,
		);
	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_matches_rounds',
		array(
			'id_match' => 'int',
			'round' => 'int',
			'id_game' => 'int',
			'status' => 'int',
		),
		$rows,
		array()
	);
	unset($rows);

	if (!empty($matchOptions['players']))
	{
		require_once($sourcedir . '/Subs-Post.php');

		$players = array();

		foreach ($matchOptions['players'] as $id)
			$players[$id] = $id == $matchOptions['starter'] ? 1 : 0;

		matchAddPlayers($id_match, $players);
	}

	return $id_match;
}

function matchAddPlayers($id_match, $players)
{
	global $smcFunc, $sourcedir, $scripturl;

	require_once($sourcedir . '/Subs-Post.php');

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.name, m.current_players, m.num_players
		FROM {db_prefix}arcade_matches AS m
		WHERE m.id_match = {int:match}',
		array(
			'match' => $id_match,
		)
	);
	$matchInfo = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!$matchInfo)
		return false;

	if ((count($players) + $matchInfo['current_players']) > $matchInfo['num_players'])
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT id_member, real_name
		FROM {db_prefix}members
		WHERE id_member IN({array_int:members})',
		array(
			'members' => array_keys($players),
		)
	);

	$rows = array();
	$sendPms = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$rows[] = array(
			$id_match,
			$row['id_member'],
			$players[$row['id_member']],
			'',
		);

		if ($players[$row['id_member']] == 0)
			$sendPms[] = $row['id_member'];
	}
	$smcFunc['db_free_result']($request);

	if (!empty($sendPms))
		arcadeEvent('arena_invite',
			array(
				'match_name' => $matchInfo['name'],
				'match_id' => $id_match,
				'match_url' => $scripturl . '?action=arcade;match=' . $id_match,
				'players' => $sendPms,
			), ''
		);

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_matches_players',
		array(
			'id_match' => 'int',
			'id_member' => 'int',
			'status' => 'int',
			'player_data' => 'string',
		),
		$rows,
		array()
	);

	unset($rows);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_matches
		SET current_players = {int:players}
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match,
			'players' => (count($players) + $matchInfo['current_players']),
		)
	);

	matchUpdateStatus($id_match);

	return true;
}

function matchUpdatePlayers($id_match, $players, $status = 1)
{
	global $smcFunc, $boarddir;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_matches_players
		SET status = {int:status}
		WHERE id_match = {int:match}
			AND id_member IN({array_int:players})',
		array(
			'match' => $id_match,
			'players' => $players,
			'status' => $status,
		)
	);

	matchUpdateStatus($id_match);

	return true;
}

function matchUpdateStatus($id_match)
{
	global $smcFunc, $scripturl, $boarddir;

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.name, m.current_players, m.num_players, m.current_round, m.num_rounds, m.status, m.match_data
		FROM {db_prefix}arcade_matches AS m
		WHERE m.id_match = {int:match}',
		array(
			'match' => $id_match,
		)
	);
	$matchInfo = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$matchInfo['match_data'] = !empty($matchInfo['match_data']) && arcadeIsSerialized($matchInfo['match_data']) ? arcade_safe_unserialize($matchInfo['match_data']) : array();
	if ($matchInfo['status'] == 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_matches_players
			WHERE id_match = {int:match}
				AND status = 0',
			array(
				'match' => $id_match,
			)
		);

		list ($cn) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ($cn > 0)
			return;

		if ($matchInfo['current_players'] == $matchInfo['num_players'])
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches
				SET status = 1, current_round = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);

			// No one has played yet
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches_players
				SET status = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);
		}
	}
	elseif ($matchInfo['status'] == 1)
	{
		if ($matchInfo['current_round'] == 0)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches
				SET current_round = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);

			// No one has played yet
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_matches_players
				SET status = 1
				WHERE id_match = {int:match}',
				array(
					'match' => $id_match,
				)
			);
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}arcade_matches_players
				WHERE id_match = {int:match}
					AND (status = 1 OR status = 2)',
				array(
					'match' => $id_match,
				)
			);

			list ($cn) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			// Has all played?
			if ($cn > 0)
				return;

			$request = $smcFunc['db_query']('', '
				SELECT id_game
				FROM {db_prefix}arcade_matches_rounds
				WHERE id_match = {int:match}
					AND round = {int:round}',
				array(
					'match' => $id_match,
					'round' => $matchInfo['current_round'],
				)
			);

			$round = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			$request = $smcFunc['db_query']('', '
				SELECT id_game, score_type, extra_data
				FROM {db_prefix}arcade_games
				WHERE id_game = {int:game}',
				array(
					'game' => $round['id_game'],
				)
			);

			$game = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			$order = !empty($game['score_type']) && $game['score_type'] == 1 ? 'ASC' : 'DESC';

			// Scores to give
			$scores = array(10, 8, 6, 5, 4, 3, 2, 1);

			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}arcade_matches_results
				WHERE id_match = {int:match}
					AND round = {int:round}
				ORDER BY score ' . $order . '',
				array(
					'match' => $id_match,
					'round' => $matchInfo['current_round'],
				)
			);

			$current = 0;

			$players = array();

			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (isset($scores[$current]))
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_matches_players
						SET score = score + {int:score}
						WHERE id_match = {int:match}
							AND id_member = {int:player}',
						array(
							'match' => $id_match,
							'player' => $row['id_member'],
							'score' => $scores[$current],
						)
					);
				}

				$players[] = $row['id_member'];

				$current++;
			}
			$smcFunc['db_free_result']($request);

			if ($matchInfo['match_data']['mode'] == 'knockout')
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}arcade_matches_players
					WHERE id_match = {int:match}
					ORDER BY score
					LIMIT 1',
					array(
						'match' => $id_match,
					)
				);

				list ($knockout) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				$request = $smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches_players
					SET status = 4
					WHERE id_match = {int:match}
						AND id_member = {int:member}',
					array(
						'match' => $id_match,
						'member' => $knockout,
					)
				);
			}

			// Last round?
			if ($matchInfo['current_round'] == $matchInfo['num_rounds'])
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches
					SET status = 2
					WHERE id_match = {int:match}',
					array(
						'match' => $id_match,
					)
				);

				arcadeEvent('arena_match_end',
					array(
						'match_name' => $matchInfo['name'],
						'match_id' => $id_match,
						'match_url' => $scripturl . '?action=arcade;match=' . $id_match,
						'players' => $players,
					), ''
				);

				return;
			}
			// Advance to next round
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches
					SET current_round = current_round + 1
					WHERE id_match = {int:match}',
					array(
						'match' => $id_match,
					)
				);

				// No one has played yet
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_matches_players
					SET status = 1
					WHERE id_match = {int:match}
						AND status = 3',
					array(
						'match' => $id_match,
					)
				);

				arcadeEvent('arena_new_round',
					array(
						'match_name' => $matchInfo['name'],
						'match_id' => $id_match,
						'match_url' => $scripturl . '?action=arcade;match=' . $id_match,
						'players' => $players,
					), ''
				);
			}
		}
	}

	return;
}

// matchRemovePlayers
function matchRemovePlayers($id_match, $players)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_players
		WHERE id_match = {int:match}
			AND id_member IN({array_int:players})',
		array(
			'match' => $id_match,
			'players' => $players,
		)
	);

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_matches_players
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match,
		)
	);

	list ($cn) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_matches
		SET current_players = {int:players}
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match,
			'players' => $cn,
		)
	);

	matchUpdateStatus($id_match);

	return true;
}

// deleteMatch
function deleteMatch($id_match)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_players
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_results
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_matches_rounds
		WHERE id_match = {int:match}',
		array(
			'match' => $id_match
		)
	);

	return true;
}

function ArcadeButtonStrip($buttons, $categories, $rom = 0)
{
	global $txt, $context, $arcadeModSettings, $user_info, $scripturl;

	list($cats, $selections, $selected) = array('', '', '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$selected = $smfVersion == 'v2.1' ? 'selected' : 'selected="selected"';
	$context['current_arcade_sa'] = !empty($context['current_arcade_sa']) ? $context['current_arcade_sa'] : (isset($_REQUEST['sa']) && is_string($_REQUEST['sa']) ? $_REQUEST['sa'] : '');
	$arena = in_array($context['current_arcade_sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')) ? true : null;
	$leftMargin = !empty($_SESSION['arcade_isMobile']) ? '0px' : '5px';
	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && empty($rom) && !allowedTo('arcade_download') ? false : (!empty($arcadeModSettings['arcadeDownloadHideLink']) && !empty($rom) && !allowedTo('arcade_download_rom') ? false : $arcadeModSettings['arcadeEnableDownload']);
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;

	if (!empty($_SESSION['arcade_isMobile']))
		$cats = ArcadeCategoryDropdown();
	elseif (!empty($arcadeModSettings['arcadeDropCat']))
			$cats = '
				<div style="margin: 5px 0px 0px ' . $leftMargin. ';font-size:1.0em;padding: 1em 0em 0em 0em;"><div><span style="display: none;">&nbsp;</span></div>' . ArcadeCategoryDropdown($rom) . '</div>';

	$typeSelections = Arcade_DoToolBarStrip('index', 'bottom', '', $rom);

	$selections = '
				<div class="arcade_buttons_margin" style="margin: 5px 0px 0px ' . $leftMargin . ';font-size:1.0em;padding: 1em 1em 0em 0em;"><div><span style="display: none;">&nbsp;</span></div>
					<select class="arcade_select" name="arcade_options" onchange="location = this.value;">';

	$addOptions = '';
	if (!empty($typeSelections[0]) && is_array($typeSelections[0]) && !empty($enableGameDownload))
	{
		// $_SESSION['arcade_download_type']
		$addOptions .= '
		<div style="margin: 5px 0px 0px ' . $leftMargin . ';font-size:1.0em;padding: 1em 1em 0em 0em;">
			<select class="arcade_select" name="arcade_options2" onchange="location = this.value;">';
		foreach ($typeSelections[0] as $archive)
			$addOptions .= '<option value="' . $archive['link'] . '"' . ($_SESSION['arcade_download_type'] == $archive['type'] ? ' ' . $selected : '') . '>' . $archive['text'] . '</option>';

		$addOptions .= '
			</select>
		</div>';
	}
	if (!empty($typeSelections[1]) && is_array($typeSelections[1]))
	{
		// $_SESSION['arcade_gametype_select' . $rom]


		$addOptions .= '
		<div style="margin: 5px 0px 0px ' . $leftMargin . ';font-size:1.0em;padding: 1em 1em 0em 0em;">
			<select class="arcade_select" name="arcade_options3" onchange="location = this.value;">';
		foreach ($typeSelections[1] as $type)
			$addOptions .= '<option value="' . $type['link'] . '"' . ($_SESSION['arcade_gametype_select' . $rom] == $type['type'] ? ' ' . $selected : '') . '>' . $type['text'] . '</option>';

		$addOptions .= '
			</select>
		</div>';
	}

	foreach ($buttons as $button => $data)
	{
		switch ($button)
		{
			case 'arcade_administrator':
				if (!allowedTo('admin_arcade'))
					continue 2;
				break;
			case 'arcade_arena':
				if (empty($rom)) {
					if (empty($arcadeModSettings['arcadeArenaEnabled']))
						continue 2;
					if (!allowedTo('arcade_create_match') && !allowedTo('arcade_join_match') && !allowedTo('arcade_join_invite_match'))
						continue 2;
					if (in_array($context['current_arcade_sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')))
						$selected = 'arcade_arena';
				}
				elseif ($context['current_arcade_sa'] == 'stats')
					$selected = 'arcade_stats';
				break;
			case 'gametype':
				continue 2;
				if (!allowedTo('arcade_gametype_select'))
					continue 2;
				if (!empty($typeSelections[0]))
					$add = $typeSelections[0];
				break;
			case 'archivetype':
				continue 2;
				if (empty($arcadeModSettings['arcadeTypeQuery']))
					continue 2;
				if (!empty($typeSelections[1]))
					$add = $typeSelections[1];
				break;
			default:
				if ($context['current_arcade_sa'] == 'stats')
					$selected = 'arcade_stats';
		}

		$selections .= '
						<option value="' . $data['url'] . '" ' . (!empty($selected) && $selected == $button ? ' selected' : '') . '>
							' . $txt[$data['text']] . '
						</option>';
	}

	if (!empty($_SESSION['arcade_isMobile']))
	{
		$selections .= '
						<option value="' . $scripturl . '?action=forum;' . $context['session_var'] . '=' . $context['session_id'] . '">
							' . $txt['home'] . '
						</option>';

		if ($user_info['is_guest'])
		{
			$selections .= '
						<option value="' . $scripturl . '?action=login;' . $context['session_var'] . '=' . $context['session_id'] . '">
							' . $txt['login'] . '
						</option>';
		}
		else
		{
			$selections .= '
						<option value="' . $scripturl . '?action=logout;' . $context['session_var'] . '=' . $context['session_id'] . '">
							' . $txt['logout'] . '
						</option>';
		}

	}

	$selections .= '
					</select>' . '
				</div>';

	$action = empty($rom) ? 'arcade' : 'retro_arch';
	if (!empty($_SESSION['arcade_isMobile']))
	{
		$mobileSelected = $smfVersion == 'v2.1' ? ' selected' : ' selected="selected"';
		$_SESSION['arcade_sortby' . $rom2] = !empty($_SESSION['arcade_sortby' . $rom2]) ? $_SESSION['arcade_sortby' . $rom2] : 'a2z';
		$_SESSION['mobile_sort_direction' . $rom2] = !empty($_SESSION['mobile_sort_direction' . $rom2]) ? $_SESSION['mobile_sort_direction' . $rom2] : '';
		$_REQUEST['sortby'] = isset($_REQUEST['sortby']) ? ArcadeSpecialChars($_REQUEST['sortby'], 'name') : (!empty($_SESSION['arcade_sortby' . $rom2]) ? ArcadeSpecialChars($_SESSION['arcade_sortby' . $rom2], 'name') :'a2z');
		$context['sort_by'] = !empty($sort_methods[$_REQUEST['sortby']]) ? $_REQUEST['sortby'] : (!empty($_SESSION['arcade_sortby' . $rom2]) ? $_SESSION['arcade_sortby' . $rom2] : 'a2z');
		$context['sort_direction'] = !empty($sort_direction[$context['sort_by']]) ? $sort_direction[$context['sort_by']] : (!empty($_SESSION['arcade_sort_dir' . $rom2]) ? $_SESSION['arcade_sort_dir' . $rom2] : 'asc');
		$context['sort_direction'] = (isset($_REQUEST['dir'])) && in_array(strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')), array('asc', 'desc')) ? strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')) : $context['sort_direction'];
		$_SESSION['arcade_sort_dir' . $rom2] = $context['sort_direction'];

		$selections .= '
				<div style="margin: 5px 0px 0px ' . $leftMargin . ';font-size:1.0em;padding: 1em 1em 0em 0em;">
					<select class="arcade_select" name="arcade_options5" onchange="window.location.href=this.value;">
						<option value="' . $scripturl . '?action=' . $action . ';sortby=a2z;dir=' . ($context['sort_direction'] == 'asc' ? 'asc' : 'desc') . '"' . ($_SESSION['arcade_sortby' . $rom2] == 'a2z' ? $mobileSelected : '') . '>
							' . $txt['arcade_mobile_sort_a2z'] . '
						</option>
						<option value="' . $scripturl . '?action=' . $action . ';sortby=age;dir=' . ($context['sort_direction'] == 'asc' ? 'asc' : 'desc') . '"' . ($_SESSION['arcade_sortby' . $rom2] == 'age' ? $mobileSelected : '') . '>
							' . $txt['arcade_mobile_sort_new'] . '
						</option>
						<option value="' . $scripturl . '?action=' . $action . ';sortby=' . $_SESSION['arcade_sortby' . $rom2] . ';dir=' . ($context['sort_direction'] == 'asc' ? 'desc' : 'asc') . '">
							' . ($context['sort_direction'] == 'asc' ? $txt['arcade_mobile_sort_dir_desc'] : $txt['arcade_mobile_sort_dir_asc']) . '
						</option>
					</select>
				</div>';
	}

	$javascript = empty($_SESSION['arcade_isMobile']) ? '
	<script type="text/javascript">
		var arcE = document.querySelectorAll("option");
		for (arcX=0;arcE.length<arcX;arcX++) {
			if(arcE[arcX].textContent.length>30)
				arcE[arcX].textContent=arcE[arcX].textContent.substring(0,30)+"...";

			arcE[arcX].style.width = "10em";
			arcE[arcX].style.minWidth = "10em";
			arcE[arcX].style.maxWidth = "10em";
		}
	</script>' : '';

	$div = '
	<div style="padding-bottom: 2em;"><span style="display: none;">&nbsp;</span></div>';
	return $selections . $addOptions . $cats . $javascript . $div;
}

function ArcadeRandomToken($length = 78)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	str_shuffle($characters);
	list($charactersLength, $randomString) = array(strlen($characters), '');
	for ($i = 0; $i < $length; $i++)
		$randomString .= $characters[rand(0, $charactersLength - 1)];

	return $randomString;
}

function ArcadeSessionSanitize($input)
{
	return preg_replace('/[^a-zA-Z0-9\-\_]/', '', (string)$input);
}

function arcade_mb_escape($string)
{
	if (function_exists('mb_ereg_replace'))
		return mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]', '\\\0', $string);
	else
		return preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]~u', '\\\$0', $string);
}

function arcade_get_client_ip()
{
	global $user_info, $boardurl;

	if (!empty($user_info['ip']))
		return trim($user_info['ip']);

	$ip_keys = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);

	foreach ($ip_keys as $key)
	{
		if (array_key_exists($key, $_SERVER) === true)
		{
			foreach (explode(',', $_SERVER[$key]) as $ip)
			{
                $ip = trim($ip);
				if (arcade_validate_ip($ip))
					return $ip;
			}
		}
	}

	$ipx = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	$ipx = $ipx == '0.0.0.0' && strpos($boardurl, 'localhost') !== false ? '127.0.0.1' : $ipx;
	return $ipx;
}

function arcade_validate_ip($ip)
{
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
		return false;

    return true;
}

function arcade_url_exists($url)
{
    if (!$fp = curl_init($url))
		return false;

    return true;
}

function arcade_addslashes($string, $type = 2)
{
	switch ($type){
		case 4:
			$newString = addcslashes($string, '",\'');
			break;
		case 3:
			$newString = addcslashes($string, '"');
			break;
		case 2:
			$newString = addcslashes($string, "'");
			break;
		case 1:
			$newString = addslashes($string);
		default:
			$newString = $string;
	}
	return $newString;
}

function arcade_html_entity_decode($string, $entQuotes = 2, $type = 2)
{
	global $arcadeModSettings, $db_character_set;	
	list($dbcharset, $string, $entQuotes) = array(strtoupper($db_character_set), str_replace(chr(0), '', stripslashes($string)), (int)$entQuotes);

	$supportedCharSets = array(
		'ISO-8859-1' => 'ISO-8859-1',
		'ISO8859-1' => 'ISO-8859-1',
		'ISO-8859-5' => 'ISO-8859-5',
		'ISO8859-5' => 'ISO-8859-5',
		'ISO-8859-15' => 'ISO-8859-15',
		'ISO8859-15' => 'ISO-8859-15',
		'UTF-8' => 'UTF-8',
		'UTF8' => 'UTF-8',
		'CP866' => 'cp866',
		'LIBM866' => 'cp866',
		'866' => 'cp866',
		'CP1251' => 'cp1251',
		'Windows-1251' => 'cp1251',
		'WIN-1251' => 'cp1251',
		'1251' => 'cp1251',
		'CP1252' => 'cp1252',
		'WINDOWS-1252' => 'cp1252',
		'1252' => 'cp1252',
		'KOI8-R' => 'KOI8-R',
		'KOI8-RU' => 'KOI8-R',
		'KOI8R' => 'KOI8-R',
		'BIG5' => 'BIG5',
		'950' => 'BIG5',
		'GB2312' => 'GB2312',
		'936' => 'GB2312',
		'BIG5-HKSCS' => 'BIG5-HKSCS',
		'SHIFT_JIS' => 'Shift_JIS',
		'SJIS' => 'Shift_JIS',
		'SJIS-WIN' => 'Shift_JIS',
		'CP-932' => 'Shift_JIS',
		'932' => 'Shift_JIS',
		'EUC-JP' => 'EUC-JP',
		'EUCJP' => 'EUC-JP',
		'EUCJP-WIN' => 'EUC-JP',
		'MACROMAN' => 'MacRoman'
	);

	$charset = in_array($dbcharset, $supportedCharSets) ? $supportedCharSets[$dbcharset] : 'UTF-8';
	switch ($entQuotes){
		case 0:
			$string = arcade_addslashes(html_entity_decode($string, ENT_HTML5 | ENT_NOQUOTES, $charset), $type);
			break;
		case 1:
			$string = arcade_addslashes(html_entity_decode($string, ENT_HTML5 | ENT_COMPAT, $charset), $type);
			break;
		default:
			$string = arcade_addslashes(html_entity_decode($string, ENT_HTML5 | ENT_QUOTES, $charset), $type);
	}

    $string = preg_replace("/\\\\{2,}/", "\\\\", $string);
	return str_replace('\\\\', '\\', $string);
}

function arcadeRuffleIsValidUrl($url = '')
{
	if(!filter_var($url, FILTER_VALIDATE_URL)){
        return false;
    }
    $curlInit = curl_init($url);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,3);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($curlInit);
    curl_close($curlInit);
	if ($response) {
		$contents = file_get_contents($url);
		if (!empty($contents) && substr($contents,0,6) == '(()=>{')
			return true;
	}

	return false;
}

function ArcadeIconAdjustment($rom = 0, $list = 'Generic')
{
	global $context, $arcadeModSettings;

	/* cover art -> min: 80px | max: 400px ~ thumbnails -> min: 50px | max: 150px */
	foreach (array('arcade_coverWidth', 'arcade_coverHeight') as $coverSetting) {
		$context[$coverSetting] = 80;
	}
	foreach (array('arcadeThumbWidth', 'arcadeThumbHeight') as $thumbSetting) {
		$context[$thumbSetting] = 50;
	}
	foreach (array('arcade_coverFullWidth', 'arcadeResizeCoverArt') as $coverSetting) {
		$context[$coverSetting] = false;
	}
	if ($list != 'Default') {
		foreach (array('arcade_thumbWidth', 'arcade_thumbHeight') as $thumbSetting) {
			$thumb[$thumbSetting] = !empty($arcadeModSettings[$thumbSetting . $list]) ? intval($arcadeModSettings[$thumbSetting . $list]) : 50;
			$thumb[$thumbSetting] = $thumb[$thumbSetting] > 150 ? 150 : ($thumb[$thumbSetting] < 50 ? 50 : $thumb[$thumbSetting]);
			$thumb[$thumbSetting] = abs($thumb[$thumbSetting]);
		}
		$context['arcadeThumbWidth'] = $thumb['arcade_thumbWidth'];
		$context['arcadeThumbHeight'] = $thumb['arcade_thumbWidth'];
		foreach (array('arcade_coverFullWidth', 'arcadeResizeCoverArt') as $coverSetting) {
			$context[$coverSetting] = !empty($arcadeModSettings[$coverSetting . $list]) ? true : false;
		}
		foreach (array('arcade_coverWidth', 'arcade_coverHeight') as $coverSetting) {
			$context[$coverSetting] = !empty($arcadeModSettings[$coverSetting . $list]) ? intval($arcadeModSettings[$coverSetting . $list]) : 80;
			$context[$coverSetting] = $context[$coverSetting] > 400 ? 400 : ($context[$coverSetting] < 80 ? 80 : $context[$coverSetting]);
			$context[$coverSetting] = abs($context[$coverSetting]);
		}
	}
}

function ArcadeListCoverIcon($rom, $gameDir, $gameUrl, $cover_icon, $coverfile, $thumbnail)
{
	global $settings, $arcadeModSettings, $context, $boarddir;

	$covericon = $settings['default_images_url'] . '/arc_icons/' . $coverfile;
	if (!empty($cover_icon) && $cover_icon != $coverfile && file_exists($gameDir . '/' . $cover_icon)) {
		$covericon = $gameUrl . '/' . $cover_icon;
	}
	elseif (empty($thumbnail) || $thumbnail == $settings['default_images_url'] . '/arc_icons/Default.gif') {
		$covericon = $settings['default_images_url'] . '/arc_icons/' . $coverfile;
	}
	else {
		$covericon = $thumbnail;
	}


	return $covericon;
}

function arcade_is_gd_image($path)
{
	if (!is_dir($path) && file_exists($path)) {
		$supported = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];
		$type = exif_imagetype($path);
		if (in_array($type, $supported)) {
			return true;
		}
	}
	return false;
}

function ArcadeListImageEnhancement($imagickImages)
{
	global $arcadeModSettings, $boarddir, $boardurl, $scripturl, $context;

	if (@extension_loaded('imagick') && !empty($arcadeModSettings['arcadeFilterImagick']) && !empty($imagickImages) && empty($_SESSION['arcade_isMobile'])) {
		$valbytes = openssl_random_pseudo_bytes(16);
		$_SESSION['arcadeFilterImagick'] = bin2hex($valbytes);
		$bdir = str_replace('\\', '/', $boarddir);
		list($thumbPaths, $thumbUrls, $coverPaths, $coverUrls, $gameIDs) = array(array(), array(), array(), array(), array());
		// $gameImages[0]
		foreach ($imagickImages as $gameImages) {
			$thumbPaths[] = str_replace($boardurl, $bdir, $gameImages[1]);
			$thumbUrls[] = $gameImages[1];
			$coverPaths[] = str_replace($boardurl, $bdir, $gameImages[2]);
			$coverUrls[] = $gameImages[2];
			$gameIDs[] = $gameImages[0];
		}
		$dataThumbs = array(
			'imagePaths' => $thumbPaths,
			'imageUrls' => $thumbUrls,
			'gameIDs' => $gameIDs,
			'sessionid' => $_SESSION['arcadeFilterImagick']
		);
		$dataCovers = array(
			'imagePaths' => $coverPaths,
			'imageUrls' => $coverUrls,
			'gameIDs' => $gameIDs,
			'sessionid' => $_SESSION['arcadeFilterImagick']
		);
		$context['html_headers'] .= '
	<script>
		function arcade_post_data(imageType, rawData) {
			let i = 0;
			for(i=0;i<rawData.length;i++){
				if (imageType == "thumb") {
					$("#arcadeThumb_" + rawData[i].gameid).prop("src", rawData[i].gamesrc);
					if (rawData[i].gamesrc && (rawData[i].gamesrc.substr(0, 14) == "data:image/gif" || rawData[i].gamesrc.substr(-4) == ".gif")) {
						$("#arcadeThumb_" + rawData[i].gameid).css("filter", "brightness(85%) contrast(115%)");
						$("#arcadeThumb_" + rawData[i].gameid).css("image-rendering", "pixelated");
					}
					else {
						$("#arcadeThumb_" + rawData[i].gameid).css("image-rendering", "high-quality");
					}
				}
				else {
					$("#arcadeCover_" + rawData[i].gameid).prop("src", rawData[i].gamesrc);
					$("#arcadeCoverFull_" + rawData[i].gameid).css("background-image", \'url(\' + rawData[i].gamesrc + \')\');
					if (rawData[i].gamesrc && (rawData[i].gamesrc.substr(0, 14) == "data:image/gif" || rawData[i].gamesrc.substr(-4) == ".gif")) {
						$("#arcadeCover_" + rawData[i].gameid).css("filter", "brightness(105%)");
						$("#arcadeCoverFull_" + rawData[i].gameid).css("background", "linear-gradient(rgba(255,255,255,0.5), rgba(255,255,255,0.5))");
						$("#arcadeCoverFull_" + rawData[i].gameid).css("background-repeat", "no-repeat");
						$("#arcadeCoverFull_" + rawData[i].gameid).css("background-size", "100% 100%");
						$("#arcadeCoverFull_" + rawData[i].gameid).css("image-rendering", "high-quality");
						$("#arcadeCover_" + rawData[i].gameid).css("image-rendering", "pixelated");
					}
					else {
						$("#arcadeCover_" + rawData[i].gameid).css("image-rendering", "crisp-edges");
						$("#arcadeCoverFull_" + rawData[i].gameid).css("image-rendering", "crisp-edges");
					}
					$("#arcadeCoverFull_" + rawData[i].gameid).css("backdrop-filter", "brightness(65%) contrast(65%)");
				}
			}
		}
		$(document).ready(function(){
			$.post("' . $scripturl . '?action=arcade;sa=imageenhancement;' . $context['session_var'] . '=' . $context['session_id'] . ';",
				' . json_encode($dataThumbs) . ',
				function(data, status){
					arcade_post_data("thumb", $.parseJSON(data));
				}
			);
			$.post("' . $scripturl . '?action=arcade;sa=imageenhancement;' . $context['session_var'] . '=' . $context['session_id'] . ';",
				' . json_encode($dataCovers) . ',
				function(data, status){
					arcade_post_data("cover", $.parseJSON(data));
				}
			);
		});
	</script>';
	}
	return false;
}

function ArcadeListImageEnhance()
{
	global $arcadeModSettings, $boarddir, $boardurl, $txt;

	//checkSession('post');
	loadLanguage('Arcade');
	$bdir = str_replace('\\', '/', $boarddir);
	$imagePaths = !empty($_POST['imagePaths']) && !is_array($_POST['imagePaths']) ? array($_POST['imagePaths']) : (!empty($_POST['imagePaths']) ? $_POST['imagePaths'] : array());
	$images = !empty($_POST['imageUrls']) && !is_array($_POST['imageUrls']) ? array($_POST['imageUrls']) : (!empty($_POST['imageUrls']) ? $_POST['imageUrls'] : array());
	$gameIDs = !empty($_POST['gameIDs']) && !is_array($_POST['gameIDs']) ? array($_POST['gameIDs']) : (!empty($_POST['gameIDs']) ? $_POST['gameIDs'] : array());
	$sessionId = !empty($_POST['sessionid']) && is_string($_POST['sessionid']) ? $_POST['sessionid'] : '';
	$rand = 'temp_' . $sessionId . '_';
	$arcadeModSettings['arcadeFilterImagick'] = !empty($arcadeModSettings['arcadeFilterImagick']) ? (int)$arcadeModSettings['arcadeFilterImagick'] : 0;
	if (!empty($_SESSION['arcadeFilterImagick']) && $sessionId == $_SESSION['arcadeFilterImagick']) {
		foreach ($imagePaths as $key => $imagePath) {
			// simple way to check for valid gd image
			$imageString = !empty($imagePath) ? arcade_is_gd_image($imagePath) : '';
			$gameid = $gameIDs[$key];
			list($valid, $imageData) = array(false, '');
			// enhance with imagick if available
			// ArcadeCheckMimeType($imagePath) | arcade_is_gd_image($imagePath)
			if (!empty($arcadeModSettings['arcadeFilterImagick']) && !empty($imagePath) && is_string($imagePath) && !empty($imageString)) {
				if (@extension_loaded('imagick')) {
					$ext = pathinfo($imagePath, PATHINFO_EXTENSION) == 'gif' ? 'gif' : 'jpg';
					$newFile = $boarddir . '/arcade/arcade_temp/' . $rand . pathinfo($imagePath, PATHINFO_FILENAME) . '.' . $ext;
					$newFile = str_replace('\\', '/', $newFile);
					try {
						$imagick = new \Imagick();
					} catch(ImagickException $e) {
						$error = true;
					}
					if (empty($error)) {
						//error_log(rand(1,9999) . '~ imagick success!');
						$imagick->setResolution(150, 150);
						$imagick->readImage($imagePath);
						//$valid = $imagick->valid();
						//$imagick->stripImage();
						clearstatcache(dirname($imagePath));
						clearstatcache(dirname($newFile));
						if ($ext == 'gif') {
							if ($arcadeModSettings['arcadeFilterImagick'] == 2) {
								$newFile = substr($newFile, 0, -3) . 'jpg';
								$imagick->setImageBackgroundColor(new ImagickPixel('white'));
								$imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
								$imagick->setImageFormat("jpeg");
								$imagick->stripImage();
							}
							else {
								$valid = true;
								$imagick->clear();
								$imagick->destroy();
								$images[] = array('gameid' => $gameid, 'gamesrc' => $images[$key]); // str_replace($bdir, $boardurl, $imagePath)
							}
							// WIP : gif images end up flat with this process
							/*
							$imagick->setImageFormat('gif');
							$imagick = $imagick->coalesceImages();
							foreach ($imagick as $frame) {
								$frame->enhanceImage();
							}
							clearstatcache(dirname($newFile));
							$imagick->getImagesBlob();
							$valid = $imagick->valid();
							$imagick->writeImages($newFile, true);
							$imagick->clear();
							$imagick->destroy();
							clearstatcache(dirname($newFile));
							if (file_exists($newFile)) {
								@chmod($newFile, 0644);
								if ($new = @imagecreatefromgif($newFile)) {
									ob_start();
									@imagegif($new);
									$imageData = ob_get_clean();
									@imagedestroy($new);
								}
								if (!empty($imageData)) {
									$images[] = array('gameid' => $gameid, 'gamesrc' => "data:image/gif;base64," . base64_encode($imageData));
								}
								else {
									$valid = false;
									$newFile = substr($newFile, 0, -3) . 'jpg';
									$imagick->setImageBackgroundColor(new ImagickPixel('white'));
									$imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
									$imagick->setImageFormat("jpeg");
									$imagick->stripImage();
								}
								@unlink($newFile);
							}
							elseif (!empty($arcadeModSettings['arcade_log_imagick'])) {
								// bug tracking
								$error = sprintf($txt['arcade_imagick_enhance_error'], $newFile);
								log_error($error);
							}
							*/
						}
						if (empty($valid)) {
							$imagick->setImageFormat('jpeg');
							$imagick->setCompressionQuality(100);
							$imagick->adaptiveSharpenImage(10, 2);
							$imagick->enhanceImage();
							$imagick->brightnessContrastImage(2,5);
							$imagick->getImageBlob();
							$valid = $imagick->valid();
							$imagick->writeImage($newFile);
							$imagick->clear();
							$imagick->destroy();
							clearstatcache(dirname($newFile));
							if (file_exists($newFile)) {
								@chmod($newFile, 0644);
								if ($new = @imagecreatefromjpeg($newFile)) {
									ob_start();
									@imagejpeg($new);
									$imageData = ob_get_clean();
									@imagedestroy($new);
								}
								if (!empty($imageData)) {
									$images[] = array('gameid' => $gameid, 'gamesrc' => "data:image/jpg;base64," . base64_encode($imageData));
								}
								else {
									$valid = false;
								}
								@unlink($newFile);
							}
							elseif (!empty($arcadeModSettings['arcade_log_imagick'])) {
								// bug tracking
								$error = sprintf($txt['arcade_imagick_enhance_error'], $newFile);
								log_error($error);
							}
						}
					}
				}
			}
		}
		// error_log('test: ' . json_encode($images));
		exit(json_encode($images));
	}

	fatal_lang_error('no_access', false);
}

function ArcadeCheckMimeType($imgPath)
{
	global $boarddir, $boardurl;
	if (!file_exists($imgPath)) {
		return false;
	}
	$mimeType = mime_content_type($imgPath);

	$allowedMimeTypes = [
		'image/gif',
		'image/jpeg',
		'image/jpg',
		'image/png'
	];

	if (file_exists($imgPath) && !empty($mimeType) && in_array($mimeType, $allowedMimeTypes) == false) {
		return false;
	}

	return true;
}

?>