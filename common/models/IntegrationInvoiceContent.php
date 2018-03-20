<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "integration_invoice_content".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $row_number
 * @property string $article
 * @property string $title
 * @property string $ed
 * @property int $percent_nds
 * @property float $price_nds
 * @property float $price_without_nds
 * @property int $quantity
 * @property string $created_at
 * @property string $updated_at
 *
 * @property IntegrationInvoice $invoice
 */
class IntegrationInvoiceContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'integration_invoice_content';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['invoice_id'], 'required'],
            [['invoice_id', 'row_number', 'percent_nds', 'quantity'], 'integer'],
            [['price_nds', 'price_without_nds'], 'double'],
            [['created_at', 'updated_at'], 'safe'],
            [['article', 'title', 'ed'], 'string', 'max' => 255],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationInvoice::className(), 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_id' => 'Invoice ID',
            'row_number' => 'Row Number',
            'article' => 'Артикул',
            'title' => 'Наименование',
            'ed' => 'Ед. измерения',
            'percent_nds' => 'НДС',
            'price_nds' => 'Цена',
            'price_without_nds' => 'Цена без НДС',
            'totalPrice' => 'Цена без НДС',
            'quantity' => 'Кол-во',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getTotalPrice() {
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
