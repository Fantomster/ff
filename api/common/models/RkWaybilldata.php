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
            [['waybill_id','product_rid','product_id','munit_rid','updated_at','quant','sum','vat'],'safe']
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
            'token' => 'Token',
            'Nonce' => 'Nonce'
        ];
    }
    
    
    public static function getStatusArray() {
        return [
        RkAccess::STATUS_UNLOCKED  => 'Активен',
        RkAccess::STATUS_LOCKED => 'Отключен',    
        ];
    }

    public function getOrganization() {
           return $this->hasOne(Organization_api::className(), ['id' => 'org']);          
           
    }
    
    public function getOrganizationName()
{
    $org = $this->organization;
    return $org ? $org->name : 'no';
}
    
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
