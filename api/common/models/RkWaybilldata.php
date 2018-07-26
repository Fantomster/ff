<?php

namespace api\common\models;

use Yii;
use common\models\Organization;
use yii\base\Exception;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer $id
 * @property integer $fid
 * @property integer $org
 * @property string $login
 * @property string $password
 * @property string $token
 * @property string $lic
 * @property datetime $fd
 * @property datetime $td
 * @property integer $ver
 * @property integer $locked
 * @property string $usereq 
 * @property string $comment
 * @property string $salespoint
 * @property datetime $linked_at
 * 
 */
class RkWaybilldata extends \yii\db\ActiveRecord {

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;

    public $pdenom;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'rk_waybill_data';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['waybill_id', 'product_id'], 'required'],
            //  [['koef'], 'number'],
            //  
            [['koef', 'sum', 'quant'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            ['vat', 'in', 'allowArray' => true,  'range' => [0, 1000, 1800] ],
            //   [['koef','sum','quant'], 'number', 'min' => 0.000001],
            ['vat', 'in', 'allowArray' => true,  'range' => [0, 1000, 1800] ],
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
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'fid' => 'FID',
            'sum' => 'Сумма б/н',
            'quant' => 'Количество',
            'product_id' => 'ID в Mixcart',
            'koef' => 'Коэфф.',
            'fproductnameProduct'=>'Наименование продукции'
        ];
    }

    public static function getStatusArray() {
        return [
            RkAccess::STATUS_UNLOCKED => 'Активен',
            RkAccess::STATUS_LOCKED => 'Отключен',
        ];
    }

    public function getWaybill() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkWaybill::findOne(['id' => $this->waybill_id]);

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    public function getVat() {


    }

    public function getProduct() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        $rprod = RkProduct::find()->andWhere('id = :id', [':id' => $this->product_rid])->one();

        return $rprod;

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }

    public function getFproductname()
    {

        return $this->hasOne(\common\models\CatalogBaseGoods::className(), ['id' => 'product_id']);
    }

    public function getFproductnameProduct()
    {
        return $this->fproductname->product;
    }

    public function beforeSave($insert) {

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
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        $wmodel = $this->waybill;

        $check = $this::find()
                ->andwhere('waybill_id= :id', [':id' => $wmodel->id])
                ->andwhere('product_rid is null or munit_rid is null')
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

    public static function getDb() {
        return \Yii::$app->db_api;
    }

    /**
     * @return double
     */
    public function getSumByWaybillid($number)
    {
        Yii::$app->get('db_api');
        $sum=0;
        $summes = RkWaybillData::find()->where(['waybill_id' => $number])->all();
        foreach ($summes as $summa) {
            $sum+=$summa->sum;
        }
        return $sum;
    }

}
