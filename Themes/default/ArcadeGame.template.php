<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_game_above()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $boardurl, $options, $user_info;

	if (!empty($context['arcade_rom_play_flag']))
		return;
	$context['arcade_rom_play_flag'] = 'above';
	$anchor = empty($context['game']['rom_flag']) && !empty($context['game']['html']) && stripos($context['game']['html'], 'html5') === FALSE ? 'game_panel' : 'playgame';
	list($skin, $sa) = array(
		!empty($user_info['arcade_settings']['skin']) ? $user_info['arcade_settings']['skin'] : 0,
		!empty($_REQUEST['sa']) ? $_REQUEST['sa'] : '',
	);

	if ($skin == 0 || $sa !== 'highscore')
		echo '
	<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>';
	else
		echo '
	<div class="cat_bar" style="clear: both;position: relative;">
		<h3 class="catbg centertext">
			<span class="centertext" style="clear: left;width: 100%;vertical-align: middle;">', $txt['arcade_title'], '</span>
		</h3>
	</div>';

	echo '
	<span class="clear upperframe"><span>&nbsp;</span></span>
	<div id="mainframe" class="roundframe maingamecontainer">
		<div class="innerframe">
			<div class="cat_bar" style="width: 100%;">
				<h3 class="catbg" style="vertical-align: middle;display: grid;font-size: medium;">
					<a style="grid-column-start: 1;grid-row-start: 1;justify-self:center;" href="', $scripturl, '?index.php;action=arcade;sa=play;game=', $context['game']['id'], '" title="', $txt['arcade_play'],' ', $context['game']['name'], '">
						<span class="clear: right;" style="font-size: medium;font-weight: bold;">', $context['game']['name'], '</span>
					</a>
					<span id="game_toggle" class="' . (empty($options['game_panel_collapse']) ? ' toggle_up' : ' toggle_down') . '" title="' . $txt['upshrink_description'] . '" style="grid-column-start: 1;grid-row-start: 1;justify-self: right;cursor: pointer;"></span>
				</h3>
			</div>
			<div id="game_panel" class="windowbg2 smalltext" style="display: flex;align-items: center;margin: 0;', empty($options['game_panel_collapse']) ? '' : ' display: none;', '">
				<span class="topslice"><span>&nbsp;</span></span>
				', !empty($context['game']['thumbnail']) ? '<img title="' . $txt['arcade_play'] . ' ' . $context['game']['name'] . '" id="gamethumb" style="height: 60px;width: 60px;padding-top: 0.25rem;" class="floatleft thumb" src="' . $context['game']['thumbnail'] . '" alt="" />' : '', '
				<div class="floatleft scores" style="padding-left: 5px;vertical-align: bottom;">';

	if ($context['game']['is_champion'])
		echo '
					<strong class="smalltext">', $txt['arcade_champion'], ':</strong> ', $context['game']['champion']['link'], ' - ', $context['game']['champion']['score'], '<br />';
	if ($context['game']['is_personal_best'])
		echo '
					<strong class="smalltext">', $txt['arcade_personal_best'], ':</strong> ', $context['game']['personal_best'], '<br />';

	echo '
					<div style="position: relative;padding-top: 0.2em;">';

	if (!empty($context['game']['description']) || !empty($context['game']['help']))
	{
		echo '
						&nbsp;&nbsp;<img id="imgObjInfo" title="', $txt['arcade_click_info_title'], '" alt="', $txt['arcade_click_info'], '" src="', $settings['default_images_url'], '/arc_icons/game_info.png" style="width: 1.3em;height: 1.3em;" />';
	}

	if ($context['arcade']['can_favorite'])
		echo '
						&nbsp;&nbsp;<a href="', $context['game']['url']['favorite'], '" onclick="arcade_favorite(', $context['game']['id'], '); return false;">', !$context['game']['is_favorite'] ?  '<img id="favgame' . $context['game']['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star4.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '<img id="favgame' . $context['game']['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/star3.gif" alt="' . $txt['arcade_remove_favorite'] . '" />', '</a><div><span style="display: none;">&nbsp;</span></div>';

	if ($context['arcade']['can_rate'])
		echo '
						&nbsp;&nbsp;', $context['arcade_ratecode'], '<span style="display: block;"><span style="display: none;">&nbsp;</span></span>';

	echo '
					</div>';


	if (!empty($context['game']['description']) || !empty($context['game']['help']))
	{
		echo '
					<div id="gameInfoDiv" style="position: relative;padding-top: 0.2em;">
						&nbsp;&nbsp;<div id="gameInfoDivData" style="display: none;padding: 1em 1.02em;border-radius: 8px;position: absolute;width: 20em;font-size: 0.9em;z-index: 99;top: 0em;">
						', (!empty($context['game']['description']) ? $txt['arcade_post_description'] . '<div><span style="display: none;">&nbsp;</span></div>
						' . $context['game']['description'] . '<div><span style="display: none;">&nbsp;</span></div><div><span style="display: none;">&nbsp;</span></div>' : ''), '
						', (!empty($context['game']['help']) ? $txt['arcade_post_help'] . '<div><span style="display: none;">&nbsp;</span></div>
						' . $context['game']['help'] : ''),  '
						</div>
					</div>';
	}
	else
		echo '
					<div style="padding-top: 1em;"><span style="display: none;">&nbsp;</span></div>';

	echo '
				</div>
			</div>
			<script type="text/javascript"><!-- // --><![CDATA[
				var oGameHeaderToggle = new smc_Toggle({
					bToggleEnabled: true,
					bCurrentlyCollapsed: ', empty($options['game_panel_collapse']) ? 'false' : 'true', ',
					aSwappableContainers: [\'game_panel\'],
					', (version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '
					aSwapImages: [
						{
							sId: \'game_toggle\',
							srcExpanded: smf_images_url + \'/collapse.gif\',
							altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
							srcCollapsed: smf_images_url + \'/expand.gif\',
							altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
						}
					],' : '
					aSwapImages: [
						{
							sId: \'game_toggle\',
							altExpanded: ' . JavaScriptEscape($txt['upshrink_description']) . ',
							altCollapsed: ' . JavaScriptEscape($txt['upshrink_description']) . '
						}
					],'), '
					oThemeOptions: {
						bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
						sOptionName: \'game_panel_collapse\',
						sSessionVar: ', JavaScriptEscape($context['session_var']), ',
						sSessionId: ', JavaScriptEscape($context['session_id']), '
					},
					oCookieOptions: {
						bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
						sCookieName: \'arcadegameupshrink\'
					}
				});', (version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '' : '
				var checkArcadeContainer = readArcadeCookie("checkArcadeContainer") != "" ? readArcadeCookie("checkArcadeContainer") : document.getElementById("game_panel").style.display;
				if (checkArcadeContainer === "none")
				{
					$("#game_toggle").toggleClass("toggle_down", true);
					writeArcadeCookie("checkArcadeContainer", "", 1);
				}
				else
				{
					$("#game_toggle").toggleClass("toggle_up", true);
					writeArcadeCookie("checkArcadeContainer", "none", 1);
				}'), '
				function myformxyz(myform, myscore)
				{
					var arcadeDisableComments = ' . (empty($arcadeModSettings['arcadeDisableComments']) ? '0' : '1') . ';
					if (myscore > 0)
					{
						var newercomment = document.getElementById("c"+myscore).value;
						myform = "commentform3";
						if (newercomment == "" || arcadeDisableComments == 1)
							newercomment = "', $txt['arcade_no_comment'], '";
						if (document.getElementById("comment" + myscore)) {
							document.getElementById("comment" + myscore).innerHTML = newercomment;
							document.forms[myform]["c" + myscore].value = newercomment;
							var datearcz = new Date();
							datearcz.setTime(datearcz.getTime()+(10*1000));
							var expiresarcz = datearcz.toGMTString();
							var valuearcz = "' . (time() + 10) . '";
							document.cookie = "arcadegameid" + "' . $context['game']['id'] . '" + "=" + (valuearcz || "")  + "; expires=" + expiresarcz + "; path=/";
						}
					}
					else if (myscore == -1)
					{
						var newguest = document.forms[myform]["name"].value;
						if (newguest == null || newguest == "")
						{
							alert("', $txt['arcade_comment_guestname'], '");
						}
						else
						{
							var checkguest = guestusername(newguest);
							if (checkguest)
							{
								document.forms[myform]["name"].value = newguest;
								document.getElementById(myform).submit();
								return true;
							}
						}

						return false;
					}
					else
					{
						if (document.getElementById("new_comment") && arcadeDisableComments == 0)
							var newercomment = document.getElementById("new_comment").value;
						myform = "commentform1";
						if (document.getElementById("mynewscoreid") && document.forms[myform]["mynewscoreid"] !== "undefined" && document.forms[myform]["mynewscoreid"].value !== "undefined")
							myscore = document.forms[myform]["mynewscoreid"].value;
						if (newercomment === "undefined" || newercomment == "" || arcadeDisableComments == 1)
							newercomment = "', $txt['arcade_no_comment'], '";
						if (myscore !== "undefined" && document.getElementById("comment" + myscore)) {
							document.getElementById("comment" + myscore).innerHTML = newercomment;
							document.forms[myform]["c" + myscore].value = newercomment;
							var datearcz = new Date();
							datearcz.setTime(datearcz.getTime()+(10*1000));
							var expiresarcz = datearcz.toGMTString();
							document.cookie = "arcadegameid" + "' . $context['game']['id'] . '" + "=" + (valuearcz || "")  + "; expires=" + expiresarcz + "; path=/";
						}
					}
					if (document.getElementById(myform))
						document.getElementById(myform).submit();
				}
				function guestusername(newguestname)
				{
					var reg = new RegExp("[^a-zA-Z0-9]");
					if (reg.test(newguestname))
						alert("', $txt['arcade_comment_noguestname'], '");
					else
						return true;

					return false;
				}
				function mycheckxyz()
				{
					if (confirm(\'', $txt['arcade_are_you_sure'], '\'))
						return true;
					else
						return false;
				}
				function enterkey(event)
				{
					var code = (event.keyCode ? event.keyCode : event.which);
					if(code == 13) {
						document.getElementById("commentform3").submit();
						return true;
					}
					return false;
				}' . (!empty($context['game']['description']) || !empty($context['game']['help']) ? '
				function expandGameElementHeight(elementSelector) {
					var $element = $(elementSelector);
					$element.css("height", "auto");
					if ($element[0].scrollHeight) {
						var contentHeight = $element[0].scrollHeight + 10;
						$element.animate({
							height: contentHeight
						}, 400, function() {
							$(this).css({"overflow": "hidden"});
						});
					}
				}
				function gameinfoClickX()
				{
					var gameInfoBox = document.getElementById("gameInfoDivData");
					var gameInfoParent = document.getElementById("gameInfoDiv");
					var gameFormId = document.getElementById("commentform3");
					if (gameInfoBox && gameInfoBox.style.display === "block") {
						gameInfoBox.style.display = "none";
						gameInfoBox.style.position = "relative";
						gameInfoParent.style.position = "relative";
						gameInfoParent.style.overflowX = "hidden";
						gameInfoParent.style.marginLeft = "0em";
						if (gameFormId)
							gameFormId.style.paddingTop = "0em";
						gameInfoBox.style.overflowX = "hidden";
					} else if (gameInfoBox) {
						gameInfoBox.style.zIndex = 99;
						gameInfoBox.style.display = "block";
						gameInfoBox.style.position = "absolute";
						gameInfoParent.style.position = "relative";
						gameInfoBox.style.zIndex = "99";
						gameInfoParent.style.overflowX = "visible";
						gameInfoBox.style.overflowX = "visible";
						gameInfoBox.style.border = "2px solid";
						gameInfoBox.style.width = "15em";
						gameInfoParent.style.margin = "0em auto";
						if (gameFormId)
							gameFormId.style.paddingTop = "1.48em 0.2em 0.2em 0.2em";
						gameInfoBox.style.right = "0em";
						gameInfoBox.className = "windowbg";
						gameInfoBox.style.left = "5em";
					}
					expandGameElementHeight(".maingamecontainer");
				}
				function gameInfoOnload()
				{
					setTimeout(function(){
						var gameElement = document.getElementById("' . $anchor . '");
						if (gameElement) {
							gameElement.scrollIntoView();
						}
					}, 100);
					var gameInfoDivDataClick = document.getElementById("gameInfoDivData");
					if (gameInfoDivDataClick) {
						gameInfoDivDataClick.onclick = function() {
							document.getElementById("gameInfoDivData").style.display = "none";
						};
					}
					var gameInfoImg = document.getElementById("imgObjInfo");
					if (gameInfoImg) {
						gameInfoImg.onclick = function() {gameinfoClickX();};
					}
				}
				$(document).ready(function() {
					gameInfoOnload();
					$("img#gamethumb").click(function() {
						window.location.href = "' . $scripturl . '?index.php;action=arcade;sa=play;game=' . $context['game']['id'] . '";
					});
				});' : '
				$(document).ready(function() {
					$("img#gamethumb").click(function() {
						window.location.href = "' . $scripturl . '?index.php;action=arcade;sa=play;game=' . $context['game']['id'] . '";
					});
				});') . '
			// ]]></script>';
}

// Play screen
function template_arcade_game_play()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info;

	if (!empty($context['arcade_rom_play_flag']) && $context['arcade_rom_play_flag'] != 'above')
		return;
	$context['arcade_rom_play_flag'] = 'play';
	$html = $context['game']['html']($context['game'], true);
	echo '
			<div class="windowbg2" id="playgame" style="clear: both;position: relative;margin: 0 auto;overflow: hidden;">
				<span class="topslice"><span>&nbsp;</span></span>
				<div class="centertext" id="gamearea" style="display: flex;align-items: center;justify-content: center;">
					', $html, '
					', !$context['arcade']['can_submit'] ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
				</div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div>
		</div>
		<div ', ($context['game']['type'] != 'fullscreen' ? 'style="display: none;" ' : ''), 'class="escgamediv">
			<img class="escgame" id="escbutton" src="' . $settings['default_theme_url'] . '/images/arc_icons/arcade_esc.png' . '" alt="[ESC]" onclick="escGameSmf()" />
		</div>
		<script type="text/javascript">
			if (window.addEventListener) {
				window.addEventListener("load", function (){
					smfArcadeGameDims3();
					return false;
				});
			}
			else {
				window.attachEvent("onload", function (){
					smfArcadeGameDims3();
					return false;
				});
			}
			function smfArcadeGameDims3() {' . (empty($user_info['is_guest']) && !empty($user_info['name']) ? '
				localStorage.setItem("arcade_game_user_name", "' . $user_info['name'] . '");' : '
				localStorage.setItem("arcade_game_user_name", "' . $txt['guest_title'] . '");') . '
				var divelement = document.getElementById("game");
				if (divelement) {
					divelement.width = "100vw";
					divelement.height = "100vh";
					scrollTo(document.body, divelement.offsetTop, 100);
				}
				var gameObject = document.getElementById("gamearea");
				gameObject.onmouseenter = function (){
					document.body.style.overflowY="hidden";
				};
				gameObject.onmouseleave = function (){
					document.body.style.overflowY="auto";
				};

			}
			function escGameSmf() {
				window.location = "' . $scripturl . '?action=arcade;sa=highscore;game=' . $context['game']['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
			}
		</script>';
}

function template_arcade_html5_game_play()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info, $boardurl, $boarddir;

	if (!empty($context['arcade_rom_play_flag']) && $context['arcade_rom_play_flag'] != 'above')
		return;
	$context['arcade_rom_play_flag'] = 'play';

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$reload = isset($_REQUEST['reload']) ? (int)$_REQUEST['reload'] : 0;
	$jsInsert = file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcadeAdd.js') ? file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/arcadeAdd.js') : '';
	echo '
			<div ', ($context['game']['type'] != 'fullscreen' ? 'style="display: none;" ' : ''), 'class="escgamediv">
				<img class="escgame" id="escbutton" src="' . $settings['default_theme_url'] . '/images/arc_icons/arcade_esc.png' . '" alt="[ESC]" onclick="escGameSmf()" />
			</div>
			<script type="text/javascript">
				if (window.addEventListener)
					window.addEventListener("load", function (){
						smfArcadeGameDims3();
						smfArcadeGamePhpbb();
						return false;
					});
				else
					window.attachEvent("onload", function (){
						smfArcadeGameDims3();
						smfArcadeGamePhpbb();
						return false;
					});

				function smfArcadeGameDims3() {' . (empty($user_info['is_guest']) && !empty($user_info['name']) ? '
				localStorage.setItem("arcade_game_user_name", "' . $user_info['name'] . '");' : '
				localStorage.setItem("arcade_game_user_name", "' . $txt['guest_title'] . '");') . '
					sessionStorage.removeItem("scoreLoop_' . $_SESSION['arcade_html5_token'][1] . '");
					var divelement = document.getElementById("game");
					var divelement2 = document.getElementById("gameTabIndex");
					divelement.width = "100vw";
					divelement.height = "100vh";
					scrollTo(document.body, (divelement2.offsetTop)-' . (int)$context['game']['height']/4 . ', 100);
					var gameObject = document.getElementById("gamecontainer");
					gameObject.onmouseenter = function (){
						document.body.style.overflowY="hidden";
					};
					gameObject.onmouseleave = function (){
						document.body.style.overflowY="auto";
					};
				}
				function smfArcadeGamePhpbb() {' . ($context['game']['submit_system'] != 'html53' ? '
					return;' : '
					return;
				') . '
				}
				function escGameSmf() {
					window.location = "' . $scripturl . '?action=arcade;sa=highscore;game=' . $context['game']['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
				}
			</script>
			<form id="gameForm" action="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . ';sa=' . $context['game']['submit_system'] . 'Game;" method="post" target="_self">
				<input type="hidden" id="game" name="game" value="' . $context['game']['id'] . '" />
				<input type="hidden" id="smfgametime" name="smfgametime" value="' . strval(time()) . '" />
				<input type="hidden" id="html5" name="html5" value="1" />' . ($context['game']['submit_system'] == 'html52' || $context['game']['submit_system'] == 'html53' ? '
				<input type="hidden" id="html52" name="html52" value="1" />' : '') . ($user_info['is_guest'] && empty($_SESSION['playerName']) ? '
				<input type="hidden" id="guestname" name="guestname" value="1" />' : '
				<input type="hidden" id="guestname" name="guestname" value="0" />') . '
				<input type="hidden" id="smfGameSaveUrl" name="smfGameSaveUrl" value="', $settings['default_theme_url'], '/arcade_scripts/arcade-html5-save.js?' . $suffixVersion . '" />
				<input type="hidden" id="html5smfGameUrl" name="html5smfGameUrl" value="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . (!empty($reload) ? ';reload=' . $reload : '') . ';#playgame" />
				<input type="hidden" id="gameSmfFullscreen" name="gameSmfFullscreen" value="0" />
				<input type="hidden" id="popup" name="popup" value="0" />
				<input type="hidden" id="gameexit" name="gameexit" value="0" />
				<input type="hidden" id="noSmfScore" name="noSmfScore" value="' . $txt['arcade_noSmfScore'] . '" />
				<input type="hidden" id="gameSmfToken" name="gameSmfToken" value="' . $_SESSION['arcade_html5_token'][1] . '" />
				<input type="hidden" id="game_name" name="game_name" value="' . $context['game']['internal_name'] . '" />
			</form>
			<div class="windowbg2" id="playgame" style="clear: both;position: relative;margin: 0 auto;overflow: hidden;">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="display: flex;align-items: center;justify-content: center;">
					<div id="gamearea" class="centertext">
						<div tabindex="-1" id="gamecontainer" style="display: inline;overflow: hidden;border: 0px;height: ' . ((int)$context['game']['height'] + 8) . 'px;width: ' . ((int)$context['game']['width'] + 8) . 'px;">
							<object onerror="reloadArcadeGameContainer(1)" onload="arcadeGameOnloadEvent()" id="gameObj" type="text/html" style="overflow: hidden;height: ' . ((int)$context['game']['height'] + 50) . 'px;width: ' . ((int)$context['game']['width'] + 50) . 'px;" data="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '">
								<param name="gameSmfName" value="' . $context['game']['internal_name'] . '">
							</object>
						</div>' . (!$context['arcade']['can_submit'] ? '
						<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '') . '
					</div>
				</div>
				<div id="gameTabIndex"></div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div>
		</div>
		<script type="text/javascript">
			localStorage.setItem("reloadArcadeGame", 0);
			function arcadeGameOnloadEvent() {
				loadSmfExtraGameData();
				if (' . (int)$context['game']['js_insertion'] . ' == 1 && typeof submitSmfArcadeScoreCode !== "function")
					reloadArcadeGameContainer();
				return false;
			}
			function reloadArcadeGameContainer(zCount = 0) {
				var yCount, xCount = localStorage.getItem("reloadArcadeGame");
				if (xCount < 1) {
					yCount = xCount + 1;
					localStorage.setItem("reloadArcadeGame", yCount);
					document.getElementById("gameObj").contentWindow.location.reload();
				}
			}
			function loadSmfExtraGameData() {
				var arcadeObject = document.getElementById("gameObj");
				var arcadeData = arcadeObject.contentDocument || arcadeObject.contentWindow.document;
				if (arcadeData && arcadeData.getElementsByTagName("BODY")) {
					arcadeData.getElementsByTagName("BODY")[0].style.minWidth = "100%";
					arcadeData.getElementsByTagName("BODY")[0].style.overflow = "hidden";
					' . (empty($context['game']['js_insertion']) ? 'if (!arcadeData.getElementById("block_game"))' : 'if (' . (int)$context['game']['js_insertion'] . ' == 1)') . ' {
						' . $jsInsert . '
					}
				}
			}
		</script>';
		/*
						<iframe id="gameObj" style="overflow: hidden;height: ' . ((int)$context['game']['height']) . 'px;width: ' . ((int)$context['game']['width']) . 'px;" src="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '"></iframe>
		*/
}

// ROM Play screen
function template_arcade_game_rom_play()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info, $boardurl, $boarddir;

	if (!empty($context['arcade_rom_play_flag']) && $context['arcade_rom_play_flag'] != 'above')
		return;
	$context['arcade_rom_play_flag'] = 'play';
		$arcade_version = $arcadeModSettings['arcadeVersion'];
		$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
		$reload = isset($_REQUEST['reload']) ? (int)$_REQUEST['reload'] : 0;
		$jsInsert = file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcadeRomAdd.js') ? file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/arcadeRomAdd.js') : '';
		$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
		$action = !empty($rom) ? 'retro_arch' : 'arcade';
		echo '
			<div class="windowbg2" id="playgame" style="clear: both;position: relative;margin: 0 auto;overflow: hidden;">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="display: flex;align-items: center;justify-content: center;">
					<div class="centertext" id="gamearea">
						<iframe style="display: inline !important;height: ' . ((int)$context['arcade_rom_game_data']['height']) . 'px;width: ' . ((int)$context['arcade_rom_game_data']['width']) . 'px;overflow: hidden;" class="centertext" id="iframe_smfarcaderoms_small" srcdoc=\'' . $context['arcade_retro_arch_rom'] . '\'></iframe>
						', empty($context['arcade']['can_submit']) ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
					</div>
				</div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div>
		</div>
		<div ', ($context['arcade_rom_game_data']['type'] != 'fullscreen' ? 'style="display: none;" ' : ''), 'class="escgamediv">
			<img class="escgame" id="escbutton" src="' . $settings['default_theme_url'] . '/images/arc_icons/arcade_esc.png' . '" alt="[ESC]" onclick="escGameSmf()" />
		</div>
		<script type="text/javascript">' . (!empty($context['arcade_rom_game_data']['javascript']) ? '
			' . $context['arcade_rom_game_data']['javascript'] : '') . '
			if (window.addEventListener) {
				window.addEventListener("load", function (){
					smfArcadeGameDims3();
					return false;
				});
			}
			else {
				window.attachEvent("onload", function (){
					smfArcadeGameDims3();
					return false;
				});
			}
			function smfArcadeGameDims3() {' . (empty($user_info['is_guest']) && !empty($user_info['name']) ? '
				localStorage.setItem("arcade_game_user_name", "' . $user_info['name'] . '");' : '
				localStorage.setItem("arcade_game_user_name", "' . $txt['guest_title'] . '");') . '
				var divelement = document.getElementById("game");
				if (divelement) {
					divelement.width = "100vw";
					divelement.height = "100vh";
					scrollTo(document.body, divelement.offsetTop, 100);
				}
				var gameObject = document.getElementById("gamearea");
				gameObject.onmouseenter = function (){
					document.body.style.overflowY="hidden";
				};
				gameObject.onmouseleave = function (){
					document.body.style.overflowY="auto";
				};

			}
			function escGameSmf() {
				window.location = "' . $scripturl . '?action=' . $action . ';sa=highscore;game=' . $context['arcade_rom_game_data']['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
			}
		</script>';
		/*
						<iframe id="gameObj" style="overflow: hidden;height: ' . ((int)$context['game']['height']) . 'px;width: ' . ((int)$context['game']['width']) . 'px;" src="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '"></iframe>
		*/
}

// Highscore
function template_arcade_game_highscore()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings;

	if (!empty($context['arcade_rom_play_flag']) && $context['arcade_rom_play_flag'] != 'above')
		return;
	$context['arcade_rom_play_flag'] = 'hiscore';

	if (isset($context['arcade']['submit']))
	{
		if ($context['arcade']['submit'] == 'newscore') // Was score submitted
		{
			$score = &$context['arcade']['new_score'];

			echo '
			<div class="cat_bar" id="submitscore">
				<h3 class="catbg">
					', $txt['arcade_submit_score'], '
				</h3>
			</div>
			<div class="windowbg2 smalltext">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="padding: 0 0.5em">';

			// No permission to save
			if (!$score['saved'])
				echo '
					<div>', $txt[$score['error']], '<br /><strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '</div>';

			else
			{
				echo '
					<div>', $txt['arcade_score_saved'], '<br /><strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '
					<div><span style="display: none;">&nbsp;</span></div>';

				if ($score['is_new_champion'])
					echo '
						', $txt['arcade_you_are_now_champion'], '<div><span style="display: none;">&nbsp;</span></div>';
				elseif ($score['is_personal_best'])
					echo '
						', $txt['arcade_this_is_your_best'], '<div><span style="display: none;">&nbsp;</span></div>';

				if ($score['can_comment'] && empty($arcadeModSettings['arcadeDisableComments']))
					echo '
					</div>
					<div>
						<form name="commentform1" id="commentform1" action="', $scripturl, '?action=arcade;sa=' . $context['arcade_scorelist'] . ';game=', $context['game']['id'], ';score=',  $score['id'], ';reload=', mt_rand(1, 9999), ';#commentform3" method="post">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" name="mynewscoreid" value="', $score['id'], '" />
							<input type="text" id="new_comment" name="new_comment" style="width: 95%;" maxlength="50" />
							<input onclick="myformxyz(\'commentform1\', 0)" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />
						</form>
					</div>';
			}

			echo '
				</div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div><br />';
		}
		elseif ($context['arcade']['submit'] == 'askname')
		{
			echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['arcade_submit_score'], '
				</h3>
			</div>
			<div class="windowbg2 smalltext">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="padding: 0 0.5em">
					<form name="commentform2" id="commentform2" action="', $scripturl, '?action=arcade;sa=save;reload=', mt_rand(1, 9999), ';#commentform3" method="post" onsubmit="myformxyz(\'commentform2\', 0)">
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="text" name="name" style="width: 95%;" maxlength="20" />
						<input class="button_submit" onclick="myformxyz(\'commentform2\'), -1" type="submit" value="', $txt['arcade_save'], '" />
					</form>
				</div>
			</div>
			<div><span style="display: none;">&nbsp;</span></div>';
		}
	}
	echo '
		</div>';

	echo '
		<form id="commentform3" name="commentform3" action="', $scripturl, '?action=arcade;sa=' . $context['arcade_scorelist'] . ';reload=', mt_rand(1, 9999), ';#commentform3" method="post" onsubmit="myformxyz(\'commentform3\', 0)">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="game" value="', $context['game']['id'], '" />
			<div style="padding-top: 8em;"><span style="display: none;">&nbsp;</span></div>
			<div class="title_bar">
				<h3 class="titlebg centertext" style="vertical-align: middle;">
					<span class="smalltext">', $txt['arcade_highscores'], '</span>
				</h3>
			</div>
			<div style="padding-top: 1em;"><span style="display: none;">&nbsp;</span></div>
			<div class="score_table smalltext">
				<div style="display: table;border-collapse: collapse;width: 100%;position: relative;" class="table_grid" id="arccomments">
					<div style="display: table-row;" class="windowbg2">';

	// Is there games?
	if (!empty($context['arcade']['scores']))
	{
			echo '
						<div scope="col" class="first_th" style="display: table-cell;width: 5px;border-bottom: 1px double;height: 1.5em;max-height: 1.5em;">', $txt['arcade_position'], '</div>
						<div scope="col" style="display: table-cell;border-bottom: 1px double;padding-left: 1.0em;height: 1.5em;max-height: 1.5em;"><a href="' . $context['arcade_scorelist_toggle'] . '">', $txt['arcade_member'], '</a></div>
						<div scope="col" style="display: table-cell;border-bottom: 1px double;padding-left: 1.0em;height: 1.5em;max-height: 1.5em;"> ', $txt['arcade_comment'], '</div>
						', (!$context['arcade']['can_admin_arcade'] ? $context['arcade_empty_title_cell'] : str_repeat($context['arcade_empty_title_cell'], 2)) . '
						' . (!$context['arcade']['can_admin_arcade'] ? '<div scope="col" class="last_th" style="display: table-cell;border-bottom: 1px double;text-align: center;height: 1.5em;max-height: 1.5em;"></div>' : '') . '
						<div scope="col" class="', !$context['arcade']['can_admin_arcade'] ? ' last_th' : '', '" style="display: table-cell;border-bottom: 1px double;text-align: center;padding-left: 1.0em;height: 1.5em;max-height: 1.5em;">', $txt['arcade_score'], '</div>';

		if ($context['arcade']['can_admin_arcade'])
			echo '
						<div scope="col" class="last_th centertext" style="display: table-cell;width: 15px;height: 1.5em;max-height: 1.5em;"><input type="checkbox" onclick="invertAll(this, this.form, \'scores[]\');" class="check" /></div>';
	}
	else
	{
		echo '
						<div scope="col" class="first_th" style="display: table-cell;width: 8%;border-bottom: 1px double;height: 1.5em;max-height: 1.5em;">&nbsp;</div>
						<div class="smalltext" style="display: table-cell;border-bottom: 1px double;height: 1.5em;max-height: 1.5em;"><strong>', $txt['arcade_no_scores'], '</strong></div>
						', (!$context['arcade']['can_admin_arcade'] ? $context['arcade_empty_cell'] : str_repeat($context['arcade_empty_cell'], 2)) . '
						<div scope="col" class="last_th" style="display: table-cell;width: 8%;border-bottom: 1px double;height: 1.5em;max-height: 1.5em;">&nbsp;</div>';
	}

	echo '
					</div>
					<div style="display: table-row;" class="windowbg2">
						', (!$context['arcade']['can_admin_arcade'] ? str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 1em;', $context['arcade_empty_cell']), 5) : str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 1em;', $context['arcade_empty_cell']), 6)) . '
					</div>';

		$edit_button = '<span style="width: 16px;height: 16px;display: inline-block;background: url(' . $settings['default_images_url'] . '/arc_icons/arcade_edit.gif) no-repeat;vertical-align: middle;">&nbsp;</span>';

	foreach ($context['arcade']['scores'] as $score)
	{
		if (empty($score['time']) || empty($score['position']))
			continue;

		$div_con = addslashes(sprintf($txt['arcade_when'], $score['time'], duration_format($score['duration'])));
		$currentScore = !empty($_SESSION['arcade_current_score_id']) && $score['id'] == $_SESSION['arcade_current_score_id'] ? '<span style="font-weight: 900;font-style: oblique 40deg;">' . $score['score'] . '</span>' : $score['score'];
		echo '
					<div class="', $score['own'] ? 'windowbg2 arcade_own_score' : 'windowbg2', '"', !empty($score['highlight']) ? ' style="clear: both;width: 100%;display: table-row;"' : ' style="clear: both;width: 100%;display: table-row;font-weight: bold;"', ' onmouseover="arcadeBox(\'', $div_con, '\')" onmousemove="arcadeBoxMove(event)" onmouseout="arcadeBox(\'\')">
						<div style="display: table-cell;height: 1.5em;max-height: 1.5em;" class="windowbg2 centertext">', $score['position'], '</div>
						<div style="display: table-cell;padding-left: 1.0em;height: 1.5em;max-height: 1.5em;" class="windowbg2">', $score['member']['link'], '</div>
						<div style="display: table-cell;width: 300px;padding-left: 1.0em;height: 1.5em;max-height: 1.5em;" class="windowbg2">';

		if ($score['can_edit'] && empty($score['edit']) && empty($arcadeModSettings['arcadeDisableComments']))
			echo '
							<div id="comment', $score['id'], '" class="floatleft">', $score['comment'], '</div>
							<div id="edit', $score['id'], '" class="floatleft" style="display: none;">
								<input name="new_comment', $score['id'], '" onkeydown="enterkey(event)" type="text" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" maxlength="50" />
								<input id="okClick_' . $score['id'] . '" type="hidden" onclick="myformxyz(\'commentform3\', \'', $score['id'], '\')" name="csave" value="', $txt['arcade_save'], '" />
							</div>
								<p style="text-align: right;"><a style="height: 1.5em;max-height: 1.5em;text-align: right;" id="editlink', $score['id'], '" onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 1); myformxyz(\'commentform3\', \'', $score['id'], '\');" href="', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], ';edit;score=', $score['id'], ';reload=' . mt_rand(1, 9999) . ';#commentform3">', $edit_button, '</a></p>';
		elseif ($score['can_edit'] && !empty($score['edit']) && empty($arcadeModSettings['arcadeDisableComments']))
		{
			echo '
							<input type="hidden" name="score" id="s', $score['id'], '" value="', $score['id'], '" />
							<input type="text" name="new_comment', $score['id'], '" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" maxlength="50" />
							<input id="okClick_' . $score['id'] . '" onclick="myformxyz(\'commentform3\', \'', $score['id'], '\')" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />';
		}
		else
			echo '			<div style="height: 1.5em;max-height: 1.5em;display: inline;" class="floatleft">
								' . (empty($arcadeModSettings['arcadeDisableComments']) ? $score['comment'] : '') . '
							</div>';

		echo '
						</div>
						', str_repeat($context['arcade_empty_cell'], 2), '
						<div style="display: table-cell;padding-left: 1.0em;height: 1.5em;max-height: 1.5em;" class="centertext windowbg2">', $currentScore, '</div>';


		if ($context['arcade']['can_admin_arcade'])
			echo '
						<div style="display: table-cell;height: 1.5em;max-height: 1.5em;" class="windowbg2 centertext"><input type="checkbox" name="scores[]" value="', $score['id'], '" class="check" /></div>';

		echo '
					</div>
					<div style="display: table-row;" class="windowbg2">
						', (!$context['arcade']['can_admin_arcade'] ? str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 0.7em;', $context['arcade_empty_cell']), 5) : str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 0.7em;', $context['arcade_empty_cell']), 6)) . '
					</div>';
	}

	echo '
				</div>';

	if ($context['arcade']['can_admin_arcade'])
	{
		echo '
				<div style="display: block;width: 100%;padding-top: 2em;position: relative;text-align: right;">
					<div style="display: inline;text-align: right;width: 100%;">
						<select name="qaction">
							<option value="">--------</option>
							<option value="delete">', $txt['arcade_delete_selected'], '</option>
						</select>
						<input value="', $txt['go'], '" onclick="return mycheckxyz()" class="button_submit" type="submit" />
					</div>
					', ($context['arcade']['can_admin_arcade'] ? str_repeat($context['arcade_empty_cell'], 4) : str_repeat($context['arcade_empty_cell'], 5)) . '
				</div>';
	}

	echo '
			</div>
		</form>';
}

// Below game
function template_arcade_game_below()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings;
	$reportRedirect = (empty($arcadeModSettings['arcadeEnableGameDisable']) ? '"' . $scripturl . '?action=arcade;sa=play;game=" + gameid + ";reload=" + Math.floor((Math.random() * 8999) + 1000)' : '"' . $scripturl . '?action=arcade"');
	if (!empty($context['arcade_rom_play_flag']) && !in_array($context['arcade_rom_play_flag'], array('play', 'hiscore')))
		return;
	$context['arcade_rom_play_flag'] = 'below';

	echo '
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div class="pagesection">
		<div class="align_left">';

	if (isset($context['page_index']))
		echo $txt['pages'], ': ', $context['page_index'];

	if (!empty($arcadeModSettings['topbottomEnable']))
		echo isset($context['page_index']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '';

	echo '</div>
		', template_button_strip($context['arcade']['buttons'], 'right'), '
	</div>
	<div class="plainbox" id="arcadebox" style="display: none; position: fixed; left: 0px; top: 0px; width: 33%;">
		<div id="arcadebox_html" style="display: inline;"></div>
	</div>';

	echo '
		<script type="text/javascript">
			function arcadeReportScript()
			{
				var i = 0,reportClass = "", gameReports = [], prefix = "reportid_";;
				gameReports = document.querySelectorAll("[class*=button_strip_report]");
				for(i=0;i<gameReports.length;i++)
				{
					var myId = "' . $context['game']['id'] . '".replace(/[^0-9]/g, "");
					var myName = "' . $context['game']['name'] . '".replace(/[^ -a-z0-9]/ig, "_");
					gameReports[i].id = "reportid_" + myId;
					gameReports[i].title = "' . $txt['pdl_report_reason'] . '" + myName;
					if (window.addEventListener)
						document.getElementById("reportid_" + myId).addEventListener("click", function() {arcadeReportAjax(myId, myName, prefix); return false;}, false);
					else if (window.attachEvent)
						document.getElementById("reportid_" + myId).onclick.attachEvent("onclick", function() {arcadeReportAjax(myId, myName, prefix);return false;});
				}
			}
			function arcadeReportAjax(gameid, gamename, prefix)
			{
				document.getElementById(prefix + gameid).removeAttribute("href");
				var reason = prompt(\'' . $txt['pdl_report_reason_name'] . ' \' + gamename + \'' . '\r\n\r\n' . $txt['pdl_report_reason_input'] . '\');
				if (reason)
				{
					var url = "'. $scripturl . '?action=arcade;sa=report;game=" + gameid + ";' . $context['session_var'] . '=' . $context['session_id'] . '";
					var play_url = '. $reportRedirect . ';
					var data = "reason=" + encodeURIComponent(reason).replace(/\'/g, "%27");
					var callback = function(data){console.log(data);};
					arcadeAjaxSend(url, data, callback);
					setTimeout(function(){window.location.href = play_url;}, 1000);
				}
				else
					return false;
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