<?php

namespace api\common\models\merc;

use api\common\models\RabbitQueues;
use console\modules\daemons\components\UpdateDictInterface;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\ikar\ListOptions;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use Yii;

/**
 * This is the model class for table "merc_vsd".
 *
 * @property int $id
 * @property string $uuid
 * @property string $number
 * @property string $date_doc
 * @property string $type
 * @property string $form
 * @property string $status
 * @property string $recipient_name
 * @property string $recipient_guid
 * @property string $sender_guid
 * @property string $sender_name
 * @property int $finalized
 * @property string $last_update_date
 * @property string $vehicle_number
 * @property string $trailer_number
 * @property string $container_number
 * @property string $transport_storage_type
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
 * @property string $raw_data
 */
class MercVsd extends \yii\db\ActiveRecord implements UpdateDictInterface
{
    const DOC_TYPE_INCOMMING = 'INCOMING';
    const DOC_TYPE_OUTGOING = 'OUTGOING';
    const DOC_TYPE_PRODUCTIVE = 'PRODUCTIVE';
    const DOC_TYPE_RETURNABLE = 'RETURNABLE';
    const DOC_TYPE_TRANSPORT = 'TRANSPORT';

    public static $types = [
        self::DOC_TYPE_INCOMMING => 'Входящий ВСД',
        self::DOC_TYPE_OUTGOING => 'Исходящий ВСД',
        self::DOC_TYPE_PRODUCTIVE => 'Производственный ВСД',
        self::DOC_TYPE_RETURNABLE => 'Возвратный ВСД',
        self::DOC_TYPE_TRANSPORT => 'Транспортный ВСД',
    ];

    const DOC_STATUS_CONFIRMED = 'CONFIRMED';
    const DOC_STATUS_WITHDRAWN = 'WITHDRAWN';
    const DOC_STATUS_UTILIZED = 'UTILIZED';

    public static $statuses = [
        self::DOC_STATUS_CONFIRMED => 'Оформлен',
        self::DOC_STATUS_WITHDRAWN => 'Аннулирован',
        self::DOC_STATUS_UTILIZED => 'Погашен',
    ];

    public static $status_color = [
        self::DOC_STATUS_CONFIRMED => '',
        self::DOC_STATUS_WITHDRAWN => 'cancelled',
        self::DOC_STATUS_UTILIZED => 'done',
    ];

    public static $forms = [
        'CERTCU1' => 'Форма 1 ветеринарного сертификата ТС',
        'LIC1' => 'Форма 1 ветеринарного свидетельства',
        'CERTCU2' => 'Форма 2 ветеринарного сертификата ТС',
        'LIC2' => 'Форма 2 ветеринарного свидетельства',
        'CERTCU3' => 'Форма 3 ветеринарного сертификата ТС',
        'LIC3' => 'Форма 3 ветеринарного свидетельства',
        'NOTE4' => 'Форма 4 ветеринарной справки',
        'CERT5I' => 'Форма 5i ветеринарного сертификата',
        'CERT61' => 'Форма 6.1 ветеринарного сертификата',
        'CERT62' => 'Форма 6.2 ветеринарного сертификата',
        'CERT63' => 'Форма 6.3 ветеринарного сертификата',
        'PRODUCTIVE' => 'Производственный сертификат',
    ];

    public static $transport_types = [
        1 => 'Автомобильный',
        2 => 'Железнодорожный',
        3 => 'Авиатранспортный',
        4 => 'Морской (контейнер)',
        5 => 'Морской (трюм)',
        6 => 'Речной',
        7 => 'Перегон',
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

    public static $storage_types = [
        'FROZEN' => 'Замороженный',
        'CHILLED' => 'Охлажденный',
        'COOLED' => 'Охлаждаемый',
        'VENTILATED' => 'Вентилируемый'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'merc_vsd';
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
            [['date_doc', 'last_update_date', 'raw_data'], 'safe'],
            [['finalized', 'product_type', 'perishable',  'low_grade_cargo'], 'integer'],
            [['amount'], 'number'],
            [['uuid', 'number', 'type', 'status', 'recipient_name', 'recipient_guid', 'sender_guid', 'sender_name', 'product_name', 'unit', 'production_date', 'expiry_date', 'producer_name', 'producer_guid'], 'string', 'max' => 255],
            [['form', 'vehicle_number', 'trailer_number', 'container_number', 'transport_storage_type', 'gtin', 'article', 'batch_id'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'number' => Yii::t('message', 'frontend.client.integration.number_vsd', ['ru' => 'Номер ВСД']),
            'date_doc' => Yii::t('message', 'frontend.client.integration.date_doc', ['ru' => 'Дата оформления']),
            'status' => Yii::t('message', 'frontend.views.order.status', ['ru' => 'Статус']),
            'product_name' => Yii::t('message', 'frontend.client.integration.product_name', ['ru' => 'Наименование продукции']),
            'amount' => Yii::t('message', 'frontend.client.integration.volume', ['ru' => 'Объем']),
            'unit' => 'Unit',
            'production_date' => Yii::t('message', 'frontend.client.integration.created_at', ['ru' => 'Дата изготовления']),
            'sender_name' => Yii::t('message', 'frontend.client.integration.recipient', ['ru' => 'Фирма-отравитель']),
            'type' => 'Type',
            'form' => 'Form',
            'recipient_name' => 'Recipient Name',
            'recipient_guid' => 'Recipient Guid',
            'sender_guid' => 'Sender Guid',
            'finalized' => 'Finalized',
            'last_update_date' => 'Last Update Date',
            'vehicle_number' => 'Vehicle Number',
            'trailer_number' => 'Trailer Number',
            'container_number' => 'Container Number',
            'transport_storage_type' => 'Transport Storage Type',
            'product_type' => 'Product Type',
            'gtin' => 'Gtin',
            'article' => 'Article',
            'expiry_date' => 'Expiry Date',
            'batch_id' => 'Batch ID',
            'perishable' => 'Perishable',
            'producer_name' => 'Producer Name',
            'producer_guid' => 'Producer Guid',
            'low_grade_cargo' => 'Low Grade Cargo',
        ];
    }

    public static function getType($uuid)
    {
        $guid = mercDicconst::getSetting('enterprise_guid');

        $vsd = self::findOne(['uuid' => $uuid]);

        return ($guid == $vsd->sender_guid) ? 2 : 1;
    }

    public static function getNumber($series, $number)
    {
        if (empty($number) && empty($series))
            return null;

        $res = '';
        if (isset($series))
            $res = $series . ' ';

        if (isset($number))
            $res .= $number;

        return $res;
    }

    public static function getDate($date_raw)
    {
        if (!isset($date_raw))
            return null;

        if (isset($date_raw->informalDate))
            return $date_raw->informalDate;

        $first_date = $date_raw->firstDate->year . '-' . $date_raw->firstDate->month;

        if (isset($date_raw->firstDate->day))
            $first_date .= '-' . $date_raw->firstDate->day;

        if (isset($date_raw->firstDate->hour))
            $first_date .= " ".$date_raw->firstDate->hour . ":00:00";

        if ($date_raw->secondDate) {
            $second_date = $date_raw->secondDate->year . '-' . $date_raw->secondDate->month;

            if (isset($date_raw->secondDate->day))
                $second_date .= '-' . $date_raw->secondDate->day;

            if (isset($date_raw->secondDate->hour))
                $second_date .= " ".$date_raw->secondDate->hour . ":00:00";
            return 'с ' . $first_date . ' до ' . $second_date;
        }

        return $first_date;
    }

    public static function getProduccerData($producer, $org_id)
    {
        if(!is_array($producer))
            $data[] = $producer;
        else
            $data = $producer;

        $result = null;
        foreach ($data as $item)
        {
            $res = isset($item->enterprise->uuid) ? cerberApi::getInstance($org_id)->getEnterpriseByUuid($item->enterprise->uuid) : null;

            $result['name'][] = isset($res) ? ($res->enterprise->name.'('. $res->enterprise->address->addressView .')') : null;
            $result['guid'][] = $item->enterprise->guid;

        }

        return $result;
    }

    /**
     * Запрос обновлений справочника
     */
    public static function getUpdateData($org_id, $enterpriseGuid = null, $day_update = false)
    {
        try {
            $enterpriseGuid = $enterpriseGuid ?? mercDicconst::getSetting('enterprise_guid', $org_id);
            //Проверяем наличие записи для очереди в таблице консюмеров abaddon и создаем новую при необходимогсти
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercVSDList', 'organization_id' => $org_id, 'sore_id' => $enterpriseGuid])->one();
            if($queue == null) {
                $queue = new RabbitQueues();
                $queue->consumer_class_name = 'MercVSDList';
                $queue->organization_id = $org_id;
                $queue->store_id = $enterpriseGuid;
            }

            if (!empty($queue->organization_id)) {
                $queueName = $queue->consumer_class_name . '_' . $queue->organization_id;
            }
            else {
                $queueName = $queue->consumer_class_name;
            }

            if(!isset($queue->data_request) || $day_update) {
                if ($day_update) {
                    $data['startDate'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));
                } else {
                    $queueDate = $queue->last_executed ?? $queue->start_executing;
                    $data['startDate'] = !isset($queueDate) ? date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)) : $queueDate;
                }
                $data['listOptions']['count'] = 100;
                $data['listOptions']['offset'] = 0;
                $data['enterpriseGuid'] = $enterpriseGuid ?? mercDicconst::getSetting('enterprise_guid', $org_id);
                $queue->data_request = json_encode($data);
            }

            $queue->save();
            //ставим задачу в очередь
            \Yii::$app->get('rabbit')
                ->setQueue($queueName)
                ->addRabbitQueue('');

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            var_dump($e->getMessage());
        }
    }
}
