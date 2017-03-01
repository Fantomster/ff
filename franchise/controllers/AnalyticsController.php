<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\Role;
use common\models\FranchiseeAssociate;
use common\models\Organization;
use common\models\User;
use common\models\Order;

/**
 * Description of AnalyticsController
 *
 * @author sharaf
 */
class AnalyticsController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            /* 'denyCallback' => function($rule, $action) {
              throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
              } */
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays analytics index (registrations)
     * 
     * @return mixed
     */
    public function actionIndex() {
        $orgTable = Organization::tableName();
        $fraTable = FranchiseeAssociate::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');

        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $day = $dt->format('w');
        $date = $dt->format('Y-m-d');

        $clientTotalCount = Organization::find()
                ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
                ->where([
                    "$orgTable.type_id" => Organization::TYPE_RESTAURANT,
                    "$fraTable.franchisee_id" => $this->currentFranchisee->id,
                ])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorTotalCount = Organization::find()
                ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
                ->where([
                    "$orgTable.type_id" => Organization::TYPE_SUPPLIER,
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
                    "$orgTable.type_id" => Organization::TYPE_RESTAURANT,
                    "$fraTable.franchisee_id" => $this->currentFranchisee->id,
                ])
                ->andWhere([">=", "$orgTable.created_at", $thisMonthStart])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorCountThisMonth = Organization::find()
                ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
                ->where([
                    "$orgTable.type_id" => Organization::TYPE_SUPPLIER,
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
                    "$orgTable.type_id" => Organization::TYPE_RESTAURANT,
                    "$fraTable.franchisee_id" => $this->currentFranchisee->id,
                ])
                ->andWhere([">=", "$orgTable.created_at", $thisDayStart])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorCountThisDay = Organization::find()
                ->leftJoin($fraTable, "$orgTable.id = $fraTable.organization_id")
                ->where([
                    "$orgTable.type_id" => Organization::TYPE_SUPPLIER,
                    "$fraTable.franchisee_id" => $this->currentFranchisee->id,
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

        $sql = "SELECT COUNT($orgTable.id) AS count, "
                . "SUM(CASE WHEN organization.type_id=1 THEN 1 ELSE 0 END) AS clients, SUM(CASE WHEN organization.type_id=2 THEN 1 ELSE 0 END) AS vendors, "
                . "YEAR($orgTable.created_at) AS year, MONTH($orgTable.created_at) AS month, DAY($orgTable.created_at) AS day FROM $orgTable "
                . "LEFT JOIN $fraTable ON $orgTable.id = $fraTable.organization_id "
                . "WHERE ($orgTable.created_at BETWEEN :dateFrom AND :dateTo) AND ($fraTable.franchisee_id=" . $this->currentFranchisee->id . ") "
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
     * Displays analytics for orders
     * 
     * @return mixed
     */
    public function actionPage2() {
        $orderTable = Order::tableName();
        $fraTable = FranchiseeAssociate::tableName();

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
            $select .= ", sum(case when `$orderTable`.status=$status then 1 else 0 end) as status_$status";
            $labelsTotal[] = $statusesList[$status];
            $colorsTotal[] = $colorsList[$status];
        }
        
        $query = "select " . $select . " from `$orderTable` left join $fraTable on $fraTable.organization_id=`$orderTable`.vendor_id "
                . "where $fraTable.franchisee_id = ".$this->currentFranchisee->id." and status <> " . Order::STATUS_FORMING;
        $command = Yii::$app->db->createCommand($query);
        $ordersStat = $command->queryAll()[0];
        
        $totalCount = $ordersStat["count"];
        unset($ordersStat["count"]);
        
        $thisMonthStart = $today->format('Y-m-01 00:00:00');
        $thisDayStart = $today->format('Y-m-d 00:00:00');
        
        $query = "select " . $select . " from `$orderTable` left join $fraTable on $fraTable.organization_id=`$orderTable`.vendor_id "
                . "where `$orderTable`.created_at > '$thisMonthStart'"." and $fraTable.franchisee_id = ".$this->currentFranchisee->id." and status <> " . Order::STATUS_FORMING;
        $command = Yii::$app->db->createCommand($query);
        $ordersStatThisMonth = $command->queryAll()[0];
        
        $totalCountThisMonth = $ordersStatThisMonth["count"];
        unset($ordersStatThisMonth["count"]);

        $query = "select " . $select . " from `$orderTable` left join $fraTable on $fraTable.organization_id=`$orderTable`.vendor_id "
                . "where `$orderTable`.created_at > '$thisDayStart'"." and $fraTable.franchisee_id = ".$this->currentFranchisee->id." and status <> " . Order::STATUS_FORMING;
        $command = Yii::$app->db->createCommand($query);
        $ordersStatThisDay = $command->queryAll()[0];

        $totalCountThisDay = $ordersStatThisDay["count"];
        unset($ordersStatThisDay["count"]);
        
        $query = "select aa.count as total, bb.first as first, aa.year as year, aa.month as month, aa.day as day 
            from (SELECT count(`$orderTable`.id) as count,year(created_at) as year, month(created_at) as month, day(created_at) as day 
                FROM `$orderTable` left join $fraTable on $fraTable.organization_id=`$orderTable`.vendor_id
                where $fraTable.franchisee_id = ".$this->currentFranchisee->id." and status <> 7 and created_at BETWEEN :dateFrom AND :dateTo group by year(created_at), month(created_at), day(created_at)) aa 
            left outer join (
                select count(b.id) as first,year(b.created_at) as year, month(b.created_at) as month, day(b.created_at) as day 
                from (select a.* 
                    from `f-keeper`.order a left join $fraTable on $fraTable.organization_id=a.vendor_id  
                    where $fraTable.franchisee_id = ".$this->currentFranchisee->id." and a.status <> 7 and a.created_at BETWEEN :dateFrom AND :dateTo group by a.client_id order by a.id) b group by year(b.created_at), month(b.created_at), day(b.created_at)
                ) bb
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
            return $this->renderPartial('page2', compact(
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
            return $this->render('page2', compact(
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

    /**
     * Displays analytics for turnover
     * 
     * @return mixed
     */
    public function actionPage3() {
        $orderTable = Order::tableName();
        $fraTable = FranchiseeAssociate::tableName();

        $today = new \DateTime();
        $dateFilterFrom = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";
        $dateFilterTo = !empty(Yii::$app->request->post("date2")) ? Yii::$app->request->post("date2") : $today->format('d.m.Y');
        
        $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
        $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
        $end = $dtEnd->add(new \DateInterval('P1D'));
        $date = $dt->format('Y-m-d');       
        
        $query = "SELECT truncate(sum(total_price),1) as spent,truncate(sum(total_price)/count(`$orderTable`.id),1) as cheque, year(created_at) as year, month(created_at) as month, day(created_at) as day "
                . "FROM `$orderTable` left join $fraTable on $fraTable.organization_id=`$orderTable`.vendor_id "
                . "where $fraTable.franchisee_id = ".$this->currentFranchisee->id." and status in (".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.") and created_at between :dateFrom and :dateTo "
                . "group by year(created_at), month(created_at), day(created_at)";
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
        
        $query = "SELECT truncate(sum(total_price),1) as total_month, truncate(sum(total_price)/count(distinct client_id),1) as spent,truncate(sum(total_price)/count(`$orderTable`.id),1) as cheque, year(created_at) as year, month(created_at) as month "
                . "FROM `$orderTable` left join $fraTable on $fraTable.organization_id=`$orderTable`.vendor_id "
                . "where $fraTable.franchisee_id = ".$this->currentFranchisee->id." and status in (".Order::STATUS_PROCESSING.",".Order::STATUS_DONE.",".Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT.",".Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR.") "
                . "group by year(created_at), month(created_at)";
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
        //jj
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('page3', compact(
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
            return $this->render('page3', compact(
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

}
