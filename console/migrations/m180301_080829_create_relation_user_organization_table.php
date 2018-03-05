<?php

use yii\db\Migration;

/**
 * Handles the creation of table `relation_user_organization`.
 */
class m180301_080829_create_relation_user_organization_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%relation_user_organization}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'organization_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull(),
            'is_active' => $this->boolean()->defaultValue(1),
        ], $tableOptions);

        $this->addForeignKey('{{%relation_user_id}}', '{{%relation_user_organization}}', 'user_id', '{{%user}}', 'id');
        $this->addForeignKey('{{%relation_organization_id_two}}', '{{%relation_user_organization}}', 'organization_id', '{{%organization}}', 'id');
        $this->addForeignKey('{{%relation_user_role_id}}', '{{%relation_user_organization}}', 'role_id', '{{%role}}', 'id');

        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        $ids = (new \yii\db\Query())->select(['id', 'organization_id', 'role_id'])->from('user')->where(['not', ['organization_id'=>null]])->all();
        foreach ($ids as $one){
            $params = ['user_id' => $one['id'], 'organization_id' => $one['organization_id'], 'role_id' => $one['role_id']];
            $this->insert('relation_user_organization', $params);
        }
        //$this->dropForeignKey('organization', '{{%user}}');
        //$this->dropColumn('{{%user}}', 'organization_id');
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%relation_user_id}}', '{{%relation_user_organization}}');
        $this->dropForeignKey('{{%relation_organization_id_two}}', '{{%relation_user_organization}}');
        $this->dropForeignKey('{{%relation_user_role_id}}', '{{%relation_user_organization}}');
        $this->dropTable('{{%relation_user_organization}}');
    }
}
