<?php

use yii\db\Migration;

/**
 * Class m180725_080240_transtale_api_web
 */
class m180725_080240_transtale_api_web extends Migration
{
    public $translations_ru = [
        'param_value_to_large' => 'Значение параметра %s слишком большое. Допустимое значение %s'
    ];

    public $translations_en = [
        'param_value_to_large' => 'The value of parameter %s is too large. Acceptable value %s'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::insertCategory('en', 'api_web', $this->translations_en);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::deleteCategory('en', 'api_web', $this->translations_en);
    }
}
