<?php

use yii\db\Migration;

/**
 * Class m181017_102220_add_email_notification_column
 */
class m181017_102220_add_email_notification_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //Уведомления для пользователя
        $this->addColumn('email_notification', 'merc_stock_expiry', $this->integer(1)->null()->defaultValue(0));
        $this->addCommentOnColumn('email_notification', 'merc_stock_expiry', 'Уведомление о проблемной продукции в меркурии');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //Уведомления для пользователя
        $this->dropColumn('email_notification', 'merc_stock_expiry');

        return false;
    }
}
