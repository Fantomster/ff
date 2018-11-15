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

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = (new Query())->select(["
            count(a.order_id) cnt_order, 
            count(distinct d.order_id) cnt_order_changed,
            coalesce(c.username, c.email) operator_name,
            a.created_at,
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
                                and a.created_at between now() - interval 7 day and now()
                                and b.status in (3, 4)")
            ->groupBy("c.email, a.created_at, status, status_call_id")
            ->orderBy("a.created_at, email, status");
        /**
         * Фильтр по дате заказа
         */
        if (!empty($params['OperatorReportSearch']['created_at'])) {
            $created_at = trim($params['OperatorReportSearch']['created_at']);
            $query->andWhere('CAST(a.created_at as DATE) = CAST(:created_at as DATE)', [
                ':created_at' => date('Y-m-d', strtotime($created_at))
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }

}