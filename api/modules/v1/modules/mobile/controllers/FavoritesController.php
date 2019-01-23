<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\modules\v1\modules\mobile\resources\Catalog;
use api\modules\v1\modules\mobile\resources\CatalogGoods;
use api\modules\v1\modules\mobile\resources\Currency;
use api\modules\v1\modules\mobile\resources\OrderContent;
use api\modules\v1\modules\mobile\resources\Organization;
use api\modules\v1\modules\mobile\resources\RelationSuppRest;
use common\models\Order;
use common\models\User;
use Yii;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\SqlDataProvider;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class FavoritesController extends ActiveController
{

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\CatalogBaseGoods';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
        ];
    }

    /**
     * @param $id
     * @return CatalogBaseGoods
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = CatalogBaseGoods::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }

    /**
     * @return SqlDataProvider
     * @throws \Throwable
     */
    public function prepareDataProvider()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $params = new \api\modules\v1\modules\mobile\resources\FavoriteSearch();

        $cbgIds = (new Query())
            ->select('cat_id')
            ->from(RelationSuppRest::tableName())
            ->where('supp_org_id = cbg.supp_org_id')
            ->andWhere(['rest_org_id' => $client->id])
            ->createCommand()
            ->getRawSql();

        $query2 = (new Query())
            ->select([
                "cbg.id as id",
                "cbg.product",
                "cbg.units",
                "cg.price",
                "cg.cat_id",
                "cbg.article",
                "cbg.supp_org_id",
                "cbg.category_id",
                "org.name as organization_name",
                "cbg.ed",
                "curr.symbol",
                "cbg.note",
            ])
            ->from(['oc' => OrderContent::tableName()])
            ->leftJoin(['ord' => Order::tableName()], 'ord.id = oc.order_id')
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'oc.product_id = cbg.id')
            ->leftJoin(['cg' => CatalogGoods::tableName()], "cg.base_goods_id = oc.product_id AND (cg.cat_id IN ({$cbgIds}))")
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cg.cat_id = cat.id')
            ->innerJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->andWhere([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ])
            ->groupBy('cbg.id');

        $query1 = (new Query())
            ->select([
                "cbg.id AS id",
                "cbg.product",
                "cbg.units",
                "cbg.price",
                "cbg.cat_id",
                "cbg.article",
                "cbg.supp_org_id",
                "cbg.category_id",
                "org.name AS organization_name",
                "cbg.ed",
                "curr.symbol",
                "cbg.note"
            ])
            ->from(['oc' => OrderContent::tableName()])
            ->leftJoin(['ord' => Order::tableName()], 'ord.id = oc.order_id')
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'oc.product_id = cbg.id')
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], "cbg.cat_id = cat.id AND (cbg.cat_id IN ({$cbgIds}))")
            ->innerJoin(['curr' => Currency::tableName()], "cat.currency_id = curr.id")
            ->andWhere([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ])
            ->groupBy('cbg.id')
            ->union($query2);

        $dataProvider = new SqlDataProvider([
            'sql'        => $query1->createCommand()->getRawSql(),
            'totalCount' => 20,
            'sort'       => [
                'attributes'   => [
                    'product',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);

        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            $dataProvider->pagination = false;
            return $dataProvider;
        }

        if (isset($params->count)) {
            $dataProvider->pagination->pageSize = $params->count;
            if (isset($params->page)) {
                $dataProvider->pagination->page = ($params->page - 1);
            }
        }

        return $dataProvider;
    }
}
