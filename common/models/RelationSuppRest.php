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
 * @property string $created_at
 * @property string $updated_at
 */
class RelationSuppRest extends \yii\db\ActiveRecord
{	
        const PAGE_CLIENTS = 3;
        const PAGE_CATALOG = 2;
        
        const PAGE_SUPPLIERS = 1;
        
        const CATALOG_STATUS_OFF = 0;
        const CATALOG_STATUS_ON = 1;

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
    
    public function search($params,$currentUser,$const) {
	    if($const==RelationSuppRest::PAGE_CLIENTS){ 
             $query = RelationSuppRest::find()
            ->where(['supp_org_id'=>$currentUser->organization_id]); 
            }
            if($const==RelationSuppRest::PAGE_SUPPLIERS){ 
             $query = RelationSuppRest::find()
            ->where(['rest_org_id'=>$currentUser->organization_id]); 
            }
	    if($const==RelationSuppRest::PAGE_CATALOG){
             $query = RelationSuppRest::find()
            ->where(['supp_org_id'=>$currentUser->organization_id])
            ->andWhere(['invite'=>RelationSuppRest::INVITE_ON]);  
            }
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
            return $dataProvider;
	}
    public static function row_count($id){
        $count = RelationSuppRest::find()
    ->where(['cat_id' => $id])
    ->count();
        return $count;
    }
}
