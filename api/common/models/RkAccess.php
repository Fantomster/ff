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
class RkAccess extends \yii\db\ActiveRecord
{

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED   = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_access';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login', 'fid', 'password', 'token', 'lic', 'org'], 'required'],
            [['id', 'fid', 'org', 'ver'], 'integer'],
            [['token', 'login', 'password', 'salespoint'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'    => 'ID',
            'fid'   => 'FID',
            'token' => 'Token',
            'Nonce' => 'Nonce'
        ];
    }

    public static function getStatusArray()
    {
        return [
            RkAccess::STATUS_UNLOCKED => Yii::t('app', 'api.common.models.active', ['ru' => 'Активен']),
            RkAccess::STATUS_LOCKED   => Yii::t('app', 'api.common.models.off', ['ru' => 'Отключен']),
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'org']);
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
