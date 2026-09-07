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

// Send off an email.
function arcadeSendmail21($to, $subject, $message, $from = null, $message_id = null, $send_html = false, $priority = 3, $hotmail_fix = null, $is_private = false, $subEncode = '')
{
	global $webmaster_email, $context, $arcadeModSettings, $txt, $modSettings, $scripturl;
	global $smcFunc;

	// base64 disabled pending further testing
	$subEncode = '';
	// Use sendmail if it's set or if no SMTP server is set.
	$use_sendmail = empty($modSettings['mail_type']) || empty($modSettings['smtp_host']);

	// Line breaks need to be \r\n only in windows or for SMTP.
	$line_break = $context['server']['is_windows'] || !$use_sendmail ? "\r\n" : "\n";

	// So far so good.
	$mail_result = true;

	// If the recipient list isn't an array, make it one.
	$to_array = is_array($to) ? $to : array($to);

	// Once upon a time, Hotmail could not interpret non-ASCII mails.
	// In honour of those days, it's still called the 'hotmail fix'.
	if ($hotmail_fix === null) {
		$hotmail_to = array();
		foreach ($to_array as $i => $to_address) {
			if (preg_match('~@(att|comcast|bellsouth)\.[a-zA-Z\.]{2,6}$~i', $to_address) === 1) {
				$hotmail_to[] = $to_address;
				$to_array = array_diff($to_array, array($to_address));
			}
		}

		// Call this function recursively for the hotmail addresses.
		if (!empty($hotmail_to))
			$mail_result = arcadeSendmail21($hotmail_to, $subject, $message, $from, $message_id, $send_html, $priority, true, $subEncode);

		// The remaining addresses no longer need the fix.
		$hotmail_fix = false;

		// No other addresses left? Return instantly.
		if (empty($to_array))
			return $mail_result;
	}

	// Get rid of entities.
	$subject = arcade_html_entity_decode($subject, 2, 2);
	// Make the message use the proper line breaks.
	$message = str_replace(array("\r", "\n"), array('', $line_break), $message);

	// Make sure hotmail mails are sent as HTML so that HTML entities work.
	if ($hotmail_fix && !$send_html) {
		$send_html = true;
		$message = strtr($message, array($line_break => '<br />' . $line_break));
		$message = preg_replace('~(' . preg_quote($scripturl, '~') . '(?:[?/][\w\-_%\.,\?&;=#]+)?)~', '<a href="$1">$1</a>', $message);
	}

	list (, $from_name) = mimespecialchars(addcslashes($from !== null ? $from : $context['forum_name'], '<>()\'\\"'), true, $hotmail_fix, $line_break);
	list (, $subject) = mimespecialchars($subject, true, $hotmail_fix, $line_break);

	// Construct the mail headers...
	$headers = 'From: "' . $from_name . '" <' . (empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from']) . '>' . $line_break;
	$headers .= $from !== null ? 'Reply-To: <' . $from . '>' . $line_break : '';
	$headers .= 'Return-Path: ' . (empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from']) . $line_break;
	$headers .= 'Date: ' . gmdate('D, d M Y H:i:s') . ' -0000' . $line_break;

	if ($message_id !== null && empty($modSettings['mail_no_message_id']))
		$headers .= 'Message-ID: <' . md5($scripturl . microtime()) . '-' . $message_id . strstr(empty($modSettings['mail_from']) ? $webmaster_email : $modSettings['mail_from'], '@') . '>' . $line_break;
	$headers .= 'X-Mailer: SMF' . $line_break;

	// Save the original message...
	$orig_message = $message;

	// The mime boundary separates the different alternative versions.
	$mime_boundary = 'SMF-' . md5($message . time());

	// Using mime, as it allows to send a plain unencoded alternative.
	$encoding = !empty($subEncode) && $use_sendmail ? $subEncode : '7bit';
	$headers .= 'Mime-Version: 1.0' . $line_break;
	$headers .= 'Content-Type: multipart/alternative; boundary="' . $mime_boundary . '"' . $line_break;
	$headers .= 'Content-Transfer-Encoding: ' . $encoding . $line_break;

	// Sending HTML?  Let's plop in some basic stuff, then.
	if ($send_html) {
		$no_html_message = arcade_html_entity_decode(strip_tags(strtr($orig_message, array('</title>' => $line_break))), 2, 2);

		// But, then, dump it and use a plain one for dinosaur clients.
		list(, $plain_message) = mimespecialchars($no_html_message, false, true, $line_break);
		$message = $plain_message . $line_break . '--' . $mime_boundary . $line_break;

		// This is the plain text version.  Even if no one sees it, we need it for spam checkers.
		list($charset, $plain_charset_message, $encoding) = mimespecialchars($no_html_message, false, false, $line_break);
		$message .= 'Content-Type: text/plain; charset=' . $charset . $line_break;
		$message .= 'Content-Transfer-Encoding: ' . $encoding . $line_break . $line_break;
		$message .= $plain_charset_message . $line_break . '--' . $mime_boundary . $line_break;

		// This is the actual HTML message, prim and proper.  If we wanted images, they could be inlined here (with multipart/related, etc.)
		list($charset, $html_message, $encoding) = mimespecialchars($orig_message, false, $hotmail_fix, $line_break);
		$message .= 'Content-Type: text/html; charset=' . $charset . $line_break;
		$message .= 'Content-Transfer-Encoding: ' . ($encoding == '' ? '7bit' : $encoding) . $line_break . $line_break;
		$message .= $html_message . $line_break . '--' . $mime_boundary . '--';
	}
	// Text is good too.
	else {
		// Send a plain message first, for the older web clients.
		list(, $plain_message) = mimespecialchars($orig_message, false, true, $line_break);
		$message = $plain_message . $line_break . '--' . $mime_boundary . $line_break;

		// Now add an encoded message using the forum's character set.
		list ($charset, $encoded_message, $encoding) = mimespecialchars($orig_message, false, false, $line_break);
		$message .= 'Content-Type: text/plain; charset=' . $charset . $line_break;
		$message .= 'Content-Transfer-Encoding: ' . $encoding . $line_break . $line_break;
		$message .= $encoded_message . $line_break . '--' . $mime_boundary . '--';
	}

	// SMTP or sendmail?
	// Includes basic support for ZhenMailer
	if ($use_sendmail) {
		$subject = strtr($subject, array("\r" => '', "\n" => ''));
		$subject = arcade_html_entity_decode($subject, 2, 2);
		if (!empty($modSettings['mail_strip_carriage'])) {
			$message = strtr($message, array("\r" => ''));
			$headers = strtr($headers, array("\r" => ''));
		}

		foreach ($to_array as $to) {
			if (function_exists('ZhenMailerSMF') && !empty($modSettings['ZhenMail_sendmail']) && $modSettings['ZhenMail_sendmail'] > 0) {
				$decoded_message = htmlspecialchars_decode($orig_message, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
				$encoded_message = htmlspecialchars((string)$orig_message, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
				$mail_result = ZhenMailerSMF($to, $subject, $orig_message, $decoded_message, (!empty($modSettings['ZhenMail_sendHTML']) ? true : false), (!empty($context['character_set']) ? $context['character_set'] : 'UTF-8'), $is_private, array(), true);
				if (!$mail_result) {
					$err = arcade_html_entity_decode(sprintf($txt['mail_send_unable'], $to), 2, 2);
					log_error($err, 'debug');
				}
			}
			elseif (!mail(strtr($to, array("\r" => '', "\n" => '')), $subject, $message, $headers)) {
				$err = arcade_html_entity_decode(sprintf($txt['mail_send_unable'], $to), 2, 2);
				log_error($err, 'debug');
				$mail_result = false;
			}

			// Wait, wait, I'm still sending here!
			@set_time_limit(300);
			if (function_exists('apache_reset_timeout'))
				@apache_reset_timeout();
		}
	}
	elseif (function_exists('ZhenMailerSMF') && !empty($modSettings['ZhenMail_sendmail']) && $modSettings['ZhenMail_sendmail'] > 0) {
		$subject = arcade_html_entity_decode($subject, 2, 2);
		$decoded_message = htmlspecialchars_decode($orig_message, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
		$encoded_message = htmlspecialchars((string)$orig_message, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
		$mail_result = $mail_result && ZhenMailerSMF($to, $subject, $orig_message, $decoded_message, (!empty($modSettings['ZhenMail_sendHTML']) ? true : false), (!empty($context['character_set']) ? $context['character_set'] : 'UTF-8'), $is_private, array(), true);
	}
	else {
		$subject = arcade_html_entity_decode($subject, 2, 2);
		$mail_result = $mail_result && smtp_mail($to_array, $subject, $message, $headers);
	}

	// Everything go smoothly?
	return $mail_result;
}

// Send off a personal message.
function arcade_sendpm21($recipients, $subject, $message, $store_outbox = false, $from = null, $pm_head = 0)
{
	global $scripturl, $txt, $user_info, $language, $sourcedir;
	global $arcadeModSettings, $modSettings, $smcFunc;

	// Make sure the PM language file is loaded, we might need something out of it.
	loadLanguage('PersonalMessage');

	// Initialize log array.
	$log = array(
		'failed' => array(),
		'sent' => array()
	);

	if ($from === null)
		$from = array(
			'id' => $user_info['id'],
			'name' => $user_info['name'],
			'username' => $user_info['username']
		);

	// This is the one that will go in their inbox.
	$htmlmessage = htmlspecialchars(str_replace(array("&apos;", "\'"), "'", $message), ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
	preparsecode($htmlmessage);
	$htmlsubject = strtr($smcFunc['htmlspecialchars']($subject), array("\r" => '', "\n" => '', "\t" => ''));
	if ($smcFunc['strlen']($htmlsubject) > 100)
		$htmlsubject = $smcFunc['substr']($htmlsubject, 0, 100);

	// Make sure it is an array
	if (!is_array($recipients))
		$recipients = array($recipients);

	// Get a list of user names and convert them to IDs.
	$usernames = array();
	foreach ($recipients as $rec_type => $rec)
	{
		foreach ($rec as $id => $member)
		{
			if (!is_numeric($recipients[$rec_type][$id]))
			{
				$recipients[$rec_type][$id] = $smcFunc['strtolower'](trim(preg_replace('~[<>&"\'=\\\]~', '', $recipients[$rec_type][$id])));
				$usernames[$recipients[$rec_type][$id]] = 0;
			}
		}
	}
	if (!empty($usernames))
	{
		$request = $smcFunc['db_query']('pm_find_username', '
			SELECT id_member, member_name
			FROM {db_prefix}members
			WHERE ' . ($smcFunc['db_case_sensitive'] ? 'LOWER(member_name)' : 'member_name') . ' IN ({array_string:usernames})',
			array(
				'usernames' => array_keys($usernames),
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			if (isset($usernames[$smcFunc['strtolower']($row['member_name'])]))
				$usernames[$smcFunc['strtolower']($row['member_name'])] = $row['id_member'];
		$smcFunc['db_free_result']($request);

		// Replace the usernames with IDs. Drop usernames that couldn't be found.
		foreach ($recipients as $rec_type => $rec)
			foreach ($rec as $id => $member)
			{
				if (is_numeric($recipients[$rec_type][$id]))
					continue;

				if (!empty($usernames[$member]))
					$recipients[$rec_type][$id] = $usernames[$member];
				else
				{
					$log['failed'][$id] = sprintf($txt['pm_error_user_not_found'], $recipients[$rec_type][$id]);
					unset($recipients[$rec_type][$id]);
				}
			}
	}

	// Make sure there are no duplicate 'to' members.
	$recipients['to'] = array_unique($recipients['to']);

	// Only 'bcc' members that aren't already in 'to'.
	$recipients['bcc'] = array_diff(array_unique($recipients['bcc']), $recipients['to']);

	// Combine 'to' and 'bcc' recipients.
	$all_to = array_merge($recipients['to'], $recipients['bcc']);

	// Check no-one will want it deleted right away!
	$request = $smcFunc['db_query']('', '
		SELECT
			id_member, criteria, is_or
		FROM {db_prefix}pm_rules
		WHERE id_member IN ({array_int:to_members})
			AND delete_pm = {int:delete_pm}',
		array(
			'to_members' => $all_to,
			'delete_pm' => 1,
		)
	);
	$deletes = array();
	// Check whether we have to apply anything...
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$criteria = $smcFunc['json_decode']($row['criteria'], true);
		// Note we don't check the buddy status, cause deletion from buddy = madness!
		$delete = false;
		foreach ($criteria as $criterium)
		{
			if (($criterium['t'] == 'mid' && $criterium['v'] == $from['id']) || ($criterium['t'] == 'gid' && in_array($criterium['v'], $user_info['groups'])) || ($criterium['t'] == 'sub' && strpos($subject, $criterium['v']) !== false) || ($criterium['t'] == 'msg' && strpos($message, $criterium['v']) !== false))
				$delete = true;
			// If we're adding and one criteria don't match then we stop!
			elseif (!$row['is_or'])
			{
				$delete = false;
				break;
			}
		}
		if ($delete)
			$deletes[$row['id_member']] = 1;
	}
	$smcFunc['db_free_result']($request);

	// Load the membergrounp message limits.
	// @todo Consider caching this?
	static $message_limit_cache = array();
	if (!allowedTo('moderate_forum') && empty($message_limit_cache))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_group, max_messages
			FROM {db_prefix}membergroups',
			array(
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$message_limit_cache[$row['id_group']] = $row['max_messages'];
		$smcFunc['db_free_result']($request);
	}

	// Load the groups that are allowed to read PMs.
	require_once($sourcedir . '/Subs-Members.php');
	$pmReadGroups = groupsAllowedTo('pm_read');

	if (empty($modSettings['permission_enable_deny']))
		$pmReadGroups['denied'] = [];

	// Load their alert preferences
	require_once($sourcedir . '/Subs-Notify.php');
	$notifyPrefs = getNotifyPrefs($all_to, array('pm_new', 'pm_reply', 'pm_notify'), true);

	$request = $smcFunc['db_query']('', '
		SELECT
			member_name, real_name, id_member, email_address, lngfile,
			instant_messages,' . (allowedTo('moderate_forum') ? ' 0' : '
			(pm_receive_from = {int:admins_only}' . (empty($modSettings['enable_buddylist']) ? '' : ' OR
			(pm_receive_from = {int:buddies_only} AND FIND_IN_SET({string:from_id}, buddy_list) = 0) OR
			(pm_receive_from = {int:not_on_ignore_list} AND FIND_IN_SET({string:from_id}, pm_ignore_list) != 0)') . ')') . ' AS ignored,
			FIND_IN_SET({string:from_id}, buddy_list) != 0 AS is_buddy, is_activated,
			additional_groups, id_group, id_post_group
		FROM {db_prefix}members
		WHERE id_member IN ({array_int:recipients})
		ORDER BY lngfile
		LIMIT {int:count_recipients}',
		array(
			'not_on_ignore_list' => 1,
			'buddies_only' => 2,
			'admins_only' => 3,
			'recipients' => $all_to,
			'count_recipients' => count($all_to),
			'from_id' => $from['id'],
		)
	);
	$notifications = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Don't do anything for members to be deleted!
		if (isset($deletes[$row['id_member']]))
			continue;

		// Load the preferences for this member (if any)
		$prefs = !empty($notifyPrefs[$row['id_member']]) ? $notifyPrefs[$row['id_member']] : array();
		$prefs = array_merge(array(
			'pm_new' => 0,
			'pm_reply' => 0,
			'pm_notify' => 0,
		), $prefs);

		// We need to know this members groups.
		$groups = explode(',', $row['additional_groups']);
		$groups[] = $row['id_group'];
		$groups[] = $row['id_post_group'];

		$message_limit = -1;
		// For each group see whether they've gone over their limit - assuming they're not an admin.
		if (!in_array(1, $groups))
		{
			foreach ($groups as $id)
			{
				if (isset($message_limit_cache[$id]) && $message_limit != 0 && $message_limit < $message_limit_cache[$id])
					$message_limit = $message_limit_cache[$id];
			}

			if ($message_limit > 0 && $message_limit <= $row['instant_messages'])
			{
				$log['failed'][$row['id_member']] = sprintf($txt['pm_error_data_limit_reached'], $row['real_name']);
				unset($all_to[array_search($row['id_member'], $all_to)]);
				continue;
			}

			// Do they have any of the allowed groups?
			if (count(array_intersect($pmReadGroups['allowed'], $groups)) == 0 || count(array_intersect($pmReadGroups['denied'], $groups)) != 0)
			{
				$log['failed'][$row['id_member']] = sprintf($txt['pm_error_user_cannot_read'], $row['real_name']);
				unset($all_to[array_search($row['id_member'], $all_to)]);
				continue;
			}
		}

		// Note that PostgreSQL can return a lowercase t/f for FIND_IN_SET
		if (!empty($row['ignored']) && $row['ignored'] != 'f' && $row['id_member'] != $from['id'])
		{
			$log['failed'][$row['id_member']] = sprintf($txt['pm_error_ignored_by_user'], $row['real_name']);
			unset($all_to[array_search($row['id_member'], $all_to)]);
			continue;
		}

		// If the receiving account is banned (>=10) or pending deletion (4), refuse to send the PM.
		if ($row['is_activated'] >= 10 || ($row['is_activated'] == 4 && !$user_info['is_admin']))
		{
			$log['failed'][$row['id_member']] = sprintf($txt['pm_error_user_cannot_read'], $row['real_name']);
			unset($all_to[array_search($row['id_member'], $all_to)]);
			continue;
		}

		// Send a notification, if enabled - taking the buddy list into account.
		if (!empty($row['email_address'])
			&& ((empty($pm_head) && $prefs['pm_new'] & 0x02) || (!empty($pm_head) && $prefs['pm_reply'] & 0x02))
			&& ($prefs['pm_notify'] <= 1 || ($prefs['pm_notify'] > 1 && (!empty($modSettings['enable_buddylist']) && $row['is_buddy']))) && $row['is_activated'] == 1)
		{
			$notifications[empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile']][] = $row['email_address'];
		}

		$log['sent'][$row['id_member']] = sprintf(isset($txt['pm_successfully_sent']) ? $txt['pm_successfully_sent'] : '', $row['real_name']);
	}
	$smcFunc['db_free_result']($request);

	// Only 'send' the message if there are any recipients left.
	if (empty($all_to))
		return $log;

	// Insert the message itself and then grab the last insert id.
	$id_pm = $smcFunc['db_insert']('',
		'{db_prefix}personal_messages',
		array(
			'id_pm_head' => 'int', 'id_member_from' => 'int', 'deleted_by_sender' => 'int',
			'from_name' => 'string-255', 'msgtime' => 'int', 'subject' => 'string-255', 'body' => 'string-65534',
		),
		array(
			$pm_head, $from['id'], ($store_outbox ? 0 : 1),
			$from['username'], time(), $htmlsubject, $htmlmessage,
		),
		array('id_pm'),
		1
	);

	// Add the recipients.
	if (!empty($id_pm))
	{
		// If this is new we need to set it part of it's own conversation.
		if (empty($pm_head))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}personal_messages
				SET id_pm_head = {int:id_pm_head}
				WHERE id_pm = {int:id_pm_head}',
				array(
					'id_pm_head' => $id_pm,
				)
			);

		// Some people think manually deleting personal_messages is fun... it's not. We protect against it though :)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pm_recipients
			WHERE id_pm = {int:id_pm}',
			array(
				'id_pm' => $id_pm,
			)
		);

		$insertRows = array();
		$to_list = array();
		foreach ($all_to as $to)
		{
			$insertRows[] = array($id_pm, $to, in_array($to, $recipients['bcc']) ? 1 : 0, isset($deletes[$to]) ? 1 : 0, 1);
			if (!in_array($to, $recipients['bcc']))
				$to_list[] = $to;
		}

		$smcFunc['db_insert']('insert',
			'{db_prefix}pm_recipients',
			array(
				'id_pm' => 'int', 'id_member' => 'int', 'bcc' => 'int', 'deleted' => 'int', 'is_new' => 'int'
			),
			$insertRows,
			array('id_pm', 'id_member')
		);
	}

	// Back to what we were on before!
	loadLanguage('index+PersonalMessage');

	// Add one to their unread and read message counts.
	foreach ($all_to as $k => $id)
		if (isset($deletes[$id]))
			unset($all_to[$k]);
	if (!empty($all_to))
		updateMemberData($all_to, array('instant_messages' => '+', 'unread_messages' => '+', 'new_pm' => 1));

	return $log;
}

?>