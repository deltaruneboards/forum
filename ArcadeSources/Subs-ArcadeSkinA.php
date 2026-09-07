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

function ArcadeEnterpriseLang()
{
	global $txt;

	loadLanguage('ArcadeSkinA');
}

function ArcadeSkinA()
{
	global $txt, $arcadeModSettings, $context, $boarddir, $settings;

	loadLanguage('ArcadeSkinA');
	$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLengthA']) ? $arcadeModSettings['arcadeGamesNameLengthA'] : 100;
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinMainB.php');
	$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-scroller.js?var=rand' . rand(0,999) . '"></script>
		<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-scroller.css?var=rand' . rand(0,999) . '" media="all" type="text/css">
		<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-buttons.css?var=rand' . rand(0,999) . '" media="all" type="text/css">';
}

function ArcadeEnterpriseAdmin()
{
	global $txt, $arcadeModSettings, $context;
	loadLanguage('ArcadeAdminA');

	$enterprisA_plus = array();
	$enterpriseA = array(
		array('check', 'arcadeDropCatA'),
		array('int', 'skin_latest_scoresA'),
		array('int', 'skin_latest_champsA'),
		array('int', 'skin_latest_gamesA'),
		array('int', 'skin_most_popularA'),
		array('int', 'skin_avatar_size_widthA', 'subtext' => $txt['avsizeA_recommend']),
		array('int', 'skin_avatar_size_heightA', 'subtext' => $txt['avsizeA_recommend']),
		array('int', 'arcadeGamesNameLengthA'),
		array('select', 'arcade_skin_a_romcenter', explode('|', $txt['arcade_skin_a_romcenter_options']), 'subtext' => $txt['arcade_skin_a_romcenter_subtext']),
	);

	if (!empty($arcadeModSettings['arcade_skin_a_romcenter'])) {
		$enterprisA_plus = array(
			array('select', 'arcade_skin_a_romcenter_type', explode('|', $txt['arcade_skin_a_romcenter_types']), 'subtext' => $txt['arcade_skin_a_romcenter_types_subtext']),
			array('select', 'arcade_skin_a_romcenter_gametype', explode('|', $txt['arcade_skin_a_romcenter_gametypes']), 'subtext' => $txt['arcade_skin_a_romcenter_gametypes_subtext']),
			array('int', 'arcade_skin_a_romcenter_qty', 'subtext' => $txt['arcade_skin_a_romcenter_qty_subtext'], 'step' => 6, 'min' => 6, 'max' => 600),
		);
	}

	$context['html_headers'] .= '
	<script>
		$(document).ready(function() {
			if ($("#arcade_skin_a_romcenter_qty").val() < 6)
				$("#arcade_skin_a_romcenter_qty").val("36");
			$("#admin_form_wrapper").on("submit", function(event) {
				let romcenterQty = $("#arcade_skin_a_romcenter_qty").val();
				romcenterQty = parseInt(romcenterQty);
				if (romcenterQty < 6)
					$("#arcade_skin_a_romcenter_qty").val("6");
				if (romcenterQty > 600)
					$("#arcade_skin_a_romcenter_qty").val("600");
				romcenterQty = $("#arcade_skin_a_romcenter_qty").val();
				romcenterQty = parseInt(romcenterQty);
				if (romcenterQty % 6 !== 0) {
					for (x=1;x<6;x++) {
						if ((romcenterQty + x) % 6 === 0) {
							$("#arcade_skin_a_romcenter_qty").val(romcenterQty + x);
							break;
						}
					}
				}
			});
		});
	</script>';

	return array_merge($enterpriseA, $enterprisA_plus, array(''));
}

function ArcadeChampsA($count = 3, $type='wins', $rom = 0)
{
	// Returns best players by count of champions
	global $db_prefix, $scripturl, $txt, $arcadeModSettings, $modSettings, $boardurl, $smcFunc, $context, $settings;

	list ($champ_list, $results) = array(array(), array());
	$romToggle = !empty($arcadeModSettings['arcadeRomToggle']) ? intval($arcadeModSettings['arcadeRomToggle']) : 0;
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	//$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$type = !empty($type) ? $type : 'gen';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeGameChamps = cache_get_data('arcade_champsA_' . $type . $rom2, $arcadeModSettings['arcade_cache_time']))) {
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
					'rom_flag' => !empty($rom) ? 1 : 0,
				)
			);
		}

		$width = !empty($arcadeModSettings['skin_avatar_size_widthA']) && (int)$arcadeModSettings['skin_avatar_size_widthA'] > 0 ? (int)$arcadeModSettings['skin_avatar_size_widthA'] : 50;
		$height = !empty($arcadeModSettings['skin_avatar_size_heightA']) && (int)$arcadeModSettings['skin_avatar_size_heightA'] > 0 ? (int)$arcadeModSettings['skin_avatar_size_heightA'] : 50;
		while ($score = $smcFunc['db_fetch_assoc']($results))
		{
			unset($avatar);

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
					$wihi = ArcadeSizer($modSettings['custom_avatar_url'] .'/' . $score['filename'], $width, $height);
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
				'avatar' => isset($avatar) ? $avatar : '<img src="' . $settings['default_images_url'] . '/arc_icons/noavatar.gif" alt="" />',
			);
		}

		$smcFunc['db_free_result']($results);

		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_champsA_' . $type . $rom2, json_encode($champ_list, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$champ_list = !empty($arcadeGameChamps) ? json_decode($arcadeGameChamps, true) : $champ_list;

	return $champ_list;
}

function ArcadeLatestA($count=5,$curved=false, $rom = 0)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings;

	$code = '
	<div style="min-height: 1.2rem;padding: 0.15rem;margin-left:2px;position: relative;width: 100%;min-width: 100%;display: flex;flex-direction: column;justify-content: flex-start;align-items: flex-start;">';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 0];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeGamesLatest = cache_get_data('arcade_games_latestA' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
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
			cache_put_data('arcade_games_latestA' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeGamesLatest) ? json_decode($arcadeGamesLatest, true) : $rows;

	if (empty($rows)) {
		$found = 0;
		$code .= '
			<div class="centertext" style="clear: both;font-size: 0.8em;font-style: italic;font-weight: bold;flex: 1 1 auto;width: 100%;">' . $txt['arcade_scores_none'] . '</div>';
	}
	else {
		$found = count($rows);
		foreach ($rows as $row) {
			switch($i){
				case 0:
					$i = 1;
					$blend = 'padding: 0.175rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.055);';
					break;
				default:
					$i = 0;
					$blend = 'padding: 0.175rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.015);';
			}
			$row['thumbnail'] = !empty($row['thumbnail']) ? $row['thumbnail'] : 'nofileexist.jpg';
			$row['thumbnail_small'] = !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : 'nofileexist.jpg';
			$main = empty($row['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
			$mainUrl = empty($row['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
			$action = empty($row['rom_flag']) ? 'arcade' : 'retro_arch';
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
			$play = '<div style="display: inline;font-weight: bold;font-size: 80%;vertical-align: middle;"><a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $game_id . '">' . $game_name . '</a></div>';
			$playLink = sprintf($txt['arcade_scored_on'], strval($score), '&nbsp;' . $play);

			$code .= '
		<div style="font-size: 75%;display: flex;flex-direction: column;width: 100%;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
			<div style="display: flex;justify-content: space-between;">
				<div style="display: inline-flex;text-align: left;padding: 0rem 0.05rem;">
					<a href="' . $scripturl.'?action=' . $action . ';sa=play;game=' . $game_id . '">
						<img src="' . $game_pic . '" alt="' . $game_name . '" title="' . $game_name . '" style="border: 0px;width: 1.35rem;height: 1.35rem;" />
					</a>
				</div>
				<div style="display: inline-flex;padding: 0rem 0.05rem;"><a style="vertical-align: top;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $game_id . '"><b>' . $game_name . '</b></a></div>
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
				<div style="display: inline;min-width: 20%;width: 20%;white-space: nowrap;overflow: hidden;text-align: right;font-size:0.6rem;display: inline-flex;justify-content: right;font-weight: 500;">' . $time . '</div>
			</div>
			<div style="line-height: 0rem;"><span>&nbsp;</span></div>' . (!empty($_SESSION['isPortalMobile']) ? '<div><span>&nbsp;</span></div>' : '') . '
		</div>';
		}

		$code .= '
		<div class="' . ($curved ? 'plainbox' : 'windowbg') . '" id="arcadebox" style="display: none; position: fixed; left: 0px; top: 0px; width: 33%;' . ($curved ? '' : 'padding:5px') . '">
			<div id="arcadebox_html" style=""></div>
		</div>';
	}

	$code .= '
	</div>';

	return $code;
}

function ArcadeNewChampsA($count = 5, $rom = 0)
{
	global $smcFunc, $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings;

	$code = '
	<div style="min-height: 1.2rem;padding:0.15rem;margin-left:2px;position: relative;width: 100%;min-width: 100%;display: flex;flex-direction: column;justify-content: flex-start;align-items: flex-start;">';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 0];
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeGamesChamps = cache_get_data('arcade_new_champsA' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.game_directory, game.thumbnail, game.thumbnail_small, score.score, score.position, score.end_time,
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
			cache_put_data('arcade_new_champsA' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeGamesChamps) ? json_decode($arcadeGamesChamps, true) : $rows;

	if(empty($rows))
	{
		$found = 0;
		$code .= '
		<div class="centertext" style="clear: both;font-size: 0.8em;font-style: italic;font-weight: bold;flex: 1 1 auto;width: 100%;">' . $txt['arcade_scores_none'] . '</div>';
	}
	else
	{
		$found = count($rows);
		foreach ($rows as $row)
		{
			switch($i){
				case 0:
					$i = 1;
					$blend = 'padding: 0.175rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.055);';
					break;
				default:
					$i = 0;
					$blend = 'padding: 0.175rem;border-radius: 0.35rem;background-color: rgba(0, 0, 0, 0.015);';
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
		<div style="font-size: 75%;display: flex;flex-direction: column;width: 100%;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
			<div style="display: flex;justify-content: space-between;">
				<div style="display: inline-flex;text-align: left;padding: 0rem 0.05rem;">
					<a href="' . $scripturl.'?action=' . $action . ';sa=play;game=' . $game_id . '">
						<img src="' . $game_pic . '" alt="' . $game_name . '" title="' . $game_name . '" style="border: 0px;width: 1.35rem;height: 1.35rem;" />
					</a>
				</div>
				<div style="display: inline-flex;padding: 0rem 0.05rem 0rem 0.05rem;"><a style="vertical-align: top;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $game_id . '"><b>' . $game_name . '</b></a></div>
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
				<div style="display: inline;min-width: 20%;width: 20%;white-space: nowrap;overflow: hidden;text-align: right;font-size:0.6rem;display: inline-flex;justify-content: right;font-weight: 500;">' . $time . '</div>
			</div>
			<div style="line-height: 0rem;"><span>&nbsp;</span></div>' . (!empty($_SESSION['isPortalMobile']) ? '<div><span>&nbsp;</span></div>' : '') . '
		</div>';
		}
	}

	$code .= '
	</div>';

	return $code;
}

function ArcadeNewestGamesA($limit=5, $rom = 0)
{
	global $db_prefix, $scripturl, $arcadeModSettings, $smcFunc, $context, $txt, $settings;

	$newgames = '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 1];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';
	$newgame = '
			<div style="padding: 0.35rem;text-align: left;width: 100%;">';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeNewGames = cache_get_data('arcade_new_gamesA' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
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
			cache_put_data('arcade_new_gamesA' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeNewGames) ? json_decode($arcadeNewGames, true) : $rows;

	if(empty($rows))
	{
		$found = 0;
		$newgame .= '
				<div class="centertext" style="clear: both;font-size: 0.8em;font-style: italic;font-weight: bold;">' . $txt['arcade_no_games'] . '</div>';
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
			$newest_game['thumbnail'] = !empty($newest_game['thumbnail']) ? $newest_game['thumbnail'] : 'nofileexist.jpg';
			$newest_game['thumbnail_small'] = !empty($newest_game['thumbnail_small']) ? $newest_game['thumbnail_small'] : 'nofileexist.jpg';
			$action = empty($newest_game['rom_flag']) ? 'arcade' : 'retro_arch';
			$main = empty($newest_game['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
			$mainUrl = empty($newest_game['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
			$gameIcon = !is_dir($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail']) && file_exists($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail']) ? $mainUrl . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon2 = !is_dir($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail_small']) && file_exists($main . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail_small']) ? $mainUrl . '/' . $newest_game['game_directory'] . '/' . $newest_game['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon = $gameIcon == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameIcon2 : $gameIcon;

			$newest_game['game_name'] = strlen($newest_game['game_name']) >= $context['nameCharLength'] ? mb_substr($newest_game['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $newest_game['game_name'];
			$newgames .= '
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


		$newgame .= !empty($newgames) ? $newgames : '
				<div class="centertext" style="display: inline;clear: both;font-size: 0.8em;font-style: italic;font-weight: bold;">' . $txt['arcade_no_games'] . '</div>';
	}

	$newgame .= '
			</div>';

	return $newgame;
}

function ArcadePopularA($count = 5, $rom = 0)
{
	// Returns most played games
	global $db_prefix, $scripturl, $context, $arcadeModSettings, $smcFunc, $txt, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	list($rows, $i) = [[], 1];
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadePopular = cache_get_data('arcade_popularA' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
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
			cache_put_data('arcade_popularA' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadePopular) ? json_decode($arcadePopular, true) : $rows;

	$pop = '<div style="padding: 0.35rem;text-align: right;width: 100%;">';
	if(empty($rows))
	{
		$found = 0;
		$pop .= '<div class="centertext" style="clear: both;font-size: 0.8em;font-style: italic;font-weight: bold;">' . $txt['arcade_none_played'] . '</div>';
		return false;
	}
	else
	{
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

			$score['thumbnail'] = !empty($score['thumbnail']) ? $score['thumbnail'] : 'nofileexist.jpg';
			$score['thumbnail_small'] = !empty($score['thumbnail_small']) ? $score['thumbnail_small'] : 'nofileexist.jpg';
			$action = empty($score['rom_flag']) ? 'arcade' : 'retro_arch';
			$main = empty($score['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
			$mainUrl = empty($score['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
			$gameIcon = !is_dir($main . '/' . $score['game_directory'] . '/' . $score['thumbnail']) && file_exists($main . '/' . $score['game_directory'] . '/' . $score['thumbnail']) ? $mainUrl . '/' . $score['game_directory'] . '/' . $score['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon2 = !is_dir($main . '/' . $score['game_directory'] . '/' . $score['thumbnail_small']) && file_exists($main . '/' . $score['game_directory'] . '/' . $score['thumbnail_small']) ? $mainUrl . '/' . $score['game_directory'] . '/' . $score['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
			$gameIcon = $gameIcon == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameIcon2 : $gameIcon;
			$score['game_name'] = strlen($score['game_name']) >= $context['nameCharLength'] ? mb_substr($score['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $score['game_name'];
			$pop .= '
	<div style="width: 100%;padding: 0.4rem 0rem 0.4rem 0rem;display: flex;justify-content: space-between;' . (!empty($arcadeModSettings['arcade_alternateBGC']) ? $blend : '') . '">
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

		$pop .= empty($rows) ? $txt['arcade_popular_none'] : '';
		$pop .= '</div>';
		return $pop;

	}
}

function ArcadeRandomGamesA($limit=5, $rom = 0, $descript = false)
{
	global $db_prefix, $scripturl, $arcadeModSettings, $smcFunc, $txt, $settings;

	// skip using the cache for this
	$random = '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';
	$results = $smcFunc['db_query']('', '
		SELECT id_game, game_directory, thumbnail, thumbnail_small, game_name, enabled, description, rom_flag
		FROM {db_prefix}arcade_games
		WHERE enabled = 1' . $whererom . '
		ORDER BY RAND()
		LIMIT {int:num}',
		array(
			'num' => $limit,
			'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
		)
	);

	while ($rg = $smcFunc['db_fetch_assoc']($results))
	{
		$rg['thumbnail'] = !empty($rg['thumbnail']) ? $rg['thumbnail'] : 'nofileexist.jpg';
		$rg['thumbnail_small'] = !empty($rg['thumbnail_small']) ? $rg['thumbnail_small'] : 'nofileexist.jpg';
		$action = empty($rg['rom_flag']) ? 'arcade' : 'retro_arch';
		$main = empty($rg['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$mainUrl = empty($rg['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
		$random_name = '<div style="padding-top: 0.3rem;text-align:center;"><a id="randomgamenamelink" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $rg['id_game'] . '">' . $rg['game_name'] . '</a></div>';
		$gameIcon = !is_dir($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail']) && file_exists($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail']) ? $mainUrl . '/' . $rg['game_directory'] . '/' . $rg['thumbnail'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
		$gameIcon2 = !is_dir($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail_small']) && file_exists($main . '/' . $rg['game_directory'] . '/' . $rg['thumbnail_small']) ? $mainUrl . '/' . $rg['game_directory'] . '/' . $rg['thumbnail_small'] : $settings['default_images_url'] . '/arc_icons/Default.gif';
		$gameIcon = $gameIcon == $settings['default_images_url'] . '/arc_icons/Default.gif' ? $gameIcon2 : $gameIcon;
		$random .= '
	<div style="text-align:center;margin:10px;font-size:1.0em;" class="smalltext">
		<a id="randomgamelink" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $rg['id_game'] . '"><img id="randomgamethumbnail" style="height: 55px;width: 55px;" class="imgBorder" src="' . $gameIcon . '" title="' . strip_tags($rg['game_name']) . '" alt="' . $rg['game_name'] . '" /></a>' . $random_name . '
	</div>' . (!empty($descript) ? '
	<div style="display: flex;justify-content: center;">
		<div id="randomgamedescript" title="' . strip_tags($rg['description']) . '" style="scrollbar-width: thin;text-align: center;min-height: 5.8em;max-height: 5.8em;overflow-x: hidden;overflow-y: auto;font-size:0.95em;padding-left: 0.3em;vertical-align: bottom;display: inline-flex;text-align: center;margin: 0 auto;overflow-wrap: break-word;">
			' . $rg['description'] . '
		</div>
	</div>' : '');
	}

	$random = empty($random) ? '<div class="centertext" style="clear: both;font-size: 1.0em;font-style: italic;font-weight: bold;">' . $txt['arcade_no_games'] . '</div>' : $random;
	$smcFunc['db_free_result']($results);
	return $random;
}

function ArcadeDailyChallengeA($game=array(), $rom = 0)
{
	global $db_prefix, $arcadeModSettings, $scripturl, $context, $smcFunc, $txt;

	list($context['CH_error'], $display) = array('', '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	if(!empty($game))
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
		$sort = !empty($t) && !empty($t[0]) && $t[0] == 1 ? 'ASC' : 'DESC' ;
		$results = $smcFunc['db_query']('', "
			SELECT game.rom_flag, a.id_game, a.score, a.position, a.end_time,
			IFNULL(mem.id_member, {int:zero}) AS id_member, IFNULL(mem.real_name, a.player_name) AS real_name
			FROM  {db_prefix}arcade_scores AS a
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = a.id_member)
			LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = a.id_game)
			WHERE a.id_game = {int:id} AND FROM_UNIXTIME(a.end_time, '%y-%m-%d') = CURDATE()" . $whererom . "
			ORDER BY a.score {raw:sort}",
			array(
				'empty' => '',
				'zero' => '0',
				'sort' => $sort,
				'id' => $game['id'],
				'date' => date('ymd'),
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);

		$count = 0;
		$display = '';
		while ($time = $smcFunc['db_fetch_assoc']($results))
		{
			$count++;
			$display .= $count . '. ' . (isset($time['real_name']) ? $time['real_name'] : $txt['arcade_guest']) . ' - ' . $time['score'] . '<br />';
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
	elseif (empty($rom))
		$display = '
	<div class="centertext" style="clear: both;font-size: 0.8em;font-style: italic;font-weight: bold;">' . $txt['arcade_no_games'] . '</div>';

	return $display;
}

function ArcadeThumbnailListA($rom = 0)
{
	global $db_prefix, $context, $scripturl, $arcadeModSettings, $boardurl, $boarddir, $settings, $smcFunc;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : $rom;
	$rom2 = !empty($rom) ? '_rom' : '';
	$icons_per_row = 6;
	$rows = array();
	$gameTypes = !empty($arcadeModSettings['arcade_skin_a_romcenter_gametype']) ? intval($arcadeModSettings['arcade_skin_a_romcenter_gametype']) : 0;
	$gameOption = !empty($arcadeModSettings['arcade_skin_a_romcenter_type']) ? intval($arcadeModSettings['arcade_skin_a_romcenter_type']) : 0;
	$limit = !empty($arcadeModSettings['arcade_skin_a_romcenter_qty']) ? intval($arcadeModSettings['arcade_skin_a_romcenter_qty']) : 48;
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$romGamesUrl = !empty($arcadeModSettings['romGamesUrl']) ? $arcadeModSettings['romGamesUrl'] : $boardurl . '/ArcadeRetroArch/roms';
	$gamesUrl = !empty($arcadeModSettings['gamesUrl']) ? $arcadeModSettings['gamesUrl'] : $boardurl . '/Games';
	list($css, $images, $allImages) = array('', '', array());

	switch($gameTypes) {
		case 1:
			$where = ' WHERE game.rom_flag = {int:romflag}';
			$romflag = 0;
			break;
		case 2:
			$where = '';
			$romflag = 0;
			break;
		default:
			$where = ' WHERE game.rom_flag = {int:romflag}';
			$romflag = 1;
	}
	switch($gameOption) {
		case 1:
			$order = '';
			$query = '(favorite.id_member = ' . $user_info['id'] . ' AND enabled = 1)' . str_replace('WHERE', 'AND', $where);
			break;
		case 2:
			$order = 'ORDER BY game.id_game DESC';
			break;
		case 3:
			$order = 'ORDER BY game.id_game ASC';
			break;
		default:
			$order = 'ORDER BY RAND()';
	}

	$arcade = '
	<div style="overflow: hidden;">
		<div class="infiniteslide_box" style="margin: 0 auto;padding: 1px;overflow: hidden;">
			<ul id="js-infiniteslide" style="overflow: hidden;justify-content: space-between;align-items: center;">
				<li style="flex: 1 1 auto;align-self: center;justify-content: space-between;">';
	list($count1, $imgFile) = array(0, '');

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeThumbs = cache_get_data('arcade_thumbsA' . $gameOption . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		if ($gameOption != 1) {
			$request = $smcFunc['db_query']('', '
				SELECT game.id_game, game.game_name, game.game_directory, game.thumbnail, game.rom_flag
				FROM {db_prefix}arcade_games AS game' . $where . '
				' . $order . '
				LIMIT {int:mylimit}',
				array('mylimit' => $limit, 'romflag' => $romflag)
			);
		}
		else {
			$request = $smcFunc['db_query']('', '
				SELECT favorite.id_favorite, favorite.id_member, favorite.id_game, game.game_name, game.game_directory, game.thumbnail, game.thumbnail_small, game.rom_flag, game.enabled FROM {db_prefix}arcade_favorite AS favorite
				LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = favorite.id_game)
				WHERE ' . $query . ' ORDER BY favorite.id_favorite DESC
				LIMIT {int:mylimit}',
				array('romflag' => $romflag, 'mylimit' => $limit)
			);

		}
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_thumbsA' . $gameOption . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeThumbs) ? json_decode($arcadeThumbs, true) : $rows;

	foreach ($rows as $row) {
		$row['thumbnail'] = !empty($row['thumbnail']) ? $row['thumbnail'] : 'nofileexist.jpg';
		$row['thumbnail_small'] = !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : 'nofileexist.jpg';
		$thumbnail = $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		if (!empty($row['thumbnail'])) {
			$imgFile = isset($row['game_directory']) && $row['game_directory'] != '' ? $row['game_directory'] . '/' . $row['thumbnail'] : $row['thumbnail'];
			if (!empty($row['rom_flag']) && !is_dir($romGamesDirectory . '/' . $imgFile) && file_exists($romGamesDirectory . '/' . $imgFile)) {
				$thumbnail = $romGamesUrl . '/' . $imgFile;
			}
			elseif (empty($row['rom_flag']) && !is_dir($gamesDirectory . '/' . $imgFile) && file_exists($gamesDirectory . '/' . $imgFile)) {
				$thumbnail = $gamesUrl . '/' . $imgFile;
			}
		}
		if ((empty($row['thumbnail']) || $thumbnail == $settings['default_theme_url'] . '/images/arc_icons/Default.gif') && !empty($row['thumbnail_small'])) {
			$imgFile = isset($row['game_directory']) && $row['game_directory'] != '' ? $row['game_directory'] . '/' . $row['thumbnail_small'] : $row['thumbnail_small'];
			if (!empty($row['rom_flag']) && !is_dir($romGamesDirectory . '/' . $imgFile) && file_exists($romGamesDirectory . '/' . $imgFile)) {
				$thumbnail = $romGamesUrl . '/' . $imgFile;
			}
			elseif (empty($row['rom_flag']) && !is_dir($gamesDirectory . '/' . $imgFile) && file_exists($gamesDirectory . '/' . $imgFile)) {
				$thumbnail = $gamesUrl . '/' . $imgFile;
			}
		}

		$action = !empty($row['rom_flag']) ? 'retro_arch' : 'arcade';
		if ($count1 > ($icons_per_row - 1)) {
			$count1 = 0;
			$arcade .= '
				</li>
				<li style="flex: 1 1 auto;justify-content: space-between;align-self: center;">';
		}
		$count1++;
		$images .= '"' . (!empty($row['rom_flag']) ? $arcadeModSettings['romGamesUrl'] : $arcadeModSettings['gamesUrl']) . '/' . $imgFile . '",';
		$arcade .= '
					<div title="' . $row['game_name'] . '" id="gameThumbClass_' . $row['id_game'] . '" style="display: inline-flex;text-align: center;margin: 0 auto;border-spacing: 1px;padding: 0.2rem;max-width: 3.25rem;max-height: 3.25rem;min-width: 3.25rem;min-height: 3.25rem;width: 3.25rem;height: 3.25rem;">
						<div class="gameThumbClass" style="position: absolute;top: 0;bottom: 0;display: inline;min-width: 100%;min-height: 100%;width: 100%;height: 100%;"></div>
					</div>';
		$allImages[] = array('gameThumbClass_' . $row['id_game'], $thumbnail, $scripturl . '?action=' . $action . ';sa=play;game=' . $row['id_game']);
	}


	$images = rtrim(',', $images);

	$arcade .= '
				</li>
			</ul>
		</div>
	</div>';

	if (!empty($allImages)) {
		foreach ($allImages as $gameData) {
			$css .= '
			$("#' . $gameData[0] . '").css("background-image", "url(\'' . $gameData[1] . '\')");
			$("#' . $gameData[0] . '").css("background-repeat", "no-repeat");
			$("#' . $gameData[0] . '").css("background-size", "100% 100%");
			$("#' . $gameData[0] . '").css("filter", "contrast(125%)");
			$("#' . $gameData[0] . '").on( "click", function() { window.location.replace("' . $gameData[2] . '"); });';
		}
	}
	// $("#' . $classId . '").css("background-image", "url(\'' . $image . '\')");';
	$arcade .= '
		<script>
		$(document).ready(function() {' . $css . '
			$("#js-infiniteslide").infiniteslide({
				"speed": 40,
				"direction": "up",
				"pauseonhover": true,
				"responsive": false,
				"clone": 1
			});
		});
	</script>';

	if (!allowedTo('arcade_view') && $gameTypes == 0) {
		$arcade = '';
	}

	echo $arcade;
}

?>