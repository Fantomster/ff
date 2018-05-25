<?php

use yii\db\Migration;

/**
 * Class m180412_124241_add_rulon_to_mp_ed
 */
class m180412_124241_add_rulon_to_mp_ed extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%mp_ed}}', ['name'], [
            ['рулон']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%mp_ed}}', ['name'=>'рулон']);
        return false;
    }
}
