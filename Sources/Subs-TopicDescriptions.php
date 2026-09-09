<?php

function topic_descriptions_admin_areas(array &$admin_areas): void
{
	global $txt;

	loadLanguage('TopicDescriptions');
	$admin_areas['config']['areas']['modsettings']['subsections']['topicdescriptions'] = array($txt['topic_descriptions']);
}

function topic_descriptions_modify_modifications(array &$sub_actions): void
{
	global $sourcedir;

	require_once $sourcedir . '/ManageTopicDescriptions.php';
	loadLanguage('TopicDescriptions');
	$sub_actions['topicdescriptions'] = 'ModifyTopicDescriptionsSettings';
}

function topic_descriptions_admin_search(array &$language_files, array &$include_files, array &$settings_search): void
{
	$language_files[] = 'TopicDescriptions';
	$include_files[] = 'ManageTopicDescriptions';
	$settings_search[] = array('ModifyTopicDescriptionsSettings', 'area=modsettings;sa=topicdescriptions');
}

function topic_descriptions_message_index(array &$message_index_selects): void
{
	$message_index_selects[] = 'COALESCE(t.description, "") AS description';

	loadLanguage('TopicDescriptions');
}

function topic_descriptions_display_topic(array &$topic_selects): void
{
	$topic_selects[] = 'COALESCE(t.description, "") AS description';

	loadLanguage('TopicDescriptions');
}

function topic_descriptions_before_create_topic(array &$msgOptions, array &$topicOptions, array &$posterOptions, &$topic_columns, &$topic_parameters): void
{
	$topic_columns['description'] = 'string';
	$topic_parameters[] = $msgOptions['description'] ?? '';
}

function topic_descriptions_post_end(): void
{
	global $context, $modSettings, $txt;

	loadLanguage('TopicDescriptions');
	$context['can_see_description'] = !empty($modSettings['topic_descriptions_boards']) && $context['is_first_post'] && in_array($context['current_board'], explode(",", $modSettings['topic_descriptions_boards']));

	if ($context['can_see_description'])
		$context['posting_fields']['description'] = array(
			'label' => array(
				'text' => $txt['topic_descriptions_post_desc'],
			),
			'input' => array(
				'type' => 'text',
				'attributes' => array(
					'size' => 80,
					'maxlength' => min(!empty($modSettings['topic_descriptions_maxlen']) ? (int) $modSettings['topic_descriptions_maxlen'] : 25, 140),
					'value' => $context['description'],
				),
			),
		);
}

function topic_descriptions_post_JavascriptModify(array &$post_errors): void
{
	global $modSettings, $smcFunc, $txt;

	if (isset($_POST['description']) && $smcFunc['htmltrim']($_POST['description']) !== '')
	{
		$_POST['description'] = strtr($smcFunc['htmlspecialchars']($_POST['description']), array("\r" => '', "\n" => '', "\t" => ''));

		$max_descriptionLength = min(!empty($modSettings['topic_descriptions_maxlen']) ? (int) $modSettings['topic_descriptions_maxlen'] : 25, 140);
		if ($smcFunc['strlen']($_POST['description']) > $max_descriptionLength)
		{
			$txt['error_long_description'] = sprintf($txt['error_long_description'], $max_descriptionLength);
			$post_errors[] = 'long_description';
			unset($_POST['description']);
		}

		// Maximum number of bytes allowed.
		$_POST['description'] = $smcFunc['truncate']($_POST['description'], 0, 255);
	}
}

function topic_descriptions_jsmodify_xml(): void
{
	global $context;

	if (isset($_POST['description']))
	{
		if (!isset($context['message']['errors']))
		{
			$context['message']['description'] = $_POST['description'];
			censorText($context['message']['description']);
		}
	}
 	else
		$context['message']['description'] = '';
}
