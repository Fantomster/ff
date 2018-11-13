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
    public static function primaryKey()
    {
        return ['operator_id'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%operator_timeout}}';
    }

    public static function getTimeoutOperator($id) {
        $model = self::findOne(['operator_id' => $id]);
        if(!isset($model)) {
            return 0;
        } else {
            $time_end = strtotime($model->timeout_at) + $model->timeout;
            $result = $time_end - strtotime(\gmdate('Y-m-d H:i:s'));
            if($result > 0) {
                return $result;
            } else {
                return 0;
            }
        }
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
