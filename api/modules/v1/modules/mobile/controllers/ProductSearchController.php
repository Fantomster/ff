<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ProductSearchController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\CatalogBaseGoods';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = CatalogBaseGoods::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
    
    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $params = new \api\modules\v1\modules\mobile\resources\GuideProductSearch();
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $vendors = $client->getSuppliers(null);
        $catalogs = $vendors ? $client->getCatalogs(null, null) : "(0)";

        $fieldsCBG = [
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cbg.price', 'cbg.cat_id', 'cbg.category_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name as organization_name',
            "(cbg.article + 0) AS c_article_1",
            "cbg.article AS c_article", "cbg.article REGEXP '^-?[0-9]+$' AS i",
            "cbg.product REGEXP '^-?[а-яА-Я].*$' AS alf_cyr"
        ];
        $fieldsCG = [
            'cbg.id', 'cbg.product', 'cbg.supp_org_id', 'cbg.units', 'cg.price', 'cg.cat_id', 'cbg.category_id',
            'cbg.article', 'cbg.note', 'cbg.ed', 'curr.symbol', 'org.name as organization_name',
            "(cbg.article + 0) AS c_article_1",
            "cbg.article AS c_article", "cbg.article REGEXP '^-?[0-9]+$' AS i",
            "cbg.product REGEXP '^-?[а-яА-Я].*$' AS alf_cyr"
        ];

        $where = '';

        $sql = "
        SELECT * FROM (
           SELECT 
              " . implode(',', $fieldsCBG) . "
           FROM catalog_base_goods cbg
             LEFT JOIN organization org ON cbg.supp_org_id = org.id
             LEFT JOIN catalog cat ON cbg.cat_id = cat.id
             LEFT JOIN currency curr ON cat.currency_id = curr.id
           WHERE
           cat_id IN (" . $catalogs . ")
           ".$where."
           AND (cbg.status = 1 AND cbg.deleted = 0)
        UNION ALL
          SELECT 
          " . implode(',', $fieldsCG) . "
          FROM catalog_goods cg
           LEFT JOIN catalog_base_goods cbg ON cg.base_goods_id = cbg.id
           LEFT JOIN organization org ON cbg.supp_org_id = org.id
           LEFT JOIN catalog cat ON cg.cat_id = cat.id
           LEFT JOIN currency curr ON cat.currency_id = curr.id
          WHERE 
          cg.cat_id IN (" . $catalogs . ")
          ".$where."
          AND (cbg.status = 1 AND cbg.deleted = 0)
        ) as c ";

        $query = Yii::$app->db->createCommand($sql);

        $dataProvider = new SqlDataProvider([
            'sql' => $query->sql,
            'sort' => [
                'attributes' => [
                    'product' => [
                        'asc' => ['alf_cyr' => SORT_DESC, 'product' => SORT_ASC],
                        'desc' => ['alf_cyr' => SORT_ASC, 'product' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'price',
                    'units',
                    'c_article_1',
                    'c_article',
                    'i'
                ],
                'defaultOrder' => [
                    'i' => SORT_DESC,
                    'c_article_1' => SORT_ASC,
                    'c_article' => SORT_ASC
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
