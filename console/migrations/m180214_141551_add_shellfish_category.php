<?php

use yii\db\Migration;

/**
 * Class m180214_141551_add_shellfish_category
 */
class m180214_141551_add_shellfish_category extends Migration
{
    public function safeUp()
    {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Моллюски', 27],
        ]);
    }

    public function safeDown()
    {
        //
    }
}
