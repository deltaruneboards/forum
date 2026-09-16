document.addEventListener('DOMContentLoaded', () => {
	const quote = document.getElementById('random-shop-quote');
	if (!quote) {
		return;
	}
	const quotes = quote.querySelectorAll('p');
	const totalWeight = Array.from(quotes)
		.reduce((acc, el) => acc + Number(el.dataset.weight || 1), 0);
	const randomIndex = Math.floor(Math.random() * totalWeight);
	let currentWeight = 0;
	for (const q of quotes) {
		currentWeight += Number(q.dataset.weight || 1);
		if (currentWeight > randomIndex) {
			q.classList.add('chosen');
			break;
		}
	}
});
