<?php

function EventMain() {
	
	$subActions = array(
		'candybox' => 'candyBox'
	);
	
	call_helper($subActions[$_REQUEST['sa']]);
}

function candyBox() {
	global $context, $scripturl, $smcFunc, $user_info, $txt;

	loadLanguage('Event');
	$event = 'halloween2026';

	if ($user_info['is_guest'])
	{
		$context['page_label'] = $txt['dumb_event_halloweenguest'];
	}
	else
	{
		// attemtping to add a new candy?
		if (isset($_GET['candy'])) {
			if (validateToken('candy', 'get', false)) {
				$smcFunc['db_query']('', '
					INSERT INTO {db_prefix}eventscore
					VALUES ({int:user_id}, {string:event}, 1)
					ON DUPLICATE KEY UPDATE score = score + 1',
					[
						'user_id' => $user_info['id'],
						'event' => $event,
					]
				);
				$context['profile_updated'] = $txt['dumb_event_success'];
			}
			else $context['profile_updated'] = $txt['dumb_event_fail'];
		}
		$requestDUMBIE = $smcFunc['db_query']('', '
			SELECT score FROM {db_prefix}eventscore
			WHERE event = {string:event}
			AND id_member = {int:user_id}',
			[
				'user_id' => $user_info['id'],
				'event' => $event,
			]
		);
		$val = $smcFunc['db_fetch_row']($requestDUMBIE)[0];
		$smcFunc['db_free_result']($requestDUMBIE);
		$context['page_label'] = sprintf($txt['dumb_event_halloweenself'], $val);
	}

	$requestDUMBIE = $smcFunc['db_query']('', '
		SELECT mbr.real_name, mbr.id_group, scr.id_member, scr.score
		FROM {db_prefix}eventscore scr
		INNER JOIN {db_prefix}members mbr ON mbr.id_member = scr.id_member
		WHERE event = {string:event}
		ORDER BY score DESC',
		[
			'user_id' => $user_info['id'],
			'event' => $event,
		]
	);
	
	$winnertally = -1;
	$candytally = [];
	while ($row = $smcFunc['db_fetch_assoc']($requestDUMBIE))
	{
		$lnk = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" class="group-' . $row['id_group'] . '">' . $row['real_name'] . '</a>';
		$candytally[$lnk] = ['label' => $row['score']];
		if ($winnertally == -1)
		{
			$winnertally = $row['score'];
			$candytally[$lnk]['perc'] = 100;
		}
		else
		{
			$candytally[$lnk]['perc'] = floor(($row['score'] / $winnertally) * 100);
		}
	}

	$smcFunc['db_free_result']($requestDUMBIE);

	loadTemplate('Event');
	$context['template_layers'][] = 'event';
	$context['sub_template'] = 'candybox';
	$context['page_title'] = $txt['dumb_event_halloweentitle'];

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=event&sa=candybox',
		'name' => $txt['dumb_event_halloweentitle']
	);

	$context['candyscores'] = $candytally;
}

function event_load_theme() {
	global $context, $user_info;

	$context['has_candy'] = false;
	if ($user_info['is_guest'] || ($_GET['action'] ?? '') === 'event')
		return;

	if (rand(0, 10) == 0)
	{
		$context['has_candy'] = true;
		loadJavaScriptFile('trickRtreat.js', ['minimize' => true], 'halloween');
		loadCSSFile('trickRtreat.css', ['minimize' => true], 'halloween');
	}
}

?>
