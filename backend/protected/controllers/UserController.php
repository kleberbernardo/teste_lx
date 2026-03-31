<?php
/**
 * UserController
 *
 * GET  /users/me
 * PUT  /users/me
 */
class UserController extends ApiController
{
    public function filters()
    {
        return array(
            array('AuthFilter'),
        );
    }

    /**
     * GET /users/me
     */
    public function actionMe()
    {
        $user = User::model()->findByPk($this->authUserId());
        if ($user === null) {
            $this->sendError('Usuário não encontrado', 404);
        }
        $this->sendJson($user->toArray());
    }

    /**
     * PUT /users/me
     * Body: { "name": "...", "email": "...", "current_password": "...", "new_password": "..." }
     */
    public function actionUpdate()
    {
        $data = $this->getBody();
        $user = User::model()->findByPk($this->authUserId());

        if ($user === null) {
            $this->sendError('Usuário não encontrado', 404);
        }

        // Atualizar nome
        if (!empty($data['name'])) {
            $user->name = trim($data['name']);
        }

        // Atualizar email
        if (!empty($data['email'])) {
            $newEmail = trim($data['email']);
            if ($newEmail !== $user->email) {
                $existing = User::model()->findByAttributes(array('email' => $newEmail));
                if ($existing !== null) {
                    $this->sendError('Este e-mail já está em uso', 422);
                }
                $user->email = $newEmail;
            }
        }

        // Trocar senha (requer senha atual)
        if (!empty($data['new_password'])) {
            if (empty($data['current_password'])) {
                $this->sendError('Informe a senha atual para alterá-la', 422);
            }
            if (!password_verify($data['current_password'], $user->password)) {
                $this->sendError('Senha atual incorreta', 422);
            }
            if (strlen($data['new_password']) < 6) {
                $this->sendError('A nova senha deve ter ao menos 6 caracteres', 422);
            }
            $user->password = password_hash($data['new_password'], PASSWORD_BCRYPT);
        }

        if (!$user->save(false)) {
            $this->sendError('Erro ao salvar usuário', 500);
        }

        $this->sendJson($user->toArray());
    }
}
