<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_manage_ruffle_upload()
{
	global $scripturl, $context, $txt, $boardurl, $boarddir, $arcadeModSettings, $settings, $sourcedir;

	echo '
	<div class="centertext">
		<div class="cat_bar uptitle">
			<h3 class="catbg">
				', $txt['arcade_ruffle_upload'], '
			</h3>
		</div>
		<div class="windowbg2 upcontainer">
			<span class="topslice"><span>&nbsp;</span></span>
			<div style="padding: 0.5em;" id="arcade_container">
				<div style="padding: 2px 0px 15px 0px;font-family: tahoma;" class="mediumtext">
					<span style="border: 1px solid;padding: 2px;">' . $txt['arcade_supported_filetypes_ruffle'] . '</span>
				</div>
				<hr />
				<div style="padding-top: 2em;"><span></span></div>
				<div id="ruffle_retrieval" class="centertext" style="display: none;">
					<div class="timer" onload="timer(1800)">
						<div>' . $txt['arcade_upload_ruffle_archive'] . '</div>
						<div style="padding-top: 2em;"><span></span></div>
						<div class="time">
							<img alt="" style="width: 50px;height: 50px;" src="' . $settings['default_theme_url'] . '/images/arc_icons/file_downloading.gif" />
						</div>
					</div>
				</div>
				<form id="external_form" action="' . $scripturl . '?action=admin;area=arcademaintenance;sa=ruffle_external1" method="post" enctype="multipart/form-data" accept-charset="' . $context['character_set'] . '">
					<div>
						<input class="button_submit" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="ruffle_external_form_github()" type="button" value="' . $txt['arcade_ruffle_external_github'] . '" />
					</div>
					<div style="padding-top: 2em;"><span></span></div>
					<div>
						<input class="button_submit" style="text-decoration: none;margin: 2px 1px;cursor: pointer;border: 1px solid;font-family:\'Roboto\',sans-serif;font-weight:300;" onclick="ruffle_external_form()" type="button" value="' . $txt['arcade_ruffle_external'] . '" />
					</div>
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
					<input type="hidden" id="override2" name="override2" value="0" />
				</form>
				<div id="ruffle_option" style="padding: 2em;font-family: Verdana;font-weight: 800;">' . $txt['arcade_ruffle_option'] . '</div>
				<form id="upload_form" action="' . $scripturl . '?action=admin;area=arcademaintenance;sa=ruffle_upload2" method="post" enctype="multipart/form-data" accept-charset="' . $context['character_set'] . '">
					<div>
						<input style="text-decoration: none;margin: 2px 1px;cursor: pointer;font-family:\'Roboto\',sans-serif;font-weight:300;" accept=".zip, .gz, .ZIP, .GZ" type="file" size="48" name="attachment[]" />
					</div>
					<div style="padding-top: 2em;"><span></span></div>
					<div style="padding: 4px 0px 4px 0px;" class="smalltext">' . $txt['post_max_size'] . ' ' . $context['post_max_size'] . ' MB</div>
					<div style="vertical-align: middle;margin: 0 auto;">
						<input style="vertical-align: middle;" type="checkbox" id="override" name="override" value="1" />
						<label style="font-size: x-small;font-weight: bold;font-family: Calibri;padding-right: 1em;" for="override"> ' . $txt['arcade_ruffle_override'] . ' </label>
						<input class="button_submit" type="submit" onclick="ruffle_upload_form()" name="upload" value="' . $txt['arcade_upload_button'] . '" />
					</div>
					<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
				</form>';

	if (!empty($arcadeModSettings['arcadeRuffleVersion']) && $arcadeModSettings['arcadeRuffleVersion'] != '2010.01.01')
		echo '
				<div style="padding-top: 2em;"><span>' . sprintf($txt['arcade_ruffle_current_file_date'], $arcadeModSettings['arcadeRuffleVersion']) . '</span></div>';
	else
		echo '
				<div style="padding-top: 2em;"><span>' . $txt['arcade_ruffle_no_file_date'] . '</span></div>';

	echo '
			</div>
			<span class="botslice"><span>&nbsp;</span></span>
		</div>
	</div>';
}

function template_manage_ruffle_replace()
{
	global $context, $txt;

	echo '
	<script type="text/javascript">
		if (document.getElementById("ruffle_retrieval"))
			document.getElementById("ruffle_retrieval").style.display = "none";
	</script>
	<div class="centertext">
		<div class="timer" onload="timer(1800)">
			<div>' . $context['ruffle_message'] . '</div>
			<div class="time">
				<strong><span id="time">' . $txt['arcade_upload_ruffle_processing'] . '</span></strong>
		  </div>
		</div>
	</div>';
}

?>