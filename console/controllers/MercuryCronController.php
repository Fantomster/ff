<?php

namespace console\controllers;

use api\common\models\merc\mercPconst;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVisits;
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
use console\modules\daemons\classes\IikoProductSync;
use console\modules\daemons\classes\MercRussianEnterpriseList;
use console\modules\daemons\classes\MercStockEntryList;
use console\modules\daemons\classes\MercVSDList;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\Products;
use yii\console\Controller;
use api\common\models\merc\mercService;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use api_web\components\FireBase;
use yii\db\Expression;
use yii\db\Query;

class MercuryCronController extends Controller
{
    /**
     * Автоматическая загрузка списка ВСД и журнала склада для всех пользователей (за прошедшие сутки)
     *
     * @param int $interval
     */
    public function actionVetDocumentsChangeList($interval = 86000)
    {
        $locations = (new Query())
            ->distinct()
            ->select([
                "guid" => "mp2.value",
                "org"  => "mp.org",
                "code" => "ms.code"
            ])
            ->from(["ms" => mercService::tableName()])
            ->leftJoin(["mp" => mercPconst::tableName()], "mp.org = ms.org AND mp.const_id = 5")
            ->leftJoin(["mp2" => mercPconst::tableName()], "mp2.org = ms.org AND mp2.const_id = 10")
            ->where(["ms.status_id" => 1])
            ->andWhere([
                "BETWEEN",
                new Expression("NOW()"),
                new Expression("ms.fd"),
                new Expression("ms.td")
            ])
            ->andWhere([
                "AND",
                ["IS NOT", "mp.value", null],
                ["IS NOT", "mp2.value", null],
            ])
            ->groupBy("guid")
            ->orderBy([
                new Expression("guid"),
                "code" => SORT_DESC
            ])
            ->all(\Yii::$app->db_api);

        foreach ($locations as $item) {
            try {
                echo "Guid: " . $item['guid'] . PHP_EOL;

                $start_date = gmdate("Y-m-d H:i:s", time() - (int)$interval);
                echo "GET MercVSDList: " . $item['guid'] . PHP_EOL;
                echo "Start date " . $start_date . PHP_EOL;
                MercVsd::getUpdateData($item['org'], $item['guid'], $start_date);

                if ($item['code'] == mercService::EXTENDED_LICENSE_CODE) {
                    echo "GET MercStockEntryList: " . $item['guid'] . PHP_EOL;
                    MercStockEntry::getUpdateData($item['org'], $item['guid'], $start_date);
                }
            } catch (\Exception $e) {
                \Yii::error($e->getMessage(), $e->getTraceAsString());
            }
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
        IikoProductSync::getUpdateData(5144);
    }

    public function actionTestOne()
    {
        $load = new Products();

        $org_id = 5144;
        $queue = null;
        echo "START" . PHP_EOL;
        //Формируем данные для запроса
        $data['method'] = 'getRussianEnterpriseChangesList';
        $data['struct'] = ['listName'     => 'enterpriseList',
                           'listItemName' => 'enterprise'
        ];

        $listOptions['count'] = 1000;
        $listOptions['offset'] = 0;

        $startDate = gmdate("Y-m-d H:i:s", time() - 60 * 60 * 24 * 80);
        $instance = cerberApi::getInstance($org_id);
        $data['request'] = json_encode($instance->{$data['method']}(['listOptions' => $listOptions, 'startDate' => $startDate]));

        $w = new MercRussianEnterpriseList($org_id);
        $w->data = json_encode($data);
        $w->getData();

        echo "FINISH" . PHP_EOL;
    }

    public function actionTestVsd($org_id = 5144, $enterpriseGuid = 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b', $start_date = null)
    {
        echo "START" . PHP_EOL;
        echo "ORG: " . $org_id . PHP_EOL;
        echo "EnterpriseGuid: " . $enterpriseGuid . PHP_EOL;
        echo "Start date: " . $start_date . PHP_EOL;
        $w = new MercVSDList($org_id);
        MercVsd::getUpdateData($org_id);

        $data['startDate'] = $start_date ?? MercVisits::getLastVisit($org_id, 'MercVSDList', $enterpriseGuid);
        $data['listOptions']['count'] = 100;
        $data['listOptions']['offset'] = 0;
        $data['enterpriseGuid'] = $enterpriseGuid;
        $w->data = json_encode($data);
        $w->getData();
        echo "FINISH" . PHP_EOL;
    }

    public function actionLoadVsd($org_id = 5144, $enterpriseGuid = 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b', $start_date = null)
    {
        echo "START" . PHP_EOL;
        echo "ORG: " . $org_id . PHP_EOL;
        echo "EnterpriseGuid: " . $enterpriseGuid . PHP_EOL;
        echo "Start date: " . $start_date . PHP_EOL;
        MercVsd::getUpdateData($org_id, $enterpriseGuid, $start_date);
        echo "FINISH" . PHP_EOL;
    }

    public function actionTestStock($org_id = 5144, $enterpriseGuid = 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b', $start_date = null)
    {
        echo "START" . PHP_EOL;
        echo "ORG: " . $org_id . PHP_EOL;
        echo "EnterpriseGuid: " . $enterpriseGuid . PHP_EOL;
        echo "Start date: " . $start_date . PHP_EOL;
        $w = new MercStockEntryList($org_id);
        MercStockEntry::getUpdateData($org_id);
        $data['startDate'] = $start_date ?? MercVisits::getLastVisit($org_id, 'MercStockEntryList', $enterpriseGuid);
        $data['listOptions']['count'] = 100;
        $data['listOptions']['offset'] = 0;
        $data['enterpriseGuid'] = $enterpriseGuid;
        $w->data = json_encode($data);
        $w->getData();
        echo "FINISH" . PHP_EOL;
    }

    public function actionTestFcm()
    {
        FireBase::getInstance()->update([
            'mercury',
            'operation'      => 'MercVSDList',
            'enterpriseGuid' => 'f8805c8f-1da4-4bda-aaca-a08b5d1cab1b',
        ], [
            'update_date' => strtotime(gmdate("M d Y H:i:s")),
        ]);
    }
}
