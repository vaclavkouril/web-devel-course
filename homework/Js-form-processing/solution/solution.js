/**
 * Example of a local function which is not exported. You may use it internally in processFormData().
 * This function verifies the base URL (i.e., the URL prefix) and returns true if it is valid.
 * @param {*} url 
 */
function verifyBaseUrl(url)
{
	return Boolean(url.match(/^https:\/\/[-a-z0-9._]+([:][0-9]+)?(\/[-a-z0-9._/]*)?$/i));
}

/**
 * Example of a local function which is not exported. You may use it internally in processFormData().
 * This function verifies the relative URL (i.e., the URL suffix) and returns true if it is valid.
 * @param {*} url 
 */
function verifyRelativeUrl(url)
{
	return Boolean(url.match(/^[-a-z0-9_/]*([?]([-a-z0-9_\]\[]+=[^&=]*&)*([-a-z0-9_\]\[]+=[^&=?#]*)?)?$/i));
}


/**
 * Main exported function that process the form and yields the sanitized data (or errors).
 * @param {*} formData Input data as FormData instance.
 * @param {*} errors Object which collects errors (if any).
 * @return Serialized JSON containing sanitized form data.
 */

/**
 * Main exported function that processes the form and yields the sanitized data (or errors).
 * @param {*} formData Input data as FormData instance.
 * @param {*} errors Object which collects errors (if any).
 * @return Serialized JSON containing sanitized form data or null if errors occurred.
 */
function processFormData(formData, errors) {
    const operations = groupOperations(formData);
    const result = [];
    let baseUrl = formData._items.url_base[0]; 
	
    operations.forEach((operation, index) => {
        let record = {};
        let hasError = false;
		if (!verifyBaseUrl(baseUrl)) {
            errors['url_base'] = 'Invalid URL format.';
            hasError = true;
		}
        for (const [key, value] of Object.entries(operation)) {
            switch (key) {
                case 'date':
                    const parsedDate = parseDate(value);
                    if (!parsedDate) {
                        addError(errors, 'date', index, 'Invalid date format.');
                        hasError = true;
                    } else {
                        record['date'] = parsedDate;
                    }
                    break;
                case 'time':
                    const parsedTime = parseTime(value, operation['repeat']);
                    if (parsedTime.error) {
                        addError(errors, 'time', index, parsedTime.error);
                        hasError = true;
                    } else {
                        record['time'] = parsedTime.value;
                    }
                    break;
                case 'repeat':

					if(!value.match(/^(?:[1-9]|[1-9][0-9]|100)$/)){
						addError(errors, 'repeat', index, 'Invalid repeat count. Repeat count must be a number between 1 and 100.');
						hasError = true;
					} else{
						record['repeat'] = parseInt(value);
					}
					break;
				case 'url':
                    if (!verifyRelativeUrl(value)) {
                        addError(errors, 'url', index, 'Invalid URL suffix format.');
                        hasError = true;
                    } else {
                        record['url'] = baseUrl + value;
                    }
                    break;
                case 'method':
                    if (!['GET', 'POST', 'PUT', 'DELETE'].includes(value)) {
                        addError(errors, 'method', index, 'Invalid HTTP method.');
                        hasError = true;
                    } else {
                        record['method'] = value;
                    }
                    break;
                case 'body':
                    try {
                        record['body'] = value.trim() !== '' ? JSON.parse(value) : {};
                    } catch {
                        addError(errors, 'body', index, 'The body must be either valid JSON or empty.');
                        hasError = true;
                    }
                    break;
            }
        }
        if (!hasError) {
            result.push(record);
        }
    });

    return Object.keys(errors).length === 0 ? JSON.stringify(result) : null;
}


function groupOperations(formData) {
    const operations = [];
    const items = formData._items;
    const operationCount = items.date.length;
	const baseUrl = items.url_base[0];
    for (let i = 0; i < operationCount; i++) {
        let operation = { url_base: baseUrl };
        for (const key in items) {
            if (key !== 'url_base') {
                operation[key] = items[key][i];
            }
        }
        operations.push(operation);
    }

    return operations;
}



function parseDate(dateString) {
    const regex = /^(\d{1,4})[./-](\d{1,2})[./-](\d{1,4})$/;
    const match = dateString.match(regex);
    if (!match) return null;

    let year, month, day;
    
    if (dateString.includes('-')) {
        [year, month, day] = match.slice(1).map(Number);
    } else if (dateString.includes('.')) {
        [day, month, year] = match.slice(1).map(Number);
    } else if (dateString.includes('/')) {
        [month, day, year] = match.slice(1).map(Number);
    } else{
		return null;
	}

    if (year < 1000 || year > 9999 || month < 1 || month > 12 || day < 1 || day > 31) {
        return null;
    }

    if (dateString.split('-').some(part => part.length === 1)) {
        return null;
    }

    month -= 1; 
    const date = new Date(Date.UTC(year, month, day));
    return date.getTime() / 1000;
} 



function parseTime(timeString, repeat) {
    const singleTimeRegex = /^(\d{1,2}):(\d{2})(?::(\d{2}))?$/;
    const intervalRegex = /^(\d{1,2}:\d{2}(?::\d{2})?)\s*-\s*(\d{1,2}:\d{2}(?::\d{2})?)$/;

    if (singleTimeRegex.test(timeString)) {
		const value = convertTimeToSeconds(timeString);
		if (value === null){ return { error: "Invalid format", value: null };}
		return { error: null, value: value};
    } else if (intervalRegex.test(timeString)) {
        if (parseInt(repeat) <= 1) {
            return { error: "Time interval is not allowed when there is only one repetition set.", value: null };
        }

        const matches = intervalRegex.exec(timeString);
		const fromTime = convertTimeToSeconds(matches[1]);
        const toTime =  convertTimeToSeconds(matches[2]);
		if (fromTime === null || toTime === null){ return { error: "Invalid format", value: null };}

		if (fromTime > toTime){
			return { error: "Start of the interval can't be after end of interval", value: null };
		}
        return { 
            error: null, 
            value: {
                from: fromTime,
                to:	toTime 
            }
        };
    } else {
        return { error: "Invalid time or time interval format.", value: null };
    }
}


function convertTimeToSeconds(timeStr) {
    const parts = timeStr.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/);
    if (!parts) return null;

    const hours = parseInt(parts[1], 10);
    const minutes = parseInt(parts[2], 10);
    const seconds = parts[3] ? parseInt(parts[3], 10) : 0;

    if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59 || seconds < 0 || seconds > 59) {
        return null; 
    }

    return hours * 3600 + minutes * 60 + seconds;
}



function addError(errors, fieldName, index, message) {
    if (!errors[fieldName]) {
        errors[fieldName] = {};
    }
    errors[fieldName][index] = message;
}



// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = { processFormData };
