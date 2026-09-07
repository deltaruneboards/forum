<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

// Provides support for phpbb HTML5 games
$forum_dir = dirname(dirname(__FILE__));
if (!isset($_REQUEST['scoreprep']))
	die('Hacking attempt...');
if (!file_exists($forum_dir . '/index.php') || !file_exists($forum_dir . '/SSI.php'))
	die('Hacking attempt...');

function _decodeArcadeString($str, $decodeKey, $arrayKey) {
   $result = "";
   $j = 0;
   $i = 0;
   for ($i=0; $i < strlen($str); $i++) {
		if ($j > strlen($decodeKey))
			$j = 0;
		$a = _uniordArcade(substr($str, $i, 1));
		if ($i <= $j && $arrayKey != 'score')
			$b = floatval(substr($decodeKey, $j, 1)) ^ floatval($a);
		else
			$b = floatval($a);
		$result .= _fromArcadeCharCode($b);
		$j++;
   }

   return $result;
}

function _getArcadeCharcode($str,$i) {
	 return _uniordArcade(mb_substr($str, $i, 1));
}

function _fromArcadeCharCode(){
  $output = '';
  $chars = func_get_args();
  foreach($chars as $char){
	$output .= chr(floatval($char));
  }
  return $output;
}

function _uniordArcade($c) {
	$h = ord($c[0]);
	if ($h <= 0x7F) {
		return $h;
	} else if ($h < 0xC2) {
		return false;
	} else if ($h <= 0xDF) {
		return ($h & 0x1F) << 6 | (ord($c[1]) & 0x3F);
	} else if ($h <= 0xEF) {
		return ($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6 | (ord($c[2]) & 0x3F);
	} else if ($h <= 0xF4) {
		return ($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 | (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F);
	} else {
		return false;
	}
}

function CheatingCheckHtml5()
{
	global $scripturl, $arcadeModSettings;

	$error = '';

	// Default check level is 1
	if (!isset($arcadeModSettings['arcadeCheckLevel']))
		$arcadeModSettings['arcadeCheckLevel'] = 1;

	if (!empty($_SERVER['HTTP_REFERER']))
		$referer = parse_url($_SERVER['HTTP_REFERER']);

	$real = parse_url($scripturl);

	// Level 1 Check
	// Checks also HTTP_REFERER if it not is empty
	if ($arcadeModSettings['arcadeCheckLevel'] == 1)
	{
		if (isset($referer) && ($real['host'] != $referer['host'] || $real['scheme'] != $referer['scheme']))
			$error = 'invalid_referer';
	}
	// Level 2 Check
	// Doesn't allow HTTP_REFERER to be empty
	elseif ($arcadeModSettings['arcadeCheckLevel'] == 2)
	{
		if (!isset($referer) || (isset($referer) && ($real['host'] != $referer['host'] || $real['scheme'] != $referer['scheme'])))
			$error = 'invalid_referer';

	}
	// Level 0 check
	else
		$error = '';

	return $error;
}

// Simple Portal & (older) EhPortal patch to thwart undefined errors
global $context;
list($context['html_headers'], $context['user']['is_guest'], $context['browser']['is_ie8']) = array('', 1, 0);

require_once($forum_dir . '/SSI.php');
global $sourcedir, $scripturl, $boarddir, $context;

if (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == 'arcade')
{
	global $user_info, $txt;
	loadLanguage('Arcade');

	$_SESSION[$_SESSION['arcade_html5_token'][1]] = !empty($_SESSION[$_SESSION['arcade_html5_token'][1]]) ? $_SESSION[$_SESSION['arcade_html5_token'][1]] : mt_rand(10000, 99999);
	$_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] = !empty($_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]]) ? $_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] : 0;

	if ($_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] == 2)
	{
		echo 'window.location="' . $scripturl . '?action=arcade' . '"';
		die();
	}

	$rand = $_SESSION[$_SESSION['arcade_html5_token'][1]];
	$_REQUEST['action'] = 'arcade';
	if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'newscore')
		$_REQUEST['sa'] = 'ibpsubmit2';
	if (isset($_REQUEST['scoreprep']) && $_REQUEST['scoreprep'] == 'prepascore')
		return;
	list($datum, $ucDatum, $score, $guest, $guestipscore, $_SESSION['save_score'], $_SESSION['arcade_guest_score']) = array(array(), array(), null, '', false, false, array());
	$sessionGame = !empty($_SESSION['arcade_current_game_internal']) ? $_SESSION['arcade_current_game_internal'] : '';
	if (isset($_POST['gsubmitscore']) && strpos($_POST['gsubmitscore'], ';') !== false)
	{
		$_POST['gsubmitscore'] = str_ireplace('javascript', 'js', $_POST['gsubmitscore']);
		$dataArray = explode(';', str_replace('gsubmitscore=', '', $_POST['gsubmitscore']));
		$datatypes = array('game_name', 'a', 'b', 'score', 'c', 'uc');
		$x = 0;
		foreach ($dataArray as $data)
		{
			if (!empty($datatypes[$x]))
				$datum[$datatypes[$x]] = _decodeArcadeString($data, $rand, $datatypes[$x]);

			$ucdatum[$datatypes[$x]] = $data;
			$x++;
		}

		$datum['do'] = 'newscore';
		$datum['act'] = 'arcade';
		$datum['time'] = time();
	}

	// feedback from POST was not correct so log it
	if (!empty($ucdatum['uc']) && $ucdatum['uc'] == 'NaN')
	{
		$datum = $ucdatum;
		log_error($txt['arcade_html53_submit_error']);
	}

	$safe = !empty($datum['game_name']) ? $datum['game_name'] : '';
	$score = isset($datum['score']) ? (floatval($datum['score'])) : null;
	$post_data = '';
	if (!empty($_POST))
	{
		foreach ($_POST as $k => $v)
			$post_data .= (($post_data) ? "\n" : '') . "$k=$v";
	}

	if (!defined('SMF'))
		require_once($boarddir . '/index.php');

	require_once($sourcedir . '/Arcade.php');
	require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	//$safe = $_SESSION['arcade_current_game_internal'];
	if (!$datum && empty($_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]]))
	{
		echo 'abc=' . (string)$rand . ';/arcade.php';
		$_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] = 1;
	}
	elseif (isset($score) && !empty($_SESSION['game_data_backup'][$safe]) && $_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] == 1)
	{
		$_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] = 2;
		$checkIp = !empty($user_info['ip']) ? trim($user_info['ip']) : !empty($user_info['ip2']) ? trim($user_info['ip2']) : arcade_get_client_ip();
		if ($user_info['is_guest'] && allowedTo('arcade_submit'))
		{
			global $smcFunc;

			$tempNames = array();
			$request = $smcFunc['db_query']('', '
				SELECT temp_name
				FROM {db_prefix}arcade_guest_data
				WHERE temp_name != {string:tempname}
				LIMIT 1',
				array(
					'tempname' => '',
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$tempNames[] = $row['temp_name'];
			}
			$smcFunc['db_free_result']($request);

			$request = $smcFunc['db_query']('', '
				SELECT online_ip, temp_name, current_game
				FROM {db_prefix}arcade_guest_data
				WHERE online_ip = {string:guestip}
				LIMIT 1',
				array(
					'guestip' => $checkIp,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$_SESSION['playerName'] = $row['temp_name'];
				$guestipscore = $row['online_ip'];
			}
			$smcFunc['db_free_result']($request);

			if (empty($_SESSION['playerName']))
			{
				$_SESSION['playerName'] = sprintf($txt['arcade_guest_score_name'], (string)mt_rand(1, 9999));
				while (in_array($_SESSION['playerName'], $tempNames) == true)
					$_SESSION['playerName'] = sprintf($txt['arcade_guest_score_name'], (string)mt_rand(1, 9999));

				if ($guestipscore)
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_guest_data
						SET temp_name = {string:tempname}
						WHERE online_ip = {string:guestip}',
						array(
							'tempname' => $_SESSION['playerName'],
							'guestip' => $guestipscore,
						)
					);
				}
				elseif (!empty($_SESSION['current_game_id']))
				{
					$smcFunc['db_insert']('replace',
						'{db_prefix}arcade_guest_data',
						array(
							'online_ip' => 'string',
							'online_time' => 'int',
							'show_online' => 'int',
							'current_action' => 'int',
							'current_game' => 'int',
						),
						array(
							$checkIp,
							time(),
							0,
							1,
							floatval($_SESSION['current_game_id']),
						),
						array()
					);
				}
			}

			$member = array(
				'id' => 0,
				'name' => isset($_SESSION['playerName']) ? $_SESSION['playerName'] : '',
				'ip' => arcade_get_client_ip()
			);
		}
		elseif (allowedTo('arcade_submit'))
		{
			$member = array(
				'id' => $user_info['id'],
				'name' => $user_info['name'],
				'ip' => arcade_get_client_ip(),
			);
		}
		else
		{
			$guest='&guest=1';
			$_SESSION['arcade_guest_score'] = false;
			echo '&gameid=' . $_SESSION['current_game_id'] . '&highscore=1' . $guest;
			die();
		}
		$game = $_SESSION['game_data_backup'][$safe];
		$score = array(
			'id' => $_SESSION['current_game_id'],
			'score' => floatval($score),
			'endTime' => time(),
			'duration' => time() - $_SESSION['arcade_html5_token'][0],
			'status' => CheatingCheckHtml5(),
			'hash' => '',
			'internal' => $safe,
		);

		$_SESSION['save_score'] = array($game, $member, $score);
		if ($user_info['is_guest'])
		{
			$guest='&guest=1';
			$_SESSION['arcade_guest_score'] = true;
		}

		echo '&gameid=' . $_SESSION['current_game_id'] . '&highscore=1' . $guest;
		die();
	}

	Arcade();
	obExit(true);
}
elseif (isset($_SESSION['current_game_id']) && allowedTo('arcade_submit') && !empty($_SESSION['save_score']))
{
	$gameid = floatval($_SESSION['current_game_id']);

	if (empty($_SESSION['arcade_guest_score']))
	{
		require_once($sourcedir . '/Arcade.php');
		require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
		$scoreData = SaveScore($_SESSION['save_score'][0], $_SESSION['save_score'][1], $_SESSION['save_score'][2]);
		redirectexit($scripturl . '?action=arcade;sa=highscore;game=' . $gameid . ';reload=' . mt_rand(1, 9999) . ';score=' . $scoreData['id'] . ';edit;#commentform3');
	}
	else
	{
		require_once($sourcedir . '/Arcade.php');
		require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
		$scoreData = SaveScore($_SESSION['save_score'][0], $_SESSION['save_score'][1], $_SESSION['save_score'][2]);
		redirectexit($scripturl . '?action=arcade;sa=highscore;game=' . $gameid . ';reload=' . mt_rand(1, 9999) . ';score=' . $scoreData['id'] . ';edit;#commentform3');
	}
}

die('Hacking attempt...');
?>
