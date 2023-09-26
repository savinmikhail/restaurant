<?php

$host = getenv('DB_HOST');
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
