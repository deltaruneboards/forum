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

function ArcadeAdmin()
{
	global $boarddir, $sourcedir, $scripturl, $txt, $arcadeModSettings, $modSettings, $context, $settings, $arcade_server;

	require_once($sourcedir . '/Arcade.php');
	require_once($sourcedir . '/ManageServer.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAncillary.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAdmin.php');

	isAllowedTo('arcade_admin');
	loadArcade('admin', 'arcadesettings');
	$subActions = [
		'main' => ['ArcadeAdminMain'],
		'settings' => ['ArcadeAdminSettings'],
		'discernible' => ['ArcadeAdminDiscernibleSettings'],
		'permission' => ['ArcadeAdminPermission'],
		'guide' => ['ArcadeHooksGuide'],
		'pdl_reports' => ['ArcadePdlReports'],
	];
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_admin_settings'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_general_desc'];
	$context['arcade_smf_version'] = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$_SESSION['arcade_cat_message'] = '';
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));

	// RC3 patch for the moderation log
	$modSettings['moderatelog_enabled'] = !empty($modSettings['modlog_enabled']) ? $modSettings['modlog_enabled'] : 0;
	$arcadeModSettings['pdl_DownMax'] = empty($arcadeModSettings['pdl_DownMax']) ? '0' : $arcadeModSettings['pdl_DownMax'];

	// Device detection depends on 3rd party PHP classes
	if (!class_exists('Mobile_Detect'))
	{
		require_once($boarddir . '/ArcadeSources/Mobile_Detect.php');
	}
	if (!class_exists('Detect'))
	{
		require_once($boarddir . '/ArcadeSources/Detect.php');
	}

	if (class_exists('Mobile_Detect') && class_exists('Detect'))
	{
		// Boolean ~ output is true or false
		$_SESSION['arcade_isMobile'] = Detect::isMobile();

		// String ~ output is "Computer", "Phone" or "Tablet"
		$_SESSION['arcade_deviceType'] = Detect::deviceType();
	}
	$context['html_headers'] .= '
	<script src="' . $settings['default_theme_url'] . '/arcade_scripts/arcadeMobileDetect.js?' . $suffixVersion . '"></script>' . (empty($_SESSION['arcade_isMobile']) ? '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin.css?' . $suffixVersion . '" />' : '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin-mobile.css?' . $suffixVersion . '" />');

	if (!empty($_SESSION['arcade_isMobile']) && $context['arcade_smf_version'] !== 'v2.1')
		$context['html_headers'] .= '
	<meta name="viewport" content="width=device-width, maximum-scale=1.0">';

	/*
	if (!empty($_SESSION['arcade_isMobile']))
		$context['html_headers'] .= '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-mobile.css?' . $suffixVersion . '" />';
	*/

	if (isset($subActions[$_REQUEST['sa']][1]))
		isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$subActions[$_REQUEST['sa']][0]();
}

function ArcadeAdminMain()
{
	global $scripturl, $txt, $arcadeModSettings, $context, $settings;
	$context['arcade_smf_version'] = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['sub_template'] = 'arcade_admin_main';
}

function isCdnLink($url)
{
    $result = false;
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

		$result = $info['http_code'] == '200' ? true : false;
	}
    return $result;
}

function ArcadeAdminDiscernibleSettings($return_config = false)
{
	global $scripturl, $txt, $arcadeModSettings, $context, $settings, $boarddir, $boardurl;
	require_once($boarddir . '/ArcadeSources/Subs-ArcadePlus.php');

	$jsboarddir = str_replace('\\', '\\\\', $boarddir);
	if ($return_config)
		require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	else
		$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_discernible_desc'];

	setupMenuContext();
	$menuButtons = [];

	$menuButtonsRaw = !empty($context['menu_buttons']) ? array_keys($context['menu_buttons']) : ['home'];
	if (($key = array_search('arcade', $menuButtonsRaw)) !== false) {
		unset($menuButtonsRaw[$key]);
	}
	foreach($menuButtonsRaw as $key) {
		$menuButtons[] = $context['menu_buttons'][$key]['title'];
	};

	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_admin_discernible'];
	$arcadeModSettings['gamesPerRowVintage'] = !empty($arcadeModSettings['gamesPerRowVintage']) ? intval($arcadeModSettings['gamesPerRowVintage']) : 4;
	$arcadeModSettings['gamesPerRowVintage'] = $arcadeModSettings['gamesPerRowVintage'] < 4 ? 4 : ($arcadeModSettings['gamesPerRowVintage'] > 7 ? 7 : abs($arcadeModSettings['gamesPerRowVintage']));
	$arcadeModSettings['arcadeList'] = !empty($arcadeModSettings['arcadeList']) ? intval($arcadeModSettings['arcadeList']) : 0;
	$arcadeModSettings['arcadeGamesNameLength'] = !empty($arcadeModSettings['arcadeGamesNameLength']) ? $arcadeModSettings['arcadeGamesNameLength'] : 100;
	$arcadeModSettings['arcadeGamesNameLengthA'] = !empty($arcadeModSettings['arcadeGamesNameLengthA']) ? $arcadeModSettings['arcadeGamesNameLengthA'] : 100;
	$arcadeModSettings['arcadeGamesNameLengthB'] = !empty($arcadeModSettings['arcadeGamesNameLengthB']) ? $arcadeModSettings['arcadeGamesNameLengthB'] : 100;
	list($custom_skin_vars, $customMobileLists, $customMobileSkins, $customArcadeSkins, $customArcadeLists, $customSkins, $customLists, $mobileSkins, $mobileLists, $checkRar) = array(array(), array(), array(), array(), array(), array(), array(), array(), array(), false);
	$php_OS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? $txt['arcadeShowOS'][1] : $txt['arcadeShowOS'][0];
	$arcadeRarText = ("<div style=\"display: inline;\" id=\"rarIndex\">" . sprintf($txt['arcadeDownloadRarNotDetected'], $php_OS) . "</div>");
	$customArcadeSkins = Arcade_integrate_skins('desktop', true, 0);
	$customArcadeLists = Arcade_integrate_lists('desktop', true, 0);
	$customMobileLists = Arcade_integrate_lists('mobile', true, 0);
	$customMobileSkins = Arcade_integrate_skins('mobile', true, 0);
	list($skinConfigs, $listConfigs, $mobileSkinConfigs, $mobileListConfigs, $skin_settings) = array(array(), array(), array(), array(), array());
	list($customSkins, $mobileSkins, $customLists, $mobileLists, $custom_settings) = array(array(), array(), array(), array(), array());

	foreach($customArcadeSkins as $skin)
	{
		if (!empty($skin['id_skin']) && $skin['id_skin'] > 3) {
			if (!empty($skin['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $skin['skin_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $skin['skin_source_file']);
				if (!empty($skin['lang_function']) && function_exists($skin['lang_function'])) {
					$skin['lang_function']();
				}
				if (!empty($skin['admin_function']) && function_exists($skin['admin_function']))
					$skinConfigs[$skin['id_skin']]['admin_vars'] = $skin['admin_function']();
			}
			$customSkins[$skin['id_skin']] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
		}
	}
	foreach($customMobileSkins as $skin)
	{
		if (!empty($skin['id_skin']) && $skin['id_skin'] > 2) {
			if (!empty($skin['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $skin['skin_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $skin['skin_source_file']);
				if (!empty($skin['lang_function']) && function_exists($skin['lang_function'])) {
					$skin['lang_function']();
				}
				if (!empty($skin['admin_function']) && function_exists($skin['admin_function']))
					$mobileSkinConfigs[$skin['id_skin']]['admin_vars'] = $skin['admin_function']();
			}
			$mobileSkins[$skin['id_skin']] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
		}
	}
	foreach($customArcadeLists as $list)
	{
		if (!empty($list['id_list']) && $list['id_list'] > 3) {
			if (!empty($list['list_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $list['list_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $list['list_source_file']);
				if (!empty($list['lang_function']) && function_exists($list['lang_function'])) {
					$list['lang_function']();
				}
				if (!empty($list['admin_function']) && function_exists($list['admin_function']))
					$listConfigs[$list['id_skin']]['admin_vars'] = $list['admin_function']();
			}
			$customLists[$list['id_list']] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
		}

	}
	foreach($customMobileLists as $list)
	{
		if (!empty($list['id_list']) && $list['id_list'] > 2) {
			if (!empty($list['list_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $list['list_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $list['list_source_file']);
				if (!empty($list['lang_function']) && function_exists($list['lang_function'])) {
					$list['lang_function']();
				}
				if (!empty($list['admin_function']) && function_exists($list['admin_function']))
					$mobileListConfigs[$list['id_skin']]['admin_vars'] = $list['admin_function']();
			}
			$mobileLists[$list['id_list']] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
		}
	}

	$discernible_settings = array(
		array('message', 'arcade_title_discernible_settings'),
		'',
			array('select', 'arcadeRomToggle', explode('|', $txt['arcadeRomToggleSelect']), 'subtext' => $txt['arcadeRomToggleText']),
			array('check', 'arcadeDisplayType'),
			array('check', 'arcadeDisplayRomType'),
			array('check', 'arcadeShowIC'),
			array('check', 'arcadeShowOnline'),
			array('select', 'arcadeProfileView', explode('|', $txt['arcadeProfileViewSelect'])),
			array('int', 'arcadeDescriptLength', 'subtext' => $txt['arcadeDescriptLengthText']),
		'',
		array('message', 'arcade_title_navigation_settings'),
		'',
			array('select', 'arcadeButtonPlacementExtremity', explode('|', $txt['arcadeButtonPlacementExtremities'])),
			array('select', 'arcadeButtonPlacement', $menuButtons),
			array('select', 'arcadeButtonSequence', explode('|', $txt['arcadeButtonSequenceValues'])),
			array('int', 'arcadeButtonIndex'),
	);

	$score_settings = array(
		'',
			array('message', 'arcade_title_score_settings'),
		'',
			array('int', 'arcadeMaxScores'),
			array('int', 'scoresPerPage'),
			array('int', 'arcadeCommentLen', 'subtext' => $txt['arcadeCommentLen_subtext']),
			array('select', 'arcadeCheckLevel',
				array($txt['arcade_check_level0'], $txt['arcade_check_level1'], $txt['arcade_check_level2'])
			),
			array('int', 'arcade_decimal', 'subtext' => $txt['arcade_decimal_recommend']),
			array('check', 'arcadeAdjustType'),
			array('check', 'arcadeDisableComments'),
			array('check', 'arcade_shout_guest_score'),
			array('check', 'arcade_shout_member_score'),
			array('check', 'arcade_shout_arena_score'),
			array('check', 'arcade_phpbb3_support_score', 'subtext' => $txt['arcade_phpbb3_support_score_subtext']),
	);

	$arena_settings = array(
		'',
			array('message', 'arcade_title_arena_settings'),
		'',
			array('check', 'arcadeArenaEnabled'),
			array('int', 'matchesPerPage'),
	);

	$icon_settings = array(
		'',
			array('message', 'arcade_title_icon_settings'),
		'',
			array('select', 'arcadeViewCovers', explode('|', $txt['arcadeViewCoversOpt']), 'subtext' => $txt['arcadeViewCoversDescript']),
			array('check', 'arcadeCropCoverArt', 'subtext' => $txt['arcadeCropCoverArtDescript']),
			array('check', 'arcadeCropThumbnails', 'subtext' => $txt['arcadeCropThumbnailsDescript']),
	);

	if (@extension_loaded('imagick')) {
		$icon_settings = array_merge($icon_settings, array(array('check', 'arcadeFilterImagickAdmin', 'subtext' => $txt['arcadeFilterImagickAdminDescript'], 'help' => 'arcadeFilterImagickAdminHelp'), array('select', 'arcadeFilterImagick', explode('|', $txt['arcadeFilterImagickOpt']), 'subtext' => $txt['arcadeFilterImagickDescript'], 'help' => 'arcadeFilterImagickHelp')));
	}

	$category_settings = array(
		'',
			array('message', 'arcade_title_cat_settings'),
		'',
			array('int', 'arcadeCatsCellWidth'),
			array('int', 'arcadeCatsPerLine'),
			array('int', 'arcade_catWidth'),
			array('int', 'arcade_catHeight'),
			array('check', 'arcade_catHideUnused'),
			array('check', 'arcade_showListCat'),
			array('check', 'arcadeCropCatIcons', 'subtext' => $txt['arcadeCropCatIconsDescript']),
	);

	switch($arcadeModSettings['arcadeList']){
		case 1:
			$list_settings = array(
				array('select', 'arcadeTypeQuery',
					array($txt['arcade_type_query0'], $txt['arcade_type_query1'])
				),
				array('select', 'arcadeListSort',
					explode('|', $txt['arcadeListDefaultSort']),
				),
				array('check', 'arcadeListHorizontalDivision'),
				array('int', 'gamesPerPage'),
				array('int', 'arcadeIconBorderRadius2'),
				array('int', 'arcade_thumbWidthRetro', 'subtext' => $txt['arcade_thumbRange']),
				array('int', 'arcade_thumbHeightRetro', 'subtext' => $txt['arcade_thumbRange']),
			);
			$coverArt = array(
				array('check', 'arcade_coverEnableRetro', 'subtext' => $txt['arcade_coverEnable']),
				array('check', 'arcadeResizeCoverArtRetro', 'subtext' => $txt['arcadeResizeCoverArtDescript']),
				array('check', 'arcade_coverFullWidthRetro', 'subtext' => $txt['arcade_coverFullWidth']),
				array('int', 'arcade_coverWidthRetro', 'subtext' => $txt['arcade_coverRange']),
				array('int', 'arcade_coverHeightRetro', 'subtext' => $txt['arcade_coverRange']),
			);
			$list_settings = !empty($arcadeModSettings['arcadeViewCovers']) ? array_merge($list_settings, $coverArt) : $list_settings;
			break;
		case 2:
			$list_settings = array(
				array('select', 'arcadeTypeQuery',
					array($txt['arcade_type_query0'], $txt['arcade_type_query1'])
				),
				array('select', 'arcadeListSort',
					explode('|', $txt['arcadeListDefaultSort']),
				),
				array('check', 'arcadeListHorizontalDivision'),
				array('int', 'gamesPerRowVintage', 'step' => 1, 'min' => 4, 'max' => 7),
				array('int', 'gamesPerPage'),
				array('int', 'arcadeIconBorderRadius1'),
				array('int', 'arcade_thumbWidthVintage', 'subtext' => $txt['arcade_thumbRange']),
				array('int', 'arcade_thumbHeightVintage', 'subtext' => $txt['arcade_thumbRange']),
			);
			$coverArt = array(
				array('check', 'arcade_coverEnableVintage', 'subtext' => $txt['arcade_coverEnable']),
				array('check', 'arcadeResizeCoverArtVintage', 'subtext' => $txt['arcadeResizeCoverArtDescript']),
				array('check', 'arcade_coverFullWidthVintage', 'subtext' => $txt['arcade_coverFullWidth']),
				array('int', 'arcade_coverWidthVintage', 'subtext' => $txt['arcade_coverRange']),
				array('int', 'arcade_coverHeightVintage', 'subtext' => $txt['arcade_coverRange']),
			);
			$list_settings = !empty($arcadeModSettings['arcadeViewCovers']) ? array_merge($list_settings, $coverArt) : $list_settings;
			break;
		default:
			$list_settings = array(
				array('select', 'arcadeTypeQuery',
					array($txt['arcade_type_query0'], $txt['arcade_type_query0'])
				),
				array('select', 'arcadeListSort',
					explode('|', $txt['arcadeListDefaultSort']),
				),
				array('select', 'arcadeListGenericExtraBg',
					array_merge(array($txt['arcade_list_generic0'], $txt['arcade_list_generic1'], $txt['arcade_list_generic2'], $txt['arcade_list_generic3']))),
				array('check', 'arcadeListGenericExtraBorder'),
				array('check', 'arcadeListHorizontalDivision'),
				array('int', 'gamesPerPage'),
				array('int', 'arcadeIconBorderRadius0'),
				array('int', 'arcade_thumbWidthGeneric', 'subtext' => $txt['arcade_thumbRange']),
				array('int', 'arcade_thumbHeightGeneric', 'subtext' => $txt['arcade_thumbRange']),
			);
			$coverArt = array(
				array('check', 'arcade_coverEnableGeneric', 'subtext' => $txt['arcade_coverEnable']),
				array('check', 'arcadeResizeCoverArtGeneric', 'subtext' => $txt['arcadeResizeCoverArtDescript']),
				array('check', 'arcade_coverFullWidthGeneric', 'subtext' => $txt['arcade_coverFullWidth']),
				array('int', 'arcade_coverWidthGeneric', 'subtext' => $txt['arcade_coverRange']),
				array('int', 'arcade_coverHeightGeneric', 'subtext' => $txt['arcade_coverRange']),
			);
			$list_settings = !empty($arcadeModSettings['arcadeViewCovers']) ? array_merge($list_settings, $coverArt) : $list_settings;
	}

	$responsive_settings = array(
		'',
			array('message', 'arcade_title_mobile_settings'),
		'',
			array('select', 'arcadeSkinMobile',
				array_merge(array($txt['arcade_skin_mobile_generic'], $txt['arcade_skin_mobile0']), $mobileSkins)
			),
			array('select', 'arcadeListMobile',
				array_merge(array($txt['arcade_list_mobile_generic'], $txt['arcade_list_mobile0']), $mobileLists)
			),
	);

	$list_select = array(
		'',
			array('message', 'arcade_title_specific_list'),
		'',
			array('select', 'arcadeList',
				array_merge(array($txt['arcade_list0'], $txt['arcade_list1'], $txt['arcade_list2']), $customLists)
			),
		'',
	);

	$skin_select = array(
		'',
			array('message', 'arcade_title_specific_skin'),
		'',
			array('select', 'arcadeSkin',
				array_merge(array($txt['arcade_default'], $txt['arcade_skin_c'], $txt['arcade_skin_b']), $customSkins)
			),
		'',
	);

	// add possible custom mobile & regular skin/list admin settings
	$custom_settings = !empty($mobileSkinConfigs) ? array_merge($custom_settings, array(''), $mobileSkinConfigs) : $custom_settings;
	$custom_settings = !empty($mobileListConfigs) ? array_merge($custom_settings, array(''), $mobileListConfigs) : $custom_settings;
	$custom_settings = !empty($listConfigs) ? array_merge($custom_settings, array(''), $listConfigs) : $custom_settings;
	$custom_settings = !empty($skinConfigs) ? array_merge($custom_settings, array(''), $skinConfigs) : $custom_settings;
	$custom_settings = array_merge($custom_settings, $skin_select);

	if(isset($arcadeModSettings['arcadeSkin']) && $arcadeModSettings['arcadeSkin'] == 1)
	{
		// Skin C (Enterprise-C)
		$c_skin_vars = array(
			array('text', 'arcade_shoutboxC_name'),
			array('check', 'arcade_shoutboxC'),
			array('check', 'arcade_alternateBGC'),
			array('int', 'arcade_show_shoutsC'),
			array('int', 'arcade_shout_intervalC', 'subtext' => $txt['arcade_shout_interval_recommendC']),
			array('int', 'arcade_shout_heightC', 'subtext' => $txt['arcade_shout_height_unitsC']),
			array('check', 'arcadeDropCat'),
			array('check', 'arcadeDailyGameScoresC'),
			array('int', 'skin_latest_scores'),
			array('int', 'skin_latest_champs'),
			array('int', 'skin_latest_games'),
			array('int', 'skin_most_popular'),
			array('int', 'skin_latest_rom_games'),
			array('int', 'skin_most_rom_popular'),
			array('int', 'skin_avatar_size_width', 'subtext' => $txt['avsize_recommend']),
			array('int', 'skin_avatar_size_height', 'subtext' => $txt['avsize_recommend']),
			array('int', 'arcadeGamesNameLength'),
			'',
		);
		$skin_settings = array_merge($skin_settings, $c_skin_vars);
	}
	elseif(isset($arcadeModSettings['arcadeSkin']) && $arcadeModSettings['arcadeSkin'] == 2)
	{
		// Skin B (Defiant)
		$b_skin_vars = array(
			array('check', 'arcadeTabs'),
			array('int', 'arcade_shout_interval', 'subtext' => $txt['arcade_shout_interval_recommend']),
			array('int', 'arcade_shout_widthB', 'step' => 1, 'min' => 40, 'max' => 100, 'subtext' => $txt['arcade_shout_width_percent']),
			array('int', 'skin_best_playersB'),
			array('int', 'skin_latest_gamesB'),
			array('int', 'skin_latest_champsB'),
			array('int', 'skin_most_popularB'),
			array('int', 'skin_longest_champsB'),
			array('int', 'skin_avatar_sizeb_width', 'subtext' => $txt['avsizeB_recommend']),
			array('int', 'skin_avatar_sizeb_height', 'subtext' => $txt['avsizeB_recommend']),
			array('int', 'arcadeGamesNameLengthB'),
			'',
		);

		$skin_settings = array_merge($skin_settings, $b_skin_vars);
	}
	elseif (isset($arcadeModSettings['arcadeSkin']) && !empty($arcadeModSettings['arcadeSkin']) && !empty($customArcadeSkins[$arcadeModSettings['arcadeSkin']]))
	{
		foreach ($customArcadeSkins as $arcadeSkin) {
			if (!empty($arcadeSkin['skin_source_file']) && !empty($arcadeSkin['admin_function']) && file_exists($boarddir . '/ArcadeSources/' . $arcadeSkin['skin_source_file']))
			{
				require_once($boarddir . '/ArcadeSources/' . $arcadeSkin['skin_source_file']);
				if (function_exists($arcadeSkin['admin_function']))
				{
					$custom_skin_vars = $arcadeSkin['admin_function']();
					$skin_settings = array_merge($skin_settings, $custom_skin_vars);
				}
			}
		}
	}
	else
	{
		$default_skin_vars = array(
			array('check', 'arcadeDropCatClassic'),
			array('int', 'arcadeGamesNameLength'),
		);

		$skin_settings = array_merge($skin_settings, $default_skin_vars);
	}

	foreach (submitSystemInfo('*') as $id => $system)
	{
		if (!isset($system['get_settings']))
			continue;

		// Load file
		require_once($boarddir . '/ArcadeSources/' . $system['file']);

		// Add settings to page
		$skin_settings[] = $system['name'];
		$skin_settings = array_merge($skin_settings, $system['get_settings']());
	}

	$emulatorJS_config_vars = array(
		'',
			array('message', 'arcade_title_emulatorjs_gamesettings'),
		'',
			array('check', 'arcade_emulatorjs_settings'),
			array('check', 'arcade_emulatorjs_fullscreen'),
			array('check', 'arcade_emulatorjs_gamepad'),
			array('check', 'arcade_emulatorjs_savestate'),
			array('check', 'arcade_emulatorjs_loadstate'),
			array('check', 'arcade_emulatorjs_savefiles'),
			array('check', 'arcade_emulatorjs_loadfiles'),
			array('check', 'arcade_emulatorjs_screenshot'),
			array('check', 'arcade_emulatorjs_record'),
			array('check', 'arcade_emulatorjs_qsave'),
			array('check', 'arcade_emulatorjs_qload'),
			array('check', 'arcade_emulatorjs_playpause'),
			array('check', 'arcade_emulatorjs_restart'),
			array('check', 'arcade_emulatorjs_cheat'),
			array('check', 'arcade_emulatorjs_cache'),
	);

	ArcadeCoverAdjustment($list_settings);
	$config_vars = array_merge($discernible_settings, $score_settings, $icon_settings, $category_settings, $list_select, $list_settings, $responsive_settings, $custom_settings, $skin_settings, $emulatorJS_config_vars);

	$limitFunc = '
		function arcadeAdminEvents() {
			document.getElementById("arcadeButtonPlacementExtremity").onchange = function(){arcadeNavigationButton();};
			document.getElementById("arcadeSkin").onchange = function(){document.getElementById("arcadeSkin").form.submit(); return false;};
			document.getElementById("arcadeList").onchange = function(){document.getElementById("arcadeList").form.submit(); return false;};
		}
		function arcadeChangeVal(mychange, myval) {
			document.getElementById(mychange).value = myval;
			return false;
		}
		function arcadeShoutboxSettings() {
			if (document.getElementById("arcade_shoutboxC_name")) {
				document.getElementById("arcade_shoutboxC_name").onkeydown = function() {
					var inputId = document.getElementById("arcade_shoutboxC_name");
					if(inputId.value.length > 25) {
						inputId.value = inputId.value.substr(0, 25);
					}
				};
			}
			if (document.getElementById("arcade_shout_heightC")) {
				if (document.getElementById("arcade_shout_heightC").value < 1) {
					document.getElementById("arcade_shout_heightC").value = 40;
				}
			}
			if (document.getElementById("arcade_phpbb3_support_score")) {
				document.getElementById("arcade_phpbb3_support_score").onclick = function() {
					if (document.getElementById("arcade_phpbb3_support_score").checked) {
						alert("' . $txt['arcade_phpbb3_support_score_alert'] . '");
					}
				}
			}
		}
		function arcadeListenSettings() {
			$("#arcadeFilterImagickAdmin").on( "click", function() {
				if (document.getElementById("arcadeFilterImagickAdmin").checked) {
					var arcadeFilterImagickConfirm = confirm("' . $txt['arcade_adjust_FilterImagick_info_warn'] . '");
					if (!arcadeFilterImagickConfirm) {
						document.getElementById("arcadeFilterImagickAdmin").checked = false;
						$("label[for=arcadeFilterImagickAdmin]").css("font-weight", "initial");
						$("label[for=arcadeFilterImagickAdmin]").css("font-style", "initial");
					}
				}
			});
			$("#arcadeFilterImagick").on( "change", function() {
				if ($(this).val() != 0) {
					var arcadeFilterImagickTempConfirm = confirm("' . $txt['arcade_adjust_FilterImagickTemp_info_warn'] . '");
					if (!arcadeFilterImagickTempConfirm) {
						$(this).val(0);
						$("label[for=arcadeFilterImagick]").css("font-weight", "initial");
						$("label[for=arcadeFilterImagick]").css("font-style", "initial");
					}
				}
			});
			if (document.getElementById("arcade_adjust_desc_info")) {
				document.getElementById("arcade_adjust_desc_info").onclick = function() {
					if (document.getElementById("arcade_adjust_desc_info").checked) {
						var arcadeDescConfirm = confirm("' . $txt['arcade_adjust_desc_info_warn'] . '");
						if (!arcadeDescConfirm) {
							document.getElementById("arcade_adjust_desc_info").checked = false;
							$("label[for=arcade_adjust_desc_info]").css("font-weight", "initial");
							$("label[for=arcade_adjust_desc_info]").css("font-style", "initial");
						}
					}
				}
			}
			if (document.getElementById("arcadeArenaEnabled")) {
				document.getElementById("arcadeArenaEnabled").disabled = true;
			} ' . (!empty($arcadeDeepLText) ? '
			if(document.getElementById("arcadeDeepLText")) {
				document.getElementById("arcadeDeepLText").innerHTML = "' . $arcadeDeepLText . '";
			}' : '') . '
			for (let i = 0; i < coverArtArray.length; ++i) {
				if ($("#arcade_coverFullWidth" + coverArtArray[i]).prop("checked")) {
					$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", true);
				}
				else {
					$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", false);
				}
				if ($("#arcadeResizeCoverArt" + coverArtArray[i]).prop("checked")) {
					$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", true);
					$("#arcade_coverHeight" + coverArtArray[i]).prop("disabled", true);
					$("#arcade_coverFullWidth" + coverArtArray[i]).prop("disabled", true);
				}
				else {
					$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", false);
					$("#arcade_coverHeight" + coverArtArray[i]).prop("disabled", false);
					$("#arcade_coverFullWidth" + coverArtArray[i]).prop("disabled", false);
					if ($("#arcade_coverFullWidth" + coverArtArray[i]).prop("checked")) {
						$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", true);
					}
					else {
						$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", false);
					}
				}
			}
		}
		function arcadeConsoleDebugMode() {
			$("#setting_arcade_emulatorjs_settings").parent().parent().prepend(\'<dt><label for="ok">' . $txt['arcade_emulatorjs_console_debug'] . '</label></dt><dd><span id="ok">' . $txt['arcade_emulatorjs_console_debug_msg'] . '</span></dd>\');
			$("[id^=arcade_emulatorjs_]").css("width", "0rem");
			$("[id^=arcade_emulatorjs_]").css("height", "0rem");
			$("[id^=arcade_emulatorjs_]").css("position", "absolute");
			$("[id^=arcade_emulatorjs_]").css("visibility", "hidden");
			$("[id^=arcade_emulatorjs_]").each(function( index ) {
				let arcadeCheckBox = $(this).prop("id");
				$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
				$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
			});
			$("[id^=arcade_emulatorjs_]").parent().append(\'<input class="arcade_admin_checkbox" title="' . $txt['arcade_emulatorjs_disable_msg'] . '" style="cursor: pointer;" type="checkbox" class="checkbox" checked disabled />\');
		}
		function arcadeNavigationButton() {
			var arcadeButtonSetting = $("#arcadeButtonPlacementExtremity").val();
			if (arcadeButtonSetting > 0 && arcadeButtonSetting < 3) {
				$("#arcadeButtonPlacement").prop("disabled", true);
				$("#arcadeButtonSequence").prop("disabled", true);
				$("#arcadeButtonIndex").prop("disabled", true);
			}
			else if (arcadeButtonSetting == 3) {
				$("#arcadeButtonPlacement").prop("disabled", true);
				$("#arcadeButtonSequence").prop("disabled", true);
				$("#arcadeButtonIndex").prop("disabled", false);

			}
			else {
				$("#arcadeButtonPlacement").prop("disabled", false);
				$("#arcadeButtonSequence").prop("disabled", false);
				$("#arcadeButtonIndex").prop("disabled", true);
			}
		}';

	$context['html_headers'] .= '
	<script>
		let coverArtArray = ["Retro", "Generic", "Vintage"];' . $limitFunc . '
		$(document).ready(function() {
			arcadeNavigationButton();
			arcadeAdminEvents();
			$("input:checkbox").addClass("arcade_admin_checkbox");
			$(".arcade_admin_checkbox").each(function( index ) {
				if ($(this).prop("checked")) {
					let arcadeCheckBox = $(this).prop("id");
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
			});
			$(".arcade_admin_checkbox").click(function(){
				let arcadeCheckBox = $(this).prop("id");
				if ($(this).prop("checked")) {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
				else {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "initial");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "initial");
				}
			});
			for (let i = 0; i < coverArtArray.length; ++i) {
				$("#arcade_coverFullWidth" + coverArtArray[i]).click(function(){
					if ($(this).prop("checked")) {
						$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", true);

					}
					else {
						$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", false);
					}
				});
				$("#arcadeResizeCoverArt" + coverArtArray[i]).click(function(){
					if ($("#arcadeResizeCoverArt" + coverArtArray[i]).prop("checked")) {
						$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", true);
						$("#arcade_coverHeight" + coverArtArray[i]).prop("disabled", true);
						$("#arcade_coverFullWidth" + coverArtArray[i]).prop("disabled", true);
					}
					else {
						$("#arcade_coverWidth" + coverArtArray[i]).prop("disabled", false);
						$("#arcade_coverHeight" + coverArtArray[i]).prop("disabled", false);
						$("#arcade_coverFullWidth" + coverArtArray[i]).prop("disabled", false);
					}
				});
			}
			$("#arcadeListMobile, #arcadeSkinMobile").css({"min-width":"50%"});
			arcadeListenSettings();
			arcadeShoutboxSettings();' . (!empty($arcadeModSettings['arcade_log_emulatorjs_console']) ? '
			arcadeConsoleDebugMode();' : '') . '
		});
	</script>';

	if ($return_config) {
		return $config_vars;
	}

	if (isset($_GET['save']))
	{
		// the arena has been temporarily disabled in v2.66 pending a revamp in v2.7+
		$_POST['arcadeArenaEnabled'] = 0;
		$arcadeModSettings['arcadeDownloadWinRarDir'] = empty($checkWinRar) ? '' : $arcadeModSettings['arcadeDownloadWinRarDir'];
		$arcadeJsInsertDefault = !empty($arcadeModSettings['arcadeJsInsertDefault']) && in_array($arcadeModSettings['arcadeJsInsertDefault'], array(0, 1, 2)) ? $arcadeModSettings['arcadeJsInsertDefault'] : 2;
		$_SESSION['arcadeFirstLoad'] = '';
		$arcadeModSettings['arcadeDownloadUnixRarDir'] = '';
		checkSession();
		$maxScores = !empty($arcadeModSettings['arcadeMaxScores']) ? $arcadeModSettings['arcadeMaxScores'] : 0;
		for ($q=0;$q<3;$q++) {
			if (isset($_POST['arcadeIconBorderRadius' . $q])) {
				$_POST['arcadeIconBorderRadius' . $q] = preg_replace("/[^0-9.]/", "", $_POST['arcadeIconBorderRadius' . $q]);
				$_POST['arcadeIconBorderRadius' . $q] = abs(floatval($_POST['arcadeIconBorderRadius' . $q]));
				if ($_POST['arcadeIconBorderRadius' . $q] < 0 || $_POST['arcadeIconBorderRadius' . $q] > 100)
					unset($_POST['arcadeIconBorderRadius' . $q]);
			}
		}
		if (isset($_POST['arcadebuttonPlacement'])) {
			$_POST['arcadebuttonPlacement'] = intval($_POST['arcadebuttonPlacement']);
		}
		if (isset($_POST['arcade_shout_widthB']))
		{
			$_POST['arcade_shout_widthB'] = intval($_POST['arcade_shout_widthB']);
			$_POST['arcade_shout_widthB'] = $_POST['arcade_shout_widthB'] < 40 || $_POST['arcade_shout_widthB'] > 100 ? 90 : $_POST['arcade_shout_widthB'];
		}
		if (isset($_POST['arcadeGamesNameLength']))
		{
			$_POST['arcadeGamesNameLength'] = preg_replace("/[^0-9.]/", "", $_POST['arcadeGamesNameLength']);
			$_POST['arcadeGamesNameLength'] = abs(intval($_POST['arcadeGamesNameLength']));
			$_POST['arcadeGamesNameLength'] = $_POST['arcadeGamesNameLength'] < 1 ? 1 : ($_POST['arcadeGamesNameLength'] > 200 ? 200 : $_POST['arcadeGamesNameLength']);
		}

		if (isset($_POST['arcadeGamesNameLengthA']))
		{
			$_POST['arcadeGamesNameLengthA'] = preg_replace("/[^0-9.]/", "", $_POST['arcadeGamesNameLengthA']);
			$_POST['arcadeGamesNameLengthA'] = abs(intval($_POST['arcadeGamesNameLengthA']));
			$_POST['arcadeGamesNameLengthA'] = $_POST['arcadeGamesNameLengthA'] < 1 ? 1 : ($_POST['arcadeGamesNameLengthA'] > 200 ? 200 : $_POST['arcadeGamesNameLengthA']);
		}

		if (isset($_POST['arcadeGamesNameLengthB']))
		{
			$_POST['arcadeGamesNameLengthB'] = preg_replace("/[^0-9.]/", "", $_POST['arcadeGamesNameLengthB']);
			$_POST['arcadeGamesNameLengthB'] = abs(intval($_POST['arcadeGamesNameLengthB']));
			$_POST['arcadeGamesNameLengthB'] = $_POST['arcadeGamesNameLengthB'] < 1 ? 1 : ($_POST['arcadeGamesNameLengthB'] > 200 ? 200 : $_POST['arcadeGamesNameLengthB']);
		}
		if (isset($_POST['arcadeDescriptLength']))
		{
			$_POST['arcadeDescriptLength'] = preg_replace("/[^0-9.]/", "", $_POST['arcadeDescriptLength']);
			$_POST['arcadeDescriptLength'] = abs(intval($_POST['arcadeDescriptLength']));
			$_POST['arcadeDescriptLength'] = $_POST['arcadeDescriptLength'] < 1 || $_POST['arcadeDescriptLength'] > 2000 ? 2000 : $_POST['arcadeDescriptLength'];
		}
		if (isset($_POST['arcade_shout_interval']))
		{
			$_POST['arcade_shout_interval'] = preg_replace("/[^0-9.]/", "", $_POST['arcade_shout_interval']);
			$_POST['arcade_shout_interval'] = abs(intval($_POST['arcade_shout_interval']));
			$_POST['arcade_shout_interval'] = $_POST['arcade_shout_interval'] > 0 && $_POST['arcade_shout_interval'] < 10 ? 10 : $_POST['arcade_shout_interval'];
			$_POST['arcade_shout_interval'] = $_POST['arcade_shout_interval'] > 40 ? 40 : $_POST['arcade_shout_interval'];
		}
		if (isset($_POST['arcadeJsInsertDefault']) && $arcadeJsInsertDefault != $_POST['arcadeJsInsertDefault'])
		{
			$_POST['arcadeJsInsertDefault'] = preg_replace("/[^0-9.]/", "", $_POST['arcadeJsInsertDefault']);
			$_POST['arcadeJsInsertDefault'] = abs(intval($_POST['arcadeJsInsertDefault']));
			$_POST['arcadeJsInsertDefault'] = in_array($_POST['arcadeJsInsertDefault'], array(0, 1, 2)) ? $_POST['arcadeJsInsertDefault'] : 2;
			$newStructure = array('name' => 'js_insertion', 'type' => 'tinyint', 'size' => 1, 'null' => false, 'default' => $_POST['arcadeJsInsertDefault'], 'unsigned' => true, 'auto' => false);
			ArcadeAdminChangeTableStructure('arcade_games', 'js_insertion', $newStructure);
		}
		// thumbnail dimension range
		foreach (array('arcade_thumbWidthRetro', 'arcade_thumbHeightRetro', 'arcade_thumbWidthGeneric', 'arcade_thumbHeightGeneric', 'arcade_thumbWidthVintage', 'arcade_thumbHeightVintage') as $listSetting) {
			if (isset($_POST[$listSetting]) && (int)$_POST[$listSetting] < 20)
				$_POST[$listSetting] = 20;
			if (isset($_POST[$listSetting]) && (int)$_POST[$listSetting] > 120)
				$_POST[$listSetting] = 120;
			$_POST[$listSetting] = !empty($_POST[$listSetting]) ? abs($_POST[$listSetting]) : 50;
		}
		// cover art dimension range
		foreach (array('arcade_coverWidth', 'arcade_coverHeight') as $coverSetting) {
			if (isset($_POST[$coverSetting]) && (int)$_POST[$coverSetting] < 80)
				$_POST[$coverSetting] = 80;
			if (isset($_POST[$coverSetting]) && (int)$_POST[$coverSetting] > 400)
				$_POST[$coverSetting] = 400;
			$_POST[$coverSetting] = !empty($_POST[$coverSetting]) ? abs($_POST[$coverSetting]) : 80;
		}
		arcadeSaveDBSettings($config_vars);
		writeLog();
		if (!empty($arcadeModSettings['arcadeMaxScores']) && (empty($maxScores) || $maxScores > $arcadeModSettings['arcadeMaxScores']))
			redirectexit('action=admin;area=arcademaintenance;sa=fixScores');

		redirectexit('action=admin;area=arcade;sa=discernible');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=discernible;save';
	$context['settings_title'] = $txt['arcade_admin_visual'];
	$context['sub_template'] = 'show_settings';

	arcadePrepareDBSettingContext($config_vars);
}

function ArcadeAdminSettings($return_config = false)
{
	global $scripturl, $txt, $arcadeModSettings, $modSettings, $context, $settings, $boarddir, $boardurl;
	require_once($boarddir . '/ArcadeSources/Subs-ArcadePlus.php');

	$arcadeMaxPacket = arcadeMaxPacket();
	$jsboarddir = str_replace('\\', '\\\\', $boarddir);
	$translationAPI = empty($arcadeModSettings['arcadeTranslationAPI']) ? 0 : (int)$arcadeModSettings['arcadeTranslationAPI'];
	$emulatorAvailable = explode('|', $txt['arcadeCDN_Availability']);
	$emulatorJS_cdnCheck = isCdnLink('https://cdn.emulatorjs.org/latest/data') ? $emulatorAvailable[1] : $emulatorAvailable[0];
	$ruffle_cdnCheck = isCdnLink('https://unpkg.com/@ruffle-rs/ruffle') ? $emulatorAvailable[1] : $emulatorAvailable[0];
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$arcadeLibVal = !empty($arcadeModSettings['arcadeRetroLibraryCode']) ? $arcadeModSettings['arcadeRetroLibraryCode'] : '';
	list($general_settings, $cache_settings, $translation_settings, $checkRar) = array(array(), array(), array(), false);

	if ($return_config)
		require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	else
		$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_settings_desc'];

	if (!empty($arcadeModSettings['arcadeRandomIdVar']) && !is_dir($romGamesDirectory . '/0_bios_' . $arcadeModSettings['arcadeRandomIdVar'])) {
		arcadePopulateBiosPath();
	}

	$arcadeModSettings['arcadeAzureSubKey'] = !empty($arcadeModSettings['arcadeAzureSubKey']) ? $arcadeModSettings['arcadeAzureSubKey'] : '';
	$arcadeModSettings['arcadeCatsCellWidth'] = !empty($arcadeModSettings['arcadeCatsCellWidth']) ? $arcadeModSettings['arcadeCatsCellWidth'] : 20;
	$arcadeModSettings['arcadeCatsPerLine'] = !empty($arcadeModSettings['arcadeCatsPerLine']) ? $arcadeModSettings['arcadeCatsPerLine'] : 5;
	if (!empty($arcadeModSettings['arcadeEnablePosting']))
	{
		$boardExists = !empty($arcadeModSettings['gamesBoard']) ? checkBoardExistsArcadeAdmin($arcadeModSettings['gamesBoard']) : false;
		$userExists = !empty($arcadeModSettings['arcadePosterid']) ? checkUserExistsArcadeAdmin($arcadeModSettings['arcadePosterid']) : false;

		$boardCheck = $boardExists ? $txt['arcade_adm_board_do_exist'] : $txt['arcade_adm_board_not_exist'];
		$userCheck = $userExists ? $txt['arcade_adm_user_do_exist'] : $txt['arcade_adm_user_not_exist'];
	}
	else
		list($boardCheck, $userCheck) = array($txt['arcade_adm_disabled'], $txt['arcade_adm_disabled']);

	$general_settings = array(
			array('message', 'arcade_title_general_enable'),
		'',
			array('check', 'arcadeEnabled'),
			array('check', 'arcadeRetroArchEnabled'),
			array('check', 'arcadeHideDisabled'),
			array('check', 'arcadeEnableFavorites'),
			array('check', 'arcadeEnableRatings'),
			array('check', 'arcadeGameTesting', 'subtext' => $txt['arcadeGameTestingText']),
			array('check', 'arcade_contentSecurityPolicy'),
			array('select', 'arcadeFilesMax', explode('|', $txt['arcadeFilesMaxOptions']), 'subtext' => $txt['arcadeFilesMaxText']),
			array('check', 'arcadeRetroLibraryCodeEnable', 'help' => 'arcadeRetroLibraryCodeEnableHelp'),
			array('check', 'arcadeNewRandomId', 'subtext' => sprintf($txt['arcadeRandomId'], $arcadeModSettings['arcadeRandomIdVar'])),
			array('select', 'arcade_flash_emulator', explode('|', $txt['arcade_flash_emulator_opt']), 'subtext' => $ruffle_cdnCheck . '<span style="display: block;">' . $txt['arcadeFlashEmulator'] . '</span>'),
			array('select', 'arcade_rom_emulator', explode('|', $txt['arcade_rom_emulator_opt']), 'subtext' => $emulatorJS_cdnCheck . '<span style="display: block;">' . $txt['arcadeRomEmulator'] . '</span>'),
			array('select', 'arcadeBehaviorCDN', explode('|', $txt['arcadeBehaviorOptionsCDN']), 'subtext' => $txt['arcadeBehaviorCDNText'], 'help' => 'arcadeBehaviorCDNTextHelp'),
	);
	if (class_exists('Shop\Integration\Addons\Arcade\Arcade') && !empty($modSettings['Shop_enable_shop'])) {
		$general_settings = array_merge($general_settings, array(array('check', 'arcadeEnableStShopPatch', 'help' => 'arcadeEnableStShopPatchHelp')));
	}
	$cache_settings = array(
		'',
			array('message', 'arcade_title_cache_settings'),
			'',
			array('check', 'arcade_cache_enable'),
			array('check', 'arcadeGamecacheUpdate'),
			array('int', 'arcade_cache_time', 'step' => 1, 'min' => 1, 'max' => 1800, 'subtext' => $txt['arcade_cache_time_msg']),
			array('int', 'arcade_suggest_time', 'step' => 1, 'min' => 1, 'max' => 30, 'subtext' => $txt['arcade_suggest_time_msg']),
			array('text', 'arcadeCookieEncryptionCipher', 'subtext' => $txt['arcadeCookieEncryptionCipherTxt']),
	);

	// translation API options
	$translation_settings = array(
		'',
		array('message', 'arcade_title_api_translate_settings'),
		'',
		array('select', 'arcadeTranslationAPI', explode('|', $txt['arcadeTranslationTypesAPI']), 'subtext' => $txt['arcadeTranslationAPIText']),
		array('check', 'arcade_adjust_desc_admin'),
		array('check', 'arcade_adjust_desc_info', 'subtext' => $txt['arcade_adjust_desc_info_msg']),
	);

	switch($translationAPI) {
		case 1:
			$arcadeDeepLText = '';
			if (!empty($arcadeModSettings['arcadeDeepLSubKey'])) {
				$arcadeDeepLText = $txt['arcadeDeepLinvalid'];
				$deepL_limit = arcade_DeepL_translate_api_limit($arcadeModSettings['arcadeDeepLSubKey']);
				if (!empty($deepL_limit['character_count']) || !empty($deepL_limit['character_limit'])) {
					$arcadeDeepLText = sprintf($txt['arcadeDeepLlimits'], $deepL_limit['character_count'], $deepL_limit['character_limit']);
				}
			}
			$configAzure = array(
				'',
				array('message', 'arcade_title_deepL_translate_settings'),
				'',
				array('message', 'arcadeDeepL'),
				array('text', 'arcadeDeepLEndpoint', 'subtext' => sprintf($txt['arcadeDeepLEndpointMsg'], (!empty($arcadeModSettings['arcadeDeepLEndpoint']) ? $arcadeModSettings['arcadeDeepLEndpoint'] : ''))),
				array('text', 'arcadeDeepLPath', 'subtext' => sprintf($txt['arcadeDeepLPathMsg'], (!empty($arcadeModSettings['arcadeDeepLPath']) ? $arcadeModSettings['arcadeDeepLPath'] : ''))),
				array('text', 'arcadeDeepLSubKey',  'subtext' => sprintf($txt['arcadeDeepLSubKeyMsg'], (!empty($arcadeModSettings['arcadeDeepLSubKey']) ? $arcadeModSettings['arcadeDeepLSubKey'] : ''))),
				array('select', 'arcadeDeepLPT', explode('|', $txt['arcadeOptDeepLPT']), 'subtext' => $txt['arcadeDeepLPTText']),
				array('select', 'arcadeDeepLEN', explode('|', $txt['arcadeOptDeepLEN']), 'subtext' => $txt['arcadeDeepLENText']),
				array('check', 'arcadeDeepLAutoFill'),
				array('message', 'arcadeDeepLText'),
			);
			$translation_settings = array_merge($translation_settings, $configAzure);
			break;
		default:
			$configAzure = array(
				'',
				array('message', 'arcade_title_azure_translate_settings'),
				'',
				array('message', 'arcadeAzure'),
				array('text', 'arcadeAzureEndpoint', 'subtext' => sprintf($txt['arcadeAzureEndpointMsg'], (!empty($arcadeModSettings['arcadeAzureEndpoint']) ? $arcadeModSettings['arcadeAzureEndpoint'] : ''))),
				array('text', 'arcadeAzurePath', 'subtext' => sprintf($txt['arcadeAzurePathMsg'], (!empty($arcadeModSettings['arcadeAzurePath']) ? $arcadeModSettings['arcadeAzurePath'] : ''))),
				array('text', 'arcadeAzureRegionCode', 'subtext' => $txt['arcadeAzureRegionCodeMsg']),
				array('text', 'arcadeAzureSubKey',  'subtext' => sprintf($txt['arcadeAzureSubKeyMsg'], (!empty($arcadeModSettings['arcadeAzureSubKey']) ? $arcadeModSettings['arcadeAzureSubKey'] : ''))),
				array('text', 'arcadeAzureSubKey2',  'subtext' => sprintf($txt['arcadeAzureSubKeyMsg2'], (!empty($arcadeModSettings['arcadeAzureSubKey2']) ? $arcadeModSettings['arcadeAzureSubKey2'] : ''))),
				array('check', 'arcadeAzureAutoFill'),
			);

			$translation_settings = array_merge($translation_settings, $configAzure);
	}

	$newGameSettings = array(
			'',
				array('message', 'arcade_title_posting_settings'),
			'',
			array('check', 'arcade_newgame_notification'),
			array('check', 'arcadeEnablePosting'),
			array('check', 'arcadeEnableIframe'),
			array('check', 'arcadeEnablePostCount'),
			array('int', 'arcadePosterid', 'subtext' => $txt['arcadeAuxiliaryUserText']),
			array('int', 'gamesBoard', 'subtext' => $boardCheck),
			array('large_text', 'gamesMessage', 8),
	);
	$pathSettings = array(
		'',
			array('message', 'arcade_title_path_settings'),
		'',
			array('message', 'arcade_html5_path_notation'),
			array('text', 'gamesUrl', 'subtext' => '<a id="changeUrl" href="" onclick="arcadeChangeVal(\'gamesUrl\', \'' .  $boardurl . '/Games' . '\'); return false;">' .  sprintf($txt['arcade_rec_val'], $boardurl . '/Games') . '</a>'),
			array('text', 'gamesDirectory', 'subtext' => '<a id="changePath" href="" onclick="arcadeChangeVal(\'gamesDirectory\', \'' .  $jsboarddir . '/Games' . '\'); return false;">' .  sprintf($txt['arcade_rec_val'], $boarddir . '/Games') . '</a>'),
			array('text', 'romGamesUrl', 'subtext' => '<a id="changeRomUrl" href="" onclick="arcadeChangeVal(\'romGamesUrl\', \'' .  $boardurl . '/ArcadeRetroArch/roms' . '\'); return false;">' .  sprintf($txt['arcade_rec_val'], $boardurl . '/ArcadeRetroArch/roms') . '</a>'),
			array('text', 'romGamesDirectory', 'subtext' => '<a id="changeRomPath" href="" onclick="arcadeChangeVal(\'romGamesDirectory\', \'' .  $jsboarddir . '/ArcadeRetroArch/roms' . '\'); return false;">' .  sprintf($txt['arcade_rec_val'], $boarddir . '/ArcadeRetroArch/roms') . '</a>'),
	);

	if (!empty($arcadeModSettings['arcadeRetroLibraryCodeEnable'])) {
		$pathSettings = array_merge($pathSettings, array(array('text', 'arcadeRetroLibraryCode', 'subtext' => '<span class="arcadeHoverEffect" style="font-size: 8pt;font-style: oblique;">' .  sprintf($txt['arcade_lib_val'], '<span class="arcadeHoverEffectImg"><img src="' . $settings['default_theme_url'] . '/images/arc_icons/showtext.jpg" style="width:3rem;height: 0.75rem;filter: contrast(1.25);mix-blend-mode: luminosity;" alt=""></span><span style="display: none;" class="arcadeHoverEffectText">' . $arcadeLibVal . '</span>') . '</span>')));
	}

	$arcadeDebugSettings = array(
		'',
			array('message', 'arcade_title_debug_settings'),
		'',
			array('select', 'arcade_db_glob_setting', explode('|', $txt['arcade_db_glob_settingOptions']), 'help' => 'arcade_db_glob_settingHelp', 'subtext' => $arcadeMaxPacket[1]),
			array('check', 'arcade_log_savetype'),
			array('check', 'arcade_log_scoreloop'),
			array('check', 'arcade_log_install_game'),
			array('check', 'arcade_log_translate'),
			array('check', 'arcade_log_remote_game'),
			array('check', 'arcade_log_emulator_core'),
	);

	if (@extension_loaded('imagick')) {
		$arcadeDebugSettings = array_merge($arcadeDebugSettings, array(array('check', 'arcade_log_imagick'), array('check', 'arcade_log_emulatorjs_console', 'help' => 'arcade_log_emulatorjs_consoleHelp')));
	}
	else {
		$arcadeDebugSettings = array_merge($arcadeDebugSettings, array(array('check', 'arcade_log_emulatorjs_console', 'help' => 'arcade_log_emulatorjs_consoleHelp')));
	}


	if (file_exists($boarddir . '/ArcadeSources/ArcadePEAR.php') && file_exists($boarddir . '/ArcadeSources/ArcadeTar.php')) {
		$upload_settings = array(
			'',
				array('message', 'arcade_title_upload_settings'),
			'',
				array('check', 'arcade_install_duplicate_game'),
				array('check', 'arcade_install_clean_db'),
				array('check', 'arcadeArchiveTar', 'subtext' => $txt['arcadeArchiveTarText']),
				array('select', 'arcadeJsInsertDefault', explode('|', $txt['arcade_js_insertion']), 'subtext' => $txt['arcadeJsInsertDefaultText']),
		);
	}
	else {
		$upload_settings = array(
			'',
				array('message', 'arcade_title_upload_settings'),
			'',
				array('check', 'arcade_install_duplicate_game'),
				array('check', 'arcade_install_clean_db'),
				array('select', 'arcadeJsInsertDefault', explode('|', $txt['arcade_js_insertion']), 'subtext' => $txt['arcadeJsInsertDefaultText']),
		);
	}

	$newNum = isset($_REQUEST['num']) ? floatval($_REQUEST['num']) : 0;
	if ($newNum && !empty($_SESSION['smfArcadeFile'][$newNum]))
		$path = str_replace('file:///', '', $_SESSION['smfArcadeFile'][$newNum]) . '\\';
	else
		$path = "";

	// the back button path
	if ($newNum == -1)
		$path = "\\";

	// check if RAR package is available
	if (is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'))
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			list($checkUnix, $num) = array(false, 2);
			$txt['arcadeDownloadUnixRarDir'] = str_replace('%s', $txt['arcadeShowOS'][1], $txt['arcadeDownloadUnixRarDir']);
			if (!empty($path) && is_dir($path) && !empty($newNum))
			{
				$arcadeModSettings['arcadeDownloadWinRarDir'] = $path;
				@chdir($path);
				$checkRar = (`where WinRAR.exe`);
				$arcadeRarText = "<div id=\"rarIndex\" onmouseover=\"this.style = 'display: inline;font-size:110%;border: 1px solid;padding: 10px;box-shadow: 5px 10px 18px #888888;'\" onmouseout=\"this.style = 'display: inline;font-size:100%;'\" onclick=\"document.getElementById('arcadeDownloadWinRarDir').value='" . addslashes($arcadeModSettings['arcadeDownloadWinRarDir']) . "'\" style=\"display: inline;position: absolute;padding-left: 1em;\">" . ($checkRar ? sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][1]) : sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][1])) . "</div>";
				$checkWinRar = false;
			}
			elseif (!empty($arcadeModSettings['arcadeDownloadWinRarDir']) && is_dir($arcadeModSettings['arcadeDownloadWinRarDir']))
			{
				$_SESSION['arcadeSelectedPath'] = $arcadeModSettings['arcadeDownloadWinRarDir'];
				@chdir($arcadeModSettings['arcadeDownloadWinRarDir']);
				$checkRar = (`where WinRAR.exe`);
				$arcadeRarText = "<div id=\"rarIndex\" onmouseover=\"this.style = 'display: inline;font-size:110%;border: 1px solid;padding: 10px;box-shadow: 5px 10px 18px #888888;'\" onmouseout=\"this.style = 'display: inline;font-size:100%;'\" onclick=\"document.getElementById('arcadeDownloadWinRarDir').value='" . addslashes($arcadeModSettings['arcadeDownloadWinRarDir']) . "'\" style=\"display: inline;position: absolute;padding-left: 1em;\">" . ($checkRar ? sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][1]) : sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][1])) . "</div>";
				$checkWinRar = true;
			}
			elseif (is_dir("\Program Files (x86)\WinRAR"))
			{
				@chdir("\Program Files (x86)\WinRAR");
				$checkWinRar = false;
				$checkRar = (`where WinRAR.exe`);
				$arcadeModSettings['arcadeDownloadWinRarDir'] = "\Program Files (x86)\WinRAR";
				$arcadeRarText = "<span id=\"rarIndex\" onmouseover=\"this.style = 'display: inline;font-size:110%;border: 1px solid;padding: 10px;box-shadow: 5px 10px 18px #888888;'\" onmouseout=\"this.style = 'display: inline;font-size:100%;'\" onclick=\"document.getElementById('arcadeDownloadWinRarDir').value='" . addslashes($arcadeModSettings['arcadeDownloadWinRarDir']) . "'\" style=\"display: inline;position: absolute;padding-left: 1em;\">" . ($checkRar ? sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][1]) : sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][1])) . "</span>";
			}
			elseif (is_dir("\Program Files\WinRAR"))
			{
				@chdir("\Program Files\WinRAR");
				$checkWinRar = false;
				$checkRar = (`where WinRAR.exe`);
				$arcadeModSettings['arcadeDownloadWinRarDir'] = "\Program Files\WinRAR";
				$arcadeRarText = "<span id=\"rarIndex\" onmouseover=\"this.style = 'display: inline;font-size:110%;border: 1px solid;padding: 10px;box-shadow: 5px 10px 18px #888888;'\" onmouseout=\"this.style = 'display: inline;font-size:100%;'\" onclick=\"document.getElementById('arcadeDownloadWinRarDir').value='" . addslashes($arcadeModSettings['arcadeDownloadWinRarDir']) . "'\" style=\"display: inline;position: absolute;padding-left: 1em;\">" . ($checkRar ? sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][1]) : sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][1])) . "</span>";
			}
		}
		else
		{
			$txt['arcadeDownloadUnixRarDir'] = str_replace('%s', $txt['arcadeShowOS'][0], $txt['arcadeDownloadUnixRarDir']);
			$checkRar = is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec') ? @shell_exec('type -P rar') : '';
			$arcadeRarText = $checkRar ? ('<div style="display: inline;" id="rarIndex">' . sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][0]) . '</div>') : ('<div style="display: inline;" id="rarIndex">' . sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][0]) . '</div>');
			$arcadeModSettings['arcadeDownloadWinRarDir'] = '';
			$arcadeModSettings['arcadeDownloadUnixRarDir'] = $checkRar ? sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][0]) : sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][0]);
			list($checkWinRar, $checkUnix, $num) = array(false, true, 0);
		}
	}
	else
	{
		$txt['arcadeDownloadUnixRarDir'] = str_replace('%s', $txt['arcadeShowOS'][0], $txt['arcadeDownloadUnixRarDir']);
		$checkRar = false;
		$arcadeRarText = ("<div style=\"display: inline;\" id=\"rarIndex\">" . sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][0]) . "</div>");
		$arcadeModSettings['arcadeDownloadWinRarDir'] = '';
		$arcadeModSettings['arcadeDownloadUnixRarDir'] = $txt['arcadeDownloadShell'];
		list($checkWinRar, $checkUnix, $num) = array(false, true, 0);
	}

	if (!empty($arcadeModSettings['arcadeEnableDownload']) && !empty($arcadeModSettings['arcadeDownloadShellEnable'])) {
		$arcadeModSettings['arcadeDownloadWinRarDir'] = !empty($arcadeModSettings['arcadeDownloadWinRarDir']) ? $arcadeModSettings['arcadeDownloadWinRarDir'] : '';
		$download_settings = array(
			'',
			array('message', 'arcade_title_download_settings'),
			'',
			array('check', 'arcadeEnableDownload'),
			array('check', 'arcadeDownloadHideLink'),
			//array('check', 'arcadeDisableArchive'),
			array('check', 'arcade_gz_user'),
			array('check', 'arcadeDownloadShellEnable'),
			array((!$checkUnix ? 'large_text' : 'text'), (!$checkUnix ? 'arcadeDownloadWinRarDir' : 'arcadeDownloadUnixRarDir'), $num, 'subtext' => (!$checkUnix ? sprintf($txt['arcadeDownloadWinRarDirSuggest'], arcadeBrowseFolders($arcadeModSettings['arcadeDownloadWinRarDir']) . $arcadeRarText) : sprintf($txt['arcadeDownloadUnixRarDirSuggest'], !$checkRar ? sprintf($txt['arcadeDownloadRarNotDetected'], $txt['arcadeShowOS'][0]) : sprintf($txt['arcadeDownloadRarDetected'], $txt['arcadeShowOS'][0])))),
			array('select', 'arcade_gz', explode('|', (!$checkRar || empty($arcadeModSettings['arcadeDownloadShellEnable']) ? str_replace('|rar', '', $txt['arcade_compression']) : $txt['arcade_compression']))),
			array('text', 'arcadeDownPass'),
			array('int', 'arcadeDownPost'),
			array('int', 'pdl_DownMax', 'subtext' => $txt['pdl_DownMax_Info']),
		);
	}
	elseif (!empty($arcadeModSettings['arcadeEnableDownload']))
		$download_settings = array(
			'',
			array('message', 'arcade_title_download_settings'),
			'',
			array('check', 'arcadeEnableDownload'),
			array('check', 'arcadeDownloadHideLink'),
			array('check', 'arcade_gz_user'),
			array('check', 'arcadeDownloadShellEnable'),
			array('message', 'arcadeDownloadShell'),
			array('select', 'arcade_gz', explode('|', (!$checkRar || empty($arcadeModSettings['arcadeDownloadShellEnable']) ? str_replace('|rar', '', $txt['arcade_compression']) : $txt['arcade_compression']))),
			array('text', 'arcadeDownPass'),
			array('int', 'arcadeDownPost'),
			array('int', 'pdl_DownMax'),
		);
	else
		$download_settings = array(
			'',
			array('message', 'arcade_title_download_settings'),
			'',
			array('check', 'arcadeEnableDownload'),
		);

	$report_settings = array(
		'',
		array('message', 'arcade_title_report_settings'),
		'',
		array('check', 'arcadeEnableReport'),
		array('check', 'arcadeEnableGameDisable'),
		array('check', 'arcadeEnableReportNotification'),
	);

	$config_vars = array_merge($general_settings, $cache_settings, $translation_settings, $newGameSettings, $pathSettings, $upload_settings, $download_settings, $report_settings, $arcadeDebugSettings);

	if ($checkUnix)
		$headFunc = '
		function arcadeDownloadAdminScript() {
			if(document.getElementById("arcadeDownloadUnixRarDir"))
				document.getElementById("arcadeDownloadUnixRarDir").disabled = true;
		}';
	else
		$headFunc = '
		function arcadeDownloadAdminScript() {
			if (document.getElementById("arcadeBrowseFolders")) {
				document.getElementById("arcadeBrowseFolders").style = "display: inline;";
			}
		}';

	$context['html_headers'] .= '
	<script>' . $headFunc . '
		function arcadeAdminEvents() {
			document.getElementById("arcadeTranslationAPI").onchange = function(){document.getElementById("arcadeTranslationAPI").form.submit(); return false;};
		}
		function arcadeChangeVal(mychange, myval) {
			document.getElementById(mychange).value = myval;
			return false;
		}
		function arcadeCacheSettings() {
			if (document.getElementById("arcade_cache_enable")) {
				if (document.getElementById("arcade_cache_enable").checked) {
					$("#arcade_cache_time").prop("disabled", false);
					$("#arcadeGamecacheUpdate").prop("disabled", false);
				}
				else {
					$("#arcade_cache_time").prop("disabled", true);
					$("#arcadeGamecacheUpdate").prop("disabled", false);
				}
				document.getElementById("arcade_cache_enable").onclick = function() {
					if (document.getElementById("arcade_cache_enable").checked) {
						$("#arcade_cache_time").prop("disabled", false);
						$("#arcadeGamecacheUpdate").prop("disabled", false);
					}
					else {
						$("#arcade_cache_time").prop("disabled", true);
						$("#arcadeGamecacheUpdate").prop("disabled", false);
					}
				}
			}
		}
		function arcadeMaxPacketDb() {
			$("#arcade_db_glob_setting").prop("disabled", true);
		}
		$(document).ready(function() {
			arcadeAdminEvents();
			$("input:checkbox").addClass("arcade_admin_checkbox");
			$(".arcade_admin_checkbox").each(function( index ) {
				if ($(this).prop("checked")) {
					let arcadeCheckBox = $(this).prop("id");
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
			});
			$(".arcade_admin_checkbox").click(function(){
				let arcadeCheckBox = $(this).prop("id");
				if ($(this).prop("checked")) {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
				else {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "initial");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "initial");
				}
			});
			arcadeCacheSettings();' . ($arcadeMaxPacket[0] < 0 ? '
			arcadeMaxPacketDb();' : '') . '
			arcadeDownloadAdminScript();
			$("#arcadeRetroLibraryCode").prop("value", "");
			$(".arcadeHoverEffect").hover(function(){
				$(".arcadeHoverEffectImg").hide();
				$(".arcadeHoverEffectText").show();
			});
		});
	</script>';

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		$_SESSION['arcadeFirstLoad'] = '';
		checkSession();
		if ((!file_exists($boarddir . '/ArcadeSources/ArcadePEAR.php') || !file_exists($boarddir . '/ArcadeSources/ArcadeTar.php')) && isset($_POST['arcadeArchiveTar'])) {
			unset($_POST['arcadeArchiveTar']);
		}
		if (isset($_POST['arcadeCookieEncryptionCipher'])) {
			$_POST['arcadeCookieEncryptionCipher'] = preg_replace('/\s+/', '', $_POST['arcadeCookieEncryptionCipher']);
			$checkCipher = ctype_alnum($_POST['arcadeCookieEncryptionCipher']) && strlen($_POST['arcadeCookieEncryptionCipher']) > 4 && strlen($_POST['arcadeCookieEncryptionCipher']) < 30 ? true : false;
			if (!$checkCipher) {
				unset($_POST['arcadeCookieEncryptionCipher']);
			}
		}
		if (isset($_POST['arcadeRetroLibraryCode'])) {
			$_POST['arcadeRetroLibraryCode'] = !is_string($_POST['arcadeRetroLibraryCode']) ? strval($_POST['arcadeRetroLibraryCode']) : $_POST['arcadeRetroLibraryCode'];
			$checkCoded = !filter_var($_POST['arcadeRetroLibraryCode'], FILTER_VALIDATE_URL) && !empty($_POST['arcadeRetroLibraryCode']) ? arcadeEncryptionCipher($_POST['arcadeRetroLibraryCode'], array(), 'decrypt') : $_POST['arcadeRetroLibraryCode'];
			if (filter_var($checkCoded, FILTER_VALIDATE_URL) && pathinfo(basename($checkCoded), PATHINFO_EXTENSION) == 'zip') {
				$_POST['arcadeRetroLibraryCode'] = arcadeEncryptionCipher($checkCoded, array(), 'encrypt');
			}
			else {
				unset($_POST['arcadeRetroLibraryCode']);
			}
		}
		if (!isset($_POST['arcadeRetroLibraryCode'])) {
			$find = arcadeSearchArray('arcadeRetroLibraryCode', $config_vars);
			$findKey = !empty($find) && is_numeric($find) ? array_key_first($find) : -1;
			if ($findKey > -1) {
				unset($config_vars[$findKey]);
			}
		}
		if (isset($_POST['arcadeBehaviorCDN'])) {
			$_POST['arcadeBehaviorCDN'] = intval($_POST['arcadeBehaviorCDN']);
			$_POST['arcadeBehaviorCDN'] = $_POST['arcadeBehaviorCDN'] < 0 ? 0 : $_POST['arcadeBehaviorCDN'];
			$_POST['arcadeBehaviorCDN'] = $_POST['arcadeBehaviorCDN'] > 2 ? 2 : $_POST['arcadeBehaviorCDN'];
		}

		if (isset($_POST['arcadeAzureAutoFill']))
		{
			$_POST['arcadeAzureAutoFill'] = 0;
			$_POST['arcadeAzureEndpoint'] = 'https://api.cognitive.microsofttranslator.com';
			$_POST['arcadeAzurePath'] = '/translate?api-version=3.0';
			$_POST['arcadeAzureRegion'] = '';
			$_POST['arcade_adjust_desc_info'] = 0;
		}
		if (isset($_POST['arcadeDeepLAutoFill']))
		{
			$_POST['arcadeDeepLAutoFill'] = 0;
			$_POST['arcadeDeepLEndpoint'] = ' https://api-free.deepl.com';
			$_POST['arcadeDeepLPath'] = '/v2/translate';
			$_POST['arcade_adjust_desc_info'] = 0;
		}
		if (isset($_POST['arcadeNewRandomId']))
		{
			$find = arcadeSearchArray('arcadeRetroLibraryCode', $config_vars);
			$findKey = !empty($find) && is_numeric($find) ? array_key_first($find) : -1;
			$config_id_var = array(
				array('text', 'arcadeRandomIdVar'),
				array('text', 'arcadeSecretIdVar'),
			);
			if ($findKey < 0) {
				$config_id_var = array_merge($config_id_var, array(array('text' => 'arcadeRetroLibraryCode')));
			}
			$config_vars = array_merge((array)$config_vars,(array)$config_id_var);
			$_POST['arcadeNewRandomId'] = 0;
			$bytes = openssl_random_pseudo_bytes(10);
			$_POST['arcadeRandomIdVar'] = strval(bin2hex($bytes));
			$bytes2 = openssl_random_pseudo_bytes(5);
			$_POST['arcadeSecretIdVar'] = strval(bin2hex($bytes2));
			$currentCoded = isset($_POST['arcadeRetroLibraryCode']) ? arcadeEncryptionCipher($_POST['arcadeRetroLibraryCode'], array(), 'decrypt') : arcadeEncryptionCipher($arcadeModSettings['arcadeRetroLibraryCode'], array(), 'decrypt');
			$_POST['arcadeRetroLibraryCode'] = arcadeEncryptionCipher($currentCoded, array($_POST['arcadeRandomIdVar'], $_POST['arcadeSecretIdVar']), 'encrypt');
			clearstatcache();
		}
		if (isset($_POST['arcadeCatsCellWidth']))
		{
			$_POST['arcadeCatsCellWidth'] = intval($_POST['arcadeCatsCellWidth']);
			$_POST['arcadeCatsCellWidth'] = $_POST['arcadeCatsCellWidth'] < 1 ? 1 : $_POST['arcadeCatsCellWidth'];
			$_POST['arcadeCatsCellWidth'] = $_POST['arcadeCatsCellWidth'] > 100 ? 100 : $_POST['arcadeCatsCellWidth'];
		}
		if (isset($_POST['arcadeCatsPerLine']))
		{
			$_POST['arcadeCatsPerLine'] = intval($_POST['arcadeCatsPerLine']);
			$_POST['arcadeCatsPerLine'] = $_POST['arcadeCatsPerLine'] < 1 ? 1 : $_POST['arcadeCatsPerLine'];
			$_POST['arcadeCatsPerLine'] = $_POST['arcadeCatsPerLine'] > 20 ? 20 : $_POST['arcadeCatsPerLine'];
		}
		if (isset($_POST['arcade_cache_time']))
		{
			$_POST['arcade_cache_time'] = intval($_POST['arcade_cache_time']);
			$_POST['arcade_cache_time'] = abs($_POST['arcade_cache_time']);
			$_POST['arcade_cache_time'] = $_POST['arcade_cache_time'] < 1 ? 1 : $_POST['arcade_cache_time'];
			$_POST['arcade_cache_time'] = $_POST['arcade_cache_time'] > 1800 ? 1800 : $_POST['arcade_cache_time'];
		}


		arcadeSaveDBSettings($config_vars);
		writeLog();
		redirectexit('action=admin;area=arcade;sa=settings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=settings;save';
	$context['settings_title'] = $txt['arcade_admin_visual'];
	$context['sub_template'] = 'show_settings';

	arcadePrepareDBSettingContext($config_vars);
}

function ArcadeAdminChangeTableStructure($table, $column, $structure)
{
	global $smcFunc;
	db_extend('packages');

	$smcFunc['db_change_column']('{db_prefix}' . $table, $column, $structure);
}

function arcadeMaxPacket()
{
	global $arcadeModSettings, $smcFunc, $db_type, $txt;
	list($val, $msg, $dbGlobSetting) = array(-1, explode('|', $txt['arcade_db_glob_settingOptionsQueryNone']), !empty($arcadeModSettings['arcade_db_glob_setting']) ? (int)$arcadeModSettings['arcade_db_glob_setting'] : 0);
	if (stripos($db_type, 'mysql') !== FALSE && !empty($dbGlobSetting)) {
		switch ($dbGlobSetting) {
			case 1:
				$request = $smcFunc['db_query']('', '
					SHOW VARIABLES LIKE {string:pack}',
					array('pack' => 'max_allowed_packet')
				);

				$dbrow = $smcFunc['db_fetch_assoc']($request);
				$val = !empty($dbrow['Value']) ? (int)$dbrow['Value'] : 0;
				$smcFunc['db_free_result']($request);
				break;
			case 2:
				$request = $smcFunc['db_query']('', '
					SELECT @@global.{string:pack}',
					array('pack' => 'max_allowed_packet')
				);
				$dbrow2 = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);
				$val = !empty($dbrow2) && !empty($dbrow2[array_keys($dbrow2)[0]]) ? (int)$dbrow2[array_keys($dbrow2)[0]] : 0;
				break;
			default:
				$val = 0;
		}

		$maxPacket = $val < 1 ? '<span class="alert">' . $msg[1] . '</span>' : ($val/1048576) . 'M';
	}
	elseif (stripos($db_type, 'mysql') !== FALSE && empty($dbGlobSetting)) {
		list($val, $maxPacket) = array(0, '<span class="alert">' . $msg[0] . '</span>');
	}

	$maxPacket = $val > -1 ? $maxPacket : '<span class="alert">' . $msg[2] . '</span>';
	return array($val, sprintf($txt['arcade_db_glob_settingOptionsQuery'], $maxPacket));
}

function ArcadeAdminPermission($return_config = false)
{
	global $scripturl, $txt, $arcadeModSettings, $context, $settings, $smcFunc;

	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	$config_vars = array(
		array('select', 'arcadePermissionMode',
			array(
				$txt['arcade_permission_mode_none'], $txt['arcade_permission_mode_category'],
				$txt['arcade_permission_mode_game'], $txt['arcade_permission_mode_and_both'],
				$txt['arcade_permission_mode_or_both']
			)
		),
		'',
		array('check', 'arcadePostPermission'),
		array('int', 'arcadePostsPlay'),
		array('int', 'arcadePostsLastDay'),
		array('int', 'arcadePostsPlayAverage'),
		'',
		array('permissions', 'arcade_view', 0, $txt['perm_arcade_view']),
		array('permissions', 'arcade_play', 0, $txt['perm_arcade_play']),
		array('permissions', 'arcade_view_retro_arch', 0, $txt['perm_arcade_view_retro_arch']),
		array('permissions', 'arcade_submit', 0, $txt['perm_arcade_submit']),
		array('permissions', 'arcade_download', 0, $txt['perm_arcade_download']),
		array('permissions', 'arcade_download_rom', 0, $txt['perm_arcade_download_rom']),
		array('permissions', 'arcade_report', 0, $txt['perm_arcade_report']),

	);

	list($groups, $downloadGroups, $groupsPost, $modGroups) = array(arcadeGetGroups('all'), array('', array('message', 'arcadeGroupLimits'), ''), array(), array());
	$arcadeModSettings['pdl_DownMax'] = empty($arcadeModSettings['pdl_DownMax']) ? '0' : $arcadeModSettings['pdl_DownMax'];

	$request = $smcFunc['db_query']('', '
		SELECT id_group, download_limit
		FROM {db_prefix}arcade_membergroups
		WHERE id_group > -2',
		array(
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$arcadeModSettings['arcade_MembergroupDownMax' . $row['id_group']] = isset($row['download_limit']) ? $row['download_limit'] : $arcadeModSettings['pdl_DownMax'];
	$smcFunc['db_free_result']($request);

	foreach ($groups as $group) {
		if ($group['id'] == -2)
			continue;

		$txt['arcade_MembergroupDownMax' . $group['id']] = sprintf($txt['arcade_MembergroupDownMax'], $group['name']);
		$downloadGroups[] = array('int', 'arcade_MembergroupDownMax' . $group['id']);
		$groupsPost[$group['id']] = 'arcade_MembergroupDownMax' . $group['id'];
		$arcadeModSettings['arcade_MembergroupDownMax' . $group['id']] = !isset($arcadeModSettings['arcade_MembergroupDownMax' . $group['id']]) ? $arcadeModSettings['pdl_DownMax'] : $arcadeModSettings['arcade_MembergroupDownMax' . $group['id']];
	}

	$config_vars = array_merge($config_vars, $downloadGroups);

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		// Arcade membergroup settings have a unique table for some of its data
		foreach ($groupsPost as $key => $post) {
			if (isset($_POST[$post])) {
				$_POST[$post] = preg_replace("/[^0-9.]/", "", $_POST[$post]);
				$val = abs(intval($_POST[$post]));
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_membergroups
					WHERE id_group = {int:groupid}',
					array(
						'groupid' => $key,
					)
				);

				$smcFunc['db_insert']('insert',
					'{db_prefix}arcade_membergroups',
					array('id_group' => 'int', 'download_limit' => 'int'),
					array($key, $val),
					array('id_group')
				);

				unset($_POST[$post]);
			}
			unset($arcadeModSettings['arcade_MembergroupDownMax' . $group['id']]);
		}
		// For peace of mind
		foreach ($arcadeModSettings as $key => $val) {
			if (stripos($key, 'arcade_MembergroupDownMax') !== false) {
				unset($arcadeModSettings[$key]);
			}
		}

		checkSession();
		arcadeSaveDBSettings($config_vars);
		writeLog();
		redirectexit('action=admin;area=arcade;sa=permission');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=permission;save';
	$context['settings_title'] = $txt['arcade_general_permissions'];
	$context['sub_template'] = 'show_settings';
	$context['html_headers'] .= '
	<script type="text/javascript">
		$(document).ready(function() {
			$("input:checkbox").addClass("arcade_admin_checkbox");
			$(".arcade_admin_checkbox").each(function( index ) {
				if ($(this).prop("checked")) {
					let arcadeCheckBox = $(this).prop("id");
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
			});
			$(".arcade_admin_checkbox").click(function(){
				let arcadeCheckBox = $(this).prop("id");
				if ($(this).prop("checked")) {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
				else {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "initial");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "initial");
				}
			});
		});
	</script>' . (empty($_SESSION['arcade_isMobile']) ? '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin.css?' . $suffixVersion . '" />' : '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin-mobile.css?' . $suffixVersion . '" />');
	arcadePrepareDBSettingContext($config_vars);
}

function ArcadeAdminCategory()
{
	global $context, $boarddir, $sourcedir, $settings, $arcadeModSettings, $txt;

	require_once($sourcedir . '/Arcade.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAdmin.php');

	loadArcade('admin', 'managecategory');

	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_category'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_category_desc'];
	$context['html_headers'] .= '
	<script type="text/javascript">
		$(document).ready(function() {
			$("input:checkbox").addClass("arcade_admin_checkbox");
			$(".arcade_admin_checkbox").each(function( index ) {
				if ($(this).prop("checked")) {
					let arcadeCheckBox = $(this).prop("id");
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
			});
			$(".arcade_admin_checkbox").click(function(){
				let arcadeCheckBox = $(this).prop("id");
				if ($(this).prop("checked")) {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
				else {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "initial");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "initial");
				}
			});
		});
	</script>' . (empty($_SESSION['arcade_isMobile']) ? '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin.css?' . $suffixVersion . '" />' : '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin-mobile.css?' . $suffixVersion . '" />');

	$subActions = array(
		'list' => array('ArcadeCategoryList', 'arcade_admin'),
		'edit' => array('ArcadeCategoryEdit', 'arcade_admin'),
		'new' => array('ArcadeCategoryEdit', 'arcade_admin'),
		'save' => array('ArcadeCategorySave', 'arcade_admin'),
		'upload' => array('ArcadeCategoryUpload', 'arcade_admin'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	$subActions[$_REQUEST['sa']][0]();
}

function ArcadeCategoryList()
{
	global $db_prefix, $arcadeModSettings, $context, $boarddir, $scripturl, $smcFunc;

	if (isset($_REQUEST['save']))
	{
		checkSession();

		asort($_REQUEST['category_order'], SORT_NUMERIC);

		$i = 1;

		if (!empty($_REQUEST['category']))
		{
			$ids = array();
			foreach ($_REQUEST['category'] as $id)
			{
				$ids[] = $id;
				unset($_REQUEST['category_order'][$id]);
			}

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_categories
				WHERE id_cat IN({array_int:category})',
				array(
					'category' => $ids,
				)
			);
		}

		foreach ($_REQUEST['category_order'] as $id => $dummy)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_categories
				SET cat_order = {int:order}
				WHERE id_cat = {int:category}',
				array(
					'order' => $i++,
					'category' => $id,
				)
			);
	}

	$request = $smcFunc['db_query']('', '
		SELECT id_cat, cat_name, num_games, cat_order, cat_icon
		FROM {db_prefix}arcade_categories
		ORDER BY cat_order',
		array()
	);

	$context['arcade_category'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['arcade_category'][] = array(
			'id' => $row['id_cat'],
			'name' => arcadeFixTagsHTML($row['cat_name'], true),
			'href' => $scripturl . '?action=admin;area=arcadecategory;sa=edit;category=' . $row['id_cat'],
			'games' => $row['num_games'],
			'order' => $row['cat_order'],
			'cat_icon' => $row['cat_icon'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Template
	$context['sub_template'] = 'arcade_admin_category_list';
}

function ArcadeCategoryEdit()
{
	global $db_prefix, $arcadeModSettings, $modSettings, $context, $boarddir, $sourcedir, $smcFunc, $settings, $user_info, $user_settings;

	$new = false;
	$context['arcade_smf_version'] = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['post_max_size'] = arcade_return_bytes(ini_get('post_max_size')) / 1048576;
	$context['post_max_size'] = preg_replace('/[^0-9\.]/', '', $context['post_max_size']);
	$context['post_max_size'] = round($context['post_max_size'], 2);

	if ($context['arcade_smf_version'] == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}
	else
		require_once($sourcedir . '/Subs-Auth.php');

	if (isset($_REQUEST['category']) && (float)$_REQUEST['category'] !== 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_cat, cat_name, num_games, cat_order, member_groups, cat_icon, cat_dl, cat_js
			FROM {db_prefix}arcade_categories
			WHERE id_cat = {int:category}',
			array(
				'category' => (float)$_REQUEST['category'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['category'] = array(
				'id' => $row['id_cat'],
				'name' => arcadeFixTagsHTML($row['cat_name'], true),
				'member_groups' => explode(',', $row['member_groups']),
				'cat_icon' => !empty($row['cat_icon']) ? $row['cat_icon'] : '',
				'disable_dl' => !empty($row['cat_dl']) ? $row['cat_dl'] : '',
				'js_insert' => !empty($row['cat_js']) ? $row['cat_js'] : '',
			);
		$smcFunc['db_free_result']($request);

		if (empty($context['category']))
		{
			$context['category'] = array(
				'id' => 'new',
				'name' => '',
				'member_groups' => array(),
				'cat_icon' => '',
				'disable_dl' => 0,
				'js_insert' => 0,
			);
			$new = true;
		}
	}
	else
	{
		$new = true;

		$context['category'] = array(
			'id' => 'new',
			'name' => '',
			'member_groups' => array(),
			'cat_icon' => '',
			'disable_dl' => 0,
			'js_insert' => 0,
		);
	}

	$context['arcade_cat_message'] = !empty($_SESSION['arcade_cat_message']) ? $_SESSION['arcade_cat_message'] : '';
	$context['arcade_cat_file'] = !empty($_SESSION['arcade_cat_file']) ? trim($_SESSION['arcade_cat_file']) : '';
	list ($_SESSION['arcade_cat_message'], $_SESSION['arcade_cat_file'], $_SESSION['arcade_cat_icon']) = array('', '', '');
	$context['category']['cat_icon'] = !empty($context['category']['cat_icon']) ? $context['category']['cat_icon'] : '';
	$context['groups'] = arcadeGetGroups($new ? 'all' : $context['category']['member_groups']);
	if ((!empty($context['arcade_cat_file'])) && in_array(substr($context['arcade_cat_file'], -4), array('.gif', '.png', '.jpg')))
	{
		if (file_exists($settings['default_theme_dir'] . '/images/arc_icons/' . $context['arcade_cat_file']))
			$_SESSION['arcade_cat_icon'] = $context['arcade_cat_file'];
		$context['arcade_cat_file'] = '';
	}
	elseif ((!empty($context['category']['cat_icon'])) && in_array(substr($context['category']['cat_icon'], -4), array('.gif', '.png', '.jpg')))
	{
		$_SESSION['arcade_cat_icon'] = '';
		if (file_exists($settings['default_theme_dir'] . '/images/arc_icons/' . $context['category']['cat_icon']))
			$_SESSION['arcade_cat_icon'] = $context['category']['cat_icon'];
		$context['category']['cat_icon'] = '';
	}

	// Template
	$context['sub_template'] = 'arcade_admin_category_edit';
}

function ArcadePdlReports($return_config = false)
{
	global $scripturl, $txt, $arcadeModSettings, $context, $settings, $boarddir, $db_prefix, $smcFunc, $modSettings;

	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	$context['arcade']['game_reports'] = array();
	$game = !empty($_REQUEST['game']) ? (int)$_REQUEST['game'] : 0;
	$where = !empty($game) ? 'pdl.pdl_gameid = {int:gameid}' . ' AND ': '';
	$types = explode('|', $txt['arcade_report_gametype']);
	/* Reported Games */
	$request = $smcFunc['db_query']('', '
		SELECT pdl.pdl_gameid, pdl.game_name, pdl.report_day, pdl.report_year, pdl.report_id, pdl.report_reason, pdl.user_id, pdl.download_count, pdl.download_disable, mem.member_name,
			game.enabled, game.game_file, game.rom_flag, game.submit_system
		FROM {db_prefix}arcade_pdl2 AS pdl
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = pdl.user_id)
		LEFT JOIN {db_prefix}arcade_games AS game ON (game.id_game = pdl.pdl_gameid)
		WHERE ' . $where . '(pdl.report_id > 0 OR pdl.download_disable > 0)
		ORDER BY pdl.pdl_gameid',
		array('gameid' => $game)
	);

	$i = 0;
	// $modSettings['default_timezone']
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$checkgame = false;
		if (empty($row['enabled'])) {$row['enabled'] = 0;}
		if (empty($row['game_file'])) {$row['game_file'] = false;}
		if ($row['enabled'] == 0 && $row['game_file'] == false) {$checkgame = 'DELETE';}
		if (empty($row['game_name'])) {$row['game_name'] = false;}
		if (empty($row['report_day'])) {$row['report_day'] = 0;}
		if (empty($row['report_year'])) {$row['report_year'] = 0;}
		if (empty($row['report_id'])) {$row['report_id'] = 0;}
		if (empty($row['report_reason'])) {$row['report_reason'] = '';}
		if (empty($row['member_name'])) {$row['member_name'] = false;}
		if (empty($row['user_id'])) {$row['user_id'] = 0;}
		if (empty($row['download_count'])) {$row['download_count'] = 0;}
		if (empty($row['download_disable'])) {$row['download_disable'] = 0;}
		if (empty($row['pdl_gameid'])) {$row['pdl_gameid'] = 0;}
		$ydate = !empty($modSettings['default_timezone']) ? date(mktime(0, 0, 0, 1, ($row['report_day'] + 1), $row['report_year'])) : date('Y-m-d', mktime(0, 0, 0, 1, ($row['report_day'] + 1), $row['report_year']));
		$ydate = date('Y-m-d', $ydate);
		$zdate = !empty($modSettings['default_timezone']) ? new DateTime($ydate, new DateTimeZone($modSettings['default_timezone'])) : $ydate;
		$xdate = $zdate->format('Y-m-d');
		$dateArray = explode('-', $xdate);
		list($row['report_month'], $row['report_day']) = array($dateArray[1], $dateArray[2]);
		$context['arcade']['game_reports'][$row['pdl_gameid']] = array(
			'check' => $checkgame,
			'gameid' => $row['pdl_gameid'],
			'name' => $row['game_name'],
			'day' => $row['report_day'],
			'month' => $row['report_month'],
			'year' => $row['report_year'],
			'user' => $row['user_id'],
			'count' => $row['download_count'],
			'disable' => $row['download_disable'],
			'report' => $row['report_id'],
			'report_reason' => $row['report_reason'],
			'edit_game' => $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $row['pdl_gameid'],
			'user_profile' => $scripturl . '?action=profile;u=' . $row['user_id'],
			'user_id' => $row['user_id'],
			'user_name' => $row['member_name'],
			'rom_flag' => $row['rom_flag'],
			'game_type' => !empty($row['rom_flag']) ? $types[0] : (!empty($row['submit_system']) && stripos($row['submit_system'], 'html5') !== FALSE ? $types[1] : $types[2]),
		);
	}
	$smcFunc['db_free_result']($request);

	if (isset($_REQUEST['save']))
	{
		/*  Are there reports to remove from the list?  */
		if (isset($_POST['delete']) && is_array($_POST['delete']))
		{
			$delete_reports = $_POST['delete'];
			foreach ($context['arcade']['game_reports'] as $games)
			{
				$i = (int)$games['gameid'];
				/* Remove the flag if it was opted */
				foreach ($delete_reports as $check_report)
				{
					if ((int)$check_report == $i)
					{
						$request = $smcFunc['db_query']('', '
							UPDATE {db_prefix}arcade_pdl2
							SET report_id = 0, report_day = 0, report_year = 0, report_reason = {string:reason}
							WHERE pdl_gameid = {int:game}',
							array('game' => $i, 'reason' => '')
						);
						if (!isset($_POST['nonenable']))
						{
							$request = $smcFunc['db_query']('', '
								UPDATE {db_prefix}arcade_games
								SET enabled = 1
								WHERE id_game = {int:game}',
								array('game' => $i, 'reason' => '')
							);
						}
					}
				}
			}
		}
		/*  Are there download settings to toggle?  */
		elseif (isset($_POST['toggle']) && is_array($_POST['toggle']))
		{
			$toggle_dls = $_POST['toggle'];
			foreach ($context['arcade']['game_reports'] as $games)
			{
				$toggle = 1;
				if ((int)$games['disable'] > 0) {$toggle = 0;}
				$i = (int)$games['gameid'];
				/* Change the flag if it was opted */
				foreach ($toggle_dls as $check_dl)
				if ((int)$check_dl == $i)
					{
						$request = $smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_pdl2
						SET download_disable = {int:change}
						WHERE pdl_gameid = {int:game}',
						array('game' => $i,
							   'change' => $toggle,));

					}
			}
		}
		/*   Are there -disabled/no file data- games to remove from the list? (also removes download stats)  */
		elseif (isset($_POST['maintain']))
		{
			foreach ($context['arcade']['game_reports'] as $games)
			{
				/* Drop the entry if it isn't in the arcade_games table */
				if ($games['check'] == 'DELETE')
				{
					$i = (int)$games['gameid'];
					$tableName = 'arcade_pdl2';
					$request = $smcFunc['db_query']('', 'DELETE FROM {db_prefix}' . $tableName . ' WHERE {db_prefix}' . $tableName . '.pdl_gameid = ' . $i);

				}
			}
		}
		else
			{$delete_reports = array(); $toggle_dls = array();}
		redirectexit('action=admin;area=arcade;sa=pdl_reports');
	}
	$context['template_layers'][] = 'arcade_reports';
	$context['sub_template'] = 'arcade_reports';
	$context['page_title'] = sprintf($txt['pdl_admin_reports']);
	$context['post_url'] = $scripturl . '?action=admin;area=arcade;sa=pdl_reports;save';
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;action=admin;area=arcade;sa=pdl_reports;sesc=' . $context['session_id'],
		'name' => $txt['pdl_admin_reports'],
	);
	$context['html_headers'] .= '
	<script type="text/javascript">
		$(document).ready(function() {
			$("input:checkbox").addClass("arcade_admin_checkbox");
			$(".arcade_admin_checkbox").each(function( index ) {
				if ($(this).prop("checked")) {
					let arcadeCheckBox = $(this).prop("id");
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
			});
			$(".arcade_admin_checkbox").click(function(){
				let arcadeCheckBox = $(this).prop("id");
				if ($(this).prop("checked")) {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "bolder");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "oblique 23deg");
				}
				else {
					$("label[for=" + arcadeCheckBox + "]").css("font-weight", "initial");
					$("label[for=" + arcadeCheckBox + "]").css("font-style", "initial");
				}
			});
		});
	</script>' . (empty($_SESSION['arcade_isMobile']) ? '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin.css?' . $suffixVersion . '" />' : '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/arcade-admin-mobile.css?' . $suffixVersion . '" />');

	loadTemplate('ArcadeReports');
	return;
}
function ArcadeCategorySave()
{
	global $db_prefix, $arcadeModSettings, $context, $boarddir, $smcFunc;
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	checkSession();
	validateToken('admin', 'post', false);

	$memberGroups = array();

	if (!empty($_REQUEST['groups']))
		foreach ($_REQUEST['groups'] as $k => $id)
			$memberGroups[] = (int) $id;

	$_REQUEST['category_name'] = isset($_REQUEST['category_name']) ? $_REQUEST['category_name'] : '???';
	$_REQUEST['category_disable_dl'] = isset($_REQUEST['category_disable_dl']) ? $_REQUEST['category_disable_dl'] : 0;
	$_REQUEST['category_js_insert'] = isset($_REQUEST['category_js_insert']) ? $_REQUEST['category_js_insert'] : 0;
	$_REQUEST['category_name'] = mb_strlen($_REQUEST['category_name'] > 120) ? mb_substr($_REQUEST['category_name'], 0, 119) : $_REQUEST['category_name'];
	$_REQUEST['category_icon'] = !empty($_SESSION['arcade_cat_icon']) ? $_SESSION['arcade_cat_icon'] : 'Default.gif';
	$_SESSION['arcade_cat_file'] = '';


	if ($_REQUEST['category'] == 'new')
	{
		$request = $smcFunc['db_query']('', '
			SELECT MAX(cat_order)
			FROM {db_prefix}arcade_categories'
		);

		list ($max) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$smcFunc['db_insert']('insert',
			'{db_prefix}arcade_categories',
			array('cat_name' => 'string', 'member_groups' => 'string', 'cat_order' => 'int', 'special' => 'int', 'cat_icon' => 'string', 'cat_dl' => 'int', 'cat_js' => 'int'),
			array($_REQUEST['category_name'], implode(',', $memberGroups), ++$max, 0, $_REQUEST['category_icon'], $_REQUEST['category_disable_dl'], $_REQUEST['category_js_insert']),
			array('id_cat')
		);
	}
	else
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET
				cat_name = {string:name},
				member_groups = {string:groups},
				cat_icon = {string:icon},
				cat_dl = {int:cat_dl},
				cat_js = {int:cat_js}
			WHERE id_cat = {int:category}',
			array(
				'name' => $_REQUEST['category_name'],
				'groups' => implode(',', $memberGroups),
				'category' => $_REQUEST['category'],
				'icon' => $_REQUEST['category_icon'],
				'cat_dl' => !empty($_REQUEST['category_disable_dl']) ? 1 : 0,
				'cat_js' => !empty($_REQUEST['category_js_insert']) ? 1 : 0,
			)
		);
	}
	if (!empty($arcadeModSettings['arcade_cache_enable'])) {
		@ini_set('memory_limit', '256M');
		$gameRelatedCache = array(
			'arcade_games_suggest',
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
	redirectexit('action=admin;area=arcadecategory');
}

function ArcadeCategoryUpload()
{
	global $smcFunc, $arcadeModSettings, $modSettings, $settings, $boarddir, $txt, $context;

	isAllowedTo('arcade_admin');
	$cropCat = !empty($arcadeModSettings['arcadeCropCatIcons']) ? 1 : 0;
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$context['post_max_size'] = arcade_return_bytes(ini_get('post_max_size')) / 1048576;
	$context['post_max_size'] = preg_replace('/[^0-9\.]/', '', $context['post_max_size']);
	$context['post_max_size'] = round($context['post_max_size'], 2);
	$_REQUEST['category'] = isset($_REQUEST['upcat']) ? (int)$_REQUEST['upcat'] : 0;
	$_SESSION['arcade_cat_file'] = '';
	$width = (!empty($arcadeModSettings['arcade_catWidth'])) && (int)$arcadeModSettings['arcade_catWidth'] > 0 ? (int)$arcadeModSettings['arcade_catWidth'] : 150;
	$height = (!empty($arcadeModSettings['arcade_catHeight'])) && (int)$arcadeModSettings['arcade_catHeight'] > 0 ? (int)$arcadeModSettings['arcade_catHeight'] : 150;
	checkSession();

	if ($smfVersion == 'v2.1')
	{
		$modSettings['cookieTime'] = 3153600;
		createToken('admin', 'post');
	}

	$postVar = !empty($_FILES['attachment']) ? $_FILES['attachment'] : (!empty($_FILES['Filedata']) ? $_FILES['Filedata'] : array());
	list($fileExists, $newname) = array(0, '');

	if (empty($postVar) && isset($_REQUEST['category']))
		ArcadeCategoryEdit();
	elseif (empty($postVar))
		redirectexit('action=admin;area=arcadecategory');

	foreach ($postVar['tmp_name'] as $n => $dummy)
	{
		if ($postVar['name'][$n] == '')
			continue;

		$postVar['name'][$n] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $postVar['name'][$n]);
		$newname = trim(basename($postVar['name'][$n]));
		$target = $settings['default_theme_dir'] . '/images/arc_icons';
		$tmp_name = $postVar['tmp_name'][$n];

		if (mb_substr($newname, -4) !== '.jpg' && mb_substr($newname, -4) !== '.png' && mb_substr($newname, -4) !== '.gif')
			continue;

		if (!is_writable($target) && !chmod($target, 0755))
			$_SESSION['arcade_cat_message'] = (sprintf($txt['arcade_not_writable'], $target));

		if (!file_exists($target . '/' . $newname))
		{
			$fileExists = 0;
			$com = fopen($target . '/' . $newname, "ab");
			$in = fopen($tmp_name, "rb");
			if ($in)
			{
				// pause on every MB
				while ($buff = fread($in, 1048576))
				{
					fwrite($com, $buff);
					sleep(3);
				}
				fclose($in);
			}
			fclose($com);

			if (!file_exists($target . '/' . $newname))
				$_SESSION['arcade_cat_message'] = ($txt['arcade_upload_file']);

			@chmod($target . '/' . $newname, 0666);
			ArcadeImageResize($target . '/' . $newname, $target . '/' . $newname, $width, $height, $cropCat);
			$_SESSION['arcade_cat_file'] = $newname;
		}
		else
		{
			$fileExists = 1;
			ArcadeImageResize($target . '/' . $newname, $target . '/' . $newname, $width, $height, $cropCat);
			$_SESSION['arcade_cat_file'] = $newname;
		}
	}

	if (!empty($newname) && empty($fileExists))
		$_SESSION['arcade_cat_message'] = sprintf($txt['arcade_upload_complete'] ,$newname);
	elseif (!empty($fileExists))
		$_SESSION['arcade_cat_message'] = sprintf($txt['arcade_upload_exists'], $newname);
	else
		$_SESSION['arcade_cat_message'] = $txt['arcade_upload_nofile'];

	if(isset($_REQUEST['category']) && !empty($_SESSION['arcade_cat_file']))
		ArcadeCategoryEdit();
	else
		redirectexit('action=admin;area=arcadecategory');
}

function ArcadeImageResize($src, $dst, $width=150, $height=150, $crop=0)
{
	global $boarddir, $boardurl, $arcadeModSettings;
	@ini_set("gd.jpeg_ignore_warning", 1);
	$bdir = str_replace('\\', '/', $boarddir);
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAncillary.php');

	/*
	$width = $width < 150 ? 150 : $width;
	$height = $height < 150 ? 150 : $height;
	*/

	if (!empty($src) && substr($src, 0, 4) == 'http') {
		$src = str_replace($boardurl, $bdir, $src);
		// $src = dirname($boarddir) . parse_url($src, PHP_URL_PATH);
	}

	if (empty($src) || !file_exists($src))
		return false;

	if(!list($w, $h) = getimagesize($src))
		return false;

	$imgTypes = array('gif', 'jpg', 'png', 'jpeg');
	$type = strtolower(pathinfo($src, PATHINFO_EXTENSION));
	if (!in_array($type, $imgTypes))
		return false;

	$imgInfo   = getimagesize($src);
	$imgMime   = $imgInfo['mime'];
	foreach ($imgTypes as $imgType) {
		if (stripos($imgMime, $imgType) !== FALSE)
			$type = $imgType;
	}
	$type = $type == 'jpeg' ? 'jpg' : $type;

	if (!in_array($type, array('jpg', 'gif', 'png'))) {
		return false;
	}

	/*
	$detectDimensions = !empty($imgInfo) && is_array($imgInfo) && !empty($imgInfo[0]) && !empty($imgInfo[1]) ? array($imgInfo[0], $imgInfo[1]) : array(0, 0);
	if (!empty($detectDimensions[0]) && !empty($detectDimensions[1]) && $detectDimensions[0] < 66 && $detectDimensions[1] < 66)
		return true;
	*/

	list($error, $imagick) = array(false, false);
	if (!empty($arcadeModSettings['arcadeFilterImagickAdmin']) && @extension_loaded('imagick')) {
		$dir = str_replace('\\', '/', dirname($src));
		$rand_src = rtrim($dir, '/') . '/' . bin2hex(random_bytes(10)) . '_' . basename($src);
		clearstatcache();
		try {
			$thumb = new \Imagick($src);
		} catch(ImagickException $e) {
			$error = true;
		}
		if (empty($error)) {
			$w = $thumb->getImageWidth();
			$h = $thumb->getImageHeight();
			if ($w > $h) {
				$resize_width = $w * $height / $h;
				$resize_height = $height;
			}
			else {
				$resize_width = $width;
				$resize_height = $h * $width / $w;
			}
			if ($type != 'gif') {
				$thumb->setCompressionQuality(100);
				if (!$crop) {
					$thumb->resizeImage($width, $height, Imagick::FILTER_CATROM, 0);
				}
				else {
					$thumb->resizeImage($resize_width, $resize_height, Imagick::FILTER_LANCZOS, 0.9);
					$thumb->cropImage($width, $height, ($resize_width - $width) / 2, ($resize_height - $height) / 2);
				}
				clearstatcache(dirname($src));
				$thumb->getImageBlob();
				@unlink($src);
				$thumb->writeImage($src);
				$thumb->destroy();
				$imagick = true;
			}
			else {
				$thumb->setFormat("gif");
				$thumb->destroy();
				$pathname = pathinfo($src, PATHINFO_DIRNAME);
				$filename = pathinfo($src, PATHINFO_FILENAME);
				$newsrc = $pathname . '/' . $filename . '.gif';
				$newsrc = substr($newsrc, 0, 1) ==  substr($boarddir, 0, 1) ? $newsrc : substr($boarddir, 0, 1) . $newsrc;
				$src = substr($src, 0, 1) ==  substr($boarddir, 0, 1) ? $src : substr($boarddir, 0, 1) . $src;
				@rename($src, $newsrc);
				clearstatcache(dirname($src));
				$src = file_exists($newsrc) ? $newsrc : $src;
				$imagePath = ArcadeGetAbsolutePath($src);
				$thumb = new Imagick($imagePath);
				if ($thumb->getNumberImages() > 1) {
					$thumb = $thumb->coalesceImages();

					foreach ($thumb as $frame) {
						if (!empty($crop)) {
							$frame->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
							$frame->cropImage($width, $height, ($resize_width - $width) / 2, ($resize_height - $height) / 2);
						}
						else {
							$frame->resizeImage($width, $height, Imagick::FILTER_CATROM, 0, true);
						}
						$frame->stripImage();
						$frame->setImageCompressionQuality(80);
					}

					$thumb = $thumb->deconstructImages();
					$thumb->writeImages($imagePath, true);
				}
				else {
					$thumb->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
					if (!empty($crop)) {
						$thumb->cropImage($width, $height, ($resize_width - $width) / 2, ($resize_height - $height) / 2);
					}
					else {
						$thumb->resizeImage($width, $height, Imagick::FILTER_CATROM, 0, true);
					}
					$thumb->stripImage();
					$thumb->setImageCompressionQuality(80);
					$thumb->writeImage($imagePath);
				}

				$thumb->clear();
				$thumb->destroy();
				$imagick = true;
			}
			clearstatcache(dirname($src));
			if (!file_exists($src)) {
				if (!empty($arcadeModSettings['arcade_log_imagick'])) {
					$error = sprintf($txt['arcade_imagick_crop_error'], $src);
					log_error($error . ' [1]', 'debug');
				}
				return false;
			}
		}
	}

	if (empty($imagick)) {
		if ($type == 'gif' && class_exists('arcade_gifresizer')) {
			$src = str_replace('\\', '/', $src);
			$boarddirX = str_replace('\\', '/', $boarddir);
			list($len, $len2) = array(strlen($boardurl), strlen($boarddirX));
			$loc = strpos($src, $boardurl) !== FALSE ? ltrim(substr($src, $len), '/') : (strpos($src, $boarddirX) !== FALSE ? ltrim(substr($src, $len2), '/') : '');
			$dstLoc = strpos($dst, $boardurl) !== FALSE ? ltrim(substr($dst, $len), '/') : (strpos($dst, $boarddirX) !== FALSE ? ltrim(substr($dst, $len2), '/') : '');
			list($path, $dstPath) = array($boarddirX . '/' . $loc, $boarddirX . '/' . $dstLoc);
			list($dir, $dir2) = array(dirname($path), dirname($dstPath));
			if (!empty($loc) && !empty($dstLoc) && file_exists($path) && $dir != $boarddirX && $dir2 != $boarddirX) {
				@mkdir($dir2 . '/tempgif', 0755);
				clearstatcache();
				if (is_dir($dir2 . '/tempgif')) {
					$gr = new arcade_gifresizer;
					$gr->temp_dir = $dir2 . '/tempgif';
					$gr->resize($path, $dstPath, $width, $height);
					arcadeRmDir($dir2 . '/tempgif');
					clearstatcache();
				}
			}
			if (file_exists($dstPath)) {
				return true;
			}
		}

		switch($type)
		{
			case 'gif':
				if (!$img = @imagecreatefromgif($src)) {
					$img = imagecreatefromstring(file_get_contents($src));
				}
				break;
			case 'png':
				if (!$img = @imagecreatefrompng($src)) {
					$img = imagecreatefromstring(file_get_contents($src));
				}
				break;
			default:
				if (!$img = @imagecreatefromjpeg($src)) {
					$img = imagecreatefromstring(file_get_contents($src));
				}
		}

		if (empty($img)) {
			return false;
		}

		imageinterlace($img, true);
		if($crop)
		{
			if($w < $width or $h < $height)
				return false;
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$w = $width / $ratio;
		}
		else
		{
			if($w < $width and $h < $height)
				return false;
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
			$x = 0;
		}

		$new = imagecreatetruecolor($width, $height);

		imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
		imagealphablending($new, false);
		imagesavealpha($new, true);
		imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

		switch($type)
		{
			case 'gif':
				imagegif($new, $dst);
				break;
			case 'png':
				imagepng($new, $dst, 0);
				break;
			default:
				imagejpeg($new, $dst, 0);
		}
	}

	return true;
}

function ArcadeBrowseFolders($setPath)
{
	global $scripturl;

	$newNum = isset($_REQUEST['num']) ? floatval($_REQUEST['num']) : 0;
	$page = '?action=admin;area=arcade;sa=settings';
	$output = '';
	isAllowedTo('arcade_admin');

	if ($newNum && !empty($_SESSION['smfArcadeFile'][$newNum]))
		$path = str_replace('file:///', '', $_SESSION['smfArcadeFile'][$newNum]) . '\\';
	else
		$path = "\\";

	if (empty($_SESSION['arcadeFirstLoad']) || !$newNum)
	{
		$_SESSION['arcadeFirstLoad'] = 'page_loaded';
		$path = $setPath;
	}

	// the back button path
	if ($newNum == -1)
		$path = "\\";

	$path = !is_dir($path) ? dirname($path) : $path;
	unset($_SESSION['smfArcadeFile']);
	if (is_dir($path))
	{
		$output = '
		<span id="arcadeBrowseFolders" style="display: none;">
			<select onchange="smfArcadeAdminDirSelect(this.value)">
				<option value="">PATH: ' . $path . '</option>
				<option value="' . $scripturl . $page . ';num=-1;#rarIndex"> &lt;&lt;BACK&lt;&lt; </option>';
		$dh = opendir($path);
		$i=1;
		while (($file = readdir($dh)) !== false) {
			if($file != "." && $file != ".." && $file != "index.php" && $file != ".htaccess" && $file != "error_log" && $file != "cgi-bin") {
				if (stripos("Recycle.Bin", basename($file)) !== false)
					continue;

				if (str_replace('.', '', $file) != $file)
					continue;

				$_SESSION['smfArcadeFile'][$i] = $path . $file;
				$output .= '
					<option value="' . $scripturl . $page . ';num=' . $i . ';#rarIndex">' . $path . $file . '</option>';
				$i++;
			}
		}
		closedir($dh);
		$output .= '
			</select>
		</span>
		<script type="text/javascript">
		function smfArcadeAdminDirSelect(evt)
		{
			window.location.replace(evt);
		}
		</script>';
	}

	return $output;
}

function arcadeSearchArray($search, array $configs) {
	try{
		foreach ($configs as $key => $value) {
			if (is_array($value)){
				array_walk_recursive($value, function($v, $k) use($search, $key, $value, &$val){
					if(strpos($v, $search) !== false )  $val[$key] = $value;
				});
			}
			else {
				if(strpos($value, $search) !== false )  $val[$key] = $value;
			}
		}
		return $val;

	}
	catch (Exception $e) {
		return false;
	}
}

?>