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
	list($skin, $sa) = array(
		!empty($user_info['arcade_settings']['skin']) ? $user_info['arcade_settings']['skin'] : 0,
		!empty($_REQUEST['sa']) ? $_REQUEST['sa'] : '',
	);
	$arcadeModSettings['arcadeRandomIdVar'] = !empty($arcadeModSettings['arcadeRandomIdVar']) ? $arcadeModSettings['arcadeRandomIdVar'] : 'smfarcade20201017';
	$currentArcadeAction = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (!empty($_SESSION['arcade_rom_initiate']) ? 'retro_arch' : 'arcade');

	echo '
	<script type="text/javascript">
		function arcadeAllDivs()
		{
			localStorage.setItem("arcadeSmfIdVar", "' . $arcadeModSettings['arcadeRandomIdVar'] . '");
			var arcadeAllTags = document.getElementsByTagName("*");
			var arcadeBody = document.getElementsByTagName("BODY")[0];
			var arcadeLinks = document.getElementsByTagName("A");
			var arcadeNavList = document.getElementById("menu_nav");
			var arcadeDeviceWidth = window.innerWidth > 0 ? window.innerWidth : screen.width;
			var arcadeChangeTags = ["DIV", "BODY", "P", "BLOCKQUOTE", "LI", "THEAD", "TFOOT", "TBODY"];
			var arcadeCheckTag;
			if (document.getElementsByClassName("frame") && document.getElementsByClassName("frame")[0])
				document.getElementsByClassName("frame")[0].style.background = "white";
			for(i=0;i<arcadeAllTags.length;i++)
			{
				arcadeCheckTag = arcadeChangeTags.includes(arcadeAllTags[i].tagName);
				if (arcadeAllTags[i].classList.contains("botslice") || arcadeAllTags[i].classList.contains("topslice")) {
					arcadeAllTags[i].style.display = "none";
				}
				if (!arcadeCheckTag)
					continue;

				arcadeAllTags[i].style.maxWidth = "100vw";
				arcadeAllTags[i].style.border = "0px";
				arcadeAllTags[i].style.borderLeft = "0px";
				arcadeAllTags[i].style.borderRight = "0px";
				arcadeAllTags[i].style.left = "0px";
				arcadeAllTags[i].style.right = "0em";
				arcadeAllTags[i].style.margin = "0em";
				arcadeAllTags[i].style.marginRight = "0em";
				arcadeAllTags[i].style.overflowX = "hidden";
				arcadeAllTags[i].style.fontSize = "medium";
				arcadeAllTags[i].style.boxSizing = "border-box";
				if (arcadeAllTags[i].classList.contains("cat_bar") || arcadeAllTags[i].classList.contains("title_bar")) {
					arcadeAllTags[i].style.paddingLeft = "0.18em";
					arcadeAllTags[i].style.marginLeft = "0em";
					arcadeAllTags[i].style.paddingRight = "-0.18em";
					arcadeAllTags[i].style.width = "99%";
				}
				else if (arcadeAllTags[i].classList.contains("frame") || arcadeAllTags[i].classList.contains("footer")) {
					arcadeAllTags[i].style.paddingLeft = "0em";
					arcadeAllTags[i].style.paddingRight = "0em";
					arcadeAllTags[i].style.background = "white";
				}
				else if (arcadeAllTags[i].classList.contains("windowbg") || arcadeAllTags[i].classList.contains("windowbg2")) {
					arcadeAllTags[i].style.background = "white";
					arcadeAllTags[i].style.display = "inline";
				}
				else {
					arcadeAllTags[i].style.paddingLeft = "0.1em";
					arcadeAllTags[i].style.paddingRight = "0.1em";
					arcadeAllTags[i].style.background = "white";
				}
			}
			for(i=0;i<arcadeLinks.length;i++)
			{
				arcadeLinks[i].style.display = "inline-flex";
				arcadeLinks[i].style.paddingRight = "0.15em";
			}
			if (arcadeNavList)
			{
				var arcadeNav = arcadeNavList.getElementsByTagName("LI");
				for(i=0;i<arcadeNav.length;i++)
				{
					arcadeNav[i].style.display = "inline-flex";
					arcadeNav[i].style.paddingLeft = "0.35em";
				}
			}
			arcadeBody.style.background = "white !important";
			arcadeBody.style.padding = "0em";
			arcadeBody.style.width = "100vw";
			arcadeBody.style.minWidth = "100vw";
			arcadeBody.style.margin = "0em";
			arcadeBody.style.textIndent = "0.2em";
			var arcadeContainers = [' . ($user_info['is_guest'] ? '"guest_form", ' : '') . '"top_section", "upper_section", "wrapper", "main_content_section", "content_section", "header", "footerfix", "footer_section"];
			var arcCurrent;
			for (i=0;i<arcadeContainers.length;i++)
			{
				arcCurrent = document.getElementById(arcadeContainers[i]);
				if (arcCurrent)
				{
					arcCurrent.style.boxSizing = "";
					switch(arcadeContainers[i]) {
						case "guest_form":
							arcCurrent.style.paddingLeft = "0.5em";
							arcCurrent.style.fontSize = "medium";
							arcCurrent.style.boxSizing = "";
							break;
						case "top_section":
							arcCurrent.style.fontSize = "small";
							arcCurrent.style.width = "100vw";
							arcCurrent.style.backgroundImage = "";
							arcCurrent.style.boxSizing = "";
							break;
						case "wrapper":
							arcCurrent.style = "min-width: 100%;max-width: 100%;width: 100%;padding: 0.1em;";
							arcCurrent.style.boxSizing = "";
							break;
						case "content_section":
							arcCurrent.style.position = "relative";
							arcCurrent.style.width = "100%";
							arcCurrent.style.boxSizing = "";
							break;
						case "header":
							arcCurrent.style.background = "white";
							arcCurrent.style.boxSizing = "";
							arcCurrent.style.padding = "0em";
							arcCurrent.style.textIndent = "0.2em";
							arcCurrent.style.width = "100%";
							break;
						case "footerfix":
							arcCurrent.style.boxSizing = "";
							arcCurrent.style.width = "100%";
							break;
						case "footer_section":
							arcCurrent.style.background = "white";
							break;
						default:
							arcCurrent.style.fontSize = "medium";
							arcCurrent.style.boxSizing = "";
					}
				}
			}
		}
		$(document).ready(function() {
			arcadeAllDivs();
			$("img#gamethumb").click(function() {
				window.location.href = "' . $scripturl . '?index.php;action=arcade;sa=play;game=' . $context['game']['id'] . '";
			});
		});
	</script>';

	echo '
	<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>';

	echo '
	<div class="arcade_up_contain" style="width: 96%;left: 0px;padding: 0px;margin: 0px;position: relative;">
		<div class="arcade_scorebox" style="border: 0px;top: 0;padding-top: 0;">
			<div class="title_bar" style="border: 0px;clear: both;position: relative;top: 0;left: 0;bottom: 0;right: 0;overflow: inherit;">
				<h3 class="titlebg" style="vertical-align: middle;border-radius: 3px;padding: 0px 2px 0px 2px;">
					<a href="', $scripturl, '?index.php;action=arcade;sa=play;game=', $context['game']['id'], '" title="', $txt['arcade_play'],' ', $context['game']['name'], '">
						<span class="clear: right;" style="font-size: 0.8em;">&nbsp;&nbsp;', $context['game']['name'], '</span>
					</a>
				</h3>
			</div>
			<div id="game_panel" class="smalltext" style="margin: 0;padding: 0px;z-index: 97;">
				<span style="padding-top: 1em;"><span>&nbsp;</span></span>
				', !empty($context['game']['thumbnail']) ? '<img id="gamethumb" class="floatleft thumb" src="' . $context['game']['thumbnail'] . '" alt="" />' : '', '
				<div class="floatleft scores" style="padding-left: 5px;vertical-align: bottom;">';

	if ($context['game']['is_champion'])
		echo '
					&nbsp;&nbsp;<strong class="smalltext">', $txt['arcade_champion'], ':</strong> ', $context['game']['champion']['link'], ' - ', $context['game']['champion']['score'], '<div><span style="display: none;">&nbsp;</span></div>';
	if ($context['game']['is_personal_best'])
		echo '
					&nbsp;&nbsp;<strong class="smalltext">', $txt['arcade_personal_best'], ':</strong> ', $context['game']['personal_best'], '<div><span style="display: none;">&nbsp;</span></div>';

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
				function myformxyz(myform, myscore)
				{
					var arcadeDisableComments = ' . (empty($arcadeModSettings['arcadeDisableComments']) ? '0' : '1') . ';
					if (myscore > 0)
					{
						var newercomment = document.getElementById("c"+myscore).value;
						myform = "commentform3";
						if (newercomment = "" || arcadeDisableComments == 1)
							newercomment = "', $txt['arcade_no_comment'], '";
						if (document.getElementById("comment" + myscore)) {
							document.getElementById("comment" + myscore).innerHTML = newercomment;
							document.forms[myform]["c" + myscore].value = newercomment;
							var datearcz = new Date();
							datearcz.setTime(datearcz.getTime()+(10*1000));
							var expiresarcz = datearcz.toGMTString();
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
						myform = "commentform1";
						if (document.getElementById("mynewscoreid") && document.forms[myform]["mynewscoreid"] !== "undefined" && document.forms[myform]["mynewscoreid"].value !== "undefined")
							myscore = document.forms[myform]["mynewscoreid"].value;
						else
							myscore = 0;
						if (document.getElementById("new_comment" + myscore)  && arcadeDisableComments == 0)
							var newercomment = document.getElementById("new_comment" + myscore).value;
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
						gameInfoBox.style.position = "relative";
						gameInfoParent.style.position = "relative";
						gameInfoBox.style.zIndex = "99";
						gameInfoParent.style.overflowX = "visible";
						gameInfoBox.style.overflowX = "visible";
						gameInfoBox.style.border = "2px solid";
						gameInfoBox.style.width = "50vw";
						gameInfoBox.style.padding = "0.2em";
						gameInfoParent.style.margin = "0em auto";
						if (gameFormId)
							gameFormId.style.paddingTop = "1.48em";
						gameInfoBox.style.right = "0em";
					}
				}
				function gameInfoOnload()
				{
					var gameInfoDivDataClick = document.getElementById("gameInfoDivData");
					gameInfoDivDataClick.onclick = function() {
						document.getElementById("gameInfoDivData").style.display = "none";
					};
					var gameInfoImg = document.getElementById("imgObjInfo");
					gameInfoImg.onclick = function() {gameinfoClickX();};
				}
				if (window.addEventListener)
					window.addEventListener("load", function (){
						gameInfoOnload();
						return false;
					});
				else
					window.attachEvent("onload", function (){
						gameInfoOnload();
						return false;
					});' : '') . '
			// ]]></script>';
}

// Play screen
function template_arcade_game_play()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info, $boardurl, $boarddir;

	$reload = isset($_REQUEST['reload']) ? (int)$_REQUEST['reload'] : 0;
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$ruffleNeed = isset($_COOKIE['smfArcadeSetFlashCookie']) && $_COOKIE['smfArcadeSetFlashCookie'] == 'shockwave' ? false : true;
	$ruffleEnable = !empty($arcadeModSettings['arcade_flash_emulator']) ? intval($arcadeModSettings['arcade_flash_emulator']) : 0;

	// fullscreen mode
	//print_r($context['game']['submit_system']);
	$context['game']['type'] = 'fullscreen';
	if (!empty($context['game']['html5']))
	{
		ob_end_clean();
		if (!empty($arcadeModSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');
		else
			ob_start();

		header('Content-Type: text/html;charset=UTF-8');
		echo str_replace('(SMF_GAME_TOKEN)', $_SESSION['arcade_html5_token'][1], $context['game']['html5']);
		obExit(false);
	}
	// container mode
	else
	{
		if (strpos($context['game']['submit_system'], 'html5')  === false && $ruffleEnable == 1 && $ruffleNeed) {
			echo '
	<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
		}
		elseif (strpos($context['game']['submit_system'], 'html5')  === false && file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/ruffle.js'))
		{
			$context['game']['extra_data']['height'] = !empty($_COOKIE['arcadeScrHeight']) ? intval($_COOKIE['arcadeScrHeight']) : $context['game']['extra_data']['height'];
			$context['game']['extra_data']['width'] = !empty($_COOKIE['arcadeScrWidth']) ? intval($_COOKIE['arcadeScrWidth']) : $context['game']['extra_data']['width'];
			//die(print_r($context['game']));
			if ($ruffleNeed)
				echo '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion . '"></script>';
		}
		elseif (strpos($context['game']['submit_system'], 'html5')  === false && $ruffleNeed && empty($ruffleEnable)) {
			echo '
	<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
		}

		$fullScreenIcon = !empty($context['game']['icon_position']) && $context['game']['icon_position'] == 2 ? '' : '
	<div onclick="arcadeFullscreenNow()" id="fullnow" class="mobile_contract" style="display: block;position: fixed;z-index: 99;' . (!empty($context['game']['icon_position']) ? $context['game']['icon_position'] : 'bottom: 0px;left: 0px;') . '"><img style="width: 1.5em;height: 1.5em;" id="fullnowimg" alt="FULL" src="' . $settings['default_theme_url'] . '/images/arc_icons/expand.png" /></div>';

		$display = $fullScreenIcon . '
	<div class="arcade_up_contain" style="width: 96%;left: 0px;padding: 0px;margin: 0px;position: relative;overflow-y: scroll;">
		<form id="gameForm" action="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . ';sa=' . $context['game']['submit_system'] . 'Game;" method="post" target="_self">
			<input type="hidden" id="game" name="game" value="' . $context['game']['id'] . '" />
			<input type="hidden" id="smfgametime" name="smfgametime" value="' . time() . '" />
			<input type="hidden" id="html5" name="html5" value="1" />' . ($context['game']['submit_system'] == 'html52' || $context['game']['submit_system'] == 'html53' ? '
			<input type="hidden" id="html52" name="html52" value="1" />' : '') . ($user_info['is_guest'] && empty($_SESSION['playerName']) ? '
			<input type="hidden" id="guestname" name="guestname" value="1" />' : '
			<input type="hidden" id="guestname" name="guestname" value="0" />') . '
			<input type="hidden" id="smfGameSaveUrl" name="smfGameSaveUrl" value="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-html5-save.js?' . $suffixVersion . '" />
			<input type="hidden" id="html5smfGameUrl" name="html5smfGameUrl" value="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . (!empty($reload) ? ';reload=' . $reload : '') . ';#playgame" />
			<input type="hidden" id="gameSmfFullscreen" name="gameSmfFullscreen" value="0" />
			<input type="hidden" id="popup" name="popup" value="0" />
			<input type="hidden" id="gameexit" name="gameexit" value="0" />
			<input type="hidden" id="noSmfScore" name="noSmfScore" value="' . $txt['arcade_noSmfScore'] . '" />
			<input type="hidden" id="gameSmfToken" name="gameSmfToken" value="' . $_SESSION['arcade_html5_token'][1] . '" />
			<input type="hidden" id="game_name" name="game_name" value="' . $context['game']['internal_name'] . '" />
		</form>
		<div id="arcadeGameFrame" style="background-color: ' . (!empty($context['game']['extra_data']['background_color']) && ctype_xdigit($context['game']['extra_data']['background_color']) && (strlen($context['game']['extra_data']['background_color'])==6 || strlen($context['game']['extra_data']['background_color'])==3) ? '#' . $context['game']['extra_data']['background_color'] : 'black') . ';border: 0px;margin: 0em;padding: 0px;left: 0;top: 0;display: flex;position: fixed;overflow: hidden;width: 99.9999vw; height: 100vh;min-height: 100vh;min-width: 99.9999vw;max-height: 100vh;max-width: 99.999vw;z-index: 95;">
			<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="" style="width: 100%;height: 100%;">
				<param name="movie" value="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '" />
				<param name="quality" value="high" />
				<param name="scale" value="exactfit" />
				<embed id="arcadeGameObject" src="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '" quality="high" type="application/x-shockwave-flash" style="width: 100%;height: 100%;" scale="exactfit" pluginspage="http://www.macromedia.com/go/getflashplayer" />
			</object>
		</div>
		' . (!$context['arcade']['can_submit'] ? '
		<div><span style="display: none;">&nbsp;</span></div><strong style="display: ' . (!empty($_SESSION['arcade_isMobilePlay']) ? 'none' : 'inline') . ';">' . $txt['arcade_cannot_save'] . '</strong>' : '') . '
	</div>';
		// <embed src="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '" style="width:100vw;height:100vh;left: 0em;top: 0em;padding: 0em;margin 0em;position: absolute;object-fit: fill;overflow: hidden;"></embed>
		echo '
	<script type="text/javascript">
		localStorage.setItem("reloadArcadeGame", 0);
		function IsArcadeFullScreen() {
			var checkThisArcadeScreen = !!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) ? true : false;
			return checkThisArcadeScreen;
		}
		function checkArcadeFullScreen() {
			setInterval(function() {
				if (!IsArcadeFullScreen() && document.getElementById("fullnow").style.display == "none") {
					document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png";
					document.getElementById("fullnow").style.display = "block";
					document.getElementById("fullnow").style.zIndex = 99;
				}
			}, 3000);
		}
		function arcadeGameOnloadEvent(gameframe) {
			loadSmfExtraGameData();
			if (' . (int)$context['game']['js_insertion'] . ' == 1 && typeof submitSmfArcadeScoreCode !== "function")
				setTimeout(reloadArcadeGameContainer(), 150);
			return false;
		}
		function reloadArcadeGameContainer() {
			var yCount, xCount = localStorage.getItem("reloadArcadeGame");
			if (xCount < 3) {
				yCount = xCount + 1;
				localStorage.setItem("reloadArcadeGame", yCount);
				document.getElementsByName("arcadeGameFrame")[0].contentWindow.location.reload();
			}
		}
		function loadSmfExtraGameData() {
			var arcadeObject = document.getElementsByName("arcadeGameFrame")[0];
			var arcadeScript = document.createElement("script");
			arcadeScript.src = "' . $settings['default_theme_url'] . '/arcade_scripts/arcadeAdd.js?' . $suffixVersion . '";
			var arcadeData = arcadeObject.contentDocument || arcadeObject.contentWindow.document;
			if (arcadeData && arcadeData.getElementsByTagName("BODY")) {
				' . (empty($context['game']['js_insertion']) ? 'if (!arcadeData.getElementById("block_game"))' : 'if (' . (int)$context['game']['js_insertion'] . ' == 1)') . '
					arcadeData.getElementsByTagName("BODY")[0].insertAdjacentElement("beforeend", arcadeScript);
			}
		}
		function requestArcadeFullscreen(element) {
			if (element.requestFullscreen) {
				element.requestFullscreen();
			} else if (element.mozRequestFullScreen) {
				element.mozRequestFullScreen();
			} else if (element.webkitRequestFullScreen) {
				element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
			}
		}
		function toggleArcadeFull() {
			var i = 0;
			window.innerHeight = window.innerHeight + 10;
			var arcadeAllTags = document.body.getElementsByTagName("*");
			for(i=0;i<arcadeAllTags.length;i++)
				arcadeAllTags[i].style.display = "none";
			document.body.insertAdjacentHTML(\'afterbegin\', \'' . preg_replace( '/(\s){2,}/s', '', $display ) . '\');
		}
		function smfArcadeGameDimsMobile() {
			sessionStorage.removeItem("scoreLoop_' . $_SESSION['arcade_html5_token'][1] . '");
		}
		function arcadeLoadFunctions() {
			$("body").css("text-indent", "0px");' . (empty($user_info['is_guest']) && !empty($user_info['name']) ? '
			localStorage.setItem("arcade_game_user_name", "' . $user_info['name'] . '");' : '
			localStorage.setItem("arcade_game_user_name", "' . $txt['guest_title'] . '");') . '
			smfArcadeGameDimsMobile();' . (empty($fullScreenIcon) ? '' : '
			checkArcadeFullScreen();') . '
			toggleArcadeFull();
			var fullnow = document.getElementById("fullnow");
			var fullevent = document.createElement("span");
			var newContent = document.createTextNode(" ");
			fullevent.appendChild(newContent);
			fullnow.appendChild(fullevent);
			if(window.attachEvent)
				window.attachEvent("onkeydown", function(event) {
					if(event.keyCode === 27 || event.key === "Escape"){
						document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png";
						document.getElementById("fullnow").style.display = "block";
						document.getElementById("fullnow").style.zIndex = 98;
						return false;
					}
				});
			else if (window.addEventListener)
				window.addEventListener("keydown", function(event) {
					if(event.keyCode === 27 || event.key === "Escape"){
						document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png";
						document.getElementById("fullnow").style.display = "block";
						document.getElementById("fullnow").style.zIndex = 98;
						return false;
					}
				});
		}
		function arcadeFullscreenNow() {
			var el = document.documentElement;
			var ex = document.getElementById("fullnow");
			if (ex.style.zIndex == 99) {
				if (el.webkitRequestFullscreen)
					el.webkitRequestFullscreen();
				else if (el.mozRequestFullScreen)
					el.mozRequestFullScreen();
				else if (el.msRequestFullscreen)
					el.msRequestFullscreen();
				else if (el.requestFullscreen)
					el.requestFullscreen();
				ex.style.zIndex = 98;
				' . (empty($context['game']['icon_position_hide']) ? 'ex.style.display = "none";' : '') . '
				document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/contract.png";
			}
			else if (ex.style.zIndex == 98) {
				if (document.exitFullscreen)
					document.exitFullscreen();
				else if (document.mozCancelFullScreen)
					document.mozCancelFullScreen();
				else if (document.webkitExitFullscreen)
					document.webkitExitFullscreen();
				else if (document.msExitFullscreen)
					document.msExitFullscreen();
				ex.style.zIndex = 99;
				ex.style.display = "block";
				document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png";
			}
		}
		$(document).ready(function(){
			arcadeLoadFunctions();
			$("iframe#arcadeGameObject").width($("iframe#arcadeGameObject")[0].scrollWidth);
			$("iframe#arcadeGameObject").height($("iframe#arcadeGameObject")[0].scrollHeight);
		});
	</script>';
	}
}

function template_arcade_html5_game_play()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info, $boardurl, $boarddir;

	$reload = isset($_REQUEST['reload']) ? (int)$_REQUEST['reload'] : 0;
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$ruffleNeed = isset($_COOKIE['smfArcadeSetFlashCookie']) && $_COOKIE['smfArcadeSetFlashCookie'] == 'shockwave' ? false : true;
	$ruffleEnable = !empty($arcadeModSettings['arcade_flash_emulator']) ? intval($arcadeModSettings['arcade_flash_emulator']) : 0;

	// fullscreen mode
	//print_r($context['game']['submit_system']);
	$context['game']['type'] = 'fullscreen';
	if (!empty($context['game']['html5']))
	{
		ob_end_clean();
		if (!empty($arcadeModSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');
		else
			ob_start();

		header('Content-Type: text/html;charset=UTF-8');
		echo str_replace('(SMF_GAME_TOKEN)', $_SESSION['arcade_html5_token'][1], $context['game']['html5']);
		obExit(false);
	}
	// container mode
	else
	{
		if (strpos($context['game']['submit_system'], 'html5')  === false && $ruffleEnable == 1 && $ruffleNeed) {
			echo '
	<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
		}
		elseif (strpos($context['game']['submit_system'], 'html5')  === false && file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/ruffle.js') && $ruffleNeed)
		{
			echo '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion . '"></script>';
		}
		elseif (strpos($context['game']['submit_system'], 'html5')  === false && $ruffleNeed && empty($ruffleEnable)) {
			echo '
	<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
		}

		$fullScreenIcon = !empty($context['game']['icon_position']) && $context['game']['icon_position'] == 2 ? '' : '
	<div onclick="arcadeFullscreenNow()" id="fullnow" class="mobile_contract" style="display: block;position: fixed;z-index: 99;' . (!empty($context['game']['icon_position']) ? $context['game']['icon_position'] : 'bottom: 0px;left: 0px;') . '"><img style="width: 1.5em;height: 1.5em;" id="fullnowimg" alt="FULL" src="' . $settings['default_theme_url'] . '/images/arc_icons/expand.png" /></div>';

		$display = $fullScreenIcon . '
	<div class="arcade_up_contain" style="width: 96%;left: 0px;padding: 0px;margin: 0px;position: relative;overflow-y: scroll;">
		<form id="gameForm" action="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . ';sa=' . $context['game']['submit_system'] . 'Game;" method="post" target="_self">
			<input type="hidden" id="game" name="game" value="' . $context['game']['id'] . '" />
			<input type="hidden" id="smfgametime" name="smfgametime" value="' . time() . '" />
			<input type="hidden" id="html5" name="html5" value="1" />' . ($context['game']['submit_system'] == 'html52' || $context['game']['submit_system'] == 'html53' ? '
			<input type="hidden" id="html52" name="html52" value="1" />' : '') . ($user_info['is_guest'] && empty($_SESSION['playerName']) ? '
			<input type="hidden" id="guestname" name="guestname" value="1" />' : '
			<input type="hidden" id="guestname" name="guestname" value="0" />') . '
			<input type="hidden" id="smfGameSaveUrl" name="smfGameSaveUrl" value="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-html5-save.js?' . $suffixVersion . '" />
			<input type="hidden" id="html5smfGameUrl" name="html5smfGameUrl" value="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . (!empty($reload) ? ';reload=' . $reload : '') . ';#playgame" />
			<input type="hidden" id="gameSmfFullscreen" name="gameSmfFullscreen" value="0" />
			<input type="hidden" id="popup" name="popup" value="0" />
			<input type="hidden" id="gameexit" name="gameexit" value="0" />
			<input type="hidden" id="noSmfScore" name="noSmfScore" value="' . $txt['arcade_noSmfScore'] . '" />
			<input type="hidden" id="gameSmfToken" name="gameSmfToken" value="' . $_SESSION['arcade_html5_token'][1] . '" />
			<input type="hidden" id="game_name" name="game_name" value="' . $context['game']['internal_name'] . '" />
		</form>
		<iframe onerror="reloadArcadeGameContainer(1)" onload="arcadeGameOnloadEvent()" name="arcadeGameFrame" id="arcadeGameFrame" style="-webkit-overflow-scrolling: touch;border: 0px;padding: 0px;left: 0;right: 0;top: 0;bottom: 0;display: flex;position: fixed;overflow-y: scroll;width: 99.9999vw; height: 100vh;min-height: 100vh;min-width: 99.9vw;max-height: 100vh;max-width: 100vw;z-index: 95;" src="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '" allowfullscreen>&nbsp;</iframe>
		' . (!$context['arcade']['can_submit'] ? '
		<div><span style="display: none;">&nbsp;</span></div><strong style="display: ' . (!empty($_SESSION['arcade_isMobilePlay']) ? 'none' : 'inline') . ';">' . $txt['arcade_cannot_save'] . '</strong>' : '') . '
	</div>';
		echo '
	<script type="text/javascript">
		localStorage.setItem("reloadArcadeGame", 0);
		function IsArcadeFullScreen() {
			var checkThisArcadeScreen = !!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) ? true : false;
			return checkThisArcadeScreen;
		}
		function checkArcadeFullScreen() {
			setInterval(function() {
				if (!IsArcadeFullScreen() && document.getElementById("fullnow").style.display == "none") {
					document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png";
					document.getElementById("fullnow").style.display = "block";
					document.getElementById("fullnow").style.zIndex = 99;
				}
			}, 3000);
		}
		function arcadeGameOnloadEvent() {
			loadSmfExtraGameData();
			' . (empty($context['game']['js_insertion']) ? 'if (typeof submitSmfArcadeScoreCode !== "function")' : 'if (' . (int)$context['game']['js_insertion'] . ' == 1 && typeof submitSmfArcadeScoreCode !== "function")') . '
				setTimeout(reloadArcadeGameContainer(), 150);
			return false;
		}
		function reloadArcadeGameContainer() {
			var yCount, xCount = localStorage.getItem("reloadArcadeGame");
			if (xCount < 1) {
				yCount = xCount + 1;
				localStorage.setItem("reloadArcadeGame", yCount);
				document.getElementsByName("arcadeGameFrame")[0].contentWindow.location.reload();
			}
		}
		function loadSmfExtraGameData() {
			var arcadeObject = document.getElementsByName("arcadeGameFrame")[0];
			var arcadeScript = document.createElement("script");
			arcadeScript.src = "' . $settings['default_theme_url'] . '/arcade_scripts/arcadeAdd.js?' . $suffixVersion . '";
			var arcadeData = arcadeObject.contentDocument || arcadeObject.contentWindow.document;
			if (arcadeData && arcadeData.getElementsByTagName("BODY")) {
				' . (empty($context['game']['js_insertion']) ? 'if (!arcadeData.getElementById("block_game"))' : 'if (' . (int)$context['game']['js_insertion'] . ' == 1)') . '
					arcadeData.getElementsByTagName("BODY")[0].insertAdjacentElement("beforeend", arcadeScript);
			}
		}
		function requestArcadeFullscreen(element) {
			if (element.requestFullscreen) {
				element.requestFullscreen();
			} else if (element.mozRequestFullScreen) {
				element.mozRequestFullScreen();
			} else if (element.webkitRequestFullScreen) {
				element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
			}
		}
		function toggleArcadeFull() {
			var i = 0;
			window.innerHeight = window.innerHeight + 10;
			var arcadeAllTags = document.body.getElementsByTagName("*");
			for(i=0;i<arcadeAllTags.length;i++)
				arcadeAllTags[i].style.display = "none";
			document.body.insertAdjacentHTML(\'afterbegin\', \'' . preg_replace( '/(\s){2,}/s', '', $display ) . '\');
		}
		function smfArcadeGameDimsMobile() {
			sessionStorage.removeItem("scoreLoop_' . $_SESSION['arcade_html5_token'][1] . '");
		}
		function arcadeLoadFunctions() {' . (empty($user_info['is_guest']) && !empty($user_info['name']) ? '
			localStorage.setItem("arcade_game_user_name", "' . $user_info['name'] . '");' : '
			localStorage.setItem("arcade_game_user_name", "' . $txt['guest_title'] . '");') . '
			smfArcadeGameDimsMobile();' . (empty($fullScreenIcon) ? '' : '
			checkArcadeFullScreen();') . '
			toggleArcadeFull();
			var fullnow = document.getElementById("fullnow");
			var fullevent = document.createElement("span");
			var newContent = document.createTextNode(" ");
			fullevent.appendChild(newContent);
			fullnow.appendChild(fullevent);
			$(window).keydown(function (event) {
				event.preventDefault();
				if(event.keyCode === 27 || event.key === "Escape"){
					$("img#fullnowimg").prop("src", "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png");
					$("$fullnow").css({"display":"block", "z-index":"999", "position":"absolute", "left":"0px", "bottom":"0px"});
					return false;
				}
			});
		}
		function arcadeFullscreenNow() {
			var el = document.documentElement;
			var ex = document.getElementById("fullnow");
			if (ex.style.zIndex == 99) {
				if (el.webkitRequestFullscreen)
					el.webkitRequestFullscreen();
				else if (el.mozRequestFullScreen)
					el.mozRequestFullScreen();
				else if (el.msRequestFullscreen)
					el.msRequestFullscreen();
				else if (el.requestFullscreen)
					el.requestFullscreen();
				ex.style.zIndex = 98;
				' . (empty($context['game']['icon_position_hide']) ? 'ex.style.display = "none";' : '') . '
				document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/contract.png";
			}
			else if (ex.style.zIndex == 98) {
				if (document.exitFullscreen)
					document.exitFullscreen();
				else if (document.mozCancelFullScreen)
					document.mozCancelFullScreen();
				else if (document.webkitExitFullscreen)
					document.webkitExitFullscreen();
				else if (document.msExitFullscreen)
					document.msExitFullscreen();
				ex.style.zIndex = 99;
				ex.style.display = "block";
				document.getElementById("fullnowimg").src = "' . $settings['default_theme_url'] . '/images/arc_icons/expand.png";
			}
			$("iframe#arcadeGameFrame").css("width", "100vw");
			$("iframe#arcadeGameFrame").css("height", "100vh");
		}
		$(document).ready(function(){
			arcadeLoadFunctions();
			$("iframe#arcadeGameFrame").width($("iframe#arcadeGameFrame")[0].scrollWidth);
			$("iframe#arcadeGameFrame").height($("iframe#arcadeGameFrame")[0].scrollHeight);
			$("iframe#arcadeGameFrame").on("load", function() {
				$(this).css("display", "flex");
				$(this).css("flex", 1);
				$(this).css("flex-direction", "row");
				$(this).css("align-self", "stretch");
				$(this).contents().find("head").append("<style>body{width: 100vw !important;}</style>");
			});
		});
	</script>';
	}
}

// Highscore
function template_arcade_game_highscore()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info;

	echo '
	<script type="text/javascript">
		sessionStorage.clear();
		function arcadeHighscoreMobile()
		{
			window.onorientationchange = function() {
				setTimeout(function() {
					if(window.innerHeight > window.innerWidth){
						arcadeDisplayPortrait();
						arcadeAllDivsZ();
					}
					else{
						arcadeDisplayLandscape();
						arcadeAllDivsZ();
					}
				}, 1000);
			}
			if(window.innerHeight > window.innerWidth){
				arcadeDisplayPortrait();
				arcadeAllDivsZ();
			}
			else{
				arcadeDisplayLandscape();
				arcadeAllDivsZ();
			}
			var allScoreBottomDivs = document.getElementsByClassName("arcade_bottom_border");
			for (i=0;i<allScoreBottomDivs.length;i++) {
				allScoreBottomDivs[i].style.borderBottom = "0.14em solid";
			}
		}
		function arcadeDisplayPortrait()
		{
			var arcadeContainers = [' . ($user_info['is_guest'] ? '"guest_form", ' : '') . '"top_section", "upper_section", "wrapper", "main_content_section", "content_section", "header", "footerfix"];
			var arcCurrent, i = 0;
			for (i=0;i<arcadeContainers.length;i++)
			{
				arcCurrent = document.getElementById(arcadeContainers[i]);
				if (arcCurrent)
				{
					arcCurrent.style.boxSizing = "";
					switch(arcadeContainers[i]) {
						case "guest_form":
							arcCurrent.style.paddingLeft = "0.5em";
							arcCurrent.style.fontSize = "medium";
							arcCurrent.style.boxSizing = "";
							break;
						case "top_section":
							arcCurrent.style.fontSize = "small";
							arcCurrent.style.width = "100vw";
							arcCurrent.style.backgroundImage = "";
							arcCurrent.style.boxSizing = "";
							break;
						case "wrapper":
							arcCurrent.style = "min-width: 100%;max-width: 100%;width: 100%;padding: 0.1em;";
							arcCurrent.style.boxSizing = "";
							break;
						case "content_section":
							arcCurrent.style.position = "relative";
							arcCurrent.style.width = "100%";
							arcCurrent.style.boxSizing = "";
							break;
						case "header":
							arcCurrent.style.background = "transparent";
							arcCurrent.style.boxSizing = "";
							arcCurrent.style.padding = "0em";
							arcCurrent.style.textIndent = "0.2em";
							arcCurrent.style.width = "100%";
							break;
						case "footerfix":
							arcCurrent.style.boxSizing = "";
							arcCurrent.style.width = "100%";
							break;
						default:
							arcCurrent.style.fontSize = "medium";
							arcCurrent.style.boxSizing = "";
					}
				}
			}
		}
		function arcadeDisplayLandscape()
		{
			var arcadeContainers = ["top_section", "upper_section", "wrapper", "main_content_section", "content_section", "header", "footerfix"];
			var arcCurrent, i = 0;
			for (i=0;i<arcadeContainers.length;i++)
			{
				arcCurrent = document.getElementById(arcadeContainers[i]);
				if (arcCurrent)
				{
					arcCurrent.style.boxSizing = "";
					switch(arcadeContainers[i]) {
						case "top_section":
							arcCurrent.style.fontSize = "small";
							arcCurrent.style.width = "100vw";
							arcCurrent.style.backgroundImage = "";
							arcCurrent.style.boxSizing = "";
							break;
						case "wrapper":
							arcCurrent.style = "min-width: 100%;max-width: 100%;width: 100%;padding: 0.1em;";
							arcCurrent.style.boxSizing = "";
							break;
						case "content_section":
							arcCurrent.style.position = "relative";
							arcCurrent.style.width = "100%";
							arcCurrent.style.boxSizing = "";
							break;
						case "header":
							arcCurrent.style.background = "transparent";
							arcCurrent.style.boxSizing = "";
							arcCurrent.style.padding = "0em";
							arcCurrent.style.textIndent = "0.2em";
							arcCurrent.style.width = "100%";
							break;
						case "footerfix":
							arcCurrent.style.boxSizing = "";
							arcCurrent.style.width = "100%";
							break;
						default:
							arcCurrent.style.fontSize = "medium";
							arcCurrent.style.boxSizing = "";
					}
				}
			}
		}
		function arcadeAllDivsZ()
		{
			var arcadeSendInput = document.getElementsByName("csave");
			var arcadeGameSendComment = document.getElementsByClassName("arcadeGameSendComment");
			var arcadeCommentInput = document.querySelectorAll(\'[name^="new_comment"]\');
			var arcadeCommentNew = document.getElementsByClassName("arcade_comment_new");
			var arcadeSearch = document.getElementById("search_form");
			var arcadeGameSearch = document.getElementsByClassName("button_submit");
			if (typeof arcadeGameSendComment !== "undefined")
			{
				for(i=0;i<arcadeGameSendComment.length;i++) {
					arcadeGameSendComment[i].style.fontSize = "small";
					arcadeGameSendComment[i].style.display = "flex-block";
					arcadeGameSendComment[i].style.width = "4em";
					arcadeGameSendComment[i].style.height = "2em";
					arcadeGameSendComment[i].style.paddingTop = "0.2em";
					arcadeGameSendComment[i].onmouseover = function() {this.style.background = "rgba(0, 0, 0, 0.5)";};
					arcadeGameSendComment[i].onmouseout = function() {this.style.backgroundColor = "inherit";};
				}
			}
			if (typeof arcadeSearch !== "undefined") {
				if (arcadeSearch)
					arcadeSearch.style.display = "none";
			}
			if (typeof arcadeGameSearch !== "undefined")
			{
				for(i=0;i<arcadeGameSearch.length;i++) {
					arcadeGameSearch[i].style.fontSize = "x-small";
				}
			}
			if (typeof arcadeCommentNew !== "undefined")
			{
				for(i=0;i<arcadeCommentNew.length;i++) {
					arcadeCommentNew[i].style = "background: white;z-index: 99;position: absolute;left: 0em;color: black;top: 5em;float: left:width: 65%;";
				}
			}
			if (typeof arcadeCommentInput !== "undefined")
			{
				for(i=0;i<arcadeCommentInput.length;i++) {
					arcadeCommentInput[i].style.width = "75vw";
					arcadeCommentInput[i].style.relative = "relative";
					arcadeCommentInput[i].style.display = "flex-block";
					arcadeCommentInput[i].style.left = "0em";
				}
			}
		}
		$(document).ready(function() {
			$(".arcade_scorebox").css("overflow", "hidden");
			$(".arcade_scorebox").css("width", "100%");
			$(".arcade_scorebox").css("padding-left", "0.75rem");
			$(".arcade_scorebox").css("padding-right", "0.75rem");
			$(".arcade_scorebox").css("border-radius", "0rem");
			$(".arcade_scorebox > .title_bar").css("border-radius", "0rem");
			$("#game_panel").css("padding", "0.3rem 0.2rem 0.3rem 0.2rem");
			$("#footer > .inner_wrap").css("overflow", "hidden");
			$("#footer > .inner_wrap").css("width", "100%");
			$("#footer > .inner_wrap").css("min-height", "120%");
			$("#footer > .inner_wrap").css("margin-bottom", "0rem");
			$("#footer > .inner_wrap").css("left", "-0.35rem");
			$("#footer > .inner_wrap").css("position", "relative");
			$("#footer > .inner_wrap > ul > li > a").css("display", "inline-flex");
			$("#footer > .inner_wrap > ul > li").css("filter", "contrast(140%)");
			$("#footer > .inner_wrap > ul > li").css("font-size", "smaller");
			$("#footer > .inner_wrap > ul > li").text(function(index, text) {return text.replace(" | ", "");});
			$("#footer > .inner_wrap > ul > li > a").css("font-family", \'"Andale Mono", "monospace"\');
			$("#footer > .inner_wrap > ul > li > a").css("justify-content", "center");
			$("#footer > .inner_wrap > ul > li.copyright").css("padding-top", "0.5rem");
			$("#footer > .inner_wrap > ul > li").css("color", "white");
			$("#footer > .inner_wrap > ul > li").css("background", "transparent");
			$("#footer > .inner_wrap > ul > li").css("line-height", "1.2rem");
			$("#footer > .inner_wrap > ul > li").css("width", "105%");
			$("#footer").css("background", "transparent");
			$(".forumtitle").css("padding-top", "1.8rem");
			$(".forumtitle").css("font-size", "12pt");
			$(".arcade_select").css("width", "84vw");
			$(".arcade_select").css("min-width", "84vw");
			$(".navigate_section > ul").css("border", "0px");
			$(".navigate_section > ul").css("background", "transparent");
			$(".arcade_up_contain").css("background", "transparent");
			$(".arcade_up_contain").css("width", "100%");
			$("div").css("background", "transparent");
			arcadeHighscoreMobile();
		});
	</script>
	<div style="position: relative; z-index: 96;overflow: hidden;margin: 0 auto;height: 100%;width: 90%;min-height: 100%;min-width: 90%;max-height: 100%;max-width: 100%;left: 0;overflow: hidden;">';

	if (isset($context['arcade']['submit']))
	{
		if ($context['arcade']['submit'] == 'newscore') // Was score submitted
		{
			$score = &$context['arcade']['new_score'];

			echo '
			<div class="title_bar" style="border: 0px;clear: both;position: relative;">
				<h3 class="titlebg" style="vertical-align: middle;border-radius: 3px;padding: 0px 2px 0px 2px;">
					<span class="clear: right;" style="font-size: 0.8em;">', $txt['arcade_submit_score'], '</span>
				</h3>
			</div>
			<div class="mediumtext">
				<span style="padding-top: 1em;"><span>&nbsp;</span></span>
				<div style="padding: 0 0.5em">';

			// No permission to save
			if (!$score['saved'])
				echo '
					<div>', $txt[$score['error']], '<div><span style="display: none;">&nbsp;</span></div><strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '</div>';

			else
			{
				echo '
					<div style="word-spacing: 0.75em;white-space: pre-line;line-height: 0.65em;padding: 0px;margin: 0 auto;">
						', $txt['arcade_score_saved'], '
						<div><span style="display: none;">&nbsp;</span></div>
						<strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '
						<div><span style="display: none;">&nbsp;</span></div>
						<div><span style="display: none;">&nbsp;</span></div>';

				if ($score['is_new_champion'])
					echo '
						', $txt['arcade_you_are_now_champion'], '<div><span>&nbsp;</span></div>
						<div><span style="display: none;">&nbsp;</span></div>
						<div><span style="display: none;">&nbsp;</span></div>';
				elseif ($score['is_personal_best'])
					echo '
						', $txt['arcade_this_is_your_best'], '
						<div><span style="display: none;">&nbsp;</span></div>
						<div><span style="display: none;">&nbsp;</span></div>';

				if ($score['can_comment'] && empty($arcadeModSettings['arcadeDisableComments']))
					echo '
					</div>
					<div style="word-spacing: 0.75em;white-space: pre-line;line-height: 0.65em;padding: 0px;margin: 0 auto;">
						<form name="commentform1" id="commentform1" action="', $scripturl, '?action=arcade;sa=highscore;game=', $context['game']['id'], ';score=',  $score['id'], ';reload=', mt_rand(1, 9999), ';#commentform3" method="post">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" name="mynewscoreid" value="', $score['id'], '" />
							<input type="text" id="new_comment', $score['id'], '" name="new_comment', $score['id'], '" style="width: 95%;" maxlength="50" />
							<input onclick="myformxyz(\'commentform1\', 0)" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />
						</form>';
			}

			echo '
				</div>
				<span style="padding-top: 1em;"><span>&nbsp;</span></span>
			</div>
			<div><span style="display: none;">&nbsp;</span></div>';
		}
		elseif ($context['arcade']['submit'] == 'askname')
		{
			echo '
			<span class="clear: right;" style="font-size: 0.8em;">', $txt['arcade_submit_score'], '</span>
			<div><span style="display: none;">&nbsp;</span></div>
			<div class="smalltext" style="word-spacing: 0.75em;white-space: pre-line;line-height: 1.5em;">
				<span style="padding-top: 1em;"><span>&nbsp;</span></span>
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
		<div style="width: 0px;height: 0px;" id="mobileHiScoreFlag"><span></span></div>
		<form id="commentform3" name="commentform3" action="', $scripturl, '?action=arcade;sa=highscore;reload=', mt_rand(1, 9999), ';#commentform3" method="post" onsubmit="myformxyz(\'commentform3\', 0)">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="game" value="', $context['game']['id'], '" />
			<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
			<span class="clear: right;" style="display: block;font-size: 0.8em;overflow: hidden;text-align: center;text-decoration: underline;">', $txt['arcade_highscores'], '</span>
			<div><span style="display: none;">&nbsp;</span></div>
			<div class="score_table smalltext">
				<div style="display: table;border-collapse: collapse;width: 100%;position: relative;" class="table_grid" id="arccomments">
					<div style="display: table-row;">';

	// Is there games?
	if (!empty($context['arcade']['scores']))
	{
			echo '
						<div scope="col" class="arcade_bottom_border first_th" style="display: table-cell;width: 5px;border-bottom: 1px double;padding-right: 1em;vertical-align: bottom;height: 1.5em;max-height: 1.5em;"><img src="', $settings['default_theme_url'] ,'/images/arc_icons/trophy_blank.png" style="width: 22px;height: 22px;" title="', $txt['arcade_position'], '" alt="#" /></div>
						<div scope="col" class="arcade_bottom_border" style="display: table-cell;border-bottom: 1px double;padding-right: 1em;height: 1.5em;max-height: 1.5em;"><a href="' . $context['arcade_scorelist_toggle'] . '">', $txt['arcade_member'], '</a></div>
						<div scope="col" class="arcade_bottom_border" style="display: table-cell;border-bottom: 1px double;height: 1.5em;max-height: 1.5em;"> ', $txt['arcade_comment'], '</div>
						', (!$context['arcade']['can_admin_arcade'] ? $context['arcade_empty_title_cell'] : str_repeat($context['arcade_empty_title_cell'], 2)) . '
						' . (!$context['arcade']['can_admin_arcade'] ? '<div scope="col" class="last_th" style="display: table-cell;border-bottom: 1px double;text-align: center;height: 1.5em;max-height: 1.5em;"></div>' : '') . '
						<div scope="col" class="', (!$context['arcade']['can_admin_arcade'] ? 'arcade_bottom_border last_th' : 'arcade_bottom_border'), '" style="display: table-cell;border-bottom: 1px double;text-align: center;height: 1.5em;max-height: 1.5em;">', $txt['arcade_score'], '</div>';

		if ($context['arcade']['can_admin_arcade'])
			echo '
						<div scope="col" class="arcade_bottom_border last_th centertext" style="display: table-cell;width: 15px;height: 1.5em;max-height: 1.5em;"><input type="checkbox" onclick="invertAll(this, this.form, \'scores[]\');" class="check" /></div>';
	}
	else
	{
		echo '
						<div scope="col" class="first_th" style="display: table-cell;width: 8%;border-bottom: 1px double;">&nbsp;</div>
						<div class="smalltext" style="display: table-cell;border-bottom: 1px double;"><strong>', $txt['arcade_no_scores'], '</strong></div>
						', (!$context['arcade']['can_admin_arcade'] ? $context['arcade_empty_cell'] : str_repeat($context['arcade_empty_cell'], 2)) . '
						<div scope="col" class="last_th" style="display: table-cell;width: 8%;border-bottom: 1px double;">&nbsp;</div>';
	}

	echo '
					</div>
					<div style="display: table-row;" class="arcade_none">
						', (!$context['arcade']['can_admin_arcade'] ? str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 1em;', $context['arcade_empty_cell']), 5) : str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 1em;', $context['arcade_empty_cell']), 6)) . '
					</div>';

		$edit_button = '<span style="width: 16px;height: 16px;display: inline-block;background: url(' . $settings['default_images_url'] . '/arc_icons/arcade_edit.gif) no-repeat;vertical-align: middle;">&nbsp;</span>';

	foreach ($context['arcade']['scores'] as $score)
	{
		if (empty($score['time']) || empty($score['position']))
			continue;

		// determine visible comment length for device
		$maxLength = strtolower($_SESSION['arcade_deviceType']) == 'phone' ? 13 : 30;
		$score['comment'] = strlen($score['comment']) > $maxLength ? substr($score['comment'], 0, $maxLength-4) . '...' : $score['comment'];

		$div_con = addslashes(sprintf($txt['arcade_when'], $score['time'], duration_format($score['duration'])));
		$currentScore = !empty($_SESSION['arcade_current_score_id']) && $score['id'] == $_SESSION['arcade_current_score_id'] ? '<span style="font-weight: 900;font-style: oblique 40deg;">' . $score['score'] . '</span>' : $score['score'];
		echo '
					<div class="', $score['own'] ? 'arcade_own_score' : 'arcade_none', '"', !empty($score['highlight']) ? ' style="clear: both;width: 98%;display: table-row;"' : ' style="clear: both;width: 98%;display: table-row;font-weight: bold;"', '>
						<div style="display: table-cell;vertical-align: middle;height: 1.5em;max-height: 1.5em;" class="arcade_none centertext">', $score['position'], '</div>
						<div style="display: table-cell;vertical-align: middle;height: 1.5em;max-height: 1.5em;" class="arcade_none">', $score['member']['link'], '</div>
						<div style="display: table-cell;width: 20em;padding-left: 0.4em;vertical-align: middle;height: 1.5em;max-height: 1.5em;" class="arcade_none">';

		if ($score['can_edit'] && empty($score['edit']) && empty($arcadeModSettings['arcadeDisableComments']))
			echo '
							<div onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 1); myformxyz(\'commentform3\', \'', $score['id'], '\');" id="comment', $score['id'], '" class="floatleft">', $score['comment'], '</div>
							<div onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 1); myformxyz(\'commentform3\', \'', $score['id'], '\');" id="edit', $score['id'], '" class="floatleft" style="display: none;">
								<input onkeydown="enterkey(event)" type="text" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" maxlength="50" />
								<input style="width: 0px;height: 0px;" id="okClick_' . $score['id'] . '" type="submit" onclick="myformxyz(\'commentform3\', \'', $score['id'], '\')" name="csave" value="', $txt['arcade_save'], '" />
							</div>';
		elseif ($score['can_edit'] && !empty($score['edit']) && empty($arcadeModSettings['arcadeDisableComments']))
		{
			echo '
							<input type="hidden" name="score" value="', $score['id'], '" />
							<input type="text" name="new_comment', $score['id'], '" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" maxlength="50" />
							<input id="okClick_' . $score['id'] . '" onclick="myformxyz(\'commentform3\', \'', $score['id'], '\')" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />';
		}
		else
			echo '			<div style="height: 1.5em;max-height: 1.5em;" class="floatleft">
								' . (empty($arcadeModSettings['arcadeDisableComments']) ? $score['comment'] : '') . '
							</div>';

		echo '
						</div>
						', str_repeat($context['arcade_empty_cell'], 2), '
						<div style="display: table-cell;vertical-align: middle;height: 1.5em;max-height: 1.5em;" class="centertext arcade_none">', $currentScore, '</div>';


		if ($context['arcade']['can_admin_arcade'])
			echo '
						<div style="display: table-cell;vertical-align: middle;height: 1.5em;max-height: 1.5em;" class="arcade_none centertext"><input type="checkbox" name="scores[]" value="', $score['id'], '" class="check" /></div>';

		echo '
					</div>
					<div style="display: table-row;" class="arcade_none">
						', (!$context['arcade']['can_admin_arcade'] ? str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 0.7em;', $context['arcade_empty_cell']), 5) : str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 0.7em;', $context['arcade_empty_cell']), 6)) . '
					</div>';
	}

	echo '
				</div>';

	if ($context['arcade']['can_admin_arcade'])
	{
		echo '
				<div style="display: block;width: 100%;padding-top: 2em;position: relative;text-align: right;">
					<div style="display: inline;text-align: right;width: 98%;">
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
		</form>
	</div>';
}

// Below game
function template_arcade_game_below()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings;

	echo '
	</div>
	<span style="padding-top: 1em;"><span>&nbsp;</span></span>
	<div class="pagesection" style="border: 0px;width: 100%;">
		<div class="align_left">';

	if (isset($context['page_index']))
		echo $txt['pages'], ': ', $context['page_index'];

	if (!empty($arcadeModSettings['topbottomEnable']))
		echo isset($context['page_index']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '';

	echo '</div>
	</div>
	<div id="arcadebox" style="display: none; position: absolute; left: 0px; top: 0px; width: 33%;">
		<div id="arcadebox_html" style="display: inline;"></div>
	</div>';

	echo '
		<script type="text/javascript">
			function arcadeReportScript()
			{
				var i = 0,reportClass = "", gameReports = [];
				gameReports = document.querySelectorAll("[class*=reportid_]");
				for(i=0;i<gameReports.length;i++)
				{
					var str = gameReports[i].className;
					var myId = str.match(/\breportid_[^\b]*?\b/gi).toString().replace(/[^0-9]/g, "");
					var myName = str.match(/\breportname_[^\b]*?\b/gi).toString();
					gameReports[i].id = "reportid_" + myId;
					gameReports[i].name = "reportid_" + myName;
					document.getElementById("reportid_" + myId).onclick = arcadeReportAjax.bind(this, [myId]);
				}
			}
			function arcadeReportAjax(gameid)
			{
				document.getElementById("reportid_" + gameid).removeAttribute("href");
				var gamename = document.getElementById("reportid_" + gameid).name.replace("reportname_", "").replace("reportid_", "").replace(/___/g, " ");
				var reason = prompt(\'' . $txt['pdl_report_reason_name'] . ' \' + gamename + \'' . '\r\n\r\n' . $txt['pdl_report_reason_input'] . '\');
				if (reason)
				{
					var url = "'. $scripturl . '?action=arcade;sa=report;game=" + gameid + ";sesc=" + "' . $context['session_id'] . '";
					var play_url = "'. $scripturl . '?action=arcade;sa=play;game=" + gameid + ";reload=" + Math.floor((Math.random() * 8999) + 1000);
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