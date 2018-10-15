<?php

namespace common\models\search;

use common\models\Currency;
use common\models\OperatorCall;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class OrderOperatorSearch extends Order
{
    /**
     * Description
     * @var
     */
    public $user_id;

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'vendor_name',
            'client_name',
            'vendor_contact',
            'status_call_id',
            'comment',
            'status_call_id',
            'operator_updated_at'
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id'                  => \Yii::t('app', 'ID заказа'),
            'created_at'          => \Yii::t('app', 'Дата заказа'),
            'total_price'         => \Yii::t('app', 'Сумма Заказа'),
            'status'              => \Yii::t('app', 'Статус Заказа'),
            'vendor_name'         => \Yii::t('app', 'Поставщик'),
            'client_name'         => \Yii::t('app', 'Ресторан'),
            'vendor_contact'      => \Yii::t('app', 'Контакты поставщика'),
            'operator'            => \Yii::t('app', 'Действие'),
            'status_call_id'      => \Yii::t('app', 'Статус звонка'),
            'comment'             => \Yii::t('app', 'Комментарий'),
            'operator_updated_at' => \Yii::t('app', 'Дата обработки')
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        if (empty($params['OrderOperatorSearch']['status'])) {
            $status = [
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_REJECTED,
                Order::STATUS_CANCELLED
            ];
        } else {
            $status = $params['OrderOperatorSearch']['status'];
        }

        $query = (new Query())->select([
            'order.id as id',
            'order.created_at',
            'order.total_price',
            'order.status',
            'client.id as client_id',
            'vendor.id as vendor_id',
            '(CASE 
                WHEN client.legal_entity = \'\' THEN client.name
                WHEN client.legal_entity is null THEN client.name 
                ELSE client.legal_entity 
              END) as client_name',
            '(CASE 
                WHEN vendor.legal_entity = \'\' THEN vendor.name
                WHEN vendor.legal_entity is null THEN vendor.name 
                ELSE vendor.legal_entity 
              END) as vendor_name',
            'CONCAT(vendor.contact_name, \' \', vendor.phone)  as vendor_contact',
            'op.operator_id as operator',
            'profile.full_name as operator_name',
            'op.status_call_id',
            'op.comment',
            'op.updated_at as operator_updated_at',
            'c.iso_code'
        ])->from(Order::tableName())
            ->leftJoin(Organization::tableName() . ' as vendor', 'order.vendor_id = vendor.id')
            ->leftJoin(Organization::tableName() . ' as client', 'order.client_id = client.id')
            ->leftJoin(OperatorCall::tableName() . ' as op', 'op.order_id = order.id')
            ->leftJoin(User::tableName() . ' as user', 'user.id = op.operator_id')
            ->leftJoin(Profile::tableName() . ' as profile', 'profile.user_id = user.id')
            ->leftJoin(Currency::tableName() . ' as c', 'c.id = order.currency_id')
            ->where(['in', 'order.status', $status])
            ->andWhere('op.operator_id is null OR op.operator_id = :current_user', [':current_user' => $this->user_id]);

        $query->orderBy([
            'status'         => SORT_ASC,
            'operator_id'    => SORT_DESC,
            'status_call_id' => SORT_ASC,
            'created_at'     => SORT_DESC,
        ]);

        /**
         * Фильтр по статусу звонка
         */
        if (!empty($params['OrderOperatorSearch']['status_call_id'])) {
            $status = (int)$params['OrderOperatorSearch']['status_call_id'];
            if ($status == 1) {
                $query->andWhere([
                    'or',
                    'status_call_id is null',
                    'status_call_id = :scid'
                ],[':scid' => $status]);
            } else {
                $query->andWhere('status_call_id = :scid', [':scid' => $status]);
            }
        }


        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }

}