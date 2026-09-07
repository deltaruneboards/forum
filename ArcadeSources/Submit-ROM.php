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
function ArcadeROMGetGame()
{
	$gname = isset($_POST['game_name']) ? $_POST['game_name'] : (isset($_POST['gname']) ? $_POST['gname'] : '');
	return getGameInfo($gname, false, 1);
}

function ArcadeROMGame()
{
	global $scripturl, $boarddir, $context, $txt, $user_info, $arcadeModSettings, $smcFunc;

	$context['html_headers'] .= '
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="pragma" content="no-cache">';

	require_once($boarddir . '/ArcadeSources/ArcadeGame.php');
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$action = !empty($rom) ? 'retro_arch' : 'arcade';
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
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($_POST['time']) && is_numeric($_POST['time']))
		$time = (int)$_POST['time'] > 0 ? (int)$_POST['time'] : time();
	elseif (isset($_POST['smfgametime']) && is_numeric($_POST['smfgametime']))
		$time = (int)$_POST['smfgametime'] > 0 ? (int)$_POST['smfgametime'] : time();
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($_POST['game_name']))
		$gameName = ArcadeSpecialChars(mb_strtolower(trim($_POST['game_name'])));
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

	if (isset($_POST['popup']) && $_POST['popup'] == 1)
		$url = $scripturl . '?action=' . $action . ';sa=' . $save . ';pop=1;end;game=' . $gameid. ';' . $scoreid . '#commentform3';
	else
		$url = $scripturl . '?action=' . $action . ';sa=' . $save . ';end;game=' . $gameid. ';' . $scoreid . '#commentform3';

	if (isset($_POST['gamesessid']) && !empty($_SESSION['arcade_rom_token']) && empty($_SESSION['game_' . $gameName]) && empty($scoreid))
	{
		$gameToken = ArcadeSpecialChars($_POST['gamesessid']);
		$initialToken = $_SESSION['arcade_rom_token'];
		unset($_SESSION['arcade_rom_token']);
		$_SESSION['game_' . $gameName] = time();
	}
	elseif (!empty($_SESSION['game_' . $gameName]) || !empty($scoreid))
	{
		unset($_SESSION['game_' . $gameName], $_SESSION['arcade_rom_token']);

		// only submit an error in the log once per session if logging is enabled
		if (empty($_SESSION['game_log_' . $gameName]))
		{
			$_SESSION['game_log_' . $gameName] = time();
			if (!empty($arcadeModSettings['arcade_log_scoreloop'])) {
				$err = sprintf($txt['arcade_submit_error_loop_log'], $gameName);
				$err = arcade_html_entity_decode($err, 2, 2);
				log_error($err, 'debug');
			}
		}
		echo '<script type="text/javascript">window.location.replace("' . $url . '");</script>';
		arcadeClearSession();
		exit();
		fatal_lang_error('arcade_submit_error_loop', false);
	}
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error_session', false);
	}

	if ($initialToken[1] == $gameToken)
	{
		$context['game'] = getGameInfo($gameid, false, 1);
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

			$_SESSION['arcade_check_' . $context['game']['id']] = 'saved';
			if (empty($gameexit))
				ArcadeSubmit($url);

			echo '<script type="text/javascript">window.location.replace("' . $url . '");</script>';
			if (isset($_POST['popup']) && $_POST['popup'] == 1)
				redirectexit($scripturl . '?action=' . $action . ';end;sa=' . $save . ';pop=1;game=' . $gameid. ';#commentform3');
			else
				redirectexit($scripturl . '?action=' . $action . ';end;sa=' . $save . ';game=' . $gameid. ';#commentform3');

		}
		elseif(!empty($_SESSION['arcade_check_' . $context['game']['id']]))
		{
			echo '<script type="text/javascript">window.location.replace("' . $url . '");</script>';
			if (isset($_POST['popup']) && $_POST['popup'] == 1)
				redirectexit($scripturl . '?action=' . $action . ';end;sa=' . $save . ';pop=1;game=' . $gameid. ';#commentform3');
			else
				redirectexit($scripturl . '?action=' . $action . ';end;sa=' . $save . ';game=' . $gameid. ';#commentform3');
		}
		else {
			arcadeClearSession();
			fatal_lang_error('arcade_submit_error', false);
		}
	}
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error_session', false);
	}

	return false;
}

// Get Score
function ArcadeROMSubmit(&$game, $session_info)
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

function ArcadeROMPlay(&$game, &$session_info)
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

function ArcadeROMXMLPlay(&$game, &$session_info)
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

function ArcadeROMHtml(&$game, $auto_start = true)
{
	global $txt, $context, $settings, $arcadeModSettings, $scripturl, $boarddir;
	$checkFull = isset($_REQUEST['full']) && $_REQUEST['full'] == 1 ? true : false;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$action = !empty($rom) ? 'retro_arch' : 'arcade';
	if (empty($context['arcade_retro_arch_rom']) && !empty($game['id'])) {
		$context['arcade']['rom_return_data'] = true;
		require_once($boarddir . '/ArcadeSources/ArcadeRetroArch.php');
		$data = ArcadeRetroArch(true, $game['id']);
		$checkFull = true;		
	}
	
	echo '
	<div style="display: flex;align-items: center;justify-content: center;">
		<div id="gamecontainer" style="margin: 0 auto;' . (empty($checkFull) ? 'width: 960px; height: 540px;overflow: hidden;' : 'width: 100vw; height: 100vh;overflow: hidden;') . '">
			<iframe style="display: inline;" class="centertext" id="iframe_smfarcaderoms" srcdoc=\'' . $context['arcade_retro_arch_rom'] . '\'>', $txt['arcade_rom_load_error'], '</iframe>
		</div>
	</div>

	<script type="text/javascript"><!-- // --><![CDATA[
		var play_url = smf_scripturl + "?action=' . $action . ';sa=play;xml";
		var running = false;
		var romObjContainer = document.getElementById("iframe_smfarcaderoms");

		function arcadeRestart()
		{
			running = false;			
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

			running = true;
			document.getElementById("iframe_smfarcaderoms").style.width = "' . ((int)$game['width']) . 'px;";
			document.getElementById("iframe_smfarcaderoms").style.height = "' . ((int)$game['height']) . 'px;";
			document.getElementById("iframe_smfarcaderoms").contentWindow.location.reload();
			return true;
		}
		if (window.addEventListener)
			window.addEventListener("load", function (){
				smfArcadeGameDims5();', $auto_start ? '
				arcadeRestart();
				return true;' : '', '
			});
		else
			window.attachEvent("onload", function (){
				smfArcadeGameDims5();', $auto_start ? '
				arcadeRestart();
				return true;' : '', '
			});
		function smfArcadeGameDims5() {
			document.getElementsByTagName("body")[0].style.overflow = "hidden";
			var divelement = document.getElementById("gamecontainer");
			divelement.style.width = "' . ((int)$game['width']) . 'px";
			divelement.style.height = "' . ((int)$game['height']) . 'px";
			scrollTo(document.body, divelement.offsetTop, 100);
		};
		function escGameSmf() {
			window.location = "' . $scripturl . '?action=' . $action . ';sa=highscore;game=' . $game['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
		}
	// ]]></script>';
	/* setInnerHTML(document.getElementById("gamecontainer"), "', addslashes($txt['arcade_please_wait']), '"); */
}

?>