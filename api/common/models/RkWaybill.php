<?php

namespace api\common\models;
//use common\models\User;

use Yii;



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
class RkWaybill extends \yii\db\ActiveRecord
{
    
    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;
      
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_waybill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id','doc_date','corr_rid','store_rid','note','text_code','num_code'], 'required'],
            [['corr_rid','store_rid','status_id'], 'integer'],
       //     [['comment'], 'string', 'max' => 255],
       //     [['acc','rid','denom','agent_type','updated_at'],'safe']
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

    
    public function getCorr() {
        
      //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
      return RkAgent::find()->andWhere('rid = :corr_rid and acc = :acc',[':corr_rid' => $this->corr_rid, ':acc' => Yii::$app->user->identity->organization_id])->one();
           
    //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }
    
        
    public function getStore() {
        
      //  return RkAgent::findOne(['rid' => 'corr_rid','acc'=> 3243]);
      return RkStore::find()->andWhere('rid = :store_rid and acc = :acc',[':store_rid' => $this->store_rid, ':acc' => Yii::$app->user->identity->organization_id])->one();
           
    //    return $this->hasOne(RkAgent::className(), ['rid' => 'corr_rid','acc'=> 3243]);          
    }
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}

