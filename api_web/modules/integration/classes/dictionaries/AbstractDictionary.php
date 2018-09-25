<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 12:39 PM
 */

namespace api_web\modules\integration\classes\dictionaries;


use api_web\components\WebApi;
use common\models\OuterAgent;
use common\models\OuterAgentNameWaybill;
use common\models\OuterProduct;
use common\models\OuterUnit;
use yii\data\ActiveDataProvider;
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

    public function agentList($request){
	    $pag = $request['pagination'];
	    $page = (isset($pag['page']) ? $pag['page'] : 1);
	    $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);

	    $search = OuterAgent::find()->joinWith(['vendor', 'store', 'nameWaybills'])
		    ->leftJoin('organization o', 'outer_agent.vendor_id = o.id')
		    ->where(['`outer_agent`.org_id' => $this->user->organization->id, '`outer_agent`.service_id' =>  $this->service_id]);

	    if (isset($request['search'])) {
		    if (isset($request['search']['name']) && !empty($request['search']['name'])) {
			    $search->andWhere(['like', 'name', $request['search']['name']]);
		    }
	    }

	    $dataProvider = new ActiveDataProvider([
		                                          'query' => $search
	                                          ]);

	    $pagination = new Pagination();
	    $pagination->setPage($page - 1);
	    $pagination->setPageSize($pageSize);
	    $dataProvider->setPagination($pagination);

	    $result = [];
	    foreach ($dataProvider->models as $model) {
		    $result[] = [
		    	'id' => $model->id,
			    'outer_uid' => $model->outer_uid,
			    'name' => $model->name,
			    'vendor_id' => $model->vendor_id,
			    'vendor_name' => $model->vendor->name ?? null,
			    'store_id' => $model->store_id,
			    'store_name' => $model->store->name ?? null,
			    'payment_delay' => $model->payment_delay,
			    'is_active' => (int)!$model->is_deleted,
			    'name_waybill' => array_map(function ($el){
					return $el['name'];
			    }, $model->nameWaybills)

		    ];
	    }

	    $return = [
		    'agents' => $result,
		    'pagination' => [
			    'page' => ($dataProvider->pagination->page + 1),
			    'page_size' => $dataProvider->pagination->pageSize,
			    'total_page' => ceil($dataProvider->totalCount / $pageSize)
		    ]
	    ];

	    return $return;
    }

    /**
     * @throws \yii\base\InvalidArgumentException
     * */
    public function agentUpdate($request){
		$model = OuterAgent::findOne($request['id']);
		$model->vendor_id = $request['vendor_id'] ?? null;
		$model->store_id = $request['store_id'] ?? null;
		$model->payment_delay = $request['payment_delay'] ?? null;
		if($model->validate()){
			$model->save();
		}

		OuterAgentNameWaybill::deleteAll(['agent_id' => $request['id']]);

	    $transaction = \Yii::$app->db->beginTransaction();
	    try {
		    \Yii::$app->db_api->createCommand()
			    ->batchInsert(OuterAgentNameWaybill::tableName(), ['agent_id', 'name'], array_map(function ($el) use($request) {
			    	return [$request['id'], $el];
			    }, $request['name_waybill']))
			    ->execute();
		    $transaction->commit();
	    } catch (\Throwable $throwable) {
		    $transaction->rollBack();
		    return ['success' => false, 'error' => $throwable->getMessage()];
	    }

		return [
			'id' => $model->id,
			'outer_uid' => $model->outer_uid,
			'name' => $model->name,
			'vendor_id' => $model->vendor_id,
			'vendor_name' => $model->vendor->name ?? null,
			'store_id' => $model->store_id,
			'store_name' => $model->store->name ?? null,
			'payment_delay' => $model->payment_delay,
			'is_active' => (int)!$model->is_deleted,
			'name_waybill' => array_map(function ($el){
				return $el['name'];
			}, $model->nameWaybills)
		];
    }
}