<?php
/**
 * ApiController — base para todos os controllers REST.
 * Fica em components/ para ser carregado pelo autoloader do Yii via 'application.components.*'
 * - Desativa layout e validação CSRF
 * - Fornece helpers sendJson / sendError / getBody
 * - Expõe o userId autenticado via authUserId()
 */
class ApiController extends CController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    protected function sendJson($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        Yii::app()->end();
    }

    protected function sendError($message, $status = 400)
    {
        $this->sendJson(array('error' => $message), $status);
    }

    protected function getBody()
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return array();
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    protected function authUserId()
    {
        return (int) Yii::app()->params['authUserId'];
    }

    protected function loadPlaylist($id)
    {
        $playlist = Playlist::model()->findByPk((int) $id);
        if ($playlist === null) {
            $this->sendError('Playlist não encontrada', 404);
        }
        if ((int) $playlist->user_id !== $this->authUserId()) {
            $this->sendError('Acesso negado', 403);
        }
        return $playlist;
    }
}
