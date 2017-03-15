<?php

namespace frontend\controllers;

use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\Organization;
/**
 * Description of RequestController
 * 
 * @author kz
 */

class RequestController extends DefaultController {
    
    public function actionCreate() {
        $profile = $this->currentUser->profile;
        return $this->render("create", compact('profile'));
    }
    public function actionList() {
        $organization = $this->currentUser->organization;
        if($organization->type_id == Organization::TYPE_RESTAURANT){
            
        }
        if($organization->type_id == Organization::TYPE_SUPPLIER){
            
        }
        $search = ['like','name',\Yii::$app->request->get('search')?:''];
        $dataListRequest = new ActiveDataProvider([
            'query' => Organization::find()->where(['type_id' => 1])->andWhere($search)->orderBy('id DESC'),
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        return $this->render("list", compact('dataListRequest','search'));
    }
    public function actionView() {
        $profile = $this->currentUser->profile;
        return $this->render("view", compact('profile'));
    }
}