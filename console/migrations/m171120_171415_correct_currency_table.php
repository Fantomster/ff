<?php

use yii\db\Migration;

/**
 * Class m171120_171415_correct_currency_table
 */
class m171120_171415_correct_currency_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%currency}}',['text' => 'Доллар США'],['symbol' =>'$']);
        $this->update('{{%currency}}',['text' => 'Российский рубль'],['symbol' =>'₽']);
        $this->update('{{%currency}}',['text' => 'Евро'],['symbol' =>'€']);
        $this->update('{{%currency}}',['text' => 'Казахстанский тенге'],['symbol' =>'₸']);
        $this->update('{{%currency}}',['text' => 'Белорусский рубль'],['symbol' =>'Br']);
        $this->update('{{%currency}}',['text' => 'Украинская гривна'],['symbol' =>'₴']);
        $this->update('{{%currency}}',['text' => 'Индонезийская рупия'],['symbol' =>'ക']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171120_171415_correct_currency_table cannot be reverted.\n";

        return false;
    }

}
