<?php
/*
 * Personalized BBC was developed for SMF forums c/o Chen Zhen @ https://web-develop.ca
 * Copyright 2025 @ web-develop.ca
 * Distributed under the CC BY-ND 4.0 License (http://creativecommons.org/licenses/by-nd/4.0/)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function SettingsPersonalizedBBC()
{
	global $txt, $scripturl, $context, $smcFunc, $modSettings, $settings;

	if (!allowedTo('admin_forum'))
		fatal_lang_error('PersonalizedBBC_ErrorMessage', false);

	$context['robot_no_index'] = true;
	list($_SESSION['personalizedBBC_duplicate_error'], $_SESSION['personalizedBBC_length_error'], $_SESSION['personalizedBBC_illegal_error']) = array(false, false, false);
	$listArray = array('enable', 'display', 'delete', 'jQueryEnable');
	$context['personalizedBBC_membergroups_view'] = !empty($context['personalizedBBC_membergroups_view']) ? $context['personalizedBBC_membergroups_view'] : array();
	$context['personalizedBBC_membergroups_use'] = !empty($context['personalizedBBC_membergroups_use']) ? $context['personalizedBBC_membergroups_use'] : array();
	$context['PersonalizedBBC_display'] = array('page' => false, 'pages' => '0');
	$context['PersonalizedBBC_imageType'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '.gif' : '.png';

	// load the jquery library for the tooltip
	$context['html_headers'] .= '
	<script src="' . $settings['default_theme_url'] . '/scripts/personalizedBBC.js" type="text/javascript"></script>
	<link href="' . $settings['default_theme_url'] . '/css/personalizedBBC.css" rel="stylesheet" type="text/css" />';

	$setting_types = array(
			'name' => array('string', false, 'name'),
			'description' => array('string', true, 'description'),
			'code' => array('code', false, 'code'),
			'image' => array('file', true, 'image'),
			'prior' => array('code', true, 'prior'),
			'after' => array('code', true, 'after'),
			'parse' => array('int', true, 'parse'),
			'trim' => array('int', true, 'trim'),
			'url_fix' => array('url', true, 'url_fix'),
			'file' => array('upload', true, 'file'),
			'type' => array('enable_int', true, 'type'),
			'block_lvl' => array('int', true, 'block_lvl'),
			'view_source' => array('int', true, 'view_source'),
			'enable' => array('checkbox', true, 'enable'),
			'display' => array('checkbox', true, 'display'),
			'delete' => array('del', false, 'del'),
			'jQueryEnable' => array('jquery', false, 'jquery'),
		);

	/*  Check for new settings values and save to database if necessary */
	if (isset($_GET['save']))
	{
		checkSession('request');
		$bbcTags = !empty($context['personalizedBBC']) ? $context['personalizedBBC'] : array();
		$_SESSION['personalizedBBC_skip'] = false;
		foreach ($bbcTags as $thisName => $context['personalizedBBC'])
		{
			$rfc = !empty($_REQUEST['url_fix']) ? cleanPersonalizedBBC_String($_REQUEST['url_fix']) : 'rfc0';
			$rfc = $rfc !== 'rfc3986x' && $rfc !== 'rfc3986' ? 'rfc0' : $rfc;
			$name = $smcFunc['strtolower'](!empty($context['current_name']) ? cleanPersonalizedBBC_String($context['current_name']) : (!empty($context['personalizedBBC']['name']) ? cleanPersonalizedBBC_String($context['personalizedBBC']['name']) : cleanPersonalizedBBC_String($context['personalizedBBC']['current_name'])));
			list($thisName, $name, $checkName) = array($smcFunc['strtolower'](trim($thisName)), $smcFunc['strtolower']($name), false);
			$context['current_name'] = !empty($context['current_name']) ? $smcFunc['strtolower'](trim($context['current_name'])) : $name;
			$filterName = preg_replace("#[a-zA-Z0-9_ ]#", '', (!empty($context['personalizedBBC']['name']) ? $context['personalizedBBC']['name'] : ''));
			$type = isset($context['personalizedBBC']['type']) ? $context['personalizedBBC']['type'] : '';
			$trim = isset($context['personalizedBBC']['trim']) ? (int)$context['personalizedBBC']['trim'] : 0;
			$parse = isset($context['personalizedBBC']['parse']) ? $context['personalizedBBC']['parse'] : '';
			$block_lvl = !empty($context['personalizedBBC']['block_lvl']) ? 1 : 0;
			$view_source = !empty($context['personalizedBBC']['view_source']) ? 1 : 0;
			$context['personalizedBBC']['code'] = !empty($context['personalizedBBC']['code']) ? $context['personalizedBBC']['code'] : '';
			$context['personalizedBBC']['code'] = $rfc !== 'rfc0' ? cleanPersonalizedBBC_Url('encode', false, $rfc, $context['personalizedBBC']['code']) : $context['personalizedBBC']['code'];
			foreach ($listArray as $comm)
				$$comm = isset($context['personalizedBBC'][$comm]) ? 1 : 0;

			// check if bbc exists within defaults
			$checkBBC = PersonalizedBBC_checkDefaults();

			if (in_array($name, $checkBBC) && empty($context['PersonalizedBBC_override']))
			{
				$_SESSION['personalizedBBC_duplicate_error'] = true;
				redirectexit($scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;name=' . $thisName);
			}

			if (strlen($name) > 25)
			{
				$_SESSION['personalizedBBC_length_error'] = true;
				$name = substr($thisName, 0, 25);
				redirectexit($scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;name=' . $name);
			}

			if ($filterName)
			{
				$_SESSION['personalizedBBC_illegal_error'] = true;
				$context['personalizedBBC']['name'] = $name;
				redirectexit($scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;name=' . $name);
			}

			// $1, $2 and/or $3 entered in the input must be changed to HTML entity
			$context['personalizedBBC']['code'] = preg_replace(array('#\$1#', '#\$2#', '#\$3#'),  array('&#036;1', '&#036;2', '&#036;3'), $context['personalizedBBC']['code']);

			if ($type != 3)
			{
				if (!$parse)
				{
					if ($type == 0)
						$context['personalizedBBC']['code'] = preg_replace('#\{content\}#si',  '\$1', $context['personalizedBBC']['code']);
					elseif ($type == 1)
						$context['personalizedBBC']['code'] = preg_replace(array('#\{option\}#si', '#\{content\}#si'),  array('\$2', '\$1'), $context['personalizedBBC']['code']);
					else
						$context['personalizedBBC']['code'] = preg_replace(array('#\{option1\}#si', '#\{option2\}#si', '#\{content\}#si'),  array('\$2', '\$3', '\$1'), $context['personalizedBBC']['code']);
				}
				else
				{
					if ($type != 2)
						$text = preg_replace('#\{option\}#si', '\$1', $context['personalizedBBC']['code']);
					else
						$text = preg_replace(array('#\{option1\}#si', '#\{option2\}#si'), array('\$1', '\$2'), $context['personalizedBBC']['code']);

					$pos = stripos($text, '{content}');
					if ($pos === false)
					{
						$context['personalizedBBC']['prior'] = empty($context['url_fix']) ? $text : cleanPersonalizedBBC_Url('encode', 'prior', $rfc, $text);
						$context['personalizedBBC']['after'] = '';
					}
					else
					{
						$context['personalizedBBC']['prior'] = empty($context['url_fix']) ? substr($text, 0, $pos) : cleanPersonalizedBBC_Url('encode', 'prior', $rfc, substr($text, 0, $pos));
						$context['personalizedBBC']['after'] = empty($context['url_fix']) ? substr($text, $pos + 9) : cleanPersonalizedBBC_Url('encode', 'after', $rfc, substr($text, $pos + 9));
					}

					$context['personalizedBBC']['code'] = preg_replace(array('#\{content\}#si', '#\{option\}#si', '#\{option1\}#si', '#\{option2\}#si'),  array('\$1', '\$2', '\$2', '\$3'), $context['personalizedBBC']['code']);
				}
			}
			else
			{
				$context['personalizedBBC']['code'] = preg_replace(array('#\{content\}#si', '#\{option\}#si', '#\{option1\}#si', '#\{option2\}#si'), '&#32;', $context['personalizedBBC']['code']);
				list($parse, $context['personalizedBBC']['parse']) = array(0, 0);
			}

			if (!$name)
				continue;

			// this table does not auto increment by design therefore create a new default column where one does not exist (key = name)
			$result = $smcFunc['db_query']('', "
				SELECT *
				FROM {db_prefix}personalized_bbc
				WHERE name = {string:name}
				LIMIT 1",
				array('name' => $name)
			);
			while ($val = $smcFunc['db_fetch_assoc']($result))
			{
				$checkName = !empty($val['name']) ? $val['name'] : '';
				foreach ($setting_types as $column => $setting)
				{
					if (in_array($column, $listArray) && !empty($context['personalizedBBC']['list']))
					{
						$context['personalizedBBC'][$column] = isset($$column) ? $$column : 0;
						continue;
					}
					elseif (!empty($context['personalizedBBC']['list']))
						continue;
					elseif ($column === 'delete' || $column === 'name' || !$checkName)
						continue;

					$context['personalizedBBC'][$column] = !empty($val[$column]) && !isset($context['personalizedBBC'][$column]) ? $val[$column] : (isset($context['personalizedBBC'][$column]) ? $context['personalizedBBC'][$column] : '');
					$context['personalizedBBC']['delete'] = 0;
				}
			}
			$smcFunc['db_free_result']($result);

			foreach (array('view', 'use') as $intent)
			{
				$result = $smcFunc['db_query']('', "
					SELECT permission, id_group, add_deny
					FROM {db_prefix}permissions
					WHERE permission = {string:perm}",
					array('perm' => 'personalized_bbc_' . trim($context['current_name'] . '_' . $intent))
				);
				while ($val = $smcFunc['db_fetch_assoc']($result))
				{
					$context['personalizedBBC']['permissions_' . $intent][$val['id_group']] = array(
						'id_group' => isset($val['id_group']) ? $val['id_group'] : null,
						'add_deny' => !empty($val['add_deny']) ? 1 : 0,
					);
				}
				$smcFunc['db_free_result']($result);
			}

			// set default values prior to adding the new tag
			if (!$checkName && empty($context['personalizedBBC']['delete']) && empty($_SESSION['personalizedBBC_skip']))
			{
				$request = $smcFunc['db_insert']('insert', "
					{db_prefix}personalized_bbc",
					array('name' => 'string', 'code' => 'string', 'description' => 'string', 'image' => 'string', 'prior' => 'string', 'after' => 'string', 'url_fix' => 'string', 'parse' => 'int', 'trim' => 'int', 'type' => 'int', 'block_lvl' => 'int', 'enable' => 'int', 'display' => 'int', 'view_source' => 'int'),
					array($name, '', '', '', '', '', 'rfc0', 0, 0, 0, 0, 1, 1, 0),
					array('bbc_id')
				);
			}

			foreach ($setting_types as $key => $data)
			{
				if (!in_array($key, $listArray) && !empty($context['personalizedBBC']['list']))
					continue;
				elseif (empty($context['personalizedBBC']['list']))
				{
					if ($data[1] && !isset($context['personalizedBBC'][$key]))
						$context['personalizedBBC'][$key] = '';
					elseif (!$data[1] && empty($context['personalizedBBC'][$key]))
					{
						$context['personalizedBBC'][$key] = false;
						continue;
					}
				}

				// delete the old bbc tag if the name was altered
				if (empty($context['personalizedBBC']['list']) && !empty($context['current_name']) && !empty($context['personalizedBBC']['name']))
				{
					if ($thisName !== $context['personalizedBBC']['name'] && $key === 'description')
					{
						$smcFunc['db_query']('', '
							DELETE FROM {db_prefix}personalized_bbc
							WHERE name = {string:name}',
							array('name' => $thisName)
						);
						$smcFunc['db_query']('', '
							DELETE FROM {db_prefix}permissions
							WHERE permission LIKE {string:perm}',
							array('perm' => 'personalized_bbc_' . $thisName . '_view')
						);
						$smcFunc['db_query']('', '
							DELETE FROM {db_prefix}permissions
							WHERE permission LIKE {string:perm}',
							array('perm' => 'personalized_bbc_' . $thisName . '_use')
						);

						$context['current_name'] = $context['personalizedBBC']['name'];
					}
				}

				if (!isset($context['personalizedBBC'][$key]))
					continue;

				$value = $key === 'name' ? cleanPersonalizedBBC_String(trim($context['personalizedBBC'][$key])) : $context['personalizedBBC'][$key];
				switch ($data[0])
				{
					case 'string':
						createPersonalizedBBC_setting('personalized_bbc', $key, $value, $name);
						continue 2;
					case 'url':
						createPersonalizedBBC_setting('personalized_bbc', $key, $rfc, $name);
						continue 2;
					case 'int':
						$val = (int)$value > 0 ? (int)$value : ($value === 'on' ? 1 : 0);
						$val = isset(${$data[2]}) ? (int)${$data[2]} : $val;
						if ($name)
							$request = $smcFunc['db_query']('', "
								UPDATE {db_prefix}personalized_bbc
								SET {raw:key} = {int:val}
								WHERE name = {string:name}
								LIMIT 1",
								array('key' => $key, 'val' => $val, 'name' => $name)
							);
						continue 2;
					case 'enable_int':
						if (!empty($val))
							$val = ($value == 2) ? 2 : 1;
						$request = $smcFunc['db_query']('', "
									UPDATE {db_prefix}personalized_bbc
									SET type = {int:val}
									WHERE name = {string:name}
									LIMIT 1",
									array('val' => (int)$type, 'name' => $name)
								);
						continue 2;
					case 'file':
						$val = PersonalizedBBC_CheckImage(array($name => trim($value)));
						$val = preg_replace('/\.[^.]*$/', '', $val);
						createPersonalizedBBC_setting('personalized_bbc', $key, $val, $name);
						continue 2;
					case 'upload':
						$val = !empty($_FILES["file"]) ? $_FILES["file"] : '';
						PersonalizedBBC_CheckUpload($name, $val);
						continue 2;
					case 'code':
						$val = cleanPersonalizedBBC_Code($value, $trim);
						createPersonalizedBBC_setting('personalized_bbc', $key, $val, $name);
						continue 2;
					case 'checkbox':
						$val = !empty($context['personalizedBBC'][$key]) ? 1 : 0;

						if (!empty($context['personalizedBBC']['current_name']))
							$request = $smcFunc['db_query']('', "
								UPDATE {db_prefix}personalized_bbc
								SET {raw:key} = {int:val}
								WHERE name = {string:name}
								LIMIT 1",
								array('key' => $key, 'val' => (int)$val, 'name' => $context['personalizedBBC']['current_name'])
							);
						continue 2;
					case 'del':
						if ($key === 'delete' && !empty($context['personalizedBBC']['delete']))
						{
							$smcFunc['db_query']('', '
								DELETE FROM {db_prefix}personalized_bbc
								WHERE name = {string:name}',
								array('name' => $context['personalizedBBC']['current_name'])
							);
							$smcFunc['db_query']('', '
								DELETE FROM {db_prefix}permissions
								WHERE permission LIKE {string:perm}',
								array('perm' => 'personalized_bbc_' . $context['personalizedBBC']['current_name'] . '_view')
							);
							$smcFunc['db_query']('', '
								DELETE FROM {db_prefix}permissions
								WHERE permission LIKE {string:perm}',
								array('perm' => 'personalized_bbc_' . $context['personalizedBBC']['current_name'] . '_use')
							);
							$_SESSION['personalizedBBC_skip'] = true;
						}
						continue 2;
					case 'jquery':
						$val = !empty($_REQUEST['jQueryEnable']) ? 1 : 0;
						$setArray['personalizedBBC_jQuery'] = $val;
						updateSettings($setArray);
						$modSettings['personalizedBBC_jQuery'] = $val;
						continue 2;
				}
			}
		}

		// Adjust membergroup permissions
		$perms = array();
		foreach (array('view', 'use') as $intent)
		{
			foreach ($context['personalizedBBC_membergroups_' . $intent] as $group => $value)
			{
				$value = isset($value) ? $value : (isset($context['personalizedBBC']['permissions_' . $intent][$group]['add_deny']) ? $context['personalizedBBC']['permissions_' . $intent][$group]['add_deny'] : null);
				$value = !isset($value) ? '' : ($value == 1 ? 1 : 0);
				$perms[] = array('group' => $group, 'name' => 'personalized_bbc_' . $name . '_' . $intent, 'value' => $value);
			}
		}
		if (!empty($name))
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}permissions
				WHERE permission LIKE {string:perm}',
				array('perm' => 'personalized_bbc_' . $name . '_view')
			);
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}permissions
				WHERE permission LIKE {string:perm}',
				array('perm' => 'personalized_bbc_' . $name . '_use')
			);
			foreach ($perms as $perm)
			{
				$smcFunc['db_insert']('replace',
					'{db_prefix}permissions',
					array(
						'id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int',
					),
					array(
						$perm['group'], $perm['name'], $perm['value'],
					),
					array('id_group', 'permission')
				);
			}

			// Clean the entire cache for permission changes to immediately take affect
			clean_cache();
		}
	}

	// redirect back to the revision template if we are testing
	if (!empty($_REQUEST['test_bbc']))
	{
		$_SESSION['bbcode_test_content'] = !empty($_REQUEST['bbcode_test_content']) ? $_REQUEST['bbcode_test_content'] : '';
		$_SESSION['bbcode_test_option1'] = !empty($_REQUEST['bbcode_test_option1']) ? $_REQUEST['bbcode_test_option1'] : '';
		$_SESSION['bbcode_test_option2'] = !empty($_REQUEST['bbcode_test_option2']) ? $_REQUEST['bbcode_test_option2'] : '';
		$context['personalizedBBC']['prior'] = !empty($context['personalizedBBC']['prior']) ? $context['personalizedBBC']['prior'] : '';
		$context['personalizedBBC']['after'] = !empty($context['personalizedBBC']['after']) ? $context['personalizedBBC']['after'] : '';
		$_SESSION['bbcode_test_string'] = TestPersonalizedBBC($parse, $type, $context['personalizedBBC']['code'], $context['personalizedBBC']['prior'], $context['personalizedBBC']['after'], $_SESSION['bbcode_test_content'], $_SESSION['bbcode_test_option1'], $_SESSION['bbcode_test_option2']);
		redirectexit($scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;name=' . $context['current_name'] . ';#personalizedBBC_index');
	}

	// unset all the testing variables
	unset($_REQUEST['bbcode_test_content'], $_REQUEST['bbcode_test_option1'], $_REQUEST['bbcode_test_option2']);
	unset($_SESSION['bbcode_test_content'], $_SESSION['bbcode_test_option1'], $_SESSION['bbcode_test_option2'], $_SESSION['bbcode_test_string']);

	// Gather data from configurations stored in the Personalized BBC settings table
	list($context['personalizedBBC_list'], $context['personalizedBBC']) = array(array(), array());
	$result = $smcFunc['db_query']('', "
		SELECT *
		FROM {db_prefix}personalized_bbc
		ORDER BY name"
	);

	while ($val = $smcFunc['db_fetch_assoc']($result))
	{
		$id = $val['name'];
		foreach ($setting_types as $key => $setting_type)
			$temp[$id][$key] = !empty($val[$key]) ? $val[$key] : false;

		if (empty($temp[$id]['parse']))
		{
			if (!empty($temp[$id]['code']) && (int)$temp[$id]['type'] == 1)
				$temp[$id]['code'] = preg_replace(array('#\$1#', '#\$2#'), array('{content}', '{option}'), $temp[$id]['code']);
			elseif (!empty($temp[$id]['code']) && (int)$temp[$id]['type'] == 2)
				$temp[$id]['code'] = preg_replace(array('#\$1#', '#\$2#', '#\$3#'), array('{content}', '{option1}', '{option2}'), $temp[$id]['code']);
			else
				$temp[$id]['code'] = preg_replace('#\$1#', '{content}', $temp[$id]['code']);
		}
		else
		{
			$temp[$id]['code'] = '';
			if ((int)$temp[$id]['type'] != 2)
			{
				if (!empty($temp[$id]['prior']))
					$temp[$id]['code'] = preg_replace('#\$1#', '{option}', $temp[$id]['prior']) . '{content}';

				if (!empty($temp[$id]['after']))
					$temp[$id]['code'] .= preg_replace('#\$1#', '{option}', $temp[$id]['after']);
			}
			else
			{
				if (!empty($temp[$id]['prior']))
					$temp[$id]['code'] = preg_replace(array('#\$1#', '#\$2#'), array('{option1}', '{option2}'), $temp[$id]['prior']) . '{content}';

				if (!empty($temp[$id]['after']))
					$temp[$id]['code'] .= preg_replace(array('#\$1#', '#\$2#'), array('{option1}', '{option2}'), $temp[$id]['after']);
			}
		}
	}
	$smcFunc['db_free_result']($result);

	// Sort the list
	if (!empty($temp))
	{
		foreach ($temp as $key => $data)
			$new[] = $key;
		natsort($new);
		foreach ($new as $key)
			$context['personalizedBBC_list'][$key] = $temp[$key];

	}

	// Set the $context for the display template
	$context['personalizedBBC_jQuery'] = !empty($modSettings['personalizedBBC_jQuery']) ? (int)$modSettings['personalizedBBC_jQuery'] : 0;
	$context['settings_title'] = $txt['PersonalizedBBC_Settings'];
	$context['post_url'] = $scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Settings;current_page=' . ($context['current_page']+1) . ';' . $context['session_var'] . '=' . $context['session_id'] . ';save';
	$context['personalizedBBC'] = PersonalizedBBC_pagination($context['personalizedBBC_list'], $scripturl . '?action=admin;area=PersonalizedBBC;', 10);
	$context['PersonalizedBBC_display'] = PersonalizedBBC_pages($txt['PersonalizedBBC_page'], '#page_top', $scripturl . '?action=admin;area=PersonalizedBBC;', $context['current_pages']);
	$context['sub_template'] = 'personalizedBBC_List';
	$context['linktree'][] = array('url' => $scripturl . '?action=admin;area=PersonalizedBBC;current_page=' . ($context['current_page']+1) . ';' . $context['session_var'] . '=' . $context['session_id'] . ';#page_top', 'name' => $txt['PersonalizedBBC_tabtitle_list']);
	$context['page_title'] = $txt['PersonalizedBBC_tabtitle_list'];

	loadTemplate('PersonalizedBBC_Admin');
}

function EntryPersonalizedBBC()
{
	global $txt, $scripturl, $context, $smcFunc, $modSettings, $settings;

	if (!allowedTo('admin_forum'))
		fatal_lang_error('PersonalizedBBC_ErrorMessage', false);

	$context['robot_no_index'] = true;
	$context['current_name'] = !empty($_REQUEST['name']) ? $smcFunc['strtolower'](trim($_REQUEST['name'])) : '';
	$context['PersonalizedBBC_imageType'] = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? '.gif' : '.png';
	$context['PersonalizedBBC_Images'] = PersonalizedBBC_images();
	// load the jquery library for the tooltip
	$context['html_headers'] .= '
	<script src="' . $settings['default_theme_url'] . '/scripts/personalizedBBC.js" type="text/javascript"></script>
	<link href="' . $settings['default_theme_url'] . '/css/personalizedBBC.css" rel="stylesheet" type="text/css" />';

	$setting_types = array('name', 'description', 'code', 'image', 'prior', 'after', 'parse', 'trim', 'type', 'block_lvl', 'enable', 'display', 'delete', 'current_name', 'view_source', 'url_fix');

	foreach ($setting_types as $setting_type)
		$context['personalizedBBC'][$setting_type] = '';
	$context['personalizedBBC']['image'] = $context['PersonalizedBBC_Images'][0];

	// All nenbergroups can use a newly created bbcode by default
	if (empty($context['current_name']))
	{
		foreach (array('view', 'use') as $intent)
		{
			$result = $smcFunc['db_query']('', "
				SELECT id_group
				FROM {db_prefix}membergroups
				WHERE id_group",
				array()
			);

			while ($val = $smcFunc['db_fetch_assoc']($result))
			{
				$context['personalizedBBC']['permissions_' . $intent][$val['id_group']] = array(
					'id_group' => isset($val['id_group']) ? $val['id_group'] : null,
					'add_deny' => 1,
				);
			}

			$smcFunc['db_free_result']($result);
			$context['personalizedBBC']['permissions_' . $intent][-1] = array(
				'id_group' => -1,
				'add_deny' => 1,
			);
			$context['personalizedBBC']['permissions_' . $intent][0] = array(
				'id_group' => 0,
				'add_deny' => 1,
			);
		}

		// autolink is also disabled by default
		$context['personalizedBBC']['view_source'] = 1;
	}

	// Gather data from configurations stored in the settings table
	if (!empty($context['current_name']))
	{
		$result = $smcFunc['db_query']('', "
			SELECT *
			FROM {db_prefix}personalized_bbc
			WHERE name = {string:name}",
			array('name' => $context['current_name'])
		);
		while ($val = $smcFunc['db_fetch_assoc']($result))
		{
			$rfc = !empty($val['url_fix']) ? $val['url_fix'] : 'rfc0';
			$val['url_fix'] = $rfc !== 'rfc3986x' && $rfc !== 'rfc3986' ? 'rfc0' : $rfc;
			$val['image'] = !empty($val['image']) ? str_replace(array('personalizedBBC/', $context['PersonalizedBBC_imageType']), '', $val['image']) . $context['PersonalizedBBC_imageType'] : $context['PersonalizedBBC_Images'][0];
			$val['code'] = !empty($val['code']) ? cleanPersonalizedBBC_Url('decode', false, $rfc, $val['code']) : '';
			$val['prior'] = !empty($val['prior']) ? cleanPersonalizedBBC_Url('decode', false, $rfc, $val['prior']) : '';
			$val['after'] = !empty($val['after']) ? cleanPersonalizedBBC_Url('decode', false, $rfc, $val['after']) : '';

			foreach ($setting_types as $setting_type)
				$context['personalizedBBC'][$setting_type] = isset($val[$setting_type]) ? $val[$setting_type] : '';

			if (empty($val['parse']) || $context['personalizedBBC']['type'] == 3)
			{
				if ((int)$context['personalizedBBC']['type'] == 1)
					$context['personalizedBBC']['code'] = preg_replace(array('#\$1#', '#\$2#'),  array('{content}', '{option}'), $context['personalizedBBC']['code']);
				elseif ((int)$context['personalizedBBC']['type'] == 2)
					$context['personalizedBBC']['code'] = preg_replace(array('#\$1#', '#\$2#', '#\$3#'),  array('{content}', '{option1}', '{option2}'), $context['personalizedBBC']['code']);
				else
					$context['personalizedBBC']['code'] = preg_replace('#\$1#',  '{content}', $context['personalizedBBC']['code']);
			}
			else
			{
				$context['personalizedBBC']['code'] = '';
				if ((int)$context['personalizedBBC']['type'] != 2)
				{
					if (isset($context['personalizedBBC']['prior']))
						$context['personalizedBBC']['code'] = preg_replace('#\$1#',  '{option}', $context['personalizedBBC']['prior']) . '{content}';

					if (isset($context['personalizedBBC']['after']))
						$context['personalizedBBC']['code'] .= preg_replace('#\$1#',  '{option}', $context['personalizedBBC']['after']);
				}
				else
				{
					if (isset($context['personalizedBBC']['prior']))
						$context['personalizedBBC']['code'] = preg_replace(array('#\$1#', '#\$2#'),  array('{option1}', '{option2}'), $context['personalizedBBC']['prior']) . '{content}';

					if (isset($context['personalizedBBC']['after']))
						$context['personalizedBBC']['code'] .= preg_replace(array('#\$1#', '#\$2#'),  array('{option1}', '{option2}'), $context['personalizedBBC']['after']);
				}
			}
		}
		$smcFunc['db_free_result']($result);

		foreach (array('view', 'use') as $intent)
		{
			$result = $smcFunc['db_query']('', "
				SELECT permission, id_group, add_deny
				FROM {db_prefix}permissions
				WHERE permission = {string:perm}",
				array('perm' => 'personalized_bbc_' . trim($context['current_name'] . '_' . $intent))
			);
			while ($val = $smcFunc['db_fetch_assoc']($result))
			{
				$context['personalizedBBC']['permissions_' . $intent][$val['id_group']] = array(
					'id_group' => isset($val['id_group']) ? $val['id_group'] : null,
					'add_deny' => !empty($val['add_deny']) ? 1 : 0,
				);
			}
			$smcFunc['db_free_result']($result);
		}
	}

	// Set the $context for the display template
	$context['PersonalizedBBC_Membergroups'] = PersonalizedBBC_load_membergroups();
	$context['settings_title'] = $txt['PersonalizedBBC_Settings'];
	$context['post_url'] = $scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Settings;' . $context['session_var'] . '=' . $context['session_id'] . ';save';
	$context['sub_template'] = 'personalizedBBC_Edit';
	$context['page_title'] = $txt['PersonalizedBBC_tabtitle_rev'];
	$context['linktree'][] = array('url' => $scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;' . (!empty($context['current_name']) ? 'name=' . $context['current_name'] . ';' : '') . $context['session_var'] . '=' . $context['session_id'], 'name' =>$txt['PersonalizedBBC_tabtitle_rev']);

	loadTemplate('PersonalizedBBC_Admin');
}

function TestPersonalizedBBC($parse, $type, $code, $prior, $after, $content, $option1, $option2)
{
	if (!allowedTo('admin_forum'))
		fatal_lang_error('PersonalizedBBC_ErrorMessage', false);

	if (empty($parse))
	{
		if (!empty($code) && (int)$type == 1)
		{
			$code = preg_replace(array('#\$1#', '#\$2#'), array('{content}', '{option}'), $code);
			$test = str_replace(array('{content}', '{option}'), array($content, $option1), $code);
		}
		elseif (!empty($code) && (int)$type == 2)
		{
			$code = preg_replace(array('#\$1#', '#\$2#', '#\$3#'), array('{content}', '{option1}', '{option2}'), $code);
			$test = str_replace(array('{content}', '{option1}', '{option2}'), array($content, $option1, $option2), $code);
		}
		else
		{
			$code = preg_replace('#\$1#', '{content}', $code);
			$test = str_replace('{content}', $content, $code);
		}
	}
	else
	{
		$code = '';
		$test = '';
		if ((int)$type != 2)
		{
			if (!empty($prior))
			{
				$code = preg_replace('#\$1#', '{option}', $prior) . '{content}';
				$test = str_replace(array('{content}', '{option}'), array($content, $option1), $code);
			}

			if (!empty($after))
			{
				$code .= preg_replace('#\$1#', '{option}', $after);
				$test .= str_replace('{option}', $option1, $code);
			}
		}
		else
		{
			if (!empty($prior))
			{
				$code = preg_replace(array('#\$1#', '#\$2#'), array('{option1}', '{option2}'), $prior) . '{content}';
				$test = str_replace(array('{content}', '{option1}', '{option2}'), array($content, $option1, $option2), $code);
			}

			if (!empty($after))
			{
				$code .= preg_replace(array('#\$1#', '#\$2#'), array('{option1}', '{option2}'), $after);
				$test .= str_replace(array('{option1}', '{option2}'), array($option1, $option2), $code);
			}
		}
	}

	$test = html_entity_decode($test, ENT_COMPAT|ENT_SUBSTITUTE|ENT_HTML5);
	$string = implode("", explode("\\", $test));
	$test = stripslashes(trim($string));
	//$test = !empty($test) ? parse_bbc(un_htmlspecialchars($test)) : '';
	return $test;
}
?>