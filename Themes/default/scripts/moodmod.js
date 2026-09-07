/**
 * MoodMod — Front-end badge injection
 */
(function () {
	'use strict';

	/* Guard — integrate_load_theme always defines MoodMod before this runs. */
	if (typeof MoodMod === 'undefined') return;

	var PROFILE_RE = /[?&;]u=(\d+)/;

	/* -----------------------------------------------------------------------
	   Helpers
	----------------------------------------------------------------------- */

	function esc(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function extractUid(href) {
		var m = href.match(PROFILE_RE);
		return m ? m[1] : null;
	}

	var HEX_RE = /^#[0-9a-fA-F]{6}$/;

	/* Apply a user-chosen background colour and pick a readable text colour. */
	function applyBadgeColor(badge, color) {
		if (!color || !HEX_RE.test(color)) return;
		var r = parseInt(color.substr(1, 2), 16),
			g = parseInt(color.substr(3, 2), 16),
			b = parseInt(color.substr(5, 2), 16);
		// Relative luminance (0 = black, 1 = white).
		var lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
		badge.style.background  = color;
		badge.style.color       = lum > 0.6 ? '#000000' : '#ffffff';
		badge.style.borderColor = lum > 0.6 ? 'rgba(0,0,0,0.35)' : 'rgba(255,255,255,0.35)';
	}

	function buildBadge(mood) {
		var d = document.createElement('div');
		d.className = 'moodmod-badge';
		/* Tooltip shows the mood's description; fall back to "Feeling <name>". */
		d.title = (mood.description && mood.description.length) ? mood.description : ('Feeling ' + mood.name);
		d.innerHTML =
			'<span class="moodmod-badge-emoji">' + esc(mood.emoji) + '</span>' +
			'<span class="moodmod-badge-name">'  + esc(mood.name)  + '</span>';
		applyBadgeColor(d, mood.color);
		return d;
	}

	function injectBadge(container, uid) {
		if (!MoodMod.userMoods[uid]) return;
		/* Avoid duplicate badges. */
		if (container.querySelector('.moodmod-badge')) return;

		var badge = buildBadge(MoodMod.userMoods[uid]);

		/*
		 * Insert right after the avatar, falling back to group/position
		 * elements for layouts that don't show an avatar.
		 */
		var anchor =
			container.querySelector('.avatar')        ||
			container.querySelector('.membergroup')   ||
			container.querySelector('.postgroup')     ||
			container.querySelector('.poster-info')   ||
			container.querySelector('.profile_group') ||
			container.querySelector('span.position')  ||
			container.querySelector('.username');

		if (anchor)
			anchor.insertAdjacentElement('afterend', badge);
		else
			container.appendChild(badge);
	}

	/* -----------------------------------------------------------------------
	   Scan the page for profile links and inject badges
	----------------------------------------------------------------------- */

	function processKnownMoods() {
		/* --- Posts: .poster divs ----------------------------------------- */
		if (MoodMod.showInPosts) {
			var posters = document.querySelectorAll('.poster, .poster_details');
			for (var i = 0; i < posters.length; i++) {
				var poster = posters[i];
				var link   = poster.querySelector('a[href*="action=profile"]');
				if (!link) continue;
				var uid = extractUid(link.href);
				if (uid) injectBadge(poster, uid);
			}
		}

		/* --- Profile view sidebar ---------------------------------------- */
		var profileContainers = document.querySelectorAll(
			'#profile_left, .profile_details, #basicinfo, #profileinfo'
		);
		for (var j = 0; j < profileContainers.length; j++) {
			var pc   = profileContainers[j];
			var pln  = pc.querySelector('a[href*="action=profile"]');
			var puid = pln ? extractUid(pln.href) : null;

			/* Profile page: member ID also lives in the URL itself. */
			if (!puid) {
				var pm = window.location.href.match(PROFILE_RE);
				if (pm) puid = pm[1];
			}

			if (puid) injectBadge(pc, puid);
		}
	}

	/* -----------------------------------------------------------------------
	   Collect UIDs on the page whose moods we do NOT yet have,
	   fetch them in one batch, then re-run injection.
	----------------------------------------------------------------------- */

	function fetchMissingMoods() {
		var needed = [];
		var links  = document.querySelectorAll('a[href*="action=profile"]');

		for (var i = 0; i < links.length; i++) {
			var uid = extractUid(links[i].href);
			if (uid && !MoodMod.userMoods.hasOwnProperty(uid) && needed.indexOf(uid) === -1)
				needed.push(uid);
		}

		// Cap at 50 to match the server-side limit and avoid large requests.
		if (needed.length > 50)
			needed = needed.slice(0, 50);

		if (!needed.length || !MoodMod.ajaxUrl) return;

		var xhr = new XMLHttpRequest();
		xhr.open('GET', MoodMod.ajaxUrl + ';sa=getmoods&users=' + needed.join(','), true);
		xhr.onreadystatechange = function () {
			if (xhr.readyState !== 4 || xhr.status !== 200) return;
			try {
				var data = JSON.parse(xhr.responseText);
				for (var uid in data) {
					if (data.hasOwnProperty(uid))
						MoodMod.userMoods[uid] = data[uid];
				}
				processKnownMoods();
			} catch (e) { /* ignore JSON errors */ }
		};
		xhr.send();
	}

	/* -----------------------------------------------------------------------
	   Profile picker UX — highlight selected option on click
	----------------------------------------------------------------------- */

	function initPicker() {
		var grid = document.querySelector('.moodmod-grid');
		if (!grid) return;

		var options = grid.querySelectorAll('.moodmod-option');
		for (var i = 0; i < options.length; i++) {
			options[i].addEventListener('click', function () {
				for (var k = 0; k < options.length; k++)
					options[k].classList.remove('moodmod-selected');
				this.classList.add('moodmod-selected');
			});
		}
	}

	/* -----------------------------------------------------------------------
	   Boot
	----------------------------------------------------------------------- */

	function init() {
		processKnownMoods();   /* instant — uses pre-loaded data */
		fetchMissingMoods();   /* one batch XHR for anything extra */
		initPicker();          /* profile page UX */
	}

	if (document.readyState === 'loading')
		document.addEventListener('DOMContentLoaded', init);
	else
		init();

})();
