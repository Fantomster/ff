<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\modules\v1\modules\mobile\models\User;
use api\modules\v1\modules\mobile\resources\Catalog;
use api\modules\v1\modules\mobile\resources\CatalogGoods;
use api\modules\v1\modules\mobile\resources\Currency;
use api\modules\v1\modules\mobile\resources\Organization;
use Yii;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use common\models\MpCategory;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CatalogBaseGoodsController extends ActiveController
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
            'index'   => [
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view'    => [
                'class'      => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel'  => [$this, 'findModel']
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction'
            ]
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
     * @throws \Throwable
     */
    public function prepareDataProvider()
    {
        $params = new CatalogBaseGoods();
        $params->load(Yii::$app->request->queryParams);

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearch'])) {
            $selectedVendor = !empty($params['OrderCatalogSearch']['selectedVendor']) ? (int)$params['OrderCatalogSearch']['selectedVendor'] : null;
        }

        if (!empty($params->vendor_id)) {
            $selectedVendor = $params->vendor_id;
        }

        $vendors = $client->getSuppliers(null);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor) : '';

        if (!empty($catalogs) && !is_array($catalogs)) {
            $catalogs = explode(',', $catalogs);
        }

        $query1 = (new Query())
            ->select([
                "cbg.id AS id",
                "cbg.product",
                "cbg.units",
                "cbg.price",
                "cbg.cat_id",
                "cbg.weight",
                "org.name AS organization_name",
                "cbg.ed",
                "curr.symbol",
                "cbg.note",
                "cbg.supp_org_id AS supp_org_id",
                "cbg.created_at AS created_at"
            ])
            ->from(['cbg' => CatalogBaseGoods::tableName()])
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cbg.cat_id = cat.id')
            ->leftJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->where(['IN', 'cat_id', $catalogs])
            ->andWhere([
                "cbg.status"  => 1,
                "cbg.deleted" => 0
            ])
            ->andFilterWhere(['org_id' => $params->vendor_id])
            ->andFilterWhere(['LIKE', 'cbg.product', $params->product])
            ->orFilterWhere(['LIKE', 'cbg.article', $params->product]);

        $query2 = (new Query())
            ->select([
                "cbg.id AS id",
                "cbg.product",
                "cbg.units",
                "coalesce(cg.price, cbg.price) AS price",
                "cbg.cat_id",
                "cbg.weight",
                "org.name AS organization_name",
                "cbg.ed",
                "curr.symbol",
                "cbg.note",
                "cbg.supp_org_id AS supp_org_id",
                "cbg.created_at AS created_at"
            ])
            ->from(['cg' => CatalogGoods::tableName()])
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'cg.base_goods_id = cbg.id')
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cg.cat_id = cat.id')
            ->leftJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->where(['IN', 'cg.cat_id', $catalogs])
            ->andWhere([
                "cbg.status"  => 1,
                "cbg.deleted" => 0
            ])
            ->andFilterWhere(['org_id' => $params->vendor_id])
            ->andFilterWhere(['like', 'cbg.product', $params->product])
            ->orFilterWhere(['like', 'cbg.article', $params->product]);

        if (!empty($params->category_id)) {
            $categories = \api\modules\v1\modules\mobile\resources\MpCategory::getCategories($params->category_id);
            $categories[] = $params->category_id;
            $categories = implode(",", $categories);
            $query1->andWhere(['category_id' => $categories]);
            $query2->andWhere(['category_id' => $categories]);
        }

        $sql = (new Query())
            ->select(['*'])
            ->from([
                $query1->union($query2)
            ])
            ->where('id != :id', [':id' => 0]);

        $dataProvider = new SqlDataProvider([
            'sql'        => $sql->createCommand()->getRawSql(),
            'pagination' => [
                'page'     => isset($params->page) ? ($params->page - 1) : 0,
                'pageSize' => isset($params->count) ? $params->count : null,
            ],
            'sort'       => [
                'attributes'   => [
                    'product',
                    'price',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
        ]);

        return $dataProvider;
    }

    private function getCategories($cat_id)
    {
        $res[] = $cat_id;
        $cats = MpCategory::find()->where(["parent" => $cat_id])->all();
        foreach ($cats as $cat) {
            $res[] = $cat->id;
            $res = array_merge($res, $this->getCategories($cat->id));
        }

        return $res;
    }
}
