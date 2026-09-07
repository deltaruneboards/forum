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
	global $sourcedir, $scripturl, $txt, $boardurl, $context, $settings, $arcadeModSettings, $user_info;

	$uri = '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
	foreach(array('sa', 'sortby', 'dir', 'gametype', 'start', 'category', 'sort') as $setRequest)
		$uri .= isset($_REQUEST[$setRequest]) ? $setRequest . '=' . preg_replace('/[^a-zA-Z0-9\-\_]/', '', (string)$_REQUEST[$setRequest]) . ';' : '';


	echo'
	<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
	<span style="vertical-align: middle;font-size: x-small;padding: 8px;">', ($context['arcade_smf_version'] == 'v2.1' ? '' : $txt['arcade_number_pages'] . '&nbsp;'), $context['page_index'], '&nbsp;&nbsp;<a href="#bot"><b>', $txt['go_down'], '</b></a></span>
	<div style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>
	<div class="cat_bar" id="arctoplist">
		<h3 class="catbg smalltext">
			<span style="display: table;width: 100%;border: 0px;border-collapse: collapse;">
				<span style="display: table-row;">
					<span class="clear" style="display: table-cell;vertical-align: middle;width: 33.3%;">&nbsp;</span>
					<span style="display: table-cell;vertical-align: middle;width: 33.3%;text-align: center;"><a href="', $context['sort_link'], '">', $_SESSION['arcade_gametype_select_title'], '</a></span>
					<span class="icon" style="display: table-cell;float: right;">', $context['sort_arrow'], '</span>
				</span>
			</span>
		</h3>
	</div>
	', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="arcade_up_contain">' :
	'<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<div class="', ($context['arcade_smf_version'] == 'v2.1' ? 'inline' : 'innerframe'), '" translate="no">
			<div class="bordercolor" style="display: table;width: 100%;border: 0px;border-spacing: 1px;border-collapse: separate;">';

	// Is there games?
	if (count($context['arcade']['games']) > 0)
	{
		echo '
				<div style="display: table-row;width: 100%;">
					<div class="catbg3" style="display: table-cell;width: 8%;">&nbsp;</div>
					<div class="catbg3" style="display: table-cell;width: 15%;"><a href="', $context['sort_link'], '">', $txt['arcade_game_name'], '</a></div>
					<div class="catbg3 centertext" style="display: table-cell;width: 34%;">', $txt['arcade_defdescript'], '</div>
					<div class="catbg3" style="display: table-cell;width: 8%;text-align: center;">', $txt['arcade_list_options'], '</div>
					<div class="catbg3" style="display: table-cell;width: 8%;text-align: center;">', $txt['arcade_list_popularity'], '</div>
					<div class="catbg3" style="display: table-cell;width: 8%;text-align: center;white-space: nowrap;">', $txt['arcade_personal_best'],'</div>
					<div class="catbg3" style="display: table-cell;width: 15%;text-align: center;">', $txt['arcade_champion'],'</div>
				</div>';

		echo '
				<div style="position: relative;width: 100%;display: table-row;">
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;padding-bottom: 0.2em;"></div>
				</div>';

		// Loop thought all games in page
		foreach ($context['arcade']['games'] as $key => $game)
		{
			list($viewreport, $adminreport) = array('', '');
			$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && empty($game['allow_download']) ? false : $arcadeModSettings['arcadeEnableDownload'];
			$enableThisGameDownload = empty($game['download']) && !allowedTo('arcade_admin') ? false : $enableGameDownload;
			$romManage = !empty($game['rom_game']) || !empty($game['rom_flag']) ? 'rom' : '';
			$dlAction = !empty($game['rom_flag']) || !empty($game['rom_game']) ? 'retro_arch' : 'arcade';
			if (mb_strpos($game['submit_system'], 'html5') !== false)
			{
				$pop = '<a title="' . $txt['arcade_popplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']+30) . ',' . ($game['height']) . ', 0, false)">' . $txt['pdl_popplay'] . '</a>';
				$fullPop = '<a title="' . $txt['arcade_fullplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']+30) . ',' . ($game['height']) . ', 0, true)">' . $txt['arcadeFullPopup'] . '</a>';
			}
			else
			{
				$pop = '<a title="' . $txt['arcade_popplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']) . ',' . ($game['height']) . ', 0, false)">' . $txt['pdl_popplay'] . '</a>';
				$fullPop = '<a title="' . $txt['arcade_fullplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $game['url']['popup'] . '\',' . ($game['width']) . ',' . ($game['height']) . ', 0, true)">' . $txt['arcadeFullPopup'] . '</a>';
			}

			if  (!empty($arcadeModSettings['arcadeEnableReport']) && allowedTo('arcade_report') && empty($game['report_id']))
				$viewreport = '<a onclick="gameReporting(\'' . $game['id'] . '\', \'' . (strlen($game['name']) < 71 ? $game['name'] : substr($game['name'], 0, 67) . '...') . '\')" id="report_game' . $game['id'] . '" href="javascript:void(0);">' . $txt['pdl_report'] . '</a>';

			if (allowedTo('arcade_admin') && !empty($game['report_id']))
				$adminreport = '<a href="' . $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $game['id'] . '">' . $txt['show_pdl_report'] . '</a>';

			$descriptLength = !empty($arcadeModSettings['arcadeDescriptLength']) ? $arcadeModSettings['arcadeDescriptLength'] : 2000;

			// Print out game information
			echo '
				<div id="gameindex' . $game['id'] . '" style="display: table-row;border-bottom: groove 1px;">
					<div class="windowbg2 centertext" style="display: table-cell;width: 8%;vertical-align: middle;padding-right: 1rem;">', $game['thumbnail'] != '' ? '
						<a href="' . $game['url']['play'] . '">
							<img id="arcadeThumb_' . $game['id'] . '" style="filter:contrast(125%);image-rendering: high-quality;width: ' . $context['arcadeThumbWidth'] . 'px;height: ' . $context['arcadeThumbHeight'] . 'px;max-width: ' . $context['arcadeThumbWidth'] . 'px;max-height: ' . $context['arcadeThumbHeight'] . 'px;vertical-align: middle;' . (!empty($arcadeModSettings['arcadeIconBorderRadius2']) ? 'border-radius: ' . $arcadeModSettings['arcadeIconBorderRadius2'] . '%;' : '') . '" src="' . $game['thumbnail'] . '" alt="'.$game['name'].'" title="'.$txt['arcade_play'].' '.$game['name'].'"/>
						</a>' : '', '
					</div>
					<div class="windowbg" style="display: table-cell;position: relative;padding-left: 5px;padding-top: 2px;width: 15%;">
						<div class="floatleft">
							<div style="border: 1px dotted;border-radius: 5px;padding-bottom: 1px;text-overflow: ellipsis;"><a style="padding: 1px;" href="', $game['url']['play'], '">', $game['name'], '</a></div>
							' . (empty($_SESSION['arcade_isMobilePlay']) ? '<div class="smalltext">
								' . $pop . ' &#8212; ' . $fullPop . '
							</div>' : '') . '
							' . (!empty($viewreport) ? '<div class="smalltext">' . $viewreport . '</div>' : '') . '
							' . (!empty($adminreport) ? '<div class="smalltext">' . $adminreport . '</div>' : '') . '
						</div>
						<div style="font-size: 8pt;display: inline;font-style: oblique 10deg;">';
			if (!empty($arcadeModSettings['arcadeDisplayType']) && !empty($txt['arcade_gamesavetype'][$game['submit_system']]) && empty($rom))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;">'. $txt['arcade_typeset']. '&#058;&nbsp;' .$txt['arcade_gamesavetype'][$game['submit_system']].'</div>';
			if (!empty($arcadeModSettings['arcadeRomToggle']) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]) && empty($rom))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;">'. $txt['arcade_romtypeset']. '&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '</div>';
			elseif (!empty($rom) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;">'. $txt['arcade_romtypeset']. '&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '</div>';
							
			echo '
						</div>
					</div>
					<div class="windowbg" style="display: table-cell;padding-left: 2px;width: 34%;clear: both;">
						', !empty($game['description']) ? '<div class="smalltext" style="word-wrap:break-word;"><span class="game_description_length" id="gameDescript0_' . $game['id'] . '" onclick="gameDescriptOnclick(this)"><span style="display: inline;" id="gameDescript1_' . $game['id'] . '">' . (strlen($game['description']) > $descriptLength ? substr($game['description'], 0, $descriptLength-2) . '...' : $game['description']) . '</span><span style="display: none;" id="gameDescript2_' . $game['id'] . '">' . $game['description'] . '</span></span></div>' : '<div><span style="display: none;">&nbsp;</span></div>', '
						<div style="inline-flex;vertical-align: bottom;text-align: right;position: relative;padding-top: 0.5em;">';

			if ($game['rating2'] > 0)
			{
				echo
					str_repeat('<img style="vertical-align: bottom;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star.gif" alt="*" />' , $game['rating2']),
					str_repeat('<img style="vertical-align: bottom;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5 - $game['rating2']);
			}

			else
			{
				echo
					str_repeat('<img style="vertical-align: bottom;" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5);
			}


			echo '
						</div>
					</div>
					<div class="windowbg" style="display: table-cell;position: relative;width: 8%;padding-top: 7px;">';

			if ($game['highscore_support'])
				echo '
						<div title="' . $txt['arcade_dviewscore'] . '" class="smalltext" style="text-align: right;clear: both;position: relative;padding: 2px 4px 2px 0px;">
							<a href="' . $game['url']['highscore'] . '">
								<img style="width: 32px;height: 13px;" alt="' . $txt['arcade_dviewscore'] . '" src="' . $settings['default_images_url'] . '/arc_icons/medals.png" />
							</a>
						</div>';

			if (!empty($game['topic_id']) && !empty($arcadeModSettings['arcadeEnablePosting']))
				echo '
						<div class="smalltext" style="clear: both;position: relative;padding: 2px 4px 2px 0px;"><a href="', $scripturl, '?topic=', $game['id_topic'], '">', $txt['arcade_topic_talk'],'</a></div>';

			echo '
						<div style="clear: both;float: right; text-align: right;position: relative;padding: 2px 4px 2px 0px;" class="smalltext">';

			// Category
			if ($game['category']['name'] && !empty($arcadeModSettings['arcade_showListCat']))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;"><a href="', $game['category']['link'], '">', (!empty($game['category']['icon']) ? '<img class="icon" src="' . $settings['default_images_url'] . '/arc_icons/' . $game['category']['icon'] . '" alt="' . $game['category']['name'] . '" title="' . $game['category']['name'] . '" style="vertical-align: middle;width: 16px;height: 16px;" />' : $game['category']['name']), '</a></div>';

			if (allowedTo('arcade_admin'))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;"><a href="', $game['url']['edit'], '"><img style="border: 0px;" src="' . $settings['default_images_url'] . '/arc_icons/modify.png" alt="' . $txt['arcade_edit'] . '" title="' . $txt['pdl_edit'] . '&nbsp;' . $game['name'] . '"/></a></div>';

			// Download link
			if (!empty($enableThisGameDownload))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;"><a data-gametype="' . (!empty($game['submit_system']) ? $game['submit_system'] : 'none') . '" data-internalname="' . $game['internal_name'] . '" href="', $game['url']['download'], '"><img class="icon" style="vertical-align: middle;width: 16px;height: 16px;" src="' . $settings['default_images_url'] . '/arc_icons/download_icon.png" alt="' . $txt['pdl_button1'] . '" title="' . $txt['pdl_button1'] . '&nbsp;' . $game['name'] . '"/></a></div>';

			// Favorite link (if can favorite)
			if (allowedTo('arcade_submit'))
				echo '
							<div style="clear: both;position: relative;padding: 2px 4px 2px 0px;">
								<a href="', $game['url']['favorite'], '" onclick="arcade_favorite(', $game['id'] , '); return false;">
									', !$game['is_favorite'] ?
									'<img style="vertical-align: middle;" id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star4.gif" alt="' . $txt['arcade_add_favorites'] . '" title="' . $txt['arcade_add_favorites'] . '"/>' :
									'<img style="vertical-align: middle;" id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star3.gif" alt="' . $txt['arcade_remove_favorite'] .'" title="' . $txt['arcade_remove_favorite'] . '" />', '
								</a>
							</div>';
			echo '
						</div>
					</div>
					<div class="windowbg2" style="display: table-cell;width: 8%; text-align: center;">', $game['plays'], '</div>';

			// Show personal best and champion only if game supports highscores
			if (!empty($game['is_champion']) && $game['highscore_support'])
			{
				echo '
					<div class="windowbg2" style="width: 8%; text-align: center;display: table-cell;">';

				if (!empty($game['personal_best']) && $user_info['id'] == $game['champion']['member_id'])
					echo'
						<img class="icon" style="border: 0px;vertical-align: middle;padding-right: 0.11em;" src="' . $settings['default_images_url'] . '/arc_icons/cup_g.gif" alt="cup_g" title="' . $txt['arcade_you_are_first'] . '&nbsp;' . $game['name'] . '"/>';
				elseif ($game['personal_best'] > 0 && $user_info['id'] == $game['second_place']['member_id'])
					echo'
						<img class="icon" style="border: 0px;vertical-align: middle;padding-right: 0.11em;" src="' . $settings['default_images_url'] . '/arc_icons/cup_s.gif" alt="cup_s" title="' . $txt['arcade_you_are_second'].'&nbsp;' . $game['name'] . '" />';
				elseif ($game['personal_best'] > 0 && $user_info['id'] == $game['third_place']['member_id'])
					echo'
						<img class="icon" style="border: 0px;vertical-align: middle;padding-right: 0.11em;" src="' . $settings['default_images_url'] . '/arc_icons/cup_b.gif" alt="cup_b" title="' . $txt['arcade_you_are_third'] . '&nbsp;' . $game['name'] . '"/>';

				echo ($game['is_personal_best'] ? $game['personal_best'] : (!$user_info['is_guest'] ? $txt['arcade_no_scores'] : '<img alt="' . $txt['arcade_guest_na'] . '" title ="' . $txt['arcade_guest_na'] . '" src="' . $settings['default_images_url'] . '/arc_icons/guest_na.gif" style="vertical-align: middle;width: 16px;height: 16px;" class="icon" />')), '
					</div>
					<div class="windowbg2" style="display: table-cell;width: 15%; text-align: center;">
						<div style="display: table;width: auto;">
							<div style="display: table-row;">
								<div style="display: table-cell;width: 10%;padding-left: 3%;"><img style="border: 0px;vertical-align: middle;" class="icon" src="' . $settings['default_images_url'] . '/arc_icons/cup_g.gif" alt="gold" title="' . $txt['arcade_first'] . '"/></div>
								<div style="display: table-cell;text-align: center;width: 87%;">', $game['champion']['member_link'], '
									<div class="windowbg2" style="display: block;width: 0px;"><span style="display: none;">&nbsp;</span></div>
										', $game['champion']['score'], '
								</div>
							</div>';
				if ($game['second_place']['score'] > 0)
					echo'
							<div style="display: table-row;">
								<div style="display: table-cell;width: 10%;padding-left: 3%;"><img style="border: 0px;vertical-align: middle;" class="icon" src="'. $settings['default_images_url']. '/arc_icons/cup_s.gif" alt="silver" title="' . $txt['arcade_second'].'"/></div>
								<div style="display: table-cell;text-align: center;width: 87%;">', $game['second_place']['member_link'], '
									<div class="windowbg2" style="display: block;width: 0px;"><span style="display: none;">&nbsp;</span></div>
									', $game['second_place']['score'], '
								</div>
							</div>';

				if ($game['third_place']['score'] > 0)
					echo'
							<div style="display: table-row;">
								<div style="display: table-cell;width: 10%;padding-left: 3%;"><img style="border: 0px;vertical-align: middle;" class="icon" src="'. $settings['default_images_url']. '/arc_icons/cup_b.gif" alt="bronze" title="'.$txt['arcade_third'].'"/></div>
								<div style="display: table-cell;text-align: center;width: 87%;">', $game['third_place']['member_link'], '
									<div class="windowbg2" style="display: block;width: 0px;"><span style="display: none;">&nbsp;</span></div>
									', $game['third_place']['score'], '
								</div>
							</div>';

				echo'
						</div>
					</div>';
			}
			elseif (!$game['highscore_support'])
				echo '
					<div class="windowbg2" style="display: table-cell;text-align: center; width: 15%;">', $txt['arcade_no_highscore'], '</div>
					<div class="windowbg2" style="display: table-cell;width: 0px;"><span style="display: none;">&nbsp;</span></div>';
			else
				echo '
					<div class="windowbg2" style="display: table-cell;text-align: center; width: 15%;">', $txt['arcade_no_scores'], '</div>
					<div class="windowbg2" style="display: table-cell;width: 0px;"><span style="display: none;">&nbsp;</span></div>';

			echo '
				</div>';

			if ($key < count($context['arcade']['games']) -1 && !empty($arcadeModSettings['arcadeListHorizontalDivision']))
				echo '
				<div style="position: relative;width: 100%;display: table-row;margin-bottom: 0.5em;">
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
					<div style="display: table-cell;border-top: groove 1px;border-left: 0px;border-right: 0px;border-bottom: 0px;padding: 0px;line-height: 5px;"></div>
				</div>';
		}
	}
	else
		echo '
				<div style="display: table-row;">
					<div style="display: table-cell;" class="catbg3"><strong>', $txt['arcade_no_games'], '</strong></div>
				</div>';

	echo '
			</div>
		</div>
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div style="vertical-align: middle;padding:8px;font-size: x-small;">', ($context['arcade_smf_version'] == 'v2.1' ? '' : $txt['arcade_number_pages'] . '&nbsp;'), $context['page_index'], '&nbsp;&nbsp;<a href="#top"><strong>', $txt['arcade_dgo_up'], '</strong></a></div>';

	if (!empty($arcadeModSettings['arcadeShowIC']))
	{
		echo '
	<div style="padding-top: 20px;"><span style="display: none;">&nbsp;</span></div>
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
				<div class="smalltext" style="padding-left: 15px;word-wrap: break-word;word-break: keep-all;overflow: hidden;">';

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
					<span style="padding-left: 5px;">', $txt['arcade_game_highlights'], '</span>
				</h4>
				<div class="smalltext" style="padding-left:10px;word-wrap: break-word;word-break: keep-all;overflow: auto;">';

		if (!empty($context['arcade']['stats']['longest_champion']) && !empty($context['arcade']['stats']['longest_champion']['member_link']) && !empty($context['arcade']['stats']['longest_champion']['game_link']))
			echo '
					<div>', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</div>';

		if (!empty($context['arcade']['stats']['most_played']) && !empty($context['arcade']['stats']['most_played']['link']))
			echo '
					<div style="padding-top: 2px;">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</div>';

		if (!empty($context['arcade']['stats']['best_player']) && !empty($context['arcade']['stats']['best_player']['link']))
			echo '
					<div style="padding-top: 2px;">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</div>';

		if (!empty($context['arcade']['stats']['games']))
			echo '
					<div style="padding-top: 2px;">', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</div>';

		echo '
				</div>';

		if (!empty($arcadeModSettings['arcadeShowOnline']))
			echo '
					<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
					<div class="smalltext">
						<h4 class="left">
							<span style="vertical-align: middle;"><img class="icon" src="', $settings['default_images_url'], '/arc_icons/online.gif" alt="" /></span>
							<span>' . $txt['arcade_users'] . '</span>
						</h4>
					</div>
					<div>
						<div class="smalltext" style="padding-bottom: 3px;padding-left: 5px;">' . $context['arcade_online_link'] . '</div>
						<div class="smalltext" style="padding-left:15px;word-wrap: break-word;keep-all: keep-all;overflow: auto;">' . implode(', ', $context['arcade_viewing']) . '</div>
					</div>';

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
			<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['default_images_url'], '/arc_icons/online.gif" alt="" />
			<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_users'], '</span>
		</h3>
	</div>
	', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="arcade_up_contain windowbg">' :
	'<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<div class="innerframe" style="border-radius: 5px;">
			<div class="smalltext" style="padding-left: 5px;padding-bottom: 3px;border: 0px;">' . $context['arcade_online_link'] . '</div>
			<div class="smalltext" style="padding-left:20px;word-wrap: break-word;word-break: keep-all;overflow: auto;border: 0px;">' . implode(', ', $context['arcade_viewing']) . '</div>
		</div>
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
	else
		echo '
	<div style="padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';

	echo '
	<script type="text/javascript">
		function gameReporting(gameid, gamename)
		{
			document.getElementById("report_game" + gameid).removeAttribute("href");
			var reason = prompt(\'' . $txt['pdl_report_reason_name'] . ' \' + gamename + \'' . '\r\n\r\n' . $txt['pdl_report_reason_input'] . '\');
			if (reason)
			{
				var url = "'. $scripturl . '?action=' . $arcadeAction . ';sa=report;game=" + gameid + ";sesc=' . $context['session_id'] . '";
				var data = "reason=" + encodeURIComponent(reason).replace(/\'/g, "%27");
				var callback = function(data){console.log(data);};
				arcadeAjaxSend(url, data, callback);
				setTimeout(function(){window.location.href = "' . $scripturl . '?action=' . $arcadeAction . ';' . $uri. '#gameindex" + gameid;}, 1000);
			}
			else
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
	</script>';
}

?>