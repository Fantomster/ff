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
use yii\db\Query;
use yii\web\Controller;
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
            ->where([
                "$userTable.status"     => User::STATUS_ACTIVE,
                "$orgTable.type_id"     => Organization::TYPE_RESTAURANT,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorTotalCount = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status"     => User::STATUS_ACTIVE,
                "$orgTable.type_id"     => Organization::TYPE_SUPPLIER,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
            ->groupBy(["$orgTable.id"])
            ->count();
        $allTimeCount = $clientTotalCount + $vendorTotalCount;

        $allTime = [$clientTotalCount, $vendorTotalCount];

        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');

        $clientCountThisMonth = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status"     => User::STATUS_ACTIVE,
                "$orgTable.type_id"     => Organization::TYPE_RESTAURANT,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorCountThisMonth = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status"     => User::STATUS_ACTIVE,
                "$orgTable.type_id"     => Organization::TYPE_SUPPLIER,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $thisMonthCount = $clientCountThisMonth + $vendorCountThisMonth;

        $thisMonth = [$clientCountThisMonth, $vendorCountThisMonth];

        $clientCountThisDay = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status"     => User::STATUS_ACTIVE,
                "$orgTable.type_id"     => Organization::TYPE_RESTAURANT,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
            ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
            ->groupBy(["$orgTable.id"])
            ->count();
        $vendorCountThisDay = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status"     => User::STATUS_ACTIVE,
                "$orgTable.type_id"     => Organization::TYPE_SUPPLIER,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
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

        $clientsByDay = (new Query())->select([
            "count"   => "COUNT($orgTable.id)",
            "clients" => "SUM(CASE WHEN organization.type_id=1 THEN 1 ELSE 0 END)",
            "vendors" => "SUM(CASE WHEN organization.type_id=2 THEN 1 ELSE 0 END)",
            "year"    => "YEAR($orgTable.created_at)",
            "month"   => "MONTH($orgTable.created_at)",
            "day"     => "DAY($orgTable.created_at)",
        ])
            ->from($orgTable)
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status"     => User::USER_STATUS_ACTIVE,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED
            ])
            ->andWhere(["BETWEEN", "$orgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
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
            ->where(["$orgTable.blacklisted" => Organization::STATUS_WHITELISTED])
            ->andWhere(["<>", "$orderTable.status", OrderStatus::STATUS_FORMING])
            ->all()[0];

        $totalCount = $ordersStat["count"];
        unset($ordersStat["count"]);

        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');

        $ordersStatThisMonth = (new Query())->select($select)->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["$orgTable.blacklisted" => Organization::STATUS_WHITELISTED])
            ->andWhere([">", "$orderTable.created_at", $thisMonthStart])
            ->andWhere(["<>", "$orderTable.status", OrderStatus::STATUS_FORMING])
            ->all()[0];

        $totalCountThisMonth = $ordersStatThisMonth["count"];
        unset($ordersStatThisMonth["count"]);

        $ordersStatThisDay = (new Query())->select($select)->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["$orgTable.blacklisted" => Organization::STATUS_WHITELISTED])
            ->andWhere([">", "$orderTable.created_at", $thisDayStart])
            ->andWhere(["<>", "$orderTable.status", OrderStatus::STATUS_FORMING])
            ->all()[0];

        $totalCountThisDay = $ordersStatThisDay["count"];
        unset($ordersStatThisDay["count"]);

        $fromSelect = (new Query())->select([
            "count" => "count($orderTable.id)",
            "year"  => "year($orderTable.created_at)",
            "month" => "month($orderTable.created_at)",
            "day"   => "day($orderTable.created_at)",
        ])
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id=$orgTable.id")
            ->where(["<>", "$orderTable.status", ':qp0'])
            ->andWhere(['BETWEEN', "$orderTable.created_at", ':qp1', ':qp2'])
            ->andWhere(["$orgTable.blacklisted" => ':qp3'])
            ->groupBy([
                "year($orderTable.created_at)",
                "month($orderTable.created_at)",
                "day($orderTable.created_at)"
            ])
            ->createCommand()->sql;

        $leftJoinInnerSelect = (new Query())->select("*")
            ->from("$orderTable a")
            ->where(["<>", "a.status", ':qp0'])
            ->andWhere(['BETWEEN', "a.created_at", ':qp1', ':qp2'])
            ->groupBy(["a.client_id"])
            ->orderBy("a.id")
            ->createCommand()->sql;

        $leftJoinOuterSelect = (new Query())->select([
            "first" => "count(b.id)",
            "year"  => "year(b.created_at)",
            "month" => "month(b.created_at)",
            "day"   => "day(b.created_at)",
        ])
            ->from("($leftJoinInnerSelect) b")
            ->groupBy([
                "year(b.created_at)",
                "month(b.created_at)",
                "day(b.created_at)"
            ])
            ->createCommand()->sql;

        $ordersByDay = (new Query())->select([
            "total" => "aa.count",
            "first" => "bb.first",
            "year"  => "aa.year",
            "month" => "aa.month",
            "day"   => "aa.day",
        ])
            ->from("($fromSelect) aa")
            ->leftJoin("($leftJoinOuterSelect) bb", "aa.year = bb.year and aa.month=bb.month and aa.day=bb.day")
            ->params([
                ':qp0' => Order::STATUS_FORMING,
                ':qp1' => $dt->format('Y-m-d'),
                ':qp2' => $end->format('Y-m-d'),
                ':qp3' => Organization::STATUS_WHITELISTED,
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
        $statusesArray = OrderStatus::getStatusesArrayForBackend();

        $ordersByDay = (new Query())->select([
            "spent"  => "truncate(sum($orderTable.total_price), 1)",
            "cheque" => "truncate(sum($orderTable.total_price)/count($orderTable.id),1)",
            "year"   => "year($orderTable.created_at)",
            "month"  => "month($orderTable.created_at)",
            "day"    => "day($orderTable.created_at)"
        ])
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id = $orgTable.id")
            ->where(['in', "$orderTable.status", $statusesArray])
            ->andWhere(["$orgTable.blacklisted" => Organization::STATUS_WHITELISTED])
            ->andWhere(["BETWEEN", "$orderTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy([
                "year($orderTable.created_at)",
                "month($orderTable.created_at)",
                "day($orderTable.created_at)"
            ])->all();

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

        $money = (new Query())->select([
            "total_month" => "truncate(sum($orderTable.total_price), 1)",
            "spent"       => "truncate(sum($orderTable.total_price)/count(distinct $orderTable.client_id),1)",
            "cheque"      => "truncate(sum($orderTable.total_price)/count($orderTable.id),1)",
            "year"        => "year($orderTable.created_at)",
            "month"       => "month($orderTable.created_at)"
        ])
            ->from($orderTable)
            ->leftJoin($orgTable, "$orderTable.client_id = $orgTable.id")
            ->where(['in', "$orderTable.status", $statusesArray])
            ->andWhere(["$orgTable.blacklisted" => Organization::STATUS_WHITELISTED])
            ->groupBy([
                "year($orderTable.created_at)",
                "month($orderTable.created_at)",
            ])->all();

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
        $statusesArray = OrderStatus::getStatusesArrayForBackend();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));
        $date = $dt->format('Y-m-d');

        $totalClients = Organization::find()
            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
            ->where([
                "$userTable.status" => User::STATUS_ACTIVE,
                "$orgTable.type_id" => Organization::TYPE_RESTAURANT
            ])
            ->andWhere(['between', "$orgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy(["$orgTable.id"])
            ->count();

        $clientsWithOrders = (new Query())->select([
            "id"          => "$orgTable.id",
            "ordersCount" => "count($orderTable.id)",
        ])
            ->from($orgTable)
            ->leftJoin($userTable, "$orgTable.id=$userTable.organization_id")
            ->leftJoin($orderTable, "$orderTable.client_id = $orgTable.id")
            ->where([
                "$orgTable.type_id"     => Organization::TYPE_RESTAURANT,
                "$userTable.status"     => User::USER_STATUS_ACTIVE,
                "$orgTable.blacklisted" => Organization::STATUS_WHITELISTED,
            ])
            ->andWhere([
                'in', "$orderTable.status", $statusesArray
            ])
            ->andWhere(['between', "$orgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy([
                "$orgTable.id",
            ])->all();

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

        $vendorsInnerSelect = (new Query())->select([
            "org_id" => "$orgTable.id"
        ])
            ->from($orgTable)
            ->leftJoin($cbgTable, "$orgTable.id = $cbgTable.supp_org_id")
            ->where([
                "$cbgTable.deleted"     => ":qp0",
                "$orgTable.blacklisted" => ":qp1",
            ])
            ->andWhere(['between', "$orgTable.created_at", ":qp2", ":qp3"])
            ->groupBy([
                "$orgTable.id",
            ])
            ->createCommand()->sql;

        $vendorsWithGoodsCount = (new Query())->select([
            "count(org_id)"
        ])
            ->from("($vendorsInnerSelect) as tmp")
            ->params([
                ':qp0' => CatalogBaseGoods::DELETED_OFF,
                ':qp1' => Organization::STATUS_WHITELISTED,
                ':qp2' => $dt->format('Y-m-d'),
                ':qp3' => $end->format('Y-m-d'),
            ])
            ->count();

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
        $dayOrderCountInnerSelect = (new Query())->select([
            "a.client_id",
            "cnt" => "count(a.id)",
            "d"   => "DATE_FORMAT(a.created_at,'%Y-%m-%d')",
        ])
            ->from(["$orderTable as a", "$orgTable as b"])
            ->where("a.client_id=b.id AND b.blacklisted=:qp1 AND a.status IN (:qp2, :qp3, :qp4, :qp5) AND a.created_at BETWEEN :qp6 AND :qp7")
            ->groupBy([
                "client_id",
                "d"
            ])->createCommand()->sql;

        $fullDayOrderCount = (new Query())->select([
            "avg(cnt)"
        ])
            ->from("($dayOrderCountInnerSelect) as a")
            ->params([
                ':qp1' => Organization::STATUS_WHITELISTED,
                ':qp2' => Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                ':qp3' => Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                ':qp4' => Order::STATUS_PROCESSING,
                ':qp5' => Order::STATUS_DONE,
                ':qp6' => $dt->format('Y-m-d'),
                ':qp7' => $end->format('Y-m-d'),
            ])->createCommand()->getRawSql();
        $arr = explode("WHERE", $fullDayOrderCount);
        $secondDayOrderCount = str_replace("`", "", $arr[1]);
        $dayQuery = $arr[0] . 'WHERE' . $secondDayOrderCount;
        $dayOrderCount = (new Query())->createCommand()->setRawSql($dayQuery)->queryScalar();

        //Среднее количество заказов ресторанами в месяц за период
        $monthOrderCountInnerSelect = (new Query())->select([
            "a.client_id",
            "cnt" => "count(a.id)",
            "d"   => "DATE_FORMAT(a.created_at,'%Y-%m')",
        ])
            ->from(["$orderTable as a", "$orgTable as b"])
            ->where("a.client_id=b.id AND b.blacklisted=:qp1 AND a.status IN (:qp2, :qp3, :qp4, :qp5) AND a.created_at BETWEEN :qp6 AND :qp7")
            ->groupBy([
                "client_id",
                "d"
            ])->createCommand()->sql;

        $fullMonthOrderCount = (new Query())->select([
            "avg(cnt)"
        ])
            ->from("($monthOrderCountInnerSelect) as a")
            ->params([
                ':qp1' => Organization::STATUS_WHITELISTED,
                ':qp2' => Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                ':qp3' => Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                ':qp4' => Order::STATUS_PROCESSING,
                ':qp5' => Order::STATUS_DONE,
                ':qp6' => $dt->format('Y-m-d'),
                ':qp7' => $end->format('Y-m-d'),
            ])->createCommand()->getRawSql();
        $arr = explode("WHERE", $fullMonthOrderCount);
        $secondMonthOrderCount = str_replace("`", "", $arr[1]);
        $monthQuery = $arr[0] . 'WHERE' . $secondMonthOrderCount;
        $monthOrderCount = (new Query())->createCommand()->setRawSql($monthQuery)->queryScalar();

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
