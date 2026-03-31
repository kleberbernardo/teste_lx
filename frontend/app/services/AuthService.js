/**
 * AuthService — login, logout, validação de token JWT (client-side)
 */
angular.module('playlistApp')
    .factory('AuthService', ['$http', '$window', 'API_URL',
        function ($http, $window, API_URL) {

            var TOKEN_KEY = 'playlist_token';
            var USER_KEY  = 'playlist_user';

            function decodeToken(token) {
                try {
                    var parts   = token.split('.');
                    var payload = parts[1]
                        .replace(/-/g, '+')
                        .replace(/_/g, '/');
                    // Pad base64
                    while (payload.length % 4 !== 0) { payload += '='; }
                    return JSON.parse($window.atob(payload));
                } catch (e) {
                    return null;
                }
            }

            return {
                login: function (email, password) {
                    return $http.post(API_URL + '/auth/login', {
                        email:    email,
                        password: password
                    }).then(function (response) {
                        $window.localStorage.setItem(TOKEN_KEY, response.data.token);
                        $window.localStorage.setItem(USER_KEY, JSON.stringify(response.data.user));
                        return response.data;
                    });
                },

                logout: function () {
                    $window.localStorage.removeItem(TOKEN_KEY);
                    $window.localStorage.removeItem(USER_KEY);
                },

                isAuthenticated: function () {
                    var token = $window.localStorage.getItem(TOKEN_KEY);
                    if (!token) { return false; }
                    var decoded = decodeToken(token);
                    if (!decoded || !decoded.exp) { return false; }
                    return decoded.exp > Math.floor(Date.now() / 1000);
                },

                getToken: function () {
                    return $window.localStorage.getItem(TOKEN_KEY);
                },

                getUser: function () {
                    var raw = $window.localStorage.getItem(USER_KEY);
                    try {
                        return raw ? JSON.parse(raw) : null;
                    } catch (e) {
                        return null;
                    }
                },

                updateStoredUser: function (user) {
                    $window.localStorage.setItem(USER_KEY, JSON.stringify(user));
                }
            };
        }
    ]);
