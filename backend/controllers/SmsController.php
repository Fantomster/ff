<?php

namespace backend\controllers;

use common\models\SmsSend;
use common\models\SmsStatus;
use Yii;
use common\models\Role;
use common\models\SmsSendSearch;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\filters\VerbFilter;

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
                        'actions' => ['index'],
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
     * Смена статуса СМС сообщения, принимает параметры от сервера провайдера Qtelecom
     * POST [
     *    ORDID = SmsSend->sms_id
     * ]
     */
    public function actionChangeStatus()
    {
        //Проверяем, получили мы ID смс или нет
        if ($sms_id = Yii::$app->request->post('ORDID')) {
            //Отправляем запрос провайдеру об этой смс
            //Если сервер знает эту смс едем дальше
            if ($statusModel = Yii::$app->sms->checkStatus($sms_id)) {
                //ищем эту смс у нас
                $model = SmsSend::findOne(['sms_id' => $sms_id]);
                //Если нашли, идем обновлять ее статус
                if (!empty($model)) {
                    $model->setAttribute('status_id', $statusModel->status);
                    if ($model->validate()) {
                        $model->save();
                    }
                }
            }
        }
    }
}
