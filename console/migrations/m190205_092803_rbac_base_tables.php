<?php
require_once \Yii::getAlias('@yii/rbac/migrations/m140506_102106_rbac_init.php');
require_once \Yii::getAlias('@yii/rbac/migrations/m170907_052038_rbac_add_index_on_auth_assignment_user_id.php');
require_once \Yii::getAlias('@yii/rbac/migrations/m180523_151638_rbac_updates_indexes_without_prefix.php');

use yii\db\Migration;

class m190205_092803_rbac_base_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        (new m140506_102106_rbac_init())->up();
        (new m170907_052038_rbac_add_index_on_auth_assignment_user_id())->up();
        (new m180523_151638_rbac_updates_indexes_without_prefix())->up();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        (new m180523_151638_rbac_updates_indexes_without_prefix())->down();
        (new m170907_052038_rbac_add_index_on_auth_assignment_user_id())->down();
        (new m140506_102106_rbac_init())->down();
    }
}
