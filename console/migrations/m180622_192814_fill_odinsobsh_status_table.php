<?php

use yii\db\Migration;

/**
 * Class m180622_192814_fill_odinsobsh_status_table
 */
class m180622_192814_fill_odinsobsh_status_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public $rows = [
        ['Синхронизирован'],
        ['Ошибка при синхронизации'],
        ['Синхронизация не проводилась']
    ];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->batchInsert('{{%one_s_dicstatus}}', ['denom'], $this->rows);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        foreach ($this->rows as $d) {
            $this->delete('{{%one_s_dicstatus}}', 'denom = :d', [':d' => $d[0]]);
        }
    }
}
