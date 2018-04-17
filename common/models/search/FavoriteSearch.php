<?php

namespace common\models\search;

use common\models\Order;
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
            'cbg.id as cbg_id',
            'cbg.product',
            'cbg.units',
            'COALESCE(cg.price, cbg.price) as price',
            'COALESCE(cg.cat_id, cbg.cat_id) as cat_id',
            'org.name',
            'cbg.ed',
            'curr.symbol',
            'cbg.note',
            'count(ord.id) as count'
        ]);
        //Толео заказа
        $query->from('`order_content` AS oc');
        //Заказ
        $query->innerJoin('`order` as ord', 'oc.order_id = ord.id');
        //Товар из главного каталога
        $query->innerJoin('`catalog_base_goods` as cbg', 'oc.product_id = cbg.id');
        //Каталоги, с которыми работает ресторан
        $query->innerJoin('`catalog` as cat', 'cbg.cat_id = cat.id AND (
           cbg.cat_id IN (
                SELECT cat_id FROM relation_supp_rest WHERE supp_org_id=cbg.supp_org_id AND rest_org_id = :client_id
           )
        )', [':client_id' => $clientId]);
        //Индивидуальный каталог
        $query->leftJoin('catalog_goods as cg', 'oc.product_id = cg.base_goods_id and cg.cat_id = cat.id');
        //Организация
        $query->innerJoin('organization AS org', 'cbg.supp_org_id = org.id');
        //Валюта
        $query->innerJoin('currency AS curr', 'cat.currency_id = curr.id');
        //Условия отбора
        $query->where('cbg.status = 1 AND cbg.deleted = 0');
        //Только эти заказы
        $query->andWhere(['in', 'ord.status', [
            Order::STATUS_PROCESSING,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_DONE
        ]]);

        if (!empty($this->searchString)) {
            $query->andWhere('cbg.product LIKE :searchString', [':searchString' => "%$this->searchString%"]);
        }
        //Группируем по товару
        $query->groupBy('cbg_id');

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
