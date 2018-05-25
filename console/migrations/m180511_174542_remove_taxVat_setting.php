<?php

use yii\db\Migration;

/**
 * Class m180511_174542_remove_taxVat_setting
 */
class m180511_174542_remove_taxVat_setting extends Migration
{

    public $tableName = '{{%rk_dicconst}}';

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
        $sql = " UPDATE {$this->tableName} SET is_active = 0 where denom = 'useTaxVat';";
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = " UPDATE {$this->tableName} SET is_active = 1 where denom = 'useTaxVat';";
        $this->execute($sql);
    }


}
