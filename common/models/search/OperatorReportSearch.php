<?php

namespace common\models\search;

use common\models\Currency;
use common\models\OperatorCall;
use common\models\OperatorVendorComment;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class OperatorReportSearch extends Order
{
    /**
     * Description
     *
     * @var
     */
    public $user_id;
    public $date_from;
    public $date_to;

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /**
         * Фильтр по дате заказа
         */
        if (!empty($params['date_from']) && !empty($params['date_to'])) {
            $dateFrom = date('Y-m-d H:i:s', strtotime(trim($params['date_from'])));
            $dateTo = date('Y-m-d H:i:s', strtotime(trim($params['date_to'])));
            $timeCondition = "a.created_at between '$dateFrom' and '$dateTo'";
        } else {
            $timeCondition = "a.created_at between now() - interval 7 day and now()";
        }
        $query = (new Query())->select(["
            count(a.order_id) cnt_order, 
            count(distinct d.order_id) cnt_order_changed,
            coalesce(c.username, c.email) operator_name,
            DATE_FORMAT(a.created_at, '%Y-%m-%d') dt,
            count(distinct b.vendor_id) cnt_vendor, 
            case b.status
                when 1 then 'Ожидает подтверждения поставщика'
                when 3 then 'Выполняется'
                when 4 then 'Завершен'
                when 5 then 'Отклонен поставщиком'
                when 6 then 'Отменен'
                else b.status
            end status,
            status_call_id,
            round(avg(TIME_TO_SEC(TIMEDIFF(a.closed_at, a.created_at))) / 60, 2) avg_resolve_mins"
        ])
            ->from("`order` b")
            ->leftJoin(OperatorCall::tableName() . ' as a', 'a.order_id = b.id')
            ->leftJoin(User::tableName() . ' as c', 'c.id = a.operator_id')
            ->leftJoin("order_chat as d", "d.order_id = b.id 
                                and b.vendor_id = d.recipient_id 
                                and d.is_system = 1
                                and d.message like '%изменил детали заказа%'")
            ->where("a.order_id = b.id
                                and a.operator_id = c.id
                                and $timeCondition
                                and b.status in (3, 4)")
            ->groupBy("c.email, dt, status, status_call_id")
            ->orderBy("a.created_at, email, status");

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }

}