<?php

use yii\db\Migration;

/**
 * Handles adding kpp to table `organization`.
 */
class m180808_131453_add_kpp_column_to_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'kpp', $this->string(15)->null());
        $this->addCommentOnColumn('{{%organization}}', 'kpp', 'КПП организации');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'kpp');
    }
}
