<?php

namespace api\common\models\merc;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury;
use Yii;

/**
 * This is the model class for table "merc_stock_entry".
 *
 * @property int $id
 * @property string $guid
 * @property string $uuid
 * @property string $owner_guid
 * @property int $active
 * @property int $last
 * @property int $status
 * @property string $create_date
 * @property string $update_date
 * @property string $previous
 * @property string $next
 * @property string $entryNumber
 * @property int $product_type
 * @property string $product_name
 * @property string $amount
 * @property string $unit
 * @property string $gtin
 * @property string $article
 * @property string $production_date
 * @property string $expiry_date
 * @property string $batch_id
 * @property int $perishable
 * @property string $producer_name
 * @property string $producer_guid
 * @property int $low_grade_cargo
 * @property string $vsd_uuid
 * @property string $raw_data
 * @property string $product_marks
 * @property string $producer_country
 */
class MercStockEntry extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    const CREATED = 100;
    const CREATED_WHEN_QUENCH_VETCERTIFICATE = 101;
    const CREATED_WHEN_QUENCH_VETDOCUMENT = 102;
    const CREATED_BY_OPERATION = 103;
    const CREATED_WHEN_MERGE = 110;
    const CREATED_WHEN_SPLIT = 120;
    const UPDATED = 200;
    const WITHDRAWN = 201;
    const UPDATED_WHEN_WRITINGOFF = 202;
    const UPDATED_WHEN_ATTACH = 230;
    const UPDATED_WHEN_ATTACH_AUTOMATIC = 231;
    const UPDATED_WHEN_FORK = 240;
    const RESTORED_AFTER_DELETE = 250;
    const MOVED = 300;
    const DELETED = 400;
    const DELETED_WHEN_MERGE = 410;
    const DELETED_WHEN_SPLIT = 420;
    const DELETED_WHEN_ATTACH = 430;


    public static $statuses = [
        self::CREATED => 'Запись создана',
        self::CREATED_WHEN_QUENCH_VETCERTIFICATE => 'Запись создана путем гашения ВС (импорт)',
        self::CREATED_WHEN_QUENCH_VETDOCUMENT => 'Запись создана путем гашения ВСД',
        self::CREATED_BY_OPERATION => 'Запись создана в результате производственной операции',
        self::CREATED_WHEN_MERGE => 'Запись создана в результате объединения двух или более других',
        self::CREATED_WHEN_SPLIT => 'Запись создана в результате разделения другой',
        self::UPDATED => 'В запись были внесены изменения',
        self::WITHDRAWN => 'Запись журнала аннулирована',
        self::UPDATED_WHEN_WRITINGOFF => 'Запись продукции изменена путём списания.',
        self::UPDATED_WHEN_ATTACH => 'Запись была обновлена в результате присоединения другой',
        self::UPDATED_WHEN_ATTACH_AUTOMATIC => 'Запись была обновлена в результате присоединения другой',
        self::UPDATED_WHEN_FORK => 'Запись была обновлена в результате отделения от неё другой',
        self::RESTORED_AFTER_DELETE => 'Запись была восстановлена после удаления',
        self::MOVED => 'Запись была перемещена в другую группу',
        self::DELETED => 'Запись была удалена',
        self::DELETED_WHEN_MERGE => 'Запись была удалена в результате объединения',
        self::DELETED_WHEN_SPLIT => 'Запись была удалена в результате разделения',
        self::DELETED_WHEN_ATTACH => 'Запись была удалена в результате присоединения'
    ];

    public static $product_types = [
        1 => 'Мясо и мясопродукты',
        2 => 'Корма и кормовые добавки',
        3 => 'Живые животные',
        4 => 'Лекарственные средства',
        5 => 'Пищевые продукты',
        6 => 'Непищевые продукты и другое',
        7 => 'Рыба и морепродукты',
        8 => 'Продукция, не требующая разрешения',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'merc_stock_entry';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'last', 'status', 'product_type', 'perishable', 'low_grade_cargo'], 'integer'],
            [['create_date', 'update_date'], 'safe'],
            [['amount'], 'number'],
            [['raw_data'], 'required'],
            [['raw_data'], 'string'],
            [['guid', 'uuid', 'owner_guid', 'previous', 'next', 'entryNumber', 'product_name', 'article', 'production_date', 'expiry_date', 'batch_id', 'producer_name', 'producer_guid', 'vsd_uuid', 'product_marks', 'producer_country'], 'string', 'max' => 255],
            [['unit', 'gtin'], 'string', 'max' => 50],
            [['guid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'guid' => 'Guid',
            'uuid' => 'Uuid',
            'owner_guid' => 'Owner Guid',
            'active' => 'Active',
            'last' => 'Last',
            'status' => 'Status',
            'create_date' => 'Create Date',
            'update_date' => 'Update Date',
            'previous' => 'Previous',
            'next' => 'Next',
            'entryNumber' => 'Entry Number',
            'product_type' => 'Product Type',
            'product_name' => 'Product Name',
            'amount' => 'Amount',
            'unit' => 'Unit',
            'gtin' => 'Gtin',
            'article' => 'Article',
            'production_date' => 'Production Date',
            'expiry_date' => 'Expiry Date',
            'batch_id' => 'Batch ID',
            'perishable' => 'Perishable',
            'producer_name' => 'Producer Name',
            'producer_guid' => 'Producer Guid',
            'low_grade_cargo' => 'Low Grade Cargo',
            'vsd_uuid' => 'Vsd Uuid',
            'raw_data' => 'Raw Data',
            'producer_country' => 'producer country',
            'product_marks' => 'product marks',
        ];
    }

    public static function getDate($date_raw)
    {
        if (!isset($date_raw)) {
            return null;
        }

        if (isset($date_raw->informalDate)) {
            return $date_raw->informalDate;
        }

        $first_date = $date_raw->firstDate->year . '-' . (($date_raw->firstDate->month < 10) ? "0" : "") . $date_raw->firstDate->month;

        if (isset($date_raw->firstDate->day)) {
            $first_date .= '-' . $date_raw->firstDate->day;
        }

        if (!empty($date_raw->firstDate->hour)) {
            $first_date .= " " . $date_raw->firstDate->hour . ":00:00";
        }

        if (!empty($date_raw->firstDate->minute)) {
            $first_date .= " " . $date_raw->firstDate->hour . ":00:00";
        }

        if ($date_raw->secondDate) {
            $second_date = $date_raw->secondDate->year . '-' . $date_raw->secondDate->month;

            if (isset($date_raw->secondDate->day)) {
                $second_date .= '-' . $date_raw->secondDate->day;
            }

            if (!empty($date_raw->secondDate->hour)) {
                $second_date .= " " . $date_raw->secondDate->hour . ":00:00";
            }

            if (!empty($date_raw->secondDate->minute)) {
                $second_date .= " " . $date_raw->secondDate->minute . ":00:00";
            }
            return 'с ' . $first_date . ' до ' . $second_date;
        }

        return $first_date;
    }

    /**
     * Запрос обновлений справочника
     */
    public static function getUpdateData($org_id, $enterpriseGuid = null, $start_date = null)
    {
        try {
            $enterpriseGuid = $enterpriseGuid ?? mercDicconst::getSetting('enterprise_guid', $org_id);
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercStockEntryList', 'organization_id' => $org_id, 'store_id' => $enterpriseGuid])->one();
            if ($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercStockEntryList';
                $queue->organization_id = $org_id;
                $queue->store_id = $enterpriseGuid;
                $queue->save();
            }

            if (!empty($queue->organization_id)) {
                $queueName = $queue->consumer_class_name . '_' . $queue->organization_id;
            } else {
                $queueName = $queue->consumer_class_name;
            }

            $data['startDate'] = $start_date ?? gmdate("Y-m-d H:i:s", time() - 60*60*24);
            $data['listOptions']['count'] = 100;
            $data['listOptions']['offset'] = 0;
            $data['enterpriseGuid'] = $enterpriseGuid;

            if(isset($start_date)) {
                $queue->data_request = json_encode($data);
            }

            //ставим задачу в очередь
            \Yii::$app->get('rabbit')
                ->setQueue($queueName)
                ->addRabbitQueue(json_encode($data));

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            var_dump($e->getMessage());
        }
    }
}
