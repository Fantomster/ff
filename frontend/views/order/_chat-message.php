<?=

$isSystem ?
        "<p>[" . Yii::$app->formatter->asTime($time, "php:Y-m-d H:i:s") . "] <i>" . $message . "</i></p>" :
        "<p>[" . Yii::$app->formatter->asTime($time, "php:Y-m-d H:i:s") . "] <strong>" . $name . "</strong>: " . $message . "</p>"
?>