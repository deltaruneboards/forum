<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Helper;

use Shop\Shop;

if (!defined('SMF'))
	die('No direct access...');

class Notify
{
	/**
	 * @var array Who sends the PM.
	 */
	var $_from = [];

	/**
	 * @var array Who receives the PM.
	 */
	var $_to = [];

	/**
	 * @var array Details provided to the Alert to use on the background task.
	 */
	var $_details = [];

	/**
	 * @var array Type of values.
	 */
	var $_types = [];

	/**
	 * @var array Columns for the background task.
	 */
	var $_columns = [];

	/**
	 * Notify::__construct()
	 *
	 * Set the apropiate types for the background task
	 */
	function __construct()
	{
		$this->_types = [
			'task_file' => 'string',
			'task_class' => 'string',
			'task_data' => 'string',
			'claimed_time' => 'int'
		];

		$this->_columns = [
			'$sourcedir/Shop/Tasks/Alerts.php',
			'Alerts'
		];
	}

	public function pm($user, $subject, $body)
	{
		global $sourcedir;

		// Required file
		require_once($sourcedir . '/Subs-Post.php');

		// Who is sending the message
		$this->_from = [
			'id' => 0,
			'name' => Shop::getText('notification_sold_from'),
			'username' => Shop::getText('notification_sold_from'),
		];

		// Who receives the message
		$this->_to = [
			'to' => is_array($user) ? $user : [$user],
			'bcc' => [],
		];

		// Send the PM
		sendpm($this->_to, $subject, $body, false, $this->_from);
	}

	public function alert($user, $action, $content, $extra_items = [], $sender = [])
	{
		global $user_info, $scripturl;

		// Add forum link
		$extra_items['item_href'] = $scripturl . '?action=shop';

		// Pointing somewhere else?
		if (!empty($extra_items['shop_href']))
			$extra_items['item_href'] .= $extra_items['shop_href'];

		// Insert the required details first
		$this->_details = [
			'sender_id' => !empty($sender) ? $sender['id'] : $user_info['id'],
			'sender_name' => !empty($sender) ? $sender['name'] : $user_info['name'],
			'receivers' => $user,
			'time' => time(),
			'action' => $action,
			'content_id' => $content,
			'extra_items' => $extra_items
		];

		// Include these details in the values
		$this->_columns = array_merge($this->_columns, [Database::json_encode($this->_details), 0]);
		
		// Just adding the background task, don't mind me
		Database::Insert('background_tasks', $this->_columns, $this->_types, ['id_task']);

		// If they called this object multitple times for some reason...
		unset($this->_columns[2]);
		unset($this->_columns[3]);
	}
}