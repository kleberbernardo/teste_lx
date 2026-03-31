/**
 * PlaylistCtrl — detalhe da playlist + gerenciar tracks
 */
angular.module('playlistApp')
    .controller('PlaylistCtrl', ['$scope', '$routeParams', '$location', '$uibModal',
        'PlaylistService', 'TrackService',
        function ($scope, $routeParams, $location, $uibModal, PlaylistService, TrackService) {

            var playlistId = $routeParams.id;

            $scope.playlist  = null;
            $scope.tracks    = [];
            $scope.loading   = true;
            $scope.error     = null;

            // ----- Carregar playlist e tracks -----
            function loadPlaylist() {
                $scope.loading = true;
                PlaylistService.getById(playlistId)
                    .then(function (r) {
                        $scope.playlist = r.data;
                        return PlaylistService.getTracks(playlistId);
                    })
                    .then(function (r) {
                        $scope.tracks = r.data;
                    })
                    .catch(function (r) {
                        if (r.status === 404 || r.status === 403) {
                            $location.path('/');
                        } else {
                            $scope.error = 'Não foi possível carregar a playlist.';
                        }
                    })
                    .finally(function () {
                        $scope.loading = false;
                    });
            }

            loadPlaylist();

            // ----- Formatar duração -----
            $scope.formatDuration = function (seconds) {
                var s = parseInt(seconds, 10) || 0;
                return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
            };

            // ----- Voltar -----
            $scope.goBack = function () {
                $location.path('/');
            };

            // ----- Editar playlist -----
            $scope.openEditModal = function () {
                var modal = $uibModal.open({
                    templateUrl: 'app/views/playlist-form.html',
                    controller:  'PlaylistFormCtrl',
                    size:        'sm',
                    resolve: {
                        playlist:     function () { return angular.copy($scope.playlist); },
                        defaultColor: function () { return $scope.playlist.cover_color; }
                    }
                });

                modal.result.then(function (data) {
                    PlaylistService.update(playlistId, data)
                        .then(function (r) { $scope.playlist = r.data; })
                        .catch(function (r) {
                            alert((r.data && r.data.error) || 'Erro ao atualizar playlist.');
                        });
                });
            };

            // ----- Deletar playlist -----
            $scope.deletePlaylist = function () {
                if (!confirm('Deseja remover esta playlist?')) { return; }
                PlaylistService.remove(playlistId)
                    .then(function () { $location.path('/'); })
                    .catch(function (r) {
                        alert((r.data && r.data.error) || 'Erro ao remover playlist.');
                    });
            };

            // ----- Adicionar track (modal de busca) -----
            $scope.openAddTrackModal = function () {
                var modal = $uibModal.open({
                    templateUrl: 'app/views/add-track.html',
                    controller:  'AddTrackCtrl',
                    size:        'md',
                    resolve: {
                        existingTrackIds: function () {
                            return $scope.tracks.map(function (t) { return t.id; });
                        }
                    }
                });

                modal.result.then(function (trackId) {
                    PlaylistService.addTrack(playlistId, trackId)
                        .then(function () { loadPlaylist(); })
                        .catch(function (r) {
                            alert((r.data && r.data.error) || 'Erro ao adicionar track.');
                        });
                });
            };

            // ----- Remover track -----
            $scope.removeTrack = function (track) {
                if (!confirm('Remover "' + track.title + '" da playlist?')) { return; }
                PlaylistService.removeTrack(playlistId, track.id)
                    .then(function () { loadPlaylist(); })
                    .catch(function (r) {
                        alert((r.data && r.data.error) || 'Erro ao remover track.');
                    });
            };
        }
    ])

    // Controller do modal de adicionar track
    .controller('AddTrackCtrl', ['$scope', '$uibModalInstance', 'TrackService', 'existingTrackIds',
        function ($scope, $uibModalInstance, TrackService, existingTrackIds) {

            $scope.query     = '';
            $scope.tracks    = [];
            $scope.loading   = false;
            $scope.searched  = false;

            $scope.search = function () {
                $scope.loading  = true;
                $scope.searched = true;
                TrackService.search($scope.query)
                    .then(function (r) {
                        $scope.tracks = r.data.map(function (t) {
                            t.alreadyAdded = existingTrackIds.indexOf(parseInt(t.id, 10)) !== -1;
                            return t;
                        });
                    })
                    .finally(function () { $scope.loading = false; });
            };

            $scope.formatDuration = function (seconds) {
                var s = parseInt(seconds, 10) || 0;
                return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
            };

            $scope.select = function (track) {
                if (track.alreadyAdded) { return; }
                $uibModalInstance.close(track.id);
            };

            $scope.cancel = function () {
                $uibModalInstance.dismiss('cancel');
            };

            // Carrega todos os tracks ao abrir
            $scope.search();
        }
    ]);
