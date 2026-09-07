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

/*  Instructions for using hooks in other modifications or custom skins & lists  */
function ArcadeHooksGuide()
{
	global $sourcedir, $context, $scripturl, $txt;

	loadLanguage('ArcadeAdminGuide');
	require_once($sourcedir . '/Subs.php');
	$context['arcadeHookGuide'] = parse_bbc($txt['arcadeHooksGuidance']);

	$context['template_layers'][] = 'arcade_guide';
	$context['sub_template'] = 'arcade_guide';
	$context['page_title'] = sprintf($txt['arcade_admin_guide']);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;action=admin;area=arcade;sa=guide;sesc=' . $context['session_id'],
		'name' => $txt['arcade_admin_guide'],
	);
	$context['html_headers'] .= '
	<script type="text/javascript">
		$(document).ready(function() {
			$("code.bbc_code").addClass("arcade_admin_guide_code");
			$("code.bbc_code").wrap(\'<div style="position: relative;display: block;padding: 0.15rem 0rem 0.15rem 0.5rem !important;"></div>\');
		});
	</script>';
	loadTemplate('ArcadeHooksGuide');
	return;
}

function ArcadeCoverAdjustment($list_settings = array())
{
	global $arcadeModSettings;
	foreach ($list_settings as $key => $config) {
		if (!empty($config[0]) && !empty($config[1]) && is_string($config[1])) {
			if (stripos($config[1], 'arcade_coverwidth') !== FALSE || stripos($config[1], 'arcade_coverheight') !== FALSE) {
				$coverSetting = $config[1];
				$arcadeModSettings[$coverSetting] = !empty($arcadeModSettings[$coverSetting]) ? intval($arcadeModSettings[$coverSetting]) : 80;
				$arcadeModSettings[$coverSetting] = $arcadeModSettings[$coverSetting] > 400 ? 400 : ($arcadeModSettings[$coverSetting] < 80 ? 80 : $arcadeModSettings[$coverSetting]);
				$arcadeModSettings[$coverSetting] = abs($arcadeModSettings[$coverSetting]);
			}
		}
	}
}

function arcadePopulateBiosPath()
{
	global $boarddir, $sourcedir, $arcadeModSettings;

	$arcadeBoarddir = str_replace('\\', '/', $boarddir);
	$arcadeBoarddir = rtrim($arcadeBoarddir, '/');
	$arcadeModSettings['romGamesDirectory'] = !empty($arcadeModSettings['romGamesDirectory']) ? $arcadeModSettings['romGamesDirectory'] : $arcadeBoarddir . '/ArcadeRetroArch/roms';
	$biosDirs = array();
	$romPath = str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']);
	$romFilePath = rtrim($romPath, '/');
	$romBiosPath = $romFilePath . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar'];
	clearstatcache();

	if (!empty($arcadeModSettings['arcadeRandomIdVar']) && is_dir($romFilePath) && !is_dir($romBiosPath)) {
		@mkdir($romBiosPath, 0755, true);
		@chmod($romBiosPath, 0755);
		clearstatcache();
	}

	if (is_dir($romFilePath) && !empty($arcadeModSettings['arcadeRandomIdVar'])) {
		$biosDirs = glob($romFilePath . "/0_bios*");
		foreach ($biosDirs as $biosDirX) {
			$biosDir = str_replace('\\', '/', $biosDirX);
			if ($biosDir != $romFilePath . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar']) {
				copyArcadeDirectory($biosDir, $romFilePath . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar']);
				arcadeRmdir($biosDir);
			}
		}
		if (file_exists($sourcedir . '/index.php')) {
			@copy($sourcedir . '/index.php', $romBiosPath . '/index.php');
			clearstatcache();
			@chmod($romBiosPath . '/index.php', 0644);
		}
		clearstatcache();
	}
}

function arcadeEncryptionCipher($string, $newcodes, $action = 'encrypt')
{
	global $arcadeModSettings;

	$bytes = openssl_random_pseudo_bytes(10);
	$randomString = strval(bin2hex($bytes));
	$bytes2 = openssl_random_pseudo_bytes(5);
	$randomString2 = strval(bin2hex($bytes2));
    $encrypt_method = "AES-256-CBC";
    $secret_key = !empty($newcodes) ? strtoupper($newcodes[0]) : (!empty($arcadeModSettings['arcadeRandomIdVar']) ? strtoupper($arcadeModSettings['arcadeRandomIdVar']) : strtoupper($randomString));
    $secret_iv = !empty($newcodes) ? $newcodes[1]: (!empty($arcadeModSettings['arcadeSecretIdVar']) ? $arcadeModSettings['arcadeSecretIdVar'] : $randomString2);
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function ArcadeGetAbsolutePath($path)
{
	global $boarddir;

	$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = [];
	foreach ($parts as $part) {
		if ($part == '.')
			continue;

		if ($part == '..') {
			array_pop($absolutes);
		}
		else {
			$absolutes[] = $part;
		}
	}
	$new = implode(DIRECTORY_SEPARATOR, $absolutes);
	$new = substr($new, 0, 1) ==  substr($boarddir, 0, 1) ? $new : substr($boarddir, 0, 1) . $new;

	return $new;
}

?>