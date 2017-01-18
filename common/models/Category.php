<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * @property integer $id
 * @property string $name
 */

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
    public static function get_value($id){
        $model = Category::find()->where(["id" => $id])->one();
        if(!empty($model)){
            return $model;
        }
        return null;
    }
}
