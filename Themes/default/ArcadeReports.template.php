<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

/*  Above reports list  */
function template_arcade_reports_above()
{
	global $txt, $context, $settings;
	$context['arcade']['reports'] = array();

	echo '
	<div class="centertext" style="padding-bottom: 3em;">
		<h3 class="catbg smalltext">
			<span>', $txt['pdl_admin_reports'], '</span>
		</h3>
	</div>';

}

/*  Reports List  */
function template_arcade_reports()
{
	global $scripturl, $txt, $context, $settings, $sourcedir, $arcadeModSettings;
	if (empty($arcadeModSettings['arcadeEnableDownload']))
		$arcadeModSettings['arcadeEnableDownload'] = false;

	echo '
	<div class="centertext">
		<h3 class="catbg2 smalltext" style="text-align:center;border: 1px solid;">
			<a href="' . $scripturl. '?action=admin;area=arcade;sa=pdl_reports;">', $txt['arcade_pdl_reps'], '</a>
		</h3>
	</div>
	<div class="centertext" style="text-align:center;border: 1px solid;display: flex;width: 100%;flex-direction: column;justify-content: space-around;">
		<form method="post" action="', $context['post_url'] ,'" accept-charset="' . $context['character_set'] . '">
			<div style="display: table;border-spacing: 2px;border-collapse: separate;border: 4px;width: 100%;position: relative;" class="table_grid centertext">';
	if (empty($_SESSION['arcade_isMobile'])) {
		echo '
				<div style="display: table-row;">
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_test'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_id'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_type'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_name'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_userid'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">' . $txt['pdl_reports_year'] . '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">' . $txt['pdl_reports_month'] . '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">' . $txt['pdl_reports_day'] . '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_reason'], '</div>';
	}
	else {
		echo '
				<div style="display: table-row;font-size: smaller;">					
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_name'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_userid'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_reason'], '</div>';
	}

	if ($arcadeModSettings['arcadeEnableDownload'] == true) {
		if (empty($_SESSION['arcade_isMobile'])) {
			echo '
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_dl_status'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_dcount'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">
						<img title="' . $txt['pdl_reports_toggle'] . '" style="width: 1.2rem;height: 1.5rem;vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/power_icon.png" alt="[+/-]" />
					</div>';
		}
		else {
			echo '
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_dl_status'], '</div>
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">
						<img title="' . $txt['pdl_reports_toggle'] . '" style="width: 1.2rem;height: 1.5rem;vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/power_icon.png" alt="[+/-]" />
					</div>';
		}
	}
	elseif (!empty($_SESSION['arcade_isMobile'])) {
		echo '
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">', $txt['pdl_reports_type'], '</div>';
	}

	echo '
					<div style="display: table-cell;padding: 4px;border-bottom: 0.25rem groove;">
						<img title="' . $txt['pdl_reports_delete'] . '" style="width: 1.15rem;height: 1.2rem;vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/trash_icon.png" alt="[-]" />						
					</div>
				</div>';

	foreach ($context['arcade']['game_reports'] as $game)
	{
		$play = '<a href="' . $scripturl . '?action=' . (empty($game['rom_arcade']) ? 'arcade' : 'retro_arch') . ';sa=play;game=' . $game['gameid'] . '">' . $txt['pdl_listplay'] . '</a>';
		$status = $txt['pdl_dl_enabled'];
		if ((int)$game['disable'] > 0)
			$status = $txt['pdl_dl_disabled'];

		if (empty($_SESSION['arcade_isMobile'])) {
			echo '
				<div style="display: table-row;justify-content: space-around;align-items: center;">
					<div style="display: table-cell;padding: 4px;">', $play, '</div>
					<div style="display: table-cell;padding: 4px;">', $game['gameid'], '</div>
					<div style="display: table-cell;padding: 4px;">', $game['game_type'], '</div>
					<div style="display: table-cell;padding: 4px;"><a href="', $game['edit_game'], '">', $game['name'], '</a></div>
					<div style="display: table-cell;padding: 4px;"><a href="', $game['user_profile'], '">', $game['user_name'], '</a></div>
					<div style="display: table-cell;padding: 4px;">' . $game['year'] . '</div>
					<div style="display: table-cell;padding: 4px;">' . $game['month'] . '</div>
					<div style="display: table-cell;padding: 4px;">' . $game['day'] . '</div>
					<div style="display: table-cell;padding: 4px;"><img style="vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/icons.png" alt="[*]" id="reason_' . $game['gameid'] . '" onclick="alert(\'' . $game['report_reason'] . '\')" /></div>';
		}
		else {
			echo '
				<div style="display: table-row;font-size: smaller;justify-content: space-around;align-items: center;">					
					<div style="display: table-cell;padding: 4px;"><a href="', $game['edit_game'], '">', $game['name'], '</a></div>
					<div style="display: table-cell;padding: 4px;"><a href="', $game['user_profile'], '">', $game['user_name'], '</a></div>
					<div style="display: table-cell;padding: 4px;"><img style="vertical-align: middle;" src="' . $settings['default_images_url'] . '/arc_icons/icons.png" alt="[*]" id="reason_' . $game['gameid'] . '" onclick="alert(\'' . $game['report_reason'] . '\')" /></div>';
		}

		if ($arcadeModSettings['arcadeEnableDownload'] == true) {
			if (empty($_SESSION['arcade_isMobile'])) {
				echo '
					<div style="display: table-cell;padding: 4px;">', $status, '</div>
					<div style="display: table-cell;padding: 4px;">', $game['count'], '</div>
					<div style="display: table-cell;padding: 4px;" class="centertext">
						<div style="display: inline-flex;justify-content: center;width: 100%;">
							<input style="display: inline-flex;justify-content: center;flex-direction: row;" type="checkbox" name="toggle[]" value="' . $game['gameid'] . '" class="check" />
						</div>
					</div>';
			}
			else {
				echo '
					<div style="display: table-cell;padding: 4px;">
						<span style="display: block;text-align: center;">', $status, '</span>
						<span style="display: block;text-align: center;">', $game['game_type'], '</span>
					</div>
					<div style="display: table-cell;padding: 4px;" class="centertext">
						<div style="display: inline-flex;justify-content: center;width: 100%;">
							<input style="display: inline-flex;justify-content: center;flex-direction: row;" type="checkbox" name="toggle[]" value="' . $game['gameid'] . '" class="check" />
						</div>
					</div>';
			}
		}
		elseif (!empty($_SESSION['arcade_isMobile'])) {
			echo '
					<div style="display: table-cell;padding: 4px;">
						<span style="display: block;text-align: center;">', $game['game_type'], '</span>
					</div>';
		}

		echo '
					<div style="display: table-cell;padding: 4px;" class="centertext">
						<div style="display: inline-flex;justify-content: center;width: 100%;">
							<input style="display: inline-flex;justify-content: center;flex-direction: row;" type="checkbox" name="delete[]" value="' . $game['gameid'] . '" class="check" />
						</div>
					</div>
				</div>' . (!empty($_SESSION['arcade_isMobile']) ? '
				<div style="display: flex;left: 0px;flex-direction: row;max-height: 0.1rem;line-height: 0.1rem;margin: 0rem;width: 100%;max-width: 100%;position: absolute;">
					<div style="padding: 0px;margin: 0px;border: 0px;width: 100%;flex-grow: 1;align-self: stretch;border-top: 0.05rem dashed;"><span style="padding: 0.2rem 0rem 0.2rem 0rem;"></span></div>
				</div>' : '');
	}

		echo '
			</div><br /><br />
			<div style="display: table;border-collapse: collapse;border: 0px;width: 100%;position: relative;">
				<div style="display: table-row;">
					<div style="display: table-cell;padding: 4px;">&nbsp;</div>
				</div>
				<div style="display: table-row">
					<div style="display: table-cell;padding: 4px;text-align:right;border: 1px solid;">
						<div style="display: inline;padding-right: 0.2em;font-weight: bold;">',$txt['pdl_nonenable1'],'</div>
						<div style="display: inline;padding-right: 2em;"><input type="checkbox" name="nonenable" value="' . $txt['pdl_nonenable2'] . '" class="check" /></div>
						<div style="display: inline;padding-right: 0.2em;font-weight: bold;">',$txt['pdl_maintain1'],'</div>
						<div style="display: inline;padding-right: 1em;"><input type="checkbox" name="maintain" value="' . $txt['pdl_maintain2'] . '" class="check" /></div>
						<input type="submit" name="'. $txt['pdl_submit']. '" value="'. $txt['pdl_submit']. '"'. (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''). ' />
					</div>
				</div>
			</div>
			<input type="hidden" name="sc" value="'. $context['session_id']. '" />
		</form>
	</div>';
}

/* Forum copyright */
function template_arcade_reports_below()
{
	/* Add more logo's and breaks as required */
	global $txt;
	//echo '<div style="text-align:center">', $txt['pdl_arcade_copyright'], '<br /></div>';
}
?>