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

function ArcadeV2GetGame()
{
	global $scripturl, $txt, $db_prefix, $context, $arcadeModSettings, $smcFunc;

	return getGameInfo($_POST['game'], false, 0);
}

function ArcadeV2Submit(&$game, $session)
{
	global $scripturl, $txt, $db_prefix, $context, $arcadeModSettings, $smcFunc;

	checkSession('post');

	// You didn't cheat unless you prove it
	$cheating = false;

	$maxTries = $session['maxTries'];
	$reverse = $game['score_type'] == 2;

	$best = array();
	$isBest = false;
	$tries = 1;

	foreach ($session['scores'] as $id => $score)
	{
		if (!$isBest || (!$reverse && $score['score'] > $best['score']) || ($reverse && $score['score'] < $best['score']))
		{
			$best = $score;
			$isBest = true;
		}

		// If maxTries have been changed it is cheating probably?
		if ($score['maxTries'] != $maxTries)
			$cheating = 'max_tries';

		// You can't play more times than you are allowed to
		if (!empty($maxTries) && $score['try'] > $maxTries)
			$cheating = 'too_much_tries';

		// Yes tries should be here always
		if ($tries != $score['try'])
			$cheating = 'invalid_try';

		$tries++;
	}

	if (empty($best))
		return false;

	return array(
		'cheating' => $cheating,
		'score' => $best['score'],
		'start_time' => $best['startTime'],
		'end_time' => $best['endTime'],
		'duration' => round($best['endTime'] - $best['startTime']),
		'hash' => $best['hash'],
	);
}

function ArcadeV2Play(&$game, &$session_info)
{
	global $scripturl, $txt, $db_prefix, $context, $arcadeModSettings, $smcFunc, $settings;
	$context['html_headers'] .= '<link href="' . $settings['default_theme_url'] . '/css/arcade.css?rc4" rel="stylesheet" type="text/css" />';
}

function ArcadeV2XMLPlay(&$game, &$session_info)
{
	global $scripturl, $txt, $db_prefix, $context, $arcadeModSettings, $smcFunc;

	return true;
}

function ArcadeV2Html(&$game, $auto_start = true)
{
	global $scripturl, $txt, $context, $settings;

	$checkFull = isset($_REQUEST['full']) && $_REQUEST['full'] == 1 ? 1 : 0;
	$checkFull = $context['game']['type'] == 'fullscreen' ? 1 : $checkFull;
	$checkPop = isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1 ? 1 : 0;
	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/arcade_scripts/swfobject.js"></script>
	<div style="display: ' . (empty($checkFull) && empty($checkPop) ? 'flex' : 'inline') . ';align-items: center;justify-content: center;">
		<div id="gameplayer">
			', $txt['arcade_no_flash'], '
		</div>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		var arcadeScroll = document.getElementById("playgame");
		if (arcadeScroll != null)
			arcadeScroll.scrollIntoView();
		var play_url = smf_scripturl + "?action=arcade;sa=play;xml";
		var running = false;
		function getArcadeUrlParam4(name){
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
		function arcadeRestart()
		{
			setInnerHTML(document.getElementById("gameplayer"), "', addslashes($txt['arcade_no_flash']), '");
			var so = document.getElementById("gameplayer");
			var full = getArcadeUrlParam4("pop");
			var flashvars = {};
			var params = { scale: "exactFit" };
			var attributes = {};
			if (full == 1)
				swfobject.embedSWF("' , $game['url']['flash'], '", so, "100%", "100%", 10, false, flashvars, params, attributes);
			else
				swfobject.embedSWF("' , $game['url']['flash'], '", so, ', $game['extra_data']['width'], ', ', $game['extra_data']['height'], ', 10);

			return true;
		}

		', $auto_start ? 'arcadeRestart();' : '', '
	// ]]></script>';
}

function ArcadeV2Start()
{
	global $scripturl, $txt, $db_prefix, $context, $boarddir, $arcadeModSettings, $smcFunc;

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	if (!isset($_REQUEST['game']))
		v2Error('invalid_game');

	$game = getGameInfo($_REQUEST['game'], false, 0);

	if ($game === false)
		v2Error('invalid_game');

	$session = &$_SESSION['arcade_play_' . $game['id']];
	$extra = &$_SESSION['arcade_play_extra_' . $game['id']];

	if (!isset($extra['max_try']))
		$maxTry = 0;
	else
		$maxTry = (int) $extra['max_try'];

	$session = array(
		'id' => $game['id'],
		'game' => $game['internal_name'],
		'maxTries' => $maxTry,
		'loadStart' => time(),
		'scores' => array(),
		'done' => false,
		'hash' => rand(1, 50),
	);

	echo '&maxtry=', $maxTry, '&sesc=', $context['session_id'] , '&hash=', $session['hash'];

	obExit(false);
}

function ArcadeV2Hash()
{
	global $scripturl, $txt, $db_prefix, $context, $boarddir, $arcadeModSettings, $smcFunc;

	checkSession('post');

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	$game = getGameInfo($_REQUEST['game'], false, 0);

	if ($game === false)
		v2Error('invalid_game');

	$session = &$_SESSION['arcade_play_' . $game['id']];
	$extra = &$_SESSION['arcade_play_extra_' . $game['id']];

	$session['score_hash'] = rand(1, 50);
	$session['score_hash_time'] = microtime_float();

	$session['request_id'] = $_REQUEST['request_id'];

	echo '&scorehash=', $session['score_hash'], '&savescore=1';

	obExit(false);
}

function ArcadeV2Score()
{
	global $scripturl, $txt, $db_prefix, $context, $boarddir, $arcadeModSettings, $smcFunc;

	checkSession('post');

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	$game = getGameInfo($_REQUEST['game'], false, 0);

	if ($game === false)
		v2Error('invalid_game');

	$session = &$_SESSION['arcade_play_' . $game['id']];
	$extra = &$_SESSION['arcade_play_extra_' . $game['id']];

	if (empty($_POST['score']))
		v2Error('invalid_try');

	if (isset($_POST['score']) && is_numeric($_POST['score']))
		$score = floatval($_POST['score']);
	elseif (isset($_POST['score']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['score']));
	else {
		$score = 0;
		$_POST['secret'] = 'invalid';
	}

	$score_hash = $score . $session['hash'] . $session['score_hash'];

	if ($score_hash != $_POST['secret'])
		v2Error('invalid_hash');
	if (!sha1($_REQUEST['game'] . $score) == $session['request_id'])
		v2Error('invalid_hash');
	if (!compHsha($_POST['score_hash'], $score . '-' . $session['hash'] . '-' . $session['score_hash']))
		v2Error('invalid_hash');
	if (!compHsha($_POST['secret2'], $score . '-' . $score_hash))
		v2Error('invalid_hash');
	if (microtime_float() - $session['score_hash_time'] > 5)
		v2Error('invalid_try');

	$session['scores'][] = array(
		'maxTries' => $_POST['maxTries'],
		'endTime' => round($_POST['endTime'] / 1000),
		'startTime' => round($_POST['startTime'] / 1000),
		'serverTime' => time(),
		'playerName' => $_POST['playerName'],
		'try' => $_POST['tries'],
		'score' => $score,
		'level' => $_POST['level'] != 'undefined' ? $_POST['level'] : '',
		'hash' => serialize(array(
			'2.5.0', $_REQUEST['game'], $score, $session['hash'],
			$session['score_hash'], $_POST['secret'], $_POST['score_hash'], $_POST['secret2'],
		)),
	);

	$session['request_id'] = '';

	echo '&maxtry=', $session['maxTries'];

	obExit(false);
}

function compHash($hash1, $hash2)
{
	return round($hash1, 3) == round($hash2, 3);
}
function compHsha($hash1, $hash2)
{
	return $hash1 == sha1($_REQUEST['game'] . sha1($hash2));
}

function v2Error($error)
{
	// DEBUG
	$err = arcade_html_entity_decode($error, 2, 2);
	log_error($err, 'debug');

	obExit(false);
}
?>