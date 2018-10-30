<?php

use yii\db\Migration;

/**
 * Class m181026_161552_translation_vetis
 */
class m181026_161552_translation_vetis extends Migration
{
    public $translations = [
        'vetis.active_license_not_found' => 'Нет активной лицензии для доступа к этой функции',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
