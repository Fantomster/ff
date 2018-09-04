<?php

use yii\db\Migration;

/**
 * Class m180904_101630_add_column_to_vetis_enterprise_column
 */
class m180904_101630_add_column_to_vetis_enterprise_column extends Migration
{

    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vetis_russian_enterprise}}', 'owner_guid', $this->string()->notNull());
        $this->addColumn('{{%vetis_russian_enterprise}}', 'owner_uuid', $this->string()->notNull());
        $this->addColumn('{{%vetis_foreign_enterprise}}', 'owner_guid', $this->string()->notNull());
        $this->addColumn('{{%vetis_foreign_enterprise}}', 'owner_uuid', $this->string()->notNull());
        $this->addCommentOnColumn('{{%vetis_russian_enterprise}}', 'owner_guid','Глобальный идентификатор хозяйствующего субъекта владельца');
        $this->addCommentOnColumn('{{%vetis_russian_enterprise}}', 'owner_uuid','Идентификатор хозяйствующего субъекта владельца');
        $this->addCommentOnColumn('{{%vetis_foreign_enterprise}}', 'owner_guid','Глобальный идентификатор хозяйствующего субъекта');
        $this->addCommentOnColumn('{{%vetis_foreign_enterprise}}', 'owner_uuid','');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vetis_russian_enterprise}}', 'owner_guid');
        $this->dropColumn('{{%vetis_russian_enterprise}}', 'owner_uuid');
        $this->dropColumn('{{%vetis_foreign_enterprise}}', 'owner_guid');
        $this->dropColumn('{{%vetis_foreign_enterprise}}', 'owner_uuid');

        return false;
    }
}
