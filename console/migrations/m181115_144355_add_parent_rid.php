<?php

use yii\db\Migration;

/**
 * Class m181115_144355_add_parent_rid
 */
class m181115_144355_add_parent_rid extends Migration
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
        $this->addColumn(\common\models\OuterCategory::tableName(), 'parent_rid', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\OuterCategory::tableName(), 'parent_rid');
    }
}
