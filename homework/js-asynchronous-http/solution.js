/**
 * Data model for loading the work hour categories and fileld hours.
 * The model implements internal cache, so the data does not have to be
 * loaded every time from the REST API.
 */
class DataModel {
	/**
	 * Initialize the data model with given URL pointing to REST API.
	 * @param {string} apiUrl Api URL prefix (without the query part).
	 */
	constructor(apiUrl)
	{
		this.apiUrl = apiUrl;
        this.cache = null;
	}
	/**
	 * Retrieve the data and pass them to given callback function.
	 * If the data are available in cache, the callback is invoked immediately (synchronously).
	 * Otherwise the data are loaded from the REST API and cached internally.
	 * @param {Function} callback Function which is called back once the data become available.
	 *                     The callback receives the data (as array of objects, where each object
	 *                     holds `id`, `caption`, and `hours` properties).
	 *                     If the fetch failed, the callback is invoked with two arguments,
	 *                     first one (data) is null, the second one is error message
	 */
	getData(callback) {
        if (this.cache) {
            callback(this.cache);
            return;
        }

        fetch(this.apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (!data.ok) {
                    throw new Error(data.error);
                }

                const categories = data.payload;
                const fetchHoursPromises = categories.map(category =>
                    fetch(`${this.apiUrl}?action=hours&id=${category.id}`)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return res.json();
                        })
                        .then(hourData => {
                            if (!hourData.ok) {
                                throw new Error(hourData.error);
                            }
                            return {
                                ...category,
                                hours: hourData.payload.hours
                            };
                        })
                );

                return Promise.all(fetchHoursPromises);
            })
            .then(completeData => {
                this.cache = completeData;
                callback(this.cache);
            })
            .catch(error => {
                callback(null, error.message);
            });
    }
	/**
	 * Invalidate internal cache. Next invocation of getData() will be forced to load data from the server.
	 */
	invalidate()
	{
		this.cache = null;
	}

	
	/**
	 * Modify hours for one record.
	 * @param {number} id ID of the record in question.
	 * @param {number} hours New value of the hours (m)
	 * @param {Function} callback Invoked when the operation is completed.
	 *                            On failutre, one argument with error message is passed to the callback.
	 */
	
	
	setHours(id, hours, callback = null) {
        fetch(`${this.apiUrl}?action=hours&id=${id}&hours=${hours}`, {
            method: 'POST'
        })
            .then(response => response.json())
            .then(data => {
                if (!data.ok) throw new Error(data.error);

                if (this.cache) {
                    const item = this.cache.find(c => c.id === id);
                    if (item) item.hours = hours;
                }

                if (callback) callback();
            })
            .catch(error => {
                if (callback) callback(error.message);
            });
    }

}


// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = { DataModel };
