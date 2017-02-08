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
        $dateFilter = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";

        $dt = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilter . " 00:00:00");
        $day = $dt->format('w');
        $date = $dt->format('Y-m-d');
        $today = new \DateTime();

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
        
        $today = [$clientCountThisDay, $vendorCountThisDay];

        $weekArray = [];
        $all = [];
        $clients = [];
        $vendors = [];
        $weeks = [];
        
        $start = $dt;
        if (!$day) {
            $day = 7;
        }
        $end = $start->add(new \DateInterval('P' . (8 - $day) . 'D'));

        while ($today > $start) {
            $from = $start->format('Y-m-d H:i:s');
            $to = $end->format('Y-m-d H:i:s');
            $clientCountForWeek = Organization::find()
                            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT])
                            ->andWhere(["between", "$orgTable.created_at", $from, $to])
                            ->groupBy(["$orgTable.id"])->count();
            $vendorCountForWeek = Organization::find()
                            ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                            ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER])
                            ->andWhere(["between", "$orgTable.created_at", $from, $to])
                            ->groupBy(["$orgTable.id"])->count();
            $countForWeek = $clientCountForWeek + $vendorCountForWeek;
            //if ($countForWeek) {
            $all[] = $countForWeek;
            $clients[] = $clientCountForWeek;
            $vendors[] = $vendorCountForWeek;
            $weeks[] = $start->format('jS M y') . '-' . (($today > $end) ? $end->format('jS M y') : $today->format('jS M y'));
            // }
            $start = $end;
            $end = $start->add(new \DateInterval('P7D'));
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('registered', compact(
                    'dateFilter', 
                    'clients',
                    'vendors',
                    'all',
                    'weeks',
                    'allTime',
                    'thisMonth',
                    'today',
                    'todayCount',
                    'thisMonthCount',
                    'allTimeCount'
                    ));
        } else {
            return $this->render('registered', compact(
                    'dateFilter', 
                    'clients',
                    'vendors',
                    'all',
                    'weeks',
                    'allTime',
                    'thisMonth',
                    'today',
                    'todayCount',
                    'thisMonthCount',
                    'allTimeCount'
                    ));
        }
    }

    public function actionOrders() {
        $dateFilter = !empty(Yii::$app->request->post("date")) ? Yii::$app->request->post("date") : "01.12.2016";

        $dt = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilter . " 00:00:00");
        $day = $dt->format('w');
        $date = $dt->format('Y-m-d');

        $orderCount = Order::find()
                ->where(['<>', 'status' , Order::STATUS_FORMING])
                ->count();
        $cancelledOrderCount = Order::find()
                ->where(['status' => Order::STATUS_CANCELLED])
                ->count();
        $acceptedOrderCount = Order::find()
                ->where(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_PROCESSING, Order::STATUS_DONE]])
                ->count();
        $rejectedOrderCount = Order::find()
                ->where(['status' => Order::STATUS_REJECTED])
                ->count();

        $orderCountSinceDate = Order::find()
                ->where(['<>', 'status' , Order::STATUS_FORMING])
                ->andWhere([">=", "created_at", $date])
                ->count();
        $cancelledOrderCountSinceDate = Order::find()
                ->where(['status' => Order::STATUS_CANCELLED])
                ->andWhere([">=", "created_at", $date])
                ->count();
        $acceptedOrderCountSinceDate = Order::find()
                ->where(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_PROCESSING, Order::STATUS_DONE]])
                ->andWhere([">=", "created_at", $date])
                ->count();
        $rejectedOrderCountSinceDate = Order::find()
                ->where(['status' => Order::STATUS_REJECTED])
                ->andWhere([">=", "created_at", $date])
                ->count();

        $weekArray = [];
        $today = new \DateTime();
        $start = $dt;
        if (!$day) {
            $day = 7;
        }
        $end = $start->add(new \DateInterval('P' . (8 - $day) . 'D'));

        while ($today > $start) {
            $from = $start->format('Y-m-d H:i:s');
            $to = $end->format('Y-m-d H:i:s');
            $orderCountForWeek = Order::find()
                ->where(['<>', 'status' , Order::STATUS_FORMING])
                ->andWhere(["between", "created_at", $from, $to])
                ->count();
            $cancelledOrderCountForWeek = Order::find()
                ->where(['status' => Order::STATUS_CANCELLED])
                ->andWhere(["between", "created_at", $from, $to])
                ->count();
            $acceptedOrderCountForWeek = Order::find()
                ->where(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_PROCESSING, Order::STATUS_DONE]])
                ->andWhere(["between", "created_at", $from, $to])
                ->count();
            $rejectedOrderCountForWeek = Order::find()
                ->where(['status' => Order::STATUS_REJECTED])
                ->andWhere(["between", "created_at", $from, $to])
                ->count();
            $weekArray[] = [
                'start' => $from,
                'end' => ($today > $end) ? $to : $today->format('Y-m-d H:i:s'),
                'count' => $orderCountForWeek,
                'cancelled' => $cancelledOrderCountForWeek,
                'accepted' => $acceptedOrderCountForWeek,
                'rejected' => $rejectedOrderCountForWeek,
            ];
            $start = $end;
            $end = $start->add(new \DateInterval('P7D'));
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('orders', compact(
                    'orderCount', 
                    'cancelledOrderCount', 
                    'acceptedOrderCount',
                    'rejectedOrderCount',
                    'orderCountSinceDate', 
                    'cancelledOrderCountSinceDate', 
                    'acceptedOrderCountSinceDate',
                    'rejectedOrderCountSinceDate',
                    'dateFilter', 
                    'weekArray'));
        } else {
            return $this->render('orders', compact(
                    'orderCount', 
                    'cancelledOrderCount', 
                    'acceptedOrderCount',
                    'rejectedOrderCount',
                    'orderCountSinceDate', 
                    'cancelledOrderCountSinceDate', 
                    'acceptedOrderCountSinceDate',
                    'rejectedOrderCountSinceDate',
                    'dateFilter', 
                    'weekArray'));
        }
    }
}
