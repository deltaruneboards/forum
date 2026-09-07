<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
if (!defined('SMF'))
	die('Hacking attempt...');

function template_arcade_above()
{
	global $settings, $context, $txt, $arcadeModSettings, $scripturl, $db_count, $user_info;

	$rom = !empty($_SESSION['arcade_rom_initiate']) ? 1 : (isset($_REQUEST['action']) && $_REQUEST['action'] == 'retro_arch' ? 1 : 0);
	$rom2 = !empty($rom) ? '_rom' : '';
	$romManage = !empty($rom) ? 'rom' : '';
	$romTxt = !empty($rom) ? 'rom_' : '';
	$arcadeAction = !empty($rom) ? 'retro_arch' : 'arcade';
	$context['current_arcade_sa'] = !empty($context['current_arcade_sa']) ? $context['current_arcade_sa'] : (isset($_REQUEST['sa']) && is_string($_REQUEST['sa']) ? $_REQUEST['sa'] : '');
	if ($context['current_arcade_sa'] == 'highscore')
		return;

	if (empty($rom))
		echo '
	<div style="display: none;font-size:0.7em;" id="arcadeHiddenInfo">
		<div id="pausecontent0">' . ArcadeInfoBestPlayers((!empty($arcadeModSettings['skin_best_playersB']) ? (int)$arcadeModSettings['skin_best_playersB'] : 5), $rom) . '</div>
		<div id="pausecontent1">' . ArcadeInfoNewestGames((!empty($arcadeModSettings['skin_latest_gamesB']) ? (int)$arcadeModSettings['skin_latest_gamesB'] : 5), $rom) . '</div>
		<div id="pausecontent2">' . Arcade3champsBlock((!empty($arcadeModSettings['skin_latest_chanpsB']) ? (int)$arcadeModSettings['skin_latest_champsB'] : 5), $rom) . '</div>
		<div id="pausecontent3">' . ArcadeInfoMostPlayed((!empty($arcadeModSettings['skin_most_popularB']) ? (int)$arcadeModSettings['skin_most_popularB'] : 5), $rom) . '</div>
		<div id="pausecontent4">' . ArcadeInfoLongestChamps((!empty($arcadeModSettings['skin_longest_champsB']) ? (int)$arcadeModSettings['skin_longest_champsB'] : 5), $rom) . '</div>
		<div id="pausecontent5">' . ArcadeGOTDBlock($rom) . '</div>
		<div id="pausecontent6">' . ArcadeRandomGameBlock($rom) . '</div>
	</div>';
	else
		echo '
	<div style="display: none;font-size:0.7em;" id="arcadeHiddenInfo">
		<div id="pausecontent0">' . ArcadeInfoNewestGames((!empty($arcadeModSettings['skin_latest_gamesB']) ? (int)$arcadeModSettings['skin_latest_gamesB'] : 5), $rom) . '</div>
		<div id="pausecontent1">' . ArcadeInfoMostPlayed((!empty($arcadeModSettings['skin_most_popularB']) ? (int)$arcadeModSettings['skin_most_popularB'] : 5), $rom) . '</div>
		<div id="pausecontent2">' . ArcadeGOTDBlock($rom) . '</div>
		<div id="pausecontent3">' . ArcadeRandomGameBlock($rom) . '</div>
	</div>';

	echo '
	<div><span style="display: none;">&nbsp;</span></div>
	<div class="cat_bar">
		<h3 class="catbg centertext">
			<span class="centertext" style="clear: left;width: 100%;vertical-align: middle;">', $txt[$romTxt . 'arcade'], '</span>
		</h3>
	</div>
	<div class="arcade_up_contain windowbg" style="width: 100%;padding: 0px;border: 0px;">
		<div style="width: 100%;display: inline;border: 0px;" translate="no">';

	$curr = 1;
	$selected = version_compare((!empty($arcadeModSettings['smfVersion']) ? substr($arcadeModSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? ' selected="selected"' : ' selected';
	echo '
			<div class="bordercolor" style="width: 100%;display: block;border: 0px;">', (!empty($context['arcade']['notice']) ? '
				<div style="display: inline-block;width: 100%;" class="windowbg2">
					<div class="centertext alert" style="display: inline-block;padding: 5px;">' . $context['arcade']['notice'] . '</div>
				</div>' : '');

	//start arcade news
	if (!empty($arcadeModSettings['arcadeNewsFader']))
	{
		//CACHE NEWS FADER
		if (($cacheFader = ArcadeInfoFader()) !== '')
		{
			echo'
				<div style="display: block;width: 100%;">
					<div style="display: inline-block;height: 50px;padding: 5px;" class="windowbg2">
						<div style="display: none;">', $cacheFader, '</div>
						<div style="display: inline;">
							<script type="text/javascript"><!-- // --><![CDATA[
								var delay = 10000;
								var maxsteps=30;
								var stepdelay=40;
								var startcolor= new Array(255,255,255);
								var endcolor=new Array(0,0,0);
								var fcontent=new Array();
								var arcadeNewzDiv = arcadeNewsDiv();
								begintag=arcadeNewzDiv[0];
								fcontent = arcadeNewsFader(', (!empty($arcadeModSettings['arcadeNewsNumber']) ? (int)$arcadeModSettings['arcadeNewsNumber'] : 0), ');
								closetag=arcadeNewzDiv[1];
								var fwidth=\'100%\';
								var fheight=\'30px\';
								var ie4=document.all&&!document.getElementById;
								var DOM2=document.getElementById;
								var index=0;
								function changecontent(){
									if (index>=fcontent.length)
										index=0
									if (DOM2){
										document.getElementById("fscroller").innerHTML=begintag+fcontent[index]+closetag
										setTimeout("changecontent()", delay);
									}
									else if (ie4)
										document.all.fscroller.innerHTML=begintag+fcontent[index]+closetag;
									index++;
								}
								if (window.addEventListener)
									window.addEventListener("load", changecontent, false)
								else if (window.attachEvent)
									window.attachEvent("onload", changecontent)
								else if (document.getElementById)
									window.onload=changecontent();
							// ]]></script>
						</div>
					</div>
				</div>';
		}
	}

	echo '
				<div style="display: block;width: 100%;">
					<div class="windowbg2" style="display: inline-block;padding: 5px;vertical-align: top;max-width: 22%;width: 22%;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;height: 25em;">
						<div style="display: block;border-collapse: collapse;width: 100%;border: 0px;">
							<div class="centertext" style="font-style: italic;font-weight: bold;margin-left: auto;margin-right: auto;width: 100%;">', $txt['arcade_info'], '</div>
							<div>
								<div style="padding: 1px;">
									<div class="middletext" style="display: inline;font-size:0.7em;" id="arcadescroller">
										<script type="text/javascript"><!-- // --><![CDATA[
											var pausecontent=new Array();

											', ArcadeInfoPanelBlock(), '
											function pauseescroller(content, divId, divClass, delay)
											{
												this.content=content;
												this.tickerid=divId;
												this.delay=delay;
												this.mouseoverBol=0;
												this.hiddendivpointer=1;
												document.getElementById("arcadescroller").innerHTML = arcadeInfoScroll(divId, divClass, content);
												var escrollerinstance=this;
												if (window.addEventListener)
													window.addEventListener("load", function(){
														escrollerinstance.initialize();
														return true;
													});
												else if (window.attachEvent)
													window.attachEvent("onload", function(){
														escrollerinstance.initialize();
														return true;
													});
												else if (document.getElementById)
													setTimeout(function(){escrollerinstance.initialize();}, 500);
											}
											pauseescroller.prototype.initialize=function(){
												this.tickerdiv=document.getElementById(this.tickerid);
												this.visiblediv=document.getElementById(this.tickerid+"1");
												this.hiddendiv=document.getElementById(this.tickerid+"2");
												this.visibledivtop=parseInt(pauseescroller.getCSSpadding(this.tickerdiv));
												this.visiblediv.style.width=this.hiddendiv.style.width=this.tickerdiv.offsetWidth-(this.visibledivtop*2)+"px";
												this.getinline(this.visiblediv, this.hiddendiv);
												this.hiddendiv.style.visibility="visible";
												var escrollerinstance=this;
												document.getElementById(this.tickerid).onmouseover=function(){escrollerinstance.mouseoverBol=1};
												document.getElementById(this.tickerid).onmouseout=function(){escrollerinstance.mouseoverBol=0};
												if (window.attachEvent)
													window.attachEvent("onunload", function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null;return true;});
												else if (window.addEventListener)
													window.addEventListener("unload", function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null;return true;});
												else if (document.getElementById)
													setTimeout(function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null;}, escrollerinstance.delay);

												setTimeout(function(){escrollerinstance.animateup();}, escrollerinstance.delay);
											}
											pauseescroller.prototype.animateup=function(){
												var escrollerinstance=this;
												if (parseInt(this.hiddendiv.style.top)>(this.visibledivtop+5))
												{
													this.visiblediv.style.top=parseInt(this.visiblediv.style.top)-5+"px";
													this.hiddendiv.style.top=parseInt(this.hiddendiv.style.top)-5+"px";
													setTimeout(function(){escrollerinstance.animateup();}, 50);
												}
												else
												{
													this.getinline(this.hiddendiv, this.visiblediv);
													this.swapdivs();
													setTimeout(function(){escrollerinstance.setmessage();}, this.delay);
												}
											}
											pauseescroller.prototype.swapdivs=function(){
												var tempcontainer=this.visiblediv;
												this.visiblediv=this.hiddendiv;
												this.hiddendiv=tempcontainer;
											}
											pauseescroller.prototype.getinline=function(div1, div2){
												div1.style.top = this.visibledivtop + "px";
												div2.style.top = Math.max(div1.parentNode.offsetHeight, div1.offsetHeight) + "px";
											}
											pauseescroller.prototype.setmessage=function(){
												var escrollerinstance=this;
												if (this.mouseoverBol==1)
													setTimeout(function(){escrollerinstance.setmessage();}, 200);
												else
												{
													var i=this.hiddendivpointer;
													var ceiling=this.content.length;
													this.hiddendivpointer = (i+1>ceiling-1)? 0 : i+1;
													this.hiddendiv.innerHTML=this.content[this.hiddendivpointer];
													this.animateup();
												}
											}
											pauseescroller.getCSSpadding=function(tickerobj){
												if (window.getComputedStyle)
													return window.getComputedStyle(tickerobj, "").getPropertyValue("padding-top");
												else if (tickerobj.currentStyle)
													return tickerobj.currentStyle["paddingTop"];
												else
													return 0;
											}
											new pauseescroller(pausecontent, "pescroller1", "someclass", 6000);
											var myscroller = document.getElementById("pescroller1");
											myscroller.style.height = "30em";
											myscroller.style.fontSize = "x-small";
											myscroller.style.borderWidth = "0px";
											myscroller.style.padding = "1px";
											myscroller.style.position = "relative";
											myscroller.style.overflow = "hidden";
											myscroller.className = "someclass";
										// ]]></script>
										<div><span style="display: none;">&nbsp;</span></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="windowbg" style="display: inline-block;vertical-align: top;padding: 5px;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;max-width: 50%;width: 50%;height: 25em;">
						<div class="centertext">
							<div style="display: block;border: 0px;width: 100%;border-collapse: collapse;">
								<div style="display: inline-block;clear: both;width: 100%;">
									<div style="display: inline-block;padding: 1px;max-width: 25%;width: 25%;">
										<div class="centertext"><span style="font-style: italic;"><strong>', $txt['arcade_u_b_1'], '&nbsp;', $user_info['name'], '</strong></span></div>
									</div>
								</div>
								<div style="width: 100%;">
									<div style="display: inline-block;padding: 1px;height: 155px;max-width: 25%;">
										<div class="centertext">
											', (!empty($context['user']['avatar']['href']) ? '<img style="width: ' . $context['arcade_user_avatar'][0] . 'px;height: ' . $context['arcade_user_avatar'][1] . 'px;" alt="&nbsp;" src="' . $context['user']['avatar']['href'] . '" />' : '<img style="border: 0px;width:' . (!empty($arcadeModSettings['skin_avatar_sizeb_width']) && (int)$arcadeModSettings['skin_avatar_sizeb_width'] > 0 ? $arcadeModSettings['skin_avatar_sizeb_width'] . 'px;' : '30px;') . 'height: ' . (!empty($arcadeModSettings['skin_avatar_sizeb_height']) && (int)$arcadeModSettings['skin_avatar_sizeb_height'] > 0 ? $arcadeModSettings['skin_avatar_sizeb_height'] . 'px;' : '30px;') . '" src="' . $settings['default_images_url'] . '/arc_icons/noavatar.gif" alt="&nbsp;" title="' . $txt['arcade_info_defavatar'] . '"/>') , '
											<div><span style="display: none;">&nbsp;</span></div>
										</div>
									</div>
								</div>
								<div style="display: inline-block;clear: both;width: 100%;">
									<div style="display: inline-block;padding: 1px;" class="smalltext">
										<div class="centertext">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</div>
									</div>
								</div>
								<div style="display: inline-block;clear: both;width: 100%;">
									<div style="display: inline-block;;padding: 1px;margin: 0; margin-top: 5px; text-align: center;">
										<input size="105" maxlength="100" onkeypress="submitShoutOnEnter(event);" class="largetext" name="the_shout" style="width: 80%;margin-top: 1ex; height: 25px;" id="arcadeShoutInputB" />
										<div style="inline-block"><span style="display: none;">&nbsp;</span></div>
										<input style="margin-top: 4px;" onclick="shoutArcadeNewShoutB()" class="mediumtext" type="submit" id="arcadeShoutSubmit" name="shout" value="', $txt['arcade_shout'], '" />
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="windowbg2" style="display: inline-block;padding: 5px;max-width: 25%;width: 25%;vertical-align: top;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;height: 25em;">
						<div id="arcade_shoutbox" class="centertext" style="display: inline-block;width: 100%;border-collapse: collapse;">
							<div style="display: inline-block;clear: both;width: 25%;">
								<div class="centertext" style="display: inline-block;padding: 0px;font-weight: bold;font-style: italic;">', $txt['arcade_shouts'], '</div>
							</div>
							<div style="display: inline-block;clear: both;width: 100%;">
								<div style="min-width: ' . $context['arcade_shout_widthB'] . '%;display: inline-block;padding: 0px;">
									<div id="arcade_shouts" class="smalltext" style="scrollbar-width: thin;display: inline-block;width: 99%; height: 250px; overflow: auto;font-size:0.7em;">
										', ArcadeInfoShouts($rom), '
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="centertext" style="display: block;clear: both;width: 100%;">
					<div class="windowbg2" style="width: 100%;vertical-align: top;border-top-left-radius: 0px;border-top-right-radius: 0px;">
						<div style="clear: both;display: block;border-collapse: collapse;border: 0px;margin: 0 auto;width: 65%;">
							<div style="display: block;">
								<div style="display: inline-block;padding-bottom: 15px;padding-top: 0px;vertical-align: top;" class="centertext">
									<span style="display: none;">&nbsp;</span>
								</div>
							</div>
							<div style="display: inline-block;margin: 0 auto;text-align: left;float: left;">
								<div style="display: inline-block;padding: 3px;">
									<div style="display: inline-block;padding-left: 15px;">
										<form name="search" action="', $scripturl, '?action=' . $arcadeAction . ';sa=search" method="post" onsubmit="return empty();">
											<div style="display: block;">
												<label for="gamesearch" style="display: block;font-style: italic;font-weight: bold;text-align: center;">', $txt['arcade_search'], '</label>
												<input autocomplete="off" placeholder="' . $txt['arcade_search_placeholder'] . '" maxlength="100" style="width: 180px;" id="gamesearch" type="text" name="name" value="', isset($context['arcade_search']['name']) ? $context['arcade_search']['name'] : '', '">
												<div id="suggest_gamesearch" class="game_suggest"></div>
											</div>
											<script type="text/javascript"><!-- // --><![CDATA[
												var gSuggest = new gameSuggest("', $context['session_id'], '", "gamesearch");
											// ]]></script>
										</form>
									</div>
								</div>
							</div>
							<div style="display: inline-block;margin: 0 auto;text-align: right;float: right;">
								<div style="display: inline-block;padding: 3px;text-align: left;">
									<div style="clear: right;display: inline;">
										<form action="', $scripturl, '?action=' . $arcadeAction . ';sa=list" method="post" id="sortgames">
											<div style="display: block;">
												<label for="arcade_sortby" style="display: block;font-style: italic;font-weight: bold;text-align: center;">', $txt['arcade_defiant_sort_list'], '</label>
												<select title="' . $txt['arcade_defiant_sort_list'] . '" style="width: 180px;" id="arcade_sortby" name="sortby" onchange="window.location.href=this.value;">
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=reset">', $txt['arcade_list_games'], '</option>
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=a2z"' . ($_SESSION['arcade_sortby' . $rom2] === 'a2z' ? $selected : '') . '>', $txt['arcade_nameAZ'], '</option>
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=z2a"' . ($_SESSION['arcade_sortby' . $rom2] === 'z2a' ? $selected : '') . '>', $txt['arcade_nameZA'], '</option>
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=age"' . ($_SESSION['arcade_sortby' . $rom2] === 'age' ? $selected : '') . '>', $txt['arcade_LatestList'], '</option>
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=nocat"' . ($_SESSION['arcade_sortby' . $rom2] === 'nocat' ? $selected : '') . '>', $txt['arcade_LatestListNoCat'], '</option>
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=plays"' . ($_SESSION['arcade_sortby' . $rom2] === 'plays' ? $selected : '') . '>', $txt['arcade_g_i_b_3'], '</option>
													' . (empty($rom) ? '<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=champs"' . ($_SESSION['arcade_sortby' . $rom2] === 'champs' ? $selected : '') . '>' . $txt['arcade_g_i_b_8'] . '</option>' : '') . '
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=plays_reverse"' . ($_SESSION['arcade_sortby' . $rom2] === 'plays_reverse' ? $selected : '') . '>', $txt['arcade_LeastPlayed'], '</option>
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=rating"' . ($_SESSION['arcade_sortby' . $rom2] === 'rating' ? $selected : '') . '>', $txt['arcade_rating_sort'], '</option>', (!$user_info['is_guest'] ? '
													<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=favorites"' . ($_SESSION['arcade_sortby' . $rom2] === 'favorites' ? $selected : '') . '>' . $txt['arcade_u_b_2'] . '</option>' : ''), '
													' . (empty($rom) ? '<option value="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=champion"' . ($_SESSION['arcade_sortby' . $rom2] === 'champion' ? $selected : '') . '>' . $txt['arcade_champion'] . '</option>' : '') . '
												</select>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
						<div style="clear: both;display: block;border-collapse: collapse;border: 0px;margin: 0 auto;width: 90%;padding-bottom: 0.6rem;">
							<div style="display: inline-block;width: 100%;">
								<div class="centertext" style="display: inline-block;padding: 3px;"><hr /></div>
							</div>
							<div style="display: inline-block;width: 100%;">
								<div style="position: relative;margin-left:auto;margin-right: auto;clear: both;width: 100%;padding-top: 0px;padding-bottom: 15px;vertical-align: top;" class="centertext">
									<span style="font-style: italic;font-weight: bold;">', $txt['arcade_Gamecategory'], '</span>
								</div>
							</div>
						</div>
						<div style="margin 0 auto;width: 100%;border-collapse: collapse;border: 0px;padding: 0.6rem 0rem 0rem 0rem;">
							<div style="display: flex;flex-wrap: wrap;flex-direction: row;justify-content: space-evenly;width: 100%;padding-top: 0.6rem;padding-bottom: 0.6rem;">';

	//START CACHE - get the categories from the cache else query them anew
	$flexBasis = strval(100 / (int)$context['arcade_defiant']['per_line']) . '%';
	if (!empty($arcadeModSettings['enable_arcade_cache']))
	{
		if (($cacheCats = cache_get_data('arcade_cats', 604800)) == null)
		{
			$cats = category_games();
			echo '
								<div style="flex: ' . $flexBasis . ';box-sizing: border-box;display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;&nbsp;
									<span style="margin-top: -2rem;height: 5rem;text-align: center;vertical-align: middle;border: 0px;display: inline-flex;flex-direction: column;justify-content: center;" id="arcade_drop_cat">
										<span style="margin-left: 1rem;">
											<a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;category=0">
												<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_show_all'] . '">' . $txt['arcade_AllGames'] . '</span>
											</a>
										</span>
										<span style="margin-left: 1rem;">
											<a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=nocat;">
												<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_info_shownocat'] . '">' . $txt['arcade_info_nocat'] . '</span>
											</a>
										</span>
									</span>
									<img class="icon arcade_cat_span" style="border: 0px;position: relative;vertical-align: middle;border: 0px;width: ', $context['arcade_defiant']['cat_width'],'px;height: ', $context['arcade_defiant']['cat_height'], 'px;" src="', $settings['default_images_url'], '/arc_icons/Unassigned.gif" alt="&nbsp;" title="', $txt['arcade_show_all'], '" />
									<span class="arcade_cat_span" style="display: block;vertical-align: middle;position: relative;overflow: hidden;" title="', $txt['arcade_show_all'], '">&nbsp;', $txt['pdl_unassigned'], '&nbsp;(', $context['arcade_new_no_cats'], ')</span>
								</div>';
			foreach($cats as $id => $tmp)
			{
				if ($curr % $context['arcade_defiant']['per_line'] == 0)
				{
					echo '
							</div>
							<div style="margin: 0 auto;display: flex;box-sizing: border-box;flex-wrap: wrap;flex-direction: row;justify-content: space-evenly;width: 100%;padding: 0.6rem 0rem 0.6rem 0rem;">';
				}
				echo'
								<div style="flex: ' . $flexBasis . ';display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=' . $arcadeAction . ';category=', $tmp['id_category'], '" style="text-decoration: none;">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="', $settings['default_images_url'], '/arc_icons/', $tmp['cat_icon'], '" alt="&nbsp;" title="', sprintf($txt['arcade_info_showcat'], $tmp['category_name']), '" />
									</a>
									<a href="', $scripturl, '?action=' . $arcadeAction . ';category=', $tmp['id_category'], '">
										<span style="display: block;vertical-align: middle;" title="', sprintf($txt['arcade_info_showcat'], $tmp['category_name']), '">&nbsp;', $tmp['category_name'], '&nbsp;(', $tmp['games'], ')</span>
									</a>
								</div>';
				$curr++;
			}
			if ($curr % $context['arcade_defiant']['per_line'] != 0) {
				$remainder = $context['arcade_defiant']['per_line'] - ($curr % $context['arcade_defiant']['per_line']);
				$x = 0;
				if ($remainder > 0) {
					while ($x < $remainder) {
						echo '
									<div style="flex: ' . $flexBasis . ';display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;</div>';
						$x++;
					}
				}
			}
			cache_put_data('arcade_cats', $cats, 604800);
		}
		else
		{
			echo '
								<div style="flex: ' . $flexBasis . ';box-sizing: border-box;display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;&nbsp;
									<span style="margin-top: -2rem;height: 5rem;text-align: center;vertical-align: middle;border: 0px;display: inline-flex;flex-direction: column;justify-content: center;" id="arcade_drop_cat">
										<span style="margin-left: 1rem;">
											<a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;category=0">
												<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_show_all'] . '">' . $txt['arcade_AllGames'] . '</span>
											</a>
										</span>
										<span style="margin-left: 1rem;">
											<a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=nocat;">
												<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_info_shownocat'] . '">' . $txt['arcade_info_nocat'] . '</span>
											</a>
										</span>
									</span>
									<img class="icon arcade_cat_span" style="border: 0px;position: relative;vertical-align: middle;border: 0px;width: ', $context['arcade_defiant']['cat_width'],'px;height: ', $context['arcade_defiant']['cat_height'], 'px;" src="', $settings['default_images_url'], '/arc_icons/Unassigned.gif" alt="&nbsp;" title="', $txt['arcade_show_all'], '" />
									<span class="arcade_cat_span" style="display: block;vertical-align: middle;position: relative;overflow: hidden;" title="', $txt['arcade_show_all'], '">&nbsp;', $txt['pdl_unassigned'], '&nbsp;(', $context['arcade_new_no_cats'], ')</span>
								</div>';
			foreach($cacheCats as $id => $tmp)
			{
				if ($curr % $context['arcade_defiant']['per_line'] == 0)
				{
					echo '
							</div>
							<div style="margin: 0 auto;display: flex;flex-wrap: wrap;flex-direction: row;justify-content: space-evenly;width: 100%;padding: 0.6rem 0rem 0.6rem 0rem;">';
				}
				echo'
								<div style="flex: ' . $flexBasis . ';box-sizing: border-box;display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=' . $arcadeAction . ';category=', $tmp['id_category'], '" style="text-decoration: none;">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="', $settings['default_images_url'], '/arc_icons/', $tmp['cat_icon'], '" alt="&nbsp;" title="', sprintf($txt['arcade_info_showcat'], $tmp['category_name']), '" />
									</a>&nbsp;
									<a href="', $scripturl, '?action=' . $arcadeAction . ';category=', $tmp['id_category'], '">
										<span style="display: block;vertical-align: middle;" title="', sprintf($txt['arcade_info_showcat'], $tmp['category_name']), '">', $tmp['category_name'], '&nbsp;(', $tmp['games'], ')</span>
									</a>
								</div>';
				$curr++;
			}
		}
		if ($curr % $context['arcade_defiant']['per_line'] != 0) {
			$remainder = $context['arcade_defiant']['per_line'] - ($curr % $context['arcade_defiant']['per_line']);
			$x = 0;
			if ($remainder > 0) {
				while ($x < $remainder) {
					echo '
								<div style="flex: ' . $flexBasis . ';display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;</div>';
					$x++;
				}
			}
		}
	}
	else
	{
		$cats = category_games();
		echo '
								<div style="flex: ' . $flexBasis . ';box-sizing: border-box;display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;&nbsp;
									<span style="margin-top: -2rem;height: 5rem;text-align: center;vertical-align: middle;border: 0px;display: inline-flex;flex-direction: column;justify-content: center;" id="arcade_drop_cat">
										<span style="margin-left: 1rem;">
											<a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;category=0">
												<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_show_all'] . '">' . $txt['arcade_AllGames'] . '</span>
											</a>
										</span>
										<span style="margin-left: 1rem;">
											<a href="' . $scripturl . '?action=' . $arcadeAction . ';sa=list;sortby=nocat;">
												<span style="vertical-align: middle;font-style: oblique 60deg;font-family: fangsong;" title="' . $txt['arcade_info_shownocat'] . '">' . $txt['arcade_info_nocat'] . '</span>
											</a>
										</span>
									</span>
									<img class="icon arcade_cat_span" style="border: 0px;vertical-align: middle;position: relative;border: 0px;width: ', $context['arcade_defiant']['cat_width'],'px;height: ', $context['arcade_defiant']['cat_height'], 'px;" src="', $settings['default_images_url'], '/arc_icons/Unassigned.gif" alt="&nbsp;" title="', $txt['arcade_show_all'], '" />
									<span class="arcade_cat_span" style="display: block;vertical-align: middle;overflow: hidden;position: relative;" title="', $txt['arcade_show_all'], '">&nbsp;', $txt['pdl_unassigned'], '&nbsp;(', $context['arcade_new_no_cats'], ')</span>
								</div>';
		foreach($cats as $id => $tmp)
		{
			if ($curr % $context['arcade_defiant']['per_line'] == 0)
			{
				echo '
							</div>
							<div style="margin: 0 auto;display: flex;flex-direction: row;justify-content: space-evenly;width: 100%;padding: 0.6rem 0rem 0.6rem 0rem;">';
			}
			echo'
								<div style="flex: ' . $flexBasis . ';box-sizing: border-box;display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;&nbsp;
									<a href="', $scripturl, '?action=' . $arcadeAction . ';category=', $tmp['id_cat'], '" style="text-decoration: none;">
										<img class="icon" style="vertical-align: middle;border: 0px;width: ',$context['arcade_defiant']['cat_width'],'px;height: ',$context['arcade_defiant']['cat_height'],'px;" src="', $settings['default_images_url'], '/arc_icons/', $tmp['cat_icon'], '" alt="&nbsp;" title="', sprintf($txt['arcade_info_showcat'], $tmp['cat_name']), '" />
									</a>&nbsp;
									<a href="', $scripturl, '?action=' . $arcadeAction . ';category=', $tmp['id_cat'], '">
										<span style="display: block;vertical-align: middle;" title="', sprintf($txt['arcade_info_showcat'], $tmp['cat_name']), '">', $tmp['cat_name'], '&nbsp;(', $tmp['games'], ')</span>
									</a>
								</div>';
			$curr++;
		}
		if ($curr % $context['arcade_defiant']['per_line'] != 0) {
			$remainder = $context['arcade_defiant']['per_line'] - ($curr % $context['arcade_defiant']['per_line']);
			$x = 0;
			if ($remainder > 0) {
				while ($x < $remainder) {
					echo '
								<div style="flex: ' . $flexBasis . ';box-sizing: border-box;display: inline-block;flex-grow: 1;flex-shrink: 0;min-width: ' . $flexBasis . ';width: ' . $flexBasis . ';">&nbsp;</div>';
					$x++;
				}
			}
		}
	}

	echo '
							</div>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
			var myArcadeTimeoutVar;
			function changeArcadeShoutContentB()
			{
				var arcadeInterval = "' . abs(intval($arcadeModSettings['arcade_shout_interval'])) * 1000 . '";
				setInterval(function() {
					var xhttp = new XMLHttpRequest();
					var str = "' . $_SESSION['arcade_shout_session'] . '";
					xhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							var i = 0, arcShoutRefresh = document.getElementById("arcade_shouts");
							if (this.responseText.indexOf(\'<div class="arcadeShout"\') > -1 && arcShoutRefresh) {
								var arcadeShoutHtmlParser = new DOMParser();
								var arcadeShoutHtml = arcadeShoutHtmlParser.parseFromString(this.responseText, "text/html").body;
								var arcadeAllShouts = arcadeShoutHtml.getElementsByClassName("arcadeShout");
								if (typeof arcadeAllShouts == "object") {
									arcShoutRefresh.innerHTML = "";
									var arcadeReverse = [], arcadeNewBox = arcadeAllShouts.length-1;
									for(i=arcadeAllShouts.length;i>0;i--) {
										arcadeReverse[arcadeNewBox] = arcadeAllShouts[i-1];
										arcadeNewBox--;
									}
									for(i=0;i<arcadeReverse.length>0;i++)
										arcShoutRefresh.appendChild(arcadeReverse[i]);
									arcShoutRefresh.scrollTop = 0;
								}
							}
						}
					};
					xhttp.open("GET", "' . $scripturl . '?action=' . $arcadeAction . ';sa=shouts;arcade_shout_session="+str + ";xml", true);
					xhttp.send();
				}, arcadeInterval);
			}' . (!empty($arcadeModSettings['arcade_shout_interval']) ? '
			$(document).ready(function() {
				changeArcadeShoutContentB();
			});' : '') . '
			function changeArcadeShoutContentNowB()
			{
				var xhttp = new XMLHttpRequest();
				var str = "' . $_SESSION['arcade_shout_session'] . '";
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var i = 0, arcShoutRefresh = document.getElementById("arcade_shouts");
						if (this.responseText.indexOf(\'<div class="arcadeShout"\') > -1 && arcShoutRefresh) {
							var arcadeShoutHtmlParser = new DOMParser();
							var arcadeShoutHtml = arcadeShoutHtmlParser.parseFromString(this.responseText, "text/html").body;
							var arcadeAllShouts = arcadeShoutHtml.getElementsByClassName("arcadeShout");
							if (typeof arcadeAllShouts == "object") {
								arcShoutRefresh.innerHTML = "";
								var arcadeReverse = [], arcadeNewBox = arcadeAllShouts.length-1;
								for(i=arcadeAllShouts.length;i>0;i--) {
									arcadeReverse[arcadeNewBox] = arcadeAllShouts[i-1];
									arcadeNewBox--;
								}
								for(i=0;i<arcadeReverse.length>0;i++)
									arcShoutRefresh.appendChild(arcadeReverse[i]);
								arcShoutRefresh.scrollTop = 0;
							}
						}
					}
				};
				xhttp.open("GET", "' . $scripturl . '?action=' . $arcadeAction . ';sa=shouts;arcade_shout_session="+str + ";xml", true);
				xhttp.send();
			}
			function shoutArcadeNewShoutB() {
				if (document.getElementById("arcadeShoutInputB")) {
					if (document.getElementById("arcadeShoutInputB").value)
						shout = encodeURIComponent(document.getElementById("arcadeShoutInputB").value).replace(/\'/g, "%27");
					else
						return false;
				}
				else
					return false;
				document.getElementById("arcadeShoutInputB").value = "";
				setTimeout(function(){
					var xhttpx = new XMLHttpRequest();
					xhttpx.open("POST", "' . $scripturl . '?action=arcade;sa=newShout", true);
					xhttpx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					xhttpx.onreadystatechange = function() {
						if (xhttpx.readyState == 4 && xhttpx.status == 200) {
							var response = xhttpx.responseText;
							changeArcadeShoutContentNowB();
						}
					};
					xhttpx.send("arcade_shout_user_id=" + ' . $user_info['id'] . ' + "&arcade_shout_text=" + shout);
				}, 500);
			}
			function submitShoutOnEnter(event) {
				if (event.keyCode == 13)
					document.getElementById("arcadeShoutSubmit").click();
			}
			$(document).ready(function() {
				$("#arcade_drop_cat").hide();
				$(".arcade_cat_span").hover(function() {
					$(".arcade_cat_span").hide();
					$("#arcade_drop_cat").show();
				},
				function() {
					$("#arcade_drop_cat").hide();
					$(".arcade_cat_span").show();
				});
				$("#arcade_drop_cat").hover(function() {
					$(".arcade_cat_span").hide();
					$("#arcade_drop_cat").show();
				},
				function() {
					$("#arcade_drop_cat").hide();
					$(".arcade_cat_span").show();
				});
			});
			</script>
		</div>
	</div>', ($context['arcade_smf_version'] !== 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="display: inline-block;width: 100%;display: inline;width: 100%;">
		<div style="display: inline;">
			', Arcade_DoToolBarStrip('index', 'left', '', $rom), '
		</div>';

	if ($context['arcade']['stats']['games'] != 0)
			echo '
			<span class="smalltext" style="clear: right;padding:8px 7px 0px 0px;float: right;">', (!empty($context['arcade']['stats']['games']) && $context['current_arcade_sa'] == 'list' ? sprintf($txt['arcade_game_we_have_games'], $context['arcade']['stats']['games']) : '<span style="display: none;">&nbsp;</span>'), '</span>';

	echo '
	</div>', ($context['arcade_smf_version'] == 'v2.1' ? '
	<span class="lowerframe"><span>&nbsp;</span></span>' : ''), '
	<div style="display: inline-block;width: 100%;padding-bottom: 10px;"><span style="display: none;">&nbsp;</span></div>';
}

function template_arcade_below()
{
	global $txt;
	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<a id="bot"></a>', $txt['pdl_arcade_copyright'];
}
?>