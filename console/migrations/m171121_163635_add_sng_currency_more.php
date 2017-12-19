<?php

use yii\db\Migration;

/**
 * Class m171121_163635_add_sng_currency_more
 */
class m171121_163635_add_sng_currency_more extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%currency}}', ['text','symbol','num_code','iso_code','signs'], [
            ['Киргизский сом', 'с', 417, 'KGS', 2],
            ['Азербайджанский манат', '₼', 944, 'AZN',2],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%currency}}',"symbol in ('с','₼')");
    }

}
