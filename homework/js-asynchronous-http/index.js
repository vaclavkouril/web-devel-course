const fs = require('fs');
var solution = require('./solution.js');

try {
	const API_URL = 'api' + Math.random().toString(36).substring(7) + '.php';
	const DataModel = solution.DataModel;
	const model = new DataModel(API_URL);

	(function(){
		class ResponseMock {
			constructor(jsonBody, initData = {})
			{
				this._body = jsonBody;
				Object.keys(initData).forEach(key => {
					this[key] = initData[key];
				});
				
				// defaults
				this.headers = [];
				this.ok = true;
				this.redirected = false;
				this.status = 200;
				this.statusText = 'OK';
				this.type = 'application/json';
				this.useFinalUrl = true;
				this.body = null;
				this.bodyUsed = false;
			}

			json()
			{
				if (this.bodyUsed) {
					throw new Error('The body has been used.');
				}
				this.bodyUsed = true;
				return Promise.resolve(this._body);
			}


			text()
			{
				if (this.bodyUsed) {
					throw new Error('The body has been used.');
				}
				this.bodyUsed = true;
				const str = JSON.stringify(this._body);
				return Promise.resolve(str);
			}
		}

		function is_scalar(val)
		{
			const type = typeof(val);
			return type === 'number' || type === 'string' || type === 'boolean';
		}

		/**
		 * Compare two structures deeply.
		 * @param {*} expected Expected (referential) structure.
		 * @param {*} actual Actual structue.
		 * @param {*} exactTypeMatch If true, actual values of scalars are ignores (only shape of objects/arrays is tested).
		 */
		function deepCompare(expected, actual, exactTypeMatch = true)
		{
			if (exactTypeMatch) {
				if (typeof(expected) !== typeof(actual)) return false;
			} else {
				if (is_scalar(expected) !== is_scalar(actual)) return false;
				if (!is_scalar(expected) && typeof(expected) !== typeof(actual)) return false;
			}

			if (Array.isArray(expected)) {
				if (!Array.isArray(actual) || expected.length !== actual.length) return false;
				for (let i = 0; i < expected.length; ++i) {
					if (!deepCompare(expected[i], actual[i], exactTypeMatch)) return false;
				}
				return true;
			}
			else if (typeof(expected) === 'object' && expected !== null) {
				const expectedKeys = Object.keys(expected);
				if (expectedKeys.length !== Object.keys(actual).length) return false;
		
				let res = true;
				expectedKeys.forEach(key => res = res && actual[key] !== undefined
					&& deepCompare(expected[key], actual[key], exactTypeMatch));
				return res;
			}
			else
				return exactTypeMatch ? expected === actual : expected == actual;
		}
		
		
		/**
		 * Process URL and return object with parsed query parameters.
		 * @param {*} url URL to process.
		 * @param {*} expectedPrefix Expected API prefix of the URL.
		 */
		function processUrl(url, expectedPrefix)
		{
			const [prefix, query] = url.split('?', 2);
			if (prefix != expectedPrefix) return null;

			const params = query ? query.split('&') : [];
			const res = {};
			params.forEach(param => {
				const [ name, value ] = param.split('=', 2);
				res[name] = value;
			});
			return res;
		}


		// Variables used inside fetch mock for controll and reporting...
		let fetchRequests = []; // requests expected from fetch (and their retun values)
		let fetchErrors = []; // unexpected/invalid fetch calls
		
		/**
		 * Mock of standard fetch() AJAX function.
		 */
		function fetch(url, init)
		{
			// Process and validate URL.
			const urlParams = processUrl(url, API_URL);
			if (urlParams === null) {
				fetchErrors.push("Invalid URL: " + url);
				return Promise.resolve(new ResponseMock(null, { ok: false, status: 404, statusText: 'Not Found' }));
			}
			if (urlParams.action === 'default') delete urlParams.action;

			// Get the method from init object (everything else is ignored).
			const method = ((init && init.method) || 'GET').toUpperCase();

			// Find the matching request in list of expected requests...
			const request = fetchRequests.find(req => deepCompare(req.query, urlParams, false) && req.method == method);

			if (request) {
				// We have expected this request, lets mock it...
				fetchRequests = fetchRequests.filter(req => req !== request);	// remove it (it has been processed)

				// Prepare JSON body to be sent as response...
				const body = { ok: !request.error };
				if (request.error) {
					body.error = request.error;
				}
				else if (request.payload) {
					body.payload = request.payload;
				}
				return Promise.resolve(new ResponseMock(body));
			}
			else {
				// Ooops ... undexpected request!
				fetchErrors.push("No corresponding request found for URL " + url);
				return Promise.resolve(new ResponseMock(null, { ok: false, status: 500, statusText: 'Internal Error' }));
			}
		}

		// Make fetch global (visible by DataModel)
		global.fetch = fetch;
		global.window = { fetch };


		// Load all test invocations...
		let fileName = process.argv.pop();
		const calls = JSON.parse(fs.readFileSync(fileName));
		if (!calls || !Array.isArray(calls)) {
			throw new Error(`File ${fileName} does not contain expected data.`);
		}
	

		// Variables used for callback mock controll and reporting...
		let callbackExpected = null;	// Expected arguments of the callback (or null if no callback is expected)
		let callbackErrors = [];		// Errors reported from the callback invocation.


		/**
		 * Callback mock that is passed to all methods that require callbacks and verify, it is inoked correctly.
		 */
		function callback(...args)
		{
			if (callbackExpected) {
				if (!deepCompare(callbackExpected, args)) {
					callbackErrors.push("Callback invoked with unexpected arguments.");
				}
			}
			else
				callbackErrors.push(`Unexpected callback(${args.join(', ')}).`);
			
			callbackExpected = null;
			setImmediate(finalizeCall);
		}


		// Variables controlling test calls processing.s
		const callsResults = [];	// collects results of all call tests
		let pendingCall = null;		// currently pending call test object
		let callTimeout = null;		// timeout watchdog that fires if no callback is invoked (so we do not end in deadlock)


		/**
		 * Set up mocking environment and invoke another test call.
		 * If no more calls are on the list, wrap up and print out the results (and terminate).
		 */
		function processCall()
		{
			if (calls.length == 0) {
				// Time to wrap it up and finish...
				console.log(JSON.stringify(callsResults));
				process.exit(0);
			}

			// Process next call...
			pendingCall = calls.shift();
			if (!pendingCall)
				throw new Error("Invalid test call descriptor.")

			// Prepare mock environment...
			fetchRequests = pendingCall.fetch || [];
			fetchErrors = [];
			
			callbackExpected = pendingCall.callback || null;
			callbackErrors = [];

			// Prepare invocation data...
			const method = pendingCall.call;
			if (!method)
				throw new Error(`Invalid call method '${method}'.`);
			const args = pendingCall.args || [];

			if (pendingCall.callback) {
				// Make sure to set watch dog in case the callback is not invoked...
				callTimeout = setTimeout(callHasTimeout, 1000);

				// It is a async function with callback.
				args.push(callback);
				model[method](...args);
			}
			else {
				// It is a sync function invoked immediately.
				model[method](...args);
				setImmediate(finalizeCall);
			}
		}


		/**
		 * Timeout handler. Reports the timeout error and finalizes the pending call.
		 */
		function callHasTimeout()
		{
			if (!pendingCall) return;	// it must have made it just in time (false timeout)
			callTimeout = null;
			callbackErrors.push("Timeout!");
			finalizeCall();
		}


		/**
		 * Finalize pending call (which was terminated either by callback or by timeout).
		 * Once the results are gathered, the processCall() call is planned as immediate.
		 */
		function finalizeCall()
		{
			if (!pendingCall)
				throw new Error('Finalize call was invoked, but no call is pending.');

			// Make sure everything was invoked as expected...
			if (callbackExpected)
				callbackErrors.push("Callback was not invoked even when it was expected.");

			// Collect results...
			callsResults.push({
				call: pendingCall.call,
				fetch: fetchErrors,
				remainingFetchRequests: fetchRequests,	// this should be normally an empty array
				callback: callbackErrors,
			})

			// Cleanup...
			pendingCall = null;
			if (callTimeout) {
				// Stop the call timeout.
				clearTimeout(callTimeout);
				callTimeout = null;
			}

			// Let's get another one...
			setImmediate(processCall);
		}

		
		/*
		 * Put everything in motion...
		 */
		processCall();

	})();
}
catch (e) {
	console.log("Error: " + e.message);
	process.exit(2);
}
