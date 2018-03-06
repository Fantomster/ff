<?php

use yii\db\Migration;

/**
 * Class m180228_170234_addConstant_usetaxVat_nds
 */
class m180228_170234_addConstant_usetaxVat_nds extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert('{{%rk_dicconst}}',[ 'denom' => 'useTaxVat', 'def_value' =>'1', 'comment' => 'Использование ставки НДС по-умолчанию','type'=>1, 'is_active' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%rk_dicconst}}',['denom' =>'useTaxVat']);
    }

}
