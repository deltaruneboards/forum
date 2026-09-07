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

/*    This file handles new game notifications for SMF Arcade games 	*/

function arcadeEventNewGame($game, $gameid, $rom = 0)
{
	global $smcFunc, $settings, $arcSettings, $scripturl, $boardurl, $txt, $user_info, $sourcedir, $boarddir, $arcadeModSettings, $language, $webmaster_email, $mbname, $memberContext;

	$arcadeModSettings['gamesEmail'] = !empty($arcadeModSettings['gamesEmail']) ? $arcadeModSettings['gamesEmail'] : $webmaster_email;
	$notifications = array('new_game_notification');
	if (filter_var($arcadeModSettings['gamesEmail'], FILTER_VALIDATE_EMAIL) === false)
		$arcadeModSettings['gamesEmail'] = $txt['arcade_default_email'];

	require_once($sourcedir . '/Subs-Post.php');
	require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	require_once($boarddir . '/ArcadeSources/Subs-ArcadeNotifications.php');
	list($arcadeSettings, $emails, $pms, $pvts, $from, $members, $sendEmailData, $sendPmData) = array(array(), array(), array(), array(), array(), array(), false, false);
	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? mb_substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	loadLanguage('Arcade');
	loadLanguage('ArcadeAdmin');
	loadLanguage('ArcadeEmail');

	// change notifications language to the forum default for bulk notifications
	$lang = $language;
	loadLanguage('ArcadeEmail', $lang, false, false);
	$ranum = rand(1000,9999);
	$gameid = !empty($gameid) ? (int)$gameid : 0;
	if (empty($gameid))
		return 0;

	list($my_message, $board_id, $gamename, $game_width, $game_height, $id, $description, $thumbnail, $style) = array(
		!empty($arcadeModSettings['gamesMessage']) ? $arcadeModSettings['gamesMessage'] : '',
		!empty($arcadeModSettings['gamesBoard']) ? $arcadeModSettings['gamesBoard'] : 0,
		str_replace('\\\\', '\\', $game['name']),
		200,
		200,
		'x' . $gameid.$ranum . 'x',
		'&nbsp;',
		$settings['default_theme_url'] . '/images/arc_icons/popup_play_btn.gif',
		'width: 50px; height: 14px;'
	);

	$action = !empty($game['rom_flag']) ? 'retro_arch' : 'arcade';
	$rom = !empty($game['rom_flag']) ? 1 : $rom;
	$dimension = !empty($game['extra_data']) ? $game['extra_data'] : array();
	$gamefile_name = !empty($game['game_file']) ? $game['game_file'] : '';
	$gamename_name = !empty($game['name']) ? str_replace('\\\\', '\\', $game['name']) : '';
	$internal = !empty($game['internal_name']) ? $game['internal_name'] : '';
	$gamedirectory = !empty($game['game_directory']) ? $game['game_directory'] : '';
	$game_width =  !empty($dimension['width']) ? (int)$dimension['width'] : 400;
	$game_height = !empty($dimension['height']) ? (int)$dimension['height'] : 400;
	$help = !empty($game['help']) ? $txt['arcade_post_help'] . wordwrap($game['help'], 140, "<br />") : '&nbsp;';
	$description = !empty($game['description']) ? $txt['arcade_post_description'] . wordwrap($game['description'], 140, "<br />") : '&nbsp;';
	$directory = (empty($rom) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory']) . (!empty($gamedirectory) ? '/' . $gamedirectory : '');
	$directory = rtrim($directory, '/');
	$game_url = '<a href="' . $scripturl. '?action=' . $action . ';sa=play;game=' . $gameid . ';reload=' . $ranum . ';#playgame" title="' . $gamename_name . '">' . $gamename_name . '</a>';
	$subject = str_replace('{GAMENAMESUB}', $gamename_name, $txt['notification_arcade_new_game_pm']['subject']);
	$gameUrl = empty($rom) ? rtrim($arcadeModSettings['gamesUrl'] . '/' . $gamedirectory, '/') : rtrim($arcadeModSettings['romGamesUrl'] . '/' . $gamedirectory, '/');
	$game_pic = !empty($game['thumbnail']) ? '<img src="' . $gameUrl . '/' . $game['thumbnail'] . '" alt="" />' : '';
	$game_pic = !empty($game['cover_icon']) ? '<img src="' . $gameUrl . '/' . $game['cover_icon'] . '" alt="" />' : $game_pic;

	if (!empty($game['cover_icon']) && arcade_url_exists($gameUrl . '/' . $game['cover_icon']))
	{
		$thumbnail = $gameUrl . '/' . $game['cover_icon'];
		$style = "max-width: 175px;max-height: 330px;min-width: 50px;min-height: 50px;";
	}
	elseif (!empty($game['thumbnail']) && arcade_url_exists($gameUrl . '/' . $game['thumbnail']))
	{
		$thumbnail = $gameUrl . '/' . $game['thumbnail'];
		$style = "max-width: 175px;max-height: 330px;min-width: 50px;min-height: 50px;";
	}
	else
	{
		$thumbnail = $settings['default_theme_url'] . '/images/arc_icons/game.gif';
		$style = "width: 50px;height: 50px;";
	}
	$popup = '<a href="javascript:window.open(\'' . $scripturl . '?action=arcade;sa=play;game=' . $gameid. ';pop=1;\', \'' . $gamename_name . '\', \'width=' . $game_width . ',height=' . $game_height . '\')"><img style="' . $style . '" src="' . $thumbnail . '" alt="' . $txt['pdl_popplay'] . '" title="' . $txt['pdl_popplay'] . '" /></a>';
	$message = str_replace(array('{GAMEURL}', '{GAMEICON}', '{GAMEDESC}', '{ARCADE_SETTINGS_URL}', '{REGARDS}'), array($game_url, $popup, $description, $txt['arcade_email_profile'], $mbname), $txt['notification_arcade_new_game_pm']['body']);
	$memberIds = array();
	// set the sender as the user ID for posting from arcade settings else use the current user ID
	if ((!empty($arcadeModSettings['arcadePosterid'])) && (int)$arcadeModSettings['arcadePosterid'] !== $user_info['id'])
	{
		$id = (int)$arcadeModSettings['arcadePosterid'];
		loadMemberData($id, false, 'normal');
		loadMemberContext($id);
		$from = array('id' => $id, 'name' => $memberContext[$id]['name'], 'username' => $memberContext[$id]['username']);
	}
	$from =	empty($from) ? array('id' => $user_info['id'], 'name' => $user_info['name'], 'username' => $user_info['username']) : $from;

	$request = $smcFunc['db_query']('', '
		SELECT arcmem.id_member, arcmem.new_game, arcmem.champion_email, arcmem.champion_pm, arcmem.download_count, mem.email_address, mem.lngfile,
			mem.additional_groups, mem.id_group, mem.id_post_group
		FROM {db_prefix}arcade_members AS arcmem
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = arcmem.id_member)
		WHERE arcmem.new_game = 1
		ORDER BY arcmem.id_member ASC',
		array(
			'newgame' => 1,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($row['id_member']))
			continue;
		if ($row['id_member'] == $user_info['id'])
			continue;
		if (empty($row['champion_email']) && empty($row['champion_pm']))
			continue;

		$members[] = array(
			'id_member' => $row['id_member'],
			'new_game' => $row['new_game'],
			'champion_email' => $row['champion_email'],
			'champion_pm' => $row['champion_pm'],
			'lngfile' => $row['lngfile'],
			'email_address' => $row['email_address'],
			'download_count' => !empty($row['download_count']) ? $row['download_count'] : 0,
			'id_group' => !empty($row['id_group']) ? (int)$row['id_group'] : 0,
			'id_post_group' => !empty($row['id_post_group']) ? (int)$row['id_post_group'] : 0,
			'additional_groups' => !empty($row['additional_groups']) ? explode(',', $row['additional_groups']) : array(),
		);
		$memberIds[] = $row['id_member'];
	}
	$smcFunc['db_free_result']($request);

	if (!empty($rom)) {
		$romGroups = groupsAllowedTo('arcade_view_retro_arch', null);
		$romGroups = $romGroups['allowed'];
		$romGroups[] = 1;
	}
	foreach ($members as $rowmember)
	{
		list($sendEmailData, $sendPmData, $ok) = array(false, false, false);
		if (!empty($rom)) {
			$checkGroups = !empty($romGroups) && !empty($rowmember['additional_groups']) ? array_intersect($romGroups, $rowmember['additional_groups']) : array();
			if (in_array($rowmember['id_group'], $romGroups) || in_array($rowmember['id_post_group'], $romGroups) || count($checkGroups) > 0) {
				$ok = true;
			}

			if (empty($ok))
				continue;
		}

		if (!empty($arcadeModSettings['gamesNotificationsBulk']))
		{
			if (!empty($rowmember['champion_email']))
				$emails[] = $rowmember['email_address'];

			if (!empty($rowmember['champion_pm']))
				$pvts[] = $rowmember['id_member'];

			continue;
		}
		else
		{
			// change notifications language for the specific destined user else the forum default
			$lang = empty($rowmember['lngfile']) || empty($arcadeModSettings['userLanguage']) ? $language : $rowmember['lngfile'];
			loadLanguage('ArcadeEmail', $lang, false, false);

			// send email ??
			if (!empty($rowmember['champion_email']))
			{
				$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div></div></body></html>';
				$replacements = array(
					'SUBJECT' => $subject,
					'MESSAGE' => $htmlMessage,
					'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
					'READLINK' => $gamename_name,
					'REPLYLINK' => $game_url,
					'TOLIST' => $rowmember['email_address'],
					'GAMENAMESUB' => $gamename_name,
					'GAMEURL' => $game_url,
					'GAMEICON' => '', //$popup
					'GAMEDESC' => $description,
					'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
					'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
				);
				$email_template = 'notification_arcade_new_game_email';
				$emailAddress = $rowmember['email_address'];
				$sendEmailData = true;
			}

			// send the PM ??
			if (!empty($rowmember['champion_pm']))
			{
				$pmData = postArcadeGames(true, $game, $gameid);
				$message = arcade_html_entity_decode($pmData[2], 2, 2);
				$subject = arcade_html_entity_decode($pmData[1], 2, 2);
				$sendId = $rowmember['id_member'];
				$sendPmData = true;
			}
		}

		// send the single PM/Email data after the mysql query is closed
		if ($sendEmailData)
		{
			$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
			$emailsSend = arcadeSendmail(array($emailAddress), $emaildata['subject'], $emaildata['body'], $arcadeModSettings['gamesEmail'], false, true, 2, null, true, 'base64');
		}

		if ($sendPmData)
			arcade_sendpm(array('to' => array($sendId), 'bcc' => array()), $subject, $message, '0', $from, '0');
	}


	if (!empty($arcadeModSettings['gamesNotificationsBulk']))
	{
		// bulk Emails
		if (!empty($emails))
		{
			$htmlMessage = '<html><head><title>' . $mbname . '</title></head><body><div></div></body></html>';
			$replacements = array(
				'SUBJECT' => $subject,
				'MESSAGE' => $htmlMessage,
				'SENDER' => arcade_html_entity_decode($mbname, 2, 2),
				'READLINK' => $gamename_name,
				'REPLYLINK' => $game_url,
				'TOLIST' => $rowmember['email_address'],
				'GAMENAMESUB' => $gamename_name,
				'GAMEURL' => $game_url,
				'GAMEICON' => '', //$popup
				'GAMEDESC' => $description,
				'ARCADE_SETTINGS_URL' => $txt['arcade_email_profile'],
				'REGARDS' => arcade_html_entity_decode($mbname, 2, 2)
			);
			$email_template = 'notification_arcade_new_game_email';
			$emaildata = loadEmailTemplate($email_template, $replacements, $lang, false);
			$emailsSend = arcadeSendmail($emails, arcade_html_entity_decode($emaildata['subject'], 2, 2), arcade_html_entity_decode($emaildata['body'], 2, 2), $arcadeModSettings['gamesEmail'], false, true, 2, null, true, 'base64');
		}

		// bulk PMs
		if (!empty($pvts))
		{
			$pmData = postArcadeGames(true, $game, $gameid);
			$subject = html_entity_decode($pmData[1], ENT_COMPAT|ENT_SUBSTITUTE|ENT_HTML5);
			$message = html_entity_decode($pmData[2], ENT_COMPAT|ENT_SUBSTITUTE|ENT_HTML5);
			arcade_sendpm(array('to' => $pvts, 'bcc' => array()), $subject, $message, false, $from, 0);
		}
	}

	return true;
}

function postArcadeGames($pm, $game, $gameid, $rom = 0)
{
	global $user_info, $arcSettings, $scripturl, $sourcedir, $arcadeModSettings, $modSettings, $sourcedir, $boardurl, $boarddir, $txt, $settings, $smcFunc;

	// SMF 2.1.X behavior will differ
	$version = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$start = '[html]';
	$end = '[/html]';

	require_once($sourcedir . '/Subs-Post.php');
	require_once($boarddir . '/ArcadeSources/Subs-Arcade.php');
	loadLanguage('Arcade');
	$ranum = (RAND(1,1255));
	$gameid = !empty($gameid) ? (int)$gameid : 0;
	if (empty($arcadeModSettings['gamesBoard']) || empty($gameid))
		if (empty($pm))
			return 0;

	list($my_message, $board_id, $gamename, $game_width, $game_height, $id, $description, $thumbnail, $style) = array(
		!empty($arcadeModSettings['gamesMessage']) ? $arcadeModSettings['gamesMessage'] : '',
		!empty($arcadeModSettings['gamesBoard']) ? $arcadeModSettings['gamesBoard'] : 0,
		str_replace('\\\\', '\\', $game['name']),
		200,
		200,
		'x' . $gameid.$ranum . 'x',
		'&nbsp;',
		$settings['default_theme_url'] . '/images/arc_icons/popup_play_btn.gif',
		'width: 50px; height: 14px;'
	);

	$action = !empty($game['rom_flag']) ? 'retro_arch' : 'arcade';
	$rom = !empty($game['rom_flag']) ? 1 : $rom;
	$enablePostCount = !empty($arcadeModSettings['arcadeEnablePostCount']) ? 'always' : 'never';
	$dimension = !empty($game['extra_data']) ? $game['extra_data'] : array();
	$gamefile_name = !empty($game['game_file']) ? $game['game_file'] : '';
	$gamename_name = !empty($game['name']) ? str_replace('\\\\', '\\', $game['name']) : '';
	$internal = !empty($game['internal_name']) ? $game['internal_name'] : '';
	$gamedirectory = !empty($game['game_directory']) ? $game['game_directory'] : '';
	$game_width =  !empty($dimension['width']) ? (int)$dimension['width'] : 400;
	$game_height = !empty($dimension['height']) ? (int)$dimension['height'] : 400;
	$help = !empty($game['help']) ? $txt['arcade_post_help'] . wordwrap($game['help'], 140, "<br />") : '&nbsp;';
	$description = !empty($game['description']) ? $txt['arcade_post_description'] . wordwrap($game['description'], 140, "<br />") : '&nbsp;';
	$directory = (empty($rom) ? $arcadeModSettings['gamesDirectory'] : $arcadeModSettings['romGamesDirectory']) . (!empty($gamedirectory) ? '/' . $gamedirectory : '');
	$directory = rtrim($directory, '/');
	$gameUrl = empty($rom) ? rtrim($arcadeModSettings['gamesUrl'] . '/' . $gamedirectory, '/') : rtrim($arcadeModSettings['romGamesUrl'] . '/' . $gamedirectory, '/');
	$game_pic = !empty($game['thumbnail']) ? '<img src="' . $gameUrl . '/' . $game['thumbnail'] . '" alt="" />' : '';
	$game_pic = !empty($game['cover_icon']) ? '<img src="' . $gameUrl . '/' . $game['cover_icon'] . '" alt="" />' : $game_pic;	
	clearstatcache();

	if (!empty($game['cover_icon']) && arcade_url_exists($gameUrl . '/' . $game['cover_icon']))
	{
		$thumbnail = $gameUrl . '/' . $game['cover_icon'];
		$style = "max-width: 175px;max-height: 330px;min-width: 50px;min-height: 50px;";
	}
	elseif (!empty($game['thumbnail']) && arcade_url_exists($gameUrl . '/' . $game['thumbnail']))
	{
		$thumbnail = $gameUrl . '/' . $game['thumbnail'];
		$style = "max-width: 175px;max-height: 330px;min-width: 50px;min-height: 50px;";
	}
	else
	{
		$thumbnail = $settings['default_theme_url'] . '/images/arc_icons/game.gif';
		$style = "width: 50px;height: 50px;";
	}

	/*
	<a style="cursor: pointer;" title="' . $txt['arcade_popplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $scripturl . '?action=arcade;sa=play;game=' . $gameid . ';pop=1\',' . $game_width . ',' . $game_height . ', 0, false);">Popup</a>
	*/
	if (empty($pm)) {
		$onclick = 'arcadePopWindowEvent(' . $gameid . ',' . $game_width . ',' . $game_height . ',3,0)';
		$popup = $start . '
				<a style="display: none;" id="arcadePostPopup' . $gameid. '" href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $gameid . ';pop=1">GameId:' . $gameid . '</a>
				<div style="display: inline;" onmouseover="arcadeOnmouseOverEvent(this, 0)" onmouseout="arcadeOnmouseOutEvent(this, 0)" title="' . $txt['arcade_popplay'] . '" onclick="' . $onclick . '">
					<img style="' . $style . '" src="' . $thumbnail . '" alt="' . $txt['pdl_popplay'] . '" title="' . $txt['pdl_popplay'] . '" />
				</div>
				<div style="display: block;" title="' . $txt['pdl_popplay'] . '">
					<div style="display: inline;" onmouseover="arcadeOnmouseOverEvent(this, 1)" onmouseout="arcadeOnmouseOutEvent(this, 1)" title="' . $txt['arcade_popplay'] . '" onclick="' . $onclick . '">
						' . $txt['arcade_popplay'] . '
					</div>
				</div><br><br>' . $end;

		$topicTalk = '<div style="padding-bottom: 0.3em;" title="' . $txt['arcadeFullPopup'] . '">[center][b][iurl='.$scripturl.'?action=' . $action . ';sa=play;game='.$gameid.']' . str_replace('%#@$', ' [i]' . $gamename . '[/i]', $txt['arcade_post']) . '[/iurl][/b][/center]</div>';
	}
	else {
		$popup = '[center][iurl=' . $scripturl . '?action=' . $action . ';sa=play;game=' . $gameid . ';pop=1;full=1]
					[img width=100]' . $thumbnail . '[/img]
				[/iurl][br]
				[iurl=' . $scripturl . '?action=' . $action . ';sa=play;game=' . $gameid . ';pop=1]
					' . $txt['arcade_fullplay'] . '
				[/iurl][/center]';

		$topicTalk = '[center][b][iurl=' . $scripturl . '?action=' . $action . ';sa=play;game=' . $gameid . ']' . str_replace('%#@$', ' [i]' . $gamename . '[/i]', $txt['arcade_post']) . '[/iurl][/b][/center][br][br]';
		if (!empty($arcadeModSettings['arcadeEnableIframe'])) {
			$topicTalk .= $popup . '[br][br]';
		}
		$topicTalk .= '[b][sub]' . str_ireplace(array('<br>', '<br />'), '[br]', $help) . '[/sub][br][br][sup]' . str_ireplace(array('<br>', '<br />'), '[br]', $description) . '[/sup][/b]';
	}
	if (empty($arcadeModSettings['arcadeEnableDownload']))
		$arcadeModSettings['arcadeEnableDownload'] = false;

	if (empty($arcadeModSettings['arcadeEnableIframe']))
		$arcadeModSettings['arcadeEnableIframe'] = false;

	if (empty($arcadeModSettings['arcadePosterid']))
		$arcadeModSettings['arcadePosterid'] = $user_info['id'];

	if ($arcadeModSettings['arcadePosterid'] < 1)
		$arcadeModSettings['arcadePosterid'] = 0;

	if (empty($pm)) {
		if ($arcadeModSettings['arcadeEnableDownload'] == true)
			$topicTalk .= $start . '
				<span style="display: block;"></span>
				<center>
					<a style="color: red;text-decoration: none;" href="' . $scripturl . '?action=' . $action . ';sa=download;game=' . $gameid . '">
						<img src="' . $settings['default_theme_url'] . '/images/arc_icons/dl_btn_popup.png" alt="' . $txt['arcade_download_game'] . '" title="' . $txt['arcade_download_game'] . '" />
					</a>
				</center><br><br>' . $end;

		if ($arcadeModSettings['arcadeEnableIframe'] == true) {
			$topicTalk .= '
			[center]' . $popup . '[/center]' . $start . '
			<div style="margin-top: 3px; text-align: center" class="smalltext">' . $txt['pdl_arcade_copyright'] . '</div>' . $end;
		}

		$topicTalk .= $start . '
				<br><br><div style="padding-left: 0.5rem;"><p id="' . $id . '">' . $help . '<br /><br />' . $description . '</p></div><br><br>' . $end;

	}

	$topicTalk .= (empty($pm) ? '<div><div style="padding-left: 0.5rem;"><p>' . $my_message . '</p></div></div>' : '[br][br]' . strip_tags($my_message));
	$topicTalk = preg_replace('/\s+/S', " ", $topicTalk);
	if (!empty($pm))
		return array($arcadeModSettings['arcadePosterid'], $gamename, $topicTalk);

	$msgOptions = array(
		'id' => 0,
		'subject' => html_entity_decode($gamename, ENT_COMPAT|ENT_SUBSTITUTE|ENT_HTML5),
		'body' => html_entity_decode($topicTalk, ENT_COMPAT|ENT_SUBSTITUTE|ENT_HTML5),
		'icon' => "xx",
		'smileys_enabled' => true,
		'attachments' => array(),
	);
	$topicOptions = array(
		'id' => 0,
		'board' => $board_id,
		'poll' => null,
		'lock_mode' => null,
		'sticky_mode' => null,
		'mark_as_read' => true,
		'is_approved' => true,
	);
	$posterOptions = array(
		'id' => $arcadeModSettings['arcadePosterid'],
		'name' => "Arcade",
		'email' => "arcade@here.com",
		'update_post_count' => $enablePostCount,
	);

	createPost($msgOptions, $topicOptions, $posterOptions);

	if (isset($topicOptions['id']))
	{
		$topicid = $topicOptions['id'];
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_games
			SET id_topic = {int:id_topic}
			WHERE id_game = {int:gameid}',
			array(
				'gameid' => $gameid,
				'id_topic' => $topicid,
			)
		);
	}

	return $topicid;
}

?>