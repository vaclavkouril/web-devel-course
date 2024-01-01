const fs = require('fs');
var solution = require('./solution.js');

/**
 * A mock for FormData object, so we can use it in CLI testing...
 */
class FormData
{
	constructor() {
		this._items = {};
	}

	_flattenItems() {
		const res = [];
		Object.keys(this._items).sort().forEach(key => {
			this._items[key].forEach(value => res.push([ key, value ]));
		});
		return res;
	}

	append(name, value) {
		if (!this._items[name]) this._items[name] = [];
		this._items[name].push(value);
	}

	delete(name) {
		if (this._items[name]) {
			delete this._items[name];
		}
	}

	entries() {
		const data = this._flattenItems();
		return {
			next: function() {
				const done = data.length === 0;
				const value = data.shift();
				return ({ done, value });
			},
			[Symbol.iterator]: function() { return this; }
		};
	}

	get(name) {
		return this._items[name] ? this._items[name][0] : null;
	}

	getAll(name) {
		return this._items[name] ? this._items[name] : [];
	}

	has(name) {
		return Boolean(this._items[name]);
	}

	keys() {
		const data = this._flattenItems().map(([key, value]) => key);
		return {
			next: function() {
				const done = data.length === 0;
				const value = data.shift();
				return ({ done, value });
			},
			[Symbol.iterator]: function() { return this; }
		};
	}

	set(name, value) {
		this._items[name] = [ value ];
	}

	values() {
		const data = this._flattenItems().map(([key, value]) => value);
		return {
			next: function() {
				const done = data.length === 0;
				const value = data.shift();
				return ({ done, value });
			},
			[Symbol.iterator]: function() { return this; }
		};
	}
}


function createFormData(input)
{
	const formData = new FormData();
	input.forEach(({name, value}) => formData.append(name, value));
	return formData;
}


function _compare(expected, actual, ignoreValues = false)
{
	if (typeof(expected) !== typeof(actual)) return false;
	if (Array.isArray(expected)) {
		if (!Array.isArray(actual) || expected.length !== actual.length) return false;
		for (let i = 0; i < expected.length; ++i) {
			if (!_compare(expected[i], actual[i], ignoreValues)) return false;
		}
		return true;
	}
	else if (typeof(expected) === 'object' && expected !== null) {
		const expectedKeys = Object.keys(expected);
		if (expectedKeys.length !== Object.keys(actual).length) return false;

		let res = true;
		expectedKeys.forEach(key => res = res && actual[key] !== undefined
			&& _compare(expected[key], actual[key], ignoreValues));
		return res;
	}
	else
		return ignoreValues || expected === actual;
}


function compareResult(result, actualResult)
{
	return _compare(result, actualResult);
}


function compareErrors(errors, actualErrors)
{
	return _compare(errors, actualErrors, true);
}


function runTests()
{
	let fileName = process.argv.pop();
	let generateResults = false;
	if (fileName === '--generate') {
		generateResults = true;
		fileName = process.argv.pop();
	}

	const tests = JSON.parse(fs.readFileSync(fileName));
	if (!tests || !Array.isArray(tests)) {
		throw new Error(`File ${fileName} does not contain expected data.`);
	}

	let ok = true;
	const res = [];
	tests.forEach(({input, result, errors}, idx) => {
		const formData = createFormData(input);
		const actualErrors = {};
		let actualResult = solution.processFormData(formData, actualErrors);
		
		if (actualResult !== null) {
			try {
				actualResult = JSON.parse(actualResult);
			}
			catch (e) {
				if (generateResults)
					throw e;
				else {
					console.log(`Test #${idx} produced invalid JSON as output:`);
					console.log(actualResult);
					ok = false;
					return;
				}
			}
		}
	
		if (generateResults) {
			res.push({
				input,
				result: actualResult,
				errors: actualErrors
			});
		}
		else {
			
			if (!compareResult(result, actualResult)) {
				console.log(`Test #${idx} has wrong result:`);
				console.log(JSON.stringify(actualResult, undefined, 2));
				console.log("Expected result was:");
				console.log(JSON.stringify(result, undefined, 2));
				ok = false;
			} else if (!compareErrors(errors, actualErrors)) {
				console.log(`Test #${idx} has wrong set of reported errors:`);
				console.log(JSON.stringify(actualErrors, undefined, 2));
				console.log("Expected errors were:");
				console.log(JSON.stringify(errors, undefined, 2));
				ok = false;
			}
			else {
				res.push((result !== null) ? result : errors);
			}
		}
	});

	if (generateResults || ok) {
		console.log(JSON.stringify(res, undefined, generateResults ? 2 : undefined));
	}
}


try {
	runTests();

}
catch (e) {
	console.log("Error: " + e.message);
	process.exit(2);
}
