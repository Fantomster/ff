<?php

use yii\db\Migration;

class m170318_102138_update_category_2 extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Сливочное мороженое',212],
        ]);
    }
}
