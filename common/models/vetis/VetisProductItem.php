<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\ListOptions;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\Products;
use Yii;

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
            [['last', 'active', 'status', 'productType', 'correspondsToGost'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code', 'globalID', 'product_uuid', 'product_guid', 'subproduct_uuid', 'subproduct_guid', 'gost', 'producer_uuid', 'producer_guid', 'tmOwner_uuid', 'tmOwner_guid'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'code' => 'Code',
            'globalID' => 'Global ID',
            'productType' => 'Product Type',
            'product_uuid' => 'Product Uuid',
            'product_guid' => 'Product Guid',
            'subproduct_uuid' => 'Subproduct Uuid',
            'subproduct_guid' => 'Subproduct Guid',
            'correspondsToGost' => 'Corresponds To Gost',
            'gost' => 'Gost',
            'producer_uuid' => 'Producer Uuid',
            'producer_guid' => 'Producer Guid',
            'tmOwner_uuid' => 'Tm Owner Uuid',
            'tmOwner_guid' => 'Tm Owner Guid',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getProductItem()
    {
        return \yii\helpers\Json::decode($this->data);
    }

    public static function getUpdateData($org_id)
    {
        try {
            $load = new Products();
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

            $listOptions = new ListOptions();
            $listOptions->count = 1000;
            $listOptions->offset = 0;

            $queueDate = $queue->last_executed ?? $queue->start_executing;

            $startDate =  !isset($queueDate) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queueDate;
            $instance = productApi::getInstance($org_id);
            $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

            if (!empty($queue->organization_id)) {
                $queueName = $queue->consumer_class_name . '_' . $queue->organization_id;
            }
            else {
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
}
