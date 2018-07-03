<?php

namespace api\common\models\merc;

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
 * @property string $low_grade_cargo
 * @property string $raw_data
 */
class MercVsd extends \yii\db\ActiveRecord
{
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
            [['finalized', 'product_type', 'perishable'], 'integer'],
            [['amount'], 'number'],
            [['uuid', 'number', 'type', 'status', 'recipient_name', 'recipient_guid', 'sender_guid', 'sender_name', 'product_name', 'unit', 'production_date', 'expiry_date', 'producer_name', 'producer_guid', 'low_grade_cargo'], 'string', 'max' => 255],
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
            'type' => Yii::t('messages', 'Type'),
            'form' => Yii::t('messages', 'Form'),
            'recipient_name' => Yii::t('messages', 'Recipient Name'),
            'recipient_guid' => Yii::t('messages', 'Recipient Guid'),
            'sender_guid' => Yii::t('messages', 'Sender Guid'),
            'finalized' => Yii::t('messages', 'Finalized'),
            'last_update_date' => Yii::t('messages', 'Last Update Date'),
            'vehicle_number' => Yii::t('messages', 'Vehicle Number'),
            'trailer_number' => Yii::t('messages', 'Trailer Number'),
            'container_number' => Yii::t('messages', 'Container Number'),
            'transport_storage_type' => Yii::t('messages', 'Transport Storage Type'),
            'product_type' => Yii::t('messages', 'Product Type'),
            'gtin' => Yii::t('messages', 'Gtin'),
            'article' => Yii::t('messages', 'Article'),
            'expiry_date' => Yii::t('messages', 'Expiry Date'),
            'batch_id' => Yii::t('messages', 'Batch ID'),
            'perishable' => Yii::t('messages', 'Perishable'),
            'producer_name' => Yii::t('messages', 'Producer Name'),
            'producer_guid' => Yii::t('messages', 'Producer Guid'),
            'low_grade_cargo' => Yii::t('messages', 'Low Grade Cargo'),
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
        if (isset($date_raw))
            return null;

        if (isset($date_raw->informalDate))
            return $date_raw->informalDate;

        $first_date = $date_raw->firstDate->year . '-' . $date_raw->firstDate->month;

        if (isset($date_raw->firstDate->day))
            $first_date .= '-' . $date_raw->firstDate->day;

        if (isset($date_raw->firstDate->hour))
            $first_date .= $date_raw->firstDate->hour . ":00:00";

        if ($date_raw->secondDate) {
            $second_date = $date_raw->secondDate->year . '-' . $date_raw->secondDate->month;

            if (isset($date_raw->secondDate->day))
                $second_date .= '-' . $date_raw->secondDate->day;

            if (isset($date_raw->secondDate->hour))
                $second_date .= $date_raw->secondDate->hour . ":00:00";
            return 'с ' . $first_date . ' до ' . $second_date;
        }

        return $first_date;
    }
}
