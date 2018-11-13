<?php

use yii\db\Migration;

/**
 * Class m181015_094431_add_columns_in_merc_vsd
 */
class m181015_094431_add_columns_in_merc_vsd extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addCommentOnColumn('{{%merc_vsd}}', 'uuid', 'Идентификатор ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'number', 'Номер ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'date_doc', 'Дата оформления ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'type', 'Тип ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'form', 'Форма ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'status', 'Статус ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'recipient_name', 'Отправитель включая данные о ХС');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'recipient_guid', 'Глобальный идентификатор предприятия');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'sender_name', 'Статус ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'sender_guid', 'Глобальный идентификатор предприятия');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'finalized', 'Флаг того, что сертификат закрыт');

        $this->addCommentOnColumn('{{%merc_vsd}}', 'last_update_date', 'Дата и время последнего изменения статуса ВСД');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'vehicle_number', 'Номер автомобиля Deprecated');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'trailer_number', 'Номер прицепа (полуприцепа) Deprecated');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'container_number', 'Номер контейнера (при автомобильной перевозке) Deprecated');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'transport_storage_type', 'Способ хранения продукции при перевозке');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'product_type', 'Тип продукции. Первый уровень иерархического справочника продукции ИС Меркурий');

        $this->addCommentOnColumn('{{%merc_vsd}}', 'product_name', 'Наименование продукции');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'amount', 'Флаг того, что сертификат закрыт');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'unit', 'Флаг того, что сертификат закрыт');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'gtin', 'Trade Identification Number (GTIN) - идентификационный номер продукции производителя');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'article', 'Артикул (код) продукции в соответствии с внутренним кодификатором производителя');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'production_date', 'Дата выработки продукции');

        $this->addCommentOnColumn('{{%merc_vsd}}', 'expiry_date', 'Дата окончания срока годности продукции (кроме типа продукции "Живые животные")');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'batch_id', 'Уникальный идентификатор производственной партии продукции');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'perishable', 'Описывает, является ли продукция скоропортящейся');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'producer_name', 'Наименование производителя');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'producer_guid', 'Глобальный идентификатор предприятия');
        $this->addCommentOnColumn('{{%merc_vsd}}', 'low_grade_cargo', 'Флаг: является ли груз некачественным');

        $this->addColumn('{{%merc_vsd}}', 'owner_guid', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'owner_guid', 'Глобальный идентификатор хозяйствующего субъекта-владельца продукции');
        $this->addColumn('{{%merc_vsd}}', 'product_guid', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'product_guid', 'Глобальный идентификатор продукции');
        $this->addColumn('{{%merc_vsd}}', 'sub_product_guid', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'sub_product_guid', 'Глобальный идентификатор вида продукции');
        $this->addColumn('{{%merc_vsd}}', 'product_item_guid', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'product_item_guid', 'Глобальный идентификатор номенклатуры');
        $this->addColumn('{{%merc_vsd}}', 'origin_country_guid', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'origin_country_guid', 'Глобальный идентификатор страны происхождения');
        $this->addColumn('{{%merc_vsd}}', 'waybill_number', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'waybill_number', 'Номер накладной');
        $this->addColumn('{{%merc_vsd}}', 'waybill_date', $this->dateTime());
        $this->addCommentOnColumn('{{%merc_vsd}}', 'waybill_date', 'Дата накладной');

        $this->addColumn('{{%merc_vsd}}', 'transport_info', $this->text());
        $this->addCommentOnColumn('{{%merc_vsd}}', 'transport_info', 'Информация о транспорте (JSON)');

        $this->addColumn('{{%merc_vsd}}', 'confirmed_by', $this->text());
        $this->addCommentOnColumn('{{%merc_vsd}}', 'confirmed_by', 'Кто выписал ВСД (JSON)');
        $this->addColumn('{{%merc_vsd}}', 'other_info', $this->text());
        $this->addCommentOnColumn('{{%merc_vsd}}', 'other_info', 'Прочая информация  (JSON)');

        $this->addColumn('{{%merc_vsd}}', 'laboratory_research', $this->text());
        $this->addCommentOnColumn('{{%merc_vsd}}', 'laboratory_research', 'Сведения о проведенных лабораторных исследованиях (JSON)');
        $this->addColumn('{{%merc_vsd}}', 'unit_guid', $this->string(255));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'unit_guid', 'Глобальный идентификатор Ед. Измерения');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181014_102312_add_service_id_column_rename_name_column_for_integration_setting cannot be reverted.\n";
        return false;
    }
}
