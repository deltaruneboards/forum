<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

class Alerts extends SMF_BackgroundTask
{
	/**
	 * This executes the task - loads up the information and inserts alerts as needed.
	 * @return bool Always returns true
	 */
	public function execute()
	{
		global $sourcedir, $smcFunc;

		// Find the author of this alert
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE id_member = {int:mem}',
			[
				'mem' => $this->_details['sender_id'],
			]
		);
		$author = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// Just rule out a self-alert or a non-existent user...
		if (!is_array($this->_details['receivers']) && (empty($author) || $author['id_member'] == $this->_details['receivers']) && (empty($this->_details['extra_items']['allow_self']) || !isset($this->_details['extra_items']['allow_self'])))
			return true;

		// Find the users that are receiving the alert then
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:mem})',
			[
				'mem' => is_array($this->_details['receivers']) ? $this->_details['receivers'] : [$this->_details['receivers']],
			]
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$members[] = $row['id_member'];
		$smcFunc['db_free_result']($request);

		// User is on that group, let's remove it
		if (in_array($author['id_member'], $members) && (empty($this->_details['extra_items']['allow_self']) || !isset($this->_details['extra_items']['allow_self'])))
			$members = array_diff($members, [$author['id_member']]);

		// We had any luck?
		if (empty($members))
			return true;

		// Who wants those alerts?
		$alert_bits = [
			'alert' => self::RECEIVE_NOTIFY_ALERT,
			'email' => self::RECEIVE_NOTIFY_EMAIL,
		];
		$notifies = [];

		// Check preferences?
		if (empty($this->_details['extra_items']['ignore_prefs']) || !isset($this->_details['extra_items']['ignore_prefs']))
		{
			require_once($sourcedir . '/Subs-Notify.php');
			$prefs = getNotifyPrefs($members, 'shop_user'.$this->_details['action'], true);

			foreach ($prefs as $member => $pref_option)
				foreach ($alert_bits as $type => $bitvalue)
					if ($pref_option['shop_user'.$this->_details['action']] & $bitvalue)
						$notifies[$type][] = $member;
		}
		// Just fire up these notifications
		else
			foreach ($members as $member)
				$notifies['alert'][] = $member;

		// Deploy alerts
		if (!empty($notifies['alert']))
		{
			$insert_rows = [];
			foreach ($notifies['alert'] as $member)
			{
				$insert_rows[] = [
					'alert_time' => $this->_details['time'],
					'id_member' => $member,
					'id_member_started' => $this->_details['sender_id'],
					'member_name' => $this->_details['sender_name'],
					'content_type' => 'shop',
					'content_id' => $this->_details['content_id'],
					'content_action' => $this->_details['action'],
					'is_read' => 0,
					'extra' => $smcFunc['json_encode']($this->_details['extra_items']),
				];
			}
			// Insert
			$smcFunc['db_insert']('ignore',
				'{db_prefix}user_alerts',
				[
					'alert_time' => 'int',
					'id_member' => 'int',
					'id_member_started' => 'int',
					'member_name' => 'string',
					'content_type' => 'string',
					'content_id' => 'int',
					'content_action' => 'string',
					'is_read' => 'int',
					'extra' => 'string'
				],
				$insert_rows,
				['id_alert']
			);

			// And update the count of alerts for those people.
			updateMemberData($notifies['alert'], ['alerts' => '+']);
		}

		return true;
	}
}