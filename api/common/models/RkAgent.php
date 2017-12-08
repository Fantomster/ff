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
class RkAgent extends \yii\db\ActiveRecord
{
    
    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;
      
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_agent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['acc','rid','denom'], 'required'],
            [['acc','rid'], 'integer'],
            [['comment'], 'string', 'max' => 255],
            [['acc','rid','denom','agent_type','updated_at'],'safe']
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
            'rid' => 'RID Store House',
            'denom' => Yii::t('app', 'api.common.models.store', ['ru'=>'Наименование Store House']),
            'updated_at' => Yii::t('app', 'api.common.models.updated', ['ru'=>'Обновлено']),

        ];
    }
    
    
    public static function getStatusArray() {
        return [
        RkAccess::STATUS_UNLOCKED  => Yii::t('app', 'api.common.models.active_two', ['ru'=>'Активен']),
        RkAccess::STATUS_LOCKED => Yii::t('app', 'api.common.models.off_two', ['ru'=>'Отключен']),
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
