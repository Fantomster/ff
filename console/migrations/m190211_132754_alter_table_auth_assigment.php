<?php

use yii\db\Migration;

/**
 * Class m190211_132754_alter_table_auth_assigment
 */
class m190211_132754_alter_table_auth_assigment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('auth_assignment_ibfk_1', '{{%auth_assignment}}');
        $this->dropPrimaryKey('PRIMARY', '{{%auth_assignment}}');
        $this->addColumn('{{%auth_assignment}}', 'id', $this->primaryKey());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('PRIMARY', '{{%auth_assignment}}');

        $this->addPrimaryKey('', '{{%auth_assignment}}', [
            'item_name',
            'user_id'
        ]);

        $this->addForeignKey(
            'auth_assignment_ibfk_1',
            '{{%auth_assignment}}',
            'item_name',
            '{{%auth_item}}',
            'name',
            'CASCADE',
            'CASCADE'
        );
    }
}
