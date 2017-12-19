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
            'dictype_id' => Yii::t('app', 'api.common.models.directory', ['ru'=>'Справочник']),
            'dicstatus_id' => Yii::t('app', 'api.common.models.condition', ['ru'=>'Состояние']),
            'created_at' => Yii::t('app', 'api.common.models.created', ['ru'=>'Создано']),
            'updated_at' => Yii::t('app', 'api.common.models.updated_two', ['ru'=>'Обновлено']),
            'obj_count' => Yii::t('app', 'api.common.models.objects_count', ['ru'=>'Кол-во объектов']),
            'obj_mapcount' => Yii::t('app', 'api.common.models.count', ['ru'=>'Кол-во сопоставленных']),
          //  'dictype_id' => '',
            
            
        ];
    }
    
    
    public static function getStatusArray() {
        return [
        RkAccess::STATUS_UNLOCKED  => Yii::t('app', 'api.common.models.active_three', ['ru'=>'Активен']),
        RkAccess::STATUS_LOCKED => Yii::t('app', 'api.common.models.off_three', ['ru'=>'Отключен']),
        ];
    }

    public function getOrganization() {
           return $this->hasOne(Organization::className(), ['id' => 'org']);          
           
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
