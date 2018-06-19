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
 * @property string $status
 * @property string $product_name
 * @property string $amount
 * @property string $unit
 * @property string $production_date
 * @property string $recipient_name
 * @property string $guid
 * @property string $type
 * @property string $consignor
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
            [['date_doc', 'production_date', 'guid'], 'safe'],
            [['amount'], 'number'],
            [['uuid', 'number', 'status', 'product_name', 'unit', 'recipient_name','type', 'consignor'], 'string', 'max' => 255],
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
            'recipient_name' => Yii::t('message', 'frontend.client.integration.recipient', ['ru' => 'Фирма-отравитель']),
        ];
    }

    public static function getType($uuid)
    {
        $guid = mercDicconst::getSetting('enterprise_guid');

        $vsd  = self::findOne(['uuid' => $uuid]);

        return ($guid == $vsd->consignor) ? 2 : 1;
    }
}
