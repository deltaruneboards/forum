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

// Defiant sub-routines for narrow themes
function Arcade3champsBlock($no)
{
	global $scripturl, $txt, $settings;

	$top_player = ArcadeLatestChamps($no);
	$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $no . '&nbsp;' . $txt['arcade_g_i_b_8'] . '
						</div>
					</div>';
	if ($top_player != false)
	{
		foreach ($top_player as $row)
		{
			$content.= '
					<div style="display: table;margin 0 auto;clear: both;width: 100%;padding-bottom: 3em;position: relative;">
						<div style="display: table-row;">
							<div style="display: table-cell;width: 25px;margin: 0 auto;">
								<div style="display:inline;text-align: left;position: relative;">
									<img style="border: 0px;width: 25px;height: 25px;vertical-align: bottom;padding-top: 0.5em;" src="' . $settings['default_images_url'] . '/arc_icons/cup_g.gif" alt="ico" />
								</div>
							</div>
							<div style="display: table-cell;vertical-align: top;text-align: left;height: 45px;">
								<div style="display:inline;position: relative;vertical-align: top;">
									<div style="display: inline;text-align: left;padding-bottom: 1em;vertical-align: top;line-height: 17px;font-size: 0.8em;" title="' . $row['real_name'] . '&nbsp;' . $txt['is_champ_of'] . '&nbsp;' . $row['game_name'] . '">' . $row['member_link'] . '&nbsp;' . $txt['is_champ_of'] . '&nbsp;' . $row['game_link'] . '</div>
								</div>
							</div>
						</div>
					</div>';
		}
	}
	$content.='
					<div style="display: block;">
						<div style="width: 100%;display: inline;padding: 3px 10px;">
							<div style="display: none;">&nbsp;</div>
							<div style="display: none;">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>';

	return $content;
}

function ArcadeRandomGameBlock($rom = 0)
{
	global $context, $txt, $arcadeModSettings, $settings;

	$gamex = small_game_query('ORDER BY RAND() LIMIT 0,1', $rom);
	$context['arcadeGameIconSizeDefiant'] = !empty($context['arcadeSkinAlt']) ? '40' : '60';
	foreach($gamex as $game)
	{
		$ratecode = '';
		$rating = $game['rating'];

		if ($rating > 0)
		{
			$ratecode = str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
			$ratecode .= str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
		}

		$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $txt['arcade_random_game'] . '
						</div>
					</div>
					<div style="display: block;margin 0 auto;clear: both;font-size: 0.7em;">
						<div style="display: inline;padding: 4px 7px 1px 1px;">
							<div class="centertext" style="display: inline;">
								<div><span style="display: none;">&nbsp;</span></div>
								<a href="' . $game['url']['play'] . '">
									<img id="gameid' . $game['id'] . '" style="width: ' . $context['arcadeGameIconSizeDefiant'] . 'px;height: ' . $context['arcadeGameIconSizeDefiant'] . 'px;" src="' . $game['thumbnail'] . '" alt="" onerror="alternateArcadeImg(\'gameid' . $game['id'] . '\');" title="' . $txt['arcade_play'] . '&nbsp;' . $game['name'] . '"/>
								</a>
								<div><span style="display: none;">&nbsp;</span></div>
								<div><span style="display: none;">&nbsp;</span></div>
								<div class="smalltext" style="font-size: small;padding-top: 0.5em;"><a href="' . $game['url']['play'] . '">' . $game['name'] . '</a></div>
							</div>
						</div>
					</div>';

		if ($rating > 0)
			$content .= '
					<div style="display: block;padding-top: 0.5em;">
						<div style="display: inline;padding: 3px 10px;width: 100%;" class="centertext">' . $ratecode . '</div>
					</div>';

		$content .= '
					<div style="display: block;padding-top: 0.5em;">
						<div style="display: inline;padding: 3px 10px;width: 100%;" class="centertext">
							<div class="smalltext">';

		if ($game['isChampion'])
			$content .= '
								<span style="font-size: small;"><strong>' . $txt['arcade_champion'] . ':</strong>&nbsp;' . $game['champion']['memberLink'] . '&nbsp;->&nbsp;' . $game['champion']['score'] . '</span>';

		else
			$content .= '<div style="display: inline;padding: 4px 10px 3px 3px;font-size: 0.8em;">' . (empty($rom) ? $txt['arcade_no_scores'] : '') . '</div>';

		$content .= '
							</div>
						</div>
					</div>
				</div>
			</div>';

		return $content;
	}
}


function ArcadeInfoNewestGames($no, $rom = 0)
{
	global $smcFunc, $scripturl, $settings, $txt, $arcadeModSettings, $boardurl, $context;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$rows = array();
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeNewest = cache_get_data('arcade_newestB' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$results = $smcFunc['db_query']('', '
			SELECT id_game, game_name, thumbnail, thumbnail_small, game_directory, rom_flag
			FROM {db_prefix}arcade_games
			WHERE enabled = 1' . $whererom . '
			ORDER BY id_game DESC
			LIMIT 0, {int:limit}',
			array(
			'limit' => $no,
			'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_newestB' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeNewest) ? json_decode($arcadeNewest, true) : $rows;

	$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $no . '&nbsp;' . $txt['arcade_LatestGames'] . '
						</div>
					</div>';
	foreach ($rows as $popgame)
	{
		$popgame['thumbnail'] = !empty($popgame['thumbnail']) ? $popgame['thumbnail'] : 'nofileexist.jpg';
		$popgame['thumbnail_small'] = !empty($popgame['thumbnail_small']) ? $popgame['thumbnail_small'] : 'nofileexist.jpg';
		$action = empty($popgame['rom_flag']) ? 'arcade' : 'retro_arch';
		$main = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$gamesUrl = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
		$gamesDir = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$popgameicoDir = empty($popgame['game_directory']) ? $popgame['thumbnail'] : $popgame['game_directory'] . '/' . $popgame['thumbnail'];
		$popgameicoDir2 = empty($popgame['game_directory']) ? $popgame['thumbnail_small'] : $popgame['game_directory'] . '/' . $popgame['thumbnail_small'];
		$popgameico = !is_dir($gamesDir . '/' . $popgameicoDir) && file_exists($gamesDir . '/' . $popgameicoDir) ? $gamesUrl . '/' . $popgameicoDir : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$popgameico2 = !is_dir($gamesDir . '/' . $popgameicoDir2) && file_exists($gamesDir . '/' . $popgameicoDir2) ? $gamesUrl . '/' . $popgameicoDir2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$popgameico = $popgameico == $settings['default_theme_url'] . '/images/arc_icons/Default.gif' ? $popgameico2 : $popgameico;
		$popgame['game_name'] = strlen($popgame['game_name']) >= $context['nameCharLength'] ? substr($popgame['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $popgame['game_name'];
		$content .='
					<div style="display: block;margin 0 auto;padding-bottom: 0.5em;margin 0 auto;">
						<div style="display: block;position: relative;padding-bottom: 0.3em;">
							<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $popgame['id_game'] . '">
								<img id="popg' . $popgame['id_game'] . '" style="text-align: center;border: 0px;width: 18px;height: 18px;vertical-align: middle;position: relative;margin 0 auto;" src="' . $popgameico. '" alt="&nbsp;" title="'.$txt['arcade_champions_play'].' '. $popgame['game_name'].'" onerror="alternateArcadeImg(\'popg' . $popgame['id_game'] . '\');" />
							</a>
						</div>
						<div style="display: block;vertical-align: middle;margin 0 auto;text-align: center;padding-bottom: 0.3em;position: relative;">
							<a style="display: inline;line-height: 17px;font-size: 0.9em;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $popgame['id_game'] . '">' . $popgame['game_name'] . '</a>
						</div>
					</div>';
	}

	$content .='
					<div style="display: block;">
						<div style="width: 100%;display: inline;padding: 3px 10px;">
							<div style="display: none;">&nbsp;</div>
							<div style="display: none;">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>' ;

	return $content;
}

function ArcadeInfoLongestChamps($no, $rom = 0)
{
	global $scripturl, $txt, $arcadeModSettings, $boardurl, $context, $settings;

	$mostgame = ArcadeStats_LongestChampions($no, $rom);
	$i = 0;
	$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $no . '&nbsp;' . $txt['arcade_g_i_b_11'] . '
						</div>
					</div>';
	foreach($mostgame as $popgame)
	{
		$popgame['thumbnail'] = !empty($popgame['thumbnail']) ? $popgame['thumbnail'] : 'nofileexist.jpg';
		$popgame['thumbnail_small'] = !empty($popgame['thumbnail_small']) ? $popgame['thumbnail_small'] : 'nofileexist.jpg';
		$action = empty($popgame['rom_flag']) ? 'arcade' : 'retro_arch';
		$main = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$gamesUrl = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
		$gamesDir = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$popgameicoDir = empty($popgame['game_directory']) ? $popgame['thumbnail'] : $popgame['game_directory'] . '/' . $popgame['thumbnail'];
		$popgameicoDir2 = empty($popgame['game_directory']) ? $popgame['thumbnail_small'] : $popgame['game_directory'] . '/' . $popgame['thumbnail_small'];
		$popgameico = !is_dir($gamesDir . '/' . $popgameicoDir) && file_exists($gamesDir . '/' . $popgameicoDir) ? $gamesUrl . '/' . $popgameicoDir : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$popgameico2 = !is_dir($gamesDir . '/' . $popgameicoDir2) && file_exists($gamesDir . '/' . $popgameicoDir2) ? $gamesUrl . '/' . $popgameicoDir2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$popgameico = $popgameico == $settings['default_theme_url'] . '/images/arc_icons/Default.gif' ? $popgameico2 : $popgameico;

		$popgame['game_name'] = strlen($popgame['game_name']) >= $context['nameCharLength'] ? substr($popgame['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $popgame['game_name'];
		$content .=	'
					<div style="display: block;margin 0 auto;clear: both;padding-bottom: 1.0em;position: relative;">
						<div style="display:inline;text-align: left;width: 30%;position: relative;float: left;padding-top: 1em;">
							<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $popgame['id'] . '">
								<img id="champgame' . $i . '" style="border: 0px;width: 17px;height: 17px;vertical-align: bottom;" src="' . $popgameico. '" alt="&nbsp;" title="'.$txt['arcade_champions_play'].'&nbsp;'. $popgame['game_name'].'" onerror="alternateArcadeImg(\'champgame' . $i . '\');" />
							</a>
						</div>
						<div style="display:inline;text-align: left;width: 45%;position: relative;vertical-align: top;float: left;top: 0px;margin-top: 0px;padding-left: 0.2em;">
							<div style="display: inline;text-align: left;vertical-align: middle;line-height: 17px;font-size: 1em;" title="' . $txt['arcade_g_i_b_5'] . '&nbsp;' . $popgame['duration'] . '">' . $popgame['member_link'] . '&nbsp;' . $txt['arcade_g_i_b_9'] . '&nbsp;' . $popgame['game_name'] . '</div>
						</div>
					</div>';
		$i++;
	}

	$content .='
					<div style="display: block;">
						<div style="width: 100%;display: inline;padding: 3px 10px;">
							<div style="display: none;">&nbsp;</div>
							<div style="display: none;">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>' ;

	return $content;
}

function ArcadeInfoMostPlayed($no, $rom = 0)
{
	global $scripturl, $txt, $arcadeModSettings, $boardurl, $settings;

	$mostgame = ArcadeStats_MostPlayed($no, $rom);
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $no . '&nbsp;' . $txt['arcade_g_i_b_10'] . '
						</div>
					</div>';
	foreach($mostgame as $popgame)
	{
		$popgame['thumbnail'] = !empty($popgame['thumbnail']) ? $popgame['thumbnail'] : 'nofileexist.jpg';
		$popgame['thumbnail_small'] = !empty($popgame['thumbnail_small']) ? $popgame['thumbnail_small'] : 'nofileexist.jpg';
		$action = empty($popgame['rom_flag']) ? 'arcade' : 'retro_arch';
		$main = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$gamesUrl = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesUrl'] : $arcadeModSettings['romGamesUrl'];
		$gamesDir = empty($popgame['rom_flag']) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory'];
		$popgameicoDir = empty($popgame['game_directory']) ? $popgame['thumbnail'] : $popgame['game_directory'] . '/' . $popgame['thumbnail'];
		$popgameicoDir2 = empty($popgame['game_directory']) ? $popgame['thumbnail_small'] : $popgame['game_directory'] . '/' . $popgame['thumbnail_small'];
		$popgameico = !is_dir($gamesDir . '/' . $popgameicoDir) && file_exists($gamesDir . '/' . $popgameicoDir) ? $gamesUrl . '/' . $popgameicoDir : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$popgameico2 = !is_dir($gamesDir . '/' . $popgameicoDir2) && file_exists($gamesDir . '/' . $popgameicoDir2) ? $gamesUrl . '/' . $popgameicoDir2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$popgameico = $popgameico == $settings['default_theme_url'] . '/images/arc_icons/Default.gif' ? $popgameico2 : $popgameico;
		$content .= '
					<div style="display: block;margin 0 auto;padding-bottom: 0.5em;margin 0 auto;">
						<div style="display: block;position: relative;padding-bottom: 0.3em;">
							<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $popgame['id'] . '">
								<img id="popg' . $popgame['id'] . '" style="text-align: center;border: 0px;width: 18px;height: 18px;vertical-align: middle;position: relative;margin 0 auto;" src="' . $popgameico. '" alt="&nbsp;" title="'.$txt['arcade_champions_play'].' '. $popgame['name'].'" onerror="alternateArcadeImg(\'popg' . $popgame['id'] . '\');" />
							</a>
						</div>
						<div style="display: block;vertical-align: middle;margin 0 auto;text-align: center;padding-bottom: 0.3em;position: relative;">
							<a style="display: inline;line-height: 17px;font-size: 0.9em;" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $popgame['id'] . '">' . $popgame['name'] . '</a>
						</div>
					</div>';
	}

	$content .='
					<div style="display: block;">
						<div style="width: 100%;display: inline;padding: 3px 10px;">
							<div style="display: none;">&nbsp;</div>
							<div style="display: none;">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>' ;

	return $content;
}

function ArcadeInfoBestPlayers($no, $rom = 0)
{
	global $scripturl, $txt, $settings;

	$top_player = ArcadeStats_BestPlayers($no, $rom);
	$i=0; //players position

	//array for icons
	$poz = array(
		'cup_g.gif',
		'cup_s.gif',
		'cup_b.gif',
		'trophy.png'
	);

	$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $no . '&nbsp;' .$txt['arcade_b3pb_1'] . '
						</div>
					</div>';

	if ($top_player != false)
	{
		foreach ($top_player as $row)
		{
			$content.= '
					<div style="display: table;margin 0 auto;clear: both;width: 100%;padding-bottom: 3em;position: relative;">
						<div style="display: table-row;">
							<div style="display: table-cell;width: 25px;margin: 0 auto;">
								<div style="display:inline;text-align: left;position: relative;">
									<img style="border: 0px;width: 25px;height: 25px;vertical-align: bottom;" src="' . $settings['default_images_url'] . '/arc_icons/' . $poz[$i] . '" alt="&nbsp;" />
								</div>
							</div>
							<div style="display: table-cell;vertical-align: top;text-align: left;height: 45px;">
								<div style="display:inline;position: relative;vertical-align: top;">
									<div style="display: inline;text-align: left;padding-bottom: 1em;vertical-align: top;line-height: 17px;font-size: 0.8em;" title="' . $row['name'] . '">' . $row['link'] . '&nbsp;' . $txt['arcade_b3pb_2'] . '&nbsp;' . $row['champions'] . '&nbsp;' . $txt['arcade_b3pb_3'] . '</div>
								</div>
							</div>
						</div>
					</div>';
			$i++;
			if ($i > 2)
				$poz[$i]= 'trophy.png';
		}
	}
	$content.='
					<div style="display: block;">
						<div style="width: 100%;display: inline;width: 100%;padding: 3px 10px;">
							<div style="display: none;">&nbsp;</div>
							<div style="display: none;">&nbsp;</div>
						</div>
					</div>
				</div>
			</div>';

	return $content;

}

function ArcadeGOTDBlock($rom = 0)
{
	global $context, $txt, $arcadeModSettings, $settings;

	list($ratecode, $game) = array('', getGameOfDay($rom));
	$context['arcadeGameIconSizeDefiant'] = !empty($context['arcadeSkinAlt']) ? '40' : '60';
	$content = '
			<div class="centertext" style="width: 100%;font-size: x-small;">
				<div style="display: block;width: 100%;border: 0px;border-collapse: collapse;">
					<div style="display: block;width: 100%;padding: 1em 0em 1em 0em;">
						<div class="centertext" style="display: inline;padding: 10px 10px 0px 0px;width: 100%;font-style: italic;font-weight: bold;text-decoration: underline;">
							' . $txt['arcade_game_of_day'] . '
						</div>
					</div>';
	$rating = !empty($game['rating']) ? (int)$game['rating'] : 0;

	if ($rating > 0) {
		$ratecode = str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/star.gif" alt="s" />' , $rating);
		$ratecode .= str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/star2.gif" alt="s" />' , 5 - $rating);
	}

	$content .= '
					<div style="display: block;margin 0 auto;clear: both;">
						<div style="display: inline;padding: 4px 7px 1px 1px;">
							<div class="centertext" style="display: inline;">';

	if (!empty($game['thumbnail']))
		$content .= '
								<div><span style="display: none;">&nbsp;</span></div>
								<a href="' . $game['url']['play'] . '">
									<img id="gotd' . $game['id'] . '" style="width: ' . $context['arcadeGameIconSizeDefiant'] . 'px;height: ' . $context['arcadeGameIconSizeDefiant'] . 'px;" src="' . $game['thumbnail'] . '" onerror="alternateArcadeImg(\'gotd' . $game['id'] . '\');" alt="" title="'.$txt['arcade_play'].' '.$game['name'].'" />
								</a>
								<div><span style="display: none;">&nbsp;</span></div>
								<div><span style="display: none;">&nbsp;</span></div>';

	$content .= '
								<div style="font-size: small;padding-top: 0.5em;">' . (!empty($game) && !empty($game['name']) ? '<a href="'. $game['url']['play']. '">'. $game['name']. '</a>' : '') . '</div>
							</div>
						</div>
					</div>';

	if ($rating > 0)
		$content .= '
					<div style="display: block;">
						<div style="display: inline;padding: 3px 10px;width: 100%;" class="centertext">' . $ratecode . '</div>
					</div>';

	$content .= '
					<div style="display: block;">
						<div style="display: inline;padding: 3px 10px;width: 100%;padding-top: 0.5em;" class="centertext">
							<div class="middletext">';

	if (!empty($game['is_champion']))
		$content .= '
								<span style="font-size: 0.9em;"><strong>' . $txt['arcade_champion'] . ':</strong> ' . $game['champion']['link']. '&nbsp;->&nbsp;' . $game['champion']['score'] . '</span>';
	else
		$content .= '<div style="display: inline;padding: 4px 10px 3px 3px;font-size: 0.9em;">' . (empty($rom) ? $txt['arcade_no_scores'] : '') . '</div>';

	$content .= '
							</div>
						</div>
					</div>
				</div>
			</div>';

	return $content;
}
?>