<?php
/**
 * TrackController
 *
 * GET /tracks?q=termo   — busca no catálogo de tracks
 */
class TrackController extends ApiController
{
    public function filters()
    {
        return array(
            array('AuthFilter'),
        );
    }

    /**
     * GET /tracks
     * Parâmetro opcional: ?q=termo (busca em título, artista ou álbum)
     */
    public function actionIndex()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        $cmd = Yii::app()->db->createCommand()
            ->select('id, title, artist, album, duration_s')
            ->from('tracks')
            ->order('artist ASC, title ASC');

        if ($q !== '') {
            $like = '%' . strtr($q, array('%' => '\%', '_' => '\_')) . '%';
            $cmd->where(
                'title LIKE :q OR artist LIKE :q OR album LIKE :q',
                array(':q' => $like)
            );
        }

        $tracks = $cmd->queryAll();

        $this->sendJson($tracks);
    }
}
