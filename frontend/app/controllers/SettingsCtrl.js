/**
 * SettingsCtrl — perfil do usuário + troca de senha
 */
angular.module('playlistApp')
    .controller('SettingsCtrl', ['$scope', '$rootScope', 'UserService', 'AuthService',
        function ($scope, $rootScope, UserService, AuthService) {

            $scope.profile       = {};
            $scope.passwordForm  = {};
            $scope.profileMsg    = null;
            $scope.profileError  = null;
            $scope.passwordMsg   = null;
            $scope.passwordError = null;
            $scope.loadingProfile  = false;
            $scope.loadingPassword = false;

            // ----- Carregar perfil -----
            UserService.getMe()
                .then(function (r) {
                    $scope.profile = {
                        name:  r.data.name,
                        email: r.data.email
                    };
                })
                .catch(function () {
                    $scope.profileError = 'Não foi possível carregar o perfil.';
                });

            // ----- Salvar perfil -----
            $scope.saveProfile = function () {
                $scope.profileMsg   = null;
                $scope.profileError = null;
                $scope.loadingProfile = true;

                UserService.updateMe({ name: $scope.profile.name, email: $scope.profile.email })
                    .then(function (r) {
                        AuthService.updateStoredUser(r.data);
                        $rootScope.currentUser = r.data;
                        $scope.profileMsg = 'Perfil atualizado com sucesso!';
                    })
                    .catch(function (r) {
                        $scope.profileError = (r.data && r.data.error) || 'Erro ao atualizar perfil.';
                    })
                    .finally(function () { $scope.loadingProfile = false; });
            };

            // ----- Trocar senha -----
            $scope.changePassword = function () {
                $scope.passwordMsg   = null;
                $scope.passwordError = null;

                if ($scope.passwordForm.new_password !== $scope.passwordForm.confirm_password) {
                    $scope.passwordError = 'A nova senha e a confirmação não coincidem.';
                    return;
                }

                $scope.loadingPassword = true;

                UserService.updateMe({
                    current_password: $scope.passwordForm.current_password,
                    new_password:     $scope.passwordForm.new_password
                })
                    .then(function () {
                        $scope.passwordMsg  = 'Senha alterada com sucesso!';
                        $scope.passwordForm = {};
                    })
                    .catch(function (r) {
                        $scope.passwordError = (r.data && r.data.error) || 'Erro ao alterar senha.';
                    })
                    .finally(function () { $scope.loadingPassword = false; });
            };
        }
    ]);
