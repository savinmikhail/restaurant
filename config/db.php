<?php

/* $host = getenv('DB_HOST');
$dbname= getenv('DB_NAME');
$pwd = getenv('DB_PASSWORD');
$user = getenv('DB_USER');
return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host=$host;dbname=$dbname",
    'username' => "$user",
    'password' => "$pwd",
    'charset' => 'utf8',

    'enableLogging' => true,
    'enableProfiling' => true,

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
*/
/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.self::env('DB_HOST', 'localhost').';dbname='.self::env('DB_NAME', 'kintsugi'),
    'username' => self::env('DB_USER', 'local'),
    'password' => self::env('DB_PASSWORD', 'local'),
    'charset' => 'utf8',
    'enableSchemaCache' => ((int)self::env('enableSchemaCache', 0) ? false : true),
    'schemaCacheDuration' => ((int)self::env('schemaCacheDuration', 3600)),
];
*/
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=192.168.0.37;dbname=kintsugi',
    'username' => 'local',
    'password' => 'local',
    'charset' => 'utf8',
//    'enableSchemaCache' => ((int)self::env('enableSchemaCache', 0) ? false : true),
//    'schemaCacheDuration' => ((int)self::env('schemaCacheDuration', 3600)),
];