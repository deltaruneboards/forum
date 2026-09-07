<?php
/**
 * SMF Arcade Mobile light Skin
 *
 * @package SMF Arcade
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_above()
{
	global $scripturl, $txt, $context, $settings, $options, $arcadeModSettings, $modSettings, $options, $user_info, $settings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$romManage = !empty($_SESSION['arcade_rom_initiate']) ? 'rom' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$hookCheck = !empty($modSettings['integrate_pre_log_stats']) ? explode(',', $modSettings['integrate_pre_log_stats']) : array();
	$categories = ArcadeCats($_SESSION['current_cat'], $rom);
/*					var imageAlt = image[0].alt;
					document.getElementsByClassName("forumtitle")[0].innerHTML = imageAlt;*/
	echo '
	<script>
		function arcadeSkinStyles()
		{' . (!empty($modSettings['sp_portal_mode']) && in_array('EhPortal_log_stats', $hookCheck) ? '
			$("#main_content_section").css("all", "unset");' : '') . '
			if (!($("#footer").hasClass("centertext"))) {
				$("#footer").addClass("centertext");
			}
			$("#footer").css("min-width", "100%");
			$("#footer > div").css("left", "0");
			$("#footer > div").css("right", "0");
			$("#footer > div").css("min-width", "100%");
			$("#footer > div > ul").css("padding-left", "0rem");
			if (document.getElementsByClassName("frame") && document.getElementsByClassName("frame")[0])
				document.getElementsByClassName("frame")[0].addClass("arcade_mobile_light_frame");
			if (document.getElementsByClassName("forumtitle") && document.getElementsByClassName("forumtitle")[0])
			{
				var image = document.getElementsByClassName("forumtitle")[0].getElementsByTagName("IMG");
				if (image && image[0] && image[0].alt)
				{
					image[0].style.display = "none";
					var imageAlt = image[0].alt;
					var t = document.createTextNode(imageAlt);
					if (document.getElementById("top"))
						document.getElementById("top").appendChild(t);
					else
						document.getElementsByClassName("forumtitle")[0].appendChild(t);
				}

			}

			$("body").addClass("arcade_mobile_light_body");
			var arcadeContainers = ["header", "nav"];
			var arcCurrent;
			for (i=0;i<arcadeContainers.length;i++)
			{
				arcCurrent = document.getElementById(arcadeContainers[i]);
				if (arcCurrent)
				{
					arcCurrent.style.boxSizing = "";
					switch(arcadeContainers[i]) {
						case "header":
							arcCurrent.style.padding = "' . ($context['user']['is_logged'] ? '0em' : '0.2em'). '";
							break;
						case "nav":
							if (arcCurrent.tagName == "INPUT") {
								arcCurrent.value = " ";
								arcCurrent.type = "image";
								arcCurrent.src = "' . $settings['default_theme_url'] . '/images/arc_icons/blank.png";
								arcCurrent.alt = "";
							}
							break;
						default:
							arcCurrent.style.padding = "' . ($context['user']['is_logged'] ? '0em' : '0.2em'). '";
					}
				}
			}
		}
		$(document).ready(function() {
			arcadeSkinStyles();
			$("#mobile_user_menu").css("font-size", "1.5rem");
		});
	</script>';

	echo '
	<div id="arcade_lightskin_body">
		<div class="arcade_lightskin_titlebox">
			<div class="arcade_lightskin_title">
				', $txt['arcade'], '
			</div>
		</div>
		<div class="arcade_up_contain arcade_up_contain_light">
			<div class="arcade_up_contain_lightchild">
				<div id="arcade_panel"', empty($options['arcade_panel_collapse']) ? '' : ' style="display: none;"', '>';

		if (!empty($context['arcade']['notice']))
			echo '
					<span class="arcade_notice">
						', $context['arcade']['notice'], '
					</span>
					<div><span></span></div>';

		echo '
					<form action="', $scripturl, '?action=' . $arcadeAction . ';sa=search" method="post">
						<div class="arcade_up_contain_light_gamesearch">
							<input id="gamesearch" class="arcade_gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" maxlength="100">&nbsp;
							<input class="button_submit" type="submit" value="', $txt['arcade_search'], '" />
							<div id="suggest_gamesearch" class="game_suggest"></div>
							<script type="text/javascript">
								var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
							</script>
						</div>
						<div id="search_extra" class="arcade_light_search_extra">
							<input type="checkbox" id="favorites" name="favorites" value="1"', !empty($context['arcade_search']['favorites']) ? ' checked="checked"' : '', ' class="check" /> <label for="favorites">', $txt['search_favorites'], '</label>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="arcade_light_stats_box">
			<div class="arcade_light_stats_buttonsbox">
				<div style="display: inline;">
					', str_replace('margin: 0px 0px 0px 0px;font-size: 1.0em;padding: 0.5em 1em 0.5em 0em;', 'margin: 5px 0px 0px 0px;font-size: 1.0em;padding: 1em 1em 0em 0em;', ArcadeButtonStrip($context['arcade_tabs'], $categories, $rom)), '
				</div>
			</div>';

	if ((!empty($context['arcade']['stats'])) && $context['arcade']['stats']['games'] != 0)
			echo '
			<div class="arcade_light_gameslist smalltext arcade_div_stats">
				', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '
			</div>';

	echo '
			<span><span>&nbsp;</span></span>
			<div class="arcade_above">
				<span style="display: none;">&nbsp;</span>
			</div>
		</div>
	</div>';
}

function template_arcade_mobile_login()
{
	global $context, $scripturl, $txt, $user_info, $arcadeModSettings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$romManage = !empty($_SESSION['arcade_rom_initiate']) ? 'rom' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';

	// message to tell guests that they must log in
	if ($context['arcade_smf_version'] == 'v2.0')
	{
		echo '
		<div class="arcade_lightmobile_login_top1">
			<span style="display: none;">&nbsp;</span>
		</div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext arcade_login_container">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div>
					<div class="padding">
						<div class="noticebox">
							', $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'], '
						</div>
						<div class="arcade_lightmobile_login_top2">
							<span style="display: none;">&nbsp;</span>
						</div>
						<div class="ssi_table arcade_login_table centertext">
							<div class="arcade_login_row">
								<div class="arcade_login_cell arcade_login_cell_padding">
									<label for="user">', $txt['username'], ':</label>&nbsp;
								</div>
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text" />
								</div>
							</div>
							<div class="arcade_login_row">
								<div class="arcade_login_cell arcade_login_cell_padding">
									<label for="passwrd">', $txt['password'], ':</label>&nbsp;
								</div>
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<input type="password" name="passwrd" id="passwrd" size="30" class="input_password" />
								</div>
							</div>';

		// Open ID?
		if (!empty($arcadeModSettings['enableOpenID']))
			echo '
							<div class="arcade_login_row">
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<strong>&mdash;', $txt['or'], '&mdash;</strong>
								</div>
							</div>
							<div class="arcade_login_row">
								<div class="arcade_login_cell arcade_login_cell_padding">
									<label for="openid_url">', $txt['openid'], ':</label>&nbsp;
								</div>
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<input type="text" name="openid_identifier" id="openid_url" class="input_text openid_login" size="17" />
								</div>
							</div>';

		echo '
							<div class="arcade_login_row">
								<div style="display: table-cell;">
									<input type="hidden" name="cookielength" value="-1" />
								</div>
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<input type="submit" value="', $txt['login'], '" class="button_submit" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div class="arcade_above">
			<span style="display: none;">&nbsp;</span>
		</div>';
	}
	else
	{
		echo '
		<div class="arcade_lightmobile_login_top">
			<span style="display: none;">&nbsp;</span>
		</div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext arcade_login_container">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div>
					<div class="padding">
						<div class="noticebox">
							', $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'], '
						</div>
						<div class="arcade_above">
							<span></span>
						</div>
						<div style="display: table;border: 0px;" class="centertext ssi_table">
							<div class="arcade_login_row">
								<div class="arcade_login_table">
									<label for="user">', $txt['username'], ':</label>&nbsp;
								</div>
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text" />
								</div>
							</div>
							<div class="arcade_login_row">
								<div class="arcade_login_cell arcade_login_cell_padding">
									<label for="passwrd">', $txt['password'], ':</label>&nbsp;
								</div>
								<div class="arcade_login_cell centertext arcade_login_cell_padding">
									<input type="password" name="passwrd" id="passwrd" size="30" class="input_password" />
								</div>
							</div>
							<div class="arcade_login_row">
								<div class="arcade_login_cell">
									<input type="hidden" name="cookielength" value="-1" />
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
									<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '" />
								</div>
								<div class="arcade_login_cell arcade_login_cell_padding">
									<input type="submit" value="', $txt['login'], '" class="button_submit" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div class="arcade_above">
			<span style="display: none;">&nbsp;</span>
		</div>';
	}
}


function template_arcade_below($echo = true)
{
	global $txt, $context;

	// Print out copyright and version. Removing copyright is not allowed by license
	$output = '
	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		' . $txt['pdl_arcade_copyright'] . '
	</div>';

	if ($echo)
		echo $output;
	else
		return $output;
}

?>