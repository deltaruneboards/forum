<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function arcadeStats($memID)
{
	global $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings, $user_info, $smcFunc, $boarddir, $sourcedir, $context;

	require_once($sourcedir . '/Arcade.php');
	loadArcade('profile');

	$context['arcade']['member_stats'] = array();

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS champion
		FROM {db_prefix}arcade_games
		WHERE id_champion = {int:member}
			AND enabled = 1',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats'] += $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS rates, (SUM(rating) / COUNT(*)) AS avg_rating
		FROM {db_prefix}arcade_rates
		WHERE id_member = {int:member}',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats'] += $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE s.id_member = {int:member}
			AND s.personal_best = 1
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY s.position
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['scores'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['scores'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE s.id_member = {int:member}
			AND s.personal_best = 1
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY s.end_time DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['latest_scores'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['latest_scores'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	// 1st 2nd 3rd placements
	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE id_member = {int:member}
			AND s.position = 1
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY s.score DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['position1'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['position1'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => (strlen($row['game_name']) > 33) ? substr($row['game_name'], 0, 30) . '...' : $row['game_name'],
			'title' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE s.id_member = {int:member}
			AND s.position = 2
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY s.score DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['position2'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['position2'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => (strlen($row['game_name']) > 33) ? substr($row['game_name'], 0, 30) . '...' : $row['game_name'],
			'title' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	$result = $smcFunc['db_query']('', '
		SELECT s.position, s.score, s.end_time, game.game_name, game.id_game
		FROM ({db_prefix}arcade_scores AS s, {db_prefix}arcade_games AS game)
		WHERE s.id_member = {int:member}
			AND s.position = 3
			AND s.id_game = game.id_game
			AND game.enabled = 1
		ORDER BY s.score DESC
		LIMIT 10',
		array(
			'member' => $memID,
		)
	);

	$context['arcade']['member_stats']['position3'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['arcade']['member_stats']['position3'][] = array(
			'link' => $scripturl . '?action=arcade;game=' . $row['id_game'],
			'name' => (strlen($row['game_name']) > 33) ? substr($row['game_name'], 0, 30) . '...' : $row['game_name'],
			'title' => $row['game_name'],
			'score' => comma_format($row['score']),
			'position' => $row['position'],
			'time' => timeformat($row['end_time'])
		);
	$smcFunc['db_free_result']($result);

	// Layout
	$context['sub_template'] = 'arcade_user_statistics';
	$context['page_title'] = sprintf($txt['arcade_user_stats_title'], $context['member']['name']);
}

function arcadeChallenge($memID)
{
	global $db_prefix, $scripturl, $txt, $arcadeModSettings, $context, $settings, $user_info, $smcFunc, $boarddir, $sourcedir;

	require_once($sourcedir . '/Arcade.php');
	require_once($boarddir . '/ArcadeSources/ArcadeArena.php');
	require_once($sourcedir . '/Subs-Members.php');

	loadArcade('profile');

	if (!memberAllowedTo(array('arcade_join_match', 'arcade_join_invite_match'), $memID)) {
		arcadeClearSession();
		fatal_lang_error('arcade_no_invite', false);
	}

	$context['matches'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_match, name
		FROM {db_prefix}arcade_matches
		WHERE id_member = {int:member}
			AND status = 0',
		array(
			'member' => $user_info['id'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['matches'][] = array(
			'id' => $row['id_match'],
			'name' => $row['name'],
		);
	$smcFunc['db_free_result']($request);

	// Layout
	$context['sub_template'] = 'arcade_arena_challenge';
	$context['page_title'] = sprintf($txt['arcade_arena_challenge_title'], $context['member']['name']);
}

function arcadeGetRomSettings($arcadeRomSettings)
{
	global $arcadeModSettings, $user_info;

	$settings = !empty($arcadeModSettings['arcadeRomUserSettings']) ? explode('|', $arcadeModSettings['arcadeRomUserSettings']) : array('webgl2Enabled', 'volume', 'mute', 'shader');
	$current = array();
	foreach ($settings as $setting) {
		$current[$setting] = !empty($arcadeRomSettings[$setting]) ? (int)$arcadeRomSettings[$setting] : 0;
	}
	return $current;
}

function arcadeSettings($memID)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $user_info, $boarddir, $sourcedir, $arcadeModSettings;

	require_once($sourcedir . '/Arcade.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeAegis.php');
	loadArcade('profile');
	$events = arcadeEvent('get', array());
	$arcadeSettings = loadMyArcadeSettings($memID);
	$context['arcade_admin_only'] = !allowedTo('arcade_admin') ? array('notify_game_reports') : array();
	$arcadeRomSettings = !empty($arcadeSettings['arcade_emulatorjs_rom']) && arcadeIsSerialized($arcadeSettings['arcade_emulatorjs_rom']) ? arcade_safe_unserialize($arcadeSettings['arcade_emulatorjs_rom']) : array();
	$arcadeRomUserSettings = arcadeGetRomSettings($arcadeRomSettings);
	list(
		$default['skins'],
		$default['mobileSkins'],
		$default['lists'],
		$default['mobileLists']
	) = [
		(int)$arcadeModSettings['arcadeSkin']+1,
		(int)$arcadeModSettings['arcadeSkinMobile']+1,
		(int)$arcadeModSettings['arcadeList']+1,
		(int)$arcadeModSettings['arcadeListMobile']+1
	];
	list($default['romSkins'], $default['romLists']) = [$default['skins'], $default['lists']];
	list(
		$arcade['lists'],
		$arcade['skins'],
		$arcade['mobileSkins'],
		$arcade['mobileLists']
	) = [
		Arcade_integrate_lists('desktop', true, 0),
		Arcade_integrate_skins('desktop', true, 0),
		Arcade_integrate_skins('mobile', true, 0),
		Arcade_integrate_lists('mobile', true, 0)
	];
	list($arcade['romLists'], $arcade['romSkins']) = [$arcade['lists'], $arcade['skins']];
	list($shaders, $romSelectIds) = [array_values($txt['arcade_profile_shaders']), ''];
	$romSettingSelects = array('shader');



	$skins = array(
		'1' => $txt['arcade_default'],
		'2' => $txt['arcade_skin_c'],
		'3' => $txt['arcade_skin_b'],
	);
	$romSkins = $skins;
	$mobileSkins = array(
		'1' => $txt['arcade_skin_mobile_generic'],
		'2' => $txt['arcade_skin_mobile0'],
	);
	$lists = array(
		'1' => $txt['arcade_list0'],
		'2' => $txt['arcade_list1'],
		'3' => $txt['arcade_list2'],
	);
	$romLists = $lists;
	$mobileLists = array(
		'1' => $txt['arcade_list_mobile_generic'],
		'2' => $txt['arcade_list_mobile0'],
	);

	foreach($arcade['skins'] as $skin)
	{
		if (!empty($skin['id_skin']) && $skin['id_skin'] > 3) {
			if (!empty($skin['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $skin['skin_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $skin['skin_source_file']);
				if (!empty($skin['lang_function']) && function_exists($skin['lang_function'])) {
					$skin['lang_function']();
				}
			}
			$skins[$skin['id_skin']] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
			$romSkins[$skin['id_skin']] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
			if ($skin['id_skin'] == $default['skins']) {
				$skins['default'] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
				$romSkins['default'] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
			}
		}
	}
	foreach($arcade['mobileSkins'] as $skin)
	{
		if (!empty($skin['id_skin']) && $skin['id_skin'] > 2) {
			if (!empty($skin['skin_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $skin['skin_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $skin['skin_source_file']);
				if (!empty($skin['lang_function']) && function_exists($skin['lang_function'])) {
					$skin['lang_function']();
				}
			}
			$mobileSkins[$skin['id_skin']] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
			if ($skin['id_skin'] == $default['mobileSkins']) {
				$mobileSkins['default'] = !empty($txt[$skin['skin_name']]) ? $txt[$skin['skin_name']] : $skin['skin_name'];
			}
		}
	}
	foreach($arcade['lists'] as $list)
	{
		if (!empty($list['id_list']) && $list['id_list'] > 3) {
			if (!empty($list['list_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $list['list_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $list['list_source_file']);
				if (!empty($list['lang_function']) && function_exists($list['lang_function'])) {
					$list['lang_function']();
				}
			}
			$lists[$list['id_list']] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
			$romLists[$list['id_list']] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
			if ($list['id_list'] == $default['lists']) {
				$lists['default'] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
				$romLists['default'] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
			}
		}

	}
	foreach($arcade['mobileLists'] as $list)
	{
		if (!empty($list['id_list']) && $list['id_list'] > 2) {
			if (!empty($list['list_source_file']) && file_exists($boarddir . '/ArcadeSources/' . $list['list_source_file'])) {
				require_once($boarddir . '/ArcadeSources/' . $list['list_source_file']);
				if (!empty($list['lang_function']) && function_exists($list['lang_function'])) {
					$list['lang_function']();
				}
			}
			$mobileLists[$list['id_list']] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
			if ($list['id_list'] == $default['mobileLists']) {
				$mobileLists['default'] = !empty($txt[$list['list_name']]) ? $txt[$list['list_name']] : $list['list_name'];
			}
		}
	}

	foreach (array('skins', 'lists', 'romSkins', 'romLists', 'mobileSkins', 'mobileLists') as $var) {
		$type = stripos($var, 'skin') !== FALSE ? 'skin' : 'list';
		$name = !empty($default[$var]) && !empty($arcade[$var][$default[$var]]) && !empty($txt[$arcade[$var][$default[$var]][$type . '_name']]) ? $txt[$arcade[$var][$default[$var]][$type . '_name']] : (!empty($$var[$default[$var]]) ? $$var[$default[$var]] : $txt['arcade_profile_unknown']);
		$$var[0] = !empty($$var['default']) ? sprintf($txt['arcade_user_default'], $$var['default']) : sprintf($txt['arcade_user_default'], $name);
		if (!empty($$var['default'])) {
			unset($$var['default']);
		}
		ksort($$var);
	}

	$context['profile_fields'] = array(
		'notifications' => array(
			'type' => 'callback',
			'callback_func' => 'arcade_notification',
		),
	);

	if (!empty($arcadeModSettings['arcadeRetroArchEnabled']) && (allowedTo('arcade_view_retro_arch') || allowedTo('arcade_admin'))) {
		$context['profile_fields'] += array(
			'divider2' => array(
				'label' => '',
				'type' => 'check',
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => ' style="display: none"',
				'value' => 0,
			),
		);
		$context['profile_fields'] += array(
			'arcade_emulatorjs_rom' => array(
				'type' => 'callback',
				'callback_func' => 'arcade_user_emulatorjs',
			),
		);
		$context['profile_fields'] += array(
			'shader' => array(
				'id' => 'shader',
				'label' => $txt['arcade_user_emulatorjs_shader'],
				'type' => 'select',
				'options' => $shaders,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['shader']) ? $arcadeSettings['shader'] : 0,
			),
		);
		$context['profile_fields'] += array(
			'divider3' => array(
				'label' => '',
				'type' => 'check',
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => ' style="display: none"',
				'value' => 0,
			),
		);
	}

	$context['profile_fields'] += array(
		'games_per_page' => array(
			'label' => $txt['arcade_user_gamesPerPage'],
			'type' => 'number',
			'size' => 7,
			'min' => 4,
			'max' => 60,
			'input_attr' => '',
			'value' => isset($arcadeSettings['games_per_page']) ? $arcadeSettings['games_per_page'] : (isset($arcadeModSettings['gamesPerPage']) ? $arcadeModSettings['gamesPerPage'] : 28),
			'cast' => 'int',
			'validate' => 'int',
		),
		'scores_per_page' => array(
			'label' => $txt['arcade_user_scoresPerPage'],
			'type' => 'select',
			'options' => array(
				0 => sprintf($txt['arcade_user_scoresPerPage_default'], $arcadeModSettings['scoresPerPage']),
				5 => 5,
				10 => 10,
				20 => 20,
				25 => 25,
				50 => 50,
			),
			'cast' => 'int',
			'validate' => 'int',
			'input_attr' => '',
			'value' => isset($arcadeSettings['scores_per_page']) ? $arcadeSettings['scores_per_page'] : 0,
		),
	);

	if (allowedTo('arcade_skin'))
	{
		$context['profile_fields'] += array(
			'skin' => array(
				'label' => $txt['arcade_user_skin'],
				'type' => 'select',
				'options' => $skins,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['skin']) ? $arcadeSettings['skin'] : 0,
			),
		);
	}

	if (allowedTo('arcade_list'))
	{
		$context['profile_fields'] += array(
			'list' => array(
				'label' => $txt['arcade_user_list'],
				'type' => 'select',
				'options' => $lists,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['list']) ? $arcadeSettings['list'] : 0,
			),
		);
	}

	if ((allowedTo('arcade_skin') && allowedTo('arcade_view_retro_arch') && empty($arcadeModSettings['arcadeRomToggle'])) || allowedTo('arcade_admin'))
	{
		$context['profile_fields'] += array(
			'skin_rom' => array(
				'label' => $txt['arcade_user_skin_rom'],
				'type' => 'select',
				'options' => $romSkins,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['skin_rom']) ? $arcadeSettings['skin_rom'] : 0,
			),
		);
	}

	if ((allowedTo('arcade_list') && allowedTo('arcade_view_retro_arch') && empty($arcadeModSettings['arcadeRomToggle'])) || allowedTo('arcade_admin'))
	{
		$context['profile_fields'] += array(
			'list_rom' => array(
				'label' => $txt['arcade_user_list_rom'],
				'type' => 'select',
				'options' => $romLists,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['list_rom']) ? $arcadeSettings['list_rom'] : 0,
			),
		);
	}

	$context['profile_fields'] += array(
		'detect_mobile' => array(
			'label' => $txt['arcade_user_mobile_detection'],
			'type' => 'select',
			'options' => array(
				0 => $txt['arcade_mobile_auto'],
				1 => $txt['arcade_mobile_desktop'],
			),
			'cast' => 'int',
			'validate' => 'int',
			'input_attr' => '',
			'value' => isset($arcadeSettings['detect_mobile']) ? $arcadeSettings['detect_mobile'] : 0,
		),
	);

	if (allowedTo('arcade_skin'))
	{
		$context['profile_fields'] += array(
			'skin_mobile' => array(
				'label' => $txt['arcade_user_skin_mobile'],
				'type' => 'select',
				'options' => $mobileSkins,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['skin_mobile']) ? $arcadeSettings['skin_mobile'] : 0,
			),
		);
	}

	if (allowedTo('arcade_list'))
	{
		$context['profile_fields'] += array(
			'list_mobile' => array(
				'label' => $txt['arcade_user_list_mobile'],
				'type' => 'select',
				'options' => $mobileLists,
				'cast' => 'int',
				'validate' => 'int',
				'input_attr' => '',
				'value' => isset($arcadeSettings['list_mobile']) ? $arcadeSettings['list_mobile'] : 0,
			),
		);
	}

	if (!empty($arcadeModSettings['disableCustomPerPage']))
	{
		unset($context['profile_fields']['games_per_page']);
		unset($context['profile_fields']['scores_per_page']);
	}

	if (isset($_REQUEST['save']))
	{
		checkSession('post');
		list($updates, $romUpdates, $errors) = array(array(), array(), false);
		foreach ($events as $event)
		{
			foreach ($event['notification'] as $notify => $default)
			{
				if (empty($_POST[$notify]))
					$updates[] = array($memID, $notify, 0);
				else
					$updates[] = array($memID, $notify, 1);
			}
		}
		foreach ($arcadeRomUserSettings as $romsetting => $default) {
			$arcadeRomSettings[$romsetting] = empty($_POST[$romsetting]) ? 0 : (int)$_POST[$romsetting];
			$updates[] = array($memID, 'arcade_emulatorjs_rom', $arcadeRomSettings);
		}

		foreach ($context['profile_fields'] as $id => $field)
		{
			if (in_array($id, array('arcade_emulatorjs_rom', 'notifications', 'divider')))
				continue;

			if ($field['cast'] == 'int')
			{
				if (in_array($id, $context['arcade_admin_only']))
					$_POST[$id] = 0;

				$_POST[$id] = abs(floatval($_POST[$id]));

				// 99 is the limit for any select POST value
				$_POST[$id] = $_POST[$id] > 99 ? 99 : $_POST[$id];

				/*
				if ($field['type'] == 'check' && in_array($id, $newCheckboxes)) {
					$updates[] = array($memID, $id, $_POST[$id]);
				}
				*/
			}
			if (in_array($id, $context['arcade_admin_only'])) {
					$_POST[$id] = 0;
			}
			if ($field['type'] == 'select' && !in_array($id, $romSettingSelects))
			{
				if (isset($field['options'][$_POST[$id]])) {
					$updates[] = array($memID, $id, $_POST[$id]);
				}
			}
			elseif (!in_array($id, $romSettingSelects) && in_array($field['type'], array('number'))) {
				$updates[] = array($memID, $id, $_POST[$id]);
			}
		}

		if (!$errors)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}arcade_members
				WHERE id_member = {int:member}
				LIMIT 1',
				array(
					'member' => $memID == 0 ? $user_info['id'] : $memID,
				)
			);
			$row = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			$b = '';
			if (!empty($row))
			{
				foreach ($updates as $update)
				{
					if (stripos($update[1], 'divider') !== false)
						continue;

					$smcFunc['db_query']('', '
						UPDATE {db_prefix}arcade_members
						SET {raw:variable} = {' . (stripos($update[1], 'arcade_emulatorjs_rom') !== false ? 'string' : 'int') . ':value}
						WHERE id_member = {int:member}',
						array(
							'member' => $update[0],
							'variable' => $update[1],
							'value' => (stripos($update[1], 'arcade_emulatorjs_rom') !== false ? serialize($update[2]) : floatval($update[2])),
						)
					);
				}

			}
			else
			{
				$member = $memID == 0 ? $user_info['id'] : $memID;
				$variables = array('id_member', 'arena_invite', 'arena_match_end', 'arena_new_round', 'champion_email', 'champion_pm', 'new_game', 'notify_game_reports', 'games_per_page', 'archive_type', 'arcade_gametype', 'arcade_gametype_rom', 'new_champion_any', 'new_champion_own', 'scores_per_page', 'skin', 'list', 'skin_rom', 'list_rom', 'skin_mobile', 'list_mobile', 'detect_mobile', 'download_count', 'arcade_emulatorjs_rom');
				foreach ($updates as $update) {
					$new[$update[1]] = $update[2];
				}

				foreach ($variables as $variable)
				{
					if ($variable == 'id_member')
						$new['id_member'] = $member;
					elseif (empty($new[$variable]))
						$new[$variable] = 0;
				}

				$smcFunc['db_insert']('replace',
					'{db_prefix}arcade_members',
					array(
						'id_member' => 'int',
						'arena_invite' => 'int',
						'arena_match_end' => 'int',
						'arena_new_round' => 'int',
						'champion_email' => 'int',
						'champion_pm' => 'int',
						'new_game' => 'int',
						'notify_game_reports' => 'int',
						'games_per_page' => 'int',
						'archive_type' => 'int',
						'arcade_gametype' => 'int',
						'arcade_gametype_rom' => 'int',
						'new_champion_any' => 'int',
						'new_champion_own' => 'int',
						'scores_per_page' => 'int',
						'skin' => 'int',
						'list' => 'int',
						'skin_rom' => 'int',
						'list_rom' => 'int',
						'skin_mobile' => 'int',
						'list_mobile' => 'int',
						'detect_mobile' => 'int',
						'download_count' => 'int',
						'arcade_emulatorjs_rom' => 'text'
					),
					array(
						(int)$member,
						(int)$new['arena_invite'],
						(int)$new['arena_match_end'],
						(int)$new['arena_new_round'],
						(int)$new['champion_email'],
						(int)$new['champion_pm'],
						(int)$new['notify_game_reports'],
						(int)$new['new_game'],
						(int)$new['games_per_page'],
						(int)$new['archive_type'],
						(int)$new['arcade_gametype'],
						(int)$new['arcade_gametype_rom'],
						(int)$new['new_champion_any'],
						(int)$new['new_champion_own'],
						(int)$new['scores_per_page'],
						(int)$new['skin'],
						(int)$new['list'],
						(int)$new['skin_rom'],
						(int)$new['list_rom'],
						(int)$new['skin_mobile'],
						(int)$new['list_mobile'],
						(int)$new['detect_mobile'],
						(int)$new['download_count'],
						serialize($arcadeRomSettings),
					),
					array('id_member')
				);
			}

			redirectexit('action=profile;area=arcadeSettings;u=' . $memID);
		}
	}

	list($context['notifications'], $context['arcade_user_emulatorjs']) = array(array(), array());

	foreach ($events as $event)
	{
		foreach ($event['notification'] as $notify => $default)
		{
			if (stripos($notify, 'divider') !== false)
				$txt['arcade_notification_' . $notify] = '';

			$context['notifications'][$notify] = array(
				'id' => $notify,
				'text' => $txt['arcade_notification_' . $notify],
				'value' => isset($arcadeSettings[$notify]) ? (bool) $arcadeSettings[$notify] : $default,
				'default' => !isset($arcadeSettings[$notify])
			);
		}
	}

	foreach ($arcadeRomUserSettings as $romsetting => $default) {
		if (stripos($romsetting, 'divider') !== false)
			$txt['arcade_user_emulatorjs_' . $romsetting] = '';
		elseif (in_array($romsetting, $romSettingSelects)) {
			continue;
		}

		$context['arcade_user_emulatorjs'][$romsetting] = array(
			'id' => $romsetting,
			'text' => $txt['arcade_user_emulatorjs_' . $romsetting],
			'value' => isset($arcadeRomSettings[$romsetting]) ? (int)$arcadeRomSettings[$romsetting] : $default,
			'default' => !isset($arcadeRomSettings[$romsetting]) ? 0 : $arcadeRomSettings[$romsetting],
		);
	}

	// Titles for the notifications
	foreach ($romSettingSelects as $changeVal) {
		$romSelectIds .= '
			$("select#' . $changeVal . '").val("' . (!empty($arcadeRomUserSettings[$changeVal]) ? $arcadeRomUserSettings[$changeVal] : '0') . '").change();';
	}
	$context['html_headers'] .= '<script>
		function arc_dividers() {
			var lookDivider;
			var newTitle = ["' . $txt['arcadeProfTitle0'] . '", "' . $txt['arcadeProfTitle1'] . '", "' . $txt['arcadeEJS_settings'] . '", "' . $txt['arcadeEJS_general_settings'] . '", "", "", ""];
			for(i=0;i<6;i++)
			{
				lookDivider = "divider" + i.toString();
				if (document.getElementById(lookDivider))
				{
					var arcDivider = document.getElementById(lookDivider);
					arcDivider.type = "hidden";
					arcDivider.value = "";
					newTitle[i] = "<span style=\'padding: 0.2em 0.2em 1em 0.2em;border: 0px;font-weight: bold;text-decoration: underline;\'>" + newTitle[i] + "</span>";
					arcDivider.insertAdjacentHTML("afterend", newTitle[i]);
				}
			}

		}
		function arc_shaders() {' . ($romSelectIds) . '
		}
		$(document).ready(function() {
			arc_dividers();
			arc_shaders();
		});
	</script>';

	// Template
	$context['profile_custom_submit_url'] = $scripturl . '?action=profile;area=arcadeSettings;u=' . $memID . ';save';
	$context['page_desc'] = $txt['arcade_usersettings_desc'];
	$context['sub_template'] = 'edit_options';
}
?>