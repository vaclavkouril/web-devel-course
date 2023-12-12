const fs = require('fs');
var solution = require('./solution.js');

try {
	const fileName = process.argv.pop();
	const { width, height, rects } = JSON.parse(fs.readFileSync(fileName));
	if (!width || !height || !rects) {
		throw new Error(`File ${fileName} does not contain expected data.`);
	}
	const rect = solution.maxFreeRect(width, height, rects);

	console.log(JSON.stringify({
		left: rect.left,
		top: rect.top,
		width: rect.width,
		height: rect.height,
	}));
}
catch (e) {
	console.log("Error: " + e.message);
	process.exit(1);
}
