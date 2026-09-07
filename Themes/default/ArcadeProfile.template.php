<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_arena_challenge()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['default_images_url'], '/arc_icons/stats_info.gif" style="width: 20px;height: 20px;" alt="" />
			', $txt['arcade_invite_user'], ' - ', $context['member']['name'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span>&nbsp;</span></span>
		<div class="content">
			<form action="', $scripturl, '?action=arcade;sa=arenaInvite2" method="post">';

	if (!empty($context['matches']))
	{
		echo '
				<strong>', $txt['invite_to_existing'], '</strong>:
				<select name="match">';

		foreach ($context['matches'] as $match)
			echo '
					<option value="', $match['id'], '">', $match['name'], '</option>';

		echo '
				</select>
				<input class="button_submit" type="submit" value="', $txt['arcade_invite'], '" /><br />';
	}

	echo '
				<a href="', $scripturl, '?action=arcade;sa=newMatch;players=2;player[]=', $context['member']['id'], '">', $txt['arcade_create_new'], '</a>
			</form>
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div><br />';
}

function template_arcade_user_statistics()
{
	global $sourcedir, $scripturl, $txt, $context, $settings, $memberContext;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['default_images_url'], '/arc_icons/stats_info.gif" style="width: 20px;height: 20px;" alt="" />
			', $txt['arcade_member_stats'], ' - ', $context['member']['name'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span>&nbsp;</span></span>
		<div class="content">
			<dl class="stats">
				<dt>', $txt['arcade_champion_in'], ':</dt>
				<dd>', comma_format($context['arcade']['member_stats']['champion']), ' ', $txt['arcade_games'], '</dd>
				<dt>', $txt['arcade_rated_game'], ':</dt>
				<dd>', comma_format($context['arcade']['member_stats']['rates']), ' ', $txt['arcade_games'], '</dd>
				<dt>', $txt['arcade_average_rating'], ':</dt>
				<dd>', comma_format($context['arcade']['member_stats']['avg_rating']), '</dd>
			</dl>
			<div class="clear"></div>
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div><br />';

	if (!empty($context['arcade']['member_stats']['scores']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['default_images_url'], '/arc_icons/stats_info.gif" style="width: 20px;height: 20px;" alt="" />
			', $txt['arcade_member_stats'], ' - ', $txt['arcade_member_best_scores'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span>&nbsp;</span></span>
		<div class="content">
			<div style="display: table;border-collapse: collapse;width: 100%;border: 0px;width: 100%;overflow: hidden;">';

		foreach ($context['arcade']['member_stats']['scores'] as $score)
			echo '
				<div style="display: table-row;">
					<div style="display: table-cell;padding: 1px;"></div>
					<div style="display: table-cell;padding: 1px;">', $score['position'], '</div>
					<div style="display: table-cell;padding: 1px;"><a href="', $score['link'], '">', $score['name'], '</a></div>
					<div style="display: table-cell;padding: 1px;float: right;">', $score['score'], '</div>
					<div style="display: table-cell;padding: 1px 1px 1px 55px;">', $score['time'], '</div>
				</div>';

		echo '
			</div>
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div><br />';
	}

	if (!empty($context['arcade']['member_stats']['latest_scores']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['default_images_url'], '/arc_icons/stats_info.gif" width="20" height="20" alt="" />
			', $txt['arcade_member_stats'], ' - ', $txt['arcade_latest_scores'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span>&nbsp;</span></span>
		<div class="content">
			<div style="display: table;border-collapse: collapse;width: 100%;border: 0px;width: 100%;overflow: hidden;">';

		foreach ($context['arcade']['member_stats']['latest_scores'] as $score)
			echo '
				<div style="display: table-row;">
					<div style="display: table-cell;padding: 1px;"></div>
					<div style="display: table-cell;padding: 1px;">', $score['position'], '</div>
					<div style="display: table-cell;padding: 1px;"><a href="', $score['link'], '">', $score['name'], '</a></div>
					<div style="display: table-cell;padding: 1px;float: right;">', $score['score'], '</div>
					<div style="display: table-cell;padding: 1px 1px 1px 55px;">', $score['time'], '</div>
				</div>';

		echo '
			</div>
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div><br />';
	}
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['default_images_url'], '/arc_icons/stats_info.gif" style="width: 20px;height: 20px;" alt="" />
			', $txt['arcade_member_stats'], ' - ', $txt['arcade_positional'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span>&nbsp;</span></span>
		<div class="content">
			<div style="display: table;border-spacing: 2px;border-collapse: separate;width: 100%;border: 0px;width: 100%;">
				<div style="display: table-row;">
					<div class="titlebg" style="display: table-cell;width: 33%;padding:2px 5px 2px 5px;margin:2px 5px 0px 5px;border-bottom:1px solid #808080">
						',$txt['arcade_1st'],'
					</div>
					<div class="titlebg" style="display: table-cell;width: 33%;padding:2px 5px 2px 5px;margin:2px 5px 0px 5px;border-bottom:1px solid #808080">
						',$txt['arcade_2nd'],'
					</div>
					<div class="titlebg" style="display: table-cell;width: 33%;padding:2px 5px 2px 5px;margin:2px 5px 0px 5px;border-bottom:1px solid #808080">
						',$txt['arcade_3rd'],'
					</div>
				</div>
				<div style="display: table-row;">
					<div style="display: table-cell;padding: 5px;vertical-align: top;" class="windowbg2">
						<div style="overflow: hidden;">';

	foreach($context['arcade']['member_stats']['position1'] as $game)
		echo '
							<div style="padding-bottom: 2px;padding-left: 1px;">
								<a title="', $game['title'], '" href="', $game['link'], '">', $game['name'], '</a>
								<span title="', $game['time'], '" style="float: right;padding-right: 1px;">', $game['score'], '</span>
							</div>';

	echo '
						</div>
					</div>
					<div style="display: table-cell;padding: 5px;vertical-align: top;" class="windowbg2">
						<div style="overflow: hidden;">';
	foreach($context['arcade']['member_stats']['position2'] as $game)
		echo '
							<div style="padding-bottom: 2px;padding-left: 1px;">
								<a title="', $game['title'], '" href="', $game['link'], '">', $game['name'], '</a>
								<span title="', $game['time'], '" style="float: right;padding-right: 1px;">', $game['score'], '</span>
							</div>';

	echo '
						</div>
					</div>
					<div style="display: table-cell;padding: 5px;vertical-align: top;" class="windowbg2">
						<div style="overflow: hidden;">';
	foreach($context['arcade']['member_stats']['position3'] as $game)
		echo '
							<div style="padding-bottom: 2px;padding-left: 1px;">
								<a title="', $game['title'], '" href="', $game['link'], '">', $game['name'], '</a>
								<span title="', $game['time'], '" style="float: right;padding-right: 1px;">', $game['score'], '</span>
							</div>';

	echo '
						</div>
					</div>
				</div>
			</div>
		</div>
		<span class="botslice"><span>&nbsp;</span></span>
	</div>
	<div style="padding-top: 25px;"><span style="display: none;">&nbsp;</span></div>';
}

function template_profile_arcade_notification()
{
	global $scripturl, $txt, $context, $arcadeModSettings;

	echo '
	<dt><strong>', $txt['arcade_notifications'], '</strong></dt>
	<dd>';

	foreach ($context['notifications'] as $id => $notify) {
		if (in_array($id, $context['arcade_admin_only']) && !allowedTo('arcade_admin'))
			continue;
		echo '
			<input type="checkbox" id="', $id, '" name="', $id, '" value="1"', $notify['value'] ? ' checked="checked"' : '', ' class="check" /> <label for="', $id, '">', $notify['text'], '</label><br />';
	}

	echo '
	</dd>';
}

function template_profile_arcade_user_emulatorjs()
{
	global $scripturl, $txt, $context, $arcadeModSettings;

	echo '
	<dt><strong>', $txt['arcade_emulatorjs_rom'], '</strong></dt>
	<dd>';

	foreach ($context['arcade_user_emulatorjs'] as $id => $romsetting) {
		if (in_array($id, $context['arcade_admin_only']) && !allowedTo('arcade_admin'))
			continue;
		echo '
			<input type="checkbox" id="', $id, '" name="', $id, '" value="1"', $romsetting['value'] ? ' checked="checked"' : '', ' class="check" /> <label for="', $id, '">', $romsetting['text'], '</label><br />';
	}

	echo '
	</dd>';
}

?>