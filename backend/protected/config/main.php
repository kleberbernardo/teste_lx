<?php
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'     => 'Playlist API',

    'preload' => array('log'),

    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.filters.*',
    ),

    'components' => array(

        'db' => require(dirname(__FILE__) . '/db.php'),

        'request' => array(
            'enableCsrfValidation'   => false,
            'enableCookieValidation' => false,
        ),

        'session' => array(
            'autoStart' => false,
        ),

        'urlManager' => array(
            'urlFormat'      => 'path',
            'showScriptName' => false,
            'rules'          => array(
                // Yii 1.1.29: array(route, 'pattern' => url, 'verb' => método)
                // $route[0] = rota, $route['pattern'] sobrescreve a chave PHP (evita chave duplicada)
                // Rotas aninhadas ANTES das rotas de playlist (mais específicas primeiro)
                array('playlist/tracks',      'pattern' => 'playlists/<id:\d+>/tracks',               'verb' => 'GET'),
                array('playlist/addTrack',    'pattern' => 'playlists/<id:\d+>/tracks',               'verb' => 'POST'),
                array('playlist/removeTrack', 'pattern' => 'playlists/<id:\d+>/tracks/<trackId:\d+>', 'verb' => 'DELETE'),

                // Auth
                array('auth/login',           'pattern' => 'auth/login',                              'verb' => 'POST'),

                // Usuário
                array('user/me',              'pattern' => 'users/me',                                'verb' => 'GET'),
                array('user/update',          'pattern' => 'users/me',                                'verb' => 'PUT'),

                // Playlists CRUD
                array('playlist/index',       'pattern' => 'playlists',                               'verb' => 'GET'),
                array('playlist/create',      'pattern' => 'playlists',                               'verb' => 'POST'),
                array('playlist/view',        'pattern' => 'playlists/<id:\d+>',                      'verb' => 'GET'),
                array('playlist/update',      'pattern' => 'playlists/<id:\d+>',                      'verb' => 'PUT'),
                array('playlist/delete',      'pattern' => 'playlists/<id:\d+>',                      'verb' => 'DELETE'),

                // Tracks
                array('track/index',          'pattern' => 'tracks',                                  'verb' => 'GET'),
            ),
        ),

        'log' => array(
            'class'  => 'CLogRouter',
            'routes' => array(
                array(
                    'class'  => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),

        // Sem errorAction — erros não tratados retornam a resposta padrão do PHP
        // (evita loop de erro ao tentar resolver site/error que não existe)
    ),

    'params' => array(
        'jwtSecret' => 'TROQUE-ESTA-CHAVE-EM-PRODUCAO-minimo-32-caracteres!!',
        'jwtExpiry' => 86400, // 24 horas
    ),
);
