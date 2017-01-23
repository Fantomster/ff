<?php

use yii\db\Migration;

class m170120_093910_new_categories extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Готовые блюда',127],
            ['Какао',127],
            ['Кофе',127],
            ['Чай',127],
        ]);
    }

    public function safeDown()
    {
        //
    }
}
