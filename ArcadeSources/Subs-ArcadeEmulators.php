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

function searchRomGameType($ext)
{
	list($key, $type) = array(false, 'none');

	// file types that may belong to multiple systems are not auto set
	$allTypes = array(
		'nes' => array("fds", "nes", "unif", "unf"),
		'snes' => array("smc", "fig", "sfc", "gd3", "gd7", "dx2", "bsx", "swc", "snes"),
		'n64' => array("z64", "n64", "v64"),
		'nds' => array("nds"),
		'gb' => array("gb"),
		'gba' => array("gba", "gbc", "cgb"),
		'segaSaturn' => array("ss", "gdi", "segaSaturn"),
		'segaCD' => array("scd", "segaCD"),
		'segaMD' => array("smd", "segaMD"),
		'segaMS' => array("sms", "segaMS"),
		'segaGG' => array("sgg", "segaGG"),
		'atari7800' => array("a78", "atari7800"),
		'atari5200' => array("a52", "atari5200"),
		'atari2600' => array("a26", "atari2600"),
		'lynx' => array("al", "lynx"),
		'pce' => array("pce"),
		'psx' => array("znx"),
		'psp' => array("psp", "pbp"),
		'mame' => array("mame"),
		'pcfx' => array("pcfx"),
		'ngp' => array("ngp", "ngc"),
		'ws' => array("ws", "wsc"),
		'coleco' => array("col", "cv", "coleco"),
		'vice_x64sc' => array("d64", "vice_x64sc"),
		'vice_x128' => array("d128", "vice_x128"),
		'vice_xvic' => array("vice_xvic"),
		'vice_xplus4' => array("vice_xplus4"),
		'vice_xpet' => array("vice_xpet"),
	);

	foreach($allTypes as $key => $values) {
		if(array_search(mb_strtolower($ext), $values) !== FALSE) {
			$type = $key;
			break;
		}
	}

	return $type;
}

function ManageRuffleGetExternalLink()
{
	global $arcadeModSettings;
	list($url, $defaultUrl, $response) = array($arcadeModSettings['arcadeRuffleExternalLink'], $arcadeModSettings['arcadeRuffleDefaultLink'], false);

	if(!filter_var($url, FILTER_VALIDATE_URL)) {
	   return $defaultUrl;
	}

	$curlInit = curl_init($url);
	curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curlInit,CURLOPT_HEADER,true);
	curl_setopt($curlInit,CURLOPT_NOBODY,true);
	curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
	$response = curl_exec($curlInit);
	curl_close($curlInit);

	if (!$response) {
		return $defaultUrl;
	}

	$html = file_get_contents($url);
	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML($html);

	foreach($dom->getElementsByTagName("a") as $each_node){
		if (stripos($each_node->textContent, 'self hosted') !== false && filter_var($each_node->getAttribute('href'), FILTER_VALIDATE_URL)) {
			$defaultUrl = $each_node->getAttribute('href');
			break;
		}
	}

	$dom->saveHTML();

	$_SESSION['ruffle_version_update'] = $defaultUrl;
	return $defaultUrl;
}

function ManageRuffleGetGitHubLatestDate()
{
	global $arcadeModSettings, $txt;

	$date = '0.1.0-nightly.';
	$url = (!empty($arcadeModSettings['arcadeRuffleExternalLatestDate']) ? rtrim($arcadeModSettings['arcadeRuffleExternalLatestDate'], '/') : 'https://github.com/ruffle-rs/ruffle/releases') . '/';
	$contents = file_get_contents($url);
	preg_match_all( '/<a class\=\"commit-link\"[^>]*href=[\"|\'](.*)[\"|\']/Ui', $contents, $out, PREG_PATTERN_ORDER);

	foreach ($out[1] as $k=>$v) {
		if (strpos( $v, 'https://' ) !== true) {
			$v = $url . $v;
		}
		$file = str_replace('nightly-', '', $v);
		break;
	}
	if (!empty($file)) {
		preg_match_all('/(.*?)(?:\d{4}-\d{2}-\d{2})$\1/', basename($file), $matches);
		if (!empty($matches)) {
			$date .= implode('', array_filter($matches[0]));
		}
	}
	else {
		$date = $date . $txt['arcade_generalized_file_error_unknown'];
	}

	return $date;
}

function isArcadeGitHubSiteAvailible($url)
{
    if(!filter_var($url, FILTER_VALIDATE_URL)){
        return false;
    }
    $curlInit = curl_init($url);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($curlInit);
    curl_close($curlInit);
    return $response ? true : false;
}

function rom_sanitize_file_name($filename, $type = 'path')
{
	global $arcadeModSettings;
	if ($type == 'path') {
		$filename = preg_replace('/^rom_/i', '', $filename, 1);
		$types = '.' . str_replace('|', '|.', $arcadeModSettings['arcadeRomGameTypes']);
		$romTypes = explode('|', $types);
		foreach ($romTypes as $type) {
			if (strpos($filename, '.') === FALSE)
				break;
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$filename = rtrim($filename, '_-');
			$filename = rtrim($filename, '.' . $ext);
		}
		$filename = str_replace(array(",", ".", "\\", "/", "<", ">", "@", "%", "&", "^", "=", "|"), "", $filename);
	}
	$sanitized_filename = $filename;
	$sanitized_filename = $type == 'path' ? preg_replace('/[^A-Za-z0-9-_[:blank:]]/', '', $sanitized_filename) : preg_replace('/[^A-Za-z0-9-_\.[:blank:]]/', '', $sanitized_filename);
	$sanitized_filename = preg_replace('/[[:blank:]]+/', '_', $sanitized_filename);
	$sanitized_filename = str_replace(' ', '_', $sanitized_filename);
	$sanitized_filename = str_replace('_.', '.', $sanitized_filename);

	return $sanitized_filename;
}

function arcadeUnzipFlatten($zipfile, $dest='')
{
	global $boarddir;

	$dest = !empty($dest) ? $dest : $boarddir . '/games_rom_upload';
	if (file_exists($zipfile) && is_dir($dest)) {
		$zip = new ZipArchive;
		if ($zip->open($zipfile) === true)
		{
			for ($i=0; $i < $zip->numFiles; $i++)
			{
				$entry = $zip->getNameIndex($i);
				if (strpos($entry, '.') === FALSE)
					continue;
				$fp = $zip->getStream($entry);
				$ofp = fopen($dest . '/' . basename($entry), 'w');
				if (!$fp) {
					break;
				}
				while (!feof($fp)) {
					fwrite($ofp, fread($fp, 4096));
				}
				fclose($fp);
				fclose($ofp);
			}
			$zip->close();
		}
		else
			return false;

		return true;
	}

	return false;
}

function list_getNumGamesRomInstalled($filter)
{
	global $smcFunc;

	list($where, $type, $alpha) = array('', '', '');
	if (!empty($_SESSION['arcade_manage_rom_sort_select_type']) && $_SESSION['arcade_manage_rom_sort_select_type'] != 'all')
	{
		$where .= '
			AND g.rom_system = {string:type}';
		$type = $_SESSION['arcade_manage_rom_sort_select_type'] == 'none' ? '' : $_SESSION['arcade_manage_rom_sort_select_type'];
	}
	if (!empty($_SESSION['arcade_manage_rom_sort_select_type']) && $_SESSION['arcade_manage_rom_sort_select_type'] == 'new')
		$sort = 'g.id_game DESC';
	if (!empty($_SESSION['arcade_manage_rom_sort_select_alpha']) && $_SESSION['arcade_manage_rom_sort_select_alpha'] != 'all')
	{
		$where .= '
			AND (g.game_name LIKE {string:alpha1} OR g.game_name LIKE {string:alpha2})';
		$alpha = $_SESSION['arcade_manage_rom_sort_select_alpha'];
	}

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}arcade_games AS g
		WHERE g.rom_flag = {int:rom_flag} AND g.id_game > 0' . ($filter == 'disabled' || $filter == 'enabled' ? '
			AND g.enabled = {int:enabled}' : '') . $where,
		array(
			'enabled' => $filter == 'disabled' ? 0 : 1,
			'type' => $type,
			'alpha1' => mb_strtoupper($alpha) . '%',
			'alpha2' => mb_strtolower($alpha) . '%',
			'rom_flag' => 1,
		)
	);

	list ($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

function list_getGamesRomInstalled($start, $items_per_page, $sort, $filter)
{
	global $smcFunc, $scripturl, $context, $txt;

	list($where, $type, $alpha) = array('', '', '');
	if (!empty($_SESSION['arcade_manage_rom_sort_select_type']) && $_SESSION['arcade_manage_rom_sort_select_type'] != 'all' && $_SESSION['arcade_manage_rom_sort_select_type'] != 'new')
	{
		$where .= '
			AND g.rom_system = {string:type}';
		$type = $_SESSION['arcade_manage_rom_sort_select_type'];
	}
	if (!empty($_SESSION['arcade_manage_rom_sort_select_type']) && $_SESSION['arcade_manage_rom_sort_select_type'] == 'new')
		$sort = 'g.id_game DESC';
	if (!empty($_SESSION['arcade_manage_rom_sort_select_alpha']) && $_SESSION['arcade_manage_rom_sort_select_alpha'] != 'all')
	{
		$where .= '
			AND (g.game_name LIKE {string:alpha1} OR g.game_name LIKE {string:alpha2})';
		$alpha = $_SESSION['arcade_manage_rom_sort_select_alpha'];
	}
	$request = $smcFunc['db_query']('', '
		SELECT g.game_name, g.internal_name, g.id_game, cat.id_cat, cat.cat_name, g.submit_system, g.rom_system
		FROM {db_prefix}arcade_games AS g
		LEFT JOIN {db_prefix}arcade_categories AS cat ON (cat.id_cat = g.id_cat)
		WHERE g.rom_flag = {int:rom_flag} AND g.id_game > 0' . ($filter == 'disabled' || $filter == 'enabled' ? '
			AND g.enabled = {int:enabled}' : '') . $where . '
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:games_per_page}',
		array(
			'start' => $start,
			'games_per_page' => $items_per_page,
			'sort' => $sort,
			'enabled' => $filter == 'disabled' ? 0 : 1,
			'type' => $type,
			'alpha1' => mb_strtoupper($alpha) . '%',
			'alpha2' => mb_strtolower($alpha) . '%',
			'rom_flag' => 1,
		)
	);

	$return = array();
	$idFile = 0;
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$status = 1; //$status = $row['status'];
		$idFile++;
		$return[] = array(
			'id' => $row['id_game'],
			'id_file' => $idFile,
			'name' => $row['game_name'],
			'submit_system' => $row['submit_system'],
			'rom_system' => $row['rom_system'],
			'href' => $scripturl . '?action=admin;area=manageromgames;sa=editrom;game=' . $row['id_game'],
			'category' => array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
			),
			'error' => $status != 1 ? $txt['arcade_missing_files'] : false,
		);
	}
	$smcFunc['db_free_result']($request);

	return $return;
}

function verboseRomCore($ok = 1, $info = 10)
{
	global $arcadeModSettings, $modSettings, $txt;

	$errors = explode('|', $txt['arcade_emulator_core_upload_errors']);
	$message = $info < 10 ? $errors[$info] : $txt['arcade_emulator_core_upload_ok'];
    if ($ok == 0) {
		if (!empty($arcadeModSettings['arcade_log_emulator_core'])) {
			log_error($message . ' [' . $info + 15 . ']', 'debug');
		}
        http_response_code(400);
    }

	/*
	if (!empty($arcadeModSettings['arcade_verbose_debug']))
		log_error(json_encode(["ok" => $ok, "info" => $message]), 'debug');
	*/

    exit(json_encode(["ok" => $ok, "info" => $message]));
}

function ManageArchGetFileName($url, $realfilename = '')
{
	$content = get_headers($url,1);
	$content = array_change_key_case($content, CASE_LOWER);

    // by header
	if ($content['content-disposition']) {
		$tmp_name = explode('=', $content['content-disposition']);
		if ($tmp_name[1])
			$realfilename = trim($tmp_name[1],'";\'');
	}
	else {
		$stripped_url = preg_replace('/\\?.*/', '', $url);
		$realfilename = basename($stripped_url);
	}

	return $realfilename;
}

function ManageArchReplace($filename = '')
{
	global $scripturl, $settings, $sourcedir, $boarddir, $context, $txt;

	require_once($sourcedir . '/Subs-Package.php');
	isAllowedTo('arcade_admin');
	$_SESSION['arcade_arch_update'] = !empty($_SESSION['arcade_arch_update']) ? (int)$_SESSION['arcade_arch_update'] : 0;
	$_SESSION['arcade_arch_update_file'] = !empty($_SESSION['arcade_arch_update_file']) ? $_SESSION['arcade_arch_update_file'] : '';
	$overwrite = true;
	$context['arch_message'] = $txt['arcade_arch_abort'];

	if (!empty($filename)) {
		$context['arch_message'] = '<div>' . sprintf($txt['arcade_upload_complete'], basename($filename)) . '<div>' . $txt['arcade_upload_arch_processing'];
		ManageArchFiles($filename, $overwrite);
	}
	else {
		$context['arch_message'] = $txt['arcade_upload_nofile'];
	}

	unset($_SESSION['arcade_arch_update']);
	$context['html_headers'] .= '
		<script>
			var time = 6;
			setInterval(function() {
				if (time == 0) {
					window.location.replace("' . $scripturl . '?action=admin;area=arcademaintenance;sa=' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'rom_git' : 'ruffle') . ';mode=done;#drop");
				}
				var seconds = time % 60;
				var minutes = (time - seconds) / 60;
				if (seconds.toString().length == 1) {
					seconds = "0" + seconds;
				}
				if (minutes.toString().length == 1) {
					minutes = "0" + minutes;
				}
				if (document.getElementById("time"))
					document.getElementById("time").innerHTML = "' . $txt['arcade_upload_please_wait'] . ' " + seconds;
				time--;
			}, 1000);
		</script>';
	$context['sub_template'] = 'manage_maintenance_arch_replace';

}

function getRomGameIcon($fullpath)
{
	global $arcadeModSettings, $txt;
	$qty = !empty($arcadeModSettings['arcadeRomIconRemoteQty']) && intval($arcadeModSettings['arcadeRomIconRemoteQty']) > 79 ? intval($arcadeModSettings['arcadeRomIconRemoteQty']) : 80;
	list($success, $fileNumRand) = array(false, mt_rand(1, $qty));
	if (!empty($arcadeModSettings['arcadeRomIconRemoteIcons'])) {
		$remoteFileName = $arcadeModSettings['arcadeRomIconRemoteIcons'] . '/rom_game_icon_' . ($fileNumRand < 10 ? '0' . $fileNumRand : $fileNumRand) . '.jpg';

		if (isArcadeGitHubSiteAvailible($remoteFileName)) {
			@copy($remoteFileName, $fullpath);
			clearstatcache();
			if (file_exists($fullpath))
				$success = true;
		}

		return $success;
	}
	elseif (empty($success) && !empty($arcadeModSettings['arcade_log_install_game'])) {
		log_error($txt['arcade_upload_remote_thumbnail_error'] . ' [18]');
	}

	return false;
}

function getRomGameType($fileName, $knownTypes, $dict)
{
	global $smcFunc, $context, $smfVersion, $arcadeModSettings, $boarddir, $sourcedir, $txt;

	isAllowedTo('arcade_admin');
	$ext = pathinfo($fileName, PATHINFO_EXTENSION);
	$gameName = pathinfo($fileName, PATHINFO_FILENAME);
	$typeName = pathinfo($gameName, PATHINFO_EXTENSION);
	$gameName = strpos($gameName, '.') !== FALSE && !empty($typeName) && $typeName != $gameName ? pathinfo($gameName, PATHINFO_FILENAME) : $gameName;
	$typeName = empty($typeName) ? pathinfo($fileName, PATHINFO_EXTENSION) : $typeName;
	$gameName = rtrim($gameName, '_');
	//$upload_directory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] : $boarddir . '/ArcadeRetroArch/roms';
	$upload_directory = $boarddir . '/games_rom_upload';
	$upload_directory = str_replace('\\', '/', $upload_directory);
	$upload_directory = rtrim($upload_directory, '/');

	list($renameX, $newData, $msg, $fileTypes) = array(array(), array($fileName, $typeName, $gameName), '', explode("|", str_replace('zip|7z|', '', $arcadeModSettings['arcadeRomGameTypes'])));
	list($saveTypeArray, $saveTypeArrayLong) = array(array_keys($txt['arcade_select_gametype_rom']), array_values($txt['arcade_select_gametype_rom']));
	clearstatcache();
	if (mb_strtolower($ext) == 'zip') {
		$zip = new ZipArchive;
		if ($result = $zip->open($upload_directory . '/' . $fileName) == TRUE) {
			$msg = isset($ZIP_ERROR[$result])? $ZIP_ERROR[$result] : 'unknown';
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
				if (strpos($filename, '.') !== FALSE) {
					$ext2 = pathinfo($filename, PATHINFO_EXTENSION);
					if (in_array(mb_strtolower($ext2), $fileTypes)) {
						$renameX = array($fileName, $ext, $ext2);
						$typeName = $ext2;
						break;
					}
				}
			}
		}
		elseif (!empty($zip) && !empty($arcadeModSettings['arcade_log_install_game'])) {
			$msg = !empty($txt['arcade_zip_error'][$zip]) ? $txt['arcade_zip_error'][$zip] : $txt['arcade_zip_error_unkown'];
			log_error($msg . ' [19]');
		}
		if (empty($msg) && $msg = 'unknown')
			$zip->close();
	}

	$msg = '';
	clearstatcache();
	if (!empty($renameX) && file_exists($upload_directory . '/' . $renameX[0])) {
		$new = rtrim($renameX[0], '.' . $renameX[1]) . '.' . $renameX[2] . '.' . $renameX[1];
		@rename($upload_directory . '/' . $renameX[0], $upload_directory . '/' . $new);
		$newData = array($new, $renameX[2], $gameName);
	}

	clearstatcache();
	$newData[1] = searchRomGameType($typeName);

	$temp = getRomGameName($gameName, $dict);
	$newData[2] = !empty($temp) ? $temp : ucwords($newData[2], ' ');
	return $newData;
}

function getRomGameName($gameName, $dict)
{
	// camelCase?
	$re = '/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x';
	$temp = preg_split($re, $gameName);
	if (count($temp) > 1) {
		$newName = implode(' ', $temp);
		$newName = ucwords($newName, ' ');
		return trim(preg_replace('/\s+/',' ', $newName));
	}

	// underscores?
	$temp0 = explode('_', $gameName);
	if (count($temp0) > 1) {
		$newName = str_replace('_', ' ', $gameName);
		$newName = ucwords($newName, ' ');
		return trim(preg_replace('/\s+/',' ', $newName));
	}

	// dictionary method ~ 4 words max ~ average English word length * 2
	list($temp1, $temp2, $temp3) = array(array(), array(), array());
	$temp1 = rom_sequential_gameName($gameName, $dict);
	$temp2 = is_array($temp1) && count($temp1) > 1 && strlen($temp1[1]) > 10 ? rom_sequential_gameName($temp1[1], $dict) : array();
	$temp3 = is_array($temp2) && count($temp2) > 1 && strlen($temp2[1]) > 10 ? rom_sequential_gameName($temp2[1], $dict) : array();
	if (!empty($temp2))
		$temp1[1] = '';
	if (!empty($temp3))
		$temp2[1] = '';

	$temp4 = array_merge($temp1, $temp2, $temp3);
	$temp5 = array_filter($temp4);
	$newName = implode(' ', $temp5);
	$newName = ucwords($newName, ' ');
	$newName = rtrim($newName, '_');
	return !empty($newName) ? trim(preg_replace('/\s+/',' ', $newName)) : ucwords($gameName, ' ');
}

function rom_sequential_gameName($compoundWord, $dict)
{
	global $newRomGameName;

	$newRomGameName = array();
	rom_getmultiplewords($compoundWord, '', $dict);
	return $newRomGameName;
}

function rom_binary_search($elem, $array)
{
   $top = sizeof($array) -1;
   $bot = 0;

   while($top >= $bot) {
      $p = floor(($top + $bot) / 2);
      if ($array[$p] < $elem)
        $bot = $p + 1;
      elseif ($array[$p] > $elem)
        $top = $p - 1;
      else
        return TRUE;
   }
   return FALSE;
}

function rom_getmultiplewords($word1, $word2, &$dict)
{
	global $newRomGameName;
    if (strlen($word1) == 0 || empty($dict))
		return;
    if (rom_binary_search($word1, $dict) && rom_binary_search($word2, $dict) && empty($newRomGameName)) {
        $newRomGameName = array($word2, $word1);
    }

    $word2 = $word2 . substr($word1, 0, 1);
    $word1 = substr($word1, 1);
    rom_getmultiplewords($word1, $word2, $dict);
}

function list_getNumRomGamesInstall()
{
	global $arcadeModSettings, $context, $txt, $boarddir, $scripturl;

	isAllowedTo('arcade_admin');
	$fileTypes = explode("|", $arcadeModSettings['arcadeRomGameTypes']);
	$romUploadDirectory = $boarddir . '/games_rom_upload/';
	$romUploadDirectory = str_replace('\\', '/', $romUploadDirectory);
	$scanned_directory = array_diff(scandir($romUploadDirectory), array('..', '.', '.htaccess', 'index.php'));
	$x = 0;
	$return = array();

	foreach ($scanned_directory as $file) {
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if (!in_array(mb_strtolower($ext), $fileTypes))
			continue;
		if (!empty($_SESSION['arcade_manage_rom_sort_rom_select_alpha']) && $_SESSION['arcade_manage_rom_sort_rom_select_alpha'] != 'all') {
			$firstLetterFile = mb_substr($file, 0, 1);
			$firstLetterRequest = mb_substr($_SESSION['arcade_manage_rom_sort_rom_select_alpha'], 0, 1);
			if ($firstLetterFile != $firstLetterRequest)
				continue;
		}
		$x++;
	}

	return $x;
}

function list_getRomGamesInstall($start, $items_per_page, $sort)
{
	global $arcadeModSettings, $context, $txt, $boarddir, $scripturl;

	isAllowedTo('arcade_admin');
	$fileTypes = explode("|", $arcadeModSettings['arcadeRomGameTypes']);
	$romUploadDirectory = $boarddir . '/games_rom_upload/';
	$romUploadDirectory = str_replace('\\', '/', $romUploadDirectory);
	$scanned_directory = array_diff(scandir($romUploadDirectory), array('..', '.', '.htaccess', 'index.php'));
	list($x, $return, $start)  = array(-1, array(), intval($start));
	if (isset($_REQUEST['desc']))
		$scanned_directory = array_reverse($scanned_directory);

	foreach ($scanned_directory as $file) {
		$x++;
		if ($x <= $start && $x > 1)
			continue;
		elseif ($x > ($start + $items_per_page))
			break;

		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if (!in_array(mb_strtolower($ext), $fileTypes))
			continue;
		if (!empty($_SESSION['arcade_manage_rom_sort_rom_select_alpha']) && $_SESSION['arcade_manage_rom_sort_rom_select_alpha'] != 'all') {
			$firstLetterFile = mb_substr($file, 0, 1);
			$firstLetterFile = mb_strtolower($firstLetterFile);
			$firstLetterRequest = mb_substr($_SESSION['arcade_manage_rom_sort_rom_select_alpha'], 0, 1);
			if ($firstLetterFile != $firstLetterRequest)
				continue;
		}
		$return[] = array(
			'id_file' => $x,
			'name' => $file,
			'href' => $scripturl . '?action=admin;area=managegames;sa=rom_install2;file=' . $x,
		);
		$_SESSION['arcade_rom_files'][$x] = $file;
	}

	return $return;
}

function arcadeRetroLibraryGet()
{
	global $boarddir, $arcadeModSettings;

	isAllowedTo('arcade_admin');
	checkSession('post');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAncillary.php');
	$fileId = isset($_POST['fileid']) ? $_POST['fileid'] : '';
	$git = isset($_POST['git']) ? intval($_POST['git']) : 0;
	if ($git == 3 && !empty($fileId) && !empty($_SESSION['arcade_rom_check']) && $_SESSION['arcade_rom_check'] == $fileId) {
		$remotePath = !empty($arcadeModSettings['arcadeRetroLibraryCode']) ? arcadeEncryptionCipher($arcadeModSettings['arcadeRetroLibraryCode'], array(), 'decrypt') : '';
		if (!empty($remotePath) && filter_var($remotePath, FILTER_VALIDATE_URL) && isArcadeGitHubSiteAvailible($remotePath)) {
			ArcadeGetRemoteFile($remotePath, $boarddir . '/games_emulator_archives/emulatorjs');
			MoveBiosFiles();
			exit(json_encode(array('success' => 1)));
		}
	}
	exit(json_encode(array('success' => 0)));
}

function ManageBiosFileNames($file, $check = false)
{
	$long = array("Atari - 400-800", "Atari - 5200", "Atari - 7800", "Coleco - ColecoVision", "Nintendo - Gameboy", "Nintendo - Gameboy Color", "Nintendo - Game Boy Advance", "Atari - ST", "Atari - Lynx", "PC - MAME",
		"Nintendo - Nintendo 64DD", "Nintendo - Nintendo DS", "Nintendo - Famicom Disk System", "Nintendo - Nintendo Entertainment System", "SNK - NeoGeo CD", "NEC - PC Engine - TurboGrafx 16 - SuperGrafx", "NEC - PC-FX", "Sony - PlayStation Portable",
		"Sony - PlayStation", "Sega - Mega Drive - Genesis", "Sega - Mega CD - Sega CD", "Sega - Game Gear", "Sega - Mega Drive - Genesis", "Sega - Master System - Mark III", "Sega - Saturn", "Nintendo - Super Nintendo Entertainment System");
	$short = array("atari2600", "atari5200", "atari7800", "coleco", "gb", "gb", "gba", "jaguar", "lynx", "mame", "n64", "nds", "nes", "nes", "ngp", "pce", "pcfx", "psp", "psx", "sega32x", "segaCD", "segaGG", "segaMD", "segaMS",
		"segaSaturn", "snes");
	$tempzip = array_map(function($value) { return $value . '.zip'; }, $short);
	$temp7z = array_map(function($value) { return $value . '.7z'; }, $short);
	$base = basename($file);
	$fname = pathinfo($base, PATHINFO_FILENAME);
	$ext = pathinfo($base, PATHINFO_EXTENSION);
	$fname2 = str_replace(array('_', ' '), '', strtolower($fname));
	$long2 = array_map(function($value) {return str_replace(' ', '', strtolower($value));}, $long);
	$long2tempzip = array_map(function($value) { return $value . '.zip'; }, $long2);
	$long2temp7z = array_map(function($value) { return $value . '.7z'; }, $long2);
	if (in_array($fname2, $long2)) {
		$key = array_search($fname2, $long2);
		$file = dirname($file) . '/' . $short[$key] . '.' . $ext;
	}

	if (!empty($check)) {
		$returnVal = 0;
		$temp = !empty($ext) ? rtrim($base, '.' . $ext) : $base;
		$temp = str_replace(array('_', ' '), '', strtolower($temp));
		$temp2 = $fname2 . (!empty($ext) ? '.' . $ext : '');
		if (in_array($temp2, $tempzip) || in_array($temp2, $temp7z) || in_array($temp2, $long2tempzip) || in_array($temp2, $long2temp7z)) {
			if (!empty($ext)) {
				return $file;
			}
		}
		foreach ($short as $sh) {
			if (stripos($temp, $sh) !== FALSE) {
				$returnVal = 1;
				break;
			}
		}
		foreach ($long2 as $lo) {
			if (stripos($temp, $lo) !== FALSE) {
				$returnVal = 2;
				break;
			}
		}

		return $returnVal;
	}

	return $file;
}

function MoveBiosFiles()
{
	global $boarddir, $arcadeModSettings;

	isAllowedTo('arcade_admin');
	clearstatcache();
	require_once($boarddir . '/ArcadeSources/ArcadeDownload.php');
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] : $boarddir . '/ArcadeRetroArch/roms';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
	$biosDest = $romGamesDirectory . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar'];
	if (!is_dir($biosDest)) {
		@mkdir($biosDest, 0755);
	}
	$biosFileTypes = array("atari2600", "atari5200", "atari7800", "coleco", "gb", "gb", "gba", "jaguar", "lynx", "mame", "n64", "nds", "nes", "nes", "ngp", "pce",
		"pcfx", "psp", "psx", "sega32x", "segaCD", "segaGG", "segaMD", "segaMS", "segaSaturn", "snes");
	$tempzip = array_map(function($value) { return $value . '.zip'; }, $biosFileTypes);
	$temp7z = array_map(function($value) { return $value . '.7z'; }, $biosFileTypes);
	// move possible bios files + attempt to accomodate different structured scenarios
	$checkBiosFiles = glob($boarddir . '/games_emulator_archives/emulatorjs/*.{7z,zip}', GLOB_BRACE);
	$dir =  str_replace('\\', '/', $boarddir) . '/games_emulator_archives/emulatorjs';
	list($bios, $zipFile) = array(false, array());
	foreach ($checkBiosFiles as $key => $xfile) {
		arcadeRmDir($dir . '/retroarch_system-libretro');
		list($flaga, $flagx, $flagy, $flagz) = array(false, false, false, 0);
		if(ManageBiosFileNames($xfile, true) || $xfile == 'retroarch_system-libretro.zip') {
			$flagx = true;
			$bios = true;
		}
		if (pathinfo(basename($xfile), PATHINFO_EXTENSION) == 'zip') {
			$flagz = ManageBiosFileNames($xfile, true);
			$zipFileX = str_replace('\\', '/', $xfile);
			$zip = new ZipArchive();
			if ($result = $zip->open($zipFileX) !== FALSE) {
				for($i = 0; $i < $zip->numFiles; $i++){
					$index = $zip->statIndex($i);
					$ext = pathinfo(basename($index['name']), PATHINFO_EXTENSION);
					if (!empty($index['name']) && !in_array($index['name'], array('index.php', '.', '..', basename($xfile))) && empty($ext)) {
						$check = ManageBiosFileNames($index['name'], true);
						if (!empty($check) && is_int($check)) {
							$bios = true;
							$flaga = true;
							break;
						}
					}
				}
				if ($result === TRUE) {
					@$zip->close();
				}
			}
		}

		if (empty($flaga) && !empty($flagz) && is_string($flagz)) {
			@rename($xfile, $biosDest . '/' . basename($flagz));
			continue;
		}
		if (!empty($bios)) {
			$zipFile[$key] = str_replace('\\', '/', $xfile);
			clearstatcache();
			@chmod($zipFile[$key], 0644);
			arcadeRmDir($dir . '/temp2');
			@mkdir($dir . '/temp2', 0755);
			arcadeUnzip($zipFile[$key], $dir . '/temp2', true, true);
			@unlink($checkBiosFiles[0]);
			if (!empty($xfile)) {
				$filename = pathinfo(basename($zipFile[$key]), PATHINFO_FILENAME);
				$temp = array_diff(scandir($dir . '/temp2'), array('..', '.', '.gitignore', 'README.md', 'index.php'));
				foreach ($temp as $tmp) {
					if (ManageBiosFileNames($tmp, true)) {
						if (is_dir($dir . '/temp2/' . $tmp) && count($temp) == 1) {
							@rename($dir . '/temp2/' . $tmp, $dir . '/' . $tmp);
							$filename = $tmp;
							clearstatcache();
						}
						else {
							@rename($dir . '/temp2', $dir . '/retroarch_system-libretro');
							clearstatcache();
							chmod($dir . '/retroarch_system-libretro', 0755);
							$temp = array_diff(scandir($dir . '/retroarch_system-libretro'), array('..', '.', '.gitignore', 'README.md', 'index.php'));
							$filename = 'retroarch_system-libretro';
						}
						$flagz = true;
						break;
					}
				}
				if (is_dir($dir . '/temp2/retroarch_system-libretro') && empty($flagz)) {
					//arcadeRmDir($dir . '/retroarch_system-libretro');
					//@mkdir($dir . '/retroarch_system-libretro', 0755);
					@rename($dir . '/temp2/retroarch_system-libretro', $dir . '/retroarch_system-libretro');
					clearstatcache();
					chmod($dir . '/retroarch_system-libretro', 0755);
					$temp = array_diff(scandir($dir . '/retroarch_system-libretro'), array('..', '.', '.gitignore', 'README.md', 'index.php'));
					$filename = 'retroarch_system-libretro';
				}
				elseif (is_dir($dir . '/temp2/' . $filename) && empty($flagz)) {
					@rename($dir . '/temp2/' . $filename, $dir . '/' . $filename);
					clearstatcache();
					$temp = is_dir($dir . '/' . $filename) ? array_diff(scandir($dir . '/' . $filename), array('..', '.', '.gitignore', 'README.md', 'index.php')) : array();
				}
				elseif (empty($flagz)) {
					@rename($dir . '/temp2', $dir . '/' . $filename);
					clearstatcache();
					$temp = array();
				}
				arcadeRmDir($dir . '/temp2');
				@unlink($zipFile[$key]);
				if (count($temp) > 1) {
					if ($filename != 'retroarch_system-libretro') {
						@rename($dir . '/' . $filename, $dir . '/retroarch_system-libretro');
					}
					clearstatcache();
					if (is_dir($dir . '/retroarch_system-libretro')) {
						chmod($dir, 0755);
						chmod($dir . '/retroarch_system-libretro', 0755);
						$checkBiosPaths = array_diff(scandir($dir . '/retroarch_system-libretro'), array('..', '.', '.gitignore', 'README.md', 'index.php'));
						foreach($checkBiosPaths as $path) {
							if (strpos($path, ',') !== FALSE || strpos($path, '+') !== FALSE) {
								continue;
							}
							if (is_dir($dir . '/retroarch_system-libretro/' . $path)) {
								$files = glob($dir . '/retroarch_system-libretro/' . $path . '/*');
								$new = ManageBiosFileNames($dir . '/' . basename($path) . '.zip');
								$old = ManageBiosFileNames($dir . '/' . basename($path) . '.7z');
								$zip = new ZipArchive;
								if (!empty($files) && !empty(basename($path)) && $zip->open($new, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
								{
									foreach ($files as $file) {
										$filex = str_replace('\\', '/', $file);
										$zip->addFile($filex, basename($file));
									}

									if (!@$zip->close() && !empty($arcadeModSettings['arcade_log_emulator_core'])) {
										$error = sprintf($txt['arcade_emulator_bios_decompress_error'], $dir . '/retroarch_system-libretro/' . $path, basename($new));
										log_error($error);
									}
									elseif (file_exists($old)) {
										@unlink($old);
									}
								}
							}
						}
						arcadeRmDir($dir . '/retroarch_system-libretro');
						clearstatcache();
						$checkBiosFiles = glob($boarddir . '/games_emulator_archives/emulatorjs/*.{7z,zip}', GLOB_BRACE);
					}
				}
				elseif (is_dir($dir . '/' . $filename)) {
					$checkBiosPaths = array_diff(scandir($dir . '/' . $filename), array('..', '.', '.gitignore', 'README.md'));
					clearstatcache();
					foreach($checkBiosPaths as $path) {
						if (strpos($path, ',') !== FALSE || strpos($path, '+') !== FALSE) {
							continue;
						}
						$files = is_dir($dir . '/' . $filename . '/' . $path) ? glob($dir . '/' . $filename . '/' . $path . '/*') : glob($dir . '/' . $filename . '/*');
						if (!empty($files)) {
							$new = ManageBiosFileNames($dir . '/' . basename($filename) . '.zip');
							$old = ManageBiosFileNames($dir . '/' . basename($filename) . '.7z');
							$zip = new ZipArchive;
							if (!empty($files) && !empty(basename($path)) && $zip->open($new, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
							{
								foreach ($files as $file) {
									$filex = str_replace('\\', '/', $file);
									$zip->addFile($filex, basename($file));
								}

								if (!@$zip->close() && !empty($arcadeModSettings['arcade_log_emulator_core'])) {
									$error = sprintf($txt['arcade_emulator_bios_decompress_error'], $dir . '/' . $filename . '/' . $path, basename($new));
									log_error($error);
								}
								elseif (file_exists($old)) {
									@unlink($old);
								}
							}
						}
					}
					arcadeRmDir($dir . '/' . $filename);
					clearstatcache();
					$checkBiosFiles = glob($boarddir . '/games_emulator_archives/emulatorjs/*.{7z,zip}', GLOB_BRACE);
				}
			}

			foreach($checkBiosFiles as $fileq) {
				if (basename($fileq) == 'retroarch_system-libretro.zip') {
					@unlink($fileq);
					continue;
				}
				$file = ManageBiosFileNames($fileq);
				if (in_array(basename($file), $tempzip) || in_array(basename($file), $temp7z) && file_exists($fileq)) {
					if (!@rename($fileq, $biosDest . '/' . basename($file)) && !empty($arcadeModSettings['arcade_log_emulator_core'])) {
						$error = sprintf($txt['arcade_emulator_bios_decompress_error'], $fileq, basename($file));
						log_error($error);
					}
				}
				else {
					@unlink($fileq);
				}
			}

			clearstatcache();
			foreach($checkBiosFiles as $fileq) {
				$base = ManageBiosFileNames($fileq);
				$fname = pathinfo(basename($base), PATHINFO_FILENAME);
				if (file_exists($biosDest . '/' . $fname . '.7z')) {
					@unlink($biosDest . '/' . $fname . '.7z');
				}
			}
			clearstatcache();
		}
	}

	foreach ($zipFile as $zip) {
		if (!empty($zip) && file_exists($zip)) {
			@unlink($zip);
		}
	}
	arcadeRmDir($dir . '/temp2');

	return $bios;
}

function ArcadeGetRemoteFile($fileUrl, $dest)
{
	isAllowedTo('arcade_admin');
	$ch = curl_init(str_replace(" ", "%20", $fileUrl));
	curl_setopt($ch, CURLOPT_TIMEOUT, 600);
	$fp = fopen($dest . '/' . basename($fileUrl), 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$data = base64_encode(curl_exec($ch));
	curl_close($ch);
	fwrite($fp,$data);
	fclose($fp);
}

?>