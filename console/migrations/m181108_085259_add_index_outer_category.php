<?php

use yii\db\Migration;

/**
 * Class m181108_085259_add_index_outer_category
 */
class m181108_085259_add_index_outer_category extends Migration
{
    public $table;

    public $fields = [
        'outer_uid',
        'parent_outer_uid',
        ['service_id', 'org_id'],
        ['left', 'right', 'tree'],
        'level',
        'tree'
    ];

    public function init()
    {
        $this->db = 'db_api';
        $this->table = \common\models\OuterCategory::tableName();
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->fields as $field) {
            $name = is_array($field) ? implode('_', $field) : $field;
            $this->createIndex('idx_' . $name . '_outer_category', $this->table, $field);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->fields as $field) {
            $name = is_array($field) ? implode('_', $field) : $field;
            $this->dropIndex('idx_' . $name . '_outer_category', $this->table);
        }
    }
}
