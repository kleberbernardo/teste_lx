/**
 * UserService — perfil do usuário autenticado
 */
angular.module('playlistApp')
    .factory('UserService', ['$http', 'API_URL',
        function ($http, API_URL) {
            return {
                getMe: function () {
                    return $http.get(API_URL + '/users/me');
                },
                updateMe: function (data) {
                    return $http.put(API_URL + '/users/me', data);
                }
            };
        }
    ]);
