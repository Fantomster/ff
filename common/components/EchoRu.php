<?php

namespace common\components;

use Yii;

/**
 * Helper for messanging in russian
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-14
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */
class EchoRu
{

    /** @var string $message_localization */
    static $message_localization = 'ru';
    /** @var $message_category $message_localization */
    static $message_category = 'message';

    /**
     * @var $index string Translation key
     * @var $body string Text
     * @var $category string Category
     * @return string
     */
    public static function echo(string $index, string $body, string $category = NULL): string
    {
        if (!$category) {
            $category = self::$message_category;
        }
        return Yii::t($category, $index, [self::$message_localization => $body]);
    }

}