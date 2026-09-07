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
	$rom2 = !empty($rom) ? '_rom' : '';
	$romManage = !empty($rom) ? 'rom' : '';
	$romTxt = !empty($rom) ? 'rom_' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && is_string($_REQUEST['sa']) ? strval($_REQUEST['sa']) : '';
	$context['current_arcade_sa'] = !empty($context['current_arcade_sa']) ? $context['current_arcade_sa'] : $_REQUEST['sa'];
	if ($_REQUEST['sa'] == 'highscore')
		return;

	if (in_array($_REQUEST['sa'], array('list', 'search')))
	{
		$categories = ArcadeCats($_SESSION['current_cat'], $rom);

		$divbg = 'cat_bar';
		$spanbg = ' class="catbg"';
		$commonLeft = 'width: 25%;padding: 3px;overflow-x: hidden;';
		$commonMid = 'width: 50%;padding: 3px;overflow-x: hidden;';
		$commonRight = 'width: 25%;padding: 3px;overflow-x: hidden;';
		$commonTitle = !empty($_SESSION['isArcadeMobile']) ? 'display: block;width: 97%;min-width: 98vw;max-width: 98vw;left: -4px;border: 0px;position: relative;' : '';

		echo '
	<div style="transform: scale(0.98);">
		<div class="clear cat_bar" style="' . $commonTitle . 'position: relative;', $context['arcade_smf_version'] == 'v2.1' ? 'bottom: -2px;' : '', '">
			<h3 class="catbg centertext" style="vertical-align: middle;">
				<span style="clear: right;">', $txt[$romTxt . 'arcade'], '</span>
			</h3>
		</div>
		<div class="arcade_up_contain windowbg arcade_skinc_border">
			<div class="innerframe" translate="no">
				<div class="tborder table_grid" style="clear: both;display: flex;flex-direction: column;border-collapse: collapse;width: 100%;position: relative;">
					<div style="clear: both;display: inline-flex;width: 100%;justify-content: start;">
						<div class="windowbg smalltext arcade_border_split_side" style="clear: both;margin-top: 0px;margin-right: 0px;padding-right: 0px;display: inline;vertical-align: top;position: relative;' . $commonLeft . 'font-size:0.85em;">
							<div class="' . $divbg . ' centertext" style="transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;' . $commonTitle . '">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['latest_games'] ,'</span></h3>
							</div>
							',  ArcadeNewestGames($arcadeModSettings['skin_latest_' . $romTxt . 'games'], $rom), (empty($rom) ? '
							<div class="' . $divbg . ' centertext" style="transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;margin-bottom:3px;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">' . $txt['latest_champs'] . '</span></h3>
							</div>
							<div style="margin:5px 2px 5px 2px;font-size:1.0em;text-align:left;">' . ArcadeNewChamps($arcadeModSettings['skin_latest_champs'], $rom) . '</div>' : '') . '
						</div>
						<div class="windowbg smalltext arcade_border_split_center" style="overflow: hidden;clear: both;margin:0 auto;margin-top: 0px;display: inline;width: 100%;vertical-align: top;font-size:0.85em;' . $commonMid . '">
							<div class="' . $divbg . ' centertext" style="margin-bottom:3px;border-radius: 3px;overflow: hidden;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['arcade_random_game'],'</span></h3>
							</div>
							<div style="margin-bottom: 3px;padding-top: 3px;font-size:0.8em;display: flex;justify-content: center;align-items: center;height: auto;overflow: hidden;position: relative;">
								<div style="padding-bottom: 0.75em;"><span></span></div>
									', ArcadeRandomGames(1, $rom), '
								<div style="padding-bottom: 0.75em;"><span></span></div>
							</div>';

		if (empty($rom)) {
			echo '
							<div class="' . $divbg . ' centertext" style="transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;margin-bottom:3px;border-radius: 3px;overflow: hidden;width: 100%;">
								<h3'. $spanbg . '>
									<span style="font-weight: bold;">
										<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" alt="" />
										', ($_SESSION['current_cat'] == 'all' ? $txt['arcade_champs'] : sprintf($txt['cat_champs'], $context['cat_name'])), '
										<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" alt="" />
									</span>
								</h3>
							</div>
							<div style="display: block;width: 100%;border: 0px;border-spacing: 2px;border-collapse: separate;padding-bottom: 0.5em;">
								<div style="padding-bottom: 0.75em;"><span></span></div>
								<div style="clear: both;display: inline-flex;width: 100%;">';

			$bp = ArcadeChamps(3, ($_SESSION['current_cat'] == 'all' ? 'wins' : 'cats'), $rom);
			$score_poss = 0;
			if(is_array($bp))
			{
				foreach ($bp as $out)
				{
					$score_poss++;
					echo '
									<div class="centertext" style="display: inline;width: 33%;border:0px;font-size:1.0em;">
										<div><img src="', $settings['default_images_url'], '/arc_icons/', $score_poss, '.gif" style="margin-bottom: 3px" alt="" /></div>
										<div>', $out['avatar'], '<br /><span style="font-weight: bold;">', $out['link'], '</span></div>
										<div>', $txt['win'], ' ', $out['champions'], '</div>
									</div>';
				}
			}
			else
				echo '
									<div class="smalltext centertext" style="display: inline;border:0px !important;font-size:1.0em;">
										', $txt['no_new_champs'], '
									</div>';

			echo '
								</div>
								<div style="padding-bottom: 0.75em;"><span></span></div>
							</div>';
		}

		echo '
							<div class="' . $divbg . ' centertext" style="transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;border-radius: 3px;overflow: hidden;margin-bottom: 3px;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['arcade_daily'], '</span></h3>
							</div>';

		$game = getGameOfDay($rom);
		if (!empty($game['url']['play']))
		{
			echo '
							<div class="smalltext" style="min-height: 8rem;max-height: 10em;padding: 0px 5px 0px 5px;position: relative;margin: 0 auto;text-align: center;">
								<div class="centertext" style="margin: 0px;border-bottom:1px solid #808080;font: 1.1em Blippo, fantasy;padding-bottom: 0.15rem;">
									<a href="', $game['url']['play'], '">', (strlen($game['name']) >= 23 ? substr($game['name'],0,22) . '...' : $game['name']), '</a>
								</div>
								<div style="padding: 0.5em 0px 0.5em 0px;height: 3rem;display: flex;flex-direction: column;text-align: center;margin: 0 auto;">
									<a href="', $game['url']['play'], '">
										<img style="width: 2rem;height: 2rem;vertical-align: middle;padding-bottom: 0.5em;" class="imgBorder" src="', $game['thumbnail'], '" alt="' . $txt['alt_play'] . '" title="' . $txt['alt_play'] . '"/>
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

		if (!empty($arcadeModSettings['arcadeDailyGameScoresC']))
		{
			echo '
							<div style="padding-bottom: 0.75em;"><span></span></div>
							<div class="smalltext" style="padding: 0px 5px 0px 5px">
								<div class="titlebg centertext" style="margin:0px 0px 0px 0px;border-bottom:1px solid #808080;font-size:1.1em;">', $txt['todays_scores'], '</div>
								<div style="margin: 0px 0px 0px 0px;padding-bottom: 0.5em;">', ArcadeDailyChallenge($game, $rom), '</div>
							</div>';

			if (!empty($context['CH_error']))
				echo '
							<div class="smalltext centertext">', $txt['arcade_daily_none'], '</div>';
		}

		//  padding for aesthetics
		echo '
							<div style="padding: 0.75rem 0rem 0.75rem 0rem;"><span></span></div>';

		echo '
							<div class="' . $divbg . ' centertext" style="overflow: hidden;transform: scale(1.0, 0.9);font-size:97%;border-radius: 3px;display: flex;flex-direction: row;flex-wrap: wrap;width: 100%;">
								<div style="overflow: hidden;width: 50%;display: flex;flex-direction: column;flex-basis: 100%;flex: 1;border-right: 0px;">
									<h3'. $spanbg . '><span style="font-weight: bold;"><label for="gamesearch" style="display: block;font-weight: bold;text-align: center;">', $txt['arcade_game_search'], '</label></span></h3>
								</div>
								<div style="overflow: hidden;width: 50%;display: flex;flex-direction: column;flex-basis: 100%;flex: 1;border-left: 0px;">
									<h3'. $spanbg . '><span style="font-weight: bold;"><label for="arcade_sortby" style="display: block;font-weight: bold;text-align: center;">', $txt['arcade_game_sort'], '</label></span></h3>
								</div>
							</div>
							<div style="padding-bottom: 2.0rem;"><span style="display: none;"></span></div>
							<div style="display: flex;flex-direction: row;flex-wrap: wrap;width: 100%;">
								<div style="display: flex;flex-direction: column;flex-basis: 100%;flex: 1;">
									<div class="centertext smalltext" style="margin-bottom:0em;font-size:1.0em;">
										<form name="search" action="', $scripturl, '?action=' . $arcadeAction . ';sa=search" method="post" onsubmit="return empty();">
											<input style="height: 1.5rem;padding: 10px;" autocomplete="off" id="gamesearch" type="text" title="' . $txt['arcade_search'] . '" placeholder="' . $txt['arcade_search'] . '" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" maxlength="100">
											<input enterkeyhint="go" style="width: auto;height: 1.5rem;vertical-align: middle;position: relative;display: inline-block;margin: 0 auto;" class="button_submit smalltext" type="submit" value="&#8629; ', $txt['arcade_search_go'] , '"  name="submit1" />
											<div style="line-height: 1.5rem;height: 1.5rem;" id="suggest_gamesearch" class="game_suggest"></div>
											<script type="text/javascript">
												var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
											</script>
										</form>
									</div>
								</div>';

		echo '
								<div style="display: flex;flex-direction: column;flex-basis: 100%;flex: 1;">
									<div class="centertext smalltext" style="padding-left:5px;padding-right: 5px;margin-left:0.5em;margin-right: 0.5em;font-size:1.0em;">
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
							</div>';

		//  padding for aesthetics
		echo '
							<div style="padding-bottom: 1.25em;"><span></span></div>';

		if (!empty($arcadeModSettings['arcadeDropCat']))
		{
			echo '
							<div class="' . $divbg . ' centertext" style="transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;margin-bottom:0px;border-radius: 3px;overflow: hidden;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['game_categories'], '</span></h3>
							</div>
							<div style="padding-bottom: 0.25em;"><span style="display: none;"></span></div>
							<div class="smalltext centertext" style="margin: 0px;font-size:1.0em;">', ArcadeCategoryDropdown($rom), '</div>';

			//  padding for aesthetics
			echo '
							<div style="padding-bottom: 0.75em;"><span></span></div>';
		}

		// shoutbox (if enabled)
		if (!empty($arcadeModSettings['arcade_shoutboxC']))
		{
			echo '
							<div class="' . $divbg . ' centertext" style="margin-bottom:3px;border-radius: 3px;overflow-x: hidden;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', (!empty($arcadeModSettings['arcade_shoutboxC_name']) ? $arcadeModSettings['arcade_shoutboxC_name'] : $txt['ArcadeShoutbox_name']), '</span></h3>
							</div>
							<div class="centertext" style="width: 100%;display: inline-block;margin: 0 auto;font-size:1.0em;">
								<div style="display: inline-block;padding: 0px;width: 100%;">
									<div id="arcade_shoutsC" class="smalltext" style="display: inline-block;width: 99%;height: ' . (!empty($arcadeModSettings['arcade_shout_heightC']) ? (int)$arcadeModSettings['arcade_shout_heightC'] : '40') . 'em;overflow: auto;">
										', ArcadeShoutboxC($rom), '
									</div>
								</div>
							</div>
							<div style="display: block;clear: both;width: 100%;text-align: center;bottom: 0;position: relative;">
								<div style="display: inline;padding: 1px;position: relative;bottom: 0px;">
									<input size="105" maxlength="100" onkeypress="submitShoutOnEnterC(event);" class="largetext" name="the_shout" style="width: 80%;margin-top: 1ex; height: 25px;" id="arcadeShoutInputC" />
									<div style="display: block;"><span style="display: none;">&nbsp;</span></div>
									<div style="position: relative;width: 100%;margin: 0 auto;bottom: 0px;"><input style="margin-top: 4px;" class="mediumtext" type="submit" onclick="shoutArcadeNewShoutC();" id="arcadeShoutSubmitC" name="shout" value="', $txt['arcade_shout'], '" /></div>
								</div>
							</div>';

			//  padding for aesthetics
			echo '
							<div style="padding-bottom: 0.1em;"><span></span></div>';
		}

		echo '
						</div>
						<div class="windowbg smalltext arcade_border_split_side" style="clear: both;margin-top: 0px;width: 100%;display: inline;' . $commonRight . 'vertical-align: top;font-size:0.85em;">
							<div class="' . $divbg . ' centertext" style="margin-bottom:3px;border-radius: 3px;overflow: hidden;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;">
								<h3'. $spanbg . '><span style="font-weight: bold;">', $txt['most_played'], '</span></h3>
							</div>
							', ArcadePopular($arcadeModSettings['skin_most_' . $romTxt . 'popular'], $rom) . (empty($rom) ? '
							<div class="' . $divbg . ' centertext" style="margin-bottom:3px;border-radius: 3px;overflow: hidden;transform: scale(1.0, 0.9);font-size:97%;display: flex;justify-content: center;flex-direction: column;align-items: center;">
								<h3'. $spanbg . '><span style="font-weight: bold;">' . $txt['latest_scores'] . '</span></h3>
							</div>
							' . ArcadeLatest($arcadeModSettings['skin_latest_scores'], $rom) : '');
		echo '
						</div>
					</div>
				</div>';

		if (empty($arcadeModSettings['arcadeDropCat']) && !empty($context['arcade']['cats']))
		{
			echo '
				<div style="padding-top: 0.5em;"><span style="display: none;">&nbsp;</span></div>
				<div class="title_bar arcade_cats_border">
					<h4 class="titlebg centertext" style="vertical-align: middle;">
						<span style="clear: right;"><a title="', $txt['arcade_defcat'], '" href="', $scripturl, '?action=' . $arcadeAction . ';category=0">', $txt['arcade_game_cats'], '</a></span>
					</h4>
				</div>', $categories;
		}

		echo '
			</div>
		</div>
	</div>
	<div style="width:100%;display: inline-flex;justify-content: end;" class="smalltext">';
		if ($context['arcade']['stats']['games'] != 0)
			echo '
		<div class="smalltext" style="clear: right;padding:8px 7px 0px 0px;display: inline;width: auto;margin-right: auto;margin-left: 1rem;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>';

		echo '
		<div style="display: inline;margin-right: 1rem;">', template_button_strip($context['arcade_tabs'], 'left', array(), $rom), '</div>';
		echo '
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div><span style="display: none;">&nbsp;</span></div>
	<div style="height: 10px;clear: both;">
		<span style="display: none;">&nbsp;</span>
	</div>
	<script type="text/javascript">
		function changeArcadeShoutContentC()
		{
			var arcadeInterval = "' . abs(intval($arcadeModSettings['arcade_shout_intervalC'])) * 1000 . '";
			setInterval(function() {
				var xhttp = new XMLHttpRequest();
				var str = "' . (!empty($_SESSION['arcade_shout_session']) ? $_SESSION['arcade_shout_session'] : substr(uniqid('invalid_', true), -5)) . '";
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var i = 0, arcShoutRefreshC = document.getElementById("arcade_shoutsC");
						if (this.responseText.indexOf(\'<div class="arcadeShout"\') > -1 && arcShoutRefreshC) {
							var arcadeShoutHtmlParser = new DOMParser();
							var arcadeShoutHtml = arcadeShoutHtmlParser.parseFromString(this.responseText, "text/html").body;
							var arcadeAllShouts = arcadeShoutHtml.getElementsByClassName("arcadeShout");
							arcShoutRefreshC.innerHTML = "";
							var arcadeReverse = [], arcadeNewBox = arcadeAllShouts.length-1;
							for(i=arcadeAllShouts.length;i>0;i--) {
								arcadeReverse[arcadeNewBox] = arcadeAllShouts[i-1];
								arcadeNewBox--;
							}
							for(i=0;i<arcadeReverse.length>0;i++)
								arcShoutRefreshC.appendChild(arcadeReverse[i]);
							arcShoutRefreshC.scrollTop = 0;
						}
					}
				};
				xhttp.open("GET", "' . $scripturl . '?action=' . $arcadeAction . ';sa=shouts;arcade_shout_session="+str + ";xml", true);
				xhttp.send();
			}, arcadeInterval);
		}' . (!empty($arcadeModSettings['arcade_shout_intervalC']) ? '
		$(document).ready(function(){
			changeArcadeShoutContentC();
		});' : '') . '
		function changeArcadeShoutContentNowC()
		{
			var xhttp = new XMLHttpRequest();
			var str = "' . $_SESSION['arcade_shout_session'] . '";
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var i = 0, arcShoutRefreshC = document.getElementById("arcade_shoutsC");
					if (this.responseText.indexOf(\'<div class="arcadeShout"\') > -1 && arcShoutRefreshC) {
						var arcadeShoutHtmlParser = new DOMParser();
						var arcadeShoutHtml = arcadeShoutHtmlParser.parseFromString(this.responseText, "text/html").body;
						var arcadeAllShouts = arcadeShoutHtml.getElementsByClassName("arcadeShout");
						arcShoutRefreshC.innerHTML = "";
						var arcadeReverse = [], arcadeNewBox = arcadeAllShouts.length-1;
						for(i=arcadeAllShouts.length;i>0;i--) {
							arcadeReverse[arcadeNewBox] = arcadeAllShouts[i-1];
							arcadeNewBox--;
						}
						for(i=0;i<arcadeReverse.length>0;i++)
							arcShoutRefreshC.appendChild(arcadeReverse[i]);
						arcShoutRefreshC.scrollTop = 0;
					}
				}
			};
			xhttp.open("GET", "' . $scripturl . '?action=' . $arcadeAction . ';sa=shouts;arcade_shout_session="+str + ";xml", true);
			xhttp.send();
		}
		function shoutArcadeNewShoutC() {
			if (document.getElementById("arcadeShoutInputC")) {
				if (document.getElementById("arcadeShoutInputC").value)
					shout = encodeURIComponent(document.getElementById("arcadeShoutInputC").value).replace(/\'/g, "%27");
				else
					return false;
			}
			else
				return false;
			document.getElementById("arcadeShoutInputC").value = "";
			setTimeout(function(){
				var xhttpx = new XMLHttpRequest();
				xhttpx.open("POST", "' . $scripturl . '?action=arcade;sa=newShout", true);
				xhttpx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttpx.onreadystatechange = function() {
					if (xhttpx.readyState == 4 && xhttpx.status == 200) {
						var response = xhttpx.responseText;
						changeArcadeShoutContentNowC();
					}
				};
				xhttpx.send("arcade_shout_user_id=" + ' . $user_info['id'] . ' + "&arcade_shout_text=" + shout);
			}, 500);
		}
		function submitShoutOnEnterC(event) {
			if (event.keyCode == 13)
				document.getElementById("arcadeShoutSubmitC").click();
		}
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