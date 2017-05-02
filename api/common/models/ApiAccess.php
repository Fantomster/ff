<?php

namespace api\common\models;

use Yii;

/**
 * This is the model class for table "api_access".
 *
 * @property integer $id
 * @property integer $fid
 * @property integer $org
 * @property string $login
 * @property string $password
 * @property datetime $fd
 * @property datetime $td
 * @property integer $ver
 * @property integer $locked
 */
class ApiAccess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'api_access';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','fid'], 'required'],
            [['id','fid','org','ver'], 'integer'],
            [['login','password'], 'string', 'max' => 255],
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
            'login' => 'Login',
            'Password' => 'Password'
        ];
    }
    
  //  public static function getCountProduct($id) {
  //      return CatalogBaseGoods::find()->where(["category_id" => $id])->count();
  //  }
    
  //  public static function getCategory($id) {
  //      return MpCategory::find()->where(["id" => $id])->one()->name;
  //  }
   public function getDb() {
   return Yii::$app->db_api;
}

}
