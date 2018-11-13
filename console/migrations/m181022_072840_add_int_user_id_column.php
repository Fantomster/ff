<?php

use yii\db\Migration;

/**
 * Class m181022_072840_add_int_user_id_column
 */
class m181022_072840_add_int_user_id_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%edi_organization}}', 'int_user_id', $this->string(50));
        $this->addCommentOnColumn('{{%edi_organization}}', 'int_user_id', 'intUserID - ID юзера в системе Leradata');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%edi_organization}}', 'int_user_id');
    }
}
