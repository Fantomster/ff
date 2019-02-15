<?php
/**
 * Date: 14.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\classes;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\helpers\CurrencyHelper;
use api_web\helpers\WebApiHelper;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Currency;
use common\models\Organization;
use common\models\ProductAnalog;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\Query;

class AnalogWebApi extends WebApi
{
    public $arAvailableFields = [
        'product_name',
        'vendor_name'
    ];

    /**
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;
        $sort = $request['sort'] ?? null;

        $query = new Query();
        $query->select([
            'product_id'   => 'cbg.id',
            'product_name' => 'cbg.product',
            'article'      => 'cbg.article',
            'vendor_id'    => 'o.id',
            'vendor_name'  => 'o.name',
            'price'        => 'cg.price',
            'coefficient'  => 'pa.coefficient',
            'ed'           => 'cbg.ed',
            'currency_id'  => 'cur.id',
            'currency_sym' => 'cur.symbol',
            'group_id'     => new Expression('COALESCE(pa.parent_id, pa.id)'),
            'sort_value'   => 'pa.sort_value'
        ])
            ->from(Catalog::tableName() . ' as cat')
            ->innerJoin(CatalogBaseGoods::tableName() . ' as cbg', "cat.id = cbg.cat_id")
            ->innerJoin(CatalogGoods::tableName() . ' as cg', "cbg.id = cg.base_goods_id")
            ->leftJoin(ProductAnalog::tableName() . ' as pa', "pa.product_id = cbg.id")
            ->leftJoin(Currency::tableName() . ' as cur', "cur.id = cat.currency_id")
            ->innerJoin(Organization::tableName() . ' as o', "cbg.supp_org_id = o.id")
            ->andWhere([
                'cat.id' => $this->user->organization->getCatalogsLazyVendor()
            ]);

        if ($sort && in_array(ltrim($sort, '-'), $this->arAvailableFields)) {
            $sortField = ltrim($sort, '-');
            $sortDirection = SORT_ASC;
            if (strpos($sort, '-') !== false) {
                $sortDirection = SORT_DESC;
            }

            if ($sortField == 'vendor_name') {
                $query->orderBy([
                    $sortField     => $sortDirection,
                    'product_name' => SORT_ASC
                ]);
            } else {
                $query->orderBy([$sortField => $sortDirection]);
            }
        } else {
            $query->orderBy(['cbg.product' => SORT_ASC]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $items = [];
        $defaultCurrency = Currency::findOne(Registry::DEFAULT_CURRENCY_ID);
        $result = $dataProvider->models;
        if ($result) {
            foreach (WebApiHelper::generator($result) as $row) {
                $items[] = $this->prepareRow($row, $defaultCurrency);
            }
        }

        return [
            "items"      => $items,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
    }

    /**
     * Вренуть список аналогов для продукта
     *
     * @param $request
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function getProductAnalogList($request)
    {
        $this->validateRequest($request, ['product_id']);

        $groupId = (new Query())
            ->select(["gid" => new Expression('COALESCE(parent_id, id)')])
            ->from(ProductAnalog::tableName())
            ->where([
                'client_id'  => $this->user->organization_id,
                'product_id' => trim($request['product_id'])
            ]);

        $query = new Query();
        $result = $query
            ->select([
                'product_id'   => 'cbg.id',
                'product_name' => 'cbg.product',
                'article'      => 'cbg.article',
                'vendor_id'    => 'o.id',
                'vendor_name'  => 'o.name',
                'price'        => 'cg.price',
                'coefficient'  => 'pa.coefficient',
                'ed'           => 'cbg.ed',
                'currency_id'  => 'cur.id',
                'currency_sym' => 'cur.symbol',
                'group_id'     => new Expression('COALESCE(pa.parent_id, pa.id)'),
                'sort_value'   => 'pa.sort_value'
            ])
            ->from(ProductAnalog::tableName() . ' as pa')
            ->innerJoin(CatalogBaseGoods::tableName() . ' as cbg', "pa.product_id = cbg.id")
            ->innerJoin(CatalogGoods::tableName() . ' as cg', "cbg.id = cg.base_goods_id")
            ->innerJoin(Catalog::tableName() . ' as cat', "cat.id = cg.cat_id")
            ->leftJoin(Currency::tableName() . ' as cur', "cur.id = cat.currency_id")
            ->innerJoin(Organization::tableName() . ' as o', "cbg.supp_org_id = o.id")
            ->andWhere(['COALESCE(pa.parent_id, pa.id)' => $groupId])
            ->orderBy(['sort_value' => SORT_ASC])
            ->all();

        $items = [];
        $defaultCurrency = Currency::findOne(Registry::DEFAULT_CURRENCY_ID);

        if ($result) {
            foreach (WebApiHelper::generator($result) as $row) {
                $items[] = $this->prepareRow($row, $defaultCurrency);
            }
        }

        return ["items" => $items];
    }

    /**
     * @param $row
     * @param $c
     * @return array
     */
    private function prepareRow($row, $c)
    {
        $r = [
            'product'  => null,
            'vendor'   => null,
            'currency' => null,
        ];

        if ($row['product_id']) {
            $r['product'] = [
                'id'           => (int)$row['product_id'],
                'name'         => $row['product_name'],
                'ed'           => $row['ed'],
                'price'        => CurrencyHelper::asDecimal($row['price']),
                'article'      => $row['article'],
                'coefficient'  => $row['coefficient'] ?? null,
                'analog_group' => $row['group_id'] ? (int)$row['group_id'] : null,
                'sort_value'   => $row['sort_value'] ? (int)$row['sort_value'] : null,
            ];
        }

        if ($row['vendor_id']) {
            $r['vendor'] = [
                'id'   => (int)$row['vendor_id'],
                'name' => $row['vendor_name']
            ];
        }

        if ($row['currency_id']) {
            $r['currency'] = [
                'id'     => (int)$row['currency_id'],
                'symbol' => $row['currency_sym']
            ];
        } else {
            $r['currency'] = [
                'id'     => $c->id,
                'symbol' => $c->symbol
            ];
        }

        return $r;
    }
}
