<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 29.09.2018
 * Time: 23:05
 */
//define('YII_ENV', 'test');
//defined('YII_DEBUG') or define('YII_DEBUG', true);
//require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
//require __DIR__ . '/../vendor/autoload.php';
//
//$config = require __DIR__ . '/../config/test.php';
//Yii::setAlias('@tests', __DIR__);


defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../console/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/../console/config/main.php'),
    require(__DIR__ . '/../console/config/main-local.php')
);

new yii\console\Application($config);
Yii::setAlias('@tests', __DIR__);