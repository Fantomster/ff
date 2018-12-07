<?php

use yii\db\Migration;

/**
 * Class m181207_063158_add_rule_column_in_edi_org
 */
class m181207_063158_add_rule_column_in_edi_org extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%edi_organization}}', 'pricat_action_attribute_rule', $this->tinyInteger()->defaultValue(2));
        $this->addCommentOnColumn('{{%edi_organization}}', 'pricat_action_attribute_rule', 'Тип обработки документа pricat');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dripColumn('{{%edi_organization}}', 'pricat_action_attribute_rule');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181207_063158_add_rule_column_in_edi_org cannot be reverted.\n";

        return false;
    }
    */
}
