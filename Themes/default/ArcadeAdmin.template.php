<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_admin_main()
{
	global $context, $settings, $options, $txt, $arcadeModSettings, $arcade_version;

	echo '
	<div style="width: 49%" class="floatleft">
		<div class="cat_bar"', $context['arcade_smf_version'] == 'v2.1' ? ' style="clear: both;position: relative;bottom: -4px;"' : '', '>
			<h3 class="catbg">
				', $txt['arcade_latest_news'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="smalltext" id="arcade_news" style="overflow: auto; height: 18ex; padding: 0.5em;"><strong>', sprintf($txt['arcade_unable_to_connect'], 'web-develop.ca'), '</strong></div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<div style="width: 49%" class="floatright">
		<div class="cat_bar"', $context['arcade_smf_version'] == 'v2.1' ? ' style="clear: both;position: relative;bottom: -4px;"' : '', '>
			<h3 class="catbg">
				', $txt['arcade_status'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="smalltext" style="overflow: auto; height: 18ex; padding: 0.5em;">
				', $txt['arcade_installed_version'], ': <span id="arcade_installed_version">', $arcade_version, '</span><br />
				', $txt['arcade_latest_version'], ': <span id="arcade_latest_version">???</span>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<br style="clear: both" />
	<script>
		function setArcadeNews()
		{
			if (typeof(window.arcadeNews) == "undefined" || typeof(window.arcadeNews.length) == "undefined")
					return;

				var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

				for (var i = 0; i < window.arcadeNews.length; i++)
				{
					str += "\n	<div style=\"padding-bottom: 2px;\"><a href=\"" + window.arcadeNews[i].url + "\">" + window.arcadeNews[i].subject + "</a> on " + window.arcadeNews[i].time + "</div>";
					str += "\n	<div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
					str += "\n		" + window.arcadeNews[i].message;
					str += "\n	</div>";
				}

				setInnerHTML(document.getElementById("arcade_news"), str + "</div>");
		}

		function setArcadeVersion()
		{
			if (typeof(window.arcadeCurrentVersion) == "undefined")
				return;

			setInnerHTML(document.getElementById("arcade_latest_version"), window.arcadeCurrentVersion);
		}
		var arcadeVer = "', $arcade_version, '";
	</script>
	<script src="https://web-develop.ca/Themes/default/scripts/arcade_news.js?v=', urlencode($arcade_version), '.' . (str_repeat('0', rand(1,20))) . '" defer="defer"></script>
	<div style="padding-top: 20px;display: block;"><span><span style="display: none;">&nbsp;</span></span></div>';
}

function template_arcade_admin_maintenance()
{
	global $scripturl, $txt, $context, $settings;

	if ($context['maintenance_finished'])
		echo '
	<div id="arcade_maintenance">
		<div id="arcade_fadeout" style="opacity: 1;font-style: normal;">
			<div class="windowbg centertext" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
				<span id="arcade_fadeout_text1">', (!empty($context['arcadeFilesMax']) ? $context['maintenance_task'] . $txt['arcade_maintain_pending'] : $context['maintenance_task'] . $txt['arcade_maintain_done']), '</span>
				<span style="display: none;" id="arcade_fadeout_text2">', $txt['arcade_maintain_done'], '</span>
			</div>
		</div>
	</div>';

	/* removed but still available:
					<div style="padding-bottom: 1.25em;">
						<button onclick="arcade_commence_task(this.value)" class="arcade_task_button arcade_task_button-white arcade_task_button-animate" name="arcade_task" id="filefix" value="filefix" />
						<span class="arcade_task_span filefix">', $txt['arcade_maintenance_fileFix'], '</span></button>
					</div>
	*/
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['arcade_maintenance'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
			<div style="padding: 0.5em;">
				<div class="centertext titlebg" id="arcade_maintenance_tasks">';

	foreach ($context['arcade_mtasks'] as $buttonID) {
		if (in_array($buttonID, array('enhanceimages', 'retroLibraryGet'))) {
			continue;
		}
		echo '
					<div class="arcade_task_box" style="padding-bottom: 1.25em;">
						<button onclick="arcade_commence_task(this.value)" class="arcade_task_button arcade_task_button-bright arcade_task_button-animate" id="' . $buttonID . '" value="' . $buttonID . '">
							<span id="span_' . $buttonID . '" class="arcade_task_span">' . $txt['arcade_maintenance_' . $buttonID] . '</span>
							<span id="' . $buttonID . '_percent"></span>
						</button>
					</div>';
	}

	echo '
					<div id="arcade_commencing" style="padding-left: 5px;padding-top: 15px;display: none;opacity: 1;">
						' . $txt['arcade_maintain_configWait'] . '
					</div>
					<div id="arcade_commence_input" style="padding-left: 5px;padding-top: 15px;display: none;opacity: 1;">
						' . $txt['arcade_maintain_configContinue'] . '
						<div style="padding-left: 2em !important;display: inline !important;">
							<input id="arcade_maintenance_submit" class="button" type="submit" name="save_settings" value="' . $txt['arcade_maintain_configInput'] . '" onclick="arcade_commence_refresh()" />
						</div>
					</div>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</div>
		<span class="botslice"><span></span></span>
	</div>
	<script type="text/javascript">
		var arc_timer = 5;
		var arcadeNewContinue = "' . (abs(isset($_REQUEST['count']) ? intval($_REQUEST['count']) + intval($context['arcadeFilesMax']) : 0)) . '";
		if (typeof arcadeFileMaintenanceContinue !== "undefined")
		{
			arcadeFileMaintenanceContinue = arcadeNewContinue != 0 ? arcadeNewContinue : parseInt(arcadeFileMaintenanceContinue);
			if (arcadeFileMaintenanceContinue)
				setTimeout(function(){document.getElementById("arcade_commence_input").style.display = "block";}, 2000);
		}
		if (arcadeNewContinue > 0 && ' . (abs(isset($_REQUEST['count']) ? intval($_REQUEST['count']) : 0)) . ' >= ' . (!empty($context['arcadeFilesCount']) ? (int)$context['arcadeFilesCount'] : 0) . ')
		{
			document.getElementById("arcade_fadeout_text1").style.display = "none";
			document.getElementById("arcade_fadeout_text2").style.display = "block";
		}
		function arcade_commence_task(taskval)
		{
			window.location.href = "' . $scripturl. '?action=admin;area=arcademaintenance;maintenance=" + taskval + ";' . $context['session_var'] . '=' . $context['session_id'] . ';#arcade_maintenance";
			return false;
		}
		function arcade_commence_refresh()
		{
			if (arcadeFileMaintenanceContinue < 1) {
				document.getElementById("arcade_commence_input").style.display = "none";
				document.getElementById("arcade_fadeout_text1").style.display = "none";
				document.getElementById("arcade_fadeout_text2").style.display = "block";
				return false;
			}
			window.location.href = "' . $scripturl . '?action=admin;area=arcademaintenance;maintenance=configfix;count=" + arcadeFileMaintenanceContinue + ";confirm=1;' . $context['session_var'] . '=' . $context['session_id'] . ';";
			return false;
		}
		function arcade_objectContains(arcObject, arcAction) {
			if (arcObject.some(({name}) => name === arcAction))
				return true;
			else
				return false;
		}
		function arcade_commencing()
		{
			var gameStart = ' . (isset($context['arcade_gameStart']) ? intval($context['arcade_gameStart']) : 0) . ';
			var gameFinish = ' . (isset($context['arcade_gameFinish']) ? intval($context['arcade_gameFinish']) : 0) . ';
			var adminArcActionObject = [{name: "htmlfilefix"}, {name: "filefix"}, {name: "configfix"}];
			var adminArcActionRequest = "' . (isset($_REQUEST['maintenance']) ? preg_replace('/[^a-zA-Z0-9]/', '' , $_REQUEST['maintenance']) : '') . '";
			var adminArcAction = adminArcActionRequest && arcade_objectContains(adminArcActionObject, adminArcActionRequest) ? true : false;
			if (gameStart > 0 && gameFinish > 0 && gameStart <= gameFinish && adminArcAction) {
				var gamePercentage = ' . (!empty($context['arcade_gameCurrentPercent']) ? $context['arcade_gameCurrentPercent'] : 0) . ';
				var timeleft = 5, arcWaitTime;
				document.getElementById("arcade_maintenance_tasks").innerHTML = gamePercentage + "% complete.  Commencing in " + timeleft + " seconds.";
				var arcadeCommenceTimer = setInterval(function(){
					if(timeleft <= 0){
						clearInterval(arcadeCommenceTimer);
						window.location.href = "' . $scripturl . '?action=admin;area=arcademaintenance;maintenance=" + adminArcActionRequest + ";confirm=1;start=" + gameStart + ";finish=" + gameFinish + ";' . $context['session_var'] . '=' . $context['session_id'] . ';";
					} else {
						arcWaitTime = "' . $txt['arcade_maintenance_waitTime'] . '".replace(/\\%s/, gamePercentage);
						document.getElementById("arcade_maintenance_tasks").innerHTML = arcWaitTime.replace(/\\%s/, timeleft);
					}
					timeleft -= 1;
				}, 1000);
			}
			else if (adminArcAction) {
				document.getElementById("arcade_commencing").style.display = "block";
				document.getElementById("arcade_commencing").style.opacity = "1";
				document.getElementById("arcade_commencing").innerHTML = "' . $txt['arcade_maintain_configFinished'] . '";
				setTimeout(function(){document.getElementById("arcade_commencing").style.opacity = "0";}, 2000);
			}
			return false;
		}
		$(document).ready(function() {
			arcade_commencing();
		});
	</script>';
}

function template_arcade_admin_maintenance_cronjobs()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings;


	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=cronjobs" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcadeCronScheduleText'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div style="padding: 0.5em;">
					<div style="padding-bottom: 1.5rem;">
						<select name="arcadeCronBiosSchedule" id="arcadeCronBiosSchedule" size="0" style="min-width: 5rem;">';
	foreach ($context['arcadeCronBiosSchedule'] as $key => $option) {
		echo '
							<option value="' . $key .'"' . ($key == $context['arcade_bios_schedule'] ? ' selected' : '') . '>' . $option . '</option>';
	}

	echo '
						</select>
						<label style="padding-left: 5px;" id="arclabel1" for="arcadeCronBiosSchedule">' . $txt['arcadeCronBiosSchedule'] . '</label>
					</div>
					<div style="padding-bottom: 3px;">
						<select name="arcadeCronSchedule" id="arcadeCronSchedule" size="0" style="min-width: 5rem;">';
	foreach ($context['arcadeCronSchedule'] as $key => $option) {
		echo '
							<option value="' . $key .'"' . ($key == $context['arcade_schedule'] ? ' selected' : '') . '>' . $option . '</option>';
	}

	echo '
						</select>
						<label style="padding-left: 5px;" id="arclabel2" for="arcadeCronSchedule">' . $txt['arcadeCronSchedule'] . '</label>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="checkbox" name="cron_game_download" value="1" ' . (!empty($arcadeModSettings['cron_game_download']) ? 'checked ' : '') . '/><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cron_download'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="checkbox" name="cron_emulator_archives" value="1" ' . (!empty($arcadeModSettings['cron_emulator_archives']) ? 'checked ' : '') . '/><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cron_emulator_archives'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="checkbox" name="cron_game_upload" value="1" ' . (!empty($arcadeModSettings['cron_game_upload']) ? 'checked ' : '') . '/><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cron_upload'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="checkbox" name="cron_game_romupload" value="1" ' . (!empty($arcadeModSettings['cron_game_romupload']) ? 'checked ' : '') . '/><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cron_romupload'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="checkbox" name="cron_game_tempbios" value="1" ' . (!empty($arcadeModSettings['cron_game_tempbios']) ? 'checked ' : '') . '/><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cron_tempbios'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<a id="setting_arcadeCronSchedule_help" href="https://localhost/smf21_site07/index.php?action=helpadmin;help=arcadeCronScheduleTextHelp" onclick="return reqOverlayDiv(this.href);"><span class="main_icons help" title="Help"></span></a><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">' . $txt['arcade_help'] . '</span>
					</div>
					<div style="margin: 1ex;text-align: right;">
						<input class="button_submit" type="submit" name="change_cronjobs" value="' . $txt['arcade_change_cronjobs'] . '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>
	<script>
		$(document).ready(function(){
			let w1 = parseInt($("#arcadeCronSchedule").width()), w2 = parseInt($("#arcadeCronBiosSchedule").width());
			if (w1 > w2) {
				$("#arcadeCronBiosSchedule").width(w1);
			}
			else {
				$("#arcadeCronSchedule").width(w2);
			}
		});
	</script>';
}

function template_arcade_admin_maintenance_highscore()
{
	global $scripturl, $txt, $context, $settings;


	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=highscore" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_highscore'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div style="padding: 0.5em;">
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="score_action" value="older" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_remove_scores_older_than'], ' <input name="age" value="30" /> ', $txt['arcade_remove_scores_days'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="score_action" value="all" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_remove_all_scores'], '</span>
					</div>
					<div style="margin: 1ex;text-align: right;">
						<input class="button_submit" type="submit" name="clear_score" value="', $txt['arcade_remove_now'], '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcade_admin_maintenance_category()
{
	global $scripturl, $txt, $context, $settings;

	if ((!empty($_REQUEST['maintenance'])) && $_REQUEST['maintenance'] == 'done')
		echo '
	<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
		', $txt['arcade_maintain_done'], '
	</div>';

	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=category" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_category'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div style="padding: 0.5em;">
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="cat_action" value="undefault" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cats_undefault'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="cat_action" value="default" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cats_default'], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="cat_action" value="peruse" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_cats_peruse'], '</span>
					</div>
					<div style="padding-bottom: 3px;padding-top: 15px;">
						<span style="padding-right: 5px;">', $txt['arcade_admin_opt_cat_title'], '</span>
						', ArcadeAdminCategoryDropdown(), '
					</div>
					<div style="margin: 1ex;text-align: right;">
						<input class="button_submit" type="submit" name="clear_score" value="', $txt['arcade_commence_now'], '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcade_admin_category_list()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcadecategory;save" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_categories'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div style="padding: 0.5em;">
				<div class="centertext" style="border: 0px;width: 100%;">
					<div style="display: table-row;width: 100%;">
						<span class="centertext" style="display: table-cell;padding: 4px;vertical-align: top;width: 15%;margin-top: 5px;border-bottom: 1px solid;">
							', $txt['arcade_cat_delete'], '
						</span>
						<span style="display: table-cell;width: 25%;text-align: left;vertical-align: top;margin-top: 5px;padding: 4px;border-bottom: 1px solid;">
							', $txt['arcade_cat_order'], '
						</span>
						<span style="display: table-cell;width: 50%;padding: 4px;vertical-align: top;text-align: left;padding-left: 20px;border-bottom: 1px solid;">
							', $txt['arcade_cat_name'], '
						</span>
						<span style="display: table-cell;width: 10%;padding: 4px;vertical-align: top;text-align: left;text-align: center;border-bottom: 1px solid;">
							', $txt['arcade_cat_image'], '
						</span>
					</div>';

	foreach ($context['arcade_category'] as $category)
	{
		echo '
					<div style="display: table-row;width: 100%;">
						<span class="centertext" style="display: table-cell;padding: 4px;vertical-align: top;width: 15%;margin-top: 5px;">
							<input id="cat', $category['id'], '" type="checkbox" name="category[', $category['id'], ']" value="', $category['id'], '" style="check" />
						</span>
						<span style="display: table-cell;width: 25%;text-align: left;vertical-align: top;margin-top: 5px;padding: 4px;">
							<input type="text" name="category_order[', $category['id'], ']" value="', $category['order'], '" style="width: 100%;" />
						</span>
						<span style="display: table-cell;width: 50%;padding: 4px;vertical-align: top;text-align: left;padding-left: 20px;">
							<a href="', $category['href'], '">', $category['name'], '</a>
						</span>
						<span style="display: table-cell;width: 10%;padding: 4px;vertical-align: top;text-align: left;text-align: center;">
							<img src="', $settings['default_theme_url'], '/images/arc_icons/', $category['cat_icon'], '" style="width: 16px; height: 16px;" alt="', $txt['arcade_cat_image_na'], '" />
						</span>
					</div>';
	}

	echo '
				</div>
				<div style="padding-left: 5px;padding-top: 15px;"><input class="button_submit" type="submit" name="save_settings" value="', $txt['arcade_save_category'], '" /></div>
			</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_arcade_admin_category_edit()
{
	global $scripturl, $txt, $context, $arcadeModSettings, $settings;

	$smfVersion = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';
	$checked = $smfVersion == 'v2.1' ? 'checked ' : 'checked="checked" ';
	echo '
	<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>
	<div class="cat_bar" style="clear: both;position: relative;', $context['arcade_smf_version'] == 'v2.1' ? 'top: 4px;' : '', '">
		<h3 class="catbg">
			', $txt['arcade_categories'], '
		</h3>
	</div>
		', $context['arcade_smf_version'] == 'v2.1' ? '
	<div class="arcade_up_contain windowbg" style="padding: 0px;border: 0px;">' : '
	<span class="clear upperframe"><span>&nbsp;</span></span>
	<div class="roundframe">', '
		<form id="upload_form" action="', $scripturl, '?action=admin;area=arcadecategory;sa=upload" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
			<input type="hidden" name="upcat" value="', $context['category']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<div style="padding: 0.5em;">
				<div style="padding-bottom: 7px;"><span style="display: none;">&nbsp;</span></div>
				<div style="display: table;width: 100%;border: 0px;">
					<div style="display: table-row;">
						<div style="display: table-cell;width: 20%;">
							<div style="text-align: left;padding-left: 10px;">', $txt['arcade_upload_cat'], '</div>
						</div>
						<div class="upcontainer" style="display: table-cell;width: 20%;text-align: left;position: relative;padding-left: 6px;">
							<input style="clear: both;width: 200px;" accept=".png, .gif, .jpg" type="file" size="48" name="attachment[]" />
						</div>', !empty($_SESSION['arcade_cat_icon']) ? '
						<div class="centertext" style="display: table-cell;width: 20%;">' . $txt['arcade_cat_image_icon'] . '&nbsp;
							<img alt="?" src="' . $settings['default_images_url'] . '/arc_icons/' . $_SESSION['arcade_cat_icon'] . '" style="height: 20px;width: 20px;vertical-align: middle;" class="icon" />
						</div>
						<div class="centertext" style="display: table-cell;width: 20%;">' . $txt['arcade_cat_image_filename'] . '&nbsp;
							<span>' . $_SESSION['arcade_cat_icon'] . '</span>
						</div>' : '
						<div style="display: table-cell;width: 20%;">
							<span style="display: none;">&nbsp;</span>
						</div>
						<div style="display: table-cell;width: 20%;">
							<span style="display: none;">&nbsp;</span>
						</div>', '
						<div style="display: table-cell;width: 20%;padding-right: 10px;">
							<input class="button_submit" type="submit" name="upload" value="', $txt['arcade_upload_button'], '" />
						</div>
					</div>
					<div style="display: table-row;">
						<div class="alert" style="display: table-cell;padding-left: 10px;">
							', !empty($context['arcade_cat_message']) ? '<span>' . $context['arcade_cat_message'] . '</span>' : '<span style="display: none;">&nbsp;</span>', '
						</div>
					</div>
				</div>
			</div>
		</form>
		<div style="padding-top: 20px;"><span style="display: none;">&nbsp;</span></div>
		<form name="category" action="', $scripturl, '?action=admin;area=arcadecategory;sa=save" method="post">
			<input type="hidden" name="category" value="', $context['category']['id'], '" />
			<div>
				<div style="padding: 0.5em;">
					<div style="border: 0px;width: 100%;display: table;" class="centertext">
						<div style="display: table-row;">
							<span style="text-align:left;display: table-cell;padding: 4px;width: 20%;">', $txt['category_name'], '</span>
							<span style="display: table-cell;padding: 20px 4px 4px 4px;width: 80%;text-align: left;">
								<input maxlength="120" ' . (!empty($_SESSION['arcade_isMobile']) ? 'style="width: 200px;overflow: hidden;" size="50"' : 'style="width: 400px;overflow: hidden;" size="120"') . ' type="text" name="category_name" value="', $context['category']['name'], '" />
							</span>
						</div>
						<div style="display: table-row;">
							<span style="text-align:left;display: table-cell;padding: 4px;width: 20%;">', $txt['arcade_cat_disable_dl'], '</span>
							<span style="display: table-cell;padding: 4px 4px 4px 4px;width: 80%;text-align: left;">
								<input type="checkbox" name="category_disable_dl" value="1" ' . (!empty($context['category']['disable_dl']) ? $checked : '') . '/>
							</span>
						</div>
						<div style="display: table-row;">
							<span style="text-align:left;display: table-cell;padding: 4px;width: 20%;">', $txt['arcade_cat_js_insert'], '</span>
							<span style="display: table-cell;padding: 4px 4px 4px 4px;width: 80%;text-align: left;">
								<input type="checkbox" name="category_js_insert" value="1" ' . (!empty($context['category']['js_insert']) ? $checked : '') . '/>
							</span>
						</div>
						<div style="display: table-row;">
							<span style="text-align:left;display: table-cell;width: 20%;padding: 4px;">', $txt['arcade_category_permission_allowed'], '</span>
							<span style="display: table-cell;padding: 20px 4px 4px 4px;width: 60%;">';

	foreach ($context['groups'] as $group)
		echo '
								<label for="groups_', $group['id'], '">
									<input style="display: inline;" type="checkbox" name="groups[]" value="', $group['id'], '" id="groups_', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '', ' class="check" />
									<span', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['pgroups_post_group'] . '"' : '', '>', $group['name'], '</span>
								</label>';

	echo '
								<span style="display: block;padding-top: 30px;padding-bottom: 15px;"><i>', $txt['check_all'], '</i> <input type="checkbox" onclick="invertAll(this, this.form, \'groups[]\');" class="check" /></span>
							</span>
						</div>
					</div>
					<input class="button_submit" type="submit" name="save_settings" value="', $txt['arcade_save_category'], '" />
					<div style="padding-bottom: 33px;"><span style="display: none;">&nbsp;</span></div>
				</div>
			</div>
			<input type="hidden" name="category_icon" value="', !empty($_SESSION['arcade_cat_icon']) ? $_SESSION['arcade_cat_icon'] : $context['category']['cat_icon'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<span class="lowerframe"><span>&nbsp;</span></span>
	<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>';
}

function template_arcadeadmin_above()
{
	global $scripturl, $txt, $arcadeModSettings, $context, $settings, $arcade_version;
}

function template_arcadeadmin_below()
{
	global $txt;

	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		' . $txt['pdl_arcade_copyright'] . '
	</div>';

}

function template_arcade_admin_maintenance_xframe()
{
	global $scripturl, $txt, $context, $settings;

	if ($context['maintenance_finished'])
		echo '
	<div id="arcade_fadeout" style="opacity: 1;">
		<div class="windowbg centertext" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
			<span>', sprintf($txt['arcade_xframe_file_task'], $context['maintenance_file'], $context['maintenance_task']), ' ~ ', $txt['arcade_maintain_done'], '</span>
		</div>
	</div>';

	if ($context['arcade_xframe_instruct'] !== 'display')
		echo '
	<form name="category" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=xframe" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_xframe'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div class="centertext" style="padding-top: 5px;padding-bottom: 15px;">', sprintf($txt['arcade_xframe_detected'], $context['arcade_server_software']), '</div>
				<div style="padding-bottom: 5px;">', sprintf($txt['arcade_xframe_config'], $context['arcade_server_title']), '</div>
				<div style="padding-bottom: 25px;">', $txt['arcade_xframe_remove'], '</div>
				<div style="padding: 0.5em;">
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="create_xframe" value="1" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', (stripos($context['arcade_server_software'], 'apache') !== FALSE ? $txt['arcade_xframe_create'][0] : $txt['arcade_xframe_create_litespeed']), '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="create_xframe" value="2" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_xframe_create'][1], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="create_xframe" value="3" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $txt['arcade_xframe_create'][2], '</span>
					</div>
					<div style="padding-bottom: 3px;">
						<input style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="create_xframe" value="4" /><span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', sprintf($txt['arcade_xframe_create'][3], $context['arcade_filepath']), '</span>
					</div>
					<div style="margin: 1ex;text-align: right;">
						<input class="button_submit" type="submit" name="select" value="', $txt['arcade_xframe_option'], '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
	else
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_xframe'], '
			</h3>
		</div>
		<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div style="padding: 0.5em;">',
					$txt['arcade_xframe_instruct'], '
				</div>
			<span class="botslice"><span></span></span>
			<form style="display: inline;" action="', $scripturl . '?action=admin;area=arcademaintenance;sa=xframe;" type="POST">
				<div id="xframe_arcade" class="centertext" style="padding-top: 15px;"><input class="button1 button_submit" type="reset" onclick="location.href=\'', $scripturl . '?action=admin;area=arcademaintenance;sa=xframe;\'" value="', $txt['arcade_xframe_exit'], '" /></div>
			</form>
			<div style="padding-top: 10px;"><span></span></div>
		</div>
		<div style="padding-bottom: 10px;"><span></span></div>';
}

function template_arcade_admin_maintenance_db_engine()
{
	global $scripturl, $txt, $context, $settings;

	$x = 1;
	if ($context['maintenance_finished']) {
		echo '
	<div id="arcade_fadeout" style="opacity: 1;">
		<div class="windowbg centertext" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
			<span>', sprintf($txt['arcade_engine_task'], $context['maintenance_engine']), ' ~ ', $txt['arcade_maintain_done'], '</span>
		</div>
	</div>
	<script>
		$(document).ready(function() {
			$("#arcade_engine").text("' . sprintf($txt['arcade_engine_detected'], $context['maintenance_engine']) . '");
			$(\'input[name="change_engine]\').prop("checked", false);
			$(\'input[data-name="' . $context['maintenance_engine'] . '"]\').prop("checked", true);
		});
	</script>';
	}

	echo '
	<form id="engine_form" name="engine" action="', $scripturl, '?action=admin;area=arcademaintenance;sa=engine" method="post">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['arcade_maintenance'], ' - ', $txt['arcade_maintenance_engine'], '
			</h3>
		</div>
		<div id="engine_set" class="windowbg" style="width: 100%;position: relative;">
			<span class="topslice"><span></span></span>
				<div class="centertext" style="position: relative;width: 100%;padding-bottom: 2rem;">
					<div style="display: flex;flex-direction: column;justify-content: space-around;position: relative;width: 100%;align-items: center;padding: 0rem 10rem 0rem 10rem;">
						<div style="display: flex;flex-direction: row;width: 100%;">
							<div style="flex: 1;padding: 0.2rem;border: 1px solid;max-width: 15%;">', $txt['arcade_db_engine_headings'][0], '</div>
							<div style="padding: 0.2rem;border: 1px solid;max-width: 85%;min-width: 85%;text-align: left;text-indent: 0.1rem;flex-wrap: wrap;">
								<div>', sprintf($txt['arcade_db_engine_detected'], $context['arcade_db_engine']), '</div>
							</div>
						</div>
						<div style="display: flex;flex-direction: row;width: 100%;">
							<div style="flex: 1;padding: 0.2rem;border: 1px solid;max-width: 15%;">', $txt['arcade_db_engine_headings'][1], '</div>
							<div style="padding: 0.2rem;border: 1px solid;max-width: 85%;min-width: 85%;text-align: left;text-indent: 0.1rem;flex-wrap: wrap;">
								<div>', sprintf($txt['arcade_smf_engine_detected'], $context['arcade_smf_db_engine']), '</div>
							</div>
						</div>
						<div style="display: flex;flex-direction: row;width: 100%;">
							<div style="flex: 1;padding: 0.2rem;border: 1px solid;max-width: 15%;">', $txt['arcade_db_engine_headings'][2], '</div>
							<div style="padding: 0.2rem;border: 1px solid;max-width: 85%;min-width: 85%;text-align: left;text-indent: 0.1rem;flex-wrap: wrap;">
								<div id="arcade_engine">', sprintf($txt['arcade_engine_detected'], $context['arcade_maintenance_engine']), '</div>
							</div>
						</div>
					</div>
				</div>
				<div style="padding-bottom: 5px;">', $txt['arcade_engine_config'], '</div>
				<div style="padding: 1rem 0rem 1rem 0rem;">', $txt['arcade_engine_only'], '</div>
				<div style="padding: 0.5em;">';
	foreach ($context['arcade_db_engines'] as $engine) {
		echo '
					<div style="padding-bottom: 3px;">
						<input data-name="' . $engine . '" style="vertical-align: middle;margin: 5px 5px;" class="icon" type="radio" name="change_engine" value="' . $x . '"' . ($context['arcade_maintenance_engine'] == $engine ? ' checked' : '') . '>
						<input type="hidden" name="engine_' . $x . '" id="db_engine_' . $x . '" value="' . $engine. '">
						<span style="vertical-align: middle;padding: 2px 0px 2px 5px;">', $engine, '</span>
					</div>';
		$x++;
	}

	echo '
					<div style="margin: 1ex;text-align: right;">
						<input id="engine_submit" class="button_submit" type="submit" name="select" value="', $txt['arcade_engine_option'], '" />
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>

		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

function template_manage_maintenance_retro_arch_upload()
{
	global $scripturl, $context, $txt, $boardurl, $boarddir, $arcadeModSettings, $settings, $sourcedir;
	echo '
	<div class="centertext">
		<div class="cat_bar uptitle">
			<h3 class="catbg">
				', sprintf($txt['arcade_retro_arch_update'], $context['emulator_type_name']), '
			</h3>
		</div>
		<div class="windowbg2 upcontainer">
			<span class="topslice"><span>&nbsp;</span></span>
			<div style="padding: 0.5em;" id="arcade_container">
				<div style="padding: 2px 0px 15px 0px;font-family: tahoma;" class="mediumtext">
					<span class="emulatorCoreTextBoxes">' . $txt['arcade_retro_arch_filetypes'] . '</span>
				</div>
				<hr />
				<div style="padding-top: 2em;"><span></span></div>
				<div id="arch_retrieval" class="centertext" style="display: none;">
					<div class="timer" onload="timer(1800)">
						<div class="emulatorCoreTextBoxesSmall">' . $txt['arcade_upload_retro_archive'] . '</div>
						<div style="padding-top: 2em;"><span></span></div>
						<div id="time" class="time">
							<img alt="" style="width: 50px;height: 50px;" src="' . $settings['default_theme_url'] . '/images/arc_icons/file_downloading.gif" />
						</div>
					</div>
				</div>
				<div id="arc_file_spinner" style="padding: 1rem;display: none;">
					<img alt="" style="width: 50px;height: 50px;" src="' . $settings['default_theme_url'] . '/images/arc_icons/file_downloading.gif" />
				</div>
				<form id="external_form" action="' . $scripturl . '?action=admin;area=arcademaintenance;sa=arch_external;emulator=' . $_SESSION['emulator_admin'] . '" method="post" enctype="multipart/form-data" accept-charset="' . $context['character_set'] . '">
					<div style="display: none;" id="romfiledetected">
						<div><input id="core_merge_install" class="emulatorCoreInput" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="arch_core_detected()" type="button" value="' . sprintf($txt['arcade_core_file_detected'], $context['emulator_type_name']) . '" /></div>
						<div style="padding-top: 2em;"><span></span></div>
						<div><input id="core_merge_delete" class="emulatorCoreInput" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="arch_core_delete()" type="button" value="' . sprintf($txt['arcade_core_delete'], $context['emulator_type_name']) . '" /></div>
					</div>
					<div style="display: block;" id="noromfiledetected">
						<div><input class="emulatorCoreInput" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="arch_external_form_github()" type="button" value="' . sprintf($txt['arcade_arch_external_github'], $context['emulator_type_name']) . '" /></div>
						<div style="padding-top: 2em;"><span></span></div>
						<div><input class="emulatorCoreInput" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="arch_external_form()" type="button" value="' . sprintf($txt['arcade_arch_external'], $context['emulator_type_name']) . '" /></div>
						' . (!empty($context['arcade_emulator_detected']) ? '<div style="padding-top: 2em;"><span></span></div>
						<div><input class="emulatorCoreInput" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="emulator_core_removal()" type="button" value="' . sprintf($txt['arcade_emulator_del_core'], $context['emulator_type_name']) . '" /></div>' : '') . '
					</div>
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
					<input type="hidden" id="override2" name="override2" value="0" />
				</form>
				<div id="arch_option" class="emulatorCoreTextBoxes" style="padding-top: 2rem;">' . $txt['arcade_ruffle_option'] . '</div>
				<div style="padding-top: 2em;"><span></span></div>
				<form id="remoteEmualtorJS" style="margin-bottom: 2rem;">
					<div class="emulatorCoreContainer">
						<div class="emulatorCoreList" id="emulatorCoreList"></div>
						<input class="emulatorCoreInput emulatorCoreBrowse" type="button" id="pick" value="' . $txt['arcadeRomCoreBrowse'] . '" />
					</div>
				</form>
				<div class="centertext" style="padding-top: 2rem;font-size: x-small;text-shadow: white 0px 0px 10px;stroke: color-mix(in lch, var(--primaryColor) 35%, gray 15%));">
					<span id="emulator_remote_version">' . (!empty($context['arcade_emulator_remote_version']) ? sprintf($txt['arcade_emulator_remote_file_date'], $context['arcade_emulator_remote_version']) : '') . '</span>
				</div>
				<div class="centertext" style="padding-top: 0.4rem;">
					<span id="emulator_version">' . (!empty ($context['arcade_emulator_version']) ? sprintf($txt['arcade_emulator_current_file_date'], $context['arcade_emulator_version']) : '') . '</span>
				</div>' . (!empty($_SESSION['emulator_admin']) && stripos($_SESSION['emulator_admin'], 'ruffle') === FALSE && !empty($arcadeModSettings['arcadeRetroLibraryCodeEnable']) ? '
				<div style="padding: 0.2rem;width: 100%;" class="centertext">
					<img class="icon" id="ejsIoChip" src="' . $settings['default_theme_url'] . '/images/arc_icons/io_chip.png" alt="" />
				</div>' : '');

	echo '
			</div>
			<span class="botslice"><span>&nbsp;</span></span>
			<div id="emulatorfilemismatch" style="display: none;"></div>
		</div>
	</div>';
}

function template_manage_maintenance_arch_replace()
{
	global $context, $txt;

	echo '
	<script type="text/javascript">
		if (document.getElementById("arch_retrieval"))
			document.getElementById("arch_retrieval").style.display = "none";
	</script>
	<div class="centertext">
		<div class="timer" onload="timer(1800)">
			<div>' . $context['arch_message'] . '</div>
			<div class="time">
				<strong><span id="time">' . $txt['arcade_upload_arch_processing'] . '</span></strong>
		  </div>
		</div>
	</div>';
}

?>