<?php

use yii\db\Migration;

/**
 * Class m180910_170840_db_columns_add_as_in_task_dev1803_1
 */
class m180910_170840_db_columns_add_as_in_task_dev1803_1 extends Migration
{

    public function safeUp()
    {
        #table `order`
        $this->addColumn('{{%order}}', 'service_id', $this->integer(11));
        $this->addColumn('{{%order}}', 'status_updated_at', $this->timestamp()->null());
        $this->addColumn('{{%order}}', 'edi_order', $this->string(45)->defaultValue(null));
        $this->addCommentOnColumn('{{%order}}', 'service_id', 'ID сервиса');
        $this->addCommentOnColumn('{{%order}}', 'status_updated_at', 'Дата обновления статуса');
        $this->addCommentOnColumn('{{%order}}', 'edi_order', 'Номер заказа в EDI');
        #table `order_content`
        $this->addColumn('{{%order_content}}', 'edi_ordersp', $this->string(45)->defaultValue(null));
        $this->addColumn('{{%order_content}}', 'merc_uuid', $this->string(36)->defaultValue(null));
        $this->addColumn('{{%order_content}}', 'vat_product', $this->integer(11));
        $this->addCommentOnColumn('{{%order_content}}', 'edi_ordersp', 'Имя файла ORDERSP который прилетает от поставщик');
        $this->addCommentOnColumn('{{%order_content}}', 'merc_uuid', 'UUID ВСД сертификата');
        $this->addCommentOnColumn('{{%order_content}}', 'vat_product', 'Ставка НДС');

    }

    public function safeDown()
    {
        #table `order_content`
        $this->dropColumn('{{%order_content}}', 'vat_product');
        $this->dropColumn('{{%order_content}}', 'merc_uuid');
        $this->dropColumn('{{%order_content}}', 'edi_ordersp');
        #table `order`
        $this->dropColumn('{{%order}}', 'edi_order');
        $this->dropColumn('{{%order}}', 'status_updated_at');
        $this->dropColumn('{{%order}}', 'service_id');
    }

}
