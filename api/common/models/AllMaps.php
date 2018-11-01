<?php

namespace api\common\models;

use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoStore;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSStore;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use common\models\Catalog;
use Yii;
use common\models\Organization;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "rk_access".
 *
 * @property  integer  $id
 * @property  integer  $service_id
 * @property  integer  $org_id
 * @property  integer  $product_id
 * @property  integer  $supp_id
 * @property  integer  $serviceproduct_id
 * @property  integer  $unit_rid
 * @property  integer  $store_rid
 * @property  double   $koef
 * @property  integer  $vat
 * @property  integer  $is_active
 * @property  datetime $created_at
 * @property  datetime $linked_at
 * @property  datetime $updated_at
 */
class AllMaps extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'all_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['supp_id', 'org_id', 'product_id'], 'required'],
            [['org_id', 'product_id'], 'required'],
            //  [['koef'], 'number'],
            //  
            [['koef'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            //[['koef'], 'number', 'min' => 0.000001],
            ['koef', 'filter', 'filter' => function ($value) {
                $newValue = 0 + str_replace(',', '.', $value);
                return $newValue;
            }, 'on'                     => 'koef'],
            //  [['koef'], 'number', 'min' => 0.0001],
            //   [['comment'], 'string', 'max' => 255],
            [['serviceproduct_id', 'product_id', 'updated_at', 'vat', 'koef', 'org_id', 'supp_id',
                'vat_included', 'linked_at', 'pdenom', 'munit_rid', 'store_rid'], 'safe']
        ];

        // test git
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'service_id'        => 'Сервис',
            'supp_id'           => 'Поставщик',
            'cat_id'            => 'Каталог',
            'product_id'        => 'Продукт Поставщика',
            'serviceproduct_id' => 'Продукт сервиса',
            'org_id'            => 'Организация',
            'vat'               => 'Ставка НДС',
            'vat_included'      => 'НДС включен в цену',
            'koef'              => 'Коэффициент пересчета',
            'store_rid'         => 'Склад',
            'is_active'         => 'Активность',
            'created_at'        => 'Дата создания',
            'updated_at'        => 'Дата обновления',
            'linked_at'         => 'Дата сопоставления',
        ];
    }

    public function getService()
    {

        // return RkWaybill::findOne(['id' => $this->waybill_id]); TODO Return Service

    }

    public function getSupplier()
    {
        return Organization::find()->andWhere('id = :id', [':id' => $this->supp_id])->one();
    }

    public function getCatalog()
    {
        return Catalog::find()->andWhere('id = :id', [':id' => $this->cat_id])->one();
    }

    public function getProduct()
    {
        return CatalogBaseGoods::find()->andWhere('id = :id', [':id' => $this->product_id])->one();
    }

    public function getProductNameService()
    {
        $attr = 'denom';

        switch ($this->service_id) {
            case 1 : // R-keeper
                $modelName = RkProduct::class;
                break;
            case 2 : // iiko
                $modelName = iikoProduct::class;
                break;
            case 8 : // 1C
                $modelName = OneSGood::class;
                $attr = 'name';
                break;
            case 10 : // tillypad
                $modelName = iikoProduct::class;
                break;
        }

        $res = $modelName::find()->andWhere('id = :id', [':id' => $this->serviceproduct_id])->one();
        return isset($res) ? $res->{$attr} : null;
    }

    public static function getStoreListService($service_id, $org_id)
    {
        $stores = [-1 => 'Нет'];
        switch ($service_id) {
            case 1 : // R-keeper
                $stores += ArrayHelper::map(RkStoretree::find()->andWhere('acc=:acc and active = 1', [':acc' => $org_id])->
                andWhere('type = 2')->all(), 'id', 'name');
                break;

            case 2 : // iiko
                $stores += ArrayHelper::map(iikoStore::find()->andWhere('org_id=:acc', [':acc' => $org_id])->
                andWhere('is_active = 1')->all(), 'id', 'denom');
                break;

            case 8 : // 1C
                $stores += ArrayHelper::map(OneSStore::find()->andWhere('org_id=:acc', [':acc' => $org_id])->
                all(), 'id', 'name');
                break;

            case 10 : // tillypad
                $stores += ArrayHelper::map(iikoStore::find()->andWhere('org_id=:acc', [':acc' => $org_id])->
                andWhere('is_active = 1')->all(), 'id', 'denom');
                break;
        }

        return $stores;
    }

    public function getStore()
    {
        $acc = ($this->org_id === null) ? Yii::$app->user->identity->organization_id : $this->org_id;

        switch ($this->service_id) {

            case 1:  // R-keeper
                return RkStoretree::find()->andWhere('id = :rid', [':rid' => $this->store_rid])->
                andWhere('acc = :acc', [':acc' => $acc])->one();
            case 2:  // iiko
                return iikoStore::find()->andWhere('id = :id', [':id' => $this->store_rid])->
                andWhere('org_id = :acc', [':acc' => $acc])->one();
            case 8:  // 1C
                return OneSStore::find()->andWhere('id = :id', [':id' => $this->store_rid])->
                andWhere('org_id = :acc', [':acc' => $acc])->one();
            case 10:  // tillypad
                return iikoStore::find()->andWhere('id = :id', [':id' => $this->store_rid])->
                andWhere('org_id = :acc', [':acc' => $acc])->one();
            default:
                return null;

        }

    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if (!$insert) {  // Обновление
                /*
                 if (strrpos($this->koef,','))
                 $this->koef = (double) str_replace(',', '.',$this->koef);

                 if (strrpos($this->sum,','))
                 $this->sum = (double)  str_replace(',', '.', $this->sum);

                 if (strrpos($this->quant,','))
                 $this->quant = (double) str_replace(',', '.', $this->quant);
                */
                if ($this->attributes['koef'] != $this->oldAttributes['koef']) {

                    if (!$this->koef)
                        $this->koef = 1;
                }
                $this->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                //$this->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            } else { // Создание
                // $this->koef = 1;
                // $this->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                $this->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                $this->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            }

            return true;
        } else {
            return false;
        }
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }
}
