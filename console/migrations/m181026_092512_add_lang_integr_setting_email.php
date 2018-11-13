<?php

use yii\db\Migration;

/**
 * Class m181026_092512_add_lang_integr_setting_email
 */
class m181026_092512_add_lang_integr_setting_email extends Migration
{
    public $translations_ru = [
        'integration.email.setting_not_found' => 'Настройки с таким id не найдено в вашей организации'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
