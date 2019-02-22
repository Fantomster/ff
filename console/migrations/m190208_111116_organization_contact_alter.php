<?php

use yii\db\Migration;

/**
 * Class m190208_111116_organization_contact_alter
 */
class m190208_111116_organization_contact_alter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn(\common\models\OrganizationContactNotification::tableName(), 'order_create', 'order_created');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190208_111116_organization_contact_alter cannot be reverted.\n";
        return false;
    }
}
