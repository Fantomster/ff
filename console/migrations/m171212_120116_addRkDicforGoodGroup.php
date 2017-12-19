<?php

use yii\db\Migration;

/**
 * Class m171212_120116_addRkDicforGoodGroup
 */
class m171212_120116_addRkDicforGoodGroup extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('{{%rk_dictype}}',[ 'denom' => 'Товарные группы', 'contr' =>'productgroup', 'created_at' => '2017-12-12 12:00:00' ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%rk_dictype}}',['contr' =>'productgroup']);
    }


}
