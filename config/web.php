<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$user_app = 'api/user_app';
$waiter_app = 'api/waiter_app';

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
            // 'baseUrl' => 'https://kintsugi.dev.redramka.ru', // enforce https

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

//приложение для юзера
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/product"],
                "GET /$user_app/basket" => "$user_app/basket/index",//написано таким образом для реализации RESTful апи
                "POST /$user_app/basket" => "$user_app/basket/add",
                "PUT /$user_app/basket" => "$user_app/basket/set",
                "DELETE /$user_app/basket" => "$user_app/basket/delete",
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/order"],
                "/$user_app/order" => "$user_app/order/index",
                "/$user_app/order/waiter" => "$user_app/order/waiter",
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/catalog"],
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/iiko"],

//приложение для официанта
                ["class" => 'yii\rest\UrlRule', "controller" => "$waiter_app/product"],
                
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
