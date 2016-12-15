<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "relation_category".
 *
 * @property integer $id
 * @property integer $supp_org_id
 * @property integer $rest_org_id
 * @property integer $category_id
 * @property string $created_at
 * 
 * @property Category $category
 * @property Organization $vendor
 * @property Organization $client
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
            [['supp_rest_id'], 'required'],
            [['supp_rest_id', 'category_id'], 'integer'],
            [['rest_supp_id'], 'required'],
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
            'category_id' => 'Категория',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory() {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor() {
        return $this->hasOne(Organization::className(), ['id' => 'supp_org_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient() {
        return $this->hasOne(Organization::className(), ['id' => 'rest_org_id']);
    }
}
