<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_retro_arch_above()
{
	// just filler
}

function template_arcade_retro_arch()
{
	global $scripturl, $txt, $context, $settings, $arcadeModSettings, $user_info, $boardurl, $boarddir;
	$checkFull = isset($_REQUEST['sameArcadeWindow']) && isset($_REQUEST['sameArcadeWindow']) == 1 && isset($_REQUEST['full']) && $_REQUEST['full'] == 1 ? true : false;
	if (empty($context['arcade']['romwindow'])) {
		echo '
		<iframe style="' . (!empty($checkFull) ? 'height: 100vh;width: 100vw;overflow: hidden;' : 'overflow: hidden;') . '" class="centertext" id="iframe_smfarcaderoms" srcdoc=\'' . $context['arcade_retro_arch_rom'] . '\'></iframe>';
	}
	else {		
		$context['arcade_rom_play_flag'] = 'play';
		$arcade_version = $arcadeModSettings['arcadeVersion'];
		$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
		$reload = isset($_REQUEST['reload']) ? (int)$_REQUEST['reload'] : 0;
		$jsInsert = file_exists($settings['default_theme_dir'] . '/arcade_scripts/arcadeRomAdd.js') ? file_get_contents($settings['default_theme_dir'] . '/arcade_scripts/arcadeRomAdd.js') : '';
		$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
		$action = !empty($rom) ? 'retro_arch' : 'arcade';
		echo '
			<div class="windowbg2" id="playgame" style="clear: both;position: relative;margin: 0 auto;overflow: hidden;">
				<span class="topslice"><span>&nbsp;</span></span>
				<div style="display: flex;align-items: center;justify-content: center;">
					<div class="centertext" id="gamearea">
						<iframe style="display: inline !important;height: ' . ((int)$context['arcade_rom_game_data']['height']) . 'px;width: ' . ((int)$context['arcade_rom_game_data']['width']) . 'px;overflow: hidden;" class="centertext" id="iframe_smfarcaderoms_small" srcdoc=\'' . $context['arcade_retro_arch_rom'] . '\'></iframe>
						', empty($context['arcade']['can_submit']) ? '<br /><strong>' . $txt['arcade_cannot_save'] . '</strong>' : '', '
					</div>
				</div>
				<span class="botslice"><span>&nbsp;</span></span>
			</div>
		</div>
		<div ', ($context['arcade_rom_game_data']['type'] != 'fullscreen' ? 'style="display: none;" ' : ''), 'class="escgamediv">
			<img class="escgame" id="escbutton" src="' . $settings['default_theme_url'] . '/images/arc_icons/arcade_esc.png' . '" alt="[ESC]" onclick="escGameSmf()" />
		</div>
		<script type="text/javascript">' . (!empty($context['arcade_rom_game_data']['javascript']) ? '
			' . $context['arcade_rom_game_data']['javascript'] : '') . '
			if (window.addEventListener) {
				window.addEventListener("load", function (){
					smfArcadeGameDims3();
					return false;
				});
			}
			else {
				window.attachEvent("onload", function (){
					smfArcadeGameDims3();
					return false;
				});
			}
			function smfArcadeGameDims3() {
				var divelement = document.getElementById("game");
				if (divelement) {
					divelement.width = "100vw";
					divelement.height = "100vh";
					scrollTo(document.body, divelement.offsetTop, 100);
				}
				var gameObject = document.getElementById("gamearea");
				gameObject.onmouseenter = function (){
					document.body.style.overflowY="hidden";
				};
				gameObject.onmouseleave = function (){
					document.body.style.overflowY="auto";
				};

			}
			function escGameSmf() {
				window.location = "' . $scripturl . '?action=' . $action . ';sa=highscore;game=' . $context['arcade_rom_game_data']['id'] . ';reload=' . mt_rand(0, 9999) . ';#commentform3";
			}
		</script>';
	}
}

function template_arcade_retro_arch_below($echo = true)
{
	global $txt, $context;

	// Print out copyright and version. Removing copyright is not allowed by license
	$output = '
	<div id="bot"><span></span></div>
	<div id="arcade_bottom" class="smalltext" style="text-align: center;">
		' . $txt['pdl_arcade_copyright'] . '
	</div>';

	if ($echo && empty($context['arcade']['romwindow']))
		echo $output;
	else
		return $output;
}

?>