<?php

use yii\db\Migration;

/**
 * Class m181109_062620_add_fields_user_agreement_and_confidencial_policy_to_organization_table
 */
class m181109_062620_add_fields_user_agreement_and_confidencial_policy_to_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'user_agreement', $this->tinyInteger()->defaultValue(0)->comment('Флаг принятого пользовательского соглашения, 1 - подтверждено'));
        $this->addColumn('{{%organization}}', 'confidencial_policy', $this->tinyInteger()->defaultValue(0)->comment('Флаг принятой политики конфиденциальности, 1 - подтверждено'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181109_062620_add_fields_user_agreement_and_confidencial_policy_to_organization_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181109_062620_add_fields_user_agreement_and_confidencial_policy_to_organization_table cannot be reverted.\n";

        return false;
    }
    */
}
