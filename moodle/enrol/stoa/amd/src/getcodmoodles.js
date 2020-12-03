define(['jquery', 'core/ajax','core/notification'], function($, Ajax, Notification) {

    return {

        /**
         * Process the results for auto complete elements.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {Array} results An array or results.
         * @return {Array} New array of results.
         */
        processResults: function(selector, results) {
            var options = [];
            $.each(results, function(index, data) {
                options.push({
                    value: data,
                    label: data
                });
            });
            return options;
        },

        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} query The query string.
         * @param {Function} callback A callback function receiving an array of results.
         */
        transport: function(selector, query, callback) {

            var promise;
            promise = Ajax.call([{
                methodname: 'enrol_stoa_get_codmoodles',
                args: {
                    query: query,
                    limitnum: 100
                }
            }])[0];

            return promise.done(callback).fail(Notification.exception);
        }
    };
});
