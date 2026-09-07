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
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings, $boardurl;

	$arcadeModSettings['arcadeListGenericExtraBg'] = !empty($arcadeModSettings['arcadeListGenericExtraBg']) ? abs(intval($arcadeModSettings['arcadeListGenericExtraBg'])) : 0;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$romManage = !empty($_SESSION['arcade_rom_initiate']) ? 'rom' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	switch($arcadeModSettings['arcadeListGenericExtraBg'])
	{
		case 1:
			list($windowbg, $windowbg2) = array('windowbg2', 'windowbg2');
			break;
		case 2:
			list($windowbg, $windowbg2) = array('windowbg', 'windowbg2');
			break;
		case 3:
			list($windowbg, $windowbg2) = array('windowbg2', 'windowbg');
			break;
		default:
			list($windowbg, $windowbg2) = array('windowbg', 'windowbg');
	}

	list($uri, $borderDbl) = array('', (!empty($arcadeModSettings['arcadeListGenericExtraBorder']) ? 'border-style: double !important;' : ''));
	foreach(array('sa', 'sortby', 'dir', 'gametype', 'start', 'category', 'sort') as $setRequest)
		$uri .= isset($_REQUEST[$setRequest]) ? $setRequest . '=' . preg_replace('/[^a-zA-Z0-9\-\_]/', '', (string)$_REQUEST[$setRequest]) . ';' : '';

	$arcade_buttons = array(
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
		),
	);

	if (isset($context['arcade']['search']) && $context['arcade']['search'])
		$arcade_buttons['search'] = array(
			'text' => 'arcade_show_all',
			'image' => 'arcade_search.gif',
			'url' => $scripturl . '?action=' . $arcadeAction
		);


	// Header for Game listing
	echo '
		<div style="width: 100%;position: relative;clear: left;">
			<div class="arcade_pagesection" style="display: inline;">
				<div style="display: inline;padding-top: 15px;float: left;">', ($context['arcade_smf_version'] == 'v2.1' ? '' : $txt['arcade_number_pages'] . '&nbsp;'), $context['page_index'], !empty($arcadeModSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#bot"><b>' . $txt['go_down'] . '</b></a>' : '', '</div>
				<div style="display: inline;clear: right;float: right;">', template_button_strip($arcade_buttons, 'right', $rom), '</div>
			</div>
		</div>
		<div class="game_table" id="arctoplist" translate="no">
			<div style="display: table;border: 0px;border-collapse: collapse;width: 100%;" class="table_grid">
				<div style="display: table-header-group;">';

	// Is there games?
	if (!empty($context['arcade']['games']))
	{
		echo '

					<div style="display: table-cell;border-radius: 4px 0px 0px 4px;vertical-align: middle;" class="first_th windowbg2">', $context['sort_arrow'], '</div>
					<div style="display: table-cell;" class="windowbg2 centertext"><a href="', $scripturl, '?action=' . $arcadeAction . ';sa=list;sortby=', ($context['arcade']['games'][0]['sort_by'] == 'a2z' ? 'z2a;#arctoplist' : 'a2z;#arctoplist'), '">', $_SESSION['arcade_gametype_select_title'], '</a></div>
					<div style="display: table-cell;" class="windowbg2"><span style="display: none;">&nbsp;</span></div>
					<div style="display: table-cell;width: 10%;" class="windowbg2 centertext"><a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=myscore' . ($context['arcade']['games'][0]['sort_by'] == 'myscore' ? $context['changedir'] : ';dir=desc;#arctoplist') . '">' . $txt['arcade_personal_best'] . '</a></div>
					<div style="display: table-cell;border-radius: 0px 4px 4px 0px;width: 10%;" class="last_th windowbg2 centertext"><a href="', $scripturl, '?action=' . $arcadeAction . ';sa=list;sortby=champs', ($context['arcade']['games'][0]['sort_by'] == 'champs' ? $context['changedir'] : ';dir=desc;#arctoplist'), '">', $txt['arcade_champion'], '</a></div>';
	}
	else
	{
		echo '
					<div style="display: table-cell;border-radius: 4px 0px 0px 4px;width: 8%;" class="titlebg first_th">&nbsp;</div>
					<div style="display: table-cell;" class="windowbg2"><span style="display: none;">&nbsp;</span></div>
					<div style="display: table-cell;" class="titlebg smalltext centertext"><strong>', $txt['arcade_no_games'], '</strong></div>
					<div style="display: table-cell;" class="windowbg2"><span style="display: none;">&nbsp;</span></div>
					<div style="display: table-cell;width: 8%;border-radius: 0px 4px 4px 0px;" class="titlebg last_th">&nbsp;</div>';
	}

	echo '
				</div>
				<div style="display: table-row-group;">';

	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && !allowedTo('arcade_download') ? false : $arcadeModSettings['arcadeEnableDownload'];
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
	foreach ($context['arcade']['games'] as $key => $game)
	{
		$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && empty($game['allow_download']) ? false : $arcadeModSettings['arcadeEnableDownload'];
		list($report, $show_report) = array(false, false);
		$game['report_id'] = !empty($game['report_id']) ? (int)$game['report_id'] : 0;
		$game['pdl_count'] = !empty($game['pdl_count']) ? (int)$game['pdl_count'] : 0;
		$game3 = !empty($game['id']) ? $game['id'] : '';
	    list($game_buttons, $game_type) = array(array(), '');
		$enableThisGameDownload = empty($game['download']) && !allowedTo('arcade_admin') ? false : $enableGameDownload;
		$dl_count = '
					<div style="display: inline;">
						<span style="vertical-align: bottom;">' . $game['pdl_count'] . '</span>
						<img id="arcadeThumb_' . $game['id'] . '" src="' . $settings['default_theme_url']. '/images/arc_icons/download_icon.png" alt="' . $txt['pdl_counter'] . '" title="' . $txt['pdl_counter'] . '" style="width: 18px;height: 19px;vertical-align: middle;padding-left: 0.1em;" />
					</div>';
		$dlgame = array('url' => $game['url']['download'], 'data-internalname' => $game['internal_name'], 'data-gametype' => (!empty($game['submit_system']) ? $game['submit_system'] : 'none'), 'text' => 'pdl_button1', 'image' => 'arcade_download.gif', 'lang' => true);
		$report = empty($game['report_id']) ? array('url' => $scripturl . '?action=' . $arcadeAction . ';sa=report;game=' . $game3 . ';name=' . rawurlencode($game['name']) . ';' . $context['session_var'] . '=', $context['session_id'] . ';', 'text' => 'pdl_report', 'image' => 'arcade_report.gif', 'lang' => true) : '';
		$edit_game2 = array('url' => $scripturl . '?action=admin;area=manage' . $romManage . 'games;sa=edit' . $romManage . ';game=' . $game3, 'text' => 'pdl_edit', 'image' => 'arcade_edit.gif', 'lang' => true);
		if ($game['submit_system'] == 'html5')
		{
			$popup = array('url' => 'javascript:void(0)', 'text' => 'pdl_popplay', 'image' => 'arcade_popup.gif', 'lang' => true, 'custom' => 'onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']+30) . ',' . ($game['height']) . ',0,false)"');
			$fullPopup = array('url' => 'javascript:void(0)', 'text' => 'arcadeFullPopup', 'image' => 'arcade_popup.gif', 'lang' => true, 'custom' => 'onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']+30) . ',' . ($game['height']) . ',0,true)"');
		}
		else
		{
			$popup = array('url' => 'javascript:void(0)', 'text' => 'pdl_popplay', 'image' => 'arcade_popup.gif', 'lang' => true, 'custom' => 'onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']) . ',' . ($game['height']) . ',0,false)"');
			$fullPopup = array('url' => 'javascript:void(0)', 'text' => 'arcadeFullPopup', 'image' => 'arcade_popup.gif', 'lang' => true, 'custom' => 'onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']) . ',' . ($game['height']) . ',0,true)"');
		}
		if ((allowedTo('arcade_admin') == true) && !empty($game['report_id']))
		{
			$show_report = array('url' => $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $game3, 'text' => 'show_pdl_report', 'image' => 'arcade_show_report.gif', 'lang' => true);
			$gamename = '<span style="font-style: italic;"><a href="' . $game['url']['play'] . '">' . $game['name'] . '</a></span>';
		}
		else
			$gamename = '<span><a href="' . $game['url']['play'] . '">' . $game['name'] . '</a></span>';

		if ($game['highscore_support'])
			$highscore = array('url' => $game['url']['highscore'], 'text' => 'arcade_viewscore', 'image' => 'arcade_highscore.gif', 'lang' => true);
		$descriptLength = !empty($arcadeModSettings['arcadeDescriptLength']) ? $arcadeModSettings['arcadeDescriptLength'] : 2000;
		if (!empty($game['description']))
		{
			$game['description'] = wordwrap($game['description'], 140);
		}

		if (!empty($arcadeModSettings['arcadeDisplayType']) && !empty($txt['arcade_gamesavetype'][$game['submit_system']]) && empty($rom))
			$game_type .= '
							<div style="clear: both;font-size: 8pt;font-style: oblique 10deg;position: relative;padding: 2px 4px 2px 0px;">'. $txt['arcade_typeset']. '&#058;&nbsp;' .$txt['arcade_gamesavetype'][$game['submit_system']].'</div>';
		if (!empty($arcadeModSettings['arcadeRomToggle']) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]) && empty($rom))
			$game_type .= '
							<div style="clear: both;font-size: 8pt;font-style: oblique 10deg;position: relative;padding: 2px 4px 2px 0px;">'. $txt['arcade_romtypeset']. '&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '</div>';
		elseif (!empty($rom) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]))
			$game_type .= '
							<div style="clear: both;font-size: 8pt;font-style: oblique 10deg;position: relative;padding: 2px 4px 2px 0px;">'. $txt['arcade_romtypeset']. '&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '</div>';

		echo '
					<div id="gameindex' . $game3 . '" style="display: table-row;' . (!empty($arcadeModSettings['arcadeListHorizontalDivision']) ? 'border-bottom: groove 1px;' : '') . '">
						<div style="' . $borderDbl . 'display: table-cell;padding: 1em;width: 10%;vertical-align: middle;" class="' . $windowbg2 . ' centertext">', $game['thumbnail'] !== '' ? '
							<a href="' . $game['url']['play'] . '"><img class="board_icon" style="filter:contrast(125%);image-rendering: high-quality;width: ' . $context['arcadeThumbWidth'] . 'px;height: ' . $context['arcadeThumbHeight'] . 'px;max-width: ' . $context['arcadeThumbWidth'] . 'px;max-height: ' . $context['arcadeThumbHeight'] . 'px;' . (!empty($arcadeModSettings['arcadeIconBorderRadius0']) ? 'border-radius: ' . $arcadeModSettings['arcadeIconBorderRadius0'] . '%;' : '') . '" src="' . $game['thumbnail'] . '" alt="" /></a>' : '', '
						</div>
						<div style="' . $borderDbl . 'display: table-cell;vertical-align: top;max-height: 60px;padding: 0.5em;text-overflow: ellipsis;" class="' . $windowbg . '">', $gamename, $game_type, !empty($game['description']) ? '<div><span class="game_description_length" id="gameDescript0_' . $game['id'] . '" onclick="gameDescriptOnclick(this)"><span style="display: inline;" id="gameDescript1_' . $game['id'] . '">' . (strlen($game['description']) > $descriptLength ? substr($game['description'], 0, $descriptLength - 2) . '...' : $game['description']) . '</span><span style="display: none;" id="gameDescript2_' . $game['id'] . '">' . $game['description'] . '</span></span></div>' : '', '
							<div class="smalltext game_buttons">
								<div class="game_list_left" style="display: inline;">';

		if (allowedTo('arcade_download') && !empty($enableThisGameDownload))
			$game_buttons['download'] = $dlgame;

		if ($context['arcade']['can_admin_arcade'])
			$game_buttons['edit'] = $edit_game2;

		if  ((!empty($arcadeModSettings['arcadeEnableReport'])) && (allowedTo('arcade_report') == true) && !empty($report))
			$game_buttons['report'] = $report;

		// Does this game support highscores?
		if ($game['highscore_support'])
			$game_buttons['highscore'] = $highscore;

		if (empty($_SESSION['arcade_isMobilePlay'])) {
			$game_buttons['popup'] = $popup;
			$game_buttons['fullPopup'] = $fullPopup;
		}

		if ((allowedTo('arcade_admin') == true) && ((int)$game['report_id'] > 0))
			$game_buttons['show_report'] = $show_report;

		if (!empty($game_buttons))
			echo template_button_strip($game_buttons, 'left', array('id' => 'game_buttons_' . $game['id']), $rom);

		echo '
								</div>
							</div>';
		// Rating
		if ($game['rating2'] > 0)
			echo '
						<div style="inline-flex;position: relative;text-align: right;vertical-align: bottom;">', str_repeat('<img style="vertical-align: bottom;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star.gif" alt="*" />' , $game['rating2']), str_repeat('<img style="vertical-align: bottom;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5 - $game['rating2']), '</div>';

		echo '
						</div>
						<div class="' . $windowbg2 . '" style="' . $borderDbl . 'display: table-cell;text-align: right;padding: 0.5em;max-height: 60px;vertical-align: middle;margin: 0 auto;width: 10%;">';

		/* Favorite link (if can favorite) */
		if ($context['arcade']['can_favorite'])
			echo '
							<div style="display: flex;flex-direction: column;justify-content: space-between;position: relative;padding-bottom: 0.5em;margin: 0 auto;height: auto;vertical-align: middle;">
								<a href="', $game['url']['favorite'], '" onclick="arcade_favorite(', $game['id'] , '); return false;">
			', !$game['is_favorite'] ? '
									<img title="[ &#8593; ]" alt="[+]" style="vertical-align: middle;" id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star4.gif" />' : '
									<img title="[ &#8595; ]" alt="[-]" style="vertical-align: middle;" id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star3.gif" />', '
								</a>
							</div>';

		// Category
		if (!empty($game['category']['name']) && !empty($arcadeModSettings['arcade_showListCat']))
		{
			echo '
							<div style="display: flex;flex-direction: column;justify-content: space-between;position: relative;padding-bottom: 0.5em;margin: 0 auto;height: auto;vertical-align: middle;">
								<a href="', $game['category']['link'], '">', (!empty($game['category']['icon']) ? '<img class="icon" src="' . $settings['default_images_url'] . '/arc_icons/' . $game['category']['icon'] . '" alt="' . $game['category']['name'] . '" title="' . $game['category']['name'] . '" style="vertical-align: middle;width: 16px;height: 16px;" />' : $game['category']['name']), '</a>
							</div>';

		}

		if ($arcadeModSettings['arcadeEnableDownload'])
			echo '
							<div style="display: flex;flex-direction: column;justify-content: space-between;position: relative;padding-bottom: 0.5em;margin: 0 auto;height: auto;vertical-align: middle;">
								', $dl_count, '
							</div>';

		echo '
						</div>';

		// Show personal best and champion only if game supports highscores
		if ($game['is_champion'] && !$user_info['is_guest'])
			echo '
						<div style="' . $borderDbl . 'display: table-cell;vertical-align: middle;margin: 0 auto;" class="' . $windowbg . ' centertext">
							', $game['is_personal_best'] ? $game['personal_best'] :  $txt['arcade_no_scores'], '
						</div>';
		else
			echo '
						<div style="' . $borderDbl . 'display: table-cell;vertical-align: middle;margin: 0 auto;" class="' . $windowbg . ' centertext">
							<span style="display: none;">&nbsp;</span>
						</div>';

		if ($game['is_champion'])
			echo '
						<div style="' . $borderDbl . 'display: table-cell;vertical-align: middle;margin: 0 auto;" class="' . $windowbg2 . ' centertext">
							', $game['champion']['member_link'], '<div>', $game['champion']['score'], '</div>
						</div>';
		elseif (!$game['highscore_support'])
			echo '
						<div style="' . $borderDbl . 'display: table-cell;vertical-align: middle;margin: 0 auto;" class="' . $windowbg2 . ' centertext">', $txt['arcade_no_highscore'], '</div>';
		else
			echo '
						<div style="' . $borderDbl . 'display: table-cell;vertical-align: middle;margin: 0 auto;" class="' . $windowbg2 . ' centertext">', $txt['arcade_no_scores'], '</div>';

		echo '
					</div>';
		}

	echo '
				</div>
			</div>
		</div>
		<div class="arcade_pagesection" style="display: inline;width: 100%;">
			<div style="text-align:left;display: inline;">', ($context['arcade_smf_version'] == 'v2.1' ? '' : $txt['arcade_number_pages'] . '&nbsp;'), $context['page_index'], !empty($arcadeModSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</div>
		</div>
		<div style="clear: both;padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>';

	if (!empty($arcadeModSettings['arcadeShowIC']))
	{
		echo '
		<div class="cat_bar centertext">
			<h3 class="catbg centertext">
				', $txt['arcade_info_center'], '
			</h3>
		</div>
		', $context['arcade_smf_version'] == 'v2.1' ? '
		<div class="arcade_up_contain windowbg">' :
		'<span class="clear upperframe"><span>&nbsp;</span></span>
		<div class="roundframe">', '
			<div class="inline">
				<div id="upshrinkHeaderArcadeIC">';

		if (!empty($context['arcade']['latest_scores']))
		{
			echo '
					<h4 class="left">
						<span>', $txt['arcade_latest_scores'], '</span>
					</h4>
					<div class="smalltext" style="padding-left: 15px;word-wrap: break-word;keep-all: hyphenate;overflow: hidden;">';

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
					<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: hyphenate;overflow: auto;">';

		if ($context['arcade']['stats']['longest_champion'] !== false && !empty($context['arcade']['stats']['longest_champion']['member_link']) && !empty($context['arcade']['stats']['longest_champion']['game_link']))
			echo '
						<div>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</div>';

		if ($context['arcade']['stats']['most_played'] !== false && !empty($context['arcade']['stats']['most_played']['link']))
			echo '
						<div style="padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</div>';

		if ($context['arcade']['stats']['best_player'] !== false && !empty($context['arcade']['stats']['best_player']['link']))
			echo '
						<div style="padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
						<div style="padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</div>';

		echo '
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
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	}
	elseif (!empty($arcadeModSettings['arcadeShowOnline']))
		echo'
		<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>
		<div class="cat_bar">
			<h3 class="catbg" style="vertical-align: middle;">
				<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);vertical-align: middle;" src="', $settings['default_images_url'], '/arc_icons/online.gif" alt="" />
				<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_users'], '</span>
			</h3>
		</div>
		', $context['arcade_smf_version'] == 'v2.1' ? '
		<div class="arcade_up_contain windowbg">' :
		'<span class="clear upperframe"><span>&nbsp;</span></span>
		<div class="roundframe">', '
			<div class="innerframe" style="border-radius: 5px;">
				<div class="smalltext" style="padding-bottom: 3px;border: 0px;">' . $context['arcade_online_link'] . '</div>
				<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: keep-all;overflow: auto;border: 0px;">' . implode(', ', $context['arcade_viewing']) . '</div>
			</div>
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	else
		echo '
		<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';

	echo '
		<script type="text/javascript">
			function arcadeReportScript()
			{
				var i = 0, reportClass = "", gameReports = [], myIdX = [], myNameX = [], myId = [], myName = [], arcbreakit = false;
				gameReports = document.querySelectorAll("[class*=button_strip_report]");
				for(i=0;i<gameReports.length;i++)
				{
					myIdX[i] = gameReports[i].href.match(/game=.+?(?=;)/);
					myNameX[i] = gameReports[i].href.match(/name=.+?(?=;)/);
					myId[i] = myIdX[i][0].substr(5, myIdX[i][0].length-1);
					myName[i] = decodeURIComponent(myNameX[i][0].substr(5, myNameX[i][0].length-1)).replace(/[^ -a-z0-9]/ig, "_");
					gameReports[i].id = "reportid_" + myId[i];
					gameReports[i].title = "' . $txt['pdl_report_reason'] . '" + myName[i];
					gameReports[i].onclick = function(event) {
						arcadeReportAjax(event, this.id, this.title);arcbreakit = true;
					};
					if (arcbreakit)
						break;
				}
			}
			function arcadeReportAjax(arc_event, gameid, gamename)
			{
				arc_event.preventDefault();
				var gameid = gameid.replace(/[^0-9]/g, "");
				var reason = prompt(\'' . $txt['pdl_report_reason_name'] . ' \' + gamename + \'' . '\r\n\r\n' . $txt['pdl_report_reason_input'] . '\');
				if (reason)
				{
					var url = "'. $scripturl . '?action=' . $arcadeAction . ';sa=report;game=" + gameid + ";' . $context['session_var'] . '=' . $context['session_id'] . '";
					var data = "reason=" + encodeURIComponent(reason).replace(/\'/g, "%27");
					var callbackX = function(data){console.log(data);};
					arcadeAjaxSend(url, data, callbackX);
					ajaxArcadeCallBack("' . $scripturl. '?action=' . $arcadeAction . ';' . $uri . '#gameindex" + gameid);
				}
				else
					return false;
			}
			function ajaxArcadeCallBack(ajax_url)
			{
				setTimeout(function(){window.location.href = ajax_url;}, 1000);
				return false;
			}
			function gameDescriptOnclick(id1)
			{
				if (document.getElementById(id1.id.replace("gameDescript0_", "gameDescript1_")).style.display == "none") {
					document.getElementById(id1.id.replace("gameDescript0_", "gameDescript1_")).style.display = "inline";
					document.getElementById(id1.id.replace("gameDescript0_", "gameDescript2_")).style.display = "none";
				}
				else {
					document.getElementById(id1.id.replace("gameDescript0_", "gameDescript1_")).style.display = "none";
					document.getElementById(id1.id.replace("gameDescript0_", "gameDescript2_")).style.display = "inline"
				}
			}
			if (window.addEventListener)
				window.addEventListener("load", arcadeReportScript, false);
			else if (window.attachEvent)
				window.attachEvent("onload", arcadeReportScript);
			else
				window.onload = arcadeReportScript();
		</script>';
}
?>