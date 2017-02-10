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
        $dateFilter = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";

        $dt = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilter . " 00:00:00");
        $day = $dt->format('w');
        $date = $dt->format('Y-m-d');
        
        $orderTable = Order::tableName();
        
        $statuses = !empty(Yii::$app->request->post("statuses")) ? Yii::$app->request->post("statuses") : [Order::STATUS_DONE];
        $labelsTotal = [];
        $colorsTotal = [];
        $statusesList = Order::getStatusList();
        $colorsList = Order::getStatusColors();
        
        $select = "count(id) as count";
        
        foreach ($statuses as $status) {
            $status = (int)$status;
            $select .= ", sum(case when status=$status then 1 else 0 end) as status_$status";
            $labelsTotal[] = $statusesList[$status];
            $colorsTotal[] = $colorsList[$status];
        }
        
        $query = "select " . $select . " from `$orderTable` where 1";
        $command = Yii::$app->db->createCommand($query);
        $raw = $command->getRawSql();
        $ordersStat = $command->queryAll()[0];
        
        $totalCount = $ordersStat["count"];
        unset($ordersStat["count"]);
        
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact(
                    'labelsTotal',
                    'ordersStat',
                    'colorsTotal',
                    'statuses',
                    'totalCount'
                    ));
        } else {
            return $this->render('orders', compact(
                    'labelsTotal',
                    'ordersStat',
                    'colorsTotal',
                    'statuses',
                    'totalCount'
                    ));
        }
    }
}
