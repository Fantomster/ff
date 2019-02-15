<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\modules\v1\modules\mobile\resources\Catalog;
use api\modules\v1\modules\mobile\resources\CatalogGoods;
use api\modules\v1\modules\mobile\resources\Currency;
use api\modules\v1\modules\mobile\resources\GuideProduct;
use api\modules\v1\modules\mobile\resources\GuideProductSearch;
use api\modules\v1\modules\mobile\resources\Organization;
use api\modules\v1\modules\mobile\resources\RelationSuppRest;
use common\models\User;
use Yii;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\SqlDataProvider;
use yii\helpers\Json;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideProductSearchController extends ActiveController
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
        $params = new GuideProductSearch();
        $params->setAttributes(Yii::$app->request->queryParams);

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;

        $cbgIds = (new Query())
            ->select('rsr.cat_id')
            ->from(['rsr' => RelationSuppRest::tableName()])
            ->where('rsr.supp_org_id = cbg.supp_org_id')
            ->andWhere(['rsr.rest_org_id' => $client->id])
            ->createCommand()
            ->getRawSql();

        $query1 = (new Query())
            ->select([
                "cbg.id as catalog_base_goods_id",
                "cbg.id as cbg_id",
                "cbg.product as product",
                "cbg.units as units",
                "cbg.price as price",
                "cbg.cat_id",
                "org.name as name",
                "cbg.ed as ed",
                "curr.symbol",
                "cbg.note",
                "org.id as supp_org_id",
                "org.name as organization_name",
                "cbg.created_at as created_at",
                "cbg.updated_at as updated_at",
                "cbg.article as article",
                "cbg.id as id"
            ])
            ->from(['gp' => GuideProduct::tableName()])
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'gp.cbg_id = cbg.id')
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cbg.cat_id = cat.id')
            ->innerJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->where("cbg.cat_id IN ({$cbgIds})")
            ->andWhere([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ])
            ->andFilterWhere(['LIKE', 'cbg.product', $params->searchString]);

        $query2 = (new Query())
            ->select([
                "cbg.id as catalog_base_goods_id",
                "cbg.id as cbg_id",
                "cbg.product as product",
                "cbg.units as units",
                "cbg.price as price",
                "cbg.cat_id",
                "org.name as name",
                "cbg.ed as ed",
                "curr.symbol",
                "cbg.note",
                "org.id as supp_org_id",
                "org.name as organization_name",
                "cbg.created_at as created_at",
                "cbg.updated_at as updated_at",
                "cbg.article as article",
                "cbg.id as id"
            ])
            ->from(['gp' => GuideProduct::tableName()])
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'gp.cbg_id = cbg.id')
            ->leftJoin(['cg' => CatalogGoods::tableName()], 'cg.base_goods_id = gp.cbg_id')
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cg.cat_id = cat.id')
            ->innerJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->where("cg.cat_id IN ({$cbgIds})")
            ->andWhere([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ])
            ->andFilterWhere(['LIKE', 'cbg.product', $params->searchString]);

        if (!empty($params->guide_list)) {
            $guideList = Json::decode($params->guide_list);
            $query1->andWhere(['IN', 'gp.guide_id', $guideList]);
            $query2->andWhere(['IN', 'gp.guide_id', $guideList]);
        } else {
            $query1->andWhere(['gp.guide_id' => $params->guide_id]);
            $query2->andWhere(['gp.guide_id' => $params->guide_id]);
        }

        $query = (new Query())
            ->select(['*'])
            ->from([
                $query1->union($query2)
            ]);

        return new SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'sort'       => [
                'attributes'   => [
                    'product',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
            'pagination' => [
                'page'     => isset($params->page) ? ($params->page - 1) : 0,
                'pageSize' => isset($params->count) ? $params->count : null,
            ],
        ]);
    }
}
