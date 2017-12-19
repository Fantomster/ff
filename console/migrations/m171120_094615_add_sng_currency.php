<?php

use yii\db\Migration;

/**
 * Class m171120_094615_add_sng_currency
 */
class m171120_094615_add_sng_currency extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%currency}}', ['text','symbol'], [
            ['Евро - €', '€'],
            ['Казахстанский тенге - ₸', '₸'],
            ['Белорусский рубль - Br', 'Br'],
            ['Украинская гривна - ₴', '₴'],
            ['Индонезийская рупия - ക', 'ക'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%currency}}',"symbol in ('€','Br','₸','₴','ക')");
    }

}
