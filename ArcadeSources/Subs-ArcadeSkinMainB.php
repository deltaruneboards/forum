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

// Defiant sub-routines
function ArcadeShout($rom = 0)
{
	global $smcFunc, $txt, $arcadeModSettings, $user_info;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	if (isset($_REQUEST['del']))
	{
	   // Only allow admins to delete shouts

       if (allowedTo('arcade_admin'))
       {
    		$id = (int)$_REQUEST['del'];

    		$smcFunc['db_query']('', '
    			DELETE FROM {db_prefix}arcade_newshouts
    			WHERE id_shout = {int:ids}',
    			array(
    				'ids' => $id,
    			)
    		);

			// force a reload
    		cache_put_data('arcade_shouts', null, 86400);
      }
	}
	elseif (!$user_info['is_guest'])
	{
		$_REQUEST['the_shout'] = isset($_REQUEST['the_shout']) ? $_REQUEST['the_shout'] : '';
		$shout = trim(strlen($_REQUEST['the_shout']) > 100 ? mb_substr($_REQUEST['the_shout'], 0, 100) . '...' : $_REQUEST['the_shout']);
		if (!empty($shout))
		{
			$shout = $txt['arcade_shouted'] . $smcFunc['htmlspecialchars']($shout, ENT_QUOTES);
			add_to_arcade_shoutbox($shout);
		}
	}

	redirectexit('action=' . $action);
}

function ArcadeLatestChamps($no, $rom = 0)
{
	global $smcFunc, $scripturl, $txt, $context, $arcadeModSettings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$rows = array();
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND g.rom_flag = {int:rom_flag}' : '';
	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeChamps = cache_get_data('arcade_champsB' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$results = $smcFunc['db_query']('', '
			SELECT g.id_game, g.game_name, g.thumbnail, g.thumbnail_small, g.game_directory, m.id_member, m.real_name, s.id_member, g.rom_flag
			FROM {db_prefix}arcade_games AS g
			LEFT JOIN {db_prefix}arcade_scores AS s ON ( g.id_champion_score = s.id_score )
			LEFT JOIN {db_prefix}members AS m ON ( m.id_member = s.id_member )
			WHERE g.id_champion_score > 0' . $whererom . '
			ORDER BY s.champion_from DESC
			LIMIT 0, {int:limit}',
			array(
			'limit' => $no,
			'rom_flag' => !empty($rom) ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_champsB' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeChamps) ? json_decode($arcadeChamps, true) : $rows;

	$top = array();
	foreach ($rows as $score)
	{
		$score['thumbnail'] = !empty($score['thumbnail']) ? $score['thumbnail'] : 'nofileexist.jpg';
		$score['thumbnail_small'] = !empty($score['thumbnail_small']) ? $score['thumbnail_small'] : 'nofileexist.jpg';
		$score['thumbnail'] = $score['thumbnail'] == 'nofileexist.jpg' ? $score['thumbnail_small'] : $score['thumbnail'];
		$action = empty($score['rom_flag']) ? 'arcade' : 'retro_arch';
		$score['game_name'] = strlen($score['game_name']) >= $context['nameCharLength'] ? substr($score['game_name'], 0, ($context['nameCharLength']-1)) . '...' : $score['game_name'];


		$top[] = array(
			'id' => $score['id_game'],
			'game_name' => $score['game_name'],
			'thumbnail' => $score['thumbnail'],
			'game_directory' => $score['game_directory'],
			'game_link' => '<a href="' . $scripturl . '?action=' . $action . ';sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
			'real_name' => $score['real_name'],
			'member_link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['arcade_guest'],
		);
	}
		return $top;
}

function category_games($rom = 0)
{
	global $smcFunc, $arcadeModSettings;
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$rom2 = !empty($rom) ? '_rom' : '';
	$rows = array();
	$whererom = empty($arcadeModSettings['arcadeRomToggle']) ? ' AND g.rom_flag = {int:rom_flag}' : '';
	list($no, $cats) = array(30, array());

	if (empty($arcadeModSettings['arcade_cache_enable']) || !($arcadeCats = cache_get_data('arcade_catsB' . $rom2, $arcadeModSettings['arcade_cache_time']))) {
		$results = $smcFunc['db_query']('', '
			SELECT c.id_cat, count(g.id_cat) AS games, c.cat_name, c.cat_icon, c.cat_order
			FROM {db_prefix}arcade_games g, {db_prefix}arcade_categories c
			WHERE g.id_cat = c.id_cat AND g.enabled = 1' . $whererom . '
			GROUP BY c.id_cat, c.cat_name, c.cat_icon, c.cat_order
			ORDER BY c.cat_order',
			array(
				'rom_flag' => !empty($rom) ? 1 : 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($results)) {
			$rows[] = $row;
		}
		$smcFunc['db_free_result']($results);
		if (!empty($arcadeModSettings['arcade_cache_enable'])) {
			cache_put_data('arcade_catsB' . $rom2, json_encode($rows, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION), $arcadeModSettings['arcade_cache_time']);
		}
	}
	$rows = !empty($arcadeCats) ? json_decode($arcadeCats, true) : $rows;

	foreach ($rows as $cat)
	{
		if (empty($cat['cat_icon']) && !empty($cat['cat_name'])) {
			$cat['cat_icon'] = ArcadeSpecialChars(trim($cat['cat_name']), 'image') . '.gif';
			$cat['cat_name'] = arcadeFixTagsHTML($cat['cat_name'], true);
		}

		$cats[$cat['id_cat']] = $cat;
	}
	return $cats;
}

function ArcadeInfoFader()
{
	global $arcadeModSettings;
	list($content, $cacheData, $i) = array('', array('news' => ''), 0);

	if (!empty($arcadeModSettings['enable_arcade_cache']))
	{
		if (($cacheData = cache_get_data('arcade_newsFader', 300)) == null)
		{
			$a_news = arcade_news_fader($arcadeModSettings['arcadeNewsFader'], $arcadeModSettings['arcadeNewsNumber']);
			foreach($a_news as $news_out)
			{
				$content .= '<div id="arcadeNews' . $i . '">' . addslashes($news_out['body']) . '</div>';
				$i++;
			}
			$cacheData['news'] = $content;
			cache_put_data('arcade_newsFader', $cacheData, 300);

		}
	}
	else
	{
		$a_news = arcade_news_fader($arcadeModSettings['arcadeNewsFader'], $arcadeModSettings['arcadeNewsNumber']);
		foreach($a_news as $news_out)
		{
			$content .= '<div id="arcadeNews' . $i . '">' . addslashes($news_out['body']) . '</div>';
			$i++;
		}

		$cacheData['news'] = !empty($content) ? $content : '';
	}

	return !empty($cacheData['news']) ? $cacheData['news'] : '';
}

function ArcadeInfoPanelBlock()
{
	global $context, $txt, $arcadeModSettings, $scripturl;
	list($no, $content, $gotd, $random, $cache3Best['info'], $cacheGotd['info']) = array(5, '', '', '', '', '');

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	if (!empty($arcadeModSettings['enable_arcade_cache']) && empty($rom))
	{
		if (($cache3Best = cache_get_data('arcade_infopanel' . $rom, 86400)) == null)
		{
			$content .= 'pausecontent[0] = document.getElementById("pausecontent0").innerHTML;';
			$content .= 'pausecontent[1] = document.getElementById("pausecontent1").innerHTML;';
			$content .= 'pausecontent[2] = document.getElementById("pausecontent2").innerHTML;';
			$content .= 'pausecontent[3] = document.getElementById("pausecontent3").innerHTML;';
			$content .= 'pausecontent[4] = document.getElementById("pausecontent4").innerHTML;';

			$cache3Best = array('info' => $content);
			cache_put_data('arcade_infopanel' . $rom, $cache3Best, 86400);
		}

		if (($cacheGotd = cache_get_data('arcade_gotd' . $rom, 86400)) == null)
		{
			$gotd .= 'pausecontent[5] = document.getElementById("pausecontent5").innerHTML;';

			$cacheGotd = array('info' => $gotd);
			cache_put_data('arcade_gotd' . $rom, $cacheGotd, 86400);
		}
	}
	elseif (!empty($arcadeModSettings['enable_arcade_cache']))
	{
		if (($cache3Best = cache_get_data('arcade_infopanel' . $rom, 86400)) == null)
		{
			$content .= 'pausecontent[0] = document.getElementById("pausecontent0").innerHTML;';
			$content .= 'pausecontent[1] = document.getElementById("pausecontent1").innerHTML;';

			$cache3Best = array('info' => $content);
			cache_put_data('arcade_infopanel' . $rom, $cache3Best, 86400);
		}

		if (($cacheGotd = cache_get_data('arcade_gotd' . $rom, 86400)) == null)
		{
			$gotd .= 'pausecontent[2] = document.getElementById("pausecontent2").innerHTML;';

			$cacheGotd = array('info' => $gotd);
			cache_put_data('arcade_gotd' . $rom, $cacheGotd, 86400);
		}
	}
	elseif (empty($rom))
	{
		$content .= 'pausecontent[0] = document.getElementById("pausecontent0").innerHTML;';
		$content .= 'pausecontent[1] = document.getElementById("pausecontent1").innerHTML;';
		$content .= 'pausecontent[2] = document.getElementById("pausecontent2").innerHTML;';
		$content .= 'pausecontent[3] = document.getElementById("pausecontent3").innerHTML;';
		$content .= 'pausecontent[4] = document.getElementById("pausecontent4").innerHTML;';
		$content .= 'pausecontent[5] = document.getElementById("pausecontent5").innerHTML;';
		$cache3Best['info'] = $content;
	}
	else
	{
		$content .= 'pausecontent[0] = document.getElementById("pausecontent0").innerHTML;';
		$content .= 'pausecontent[1] = document.getElementById("pausecontent1").innerHTML;';
		$content .= 'pausecontent[2] = document.getElementById("pausecontent2").innerHTML;';
		$cache3Best['info'] = $content;
	}

	$random = empty($rom) ? 'pausecontent[6] = document.getElementById("pausecontent6").innerHTML;' : 'pausecontent[3] = document.getElementById("pausecontent3").innerHTML;';
	return $cache3Best['info'] . $cacheGotd['info'] . $random;
}

function arcade_news_fader($board, $limit)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT id_first_msg
		FROM {db_prefix}topics
		WHERE id_board = {int:board}
 		ORDER BY id_first_msg DESC
 		LIMIT 0, {int:limit}',
		array(
		'limit' => $limit,
		'board' => $board,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$posts[] = $row['id_first_msg'];
	}

	if (empty($posts))
		return array();

	$result = $smcFunc['db_query']('', '
		SELECT m.body, m.smileys_enabled, m.id_msg
		FROM {db_prefix}topics AS t, {db_prefix}messages AS m
		WHERE t.id_first_msg IN (' . implode(', ', $posts) . ')
 		AND m.id_msg = t.id_first_msg
		ORDER BY t.id_first_msg DESC
 		LIMIT 0, {int:limit}',
		array(
		'limit' => count($posts),
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{

		$find  = '<br';
		$pos = strpos($row['body'], $find);

		if ($pos !== false)
			$row['body'] = substr($row['body'], 0, $pos);

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);
		censorText($row['body']);
		$return[] = array(
			'body' => $row['body'],
			'is_last' => false
		);
	}

	$return[count($return) - 1]['is_last'] = true;

	return $return;
}

function ArcadeInfoShouts($rom = 0)
{
    global $smcFunc, $scripturl, $settings, $txt, $sourcedir, $arcadeModSettings, $context;
	require_once($sourcedir . '/Subs.php');
	list($content, $shouts) = array('', array());
	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : $rom);
	$action = empty($rom) ? 'arcade' : 'retro_arch';
	$version = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	if (!empty($arcadeModSettings['enable_arcade_cache']))
	{
		if ($shouts = cache_get_data('arcade_shouts', 86400) == null)
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
			cache_put_data('arcade_shouts', $shouts, 86400);
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
		$content .= '
					<div style="margin: 4px;">
						<div style="border: dotted 1px; padding: 2px 4px 2px 4px;" class="windowbg2">';

		if (allowedTo('arcade_admin'))
			$content .= '
							<a href="' . $scripturl.'?action=' . $action . ';sa=shout;del=' . $shout['id_shout'] . '">
								<img style="border: 0px;" src="' . $settings['default_images_url'] . '/arc_icons/del1.png" alt="X"  title="' . $txt['arcade_shout_del'] . '"/>
							</a>&nbsp;';

		$content .= '
							<b>' . $shout['real_name'] . '</b>
						</div>
						<div style="padding: 2px;">' . timeformat($shout['time']) . '</div>
						<div style="padding: 4px;">' . arcade_shout_parser(wordwrap(parse_bbc(censorText($shout['content'])), 34, "\n", true), $version) . '</div>
					</div>';


	}

	return $content;
}

function arcade_shout_parser($img_tag, $version = 'v2.0')
{
	global $txt;

	if (empty($img_tag))
		return '';
	// shoutbox wordwrap messes up the img src... so fix it
	$result = '';
    $doc = new DOMDocument();
	libxml_use_internal_errors(true);
    if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD'))
		$doc->loadHTML($img_tag, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	else
		$doc->loadHTML($img_tag);
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

function add_to_arcade_shoutbox($shout)
{
	global $user_info, $smcFunc, $arcSettings, $txt;

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

?>