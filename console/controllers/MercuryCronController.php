<?php

namespace console\controllers;

use api\common\models\merc\mercPconst;
use api\common\models\merc\MercVsd;
use api\common\models\RabbitQueues;
use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisCountry;
use common\models\vetis\VetisForeignEnterprise;
use common\models\vetis\VetisProductByType;
use common\models\vetis\VetisProductItem;
use common\models\vetis\VetisPurpose;
use common\models\vetis\VetisRussianEnterprise;
use common\models\vetis\VetisSubproductByProduct;
use common\models\vetis\VetisUnit;
use console\modules\daemons\classes\MercBusinessEntityList;
use console\modules\daemons\classes\MercForeignEnterpriseList;
use console\modules\daemons\classes\MercProductItemList;
use console\modules\daemons\classes\MercProductList;
use console\modules\daemons\classes\MercRussianEnterpriseList;
use console\modules\daemons\classes\MercStoreEntryList;
use console\modules\daemons\classes\MercSubProductList;
use console\modules\daemons\classes\MercSubProductListList;
use console\modules\daemons\classes\MercUnitList;
use console\modules\daemons\classes\MercVSDList;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\Cerber;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\ListOptions;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\productApi;
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
        $org_id = (mercPconst::findOne('1'))->org;
        echo "START" . PHP_EOL;
       /* echo "GET Unit" . PHP_EOL;
        VetisUnit::getUpdateData($org_id);
        echo "GET Purpose" . PHP_EOL;
        VetisPurpose::getUpdateData($org_id);

        echo "GET Country" . PHP_EOL;
        VetisCountry::getUpdateData($org_id);*/

        echo "GET RussianEnterprise" . PHP_EOL;
        VetisRussianEnterprise::getUpdateData($org_id);
       /* echo "GET ForeignEnterprise" . PHP_EOL;
        VetisForeignEnterprise::getUpdateData($org_id);
        echo "GET BusinessEntity" . PHP_EOL;
        VetisBusinessEntity::getUpdateData($org_id);

        echo "GET ProductByType" . PHP_EOL;
        VetisProductByType::getUpdateData($org_id);
        echo "GET ProductItem" . PHP_EOL;
        VetisProductItem::getUpdateData($org_id);
        echo "GET SubproductByProduct" . PHP_EOL;
        VetisSubproductByProduct::getUpdateData($org_id);

        echo "GET MercVSDList";
        MercVsd::getUpdateData($org_id);

        echo "GET MercVSDList";
        MercStoreEntryList::getUpdateData($org_id);*/
        echo "FINISH" . PHP_EOL;
    }

    public function actionTest2()
    {
        echo "START" . PHP_EOL;
        $load = new Cerber();

        $org_id = (mercPconst::findOne('1'))->org;
        $queue = null;
        echo "START" . PHP_EOL;
        //Формируем данные для запроса
        $data['method'] = 'getRussianEnterpriseChangesList';
        $data['struct'] = ['listName' => 'enterpriseList',
            'listItemName' => 'enterprise'
            ];

        $listOptions = new ListOptions();
        $listOptions->count = 100;
        $listOptions->offset = 0;

        $startDate =  ($queue === null) ?  date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)): $queue->last_executed;
        $instance = productApi::getInstance($org_id);
        $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

        $w = new MercRussianEnterpriseList($org_id);
        $w->data = json_encode($data);
        $w = new MercVSDList(5144);
        $w->getData();

        echo "FINISH" . PHP_EOL;


        echo "FINISH" . PHP_EOL;
    }
}
