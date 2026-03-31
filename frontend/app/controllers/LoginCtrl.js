/**
 * LoginCtrl — tela de login
 */
angular.module('playlistApp')
    .controller('LoginCtrl', ['$scope', '$rootScope', '$location', 'AuthService',
        function ($scope, $rootScope, $location, AuthService) {

            // Redireciona se já estiver logado
            if (AuthService.isAuthenticated()) {
                $location.path('/');
                return;
            }

            $scope.credentials = { email: '', password: '' };
            $scope.error       = null;
            $scope.loading     = false;

            $scope.login = function () {
                if ($scope.loading) { return; }
                $scope.error   = null;
                $scope.loading = true;

                AuthService.login($scope.credentials.email, $scope.credentials.password)
                    .then(function (data) {
                        $rootScope.currentUser = data.user;
                        $location.path('/');
                    })
                    .catch(function (response) {
                        var msg = (response.data && response.data.error)
                            ? response.data.error
                            : 'Erro ao realizar login. Tente novamente.';
                        $scope.error = msg;
                    })
                    .finally(function () {
                        $scope.loading = false;
                    });
            };
        }
    ]);
