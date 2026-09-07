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

function ManageRomGamesInstall()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $modSettings, $context, $smcFunc, $sourcedir, $smcFunc, $settings, $boarddir, $smfVersion;

	isAllowedTo('arcade_admin');
	if ($smfVersion === 'v2.1') {
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}

	loadClassFile('Class-Package.php');
	if (!empty($arcadeModSettings['arcadeRomIconRemoteDict']) && isArcadeGitHubSiteAvailible($arcadeModSettings['arcadeRomIconRemoteDict'])) {
		$read = file_get_contents($arcadeModSettings['arcadeRomIconRemoteDict']);
		$read = preg_replace('/[^A-zÀ-ú0-9_\-\|]+/', '', $read);
		$read = preg_replace('/\s*/m', '',$read);
		$dict = explode('|', $read);
		sort($dict);
	}
	$context['arcade_smf_version'] = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$getLetter = !empty($_REQUEST['letter']) ? mb_ereg_replace('/[^0-9a-zA-Z]+/', '', $_REQUEST['letter']) : '';
	$firstLetter = mb_strtolower($getLetter);
	$_SESSION['arcade_manage_rom_sort_rom_select_alpha'] = !empty($_SESSION['arcade_manage_rom_sort_rom_select_alpha']) && empty($firstLetter) ? $_SESSION['arcade_manage_rom_sort_rom_select_alpha'] : (!empty($firstLetter) && $firstLetter != 'all' ? $firstLetter : '');
	$_SESSION['arcade_rom_files'] = array();
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$context['sub_template'] = 'manage_rom_install_games_list';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_rom_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_rom_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=managegames;sa=rom_install';
	$context['settings_title'] = $txt['arcade_manage_games'];
	$translate = !empty($arcadeModSettings['arcade_adjust_desc_admin']) || !empty($arcadeModSettings['arcade_adjust_desc_info']) ? 1 : 0;
	if (isset($_REQUEST['done']) && !empty($_SESSION['qaction']))
	{
		$context['show_done'] = true;
		if ($_SESSION['qaction'] == 'install')
		{
			$context['qaction_title'] = $txt['arcade_install_complete'];
			$context['qaction_text'] = $txt['arcade_install_following_games'];
		}
		elseif ($_SESSION['qaction'] == 'uninstall')
		{
			$context['qaction_title'] = $txt['arcade_uninstall_complete'];
			$context['qaction_text'] = $txt['arcade_uninstall_following_games'];
		}

		if (isset($_SESSION['qaction_data']) && is_array($_SESSION['qaction_data'])) {
			$context['qaction_data'] = $_SESSION['qaction_data'];
			$context['qaction_data'] = array_filter($context['qaction_data']);
		}

		unset($_SESSION['qaction_data']);
		unset($_SESSION['qaction']);
	}

	$romtypes = '';
	foreach ($txt['arcade_select_gametype_rom'] as $id => $romtype)
		$romtypes .= '
	<option value="' . $id . '">' . ($id == 'all' ? $txt['install_rom_type'] : $romtype) . '</option>';

	$dbgamePacks = array_filter(array_column(list_getRomGamesInstall('0', list_getNumRomGamesInstall(), 'id_file'), 'name'), function($var) {return substr($var, 0, 8) == 'GamePack' ? false : true;});

	$listOptions = array(
		'id' => 'games_list',
		'title' => '',
		'items_per_page' => $arcadeModSettings['gamesPerPage'],
		'base_href' => $scripturl . '?action=admin;area=manageromgames;sa=rom_install',
		'default_sort_col' => 'name',
		'no_items_label' => sprintf($txt['arcade_no_rom_games_available'], $scripturl . '?action=admin;area=manageromgames;sa=rom_upload'),
		'get_items' => array(
			'function' => 'list_getRomGamesInstall',
			'params' => array(
			),
		),
		'get_count' => array(
			'function' => 'list_getNumRomGamesInstall',
			'params' => array(
			),
		),
		'columns' => array(
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check installallfiles" />',
					'style' => 'width: 10px;'
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="file[]" class="check installfiles arcadefile%d" value="%s" />', /* value="%d" */
						'params' => array('id_file' => false, 'name' => true),
					),
					'style' => 'text-align: center;',
				),
			),
			'name' => array(
				'header' => array(
					'value' => '<div style="display: inline-flex;justify-content: space-between;width: 98%;"><span style="width: 20%;">' . $txt['arcade_game_name'] . '</span>' . (!empty($translate) ? '<span style="padding-right: 1rem;width: 80%;text-align: right;">' . $txt['arcade_translate_limit'] . '</span>' : '') . '</div>',
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => function ($dbrowData) {
						$link = $dbrowData['name'];
						return $link;
					},
				),
				'sort' => array(
					'default' => 'f.game_name',
					'reverse' => 'f.game_name DESC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=manageromgames;sa=rom_install2;sesc=' . $context['session_id'],
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<div style="display: ' . (!empty($translate) ? 'inline': 'none') . ';"><label for="">' . $txt['arcade_override_limit'] . '</label><input style="height: 1.4rem;" id="override_limit" type="checkbox" name="override_limit" value="1" /></div>',
				'class' => 'arcade_install_button',
				'style' => 'max-height: 1.4rem;min-height: 1.4rem;display: inline;',
			),
			array(
				'position' => 'below_table_data',
				'value' => '<input style="height: 1.4rem;" onclick="return arcadeDelClick()" id="quick_del" type="submit" name="delete_submit" value="' . $txt['quickmod_delete_selected'] . '" />',
				'class' => 'arcade_install_button',
				'style' => 'max-height: 1.4rem;min-height: 1.4rem;display: inline;',
			),
			array(
				'position' => 'below_table_data',
				'value' => '<select style="height: 1.4rem;" name="romtype">' . $romtypes . '</select>',
				'class' => 'arcade_install_button',
				'style' => 'font-size: 8pt;max-height: 1.4rem;min-height: 1.4rem;display: inline;',
			),
			array(
				'position' => 'below_table_data',
				'value' => '<button style="height: 1.4rem;" onclick="return arcadeMoreRom(this.id)" value="rom_installer" id="rom_install_submit" name="rom_install_submit" style="vertical-align: middle;">' . $txt['arcade_manage_rom_games_rename'] . '</button>',
				'class' => 'arcade_install_button',
				'style' => 'max-height: 1.4rem;min-height: 1.4rem;display: inline;',
			),
		),
	);

	$context['html_headers'] .= '
	<script type="text/javascript">
		function arcadeDelClick(val) {
			var myConf = confirm("' . $txt['arcade_are_you_sure_delete'] . '");
			return myConf;
		}
		function arcadeRomLetter(letter) {
			location.href = "' . $scripturl . '?action=admin;area=manageromgames;sa=rom_install;letter=" + letter;
		}
		function arcadeMoreRom(rom_install_submit) {
			var myConf = confirm("' . $txt['arcade_are_you_sure_rom_install'] . '");
			return myConf;
		}
		$(document).ready(function () {
			$("input.installfiles").on("change", function(evt) {' . (!empty($translate) ? '
				if($("input.installfiles:checked").length >= 16 && $("input#override_limit").checked == false) {
				   this.checked = false;
				}' : '') . '
			});
			$("input.installallfiles").on("change", function(evt) {
				invertAll(this, this.form, "file[]");' . (!empty($translate) ? '
				$("input.installfiles").each(function(i, obj) {
					if (i >= 15 && $("input.installfiles")[i].checked == true && $("input#override_limit").checked == false)
						$("input.installfiles")[i].checked = false;
				});' : '') . '
			});
		});
		function arcadeAddFlex() {
			var flexBox = document.getElementsByClassName("flow_auto");
			if (flexBox) {
				flexBox[0].style = "display: flex;justify-content: space-between;height: 2.0rem;margin-bottom: 1.0rem;font-size: 7pt;";
			}
			return false;
		}
		if (window.addEventListener)
			window.addEventListener("load", arcadeAddFlex, false);
		else if (window.attachEvent)
			window.attachEvent("onload", arcadeAddFlex);
	</script>
	<link href="' . $settings['default_theme_url'] . '/css/arcade-upload' . ($smfVersion == 'v2.1' ? '2' : '') . '.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />';
	// <input onclick="arcadeMoreRom(this.id)" type="checkbox" value="rom_installer" id="rom_install_submit" name="rom_install_submit" />
	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);
}

function ManageRomGamesInstall2()
{
	global $smcFunc, $context, $smfVersion, $arcadeModSettings, $boarddir, $sourcedir;

	isAllowedTo('arcade_admin');
	checkSession('post');
	$dict = array();

	if ($smfVersion === 'v2.1')
		validateToken('admin', 'post', false);

	if (!empty($arcadeModSettings['arcadeRomIconRemoteDict']) && isArcadeGitHubSiteAvailible($arcadeModSettings['arcadeRomIconRemoteDict'])) {
		$read = file_get_contents($arcadeModSettings['arcadeRomIconRemoteDict']);
		$read = preg_replace('/[^A-zÀ-ú0-9_\-\|]+/', '', $read);
		$read = preg_replace('/\s*/m', '',$read);
		$dict = explode('|', $read);
		sort($dict);
	}

	$target_directory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] . '/' : $boarddir . '/ArcadeRetroArch/roms';
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$target_directory = str_replace('\\', '/', $target_directory);
	$target = rtrim($target_directory, '/');
	$upload_directory = $boarddir . '/games_rom_upload';
	$fileTypes = explode("|", $arcadeModSettings['arcadeRomGameTypes']);
	list($cacheCheck, $status, $count) = array(false, array(), -1);
	if (!empty($_SESSION['qaction']))
		unset($_SESSION['qaction']);
	if (!empty($_SESSION['qaction_data']))
		unset($_SESSION['qaction_data']);
	$romType = isset($_POST['romtype']) && $_POST['romtype'] != 'all' ? $_POST['romtype'] : '';
	if (isset($_POST['rom_install_submit']) && $_POST['rom_install_submit'] == 'rom_installer' && isset($_POST['file']))
	{
		clearstatcache();
		$scanned_directory = array_diff(scandir($upload_directory), array('..', '.', '.htaccess', 'index.php'));
		foreach ($_SESSION['arcade_rom_files'] as $file) {
			$count++;
			if (!in_array($file, $_POST['file'])) //if (!isset($_POST['file'][$count]))
				continue;
			list($ext, $count, $rom_processed, $romExists) = array(pathinfo($file, PATHINFO_EXTENSION), 0, false, false);
			// [ROM] archives derived from another SMF Arcade can only be zip files
			if (mb_strtolower($ext) == 'zip' && strlen(mb_substr(basename($file), 5)) > 0 && mb_substr(mb_strtolower(basename($file)), 0, 4) == 'rom_') {
				if (strlen(mb_substr(basename($file), 5)) > 0 && mb_substr(mb_strtolower(basename($file)), 0, 4) == 'rom_') {
					$newfile = mb_substr($file, 4);
					@rename($upload_directory . '/' . $file, $upload_directory . '/' . $newfile);
					$file = file_exists($upload_directory . '/' . $newfile) ? $newfile : $file;
				}
				$newname = rom_sanitize_file_name($file, 'path');
				$sanitizedFilename = rom_sanitize_file_name($file, 'file');
				$newname = str_replace('----', '--', $newname);
				$newname = trim($newname, '_');
				$path = rtrim($upload_directory, '/') . '/' . $newname;
				$phpFileName = mb_substr($file, 0, -(strlen($ext)+1)) . '.php';
				list($romExists, $x) = array(false, 1);
				clearstatcache();
				if (is_dir($target_directory . '/' . $newname) && empty($arcadeModSettings['arcade_install_duplicate_game'])) {
					while ($x < 100) {
						if (!is_dir($target_directory . '/' . $newname . strval($x))) {
							@rename($upload_directory . '/' . $file, $upload_directory . '/' . $newname . strval($x) . '.' . $ext);
							clearstatcache();
							if (file_exists($upload_directory . '/' . $newname . strval($x) . '.' . $ext)) {
								$newname = $newname . strval($x);
								$file = $newname . '.' . $ext;
								$sanitizedFilename = rom_sanitize_file_name($file, 'file');
								$path = rtrim($upload_directory, '/') . '/' . $newname;
							}
							break;
						}
						$x++;
					}
				}
				elseif (is_dir($target_directory . '/' . $newname)) {
					@unlink($upload_directory . '/' . $file);
					arcadeRmDir($path);
					$status[] = array(
						'id' => 0,
						'name' => '[ROM] ' . $file,
						'error' => array('arcade_directory_make_exists', array($path)),
					);
					$romExists = 1;
				}
				if (empty($romExists))
					$files = arcadeUnzip($upload_directory . '/' . $file, $path . '/', false, false);
				// find internal game name if archive is mismatched
				clearstatcache();
				if (!file_exists($path . '/' . $phpFileName) && empty($romExists)) {
					list($xfiles, $xpath) = array(array(), basename($path));
					$xfiles = glob($path . '/*.{php}', GLOB_BRACE);
					foreach ($xfiles as $xfile) {
						$fileTemp = file_get_contents($xfile);
						if (!empty($fileTemp) && stripos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
							$yfile = pathinfo(basename($xfile), PATHINFO_FILENAME);
							if (stripos($path, $yfile) !== FALSE) {
								$phpFileName = basename($xfile);
								$filedir = dirname($path);
								@chmod($path, 0755);
								copyArcadeDirectory($path, $filedir . '/' . $yfile);
								arcadeRmDir($path);
								clearstatcache();
								if (file_exists($upload_directory . '/' . $file))
									@unlink($upload_directory . '/' . $file);
								$file = $yfile . '.zip';
								$newname = rom_sanitize_file_name($file, 'path');
								$sanitizedFilename = rom_sanitize_file_name($file, 'file');
								$newname = str_replace('----', '--', $newname);
								$path = rtrim($upload_directory, '/') . '/' . $newname;
								break;
							}
						}
					}
				}
				if (file_exists($path . '/' . $phpFileName) && empty($romExists)) {
					$fileTemp = file_get_contents($path . '/' . $phpFileName);
					if (!empty($fileTemp) && strpos(str_replace(' ', '', $fileTemp), '$config=array(') !== false) {
						$phpInfo = readPhpGameInfo($newname, ($newname . '.php' == $phpFileName ? $newname : ''), true, 1);
						$romFlag = !empty($phpInfo['rom_flag']) || !empty($phpInfo['romflag']) ? 1 : 0;
						if (!empty($phpInfo) && !empty($romFlag)) {
							$phpInfo['rom_system'] = !empty($romType) ? $romType : (!empty($phpInfo['rom_system']) ? $phpInfo['rom_system'] : 'none');
							list($_SESSION['qaction'], $phpInfo['translate_skip'], $saveType, $set_category) = array('install', 0, 'rom', isset($_POST['set_category']) ? floatval($_POST['set_category']) : 0);
							$phpInfo['cover_icon'] = !empty($phpInfo['cover_icon']) ? $phpInfo['cover_icon'] : '';
							if (empty($phpInfo['thumbnail'])) {
								@copy($settings['default_theme_dir'] . '/images/arc_icons/Default.gif', $path . '/Default1.gif');
								$phpInfo['thumbnail'] = 'Default1.gif';
							}
							if (empty($phpInfo['thumbnail_small'])) {
								@copy($settings['default_theme_dir'] . '/images/arc_icons/Default.gif', $path . '/Default2.gif');
								$phpInfo['thumbnail_small'] = 'Default2.gif';
							}
							if (empty($phpInfo['covericon'])) {
								@copy($settings['default_theme_dir'] . '/images/arc_icons/romgamecover.gif', $path . '/romgamecover.gif');
								$phpInfo['covericon'] = 'romgamecover.gif';
							}
							$rom = createNewRomGame($phpInfo, $set_category, array('upload_dir' => 'games_rom_upload'));
							if (!empty($rom)) {
								@rename($path, $arcadeModSettings['romGamesDirectory'] . '/' . $rom[1]);
								clearstatcache();

								@unlink(rtrim($upload_directory, '/') . '/' . $file);
								clearstatcache();
								$status[] = array(
									'id' => $rom[0],
									'name' => '[ROM] ' . $phpInfo['name'],
									'error' => false,
								);
								$rom_processed = true;
								$cacheCheck = 1;
							}
						}
					}
				}
			}
			$msg = '';
			if (empty($rom_processed) && empty($romExists)) {
				clearstatcache();
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				$newname = rom_sanitize_file_name($file, 'path');
				$sanitizedFilename = rom_sanitize_file_name($file, 'file');
				$newname =  strlen(mb_substr($newname, 5)) > 0 && mb_substr(mb_strtolower($newname), 0, 4) == 'rom_' ? mb_substr($newname, 4) : $newname;
				$newname = str_replace('----', '--', $newname);
				$newname = trim($newname, '_');
				$path = rtrim($upload_directory, '/') . '/' . $newname;
				list($romExists, $x) = array(0, 1);
				if (is_dir($target_directory . '/' . $newname) && empty($arcadeModSettings['arcade_install_duplicate_game'])) {
					while ($x < 100) {
						if (!is_dir($target_directory . '/' . $newname . strval($x))) {
							@rename($upload_directory . '/' . $file, $upload_directory . '/' . $newname . strval($x) . '.' . $ext);
							clearstatcache();
							if (file_exists($upload_directory . '/' . $newname . strval($x) . '.' . $ext)) {
								$newname = $newname . strval($x);
								$file = $newname . '.' . $ext;
								$sanitizedFilename = rom_sanitize_file_name($file, 'file');
								$path = rtrim($upload_directory, '/') . '/' . $newname;
							}
							break;
						}
						$x++;
					}
				}
				elseif (is_dir($target_directory . '/' . $newname)) {
					$romExists = 1;
				}

				if (is_dir($upload_directory . '/' . $file) || !in_array(mb_strtolower($ext), $fileTypes) || empty($newname))
					continue;
				// attempt to add ROM to the database
				list($fileExists, $types, $sanitizedFilename, $internalName, $newname) = array(1, explode('|', $arcadeModSettings['arcadeRomGameTypes']), rom_sanitize_file_name($file, 'file'), $newname, '');
				$request = $smcFunc['db_query']('', '
					SELECT game_file, internal_name, game_directory, rom_flag
					FROM {db_prefix}arcade_games
					WHERE game_directory = {string:dirname} OR internal_name = {string:internalname}
					LIMIT 1',
					array('dirname' => $newname, 'internalname' => $internalName)
				);

				while ($dbrow = $smcFunc['db_fetch_assoc']($request)) {
					$romFlag = !empty($dbrow['rom_flag']) ? 1 : 0;
					$romExists = !empty($dbrow['game_file']) && $dbrow['game_file'] == $sanitizedFilename ? 1 : $romExists;
					$romExists = !empty($dbrow['game_directory']) && $dbrow['game_directory'] == $newname && !empty($romFlag) ? 1 : $romExists;
					$newname = !empty($dbrow['internal_name']) && $dbrow['internal_name'] == $internalName ? $dbrow['internal_name'] : $newname;
				}
				$smcFunc['db_free_result']($request);
				if (empty($romExists) && !is_dir($path)) {
					$set_category = isset($_POST['set_category']) ? floatval($_POST['set_category']) : 0;
					list($gameType, $name, $sanitizedImagename) = array('none', $newname, '');
					$gameData = getRomGameType($file, $types, $dict);
					list($gameFile, $gameType, $name, $pathname, $internals, $numb) = array($gameData[0], $gameData[1], $gameData[2], $newname, arcadeInternalName($newname), 0);
					@mkdir($path, 0755);
					if ($gameFile == $file && file_exists($upload_directory . '/' . $file))
						@rename($upload_directory . '/' . $file, $path . '/' . $sanitizedFilename);
					elseif (file_exists($upload_directory . '/' . $gameFile))
						@rename($upload_directory . '/' . $gameData[0], $path . '/' . $sanitizedFilename);
					clearstatcache();
					if (!file_exists($path . '/' . $sanitizedFilename)) {
						$files = glob($upload_directory . '/*.{' . $ext . '}', GLOB_BRACE);
						foreach ($files as $file) {
							$fileName = basename($file);
							if (!in_array($fileName, array('index.php', '.htaccess'))) {
								@rename($file, $path . '/' . $sanitizedFilename);
								break;
							}
						}
					}
					@copy($upload_directory . '/index.php', $path . '/index.php');
					$sanitizedImagename = (mb_substr($sanitizedFilename, 0, -mb_strlen('.' . $ext))) . '.jpg';
					$getIcon = getRomGameIcon($path . '/' . $sanitizedImagename);
					while(in_array($newname, $internals))
					{
						$numb++;
						$newname = $numb > 1 ? substr($newname, 0, -1) . (string)$numb : $newname . (string)$numb;
					}
					$romType = !empty($romType) ? $romType : $gameType;
					$dims = rom_getRawDimensions($romType);
					$gameinfo = array(
						'category' => intval($set_category),
						'internal_name' => $newname,
						'name' => $name,
						'game_name' => $name,
						'submit_system' => 'rom',
						'rom_system' => $romType,
						'description' => '',
						'help' => '',
						'thumbnail' => !empty($getIcon) ? $sanitizedImagename : '',
						'thumbnail_small' => !empty($getIcon) ? $sanitizedImagename : '',
						'cover_icon' => '',
						'js_insertion' => 0,
						'file' => $sanitizedFilename,
						'game_directory' => $pathname,
						'flash' => array(
							'width' => $dims[0],
							'height' => $dims[1],
							'bgcolor' => '000000'
						),
						'extra_data' => array(
							'width' => $dims[0],
							'height' => $dims[1],
							'flash_version' => 0,
							'type' => '',
							'external_link' => '',
							'background_color' => array(
								hexdec('00'),
								hexdec('00'),
								hexdec('00')
							),
						),
						'score_type' => 2,
						'rom_flag' => 1,
						'translate_skip' => 1,
					);
					$rom = createNewRomGame($gameinfo, $set_category, array('upload_dir' => 'games_rom_upload'));
					if ($rom) {
						$extraPath = pathinfo(basename($path), PATHINFO_FILENAME);
						@rename($path, $target . '/' . basename($path));
						@unlink($upload_directory . '/' . $file);
						clearstatcache();
						if (is_dir($target . '/' . basename($path) . '/' . $extraPath) && strpos(basename($sanitizedImagename), '.') !== FALSE && file_exists($target . '/' . basename($path) . '/' . $sanitizedImagename))
							arcadeRmDir($target . '/' . basename($path) . '/' . $extraPath);
						$status[] = array(
							'id' => $rom[0],
							'name' => '[ROM] ' . $gameinfo['name'],
							'error' => false,
						);
						$cacheCheck = 1;
					}
					else {
						$status[] = array(
							'id' => 0,
							'name' => '[ROM] ' . $file,
							'error' => array('arcade_rom_db_failure', array('[ROM] ' . $gameinfo['name'])),
						);
					}
				}
				else {
					@unlink($upload_directory . '/' . $file);
					arcadeRmDir($path);
					$status[] = array(
						'id' => 0,
						'name' => '[ROM] ' . $file,
						'error' => array('arcade_directory_make_exists', array($path)),
					);
				}
			}
			clearstatcache();
		}
		if (!empty($status)) {
			$status = array_map('array_filter', $status);
			$status = array_filter($status);
		}
		$_SESSION['qaction'] = 'install';
		$_SESSION['qaction_data'] = $status;
		if (!empty($cacheCheck) && !empty($arcadeModSettings['arcade_cache_enable'])) {
			@ini_set('memory_limit', '256M');
			$gameRelatedCache = array(
				'arcade_games_list',
				'arcade_games_suggest',
				'arcade_gamecount',
				'arcade_games_recommended',
				'arcade_games_query_small',
				'arcade_games_latestA',
				'arcade_new_gamesA',
				'arcade_thumbsA0',
				'arcade_thumbsA1',
				'arcade_thumbsA2',
				'arcade_thumbsA3',
				'arcade_newestB',
				'arcade_games_latestC',
				'arcade_newestC',
				'arcade_games_nocat',
			);
			foreach ($gameRelatedCache as $cache) {
				$rom2 = '_rom';
				cache_put_data($cache, null, 0);
				cache_put_data($cache . $rom2, null, 0);
			}
		}
		redirectexit('action=admin;area=manageromgames;sa=rom_install;done;sesc=' . $context['session_id']);
	}

	if (!isset($_REQUEST['file']) && isset($_REQUEST['done']))
		redirectexit('action=admin;area=manageromgames;sa=rom_install;sesc=' . $context['session_id']);
	if (!isset($_REQUEST['file'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_rom_games_selected', false);
	}

	if (!is_array($_REQUEST['file']))
		$romxgames = array($_REQUEST['file']);
	else
		$romxgames = $_REQUEST['file'];

	$romgames = array();
	foreach ($romxgames as $id => $dbgame)
		$romgames[] = urldecode($dbgame);

	$romgames = array_unique($romgames);

	if (count($romgames) == 0) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected2', false);
	}

	if (!empty($cacheCheck) && !empty($arcadeModSettings['arcade_cache_enable'])) {
		$gameRelatedCache = array(
			'arcade_games_list',
			'arcade_games_suggest',
			'arcade_gamecount',
			'arcade_games_recommended',
			'arcade_games_query_small',
			'arcade_games_latestA',
			'arcade_new_gamesA',
			'arcade_thumbsA0',
			'arcade_thumbsA1',
			'arcade_thumbsA2',
			'arcade_thumbsA3',
			'arcade_newestB',
			'arcade_games_latestC',
			'arcade_newestC',
			'arcade_games_nocat',
		);
		foreach ($gameRelatedCache as $cache) {
			$value = in_array($cache, array('arcade_gamecount', 'arcade_online_count', 'arcade_online_guestcount', 'arcade_user_counts')) ? 0 : array();
			$rom2 = '_rom';
			cache_put_data($cache, null, 0);
			cache_put_data($cache . $rom2, null, 0);
		}
	}

	if (isset($_REQUEST['delete_submit']))
	{
		if (!empty($romgames))
		{
			clearstatcache();
			foreach ($romgames as $fileNum) {
				if (!empty($fileNum) && is_file($upload_directory . '/' . $fileNum) && file_exists($upload_directory . '/' . $fileNum))
					@unlink($upload_directory . '/' . $fileNum);
					//uninstallRomGames($_SESSION['arcade_rom_files'][$fileNum], false, true);
			}
		}
		clearstatcache();
		redirectexit('action=admin;area=manageromgames;sa=rom_install;sesc=' . $context['session_id']);
	}
}

function ManageRomGamesUpload()
{
	global $scripturl, $txt, $arcadeModSettings, $modSettings, $context, $boarddir, $sourcedir, $smcFunc, $settings, $user_settings, $cookiename, $user_info, $smfVersion;

	isAllowedTo('arcade_admin');
	$upload_directory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] . '/' : $boarddir . '/ArcadeRetroArch/roms';
	$upload_directory = str_replace('\\', '/', $upload_directory);
	require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	if ($smfVersion == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}
	else
		require_once($sourcedir . '/Subs-Auth.php');

	if (!empty($arcadeModSettings['arcadeUploadSystem']))
	{
		unset($_SESSION['arcadeUploadFiles']);

		// this is done so we are not logged-out whilst using the container
		if ($smfVersion !== 'v2.1')
		{
			$cookie_state = (empty($modSettings['localCookies']) ? 0 : 1) | (empty($modSettings['globalCookies']) ? 0 : 2);
			$data = serialize(array($user_info['id'], sha1($user_settings['passwd'] . $user_settings['password_salt']), time() + (60 * $modSettings['cookieTime']), $cookie_state));
			$_SESSION['login_' . $cookiename] = $data;
			setLoginCookie(60 * $modSettings['cookieTime'], $user_info['id'], sha1($user_settings['passwd'] . $user_settings['password_salt']));
		}

		$update = array('member_ip' => arcade_get_client_ip(), 'member_ip2' => $_SERVER['BAN_CHECK_IP'], 'passwd_flood' => '');
		$user_info['is_guest'] = false;
		$user_settings['additional_groups'] = explode(',', $user_settings['additional_groups']);
		$user_info['is_admin'] = $user_settings['id_group'] == 1 || in_array(1, $user_settings['additional_groups']);
		$update['last_login'] = time();
		updateMemberData($user_info['id'], $update);

		// css & js implementation
		$context['html_headers'] .= '
	<script type="text/javascript">
		sessionStorage.clear();
	</script>
	<link href="' . $settings['default_theme_url'] . '/css/arcade-upload' . ($context['arcade_smf_version'] == 'v2.1' ? '2' : '') . '.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-jquery.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade.knob.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeUIWidget.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeIframeTransport.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeFileUpload.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeUploadScript.js?' . $suffixVersion . '"></script>';
		if ($smfVersion == 'v2.1')
		{
			$context['html_headers'] .= '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeSuperfish.js?' . $suffixVersion . '"></script>
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/smf_jquery_plugins.js?' . $suffixVersion . '"></script>';
		}
	}

	if (!is_writable($upload_directory) && !chmod($upload_directory, 0755)) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($upload_directory));
	}

	$context['post_max_size'] = arcade_return_bytes(ini_get('post_max_size')) / 1048576;
	$context['post_max_size'] = preg_replace('/[^0-9\.]/', '', $context['post_max_size']);
	$context['post_max_size'] = round($context['post_max_size'], 2);
	$context['rom_file_types'] = '.' . str_replace('|', ', .', $arcadeModSettings['arcadeRomGameTypes']);
	$context['rom_upload_file_types'] = !empty($arcadeModSettings['arcadeRomArchiveFileTypes']) ? str_replace('|', ' | ', $arcadeModSettings['arcadeRomArchiveFileTypes']) : 'zip | 7z';

	// Template
	$context['sub_template'] = 'manage_rom_games_upload';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_rom_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_rom_upload_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=manageromgames;sa=rom_upload';
}

function ManageRomGamesUpload2()
{
	global $txt, $arcadeModSettings, $modSettings, $smcFunc, $context, $cookiename, $smfVersion, $boarddir;

	isAllowedTo('arcade_admin');
	//checkSession('post');

	$postVar = !empty($_FILES['attachment']) ? $_FILES['attachment'] : array();
	$dir = 'games_rom_upload';
	$upload_directory = $boarddir . '/' . $dir;
	$upload_directory = str_replace('\\', '/', $upload_directory);
	$allowed = explode("|", $arcadeModSettings['arcadeRomGameTypes']);
	if (!empty($arcadeModSettings['arcadeRomIconRemoteDict']) && isArcadeGitHubSiteAvailible($arcadeModSettings['arcadeRomIconRemoteDict'])) {
		$read = file_get_contents($arcadeModSettings['arcadeRomIconRemoteDict']);
		$read = preg_replace('/[^A-zÀ-ú0-9_\-\|]+/', '', $read);
		$read = preg_replace('/\s*/m', '',$read);
		$dict = explode('|', $read);
		sort($dict);
	}

	// HTML5 / jQuery 1MB chunk uploads
	if (empty($postVar) && isset($_FILES['upl']))
	{
		// A list of permitted file extensions
		if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0)
		{
			$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

			if(!in_array(mb_strtolower($extension), $allowed))
			{
				echo '{"status":"error"}';
				exit;
			}

			$_FILES['upl']['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['upl']['name']);
			$target = rtrim($upload_directory, '/');
			$newname = trim(basename($_FILES['upl']['name']));
			$newname = str_replace('----', '--', $newname);
			if (empty($newname))
				exit;

			// remove compressed game archive if it already exists
			if (empty($_SESSION['arcadeUploadFiles'][$newname]) && file_exists($target . '/' . $newname))
			{
				unlink($target . '/' . $newname);
				clearstatcache();
			}

			$_SESSION['arcadeUploadFiles'][$newname] = 'initialized';
			$com = fopen($target . '/' . $newname, "ab");
			$in = fopen($_FILES['upl']['tmp_name'], "rb");
			if ($in)
			{
				while ($buff = fread($in, 4096))
					fwrite($com, $buff);

				fclose($in);
			}
			fclose($com);
			@unlink($_FILES['upl']['tmp_name']);
			@chmod($target . '/' . $newname, 0644);
			clearstatcache();
			echo '{"status":"success"}';
			exit;

		}

		echo '{"status":"error"}';
		exit;
	}

	list($fileExists, $newname) = array(0, '');

	if ($smfVersion == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}

	if (empty($postVar) && empty($arcadeModSettings['arcadeUploadSystem']))
		redirectexit('action=admin;area=manageromgames;sa=rom_install');
	elseif (empty($postVar))
		die($txt['arcade_upload_nofile']);

	foreach ($postVar['tmp_name'] as $n => $dummy)
	{
		if ($postVar['name'][$n] == '')
			continue;

		$postVar['name'][$n] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $postVar['name'][$n]);
		$newname = trim(basename($postVar['name'][$n]));
		$target = rtrim($upload_directory, '/');

		$tmp_name = $postVar['tmp_name'][$n];
		$ext = pathinfo($newname, PATHINFO_EXTENSION);
		if (!in_array(mb_strtolower($ext), $allowed))
			continue;

		if ($target != $upload_directory)
		{
			if (!is_dir($target) && !mkdir($target, 0755) && empty($arcadeModSettings['arcadeUploadSystem'])) {
				arcadeClearSession();
				fatal_lang_error('arcade_not_writable', false, array($target));
			}
			elseif (!is_dir($target) && !mkdir($target, 0755)) {
				arcadeClearSession();
				die($txt['arcade_not_writable'] . ' ~ ' . $target);
			}

			if (!is_writable($target) && !chmod($target, 0755) && empty($arcadeModSettings['arcadeUploadSystem'])) {
				arcadeClearSession();
				fatal_lang_error('arcade_not_writable', false, array($target));
			}
			elseif (!is_writable($target) && !chmod($target, 0755)) {
				arcadeClearSession();
				die($txt['arcade_not_writable'] . ' ~ ' . $target);
			}
		}

		if (!file_exists($target . '/' . $newname))
		{
			if (empty(filesize($tmp_name)))
			{
				if (file_exists($tmp_name))
					unlink($tmp_name);
				clearstatcache();
				arcadeClearSession();
				fatal_lang_error('arcade_upload_file_size', false);
			}

			$fileExists = 0;
			$com = fopen($target . '/' . $newname, "ab");
			$in = fopen($tmp_name, "rb");
			if ($in)
			{
				// pause on every MB
				while ($buff = fread($in, 4096)) {
					fwrite($com, $buff);
				}
				fclose($in);
			}
			fclose($com);

			//move_uploaded_file($postVar['tmp_name'][$n], $target . '/' . $newname);

			if (!file_exists($target . '/' . $newname) && empty($arcadeModSettings['arcadeUploadSystem'])) {
				arcadeClearSession();
				fatal_lang_error('arcade_upload_file', false);
			}
			elseif (!file_exists($target . '/' . $newname)) {
				arcadeClearSession();
				die($txt['arcade_upload_file']);
			}

			@chmod($target . '/' . $newname, 0644);
		}
	}

	if (empty($arcadeModSettings['arcadeUploadSystem']))
		redirectexit('action=admin;area=manageromgames;sa=rom_install');
	elseif (!empty($newname) && empty($fileExists)) {
		arcadeClearSession();
		die(sprintf($txt['arcade_upload_complete'] ,$newname));
	}
	elseif (!empty($fileExists)) {
		arcadeClearSession();
		die(sprintf($txt['arcade_upload_exists'], $newname));
	}
	else {
		arcadeClearSession();
		die($txt['arcade_upload_nofile']);
	}
}

function ManageRetroArchCore()
{
	global $scripturl, $txt, $arcadeModSettings, $modSettings, $context, $boarddir, $boardurl, $sourcedir, $smcFunc, $settings, $user_settings, $cookiename, $user_info, $smfVersion;
	isAllowedTo('arcade_admin');
	if (empty($_SESSION['emulator_admin'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_function_disabled', false);
	}

	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] : $boarddir . '/ArcadeRetroArch/roms';
	$romGamesUrl = !empty($arcadeModSettings['romGamesUrl']) ? $arcadeModSettings['romGamesUrl'] : $boardurl . '/ArcadeRetroArch/roms';
	$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
	$biosUrl = $romGamesUrl . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar'];
	$biosDest = $romGamesDirectory . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar'];
	$emulatorTypes = !empty($txt['emulator_types']) ? explode('|', $txt['emulator_types']) : array('EmulatorJS', 'Ruffle');
	$destination = $_SESSION['emulator_admin'] == 'emulatorjs' ? $boarddir . '/ArcadeRetroArch' : $settings['default_theme_dir'] . '/arcade_scripts/ruffle';
	$context['emulator_type_name'] = $_SESSION['emulator_admin'] == 'emulatorjs' ? $emulatorTypes[0] : $emulatorTypes[1];
	unset($_SESSION['ruffle_version_update']);
	$ruffleExternal = $_SESSION['emulator_admin'] == 'emulatorjs' ? $arcadeModSettings['arcadeRuffleDefaultLink'] : ManageRuffleGetExternalLink();
	$context['arcade_emulator_detected'] = 0;
	$context['arcade_emulator_version'] = sprintf($txt['arcade_emulator_no_file_date'], $context['emulator_type_name']);
	$msg = explode('|', $txt['arcadeCDN_Availability']);
	$dest = $boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin'];
	$checkFiles = array_merge(glob($dest . '/check_*.json'), glob($destination . '/check_*.json'), glob($biosDest . '/check_*.json'));
	$foundBios = false;
	if (!empty($checkFiles)) {
		foreach ($checkFiles as $del)
			@unlink($del);
	}

	if ($_SESSION['emulator_admin'] == 'ruffle' && file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/ruffle.js')) {
		$latestRuffle = ManageRuffleGetGitHubLatestDate(); //basename(ManageRuffleGetExternalLink());
		$arcadeModSettings['arcadeRuffleVersion'] = !empty($arcadeModSettings['arcadeRuffleVersion']) ? $arcadeModSettings['arcadeRuffleVersion'] : '';
		$context['arcade_emulator_detected'] = 1;
		$context['arcade_emulator_version'] = !empty($arcadeModSettings['arcadeRuffleVersion']) && $arcadeModSettings['arcadeRuffleVersion'] != '2010.01.01' ? sprintf($txt['arcade_emulator_current_file_date'], $arcadeModSettings['arcadeRuffleVersion']) : sprintf($txt['arcade_emulator_no_file_date'], $context['emulator_type_name']);
		if (file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/package.json')) {
			$contents = file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/ruffle/package.json');
			$versionObj = json_decode($contents);
			if (!empty($versionObj->{'version'})) {
				$context['arcade_emulator_version'] = $versionObj->{'version'};
				if (!empty($context['arcade_emulator_version']) && (empty($arcadeModSettings['arcadeRuffleVersion']) || $context['arcade_emulator_version'] != $arcadeModSettings['arcadeRuffleVersion'])) {
					$update['arcadeRuffleVersion'] = $context['arcade_emulator_version'];
					updateArcadeSettings($update, true);
					$arcadeModSettings['arcadeRuffleVersion'] = $context['arcade_emulator_version'];
				}
			}
		}
		//$v = preg_match_all('/\-{1}([^-]*)\-{1}/', $latestRuffle, $m);
		if (!empty($latestRuffle)) {
			//preg_match("/\-(.*)\-/", $latestRuffle, $m);
			//$context['arcade_emulator_remote_version'] = !empty($m) && !empty($m[1]) ? str_replace(array('nightly-', '-web'), '', $m[1]) : $msg[1];
			$context['arcade_emulator_remote_version'] = str_replace('_', '.', $latestRuffle);
		}
		else
			$context['arcade_emulator_remote_version'] = $msg[1];
	}
	elseif ($_SESSION['emulator_admin'] == 'emulatorjs' && (file_exists($boarddir . '/ArcadeRetroArch/data/emulator.js') || file_exists($boarddir . '/ArcadeRetroArch/data/src/emulator.js'))) {
		$arcadeModSettings['arcadeEmulatorJSVersion'] = !empty($arcadeModSettings['arcadeEmulatorJSVersion']) ? $arcadeModSettings['arcadeEmulatorJSVersion'] : '';
		$context['arcade_emulator_detected'] = 1;
		$context['arcade_emulator_version'] = !empty($arcadeModSettings['arcadeEmulatorJSVersion']) && $arcadeModSettings['arcadeEmulatorJSVersion'] != '2010.01.01' ? sprintf($txt['arcade_emulator_current_file_date'], $arcadeModSettings['arcadeEmulatorJSVersion']) : sprintf($txt['arcade_emulator_no_file_date'], $context['emulator_type_name']);
		if (file_exists($boarddir . '/ArcadeRetroArch/data/version.json')) {
			$contents = file_get_contents($boarddir . '/ArcadeRetroArch/data/version.json');
			$versionObj = json_decode($contents);
			if (!empty($versionObj->{'version'})) {
				$context['arcade_emulator_version'] = $versionObj->{'version'};
				if (!empty($context['arcade_emulator_version']) && (empty($arcadeModSettings['arcadeEmulatorJSVersion']) || $context['arcade_emulator_version'] != $arcadeModSettings['arcadeEmulatorJSVersion'])) {
					$update['arcadeEmulatorJSVersion'] = $context['arcade_emulator_version'];
					updateArcadeSettings($update, true);
					$arcadeModSettings['arcadeEmulatorJSVersion'] = $versionObj->{'version'};
				}
			}
		}
		if ($contents = arcade_maintenanceRemoteFileExists('https://cdn.emulatorjs.org/stable/data/version.json')) {
			$versionObj = json_decode($contents);
			if (!empty($versionObj->{'version'})) {
				$context['arcade_emulator_remote_version'] = $versionObj->{'version'};
			}
		}
		else {
			$context['arcade_emulator_remote_version'] = $msg[1];
		}
	}

	if ($_SESSION['emulator_admin'] == 'ruffle' && !empty($context['arcade_emulator_remote_version']) && !empty($context['arcade_emulator_version'])) {
		list($date1, $date2, $rufflePass) = array('', '', false);
		$date1 = explode('.', $context['arcade_emulator_remote_version']);
		$date2 = explode('.', $context['arcade_emulator_version']);
		if (count($date1) > 3 && count($date2) > 3) {
			if (strtotime($date1[3]) == strtotime($date2[3]) || strtotime($date2[3]) > strtotime($date1[3])) {
				$rufflePass = true;
			}
		}
	}
	if (!empty($context['arcade_emulator_remote_version']) && $context['arcade_emulator_remote_version'] != $msg[1] && $context['arcade_emulator_remote_version'] != $context['arcade_emulator_version'] && empty($rufflePass)) {
		$context['html_headers'] .= '
		<script>
			$(document).ready(function() {
				$("#emulatorfilemismatch").css("display", "block");
				$("#emulatorfilemismatch").css("font-size", "0.85rem");
				$("#emulatorfilemismatch").css("padding-bottom", "0.7rem");
				$("#emulatorfilemismatch").html("' . $txt['arcade_core_available'] . '");
				setInterval(function(){
					$("#emulatorfilemismatch").css("filter", "invert(1)");
					$("#emulatorfilemismatch").css("font-weight", "900");
					$("#emulatorfilemismatch").css("font-style", "italic");
				}, 3000);
				setInterval(function(){
					$("#emulatorfilemismatch").css("filter", "invert(0)");
					$("#emulatorfilemismatch").css("font-weight", "900");
					$("#emulatorfilemismatch").css("font-style", "normal");
				}, 6000);
			});
		</script>';

		$newRemotePath = 'https://github.com/EmulatorJS/EmulatorJS/archive/refs/tags/v' . $context['arcade_emulator_remote_version'] . '.zip';
		$newWebDevPath = 'https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/Arcade_EmulatorJS_v' . $context['arcade_emulator_remote_version'] . '.zip';
		if($newRemotePath != $arcadeModSettings['arcadeEmulatorJS_GithubLink']) {
			if ($handle = @fopen($newRemotePath, 'r')) {
				@fclose($handle);
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_modsettings
					SET value = {string:value}
					WHERE variable = {string:variable}',
					array(
						'value' => $newRemotePath,
						'variable' =>'arcadeEmulatorJS_GithubLink',
					)
				);
				$arcadeModSettings['arcadeEmulatorJS_GithubLink'] = $newRemotePath;
			}
		}
		if ($newWebDevPath != $arcadeModSettings['arcadeEmulatorJS_WebDevLink']) {
			if ($handle = @fopen($newWebDevPath, 'r')) {
				@fclose($handle);
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_modsettings
					SET value = {string:value}
					WHERE variable = {string:variable}',
					array(
						'value' => $newWebDevPath,
						'variable' =>'arcadeEmulatorJS_WebDevLink',
					)
				);
				$arcadeModSettings['arcadeEmulatorJS_WebDevLink'] = $newWebDevPath;
			}
		}
	}

	$destination = str_replace('\\', '/', $destination);
	require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$modSettings['cookieTime'] = 3153600;
	createToken('admin', 'post');
	$_SESSION['arcadRomTmp'] = 1;
	$showInstall = '';

	if (!is_dir($destination)) {
		arcadeClearSession();
		fatal_lang_error('arcade_generalized_file_error', false, array($destination));
	}
	if (!is_writable($destination) && !chmod($destination, 0755)) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($destination));
	}

	unset($_SESSION['emulatorjs_core_filename']);
	if (!isset($_REQUEST['mode']))
		unset($_SESSION['arcadeRomFileName']);
	elseif ($_REQUEST['mode'] == 'done') {
		if ($_SESSION['emulator_admin'] == 'emulatorjs')
			arcadeRmDir($boarddir . '/ArcadeRetroArch/updated_files');
		else
			arcadeRmDir($settings['default_theme_dir'] . '/arcade_scripts/ruffle/updated_files');
	}

	$_SESSION['emulatorjs_core_filename'] = !empty($_SESSION['emulatorjs_core_filename']) ? $_SESSION['emulatorjs_core_filename'] : '';
	$upload_directory = $boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin'];
	$upload_directory = str_replace('\\', '/', $upload_directory);
	$fileParts = array();
	$redirectTime = 5000;
	$navOptions = explode('|', $txt['arcade_core_download_options']);
	$strOptions = explode('|', $txt['arcade_core_download_remote']);
	$delOptions = explode('|', $txt['arcade_core_removal_opts']);
	$delOptionsText = explode('|', $txt['arcade_core_removal_opts_txt']);
	$ioOptionsText = explode('|', $txt['arcade_io_get_opts_txt']);
	$delNum = $_SESSION['emulator_admin'] == 'emulatorjs' ? 1 : 0;
	$files = glob(realpath($upload_directory) . '/*.{zip,gz,tar}', GLOB_BRACE);
	natsort($files);
	if (!empty($files) && empty($_SESSION['arcadeRomFileName'])) {
		$redirectTime = 1000;
		$_SESSION['arcadeRomFileName'] = basename($files[0]);
		$fileName = $_SESSION['arcadeRomFileName'];
		$tempName = pathinfo($_SESSION['arcadeRomFileName'], PATHINFO_FILENAME);
		$_SESSION['emulatorjs_core_filename'] = basename($files[0]);
		$showInstall = '
				emulatorJS_coreFound(false);';
	}

	if (!empty($_SESSION['emulatorjs_core_filename']) && file_exists($upload_directory . '/' . $_SESSION['emulatorjs_core_filename']) && $_SESSION['emulator_admin'] == 'emulatorjs' && pathinfo(basename($_SESSION['emulatorjs_core_filename']), PATHINFO_EXTENSION) == 'zip') {
		$pathData = glob($upload_directory . '/*');
		foreach ($pathData as $xfile) {
			if (pathinfo(basename($xfile), PATHINFO_EXTENSION) == 'zip') {
				$base = basename($xfile);
				$zipFile = dirname($xfile) . '/' . pathinfo($xfile, PATHINFO_FILENAME) . '.zip';
				$zipFile = str_replace('\\', '/', $zipFile);
				$zip = new ZipArchive();
				if ($zipresult = $zip->open($zipFile) !== FALSE) {
					for($i = 0; $i < $zip->numFiles; $i++){
						$index = $zip->statIndex($i);
						$extension = pathinfo(basename($index['name']), PATHINFO_EXTENSION);
						$biosCheck = ManageBiosFileNames($index['name'], true);
						if (!empty($index['name']) && !in_array($index['name'], array('index.php', '.', '..')) && is_int($biosCheck) && !empty($biosCheck) && empty($extension)) {
							$foundBios = true;
							break;
						}

					}
					if ($zipresult === TRUE) {
						$zip->close();
					}
				}
			}
		}
	}
	$context['html_headers'] .= '
		<link href="' . $settings['default_theme_url'] . '/css/arcade-prompt.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />
		<script src="' . $settings['default_theme_url'] . '/arcade_scripts/arcade-prompt.js"></script>
		<script>
			var arcadeButt2Interval, arcadeSpinTimer;
			function arch_spin(archFileTimeout, localFile = false) {
				var message = localFile == false ? "' . sprintf($txt['arcade_manage_retro_arch_get_archive'], $context['emulator_type_name']) . '" : "' . sprintf($txt['arcade_manage_retro_arch_get_archive_local'], $context['emulator_type_name']) . '";
				if (archFileTimeout > 0) {
					$("#arc_file_spinner").css("display", "block");
					setTimeout(function() {
						$("#arch_retrieval").html(message);
					}, archFileTimeout);
					arcadeSpinTimer = setTimeout(function() {
						if (document.getElementById("arc_file_spinner")) {
							document.getElementById("arc_file_spinner").style.display = "none";
							var checkFilterValue = $(".emulatorCoreBrowse").val();
							if (checkFilterValue) {
								$(".emulatorCoreBrowse").val("' . $txt['arcadeRomCoreBusy'] . '");
								$(".emulatorCoreBrowse").prop("disabled", true);
							}
							setTimeout(function() {
								var checkFilterValue = $(".emulatorCoreBrowse").val();
								if (checkFilterValue)
									$(".emulatorCoreBrowse").val("' . $txt['arcadeRomCoreBrowse'] . '");
									$(".emulatorCoreBrowse").prop("disabled", false);
							}, 15000);
							arcadeButt2Interval = setInterval(function(){
								var checkFilterVal = $(".emulatorCoreBrowse").css("filter");
								if (checkFilterVal && checkFilterVal == "invert(1)")
									$(".emulatorCoreBrowse").css("filter", "invert(0)");
								else
									$(".emulatorCoreBrowse").css("filter", "invert(1)");
							}, 2500);
						}
					}, archFileTimeout + 5000);
				}
			}
			function arch_message() {
				if (document.getElementById("arch_retrieval")) {
					document.getElementById("arch_retrieval").style.display = "block";
				}
			}
			function arch_external_form() {
				if (document.getElementById("arch_option"))
					document.getElementById("arch_option").style.display = "none";
				if (document.getElementById("upload_form"))
					document.getElementById("upload_form").style.display = "none";
				if (document.getElementById("external_form")) {
					document.getElementById("external_form").style.display = "none";
					$.confirm({
						boxWidth: "30%",
						useBootstrap: false,
						title: \'<div class="centertext" style="padding-bottom: 0.5rem;text-decoration: underline;">' . sprintf($txt['arcade_core_download'], $context['emulator_type_name']) . '</div>\',
						content: \'' . sprintf('<div class="centertext"><div>%s</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">%s</div><div>%s</div></div>', $strOptions[0], $strOptions[1], $strOptions[2]) . '\',
						buttons: {
							local: {
								text: "' . $navOptions[0] . '",
								btnClass: "btn-blue",
								action: function(){
									arch_spin(4000, true);
									var link = document.createElement("a");
									link.href = "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $arcadeModSettings['arcadeEmulatorJS_WebDevLink'] : $arcadeModSettings['arcadeRuffleDefaultLink']) . '";
									document.body.appendChild(link);
									link.click();
									setTimeout(function(){
										 link.remove();
									},3000);
								}
							},
							remote: {
								text: "' . $navOptions[1] . '",
								btnClass: "btn-blue",
								action: function(){
									arch_core_remote_file("webdev");
									arch_spin(8000, false);
								}
							},
							cancel: {
								text: "' . $navOptions[2] . '",
								action: function(){
									setTimeout(function(){
										 window.location.reload();
									},3000);
								}
							}
						}
					});
					document.getElementById("arch_retrieval").innerHTML = "' . sprintf($txt['arcade_arch_external'], $context['emulator_type_name']) . '";
					arch_message();
					return false;
				}
			}
			function arch_external_form_github() {
				var actionString = document.getElementById("external_form").action;
				if (document.getElementById("arch_option"))
					document.getElementById("arch_option").style.display = "none";
				if (document.getElementById("upload_form"))
					document.getElementById("upload_form").style.display = "none";
				if (document.getElementById("external_form")) {
					document.getElementById("external_form").style.display = "none";
					document.getElementById("external_form").action = actionString.slice(0, -1) + "2";
					$.confirm({
						boxWidth: "30%",
						useBootstrap: false,
						title: "' . sprintf($txt['arcade_core_download'], $context['emulator_type_name']) . '",
						content: \'' . sprintf('<div class="centertext"><div>%s</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">%s</div><div>%s</div></div>', $strOptions[0], $strOptions[1], $strOptions[2]) . '\',
						buttons: {
							local: {
								text: "' . $navOptions[0] . '",
								btnClass: "btn-blue",
								action: function(){
									arch_spin(4000, true);
									var link = document.createElement("a");
									link.href = "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $arcadeModSettings['arcadeEmulatorJS_GithubLink'] : $ruffleExternal) . '";
									document.body.appendChild(link);
									link.click();
									setTimeout(function(){
										 link.remove();
									},3000);
								}
							},
							remote: {
								text: "' . $navOptions[1] . '",
								btnClass: "btn-blue",
								action: function(){
									arch_core_remote_file("github");
									arch_spin(8000, false);
								}
							},
							cancel: {
								text: "' . $navOptions[2] . '",
								action: function(){
									setTimeout(function(){
										 window.location.reload();
									},3000);
								}
							}
						}
					});
					document.getElementById("arch_retrieval").innerHTML = "' . sprintf($txt['arcade_arch_external_github'], $context['emulator_type_name']) . '";
					arch_message();
					return false;
				}
			}
			function emulatorjs_io_get() {
				$("#ejsIoChip").css("display", "none");
				$.confirm({
					boxWidth: "30%",
					useBootstrap: false,
					title: \'<div style="padding-bottom: 1rem 0rem;text-decoration: underline;width: 100%;text-align: center;position: absolute;">' . sprintf($txt['arcade_emulator_io_title'], $context['emulator_type_name']) . '</div>\',
					content: \'' . sprintf('<div class="centertext"><div>%s</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">%s</div></div>', $ioOptionsText[0], $ioOptionsText[1]) . '\',
					buttons: {
						confirm: {
							text: "' . $delOptions[0] . '",
							btnClass: "btn-blue",
							action: function(){
								$("#arch_option").css("display", "none");
								$("#upload_form").css("display", "none");
								$("#external_form").css("display", "none");
								$(".emulatorCoreBrowse").val("' . $txt['arcadeRomCoreBusy'] . '");
								$(".emulatorCoreBrowse").prop("disabled", true);
								$("#arch_retrieval").css("display", "block");
								$("#arch_retrieval").html("' . $txt['arcade_io_get_transfer'] . '");
								arch_spin(4000, false);
								$(".jconfirm").css("display", "none");
								setTimeout(function(){emulator_io_get_files();}, 1000);
							}
						},
						cancel: {
							text: "' . $delOptions[1] . '",
							action: function(){
								setTimeout(function(){
									 window.location.reload();
								},3000);
							}
						}
					}
				});
			}
			function emulator_core_removal() {
				$("#arch_option").css("display", "none");
				$("#upload_form").css("display", "none");
				if (document.getElementById("external_form")) {
					$("#external_form").css("display", "none");
					$.confirm({
						boxWidth: "30%",
						useBootstrap: false,
						title: \'<div class="centertext" style="padding-bottom: 0.5rem;text-decoration: underline;">' . sprintf($txt['arcade_emulator_del_core_title'], $context['emulator_type_name']) . '</div>\',
						content: \'' . sprintf('<div class="centertext"><div>%s</div><div style="padding: 0.5rem 0rem 0.5rem 0rem;">%s</div></div>', $delOptionsText[$delNum], $delOptionsText[2]) . '\',
						buttons: {
							remove: {
								text: "' . $delOptions[0] . '",
								btnClass: "btn-blue",
								action: function(){
									emulator_core_remove_files();
									arch_spin(4000, false);
								}
							},
							cancel: {
								text: "' . $delOptions[1] . '",
								action: function(){
									setTimeout(function(){
										 window.location.reload();
									},3000);
								}
							}
						}
					});
					document.getElementById("arch_retrieval").innerHTML = "' . sprintf($txt['arcade_arch_external'], $context['emulator_type_name']) . '";
					arch_message();
					return false;
				}
			}
			function arch_upload_form() {
				if (document.getElementById("arch_option"))
					document.getElementById("arch_option").style.display = "none";
				if (document.getElementById("external_form"))
					document.getElementById("external_form").style.display = "none";
				if (document.getElementById("upload_form"))
					document.getElementById("upload_form").style.display = "none";
				arch_message();
			}
		</script>';

	// document.getElementById("external_form").submit();
	unset($_SESSION['arcadeUploadFiles']);
	$update = array('member_ip' => arcade_get_client_ip(), 'member_ip2' => $_SERVER['BAN_CHECK_IP'], 'passwd_flood' => '');
	$user_info['is_guest'] = false;
	$user_settings['additional_groups'] = explode(',', $user_settings['additional_groups']);
	$user_info['is_admin'] = $user_settings['id_group'] == 1 || in_array(1, $user_settings['additional_groups']);
	$update['last_login'] = time();
	updateMemberData($user_info['id'], $update);
	$bytes = openssl_random_pseudo_bytes(16);
	$_SESSION['arcade_rom_check'] = bin2hex($bytes);

	// css & js implementation
	$context['html_headers'] .= '
	<script type="text/javascript">
		function emulatorJS_done(redirectx = false, type = "core") {
			setTimeout(function() {
				if (arcadeButt2Interval) {
					clearInterval(arcadeButt2Interval);
					arcadeButt2Interval = false;
				}
				console.log("File upload complete! -> Attempting merge of temp " + type + " files...");
				$("#noromfiledetected").css("display", "none");
				$("#romfiledetected").css("display", "none");
				$("#arch_retrieval").css("display", "block");
				if (type != "core") {
					$("#arch_retrieval").html("' . $txt['arcade_io_get_transfer_done'] . '");
				}
				if (document.getElementById("time"))
					document.getElementById("time").style.display = "block";
				setTimeout(function() {
					window.location.reload();
				}, 3000);
			}, ' . strval($redirectTime) . ');
		}
		function emulatorJS_coreFound(redirectx = false) {
			setTimeout(function() {
				document.getElementById("noromfiledetected").style.display = "none";
				document.getElementById("romfiledetected").style.display = "block";
				if (redirectx == true) {
					arch_core_detected("' . $_SESSION['emulatorjs_core_filename'] . '");
				}
			}, ' . strval($redirectTime) . ');
		}
		function emulator_core_remove_files() {
			console.log("Removing core files for ' . $context['emulator_type_name'] . '...");
			var formx = $("<form/>", {action:"' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git2;emulator=' . $_SESSION['emulator_admin'] . ';", method:"POST"});
			formx.append($("<input>", {type:"hidden", name:"plugin", value:"' . ($_SESSION['emulator_admin'] == 'ruffle' ? 'ruffle' : 'emulatorjs') . '"}));
			formx.append($("<input>", {type:"hidden", name:"delete_' . ($_SESSION['emulator_admin'] == 'ruffle' ? 'ruffle' : 'emulatorjs') . '_core", value:"1"}));
			formx.append($("<input>", { type:"hidden", name:"fileid", value:"' . $_SESSION['arcade_rom_check'] . '"}));
			formx.append($("<input>", { type:"hidden", name:"' . $context['session_var'] . '", value:"' . $context['session_id'] . '"}));
			$("body").append(formx);
			formx.submit();
		}
		function arch_core_delete() {
			if (confirm("' . $txt['arcade_are_you_sure_rom_core_del'] . '") == true) {
				console.log("Deleting games_emulator_archives files...");
				var formx = $("<form/>", {action:"' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git2;emulator=' . $_SESSION['emulator_admin'] . ';", method:"POST"});
				formx.append($("<input>", {type:"hidden", name:"plugin", value:"emulatorjs"}));
				formx.append($("<input>", {type:"hidden", name:"delete", value:"1"}));
				formx.append($("<input>", { type:"hidden", name:"fileid", value:"' . $_SESSION['arcade_rom_check'] . '"}));
				formx.append($("<input>", { type:"hidden", name:"' . $context['session_var'] . '", value:"' . $context['session_id'] . '"}));
				$("body").append(formx);
				formx.submit();
			}
		}
		function arch_core_auto_delete(myCoreEvent) {
			if (confirm("' . sprintf($txt['arcade_are_you_sure_rom_core_del_auto'], $context['emulator_type_name']) . '") == true) {
				console.log("Deleting obsolete games_emulator_archives files...");
				$.ajax({
					type: "POST",
					url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git2;emulator=' . $_SESSION['emulator_admin'] . ';",
					data: {"plugin": "emulatorjs", "fileid": "' . $_SESSION['arcade_rom_check'] . '", "' . $context['session_var'] . '": "' . $context['session_id'] . '","delete": "1"},
					success: function(data){console.log(data);}
				});
				$("#emulatorjsfile").removeAttr("disabled");
				return false;
			}
			else {
				window.location.reload();
			}
		}
		function arch_allcores_remote_file() {
			var remoteCoreName = "EmulatorJS_Cores.zip";
			var websiteCore = "' . (!empty($arcadeModSettings['arcadeEmulatorJS_WebDevCoresLink']) ? $arcadeModSettings['arcadeEmulatorJS_WebDevCoresLink'] : 'https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/Arcade_EmulatorJS_Cores.zip') . '";
			console.log("Attempting to fetch EmulatorJS cores...");
			$.ajax({
				type: "POST",
				dataType : "html",
				contentType: "application/x-www-form-urlencoded; charset=UTF-8",
				data: {"git" : 2,"remote_file": websiteCore, "remote_name": remoteCoreName, "fileid": "' . $_SESSION['arcade_rom_check'] . '", "' . $context['session_var'] . '": "' . $context['session_id'] . '"},
				url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git3;emulator=' . $_SESSION['emulator_admin'] . ';",
				error: function(arguments){console.log("error", arguments);arch_core_remote_error();},
				success: function(data){console.log(data);}
			});
		}
		function emulator_io_get_files() {
			$("#arch_retrieval").css("display", "none");
			$("#arch_option").css("display", "block");
			$("#arch_option").css("font-size", "small");
			$("#arch_option").html("' . $txt['arcade_upload_arch_processing'] . '");
			$.ajax({
				type: "POST",
				dataType: "json",
				async: false,
				data: {"git" : 3,"fileid": "' . $_SESSION['arcade_rom_check'] . '", "' . $context['session_var'] . '": "' . $context['session_id'] . '"},
				url: "' . $scripturl . '?action=admin;sa=main;area=arcademaintenance;maintenance=retroLibraryGet;",
				error: function(arguments){console.log("error", arguments);arch_core_remote_error();},
				success: function(returnData){console.log(returnData);
					if (returnData && returnData.success && parseInt(returnData.success) == 1) {
						clearTimeout(arcadeSpinTimer);
						emulatorJS_done(false, "I/O");
					}
				}
			});
			setTimeout(function(){clearTimeout(arcadeSpinTimer);window.location.reload();}, 65000);
		}
		function arch_core_remote_file(site = "webdev") {
			var arcadeCoreInterval, arcadeButInterval;
			var website = site == "github" ? "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $arcadeModSettings['arcadeEmulatorJS_GithubLink'] : $ruffleExternal) . '" : "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $arcadeModSettings['arcadeEmulatorJS_WebDevLink'] : $arcadeModSettings['arcadeRuffleDefaultLink']). '";
			var remoteName = site == "github" && 1 == ' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? '0' : '1') . ' ? "' . (!empty($_SESSION['ruffle_version_update']) && stripos($_SESSION['ruffle_version_update'], 'https://github.com/ruffle-rs/ruffle/releases/download/') !== FALSE ? basename($_SESSION['ruffle_version_update']) : 'Ruffle_RemoteCore.zip') . '" : "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'EmulatorJS_RemoteCore.zip' : 'Ruffle_RemoteCore.zip') . '";
			$(".emulatorCoreBrowse").prop("disabled", true);
			arcadeButInterval = setInterval(function() {
				var checkFilterValue = $(".emulatorCoreBrowse").val();
				if (checkFilterValue && $(".emulatorCoreBrowse").val() != "' . $txt['arcadeRomCoreBusy'] . '")
					$(".emulatorCoreBrowse").val("' . $txt['arcadeRomCoreBusy'] . '");
					$(".emulatorCoreBrowse").prop("disabled", true);
			}, 1000);
			console.log("Attempting to fetch EmulatorJS main files...");
			let git = site == "github" && "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'git1' : 'git0') . '" == "git1" ? 2 : 1;
			$.ajax({
				type: "POST",
				dataType : "html",
				contentType: "application/x-www-form-urlencoded; charset=UTF-8",
				data: {"git" : git, "remote_file": website, "remote_name": remoteName, "fileid": "' . $_SESSION['arcade_rom_check'] . '", "' . $context['session_var'] . '": "' . $context['session_id'] . '"},
				url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git3;emulator=' . $_SESSION['emulator_admin'] . ';",
				error: function(arguments){
					console.log("error", arguments);
					arch_core_remote_error();
				},
				success: function(data){console.log(data);}
			});
			arch_message();
			if (git == 2) {
				arch_spin(65000, true);
				arch_allcores_remote_file();
				arcadeCoreInterval = setInterval(function(){emulator_core_check_files(git, site, "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'emulatorjs' : 'ruffle') . '");}, 9000);
				setTimeout(function(){clearInterval(arcadeButInterval);clearInterval(arcadeCoreInterval);$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 65000);
			}
			else {
				arcadeCoreInterval = setInterval(function(){emulator_core_check_files(1, site, "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'emulatorjs' : 'ruffle') . '");}, 9000);
				setTimeout(function(){clearInterval(arcadeButInterval);clearInterval(arcadeCoreInterval);$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 65000);
			}
			return false;
		}
		function emulator_core_check_files(git, site, app) {
			let gitCheck = git == 2 ? "2" : "";
			let fileName = "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $boardurl . '/games_emulator_archives/emulatorjs/check_" + gitCheck + "' . $_SESSION['arcade_rom_check'] . '.json' : $boardurl . '/games_emulator_archives/ruffle/check_" + gitCheck + "' . $_SESSION['arcade_rom_check'] . '.json') . '";
			$.ajax({
				url: fileName,
				dataType: "json",
				async: false,
				success: function(data) {
					if (data && data.id && data.id == "' . $_SESSION['arcade_rom_check'] . '") {
						setTimeout(function(){$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 3000);
					}
				},
				error: function(arguments){console.log("emulator archive not available yet");}
			});
		}
		function emulator_core_check_transfers(app) {
			let fileName = "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $boardurl . '/ArcadeRetroArch/check_' . $_SESSION['arcade_rom_check'] . '.json' : $settings['default_theme_url'] . '/arcade_scripts/ruffle/check_' . $_SESSION['arcade_rom_check'] . '.json') . '";
			let biosFileName = "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? $biosUrl . '/check_' . $_SESSION['arcade_rom_check'] . '.json' : '') . '";
			$.ajax({
				url: fileName,
				dataType: "json",
				async: false,
				success: function(data) {
					if (data && data.id && data.id == "' . $_SESSION['arcade_rom_check'] . '") {
						setTimeout(function(){$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 3000);
					}
				},
				error: function(arguments){console.log("emulator files not available yet");}
			});' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? '
			$.ajax({
				url: biosFileName,
				dataType: "json",
				async: false,
				success: function(data) {
					if (data && data.id && data.id == "' . $_SESSION['arcade_rom_check'] . '") {
						setTimeout(function(){$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 3000);
					}
				},
				error: function(arguments){console.log("emulator bios files not available yet");}
			});' : '') . '
		}
		function arch_core_remote_success() {
			setTimeout(function(){
				arch_message();
				window.location.reload();
			}, 5000);
		}
		function arch_core_remote_error() {
			if (document.getElementById("arch_retrieval"))
				document.getElementById("arch_retrieval").innerHTML = "' . $txt['arcade_upload_romcore_remote_err'] . '";
			if (document.getElementById("noromfiledetected"))
				document.getElementById("noromfiledetected").style.display = "none";
			if (document.getElementById("romfiledetected"))
				document.getElementById("romfiledetected").style.display = "none";
			if (document.getElementById("arch_retrieval"))
				document.getElementById("arch_retrieval").style.display = "block";
			if (document.getElementById("time"))
				document.getElementById("time").style.display = "none";
		}
		function arch_core_transfer_error() {
			if (document.getElementById("arch_retrieval"))
				document.getElementById("arch_retrieval").innerHTML = "' . $txt['arcade_upload_romcore_transfer_err'] . '";
			if (document.getElementById("noromfiledetected"))
				document.getElementById("noromfiledetected").style.display = "none";
			if (document.getElementById("romfiledetected"))
				document.getElementById("romfiledetected").style.display = "none";
			if (document.getElementById("arch_retrieval"))
				document.getElementById("arch_retrieval").style.display = "block";
			if (document.getElementById("time"))
				document.getElementById("time").style.display = "none";
		}
		function arch_core_detected(filenamex = "") {
			var arcadeCoreInterval, arcadeButInterval;
			console.log("file upload complete! File: " + filenamex + " -> Replacing ' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'EmulatorJS' : 'Ruffle') . ' files...");
			$.ajax({
				type: "POST",
				dataType : "html",
				contentType: "application/x-www-form-urlencoded; charset=UTF-8",
				data: {"upload": "1", "plugin": "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'emulatorjs' : 'ruffle') . '", "filename": filenamex, "fileid": "' . $_SESSION['arcade_rom_check'] . '", "' . $context['session_var'] . '": "' . $context['session_id'] . '"},
				url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git2;emulator=' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'emulatorjs' : 'ruffle') . ';",
				error: function(arguments){console.log("error", arguments);},
				success: function(data){
					emulator_core_check_transfers("' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'emulatorjs' : 'ruffle') . '");
					setTimeout(function(){$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 65000);
				}
			});
			$("#arch_retrieval").html("' . sprintf($txt['arcade_core_transfer_files'], ($_SESSION['emulator_admin'] == 'emulatorjs' ? $txt['arcade_rom_emulator'] : $txt['arcade_flash_emulator'])) . '");
			$("#arch_option").css("display", "none");
			$("#upload_form").css("display", "none");
			$("#external_form").css("display", "none");
			$(".emulatorCoreBrowse").prop("disabled", true);
			arch_spin(65000, true);
			arcadeButInterval = setInterval(function() {
				var checkFilterValue = $(".emulatorCoreBrowse").val();
				if (checkFilterValue && $(".emulatorCoreBrowse").val() != "' . $txt['arcadeRomCoreBusy'] . '")
					$(".emulatorCoreBrowse").val("' . $txt['arcadeRomCoreBusy'] . '");
					$(".emulatorCoreBrowse").prop("disabled", true);
			}, 1000);
			arch_message();
			arcadeCoreInterval = setInterval(function(){emulator_core_check_transfers("' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'emulatorjs' : 'ruffle') . '");}, 9000);
			setTimeout(function(){clearInterval(arcadeCoreInterval);clearInterval(arcadeButInterval);$("#emulatorjsfile").removeAttr("disabled");window.location.reload();}, 65000);
		}
		function emulator_core() {
			$("#emulatorjsdeltrigger").click(function(){
				$("#emulatorjsfile").attr("disabled", "disabled");
				arch_core_auto_delete();
				$("#emulatorjsfile").trigger("click");
				$("#emulatorjsfile").attr("disabled", "disabled");
			});
			if (document.getElementById("noromfiledetected"))
				document.getElementById("noromfiledetected").style.display = "block";' . (isset($_REQUEST['mode']) && is_string($_REQUEST['mode']) && $_REQUEST['mode'] == 'done' ? '
			if (document.getElementById("arch_retrieval")) {
				document.getElementById("arch_retrieval").style.display = "block";
				document.getElementById("arch_retrieval").style.padding = "2rem";
			}
			if (document.getElementById("time"))
				document.getElementById("time").innerHTML = "' . sprintf($txt['arcadeRomCoreUploadComplete'], $context['emulator_type_name']) . '";
			setTimeout(function() {
				document.getElementById("arch_retrieval").style.display = "none";
			}, 5000);' : '
			if (document.getElementById("arch_retrieval"))
				document.getElementById("arch_retrieval").style.display = "none";') . $showInstall . '
			return false;
		}
		function emulator_bios() {' . (!empty($foundBios) ? '
			$("#core_merge_install").val("' . $txt['arcade_bios_file_detected'] . '");
			$("#core_merge_delete").val("' . $txt['arcade_bios_delete'] . '");' : '') . '
			return false;
		}
		$(document).ready(function() {
			emulator_core();
			const emulatorCoreInputBgImg = $(".emulatorCoreInput").css("backgroundImage");
			$(".emulatorCoreInput").on( "click", function() {
				$.when(new Promise(res=>setTimeout(res,1000))).then(()=>{$(".emulatorCoreInput").css({"backgroundImage": emulatorCoreInputBgImg});});
				return true;
			});
			var emulatorCoreList = document.getElementById("emulatorCoreList");
			var uploader = new plupload.Uploader({
				runtimes: "html5",
				browse_button: "pick",
				url: "' . $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git3;emulator=' . $_SESSION['emulator_admin'] . ';",
				chunk_size: "10mb",
				init: {
					PostInit: () => emulatorCoreList.innerHTML = "<div class=\'emulatorCoreTextBoxesSmall\' style=\'padding-bottom: 1rem;\'>' . $txt['arcadeRomCoreBrowseReady'] . '</div>",
					FilesAdded: (up, files) => {
						plupload.each(files, file => {
							let spanid, row1 = document.createElement("div"), div1 = document.createElement("div"), div2 = document.createElement("div"), div3 = document.createElement("div"), span1 = document.createElement("span"), span2 = document.createElement("span");
							let checkName = file.name.endsWith(".php") || file.name.startsWith("index") ? "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'EmulatorJS' : 'Ruffle') . '_RemoteCore.zip" : (file.name.endsWith(".zip") && file.name.startsWith("main") ? "' . ($_SESSION['emulator_admin'] == 'emulatorjs' ? 'EmulatorJS' : 'Ruffle') . '_RemoteCore.zip" : file.name);
							let acceptedTypes = ["zip", "gz", "tar", "7z"];
							row1.className = "file-row";
							row1.id = file.id;
							span2.innerHTML = "%";
							span1.id = "divpercent" + file.id;
							span1.className = file.id;
							div1.innerHTML = checkName;
							div1.className = "file-name emulatorCoreTextBoxesSmall";
							div2.innerHTML = plupload.formatSize(file.size);
							div2.className = "file-size";
							var thisType = checkName.split(".").pop();
							if (acceptedTypes.includes(thisType)) {
								div2.appendChild(div3);
								div1.appendChild(div2);
								row1.appendChild(div1);
								emulatorCoreList.appendChild(row1);
								div3.appendChild(span1);
								div3.appendChild(span2);
							}
							else {
								files[file] = [];
							}
						});
						uploader.start();
					},
					UploadProgress: (up, file) => {
						let inputElem = document.getElementById("pick");
						inputElem.disabled = true;
						let el = document.querySelector("." + file.id);
						el.innerHTML = file.percent;
						if (parseInt(file.percent) >= 100) {
							emulatorJS_done(false, "core");
						}
					},
					Error: (up, err) => console.error(err)
				}
			});
			uploader.init();
			emulator_bios();
			$("#ejsIoChip").hover(function() {
				$(this).css("cursor", "pointer");
			});
			$("#ejsIoChip").on("click", function() {
				' . (!empty($arcadeModSettings['arcadeRetroLibraryCodeEnable']) ? 'emulatorjs_io_get();' : 'return false;') . '
			});
		});
	</script>
	<link href="' . $settings['default_theme_url'] . '/css/arcade-upload.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/plupload/3.1.5/plupload.full.min.js"></script>';

	$context['post_max_size'] = arcade_return_bytes(ini_get('post_max_size')) / 1048576;
	$context['post_max_size'] = preg_replace('/[^0-9\.]/', '', $context['post_max_size']);
	$context['post_max_size'] = round($context['post_max_size'], 2);

	// Template
	$context['sub_template'] = 'manage_maintenance_retro_arch_upload';
	$context[$context['admin_menu_name']]['tab_data']['title'] = sprintf($txt['arcade_retro_arch_update'], $context['emulator_type_name']);
	$context[$context['admin_menu_name']]['tab_data']['description'] = sprintf($txt['arcade_manage_retro_arch_desc'], $context['emulator_type_name']);
	$context['post_url'] = $scripturl . '?action=admin;area=arcademaintenance;sa=rom_git2;emulator=' . $_SESSION['emulator_admin'] . ';';
}

function ManageRetroArchCore2()
{
	global $txt, $scripturl, $boarddir, $arcadeModSettings, $modSettings, $settings, $context, $cookiename, $smfVersion;
	isAllowedTo('arcade_admin');
	checkSession('post');
	if (empty($_SESSION['emulator_admin'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_function_disabled', false);
	}
	$emulatorTypes = !empty($txt['emulator_types']) ? explode('|', $txt['emulator_types']) : array('EmulatorJS', 'Ruffle');
	$context['emulator_type_name'] = $_SESSION['emulator_admin'] == 'emulatorjs' ? $emulatorTypes[0] : $emulatorTypes[1];
	$check = isset($_POST['fileid']) && is_string($_POST['fileid']) ? $_POST['fileid'] : 'kickout';
	$plugin = isset($_POST['plugin']) && is_string($_POST['plugin']) && in_array(strval($_POST['plugin']), array('emulatorjs', 'ruffle')) ? $_POST['plugin'] : 'emulatorjs';
	$file = isset($_POST['filename']) && is_string($_POST['filename']) ? $_POST['filename'] : 'none';
	$newUpload = isset($_POST['upload']) && is_numeric($_POST['upload']) && intval($_POST['upload']) == 1 ? 1 : '';
	$deleteArc = isset($_POST['delete']) && is_numeric($_POST['delete']) && intval($_POST['delete']) == 1 ? 1 : '';
	$deleteArcCore = isset($_POST['delete_emulatorjs_core']) && is_numeric($_POST['delete_emulatorjs_core']) && intval($_POST['delete_emulatorjs_core']) == 1 ? 1 : '';
	$deleteRuffleCore = isset($_POST['delete_ruffle_core']) && is_numeric($_POST['delete_ruffle_core']) && intval($_POST['delete_ruffle_core']) == 1 ? 1 : '';
	$dirFiles = array();
	$dir = 'games_emulator_archives/' . $_SESSION['emulator_admin'];
	$upload_directory = $boarddir . '/' . $dir;
	$upload_directory = str_replace('\\', '/', $upload_directory);
	$checkSess = !empty($check) ? $check : 'mismatch';
	$destPath = $plugin == 'emulatorjs' ? str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch' : str_replace('\\', '/', $settings['default_theme_dir']) . '/arcade_scripts/ruffle';

	if (empty($_SESSION['arcade_rom_check']) || $check != $_SESSION['arcade_rom_check']) {
		arcadeClearSession();
		fatal_lang_error('arcade_function_disabled', false);
	}

	if ($deleteArc == 1) {
		clearstatcache();
		if (is_dir($upload_directory)) {
			$dir_iterator = new RecursiveDirectoryIterator($upload_directory);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $file) {
				if (mb_substr($file, -1) != '.' && mb_substr($file, -2) != '..' && basename($file) != 'index.php') {
					$dirFiles[] = $file;
				}
			}
			foreach ($dirFiles as $file) {
				if (is_dir($file))
					arcadeRmDir($file);
				elseif (file_exists($file) && basename($file) != 'index.php')
					@unlink($file);
			}
		}
		clearstatcache();
		redirectexit($scripturl . '?action=admin;area=arcademaintenance;sa=' . ($_SESSION['emulator_admin'] == 'ruffle' ? 'ruffle' : 'rom_git'));
	}

	if ($deleteArcCore == 1) {
		clearstatcache();
		if (is_dir($boarddir . '/ArcadeRetroArch')) {
			$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
			$boarddirx = str_replace('\\', '/', $boarddir);
			$skip = array($boarddirx . '/ArcadeRetroArch/.htaccess', $boarddirx . '/ArcadeRetroArch/index.php', $boarddirx . '/ArcadeRetroArch/open.svg', $boarddirx . '/ArcadeRetroArch/pagenotfound.html', $boarddirx . '/ArcadeRetroArch/data/index.php', $boarddirx . '/ArcadeRetroArch/data/loader.js');
			$dir_iterator = new RecursiveDirectoryIterator($boarddir . '/ArcadeRetroArch');
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
			$lengthx = strlen($romGamesDirectory);
			$lengthy = strlen($boarddirx . '/ArcadeRetroArch/roms');
			foreach ($iterator as $file) {
				$filex = str_replace('\\', '/', $file);
				if (mb_substr($file, -1) != '.' && mb_substr($file, -2) != '..' && !in_array($filex, $skip) && substr($filex, 0, $lengthx) != $romGamesDirectory && substr($filex, 0, $lengthy) != $boarddirx . '/ArcadeRetroArch/roms') {
					$dirFiles[] = $file;
				}
			}
			foreach ($dirFiles as $file) {
				$filex = str_replace('\\', '/', $file);
				if (is_dir($file) && $filex != $boarddirx . '/ArcadeRetroArch/data' && $filex != $boarddirx . '/ArcadeRetroArch/data/')
					arcadeRmDir($file);
				elseif (file_exists($file))
					@unlink($file);
			}
		}
		clearstatcache();
		redirectexit($scripturl . '?action=admin;area=arcademaintenance;sa=' . ($_SESSION['emulator_admin'] == 'ruffle' ? 'ruffle' : 'rom_git'));
	}

	if ($deleteRuffleCore == 1) {
		clearstatcache();
		if (is_dir($settings['default_theme_dir'] . '/arcade_scripts/ruffle')) {
			$dir_iterator = new RecursiveDirectoryIterator($settings['default_theme_dir'] . '/arcade_scripts/ruffle');
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
			$ruffleDir = str_replace('\\', '/', $settings['default_theme_dir'] . '/arcade_scripts/ruffle');
			$skip = array($ruffleDir . '/.htaccess', $ruffleDir . '/index.php');
			foreach ($iterator as $file) {
				$filex = str_replace('\\', '/', $file);
				if (mb_substr($file, -1) != '.' && mb_substr($file, -2) != '..' && !in_array($filex, $skip)) {
					$dirFiles[] = $file;
				}
			}
			foreach ($dirFiles as $file) {
				if (is_dir($file))
					arcadeRmDir($file);
				elseif (file_exists($file) && basename($file))
					@unlink($file);
			}
		}
		clearstatcache();
		redirectexit($scripturl . '?action=admin;area=arcademaintenance;sa=' . ($_SESSION['emulator_admin'] == 'ruffle' ? 'ruffle' : 'rom_git'));
	}

	$fileName = $_SESSION['arcadeRomFileName'];
	$tempName = pathinfo($_SESSION['arcadeRomFileName'], PATHINFO_FILENAME);
	$file = $fileName;

	$newfile = array('emulatorjs' => 'ArcadeRetroArch.zip', 'ruffle' => 'Ruffle.zip');
	$filename = str_replace('\\', '/', $boarddir) . '/games_emulator_archives/' . $_SESSION['emulator_admin'] . '/' .  $file;
	clearstatcache();

	$dir_iterator = new RecursiveDirectoryIterator(dirname($filename));
	$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($iterator as $file)
		if (mb_substr($file, -1) != '.' && mb_substr($file, -2) != '..' && basename($file) != 'index.php' && basename($file) != 'EmulatorJS_Cores.zip' && basename($file) != '.htaccess')
			$dirFiles[] = $file;

	if (empty($dirFiles) || !file_exists($dirFiles[0])) {
		arcadeClearSession();
		fatal_lang_error('arcade_function_disabled', false);
	}
	$filename = $dirFiles[0];
	$modSettings['cookieTime'] = 3153600;
	createToken('admin', 'post');

	$_SESSION['arcade_arch_update'] = 1;
	$_SESSION['arcade_arch_update_file'] = basename($filename);
	$override = ';overwrite=1'; //isset($_POST['override']) ? ';overwrite=1' : '';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_rom_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = sprintf($txt['arcade_manage_retro_arch_desc'], $context['emulator_type_name']);
	ManageArchReplace($filename);
	//file_put_contents($destPath . '/check_' .  $checkSess . '.json', json_encode(["ok" => "1", "info" => "check", "id" => $checkSess]));
	//@chmod($destPath . '/check_' .  $checkSess . '.json', 0644);
}

function ManageRetroArchCore3()
{
	global $txt, $arcadeModSettings, $modSettings, $smcFunc, $context, $cookiename, $smfVersion, $boarddir;

	isAllowedTo('arcade_admin');
	//checkSession('post');
	$modSettings['cookieTime'] = 3153600;
	createToken('admin', 'post');
	$remoteName = isset($_POST['remote_name']) ? $_POST['remote_name'] : '';
	$check = isset($_POST['fileid']) && is_string($_POST['fileid']) ? $_POST['fileid'] : 'kickout';
	$tempFileName = isset($_POST['remote_file']) && !empty($_SESSION['arcade_rom_check']) && $check == $_SESSION['arcade_rom_check'] ? $_POST['remote_file'] : $_FILES["file"]["tmp_name"];
	$checkSess = !empty($_SESSION['arcade_rom_check']) ? $_SESSION['arcade_rom_check'] : 'mismatch';
	if (stripos($tempFileName, 'https://github.com/ruffle-rs/ruffle/releases/download/') === FALSE && !empty($_SESSION['ruffle_version_update']))
		unset($_SESSION['ruffle_version_update']);
	if (empty($remoteName)) {
		if (empty($_FILES) || $_FILES["file"]["error"]) {
			verboseRomCore(0, 0);
		}
	}

	$remoteCoreName = isset($_POST['remoteCoreName']) && is_string($_POST['remoteCoreName']) ? $_POST['remoteCoreName'] : '';
	$filePath = realpath($boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin']);
	$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : (!empty($remoteName) ? $remoteName : $_FILES["file"]["name"]);
	$fileName = stripos($fileName, '.') === FALSE || substr($fileName, -4) == '.php' ? 'EmulatorJS_RemoteCore.zip' : (substr($fileName, 0, 4) == 'main' && substr($fileName, -4) == '.zip' ? 'EmulatorJS_RemoteCore.zip' : $fileName);
	$fileName == 'EmulatorJS_RemoteCore.zip' && $_SESSION['emulator_admin'] == 'ruffle' ? 'Ruffle_RemoteCore.zip' : $fileName;
	$filePath = $filePath . '/' . $fileName;
	$git = isset($_POST['git']) && is_numeric($_POST['git']) && (int)$_POST['git'] == 2 ? 2 : 1;
	$dat = '.dat';
	$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
	$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
	$out = @fopen($filePath . $dat, ($chunk == 0 ? 'wb' : 'ab'));
	if ($out) {
		$in = @fopen($tempFileName, "rb");
		if ($in) {
			while ($buff = fread($in, 4096)) {
				fwrite($out, $buff);
			}
		} else {
			verboseRomCore(0, 1);
		}
		@fclose($in);
		@fclose($out);
		@unlink($_FILES["file"]["tmp_name"]);
	}
	else {
		verboseRomCore(0, 3);
	}

	if (empty($chunks) || $chunk == $chunks - 1) {
		if (!empty($dat))
			rename($filePath . $dat, $filePath);
		clearstatcache(true, $filePath);
		$checkFiles = glob($boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin'] . '/*.{zip,tar,gz,7z}', GLOB_BRACE);
		natsort($checkFiles);
		if (!empty($checkFiles)) {
			$_SESSION['arcadeRomFileName'] = basename($checkFiles[0]);
			$count = count($checkFiles);
			if ($git == $count && $_SESSION['emulator_admin'] == 'emulatorjs') {
				$file = $boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin'] . '/check_2' . $checkSess . '.json';
				file_put_contents($file, json_encode(["ok" => "1", "info" => "check", "id" => $checkSess]));
				@chmod($file, 0644);
			}
			elseif (($git == 1 && $_SESSION['emulator_admin'] == 'emulatorjs') || $_SESSION['emulator_admin'] == 'ruffle') {
				$file = $boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin'] . '/check_' . $checkSess . '.json';
				file_put_contents($file, json_encode(["ok" => "1", "info" => "check", "id" => $checkSess]));
				@chmod($file, 0644);
			}
		}
		elseif (!empty($_SESSION['arcadeRomFileName']))
			unset($_SESSION['arcadeRomFileName']);
	}

	verboseRomCore(1, 10);
	redirectexit('action=admin;area=manageromgames;sa=rom_git');
}

function ManageArchFiles($from, $overwrite = false)
{
	global $settings, $smfVersion, $arcadeModSettings, $boarddir, $txt, $sourcedir;
	isAllowedTo('arcade_admin');
	ini_set('upload_max_filesize', '50M');
	ini_set('post_max_size', '55M');
	$sourcePath = dirname($from);
	$archive = basename($from);
	$emulatorJSDirectory = str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch';
	$destPath = $_SESSION['emulator_admin'] == 'emulatorjs' ? str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch' : str_replace('\\', '/', $settings['default_theme_dir']) . '/arcade_scripts/ruffle';
	$target = 'updated_files';
	$from = str_replace('\\', '/', $from);
	$path = $destPath . '/updated_files';
	$gitPath = $_SESSION['emulator_admin'] == 'emulatorjs' ? str_replace('\\', '/', $path) . '/ArcadeRetroArch-main' : str_replace('\\', '/', $path) . '/Ruffle-main';
	list($extractZip, $extractCoresZip, $attemptCore, $skipzip, $dirFiles, $checkFiles, $tempPath) = array(false, false, false, false, array(), array(), strval(time()));
	clearstatcache(false, $path);
	$ext = strtolower(pathinfo($archive, PATHINFO_EXTENSION));
	$emulatorTypes = explode('|', $txt['emulator_types']);
	$emulator_type_name = $_SESSION['emulator_admin'] == 'emulatorjs' ? $emulatorTypes[0] : $emulatorTypes[1];
	$checkSess = !empty($_SESSION['arcade_rom_check']) ? $_SESSION['arcade_rom_check'] : 'mismatch';
	$bios = $_SESSION['emulator_admin'] == 'emulatorjs' ? MoveBiosFiles() : false;
	if (!empty($bios)) {
		$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] : $boarddir . '/ArcadeRetroArch/roms';
		$romGamesDirectory = str_replace('\\', '/', $romGamesDirectory);
		$biosDest = $romGamesDirectory . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar'];
		file_put_contents($biosDest . '/check_' .  $checkSess . '.json', json_encode(["ok" => "1", "info" => "check", "id" => $checkSess]));
		@chmod($biosDest . '/check_' .  $checkSess . '.json', 0644);
	}
	else {
		if (!is_dir($path)) {
			@mkdir($path, 0755, true);
			@chmod($path, 0755);
			@copy($boarddir . '/ArcadeSources/index.php', $path . '/index.php');
			@chmod($path . '/index.php', 0644);
			@copy($boarddir . '/games_upload_archives/.htaccess', $path . '/.htaccess');
			@chmod($path . '/.htaccess', 0644);
		}
		if (!file_exists($path . '/index.php')) {
			@copy($boarddir . '/ArcadeSources/index.php', $path . '/index.php');
			@chmod($path . '/index.php', 0644);
		}
		if (!file_exists($path . '/.htaccess')) {
			@copy($boarddir . '/games_upload_archives/.htaccess', $path . '/.htaccess');
			@chmod($path . '/.htaccess', 0644);
		}
		if ($ext == 'gz')
		{
			$out_file_name = pathinfo($from, PATHINFO_FILENAME);
			$error = '';
			try {
				$phar = new PharData($from);
				$phar->decompress();
			}
			catch (Exception $e) {
				$error = sprintf($txt['arcade_arch_install_error'], $archive);
				$err = arcade_html_entity_decode($error, 2, 2);
				if (!empty($arcadeModSettings['arcade_log_emulator_core'])) {
					log_error($err . ' [20]', 'debug');
				}
			}
			if (file_exists($sourcePath . '/' . $out_file_name))
			{
				if (file_exists($from))
					@unlink($from);
				$from = $sourcePath . '/' . $out_file_name;
				$archive = $out_file_name;
			}
			elseif (!empty($error)) {
				arcadeClearSession();
				fatal_lang_error('arcade_file_non_read', false);
			}
		}

		if ($ext == 'tar')
		{
			$out_file_name = pathinfo($from, PATHINFO_FILENAME);
			$error = '';
			try {
				$phar = new PharData($from);
				$phar->extractTo($path . '/');
			}
			catch (Exception $e) {
				$error = sprintf($txt['arcade_arch_install_error'], $archive);
				$err = arcade_html_entity_decode($error, 2, 2);
				if (!empty($arcadeModSettings['arcade_log_emulator_core'])) {
					log_error($err . ' [21]', 'debug');
				}
			}
			if (empty($error)) {
				$skipzip = true;
			}
			elseif (!empty($error)) {
				arcadeClearSession();
				fatal_lang_error('arcade_file_non_read', false);
			}
		}
		//arcade_gzcompressfile($from);
		if (mb_substr(mb_strtolower($from), -4) != '.zip') {
			clearstatcache(true, $from);
			$checkFiles = glob($boarddir . '/games_emulator_archives/' . $_SESSION['emulator_admin'] . '/*.{zip}', GLOB_BRACE);
			natsort($checkFiles);
			foreach ($checkFiles as $file) {
				if (substr($file, -4) == '.zip') {
					$from = str_replace('\\', '/', $file);
					$archive = basename($file);
					break;
				}
			}
			// log_error(print_r($checkFiles, true), 'debug');
		}

		if (mb_substr(mb_strtolower($archive) , -4) == '.zip' && file_exists($from) && empty($skipzip)) {
			chmod($from, 0644);
			clearstatcache();
			$coreFile = dirname($from) . '/EmulatorJS_Cores.zip';
			$mainEJSFile = dirname($from) . '/EmulatorJS_RemoteCore.zip';
			$mainRuffleFile = dirname($from) . '/Ruffle_RemoteCore.zip';
			$zip = new ZipArchive;
			if ($zip->open($from) === true) {
				$zip->extractTo($path . '/');
				$zip->close();
				$extractZip = true;
			}
			else {
				$extractZip = arcadeUnzip($from, $path . '/', true, true);
				$extractZip = !is_dir($path) ? false : true;
			}
			clearstatcache();
			if (basename($destPath) == 'ruffle' && file_exists($path . '/ruffle.js')) {
				copyArcadeDirectory($path, $destPath);
			}
			if (file_exists($coreFile) && !is_dir($path . '/data/cores')) {
				$attemptCore = true;
				$zip2 = new ZipArchive;
				if ($zip2->open($coreFile) === true) {
					$zip2->extractTo($emulatorJSDirectory . '/data');
					$zip2->close();
					$extractCoresZip = true;
					if (is_dir($emulatorJSDirectory . '/data/cores')) {
						chmod($emulatorJSDirectory . '/data/cores', 0755);
					}
				}
				@unlink($coreFile);
				clearstatcache();
			}
			if (file_exists($mainEJSFile) && $_SESSION['emulator_admin'] == 'emulatorjs') {
				arcadeUnzip($mainEJSFile, $path . '/', true, true);
				clearstatcache();
				$find = glob($path . "{,*/,*/*/,*/*/*/}version.json", GLOB_BRACE);
				foreach ($find as $found) {
					if (basename($found) == 'version.json') {
						unlink($mainEJSFile);
						break;
					}
				}
			}
			elseif (file_exists($mainRuffleFile)) {
				arcadeUnzip($mainRuffleFile, $path . '/', true, true);
				clearstatcache();
				$find = glob($path . "/*.{json}", GLOB_BRACE);
				foreach ($find as $found) {
					if (basename($found) == 'package.json') {
						unlink($mainRuffleFile);
						file_put_contents($destPath . '/check_' .  $checkSess . '.json', json_encode(["ok" => "1", "info" => "check", "id" => $checkSess]));
						@chmod($destPath . '/check_' .  $checkSess . '.json', 0644);
						break;
					}
				}
			}

			if (empty($extractZip)) {
				if (!empty($arcadeModSettings['arcade_log_emulator_core'])) {
					$error = sprintf($txt['arcade_emulator_main_decompress_error'], $emulator_type_name, print_r($zip, true));
					log_error($error . ' [22]', 'debug');
				}
				@unlink($from);
			}
			if (!empty($attemptCore) && empty($extractCoresZip) && !empty($zip2) && !empty($arcadeModSettings['arcade_log_emulator_core'])) {
				$error = sprintf($txt['arcade_emulator_cores_decompress_error'], $emulator_type_name, print_r($zip2, true));
				log_error($error . ' [23]', 'debug');
			}
			// $files = arcadeUnzip($from, $path . '/', true, false);
		}
		sleep(1);

		clearstatcache(false, dirname($path));

		// we can go deep into the rabbit hole...
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item) {
			$path = str_replace('\\', '/', $item->getPathname());
			if ($_SESSION['emulator_admin'] == 'emulatorjs' && $item->isFile() && mb_substr($path, -17) == '/data/emulator.js') {
				$gitPath = dirname(dirname($path));
				$gitPath = str_replace('\\', '/', $gitPath);
				$gitPath = rtrim($gitPath, '/');
				break;
			}
			elseif ($_SESSION['emulator_admin'] == 'emulatorjs' && $item->isFile() && mb_substr($path, -21) == '/data/src/emulator.js') {
				$gitPath = dirname(dirname(dirname($path)));
				$gitPath = str_replace('\\', '/', $gitPath);
				$gitPath = rtrim($gitPath, '/');
				break;
			}
			elseif ($item->isFile() && mb_substr($path, -10) == '/ruffle.js') {
				$gitPath = dirname($path);
				$gitPath = str_replace('\\', '/', $gitPath);
				$gitPath = rtrim($gitPath, '/');
				break;
			}
		}

		clearstatcache(false, $gitPath);
		if ($_SESSION['emulator_admin'] == 'ruffle' && !file_exists($gitPath . '/ruffle.js')) {
			list($check1, $check2) = array(str_replace('\\', '/', $boarddir), str_replace('\\', '/', $path));
			if ($check1 != $check2) {
				arcadeRmdir($path);
			}
			if (!empty($arcadeModSettings['arcade_log_emulator_core']))
				log_error($txt['arcade_upload_rufflecore_file'] . ' [ruffle]', 'debug');
			arcadeClearSession();
			fatal_lang_error('arcade_upload_rufflecore_file', false);
		}
		elseif ($_SESSION['emulator_admin'] == 'emulatorjs') {
			//$gitPath = substr($gitPath, -5) == '/data' ? substr($gitPath, 0, -5) : $gitPath;
			if (!is_dir($gitPath . '/data') || (!file_exists($gitPath . '/data/emulator.js') && !file_exists($gitPath . '/data/src/emulator.js'))) {
				list($check1, $check2) = array(str_replace('\\', '/', $boarddir), str_replace('\\', '/', $path));
				if ($check1 != $check2 && strpos($check2, '/updated_files/') !== FALSE) {
					arcadeRmdir($path);
				}
				if (!empty($arcadeModSettings['arcade_log_emulator_core']))
					log_error($txt['arcade_upload_rufflecore_file'] . ' [emulatorjs]', 'debug');
				arcadeClearSession();
				fatal_lang_error('arcade_upload_romcore_file', false);
			}
		}

		ini_set('memory_limit', '256M');
		ini_set('max_execution_time', '0');
		@set_time_limit(0);

		$dir_iterator = new RecursiveDirectoryIterator($gitPath);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
		$sourceDirFiles = array();
		foreach ($iterator as $file) {
			if (in_array(basename($file), array('.', '..')))
				continue;

			$xfile = basename($file);
			if (mb_substr($xfile, 0, 4) == '.git')
				continue;
			if (mb_substr($file, -1) != '.' && mb_substr($file, -2) != '..') {
				$sourceDirFiles[] = str_replace('\\', '/', $file);
			}
		}

		if (!empty($sourceDirFiles) && count($sourceDirFiles) > 2) {
			clearstatcache($gitPath);
			// RC or Beta versions may have new main index.php or .htaccess files so ensure they aren't replaced
			if (stripos($arcadeModSettings['arcadeVersion'], 'rc') !== FALSE || stripos($arcadeModSettings['arcadeVersion'], 'beta') !== FALSE) {
				list($check1, $check2) = array(str_replace('\\', '/', $boarddir), str_replace('\\', '/', $gitPath));
				if ($check1 != $check2) {
					foreach (array($gitPath . '/index.php', $gitPath . '/.htaccess', $gitPath . '/roms/.htaccess') as $delFile) {
						if (file_exists($delFile))
							@unlink($delFile);
					}
				}
			}

			arcade_emulator_archive_copy($gitPath, $destPath);
			//copyArcadeDirectory($gitPath, $destPath);

			$files = glob($sourcePath . '/.*');
			foreach($files as $file){
				if (in_array(basename($file), array('.', '..'))) {
					continue;
				}
				$xfile = basename($file);
				if(is_file($file) && mb_substr($xfile, 0, 4) == '.git')
					unlink($file);
			}
			clearstatcache();
			arcadeRmdir($gitPath);

			if (file_exists($from))
				unlink($from);

			clearstatcache();

			if (!empty($_SESSION['ruffle_version_update']) && $_SESSION['emulator_admin'] == 'ruffle' && file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/ruffle.js')) {
				$arcadeModSettings['arcadeRuffleVersion'] = !empty($arcadeModSettings['arcadeRuffleVersion']) ? $arcadeModSettings['arcadeRuffleVersion'] : '';
				if (file_exists($settings['default_theme_dir'] . '/arcade_scripts/ruffle/package.json')) {
					$contents = file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/ruffle/package.json');
					$versionObj = json_decode($contents);
					if (!empty($versionObj->{'version'})) {
						if (empty($arcadeModSettings['arcadeRuffleVersion']) || $versionObj->{'version'} != $arcadeModSettings['arcadeRuffleVersion']) {
							$update['arcadeRuffleVersion'] = $versionObj->{'version'};
							updateArcadeSettings($update, true);
							$arcadeModSettings['arcadeRuffleVersion'] = $versionObj->{'version'};
						}
					}
				}
			}
			elseif ($_SESSION['emulator_admin'] == 'emulatorjs' && (file_exists($boarddir . '/ArcadeRetroArch/data/emulator.js') || file_exists($boarddir . '/ArcadeRetroArch/data/src/emulator.js'))) {
				$arcadeModSettings['arcadeEmulatorJSVersion'] = !empty($arcadeModSettings['arcadeEmulatorJSVersion']) ? $arcadeModSettings['arcadeEmulatorJSVersion'] : '';
				if (file_exists($boarddir . '/ArcadeRetroArch/data/version.json')) {
					$contents = file_get_contents($boarddir . '/ArcadeRetroArch/data/version.json');
					$versionObj = json_decode($contents);
					if (!empty($versionObj->{'version'})) {
						if (empty($arcadeModSettings['arcadeEmulatorJSVersion']) || $versionObj->{'version'} != $arcadeModSettings['arcadeEmulatorJSVersion']) {
							$update['arcadeEmulatorJSVersion'] = $versionObj->{'version'};
							updateArcadeSettings($update, true);
							$arcadeModSettings['arcadeEmulatorJSVersion'] = $versionObj->{'version'};
						}
					}
				}
			}
			file_put_contents($destPath . '/check_' .  $checkSess . '.json', json_encode(["ok" => "1", "info" => "check", "id" => $checkSess]));
			@chmod($destPath . '/check_' .  $checkSess . '.json', 0644);
		}
	}
}

function arcade_emulator_archive_copy($source, $dest, $ignoreFiles = array())
{
	global $boarddir;
	// this does not retain the original files but is faster
	if (is_dir($source)) {
		if ($dir = @opendir($source)) {

			if (!is_dir($dest)) {
				@mkdir($dest, 0755);
			}

			while($file = readdir($dir)) {

				if (!in_array($file, array('.', '..')) && !in_array($source . '/' . $file, $ignoreFiles)) {
					$checkArcadeFile[] = $source . '/' . $file;
					if (is_dir($source . '/' . $file)) {
						arcade_emulator_archive_copy($source . '/' . $file, $dest . '/' . $file, $ignoreFiles);
					}
					else {
						if (str_replace('\\', '/', $dest) . '/' . $file != str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/index.php' && strpos(str_replace('\\', '/', $dest), str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/') !== FALSE)
							@rename($source . '/' . $file, $dest . '/' . $file);
					}
				}
			}
			closedir($dir);
		}
	}
	elseif (file_exists($source) && !file_exists($dest) && !in_array(basename($source), array('.', '..')) && !in_array($source, $ignoreFiles)) {
		@rename($source, $dest);
	}
	else
		return false;

	return true;
}

function EditRomGame()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $modSettings, $settings, $boardurl, $context, $boarddir, $smcFunc, $sourcedir, $smfVersion;

	$context['game_permissions'] = $arcadeModSettings['arcadePermissionMode'] > 2;
	$context['edit_page'] = 'basic';
	$arcadeModSettings['arcadeJsInsertDefault'] = !empty($arcadeModSettings['arcadeJsInsertDefault']) && in_array($arcadeModSettings['arcadeJsInsertDefault'], array(0, 1, 2)) ? $arcadeModSettings['arcadeJsInsertDefault'] : 2;
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$romGamesUrl = !empty($arcadeModSettings['romGamesUrl']) ? $arcadeModSettings['romGamesUrl'] : $boardurl . '/ArcadeRetroArch/roms';

	isAllowedTo('arcade_admin');
	$modSettings['cookieTime'] = 3153600;
	createToken('admin', 'post');
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');

	// Load game data unless it has been loaded by EditGame2
	if (!isset($context['game']))
	{
		if (!isset($_REQUEST['game'])) {
			arcadeClearSession();
			fatal_lang_error('arcade_no_games_selected', false);
		}
		$id = loadRomGame((int) $_REQUEST['game'], true);

		if ($id === false) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		$dbgame = &$context['arcade']['game_data'][$id];
		list($conflictId, $dbgameId, $primaryConflictGame, $subsequentData, $primaryGameName, $subsequentDataLink, $subsequentConflictGames) = array(0, 0, '', '', '', '', array());
		$hide = !empty($dbgame['icon_position_hide']) ? (int)$dbgame['icon_position_hide'] : 0;
		$arcadeModSettings['arcadeViewCovers'] = !empty($arcadeModSettings['arcadeViewCovers']) ? intval($arcadeModSettings['arcadeViewCovers']) : 0;
		$context['coverIconEnabled'] = $arcadeModSettings['arcadeViewCovers'] == 1 || $arcadeModSettings['arcadeViewCovers'] == 3 ? true : false;
		$coverfile = $dbgame['submit_system'] == 'rom' ? 'romgamecover.gif' : 'arcadegamecover.png';
		$imgFile1 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail'] : $dbgame['thumbnail'];
		$imgFile2 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail_small'] : $dbgame['thumbnail_small'];
		$imgFile3 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['cover_icon'] : $dbgame['cover_icon'];
		$imgPath1 = !empty($dbgame['thumbnail']) && file_exists($romGamesDirectory . '/' . $imgFile1) ? $romGamesUrl . '/' . $imgFile1 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath2 = !empty($dbgame['thumbnail_small']) && file_exists($romGamesDirectory . '/' . $imgFile2) ? $romGamesUrl . '/' . $imgFile2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath3 = !empty($dbgame['cover_icon']) && file_exists($romGamesDirectory . '/' . $imgFile3) ? $romGamesUrl . '/' . $imgFile3 : $settings['default_theme_url'] . '/images/arc_icons/' . $coverfile;
		$extra = !empty($dbgame['extra_data']) && arcadeIsSerialized($dbgame['extra_data']) ? arcade_safe_unserialize($dbgame['extra_data']) : array();
		$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
		$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
		$context['game'] = array(
			'id' => $dbgame['id_game'],
			'internal_name' => $dbgame['internal_name'],
			'category' => $dbgame['id_cat'],
			'name' => htmlspecialchars(str_replace("&apos;", "'", $dbgame['game_name']), ENT_QUOTES | ENT_HTML5, 'UTF-8', false),
			'thumbnail' => htmlspecialchars($dbgame['thumbnail']),
			'thumbnail_small' => htmlspecialchars($dbgame['thumbnail_small']),
			'thumbnail_prev' => $imgPath1 . '?' . $suffixVersion,
			'thumbnail_small_prev' => $imgPath2. '?' . $suffixVersion,
			'thumb1' => stripos($imgPath1, $settings['default_theme_url'] . '/images/arc_icons/Default.gif') !== FALSE ? '<li style="font-size: 8pt;">[thumbnail] ' . $txt['arcade_warning_no_gameimage'] . '</li>' : '',
			'thumb2' => stripos($imgPath2, $settings['default_theme_url'] . '/images/arc_icons/Default.gif') !== FALSE ? '<li style="font-size: 8pt;">[thumbnail_small] ' . $txt['arcade_warning_no_gameimage'] . '</li>' : '',
			'cover_icon' => htmlspecialchars($dbgame['cover_icon']),
			'cover_icon_prev' => $imgPath3 . '?' . $suffixVersion,
			'covericon' => stripos($imgPath3, $settings['default_theme_url'] . '/images/arc_icons/Default.gif') !== FALSE ? '<li style="font-size: 8pt;">[cover_icon] ' . $txt['arcade_warning_no_gameimage'] . '</li>' : '',
			'description' => htmlspecialchars(str_replace("&apos;", "'", $dbgame['description']), ENT_QUOTES | ENT_HTML5, 'UTF-8', false),
			'help' => htmlspecialchars(str_replace("&apos;", "'", $dbgame['help']), ENT_QUOTES | ENT_HTML5, 'UTF-8', false),
			'game_file' => (string)$dbgame['game_file'],
			'game_directory' => $dbgame['game_directory'],
			'rom_system' => $dbgame['rom_system'],
			'submit_system' => 'rom',
			'score_type' => $dbgame['score_type'],
			'js_insertion' => !empty($dbgame['js_insertion']) ? $dbgame['js_insertion'] : $arcadeModSettings['arcadeJsInsertDefault'],
			'member_groups' => explode(',', $dbgame['member_groups']),
			'extra_data' => $extra,
			'enabled' => !empty($dbgame['enabled']),
			'download' => !empty($dbgame['download']),
			'icon_position' => !empty($dbgame['icon_position']) ? $dbgame['icon_position'] : 0,
			'icon_position_hide' => abs(intval($hide)),
			'primaryConflictGame' => $primaryConflictGame,
			'subsequentData' => $subsequentDataLink,
		);

		if (empty($context['game']['extra_data']))
			$context['game']['extra_data'] = array(
			'width' => '',
			'height' => '',
			'flash_version' => '',
			'background_color' => array('', '', ''),
			'type' => '',
			'remote_link' => '',
		);
		$context['game']['extra_data']['type'] = !empty($context['game']['extra_data']['type']) ? $context['game']['extra_data']['type'] : 'normal';

		if (empty($context['game']['extra_data']['background_color']))
			$context['game']['extra_data']['background_color'] = array('', '', '');
	}

	if ($context['game_permissions'])
		$context['groups'] = arcadeGetGroups($context['game']['member_groups']);

	// Load categories
	if (!isset($context['arcade_category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name
			FROM {db_prefix}arcade_categories'
		);

		$context['arcade_category'] = array();

		while ($dbrow = $smcFunc['db_fetch_assoc']($request))
			$context['arcade_category'][] = array(
				'id' => $dbrow['id_cat'],
				'name' => $dbrow['cat_name']
			);
		$smcFunc['db_free_result']($request);
	}

	// Load ROM game systems
	$context['rom_systems'] = $txt['arcade_select_gametype_rom'] + array('arcade' => $txt['arcade_rom_switch']['arcade']['name']);
	unset($context['rom_systems']['all']);

	$context['template_layers'][] = 'edit_rom_game';
	$context['sub_template'] = 'edit_rom_game_basic';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_rom_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_rom_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=manageromgames;sa=editrom';
	$context['settings_title'] = $txt['arcade_manage_rom_games'];
	$context['html_headers'] .= '
	<script>
		$(function() {
			$( "#thumbnail" ).after(\'<input style="width: 99%;margin-bottom: 0.5rem;" id="thumb1" name="thumb1" type="file" value="' . $txt['arcadeRomCoreBrowse'] . '" accept=".png,.jpg,.gif" />\');
			$( "#thumbnail_small" ).after(\'<input style="width: 99%;margin-bottom: 0.5rem;" id="thumb2" name="thumb2" type="file" value="' . $txt['arcadeRomCoreBrowse'] . '" accept=".png,.jpg,.gif" />\');
			$( "#cover_icon" ).after(\'<input style="width: 99%;margin-bottom: 0.5rem;" id="covericon" name="covericon" type="file" value="' . $txt['arcadeRomCoreBrowse'] . '" accept=".png,.jpg,.gif" />\');
			$("#thumb1").on( "click", function() {
			  $("#fileflag").val("flag");
			});
			$("#thumb2").on( "click", function() {
			  $("#fileflag").val("flag");
			});
			$("#covericon").change(function() {
				$("#arcade_preview").trigger("click");
			});
			$("#thumb1").change(function() {
				$("#arcade_preview").trigger("click");
			});
			$("#thumb2").change(function() {
				$("#arcade_preview").trigger("click");
			});
			$("#covericon").on( "click", function() {
			  $("#fileflag").val("flag");
			});
		});
	</script>';
}

function EditRomGame2()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $settings, $context, $boarddir, $boardurl, $smcFunc, $sourcedir, $smfVersion;

	$arcadeModSettings['arcadeViewCovers'] = !empty($arcadeModSettings['arcadeViewCovers']) ? intval($arcadeModSettings['arcadeViewCovers']) : 0;
	$context['coverIconEnabled'] = $arcadeModSettings['arcadeViewCovers'] == 1 || $arcadeModSettings['arcadeViewCovers'] == 3 ? true : false;
	$context['game_permissions'] = $arcadeModSettings['arcadePermissionMode'] > 2;
	$arcadeModSettings['arcadeJsInsertDefault'] = !empty($arcadeModSettings['arcadeJsInsertDefault']) && in_array($arcadeModSettings['arcadeJsInsertDefault'], array(0, 1, 2)) ? $arcadeModSettings['arcadeJsInsertDefault'] : 2;
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$romGamesUrl = !empty($arcadeModSettings['romGamesUrl']) ? $arcadeModSettings['romGamesUrl'] : $boardurl . '/ArcadeRetroArch/roms';
	list($context['edit_page'], $newExLink) = array('basic', '');
	isAllowedTo('arcade_admin');
	checkSession('request');
	validateToken('admin', 'post', false);
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	list($cropThumb, $cropCover) = array(!empty($arcadeModSettings['arcadeCropThumbnails']) ? 1 : 0, !empty($arcadeModSettings['arcadeCropCoverArt']) ? 1 : 0);
	require_once($sourcedir . '/ArcadeAdmin.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeMaintenance.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	if (!isset($context['game']))
	{
		if (!isset($_REQUEST['game'])) {
			arcadeClearSession();
			fatal_lang_error('arcade_no_games_selected', false);
		}

		$id = loadRomGame((int) $_REQUEST['game'], true);

		if ($id === false) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		$dbgame = &$context['arcade']['game_data'][$id];
		$coverfile = $dbgame['submit_system'] == 'rom' ? 'romgamecover.gif' : 'arcadegamecover.png';
		$imgFile1 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail'] : $dbgame['thumbnail'];
		$imgFile2 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail_small'] : $dbgame['thumbnail_small'];
		$imgFile3 = !empty($dbgame['cover_icon']) && !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['cover_icon'] : $dbgame['cover_icon'];
		$imgPath1 = file_exists($romGamesDirectory . '/' . $imgFile1) ? $romGamesUrl . '/' . $imgFile1 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath2 = file_exists($romGamesDirectory . '/' . $imgFile2) ? $romGamesUrl . '/' . $imgFile2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath3 = !empty($dbgame['cover_icon']) && file_exists($romGamesDirectory . '/' . $imgFile3) ? $romGamesUrl . '/' . $imgFile3 : $settings['default_theme_url'] . '/images/arc_icons/' . $coverfile;
		$hide = !empty($dbgame['icon_position_hide']) ? (int)$dbgame['icon_position_hide'] : 0;
		$extra = !empty($dbgame['extra_data']) && arcadeIsSerialized($dbgame['extra_data']) ? arcade_safe_unserialize($dbgame['extra_data']) : array();
		$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
		$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
		$extra['type'] = !empty($extra['type']) ? $extra['type'] : 'normal';
		$type = $extra['type'];
		$context['game'] = array(
			'id' => $dbgame['id_game'],
			'internal_name' => $dbgame['internal_name'],
			'category' => $dbgame['id_cat'],
			'name' => htmlspecialchars($dbgame['game_name']),
			'thumbnail' => htmlspecialchars($dbgame['thumbnail']),
			'thumbnail_small' => htmlspecialchars($dbgame['thumbnail_small']),
			'thumbnail_prev' => $imgPath1 . '?' . $suffixVersion,
			'thumbnail_small_prev' => $imgPath2. '?' . $suffixVersion,
			'thumb1' => stripos($imgPath1, $settings['default_theme_url'] . '/images/arc_icons/Default.gif') !== FALSE ? '<li style="font-size: 8pt;">thumbnail: ' . $txt['arcade_error_thumbnail'] . '</li>' : '',
			'thumb2' => stripos($imgPath2, $settings['default_theme_url'] . '/images/arc_icons/Default.gif') !== FALSE ? '<li style="font-size: 8pt;">thumbnail_small: ' . $txt['arcade_error_thumbnail_small'] . '</li>' : '',
			'cover_icon' => htmlspecialchars($dbgame['cover_icon']),
			'cover_icon_prev' => $imgPath3 . '?' . $suffixVersion,
			'covericon' => stripos($imgPath3, $settings['default_theme_url'] . '/images/arc_icons/Default.gif') !== FALSE ? '<li style="font-size: 8pt;">thumbnail: ' . $txt['arcade_error_cover_icon'] . '</li>' : '',
			'description' => htmlspecialchars($dbgame['description']),
			'help' => htmlspecialchars($dbgame['help']),
			'game_file' => (string)$dbgame['game_file'],
			'game_directory' => $dbgame['game_directory'],
			'rom_system' => $dbgame['rom_system'],
			'submit_system' => 'rom',
			'score_type' => $dbgame['score_type'],
			'js_insertion' => !empty($dbgame['js_insertion']) ? $dbgame['js_insertion'] : 0,
			'member_groups' => explode(',', $dbgame['member_groups']),
			'extra_data' => $extra,
			'enabled' => !empty($dbgame['enabled']),
			'download' => !empty($dbgame['download']) ? 1 : 0,
			'icon_position' => !empty($dbgame['icon_position']) ? $dbgame['icon_position'] : 0,
			'icon_position_hide' => abs(intval($hide)),
		);

		if (empty($context['game']['extra_data']))
			$context['game']['extra_data'] = array(
			'width' => '',
			'height' => '',
			'flash_version' => '',
			'background_color' => array('', '', ''),
			'type' => '',
			'remote_link' => '',
		);
	}

	$context['game']['extra_data']['external_link'] = !empty($context['game']['extra_data']['external_link']) ? $context['game']['extra_data']['external_link'] : '';
	$context['game_permissions'] = $arcadeModSettings['arcadePermissionMode'] > 2;
	if (isset($_POST['extra_data']) && isset($_POST['extra_data']['external_link'])) {
		if (filter_var($_POST['extra_data']['external_link'], FILTER_VALIDATE_URL)) {
			$header_response = isArcadeLink($_POST['extra_data']['external_link']);
			if (!empty($header_response) && !empty($header_response[0]) && strpos($header_response[0], "404") === FALSE && !empty($_POST['extra_data']['external_link'])) {
				$newExLink = $_POST['extra_data']['external_link'];
			}
		}
	}
	$exlink = !empty($newExLink) ? $newExLink : (!empty($context['game']['extra_data']['external_link']) ? $context['game']['extra_data']['external_link'] : '');
	$exlink = preg_replace("/^http:/i", "https:", $exlink);

	// Load categories
	if (!isset($context['arcade_category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name
			FROM {db_prefix}arcade_categories'
		);

		$context['arcade_category'] = array();

		while ($dbrow = $smcFunc['db_fetch_assoc']($request))
			$context['arcade_category'][] = array(
				'id' => $dbrow['id_cat'],
				'name' => $dbrow['cat_name']
			);
		$smcFunc['db_free_result']($request);
	}

	// Load Sumbit Systems
	if (!isset($context['rom_systems'])) {
		$context['rom_systems'] = $txt['arcade_select_gametype_rom'] + array('arcade' => $txt['arcade_rom_switch']['arcade']['name']);
		unset($context['rom_systems']['all']);
	}

	list($dbgameOptions, $context['errors'], $errors, $thumbErrors, $thumbnailErrors)  = array(array(), array(), array(), array(), '');

	if (checkSession('request', '', false) !== '')
		$errors['session'] = 'session_timeout';

	// All game specific settings
	if (isset($_POST['game_name']) && trim($_POST['game_name']) == '')
		$errors['game_name'] = 'invalid';

	$_POST['icon_position'] = isset($_POST['icon_position']) ? (int)$_POST['icon_position'] : 0;
	$dbgameOptions['name'] = isset($_POST['game_name']) ? $_POST['game_name'] : '';
	$dbgameOptions['description'] = isset($_POST['description']) ? $_POST['description'] : '';
	$dbgameOptions['thumbnail'] = isset($_POST['thumbnail']) ? $_POST['thumbnail'] : '';
	$dbgameOptions['thumbnail_small'] = isset($_POST['thumbnail_small']) ? $_POST['thumbnail_small'] : '';
	$dbgameOptions['cover_icon'] = isset($_POST['cover_icon']) ? $_POST['cover_icon'] : '';
	$dbgameOptions['help'] = isset($_POST['help']) ? $_POST['help'] : '';
	$dbgameOptions['icon_position'] = !empty($_POST['icon_position']) ? abs(intval($_POST['icon_position'])) : 0;
	$dbgameOptions['icon_position_hide'] = isset($_POST['icon_position_hide']) ? abs(intval($_POST['icon_position_hide'])) : 0;
	$dbgameOptions['enabled'] = !empty($_POST['game_enabled']);
	$preview = isset($_POST['preview']) ? 1 : 0;
	$fileflag = isset($_POST['file_flag']) && $_POST['file_flag'] == 'flag' ? 1 : 0;
	foreach (array('thumbnail', 'thumbnail_small', 'cover_icon') as $post) {
		if (!empty($dbgameOptions[$post])) {
			$dbgameOptions[$post] = preg_replace('~[^a-zA-Z0-9\-\._]~','', $dbgameOptions[$post]);
		}
	}

	if ($context['game_permissions'])
	{
		$dbgameOptions['member_groups'] = array();

		if (!empty($_POST['groups']))
			foreach ($_POST['groups'] as $id)
				$dbgameOptions['member_groups'][] = (int) $id;
	}

	$dbgameOptions['category'] = (int) $_POST['category'];

	if (trim($_POST['internal_name']) == '')
		$errors['internal_name'] = 'invalid';

	if (trim($_POST['game_file']) == '' && !empty($exlink) && !file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgame['internal_name'] . '.url')) {
		$fp = fopen($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgame['internal_name'] . '.url', 'wb');
		fwrite($fp, "[InternetShortcut]\r\nURL=" . $exlink . "\r\n");
		fclose($fp);
		chmod($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgame['internal_name'] . '.url', 0644);
		clearstatcache();
		if (file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgame['internal_name'] . '.url')) {
			$_POST['game_file'] = $dbgame['internal_name'] . '.url';
		}
	}

	if (trim($_POST['game_file']) == '')
		$errors['game_file'] = 'invalid';

	if (!isset($context['rom_systems'][$_POST['rom_system']]))
		$errors['submit_system'] = 'invalid';

	$context['game']['extra_data']['type'] = !empty($context['game']['extra_data']['type']) ? $context['game']['extra_data']['type'] : 'normal';
	$extra_data = $context['game']['extra_data'];

	if (isset($_POST['extra_data']))
	{
		foreach ($_POST['extra_data'] as $item => $value) {
			if ($item == 'external_link') {
				if (substr($value, 6) == 'ftp://' || substr($value, 6) == 'ssh://')
					$value = substr($value, 6);

				if (substr($value, 0, 7) == 'http://')
					$value = 'https://' . substr($value, 7);

				if (substr($value, 0, 8) != 'https://')
					$value = 'https://' . $value;

				$value = filter_var($value, FILTER_SANITIZE_URL);

				if (filter_var($value, FILTER_VALIDATE_URL) === false) {
					$value = '';
				}
			}
			$extra_data[$item] = $value;
		}
	}

	$_POST['game_download'] = isset($_POST['game_download']) ? intval($_POST['game_download']) : 0;
	$_POST['game_compression'] = isset($_POST['game_compression']) ? intval($_POST['game_compression']) : 0;
	$dbgameOptions['internal_name'] = str_replace(array('/', '\\'), array('', ''), trim($_POST['internal_name'], '.'));
	$dbgameOptions['submit_system'] = 'rom';
	$dbgameOptions['rom_system'] = $_POST['rom_system'];
	$dbgameOptions['game_directory'] = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $_POST['game_directory'])))));
	$dbgameOptions['game_file'] = str_replace(array('\\'), array(''), trim($_POST['game_file'], '.'));
	$dbgameOptions['score_type'] = (int)$_POST['score_type'];
	$dbgameOptions['js_insertion'] = isset($_POST['js_insertion']) ? (int)$_POST['js_insertion'] : 0;
	$dbgameOptions['download'] = isset($_POST['game_download']) ? abs(intval($_POST['game_download'])) : 0;
	$dbgameOptions['extra_data'] = $extra_data;
	$dbgameOptions['rom_flag'] = 1;
	$dbgameOptions['switch_system'] = 0;
	if (isset($_POST['rom_system']) && $_POST['rom_system'] == 'arcade') {
		$dbgameOptions['rom_flag'] = 0;
		$dbgameOptions['rom_system'] = 'none';
		$dbgameOptions['submit_system'] = 'html52';
		$dbgameOptions['switch_system'] = 1;
		if (!is_dir($gamesDirectory . '/' . $dbgameOptions['game_directory']) && is_dir($romGamesDirectory . '/' . $dbgameOptions['game_directory']))
			@rename($romGamesDirectory . '/' . $dbgameOptions['game_directory'], $gamesDirectory . '/' . $dbgameOptions['game_directory']);
	}

	if (isset($_POST['thumbnail']) && empty($fileflag)) {
		list($thumbPost, $thumbFix) = array(preg_replace('/[^A-Za-z0-9-_\. ]/', '', $_POST['thumbnail']), arcade_sanitize_file_name($_POST['thumbnail'], 'file'));
		$imgFileDir = !empty($context['game']['game_directory']) ? '/' . $context['game']['game_directory'] : '';
		if (!is_dir($romGamesDirectory . $imgFileDir . '/' . $thumbPost) && file_exists($romGamesDirectory . $imgFileDir . '/' . $thumbPost) && $thumbPost != $thumbFix) {
			@rename($romGamesDirectory . $imgFileDir . '/' . $thumbPost, $romGamesDirectory . $imgFileDir . '/' . $thumbFix);
			if (file_exists($romGamesDirectory . $imgFileDir . '/' . $thumbFix)) {
				$_POST['thumbnail'] = $thumbFix;
				$dbgameOptions['thumbnail'] = $thumbFix;
				if (!empty($context['game']['thumbail_small']) && $context['game']['thumbail_small'] == $thumbPost) {
					$_POST['thumbnail_small'] = $thumbFix;
				}
			}
			else {
				$_POST['thumbnail'] = '';
				$thumbErrors['thumbnail'] = 'invalid';
			}
		}
		elseif ($thumbPost == $thumbFix && !is_dir($romGamesDirectory . $imgFileDir . '/' . $thumbPost) && !file_exists($romGamesDirectory . $imgFileDir . '/' . $thumbPost)) {
			$_POST['thumbnail'] = '';
			$thumbErrors['thumbnail'] = 'invalid';
		}
		elseif ($thumbPost != $thumbFix) {
			$_POST['thumbnail'] = '';
			$thumbErrors['thumbnail'] = 'invalid';
		}
	}
	if (isset($_POST['thumbnail_small']) && empty($fileflag)) {
		list($thumbPost, $thumbFix) = array(preg_replace('/[^A-Za-z0-9-_\. ]/', '', $_POST['thumbnail_small']), arcade_sanitize_file_name($_POST['thumbnail_small'], 'file'));
		$imgFileDir = !empty($context['game']['game_directory']) ? '/' . $context['game']['game_directory'] : '';
		if (!is_dir($romGamesDirectory . $imgFileDir . '/' . $thumbPost) && file_exists($romGamesDirectory . $imgFileDir . '/' . $thumbPost) && $thumbPost != $thumbFix) {
			@rename($romGamesDirectory . $imgFileDir . '/' . $thumbPost, $romGamesDirectory . $imgFileDir . '/' . $thumbFix);
			if (file_exists($romGamesDirectory . $imgFileDir . '/' . $thumbFix)) {
				$_POST['thumbnail_small'] = $thumbFix;
				$dbgameOptions['thumbnail_small'] = $thumbFix;
				if ($context['game']['thumbail'] == $thumbPost) {
					$_POST['thumbnail'] = $thumbFix;
				}
			}
			else {
				$_POST['thumbnail_small'] = '';
				$thumbErrors['thumbnail_small'] = 'invalid';
			}
		}
		elseif ($thumbPost == $thumbFix && !is_dir($romGamesDirectory . $imgFileDir . '/' . $thumbPost) && !file_exists($romGamesDirectory . $imgFileDir . '/' . $thumbPost)) {
			$_POST['thumbnail_small'] = '';
			$thumbErrors['thumbnail_small'] = 'invalid';
		}
		elseif ($thumbPost != $thumbFix) {
			$_POST['thumbnail_small'] = '';
			$thumbErrors['thumbnail_small'] = 'invalid';
		}
	}

	if (isset($_POST['cover_icon']) && empty($fileflag)) {
		list($coverPost, $coverFix) = array(preg_replace('/[^A-Za-z0-9-_\. ]/', '', $_POST['cover_icon']), arcade_sanitize_file_name($_POST['cover_icon'], 'file'));
		$imgFileDir = !empty($context['game']['game_directory']) ? '/' . $context['game']['game_directory'] : '';
		if (!is_dir($romGamesDirectory . $imgFileDir . '/' . $coverPost) && file_exists($romGamesDirectory . $imgFileDir . '/' . $coverPost) && $coverPost != $coverFix) {
			@rename($romGamesDirectory . $imgFileDir . '/' . $coverPost, $romGamesDirectory . $imgFileDir . '/' . $coverFix);
			if (file_exists($romGamesDirectory . $imgFileDir . '/' . $coverFix)) {
				$_POST['cover_icon'] = $coverFix;
				$dbgameOptions['cover_icon'] = $coverFix;
			}
			else {
				$_POST['cover_icon'] = '';
				$thumbErrors['covericon'] = 'invalid';
			}
		}
		elseif ($coverPost == $coverFix && !is_dir($romGamesDirectory . $imgFileDir . '/' . $coverPost) && !file_exists($romGamesDirectory . $imgFileDir . '/' . $coverPost)) {
			$_POST['cover_icon'] = '';
			$thumbErrors['covericon'] = 'invalid';
		}
		elseif ($coverPost != $coverFix) {
			$_POST['cover_icon'] = '';
			$thumbErrors['covericon'] = 'invalid';
		}
	}

	list($errors, $thumbErrors) = array(array_filter($errors), array_filter($thumbErrors));
	if (!empty($preview) && !empty($thumbErrors) && empty($errors)) {
		foreach($thumbErrors as $key => $thumbError) {
			$thumbnailErrors .= $key . '=' . $thumbError . ';';
		}
	}
	if (!empty($errors)) {
		$context['errors'] = array_filter(array_combine($errors, $thumbErrors));
		return EditRomGame();
	}

	// uploaded image files will ovveride any current icon settings
	if (isset($_FILES["thumb1"]["name"]) && isset($_FILES["thumb1"]["tmp_name"])) {
		$errors['thumbnail'] = '';
		$targetFile = basename($_FILES["thumb1"]["name"]);
		$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		if (in_array($imageFileType, array('jpg', 'png', 'gif', 'jpeg'))) {
			$check = getimagesize($_FILES["thumb1"]["tmp_name"]);
			$detectType = !empty($check) && is_array($check) && !empty($check['mime']) ? trim(str_ireplace('image/', '', $check['mime'])) : 'mismatch';
			if ($_FILES["thumb1"]["size"] <= 10000000 && in_array($detectType, array('jpg', 'jpeg', 'gif', 'png'))) {
				list($targetFile, $tempfile) = array(str_replace('.jpeg', '.jpg', $targetFile), '');
				if (!empty($dbgameOptions['thumbnail']) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail'])) {
					$tempfile = 'temp_' . uniqid() . '_' . $dbgameOptions['thumbnail'];
					@rename($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail'], $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					clearstatcache();
				}
				if (@move_uploaded_file($_FILES["thumb1"]["tmp_name"], $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile)) {
					if (!empty($tempfile) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					}
					$tempfile = '';
					if (!empty(basename($imgFile1)) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1)) && basename($imgFile1) != $context['game']['thumbnail_small']) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1));
					}
					elseif (!empty(basename($imgFile1)) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1)) && isset($_FILES["thumb2"]["name"])) {
						if (basename($_FILES["thumb2"]["name"]) != basename($imgFile1))
							@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1));
					}
					elseif (!empty(basename($imgFile1)) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1)) && isset($_POST['thumbnail_small'])) {
						if (basename($_POST['thumbnail_small']) != basename($imgFile1))
							@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1));
					}
					$dbgameOptions['thumbnail'] = $targetFile;
					$context['game']['thumbnail'] = $targetFile;
					$_POST['thumbnail'] = $targetFile;
					$errors['thumbnail'] = '';
					clearstatcache();
					list($q, $newTarget) = array(1, rtrim($context['game']['internal_name'], '_') . '1.' . $imageFileType);
					if (file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $targetFile != $newTarget) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget);
						clearstatcache();
					}
					ArcadeImageResize($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, 120, 120, $cropThumb);
					@rename($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget);
					clearstatcache();
					if (file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $context['game']['thumbnail_small'] != $targetFile) {
						@unlink($arcadeModSettings['gamesDirectory'] . '/' . $context['game']['game_directory'] . '/' . $targetFile);
					}
					$targetFile = $newTarget;
					$dbgameOptions['thumbnail'] = $targetFile;
					$_POST['thumbnail'] = $targetFile;
					$errors['thumbnail'] = '';
					clearstatcache();
					if (empty($context['game']['thumbnail_small']) && !isset($_POST['thumbnail_small'])) {
						$dbgameOptions['thumbnail_small'] = $targetFile;
						$_POST['thumbnail_small'] = $targetFile;
						$errors['thumbnail_small'] = '';
					}
					elseif (!isset($_POST['thumbnail_small']) && !file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $context['game']['thumbnail_small'])) {
						$dbgameOptions['thumbnail_small'] = $targetFile;
						$_POST['thumbnail_small'] = $targetFile;
						$errors['thumbnail_small'] = '';
					}
				}
				elseif (!empty($arcadeModSettings['arcade_log_install_game'])) {
					$dest = !empty($context['game']['game_directory'] . '/' . $targetFile) ? $context['game']['game_directory'] . '/' . $targetFile : $txt['arcade_generalized_file_error_unknown'];
					log_error($txt['arcade_upload_remote_thumbnail_error'] . ' <-> ' . sprintf($txt['arcade_generalized_file_error'], $dest));
				}
				if (!empty($tempfile) && !empty($dbgameOptions['thumbnail']) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
					@rename($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile, $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail']);
					$tempfile = '';
				}
			}
		}
	}
	if (isset($_FILES["thumb2"]["name"]) && isset($_FILES["thumb2"]["tmp_name"])) {
		$errors['thumbnail_small'] = '';
		$targetFile = basename($_FILES["thumb2"]["name"]);
		$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		if (in_array($imageFileType, array('jpg', 'png', 'gif', 'jpeg'))) {
			$check = getimagesize($_FILES["thumb2"]["tmp_name"]);
			$detectType = !empty($check) && is_array($check) && !empty($check['mime']) ? trim(str_ireplace('image/', '', $check['mime'])) : 'mismatch';
			if ($_FILES["thumb2"]["size"] <= 10000000 && in_array($detectType, array('jpg', 'jpeg', 'gif', 'png'))) {
				list($targetFile, $tempfile) = array(str_replace('.jpeg', '.jpg', $targetFile), '');
				if (!empty($dbgameOptions['thumbnail_small']) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small'])) {
					$tempfile = 'temp_' . uniqid() . '_' . $dbgameOptions['thumbnail_small'];
					@rename($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small'], $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					clearstatcache();
				}
				if (@move_uploaded_file($_FILES["thumb2"]["tmp_name"], $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile)) {
					if (!empty($tempfile) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					}
					$tempfile = '';
					if (!empty(basename($imgFile2)) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile2)) && $context['game']['thumbnail'] != basename($imgFile2)) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile2));
					}
					$dbgameOptions['thumbnail_small'] = $targetFile;
					$context['game']['thumbnail_small'] = $targetFile;
					$_POST['thumbnail_small'] = $targetFile;
					$errors['thumbnail_small'] = '';
					clearstatcache();
					list($q, $newTarget) = array(2, rtrim($context['game']['internal_name'], '_') . '2.' . $imageFileType);
					if (file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $targetFile != $newTarget) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget);
						clearstatcache();
					}
					ArcadeImageResize($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile, $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile, 80, 80, $cropThumb);
					@rename($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget);
					clearstatcache();
					if (file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $context['game']['thumbnail'] != $targetFile) {
						@unlink($arcadeModSettings['gamesDirectory'] . '/' . $context['game']['game_directory'] . '/' . $targetFile);
					}
					$targetFile = $newTarget;
					$dbgameOptions['thumbnail_small'] = $targetFile;
					$context['game']['thumbnail_small'] = $newTarget;
					$_POST['thumbnail_small'] = $targetFile;
					$errors['thumbnail_small'] = '';
					clearstatcache();
					if (empty($context['game']['thumbnail']) && !isset($_POST['thumbnail'])) {
						$dbgameOptions['thumbnail'] = $targetFile;
						$_POST['thumbnail'] = $targetFile;
						$errors['thumbnail'] = '';
					}
					elseif (!isset($_POST['thumbnail']) && !file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $context['game']['thumbnail'])) {
						$dbgameOptions['thumbnail'] = $targetFile;
						$_POST['thumbnail'] = $targetFile;
						$errors['thumbnail'] = '';
					}
				}
				elseif (!empty($arcadeModSettings['arcade_log_install_game'])) {
					$dest = !empty($context['game']['game_directory'] . '/' . $targetFile) ? $context['game']['game_directory'] . '/' . $targetFile : $txt['arcade_generalized_file_error_unknown'];
					log_error($txt['arcade_upload_remote_thumbnail_error'] . ' <-> ' . sprintf($txt['arcade_generalized_file_error'], $dest));
				}
				if (!empty($tempfile) && !empty($dbgameOptions['thumbnail_small']) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
					@rename($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile, $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small']);
					$tempfile = '';
				}
			}
		}
	}

	if (isset($_FILES["covericon"]["name"]) && isset($_FILES["covericon"]["tmp_name"])) {
		$errors['thumbnail'] = 'errorfile';
		$targetFile = basename($_FILES["covericon"]["name"]);
		$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		$checkIcon = $_FILES["covericon"]["tmp_name"] != $dbgameOptions['thumbnail_small'] && $_FILES["covericon"]["tmp_name"] != $dbgameOptions['thumbnail'] ? true: false;
		if (in_array($imageFileType, array('jpg', 'png', 'gif', 'jpeg')) && !empty($checkIcon)) {
			$check = getimagesize($_FILES["covericon"]["tmp_name"]);
			$detectType = !empty($check) && is_array($check) && !empty($check['mime']) ? trim(str_ireplace('image/', '', $check['mime'])) : 'mismatch';
			if ($_FILES["covericon"]["size"] <= 10000000 && in_array($detectType, array('jpg', 'jpeg', 'gif', 'png'))) {
				list($targetFile, $tempfile) = array(str_replace('.jpeg', '.jpg', $targetFile), '');
				if (!empty($dbgameOptions['cover_icon']) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon'])) {
					$tempfile = 'temp_' . uniqid() . '_' . $dbgameOptions['cover_icon'];
					@rename($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon'], $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					clearstatcache();
				}
				if (@move_uploaded_file($_FILES["covericon"]["tmp_name"], $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile)) {
					if (!empty($tempfile) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					}
					$tempfile = '';
					$dbgameOptions['cover_icon'] = $targetFile;
					$context['game']['cover_icon'] = $targetFile;
					$_POST['cover_icon'] = $targetFile;
					$errors['thumbnail'] = '';
					clearstatcache();
					list($q, $newTarget) = array(1, rtrim($context['game']['internal_name'], '_') . '3.' . $imageFileType);
					if (file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $targetFile != $newTarget) {
						@unlink($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget);
						clearstatcache();
					}
					list($icwidth, $icheight, $ictype, $icattr) = getimagesize($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile);
					$icwidth2 = $icwidth < 150 ? 150 : ($icwidth > 200 ? 200 : $icwidth);
					$icheight2 = $icheight < 150 ? 150 : ($icheight > 355 ? 355 : $icheight);
					if ($icwidth != $icwidth2 || $icheight != $icheight2) {
						ArcadeImageResize($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $icwidth2, $icheight2, $cropCover);
					}
					@rename($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget);
					clearstatcache();
					if (file_exists($romGamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget)) {
						$targetFile = $newTarget;
						$dbgameOptions['cover_icon'] = $targetFile;
						$_POST['cover_icon'] = $targetFile;
						$errors['thumbnail'] = '';
					}
				}
				elseif (!empty($tempfile) && !empty($dbgameOptions['cover_icon']) && file_exists($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
					@rename($romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile, $romGamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon']);
					$tempfile = '';
				}
			}
		}
	}

	if (!empty($_POST['game_compression']) && !empty($context['game']['game_file']) && !empty($context['game']['game_directory'])) {
		if (is_dir($arcadeModSettings['romGamesDirectory'] . '/' . $context['game']['game_directory'])) {
			$path = $arcadeModSettings['romGamesDirectory'] . '/' . $context['game']['game_directory'];
			clearstatcache($path);
			// attempt to fix possible ROM game file mismatch
			if (!file_exists($arcadeModSettings['romGamesDirectory'] . '/' . $context['game']['game_directory'] . '/' . $context['game']['game_file'])) {
				list($scanned_directory, $allFiles, $fileData, $zipFiles, $Files7z, $romFileTypes) = array(array(), array(), array(), array(), array(), str_replace('|', ',', $arcadeModSettings['arcadeRomGameTypes']) . ',' . str_replace('|', ',', mb_strtoupper($arcadeModSettings['arcadeRomGameTypes'])));
				$scanned_directory = glob($path . "/*.{" . $romFileTypes . "}", GLOB_BRACE);
				if (count($scanned_directory) > 0) {
					$scanned_directory = !empty($scanned_directory) ? array_values(array_filter($scanned_directory)) : $scanned_directory;
					foreach ($scanned_directory as $fileX) {
						$fileX = basename($fileX);
						$typeX = pathinfo($fileX, PATHINFO_EXTENSION);
						// ignore txt & doc files
						if (!in_array(mb_strtolower($typeX), array('txt', 'doc'))) {
							switch(mb_strtolower($typeX)) {
								case 'zip':
									$zipFiles[] = $fileX;
									break;
								case '7z':
									$Files7z[] = $fileX;
									break;
								default:
									$fileData[] = $fileX;
							}
						}
					}
					$context['game']['game_file'] = !empty($fileData) ? $fileData[0] : (!empty($Files7z) ? $Files7z[0] : $zipFiles[0]);
					$allFiles = array_merge($fileData, $Files7z, $zipFiles);
					$allFiles = !empty($allFiles) ? array_values(array_filter($allFiles)) : $allFiles;
					foreach ($allFiles as $fileZ) {
						if ($fileZ != $context['game']['game_file'])
							@unlink($path . '/' . $fileZ);
					}
					clearstatcache($path);
				}
			}
			$ext = pathinfo($context['game']['game_file'], PATHINFO_EXTENSION);
			$oldFileName = pathinfo($context['game']['game_file'], PATHINFO_FILENAME);
			if (!in_array(mb_strtolower($ext), array('zip', '7z'))) {
				list($zippedFiles, $scanned_directory, $fileData, $zipFiles, $Files7z, $romFileTypes) = array(array(), array(), array(), array(), array(), str_replace('|', ',', $arcadeModSettings['arcadeRomGameTypes']) . ',' . str_replace('|', ',', mb_strtoupper($arcadeModSettings['arcadeRomGameTypes'])));
				$scanned_directory = glob($path . "/*.*", GLOB_BRACE);
				$newZipName =  rom_sanitize_file_name($oldFileName . '.zip', 'file');
				$zip = new ZipArchive();
				$zip->open($path . '/' . $newZipName, ZipArchive::CREATE);
				foreach ($scanned_directory as $file) {
					$filenamex = basename($file);
					$filetype = pathinfo($filenamex, PATHINFO_EXTENSION);
					if (!in_array(mb_strtolower($filetype), array('zip', 'php', 'jpg', 'gif', 'png', '7z'))) {
						$zip->addFile($file, basename($path) . '/' . basename($file));
						$zippedFiles[] = $file;
					}
				}
				$zip->close();
				clearstatcache($path);
				if (file_exists($path . '/' . $newZipName)) {
					$dbgameOptions['game_file'] = $newZipName;
					foreach ($scanned_directory as $file) {
						if (basename($file) != $newZipName && in_array($file, $zippedFiles))
							@unlink($file);
					}
				}
			}
			elseif (mb_strtolower($ext) == 'zip') {
				list($fileData, $zipFiles, $Files7z, $romFileTypes) = array(array(), array(), array(), str_replace('|', ',', $arcadeModSettings['arcadeRomGameTypes']) . ',' . str_replace('|', ',', mb_strtoupper($arcadeModSettings['arcadeRomGameTypes'])));
				//arcadeUnzip($path . '/' . $sanitizedFilename, $path . '/', false, false);
				arcadeUnzipFlatten($path . '/' . $dbgameOptions['game_file'], $path);
				clearstatcache($path);
				$scanned_directory = array();
				$scanned_directory = glob($path . "/*.{" . $romFileTypes . "}", GLOB_BRACE);
				if (count($scanned_directory) > 0) {
					$scanned_directory = !empty($scanned_directory) ? array_values(array_filter($scanned_directory)) : $scanned_directory;
					foreach ($scanned_directory as $fileX) {
						$typeX = pathinfo($fileX, PATHINFO_EXTENSION);
						$fileX = basename($fileX);
						// ignore txt & doc files
						if (!in_array(mb_strtolower($typeX), array('txt', 'doc'))) {
							switch(mb_strtolower($typeX)) {
								// possible double compression?
								case 'zip':
									$zipFiles[] = $fileX;
									break;
								case '7z':
									$Files7z[] = $fileX;
									break;
								default:
									$fileData[] = $fileX;
							}
						}
					}
					$newFile = !empty($fileData) ? $fileData[0] : (!empty($Files7z) ? $Files7z[0] : $zipFiles[0]);
					$ext1 = pathinfo($newFile, PATHINFO_EXTENSION);
					@rename($path . '/' . basename($newFile), $path . '/' . $oldFileName . '.' . $ext1);
					clearstatcache($path);
					if (file_exists($path . '/' . $oldFileName . '.' . $ext1)) {
						@unlink($path . '/' . $oldFileName . '.' . $ext);
						$dbgameOptions['game_file'] = $oldFileName . '.' . $ext1;
						clearstatcache($path);
						if (file_exists($path . '/' . $oldFileName . '.' . $ext)) {
							// log error for left over file
						}
					}
				}
			}
		}
	}

	unset($_POST['game_compression']);
	updateGame($context['game']['id'], $dbgameOptions, true, true);
	$start = !empty($_SESSION['arcade_game_start_edit']) ? 'start=' . $_SESSION['arcade_game_start_edit'] . ';' : '';

	if ($dbgameOptions['rom_flag'] == 0)
		redirectexit('action=admin;area=managegames;sa=edit;game=' . $context['game']['id']);

	if (empty($preview))
		redirectexit('action=admin;area=manageromgames;' . $start . $context['session_var'] . '=' . $context['session_id']);
	else
		redirectexit('action=admin;area=manageromgames;sa=editrom;game=' . $context['game']['id'] . ';' . $thumbnailErrors . '#adm_submenus');
}

function ManageRomGamesList()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $sourcedir, $boarddir, $smcFunc;

	isAllowedTo('arcade_admin');
	$context['arcade_smf_version'] = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	if (!isset($context['arcade_category']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name
			FROM {db_prefix}arcade_categories'
		);

		$context['arcade_category'] = array();

		while ($dbrow = $smcFunc['db_fetch_assoc']($request))
			$context['arcade_category'][$dbrow['id_cat']] = array(
				'id' => $dbrow['id_cat'],
				'name' => $dbrow['cat_name']
			);
		$smcFunc['db_free_result']($request);
	}

	$category_data = '';

	foreach ($context['arcade_category'] as $id => $cat)
		$category_data .= '
	<option value="' . $id . '">' . $cat['name'] . '</option>';

	if (isset($_REQUEST['category_submit']))
	{
		$_REQUEST['game'] = !empty($_REQUEST['game']) ? $_REQUEST['game'] : array();
		$_REQUEST['game'] = !is_array($_REQUEST['game']) ? array($_REQUEST['game']) : $_REQUEST['game'];
		foreach ($_REQUEST['game'] as $id_game)
			updateGame($id_game, array('category' => (int) $_REQUEST['category']), true, true);

		redirectexit('action=admin;area=manageromgames');
	}

	list($filter, $sortType, $sortAlpha) = array('all', !empty($_SESSION['arcade_manage_rom_sort_select_type']) ? $_SESSION['arcade_manage_rom_sort_select_type'] : 'all', !empty($_SESSION['arcade_manage_rom_sort_select_alpha']) ? $_SESSION['arcade_manage_rom_sort_select_alpha'] : 'all');
	$context['arcade_rom_game_types_search_list'] = array_keys($txt['arcade_select_gametype_rom']);
	$alphaArray = array_merge(array('All'), range('A', 'Z'));

	if (isset($_REQUEST['filter']) && in_array($_REQUEST['filter'], array('enabled', 'disabled')))
		$filter = $_REQUEST['filter'];
	if (isset($_REQUEST['sortType']) && in_array($_REQUEST['sortType'], $context['arcade_rom_game_types_search_list']))
	{
		$sortType = $_REQUEST['sortType'];
		$_SESSION['arcade_manage_rom_sort_select_type'] = $sortType;
	}
	if (isset($_REQUEST['sortAlpha']) && in_array($_REQUEST['sortAlpha'], $alphaArray))
	{
		$sortAlpha = stripos($_REQUEST['sortAlpha'], 'all') !== false ? mb_strtolower($_REQUEST['sortAlpha']) : $_REQUEST['sortAlpha'];
		$_SESSION['arcade_manage_rom_sort_select_alpha'] = $sortAlpha;
	}

	$listOptions = array(
		'id' => 'rom_games_list',
		'title' => '',
		'items_per_page' => $arcadeModSettings['gamesPerPage'],
		'base_href' => $scripturl . '?action=admin;area=manageromgames' . ($filter !== 'all' ? ';filter=' . $filter : ''),
		'default_sort_col' => 'name',
		'no_items_label' => sprintf($filter == 'all' ? $txt['arcade_no_rom_games_installed'] : $txt['arcade_no_games_filter'], $scripturl . '?action=admin;area=manageromgames;sa=rom_install'),
		'use_tabs' => true,
		'list_rom_menu' => array(
			'style' => 'buttons',
			'position' => 'right',
			'columns' => 3,
			'show_on' => 'both',
			'links' => array(
				'show_all' => array(
					'href' => $scripturl . '?action=admin;area=manageromgames',
					'label' => $txt['manage_games_filter_all'],
					'is_selected' => $filter == 'all',
				),
				'enabled' => array(
					'href' => $scripturl . '?action=admin;area=manageromgames;filter=enabled',
					'label' => $txt['manage_games_filter_enabled'],
					'is_selected' => $filter == 'enabled',
				),
				'disabled' => array(
					'href' => $scripturl . '?action=admin;area=manageromgames;filter=disabled',
					'label' => $txt['manage_games_filter_disabled'],
					'is_selected' => $filter == 'disabled',
				),
			),
		),
		'get_items' => array(
			'function' => 'list_getGamesRomInstalled',
			'params' => array($filter),
		),
		'get_count' => array(
			'function' => 'list_getNumGamesRomInstalled',
			'params' => array($filter),
		),
		'columns' => array(
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form, \'game[]\');" />',
					'style' => 'width: 10px;'
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="game[]" value="%d" class="check" />',
						'params' => array('id' => false),
					),
					'style' => 'text-align: center;',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['arcade_game_name'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => function ($dbrowData) {
						global $txt, $smcFunc, $context;
						list($conflictId, $dbgameId, $conflictCss) = array(0, 0, '');
						list($saveTypeArray, $saveTypeArrayLong) = array(array_keys($txt['arcade_select_gametype_rom']), array_values($txt['arcade_select_gametype_rom']));
						$key = array_search($dbrowData['rom_system'], $saveTypeArray);
						$link = '<a ' . $conflictCss . 'id="game_' . $dbrowData['id'] . '" href="' . $dbrowData['href'] . '">' . $dbrowData['name'] . '</a>';
						$romSystem = $key !== false && !empty($saveTypeArrayLong[$key]) ? $saveTypeArrayLong[$key] : $dbrowData['rom_system'];
						if (!empty($dbrowData['error']))
							$link .= '<div class="alert smalltext">' . $dbrowData['error'] . '</div>';
						$link .= !empty($_SESSION['arcade_isMobile']) ? '' : '<div style="display: inline;float: right;">' . $romSystem . '</div>';
						return $link;
					},
				),
				'sort' => array(
					'default' => 'g.game_name',
					'reverse' => 'g.game_name DESC',
				),
			),
			'category' => array(
				'header' => array(
					'value' => $txt['arcade_category'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => function($dbrowData) {
						$link = $dbrowData['category']['name'];

						return $link;
					},
				),
				'sort' => array(
					'default' => 'cat.cat_name',
					'reverse' => 'cat.cat_name DESC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=manageromgames' . ($filter !== 'all' ? ';filter=' . $filter : ''),
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<select name="category">' . $category_data . '</select> <input class="button_submit" type="submit" name="category_submit" value="' . $txt['quickmod_change_category'] . '" />
				<input class="button_submit" type="submit" name="uninstall_submit" value="' . $txt['quickmod_uninstall_selected'] . '" />',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
	);

	// tabs
	$_SESSION['arcade_game_start_edit'] = isset($_REQUEST['start']) ? abs(intval($_REQUEST['start'])) : 0;
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_rom_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_rom_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=manageromgames;sa=rom_list';
	$context['settings_title'] = $txt['arcade_manage_rom_games'];
	if (isset($_REQUEST['done']) && !empty($_SESSION['qaction']))
	{
		$context['show_done'] = true;
		if ($_SESSION['qaction'] == 'install')
		{
			$context['qaction_title'] = $txt['arcade_install_complete'];
			$context['qaction_text'] = $txt['arcade_install_following_games'];
		}
		elseif ($_SESSION['qaction'] == 'uninstall')
		{
			$context['qaction_title'] = $txt['arcade_uninstall_complete'];
			$context['qaction_text'] = $txt['arcade_uninstall_following_games'];
		}

		if (isset($_SESSION['qaction_data']) && is_array($_SESSION['qaction_data'])) {
			$context['qaction_data'] = $_SESSION['qaction_data'];
			$context['qaction_data'] = array_filter($context['qaction_data']);
		}

		unset($_SESSION['qaction_data']);
		unset($_SESSION['qaction']);
	}

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	// filter the array for a valid search argument
	$_SESSION['search_rom_game_list'] = isset($_GET['search_game']) ? preg_replace('~[^a-z0-9]+~i', '', trim($_GET['search_game'])) : (!empty($_SESSION['search_rom_game_list']) ? $_SESSION['search_rom_game_list'] : '');
	if (!empty($_SESSION['search_rom_game_list']))
	{
		$listArray = $context['games_list']['rows'];
		foreach ($context['games_list']['rows'] as $id => $dbrow)
		{
			if (!empty($dbrow['data']['name']['value']))
			{
				$dbgameName = preg_replace('#<a.*?>([^<]*)</a>#i', '$1', $dbrow['data']['name']['value']);
				if (strlen($_SESSION['search_rom_game_list']) == 1 && strtolower(substr($dbgameName, 0, 1))  != strtolower($_SESSION['search_rom_game_list']))
					unset($context['games_list']['rows'][$id]);
				elseif (stripos($dbrow['data']['name']['value'], $_SESSION['search_rom_game_list']) === false)
					unset($context['games_list']['rows'][$id]);
			}
		}

		if (empty($context['games_list']['rows']))
			$context['games_list']['rows'] = $listArray;
	}

	// add a search function to the generic list
	$context['arcade_game_types_search_list'] = $txt['arcade_select_gametype_rom'];
	$clickFunction = 'window.location.href = \'' . $scripturl . '?action=admin;area=manageromgames;search_game=\' + document.getElementById(\'search_game\').value + \';sesc=' . $context['session_id'] . ';\'';
	$context['games_list']['title'] = '<span title="' . $txt['arcade_manage_games_clear_search']. '"><a href="' . $scripturl . '?action=admin;area=manageromgames;search_game=;sesc=' . $context['session_id'] . '">' . $txt['arcade_manage_rom_games_list'] . '</a></span> <span style="float: right;"><input id="gameSearchButton" type="button" onclick="' . $clickFunction . '" value="' . $txt['arcade_manage_games_list_search'] . '"> <input id="search_game" onkeydown="if (event.keyCode == 13){event.preventDefault();document.getElementById(\'gameSearchButton\').click();}" type="text" name="search_game" style="width: 8em;" /></span>';
	$context['sub_template'] = 'manage_rom_games_list';
	$context['html_headers'] .= '
	<script type="text/javascript">
		function arcadeSortRomListOptions() {
			var arcadeAlphaVal = document.getElementById("arcadealphaval");
			var arcadeTypeVal = document.getElementById("arcadetypeval");
			arcadeAlphaVal.onchange = function arcadeSortListAlphaVal() {
				window.location.assign("' . $scripturl . '?action=admin;area=manageromgames;sa=rom_main;sortAlpha=" + arcadeAlphaVal.value);
			}
			arcadeTypeVal.onchange = function arcadeSortListTypeVal() {
				window.location.assign("' . $scripturl . '?action=admin;area=manageromgames;sa=rom_main;sortType=" + arcadeTypeVal.value);
			}
		}
		if (window.addEventListener)
			window.addEventListener("load", arcadeSortRomListOptions, false);
		else if (window.attachEvent)
			window.attachEvent("onload", arcadeSortRomListOptions);
		else
			window.onload = arcadeSortRomListOptions();
	</script>';
}

function ExportRomGameInfo()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $boarddir, $smcFunc, $sourcedir, $arcade_version;

	$id = stripos($_REQUEST['area'], 'manageromgames') !== FALSE ? loadRomGame((int) $_REQUEST['game'], true) : loadGame((int) $_REQUEST['game'], true);
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$array = array('internal_name', 'game_name', 'description', 'help', 'thumbnail', 'thumbnail_small', 'cover_icon', 'game_file', 'submit_system');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');

	if ($id === false) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_not_found', false);
	}

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_rom_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=managegames;sa=export';
	$context['settings_title'] = $txt['arcade_manage_games'];
	$dbgameInfo = &$context['arcade']['game_data'][$id];
	$check = !empty($dbgameInfo['game_file']) && strlen($dbgameInfo['game_file']) > 9 && substr($dbgameInfo['game_file'], -10) == 'index.html' ? true : (!empty($dbgameInfo['game_file']) && strlen($dbgameInfo['game_file']) > 8 && substr($dbgameInfo['game_file'], -9) == 'index.php' ? true : false);
	$subSystem = !empty($dbgameInfo['submit_system']) ? $dbgameInfo['submit_system'] : '';
	$internal = !empty($dbgameInfo['internal_name']) ? $dbgameInfo['internal_name'] : '';
	$phpFilex = $check && $subSystem == 'html52' ? $internal . '.php' : (!empty($dbgameInfo['game_file']) ? str_replace('.swf', '.php', $dbgameInfo['game_file']) : 'generated_file.php');
	$phpFilex = preg_replace('"\.(htm|html)$"', '.php', $phpFilex);
	$extra = !empty($dbgameInfo['extra_data']) && arcadeIsSerialized($dbgameInfo['extra_data']) ? arcade_safe_unserialize($dbgameInfo['extra_data']) : array();
	$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
	$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
	$data = array(
		'id_game' => !empty($dbgameInfo['id_game']) ? $dbgameInfo['id_game'] : 0,
		'enabled' => !empty($dbgameInfo['enabled']) ? $dbgameInfo['enabled'] : 0,
		'js_insertion' => !empty($dbgameInfo['js_insertion']) ? $dbgameInfo['js_insertion'] : 0,
		'score_type' => !empty($dbgameInfo['score_type']) ? $dbgameInfo['score_type'] : 0,
		'gamename' => !empty($dbgameInfo['game_name']) ? $dbgameInfo['game_name'] : '',
		'internal_name' => !empty($dbgameInfo['internal_name']) ? $dbgameInfo['internal_name'] : '',
		'php_file' => !empty($dbgameInfo['internal_name']) ? $dbgameInfo['internal_name'] . '.php' : '',
		'help' => !empty($dbgameInfo['help']) ? $dbgameInfo['help'] : '',
		'description' => !empty($dbgameInfo['description']) ? $dbgameInfo['description'] : '',
		'game_directory' => !empty($dbgameInfo['game_directory']) ? $dbgameInfo['game_directory'] : '',
		'game_file' => !empty($dbgameInfo['game_file']) ? $dbgameInfo['game_file'] : 'generated_file.swf',
		'gamephp' => $phpFilex,
		'latest_day' => !empty($dbgameInfo['latest_day']) ? $dbgameInfo['latest_day'] : '',
		'latest_year' => !empty($dbgameInfo['latest_year']) ? $dbgameInfo['latest_year'] : '',
		'permission' => !empty($dbgameInfo['permission']) ? (int)$dbgameInfo['permission'] : 0,
		'report_day' => !empty($dbgameInfo['report_day']) ? $dbgameInfo['report_day'] : '',
		'report_year' => !empty($dbgameInfo['report_year']) ? $dbgameInfo['report_year'] : '',
		'user_id' => !empty($dbgameInfo['user_id']) ? $dbgameInfo['user_id'] : 0,
		'report_id' => !empty($dbgameInfo['report_id']) ? $dbgameInfo['report_id'] : 0,
		'report_reason' => !empty($dbgameInfo['report_reason']) ? $dbgameInfo['report_reason'] : '',
		'thumbnail' => !empty($dbgameInfo['thumbnail']) ? $dbgameInfo['thumbnail'] : '',
		'thumbnail_small' => !empty($dbgameInfo['thumbnail_small']) ? $dbgameInfo['thumbnail_small'] : '',
		'cover_icon' => !empty($dbgameInfo['cover_icon']) ? $dbgameInfo['cover_icon'] : '',
		'extra_data' => $extra,
		'id_cat' => !empty($dbgameInfo['id_cat']) ? $dbgameInfo['id_cat'] : 0,
		'submit_system' => !empty($dbgameInfo['submit_system']) ? $dbgameInfo['submit_system'] : '',
		'rom_system' => !empty($dbgameInfo['rom_system']) ? $dbgameInfo['rom_system'] : '',
		'rom_flag' => 1,
		'game_rating' => !empty($dbgameInfo['game_rating']) ? $dbgameInfo['game_rating'] : 0,
		'gamefile_name' => !empty($internal) ? ArcadeSpecialChars(trim($internal), 'name') : '',
		'gamesave' => 'games_download',
		'dl_disable' => !empty($dbgameInfo['download_disable']) ? $dbgameInfo['download_disable'] : 0,
		'report_user' => !empty($dbgameInfo['user_id']) ? (int)$dbgameInfo['user_id'] : 0,
	);

	ob_end_clean();
	if (!empty($arcadeModSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	$readonly = $smfVersion == 'v2.1' ? 'readonly' : 'readonly="readonly"';
	$controls = 'GAME_CONTROL_MOUSE';
	$stype = array_keys($txt['arcade_select_gametype_rom']);
	$romtype = !empty($data['rom_system']) && in_array($data['rom_system'], $stype) ? $data['rom_system'] : 'none';
	$data['description'] = stripslashes($data['description']);
	$data['description'] = str_replace("&rsquo;", "'", htmlspecialchars_decode(mb_convert_encoding($data['description'], "HTML-ENTITIES", "UTF-8")));
	$data['description'] = htmlspecialchars_decode($data['description']);
	$data['help'] = stripslashes($data['help']);
	$data['help'] = str_replace("&rsquo;", "'", htmlspecialchars_decode(mb_convert_encoding($data['help'], "HTML-ENTITIES", "UTF-8")));
	$data['help'] = htmlspecialchars_decode($data['help']);

	// do we really need these alias names on everyone's arcade?
	foreach (array('_origon', '_masodo', '_jvh5') as $trailblazer)
		$data['gamename'] = strlen(mb_substr($data['gamename'], 0, -strlen($trailblazer))) > 0 && mb_substr(mb_strtolower($data['gamename']), -strlen($trailblazer)) == $trailblazer ? mb_substr($data['gamename'], 0, -strlen($trailblazer)) : $data['gamename'];

	$dbgameinfo = array(
		'active' => $data['enabled'],
		'bgcolor' => !empty($data['extra_data']['background_color']) ? arcadeHexToColor($data['extra_data']['background_color']) : '',
		'gcat' => $data['id_cat'],
		'gheight' => !empty($data['extra_data']['height']) ? $data['extra_data']['height'] : 500,
		'gwidth' => !empty($data['extra_data']['width']) ? $data['extra_data']['width'] : 500,
		'external_link' => !empty($data['extra_data']['external_link']) ? $data['extra_data']['external_link'] : '',
		'gkeys' => htmlspecialchars($data['help'], ENT_QUOTES | ENT_HTML5),
		'gname' => $data['internal_name'],
		'gtitle' => htmlspecialchars($data['gamename'], ENT_QUOTES | ENT_HTML5),
		'gwords' => htmlspecialchars($data['description'], ENT_QUOTES | ENT_HTML5),
		'object' => htmlspecialchars($data['description'], ENT_QUOTES | ENT_HTML5),
		'snggame' => $data['score_type'],
		'savetype' => 'rom',
		'romtype' => $romtype,
		'date' => gmdate('D, d M Y H:i:s \G\M\T', time()),
		'thumbnail' => $data['thumbnail'],
		'thumbnail_small' => $data['thumbnail_small'],
		'cover_icon' => $data['cover_icon'],
		'file' => $data['game_file'],
		'flash_version' => !empty($data['extra_data']['flash_version']) ? $data['extra_data']['flash_version'] : 0,
		'type' => (!empty($data['extra_data']['type'])) &&  $data['extra_data']['type'] == 'fullscreen' ? $data['extra_data']['type'] : 'normal',
		'score_type' => $data['score_type'],
		'js_insertion' => $data['js_insertion'],
		'force_ibp' => !empty($data['force_php']) ? 1 : 0,
		'romflag' => 1,
	);
	$thumbnail = !empty($data['thumbnail_small']) ? $data['thumbnail_small'] : $data['thumbnail'];
	$thumbnailNum = $thumbnail == $dbgameinfo['gname'] . '1' || $thumbnail == $dbgameinfo['gname'] . '2' ? mb_substr($thumbnail, -1) : '0';
	$covericon = !empty($data['cover_icon']) ? htmlspecialchars(str_replace(array("'", '"'), "", $data['cover_icon']), ENT_QUOTES | ENT_HTML5) : '';
	$helpArray = array(
		'arcade_game_control_mouse_key' => 'GAME_CONTROL_KEYBOARD_MOUSE',
		'arcade_game_control_mouse' => 'GAME_CONTROL_MOUSE',
		'arcade_game_control_key' => 'GAME_CONTROL_KEYBOARD',
		'arcade_game_control_mouse_touch' => 'GAME_CONTROL_MOUSE_TOUCH',
		'arcade_game_control_key_touch' => 'GAME_CONTROL_KEYBOARD_TOUCH'
	);
	foreach($helpArray as $key => $control)
	{
		if (!empty($txt[$key]) && stripos($data['help'], $txt[$key]) !== false)
		{
			$controls = $control;
			break;
		}
	}

	$infofile = '<?php
/*---------------------------------------------------------------*/
/* File Created by SMF Arcade ' . $arcade_version . '
/* File Generated: ' . $dbgameinfo['date'] . '
/*---------------------------------------------------------------*/

$config = array(
	\'active\'	=> \'' . $dbgameinfo['active'] . '\',
	\'bgcolor\'	=> \'' . $dbgameinfo['bgcolor'] . '\',
	\'gcat\'		=> \'' . $dbgameinfo['gcat'] . '\',
	\'gkeyimg\'	=> \'' . $thumbnailNum . '\',
	\'covericon\'	=> \'' . $covericon . '\',
	\'gheight\'	=> \'' . $dbgameinfo['gheight'] . '\',
	\'gwidth\'	=> \'' . $dbgameinfo['gwidth'] . '\',
	\'gkeys\'		=> \'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['gkeys']) . '\',
	\'gname\'		=> \'' . $dbgameinfo['gname'] . '\',
	\'gtitle\'	=> \'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['gtitle']) . '\',
	\'gwords\'	=> \'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['gwords']) . '\',
	\'object\'	=> \'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['object']) . '\',
	\'snggame\'	=> \'' . $dbgameinfo['snggame'] . '\',
	\'gtype\'		=> \'' . (!empty($dbgameinfo['type']) &&  $dbgameinfo['type'] == 'fullscreen' ? 'fullscreen' : 'normal') . '\',
	\'savetype\'	=> \'' . $dbgameInfo['submit_system'] . '\',
	\'romtype\'	=> \'' . $dbgameInfo['rom_system'] . '\',
	\'romflag\'	=> \'1\',
	\'scoring\'	=> \'' . $dbgameinfo['score_type'] . '\',
	\'exlink\'	=> \'' . $dbgameinfo['external_link'] . '\',
);
?>';

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	echo '
	<html>
		<head>
			<title>PHP Example Output</title>
		</head>
		<body>
			<textarea rows="27" cols="250" style="border:none;" ' . $readonly . '>', $infofile, '</textarea>
		</body>
	</html>';

	obExit(false);
}

function uninstallRomGames($games, $delete_files = false)
{
	global $smcFunc, $arcadeModSettings, $boarddir, $sourcedir;
	isAllowedTo('arcade_admin');
	require_once($sourcedir . '/Subs-Package.php');
	require_once($sourcedir . '/RemoveTopic.php');
	require_once($boarddir . '/ArcadeSources/ArcadeDownload.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	@ini_set('memory_limit', '256M');
	$maindir = rtrim(str_replace('\\', '/', $boarddir), '/') . '/ArcadeRetroArch/roms';
	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_name, game_directory, game_file, id_cat, game_rating, description, internal_name, id_topic, cover_icon,
			thumbnail, thumbnail_small, extra_data, submit_system, rom_system, enabled, score_type, help, js_insertion, download, rom_flag
		FROM {db_prefix}arcade_games
		WHERE id_game IN({array_string:games}) AND rom_flag = {int:rom_flag}',
		array(
			'games' => (is_array($games) ? $games : array($games)),
			'rom_flag' => 1,
		)
	);

	list($status, $topics) = array(array(), array());

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$gameNameDel = !empty($row['game_name']) ? $row['game_name'] : '';
		$directory = $maindir . (!empty($row['game_directory']) ? '/' . $row['game_directory'] : '');
		$directory = preg_replace('#/+#','/',implode('/', array_map(function($value) {return rtrim($value, '.');}, explode('/', str_replace('\\', '/', $directory)))));
		$directory = rtrim($directory, '/');
		$directory = str_replace('\\', '/', $directory);
		$idGame = intval($row['id_game']);
		if (!empty($row['id_topic']))
			$topics[] = $row['id_topic'];

		if (!empty($row['game_directory']) && $delete_files)
			arcadeRmdir($maindir . '/' . $row['game_directory']);
		else {
			$romSystem = !empty($row['rom_system']) ? $row['rom_system'] : '';
			$subSystem = 'rom';
			$internal = !empty($row['internal_name']) ? $row['internal_name'] : '';
			if (!empty($row['game_file']) && stripos($row['game_file'], '.') !== FALSE) {
				$fileName = pathinfo($row['game_file'], PATHINFO_FILENAME);
			}
			elseif (!empty($row['game_directory'])) {
				$fileName = $row['game_directory'];
			}
			else {
				$fileName = $row['internal_name'];
			}
			$phpFilex = $fileName . '.php';
			$romFileName = 'rom_' . $fileName;
			$extra = !empty($row['extra_data']) && arcadeIsSerialized($row['extra_data']) ? arcade_safe_unserialize($row['extra_data']) : array();
			$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
			$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
			$extra['type'] = !empty($extra['type']) && $extra['type'] == 'fullscreen' ? 'fullscreen' : 'normal';

			$dataArray = array(
				'id_game' => !empty($idGame) ? $idGame : 0,
				'enabled' => !empty($row['enabled']) ? $row['enabled'] : 0,
				'download' => !empty($row['download']) ? 1 : 0,
				'score_type' => !empty($row['score_type']) ? $row['score_type'] : 0,
				'js_insertion' => !empty($row['js_insertion']) ? $row['js_insertion'] : 0,
				'gamename' => !empty($row['game_name']) ? $row['game_name'] : '',
				'internal_name' => $fileName,
				'php_file' => $phpFilex,
				'help' => !empty($row['help']) ? $row['help'] : '',
				'description' => !empty($row['description']) ? $row['description'] : '',
				'game_directory' => !empty($row['game_directory']) ? $row['game_directory'] : '',
				'game_file' => !empty($row['game_file']) ? $row['game_file'] : 'generated_file.swf',
				'gamephp' => $phpFilex,
				'thumbnail' => !empty($row['thumbnail']) ? $row['thumbnail'] : '',
				'thumbnail_small' => !empty($row['thumbnail_small']) ? $row['thumbnail_small'] : '',
				'cover_icon' => !empty($row['cover_icon']) ? $row['cover_icon'] : '',
				'extra_data' => $extra,
				'submit_system' => $subSystem,
				'rom_system' => $romSystem,
				'id_cat' => !empty($row['id_cat']) ? $row['id_cat'] : 0,
				'rom_flag' => 1,
				'type' => $extra['type'],
				'external_link' => !empty($extra['external_link']) ? $extra['external_link'] : '',
				'gwords' => htmlspecialchars($row['description'], ENT_QUOTES | ENT_HTML5),
				'object' => htmlspecialchars($row['description'], ENT_QUOTES | ENT_HTML5),
			);
			$tempFiles = arcade_game_down($dataArray, $directory, 1);
			$ext =  pathinfo($tempFiles[0], PATHINFO_EXTENSION);
			@rename($tempFiles[0], $directory . '/' . $dataArray['php_file']);
			$upload_directory = str_replace('\\', '/', $boarddir) . '/games_rom_upload';
			@rename($directory, $upload_directory . '/' . $fileName);
			if (is_dir($upload_directory . '/' . $fileName)) {
				$zip = new ZipArchive();
				$zip->open($upload_directory . '/' . $romFileName . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($upload_directory . '/' . $fileName),
					RecursiveIteratorIterator::LEAVES_ONLY
				);
				foreach ($files as $file)
				{
					if (!$file->isDir())
					{
						// Get real and relative path for current file
						$filePath = $file->getRealPath();
						$relativePath = substr($filePath, strlen($upload_directory . '/' . $fileName) + 1);

						// Add current file to archive
						$zip->addFile($filePath, $relativePath);
					}
				}
				$zip->close();
				clearstatcache();
			}
			if (is_dir($upload_directory. '/' . $fileName) && $directory !== $boarddir && $directory != $maindir) {
				arcadeRmdir($directory);
			}
			else {
				$error = sprintf($txt['file_move_failed'], $directory, $upload_directory . '/' . $fileName);
				if (!empty($arcadeModSettings['arcade_log_install_game'])) {
					log_error($error . ' [6]', 'debug');
				}
			}
			if (file_exists($upload_directory . '/' . $romFileName . '.zip')) {
				arcadeRmDir($upload_directory . '/' . $fileName);
				$delete_files = true;
			}
			clearstatcache();
			$filename = !empty($row['game_file']) ? $row['game_file'] : (!empty($row['game_directory']) ?  $row['game_directory'] . '.zip' : $row['internal_name'] . '.zip');
			$ext = mb_substr($filename, strrpos($filename, '.') + 1);
			$filename = mb_substr($filename, 0, -mb_strlen('.' . $ext));
			$files = array_unique(
				array(
					$row['game_file'],
					$row['thumbnail'],
					$row['thumbnail_small'],
					$row['cover_icon'],
					$filename . '.php',
				)
			);
			if ($delete_files)
			{
				foreach ($files as $file) {
					if (!empty($file) && file_exists($maindir . '/' . $file))
						@unlink($maindir . '/' . $file);
				}
			}
		}
		$status[] = array(
			'id' => (!empty($idGame) ? $idGame : 0),
			'name' => (!empty($gameNameDel) ? $gameNameDel : ''),
		);
		deleteRomGame($idGame, $delete_files, $gameNameDel);
	}

	$smcFunc['db_free_result']($request);

	// remove related topics if they exist
	if (!empty($topics))
		removeTopics($topics, false, false);

	if (!empty($status)) {
		$status = array_map('array_filter', $status);
		$status = array_filter($status);
	}
	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		$gameRelatedCache = array(
			'arcade_games_list',
			'arcade_games_suggest',
			'arcade_games_recommended',
			'arcade_games_query_small',
			'arcade_thumbsA0',
			'arcade_thumbsA1',
			'arcade_thumbsA2',
			'arcade_thumbsA3',
			'arcade_cats',
			'arcade_catsB',
			'arcade_games_nocat',
			'arcade_gamecount',
			'arcade_most_played',
			'arcade_games_rating',
			'arcade_games_best',
			'arcade_games_mostactive',
			'arcade_games_longchamps',
			'arcade_games_players',
			'arcade_games_latestScores',
			'arcade_user_counts',
			'arcade_champsA_wins',
			'arcade_champsA_gen',
			'arcade_games_latestA',
			'arcade_new_champsA',
			'arcade_new_gamesA',
			'arcade_popularA',
			'arcade_newestB',
			'arcade_champsC_wins',
			'arcade_champsC_gen',
			'arcade_games_latestC',
			'arcade_newChampsC',
			'arcade_newestC',
			'arcade_popularC',
			'arcade_champsB'
		);
		foreach ($gameRelatedCache as $cache) {
			cache_put_data($cache . '_rom', null, 0);
		}
	}

	return $status;
}

function deleteRomGame($id, $remove_files, $gameNameDel = '')
{
	global $scripturl, $txt, $db_prefix, $user_info, $smcFunc, $arcadeModSettings;

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_game_info
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_favorite
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_rates
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_games
		WHERE id_game = {int:game}',
		array(
			'game' => $id,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:game}',
		array(
			'game' => $id,
		)
	);

	$gameNameDel = !empty($gameNameDel) ? $gameNameDel : $id;
	logAction('arcade_delete_game_rom', array('game' => $gameNameDel));

	return true;
}

function rom_getRawDimensions($type)
{
	// default dimensions for RAW-ROM console types
	// type => array(width, height)
	$type = strtolower($type);
	$allTypes = array(
		'nes' => array(715, 540),
		'snes' => array(720, 540),
		'n64' => array(620, 465),
		'nds' => array(360, 540),
		'gb' => array(600, 540),
		'gba' => array(720, 480),
		'segaSaturn' => array(710, 540),
		'segaCD' => array(710, 540),
		'segaMD' => array(710, 540),
		'segaMS' => array(710, 540),
		'segaGG' => array(710, 540),
		'atari7800' => array(725, 540),
		'atari5200' => array(725, 540),
		'atari2600' => array(725, 540),
		'lynx' => array(725, 540),
		'pce' => array(715, 540),
		'psx' => array(710, 530),
		'psp' => array(960, 540),
		'mame' => array(640, 480),
		'pcfx' => array(715, 540),
		'ngp' => array(565, 540),
		'ws' => array(840, 540),
		'coleco' => array(640, 480),
		'vice_x64sc' => array(640, 480),
		'vice_x128' => array(640, 480),
		'vice_xvic' => array(640, 480),
		'vice_xplus4' => array(715, 540),
		'vice_xpet' => array(715, 540),
	);

	$romKeys = array_keys($allTypes);
	$dims = !empty($type) && in_array($type, $romKeys) ? $allTypes[$type] : array(960, 540);

	return $dims;
}

?>