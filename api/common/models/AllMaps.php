<?php

namespace api\common\models;

use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use common\models\Catalog;
use Yii;
use common\models\Organization;
use yii\base\Exception;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $supp_id
 * @property integer $cat_id
 * @property integer $product_id
 * @property integer $product_rid
 * @property integer $org_id
 * @property integer $vat
 * @property integer $vat_included
 * @property double $koef
 * @property integer $store_rid
 * @property integer $is_active
 * @property datetime $created_at
 * @property datetime $updated_at
 * @property datetime $linked_at
 *
 *
 */
class AllMaps extends \yii\db\ActiveRecord {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'all_map';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['supp_id','org_id', 'product_id'], 'required'],
            //  [['koef'], 'number'],
            //  
            [['koef'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            //   [['koef','sum','quant'], 'number', 'min' => 0.000001],
            ['koef', 'filter', 'filter' => function ($value) {
                        $newValue = 0 + str_replace(',', '.', $value);
                        return $newValue;
                    }],
            [['koef'], 'number', 'min' => 0.0001],
            //   [['comment'], 'string', 'max' => 255],
            [[ 'product_rid', 'product_id', 'updated_at', 'vat', 'koef', 'org_id',
                'vat_included', 'linked_at', 'pdenom', 'munit_rid'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
 'id' => 'ID',
 'service_id' => 'Сервис',
 'supp_id' => 'Поставщик',
 'cat_id' => 'Каталог',
 'product_id' => 'Продукт Поставщика',
 'product_rid' => 'Продукт сервиса',
 'org_id' => 'Организация',
 'vat' => 'Ставка НДС',
 'vat_included' => 'НДС включен в цену',
 'koef' => 'Коэффициент пересчета',
 'store_rid' => 'Склад',
 'is_active' => 'Активность',
 'created_at' => 'Дата создания',
 'updated_at' => 'Дата обновления',
 'linked_at' => 'Дата сопоставления',
        ];
    }

    public function getService() {

        // return RkWaybill::findOne(['id' => $this->waybill_id]); TODO Return Service

    }

    public function getSupplier() {
        return Organization::find()->andWhere('id = :id', [':id' => $this->supp_id])->one();
    }

    public function getCatalog() {
        return Catalog::find()->andWhere('id = :id', [':id' => $this->cat_id])->one();
    }

    public function getProduct() {
        return CatalogBaseGoods::find()->andWhere('id = :id', [':id' => $this->product_id])->one();
    }

    public function getProductrk() {
        return RkProduct::find()->andWhere('id = :rid', [':rid' => $this->product_rid])->one();
    }


    public function beforeSave($insert) {

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

                $this->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

            } else { // Создание
               // $this->koef = 1;
            }


            return true;
        } else {
            return false;
        }
    }

/*    public function beforeValidate() {
        
        if (parent::beforeValidate()) {
            $this->koef = 0 + str_replace(',', '.', $this->koef);
            
            return true;
        }
        return false;
    }
*/

/*
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);


    }
*/

    public static function getDb() {
        return \Yii::$app->db_api;
    }

}
