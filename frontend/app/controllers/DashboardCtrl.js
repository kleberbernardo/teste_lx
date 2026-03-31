/**
 * DashboardCtrl — lista de playlists + criar/deletar
 */
angular.module('playlistApp')
    .controller('DashboardCtrl', ['$scope', '$location', '$uibModal', 'PlaylistService',
        function ($scope, $location, $uibModal, PlaylistService) {

            $scope.playlists = [];
            $scope.loading   = true;

            // Paleta de cores para novas playlists
            var colorPalette = [
                '#E91429', '#509BF5', '#1DB954', '#F59B23',
                '#8D67AB', '#E61E32', '#148A08', '#0D72EA'
            ];

            function randomColor() {
                return colorPalette[Math.floor(Math.random() * colorPalette.length)];
            }

            // ----- Carregar playlists -----
            function loadPlaylists() {
                $scope.loading = true;
                PlaylistService.getAll()
                    .then(function (response) {
                        $scope.playlists = response.data;
                    })
                    .catch(function () {
                        $scope.error = 'Não foi possível carregar as playlists.';
                    })
                    .finally(function () {
                        $scope.loading = false;
                    });
            }

            loadPlaylists();

            // ----- Navegar para playlist -----
            $scope.openPlaylist = function (id) {
                $location.path('/playlists/' + id);
            };

            // ----- Criar playlist via modal -----
            $scope.openCreateModal = function () {
                var modal = $uibModal.open({
                    templateUrl: 'app/views/playlist-form.html',
                    controller:  'PlaylistFormCtrl',
                    size:        'sm',
                    resolve: {
                        playlist: function () { return null; },
                        defaultColor: function () { return randomColor(); }
                    }
                });

                modal.result.then(function (data) {
                    PlaylistService.create(data)
                        .then(function () { loadPlaylists(); })
                        .catch(function (r) {
                            alert((r.data && r.data.error) || 'Erro ao criar playlist.');
                        });
                });
            };

            // ----- Editar playlist via modal -----
            $scope.openEditModal = function ($event, playlist) {
                $event.stopPropagation();
                var modal = $uibModal.open({
                    templateUrl: 'app/views/playlist-form.html',
                    controller:  'PlaylistFormCtrl',
                    size:        'sm',
                    resolve: {
                        playlist:     function () { return angular.copy(playlist); },
                        defaultColor: function () { return playlist.cover_color; }
                    }
                });

                modal.result.then(function (data) {
                    PlaylistService.update(playlist.id, data)
                        .then(function () { loadPlaylists(); })
                        .catch(function (r) {
                            alert((r.data && r.data.error) || 'Erro ao atualizar playlist.');
                        });
                });
            };

            // ----- Deletar playlist -----
            $scope.deletePlaylist = function ($event, playlist) {
                $event.stopPropagation();
                if (!confirm('Deseja remover a playlist "' + playlist.name + '"?')) { return; }

                PlaylistService.remove(playlist.id)
                    .then(function () { loadPlaylists(); })
                    .catch(function (r) {
                        alert((r.data && r.data.error) || 'Erro ao remover playlist.');
                    });
            };
        }
    ])

    // Controller do formulário (modal de criar/editar)
    .controller('PlaylistFormCtrl', ['$scope', '$uibModalInstance', 'playlist', 'defaultColor',
        function ($scope, $uibModalInstance, playlist, defaultColor) {

            $scope.isEdit = !!playlist;
            $scope.form = playlist
                ? { name: playlist.name, description: playlist.description, cover_color: playlist.cover_color }
                : { name: '', description: '', cover_color: defaultColor };

            $scope.colors = [
                '#E91429', '#509BF5', '#1DB954', '#F59B23',
                '#8D67AB', '#E61E32', '#148A08', '#0D72EA',
                '#BA5D07', '#477D95'
            ];

            $scope.selectColor = function (color) {
                $scope.form.cover_color = color;
            };

            $scope.submit = function () {
                if (!$scope.form.name) { return; }
                $uibModalInstance.close($scope.form);
            };

            $scope.cancel = function () {
                $uibModalInstance.dismiss('cancel');
            };
        }
    ]);
