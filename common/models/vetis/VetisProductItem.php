<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "vetis_product_item".
 *
 * @property string $uuid
 * @property string $guid
 * @property int $last
 * @property int $active
 * @property int $status
 * @property string $next
 * @property string $previous
 * @property string $name
 * @property string $code
 * @property string $globalID
 * @property int $productType
 * @property string $product_uuid
 * @property string $product_guid
 * @property string $subproduct_uuid
 * @property string $subproduct_guid
 * @property int $correspondsToGost
 * @property string $gost
 * @property string $producer_uuid
 * @property string $producer_guid
 * @property string $tmOwner_uuid
 * @property string $tmOwner_guid
 * @property string $createDate
 * @property string $updateDate
 * @property object $productItem
 * @property string packagingType_guid
 * @property string packagingType_uuid
 * @property string unit_uuid
 * @property string unit_guid
 * @property int packagingQuantity
 * @property float packagingVolume
 */

class VetisProductItem extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_product_item';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    public static function primaryKey()
    {
        return ['uuid'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'guid'], 'required'],
            [['uuid'], 'unique'],
            /*[['active','last', 'correspondsToGost'], 'filter', 'filter' => function ($value) {
                $value = ($value === 'true') ? 1 : 0;
                return $value;
            }],*/
            [['last', 'active', 'status', 'productType', 'correspondsToGost', 'packagingQuantity'], 'integer'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code', 'globalID', 'product_uuid', 'product_guid', 'subproduct_uuid',
                'subproduct_guid', 'gost', 'producer_uuid', 'producer_guid', 'tmOwner_uuid', 'tmOwner_guid',
                'packagingType_guid', 'packagingType_uuid', 'unit_uuid', 'unit_guid'], 'string', 'max' => 255],
            [['createDate', 'updateDate', 'packagingType_guid', 'packagingType_uuid', 'unit_uuid', 'unit_guid', 'packagingQuantity', 'packagingVolume'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'Uuid',
            'guid' => 'Guid',
            'last' => 'Last',
            'active' => 'Active',
            'status' => 'Status',
            'next' => 'Next',
            'previous' => 'Previous',
            'name' => 'Наименование продукции',
            'code' => 'Артикул',
            'globalID' => 'GTIN',
            'productType' => 'Тип продукции',
            'product_uuid' => 'Продукция',
            'product_guid' => 'Продукция',
            'subproduct_uuid' => 'Вид продукции',
            'subproduct_guid' => 'Вид продукции',
            'correspondsToGost' => 'Соответствие ГОСТ',
            'gost' => 'ГОСТ',
            'producer_uuid' => 'Producer Uuid',
            'producer_guid' => 'Producer Guid',
            'tmOwner_uuid' => 'Tm Owner Uuid',
            'tmOwner_guid' => 'Tm Owner Guid',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
            'packagingType_guid' => 'Упаковка',
            'packagingType_uuid' => 'Упаковка',
            'unit_uuid' => 'Единица Измерения',
            'unit_guid' => 'Единица Измерения',
            'packagingQuantity' => 'packagingQuantity',
            'packagingVolume' => 'packagingVolume',
        ];
    }

    public function getProductItem()
    {
        return \yii\helpers\Json::decode($this->data);
    }

    public function getUnit()
    {
        if(!is_null($this->unit_uuid)) {
            return $this->hasOne(VetisUnit::className(), ['uuid' => 'unit_uuid']);
        }

        return $this->hasOne(VetisUnit::className(), ['guid' => 'unit_guid']);
    }

    public function getPackingType()
    {
        if(!is_null($this->packagingType_uuid)) {
            return $this->hasOne(VetisUnit::className(), ['uuid' => 'packagingType_uuid']);
        }

        return $this->hasOne(VetisUnit::className(), ['guid' => 'packagingType_guid']);
    }

    public function getProduct()
    {
        if(!is_null($this->product_uuid))
            return $this->hasOne(VetisProductByType::className(), ['uuid' => 'product_uuid']);

        return $this->hasOne(VetisProductByType::className(), ['guid' => 'product_guid']);
    }

    public function getSubProduct()
    {
        if(!is_null($this->subproduct_uuid)) {
            return $this->hasOne(VetisSubproductByProduct::className(), ['uuid' => 'subproduct_uuid']);
        }

        return $this->hasOne(VetisSubproductByProduct::className(), ['guid' => 'subproduct_guid']);
    }

    public static function getUpdateData($org_id)
    {
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercProductItemList'])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercProductItemList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getProductItemChangesList';
            $data['struct'] = ['listName' => 'productItemList',
                'listItemName' => 'productItem'
            ];

            $listOptions['count'] = 1000;
            $listOptions['offset'] = 0;

            $queueDate = $queue->last_executed ?? $queue->start_executing;

            $startDate =  gmdate("Y-m-d H:i:s", time() - 60*60*24*80); //!isset($queueDate) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queueDate;
            $instance = productApi::getInstance($org_id);
            $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

            if (!empty($queue->organization_id)) {
                $queueName = $queue->consumer_class_name . '_' . $queue->organization_id;
            } else {
                $queueName = $queue->consumer_class_name;
            }

            //ставим задачу в очередь
            \Yii::$app->get('rabbit')
                ->setQueue($queueName)
                ->addRabbitQueue(json_encode($data));

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
        }
    }


    public static function getProductItemList($subproduct_uuid)
    {
        $models = self::find()
            ->select(['guid', 'name'])
            ->where(['active' => true, 'last' => true, 'subproduct_uuid' => $subproduct_uuid])
            ->asArray()
            ->all();

        return ArrayHelper::map($models, 'guid', function ($model) {
            return $model['name'];
        });
    }
}
