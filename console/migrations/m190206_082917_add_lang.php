<?php

use yii\db\Migration;

/**
 * Class m190206_082917_add_lang
 */
class m190206_082917_add_lang extends Migration
{
    public $translations = [
        'common.models.order.status_preorder' => 'Предзаказ'
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'app', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'app', $this->translations);
    }
}
