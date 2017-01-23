<?php

use yii\db\Migration;

class m161223_124842_insert_category extends Migration
{
    public function safeUp() {
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['Водоросли',27]
        ]);
    }
}
