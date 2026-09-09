<?php

function ModifyTopicDescriptionsSettings(bool $return_config = false)
{
	global $context, $txt, $sourcedir, $scripturl, $smcFunc, $modSettings;

	$config_vars = array(
		array('title', 'settings'),
		array('check', 'topic_descriptions_enable'),
		array('select', 'topic_descriptions_where', array($txt['topic_descriptions_below'], $txt['topic_descriptions_right'])),
		'',
		array('check', 'topic_descriptions_topics'),
		array('int', 'topic_descriptions_maxlen', 4),
		array('title', 'admin_boards'),
		array('desc', 'select_boards_from_list'),
		array(isset($_GET['save']) ? 'text' : 'callback', 'topic_descriptions_boards'),
	);

	if ($return_config)
		return $config_vars;

	$boards = isset($modSettings['topic_descriptions_boards'])
		? explode(',', $modSettings['topic_descriptions_boards'])
		: [];

	$request = $smcFunc['db_query']('', '
		SELECT id_board, b.name, child_level, c.name AS cat_name, id_cat
		FROM {db_prefix}boards AS b
			JOIN {db_prefix}categories AS c USING (id_cat)
		WHERE redirect = {string:empty_string}
		ORDER BY board_order',
		array(
			'empty_string' => '',
		)
	);
	$i = -1;
	$a = array();
	$context['categories'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($a[$row['id_cat']]))
		{
			$context['categories'][++$i] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
				'boards' => []
			);
			$a[$row['id_cat']] = true;
		}

		$context['categories'][$i]['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'child_level' => (int) $row['child_level'],
			'selected' => in_array($row['id_board'], $boards)
		);
	}
	$smcFunc['db_free_result']($request);

	require_once $sourcedir . '/ManageServer.php';

	if (isset($_GET['save']))
	{
		checkSession();

		if (isset($_POST['topic_descriptions_boards']) && is_array($_POST['topic_descriptions_boards']))
			$_POST['topic_descriptions_boards'] = implode(',', array_filter($_POST['topic_descriptions_boards'], 'ctype_digit'));

		saveDBSettings($config_vars);
		$_SESSION['adm-save'] = true;
		redirectexit('action=admin;area=modsettings;sa=topicdescriptions');
	}

	if (!defined('SMF_VERSION'))
		add_integration_function('integrate_buffer', 'topic_descriptions_buffer', false);

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;sa=topicdescriptions;save';
	$context['page_title'] = $txt['topic_descriptions'] . ' - ' . $txt['settings'];
	$context['sub_template'] = 'show_settings';
	$context['html_headers'] .= '
	<style>
		#admin_form_wrapper fieldset > ul {
			display: grid;
		}
		@media (min-width: 720px) {
			#admin_form_wrapper fieldset > ul {
				grid-template-columns: 1fr 1fr;
			}
		}
		@media (min-width: 1200px) {
			#admin_form_wrapper fieldset > ul {
				grid-template-columns: 1fr 1fr 1fr;
			}
		}
		#admin_form_wrapper ul {
			padding-' . ($context['right_to_left'] ? 'right' : 'left') . ': 1em;
		}
	</style>';

	loadTemplate('ManageTopicDescriptions');
	loadLanguage('TopicDescriptions');
	prepareDBSettingContext($config_vars);

	if (defined('SMF_VERSION'))
	{
		// Two tokens because saving these settings requires both save_inline_permissions and saveDBSettings
		createToken('admin-mp');
		createToken('admin-dbsc');
	}
}
