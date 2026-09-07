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

function ArcadeChamps($count = 3, $type='wins', $rom = 0)
{
	// Returns best players by count of champions
	global $db_prefix, $scripturl, $txt, $arcadeModSettings, $modSettings, $boardurl, $smcFunc, $context, $settings;

	list ($champ_list, $results) = array(array(), array());
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$rows = array();
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$type = !empty($type) ? $type : 'gen';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeChamps = cache_get_data('arcade_champsC_' . $type . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		if($type == 'wins')
		{
			$results = $smcFunc['db_query']('', '
				SELECT count(*) AS champions,
				IFNULL(mem.id_member, {int:zero}) AS id_member,
				IFNULL(mem.real_name, {string:empty}) AS real_name,
				IFNULL(mem.avatar, {string:empty}) AS avatar,
				IFNULL(attach.filename, {string:empty}) AS filename,
				IFNULL(attach.id_attach, {string:empty}) AS id_attach
				FROM {db_prefix}arcade_games AS game
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
				LEFT JOIN {db_prefix}attachments AS attach ON (attach.id_member = game.id_champion)
				WHERE game.id_champion_score > {int:zero} AND mem.id_member != 0' . $whererom . '
				GROUP BY game.id_champion, mem.id_member, mem.real_name, mem.avatar, attach.filename, attach.id_attach
				ORDER BY champions DESC
				LIMIT '.$count,
				array(
					'empty' => '',
					'zero' => '0',
					'number' => $count,
					'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
				)
			);
		}
		else
		{
			$results = $smcFunc['db_query']('', '
				SELECT count(*) AS champions, game.id_cat,
				IFNULL(mem.id_member, {int:zero}) AS id_member,
				IFNULL(mem.real_name, {string:empty}) AS real_name,
				IFNULL(mem.avatar, {string:empty}) AS avatar,
				IFNULL(attach.filename, {string:empty}) AS filename,
				IFNULL(attach.id_attach, {string:empty}) AS id_attach
				FROM {db_prefix}arcade_games AS game
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_champion)
				LEFT JOIN {db_prefix}attachments AS attach ON (attach.id_member = game.id_champion)
				WHERE game.id_champion_score > {int:zero} AND mem.id_member != 0 AND game.id_cat = {int:cat}' . $whererom . '
				GROUP BY game.id_champion, game.id_cat, mem.id_member, mem.real_name, mem.avatar, attach.id_attach, attach.filename
				ORDER BY champions DESC
				LIMIT '.$count,
				array(
					'empty' => '',
					'zero' => '0',
					'number' => $count,
					'cat' => (int)$_REQUEST['category'],
					'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
				)
			);
		}
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_champsC_' . $type . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeChamps) ? json_decode($arcadeChamps, true) : $rows;

	foreach ($rows as $score)
	{
		if (!empty($avatar)) {
			unset($avatar);
		}
		$width = !empty($arcadeModSettings['skin_avatar_size_width']) && (int)$arcadeModSettings['skin_avatar_size_width'] > 0 ? (int)$arcadeModSettings['skin_avatar_size_width'] : 50;
		$height = !empty($arcadeModSettings['skin_avatar_size_height']) && (int)$arcadeModSettings['skin_avatar_size_height'] > 0 ? (int)$arcadeModSettings['skin_avatar_size_height'] : 50;
		//linked avatar
		if (mb_substr($score['avatar'], 0, 7) == 'http://' || mb_substr($score['avatar'], 0, 8) == 'https://')
		{
			if ($wihi = ArcadeSizer($score['avatar'], $width, $height))
				$avatar = '<img src="' . $score['avatar'] . '" style="width: ' . $wihi[0] . 'px;height: ' . $wihi[1] . 'px;" alt="&nbsp;" />';
			else
				unset($avatar);
		}

		//resident avatar
		if($score['avatar'] && !isset($avatar))
		{
			if($wihi = ArcadeSizer($modSettings['avatar_url'] . '/' . $score['avatar'], $width, $height))
				$avatar = '<img alt="&nbsp;" src="' . $modSettings['avatar_url'] . '/' . $score['avatar'] . '" style="width: ' . $wihi[0] . 'px;height: ' . $wihi[1] . 'px;" />';
			else
				unset($avatar);

		}

		//uploaded avatar custom
		if(isset($score['filename']) && !isset($avatar) && mb_substr($score['filename'],0, 7) == 'avatar_' )
		{
			if(isset($modSettings['custom_avatar_dir']) && file_exists($modSettings['custom_avatar_dir'] . '/' . $score['filename']))
			{
				$wihi = ArcadeSizer($modSettings['custom_avatar_url'] . '/' . $score['filename'], $width, $height);
				$avatar = '<img alt="&nbsp;" src="' . $modSettings['custom_avatar_url'] . '/' . $score['filename'] . '" style="border: 0px;width: ' . (!empty($wihi[0]) ? $wihi[0] : '100') . 'px;height: ' . (!empty($wihi[1]) ? $wihi[1] : '100') .'px;" />';
			}
		}

		//uploaded avatar attachment
		if(isset($score['filename']) && !isset($avatar) && mb_substr($score['filename'],0, 7) == 'avatar_')
			$avatar = '<img src="' . $scripturl.'?action=dlattach;attach=' . $score['id_attach'] . ';type=avatar" alt="&nbsp;" style="border: 0px;width: ' . $width . 'px;height: ' . $height . 'px;' . '" />';

		$champ_list[] = array(
			'id' => $score['id_member'],
			'name' => $score['real_name'],
			'link' => ($context['user']['is_logged'] && $score['id_member'])? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $score['real_name'],
			'champions' => isset($score['champions']) ? $score['champions'] : '',
			'score' => isset($score['value']) ? $score['value'] : '',
			'avatar' => isset($avatar) ? $avatar : '<img style="width: ' . $width . 'px;height: ' . $height . 'px;" src="' . $settings['default_images_url'] . '/arc_icons/noavatar.gif" alt="" />',
		);
	}

	return $champ_list;
}

function ArcadeLatest($count=8, $curved=false, $rom = 0)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 0];
	$code = '
	<div style="padding: 10px;text-align: right;margin-right: 1px;width: 100%;">';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeGamesLatest = cache_get_data('arcade_games_latestC' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$results = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.thumbnail, game.thumbnail_small, game.game_directory, game.rom_flag, score.score, score.position, score.champion_from, score.duration,
			IFNULL(mem.id_member, {int:zero}) AS id_member, IFNULL(score.player_name, {string:empty}) AS real_name, score.end_time
			FROM ({db_prefix}arcade_scores AS score, {db_prefix}arcade_games AS game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
			WHERE game.id_game = score.id_game' . $whererom . '
			ORDER BY end_time DESC
			LIMIT {int:count}',
			array(
				'count' => $count,
				'zero' => '0',
				'empty' => '',
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_latestC' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeGamesLatest) ? json_decode($arcadeGamesLatest, true) : $rows;

	if(empty($rows))
	{
		echo '
	<div class="centertext" style="clear: both;font-weight: bold;font-size: 0.9em;font-style: italic;">', $txt['arcade_scores_none'] ,'</div>';
		return false;
	}
	else
	{
		$found = count($rows);
		foreach ($rows as $row)
		{
			switch($i){
				case 0:
					$i = 1;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.055);';
					break;
				default:
					$i = 0;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.015);';
			}

			$row['thumbnail'] = !empty($row['thumbnail']) ? $row['thumbnail'] : 'nofileexist.jpg';
			$row['thumbnail_small'] = !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : 'nofileexist.jpg';
			$action = empty($row['rom_flag']) ? 'arcade' : 'retro_arch';
			$main = empty($row['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
			$mainUrl = empty($row['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
			//latest scores details
			$date = $row['end_time'];
			$playerid = $row['id_member'];
			$player = $row['real_name'];
			$game_id = $row['id_game'];
			$row['game_name'] = strlen($row['game_name']) >= $context['nameCharLength'] ? mb_substr($row['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $row['game_name'];
			$game_name = $row['game_name'];
			$score = comma_format($row['score']);
			$game_pic = !is_dir($main . '/' . $row['game_directory'] . '/' . $row['thumbnail']) && file_exists($main . '/' . $row['game_directory'] . '/' . $row['thumbnail']) ? $mainUrl . '/' . $row['game_directory'] . '/' . $row['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$game_pic2 = !is_dir($main . '/' . $row['game_directory'] . '/' . $row['thumbnail_small']) && file_exists($main . '/' . $row['game_directory'] . '/' . $row['thumbnail_small']) ? $mainUrl . '/' . $row['game_directory'] . '/' . $row['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$game_pic = $game_pic == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $game_pic2 : $game_pic;
			$time = date("m/d/Y", $row['end_time']);
			$div_con = addslashes(sprintf($txt['skin_when'], $time));
			$code .= '
		<div style="display: flex;flex-direction: column;width: 100%;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
			<div style="display: flex;justify-content: space-between;">
				<a style="padding: 0rem 0.3rem 0rem 0.3rem;vertical-align: top;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $game_id . '"><b>' . $game_name . '</b></a>
				<a style="text-indent: 0.3em;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $game_id . '" title="' . $game_name . '">
					<img src="' . $game_pic . '" alt="' . $game_name . '"  style="border: 0px;width: 1.35rem;height: 1.35rem;" />
				</a>
			</div>
			<div style="display: flex;justify-content: space-between;padding-bottom: 0em !important;width: 100%;">';

			if($context['user']['is_logged'] && $playerid)
					$xplayer = '<a href="' . $scripturl . '?action=profile;u=' . $playerid . '"><b>' . $player . '</b></a>';
			else
					$xplayer = $player;

				$code .= '
				<div style="display: inline-flex;justify-content: left;width: 48%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;text-indent: 0.3rem;">' . $xplayer . '</div>
				<div style="display: inline-flex;flex: 1 1 auto;justify-content: center;min-width: 20%;width: 20%;white-space: nowrap;overflow: hidden;text-align: right;font-size: clamp(0.5rem, 20vw, 0.6rem);">' . $time . '</div>
				<div style="padding: 0rem 0.2rem 0rem 0.2rem;display: inline-flex;justify-content: right;width: 32%;white-space: nowrap;overflow: hidden;align-self: flex-end;" title="' . $txt['arcade_new_score'] . '"><span style="padding-right: 0.01rem;">&#128392;</span>' . $score . '</div>
			</div>
			<div style="line-height: 0rem;"><span>&nbsp;</span></div>' . (!empty($_SESSION['isPortalMobile']) ? '<div><span>&nbsp;</span></div>' : '') . '
		</div>';
		}

		$code .= '
	</div>';

		return $code;
	}
}

function ArcadeNewChamps($count = 8, $rom = 0)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 0];
	$code = '
	<div style="padding:10px;text-align:right;margin-right:1px;width: 100%;">';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeNewChamps = cache_get_data('arcade_newChampsC' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.game_directory, game.thumbnail, game.thumbnail_small, game.rom_flag, score.score, score.position, score.end_time,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, score.player_name) AS real_name
			FROM ({db_prefix}arcade_scores AS score, {db_prefix}arcade_games AS game)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
			WHERE score.position = 1 AND game.id_game = score.id_game' . $whererom . '
			ORDER BY end_time DESC
			LIMIT {int:count}',
			array(
				'count' => $count,
				'empty' => '',
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_newChampsC' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeNewChamps) ? json_decode($arcadeNewChamps, true) : $rows;

	if(empty($rows))
	{
		echo '<div class="centertext" style="font-weight: bold;font-size: 0.9em;font-style: italic;">', $txt['arcade_scores_none'], '</div>';
		return false;
	}
	else
	{
		foreach ($rows as $row)
		{
			switch($i){
				case 0:
					$i = 1;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.055);';
					break;
				default:
					$i = 0;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.015);';
			}
			if (empty($whererom))
				list($action, $main, $mainUrl) = empty($row['rom_flag']) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']);
			else
				list($action, $main, $mainUrl) = empty($rom) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']);

			//newest champ details
			$row['thumbnail'] = !empty($row['thumbnail']) ? $row['thumbnail'] : 'nofileexist.jpg';
			$row['thumbnail_small'] = !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : 'nofileexist.jpg';
			$playerid = $row['id_member'];
			$player = $row['real_name'];
			$game_id = $row['id_game'];
			$row['game_name'] = strlen($row['game_name']) >= $context['nameCharLength'] ? mb_substr($row['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $row['game_name'];
			$game_name = $row['game_name'];
			$score = $row['score'];
			$time = date("m/d/Y", $row['end_time']);
			$game_pic = !is_dir($main . '/' . $row['game_directory'] . '/' . $row['thumbnail']) && file_exists($main . '/' . $row['game_directory'] . '/' . $row['thumbnail']) ? $mainUrl . '/' . $row['game_directory'] . '/' . $row['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$game_pic2 = !is_dir($main . '/' . $row['game_directory'] . '/' . $row['thumbnail_small']) && file_exists($main . '/' . $row['game_directory'] . '/' . $row['thumbnail_small']) ? $mainUrl . '/' . $row['game_directory'] . '/' . $row['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$game_pic = $game_pic == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $game_pic2 : $game_pic;
			$code .= '
		<div style="display: flex;flex-direction: column;width: 100%;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
			<div style="display: flex;justify-content: space-between;">
				<div style="display: inline-flex;text-align: left;padding: 0rem 0.15rem 0rem 0.15rem;">
					<a href="' . $scripturl.'?action=' . $action . ';sa=play;game=' . $game_id . '">
						<img src="' . $game_pic . '" alt="' . $game_name . '" title="' . $game_name . '" style="border: 0px;width: 1.35rem;height: 1.35rem;" />
					</a>
				</div>
				<div style="display: inline-flex;padding: 0rem 0.15rem 0rem 0.15rem;"><a style="vertical-align: top;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $game_id . '"><b>' . $game_name . '</b></a></div>
			</div>
			<div style="display: flex;justify-content: space-between;text-align: left;padding-bottom: 0em !important;width: 100%;">';

			if($context['user']['is_logged'] && $playerid)
						$code .= '
				<div style="display: inline;width: 70%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><b><a href="' . $scripturl.'?action=profile;u=' . $playerid . '">' . $player . '</a></b></div>';
			else
						$code .= '
				<div style="display: inline;width: 70%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;"><b>' . $player . '</b></div>';

					$code .= '
				<div title="' . $txt['skin_new_champ'] . '" style="display: inline;width: 10%;white-space: nowrap;overflow: hidden;">&nbsp;&#127941;&nbsp;</div>
				<div style="min-width: 20%;width: 20%;white-space: nowrap;overflow: hidden;text-align: right;display: inline-flex;flex: 1 1 auto;justify-content: right;font-size: clamp(0.5rem, 20vw, 0.6rem);">' . $time . '</div>
			</div>
			<div style="line-height: 0rem;"><span>&nbsp;</span></div>' . (!empty($_SESSION['isPortalMobile']) ? '<div><span>&nbsp;</span></div>' : '') . '
		</div>';
		}

		$code .= '
	</div>';

		return $code;
	}
}

function ArcadeNewestGames($limit=5, $rom = 0)
{
	global $db_prefix, $scripturl, $arcadeModSettings, $smcFunc, $context, $txt, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 1];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';
	$newgam = '<div style="padding:10px;text-align:left;margin-left:1px;">';
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeNewest = cache_get_data('arcade_newestC' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$results = $smcFunc['db_query']('', '
			SELECT id_game, internal_name, game_name, game_directory, thumbnail, thumbnail_small, enabled, rom_flag
			FROM {db_prefix}arcade_games
			WHERE enabled=1' . $whererom . '
			ORDER BY id_game DESC
			LIMIT 0,{int:num}',
			array(
				'num' => $limit,
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_newestC' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeNewest) ? json_decode($arcadeNewest, true) : $rows;

	if(empty($rows))
	{
		$newgam .= '<div class="centertext" style="font-size: 0.9em;font-weight: bold;font-style: italic;">' . $txt['arcade_no_games'] . '</div></div>';
		return $newgam;
	}
	else
	{
		$found = count($rows);
		foreach ($rows as $newest_game)
		{
			switch($i){
				case 0:
					$i = 1;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.055);';
					break;
				default:
					$i = 0;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.015);';
			}

			if (empty($whererom))
				list($action, $main, $mainUrl) = empty($newest_game['rom_flag']) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : (array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']));
			else
				list($action, $main, $mainUrl) = empty($rom) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : (array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']));
			$newest_game['thumbnail'] = !empty($newest_game['thumbnail']) ? $newest_game['thumbnail'] : 'nofileexist.jpg';
			$newest_game['thumbnail_small'] = !empty($newest_game['thumbnail_small']) ? $newest_game['thumbnail_small'] : 'nofileexist.jpg';
			$gameIcon = !is_dir($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail']) && file_exists($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail']) ? $mainUrl . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon2 = !is_dir($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail_small']) && file_exists($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail_small']) ? $mainUrl . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon = $gameIcon == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameIcon2 : $gameIcon;
			$newest_game['game_name'] = strlen($newest_game['game_name']) >= $context['nameCharLength'] ? mb_substr($newest_game['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $newest_game['game_name'];
			$newgam .= '
	<div style="padding: 0.4rem 0rem 0.4rem 0rem;display: flex;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
		<div style="display: inline-flex;justify-content: left;min-width: 10%;max-width: 10%;">
			<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $newest_game['id_game'] . '">
				<img src="' . $gameIcon . '" style="width: 1.35rem;height: 1.35rem;vertical-align: bottom;" alt="Play ' . $newest_game['game_name'] . '" title="Play ' . $newest_game['game_name'] . '">
			</a>
		</div>
		<div style="display: inline-flex;justify-content: right;min-width: 90%;max-width: 90%;">
			<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $newest_game['id_game'] . '">' . $newest_game['game_name'] . '</a>
		</div>
		<div style="line-height: 0rem;"><span></span></div>
	</div>';
		}

		$newgam .= empty($rows) ? '<div class="centertext" style="display: inline;font-size: 1.0em;font-weight: bold;font-style: italic;">' . $txt['arcade_no_games'] . '</div>' : '';
		return $newgam . '</div>';
	}
}

function ArcadePopular($count = 5, $rom = 0)
{
	// Returns most played games
	global $db_prefix, $scripturl, $context, $arcadeModSettings, $smcFunc, $txt, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 1];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadePopular = cache_get_data('arcade_popularC' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$results = $smcFunc['db_query']('', '
			SELECT id_game, game_name, game_directory, thumbnail, thumbnail_small, enabled, num_plays, rom_flag
			FROM {db_prefix}arcade_games
			WHERE num_plays != 0 AND enabled = 1' . $whererom . '
			ORDER BY num_plays DESC
			LIMIT {int:num}',
			array(
				'num' => $count,
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_popularC' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadePopular) ? json_decode($arcadePopular, true) : $rows;

	$pop = '<div style="padding:10px;text-align:right;margin-right:1px;width: 100%;">';
	if(empty($rows)) {
		$pop .= '<div class="centertext" style="font-weight: bold;font-size: 0.9em;font-style: italic;">' . $txt['arcade_popular_none'] . '</div></div>';
		return $pop;
	}
	else {
		$found = count($rows);
		foreach ($rows as $score)
		{
			switch($i){
				case 0:
					$i = 1;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.055);';
					break;
				default:
					$i = 0;
					$blend = 'padding: 0.3rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.015);';
			}

			if (empty($whererom))
				list($action, $main, $mainUrl) = empty($score['rom_flag']) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : (array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']));
			else
				list($action, $main, $mainUrl) = empty($rom) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : (array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']));
			$score['thumbnail'] = !empty($score['thumbnail']) ? $score['thumbnail'] : 'nofileexist.jpg';
			$score['thumbnail_small'] = !empty($score['thumbnail_small']) ? $score['thumbnail_small'] : 'nofileexist.jpg';
			$gameIcon = !is_dir($main . '/' . $score['game_directory'] . '/' . $score['thumbnail']) && file_exists($main . '/' . $score['game_directory'] . '/' . $score['thumbnail']) ? $mainUrl . '/' . $score['game_directory'] . '/' . $score['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon2 = !is_dir($main . '/' . $score['game_directory'] . '/' . $score['thumbnail_small']) && file_exists($main . '/' . $score['game_directory'] . '/' . $score['thumbnail_small']) ? $mainUrl . '/' . $score['game_directory'] . '/' . $score['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon = $gameIcon == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameIcon2 : $gameIcon;
			$score['game_name'] = strlen($score['game_name']) >= $context['nameCharLength'] ? mb_substr($score['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $score['game_name'];
			$pop .= '
	<div style="padding: 0.4rem 0rem 0.4rem 0rem;display: flex;justify-content: space-between;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
		<div style="display: inline-flex;justify-content: left;min-width: 90%;max-width: 90%;">
			<a href="' . $scripturl. '?action=' . $action . ';sa=play;game=' . $score['id_game'] . '">' . $score['game_name'] . '</a>
		</div>
		<div style="display: inline-flex;justify-content: right;min-width: 10%;max-width: 10%;">
			<a href="' . $scripturl. '?action=' . $action . ';sa=play;game=' . $score['id_game'] . '">
				<img src="' . $gameIcon . '" style="width: 1.35rem;height: 1.35rem;vertical-align: bottom;" alt="Play ' . $score['game_name'] . '" title="Play ' . $score['game_name'] . '" />
			</a>
		</div>
		<div style="line-height: 0rem;"><span></span></div>
	</div>';
		}

		$pop .= '</div>';
		return $pop;

	}
}

function ArcadeRandomGames($limit=5, $rom=0)
{
	global $db_prefix, $scripturl, $arcadeModSettings, $smcFunc, $txt, $settings;
	$randomz = [];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';
	$results = $smcFunc['db_query']('', '
		SELECT id_game, game_directory, thumbnail, thumbnail_small, cover_icon, game_name, enabled, description, rom_flag
		FROM {db_prefix}arcade_games
		WHERE id_game >= (SELECT FLOOR( MAX(id_game) * RAND()) FROM {db_prefix}arcade_games ) AND enabled = 1' . $whererom . '
		ORDER BY id_game
		LIMIT {int:num}',
		array(
			'num' => $limit,
			'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
		)
	);

	while ($rg = $smcFunc['db_fetch_assoc']($results))
	{
		if (empty($whererom))
			list($action, $main, $mainUrl) = empty($rg['rom_flag']) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']);
		else
			list($action, $main, $mainUrl) = empty($rom) ? array('arcade', $arcadeModSettings['gamesDirectory'], $arcadeModSettings['gamesUrl']) : array('retro_arch', $arcadeModSettings['romGamesDirectory'], $arcadeModSettings['romGamesUrl']);
		$rg['thumbnail'] = !empty($rg['thumbnail']) ? $rg['thumbnail'] : 'nofileexist.jpg';
		$rg['thumbnail_small'] = !empty($rg['thumbnail_small']) ? $rg['thumbnail_small'] : 'nofileexist.jpg';
		$random_name = '<div><span></span></div><div style="text-align:center;"><a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $rg['id_game'] . '">' . $rg['game_name'] . '</a></div>';
		$random_description = '';
		$gameIcon = !is_dir($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail']) && file_exists($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail']) ? $mainUrl . '/' . $rg['game_directory'] . '/' . $rg['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
		$gameIcon2 = !is_dir($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail_small']) && file_exists($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail_small']) ? $mainUrl . '/' . $rg['game_directory'] . '/' . $rg['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
		$gameIcon = $gameIcon == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameIcon2 : $gameIcon;
		$iconfile = empty($rg['rom_flag']) ? 'arcadegamecover.png' : 'romgamecover.gif';
		$covericon = !empty($rg['cover_icon']) && !is_dir($main . '/' . $rg['game_directory'] . '/' . $rg['cover_icon']) && file_exists($main . '/' . $rg['game_directory'] . '/' . $rg['cover_icon']) ? $mainUrl . '/' . $rg['game_directory'] . '/' . $rg['cover_icon'] : $settings['default_images_url'] . '/arc_icons/' . $iconfile;

		$randomz[] = '
	<div style="text-align:center;font-size:1.2em;" class="smalltext">
		<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $rg['id_game'] . '"><img style="height: 2rem;width: 2rem;" class="imgBorder" src="' . $gameIcon . '" title="' . $rg['game_name'] . '" alt="' . $rg['game_name'] . '" /></a>' . $random_name . $random_description . '
	</div>';
	}

	$randomz[0] = empty($randomz) ? '<div class="centertext" style="clear: both;font-size: 1.1em;font-weight: bold;font-style: italic;">' . $txt['arcade_no_games'] . '</div>' : $randomz[0];
	$smcFunc['db_free_result']($results);

	shuffle($randomz);
	return implode('', $randomz);
}

function ArcadeDailyChallenge($game='', $rom = 0)
{
	global $db_prefix, $scripturl, $context, $smcFunc, $txt, $arcadeModSettings;

	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$context['CH_error'] = '';
	if($game)
	{
		$results = $smcFunc['db_query']('', '
			SELECT game.score_type, game.rom_flag
			FROM  {db_prefix}arcade_games as game
			WHERE game.id_game = {int:id}' . $whererom,
			array(
				'id' => $game['id'],
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);

		$t = $smcFunc['db_fetch_row']($results);
		$sort = !empty($t) && !empty($t[0]) && $t[0] == 1 ? 'ASC' : 'DESC';
		$results = $smcFunc['db_query']('', "
			SELECT
			a.id_game, a.score, a.position, a.end_time, game.rom_flag,
			IFNULL(mem.id_member, {int:zero}) AS id_member, IFNULL(mem.real_name, a.player_name) AS real_name
			FROM  {db_prefix}arcade_scores AS a
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = a.id_member)
			LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = a.id_game)
			WHERE a.id_game = {int:id} AND FROM_UNIXTIME(end_time, '%y-%m-%d') = CURDATE()" . $whererom . "
			ORDER BY a.score {raw:sort}",
			array(
				'empty' => '',
				'zero' => '0',
				'sort' => $sort,
				'id' => $game['id'],
				'date' => date('ymd'),
				'rom_flag' => !empty($rom) ? 1 : 0,
			)
		);

		$count = 0;
		$display = '';
		while ($time = $smcFunc['db_fetch_assoc']($results))
		{
			$count++;
			$display .= $count . '. ' . (isset($time['real_name']) ? $time['real_name'] : $txt['arcade_guest']) . ' - ' . $time['score'] . '<div><span></span></div>';
			if($count == 5)
				break;
		}

		if($count == 0)
		{
			$context['CH_error'] = 1;
			cache_put_data('game_of_day', null, 120);
		}

		$smcFunc['db_free_result']($results);
	}
	else
		$display = '
	<div class="centertext" style="font-weight: bold;font-size: 1.0em;font-style: italic;">' . $txt['arcade_no_games'] . '</div>';

	return $display;
}

function ArcadeShoutC($rom = 0)
{
	global $smcFunc, $txt, $arcadeModSettings, $user_info;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	if (isset($_REQUEST['del']))
	{
	   // Only allow admins to delete shouts

       if (allowedTo('arcade_admin'))
       {
    		$id = (int)$_REQUEST['del'];

    		$smcFunc['db_query']('', '
    			DELETE FROM {db_prefix}arcade_newshouts
    			WHERE id_shout = {int:ids}',
    			array(
    				'ids' => $id,
    			)
    		);

			// force a reload
    		cache_put_data('arcade_shouts', null, 86400);
      }
	}
	elseif (!$user_info['is_guest'])
	{
		$_REQUEST['the_shout'] = isset($_REQUEST['the_shout']) ? $_REQUEST['the_shout'] : '';
		$shout = trim(strlen($_REQUEST['the_shout']) > 100 ? mb_substr($_REQUEST['the_shout'], 0, 100) . '...' : $_REQUEST['the_shout']);
		if (!empty($shout))
		{
			$shout = $txt['arcade_shouted'] . $smcFunc['htmlspecialchars']($shout, ENT_QUOTES);
			add_to_arcade_shoutboxC($shout);
		}
	}

	redirectexit('action=' . $action);
}

function ArcadeShoutboxC($rom = 0)
{
    global $smcFunc, $scripturl, $settings, $txt, $sourcedir, $arcadeModSettings, $context;
	require_once($sourcedir . '/Subs.php');
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	list($content, $shouts, $action) = array('', array(), empty($rom) ? 'arcade': 'retro_arch');
	$version = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	if (!empty($arcadeModSettings['enable_arcade_cache']))
	{
		if ($shouts = cache_get_data('arcade_shouts', 86400) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT s.id_shout, s.id_member, s.content, s.time, m.real_name
				FROM {db_prefix}arcade_newshouts AS s
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
				ORDER BY id_shout DESC
				LIMIT 0, {int:limit}',
				array(
					'limit' => !empty($arcadeModSettings['arcade_show_shoutsC']) ? $arcadeModSettings['arcade_show_shoutsC'] : 25,
				)
			);

			while ($shout = $smcFunc['db_fetch_assoc']($result))
			{
				$shouts[] = array(
					'id_shout' => $shout['id_shout'],
					'id_member' => $shout['id_member'],
					'content' => str_replace(array("\n", "\t", "\r"), '', $shout['content']),
					'time' => $shout['time'],
					'real_name' => $shout['real_name'],
				);
			}
			$smcFunc['db_free_result']($result);
			cache_put_data('arcade_shouts', $shouts, 86400);
		}
	}
	else
	{
		$result = $smcFunc['db_query']('', '
			SELECT s.id_shout, s.id_member, s.content, s.time, m.real_name
			FROM {db_prefix}arcade_newshouts AS s
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
			ORDER BY id_shout DESC
			LIMIT 0, {int:limit}',
			array(
				'limit' => !empty($arcadeModSettings['arcade_show_shoutsC']) ? $arcadeModSettings['arcade_show_shoutsC'] : 25,
			)
		);

		while ($shout = $smcFunc['db_fetch_assoc']($result))
		{
			$shouts[] = array(
				'id_shout' => $shout['id_shout'],
				'id_member' => $shout['id_member'],
				'content' => str_replace(array("\n", "\t", "\r"), '', $shout['content']),
				'time' => $shout['time'],
				'real_name' => $shout['real_name'],
			);
		}

		$smcFunc['db_free_result']($result);
	}

	foreach($shouts as $shout)
	{
		$content .= '
					<div class="arcadeShout" style="margin: 4px;">
						<div style="border: dotted 1px; padding: 2px 4px 2px 4px;">';

		if (allowedTo('arcade_admin'))
			$content .= '
							<a href="' . $scripturl.'?action=' . $action . ';sa=shout;del=' . $shout['id_shout'] . '">
								<img style="border: 0px;" src="' . $settings['default_images_url'] . '/arc_icons/del1.png" alt="X"  title="' . $txt['arcade_shout_del'] . '"/>
							</a>&nbsp;';

		$content .= '
							<b>' . $shout['real_name'] . '</b>
						</div>
						<div style="padding: 2px;">' . timeformat($shout['time']) . '</div>
						<div style="padding: 4px;">' . ArcadeShoutParser(wordwrap(parse_bbc(censorText($shout['content'])), 34, "\n", true), $version) . '</div>
					</div>';


	}

	return $content;
}

function ArcadeShoutParser($img_tag, $version = 'v2.0')
{
	global $txt;

	if (empty($img_tag))
		return '';
	// shoutbox wordwrap messes up the img src... so fix it
	$result = '';
	libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD'))
		$doc->loadHTML($img_tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	else
		$doc->loadHTML($img_tag);
	libxml_clear_errors();
	$tags = $doc->getElementsByTagName('IMG');
    foreach ($tags as $tag)
	{
        $old_src = $tag->getAttribute('src');
        $new_src_url = preg_replace('/\v(?:[\v\h]+)/', '', $old_src);
        $tag->setAttribute('src', $new_src_url);
    }

	// ... remove any links for those not allowed to view them otherwise keep link but change link text to generic value
	if (!allowedTo('arcade_view_hyperlink'))
	{
		$tags = $doc->getElementsByTagName('A');
		$newelement = $doc->createTextNode($txt['arcade_no_links']);
		foreach ($tags as $tag)
			$tag->parentNode->replaceChild($newelement, $tag);
	}
	else
	{
		$tags = $doc->getElementsByTagName('A');
		$newelement = $doc->createTextNode($txt['arcade_linkname']);
		foreach ($tags as $tag)
			$tag->nodeValue = $txt['arcade_linkname'];
	}

	foreach($doc->childNodes as $node)
	{
		if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD'))
			$result .= $doc->saveHTML($node);
		else
			$result .= preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $doc->saveHTML($node));
	}

	if ($version !== 'v2.1')
		$result = trim(preg_replace('/<img([^>]*)>/i', "<img $1 />", $result));

	return !empty($result) ? str_replace('%0A', '', $result) : $txt['arcade_no_links'];
}

function add_to_arcade_shoutboxC($shout)
{
	global $user_info, $smcFunc, $arcSettings, $txt, $context;

	if (empty($context['user']['is_logged']))
		return;

	// remove hyperlinks for those not allowed to shout them
	if (!allowedTo('arcade_hyperlink'))
	{
		$url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^‌​,.\s])@';
		$shout = preg_replace($url, $txt['arcade_no_links'], $shout);

	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_newshouts',
		array(
			'id_member' => 'int', 'content' => 'string-255', 'time' => 'int',
		),
		array(
			$user_info['id'], $shout, time(),
		),
		array('id_shout')
	);

	cache_put_data('arcade_shouts', null, 86400);
}

?>