<?php

use yii\db\Migration;

/**
 * Class m180215_121905_update_manager_notifications_data_again
 */
class m180215_121905_update_manager_notifications_data_again extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $supplierManagers = \common\models\User::findAll(['role_id' => \common\models\Role::ROLE_SUPPLIER_MANAGER]);
        if ($supplierManagers) {
            foreach ($supplierManagers as $manager) {
                $userId = $manager->id;
                $organizationId = $manager->organization_id;
                $clients = \common\models\RelationSuppRest::findAll(['supp_org_id' => $organizationId]);
                if ($clients){
                    foreach ($clients as $client){
                        $clientId = $client->rest_org_id;
                        $ma = \common\models\ManagerAssociate::findAll(['manager_id'=>$userId, 'organization_id'=>$clientId]);
                        if(!$ma){
                            $this->insert('{{%manager_associate}}', ['manager_id'=>$userId, 'organization_id'=>$clientId]);
                        }
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
        echo "m180215_121905_update_manager_notifications_data_again cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180215_121905_update_manager_notifications_data_again cannot be reverted.\n";

        return false;
    }
    */
}
