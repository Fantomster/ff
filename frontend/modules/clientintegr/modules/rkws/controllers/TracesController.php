<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\AllMaps;
use api\common\models\RkTasksSearch;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use Yii;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkWaybilldata;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;
use common\models\Order;
use yii\helpers\Url;
use frontend\modules\clientintegr\modules\rkws\components\FullmapHelper;
use api\common\models\RkTasks;


// use yii\mongosoft\soapserver\Action;

/**
 * Description of FullmapController
 * Controls all the actions of pre-mapping by goods catalog service
 * Author: R.Smirnov
 */

class TracesController extends \frontend\modules\clientintegr\controllers\DefaultController {



    public function actionIndex() {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $records =  RkTasks::find()->andWhere('org_id = :org',["org" => $organization]);

        $dataProvider = new ActiveDataProvider(['query' => $records,
            'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

    }


}
