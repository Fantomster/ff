<?php

use yii\db\Migration;

/**
 * Class m181114_114410_add_traslations_for_settings_errors
 */
class m181114_114410_add_traslations_for_settings_errors extends Migration
{
    public $translations = [
        'setting.main_org_equal_child_org' => 'Бизнес не может быть одновременно и главным, и дочерним.'
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
