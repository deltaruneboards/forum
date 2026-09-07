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

// Get Game
function ArcadeHTML53GetGame()
{
	$gname = isset($_POST['game_name']) ? $_POST['game_name'] : (isset($_POST['gname']) ? $_POST['gname'] : '');
	return GetGameInfo($gname, false, 0);
}

function ArcadeHTML53Game()
{
	global $scripturl, $boarddir, $boardurl, $context, $txt, $user_info, $arcadeModSettings, $smcFunc;

	$context['html_headers'] .= '
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="pragma" content="no-cache">';

	require_once($boarddir . '/ArcadeSources/ArcadeGame.php');

	/*
	$vars = array(
		'game_name',
		'score',
		'game',
		'gtime',
		'gamesessid',
		'time',
		'popup',
		'gameexit',
		'html53'
	);

	foreach ($vars as $queryString)
	{
		if (!isset($_POST[$queryString]) && isset($_GET[$queryString]))
			$_POST[$queryString] = $_GET[$queryString];
	}
	*/

	if (isset($_POST['gameexit']) && is_numeric($_POST['gameexit']))
		$gameexit = (int)$_POST['gameexit'];

	if (isset($_POST['game']) && is_numeric($_POST['game']))
		$gameid = (int)$_POST['game'];
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($_POST['score']) && is_numeric($_POST['score']))
		$score = floatval($_POST['score']);
	elseif (isset($_POST['score']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['score']));
	elseif (isset($_POST['gscore']) && is_numeric($_POST['gscore']))
		$score = floatval($_POST['gscore']);
	elseif (isset($_POST['gscore']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['gscore']));
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($_POST['smfgametime']) && is_numeric($_POST['smfgametime']))
		$time = (int)$_POST['smfgametime'] > 0 ? (int)$_POST['smfgametime'] : time();
	elseif (isset($_POST['time']) && is_numeric($_POST['time']))
		$time = (int)$_POST['time'] > 0 ? (int)$_POST['time'] : time();
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($_POST['game_name']))
		$gameName = ArcadeSpecialChars(mb_strtolower(trim($_POST['game_name'])));
	elseif (isset($_POST['gname']))
		$gameName = ArcadeSpecialChars(mb_strtolower(trim($_POST['gname'])));
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	$scoreid = '';
	$guestName = $user_info['is_guest'] && !empty($_SESSION['playerName']) ? ArcadeSpecialChars(trim($_SESSION['playerName'])) : '';

	// must surpass 3 seconds else a score loop is flagged
	if (empty($guestName))
	{
		$result = $smcFunc['db_query']('', '
			SELECT
				id_score, score, end_time, comment, id_member, id_game
			FROM  {db_prefix}arcade_scores
			WHERE id_game = {int:game} AND id_member = {int:member} AND comment = "" AND UNIX_TIMESTAMP() - end_time < {int:max_exec_time}
			ORDER BY id_score DESC
			LIMIT 1',
			array(
				'game' => $gameid,
				'member' => empty($user_info['id']) || $user_info['id'] == -1 ? 0 : $user_info['id'],
				'max_exec_time' => 3,
			)
		);
	}
	else
		$result = $smcFunc['db_query']('', '
			SELECT
				id_score, score, end_time, comment, id_member, id_game, player_name
			FROM  {db_prefix}arcade_scores
			WHERE id_game = {int:game} AND id_member = {int:member} AND comment = "" AND player_name = {string:name} AND UNIX_TIMESTAMP() - end_time < {int:max_exec_time}
			ORDER BY id_score DESC
			LIMIT 1',
			array(
				'game' => $gameid,
				'member' => empty($user_info['id']) || $user_info['id'] == -1 ? 0 : $user_info['id'],
				'name' => $guestName,
				'max_exec_time' => 3,
			)
		);

	while ($info = $smcFunc['db_fetch_assoc']($result))
		$scoreid = !empty($info['id_score']) ? 'edit;score=' . $info['id_score'] . ';' : '';
	$smcFunc['db_free_result']($result);
	$save = $user_info['is_guest'] && empty($scoreid) ? 'save' : 'highscore';
	$checkFullscreen = isset($_REQUEST['sameArcadeWindow']) && $_REQUEST['sameArcadeWindow'] == 1 ? true : false;
	if (isset($_POST['popup']) && $_POST['popup'] == 1 && empty($checkFullscreen))
		$url = $scripturl . '?action=arcade;sa=' . $save . ';pop=1;end;game=' . $gameid. ';' . $scoreid . '#commentform3';
	else
		$url = $scripturl . '?action=arcade;sa=' . $save . ';end;game=' . $gameid. ';' . $scoreid . '#commentform3';

	if (isset($_POST['gamesessid']) && !empty($_SESSION['arcade_html5_token']) && empty($_SESSION['game_' . $gameName]) && empty($scoreid))
	{
		$gameToken = ArcadeSpecialChars($_POST['gamesessid']);
		$initialToken = $_SESSION['arcade_html5_token'];
		unset($_SESSION['arcade_html5_token']);
		$_SESSION['game_' . $gameName] = time();
	}
	elseif (!empty($_SESSION['game_' . $gameName]) || !empty($scoreid))
	{
		unset($_SESSION['game_' . $gameName], $_SESSION['arcade_html5_token']);
		// only submit an error in the log once per session if logging is enabled
		if (empty($_SESSION['game_log_' . $gameName]))
		{
			$_SESSION['game_log_' . $gameName] = time();
			if (!empty($arcadeModSettings['arcade_log_scoreloop'])) {
				$err = sprintf($txt['arcade_submit_error_loop_log'], $gameName);
				$err = arcade_html_entity_decode($err, 2, 2);
				log_error($err, 'debug');
			}
			echo '
		<script>
			try {
				throw new Error("Saving score...")
			} catch (e) {
				console.error(e.name + ": " + e.message)
			}
		</script>';
		}
		//redirectexit($url);
		echo '<script type="text/javascript">window.location.assign("' . $url . '");</script>';
		arcadeClearSession();
		exit('1');
		fatal_lang_error('arcade_submit_error_loop', false);
	}
	else
	{
		if (isset($_POST['popup']) && $_POST['popup'] == 1 && empty($checkFullscreen))
			$url = $scripturl . '?action=arcade;sa=' . $save . ';pop=1;end;game=' . $gameid. ';' . $scoreid . '#commentform3';
		else
			$url = $scripturl . '?action=arcade;sa=' . $save . ';end;game=' . $gameid. ';' . $scoreid . '#commentform3';
		//echo '<script type="text/javascript">window.location.assign("' . $url . '");</script>';
		redirectexit($url);
		arcadeClearSession();
		exit('2');
		fatal_lang_error('arcade_submit_error_session', false);
	}

	if ($initialToken[1] == $gameToken)
	{
		$context['game'] = getGameInfo($gameid, false, 0);
		$cheating = CheatingCheck();
		if (empty($cheating) && empty($_SESSION['arcade_check_' . $context['game']['id']]))
		{
			// DB session lifetime else 48 minutes max for playing a game
			$maxSessTime = !empty($arcadeModSettings['databaseSession_lifetime']) ? $arcadeModSettings['databaseSession_lifetime'] : 2880;
			if (empty($gameexit) && (time() - $initialToken[0]) > $maxSessTime)
			{
				arcadeClearSession();
				fatal_lang_error('arcade_submit_error_session', false);
				return false;
			}

			echo '
		<script type="text/javascript">
			sessionStorage.removeItem("scoreLoop_' . ArcadeSpecialChars($_POST['gamesessid']) . '");
		</script>';
			$_SESSION['arcade_check_' . $context['game']['id']] = 'saved';
			if (empty($gameexit))
				ArcadeSubmit($url);

			//echo '<script type="text/javascript">window.location.assign("' . $url . '");</script>';
			if (isset($_POST['popup']) && $_POST['popup'] == 1)
				redirectexit($scripturl . '?action=arcade;end;sa=' . $save . ';pop=1;game=' . $gameid. ';#commentform3');
			else
				redirectexit($scripturl . '?action=arcade;end;sa=' . $save . ';game=' . $gameid. ';#commentform3');

		}
		elseif(!empty($_SESSION['arcade_check_' . $context['game']['id']]))
		{
			if (isset($_POST['gamesessid']))
			{
				echo '
		<script type="text/javascript">
			sessionStorage.removeItem("scoreLoop_' . ArcadeSpecialChars($_POST['gamesessid']) . '");
		</script>';

				redirectexit($url);
			}
			//echo '<script type="text/javascript">window.location.assign("' . $url . '");</script>';
			if (isset($_POST['popup']) && $_POST['popup'] == 1)
				redirectexit($scripturl . '?action=arcade;end;sa=' . $save . ';pop=1;game=' . $gameid. ';#commentform3');
			else
				redirectexit($scripturl . '?action=arcade;end;sa=' . $save . ';game=' . $gameid. ';#commentform3');
		}
		else
		{
			arcadeClearSession();
			fatal_lang_error('arcade_submit_error', false);
		}
	}
	else
	{
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error_session', false);
	}

	return false;
}

// Get Score
function ArcadeHTML53Submit(&$game, $session_info)
{
	if (isset($_POST['score']) && is_numeric($_POST['score']))
		$score = floatval($_POST['score']);
	elseif (isset($_POST['score']))
		$score = floatval(preg_replace("/[^-0-9\.]/","", $_POST['score']));
	elseif (isset($_POST['gscore']) && is_numeric($_POST['gscore']))
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

function ArcadeHTML53Play(&$game, &$session_info)
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

	$_SESSION['arcade']['ibp_game'] = $game['internal_name'];
}

function ArcadeHTML53XMLPlay(&$game, &$session_info)
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

	$_SESSION['arcade']['ibp_game'] = $game['internal_name'];
	return true;
}

function ArcadeHTML53Html(&$game, $auto_start = true)
{
	global $txt, $context, $settings, $arcadeModSettings, $scripturl;
	$checkFull = isset($_REQUEST['full']) && $_REQUEST['full'] == 1 ? true : false;
	if (isset($_REQUEST['sameArcadeWindow']) && $_REQUEST['sameArcadeWindow'] == 1)
	{
		$_REQUEST['pop'] = 0;
		$_REQUEST['full'] = 0;
		$_REQUEST['sameArcadeWindow'] = 0;
	}

	echo '
	<div style="display: flex;align-items: center;justify-content: center;">
		<div id="gameplayer" style="margin: auto; width: ', $game['width'], 'px; height: ', $game['height'], 'px; ">
			', $txt['arcade_html5'], '
		</div>
	</div>

	<script type="text/javascript"><!-- // --><![CDATA[
		var play_url = smf_scripturl + "?action=arcade;sa=play;xml";
		var running = false;

		function arcadeRestart()
		{
			running = false;
			setInnerHTML(document.getElementById("gameplayer"), "', addslashes($txt['arcade_please_wait']), '");
			var i, x = new Array();
			x[0] = "game=', $game['id'] . '";
			x[1] = "', $context['session_var'], '=', $context['session_id'], '";
			ArcadeStart();
			return false;
		}

		function ArcadeStart()
		{
			if (running)
				return;

			running = true;' . ($checkFull ? '
			var so = \'<object type="text/html" style="overflow: hidden;height: 100vh;width: 100vw;position: absolute;top: 0px;left: 0px;" data="' . $arcadeModSettings['gamesUrl'] . '/' . $game['directory'] . '/' . $game['file'] . '"></object>\';' : '
			var so = \'<object type="text/html" style="overflow: hidden;height: ' . ((int)$game['height']) . 'px;width: ' . ((int)$game['width']) . 'px;" data="' . $arcadeModSettings['gamesUrl'] . '/' . $game['directory'] . '/' . $game['file'] . '"></object>\';') . '
			document.getElementById("gameplayer").innerHTML = so;
			return true;
		}
		if (window.addEventListener)
			window.addEventListener("load", function (){
				smfArcadeGameDims4();', $auto_start ? '
				arcadeRestart();
				return true;' : '', '
			});
		else
			window.attachEvent("onload", function (){
				smfArcadeGameDims4();', $auto_start ? '
				arcadeRestart();
				return true;' : '', '
			});
		function smfArcadeGameDims4() {
			document.getElementsByTagName("body")[0].style.overflow = "hidden";
			var divelement = document.getElementById("gameplayer");
			divelement.style.width = "' . ((int)$game['width']) . 'px";
			divelement.style.height = "' . ((int)$game['height']) . 'px";
			scrollTo(document.body, divelement.offsetTop, 100);
		};
		function escGameSmf() {
			window.location = "' . $scripturl . '?action=arcade;sa=highscore;game=' . $game['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
		}
	// ]]></script>';
}

?>