<?php

use yii\db\Migration;

/**
 * Class m180910_170840_db_columns_add_as_in_task_dev1803_2
 */
class m180910_170840_db_columns_add_as_in_task_dev1803_2 extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        #table `waybill`
        $this->addColumn('{{%waybill}}', 'edi_number', $this->string(45)->defaultValue(null));
        $this->addColumn('{{%waybill}}', 'edi_recadv', $this->string(45)->defaultValue(null));
        $this->addColumn('{{%waybill}}', 'edi_invoice', $this->string(45)->defaultValue(null));
        $this->addColumn('{{%waybill}}', 'doc_date', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'is_duedate', $this->tinyInteger());
        $this->addColumn('{{%waybill}}', 'is_deleted', $this->tinyInteger());
        $this->addColumn('{{%waybill}}', 'created_at', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'updated_at', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'exported_at', $this->timestamp());
        $this->addColumn('{{%waybill}}', 'payment_delay', $this->integer(11));
        $this->addColumn('{{%waybill}}', 'payment_delay_date', $this->timestamp());

        // $this->addCommentOnColumn('{{%waybill}}', 'edi_number', '');
        // $this->addCommentOnColumn('{{%waybill}}', 'edi_recadv', '');
        // $this->addCommentOnColumn('{{%waybill}}', 'edi_invoice', '');
        $this->addCommentOnColumn('{{%waybill}}', 'doc_date', 'Дата документа');
        // $this->addCommentOnColumn('{{%waybill}}', 'is_duedate', '');
        // $this->addCommentOnColumn('{{%waybill}}', 'is_deleted', '');
        $this->addCommentOnColumn('{{%waybill}}', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('{{%waybill}}', 'updated_at', 'Дата обновления');
        $this->addCommentOnColumn('{{%waybill}}', 'exported_at', 'Дата выгрузки');
        $this->addCommentOnColumn('{{%waybill}}', 'payment_delay', 'Отсрочка платежа');
        $this->addCommentOnColumn('{{%waybill}}', 'payment_delay_date', 'Дата отсрочки платежа');

        #table `waybill_content`
        $this->addColumn('{{%waybill_content}}', 'edi_desadv', $this->string(45)->defaultValue(null));
        $this->addColumn('{{%waybill_content}}', 'edi_alcdes', $this->string(45)->defaultValue(null));
        $this->addColumn('{{%waybill_content}}', 'sum_with_vat', $this->integer(11));
        $this->addColumn('{{%waybill_content}}', 'sum_without_vat', $this->integer(11));
        $this->addColumn('{{%waybill_content}}', 'price_with_vat', $this->integer(11));
        $this->addColumn('{{%waybill_content}}', 'price_without_vat', $this->integer(11));

        // $this->addCommentOnColumn('{{%waybill_content}}', 'edi_desadv', '');
        // $this->addCommentOnColumn('{{%waybill_content}}', 'edi_alcdes', '');
        $this->addCommentOnColumn('{{%waybill_content}}', 'sum_with_vat', 'Сумма с учетом НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'sum_without_vat', 'Сумма без учета НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'price_with_vat', 'Цена с учетом НДС');
        $this->addCommentOnColumn('{{%waybill_content}}', 'price_without_vat', 'Цена без учета НДС');
    }

    public function safeDown()
    {
        #table `waybill_content`
        $this->dropColumn('{{%waybill_content}}', 'price_without_vat');
        $this->dropColumn('{{%waybill_content}}', 'price_with_vat');
        $this->dropColumn('{{%waybill_content}}', 'sum_without_vat');
        $this->dropColumn('{{%waybill_content}}', 'sum_with_vat');
        $this->dropColumn('{{%waybill_content}}', 'edi_alcdes');
        $this->dropColumn('{{%waybill_content}}', 'edi_desadv');
        #table `waybill`
        $this->dropColumn('{{%waybill}}', 'payment_delay_date');
        $this->dropColumn('{{%waybill}}', 'payment_delay');
        $this->dropColumn('{{%waybill}}', 'exported_at');
        $this->dropColumn('{{%waybill}}', 'updated_at');
        $this->dropColumn('{{%waybill}}', 'created_at');
        $this->dropColumn('{{%waybill}}', 'is_deleted');
        $this->dropColumn('{{%waybill}}', 'is_duedate');
        $this->dropColumn('{{%waybill}}', 'doc_date');
        $this->dropColumn('{{%waybill}}', 'edi_invoice');
        $this->dropColumn('{{%waybill}}', 'edi_recadv');
        $this->dropColumn('{{%waybill}}', 'edi_number');
    }

}
