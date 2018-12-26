<?php

namespace common\models;

/**
 * This is the model class for table "integration_invoice_content".
 *
 * @property int                $id                Идентификатор записи в таблице
 * @property int                $invoice_id        Идентификатор накладной поставщика
 * @property int                $row_number        Номер строки в таблице накладной поставщика
 * @property string             $article           Артикул/код товара в таблице накладной поставщика
 * @property string             $title             Наименование товара в накладной поставщика
 * @property int                $percent_nds       Налоговая ставка НДС в накладной поставщика
 * @property double             $price_nds         Цена за единицу товара с НДС в накладной поставщика
 * @property double             $price_without_nds Цена за единицу товара без НДС в накладной поставщика
 * @property string             $quantity          Количество товара в накладной поставщика
 * @property string             $ed                Единица измерения товара в накладной поставщика
 * @property string             $created_at        Дата и время создания записи в таблице
 * @property string             $updated_at        Дата и время последнего изменения записи в таблице
 * @property double             $sum_without_nds   Сумма товаров без НДС в строке накладной поставщика
 *
 * @property IntegrationInvoice $invoice
 */
class IntegrationInvoiceContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integration_invoice_content}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id'], 'required'],
            [['invoice_id', 'row_number', 'percent_nds'], 'integer'],
            [['price_nds', 'price_without_nds', 'quantity', 'sum_without_nds'], 'double'],
            [['created_at', 'updated_at'], 'safe'],
            [['article', 'title', 'ed'], 'string', 'max' => 255],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationInvoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'invoice_id'        => 'Invoice ID',
            'row_number'        => 'Row Number',
            'article'           => 'Артикул',
            'title'             => 'Наименование',
            'ed'                => 'Ед. измерения',
            'percent_nds'       => 'НДС',
            'price_nds'         => 'Сумма с НДС',
            'price_without_nds' => 'Цена без НДС',
            'totalPrice'        => 'Общая сумма без НДС',
            'quantity'          => 'Кол-во',
            'created_at'        => 'Created At',
            'updated_at'        => 'Updated At',
            'sum_without_nds'   => 'Сумма без НДС',
        ];
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return round($this->price_without_nds * $this->quantity, 2);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(IntegrationInvoice::className(), ['id' => 'invoice_id']);
    }
}
