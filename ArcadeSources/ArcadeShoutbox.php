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

/*
	!!!
*/

function ArcadeShouts()
{
	global $smcFunc, $scripturl, $settings, $txt, $sourcedir, $arcadeModSettings, $context;

	// Do we have permission?
	isAllowedTo('arcade_view');

	// Fatal error if Arcade is disabled
	if (empty($arcadeModSettings['arcadeEnabled'])) {
		arcadeClearSession();
		fatal_lang_error('arcade_disabled', false);
	}

	$sessionCheck = isset($_REQUEST['arcade_shout_session']) ? $_REQUEST['arcade_shout_session'] : '';
	$_SESSION['arcade_shout_session'] = !empty($_SESSION['arcade_shout_session']) ? $_SESSION['arcade_shout_session'] : '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);

	/*if (empty($_SESSION['arcade_shout_session']) || $_SESSION['arcade_shout_session'] != $sessionCheck)
		exit('<div class="arcadeShout" style="font-size: 18px;">sess: ' . $_SESSION['arcade_shout_session'] . ' ~ ' . 'req: ' . $sessionCheck . '</div>');//fatal_lang_error('arcade_shoutbox_session', false); */
	// for now if they're logged in it will refresh.. if not then it won't!
	if (empty($context['user']['is_logged']))
		die();

	require_once($sourcedir . '/Subs.php');
	list($content, $shouts) = array('', array());
	$version = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	// the cache does not work for the shoutbox
	if (!empty($arcadeModSettings['arcade_cache_enable_shoutbox']))
	{
		if ($shouts = cache_get_data('arcade_shouts', (int)$arcadeModSettings['arcade_cache_shouttime']) == null)
		{
			$result = $smcFunc['db_query']('', '
				SELECT s.id_shout, s.id_member, s.content, s.time, m.real_name
				FROM {db_prefix}arcade_newshouts AS s
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
				ORDER BY id_shout DESC
				LIMIT 0, {int:limit}',
				array(
					'limit' => !empty($arcadeModSettings['arcade_show_shouts']) ? $arcadeModSettings['arcade_show_shouts'] : 25,
				)
			);

			while ($shout = $smcFunc['db_fetch_assoc']($result))
			{
				$shouts[] = array(
					'id_shout' => $shout['id_shout'],
					'id_member' => $shout['id_member'],
					'content' => str_replace(array("\n", "\t", "\r"), '', $shout['content']),
					'time' => $shout['time'],
					'real_name' => $shout['real_name'],
				);
			}
			$smcFunc['db_free_result']($result);
			cache_put_data('arcade_shouts', $shouts, $arcadeModSettings['arcade_cache_shouttime']);
		}
	}
	else
	{
		$result = $smcFunc['db_query']('', '
			SELECT s.id_shout, s.id_member, s.content, s.time, m.real_name
			FROM {db_prefix}arcade_newshouts AS s
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
			ORDER BY id_shout DESC
			LIMIT 0, {int:limit}',
			array(
				'limit' => !empty($arcadeModSettings['arcade_show_shouts']) ? $arcadeModSettings['arcade_show_shouts'] : 25,
			)
		);

		while ($shout = $smcFunc['db_fetch_assoc']($result))
		{
			$shouts[] = array(
				'id_shout' => $shout['id_shout'],
				'id_member' => $shout['id_member'],
				'content' => str_replace(array("\n", "\t", "\r"), '', $shout['content']),
				'time' => $shout['time'],
				'real_name' => $shout['real_name'],
			);
		}

		$smcFunc['db_free_result']($result);
	}

	foreach($shouts as $shout)
	{
		$content .= '<div class="arcadeShout" style="margin: 4px;">
						<div style="border: dotted 1px; padding: 2px 4px 2px 4px;" class="windowbg2">';

		if (allowedTo('arcade_admin'))
			$content .= '
							<a href="' . $scripturl.'?action=' . (empty($rom) ? 'arcade' : 'retro_arch') . ';sa=shout;del=' . $shout['id_shout'] . '">
								<img style="border: 0px;" src="' . $settings['default_images_url'] . '/arc_icons/del1.png" alt="X"  title="' . $txt['arcade_shout_del'] . '"/>
							</a>&nbsp;';

		$content .= '
							<b>' . $shout['real_name'] . '</b>
						</div>
						<div style="padding: 2px;">' . timeformat($shout['time']) . '</div>
						<div style="padding: 4px;">' . arcade_shout_analyzer(wordwrap(parse_bbc(censorText($shout['content'])), 34, "\n", true), $version) . '</div>
					</div>';


	}

	die($content);
}

function arcade_shout_analyzer($img_tag, $version = 'v2.0')
{
	global $txt;

	if (empty($img_tag))
		return '';
	// shoutbox wordwrap messes up the img src... so fix it
	$result = '';
    $doc = new DOMDocument();
	libxml_use_internal_errors(true);
	$searchText = mb_convert_encoding($img_tag, 'HTML-ENTITIES', "UTF-8");
    if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD'))
		$doc->loadHTML($searchText, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	else
		$doc->loadHTML($searchText);
	libxml_clear_errors();
	$tags = $doc->getElementsByTagName('IMG');
    foreach ($tags as $tag)
	{
        $old_src = $tag->getAttribute('src');
        $new_src_url = preg_replace('/\v(?:[\v\h]+)/', '', $old_src);
        $tag->setAttribute('src', $new_src_url);
    }

	// ... remove any links for those not allowed to view them otherwise keep link but change link text to generic value
	if (!allowedTo('arcade_view_hyperlink'))
	{
		$tags = $doc->getElementsByTagName('A');
		$newelement = $doc->createTextNode($txt['arcade_no_links']);
		foreach ($tags as $tag)
			$tag->parentNode->replaceChild($newelement, $tag);
	}
	else
	{
		$tags = $doc->getElementsByTagName('A');
		$newelement = $doc->createTextNode($txt['arcade_linkname']);
		foreach ($tags as $tag)
			$tag->nodeValue = $txt['arcade_linkname'];
	}

	foreach($doc->childNodes as $node)
	{
		if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD'))
			$result .= $doc->saveHTML($node);
		else
			$result .= preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $doc->saveHTML($node));
	}

	if ($version !== 'v2.1')
		$result = trim(preg_replace('/<img([^>]*)>/i', "<img $1 />", $result));

	return !empty($result) ? str_replace('%0A', '', $result) : $txt['arcade_no_links'];
}

function add_to_arcade_shoutbox_ajax()
{
	global $user_info, $smcFunc, $arcSettings, $txt, $context;
	
	if (isset($_POST))
		$data = $_POST;

	$userId = isset($data['arcade_shout_user_id']) ? $data['arcade_shout_user_id'] : 0;
	$shout = isset($data['arcade_shout_text']) ? urldecode($data['arcade_shout_text']) : '';
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);

	if (empty($context['user']['is_logged']) || $user_info['id'] != (int)$userId || empty($shout))
		exit();

	// remove hyperlinks for those not allowed to shout them
	if (!allowedTo('arcade_hyperlink'))
	{
		$url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^‌​,.\s])@';
		$shout = preg_replace($url, $txt['arcade_no_links'], $shout);

	}
	
	$smcFunc['db_insert']('insert',
		'{db_prefix}arcade_newshouts',
		array(
			'id_member' => 'int', 'content' => 'string-255', 'time' => 'int',
		),
		array(
			$user_info['id'], $shout, time(),
		),
		array('id_shout')
	);

	cache_put_data('arcade_shouts', null, 86400);
}

function add_to_arcade_shoutbox_score($shout)
{
	global $arcadeModSettings, $smcFunc, $arcSettings, $txt, $context;

	// remove hyperlinks for those not allowed to shout them
	if (!allowedTo('arcade_hyperlink'))
	{
		$url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^‌​,.\s])@';
		$shout = preg_replace($url, $txt['arcade_no_links'], $shout);

	}
	$userId = !empty($arcadeModSettings['arcadePosterid']) ? (int)$arcadeModSettings['arcadePosterid'] : 0;
	if (!empty($userId))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE id_member = {int:userid}',
			array(
				'userid' => $userId,
			)
		);
		$found = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($found))
		{
			$smcFunc['db_insert']('insert',
				'{db_prefix}arcade_newshouts',
				array(
					'id_member' => 'int', 'content' => 'string-255', 'time' => 'int',
				),
				array(
					$userId, $shout, time(),
				),
				array('id_shout')
			);

			cache_put_data('arcade_shouts', null, 86400);
		}
	}
}

function arcade_shout_scores($data, $type)
{
	global $smcFunc, $arcadeModSettings, $txt, $user_info;

	switch ($type)
	{
		case 'guest':
			if (!empty($arcadeModSettings['arcade_shout_guest_score']))
			{
				$gameName = !empty($data[0]) ? $data[0] : '';
				$gameScore = !empty($data[1]) ? $data[1] : '0';
				if (!empty($gameScore) && !empty($gameName))
				{
					$message = sprintf($txt['arcade_shout_guest_score_text'], round($gameScore, 2), $gameName);
					add_to_arcade_shoutbox_score($message);
				}
			}
			break;
		case 'arena':
			if (!empty($arcadeModSettings['arcade_shout_arena_score']))
			{
				$name = !empty($user_info['name']) ? $user_info['name'] : $user_info['username'];
				$gameName = !empty($data[0]) ? $data[0] : '';
				$matchName = !empty($data[1]) ? $data[1] : '';
				$currentRound = !empty($data[2]) ? $data[2] : '0';
				$gameScore = !empty($data[3]) ? $data[3] : '0';
				if (!empty($gameScore) && !empty($gameName) && !empty($matchName) && !empty($currentRound))
				{
					$message = sprintf($txt['arcade_shout_arena_score_text'], $name, round($gameScore, 2), $gameName, $currentRound);
					add_to_arcade_shoutbox_score($message);
				}
			}
			break;
		default:
			if (!empty($arcadeModSettings['arcade_shout_member_score']))
			{
				$name = !empty($user_info['name']) ? $user_info['name'] : $user_info['username'];
				$gameName = !empty($data[0]) ? $data[0] : '';
				$gameScore = !empty($data[1]) ? $data[1] : '0';
				if (!empty($gameScore) && !empty($gameName))
				{
					$message = sprintf($txt['arcade_shout_member_score_text'], $name, round($gameScore, 2), $gameName);
					add_to_arcade_shoutbox_score($message);
				}
			}
	}
}

?>