<?php

use yii\db\Migration;

/**
 * Class m171120_123404_add_columns_iso_to_currency
 */
class m171120_123404_add_columns_iso_to_currency extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%currency}}','num_code', $this->integer(11));
        $this->addColumn('{{%currency}}','iso_code', $this->string(3));
        $this->addColumn('{{%currency}}','signs', $this->integer(11));


        $this->update('{{%currency}}',['num_code' => 840, 'iso_code' => 'USD', 'signs' => 2],['symbol' =>'$']);
        $this->update('{{%currency}}',['num_code' => 643, 'iso_code' => 'RUB', 'signs' => 2],['symbol' =>'₽']);
        $this->update('{{%currency}}',['num_code' => 978, 'iso_code' => 'EUR', 'signs' => 2],['symbol' =>'€']);
        $this->update('{{%currency}}',['num_code' => 398, 'iso_code' => 'KZT', 'signs' => 2],['symbol' =>'₸']);
        $this->update('{{%currency}}',['num_code' => 933, 'iso_code' => 'BYN', 'signs' => 2],['symbol' =>'Br']);
        $this->update('{{%currency}}',['num_code' => 980, 'iso_code' => 'UAH', 'signs' => 2],['symbol' =>'₴']);
        $this->update('{{%currency}}',['num_code' => 360, 'iso_code' => 'IDR', 'signs' => 2],['symbol' =>'ക']);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%currency}}','num_code');
        $this->dropColumn('{{%currency}}','iso_code');
        $this->dropColumn('{{%currency}}','signs');
    }

}
