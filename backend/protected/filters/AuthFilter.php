<?php
use Firebase\JWT\JWT;

/**
 * AuthFilter — valida o Bearer JWT em todas as rotas protegidas.
 * Registre em controllers com:
 *   public function filters() {
 *       return array('application.filters.AuthFilter');
 *   }
 */
class AuthFilter extends CFilter
{
    protected function preFilter($filterChain)
    {
        $authHeader = $this->getAuthorizationHeader();

        if (strpos($authHeader, 'Bearer ') !== 0) {
            $this->denyAccess('Unauthorized');
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode(
                $token,
                Yii::app()->params['jwtSecret'],
                array('HS256')
            );
            // Armazena o ID do usuário para uso nos controllers
            Yii::app()->params['authUserId'] = (int) $decoded->sub;
        } catch (Exception $e) {
            $this->denyAccess('Token inválido ou expirado');
        }

        return true;
    }

    /**
     * Lê o header Authorization compatível com Apache e nginx.
     */
    private function getAuthorizationHeader()
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // Apache mod_php às vezes expõe via apache_request_headers()
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }

        return '';
    }

    private function denyAccess($message)
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('error' => $message));
        Yii::app()->end();
    }
}
