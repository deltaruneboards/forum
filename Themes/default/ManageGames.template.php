<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_manage_games_list()
{
	global $context, $txt, $scripturl, $settings;
	$selected = $context['arcade_smf_version'] == '' ? 'selected ' : 'selected="selected" ';
	$alphaArray = array_merge(array('All'), range('A', 'Z'));

	echo '
		<div style="padding: 2em 1em 2em 1em; width: 100%;">
			<div style="text-align: left;display: inline;padding-left: 3em;float: right;">
				<select id="arcadealphaval">
					<option value="0">' . $txt['arcade_manage_sort_select_alpha'] . '</option>';

	foreach ($alphaArray as $letter)
	{
		echo '
					<option ' . (!empty($_SESSION['arcade_manage_sort_select_alpha']) && $_SESSION['arcade_manage_sort_select_alpha'] == $letter ? $selected : '') . 'value="' . $letter . '">' . sprintf($txt['arcade_manage_sort_alphabetical'], $letter) . '</option>';
	}

	echo '
				</select>
			</div>
			<div style="width: 50%;text-align: right;display: inline;padding-left: 3em;float: right;">
				<select id="arcadetypeval">
					<option value="0">' . $txt['arcade_manage_sort_select_type'] . '</option>';

	foreach ($context['arcade_game_types_search_list'] as $key => $type)
	{
		if (empty($txt['arcade_manage_sort_gametype'][$key]))
			continue;

		echo '
					<option ' . (!empty($_SESSION['arcade_manage_sort_select_type']) && $_SESSION['arcade_manage_sort_select_type'] == $type ? $selected : '') . 'value="' . $type . '">' . $txt['arcade_manage_sort_gametype'][$key] . '</option>';
	}

	echo '
				</select>
			</div>
		</div>';

	if (!empty($context['qaction_title']))
	{
		echo '
		<div id="arcade_message">
			<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex 0px 2ex; border: 1px dashed green; color: green;">
				<div style="text-decoration: underline;" id="arcade_message_title">', $context['qaction_title'], '</div>
				<div id="arcade_message_text">', $context['qaction_text'], ':
					<ul>';

		if (!empty($context['qaction_data']) && is_array($context['qaction_data']))
		{
			foreach ($context['qaction_data'] as $game)
			{
				echo '
						<li>', $game['name'], !empty($game['error']) ? '<div class="smalltext" style="color: red">' . vsprintf($txt[$game['error'][0]], $game['error'][1]) . '</div>' : '', '</li>';
			}
		}

		if (!empty($_SESSION['arcade_exists']))
		{
			echo '
						<hr />';
			foreach ($_SESSION['arcade_exists'] as $gamex)
			{
				echo '
						<li><div class="smalltext" style="color: red">' . sprintf($txt['arcade_upload_path_exists'], $gamex) . '</div></li>';
			}
			$_SESSION['arcade_exists'] = array();
		}

		echo '
						<li style="padding-top: 2px;list-style: none;"><span>&nbsp;</span></li>
					</ul>
				</div>
			</div>
		</div>
		<form enctype="multipart/form-data" id="upload_form" action="', $scripturl, '?action=admin;area=managegames;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="sesc" value="', $context['session_id'], '" />
		</form>';
	}

	template_show_list('games_list');
}

function template_manage_games_uninstall_confirm()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<form action="', $context['confirm_url'], ';confirm;sesc=', $context['session_id'], '" method="post" enctype="multipart/form-data">
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />

		<div class="tborder">
			<div style="display: table;border: 0px;padding: 4px;margin: 1px;width: 100%;" class="bordercolor">
				<div style="display: table-row;" class="catbg">
					<div style="display: table-cell;">', $context['confirm_title'], '</div>
				</div>
				<div style="display: table-row;">
					<div style="display: table-cell;" class="windowbg2">
						', $context['confirm_text'], '
						<div><span style="display: none;">&nbsp;</span></div>
						<input type="checkbox" name="remove_files" value="1" class="check" ', ($context['arcade_smf_version'] == 'v2.1' ? 'checked' : 'checked="checked"'), ' /> ', $txt['arcade_games_uninstall_remove_files'], '<br />
						<div style="display: table;border: 0px;padding: 1px;margin: 0px;width: 100%;">
							<div style="display: table-row;">
								<div style="display: table-cell;width: 10px;"></div>
								<div style="display: table-cell;"><b>', $txt['arcade_game_name'], '</b></div>
							</div>';

	if (!empty($context['games']))
	{
		$alternate = true;

		foreach ($context['games'] as $id => $game)
		{
			echo '
							<div style="display: table-row;" class="windowbg', $alternate? '' : '2', '">
								<div style="display: table-cell;"><input class="check" id="game', $id, '" type="checkbox" name="game[]" value="', $id, '" ', ($context['arcade_smf_version'] == 'v2.1' ? 'checked' : 'checked="checked"'), ' /></div>
								<div style="display: table-cell;"><label for="game', $id, '">', $game['name'], '</label></div>
							</div>';

			$alternate = !$alternate;
		}
	}

	echo '
						</div>
					</div>
				</div>
				<div style="display: table-row;">
					<div style="display: table-cell;text-align: right;">
						<input class="button_submit" type="submit" name="save" value="', $context['confirm_button'], '" />
					</div>
				</div>
			</div>
		</div>
	</form>';
}

function template_manage_games_upload()
{
	global $scripturl, $context, $txt, $boardurl, $boarddir, $arcadeModSettings, $settings, $sourcedir;

	echo '
	<div class="cat_bar uptitle">
		<h3 class="catbg">
			', $txt['arcade_upload'], '
		</h3>
	</div>
	<div class="windowbg2 upcontainer">
		<span class="topslice"><span>&nbsp;</span></span>
		<div style="padding: 0.5em;" id="arcade_container">', (!empty($arcadeModSettings['arcadeUploadSystem']) ?	'
			<div style="padding: 4px 0px 4px 0px;font-family: tahoma;" class="mediumtext centertext">
				<span style="border: 1px solid;padding: 2px;">' . $txt['arcade_supported_filetypes'] . '</span>
			</div>
			<form id="upload" method="post" action="' . $scripturl . '?action=admin;area=managegames;sa=upload2" enctype="multipart/form-data" accept-charset="' . $context['character_set'] . '">
				<div id="entire_upcon" style="overflow: hidden;border: 0px;margin: 0 auto;">
					<div id="upload_container">
						<div id="drop" class="centertext" style="height: 200px;padding-top: 30px;width: 100%;padding: 0px;margin: 0 auto;top: 50px;bottom: 50px;' . ($context['arcade_smf_version'] == 'v2.1' ? '' : 'position: relative;') . '">
							<span style="display: block;padding:8px 4px 8px 4px;color:#fff;font-size:14px;border-radius:2px;cursor:pointer;display:inline-block;margin: 0 auto;line-height:1em;text-align: center;">' . $txt['arcade_upload_msg1'] . '</span>
							<span style="display: block;padding-top: 20px;"></span>
							<a style="background-color:#007a96;padding:8px 4px 8px 4px;color:#fff;font-size:12px;border-radius:2px;cursor:pointer;display:inline-block;margin: 0 auto;line-height:1em;text-align: center;">' . $txt['arcade_upload_msg2'] . '</a>
							<input style="display: none;" type="file" accept=".tar, .zip, .gz, .TAR, .ZIP, .GZ' . (class_exists('RarArchive') ? ', .rar, .RAR' : '') . '" name="upl" multiple />
						</div>
					</div>
					<ul style="text-align: center;">
						<!-- The file uploads will be shown here -->
					</ul>
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
				</div>
			</form>' : '
			<div style="padding: 2px 0px 15px 0px;font-family: tahoma;" class="mediumtext">
				<span style="border: 1px solid;padding: 2px;">' . $txt['arcade_supported_filetypes'] . '</span>
			</div>
			<form id="upload_form" action="' . $scripturl . '?action=admin;area=managegames;sa=upload2" method="post" enctype="multipart/form-data" accept-charset="' . $context['character_set'] . '">
				<input accept=".tar, .zip, .gz, .TAR, .ZIP, .GZ' . (class_exists('RarArchive') ? ', .rar, .RAR' : '') . '" type="file" size="48" name="attachment[]" /><br />
				<input accept=".tar, .zip, .gz, .TAR, .ZIP, .GZ' . (class_exists('RarArchive') ? ', .rar, .RAR' : '') . '" type="file" size="48" name="attachment[]" /><br />
				<input accept=".tar, .zip, .gz, .TAR, .ZIP, .GZ' . (class_exists('RarArchive') ? ', .rar, .RAR' : '') . '" type="file" size="48" name="attachment[]" /><br />
				<div style="padding: 4px 0px 4px 0px;" class="smalltext">' . $txt['post_max_size'] . ' ' . $context['post_max_size'] . ' MB</div>
				<input class="button_submit" type="submit" name="upload" value="' . $txt['arcade_upload_button'] . '" />
				<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
			</form>');

	echo '
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div>';
}

function template_edit_game_above()
{
	global $scripturl, $context, $txt;

	echo '
			<form action="', $scripturl, '?action=admin;area=managegames;sa=edit2;sesc=', $context['session_id'], '" method="post" enctype="multipart/form-data">
				<input type="hidden" name="game" value="', $context['game']['id'], '" />
				<input type="hidden" name="sesc" value="', $context['session_id'], '" />
				<input type="hidden" name="edit_page" value="', $context['edit_page'], '" />
				<input type="hidden" id="fileflag" name="file_flag" value="0" />
				<div class="cat_bar">
					<h3 class="catbg">
						', $context['game']['name'], '
					</h3>
				</div>
				<span class="clear upperframe"><span></span></span>
				<div class="roundframe">
					<div class="innerframe">
							<div>
								<div>';


	if (isset($context['errors']))
	{
		echo '
					<ul class="error">';

		foreach ($context['errors'] as $field => $error) {
			if (in_array($field, array('thumbnail', 'thumbnail_small'))) {
				$errorMsg = $error == 'errorfile' ? $txt['arcade_errorfile_' . $field] : $txt['arcade_error_' . $field];
				echo '
						<li style="font-size: 8pt;">[', ($field), '] ', $errorMsg, '</li>';
			}
			elseif (!empty($context['coverIconEnabled']) && $field == 'cover_icon') {
				$errorMsg = $error == 'errorfile' ? $txt['arcade_errorfile_' . $field] : $txt['arcade_error_' . $field];
				echo '
						<li style="font-size: 8pt;">[', ($field), '] ', $errorMsg, '</li>';
			}
			elseif ($field != 'cover_icon')
				echo '
						<li>[', ($field), ']: ', $error, '</li>';
		}

		echo '
					</ul>';
	}
	if ((!empty($context['game']['thumb1']) || !empty($context['game']['thumb2']) || !empty($context['game']['covericon'])) && (empty($context['errors']['thumbnail']) || empty($context['errors']['thumbnail_small']) || empty($context['errors']['cover_icon']))) {
		echo '
					<ul class="error">',
						empty($context['errors']['thumbnail']) ? $context['game']['thumb1'] : '',
						empty($context['errors']['thumbnail_small']) ? $context['game']['thumb2'] : '',
						empty($context['errors']['cover_icon']) && !empty($context['coverIconEnabled']) ? $context['game']['covericon'] : '',
					'</ul>';
	}

	echo '
					<div style="display: table;border: 0px;width: 100%;" class="bordercolor">
						<div style="display: table-row;">
							<div style="display: table-cell;">
								<div style="display: table-row;width: 100%;">';
}

function template_edit_game_basic()
{
	global $scripturl, $context, $txt;

	$selected = $context['arcade_smf_version'] == '' ? 'selected ' : ' selected="selected"';
	echo '
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="game_name">', $txt['arcade_game_name'], '</label></div>
										<div style="display: table-cell;padding-bottom: 1rem;"><input type="text" name="game_name" id="game_name"  value="', $context['game']['name'], '" style="width: 99%" /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="thumbnail">', $txt['arcade_thumbnail'], '</label></div>
										<div id="thumb01" style="display: table-cell;"><input pattern="^[a-zA-Z0-9_\/\-\.]+$" type="text" name="thumbnail" id="thumbnail"  value="', $context['game']['thumbnail'], '" style="width: 99%" /></div>
										<div id="thumbnail_1" style="display: table-cell;height: 100%;padding-left: 2rem;margin: 0rem;position: absolute;vertical-align: middle;"><img style="vertical-align: middle;margin-top: 0.5rem;width: 2.25rem;height: 2.25rem;" class="icon" alt="" src="' . ($context['game']['thumbnail_prev']) . '" /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;padding-top: 1rem;"><label for="thumbnail_small">', $txt['arcade_thumbnail_small'], '</label></div>
										<div id="thumb02" style="display: table-cell;padding-top: 1rem;"><input pattern="^[a-zA-Z0-9_\/\-\.]+$" type="text" name="thumbnail_small" id="thumbnail_small"  value="', $context['game']['thumbnail_small'], '" style="width: 99%" /></div>
										<div id="thumbnail_2" style="display: table-cell;padding-top: 1rem;height: 100%;padding-left: 2rem;margin: 0rem;position: absolute;vertical-align: middle;"><img style="vertical-align: middle;margin-top: 0.5rem;width: 2.25rem;height: 2.25rem;" class="icon" alt="" src="' . ($context['game']['thumbnail_small_prev']) . '" /></div>
									</div>
									<div style="display: ' . (!empty($context['coverIconEnabled']) ? 'table-row' : 'none') . ';">
										<div style="display: table-cell;padding-top: 1rem;"><label for="cover_icon">', $txt['arcade_cover_icon'], '</label></div>
										<div id="covericon01" style="display: table-cell;padding-top: 1rem;"><input pattern="^[a-zA-Z0-9_\/\-\.]+$" type="text" name="cover_icon" id="cover_icon"  value="', $context['game']['cover_icon'], '" style="width: 99%" /></div>
										<div id="covericon_2" style="display: table-cell;padding-top: 1rem;height: 100%;padding-left: 2rem;margin: 0rem;position: absolute;vertical-align: middle;"><img style="vertical-align: middle;margin-top: 0.5rem;max-width: 150px;max-height: 197px;" class="icon" alt="" src="' . ($context['game']['cover_icon_prev']) . '" /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;padding-top: 0.5rem;"><label for="game_enabled">', $txt['arcade_enable_game'], '</label></div>
										<div style="display: table-cell;padding-top: 0.5rem;"><input type="checkbox" name="game_enabled" id="game_enabled"  value="1" ', $context['game']['enabled'] ? ' checked="checked"' : '', ' /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="game_enabled">', $txt['arcade_enable_download_game'], '</label></div>
										<div style="display: table-cell;"><input type="checkbox" name="game_download" id="game_download"  value="1" ', $context['game']['download'] ? ' checked="checked"' : '', ' /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="description">', $txt['arcade_description'], '</label></div>
										<div style="display: table-cell;">', $context['arcade_smf_version'] == 'v2.1' ? '
											<textarea name="description" id="description"  rows="5" cols="40" style="width: 99%">' . (isset($context['game']['description']) ? htmlspecialchars_decode($context['game']['description'], ENT_QUOTES|ENT_HTML5) : '') . '</textarea>' : '
											<textarea name="description" id="description"  rows="5" cols="40" style="width: 99%">' . (isset($context['game']['description']) ? htmlspecialchars_decode($context['game']['description'], ENT_QUOTES|ENT_XHTML) : '') . '</textarea>', '
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="help">', $txt['arcade_help'], '</label></div>
										<div style="display: table-cell;">', $context['arcade_smf_version'] == 'v2.1' ? '
											<textarea name="help" id="help"  rows="5" cols="40" style="width: 99%">' . (isset($context['game']['help']) ? htmlspecialchars_decode($context['game']['help'], ENT_QUOTES|ENT_HTML5) : '') . '</textarea>' : '
											<textarea name="help" id="help"  rows="5" cols="40" style="width: 99%">' . (isset($context['game']['help']) ? htmlspecialchars_decode($context['game']['help'], ENT_QUOTES|ENT_XHTML) : '') . '</textarea>', '
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="category">', $txt['arcade_category'], '</label></div>
										<div style="display: table-cell;">
											<select id="category" name="category">
												<option value="0">', $txt['arcade_no_category'], '</option>';

	foreach ($context['arcade_category'] as $cat)
		echo '
												<option value="', $cat['id'], '"', $cat['id'] == $context['game']['category'] ? $selected : '', '>' . arcadeFixTagsHTML($cat['name'], true) . '</option>';
	echo '
											</select>
										</div>
									</div>';

	if (!empty($context['game_permissions']))
	{
		echo '
									<div style="display: table-row;">
										<div style="display: table-cell;">', $txt['arcade_membergroups'], '</div>
										<div style="display: table-cell;">';

	foreach ($context['groups'] as $group)
		echo '
											<label for="groups_', $group['id'], '"><input type="checkbox" name="groups[]" value="', $group['id'], '" id="groups_', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' class="check" /><span', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['pgroups_post_group'] . '"' : '', '>', $group['name'], '</span></label><br />';

	echo '
											<i>', $txt['check_all'], '</i> <input type="checkbox" onclick="invertAll(this, this.form, \'groups[]\');" class="check" /><br />
											<br />
										</div>
									</div>';
	}

	echo '
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="internal_name">', $txt['arcade_internal_name'], '</label></div>
										<div style="display: table-cell;"><input type="text" id="internal_name" name="internal_name" value="', $context['game']['internal_name'], '" style="width: 99%" /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="game_directory">', $txt['arcade_directory'], '</label></div>
										<div style="display: table-cell;"><input type="text" id="game_directory" name="game_directory" value="', $context['game']['game_directory'], '" style="width: 99%" /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="game_file">', $txt['arcade_file'], '</label></div>
										<div style="display: table-cell;"><input type="text" id="game_file" name="game_file" value="', $context['game']['game_file'], '" style="width: 99%" /></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="submit_system">', $txt['arcade_submit_system'], '</label></div>
										<div style="display: table-cell;">
											<select id="submit_system" name="submit_system">';

	foreach ($context['submit_systems'] as $system)
		echo '
												<option value="', $system['system'], '"', $context['game']['submit_system'] == $system['system'] ? $selected : '', '>', $system['name'], '</option>';

	echo '
											</select>
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="score_type">', $txt['arcade_score_type'], '</label></div>
										<div style="display: table-cell;">
											<select id="score_type" name="score_type">
												<option value="0"', $context['game']['score_type'] == 0 ? $selected : '', '>', $txt['arcade_score_normal'], '</option>
												<option value="1"', $context['game']['score_type'] == 1 ? $selected : '', '>', $txt['arcade_score_reverse'], '</option>
												<option value="2"', $context['game']['score_type'] == 2 ? $selected : '', '>', $txt['arcade_score_none'], '</option>
											</select>
										</div>
									</div>
									<div style="display: ' . (stripos($context['game']['submit_system'], 'html5') !== false ? 'table-row' : 'none') . ';">
										<div style="display: table-cell;"><label for="score_type">', $txt['arcade_jsfile_insertion'], '</label></div>
										<div style="display: table-cell;">
											<select id="js_insertion" name="js_insertion">
												<option value="0"', $context['game']['js_insertion'] == 0 || stripos($context['game']['submit_system'], 'html5') === false ? $selected : '', '>', (explode('|', $txt['arcade_js_insertion'])[0]), '</option>
												<option value="1"', $context['game']['js_insertion'] == 1 && stripos($context['game']['submit_system'], 'html5') !== false ? $selected : '', '>', (explode('|', $txt['arcade_js_insertion'])[1]), '</option>
												<option value="2"', $context['game']['js_insertion'] == 2 && stripos($context['game']['submit_system'], 'html5') !== false ? $selected : '', '>', (explode('|', $txt['arcade_js_insertion'])[2]), '</option>
											</select>
										</div>
									</div>';

	if (isset($context['game']['extra_data']['flash_version']) || isset($_REQUEST['flash']))
	{
		echo '
									<div style="display: table-row;font-size: 9pt;">
										<div style="display: table-cell;margin-right: 0.5rem;">', $txt['arcade_extra_options_flash'], ':</div>
										<div style="display: table-cell;">
											<div style="display: table-row;width: 100%;">
												<div style="display: table-row;">
													<div style="display: table-cell;width: 25%;">', $txt['arcade_extra_options_width'], '</div>
													<div style="display: table-cell;"><input type="text" name="extra_data[width]" value="', $context['game']['extra_data']['width'], '" /></div>
												</div>
												<div style="display: table-row;">
													<div style="display: table-cell;width: 25%;">', $txt['arcade_extra_options_height'], '</div>
													<div style="display: table-cell;"><input type="text" name="extra_data[height]" value="', $context['game']['extra_data']['height'], '" /></div>
												</div>
												<div style="display: table-row;">
													<div style="display: table-cell;"><label>', $txt['arcade_extra_options_type'], '</label></div>
													<div style="display: table-cell;">
														<select name="extra_data[type]">
															<option value="normal"', $context['game']['extra_data']['type'] !== 'fullscreen' ? $selected : '', '>', $txt['arcade_extra_data_type_normal'], '</option>
															<option value="fullscreen"', $context['game']['extra_data']['type'] == 'fullscreen' ? $selected : '', '>', $txt['arcade_extra_data_type_full'], '</option>
														</select>
													</div>
												</div>', (stripos($context['game']['submit_system'], 'html5') === false ? '
												<div style="display: table-row;">
													<div style="display: table-cell;width: 25%;">' . $txt['arcade_extra_options_version'] . '</div>
													<div style="display: table-cell;"><input type="text" name="extra_data[flash_version]" value="' . $context['game']['extra_data']['flash_version'] . '" /></div>
												</div>' : '
												<input type="hidden" name="extra_data[flash_version]" value="' . $context['game']['extra_data']['flash_version'] . '" />'), '
												<div style="display: table-row;">
													<div style="display: table-cell;width: 25%;">', $txt['arcade_extra_options_backgroundcolor'], '</div>
													<div style="display: table-cell;">
														<input type="text" name="extra_data[background_color][0]" value="', $context['game']['extra_data']['background_color'][0], '" size="4"/>
														<input type="text" name="extra_data[background_color][1]" value="', $context['game']['extra_data']['background_color'][1], '" size="4"/>
														<input type="text" name="extra_data[background_color][2]" value="', $context['game']['extra_data']['background_color'][2], '" size="4"/>
													</div>
												</div>
											</div>
										</div>
									</div>';
	}

	if (stripos($context['game']['submit_system'], 'html5') !== false)
		echo '
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="icon_position">', $txt['arcade_icon_position'], '</label></div>
										<div style="display: table-cell;">
											<select id="icon_position" name="icon_position">
												<option value="0"', $context['game']['icon_position'] == 0 ? $selected : '', '>', $txt['arcade_icon_position_bot_left'], '</option>
												<option value="1"', $context['game']['icon_position'] == 1 ? $selected : '', '>', $txt['arcade_icon_position_bot_right'], '</option>
												<option value="2"', $context['game']['icon_position'] == 2 ? $selected : '', '>', $txt['arcade_icon_position_top_left'], '</option>
												<option value="3"', $context['game']['icon_position'] == 3 ? $selected : '', '>', $txt['arcade_icon_position_top_right'], '</option>
											</select>
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;"><label for="icon_position">', $txt['arcade_icon_position_hide'], '</label></div>
										<div style="display: table-cell;">
											<select id="icon_position_hide" name="icon_position_hide">
												<option value="0"', $context['game']['icon_position_hide'] == 0 ? $selected : '', '>', $txt['arcade_icon_position_hide_click'], '</option>
												<option value="1"', $context['game']['icon_position_hide'] == 1 ? $selected : '', '>', $txt['arcade_icon_position_hide_enable'], '</option>
												<option value="2"', $context['game']['icon_position_hide'] == 2 ? $selected : '', '>', $txt['arcade_icon_position_hide_disable'], '</option>
											</select>
										</div>
									</div>';
}

function template_edit_game_below()
{
	global $scripturl, $context, $txt;

	$extraSub = !empty($context['game']['subsequentData']) ? '<div style="padding: 1em;">' . $context['game']['subsequentData'] . '</div>' : '';
	$extraPrimary = !empty($context['game']['primaryConflictGame']) ? '<div style="padding: 1em;">' . $context['game']['primaryConflictGame'] . '</div>' : '';
	if (!empty($_SESSION['arcade_isMobile'])) {
		$play = '<a class="arcadeLinkStyle" title="' . $txt['alt_play'] . '" href="' . $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';">' . $txt['arcade_admin_play'] . '</a>';
	}
	else {
		$width = !empty($context['game']['extra_data']['width']) ? $context['game']['extra_data']['width'] : 500;
		$height = !empty($context['game']['extra_data']['height']) ? $context['game']['extra_data']['height'] : 500;
		$play = '<a title="' . $txt['arcade_popplay'] . '" href="javascript:void(0)" onclick="myGamePopupArcade(\'' . $scripturl . '?action=arcade;sa=play;game=' . $context['game']['id'] . ';pop=1' . '\',' . ($width+30) . ',' . ($height-20) . ', 0, false);">' . $txt['arcade_admin_play'] . '</a>';
	}

	echo '
								</div>
							</div>
						</div>', $extraPrimary, $extraSub, '
						<div style="display: table-row;">
							<div style="display: table-cell;padding-top: 2em;text-align: left;padding-left: 0.6em;position: relative;">
								<a href="', $scripturl, '?action=admin;area=managegames;sa=uninstall;game=', $context['game']['id'], '">', $txt['arcade_uninstall'], '</a>
								<span style="padding-left: 3.5em;">', $play, '</span>
							</div>
							<div style="display: table-cell;padding-top: 2em;text-align: right;padding-right: 0.6em;position: relative;">
								<input class="button_submit" type="submit" id="arcade_preview" name="preview" value="', $txt['arcade_preview'], '" /><br />
							</div>
							<div style="display: table-cell;padding-top: 2em;text-align: right;padding-right: 0.6em;position: relative;">
								<input class="button_submit" type="submit" name="save" value="', $txt['arcade_save'], '" /><br />
								<a href="', $scripturl, '?action=admin;area=managegames;sa=export;game=', $context['game']['id'], '">', (in_array(strtolower($context['game']['submit_system']), array('ibp', 'ibp2', 'ibp3', 'ibp32', 'html52', 'html53')) ? sprintf($txt['game_info_export'], $context['game']['internal_name'] . '.php') : sprintf($txt['game_info_export'], 'game-info.xml')), '</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<span class="lowerframe"><span></span></span></form>';
}

?>