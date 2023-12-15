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
                    'levels' => ['error', 'warning', 'info'],
                    'exportInterval' => 1,
                ],
            ],
        ],

        'db' => $db,

        'session' => [
            'class' => 'yii\web\Session',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

                ['class' => 'yii\rest\UrlRule', 'controller' => 'admin/users'],
                '/admin' => 'admin/admin/index',
                '/admin/logout' => 'admin/admin/logout',
                '/admin/settings' => 'admin/setting/index',

                //приложение для юзера
                "GET /$user_app/products" => "$user_app/product/index",
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/product"],
                "GET /$user_app/basket" => "$user_app/basket/index", //написано таким образом для реализации RESTful апи
                "POST /$user_app/basket" => "$user_app/basket/add",
                "PUT /$user_app/basket" => "$user_app/basket/set",
                "DELETE /$user_app/basket" => "$user_app/basket/delete",
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/order"],
                "/$user_app/order" => "$user_app/order/index",
                "/$user_app/table/waiter" => "$user_app/table/waiter",
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/catalog"],
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/iiko"],
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/iiko-transport"],
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/iiko-config"],
                ["class" => 'yii\rest\UrlRule', "controller" => "$user_app/payment"],


                //приложение для официанта
                "GET /$waiter_app/products" => "$waiter_app/product/index",
                "GET /$waiter_app/orders" => "$waiter_app/order/list",
                ["class" => 'yii\rest\UrlRule', "controller" => "$waiter_app/order"],
                ["class" => 'yii\rest\UrlRule', "controller" => "$waiter_app/user"],

            ],
        ],

    ],
    'params' => $params,

    //DI
    'container' => [
        'definitions' => [

            //user_app here
            'app\Services\api\user_app\OrderService' => [
                'class' => 'app\Services\api\user_app\OrderService',
                // Any additional configuration for the Service
            ],
            'app\Services\api\user_app\ProductService' => [
                'class' => 'app\Services\api\user_app\ProductService',
            ],
            'app\Services\api\user_app\IikoConfigService' => [
                'class' => 'app\Services\api\user_app\IikoConfigService',
            ],
            'app\Services\api\user_app\IikoService' => [
                'class' => 'app\Services\api\user_app\IikoService',
            ],
            'app\Services\api\user_app\IikoTransportService' => [
                'class' => 'app\Services\api\user_app\IikoTransportService',
            ],
            'app\Services\api\user_app\Payment' => [
                'class' => 'app\Services\api\user_app\Payment',
            ],
            'app\Services\api\user_app\PaymentService' => [
                'class' => 'app\Services\api\user_app\PaymentService',
            ],
            'app\Services\api\user_app\BasketService' => [
                'class' => 'app\Services\api\user_app\BasketService',
            ],
            'app\Services\api\user_app\TableService' => [
                'class' => 'app\Services\api\user_app\TableService',
            ],
            'app\Services\api\user_app\import_helpers\GroupHelper' => [
                'class' => 'app\Services\api\user_app\import_helpers\GroupHelper',
            ],
            'app\Services\api\user_app\import_helpers\SizeHelper' => [
                'class' => 'app\Services\api\user_app\import_helpers\SizeHelper',
            ],
            'app\Services\api\user_app\import_helpers\CategoryHelper' => [
                'class' => 'app\Services\api\user_app\import_helpers\CategoryHelper',
            ],
            'app\Services\api\user_app\import_helpers\ProductHelper' => [
                'class' => 'app\Services\api\user_app\import_helpers\ProductHelper',
            ],

            //waiter_app here
            'app\Services\api\waiter_app\OrderService' => [
                'class' => 'app\Services\api\waiter_app\OrderService',
            ],
            'app\Services\api\waiter_app\ProductService' => [
                'class' => 'app\Services\api\waiter_app\ProductService',
            ],
        ],
    ],

];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
    'class' => 'yii\debug\Module',
    // uncomment the following to add your IP if you are not connecting from localhost.
    'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    // uncomment the following to add your IP if you are not connecting from localhost.
    'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
