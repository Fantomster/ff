<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer $id
 * @property integer $rest_org_id
 * @property integer $sup_org_id
 * @property integer $cat_id
 */
class RelationSuppRest extends \yii\db\ActiveRecord
{	
	const INVITE_OFF = 0;
	
    const INVITE_ON = 1;
    
    const STATUS_OFF = 0;
	
    const STATUS_ON = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'relation_supp_rest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rest_org_id', 'sup_org_id', 'cat_id'], 'required'],
            [['rest_org_id', 'sup_org_id', 'cat_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rest_org_id' => 'Rest Org ID',
            'sup_org_id' => 'Sup Org ID',
            'cat_id' => 'Cat ID',
        ];
    }
    public static function GetCatalogs()
    {
		$catalog = RelationSuppRest::
		find()
		->select(['id','cat_id','rest_org_id','status'])
		->where(['sup_org_id' => User::getOrganizationUser(Yii::$app->user->id)])->all();  
		return $catalog;  
    }
    public static function getStatusRelation($sup_org_id,$rest_org_id){
	    $catalogName = RelationSuppRest::find()
		->where(['sup_org_id' => $sup_org_id,'rest_org_id'=>$rest_org_id])->one();  
		return $catalogName->status;
    }
}
