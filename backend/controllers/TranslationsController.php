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

}
