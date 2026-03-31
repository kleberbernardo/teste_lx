<?php
use Firebase\JWT\JWT;

/**
 * AuthController
 *
 * POST /auth/login
 */
class AuthController extends ApiController
{
    /**
     * POST /auth/login
     * Body: { "email": "...", "password": "..." }
     */
    public function actionLogin()
    {
        $data = $this->getBody();

        if (empty($data['email']) || empty($data['password'])) {
            $this->sendError('Email e senha são obrigatórios', 422);
        }

        $user = User::model()->findByAttributes(array('email' => trim($data['email'])));

        if ($user === null || !password_verify($data['password'], $user->password)) {
            $this->sendError('Credenciais inválidas', 401);
        }

        $now     = time();
        $expiry  = (int) Yii::app()->params['jwtExpiry'];
        $secret  = Yii::app()->params['jwtSecret'];

        $payload = array(
            'sub' => (int) $user->id,
            'iat' => $now,
            'exp' => $now + $expiry,
        );

        $token = JWT::encode($payload, $secret);

        $this->sendJson(array(
            'token' => $token,
            'user'  => $user->toArray(),
        ));
    }
}
