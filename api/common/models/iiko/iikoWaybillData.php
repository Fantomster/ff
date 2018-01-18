<?php

namespace api\common\models\iiko;

use Yii;

/**
 * This is the model class for table "iiko_waybill_data".
 *
 * @property integer $id
 * @property integer $waybill_id
 * @property integer $product_id
 * @property integer $product_rid
 * @property string $munit
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
 */
class iikoWaybillData extends \yii\db\ActiveRecord
{
    public $pdenom;

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
            [['munit'], 'string', 'max' => 10],
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
            [['koef', 'sum', 'quant'], 'number', 'min' => 0.0001],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'waybill_id' => Yii::t('app', 'Waybill ID'),
            'product_id' => Yii::t('app', 'ID в F-keeper'),
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
                }
            } else { // Создание
                $this->koef = 1;
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $wmodel = $this->waybill;

        $check = $this::find()
            ->andwhere('waybill_id= :id', [':id' => $wmodel->id])
            ->andwhere('product_rid is null or munit is null')
            ->count('*');

        if ($check > 0) {
            $wmodel->readytoexport = 0;
        } else {
            $wmodel->readytoexport = 1;
        }

        if (!$wmodel->save(false)) {
            echo "Can't save model in after save";
            exit;
        }
    }

    public function getWaybill()
    {
        return iikoWaybill::findOne(['id' => $this->waybill_id]);
    }

    public function getFproductname()
    {
        $prod = \common\models\CatalogBaseGoods::find()->andWhere('id = :id', [':id' => $this->product_id]);
        return $prod;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct() {
        return $this->hasOne(iikoProduct::className(), ['id' => 'product_rid']);
    }
}
