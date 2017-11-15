<?php

use yii\db\Migration;

/**
 * Class m171114_150037_addColumnActiveTableDicconst
 */
class m171114_150037_addColumnActiveTableDicconst extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('rk_dicconst','is_active', $this->integer(2));

        $this->update('rk_dicconst',['is_active' => 0],'');
        $this->update('rk_dicconst',['is_active' => 1], ['in','id',[1,4,7]]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('rk_dicconst', 'is_active');
    }

}
