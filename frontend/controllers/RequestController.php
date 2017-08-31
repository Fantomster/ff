<?php

namespace frontend\controllers;

use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use common\models\Request;
use common\models\Role;
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
                            'list', 
                            'view',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions' => [
                            'close-request',
                            'save-request',
                            'set-responsible',
                            'add-supplier',
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
                    [
                        'actions' => [
                            'add-callback',
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
                ],
            ],
        ];
    }
    public function actionSaveRequest() {
        $currentUser = $this->currentUser;
        if($currentUser->organization->type_id != Organization::TYPE_RESTAURANT){
           return false; 
        }
        $request = new Request();
        $organization = $currentUser->organization;
        $profile = $currentUser->profile;
        $request->rest_org_id = $currentUser->organization_id;
        if (Yii::$app->request->isAjax && $request->load(Yii::$app->request->post()) && 
                $organization->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $validForm = ActiveForm::validate($request);
            $validFormOrg = ActiveForm::validate($organization);
            if(empty($organization->lat) || 
                empty($organization->lng) || 
                empty($organization->place_id) || 
                empty($organization->country)){
                return ['organization-address' => false];     
            }
            
            if($validForm){
                return $validForm;
            }else{
             if(Yii::$app->request->post('step')==3){
                if ($request->validate() && $organization->validate()) {
                    $organization->city = $organization->locality;
                    $organization->save();
                    $request->save(); 
                    return ['saved'=>true];
                } else {
                    return ['error'=>['organization'=>$organization->errors,'request'=>$request->errors]];
                } 
              }else{
                return $validForm;
              }
            }
        }
    }
    public function actionList() {
        $organization = $this->currentUser->organization;
        $profile = $this->currentUser->profile;
        $search = ['like','product',\Yii::$app->request->get('search')?:''];
        $category = \Yii::$app->request->get('category')?['category' => \Yii::$app->request->get('category')]:[];
        
        if($organization->type_id == Organization::TYPE_RESTAURANT){
            $dataListRequest = new ActiveDataProvider([
                'query' => Request::find()->where(['rest_org_id' => $organization->id])->andWhere($search)->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 15,
                ],
            ]);
            if (Yii::$app->request->isPjax) {
                return $this->renderPartial("list-client", compact('dataListRequest','organization','profile'));
            }else{
                return $this->render("list-client", compact('dataListRequest','organization','profile'));
            }    
        }
        if($organization->type_id == Organization::TYPE_SUPPLIER){
            $my = \Yii::$app->request->get('myOnly')==2?['responsible_supp_org_id' => $organization->id]:[];
            $rush = \Yii::$app->request->get('rush')==2?['rush_order' => 1]:[];
            $dataListRequest = new ActiveDataProvider([
                'query' => Request::find()->where(['active_status' => Request::ACTIVE])
                    ->andWhere(['>=', 'end', new \yii\db\Expression('NOW()')])
                    ->andWhere($search)
                    ->andWhere($category)
                    ->andWhere($my)
                    ->andWhere($rush)
                    //->andWhere('responsible_supp_org_id is null or responsible_suspp_org_id = ' . $organization->id)
                    ->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 15,
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
                    'pageSize' => 15,
                ],
            ]);
            //var_dump($author);
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
                'query' => RequestCallback::find()->where(['request_id' => $id,'supp_org_id'=>$user->organization_id])->orderBy('id DESC'),
                'pagination' => [
                    'pageSize' => 15,
                ],
            ]);
            return $this->render("view-vendor", compact('request','countComments','author','dataCallback','trueFalseCallback'));
        }
    }
    public function actionSetResponsible(){
        $client = $this->currentUser;
        
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON; 
        
        $id = Yii::$app->request->post('id');
        $responsible_id = Yii::$app->request->post('responsible_id');
        
        if(!Request::find()->where(['rest_org_id' => $client->organization_id,'id'=>$id])->exists()){
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
            $request->save();
            //Отправка СМС
            $rows = \common\models\User::find()->where(['organization_id' => $responsible_id])->all();
            foreach($rows as $row){
                if($row->profile->phone && $row->profile->sms_allow){
                    $text = 'Вы больше не исполнитель по заявке №' . $id . ' в системе f-keeper.ru';
                    $target = $row->profile->phone;
                    $sms = new \common\components\QTSMS();
                    $sms->post_message($text, $target); 
                }
            }
        }else{
            $request->responsible_supp_org_id = $responsible_id;
            $request->save();
            $vendors = \common\models\User::find()->where(['organization_id' => $request->responsible_supp_org_id])->all();
            //Отправка почты
            if($client->email){
                $mailer = Yii::$app->mailer; 
                $email = $client->email;
                //$email = 'marshal1209448@gmail.com';
                $subject = "f-keeper.ru - заявка №" . $request->id;
                $mailer->htmlLayout = 'layouts/request';
                $result = $mailer->compose('requestSetResponsible', compact("request","client"))
                        ->setTo($email)->setSubject($subject)->send();
            }
            //Отправка СМС
            foreach($vendors as $vendor){
                if($vendor->profile->phone && $vendor->profile->sms_allow){
                    $text = 'Вы назначены исполнителем по заявке №' . $id . ' в системе f-keeper.ru';
                    $target = $vendor->profile->phone;
                    $sms = new \common\components\QTSMS();
                    $sms->post_message($text, $target); 
                }
                if($vendor->email){
                $mailer = Yii::$app->mailer; 
                $email = $vendor->email;
                //$email = 'marshal1209448@gmail.com';
                $subject = "f-keeper.ru - заявка №" . $request->id;
                $mailer->htmlLayout = 'layouts/request';
                $result = $mailer->compose('requestSetResponsibleMailToSupp', compact("request","vendor"))
                        ->setTo($email)->setSubject($subject)->send();
                }
            }
        }
        
        
        return ['success'=>true];
        }
    }
    public function actionAddSupplier(){
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $client = $this->currentUser;
            $request_id = Yii::$app->request->post('request_id');
            $vendor = Organization::findOne(['id'=>Yii::$app->request->post('supp_org_id')]);
            if(RequestCallback::find()->where(['supp_org_id'=>$vendor->id,'request_id'=>$request_id])->exists()){
                if(\common\models\RelationSuppRest::find()->where([
                                'rest_org_id' => $client->organization_id, 
                                'supp_org_id' => $vendor->id
                                ])->exists()){
                $relationSuppRest = \common\models\RelationSuppRest::find()->where([
                                'rest_org_id' => $client->organization_id, 
                                'supp_org_id' => $vendor->id
                                ])->one(); 
                }else{
                $relationSuppRest = new \common\models\RelationSuppRest();   
                }
                $relationSuppRest->deleted = false;
                $relationSuppRest->rest_org_id = $client->organization_id;
                $relationSuppRest->supp_org_id = $vendor->id;
                $relationSuppRest->invite = \common\models\RelationSuppRest::INVITE_OFF;
                $relationSuppRest->save(); 
                $request = Request::findOne(['id'=>$request_id]);
                
                $vendorUsers = \common\models\User::find()->where(['organization_id' => $vendor->id])->all();
                if($client->email){
                $mailer = Yii::$app->mailer; 
                $email = $client->email;
                //$email = 'marshal1209448@gmail.com';
                $subject = "f-keeper.ru - заявка №" . $request->id;
                $mailer->htmlLayout = 'layouts/request';
                $result = $mailer->compose('requestInviteSupplierMailToRest', compact("request","client"))
                        ->setTo($email)->setSubject($subject)->send();
                }
                foreach($vendorUsers as $user){
                    if($user->profile->phone && $user->profile->sms_allow){
                        $text = $client->organization->name . ' хочет работать с Вами в системе f-keeper.ru';
                        $target = $user->profile->phone;
                        $sms = new \common\components\QTSMS();
                        $sms->post_message($text, $target); 
                    }
                    //$this->sendMail("invite-supplier", $request, $row);
                    if(!empty($user->email)){
                    $mailer = Yii::$app->mailer;
                    $email = $user->email; 
                    //$email = 'marshal1209448@gmail.com';
                    $subject = "f-keeper.ru - заявка №" . $request->id;
                    $mailer->htmlLayout = 'layouts/request';
                    $result = $mailer->compose('requestInviteSupplier', compact("request","user"))
                            ->setTo($email)->setSubject($subject)->send();
                    }
                }
                
                return ['success'=>true];
            }
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
        $vendor = $this->currentUser;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON; 
        $id = Yii::$app->request->post('id');
        $price = Yii::$app->request->post('price');
        $comment = Yii::$app->request->post('comment');
        $requestCallback = new RequestCallback();
        $requestCallback->request_id = $id;
        $requestCallback->supp_org_id = $vendor->organization_id;
        $requestCallback->price = $price;
        $requestCallback->comment = $comment;
        $requestCallback->save();
        $request = Request::find()->where(['id' => $id])->one();
        $clients = \common\models\User::find()->where(['organization_id'=>$request->rest_org_id])->all();
        foreach($clients as $client){
            if($client->profile->phone && $client->profile->sms_allow){
                        $text = "Новый отклик по Вашей заявке №" . $request->id . " от поставщика " . $vendor->organization->name;
                        $target = $client->profile->phone;
                        $sms = new \common\components\QTSMS();
                        $sms->post_message($text, $target); 
            }
            if($client->email){
            $mailer = Yii::$app->mailer; 
            $email = $client->email;
            //$email = 'marshal1209448@gmail.com';
            $subject = "f-keeper.ru - заявка №" . $request->id;
            $mailer->htmlLayout = 'layouts/request';
            $result = $mailer->compose('requestNewCallback', compact("request","client","vendor"))
                    ->setTo($email)->setSubject($subject)->send();
            }
        }
        return ['success'=>true];
        }
    }
}
