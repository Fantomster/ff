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
        $organization = new Organization();
        $sql = "
        select distinct id as `id`,`name`,`type_id` from (
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `parent_id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `id` = " . $user->organization_id . "
        union all
        select `parent_id`,
        (select `name` from `organization` where `id` = o.`parent_id`) as `name`, 
        (select `type_id` from `organization` where `id` = o.`parent_id`) as `type_id`
        from `organization` o where id = " . $user->organization_id . "
        )tb where id is not null";
        $sql2 = "
        select count(*) from (
        select distinct id as `id`,`name`,`type_id` from (
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `parent_id` = (select `parent_id` from `organization` where `id` = " . $user->organization_id . ")
        union all
        select id,`name`,`type_id` from `organization` where `id` = " . $user->organization_id . "
        union all
        select `parent_id`,
        (select `name` from `organization` where `id` = o.`parent_id`) as `name`, 
        (select `type_id` from `organization` where `id` = o.`parent_id`) as `type_id`
        from `organization` o where id = " . $user->organization_id . "
        )tb where id is not null)tb2";
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => \Yii::$app->db->createCommand($sql)->sql,
            'totalCount' => \Yii::$app->db->createCommand($sql2)->queryScalar(),
            'pagination' => [
                'pageSize' => 4,
            ],
        ]);
        return $this->renderAjax('_changeForm', compact('user','dataProvider','organization'));
    }
    public function actionChange($id){
        $user = User::findIdentity(Yii::$app->user->id);
        $organization = Organization::findOne(['id'=>$id]);
        
        $sql = "
        select distinct id as `id`,`name` from (
        select id,`name` from organization where parent_id = (select id from organization where id = " . $user->organization_id . ")
        union all
        select id,`name` from organization where parent_id = (select parent_id from organization where id = " . $user->organization_id . ")
        union all
        select id,`name` from organization where id = " . $user->organization_id . "
        union all
        select parent_id,(select `name` from organization where id = o.parent_id) as name from organization o where id = " . $user->organization_id . "
        )tb where id = " . $id;
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
    
    public function actionCreate(){
        $user = User::findIdentity(Yii::$app->user->id);
        $sql = "select distinct parent_id as `parent_id` from (
        select id, parent_id from organization where parent_id = (select parent_id from organization where id = " . $user->organization_id . ")
        union all
        select id, parent_id from organization where id = " . $user->organization_id . ")tb";
        if(!empty(Organization::findBySql($sql)->one()->parent_id)){
          $parent_id = Organization::findBySql($sql)->one()->parent_id;   
        }else{
          $parent_id = $user->organization_id; 
        }
        $organization = new Organization();
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            if ($organization->load($post)) {
            $organization->parent_id = $parent_id;
            $organization->save();
            }
        }
    }
}