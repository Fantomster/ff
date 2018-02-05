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
class RkTasks extends \yii\db\ActiveRecord
{
    
    const INTSTATUS_SENT = 1;
    const INTSTATUS_EXTERROR = 2;
    const INTSTATUS_XMLOK = 3;
    const INTSTATUS_DICOK = 4;
    const INTSTATUS_FULLOK = 5;


      
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid','acc','guid'], 'required'],
            [['id','fid','acc'], 'integer'],
            [['guid','acc','created_at','updated_at', 'callback_at', 'intstatus_id', 'wsstatus_id', 
                'wsclientstatus_id','tasktype_id','fid','fcode','version','isactive','callback_xml','callback_end','rcount','total_parts','current_part', 'req_uid' ], 'safe'],
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
            'guid' => 'GUID',
            'Nonce' => 'Nonce'
        ];
    }
    
   /* 
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
    
    */
    
   
    public function setCallbackXML() {
        
        $this->callback_xml = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

        if (!$this->save())
            return false;
        else
            return true;
    }

    public function setCallbackStart() {

        $this->callback_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        $this->intstatus_id = self::INTSTATUS_XMLOK;

        if (!$this->save())
            return false;
        else
            return true;
    }

    public function setCallbackEnd() {

        $this->callback_end = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

        if (!$this->save())
            return false;
        else
            return true;
    }

    public function isAllPartsReady($uid) {

        $parts = RkTasks::find()->andWhere('req_uid = :uid',[':uid' => $uid])->andWhere('fcode = 0')->all();

        return ($parts) ? false : true;
    }

    public static function getDb()
    {
       return \Yii::$app->db_api;
    }



}
