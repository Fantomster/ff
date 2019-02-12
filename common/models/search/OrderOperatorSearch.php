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
use yii\db\Expression;
use yii\db\Query;

class OrderOperatorSearch extends Order
{
    /**
     * Description
     *
     * @var
     */
    public $user_id;

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['client_name', 'vendor_name', 'vendor_contact'], 'safe'],
        ]);
    }

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
                Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                Order::STATUS_REJECTED,
                Order::STATUS_CANCELLED
            ];
        } else {
            $status = $params['OrderOperatorSearch']['status'];
        }

        $tblOrder = Order::tableName();
        $tblOrg = Organization::tableName();
        $tblUser = User::tableName();
        $tblProf = Profile::tableName();
        $tblOpC = OperatorCall::tableName();
        $tblOpVC = OperatorVendorComment::tableName();
        $tblCurr = Currency::tableName();

        $query = (new Query())->select([
            "$tblOrder.id as id",
            "$tblOrder.created_at",
            "$tblOrder.total_price",
            "$tblOrder.status",
            'client.id as client_id',
            'vendor.id as vendor_id',
            'client.name as client_title',
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
            "REPLACE(CONCAT(
                  vendor.contact_name,
                  ' ',
                  vendor.phone,
                  ', ',
                  (
                    SELECT
                      GROUP_CONCAT(' ', pm.full_name, ' ', pm.phone)
                    FROM relation_user_organization m
                      LEFT JOIN $tblUser um ON um.id = m.user_id
                      LEFT JOIN $tblProf pm ON pm.user_id = m.user_id
                    WHERE
                      m.organization_id = vendor.id
                      AND
                      um.status = 1
                  )
              ), ' ,  ', '') AS vendor_contact",
            'op.operator_id as operator',
            "$tblProf.full_name as operator_name",
            'op.status_call_id',
            'op.comment',
            'op.updated_at as operator_updated_at',
            'c.iso_code',
            'opvc.comment as vendor_comment'
        ])->from($tblOrder)
            ->leftJoin($tblOrg . ' as vendor', "$tblOrder.vendor_id = vendor.id")
            ->leftJoin($tblOrg . ' as client', "$tblOrder.client_id = client.id")
            ->leftJoin($tblOpC . ' as op', "op.order_id = $tblOrder.id")
            ->leftJoin($tblOpVC . ' as opvc', "opvc.vendor_id = $tblOrder.vendor_id")
            ->leftJoin($tblUser, "$tblUser.id = op.operator_id")
            ->leftJoin($tblProf, "$tblProf.user_id = $tblUser.id")
            ->leftJoin($tblCurr . ' as c', "c.id = $tblOrder.currency_id")
            ->where([
                'OR',
                "$tblOrder.status in (" . Order::STATUS_DONE . ', ' . Order::STATUS_PROCESSING . ') AND op.status_call_id != 3',
                [
                    'AND',
                    ['in', "$tblOrder.status", $status],
                ]
            ])
            ->andWhere('op.operator_id is null OR op.operator_id = :current_user', [':current_user' => $this->user_id])
            ->andWhere("$tblOrder.created_at > '2018-10-17 00:00:00'")
            //Показывать заказы только если они простояли 1 час
            ->andWhere([
                '<',
                "$tblOrder.created_at",
                \gmdate('Y-m-d H:i:s', strtotime("-1 hour"))
            ]);

        $query->orderBy([
            'status_call_id' => SORT_DESC,
            'status'         => SORT_ASC,
            'operator_id'    => SORT_DESC,
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
                ], [':scid' => $status]);
            } else {
                $query->andWhere('status_call_id = :scid', [':scid' => $status]);
            }
        }

        /**
         * Фильтр по названию ресторана
         */
        if (!empty($params['OrderOperatorSearch']['client_name'])) {
            $name = trim($params['OrderOperatorSearch']['client_name']);
            $query->andWhere([
                'or',
                'client.name LIKE :client_name',
                'client.name LIKE :client_name_',
                'client.name LIKE :_client_name_',
                'client.legal_entity LIKE :client_name_legal',
                'client.legal_entity LIKE :client_name_legal_',
                'client.legal_entity LIKE :_client_name_legal_',
            ], [
                ':client_name'         => $name,
                ':client_name_'        => $name . '%',
                ':_client_name_'       => '%' . $name . '%',
                ':client_name_legal'   => $name,
                ':client_name_legal_'  => $name . '%',
                ':_client_name_legal_' => '%' . $name . '%',
            ]);
        }

        /**
         * Фильтр по названию поставщика
         */
        if (!empty($params['OrderOperatorSearch']['vendor_name'])) {
            $name = trim($params['OrderOperatorSearch']['vendor_name']);
            $query->andWhere([
                'or',
                'vendor.name LIKE :vendor_name',
                'vendor.name LIKE :vendor_name_',
                'vendor.name LIKE :_vendor_name_',
                'vendor.legal_entity LIKE :vendor_name_legal',
                'vendor.legal_entity LIKE :vendor_name_legal_',
                'vendor.legal_entity LIKE :_vendor_name_legal_',
            ], [
                ':vendor_name'         => $name,
                ':vendor_name_'        => $name . '%',
                ':_vendor_name_'       => '%' . $name . '%',
                ':vendor_name_legal'   => $name,
                ':vendor_name_legal_'  => $name . '%',
                ':_vendor_name_legal_' => '%' . $name . '%',
            ]);
        }

        /**
         * Фильтр по контактам поставщика
         */
        if (!empty($params['OrderOperatorSearch']['vendor_contact'])) {
            $vendor_contact = trim($params['OrderOperatorSearch']['vendor_contact']);
            $query->andWhere([
                'or',
                'CONCAT(vendor.contact_name, \' \', vendor.phone) LIKE :vendor_contact',
                'CONCAT(vendor.contact_name, \' \', vendor.phone) LIKE :vendor_contact_',
                'CONCAT(vendor.contact_name, \' \', vendor.phone) LIKE :_vendor_contact_'
            ], [
                ':vendor_contact'   => $vendor_contact,
                ':vendor_contact_'  => $vendor_contact . '%',
                ':_vendor_contact_' => '%' . $vendor_contact . '%'
            ]);
        }

        /**
         * Фильтр по сумме заказа
         */
        if (!empty($params['OrderOperatorSearch']['total_price'])) {
            $total_price = (float)$params['OrderOperatorSearch']['total_price'];
            $query->andWhere([
                'or',
                "$tblOrder.total_price LIKE :total_price",
                "$tblOrder.total_price LIKE :total_price_",
            ], [
                ':total_price'  => $total_price,
                ':total_price_' => $total_price . '%',
            ]);
        }

        /**
         * Фильтр по дате заказа
         */
        if (!empty($params['OrderOperatorSearch']['created_at'])) {
            $created_at = trim($params['OrderOperatorSearch']['created_at']);
            $query->andWhere("CAST($tblOrder.created_at as DATE) = CAST(:created_at as DATE)", [
                ':created_at' => date('Y-m-d', strtotime($created_at))
            ]);
        }

        /**
         * Фильтр по дате обработки
         */
        if (!empty($params['OrderOperatorSearch']['operator_updated_at'])) {
            $operator_updated_at = trim($params['OrderOperatorSearch']['operator_updated_at']);
            $query->andWhere('CAST(op.updated_at as DATE) = CAST(:operator_updated_at as DATE)', [
                ':operator_updated_at' => date('Y-m-d', strtotime($operator_updated_at))
            ]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }

}
