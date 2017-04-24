<?php

use yii\db\Migration;

class m170411_091627_fill_franchisee_types extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%franchise_type}}', ['name', 'share', 'price'], [
            ['Стартап', 35, 1],
            ['Предприниматель', 70, 2],
            ['Инвестор', 80, 3]
        ]);
    }

    public function safeDown()
    {
    }
}
