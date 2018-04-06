<?php

namespace api\modules\v1\modules\mobile\controllers;

use Yii;
use yii\data\Pagination;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use yii\data\SqlDataProvider;
use yii\helpers\Json;

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

        if($params->guide_list != null)
            $where = 'gp.guide_id IN('.implode(',', Json::decode($params->guide_list)).')';
        else
            $where = "gp.guide_id = $params->guide_id";

        $query = "
        SELECT * FROM (
            SELECT cbg.id as catalog_base_goods_id, cbg.id as cbg_id, cbg.product as product, cbg.units as units, cbg.price as price, cbg.cat_id, org.name as name, cbg.ed as ed, curr.symbol, cbg.note, org.id as supp_org_id, org.name as organization_name, cbg.created_at as created_at, cbg.updated_at as updated_at, 
            cbg.article as article, cbg.id as id, 0 as count
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog cat ON cbg.cat_id = cat.id 
                            AND (cbg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE ($where)
                    AND (cbg.product LIKE :searchString) 
                AND (cbg.status = 1) 
                AND (cbg.deleted = 0) 
            UNION ALL
            (SELECT cbg.id as catalog_base_goods_id, cbg.id as cbg_id, cbg.product as product, cbg.units as units, cbg.price as price, cbg.cat_id, org.name as name, cbg.ed as ed, curr.symbol, cbg.note, org.id as supp_org_id, org.name as organization_name, cbg.created_at as created_at, cbg.updated_at as updated_at, 
            cbg.article as article, cbg.id as id, 0 as count
            FROM guide_product AS gp
                    LEFT JOIN catalog_base_goods AS cbg ON gp.cbg_id = cbg.id
                    LEFT JOIN catalog_goods AS cg ON cg.base_goods_id = gp.cbg_id 
                            AND (cg.cat_id IN (SELECT cat_id FROM relation_supp_rest WHERE (supp_org_id=cbg.supp_org_id) AND (rest_org_id = $client->id)))
                LEFT JOIN organization AS org ON cbg.supp_org_id = org.id 
                LEFT JOIN catalog AS cat ON cg.cat_id = cat.id 
                JOIN currency curr ON cat.currency_id = curr.id 
            WHERE ($where)
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
                $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
                if(strpos($agent, 'okhttp') !== false)
                    $params->page--;
                $pagination->page = $params->page;
            }
            $dataProvider->pagination = $pagination;
        }

        return $dataProvider;
    }
}
