<?php

use yii\db\Migration;

/**
 * Class m181204_140427_add_lang
 */
class m181204_140427_add_lang extends Migration
{
    public $translations = [
        'dictionary.agent.update.vendor_exists' => 'Этот поставщик уже сопоставлен с другим контрагентом.'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
