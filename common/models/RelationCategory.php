<?php

namespace common\models;

use Yii;
//use common\models\Category;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "relation_category".
 *
 * @property integer $id
 * @property integer $supp_org_id
 * @property integer $rest_org_id
 * @property integer $category_id
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
            [['supp_rest_id', 'category_id'], 'required'],
            [['supp_rest_id', 'category_id'], 'integer'],
            [['rest_supp_id', 'category_id'], 'required'],
            [['rest_supp_id', 'category_id'], 'integer'],
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supp_org_id' => 'Relation Supp org ID',
            'rest_org_id' => 'Relation Rest org ID',
            'category_id' => 'Category ID',
        ];
    }
    /*public static function allCategory() {
		return ArrayHelper::map(Category::find()->all(),'id','name');
	}*/
}
