<?php

namespace common\models\search;

use common\models\CatalogGoodsBlocked;
use common\models\Order;
use common\models\Organization;
use Yii;
use yii\db\Query;
use yii\db\Expression;

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

        $tblOrder        = Order::tableName();
        $tblOrderContent = \common\models\OrderContent::tableName();
        $tblCBG          = \common\models\CatalogBaseGoods::tableName();
        $tblCG           = \common\models\CatalogGoods::tableName();
        $tblOrg          = Organization::tableName();
        $tblCurr         = \common\models\Currency::tableName();
        $tblRSR          = \common\models\RelationSuppRest::tableName();

        $orderStatuses = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
        ];

        $query = (new Query())->select([
                    "cbg_id"  => 'oc.product_id',
                    "product" => 'cbg.product',
                    "units"   => 'cbg.units',
                    "price"   => new Expression('COALESCE(cg.price, cbg.price)'),
                    "cat_id"  => new Expression('COALESCE(cbg.cat_id, cg.cat_id)'),
                    "name"    => 'org.name',
                    "ed"      => 'cbg.ed',
                    "symbol"  => 'curr.symbol',
                    "note"    => 'cbg.note',
                    "count"   => new Expression('count(oc.id)')
                ])
                ->from(["oc" => $tblOrderContent])
                ->leftJoin(["ord" => $tblOrder], 'oc.order_id = ord.id')
                ->leftJoin(["cbg" => $tblCBG], 'oc.product_id = cbg.id')
                ->leftJoin(["cg" => $tblCG], 'oc.product_id = cg.base_goods_id')
                ->leftJoin(["org" => $tblOrg], 'cbg.supp_org_id = org.id')
                ->leftJoin(["curr" => $tblCurr], 'ord.currency_id = curr.id')
                ->where([
            "and",
            ["cbg.deleted" => 0],
            ["!=", new Expression("COALESCE(cbg.cat_id, cg.cat_id)"), 0],
            ["ord.status" => $orderStatuses],
            ["ord.client_id" => $clientId],
        ]);

        $subQueryCatIds = (new Query())
                ->select(["cat_id"])
                ->from($tblRSR)
                ->where(['rest_org_id' => $clientId])
                ->distinct();

        $query->andWhere([
            "or",
            ["cbg.cat_id" => $subQueryCatIds],
            ["cg.cat_id" => $subQueryCatIds],
        ]);

        //Добавляем блокировку запрещенных товаров
        $blockedItems = CatalogGoodsBlocked::getBlockedList($clientId);
        $query->andWhere(["not in", "oc.product_id", $blockedItems]);

        $query->andFilterWhere(["like", "cbg.product", $this->searchString]);

        //Группируем по товару
        $query->groupBy(['cbg_id']);
        $query->having(["cat_id" => $subQueryCatIds]);

        //Выдача в датапровайдер
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort'       => [
                'attributes'   => [
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
