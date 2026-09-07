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

function ArcadeStatistics($rom = 0)
{
	global $txt, $context, $scripturl, $arcadeModSettings, $boarddir;

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);

	// Load data using functions
	$context['arcade']['statistics']['play'] = ArcadeStats_MostPlayed(10, $rom);
	$context['arcade']['statistics']['play_rom'] = ArcadeStats_MostPlayed(10, 1);
	$context['arcade']['statistics']['active'] = ArcadeStats_MostActive();
	$context['arcade']['statistics']['rating'] = ArcadeStats_Rating(10, 0);
	$context['arcade']['statistics']['rating_rom'] = ArcadeStats_Rating(10, 1);
	$context['arcade']['statistics']['champions'] = ArcadeStats_BestPlayers();
	$context['arcade']['statistics']['longest'] = ArcadeStats_LongestChampions();

	// Layout
	loadTemplate('ArcadeStats');
	$context['sub_template'] = 'arcade_statistics';
	$context['page_title'] = $txt['arcade_stats_title'];

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';sa=stats',
		'name' => $txt['arcade_stats'],
	);
}

function ArcadeStats_MostPlayed($count = 10, $rom = 0)
{
	// Returns most played games
	global $db_prefix, $scripturl, $smcFunc, $arcadeModSettings, $modSettings, $cacheAPI, $boarddir;

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arcade' ? 'arcade' : '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) && !empty($action) ? 1 : ($action == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$where = empty($arcadeModSettings['arcadeRomToggle']) && $rom > -1 ? ' AND game.rom_flag = {int:romflag}' : '';
	list($top, $max) = [[], -1];
	//$arcadeMostPlayed = !empty($arcadeModSettings['arcade_cache_enable']) && !empty($modSettings['cache_enable']) ? $cacheAPI->getData('arcade_most_played' . $rom2, (int)$arcadeModSettings['arcade_cache_time']) : [];
	$arcadeMostPlayed = !empty($arcadeModSettings['arcade_cache_enable']) ? cache_get_data('arcade_most_played' . (string)$rom2, 1) : [];
	if (empty($arcadeMostPlayed) || $arcadeMostPlayed === null) {
		$request = $smcFunc['db_query']('', '
			SELECT game.num_plays, SUM(game.num_plays) AS most_played, game.id_game, game.game_name, game.game_rating, game.thumbnail, game.cover_icon, game.game_directory, game.rom_flag
			FROM {db_prefix}arcade_games AS game
			WHERE game.num_plays > 0' . $where . '
			GROUP BY game.num_plays, game.id_game, game.game_name, game.game_rating, game.thumbnail, game.cover_icon, game.game_directory, game.rom_flag
			ORDER BY most_played DESC
			LIMIT {int:count}',
			array(
				'count' => $count,
				'empty' => '',
				'romflag' => $rom,
			)
		);

		while ($score = $smcFunc['db_fetch_assoc']($request))
		{
			if ($max == -1)
				$max = $score['num_plays'];
			if ($max == 0)
				return []; // No one has played games yet
			$section = !empty($score['rom_flag']) || !empty($rom) ? 'retro_arch' : 'arcade';
			$top[] = array(
				'id' => $score['id_game'],
				'thumbnail' => !empty($score['thumbnail']) ? $score['thumbnail'] : '',
				'cover_icon' => !empty($score['cover_icon']) ? $score['cover_icon'] : '',
				'game_directory' => !empty($score['game_directory']) ? $score['game_directory'] : '',
				'name' => $score['game_name'],
				'link' => '<a href="' . $scripturl . '?action=' . $section . ';sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
				'rating' => $score['game_rating'],
				'plays' => comma_format($score['num_plays']),
				'percent' => ($score['num_plays'] / $max) * 100,
				'rom_flag' => !empty($score['rom_flag']) ? 1 : 0,
			);
		}
		$smcFunc['db_free_result']($request);

		$top = array_filter($top);
		if (!empty($arcadeModSettings['arcade_cache_enable'])  && !empty($modSettings['cache_enable'])) {
			//cache_put_data('arcade_most_played' . (string)$rom2, $top, 1);
		}
	}
	$top = !empty($arcadeMostPlayed) ? $arcadeMostPlayed : $top;

	if (empty($top))
		return [];
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_Rating($count = 10, $rom = 0)
{
	global $db_prefix, $scripturl, $smcFunc, $arcadeModSettings;

	$action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arcade' ? 'arcade' : '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) && !empty($action) ? 1 : ($action == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$where = empty($arcadeModSettings['arcadeRomToggle']) && $rom > -1 ? ' AND game.rom_flag = {int:romflag}' : '';
	list($top, $max) = [[], -1];
	$arcadeGamesRating = !empty($arcadeModSettings['arcade_cache_enable']) ? cache_get_data('arcade_games_rating' . $rom2, (int)$arcadeModSettings['arcade_cache_time']) : [];
	if (empty($arcadeGamesRating)) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.game_rating, game.num_plays, game.rom_flag
			FROM {db_prefix}arcade_games AS game
			WHERE game.game_rating > 0' . $where . '
			ORDER BY game.game_rating DESC
			LIMIT {int:count}',
			array(
				'count' => $count,
				'empty' => '',
				'romflag' => $rom,
			)
		);

		while ($score = $smcFunc['db_fetch_assoc']($request))
		{
			if ($max == -1)
				$max = $score['game_rating'];

			$section = !empty($score['rom_flag']) || !empty($rom) ? 'retro_arch' : 'arcade';
			$stars = '';
			for ($i=1;$i<6;$i++) {
				if ((int)$score['game_rating'] >= $i) {
					$stars .= '&#9733;';
				}
				else {
					$stars .= '&#9734;';
				}
			}
			$top[] = array(
				'id' => $score['id_game'],
				'name' => $score['game_name'],
				'link' => '<a href="' . $scripturl . '?action=' . $section . ';sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
				'bar_rating' => '<meter optimum="5" min="0" max="5" value="' . (int)$score['game_rating'] . '">' . ((int)$score['game_rating'] * 20) . '%</meter>', //$score['game_rating'],
				'rating' => (int)$score['game_rating'],
				'plays' => comma_format($score['num_plays']),
				'percent' => ($score['game_rating'] / $max) * 100,
				'rom_flag' => !empty($score['rom_flag']) ? 1 : 0,
				'stars' => $stars,
			);
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_rating' . $rom2, $top, (int)$arcadeModSettings['arcade_cache_time']);
		}
	}
	$top = !empty($arcadeGamesRating) ? $arcadeGamesRating : $top;

	if (empty($top))
		return [];
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_BestPlayers($count = 10, $rom = 0)
{
	// Returns best players by count of champions
	global $db_prefix, $scripturl, $txt, $smcFunc, $arcadeModSettings;

	$action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arcade' ? 'arcade' : '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) && !empty($action) ? 1 : ($action == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$where = empty($arcadeModSettings['arcadeRomToggle']) && $rom > -1 ? ' AND game.rom_flag = {int:romflag}' : '';
	list($top, $max) = [[], -1];
	$arcadeBestPlayers = !empty($arcadeModSettings['arcade_cache_enable']) ? cache_get_data('arcade_games_best' . $rom2, (int)$arcadeModSettings['arcade_cache_time']) : [];
	if (empty($arcadeBestPlayers)) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_champion, SUM(game.id_champion) AS champions, game.rom_flag, IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, {string:empty}) AS real_name
			FROM {db_prefix}arcade_games AS game
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
			WHERE game.id_champion_score > 0' . $where . '
			GROUP BY game.id_champion, id_member, real_name, game.rom_flag
			ORDER BY champions DESC
			LIMIT {int:count}',
			array(
				'count' => $count,
				'empty' => '',
				'romflag' => $rom,
			)
		);

		while ($score = $smcFunc['db_fetch_assoc']($request))
		{
			if ($max == -1)
				$max = $score['champions'];

			$top[] = array(
				'name' => $score['real_name'],
				'link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['guest'],
				'champions' => comma_format($score['champions']),
				'percent' => ($score['champions'] / $max) * 100,
			);
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			//cache_put_data('arcade_games_best' . $rom2, $top, (int)$arcadeModSettings['arcade_cache_time']);
		}

	}

	$top = !empty($arcadeBestPlayers) ? $arcadeBestPlayers : $top;

	if (empty($top))
		return [];
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_MostActive($count = 10, $time = -1, $rom = 0)
{
	// Returns most active players
	global $db_prefix, $scripturl, $txt, $smcFunc, $arcadeModSettings;

	$action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arcade' ? 'arcade' : '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) && !empty($action) ? 1 : ($action == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($top, $max) = [[], -1];
	$arcadeGamesActive = !empty($arcadeModSettings['arcade_cache_enable']) ? cache_get_data('arcade_games_mostactive' . $rom2, (int)$arcadeModSettings['arcade_cache_time']) : [];
	if (empty($arcadeGamesActive)) {
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS scores, IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, {string:empty}) AS real_name
			FROM {db_prefix}arcade_scores AS score
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
			GROUP BY score.id_member, mem.id_member, mem.real_name
			ORDER BY scores DESC
			LIMIT {int:count}',
			array(
				'count' => $count,
				'empty' => ''
			)
		);

		while ($score = $smcFunc['db_fetch_assoc']($request))
		{
			if ($max == -1)
				$max = $score['scores'];

			$top[] = array(
				'name' => $score['real_name'],
				'link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['guest'],
				'scores' => comma_format($score['scores']),
				'percent' => ($score['scores'] / $max) * 100,
			);
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_mostactive' . $rom2, $top, (int)$arcadeModSettings['arcade_cache_time']);
		}
	}
	$top = !empty($arcadeGamesActive) ? $arcadeGamesActive : $top;

	if (empty($top))
		return [];
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_LongestChampions($count = 10, $time = - 1, $where = '', $rom = 0)
{
	global $db_prefix, $scripturl, $txt, $smcFunc, $arcadeModSettings;

	$action = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'arcade' ? 'arcade' : '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) && !empty($action) ? 1 : ($action == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($top, $max) = [[], -1];
	$where = empty($where) ? '' : $where;
	$whereRom = empty($arcadeModSettings['arcadeRomToggle']) && $rom > -1 ? ' AND game.rom_flag = {int:romflag}' : '';
	$arcadeLongChamps = !empty($arcadeModSettings['arcade_cache_enable']) ? cache_get_data('arcade_games_longchamps' . $rom2, (int)$arcadeModSettings['arcade_cache_time']) : [];

	// 100 max for this function
	$count = $count > 100 ? 100 : $count;
	$order = 'game.id_game ASC, durationx DESC';

	switch ($where) {
		case 'current':
			$where = 'score.champion_to = 0';
			$order = 'score.champion_from';
			break;
		case 'past':
			$where = 'score.champion_to > 0';
			$order = 'score.champion_to - score.champion_from';
			break;
		default:
			$where = '1 = 1';
			$order = '
				CASE
					WHEN cast(score.champion_from as signed) > 0 AND cast(score.champion_to as signed) = 0
						THEN UNIX_TIMESTAMP() - cast(score.champion_from as signed)
					WHEN cast(score.champion_from as signed) > 0
						THEN cast(score.champion_to as signed) - cast(score.champion_from as signed)
					ELSE
						0
				END DESC';
	}

	if (empty($arcadeLongChamps)) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.thumbnail, game.cover_icon, game.game_directory, game.rom_flag, score.champion_to, score.champion_from,
			MAX(TIMEDIFF(score.champion_to, score.champion_from)) AS durationx,
				CASE
					WHEN cast(score.champion_from as signed) > 0 AND cast(score.champion_to as signed) = 0
						THEN UNIX_TIMESTAMP() - cast(score.champion_from as signed)
					WHEN cast(score.champion_from as signed) > 0
						THEN cast(score.champion_to as signed) - cast(score.champion_from as signed)
					ELSE 0
				END AS champion_duration,
				IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, {string:empty}) AS real_name, CASE WHEN score.champion_to = 0 THEN 1 ELSE 0 END AS current
			FROM {db_prefix}arcade_scores AS score
				RIGHT JOIN {db_prefix}arcade_games AS game ON (game.id_game = score.id_game)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
			WHERE ' . $where . $whereRom . '
			GROUP BY score.id_score, game.id_game, game.game_name, game.thumbnail, game.cover_icon, game.game_directory, score.champion_from, score.champion_to, mem.id_member, mem.real_name, champion_duration, current, game.rom_flag
			ORDER BY ' . $order . '
			LIMIT {int:count}',
			array(
				'count' => $count,
				'empty' => '',
				'romflag' => $rom,
			)
		);

		while ($score = $smcFunc['db_fetch_assoc']($request))
		{
			$score['champion_from'] = !empty($score['champion_from']) ? (int)$score['champion_from'] : 0;
			$score['champion_to'] = !empty($score['champion_to']) ? (int)$score['champion_to'] : 0;

			if (empty($score['champion_from']) && empty($score['champion_to'])) {
				continue;
			}

			$score['champion_duration'] = !empty($score['champion_from']) && empty($score['champion_to']) ? time() - $score['champion_from'] : (!empty($score['champion_from']) ? $score['champion_to'] - $score['champion_from'] : 0);
			$score['champion_duration'] = (int)$score['champion_duration'];

			if (empty($score['champion_duration']) || (int)$score['champion_duration'] < 1)
				continue;

			$section = !empty($score['rom_flag']) ? 'retro_arch' : 'arcade';
			preg_match_all('/\d+(\.\d+)?/', duration_format($score['champion_duration']), $matches);
			list($totalSeconds, $i) = [0, 0];
			if (!empty($matches[0])) {
				foreach ($matches[0] as $seconds) {
					switch($i) {
						case 0:
							$totalSeconds = $totalSeconds + ($seconds * 24 * 3600);
							break;
						case 1:
							$totalSeconds = $totalSeconds + ($seconds * 3600);
							break;
						case 2:
							$totalSeconds = $totalSeconds + ($seconds * 60);
							break;
						case 3:
							$totalSeconds = $totalSeconds + ($seconds);
							break;
						default:
							$totalSeconds = $totalSeconds;
					}
					$i++;
				}

				if ($max == -1 || $max < $totalSeconds) {
					$max = $totalSeconds;
				}
				$top[] = array(
					'id' => $score['id_game'],
					'game_directory' => !empty($score['game_directory']) ? $score['game_directory'] : '',
					'thumbnail' => !empty($score['thumbnail']) ? $score['thumbnail'] : '',
					'cover_icon' => !empty($score['cover_icon']) ? $score['cover_icon'] : '',
					'game_name' => $score['game_name'],
					'game_link' => '<a href="' . $scripturl . '?action=' . $section . ';sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
					'member_name' => $score['real_name'],
					'member_link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['guest'],
					'duration' => $matches[0],
					'durationx' => (int)$score['durationx'],
					'durationy' => duration_format($totalSeconds),
					'percent' => ($totalSeconds / $max) * 100,
					'current' => $score['current'] == 1 ? true : false,
					'champ_duration' => $score['champion_from'],
				);
			}

		}
		$smcFunc['db_free_result']($request);

		usort($top, function($a, $b) {
			return $b['durationx'] <=> $a['durationx'];
		});

		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_longchamps' . $rom2, $top, (int)$arcadeModSettings['arcade_cache_time']);
		}
	}

	$top = !empty($arcadeLongChamps) ? $arcadeLongChamps : $top;

	if (empty($top))
		return [];
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

?>