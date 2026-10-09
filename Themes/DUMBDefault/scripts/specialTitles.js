document.addEventListener('DOMContentLoaded', () => {
	'use strict';
	if (!document.getElementById('forumposts')) {
		return;
	}
	const yourTakingTooLongAudio = new Audio('/assets/audio/yourtakingtoolong.mp3');
	const southOfTheBorderAudio = new Audio('/assets/audio/tropic.mp3');

	document.querySelectorAll('img.group-32').forEach(theYouthfulDays);
	document.querySelectorAll('img.group-34').forEach(title => forecaster(title, 'c'));
	document.querySelectorAll('img.group-35').forEach(title => forecaster(title, 'f'));
	document.querySelectorAll('img.group-50').forEach(roommate);
	document.querySelectorAll('img.group-52').forEach(yourTakingTooLong);
	document.querySelectorAll('img.group-59').forEach(southOfTheBorder);

	function theYouthfulDays(title) {
		title.src = '/assets/titles/theyouthfuldays.png';
		title.parentElement.style.marginBottom = '1em';
		title.parentElement.style.marginLeft = '-5em';
	}

	function forecaster(title, unit) {
		const txt = document.createElement('div');
		txt.textContent = 'Click!';
		txt.classList.add('group-34-35-text');
		txt.addEventListener('click', async () => {
			const response = await fetch('https://api.weatherapi.com/v1/current.json?key=822cd91afbca4a6e835211020261309&q=auto:ip&aqi=no');
			const {current} = await response.json();
			txt.textContent = `${current[`temp_${unit}`]} °${unit.toUpperCase()}`;
		});
		title.parentElement.insertBefore(txt, title);
	}

	function roommate(title) {
		const btn = document.createElement('button');
		btn.classList.add('group-50-playbutton');
		btn.addEventListener('click', () => {
			const track = Math.floor(Math.random() * 38) + 1;
			new Audio(`/assets/audio/flowery_lines/${track}.mp3`).play();
		});
		title.parentElement.append(btn);
		title.parentElement.firstChild.textContent = 'LV99 ';
	}

	function yourTakingTooLong(title) {
		title.src = '/assets/titles/takingtoolong.gif';
		const btn = document.createElement('button');
		btn.classList.add('group-52-playbutton');
		btn.addEventListener('click', () => {
			yourTakingTooLongAudio.play();
		});
		title.parentElement.append(btn);
	}

	function southOfTheBorder(title) {
		const txt = document.createElement('div');
		txt.classList.add('group-59-text');
		title.parentElement.insertBefore(txt, title);
		title.addEventListener('click', () => {
			const oldSrc = title.src;
			title.src = '/Themes/DUMBDefault/images/blank.png';
			title.classList.add('oflove');
			southOfTheBorderAudio.play();
			southOfTheBorderAudio.addEventListener('ended', () => {
				title.classList.remove('oflove');
				title.src = oldSrc;
			});
		});
	}
});
