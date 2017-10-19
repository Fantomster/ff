<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\Role;
use common\models\Organization;
use common\models\Order;
use backend\models\UserSearch;
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
class StatisticsController extends Controller {
    
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'registered', 'orders', 'turnover', 'misc'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionRegistered() {
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

        $sql = "SELECT COUNT($orgTable.id) AS count, "
                . "SUM(CASE WHEN organization.type_id=1 THEN 1 ELSE 0 END) AS clients, SUM(CASE WHEN organization.type_id=2 THEN 1 ELSE 0 END) AS vendors, "
                . "YEAR($orgTable.created_at) AS year, MONTH($orgTable.created_at) AS month, DAY($orgTable.created_at) AS day FROM $orgTable "
                . "LEFT JOIN $userTable ON $orgTable.id = $userTable.organization_id "
                . "WHERE ($userTable.status=1) AND ($orgTable.created_at BETWEEN :dateFrom AND :dateTo) AND $orgTable.blacklisted = 0 "
                . "GROUP BY YEAR($orgTable.created_at), MONTH($orgTable.created_at), DAY($orgTable.created_at)";
        $command = Yii::$app->db->createCommand($sql, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $clientsByDay = $command->queryAll();
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

    public function actionOrders() {
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
        
        $query = "select " . $select . " from `$orderTable` left join $orgTable on $orderTable.client_id=$orgTable.id where $orgTable.blacklisted = 0 and $orderTable.status <> " . Order::STATUS_FORMING;
        $command = Yii::$app->db->createCommand($query);
        $ordersStat = $command->queryAll()[0];
        
        $totalCount = $ordersStat["count"];
        unset($ordersStat["count"]);
        
        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');
        
        $query = "select " . $select . " from `$orderTable` left join $orgTable on $orderTable.client_id=$orgTable.id "
                . "where $orgTable.blacklisted = 0 and `$orderTable`.created_at > '$thisMonthStart'"." and status <> " . Order::STATUS_FORMING;
        $command = Yii::$app->db->createCommand($query);
        $ordersStatThisMonth = $command->queryAll()[0];
        
        $totalCountThisMonth = $ordersStatThisMonth["count"];
        unset($ordersStatThisMonth["count"]);

        $query = "select " . $select . " from `$orderTable` left join $orgTable on $orderTable.client_id=$orgTable.id "
                . "where $orgTable.blacklisted = 0 and `$orderTable`.created_at > '$thisDayStart'"." and status <> " . Order::STATUS_FORMING;
        $command = Yii::$app->db->createCommand($query);
        $ordersStatThisDay = $command->queryAll()[0];

        $totalCountThisDay = $ordersStatThisDay["count"];
        unset($ordersStatThisDay["count"]);
        
        $query = "select aa.count as total, bb.first as first, aa.year as year, aa.month as month, aa.day as day 
            from (SELECT count($orderTable.id) as count,year($orderTable.created_at) as year, month($orderTable.created_at) as month, day($orderTable.created_at) as day FROM `order` left join $orgTable on $orderTable.client_id=$orgTable.id where $orgTable.blacklisted=0 and $orderTable.status <> 7 and $orderTable.created_at BETWEEN :dateFrom AND :dateTo group by year($orderTable.created_at), month($orderTable.created_at), day($orderTable.created_at)) aa 
            left outer join (select count(b.id) as first,year(b.created_at) as year, month(b.created_at) as month, day(b.created_at) as day from (select * from `order` a where a.status <> 7 and a.created_at BETWEEN :dateFrom AND :dateTo group by a.client_id order by a.id) b group by year(b.created_at), month(b.created_at), day(b.created_at)) bb
            on aa.year = bb.year and aa.month=bb.month and aa.day=bb.day";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $ordersByDay = $command->queryAll();
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
    
    public function actionTurnover() {
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
        
        $query = "SELECT truncate(sum($orderTable.total_price),1) as spent,truncate(sum($orderTable.total_price)/count($orderTable.id),1) as cheque, year($orderTable.created_at) as year, month($orderTable.created_at) as month, day($orderTable.created_at) as day "
                . "FROM `order` LEFT JOIN $orgTable ON $orderTable.client_id = $orgTable.id "
                . "where $orderTable.status in (".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.") and $orgTable.blacklisted = 0 and $orderTable.created_at between :dateFrom and :dateTo "
                . "group by year($orderTable.created_at), month($orderTable.created_at), day($orderTable.created_at)";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $ordersByDay = $command->queryAll();
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
                . "FROM `order` LEFT JOIN $orgTable ON $orderTable.client_id = $orgTable.id "
                . "where $orderTable.status in (".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.") and $orgTable.blacklisted = 0 "
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
    
    public function actionMisc() {
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
        
        $query = "select $orgTable.id as id, count(`$orderTable`.id) as ordersCount from $orgTable "
                . "left join $userTable on $orgTable.id=$userTable.organization_id "
                . "left join `$orderTable` on `$orderTable`.client_id = $orgTable.id "
                . "where type_id=1 and $userTable.status=1 and `$orderTable`.status in (".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.") "
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
                    'deleted' => CatalogBaseGoods::DELETED_OFF, 
                    'market_place' => CatalogBaseGoods::MARKETPLACE_ON, 
                    'white_list' => Organization::WHITE_LIST_ON,
                    'status' => CatalogBaseGoods::STATUS_ON,
                        ])
                ->andWhere('category_id is not null')
                ->andWhere(['between', "$cbgTable.created_at", $dt->format('Y-m-d'), $end->format('Y-m-d')])
                ->count();
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('misc', compact(
                    'totalClients',
                    'clientsStats',
                    'vendorsWithGoodsCount',
                    'productsCount',
                    'productsOnMarketCount',
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
                    'dateFilterFrom', 
                    'dateFilterTo'
                    ));
        }
        
    }
}
