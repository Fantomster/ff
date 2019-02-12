<?php

use yii\db\Migration;

class m190211_070555_rbac_assigning_roles_to_current_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            '{{%auth_assignment}}',
            'created_at', $this->timestamp()
            ->defaultValue((new \yii\db\Expression('NOW()')))
        );

        $this->addColumn(
            '{{%auth_assignment}}',
            'organization_id',
            $this->integer(11)->notNull()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%auth_assignment}}', 'organization_id');
        $this->alterColumn('{{%auth_assignment}}', 'created_at', $this->integer(11));
    }
}
