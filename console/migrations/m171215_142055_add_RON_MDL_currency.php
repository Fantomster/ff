<?php

use yii\db\Migration;

/**
 * Class m171215_142055_add_RON_MDL_currency
 */
class m171215_142055_add_RON_MDL_currency extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%currency}}', ['text','symbol','num_code','iso_code','signs'], [
            ['Румынский лей', 'L', 4217, 'RON', 2],
            ['Молдавский лей', 'L', 4217, 'MDL',2],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%currency}}',"num_code = 4217");
    }


}
