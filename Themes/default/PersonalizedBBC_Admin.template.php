<?php
/*
 * Personalized BBC was developed for SMF forums c/o Chen Zhen @ https://web-develop.ca
 * Copyright 2025  @ web-develop.ca
 * Distributed under the CC BY-ND 4.0 License (http://creativecommons.org/licenses/by-nd/4.0/)
*/
function template_PersonalizedBBC_Admin_above()
{
	global $txt;

	// Create the tabs for the template
	echo '
	<div class="cat_bar">
		<h3 class="catbg" style="text-align:center;">', $txt['PersonalizedBBC_tabtitle'], '</h3>
	</div>
	<div class="title_bar">
		<h4 class="titlebg" style="text-align:center;">', $txt['PersonalizedBBC_tabtitle_rev'], '</h4>
	</div>
	<div id="help_container">
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div id="pbcmain">';
}

// Personalized BBC settings list
function template_PersonalizedBBC_List()
{
	global $txt, $scripturl, $context, $settings;

	echo '
				<script type="text/javascript"><!-- // --><![CDATA[
					function confirmSubmit()
					{
						var agree=confirm("' . $txt['personalizedBBC_confirm'] . '");
						if (agree)
							return true ;
						else
							return false ;
					}
				// ]]></script>
				<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '">
					<table style="width: 80%;border: 0px;border-spacing: 0px;padding: 0px;margin-left: auto;margin-right: auto;" class="tborder">
						<tr>
							<td>
								<table style="border: 0px;padding: 4px;border-spacing: 0px;width: 100%;">
									<tr class="windowbg2 centertext">
										<td>', $txt['PersonalizedBBC_tabtitle_list'], '</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
									</tr>
									<tr>
										<td><hr /></td>
									</tr>
								</table>
								<table style="border: 1px;border-spacing: 1px solid;padding: 4px;width: 100%;">
									<tr class="catbg2">
										<td style="width: 19%;padding-left: 6px;"><u>' , $txt['personalizedBBC_name'] , '</u></td>
										<td style="width: 51%;padding-left: 6px;"><u>' , $txt['personalizedBBC_bbc'] , '</u></td>
										<td style="width: 10%;" class="centertext"><u>' , $txt['personalizedBBC_enable'] , '</u></td>
										<td style="width: 10%;" class="centertext"><u>' , $txt['personalizedBBC_display'] , '</u></td>
										<td style="width: 10%;" class="centertext"><u>' , $txt['personalizedBBC_delete'] , '</u></td>
									</tr>';

	foreach ($context['personalizedBBC'] as $i => $tag)
	{
		if (empty($context['personalizedBBC'][$i]['name']))
			continue;

		echo '
									<tr>
										<td class="windowbg" style="padding-left: 6px;">
											<div class="pbbc_bubbleInfo">
												<a href="' , $scripturl , '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;' , $context['session_var'] , '=' , $context['session_id'] , ';name=', $context['personalizedBBC'][$i]['name'], ';">' , $context['personalizedBBC'][$i]['name'] , '</a>
											</div>
										</td>
										<td class="windowbg" style="padding-left: 6px;" onmouseout="pbbc_hide(\'xx' . $context['personalizedBBC'][$i]['name'] . '\');" onmouseover="pbbc_show(\'xx' . $context['personalizedBBC'][$i]['name'] . '\');">
											<span>
												', str_replace('#%^@!', $context['personalizedBBC'][$i]['name'], $txt['personalizedBBC_type_display'][$context['personalizedBBC'][$i]['type']]), '
											</span>
											<div class="pbbc_popup windowbg2" id="xx' . $context['personalizedBBC'][$i]['name'] . '" style="display: none;">
												<span style="padding: 8px;display: inline;">', sprintf($txt['PersonalizedBBC_viewBBC'], '<img style="vertical-align: middle;position: relative;bottom: 1px;width: 20px;height: 22px;" src="' . $settings['theme_url'] . '/images/bbc/' . $context['personalizedBBC'][$i]['image'] . $context['PersonalizedBBC_imageType'] . '" alt="' . $txt['PersonalizedBBC_viewNoBBC'] . '" />'), '</span>
											</div>
										</td>
										<td class="windowbg centertext">
											<input type="checkbox" name="enable[', $context['personalizedBBC'][$i]['name'], ']" id="enable_', $context['personalizedBBC'][$i]['name'], '" class="check"';

		if (!empty($context['personalizedBBC'][$i]['enable']))
			echo '	checked />';

		else
			echo ' />';

		echo '
										</td>
										<td class="windowbg centertext">
											<input type="checkbox" name="display[', $context['personalizedBBC'][$i]['name'], ']" id="display_', $context['personalizedBBC'][$i]['name'], '" class="check"';

		if (!empty($context['personalizedBBC'][$i]['display']))
			echo ' checked />';

		else
			echo ' />';

		echo '
										</td>
										<td class="windowbg centertext">
											<span style="display:none;">
												<input type="text" name="current_name[', $context['personalizedBBC'][$i]['name'], ']" value="', $context['personalizedBBC'][$i]['name'], '" />
												<input type="text" name="list[', $context['personalizedBBC'][$i]['name'], ']" value="1" />
											</span>
											<input type="checkbox" name="delete[', $context['personalizedBBC'][$i]['name'], ']" class="check" />
										</td>
									</tr>';
	}

	echo '
								</table>
								<table style="border: 1px;padding: 4px;width: 100%;border-spacing: 0px;">
									<tr>
										<td><hr /></td>
									</tr>
									<tr>
										<td style="float:left;">
											<a href="' , $scripturl , '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;' , $context['session_var'] , '=' , $context['session_id'] , ';"><img style="position:relative;bottom:-2.05em;" src="', $settings['default_theme_url'],'/images/admin/personalizedBBC_add.png" alt="' , $txt['PersonalizedBBC_add'], '" title="' , $txt['PersonalizedBBC_add'], '" /></a>
										</td>
									</tr>
									<tr>
										<td style="float:right;">
											<input class="button_submit" type="submit" value="', $txt['personalizedBBC_submit'], '"', (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''), ' onclick="check=confirmSubmit();if(!check){return false;}" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>', $context['PersonalizedBBC_display']['page'];
}

// Personalized BBC revision
function template_PersonalizedBBC_Edit()
{
	global $txt, $scripturl, $context, $settings;

	echo '
				<script type="text/javascript"><!-- // --><![CDATA[
					var zUpdateStatus = function (personalized_bbc)
					{
						var check = false;
						var currentVal = "', $context['personalizedBBC']['url_fix'], '";
						for (i=0; i<3; i++)
						{
							if (i == personalized_bbc)
								document.getElementById("url_fix" + i).checked = true;
							else
								document.getElementById("url_fix" + i).checked = false;

							if (document.getElementById("url_fix" + i).checked == true)
								check = true;
						}

						if (!check)
						{
							var currentArray = ["rfc0", "rfc3986x", "rfc3986"];
							check = true;
							for (i=0; i<3; i++)
							{
								if (currentArray[i] === currentVal)
								{
									document.getElementById("url_fix" + i).checked = true;
									check = false;
								}
							}
						}
						if (check)
							document.getElementById("url_fix0").checked = true;
					}
					addLoadEvent(zUpdateStatus);
				// ]]></script>
				<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" name="PersonalizedBBC_spec">
					<table style="width: 80%;border: 0px;border-spacing: 0px;padding: 0px;margin-left: auto;margin-right: auto;" class="tborder" id="persoanlized_bbc_settings">
						<tr>
							<td>
								<table style="border: 0px;border-spacing: 0px;padding: 4px;width: 100%;">
									<tr class="windowbg2 centertext">
										<td>', $txt['PersonalizedBBC_tabtitle_rev'], '</td>
									</tr>
									<tr>
										<td><hr /></td>
									</tr>
								</table>
								<table style="border: 0px;border-spacing: 0px;padding: 4px;width: 100%;">
									', (!empty($_SESSION['personalizedBBC_duplicate_error']) ? '
									<tr>
										<td style="width: 2%;">&nbsp;</td>
										<td colspan="3" style="width:90%;">
											' . $txt['PersonalizedBBC_DuplicateErrorMessage'] . '
										</td>
									</tr>' : ''), '
									', (!empty($_SESSION['personalizedBBC_length_error']) ? '
									<tr>
										<td style="width: 2%">&nbsp;</td>
										<td colspan="3" style="width:90%;">
											' . $txt['PersonalizedBBC_LengthErrorMessage'] . '
										</td>
									</tr>' : ''), '
									', (!empty($_SESSION['personalizedBBC_illegal_error']) ? '
									<tr>
										<td style="width: 2%;">&nbsp;</td>
										<td colspan="3" style="width:90%;">
											' . $txt['PersonalizedBBC_IllegalErrorMessage'] . '
										</td>
									</tr>' : ''), '
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagHelp" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%">
											', $txt['personalizedBBC_name'], '
										</td>
										<td colspan="2" style="width: 70%;">
											<input type="text" size="50" name="name[', $context['current_name'],']" value="', $context['current_name'] ,'" />
											<span style="float:right;display: inline-block;vertical-align: middle;" title="', $txt['PersonalizedBBC_disabledFeature'] ,'">
												<span style="position:relative;vertical-align: bottom;">', $txt['PersonalizedBBC_Override'], '&nbsp;</span>
												<input style="position: relative;vertical-align: middle;display: inline-block;" type="checkbox" name="override" value="1" disabled="disabled" />
											</span>
										</td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagDescription" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_description'], '
										</td>
										<td colspan="2" style="width: 70%;">
											<input type="text" size="50" name="description[', $context['personalizedBBC']['current_name'],']" value="', $context['personalizedBBC']['description'],'" />
										</td>
									</tr>
									<tr>
										<td colspan="4"><hr /></td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagType" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_type'], '
										</td>
										<td colspan="2" style="width: 70%;">
											<select name="type[', $context['personalizedBBC']['current_name'],']" id="personalizedBBC_test_type" onchange="pbbc_test(this.value);">
												<option value="0" ', ($context['personalizedBBC']['type'] == 0 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_type_options'][0], '
												</option>
												<option value="1" ', ($context['personalizedBBC']['type'] == 1 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_type_options'][1], '
												</option>
												<option value="2" ', ($context['personalizedBBC']['type'] == 2 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_type_options'][2], '
												</option>
												<option value="3" ', ($context['personalizedBBC']['type'] == 3 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_type_options'][3], '
												</option>
											</select>
										</td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagParse" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_parse'], '
										</td>
										<td colspan="2" style="width: 70%;">
											<select name="parse[', $context['personalizedBBC']['current_name'],']">
												<option value="0" ', ($context['personalizedBBC']['parse'] == 0 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_parse_options'][0], '
												</option>
												<option value="1" ', ($context['personalizedBBC']['parse'] == 1 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_parse_options'][1], '
												</option>
												<option value="2" ', ($context['personalizedBBC']['parse'] == 2 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_parse_options'][2], '
												</option>
											</select>
										</td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagTrim" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_trim'], '
										</td>
										<td colspan="2" style="width: 70%;">
											<select name="trim[', $context['personalizedBBC']['current_name'],']">
												<option value="0" ', ($context['personalizedBBC']['trim'] == 0 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_trim_options'][0], '
												</option>
												<option value="1" ', ($context['personalizedBBC']['trim'] == 1 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_trim_options'][1], '
												</option>
												<option value="2" ', ($context['personalizedBBC']['trim'] == 2 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_trim_options'][2], '
												</option>
												<option value="3" ', ($context['personalizedBBC']['trim'] == 3 ? 'selected="selected"' : ''), '>
													', $txt['personalizedBBC_trim_options'][3], '
												</option>
											</select>
										</td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagBlockLvl" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_blockLvl'], '
										</td>
										<td colspan="2" style="width: 70%;">
											<input type="checkbox" name="block_lvl[', $context['personalizedBBC']['current_name'],']" value="1"', (!empty($context['personalizedBBC']['block_lvl']) ? ' checked="checked"' : ''),' />
										</td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="' . $scripturl . '?action=helpadmin;help=personalizedBBC_tagViewSource" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											' . $txt['personalizedBBC_viewSource'] . '
										</td>
										<td style="width: 70%;">
											<input type="checkbox" name="view_source[' . $context['personalizedBBC']['current_name'] . ']" value="1"' . (!empty($context['personalizedBBC']['view_source']) ? ' checked="checked"' : '') . ' />
										</td>
										<td style="width: 40%;">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="4"><hr /></td>
									</tr>
									<tr>
										<td style="width: 2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagCode" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_code'], '
										</td>
										<td colspan="2">
											<textarea name="code[', $context['personalizedBBC']['current_name'],']" rows="4" cols="50" tabindex="5">', $context['personalizedBBC']['code'], '</textarea>
											<span style="float:right;padding-left:2px;">
												<span style="position:relative;bottom:3px;">', $txt['PersonalizedBBC_UrlCheck'], '&nbsp;</span><br />
												<input type="radio" name="url_fix" id="url_fix0" value="rfc0" onclick="zUpdateStatus(\'0\');" class="input_radio"', ($context['personalizedBBC']['url_fix'] !== 'rfc1736' && $context['personalizedBBC']['url_fix'] !== 'rfc3986' ? ' checked="checked"' : ''), ' /> <label style="position:relative; bottom: 2px;" for="url_fix0">', $txt['PersonalizedBBC_UrlCheckDisable'], '</label><br />
												<input type="radio" name="url_fix" id="url_fix1" value="rfc3986x" onclick="zUpdateStatus(\'1\');" class="input_radio"', ($context['personalizedBBC']['url_fix'] === 'rfc3986x' ? ' checked="checked"' : ''), ' /> <label style="position:relative; bottom: 2px;" for="url_fix1">', $txt['PersonalizedBBC_UrlCheck3986x'], '</label><br />
												<input type="radio" name="url_fix" id="url_fix2" value="rfc3986" onclick="zUpdateStatus(\'2\');" class="input_radio"', ($context['personalizedBBC']['url_fix'] === 'rfc3986' ? ' checked="checked"' : ''), ' /> <label style="position:relative; bottom: 2px;" for="url_fix2">', $txt['PersonalizedBBC_UrlCheck3986'], '</label>
											</span>
										</td>
									</tr>
									<tr>
										<td style="width:2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagImage" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_image'], '
										</td>
										<td style="position:relative;padding-top: 1em;padding-bottom: 1em;vertical-align: bottom;">
											<select name="image[', $context['personalizedBBC']['current_name'], ']" id="opt" onchange="show_image()" style="display:block;position:relative;">';

	// Get all images for the dropdown list
	foreach ($context['PersonalizedBBC_Images'] as $image)
		echo '
												<option value="', $image, '"', ($context['personalizedBBC']['image'] === $image ? ' selected="selected"' : ''), '>
													', $image, '
												</option>';

	echo '
											</select>
										</td>
										<td style="padding-top: 1em;padding-bottom: 1em;left: 50%;position: absolute;">
											<span>
												<img id="icon" src="', $settings['default_theme_url'], '/images/bbc/personalizedBBC/', $context['personalizedBBC']['image'], '" style="max-height: 20px;max-width: 20px;border: 1px;position: relative;vertical-align: bottom;" alt="" />
											</span>
										</td>
									</tr>
									<tr>
										<td style="width:2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_tagImageUpload" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="width: 20%;">
											', $txt['personalizedBBC_imageUpload'], '
										</td>
										<td colspan="2">
											<input type="file" name="file" id="file" accept="image/' . (str_replace('.', '', $context['PersonalizedBBC_imageType'])) . '" />
										</td>
									</tr>
									<tr>
										<td colspan="4"><hr /></td>
									</tr>
								</table>
								<table>
									<tr>
										<td style="width:2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_membergroups" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="float: left;">
											&nbsp;&nbsp;', $txt['personalizedBBC_membergroups'], '
										</td>
									</tr>
								</table>
								<table style="width: 100%;margin-left: auto;margin-right: auto;">
									<tr>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td colspan="2" class="titlebg">
											', $txt['personalizedBBC_membergroups_view'], '
										</td>
										<td class="titlebg">
											', $txt['personalizedBBC_membergroups_use'], '
										</td>
									</tr>';
	foreach ($context['PersonalizedBBC_Membergroups'] as $gpId => $group)
	{
		echo '
									<tr>
										<td>&nbsp;</td>
										<td colspan="2">
											<input class="checkbox_view" type="checkbox" name="membergroups_view[', $gpId, ']" value="1"', (!empty($context['personalizedBBC']['permissions_view'][$gpId]['add_deny']) ? ' checked="checked"' : ''),' />
											<span style="left:1em;bottom:0.2em;position:relative;">', $group, '</span>
										</td>
										<td>
											<input class="checkbox_use" type="checkbox" name="membergroups_use[', $gpId, ']" value="1"', (!empty($context['personalizedBBC']['permissions_use'][$gpId]['add_deny']) ? ' checked="checked"' : ''),' />
											<span style="left:1em;bottom:0.2em;position:relative;">', $group, '</span>
										</td>
									</tr>';
	}
	echo '
									<tr>
										<td>&nbsp;</td>
										<td colspan="2">
											<input type="checkbox" id="membergroup_check_view" name="membergroup_view_check" onclick="checkToggle(\'view\');return true;" value="1" />
											<span style="left:1em;bottom:0.2em;position:relative;">', $txt['personalizedBBC_membergroups_check'], '</span>
										</td>
										<td>
											<input type="checkbox" id="membergroup_check_use" name="membergroup_use_check" onclick="checkToggle(\'use\');return true;" value="1" />
											<span style="left:1em;bottom:0.2em;position:relative;">', $txt['personalizedBBC_membergroups_check'], '</span>
										</td>
									</tr>
									<tr>
										<td colspan="4" style="width: 100%;"><hr /></td>
									</tr>
								</table>
								<table>
									<tr>
										<td style="width:2%;">
											<a href="', $scripturl, '?action=helpadmin;help=personalizedBBC_testBBC" onclick="return reqWin(this.href);" style="text-decoration:none;">
												<img style="vertical-align:middle;position:relative;bottom:1px;width:12px;height:12px;" src="' . $settings['default_theme_url'] . '/images/admin/personalizedBBC-help.gif" alt="?" />
											</a>
										</td>
										<td style="float:left;">
											&nbsp;&nbsp;', $txt['personalizedBBC_testBBC'], '
										</td>
									</tr>
								</table>
								<table style="border-spacing: 3em;margin-left: auto;margin-right: auto;">
									<tr>
										<td id="personalizedBBC_test_content" style="display: ', ((int)$context['personalizedBBC']['type'] == 3) ? 'none' : 'inline', ';">
											<span>', $txt['personalizedBBC_test_content'], '</span>
											<input type="text" name="bbcode_test_content" value="'. (!empty($_SESSION['bbcode_test_content']) ? $_SESSION['bbcode_test_content'] : '') .'" />
										</td>
										<td id="personalizedBBC_test_option1" style="display: ', ((int)$context['personalizedBBC']['type'] == 0 || (int)$context['personalizedBBC']['type'] == 3) ? 'none' : 'inline', ';">
											<span style="display: ', ((int)$context['personalizedBBC']['type'] == 2) ? 'none' : 'inline', ';" id="personalizedBBC_test_option_num">', $txt['personalizedBBC_test_option'], '</span>
											<span style="display: ', ((int)$context['personalizedBBC']['type'] == 1) ? 'none' : 'inline', ';" id="personalizedBBC_test_option_num1">', $txt['personalizedBBC_test_option1'], '</span>
											<input type="text" name="bbcode_test_option1" value="'. (!empty($_SESSION['bbcode_test_option1']) ? $_SESSION['bbcode_test_option1'] : '') .'" />
										</td>
										<td id="personalizedBBC_test_option2" style="display: ', ((int)$context['personalizedBBC']['type'] != 2) ? 'none' : 'inline', ';">
											<span>', $txt['personalizedBBC_test_option2'], '</span>
											<input type="text" name="bbcode_test_option2" value="'. (!empty($_SESSION['bbcode_test_option2']) ? $_SESSION['bbcode_test_option2'] : '') .'" />
										</td>
										<td style="width: 3rem;" id="personalizedBBC_index">
											<span style="display: flex;align-items: flex-end;">
												<input onmouseover="test_submit_bbc_over(this)" onmouseout="test_submit_bbc_out(this)" id="test_submit_bbc" style="padding: 0.3em;width: 3rem;height: 2rem;vertical-align: middle;filter: brightness(85%);" name="test_bbc" class="button_submit" type="submit" value="', $txt['personalizedBBC_test_button'], '" />
											</span>
										</td>
									</tr>
								</table>
								<table>
									<tr>
										<td style="width: 100%;">&nbsp;</td>
									</tr>
									<tr>
										<td style="overflow: hidden;width: 100%;height: 100%;padding-bottom: 15px;">
											<div style="display: ', (!empty($_SESSION['bbcode_test_string']) ? 'inline' : 'none') ,';">
												', (!empty($_SESSION['bbcode_test_string']) ? $_SESSION['bbcode_test_string'] : ''), '
											</div>
										</td>
									</tr>
								</table>
								<table style="border: 0px;border-spacing: 0px;padding: 4px;width: 100%;">
									<tr>
										<td style="width: 100%;"><hr /></td>
									</tr>
									<tr>
										<td style="width: 100%;">
											<input class="button_submit" type="submit" value="', $txt['personalizedBBC_submit'], '"', (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''), ' />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<span style="display:none;">
						<input type="text" name="current_name[', $context['personalizedBBC']['current_name'], ']" value="', $context['personalizedBBC']['current_name'], '" />
						<input type="text" name="list[', $context['personalizedBBC']['current_name'], ']" value="0" />
					</span>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
				<script type="text/javascript"><!-- // --><![CDATA[
					function show_image()
					{
						var myopt = document.getElementById("opt");
						var myimage = document.getElementById("icon");
						if (myopt.value !== "', $context['PersonalizedBBC_Images'][0], '")
						{
						    var image_url = "', $settings['default_theme_url'], '/images/bbc/personalizedBBC/" + myopt.value;
						    myimage.src = image_url;
						    myimage.style = "display:inline;max-height:20px;max-width:20px;";
						}
						else
						{
						    myimage.src = "', $settings['default_theme_url'], '/images/bbc/personalizedBBC/', $context['PersonalizedBBC_Images'][0],'";
						    myimage.style = "height:0px;width:0px;";
						}
					}
					function checkToggle(intent)
					{
						var toggle = document.getElementsByName("membergroup_" + intent);
						var field = document.getElementsByClassName("checkbox_" + intent);
						var checksall = document.getElementById("membergroup_check_" + intent);

						for (i = 0; i < field.length; i++)
						{
							if (checksall.checked == false)
								field[i].checked = false;
							else
								field[i].checked = true;
						}
					}
					function test_submit_bbc_over(test_submit)
					{
						test_submit.style.filter = "brightness(100%)";
						test_submit.style.cursor = "pointer";
						console.log("over");
					}
					function test_submit_bbc_out(test_submit)
					{
						test_submit.style.filter = "brightness(85%)";
						test_submit.style.cursor = "default";
						console.log("out");
					}
				// ]]></script>';
}

function template_PersonalizedBBC_Admin_below()
{
	echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}
?>