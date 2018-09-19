<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 12:39 PM
 */

namespace api_web\modules\integration\classes\dictionaries;


use api_web\components\WebApi;
use common\models\OuterProduct;
use common\models\OuterUnit;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;

class AbstractDictionary extends WebApi
{
    public $service_id;

    public function __construct($serviceId)
    {
        parent::__construct();
        $this->service_id = $serviceId;
    }

    /**
     * Список продуктов полученных из iiko
     * @param $request
     * @return array
     */
    public function productList($request){
        $pag = $request['pagination'];
        $page = (isset($pag['page']) ? $pag['page'] : 1);
        $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);

        $search = OuterProduct::find()->where(['org_id' => $this->user->organization->id, 'service_id' =>  $this->service_id]);

        if (isset($request['search'])) {
            if (isset($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }
//            if (isset($request['search']['is_active'])) {
//                $search->andWhere(['is_active' => (int)$request['search']['is_active']]);
//            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $search->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareProduct($model);
        }

        $return = [
            'products' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Подготовка продукта к выдаче
     * @param OuterProduct $model
     * @return array
     */
    private function prepareProduct(OuterProduct $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->name,
            'unit' => (OuterUnit::findOne($model->outer_unit_id))->name,
            'is_active' => (int)!$model->is_deleted
        ];
    }
}