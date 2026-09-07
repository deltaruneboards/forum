<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_statistics()
{
	global $scripturl, $txt, $context, $settings, $arcadeTempSettings;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : 0;
	$romManage = !empty($_SESSION['arcade_rom_initiate']) ? 'rom' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	echo '
	<div style="align-self: flex-end;justify-content: baseline;width: 100%;flex-direction: row;">
		<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>
		<div class="cat_bar">
			<h3 class="catbg centertext" style="vertical-align: middle;">
				<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['default_images_url'], '/arc_icons/gold.gif" alt="" />
				<span class="mediumtext" style="padding: 0px 6px 0px 0px;vertical-align: middle;">', $txt['arcade_stats'], '</span>
				<img class="icon" style="margin: 3px 5px 0 0;padding-bottom: 0.2em;filter: brightness(200%);-webkit-filter: brightness(200%);-moz-filter: brightness(200%);" src="', $settings['default_images_url'], '/arc_icons/gold.gif" alt="" />
			</h3>
		</div>
		', $context['arcade_smf_version'] == 'v2.1' ? '
		<div class="game_table arcade_up_contain windowbg">' :
		'<span class="clear upperframe"><span>&nbsp;</span></span>
		<div class="roundframe">', '
			<div class="innerframe" style="border-radius: 5px;display: flex;width: 100%;flex-wrap: wrap;">';

	$alternate = false;

	// Most played games
	if (!empty($context['arcade']['statistics']['play']) > 0)
	{
		echo '
				<div style="padding-top: 10px;"><span style="display: none;">&nbsp;</span></div>
				<div class="arcade_stats_cell" style="flex: 1 1 50%;padding: 0.5rem;">
					<div>
						<h3 style="border-bottom: 1px dotted;">
							<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" class="icon" alt="" />
							<span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_most_played'], '</span>
						</h3>
					</div>
					<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
						<span class="topslice"><span>&nbsp;</span></span>
						<div class="content" style="display: flex;width: 100%;text-decoration: underline;justify-content: center;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">' . ucfirst($txt['arcade_stats_game']) . '</div>
							<div style="display: inline-flex;width: 20%;justify-content: center;">' . (!empty($context['arcade']['statistics']['play'][0]['percent']) ? ucfirst($txt['arcade_avg']) : ucfirst($txt['arcade_qty'])) . '</div>
						</div>
						<div class="clear" style="padding-top: 0.1rem;line-height: 0.1rem;"><span>&nbsp;</span></div>';

		foreach ($context['arcade']['statistics']['play'] as $game)
		{
			echo '
						<div class="content" style="display: flex;width: 100%;justify-content: space-evenly;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">', $game['link'], '</span></div>
							<div style="display: inline-flex;width: 20%;justify-content: center;" title="' . (sprintf($txt['arcade_stats_qty'], $game['plays'])) . '">' . (!empty($game['percent']) ? '<progress value="' . $game['percent'] . '" max="100" min="0">' . $game['percent'] . '%</progress>' : $game['plays']) . '</div>
							<div style="height: 1px;line-height: 1px;"><span style="dislay: none;">&nbsp;</span></div>
						</div>';
		}

		echo '
						<div class="clear" style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>
						<span class="botslice"><span>&nbsp;</span></span>
					</div>
				</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
				<div class="clear"></div>';
	}

	// Most active in arcade
	if (!empty($context['arcade']['statistics']['active']))
	{
		echo '
				<div class="arcade_stats_cell" style="flex: 1 1 50%;padding: 0.5rem;">
					<div>
						<h3 style="border-bottom: 1px dotted;">
							<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" class="icon" alt="" />
							<span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_most_active'], '</span>
						</h3>
					</div>
					<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
						<span class="topslice"><span>&nbsp;</span></span>
						<div class="content" style="display: flex;width: 100%;text-decoration: underline;justify-content: center;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">' . ucfirst($txt['arcade_member']) . '</div>
							<div style="display: inline-flex;width: 20%;justify-content: center;">' . (!empty($context['arcade']['statistics']['active'][0]['percent']) ? ucfirst($txt['arcade_avg']) : ucfirst($txt['arcade_qty'])) . '</div>
						</div>
						<div class="clear" style="padding-top: 0.1rem;line-height: 0.1rem;"><span>&nbsp;</span></div>';


		foreach ($context['arcade']['statistics']['active'] as $game)
		{
			echo '
						<div class="content" style="display: flex;width: 100%;justify-content: space-evenly;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">', $game['link'], '</span></div>
							<div style="display: inline-flex;width: 20%;justify-content: center;" title="' . (sprintf($txt['arcade_stats_qty'], $game['scores'])) . '">' . (!empty($game['percent']) ? '<progress value="' . $game['percent'] . '" max="100" min="0">' . $game['percent'] . '%</progress>' : $game['scores']) . '</div>
							<div style="height: 1px;line-height: 1px;"><span style="dislay: none;">&nbsp;</span></div>
						</div>';
		}

		echo '
						<div class="clear" style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>
						<span class="botslice"><span>&nbsp;</span></span>
					</div>
				</div>';


		$alternate = !$alternate;

		if (!$alternate)
			echo '
				<div class="clear"></div>';
	}

	// Top rated games
	if (!empty($context['arcade']['statistics']['rating']))
	{
		echo '
				<div class="arcade_stats_cell" style="flex: 1 1 50%;padding: 0.5rem;">
					<div style="padding-top: 10px;">
						<h3 style="border-bottom: 1px dotted;">
							<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" class="icon" alt="" />
							<span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_best_games'], '</span>
						</h3>
					</div>
					<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
						<span class="topslice"><span>&nbsp;</span></span>
						<div class="content" style="display: flex;width: 100%;text-decoration: underline;justify-content: center;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">' . ucfirst($txt['arcade_stats_game']) . '</div>
							<div style="display: inline-flex;width: 20%;justify-content: center;">' . (!empty($context['arcade']['statistics']['play'][0]['percent']) ? ucfirst($txt['arcade_stats_rating']) : ucfirst($txt['arcade_stats_rating'])) . '</div>
						</div>
						<div class="clear" style="padding-top: 0.1rem;line-height: 0.1rem;"><span>&nbsp;</span></div>';

		foreach ($context['arcade']['statistics']['rating'] as $game)
		{
			echo '
						<div class="content" style="display: flex;width: 100%;justify-content: space-evenly;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">', $game['link'], '</span></div>
							<div style="display: inline-flex;width: 20%;justify-content: center;" title="' . (sprintf($txt['arcade_stats_stars'], $game['rating'])) . '">' . (!empty($game['percent']) ? '<progress value="' . $game['percent'] . '" max="100" min="0">' . $game['percent'] . '%</progress>' : $game['stars']) . '</div>
							<div style="height: 1px;line-height: 1px;"><span style="dislay: none;">&nbsp;</span></div>
						</div>';
		}

		echo '
						<div class="clear" style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>
						<span class="botslice"><span>&nbsp;</span></span>
					</div>
				</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
				<div class="clear" style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>';
	}

	// Best players by champions
	if (!empty($context['arcade']['statistics']['champions']))
	{
		echo '
				<div class="arcade_stats_cell" style="flex: 1 1 50%;padding: 0.5rem;">
					<div style="padding-top: 10px;">
						<h3 style="border-bottom: 1px dotted;">
							<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" class="icon" alt="" />
							<span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_best_players'], '</span>
						</h3>
					</div>
					<div class="smalltext" style="padding-left: 5px;padding-right: 15px;">
						<span class="topslice"><span>&nbsp;</span></span>
						<div class="content" style="display: flex;width: 100%;text-decoration: underline;justify-content: center;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">' . ucfirst($txt['arcade_member']) . '</div>
							<div style="display: inline-flex;width: 20%;justify-content: center;">' . (!empty($context['arcade']['statistics']['play'][0]['percent']) ? ucfirst($txt['arcade_avg']) : ucfirst($txt['arcade_qty'])) . '</div>
						</div>
						<div class="clear" style="padding-top: 0.1rem;line-height: 0.1rem;"><span>&nbsp;</span></div>';

		foreach ($context['arcade']['statistics']['champions'] as $member)
		{
			echo '
						<div class="content" style="display: flex;width: 100%;justify-content: space-evenly;">
							<div style="display: inline-flex;width: 80%;justify-content: flex-start;">', $member['link'], '</span></div>
							<div style="display: inline-flex;width: 20%;justify-content: center;" title="' . (sprintf($txt['arcade_stats_qty'], $member['champions'])) . '">' . (!empty($member['percent']) ? '<progress value="' . $member['percent'] . '" max="100" min="0">' . $member['percent'] . '%</progress>' : $member['champions']) . '</div>
							<div style="height: 1px;line-height: 1px;"><span style="dislay: none;">&nbsp;</span></div>
						</div>';
		}

		echo '
						<div class="clear" style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>
						<span class="botslice"><span>&nbsp;</span></span>
					</div>
				</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
				<div class="clear"></div>';
	}

	if (!empty($context['arcade']['statistics']['longest']))
	{
		echo '
				<div class="arcade_stats_cell" style="flex: 1 1 50%;padding: 0.5rem;">
					<div style="padding-top: 10px;">
						<h3 style="border-bottom: 1px dotted;">
							<img src="', $settings['default_images_url'], '/arc_icons/gold.gif" class="icon" alt="" />
							<span style="padding: 0px 0px 7px 5px;vertical-align: middle;">', $txt['arcade_longest_champions'], '</span>
						</h3>
					</div>
					<div class="smalltext" style="padding-left: 5px;padding-left: 15px;">
						<span class="topslice"><span>&nbsp;</span></span>
						<div class="content" style="display: flex;width: 100%;text-decoration: underline;justify-content: center;">
							<div style="display: inline-flex;width: 70%;justify-content: flex-start;">' . ucfirst($txt['arcade_member']) . '</div>
							<div style="display: inline-flex;width: 30%;justify-content: center;">' . ucfirst($txt['arcade_stats_duration']) . '</div>
						</div>
						<div class="clear" style="padding-top: 0.1rem;line-height: 0.1rem;"><span>&nbsp;</span></div>';

		foreach ($context['arcade']['statistics']['longest'] as $game)
		{
			if (empty($game)) {
				continue;
			}
			$current = $game['current'] ? 'font-weight: bold;' : '';
			echo '
						<div class="content" style="display: flex;width: 100%;justify-content: space-evenly;">
							<div style="display: inline-flex;width: 70%;justify-content: flex-start;' . $current . '">', $game['member_link'], '<span style="padding-left: 0.1rem;">(', $game['game_link'], ')</span></div>
							<div style="display: inline-flex;width: 30%;justify-content: center;' . $current . '" title="' . ($game['durationy']) . '">' . (!empty($game['percent']) ? '<progress style="width: 100%;" value="' . $game['percent'] . '" max="100" min="0">' . $game['percent'] . '%</progress>' : game['durationy']) . '</div>
							<div style="height: 1px;line-height: 1px;"><span style="dislay: none;">&nbsp;</span></div>
						</div>';
		}

		echo '

						<div class="clear" style="padding-top: 5px;"><span style="display: none;">&nbsp;</span></div>
						</div>
						<span class="botslice"><span>&nbsp;</span></span>
					</div>
				</div>';

		$alternate = !$alternate;

		if (!$alternate)
			echo '
				<div class="clear"><span style="display: none;">&nbsp;</span></div>';
	}

	if ($alternate)
			echo '
				<div class="clear" style="padding-top: 5px;"><span>&nbsp;</span></div>';
	echo '
			</div>
		</div>
		<span class="lowerframe"><span>&nbsp;</span></span>';

	if(empty($arcadeTempSettings['arcade_hide_buttons']) && empty($_SESSION['arcade_isMobile']))
	{
		echo '
		<div style="clear: both;width:100%;display: flex;align-self: flex-end;flex-direction: column;" class="smalltext">
			<div style="display: inline;">', template_button_strip($context['arcade_tabs'], 'left', array(), $rom), '</div>';

		if ($context['arcade']['stats']['games'] != 0)
			echo '
			<div class="smalltext" style="clear: right;padding:8px 7px 0px 0px;display: flex;justify-content: flex-end;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>';

		echo '
		</div>';
	}

	echo '
		<div style="padding-top: 0.15rem;"><span style="display: none;">&nbsp;</span></div>
		<div id="arcade_stats_copy">&nbsp;</div>
	</div>
	<script>
		function equalizeHeights() {
			var maxHeight = 0;
			$(".arcade_stats_cell").each(function() {
				$(this).css("height", "auto");
				var currentHeight = $(this).height();
				if (currentHeight > maxHeight) {
					maxHeight = currentHeight;
				}
			});
			$(".arcade_stats_cell").height(maxHeight);
		}
		$(document).ready(function() {
			equalizeHeights();
			$(window).resize(function() {
				equalizeHeights();
			});
			$("#arcade_bottom").appendTo("#arcade_stats_copy");
		});
	</script>';
}
?>