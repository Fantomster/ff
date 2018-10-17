<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 12:39 PM
 */

namespace api_web\modules\integration\classes\dictionaries;


use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\Organization;
use common\models\OuterAgent;
use common\models\OuterAgentNameWaybill;
use common\models\OuterCategory;
use common\models\OuterProduct;
use common\models\OuterStore;
use common\models\OuterUnit;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;

/**
 * Class AbstractDictionary
 *
 * @package api_web\modules\integration\classes\dictionaries
 */
class AbstractDictionary extends WebApi
{
    /**
     * @var
     */
    public $service_id;

    /**
     * AbstractDictionary constructor.
     *
     * @param $serviceId
     */
    public function __construct($serviceId)
    {
        parent::__construct();
        $this->service_id = $serviceId;
    }

    /**
     * Список продуктов полученных из внешней системы
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function productList($request)
    {
        $pag = $request['pagination'];
        $page = (isset($pag['page']) ? $pag['page'] : 1);
        $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);

        $search = OuterProduct::find()->where(['org_id' => $this->user->organization->id, 'service_id' => $this->service_id]);

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

    /**
     * Список агентов
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function agentList($request)
    {
        $pag = $request['pagination'];
        $page = (isset($pag['page']) ? $pag['page'] : 1);
        $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);


        $search = OuterAgent::find()->joinWith(['store', 'nameWaybills'])
            ->where([
                '`outer_agent`.org_id' => $this->user->organization->id,
                '`outer_agent`.service_id' => $this->service_id
            ]);

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', '`outer_agent`.name', $request['search']['name']]);
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
            $result[] = $this->prepareAgent($model);
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
     * Информация по агенту
     * @param $agent_uid
     * @return array
     */
    public function agentInfo($agent_uid)
    {
        $model = OuterAgent::find()->joinWith(['store', 'nameWaybills'])
            ->where([
                '`outer_agent`.org_id' => $this->user->organization->id,
                '`outer_agent`.service_id' => $this->service_id,
                '`outer_agent`.outer_uid' => $agent_uid,
            ])->one();

        if ($model === null) {
            return [];
        }

        return $this->prepareAgent($model);
    }

    /**
     * Обновление контрагента
     * @param $request
     * @return array
     * @throws ValidationException
     * @throws \Throwable
     */
    public function agentUpdate($request)
    {
        $model = OuterAgent::findOne($request['id']);

        if (empty($model)) {
            throw new BadRequestHttpException('model_not_found');
        }

        $model->vendor_id = $request['vendor_id'] ?? null;
        $model->store_id = $request['store_id'] ?? null;
        $model->payment_delay = $request['payment_delay'] ?? null;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        if (OuterAgentNameWaybill::find()->where(['agent_id' => $request['id']])->exists()) {
            OuterAgentNameWaybill::deleteAll(['agent_id' => $request['id']]);
        }


        $transaction = \Yii::$app->db->beginTransaction();
        try {
            \Yii::$app->db_api->createCommand()
                ->batchInsert(
                    OuterAgentNameWaybill::tableName(),
                    ['agent_id', 'name'],
                    array_map(
                        function ($el) use ($request) {
                            return [$request['id'], $el];
                        },
                        $request['name_waybill']
                    )
                )->execute();
            $transaction->commit();
        } catch (\Throwable $throwable) {
            $transaction->rollBack();
            throw $throwable;
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
            'name_waybill' => array_map(
                function ($el) {
                    return $el['name'];
                },
                $model->nameWaybills
            )
        ];
    }

    /**
     * Получение списка складов
     * @param $request
     * @return array
     * */
    public function storeList($request): array
    {
        $search = OuterStore::find()->where(['org_id' => $this->user->organization->id, 'service_id' => $this->service_id]);

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }
        }

        $rootModels = $search->roots()->indexBy('id')->all();

        $result = [];

        foreach ($rootModels as $rootModel) {
            $result = $this->prepareStore($rootModel);
        }

        return ['stores' => $result];
    }

    /***
     * Информация по складу
     * @param $store_uid
     * @return array
     */
    public function storeInfo($store_uid)
    {
        $model = OuterStore::find()
            ->where([
                'org_id' => $this->user->organization->id,  
                'outer_uid' => $store_uid,
                'service_id' => $this->service_id])
            ->one();

        if ($model === null) {
            return [];
        }

        return $this->prepareStore($model);
    }

    /**
     * Функция рекурсия от корневого склада
     * @param OuterStore $model
     * @return array
     * */
    private function prepareStore($model)
    {
        $child = function ($model) {
            $childrens = $model->children()->all();
            $arReturn = [];
            foreach ($childrens as $children) {
                $arReturn[] = $this->prepareStore($children);
            }
            return $arReturn;
        };
        return [
            'id' => $model->id,
            'outer_uid' => $model->outer_uid,
            'name' => $model->name,
            'store_type' => $model->store_type,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
            'is_active' => (int)!$model->is_deleted,
            'childs' => $child($model),
        ];
    }

    /**
     * Агент. Собираем необходимые данные из модели
     * @param \yii\db\ActiveRecord $model
     * @return array
     */
    private function prepareAgent(\yii\db\ActiveRecord $model)
    {
        /**@var OuterAgent $model*/
        $orgModel = Organization::findOne($model->vendor_id);
        return [
            'id' => $model->id,
            'outer_uid' => $model->outer_uid,
            'name' => $model->name,
            'vendor_id' => $model->vendor_id,
            'vendor_name' => $orgModel->name ?? null,
            'store_id' => $model->store_id,
            'store_name' => $model->store->name ?? null,
            'payment_delay' => $model->payment_delay,
            'is_active' => (int)!$model->is_deleted,
            'name_waybill' => array_map(function ($el) {
                return $el['name'];
            }, $model->nameWaybills)

        ];
    }

    /**
     * Получение списка единиц измерения
     * @param $request
     * @return array
     * */
    public function unitList($request): array
    {
        $search = OuterUnit::find()->where(['org_id' => $this->user->organization->id, 'service_id' => $this->service_id]);

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }
        }

        $result = [];
        foreach ($search->all() as $model) {
            $result[] = $model;
        }

        return ['units' => $result];
    }

    /**
     * Получение списка категорий
     * @param $request
     * @return array
     * */
    public function categoryList($request): array
    {
        $search = OuterCategory::find()->where(['org_id' => $this->user->organization->id, 'service_id' =>
            $this->service_id]);


        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }
        }

        $rootModels = $search->roots()->indexBy('id')->all();

        $result = [];

        foreach ($rootModels as $rootModel) {
            $result = $this->prepareCategory($rootModel);
        }

        return ['categories' => $result];
    }

    /**
     * Функция рекурсия от корневой категории
     * @param OuterCategory $model
     * @return array
     * */
    private function prepareCategory($model)
    {
        $child = function ($model) {
            $childrens = $model->children()->all();
            $arReturn = [];
            foreach ($childrens as $children) {
                $arReturn[] = $this->prepareCategory($children);
            }
            return $arReturn;
        };
        return [
            'id' => $model->id,
            'outer_uid' => $model->outer_uid,
            'name' => $model->name,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
            'is_active' => (int)!$model->is_deleted,
            'childs' => $child($model),
        ];
    }
}