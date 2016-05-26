<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'name' => 'api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'ckBfTqgmPZh6VBAcNy5nTaiNQx-Df2ki',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning'],
                    'categories' => ['service_warning'],
                    'logFile' => '@app/runtime/logs/service/service_warning.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ]
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'weixin' => [
            'class'         => 'app\components\Weixin',
            'appid'         => 'wxacb4aa230696bd1d',
            'appsecret'     => 'a60fc97888aded50ae333bc00ce0c6a3',
            'encodingAesKey'=> '',
            'token'         => '13651006864'
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
