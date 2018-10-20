<?php

namespace api\common\models;

use api_web\modules\integration\modules\rkeeper\models\rkeeperService;
use Yii;
use common\models\Organization;
use yii\base\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "rk_waybill_data".
 *
 * @property integer $id
 * @property integer $waybill_id
 * @property integer $product_id
 * @property integer $product_rid
 * @property string $munit_rid
 * @property integer $org
 * @property integer $vat
 * @property integer $vat_included
 * @property double $sum
 * @property double $quant
 * @property double $defsum
 * @property double $defquant
 * @property double $koef
 * @property string $created_at
 * @property string $updated_at
 * @property string $linked_at
 * @property integer $unload_status
 *
 */
class RkWaybilldata extends \yii\db\ActiveRecord
{

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;

    public $pdenom;
    public $enable_all_map = true;
    public $koef_buttons;
    public $koef_forever;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_waybill_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['waybill_id', 'product_id'], 'required'],
            //  [['koef'], 'number'],
            //
            [['koef', 'sum', 'quant'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            // ['vat', 'in', 'allowArray' => true, 'range' => [0, 1000, 1800]],
            //   [['koef','sum','quant'], 'number', 'min' => 0.000001],
            // ['vat', 'in', 'allowArray' => true, 'range' => [0, 1000, 1800]],
            ['koef', 'filter', 'filter' => function ($value) {
                $newValue = 0 + str_replace(',', '.', $value);
                return $newValue;
            }],
            ['sum', 'filter', 'filter' => function ($value) {
                $newValue = 0 + str_replace(',', '.', $value);
                return $newValue;
            }],
            ['quant', 'filter', 'filter' => function ($value) {
                $newValue = 0 + str_replace(',', '.', $value);
                return $newValue;
            }],
            //[['koef', 'quant'], 'number', 'min' => 0.0001],
            //   [['comment'], 'string', 'max' => 255],
            [['waybill_id', 'product_rid', 'product_id', 'munit_rid', 'updated_at', 'quant', 'sum', 'vat', 'pdenom', 'koef', 'org', 'vat_included', 'linked_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fid' => 'FID',
            'id' => Yii::t('app', 'ID'),
            'waybill_id' => Yii::t('app', 'Waybill ID'),
            'product_id' => Yii::t('app', 'ID в Mixcart'),
            'product_rid' => Yii::t('app', 'Product Rid'),
            'munit' => Yii::t('app', 'Munit'),
            'org' => Yii::t('app', 'Org'),
            'vat' => Yii::t('app', 'Vat'),
            'vat_included' => Yii::t('app', 'Vat Included'),
            'sum' => Yii::t('app', 'Сумма б/н'),
            'quant' => Yii::t('app', 'Количество'),
            'defsum' => Yii::t('app', 'Defsum'),
            'defquant' => Yii::t('app', 'Defquant'),
            'koef' => Yii::t('app', 'Коэфф.'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'fproductnameProduct' => Yii::t('app', 'Наименование продукции'),
            'enable_all_map' => Yii::t('app', 'Сохранить в сопоставлении'),
            'koef_buttons' => Yii::t('app', ''),
            'koef_forever' => Yii::t('app', ''),
            'querys' => Yii::t('app', ''),
            'unload_status' => Yii::t('app', 'Статус для отправления'),
        ];
    }

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED => 'Отключен',
        ];
    }

    /**
     * @return RkWaybill
     */
    public function getWaybill()
    {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkWaybill::findOne(['id' => $this->waybill_id]);

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);
    }

    public function getVat()
    {


    }

    public function getProduct()
    {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        $rprod = RkProduct::find()->andWhere('id = :id', [':id' => $this->product_rid])->one();

        return $rprod;

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFproductname()
    {
        return $this->hasOne(\common\models\CatalogBaseGoods::className(), ['id' => 'product_id']);
    }

    public function getFproductnameProduct()
    {
        return $this->fproductname->product;
    }

    public function beforeSave($insert)
    {

        if (parent::beforeSave($insert)) {

            if (!$insert) {  // Обновление

                /*    if (strrpos($this->koef,','))
                  $this->koef = (double) str_replace(',', '.',$this->koef);

                  if (strrpos($this->sum,','))
                  $this->sum = (double)  str_replace(',', '.', $this->sum);

                  if (strrpos($this->quant,','))
                  $this->quant = (double) str_replace(',', '.', $this->quant);
                 */
                if ($this->attributes['koef'] != $this->oldAttributes['koef']) {

                    if (!$this->koef)
                        $this->koef = 1;

                    $this->quant = round($this->defquant * $this->koef, 10);
                }

                if ($this->attributes['quant'] != $this->oldAttributes['quant']) {

                    $this->koef = round($this->quant / $this->defquant, 10);
                    if ($this->attributes['quant'] == 0) {
                        $this->koef = 1;
                    }
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
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // $this->saveAllMap();

        $wmodel = $this->waybill;
        $check = $this::find()
            ->andwhere('waybill_id= :id', [':id' => $wmodel->id])
            ->andwhere('product_rid is null or munit_rid is null')
            ->andWhere('unload_status=1')
            ->count('*');

        if ($check > 0) {
            $wmodel->readytoexport = 0;
            $wmodel->status_id = 1;
        } else {
            $wmodel->readytoexport = 1;
            $wmodel->status_id = 5;
        }

        if (!$wmodel->save(false)) {
            echo "Can't save model in after save";
            exit;
        }
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

    /**
     * @return double
     */
    public function getSumByWaybillid($number)
    {
        Yii::$app->get('db_api');
        $sum = 0;
        $summes = RkWaybillData::find()->where(['waybill_id' => $number])->all();
        foreach ($summes as $summa) {
            $sum += $summa->sum;
        }
        $sum = number_format($sum, 2, ',', ' ');
        return $sum;
    }

    /**
     * @return bool
     */
    public function saveAllMap()
    {
        $client_id = $this->getWaybill()->org;
        $vendor_id = $this->getWaybill()->getOrder()->vendor_id;

        $allMapModel = AllMaps::findOne([
            'service_id' => rkeeperService::getServiceId(),
            'org_id' => $client_id,
            //    'supp_id' => $vendor_id,
            'product_id' => $this->product_id
        ]);

        if (empty($allMapModel)) {
            $allMapModel = new AllMaps([
                'service_id' => rkeeperService::getServiceId(),
                'org_id' => $client_id,
                'supp_id' => $vendor_id,
                'product_id' => $this->product_id,
                'created_at' => Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss')
            ]);
        }

        $allMapModel->serviceproduct_id = $this->product_rid;
        $allMapModel->outer_unit_id = $this->munit_rid;
        // $allMapModel->store_rid = $this->getWaybill()->store_rid;
        $allMapModel->koef = $this->koef;
        $allMapModel->vat = $this->vat * 100;
        $allMapModel->is_active = 1;

        return !empty($allMapModel->dirtyAttributes) ? $allMapModel->save() : false;
    }

}