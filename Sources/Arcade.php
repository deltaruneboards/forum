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

function Arcade($rom = 0)
{
	global $context, $smcFunc, $scripturl, $txt, $boarddir, $arcadeModSettings, $modSettings, $settings, $boardurl, $user_info, $sourcedir;

	// Do we have permission?
	$allowed = allowedTo('arcade_view_retro_arch') ? true : (allowedTo('arcade_admin') ? true : false);
	$action = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) && strtolower($_REQUEST['action']) == 'retro_arch' ? 'retro_arch' : 'arcade';
	if (!$allowed && $action == 'retro_arch') {
		arcadeClearSession();
		fatal_lang_error('cannot_arcade_play_retro_arch', false);
	}
	if (!allowedTo('arcade_view')) {
		arcadeClearSession();
		fatal_lang_error('cannot_arcade_view', false);
	}

	// set the encrypted cookie
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAncillary.php');
	$expectedCookieValue = !empty($arcadeModSettings['arcadeCookieEncryptionCipher']) ? $arcadeModSettings['arcadeCookieEncryptionCipher'] : '';
	if (!empty($expectedCookieValue) && empty($_COOKIE['arcade_suggest_timer'])) {
		setcookie(
			"arcade_suggest_timer",
			arcadeEncryptionCipher($expectedCookieValue . '_' . time(), '', 'encrypt'),
			[
				'expires' => time() + (30 * 60),
				'path' => '/',
				'secure' => true,
				'httponly' => true,
				'samesite' => 'Strict'
			]
		);
	}

	if (empty($context['arcade_rom_initiated'])) {
		// Load Arcade
		$context['arcade_rom_initiated'] = true;
		$rom = isset($_REQUEST['action']) && stripos($_REQUEST['action'], 'retro_arch') !== FALSE ? 1 : $rom;
		loadArcade('normal', '', $rom);
		$rom2 = !empty($rom) ? '_rom' : '';
		$rom3 = !empty($rom) ? 'rom_' : '';
		$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
		$allowedRom = !empty($arcadeModSettings['arcadeRetroArchEnabled']) && allowedTo('arcade_view_retro_arch') ? true : (allowedTo('arcade_admin') ? true : false);
		$_SESSION['arcade_rom_initiate'] = !empty($rom) ? $rom : '';
		$context['arcade_tabs'] = !empty($context['arcade_tabs']) ? $context['arcade_tabs'] : array();
		$user_info['arcade_settings'] = $user_info['is_guest'] ? loadMyArcadeSettings(0) : loadMyArcadeSettings($user_info['id']);

		// Fatal error if Arcade is disabled
		if (empty($rom) && empty($arcadeModSettings['arcadeEnabled']) && !allowedTo('arcade_admin')) {
			arcadeClearSession();
			fatal_lang_error('arcade_disabled', false);
		}
		elseif (!empty($rom) && empty($arcadeModSettings['arcadeRetroArchEnabled']) && !allowedTo('arcade_admin')) {
			arcadeClearSession();
			fatal_lang_error('arcade_play_retro_arch_disabled', false);
		}
		elseif (!empty($rom) && !$allowedRom) {
			arcadeClearSession();
			fatal_lang_error('cannot_arcade_play_retro_arch', false);
		}

		// the arena has been temporarily disabled in v2.7.0.1 pending a revamp in v2.7.0.X
		$arcadeModSettings['arcadeArenaEnabled'] = 0;

		// Information for actions (file, function, [permission])
		$subActions = array(
			// ArcadeArena.php
			// 'arena' => array('ArcadeArena.php', 'ArcadeMatchList'),
			// 'newMatch' => array('ArcadeArena.php', 'ArcadeNewMatch', 'arcade_create_match'),
			// 'newMatch2' => array('ArcadeArena.php', 'ArcadeNewMatch2', 'arcade_create_match'),
			// 'viewMatch' => array('ArcadeArena.php', 'ArcadeViewMatch'),
			// ArcadeList.php
			'list' => array('ArcadeList.php', 'ArcadeList'),
			'suggest' => array('ArcadeList.php', 'ArcadeXMLSuggest'),
			'search' => array('ArcadeList.php', 'ArcadeList'),
			'rate' => array('ArcadeList.php', 'ArcadeRate'),
			'favorite' => array('ArcadeList.php', 'ArcadeFavorite'),
			// ArcadeGame.php
			/* Game Popup in Iframe  */
			'popup' => array('ArcadePopup-smf2.php', 'ArcadePopup'),
			'play' => array('ArcadeGame.php', 'ArcadePlay', 'arcade_play'),
			'highscore' => array('ArcadeGame.php', 'ArcadeHighscore'),
			'myhighscore' => array('ArcadeGame.php', 'ArcadeHighscoreList'),
			'arcadecscore' => array('ArcadeGame.php', 'ArcadeUpdateComment'),
			'highscorexml' => array('ArcadeGame.php', 'ArcadeHighscoreXml'),
			'save' => array('ArcadeGame.php', 'ArcadeSave_Guest'),
			// ArcadeStats.php
			'stats' => array('ArcadeStats.php', 'ArcadeStatistics'),
			'submit' => array('ArcadeGame.php', 'ArcadeSubmit'),
			// Arcade Online
			'online' => array('ArcadeOnline.php', 'ArcadeOnline'),
			// Advanced
			'download' => array('ArcadeDownload.php', 'ArcadeDownload'),
			'report' => array('ArcadeReport.php', 'ArcadeReport'),
			'shout' => array('Subs-ArcadeSkinMainB.php', 'ArcadeShout'),
			'shoutboxC' => array('Subs-ArcadeSkinCrux.php', 'ArcadeShoutC'),
			'randomxml' => array('Subs-ArcadePlus.php', 'arcade_random_xml'),
			// IBP Submit
			'ibpverify' => array('Submit-ibp.php', 'ArcadeVerifyIBP'),
			'ibpsubmit2' => array('ArcadeGame.php', 'ArcadeSubmit'),
			'ibpsubmit3' => array('ArcadeGame.php', 'ArcadeSubmit'),
			// v2 Submit
			'v2Start' => array('Submit-v2game.php', 'ArcadeV2Start'),
			'v2Hash' => array('Submit-v2game.php', 'ArcadeV2Hash'),
			'v2Score' => array('Submit-v2game.php', 'ArcadeV2Score'),
			'v2Submit' => array('ArcadeGame.php', 'ArcadeSubmit'),
			// v3Arcade
			'vbSessionStart' => array('Submit-v3arcade.php', 'ArcadeVbStart'),
			'vbPermRequest' => array('Submit-v3arcade.php', 'ArcadeVbPermRequest'),
			'vbBurn' => array('ArcadeGame.php', 'ArcadeSubmit'),
			// HTML5
			'html5Game' => array('Submit-HTML5.php', 'ArcadeHTML5Game'),
			'html52Game' => array('Submit-HTML52.php', 'ArcadeHTML52Game'),
			'html53Game' => array('Submit-HTML53.php', 'ArcadeHTML53Game'),
			// ROM
			'romGame' => array('Submit-ROM.php', 'ArcadeROMGame'),
			'romRemoteGet' => array('ArcadeRetroArch.php', 'ArcadeRetroArchRemoteGet'),
			// Shoutbox
			'shouts' => array('ArcadeShoutbox.php', 'ArcadeShouts'),
			'newShout' => array('ArcadeShoutbox.php', 'add_to_arcade_shoutbox_ajax'),
			'imageenhancement' => array('Subs-Arcade.php', 'ArcadeListImageEnhance'),
		);

		if (!empty($rom)) {
			$allowed = !empty($arcadeModSettings['arcadeRetroArchEnabled']) && allowedTo('arcade_view_retro_arch') ? true : (allowedTo('arcade_admin') ? true : false);
			if (!$allowed) {
				arcadeClearSession();
				fatal_lang_error('cannot_arcade_play_retro_arch', false);
			}

			if (!defined('ARCADE_EmulatorJS'))
				define('ARCADE_EmulatorJS', '1');

			$full = isset($_REQUEST['full']) && is_numeric($_REQUEST['full']) && intval($_REQUEST['full']) == 1 ? 1 : 0;
			$pop = isset($_REQUEST['pop']) && is_numeric($_REQUEST['pop']) && intval($_REQUEST['pop']) == 1 ? 1 : 0;

			if (!empty($full) || !empty($pop) || !empty($_SESSION['arcade_isMobilePlay']))
				$subActions['play'] = array('ArcadeRetroArch.php', 'ArcadeRetroArch');

			// Template
			$context['arcade_retro_arch']['tab_data']['title'] = $txt['arcade_rom_list_title'];
			$context['arcade_retro_arch']['tab_data']['description'] = $txt['arcade_rom_list_desc'];
		}

		$_SESSION['arcade_highscore_list'] = !empty($_SESSION['arcade_highscore_list']) ? $_SESSION['arcade_highscore_list'] : 'highscore';
		if (isset($_REQUEST['sa']) && stripos($_REQUEST['sa'], 'highscore') !== FALSE && empty($rom)) {
			if (stripos($_REQUEST['sa'], 'myhighscore') !== FALSE) {
				$_SESSION['arcade_highscore_list'] = 'myhighscore';
				$_REQUEST['sa'] = 'myhighscore';
			}
			elseif ($_SESSION['arcade_highscore_list'] == 'myhighscore' && stripos($_REQUEST['sa'], 'highscore') !== FALSE && stripos($_REQUEST['sa'], 'myhighscore') === FALSE) {
				$_REQUEST['sa'] = 'highscore';
			}
			elseif ($_SESSION['arcade_highscore_list'] == 'myhighscore') {
				$_SESSION['arcade_highscore_list'] = 'myhighscore';
				$_REQUEST['sa'] = 'myhighscore';
			}
			else {
				$_SESSION['arcade_highscore_list'] = 'highscore';
				$_REQUEST['sa'] = 'highscore';
			}
		}

		if (empty($arcadeModSettings['arcadeArenaEnabled']))
			unset($subActions['arena'], $subActions['newMatch'], $subActions['newMatch2'], $subActions['viewMatch']);

		// Fix for broken games which do not send sa/do=submit
		if (empty($rom)) {
			if (isset($_POST['game']) && isset($_POST['score']) && !isset($_REQUEST['sa']))
				$_REQUEST['sa'] = 'submit';
			// Short urls like index.php?game=1 or index.php/game,1.html
			elseif (isset($_REQUEST['game']) && is_numeric($_REQUEST['game']) && !isset($_REQUEST['sa']))
				$_REQUEST['sa'] = 'play';
			elseif (isset($_REQUEST['match']) && is_numeric($_REQUEST['match']) && !isset($_REQUEST['sa']))
				$_REQUEST['sa'] = 'viewMatch';
			// Let Custom ("php games") do ajax/etc magic
			elseif (isset($_REQUEST['game']) && isset($_REQUEST['xml']) && !isset($_REQUEST['sa']))
				$_REQUEST['sa'] = 'custData';
		}

		$arcade_version = $arcadeModSettings['arcadeVersion'];
		$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
		$arcadeSA = isset($_REQUEST['sa']) && !is_array($_REQUEST['sa']) ? $_REQUEST['sa'] : '';
		$_REQUEST['sa'] = !empty($arcadeSA) && !empty($subActions[$arcadeSA]) ? $arcadeSA : 'list';
		$sortbydefault = !empty($arcadeModSettings['arcadeListSort']) ? intval($arcadeModSettings['arcadeListSort']) : 0;
		$arcadeSortBy = function() use ($sortbydefault){
			switch($sortbydefault) {
				case 0:
					$sort = 'a2z';
					break;
				case 1:
					$sort = 'ztoa';
					break;
				case 2:
					$sort = 'age;dir=asc';
					break;
				case 3:
					$sort = 'age';
					break;
				case 4:
					$sort = 'plays';
					break;
				case 5:
					$sort = 'plays_reverse';
					break;
				case 6:
					$sort = 'champion';
					break;
				case 7:
					$sort = 'champs';
					break;
				case 8:
					$sort = 'rating';
					break;
				case 9:
					$sort = 'favorites';
					break;
				default:
					$sort = 'a2z';
			}
			return $sort;
		};
		$arcadeSortBy = in_array($arcadeSortBy, array('champion', 'champs')) && !empty($rom) ? 'a2z' : $arcadeSortBy;

		$context['curved'] = true;
		$context['arcade_smf_version'] = 'v2.1';
		$context['current_arcade_sa'] = !empty($_REQUEST['sa']) ? $_REQUEST['sa'] : 'list';
		$_SESSION['current_cat'] = !empty($_SESSION['current_cat']) ? $_SESSION['current_cat'] : 'all';
		$_SESSION['arcade_sortby' . $rom2] = !empty($_SESSION['arcade_sortby' . $rom2]) ? $_SESSION['arcade_sortby' . $rom2] : $arcadeSortBy();
		$sort = ($_SESSION['current_cat'] == 0 || $_SESSION['current_cat'] == 'all') && $_SESSION['arcade_sortby' . $rom2] == 'a2z' ? '' : ';sortby=reset';
		$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
		list($user_gametype, $javascript, $checkRar, $countGames, $archivetype, $archiveContent, $gametype, $gametypeContent) = array('', '', false, 0, array(), array(), array(), array());
		$_SESSION['arcade_isMobile'] = empty($user_info['arcade_settings']['detect_mobile']) && !empty($_SESSION['arcade_isMobile']) ? $_SESSION['arcade_isMobile'] : 0;
		$_SESSION['arcadeNewSessionCheck'] = !empty($_SESSION['arcadeNewSessionCheck']) && !empty($_SESSION['arcade_isMobilePlay']) ? 1 : 0;
		$arcadeModSettings['arcadeRandomIdVar'] = !empty($arcadeModSettings['arcadeRandomIdVar']) ? $arcadeModSettings['arcadeRandomIdVar'] : 'smfarcade20201017';
		$arcadeModSettings['arcadeEnableDownload'] = !empty($arcadeModSettings['arcadeEnableDownload']) ? $arcadeModSettings['arcadeEnableDownload'] : false;
		$enableGameDownload = !empty($arcadeModSettings['arcadeDownloadHideLink']) && !allowedTo('arcade_download') ? false : $arcadeModSettings['arcadeEnableDownload'];
		$arcadeModSettings['arcadeEnableReport'] = !empty($arcadeModSettings['arcadeEnableReport']) ? $arcadeModSettings['arcadeEnableReport'] : false;
		$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
		$waitImgWidth = strlen($txt['arcade_download_game']) > 3 && strlen($txt['arcade_download_game']) < 13 ? strlen($txt['arcade_download_game']) : 8;
		loadArcadeSkin($rom, true);
		if (!$user_info['is_guest'])
		{
			$arcadeSettings = $user_info['arcade_settings'];
			$type = !empty($arcadeSettings['archive_type']) ? $arcadeSettings['archive_type'] : 0;
		}
		else
			$type = isset($_SESSION['arcade_download_type']) ? $_SESSION['arcade_download_type'] : (!empty($arcadeModSettings['arcade_gz']) ? (int)$arcadeModSettings['arcade_gz'] : 0);

		switch($type)
		{
			case 0:
				$compress = class_exists('ZipArchive') ? 'zip' : 'tar';
				break;
			case 1:
				$compress = 'tar';
				break;
			case 2:
				$compress = 'tar.gz';
				break;
			case 3:
				$compress = 'rar';
				break;
			default:
				$compress = 'tar';
		}

		// admin message if arcade is disabled for regular members
		$addAdminJavaScript = empty($arcadeModSettings['arcadeEnabled']) && allowedTo('arcade_admin') ? '
				smfArcadeAdminMessage();' : '';

		// RC3 patch for the moderation log
		$modSettings['moderatelog_enabled'] = !empty($modSettings['modlog_enabled']) ? $modSettings['modlog_enabled'] : 0;

		$context['html_headers'] .= '
		<script type="text/javascript">
			localStorage.setItem("arcadeSmfIdVar", "' . $arcadeModSettings['arcadeRandomIdVar'] . '");
			localStorage.setItem("arcadeEnterComment", "' . $txt['arcade_enter_comment'] . '");
			function setArcadeCookie(cname, cvalue, exdays) {
				var d = new Date();
				d.setTime(d.getTime() + (exdays*24*60*60*1000));
				var expires = "expires="+ d.toUTCString();
				document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			}
			function getArcadeCookieFunc(name) {
				var dc = document.cookie;
				var prefix = name + "=";
				var begin = dc.indexOf("; " + prefix);
				if (begin == -1) {
					begin = dc.indexOf(prefix);
					if (begin != 0) return null;
				}
				else
				{
					begin += 2;
					var end = document.cookie.indexOf(";", begin);
					if (end == -1) {
					end = dc.length;
					}
				}
				return decodeURI(dc.substring(begin + prefix.length, end));
			}
			setArcadeCookie("arcadeScrWidth", screen.width, 1);
			setArcadeCookie("arcadeScrHeight", screen.height, 1);
		</script>';

		$context['html_headers'] .= '
		<script>
			$.gameIdParam = function(name, str){
				var results = new RegExp("[\?;]" + name + "=([^;#]*)").exec(str);
				if (results==null) {
				   return null;
				}
				return decodeURI(results[1]) || 0;
			}
			function fetchArcadeHeader(url, wch) {
				try {
					var req=new XMLHttpRequest();
					req.open("HEAD", url, false);
					req.send(null);
					if(req.status== 200){
						return req.getResponseHeader(wch);
					}
					else return false;
				} catch(er) {
					return er.message;
				}
			}
			var arcadeCurrentDL_value, arcadeCurrentDLPath_value, arcadeDownloadData = "", arcadeDownloadDataType = "", currentArcadeDLindex, arcadeArchiveType, arcadePrefix;
			$(document).ready(function(){
				$(".button_strip_arcade").on("click", function(){
					window.location.href = "' . $scripturl. '?action=arcade;sa=list;sortby=age;category=all";
					return false;
				});
				$(".button_strip_romarcade").on("click", function(){
					window.location.href = "' . $scripturl. '?action=retro_arch;sa=list;sortby=age;category=all";
					return false;
				});
				$(\'a[href^="' . $scripturl . '?action=' . (!empty($rom) ? 'retro_arch' : 'arcade') . ';sa=download;game="]\').click(function(){
					let currentDateX = new Date();
					const currentDateTime = +new Date(currentDateX);
					currentArcadeDLindex = $(this);
					arcadeCurrentDL_value = $(this).html();
					arcadeCurrentDLPath = $(this).prop("href");
					arcadeDownloadData = $(this).data("internalname");
					arcadeDownloadDataType = $(this).data("gametype") ? $(this).data("gametype") : "none";
					switch(arcadeDownloadDataType){
						case "rom":
							arcadePrefix = "rom_";
							break;
						case "html5":
							arcadePrefix = "html5_";
							break;
						case "html53":
							arcadePrefix = "";
							break;
						default:
							arcadePrefix = "game_";
					}
					arcadeArchiveType = arcadeDownloadDataType == "rom" ? ".zip" : ".' . (!empty($compress) ? $compress : 'zip') . '";
					var arcadeCurrentDLPath_value = $.gameIdParam("game", arcadeCurrentDLPath);
					if (arcadeDownloadData) {
						var arcadeDownloadPath = "' . $boardurl . '/games_download/" + arcadePrefix + arcadeDownloadData + arcadeArchiveType;
						$(this).html(\'<img style="height:1ch;width:' . $waitImgWidth . 'cw;" src="' . $settings['default_theme_url'] . '/images/arc_icons/wait_icon.gif" alt="Compressing..." title="Compressing archive" />\');
						var arcadeCheckDownload = setInterval(function(){
							var arcadeGetLastModified = fetchArcadeHeader(arcadeDownloadPath, "Last-Modified");
							if (arcadeGetLastModified) {
								const currentDate = new Date();
								var date1 = +new Date(currentDate);
								var date2 = +new Date(arcadeGetLastModified);
								if ((date1 - date2) < 5000 || (date1 - currentDateTime) > 30000) {
									console.log("Game#: " + arcadeCurrentDLPath_value + " ~ compression completed");
									currentArcadeDLindex.html(arcadeCurrentDL_value);
									clearInterval(arcadeCheckDownload);
								}
							}
						}, 2000);
					}
				});
			});
		</script>';

		if (!empty($_SESSION['arcade_isMobilePlay']) && isset($_REQUEST['sa']) && in_array(strtolower($_REQUEST['sa']), array('arena', 'newmatch', 'newmatch2', 'viewmatch')))
			$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<meta name="mobile-web-app-capable" content="yes">
			<meta name="HandheldFriendly" content="true">';
		elseif (!empty($_SESSION['arcade_isMobilePlay']) && isset($_REQUEST['sa']) && $_REQUEST['sa'] != 'highscore')
			$context['html_headers'] .= '
			<meta id="Viewport" name="viewport" content="width=device-width, maximum-scale=3.0, user-scalable=yes">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<meta name="mobile-web-app-capable" content="yes">
			<meta name="HandheldFriendly" content="true">';
		elseif (!empty($_SESSION['arcade_isMobilePlay']))
			$context['html_headers'] .= '
			<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<meta name="mobile-web-app-capable" content="yes">
			<meta name="HandheldFriendly" content="true">';

		/*$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeMobileDetect.js?' . $suffixVersion. '"></script>';*/

		if (substr($scripturl, 0, 6) == 'https:' && empty($arcadeModSettings['arcade_contentSecurityPolicy']))
			$context['html_headers'] .= '
		<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">';

		// a new session sometimes requires a second page load to properly detect mobile
		if (empty($arcadeModSettings['arcadeRomToggle']) && allowedTo('arcade_view_retro_arch') && !empty($rom)) {
			$context['arcade_tabs']['romarcade'] =  array(
				'text' => 'rom_arcade',
				'image' => 'arcade.gif',
				'url' => $scripturl . '?action=retro_arch' . $sort,
				'active' => (empty($rom) && in_array($context['current_arcade_sa'], array('list', 'online', 'highscore', 'play')) ? true : (in_array($context['current_arcade_sa'], array('list', 'online', 'play')) ? true : null)),
				'lang' => true,
			);

			$context['arcade_tabs']['arcade'] =  array(
				'text' => 'arcade',
				'image' => 'arcade.gif',
				'url' => $scripturl . '?action=arcade' . $sort,
				'active' => false,
				'lang' => true,
			);
		}
		elseif (empty($arcadeModSettings['arcadeRomToggle']) && allowedTo('arcade_view_retro_arch') && empty($rom)) {
			$context['arcade_tabs']['arcade'] =  array(
				'text' => 'arcade',
				'image' => 'arcade.gif',
				'url' => $scripturl . '?action=arcade' . $sort,
				'active' => (empty($rom) && in_array($context['current_arcade_sa'], array('list', 'online', 'highscore', 'play')) ? true : (in_array($context['current_arcade_sa'], array('list', 'online', 'play')) ? true : null)),
				'lang' => true,
			);

			$context['arcade_tabs']['romarcade'] =  array(
				'text' => 'rom_arcade',
				'image' => 'arcade.gif',
				'url' => $scripturl . '?action=retro_arch' . $sort,
				'active' => false,
				'lang' => true,
			);
		}
		else {
			$context['arcade_tabs']['arcade'] = array(
				'text' => 'arcade',
				'image' => 'arcade.gif',
				'url' => empty($rom) ? $scripturl . '?action=arcade' . $sort : $scripturl . '?action=retro_arch' . $sort,
				'active' => (empty($rom) && in_array($context['current_arcade_sa'], array('list', 'online', 'highscore', 'play')) ? true : (in_array($context['current_arcade_sa'], array('list', 'online', 'play')) ? true : null)),
				'lang' => true
			);
		}

		if (!empty($arcadeModSettings['arcadeArenaEnabled']) && empty($rom))
			$context['arcade_tabs']['arcade_arena'] = array(
				'text' => 'arcade_arena',
				'image' => 'arcade_arena.gif',
				'url' => $scripturl . '?action=arcade;sa=arena;reload=' . mt_rand(0, 9999) . ';#arenamatch',
				'active' => in_array($context['current_arcade_sa'], array('arena', 'newMatch', 'newMatch2', 'viewMatch')) ? true : null,
				'lang' => true
			);

		$context['arcade_tabs']['arcade_stats'] = array(
			'text' => 'arcade_stats',
			'image' => 'arcade_stats.gif',
			'url' => $scripturl . '?action=' . (!empty($rom) ? 'retro_arch' : 'arcade') . ';sa=stats;', // . (!empty($rom) ? 'retro_arch=rom' : ''),
			'active' => in_array($context['current_arcade_sa'], array('stats')) ? true : null,
			'lang' => true
		);

		if (allowedTo('arcade_admin'))
			$context['arcade_tabs']['arcade_administrator'] = array(
				'text' => 'arcade_administrator',
				'image' => 'arcade_administrator.gif',
				'url' => $scripturl . '?action=admin;area=arcade',
				'lang' => true
			);

		if (!$user_info['is_guest'] && !empty($user_info['arcade_settings']))
		{
			$userArchive = !empty($user_info['arcade_settings']['archive_type']) ? $user_info['arcade_settings']['archive_type'] : -1;
			$userGametype = !empty($user_info['arcade_settings']['arcade_gametype']) ? $user_info['arcade_settings']['arcade_gametype'] : -1;
		}
		elseif (!$user_info['is_guest'])
		{
			loadArcadeSettings($user_info['id']);
			if (empty($rom)) {
				$userArchive = !empty($user_info['arcade_settings']['archive_type']) ? $user_info['arcade_settings']['archive_type'] : -1;
				$userGametype = !empty($user_info['arcade_settings']['arcade_gametype']) ? $user_info['arcade_settings']['arcade_gametype'] : -1;
			}
			else {
				$userArchive = !empty($user_info['arcade_settings']['archive_type_rom']) ? $user_info['arcade_settings']['archive_type_rom'] : -1;
				$userGametype = !empty($user_info['arcade_settings']['arcade_gametype_rom']) ? $user_info['arcade_settings']['arcade_gametype_rom'] : -1;
			}
		}
		else
			list($userArchive, $userGametype) = array(-1, -1);

		if (!empty($arcadeModSettings['arcade_gz_user']) && allowedTo('arcade_download_type') && !empty($enableGameDownload))
		{
			$x = 0;
			$typeArray = empty($rom) ? explode('|', $txt['arcade_compression']) : array('zip');
			$arcadeModSettings['arcade_gz'] = !empty($arcadeModSettings['arcade_gz']) ? $arcadeModSettings['arcade_gz'] : (class_exists('ZipArchive') ? 0 : 1);
			$arcadeModSettings['arcade_gz'] = $userArchive != -1 ? $userArchive : $arcadeModSettings['arcade_gz'];
			$selected = 'class="active"';
			$paddingLeft = 'padding-left: 0.375em;';
			$_SESSION['arcade_download_type'] = !empty($rom) ? 'zip' : (isset($_SESSION['arcade_download_type']) ? $_SESSION['arcade_download_type'] : $arcadeModSettings['arcade_gz']);
			$archivetype = isset($_REQUEST['archive']) && empty($rom) ? $_REQUEST['archive'] : '';
			if (!empty($archivetype))
				ArcadeSelectType($typeArray);

			$currentUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
			$locate = empty($rom) ? stripos($currentUri, 'index.php?action=arcade') : stripos($currentUri, 'index.php?action=retro_arch');
			$currentLink = $boardurl . '/' . substr($currentUri, $locate);
			foreach(array_reverse($typeArray) as $typex)
				$currentLink = str_replace(';archive=' . $typex, '', $currentLink);
			if (empty($rom))
				$currentLink = str_replace('index.php?action=arcade', 'index.php?action=arcade;archive=', $currentLink);
			else
				$currentLink = str_replace('index.php?action=retro_arch', 'index.php?action=retro_arch;archive=', $currentLink);

			// check if RAR package is available
			if (empty($arcadeModSettings['arcadeDownloadShellEnable']) || !empty($rom))
				$checkRar = false;
			elseif (is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'))
			{
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				{
					if (!empty($arcadeModSettings['arcadeDownloadWinRarDir']) && is_dir($arcadeModSettings['arcadeDownloadWinRarDir']))
						@chdir($arcadeModSettings['arcadeDownloadWinRarDir']);
					if (is_dir("\Program Files (x86)\WinRAR"))
						@chdir("\Program Files (x86)\WinRAR");
					elseif (is_dir("\Program Files\WinRAR"))
						@chdir("\Program Files\WinRAR");

					$checkRar = (`where WinRAR.exe`);
				}
				else
					$checkRar = (`type -P grep`);
			}
			else
				$checkRar = false;

			foreach ($typeArray as $type)
			{
				if (!$checkRar && $type == 'rar')
					continue;

				$archiveContent[] = array(
					'link' => $scripturl . '?action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';archive=' . $type,
					'text' => trim($type)
				);

				$x++;
			}

			$archivetype = array(
				'text' => 'arcade_user_archive_type_button',
				'image' => 'arcade_user_archive_type.gif',
				'url' => $scripturl . '?action=' . (empty($rom) ? 'arcade;archive=tar.gz' : 'retro_arch;archive=zip'),
				'lang' => true,
				'is_last' => true,
			);
		}

		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.rom_flag, game.member_groups AS game_groups, game.id_cat, cat.member_groups AS cat_groups,
			IFNULL(cat.member_groups, {string:empty_string}) AS cat_groups
			FROM {db_prefix}arcade_games as game
			LEFT JOIN {db_prefix}arcade_categories AS cat ON (cat.id_cat = game.id_cat)
			WHERE id_game > 0' . $whererom,
			array(
				'empty_string' => '',
				'rom_flag' => (!empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0),
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$groups = explode(',', $row['game_groups']);
			$cat_groups = explode(',', $row['cat_groups']);
			list($cat_result, $result) = array(array(1), array());
			$result = array_intersect($groups, $user_info['groups']);
			if (!empty($cat_groups) && !empty($row['id_cat']))
				$cat_result = array_intersect($cat_groups, $user_info['groups']);

			if (!empty($result) && !empty($cat_result))
			{
				$countGames = 1;
				break;
			}
			if ($user_info['is_admin'])
			{
				$countGames = 1;
				break;
			}
		}
		$smcFunc['db_free_result']($request);

		if (allowedTo('arcade_gametype_select') && !empty($countGames))
		{
			$typeCheckArray = empty($rom) ? explode('|', $txt['arcade_select_gametype']) : array_keys($txt['arcade_select_gametype_rom']);
			$langCheckArray = empty($rom) ? explode('|', $txt['arcade_select_gametype_english']) : array_values($txt['arcade_select_gametype_rom']);
			list($checkSubSystem, $typeArray, $langArray, $x, $y) = array(array(), array('all'), array($langCheckArray[0]), 0, 0);
			foreach ($typeCheckArray as $checkType)
			{
				if (empty($rom))
					$result = $smcFunc['db_query']('', '
						SELECT submit_system
						FROM {db_prefix}arcade_games
						WHERE submit_system = {string:subsystem}
						LIMIT 1',
						array(
						'subsystem' => $checkType)
					);
				else
					$result = $smcFunc['db_query']('', '
						SELECT rom_system
						FROM {db_prefix}arcade_games
						WHERE rom_system = {string:subsystem}
						LIMIT 1',
						array(
						'subsystem' => $checkType)
					);

				while ($row = $smcFunc['db_fetch_assoc']($result))
				{
					$typeArray[] = $checkType;
					$langArray[] = $langCheckArray[$y];
				}
				$smcFunc['db_free_result']($result);
				$y++;
			}
			$selected = ' class="active"';
			$paddingLeft = 'padding-left: 0.375em;';
			$gamesavetype = isset($_REQUEST['gametype']) ? $_REQUEST['gametype'] : '';
			$_SESSION['arcade_gametype_select' . $rom2] = !empty($gamesavetype) ? $gamesavetype : (isset($_SESSION['arcade_gametype_select' . $rom2]) ? $_SESSION['arcade_gametype_select' . $rom2] : 'all');
			$_SESSION['arcade_gametype_select' . $rom2] = !empty($gamesavetype) && $userGametype != -1 && isset($typeArray[$userGametype]) ? $typeArray[$userGametype] : $_SESSION['arcade_gametype_select' . $rom2];

			if (!empty($gamesavetype))
				ArcadeSelectGameType($typeArray);

			foreach ($typeArray as $type)
			{
				$null = empty($gamesavetype) && empty($_SESSION['arcade_gametype_select' . $rom2]) && $type == 'all' ? true : false;
				$gametypeContent[] = array(
					'link' => $scripturl . '?action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';gametype=' . $typeArray[$x],
					'text' => trim($langArray[$x])
				);


				$x++;
			}

			$gametype = array(
				'text' => 'arcade_user_gametype_button',
				'image' => 'arcade_user_gametype.gif',
				'url' => $scripturl . '?action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';gametype=all',
				'lang' => true,
				'is_last' => true,
			);

		}

		// the custom select menu for SMF buttons ~ use class for SMF defaults or id for custom
		list($x, $y) = array(0, 0);
		$javascript .= '
		<script type="text/javascript">
			function propertyFromSmfStylesheet(selector, selector2, attribute) {
				var value;
				try {
					[].some.call(document.styleSheets, function (sheet) {
						return [].some.call(sheet.rules, function (rule) {
							if (rule.selectorText && selector2 == "" && rule.selectorText.search(selector) != -1) {
								return [].some.call(rule.style, function (style) {
									if (attribute === style) {
										value = rule.style.getPropertyValue(attribute);
										return true;
									}

									return false;
								});
							}
							else if (rule.selectorText && rule.selectorText.search(selector) != -1 && rule.selectorText.search(selector2) != -1) {
								return [].some.call(rule.style, function (style) {
									if (attribute === style) {
										value = rule.style.getPropertyValue(attribute);
										return true;
									}

									return false;
								});
							}
							return false;
						});
					});
				}
				finally {
					return "";
				}
				return value;
			}
			function smfArcadeAdminMessage() {
				var smfArcadeAdminMessage = document.getElementById("main_content_section");
				if (smfArcadeAdminMessage) {
					var smfArcadeAdminMessageDiv = document.createElement("DIV");
					smfArcadeAdminMessageDiv.className = "errorbox";
					smfArcadeAdminMessageDiv.innerHTML = "' . $txt['arcade_admin_general_mode'] . '";
					smfArcadeAdminMessage.insertAdjacentElement("afterbegin", smfArcadeAdminMessageDiv);
				}
			}
			function smfArcadeDropDown() {' . $addAdminJavaScript . '
				if (' . (!empty($_SESSION['arcade_isMobilePlay']) ? 'true' : 'false') . ' == false && ' . (!empty($_SESSION['arcadeNewSessionCheck']) ? 'true' : 'false') . ' == false && arcadeIsMobile == false)
					window.location.replace(window.location.href);
				// var arcadeBgColor = propertyFromSmfStylesheet(".event", "", "color");
				var arcadeHover, arcadeNoHover;
				var arcadeBgColor = propertyFromSmfStylesheet("button", "", "background-color");
				arcadeBgColor = arcadeBgColor == "undefined" || arcadeBgColor == null ? "gray" : arcadeBgColor;
				arcadeHover = propertyFromSmfStylesheet("buttonlist", ":hover", "color");
				arcadeNoHover = propertyFromSmfStylesheet("buttonlist", "", "color");
				if (arcadeHover == null || arcadeHover == "undefined")
					arcadeHover = propertyFromSmfStylesheet("button", ":hover", "color");
				if (arcadeNoHover == null || arcadeNoHover == "undefined")
					arcadeNoHover = propertyFromSmfStylesheet("button", "", "color");
				arcadeHover = arcadeHover == "undefined" || arcadeHover == null || arcadeHover == arcadeNoHover ? "#CCC" : arcadeHover;
				arcadeNoHover = arcadeNoHover == "undefined" || arcadeNoHover == null ? "#000" : arcadeNoHover;
				var container = document.createElement("div");
				container.style = "display: none;position: absolute;border-radius: 0.6em;min-width: 7em;overflow: auto;box-shadow: 0.5em 0.5em 1.0em 0.5em rgba(0,0,0,0.2);z-index: 1;overflow: hidden;";
				container.style.backgroundColor = "inherit";
				container.id = "archivetype";
				container.onmouseleave = function() {document.getElementById("archivetype").style.display = "none";}
				var container2 = document.createElement("div");
				container2.style = "display: none;background-color: initial;position: absolute;border-radius: 0.6em;min-width: 7em;overflow: auto;box-shadow: 0.5em 0.5em 1.0em 0.5em rgba(0,0,0,0.2);z-index: 1;overflow: hidden;";
				container2.style.backgroundColor = "inherit";
				container2.id = "gametype";
				container2.onmouseleave = function() {document.getElementById("gametype").style.display = "none";}';

		foreach ($archiveContent as $archive)
		{
			$x++;
			$javascript .= '
				var $arcadeLink' . $x . ' = $("<span />");
				$arcadeLink' . $x . '.attr("id", "arcadeLinkId' . $x . '");
				$arcadeLink' . $x . '.css({"padding-left":"0.7em","margin-left":"0em","left":"-0.3em","text-decoration":"none","display":"block","border-radius":"0.3em","position":"relative","width":"100%","color":arcadeNoHover});
				$arcadeLink' . $x . '.on( "mouseenter", function() {$("#arcadeLinkId' . $x . '").css("color", arcadeHover);});
				$arcadeLink' . $x . '.on( "mouseleave", function() {$("#arcadeLinkId' . $x . '").css("color", arcadeNoHover);});
				$arcadeLink' . $x . '.on( "click", function() {$(location).attr("href", "' . $archive['link'] . '");});
				$arcadeLink' . $x . '.text("' . $archive['text'] . '");
				$(container).append($arcadeLink' . $x . ');';
		}

		foreach ($gametypeContent as $gametypes)
		{
			$y++;
			$javascript .= '
				var $arcadeLinkz' . $y . ' = $("<span />");
				$arcadeLinkz' . $y . '.attr("id", "arcadeLinkzId' . $y . '");
				$arcadeLinkz' . $y . '.css({"padding-left":"0.7em","margin-left":"0em","left":"-0.3em","text-decoration":"none","display":"block","border-radius":"0.3em","position":"relative","width":"100%","color":arcadeNoHover});
				$arcadeLinkz' . $y . '.on( "mouseenter", function() {$("#arcadeLinkzId' . $y . '").css("color", arcadeHover);});
				$arcadeLinkz' . $y . '.on( "mouseleave", function() {$("#arcadeLinkzId' . $y . '").css("color", arcadeNoHover);});
				$arcadeLinkz' . $y . '.on( "click", function() {$(location).attr("href", "' . $gametypes['link'] . '"); });
				$arcadeLinkz' . $y . '.text("' . $gametypes['text'] . '");
				$(container2).append($arcadeLinkz' . $y . ');';
		}

		$javascript .= '
				var archive, archivesClass, archiveHref, countHref, originalArchiveColor, hoverArchiveColor = "";
				var archiveHrefs = [];
				archivesClass = document.getElementsByClassName("button_strip_archivetype");
				archive = archivesClass != null ? archivesClass[0] : document.getElementById("archivetype");
				originalArchiveColor = $(archive).css("color");
				$(archive).hover(
					function() {
						if (hoverArchiveColor == "") {
							hoverArchiveColor = $(archive).css("color");
						}
						else {
							$(archive).css("color", hoverArchiveColor);
						}
					},
					function() {
						$(archive).css("color", originalArchiveColor);
						return false;
					}
				);
				if (archive == null)
				{
					archiveHrefs = document.getElementsByTagName("A");
					for(countHref=0;countHref<archiveHrefs.length;countHref++)
					{
						if (archiveHrefs[countHref].href.includes("action=arcade;archive=tar.gz"))
						{
							archive = archiveHrefs[countHref];
							break;
						}
					}
				}
				if (archive != null)
				{
					archive.style.position = "relative";
					archive.href = "javascript:void(0)";
					$(archive).append(container);
					archive.onclick = function() {
						var showMe1 = document.getElementById("archivetype");
						if (showMe1.style.display == "none") {
							showMe1.style.display = "block";
						}
						else {
							showMe1.style.display = "none";
						}
					}
					archive.onmouseleave = function() {
						document.getElementById("archivetype").style.display = "none";
					}
				}
				var gametype, gametypeClass, gametypeHref, countHref, originalGametypeColor, hoverGametypeColor = "";
				var gametypeHrefs = [];
				gametypeClass = document.getElementsByClassName("button_strip_gametype");
				gametype = gametypeClass != null ? gametypeClass[0] : document.getElementById("gametype");
				originalGametypeColor = $(gametype).css("color");
				$(gametype).hover(
					function() {
						if (hoverGametypeColor == "") {
							hoverGametypeColor = $(gametype).css("color");
						}
						else {
							$(gametype).css("color", hoverGametypeColor);
						}
					},
					function() {
						$(gametype).css("color", originalGametypeColor);
						return false;
					}
				);
				if (gametype == null)
				{
					gametypeHrefs = document.getElementsByTagName("A");
					for (countHref = 0;countHref<gametypeHrefs.length;countHref++)
					{
						if (gametypeHrefs[countHref].href.includes("index.php?action=arcade;gametype=all"))
						{
							gametype = gametypeHrefs[countHref];
							break;
						}
					}
				}
				if (gametype != null)
				{
					gametype.style.position = "relative";
					gametype.href = "javascript:void(0)";
					$(gametype).append(container2);
					gametype.onclick = function() {
						var showMe2 = document.getElementById("gametype");
						if (showMe2.style.display == "none") {
							showMe2.style.display = "block";
						}
						else {
							showMe2.style.display = "none";
						}
					}
					gametype.onmouseleave = function() {
						document.getElementById("gametype").style.display = "none";
					}
				}
			}
			$(document).ready(function() {
				smfArcadeDropDown();
			});
		</script>';

		if (!empty($arcadeModSettings['arcade_gz_user']) && allowedTo('arcade_download_type') && !empty($enableGameDownload))
		{
			if (isset($_REQUEST['sa']) && $_REQUEST['sa'] != 'stats')
				$context['arcade_tabs']['archivetype'] =  $archivetype;
		}

		if (allowedTo('arcade_gametype_select') && !empty($countGames))
		{
			if (isset($_REQUEST['sa']) && $_REQUEST['sa'] != 'stats')
				$context['arcade_tabs']['gametype'] =  $gametype;
		}

		if (!in_array($_REQUEST['sa'], array('highscore', 'comment')) && isset($_SESSION['arcade']['highscore']) && empty($rom))
			unset($_SESSION['arcade']['highscore']);

		// Check permission if needed
		if (isset($subActions[$_REQUEST['sa']][2]))
			isAllowedTo($subActions[$_REQUEST['sa']][2]);

		//$context['arcade_javascript'] = $javascript;
		$context['html_headers'] .= $javascript;
		require_once($boarddir . '/ArcadeSources/' . $subActions[$_REQUEST['sa']][0]);
		!isset($_SESSION['current_cat']) ? $_SESSION['current_cat'] = 'all' : '';
		isset($_REQUEST['category']) ? $_SESSION['current_cat'] = $_REQUEST['category'] : $_REQUEST['category'] = $_SESSION['current_cat'];
		$gamesavetype = isset($_REQUEST['gametype']) ? ArcadeSessionSanitize($_REQUEST['gametype']) : '';
		$queryArray = explode('|', $txt['arcade_select_gametype']);
		$nameArray = explode('|', $txt['arcade_select_gametype_english']);
		$_REQUEST['category'] = !empty($_REQUEST['current_cat']) ? ArcadeSpecialChars($_REQUEST['current_cat'], 'cat'): $_SESSION['current_cat'];
		$_SESSION['arcade_gametype_select_title'] = $userGametype != -1 && !empty($typeArray) && !empty($typeArray[$userGametype]) ? strtoupper($typeArray[$userGametype]) . '&nbsp;' . $txt['arcade_game_list'] : $txt['arcade_game_list'];
		$_SESSION['arcade_gametype_select' . $rom2] = !empty($_SESSION['arcade_gametype_select' . $rom2]) ? $_SESSION['arcade_gametype_select' . $rom2] : 'all';
		if ($userGametype != -1 && !empty($queryArray[$userGametype]))
		{
			$sortGametype = !empty($user_info['arcade_settings']['arcade_gametype' . $rom]) ? $queryArray[$user_info['arcade_settings']['arcade_gametype' . $rom]] : '';
			$sortGametype = !empty($_SESSION['arcade_gametype_select' . $rom2]) && in_array($_SESSION['arcade_gametype_select' . $rom2], $queryArray) && $_SESSION['arcade_gametype_select' . $rom2] != 'all' ? $_SESSION['arcade_gametype_select' . $rom2] : $sortGametype;
			$sortGametype = ($sortGametype == 'all' || $_SESSION['arcade_gametype_select' . $rom2] == 'all') ? '' : $sortGametype;
			$key = array_search($sortGametype, $queryArray);
			$_SESSION['arcade_gametype_select_title'] = !empty($sortGametype) ? $nameArray[$key] . '&nbsp;' . $txt['arcade_game_list' . $rom2] : $txt['arcade_game_list' . $rom2];
		}
		if (!empty($gamesavetype) && in_array($gamesavetype, $queryArray))
		{
			$key = array_search($gamesavetype, $queryArray);
			$_SESSION['arcade_gametype_select_title'] = $key != 0 ? $nameArray[$key] . '&nbsp;' . $txt['arcade_game_list' . $rom2] : $txt['arcade_game_list' . $rom2];
		}
		arcade_log_online($rom);

		$subActions[$_REQUEST['sa']][1]();
	}
}

function loadArcadeSkin($rom = 0, $head = true)
{
	global $settings, $arcadeModSettings, $user_info, $boarddir, $boardurl, $context;
	$rom = isset($_REQUEST['action']) && stripos($_REQUEST['action'], 'retro_arch') !== FALSE ? 1 : $rom;
	$rom2 = !empty($rom) ? '_rom' : '';
	list($files, $custom, $headers, $arcade_version) = array(array(), false, '', $arcadeModSettings['arcadeVersion']);
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$action = isset($_REQUEST['action']) && is_string($_REQUEST['action']) && in_array($_REQUEST['action'], array('arcade', 'retro_arch')) ? 1 : 0;
	list($arcadeSkin, $arcadeMobileSkin) = array($user_info['arcade_settings']['skin' . $rom2], $user_info['arcade_settings']['skin_mobile']);
	$customSkinSettings = !empty($user_info['arcade_settings']['custom_skin']) ? $user_info['arcade_settings']['custom_skin'] : '';
	$customMobileSkinSettings = !empty($user_info['arcade_settings']['custom_skin_mobile']) ? $user_info['arcade_settings']['custom_skin_mobile'] : '';

	if (!empty($_SESSION['arcade_isMobile']) && !empty($head))
	{
		switch ($arcadeMobileSkin) {
			case 1:
				$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-mobile-generic-skin.css?' . $suffixVersion);
				break;
			case 2:
				$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-mobile-light-skin.css?' . $suffixVersion);
				break;
			default:
				if (!empty($action) && !empty($customMobileSkinSettings) && !empty($customMobileSkinSettings['skin_name']) && !empty($customMobileSkinSettings['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $customMobileSkinSettings['skin_source_file']))
				{
					if (!function_exists($customMobileSkinSettings['skin_function'])) {
						require_once($boarddir . '/ArcadeSources/' . $customMobileSkinSettings['skin_source_file']);
						if (!empty($customMobileSkinSettings['skin_function']) && function_exists($customMobileSkinSettings['skin_function']))
						{
							// css & javascript should be loaded within the custom skin function
							$context['arcade_skin'] = $customMobileSkinSettings['skin_function']();
							$custom = true;
						}
						else {
							// use the defaults if the above is unavailable
							$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-mobile-light-skin.css?' . $suffixVersion);
						}
					}
					elseif (function_exists($customMobileSkinSettings['skin_function'])) {
						// css must already be loaded?
					}
					else {
						$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-mobile-light-skin.css?' . $suffixVersion);
					}
				}
		}
		foreach ($files as $filedata) {
			switch ($filedata['type']) {
				case 'css':
					$headers .= !empty($custom) ? '' : '
	<link rel="stylesheet" href="' . $filedata['file'] . '" />';
					break;
				case 'js':
					$headers .= '
	<script type="text/javascript" src="' . $filedata['file'] . '"></script>';
					break;
				default:
					$headers .= '';
			}
		}
	}
	elseif (!empty($head))
	{
		switch ($arcadeSkin)
		{
			case 2:
				$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
				break;
			case 1:
				$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
				break;
			default:
				//$arcadeSkin++;
				if ($arcadeSkin > 3)
				{
					$arcadeTempSettings['arcade_hide_buttons'] = false;
					if (!empty($action) && !empty($customSkinSettings) && !empty($customSkinSettings['skin_name']) && !empty($customSkinSettings['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $customSkinSettings['skin_source_file']))
					{
						if (!function_exists($customSkinSettings['skin_function'])) {
							require_once($boarddir . '/ArcadeSources/' . $customSkinSettings['skin_source_file']);
							if (!empty($customSkinSettings['skin_function']) && function_exists($customSkinSettings['skin_function']))
							{
								// css & javascript for the custom skin should be loaded from this file
								$context['arcade_skin'] = $customSkinSettings['skin_function']();
								$custom = true;
							}
							else {
								$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
							}
						}
						elseif (function_exists($customSkinSettings['skin_function'])) {
							// css must already be loaded?
						}
						else {
							$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
						}
					}
					elseif (!empty($customSkinSettings) && !empty($customSkinSettings['skin_template']))
					{
						$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
					}
					else
					{
						$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
					}
				}
				else
				{
					$files[] = array('type' => 'css', 'file' => $settings['default_theme_url'] . '/css/arcade-skin-b.css?' . $suffixVersion);
				}
		}
		foreach ($files as $filedata) {
			switch ($filedata['type']) {
				case 'css':
					$headers .= empty($custom) ? '' : '
	<link rel="stylesheet" href="' . $filedata['file'] . '" />';
					break;
				case 'js':
					$headers .= '
	<script type="text/javascript" src="' . $filedata['file'] . '"></script>';
					break;
				default:
					$headers .= '';
			}
		}
	}
	$context['html_headers'] .= $headers;
}

function template_main()
{
	/*
	** This patch function stops the template error for some games
	** Just leave it empty
	*/
}

function loadArcade($mode = 'normal', $index = '', $rom = 0)
{
	global $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings, $user_info, $smcFunc, $boarddir, $arcade_version, $arcade_lang_version, $arcadeTempSettings;
	loadArcadeSubs();
    /* Are we using the curve or curve type theme?  */
    file_exists($settings['actual_theme_dir'] . '/images/theme/main_block.png') ? $context['curved'] = true : $context['curved'] = false;


	if (!empty($arcade_version))
		return;

	if (empty($user_info['arcade_settings'])) {
		$user_info['arcade_settings'] = $user_info['is_guest'] ? loadMyArcadeSettings(0) : loadMyArcadeSettings($user_info['id']);
	}
	$rom2 = !empty($rom) ? '_rom' : '';
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$arcade_lang_version = '2.6';
	$_SESSION['arcade_isMobile'] = empty($user_info['arcade_settings']['detect_mobile']) && !empty($_SESSION['arcade_isMobile']) ? $_SESSION['arcade_isMobile'] : 0;
	$_SESSION['arcade_sortby' . $rom2] = !empty($_SESSION['arcade_sortby' . $rom2]) ? $_SESSION['arcade_sortby' . $rom2] : '';
	$skinAlt = array();
	$context['arcadeSkinAlt'] = !empty($skinAlt) && in_array($settings['theme_id'], $skinAlt) ? true : false;
	$context['arcade'] = array();
	$context['arcade_smf_version'] = 'v2.1';
	$context['arcade_new_no_cats'] = ArcadeNewGamesNoCat(array(), $rom);
	list($arcadeSkin, $arcadeMobileSkin) = array($user_info['arcade_settings']['skin' . $rom2], $user_info['arcade_settings']['skin_mobile']);
	$customSkinSettings = !empty($user_info['arcade_settings']['custom_skin' . $rom2]) ? $user_info['arcade_settings']['custom_skin' . $rom2] : '';
	$customMobileSkinSettings = !empty($user_info['arcade_settings']['custom_skin_mobile']) ? $user_info['arcade_settings']['custom_skin_mobile'] : '';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND rom_flag = {int:rom_flag}' : '';
	$action = isset($_REQUEST['action']) && is_string($_REQUEST['action']) && in_array($_REQUEST['action'], array('arcade', 'retro_arch')) ? 1 : 0;
	// Arcade stats
	$context['arcade']['stats'] = array();
	// How many games?
	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS games
		FROM {db_prefix}arcade_games
		WHERE enabled = 1' . $whererom,
		array(
			'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
		)
	);
	$context['arcade']['stats'] += $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	if (!empty($arcadeModSettings['arcadeShowInfoCenter']))
	{
		require_once($boarddir . '/ArcadeSources/ArcadeStats.php');
		$context['arcade']['stats']['best_player'] = ArcadeStats_BestPlayers(1);
		$context['arcade']['stats']['longest_champion'] = ArcadeStats_LongestChampions(1, null, 'current');
		$context['arcade']['stats']['most_played'] = ArcadeStats_MostPlayed(1, $rom);
	}

	if (!empty($_SESSION['arcade_isMobile']))
	{
		switch ($arcadeMobileSkin)
		{
			case 1:
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
				if ($mode == 'normal' || $mode == 'arena')
					loadTemplate('ArcadeSkinMobileGeneric');
				$context['nameCharLength'] = 60;
				break;
			case 2:
				// mobile support should have a simple layout
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
				if ($mode == 'normal' || $mode == 'arena')
					loadTemplate('ArcadeSkinMobileLight');
				$context['nameCharLength'] = 60;
				break;
			default:
				if (!empty($action) && !empty($customMobileSkinSettings) && !empty($customMobileSkinSettings['skin_name']) && !empty($customMobileSkinSettings['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $customMobileSkinSettings['skin_source_file']))
				{
					if (!function_exists($customMobileSkinSettings['skin_function'])) {
						require_once($boarddir . '/ArcadeSources/' . $customMobileSkinSettings['skin_source_file']);
						if (!empty($customMobileSkinSettings['lang_function']) && function_exists($customMobileSkinSettings['lang_function'])) {
							$customMobileSkinSettings['lang_function']();
						}
						if (!empty($customMobileSkinSettings['skin_function']) && function_exists($customMobileSkinSettings['skin_function'])) {
							$context['arcade_skin'] = $customMobileSkinSettings['skin_function']();
							loadTemplate($customMobileSkinSettings['skin_template']);
						}
						else
						{
							require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
							if ($mode == 'normal' || $mode == 'arena')
								loadTemplate('ArcadeSkinMobileLight');
							$context['nameCharLength'] = 60;
						}
					}
					else {
						if (!empty($customMobileSkinSettings['lang_function']) && function_exists($customMobileSkinSettings['lang_function'])) {
							$customMobileSkinSettings['lang_function']();
						}
						$context['arcade_skin'] = $customMobileSkinSettings['skin_function']();
						loadTemplate($customMobileSkinSettings['skin_template']);
					}
				}
				elseif(!empty($action) && !empty($customMobileSkinSettings) && !empty($customMobileSkinSettings['skin_name']) && !empty($customMobileSkinSettings['skin_template']))
				{
					loadTemplate($customMobileSkinSettings['skin_template']);
				}
				else
				{
					require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
					if ($mode == 'normal' || $mode == 'arena')
						loadTemplate('ArcadeSkinMobileLight');
					$context['nameCharLength'] = 60;
				}
		}
	}
	else
	{
		switch ($arcadeSkin)
		{
			case 3:
				$arcadeTempSettings['arcade_hide_buttons'] = true;
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinB.php');
				require_once($boarddir . '/ArcadeSources/ArcadeStats.php');
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinMainB.php');
				$context['html_headers'] .= '<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-skin-b.js?' . $suffixVersion. '"></script>';
				$_SESSION['arcade_shout_session'] = !empty($_SESSION['arcade_shout_session']) ? $_SESSION['arcade_shout_session'] : substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);
				$context['arcade_shout_widthB'] = !empty($arcadeModSettings['arcade_shout_widthB']) ? abs($arcadeModSettings['arcade_shout_widthB']) : 90;
				$context['arcade_shout_widthB'] = $context['arcade_shout_widthB'] < 40 || $context['arcade_shout_widthB'] > 100 ? 90 : $context['arcade_shout_widthB'];
				if ($mode == 'normal' || $mode == 'arena')
				{
					$context['arcade_defiant']['per_line'] = 4;
					$context['arcade_defiant']['cat_width'] = 20;
					$context['arcade_defiant']['cat_height'] = 20;
					$context['page_title'] = $txt['arcade_game_list'];
					$width = !empty($arcadeModSettings['skin_avatar_size_width']) && (int)$arcadeModSettings['skin_avatar_size_width'] > 0 ? (int)$arcadeModSettings['skin_avatar_size_width'] : 50;
					$height = !empty($arcadeModSettings['skin_avatar_size_height']) && (int)$arcadeModSettings['skin_avatar_size_height'] > 0 ? (int)$arcadeModSettings['skin_avatar_size_height'] : 50;
					$context['arcade_user_avatar'] = (!empty($context['user']['avatar']['href'])) ? ArcadeSizer($context['user']['avatar']['href'], $width, $height) : array($width, $height);
					loadTemplate('ArcadeSkinB');
				}
				$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLengthB']) ? $arcadeModSettings['arcadeGamesNameLengthB'] : 100;
				break;
			case 2:
				$arcadeTempSettings['arcade_hide_buttons'] = false;
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
				// this is broken atm
				$_SESSION['arcade_shout_session'] = !empty($_SESSION['arcade_shout_session']) ? $_SESSION['arcade_shout_session'] : substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);
				if ($mode == 'normal' || $mode == 'arena')
					loadTemplate('ArcadeSkinC');
				$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLengthA']) ? $arcadeModSettings['arcadeGamesNameLengthA'] : 100;
				break;
			case 1:
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
				$arcadeTempSettings['arcade_hide_buttons'] = true;
				if ($mode == 'normal' || $mode == 'arena') {
					loadTemplate('Arcade');
				}
				$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLength']) ? $arcadeModSettings['arcadeGamesNameLength'] : 100;
				break;
			case 0:
				require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
				$arcadeTempSettings['arcade_hide_buttons'] = true;
				if ($mode == 'normal' || $mode == 'arena')
					loadTemplate('Arcade');
				$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLength']) ? $arcadeModSettings['arcadeGamesNameLength'] : 100;
				break;
			default:
				$arcadeTempSettings['arcade_hide_buttons'] = false;
				if (!empty($action) && !empty($customSkinSettings) && !empty($customSkinSettings['skin_name']) && !empty($customSkinSettings['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $customSkinSettings['skin_source_file']))
				{
					if (!function_exists($customSkinSettings['skin_function'])) {
						require_once($boarddir . '/ArcadeSources/' . $customSkinSettings['skin_source_file']);
						if (!empty($customSkinSettings['lang_function']) && function_exists($customSkinSettings['lang_function'])) {
							$customSkinSettings['lang_function']();
						}
						if (!empty($customSkinSettings['skin_function']) && function_exists($customSkinSettings['skin_function'])) {
							//$context['arcade_skin'] = $customArcadeSkins[$arcadeSkin]['skin_function']();
							$context['arcade_skin'] = $customSkinSettings['skin_function']();
							if ($mode == 'normal' || $mode == 'arena')
								loadTemplate($customSkinSettings['skin_template']);
						}
						elseif(!empty($customSkinSettings) && !empty($customSkinSettings['skin_name']) && !empty($customSkinSettings['skin_template'])) {
							if (!empty($customSkinSettings['lang_function']) && function_exists($customSkinSettings['lang_function'])) {
								$customSkinSettings['lang_function']();
							}
							if ($mode == 'normal' || $mode == 'arena') {
								loadTemplate($customSkinSettings['skin_template']);
							}
						}
						else {
							require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
							if ($mode == 'normal' || $mode == 'arena')
								loadTemplate('Arcade');
							$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLength']) ? $arcadeModSettings['arcadeGamesNameLength'] : 100;
						}
					}
					elseif(!empty($customSkinSettings) && !empty($customSkinSettings['skin_name']) && !empty($customSkinSettings['skin_template'])) {
						if ($mode == 'normal' || $mode == 'arena')
							loadTemplate($customSkinSettings['skin_template']);
					}
					else {
						require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
						if ($mode == 'normal' || $mode == 'arena')
							loadTemplate('Arcade');
						$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLength']) ? $arcadeModSettings['arcadeGamesNameLength'] : 100;
					}
				}
				elseif (!empty($action) && !empty($customSkinSettings) && !empty($customSkinSettings['skin_template']))
				{
					if ($mode == 'normal' || $mode == 'arena')
						loadTemplate($customSkinSettings['skin_template']);
				}
				else
				{
					require_once($boarddir . '/ArcadeSources/Subs-ArcadeSkinCrux.php');
					if ($mode == 'normal' || $mode == 'arena')
						loadTemplate('Arcade');
					$context['nameCharLength'] = !empty($arcadeModSettings['arcadeGamesNameLength']) ? $arcadeModSettings['arcadeGamesNameLength'] : 100;
				}
		}
	}

	// Load language
	loadLanguage('Arcade');
	loadLanguage('ArcadeSkinC');

	// Permission query
	arcadePermissionQuery();

	// Normal mode
	if ($mode == 'normal' || $mode == 'arena')
	{
		if (empty($arcadeModSettings['arcadeEnabled']) && !allowedTo('arcade_admin'))
			return false;

		$context['games_per_page'] = !empty($user_info['arcade_settings']['games_per_page']) ? $user_info['arcade_settings']['games_per_page'] : $arcadeModSettings['gamesPerPage'];
		$context['scores_per_page'] = !empty($user_info['arcade_settings']['scores_per_page']) ? $user_info['arcade_settings']['scores_per_page'] : $arcadeModSettings['scoresPerPage'];

		// Arcade javascript & css
		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade.js?' . $suffixVersion. '"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-html5-save.js?' . $suffixVersion. '"></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-skin-a.js?' . $suffixVersion. '"></script>
		<script type="text/javascript">var myArcadeWindow;</script>';

		// Add Arcade to link tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=' . (empty($rom) ? 'arcade' : 'retro_arch'),
			'name' => empty($rom) ? $txt['arcade'] : $txt['rom_arcade'],
		);

		// What I can do?
		$context['arcade']['can_play'] = allowedTo('arcade_play');
		$context['arcade']['can_favorite'] = !empty($arcadeModSettings['arcadeEnableFavorites']) && !$user_info['is_guest'] ? true : false;
		$context['arcade']['can_rate'] = !empty($arcadeModSettings['arcadeEnableRatings']) && !$user_info['is_guest'] ? true : false;
		$context['arcade']['can_submit'] = allowedTo('arcade_submit');
		$context['arcade']['can_comment_own'] = allowedTo('arcade_comment_own');
		$context['arcade']['can_comment_any'] = allowedTo('arcade_comment_any');
		$context['arcade']['can_admin_arcade'] = allowedTo('arcade_admin');
		$context['arcade']['can_create_match'] = allowedTo('arcade_create_match');
		$context['arcade']['can_join_match'] = allowedTo('arcade_join_match');

		// Or can I? (do I have enough posts etc.)
		PostPermissionCheck();

		// Finally load Arcade Settings
		if (empty($user_info['is_guest']))
			loadArcadeSettings();

		if (!isset($_REQUEST['xml']))
			$context['template_layers'][] = 'Arcade';
		else
			$context['template_layers'] = [];
	}
	elseif ($mode == 'profile')
		loadTemplate('ArcadeProfile', array('arcade'));
	// Admin mode
	elseif ($mode == 'admin')
	{
		loadTemplate('ArcadeAdmin');
		loadLanguage('ArcadeAdmin');
		isAllowedTo('arcade_admin');

		$context['html_headers'] .= '
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade.js?' . $suffixVersion . '"></script>
		<script type="text/javascript">var myArcadeWindow;</script>';

		$context['template_layers'][] = 'ArcadeAdmin';
		$context['page_title'] = $txt['arcade_admin_title'];
	}
}

function arcadeLogin($rom = '')
{
	global $scripturl, $txt, $user_info, $context, $arcadeModSettings;

	$sub_actions = array(
		'arena',
		'newMatch',
		'newMatch2',
		'viewMatch',
		'highscore',
		'play',
	);

	$rom = isset($_REQUEST['retro_arch']) && $_REQUEST['retro_arch'] == 'rom' ? 1 : $rom;
	$_SESSION['arcade_isMobile'] = empty($user_info['arcade_settings']['detect_mobile']) && !empty($_SESSION['arcade_isMobile']) ? $_SESSION['arcade_isMobile'] : 0;
	$_REQUEST['sa'] = (!empty($_REQUEST['sa'])) ? trim($_REQUEST['sa']) : '';
	$sa = (!empty($_REQUEST['sa'])) && in_array($_REQUEST['sa'], $sub_actions) ? $_REQUEST['sa'] : '';
	$gameId = isset($_REQUEST['game']) ? (int)$_REQUEST['game'] : 0;
	$game = !empty($gameId) ? 'game=' . abs($gameId) . ';' : '';
	$matchId = isset($_REQUEST['match']) ? (int)$_REQUEST['match'] : 0;
	$match = !empty($matchId) ? 'match=' . abs($matchId) . ';' : '';
	$subaction = 'sa=' . $sa . ';' . $match . $game;
	$anchor = in_array($sa, array('play', 'highscore')) && $sa == 'play' ? ';#playgame' : (in_array($sa, array('play', 'highscore')) && $sa == 'highscore' ? ';#commentform3' : '');
	$_SESSION['old_url'] = $scripturl . '?action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';' . $subaction;
	$context['arcade_sub'] = (isset($_REQUEST['hs'])) ? 'score' : 'play';
	$context['arcade_smf_version'] = 'v2.1';
	$_POST[$context['session_var']] = $context['session_id'];

	if (empty($match) && empty($game) && empty($sa))
		redirectexit();

	if (!$user_info['is_guest'])
		redirectexit('action=arcade;' . $subaction . $anchor);

	// Create a login token for SMF 2.1.x
	createToken('login');

	$context['page_title'] = $txt['arcade_login_title'];
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=ingressarcade;' . (!empty($rom) ? 'retro_arch=rom;' : '') . $subaction,
		'name' => $txt['arcade_login_top'],
	);

	// we only need the login template from regular or mobile display
	if (empty($_SESSION['arcade_isMobilePlay']))
	{
		$context['sub_template'] = 'arcade_login';
		loadTemplate('Arcade');
	}
	else
	{
		$context['sub_template'] = 'arcade_mobile_login';
		loadTemplate('ArcadeSkinMobileLight');
	}
}

function arcade_log_online($romCheck = 0)
{
	global $smcFunc, $user_info, $context, $arcadeModSettings;
	$time = time();
	$checkIp = !empty($user_info['ip']) ? trim($user_info['ip']) : (!empty($user_info['ip2']) ? trim($user_info['ip2']) : arcade_get_client_ip());
	list($guests, $users, $action, $gameCheck, $userIp) = array(0, 0, 0, 0, array());
	$game = isset($_REQUEST['game']) ? (int)$_REQUEST['game'] : 0;
	$sa = !empty($_REQUEST['sa']) ? trim($_REQUEST['sa']) : 'index';
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND rom_flag = {int:rom_flag}' : '';

	// check to make sure the game exists
	if (!empty($game)) {
		$request = $smcFunc['db_query']('', '
			SELECT id_game, rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game = {int:gameid}
			LIMIT 1',
			array(
				'gameid' => $game,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			list($romCheck, $gameCheck) = array($row['rom_flag'], $row['id_game']);
		}
		$smcFunc['db_free_result']($request);
	}
	$game = !empty($gameCheck) && $gameCheck == $game ? $game : 0;

	if (empty($romCheck)) {
		switch ($sa)
		{
			case 'play':
				$action = 1;
				break;
			case 'highscore':
				$action = 2;
				break;
			case 'arena':
				$action = 3;
				break;
			case 'online':
				$action = 4;
				break;
			case 'viewMatch':
				$action = 5;
				break;
			case 'newMatch':
				$action = 6;
				break;
			case 'newMatch2':
				$action = 6;
				break;
			case 'stats':
				$action = 7;
				break;
			default:
				$action = 0;
		}
	}
	else {
		switch ($sa)
		{
			case 'play':
				$action = 1;
				break;
			case 'online':
				$action = 4;
				break;
			case 'stats':
				$action = 7;
				break;
			default:
				$action = 0;
		}
	}


	// count guests online for user comparison
	$request = $smcFunc['db_query']('', '
		SELECT online_ip, online_time
		FROM {db_prefix}arcade_guest_data
		WHERE online_ip = {string:ip} AND {int:now} - cast(online_time as signed) < 600',
		array(
			'ip' => (string)$checkIp,
			'now' => $time
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$userIp[] = $row['online_ip'];
	$smcFunc['db_free_result']($request);

	// remove user & guest values that refresh the page or are gone over 10 minutes
	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_member_data
		WHERE id_member = {int:member} OR {int:now} - cast(online_time as signed) >= 600',
		array(
			'member' => $user_info['id'],
			'now' => $time
		)
	);

	if (!$user_info['is_guest'])
		$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_guest_data
			WHERE online_ip = {string:ip} OR {int:now} - cast(online_time as signed) >= 600',
			array(
				'ip' => !in_array($checkIp, $userIp) ? $checkIp : '256.0.0.0',
				'now' => $time
			)
		);

	// insert user or guest into the online log
	if ($user_info['is_guest'] && !empty($checkIp))
	{
		$request = $smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_guest_data
			WHERE online_ip = {string:ip} OR {int:now} - cast(online_time as signed) >= 600',
			array(
				'ip' => $checkIp,
				'now' => $time
			)
		);

		list($time, $show, $ip) = array(time(), '0', $checkIp);

		$smcFunc['db_insert']('replace',
			'{db_prefix}arcade_guest_data',
			array(
				'online_ip' => 'string',
				'online_time' => 'int',
				'show_online' => 'int',
				'current_action' => 'int',
				'current_game' => 'int',
				'temp_name' => 'string',
				'game_section' => 'int',
			),
			array(
				$ip,
				$time,
				$show,
				$action,
				$game,
				!empty($_SESSION['playerName']) ? $_SESSION['playerName'] : '',
				!empty($romCheck) ? 1 : 0,
			),
			array('online_time')
		);
	}
	else
	{
		list($userid, $time, $show, $name, $color) = array($user_info['id'], time(), '0', $user_info['name'], '');

		$request = $smcFunc['db_query']('', '
			SELECT
				mem.id_member, mem.real_name, mem.member_name, mem.show_online,
				mg.online_color, mg.id_group, mg.group_name
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)
			WHERE mem.id_member = {int:member}',
			array(
				'member' => $user_info['id'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$color = !empty($row['online_color']) ? $row['online_color'] : '';
			$show = !empty($row['show_online']) ? 1 : 0;
		}
		$smcFunc['db_free_result']($request);

		$smcFunc['db_insert']('replace',
			'{db_prefix}arcade_member_data',
			array(
				'id_member' => 'int',
				'online_ip' => 'string',
				'online_time' => 'int',
				'show_online' => 'int',
				'online_name' => 'string',
				'online_color' => 'string',
				'current_action' => 'int',
				'current_game' => 'int',
				'game_section' => 'int',
			),
			array(
				$userid,
				$checkIp,
				$time,
				$show,
				$name,
				$color,
				$action,
				$game,
				!empty($romCheck) ? 1 : 0,
			),
			array('id_member')
		);
	}
}

function loadMyArcadeSettings($memID = 0)
{
	global $smcFunc, $txt, $user_info, $arcadeModSettings, $sourcedir, $boarddir;

	loadArcadeSubs();
	$arcadeModSettings['arcadeSkin'] = !empty($arcadeModSettings['arcadeSkin']) ? (int)$arcadeModSettings['arcadeSkin'] : 0;
	$arcadeModSettings['arcadeList'] = !empty($arcadeModSettings['arcadeList']) ? (int)$arcadeModSettings['arcadeList'] : 0;
	$arcadeModSettings['arcadeSkinMobile'] = !empty($arcadeModSettings['arcadeSkinMobile']) ? (int)$arcadeModSettings['arcadeSkinMobile'] : 0;
	$arcadeModSettings['arcadeListMobile'] = !empty($arcadeModSettings['arcadeListMobile']) ? (int)$arcadeModSettings['arcadeListMobile'] : 0;
	$_SESSION['arcade_isMobile'] = empty($user_info['arcade_settings']['detect_mobile']) && !empty($_SESSION['arcade_isMobile']) ? $_SESSION['arcade_isMobile'] : 0;
	$arcadeSettings = array();

	if ($memID == 0 || $user_info['is_guest']) {
		$memID = 0;
	}
	else {
		$request = $smcFunc['db_query']('', '
				SELECT id_member, arena_invite, arena_match_end, arena_new_round, champion_email, champion_pm, notify_game_reports, new_game, games_per_page, archive_type, download_count,
				arcade_gametype, arcade_gametype_rom, new_champion_any, new_champion_own, scores_per_page, skin, list, skin_rom, list_rom, skin_mobile, list_mobile, detect_mobile, arcade_emulatorjs_rom
				FROM {db_prefix}arcade_members
				WHERE id_member = {int:member}
				LIMIT 1',
				array(
					'member' => $memID,
				)
			);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$arcadeSettings = array(
				'id_member' => $row['id_member'],
				'arena_invite' => !empty($row['arena_invite']) ? $row['arena_invite'] : 0,
				'arena_match_end' => !empty($row['arena_match_end']) ? $row['arena_match_end'] : 0,
				'arena_new_round' => !empty($row['arena_new_round']) ? $row['arena_new_round'] : 0,
				'champion_email' => !empty($row['champion_email']) ? $row['champion_email'] : 0,
				'champion_pm' => !empty($row['champion_pm']) ? $row['champion_pm'] : 0,
				'notify_game_reports' => (!empty($row['notify_game_reports']) && memberAllowedTo('arcade_admin', $memID) ? $row['notify_game_reports'] : 0),
				'new_game' => !empty($row['new_game']) ? $row['new_game'] : 0,
				'detect_mobile' => !empty($row['detect_mobile']) ? (int)$row['detect_mobile'] : 0,
				'games_per_page' => !empty($row['games_per_page']) ? $row['games_per_page'] : (!empty($arcadeModSettings['gamesPerPage']) ? $arcadeModSettings['gamesPerPage'] : 28),
				'archive_type' => isset($row['archive_type']) && !empty($arcadeModSettings['arcade_gz_user']) ? $row['archive_type'] : (!empty($arcadeModSettings['arcade_gz']) ? $arcadeModSettings['arcade_gz'] : 1),
				'arcade_gametype' => !empty($row['arcade_gametype']) ? $row['arcade_gametype'] : 0,
				'arcade_gametype_rom' => !empty($row['arcade_gametype_rom']) ? $row['arcade_gametype_rom'] : 0,
				'new_champion_any' => !empty($row['new_champion_any']) ? $row['new_champion_any'] : 0,
				'new_champion_own' => !empty($row['new_champion_own']) ? $row['new_champion_own'] : 0,
				'scores_per_page' => !empty($row['scores_per_page']) ? $row['scores_per_page'] : (!empty($arcadeModSettings['scoresPerPage']) ? $arcadeModSettings['scoresPerPage'] : 50),
				'skin' => !empty($row['skin']) && memberAllowedTo('arcade_skin', $memID) ? $row['skin'] : 0,
				'list' => !empty($row['list']) && memberAllowedTo('arcade_list', $memID) ? $row['list'] : 0,
				'skin_rom' => !empty($row['skin_rom']) && memberAllowedTo('arcade_skin', $memID) ? $row['skin_rom'] : 0,
				'list_rom' => !empty($row['list_rom']) && memberAllowedTo('arcade_list', $memID) ? $row['list_rom'] : 0,
				'skin_mobile' => (!empty($row['skin_mobile']) && memberAllowedTo('arcade_skin', $memID) ? (int)$row['skin_mobile'] : 0),
				'list_mobile' => (!empty($row['list_mobile']) && memberAllowedTo('arcade_list', $memID) ? (int)$row['list_mobile'] : 0),
				'download_count' => !empty($row['download_count']) ? $row['download_count'] : 0,
				'arcade_emulatorjs_rom' => !empty($row['arcade_emulatorjs_rom']) ? $row['arcade_emulatorjs_rom'] : '',
			);

		}
		$smcFunc['db_free_result']($request);
	}

	if (empty($arcadeSettings)) {
		// Default
		$arcadeSettings = array(
			'id_member' => $memID,
			'arena_invite' => 0,
			'arena_match_end' => 0,
			'arena_new_round' => 0,
			'champion_email' => 0,
			'champion_pm' => 0,
			'new_game' => 0,
			'detect_mobile' => 0,
			'games_per_page' => !empty($arcadeModSettings['gamesPerPage']) ? (int)$arcadeModSettings['gamesPerPage'] : 28,
			'archive_type' => !empty($arcadeModSettings['arcade_gz']) ? (int)$arcadeModSettings['arcade_gz'] : 2,
			'arcade_gametype' => 0,
			'arcade_gametype_rom' => 0,
			'new_champion_any' => 0,
			'new_champion_own' => 0,
			'scores_per_page' => !empty($arcadeModSettings['scoresPerPage']) ? (int)$arcadeModSettings['scoresPerPage'] : 50,
			'skin' => $arcadeModSettings['arcadeSkin']+1,
			'list' => $arcadeModSettings['arcadeList']+1,
			'skin_rom' => $arcadeModSettings['arcadeSkin']+1,
			'list_rom' => $arcadeModSettings['arcadeList']+1,
			'skin_mobile' => $arcadeModSettings['arcadeSkinMobile']+1,
			'list_mobile' => $arcadeModSettings['arcadeListMobile']+1,
			'download_count' => 0,
			'arcade_emulatorjs_rom' => '',
		);
	}

	$_SESSION['arcade_isMobile'] = empty($arcadeSettings['detect_mobile']) && !empty($_SESSION['arcade_isMobile']) ? $_SESSION['arcade_isMobile'] : 0;
	list($customArcadeSkins, $customMobileSkins, $customArcadeLists, $customMobileLists, $default, $custom) = array(array(), array(), array(), array(), array(), array());
	list($default['skin'], $default['skin_rom'], $default['skin_mobile'], $default['list'], $default['list_rom'], $default['list_mobile'], $ds, $dms, $cl, $cml) = array($arcadeModSettings['arcadeSkin']+1, $arcadeModSettings['arcadeSkin']+1, $arcadeModSettings['arcadeSkinMobile']+1, $arcadeModSettings['arcadeList']+1, $arcadeModSettings['arcadeList']+1, $arcadeModSettings['arcadeListMobile']+1, 1, 1, 1, 1);
	$arcadeSettings['skin'] = !empty($arcadeSettings['skin']) ? $arcadeSettings['skin'] : (int)$default['skin'];
	$arcadeSettings['skin_rom'] = !empty($arcadeSettings['skin_rom']) ? $arcadeSettings['skin_rom'] : (int)$default['skin'];
	$arcadeSettings['skin_mobile'] = !empty($arcadeSettings['skin_mobile']) ? $arcadeSettings['skin_mobile'] : (int)$default['skin_mobile'];
	$arcadeSettings['list'] = !empty($arcadeSettings['list']) ? $arcadeSettings['list'] : (int)$default['list'];
	$arcadeSettings['list_rom'] = !empty($arcadeSettings['list_rom']) ? $arcadeSettings['list_rom'] : (int)$default['list'];
	$arcadeSettings['list_mobile'] = !empty($arcadeSettings['list_mobile']) ? $arcadeSettings['list_mobile'] : (int)$default['list_mobile'];


	if ($arcadeSettings['skin'] > 3) {
		$customArcadeSkin = Arcade_integrate_skins('desktop', true, $arcadeSettings['skin']);
		$arcadeSettings['custom_skin'] = !empty($customArcadeSkin[$arcadeSettings['skin']]) ? $customArcadeSkin[$arcadeSettings['skin']] : 1;
	}
	if ($arcadeSettings['skin_rom'] > 3) {
		$customArcadeRomSkin = Arcade_integrate_skins('desktop', true, $arcadeSettings['skin_rom']);
		$arcadeSettings['custom_skin_rom'] = !empty($customArcadeRomSkin[$arcadeSettings['skin_rom']]) ? $customArcadeRomSkin[$arcadeSettings['skin_rom']] : 1;
	}
	if ($arcadeSettings['skin_mobile'] > 2) {
		$customMobileSkin = Arcade_integrate_skins('mobile', true, $arcadeSettings['skin_mobile']);
		$arcadeSettings['custom_skin_mobile'] = !empty($customMobileSkin[$arcadeSettings['skin_mobile']]) ? $customMobileSkin[$arcadeSettings['skin_mobile']] : 1;
	}
	if ($arcadeSettings['list'] > 3) {
		$customArcadeList = Arcade_integrate_lists('desktop', true, $arcadeSettings['list']);
		$arcadeSettings['custom_list'] = !empty($customArcadeList[$arcadeSettings['list']]) ? $customArcadeList[$arcadeSettings['list']] : 1;
	}
	if ($arcadeSettings['list_rom'] > 3) {
		$customArcadeRomList = Arcade_integrate_lists('desktop', true, $arcadeSettings['list_rom']);
		$arcadeSettings['custom_list_rom'] = !empty($customArcadeRomList[$arcadeSettings['list_rom']]) ? $customArcadeRomList[$arcadeSettings['list_rom']] : 1;
	}
	if ($arcadeSettings['list_mobile'] > 2) {
		$customMobileList = Arcade_integrate_lists('mobile', true, $arcadeSettings['list_mobile']);
		$arcadeSettings['custom_list_mobile'] = !empty($customMobileList[$arcadeSettings['list_mobile']]) ? $customMobileList[$arcadeSettings['list_mobile']] : 1;
	}

	return $arcadeSettings;
}

function ArcadeNewGamesNoCat($categories = array(), $rom = 0)
{
	global $smcFunc, $arcadeModSettings;

	$categories = is_array($categories) ? $categories : array($categories);
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$rows = array();
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) || !allowedTo('arcade_view_retro_arch') ? ' AND game.rom_flag = {int:rom_flag}' : '';
	$count = 0;

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($count = cache_get_data('arcade_games_nocat' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$count = 0;
		$request = $smcFunc['db_query']('', '
			SELECT game.id_game, game.game_name, game.id_cat, game.enabled, rom_flag
			FROM {db_prefix}arcade_games AS game
			WHERE game.id_game > 0' . $whererom . '
			ORDER BY game.id_game DESC',
			array(
				'empty' => '',
				'rom_flag' => !empty($rom) && allowedTo('arcade_view_retro_arch') ? 1 : 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (in_array($row['id_cat'], $categories) && !empty($row['enabled']))
				$count++;
			elseif (empty($row['id_cat']) && !empty($row['enabled']))
				$count++;
		}
		$smcFunc['db_free_result']($request);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_games_nocat' . $rom2, $count, $arcadeModSettings['arcade_cache_time']);
		}
	}
	$count = (int)$count;
	return $count;
}

function loadArcadeSubs()
{
	global $sourcedir, $boarddir;

	if (!function_exists('loadClassFile'))
		require_once($boarddir . '/ArcadeSources/Subs-ArcadeClass.php');
	if (!function_exists('loadArcadeSettings'))
		require_once($boarddir . '/ArcadeSources/Subs-ArcadePlus.php');
	if (!function_exists('arcade_html_entity_decode'))
		require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	if (!function_exists('membersAllowedTo'))
		require_once($sourcedir . '/Subs-Members.php');
}

?>