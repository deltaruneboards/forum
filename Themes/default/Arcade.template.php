<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_above()
{
	global $scripturl, $txt, $context, $settings, $options, $arcadeModSettings, $options, $user_info;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$romTxt = !empty($rom) ? 'rom_' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext arcade_vert_mid">
			<span style="clear: right;">', $txt[$romTxt . 'arcade'], '</span>
		</h3>
	</div>
	<div class="arcade_up_contain windowbg">
		<div class="innerframe">
			<div id="arcade_panel"', empty($options['arcade_panel_collapse']) ? ' style="position: relative;"' : ' style="display: none;position: relative;"', '>';

		if (!empty($context['arcade']['notice']))
			echo '
				<span class="arcade_notice">', $context['arcade']['notice'], '</span><br />';

	echo '
				<form action="', $scripturl, '?action=' . $arcadeAction . ';sa=search" method="post">
					<input autocomplete="off" placeholder="' . $txt['arcade_search_placeholder'] . '" id="gamesearch" class="arcade_gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '" />&nbsp;<input class="button_submit" type="submit" value="', $txt['arcade_search'], '" />
					<div id="suggest_gamesearch" class="game_suggest"></div>
					<div id="search_extra">
						<input type="checkbox" id="favorites" name="favorites" value="1"', !empty($context['arcade_search']['favorites']) ? ' checked="checked"' : '', ' class="check" /> <label for="favorites">', $txt['search_favorites'], '</label>
					</div>
					<script type="text/javascript"><!-- // --><![CDATA[
						var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
					// ]]></script>
				</form>';
	// categories
	if (!empty($arcadeModSettings['arcadeDropCatClassic'])) {
		echo '
				<div style="position: absolute;display: inline;top: 0px;right: 0px;">', ArcadeCategoryDropdown($rom), '</div>';
	}

	echo '
			</div>
		</div>
	</div>
	<div style="width: 100%;display: inline-block;">
		<div style="display: inline-block;">', template_button_strip($context['arcade_tabs'], 'left', array(), $rom), '</div>
	</div>
	<div class="arcade_above"><span style="display: none;">&nbsp;</span></div>
	<div style="padding-top: 1em;clear: both;"><span></span></div>';
}

function template_arcade_login()
{
	global $context, $scripturl, $txt, $user_info, $arcadeModSettings;

	echo '
		<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<div class="centertext arcade_login_container">
				<div class="title_bar">
					<h3 class="titlebg centertext">', $txt['arcade_email_' . $context['arcade_sub'] . '_error'], '</h3>
				</div>
				<div class="windowbg">
					<div class="padding">
						<div class="noticebox">', (!empty($txt['arcade_email_' . $context['arcade_sub'] . '_error_msg']) ? $txt['arcade_email_' . $context['arcade_sub'] . '_error_msg'] : $txt['arcade_login_title']), '</div>
						<div class="arcade_above"><span></span></div>
						<div style="display: flex;flex-direction: column;border: 0px;" class="centertext">
							<div style="flex: 1 0 100%;">
								<div>
									<div><label for="user">', $txt['username'], ':</label>&nbsp;</div>
									<div class="centertext arcade_login_cell_padding">
										<input type="text" id="user" name="user" size="30" value="', $user_info['username'], '" class="input_text">
									</div>
								</div>
								<div>
									<div class="arcade_login_cell_padding"><label for="passwrd">', $txt['password'], ':</label>&nbsp;</div>
									<div class="centertext arcade_login_cell_padding"><input type="password" name="passwrd" id="passwrd" size="30" class="input_password"></div>
								</div>
								<div>
									<div>
										<input type="hidden" name="cookielength" value="-1">
										<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
										<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">
									</div>
									<div class="arcade_login_cell_padding"><input type="submit" value="', $txt['login'], '" class="button_submit"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div class="arcade_above"><span style="display: none;">&nbsp;</span></div>';
}


function template_arcade_below($echo = true)
{
	global $txt;

	// Print out copyright and version. Removing copyright is not allowed by license
	$output = '
	<div id="bot"><span></span></div>
	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		' . $txt['pdl_arcade_copyright'] . '
	</div>';

	if ($echo)
		echo $output;
	else
		return $output;
}

?>