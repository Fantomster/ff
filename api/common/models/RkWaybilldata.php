<?php

namespace api\common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "rk_waybill_data".
 *
 * @property integer $id
 * @property integer $waybill_id
 * @property integer $product_id
 * @property integer $product_rid
 * @property string  $munit_rid
 * @property integer $org
 * @property integer $vat
 * @property integer $vat_included
 * @property double  $sum
 * @property double  $quant
 * @property double  $defsum
 * @property double  $defquant
 * @property double  $koef
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $linked_at
 * @property integer $unload_status
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
            [['koef', 'sum', 'quant'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
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
            [['waybill_id', 'product_rid', 'product_id', 'munit_rid', 'updated_at', 'quant', 'sum', 'vat', 'pdenom', 'koef', 'org', 'vat_included', 'linked_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fid'                 => 'FID',
            'id'                  => Yii::t('app', 'ID'),
            'waybill_id'          => Yii::t('app', 'Waybill ID'),
            'product_id'          => Yii::t('app', 'ID в Mixcart'),
            'product_rid'         => Yii::t('app', 'Product Rid'),
            'munit'               => Yii::t('app', 'Munit'),
            'org'                 => Yii::t('app', 'Org'),
            'vat'                 => Yii::t('app', 'Vat'),
            'vat_included'        => Yii::t('app', 'Vat Included'),
            'sum'                 => Yii::t('app', 'Сумма б/н'),
            'quant'               => Yii::t('app', 'Количество'),
            'defsum'              => Yii::t('app', 'Defsum'),
            'defquant'            => Yii::t('app', 'Defquant'),
            'koef'                => Yii::t('app', 'Коэфф.'),
            'created_at'          => Yii::t('app', 'Created At'),
            'updated_at'          => Yii::t('app', 'Updated At'),
            'fproductnameProduct' => Yii::t('app', 'Наименование продукции'),
            'enable_all_map'      => Yii::t('app', 'Сохранить в сопоставлении'),
            'koef_buttons'        => Yii::t('app', ''),
            'koef_forever'        => Yii::t('app', ''),
            'querys'              => Yii::t('app', ''),
            'unload_status'       => Yii::t('app', 'Статус для отправления'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
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

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED   => 'Отключен',
        ];
    }

    /**
     * @return RkWaybill
     */
    public function getWaybill()
    {
        return RkWaybill::findOne(['id' => $this->waybill_id]);
    }

    public function getProduct()
    {
        $rprod = RkProduct::find()->andWhere('id = :id', [':id' => $this->product_rid])->one();

        return $rprod;
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

}