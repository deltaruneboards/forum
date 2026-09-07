<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_list()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings;

	// games per row
	list($row_tally, $tally, $code, $uri) = array(4, 0, '', '');
	$remainder = !empty($context['arcade']['games']) ? (count($context['arcade']['games'])) % $row_tally : 0;
	foreach(array('sa', 'sortby', 'dir', 'gametype', 'start', 'category', 'sort') as $setRequest)
		$uri .= isset($_REQUEST[$setRequest]) ? $setRequest . '=' . preg_replace('/[^a-zA-Z0-9\-\_]/', '', (string)$_REQUEST[$setRequest]) . ';' : '';
	$mobileTitle = !empty($_SESSION['arcade_isMobile']) ? 'clear: both;box-sizing: border-box;display: flex;width: 97.1vw;min-width: 97.1vw;max-width: 105vw;position: relative;border-radius: 8px;margin-left: auto;margin-right: auto;' : 'position: static;';
	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$perRow = !empty($arcadeModSettings['gamesPerRowVintage']) ? intval($arcadeModSettings['gamesPerRowVintage']) : 4;
	$perRow = abs($perRow);
	$perc = strval(100/$perRow) . '%';

	if (empty($arcadeModSettings['arcadeEnableDownload']))
		$arcadeModSettings['arcadeEnableDownload'] = false;
	if (empty($arcadeModSettings['arcadeEnableReport']))
			$arcadeModSettings['arcadeEnableReport'] = false;

	$arcade_buttons = array(
		$arcade_buttons['search'] = array(
			'text' => 'arcade_show_all',
			'image' => 'arcade_search.gif',
			'url' => $scripturl . '?action=' . $arcadeAction . ';category=all',
			'lang' => true
		),
		'random' => array(
			'text' => 'arcade_random_game',
			'image' => 'arcade_random.gif', // Theres no image for this included (yet)
			'url' => $scripturl . '?action=' . $arcadeAction . ';sa=play;random',
			'lang' => true
		),
		'favorites' => array(
			'text' => 'arcade_favorites_only',
			'image' => 'arcade_favorites.gif',
			'url' => $scripturl . '?action=' . $arcadeAction . ';favorites',
			'lang' => true
		)
	);

	// Header for Game listing
	echo '
		<div style="padding: 15px;"><span style="display: none;">&nbsp;</span></div>
		<div class="title_bar" id="arctoplist" style="' . $mobileTitle . '">
			<h3 class="titlebg centertext" style="vertical-align: middle;' . $mobileTitle . '">
				', $context['sort_arrow'], '<span style="clear: right;"><a href="', $context['sort_link'], '">', $_SESSION['arcade_gametype_select_title'], '</a></span>
			</h3>
		</div>
		<div class="windowbg" translate="no">';

	/*  loop through games for the list  */
	foreach ($context['arcade']['games'] as $key => $game)
	{
		if(empty($game['report_id']))
			$game['report_id'] = 0;
		$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && empty($game['allow_download']) ? false : $arcadeModSettings['arcadeEnableDownload'];
		$dlAction = !empty($game['rom_flag']) || !empty($game['rom_game']) ? 'retro_arch' : 'arcade';
		//strlen($game['name']) >= 100 ? $game['name'] = substr($game['name'], 0, 97) . '...' : '';
		// Show personal best and champion
		$romManage = !empty($game['rom_game']) || !empty($game['rom_flag']) ? 'rom' : '';
		//$arcadeAction = !empty($game['rom_game']) || !empty($game['rom_flag']) ? 'retro_arch' : 'arcade';
		$game['personal_best'] ? $your_best = $txt['your_score'] . $game['personal_best'] : $your_best=$txt['your_score'] . $txt['not_applicable'];
		$game['champion']['member_link'] == $txt['arcade_guest'] && empty($game['champion']['score']) ? $game['champion']['member_link'] = '' : '';
		$game['champion']['member_link'] ? $champ = sprintf($txt['champ'], $game['champion']['member_link']) : $champ = sprintf($txt['champ'], $txt['not_applicable']);
		$game['champion']['score'] ? $champ_score = $txt['champ_scoring'] . $game['champion']['score'] : $champ_score = $txt['champ_scoring'] . $txt['not_applicable'];
		(empty($game['description'])) ? $game['description'] = $txt['no_description'] : '';
		$game['description'] = stripslashes($game['description']);
		$enableThisGameDownload = empty($game['download']) && !allowedTo('arcade_admin') ? false : $enableGameDownload;
		$fav = '';

		if ($context['arcade']['can_favorite'])
		{
			$fav = '
						<a href="'. $game['url']['favorite']. '" onclick="arcade_favorite('. $game['id'] . '); return false;" style="height: 100%;vertical-align: middle;">';

			if (!$game['is_favorite'])
				$fav .= '
							<img id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star4.gif" style="filter: contrast(125%);image-rendering: high-quality;width: 15px;height: 13px;min-width: 15px;min-height: 13px;border: 0px;" alt="' . $txt['arcade_add_favorites'] . '" />' . '
						</a>';
			else
				$fav .= '
							<img id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star3.gif" style="filter: contrast(125%);image-rendering: high-quality;width: 15px;height: 13px;min-width: 15px;min-height: 13px;border: 0px;" alt="' . $txt['arcade_remove_favorite'] . '" />
							</a>';
		}

		$rate = '';
		if ($game['rating2'] > 0)
			$rate = str_repeat('<img style="vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star.gif" alt="*" />' , $game['rating2']) . str_repeat('<img style="vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5 - $game['rating2']);
		else
			$rate = str_repeat('<img style="vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5);

		if (empty($game['pdl_count']))
			$game['pdl_count'] = 0;

		$game['height'] = $game['height'] + 20;

		if (mb_strpos($game['submit_system'], 'html5') !== false)
		{
			$pop = '<a title="' . $txt['arcade_popplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']+30) . ',' . ($game['height']-20) . ', 0, false);">' . $txt['pdl_popplay'] . '</a>';
			$fullPop = '<a title="' . $txt['arcade_fullplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']+30) . ',' . ($game['height']-20) . ', 0, true);">' . $txt['arcadeFullPopup'] . '</a>';
		}
		else
		{
			$pop = '<a title="' . $txt['arcade_popplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']) . ',' . ($game['height']-20) . ', 0, false);">' . $txt['pdl_popplay'] . '</a>';
			$fullPop = '<a title="' . $txt['arcade_fullplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']) . ',' . ($game['height']-20) . ', 0, true);">' . $txt['arcadeFullPopup'] . '</a>';
		}

		$hiscr = '
							<a href="' . $game['url']['highscore'] . ';">' . $txt['arcade_viewscore'] . '</a>';
		$viewdl = '
							<b>&bull;</b>&nbsp;'.$your_best.'<br /><b>&bull;</b>&nbsp;'. $txt['num_plays']. '&#058;&nbsp;' . $game['plays'] . '<br />';

		$viewreport = empty($_SESSION['arcade_isMobilePlay']) ? '
							<b>&bull;</b>&nbsp;'. $pop . ' &#8212; ' . $fullPop . '<br />' : '';

		if  (($arcadeModSettings['arcadeEnableReport'] == true) && (allowedTo('arcade_report') == true) && empty($game['report_id']))
			$viewreport .= '<b>&bull;</b>&nbsp;<a onclick="gameReporting(\'' . $game['id'] . '\', \'' . (strlen($game['name']) < 71 ? $game['name'] : substr($game['name'], 0, 67) . '...') . '\')" id="report_game' . $game['id'] . '" href="javascript:void(0);">' . $txt['pdl_report'] . '</a><br />';

		if ((allowedTo('arcade_admin') == true) && ((int)$game['report_id'] > 0))
		{
			$viewreport .= '<b>&bull;</b>&nbsp;<a href="' . $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $game['id'] . '">' . $txt['show_pdl_report'] . '</a><br />';
			$gamename = '<span style="font-style: italic;"><a class="smalltext" href="' . $game['url']['play'] . '" title="' . $txt['alt_play'] . '">' . $game['name'] . ' </a></span>';
		}
		else
			$gamename = '<a class="smalltext" href="' . $game['url']['play'] . '" title="' . $txt['alt_play'] . '">' . $game['name'] . ' </a>';

		if (!empty($enableThisGameDownload))
			$viewdl .= '
							<b>&bull;</b>&nbsp;'. $txt['pdl_counter']. '&nbsp;' .$game['pdl_count'].'<br />
							<b>&bull;</b>&nbsp;<a data-gametype="' . (!empty($game['submit_system']) ? $game['submit_system'] : 'none') . '" data-internalname="' . $game['internal_name'] . '" href="' . $game['url']['download'] . '">' . $txt['arcade_download_game'] . '</a><br />';

		if (!empty($arcadeModSettings['arcadeDisplayType']) && !empty($txt['arcade_gamesavetype'][$game['submit_system']]) && empty($rom))
			$viewdl .= '
							<b>&bull;</b>&nbsp;'. $txt['arcade_typeset']. '&#058;&nbsp;' .$txt['arcade_gamesavetype'][$game['submit_system']].'<br />';
		if (!empty($arcadeModSettings['arcadeRomToggle']) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]) && empty($rom))
			$viewdl .= '
							<b>&bull;</b>&nbsp;'. $txt['arcade_romtypeset']. '&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '<br />';
		elseif (!empty($rom) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]))
			$viewdl .= '
							<b>&bull;</b>&nbsp;'. $txt['arcade_romtypeset']. '&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '<br />';

		if ($context['arcade']['can_admin_arcade'])
			$viewdl .= '<b>&bull;</b>&nbsp;<a href="' . $scripturl . '?action=admin;area=manage' . $romManage . 'games;sa=edit' . $romManage . ';game=' . $game['id'] . '">' . $txt['pdl_edit'] . '</a><br />';

		// four cells wide
		$tally++;
		$remainder = intval($tally % $row_tally);
		$catLink = '<a href="' . $game['category']['link'] . '">' . $game['category']['name'] . '</a>';
		$cat = !empty($game['category']['name']) ? '<b>&bull;</b>  ' . sprintf($txt['pdl_cat_list'],  $catLink) . '<br />' : '<b>&bull;</b>  ' . sprintf($txt['pdl_cat_list'], $txt['arcade_list_none']) . '<br />';
		if (empty($game['show_cover']) || empty($arcadeModSettings['arcade_coverEnableVintage'])) {
			$code .= '
			<div class="windowbg smalltext arcade_game_cell" style="flex: 1 0 ' . $perc . ';min-width: ' . $perc . ';max-width: ' . $perc . ';padding: 0.3rem;vertical-align: top;box-shadow:inset 0 0 0 1px;">
				<div class="cat_bar" style="position: relative;border-radius: 0 !important;" id="gameindex' . $game['id'] . '">
					<h3 class="catbg" style="vertical-align: middle;">
						<span style="display: inline-flex;width: 100%;height: 100%;position: relative;">
							<span class="button_strip_random" style="font-size: 10pt;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;text-align: left;width: 95%;padding-left: 0.1rem;" >' . $gamename . '</span>
							<span style="width: 5%;height: 100%;justify-content: right;align-self: center;">' . $fav . ' </span>
						</span>
					</h3>
				</div>
				<div style="display: flex;width: 100%;justify-content: flex-start;align-items: center;flex-direction: row;margin: 6px 0px;">
					<a style="display: inline;" href="' . $game['url']['play'] . '">
						<img id="arcadeThumb_' . $game['id'] . '" class="imgBorder" style="filter:contrast(125%);width: ' . $context['arcadeThumbWidth'] . 'px;height: ' . $context['arcadeThumbHeight'] . 'px;max-width: ' . $context['arcadeThumbWidth'] . 'px;max-height: ' . $context['arcadeThumbHeight'] . 'px;' . (!empty($arcadeModSettings['arcadeIconBorderRadius1']) ? 'border-radius: ' . $arcadeModSettings['arcadeIconBorderRadius1'] . '%;' : '') . '" src="' . $game['thumbnail'] . '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
					</a>
					<div style="display: inlline;min-width: 49%;font-size: 7pt;height:' . $context['arcadeThumbHeight']. 'px;margin: 4px 0px 5px 0.5rem;overflow: auto;word-break: break-word;">
						' . $game['description'] . '
					</div>
				</div>
				<div class="windowbg3" style="height: 1px"></div>
				<div class="smalltext" style="padding:4px 0px 4px 10px;line-height: 13px;">' . (!empty($arcadeModSettings['arcade_showListCat']) ? $cat : '') . '
					<b>&bull;</b>  ' . $champ . '<br />
					<b>&bull;</b> ' . $champ_score . '<br />
					' . $viewdl . '
					<b>&bull;</b> ' . $hiscr . '<br />
					' . rtrim($viewreport, '<br />') . ' <br /><br />
					<b>&bull;</b> ' . $rate . '
				</div>
			</div>';
		}
		else {
			$code .= '
			<div class="windowbg smalltext arcade_game_cell" style="flex: 1 0 ' . $perc . ';min-width: ' . $perc . ';max-width: ' . $perc . ';padding: 0.3rem;vertical-align: top;box-shadow:inset 0 0 0 1px;">
				<div class="cat_bar" style="position: relative;border-radius: 0 !important;" id="gameindex' . $game['id'] . '">
					<h3 class="catbg" style="vertical-align: middle;">
						<span style="display: inline-flex;width: 100%;height: 100%;position: relative;">
							<span class="button_strip_random" style="font-size: 10pt;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;text-align: left;width: 95%;padding-left: 0.1rem;" >' . $gamename . '</span>
							<span style="width: 5%;height: 100%;justify-content: right;align-self: center;">' . $fav . ' </span>
						</span>
					</h3>
				</div>';

			if (!empty($context['arcadeResizeCoverArt']))
				$code .= '
				<div onclick="arcadeplaygame(\'' . $game['url']['play'] . '\');" style="background-position: center;background-size: auto auto;background-repeat: no-repeat;width: 100%;height: auto;display: flex;justify-content: center;align-self: top;background-image: url(\'' . $game['cover_icon'] . '\');background-repeat: no-repeat;margin: 0.2rem 0;">
					<a style="display: flex;" href="' . $game['url']['play'] . '">
						<img id="arcadeCover_' . $game['id'] . '" class="imgBorder" style="filter:contrast(125%);width: auto;height: auto;max-width: 100%;max-height: 100%;' . (!empty($arcadeModSettings['arcadeIconBorderRadius1']) ? 'border-radius: ' . $arcadeModSettings['arcadeIconBorderRadius1'] . '%;' : '') . '" src="' . $game['cover_icon'] . '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
					</a>
				</div>';
			elseif (!empty($context['arcade_coverFullWidth']))
				$code .=  '
				<div id="arcadeCoverFull_' . $game['id'] . '" onclick="arcadeplaygame(\'' . $game['url']['play'] . '\');" style="object-fit: fill;background-size: 100% 100%;background-repeat: no-repeat;width: 100%;min-height: ' . $context['arcade_coverHeight'] . 'px;height: ' . $context['arcade_coverHeight'] . 'px;display: flex;justify-content: center;align-self: top;background-image: url(\'' . $game['cover_icon'] . '\');background-repeat: no-repeat;">
					<span style="width: 100%;height: 100%;"></span>
				</div>';
			else
				$code .= '
				<div id="arcadeCoverFull_' . $game['id'] . '" onclick="arcadeplaygame(\'' . $game['url']['play'] . '\');" style="background-position: center;background-size: ' . $context['arcade_coverHeight'] . 'px 100%;background-repeat: no-repeat;width: 100%;min-height: ' . $context['arcade_coverHeight'] . 'px;height: ' . $context['arcade_coverHeight'] . 'px;display: flex;justify-content: center;align-self: top;background-image: url(\'' . $game['cover_icon'] . '\');background-repeat: no-repeat;margin: 0.2rem 0;">
					<span style="width: 100%;height: 100%;"></span>
				</div>';

			$code .= '
				<div style="font-size: 7pt;height:55px; margin: 4px 0px 5px 0px;padding-left: 3px;overflow: auto;word-break: break-word;">' . $game['description'] . '</div>
				<div class="windowbg3" style="height: 1px"></div>
				<div class="smalltext" style="padding:4px 0px 4px 10px;line-height: 13px;">' . (!empty($arcadeModSettings['arcade_showListCat']) ? $cat : '') . '
					<b>&bull;</b>  ' . $champ . '<br />
					<b>&bull;</b> ' . $champ_score . '<br />
					' . $viewdl . '
					<b>&bull;</b> ' . $hiscr . '<br />
					' . rtrim($viewreport, '<br />') . ' <br /><br />
					<b>&bull;</b> ' . $rate . '
				</div>
			</div>';
		}
	}
	/*
				<a style="display: flex;" href="' . $game['url']['play'] . '">
							<img class="imgBorder" style="filter:contrast(125%);' . (empty($arcadeModSettings['arcadeResizeCoverArt']) ? 'width: 100%;height: ' . $context['arcade_coverHeight'] . 'px;max-width: 100%;min-width: 100%;max-height: ' . $context['arcade_coverHeight'] . 'px;' : '') . (!empty($arcadeModSettings['arcadeIconBorderRadius1']) ? 'border-radius: ' . $arcadeModSettings['arcadeIconBorderRadius1'] . '%;' : '') . '" src="' . $game['cover_icon'] . '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
						</a>
	*/
	if (!empty($remainder)) {
		for($i=0;$i<$remainder;$i++) {
			$code .= '
			<div class="windowbg smalltext arcade_game_cell" style="flex: 1 0 25%;min-width: 21%;max-width: 25%;padding: 5px;vertical-align: top;visibility: hidden;">
				<span></span>
			</div>';
		}
	}

	echo '
			<div style="display: flex;flex-wrap: wrap;width: 100%;justify-content: start;align-items: left;flex-direction: row;">', $code, '</div>
			<div style="flex: 1 0 25%;min-width: 21%;max-width: 25%;"><span style="display: none;">&nbsp;</span></div>
		</div>
		<div style="width: 100%;position: relative;clear: left;">
			<div class="arcade_pagesection" style="display: inline;">
				<div style="display: inline;padding-top: 15px;float: left;">', $context['page_index'], !empty($arcadeModSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
				<div style="display: inline;clear: right;float: right;">', template_button_strip($arcade_buttons, 'right', $rom), '</div>
			</div>
		</div>
		<div style="clear: both;padding-top: 40px;"><span style="display: none;">&nbsp;</span></div>';

	if (!empty($arcadeModSettings['arcadeShowIC']))
	{
		echo '
		<div class="cat_bar centertext" style="' . $mobileTitle . '">
			<h3 class="catbg centertext">
				', $txt['arcade_info_center'], '
			</h3>
		</div>
		<div class="arcade_up_contain windowbg arcade_skinc_ic_border">
			<div class="', ($context['arcade_smf_version'] == 'v2.1' ? 'inline' : 'innerframe'), '">
				<div id="upshrinkHeaderArcadeIC">';

		if (!empty($context['arcade']['latest_scores']))
		{
			echo '
					<h4 class="left">
						<span>', $txt['arcade_latest_scores'], '</span>
					</h4>
					<div class="smalltext" style="padding-left: 15px;word-wrap: break-word;word-break: hyphenate;overflow: hidden;">';

			foreach ($context['arcade']['latest_scores'] as $score)
				echo '
						<span>', sprintf($txt['arcade_latest_score_item'], $scripturl . '?action=' . $arcadeAction . ';sa=play;game=' . $score['game_id'], $score['name'], $score['score'], $score['memberLink']), '</span><br />
						<span style="padding-left: 5px;padding-bottom: 1px;">',  $score['time'], '</span><br />';

			echo '
					</div>';
		}
		elseif (empty($rom))
			echo '
					<h4 class="left">
						<span>', $txt['arcade_latest_scores'], '</span>
					</h4>
					<div class="smalltext" style="padding-left:15px;">', $txt['arcade_no_scores'], '</div>';

		echo '
					<h4 class="left clear" style="padding-top:10px;">
						<span>', $txt['arcade_game_highlights'], '</span>
					</h4>
					<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: hyphenate;overflow: auto;">';

		if ($context['arcade']['stats']['longest_champion'] !== false && !empty($context['arcade']['stats']['longest_champion']['member_link']) && !empty($context['arcade']['stats']['longest_champion']['game_link']))
			echo '
						<span>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</span><br />';

		if ($context['arcade']['stats']['most_played'] !== false && !empty($context['arcade']['stats']['most_played']['link']))
			echo '
						<span style="padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</span><br />';

		if ($context['arcade']['stats']['best_player'] !== false && !empty($context['arcade']['stats']['best_player']['link']))
			echo '
						<span style="padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</span><br />';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
						<span style="padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</span>';

		echo '
					</div>
				</div>';

		if (!empty($arcadeModSettings['arcadeShowOnline']))
			echo '
				<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
				<div class="smalltext">
					<h4 class="left">
						<span class="icon" style="vertical-align: middle;"><img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['default_images_url'], '/arc_icons/online.gif" alt="" /></span>
						<span>' . $txt['arcade_users'] . '</span>
					</h4>
				</div>
				<div class="smalltext" style="padding-bottom: 3px;">' . $context['arcade_online_link'] . '</div>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: keep-all;overflow: auto;">' . implode(', ', $context['arcade_viewing']) . '</div>';

		echo '
			</div>
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	}
	elseif (!empty($arcadeModSettings['arcadeShowOnline']))
		echo'
		<div class="cat_bar" style="' . $mobileTitle . '">
			<h3 class="catbg" style="vertical-align: middle;">
				<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['default_images_url'], '/arc_icons/online.gif" alt="" />
				<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_users'], '</span>
			</h3>
		</div>
		<div class="arcade_up_contain windowbg">
			<div class="', ($context['arcade_smf_version'] == 'v2.1' ? 'inline' : 'innerframe'), '" style="border-radius: 5px;">
				<div class="smalltext" style="padding-bottom: 3px;border: 0px;">' . $context['arcade_online_link'] . '</div>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;word-break: keep-all;overflow: auto;border: 0px;">' . implode(', ', $context['arcade_viewing']) . '</div>
			</div>
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	else
		echo '
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';

	echo '
		<script>
			function arcadeplaygame(gamelink) {
				window.location.href = gamelink;
			}
			function gameReporting(gameid, gamename) {
				document.getElementById("report_game" + gameid).removeAttribute("href");
				var reason = prompt(\'' . $txt['pdl_report_reason_name'] . ' \' + gamename + \'' . '\r\n\r\n' . $txt['pdl_report_reason_input'] . '\');
				if (reason)
				{
					var url = "'. $scripturl . '?action=' . $arcadeAction . ';sa=report;game=" + gameid + ";' . $context['session_var'] . '=' . $context['session_id'] . '";
					var data = "reason=" + encodeURIComponent(reason);
					var callback = function(data){console.log(data);};
					arcadeAjaxSend(url, data, callback);
					setTimeout(function(){window.location.href = "' . $scripturl . '?action=' . $arcadeAction . ';' . $uri . '#gameindex" + gameid;}, 1000);
				}
				else
					return false;
			}
		</script>';
}

?>