<?php

namespace api\common\models;

use Yii;
use common\models\Organization;


/**
 * This is the model class for table "rk_dicconst".
 *
 * @property integer $id
 * @property string $denom
 * @property string $def_value
 * @property string $comment
 *
 */
class RkDicconst extends \yii\db\ActiveRecord
{

    const PC_TYPE_DROP = 1;
    const PC_TYPE_STRING = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_dicconst';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['denom','def_value','comment'], 'string', 'max' => 255],
            [['denom','def_value','comment'], 'safe'],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'denom' => 'Название константы',
            'def_value' => 'Значение по умолчанию',
            'comment' => 'Комментарий',
        ];
    }


    public static function getDb()
    {
       return \Yii::$app->db_api;
    }


}
