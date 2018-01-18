<?php

use yii\db\Migration;

/**
 * Class m171221_073134_add_iiko_dicstatus_rows
 */
class m171221_073134_add_iiko_dicstatus_rows extends Migration
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
        $this->batchInsert('{{%iiko_dicstatus}}', ['denom'], $this->rows);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        foreach ($this->rows as $d) {
            $this->delete('{{%iiko_dicstatus}}', 'denom = :d', [':d' => $d[0]]);
        }
    }
}
