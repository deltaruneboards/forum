(function() {
	'use strict';

	function apply(root) {
		if (root.nodeType !== Node.ELEMENT_NODE) {
			return;
		}
		for (const el of (root.parentElement || root).getElementsByClassName('bgcolor')) {
			el.style.backgroundColor = el.dataset.bgcolor;
		}
	}

	document.addEventListener('DOMContentLoaded', () => {
		apply(document.body);
		new MutationObserver(muts => muts.forEach(m => m.addedNodes.forEach(apply)))
			.observe(document.body, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: ['data-bgcolor', 'style'],
			});
	});
})();
