<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;
use common\models\Profile;
use common\models\Organization;
use common\models\NetworkOrganization;
use common\models\Role;
use common\components\AccessRule;
use yii\web\HttpException;
use yii\helpers\Url;
use yii\web\Response;
use yii\data\ActiveDataProvider;

/**
 * Site controller
 */
class NetworkController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => [
                            'change-form',
                            'change',
                            'create-form',
                            'create'
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions' => [
                            'change-form',
                            'change',
                            'create-form',
                            'create'
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
//                'denyCallback' => function($rule, $action) {
//                    throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
//                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    public function actionChangeForm(){
        $user = User::findIdentity(Yii::$app->user->id);
        if(Organization::find()->where(['id' => $user->organization_id])
                ->andWhere('parent_id is not null')
                ->exists()){
            $parent = Organization::find()->where(['id' => $user->organization_id])->one()->parent_id;
            
            $networks1 = Organization::find()->where(['parent_id' => $parent]);
            
            $networks2 = Organization::find()->where(['id' => $parent]);
            
            $networks1->union($networks2, false);
            
            $sql = $networks1->createCommand()->getRawSql();
            
            $networks = Organization::findBySql($sql);
            
        }elseif(Organization::find()->where(['parent_id' => $user->organization_id])->exists()){
            $networks = Organization::find()->where(['parent_id' => $user->organization_id]);  
        }else{
        $networks = Organization::find()->where(['id' => $user->organization_id]);    
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $networks,
        ]);
        return $this->renderAjax('_changeForm', compact('user','dataProvider'));
    }
    public function actionChange($id){
        $user = User::findIdentity(Yii::$app->user->id);
        $organization = Organization::findOne(['id'=>$id]);
        
        $sql = "select distinct count(id) from (
        select id from organization where parent_id = (select parent_id from organization where id = " . $user->organization_id . ")
        union all
        select id from organization where id = " . $user->organization_id . ")tb where id = $id";
        if(\Yii::$app->db->createCommand($sql)->queryScalar() && 
                ($user->role_id == Role::ROLE_RESTAURANT_MANAGER || 
                 $user->role_id == Role::ROLE_SUPPLIER_MANAGER || 
                 $user->role_id == Role::ROLE_ADMIN ||
                 $user->role_id == Role::ROLE_FKEEPER_MANAGER)){
            if($organization->type_id == Organization::TYPE_RESTAURANT && 
                    ($user->role_id != Role::ROLE_ADMIN &&
                     $user->role_id != Role::ROLE_FKEEPER_MANAGER)){
                
                $user->role_id = Role::ROLE_RESTAURANT_MANAGER;   
            }
            if($organization->type_id == Organization::TYPE_SUPPLIER && 
                    ($user->role_id != Role::ROLE_ADMIN &&
                     $user->role_id != Role::ROLE_FKEEPER_MANAGER)){
                $user->role_id = Role::ROLE_SUPPLIER_MANAGER;   
            }
            $user->organization_id = $id;
            $user->save();
            return true;
        }
        return false;
    }
    
    public function actionCreateForm(){
        $organization = new Organization();
        return $this->renderAjax('_createForm', compact('organization'));
    }
    
    public function actionCreate(){
        $user = User::findIdentity(Yii::$app->user->id);
        $sql = "select distinct parent_id from (
        select id, parent_id from organization where parent_id = (select parent_id from organization where id = " . $user->organization_id . ")
        union all
        select id, parent_id from organization where id = " . $user->organization_id . ")tb";
        $organization = new Organization();
        if(\Yii::$app->db->createCommand($sql)->queryScalar()){
          $parent_id = \Yii::$app->db->createCommand($sql)->queryAll();  
          
          
        }else{
          $parent_id = $user->organization_id; 
         
        }
    }
}