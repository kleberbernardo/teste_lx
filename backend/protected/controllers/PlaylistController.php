<?php
/**
 * PlaylistController
 *
 * GET    /playlists
 * POST   /playlists
 * GET    /playlists/:id
 * PUT    /playlists/:id
 * DELETE /playlists/:id
 * GET    /playlists/:id/tracks
 * POST   /playlists/:id/tracks
 * DELETE /playlists/:id/tracks/:trackId
 */
class PlaylistController extends ApiController
{
    public function filters()
    {
        return array(
            array('AuthFilter'),
        );
    }

    /**
     * GET /playlists
     * Lista todas as playlists do usuário autenticado.
     */
    public function actionIndex()
    {
        $playlists = Playlist::model()->findAllByAttributes(
            array('user_id' => $this->authUserId()),
            array('order' => 'created_at DESC')
        );

        $result = array();
        foreach ($playlists as $p) {
            $result[] = $p->toArray();
        }

        $this->sendJson($result);
    }

    /**
     * POST /playlists
     * Body: { "name": "...", "description": "...", "cover_color": "#hex" }
     */
    public function actionCreate()
    {
        $data = $this->getBody();

        if (empty($data['name'])) {
            $this->sendError('O nome da playlist é obrigatório', 422);
        }

        $playlist              = new Playlist();
        $playlist->user_id     = $this->authUserId();
        $playlist->name        = trim($data['name']);
        $playlist->description = isset($data['description']) ? trim($data['description']) : null;
        $playlist->cover_color = $this->sanitizeColor(
            isset($data['cover_color']) ? $data['cover_color'] : '#1DB954'
        );

        if (!$playlist->save()) {
            $this->sendError('Erro ao criar playlist', 500);
        }

        $this->sendJson($playlist->toArray(), 201);
    }

    /**
     * GET /playlists/:id
     */
    public function actionView($id)
    {
        $playlist = $this->loadPlaylist($id);
        $this->sendJson($playlist->toArray());
    }

    /**
     * PUT /playlists/:id
     * Body: { "name": "...", "description": "...", "cover_color": "#hex" }
     */
    public function actionUpdate($id)
    {
        $playlist = $this->loadPlaylist($id);
        $data     = $this->getBody();

        if (!empty($data['name'])) {
            $playlist->name = trim($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $playlist->description = trim($data['description']);
        }
        if (!empty($data['cover_color'])) {
            $playlist->cover_color = $this->sanitizeColor($data['cover_color']);
        }

        if (!$playlist->save(false)) {
            $this->sendError('Erro ao atualizar playlist', 500);
        }

        $this->sendJson($playlist->toArray());
    }

    /**
     * DELETE /playlists/:id
     */
    public function actionDelete($id)
    {
        $playlist = $this->loadPlaylist($id);
        $playlist->delete();
        $this->sendJson(array('message' => 'Playlist removida'));
    }

    /**
     * GET /playlists/:id/tracks
     */
    public function actionTracks($id)
    {
        $playlist = $this->loadPlaylist($id);

        $rows = Yii::app()->db->createCommand()
            ->select('t.id, t.title, t.artist, t.album, t.duration_s, pt.position, pt.added_at')
            ->from('tracks t')
            ->join('playlist_tracks pt', 'pt.track_id = t.id')
            ->where('pt.playlist_id = :pid', array(':pid' => (int) $playlist->id))
            ->order('pt.position ASC, pt.added_at ASC')
            ->queryAll();

        $this->sendJson($rows);
    }

    /**
     * POST /playlists/:id/tracks
     * Body: { "track_id": 5 }
     */
    public function actionAddTrack($id)
    {
        $playlist = $this->loadPlaylist($id);
        $data     = $this->getBody();

        if (empty($data['track_id'])) {
            $this->sendError('track_id é obrigatório', 422);
        }

        $trackId = (int) $data['track_id'];
        $track   = Track::model()->findByPk($trackId);

        if ($track === null) {
            $this->sendError('Track não encontrada', 404);
        }

        // Verifica se já existe na playlist
        $existing = PlaylistTrack::model()->findByAttributes(array(
            'playlist_id' => (int) $playlist->id,
            'track_id'    => $trackId,
        ));

        if ($existing !== null) {
            $this->sendError('Esta track já está na playlist', 422);
        }

        // Próxima posição
        $maxPosition = Yii::app()->db->createCommand()
            ->select('MAX(position)')
            ->from('playlist_tracks')
            ->where('playlist_id = :pid', array(':pid' => (int) $playlist->id))
            ->queryScalar();

        $pt              = new PlaylistTrack();
        $pt->playlist_id = (int) $playlist->id;
        $pt->track_id    = $trackId;
        $pt->position    = (int) $maxPosition + 1;

        if (!$pt->save()) {
            $this->sendError('Erro ao adicionar track', 500);
        }

        $this->sendJson(array('message' => 'Track adicionada', 'track' => $track->toArray()), 201);
    }

    /**
     * DELETE /playlists/:id/tracks/:trackId
     */
    public function actionRemoveTrack($id, $trackId)
    {
        $playlist = $this->loadPlaylist($id);

        $pt = PlaylistTrack::model()->findByAttributes(array(
            'playlist_id' => (int) $playlist->id,
            'track_id'    => (int) $trackId,
        ));

        if ($pt === null) {
            $this->sendError('Track não encontrada na playlist', 404);
        }

        $pt->delete();

        $this->sendJson(array('message' => 'Track removida da playlist'));
    }

    // ------------------------------------------------------------------

    private function sanitizeColor($color)
    {
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $color;
        }
        return '#1DB954';
    }
}
