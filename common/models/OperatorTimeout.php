<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%operator_timeout}}".
 *
 * @property int $operator_id
 * @property string $timeout_at
 * @property int $timeout
 */
class OperatorTimeout extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%operator_timeout}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['operator_id'], 'required'],
            [['operator_id', 'timeout'], 'integer'],
            [['timeout_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'operator_id' => Yii::t('app', 'Operator ID'),
            'timeout_at' => Yii::t('app', 'Timeout At'),
            'timeout' => Yii::t('app', 'Timeout'),
        ];
    }
}
