<?php

namespace common\models\vetis;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "vetis_product_by_type".
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
 * @property int $productType
 * @property string $createDate
 * @property string $updateDate
 * @property object $product
 */
class VetisProductByType extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vetis_product_by_type';
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
            /*[['active','last'], 'filter', 'filter' => function ($value) {
                $value = ($value === 'true') ? 1 : 0;
                return $value;
            }],*/
            [['last', 'active', 'status', 'productType'], 'integer'],
            [['createDate', 'updateDate'], 'safe'],
            [['uuid', 'guid', 'next', 'previous', 'name', 'code'], 'string', 'max' => 255],
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
            'productType' => 'Product Type',
            'createDate' => 'Create Date',
            'updateDate' => 'Update Date',
        ];
    }
    
    public function getProduct()
    {
        return \yii\helpers\Json::decode($this->data);
    }
    
    public static function getProductByTypeList($type)
    {
        $models = self::find()
                ->select(['guid', 'name'])
                ->where(['active' => true, 'last' => true, 'productType' => $type])
                ->asArray()
                ->all();

        return ArrayHelper::map($models, 'guid', 'name');
    }

    public static function getUpdateData($org_id)
    {
        try {
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercProductList'])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercProductList';
                $queue->save();
            }

            //Формируем данные для запроса
            $data['method'] = 'getProductChangesList';
            $data['struct'] = ['listName' => 'productList',
                'listItemName' => 'product'
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
