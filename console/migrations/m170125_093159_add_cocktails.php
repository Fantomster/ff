<?php

use yii\db\Migration;

class m170125_093159_add_cocktails extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Коктейли', 51],
        ]);
    }

    public function safeDown()
    {
        //
    }
}
