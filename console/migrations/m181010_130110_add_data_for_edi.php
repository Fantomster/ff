<?php

use yii\db\Migration;

/**
 * Class m181010_130110_add_data_for_edi
 */
class m181010_130110_add_data_for_edi extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert("{{%order_status}}", ['denom' => 'STATUS_EDI_SENDING_TO_VENDOR', 'comment' => 'Отправка поставщику', 'comment_edi' => 'Заказ отправлен в очередь для отправки ПОСТАВЩИКУ']);
        $this->insert("{{%order_status}}", ['denom' => 'STATUS_EDI_SENDING_ERROR', 'comment' => 'Ошибка отправки', 'comment_edi' => 'Отправка по EDI вернула ошибку']);
        $this->addColumn("{{%order}}", "edi_error_message", $this->string(50));
        $this->addCommentOnColumn("{{%order}}", "edi_error_message", "Сообщение об ошибке от EDI");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("{{%order}}", "edi_error_message");
    }
}
