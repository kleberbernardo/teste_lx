<?php
// Credenciais do banco de dados
// Docker: host=db (nome do servico no docker-compose)
// Local:  troque para host=127.0.0.1
return array(
    'class'            => 'CDbConnection',
    'connectionString' => 'mysql:host=db;dbname=playlist_db;charset=utf8mb4',
    'username'         => 'playlist',
    'password'         => 'playlist123',
    'charset'          => 'utf8mb4',
    'tablePrefix'      => '',
    'emulatePrepare'   => false,
    'attributes'       => array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ),
);
