/**
 * app.js — Declaração do módulo Angular + interceptor JWT + run block (auth guard)
 */
angular.module('playlistApp', ['ngRoute', 'ui.bootstrap'])

    // URL base da API.
    // Docker: http://localhost:8080 (padrão)
    // Produção: ajuste para o domínio real
    .constant('API_URL', 'http://localhost:8080')

    // ----------------------------------------------------------------
    // Config: registra o interceptor de autenticação
    // ----------------------------------------------------------------
    .config(['$httpProvider', function ($httpProvider) {
        $httpProvider.interceptors.push('AuthInterceptor');
    }])

    // ----------------------------------------------------------------
    // AuthInterceptor — injeta Bearer em toda request; redireciona em 401
    // ----------------------------------------------------------------
    .factory('AuthInterceptor', ['$q', '$window', '$injector',
        function ($q, $window, $injector) {
            return {
                request: function (config) {
                    var AuthService = $injector.get('AuthService');
                    var token = AuthService.getToken();
                    if (token) {
                        config.headers['Authorization'] = 'Bearer ' + token;
                    }
                    return config;
                },
                responseError: function (rejection) {
                    if (rejection.status === 401) {
                        var AuthService = $injector.get('AuthService');
                        AuthService.logout();
                        $injector.get('$location').path('/login');
                    }
                    return $q.reject(rejection);
                }
            };
        }
    ])

    // ----------------------------------------------------------------
    // Run block — guard global de rotas + expose currentUser no $rootScope
    // ----------------------------------------------------------------
    .run(['$rootScope', '$location', 'AuthService',
        function ($rootScope, $location, AuthService) {

            // Expõe o usuário e o método de logout globalmente (usado pela navbar)
            $rootScope.currentUser = AuthService.getUser();
            $rootScope.logout = function () {
                AuthService.logout();
                $rootScope.currentUser = null;
                $location.path('/login');
            };
            $rootScope.isActive = function (path) {
                return $location.path() === path;
            };

            $rootScope.$on('$routeChangeStart', function (event, next) {
                var requireAuth = next.data && next.data.requireAuth;
                var isLoggedIn  = AuthService.isAuthenticated();

                if (requireAuth && !isLoggedIn) {
                    event.preventDefault();
                    $location.path('/login');
                }

                // Atualiza currentUser após login
                $rootScope.currentUser = AuthService.getUser();
            });
        }
    ]);
