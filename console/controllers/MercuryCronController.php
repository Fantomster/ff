<?php

namespace console\controllers;

use api\common\models\RabbitQueues;
use console\modules\daemons\classes\MercUnitList;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\ListOptions;
use yii\console\Controller;
use api\common\models\merc\mercService;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use Yii;

class MercuryCronController extends Controller
{

    /**
     * Автоматическая загрузка списка ВСД для всех пользователей (за прошедшие сутки)
     * @param $interval - период за который нужно выгрузить ВСД в часах
     */
    public function actionVetDocumentsChangeList($interval = 24)
    {
        $organizations = (new \yii\db\Query)
            ->select('org')
            ->from(mercService::tableName())
            ->where('status_id = 1 and now() between fd and td')
            ->createCommand(Yii::$app->db_api)
            ->queryColumn();

        foreach ($organizations as $org_id) {
            try {
                $locations = cerberApi::getInstance($org_id)->getActivityLocationList();

                if (!isset($locations->activityLocationList->location)) {
                    continue;
                }

                foreach ($locations->activityLocationList->location as $item) {
                    if (!isset($item->enterprise)) {
                        continue;
                    }
                    $request = [
                        'enterpriseGuid' => $item->enterprise->guid,
                        'orgId' => $org_id,
                        'intervalHours' => $interval,
                    ];

                    try {
                        \Yii::$app->get('rabbit')
                            ->setQueue('merc_load_vsd')
                            ->addRabbitQueue(\json_encode($request));
                    } catch (\Exception $e) {
                        Yii::error($e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Yii::error($e->getMessage());
            }
        }
    }

    public function actionTest()
    {
        echo "START" . PHP_EOL;
        try {
            $instance = dictsApi::getInstance(5144);
            $instance->setMode(dictsApi::GET_UPDATES_DICTS);
            $queue = RabbitQueues::find()->where(['consumer_class_name' => 'MercUnitList'])->orderBy(['last_executed' => SORT_DESC])->one();
            $data['method'] = ($queue === null) ? 'getUnitList' : 'getUnitChangesList';
            $data['struct'] = ['listName' => 'unitList',
                'listItemName' => 'unit'
            ];

            $listOptions = new ListOptions();
            $listOptions->count = 100;
            $listOptions->offset = 0;

            $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => ($queue === null) ? null : $queue->last_executed]));

            \Yii::$app->get('rabbit')
                ->setQueue('MercUnitList_5144')
                ->addRabbitQueue(json_encode($data));

           /*$t = new MercUnitList(5144);
            $t->data = json_encode($data);
            json_decode(json_encode($data), true);
            $t->getData();
            echo ($t->saveData()).PHP_EOL;*/

        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            echo $e->getMessage().PHP_EOL;
        }

        echo "FINISH" . PHP_EOL;
    }
}
