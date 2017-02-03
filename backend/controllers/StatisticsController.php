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
        $totalCount = $clientTotalCount + $vendorTotalCount;

        $clientCountSinceDate = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_RESTAURANT])
                ->andWhere([">=", "$orgTable.created_at", $date])
                ->groupBy(["$orgTable.id"])
                ->count();
        $vendorCountSinceDate = Organization::find()
                ->leftJoin($userTable, "$orgTable.id = $userTable.organization_id")
                ->where(["$userTable.status" => User::STATUS_ACTIVE, "$orgTable.type_id" => Organization::TYPE_SUPPLIER])
                ->andWhere([">=", "$orgTable.created_at", $date])
                ->groupBy(["$orgTable.id"])
                ->count();
        $countSinceDate = $clientCountSinceDate + $vendorCountSinceDate;

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
            $weekArray[] = [
                'start' => $from,
                'end' => ($today > $end) ? $to : $today->format('Y-m-d H:i:s'),
                'count' => $countForWeek,
                'clientCount' => $clientCountForWeek,
                'vendorCount' => $vendorCountForWeek,
            ];
            // }
            $start = $end;
            $end = $start->add(new \DateInterval('P7D'));
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('registered', compact(
                                    'totalCount', 'clientTotalCount', 'vendorTotalCount', 'countSinceDate', 'clientCountSinceDate', 'vendorCountSinceDate', 'dateFilter', 'weekArray'));
        } else {
            return $this->render('registered', compact(
                                    'totalCount', 'clientTotalCount', 'vendorTotalCount', 'countSinceDate', 'clientCountSinceDate', 'vendorCountSinceDate', 'dateFilter', 'weekArray'));
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
                ->where(['status' => [Order::STATUS_PROCESSING, Order::STATUS_DONE]])
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
                ->where(['status' => [Order::STATUS_PROCESSING, Order::STATUS_DONE]])
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
