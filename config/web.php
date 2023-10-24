<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'I-nhxZFlYofu09obq92_GxLG_BNzxZQC',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser'
            ],
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
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'session' => [
            'class' => 'yii\web\Session',
            // Other session configuration options here
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

                ['class' => 'yii\rest\UrlRule', 'controller' => 'admin/category'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'admin/product'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'admin/table'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'admin/orders'],

                '/admin/settings' => 'admin/setting/index',


                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/product'],
                // ['class' => 'yii\rest\UrlRule', 'controller' => 'api/basket'],

                'GET /api/basket' => 'api/basket/index',
                'POST /api/basket' => 'api/basket/add',
                'PUT /api/basket' => 'api/basket/set',
                'DELETE /api/basket' => 'api/basket/delete',

                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/order'],
                '/api/order' => 'api/order/index',

                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/catalog'],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/iiko'],

            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
