<?php

use yii\db\Migration;

class m170124_091602_add_tobacco extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Табачные изделия', NULL],
            ['Сигареты',207],
            ['Сигары',207],
            ['Табак для кальяна',207],
        ]);
    }

    public function safeDown()
    {
        //
    }
}
