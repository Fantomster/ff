<?php

use yii\db\Migration;

/**
 * Class m181107_090058_add_lang
 */
class m181107_090058_add_lang extends Migration
{
    public $translations = [
        'dictionary.you_not_work_this_vendor' => 'Вы не работаете с этим поставщиком',
        'dictionary.this_not_you_store'       => 'Это не ваш склад',
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
