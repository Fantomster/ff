<?php

use yii\db\Migration;

/**
 * Class m180921_124352_alter_column_comment_edi
 */
class m180921_124352_alter_column_comment_edi extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn("{{%order_status}}", 'comment_edo', 'comment_edi');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn("{{%order_status}}", 'comment_edi', 'comment_edo');
    }
}
