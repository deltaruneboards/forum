<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_above()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info;

	$selected = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	if (!empty($rom)) {
		template_arcade_rom();
		return;
	}
	$rom2 = !empty($rom) ? '_rom' : '';
	$romManage = !empty($rom) ? 'rom' : '';
	$romTxt = !empty($rom) ? 'rom_' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$_SESSION['arcade_shout_session'] = !empty($_SESSION['arcade_shout_session']) ? $_SESSION['arcade_shout_session'] : '';
	$add_cats = [];

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'highscore')
		return;

	if ( $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'search')
	{
		$categories = ArcadeCats($_SESSION['current_cat']);

		// SMF 2.0 / 2.1 css differs for inner title bg
		$divbg = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'titlebg' : 'cat_bar';
		$spanbg = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '' : ' class="catbg"';
		$common = !empty($_SESSION['isArcadeMobile']) ? 'overflow: hidden;margin: 0 auto;padding-left: 1px;display: block;width: 97%;min-width: 97%;max-width: 97%;left: -4px;border: 0px;position: relative;' : 'width: 33.3%;padding: 3px;';
		$commonTitle = !empty($_SESSION['isArcadeMobile']) ? 'display: block;width: 97%;min-width: 98vw;max-width: 98vw;left: -4px;border: 0px;position: relative;' : '';

		echo '
	<div class="clear cat_bar" style="' . $commonTitle . 'position: relative;bottom: -2px;">
		<h3 class="catbg centertext" style="vertical-align: middle;">
			<span style="clear: right;">', $txt[$romTxt . 'arcade'], '</span>
		</h3>
	</div>
	<div class="arcade_up_contain windowbg">
		<div class="innerframe" translate="no">
			<div style="clear: both;display: block;border-collapse: collapse;width: 100%;position: relative;" class="tborder table_grid">
				<div style="clear: both;display: inline-flex;width: 100%;">
					<div id="arcade_enterprisea_left" class="windowbg smalltext" style="clear: both;margin-top: 0px;margin-right: 0px;padding-right: 0px;display: flex;flex-direction: column;width: 100%;vertical-align: top;border-right: 0.05em groove;' . $common . 'font-size: 1.0em;">
						<div id="arcade_enterprisea_topl">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['arcade_random_game'],'</span></h3>
							</div>
							<div style="height: auto;overflow: hidden;display: inline-flex;flex-direction: column;align-items: center;justify-content: center;padding: 25% 10%;width: 100%;" id="arcade_randombg">
								<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;height: 100%;width: 100%;padding: 0px;margin: 0px;position: relative;">
									<div id="arcade_random" style="position: relative;margin: 0 auto;width: 100%;justify-content: stretch;display: flex;flex-direction: column;">', ArcadeRandomGamesA(1, $rom, true), '</div>
									<div style="display: inline-flex;align-self: center;align-items: stretch;justify-content: center;padding-top: 1.0rem;">
										<button class="arcade_pushable" id="arcade_randgame">
											<span class="arcade_shadow"></span>
											<span class="arcade_front">
												' . $txt['arcade_change_rgame'] . '
											</span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;margin-top: auto;">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;"><label for="arcade_sortby" style="display: block;font-weight: bold;text-align: center;">', $txt['arcade_game_sort'], '</label></span></h3>
							</div>
							<div class="smalltext" style="font-size: 1.0em;display: inline-flex;flex-direction: column;justify-content: center;align-items: center;position: relative;padding: 1rem 0rem;">
								<form action="', $scripturl, '?action=' . $arcadeAction . ';sa=list" method="post">
									<select title="' . $txt['arcade_game_sort'] . '" id="arcade_sortby" name="sortby" onchange="window.location.href=this.value;">
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=reset">', $txt['arcade_sort_by'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=age"' . ($_SESSION['arcade_sortby' . $rom2] === 'age' ? $selected : '') . '>', $txt['arcade_age'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=nocat"' . ($_SESSION['arcade_sortby' . $rom2] === 'nocat' ? $selected : '') . '>', $txt['arcade_LatestListNoCat'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=a2z"' . ($_SESSION['arcade_sortby' . $rom2] === 'a2z' ? $selected : '') . '>', $txt['arcade_a2z'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=z2a"' . ($_SESSION['arcade_sortby' . $rom2] === 'z2a' ? $selected : '') . '>', $txt['arcade_z2a'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=plays"' . ($_SESSION['arcade_sortby' . $rom2] === 'plays' ? $selected : '') . '>', $txt['arcade_plays'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=plays_reverse"' . ($_SESSION['arcade_sortby' . $rom2] === 'plays_reverse' ? $selected : '') . '>', $txt['arcade_playsl'], '</option>
										' . (empty($rom) ? '<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=champion"' . ($_SESSION['arcade_sortby' . $rom2] === 'champion' ? $selected : '') . '>' . $txt['arcade_champion'] . '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=champs"' . ($_SESSION['arcade_sortby' . $rom2] === 'champs' ? $selected : '') . '>' . $txt['arcade_latest_champions'] . '</option>' : '') . '
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=rating"' . ($_SESSION['arcade_sortby' . $rom2] === 'rating' ? $selected : '') . '>', $txt['arcade_rating'], '</option>', (!$user_info['is_guest'] ? '
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=favorites"' . ($_SESSION['arcade_sortby' . $rom2] === 'favorites' ? $selected : '') . '>' . $txt['arcade_favs'] . '</option>' : ''), '
									</select>
								</form>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;justify-content: flex-end;">
							<div class="' . $divbg . ' centertext" style="width: 100%;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;' . $commonTitle . '">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['latest_games'] ,'</span></h3>
							</div>
							<div id="arcadeNewestGamesA" style="display: flex;align-items: top;justify-content: left;min-height: 10rem;">',  ArcadeNewestGamesA($arcadeModSettings['skin_latest_gamesA'], $rom) . '</div>
						</div>
					</div>';

		echo '
					<div id="arcade_enterprisea_center" class="windowbg smalltext" style="clear: both;margin:0 auto;margin-top: 0px;display: flex;flex-direction: column;width: 100%;vertical-align: top;font-size: 1.0em;border-left: 0.05em groove;border-right: 0.05em groove;' . $common . '">
						<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
							<h3'. $spanbg . '><span style="font-weight: bold;">' . $txt['latest_champs'] . '</span></h3>
						</div>
						<div class="windowbg2" style="margin:5px 2px 5px 2px;font-size: 1.0em;text-align:left;width: 98%;">' . ArcadeNewChampsA($arcadeModSettings['skin_latest_champsA'], $rom) . '</div>
						<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
							<h3'. $spanbg . '><span style="font-weight: bold;">
								<img src="' . $settings['default_images_url'] . '/arc_icons/gold.gif" alt="" />
								' . ($_SESSION['current_cat'] == 'all' ? $txt['arcade_champs'] : sprintf($txt['cat_champs'], $context['cat_name'])) . '
								<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" alt="" />
							</span></h3>
						</div>
						<div style="display: block;width: 100%;border: 0px;border-spacing: 2px;border-collapse: separate;">
							<div style="clear: both;display: inline-flex;width: 100%;">';

		$bp = ArcadeChampsA(3, $_SESSION['current_cat'] == 'all' ? 'wins' : 'cats');
		$score_poss = 0;
		if(is_array($bp))
		{
			foreach ($bp as $out)
			{
				$score_poss++;
				echo '
									<div class="windowbg2 centertext" style="display: inline;width: 33%;border:0px;font-size: 1.0em;">
										<img src="', $settings['default_images_url'], '/arc_icons/', $score_poss, '.gif" style="margin-bottom: 3px" alt="" /><br />
										', $out['avatar'], '<br /><span style="font-weight: bold;">', $out['link'], '</span><br />
										', $txt['win'], ' ', $out['champions'], '
									</div>';
			}
		}
		else
			echo '
									<div class="windowbg2 smalltext centertext" style="display: inline;border:0px;font-size: 1.0em;">
										', $txt['no_new_champs'], '
									</div>';

		echo '
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-top: auto;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['latest_scores'] ,'</span></h3>
							</div>
							<div class="windowbg2" style="border:0px;margin:5px 2px 1px 2px;font-size: 1.0em;text-align:left;width: 98%;">', ArcadeLatestA($arcadeModSettings['skin_latest_scoresA'], $rom), '</div>
						</div>';
		echo '
					</div>
					<div id="arcade_enterprisea_right" class="windowbg smalltext" style="clear: both;margin-top: 0px;width: 100%;display: flex;flex-direction: column;border-left: 0.05em groove;' . $common . 'vertical-align: top;font-size: 1.0em;">
						<div id="arcade_enterprisea_topr">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:4px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['arcade_daily'], '</span></h3>
							</div>
							<div style="height: auto;overflow: hidden;display: inline-flex;flex-direction: column;align-items: center;justify-content: center;padding: 25% 10%;width: 100%;" id="arcade_challengebg">';

		$game = getGameOfDay($rom);
		if (!empty($game['url']['play']))
		{
			echo '
								<div class="smalltext" style="padding: 0px 5px 0px 5px;position: relative;margin: 0 auto;text-align: center;">
									<div class="centertext" style="margin: 0px;border-bottom:1px solid #808080;font: 1.1em Blippo, fantasy;padding-bottom: 0.15rem;">
										<a href="', $game['url']['play'], '">', (strlen($game['name']) >= 23 ? substr($game['name'],0,22) . '...' : $game['name']), '</a>
									</div>
									<div style="padding: 0.5em 0px 0.5em 0px;height: 3rem;display: flex;flex-direction: column;text-align: center;margin: 0 auto;">
										<a href="', $game['url']['play'], '">
											<img style="width: 40px;height: 40px;vertical-align: middle;padding-bottom: 0.5em;" class="imgBorder" src="', $game['thumbnail'], '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
										</a>
									</div>';

			if($game['description'])
				echo '
									<div title="' . $game['description'] . '" style="scrollbar-width: thin;text-align: center;min-height: 3em;max-height: 5.8em;overflow-x: hidden;overflow-y: auto;font-size:0.95em;padding-left: 0.3em;vertical-align: bottom;display: inline-flex;text-align: center;margin: 0 auto;overflow-wrap: break-word;">', $game['description'], '</div>';
			else
				echo '
									<div style="min-height: 3em;max-height: 7em;overflow: auto;font-size:0.95em;padding: 0.2em 0.3302em 0.5em 0em;vertical-align: bottom;display: inline-flex;">', $txt['no_description'], '</div>';
			echo '
								</div>';
		}


		echo '
								<div>
									<div class="titlebg" style="margin:4px 0px 5px 0px;border-bottom:1px solid #808080; text-align:center;font-size:1.1em;">', $txt['todays_scores'], '</div>
									<div style="margin: 5px 0px 0px 5px">', ArcadeDailyChallengeA($game, $rom), '</div>';

		if ($context['CH_error'])
			echo '
									<div class="smalltext centertext">', $txt['arcade_daily_none'], '</div>';

		echo '
								</div>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;margin-top: auto;">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:0px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">' . $txt['arcade_game_search'] . '</span></h3>
							</div>
							<div class="smalltext" style="font-size: 1.0em;display: inline-flex;flex-direction: column;justify-content: center;align-items: center;position: relative;padding: 1rem 0rem;">
								<form name="search" action="' . $scripturl . '?action=' . $arcadeAction . ';sa=search" method="post" onsubmit="return empty();">
									<input autocomplete="off" id="gamesearch" type="text" name="name" value="' . (isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '') . '" />
									<input class="button_submit smalltext" type="submit" value="' . $txt['arcade_search_go'] . '"  name="submit1" />
									<div id="suggest_gamesearch" class="game_suggest"></div>
									<script>
										var gSuggest = new gameSuggest("' . $context['session_id'] . '", "gamesearch");
									</script>
								</form>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;justify-content: flex-end;">
							<div class="' . $divbg . ' centertext" style="width: 100%;position: relative;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['most_played'], '</span></h3>
							</div>
							<div id="arcadePopularA" style="display: flex;align-items: top;justify-content: right;min-height: 10rem;">', ArcadePopularA($arcadeModSettings['skin_most_popularA']), '</div>
						</div>
					</div>
				</div>
			</div>';

		if (empty($arcadeModSettings['arcadeDropCatA']) && !empty($context['arcade']['cats']))
		{
			echo '
			<div style="padding-top: 0.5em;"><span style="display: none;">&nbsp;</span></div>
			<div class="title_bar" style="width: 100%;">
				<h4 class="titlebg centertext" style="vertical-align: middle;">
					<span style="clear: right;"><a title="', $txt['arcade_defcat'], '" href="', $scripturl, '?action=' . $arcadeAction . ';category=0">', $txt['arcade_game_cats'], '</a></span>
				</h4>
			</div>', $categories;
		}

		if (!empty($arcadeModSettings['arcadeDropCatA'])) {
			$add_cats['cats'] = [
				'text' => 'view_cat',
				'image' => 'arcade_user_cat.gif',
				'url' => '',
				'lang' => 1,
				'id' => 'arcade_cats_button',
				'is_last' => 1
			];
		}

		echo '
		</div>
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div style="width:100%;display: inline;" class="smalltext">
		<div style="display: inline-flex;padding-left: 0.25rem !important;align-items: flex-start;">', template_button_strip(array_filter(array_merge($context['arcade_tabs'], $add_cats)), 'left', []), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
		<div class="smalltext" style="clear: right;padding:8px 0.25rem 0px 0px;float: right;display: inline;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>';

		echo '
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div><span style="line-height: 0.2rem;">&nbsp;</span></div>
	<div style="height: 10px;clear: both;">
		<span style="display: none;">&nbsp;</span>
	</div>
	<script>
		let arcadebgfade = 0, brightnessValue = 10, arcadechange = 0, arcadenewbg = "", arcadenewbgcolor = "";
		function arcadebgfader() {
			if (arcadechange == 1 && brightnessValue >= 10) {
				arcadechange = 0;
				arcadenewbgcolor = "rgba(255, 255, 255, ";
			}
			else if (arcadechange == 0 && brightnessValue <= 7) {
				arcadechange = 1;
				arcadenewbgcolor = "rgba(0, 0, 0, ";
			}

			if (arcadechange == 0) {
				brightnessValue--;
			}
			else {
				brightnessValue++;
			}
			arcadenewbg = "brightness(" + String(brightnessValue/10) + ")";
			arcadebgfade = arcadenewbgcolor + String((10 - brightnessValue)/10) + ")";
			document.getElementById("arcade_randombg").animate([
				{opacity: (brightnessValue/10)},
				{transform: "scale(1.2)"}
				], {
					duration: 8000,
					iterations: Infinity,
					direction: "alternate"
				}
			);

		}
		$(document).ready(function(){' . (!empty($arcadeModSettings['arcadeDropCatA']) ? '
			$("a.button_strip_cats").html(\'' . (trim(preg_replace('/>\s+</', '><', ArcadeCategoryDropdown($rom, 'enterprisea')))) . '\');' : '') . '
			let newGamesHeight = Math.round($("#arcadeNewestGamesA :first-child").outerHeight(true)), popularGamesHeight = Math.round($("#arcadePopularA :first-child").outerHeight(true));
			if (newGamesHeight > popularGamesHeight) {
				$("#arcadePopularA").height(newGamesHeight);
				$("#arcadeNewestGamesA").height(newGamesHeight);
			}
			else if (newGamesHeight < popularGamesHeight) {
				$("#arcadeNewestGamesA").height(popularGamesHeight);
				$("#arcadePopularA").height(popularGamesHeight);
			}
			let arcadeLeftHeight = Math.round($("#arcade_enterprisea_left").height()), arcadeCenterHeight = Math.round($("#arcade_enterprisea_center").height());
			if (arcadeLeftHeight > arcadeCenterHeight) {
				$("#arcade_enterprisea_center").height(arcadeLeftHeight);
			}
			$("#arcade_enterprisea_left").css("overflow", "hidden");
			$("#arcade_arcade_randombg").css("filter", "contrast(130%)");
			arcadebgfader();
			$("#arcade_randgame").on("click", function() {
				$.ajax({
					type: "GET",
					url: "' . $scripturl . '?action=arcade;rom=' . $rom . ';sa=randomxml;' . $context['session_var'] . '=' . $context['session_id'] . ';xml",
					dataType: "xml",
					success: function(xmlData) {
							var $xml = $(xmlData);
							$xml.find("gamexml").each(function() {
								var play = $(this).find("play").text();
								var description = $(this).find("description").text();
								var thumb = $(this).find("thumbnail").text();
								var gamename = $(this).find("gamename").text();
								var gamedesctitle = description.replace(/(<([^>]+)>)/gi, "");
								$("#randomgamedescript").html(description);
								$("#randomgamedescript").prop("title", gamedesctitle);
								$("#randomgamelink").attr("href", play);
								$("#randomgamenamelink").attr("href", play);
								$("#randomgamenamelink").text(gamename);
								$("#randomgamethumbnail").attr("src", thumb);
								$("#randomgamethumbnail").prop("title", gamename);
							});
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error("Error loading XML data:", textStatus, errorThrown);
					}
				});
			});
			let arcadeenterpriseatopl = $("#arcade_enterprisea_topl").height();
			$("#arcade_enterprisea_topr").height(arcadeenterpriseatopl);
			$("#arcade_enterprisea_topl").height(arcadeenterpriseatopl);
			$(".infiniteslide_wrap").height(arcadeLeftHeight);
		});
	</script>';
	}
}

function template_arcade_rom()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info;

	$selected = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$rom2 = !empty($rom) ? '_rom' : '';
	$romManage = !empty($rom) ? 'rom' : '';
	$romTxt = !empty($rom) ? 'rom_' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$_SESSION['arcade_shout_session'] = !empty($_SESSION['arcade_shout_session']) ? $_SESSION['arcade_shout_session'] : '';
	$add_cats = [];

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'highscore')
		return;

	if ( $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'search')
	{
		$categories = ArcadeCats($_SESSION['current_cat']);

		// SMF 2.0 / 2.1 css differs for inner title bg
		$divbg = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'titlebg' : 'cat_bar';
		$spanbg = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '' : ' class="catbg"';
		$common = !empty($_SESSION['isArcadeMobile']) ? 'overflow: hidden;margin: 0 auto;padding-left: 1px;display: block;width: 97%;min-width: 97%;max-width: 97%;left: -4px;border: 0px;position: relative;' : 'width: 33.3%;padding: 3px;';
		$commonTitle = !empty($_SESSION['isArcadeMobile']) ? 'display: block;width: 97%;min-width: 98vw;max-width: 98vw;left: -4px;border: 0px;position: relative;' : '';

		echo '
	<div class="clear cat_bar" style="' . $commonTitle . 'position: relative;bottom: -2px;">
		<h3 class="catbg centertext" style="vertical-align: middle;">
			<span style="clear: right;">', $txt[$romTxt . 'arcade'], '</span>
		</h3>
	</div>
	<div class="arcade_up_contain windowbg">
		<div class="innerframe" translate="no">
			<div style="clear: both;display: block;border-collapse: collapse;width: 100%;position: relative;" class="tborder table_grid">
				<div style="clear: both;display: inline-flex;width: 100%;">
					<div id="arcade_enterprisea_left" class="windowbg smalltext" style="clear: both;margin-top: 0px;margin-right: 0px;padding-right: 0px;display: flex;flex-direction: column;width: 100%;vertical-align: top;border-right: 0.05em groove;' . $common . 'font-size: 1.0em;">
						<div id="arcade_enterprisea_topl">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['arcade_random_game'],'</span></h3>
							</div>
							<div style="height: auto;overflow: hidden;display: inline-flex;flex-direction: column;align-items: center;justify-content: center;padding: 25% 10%;width: 100%;" id="arcade_randombg">
								<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;height: 100%;width: 100%;padding: 0px;margin: 0px;position: relative;">
									<div id="arcade_random" style="position: relative;margin: 0 auto;width: 100%;justify-content: stretch;display: flex;flex-direction: column;">', ArcadeRandomGamesA(1, $rom, true), '</div>
									<div style="display: inline-flex;align-self: center;align-items: stretch;justify-content: center;padding-top: 1.0rem;">
										<button class="arcade_pushable" id="arcade_randgame">
											<span class="arcade_shadow"></span>
											<span class="arcade_front">
												' . $txt['arcade_change_rgame'] . '
											</span>
										</button>
									</div>
								</div>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;margin-top: auto;">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;"><label for="arcade_sortby" style="display: block;font-weight: bold;text-align: center;">', $txt['arcade_game_sort'], '</label></span></h3>
							</div>
							<div class="smalltext" style="font-size: 1.0em;display: inline-flex;flex-direction: column;justify-content: center;align-items: center;position: relative;padding: 1rem 0rem;">
								<form action="', $scripturl, '?action=' . $arcadeAction . ';sa=list" method="post">
									<select title="' . $txt['arcade_game_sort'] . '" id="arcade_sortby" name="sortby" onchange="window.location.href=this.value;">
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=reset">', $txt['arcade_sort_by'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=age"' . ($_SESSION['arcade_sortby' . $rom2] === 'age' ? $selected : '') . '>', $txt['arcade_age'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=nocat"' . ($_SESSION['arcade_sortby' . $rom2] === 'nocat' ? $selected : '') . '>', $txt['arcade_LatestListNoCat'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=a2z"' . ($_SESSION['arcade_sortby' . $rom2] === 'a2z' ? $selected : '') . '>', $txt['arcade_a2z'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=z2a"' . ($_SESSION['arcade_sortby' . $rom2] === 'z2a' ? $selected : '') . '>', $txt['arcade_z2a'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=plays"' . ($_SESSION['arcade_sortby' . $rom2] === 'plays' ? $selected : '') . '>', $txt['arcade_plays'], '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=plays_reverse"' . ($_SESSION['arcade_sortby' . $rom2] === 'plays_reverse' ? $selected : '') . '>', $txt['arcade_playsl'], '</option>
										' . (empty($rom) ? '<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=champion"' . ($_SESSION['arcade_sortby' . $rom2] === 'champion' ? $selected : '') . '>' . $txt['arcade_champion'] . '</option>
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=champs"' . ($_SESSION['arcade_sortby' . $rom2] === 'champs' ? $selected : '') . '>' . $txt['arcade_latest_champions'] . '</option>' : '') . '
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=rating"' . ($_SESSION['arcade_sortby' . $rom2] === 'rating' ? $selected : '') . '>', $txt['arcade_rating'], '</option>', (!$user_info['is_guest'] ? '
										<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=favorites"' . ($_SESSION['arcade_sortby' . $rom2] === 'favorites' ? $selected : '') . '>' . $txt['arcade_favs'] . '</option>' : ''), '
									</select>
								</form>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;justify-content: flex-end;">
							<div class="' . $divbg . ' centertext" style="width: 100%;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;' . $commonTitle . '">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['latest_games'] ,'</span></h3>
							</div>
							<div id="arcadeNewestGamesA" style="display: flex;align-items: top;justify-content: left;min-height: 10rem;">',  ArcadeNewestGamesA($arcadeModSettings['skin_latest_gamesA'], $rom) . '</div>
						</div>
					</div>';

		if (empty($arcadeModSettings['arcade_skin_a_romcenter'])) {
			echo '
					<div id="arcade_enterprisea_center" class="windowbg smalltext" style="clear: both;margin:0 auto;margin-top: 0px;display: flex;flex-direction: column;width: 100%;vertical-align: top;font-size: 1.0em;border-left: 0.05em groove;border-right: 0.05em groove;' . $common . '">
						<div style="display: flex;height: 100%;align-items: center;flex-direction: column;flex: 1;justify-content: center;">
							<div style="display: inline-block;clear: both;width: 25%;height: 5%;">
								<div class="centertext" style="display: inline-block;padding: 0px;font-weight: bold;font-style: italic;">', $txt['arcade_shouts'], '</div>
							</div>
							<div style="display: inline-block;clear: both;width: 100%;display: flex;flex-direction: row;justify-content: center;align-items: center;height: 75%;align-items: stretch;">
								<div style="min-width: 90%;display: inline-block;padding: 0px;">
									<div id="arcade_shouts" class="smalltext" style="scrollbar-width: thin;display: inline-block;width: 99%; height: 250px; overflow: auto;font-size:0.7rem;margin: 0 auto;align-items: stretch;min-height: 100%;">
										', ArcadeInfoShouts($rom), '
									</div>
								</div>
							</div>
							<div style="display: inline-block;clear: both;width: 100%;margin-top: auto;font-size:0.5rem;max-height: 20%;">
								<div style="display: inline-block;;padding: 1px;margin: 0; margin-top: 5px; text-align: center;">
									<input size="105" maxlength="100" onkeypress="submitShoutOnEnter(event);" class="largetext" name="the_shout" style="width: 80%;margin-top: 1ex; height: 25px;" id="arcadeShoutInputB" />
									<div style="inline-block"><span style="display: none;">&nbsp;</span></div>
									<input style="margin-top: 4px;" onclick="shoutArcadeNewShoutB()" class="mediumtext" type="submit" id="arcadeShoutSubmit" name="shout" value="', $txt['arcade_shout'], '" />
								</div>
							</div>
						</div>';
		}
		else {
			echo '
					<div id="arcade_enterprisea_center" class="windowbg smalltext" style="clear: both;margin:0 auto;margin-top: 0px;display: flex;overflow: hidden;height: auto;width: 100%;vertical-align: top;font-size: 1.0em;border-left: 0.05em groove;border-right: 0.05em groove;' . $common . '">
						<div style="display: flex;height: 100%;align-items: center;flex-direction: column;flex: 1;justify-content: center;">
							<div style="display: inline-flex;clear: both;width: 100%;display: flex;flex-direction: row;justify-content: center;align-items: center;height: 98%;align-items: stretch;">
								<div style="min-width: 90%;display: inline-flex;padding: 0px;">
									<div id="arcade_shouts" class="smalltext" style="scrollbar-width: thin;display: inline-flex;width: 100%; height: 250px; overflow: hidden;font-size:0.7rem;justify-content: center;align-items: stretch;min-height: 100%;">
										', ArcadeThumbnailListA($rom), '
									</div>
								</div>
							</div>
						</div>';
		}

		echo '
					</div>
					<div id="arcade_enterprisea_right" class="windowbg smalltext" style="clear: both;margin-top: 0px;width: 100%;display: flex;flex-direction: column;border-left: 0.05em groove;' . $common . 'vertical-align: top;font-size: 1.0em;">
						<div id="arcade_enterprisea_topr">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:4px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['arcade_daily'], '</span></h3>
							</div>
							<div style="height: auto;overflow: hidden;display: inline-flex;flex-direction: column;align-items: center;justify-content: center;padding: 25% 10%;width: 100%;" id="arcade_challengebg">';

		$game = getGameOfDay($rom);
		if (!empty($game['url']['play']))
		{
			echo '
								<div class="smalltext" style="padding: 0px 5px 0px 5px;position: relative;margin: 0 auto;text-align: center;">
									<div class="titlebg centertext" style="margin: 0px;border-bottom:1px solid #808080;font-size:1.1em;">
										<a href="', $game['url']['play'], '">', (strlen($game['name']) >= 23 ? substr($game['name'],0,22) . '...' : $game['name']), '</a>
									</div>
									<div style="padding: 0.5em 0px 0.5em 0px;height: 3rem;display: flex;flex-direction: column;text-align: center;margin: 0 auto;">
										<a href="', $game['url']['play'], '">
											<img style="width: 40px;height: 40px;vertical-align: middle;padding-bottom: 0.5em;" class="imgBorder" src="', $game['thumbnail'], '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
										</a>
									</div>';

			if($game['description'])
				echo '
									<div title="' . $game['description'] . '" style="scrollbar-width: thin;text-align: center;min-height: 3em;max-height: 5.8em;overflow-x: hidden;overflow-y: auto;font-size:0.95em;padding-left: 0.3em;vertical-align: bottom;display: inline-flex;text-align: center;margin: 0 auto;overflow-wrap: break-word;">', $game['description'], '</div>';
			else
				echo '
									<div style="min-height: 3em;max-height: 7em;overflow: auto;font-size:0.95em;padding: 0.2em 0.3302em 0.5em 0em;vertical-align: bottom;display: inline-flex;">', $txt['no_description'], '</div>';
			echo '
								</div>';
		}


		echo '
								<div>
									<div class="titlebg" style="margin:4px 0px 5px 0px;border-bottom:1px solid #808080; text-align:center;font-size:1.1em;">', $txt['todays_scores'], '</div>
									<div style="margin: 5px 0px 0px 5px">', ArcadeDailyChallengeA($game, $rom), '</div>';

		if ($context['CH_error'])
			echo '
									<div class="smalltext centertext">', $txt['arcade_daily_none'], '</div>';

		echo '
								</div>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;margin-top: auto;">
							<div class="' . $divbg . ' centertext" style="width: 100%;margin-bottom:0px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">' . $txt['arcade_game_search'] . '</span></h3>
							</div>
							<div class="smalltext" style="font-size: 1.0em;display: inline-flex;flex-direction: column;justify-content: center;align-items: center;position: relative;padding: 1rem 0rem;">
								<form name="search" action="' . $scripturl . '?action=' . $arcadeAction . ';sa=search" method="post" onsubmit="return empty();">
									<input autocomplete="off" id="gamesearch" type="text" name="name" value="' . (isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '') . '" />
									<input class="button_submit smalltext" type="submit" value="' . $txt['arcade_search_go'] . '"  name="submit1" />
									<div id="suggest_gamesearch" class="game_suggest"></div>
									<script>
										var gSuggest = new gameSuggest("' . $context['session_id'] . '", "gamesearch");
									</script>
								</form>
							</div>
						</div>
						<div style="display: inline-flex;flex-direction: column;margin-top: auto;width: 100%;align-self: flex-end;">
							<div class="' . $divbg . ' centertext" style="width: 100%;position: relative;margin-bottom:3px;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['most_played'], '</span></h3>
							</div>
							<div id="arcadePopularA" style="display: flex;align-items: top;justify-content: right;min-height: 10rem;">', ArcadePopularA($arcadeModSettings['skin_most_popularA']), '</div>
						</div>
					</div>
				</div>
			</div>';

		if (empty($arcadeModSettings['arcadeDropCatA']) && !empty($context['arcade']['cats']))
		{
			echo '
			<div style="padding-top: 0.5em;"><span style="display: none;">&nbsp;</span></div>
			<div class="title_bar" style="width: 100%;">
				<h4 class="titlebg centertext" style="vertical-align: middle;">
					<span style="clear: right;"><a title="', $txt['arcade_defcat'], '" href="', $scripturl, '?action=' . $arcadeAction . ';category=0">', $txt['arcade_game_cats'], '</a></span>
				</h4>
			</div>', $categories;
		}

		if (!empty($arcadeModSettings['arcadeDropCatA'])) {
			$add_cats['cats'] = [
				'text' => 'view_cat',
				'image' => 'arcade_user_cat.gif',
				'url' => '',
				'lang' => 1,
				'id' => 'arcade_cats_button',
				'is_last' => 1
			];
		}

		echo '
		</div>
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div style="width:100%;display: inline;" class="smalltext">
		<div style="display: inline-flex;padding-left: 0.25rem !important;align-items: flex-start;">', template_button_strip(array_filter(array_merge($context['arcade_tabs'], $add_cats)), 'left', []), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
		<div class="smalltext" style="clear: right;padding:8px 0.25rem 0px 0px;float: right;display: inline;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>';

		echo '
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div><span style="line-height: 0.2rem;">&nbsp;</span></div>
	<div style="height: 10px;clear: both;">
		<span style="display: none;">&nbsp;</span>
	</div>';

		if (!empty($rom) && empty($arcadeModSettings['arcade_skin_a_romcenter'])) {
		echo '
	<script type="text/javascript">
		var myArcadeTimeoutVar;
		function changeArcadeShoutContentB()
		{
			var arcadeInterval = "' . abs(intval($arcadeModSettings['arcade_shout_interval'])) * 1000 . '";
			setInterval(function() {
				var xhttp = new XMLHttpRequest();
				var str = "' . $_SESSION['arcade_shout_session'] . '";
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var i = 0, arcShoutRefresh = document.getElementById("arcade_shouts");
						if (this.responseText.indexOf(\'<div class="arcadeShout"\') > -1 && arcShoutRefresh) {
							var arcadeShoutHtmlParser = new DOMParser();
							var arcadeShoutHtml = arcadeShoutHtmlParser.parseFromString(this.responseText, "text/html").body;
							var arcadeAllShouts = arcadeShoutHtml.getElementsByClassName("arcadeShout");
							if (typeof arcadeAllShouts == "object") {
								arcShoutRefresh.innerHTML = "";
								var arcadeReverse = [], arcadeNewBox = arcadeAllShouts.length-1;
								for(i=arcadeAllShouts.length;i>0;i--) {
									arcadeReverse[arcadeNewBox] = arcadeAllShouts[i-1];
									arcadeNewBox--;
								}
								for(i=0;i<arcadeReverse.length>0;i++)
									arcShoutRefresh.appendChild(arcadeReverse[i]);
								arcShoutRefresh.scrollTop = 0;
							}
						}
					}
				};
				xhttp.open("GET", "' . $scripturl . '?action=' . $arcadeAction . ';sa=shouts;arcade_shout_session="+str + ";xml", true);
				xhttp.send();
			}, arcadeInterval);
		}
		function changeArcadeShoutContentNowB()
		{
			var xhttp = new XMLHttpRequest();
			var str = "' . $_SESSION['arcade_shout_session'] . '";
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var i = 0, arcShoutRefresh = document.getElementById("arcade_shouts");
					if (this.responseText.indexOf(\'<div class="arcadeShout"\') > -1 && arcShoutRefresh) {
						var arcadeShoutHtmlParser = new DOMParser();
						var arcadeShoutHtml = arcadeShoutHtmlParser.parseFromString(this.responseText, "text/html").body;
						var arcadeAllShouts = arcadeShoutHtml.getElementsByClassName("arcadeShout");
						if (typeof arcadeAllShouts == "object") {
							arcShoutRefresh.innerHTML = "";
							var arcadeReverse = [], arcadeNewBox = arcadeAllShouts.length-1;
							for(i=arcadeAllShouts.length;i>0;i--) {
								arcadeReverse[arcadeNewBox] = arcadeAllShouts[i-1];
								arcadeNewBox--;
							}
							for(i=0;i<arcadeReverse.length>0;i++)
								arcShoutRefresh.appendChild(arcadeReverse[i]);
							arcShoutRefresh.scrollTop = 0;
						}
					}
				}
			};
			xhttp.open("GET", "' . $scripturl . '?action=' . $arcadeAction . ';sa=shouts;arcade_shout_session="+str + ";xml", true);
			xhttp.send();
		}
		function shoutArcadeNewShoutB() {
			if (document.getElementById("arcadeShoutInputB")) {
				if (document.getElementById("arcadeShoutInputB").value)
					shout = encodeURIComponent(document.getElementById("arcadeShoutInputB").value).replace(/\'/g, "%27");
				else
					return false;
			}
			else
				return false;
			document.getElementById("arcadeShoutInputB").value = "";
			setTimeout(function(){
				var xhttpx = new XMLHttpRequest();
				xhttpx.open("POST", "' . $scripturl . '?action=arcade;sa=newShout", true);
				xhttpx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttpx.onreadystatechange = function() {
					if (xhttpx.readyState == 4 && xhttpx.status == 200) {
						var response = xhttpx.responseText;
						changeArcadeShoutContentNowB();
					}
				};
				xhttpx.send("arcade_shout_user_id=" + ' . $user_info['id'] . ' + "&arcade_shout_text=" + shout);
			}, 500);
		}
		function submitShoutOnEnter(event) {
			if (event.keyCode == 13)
				document.getElementById("arcadeShoutSubmit").click();
		}
	</script>';
		}
		echo '
	<script>
		let arcadebgfade = 0, brightnessValue = 10, arcadechange = 0, arcadenewbg = "", arcadenewbgcolor = "";
		function arcadebgfader() {
			if (arcadechange == 1 && brightnessValue >= 10) {
				arcadechange = 0;
				arcadenewbgcolor = "rgba(255, 255, 255, ";
			}
			else if (arcadechange == 0 && brightnessValue <= 7) {
				arcadechange = 1;
				arcadenewbgcolor = "rgba(0, 0, 0, ";
			}

			if (arcadechange == 0) {
				brightnessValue--;
			}
			else {
				brightnessValue++;
			}
			arcadenewbg = "brightness(" + String(brightnessValue/10) + ")";
			arcadebgfade = arcadenewbgcolor + String((10 - brightnessValue)/10) + ")";
			document.getElementById("arcade_randombg").animate([
				{opacity: (brightnessValue/10)},
				{transform: "scale(1.2)"}
				], {
					duration: 8000,
					iterations: Infinity,
					direction: "alternate"
				}
			);

		}
		$(document).ready(function(){' . (!empty($arcadeModSettings['arcadeDropCatA']) ? '
			$("a.button_strip_cats").html(\'' . (trim(preg_replace('/>\s+</', '><', ArcadeCategoryDropdown($rom, 'enterprisea')))) . '\');' : '') . '
			let arcadeenterpriseatopl = $("#arcade_enterprisea_topl").height();
			$("#arcade_enterprisea_topr").height(arcadeenterpriseatopl);
			$("#arcade_enterprisea_topl").height(arcadeenterpriseatopl);
			let newGamesHeight = Math.round($("#arcadeNewestGamesA :first-child").outerHeight(true)), popularGamesHeight = Math.round($("#arcadePopularA :first-child").outerHeight(true));
			if (newGamesHeight > popularGamesHeight) {
				$("#arcadePopularA").height(newGamesHeight);
				$("#arcadeNewestGamesA").height(newGamesHeight);
			}
			else if (newGamesHeight < popularGamesHeight) {
				$("#arcadeNewestGamesA").height(popularGamesHeight);
				$("#arcadePopularA").height(popularGamesHeight);
			}
			let arcadeLeftHeight = Math.round($("#arcade_enterprisea_left").height()), arcadeCenterHeight = Math.round($("#arcade_enterprisea_center").height());
			$("#arcade_enterprisea_left").css("overflow", "hidden");
			$("#arcade_arcade_randombg").css("filter", "contrast(130%)");
			arcadebgfader();
			$("#arcade_randgame").on("click", function() {
				$.ajax({
					type: "GET",
					url: "' . $scripturl . '?action=arcade;rom=' . $rom . ';sa=randomxml;' . $context['session_var'] . '=' . $context['session_id'] . ';xml",
					dataType: "xml",
					success: function(xmlData) {
							var $xml = $(xmlData);
							$xml.find("gamexml").each(function() {
								var play = $(this).find("play").text();
								var description = $(this).find("description").text();
								var thumb = $(this).find("thumbnail").text();
								var gamename = $(this).find("gamename").text();
								var gamedesctitle = description.replace(/(<([^>]+)>)/gi, "");
								$("#randomgamedescript").html(description);
								$("#randomgamedescript").prop("title", gamedesctitle);
								$("#randomgamelink").attr("href", play);
								$("#randomgamenamelink").attr("href", play);
								$("#randomgamenamelink").text(gamename);
								$("#randomgamethumbnail").attr("src", thumb);
								$("#randomgamethumbnail").prop("title", gamename);
							});
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error("Error loading XML data:", textStatus, errorThrown);
					}
				});
			});
			let arcadeRightHeight = Math.round($("#arcade_enterprisea_right").height())
			$(".infiniteslide_box:first-child").css("height", "100%");' . (!empty($arcadeModSettings['arcade_shout_interval']) ? '
			changeArcadeShoutContentB();' : '') . '
			if (arcadeLeftHeight > arcadeCenterHeight) {
				$("#arcade_enterprisea_center").height(arcadeLeftHeight);
			}
		});
	</script>';
	}
}

function template_arcade_below()
{
	global $txt, $arcadeModSettings;

	if (empty($arcadeModSettings['arcadeList']))
		$arcadeModSettings['arcadeList'] = 0;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
			<div id="arcade_bottom" class="smalltext" style="text-align: center;">
				' . $txt['pdl_arcade_copyright'] . '
			</div>';
}

?>