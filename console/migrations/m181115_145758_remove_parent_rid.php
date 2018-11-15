<?php

use yii\db\Migration;

/**
 * Class m181115_145758_remove_parent_rid
 */
class m181115_145758_remove_parent_rid extends Migration
{
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
        $this->dropColumn(\common\models\OuterCategory::tableName(), 'parent_rid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(\common\models\OuterCategory::tableName(), 'parent_rid', $this->string()->null());
    }
}
