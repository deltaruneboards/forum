<?php
/*
 * Personalized BBC was developed for SMF forums c/o Chen Zhen @ https://web-develop.ca
 * Copyright 2025  @ web-develop.ca
 * Distributed under the CC BY-ND 4.0 License (http://creativecommons.org/licenses/by-nd/4.0/)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file handles all of the Personalized BBC integrated hooks

	void function PersonalizedBBC_load_permissions(&$actionArray)
		- Inserts necessary data into SMF permissions array

	void function PersonalizedBBC_admin_areas(&$admin_areas)
		- Loads Personalized BBC admin file

	void function PersonalizedBBC_codes()
		- Adds Personalized BBCs to the bbc array

	void function PersonalizedBBC_buttons()
		- Adds Personalized BBCs to the message editor bbc array

	void function PersonalizedBBC_message(&$message, &$smileys, &$cache_id, &$parse_tags)
		- edit $message variable to remove restricted bbcodes

	void function PersonalizedBBC_load()
		- Loads necessary language file
		- Adds varying permission data to the language array

	void function PersonalizedBBC_parser($text)
		- Parses message body for restricted BBC tags

	void function PersonalizedBBC_redact($string)
		- Parses string for BBCodes being used outside of posts/pm's
*/

function PersonalizedBBC_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $smcFunc, $modSettings;
	loadLanguage('PersonalizedBBC');
	$permNames = array();
	$key = 0;

	$request = $smcFunc['db_query']('', '
			SELECT name
			FROM {db_prefix}personalized_bbc
			ORDER BY name'
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$permNames[] = $row['name'];

	$smcFunc['db_free_result']($request);

	// Sort the list
	natsort($permNames);

	foreach ($permNames as $permission)
	{
		$key++;
		$BBC_Name_View = !empty($permission) ? $smcFunc['strtolower'](trim('personalized_bbc_' . $permission . '_view')) : 'personalized_bbc_' . (string)$key . '_view';
		$BBC_Name_Use = !empty($permission) ? $smcFunc['strtolower'](trim('personalized_bbc_' . $permission . '_use')) : 'personalized_bbc_' . (string)$key . '_use';

		$permissionList['membergroup'] += array(
			$BBC_Name_View => array(false, 'PersonalizedBBC_perms', 'PersonalizedBBC_perms'),
			$BBC_Name_Use => array(false, 'PersonalizedBBC_perms', 'PersonalizedBBC_perms'),
		);
	}

	// SMF 2.1.X behavior will differ
	$version = $modSettings['smfVersion'];
	if (preg_match('/\d+(?:\.\d+)+/', $modSettings['smfVersion'], $matches))
		$version = $matches[0];

	if ($version !== '2.1' && version_compare($version, '2.1.0', '<'))
	{
		if (!empty($permNames))
		{
			$permissionGroups['membergroup']['simple'] += array(
				'PersonalizedBBC_perms',
			);
			$permissionGroups['membergroup']['classic'] += array(
				'PersonalizedBBC_perms',
			);
		}
	}
}

function PersonalizedBBC_admin_areas(&$admin_areas)
{
	global $txt;
	loadLanguage('PersonalizedBBC');

	$PersonalizedBBC = array(
		'PersonalizedBBC' => array(
			'label' => $txt['PersonalizedBBC_tabtitle'],
			'file' => 'PersonalizedBBC_Admin.php',
			'function' => 'PersonalizedBBC_Admin',
			'icon' => 'PersonalizedBBC_settings.png',
			'permission' => allowedTo('admin_forum'),
			'subsections' => array(),
		),
	);

	$admin_areas['layout']['areas'] += $PersonalizedBBC;
}

function PersonalizedBBC_codes(&$codes, &$no_autolink_tags)
{
	global $smcFunc, $txt;
	loadLanguage('PersonalizedBBC');
	$key = 0;

	$request = $smcFunc['db_query']('', '
			SELECT name, description, image, code, prior, after, parse, trim, type, block_lvl, enable, display, view_source
			FROM {db_prefix}personalized_bbc
			ORDER BY name'
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$key++;
		$tag = !empty($row['name']) ? $smcFunc['strtolower'](trim($row['name'])) : 'personalized_bbc_' . (string)$key;
		if (!empty($row['enable']) && !empty($row['code']) && allowedTo('personalized_bbc_' . $tag . '_view'))
		{
			$parsed = !empty($row['parse']) ? (int)$row['parse'] : 0;
			$tagType = !empty($row['type']) ? (int)$row['type'] : 0;
			$trim = !empty($row['trim']) && $row['trim'] == 1 ? 'inside' : (!empty($row['trim']) && $row['trim'] == 2 ? 'outside' : '');

			if ($tagType == 3)
			{
				$type = 'closed';
				$parsed = 0;
			}
			elseif (empty($parsed))
			{
				if ($tagType == 1)
					$type = 'unparsed_equals_content';
				elseif ($tagType == 2)
					$type = 'unparsed_commas_content';
				else
					$type = 'unparsed_content';
			}
			else
			{
				if ($tagType == 1 && $parsed == 1)
					$type = 'unparsed_equals';
				elseif ($tagType == 1)
					$type = 'parsed_equals';
				elseif ($tagType == 2)
					$type = 'unparsed_commas';
				else
					$type = '';
			}

			$codes[] = array(
				'tag' => $tag,
				'content' => empty($parsed) ? un_htmlspecialchars($row['code']) : '',
				'before' => !empty($parsed) && !empty($row['prior']) ? un_htmlspecialchars($row['prior']) : '',
				'after' => !empty($parsed) && !empty($row['after']) ? un_htmlspecialchars($row['after']) : '',
				'trim' => $trim,
				'type' => !empty($type) ? $type : null,
				'block_level' => !empty($row['block_lvl']) ? true : false,
				'enable' => !empty($row['enable']) ? true : false,
				'show' => !empty($row['display']) ? true : false,
			);

			// add to the no link array if it was opted
			if (!empty($row['view_source']) && !in_array($tag, $no_autolink_tags))
				$no_autolink_tags[] = trim($tag);
		}
	}
	$smcFunc['db_free_result']($request);

	return $codes;
}

function PersonalizedBBC_buttons(&$bbc_buttons)
{
	global $smcFunc, $txt, $settings, $modSettings;
	loadLanguage('PersonalizedBBC');

	list($datums, $key, $imageType, $lvl) = array(array(), 0, 'png', 1);
	$request = $smcFunc['db_query']('', '
			SELECT name, description, image, code, prior, after, parse, trim, type, block_lvl, enable, display, view_source
			FROM {db_prefix}personalized_bbc
			WHERE display = {int:display}
			ORDER BY name',
			array('display' => 1)
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$key++;
		$name = !empty($row['name']) ? trim($row['name']) : 'personalized_bbc_' . (string)$key;
		if (!empty($row['enable']) && !empty($row['code']) && allowedTo('personalized_bbc_' . $name . '_use'))
		{
			$parsed = !empty($row['parse']) ? (int)$row['parse'] : 0;
			$tagType = !empty($row['type']) ? (int)$row['type'] : 0;

			if ($tagType == 3)
			{
				$type = 'closed';
				$parsed = 0;
			}
			elseif (empty($parsed))
			{
				if ($tagType == 1)
					$type = 'unparsed_equals_content';
				elseif ($tagType == 2)
					$type = 'unparsed_commas_content';
				else
					$type = 'unparsed_content';
			}
			else
			{
				if ($tagType == 1 && $parsed == 1)
					$type = 'unparsed_equals';
				elseif ($tagType == 1)
					$type = 'parsed_equals';
				elseif ($tagType == 2)
					$type = 'unparsed_commas';
				else
					$type = '';
			}

			$datums[] = array(
				'name' => $name,
				'description' => !empty($row['description']) ? $row['description'] : $name,
				'code' => un_htmlspecialchars($row['code']),
				'before' => !empty($row['prior']) ? $row['prior'] : '',
				'after' => !empty($row['after']) ? $row['after'] : '',
				'image' => !empty($row['image']) ? str_replace('.' . $imageType, '', $row['image']) : trim($name),
				'show' => !empty($row['display']) ? true : false,
				'enable' => !empty($row['enable']) ? true : false,
				'type' => $type,
				'block_lvl' => !empty($row['block_lvl']) ? true : false,
				'view_source' => !empty($row['view_source']) ? true : false,
			);
		}
	}
	$smcFunc['db_free_result']($request);

	foreach ($datums as $datum)
	{
		if (!empty($datum['show']) && !empty($datum['enable']))
		{
			if (strpos($datum['type'], 'commas') !== false)
				$before = '[' . $datum['name'] . '=0,0]';
			elseif ($datum['type'] === 'unparsed_equals' || $datum['type'] === 'parsed_equals' || $datum['type'] === 'unparsed_equals_content')
				$before = '[' . $datum['name'] . '=]';
			else
				$before = '[' . $datum['name'] . ']';

			$bbc_buttons[$lvl][] = array(
					'image' => $datum['image'],
					'code' => $datum['name'],
					'html' => !empty($datum['view_source']) ? $datum['code'] : '',
					'description' => $datum['description'],
					'block_lvl' => $datum['block_lvl'],
					'allowed_children' => !empty($datum['allowed_children']) ? $datum['allowed_children'] : 'null',
					'before' => $before,
					'after' => ($datum['type'] !== 'closed' ? '[/' . $datum['name'] . ']' : ''),
			);
		}
	}
}

function PersonalizedBBC_message(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	// Remove any Personalized BBC where the user does not have permission to view it
	$message = PersonalizedBBC_redact($message);
}

function PersonalizedBBC_load()
{
	global $smcFunc, $txt, $helptxt, $modSettings, $personalized_BBC;
	loadLanguage('PersonalizedBBC');
	$personalized_BBC = array();
	$key = 0;
	$imageType = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'gif' : 'png';

	$helptxt['personalizedBBC_tagImage'] = str_replace('&@!%@', $imageType, $helptxt['personalizedBBC_tagImage']);
	// BB Code permissions are varying
	$request = $smcFunc['db_query']('', '
			SELECT name, type
			FROM {db_prefix}personalized_bbc
			ORDER BY name ASC'
		);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$personalized_BBC[] = array('name' => $smcFunc['strtolower']($row['name']), 'type' => (!empty($row['type']) ? (int)$row['type'] : 0));

	$smcFunc['db_free_result']($request);

	foreach ($personalized_BBC as $bbc)
	{
		$key++;
		$permission = $bbc['name'];
		$BBC_Name = !empty($permission) ? $smcFunc['strtolower'](trim('personalized_bbc_' . $permission)) : 'personalized_bbc_' . (string)$key;
		$txt['permissionname_' . $BBC_Name . '_view'] = trim(str_replace('#@#$!', $permission, $txt['permissionname_PersonalizedBBC_perm_view']));
		$txt['permissionhelp_' . $BBC_Name . '_view'] = trim(str_replace('#@#$!', $permission, $txt['permissionhelp_PersonalizedBBC_perm_view']));
		$txt['permissionname_' . $BBC_Name . '_use'] = trim(str_replace('#@#$!', $permission, $txt['permissionname_PersonalizedBBC_perm_use']));
		$txt['permissionhelp_' . $BBC_Name . '_use'] = trim(str_replace('#@#$!', $permission, $txt['permissionhelp_PersonalizedBBC_perm_use']));
	}

	// easiest way to force censorship using a nonsense word ~ triggers PersonalizedBBC_censorship()
	$modSettings['allow_no_censored'] = 0;
	$modSettings['censor_vulgar'] = !empty($modSettings['censor_vulgar']) ? $modSettings['censor_vulgar'] : 'fatellapplumve';
}

function PersonalizedBBC_censorship(&$text)
{
	// Remove any Personalized BBC where the user does not have permission to view it
	$text = PersonalizedBBC_parser($text, 'view');
}

function PersonalizedBBC_preparsecode(&$message, $previewing)
{
	// Remove any Personalized BBC where the user does not have permission to use it
	$message = PersonalizedBBC_parser($message, 'use');
}

function PersonalizedBBC_parser($content, $intent = 'view')
{
	global $personalized_BBC, $topic, $context;
	$personalized_BBC = !empty($personalized_BBC) ? $personalized_BBC : array();

	foreach ($personalized_BBC as $parseBBC)
	{
		if ((isset($_REQUEST['msg']) && !empty($topic) && $intent === 'view') && allowedTo('personalized_bbc_' . $parseBBC['name'] . '_use') && empty($context['PersonalizedBBC_parser']))
		{
			$context['PersonalizedBBC_parser'] = 'init';
			return $content;
		}

		if (!allowedTo('personalized_bbc_' . $parseBBC['name'] . '_' . $intent))
		{
			if ((!empty($parseBBC['type'])) && (int)$parseBBC['type'] == 3)
				$content = preg_replace("~\[" . $parseBBC['name'] . "\](.*?(<br( />)|<br>|\Z))~i", "", $content);
			else
				$content = preg_replace(array("~\[" . $parseBBC['name'] . "\](.*?)\[\/" . $parseBBC['name'] . "\]~i", "~\[" . $parseBBC['name'] . "=(.*?)\](.*?)\[\/" . $parseBBC['name'] . "\]~i"), array('', ''), $content);
		}
	}

	return $content;
}

function PersonalizedBBC_redact($string)
{
	global $personalized_BBC;

	foreach ($personalized_BBC as $parseBBC)
	{
		if (!allowedTo('personalized_bbc_' . $parseBBC['name'] . '_view'))
		{
			if ((!empty($parseBBC['type'])) && (int)$parseBBC['type'] == 3)
				$string = preg_replace("~\[" . $parseBBC['name'] . "\](.*?(<br( />)|<br>|\Z))~i", "", $string);
			else
				$string = preg_replace(array("~\[" . $parseBBC['name'] . "\](.*?)\[\/" . $parseBBC['name'] . "\]~i", "~\[" . $parseBBC['name'] . "=(.*?)\](.*?)\[\/" . $parseBBC['name'] . "\]~i"), array('', ''), $string);
		}
	}

	return $string;
}

function PersonalizedBBC_load_theme()
{
	global $settings, $context;

	$context['html_headers'] .= '
	<script src="' . $settings['default_theme_url'] . '/scripts/personalizedBBC_frames.js?v195" type="text/javascript"></script>
	<script>
		$(document).ready(function(){
			pbbc_containers("iframe");
		});
	</script>';
}

?>