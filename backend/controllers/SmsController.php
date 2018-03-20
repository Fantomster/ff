<?php

namespace backend\controllers;

use common\models\Message;
use common\models\SourceMessage;
use Yii;
use common\models\Role;
use common\models\SmsSendSearch;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Response;

class SmsController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id == 'change-status') {
            $this->enableCsrfValidation = false;
        } else {
            $this->enableCsrfValidation = true;
        }
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['change-status'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'ajax-balance', 'message', 'message-update'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'change-status' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new SmsSendSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Возвращает баланс в ЛК
     */
    public function actionAjaxBalance()
    {
        if(Yii::$app->request->isAjax){

            if(!Yii::$app->cache->get('sms_balance')){
                Yii::$app->cache->set('sms_balance', Yii::$app->sms->getBalance(), 300);
            }

            $balance = Yii::$app->cache->get('sms_balance');

            return \kartik\alert\Alert::widget([
                'options' => [
                    'class' => ($balance > 1000 ? 'alert-info' : 'alert-danger')
                ],
                'body' =>   Html::tag('b', Yii::t('app', 'Баланс СМС')) .
                            ": " .
                            $balance .
                            Html::a(Yii::t('app', 'пополнить'), 'https://go.qtelecom.ru/index.php', [
                                'target' => '_blank',
                                'class' => 'btn btn-success btn-xs',
                                'style' => 'margin-left:20px'
                            ])
            ]);
        }
    }

    /**
     * Список сообщений
     */
    public function actionMessage()
    {
        $query = SourceMessage::find()
            ->joinWith('messages')
            ->where(['category' => 'sms_message'])->orderBy('language ASC')->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query
        ]);

        return $this->render('messages', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Обновление перевода
     * @param $id
     * @return array
     */
    public function actionMessageUpdate($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params = [];
            $params['id'] = $id;
            $params['language'] = Yii::$app->request->post('language');
            $translation = Yii::$app->request->post('translation');
            $model = Message::find()->where($params)->one();
            if($model) {
                $model->translation = $translation;
                return ['success' => $model->save()];
            } else {
                return ['success' => false];
            }
        }
    }
}
