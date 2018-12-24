<?php

use yii\db\Migration;

class m181224_141223_add_translate_settings_save extends Migration
{
    public $translations_ru = [
        'api_web.moderation_setting_save_msg' => 'Ваши настройки отправлены на модерацию',
    ];

    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
