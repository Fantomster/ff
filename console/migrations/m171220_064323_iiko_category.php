<?php

use yii\db\Migration;

/**
 * Class m171220_064323_iiko_category
 */
class m171220_064323_iiko_category extends Migration
{

    public $tableName = '{{%iiko_category}}';
    /**
     * @inheritdoc
     */

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->createTable($this->tableName,[
            'id' => $this->primaryKey(),
            'uuid' => $this->string(36)->notNull(),
            'parent_uuid' => $this->string(36)->null(),
            'denom' => $this->string(250)->notNull(),
            'group_type' => $this->string(250)->null(),
            'org_id' => $this->integer()->notNull(),
            'active' => $this->integer(1)->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->null()
        ]);

        $this->createIndex('idx-category-org_id', $this->tableName, 'org_id');
        $this->createIndex('idx-category-uuid', $this->tableName, 'uuid');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-category-uuid', $this->tableName);
        $this->dropIndex('idx-category-org_id', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
