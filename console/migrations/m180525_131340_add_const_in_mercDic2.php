<?php

use yii\db\Migration;

/**
 * Class m180525_131340_add_const_in_mercDic2
 */
class m180525_131340_add_const_in_mercDic2 extends Migration
{
    public $tableName = '{{%merc_dicconst}}';

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete($this->tableName, ['denom' => 'enterpriseGuid']);
        $this->insert($this->tableName, [
            'denom' => 'enterprise_guid',
            'def_value' => 'EnterpriseGuid',
            'comment' => 'GUID предприятия',
            'type' => 2,
            'is_active' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180511_104644_add_const_in_merc_dictconst cannot be reverted.\n";

        return false;
    }
}
