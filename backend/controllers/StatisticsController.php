<?php

namespace backend\controllers;

use backend\models\DynamicUsageSearch;
use backend\models\MercuryReportSearch;
use backend\models\OrgUseMercFrequently;
use common\models\OrderStatus;
use Yii;
use common\models\User;
use common\models\Role;
use common\models\Organization;
use common\models\Order;
use backend\models\UserSearch;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\CatalogBaseGoods;

/**
 * Description of StatisticsController
 *
 * @author sharaf
 */
class StatisticsController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => ['index', 'registered', 'orders', 'turnover', 'misc', 'dynamics', 'mercury', 'merc-active-org'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRegistered()
    {
        $userTable = User::tableName();
        $orgTable = Organization::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $day = $dt->format('w');
        $date = $dt->format('Y-m-d');

        $clientTotalCount = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT, "$orgTable.blacklisted" => false])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorTotalCount = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER, "$orgTable.blacklisted" => false])
            ->groupBy(["$orgTable.id"])
            ->count();
        $allTimeCount = $clientTotalCount + $vendorTotalCount;

        $allTime = [$clientTotalCount, $vendorTotalCount];

        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');

        $clientCountThisMonth = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT, "$orgTable.blacklisted" => false])
            ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorCountThisMonth = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER, "$orgTable.blacklisted" => false])
            ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $thisMonthCount = $clientCountThisMonth + $vendorCountThisMonth;

        $thisMonth = [$clientCountThisMonth, $vendorCountThisMonth];

        $clientCountThisDay = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT, "$orgTable.blacklisted" => false])
            ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorCountThisDay = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER, "$orgTable.blacklisted" => false])
            ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $todayCount = $clientCountThisDay + $vendorCountThisDay;

        $todayArr = [$clientCountThisDay, $vendorCountThisDay];

        $all = [];
        $clients = [];
        $vendors = [];
        $weeks = [];

        $start = $dt;
        if (!$day) {
            $day = 7;
        }
        $end = $dtEnd->add(new \DateInterval('P1D'));

        $clientsByDay = (new Query())->select("COUNT($orgTable.id) AS count, 
                SUM(CASE WHEN organization.type_id=1 THEN 1 ELSE 0 END) AS clients, 
                SUM(CASE WHEN organization.type_id=2 THEN 1 ELSE 0 END) AS vendors, 
                YEAR($orgTable.created_at) AS year, 
                MONTH($orgTable.created_at) AS month, 
                DAY($orgTable.created_at) AS day")
            ->from($orgTable)
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where("($userTable.status=1) AND 
                    ($orgTable.created_at BETWEEN :dateFrom AND :dateTo) AND 
                    $orgTable.blacklisted = 0",
                [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')])
            ->groupBy("YEAR($orgTable.created_at), MONTH($orgTable.created_at), DAY($orgTable.created_at)")
            ->all();

        $dayLabels = [];
        $dayStats = [];
        $total = 0;
        foreach ($clientsByDay as $day) {
            $dayLabels[] = $day["day"] . " " . date('M', strtotime("2000-$day[month]-01")) . " " . $day["year"];
            $dayStats[] = $day["count"];
            $total += $day["count"];
            $clients[] = $day["clients"];
            $vendors[] = $day["vendors"];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('registered', compact(
                'total',
                'dateFilterFrom',
                'dateFilterTo',
                'clients',
                'vendors',
                'allTime',
                'thisMonth',
                'todayArr',
                'todayCount',
                'thisMonthCount',
                'allTimeCount',
                'dayLabels',
                'dayStats'
            ));
        } else {
            return $this->render('registered', compact(
                'total',
                'dateFilterFrom',
                'dateFilterTo',
                'clients',
                'vendors',
                'allTime',
                'thisMonth',
                'todayArr',
                'todayCount',
                'thisMonthCount',
                'allTimeCount',
                'dayLabels',
                'dayStats'
            ));
        }
    }

    public function actionOrders()
    {
        $orderTable = Order::tableName();
        $userTable = User::tableName();
        $orgTable = Organization::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));
        $date = $dt->format('Y-m-d');

        $labelsTotal = [];
        $colorsTotal = [];
        $statusesList = Order::getStatusList();
        unset($statusesList[OrderStatus::STATUS_FORMING]);
        $statuses = array_keys($statusesList);
        $colorsList = Order::getStatusColors();

        $select = "count($orderTable.id) as count";

        foreach ($statuses as $status) {
            $status = (int)$status;
            $select .= ", sum(case when $orderTable.status=$status then 1 else 0 end) as status_$status";
            $labelsTotal[] = $statusesList[$status];
            $colorsTotal[] = $colorsList[$status];
        }

        $ordersStat = (new Query())->select($select)->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["$orgTable.blacklisted" => 0])
            ->andWhere(["<>", "$orderTable.status", OrderStatus::STATUS_FORMING])
            ->all()[0];

        $totalCount = $ordersStat["count"];
        unset($ordersStat["count"]);

        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');

        $ordersStatThisMonth = (new Query())->select($select)->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["$orgTable.blacklisted" => 0])
            ->andWhere([">", "$orderTable.created_at", $thisMonthStart])
            ->andWhere(["<>", "$orderTable.status", OrderStatus::STATUS_FORMING])
            ->all()[0];

        $totalCountThisMonth = $ordersStatThisMonth["count"];
        unset($ordersStatThisMonth["count"]);

        $ordersStatThisDay = (new Query())->select($select)->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["$orgTable.blacklisted" => 0])
            ->andWhere([">", "$orderTable.created_at", $thisDayStart])
            ->andWhere(["<>", "$orderTable.status", OrderStatus::STATUS_FORMING])
            ->all()[0];

        $totalCountThisDay = $ordersStatThisDay["count"];
        unset($ordersStatThisDay["count"]);

        $fromSelect = (new Query())->select("count($orderTable.id) as count,
                year($orderTable.created_at) as year, 
                month($orderTable.created_at) as month, 
                day($orderTable.created_at) as day")
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["<>", "$orderTable.status", ':qp0'])
            ->andWhere(['BETWEEN', "$orderTable.created_at", ':qp1', ':qp2'])
            ->andWhere(["$orgTable.blacklisted" => ':qp3'])
            ->groupBy(["year($orderTable.created_at)",
                "month($orderTable.created_at)",
                "day($orderTable.created_at)"])
            ->createCommand()->sql;

        $leftJoinInnerSelect = (new Query())->select("*")
            ->from("$orderTable a")
            ->where(["<>", "a.status", ':qp0'])
            ->andWhere(['BETWEEN', "a.created_at", ':qp1', ':qp2'])
            ->groupBy(["a.client_id"])
            ->orderBy("a.id")
            ->createCommand()->sql;

        $leftJoinOuterSelect = (new Query())->select("count(b.id) as first,
                year(b.created_at) as year, 
                month(b.created_at) as month, 
                day(b.created_at) as day")
            ->from("($leftJoinInnerSelect) b")
            ->groupBy(["year(b.created_at)",
                "month(b.created_at)",
                "day(b.created_at)"])
            ->createCommand()->sql;

        $ordersByDay = (new Query())->select("aa.count as total, bb.first as first, aa.year as year, aa.month as month, aa.day as day")
            ->from("($fromSelect) aa")
            ->leftJoin("($leftJoinOuterSelect) bb", "aa.year = bb.year and aa.month=bb.month and aa.day=bb.day")
            ->params([
                ':qp0' => 7,
                ':qp1' => $dt->format('Y-m-d'),
                ':qp2' => $end->format('Y-m-d'),
                ':qp3' => 0,
            ])->all();

        $dayLabels = [];
        $dayStats = [];
        $firstDayStats = [];
        $total = 0;
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . date('M', strtotime("2000-$order[month]-01")) . " " . $order["year"];
            $dayStats[] = $order["total"];
            $total += $order["total"];
            $firstDayStats[] = $order["first"];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact(
                'total',
                'dateFilterFrom',
                'dateFilterTo',
                'ordersStatThisMonth',
                'ordersStatThisDay',
                'labelsTotal',
                'ordersStat',
                'colorsTotal',
                'totalCountThisMonth',
                'totalCountThisDay',
                'totalCount',
                'firstDayStats',
                'dayLabels',
                'dayStats'
            ));
        } else {
            return $this->render('orders', compact(
                'total',
                'dateFilterFrom',
                'dateFilterTo',
                'ordersStatThisMonth',
                'ordersStatThisDay',
                'labelsTotal',
                'ordersStat',
                'colorsTotal',
                'totalCountThisMonth',
                'totalCountThisDay',
                'totalCount',
                'firstDayStats',
                'dayLabels',
                'dayStats'
            ));
        }
    }

    public function actionTurnover()
    {
        $orderTable = Order::tableName();
        $userTable = User::tableName();
        $orgTable = Organization::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));
        $date = $dt->format('Y-m-d');

        $ordersByDay = (new Query())->select([
            "spent"  => "truncate(sum($orderTable.total_price), 1)",
            "cheque" => "truncate(sum($orderTable.total_price)/count($orderTable.id),1)",
            "year"   => "year($orderTable.created_at)",
            "month"  => "month($orderTable.created_at)",
            "day"    => "day($orderTable.created_at)"
        ])
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id = $orgTable.id")
            ->where(['in', "$orderTable.status", [
                OrderStatus::STATUS_PROCESSING,
                OrderStatus::STATUS_DONE,
                OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR
            ]])
            ->andWhere(["$orgTable.blacklisted" => 0])
            ->andWhere(["BETWEEN", "$orderTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy([
                "year($orderTable.created_at)",
                "month($orderTable.created_at)",
                "day($orderTable.created_at)"
            ])
            ->all();

        $dayLabels = [];
        $dayTurnover = [];
        $dayCheque = [];
        $total = 0;
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . date('M', strtotime("2000-$order[month]-01")) . " " . $order["year"];
            $dayTurnover[] = $order["spent"];
            $total += $order["spent"];
            $dayCheque[] = $order["cheque"];
        }

        $query = "SELECT truncate(sum($orderTable.total_price),1) as total_month, truncate(sum($orderTable.total_price)/count(distinct $orderTable.client_id),1) as spent,truncate(sum($orderTable.total_price)/count($orderTable.id),1) as cheque, year($orderTable.created_at) as year, month($orderTable.created_at) as month "
            . "FROM $orderTable LEFT JOIN $orgTable ON $orderTable.client_id = $orgTable.id "
            . "where $orderTable.status in (" . OrderStatus::STATUS_PROCESSING . "," . OrderStatus::STATUS_DONE . "," . OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT . "," . OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR . ") and $orgTable.blacklisted = 0 "
            . "group by year($orderTable.created_at), month($orderTable.created_at)";
        $command = Yii::$app->db->createCommand($query);
        $money = $command->queryAll();
        $monthLabels = [];
        $averageSpent = [];
        $averageCheque = [];
        $totalSpent = [];
        foreach ($money as $month) {
            $monthLabels[] = date('M', strtotime("2000-$month[month]-01")) . " " . $month["year"];
            $averageSpent[] = $month["spent"];
            $averageCheque[] = $month["cheque"];
            $totalSpent[] = $month["total_month"];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('turnover', compact(
                'total',
                'totalSpent',
                'monthLabels',
                'averageSpent',
                'averageCheque',
                'dateFilterFrom',
                'dateFilterTo',
                'dayLabels',
                'dayTurnover',
                'dayCheque'
            ));
        } else {
            return $this->render('turnover', compact(
                'total',
                'totalSpent',
                'monthLabels',
                'averageSpent',
                'averageCheque',
                'dateFilterFrom',
                'dateFilterTo',
                'dayLabels',
                'dayTurnover',
                'dayCheque'
            ));
        }
    }

    public function actionMisc()
    {
        $orderTable = Order::tableName();
        $userTable = User::tableName();
        $orgTable = Organization::tableName();
        $cbgTable = CatalogBaseGoods::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));
        $date = $dt->format('Y-m-d');

        $totalClients = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT])
            ->andWhere(['between', "$orgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy(["$orgTable.id"])
            ->count();

        $query = "select $orgTable.id as id, count($orderTable.id) as ordersCount from $orgTable "
            . "left join $userTable on $orgTable.id=$userTable.organization_id "
            . "left join $orderTable on $orderTable.client_id = $orgTable.id "
            . "where type_id=1 and $userTable.status=1 and $orderTable.status in (" . OrderStatus::STATUS_PROCESSING . "," . OrderStatus::STATUS_DONE . "," . OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT . "," . OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR . ") "
            . "and $orgTable.blacklisted = 0 and $orgTable.created_at between :dateFrom and :dateTo group by $orgTable.id";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $clientsWithOrders = $command->queryAll();
        $clientsWithOrdersCount = count($clientsWithOrders);
        $clientsWithoutOrdersCount = $totalClients - $clientsWithOrdersCount;
        $clientsStats = [
            'c0' => $clientsWithoutOrdersCount,
            'c1' => 0,
            'c2' => 0,
            'c3' => 0,
            'c4' => 0,
            'c5' => 0,
            'cn' => 0,
        ];
        foreach ($clientsWithOrders as $client) {
            switch ($client["ordersCount"]) {
                case 1:
                    $clientsStats["c1"]++;
                    break;
                case 2:
                    $clientsStats["c2"]++;
                    break;
                case 3:
                    $clientsStats["c3"]++;
                    break;
                case 4:
                    $clientsStats["c4"]++;
                    break;
                case 5:
                    $clientsStats["c5"]++;
                    break;
                default:
                    $clientsStats["cn"]++;
                    break;
            }
        }

        $query = "select count(org_id) from (select $orgTable.id as org_id from $orgTable left join $cbgTable on $orgTable.id = $cbgTable.supp_org_id "
            . "where $orgTable.blacklisted=0 and $cbgTable.deleted = 0 and $orgTable.created_at between :dateFrom and :dateTo group by $orgTable.id) as tmp";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $vendorsWithGoodsCount = $command->queryScalar();

        $productsCount = CatalogBaseGoods::find()
            ->where(['deleted' => CatalogBaseGoods::DELETED_OFF])
            ->andWhere(['between', "$cbgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->count();
        $productsOnMarketCount = CatalogBaseGoods::find()
            ->joinWith("vendor")
            ->where([
                'deleted'      => CatalogBaseGoods::DELETED_OFF,
                'market_place' => CatalogBaseGoods::MARKETPLACE_ON,
                'white_list'   => Organization::WHITE_LIST_ON,
                'status'       => CatalogBaseGoods::STATUS_ON,
            ])
            ->andWhere('category_id is not null')
            ->andWhere(['between', "$cbgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->count();

        //Среднее количество заказов ресторанами в день за период
        $query = "select avg(cnt)
                        from (
                        select a.client_id, count(a.id) cnt, DATE_FORMAT(a.created_at,'%Y-%m-%d') d
                        from $orderTable a,
                             organization b
                        where a.client_id = b.id
                          and b.blacklisted = 0
                          and a.status in (3,4,2,1)
                          and a.created_at between :dateFrom and :dateTo
                        group by client_id, d) a";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $dayOrderCount = $command->queryScalar();

        //Среднее количество заказов ресторанами в месяц за период
        $query = "select avg(cnt)
                        from (
                        select a.client_id, count(a.id) cnt, DATE_FORMAT(a.created_at,'%Y-%m') d
                        from $orderTable a,
                             organization b
                        where a.client_id = b.id
                          and b.blacklisted = 0
                          and a.status in (3,4,2,1)
                          and a.created_at between :dateFrom and :dateTo
                        group by client_id, d) a";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $monthOrderCount = $command->queryScalar();

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('misc', compact(
                'totalClients',
                'clientsStats',
                'vendorsWithGoodsCount',
                'productsCount',
                'productsOnMarketCount',
                'dayOrderCount',
                'monthOrderCount',
                'dateFilterFrom',
                'dateFilterTo'
            ));
        } else {
            return $this->render('misc', compact(
                'totalClients',
                'clientsStats',
                'vendorsWithGoodsCount',
                'productsCount',
                'productsOnMarketCount',
                'dayOrderCount',
                'monthOrderCount',
                'dateFilterFrom',
                'dateFilterTo'
            ));
        }

    }

    public function actionDynamics()
    {
        $params = Yii::$app->request->getQueryParams();

        $today = new \DateTime();
        //var_dump(Yii::$app->request->post());
        $start_date = !empty(Yii::$app->request->get("start_date")) ? Yii::$app->request->get("start_date") : $today->format('d.m.Y');
        $SearchModel = new DynamicUsageSearch();
        $DataProvider = $SearchModel->search($params);

        return $this->render('dynamics', compact('SearchModel', 'DataProvider', 'start_date'));

    }

    /**
     * Lists all mercuryStatistic.
     *
     * @return mixed
     */
    public function actionMercury()
    {

        $searchModel = new MercuryReportSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('mercury', compact('searchModel', 'dataProvider'));
    }

    public function actionMercActiveOrg()
    {
        $mercOrg = new OrgUseMercFrequently();
        $dataProviderIn = $mercOrg->getOrgList();
        $dataProviderIn->setSort([
            'attributes' => [
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                ],
                'id'
            ]
        ]);
        $dataProviderNotIn = $mercOrg->getOrgList(true);
        $dataProviderNotIn->setSort([
            'attributes' => [
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                ],
                'id'
            ]
        ]);
        return $this->render('merc-active-org', compact('dataProviderIn', 'dataProviderNotIn'));
    }
}
