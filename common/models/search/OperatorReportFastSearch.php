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

class OperatorReportFastSearch extends Order
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
            coalesce(c.username, c.email) operator_name,
            count(a.order_id) cnt_order, 
            count(distinct d.order_id) cnt_order_changed"
        ])
            ->from(OperatorCall::tableName() . ' as a')
            ->leftJoin(User::tableName() . ' as c', 'c.id = a.operator_id')
            ->leftJoin("order_chat as d", "d.order_id = a.order_id")
            ->where("a.operator_id = c.id
                                and $timeCondition")
            ->groupBy("c.email")
            ->orderBy("a.created_at, email, status");

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }

}