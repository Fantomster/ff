<?php

use yii\db\Migration;

/**
 * Class m180606_075917_add_one_s_integration_role
 */
class m180606_075917_add_one_s_integration_role extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%role}}', ['id', 'name', 'can_admin', 'can_manage', 'can_observe', 'organization_type'], [
            [\common\models\Role::ROLE_ONE_S_INTEGRATION, '1С интеграция', 0, 0, 0, \common\models\Organization::TYPE_RESTAURANT],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180606_075917_add_one_s_integration_role cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180606_075917_add_one_s_integration_role cannot be reverted.\n";

        return false;
    }
    */
}
