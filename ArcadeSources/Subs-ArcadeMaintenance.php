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

function ArcadeMaintenanceDownload()
{
	global $boarddir, $txt, $context;

	$files = ArcadeScanDir($boarddir . '/games_download', 'index.php');
	array_map('unlink', $files);
	clearstatcache();
	$context['maintenance_task'] = $txt['arcade_maintenance_downloadPurge'];
}

function arcadeRemoveRomArchives($flag = 0)
{
	global $arcadeModSettings, $boarddir, $txt, $context;
	$arcadeModSettings['romGamesDirectory'] = str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$fileTypes = '{zip,gz,tar,rar,7z,ZIP,GZ,TAR,RAR,7Z}';
	$path = (isset($_REQUEST['maintenance']) && $_REQUEST['maintenance'] == 'romUploadPathPurge') || !empty($flag) ? str_replace('\\', '/', $boarddir) . '/games_rom_upload' : '';
	$dir = empty($path) && !empty($arcadeModSettings['romGamesDirectory']) ? rtrim($arcadeModSettings['romGamesDirectory'], '/') : $path;
	$text = empty($path) ? $txt['arcade_maintenance_romUploadPurge'] : $txt['arcade_maintenance_romUploadPathPurge'];

	if (empty($dir) || $dir == $boarddir)
		return false;

	foreach (glob($dir . "/*." . $fileTypes, GLOB_BRACE) as $filename)
		unlink($filename);

	clearstatcache();
	$boarddirx = str_replace('\\', '/', $boarddir);
	$context['maintenance_task'] = str_replace($boarddirx . '/', '', $text);
}

function arcadeRemoveArchives($flag = 0)
{
	global $arcadeModSettings, $boarddir, $txt, $context;
	$arcadeModSettings['gamesDirectory'] = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$fileTypes = '{zip,gz,tar,rar,7z,ZIP,GZ,TAR,RAR,7Z}';
	$path = (isset($_REQUEST['maintenance']) && $_REQUEST['maintenance'] == 'uploadPathPurge') || !empty($flag) ? str_replace('\\', '/', $boarddir) . '/games_upload' : '';
	$dir = empty($path) && !empty($arcadeModSettings['gamesDirectory']) ? rtrim($arcadeModSettings['gamesDirectory'], '/') : $path;
	$text = empty($path) ? $txt['arcade_maintenance_uploadPurge'] : $txt['arcade_maintenance_uploadPathPurge'];

	if (empty($dir) || $dir == $boarddir)
		return false;

	foreach (glob($dir . "/*." . $fileTypes, GLOB_BRACE) as $filename)
		unlink($filename);

	clearstatcache();
	$boarddirx = str_replace('\\', '/', $boarddir);
	$context['maintenance_task'] = str_replace($boarddirx . '/', '', $text);
}

function arcadeRemoveUploadEmpty($all = false)
{
	global $boarddir, $txt, $context;

	$upload_directories = array(str_replace('\\', '/', $boarddir) . '/games_upload');
	$all = isset($_REQUEST['maintenance']) && $_REQUEST['maintenance'] == 'uploadPathPurgeAll' ? 1 : (!empty($all) ? 1 : 0);
	$text = empty($all) ? $txt['arcade_maintenance_uploadPathPurgeEmpty'] : $txt['arcade_maintenance_uploadPathPurgeAll'];
	foreach ($upload_directories as $upload_directory) {
		$checkFiles = glob($upload_directory . '/*', GLOB_ONLYDIR);
		foreach ($checkFiles as $pathz) {
			$mainDir = str_replace('\\', '/', $pathz);
			if ($mainDir != $upload_directory) {
				$checkFiles = glob($mainDir . '/*');
				if (empty($checkFiles) || !empty($all))
					arcadeRmdir($mainDir);
			}
		}

		if (!empty($all)) {
			foreach (glob($upload_directory . "/*.*") as $filename) {
				if (basename($filename) != 'index.php' && basename($filename) != '.htaccess')
					unlink($filename);
			}
		}
	}

	$context['maintenance_task'] = $text;
}

function arcadeRemoveRomUploadEmpty($all = false)
{
	global $boarddir, $txt, $context;

	$upload_directories = array(str_replace('\\', '/', $boarddir) . '/games_rom_upload');
	$all = isset($_REQUEST['maintenance']) && $_REQUEST['maintenance'] == 'romUploadPathPurgeAll' ? 1 : (!empty($all) ? 1 : 0);
	$text = empty($all) ? $txt['arcade_maintenance_romUploadPathPurgeEmpty'] : $txt['arcade_maintenance_romUploadPathPurgeAll'];
	foreach ($upload_directories as $upload_directory) {
		$checkFiles = glob($upload_directory . '/*', GLOB_ONLYDIR);
		foreach ($checkFiles as $pathz) {
			$mainDir = str_replace('\\', '/', $pathz);
			if ($mainDir != $upload_directory) {
				$checkFiles = glob($mainDir . '/*');
				if (empty($checkFiles) || !empty($all))
					arcadeRmdir($mainDir);
			}
		}

		if (!empty($all)) {
			foreach (glob($upload_directory . "/*.*") as $filename) {
				if (basename($filename) != 'index.php' && basename($filename) != '.htaccess')
					unlink($filename);
			}
		}
	}

	$context['maintenance_task'] = $text;
}

function arcadeRemoveEmulatorArchives()
{
	global $settings, $boarddir, $txt, $context;
	$bdir = str_replace('\\', '/', $boarddir);
	$dirs = array($bdir . '/games_emulator_archives', $bdir . '/games_emulator_archives/emulatorjs', $bdir . '/games_emulator_archives/ruffle');
	clearstatcache();
	foreach ($dirs as $dir) {
		if (empty($dir) || $dir == $boarddir)
			continue;

		foreach (glob($dir . "/*") as $filename) {
			$filename = str_replace('\\', '/', $filename);
			if (in_array(basename($filename), array('.', '..')))
				continue;
			if (is_file($filename) && basename($filename) != 'index.php')
				unlink($filename);
			elseif (is_dir($filename) && !in_array($filename, $dirs))
				arcadeRmDir($filename);
		}
	}

	// also remove the temp directory
	if (is_dir($bdir . '/ArcadeRetroArch/updated_files'))
		arcadeRmDir($bdir . '/ArcadeRetroArch/updated_files');
	clearstatcache();
	$context['maintenance_task'] = $txt['arcade_maintenance_emulatorPathPurgeAll'];
}

function arcadeTempBiosPathPurge()
{
	global $boarddir, $arcadeModSettings;

	isAllowedTo('arcade_admin');
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] . '/' : $boarddir . '/ArcadeRetroArch/roms/';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);

	clearstatcache();
	$files = glob($romGamesDirectory . '/temp_bios/user-*');
	foreach ($files as $path) {
		if (basename($path) == 'index.php') {
			continue;
		}
		if (is_dir($path)) {
			arcadeAdminRmDir($path);
		}
		else {
			unlink($path);
			updateArcadeSettings(array('arcadeRecurrentCronTasks' => time()));
		}
	}
	clearstatcache();
	return true;
}

function arcadeMainBiosPathPurge()
{
	global $boarddir, $arcadeModSettings;

	isAllowedTo('arcade_admin');
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] . '/' : $boarddir . '/ArcadeRetroArch/roms/';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
	$mainBiosDir = $romGamesDirectory . '0_bios_' . $arcadeModSettings['arcadeRandomIdVar'];

	clearstatcache();

	if (is_dir($mainBiosDir)) {
		$files = glob($mainBiosDir . '/*');
		foreach ($files as $path) {
			if (basename($path) == 'index.php') {
				continue;
			}
			if (is_dir($path)) {
				arcadeAdminRmDir($path);
			}
			else {
				@unlink($path);
			}
		}
		clearstatcache();
	}
	return true;
}

function arcadeRemoveUnusedRomFolders()
{
	global $smcFunc, $arcadeModSettings, $boarddir, $txt, $context;
	$folders = array();
	$dirnames = array();
	$gamesdir = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : '';
	$gamesdir = rtrim($gamesdir, '/');
	$boarddirx = str_replace('\\', '/', $boarddir);

	if (!empty($gamesdir))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_game, game_directory, rom_flag
			FROM {db_prefix}arcade_games
			WHERE id_game AND rom_flag = {int:romflag}
			ORDER BY id_game',
			array(
				'romflag' => 1,
			)
		);

		while ($game = $smcFunc['db_fetch_assoc']($request))
		{
			$id = !empty($game['id_cat']) ? (int)$game['id_cat'] : 0;
			$folders[] = !empty($game['game_directory']) ? trim($game['game_directory'], '/') : '';
		}
		$smcFunc['db_free_result']($request);

		$paths = arcadeReturnPaths($gamesdir);

		foreach ($folders as $folder)
		{
			$folder = str_replace('\\', '/', $folder);
			$check = explode('/', $folder);
			if (count($check) > 1)
				$dirname = dirname($folder);
			else
				$dirname = $folder;

			$dirname = trim($dirname, '/');
			$dirnames[] = $dirname;
		}

		foreach ($paths as $path)
		{
			$parents = explode('/', $path);
			if (!empty($parents))
			{
				$parent = str_replace('\\', '/', $parents[0]);
				if (!in_array($parent, $dirnames))
				{
					$dir = dirname($gamesdir . '/' . $parent);
					$base = $gamesdir . '/' . $parent;
					$gd = $gamesdir;
					$bd = $boarddirx;
					foreach (array('/', '\\') as $sep)
					{
						$base = rtrim($base, $sep);
						$dir = rtrim($dir, $sep);
						$gd = rtrim($gd, $sep);
						$bd = rtrim($bd, $sep);
					}
					if (is_dir($base) && $base != $gd && $base != $bd)
					{
						if ($base != $dir)
						{
							$files = ArcadeAdminScanDir($base, '');
							foreach ($files as $file)
								if (!is_dir($file) && str_replace('\\', '/', $file) != str_replace('\\', '/', $gd) . '/index.php')
									unlink($file);

							if (count(scandir($base)) == 2)
								arcadeRmdir($base);

							$paths = arcadeReturnPaths($base);
							if (!empty($paths))
							{
								foreach($paths as $path)
									if ($base . '/' . $path != $gd)
										arcadeRmdir($base . '/' . $path);
							}

							if (empty(arcadeReturnPaths($base)))
							{
								if (file_exists($dir . '/master-info.xml'))
									unlink($dir . '/master-info.xml');

								if ($base != $gd)
									arcadeRmdir($base);
							}

							if (empty(arcadeReturnPaths($dir)) && $dir != $gd)
								arcadeRmdir($dir);
						}
					}
				}
			}
		}

		clearstatcache();
		$context['maintenance_task'] = str_replace($boarddirx . '/', '', $txt['arcade_maintenance_romPathPurge']);
	}
	else
	{
		$context['maintenance_task'] = str_replace($arcadeModSettings['romGamesDirectory'] . ' ~ ', $txt['arcade_maintain_none'], $txt['arcade_maintenance_romPathPurge']);
	}

}

function arcade_sanitize_file_name($filename, $type = 'path')
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

	$newName = trim($sanitized_filename, '_');
	return $newName;
}

function arcade_maintenanceRemoteFileExists($url)
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
		$contents = @file_get_contents($url);
		if (!empty($contents) && substr($contents,0,1) == '{')
			return $contents;
	}

	return false;
}

function isArcadeLink($url)
{
    $result = 404;
    if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36');
		curl_setopt($ch, CURLOPT_FILETIME, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		$headers = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		$result = !empty($info['http_code']) ? $info['http_code'] : $result;
	}
    return $result;
}

function arcadeClearDbSession()
{
	global $smcFunc, $modSettings;

	$modSettings['databaseSession_enable'] = 0;
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}sessions
		WHERE last_update > 0',
		array()
	);
}

function ArcadeScanDir($dir, $ignore = array('index.php'))
{
	$arrfiles = array();
	$ignore = !is_array($ignore) ? array($ignore) : $ignore;

	if (is_dir($dir))
	{
		if ($handle = opendir($dir))
		{
			chdir($dir);
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && substr($file, -1) !== '~' && !in_array($file, $ignore))
				{
					if (is_dir($file))
					{
						$arr = ArcadeScanDir($file, 'index.php');
						foreach ($arr as $value)
							$arrfiles[] = $dir . '/' . $value;
                    }
					else
                        $arrfiles[] = $dir . '/' . $file;
				}
			}
			chdir("../");
		}
		closedir($handle);
	}

	return $arrfiles;
}

function arcadeEnhanceThumbnails()
{
	global $context, $boarddir, $boardurl, $scripturl, $arcadeModSettings, $txt;

	isAllowedTo('arcade_admin');
	$valbytes = 'z' . openssl_random_pseudo_bytes(16);
	$varbytes = 'a' . openssl_random_pseudo_bytes(8);
	$tempbytes = 't' . openssl_random_pseudo_bytes(16);
	$backup = !empty($_REQUEST['confirm']) ? intval($_REQUEST['confirm']) : 0;
	$files = glob($boarddir . '/arcade/arcade_temp/check_thumb_*.json');
	foreach ($files as $file) {
		@unlink($file);
	}

	if (!empty($arcadeModSettings['arcade_thumb_checkvar']) && !empty($arcadeModSettings['arcade_thumb_checkval']) && file_exists($boarddir . '/arcade/arcade_temp/check_thumb_' . $arcadeModSettings['arcade_thumb_checkvar'] . '.json')) {
		$context['html_headers'] .= '
		<script>
			$(document).ready(function(){
				function arcade_thumb_check_files() {
					let fileName = "' . $boardurl . '/arcade/arcade_temp/check_thumb_' . $arcadeModSettings['arcade_thumb_checkvar'] . '.json";
					let nowtimer = "' . time() . '";
					$.ajax({
						url: fileName,
						dataType: "json",
						async: false,
						success: function(data) {
							if (data && data.arcade_thumb_check_' . $arcadeModSettings['arcade_thumb_checkvar'] . ' && data.arcade_thumb_check_' . $arcadeModSettings['arcade_thumb_checkvar'] . ' == "' . $arcadeModSettings['arcade_thumb_checkval'] . '") {
								if (data && data.percent > 99) {
									clearInterval(thumbInterval);
									setTimeout(function(){
										$("#enhanceimages_percent").css("display", "none");
										$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancethumbs'] . '");
									}, 3000);
								}
								else if (data && data.timer && nowtimer - parseInt(data.timer) > 3600) {
									clearInterval(thumbInterval);
									setTimeout(function(){
										$("#enhanceimages_percent").css("display", "none");
										$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancethumbs'] . '");
									}, 3000);
								}
								else if (data && data.percent < 100) {
									$("#enhanceimages_percent").css("display", "inline");
									$("#span_enhanceimages").html("' . $txt['arcade_thumb_percent'] . '");
									$("#enhanceimages_percent").html(data.percent + "%");
								}
							}

						},
						error: function(arguments){console.log("thumbnail enhancement processing...");}
					});
				}
				let thumbInterval = setInterval(arcade_thumb_check_files, 5000);
			});
		</script>';
	}
	else {
		$arcade_thumb_checkvar = bin2hex($varbytes);;
		$arcade_thumb_checkval = bin2hex($valbytes);
		$tempval = bin2hex($tempbytes);
		updateArcadeSettings(array('arcade_thumb_checkval' => $arcade_thumb_checkval, 'arcade_thumb_checkvar' => $arcade_thumb_checkvar));
		$context['html_headers'] .= '
		<script>
			sessionStorage.setItem("arcade_thumb_flag", "");
			$(document).ready(function(){
				$.post("' . $scripturl . '?action=admin;area=arcademaintenance;maintenance=enhanceimages;' . $context['session_var'] . '=' . $context['session_id'] . ';",
					{last:"0",backup:"' . $backup . '",type: "thumb",arcade_thumb_check_' . $arcade_thumb_checkvar . ': "' . $arcade_thumb_checkval . '", ' . $context['session_var'] . ': "' . $context['session_id'] . '"},
					function(data, status){
						sessionStorage.setItem("arcade_thumb_flag", "' . $tempval . '");
						console.log("Data: " + data + "\nStatus: " + status);
					}
				);
				function arcade_thumb_check_files() {
					if (sessionStorage.getItem("arcade_thumb_flag") == "' . $tempval . '") {
						let fileName = "' . $boardurl . '/arcade/arcade_temp/check_thumb_' . $arcade_thumb_checkvar . '.json";
						let nowtimer = "' . time() . '";
						$.ajax({
							url: fileName,
							dataType: "json",
							async: false,
							success: function(data) {
								if (data && data.arcade_thumb_check_' . $arcade_thumb_checkvar . ' && data.arcade_thumb_check_' . $arcade_thumb_checkvar . ' == "' . $arcade_thumb_checkval . '") {
									if (data && data.percent > 99) {
										clearInterval(thumbInterval);
										setTimeout(function(){
											$("#enhanceimages_percent").css("display", "none");
											$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancethumbs'] . '");
										}, 3000);
									}
									else if (data && data.timer && nowtimer - parseInt(data.timer) > 3600) {
										clearInterval(thumbInterval);
										setTimeout(function(){
											$("#enhanceimages_percent").css("display", "none");
											$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancethumbs'] . '");
										}, 3000);
									}
									else if (data && data.percent < 100) {
										$("#enhanceimages_percent").css("display", "inline");
										$("#span_enhanceimages").html("' . $txt['arcade_thumb_percent'] . '");
										$("#enhanceimages_percent").html(data.percent + "%");
									}
								}

							},
							error: function(arguments){console.log("thumbnail enhancement processing...");}
						});
					}
				}
				let thumbInterval = setInterval(arcade_thumb_check_files, 5000);
			});
		</script>';

	}
}

function arcadeEnhanceCovericons()
{
	global $context, $boarddir, $boardurl, $scripturl, $arcadeModSettings, $txt;

	isAllowedTo('arcade_admin');
	$valbytes = 'z' . openssl_random_pseudo_bytes(16);
	$varbytes = 'a' . openssl_random_pseudo_bytes(8);
	$tempbytes = 't' . openssl_random_pseudo_bytes(16);
	$backup = !empty($_REQUEST['confirm']) ? intval($_REQUEST['confirm']) : 0;
	$files = glob($boarddir . '/arcade/arcade_temp/check_cover_*.json');
	foreach ($files as $file) {
		@unlink($file);
	}

	if (!empty($arcadeModSettings['arcade_cover_checkvar']) && !empty($arcadeModSettings['arcade_cover_checkval']) && file_exists($boarddir . '/arcade/arcade_temp/check_cover_' . $arcadeModSettings['arcade_cover_checkvar'] . '.json')) {
		$context['html_headers'] .= '
		<script>
			$(document).ready(function(){
				function arcade_cover_check_files() {
					let fileName = "' . $boardurl . '/arcade/arcade_temp/check_cover_' . $arcadeModSettings['arcade_cover_checkvar'] . '.json";
					let nowtimer = "' . time() . '";
					$.ajax({
						url: fileName,
						dataType: "json",
						async: false,
						success: function(data) {
							if (data && data.arcade_cover_check_' . $arcadeModSettings['arcade_cover_checkvar'] . ' && data.arcade_cover_check_' . $arcadeModSettings['arcade_cover_checkvar'] . ' == "' . $arcadeModSettings['arcade_cover_checkval'] . '") {
								if (data && data.percent > 99) {
									clearInterval(coverInterval);
									setTimeout(function(){
										$("#enhanceimages_percent").css("display", "none");
										$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancecovers'] . '");
									}, 3000);
								}
								else if (data && data.timer && nowtimer - parseInt(data.timer) > 3600) {
									clearInterval(coverInterval);
									setTimeout(function(){
										$("#enhanceimages_percent").css("display", "none");
										$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancecovers'] . '");
									}, 3000);
								}
								else if (data && data.percent < 100) {
									$("#enhanceimages_percent").css("display", "inline");
									$("#span_enhanceimages").html("' . $txt['arcade_cover_percent'] . '");
									$("#enhanceimages_percent").html(data.percent + "%");
								}
							}

						},
						error: function(arguments){console.log("cover art enhancement processing...");}
					});
				}
				let coverInterval = setInterval(arcade_cover_check_files, 5000);
			});
		</script>';
	}
	else {
		$arcade_cover_checkvar = bin2hex($varbytes);;
		$arcade_cover_checkval = bin2hex($valbytes);
		$tempval = bin2hex($tempbytes);
		updateArcadeSettings(array('arcade_cover_checkval' => $arcade_cover_checkval, 'arcade_cover_checkvar' => $arcade_cover_checkvar));
		$context['html_headers'] .= '
		<script>
			sessionStorage.setItem("arcade_cover_flag", "");
			$(document).ready(function(){
				$.post("' . $scripturl . '?action=admin;area=arcademaintenance;maintenance=enhanceimages;' . $context['session_var'] . '=' . $context['session_id'] . ';",
					{last:"0",backup:"' . $backup . '",type: "cover",arcade_cover_check_' . $arcade_cover_checkvar . ': "' . $arcade_cover_checkval . '", ' . $context['session_var'] . ': "' . $context['session_id'] . '"},
					function(data, status){
						sessionStorage.setItem("arcade_cover_flag", "' . $tempval . '");
						console.log("Data: " + data + "\nStatus: " + status);
					}
				);
				function arcade_cover_check_files() {
					if (sessionStorage.getItem("arcade_cover_flag") == "' . $tempval . '") {
						let fileName = "' . $boardurl . '/arcade/arcade_temp/check_cover_' . $arcade_cover_checkvar . '.json";
						let nowtimer = "' . time() . '";
						$.ajax({
							url: fileName,
							dataType: "json",
							async: false,
							success: function(data) {
								if (data && data.arcade_cover_check_' . $arcade_cover_checkvar . ' && data.arcade_cover_check_' . $arcade_cover_checkvar . ' == "' . $arcade_cover_checkval . '") {
									if (data && data.percent > 99) {
										console.log("100% detected");
										clearInterval(coverInterval);
										setTimeout(function(){
											$("#enhanceimages_percent").css("display", "none");
											$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancecovers'] . '");
										}, 3000);
									}
									else if (data && data.timer && nowtimer - parseInt(data.timer) > 3600) {
										clearInterval(coverInterval);
										setTimeout(function(){
											$("#enhanceimages_percent").css("display", "none");
											$("#span_enhanceimages").html("' . $txt['arcade_maintenance_enhancecovers'] . '");
										}, 3000);
									}
									else if (data && data.percent < 100) {
										$("#enhanceimages_percent").css("display", "inline");
										$("#span_enhanceimages").html("' . $txt['arcade_cover_percent'] . '");
										$("#enhanceimages_percent").html(data.percent + "%");
									}
								}

							},
							error: function(arguments){console.log("cover art enhancement processing...");}
						});
					}
				}
				let coverInterval = setInterval(arcade_cover_check_files, 5000);
			});
		</script>';
	}
}

function arcadeEnhanceImages()
{
	global $boarddir, $boardurl, $scripturl, $arcadeModSettings, $smcFunc, $context, $settings, $txt;

	isAllowedTo('arcade_admin');
	$type = !empty($_POST['type']) && is_string($_POST['type']) && in_array($_POST['type'], array('thumb', 'cover')) ? $_POST['type'] : 'unknown';
	$backup = !empty($_POST['backup']) ? intval($_POST['backup']) : 0;
	$checkVar = !empty($type) && !empty($arcadeModSettings['arcade_' . $type . '_checkvar']) ? 'arcade_' . $type . '_check_' . $arcadeModSettings['arcade_' . $type . '_checkvar'] : '';
	$checkVal = !empty($arcadeModSettings['arcade_' . $type . '_checkval']) ? $arcadeModSettings['arcade_' . $type . '_checkval'] : '';
	if (!empty($type) && !empty($checkVar) && !empty($checkVal) && !empty($_POST[$checkVar]) && $_POST[$checkVar] == $checkVal) {
		require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
		$last = !empty($_REQUEST['continue']) && is_string($_REQUEST['continue']) ? intval($_REQUEST['continue']) : 0;
		$limitMax = !empty($arcadeModSettings['arcadeFilesMax']) && intval($arcadeModSettings['arcadeFilesMax']) > 500 ? intval($arcadeModSettings['arcadeFilesMax']) : 500;
		list($limit, $list) = array(intval($limitMax/5), array());

		$num = $smcFunc['db_query']('', '
		  SELECT count(*)
		  FROM {db_prefix}arcade_games
		  WHERE id_game > 0'
		);

		list ($gamecount) = $smcFunc['db_fetch_row']($num);
		$smcFunc['db_free_result']($num);

		$qty = empty($last) ? $limit : $last+$limit;
		$percent = ($qty / $gamecount) * 100;
		if (($last+$limit) < $gamecount) {
			arcadeProcessImages($last, $limit, $type, $backup);
			file_put_contents($boarddir . '/arcade/arcade_temp/check_' . $type . '_' . $arcadeModSettings['arcade_' . $type . '_checkvar'] . '.json', json_encode(['timer' => time(), 'percent' => $percent, $checkVar => $checkVal]));
			@chmod($boardurl . '/arcade/arcade_temp/check_' . $type . '_' . $arcadeModSettings['arcade_' . $type . '_checkvar'] . '.json', 0644);
			$dataArray = array('backup' => $backup, 'type' => $type, $checkVar => $checkVal, $context['session_var'] => $context['session_id']);
			arcadePostData($scripturl . '?action=admin;area=arcademaintenance;maintenance=enhanceimages;continue=' . strval($last+$limit) . ';' . $context['session_var'] . '=' . $context['session_id'] . ';', $dataArray);
			exit();
		}
		else {
			arcadeProcessImages($last, $limit, $type, $backup);
			file_put_contents($boarddir . '/arcade/arcade_temp/check_' . $type . '_' . $arcadeModSettings['arcade_' . $type . '_checkvar'] . '.json', json_encode(['timer' => time(), 'percent' => 100, $checkVar => $checkVal]));
			@chmod($boardurl . '/arcade/arcade_temp/check_' . $type . '_' . $arcadeModSettings['arcade_' . $type . '_checkvar'] . '.json', 0644);
			exit();
		}

		redirectexit($scripturl . '?action=admin;area=arcademaintenance;');
	}
	else {
		fatal_lang_error('no_access', false);
	}
}

function arcadePostData($url, $dataArray)
{
	$data_string = http_build_query($dataArray);
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, true);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	$result = curl_exec($ch);
	return $result;
}

function arcadeProcessImages($last, $limit, $imagetypes, $backup)
{
	global $boarddir, $scripturl, $arcadeModSettings, $modSettings, $smcFunc, $context, $settings, $txt, $user_info, $user_settings, $cookiename;

	isAllowedTo('arcade_admin');
	// this might take a while...
	$modSettings['securityDisable'] = 1;
	$modSettings['cookieTime'] = 3153600;
	createToken('admin', 'post');
	$list = array();
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	clearstatcache();
	$num = $smcFunc['db_query']('', '
      SELECT count(*)
      FROM {db_prefix}arcade_games
      WHERE id_game > 0'
	);

	list ($gamecount) = $smcFunc['db_fetch_row']($num);
	$smcFunc['db_free_result']($num);

	$request = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_directory, game.thumbnail, game.thumbnail_small, game.rom_flag, game.cover_icon, game.extra_data
		FROM {db_prefix}arcade_games AS game
		WHERE game.id_game > {int:lastid}
		ORDER BY game.id_game ASC
		LIMIT {int:xlimit}',
		array('lastid' => $last, 'xlimit' => $limit)
	);
	while ($game = $smcFunc['db_fetch_assoc']($request)) {
		$game['thumbnail'] = !empty($game['thumbnail']) ? $game['thumbnail'] : '';
		$game['thumbnail_small'] = !empty($game['thumbnail_small']) ? $game['thumbnail_small'] : '';
		$game['cover_icon'] = !empty($game['cover_icon']) ? $game['cover_icon'] : '';
		$main = empty($game['rom_flag']) ? $gamesDirectory : $romGamesDirectory;
		$gameDir = !empty($game['game_directory']) ? $main . '/' . $game['game_directory'] : $main;
		$action = empty($game['rom_flag']) ? 'arcade' : 'retro_arch';
		$gameico = !is_dir($gameDir . '/' . $game['thumbnail']) && file_exists($gameDir . '/' . $game['thumbnail']) ? $gameDir . '/' . $game['thumbnail'] : '';
		$gameicosmall = !is_dir($gameDir . '/' . $game['thumbnail_small']) && file_exists($gameDir . '/' . $game['thumbnail_small']) ? $gameDir . '/' . $game['thumbnail_small'] : '';
		$covericon = !empty($game['cover_icon']) && file_exists($gameDir . '/' . $game['cover_icon']) ? $gameDir . '/' . $game['cover_icon'] : '';
		$list[] = array(
			'thumbnail' => $gameico,
			'thumbnail_small' => $gameicosmall,
			'covericon' => $covericon
		);
	}
	$smcFunc['db_free_result']($request);
	foreach ($list as $item) {
		if ($imagetypes == 'thumb') {
			arcadeImageEnhancement($item['thumbnail'], $backup);
			arcadeImageEnhancement($item['thumbnail_small'], $backup);
		}
		else {
			arcadeImageEnhancement($item['covericon'], $backup);
		}
	}
	clearstatcache();
}

function arcadeImageEnhancement($imagePath, $backup)
{
	global $modSettings;

	isAllowedTo('arcade_admin');
	$dir = dirname($imagePath);
	$filename = pathinfo(basename($imagePath), PATHINFO_FILENAME);
	$ext = strtolower(pathinfo(basename($imagePath), PATHINFO_EXTENSION));
	$backupImagePath = $dir . '/' . $filename . '_imagick-backup.' . $ext;
	if ($backup == 3 && !is_dir($backupImagePath) && file_exists($backupImagePath)) {
		if (!is_dir($imagePath) && file_exists($imagePath)) {
			@unlink($imagePath);
			rename($backupImagePath, $imagePath);
		}
	}
	elseif (!is_dir($imagePath) && file_exists($imagePath)) {
		// simple way to check for valid gd image
		$imageString = !empty($imagePath) ? arcade_is_gd_image($imagePath) : false;
		// enhance with imagick if available
		if (!empty($imagePath) && ArcadeCheckMimeType($imagePath) && $imageString) {
			if (@extension_loaded('imagick')) {
				try {
					$imagick = new \Imagick($imagePath);
				} catch(ImagickException $e) {
					$error = true;
				}
				if (empty($error)) {
					if ($backup == 1 && !file_exists($backupImagePath)) {
						@copy($imagePath, $backupImagePath);
					}
					$valid = $imagick->valid();
					//$imagick->setResolution(150, 150);
					//$imagick->adaptiveSharpenImage(10, 2);
					if ($ext != 'gif') {
						$imagick->enhanceImage();
					}
					//$imagick->stripImage();
					$imagick->setCompressionQuality(100);
					clearstatcache(dirname($imagePath));
					$imagick->getImageBlob();
					@unlink($imagePath);
					$imagick->writeImage($imagePath);
				}
			}
		}
	}
}

function arcadeSetEngineDB($engine = 'MyISAM')
{
	global $smcFunc, $db_name;
	$tables = [];
	$knownEngines = ['innodb', 'myisam', 'aria', 'mrg_myisam'];
	$engine = !empty($engine) && in_array(strtolower($engine), array_map('strtolower', $knownEngines)) ? $engine : '';

	if (empty($engine)) {
		return false;
	}

	$request = $smcFunc['db_query']('', '
		SHOW TABLES FROM ' . $db_name,
		array()
	);

	while ($row = $smcFunc['db_fetch_row']($request)) {
		$tables[] = $row[0];
	}
	$smcFunc['db_free_result']($request);

	foreach ($tables as $table) {
		if (substr($table, 0, strlen($db_name . '.')) == ($db_name . '.')) {
			$table = substr($table, strlen($db_name . '.'));
		}

		if (stripos($table, 'arcade') !== FALSE) {
			$smcFunc['db_query']('', '
				ALTER TABLE ' . $table . ' ENGINE = ' . $engine,
				array()
			);
		}
	}

	return true;
}

function arcadeGetEngineDB($tableSpec = 'members')
{
	global $smcFunc, $db_name, $txt;

	list($schema, $schemaAll, $unknown) = [[0 => ''], [], $txt['arcade_generalized_undetected']];
	$knownEngines = ['innodb', 'myisam', 'aria', 'mrg_myisam'];

	switch($tableSpec) {
		case 'all':
			$request = $smcFunc['db_query']('', '
				SELECT * FROM INFORMATION_SCHEMA.ENGINES',
				array()
			);
			while ($row = $smcFunc['db_fetch_row']($request)) {
				if (in_array(strtolower($row[0]), array_map('strtolower', $knownEngines))) {
					$schemaAll[] = $row[0];
				}
			}
			$smcFunc['db_free_result']($request);
			$schemaAll = empty($schemaAll) ? ['InnoDB', 'MyISAM'] : $schemaAll;
			break;
		case 'default':
			$request = $smcFunc['db_query']('', '
				SELECT * FROM INFORMATION_SCHEMA.ENGINES
				WHERE SUPPORT = "DEFAULT"',
				array()
			);
			while ($row = $smcFunc['db_fetch_row']($request)) {
				$schemaY = $row;
			}
			$smcFunc['db_free_result']($request);
			foreach ($schemaY as $val) {
				if (in_array(strtolower($val), array_map('strtolower', $knownEngines))) {
					$schema[0] = $val;
				}
			}
			$schema[0] = empty($schema[0]) ? $unknown : $schema[0];
			break;
		default:
			$request = $smcFunc['db_query']('', '
				SHOW TABLE STATUS WHERE Name = "{db_prefix}{raw:name}"',
				array('name' => $tableSpec)
			);
			while ($row = $smcFunc['db_fetch_row']($request)) {
				$schemaX = empty($schemaX) ? $row : $schemaX;
			}
			$smcFunc['db_free_result']($request);
			foreach ($schemaX as $val) {
				if (in_array(strtolower($val), array_map('strtolower', $knownEngines))) {
					$schema[0] = $val;
				}
			}
			$schema[0] = empty($schema[0]) ? $unknown : $schema[0];
	}

	return !empty($schemaAll) ? $schemaAll : $schema[0];
}

?>