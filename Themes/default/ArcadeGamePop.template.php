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

function arcadePopupTemplate()
{
	global $scripturl, $txt, $context, $settings, $boardurl, $arcadeModSettings, $user_info, $arcade_version;
	$context['show_pm_popup'] = !empty($context['show_pm_popup']) ? true : false;
	$jsInsert = file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcadeAdd.js') ? file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/arcadeAdd.js') : '';
	$arcadeModSettings['arcadeRandomIdVar'] = !empty($arcadeModSettings['arcadeRandomIdVar']) ? $arcadeModSettings['arcadeRandomIdVar'] : 'smfarcade' . time();
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$ruffleNeed = isset($_COOKIE['smfArcadeSetFlashCookie']) && stripos($_COOKIE['smfArcadeSetFlashCookie'], 'shockwave') === FALSE ? true : false;
	$ruffleEnable = !empty($arcadeModSettings['arcade_flash_emulator']) ? intval($arcadeModSettings['arcade_flash_emulator']) : 0;
	$romDetect = strpos($context['game']['submit_system'], 'rom')  !== false ? true : false;
	$currentArcadeAction = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (!empty($_SESSION['arcade_rom_initiate']) ? 'retro_arch' : 'arcade');

	echo '
<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>';

	echo '
   <head>
	<meta http-equiv="Content-Type" content="text/html; charset=',$context['character_set'],'" />
	<meta name="description" content="', $context['forum_name_html_safe'], '" />
	<meta name="keywords" content="', $context['game']['internal_name'], '" />', (!empty($_SESSION['arcade_isMobile']) ? '
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0" />
	<style>
	div {
		border: 0px;
		border-bottom: 1px solid #eee;
		position: relative;
		min-width: 97vw;
		max-width: 97vw;
		width: 97vw;
		margin-left: auto;
		margin-right: auto;
	}
	</style>' : ''), '
    <title>', $context['forum_name_html_safe'], '</title>', ($context['arcade_smf_version'] == 'v2.1' ? '
	<script type="text/javascript" src="' . $context['arcade_jquery_file'] . '?' . $suffixVersion . '"></script>' : ''), '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?' . $suffixVersion . '"></script>
	<script type="text/javascript">
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['default_images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_charset = "', $context['character_set'], '";
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
		var stopit;
		var smf_quote_expand = false;
		var arcadeDetectGameAction = "' . ($currentArcadeAction) . '";
		localStorage.setItem("arcadeSmfIdVar", "' . $arcadeModSettings['arcadeRandomIdVar'] . '");
	</script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade.js?' . $suffixVersion . '"></script>';

	if (strpos($context['game']['submit_system'], 'html5')  === false && $ruffleEnable == 1 && $ruffleNeed && empty($romDetect)) {
			echo '
	<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
	}
	elseif (strpos($context['game']['submit_system'], 'html5')  === false && file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/ruffle.js') && $ruffleNeed && empty($romDetect)) {
		echo '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion . '"></script>';
	}
	elseif (strpos($context['game']['submit_system'], 'html5')  === false && $ruffleNeed && empty($ruffleEnable) && empty($romDetect)) {
			echo '
	<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
	}

	if ($context['game']['submit_system'] == 'html53')
		echo '
	<script type="text/javascript">
		var arcadeCurrentGameId = "' . $context['game']['id'] . '";
		var arcadeCurrentGameToken = "' . $_SESSION['arcade_html5_token'][1] . '";
	</script>' . (!empty($arcadeModSettings['arcade_jQuery_JV']) && file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcade-jquery.js?' . $suffixVersion . '') ? '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-jquery.js?v' . $suffixVersion . '"></script>' : '') . (!empty($arcadeModSettings['arcade_phpbb3_support_score']) ? '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-html53.js?' . $suffixVersion . '7"></script>' : '');

	echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css?' . $suffixVersion . '" />
    <style type="text/css">
         body
         {
            padding: 0px 0px 0px 0px;
			border: 0px;
			margin: 0px;
			background-size: cover;
			width: 100vw;
			height: 100vh;
			font-size: x-small;
         }
		 .bodyblock
		 {
			 background-color: transparent;
		 }
		.gamepop1
		 {
			overflow: hidden;
			min-height: 100vh;
			min-width: 100vw;
			border: 0px;
			margin: 0px;
			width: ', ($context['game']['width']), 'px;
			height: ', ($context['game']['height']), 'px;
		 }
		.gamepop2
		 {
			display: inline-block;
			overflow: hidden;
			border: 0px;
			min-width: 100vw;
			min-height: 100vh;
			width: ', ($context['game']['width']), 'px;
			height: ', ($context['game']['height']), 'px;
		 }
		.gamepop3
		 {
			overflow: hidden;
			min-height: 100vh;
			min-width: 100vw;
			border: 0px;
			width: ', ($context['game']['width']), 'px;
			height: ', ($context['game']['height']), 'px;
		 }
		.padbottom
		 {
			 padding-bottom: 40px;
		 }
		 .infotext
		 {
			text-align: center;
		 }
		.bodyoverflow
		{
			overflow: hidden;
		}
      </style>
   </head>';
	$check_block = !empty($_REQUEST['block']) ? (int)$_REQUEST['block'] : 0;
	if ($check_block == 1)
	{
		echo '
<!--[if IE]>
    <body class="bodyoverflow" id="html_page1">
		<div style="text-align:center;overflow: hidden;">
<![endif]-->
<!--[if !IE]><!-->
	<body class="bodyblock bodyoverflow" id="html_page1">
		<div style="text-align:center;overflow: hidden;">
<!--<![endif]-->';
	}
	else
	{
		echo '
	<body class="bodyoverflow" id="html_page1">
		<div style="text-align:center;overflow: hidden;width: ', $context['game']['width'], 'px;height: ', $context['game']['height'], 'px">';
	}

	echo '
			<div style="position: absolute;left: 0px;right: 0px;bottom: 0.5px;top: 0px;overflow: hidden;width: 100vw;height: 100vh;margin 0 auto;">';

	$highScore = isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'highscore' ? true : false;
	if (strpos($context['game']['submit_system'], 'html5') !== false)
	{
		if (!empty($context['game']['description']) || !empty($context['game']['help']))
			echo '
				<div id="gameInfoDiv" class="floatleft" style="display: ' . (!empty($highscore) ? 'inline' : 'none') . ';position: absolute;padding: 0.2em 0em 0em 0.9em;"><img class="game_info_icon" title="', $txt['arcade_click_info_title'], '" style="position: absolute;top: -0.3em;max-width: 21px;max-height: 21px;" onclick="gameinfoClick();" alt="', $txt['arcade_click_info'], '" src="', $settings['default_images_url'], '/arc_icons/game_info.png" />
					<div id="gameInfoDivData" class="windowbg" style="display: none;padding: 1em 1.02em;border-radius: 8px;position: absolute;width: 20em;font-size: 0.9em;top: 2em;">
					', (!empty($context['game']['description']) ? $txt['arcade_post_description'] . '<br />
					' . $context['game']['description'] . '<br /><br />' : ''), '
					', (!empty($context['game']['help']) ? $txt['arcade_post_help'] . '<br />
					' . $context['game']['help'] : ''),  '
					</div>
				</div>';

		echo '
				<form id="gameForm" action="', $scripturl, '?action=arcade;game=', $context['game']['id'], ';sa=' . $context['game']['submit_system'] . 'Game;pop=1;" method="post">
					<input type="hidden" id="score" name="score" />
					<input type="hidden" id="game" name="game" value="', $context['game']['id'], '" />
					<input type="hidden" id="smfgametime" name="smfgametime" value="', time(), '" />
					<input type="hidden" id="gameexit" name="gameexit" value="0" />
					<input type="hidden" id="noSmfScore" name="noSmfScore" value="', $txt['arcade_noSmfScore'], '" />
					<input type="hidden" id="gameSmfToken" name="gameSmfToken" value="', $_SESSION['arcade_html5_token'][1], '" />
					<input type="hidden" id="html5" name="html5" value="1" />' . ($context['game']['submit_system'] == 'html52' || $context['game']['submit_system'] == 'html53' ? '
					<input type="hidden" id="html52" name="html52" value="1" />' : '') . ($user_info['is_guest'] && empty($_SESSION['playerName']) ? '
					<input type="hidden" id="guestname" name="guestname" value="1" />' : '
					<input type="hidden" id="guestname" name="guestname" value="0" />') . '
					<input type="hidden" id="smfGameSaveUrl" name="smfGameSaveUrl" value="', $settings['default_theme_url'], '/arcade_scripts/arcade-html5-save.js?' . $suffixVersion . '" />
					<input type="hidden" id="html5smfGameUrl" name="html5smfGameUrl" value="' . $scripturl . '?action=arcade;game=' . $context['game']['id'] . (!empty($reload) ? ';reload=' . $reload : '') . ';#playgame" />
					<input type="hidden" id="gameSmfFullscreen" name="gameSmfFullscreen" value="0" />
					<input type="hidden" id="popup" name="popup" value="1" />
					<input type="hidden" id="game_name" name="game_name" value="', $context['game']['internal_name'], '" />
				</form>
				<div class="gamepop1" id="gamearea1">
					<div class="gamepop2" id="gameObjDiv" style="position:relative; left: 0px; top: 0px;">
						<iframe onerror="reloadArcadeGameContainer(1)" onload="arcadeGameOnloadEvent()" style="width: 100vw;height: 100vh;" id="gameObj" class="gamepop3" src="' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '/' . $context['game']['file'] . '"></iframe>
					</div>
					', !$context['arcade']['can_submit'] ? '<br /><div class="infotext"><strong>' . $txt['arcade_cannot_save'] . '</strong></div>' : '', '
					<div class="padbottom"><span style="display: none;">&nbsp;</span></div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			localStorage.setItem("reloadArcadeGame", 0);
			function arcadeGameOnloadEvent() {
				loadSmfExtraGameData();
				if (' . (int)$context['game']['js_insertion'] . ' == 1 && typeof submitSmfArcadeScoreCode !== "function")
					setTimeout(reloadArcadeGameContainer(), 50);
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
				var arcadeScript = document.createElement("script");
				arcadeScript.src = "' . $settings['default_theme_url'] . '/arcade_scripts/arcadeAdd.js?' . $suffixVersion . '";
				var arcadeData = arcadeObject.contentDocument || arcadeObject.contentWindow.document;
				if (arcadeData && arcadeData.getElementsByTagName("BODY")) {
					' . (empty($context['game']['js_insertion']) ? 'if (!arcadeData.getElementById("block_game"))' : 'if (' . (int)$context['game']['js_insertion'] . ' == 1)') . ' {
						' . $jsInsert . '
					}
				}
			}
			function gameStartOnload() {
				var arcade_iframeDoc = document.getElementById("gameObj");
				var arcadeDivCreate = document.createElement("DIV");
				arcadeDivCreate.id = "arcade_hidemenowx";
				arcadeDivCreate.style = "display: block;margin: 0 auto;bottom: 50vw;position: absolute;";
				arcadeDivCreate.onclick = "arcade_hidemenow()";
				arcade_iframeDoc.contentWindow.document.getElementsByTagName("BODY")[0].appendChild(arcadeDivCreate);
				arcade_autoclickme();
			}
			function arcade_autoclickme() {
				setTimeout(() => {var hidemearcvar = document.getElementById("gameObj").contentWindow.document.getElementById("arcade_hidemenowx"); if(hidemearcvar){hidemearcvar.click();}}, 2000);
			}
			function arcade_hidemenow() {
				document.getElementById("gameObj").contentWindow.document.getElementById("hidemenowx").style.display = "none";
			}
			if (window.addEventListener)
				window.addEventListener("load", function (){
					gameStartOnload();
					return false;
				});
			else
				window.attachEvent("onload", function (){
					gameStartOnload();
					return false;
				});
		</script>';
	}
	else
	{
		if (!empty($context['game']['description']) || !empty($context['game']['help']))
			echo '
				<div id="gameInfoDiv" class="floatleft" style="display: ' . (!empty($highscore) ? 'inline' : 'none') . ';position: absolute;padding: 0.2em 0em 0em 0.9em;"><img class="game_info_icon" title="', $txt['arcade_click_info_title'], '" style="position: absolute;top: -0.3em;max-width: 21px;max-height: 21px;" onclick="gameinfoClick();" alt="', $txt['arcade_click_info'], '" src="', $settings['default_images_url'], '/arc_icons/game_info.png" />
					<div id="gameInfoDivData" class="windowbg" style="display: none;padding: 1em 1.02em;border-radius: 8px;position: absolute;width: 20em;font-size: 0.9em;top: 2em;">
					', (!empty($context['game']['description']) ? $txt['arcade_post_description'] . '<br />
					' . $context['game']['description'] . '<br /><br />' : ''), '
					', (!empty($context['game']['help']) ? $txt['arcade_post_help'] . '<br />
					' . $context['game']['help'] : ''),  '
					</div>
				</div>';

		echo '
				<div id="gamearea1" class="gamepop1">
					', $context['game']['html']($context['game'], true), '
					', !$context['arcade']['can_submit'] ? '<br /><div class="infotext"><strong>' . $txt['arcade_cannot_save'] . '</strong></div>' : '', '
					<div class="padbottom"><span style="display: none;"> </span></div>
				</div>
			</div>
		</div>';
	}

	echo '
		<script type="text/javascript">
			if (window.addEventListener) {
				window.addEventListener("load", function (){
					smfArcadeGameDims();
					return false;
				});
				window.addEventListener("keydown", arcadeEscKey, false);
			}
			else {
				window.attachEvent("onload", function (){
					smfArcadeGameDims();
					return false;
				});
				window.attachEvent("keydown", arcadeEscKey);
			}
			function arcadeOnResize() {
				window.height = window.innerHeight;
				window.width = window.innerWidth;
			}
			function smfArcadeGameDims() {' . (empty($user_info['is_guest']) && !empty($user_info['name']) ? '
				localStorage.setItem("arcade_game_user_name", "' . $user_info['name'] . '");' : '
				localStorage.setItem("arcade_game_user_name", "' . $txt['guest_title'] . '");') . '
				sessionStorage.removeItem("scoreLoop_' . $_SESSION['arcade_html5_token'][1] . '");
				var resizeMe = arcadeOnResize();

				var docbody = parent.document.getElementsByTagName("BODY")[0];
				docbody.style.height = "100vh";
				docbody.style.width = "100vw";
				self.focus();
				resizeArcadeWinTo("gamearea1");
				var divelement = document.getElementById("gameObjDiv");
				if (divelement) {
					divelement.style.minWidth = "100vw";
					divelement.style.minHeight = "100vh";
					divelement.style.maxWidth = "100vw";
					divelement.style.maxHeight = "100vh";
					divelement.style.overflow = "hidden";
				}
				document.body.style.width = "100vw";
				document.body.style.height = "100vh";' . (!empty($context['game']['description']) || !empty($context['game']['help']) ? '
				var gameInfoDivDataClick = document.getElementById("gameInfoDivData");
				if (gameInfoDivDataClick) {
					gameInfoDivDataClick.onclick = function() {
						document.getElementById("gameInfoDivData").style.display = "none";
					};
				}' : '') . '
			}
			function escGameSmf() {
				window.location = "' . $scripturl . '?action=arcade;sa=highscore;game=' . $context['game']['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
			}' . (!empty($context['game']['description']) || !empty($context['game']['help']) ? '
			function gameinfoClick() {
					var gameInfoBox = document.getElementById("gameInfoDivData");
					if (gameInfoBox) {
						if (gameInfoBox.style.display === "block") {
							gameInfoBox.style.display = "none";
						} else {
							gameInfoBox.style.display = "block";
						}
					}
			}' : '') . '
			function arcadeEscKey(event)
			{
				var code = (event.keyCode ? event.keyCode : event.which);
				if(code == 27) {
					event.preventDefault();
					escGameSmf();
					return true;
				}
				return false;
			}
		</script>
		<div style="position: absolute;bottom: 0px;text-align: center;width: 100vw;z-index: 0;">
			' . $txt['pdl_arcade_copyright'] . '
		</div>
	</body>
</html>';
}

function arcadePopHighscoreTemplate()
{
	global $scripturl, $txt, $context, $settings, $boardurl, $arcadeModSettings;

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$currentArcadeAction = isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 'retro_arch' : (!empty($_SESSION['arcade_rom_initiate']) ? 'retro_arch' : 'arcade');

	if (empty($context['arcade']['buttons']))
		$context['arcade']['buttons'] = array();

	if ($context['arcade_smf_version'] == 'v2.1')
		echo '
<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>';
	else
		echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>';

	echo '
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=',$context['character_set'],'" />
		<meta name="description" content="', $context['forum_name_html_safe'], '" />
		<meta name="keywords" content="', $context['game']['internal_name'], '" />', (!empty($_SESSION['arcade_isMobile']) ? '
		<meta name="viewport" content="width=device-width, initial-scale=1.0">' : ''), '
		<title>', $context['forum_name_html_safe'], '</title>
		<script type="text/javascript">sessionStorage.clear();</script>
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?' . $suffixVersion . '"></script>
		<script type="text/javascript" src="', $settings['theme_url'], '/scripts/theme.js?' . $suffixVersion . '"></script>
		<script type="text/javascript">
			var smf_theme_url = "', $settings['theme_url'], '";
			var smf_default_theme_url = "', $settings['default_theme_url'], '";
			var smf_images_url = "', $settings['default_images_url'], '";
			var smf_scripturl = "', $scripturl, '";
			var smf_charset = "', $context['character_set'], '";
			var ajax_notification_text = "', $txt['ajax_in_progress'], '";
			var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
			var stopit;
			var arcadeDetectGameAction = ' . ($currentArcadeAction) . ';
		</script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade.js?' . $suffixVersion . '"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-skin-a.js?' . $suffixVersion . '"></script>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css?' . $suffixVersion . '" />
		<style type="text/css">
			body{
				padding: 0px 0px 0px 0px; background: transparent;
				overflow: hidden;
				width: 100vw;
				min-width: 100vw;
				border: 0px;
				margin: 0px;
				background-size: cover;
				height: 100vh;
			}
			body, td, th, .normaltext{
				font-size: x-small;
			}
			.smalltext{
				font-size: xx-small;
			}
		</style>
	</head>   ';
	$check_block = !empty($_REQUEST['block']) ? (int) $_REQUEST['block'] : 0;
	if ($check_block == 1)
	{
		echo '
<!--[if IE]>
	<body class="windowbg" id="html_page1">
		<div style="text-align:center;overflow: hidden;">
<![endif]-->
<!--[if !IE]><!-->
	<body style="background-color: transparent;" id="html_page1">
      <div id="scoresdiv" style="background-color: transparent;text-align:center;overflow: hidden;">
<!--<![endif]-->';
	}
	else
	{
		echo '
	<body class="windowbg" id="html_page1">
		<div id="scoresdiv" style="text-align:center;overflow: hidden;">';
	}

	if (isset($context['arcade']['submit']))
	{
		if ($context['arcade']['submit'] == 'newscore') // Was score submitted
		{
			$score = &$context['arcade']['new_score'];
			echo '
		<div class="cat_bar" style="clear: both;position: relative;">
			<h3 class="catbg centertext">
				<span class="centertext" style="clear: left;vertical-align: middle;">', $txt['arcade_submit_score'], '</span>
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span>&nbsp;</span></span>
		<div style="padding: 0 0.5em;overflow: hidden;">';

			// No permission to save
			if (!$score['saved'])
				echo '
			<div>
				', $txt[$score['error']], '<br />
				<strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '
			</div>';

			else
			{
				echo '
			<div>
				', $txt['arcade_score_saved'], '<br />
				<strong>', $txt['arcade_score'], ':</strong> ', $score['score'], '<br />';

				if ($score['is_new_champion'])
					echo '
				', $txt['arcade_you_are_now_champion'], '<br />';

				elseif ($score['is_personal_best'])
					echo '
				', $txt['arcade_this_is_your_best'], '<br />';

				if ($score['can_comment'])
					echo '
			</div>
			<div>
				<form name="commentform1" id="commentform1" action="', $scripturl, '?action=arcade;game=', $context['game']['id'], ';score=',  $score['id'], ';sa=highscore;pop=1;reload=' . mt_rand(1, 9999) . '" method="post">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="mynewscoreid" value="', $score['id'], '" />
					<input type="text" id="new_comment', $score['id'], '" name="new_comment', $score['id'], '" maxlength="50" size="30" />
					<input class="button_submit" onclick="myformxyz(\'commentform1\', ', $score['id'], ')" type="submit" name="csave" value="', $txt['arcade_save'], '" />
				</form>
			</div>';
			}

			echo '
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div>
	<br />';
		}
		elseif ($context['arcade']['submit'] == 'askname')
		{
			echo '
	<div style="text-align:left;">
		<h3 class="catbg">
			<span style="clear: both;float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
			<span>', $txt['arcade_submit_score'], '</span>
		</h3>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span>&nbsp;</span></span>
		<div style="padding: 0 0.5em">
			<form name="commentform2" id="commentform2" action="', $scripturl, '?action=arcade;sa=save" method="post" onsubmit="myformxyz(\'commentform2\'), 0">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="text" id="name" name="name" maxlength="20" />
				<input class="button_submit" onclick="myformxyz(\'commentform2\'), -1" type="submit" value="', $txt['arcade_save'], '" />
			</form>
		</div>
	</div><br />';
		}
	}
	echo '
	<div class="pagesection">
		<div class="align_left">', $txt['pages'], ': ', $context['page_index'], !empty($arcadeModSettings['topbottomEnable']) ? $context['menu_separator'] . '&nbsp;&nbsp;<a href="#bot"><b>' . $txt['go_down'] . '</b></a>' : '', '</div>
	</div>
	<form name="commentform3" id="commentform3" action="', $scripturl, '?action=arcade;sa=highscore;pop=1;reload=' . mt_rand(1, 9999) . '" method="post">
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="game" value="', $context['game']['id'], '" />
		<div style="text-align:left;">
			<h3 class="catbg">
				<span style="clear: both;float:left;border:0px;background: url(',$settings['actual_theme_url'],'/images/theme/main_block.png) no-repeat 0% -160px;">&nbsp;</span>
				<span>', $txt['arcade_highscores'], '</span>
				<span class="floatright">', '<a onclick="replaygame()" href="Javascript:void(0)">',$txt['arcade_replay'] ,'</a>' , '</span>
			</h3>
		</div>
		<div class="score_table" style="width: 100%;height: 100%;overflow: auto;">
			<div cellspacing="0" class="table_grid" style="display: table;width: 100%;height: 100%;">
					<div style="display: table-row;">';

	/* Is there games? */
	if (!empty($context['arcade']['scores']))
	{
			echo '
						<div scope="col" class="smalltext first_th" style="display: table-cell;width: 5px;border-bottom: 1px double;height: 1.1em;max-height: 1.1em;">', $txt['arcade_position'], '</div>
						<div style="display: table-cell;border-bottom: 1px double;padding-left: 1.0em;height: 1.1em;max-height: 1.1em;" scope="col" class="smalltext">', $txt['arcade_member'], '</div>
						<div style="display: table-cell;border-bottom: 1px double;height: 1.1em;padding-left: 1.0em;height: 1.1em;max-height: 1.1em;" scope="col" class="smalltext"> ', $txt['arcade_comment'], '</div>
						', (!$context['arcade']['can_admin_arcade'] ? $context['arcade_empty_title_cell'] : str_repeat($context['arcade_empty_title_cell'], 2)) . '
						<div style="display: table-cell;border-bottom: 1px double;text-align: center;padding-left: 1.0em;height: 1.1em;max-height: 1.1em;" scope="col" class="smalltext', !$context['arcade']['can_admin_arcade'] ? ' last_th' : '', '">', $txt['arcade_score'], '</div>';

		if ($context['arcade']['can_admin_arcade'])
			echo '
						<div scope="col" class="smalltext last_th" align="center" style="display: table-cell;width: 15px;height: 1.1em;max-height: 1.1em;"><input type="checkbox" onclick="invertAll(this, this.form, \'scores[]\');" class="check" /></div>';
	}
	else
	{
		echo '
						<div style="display: table-cell;width: 8%;border-bottom: 1px double;height: 1.1em;max-height: 1.1em;" scope="col" class="smalltext first_th">&nbsp;</div>
						<div style="display: table-cell;border-bottom: 1px double;height: 1.1em;max-height: 1.1em;" class="smalltext"><strong>', $txt['arcade_no_scores'], '</strong></div>
						', (!$context['arcade']['can_admin_arcade'] ? $context['arcade_empty_cell'] : str_repeat($context['arcade_empty_cell'], 2)) . '
						<div scope="col" class="smalltext last_th" style="display: table-cell;width: 8%;border-bottom: 1px double;height: 1.1em;max-height: 1.1em;">&nbsp;</div>';
	}

	echo '
					</div>
					<div style="display: table-row;" class="windowbg2">
						', (!$context['arcade']['can_admin_arcade'] ? str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 1em;', $context['arcade_empty_cell']), 5) : str_repeat(str_replace('display: table-cell;', 'display: table-cell;padding-bottom: 1em;', $context['arcade_empty_cell']), 6)) . '
					</div>';

	$edit_button = '<span style="width: 16px;height: 16px;display: inline-block;background: url(' . $settings['default_images_url'] . '/arc_icons/arcade_edit.gif) no-repeat;vertical-align: middle;"><span style="display: none;">&nbsp;</span></span>';
	foreach ($context['arcade']['scores'] as $score)
	{
		if (empty($score['time']) || empty($score['position']))
			continue;

		$div_con = addslashes(sprintf($txt['arcade_when'], $score['time'], duration_format($score['duration'])));
		$currentScore = !empty($_SESSION['arcade_current_score_id']) && $score['id'] == $_SESSION['arcade_current_score_id'] ? '<span style="font-weight: 900;font-style: oblique 40deg;">' . $score['score'] . '</span>' : $score['score'];
		echo '
					<div class="', $score['own'] ? 'windowbg3' : 'windowbg', '"', !empty($score['highlight']) ? ' style="display: table-row;font-weight: bold;"' : ' style="display: table-row;"', '>
						<div style="display: table-cell;height: 1.1em;max-height: 1.1em;" class="windowbg2 centertext">', $score['position'], '</div>
						<div style="display: table-cell;padding-left: 1.0em;height: 1.1em;max-height: 1.1em;">', $score['member']['link'], '</div>
						<div style="display: table-cell;width: 300px;padding-left: 1.0em;height: 1.1em;max-height: 1.1em;" class="windowbg2">';

		if ($score['can_edit'] && empty($score['edit']) && empty($arcadeModSettings['arcadeDisableComments']))
			echo '
							<div id="comment', $score['id'], '" class="floatleft">', $score['comment'], '</div>
							<div id="edit', $score['id'], '" class="floatleft" style="display: none;">
								<input onkeydown="enterkey(event, ', $score['id'], ')" type="text" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" maxlength="50" />
								<input id="okClick_' . $score['id'] . '" type="hidden" onclick="myformxyz(\'commentform3\', \'', $score['id'], '\');" name="csave" value="', $txt['arcade_save'], '" />
							</div>
							<p style="text-align: right;"><a style="height: 1.1em;;max-height: 1.1em;" id="editlink', $score['id'], '" onclick="arcadeCommentEdit(', $score['id'], ', ', $context['game']['id'], ', 0); myformxyz(\'commentform3\', \'', $score['id'], '\');" href="', $scripturl, '?action=arcade;pop=1;sa=highscore;game=', $context['game']['id'], ';edit;score=', $score['id'], ';reload=' . mt_rand(1, 9999) . '" class="floatright">', $edit_button, '</a></p>';
		elseif ($score['can_edit'] && !empty($score['edit']) && empty($arcadeModSettings['arcadeDisableComments']))
		{
			echo '
							<input type="hidden" name="score" value="', $score['id'], '" />
							<input type="text" name="new_comment', $score['id'], '" id="c', $score['id'], '" value="', $score['raw_comment'], '" maxlength="50" />
							<input id="okClick_' . $score['id'] . '" onclick="myformxyz(\'commentform3\', 0);" class="button_submit" type="submit" name="csave" value="', $txt['arcade_save'], '" />';
		}
		else
			echo '			<div style="height: 1.1em;max-height: 1.1em;" class="floatleft">
								' . (empty($arcadeModSettings['arcadeDisableComments']) ? $score['comment'] : '') . '
							</div>';

		echo '
						</div>
						<div style="display: table-cell;padding-left: 1.0em;height: 1.1em;max-height: 1.1em;" class="centertext">', $currentScore, '</div>';


		if ($context['arcade']['can_admin_arcade'])
			echo '
						<div style="display: table-cell;height: 1.1em;max-height: 1.1em;" class="windowbg2 centertext">
							<input type="checkbox" name="scores[]" value="', $score['id'], '" class="check" />
						</div>';

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
		</form>
	</div>
	<script type="text/javascript">
		if (window.addEventListener)
			window.addEventListener("load", function (){
				smfArcadeGameDims2();
				return false;
			});
		else
			window.attachEvent("onload", function (){
				smfArcadeGameDims2();
				return false;
			});

		function smfArcadeGameDims2()
		{
			var width = document.getElementById("scoresdiv").offsetWidth;
			var height = document.getElementById("scoresdiv").offsetHeight;
			window.resizeTo(width,height);
			window.innerHeight = height;
			self.focus();
			setTimeout(function(){ smfArcadeGameScoresDiv(); }, 3000);
		}
		function smfArcadeGameScoresDiv()
		{
			var width = window.outerWidth || window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
			var height = document.getElementById("scoresdiv").offsetHeight;
			window.resizeTo(width,height+40);
			window.innerHeight = height+130;
			self.focus();
		}
		function replaygame()
		{
			window.location.href = "' . $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';pop=1";
			window.resizeTo(' . ($context['game']['width']) . ',' . ($context['game']['height']) . ');
			self.focus();
		}
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
						if (document.getElementById("new_comment" + myscore) && arcadeDisableComments == 0)
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
		function enterkey(event, myscore)
		{
			var code = (event.keyCode ? event.keyCode : event.which);
			if(code == 13) {
				var newercomment = document.getElementById("c" + myscore).value;
				document.getElementById("c" + myscore).value = newercomment;
				document.getElementById("commentform3").submit();
				return true;
			}
			return false;
		}
	</script>
   </body>
</html>';
	die();
}
?>