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

/*  Add Error Report to the database if logic allows  */
function ArcadeReport()
{
	global $txt, $context, $db_prefix, $smcFunc, $arcadeModSettings, $scripturl, $sourcedir, $user_info;
	db_extend('packages');
	$gameid = !empty($_REQUEST['game']) ? (int) $_REQUEST['game'] : 0;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$url = empty($rom) ? 'action=arcade;' : 'action=retro_arch;';

	if (empty($arcadeModSettings['arcadeEnableReport']) || !allowedTo('arcade_report') || $user_info['is_guest'])
		redirectexit($url);

	$auxUser = !empty($arcadeModSettings['arcadePosterid']) ? $arcadeModSettings['arcadePosterid'] : 0;
	$repuser = $repid = !empty($user_info['is_guest']) ? 1 : $user_info['id'];
	$repusername = !empty($user_info['is_guest']) ? $txt['arcade_is_guest'] : $user_info['name'];
	$checkTable = false;
	$checkTable = check_table_existsPDLReport('arcade_pdl2');

	if ($checkTable == false)
		redirectexit($url);

	require_once($sourcedir . '/Subs-Post.php');
	$pdl_array1 = array('download_count', 'download_disable', 'report_id', 'report_reason', 'report_year', 'report_day', 'id_game');
	$reason = !empty($_POST['reason']) ? urldecode(trim($_POST['reason'])) : '';
	$reason = !empty($_POST['reason']) ? preg_replace('/[^\w$\x{0080}-\x{FFFF}(-.:+\-=! ]+/u', '', urldecode(trim($_POST['reason']))) : '';
	preparsecode($reason);
	$reason = censorText($reason);
	$reason = filter_var(trim($reason),FILTER_SANITIZE_STRING);
	$reason = strlen($reason) > 100 ? substr($reason, 0, 99) . '...' : $reason;
	$reason = empty($reason) ? $txt['pdl_report_reason_default'] : $reason;

	/* Set GMT time zone, Check date and then reset back to original time zone */
	$myzone = date("e");
	date_default_timezone_set('GMT');
	$repday = date("z");
	$repyear = date("Y");
	date_default_timezone_set($myzone);

	/* Gather needed mysql db data  */
	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.rom_flag, game.submit_system, rep.download_count, rep.report_id, rep.report_reason, rep.report_day, rep.report_year, rep.download_disable
		FROM {db_prefix}arcade_games AS game
			LEFT JOIN {db_prefix}arcade_pdl2 AS rep ON (rep.pdl_gameid = game.id_game)
		WHERE game.id_game = {int:game}
		LIMIT 1',
		array(
			'game' => $gameid,
			'string_empty' => '',
			'member' => $repuser,
		)
	);

	$check = false;
	while ($gamex = $smcFunc['db_fetch_assoc']($result))
	{
		foreach ($pdl_array1 as $pdl1)
		{
			if (empty($gamex[$pdl1]))
				$gamex[$pdl1] = 0;
		}

		if (empty($gamex['game_name']))
			$gamex['game_name'] = false;

		if ($gamex['report_year'] == $repyear && $gamex['report_day'] == $repday)
			$check = true;

		$data_game['pdl'] = array(
			'name' => $gamex['game_name'],
			'count' => $gamex['download_count'],
			'disable' => $gamex['download_disable'],
			'report' => $gamex['report_id'],
			'report_reason' => $gamex['report_reason'],
			'year' => $gamex['report_year'],
			'day' => $gamex['report_day'],
			'gameid' => $gameid,
			'rom_flag' => !empty($gamex['rom_flag']) ? 1 : 0,
			'submit_system' => !empty($gamex['submit_system']) ? $gamex['submit_system'] : 'none',
		);
	}

	$smcFunc['db_free_result']($result);

	if (!empty($check))
		redirectexit($url);

	if (empty($data_game['pdl']['name']) || empty($reason) || empty($gameid))
		redirectexit($url);

	$gamename = $data_game['pdl']['name'];
	$dl_count = (int)$data_game['pdl']['count'];
	$dl_disable = (int)$data_game['pdl']['disable'];
	$rom = $data_game['pdl']['rom_flag'];
	createpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $reason, $dl_count, $dl_disable, $rom);
	$disabled = !empty($arcadeModSettings['arcadeEnableGameDisable']) ? true : false;
	if (!empty($arcadeModSettings['arcadeEnableReportNotification']))
		pmReportedGame($gameid, $gamename, $repday, $repyear, $repusername, $repid, $reason, $dl_count, $dl_disable, $disabled);
	if ($disabled)
		disableGame($gameid, $rom);

	redirectexit($url);
}

function pmReportedGame($gameid, $gamename, $repday, $repyear, $repusername, $repid, $reason, $dl_count, $dl_disable, $disabled)
{
	global $smcFunc, $sourcedir, $boarddir, $scripturl, $arcadeModSettings, $language, $txt, $mbname, $user_info, $webmaster_email;

	$arcadePosterId = !empty($arcadeModSettings['arcadePosterid']) ? $arcadeModSettings['arcadePosterid'] : $user_info['id'];
	list($groups, $members, $emails, $arcadePosterName, $arcadePosterUserName) = array(array(1), array(), array(), $user_info['name'], $user_info['username']);
	$members[] = array('id' => $arcadePosterId, 'group' => 0, 'email' => '', 'lngfile' => $language);
	require_once($sourcedir . '/Subs-Post.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeNotifications.php');

	$result = $smcFunc['db_query']('', '
		SELECT id_group
		FROM {db_prefix}permissions
		WHERE permission = {string:perm}',
		array(
		'perm' => 'arcade_admin')
	);

	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$groups[] = $row['id_group'];
	}
	$smcFunc['db_free_result']($result);
	$result = $smcFunc['db_query']('', '
		SELECT id_member, id_group, email_address, lngfile, real_name, member_name
		FROM {db_prefix}members
		WHERE id_group IN ({array_int:groups})',
		array(
		'groups' => $groups)
	);

	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if (!empty($arcadePosterId) && $row['id_member'] == $arcadePosterId) {
			$arcadePosterName = $row['real_name'];
			$arcadePosterUserName = $row['member_name'];
			continue;
		}
		elseif ($row['id_member'] == 0)
			continue;

		$members[] = array('id' => $row['id_member'], 'group' => $row['id_group'], 'email' => $row['email_address'], 'lngfile' => $row['lngfile']);
	}
	$smcFunc['db_free_result']($result);

	$members = array_filter($members);
	foreach ($members as $rowmember) {
		// don't bother notifying the person that made the report nor the arcade auxiliary id
		if (empty($rowmember['id']))
			continue;
		elseif ($rowmember['id'] == $arcadePosterId || $rowmember['id'] == $repid)
			continue;
		list($sendId, $emailAddress) = array($rowmember['id'], $rowmember['email']);
		$arcadeSettings = loadMyArcadeSettings($sendId);
		if (empty($arcadeSettings['notify_game_reports']))
			continue;
		if (empty($arcadeSettings['champion_pm']) && empty($arcadeSettings['champion_email']))
			continue;

		$lang = empty($rowmember['lngfile']) || empty($arcadeModSettings['userLanguage']) ? $language : $rowmember['lngfile'];
		loadLanguage('ArcadeEmail', $lang, false, false);
		$url = '[url=' . $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $gameid. ']' . $gamename . '[/url]';
		$user = '[url=' . $scripturl . '?action=profile;u=' . $repid. ']' . $repusername . '[/url]';
		$urlEmail = '<a href="' . $scripturl . '?action=admin;area=arcade;sa=pdl_reports;game=' . $gameid. '">' . $gamename . '</a>';
		$userEmail = '<a href="' . $scripturl . '?action=profile;u=' . $repid. '">' . $repusername . '</a>';
		$enabled = empty($disabled) ? $txt['pdl_dl_enabled'] : $txt['pdl_dl_disabled'];
		$message = str_replace(array('{GAMEURL}', '{USERID}', '{REASON}', '{GAME_STATUS}', '{REGARDS}'), array($url, $user, $reason, $enabled, arcade_html_entity_decode($mbname, 2, 2)), $txt['notification_arcade_reported_game_pm']['body']);
		$subject = str_replace('{GAMENAMESUB}', $gamename, $txt['notification_arcade_reported_game_pm']['subject']);
		$from = array('id' => $arcadePosterId, 'name' => $arcadePosterName, 'username' => $arcadePosterUserName);

		// send PM
		if (!empty($arcadeSettings['champion_pm'])) {
			arcade_sendpm(array('to' => array($sendId), 'bcc' => array()), arcade_html_entity_decode($subject, 2, 2), arcade_html_entity_decode($message, 2, 2), '0', $from, '0');
		}

		// send email
		if(!empty($arcadeSettings['champion_email']) && !empty($emailAddress)) {
			$arcadeModSettings['gamesEmail'] = !empty($arcadeModSettings['gamesEmail']) ? $arcadeModSettings['gamesEmail'] : $webmaster_email;
			$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div></div></body></html>';
			$replacements = array(
				'GAMENAMESUB' => $gamename,
				'GAMEURL' => $urlEmail,
				'SUBJECT' => $subject,
				'MESSAGE' => $htmlMessage,
				'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
				'READLINK' =>  $gamename,
				'REPLYLINK' => $scripturl . '?action=pm;sa=send;u=' . $repid,
				'TOLIST' => $emailAddress,
				'USERID' => $userEmail,
				'REASON' => $reason,
				'GAME_STATUS' => $enabled,
				'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
			);
			$email_template = 'notification_arcade_report_game_email';
			$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
			$emailsSend = arcadeSendmail(array($emailAddress), $emaildata['subject'], $emaildata['body'], $arcadeModSettings['gamesEmail'], false, true, 2, null, true, 'base64');
		}
	}
}

/* Check if the column exists */
function checkFieldPDLReport($tableName,$columnName)
{
	if (check_table_existsPDLReport($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		if (in_array($columnName, $check))
			return true;
	}

	return false;
}

/*  Returns amount of columns in a table  */
function checkTablePDLReport($tableName)
{
	global $smcFunc;

	if (check_table_existsPDLReport($tableName))
	{
		$check = $smcFunc['db_list_columns'] ('{db_prefix}' . $tableName, false, array());
		return !empty($check) ? count($check) : false;
	}
	return false;
}

/*  Check if table exists  */
function check_table_existsPDLReport($table)
{
	global $db_prefix, $smcFunc;

	if ($smcFunc['db_list_tables'](false, $db_prefix . $table))
		return true;

	return false;
}

/*  Update arcade_pdl1 values  */
function createpdlval1($userid, $count, $year, $day, $latest_year, $latest_day, $permission)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl1
		WHERE id_member = {int:userid}',
		array('userid' => $userid,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_pdl1',
		array(
			'id_member' => 'int', 'count' => 'int', 'year' => 'string', 'day' => 'string', 'latest_year' => 'string', 'latest_day' => 'string', 'permission' => 'int',
		),
		array(
			$userid, $count, $year, $day, $latest_year, $latest_day, $permission,
		),
		array('id_member',
		)
	);
}

/*  Update arcade_pdl2 values  */
function createpdlval2($gameid, $gamename, $repday, $repyear, $repuser, $repid, $reason, $dl_count, $dl_disable, $romFlag = 0)
{
	global $smcFunc;
	$game_name = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $gamename);

	$request = $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_pdl2
		WHERE pdl_gameid = {int:pdl_gameid}',
		array('pdl_gameid' => $gameid,
		)
	);

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_pdl2',
		array(
			'pdl_gameid' => 'int', 'game_name' => 'string', 'report_day' => 'string', 'report_year' => 'string', 'user_id' => 'int', 'report_id' => 'int', 'report_reason' => 'string', 'download_count' => 'int', 'download_disable' => 'int'
		),
		array(
			$gameid, $game_name, $repday, $repyear, $repuser, $repid, $reason, $dl_count, $dl_disable
		),
		array('pdl_gameid',
		)
	);
}

/* Disable the game */
function disableGame($gameid, $rom = 0)
{
	if ((int)$gameid < 1)
		return;

	global $db_prefix, $smcFunc;
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET enabled = 0
		WHERE id_game = {int:game}',
		array(
			'game' => $gameid,
		)
	);
}
?>