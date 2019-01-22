<?php

namespace api\common\models;

use Yii;

/**
 * This is the model class for table "api_access".
 *
 * @property integer  $id
 * @property integer  $fid
 * @property string   $token
 * @property int      $acc
 * @property string   $nonce
 * @property string   $ip
 * @property datetime $fd
 * @property datetime $td
 * @property integer  $ver
 * @property integer  $status
 * @property datetime $extimefrom
 */
class ApiSession extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'api_session';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token', 'fid'], 'required'],
            [['id', 'fid', 'acc', 'ver'], 'integer'],
            [['token', 'nonce'], 'string', 'max' => 255],
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

    //  public static function getCountProduct($id) {
    //      return CatalogBaseGoods::find()->where(["category_id" => $id])->count();
    //  }

    //  public static function getCategory($id) {
    //      return MpCategory::find()->where(["id" => $id])->one()->name;
    //  }

    public function getAccess()
    {

        $acc = RkAccess::findOne('fid = :fid', ['fid' => $this->acc]);
        return $acc;
    }

    public function openSession()
    {
        $org = \common\models\User::findOne(['id' => Yii::$app->user->id])->organization_id;
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

}
