<?php

namespace api\common\models;

use Yii;
use common\models\Organization;


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
class RkDic extends \yii\db\ActiveRecord
{
    
    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;
      
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_dic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
      //      [['login','fid','password','token','lic','org'], 'required'],
            [['org_id','dictype_id','dicstatus_id','obj_count','obj_mapcount'], 'integer'],
            [['org_id','dictype_id','dicstatus_id','obj_count','obj_mapcount','created_at','updated_at'], 'safe'],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dictype_id' => 'Справочник',
            'dicstatus_id' => 'Состояние',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'obj_count' => 'Кол-во объектов',
            'obj_mapcount' => 'Кол-во сопоставленных',
          //  'dictype_id' => '',
            
            
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
    
    
    public function getDictype() {
           return $this->hasOne(RkDictype::className(), ['id' => 'dictype_id']);          
           
    }
    
        
    public function getDicstatus() {
           return $this->hasOne(RkDicstatus::className(), ['id' => 'dicstatus_id']);          
           
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
