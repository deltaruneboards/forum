<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

// SMF 2.1 behaviour
// Highscore email notifications should be set to HTML
$txt['notification_arcade_new_champion_own_subject'] = 'You are no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_own_body'] = '<div>You are no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;"> {champion.name} has beaten your score and is a new champion!</div>
<div style="padding-top: 5px;">To reclaim this title, {play.the.game} and get a score better than {champion.score}.</div>
<div style="padding-top: 15px;">You may opt to disable this notification from your profile settings.</div>
<div style="padding-top: 15px;">In order to save your score you must login.</div>
<div style="padding-top: 25px;">{REGARDS}</div>';

$txt['notification_arcade_new_champion_any_subject'] = '{old_champion.name} is no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_any_body'] = '<div>{old_champion.name} is no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;">{champion.name} has beaten {old_champion.name}\'s score and is a new champion!</div>
<div style="padding-top: 15px;">You may opt to disable this notification from your profile settings.</div>
<div style="padding-top: 15px;">In order to save your score you must login.</div>
<div style="padding-top: 25px;">{REGARDS}</div>';

// Highscore PM notifications should be plain text only
$txt['notification_arcade_new_champion_ownPM_subject'] = 'You are no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_ownPM_body'] = 'You are no longer champion of {GAMENAME},
{champion.name} has beaten your score and is a new champion!
To reclaim this title, {play.the.game} and get a score better than {champion.score}.

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}.


{REGARDS}';

$txt['notification_arcade_new_champion_ownNewPM_subject'] = 'There is a new champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_ownNewPM_body'] = '{champion.name} is the champion of {GAMENAME} with a score of {champion.score},
Can you to beat the high score of {champion.name}?

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}.


{REGARDS}';

$txt['notification_arcade_new_champion_anyPM_subject'] = '{old_champion.name} is no longer champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_anyPM_body'] = '{old_champion.name} is no longer champion of {GAMENAME},
{champion.name} has beaten {old_champion.name}\'s score and is a new champion!

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}.


{REGARDS}';

$txt['notification_arcade_new_champion_anyNewPM_subject'] = 'There is a new champion of {GAMENAMESUB}';
$txt['notification_arcade_new_champion_anyNewPM_body'] = '{champion.name} is the champion of {GAMENAME} with a score of {champion.score},
Can you to beat the high score of {champion.name}?

You may opt to disable this notification from: {ARCADE_SETTINGS_URL}.


{REGARDS}';

// new game email
$txt['notification_arcade_new_game_email_body'] = array(
	'subject' => 'New game: {GAMENAMESUB}',
	'body' => '<div>Try {GAMEURL} from the Arcade!</div>
<div style="padding-top: 5px;">{GAMEICON}</div>
<div style="padding-top: 15px;">{GAMEDESC}</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

$txt['notification_arcade_new_game_email_subject'] = 'New game: {GAMENAMESUB}';

// SMF 2.0 behavior
// Highscore email notifications should be set to HTML
$txt['emails']['notification_arcade_new_champion_own'] = array(
	'subject' => 'You are no longer champion of {GAMENAMESUB}',
	'body' => '<div>You are no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;"> {champion.name} has beaten your score and is a new champion!</div>
<div style="padding-top: 5px;">To reclaim this title, {play.the.game} and get a score better than {champion.score}.</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

$txt['emails']['notification_arcade_new_champion_any'] = array(
	'subject' => '{old_champion.name} is no longer champion of {GAMENAMESUB}',
	'body' => '<div>{old_champion.name} is no longer champion of {GAMENAME},</div>
<div style="padding-top: 5px;">{champion.name} has beaten {old_champion.name}\'s score and is a new champion!</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

// High score PM notifications should be plain text only
$txt['emails']['notification_arcade_new_champion_ownPM'] = array(
	'subject' => 'You are no longer champion of {GAMENAMESUB}',
	'body' => 'You are no longer champion of {GAMENAME},
{champion.name} has beaten your score and is a new champion!
To reclaim this title, {play.the.game} and get a score better than {champion.score}

You can opt to disable this notification by {ARCADE_SETTINGS_URL}.


{REGARDS}');

$txt['emails']['notification_arcade_new_champion_anyPM'] = array(
	'subject' => '{old_champion.name} is no longer champion of {GAMENAMESUB}',
	'body' => '{old_champion.name} is no longer champion of {GAMENAME},
{champion.name} has beaten {old_champion.name}\'s score and is a new champion!

You can opt to disable this notification by {ARCADE_SETTINGS_URL}.


{REGARDS}');

$txt['emails']['notification_arcade_new_champion_anyNew'] = array(
	'subject' => 'There is a new champion of {GAMENAMESUB}',
	'body' => '<div>{champion.name} is the champion of {GAMENAME} with a score of {champion.score},</div>
<div style="padding-top: 5px;">{MBNAME} is challenging you to beat the high score of {champion.name}!</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

$txt['emails']['notification_arcade_new_champion_ownNew'] = array(
	'subject' => 'There is a new champion of {GAMENAMESUB}',
	'body' => '<div>{champion.name} is the champion of {GAMENAME} with a score of {champion.score},</div>
<div style="padding-top: 5px;">{MBNAME} is challenging you to beat the high score of {champion.name}!</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

// Arena notifications
$txt['notification_arcade_arena_invite_subject'] = 'You are invited to join a match';
$txt['notification_arcade_arena_new_round_subject'] = '{MATCHNAME}: New Round begins';
$txt['notification_arcade_arena_match_end_subject'] = '{MATCHNAME}: Completed';

$txt['emails']['notification_arcade_arena_invite'] = array(
	'subject' => 'You are invited to join a match',
	'body' => '<div>You have been invited to join a match named "{MATCHNAME}" on the Arcade Arena.</div>
<div style="padding-top: 5px;>To accept or decline this offer, visit match\'s page in url below:</div>
<div style="padding-top: 15px;>{MATCHURL}</div>
<div style="padding-top: 15px;>{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;>{REGARDS}</div>');

$txt['emails']['notification_arcade_arena_new_round'] = array(
	'subject' => '{MATCHNAME}: New Round begins',
	'body' => '<div>A new round has begun on the match named "{MATCHNAME}</div>".
<div style="padding-top: 5px;>Visit the following URL to play:</div>
<div style="padding-top: 5px;>{MATCHURL}</div>
<div style="padding-top: 15px;>{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;>{REGARDS}</div>');

$txt['emails']['notification_arcade_arena_match_end'] = array(
	'subject' => '{MATCHNAME}: Finished',
	'body' => '<div style="padding-top: 5px;>The match named "{MATCHNAME}" has been completed.</div>
<div style="padding-top: 5px;>Visit the following URL to see results:</div>
<div style="padding-top: 5px;>{MATCHURL}</div>
<div style="padding-top: 15px;>{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;>{REGARDS}</div>');

$txt['notification_arcade_arena_invite_body'] = '<div>You have been invited to join a match named "{MATCHNAME}" on the Arcade Arena.</div>
<div style="padding-top: 5px;>To accept or decline this offer, visit match\'s page in URL below:</div>
<div style="padding-top: 15px;>{MATCHURL}</div>
<div style="padding-top: 15px;>{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;>{REGARDS}</div>';


$txt['notification_arcade_arena_new_round_body'] = '<div>A new round has begun on the match named "{MATCHNAME}</div>".
<div style="padding-top: 5px;>Visit the following URL to play:</div>
<div style="padding-top: 5px;>{MATCHURL}</div>
<div style="padding-top: 15px;>{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;>{REGARDS}</div>';


$txt['notification_arcade_arena_match_end_body'] = '<div style="padding-top: 5px;>The match named "{MATCHNAME}" has been completed.</div>
<div style="padding-top: 5px;>Visit the following URL to see results:</div>
<div style="padding-top: 5px;>{MATCHURL}</div>
<div style="padding-top: 15px;>{ARCADE_SETTINGS_PROFILE}</div>
<div style="padding-top: 25px;>{REGARDS}</div>';

// Arena PM notifications
$txt['notification_arcade_arena_invite_PMbody'] = 'You have been invited to join the match "{MATCHNAME}" on the Arcade Arena.
To accept or decline this offer, visit match\'s page in URL below:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}';

$txt['notification_arcade_arena_new_round_PMbody'] = 'New Round has begun on match "{MATCHNAME}".
Visit following URL to play:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}';

$txt['notification_arcade_arena_match_end_PMbody'] = 'Match "{MATCHNAME}" has been finished.
Visit following URL to see results:
{MATCHURL}

If you want to, You can disable this notification from
{ARCADE_SETTINGS_URL}

{REGARDS}';

// General replacements
$txt['arcade_pm_play_game'] = '[-- Play the game --]';
$txt['arcade_pm_join_match'] = '[-- Play the match --]';
$txt['arcade_pm_profile'] = 'viewing your profile';
$txt['arcade_email_profile'] = 'You may opt to disable this notification from your profile settings.';

// new game email
$txt['emails']['notification_arcade_new_game_email'] = array(
	'subject' => 'New game: {GAMENAMESUB}',
	'body' => '<div>Try {GAMEURL} from the Arcade!</div>
<div style="padding-top: 5px;">{GAMEICON}</div>
<div style="padding-top: 15px;">{GAMEDESC}</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_URL}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

// new game PM
$txt['notification_arcade_new_game_pm'] = array(
	'subject' => 'New game: {GAMENAMESUB}',
	'body' => 'Try {GAMEURL} from the Arcade!
<div style="padding-top: 5px;">{GAMEICON}</div>
<div style="padding-top: 15px;">{GAMEDESC}</div>
<div style="padding-top: 15px;">{ARCADE_SETTINGS_URL}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

// game error reporting PM
$txt['notification_arcade_reported_game_pm'] = array(
	'subject' => 'Game error reported for {GAMENAMESUB}',
	'body' => '{GAMEURL} has been reported with a possible issue from the Arcade.

Reported by: {USERID}
Reason: {REASON}
Status: {GAME_STATUS}

{REGARDS}');

// game error reporting email
$txt['emails']['notification_arcade_report_game_email'] = array(
	'subject' => 'Game error reported for {GAMENAMESUB}',
	'body' => '<div>{GAMEURL} has been reported with a possible issue from the Arcade.</div>
<div style="padding-top: 5px;">Reported by: {USERID}</div>
<div style="padding-top: 15px;">Reason: {REASON}</div>
<div style="padding-top: 15px;">Status: {GAME_STATUS}</div>
<div style="padding-top: 25px;">{REGARDS}</div>');

?>