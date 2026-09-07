<?php
/**
 * MoodMod — Template file
 *
 * Functions defined here:
 *   template_moodmod_profile          — user-facing mood picker
 *   template_moodmod_admin_settings   — admin settings page
 *   template_moodmod_admin_moods      — admin mood list
 *   template_moodmod_admin_edit_mood  — admin add/edit mood form
 */

if (!defined('SMF'))
	die('Hacking attempt...');

// User Profile - Mood Picker
function template_moodmod_profile()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="moodmod_profile" class="cat_bar">
		<h3 class="catbg">', $txt['moodmod_profile_tab'], '</h3>
	</div>
	<div class="windowbg">';

	// Access denied message.
	if (!empty($context['moodmod_denied']))
	{
		echo '
		<p class="noticebox">', $txt['moodmod_not_allowed'], '</p>
	</div>';
		return;
	}

	// Saved confirmation.
	if (!empty($context['moodmod_saved']))
		echo '
		<p class="infobox">', $txt['moodmod_saved'], '</p>';

	echo '
		<form action="', $context['moodmod_action'], '" method="post">
			<div class="moodmod-picker">
				<p class="moodmod-instructions">', $txt['moodmod_pick_instructions'], '</p>
				<div class="moodmod-grid">';

	// "No mood" option first.
	$mood_none = $context['moodmod_moods'][0];
	echo '
					<label class="moodmod-option', ($context['moodmod_current'] === 0 ? ' moodmod-selected' : ''), '">
						<input type="radio" name="mood_id" value="0"',
							($context['moodmod_current'] === 0 ? ' checked="checked"' : ''), '>
						<span class="moodmod-emoji moodmod-none">&#x2205;</span>
						<span class="moodmod-label">', $txt['moodmod_no_mood'], '</span>
					</label>';

	// All active moods.
	foreach ($context['moodmod_moods'] as $id => $mood)
	{
		if ($id === 0)
			continue;

		$selected = ((int) $context['moodmod_current'] === (int) $id);

		echo '
					<label class="moodmod-option', ($selected ? ' moodmod-selected' : ''), '" title="', htmlspecialchars($mood['description']), '">
						<input type="radio" name="mood_id" value="', (int) $id, '"',
							($selected ? ' checked="checked"' : ''), '>
						<span class="moodmod-emoji">', htmlspecialchars($mood['emoji'], ENT_QUOTES, 'UTF-8'), '</span>
						<span class="moodmod-label">', htmlspecialchars($mood['name']), '</span>
					</label>';
	}

	echo '
				</div><!-- .moodmod-grid -->
			</div><!-- .moodmod-picker -->';

	// --- Badge background colour picker -----------------------------------
	$current_color = !empty($context['moodmod_color']) ? $context['moodmod_color'] : '';
	$presets = array('#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#1abc9c', '#3498db', '#9b59b6', '#34495e');

	echo '
			<div class="moodmod-color">
				<p class="moodmod-instructions">', $txt['moodmod_color_instructions'], '</p>
				<div class="moodmod-swatches">
					<button type="button" class="moodmod-swatch moodmod-swatch-none', ($current_color === '' ? ' moodmod-swatch-active' : ''), '" data-color="" title="', htmlspecialchars($txt['moodmod_color_default']), '">&#x2205;</button>';

	foreach ($presets as $hex)
		echo '
					<button type="button" class="moodmod-swatch', (strcasecmp($current_color, $hex) === 0 ? ' moodmod-swatch-active' : ''), '" data-color="', $hex, '" style="background:', $hex, '" title="', $hex, '"></button>';

	$color_is_preset = false;
	foreach ($presets as $hex)
		if (strcasecmp($current_color, $hex) === 0)
			$color_is_preset = true;
	$color_is_custom = ($current_color !== '' && !$color_is_preset);

	echo '
					<span class="moodmod-swatch moodmod-swatch-custom', ($color_is_custom ? ' moodmod-swatch-active' : ''), '" id="moodmod_color_trigger" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false" title="', htmlspecialchars($txt['moodmod_color_custom']), '"', ($color_is_custom ? ' style="background:' . htmlspecialchars($current_color) . '"' : ''), '>
						<span class="moodmod-swatch-custom-icon">&#127912;</span>
					</span>
				</div>

				<div class="moodmod-cp" id="moodmod_cp" hidden>
					<div class="moodmod-cp-row">
						<span class="moodmod-cp-label">', $txt['moodmod_color_hue'], '</span>
						<input type="range" class="moodmod-cp-slider moodmod-cp-hue" id="moodmod_cp_hue" min="0" max="360" value="210" aria-label="', htmlspecialchars($txt['moodmod_color_hue']), '">
					</div>
					<div class="moodmod-cp-row">
						<span class="moodmod-cp-label">', $txt['moodmod_color_sat'], '</span>
						<input type="range" class="moodmod-cp-slider moodmod-cp-sat" id="moodmod_cp_sat" min="0" max="100" value="65" aria-label="', htmlspecialchars($txt['moodmod_color_sat']), '">
					</div>
					<div class="moodmod-cp-row">
						<span class="moodmod-cp-label">', $txt['moodmod_color_light'], '</span>
						<input type="range" class="moodmod-cp-slider moodmod-cp-light" id="moodmod_cp_light" min="0" max="100" value="55" aria-label="', htmlspecialchars($txt['moodmod_color_light']), '">
					</div>
					<div class="moodmod-cp-foot">
						<span class="moodmod-cp-preview" id="moodmod_cp_preview"></span>
						<input type="text" class="moodmod-cp-hex" id="moodmod_cp_hex" maxlength="7" spellcheck="false" autocapitalize="none" autocomplete="off" value="#3498db" aria-label="Hex">
						<button type="button" class="button moodmod-cp-apply" id="moodmod_cp_apply">', $txt['moodmod_color_set'], '</button>
					</div>
				</div>

				<input type="hidden" name="mood_color" id="moodmod_color_value" value="', htmlspecialchars($current_color), '">
			</div><!-- .moodmod-color -->

			<div class="moodmod-submit">
				<input type="submit" name="moodmod_save" value="', $txt['moodmod_save'], '" class="button">
			</div>

			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="hidden" name="', $context['token_var'], '" value="', $context['token'], '">
		</form>
	</div><!-- .windowbg -->

	<script>
	(function () {
		var hidden   = document.getElementById("moodmod_color_value");
		var swatches = document.querySelectorAll(".moodmod-swatches .moodmod-swatch");
		var trigger  = document.getElementById("moodmod_color_trigger");
		var cp       = document.getElementById("moodmod_cp");
		if (!hidden) return;

		function setActive(el) {
			for (var i = 0; i < swatches.length; i++)
				swatches[i].classList.remove("moodmod-swatch-active");
			if (el) el.classList.add("moodmod-swatch-active");
		}

		function closeCp() {
			if (!cp) return;
			cp.hidden = true;
			if (trigger) trigger.setAttribute("aria-expanded", "false");
		}

		/* --- Preset / default swatches ----------------------------------- */
		for (var i = 0; i < swatches.length; i++) {
			(function (sw) {
				if (sw === trigger) return;
				sw.addEventListener("click", function () {
					hidden.value = sw.getAttribute("data-color") || "";
					setActive(sw);
					if (trigger) trigger.style.background = "";   /* restore gradient */
					closeCp();
				});
			})(swatches[i]);
		}

		/* --- Custom HSL slider picker (works identically on mobile) ------- */
		var hueEl  = document.getElementById("moodmod_cp_hue");
		var satEl  = document.getElementById("moodmod_cp_sat");
		var litEl  = document.getElementById("moodmod_cp_light");
		var hexEl  = document.getElementById("moodmod_cp_hex");
		var prevEl = document.getElementById("moodmod_cp_preview");
		var applyEl = document.getElementById("moodmod_cp_apply");

		if (cp && hueEl && satEl && litEl) {
			function hslToHex(h, s, l) {
				s /= 100; l /= 100;
				var c = (1 - Math.abs(2 * l - 1)) * s;
				var x = c * (1 - Math.abs((h / 60) % 2 - 1));
				var m = l - c / 2, r = 0, g = 0, b = 0;
				if      (h <  60) { r = c; g = x; }
				else if (h < 120) { r = x; g = c; }
				else if (h < 180) { g = c; b = x; }
				else if (h < 240) { g = x; b = c; }
				else if (h < 300) { r = x; b = c; }
				else              { r = c; b = x; }
				function hx(v) { var t = Math.round((v + m) * 255).toString(16); return t.length < 2 ? "0" + t : t; }
				return "#" + hx(r) + hx(g) + hx(b);
			}
			function hexToHsl(hex) {
				var mm = /^#?([0-9a-f]{6})$/i.exec(String(hex).replace(/\s/g, ""));
				if (!mm) return null;
				var n = parseInt(mm[1], 16);
				var r = ((n >> 16) & 255) / 255, g = ((n >> 8) & 255) / 255, b = (n & 255) / 255;
				var max = Math.max(r, g, b), min = Math.min(r, g, b), h, s, l = (max + min) / 2;
				if (max === min) { h = 0; s = 0; }
				else {
					var d = max - min;
					s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
					if (max === r)      h = (g - b) / d + (g < b ? 6 : 0);
					else if (max === g) h = (b - r) / d + 2;
					else                h = (r - g) / d + 4;
					h *= 60;
				}
				return { h: Math.round(h), s: Math.round(s * 100), l: Math.round(l * 100) };
			}

			function paint(h, s, l, hex) {
				prevEl.style.background = hex;
				satEl.style.setProperty("--mm-sat-grad",
					"linear-gradient(to right, hsl(" + h + ",0%," + l + "%), hsl(" + h + ",100%," + l + "%))");
				litEl.style.setProperty("--mm-light-grad",
					"linear-gradient(to right, #000, hsl(" + h + "," + s + "%,50%), #fff)");
			}
			function selectCustom(hex) {
				hidden.value = hex;
				if (trigger) trigger.style.background = hex;
				setActive(trigger);
			}

			function fromSliders() {
				var h = +hueEl.value, s = +satEl.value, l = +litEl.value;
				var hex = hslToHex(h, s, l);
				hexEl.value = hex;
				paint(h, s, l, hex);
				selectCustom(hex);
			}
			hueEl.addEventListener("input", fromSliders);
			satEl.addEventListener("input", fromSliders);
			litEl.addEventListener("input", fromSliders);

			hexEl.addEventListener("input", function () {
				var hsl = hexToHsl(hexEl.value);
				if (!hsl) return;
				hueEl.value = hsl.h; satEl.value = hsl.s; litEl.value = hsl.l;
				var hex = "#" + /([0-9a-f]{6})/i.exec(hexEl.value)[1].toLowerCase();
				paint(hsl.h, hsl.s, hsl.l, hex);
				selectCustom(hex);
			});

			function openCp() {
				var hsl = hexToHsl(hidden.value);
				if (hsl) { hueEl.value = hsl.h; satEl.value = hsl.s; litEl.value = hsl.l; }
				var h = +hueEl.value, s = +satEl.value, l = +litEl.value;
				var hex = hslToHex(h, s, l);
				hexEl.value = hex;
				paint(h, s, l, hex);   /* show only — does not commit until user edits */
				cp.hidden = false;
				if (trigger) trigger.setAttribute("aria-expanded", "true");
			}
			function toggleCp() { cp.hidden ? openCp() : closeCp(); }

			if (trigger) {
				trigger.addEventListener("click", function (e) { e.stopPropagation(); toggleCp(); });
				trigger.addEventListener("keydown", function (e) {
					if (e.key === "Enter" || e.key === " " || e.keyCode === 13 || e.keyCode === 32) {
						e.preventDefault(); toggleCp();
					}
				});
			}
			if (applyEl) applyEl.addEventListener("click", closeCp);

			/* Tap outside the popover closes it. */
			document.addEventListener("click", function (e) {
				if (cp.hidden) return;
				if (cp.contains(e.target) || (trigger && trigger.contains(e.target))) return;
				closeCp();
			});
		}
	})();
	</script>';
}

// ADMIN - Settings

function template_moodmod_admin_settings()
{
	global $context, $txt, $scripturl;

	if (!empty($context['moodmod_saved']))
		echo '<div class="infobox">', $txt['moodmod_settings_saved'], '</div>';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['moodmod_tab_settings'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=admin;area=moodmod;sa=settings" method="post">

			<dl class="settings">

				<dt>
					<label for="moodmod_enabled"><strong>', $txt['moodmod_enabled_label'], '</strong></label>
					<span class="smalltext">', $txt['moodmod_enabled_desc'], '</span>
				</dt>
				<dd>
					<input type="checkbox" name="moodmod_enabled" id="moodmod_enabled" value="1"',
						(!empty($context['moodmod_enabled']) ? ' checked="checked"' : ''), '>
				</dd>

				<dt>
					<label for="moodmod_show_in_posts"><strong>', $txt['moodmod_show_posts_label'], '</strong></label>
					<span class="smalltext">', $txt['moodmod_show_posts_desc'], '</span>
				</dt>
				<dd>
					<input type="checkbox" name="moodmod_show_in_posts" id="moodmod_show_in_posts" value="1"',
						(!empty($context['moodmod_show_in_posts']) ? ' checked="checked"' : ''), '>
				</dd>

				<dt>
					<label for="moodmod_allowed_groups"><strong>', $txt['moodmod_groups_label'], '</strong></label>
					<span class="smalltext">', $txt['moodmod_groups_desc'], '</span>
				</dt>
				<dd>
					<select name="moodmod_allowed_groups[]" id="moodmod_allowed_groups" multiple="multiple" size="8" style="min-width:220px">';

	foreach ($context['moodmod_all_groups'] as $gid => $gname)
		echo '
						<option value="', (int) $gid, '"',
							(in_array((int) $gid, $context['moodmod_allowed_groups']) ? ' selected="selected"' : ''), '>',
							htmlspecialchars($gname), '</option>';

	echo '
					</select>
					<br><span class="smalltext">', $txt['moodmod_groups_hint'], '</span>
				</dd>

			</dl>

			<div class="moodmod-submit">
				<input type="submit" name="save_moodmod_settings" value="', $txt['moodmod_save'], '" class="button">
			</div>

			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="hidden" name="', $context['token_var'], '" value="', $context['token'], '">
		</form>
	</div>';
}

// ADMIN - Mood List

function template_moodmod_admin_moods()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['moodmod_tab_moods'], '
			<a href="', $scripturl, '?action=admin;area=moodmod;sa=editmood" class="floatright">
				+ ', $txt['moodmod_add_mood'], '
			</a>
		</h3>
	</div>
	<div class="windowbg">';

	if (empty($context['moodmod_moods']))
	{
		echo '<p class="noticebox">', $txt['moodmod_no_moods'], '</p>';
	}
	else
	{
		echo '
		<table class="table_grid moodmod-table">
			<thead>
				<tr class="title_bar">
					<th class="moodmod-th-emoji">', $txt['moodmod_col_emoji'], '</th>
					<th>', $txt['moodmod_col_name'], '</th>
					<th>', $txt['moodmod_col_description'], '</th>
					<th class="moodmod-th-order">', $txt['moodmod_col_order'], '</th>
					<th class="moodmod-th-active">', $txt['moodmod_col_active'], '</th>
					<th class="moodmod-th-actions">', $txt['moodmod_col_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['moodmod_moods'] as $mood)
			echo '
				<tr class="windowbg">
					<td class="moodmod-td-emoji">', htmlspecialchars($mood['emoji'], ENT_QUOTES, 'UTF-8'), '</td>
					<td>', htmlspecialchars($mood['name']), '</td>
					<td>', htmlspecialchars($mood['description']), '</td>
					<td class="moodmod-td-center">', (int) $mood['sort_order'], '</td>
					<td class="moodmod-td-center">',
						($mood['active'] ? '<span class="moodmod-status-active">&#10003;</span>' : '<span class="moodmod-status-inactive">&#10007;</span>'),
					'</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=moodmod;sa=editmood;mood=', (int) $mood['id_mood'], '"
						   class="button">', $txt['modify'], '</a>
						<a href="', $scripturl, '?action=admin;area=moodmod;sa=deletemood;mood=', (int) $mood['id_mood'],
						   ';', $context['session_var'], '=', $context['session_id'],
						   ';', $context['moodmod_delete_token_var'], '=', $context['moodmod_delete_token'], '"
						   class="button moodmod-btn-delete"
						   onclick="return confirm(', JavaScriptEscape($txt['moodmod_confirm_delete']), ')">',
						   $txt['delete'], '</a>
					</td>
				</tr>';

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
}

// ADMIN - Add/Edit Mood

function template_moodmod_admin_edit_mood()
{
	global $context, $txt, $scripturl;

	$mood = $context['moodmod_mood'];
	$editing = ((int) $mood['id_mood'] > 0);

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', ($editing ? $txt['moodmod_edit_mood'] : $txt['moodmod_add_mood']), '</h3>
	</div>
	<div class="windowbg">';

	if (!empty($context['moodmod_error']))
		echo '<p class="errorbox">', $context['moodmod_error'], '</p>';

	echo '
		<form action="', $scripturl, '?action=admin;area=moodmod;sa=editmood',
			($editing ? ';mood=' . (int) $mood['id_mood'] : ''), '"
		      method="post">

			<dl class="settings">

				<dt>
					<label for="mood_emoji"><strong>', $txt['moodmod_field_emoji'], '</strong></label>
					<span class="smalltext">', $txt['moodmod_field_emoji_hint'], '</span>
				</dt>
				<dd>
					<input type="text" name="mood_emoji" id="mood_emoji"
					       value="', htmlspecialchars(html_entity_decode($mood['emoji'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8'), '"
					       style="width:80px;font-size:1.4em" maxlength="100">
					<button type="button" id="moodmod_emoji_toggle" class="button">', $txt['moodmod_choose_emoji'], '</button>
					<span id="moodmod_preview" style="font-size:2em;vertical-align:middle;margin-left:8px">',
					       htmlspecialchars($mood['emoji'], ENT_QUOTES, 'UTF-8'), '</span>
					<div id="moodmod_emoji_picker" class="moodmod-emoji-picker" hidden>
						<div class="moodmod-emoji-tabs"></div>
						<div class="moodmod-emoji-grid"></div>
					</div>
				</dd>

				<dt>
					<label for="mood_name"><strong>', $txt['moodmod_field_name'], '</strong></label>
				</dt>
				<dd>
					<input type="text" name="mood_name" id="mood_name"
					       value="', htmlspecialchars($mood['name']), '"
					       style="width:220px" maxlength="80">
				</dd>

				<dt>
					<label for="mood_description"><strong>', $txt['moodmod_field_desc'], '</strong></label>
				</dt>
				<dd>
					<input type="text" name="mood_description" id="mood_description"
					       value="', htmlspecialchars($mood['description']), '"
					       style="width:340px" maxlength="255">
				</dd>

				<dt>
					<label for="sort_order"><strong>', $txt['moodmod_field_order'], '</strong></label>
					<span class="smalltext">', $txt['moodmod_field_order_hint'], '</span>
				</dt>
				<dd>
					<input type="number" name="sort_order" id="sort_order"
					       value="', (int) $mood['sort_order'], '" min="0" style="width:80px">
				</dd>

				<dt>
					<label for="mood_active"><strong>', $txt['moodmod_field_active'], '</strong></label>
				</dt>
				<dd>
					<input type="checkbox" name="active" id="mood_active" value="1"',
					       (!empty($mood['active']) ? ' checked="checked"' : ''), '>
				</dd>

			</dl>

			<div class="moodmod-submit">
				<input type="submit" name="save_mood" value="', $txt['moodmod_save'], '" class="button">
				<a href="', $scripturl, '?action=admin;area=moodmod;sa=moods" class="button">', (!empty($txt['moodmod_cancel']) ? $txt['moodmod_cancel'] : (!empty($txt['cancel']) ? $txt['cancel'] : 'Cancel')), '</a>
			</div>

			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="hidden" name="', $context['token_var'], '" value="', $context['token'], '">
		</form>
	</div>

	<script>
	(function () {
		var emojiInput   = document.getElementById("mood_emoji");
		var emojiPreview = document.getElementById("moodmod_preview");
		var toggle       = document.getElementById("moodmod_emoji_toggle");
		var picker       = document.getElementById("moodmod_emoji_picker");
		if (!emojiInput) return;

		function refreshPreview() {
			if (emojiPreview) emojiPreview.textContent = emojiInput.value;
		}
		emojiInput.addEventListener("input", refreshPreview);

		/* Curated, dependency-free emoji set grouped into tabs. */
		var CATEGORIES = [
			["😃", "😀😃😄😁😆😅😂🤣😊😇🙂🙃😉😌😍🥰😘😋😛😜🤪😝🤗🤔🤨😐😑😶😏😒🙄😬😴😪😮😲🥱😕😟🙁☹️😣😖😫😩🥺😢😭😤😠😡🤬🤯😳🥵🥶😨😰😥😓😎🤓🤠"],
			["👍", "👍👎👌✌️🤞🤟🤘🤙👏🙌🙏💪🤝👋🦚✋👊🤛🤜🖐️"],
			["❤️", "❤️🧡💛💚💙💜🖤🤍🤎💔❣️💕💞💓💗💖💘💝⭐🌟✨⚡🔥💯"],
			["🐶", "🐶🐱🐭🐹🐰🦊🐻🐼🐨🐯🦁🐮🐷🐸🐵🦄🐝🦋🌸🌹🌻🌈🍀"],
			["🍕", "🍕🍔🍟🌮🍦🍩🍪🎂☕🍺🎮🎧🎵⚽🏀🎯🎉🎁"]
		];

		var grid = picker ? picker.querySelector(".moodmod-emoji-grid") : null;
		var tabs = picker ? picker.querySelector(".moodmod-emoji-tabs") : null;

		function insertEmoji(ch) {
			var start = emojiInput.selectionStart, end = emojiInput.selectionEnd;
			if (typeof start === "number" && typeof end === "number") {
				var v = emojiInput.value;
				emojiInput.value = (v.slice(0, start) + ch + v.slice(end)).slice(0, 100);
				var pos = start + ch.length;
				emojiInput.setSelectionRange(pos, pos);
			} else {
				emojiInput.value = (emojiInput.value + ch).slice(0, 100);
			}
			emojiInput.focus();
			refreshPreview();
		}

		function showCategory(idx) {
			if (!grid) return;
			grid.innerHTML = "";
			var chars = Array.from(CATEGORIES[idx][1]);
			for (var i = 0; i < chars.length; i++) {
				(function (ch) {
					var b = document.createElement("button");
					b.type = "button";
					b.className = "moodmod-emoji-cell";
					b.textContent = ch;
					b.addEventListener("click", function () { insertEmoji(ch); });
					grid.appendChild(b);
				})(chars[i]);
			}
			if (tabs) {
				var t = tabs.children;
				for (var k = 0; k < t.length; k++)
					t[k].classList.toggle("moodmod-emoji-tab-active", k === idx);
			}
		}

		if (grid && tabs) {
			for (var c = 0; c < CATEGORIES.length; c++) {
				(function (idx) {
					var t = document.createElement("button");
					t.type = "button";
					t.className = "moodmod-emoji-tab";
					t.textContent = CATEGORIES[idx][0];
					t.addEventListener("click", function () { showCategory(idx); });
					tabs.appendChild(t);
				})(c);
			}
			showCategory(0);
		}

		if (toggle && picker)
			toggle.addEventListener("click", function () {
				picker.hidden = !picker.hidden;
			});
	})();
	</script>';
}
