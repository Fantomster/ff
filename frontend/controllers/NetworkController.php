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
                            'ajax-change-organization',
                            'change-organization'
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
    public function actionAjaxChangeOrganization(){
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
    public function actionChangeOrganization($id){
        $user = User::findIdentity(Yii::$app->user->id);
        $organization = Organization::findOne(['id'=>$id]);
        if($organization->type_id == Organization::TYPE_RESTAURANT){
        $user->role_id = Role::ROLE_RESTAURANT_MANAGER;   
        }
        if($organization->type_id == Organization::TYPE_SUPPLIER){
        $user->role_id = Role::ROLE_SUPPLIER_MANAGER;   
        }
        $user->organization_id = $id;
        $user->save();
        return true;
    }
}