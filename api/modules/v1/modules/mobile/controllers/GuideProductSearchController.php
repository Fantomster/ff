<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\data\Pagination;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\SqlDataProvider;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideProductSearchController extends ActiveController {

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
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            throw new NotFoundHttpException;
        }
        $user = Yii::$app->user->getIdentity();
        $client = $user->organization;
        $searchString = "%$params->searchString%";
        $query = "
        SELECT * FROM (
            SELECT gp.id, cbg.id as cbg_id, cbg.product, cbg.units, cbg.price, cbg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note 
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (gp.guide_id = $params->guide_id)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            UNION ALL
            (SELECT gp.id, cbg.id as cbg_id, cbg.product, cbg.units, cg.price, cg.cat_id, org.name, cbg.ed, curr.symbol, cbg.note
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = gp.cbg_id 
                            AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog AS cat ON cg.cat_id = cat.id 
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE (gp.guide_id = $params->guide_id)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0))
                ) as tbl
                ";

        // add conditions that should always apply here

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [':searchString' => $searchString],
            'sort' => [
                'attributes' => [
                    'product',
                ],
                'defaultOrder' => [
                    'product' => SORT_ASC
                ]
            ],
            'pagination' => false,
        ]);

        if(isset($params->count))
        {
            $pagination = new Pagination();
            $pagination->pageSize = $params->count;
            if(isset($params->page))
            {
                /*$offset = ($params->page * $params->count) - $params->count;
                $query .= " OFFSET $offset";*/
                $pagination->page = $params->page;
            }
            $dataProvider->pagination = $pagination;
        }

        return $dataProvider;
    }
}
