<?php

namespace common\models;

use Yii;
use common\models\Category;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "relation_category".
 *
 * @property integer $id
 * @property integer $relation_supp_rest_id
 * @property integer $category
 */
class RelationCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'relation_category';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['relation_supp_rest_id', 'category'], 'required'],
            [['relation_supp_rest_id', 'category'], 'integer'],
            [['relation_rest_supp_id', 'category'], 'required'],
            [['relation_rest_supp_id', 'category'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'relation_supp_rest_id' => 'Relation Supp Rest ID',
            'relation_rest_supp_id' => 'Relation Rest Supp ID',
            'category' => 'Категория',
        ];
    }
    public static function allCategory() {
		return ArrayHelper::map(Category::find()->all(),'id','name');
	}
}
