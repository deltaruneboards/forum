<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

function template_arcade_arena_matches()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings, $arcadeTempSettings;

	echo arcadeArenaTemplate();

	echo '
		<div id="arcadeArenaLogin"><span></span></div>
		<div class="matchmobilebg" id="arenamatch">
			<div class="match">
				<div id="contain" class="rowinfo">
					<div class="menu">', ($context['arcade']['can_create_match'] ? '
						<a class="matchlink" href="' . $scripturl . '?action=arcade;sa=newMatch;reload=' . mt_rand(0, 9999) . ';#arenamatch">' . $txt['arcade_newMatch'] . '</a>' : '<span style="display: none;">&nbsp;</span>'), '
					</div>
					<div style="padding-top: 15px;">';

	if (empty($context['matches']))
		echo '

						<h3 style="vertical-align: middle;">
							<strong>', $txt['arcade_no_matches'], '</strong>
						</h3>';
	else
	{
		$x = 0;
		foreach ($context['matches'] as $match)
		{
			$x++;
			echo '
						<div style="display: table;">
							<div style="display: table-header-group">
								<div style="display: table-row;" class="arenatitlebg title">
									<div style="display: table-cell;" class="arenacellwidthy">', $txt['match_name'], '</div>
									<div style="display: table-cell;" class="arenacellwidthy">', $txt['match_opponent'], '</div>
								</div>
							</div>
							<div style="display: table-row-group;">';

			echo '
								<div style="display: table-row;">
									<div style="display: table-cell;" class="arenacellwidthy">', str_replace('<a', '<a style="background-color: lightgreen;border-radius: 0.15em;color: black;font-size: 0.65em;"', $match['link']), '</div>
									<div style="display: table-cell;" class="rowinfo arenacellwidthy">', $match['starter']['link'], '</div>
								</div>
							</div>
						</div>
						<div style="display: table;">
							<div style="display: table-header-group">
								<div style="display: table-row;" class="arenatitlebg title">
									<div style="display: table-cell;" class="arenacellwidthz">', $txt['match_status'], '</div>
									<div style="display: table-cell;" class="arenacellwidthx">', $txt['match_players'], '</div>
									<div style="display: table-cell;" class="arenacellwidthx">', $txt['match_round'], '</div>
								</div>
							</div>
							<div style="display: table-row-group;">
								<div style="display: table-row;">
									<div style="display: table-cell;" class="rowinfo arenacellwidthz">', $txt[$match['status']], '</div>
									<div style="display: table-cell;" class="rowinfo arenacellwidthx">', $match['players'], ' / ', $match['players_limit'], '</div>
									<div style="display: table-cell;" class="rowinfo arenacellwidthx">', $match['round'], ' / ', $match['rounds'], '</div>
								</div>
							</div>
						</div>
					' . ($x < count($context['matches']) ? '<div style="width: 100%;border-bottom: 2px solid;"></div>' : '');
		}
	}

	echo '
					</div>
					<div class="footer">
						<div class="pagesection" style="padding-top: 5px;">
							<div class="align_left">', $context['page_index'], '</div>
						</div>
					</div>
				</div>
			</div>
		</div>';
}

function template_arcade_arena_view_match_above()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings;
	echo arcadeArenaTemplate();
	echo '
	<div class="matchmobilebg" id="arenamatch">
		<div style="font-size: 1.8vw;">
			<div id="contain">
				<div class="title">
					<span>', $context['match']['name'], '</span>
				</div>
				<div style="padding: 0 0.5em;display: table;border-collapse:separate;border-spacing:5px;width: 100%;">
					<div style="display: table-row;width: 100%;">
						<span class="clear" style="display: table-cell;position: absolute;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display: table-row;">
									<span style="display: table-cell"><strong>', $txt['match_status'], '</strong>:</span><span style="display: table-cell">', $txt[$context['match']['status']], '</span>
								</span>
								<span style="display: table-row;">
									<span style="display: table-cell"><strong>', $txt['match_players'], '</strong>:</span><span style="display: table-cell">', $context['match']['num_players'], ' / ', $context['match']['players_limit'], '</span>
								</span>
								<span style="display: table-row;">
									<span style="display: table-cell"><strong>', $txt['match_round'], '</strong>:</span><span style="display: table-cell">', $context['match']['round'], ' / ', $context['match']['num_rounds'], '</span>
								</span>
							</span>
						</span>';

	if ($context['can_start_match'])
	{
		echo '
						<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display: table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;start;match=', $context['match']['id'], ';' . $context['session_var'] . '=' . $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_startMatch'], '</span>
										</a>
									</span>
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;start;match=', $context['match']['id'], ';' . $context['session_var'] . '=' . $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_accept.png" alt="" />
										</a>
									</span>
								</span>
							</span>
						</span>';
	}

	if ($context['can_play_match'])
	{
		echo '
						<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display: table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=play;match=', $context['match']['id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_play'], '</span>
										</a>
									</span>
									<span style="display: table-cell">
										<a href="', $scripturl, '?action=arcade;sa=play;match=', $context['match']['id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_accept.png" alt="" />
										</a>
									</span>
								</span>
							</span>
						</span>';
	}

	if ($context['can_edit_match'])
	{
		echo '
						<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display: table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;delete;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_cancelMatch'], '</span>
										</a>
									</span>
									<span style="display: table-cell">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;delete;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_decline.png" alt="" />
										</a>
									</span>
								</span>
							</span>
						</span>';
	}

	if ($context['can_join_match'])
	{
		echo '
						<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display: table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_joinMatch'], '</span>
										</a>
									</span>
									<span style="display: table-cell">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_accept.png" alt="" />
										</a>
									</span>
								</span>
							</span>
						</span>';
	}
	elseif ($context['can_leave'])
	{
		echo '
						<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display:table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_leaveMatch'], '</span>
										</a>
									</span>
									<span style="display: table-cell">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_decline.png" alt="" />
										</a>
									</span>
								</span>
							</span>
						</span>';
	}
	elseif ($context['can_accept'])
	{
		echo '
						<span class="clear" style="display: table-cell;float: right;padding-left: 5px;margin-right: 10px;padding-top: 5px;">
							<span style="display: table;">
								<span style="display: table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_accept'], '</span>
										</a>
									</span>
									<span style="display: table-cell">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;join;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_accept.png" alt="" />
										</a>
									</span>
								</span>
								<span style="display: table-row;" class="matchinfo">
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<span class="matchinfotext">', $txt['arcade_decline'], '</span>
										</a>
									</span>
									<span style="display: table-cell;">
										<a href="', $scripturl, '?action=arcade;sa=viewMatch;leave;match=', $context['match']['id'], ';' . $context['session_var'] . '=', $context['session_id'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch">
											<img class="arena_icon" src="', $settings['default_images_url'], '/arc_icons/arena_decline.png" alt="" />
										</a>
									</span>
								</span>
							</span>
						</span>
					</div>
				</div>';
	}

	echo '
			</div>
			<div style="padding-top: 12%;"><span style="display: none;">&nbsp;</span></div>';
}

function template_arcade_arena_view_match()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings;

	echo '
	<div style="padding-top: 3em;"><span style="display: none;">&nbsp;</span></div>
	<div style="width: 100%;padding: 0em;margin: 0em;">
		<div class="matchlist" style="display: table;width: 100%;padding: 0em;">
			<div style="display: table-row;" class="arenatitlebg title">
				<div style="display: table-cell;font-size: 3vw;padding: 0em;margin: 0em;" class="first_th arenatitlebg matchlist" style="width: 2em;padding: 0em;">', $txt['arcade_position_rank'], '</div>
				<div style="display: table-cell;font-size: 3vw;" class="matchlist" style="width: 40%;padding-left: 0.3em;"><span style="padding-left: 0.2em;">', $txt['arcade_member'], '</span></div>
				<div style="display: table-cell;font-size: 3vw;" class="matchlist" style="width: 30%">', $txt['match_status'], '</div>
				<div style="display: table-cell;font-size: 3vw;" class="matchlist last_th" style="width: 15%;">', $txt['arcade_score'], '</div>
			</div>';

	foreach ($context['match']['players'] as $player)
	{
		echo '
			<div style="display: table-row;">
				<div style="display: table-cell;" style="width: 2em;" class="matchlist smalltext">', $player['rank'], '</div>
				<div style="display: table-cell;" class="matchlist rowinfo">
					<span class="floatleft" style="padding-left: 0.2em;">', $player['link'], '</span>
					<span class="floatright">';

		if ($player['can_kick'])
			echo '
						<a href="', $player['kick_url'], ';reload=' . mt_rand(0, 9999) . ';#arenamatch"><img src="', $settings['default_images_url'], '/arc_icons/arena_decline.png" alt="" /></a>';
		else
			echo '
						&nbsp;';
		echo '
					</span>
				</div>
				<div style="display: table-cell;" class="matchlist rowinfo">', $player['status'], '</div>
				<div style="display: table-cell;" class="matchlist rowinfo">', $player['score'], '</div>
			</div>';
	}

	echo '
		</div>
	</div>
	<div style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>

	<div style="width: 100%;padding: 0 0.5em;">
		<div class="matchlist" style="display: table;width: 100%;">
			<div style="display: table-row;" class="arenatitlebg title">
				<div style="display: table-cell;" class="first_th matchlist" style="width: 20%;"><span style="font-size: 3vw;">', $txt['arcade_rounds'], '</span></div>
				<div style="display: table-cell;" class="matchlist last_th" style="width: 80%;"><span style="font-size: 3vw;">', $txt['arcade_game_name'], '</span></div>
			</div>';

	foreach ($context['match']['rounds'] as $round)
	{
		echo '
			<div style="display: table-row;">
				<div style="display: table-cell;" style="width: 20%;" class="matchlist rowinfo">', $round['round'], '.</div>';

		if ($round['select'] && !$round['can_play'])
			echo '
				<div style="display: table-cell;" class="matchlist rowinfo">', $round['name'], '</div>';
		elseif ($round['select'] && $round['can_play'])
			echo '
				<div style="display: table-cell;" class="matchlist rowinfo"><a href="', $round['play_url'], '">', $round['name'], '</a></div>';
		elseif ($round['can_select'])
			echo '
				<div style="display: table-cell;" class="matchlist rowinfo"><a href="', $round['url'], '">', $txt['select_game'], '</a></div>';
		else
			echo '
				<div style="display: table-cell;" class="matchlist rowinfo"></div>';

		echo '
			</div>';
	}

	echo '
		</div>
	</div>
	<br class="clear" />';
}

function template_arcade_arena_view_match_below()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings;
	echo '
				</div>
			</div>
		</div>
		<div class="clear" style="padding-top: 15px;"><span style="display: none;">&nbsp;</span></div>
	</div>';
}

function template_arcade_arena_new_match()
{
	global $scripturl, $txt, $context, $settings, $user_info, $arcadeModSettings;

	$arcade_version = $arcadeModSettings['arcadeVersion'];
	$suffixVersion = 'v' . (!empty($arcadeModSettings['arcadeGameTesting']) ? str_pad(dechex(mt_rand(0, 0xFFFFF)), 5, '0', STR_PAD_LEFT) : preg_replace('/[^0-9a-zA-Z]+/', '', $arcade_version));
	echo arcadeArenaTemplate();

	echo '
	<div class="matchmobilebg" id="arenamatch">
		<div class="match">
			<div id="contain">
				<div class="title">
					<span>', $txt['arcade_new_match'], '</span>
				</div>
				<form action="', $scripturl, '?action=arcade;sa=newMatch2;reload=' . mt_rand(0, 9999) . ';#arenamatch" method="post">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="segnum" value="', $context['form_sequence_number'], '" />';

	if (!empty($context['errors']))
		echo '
					<div>
						<div style="color: red; margin: 1ex 0 2ex 3ex;" id="error_list">
							', implode('<br />', $context['errors']), '
						</div>
					</div>';

	echo '
					<div>
						<span class="topslice"><span>&nbsp;</span></span>
						<div style="padding: 0 0.5em">
							<div class="rowinfo">
								<div style="display: table;width: 100%;">
									<div style="display: table-row;">
										<div style="display: table-cell;" class="rowinfo"><label for="match_name">', $txt['arcade_match_name'], '</label></div>
										<div style="display: table-cell;"><input type="text" name="match_name" id="match_name"  value="', $context['match']['name'], '" style="width: 50%"/></div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;" class="rowinfo"><label for="game_mode">', $txt['game_mode'], '</label></div>
										<div style="display: table-cell;" class="rowinfo">
											<select name="game_mode" id="game_mode">
												<option value="normal"', $context['match']['game_mode'] == 'normal' ? ' selected="selected"' : '', '>', $txt['game_mode_normal'], '</option>
												<option value="knockout"', $context['match']['game_mode'] == 'knockout' ? ' selected="selected"' : '', '>', $txt['game_mode_knockout'], '</option>
											</select><br />
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;" class="rowinfo" style="vertical-align: top;"><label for="player">', $txt['players'], '</label></div>
										<div style="display: table-cell;" class="rowinfo">
											<input type="text" name="player" id="player" style="width: 50%;" />
											<div><input class="button_submit" name="player_submit" value="', $txt['player_add'], '" onclick="return oPlayerSuggest.onSubmit();" type="submit" /></div>
											<div id="player_container"></div>
											<script type="text/javascript" src="', $settings['default_theme_url'], '/arcade_scripts/suggest.js?' . $suffixVersion . '"></script>
											<script type="text/javascript"><!-- // --><![CDATA[
												var oPlayerSuggest = new smc_AutoSuggest({
													sSelf: \'oPlayerSuggest\',
													sSessionVar: \'', $context['session_var'], '\',
													sSessionId: \'', $context['session_id'], '\',
													sSuggestId: \'player\',
													sControlId: \'player\',
													sSearchType: \'member\',
													bItemList: true,
													sPostName: \'players_list\',
													sURLMask: \'action=profile;u=%item_id%\',
													sItemListContainerId: \'player_container\',
													aListItems:
													[';

	foreach ($context['players'] as $member)
		echo '
														{
															sItemId: ', JavaScriptEscape($member['id']), ',
															sItemName: ', JavaScriptEscape($member['name']), '
														}', $member['id'] == $context['last_player_id'] ? '' : ',';

		echo '
													]
												});
											// ]]></script>
											<noscript>';

	foreach ($context['players'] as $member)
		echo '
												<div>
													<input type="hidden" name="players_list[]" value="', $member['id'], '" />
													<a href="', $scripturl, '?action=profile;u=', $member['id'], '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">', $member['name'], '</a>
													', $user_info['id'] == $member['id'] ? '' : '<input type="image" name="delete_player" value="' . $member['id'] . '" src="' . $settings['default_images_url'] . '/arc_icons/pm_recipient_delete.gif" alt="' . $txt['player_remove'] . '" />', '
												</div>';

	echo '
											</noscript>
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;" class="rowinfo"><label for="num_players">', $txt['num_players'], '</label></div>
										<div style="display: table-cell;" class="rowinfo">
											<input type="text" name="num_players" id="num_players" value="', $context['match']['num_players'], '" style="width: 50%;" />
											<div style="position: relative;width: 70vw;padding: 0em !important;line-height: 1em;" class="smalltext">', $txt['num_players_help'], '</div>
										</div>
									</div>
									<div style="display: table-row;">
										<div style="display: table-cell;" class="rowinfo" style="vertical-align: top;"><label for="arenagame">', $txt['arcade_enter_games'], '</label></div>
										<div style="display: table-cell;" class="rowinfo">
											<input id="arenagame" style="width: 50%;" type="text" name="arenagame_input" value="" />
											<div><input class="button_submit" type="submit" name="arenagame_submit" value="', $txt['add_game'], '" onclick="return gameSelectorarenagame.onSubmit();" /></div>
											<div id="suggest_arenagame" class="game_suggest"></div>
											<script type="text/javascript"><!-- // --><![CDATA[
												var gameSelectorarenagame = new gameSelector("', $context['session_id'], '", "arenagame");
											// ]]></script>';

	foreach ($context['match']['rounds'] as $i => $game)
	{
		$game = $context['games'][$game];

		echo '
											<div id="suggest_template_arenagame_', $i, '">
												<input type="hidden" name="rounds[', $i, ']" value="', $game['id'], '" />
												<a href="', $scripturl, '?action=arcade;game=', $game['id'], '" id="game_link_to_', $i, '" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">', $game['name'], '</a>
												<input type="image" name="delete_round" value="', $i, '" onclick="return gameSelectorarenagame.deleteItem(', $i, ');" src="', $settings['default_images_url'], '/arc_icons/pm_recipient_delete.gif" alt="', $txt['player_remove'], '" /></a>
											</div>';
	}

	echo '
											<div id="suggest_template_arenagame" style="visibility: hidden; display: none;">
												<input type="hidden" name="rounds[]" value="::GAME_ID::" />
												<a href="', $scripturl, '?action=arcade;game=::GAME_ID::" id="game_link_to_::ROUND::" class="extern" onclick="window.open(this.href, \'_blank\'); return false;">::GAME_NAME::</a>
												<input type="image" onclick="return \'::DELETE_ROUND_URL::\'" src="', $settings['default_images_url'], '/arc_icons/pm_recipient_delete.gif" alt="', $txt['game_remove'], '" />
											</div>
										</div>
									</div>
								</div>
							</div>
							<div style="text-align: right;padding-top: 20px;">
								<input class="button_submit" type="submit" name="continue" value="', $txt['arcade_continue'], '" />
							</div>
						</div>
					</div>
					<span class="botslice"><span>&nbsp;</span></span>
					<br />
				</form>
			</div>
		</div>
	</div>';
}

?>