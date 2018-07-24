<?php

use yii\db\Migration;

/**
 * Class m180724_141721_api_web_translation
 */
class m180724_141721_api_web_translation extends Migration
{
    public $translations_ru = [
        'bad_password' => 'Это плохой пароль, придумайте другой',
        'same_password' => 'Вы отправили один и тот же пароль.',
        'bad_format_phone' => 'Введите номер в формате +79112223344',
        'bad_format_code' => 'Введите код в формате 9999',
        'not_code_to_change_phone' => 'Вы еще не запросили код для смены телефона.',
    ];

    public $translations_en = [
        'bad_password' => 'It is a bad password, create another',
        'same_password' => 'You have sent the same password.',
        'bad_format_phone' => 'Enter a phone number in the format +79112223344',
        'bad_format_code' => 'Enter a code in the format 9999',
        'not_code_to_change_phone' => 'You have not requested a code to change the phone.',
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
