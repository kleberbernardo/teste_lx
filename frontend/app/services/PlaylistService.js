/**
 * PlaylistService — CRUD de playlists via API
 */
angular.module('playlistApp')
    .factory('PlaylistService', ['$http', 'API_URL',
        function ($http, API_URL) {
            var base = API_URL + '/playlists';

            return {
                getAll: function () {
                    return $http.get(base);
                },
                getById: function (id) {
                    return $http.get(base + '/' + id);
                },
                create: function (data) {
                    return $http.post(base, data);
                },
                update: function (id, data) {
                    return $http.put(base + '/' + id, data);
                },
                remove: function (id) {
                    return $http.delete(base + '/' + id);
                },
                getTracks: function (id) {
                    return $http.get(base + '/' + id + '/tracks');
                },
                addTrack: function (id, trackId) {
                    return $http.post(base + '/' + id + '/tracks', { track_id: trackId });
                },
                removeTrack: function (id, trackId) {
                    return $http.delete(base + '/' + id + '/tracks/' + trackId);
                }
            };
        }
    ]);
