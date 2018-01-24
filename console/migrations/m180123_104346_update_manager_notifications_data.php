<?php

use yii\db\Migration;

/**
 * Class m180123_104346_update_manager_notifications_data
 */
class m180123_104346_update_manager_notifications_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%email_notification}}', 'receive_employee_email', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%sms_notification}}', 'receive_employee_sms', $this->boolean()->notNull()->defaultValue(false));

        $supplierManagers = \common\models\User::findAll(['role_id' => \common\models\Role::ROLE_SUPPLIER_MANAGER]);
        if ($supplierManagers) {
            foreach ($supplierManagers as $manager) {
                $userId = $manager->id;
                $organizationId = $manager->organization_id;
                    $clients = \common\models\RelationSuppRest::findAll(['supp_org_id' => $organizationId]);
                    if ($clients){
                        foreach ($clients as $client){
                            $clientId = $client->rest_org_id;
                            $this->insert('{{%manager_associate}}', ['manager_id'=>$userId, 'organization_id'=>$clientId]);
                        }
                    }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //$this->dropColumn('{{%email_notification}}', 'receive_employee_email');
        //$this->dropColumn('{{%sms_notification}}', 'receive_employee_sms');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180123_104346_update_manager_notifications_data cannot be reverted.\n";

        return false;
    }
    */
}
