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

/*
	!!!
*/

// Get Game
function ArcadeV1GetGame()
{
	//if (!isset($_SESSION['arcade_v1game'][$_POST['game']]))
	//	return false;

	return GetGameInfo($_POST['game'], false, 0);
}

// Get Score
function ArcadeV1Submit(&$game, $session_info)
{
	if (isset($_POST['score']) && is_numeric($_POST['score']))
		$score = floatval($_POST['score']);
	elseif (isset($_POST['score']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['score']));
	else
		return false;

	$cheating = CheatingCheck();

	return array(
		'cheating' => $cheating,
		'score' => $score,
		'start_time' => $session_info['start_time'],
		'duration' => time() - $session_info['start_time'],
		'end_time' => time(),
	);
}

function ArcadeV1Play(&$game, &$session_info)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $settings;

	$context['html_headers'] .= '<link href="' . $settings['default_theme_url'] . '/css/arcade.css?rc4" rel="stylesheet" type="text/css" />';

	// We store this session to check cheating later
	$session_info = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);
}

function ArcadeV1XMLPlay(&$game, &$session_info)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc;

	// We store this session to check cheating later
	$session_info = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);

	return true;
}

function ArcadeV1Html(&$game, $auto_start = true)
{
	global $txt, $context, $settings;

	$checkFull = isset($_REQUEST['full']) && $_REQUEST['full'] == 1 ? 1 : 0;
	$checkFull = $context['game']['type'] == 'fullscreen' ? 1 : $checkFull;
	$checkPop = isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1 ? 1 : 0;
	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/arcade_scripts/swfobject.js?rc5" defer="defer"></script>
	<div style="display: ' . (empty($checkFull) && empty($checkPop) ? 'flex' : 'inline') . ';align-items: center;justify-content: center;">
		<div id="gameplayer" style="overflow: hidden;padding: 0px;border: 0px;margin: 0px; width: ', $game['extra_data']['width'], 'px; height: ', $game['extra_data']['height'], 'px;">
			<div class="infotext">', $txt['arcade_no_javascript'], '</div>
		</div>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		var arcadeScroll = document.getElementById("playgame");
		if (arcadeScroll != null)
			arcadeScroll.scrollIntoView();
		var play_url = smf_scripturl + "?action=arcade;sa=play;xml";
		var running = false;

		function arcadeRestart()
		{
			running = false;
			setInnerHTML(document.getElementById("gameplayer"), "', addslashes($txt['arcade_please_wait']), '");
			var i, x = new Array();
			x[0] = "game=', $game['id'] . '";
			x[1] = "', $context['session_var'], '=', $context['session_id'], '";
			arcadeAjaxSend(play_url, x.join("&"), ArcadeStart);
			return false;
		}
		function getArcadeUrlParam3(name){
			var qs = (function(a) {
				if (a == "")
					return {};
				var b = {};
				for (var i = 0; i < a.length; ++i)
				{
					var p=a[i].split("=", 2);
					if (p.length == 1)
						b[p[0]] = "";
					else
						b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
				}
				return b;
			})(window.location.search.substr(1).split(";"));
			return qs[name] !== "undewfined" ? qs[name] : "";
		}
		function ArcadeStart()
		{
			if (running)
				return;

			running = true;

			setInnerHTML(document.getElementById("gameplayer"), "', addslashes($txt['arcade_no_flash']), '");
			var so = document.getElementById("gameplayer");
			var full = getArcadeUrlParam3("pop");
			var flashvars = {};
			var params = { scale: "exactFit" };
			var attributes = {};
			if (full == 1 || ' . $checkFull . ' == 1)
				swfobject.embedSWF("' , $game['url']['flash'], '", so, "100%", "100%", 10, false, flashvars, params, attributes);
			else
				swfobject.embedSWF("' , $game['url']['flash'], '", so, ', $game['extra_data']['width'], ', ', $game['extra_data']['height'], ', 10);

			return true;
		}

		', $auto_start ? '
		if (window.addEventListener)
			window.addEventListener("load", arcadeRestart);
		else
			window.attachEvent("onload", arcadeRestart);' : '', '
	// ]]></script>';
}

?>