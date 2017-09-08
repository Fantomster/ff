<?php

use yii\db\Migration;

class m170901_085418_insert_new_roles extends Migration
{
    public function safeUp()
    {
        $columns = ['id', 'name', 'organization_type', 'created_at'];
        $this->batchInsert('{{%role}}', $columns, [
            [13, 'Руководитель', 3, gmdate('Y-m-d H:i:s')],
            [14, 'Менеджер', 3, gmdate('Y-m-d H:i:s')],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%role}}', ['id' => 13]);
        $this->delete('{{%role}}', ['id' => 14]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170901_085418_insert_new_roles cannot be reverted.\n";

        return false;
    }
    */
}
