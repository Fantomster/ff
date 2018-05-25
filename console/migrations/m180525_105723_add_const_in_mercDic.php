<?php

use yii\db\Migration;

/**
 * Class m180525_105723_add_const_in_mercDic
 */
class m180525_105723_add_const_in_mercDic extends Migration
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
        $this->delete($this->tableName, ['denom' => 'org_name']);
        $this->insert($this->tableName, [
            'denom' => 'enterpriseGuid',
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
