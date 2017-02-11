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

/**
 * Description of StatisticsController
 *
 * @author sharaf
 */
class StatisticsController extends Controller {
    
    private $blacklist = '(1,2,5,16,63,88,99,108,111,114,116,272,333,526,673,784,824)';

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'registered', 'orders'],
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
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorTotalCount = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER])
                ->groupBy(["$orgTable.id"])
                ->count();
        $allTimeCount = $clientTotalCount + $vendorTotalCount;

        $allTime = [$clientTotalCount, $vendorTotalCount];
        
        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');
        
        $clientCountThisMonth = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT])
                ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorCountThisMonth = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER])
                ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
                ->groupBy(["$orgTable.id"])
                ->count();
        $thisMonthCount = $clientCountThisMonth + $vendorCountThisMonth;
        
        $thisMonth = [$clientCountThisMonth, $vendorCountThisMonth];

        $clientCountThisDay = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT])
                ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorCountThisDay = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER])
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
                . "WHERE ($userTable.status=1) AND ($orgTable.created_at BETWEEN :dateFrom AND :dateTo) "
                . "GROUP BY YEAR($orgTable.created_at), MONTH($orgTable.created_at), DAY($orgTable.created_at)";
        $command = Yii::$app->db->createCommand($sql, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $clientsByDay = $command->queryAll();
        $dayLabels = [];
        $dayStats = [];
        foreach ($clientsByDay as $day) {
            $dayLabels[] = $day["day"] . " " . date('M', strtotime("2000-$day[month]-01")) . " " . $day["year"];
            $dayStats[] = $day["count"];
            $clients[] = $day["clients"];
            $vendors[] = $day["vendors"];
        }
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('registered', compact(
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
        
        $query = "select " . $select . " from `$orderTable` where created_by_id not in ".$this->blacklist;
        $command = Yii::$app->db->createCommand($query);
        $ordersStat = $command->queryAll()[0];
        
        $totalCount = $ordersStat["count"];
        unset($ordersStat["count"]);
        
        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');
        
        $query = "select " . $select . " from `$orderTable` "
                . "where created_by_id not in ".$this->blacklist." and `$orderTable`.created_at > '$thisMonthStart'";
        $command = Yii::$app->db->createCommand($query);
        $ordersStatThisMonth = $command->queryAll()[0];
        
        $totalCountThisMonth = $ordersStatThisMonth["count"];
        unset($ordersStatThisMonth["count"]);

        $query = "select " . $select . " from `$orderTable` "
                . "where created_by_id not in ".$this->blacklist." and `$orderTable`.created_at > '$thisDayStart'";
        $command = Yii::$app->db->createCommand($query);
        $ordersStatThisDay = $command->queryAll()[0];

        $totalCountThisDay = $ordersStatThisDay["count"];
        unset($ordersStatThisDay["count"]);
        
        $query = "SELECT count(id) as count, year(created_at) as year, month(created_at) as month, day(created_at) as day FROM `f-keeper`.order "
                . "where created_by_id not in ".$this->blacklist." and status <> 7 and created_at BETWEEN :dateFrom AND :dateTo "
                . "group by year(created_at), month(created_at), day(created_at)";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $ordersByDay = $command->queryAll();
        $dayLabels = [];
        $dayStats = [];
        foreach ($ordersByDay as $order) {
            $dayLabels[] = $order["day"] . " " . date('M', strtotime("2000-$order[month]-01")) . " " . $order["year"];
            $dayStats[] = $order["count"];
        }
        
        $query = "select count(b.id) as count,year(b.created_at) as year, month(b.created_at) as month, day(b.created_at) as day "
                . "from (select * from `f-keeper`.order a where a.created_by_id not in ".$this->blacklist." and a.status <> 7 group by a.client_id order by a.id and a.created_at BETWEEN :dateFrom AND :dateTo) b "
                . "group by year(b.created_at), month(b.created_at), day(b.created_at)";
        $command = Yii::$app->db->createCommand($query, [':dateFrom' => $dt->format('Y-m-d'), ':dateTo' => $end->format('Y-m-d')]);
        $firstOrdersByDay = $command->queryAll();
        $firstDayStats = [];
        foreach ($firstOrdersByDay as $order) {
            $firstDayStats[] = $order["count"];
        }
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact(
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
}
