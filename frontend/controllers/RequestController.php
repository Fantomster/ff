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
        $search = ['like','product',\Yii::$app->request->get('search')?:''];
        $category = \Yii::$app->request->get('category')?['category' => \Yii::$app->request->get('category')]:[];
        if($organization->type_id == Organization::TYPE_RESTAURANT){
            $dataListRequest = new ActiveDataProvider([
                'query' => Request::find()->where(['rest_org_id' => $organization->id])->andWhere($search)->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial("list-client", compact('dataListRequest','organization'));
            }else{
                return $this->render("list-client", compact('dataListRequest','organization'));
            }    
        }
        if($organization->type_id == Organization::TYPE_SUPPLIER){
            $myOnly = [];
            if(\Yii::$app->request->get('myOnly')=='true'){
            $myOnly = ['responsible_supp_org_id' => $organization->id];
            }
            $dataListRequest = new ActiveDataProvider([
                'query' => Request::find()->where(['active_status' => Request::ACTIVE])
                    ->andWhere($search)
                    ->andWhere($myOnly)
                    ->andWhere($category)
                    ->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial("list-vendor", compact('dataListRequest','organization'));
            }else{
                return $this->render("list-vendor", compact('dataListRequest','organization'));
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
        
        
        if($user->organization->type_id == Organization::TYPE_RESTAURANT){
            $countComments = RequestCallback::find()->where(['request_id' => $id])->count();
            $dataCallback = new ActiveDataProvider([
                'query' => RequestCallback::find()->where(['request_id' => $id])->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            return $this->render("view-client", compact('request','countComments','author','dataCallback'));
        }
        if($user->organization->type_id == Organization::TYPE_SUPPLIER){
            if(!RequestCounters::find()->where(['request_id' => $id, 'user_id'=>$user->id])->exists()){
                $requestCounters = new RequestCounters();
                $requestCounters->request_id = $id;
                $requestCounters->user_id = $user->id;
                $requestCounters->save();
            }  
            $trueFalseCallback = RequestCallback::find()->where(['request_id' => $id,'supp_org_id'=>$user->organization_id])->exists();
            $dataCallback = new ActiveDataProvider([
                'query' => RequestCallback::find()->where(['request_id' => $id])->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            return $this->render("view-vendor", compact('request','countComments','author','dataCallback','trueFalseCallback'));
        }
    }
    public function actionSetResponsible(){
        $userOrg = $this->currentUser->organization_id;
        
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON; 
        
        $id = Yii::$app->request->post('id');
        $responsible_id = Yii::$app->request->post('responsible_id');
        
        if(!Request::find()->where(['rest_org_id' => $userOrg,'id'=>$id])->exists()){
            return ['success'=>false];
        }
        if(!RequestCallback::find()->where([
            'request_id' => $id, 
            'supp_org_id'=>$responsible_id
            ])->exists()){
            return ['success'=>false];
        }
        $request = Request::find()->where(['id' => $id])->one();
        if($request->responsible_supp_org_id == $responsible_id){
           $request->responsible_supp_org_id = null; 
        }else{
            $request->responsible_supp_org_id = $responsible_id;
        }
        $request->save();
        return ['success'=>true];
        }
    }
    public function actionCloseRequest(){
        $user = $this->currentUser;
        
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON; 
        
        $id = Yii::$app->request->post('id');
        if(!Request::find()->where(['rest_org_id' => $user->organization_id,'id'=>$id])->exists()){
            return ['success'=>false];
        }
        $request = Request::find()->where(['id' => $id])->one();
        $request->active_status = Request::INACTIVE;
        $request->save();
        return ['success'=>true];
        }
    }
    public function actionAddCallback(){
        $user = $this->currentUser;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON; 
        $id = Yii::$app->request->post('id');
        $price = Yii::$app->request->post('price');
        $comment = Yii::$app->request->post('comment');
        $requestCallback = new RequestCallback();
        $requestCallback->request_id = $id;
        $requestCallback->supp_org_id = $user->organization_id;
        $requestCallback->price = $price;
        $requestCallback->comment = $comment;
        $requestCallback->save();
        return ['success'=>true];
        }
    }
}
