document.addEventListener('DOMContentLoaded', () => {
	const candy = document.getElementById('trickCandy');
	const random = (min, max) => Math.random() * (max - min) + min;
	candy.style.top = `${random(20, window.innerHeight)}px`;
	candy.style.left = `${random(20, window.innerWidth)}px`;
	candy.style.display = 'block';
});
