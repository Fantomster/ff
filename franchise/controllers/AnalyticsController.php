<?php

namespace franchise\controllers;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\Role;
use common\models\FranchiseeAssociate;
use common\models\Organization;
use common\models\Order;
use common\models\RelationSuppRest;
use common\models\OrderContent;
use common\models\CatalogBaseGoods;
use yii\data\SqlDataProvider;

/**
 * Description of AnalyticsController
 *
 * @author sharaf
 */
class AnalyticsController extends DefaultController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::class,
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::class,
                ],
                'only'       => ['index'],
                'rules'      => [
                    [
                        'actions' => ['index'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_FRANCHISEE_LEADER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionIndex()
    {
        $orgTable = Organization::tableName();
        $fraTable = FranchiseeAssociate::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");

        $clientTotalCount = Organization::find()
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where([
                "$orgTable.type_id"       => Organization::TYPE_RESTAURANT,
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorTotalCount = Organization::find()
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where([
                "$orgTable.type_id"       => Organization::TYPE_SUPPLIER,
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->groupBy(["$orgTable.id"])
            ->count();
        $allTimeCount = $clientTotalCount + $vendorTotalCount;

        $allTime = [$clientTotalCount, $vendorTotalCount];

        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');

        $clientCountThisMonth = Organization::find()
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where([
                "$orgTable.type_id"       => Organization::TYPE_RESTAURANT,
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorCountThisMonth = Organization::find()
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where([
                "$orgTable.type_id"       => Organization::TYPE_SUPPLIER,
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $thisMonthCount = $clientCountThisMonth + $vendorCountThisMonth;

        $thisMonth = [$clientCountThisMonth, $vendorCountThisMonth];

        $clientCountThisDay = Organization::find()
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where([
                "$orgTable.type_id"       => Organization::TYPE_RESTAURANT,
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorCountThisDay = Organization::find()
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where([
                "$orgTable.type_id"       => Organization::TYPE_SUPPLIER,
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $todayCount = $clientCountThisDay + $vendorCountThisDay;

        $todayArr = [$clientCountThisDay, $vendorCountThisDay];

        $clients = [];
        $vendors = [];
        $end = $dtEnd->add(new \DateInterval('P1D'));

        $clientsByDay = (new Query())
            ->select("
              COUNT($orgTable.id) AS count,
              SUM(CASE WHEN $orgTable.type_id=1 THEN 1 ELSE 0 END) AS clients,
              SUM(CASE WHEN $orgTable.type_id=2 THEN 1 ELSE 0 END) AS vendors,
              YEAR($orgTable.created_at) AS year,
              MONTH($orgTable.created_at) AS month,
              DAY($orgTable.created_at) AS day
            ")->from($orgTable)
            ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
            ->where(["$fraTable.franchisee_id" => $this->currentFranchisee->id])
            ->andWhere("$orgTable.created_at BETWEEN :dateFrom AND :dateTo", [
                ":dateFrom" => $dt->format('Y-m-d'),
                ":dateTo"   => $end->format('Y-m-d')
            ])
            ->groupBy("YEAR($orgTable.created_at), MONTH($orgTable.created_at), DAY($orgTable.created_at)")
            ->all();

        $dayLabels = [];
        $dayStats = [];
        $total = 0;
        foreach ($clientsByDay as $day) {
            $dayLabels[] = $day["day"] . " " . Yii::$app->formatter->asDatetime(strtotime("2000-$day[month]-01"), "php:M") . " " . $day["year"];
            $dayStats[] = $day["count"];
            $total += $day["count"];
            $clients[] = $day["clients"];
            $vendors[] = $day["vendors"];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact(
                'total', 'dateFilterFrom', 'dateFilterTo', 'clients', 'vendors', 'allTime', 'thisMonth', 'todayArr', 'todayCount', 'thisMonthCount', 'allTimeCount', 'dayLabels', 'dayStats'
            ));
        } else {
            return $this->render('index', compact(
                'total', 'dateFilterFrom', 'dateFilterTo', 'clients', 'vendors', 'allTime', 'thisMonth', 'todayArr', 'todayCount', 'thisMonthCount', 'allTimeCount', 'dayLabels', 'dayStats'
            ));
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionPage2()
    {
        $orderTable = Order::tableName();
        $fraTable = FranchiseeAssociate::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));

        $labelsTotal = [];
        $colorsTotal = [];
        $statusesList = Order::getStatusList();
        unset($statusesList[Order::STATUS_FORMING]);
        $statuses = array_keys($statusesList);
        $colorsList = Order::getStatusColors();

        $select = "count($orderTable.id) as count";

        foreach ($statuses as $status) {
            $status = (int)$status;
            $select .= ", sum(case when $orderTable.status=$status then 1 else 0 end) as status_$status";
            $labelsTotal[] = $statusesList[$status];
            $colorsTotal[] = $colorsList[$status];
        }

        $totalCount = (new Query())
            ->select($select)
            ->from($orderTable)
            ->leftJoin($fraTable, "$fraTable.organization_id=$orderTable.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere("status <> :st", [':st' => Order::STATUS_FORMING])
            ->scalar();
        $ordersStat[] = $totalCount;

        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');

        $totalCountThisMonth = (new Query())
            ->select($select)
            ->from($orderTable)
            ->leftJoin($fraTable, "$fraTable.organization_id=$orderTable.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere("status <> :st", [':st' => Order::STATUS_FORMING])
            ->andWhere("$orderTable.created_at > '$thisMonthStart'")
            ->scalar();
        $ordersStatThisMonth[] = $totalCountThisMonth;

        $totalCountThisDay = (new Query())
            ->select($select)
            ->from($orderTable)
            ->leftJoin($fraTable, "$fraTable.organization_id=$orderTable.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
            ])
            ->andWhere("status <> :st", [':st' => Order::STATUS_FORMING])
            ->andWhere("$orderTable.created_at > '$thisDayStart'")
            ->scalar();
        $ordersStatThisDay[] = $totalCountThisDay;

        /**
         * Какой то жопошный запрос
         */
        $subQueryFrom = (new Query())
            ->select("
                count($orderTable.id) as count,
                year($orderTable.created_at) as year,
                month($orderTable.created_at) as month,
                day($orderTable.created_at) as day
            ")->from($orderTable)
            ->leftJoin($fraTable, "$fraTable.organization_id = $orderTable.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id
            ])
            ->andWhere("$orderTable.status <> :st", [":st" => Order::STATUS_FORMING])
            ->andWhere("$orderTable.created_at BETWEEN :dateFrom AND :dateTo", [":dateFrom" => $dt->format('Y-m-d'), ":dateTo" => $end->format('Y-m-d')])
            ->groupBy("year($orderTable.created_at), month($orderTable.created_at), day($orderTable.created_at)")
            ->createCommand()->getRawSql();

        $subQueryInJoin = (new Query())
            ->select("a.*")
            ->from($orderTable . " a")
            ->leftJoin($fraTable, "$fraTable.organization_id = a.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id
            ])
            ->andWhere("a.status <> :st", [":st" => Order::STATUS_FORMING])
            ->andWhere("a.created_at BETWEEN :dateFrom AND :dateTo", [":dateFrom" => $dt->format('Y-m-d'), ":dateTo" => $end->format('Y-m-d')])
            ->groupBy("a.client_id")
            ->orderBy("a.id")
            ->createCommand()->getRawSql();

        $subQueryJoin = (new Query())
            ->select("
                count(b.id) as first,
                year(b.created_at) as year, 
                month(b.created_at) as month, 
                day(b.created_at) as day
            ")->from("($subQueryInJoin) b")
            ->groupBy("year(b.created_at), month(b.created_at), day(b.created_at)")
            ->createCommand()->getRawSql();

        $ordersByDay = (new Query())
            ->select("aa.count as total, bb.first as first, aa.year as year, aa.month as month, aa.day as day")
            ->from("($subQueryFrom) aa")
            ->join("left outer join", "($subQueryJoin) bb", "aa.year = bb.year and aa.month=bb.month and aa.day=bb.day")
            ->all();
        /**
         * Какой то жопошный запрос END
         */

        $dayLabels = [];
        $dayStats = [];
        $firstDayStats = [];
        $total = 0;
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . Yii::$app->formatter->asDatetime(strtotime("2000-$order[month]-01"), "php:M") . " " . $order["year"];
            $dayStats[] = $order["total"];
            $total += $order["total"];
            $firstDayStats[] = $order["first"];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('page2', compact(
                'total', 'dateFilterFrom', 'dateFilterTo', 'ordersStatThisMonth', 'ordersStatThisDay', 'labelsTotal', 'ordersStat', 'colorsTotal', 'totalCountThisMonth', 'totalCountThisDay', 'totalCount', 'firstDayStats', 'dayLabels', 'dayStats'
            ));
        } else {
            return $this->render('page2', compact(
                'total', 'dateFilterFrom', 'dateFilterTo', 'ordersStatThisMonth', 'ordersStatThisDay', 'labelsTotal', 'ordersStat', 'colorsTotal', 'totalCountThisMonth', 'totalCountThisDay', 'totalCount', 'firstDayStats', 'dayLabels', 'dayStats'
            ));
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionPage3()
    {
        $orderTable = Order::tableName();
        $fraTable = FranchiseeAssociate::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));

        $ordersByDay = (new Query())
            ->select([
                'spent'  => new Expression("truncate(sum($orderTable.total_price), 1)"),
                'cheque' => new Expression("truncate(sum($orderTable.total_price)/count($orderTable.id),1)"),
                'year'   => new Expression("year($orderTable.created_at)"),
                'month'  => new Expression("month($orderTable.created_at)"),
                'day'    => new Expression("day($orderTable.created_at)"),
            ])
            ->from($orderTable)
            ->leftJoin($fraTable, "$fraTable.organization_id=$orderTable.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
                "$orderTable.status"      => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("$orderTable.created_at between :dateFrom and :dateTo", [
                ":dateFrom" => $dt->format('Y-m-d'),
                ":dateTo"   => $end->format('Y-m-d')
            ])
            ->groupBy("year($orderTable.created_at), month($orderTable.created_at), day($orderTable.created_at)")
            ->all();

        $dayLabels = [];
        $dayTurnover = [];
        $dayCheque = [];
        $total = 0;
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . Yii::$app->formatter->asDatetime(strtotime("2000-$order[month]-01"), "php:M") . " " . $order["year"];
            $dayTurnover[] = $order["spent"];
            $total += $order["spent"];
            $dayCheque[] = $order["cheque"];
        }

        $money = (new Query())
            ->select([
                'total_month' => new Expression("truncate(sum($orderTable.total_price),1)"),
                'spent'       => new Expression("truncate(sum($orderTable.total_price)/count(distinct $orderTable.client_id),1)"),
                'cheque'      => new Expression("truncate(sum($orderTable.total_price)/count($orderTable.id),1)"),
                'year'        => new Expression("year($orderTable.created_at)"),
                'month'       => new Expression("month($orderTable.created_at)"),
            ])
            ->from($orderTable)
            ->leftJoin($fraTable, "$fraTable.organization_id=$orderTable.vendor_id")
            ->where([
                "$fraTable.franchisee_id" => $this->currentFranchisee->id,
                "$orderTable.status"      => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->groupBy("year($orderTable.created_at), month($orderTable.created_at)")
            ->all();

        $monthLabels = [];
        $averageSpent = [];
        $averageCheque = [];
        $totalSpent = [];
        foreach ($money as $month) {
            $monthLabels[] = Yii::$app->formatter->asDatetime(strtotime("2000-$month[month]-01"), "php:M") . " " . $month["year"];
            $averageSpent[] = $month["spent"];
            $averageCheque[] = $month["cheque"];
            $totalSpent[] = $month["total_month"];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('page3', compact(
                'total', 'totalSpent', 'monthLabels', 'averageSpent', 'averageCheque', 'dateFilterFrom', 'dateFilterTo', 'dayLabels', 'dayTurnover', 'dayCheque'
            ));
        } else {
            return $this->render('page3', compact(
                'total', 'totalSpent', 'monthLabels', 'averageSpent', 'averageCheque', 'dateFilterFrom', 'dateFilterTo', 'dayLabels', 'dayTurnover', 'dayCheque'
            ));
        }
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function actionClientStats($id)
    {
        $client = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
            ->one();

        $orgTable = Organization::tableName();
        $orderTable = Order::tableName();
        $contTable = OrderContent::tableName();
        $cbgTable = CatalogBaseGoods::tableName();

        //---header stats start
        $headerStats["ordersCount"] = Order::find()
            ->where(["client_id" => $client->id])
            ->count();
        $headerStats["vendorsCount"] = RelationSuppRest::find()
            ->where(["rest_org_id" => $client->id])
            ->count();
        $headerStats["totalTurnover"] = Order::find()
            ->where(['client_id' => $client->id, 'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_PROCESSING, Order::STATUS_DONE]])
            ->sum('total_price');
        //---header stats end

        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : date("d.m.Y", strtotime(" -1 months"));
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : date("d.m.Y");

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));

        //---turnover by day start

        $ordersByDay = (new Query())
            ->select([
                'spent'  => new Expression("TRUNCATE(SUM(total_price),1)"),
                'cheque' => new Expression("truncate(sum($orderTable.total_price)/count($orderTable.id),1)"),
                'year'   => new Expression("year(created_at)"),
                'month'  => new Expression("month(created_at)"),
                'day'    => new Expression("DAY(created_at)"),
            ])
            ->from($orderTable)
            ->where([
                "client_id"          => $client->id,
                "$orderTable.status" => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("created_at BETWEEN :dateFrom AND :dateTo", [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("YEAR(created_at), MONTH(created_at), DAY(created_at)")
            ->all();

        $dayLabels = [];
        $dayTurnover = [];
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . Yii::$app->formatter->asDatetime(strtotime("2000-$order[month]-01"), "php:M") . " " . $order["year"];
            $dayTurnover[] = $order["spent"];
        }
        //---turnover by day end

        //---turnover by vendor start
        $turnoverByVendor = (new Query())
            ->select([
                'vendor_turnover' => new Expression("TRUNCATE(SUM(total_price),1)"),
                'name'            => "$orgTable.name"
            ])
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.vendor_id=$orgTable.id")
            ->where([
                "client_id"          => $client->id,
                "$orderTable.status" => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("$orderTable.created_at BETWEEN :dateFrom AND :dateTo", [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("vendor_id")
            ->all();

        $vendorsTurnover['stats'] = [];
        $vendorsTurnover['labels'] = [];
        $vendorsTurnover['colors'] = [];
        foreach ($turnoverByVendor as $vendor) {
            $vendorsTurnover['stats'][] = $vendor['vendor_turnover'];
            $vendorsTurnover['labels'][] = $vendor['name'];
            $vendorsTurnover['colors'][] = $this->hex();
        }
        //---turnover by vendor end

        //---top goods start
        $query = (new Query())
            ->select([
                'sum_spent' => new Expression("TRUNCATE(SUM($contTable.price*quantity),2)"),
                'quantity'  => new Expression("SUM(quantity)"),
                'ed'        => "$cbgTable.ed",
                'name'      => "$cbgTable.product"
            ])
            ->from($contTable)
            ->leftJoin($orderTable, "$contTable.order_id = $orderTable.id")
            ->leftJoin($cbgTable, "$contTable.product_id = $cbgTable.id")
            ->where([
                "client_id"          => $client->id,
                "$orderTable.status" => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("$orderTable.created_at BETWEEN :dateFrom AND :dateTo", [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("product_id")
            ->orderBy(["SUM($contTable.price*quantity)" => SORT_DESC])
            ->createCommand()->getRawSql();

        $topGoodsDP = new SqlDataProvider([
            'sql'    => $query,
            'params' => [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')],
        ]);
        //---top goods end

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('client-stats', compact(
                'headerStats', 'dateFilterFrom', 'dateFilterTo', 'dayTurnover', 'dayLabels', 'vendorsTurnover', 'topGoodsDP', 'client'
            ));
        } else {
            return $this->render('client-stats', compact(
                'headerStats', 'dateFilterFrom', 'dateFilterTo', 'dayTurnover', 'dayLabels', 'vendorsTurnover', 'topGoodsDP', 'client'
            ));
        }
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function actionVendorStats($id)
    {
        $vendor = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where([
                'franchisee_associate.franchisee_id' => $this->currentFranchisee->id,
                'organization.id'                    => $id,
                'organization.type_id'               => Organization::TYPE_SUPPLIER
            ])
            ->one();

        $orgTable = Organization::tableName();
        $orderTable = Order::tableName();
        $contTable = OrderContent::tableName();
        $cbgTable = CatalogBaseGoods::tableName();

        //---header stats start
        $headerStats["ordersCount"] = Order::find()
            ->where(["vendor_id" => $vendor->id])
            ->count();
        $headerStats["goodsCount"] = CatalogBaseGoods::find()
            ->where(["supp_org_id" => $vendor->id, "status" => CatalogBaseGoods::STATUS_ON, "deleted" => CatalogBaseGoods::DELETED_OFF])
            ->count();
        $headerStats["clientsCount"] = RelationSuppRest::find()
            ->where(["supp_org_id" => $vendor->id])
            ->count();
        $headerStats["totalTurnover"] = Order::find()
            ->where(['vendor_id' => $vendor->id, 'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_PROCESSING, Order::STATUS_DONE]])
            ->sum('total_price');
        //---header stats end

        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : date("d.m.Y", strtotime(" -1 months"));
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : date("d.m.Y");

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));

        //---turnover by day start

        $ordersByDay = (new Query())
            ->select([
                'spent'  => new Expression("TRUNCATE(SUM(total_price),1)"),
                'year'   => new Expression("year(created_at)"),
                'month'  => new Expression("month(created_at)"),
                'day'    => new Expression("DAY(created_at)"),
            ])
            ->from($orderTable)
            ->where([
                "vendor_id"          => $vendor->id,
                "$orderTable.status" => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("created_at BETWEEN :dateFrom AND :dateTo", [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("YEAR(created_at), MONTH(created_at), DAY(created_at)")
            ->all();

        $dayLabels = [];
        $dayTurnover = [];
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . date('M', strtotime("2000-$order[month]-01")) . " " . $order["year"];
            $dayTurnover[] = $order["spent"];
        }
        //---turnover by day end

        //---clients count by day
        $clientsByDay = (new Query())
            ->select([
                "COUNT(*) AS clients_count, YEAR(created_at) AS year, MONTH(created_at) AS month, DAY(created_at) AS day"
            ])
            ->from(RelationSuppRest::tableName())
            ->where([
                'supp_org_id' => $id
            ])
            ->andWhere("created_at IS NOT NULL")
            ->andWhere("created_at BETWEEN :dateFrom AND :dateTo", [
                ":dateFrom" => $dt->format('Y-m-d'),
                ":dateTo"   => $end->format('Y-m-d')
            ])
            ->groupBy("YEAR(created_at), MONTH(created_at), DAY(created_at)")
            ->all();

        $clientsDayLabels = [];
        $clientsDayTurnover = [];
        foreach ($clientsByDay as $client) {
            $clientsDayLabels[] = $client["day"] . " " . date('M', strtotime("2000-$client[month]-01")) . " " . $client["year"];
            $clientsDayTurnover[] = $client["clients_count"];
        }
        //---clients count by day end

        //---turnover by client start
        $turnoverByClient = (new Query())
            ->select([
                'client_turnover' => new Expression("TRUNCATE(SUM(total_price),1)"),
                'name'            => "$orgTable.name"
            ])
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where([
                "vendor_id"          => $vendor->id,
                "$orderTable.status" => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("$orderTable.created_at BETWEEN :dateFrom AND :dateTo", [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("client_id")
            ->all();

        $clientsTurnover['stats'] = [];
        $clientsTurnover['labels'] = [];
        $clientsTurnover['colors'] = [];
        foreach ($turnoverByClient as $client) {
            $clientsTurnover['stats'][] = $client['client_turnover'];
            $clientsTurnover['labels'][] = $client['name'];
            $clientsTurnover['colors'][] = $this->hex();
        }
        //---turnover by client end

        //---top goods start
        $query = (new Query())
            ->select([
                'sum_spent' => new Expression("TRUNCATE(SUM($contTable.price*quantity),2)"),
                'quantity'  => new Expression("SUM(quantity)"),
                'ed'        => "$cbgTable.ed",
                'name'      => "$cbgTable.product"
            ])
            ->from($contTable)
            ->leftJoin($orderTable, "$contTable.order_id = $orderTable.id")
            ->leftJoin($cbgTable, "$contTable.product_id = $cbgTable.id")
            ->where([
                "vendor_id"          => $vendor->id,
                "$orderTable.status" => [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]
            ])
            ->andWhere("$orderTable.created_at BETWEEN :dateFrom AND :dateTo", [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("product_id")
            ->orderBy(["SUM($contTable.price*quantity)" => SORT_DESC])
            ->createCommand()->getRawSql();

        $topGoodsDP = new SqlDataProvider([
            'sql'    => $query,
            'params' => [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')],
        ]);
        //---top goods end

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('vendor-stats', compact(
                'headerStats', 'dateFilterFrom', 'dateFilterTo', 'dayTurnover', 'dayLabels', 'clientsTurnover', 'topGoodsDP', 'vendor', 'clientsDayLabels', 'clientsDayTurnover'
            ));
        } else {
            return $this->render('vendor-stats', compact(
                'headerStats', 'dateFilterFrom', 'dateFilterTo', 'dayTurnover', 'dayLabels', 'clientsTurnover', 'topGoodsDP', 'vendor', 'clientsDayLabels', 'clientsDayTurnover'
            ));
        }
    }

    private function hex()
    {
        $hex = '#';
        foreach (['r', 'g', 'b'] as $color) {
            //случайное число в диапазоне 0 и 255.
            $val = mt_rand(0, 255);
            //преобразуем число в Hex значение.
            $dechex = dechex($val);
            //с 0, если длина меньше 2
            if (strlen($dechex) < 2) {
                $dechex = "0" . $dechex;
            }
            //объединяем
            $hex .= $dechex;
        }
        return $hex;
    }

}
