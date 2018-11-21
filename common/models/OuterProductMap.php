<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "outer_product_map".
 *
 * @property int              $id               первичный ключ
 * @property string           $created_at       Дата создания записи
 * @property string           $updated_at       Дата последнего изменения записи
 * @property int              $service_id       id сервиса из таблицы all_service
 * @property int              $organization_id  id ресторана
 * @property int              $vendor_id        id поставщика
 * @property int              $product_id       id продукта в MC
 * @property int              $outer_product_id id продукта из у.с. таблицы outer_product
 * @property int              $outer_unit_id    id единицы измерения у.с. таблицы outer_unit
 * @property int              $outer_store_id   id склада у.с. таблицы outer_store
 * @property double           $coefficient      коэффициент
 * @property double           $vat              НДС
 * @property AllService       $service
 * @property OuterProduct     $outerProduct
 * @property OuterStore       $outerStore
 * @property OuterUnit        $outerUnit
 * @property CatalogBaseGoods $product
 */
class OuterProductMap extends \yii\db\ActiveRecord
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outer_product_map';
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
            [['created_at', 'updated_at'], 'safe'],
            [['service_id', 'organization_id', 'vendor_id', 'product_id'], 'required'],
            [['service_id', 'organization_id', 'vendor_id', 'product_id', 'outer_product_id', 'outer_unit_id', 'outer_store_id'], 'integer'],
            [['coefficient', 'vat'], 'number'],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => AllService::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['outer_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => OuterProduct::className(), 'targetAttribute' => ['outer_product_id' => 'id']],
            [['outer_store_id'], 'exist', 'skipOnError' => true, 'targetClass' => OuterStore::className(), 'targetAttribute' => ['outer_store_id' => 'id']],
            [['outer_unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => OuterUnit::className(), 'targetAttribute' => ['outer_unit_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => Yii::t('app', 'ID'),
            'created_at'       => Yii::t('app', 'Created At'),
            'updated_at'       => Yii::t('app', 'Updated At'),
            'service_id'       => Yii::t('app', 'Service ID'),
            'organization_id'  => Yii::t('app', 'Rest ID'),
            'vendor_id'        => Yii::t('app', 'Vendor ID'),
            'product_id'       => Yii::t('app', 'Product ID in MC'),
            'outer_product_id' => Yii::t('app', 'Ooter product ID'),
            'outer_unit_id'    => Yii::t('app', 'Outer unit ID'),
            'outer_store_id'   => Yii::t('app', 'Outer store ID'),
            'coefficient'      => Yii::t('app', 'Coefficient'),
            'vat'              => Yii::t('app', 'Vat'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(AllService::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterProduct()
    {
        return $this->hasOne(OuterProduct::className(), ['id' => 'outer_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CatalogBaseGoods::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterStore()
    {
        return $this->hasOne(OuterStore::className(), ['id' => 'outer_store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOuterUnit()
    {
        return $this->hasOne(OuterUnit::className(), ['id' => 'outer_unit_id']);
    }

    /**
     * @param $org_id
     * @return null|string
     */
    public static function getMainOrg($org_id)
    {
        $obDicConstModel = IntegrationSetting::findOne(['name' => 'main_org']);
        $obConstModel = IntegrationSettingValue::findOne(['setting_id' => $obDicConstModel->id, 'org_id' => $org_id]);
        return $obConstModel->value ?? null;
    }

    /**
     * @param $org_id
     * @return array
     */
    public static function getChildOrgsId($org_id)
    {
        $obDicConstModel = IntegrationSetting::findOne(['name' => 'main_org']);
        return IntegrationSettingValue::find()->select('org_id')->where(['setting_id' => $obDicConstModel->id, 'value' => $org_id])->column();
    }
}
