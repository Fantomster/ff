<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer $id
 * @property integer $rest_org_id
 * @property integer $supp_org_id
 * @property integer $cat_id
 * @property integer $invite
 */
class RelationSuppRest extends \yii\db\ActiveRecord
{	
	const INVITE_OFF = 0;
	const INVITE_ON = 1;
    
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
            [['rest_org_id', 'supp_org_id', 'cat_id'], 'required'],
            [['rest_org_id', 'supp_org_id', 'cat_id'], 'integer'],
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
            'supp_org_id' => 'Supp Org ID',
            'cat_id' => 'Cat ID',
        ];
    }
    public static function GetRelationCatalogs()
    {
		$catalog = RelationSuppRest::
		find()
		->select(['id','cat_id','rest_org_id','invite'])
		->where(['supp_org_id' => User::getOrganizationUser(Yii::$app->user->id)])
		->andWhere(['not', ['cat_id' => null]])
		->all();  
		return $catalog;  
    }
    /*public static function getStatusRelation($sup_org_id,$rest_org_id){
	    $catalogName = RelationSuppRest::find()
		->where(['sup_org_id' => $sup_org_id,'rest_org_id'=>$rest_org_id])->one();  
		return $catalogName->status;
    }*/
    
    public function search($params,$currentUser) {
	    
	    $query = RelationSuppRest::find()
	    ->where(['supp_org_id'=>$currentUser->organization_id,'invite'=>RelationSuppRest::INVITE_ON]);
	    $dataProvider = new ActiveDataProvider([
	        'query' => $query,
	    ]);
	    $dataProvider->setSort([
	        'attributes' => [
	            'id',
				'rest_org_id',
				'supp_org_id',
				'cat_id',
				'invite'
	        ]
	    ]);
	 
	    if (!($this->load($params) && $this->validate())) {
	        return $dataProvider;
	    }
	 
	    $this->addCondition($query, 'id');
	    $this->addCondition($query, 'rest_org_id', true);
	    $this->addCondition($query, 'cat_id', true);
	    $this->addCondition($query, 'invite');
	 
	    /* Setup your custom filtering criteria */
	 
	    // filter by person full name
	    /*$query->andWhere('first_name LIKE "%' . $this->fullName . '%" ' .
	        'OR last_name LIKE "%' . $this->fullName . '%"'
	    );*/
	 
	    return $dataProvider;
	}
}
