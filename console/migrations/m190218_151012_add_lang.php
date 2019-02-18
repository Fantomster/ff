<?php

use yii\db\Migration;

/**
 * Class m190218_151012_add_lang
 */
class m190218_151012_add_lang extends Migration
{
    public $translations = [
        'preorder.repeat_order_not_canceled' => 'Можно повторять только отмененный заказ',
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
