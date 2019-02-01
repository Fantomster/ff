<?php

namespace api\common\models\iiko;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "iiko_waybill_data".
 *
 * @property integer $id
 * @property integer $waybill_id
 * @property integer $product_id
 * @property integer $product_rid
 * @property string  $munit
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
 * @property integer $unload_status
 */
class iikoWaybillData extends \yii\db\ActiveRecord
{
    public $pdenom;
    public $enable_all_map = true;
    public $koef_buttons;
    public $koef_forever;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'iiko_waybill_data';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['waybill_id'], 'required'],
            [['waybill_id', 'product_id', 'product_rid', 'org', 'vat', 'vat_included'], 'integer'],
            [['sum', 'quant', 'defsum', 'defquant', 'koef', 'created_at', 'updated_at', 'pdenom'], 'safe'],
            [['munit', 'koef_buttons'], 'string', 'max' => 10],
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
            //[['koef', 'quant'], 'number', 'min' => 0.0001],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!$insert) {  // Обновление
                if ($this->attributes['koef'] != $this->oldAttributes['koef']) {
                    if (!$this->koef) {
                        $this->koef = 1;
                    }
                    $this->quant = round($this->defquant * $this->koef, 10);
                }
                if ($this->attributes['quant'] != $this->oldAttributes['quant']) {
                    $this->koef = round($this->quant / $this->defquant, 10);
                    if ($this->attributes['quant'] == 0) {
                        $this->koef = 1;
                    }
                }
            } else { // Создание
                //    $this->koef = 1;
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->setReadyToExportStatus();
    }

    protected function setReadyToExportStatus()
    {

        $wmodel = $this->waybill;
        $check = $this::find()
            ->andwhere('waybill_id= :id', [':id' => $wmodel->id])
            ->andwhere('product_rid is null')
            ->andWhere('unload_status=1')
            ->count('*');

        if (!isset($wmodel->store_id) || empty($wmodel->agent_uuid) || $check > 0) {
            $wmodel->readytoexport = 0;
            $wmodel->status_id = 1;
        } else {
            $wmodel->readytoexport = 1;
            $wmodel->status_id = 4;
        }

        if (!$wmodel->save(false)) {
            echo "Can't save model in after save";
            return false;
        } else {
            return true;
        }

    }

    public function getWaybill()
    {
        return iikoWaybill::findOne(['id' => $this->waybill_id]);
    }

    public function getFproductname()
    {
        return $this->hasOne(\common\models\CatalogBaseGoods::className(), ['id' => 'product_id']);
    }

    public function getFproductnameProduct()
    {
        return $this->fproductname->product;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(iikoProduct::className(), ['id' => 'product_rid']);
    }

    /**
     * @return double
     */
    public function getSumByWaybillid($number)
    {
        Yii::$app->get('db_api');
        $sum = 0;
        $summes = iikoWaybillData::find()->where(['waybill_id' => $number])->all();
        foreach ($summes as $summa) {
            $sum += $summa->sum;
        }
        $sum = number_format($sum, 2, ',', ' ');
        return $sum;
    }
}