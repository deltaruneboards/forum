(function() {
	'use strict';

	function apply(root) {
		if (root.nodeType !== Node.ELEMENT_NODE) {
			return;
		}
		const /** @type {Element} */ queryEl = root.parentElement || root;
		for (const el of queryEl.getElementsByClassName('bgcolor')) {
			el.style.backgroundColor = el.dataset.bgcolor;
		}
		for (const el of queryEl.getElementsByClassName('glow')) {
			const color = el.dataset.color;
			if (!CSS.supports('color', color)) {
				continue;
			}
			el.style.textShadow = `0 0 2px ${color}, 0 0 6px ${color}`;
		}
		for (const img of queryEl.getElementsByTagName('img')) {
			if (img.naturalWidth >= 800 || img.naturalHeight >= 600) {
				img.classList.add('smooth');
			}
		}
	}

	document.addEventListener('DOMContentLoaded', () => {
		apply(document.body);
		new MutationObserver(muts => muts.forEach(m => m.addedNodes.forEach(apply)))
			.observe(document.body, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: ['data-color', 'data-bgcolor', 'style'],
			});
	});
})();
