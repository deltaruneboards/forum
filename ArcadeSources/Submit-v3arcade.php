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

function ArcadeVbPlay(&$game, &$session_info)
{
	global $scripturl, $context, $arcadeModSettings, $smcFunc, $settings;
	$context['html_headers'] .= '<link href="' . $settings['default_theme_url'] . '/css/arcade.css?rc4" rel="stylesheet" type="text/css" />';

}

function ArcadeVbXMLPlay(&$game, &$session_info)
{
	global $scripturl, $context, $arcadeModSettings, $smcFunc;

}

function ArcadeVbStart()
{
	global $scripturl, $context, $arcadeModSettings, $smcFunc;

	if (!($game = getGameInfo($_POST['gamename'], false, 0)))
		return false;

	$time = time();
	$gamerand = rand(1, 10);
	$lastid = rand(0, 234);

	$_SESSION['arcade_play_' . $game['id']] = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'start_time' => $time,
		'done' => false,
		'score' => 0,
		'end_time' => 0,
		'initbar' => $gamerand,
		'last_id' => $lastid,
	);
	$_SESSION['arcade_play_vb3g'][$lastid] = $game['id'];

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	echo '&connStatus=1&initbar=', $gamerand, '&gametime=', $time, '&lastid=', $lastid, '&result=OK';

	obExit(false);
}

function ArcadeVbPermRequest()
{
	global $scripturl, $context, $arcadeModSettings, $smcFunc, $txt;

	$score = isset($_POST['score']) ? floatval(preg_replace("/[^-0-9\.]/","", $_POST['score'])) : 0;
	$fakekey = isset($_POST['fakekey']) ? floatval(preg_replace("/[^-0-9\.]/","", $_POST['fakekey'])) : 0;
	$note = isset($_POST['note']) ? floatval(preg_replace("/[^-0-9\.]/","", $_POST['note'])) : 0;
	$gameid = isset($_POST['id']) ? floatval(preg_replace("/[^-0-9\.]/","", $_POST['id'])) : 0;
	$lastid =  isset($_POST['lastid']) ? floatval(preg_replace("/[^-0-9\.]/","", $_POST['lastid'])) : 0;

	$_SESSION['arcade_play_vb3g'][$gameid] = !empty($_SESSION['arcade_play_vb3g'][$gameid]) ? $_SESSION['arcade_play_vb3g'][$gameid] : 0;

	if (!($game = getGameInfo($_SESSION['arcade_play_vb3g'][$gameid], false, 0)))
	{
		// game name not known so just use the game id for reference
		if (empty($txt['arcade_submit_error_game_session']))
			loadLanguage('Arcade');

		$error = sprintf($txt['arcade_submit_error_game_session'],  $gameid);
		if (!empty($arcadeModSettings['arcade_log_scoreloop'])) {
			$err = arcade_html_entity_decode($error, 2, 2);
			log_error($err, 'debug');
		}

		return false;
	}

	$session = &$_SESSION['arcade_play_' . $game['id']];

	$check = $fakekey * ceil($score);
	$noteid = $check != 0 ? $note / $check : 0;

	if ($gameid != $session['last_id'] || $noteid != $gameid || $_POST['gametime'] != $session['start_time'])
	{
		ob_end_clean();
		if (!empty($arcadeModSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');
		else
			ob_start();

		echo '&validate=0';

		obExit(false);
	}

	$session['end_time'] = microtime_float();
	$session['score'] = $_POST['score'];

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	echo '&validate=1&microone=', microtime_float(), '&result=OK';

	obExit(false);
}

function ArcadeVbGetGame()
{
	global $scripturl, $context, $arcadeModSettings, $smcFunc;
	$_POST['game'] = isset($_POST['game']) ? $_POST['game'] : '';
	$id = !empty($_SESSION['current_game_id']) ? $_SESSION['current_game_id'] : 0;

	if (!empty($id))
	{
		$game = getGameInfo($_SESSION['arcade']['play_vb3g'][$id], false, 0);
	}
	elseif (!empty($_POST['game']))
	{
		$_SESSION['arcade']['play_vb3g'][$_POST['id']] = $_POST['game'];
		$game = getGameInfo($_POST['game'], false, 0);
	}
	else
		$game = getGameInfo($_SESSION['arcade']['play_vb3g'][$_POST['id']], false, 0);

	return $game;
}

function ArcadeVbSubmit(&$game, $session)
{
	global $scripturl, $context, $arcadeModSettings, $smcFunc;

	$session['end_time'] = !empty($session['end_time']) ? $session['end_time'] : 0;
	$diff = (microtime_float() - $session['end_time']);

	if ($diff > 4500 || !empty($session['ping']))
		return false;

	$session['ping'] = true;

	$cheating = CheatingCheck();

	unset($_SESSION['arcade_play_vb3g'][$_POST['id']]);

	return array(
		'cheating' => $cheating,
		'score' => $session['score'],
		'start_time' => $session['start_time'],
		'duration' => round($session['end_time'] - $session['start_time'], 0),
		'end_time' => round($session['end_time'], 0),
	);
}

function ArcadeVbHtml(&$game, $auto_start = true)
{
	global $txt, $context, $settings;

	$checkFull = isset($_REQUEST['full']) && $_REQUEST['full'] == 1 ? 1 : 0;
	$checkFull = $context['game']['type'] == 'fullscreen' ? 1 : $checkFull;
	$checkPop = isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1 ? 1 : 0;
	
	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/arcade_scripts/swfobject.js" defer="defer"></script>
	<div style="display: ' . (empty($checkFull) && empty($checkPop) ? 'flex' : 'inline') . ';align-items: center;justify-content: center;">
		<div id="gameplayer" style="margin: auto; width: ', $game['extra_data']['width'], 'px; height: ', $game['extra_data']['height'], 'px; ">
			', $txt['arcade_no_javascript'], '
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
		function getArcadeUrlParam5(name){
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
			var full = getArcadeUrlParam5("pop");
			var flashvars = {};
			var params = { scale: "exactFit" };
			var attributes = {};
			if (full == 1)
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