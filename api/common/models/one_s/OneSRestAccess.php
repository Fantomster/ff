<?php

namespace api\common\models\one_s;

use Yii;

/**
 * This is the model class for table "one_s_rest_access".
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
class OneSRestAccess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_rest_access';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid'], 'required'],
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
    

    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
