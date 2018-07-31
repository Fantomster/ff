<?php

use yii\db\Migration;

/**
 * Class m180730_160314_rename_inn_kpp_column
 */
class m180730_160314_rename_inn_kpp_column extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('one_s_contragent', 'inn', 'inn_kpp');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->renameColumn('one_s_contragent', 'inn_kpp', 'inn');
    }
}
