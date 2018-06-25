<?php

namespace api\common\models\one_s;

use Yii;

/**
 * This is the model class for table "iiko_dicstatus".
 *
 * @property integer $id
 * @property string $denom
 * @property string $comment
 */
class OneSDicstatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_dicstatus';
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
            [['denom', 'comment'], 'string', 'max' => 255],
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
            'comment' => Yii::t('app', 'Comment'),
        ];
    }
}
