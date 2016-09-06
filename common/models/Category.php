<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */	
    public static function tableName()
    {
        return 'category';
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
    public static function allCategory() {
		return ArrayHelper::map(Category::find()->all(),'id','name');
	}
}
