document.addEventListener('DOMContentLoaded', () =>
	document.querySelector('#above_footer a')
		?.addEventListener('click', e => {
			e.preventDefault();
			const goHome = new Audio('/assets/audio/go_home.wav');
			goHome.play();
			const link = e.currentTarget.href;
			goHome.addEventListener('ended', () => window.location.href = link);
		})
);
