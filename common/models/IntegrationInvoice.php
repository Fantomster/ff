<?php

namespace common\models;

use Yii;
use yii\db\Exception;
use yii\helpers\HtmlPurifier;

/**
 * This is the model class for table "integration_invoice".
 *
 * @property int                         $id                                Идентификатор записи в таблице
 * @property int                         $organization_id                   Идентификатор организации - получателя
 *           накладной
 * @property int                         $integration_setting_from_email_id Идентификатор электронного ящика,
 *           настроенного в интеграциях
 * @property string                      $number                            Номер накладной, взятый из накладной
 * @property string                      $date                              Дата поставки, взятая из накладной
 * @property string                      $email_id                          Идентификатор почтового ящика, откуда был
 *           загружен файл с накладной
 * @property int                         $order_id                          Идентификатор заказа, созданного по этой
 *           накладной
 * @property string                      $file_mime_type                    МИМЕ-тип вложенного файла накладной
 * @property string                      $file_content                      Хэш-контент файла накладной
 * @property string                      $file_hash_summ                    Хэш-сумма файла накладной
 * @property string                      $created_at                        Дата и время создания записи в таблице
 * @property string                      $updated_at                        Дата и время последнего изменения записи в
 *           таблице
 * @property string                      $total_sum_withtax                 Стоимость всех товаров по накладной с НДС
 * @property string                      $total_sum_withouttax              Стоимость всех товаров по накладной без НДС
 * @property string                      $name_postav                       Наименование поставщика, взятое из
 *           накладной
 * @property string                      $inn_postav                        ИНН поставщика, взятый из накладной
 * @property string                      $kpp_postav                        КПП поставщика, взятый из накладной
 * @property string                      $consignee                         Наименование грузополучателя, взятое из
 *           накладной ТОРГ-12
 * @property int                         $vendor_id                         Идентификатор организации-поставщика
 *
 * @property Organization                $organization
 * @property IntegrationInvoiceContent[] $content
 * @property Order                       $order
 * @property Organization                $vendor
 * @property Order                       $orderRelation
 * @property IntegrationSettingFromEmail $integrationSettingFromEmail
 */
class IntegrationInvoice extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%integration_invoice}}';
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
            [['organization_id', 'integration_setting_from_email_id'], 'required'],
            [['organization_id', 'integration_setting_from_email_id', 'order_id', 'vendor_id'], 'integer'],
            [['date', 'created_at', 'updated_at', 'total_sum_withtax', 'price_without_tax_sum'], 'safe'],
            [['file_content'], 'string'],
            [['number', 'email_id', 'file_mime_type', 'file_hash_summ', 'name_postav', 'inn_postav', 'kpp_postav', 'consignee'], 'string', 'max' => 255],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationInvoiceContent::className(), 'targetAttribute' => ['id' => 'invoice_id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['integration_setting_from_email_id'], 'exist', 'skipOnError' => true, 'targetClass' => IntegrationSettingFromEmail::className(), 'targetAttribute' => ['integration_setting_from_email_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                                => 'ID',
            'organization_id'                   => 'Получатель накладной',
            'integration_setting_from_email_id' => 'Настройка',
            'number'                            => 'Номер накладной',
            'date'                              => 'Дата',
            'email_id'                          => 'Email ID',
            'order_id'                          => 'Связь с заказом',
            'file_mime_type'                    => 'MimeType',
            'file_content'                      => 'File Content',
            'file_hash_summ'                    => 'File Hash Summ',
            'created_at'                        => 'Дата получения',
            'count'                             => 'Кол-во позиций',
            'total'                             => 'Итоговая сумма',
            'updated_at'                        => 'Updated At',
            'total_sum_withtax'                 => 'Итого с НДС',
            'price_without_tax_sum'             => 'Итого без НДС',
            'name_postav'                       => 'Наименование поставщика',
            'inn_postav'                        => 'ИНН поставщика',
            'kpp_postav'                        => 'КПП поставщика',
            'consignee'                         => 'Грузополучатель',
            'vendor_id'                         => 'Идентификатор поставщика'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasMany(IntegrationInvoiceContent::className(), ['invoice_id' => 'id']);
    }

    /**
     * @return float
     */
    public function getTotalSumm()
    {
        $total = 0;
        if ($this->content) {
            foreach ($this->content as $row) {
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
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
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
        $this->total_sum_withtax = $invoice['invoice']['price_with_tax_sum'];
        $this->total_sum_withouttax = $invoice['invoice']['price_without_tax_sum'];
        $this->name_postav = $invoice['invoice']['namePostav'];
        $this->inn_postav = $invoice['invoice']['innPostav'];
        $this->kpp_postav = $invoice['invoice']['kppPostav'];
        $this->consignee = $invoice['invoice']['nameConsignee'];

        if ($this->date == '1970-01-01') {
            $this->date = null;
        }

        if (!$this->save()) {
            throw new Exception(implode(' ', $this->getFirstErrors()));
        }

        if (!empty($invoice['invoice']['rows'])) {
            foreach ($invoice['invoice']['rows'] as $row) {
                $content = new IntegrationInvoiceContent([
                    'invoice_id'        => $this->id,
                    'row_number'        => $row['num'],
                    'article'           => $row['code'],
                    'title'             => $row['name'],
                    'ed'                => $row['ed'],
                    'percent_nds'       => ceil($row['tax_rate']),
                    'price_nds'         => round($row['sum_with_tax'], 2),
                    'price_without_nds' => round($row['price_without_tax'], 2),
                    'quantity'          => $row['cnt'],
                    'sum_without_nds'   => $row['sum_without_tax'],
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
        //Если не нашли, создаём
        foreach ($this->content as $row) {
            $model = CatalogBaseGoods::find()->where(['supp_org_id' => $vendor->id])
                ->andWhere(['product' => HtmlPurifier::process($row->title)])
                ->one();

            if (empty($model)) {
                $model = new CatalogBaseGoods();
                $model->cat_id = $vendor->baseCatalog->id;
                $model->article = $row->article;
                $model->product = $row->title;
                $model->supp_org_id = $vendor->id;
                $model->ed = $row->ed;
                $model->status = 0;
                $model->units = 1;
                $model->price = round($row->price_without_nds + ($row->price_without_nds * $row->percent_nds / 100), 2);
                if (!$model->save()) {
                    throw new \yii\db\Exception(print_r($model->getFirstErrors(), 1));
                }
            }
            $models[] = [
                'id'                 => $model->id,
                'quantity'           => $row->quantity,
                'price'              => $row->price_without_nds, //round($row->price_without_nds + ($row->price_without_nds * $row->percent_nds / 100), 2),
                'units'              => 1,
                'product_name'       => $model->product,
                'article'            => $model->article,
                'invoice_content_id' => $row->id,
            ];
        }

        return $models;
    }

    /**
     * Записывает товарные позиции из накладной ТОРГ-12 в таблицу catalog_goods
     *
     * @param Organization $vendor
     * @return boolean
     * @throws Exception
     */
    public function addProductsFromTorg12InCatalogGoods(Organization $vendor, Organization $client)
    {
        //Ищем товары у поставщика по наименованию в специально назначенном для ресторана каталоге
        //Если не нашли, создаём
        $catalogIds = RelationSuppRest::find()->select('cat_id')->where(['rest_org_id' => $client->id, 'supp_org_id' => $vendor->id])->asArray()->column();
        if (empty($catalogIds)) {
            return false;
        }
        foreach ($this->content as $row) {
            $model = CatalogBaseGoods::find()->where(['supp_org_id' => $vendor->id])
                ->andWhere(['like', 'product', HtmlPurifier::process($row->title), 'status' => CatalogBaseGoods::STATUS_ON])
                ->one();
            foreach ($catalogIds as $catId) {
                $model2 = CatalogGoods::find()->where(['cat_id' => $catId])
                    ->andWhere(['base_goods_id' => $model->id])
                    ->one();

                if (empty($model2)) {
                    $model2 = new CatalogGoods();
                    $model2->cat_id = $catId;
                    $model2->base_goods_id = $model->id;
                    $model2->price = $row->price_without_nds;
                    if (!$model2->save()) {
                        throw new \yii\db\Exception(print_r($model2->getFirstErrors(), 1));
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $pageSize
     * @return int
     */
    public function getOrderPage(int $pageSize = 20)
    {
        //$subQuery = (new \yii\db\Query())->select
        return Order::find()
                ->select(new \yii\db\Expression("(FLOOR(COUNT(*) / $pageSize) + 1"))
                ->where(['status' => Order::STATUS_DONE, 'client_id' => $this->organization_id])
                ->andWhere(['>=', 'id', $this->order_id])
                ->orderBy(['id' => SORT_DESC])
                ->scalar();
    }
}
