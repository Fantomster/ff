<?php

use yii\db\Migration;

/**
 * Class m181025_135206_add_lang_to_license_payment
 */
class m181025_135206_add_lang_to_license_payment extends Migration
{
    public $translations_ru = [
        'license.payment_required' => 'Необходимо продлить лицензию'
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
