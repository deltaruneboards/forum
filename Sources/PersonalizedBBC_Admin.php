<?php
/*
 * Personalized BBC was developed for SMF forums c/o Chen Zhen @ https://web-develop.ca
 * Copyright 2025 @ web-develop.ca
 * Distributed under the CC BY-ND 4.0 License (http://creativecommons.org/licenses/by-nd/4.0/)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function PersonalizedBBC_Admin()
{
	global $txt, $context, $sourcedir, $smcFunc;

	if (version_compare(phpversion(), '5.3.0', '<'))
		fatal_error(str_replace('#@#$!', phpversion(), $txt['PersonalizedBBC_PHP_ErrorMessage']), false);
	else
	{
		require_once($sourcedir . '/PersonalizedBBC_AdminSettings.php');
		require_once($sourcedir . '/Subs-PersonalizedBBC_Admin.php');
	}

	// Fill $context with name, page and possible input data
	$personalizedBBC_settings = array('name', 'description', 'code', 'image', 'prior', 'after', 'parse', 'trim', 'type', 'block_lvl', 'enable', 'display', 'delete', 'current_name', 'list', 'view_source', 'url_fix', 'url_fix', 'file', 'jQueryEnable');
	$context['current_name'] = (!empty($_REQUEST['name'])) && !is_array($_REQUEST['name']) ? $smcFunc['strtolower'](trim(cleanPersonalizedBBC_String($_REQUEST['name']))) : '';
	$context['current_page'] = !empty($context['current_page']) ? (int)$context['current_page'] : 1;
	$context['current_page'] = (!empty($_REQUEST['current_page']) ? (int)$_REQUEST['current_page'] : $context['current_page']) -1;
	$context['PersonalizedBBC_override'] = !empty($_REQUEST['override']) ? $_REQUEST['override'] : '';

	foreach ($personalizedBBC_settings as $setting_type)
	{
		$_REQUEST[$setting_type] = isset($_REQUEST[$setting_type]) ? $_REQUEST[$setting_type] : array();

		if (is_array($_REQUEST[$setting_type]))
			foreach ($_REQUEST[$setting_type] as $id => $data)
				$context['personalizedBBC'][$id][$setting_type] = $data;
		elseif ($context['current_name'])
			$context['personalizedBBC'][$context['current_name']][$setting_type] = $_REQUEST[$setting_type][0];
	}

	foreach (array('view', 'use') as $intent)
	{
		if (isset($_POST['membergroups_' . $intent]) && is_array($_POST['membergroups_' . $intent]))
		{
			foreach ($_POST['membergroups_' . $intent] as $id => $value)
			{
				if ($value == 1)
					$context['personalizedBBC_membergroups_' . $intent][$id] = 1;
				elseif (isset($value))
					$context['personalizedBBC_membergroups_' . $intent][$id] = 0;
			}
		}
	}

	$subActions = array(
		'personalizedBBC_Settings' => array('SettingsPersonalizedBBC'),
		'personalizedBBC_Entry' => array('EntryPersonalizedBBC'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'personalizedBBC_Settings';
	isAllowedTo('admin_forum');
	$subActions[$_REQUEST['sa']][0]();
}
?>