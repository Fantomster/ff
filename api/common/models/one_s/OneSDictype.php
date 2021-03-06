<?php

namespace api\common\models\one_s;

use Yii;

/**
 * This is the model class for table "iiko_dictype".
 *
 * @property integer $id
 * @property string $denom
 * @property string $created_at
 * @property string $comment
 * @property string $contr
 * @property string $method
 */
class OneSDictype extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_dictype';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['denom', 'comment'], 'string', 'max' => 255],
            [['contr'], 'string', 'max' => 128],
            [['method'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'denom' => Yii::t('app', 'Denom'),
            'created_at' => Yii::t('app', 'Created At'),
            'comment' => Yii::t('app', 'Comment'),
            'contr' => Yii::t('app', 'Contr'),
            'method' => Yii::t('app', 'Method'),
        ];
    }
}
