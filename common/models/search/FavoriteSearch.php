<?php

namespace common\models\search;

use common\models\CatalogGoodsBlocked;
use common\models\Order;
use common\models\OrderStatus;
use common\models\Organization;
use Yii;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 * Description of FavoriteSearch
 *
 * @author elbabuino
 */
class FavoriteSearch extends \yii\base\Model
{

    public $searchString;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['searchString', 'id', 'product', 'order.created_at'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $clientId
     *
     * @return SqlDataProvider
     */
    public function search($params, $clientId)
    {
        $this->load($params);
        //Создаем запрос
        $query = (new Query())->select([
            'oc.product_id as cbg_id',
            'cbg.product',
            'cbg.units',
            'COALESCE(cg.price, cbg.price) as price',
            'COALESCE(cbg.cat_id, cg.cat_id) as cat_id',
            'org.name',
            'cbg.ed',
            'curr.symbol',
            'cbg.note',
            'count(oc.id) as count'
        ]);
        //Толео заказа
        $query->from('order_content AS oc');
        //Заказ
        $query->innerJoin(Order::tableName() . ' ord', 'oc.order_id = ord.id');
        //Товар из главного каталога
        $query->leftJoin('catalog_base_goods as cbg', 'oc.product_id = cbg.id');
        //Индивидуальный каталог
        $query->leftJoin('catalog_goods as cg', 'oc.product_id = cg.base_goods_id');
        //Организация
        $query->innerJoin('organization AS org', 'cbg.supp_org_id = org.id');
        //Валюта
        $query->innerJoin('currency AS curr', 'ord.currency_id = curr.id');
        //Условия отбора
        $query->where('cbg.deleted = 0 AND COALESCE(cbg.cat_id, cg.cat_id) != 0');

        //Только эти заказы
        $query->andWhere(['in', 'ord.status', [
            OrderStatus::STATUS_PROCESSING,
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            OrderStatus::STATUS_DONE
        ]]);

        if (!empty($this->searchString)) {
            $query->andWhere('cbg.product LIKE :searchString', [':searchString' => "%$this->searchString%"]);
        }

        $query->andWhere("ord.client_id = :cid", [':cid' => $clientId]);
        $query->addParams([':cid' => $clientId]);
        $query->andWhere([
            "OR",
            "cbg.cat_id IN (SELECT DISTINCT cat_id FROM relation_supp_rest WHERE rest_org_id = :cid)",
            "cg.cat_id IN (SELECT DISTINCT cat_id FROM relation_supp_rest WHERE rest_org_id = :cid)"
        ]);

        //Добавляем блокировку запрещенных товаров
        $blockedItems = implode(",", CatalogGoodsBlocked::getBlockedList($clientId));
        $query->andWhere(["AND",
            "oc.product_id NOT IN ($blockedItems)"
            ]);

        //Группируем по товару
        $query->groupBy('cbg_id');
        $query->having("cat_id IN (SELECT DISTINCT cat_id FROM relation_supp_rest WHERE rest_org_id = :cid)", [':cid' => $clientId]);

        //Выдача в датапровайдер
        $dataProvider = new SqlDataProvider([
            'sql' => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count(),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'count',
                ],
                'defaultOrder' => [
                    'count' => SORT_DESC
                ]
            ],
        ]);

        return $dataProvider;
    }

}
