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

function ArcadePlay()
{
	global $boarddir, $boardurl, $sourcedir, $scripturl, $txt, $db_prefix, $context, $smcFunc, $user_info, $arcadeModSettings, $context, $settings;
	$context['arcade']['popupscore'] = false;
	$context['arcade_smf_version'] = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$ruffleNeed = isset($_COOKIE['smfArcadeSetFlashCookie']) && $_COOKIE['smfArcadeSetFlashCookie'] == 'shockwave' ? false : true;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$action = !empty($rom) ? 'retro_arch' : 'arcade';
	list($_SESSION['arcade_rom_initiate'], $numberPlays) = array('', false);

	// immediate container reloads are thwarted
	if (!empty($_SESSION['arcade_reload_timer']) && time() - $_SESSION['arcade_reload_timer'] < 1.5) {
		return;
	}
	$_SESSION['arcade_reload_timer'] = time();

	// Add the JQuery library for SMF 2.1
	if (isset($arcadeModSettings['jquery_source']) && $arcadeModSettings['jquery_source'] == 'cdn')
		$context['arcade_jquery_file'] = 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js';
	elseif (isset($arcadeModSettings['jquery_source']) && $arcadeModSettings['jquery_source'] == 'local' && file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcade-jquery.js'))
		$context['arcade_jquery_file'] = $settings['default_theme_url'] . '/arcade_scripts/arcade-jquery.js';
	elseif (isset($arcadeModSettings['jquery_source'], $arcadeModSettings['jquery_custom']) && $arcadeModSettings['jquery_source'] == 'custom')
		$context['arcade_jquery_file'] = $arcadeModSettings['jquery_custom'];
	else
		$context['arcade_jquery_file'] = 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js';

	if (substr($scripturl, 0, 6) == 'https:' && empty($arcadeModSettings['arcade_contentSecurityPolicy']))
		$context['html_headers'] .= '
	<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">';

	if (!$context['arcade']['can_play']) {
		arcadeClearSession();
		fatal_lang_error('cannot_arcade_play', false);
	}

	if (empty($_REQUEST['game']) && !isset($_REQUEST['random']) && empty($_REQUEST['match'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_not_found', false);
	}

	$arcadeModSettings['arcade_playscreen'] = !empty($arcadeModSettings['arcade_playscreen']) ? 1 : 0;
	$extra = array();
	unset($_SESSION['arcade']);
	unset($context['game']);

	if (isset($_REQUEST['match']))
	{
		$id_match = (int) $_REQUEST['match'];
		$extra['match'] = $id_match;
		$extra['max_try'] = 1;

		$request = $smcFunc['db_query']('', '
			SELECT m.id_match, m.name, m.current_players, m.num_players, m.current_round, m.num_rounds, m.status
			FROM {db_prefix}arcade_matches AS m
			WHERE m.id_match = {int:match}
				AND status = 1',
			array(
				'match' => $id_match,
			)
		);
		$matchInfo = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!$matchInfo) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		$request = $smcFunc['db_query']('', '
			SELECT id_game
			FROM {db_prefix}arcade_matches_rounds
			WHERE id_match = {int:match}
				AND round = {int:round}',
			array(
				'match' => $id_match,
				'round' => $matchInfo['current_round'],
			)
		);
		$round = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!$round || empty($round['id_game'])) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found');
		}

		$request = $smcFunc['db_query']('', '
			SELECT status
			FROM {db_prefix}arcade_matches_players
			WHERE id_match = {int:match}
				AND id_member = {int:member}',
			array(
				'match' => $id_match,
				'round' => $matchInfo['current_round'],
				'member' => $user_info['id'],
			)
		);
		$result = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!$result) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		if ($result['status'] == 1)
		{
			matchUpdatePlayers($id_match, array($user_info['id']), 2);
		}
		elseif ($result['status'] == 2 || $result['status'] == 3)
		{
			matchUpdatePlayers($id_match, array($user_info['id']), 3);

			redirectexit('action=' . $action . ';sa=viewMatch;match=' . $id_match);
		}

		$_REQUEST['game'] = $round['id_game'];
	}

	$query = isset($_REQUEST['random']) ? 'random' : $_REQUEST['game'];
	if (isset($_REQUEST['random'])) {
		$id = small_game_query('ORDER BY RAND() LIMIT 1', $rom);
		if (!empty($id)) {
			$key = array_key_first($id);
			$query = $id[$key]['id'];
		}
		else {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}
	}
	if (!$context['game'] = getGameInfo($query, false, 0)) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_not_found', false);
	}

	unset($_SESSION['arcade_play_' . $context['game']['id']]);
	$_SESSION['current_game_id'] = !empty($context['game']['id']) ? $context['game']['id'] : 0;
	$safe = ArcadeSessionSanitize($context['game']['internal_name']);
	unset($_SESSION['game_data_backup']);
	$gameName = ArcadeSpecialChars(mb_strtolower(trim($context['game']['internal_name'])));
	unset($_SESSION['game_' . $gameName]);
	$_SESSION['game_data_backup'][$safe] = $context['game'];
	// in case of html5 game type mismatch, fall back to smf v1
	if (strpos($context['game']['submit_system'], 'html5')  !== false && substr($context['game']['file'], -5) != '.html')
		$context['game']['submit_system'] = 'v1game';

	$system = SubmitSystemInfo($context['game']['submit_system']);
	if (strpos($context['game']['submit_system'], 'html53')  !== false)
		unset($_SESSION['gameloopcheck']);

	if (empty($system['file'])) {
		redirectexit($scripturl . '?action=' . $action);
	}
	require_once($boarddir . '/ArcadeSources/' . $system['file']);
	$isCustomGame = $context['game']['submit_system'] == 'custom_game';
	$ruffleEnable = !empty($arcadeModSettings['arcade_flash_emulator']) ? intval($arcadeModSettings['arcade_flash_emulator']) : 0;
	ArcadePlayTabs($context['game']);
	$_SESSION['arcade']['play_vb3g'][$_SESSION['current_game_id']] = $context['game']['submit_system'] == 'v3arcade' ? $context['game']['id'] : 0;
	$_SESSION['arcade_play_' . $context['game']['id']] = array(
		'game' => $context['game']['internal_name'],
		'id' => $context['game']['id'],
		'start_time' => time(),
		'done' => false,
		'score' => 0,
		'end_time' => 0,
		'last_id' => $context['game']['id'],
	);

	if (!empty($context['game']['type']) && $context['game']['type'] == 'fullscreen' && empty($_SESSION['arcade_isMobilePlay']))
	{
		$_REQUEST['pop'] = 1;
		$_REQUEST['full'] = 1;
		$_REQUEST['sameArcadeWindow'] = 1;
	}
	$selfCheck = file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/ruffle.js') ? 1 : 0;
	$arcadeModSettings['arcadeBehaviorCDN'] = !empty($arcadeModSettings['arcadeBehaviorCDN']) ? intval($arcadeModSettings['arcadeBehaviorCDN']) : 0;
	if (stripos($context['game']['submit_system'], 'rom') === FALSE && stripos($context['game']['submit_system'], 'html5')  === FALSE && $ruffleNeed && $ruffleEnable != 2) {
		switch($arcadeModSettings['arcadeBehaviorCDN']) {
			// the external check takes a second so we avoid that behavior if possible
			case 1:
				$hostFile = !empty($selfCheck) && empty($ruffleEnable) ? $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion : 'https://unpkg.com/@ruffle-rs/ruffle';
				$context['html_headers'] .= '
	<script src="' . $hostFile . '"></script>';
				break;
			case 2:
				if ($ruffleEnable == 1) {
					$cdnCheck = !empty(arcadeRuffleIsValidUrl('https://unpkg.com/@ruffle-rs/ruffle')) ? 1 : 0;
					$hostFile = !empty($cdnCheck) ? 'https://unpkg.com/@ruffle-rs/ruffle' : (!empty($selfCheck) ? $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion : '');
				}
				elseif (!empty($selfCheck)) {
					$hostFile = $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion;
				}
				else {
					$hostFile = !empty(arcadeRuffleIsValidUrl('https://unpkg.com/@ruffle-rs/ruffle')) ? 'https://unpkg.com/@ruffle-rs/ruffle' : '';
				}
				if (empty($hostFile)) {
					arcadeClearSession();
					fatal_lang_error('arcade_ruffle_core_error', false);
				}
				else {
					$context['html_headers'] .= '
	<script src="' . $hostFile . '"></script>';
				}
				break;
			default:
				$hostFile = $ruffleEnable == 1 ? 'https://unpkg.com/@ruffle-rs/ruffle' : (!empty($selfCheck) ? $settings['default_theme_url'] . '/arcade_scripts/ruffle/ruffle.js?' . $suffixVersion : '');
				if (empty($hostFile)) {
					arcadeClearSession();
					fatal_lang_error('arcade_ruffle_core_error', false);
				}
				else {
					$context['html_headers'] .= '
	<script src="' . $hostFile . '"></script>';
				}
		}
	}

	$romfull = isset($_REQUEST['full']) && is_numeric($_REQUEST['full']) && intval($_REQUEST['full']) == 1 ? 1 : 0;
	$rompop = isset($_REQUEST['pop']) && is_numeric($_REQUEST['pop']) && intval($_REQUEST['pop']) == 1 ? 1 : 0;
	if (strpos($context['game']['submit_system'], 'html5')  !== false || strpos($context['game']['submit_system'], 'rom')  !== false)
	{
		unset($_SESSION['arcade_check_' . $context['game']['id']]);
		if (!isset($_SESSION['arcade_play_' . $context['game']['id']]) || isset($_REQUEST['restart']))
			$_SESSION['arcade_play_' . $context['game']['id']] = array();

		$system['play']($context['game'], $_SESSION['arcade_play_' . $context['game']['id']]);
		$_SESSION['arcade_play_extra_' . $context['game']['id']] = $extra;
		$_SESSION['arcade_html5_token'] = array(time(), ArcadeSpecialChars(ArcadeRandomToken(78)));
		$_SESSION['arcade_current_game_internal'] = $context['game']['internal_name'];
		$_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] = 0;
		$context['game']['html'] = $system['html'];

		if ($context['game']['submit_system'] == 'html53')
		{
			$context['html_headers'] .= '
	<script type="text/javascript">
		var arcadeCurrentGameId = "' . $context['game']['id'] . '";
		var arcadeCurrentGameToken = "' . $_SESSION['arcade_html5_token'][1] . '";
	</script>' . (!empty($arcadeModSettings['arcade_jQuery_JV']) && file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcade-jquery.js') ? '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-jquery.js?' . $suffixVersion . '"></script>' : '') . (!empty($arcadeModSettings['arcade_phpbb3_support_score']) ? '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-html53.js?' . $suffixVersion . '"></script>' : '');
		}

		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'play') {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET num_plays = num_plays + 1
				WHERE id_game = {int:game}',
				array(
					'game' => $context['game']['id'],
				)
			);
			$context['arcade_rom_counter'] = true;
			$numberPlays = true;
		}

		/* Layout start */
		if (isset($_REQUEST['gamepopup']) && $_REQUEST['gamepopup'] == 1)
		{
			$context['arcade']['play'] = true;
			$context['template_layers'] = array();
			$context['popup2'] = false;
			$_SESSION['arcade']['gamepopup'] = 1;
			$_REQUEST['game'] = $context['game']['id'];
			$context['sub_template'] = 'arcade_popup_player';
			$context['arcade']['popupscore'] = true;
			return;
		}
		elseif (!empty($_REQUEST['sameArcadeWindow']) && isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1)
		{
			$context['arcade']['play'] = true;
			$context['template_layers'] = array();
			$context['popup2'] = false;
			$context['arcade']['popupscore'] = false;
			$_SESSION['arcade']['pop'] = 0;
			$_REQUEST['game'] = $context['game']['id'];
			$context['sub_template'] = 'arcade_popup_player';
			return;
		}
		elseif (isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1)
		{
			$context['arcade']['play'] = true;
			$context['template_layers'] = array();
			$context['popup2'] = false;
			$context['arcade']['popupscore'] = true;
			$_SESSION['arcade']['pop'] = 1;
			$_REQUEST['game'] = $context['game']['id'];
			$context['sub_template'] = 'arcade_popup_player';
			return;
		}
		elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'download')
		{
			unset($_SESSION['arcade']['gamepopup']);
			unset($_SESSION['arcade']['gamepop']);
			$context['arcade']['popupscore'] = false;
			$_REQUEST['game'] = $context['game']['id'];
			$context['arcade']['popupscore'] = false;
			$context['template_layers'][] = 'arcade_game';
			$context['sub_template'] = 'arcade_html5_game_play';
			$context['page_title'] = sprintf($txt['arcade_game_play'], $context['game']['name']);
			$context['arcade']['play'] = true;
			$context['linktree'][] = array(
				'url' => $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
				'name' => $context['game']['name'],
			);
			$context['html_headers'] .= '
			<script type="text/javascript"><!-- // --><![CDATA[
			var arcade_game_dir = "' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '";
			// ]]></script>';

			if (empty($_SESSION['arcade_isMobilePlay']))
				loadTemplate('ArcadeGame');
			else
			{
				$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">';
				loadTemplate('ArcadeGameMobile');
			}

			return;
		}
		else
		{
			unset($_SESSION['arcade']['popupscore']);
			unset($_SESSION['arcade']['gamepopup']);
			unset($_SESSION['arcade']['gamepop']);
			$context['arcade']['popupscore'] = false;
			$_SESSION['arcade']['pop'] = false;

			if (empty($context['show_pm_popup']))
				$context['show_pm_popup'] = false;

			if (empty($arcadeModSettings['arcadeEnableGameTemplate']))
				$arcadeModSettings['arcadeEnableGameTemplate'] = false;

			if ($context['game']['submit_system'] == 'rom' && empty($rompop) && empty($romfull))
			{
				require_once($boarddir . '/ArcadeSources/ArcadeRetroArch.php');
				//$context['page_title'] = $txt['arcade_rom_game_title'];
				$jsInsert = file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcadeRomAdd.js') ? file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/arcadeRomAdd.js') : '';
				$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
				$action = !empty($rom) ? 'retro_arch' : 'arcade';
				$_REQUEST['game'] = $context['game']['id'];
				ArcadeRetroArch(false);
				return;
			}

			if ($arcadeModSettings['arcadeEnableGameTemplate'] == 1)
			{
				$_REQUEST['game'] = $context['game']['id'];
				$context['arcade']['popupscore'] = false;
				$context['template_layers'][] = 'arcade_game';
				$context['sub_template'] = 'arcade_html5_game_play';
				$context['page_title'] = sprintf($txt['arcade_game_play'], $context['game']['name']);
				$context['arcade']['play'] = true;
				$context['linktree'][] = array(
					'url' => $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
					'name' => $context['game']['name'],
				);
				$context['html_headers'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
				var arcade_game_dir = "' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '";
				// ]]></script>';
				if (empty($_SESSION['arcade_isMobilePlay']))
					loadTemplate('ArcadeGame');
				else
				{
					$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">';
					loadTemplate('ArcadeGameMobile');
				}

				return;
			}
			else
			{
				$_REQUEST['game'] = $context['game']['id'];
				$context['arcade']['popupscore'] = false;
				$context['template_layers'][] = 'arcade_game';
				$context['sub_template'] = 'arcade_html5_game_play';
				$context['page_title'] = sprintf($txt['arcade_game_play'], $context['game']['name']);
				$context['arcade']['play'] = true;
				$context['linktree'][] = array(
					'url' => $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
					'name' => $context['game']['name'],
				);
				$context['html_headers'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
				var arcade_game_dir = "' . $arcadeModSettings['gamesUrl'] . '/' . $context['game']['directory'] . '";
				// ]]></script>';
				if (empty($_SESSION['arcade_isMobilePlay']))
					loadTemplate('ArcadeGame');
				else
				{
					$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">';
					loadTemplate('ArcadeGameMobile');
				}

				return;
			}
		}
	}
	elseif (!isset($_REQUEST['xml']) && !isset($_REQUEST['ajax']))
	{
		if (!isset($_SESSION['arcade_play_' . $context['game']['id']]) || !$isCustomGame || isset($_REQUEST['restart']))
			$_SESSION['arcade_play_' . $context['game']['id']] = array();

		$system['play']($context['game'], $_SESSION['arcade_play_' . $context['game']['id']]);
		$_SESSION['arcade_play_extra_' . $context['game']['id']] = $extra;
		$_SESSION['arcade_html5_token'] = array(time(), ArcadeSpecialChars(ArcadeRandomToken(78)));
		$_SESSION['arcade_current_game_internal'] = $context['game']['internal_name'];
		$_SESSION[$_SESSION['arcade_html5_token'][1] . '_flag_' . $_SESSION['arcade_html5_token'][1]] = 0;
		$context['game']['html'] = $system['html'];
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'play' && !empty($context['game']['html']))
		{
			$checkpop = isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1 ? 1 : 0;
			if (empty($_SESSION['arcade']['pop']) && empty($numberPlays)) {
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET num_plays = num_plays + 1
					WHERE id_game = {int:game}',
					array(
						'game' => $context['game']['id'],
					)
				);
				$numberPlays = true;
			}
		}

		/* Layout start */
		if (isset($_REQUEST['gamepopup']) && $_REQUEST['gamepopup'] == 1)
		{
			$context['arcade']['play'] = true;
			$context['template_layers'] = array();
			$context['popup2'] = false;
			$_SESSION['arcade']['gamepopup'] = 1;
			$_REQUEST['game'] = $context['game']['id'];
			$context['sub_template'] = 'arcade_popup_player';
			$context['arcade']['popupscore'] = true;
			return;
		}
		elseif (isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1)
		{
			$context['arcade']['play'] = true;
			$context['template_layers'] = array();
			$context['popup2'] = false;
			$context['arcade']['popupscore'] = !empty($_REQUEST['sameArcadeWindow']) ? false : true;
			$_SESSION['arcade']['pop'] = !empty($_REQUEST['sameArcadeWindow']) ? 0 : 1;
			$_REQUEST['game'] = $context['game']['id'];
			$context['sub_template'] = 'arcade_popup_player';
			return;
		}
		elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'download')
		{
			unset($_SESSION['arcade']['gamepopup']);
			unset($_SESSION['arcade']['gamepop']);
			$context['arcade']['popupscore'] = false;
			$context['template_layers'][] = 'arcade_game';
			$context['sub_template'] = 'arcade_game_play';
			$context['page_title'] = sprintf($txt['arcade_game_play'], $context['game']['name']);
			$context['arcade']['play'] = true;
			$context['linktree'][] = array(
				'url' => $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
				'name' => $context['game']['name'],
			);

			if (empty($_SESSION['arcade_isMobilePlay']))
				loadTemplate('ArcadeGame');
			else
			{
				$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">';
				loadTemplate('ArcadeGameMobile');
			}

			return;
		}
		else
		{
			unset($_SESSION['arcade']['popupscore']);
			unset($_SESSION['arcade']['gamepopup']);
			unset($_SESSION['arcade']['gamepop']);
			$context['arcade']['popupscore'] = false;
			$_SESSION['arcade']['pop'] = false;

			if (empty($context['show_pm_popup']))
				$context['show_pm_popup'] = false;

			if (empty($arcadeModSettings['arcadeEnableGameTemplate']))
				$arcadeModSettings['arcadeEnableGameTemplate'] = false;

			if ($arcadeModSettings['arcadeEnableGameTemplate'] == 1)
			{
				$context['arcade']['popupscore'] = false;
				$context['template_layers'][] = 'arcade_game';
				$context['sub_template'] = 'arcade_game_play';
				$context['page_title'] = sprintf($txt['arcade_game_play'], $context['game']['name']);
				$_REQUEST['game'] = $context['game']['id'];
				$context['arcade']['play'] = true;
				$context['linktree'][] = array(
					'url' => $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
					'name' => $context['game']['name'],
				);

				if (empty($_SESSION['arcade_isMobilePlay']))
					loadTemplate('ArcadeGame');
				else
				{
					$context['html_headers'] .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">';
					loadTemplate('ArcadeGameMobile');
				}

				return;
				// ignore...
				$context['arcade']['play'] = true;
				$context['template_layers'] = array();
				$context['popup2'] = false;
				$_REQUEST['game'] = $context['game']['id'];
				$context['sub_template'] = 'arcade_gameplay';
				return;
			}
			else
			{
				$context['arcade']['popupscore'] = false;
				$context['template_layers'][] = 'arcade_game';
				$context['sub_template'] = 'arcade_game_play';
				$context['page_title'] = sprintf($txt['arcade_game_play' ], $context['game']['name']);
				$_REQUEST['game'] = $context['game']['id'];
				$context['arcade']['play'] = true;
				$context['linktree'][] = array(
					'url' => $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
					'name' => $context['game']['name'],
				);

				if (empty($_SESSION['arcade_isMobilePlay']))
					loadTemplate('ArcadeGame');
				else
				{
					$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">';
					loadTemplate('ArcadeGameMobile');
				}
				return;
			}
		}
	}
	elseif ($isCustomGame)
	{
		$ok = $system['xml_play']($context['game'], $_SESSION['arcade_play_' . $context['game']['id']]);
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'play' && !empty($ok) && empty($numberPlays)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET num_plays = num_plays + 1
				WHERE id_game = {int:game}',
				array(
					'game' => $context['game']['id'],
				)
			);
			$numberPlays = true;
		}
		$context['game']['class']->showAjax();
		obExit(false);
	}
	else
	{
		if (!isset($_SESSION['arcade_play_' . $context['game']['id']]))
			ArcadeXMLOutput(array('check' => '0'), null);

		$ok = $system['xml_play']($context['game'], $_SESSION['arcade_play_' . $context['game']['id']]);

		if (!$ok)
			ArcadeXMLOutput(array('check' => '0'), null);

		if ($ok === true && empty($numberPlays)) {
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET num_plays = num_plays + 1
				WHERE id_game = {int:game}',
				array(
					'game' => $context['game']['id'],
				)
			);
			$numberPlays = true;
		}

		ArcadeXMLOutput(array('check' => '1'), null);
	}

	fatal_error('Hacking attempt...');
}

function ArcadeSubmit($url = '')
{
	global $scripturl, $boarddir, $arcadeModSettings, $modSettings, $txt, $db_prefix, $context, $smcFunc, $user_info;

	clearstatcache();
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$action = !empty($rom) ? 'retro_arch' : 'arcade';
	if (!$system = SubmitSystemInfo()) {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($_REQUEST['end']))
	{
		if (!empty($url))
		{
			//echo '<script type="text/javascript">window.close();</script>';
			echo '<script type="text/javascript">window.top.location.replace("' . $url . '");</script>';
		}
		else {
			arcadeClearSession();
			exit();
		}

		return;
	}

	require_once($boarddir . '/ArcadeSources/' . $system['file']);
	require_once($boarddir . '/ArcadeSources/ArcadeShoutbox.php');
	unset($safeName);

	if ($system['get_game'] !== false)
		$context['game'] = $system['get_game']();

	$gamename = array(
		!empty($_REQUEST['game']) ? ArcadeSessionSanitize($_REQUEST['game']) : '',
		!empty($_REQUEST['gname']) ? ArcadeSessionSanitize($_REQUEST['gname']) : '',
		!empty($_REQUEST['game_name']) ? ArcadeSessionSanitize($_REQUEST['game_name']) : '',
		!empty($_REQUEST['gamename']) ? ArcadeSessionSanitize($_REQUEST['gamename']) : '',
	);

	$safeName = current(array_filter($gamename));
	if (!empty($_SESSION['game_data_backup']))
	{
		$gameData = array_keys($_SESSION['game_data_backup']);
		$safeSearch = ArcadeSessionSanitize(array_search(mb_strtolower($safeName), array_map('mb_strtolower', $gameData)));
	}

	$safe = !empty($safeSearch) ? $gameData[$safeSearch] : (!empty($safeName) ? $safeName : ArcadeRandomToken());
	$context['game'] = empty($context['game']) && !empty($_SESSION['game_data_backup'][$safe]) ? $_SESSION['game_data_backup'][$safe] : $context['game'];

	// eliminate any native SMF back button
	$context['html_headers'] = !empty($context['html_headers']) ? $context['html_headers'] : '';
	if (!$context['game'] || !isset($_SESSION['arcade_play_' . $context['game']['id']]) || !isset($_SESSION['arcade_play_extra_' . $context['game']['id']]))
		$newLink = $scripturl . '?action=' . $action . ';';
	else
		$newLink = !empty($context['game']['id']) ? $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';reload=' . rand(100, 999) : $scripturl . '?action=' . $action . ';';

	$context['html_headers'] .= '
		<script type="text/javascript">
			function arcadeChangeBackButton() {
				var xx = 0, allLinks = document.getElementsByTagName("A");
				if (allLinks && allLinks.length > 0) {
					for (xx=0;xx<allLinks.length;xx++) {
						if (allLinks[xx].innerHTML && allLinks[xx].href && allLinks[xx].innerHTML == "' . $txt['back'] . '") {
							allLinks[xx].href = "' . $newLink . ';";
						}
					}
				}
			}
			if (window.addEventListener)
				window.addEventListener("load", arcadeChangeBackButton, false);
			else if (window.attachEvent)
				window.attachEvent("onload", arcadeChangeBackButton);
		</script>';

	// Check that everything is OK
	if (!$context['game'] || !isset($_SESSION['arcade_play_' . $context['game']['id']]) || !isset($_SESSION['arcade_play_extra_' . $context['game']['id']])) {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error_session', false);
	}

	// clear any cache that queries scores if setting is enabled
	if (!empty($arcadeModSettings['arcadeGamecacheUpdate'])) {
		$queryGamesCache = array(
			'arcade_games_best',
			'arcade_games_longchamps',
			'arcade_games_latestScores',
			'arcade_games_query_small',
			'arcade_champsA_wins',
			'arcade_champsA_gen',
			'arcade_games_latestA',
			'arcade_new_champsA',
			'arcade_champsC_wins',
			'arcade_champsC_gen',
			'arcade_newChampsC',
			'arcade_champsB',
		);
		foreach ($queryGamesCache as $cache) {
			$rom2 = '_rom';
			cache_put_data($cache, array(), 0);
			cache_put_data($cache . $rom2, array(), 0);
		}
	}

	if ($context['game']['score_type'] == 2 || $context['game']['submit_system'] != $system['system'])
	{
		// patch for IBP HTML5 arcade games that did not meet the criteria during game install
		$changeType = true;
		if (stripos($context['game']['submit_system'], 'html5')  !== false)
		{
			if (isset($_REQUEST['act']) && strtolower($_REQUEST['act']) == $action)
			{

				$_REQUEST['action'] = $action;
				$do = isset($_REQUEST['do']) ? mb_strtolower(trim($_REQUEST['do'])) : '';
				if (in_array($do, array('newscore', 'savescore')))
				{
					$changeType = false;
					$randomchar = rand(1, 200);
					$randomchar2 = rand(1, 200);
					$_SESSION['arcade']['ibp_verify'] = array($randomchar, $randomchar2, microtime_float());
				}
			}
		}

		if ($changeType)
		{
			$adjust = ArcadeAdjustSaveType($context['game']['id'], $system['system']);

			if (!$adjust)
			{
				if (!empty($arcadeModSettings['arcade_log_savetype']))
				{
					$gameError = sprintf($txt['arcade_submit_error_configure_log'], $context['game']['name'], $system['system']);
					$err = arcade_html_entity_decode($gameError, 2, 2);
					log_error(ArcadeRemoveDuplicateHyperlink($err), 'debug');
				}
				arcadeClearSession();
				fatal_lang_error('arcade_submit_error_js', false);
			}
			else
			{
				$context['game']['submit_system'] = $system['system'];
				echo '<script type="text/javascript">window.top.close();</script>';
				if (!empty($arcadeModSettings['arcade_log_savetype'])) {
					$gameError = sprintf($txt['arcade_submit_adjust_configure_log'], $context['game']['name'], $system['system']);
					$err = arcade_html_entity_decode($gameError, 2, 2);
					log_error(ArcadeRemoveDuplicateHyperlink($err), 'debug');
				}
			}
		}
	}
	else
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_internal_game_conflicts
			WHERE submit_system_flag = {int:gameid}',
			array(
				'gameid' => $context['game']['id'],
			)
		);
	}

	$session_info = $_SESSION['arcade_play_' . $context['game']['id']];
	$extra = $_SESSION['arcade_play_extra_' . $context['game']['id']];

	$submit_info = $system['info']($context['game'], $session_info);
	unset($_SESSION['arcade_play_' . $context['game']['id']], $_SESSION['arcade_play_extra_' . $context['game']['id']], $_SESSION['game_data_backup']);

	if (empty($context['game']['score_type']) && $submit_info['score'] < 1) {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error_neg', false);
	}

	if (!$submit_info || isset($submit_info['error'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_submit_error', false);
	}

	if (isset($extra['match'])) {
		$id_match = (int) $extra['match'];

		$request = $smcFunc['db_query']('', '
			SELECT m.id_match, m.name, m.current_players, m.num_players, m.current_round, m.num_rounds, m.status
			FROM {db_prefix}arcade_matches AS m
			WHERE m.id_match = {int:match}
				AND status = 1',
			array(
				'match' => $id_match,
			)
		);
		$matchInfo = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!$matchInfo) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		$request = $smcFunc['db_query']('', '
			SELECT id_game
			FROM {db_prefix}arcade_matches_rounds
			WHERE id_match = {int:match}
				AND round = {int:round}',
			array(
				'match' => $id_match,
				'round' => $matchInfo['current_round'],
			)
		);
		$round = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (empty($round)) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		$request = $smcFunc['db_query']('', '
			SELECT game_name
			FROM {db_prefix}arcade_games
			WHERE id_game = {int:gameid}',
			array(
				'gameid' => $round['id_game'],
			)
		);
		while ($gameData = $smcFunc['db_fetch_assoc']($request))
			$gameName = $gameData['game_name'];

		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT status
			FROM {db_prefix}arcade_matches_players
			WHERE id_match = {int:match}
				AND id_member = {int:member}',
			array(
				'match' => $id_match,
				'round' => $matchInfo['current_round'],
				'member' => $user_info['id'],
			)
		);
		$result = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!$result) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		if ($result['status'] == 2)
		{
			$smcFunc['db_insert']('insert',
				'{db_prefix}arcade_matches_results',
				array(
					'id_match' => 'int',
					'id_member' => 'int',
					'round' => 'int',
					'score' => 'float',
					'duration' => 'float',
					'end_time' => 'int',
					'score_status' => 'string-30',
					'validate_hash' => 'string-255',
				),
				array(
					$id_match,
					$user_info['id'],
					$matchInfo['current_round'],
					$submit_info['score'],
					$submit_info['duration'],
					$submit_info['end_time'],
					$submit_info['cheating'],
					!empty($submit_info['hash']) ? $submit_info['hash'] : 'v1' . sha1($submit_info['score']),
				),
				array('id_match', 'id_member', 'round')
			);
			arcade_shout_scores(array($gameName, $matchInfo['name'], $matchInfo['current_round'], $submit_info['score']), 'arena');
			matchUpdatePlayers($id_match, array($user_info['id']), 3);
			call_integration_hook('integrate_arcade_match', array($id_match, $user_info['id'], $gameName, $matchInfo['current_round'], $matchInfo['name'], $submit_info['score'], $submit_info['duration'], $submit_info['end_time']));
			redirectexit('action=arcade;sa=viewMatch;match=' . $id_match);
		}
	}

	if ($user_info['is_guest'] && !$context['arcade']['can_submit'])
	{
		$_SESSION['arcade']['highscore'] = array(
			'id' => false,
			'game' => $context['game']['id'],
			'score' => $submit_info['score'],
			'position' => 0,
			'start' => 0,
			'saved' => false,
			'error' => 'arcade_no_permission'
		);

		arcade_shout_scores(array($context['game']['name'], $submit_info['score']), 'guest');
		call_integration_hook('integrate_arcade_guest', array($context['game'], $submit_info['score']));
		redirectexit('action=arcade;sa=highscore;game=' . $context['game']['id'] . ';#commentform3');
	}
	elseif ($user_info['is_guest'])
	{
		$member = array(
			'id' => 0,
			'name' => isset($_SESSION['playerName']) ? $_SESSION['playerName'] : '',
			'ip' => arcade_get_client_ip()
		);
	}
	else
	{
		$member = array(
			'id' => $user_info['id'],
			'name' => $user_info['name'],
			'ip' => arcade_get_client_ip()
		);
	}

	$score = array(
		'score' => $submit_info['score'],
		'duration' => $submit_info['duration'],
		'endTime' => $submit_info['end_time'],
		'status' => $submit_info['cheating'],
		'hash' => !empty($submit_info['hash']) ? $submit_info['hash'] : '',
	);

	unset($_SESSION['arcade_current_score_id']);
	if ($context['arcade']['can_submit'] && empty($member['name']))
	{
		$_SESSION['save_score'] = array($context['game'], $member, $score);
		redirectexit('action=arcade;sa=save;game=' . $context['game']['id']);
	}
	elseif ($context['arcade']['can_submit'])
	{
		$save = SaveScore($context['game'], $member, $score);
		if (empty($save['id']))
		{
			$_SESSION['arcade']['highscore'] = array(
				'id' => false,
				'game' => $context['game']['id'],
				'score' => $score['score'],
				'start' => 0,
				'saved' => false,
				'error' => isset($save['error']) ? $save['error'] : 'arcade_saving_error',
			);
		}
		else
		{
			$_SESSION['arcade']['highscore'] = array(
				'id' => $save['id'],
				'position' => $save['position'],
				'game' => $context['game']['id'],
				'newChampion' => $save['newChampion'],
				'personalBest' => $save['isPersonalBest'],
				'score' => $score['score'],
				'start' => floor(($save['position'] - 1) / $context['scores_per_page']) * $context['scores_per_page'],
				'saved' => true
			);
			$_SESSION['arcade_current_score_id'] = $save['id'];
			arcade_shout_scores(array($context['game']['name'], $score['score']), 'member');
			call_integration_hook('integrate_arcade_score', array($context['game'], $member, $score));
			// fix for ST-Shop v4.1.12 ~ :(
			$getScoreHooks = !empty($modSettings['integrate_arcade_score']) ? explode(',', $modSettings['integrate_arcade_score']) : [];
			if (!in_array('Shop\Integration\Addons\Arcade\Arcade::score', $getScoreHooks) && class_exists('Shop\Integration\Addons\Arcade\Arcade') && !empty($modSettings['Shop_enable_shop'])) {
				// if (Shop\Shop::$version == '4.1.12')
				if (!empty($arcadeModSettings['arcadeEnableStShopPatch'])) {
					Shop\Integration\Addons\Arcade\Arcade::score($context['game'], $member, $score);
				}
			}
		}
	}
	else
	{
		$_SESSION['arcade']['highscore'] = array(
			'id' => false,
			'game' => $context['game']['id'],
			'score' => $submit_info['score'],
			'position' => 0,
			'start' => 0,
			'saved' => false,
			'error' => 'arcade_no_permission'
		);
	}

	if (!isset($_REQUEST['xml']))
		redirectexit('action=arcade;sa=highscore;game=' . $context['game']['id'] . ';start=' . $_SESSION['arcade']['highscore']['start']) . ';reload=' . mt_rand(1, 9999) . ';#commentform3';
	else
		redirectexit('action=arcade');
}

function ArcadeSave_Guest()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $func, $boarddir, $sourcedir, $smcFunc, $user_info;

	if (!isset($_REQUEST['name']) && !isset($_SESSION['playerName']))
	{
		$context['arcade']['submit'] = 'askname';

		return ArcadeHighscore();
	}
	elseif (isset($_REQUEST['name']) || isset($_SESSION['playerName']))
	{
		$_REQUEST['game'] = isset($_SESSION['save_score']) && isset($_SESSION['save_score'][0]['id']) && floatval($_SESSION['save_score'][0]['id']) > 0 ? $_SESSION['save_score'][0]['id'] : 0;
		require_once($sourcedir . '/Subs-Post.php');
		$guestName = isset($_REQUEST['name']) ? $_REQUEST['name'] : (isset($_SESSION['playerName']) ? $_SESSION['playerName'] : '');
		preparsecode($guestName);
		if (!empty($guestName) && allowedTo('arcade_submit') && !empty($user_info['is_guest']))
		{
			require_once($sourcedir . '/Subs-Members.php');

			//checkSession('post');

			$name = htmlspecialchars($guestName);

			if (isReservedName($name, 0, true, false))
			{
				$context['arcade']['submit'] = 'askname';
				$context['arcade']['error'] = 'bad_name';

				return ArcadeHighscore();
			}

			$_SESSION['playerName'] = $name;
			$_SESSION['save_score'][1]['name'] = $name;
			$_SESSION['save_score'][1]['id'] = 0;
		}
		else
			redirectexit('action=arcade;sa=highscore;game=' . $_REQUEST['game'] . ';reload=' . mt_rand(1, 9999));

		if (!empty($user_info['is_guest']) && allowedTo('arcade_submit') && isset($_SESSION['save_score'][0]) && isset($_SESSION['save_score'][1]) && isset($_SESSION['save_score'][2]))
			SaveScore($_SESSION['save_score'][0], $_SESSION['save_score'][1], $_SESSION['save_score'][2]);

		//unset($_SESSION['save_score']);
		redirectexit('action=arcade;sa=highscore;game=' . $_REQUEST['game'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3');
	}
}

function ArcadeHighscoreXml()
{
	global $smcFunc, $arcadeModSettings, $user_info, $arcade_version, $txt;
	if (!isset($_REQUEST['game']))
		$scores = array();

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$game = getGameInfo($_REQUEST['game'], false, $rom);

	if (!isset($game))
		$scores = array();
	else
	{
		$result = $smcFunc['db_query']('', '
			SELECT
				sc.id_score, sc.score, sc.end_time AS time, sc.duration, sc.comment,
				sc.position, sc.score_status, IFNULL(mem.id_member, 0) AS id_member,
				IFNULL(mem.real_name, sc.player_name) AS real_name
			FROM  {db_prefix}arcade_scores AS sc
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sc.id_member)
			WHERE sc.id_game = {int:game}
			ORDER BY position
			LIMIT {int:start}, {int:scores_per_page}',
			array(
				'game' => $game['id'],
				'empty' => '',
				'start' => 0,
				'scores_per_page' => 50,
			)
		);

		while ($score = $smcFunc['db_fetch_assoc']($result))
		{
			if (empty($score['real_name']))
				$score['real_name'] = $txt['guest'];
			elseif (!empty($score['id_member']) && !empty($arcadeModSettings['arcadeMaxScores'])) {
				$countScore[$score['id_member']] = empty($score['id_member']) ? 1 : $score['id_member'] + 1;
				if ($countScore[$score['id_member']] > (int)$arcadeModSettings['arcadeMaxScores'])
					continue;
			}

			if ($user_info['id'] == $score['id_member'] && time() - $score['time'] < 3)
				$scoreId = $score['id_score'];

			$scores[$score['id_score']] = array(
				'id' => $score['id_score'],
				'member' => array(
					'name' => $score['real_name'],
				),
				'position' => $score['position'],
				'score' => comma_format(floatval($score['score'])),
				'time' => timeformat($score['time']),
				'duration' => $score['duration'],
				'scoreStatus' => $score['score_status'],
			);
		}
		$smcFunc['db_free_result']($result);
	}

	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$readonly = $smfVersion == 'v2.1' ? 'readonly' : 'readonly="readonly"';
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	$output = '
<html>
<head>
	<title>XML Scores Output</title>
</head>
<body>
<textarea rows="25" cols="250" style="border:none;" ' . $readonly . '>
<?xml version="1.0"?>
<!-- Generated with SMF Arcade ' . $arcade_version . ' -->';

	if (!empty($scores))
		foreach ($scores as $scorex)
		{
			$output .= '
<hiscore>
<name>' . $scorex['member']['name'] . '</name>
<score>' . $scorex['score'] . '</score>
</hiscore>';
		}
	else
		$output .= '
<hiscore>
<name>N/A</name>
<score>0</score>
</hiscore>';

	$output .= '
</textarea>
</body>
</html>';

	echo $output;
	obExit(false);
}

function ArcadeHighscoreList()
{
	global $user_info;
	$highscore = isset($_REQUEST['sa']) && stripos($_REQUEST['sa'], 'myhighscore') !== FALSE ? 'myhighscore' : 'highscore';
	ArcadeHighscore($highscore);
}

function ArcadeHighscore($selfScorelist = 'highscore')
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc, $user_info, $boarddir, $sourcedir, $settings;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$action = !empty($rom) ? 'retro_arch' : 'arcade';
	// Is game set
	if (!isset($_REQUEST['game'])) {
		arcadeClearSession();
		/*
		if (!empty($user_info['is_guest'])) {
			redirectexit($scripturl . '?action=' . $action);
		}
		*/
		fatal_lang_error('arcade_game_not_found', false);
	}
	// Get game info
	if (!($game = getGameInfo($_REQUEST['game'], false, $rom))) {
		arcadeClearSession();
		/*
		if (!empty($user_info['is_guest'])) {
			redirectexit($scripturl . '?action=' . $action);
		}
		*/
		fatal_lang_error('arcade_game_not_found', false);
	}
	if (!$game['highscore_support']) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_score_support', false);
	}

	$scoreVar = isset($_REQUEST['score']) ? filter_var($_REQUEST['score'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
	list($newScore, $scoreCount, $userCountArray, $context['arcade_scorevar'], $context['arcade_scorelist'], $_SESSION['arcade']['gamepopup'], $_SESSION['arcade']['pop']) = array(false, 0, array(), $scoreVar, $selfScorelist, false, false);
	$maxScores = !empty($arcadeModSettings['arcadeMaxScores']) ? (int)$arcadeModSettings['arcadeMaxScores'] : 0;
	$pop = isset($_REQUEST['pop']) && $_REQUEST['pop'] == 1 ? 1 : 0;
	$pop = !empty($_SESSION['arcade']['gamepopup']) || !empty($_SESSION['arcade']['pop']) || !empty($pop) ? 1 : 0;
	$start = !empty($_REQUEST['start']) ? preg_replace("/[^0-9.]/", "", $_REQUEST['start']) : '';
	$start = abs(intval($start));
	ArcadePlayTabs($game);
	require_once($sourcedir . '/Subs-Post.php');

	if (isset($_SESSION['arcade']['highscore']['game']) && $_SESSION['arcade']['highscore']['game'] == $game['id'])
	{
		// For highlight
		$newScore = $_SESSION['arcade']['highscore']['saved'];
		$newScore_id = $_SESSION['arcade']['highscore']['id'];

		$context['arcade']['submit'] = 'newscore';

		$score = &$_SESSION['arcade']['highscore'];

		$context['arcade']['new_score'] = array(
			'id' => $score['id'],
			'saved' => $score['saved'],
			'error' => !empty($score['error']) ? $score['error'] : '',
			'score' => comma_format(floatval($score['score'])),
			'position' => isset($score['position']) ? $score['position'] : 0,
			'can_comment' => $context['arcade']['can_comment_own'] || $context['arcade']['can_comment_any'],
			'is_new_champion' => !empty($score['champion']),
			'is_personal_best' => !empty($score['best']),
		);

		if (!isset($_GET['start']))
			$_REQUEST['start'] = $score['start'];
	}
	elseif (isset($_SESSION['arcade']['highscore']))
		unset($_SESSION['arcade']['highscore']);

	// Edit Comment
	if (isset($_COOKIE['arcadegameid' . $game['id']])) {
		$expiry = filter_var($_COOKIE['arcadegameid' . $game['id']], FILTER_SANITIZE_NUMBER_INT);
		if (time() > $expiry) {
			unset($_COOKIE['arcadegameid' . $game['id']]);
		}
	}
	if (empty($arcadeModSettings['arcadeDisableComments']) && isset($_REQUEST['csave']) && isset($_REQUEST['score']) && isset($_REQUEST['new_comment' . $_REQUEST['score']]) && !isset($_COOKIE['arcadegameid' . $game['id']]))
	{
		if (isset($_SESSION['arcade']['highscore']))
			unset($_SESSION['arcade']['highscore']);

		require_once($sourcedir . '/Subs-Post.php');

		$where = $context['arcade']['can_comment_any'] ? '1 = 1' : ($context['arcade']['can_comment_own'] ? 'id_member = {int:member}' : '0 = 1');
		$_REQUEST['new_comment' . $_REQUEST['score']] = strtr($smcFunc['htmlspecialchars']($_REQUEST['new_comment' . $_REQUEST['score']], ENT_QUOTES), array("\r" => '', "\n" => '', "\t" => ''));
		setcookie('arcadegameid' . $game['id'], time() + 10, time() + 10, "/");
		preparsecode($_REQUEST['new_comment' . $_REQUEST['score']]);

		if (!empty($arcadeModSettings['arcadeCommentLen']))
			$_REQUEST['new_comment' . $_REQUEST['score']] = substr($_REQUEST['new_comment' . $_REQUEST['score']], 0, $arcadeModSettings['arcadeCommentLen']);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_scores
			SET comment = {string:comment}
			WHERE id_score = {int:score}
				AND ' . $where,
			array(
				'score' => (int) $_REQUEST['score'],
				'comment' => $_REQUEST['new_comment' . $_REQUEST['score']],
				'member' => $user_info['id'],
			)
		);

		$_SESSION['arcade']['highscore']['saved'] = true;

		if (isset($_REQUEST['xml']))
		{
			ArcadeXMLOutput(
				array(
					'comment' => parse_bbc($_REQUEST['new_comment' . $_REQUEST['score']]),
					'message' => $txt['arcade_comment_saved']
				),
				null
			);
		}
		redirectexit('action=arcade;sa=' . $context['arcade_scorelist'] . ';' . ($pop == 1 ? 'pop=1;' : '') . 'game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . (!empty($start) ? ';start=' . $start : '') . ';#commentform3');
	}
	// Quick Management
	elseif ($context['arcade']['can_admin_arcade'] && isset($_REQUEST['qaction']))
	{
		checkSession('request');

		if ($_REQUEST['qaction'] == 'delete' && !empty($_REQUEST['scores']))
		{
			require_once($sourcedir . '/ArcadeMaintenance.php');
			deleteScores($game, $_REQUEST['scores']);
			$request = $smcFunc['db_query']('', '
				SELECT id_game, score_type, extra_data
				FROM {db_prefix}arcade_games
				WHERE id_game = {int:gameid}
				LIMIT 1', array('gameid' => $game['id']));

			while ($row = $smcFunc['db_fetch_assoc']($request))
				ArcadeFixScores($row['id_game'], $row['score_type']);

			$smcFunc['db_free_result']($request);
		}

		redirectexit('action=arcade;sa=' . $context['arcade_scorelist'] . ';' . ($pop == 1 ? 'pop=1;' : '') . 'game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . (!empty($start) ? ';start=' . $start : '') . ';#commentform3');
	}

	// How many scores there are
	$result = $smcFunc['db_query']('', '
		SELECT sc.id_member
		FROM {db_prefix}arcade_scores as sc
		WHERE sc.id_game = {int:game}' . ($context['arcade_scorelist'] == 'highscore' || !empty($user_info['is_guest']) ? '' : ' AND sc.id_member = {int:memberid}') . '
		LIMIT {int:start}, {int:scores_per_page}',
		array(
			'game' => $game['id'],
			'start' => !empty($start) ? $start : 0,
			'scores_per_page' => $context['scores_per_page'],
			'maxscores' => $maxScores,
			'memberid' => !empty($user_info['is_guest']) ? $user_info['id'] : 0,
		)
	);

	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if (!empty($maxScores)) {
			$memberId = $score['id_member'];
			$userCountArray[] = $memberId;
			$countVal = array_count_values($userCountArray);
			if ($countVal[$memberId] > $maxScores)
				continue;
		}
		$scoreCount++;
	}

	$smcFunc['db_free_result']($result);
	$context['page_index'] = constructPageIndex($scripturl .'?action=arcade;sa=' . $context['arcade_scorelist'] . ';' . ($pop == 1 ? 'pop=1;' : '') . 'game=' . $game['id'] . ';reload=' . mt_rand(1, 9999), $_REQUEST['start'], $scoreCount, $context['scores_per_page'], false);
	$context['page_index'] = preg_replace('~href=("|\')(.+?)\1~', 'href=$1$2;commentform3$1', $context['page_index']);
	$context['arcade_scorelist_toggle'] = $scripturl . '?action=arcade;sa=' . ($context['arcade_scorelist'] == 'highscore' ? 'myhighscore' : 'highscore') . ';' . ($pop == 1 ? 'pop=1;' : '') . 'game=' . $game['id'] . ';start=' . $_REQUEST['start'] . ';reload=' . mt_rand(1, 9999);

	// Actual query
	$result = $smcFunc['db_query']('', '
		SELECT
			sc.id_score, sc.score, sc.end_time AS time, sc.duration, sc.comment, sc.id_member,
			sc.position, sc.score_status, IFNULL(mem.id_member, 0) AS id_member,
			IFNULL(mem.real_name, sc.player_name) AS real_name
		FROM  {db_prefix}arcade_scores AS sc
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sc.id_member)
		WHERE sc.id_game = {int:game}' . ($selfScorelist == 'myhighscore' && !empty($user_info['is_guest']) ? ' AND sc.id_member = {int:memberid}' : '') . '
		ORDER BY position
		LIMIT {int:start}, {int:scores_per_page}',
		array(
			'game' => $game['id'],
			'empty' => '',
			'start' => !empty($start) ? $start : 0,
			'scores_per_page' => $context['scores_per_page'] >= $scoreCount ? $context['scores_per_page'] : $scoreCount,
			'memberid' => empty($user_info['is_guest']) ? $user_info['id'] : 0,
			'maxscores' => $maxScores,
		)
	);

	list($context['arcade']['scores'], $userCountArray, $context['game'], $positionCount) = array(array(), array(), $game, 0);
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if (!empty($maxScores)) {
			$memberId = $score['id_member'];
			$userCountArray[] = $memberId;
			$countVal = array_count_values($userCountArray);
			if ($countVal[$memberId] > $maxScores)
				continue;
		}
		censorText($score['comment']);
		$positionCount++; // $score['position']
		if (empty($score['real_name']))
			$score['real_name'] = $txt['guest'];

		if ($user_info['id'] == $score['id_member'] && time() - $score['time'] < 3)
			$scoreId = $score['id_score'];
		if (!empty($score['comment']))
			preparsecode($score['comment']);

		$context['arcade']['scores'][$score['id_score']] = array(
			'id' => $score['id_score'],
			'own' => $user_info['id'] == $score['id_member'],
			'member' => array(
				'id' => $score['id_member'],
				'name' => $score['real_name'],
				'link' => !empty($score['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' . $score['real_name'] . '</a>' : $score['real_name'],
			),
			'position' => $positionCount > 0 && $positionCount < 4 ? '<img style="max-width: 2.188em; max-height: 1.5em;" src="' . $settings['default_images_url'] . '/arc_icons/' . $positionCount . '.gif" alt="' . $positionCount . '" />' : $positionCount,
			'score' => comma_format(floatval($score['score'])),
			'time' => timeformat($score['time']),
			'duration' => $score['duration'],
			'scoreStatus' => $score['score_status'],
			'comment' => parse_bbc(!empty($score['comment']) ? $score['comment'] : $txt['arcade_no_comment']),
			'raw_comment' => $score['comment'],
			'can_edit' => $user_info['id'] == $score['id_member'] ? ($context['arcade']['can_comment_own'] || $context['arcade']['can_comment_any']) : $context['arcade']['can_comment_any'],
			'edit' => !empty($_POST['p']) ? 1 : 0,
		);
	}
	$smcFunc['db_free_result']($result);

	if (isset($_REQUEST['edit']) && !isset($_REQUEST['score']))
	{
		$gamename = isset($_REQUEST['gnamex']) ? ArcadeSpecialChars($_REQUEST['gnamex'], 'name') : '';
		$_REQUEST['score'] = -99;
		$idgame = 0;

		if (!empty($gamename))
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_game, internal_name
				FROM {db_prefix}arcade_games
				WHERE internal_name = {string:gamename}
				LIMIT 1',
				array(
					'gamename' => $gamename,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$idgame = $row['id_game'];
			$smcFunc['db_free_result']($request);

			if (!empty($idgame))
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_score, id_game, id_member, score, end_time
					FROM {db_prefix}arcade_scores
					WHERE id_member = {int:member}
						AND {int:current} - end_time < {int:timecount}
						AND id_game = {int:idgame}
					ORDER BY id_score DESC
					LIMIT 1',
					array(
						'member' => $user_info['id'],
						'timecount' => 5,
						'idgame' => $idgame,
						'current' => time(),
					)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
					$_REQUEST['score'] = $row['id_score'];
				$smcFunc['db_free_result']($request);
			}
		}
	}

	if (isset($_REQUEST['edit']) && is_numeric($_REQUEST['score']) && !isset($_REQUEST['end']))
		$context['arcade']['scores'][$_REQUEST['score']]['edit'] = !empty($context['arcade']['scores'][$_REQUEST['score']]['can_edit']) ? true : false;

	if ($newScore)
		$context['arcade']['scores'][$newScore_id]['highlight'] = true;

	// Template
	$popScore = !empty($scoreId) ? (int)$scoreId : -1;
	$context['arcade_empty_title_cell'] = '<div class="arcade_bottom_border" style="display: table-cell;border-bottom: 1px double;height: 1.5em;max-height: 1.5em;"><span style="display: none;">&nbsp;</span></div>';
	$context['arcade_empty_cell'] = '<div class="minwidth" style="display: table-cell;width: 0px;height: 1.5em;max-height: 1.5em;"><span style="display: none;">&nbsp;</span></div>';
	$context['page_title'] = sprintf($txt['arcade_view_highscore'], $game['name']);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;sa=play;game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . ';#playgame',
		'name' => $game['name'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;sa=highscore;' . ($pop == 1 ? 'pop=1;' : '') . 'game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3',
		'name' => $txt['arcade_viewscore'],
	);

	if ($pop == 1)
	{
		$context['html_headers'] .= !isset($_REQUEST['end']) ? '
		<script type="text/javascript">
			window.location.href = "' . $scripturl . '?action=arcade;sa=' . $context['arcade_scorelist'] . ';pop=1;game=' . $game['id'] . ';edit;score=' . $popScore . ';reload=' . mt_rand(1, 9999) . (!empty($start) ? ';start=' . $start : '') . ';#commentform3";
		</script>' : '
		<script type="text/javascript">
			window.location.href = "' . $scripturl . '?action=arcade;sa=' . $context['arcade_scorelist'] . ';pop=1;game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . (!empty($start) ? ';start=' . $start : '') . ';#commentform3";
		</script>';
		require_once($settings['default_theme_dir'] . '/ArcadeGamePop.template.php');
		arcadePopHighscoreTemplate();
	}
	else
	{
		$lastid = isset($_POST['lastid']) ? floatval(preg_replace("/[^-0-9\.]/","", $_POST['lastid'])) : 0;
		$lastArcadeTime = isset($_REQUEST['time']) && ctype_digit($_REQUEST['time']) ? intval($_REQUEST['time']) : 0;
		$checkLastTime = $lastArcadeTime < 1 ? 'false' : 'true';
		$context['html_headers'] .= !isset($_REQUEST['end']) ? '
		<script type="text/javascript">
			if (false == ' . $checkLastTime . ')
				var myArcadeWindow = window.location.replace("' . $scripturl . '?action=arcade;sa=' . $context['arcade_scorelist'] . ';' . (!empty($lastid) ? 'lastid=' . $lastid . ';' : '') . 'game=' . $game['id'] . ';edit;score=' . $popScore . ';reload=' . mt_rand(1, 9999) . (!empty($start) ? ';start=' . $start : '') . ';time=' . time() . ';#commentform3");
			if(myArcadeWindow && !myArcadeWindow.closed) {
				myArcadeWindow.preventDefault();myArcadeWindow.close();
			}
				setTimeout(throwArcadeErr("Closing window ~ Ignore this warning unless it is looping"), 1000);
		</script>' : '
		<script type="text/javascript">
			if (false === ' . $checkLastTime . ')
				var myArcadeWindow = window.location.replace("' . $scripturl . '?action=arcade;sa=' . $context['arcade_scorelist'] . ';game=' . $game['id'] . ';reload=' . mt_rand(1, 9999) . (!empty($start) ? ';start=' . $start : '') . ';time=' . time() . ';#commentform3");
			if(myArcadeWindow && !myArcadeWindow.closed)
				setTimeout(throwArcadeErr("Closing window ~ Ignore this warning unless it is looping"), 1000);
		</script>';
		$context['template_layers'][] = 'arcade_game';
		$context['sub_template'] = 'arcade_game_highscore';

		// Do we show remove score functions?
		$context['arcade']['show_editor'] = $context['arcade']['can_admin_arcade'];
		if (empty($_SESSION['arcade_isMobilePlay']))
			loadTemplate('ArcadeGame');
		else
		{
			loadTemplate('ArcadeGameMobile');
		}
	}

	return true;
}

function ArcadeUpdateComment()
{
	global $sourcedir, $context, $smcFunc, $user_info, $arcadeModSettings;

	if (!empty($arcadeModSettings['arcadeDisableComments']))
		return;

	$updateArray = array('game', 'score', 'csave', 'new_comment');
	list($data, $newComment) = array(array(), '');
	foreach ($updateArray as $key) {
		$data[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
	}
	$post_data = array_filter($data);
	if (!empty($post_data) && count($post_data) > 3) {
		require_once($sourcedir . '/Subs-Post.php');

		$where = $context['arcade']['can_comment_any'] ? '1 = 1' : ($context['arcade']['can_comment_own'] ? 'id_member = {int:member}' : '0 = 1');
		preparsecode($post_data['new_comment']);
		censorText($post_data['new_comment']);
		if (!empty($arcadeModSettings['arcadeCommentLen'])) {
			$_REQUEST['new_comment' . $_REQUEST['score']] = isset($_REQUEST['new_comment' . $_REQUEST['score']]) ? $_REQUEST['new_comment' . $_REQUEST['score']] : '';
			$_REQUEST['new_comment' . $_REQUEST['score']] = substr($_REQUEST['new_comment' . $_REQUEST['score']], 0, $arcadeModSettings['arcadeCommentLen']);
		}

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_scores
			SET comment = {string:comment}
			WHERE id_score = {int:score}
				AND ' . $where,
			array(
				'score' => (int) $post_data['score'],
				'comment' => $post_data['new_comment'],
				'member' => $user_info['id'],
			)
		);

		$request = $smcFunc['db_query']('', '
			SELECT comment
			FROM {db_prefix}arcade_scores
			WHERE score = {int:scoreid}
			LIMIT 1',
			array(
				'scoreid' => (int) $post_data['score'],
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$newComment = $row['comment'];
		$smcFunc['db_free_result']($request);

		if (!empty($newComment) && $newComment == $post_data['new_comment'])
			echo json_encode('score updated');
	}
}

function template_arcade_popup_player()
{
	global $scripturl, $txt, $context, $settings;

	$myTemplate = $settings['default_theme_dir'] . '/ArcadeGamePop.template.php';
	if (file_exists($myTemplate))
	{
		require_once($myTemplate);
		arcadePopupTemplate();
	}
	return;
}

function ArcadeAdjustSaveType($gameid, $submit_system)
{
	global $smcFunc, $arcadeModSettings;

	$rom = 0; // future implementation

	if (empty($arcadeModSettings['arcadeAdjustType']) && !empty($submit_system))
	{
		$request = $smcFunc['db_query']('', '
			SELECT submit_system_flag
			FROM {db_prefix}arcade_internal_game_conflicts
			WHERE submit_system_flag = {int:gameid}
			LIMIT 1',
			array(
				'gameid' => $gameid,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$idgame = $row['id_game'];
		$smcFunc['db_free_result']($request);

		if (!empty($idgame))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET submit_system = {string:system}
				WHERE id_game = {int:gameid}',
				array(
					'gameid' => (int)$gameid,
					'system' => $submit_system,
					'rom_flag' => !empty($rom) ? 1 : 0,
				)
			);

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_internal_game_conflicts
				WHERE submit_system_flag = {int:gameid}',
				array(
					'gameid' => $gameid,
				)
			);

			return true;
		}
	}

	if (!empty($arcadeModSettings['arcadeAdjustType']) && !empty($submit_system))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_games
			SET submit_system = {string:system}
			WHERE id_game = {int:gameid}',
			array(
				'gameid' => (int)$gameid,
				'system' => $submit_system,
				'rom_flag' => !empty($rom) ? 1 : 0,
			)
		);

		return true;
	}

	return false;
}

function ArcadeRemoveDuplicateHyperlink($html)
{

	preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $html, $match);
	$links = !empty($match[0]) ? $match[0] : array();
	$links2 = array_unique( $links );

	if (count($links) > count($links2))
		$string = preg_replace('#^(.*)<a[^>]*?>(.*?)</a>(.*?)#im', '$1$3', $html);
	else
		$string = $html;

	return $string;
}

function ArcadePlayTabs($game)
{
	global $context, $arcadeModSettings, $scripturl, $settings;

	$context['game'] = $game;
	$action = !empty($game['rom_flag']) ? 'retro_arch' : 'arcade';
	// Play link
	$context['arcade']['buttons']['play'] =  array(
		'text' => 'arcade_play',
		'image' => 'arcade_play.gif', // Theres no image for this included (yet)
		'url' => !empty($context['arcade']['play']) ? $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999). ';#playgame" onclick="arcadeRestart(); return false;' : $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999). ';#playgame',
		'lang' => true
	);

	$context['arcade']['buttons']['fullscreen'] =  array(
		'text' => 'arcadeFullPopup',
		'image' => 'arcade_fullscreen.gif', // Theres no image for this included (yet)
		'url' => !empty($context['arcade']['play']) && empty($context['game']['rom_flag']) && empty($context['game']['rom_game']) ? $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';pop=1;full=1;sameArcadeWindow=1;reload=' . mt_rand(1, 9999). ';#playgame" onclick="arcadeRestart(); return false;' : $scripturl . '?action=' . $action . ';sa=play;game=' . $context['game']['id'] . ';pop=1;full=1;sameArcadeWindow=1;reload=' . mt_rand(1, 9999). ';#playgame',
		'lang' => true
	);


	// Highscores link if it is supported
	if ($context['game']['highscore_support'])
		$context['arcade']['buttons']['score'] =  array(
			'text' => 'arcade_viewscore',
			'image' => 'arcade_viewscore.gif', // Theres no image for this included (yet)
			'url' => $scripturl . '?action=' . $action . ';sa=highscore;game=' . $context['game']['id'] . ';reload=' . mt_rand(1, 9999) . ';#commentform3',
			'lang' => true
		);

	// Random game
	$context['arcade']['buttons']['random'] =  array(
		'text' => 'arcade_random_game',
		'image' => 'arcade_random.gif', // Theres no image for this included (yet)
		'url' => $scripturl . '?action=' . $action . ';sa=play;random;reload=' . mt_rand(1, 9999) . ';#playgame',
		'lang' => true
	);

	if ($context['arcade']['can_admin_arcade'])
		$context['arcade']['buttons']['edit'] =  array(
			'text' => 'arcade_edit_game',
			'image' => 'arcade_edit_game.gif', // Theres no image for this included (yet)
			'url' => $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $context['game']['id'],
			'lang' => true
		);

	/* Links if they are supported */
	$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
	$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && !allowedTo('arcade_download') ? false : $arcadeModSettings['arcadeEnableDownload'];
	$enableGameDownload = empty($context['game']['download']) && empty($context['game']['allowed_download']) ? false : $enableGameDownload;
	$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;


	if (!empty($enableGameDownload))
	{
		$context['arcade']['buttons']['download'] =  array(
			'text' => 'arcade_download_game',
			'image' => 'arc_icons/dl_btn.png', // Use image from pdl mod -  / Themes / DEFAULT_THEME / images / arc_icons / dl_btn.png
			'url' => $context['game']['downlink'],
			'lang' => true
		);
	}

	if  (($arcadeModSettings['arcadeEnableReport'] == true) && (allowedTo('arcade_report') == true) && empty($context['game']['report_id']))
	{
		$tempName = str_replace('-', '_', $context['game']['name']);
		$context['arcade']['buttons']['report'] =  array(
			'text' => 'pdl_report',
			'id' => 'reportid_' . $context['game']['id'],
			'title' => $context['game']['name'],
			'image' => 'arc_icons/arcade_report.gif',
			'url' => $scripturl . '?action=' . $action . ';sa=report;game=' . $context['game']['id'],
			'lang' => true
		);
	}

	$context['arcade_ratecode'] = '';
	$rating = $context['game']['rating'];

	if ($context['arcade']['can_rate'])
	{
		// Can rate
		for ($i = 1; $i <= 5; $i++)
		{
			if ($i <= $rating)
				$context['arcade_ratecode'] .= '<a href="' . $scripturl . '?action=' . $action . ';sa=rate;game=' . $context['game']['id'] . ';rate=' . $i . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star.gif" alt="*" /></a>';
			else
				$context['arcade_ratecode'] .= '<a href="' . $scripturl . '?action=' . $action . ';sa=rate;game=' . $context['game']['id'] . ';rate=' . $i . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="*" /></a>';
		}
	}
	else
	{
		// Can't rate
		$context['arcade_ratecode'] = str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/arcade_star.gif" alt="*" />' , $rating);
		$context['arcade_ratecode'] .= str_repeat('<img src="' . $settings['default_images_url'] . '/arc_icons/arcade_star2.gif" alt="" />' , 5 - $rating);
	}
}

?>