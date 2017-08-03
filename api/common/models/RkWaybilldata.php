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
 * 
 * 
 */
class RkWaybilldata extends \yii\db\ActiveRecord
{
    
    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;
    
    public $pdenom;
      
    
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
            [['waybill_id','product_id'], 'required'],
         //   [['acc','rid'], 'integer'],
         //   [['comment'], 'string', 'max' => 255],
            [['waybill_id','product_rid','product_id','munit_rid','updated_at','quant','sum','vat','pdenom'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fid' => 'FID',
            'sum' => 'Цена',
            'quant' => 'Количество',
            'product_id' => 'ID в F-keeper',            

        ];
    }
    
    
    public static function getStatusArray() {
        return [
        RkAccess::STATUS_UNLOCKED  => 'Активен',
        RkAccess::STATUS_LOCKED => 'Отключен',    
        ];
    }

    public function getWaybill() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        return RkWaybill::findOne(['id'=>$this->waybill_id]);

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }
    
    public function getProduct() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        $rprod = RkProduct::find()->andWhere('rid = :rid and unit_rid = :urid',[':rid' =>$this->product_rid,':urid' => $this->munit_rid]);
        
        return $rprod;

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }
    
    public function getFproductname() {

        //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
        $rprod = \common\models\CatalogBaseGoods::find()->andWhere('id = :id',[':id' =>$this->product_id]);
        
        return $rprod;

        //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }
    
        public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        $wmodel = $this->waybill;
        
        $check = $this::find()
                      ->andwhere('waybill_id= :id',[':id'=>$wmodel->id])
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
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
