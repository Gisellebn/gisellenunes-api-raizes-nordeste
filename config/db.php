<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_SERVER['DB_DSN'] ?? getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=raizes_nordeste',
    'username' => $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'postgres',
    'password' => $_SERVER['DB_PASSWORD'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '',
    'charset' => 'utf8',
];
