<?php

namespace common\components;
use yii\helpers\Html;
use yii\helpers\Url;


/**
 * Helper for making std url in pjax grid
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-15
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */
class UrlPjax
{

    public static function make(string $title, string $url, string $value = NULL, string $identifier = 'id') {
        if (!$title) {$title = '';}
        if (!$value) {$value = $title;}
        return Html::a($title, Url::to([$url, $identifier => $value]), ['class' => 'target-blank', 'data-pjax' => "0"]);
    }

}