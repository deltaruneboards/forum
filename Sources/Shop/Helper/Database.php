<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Helper;

if (!defined('SMF'))
	die('No direct access...');

class Database
{
	// Regular
	public static $items = ['s.itemid', 's.name', 's.image', 's.description', 's.price', 's.stock', 's.module', 's.info1', 's.info2', 's.info3', 's.info4', 's.input_needed', 's.can_use_item', 's.delete_after_use', 's.catid', 's.status', 's.itemlimit'];
	public static $categories = ['sc.catid', 'sc.name', 'sc.image', 'sc.description'];
	public static $modules = ['sm.id', 'sm.name', 'sm.description', 'sm.price', 'sm.author', 'sm.email', 'sm.require_input', 'sm.can_use_item', 'sm.editable_input', 'sm.web', 'sm.file'];
	public static $inventory = ['si.id', 'si.userid', 'si.itemid', 'si.trading', 'si.tradecost', 'si.date', 'si.tradedate', 'si.fav'];
	public static $profile_inventory = ['si.userid', 'si.itemid', 'si.trading', 'si.date', 's.name', 's.image', 's.description', 's.status'];

	// Logs
	public static $log_buy = ['lb.id', 'lb.itemid', 'lb.invid', 'lb.userid', 'lb.sellerid', 'lb.amount', 'lb.fee', 'lb.date', 's.name', 's.image', 's.description', 's.status', 's.catid'];
	public static $log_gift = ['lg.id', 'lg.userid', 'lg.receiver', 'lg.amount', 'lg.itemid', 'lg.invid', 'lg.is_admin', 'lg.date'];
	public static $log_bank = ['lb.id', 'lb.userid', 'lb.amount', 'lb.fee', 'lb.action', 'lb.type', 'lb.date'];
	public static $log_games = ['lg.id', 'lg.userid', 'lg.amount', 'lg.game', 'lg.date'];

	public static function Save($config_vars, $return_config, $sa, $area = 'shopsettings')
	{
		global $context, $scripturl;

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $scripturl . '?action=admin;area='. $area. ';sa='. $sa. ';save';

		// Saving?
		if (isset($_GET['save'])) {
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area='. $area. ';sa='. $sa. '');
		}
		prepareDBSettingContext($config_vars);
	}

	public static function sanitize($string)
	{
		global $smcFunc;

		return $smcFunc['htmlspecialchars']($string, ENT_QUOTES);
	}

	public static function strtolower($value)
	{
		global $smcFunc;

		return $smcFunc['strtolower']($value);
	}

	public static function strlen($string)
	{
		global $smcFunc;

		return $smcFunc['strlen']($string);
	}

	public static function json_encode($add = [])
	{
		global $smcFunc;

		return $smcFunc['json_encode']($add);
	}

	public static function json_decode($add = [], $param = false)
	{
		global $smcFunc;

		return $smcFunc['json_decode']($add, $param);
	}

	public static function Count($table, $columns, $additional_query = '', $additional_columns = '', $more_values = [])
	{
		global $smcFunc;

		$columns = implode(', ', $columns);
		$data = array_merge(
			[
				'table' => $table,
			],
			$more_values
		);
		$request = $smcFunc['db_query']('','
			SELECT ' . $columns . '
			FROM {db_prefix}{raw:table} ' .
			$additional_columns. ' 
			'. $additional_query,
			$data
		);
		$rows = $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);

		return $rows;
	}

	public static function Get($start, $items_per_page, $sort, $table, $columns, $additional_query = '', $single = false, $additional_columns = '', $more_values = [], $attachments = [])
	{
		global $smcFunc;

		$columns = implode(', ', $columns);
		$data = array_merge(
			[
				'table' => $table,
				'start' => $start,
				'maxindex' => $items_per_page,
				'sort' => $sort,
			],
			$more_values
		);
		$result = $smcFunc['db_query']('', '
			SELECT ' . $columns . '
			FROM {db_prefix}{raw:table} ' .
			$additional_columns. ' 
			'. $additional_query . (empty($single) ? '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:maxindex}' : ''),
			$data
		);

		// Single?
		if (empty($single))
		{
			$items = [];
			while ($row = $smcFunc['db_fetch_assoc']($result))
				$items[] = $row;
		}
		else
			$items = $smcFunc['db_fetch_assoc']($result);

		$smcFunc['db_free_result']($result);

		return $items;
	}

	public static function Nested($table, $sort, $column_main, $column_sec, $query_member, $additional_query = '', $additional_columns = '', $more_values = [], $attachments = [], $attach_main = false)
	{
		global $smcFunc;

		$columns = array_merge(array_merge($column_main, $column_sec), $attachments);
		$columns = implode(', ', $columns);
		$data = array_merge(
			[
				'table' => $table,
				'sort' => $sort,
			],
			$more_values
		);
		$result = $smcFunc['db_query']('', '
			SELECT ' . $columns . '
			FROM {db_prefix}{raw:table} ' .
			$additional_columns. ' 
			'. $additional_query . '
			ORDER by {raw:sort}',
			$data
		);

		$items = [];
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$tmp_main = [];
			$tmp_sec  = [];

			// Split them
			foreach($row as $col => $value)
			{
				if (in_array(strstr($column_main[0], '.', true).'.'.$col, $column_main))
					$tmp_main[$col] = $value;
				elseif (in_array(strstr($column_sec[0], '.', true).'.'.$col, $column_sec))
					$tmp_sec[$col] = $value;
				else
					$tmp_main[$col] = $value;
			}

			// Just loop once on each group/category
			if (!isset($items[$row[substr(strrchr($column_main[0], '.'), 1)]]))
			{
				$items[$row[substr(strrchr($column_main[0], '.'), 1)]] = $tmp_main;

				// Attachments?
				if (!empty($attachments) && !empty($attach_main))
					$items[$row[substr(strrchr($column_main[0], '.'), 1)]]['avatar'] = self::Attachments($row);
			}
			$items[$row[substr(strrchr($column_main[0], '.'), 1)]][$query_member][$row[substr(strrchr($column_sec[0], '.'), 1)]] = $tmp_sec;

			// Attachments?
			if (!empty($attachments) && empty($attach_main))
				$items[$row[substr(strrchr($column_main[0], '.'), 1)]][$query_member][$row[substr(strrchr($column_sec[0], '.'), 1)]]['avatar'] = self::Attachments($row);
				
		}
		$smcFunc['db_free_result']($result);

		return $items;
	}

	public static function Attachments($row)
	{
		global $modSettings, $scripturl;

		// Build the array for avatar
		$set_attachments = [
			'name' => $row['avatar'],
			'image' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img class="avatar" src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" />' : '') : ((stristr($row['avatar'], 'http://') || stristr($row['avatar'], 'https://')) ? '<img class="avatar" src="' . $row['avatar'] . '" alt="" />' : '<img class="avatar" src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" />'),
			'href' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : ((stristr($row['avatar'], 'http://') || stristr($row['avatar'], 'https://')) ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
			'url' => $row['avatar'] == '' ? '' : ((stristr($row['avatar'], 'http://') || stristr($row['avatar'], 'https://')) ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])
		];

		return $set_attachments;
	}

	public static function Find($table, $column, $search = '', $additional_query = '')
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('','
			SELECT ' . $column . '
			FROM {db_prefix}{raw:table}'.(!empty($search) ? ('
			WHERE ('. $column . (is_array($search) ? ' IN ({array_int:array_search})' : ('  = {int:search}')) . ') '.$additional_query) : '').'
			LIMIT 1',
			[
				'table' => $table,
				'array_search' => $search,
				'search' => $search,
			]
		);
		$result = $smcFunc['db_num_rows']($request);
		$smcFunc['db_free_result']($request);

		return $result;
	}

	public static function Delete($table, $column, $search, $additional_query = '')
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}{raw:table}
			WHERE '. $column . (is_array($search) ? ' IN ({array_int:array_search})' : '  = {int:search}') . $additional_query,
			[
				'table' => $table,
				'array_search' => $search,
				'search' => $search,
			]
		);
	}

	public static function Truncate($table)
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			TRUNCATE TABLE {db_prefix}{raw:table}',
			[
				'table' => $table,
			]
		);
	}

	public static function Insert($table, $columns, $types, $indexes = [], $method = 'ignore')
	{
		global $smcFunc;

		$smcFunc['db_insert']($method,
			'{db_prefix}'.$table,
			$types,
			$columns,
			$indexes
		);
	}

	public static function Update($table, $columns, $types, $query)
	{
		global $smcFunc;

		$smcFunc['db_query']('','
			UPDATE {db_prefix}'.$table .  '
			SET
			'.rtrim($types, ', ') . '
			'.$query,
			$columns
		);
	}

	public static function list_columns($table,  $details = true)
	{
		global $smcFunc;

		db_extend('packages');
		return $smcFunc['db_list_columns']('{db_prefix}' . $table, $details);
	}
}