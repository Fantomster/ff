<?php

use yii\db\Migration;

/**
 * Class m180314_065559_rename_user_roles_for_client
 */
class m180314_065559_rename_user_roles_for_client extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('role', ['name' => 'Руководитель'], ['id' => 3]);
        $this->update('role', ['name' => 'Менеджер'], ['id' => 4]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('role', ['name' => 'Менеджер'], ['id' => 3]);
        $this->update('role', ['name' => 'Работник'], ['id' => 4]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180314_065559_rename_user_roles_for_client cannot be reverted.\n";

        return false;
    }
    */
}
