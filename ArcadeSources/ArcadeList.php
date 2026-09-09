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

function ArcadeList($rom = 0)
{
 	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $user_info, $smcFunc, $boarddir, $settings;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	$context['current_arcade_sa'] = 'list';
	require_once($boarddir . '/ArcadeSources/Subs-ArcadePlus.php');
	require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	if (empty($user_info['arcade_settings'])) {
		$user_info['arcade_settings'] = loadMyArcadeSettings($user_info['id']);
	}
	list($arcadeList, $arcadeMobileList) = array($user_info['arcade_settings']['list' . $rom2], $user_info['arcade_settings']['list_mobile']);
	$imagickImages = array();
	$customListSettings = !empty($user_info['arcade_settings']['custom_list']) ? $user_info['arcade_settings']['custom_list'] : '';
	$customMobileListSettings = !empty($user_info['arcade_settings']['custom_list_mobile']) ? $user_info['arcade_settings']['custom_list_mobile'] : '';

	// Sorting methods
	$sort_methods = array(
		'reset' => 'game.id_game',
		'age' => 'game.id_game',
		'a2z' => 'game.game_name',
		'z2a' => 'game.game_name',
		'savetype' => 'game.submit_system',
		'plays' => 'game.num_plays',
		'plays_reverse' => 'game.num_plays',
		'champion' => 'mem.real_name',
		'myscore' => !$user_info['is_guest'] ? 'IFNULL(pb.score, 0)' : 'score.score',
		'rating' => 'game.game_rating',
		'champs' => 'score.champion_from',
		'favorites' => 'favorite.id_game',
		'cats' => 'category.cat_name',
	);

	$sort_methods2 = array(
		'reset' => 'id',
		'age' => 'id',
		'a2z' => 'name',
		'z2a' => 'name',
		'savetype' => 'submit_system',
		'plays' => 'plays',
		'plays_reverse' => 'plays',
		'champion' => 'champion_name',
		'myscore' => !$user_info['is_guest'] ? 'champion_score' : 'personal_best_score',
		'rating' => 'rating2',
		'champs' => 'champion_time',
		'favorites' => 'is_favorite',
		'cats' => 'category_name',
	);

	$sort_direction = array(
		'reset' => 'desc',
		'age' => 'desc',
		'a2z' => 'asc',
		'z2a' => 'desc',
		'savetype' => 'asc',
		'plays' => 'desc',
		'plays_reverse' => 'asc',
		'champion' => 'desc',
		'myscore' => 'desc',
		'rating' => 'desc',
		'champs' => 'desc',
		'favorites' => 'desc',
		'cats' => 'asc',
	);

	$sortbydefault = !empty($arcadeModSettings['arcadeListSort']) ? intval($arcadeModSettings['arcadeListSort']) : 0;
	$sortbydefault = isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'reset' ? 3 : $sortbydefault;
	$arcadeSortBy = function() use ($sortbydefault){
		switch($sortbydefault) {
			case 0:
				$sort = array('a2z', 'asc');
				break;
			case 1:
				$sort = array('ztoa', 'desc');
				break;
			case 2:
				$sort = array('age', 'asc');
				break;
			case 3:
				$sort = array('age', 'desc');
				break;
			case 4:
				$sort = array('plays', 'asc');
				break;
			case 5:
				$sort = array('plays_reverse', 'desc');
				break;
			case 6:
				$sort = array('champion', 'asc');
				break;
			case 7:
				$sort = array('champs', 'asc');
				break;
			case 8:
				$sort = array('rating', 'asc');
				break;
			case 9:
				$sort = array('favorites', 'asc');
				break;
			default:
				$sort = array('a2z', 'asc');
		}
		return $sort;
	};
	$arcadeSortByVal = $arcadeSortBy();

	if (isset($_REQUEST['category']) && floatval($_REQUEST['category']) != 0)
	{
		$_SESSION['arcade_nocats'. $rom2] = '';
		$_REQUEST['gametype'] = 'all';
		$user_info['arcade_settings']['arcade_gametype' . $rom2] = '';
		$catOverride = true;
	}

	// the mess of variables to set...
	$startX = isset($_REQUEST['start']) ? preg_replace("/[^0-9.]/", "", $_REQUEST['start']) : 0;
	$_SESSION['arcade_game_start'. $rom2] = abs(intval($startX));
	if (!isset($_REQUEST['sortby']) && isset($_REQUEST['category']) && is_string($_REQUEST['category']) && (int)$_REQUEST['category'] == 0) {
		list($_SESSION['arcade_nocats'. $rom2], $context['sort_direction'], $_SESSION['arcade_sort_dir'. $rom2], $_REQUEST['sortby'], $_SESSION['arcade_gamesearch'. $rom2]) = array('no', 'desc', 'desc', 'age', array());
	}
	if (isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'nocat') {
		list($_SESSION['arcade_nocats'. $rom2], $context['sort_direction'], $_SESSION['arcade_sort_dir'. $rom2], $_REQUEST['sortby'], $_SESSION['arcade_gamesearch'. $rom2]) = array('yes', '', '', '', array());
		$_SESSION['arcade_nocats'. $rom2] = '';
		$_REQUEST['gametype'] = 'all';
		$_REQUEST['category'] = 'nocat';
		$user_info['arcade_settings']['arcade_gametype' . $rom2] = '';
		$catOverride = true;
	}
	if (isset($_REQUEST['sortby']) && in_array($_REQUEST['sortby'], array_keys($sort_methods)))
		$_SESSION['arcade_nocats'. $rom2] = '';
	elseif (isset($_REQUEST['default']) || (isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'reset'))
	{
		if (!$user_info['is_guest'])
		{
			ArcadeSelectGameType(array(0));
			$user_info['arcade_settings']['arcade_gametype' . $rom2] = 0;
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_members
				SET arcade_gametype' . $rom2 . ' = {int:gtype}
				WHERE id_member = {int:memberid}',
				array(
					'memberid' => $user_info['id'],
					'gtype' => 0,
				)
			);
		}
		unset($_SESSION['arcade_sortby'. $rom2], $_SESSION['arcade_sort_dir'. $rom2], $_REQUEST['sortby'], $context['sort_by']);
		list($_SESSION['arcade_nocats'. $rom2], $context['sort_direction'], $context['sort_direction'], $_SESSION['arcade_sort_dir'. $rom2], $_REQUEST['dir'], $_REQUEST['sortby'], $context['sort_by'], $_SESSION['arcade_gametype_select' . $rom], $_REQUEST['gametype'], $context['arcade_category' . $rom2], $_REQUEST['category'], $_SESSION['current_cat'. $rom2], $_SESSION['arcade_gamesearch'. $rom2], $sortby, $subsystem, $ascending) = array('yes', $arcadeSortByVal[1], $arcadeSortByVal[1], $arcadeSortByVal[1], $arcadeSortByVal[1], $arcadeSortByVal[0], $arcadeSortByVal[0], 'all', 'all', 'all', '', '', array(), 'id_game', '', 'DESC');
	}
	else
		$_SESSION['arcade_nocats'. $rom2] = isset($_SESSION['arcade_nocats'. $rom2]) && $_SESSION['arcade_nocats'. $rom2] == 'yes' ? $_SESSION['arcade_nocats'. $rom2] : '';

	if (!empty($_SESSION['arcade_gamesearch'. $rom2]) && isset($_REQUEST['sa']) && isset($_REQUEST['dir']) && $_REQUEST['sa'] == 'list')
		$_REQUEST['sa'] = 'search';

	$listAction = empty($rom) ? 'arcade' : 'retro_arch';
	$_SESSION['arcade_isMobile'] = empty($user_info['arcade_settings']['detect_mobile']) && !empty($_SESSION['arcade_isMobile']) ? $_SESSION['arcade_isMobile'] : 0;
	$skinAlt = !empty($arcadeModSettings['arcadeSkinAlt']) ? explode('|', $arcadeModSettings['arcadeSkinAlt']) : array();
	$context['arcadeSkinAlt'] = !empty($skinAlt) && in_array($settings['theme_id'], $skinAlt) ? true : false;
	$context['arcadeGameIconSizeB'] = !empty($context['arcadeSkinAlt']) ? '50' : '70';
	$gameTypes = empty($rom) ? explode('|', $txt['arcade_select_gametype']) : (array_keys($txt['arcade_select_gametype_rom']));
	$_SESSION['arcade_gametype_select' . $rom] = !empty($_SESSION['arcade_gametype_select' . $rom]) ? $_SESSION['arcade_gametype_select' . $rom] : '';
	$sortGametype = !empty($user_info['arcade_settings']['arcade_gametype' . $rom2]) && !empty($gameTypes[$user_info['arcade_settings']['arcade_gametype' . $rom2]]) ? $gameTypes[$user_info['arcade_settings']['arcade_gametype' . $rom2]] : '';
	$sortGametype = !empty($_SESSION['arcade_gametype_select' . $rom]) && in_array($_SESSION['arcade_gametype_select' . $rom], $gameTypes) && $_SESSION['arcade_gametype_select' . $rom] != 'all' ? $_SESSION['arcade_gametype_select' . $rom] : $sortGametype;
	$sortGametype = ($sortGametype == 'all' || $_SESSION['arcade_gametype_select' . $rom] == 'all') ? '' : $sortGametype;
	$sortGametype = !empty($catOverride) ? '' : $sortGametype;
	if (isset($_REQUEST['gametype']) && is_string($_REQUEST['gametype'])) {
		if (in_array($_REQUEST['gametype'], $gameTypes)) {
			$sortGametype = $_REQUEST['gametype'] != 'all' ? $_REQUEST['gametype'] : '';
		}
	}
	$_SESSION['arcade']['gamepopup'] = false;
	$_SESSION['arcade']['pop'] = false;
	$_SESSION['arcade_sortby'. $rom2] = !empty($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'reset' ? $arcadeSortByVal[0] : (!empty($_SESSION['arcade_sortby'. $rom2]) ? $_SESSION['arcade_sortby'. $rom2] : '');
	$context['arcade_category' . $rom2] = !empty($_REQUEST['category']) ? ArcadeSpecialChars($_REQUEST['category'], 'name') : (!empty($_SESSION['current_cat'. $rom2]) ? ArcadeSpecialChars($_SESSION['current_cat'. $rom2], 'name') : 'all');
	$context['arcade_category' . $rom2] = (!empty($_REQUEST['sortby'])) && $_REQUEST['sortby'] == 'reset' ? 0 : $context['arcade_category' . $rom2];
	$_REQUEST['sortby'] = isset($_REQUEST['sortby']) ? ArcadeSpecialChars($_REQUEST['sortby'], 'name') : (!empty($_SESSION['arcade_sortby'. $rom2]) ? ArcadeSpecialChars($_SESSION['arcade_sortby'. $rom2], 'name') : 'a2z');
	$context['sort_by'] = !empty($sort_methods[$_REQUEST['sortby']]) ? $_REQUEST['sortby'] : (!empty($_SESSION['arcade_sortby'. $rom2]) ? $_SESSION['arcade_sortby'. $rom2] : 'a2z');
	switch ($context['sort_by'])
	{
		case 'a2z':
			if (empty($_SESSION['arcade_isMobile']))
				$_REQUEST['dir'] = 'asc';
			else
			{
				$gameListDir = isset($_REQUEST['dir']) && is_string($_REQUEST['dir']) ? mb_strtolower($_REQUEST['dir']) : '';
				$_REQUEST['dir'] = in_array($gameListDir, array('asc', 'desc')) ? $gameListDir : 'asc';
			}
			break;
		case 'z2a':
			$_REQUEST['dir'] = 'desc';
			break;
		case 'plays':
			$_REQUEST['dir'] = 'desc';
			break;
		case 'plays_reverse':
			$_REQUEST['dir'] = 'asc';
			break;
		default:
			$_REQUEST['dir'] = isset($_REQUEST['dir']) && in_array(mb_strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')), array('asc', 'desc')) ? $_REQUEST['dir'] : '';
	}

	$context['sort_direction'] = !empty($sort_direction[$context['sort_by']]) ? $sort_direction[$context['sort_by']] : (!empty($_SESSION['arcade_sort_dir'. $rom2]) ? $_SESSION['arcade_sort_dir'. $rom2] : 'asc');
	$context['sort_direction'] = (isset($_REQUEST['dir'])) && in_array(strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')), array('asc', 'desc')) ? strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')) : $context['sort_direction'];
	$_SESSION['arcadeSessionDir'. $rom2] = (isset($_REQUEST['dir'])) && in_array(strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')), array('asc', 'desc')) ? strtolower(ArcadeSpecialChars($_REQUEST['dir'], 'name')) : (!empty($_SESSION['arcade_sort_dir'. $rom2]) ? $_SESSION['arcade_sort_dir'. $rom2] : $context['sort_direction']);
	$_SESSION['arcade_sort_dir'. $rom2] = $context['sort_direction'];
	$context['sort_link'] = $context['sort_direction'] == 'asc' ? $scripturl . '?action=' . $listAction . ';sa=list;sortby=' . str_replace('a2z', 'z2a', $context['sort_by']) . ';dir=desc;#arctoplist' : $scripturl . '?action=' . $listAction . ';sa=list;sortby=' . str_replace('z2a', 'a2z', $context['sort_by']) . ';dir=asc;#arctoplist';
	$context['changedir'] = $context['sort_direction'] == 'desc' ? ';dir=asc' : ';dir=desc';
	$context['sort_arrow'] = '<span title="' . $txt['arcade_list_sort'] . '" class="floatleft">&nbsp;<a href="' . $context['sort_link'] . '"><img style="vertical-align: middle;" class="icon" src="' . $settings['default_images_url'] . '/arc_icons/' . ($context['sort_direction'] == 'desc' ? 'sort_up.gif' : 'sort_down.gif') . '" alt="" /></a></span>';
	$arcadeModSettings['arcadeViewCovers'] = !empty($arcadeModSettings['arcadeViewCovers']) ? intval($arcadeModSettings['arcadeViewCovers']) : 0;
	switch($arcadeModSettings['arcadeViewCovers']){
		case 1:
			if (!empty($rom)) {
				$context['show_cover'] = true;
			}
			break;
		case 2:
			if (empty($rom)) {
				$context['show_cover'] = true;
			}
			break;
		case 3:
			$context['show_cover'] = true;
			break;
		default:
			$context['show_cover'] = false;
	}

	list($context['arcade']['games'], $search, $checkDirs, $_SESSION['arcade_sortby'. $rom2], $_SESSION['current_cat'. $rom2], $sortby, $ascending, $select_rows, $select_tables, $where, $subsystem, $gameCount) = array(array(), array(), array(), $context['sort_by'], $context['arcade_category' . $rom2], $sort_methods[$context['sort_by']], $context['sort_direction'], '', '', '', '', 0);
	if (!$user_info['is_guest'])
	{
		$select_rows = ',
			IFNULL(pb.id_score, 0) AS id_pb, IFNULL(pb.score, 0) AS personal_best, IFNULL(favorite.id_favorite, 0) AS is_favorite';

		$select_tables = (isset($_REQUEST['favorites']) ? 'INNER JOIN' : 'LEFT JOIN') . ' {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game
				AND favorite.id_member = {int:member})
			LEFT JOIN {db_prefix}arcade_scores AS pb ON (pb.id_game = game.id_game
				AND pb.id_member = {int:member} AND pb.personal_best = 1)';
		$guest = '';
	}
	else
		$guest = 'LEFT JOIN {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game)';
	$baseurl = $scripturl . '?action=' . $listAction;

	$context['arcade_search'] = array();
	$search = isset($_SESSION['arcade_gamesearch'. $rom2]) ? $_SESSION['arcade_gamesearch'. $rom2] : array();
	$_REQUEST['name'] = isset($_REQUEST['name']) ? mb_ereg_replace('[^0-9,a-z,A-Z$_ ]', '_', $_REQUEST['name']) : '';
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'search')
	{
		$baseurl .= ';sa=search';

		if (!empty($_REQUEST['name']))
		{
			$baseurl .= ';name=' . urlencode($_REQUEST['name']);
			$context['arcade_search']['name'] = $_REQUEST['name'];

			//$where .= ' AND game.game_name LIKE {string:name}';
			// AND concat(title,body) REGEXP "{raw:search}"
			$where .= ' AND concat(game.internal_name,game.game_name) REGEXP "{raw:search}"';
			if (isset($_REQUEST['name']))
			{
				$search = ArcadeSpecialChars($_REQUEST['name'], 'name');
				$search = mb_ereg_replace("'[^[:alnum:],]*,[^[:alnum:]]*|[\s,]+'", ",", $search);
				$search = mb_ereg_replace('\s+', '', $search);
				$search = mb_ereg_replace(',+', ',', $search);
				$search = explode(',', $search);
				$_SESSION['arcade_gamesearch'. $rom2] = $search;
			}
		}
		elseif (!empty($_SESSION['arcade_gamesearch'. $rom2]))
			$search = $_SESSION['arcade_gamesearch'. $rom2];
		else
			list($_SESSION['arcade_gamesearch'. $rom2], $search) = array(array(), array());
	}
	else
	{
		list($_SESSION['arcade_gamesearch'. $rom2], $search) = array(array(), array());
	}

	if ($context['arcade_category' . $rom2] !== 'all')
	{
		$where .= ' AND game.id_cat = {int:category}';
		if (isset($_REQUEST['sa']) && !empty($search[0]) && $_REQUEST['sa'] == 'search' && mb_strlen($search[0]) == 1)
		{
			$where .= " AND LOWER(game.game_name) LIKE '" . mb_strtolower($search[0]) . "%'";
			if (!ctype_upper($search[0]))
				$where .= " OR LOWER(game.game_name) LIKE '%" . mb_strtolower($search[0]) . "%'";
			$sort = !empty($sort) ? $sort . ", game.game_name" : "game.game_name";
		}
	}
	elseif (isset($_REQUEST['sa']) && !empty($search[0]) && $_REQUEST['sa'] == 'search' && mb_strlen($search[0]) == 1)
	{
		$where .= " AND LOWER(game.game_name) LIKE '" . mb_strtolower($search[0]) . "%'";
		if (!ctype_upper($search[0]))
			$where .= " OR LOWER(game.game_name) LIKE '%" . mb_strtolower($search[0]) . "%'";
		$sort = !empty($sort) ? $sort . ", game.game_name" : "game.game_name";
	}
	else
		$where .= " AND game.game_name LIKE '%'";

	if (isset($_REQUEST['sortby']))
		$baseurl .=  ';sort=' . $context['sort_by'];
	if (isset($_REQUEST['desc']))
		$baseurl .=  ';dir=desc';
	if (isset($_REQUEST['favorites']))
	{
		$baseurl .=  ';favorites';
		$context['arcade_search']['favorites'] = true;
	}
	if (isset($_REQUEST['category']))
		$baseurl .= ';category=' . arcade_mb_escape($_REQUEST['category']);

	if (empty($arcadeModSettings['arcadeTypeQuery']) && allowedTo('arcade_gametype_select'))
	{
		$where = !empty($sortGametype) ? $where . ' AND ' . (!empty($rom) ? 'rom' : 'submit') . '_system = {string:gametype}' : $where;
		$subsystem = '';
	}
	elseif (allowedTo('arcade_gametype_select') && empty($catOverride))
		$subsystem = !empty($sortGametype) ? ' case when submit_system in ("' . $sortGametype . '") then -1 else submit_system end, ' . $sortby . ($ascending == 'asc' ? ' ASC' : ' DESC') : '';

	if ($_SESSION['arcade_nocats'. $rom2] == 'yes')
		$where .= ' AND game.id_cat = 0';

	$where .= ' AND game.enabled = 1';
	$gameCount = 0;
	$result = $smcFunc['db_query']('', '
		SELECT game.id_cat, game.id_game, game.game_name, game.internal_name, game.member_groups, game.enabled, game.rom_flag
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)'. (isset($_REQUEST['favorites']) ? '
			INNER JOIN {db_prefix}arcade_favorite AS favorite ON (favorite.id_game = game.id_game
				AND favorite.id_member = {int:member})' : '') . '
		WHERE {raw:query_see_game}' . $where . $whererom . '
		ORDER BY game.id_game, game.id_cat, game.game_name, game.internal_name, game.member_groups, game.enabled',
		array(
			'name' => isset($_REQUEST['name']) ? '%' . arcade_mb_escape($_REQUEST['name']) . '%' : '',
			'member' => $user_info['id'],
			'category' => intval($context['arcade_category' . $rom2]),
			'query_see_game' => !empty($user_info['query_see_game']) ? $user_info['query_see_game'] : 'game.id_game > 0',
			'gametype' => !empty($sortGametype) ? $sortGametype : '',
			'search' => is_array($search) ? implode('|', $search) : '',
			'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$groupsAllowed = explode(',', $row['member_groups']);
		list($matches1, $matches2) = array(array(), '');
		$allowedCheck = array_intersect($user_info['groups'], $groupsAllowed);

		//if (empty($user_info['is_admin']) && !empty($groupsAllowed) && (count($allowedCheck) == 0))
			//continue;

		$gameCount++;
	}
	$smcFunc['db_free_result']($result);

	$gameCount = (int)$gameCount;
	// round up if necessary for the Vintage list
	if (empty($_SESSION['arcade_isMobile']) && !empty($arcadeList) && $arcadeList == 3) {
		$round = !empty($arcadeModSettings['gamesPerRowVintage']) ? intval($arcadeModSettings['gamesPerRowVintage']) : 4;
		$value = (round($context['games_per_page'] / $round, 0)) * $round;
		$context['games_per_page'] = (ceil($value / $round)) * $round;

	}
	$context['page_index'] = !empty($context['games_per_page']) ? constructPageIndex($baseurl, $_REQUEST['start'], $gameCount , $context['games_per_page'], false) : '';
	$context['page_index'] = preg_replace('~href=("|\')(.+?)\1~', 'href=$1$2;#arctoplist$1', $context['page_index']);
	$context['arcade']['games'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT
			game.id_game, game.game_name, game.description, game.game_rating, game.num_plays, pdl.download_count, pdl.report_id, pdl.report_reason, game.extra_data, game.game_file, game.internal_name, game.enabled, game.rom_system,
			game.score_type, game.thumbnail, game.game_directory, game.id_topic, score.champion_from, game.id_cat, game.submit_system, category.cat_name, category.cat_icon, game.member_groups, game.download, game.rom_flag,
			game.cover_icon, game.thumbnail_small, game.help, IFNULL(s1.id_member, 0) AS id_member_first, IFNULL(s2.id_member, 0) AS id_member_second, IFNULL(s3.id_member,0) AS id_member_third, category.cat_dl, category.cat_js,
			IFNULL(m1.real_name, 0) AS real_name1,
			IFNULL(m2.real_name, 0) AS real_name2,
			IFNULL(m3.real_name, 0) AS real_name3,
			IFNULL(s1.score, 0) AS gold_score,
			IFNULL(s2.score, 0) AS silver_score,
			IFNULL(s3.score, 0) AS bronze_score,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(score.id_score, 0) AS id_score,
			IFNULL(score.score, 0) AS champ_score, IFNULL(mem.real_name, score.player_name) AS real_name,
			IFNULL(score.end_time, 0) AS champion_time, IFNULL(category.id_cat, 0) AS id_cat,
			IFNULL(category.cat_name, {string:empty_string}) AS cat_name' . $select_rows . '
		FROM {db_prefix}arcade_games AS game
			' . $guest . '
			LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = game.id_champion_score)
			LEFT JOIN {db_prefix}arcade_scores AS s1 ON (s1.id_game = game.id_game AND s1.position = {int:first})
			LEFT JOIN {db_prefix}arcade_scores AS s2 ON (s2.id_game = game.id_game AND s2.position = {int:second})
			LEFT JOIN {db_prefix}arcade_scores AS s3 ON (s3.id_game = game.id_game AND s3.position = {int:third})
			LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = s1.id_member)
			LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = s2.id_member)
			LEFT JOIN {db_prefix}members AS m3 ON (m3.id_member = s3.id_member)
			LEFT JOIN {db_prefix}arcade_pdl2 AS pdl ON (pdl.pdl_gameid = game.id_game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)' . $select_tables . '
		WHERE {raw:query_see_game}' . $where . $whererom . '
		ORDER BY {raw:sort}
		LIMIT {int:limit}, {int:games_per_page}',
		array(
			'empty_string' => '',
			'name' => isset($_REQUEST['name']) ? arcade_mb_escape($_REQUEST['name']) . '%' : '',
			'sort' => !empty($subsystem) ? $subsystem : $sortby . ($ascending == 'asc' ? ' ASC' : ' DESC'),
			'gametype' => !empty($sortGametype) ? $sortGametype : '',
			'limit' => intval($_REQUEST['start']),
			'games_per_page' => $context['games_per_page'],
			'member' => $user_info['id'],
			'category' => intval($context['arcade_category' . $rom2]),
			'query_see_game' => $user_info['query_see_game'],
			'search' => is_array($search) ? implode('|', $search) : '',
			'first' => 1,
			'second' => 2,
			'third' => 3,
			'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$groupsAllowed = explode(',', $row['member_groups']);
		list($matches1, $matches2) = array(array(), '');
		$allowedCheck = array_intersect($user_info['groups'], $groupsAllowed);
		$listAction = empty($row['rom_flag']) ? 'arcade' : 'retro_arch';
		$main = empty($row['rom_flag']) ? $gamesDirectory : $romGamesDirectory;
		$gameDir = !empty($row['game_directory']) ? $main . '/' . $row['game_directory'] : $main;
		$romMgr = !empty($row['rom_flag']) ? 'rom' : '';
		//if (empty($user_info['is_admin']) && !empty($groupsAllowed) && (count($allowedCheck) == 0))
			//continue;

		if (!in_array($row['id_game'], $checkDirs))
		{
			$extra = !empty($row['extra_data']) && arcadeIsSerialized($row['extra_data']) ? arcade_safe_unserialize($row['extra_data']) : array();
			$extra['height'] = !empty($extra['height']) ? (int)$extra['height'] : 600;
			$extra['width'] = !empty($extra['width']) ? (int)$extra['width'] : 800;
			$downlink = !empty($extra['external_link']) && strlen(basename($extra['external_link']) > 4) && substr(basename($extra['external_link']), 0, 4) == 'rom_' ? $extra['external_link'] : $scripturl . '?action=' . $listAction . ';sa=download;game=' . $row['id_game'];
			$allowedToDownload = allowedTo('arcade_download') && empty($row['rom_flag']) ? 1 : (allowedTo('arcade_download_rom') && !empty($row['rom_flag']) ? 1 : 0);
			if (empty($row['real_name']))
				$row['real_name'] = $txt['guest'];

			if (!empty($row['game_directory']))
				$gameUrl = empty($row['rom_flag']) ? $arcadeModSettings['gamesUrl'] . '/' . $row['game_directory'] : $arcadeModSettings['romGamesUrl'] . '/' . $row['game_directory'];
			else
				$gameUrl = empty($row['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];

			$row['rom_flag'] = !empty($row['rom_flag']) ? 1 : 0;
			$row['cover_icon'] = !empty($row['cover_icon']) ? $row['cover_icon'] : '';
			$thumbnail = !is_dir($gameDir . '/' . $row['thumbnail']) && file_exists($gameDir . '/' . $row['thumbnail']) ? $gameUrl . '/' . $row['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$thumbnail_small = !is_dir($gameDir . '/' . $row['thumbnail_small']) && file_exists($gameDir . '/' . $row['thumbnail_small']) ? $gameUrl . '/' . $row['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$thumbnail = $thumbnail == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $thumbnail_small : $thumbnail;
			$thumbPath = str_replace($gameUrl, $gameDir, $thumbnail);
			$thumbnail_small = $thumbnail_small == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $thumbnail : $thumbnail_small;
			$coverfile = empty($row['rom_flag']) ? 'arcadegamecover.png' : 'romgamecover.gif';
			$covericon = ArcadeListCoverIcon($rom, $gameDir, $gameUrl, $row['cover_icon'], $coverfile, $thumbnail);
			$coverPath = str_replace($gameUrl, $gameDir, $covericon);
			$imagickImages[] = array($row['id_game'], $thumbnail, $covericon);

			$context['arcade']['games'][] = array(
				'id' => $row['id_game'],
				'game_file' => $row['game_file'],
				'url' => array(
					'play' => $scripturl . '?action=' . $listAction . ';sa=play;game=' . $row['id_game'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
					'popup' => $scripturl . '?action=' . $listAction . ';sa=play;game=' . $row['id_game'] . ';pop=1',
					'highscore' => $scripturl . '?action=' . $listAction . ';sa=highscore;game=' . $row['id_game'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3',
					'edit' => $scripturl . '?action=admin;area=manage' . $romMgr . 'games;sa=edit' . $romMgr . ';game=' . $row['id_game'],
					'download' => $downlink,
					'favorite' => $context['arcade']['can_favorite'] ? $row['is_favorite'] == 0 ? $scripturl . '?action=' . $listAction . ';sa=favorite;game=' . $row['id_game'] : $scripturl . '?action=' . $listAction . ';sa=favorite;remove;game=' . $row['id_game'] : '#',
				),
				'category' => array(
					'id' => $row['id_cat'],
					'name' => $row['cat_name'],
					'link' => $scripturl . '?action=' . $listAction . ';category=' . $row['id_cat'],
					'icon' => !empty($row['cat_icon']) ? $row['cat_icon'] : (file_exists($settings['default_theme_dir'] . '/images/arc_icons/' . ArcadeSpecialChars($row['cat_name'], 'image') . '.gif') ? ArcadeSpecialChars($row['cat_name'], 'image') . '.gif' : ''),
				),
				'name' => $row['game_name'],
				'internal_name' => $row['internal_name'],
				'description' => arcadeDecodeHtmlEnt(arcadeFilterVar($row['description'])),
				'help' => arcadeDecodeHtmlEnt(arcadeFilterVar($row['help'])),
				'plays' => $row['num_plays'],
				'is_champion' => $row['id_score'] > 0,
				'champion' => array(
					'member_id' => $row['id_member'],
					'member_name' => $row['real_name'],
					'score_id' => $row['id_score'],
					'member_link' =>  !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;area=arcadeStats;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $row['real_name'],
					'score' => comma_format($row['champ_score']),
					'time' => $row['champion_time'],
				),
				'second_place' => array(
					'member_id' => !empty($row['id_member_second']) ? $row['id_member_second'] : 0,
					'score_id' => !empty($game['id_score_second']) ? $game['id_score_second'] : 0,
					'member_link' =>  !empty($game['real_name2']) ? '<a href="' . $scripturl . '?action=profile;area=arcadeStats;u=' . $game['id_member_second'] . '">' . $game['real_name2'] . '</a>' : $txt['arcade_is_guest'],
					'score' => !empty($game['silver_score']) ? round($game['silver_score'], 3) : 0,
				),
				'third_place' => array(
					'member_id' => !empty($row['id_member_third']) ? $row['id_member_third'] : 0,
					'score_id' => !empty($game['id_score_third']) ? $game['id_score_third'] : 0,
					'member_link' =>  !empty($game['real_name3']) ? '<a href="' . $scripturl . '?action=profile;area=arcadeStats;u=' . $game['id_member_third'] . '">' . $game['real_name3'] . '</a>' : $txt['arcade_is_guest'],
					'score' => !empty($game['bronze_score']) ? round($game['bronze_score'], 3) : 0,
				),
				'is_personal_best' => !$user_info['is_guest'] && $row['id_pb'] > 0,
				'personal_best' => !$user_info['is_guest'] ? comma_format($row['personal_best']) : 0,
				'personal_best_score' => !$user_info['is_guest'] ? $row['personal_best'] : 0,
				'highscore_support' => $row['score_type'] != 2,
				'is_favorite' => $context['arcade']['can_favorite'] ? $row['is_favorite'] > 0 : false,
				'rating' => !empty($row['game_rating']) ? $row['game_rating'] : 0,
				'width' => $extra['width'],
				'height' => $extra['height'],
				'submit_system' => !empty($row['submit_system']) && empty($rom) ? $row['submit_system'] : (!empty($row['rom_system']) ? 'rom' : ''),
				'rom_system' => !empty($row['rom_system']) ? $row['rom_system'] : '',
				'pdl_count' => $row['download_count'],
				'report_id' => $row['report_id'],
				'download' => !empty($row['download']) && empty($row['cat_dl']) ? 1 : 0,
				'report_reason' => $row['report_reason'],
				'rating2' => round($row['game_rating']),
				'thumbnail' => $thumbnail,
				'thumbnail_small' => $thumbnail_small,
				'cover_icon' => $covericon,
				'show_cover' => !empty($context['show_cover']) ? true : false,
				'id_topic' => !empty($row['id_topic']) ? $row['id_topic'] : 0,
				'sort_by' => $context['sort_by'],
				'champion_score' => $row['champ_score'],
				'champion_name' => $row['real_name'],
				'champion_time' => $row['champion_time'],
				'category_name' => $row['cat_name'],
				'rom_game' => !empty($row['rom_flag']) ? 1 : 0,
				'allow_download' => $allowedToDownload,
			);
		}
		$checkDirs[] = $row['id_game'];
	}
	$smcFunc['db_free_result']($request);

	ArcadeListImageEnhancement($imagickImages);
	// refine the order of the page
	$sortName = array_column($context['arcade']['games'], $sort_methods2[$context['sort_by']]);
	switch ($context['sort_by'])
	{
		case 'a2z':
			if (empty($_SESSION['arcade_isMobile']))
				$direction = 'asc';
			else
			{
				$gameListDir = isset($_REQUEST['dir']) && is_string($_REQUEST['dir']) ? mb_strtolower($_REQUEST['dir']) : '';
				$direction = in_array($gameListDir, array('asc', 'desc')) ? $gameListDir : 'asc';
			}
			break;
		case 'z2a':
			$direction = 'desc';
			break;
		case 'plays':
			$direction = 'desc';
			break;
		case 'plays_reverse':
			$direction = 'asc';
			break;
		default:
			$direction = !empty($sortDirGames) && in_array($sortDirGames, array('asc', 'desc')) ? $sortDirGames : $_SESSION['arcadeSessionDir'. $rom2];
	}
	//$sortName = !empty($sortByGames) && in_array($sortByGames, array('age', 'name'))
	if ($direction == 'desc')
		array_multisort($sortName, SORT_DESC, $context['arcade']['games']);
	else
		array_multisort($sortName, SORT_ASC, $context['arcade']['games']);

	if (!empty($arcadeModSettings['arcadeShowInfoCenter']))
	{
		require_once($boarddir . '/ArcadeSources/ArcadeStats.php');
		$context['arcade']['latest_scores'] = ArcadeLatestScores(5, 0, $rom);
		$context['arcade_viewing'] = array();
		$context['arcade_num_viewing'] = array('member' => 0, 'guest' => 0, 'hidden' => 0);

		// log the current user to the online list & then search for members in the arcade within 10 minutes
		$log_online = arcade_online();
		$context['arcade_online'] = array($log_online[1], $log_online[2]);

		$request = $smcFunc['db_query']('', '
			SELECT
				id_member, online_time, show_online, online_name, online_color
			FROM {db_prefix}arcade_member_data
			WHERE {int:now} - online_time < 600',
			array(
				'now' => $log_online[0],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!empty($row['online_color']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['online_name'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['online_name'] . '</a>';

			$is_buddy = in_array($row['id_member'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			// Add them both to the list and to the more detailed list.
			if (!empty($row['show_online']) || allowedTo('moderate_forum'))
			{
				$context['arcade_num_viewing']['member']++;
				$context['arcade_viewing'][$row['online_time'] . $row['online_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
			}

			if (empty($row['show_online']))
				$context['arcade_num_viewing']['hidden']++;
		}
		$smcFunc['db_free_result']($request);

		krsort($context['arcade_viewing']);
	}

	// Layout
	if (allowedTo('arcade_online'))
		$context['arcade_online_link'] = '<a href="' . $scripturl . '?index.php;action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';sa=online">' . sprintf($txt['arcade_info_who'], $context['arcade_online'][0], $context['arcade_online'][1], empty($context['arcade_online'][0]) || $context['arcade_online'][0] > 1 ? 's' : '', empty($context['arcade_online'][1]) || $context['arcade_online'][1] > 1 ? 's' : '') . '</a>';
	else
		$context['arcade_online_link'] = sprintf($txt['arcade_info_who'], $context['arcade_online'][0], $context['arcade_online'][1], empty($context['arcade_online'][0]) || $context['arcade_online'][0] > 1 ? 's' : '', empty($context['arcade_online'][1]) || $context['arcade_online'][1] > 1 ? 's' : '');

	// Arcade lists
	if (!empty($_SESSION['arcade_isMobile']))
	{
		//ArcadeIconAdjustment();
		switch ($arcadeMobileList)
		{
			case 1:
				$context['html_headers'] .= '	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-mobile-generic-list.css?' . $suffixVersion . '" />';
				loadTemplate('ArcadeListMobileGeneric');
				$context['sub_template'] = 'arcade_list';
				$context['page_title'] = $txt['arcade_game_list'];
				break;
			case 2:
				$context['html_headers'] .= '	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-mobile-light-list.css?' . $suffixVersion . '" />';
				loadTemplate('ArcadeListMobileLight');
				$context['sub_template'] = 'arcade_list';
				$context['page_title'] = $txt['arcade_game_list'];
				break;
			default:
				if (!empty($customMobileListSettings) && !empty($customMobileListSettings['list_name']) && !empty($customMobileListSettings['list_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $customMobileListSettings['list_source_file']))
				{
					if (!function_exists($customMobileListSettings['list_function'])) {
						require_once($boarddir . '/ArcadeSources/' . $customMobileListSettings['list_source_file']);
						if (!empty($customMobileListSettings['lang_function']) && function_exists($customMobileListSettings['lang_function'])) {
							$customMobileListSettings['lang_function']();
						}
						if (!empty($customMobileListSettings['list_function']) && function_exists($customMobileListSettings['list_function'])) {
							$context['arcade_list'] = $customMobileListSettings['list_function']();
							loadTemplate($customMobileListSettings['list_template']);
							$context['sub_template'] = 'arcade_list';
							$context['page_title'] = $txt['arcade_game_list'];
						}
						else {
							loadTemplate('ArcadeListMobileLight');
							$context['sub_template'] = 'arcade_list';
							$context['page_title'] = $txt['arcade_game_list'];
						}
					}
					elseif (function_exists($customMobileListSettings['list_function'])) {
						if (!empty($customMobileListSettings['lang_function']) && function_exists($customMobileListSettings['lang_function'])) {
							$customMobileListSettings['lang_function']();
						}
						loadTemplate($customMobileListSettings['list_template']);
						$context['sub_template'] = 'arcade_list';
						$context['page_title'] = $txt['arcade_game_list'];
					}
					else {
						$context['html_headers'] .= '	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-mobile-light-list.css?' . $suffixVersion . '" />';
						loadTemplate('ArcadeListMobileLight');
						$context['sub_template'] = 'arcade_list';
						$context['page_title'] = $txt['arcade_game_list'];
					}
				}
				elseif (!empty($customMobileListSettings) && !empty($customMobileListSettings['list_name']) && !empty($customMobileListSettings['list_template'])) {
					loadTemplate($customMobileListSettings['list_template']);
					$context['sub_template'] = 'arcade_list';
					$context['page_title'] = $txt['arcade_game_list'];
				}
				else {
					$context['html_headers'] .= '	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-mobile-light-list.css?' . $suffixVersion . '" />';
					loadTemplate('ArcadeListMobileLight');
					$context['sub_template'] = 'arcade_list';
					$context['page_title'] = $txt['arcade_game_list'];
				}
		}
	}
	else
	{
		switch ($arcadeList)
		{
			case 3:
				ArcadeIconAdjustment($rom, 'Vintage');
				loadTemplate('ArcadeSkinListA');
				$context['sub_template'] = 'arcade_list';
				$context['page_title'] = $txt['arcade_game_list'];
				break;
			case 2:
				ArcadeIconAdjustment($rom, 'Retro');
				loadTemplate('ArcadeSkinListB');
				$context['sub_template'] = 'arcade_list';
				$context['page_title'] = $txt['arcade_game_list'];
				break;
			case 1:
				ArcadeIconAdjustment($rom, 'Generic');
				loadTemplate('ArcadeList');
				$context['sub_template'] = 'arcade_list';
				$context['page_title'] = $txt['arcade_game_list'];
				break;
			default:
				if (!empty($customListSettings) && !empty($customListSettings['list_name']) && !empty($customListSettings['list_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $customListSettings['list_source_file']))
				{
					if (!function_exists($customListSettings['list_function'])) {
						require_once($boarddir . '/ArcadeSources/' . $customListSettings['list_source_file']);
						if (!empty($customListSettings['lang_function']) && function_exists($customListSettings['lang_function'])) {
							$customListSettings['lang_function']();
						}
						if (!empty($customListSettings['list_function']) && function_exists($customListSettings['list_function']))
						{
							ArcadeIconAdjustment($rom, 'Generic');
							$context['arcade_list'] = $customListSettings['list_function']();
							loadTemplate($customListSettings['list_template']);
							$context['sub_template'] = 'arcade_list';
							$context['page_title'] = $txt['arcade_game_list'];
						}
						else
						{
							ArcadeIconAdjustment($rom, 'Default');
							loadTemplate('ArcadeList');
							$context['sub_template'] = 'arcade_list';
							$context['page_title'] = $txt['arcade_game_list'];
						}
					}
					elseif (function_exists($customListSettings['list_function'])) {
						if (!empty($customListSettings['lang_function']) && function_exists($customListSettings['lang_function'])) {
							$customListSettings['lang_function']();
						}
						ArcadeIconAdjustment($rom, 'Default');
						loadTemplate($customListSettings['list_template']);
						$context['sub_template'] = 'arcade_list';
						$context['page_title'] = $txt['arcade_game_list'];
					}
					else {
						ArcadeIconAdjustment($rom, 'Generic');
						loadTemplate('ArcadeList');
						$context['sub_template'] = 'arcade_list';
						$context['page_title'] = $txt['arcade_game_list'];
					}
				}
				elseif (!empty($customListSettings) && !empty($customListSettings['list_name']) && !empty($customListSettings['list_template']))
				{
					ArcadeIconAdjustment($rom, 'Default');
					loadTemplate($customListSettings['list_template']);
					$context['sub_template'] = 'arcade_list';
					$context['page_title'] = $txt['arcade_game_list'];
				}
				else
				{
					ArcadeIconAdjustment($rom, 'Generic');
					loadTemplate('ArcadeList');
					$context['sub_template'] = 'arcade_list';
					$context['page_title'] = $txt['arcade_game_list'];
				}
		}
	}

	return;
}

function ArcadeXMLSuggest($rom = 0)
{
	global $context, $boarddir, $user_info, $txt, $smcFunc, $arcadeModSettings;

	$expectedCookieValue = !empty($arcadeModSettings['arcadeCookieEncryptionCipher']) ? $arcadeModSettings['arcadeCookieEncryptionCipher'] : '';

	$passedTime = 0;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$context['xml_data']['games']['children'] = [];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$_REQUEST['name'] = trim($smcFunc['strtolower']($_REQUEST['name'])) . '*';
	$_REQUEST['name'] = strtr($_REQUEST['name'], array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;'));
	$arcadeSuggestCookie = !empty($arcadeModSettings['arcade_suggest_time']) && filter_var($arcadeModSettings['arcade_suggest_time'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 30]]) ? (int)$arcadeModSettings['arcade_suggest_time'] : 10;

	if (isset($_COOKIE['arcade_suggest_timer'])) {
		require_once($boarddir . '/ArcadeSources/Subs-ArcadeAncillary.php');
		$_COOKIE['arcade_suggest_timer'] = preg_replace("/[^a-zA-Z0-9]+/", "", $_COOKIE['arcade_suggest_timer']);
		$getCookie = arcadeEncryptionCipher($_COOKIE['arcade_suggest_timer'], '', 'decrypt');
		$parts = explode('_', $getCookie);
		$passedTime = !empty($expectedCookieValue) && $parts[0] == $expectedCookieValue ? intval(substr($getCookie, strpos($getCookie, "_") + 1)) : 0;
	}

	// Find the Game
	if (!empty($passedTime) && time() - $passedTime > $arcadeSuggestCookie) {
		//setcookie("arcade_suggest_timer", time(), time() + ($arcadeSuggestCookie), "/");
		setcookie(
			"arcade_suggest_timer",
			arcadeEncryptionCipher($expectedCookieValue . '_' . time(), '', 'encrypt'),
			[
				'expires' => time() + (30 * 60),
				'path' => '/',
				'secure' => true,
				'httponly' => true,
				'samesite' => 'Strict'
			]
		);
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.id_cat, game.rom_flag
			FROM {db_prefix}arcade_games AS game
				LEFT JOIN {db_prefix}arcade_categories AS category ON (category.id_cat = game.id_cat)
			WHERE ' . (isset($_REQUEST['textid']) && $_REQUEST['textid'] == 'arenagame' ? '{raw:query_arena_game}' : '{raw:query_see_game}') . '
				AND enabled = {int:enabled} AND game.game_name LIKE {string:search}' . $whererom . '
			LIMIT ' . (strlen($_REQUEST['name']) <= 2 ? '100' : '800'),
			array(
				'search' => $_REQUEST['name'],
				'query_see_game' => $user_info['query_see_game'],
				'query_arena_game' => $user_info['query_arena_game'],
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
				'enabled' => 1,
			)
		);
		$context['xml_data'] = array(
			'games' => array(
				'identifier' => 'game',
				'children' => array(),
			),
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (function_exists('iconv'))
			{
				$utf8 = iconv($txt['lang_character_set'], 'UTF-8', $row['game_name']);
				if ($utf8)
					$row['game_name'] = $utf8;
			}

			if (preg_match('~&#\d+;~', $row['game_name']) != 0)
			{
				$fixchar = function ($n) {
					if ($n < 128)
						return chr($n);
					elseif ($n < 2048)
						return chr(192 | $n >> 6) . chr(128 | $n & 63);
					elseif ($n < 65536)
						return chr(224 | $n >> 12) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
					else
						return chr(240 | $n >> 18) . chr(128 | $n >> 12 & 63) . chr(128 | $n >> 6 & 63) . chr(128 | $n & 63);
				};

				$row['game_name'] = preg_replace('~&#(\d+);~e', '$fixchar(\'$1\')', $row['game_name']);
			}

			$row['game_name'] = strtr($row['game_name'], array('&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;'));

			$context['xml_data']['games']['children'][] = array(
				'attributes' => array(
					'id' => $row['id_game'],
					'action' => !empty($row['rom_flag']) ? 'retro_arch' : 'arcade',
				),
				'value' => $row['game_name'],
			);
		}
		$smcFunc['db_free_result']($request);
	}

	// Template
	loadTemplate('Xml');
	$context['sub_template'] = 'generic_xml';
}

function ArcadeRate($rom = 0)
{
 	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $user_info, $smcFunc;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : $rom;
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND game.rom_flag = {int:rom_flag}' : '';
	if (empty($arcadeModSettings['arcadeEnableRatings']) || !$game = getGameInfo((int) $_REQUEST['game'], false, $rom)) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_not_found', false);
	}

	$_REQUEST['rate'] = (int) $_REQUEST['rate'];

	// Check that rating is correct
	if ($_REQUEST['rate'] < 0 || $_REQUEST['rate'] > 5) {
		arcadeClearSession();
		fatal_lang_error('arcade_rate_error', false);
	}

	// We may need time ;)
	$time = time();

	// Remove rating
	if ($_REQUEST['rate'] === 0)
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_rates
			WHERE id_member = {int:member}
				AND id_game = {int:game}',
			array(
				'game' => $game['id'],
				'member' => $user_info['id'],
			)
		);
	}
	// Update rating
	else
	{
		$smcFunc['db_insert']('replace',
			'{db_prefix}arcade_rates',
			array(
				'id_member' => 'int',
				'id_game' => 'int',
				'rating' => 'int',
				'rate_time' => 'int',
			),
			array(
				$user_info['id'],
				$game['id'],
				$_REQUEST['rate'],
				$time
			),
			array('id_member', 'id_game')
		);
	}

	// Update rating
	$request = $smcFunc['db_query']('', '
		SELECT SUM(rating), COUNT(*)
		FROM {db_prefix}arcade_rates
		WHERE id_game = {int:game}
		GROUP BY id_game',
		array(
			'game' => $game['id'],
		)
	);
	list ($sum_rates, $num_rates) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	updateGame($game['id'], array('rating' => $sum_rates / $num_rates, 'num_rates' => $num_rates), $rom);

	if (isset($_REQUEST['xml']))
		ArcadeXMLOutput(
			array(
				'message' => &$txt['arcade_rating_saved'],
				'rating' => floor($sum_rates / $num_rates)
			)
		);

	redirectexit('?action=' . (empty($rom) ? 'arcade;sa=highscore;' : 'retro_arch;') . 'game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3');
}

function ArcadeFavorite($rom = 0)
{
 	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $user_info, $smcFunc;

	$xml = isset($_REQUEST['xml']);
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : $rom;
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND game.rom_flag = {int:rom_flag}' : '';

	is_not_guest();

	if (empty($arcadeModSettings['arcadeEnableFavorites']) || !($game = getGameInfo((int) $_REQUEST['game'], false, $rom))) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_not_found', false);
	}

	// It's favorite so we can remove it
	if ($game['is_favorite'])
	{
		$remove = true;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_favorite
			WHERE id_member = {int:member}
				AND id_game = {int:game}',
			array(
				'game' => $game['id'],
				'member' => $user_info['id'],
			)
		);

		// Update favorites count
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}arcade_favorite
			WHERE id_game = {int:game}
			GROUP BY id_game',
			array(
				'game' => $game['id'],
			)
		);

		list ($num_favorites) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		updateGame($game['id'], array('num_favorites' => $num_favorites), $rom);

		if ($xml)
			ArcadeXMLOutput(
				array(
					'message' => &$txt['arcade_favorite_removed'],
					'state' => 0
				)
			);
	}
	// It's not favorite, let's add it
	else
	{
		$remove = false;

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_favorite',
			array(
				'id_member' => 'int',
				'id_game' => 'int',
			),
			array(
				$user_info['id'],
				$game['id']
			),
			array()
		);

		// Update favorites count
		updateGame($game['id'], array('num_favorites' => '+'), $rom);

		if ($xml)
			ArcadeXMLOutput(array(
				'message' => $txt['arcade_favorite_added'],
				'state' => 1
			));
	}

	redirectexit('?action=' . (empty($rom) ? 'arcade;sa=highscore;' : 'retro_arch;') . 'game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3');
}

?>