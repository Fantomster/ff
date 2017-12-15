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

class TranslationsController extends SmsController
{

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
                        'actions' => ['index', 'ajax-balance', 'message', 'message-update', 'create'],
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

    /**
     * Список сообщений
     */
    public function actionMessage()
    {
        $query = SourceMessage::find()
            ->joinWith('messages')
            ->where(['!=', 'category', 'sms_message'])
            ->orderBy('language ASC')->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query
        ]);

        return $this->render('messages', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $sourceMessage = new SourceMessage();
        $message = new Message();
        if ($sourceMessage->load(Yii::$app->request->post()) && $sourceMessage->save()) {
            $id = $sourceMessage->id;
            $post = Yii::$app->request->post();
            foreach ($post['Message']['translation'] as $lang=>$translation){
                $m = new Message();
                $m->id = $id;
                $m->language = $lang;
                $m->translation = $translation;
                $m->save();
            }
            return $this->redirect(['message']);
        } else {
            return $this->render('create', [
                'sourceMessage' => $sourceMessage,
                'message' => $message,
            ]);
        }
    }

}
