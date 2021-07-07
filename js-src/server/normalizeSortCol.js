module.exports = normalizeSortCol;

function normalizeSortCol (sortStr) {
	if (!sortStr) return [];
	let sortPairs = sortStr.split(',').map((pair) => {
		pair = pair.split(':');
		pair[1] = pair[1].toUpperCase();
		return pair;
	});
	return sortPairs;
}
