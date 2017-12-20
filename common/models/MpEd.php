<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mp_ed".
 *
 * @property integer $id
 * @property string $name
 */
class MpEd extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mp_ed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    public static function dropdown() {
        $list = \yii\helpers\ArrayHelper::getColumn(self::find()->all(), 'name');
        $list = array_map(function($item) { return Yii::t('app', $item); }, $list);
        return $list;
    }
}
