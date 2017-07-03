<?php

namespace api\common\models;

use Yii;

/**
 * This is the model class for table "api_actions".
 *
 * @property integer $id
 * @property string $action
 * @property integer $session
 * @property datetime $created
 * @property integer $result
 * @property string $comment
 */
class ApiActions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'api_actions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action'], 'required'],
            [['id','session'], 'integer'],
            [['action','comment'], 'string', 'max' => 255],
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
    
            public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
