<?php
// CORS — deve ser emitido antes de qualquer saída
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

// OPTIONS preflight — retorna imediatamente sem inicializar o Yii
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Composer autoload (firebase/php-jwt)
require dirname(__FILE__) . '/../vendor/autoload.php';

// Yii framework
$yiiPath = dirname(__FILE__) . '/../framework/yii.php';
if (!file_exists($yiiPath)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(array('error' => 'Framework Yii não encontrado. Veja o README para instalação.'));
    exit;
}
require $yiiPath;

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

$config = dirname(__FILE__) . '/../protected/config/main.php';

Yii::createWebApplication($config)->run();
