<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=192.168.0.37;dbname=kintsugi',
    'username' => 'local',
    'password' => 'local',
    'charset' => 'utf8',

    'enableLogging' => true,
    'enableProfiling' => true,

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
