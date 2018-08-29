<?php

namespace console\controllers;

use api\common\models\RabbitQueues;
use common\models\vetis\VetisPurpose;
use common\models\vetis\VetisUnit;
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
        echo "GET UNITS" . PHP_EOL;
        VetisUnit::getUpdateData();
        echo "GET PURPOSE" . PHP_EOL;
        VetisPurpose::getUpdateData();
        echo "FINISH" . PHP_EOL;
    }
}
