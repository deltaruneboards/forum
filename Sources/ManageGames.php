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

function ManageGames()
{
	global $scripturl, $txt, $context, $boarddir, $sourcedir, $smcFunc, $arcadeModSettings, $settings, $smfVersion;

	require_once($sourcedir . '/Arcade.php');
	require_once($boarddir . '/ArcadeSources/ManageRomGames.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAdmin.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeEmulators.php');

	// Templates
	loadArcade('admin', 'manage_games');
	loadClassFile('Class-Package.php');
	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$saveType = isset($_REQUEST['submit_system']) && in_array($_REQUEST['submit_system'], $classicGameTypes) ? $_REQUEST['submit_system'] : '';
	//arcade_array_insert($txt['arcade_select_gametype_rom'], 'n64', array('new' => 'none'), 'before', false);
	list($context['arcade_rom_game_types_search_list'], $context['arcade_rom_game_types_name_list']) = array(array_keys($txt['arcade_select_gametype_rom']), array_values($txt['arcade_select_gametype_rom']));
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));

	// RC3 patch for the moderation log
	$arcadeModSettings['moderatelog_enabled'] = !empty($arcadeModSettings['modlog_enabled']) ? $arcadeModSettings['modlog_enabled'] : 0;

	if (isset($_REQUEST['uninstall_submit']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'uninstall';

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

	$subActions = array(
		'list' => array('ManageGamesList', 'arcade_admin'),
		'main' => array('ManageGamesList', 'arcade_admin'),
		'rom_list' => array('ManageRomGamesList', 'arcade_admin'),
		'rom_main' => array('ManageRomGamesList', 'arcade_admin'),
		'uninstall' => array('ManageGamesUninstall', 'arcade_admin'),
		'uninstall2' => array('ManageGamesUninstall2', 'arcade_admin'),
		'install' => array('ManageGamesInstall', 'arcade_admin'),
		'install2' => array('ManageGamesInstall2', 'arcade_admin'),
		'upload' => array('ManageGamesUpload', 'arcade_admin'),
		'upload2' => array('ManageGamesUpload2', 'arcade_admin'),
		'rom_install' => array('ManageRomGamesInstall', 'arcade_admin'),
		'rom_install2' => array('ManageRomGamesInstall2', 'arcade_admin'),
		'rom_upload' => array('ManageRomGamesUpload', 'arcade_admin'),
		'rom_upload2' => array('ManageRomGamesUpload2', 'arcade_admin'),
		'rom_git' => array('ManageRetroArchCore', 'arcade_admin'),
		'rom_git2' => array('ManageRetroArchCore2', 'arcade_admin'),
		'rom_git3' => array('ManageRetroArchCore3', 'arcade_admin'),
		'arch_replace' => array('ManageArchReplace', 'arcade_admin'),
		'edit' => array('EditGame', 'arcade_admin'),
		'edit2' => array('EditGame2', 'arcade_admin'),
		'editrom' => array('EditRomGame', 'arcade_admin'),
		'editrom2' => array('EditRomGame2', 'arcade_admin'),
		'export' => array('ExportGameinfo', 'arcade_admin'),
		'romexport' => array('ExportRomGameinfo', 'arcade_admin'),
	);

	// What the user wants to do?
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (stripos($_REQUEST['area'], 'manageromgames') !== FALSE ? 'rom_list' : 'list');

	if (!in_array($_REQUEST['sa'], array('rom_upload', 'rom_upload2', 'rom_git', 'rom_git2', 'rom_git3')))
		unset($_SESSION['smf_arcade_dict']);

	// We have a different template for Retro Arch Games
	if (stripos($_REQUEST['area'], 'manageromgames') !== FALSE)
		loadTemplate('ManageRetroArch');
	else
		loadTemplate('ManageGames');


	// Do we have a reason to allow him/her to do it?
	isAllowedTo($subActions[$_REQUEST['sa']][1]);
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];
	$context['arcade_game_types_search_list']  = !empty($context['arcade_game_types_search_list']) ? $context['arcade_game_types_search_list']  : array();
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

	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$subActions[$_REQUEST['sa']][0]();
}

function ManageGamesList()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $sourcedir, $settings, $smcFunc;

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
				'name' => arcadeFixTagsHTML($dbrow['cat_name'], true)
			);
		$smcFunc['db_free_result']($request);
	}

	$category_data = '';

	foreach ($context['arcade_category'] as $id => $cat)
		$category_data .= '
	<option value="' . $id . '">' . arcadeFixTagsHTML($cat['name'], false) . '</option>';

	if (isset($_REQUEST['category_submit']))
	{
		$_REQUEST['game'] = !empty($_REQUEST['game']) ? $_REQUEST['game'] : array();
		$_REQUEST['game'] = !is_array($_REQUEST['game']) ? array($_REQUEST['game']) : $_REQUEST['game'];
		foreach ($_REQUEST['game'] as $id_game)
			updateGame($id_game, array('category' => (int) $_REQUEST['category']), true);

		redirectexit('action=admin;area=managegames');
	}

	list($filter, $sortType, $sortAlpha) = array('all', !empty($_SESSION['arcade_manage_sort_select_type']) ? $_SESSION['arcade_manage_sort_select_type'] : 'all', !empty($_SESSION['arcade_manage_sort_select_alpha']) ? $_SESSION['arcade_manage_sort_select_alpha'] : 'all');
	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$context['arcade_game_types_search_list'] = array_merge(array('all', 'new'), $classicGameTypes);
	$alphaArray = array_merge(array('All'), range('A', 'Z'));

	if (isset($_REQUEST['filter']) && in_array($_REQUEST['filter'], array('enabled', 'disabled')))
		$filter = $_REQUEST['filter'];
	if (isset($_REQUEST['sortType']) && in_array($_REQUEST['sortType'], $context['arcade_game_types_search_list']))
	{
		$sortType = $_REQUEST['sortType'];
		$_SESSION['arcade_manage_sort_select_type'] = $sortType;
	}
	if (isset($_REQUEST['sortAlpha']) && in_array($_REQUEST['sortAlpha'], $alphaArray))
	{
		$sortAlpha = stripos($_REQUEST['sortAlpha'], 'all') !== false ? mb_strtolower($_REQUEST['sortAlpha']) : $_REQUEST['sortAlpha'];
		$_SESSION['arcade_manage_sort_select_alpha'] = $sortAlpha;
	}

	$listOptions = array(
		'id' => 'games_list',
		'title' => '',
		'items_per_page' => $arcadeModSettings['gamesPerPage'],
		'base_href' => $scripturl . '?action=admin;area=managegames' . ($filter !== 'all' ? ';filter=' . $filter : ''),
		'default_sort_col' => 'name',
		'no_items_label' => sprintf($filter == 'all' ? $txt['arcade_no_games_installed'] : $txt['arcade_no_games_filter'], $scripturl . '?action=admin;area=managegames;sa=install'),
		'use_tabs' => true,
		'list_menu' => array(
			'style' => 'buttons',
			'position' => 'right',
			'columns' => 3,
			'show_on' => 'both',
			'links' => array(
				'show_all' => array(
					'href' => $scripturl . '?action=admin;area=managegames',
					'label' => $txt['manage_games_filter_all'],
					'is_selected' => $filter == 'all',
				),
				'enabled' => array(
					'href' => $scripturl . '?action=admin;area=managegames;filter=enabled',
					'label' => $txt['manage_games_filter_enabled'],
					'is_selected' => $filter == 'enabled',
				),
				'disabled' => array(
					'href' => $scripturl . '?action=admin;area=managegames;filter=disabled',
					'label' => $txt['manage_games_filter_disabled'],
					'is_selected' => $filter == 'disabled',
				),
			),
		),
		'get_items' => array(
			'function' => 'list_getGamesInstalled',
			'params' => array($filter),
		),
		'get_count' => array(
			'function' => 'list_getNumGamesInstalled',
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

						$conf_request = $smcFunc['db_query']('', '
							SELECT id_conflict, id_game, internal_id_conflict, internal_name_conflict, conflict_directory
							FROM {db_prefix}arcade_internal_game_conflicts
							WHERE internal_id_conflict = {int:idgame} || id_game = {int:idgame}
							LIMIT 1',
							array('idgame' => $dbrowData['id'])
						);

						while ($conf_row = $smcFunc['db_fetch_assoc']($conf_request)) {
							$conflictId = $conf_row['internal_id_conflict'];
							$dbgameId = $conf_row['id_game'];
						}
						$smcFunc['db_free_result']($conf_request);
						if (!empty($conflictId) || !empty($dbgameId)) {
							$conflictCss = $dbgameId == $dbrowData['id'] ? 'style="font-style: oblique;" title="' . $txt['arcade_conflict_primary'] . '" ' : 'style="font-style: italic;" class="alert" title="' . $txt['arcade_conflict_subsequent'] . '" ';
							$context['html_headers'] .= '
		<script type="text/javascript">
			function arcadeConflict_' . $dbrowData['id'] . '() {
				setInterval(function() {
					var arcadeGame = document.getElementById("game_' . $dbrowData['id'] . '");
					if (arcadeGame.style.opacity == "0.5")
						arcadeGame.style.opacity = "1.0";
					else
						arcadeGame.style.opacity = "0.5";
				}, ' . ($dbgameId == $dbrowData['id'] ? '2000' : '500') . ');
			}
			if (window.addEventListener)
				window.addEventListener("load", arcadeConflict_' . $dbrowData['id'] . ', false);
			else if (window.attachEvent)
				window.attachEvent("onload", arcadeConflict_' . $dbrowData['id'] . ');
			else
				window.onload = arcadeSortListOptions();
		</script>';
						}
						list($saveTypeArray, $saveTypeArrayLong) = array(explode('|', $txt['arcade_select_gametype']), explode('|', $txt['arcade_select_gametype_english']));
						$key = array_search($dbrowData['submit_system'], $saveTypeArray);
						$link = '<a ' . $conflictCss . 'id="game_' . $dbrowData['id'] . '" href="' . $dbrowData['href'] . '">' . $dbrowData['name'] . '</a>';
						$submitSystem = $key !== false && !empty($saveTypeArrayLong[$key]) ? $saveTypeArrayLong[$key] : $dbrowData['submit_system'];
						if (!empty($dbrowData['error']))
							$link .= '<div class="alert smalltext">' . $dbrowData['error'] . '</div>';
						$link .= !empty($_SESSION['arcade_isMobile']) ? '' : '<div style="display: inline;float: right;">' . $submitSystem . '</div>';
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
						$link = arcadeFixTagsHTML($dbrowData['category']['name'], true);

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
			'href' => $scripturl . '?action=admin;area=managegames' . ($filter !== 'all' ? ';filter=' . $filter : ''),
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
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=managegames;sa=list';
	$context['settings_title'] = $txt['arcade_manage_games'];

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	// filter the array for a valid search argument
	$_SESSION['search_game_list'] = isset($_GET['search_game']) ? preg_replace('~[^a-z0-9]+~i', '', trim($_GET['search_game'])) : (!empty($_SESSION['search_game_list']) ? $_SESSION['search_game_list'] : '');
	if (!empty($_SESSION['search_game_list']))
	{
		$listArray = $context['games_list']['rows'];
		foreach ($context['games_list']['rows'] as $id => $dbrow)
		{
			if (!empty($dbrow['data']['name']['value']))
			{
				$dbgameName = preg_replace('#<a.*?>([^<]*)</a>#i', '$1', $dbrow['data']['name']['value']);
				if (strlen($_SESSION['search_game_list']) == 1 && strtolower(substr($dbgameName, 0, 1))  != strtolower($_SESSION['search_game_list']))
					unset($context['games_list']['rows'][$id]);
				elseif (stripos($dbrow['data']['name']['value'], $_SESSION['search_game_list']) === false)
					unset($context['games_list']['rows'][$id]);
			}
		}

		if (empty($context['games_list']['rows']))
			$context['games_list']['rows'] = $listArray;
	}

	// add a search function to the generic list
	$clickFunction = 'window.location.href = \'' . $scripturl . '?action=admin;area=managegames;search_game=\' + document.getElementById(\'search_game\').value + \';sesc=' . $context['session_id'] . ';\'';
	$context['games_list']['title'] = '<span title="' . $txt['arcade_manage_games_clear_search']. '"><a href="' . $scripturl . '?action=admin;area=managegames;search_game=;sesc=' . $context['session_id'] . '">' . $txt['arcade_manage_games_list'] . '</a></span> <span style="float: right;"><input id="gameSearchButton" type="button" onclick="' . $clickFunction . '" value="' . $txt['arcade_manage_games_list_search'] . '"> <input id="search_game" onkeydown="if (event.keyCode == 13){event.preventDefault();document.getElementById(\'gameSearchButton\').click();}" type="text" name="search_game" style="width: 8em;" /></span>';
	$context['sub_template'] = 'manage_games_list';
	$context['html_headers'] .= '
	<script type="text/javascript">
		function arcadeSortListOptions() {
			var arcadeAlphaVal = document.getElementById("arcadealphaval");
			var arcadeTypeVal = document.getElementById("arcadetypeval");
			arcadeAlphaVal.onchange = function arcadeSortListAlphaVal() {
				window.location.assign("' . $scripturl . '?action=admin;area=managegames;sa=main;sortAlpha=" + arcadeAlphaVal.value);
			}
			arcadeTypeVal.onchange = function arcadeSortListTypeVal() {
				window.location.assign("' . $scripturl . '?action=admin;area=managegames;sa=main;sortType=" + arcadeTypeVal.value);
			}
		}
		$(document).ready(function() {
			arcadeSortListOptions();
		});
	</script>';
}

function ManageGamesInstall()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $smcFunc, $sourcedir, $smcFunc, $settings, $smfVersion;

	isAllowedTo('arcade_admin');

	if ($smfVersion === 'v2.1')
		createToken('admin', 'post');

	loadClassFile('Class-Package.php');

	if (!isset($_REQUEST['submit_system']))
		updateGamePendingCache();

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	$context['sub_template'] = 'manage_games_list';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=managegames;sa=install';
	$context['settings_title'] = $txt['arcade_manage_games'];
	$translate = !empty($arcadeModSettings['arcade_adjust_desc_admin']) || !empty($arcadeModSettings['arcade_adjust_desc_info']) ? 1 : 0;
	$request = $smcFunc['db_query']('', '
		SELECT id_cat, cat_name
		FROM {db_prefix}arcade_categories'
	);

	$context['arcade_category'][0] = array(
		'id' => 0,
		'name' => $txt['pdl_unassigned']
	);

	while ($dbrow = $smcFunc['db_fetch_assoc']($request))
		$context['arcade_category'][$dbrow['id_cat']] = array(
			'id' => $dbrow['id_cat'],
			'name' => arcadeFixTagsHTML($dbrow['cat_name'], true)
		);
	$smcFunc['db_free_result']($request);


	$category_data = '';

	foreach ($context['arcade_category'] as $id => $cat)
		$category_data .= '
	<option value="' . $id . '">' . arcadeFixTagsHTML($cat['name'], true) . '</option>';
	$dbgamePacks = array_filter(array_column(list_getGamesInstall('0', list_getNumGamesInstall(), 'id_file'), 'name'), function($var) {return substr($var, 0, 8) == 'GamePack' ? false : true;});
	$cats = (!empty($category_data) && isset($_REQUEST['sa']) && !empty($dbgamePacks) && $_REQUEST['sa'] == 'install')?  array(
				'position' => 'below_table_data',
				'value' => $txt['install_category'] . ' <select name="set_category">' . $category_data . '</select>',
				'class' => 'titlebg',
				'style' => 'text-align: right;') : array();

	$listOptions = array(
		'id' => 'games_list',
		'title' => '',
		'items_per_page' => $arcadeModSettings['gamesPerPage'],
		'base_href' => $scripturl . '?action=admin;area=managegames;sa=install',
		'default_sort_col' => 'name',
		'no_items_label' => sprintf($txt['arcade_no_games_available_for_install'], $scripturl . '?action=admin;area=managegames;sa=upload'),
		'get_items' => array(
			'function' => 'list_getGamesInstall',
			'params' => array(
			),
		),
		'get_count' => array(
			'function' => 'list_getNumGamesInstall',
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
						'format' => '<input type="checkbox" name="file[]" value="%d" class="check installfiles" />',
						'params' => array('id_file' => false),
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
			'href' => $scripturl . '?action=admin;area=managegames;sa=install2;sesc=' . $context['session_id'],
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
			$cats,
			array(
				'position' => 'below_table_data',
				'value' => '<input onclick="return arcadeDelClick()" id="quick_del" type="submit" name="delete_submit" value="' . $txt['quickmod_delete_selected'] . '" />',
				'class' => 'arcade_install_button',
				'style' => 'display: inline;float: left;',
			),
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="install_submit" value="' . $txt['quickmod_install_selected'] . '" />',
				'class' => 'arcade_install_button',
				'style' => 'display: inline;float: right;',
			),
		),
	);

	$context['html_headers'] .= '
	<script type="text/javascript">
		function arcadeDelClick(val) {
			var myConf = confirm("' . $txt['arcade_are_you_sure_delete'] . '");
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
	</script>
	<link href="' . $settings['default_theme_url'] . '/css/arcade-upload2.css?' . $suffixVersion . '" rel="stylesheet" type="text/css" />';

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);
}

function ManageGamesInstall2()
{
	global $smcFunc, $context, $smfVersion, $arcadeModSettings, $boarddir, $sourcedir;

	isAllowedTo('arcade_admin');
	checkSession('post');
	validateToken('admin', 'post', false);

	if (!isset($_REQUEST['file']) && isset($_REQUEST['done']))
		redirectexit('action=admin;area=managegames;sa=list;sesc=' . $context['session_id']);
	if (!isset($_REQUEST['file'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected1', false);
	}

	if (!is_array($_REQUEST['file']))
		$dbgames = array($_REQUEST['file']);
	else
		$dbgames = $_REQUEST['file'];

	foreach ($dbgames as $id => $dbgame)
		$dbgames[$id] = (int) $dbgame;

	$dbgames = array_unique($dbgames);
	$arcadeModSettings['gamesDirectory'] = str_replace('\\', '/', $arcadeModSettings['gamesDirectory']);
	$arcadeModSettings['gamesDirectory'] = rtrim($arcadeModSettings['gamesDirectory'], '/');
	$upload_directory = str_replace('\\', '/', $boarddir) . '/games_upload';

	if (count($dbgames) == 0) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected2', false);
	}

	if (isset($_REQUEST['install_submit']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_file, file_type, status, submit_system
			FROM {db_prefix}arcade_files
			WHERE id_file IN ({array_int:games})
				AND status != 0',
			array(
				'games' => $dbgames,
			)
		);

		$unpack = array();
		$install = array();

		while ($dbrow = $smcFunc['db_fetch_assoc']($request))
		{
			// Need decompression?
			if ($dbrow['file_type'] !== 'game')
				$unpack[] = $dbrow['id_file'];
			else
				$install[] = $dbrow['id_file'];
		}
		$smcFunc['db_free_result']($request);

		// Unpack games first
		if (!empty($unpack))
		{
			$saveType = unpackGames($unpack);
		}

		if (!empty($install))
		{
			$_SESSION['qaction'] = 'install';
			$setCategory = isset($_POST['set_category']) ? floatval($_POST['set_category']) : 0;
			$_SESSION['qaction_data'] = installGames($install, $setCategory, false);
			$_SESSION['qaction_data'] = array_filter($_SESSION['qaction_data']);
			require_once($sourcedir . '/ArcadeMaintenance.php');
			ArcadeFixCategories('uninstall');
			if (!empty($arcadeModSettings['arcade_install_clean_db']))
				ArcadeMaintenanceDatabase();
		}

		redirectexit('action=admin;area=managegames;sa=install;done;sesc=' . $context['session_id']);
	}
	else
	{
		list($id_games, $id_files, $id_game_files) = array(array(), array(), array());

		foreach ($dbgames as $dbgame)
			$id_files[] = $dbgame;

		if (!empty($id_files))
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_file, id_game, game_file, game_directory, status, submit_system
				FROM {db_prefix}arcade_files
				WHERE id_file IN({array_int:games})
					AND status = 10',
				array(
					'games' => $id_files
				)
			);

			while ($dbrow = $smcFunc['db_fetch_assoc']($request))
			{
				$id_games[] = !empty($dbrow['id_game']) ? (int)$dbrow['id_game'] : 0;
				$id_game_files[] = !empty($dbrow['id_file']) ? $dbrow['id_file'] : 0;
				$dbrow['game_directory'] = !empty($dbrow['game_directory']) ? trim($dbrow['game_directory'], '/') : '';
				$dbgamePath = $upload_directory . (!empty($dbrow['game_directory']) ? '/' . $dbrow['game_directory'] : '');
				$dbgamePath = rtrim($dbgamePath, '/');
				$files = ArcadeAdminScanDir($dbgamePath, '');
				$ibp = isset($dbrow['game_file']) && strlen($dbrow['game_file']) > 4 && mb_substr($dbrow['game_file'], -4) == '.php' ? basename($dbgame['game_file']) : '';
				if ((!empty($dbrow['game_directory'])) && $dbgamePath !== $upload_directory)
				{
					foreach ($files as $file) {
						if (file_exists($dbgamePath . '/' . basename($file)) && !is_dir($dbgamePath . '/' . basename($file)))
							unlink($dbgamePath . '/' . basename($file));
					}
					if (is_dir($dbgamePath))
					{
						$gfiles = ArcadeAdminScanDir($dbgamePath, '');
						if (empty($gfiles))
							rmdir($dbgamePath);
						elseif ((count($gfiles) == 1) && $gfiles[0] == 'master-info.xml')
						{
							unlink($dbgamePath . '/master-info.xml');
							rmdir($dbgamePath);
						}
					}

					$files = ArcadeAdminScanDir($dbgamePath, '');
					if(is_dir($dbgamePath))
					{
						foreach ($files as $file) {
							if (file_exists($file) && !is_dir($file)) {
								unlink($file);
								clearstatcache($file);
							}
							elseif (is_dir($file)) {
								arcadeRmdir($file);
								clearstatcache($file);
								if (is_dir($file))
									rmdir($file);
							}
						}

						arcadeRmdir($dbgamePath);

						if ($arcadeModSettings['gamesDirectory'] . '/' !== $upload_directory . '/' . dirname($dbrow['game_directory']))
						{
							$allFiles = ArcadeAdminScanDir($upload_directory . '/' . dirname($dbrow['game_directory']), '');
							if (empty($allFiles))
								arcadeRmdir($upload_directory . '/' . dirname($dbrow['game_directory']));
						}
					}
				}
				if(!empty($dbrow['game_file']))
				{
					foreach ($files as $file)
						if (file_exists($upload_directory . '/' . $file))
							unlink($upload_directory . '/' . $file);

					if (file_exists($upload_directory . '/' . $dbrow['game_file']))
						@unlink($upload_directory . '/' . $dbrow['game_file']);
				}
			}

			$smcFunc['db_free_result']($request);

			clearstatcache();

			//if (!empty($id_games))
				//uninstallGames($id_games, true);

			foreach ($id_game_files as $id)
			{
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_files
					WHERE id_file = {int:file}',
					array(
						'file' => $id,
					)
				);
			}
		}

		redirectexit('action=admin;area=managegames;sa=install;sesc=' . $context['session_id']);
	}
}

function ManageGamesUninstall()
{
	global $smcFunc, $context, $txt, $scripturl, $smfVersion, $arcadeModSettings;

	isAllowedTo('arcade_admin');
	$area = isset($_REQUEST['area']) && stripos($_REQUEST['area'], 'manageromgames') !== FALSE ? 'manageromgames' : 'managegames';
	$rom = isset($_REQUEST['area']) && stripos($_REQUEST['area'], 'manageromgames') !== FALSE ? '_rom' : '';

	if ($smfVersion === 'v2.1')
		createToken('admin', 'post');

	if (!isset($_REQUEST['game'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected3', false);
	}

	if (!is_array($_REQUEST['game']))
		$dbgames = array($_REQUEST['game']);
	else
		$dbgames = $_REQUEST['game'];

	foreach ($dbgames as $id => $dbgame)
		$dbgames[$id] = (int) $dbgame;
	$dbgames = array_unique($dbgames);

	if (count($dbgames) == 0) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected4', false);
	}

	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_name, rom_flag
		FROM {db_prefix}arcade_games
		WHERE id_game IN ({array_int:games})',
		array(
			'games' => $dbgames,
			'rom_flag' => 0,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected5', false);
	}

	$context['games'] = array();

	while ($dbrow = $smcFunc['db_fetch_assoc']($request))
		$context['games'][$dbrow['id_game']] = array(
			'id' => $dbrow['id_game'],
			'id_file' => $dbrow['id_game'],
			'name' => $dbrow['game_name'],
		);
	$smcFunc['db_free_result']($request);

	$context['confirm_url'] = $scripturl . '?action=admin;area=' . $area . ';sa=uninstall2;sesc=' . $context['session_id'];
	$context['confirm_title'] = $txt['arcade_uninstall_games'];
	$context['confirm_text'] = $txt['arcade_following_games_uninstall'];
	$context['confirm_button'] = $txt['arcade_uninstall_games'];

	// Template
	$context['sub_template'] = 'manage' . $rom . '_games_uninstall_confirm';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=' . $area . ';sa=uninstall';
	$context['settings_title'] = $txt['arcade_manage_games'];
}

function ManageGamesUninstall2()
{
	global $smcFunc, $context, $boarddir, $sourcedir, $smfVersion;

	isAllowedTo('arcade_admin');
	checkSession('request');
	$area = isset($_REQUEST['area']) && stripos($_REQUEST['area'], 'manageromgames') !== FALSE ? 'manageromgames' : 'managegames';
	$rom = isset($_REQUEST['area']) && stripos($_REQUEST['area'], 'manageromgames') !== FALSE ? 'rom_' : '';

	if ($smfVersion === 'v2.1')
		validateToken('admin', 'post', false);

	if (!isset($_REQUEST['game'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected6', false);
	}

	if (!is_array($_REQUEST['game']))
		$dbgames = array($_REQUEST['game']);
	else
		$dbgames = $_REQUEST['game'];

	foreach ($dbgames as $id => $dbgame)
		$dbgames[$id] = (int) $dbgame;
	$dbgames = array_unique($dbgames);

	if (count($dbgames) == 0) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected7', false);
	}

	$request = $smcFunc['db_query']('', '
		SELECT id_game, game_name, rom_flag
		FROM {db_prefix}arcade_games
		WHERE id_game IN ({array_int:games})',
		array(
			'games' => $dbgames,
			'rom_flag' => 0,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_games_selected8', false);
	}

	list($context['games'], $id_game)  = array(array(), array());

	while ($dbrow = $smcFunc['db_fetch_assoc']($request)) {
		$context['games'][$dbrow['id_game']] = array(
			'id_game' => $dbrow['id_game'],
			'id_file' => $dbrow['id_game'],
			'name' => $dbrow['game_name'],
		);
	}
	$smcFunc['db_free_result']($request);

	foreach ($context['games'] as $dbgame) {
		$id_game[] = $dbgame['id_game'];
	}

	if (!empty($dbgameDirs) && count($dbgameDirs) > 1 && count(array_unique($dbgameDirs)) != count($dbgameDirs))
		unset($_REQUEST['remove_files']);

	$_SESSION['qaction'] = 'uninstall';
	$_SESSION['qaction_data'] = !empty($rom) ? uninstallRomGames($id_game, isset($_REQUEST['remove_files'])) : uninstallGames($id_game, isset($_REQUEST['remove_files']));
	$_SESSION['qaction_data'] = array_filter($_SESSION['qaction_data']);
	require_once($sourcedir . '/ArcadeMaintenance.php');
	ArcadeFixCategories('uninstall');
	redirectexit('action=admin;area=' . $area . ';sa=' . $rom . 'list;done;sesc=' . $context['session_id']);
}

function ManageGamesUpload()
{
	global $scripturl, $txt, $arcadeModSettings, $modSettings, $context, $boarddir, $sourcedir, $smcFunc, $settings, $user_settings, $cookiename, $user_info, $smfVersion;

	isAllowedTo('arcade_admin');
	$upload_directory = str_replace('\\', '/', $boarddir) . '/games_upload';
	$arcadeModSettings['gamesDirectory'] = !empty($arcadeModSettings['gamesDirectory']) ? $arcadeModSettings['gamesDirectory'] : str_replace('\\', '/', $boarddir) . '/Games';
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

	if (!is_writable($arcadeModSettings['gamesDirectory']) && !chmod($arcadeModSettings['gamesDirectory'], 0755)) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($arcadeModSettings['gamesDirectory']));
	}
	if (!is_writable($upload_directory) && !chmod($upload_directory, 0755)) {
		arcadeClearSession();
		fatal_lang_error('arcade_not_writable', false, array($upload_directory));
	}

	$context['post_max_size'] = arcade_return_bytes(ini_get('post_max_size')) / 1048576;
	$context['post_max_size'] = preg_replace('/[^0-9\.]/', '', $context['post_max_size']);
	$context['post_max_size'] = round($context['post_max_size'], 2);

	// Template
	$context['sub_template'] = 'manage_games_upload';
}

function ManageGamesUpload2()
{
	global $txt, $arcadeModSettings, $modSettings, $context, $cookiename, $smfVersion, $boarddir;

	isAllowedTo('arcade_admin');
	//checkSession('post');
	$postVar = !empty($_FILES['attachment']) ? $_FILES['attachment'] : array();
	$upload_directory = str_replace('\\', '/', $boarddir . '/games_upload');

	// HTML5 / jQuery 1MB chunk uploads
	if (empty($postVar) && isset($_FILES['upl']))
	{
		// A list of permitted file extensions
		$allowed = class_exists('RarArchive') ? array('zip', 'tar', 'gz', 'tar.gz', 'rar', 'ZIP', 'TAR', 'GZ', 'TAR.gz', 'TAR.GZ', 'RAR') : array('zip', 'tar', 'gz', 'tar.gz', 'ZIP', 'TAR', 'GZ', 'TAR.gz', 'TAR.GZ');
		if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0)
		{

			$extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

			if(!in_array(strtolower($extension), $allowed))
			{
				echo '{"status":"error"}';
				exit;
			}

			$_FILES['upl']['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['upl']['name']);
			$target = $upload_directory;
			$target = rtrim($target, '/');
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
		redirectexit('action=admin;area=managegames;sa=install');
	elseif (empty($postVar))
		die($txt['arcade_upload_nofile']);

	foreach ($postVar['tmp_name'] as $n => $dummy)
	{
		if ($postVar['name'][$n] == '')
			continue;

		$postVar['name'][$n] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $postVar['name'][$n]);
		$newname = trim(basename($postVar['name'][$n]));
		$target = $upload_directory;
		$target = rtrim($target, '/');

		$tmp_name = $postVar['tmp_name'][$n];

		if (mb_substr(mb_strtolower($newname), -3) !== '.gz' && mb_substr(mb_strtolower($newname), -4) !== '.tar' && mb_substr(mb_strtolower($newname), -4) !== '.zip' && mb_substr(mb_strtolower($newname), -4) !== '.rar')
			continue;

		if (!class_exists('RarArchive') && mb_substr(mb_strtolower($newname), -4) == '.rar')
			continue;

		if ($target != $upload_directory)
		{
			if (!file_exists($target) && !mkdir($target, 0755) && empty($arcadeModSettings['arcadeUploadSystem'])) {
				arcadeClearSession();
				fatal_lang_error('arcade_not_writable', false, array($target));
			}
			elseif (!file_exists($target) && !mkdir($target, 0755)) {
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
				fatal_lang_error('arcade_upload_file_size');
			}

			$fileExists = 0;
			$com = fopen($target . '/' . $newname, "ab");
			$in = fopen($tmp_name, "rb");
			if ($in)
			{
				// pause on every MB
				while ($buff = fread($in, 4096))
				{
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

			@chmod($target . '/' . $newname, 0755);
		}
		else
			$fileExists = 1;
	}

	if (empty($arcadeModSettings['arcadeUploadSystem']))
		redirectexit('action=admin;area=managegames;sa=install');
	elseif (!empty($newname) && empty($fileExists))
		die(sprintf($txt['arcade_upload_complete'] ,$newname));
	elseif (!empty($fileExists))
		die(sprintf($txt['arcade_upload_exists'], $newname));
	else
		die($txt['arcade_upload_nofile']);

}

function EditGame()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $modSettings, $settings, $boardurl, $context, $boarddir, $smcFunc, $sourcedir, $smfVersion;

	unset($_SESSION['arcade_rom_initiate']);
	$context['game_permissions'] = $arcadeModSettings['arcadePermissionMode'] > 2;
	$context['edit_page'] = 'basic';
	$arcadeModSettings['arcadeJsInsertDefault'] = !empty($arcadeModSettings['arcadeJsInsertDefault']) && in_array($arcadeModSettings['arcadeJsInsertDefault'], array(0, 1, 2)) ? $arcadeModSettings['arcadeJsInsertDefault'] : 2;
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$gamesUrl = !empty($arcadeModSettings['gamesUrl']) ? $arcadeModSettings['gamesUrl'] : $boardurl . '/Games';
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcadeModSettings['arcadeVersion']));
	isAllowedTo('arcade_admin');
	$modSettings['cookieTime'] = 3153600;
	createToken('admin', 'post');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');

	// Load game data unless it has been loaded by EditGame2
	if (!isset($context['game']))
	{
		if (!isset($_REQUEST['game'])) {
			arcadeClearSession();
			fatal_lang_error('arcade_no_games_selected', false);
		}
		$id = loadGame((int) $_REQUEST['game'], true);

		if ($id === false) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}

		$dbgame = &$context['arcade']['game_data'][$id];
		list($conflictId, $dbgameId, $primaryConflictGame, $subsequentData, $primaryGameName, $subsequentDataLink, $subsequentConflictGames) = array(0, 0, '', '', '', '', array());
		if (!empty($dbgame['rom_flag']) || !empty($dbgame['rom_game'])) {
			redirectexit($scripturl . '?action=admin;area=manageromgames;sa=editrom;game=' . $dbgame['id_game']);
		}

		$conf_request = $smcFunc['db_query']('', '
			SELECT id_conflict, id_game, internal_id_conflict, internal_name_conflict, conflict_directory
			FROM {db_prefix}arcade_internal_game_conflicts
			WHERE internal_id_conflict = {int:idgame} || id_game = {int:idgame}
			LIMIT 1',
			array('idgame' => $dbgame['id_game'])
		);

		while ($conf_row = $smcFunc['db_fetch_assoc']($conf_request)) {
			$conflictId = $conf_row['internal_id_conflict'];
			$dbgameId = $conf_row['id_game'];
		}
		$smcFunc['db_free_result']($conf_request);
		if (!empty($dbgameId) && $dbgameId == $dbgame['id_game']) {
			$conf_request = $smcFunc['db_query']('', '
				SELECT id_game, internal_id_conflict
				FROM {db_prefix}arcade_internal_game_conflicts
				WHERE id_game = {int:idgame}
				ORDER BY id_game ASC',
				array('idgame' => $dbgame['id_game'])
			);

			while ($conf_row = $smcFunc['db_fetch_assoc']($conf_request)) {
				$subsequentConflictGames[] = $conf_row['internal_id_conflict'];
			}
			$smcFunc['db_free_result']($conf_request);
			foreach ($subsequentConflictGames as $subsequentConflictGame) {
				$conf_request = $smcFunc['db_query']('', '
					SELECT id_game, game_name, rom_flag
					FROM {db_prefix}arcade_games
					WHERE id_game = {int:idgame} AND rom_flag = {int:rom_flag}
					ORDER BY game_name ASC',
					array(
						'idgame' => $subsequentConflictGame,
						'rom_flag' => 0,
					)
				);

				while ($conf_row = $smcFunc['db_fetch_assoc']($conf_request)) {
					$subsequentData .= ' <a href="' . $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $conf_row['id_game'] . '" title="' . sprintf($txt['arcade_conflict_msg_edit'], $conf_row['game_name']) . '">' . $conf_row['game_name'] . '</a>,';
				}
				$smcFunc['db_free_result']($conf_request);
			}
			$subsequentData = rtrim($subsequentData, ',');
			$subsequentDataLink = sprintf($txt['arcade_conflict_subsequent_list'], $subsequentData);
		}
		elseif (!empty($conflictId) && $conflictId == $dbgame['id_game']) {
			$conf_request = $smcFunc['db_query']('', '
				SELECT id_game, game_name, rom_flag
				FROM {db_prefix}arcade_games
				WHERE id_game = {int:idgame} AND rom_flag = {int:rom_flag}
				LIMIT 1',
				array(
					'idgame' => $dbgameId,
					'rom_flag' => 0,
				)
			);

			while ($conf_row = $smcFunc['db_fetch_assoc']($conf_request)) {
				$primaryGameName = $conf_row['game_name'];
			}
			$smcFunc['db_free_result']($conf_request);
			$dbgameLink = '<a href="' . $scripturl . '?action=admin;area=managegames;sa=edit;game=' . $dbgameId . '" title="' . sprintf($txt['arcade_conflict_msg_edit'], $primaryGameName) . '">' . $primaryGameName . '</a>';
			$primaryConflictGame = sprintf($txt['arcade_conflict_primary_game'], $dbgameLink);
		}
		$arcadeModSettings['arcadeViewCovers'] = !empty($arcadeModSettings['arcadeViewCovers']) ? intval($arcadeModSettings['arcadeViewCovers']) : 0;
		$context['coverIconEnabled'] = $arcadeModSettings['arcadeViewCovers'] > 1 ? true : false;
		$coverfile = !empty($dbgame['submit_system']) && $dbgame['submit_system'] == 'rom' ? 'romgamecover.gif' : 'arcadegamecover.png';
		$imgFile1 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail'] : $dbgame['thumbnail'];
		$imgFile2 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail_small'] : $dbgame['thumbnail_small'];
		$imgFile3 = !empty($dbgame['cover_icon']) && !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['cover_icon'] : $dbgame['cover_icon'];
		$imgPath1 = !empty($dbgame['thumbnail']) && file_exists($gamesDirectory . '/' . $imgFile1) ? $gamesUrl . '/' . $imgFile1 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath2 = !empty($dbgame['thumbnail_small']) && file_exists($gamesDirectory . '/' . $imgFile2) ? $gamesUrl . '/' . $imgFile2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath3 = !empty($dbgame['cover_icon']) && file_exists($gamesDirectory . '/' . $imgFile3) ? $gamesUrl . '/' . $imgFile3 : $settings['default_theme_url'] . '/images/arc_icons/' . $coverfile;
		$hide = !empty($dbgame['icon_position_hide']) ? (int)$dbgame['icon_position_hide'] : 0;
		$extra = !empty($dbgame['extra_data']) && arcadeIsSerialized($dbgame['extra_data']) ? arcade_safe_unserialize($dbgame['extra_data']) : array();
		$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
		$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
		$coverfile = $dbgame['submit_system'] == 'rom' ? 'romgamecover.gif' : 'arcadegamecover.png';
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
			'covericon' => stripos($imgPath3, $settings['default_theme_url'] . '/images/arc_icons/' . $coverfile) !== FALSE ? '<li style="font-size: 8pt;">[cover_icon] ' . $txt['arcade_warning_no_gameimage'] . '</li>' : '',
			'description' => htmlspecialchars(str_replace("&apos;", "'", $dbgame['description']), ENT_QUOTES | ENT_HTML5, 'UTF-8', false),
			'help' => htmlspecialchars(str_replace("&apos;", "'", $dbgame['help']), ENT_QUOTES | ENT_HTML5, 'UTF-8', false),
			'game_file' => (string)$dbgame['game_file'],
			'game_directory' => $dbgame['game_directory'],
			'submit_system' => $dbgame['submit_system'],
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
		$context['game']['extra_data']['type'] = !empty($context['game']['extra_data']['type']) ? $context['game']['extra_data']['type'] : 'normal';

		if (!is_array($context['game']['extra_data']) || isset($_REQUEST['detect']))
		{
			require_once($boarddir . '/ArcadeSources/SWFReader.php');
			$swf = new SWFReader();

			if (substr($dbgame['game_file'], -3) == 'swf')
			{
				$swf->open($upload_directory . '/' . $dbgame['game_directory'] . '/' . $dbgame['game_file']);

				$context['game']['extra_data'] = array(
					'width' => $swf->header['width'],
					'height' => $swf->header['height'],
					'flash_version' => $swf->header['version'],
					'background_color' => $swf->header['background'],
					'type' => (!empty($context['game']['extra_data']['type'])) && $context['game']['extra_data']['type'] == 'fullscreen' ? 'fullscreen' : 'normal',
				);

				$swf->close();
			}
			else
			{
				$context['game']['extra_data'] = array(
					'width' => '',
					'height' => '',
					'flash_version' => '',
					'background_color' => array('', '', ''),
					'type' => '',
				);
			}
		}

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

	// Load Sumbit Systems
	if (!isset($context['submit_systems']))
		$context['submit_systems'] = SubmitSystemInfo('*') + array('rom' => $txt['arcade_rom_switch']['rom']);

	$context['template_layers'][] = 'edit_game';
	$context['sub_template'] = 'edit_game_basic';
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=managegames;sa=edit';
	$context['settings_title'] = $txt['arcade_manage_games'];
	$context['html_headers'] .= '
	<script>
		$(function() {
			$( "#thumbnail" ).after(\'<input style="width: 99%;margin-bottom: 0.5rem;" id="thumb1" name="thumb1" type="file" value="' . $txt['arcadeRomCoreBrowse'] . '" accept=".png,.jpg,.gif" />\');
			$( "#thumbnail_small" ).after(\'<input style="width: 99%;margin-bottom: 0.5rem;" id="thumb2" name="thumb2" type="file" value="' . $txt['arcadeRomCoreBrowse'] . '" accept=".png,.jpg,.gif" />\');
			$( "#cover_icon" ).after(\'<input style="width: 99%;margin-bottom: 0.5rem;" id="covericon" name="covericon" type="file" value="' . $txt['arcadeRomCoreBrowse'] . '" accept=".png,.jpg,.gif" />\');
			$("#thumb1").change(function() {
				$("#arcade_preview").trigger("click");
			});
			$("#thumb2").change(function() {
				$("#arcade_preview").trigger("click");
			});
			$("#covericon").change(function() {
				$("#arcade_preview").trigger("click");
			});
			$("#thumb1").on( "click", function() {
			  $("#fileflag").val("flag");
			});
			$("#thumb2").on( "click", function() {
			  $("#fileflag").val("flag");
			});
			$("#covericon").on( "click", function() {
			  $("#fileflag").val("flag");
			});
		});
	</script>';
}

function EditGame2()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $settings, $boardurl, $boarddir, $smcFunc, $sourcedir, $smfVersion;

	$arcadeModSettings['arcadeViewCovers'] = !empty($arcadeModSettings['arcadeViewCovers']) ? intval($arcadeModSettings['arcadeViewCovers']) : 0;
	$context['coverIconEnabled'] = $arcadeModSettings['arcadeViewCovers'] > 1 ? true : false;
	$context['game_permissions'] = $arcadeModSettings['arcadePermissionMode'] > 2;
	$arcadeModSettings['arcadeJsInsertDefault'] = !empty($arcadeModSettings['arcadeJsInsertDefault']) && in_array($arcadeModSettings['arcadeJsInsertDefault'], array(0, 1, 2)) ? $arcadeModSettings['arcadeJsInsertDefault'] : 2;
	$gamesDirectory = !empty($arcadeModSettings['gamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['gamesDirectory']) : str_replace('\\', '/', $boarddir) . '/Games';
	$romGamesDirectory = !empty($arcadeModSettings['romGamesDirectory']) ? str_replace('\\', '/', $arcadeModSettings['romGamesDirectory']) : str_replace('\\', '/', $boarddir) . '/ArcadeRetroArch/roms';
	$gamesUrl = !empty($arcadeModSettings['gamesUrl']) ? $arcadeModSettings['gamesUrl'] : $boardurl . '/Games';
	$context['edit_page'] = 'basic';
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
		$id = loadGame((int) $_REQUEST['game'], true);

		if ($id === false) {
			arcadeClearSession();
			fatal_lang_error('arcade_game_not_found', false);
		}
		$dbgame = &$context['arcade']['game_data'][$id];
		$coverfile = !empty($dbgame['submit_system']) && $dbgame['submit_system'] == 'rom' ? 'romgamecover.gif' : 'arcadegamecover.png';
		$imgFile1 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail'] : $dbgame['thumbnail'];
		$imgFile2 = !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['thumbnail_small'] : $dbgame['thumbnail_small'];
		$imgFile3 = !empty($dbgame['cover_icon']) && !empty($dbgame['game_directory']) ? $dbgame['game_directory'] . '/' . $dbgame['cover_icon'] : $dbgame['cover_icon'];
		$imgPath1 = file_exists($gamesDirectory . '/' . $imgFile1) ? $gamesUrl . '/' . $imgFile1 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath2 = file_exists($gamesDirectory . '/' . $imgFile2) ? $gamesUrl . '/' . $imgFile2 : $settings['default_theme_url'] . '/images/arc_icons/Default.gif';
		$imgPath3 = !empty($dbgame['cover_icon']) && file_exists($gamesDirectory . '/' . $imgFile3) ? $gamesUrl . '/' . $imgFile3 : $settings['default_theme_url'] . '/images/arc_icons/' . $coverfile;
		$hide = !empty($dbgame['icon_position_hide']) ? (int)$dbgame['icon_position_hide'] : 0;
		$extra = !empty($dbgame['extra_data']) && arcadeIsSerialized($dbgame['extra_data']) ? arcade_safe_unserialize($dbgame['extra_data']) : array();
		$extra['width'] = !empty($extra['width']) ? $extra['width'] : 600;
		$extra['height'] = !empty($extra['height']) ? $extra['height'] : 400;
		$extra['type'] = !empty($extra['type']) ? $extra['type'] : 'normal';
		$type = $extra['type'];
		$coverfile = $dbgame['submit_system'] == 'rom' ? 'romgamecover.gif' : 'arcadegamecover.png';
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
			'covericon' => stripos($imgPath3, $settings['default_theme_url'] . '/images/arc_icons/' . $coverfile) !== FALSE ? '<li style="font-size: 8pt;">thumbnail: ' . $txt['arcade_error_cover_icon'] . '</li>' : '',
			'description' => htmlspecialchars($dbgame['description']),
			'help' => htmlspecialchars($dbgame['help']),
			'game_file' => (string)$dbgame['game_file'],
			'game_directory' => $dbgame['game_directory'],
			'submit_system' => $dbgame['submit_system'],
			'score_type' => $dbgame['score_type'],
			'js_insertion' => !empty($dbgame['js_insertion']) ? $dbgame['js_insertion'] : $arcadeModSettings['arcadeJsInsertDefault'],
			'member_groups' => explode(',', $dbgame['member_groups']),
			'extra_data' => $extra,
			'enabled' => !empty($dbgame['enabled']),
			'download' => !empty($dbgame['download']) ? 1 : 0,
			'icon_position' => !empty($dbgame['icon_position']) ? $dbgame['icon_position'] : 0,
			'icon_position_hide' => abs(intval($hide)),
		);

		if (!is_array($context['game']['extra_data']) || isset($_REQUEST['detect']))
		{
			require_once($boarddir . '/ArcadeSources/SWFReader.php');
			$swf = new SWFReader();

			if (substr($dbgame['game_file'], -3) == 'swf')
			{
				$swf->open($upload_directory . '/' . $dbgame['game_directory'] . '/' . $dbgame['game_file']);

				$context['game']['extra_data'] = array(
					'width' => $swf->header['width'],
					'height' => $swf->header['height'],
					'flash_version' => $swf->header['version'],
					'type' => $type,
					'background_color' => $swf->header['background'],
				);

				$swf->close();
			}
			else
			{
				$context['game']['extra_data'] = array(
					'width' => '',
					'height' => '',
					'flash_version' => '',
					'background_color' => array('', '', ''),
					'type' => '',
				);
			}
		}
	}

	$context['game_permissions'] = $arcadeModSettings['arcadePermissionMode'] > 2;

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
				'name' => arcadeFixTagsHTML($dbrow['cat_name'], true)
			);
		$smcFunc['db_free_result']($request);
	}

	// Load Sumbit Systems
	if (!isset($context['submit_systems'])) {
		$context['submit_systems'] = SubmitSystemInfo('*') + array('rom' => $txt['arcade_rom_switch']['rom']);
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
	$dbgameOptions['thumbanil_small'] = isset($_POST['thumbnail_small']) ? $_POST['thumbnail_small'] : '';
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

	if (trim($_POST['game_file']) == '')
		$errors['game_file'] = 'invalid';

	if (!isset($context['submit_systems'][$_POST['submit_system']]))
		$errors['submit_system'] = 'invalid';

	$context['game']['extra_data']['type'] = !empty($context['game']['extra_data']['type']) ? $context['game']['extra_data']['type'] : 'normal';
	$extra_data = $context['game']['extra_data'];

	if (isset($_POST['extra_data']))
	{
		foreach ($_POST['extra_data'] as $item => $value)
			$extra_data[$item] = $value;
	}

	$_POST['game_download'] = isset($_POST['game_download']) ? intval($_POST['game_download']) : 0;
	$dbgameOptions['internal_name'] = str_replace(array('/', '\\'), array('', ''), trim($_POST['internal_name'], '.'));
	$dbgameOptions['submit_system'] = $_POST['submit_system'];
	$dbgameOptions['game_directory'] = preg_replace('#/+#','/',implode('/', array_map(function($value) {return trim($value, '.');}, explode('/', str_replace('\\', '/', $_POST['game_directory'])))));
	$dbgameOptions['game_file'] = str_replace(array('\\'), array(''), trim($_POST['game_file'], '.'));
	$dbgameOptions['score_type'] = (int)$_POST['score_type'];
	$dbgameOptions['js_insertion'] = (int)$_POST['js_insertion'];
	$dbgameOptions['download'] = isset($_POST['game_download']) ? abs(intval($_POST['game_download'])) : 0;
	$dbgameOptions['extra_data'] = $extra_data;
	$dbgameOptions['rom_flag'] = 0;
	if (isset($_POST['submit_system']) && $_POST['submit_system'] == 'rom') {
		$dbgameOptions['rom_flag'] = 1;
		$dbgameOptions['rom_system'] = 'none';
		$dbgameOptions['submit_system'] = 'rom';
		if (is_dir($gamesDirectory . '/' . $dbgameOptions['game_directory']) && !is_dir($romGamesDirectory . '/' . $dbgameOptions['game_directory']))
			@rename($gamesDirectory . '/' . $dbgameOptions['game_directory'], $romGamesDirectory . '/' . $dbgameOptions['game_directory']);
	}

	if (isset($_POST['thumbnail']) && empty($fileflag)) {
		list($thumbPost, $thumbFix) = array(preg_replace('/[^A-Za-z0-9-_\. ]/', '', $_POST['thumbnail']), arcade_sanitize_file_name($_POST['thumbnail'], 'file'));
		$imgFileDir = !empty($context['game']['game_directory']) ? '/' . $context['game']['game_directory'] : '';
		if (!is_dir($gamesDirectory . $imgFileDir . '/' . $thumbPost) && file_exists($gamesDirectory . $imgFileDir . '/' . $thumbPost) && $thumbPost != $thumbFix) {
			@rename($gamesDirectory . $imgFileDir . '/' . $thumbPost, $gamesDirectory . $imgFileDir . '/' . $thumbFix);
			if (file_exists($gamesDirectory . $imgFileDir . '/' . $thumbFix)) {
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
		elseif ($thumbPost == $thumbFix && !is_dir($gamesDirectory . $imgFileDir . '/' . $thumbPost) && !file_exists($gamesDirectory . $imgFileDir . '/' . $thumbPost)) {
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
		if (!is_dir($gamesDirectory . $imgFileDir . '/' . $thumbPost) && file_exists($gamesDirectory . $imgFileDir . '/' . $thumbPost) && $thumbPost != $thumbFix) {
			@rename($gamesDirectory . $imgFileDir . '/' . $thumbPost, $gamesDirectory . $imgFileDir . '/' . $thumbFix);
			if (file_exists($gamesDirectory . $imgFileDir . '/' . $thumbFix)) {
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
		elseif ($thumbPost == $thumbFix && !is_dir($gamesDirectory . $imgFileDir . '/' . $thumbPost) && !file_exists($gamesDirectory . $imgFileDir . '/' . $thumbPost)) {
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
		if (!is_dir($gamesDirectory . $imgFileDir . '/' . $coverPost) && file_exists($gamesDirectory . $imgFileDir . '/' . $coverPost) && $coverPost != $coverFix) {
			@rename($gamesDirectory . $imgFileDir . '/' . $coverPost, $gamesDirectory . $imgFileDir . '/' . $coverFix);
			if (file_exists($gamesDirectory . $imgFileDir . '/' . $coverFix)) {
				$_POST['cover_icon'] = $coverFix;
				$dbgameOptions['cover_icon'] = $coverFix;
			}
			else {
				$_POST['cover_icon'] = '';
				$thumbErrors['covericon'] = 'invalid';
			}
		}
		elseif ($coverPost == $coverFix && !is_dir($gamesDirectory . $imgFileDir . '/' . $coverPost) && !file_exists($gamesDirectory . $imgFileDir . '/' . $coverPost)) {
			$_POST['cover_icon'] = '';
			$thumbErrors['covericon'] = 'invalid';
		}
		elseif ($coverPost != $coverFix) {
			$_POST['cover_icon'] = '';
			$thumbErrors['covericon'] = 'invalid';
		}
	}
	elseif (!isset($_POST['cover_icon'])) {
		$thumbErrors['covericon'] = '';
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
		$errors['thumbnail'] = 'errorfile';
		$targetFile = basename($_FILES["thumb1"]["name"]);
		$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		if (in_array($imageFileType, array('jpg', 'png', 'gif', 'jpeg'))) {
			$check = getimagesize($_FILES["thumb1"]["tmp_name"]);
			$detectType = !empty($check) && is_array($check) && !empty($check['mime']) ? trim(str_ireplace('image/', '', $check['mime'])) : 'mismatch';
			if ($_FILES["thumb1"]["size"] <= 10000000 && in_array($detectType, array('jpg', 'jpeg', 'gif', 'png'))) {
				list($targetFile, $tempfile) = array(str_replace('.jpeg', '.jpg', $targetFile), '');
				if (!empty($dbgameOptions['thumbnail']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail'])) {
					$tempfile = 'temp_' . uniqid() . '_' . $dbgameOptions['thumbnail'];
					@rename($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail'], $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					clearstatcache();
				}
				if (@move_uploaded_file($_FILES["thumb1"]["tmp_name"], $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile)) {
					if (!empty($tempfile) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					}
					$tempfile = '';
					if (!empty($dbgameOptions['thumbnail']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail'])) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail']);
					}
					if (!empty(basename($imgFile1)) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1)) && basename($imgFile1) != $context['game']['thumbnail_small']) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1));
					}
					elseif (!empty(basename($imgFile1)) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1)) && isset($_FILES["thumb2"]["name"])) {
						if (basename($_FILES["thumb2"]["name"]) != basename($imgFile1))
							@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1));
					}
					elseif (!empty(basename($imgFile1)) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1)) && isset($_POST['thumbnail_small'])) {
						if (basename($_POST['thumbnail_small']) != basename($imgFile1))
							@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile1));
					}
					$dbgameOptions['thumbnail'] = $targetFile;
					$context['game']['thumbnail'] = $targetFile;
					$_POST['thumbnail'] = $targetFile;
					$errors['thumbnail'] = '';
					clearstatcache();
					list($q, $newTarget) = array(1, rtrim($context['game']['internal_name'], '_') . '1.' . $imageFileType);
					if (file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $targetFile != $newTarget) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget);
						clearstatcache();
					}
					ArcadeImageResize($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, 120, 120, $cropThumb);
					@rename($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget);
					clearstatcache();
					if (file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $context['game']['thumbnail_small'] != $targetFile) {
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
					elseif (!isset($_POST['thumbnail_small']) && !file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $context['game']['thumbnail_small'])) {
						$dbgameOptions['thumbnail_small'] = $targetFile;
						$_POST['thumbnail_small'] = $targetFile;
						$errors['thumbnail_small'] = '';
					}

				}
				if (!empty($tempfile) && !empty($dbgameOptions['thumbnail']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
					@rename($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile, $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail']);
					$tempfile = '';
				}
			}
		}
	}
	if (isset($_FILES["thumb2"]["name"]) && isset($_FILES["thumb2"]["tmp_name"])) {
		$errors['thumbnail_small'] = 'errorfile';
		$targetFile = basename($_FILES["thumb2"]["name"]);
		$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		if (in_array($imageFileType, array('jpg', 'png', 'gif', 'jpeg'))) {
			$check = getimagesize($_FILES["thumb2"]["tmp_name"]);
			$detectType = !empty($check) && is_array($check) && !empty($check['mime']) ? trim(str_ireplace('image/', '', $check['mime'])) : 'mismatch';
			if ($_FILES["thumb2"]["size"] <= 10000000 && in_array($detectType, array('jpg', 'jpeg', 'gif', 'png'))) {
				list($targetFile, $tempfile) = array(str_replace('.jpeg', '.jpg', $targetFile), '');
				if (!empty($dbgameOptions['thumbnail_small']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small'])) {
					$tempfile = 'temp_' . uniqid() . '_' . $dbgameOptions['thumbnail_small'];
					@rename($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small'], $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					clearstatcache();
				}
				if (@move_uploaded_file($_FILES["thumb2"]["tmp_name"], $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile)) {
					if (!empty($dbgameOptions['thumbnail_small']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small'])) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small']);
					}
					if (!empty(basename($imgFile2)) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile2)) && $context['game']['thumbnail'] != basename($imgFile2)) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . basename($imgFile2));
					}
					$dbgameOptions['thumbnail_small'] = $targetFile;
					$context['game']['thumbnail_small'] = $targetFile;
					$_POST['thumbnail_small'] = $targetFile;
					$errors['thumbnail_small'] = '';
					clearstatcache();
					list($q, $newTarget) = array(2, rtrim($context['game']['internal_name'], '_') . '2.' . $imageFileType);
					if (file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $targetFile != $newTarget) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget);
						clearstatcache();
					}
					ArcadeImageResize($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile, $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile, 80, 80, $cropThumb);
					@rename($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget);
					clearstatcache();
					if (file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $context['game']['thumbnail'] != $targetFile) {
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
					elseif (!isset($_POST['thumbnail']) && !file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $context['game']['thumbnail'])) {
						$dbgameOptions['thumbnail'] = $targetFile;
						$_POST['thumbnail'] = $targetFile;
						$errors['thumbnail'] = '';
					}
				}
				if (!empty($tempfile) && !empty($dbgameOptions['thumbnail_small']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
					@rename($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile, $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['thumbnail_small']);
					$tempfile = '';
				}
			}
		}
	}
	if (isset($_FILES["covericon"]["name"]) && isset($_FILES["covericon"]["tmp_name"])) {
		$errors['thumbnail'] = 'errorfile';
		$targetFile = basename($_FILES["covericon"]["name"]);
		$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		$dbgameOptions2['thumbnail'] = isset($_POST['thumbnail']) ? $_POST['thumbnail'] : '';
		$dbgameOptions2['thumbnail_small'] = isset($_POST['thumbnail_small']) ? $_POST['thumbnail_small'] : '';
		$checkIcon = $_FILES["covericon"]["tmp_name"] != $dbgameOptions2['thumbnail_small'] && $_FILES["covericon"]["tmp_name"] != $dbgameOptions2['thumbnail'] ? true: false;
		if (in_array($imageFileType, array('jpg', 'png', 'gif', 'jpeg')) && !empty($checkIcon)) {
			$check = getimagesize($_FILES["covericon"]["tmp_name"]);
			$detectType = !empty($check) && is_array($check) && !empty($check['mime']) ? trim(str_ireplace('image/', '', $check['mime'])) : 'mismatch';
			if ($_FILES["covericon"]["size"] <= 10000000 && in_array($detectType, array('jpg', 'jpeg', 'gif', 'png'))) {
				list($targetFile, $tempfile) = array(str_replace('.jpeg', '.jpg', $targetFile), '');
				if (!empty($dbgameOptions['cover_icon']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon'])) {
					$tempfile = 'temp_' . uniqid() . '_' . $dbgameOptions['cover_icon'];
					@rename($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon'], $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile);
					clearstatcache();
				}
				if (@move_uploaded_file($_FILES["covericon"]["tmp_name"], $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $targetFile)) {
					if (!empty($dbgameOptions['cover_icon']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon'])) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon']);
					}
					$dbgameOptions['cover_icon'] = $targetFile;
					$context['game']['cover_icon'] = $targetFile;
					$_POST['cover_icon'] = $targetFile;
					$errors['thumbnail'] = '';
					clearstatcache();
					list($q, $newTarget) = array(1, rtrim($context['game']['internal_name'], '_') . '3.' . $imageFileType);
					if (file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget) && $targetFile != $newTarget) {
						@unlink($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $newTarget);
						clearstatcache();
					}
					list($icWidth, $icHeight, $ictype, $icattr) = getimagesize($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile);
					$icWidth2 = $icWidth < 150 ? 150 : ($icWidth > 200 ? 200 : $icWidth);
					$icHeight2 = $icHeight < 150 ? 150 : ($icHeight > 355 ? 355 : $icHeight);
					if ($icWidth != $icWidth2 || $icHeight != $icHeight2) {
						ArcadeImageResize($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $icWidth2, $icHeight2, $cropCover);
					}
					@rename($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $targetFile, $gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget);
					clearstatcache();
					if (file_exists($gamesDirectory . '/' .  $context['game']['game_directory'] . '/' . $newTarget)) {
						$targetFile = $newTarget;
						$dbgameOptions['cover_icon'] = $targetFile;
						$_POST['cover_icon'] = $targetFile;
						$errors['thumbnail'] = '';
					}
				}
				elseif (!empty($tempfile) && !empty($dbgameOptions['cover_icon']) && file_exists($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile)) {
					@rename($gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $tempfile, $gamesDirectory . '/' . $context['game']['game_directory'] . '/' . $dbgameOptions['cover_icon']);
					$tempfile = '';
				}
			}
		}
	}

	updateGame($context['game']['id'], $dbgameOptions, true);

	$start = !empty($_SESSION['arcade_game_start_edit']) ? 'start=' . $_SESSION['arcade_game_start_edit'] . ';' : '';
	if ($dbgameOptions['rom_flag'] == 1)
		redirectexit('action=admin;area=manageromgames;sa=editrom;game=' . $context['game']['id']);

	if (empty($preview))
		redirectexit('action=admin;area=managegames;' . $start . $context['session_var'] . '=' . $context['session_id']);
	else
		redirectexit('action=admin;area=managegames;sa=edit;game=' . $context['game']['id'] . ';' . $thumbnailErrors . '#adm_submenus');
}

function ExportGameInfo()
{
	global $scripturl, $txt, $db_prefix, $arcadeModSettings, $context, $boarddir, $smcFunc, $sourcedir, $arcade_version;

	$id = loadGame((int) $_REQUEST['game'], true);
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$array = array('internal_name', 'game_name', 'description', 'help', 'thumbnail', 'thumbnail_small', 'cover_icon', 'game_file', 'submit_system');

	if ($id === false) {
		arcadeClearSession();
		fatal_lang_error('arcade_game_not_found', false);
	}

	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['arcade_manage_games'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['arcade_manage_games_desc'];
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
	$classicGameTypes = explode('|', $arcadeModSettings['arcadeCommonGameTypes']);
	if (($key = array_search('rom', $classicGameTypes)) !== FALSE) {
		unset($classicGameTypes['rom']);
	}
	$stype = array_merge(array('auto'), $classicGameTypes);
	$savetype = !empty($data['submit_system']) && in_array($data['submit_system'], $stype) ? $data['submit_system'] : 'v1game';
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
		'gkeys' => htmlspecialchars($data['help'], ENT_QUOTES | ENT_HTML5),
		'gname' => $data['internal_name'],
		'gtitle' => htmlspecialchars($data['gamename'], ENT_QUOTES | ENT_HTML5),
		'gwords' => htmlspecialchars($data['description'], ENT_QUOTES | ENT_HTML5),
		'object' => htmlspecialchars($data['description'], ENT_QUOTES | ENT_HTML5),
		'snggame' => $data['score_type'],
		'savetype' => $savetype,
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

	if (in_array($savetype, array('ibp', 'ibp2', 'ibp3', 'ibp32', 'html52', 'rom')) || !empty($dbgameinfo['force_ibp']))
	{
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
	\'savetype\'	=> \'' . $dbgameinfo['savetype'] . '\',' . (stripos($dbgameinfo['savetype'], 'html5') !== false ? '
	\'scoring\'	=> \'' . $dbgameinfo['score_type'] . '\',
	\'html5\'		=> \'1\',' : '') . '
' . (stripos($dbgameinfo['savetype'], 'html5') !== false ? '	\'js_insertion\'	=> \'' . $dbgameinfo['js_insertion'] . '\',' : '') . '
);
?>';
		$ext = '.php';
	}
	elseif ($savetype == 'html53')
	{
		$infofile = '<?php
/*---------------------------------------------------------------*/
/* File Created by SMF Arcade ' . $arcade_version . '
/* File Generated: ' . $dbgameinfo['date'] . '
/*---------------------------------------------------------------*/

if (!defined(\'IN_PHPBB\') || !defined(\'IN_PHPBB_ARCADE\'))
{
	exit;
}

$dbgame_data = array(
	\'game_height\'		=>	' . $dbgameinfo['gheight'] . ',
	\'game_width\'		=>	' . $dbgameinfo['gwidth'] . ',
	\'game_control_desc\'	=>	\'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['gkeys']) . '\',
	\'game_control\'		=>	' . $controls . ',
	\'game_scorevar\'		=>	\'' . $dbgameinfo['gname'] . '\',
	\'game_image\'		=>	\'' . $thumbnail . '\',
	\'covericon\'		=> \'' . $covericon . '\',
	\'game_name\'			=>	\'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['gtitle']) . '\',
	\'game_desc\'			=>	\'' . mb_ereg_replace("&apos;", "\'", $dbgameinfo['gwords']) . '\',
	\'game_scoretype\'	=>	' . (empty($dbgameinfo['snggame']) ? 'SCORETYPE_HIGH' : 'SCORETYPE_LOW') . ',
	\'game_save_type\'	=>	' . (substr($dbgameinfo['gname'], -2) == 'RA' ? 'PHPBB_RA_GAME' : 'PHPBBARCADE_GAME') . ',
	\'scoring\'			=>	\'' . $dbgameinfo['score_type'] . '\',
	\'game_type\'			=>	GAME_TYPE_HTML5,
	\'js_insertion\'		=>	' . $dbgameinfo['js_insertion'] . ',
	\'game_inherit\'		=>	\'\',
	\'privacy_desc\'		=>	\'\',
	\'privacy_link\'		=>	\'\',
);
?>';
		$ext = '.php';
	}
	else
	{
		$infofile = "
<!-- 	File Created by SMF Arcade " . $arcade_version . "			-->
<!-- 	File Generated: " . $dbgameinfo['date'] . "		-->";
		$infofile .= '
<game-info>
	<id>' . $dbgameinfo['gname'] . '</id>
	<name>' . $dbgameinfo['gtitle'] . '</name>
	<description>' . $dbgameinfo['gwords'] . '</description>
	<help>' . $dbgameinfo['gkeys'] . '</help>
	<thumbnail>' . $dbgameinfo['thumbnail'] . '</thumbnail>
	<thumbnail-small>' . $dbgameinfo['thumbnail_small'] . '</thumbnail-small>
	<covericon>' . $covericon . '</covericon>
	<file>' . $dbgameinfo['file'] . '</file>
	<scoring>' . $dbgameinfo['score_type'] . '</scoring>
	<submit>' . $dbgameinfo['savetype'] . '</submit>
	<flash>
		<version>' . $dbgameinfo['flash_version'] . '</version>
		<width>' . $dbgameinfo['gwidth'] . '</width>
		<height>' . $dbgameinfo['gheight'] . '</height>
		<type>' . (!empty($dbgameinfo['type']) &&  $dbgameinfo['type'] == 'fullscreen' ? 'fullscreen' : 'normal') . '</type>
		<bgcolor>' . $dbgameinfo['bgcolor'] . '</bgcolor>
	</flash>
</game-info>';
		$ext = '.xml';
	}

	if ($ext == '.php')
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		echo '
	<html>
		<head>
			<title>PHP Example Output</title>
		</head>
		<body>
			<textarea rows="25" cols="250" style="border:none;" ' . $readonly . '>', $infofile, '</textarea>
		</body>
	</html>';;
	}
	else
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		echo '
	<html>
		<head>
			<title>XML Example Output</title>
		</head>
		<body>
			<textarea rows="25" cols="250" style="border:none;" ' . $readonly . '>
<?xml version="1.0"?>', $infofile, '
			</textarea>
		</body>
	</html>';
	}

	obExit(false);
}

function arcadeHexToColor($hex)
{
	$hex = array_map('intval', $hex);
	list($r, $g, $b) = $hex;
	list($r, $g, $b) = array(dechex($r), dechex($g), dechex($b));
	$r = strlen($r)<2 ? '0' . $r : $r;
	$g = strlen($g)<2 ? '0' . $g : $g;
	$b = strlen($b)<2 ? '0' . $b : $b;
    return $r . $g . $b;
}

function arcadeBytesToSize1024($bytes, $precision = 2)
{
    $unit = array('B','KB','MB');
    return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
}

?>