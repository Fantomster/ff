<?php

namespace api\common\models;

use Yii;

/**
 * This is the model class for table "rk_session".
 *
 * @property integer $id
 * @property integer $fid
 * @property integer $acc
 * @property string $cook
 * @property string $ip
 * @property datetime $fd
 * @property datetime $td
 * @property integer $ver
 * @property string $status
 * @property datetime $extime
 * @property string $comment
 * 
 */
class RkSession extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_session';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','fid','acc','cook','status','ver'], 'required'],
            [['id','fid','org','ver'], 'integer'],
            [['comment'], 'string', 'max' => 255],
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
    
  //  public static function getCountProduct($id) {
  //      return CatalogBaseGoods::find()->where(["category_id" => $id])->count();
  //  }
    
  //  public static function getCategory($id) {
  //      return MpCategory::find()->where(["id" => $id])->one()->name;
  //  }
    
        public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
