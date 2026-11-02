(function () {
	'use strict';

	function initPicker() {
		const grid = document.querySelector('.moodmod-grid');
		if (!grid) {
			return;
		}
		const options = grid.querySelectorAll('.moodmod-option');
		for (const option of options) {
			option.addEventListener('click', e => {
				options.forEach(opt => opt.classList.remove('moodmod-selected'));
				e.currentTarget.classList.add('moodmod-selected');
			});
		}
	}

	async function initMoods() {
		const posts = document.querySelectorAll('#forumposts .post_wrapper');
		if (posts.length === 0) {
			return;
		}
		const userPlaceholderPairs = Array.from(posts)
			.map(post => [
				Number(post
					.querySelector('.poster > .user_info > li.avatar > a')
					.href
					.match(/;u=(\d+)$/)[1]),
				post.querySelector('.postinfo .spacer'),
			]);
		const response = await fetch(`${smf_scripturl}?${new URLSearchParams({
			action: 'moodmod',
			sa: 'getmoods',
			users: Array.from(new Set(userPlaceholderPairs.map(([id]) => id)))
				.join(','),
		})}`);
		const moods = await response.json();
		for (const [userId, spacer] of userPlaceholderPairs) {
			if (!moods[userId]) {
				continue;
			}
			const {emoji, name, description, color} = moods[userId];

			const status = document.createElement('div');
			status.classList.add('moodmod');
			spacer.after(status);

			const statusText = document.createElement('span');
			statusText.classList.add('smalltext');
			statusText.innerHTML = 'Status: ';
			status.appendChild(statusText);

			var statusBadge = document.createElement('div');
			statusBadge.classList.add('moodmod-badge');
			statusBadge.title = description || `Feeling ${name}`;
			status.appendChild(statusBadge);
			if (color && /^#[0-9a-fA-F]{6}$/.test(color)) {
				const r = parseInt(color.substr(1, 2), 16);
				const g = parseInt(color.substr(3, 2), 16);
				const b = parseInt(color.substr(5, 2), 16);
				// Relative luminance (0 = black, 1 = white).
				const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
				const lightBg = lum > 0.6;
				statusBadge.style.backgroundColor = color;
				statusBadge.style.color = lightBg ? 'black' : 'white';
				statusBadge.style.borderColor = lightBg ?
					'rgba(0, 0, 0, 0.35)' :
					'rgba(255, 255, 255, 0.35)';
			}

			const statusBadgeName = document.createElement('span');
			statusBadgeName.classList.add('moodmod-badge-name');
			statusBadgeName.textContent = name;
			statusBadge.appendChild(statusBadgeName);

			const statusBadgeEmoji = document.createElement('span');
			statusBadgeEmoji.classList.add('moodmod-badge-emoji');
			statusBadgeEmoji.innerHTML = emoji;
			statusBadge.appendChild(statusBadgeEmoji);
		}
	}

	function init() {
		initMoods();
		initPicker();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
