/**
 * TrackService — catálogo de tracks disponíveis
 */
angular.module('playlistApp')
    .factory('TrackService', ['$http', 'API_URL',
        function ($http, API_URL) {
            return {
                search: function (query) {
                    var params = query ? { q: query } : {};
                    return $http.get(API_URL + '/tracks', { params: params });
                }
            };
        }
    ]);
