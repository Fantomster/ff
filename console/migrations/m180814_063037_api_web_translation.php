<?php

use yii\db\Migration;

/**
 * Class m180814_063037_api_web_translation
 */
class m180814_063037_api_web_translation extends Migration
{
    public $translations_ru = [
        'page_not_found' => 'Страница не найдена',
        'api_web.user.agreement.UserAgreement' => 'UserAgreement Текст',
        'api_web.user.agreement.ConfidencialPolicy' => 'ConfidencialPolicy Текст'
    ];

    public $translations_en = [
        'page_not_found' => 'Page not found',
        'api_web.user.agreement.UserAgreement' => 'UserAgreement TEXT',
        'api_web.user.agreement.ConfidencialPolicy' => 'ConfidencialPolicy TEXT'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::insertCategory('en', 'api_web', $this->translations_en);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
        \console\helpers\BatchTranslations::deleteCategory('en', 'api_web', $this->translations_en);
    }
}
