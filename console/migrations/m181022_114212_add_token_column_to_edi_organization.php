<?php

use yii\db\Migration;

/**
 * Class m181022_114212_add_token_column_to_edi_organization
 */
class m181022_114212_add_token_column_to_edi_organization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%edi_organization}}', 'token', $this->string(150));
        $this->addCommentOnColumn('{{%edi_organization}}', 'token', 'Токен юзера в системе Leradata');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%edi_organization}}', 'token');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181022_114212_add_token_column_to_edi_organization cannot be reverted.\n";

        return false;
    }
    */
}
