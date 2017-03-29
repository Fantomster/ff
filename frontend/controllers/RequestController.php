<?php

namespace frontend\controllers;

use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\models\Request;
use common\models\RequestCallback;
use common\models\RequestCounters;
use yii\web\Response;
use yii\widgets\ActiveForm;
/**
 * Description of RequestController
 * 
 * @author kz
 */

class RequestController extends DefaultController {
    
    public function actionCreate() {
        if (Yii::$app->request->isAjax) {
            $currentUser = $this->currentUser;
            if($currentUser->organization->type_id != Organization::TYPE_RESTAURANT){
               return false; 
            }
            $request = new \common\models\Request();
            return $this->renderAjax("create", compact('request'));
            }else{
                return $this->redirect(['list']);
            }
    }
    public function actionSaveRequest() {
        $currentUser = $this->currentUser;
        if($currentUser->organization->type_id != Organization::TYPE_RESTAURANT){
           return false; 
        }
        $request = new \common\models\Request();
        $request->rest_org_id = $currentUser->organization_id;
        if (Yii::$app->request->isAjax && $request->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $validForm = ActiveForm::validate($request);
            if($validForm){
                return $validForm;
            }else{
             if(Yii::$app->request->post('step')==3){
                    $request->save();  
                    return ['saved'=>true];   
                }else{
                    return $validForm;
                }
            }
        }
    }
    public function actionList() {
        $organization = $this->currentUser->organization;
        if($organization->type_id == Organization::TYPE_RESTAURANT){
            $search = ['like','product',\Yii::$app->request->get('search')?:''];
            $dataListRequest = new ActiveDataProvider([
                'query' => Request::find()->where(['rest_org_id' => $organization->id])->andWhere($search)->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial("list", compact('dataListRequest','search','countComments'));
            }else{
                return $this->render("list", compact('dataListRequest','search','countComments'));
            }    
        }
    }
    
    public function actionView($id) {
        if(!Request::find()->where(['id' => $id])->exists()){
           return $this->redirect("list"); 
        }
        $user = $this->currentUser;
        
        $request = Request::find()->where(['id' => $id])->one();
        $author = Organization::findOne(['id'=>$request->rest_org_id]);
        $countComments = RequestCallback::find()->where(['request_id' => $id])->count();
        
        if($user->organization->type_id == Organization::TYPE_SUPPLIER){
            if(!RequestCounters::find()->where(['request_id' => $id, 'user_id'=>$user->id])->exists()){
                $requestCounters = new RequestCounters();
                $requestCounters->request_id = $id;
                $requestCounters->user_id = $user->id;
                $requestCounters->save();
            }  
        }
        return $this->render("view", compact('request','countComments','author'));
    }
}
