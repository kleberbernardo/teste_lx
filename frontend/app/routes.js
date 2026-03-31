/**
 * routes.js — Definição de rotas da SPA via $routeProvider
 */
angular.module('playlistApp')
    .config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {

            $routeProvider
                .when('/login', {
                    templateUrl: 'app/views/login.html',
                    controller:  'LoginCtrl',
                    data: { requireAuth: false }
                })
                .when('/', {
                    templateUrl: 'app/views/dashboard.html',
                    controller:  'DashboardCtrl',
                    data: { requireAuth: true }
                })
                .when('/playlists/new', {
                    templateUrl: 'app/views/playlist-form.html',
                    controller:  'DashboardCtrl',
                    data: { requireAuth: true }
                })
                .when('/playlists/:id', {
                    templateUrl: 'app/views/playlist.html',
                    controller:  'PlaylistCtrl',
                    data: { requireAuth: true }
                })
                .when('/playlists/:id/edit', {
                    templateUrl: 'app/views/playlist-form.html',
                    controller:  'PlaylistCtrl',
                    data: { requireAuth: true }
                })
                .when('/settings', {
                    templateUrl: 'app/views/settings.html',
                    controller:  'SettingsCtrl',
                    data: { requireAuth: true }
                })
                .otherwise({ redirectTo: '/' });

            // Usa hash-based routing (compatível com qualquer servidor)
            // $locationProvider.html5Mode(true); — descomente se usar HTML5 mode + .htaccess SPA
        }
    ]);
