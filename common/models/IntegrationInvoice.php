<?php

namespace common\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "integration_invoice".
 *
 * @property int $id
 * @property int $organization_id
 * @property int $integration_setting_from_email_id
 * @property string $number
 * @property string $date
 * @property string $email_id
 * @property int $order_id
 * @property string $file_mime_type
 * @property string $file_content
 * @property string $file_hash_summ
 * @property string $created_at
 * @property string $updated_at
 *
 * @property IntegrationInvoiceContent[] $Content
 * @property Organization $organization
 * @property IntegrationSettingFromEmail $integrationSettingFromEmail
 */
class IntegrationInvoice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'integration_invoice';
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
            [['organization_id', 'integration_setting_from_email_id'], 'required'],
            [['organization_id', 'integration_setting_from_email_id', 'order_id'], 'integer'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['file_content'], 'string'],
            [['number', 'email_id', 'file_mime_type', 'file_hash_summ'], 'string', 'max' => 255],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationInvoiceContent::className(), 'targetAttribute' => ['id' => 'invoice_id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['integration_setting_from_email_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationSettingFromEmail::className(), 'targetAttribute' => ['integration_setting_from_email_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Получатель накладной',
            'integration_setting_from_email_id' => 'Настройка',
            'number' => 'Номер накладной',
            'date' => 'Дата',
            'email_id' => 'Email ID',
            'order_id' => 'Связь с заказом',
            'file_mime_type' => 'MimeType',
            'file_content' => 'File Content',
            'file_hash_summ' => 'File Hash Summ',
            'created_at' => 'Дата получения',
            'count' => 'Кол-во позиций',
            'total' => 'Итоговая сумма',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasMany(IntegrationInvoiceContent::className(), ['invoice_id' => 'id']);
    }

    public function getTotalSumm() {
        $total = 0;
        if($this->content){
            foreach($this->content as $row) {
                $total += $row->price_nds;
            }
        }
        return round($total, 2);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderRelation()
    {
        return $this->hasOne(Order::className(), ['invoice_relation' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntegrationSettingFromEmail()
    {
        return $this->hasOne(IntegrationSettingFromEmail::className(), ['id' => 'integration_setting_from_email_id']);
    }

    /**
     * @param array $invoice
     * @return int
     * @throws Exception
     */
    public function saveInvoice(array $invoice)
    {
        $this->integration_setting_from_email_id = $invoice['integration_setting_from_email_id'];
        $this->organization_id = $invoice['organization_id'];
        $this->email_id = $invoice['email_id'];
        $this->file_mime_type = $invoice['file_mime_type'];
        $this->file_content = $invoice['file_content'];
        $this->file_hash_summ = $invoice['file_hash_summ'];
        $this->number = $invoice['invoice']['number'];
        $this->date = (!empty($invoice['invoice']['date']) ? date('Y-m-d', strtotime($invoice['invoice']['date'])) : null);

        if($this->date == '1970-01-01') {
            $this->date = null;
        }

        if (!$this->save()) {
            throw new Exception(implode(' ', $this->getFirstErrors()));
        }

        if (!empty($invoice['invoice']['rows'])) {
            foreach ($invoice['invoice']['rows'] as $row) {
                $content = new IntegrationInvoiceContent([
                    'invoice_id' => $this->id,
                    'row_number' => $row['num'],
                    'article' => $row['code'],
                    'title' => $row['name'],
                    'ed' => $row['ed'],
                    'percent_nds' => ceil($row['tax_rate']),
                    'price_nds' => round($row['price_with_tax'], 2),
                    'price_without_nds' => round($row['price_without_tax'], 2),
                    'quantity' => ceil($row['cnt'])
                ]);
                if (!$content->save()) {
                    throw new Exception(implode(' ', $content->getFirstErrors()));
                }
            }
        } else {
            throw new Exception('Error: empty rows');
        }

        return $this->id;
    }

    /**
     * @param Organization $vendor
     * @return array
     * @throws Exception
     */
    public function getBaseGoods(Organization $vendor)
    {
        $models = [];
        /**
         * @var $row IntegrationInvoiceContent
         */
        //Ищем товары у поставщика по наименованию
        //Если не нашли, создаем
        foreach ($this->content as $row) {
            $model = CatalogBaseGoods::find()->where(['supp_org_id' => $vendor->id])
                ->andWhere('product LIKE :product', [':product' => $row->title])
                ->one();

            if (empty($model)) {
                $model = new CatalogBaseGoods();
                $model->cat_id = $vendor->baseCatalog->id;
                $model->article = $row->article;
                $model->product = $row->title;
                $model->supp_org_id = $vendor->id;
                $model->ed = $row->ed;
                $model->units = 1;
                $model->price = $row->price_without_nds;
                if ($model->validate()) {
                    $model->save();
                } else {
                    throw new \yii\db\Exception(print_r($model->getFirstErrors(), 1));
                }
            }
            $models[] = [
                'id' => $model->id,
                'quantity' => $row->quantity,
                'price' => $row->price_without_nds,
                'units' => 1,
                'product_name' => $model->product,
                'article' => $model->article
            ];
        }

        return $models;
    }
}
