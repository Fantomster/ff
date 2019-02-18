<?php
/**
 * Date: 14.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\classes;

use api_web\components\{Registry, WebApi};
use api_web\exceptions\ValidationException;
use api_web\helpers\{CurrencyHelper, WebApiHelper};
use common\models\{Catalog,
    CatalogBaseGoods,
    CatalogGoods,
    Currency,
    Organization,
    Preorder,
    ProductAnalog};
use yii\data\{ActiveDataProvider, Pagination};
use yii\db\{Expression, Query};
use yii\helpers\ArrayHelper;

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
            'units'        => 'cbg.units',
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
            ->leftJoin(ProductAnalog::tableName() . ' as pa', "pa.product_id = cbg.id AND pa.client_id = :cid", [
                ":cid" => $this->user->organization->id
            ])
            ->leftJoin(Currency::tableName() . ' as cur', "cur.id = cat.currency_id")
            ->innerJoin(Organization::tableName() . ' as o', "cbg.supp_org_id = o.id")
            ->andWhere("cg.price is not null")
            ->andWhere([
                'cat.id'   => explode(',', $this->user->organization->getCatalogs()),
                'cat.type' => Catalog::CATALOG
            ])
            ->groupBy('cbg.id');

        if (isset($request['search'])) {
            if (isset($request['search']['vendor']) && !empty($request['search']['vendor'])) {
                $query->andWhere(['o.id' => $request['search']['vendor']]);
            }
        }

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
     * @return array
     */
    public function getListSortFields()
    {
        return [
            'product_name'  => \Yii::t('api_web', 'analog_web_api.sort.product_name', ['ru' => 'Наименованию А-Я']),
            '-product_name' => \Yii::t('api_web', 'analog_web_api.sort._product_name', ['ru' => 'Наименованию Я-А']),
            'vendor_name'   => \Yii::t('api_web', 'analog_web_api.sort.vendor_name', ['ru' => 'Поставщику']),
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

        $preorder_id = $request['preorder_id'] ?? null;

        $query = (new Query())->select([
            'product_id'   => 'cbg.id',
            'product_name' => 'cbg.product',
            'article'      => 'cbg.article',
            'units'        => 'cbg.units',
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
            ->innerJoin(Organization::tableName() . ' as o', "cbg.supp_org_id = o.id")
            ->leftJoin(Currency::tableName() . ' as cur', "cur.id = cat.currency_id")
            ->andWhere(['COALESCE(pa.parent_id, pa.id)' => $groupId])
            ->orderBy(['sort_value' => SORT_ASC])
            ->groupBy('cbg.id');

        if ($preorder_id) {
            $query->andWhere(['!=', 'pa.product_id', $request['product_id']]);
        }

        $result = $query->all();
        $items = [];
        $defaultCurrency = Currency::findOne(Registry::DEFAULT_CURRENCY_ID);

        if ($result) {
            if (!is_null($preorder_id)) {
                $preOrder = Preorder::findOne($preorder_id);
            }

            foreach (WebApiHelper::generator($result) as $row) {
                $r = $this->prepareRow($row, $defaultCurrency);
                if (isset($preOrder)) {
                    $r['product']['quantity'] = $preOrder->getQuantityWithCoefficient($row['product_id']);
                }
                $items[] = $r;
            }
        }

        return ["items" => $items];
    }

    /**
     * Вренуть список групп аналогов
     *
     * @param $request
     * @return array
     * @throws
     */
    public function getListGroup($request)
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
            'group_id'     => 'pa.id',
            'sort_value'   => 'pa.sort_value',
            'analog_count' => 'COUNT(p_count.id)'
        ])
            ->from(ProductAnalog::tableName() . ' as pa')
            ->leftJoin(ProductAnalog::tableName() . " as p_count", "p_count.parent_id = pa.id")
            ->innerJoin(CatalogBaseGoods::tableName() . ' as cbg', "pa.product_id = cbg.id")
            ->innerJoin(CatalogGoods::tableName() . ' as cg', "cbg.id = cg.base_goods_id")
            ->innerJoin(Catalog::tableName() . ' as cat', "cat.id = cg.cat_id")
            ->leftJoin(Currency::tableName() . ' as cur', "cur.id = cat.currency_id")
            ->innerJoin(Organization::tableName() . ' as o', "cbg.supp_org_id = o.id")
            ->where(['pa.client_id' => $this->user->organization_id])
            ->andWhere('pa.parent_id is NULL')
            ->groupBy('pa.id')
            ->orderBy([
                'product_name' => SORT_ASC,
                'pa.id'        => SORT_ASC
            ]);

        if (isset($request['search'])) {
            if (isset($request['search']['vendor']) && !empty($request['search']['vendor'])) {
                $query->andWhere(['o.id' => $request['search']['vendor']]);
            }
        }

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

        $result = $dataProvider->models;
        $items = [];
        $defaultCurrency = Currency::findOne(Registry::DEFAULT_CURRENCY_ID);

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
     * @param $request
     * @return array
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function saveGroup($request)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $deleteItems = [];
            $firstProduct = current($request);
            $analogs = $this->findAnalogGroup($firstProduct['analog_group']);
            if (empty($analogs)) {
                $this->createAnalogGroup($request);
            } else {
                $deleteItems = $this->updateAnalogGroup($request, $analogs, $firstProduct['analog_group']);
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }

        $items = $this->getProductAnalogList(['product_id' => $firstProduct['id']])['items'];

        return [
            'items'        => $items,
            'items_delete' => $deleteItems
        ];
    }

    /**
     * @param $id
     * @return array|ProductAnalog[]|\yii\db\ActiveRecord[]
     */
    private function findAnalogGroup($id)
    {
        return ProductAnalog::find()->where([
            'OR',
            ['=', 'id', $id],
            ['=', 'parent_id', $id]
        ])
            ->orderBy(['sort_value' => SORT_ASC])
            ->all();
    }

    /**
     * @param $products
     * @throws ValidationException
     */
    private function createAnalogGroup($products): void
    {
        ArrayHelper::multisort($products, 'sort_value');
        $parent_id = null;

        foreach ($products as $product) {
            $model = new ProductAnalog();
            $model->product_id = (int)$product['id'];
            $model->client_id = $this->user->organization_id;
            $model->sort_value = (int)$product['sort_value'];
            $model->coefficient = $product['coefficient'] ?? 1;
            $model->parent_id = $parent_id;

            if ($model->save()) {
                $parent_id = $parent_id ?? $model->id;
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        }
    }

    /**
     * @param $products
     * @param $analogs
     * @param $oldGroupId
     * @return array
     * @throws ValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function updateAnalogGroup($products, $analogs, $oldGroupId)
    {
        $deleteProducts = [];
        $productsIds = ArrayHelper::getColumn($products, 'id');
        ArrayHelper::multisort($products, 'sort_value');
        /** @var ProductAnalog $model */
        foreach ($analogs as $model) {
            if (!in_array($model->product_id, $productsIds)) {
                $deleteProducts[] = $model->product_id;
                $model->delete();
            }
        }

        foreach ($products as $product) {
            $model = ProductAnalog::findOne([
                'client_id'  => $this->user->organization_id,
                'product_id' => $product['id']
            ]);
            if (!$model) {
                $model = new ProductAnalog();
                $model->product_id = (int)$product['id'];
                $model->client_id = $this->user->organization_id;
            }

            $model->sort_value = (int)$product['sort_value'];
            $model->coefficient = $product['coefficient'] ?? 1;
            $model->parent_id = $oldGroupId;

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }

        $firstProductId = current($products)['id'];
        $parentModel = ProductAnalog::findOne([
            'client_id'  => $this->user->organization_id,
            'product_id' => $firstProductId
        ]);

        ProductAnalog::updateAll(['parent_id' => $parentModel->id], [
            'client_id'  => $this->user->organization_id,
            'product_id' => $productsIds
        ]);

        $parentModel->parent_id = null;
        if (!$parentModel->save()) {
            throw new ValidationException($parentModel->getFirstErrors());
        }

        return $deleteProducts;
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

        if (isset($row['analog_count'])) {
            $r['analog_count'] = (int)$row['analog_count'];
        }

        if ($row['product_id']) {
            $r['product'] = [
                'id'           => (int)$row['product_id'],
                'name'         => $row['product_name'],
                'ed'           => $row['ed'],
                'units'        => round($row['units'] ?? 1, 3),
                'price'        => CurrencyHelper::asDecimal($row['price']),
                'article'      => $row['article'],
                'coefficient'  => $row['coefficient'] ? round($row['coefficient'], 6) : null,
                'analog_group' => $row['group_id'] ? (int)$row['group_id'] : null,
                'sort_value'   => $row['sort_value'] ? (int)$row['sort_value'] : null,
                'quantity'     => $row['quantity'] ?? 0,
            ];
        }

        if ($row['vendor_id']) {
            $vendor = Organization::findOne((int)$row['vendor_id']);
            $r['vendor'] = WebApiHelper::prepareOrganization($vendor);
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
