<?php

use yii\db\Migration;

/**
 * Class m180321_070855_update_relation_user_organization_table
 */
class m180321_070855_update_relation_user_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('relation_user_organization');
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        $ids = (new \yii\db\Query())->select(['id', 'organization_id', 'role_id'])->from('user')->where(['not', ['organization_id'=>null]])->all();
        $columns = ['user_id', 'organization_id', 'role_id'];
        $params = [];
        foreach ($ids as $one){
            $params[] = [$one['id'], $one['organization_id'], $one['role_id']];

            $children = (new \yii\db\Query())->select(['id'])->from('organization')->where(['parent_id'=>$one['organization_id']])->all();
            foreach ($children as $child){
                if($child['id']){
                    $params[] = [$one['id'], $child['id'], $one['role_id']];
                }
            }

            $parents = (new \yii\db\Query())->select(['parent_id'])->from('organization')->where(['id'=>$one['organization_id']])->all();
            foreach ($parents as $parent){
                if($parent['parent_id']){
                    $params[] = [$one['id'], $parent['parent_id'], $one['role_id']];
                    $children2 = (new \yii\db\Query())->select(['id'])->from('organization')->where(['parent_id'=>$parent['parent_id']])->all();
                    foreach ($children2 as $child2){
                        if($child2['id']){
                            $params[] = [$one['id'], $child2['id'], $one['role_id']];
                        }
                    }
                }
            }
        }
        $this->batchInsert('relation_user_organization', $columns, $params);
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180321_070855_update_relation_user_organization_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180321_070855_update_relation_user_organization_table cannot be reverted.\n";

        return false;
    }
    */
}
