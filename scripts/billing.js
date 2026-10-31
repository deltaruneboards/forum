const columns = {
	'Deposit Fee': false,
	'Instant Rebate': false,
	'MySQL Charge': 'Database',
	'Resource Charge': 'Server',
	'Site Charge': 'Site',
	'Storage Charge': 'Storage',
};
const table = {};
let currentDate = '';
for (const row of document.querySelectorAll('.zebra tbody tr')) {
	const dateColumn = row.children[0].textContent;
	const column = columns[row.children[1].textContent];
	const amount = row.children[2].textContent;
	if (column === undefined) throw new Error('Unknown column');
	if (dateColumn) currentDate = dateColumn;
	if (!currentDate) throw new Error('Unknown date');
	if (!amount.startsWith('$-')) continue;
	if (column === false) continue;
	table[currentDate] ??= {};
	table[currentDate][column] = Number(amount.replace(/^\$-/, ''));
}
console.log(Object
	.entries(table)
	.map(([date, values]) => [
		date,
		...Object
			.values(columns)
			.filter(Boolean)
			.map(value => values[value])
	].join('\t')).join('\n'));
