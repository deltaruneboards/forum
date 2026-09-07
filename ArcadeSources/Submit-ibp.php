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

function ArcadeIBPGetGame()
{
	$_POST['gname'] = !empty($_POST['gname']) ? $_POST['gname'] : false;

	return getGameInfo($_POST['gname'], false, 0);
}

function ArcadeIBPSubmit(&$game, $session_info)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc;

	if (isset($_POST['gscore']) && is_numeric($_POST['gscore']))
		$score = floatval($_POST['gscore']);
	elseif (isset($_POST['gscore']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['gscore']));
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

function ArcadeIBPPlay(&$game, &$session)
{

	// We store this session to check cheating later
	$session = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);
}

function ArcadeIBPXMLPlay(&$game, &$session)
{
	$session = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);

	return true;
}

// v2
function ArcadeIBP2GetGame()
{
	$_POST['gname'] = !empty($_POST['gname']) ? $_POST['gname'] : false;

	return getGameInfo($_POST['gname'], false, 0);
}

function ArcadeIBP2Submit(&$game, $session_info)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc;

	if (isset($_POST['gscore']) && is_numeric($_POST['gscore']))
		$score = floatval($_POST['gscore']);
	elseif (isset($_POST['gscore']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['gscore']));
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

function ArcadeIBP2Play(&$game, &$session)
{

	// We store this session to check cheating later
	$session = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);
}

function ArcadeIBP2XMLPlay(&$game, &$session)
{
	$session = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);

	return true;
}

// v3
function ArcadeIBP3GetGame()
{
	if (!empty($_SESSION['arcade']['ibp_game']))
		return getGameInfo($_SESSION['arcade']['ibp_game'], false, 0);

	return array();
}

function ArcadeIBP3Submit(&$game, $session_info)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc;

	list ($hash1, $hash2, $endTime) = isset($_SESSION['arcade']['ibp_verify']) ? $_SESSION['arcade']['ibp_verify'] : array(0, 0, 0);

	// No longer needed
	unset($_SESSION['arcade']['ibp_verify']);

	// How long it took? Must be more than 0 seconds and less than 7 seconds (same as in IPB arcade)
	$time_taken = microtime_float() - $endTime;

	if ($time_taken < 0 || $time_taken > 7)
		return false;

	// Was score even submitted
	if (isset($_POST['gscore']) && is_numeric($_POST['gscore']))
		$score = floatval($_POST['gscore']);
	elseif (isset($_POST['gscore']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['gscore']));
	else
		return false;

	// Check 'hash'
	if (($score * $hash1 ^ $hash2) != $_POST['enscore'])
		return false;

	$cheating = CheatingCheck();

	return array(
		'cheating' => $cheating,
		'score' => $score,
		'start_time' => $session_info['start_time'],
		'duration' => round($endTime - $session_info['start_time'], 0),
		'end_time' => round($endTime, 0),
	);
}

function ArcadeIBP3Play(&$game, &$session)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $settings;

	$context['html_headers'] .= '<link href="' . $settings['default_theme_url'] . '/css/arcade.css?rc5" rel="stylesheet" type="text/css" />';
	// We store this session to check cheating later
	$session = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);

	$_SESSION['arcade']['ibp_game'] = $game['internal_name'];
}

function ArcadeIBP3XMLPlay(&$game, &$session)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc;

	$session = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
	);

	$_SESSION['arcade']['ibp_game'] = $game['internal_name'];

	return true;
}

// v3.2
function ArcadeIBP32GetGame()
{
	$_POST['gname'] = !empty($_POST['gname']) ? $_POST['gname'] : false;
	return getGameInfo($_POST['gname'], false, 0);
}

function ArcadeIBP32Submit(&$game, $session_info)
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc;

	list ($hash1, $hash2, $endTime) = $_SESSION['arcade']['ibp_verify'];

	// No longer needed
	unset($_SESSION['arcade']['ibp_verify']);

	// How long it took? Must be more than 0 secods and less than 7 seconds (same as in IPB arcade)
	$time_taken = microtime_float() - $endTime;

	if ($time_taken < 0 || $time_taken > 7)
		return false;

	// Was score even submitted
	if (isset($_POST['gscore']) && is_numeric($_POST['gscore']))
		$score = floatval($_POST['gscore']);
	elseif (isset($_POST['gscore']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['gscore']));
	else
		return false;

	// Check 'hash'
	if (($score * $hash1 ^ $hash2) != $_POST['enscore'])
		return false;

	$cheating = CheatingCheck();

	return array(
		'cheating' => $cheating,
		'score' => $score,
		'start_time' => $session_info['start_time'],
		'duration' => round($endTime - $session_info['start_time'], 0),
		'end_time' => round($endTime, 0),
	);
}

function ArcadeVerifyIBP()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc;

	$randomchar = rand(1, 200);
	$randomchar2 = rand(1, 200);

	$_SESSION['arcade']['ibp_verify'] = array($randomchar, $randomchar2, microtime_float());

	// We output flash vars no need for anything that might output something before or after this
	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	echo '&randchar=', $randomchar, '&randchar2=', $randomchar2, '&savescore=1&blah=OK';

	obExit(false);
}

function ArcadeIBPHtml(&$game, $auto_start = true)
{
	global $txt, $context, $settings;

	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/arcade_scripts/swfobject.js?rc5" defer="defer"></script>
	<div style="display: flex;align-items: center;justify-content: center;">
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
		function getArcadeUrlParam1(name){
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
			var full = getArcadeUrlParam1("pop");
			var flashvars = {};
			var params = { scale: "exactFit" };
			var attributes = {};
			if (full == 1) {
				swfobject.embedSWF("' , $game['url']['flash'], '", so, window.innerWidth, window.innerHeight, 10, false, flashvars, params, attributes);
			}
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