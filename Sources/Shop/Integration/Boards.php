<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Integration;

use Shop\Shop;
use Shop\Helper\Format;

if (!defined('SMF'))
	die('No direct access...');

class Boards
{
	/**
	 * @var array Will provide the board columns if needed.
	 */
	var $_columns;

	/**
	 * Boards::__construct()
	 *
	 * Build our column array with the board columns
	 */
	function __construct()
	{
		// Add the columns when needed
		$this->_columns = ['Shop_credits_count', 'Shop_credits_topic', 'Shop_credits_post', 'Shop_credits_bonus'];
	}

	public function pre_boardtree(&$boardColumns, &$boardParameters, &$boardJoins, &$boardWhere, &$boardOrder)
	{
		foreach ($this->_columns as $column)
			$boardColumns[] = 'b.' . $column;
	}

	public function boardtree_board($row)
	{
		global $boards;

		if (!empty($row['id_board']))
			foreach ($this->_columns as $setting)
				$boards[$row['id_board']][$setting] = $row[$setting];
	}

	public function edit_board()
	{
		global $context, $modSettings;

		if (isset($context['board']['is_new']) && $context['board']['is_new'] === true) {
			foreach ($this->_columns as $setting)
				$context['board'][$setting] = 0;
			$context['board']['Shop_credits_count'] = 1;
		}

		$context['custom_board_settings']['Shop_credits_count'] = [
			'dt' => '<strong>'. Shop::getText('credits_count'). '</strong><br /><span class="smalltext">'. Shop::getText('credits_count_desc'). '</span>',
			'dd' => '<input type="checkbox" name="Shop_credits_count" class="input_check"'. (!empty($context['board']['Shop_credits_count']) ? ' checked="checked"' : ''). '>',
		];
		$context['custom_board_settings']['Shop_credits_topic'] = [
			'dt' => '<strong>'. Shop::getText('credits_topic'). ':</strong><br /><span class="smalltext">'. Shop::getText('credits_custom_override'). '</span>',
			'dd' => Format::cash('<input type="text" name="Shop_credits_topic" size="5" value="'. (!empty($context['board']['Shop_credits_topic']) ? $context['board']['Shop_credits_topic'] : $modSettings['Shop_credits_topic']). '">'),
		];
		$context['custom_board_settings']['Shop_credits_post'] = [
			'dt' => '<strong>'. Shop::getText('credits_post'). ':</strong><br /><span class="smalltext">'. Shop::getText('credits_custom_override'). '</span>',
			'dd' => Format::cash('<input type="text" name="Shop_credits_post" size="5" value="'. (!empty($context['board']['Shop_credits_topic']) ? $context['board']['Shop_credits_post'] : $modSettings['Shop_credits_post']). '">'),
		];
		$context['custom_board_settings']['Shop_credits_bonus'] = [
			'dt' => '<strong>'. Shop::getText('credits_enable_bonus'). ':</strong><br /><span class="smalltext">'. Shop::getText('credits_enable_bonus_desc'). '</span>',
			'dd' => '<input type="checkbox" name="Shop_credits_bonus" class="input_check"'. (!empty($context['board']['Shop_credits_bonus']) ? ' checked="checked"' : ''). '>',
		];
	}

	public function create_board(&$boardOptions, &$board_columns, &$board_parameters)
	{
		foreach ($this->_columns as $setting)
			$boardOptions[$setting] = 0;
		$boardOptions['Shop_credits_count'] = 1;
	}

	public static function modify_board($id, $boardOptions, &$boardUpdates, &$boardUpdateParameters)
	{
		$boardOptions['Shop_credits_count'] = isset($_POST['Shop_credits_count']);
		$boardOptions['Shop_credits_topic'] = !empty($_POST['Shop_credits_topic']) ? (int) $_POST['Shop_credits_topic'] : 0;
		$boardOptions['Shop_credits_post'] = !empty($_POST['Shop_credits_post']) ? (int) $_POST['Shop_credits_post'] : 0;
		$boardOptions['Shop_credits_bonus'] = isset($_POST['Shop_credits_bonus']);

		if (isset($boardOptions['Shop_credits_count'])) {
			$boardUpdates[] = 'Shop_credits_count = {int:Shop_credits_count}';
			$boardUpdateParameters['Shop_credits_count'] = $boardOptions['Shop_credits_count'] ? 1 : 0;
		}		
		if (isset($boardOptions['Shop_credits_topic'])) {
			$boardUpdates[] = 'Shop_credits_topic = {int:Shop_credits_topic}';
			$boardUpdateParameters['Shop_credits_topic'] = (int) $boardOptions['Shop_credits_topic'];
		}
		if (isset($boardOptions['Shop_credits_post'])) {
			$boardUpdates[] = 'Shop_credits_post = {int:Shop_credits_post}';
			$boardUpdateParameters['Shop_credits_post'] = (int) $boardOptions['Shop_credits_post'];
		}
		if (isset($boardOptions['Shop_credits_bonus'])) {
			$boardUpdates[] = 'Shop_credits_bonus = {int:Shop_credits_bonus}';
			$boardUpdateParameters['Shop_credits_bonus'] = $boardOptions['Shop_credits_bonus'] ? 1 : 0;
		}
	}
}