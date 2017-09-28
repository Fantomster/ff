<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\RelationSuppRest;
use yii\data\ActiveDataProvider;
use api\modules\v1\modules\mobile\models\User;
use common\models\forms\LoginForm;
use common\models\Organization;
use common\models\RequestCallback;
use common\models\Request;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RelationSuppRestController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\RelationSuppRest';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

       $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = RelationSuppRest::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
    
    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $params = new RelationSuppRest();
        $query = RelationSuppRest::find();
        
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
        ));
        $filters = [];
        $user = Yii::$app->user->getIdentity();
        
        $filters['rest_org_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) ? $user->organization_id : $params->rest_org_id;
        $filters['supp_org_id'] = ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) ? $user->organization_id : $params->supp_org_id;
        $filters['deleted'] = 0;  
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $query->andFilterWhere($filters);
            return $dataProvider;
        }
  
       
            $filters['id'] = $params->id; 
            $filters['cat_id'] = $params->cat_id; 
            $filters['invite'] = $params->invite; 
            $filters['created_at'] = $params->created_at; 
            $filters['updated_at'] = $params->updated_at; 
            $filters['status'] = $params->status; 
            $filters['uploaded_catalog'] = $params->uploaded_catalog; 
            $filters['uploadded_processed'] = $params->uploaded_catalog;
            $filters['is_from_market'] = $params->is_from_market;
            
            $query->andFilterWhere($filters);
  
        return $dataProvider;
    }

    public function actionCreate()
    {
            $client = Yii::$app->user->identity;
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
                
                $vendorUsers = User::find()->where(['organization_id' => $vendor->id])->all();
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
                
                return compact("relationSuppRest");
            }
    }
}
