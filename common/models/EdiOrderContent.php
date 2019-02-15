<?php

namespace common\models;

/**
 * This is the model class for table "edi_order_content".
 *
 * @property int          $id                   Идентификатор записи в таблице
 * @property int          $order_content_id     Идентификатор товарной позиции заказа
 * @property string       $edi_supplier_article Артикул товара от поставщика
 * @property int          $doc_type             Тип документа (0 - заказ, 1 - уведомление об отгрузке DESADV, 2 -
 *           уведомление об отгрузке ALCDES)
 * @property string       $barcode              Штрих-код товара
 * @property string       $pricewithvat         Цена с НДС
 * @property string       $taxrate              Ставка НДС
 * @property string       $uuid                 Универсальный идентификатор товара в системе ВЕТИС
 * @property string       $gtin                 Глобальный идентификатор товарной продукции системы ВЕТИС
 * @property string       $waybill_date         Дата получения товарной накладной по EDI
 * @property string       $waybill_number       Номер товарной накладной по EDI
 * @property string       $delivery_note_number Номер товарно-транспортной накладной по EDI
 * @property string       $delivery_note_date   Дата получения товарно-транспортной накладной по EDI
 *
 * @property OrderContent $orderContent
 */
class EdiOrderContent extends \yii\db\ActiveRecord
{
    const DESADV = 1;
    const ALCDES = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%edi_order_content}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_content_id', 'doc_type', 'barcode'], 'integer'],
            [['pricewithvat', 'taxrate'], 'number'],
            [['order_content_id'], 'unique'],
            [['edi_supplier_article'], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderConent()
    {
        return $this->hasOne(OrderContent::className(), ['id' => 'order_content_id']);
    }
}
