<?php

use yii\db\Migration;

/**
 * Class m180406_120412_update_relation_user_organization_table_two
 */
class m180406_120412_update_relation_user_organization_table_two extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $ids = (new \yii\db\Query())->select(['id', 'user_id', 'organization_id', 'role_id'])->from('relation_user_organization')->all();
        foreach ($ids as $one){
            $children = (new \yii\db\Query())->select(['type_id'])->from('organization')->where(['id'=>$one['organization_id']])->one();
            if($children['type_id']==1){
                if($one['role_id']==5){
                    $this->update('relation_user_organization', ['role_id'=>3], ['id'=>$one['id']]);
                }
                if($one['role_id']==6){
                    $this->update('relation_user_organization', ['role_id'=>4], ['id'=>$one['id']]);
                }
            }
            if($children['type_id']==2){
                if($one['role_id']==3){
                    $this->update('relation_user_organization', ['role_id'=>5], ['id'=>$one['id']]);
                }
                if($one['role_id']==4){
                    $this->update('relation_user_organization', ['role_id'=>6], ['id'=>$one['id']]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180406_120412_update_relation_user_organization_table_two cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180406_120412_update_relation_user_organization_table_two cannot be reverted.\n";

        return false;
    }
    */
}
