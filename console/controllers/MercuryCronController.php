<?php

namespace console\controllers;

use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisCountry;
use common\models\vetis\VetisForeignEnterprise;
use common\models\vetis\VetisProductByType;
use common\models\vetis\VetisProductItem;
use common\models\vetis\VetisPurpose;
use common\models\vetis\VetisRussianEnterprise;
use common\models\vetis\VetisSubproductByProduct;
use common\models\vetis\VetisUnit;
use console\modules\daemons\classes\MercRussianEnterpriseList;
use console\modules\daemons\classes\MercStockEntryList;
use console\modules\daemons\classes\MercStoreEntryList;
use console\modules\daemons\classes\MercSubProductListList;
use console\modules\daemons\classes\MercVSDList;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\Products;
use yii\console\Controller;
use api\common\models\merc\mercService;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use Yii;
use api_web\components\FireBase;

class MercuryCronController extends Controller
{

    /**
     * Автоматическая загрузка списка ВСД и журнала склада для всех пользователей (за прошедшие сутки)
     */
    public function actionVetDocumentsChangeList()
    {
        $organizations = (new \yii\db\Query)
                ->from(mercService::tableName())
                ->where('status_id = 1 and now() between fd and td')
                ->createCommand(Yii::$app->db_api)
                ->queryColumn();
        try {
            foreach ($organizations as $org) {
                $org_id = $org->org;

                $locations = cerberApi::getInstance($org_id)->getActivityLocationList();

                if (!isset($locations->activityLocationList->location)) {
                    continue;
                }

                foreach ($locations->activityLocationList->location as $item) {
                    if (!isset($item->enterprise)) {
                        continue;
                    }

                    echo "GET MercVSDList " . $item->enterprise->guid . PHP_EOL;
                    MercVsd::getUpdateData($org_id, $item->enterprise->guid);

                    if ($org->code == mercService::EXTENDED_LICENSE_CODE) {
                        echo "GET MercStockEntryList" . $item->enterprise->guid . PHP_EOL;
                        MercStockEntry::getUpdateData($org_id, $item->enterprise->guid);
                    }
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }

    public function actionTest()
    {
        $org_id = 0;
        echo "START" . PHP_EOL;
        echo "GET Unit" . PHP_EOL;
        VetisUnit::getUpdateData($org_id);
        echo "GET Purpose" . PHP_EOL;
        VetisPurpose::getUpdateData($org_id);

        echo "GET Country" . PHP_EOL;
        VetisCountry::getUpdateData($org_id);

        echo "GET RussianEnterprise" . PHP_EOL;
        VetisRussianEnterprise::getUpdateData($org_id);
        echo "GET ForeignEnterprise" . PHP_EOL;
        VetisForeignEnterprise::getUpdateData($org_id);
        echo "GET BusinessEntity" . PHP_EOL;
        VetisBusinessEntity::getUpdateData($org_id);

        echo "GET ProductByType" . PHP_EOL;
        VetisProductByType::getUpdateData($org_id);
        echo "GET ProductItem" . PHP_EOL;
        VetisProductItem::getUpdateData($org_id);
        echo "GET SubproductByProduct" . PHP_EOL;
        VetisSubproductByProduct::getUpdateData($org_id);
        echo "FINISH" . PHP_EOL;
    }

    public function actionTest2()
    {
        $org_id = 0;
        echo "START" . PHP_EOL;
        echo "GET MercVSDList" . PHP_EOL;
        MercVsd::getUpdateData($org_id);

        echo "GET MercStockEntryList" . PHP_EOL;
        MercStockEntry::getUpdateData($org_id);
        echo "FINISH" . PHP_EOL;
    }

    public function actionTest3()
    {
        IikoProductsSync::getUpdateData(5144);
    }

    public function actionTestOne()
    {
        $load = new Products();

        $org_id = 0;
        $queue = null;
        echo "START" . PHP_EOL;
        //Формируем данные для запроса
        $data['method'] = 'getRussianEnterpriseChangesList';
        $data['struct'] = ['listName' => 'enterpriseList',
            'listItemName' => 'enterprise'
        ];

        $listOptions = new \frontend\modules\clientintegr\modules\merc\helpers\api\products\ListOptions();
        $listOptions->count = 1000;
        $listOptions->offset = 0;

        $startDate = ($queue === null) ? date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000)) : $queue->last_executed;
        $instance = cerberApi::getInstance($org_id);
        $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

        $w = new MercRussianEnterpriseList($org_id);
        $w->data = json_encode($data);
        $w->getData();

        echo "FINISH" . PHP_EOL;
    }

    public function actionTestVsd()
    {
        echo "START" . PHP_EOL;
        $w = new MercVSDList(5144);
        $w->data = 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b';
        $w->getData();
        echo "FINISH" . PHP_EOL;
    }

    public function actionTestStock()
    {
        echo "START" . PHP_EOL;
        $w = new MercStockEntryList(5144);
        $w->data = 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b';
        $w->getData();
        echo "FINISH" . PHP_EOL;
    }

    public function actionTestFcm()
    {
        FireBase::getInstance()->update([
            'mercury',
            'operation' => 'MercVSDList',
            'enterpriseGuid' => 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b',
                ], [
            'update_date' => strtotime(gmdate("M d Y H:i:s")),
        ]);
    }

}
