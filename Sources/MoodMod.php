<?php
/**
 * MoodMod — Mood/Emotions Mod for SMF 2.1
 * @author   TwitchisMental
 * @package  MoodMod
 * @version  1.1.0
 * @license  MIT
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/* Helpers */

/**
 * Convert a raw UTF-8 emoji string to HTML numeric entities for safe DB storage.
 */
function MoodMod_emoji_encode($str)
{
	$str = html_entity_decode((string) $str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$encoded = '';
	$len = mb_strlen($str, 'UTF-8');
	for ($i = 0; $i < $len; $i++)
	{
		$char = mb_substr($str, $i, 1, 'UTF-8');
		$cp   = unpack('N', mb_convert_encoding($char, 'UTF-32BE', 'UTF-8'))[1];
		// Only encode non-ASCII (codepoint > 127) to entities; leave ASCII as-is.
		$encoded .= $cp > 127 ? '&#' . $cp . ';' : $char;
	}
	return mb_substr($encoded, 0, 100, 'UTF-8');
}

/**
 * Decode stored entities back to raw UTF-8 for display in HTML or JS.
 * &#128522; → 😊
 */
function MoodMod_emoji_decode($str)
{
	return html_entity_decode((string) $str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Validate a user-supplied badge background colour.
 * Returns a normalised lowercase #rrggbb string, or '' (= theme default)
 * for empty/invalid input.
 */
function MoodMod_sanitize_color($str)
{
	$str = strtolower(trim((string) $str));
	return preg_match('/^#[0-9a-f]{6}$/', $str) ? $str : '';
}

/**
 * Return true if $member_id is allowed to use moods.
 * Admins are always allowed.  When no groups are configured everyone is allowed.
 *
 * @param  int|null $member_id  Defaults to the current user.
 * @return bool
 */
function MoodMod_canUseMood($member_id = null)
{
	global $modSettings, $user_info, $smcFunc;

	if ($user_info['is_admin'])
		return true;

	$allowed = !empty($modSettings['moodmod_allowed_groups'])
		? array_map('intval', explode(',', $modSettings['moodmod_allowed_groups']))
		: array();

	// Empty list or "0" (all groups) = everyone is allowed.
	if (empty($allowed) || in_array(0, $allowed))
		return true;

	if ($member_id === null)
		$member_id = (int) $user_info['id'];

	if ($member_id === (int) $user_info['id'])
	{
		$groups = array_merge(
			array((int) $user_info['id_group']),
			array_map('intval', $user_info['additional_groups'])
		);
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_group, additional_groups
			FROM {db_prefix}members
			WHERE id_member = {int:mid}',
			array('mid' => $member_id)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!$row)
			return false;

		$groups = array_merge(
			array((int) $row['id_group']),
			!empty($row['additional_groups'])
				? array_map('intval', explode(',', $row['additional_groups']))
				: array()
		);
	}

	return !empty(array_intersect($groups, $allowed));
}

/* Hooks */

/**
 * Hook: integrate_load_theme
 *
 * Loads the stylesheet + deferred JS, and initialises the JS namespace so
 * that integrate_memberContext can push mood data into it incrementally.
 */
function MoodMod_load_theme()
{
	global $modSettings, $scripturl, $smcFunc, $sourcedir;

	// Self-heal: re-register hooks if they were never registered
	// (e.g. hooks were added after the mod was already installed).
	if (strpos((string) (!empty($modSettings['integrate_menu_buttons']) ? $modSettings['integrate_menu_buttons'] : ''), 'MoodMod_menu_buttons') === false)
		add_integration_function('integrate_menu_buttons', 'MoodMod_menu_buttons', true, '$sourcedir/MoodMod.php');

	if (strpos((string) (!empty($modSettings['integrate_profile_popup']) ? $modSettings['integrate_profile_popup'] : ''), 'MoodMod_profile_popup') === false)
		add_integration_function('integrate_profile_popup', 'MoodMod_profile_popup', true, '$sourcedir/MoodMod.php');

	if (empty($modSettings['moodmod_enabled']))
		return;

	loadCSSFile('moodmod.css', array('default_theme' => true), 'smf_moodmod');
	loadJavaScriptFile('moodmod.js', array('default_theme' => true, 'defer' => true), 'smf_moodmod');

	// Initialise the JS namespace early — memberContext calls will populate it.
	addInlineJavaScript('
var MoodMod = {
	showInPosts : ' . (empty($modSettings['moodmod_show_in_posts']) ? 'false' : 'true') . ',
	ajaxUrl     : ' . JavaScriptEscape($scripturl . '?action=moodmod') . ',
	userMoods   : {}
};', false);
}

/**
 * Hook: integrate_actions
 *
 * Registers ?action=moodmod as a lightweight JSON endpoint.
 */
function MoodMod_actions(&$actions)
{
	$actions['moodmod'] = array('MoodMod.php', 'MoodMod_ajaxDispatch');
}

/**
 * Hook: integrate_memberContext
 *
 * Queries mood for the just-loaded member (results cached per request)
 * and injects a JS assignment so moodmod.js can render the badge without
 * an additional round-trip.
 */
function MoodMod_memberContext(&$data, $user_id)
{
	global $smcFunc, $modSettings;
	static $cache = array();

	if (empty($modSettings['moodmod_enabled']))
		return;

	if (!isset($cache[$user_id]))
	{
		$request = $smcFunc['db_query']('', '
			SELECT mo.id_mood, mo.name, mo.emoji, mo.description, m.mood_color
			FROM {db_prefix}members AS m
			LEFT JOIN {db_prefix}moods AS mo
				ON (mo.id_mood = m.mood_id AND mo.active = 1)
			WHERE m.id_member = {int:uid}',
			array('uid' => (int) $user_id)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$cache[$user_id] = ($row && !empty($row['id_mood']))
			? array(
				'id'          => (int) $row['id_mood'],
				'name'        => $row['name'],
				'emoji'       => MoodMod_emoji_decode($row['emoji']),
				'description' => MoodMod_emoji_decode($row['description']),
				'color'       => MoodMod_sanitize_color($row['mood_color']),
			)
			: null;
	}

	$mood = $cache[$user_id];
	$data['mood'] = $mood;

	// Push into JS map so moodmod.js can render badges without extra XHR.
	if (!empty($mood))
		addInlineJavaScript(
			'if(typeof MoodMod!=="undefined")' .
			'MoodMod.userMoods[' . (int) $user_id . ']=' .
			json_encode(array(
				'emoji'       => $mood['emoji'],
				'name'        => $mood['name'],
				'description' => $mood['description'],
				'color'       => $mood['color'],
			), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';',
			true
		);
}

/**
 * Hook: integrate_profile_areas
 *
 * Appends a "Mood" section inside the "Edit Profile" group.
 */
function MoodMod_profile_areas(&$areas)
{
	global $txt, $modSettings;

	if (empty($modSettings['moodmod_enabled']))
		return;

	loadLanguage('MoodMod');

	if (!isset($areas['edit_profile']))
		return;

	$areas['edit_profile']['areas']['moodmod'] = array(
		'label'       => $txt['moodmod_profile_tab'],
		'file'        => 'MoodMod.php',
		'function'    => 'MoodMod_profileSection',
		'icon'        => 'smiley',
		'sc'          => 'post',
		'token'       => 'profile-u%u',
		'permission'  => array(
			'own' => array('profile_extra_own'),
			'any' => array('profile_extra_any'),
		),
		'enabled'     => true,
		'subsections' => array(),
	);
}

/**
 * Hook: integrate_admin_areas
 *
 * Adds the Mood Mod entry under Admin → Configuration.
 */
function MoodMod_admin_areas(&$areas)
{
	global $txt;

	loadLanguage('MoodMod');

	$areas['config']['areas']['moodmod'] = array(
		'label'       => (!empty($txt['moodmod_admin_label'])  ? $txt['moodmod_admin_label']  : 'Mood Mod'),
		'file'        => 'MoodMod.php',
		'function'    => 'MoodMod_adminDispatch',
		'icon'        => 'smiley',
		'permission'  => array('admin_forum'),
		'subsections' => array(
			'settings' => array(!empty($txt['moodmod_tab_settings']) ? $txt['moodmod_tab_settings'] : 'Settings'),
			'moods'    => array(!empty($txt['moodmod_tab_moods'])    ? $txt['moodmod_tab_moods']    : 'Manage Moods'),
		),
	);
}

/**
 * Hook: integrate_profile_popup
 *
 * Adds a "My Mood" entry to the profile_user_links sidebar menu shown on
 * the profile page (the div.profile_user_links list).  SMF builds this list
 * from $profile_items; each entry must name the 'menu' section and 'area'
 * key that was registered via integrate_profile_areas.
 */
function MoodMod_profile_popup(&$profile_items)
{
	global $modSettings, $user_info, $txt;

	if (empty($modSettings['moodmod_enabled']) || $user_info['is_guest'] || !MoodMod_canUseMood())
		return;

	loadLanguage('MoodMod');

	// Insert before the logout entry so the link sits with the other edit_profile items.
	$new_item = array(
		'menu'  => 'edit_profile',
		'area'  => 'moodmod',
		'title' => isset($txt['moodmod_profile_tab']) ? $txt['moodmod_profile_tab'] : 'My Mood',
	);

	// Find the logout entry and splice our item in just before it.
	foreach ($profile_items as $index => $item)
	{
		if ($item['area'] === 'logout')
		{
			array_splice($profile_items, $index, 0, array($new_item));
			return;
		}
	}

	// Fallback: just append.
	$profile_items[] = $new_item;
}

/**
 * Hook: integrate_menu_buttons
 *
 * Adds a "My Mood" link to the user's Profile drop-down in the top menu.
 */
function MoodMod_menu_buttons(&$menu_buttons)
{
	global $modSettings, $user_info, $scripturl, $txt;

	// Only proceed if the mod is enabled, the user is logged in, and they have permission
	if (empty($modSettings['moodmod_enabled']) || $user_info['is_guest'] || !MoodMod_canUseMood())
		return;

	loadLanguage('MoodMod');

	// Verify the profile menu and sub_buttons exist
	if (isset($menu_buttons['profile']['sub_buttons']))
	{
		// The button we want to add
		$new_button = array(
			'title' => isset($txt['moodmod_profile_tab']) ? $txt['moodmod_profile_tab'] : 'My Mood',
			'href'  => $scripturl . '?action=profile;area=moodmod',
			'show'  => true,
			'icon'  => 'smiley', // Uses SMF's built-in smiley icon
		);

		$sub_buttons = $menu_buttons['profile']['sub_buttons'];
		$new_subs = array();

		// Rebuild the array so we can place our link exactly where we want it
		foreach ($sub_buttons as $key => $val)
		{
			$new_subs[$key] = $val;
			
			// Insert it right after the native 'Forum Profile' link
			if ($key === 'forumprofile')
				$new_subs['moodmod'] = $new_button;
		}

		// Fallback: if 'forumprofile' wasn't found, make sure it goes BEFORE 'logout'
		if (!isset($new_subs['moodmod']))
		{
			if (isset($new_subs['logout']))
			{
				$logout = $new_subs['logout'];
				unset($new_subs['logout']);
				$new_subs['moodmod'] = $new_button;
				$new_subs['logout'] = $logout;
			}
			else
			{
				$new_subs['moodmod'] = $new_button;
			}
		}

		// Apply the newly ordered buttons
		$menu_buttons['profile']['sub_buttons'] = $new_subs;
	}
}

/* Ajax Endpoints */

/**
 * Thin JSON endpoint: ?action=moodmod;sa=getmoods&users=1,2,3
 * Returns {"<uid>":{"emoji":"...","name":"..."},...}
 */
function MoodMod_ajaxDispatch()
{
	global $smcFunc, $modSettings, $user_info;

	header('Content-Type: application/json');

	if (empty($modSettings['moodmod_enabled']) || $user_info['is_guest'])
	{
		echo '{}';
		exit;
	}

	$sa = isset($_GET['sa']) ? $_GET['sa'] : '';

	if ($sa === 'getmoods')
	{
		// Limit to 50 IDs per request to prevent bulk scraping / DoS.
		$ids = isset($_GET['users'])
			? array_slice(array_filter(array_map('intval', explode(',', $_GET['users']))), 0, 50)
			: array();

		$result = array();

		if (!empty($ids))
		{
			$request = $smcFunc['db_query']('', '
				SELECT m.id_member, m.mood_color, mo.emoji, mo.name, mo.description
				FROM {db_prefix}members AS m
				INNER JOIN {db_prefix}moods AS mo
					ON (mo.id_mood = m.mood_id AND mo.active = 1)
				WHERE m.id_member IN ({array_int:ids})',
				array('ids' => $ids)
			);

			while ($row = $smcFunc['db_fetch_assoc']($request))
				$result[(int) $row['id_member']] = array(
					'emoji'       => MoodMod_emoji_decode($row['emoji']),
					'name'        => $row['name'],
					'description' => MoodMod_emoji_decode($row['description']),
					'color'       => MoodMod_sanitize_color($row['mood_color']),
				);

			$smcFunc['db_free_result']($request);
		}

		echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		exit;
	}

	// Unknown sub-action — just close.
	echo '{}';
	exit;
}

/* Profile Sections */

/**
 * Handles display + save for the Mood profile tab.
 *
 * @param  array  $profile_vars   (unused — we handle saving ourselves)
 * @param  array  $post_errors    Error accumulator.
 * @param  int    $memID          The profile owner's member ID.
 */
function MoodMod_profileSection($memID)
{
	global $context, $smcFunc, $txt, $scripturl, $modSettings;

	loadLanguage('MoodMod');
	loadTemplate('MoodMod');

	// Permission: can the profile owner actually use moods?
	if (!MoodMod_canUseMood($memID))
	{
		$context['moodmod_denied'] = true;
		$context['sub_template']   = 'moodmod_profile';
		return;
	}

	// Load all active moods.
	$request = $smcFunc['db_query']('', '
		SELECT id_mood, name, emoji, description
		FROM {db_prefix}moods
		WHERE active = 1
		ORDER BY sort_order, name',
		array()
	);

	$moods = array(
		0 => array('id_mood' => 0, 'name' => $txt['moodmod_no_mood'], 'emoji' => '', 'description' => ''),
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['emoji'] = MoodMod_emoji_decode($row['emoji']);
		$moods[(int) $row['id_mood']] = $row;
	}

	$smcFunc['db_free_result']($request);

	// Current mood + badge colour for this member.
	$request = $smcFunc['db_query']('', '
		SELECT mood_id, mood_color
		FROM {db_prefix}members
		WHERE id_member = {int:mid}',
		array('mid' => $memID)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$current = isset($row['mood_id']) ? (int) $row['mood_id'] : 0;
	$color   = isset($row['mood_color']) ? MoodMod_sanitize_color($row['mood_color']) : '';

	// SMF's profile system handles checkSession + validateToken for us
	// via 'sc' => 'post' and 'token' => 'profile-u%u' in the area definition.
	if (isset($_POST['moodmod_save']))
	{
		$new_id = isset($_POST['mood_id']) ? (int) $_POST['mood_id'] : 0;

		if ($new_id !== 0 && !isset($moods[$new_id]))
			$new_id = 0;

		$new_color = isset($_POST['mood_color']) ? MoodMod_sanitize_color($_POST['mood_color']) : '';

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET mood_id = {int:mood_id}, mood_color = {string:mood_color}
			WHERE id_member = {int:mid}',
			array('mood_id' => $new_id, 'mood_color' => $new_color, 'mid' => $memID)
		);

		$current = $new_id;
		$color   = $new_color;
		$context['moodmod_saved'] = true;
	}

	// SMF has already called createToken('profile-u<memID>') for us.
	$token_name = 'profile-u' . $memID;
	$context['token_var'] = $context[$token_name . '_token_var'];
	$context['token']     = $context[$token_name . '_token'];

	$context['moodmod_moods']   = $moods;
	$context['moodmod_current'] = $current;
	$context['moodmod_color']   = $color;
	$context['moodmod_action']  = $scripturl . '?action=profile;u=' . $memID . ';area=moodmod';
	$context['sub_template']    = 'moodmod_profile';
}

/* Admin Sections */

function MoodMod_adminDispatch()
{
	global $context, $txt, $scripturl;

	loadLanguage('MoodMod');
	loadTemplate('MoodMod');

	$context['page_title'] = $txt['moodmod_admin_label'];

	$sa = isset($_GET['sa']) ? $_GET['sa'] : 'settings';

	// Build the tab_data so GenericMenu.template.php can render the sub-navigation.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title'       => $txt['moodmod_admin_label'],
		'description' => '',
		'tabs'        => array(
			'settings' => array(
				'description' => $txt['moodmod_enabled_desc'],
			),
			'moods' => array(
				'description' => $txt['moodmod_no_moods'],
			),
		),
	);

	switch ($sa)
	{
		case 'moods':      MoodMod_adminMoods();    break;
		case 'editmood':   MoodMod_adminEditMood();  break;
		case 'deletemood': MoodMod_adminDeleteMood(); break;
		default:           MoodMod_adminSettings();  break;
	}
}

/* Admin - Settings */

function MoodMod_adminSettings()
{
	global $context, $smcFunc, $txt, $modSettings, $scripturl;

	isAllowedTo('admin_forum');

	// Load all member groups for the multi-select.
	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE id_group > 0
		ORDER BY group_name',
		array()
	);

	$groups = array(0 => $txt['moodmod_all_groups']);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$groups[(int) $row['id_group']] = $row['group_name'];
	$smcFunc['db_free_result']($request);

	if (isset($_POST['save_moodmod_settings']))
	{
		checkSession('post');
		validateToken('admin-moodmod-settings');

		$sel_groups = isset($_POST['moodmod_allowed_groups']) && is_array($_POST['moodmod_allowed_groups'])
			? array_map('intval', $_POST['moodmod_allowed_groups'])
			: array(0);

		updateSettings(array(
			'moodmod_enabled'        => !empty($_POST['moodmod_enabled'])       ? 1 : 0,
			'moodmod_show_in_posts'  => !empty($_POST['moodmod_show_in_posts']) ? 1 : 0,
			'moodmod_allowed_groups' => implode(',', $sel_groups),
		));

		redirectexit('action=admin;area=moodmod;sa=settings;saved=1');
	}

	createToken('admin-moodmod-settings');
	$context['token_var'] = $context['admin-moodmod-settings_token_var'];
	$context['token']     = $context['admin-moodmod-settings_token'];

	$context['moodmod_enabled']        = !empty($modSettings['moodmod_enabled']);
	$context['moodmod_show_in_posts']  = !empty($modSettings['moodmod_show_in_posts']);
	$context['moodmod_allowed_groups'] = !empty($modSettings['moodmod_allowed_groups'])
		? array_map('intval', explode(',', $modSettings['moodmod_allowed_groups']))
		: array(0);
	$context['moodmod_all_groups']     = $groups;
	$context['moodmod_saved']          = isset($_GET['saved']);
	$context['sub_template']           = 'moodmod_admin_settings';
	$context['sub_action']             = 'settings';
}

/* Admin - Manage Moods */

function MoodMod_adminMoods()
{
	global $context, $smcFunc, $txt;

	isAllowedTo('admin_forum');

	$request = $smcFunc['db_query']('', '
		SELECT id_mood, name, emoji, description, sort_order, active
		FROM {db_prefix}moods
		ORDER BY sort_order, name',
		array()
	);

	$moods = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['emoji'] = MoodMod_emoji_decode($row['emoji']);
		$moods[] = $row;
	}
	$smcFunc['db_free_result']($request);

	$context['moodmod_moods'] = $moods;
	$context['sub_template']  = 'moodmod_admin_moods';
	$context['sub_action']    = 'moods';

	// Token for delete links (GET-based CSRF protection).
	createToken('admin-moodmod-delete', 'get');
	$context['moodmod_delete_token_var'] = $context['admin-moodmod-delete_token_var'];
	$context['moodmod_delete_token']     = $context['admin-moodmod-delete_token'];
}

/* Admin - Add/Edit Mood */

function MoodMod_adminEditMood()
{
	global $context, $smcFunc, $txt;

	isAllowedTo('admin_forum');

	$mood_id = isset($_GET['mood']) ? (int) $_GET['mood'] : 0;

	if (isset($_POST['save_mood']))
	{
		checkSession('post');
		validateToken('admin-moodmod-editmood');

		$name       = htmlspecialchars(trim($_POST['mood_name']),        ENT_QUOTES, 'UTF-8');
		$emoji      = MoodMod_emoji_encode(trim($_POST['mood_emoji']));
		$desc       = htmlspecialchars(trim($_POST['mood_description']), ENT_QUOTES, 'UTF-8');
		$sort       = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
		$active     = !empty($_POST['active']) ? 1 : 0;

		if ($name === '')
		{
			$context['moodmod_error'] = $txt['moodmod_error_name'];
		}
		else
		{
			if ($mood_id > 0)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}moods
					SET name        = {string:name},
					    emoji       = {string:emoji},
					    description = {string:desc},
					    sort_order  = {int:sort},
					    active      = {int:active}
					WHERE id_mood = {int:mid}',
					array('name' => $name, 'emoji' => $emoji, 'desc' => $desc,
					      'sort' => $sort, 'active' => $active, 'mid' => $mood_id)
				);
			}
			else
			{
				$smcFunc['db_insert']('insert', '{db_prefix}moods',
					array('name' => 'string', 'emoji' => 'string', 'description' => 'string',
					      'sort_order' => 'int', 'active' => 'int'),
					array($name, $emoji, $desc, $sort, $active),
					array('id_mood')
				);
			}

			redirectexit('action=admin;area=moodmod;sa=moods');
		}
	}

	// Load existing mood or blank defaults.
	$mood = array('id_mood' => 0, 'name' => '', 'emoji' => '', 'description' => '', 'sort_order' => 0, 'active' => 1);

	if ($mood_id > 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_mood, name, emoji, description, sort_order, active
			FROM {db_prefix}moods
			WHERE id_mood = {int:mid}',
			array('mid' => $mood_id)
		);

		if ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$row['emoji'] = MoodMod_emoji_decode($row['emoji']);
			$mood = $row;
		}
		$smcFunc['db_free_result']($request);
	}

	createToken('admin-moodmod-editmood');
	$context['token_var'] = $context['admin-moodmod-editmood_token_var'];
	$context['token']     = $context['admin-moodmod-editmood_token'];

	$context['moodmod_mood'] = $mood;
	$context['sub_template'] = 'moodmod_admin_edit_mood';
	$context['sub_action']   = 'moods';
	$context['page_title']   = $mood_id > 0 ? $txt['moodmod_edit_mood'] : $txt['moodmod_add_mood'];
}

/* Admin - Delete Mood */

function MoodMod_adminDeleteMood()
{
	global $smcFunc;

	isAllowedTo('admin_forum');
	checkSession('get');
	validateToken('admin-moodmod-delete', 'get');

	$mood_id = isset($_GET['mood']) ? (int) $_GET['mood'] : 0;

	if ($mood_id > 0)
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}moods
			WHERE id_mood = {int:mid}',
			array('mid' => $mood_id)
		);

		// Reset members who had this mood selected.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET mood_id = 0
			WHERE mood_id = {int:mid}',
			array('mid' => $mood_id)
		);
	}

	redirectexit('action=admin;area=moodmod;sa=moods');
}