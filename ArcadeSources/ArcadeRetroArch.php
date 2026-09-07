<?PHP
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
if (!defined('SMF'))
	die('No direct access...');

function ArcadeRetroArch($full = true, $gameid = 0)
{
	global $boarddir, $user_info, $boardurl, $arcadeModSettings, $settings, $context, $txt, $smcFunc, $user_info, $sourcedir, $scripturl, $arcadeSettings;

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	// Do we have permission?
	$allowed = allowedTo('arcade_view_retro_arch') ? true : (allowedTo('arcade_admin') ? true : false);
	$action = !empty($_REQUEST['action']) && is_string($_REQUEST['action']) && strtolower($_REQUEST['action']) == 'retro_arch' ? 'retro_arch' : 'arcade';
	if (!allowedTo('arcade_view') && $action == 'arcade') {
		arcadeClearSession();
		fatal_lang_error('cannot_arcade_view', false);
	}
	if (!$allowed) {
		arcadeClearSession();
		fatal_lang_error('cannot_arcade_play_retro_arch', false);
	}

	require_once($sourcedir . '/Arcade.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	loadLanguage('Arcade');
	$arcadeSettings = !empty($context['user']['is_logged']) ? loadMyArcadeSettings($user_info['id']) : array();

	$selfCheck = file_exists($boarddir. '/ArcadeRetroArch/data/emulator.js') || file_exists($boarddir. '/ArcadeRetroArch/data/src/emulator.js') ? 1 : 0;
	$arcadeModSettings['arcadeBehaviorCDN'] = !empty($arcadeModSettings['arcadeBehaviorCDN']) ? intval($arcadeModSettings['arcadeBehaviorCDN']) : 0;
	switch($arcadeModSettings['arcadeBehaviorCDN']) {
		// the external check takes a second so we avoid that behavior if possible
		case 1:
			$cdnCheck = 1;
			$arcadeBehaviorCDN = !empty($selfCheck) && empty($arcadeModSettings['arcade_rom_emulator']) ? 0 : 1;
			break;
		case 2:
			if (!empty($arcadeModSettings['arcade_rom_emulator'])) {
				$cdnCheck = !empty(arcadeRemoteEjsFileExists('https://cdn.emulatorjs.org/stable/data/version.json')) ? 1 : 0;
				$arcadeBehaviorCDN = !empty($arcadeModSettings['arcade_rom_emulator']) && !empty($cdnCheck) ? 1 : (!empty($selfCheck) ? 0 : -1);
			}
			elseif (!empty($selfCheck)) {
				$arcadeBehaviorCDN = 0;
			}
			else {
				$cdnCheck = !empty(arcadeRemoteEjsFileExists('https://cdn.emulatorjs.org/stable/data/version.json')) ? 1 : 0;
				$arcadeBehaviorCDN = !empty($cdnCheck) ? 1 : -1;
			}
			break;
		default:
			$arcadeBehaviorCDN = !empty($arcadeModSettings['arcade_rom_emulator']) ? 1 : (!empty($selfCheck) ? 0 : -1);
			$cdnCheck = 0;
	}

	$threadsArray = !empty($arcadeModSettings['arcade_ejs_threads']) ? explode('|', $arcadeModSettings['arcade_ejs_threads']) : array('psp');
	$shaders = array_keys($txt['arcade_profile_shaders']);
	$gameid = filter_var($_REQUEST['game'], FILTER_VALIDATE_INT) !== false ? $gameid : 0;
	$gameid = isset($_REQUEST['game']) && filter_var($_REQUEST['game'], FILTER_VALIDATE_INT) !== false ? $_REQUEST['game'] : $gameid;
	$fileTypes = array_keys($txt['arcade_select_gametype_rom']);
	$_SESSION['smf_game_rom'] = str_pad(dechex(mt_rand(0, 0xFFFFF)), 18, '0', STR_PAD_LEFT);
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] . '/' : $boarddir . '/ArcadeRetroArch/roms/';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
	$romGamesUrl = !empty($arcadeModSettings['romGamesUrl']) ? $arcadeModSettings['romGamesUrl'] . '/' : $boardurl . '/ArcadeRetroArch/roms/';
	$romUserSettings = !empty($arcadeSettings['arcade_emulatorjs_rom']) && arcadeIsSerialized($arcadeSettings['arcade_emulatorjs_rom']) ? arcade_safe_unserialize($arcadeSettings['arcade_emulatorjs_rom']) : array('webgl2Enabled' => 0);
	list($context['arcade_rom_initiated'], $context['arcade_rom_game_datum'], $context['arcade_rom_files'], $context['arcade_rom_game_data'], $context['arcade_rom_game_types'], $context['rom_arcade_bios']) = array('rom_initiated', array('', 'const ext2 = parts.length > 2 ? parts[parts.length-2] : parts[parts.length-1];'), array(), array(), $fileTypes, '');

	foreach (array('none', 'all') as $del) {
		if (($key = array_search($del, $context['arcade_rom_game_types'])) !== false) {
			unset($context['arcade_rom_game_types'][$key]);
		}
	}
	if (!empty($gameid)) {
		$request = $smcFunc['db_query']('', '
			SELECT g.game_name, g.id_game, g.rom_system, g.game_file, g.game_directory, g.enabled, g.rom_flag, g.extra_data, g.internal_name, cats.member_groups
			FROM {db_prefix}arcade_games AS g
			LEFT JOIN {db_prefix}arcade_categories AS cats ON (cats.id_cat = g.id_cat)
			WHERE g.id_game = {int:gameid} AND g.rom_flag = {int:rom_flag}' . (!allowedTo('arcade_admin') ? '
				AND g.enabled = {int:enabled}' : '') . '
			LIMIT 1',
			array(
				'gameid' => $gameid,
				'enabled' => 1,
				'rom_flag' => 1,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!empty($row['member_groups'])) {
				$groupsAllowed = explode(',', $row['member_groups']);
				$allowedCheck = array_intersect($user_info['groups'], $groupsAllowed);
				if (!allowedTo('arcade_admin') && !empty($groupsAllowed) && (count($allowedCheck) == 0)) {
					arcadeClearSession();
					fatal_lang_error('arcade_no_game_permission', false);
				}
			}
			$extra = !empty($row['extra_data']) && arcadeIsSerialized($row['extra_data']) ? arcade_safe_unserialize($row['extra_data']) : array();
			$extra['height'] = !empty($extra['height']) ? (int)$extra['height'] : 600;
			$extra['width'] = !empty($extra['width']) ? (int)$extra['width'] : 800;
			$bgcolor = !empty($extra['background_color']) ? arcadeRomHexToColor($extra['background_color']) : '1AAFFF';
			$exlink = !empty($extra['external_link']) ? $extra['external_link'] : '';
			$context['arcade_rom_game_data'] = array(
				'id' => $row['id_game'],
				'path' => $row['game_directory'] . '/' . $row['game_file'],
				'name' => htmlspecialchars($row['game_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8', false),
				'internal_name' => $row['internal_name'],
				'width' => !empty($extra['width']) ? intval($extra['width']) : 960,
				'height' => !empty($extra['height']) ? intval($extra['height']) : 540,
				'rom_type' => $row['rom_system'],
				'type' => !empty($extra['type']) ? $extra['type'] : '',
				'external_link' => $exlink,
				'language' => ArcadeRomLanguageCode(),
				'bgcolor' => '#' . $bgcolor,
				'debug' => !empty($arcadeModSettings['arcade_log_emulatorjs_console']) ? 1 : 0,
				'src' => $arcadeBehaviorCDN,
				'exit' => $scripturl . '?action=' . $action,
				'savestatejs' => '',
				'webgl2' => !empty($romUserSettings['webgl2Enabled']) ? 1 : 0,
				'volume' => !empty($romUserSettings['volume']) ? 1 : 0,
				'mute' => !empty($romUserSettings['mute']) ? 1 : 0,
				'shader' => !empty($romUserSettings['shader']) && is_int($romUserSettings['shader']) && !empty($shaders[$romUserSettings['shader']]) ? $shaders[$romUserSettings['shader']] : $shaders[0],
				'threads' => !empty($row['rom_system']) && in_array($row['rom_system'], $threadsArray)
			);
		}
		$smcFunc['db_free_result']($request);

		if (!empty($context['arcade_rom_game_data']['src']) && $context['arcade_rom_game_data']['src'] < 0) {
			arcadeClearSession();
			fatal_lang_error('arcade_emulatorjs_core_error', false);
		}

		$pop = isset($_REQUEST['pop']) && is_numeric($_REQUEST['pop']) && intval($_REQUEST['pop']) == 1 ? 1 : 0;
		$typeCheck = !empty($context['arcade_rom_game_data']['rom_type']) && in_array($context['arcade_rom_game_data']['rom_type'], $context['arcade_rom_game_types']) ? 'const ext2 = "' . $context['arcade_rom_game_data']['rom_type'] . '";' : '';
		if (!empty($context['arcade_rom_game_data'])) {
			if (!isset($_POST['romremotefile'])) {
				$validRemoteLink = !empty($context['arcade_rom_game_data']['external_link']) && arcadeRetroArchIsValidUrl($context['arcade_rom_game_data']['external_link'], $context['arcade_rom_game_data']['id']) ? true : false;
				$gamePath = !empty($validRemoteLink) ? $context['arcade_rom_game_data']['external_link'] : (!empty($context['arcade_rom_game_data']['path']) ? $context['arcade_rom_game_data']['path'] : '');
			}
			else {
				if (file_exists($boarddir . '/ArcadeRetroArch/roms/' . $_POST['romremotefile']))
					$gamePath = $_POST['romremotefile'];
				else {
					$validRemoteLink = !empty($context['arcade_rom_game_data']['external_link']) && arcadeRetroArchIsValidUrl($context['arcade_rom_game_data']['external_link'], $context['arcade_rom_game_data']['id']) ? true : false;
					$gamePath = !empty($validRemoteLink) ? $context['arcade_rom_game_data']['external_link'] : (!empty($context['arcade_rom_game_data']['path']) ? $context['arcade_rom_game_data']['path'] : '');
				}
			}
			$context['arcade_rom_game_datum'] = array(
				'
						localStorage.setItem("emulatorconfiggame", "' . ($gamePath) . '");
						$("#input").val("' . ($gamePath) . '");
						arcadeRomRun();',
				$typeCheck,
			);

			if (empty($context['arcade_rom_counter'])) {
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET num_plays = num_plays + 1
					WHERE id_game = {int:game}',
					array(
						'game' => $gameid,
					)
				);
				$context['arcade_rom_counter'] = 1;
				arcade_log_online(1);
			}
		}
		else {
			arcadeClearSession();
			fatal_lang_error('arcade_retro_arch_gameid_mismatch', false);
		}
	}
	else {
		arcadeClearSession();
		fatal_lang_error('arcade_retro_arch_gameid_none', false);
	}

	require_once($boarddir . '/ArcadeRetroArch/index.php');
	$context['page_title'] = $txt['arcade_rom_game_title'];
	$context['arcade_base_href'] = $boardurl . '/ArcadeRetroArch/';
	$context['arcade_suffix_version'] = $suffixVersion;
	if (defined('JQUERY_VERSION') && version_compare(JQUERY_VERSION, '3.7.1', '>=') && file_exists($settings['default_theme_dir'] . '/scripts/jquery-' . JQUERY_VERSION . '.min.js')) {
		$context['rom_arcade_jquery'] = $settings['default_theme_url'] . '/scripts/jquery-' . JQUERY_VERSION . '.min.js';
	}
	else {
		$context['rom_arcade_jquery'] = 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js';
	}
	if (!empty($context['arcade_rom_game_data']['rom_type']) && file_exists($romGamesDirectory . '0_bios_' . $arcadeModSettings['arcadeRandomIdVar'] . '/' . $context['arcade_rom_game_data']['rom_type'] . '.7z')) {

		$context['rom_arcade_bios'] = arcadeRetroArchTempBios($romGamesDirectory . '0_bios_' . $arcadeModSettings['arcadeRandomIdVar'] . '/' . $context['arcade_rom_game_data']['rom_type'] . '.7z');
	}
	elseif (!empty($context['arcade_rom_game_data']['rom_type']) && file_exists($romGamesDirectory . '0_bios_' . $arcadeModSettings['arcadeRandomIdVar'] . '/' . $context['arcade_rom_game_data']['rom_type'] . '.zip')) {
		$context['rom_arcade_bios'] = arcadeRetroArchTempBios($romGamesDirectory . '0_bios_' . $arcadeModSettings['arcadeRandomIdVar'] . '/' . $context['arcade_rom_game_data']['rom_type'] . '.zip');
	}
	$context['arcade_retro_arch_rom'] = smf_arcade_iframe_roms(true);
	$context['html_headers'] .= '
			<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-retro-arch-parent.css?' . $suffixVersion . '" />
			<script>
				function arch_game_remote_success(filepath = "") {
					if (filepath) {
						setTimeout(function() {
							var newurlpath = "roms/" + filepath;
							localStorage.setItem("emulatorconfiggame2", newurlpath);
							var iframe = document.getElementById("iframe_smfarcaderoms_small");
							iframe.sandbox = "allow-same-origin allow-scripts";
							iframe.srcdoc = iframe.srcdoc;
						}, 2000);
					}
				}
			</script>';
	if (!empty($context['arcade']['rom_return_data'])) {
		return array($context['arcade_retro_arch_rom'], $context['arcade_rom_files'], $gameid);
	}
	if (!empty($context['arcade_rom_game_data']) && !empty($context['arcade_rom_game_data']['rom_type']) && !empty($context['arcade_rom_game_data']['threads'])) {
		header('Cross-Origin-Opener-Policy: same-origin');
		header('Cross-Origin-Embedder-Policy: require-corp');
	}
	if (!empty($full)) {
		$context['arcade']['romwindow'] = false;
		$context['arcade']['popupscore'] = true;
		$context['sub_template'] = 'arcade_retro_arch';
		loadTemplate('ArcadeRetroArch');
	}
	elseif (isset($_REQUEST['sameArcadeWindow']) && is_numeric($_REQUEST['sameArcadeWindow']) && $_REQUEST['sameArcadeWindow'] == 1) {
		$context['arcade']['romwindow'] = false;
		$context['arcade']['popupscore'] = true;
		$context['sub_template'] = 'arcade_retro_arch';
		loadTemplate('ArcadeRetroArch');
	}
	elseif (!empty($context['arcade']['rom_no_topbottom'])) {
		$context['arcade']['popupscore'] = true;
		$context['arcade']['romwindow'] = true;
		$context['sub_template'] = 'arcade_retro_arch';
		loadTemplate('ArcadeRetroArch');
	}
	else {
		$context['arcade']['popupscore'] = false;
		$context['arcade']['romwindow'] = true;
		$context['template_layers'][] = 'arcade_game';
		$context['sub_template'] = 'arcade_game_rom_play';
		loadTemplate('ArcadeGame');
	}
}

function arcadeRetroArchTempBios($file)
{
	global $boarddir, $boardurl, $arcadeModSettings, $sourcedir, $user_info;

	// guests may not play games that require standard bios files
	if ($user_info['is_guest']) {
		return '';
	}
	clearstatcache();
	list($rand, $files) = array(uniqid('bios-file_'), array());
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] : $boarddir . '/ArcadeRetroArch/roms';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
	$romGamesUrl = !empty($arcadeModSettings['romGamesUrl']) ? $arcadeModSettings['romGamesUrl'] : $boardurl . '/ArcadeRetroArch/roms';
	$newfile = $romGamesDirectory . '/temp_bios/user-' . $user_info['id'] . '_' . $rand . '_' . basename($file);
	$newUrl = $romGamesUrl . '/temp_bios/user-' . $user_info['id'] . '_' . $rand . '_' . basename($file);
	if (!is_dir($romGamesDirectory . '/temp_bios')) {
		@mkdir($romGamesDirectory . '/temp_bios', 0755);
		@copy($sourcedir . '/index.php', $romGamesDirectory . '/temp_bios/index.php');
		@chmod($romGamesDirectory . '/temp_bios/index.php', 0644);
	}

	// delete any previous temp bios files from this specfic user before creating another ~ regardless of the aggressive cron schedule
	$files = glob($romGamesDirectory . '/temp_bios/user-' . $user_info['id'] . '_*');
	foreach ($files as $path) {
		if (is_dir($path)) {
			arcadeRmDir($path);
		}
		else {
			unlink($path);
		}
	}

	@copy($file, $newfile);
	@chmod($newfile, 0644);
	clearstatcache();

	if (!@file_exists($newfile)) {
		return '';
	}

	return $newUrl;
}

function arcadeRetroArchIsValidUrl($remoteFile, $gameid, $check = false)
{
	global $arcadeModSettings, $scripturl, $txt;
	if(filter_var($remoteFile, FILTER_VALIDATE_URL)){
		$handle = @fopen($remoteFile, "rb");
		if (!empty($handle)) {
			$check = @fread($handle, 10);
			@fclose($handle);
		}
	}

	if (empty($check) && !empty($arcadeModSettings['arcade_log_remote_game'])) {
		//$editGameLink = '<a target="_self" href="' . $scripturl . '?action=admin;area=manageromgames;sa=editrom;game=' . $gameid . '">' . $gameid . '</a>';
		$errorMsg = sprintf($txt['arcade_remote_game_file_error'], $gameid, $remoteFile);
		log_error($errorMsg, 'debug');
		return false;
	}

	return true;
}

function arcadeRemoteEjsFileExists($url)
{
	if(!filter_var($url, FILTER_VALIDATE_URL)){
        return false;
    }
    $curlInit = curl_init($url);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,3);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($curlInit);
    curl_close($curlInit);
	if ($response) {
		$contents = file_get_contents($url);
		if (!empty($contents) && substr($contents,0,1) == '{')
			return true;
	}

	return false;
}

function arcadeRomHexToColor($hex)
{
	$hex = array_map('intval', $hex);
	list($r, $g, $b) = $hex;
	list($r, $g, $b) = array(dechex($r), dechex($g), dechex($b));
	$r = strlen($r)<2 ? '0' . $r : $r;
	$g = strlen($g)<2 ? '0' . $g : $g;
	$b = strlen($b)<2 ? '0' . $b : $b;
    return $r . $g . $b;
}

function ArcadeRomLanguageCode($langCode = 'en-US')
{
	global $user_info, $context, $language;

	$userLang = !empty($context['is_logged']) ? $user_info['language'] : (!empty($language) ? $language : 'english');
	$userLang = mb_strtolower($userLang);

	// the actual code is specific to this platform
	$langArray = array(
        'af-FR' => 'French',
        'ar-AR' => 'Arabic',
        'ben-BEN' => 'Bengali',
        'de-GER' => 'German',
        'el-GR' => 'Greek',
        'en-US' => 'English (United States)',
        'es-ES' => 'Spanish (España)',
		'hi-HI' => 'Hindi',
        'ja-JA' => 'Japanese',
		'jv-JV' => 'Javanese',
		'ko-KO' => 'Korean',
        'pt-BR' => 'Portuguese (Brazil)',
        'ru-RU' => 'Russian',
        'zh-CN' => 'Chinese (China)',
    );

	foreach ($langArray as $key => $val) {
		if (stripos($val, $userLang) !== FALSE)
			$langCode = $key;
	}

	return $langCode;
}

function ArcadeRetroArchRemoteLink($gameData)
{
	global $context, $scripturl, $boarddir, $boardurl, $arcadeModSettings;

	$basename = basename($gameData['external_link']);
	$ext = pathinfo($basename, PATHINFO_EXTENSION);
	$ext = empty($ext) ? 'zip' : $ext;
	//if (!in_array(strtolower($ext), explode('|', $arcadeModSettings['arcadeRomGameTypes'])) || empty($remoteLink) || empty($id))
	//	return $remoteLink;
	$newFileName = $gameData['id'] . '_' . $gameData['internal_name'] . '.' . strtolower($ext);
	$gamePath = dirname($context['arcade_rom_game_data']['path']);
	$context['html_headers'] .= '
	<script src="https://cdnjs.cloudflare.com/ajax/libs/plupload/3.1.5/plupload.full.min.js"></script>
	<script>
		$(document).ready(function() {
			var emulatorRemoteFile = "' . $gameData['external_link'] . '";
			var emulatorRemoteName = "' . $newFileName . '";
			var emulatorRemoteNameFull = "' . $gamePath . '/' . $newFileName . '";
			$.ajax({
				type: "POST",
				dataType : "html",
				contentType: "application/x-www-form-urlencoded; charset=UTF-8",
				data: {"remote_file": emulatorRemoteFile, "remote_name": emulatorRemoteName, "' . $context['session_var'] . '": "' . $context['session_id'] . '", "game_path" : emulatorRemoteNameFull},
				url: "' . $scripturl . '?action=arcade;sa=romRemoteGet;",
				error: function(arguments){console.log("error", arguments);},
				success: function(data){console.log(data);arch_game_remote_success(emulatorRemoteNameFull);}
			});
		});
	</script>';

	clearstatcache();
	if (file_exists($boarddir . '/ArcadeRetroArch/roms/' . $gamePath . '/' . $newFileName)) {
		return $gamePath . '/' .  $newFileName;
	}
	else
		return '';
}

function verboseRomGame($ok = 1, $info = "")
{
	global $arcadeModSettings;

    if ($ok == 0) {
        http_response_code(400);
    }

	if (!empty($arcadeModSettings['arcade_verbose_debug']))
		log_error(json_encode(["ok" => $ok, "info" => $info]), 'debug');

    exit(json_encode(["ok" => $ok, "info" => $info]));
}

?>
