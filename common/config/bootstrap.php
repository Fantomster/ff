<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@market', dirname(dirname(__DIR__)) . '/market');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@franchise', dirname(dirname(__DIR__)) . '/franchise');
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');


// настройки локальной машины
$path_local = __DIR__ . '/bootstrap.local.php';
if(file_exists($path_local)) {
    require $path_local;
}