<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2018-12-10
 * Time: 13:26
 */

namespace common\models\egais;

/**
 *
 * @property int $id [int(11)]
 * @property int $org_id [int(11)]
 * @property string $quantity [decimal(10,4)]
 * @property string $inform_a_reg_id [varchar(255)]
 * @property string $inform_b_reg_id [varchar(255)]
 * @property string $full_name [varchar(255)]
 * @property string $alc_code [varchar(255)]
 * @property string $capacity [decimal(10,4)]
 * @property string $alc_volume [decimal(10,3)]
 * @property string $producer_client_reg_id [varchar(255)]
 * @property string $producer_inn [varchar(255)]
 * @property string $producer_kpp [varchar(255)]
 * @property string $producer_full_name [varchar(255)]
 * @property string $producer_short_name [varchar(255)]
 * @property int $address_country [int(11)]
 * @property int $address_region_code [int(11)]
 * @property string $address_description
 * @property int $product_v_code [int(11)]
 */
class EgaisProductOnBalance extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'egais_product_on_balance';
    }

    /**
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org_id'], 'required'],
            [['org_id', 'product_v_code', 'address_country', 'address_region_code'], 'integer'],
            [[
                 'quantity',
                 'inform_a_reg_id',
                 'inform_b_reg_id',
                 'full_name',
                 'alc_code',
                 'producer_client_reg_id',
                 'producer_inn',
                 'producer_kpp',
                 'producer_full_name',
                 'producer_short_name',
                 'address_description',
             ], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Organization id',
            'quantity' => 'Quantity',
            'inform_a_reg_id' => 'InformARegId',
            'inform_b_reg_id' => 'InformBRegId',
            'full_name' => 'FullName',
            'alc_code' => 'AlcCode',
            'capacity' => 'Capacity',
            'alc_volume' => 'AlcVolume',
            'product_v_code' => 'ProductVCode',
            'producer_client_reg_id' => 'ProducerClientRegId',
            'producer_inn' => 'ProducerInn',
            'producer_kpp' => 'ProducerKpp',
            'producer_full_name' => 'ProducerFull_Name',
            'producer_short_name' => 'ProducerShortName',
            'address_country' => 'AddressCountry',
            'address_region_code' => 'Address Region Code',
            'address_description' => 'Address Description',
        ];
    }
}