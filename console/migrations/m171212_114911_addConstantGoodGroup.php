<?php

use yii\db\Migration;

/**
 * Class m171212_114911_addConstantGoodGroup
 */
class m171212_114911_addConstantGoodGroup extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'defGoodGroup', 'def_value' =>'1', 'comment' => 'Группа справочника Товары для ограничения выгрузки','type'=>2, 'is_active' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%rk_dicconst}}',['denom' =>'defGoodGroup']);
    }

}
