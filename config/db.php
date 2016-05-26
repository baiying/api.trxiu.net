<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=show',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    // 配置从服务器
    'slaveConfig' => [
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'attributes' => [
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],
    // 配置从服务器组
    'slaves' => [
        ['dsn' => 'mysql:host=localhost;dbname=show'],
    ],
];
