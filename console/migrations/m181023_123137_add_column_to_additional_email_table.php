<?php

use yii\db\Migration;

/**
 * Class m181023_123137_add_column_to_additional_email_table
 */
class m181023_123137_add_column_to_additional_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //Уведомления для пользователя
        $this->addColumn('additional_email', 'merc_stock_expiry', $this->integer(1)->null()->defaultValue(0));
        $this->addCommentOnColumn('additional_email', 'merc_stock_expiry', 'Уведомление о проблемной продукции в меркурии');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //Уведомления для пользователя
        $this->dropColumn('additional_email', 'merc_stock_expiry');

        return false;
    }
}
