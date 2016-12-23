<?php
use yii\db\Schema;
use yii\db\Migration;

class m161223_092519_insert_ed extends Migration
{
    public function safeUp() {
        $this->batchInsert('{{%mp_ed}}', ['name'], [
            ['Банка']
        ]);
    }
}
