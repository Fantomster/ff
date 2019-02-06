<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\modules\v1\modules\mobile\resources\Catalog;
use api\modules\v1\modules\mobile\resources\CatalogGoods;
use api\modules\v1\modules\mobile\resources\Currency;
use api\modules\v1\modules\mobile\resources\Organization;
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
class ProductSearchController extends ActiveController
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
        $params = new \api\modules\v1\modules\mobile\resources\GuideProductSearch();

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $vendors = $client->getSuppliers(null);
        $catalogs = $vendors ? $client->getCatalogs(null) : '';

        if (!empty($catalogs) && !is_array($catalogs)) {
            $catalogs = explode(',', $catalogs);
        }

        $query1 = (new Query())
            ->select([
                'cbg.id',
                'cbg.product',
                'cbg.supp_org_id',
                'cbg.units',
                'cbg.price',
                'cbg.cat_id',
                'cbg.category_id',
                'cbg.article',
                'cbg.note',
                'cbg.ed',
                'curr.symbol',
                'org.name as organization_name',
                "(cbg.article + 0) AS c_article_1",
                "cbg.article AS c_article",
                "(cbg.article REGEXP '^-?[0-9]+$') AS i",
                "(cbg.product REGEXP '^-?[а-яА-Я].*$') AS alf_cyr"
            ])
            ->from(['cbg' => CatalogBaseGoods::tableName()])
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cbg.cat_id = cat.id')
            ->leftJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->where(['IN', 'cat_id', $catalogs])
            ->andWhere([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ]);

        $query2 = (new Query())
            ->select([
                'cbg.id',
                'cbg.product',
                'cbg.supp_org_id',
                'cbg.units',
                'cg.price',
                'cg.cat_id',
                'cbg.category_id',
                'cbg.article',
                'cbg.note',
                'cbg.ed',
                'curr.symbol',
                'org.name as organization_name',
                "(cbg.article + 0) AS c_article_1",
                "cbg.article AS c_article",
                "(cbg.article REGEXP '^-?[0-9]+$') AS i",
                "(cbg.product REGEXP '^-?[а-яА-Я].*$') AS alf_cyr"
            ])
            ->from(['cg' => CatalogGoods::tableName()])
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'cg.base_goods_id = cbg.id')
            ->leftJoin(['org' => Organization::tableName()], 'cbg.supp_org_id = org.id')
            ->leftJoin(['cat' => Catalog::tableName()], 'cg.cat_id = cat.id')
            ->leftJoin(['curr' => Currency::tableName()], 'cat.currency_id = curr.id')
            ->where(['IN', 'cg.cat_id', $catalogs])
            ->andWhere([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ]);;

        $sql = (new Query())
            ->select(['*'])
            ->from([
                $query1->union($query2)
            ])
            ->createCommand()
            ->getRawSql();

        $dataProvider = new SqlDataProvider([
            'sql'        => $sql,
            'pagination' => false,
            'sort'       => [
                'attributes'   => [
                    'product' => [
                        'asc'     => ['alf_cyr' => SORT_DESC, 'product' => SORT_ASC],
                        'desc'    => ['alf_cyr' => SORT_ASC, 'product' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'price',
                    'units',
                    'c_article_1',
                    'c_article',
                    'i'
                ],
                'defaultOrder' => [
                    'i'           => SORT_DESC,
                    'c_article_1' => SORT_ASC,
                    'c_article'   => SORT_ASC
                ]
            ],
        ]);

        if (isset($params->count)) {
            $dataProvider->pagination->pageSize = $params->count;
            if (isset($params->page)) {
                $dataProvider->pagination->page = ($params->page - 1);
            }
        }

        return $dataProvider;
    }
}
