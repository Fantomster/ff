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
class RkWserror extends \yii\db\ActiveRecord
{
     
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_wserror';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login','fid','password','token','lic','org'], 'required'],
            [['id','fid','org','ver'], 'integer'],
            [['token','login','password','salespoint'], 'string', 'max' => 255],
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
    
    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
