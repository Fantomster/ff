<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "vetis_subproduct_by_product".
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
 * @property string $productGuid
 * @property string $createDate
 * @property string $updateDate
 * @property object $subProduct
 */
class VetisSubproductByProduct extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_subproduct_by_product';
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
            [['last', 'active', 'status'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code', 'productGuid'], 'string', 'max' => 255],
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
            'productGuid' => 'Product Guid',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getSubProduct()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getSubProductByProductList($product_guid) {
        $product = VetisProductByType::findOne(['active' => true, 'last' => true, 'guid' => $product_guid]);
        $models = self::find()
                ->select(['guid', 'name', 'code'])
                ->where(['active' => true, 'last' => true, 'productGuid' => $product->guid])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'guid', function($model) {
            return $model['name'] . ' (' . $model['code'] . ')';
        });
    }

    public static function getUpdateData($org_id)
    {
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercSubProductList'])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercSubProductList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getSubProductChangesList';
            $data['struct'] = ['listName' => 'subProductList',
                'listItemName' => 'subProduct'
            ];

            $listOptions['count'] = 1000;
            $listOptions['offset'] = 0;

            $queueDate = $queue->last_executed ?? $queue->start_executing;

            $startDate =  gmdate("Y-m-d H:i:s", time() - 60*60*24*80); //!isset($queueDate) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queueDate;
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
