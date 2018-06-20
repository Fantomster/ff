<?php

use yii\db\Migration;

class m180613_075535_add_comments_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `integration_invoice` comment "Таблица общих сведений о накладной поставщика";');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'organization_id', 'Идентификатор организации - получателя накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'integration_setting_from_email_id', 'Идентификатор электронного ящика, настроенного в интеграциях');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'number', 'Номер накладной, взятый из накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'date', 'Дата поставки, взятая из накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'email_id', 'Идентификатор почтового ящика, откуда был загружен файл с накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'order_id', 'Идентификатор заказа, созданного по этой накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'file_mime_type', 'МИМЕ-тип вложенного файла накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'file_content', 'Хэш-контент файла накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'file_hash_summ', 'Хэш-сумма файла накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'total_sum_withtax', 'Стоимость всех товаров по накладной с НДС');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'total_sum_withouttax', 'Стоимость всех товаров по накладной без НДС');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'name_postav', 'Наименование поставщика, взятое из накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'inn_postav', 'ИНН поставщика, взятый из накладной');
        $this->addCommentOnColumn('{{%integration_invoice}}', 'kpp_postav', 'КПП поставщика, взятый из накладной');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_invoice` comment "";');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'id');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'organization_id');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'integration_setting_from_email_id');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'number');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'date');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'email_id');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'order_id');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'file_mime_type');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'file_content');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'file_hash_summ');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'created_at');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'updated_at');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'total_sum_withtax');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'total_sum_withouttax');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'name_postav');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'inn_postav');
        $this->dropCommentFromColumn('{{%integration_invoice}}', 'kpp_postav');
    }
}
