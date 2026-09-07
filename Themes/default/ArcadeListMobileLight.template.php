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
	global $scripturl, $txt, $context, $settings, $user_info, $modSettings, $arcadeModSettings;
	list($row_tally, $tally, $code, $uri) = array(2, 0, '', '');
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	foreach(array('sa', 'sortby', 'dir', 'gametype', 'start', 'category', 'sort') as $setRequest)
		$uri .= isset($_REQUEST[$setRequest]) ? $setRequest . '=' . preg_replace('/[^a-zA-Z0-9\-\_]/', '', (string)$_REQUEST[$setRequest]) . ';' : '';
	$count = count($context['arcade']['games']);
	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
	$hookCheck = !empty($modSettings['integrate_pre_log_stats']) ? explode(',', $modSettings['integrate_pre_log_stats']) : array();

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
		<div><span style="display: none;">&nbsp;</span></div>
		<div class="mobile_title arcade_light_list_mobile_title arcade_light_list_header" id="arctoplist">
			<h4 class="centertext arcade_light_list_mobile_title arcade_light_list_h4">
				<span class="centertext arcade_light_list_title"><a href="', $context['sort_link'], '">', $_SESSION['arcade_gametype_select_title'], '</a></span>
			</h4>
		</div>
		<div><span style="display: none;">&nbsp;</span></div>
		<div class="game_table arcade_up_contain arcade_light_list_upcontain">
			<div translate="no" class="arcade_light_games_listbox">';

	/*  loop through games for the list  */
	foreach ($context['arcade']['games'] as $game)
	{
		$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && empty($game['allow_download']) ? false : $arcadeModSettings['arcadeEnableDownload'];
		$enableThisGameDownload = empty($game['download']) && !allowedTo('arcade_admin') ? false : $enableGameDownload;
		if(empty($game['report_id']))
			$game['report_id'] = 0;

		strlen($game['name']) >= 23 ? $game['name'] = substr($game['name'], 0, 22) . '...' : '';
		$romManage = !empty($game['rom_game']) || !empty($game['rom_flag']) ? 'rom' : '';
		$dlAction = !empty($game['rom_flag']) || !empty($game['rom_game']) ? 'retro_arch' : 'arcade';
		//$arcadeAction = !empty($game['rom_game']) || !empty($game['rom_flag']) ? 'retro_arch' : 'arcade';
		// Show personal best and champion
		$game['personal_best'] ? $your_best = $txt['your_score'] . $game['personal_best'] : $your_best=$txt['your_score'] . $txt['not_applicable'];
		$game['champion']['member_link'] == $txt['arcade_guest'] && empty($game['champion']['score']) ? $game['champion']['member_link'] = '' : '';
		$game['champion']['member_link'] ? $champ = sprintf($txt['champ'], $game['champion']['member_link']) : $champ = sprintf($txt['champ'], $txt['not_applicable']);
		$game['champion']['score'] ? $champ_score = $txt['champ_scoring'] . $game['champion']['score'] : $champ_score = $txt['champ_scoring'] . $txt['not_applicable'];
		$game['description'] = !empty($game['description']) ? stripslashes($game['description']) : $txt['no_description'];
		//$subSystem = !empty($game['rom_game']) && !empty($game['rom_system']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]) ? $txt['arcade_select_gametype_rom'][$game['rom_system']] : (!empty($game['submit_system']) && !empty($txt['arcade_gamesavetype'][$game['submit_system']]) ? $txt['arcade_gamesavetype'][$game['submit_system']] : '');
		list($fav, $game_type) = array('', '');
		$catLink = '<a href="' . $game['category']['link'] . '">' . $game['category']['name'] . '</a>';
		$cat = !empty($game['category']['name']) ? '<div><b>&bull;</b>  ' . sprintf($txt['pdl_cat_list'], $catLink) . '</div>' : '<div><b>&bull;</b>  ' . sprintf($txt['pdl_cat_list'], $txt['arcade_list_none']) . '</div>';
		if (!empty($arcadeModSettings['arcadeDisplayType']) && !empty($txt['arcade_gamesavetype'][$game['submit_system']]) && empty($rom))
			$game_type .= '
							<div><b>&bull;</b>&nbsp;<span class="arcade_light_list_typeset">'. $txt['arcade_typeset']. '&nbsp;&#058;&nbsp;' .$txt['arcade_gamesavetype'][$game['submit_system']].'</span></div>';
		if (!empty($arcadeModSettings['arcadeRomToggle']) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]) && empty($rom))
			$game_type .= '
							<div><b>&bull;</b>&nbsp;<span class="arcade_light_list_typeset">'. $txt['arcade_romtypeset']. '&nbsp;&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '</span></div>';
		elseif (!empty($rom) && !empty($arcadeModSettings['arcadeDisplayRomType']) && !empty($txt['arcade_select_gametype_rom'][$game['rom_system']]))
			$game_type .= '
							<div><b>&bull;</b>&nbsp;<span class="arcade_light_list_typeset">'. $txt['arcade_romtypeset']. '&nbsp;&#058;&nbsp;' . $txt['arcade_select_gametype_rom'][$game['rom_system']] . '</span></div>';

		if ($context['arcade']['can_favorite'])
		{
			if (!$game['is_favorite'])
				$fav .= '
						<span>
							<a id="favgametext' . $game['id'] . '" href="'. $game['url']['favorite']. '" onclick="arcade_favorite('. $game['id'] . ');arcade_favorite_text('. $game['id'] . '); return false;">' . $txt['arcade_mobile_add_fav'] . '</a>
						</span>
						<span style="padding-left: 0.5em;">
							<a href="'. $game['url']['favorite']. '" onclick="arcade_favorite('. $game['id'] . ');arcade_favorite_text('. $game['id'] . '); return false;">
								<img id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star4.gif" class="arcade_light_list_listicon" alt="' . $txt['arcade_add_favorites'] . '" />' . '
							</a>
						</span>';
			else
				$fav .= '
						<span>
							<a id="favgametext' . $game['id'] . '" href="'. $game['url']['favorite']. '" onclick="arcade_favorite('. $game['id'] . ');arcade_favorite_text('. $game['id'] . '); return false;">' . $txt['arcade_mobile_del_fav'] . '</a>
						</span>
						<span style="padding-left: 0.5em;">
							<a href="'. $game['url']['favorite']. '" onclick="arcade_favorite('. $game['id'] . ');arcade_favorite_text('. $game['id'] . '); return false;">
								<img id="favgame' . $game['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star3.gif" class="arcade_light_list_listicon" alt="' . $txt['arcade_remove_favorite'] . '" />
							</a>
						</span>';
		}

		$rate = '';
		if ($game['rating2'] > 0)
			$rate = str_repeat('<img class="arcade_light_list_listicon" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star.gif" alt="*" />' , $game['rating2']) . str_repeat('<img class="arcade_light_list_listicon" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5 - $game['rating2']);
		else
			$rate = str_repeat('<img class="arcade_light_list_listicon" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="-" />' , 5);

		if (empty($game['pdl_count']))
			$game['pdl_count'] = 0;

		$game['height'] = $game['height'] + 20;
		$hiscr = '
							<a href="' . $game['url']['highscore'] . ';">' . $txt['arcade_viewscore'] . '</a>';
		$viewdl = '
							<div><b>&bull;</b>&nbsp;'.$your_best.'</div>
							<div><b>&bull;</b>&nbsp;'. $txt['num_plays']. '&#058;&nbsp;' . $game['plays'] . '</div>';
		$viewreport = '';
		// id="gameindex' . $game['id'] . '"
		if  (($arcadeModSettings['arcadeEnableReport'] == true) && (allowedTo('arcade_report') == true) && empty($game['report_id']))
			$viewreport .= '<div id="gameindex' . $game['id'] . '"><b>&bull;</b>&nbsp;<a onclick="gameReporting(\'' . $game['id'] . '\', \'' . (strlen($game['name']) < 71 ? $game['name'] : substr($game['name'], 0, 67) . '...') . '\')" id="report_game' . $game['id'] . '" href="javascript:void(0);">' . $txt['pdl_report'] . '</a></div>';
		elseif (($arcadeModSettings['arcadeEnableReport'] == true) && (allowedTo('arcade_report') == true) && !empty($game['report_id']) && !allowedTo('arcade_admin'))
			$viewreport .= '<div id="gameindex' . $game['id'] . '"><b>&bull;</b>&nbsp;' . $txt['show_pdl_report'] . '</div>';

		if ((allowedTo('arcade_admin') == true) && ((int)$game['report_id'] > 0))
		{
			$viewreport .= '<div id="gameindex' . $game['id'] . '"><b>&bull;</b>&nbsp;<a href="' . $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $game['id'] . '">' . $txt['show_pdl_report'] . '</a></div>';
			$gamename = '<span class="arcade_light_list_playlink"><a class="arcadeLinkStyle" href="' . $game['url']['play'] . '" title="' . $txt['alt_play'] . '">' . $game['name'] . ' </a></span>';
		}
		else
			$gamename = '<a class="arcadeLinkStyle" href="' . $game['url']['play'] . '" title="' . $txt['alt_play'] . '">' . $game['name'] . ' </a>';

		if (!empty($enableThisGameDownload))
			$viewdl .= '
							<div><b>&bull;</b>&nbsp;'. $txt['pdl_counter']. '&nbsp;' .$game['pdl_count'].'</div>
							<div><b>&bull;</b>&nbsp;<a data-gametype="' . (!empty($game['submit_system']) ? $game['submit_system'] : 'none') . '" data-internalname="' . $game['internal_name'] . '" href="' . $game['url']['download'] . '">' . $txt['arcade_download_game'] . '</a></div>';

		$viewdl .= $game_type;

		if ($context['arcade']['can_admin_arcade'])
			$viewdl .= '<div><b>&bull;</b>&nbsp;<a href="' . $scripturl . '?action=admin;area=manage' . $romManage . 'games;sa=edit' . $romManage . ';game=' . $game['id'] . '">' . $txt['pdl_edit'] . '</a></div>';

		// default is two cells wide
		$tally++;
		$remainder = intval($tally % $row_tally);

		$code .=  '
						<div class="smalltext arcadeListCell arcade_light_list_cell">
							<div style="display: flex;justify-content: center;flex-direction: row;padding: 0rem !important;" class="cat_bar mobile_title arcadeListTitle arcade_light_list_mobile_title arcade_light_list_mobile_box" id="arctoplist' . $game['id'] . '">
								<h3 style="padding-left: 0rem !important;" class="catbg centertext mobileGameLinkH3 arcade_light_list_mobile_title arcade_light_list_mobile_boxh3">
									<span class="arcade_light_list_mobile_titlename">' . $gamename . '</span>
								</h3>
							</div>
							<div class="arcadeGameDescriptAll"><div class="arcadeGameDescript arcade_light_list_descript">' . $game['description'] . '</div></div>
							<div style="height: 1px"></div>
							<div class="smalltext arcadeGameData arcade_light_list_cat_title">' . (!empty($arcadeModSettings['arcade_showListCat']) ? $cat : '') . '
								<div><b>&bull;</b>  ' . $champ . '</div>
								<div><b>&bull;</b> ' . $champ_score . '</div>
								' . $viewdl . '
								<div><b>&bull;</b> ' . $hiscr . '</div>
								' . $viewreport . '
								' . (!empty($fav) ? '<div><b>&bull;</b>  ' . $fav . '</div>' : '') . '
								<div class="arcade_light_list_rate"><b>&bull;</b> ' . $rate . '</div>
							</div>
						</div>';
	}


	if (!empty($remainder))
		$code .= str_repeat('
						<div class="smalltext arcadeListCell arcade_light_list_cell"></div>', $row_tally-$remainder);

	echo '
					<div style="display: flex;width: 100%;" id="arcadeListTable">
						' . $code . '
					</div>
					<div style="width: 100%;display: flex;">
						<div class="arcade_light_list_emptycell" style="display: table-cell;"><span style="display: none;">&nbsp;</span></div>
					</div>
				</div>
			</div>
			<span><span>&nbsp;</span></span>
			<div style="width: 100%;position: relative;clear: left;padding-left: 0.3em;">
				<div class="pagesection" style="display: inline;">
					<div class="arcade_light_list_pages">', $context['page_index'], (!empty($arcadeModSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : ''), '</div>
				</div>
			</div>
			<div class="arcade_light_list_bottom"><span style="display: none;">&nbsp;</span></div>';

	if (!empty($arcadeModSettings['arcadeShowIC']))
	{
		echo '
			<div class="arcade_light_list_ic_body">
				<div class="arcadeListRows">
					<div class="arcadeListIC">
						<div id="arctoplistIC" style="display: flex;justify-content: center;flex-direction: row;left: 0.1rem !important;right: 0.1rem !important;" class="cat_bar mobile_title arcade_light_list_mobile_title arcade_light_list_mobile_box">
							<h3 style="width: 100%;" class="catbg centertext mobileGameLinkH3 arcade_light_list_mobile_title arcade_light_list_mobile_boxh3" id="arcade_ic_title">
								<span class="arcade_light_list_mobile_icname">' . $txt['arcade_info_center'] . '</span>
							</h3>
						</div>
						<div class="arcade_up_contain arcadeGameDescript arcade_light_list_mobile_ic_start">
							<div class="inline">
								<div id="upshrinkHeaderArcadeIC">';

		if (!empty($context['arcade']['latest_scores']))
		{
			echo '
									<h4 class="mobileGameLinkH4" style="padding-top: 0.1rem !important;">
										<span class="arcade_light_list_mobile_ic_title">', $txt['arcade_latest_scores'], '</span>
									</h4>
									<div class="mediumtext arcadeGameData arcade_light_list_mobile_ic_ex_box">';

			foreach ($context['arcade']['latest_scores'] as $score)
				echo '
										<div>', sprintf($txt['arcade_latest_score_item'], $scripturl . '?action=' . $arcadeAction . ';sa=play;game=' . $score['game_id'], $score['name'], $score['score'], $score['memberLink']), '</div>
										<div class="arcade_light_list_mobile_ic_lscores_time">',  $score['time'], '</div>';

			echo '
									</div>';
		}
		elseif (empty($rom))
			echo '
									<h4 class="left  mobileGameLinkH4" style="padding-top: 0.1rem !important;">
										<span class="arcade_light_list_mobile_ic_title">', $txt['arcade_latest_scores'], '</span>
									</h4>
									<div class="mediumtext arcade_light_list_mobile_ic_lscores_none arcade_list_noscore">', $txt['arcade_no_scores'], '</div>';

		echo '
									<h4 class="left clear  mobileGameLinkH4 arcade_light_list_mobile_ic_h4">
										<span class="arcade_light_list_mobile_ic_title">', $txt['arcade_game_highlights'], '</span>
									</h4>
									<div class="mediumtext arcadeGameData arcade_light_list_mobile_ic_box">';

		if ($context['arcade']['stats']['longest_champion'] !== false && !empty($context['arcade']['stats']['longest_champion']['member_link']) && !empty($context['arcade']['stats']['longest_champion']['game_link']))
			echo '
										<div class="arcade_light_list_mobile_ic_padbot">', sprintf($txt['arcade_game_with_longest_champion'], $context['arcade']['stats']['longest_champion']['member_link'], $context['arcade']['stats']['longest_champion']['game_link']), '</div>';

		if ($context['arcade']['stats']['most_played'] !== false && !empty($context['arcade']['stats']['most_played']['link']))
			echo '
										<div class="arcade_light_list_mobile_ic_padbot">', sprintf($txt['arcade_game_most_played'], $context['arcade']['stats']['most_played']['link']), '</div>';

		if ($context['arcade']['stats']['best_player'] !== false && !empty($context['arcade']['stats']['best_player']['link']))
			echo '
										<div class="arcade_light_list_mobile_ic_padbot">', sprintf($txt['arcade_game_best_player'], $context['arcade']['stats']['best_player']['link']), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
										<div>', sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']), '</div>';

		echo '
									</div>
								</div>';

		if (!empty($arcadeModSettings['arcadeShowOnline']))
			echo '
								<h4 class="left clear  mobileGameLinkH4 arcade_light_list_mobile_ic_olh4">
									<span class="arcade_light_list_mobile_ic_title">', $txt['arcade_users'], '</span>
								</h4>
								<div class="mediumtext arcadeGameData arcade_light_list_mobile_ic_ex_box">
									<div class="arcade_light_list_mobile_ic_padbot">' . $context['arcade_online_link'] . '</div>
									<div class="arcade_light_list_mobile_ic_viewing">' . implode(', ', $context['arcade_viewing']) . '</div>
								</div>';

		echo '
							</div>
						</div>
					</div>
				</div>
			</div>
			<span><span>&nbsp;</span></span>
			<div class="arcade_light_list_mobile_ic_padbotend"><span style="display: none;">&nbsp;</span></div>';
	}
	elseif (!empty($arcadeModSettings['arcadeShowOnline']))
		echo '
			<div class="arcade_light_list_mobile_ic_olbox">
				<div class="arcadeListRows">
					<div class="arcadeListIC">
						<div style="display: flex;justify-content: center;flex-direction: row;left: 0.1rem !important;right: 0.1rem !important;" class="cat_bar mobile_title arcade_light_list_mobile_title arcade_light_list_mobile_box" id="arctoplistIC">
							<h3 style="width: 100%;" class="catbg centertext mobileGameLinkH3 arcade_light_list_mobile_title arcade_light_list_mobile_boxh3" id="arcade_ic_title">
								<span class="arcade_light_list_mobile_icname">' . $txt['arcade_users'] . '</span>
							</h3>
						</div>
						<div class="arcade_up_contain">
							<div class="arcade_light_list_mobile_viewbox">
								<div class="mediumtext arcadeGameData arcade_light_list_mobile_view_linkbox">
									<div class="arcade_light_list_mobile_view_linktitle">' . $context['arcade_online_link'] . '</div>
									<div class="arcade_light_list_mobile_view_link">' . implode(', ', $context['arcade_viewing']) . '</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<span><span>&nbsp;</span></span>
			<div class="arcade_light_list_mobile_ic_padbotend"><span style="display: none;">&nbsp;</span></div>';
	else
		echo '
			<div class="arcade_light_list_mobile_ic_padbotend"><span style="display: none;">&nbsp;</span></div>';

	echo '
		<script>
			$(window).on("orientationchange", function(event) {
				if (this.orientation == 0 || this.orientation == 180) {
					arcadeListDisplayOne();
				}
				else {
					setTimeout(function(){arcadeListDisplayTwo();}, 500);
				}
			});
			function gameReporting(gameid, gamename)
			{
				$("#report_game" + gameid).removeAttr("href");
				var reason = prompt(\'' . $txt['pdl_report_reason_name'] . ' \' + gamename + \'' . '\r\n\r\n' . $txt['pdl_report_reason_input'] . '\');
				if (reason) {
					var url = "'. $scripturl . '?action=arcade;sa=report;game=" + gameid + ";sesc=' . $context['session_id'] . '";
					var data = "reason=" + encodeURIComponent(reason).replace(/\'/g, "%27");
					var callback = function(data){console.log(data);};
					arcadeAjaxSend(url, data, callback);
					setTimeout(function(){window.location.href = "' . $scripturl . '?action=' . $arcadeAction . ';' . $uri . '#gameindex" + gameid;}, 1000);
				}
				else
					return false;
			}
			function arcade_favorite_text(gameid)
			{
				if ($("#favgametext"+gameid).html() == "' . $txt['arcade_mobile_add_fav'] . '") {
					$("#favgametext"+gameid).html("' . $txt['arcade_mobile_del_fav'] . '");
				}
				else {
					$("#favgametext"+gameid).html("' . $txt['arcade_mobile_add_fav'] . '");
				}
			}
			function arcadeListTableRow()
			{
				$("body,html").css("font-size","1.1em");
				$(".arcadeLinkStyle").css("color", "inherit");
				$(".arcadeLinkStyle").on("mouseout", function() {
					$(this).css("color","inherit");
				}).on("mouseover", function() {
					$(this).css("color","initial");
				});
				if(window.orientation == 0 || window.orientation == 180){
					arcadeListDisplayOne();
				}
				else{
					arcadeListDisplayTwo();
				}
				$(".arcadeGameDescript").css("padding-left","0.2rem");
				$(".arcadeGameDescript").css("text-indent","0rem");
			}
			function arcadeListDisplayOne()
			{
				arcadeDescriptInit();
				$(".arcade_light_list_ic_body").css("margin-left", "-0.25rem");
				$(".arcade_light_list_cell").css("padding-right", "0.75rem");
				$(".arcade_light_list_cell").css("max-width", "100%");
				$(".arcade_light_list_cell").css("min-width", "100%");
				$(".arcade_light_list_cell").css("flex", "100%");
				$(".arcade_light_list_cell").css("justify-content", "space-around");
				$(".arcadeListIC").css({"display":"block","margin":"0rem","padding":"0px","width":"100%","max-width":"100%","min-width":"100%","font-size":"medium","font-weight":"900","left":"0rem","padding-left":"1.15rem"});
				$(".arcadeGameData").css("font-size", "' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? 'x-large' : 'medium') . '");
				$(".arcadeGameData").css("line-height", "' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '125' : '100') . '%");
				$(".arcadeGameData").css("font-weight", "900");
				$(".arcade_light_list_listicon").css("width", "' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '1' : '0.75') . 'rem");
				$(".arcade_light_list_listicon").css("height", "' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '1' : '0.75') . 'rem");
				$(".arcade_light_list_listicon").css("padding-top", "' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '0.05' : '0.10') . 'rem");
				$(".arcadeGameDescript").css("font-size","' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? 'large' : 'small') . '");
				$(".arcadeGameDescript").css("padding-bottom","0.2rem");
				$(".arcadeGameDescript").css("padding-top","0.2rem");
				$(".arcadeGameDescript").css("line-height","' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '125' : '110') . '%");
				arcadeDescriptMore(1);
			}
			function arcadeListDisplayTwo()
			{
				arcadeDescriptInit();
				$(".arcade_light_list_ic_body").css("margin-left", "-0.5rem");
				$(".arcade_light_list_cell").css("padding-right", "0.5rem");
				$(".arcade_light_list_cell").css("align-self", "flex-start");
				$(".arcade_light_list_cell").css("max-width", "50%");
				$(".arcade_light_list_cell").css("min-width", "50%");
				$(".arcade_light_list_cell").css("width", "50%");
				$(".arcade_light_list_cell").css("flex", "1 1 50%");
				$(".arcade_light_list_cell").css("justify-content", "space-around");
				$(".arcadeListIC").css({"display":"block","margin":"0rem 0.25rem","padding":"0px","width":"100%","max-width":"100%","min-width":"100%","font-size":"medium","font-weight":"900","left":"0rem","padding-left":"1.15rem"});
				$(".arcadeGameData").css("font-size", "large");
				$(".arcadeGameData").css("line-height", "125%");
				$(".arcade_light_list_listicon").css("width", "0.75rem");
				$(".arcade_light_list_listicon").css("height", "0.75rem");
				$(".arcade_light_list_listicon").css("padding-top", "' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '0.025' : '0.05') . 'rem");
				$(".arcadeGameDescript").css("font-size","medium");
				$(".arcadeGameDescript").css("padding-bottom","0.2rem");
				$(".arcadeGameDescript").css("line-height","' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '125' : '110') . '%");
				arcadeDescriptMore(2);
			}
			function arcadeDescriptInit() {
				$("a.arcade_read_more").remove();
				$(".arcade_ellipsis").remove();
			}
			function arcadeDescriptMore(arcadeOrientation = 1) {
				$(".arcade_light_list_descript").each(function(event){
					var myhtml = $(this).text();
					var max_lines = 3, line_height = parseInt($(this).css("line-height")), descriptHeight = parseInt($(this).height());
					var descriptCopy = document.createElement("div");
					var mymaxheight = parseInt(descriptHeight / line_height);
					descriptCopy.style = "overflow-y: hidden;max-height:" + mymaxheight + ";height: " + mymaxheight + ";";
					var target_width = parseInt($(this).width());
					var fit = myhtml.length;
					for (var i = 0; i < fit; ++i) {
						descriptCopy.innerHTML += myhtml[i];
						if (i*22  > target_width) {
							fit = i - 1;
							break;
						}
					}
					var max_length = arcadeOrientation == 1 ? parseInt((fit * (max_lines+1)) * 1.9) : parseInt((fit * (max_lines+1)) * 2.1);
					if(myhtml.length > max_length){
						if (!$(this).find($(".shortgame_descript")).length) {
							var short_content = \'<div style="display: inline;" class="shortgame_descript">\' + $(this).html().substr(0,max_length) + \'<span class="arcade_ellipsis">...</span></div>\';
						}
						else {
							var short_content = \'<div style="display: inline;" class="shortgame_descript">\' + $(this).find($(".shortgame_descript")).html() + \'<span class="arcade_ellipsis">...</span></div>\';
						}
						var long_content = $(this).html().substr(max_length);
						if (!$(this).find($(".arcade_more_text")).length) {
							$(this).html(short_content +
							\'<div class="arcade_more_text" style="display:none;">\'+long_content+\'</div>\' +
							\'<a href="#" class="arcade_read_more" style="display: block;font-size: ' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? 'medium' : 'small') . ';font-style: oblique;">' . $txt['arcade_defiant_read_more'] . '</a>\');
						}
						else {
							$(this).html(short_content +
							\'<div class="arcade_more_text" style="display:none;">\'+$(this).find($(".arcade_more_text")).html()+\'</div>\' +
							\'<a href="#" class="arcade_read_more" style="display: block;font-size: ' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? 'medium' : 'small') . ';font-style: oblique;">' . $txt['arcade_defiant_read_more'] . '</a>\');
						}

						$(this).find("a.arcade_read_more").click(function(event){
							event.preventDefault();
							$(this).hide();
							$(this).parents(".arcadeGameDescript").find(".arcade_ellipsis").remove();
							$(this).parents(".arcadeGameDescript").find(".arcade_more_text").css("display", "inline");
							$(this).parents(".arcadeGameDescript").find(".arcade_more_text").show();
						});
					}
				});
			}
			$(document).ready(function() {
				arcadeListTableRow();
			});
		</script>';
}

?>