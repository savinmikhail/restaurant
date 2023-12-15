<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

//C3 Code Coverage
define('C3_CODECOVERAGE_ERROR_LOG_FILE', '/path/to/c3_error.log'); //Optional (if not set the default c3 output dir will be used)
include '/../c3.php';

define('MY_APP_STARTED', true);
// App::start();


$config = require __DIR__ . '/../config/test.php';

(new yii\web\Application($config))->run();
