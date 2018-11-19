<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 12:39 PM
 */

namespace api_web\modules\integration\classes\dictionaries;

use api_web\classes\UserWebApi;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\Organization;
use common\models\OrganizationDictionary;
use common\models\OuterAgent;
use common\models\OuterAgentNameWaybill;
use common\models\OuterCategory;
use common\models\OuterDictionary;
use common\models\OuterProduct;
use common\models\OuterStore;
use common\models\OuterUnit;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;
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
     * Список справочников
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function getList()
    {
        $dictionary = OuterDictionary::find()
            ->select('id')
            ->where('service_id = :service_id', [':service_id' => (int)$this->service_id])
            ->asArray()
            ->column();

        $models = OrganizationDictionary::find()
            ->where([
                'outer_dic_id' => $dictionary,
                'org_id'       => $this->user->organization_id
            ])->all();

        $return = [];
        /**
         * Статус по умолчанию = "Синхронизация не проводилась"
         */
        $defaultStatusText = OrganizationDictionary::getStatusTextList()[OrganizationDictionary::STATUS_DISABLED];
        foreach ($models as $model) {
            /** @var \common\models\OrganizationDictionary $model */
            $return[] = [
                'id'          => $model->id,
                'name'        => $model->outerDic->name,
                'title'       => \Yii::t('api_web', 'dictionary.' . $model->outerDic->name),
                'count'       => $model->count ?? 0,
                'status_id'   => $model->status_id ?? 0,
                'status_text' => $model->statusText ?? $defaultStatusText,
                'created_at'  => $model->created_at ?? null,
                'updated_at'  => $model->updated_at ?? null
            ];
        }

        return $return;
    }

    /**
     * Список продуктов полученных из внешней системы
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function productList($request)
    {
        $pag = $request['pagination'];
        $page = (isset($pag['page']) ? $pag['page'] : 1);
        $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);

        $search = OuterProduct::find()->where(['service_id' => $this->service_id]);
        $orgId = $this->user->organization->id;

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }

            if (isset($request['search']['business_id']) && !empty($request['search']['business_id'])) {
                $orgId = $request['search']['business_id'];
            }
        }

        $search->andWhere('org_id = :org_id', [':org_id' => $orgId]);
        $search->andWhere('is_deleted = 0');

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
            'products'   => empty($result) ? null : $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Подготовка продукта к выдаче
     *
     * @param OuterProduct $model
     * @return array
     */
    private function prepareProduct(OuterProduct $model)
    {
        return [
            'id'        => (int)$model->id,
            'name'      => $model->name,
            'unit'      => (OuterUnit::findOne($model->outer_unit_id))->name,
            'is_active' => (int)!$model->is_deleted
        ];
    }

    /**
     * Список агентов
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function agentList($request)
    {
        $pag = $request['pagination'];
        $page = (isset($pag['page']) ? $pag['page'] : 1);
        $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);

        $search = OuterAgent::find()
            ->joinWith(['store', 'nameWaybills'])
            ->where([
                '`outer_agent`.service_id' => $this->service_id,
                '`outer_agent`.is_deleted' => 0
            ]);
        $orgId = $this->user->organization->id;

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', '`outer_agent`.name', $request['search']['name']]);
            }

            if (isset($request['search']['business_id']) && !empty($request['search']['business_id'])) {
                $orgId = $request['search']['business_id'];
            }
        }

        $search->andWhere('`outer_agent`.org_id = :org_id', [':org_id' => $orgId]);

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
            'agents'     => empty($result) ? null : $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Информация по агенту
     *
     * @param $agent_uid
     * @return array
     */
    public function agentInfo($agent_uid)
    {
        $model = OuterAgent::find()->joinWith(['store', 'nameWaybills'])
            ->where([
                '`outer_agent`.org_id'     => $this->user->organization->id,
                '`outer_agent`.service_id' => $this->service_id,
                '`outer_agent`.outer_uid'  => $agent_uid,
            ])->one();

        if ($model === null) {
            return [];
        }

        return $this->prepareAgent($model);
    }

    /**
     * Обновление контрагента
     *
     * @param $request
     * @return array
     * @throws ValidationException
     * @throws \Throwable
     */
    public function agentUpdate($request)
    {
        $this->validateRequest($request, ['id', 'service_id']);

        $model = OuterAgent::findOne([
            'id'         => (int)$request['id'],
            'service_id' => (int)$request['service_id'],
            'org_id'     => $this->user->organization_id
        ]);

        if (empty($model)) {
            throw new BadRequestHttpException('model_not_found');
        }

        //Если хотят поменять поставщика, проверим работает ли с нми ресторан
        if (!empty($request['vendor_id'])) {
            $vendors = $this->user->organization->getSuppliers();
            if (!array_key_exists($request['vendor_id'], $vendors)) {
                throw new BadRequestHttpException('dictionary.you_not_work_this_vendor');
            }
            $model->vendor_id = (int)$request['vendor_id'];
        }
        //Если хотят поменять склад, смотрим принадлежит ли он организации пользователя
        if (!empty($request['store_id'])) {
            $store = OuterStore::findOne(['id' => $request['store_id'], 'org_id' => $this->user->organization_id]);
            if (empty($store)) {
                throw new BadRequestHttpException('dictionary.this_not_you_store');
            }
            $model->store_id = (int)$request['store_id'];
        }
        //Дата отсрочки платежа
        if (!empty($request['payment_delay'])) {
            $model->payment_delay = $request['payment_delay'];
        }

        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        if (isset($request['name_waybill'])) {
            if (OuterAgentNameWaybill::find()->where(['agent_id' => $model->id])->exists()) {
                OuterAgentNameWaybill::deleteAll(['agent_id' => $model->id]);
            }
            if (!empty($request['name_waybill'])) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    \Yii::$app->db_api->createCommand()
                        ->batchInsert(
                            OuterAgentNameWaybill::tableName(),
                            ['agent_id', 'name'],
                            array_map(
                                function ($el) use ($model) {
                                    return [$model->id, $el];
                                },
                                $request['name_waybill']
                            )
                        )->execute();
                    $transaction->commit();
                } catch (\Throwable $throwable) {
                    $transaction->rollBack();
                    throw $throwable;
                }
            }
        }

        return $this->prepareAgent($model);
    }

    /**
     * Получение списка складов
     *
     * @param $request
     * @return array
     * */
    public function storeList($request): array
    {
        $search = OuterStore::find()->where(['service_id' => $this->service_id]);
        $orgId = $this->user->organization->id;

        if (isset($request['search'])) {
            if (isset($request['search']['business_id']) && !empty($request['search']['business_id'])) {
                $orgId = $request['search']['business_id'];
            }
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $result = $this->likeQueryNestedSets('outer_store', $request['search']['name'], $orgId);
                $tree = $this->createTree($result);
                return ['stores' => empty($tree) ? null : reset($tree)];
            }
        }

        $search->andWhere('org_id = :org_id', [':org_id' => $orgId]);
        $search->andWhere('is_deleted = 0');

        $rootModels = $search->roots()->indexBy('id')->all();

        $result = [];

        foreach ($rootModels as $rootModel) {
            $result = $this->prepareStore($rootModel);
        }

        return ['stores' => empty($result) ? null : $result];
    }

    /**
     * Плоский список складов
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function storeFlatList($request)
    {
        $search = OuterStore::find()->where(['service_id' => $this->service_id]);
        $orgId = $this->user->organization->id;

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }
        }

        if (isset($request['search']['business_id'])) {
            $find_org_id = (int)$request['search']['business_id'];
            $organizations = (new UserWebApi())->getUserOrganizationBusinessList();
            if (!empty($organizations['result'])) {
                $organizations = ArrayHelper::map($organizations['result'], 'id', 'name');
                if (isset($organizations[$find_org_id])) {
                    $orgId = $find_org_id;
                } else {
                    throw new BadRequestHttpException('dictionary.access_denied');
                }
            }
        }

        $search->andWhere('org_id = :org_id', [':org_id' => $orgId]);
        $search->andWhere('is_deleted = 0');
        $models = $search->orderBy(['left' => SORT_ASC])->all();
        $result = [];

        if (!empty($models)) {
            /**@var OuterStore $rootModel * */
            foreach ($models as $model) {
                $result[] = [
                    'id'          => $model->id,
                    'outer_uid'   => $model->outer_uid,
                    'level_name'  => str_pad('', $model->level, "-"),
                    'name'        => $model->name,
                    'is_active'   => (bool)!$model->is_deleted,
                    'is_category' => (bool)(!$model->isLeaf())
                ];
            }
        }

        return ['stores' => empty($result) ? null : $result];
    }

    /***
     * Информация по складу
     *
     * @param $store_uid
     * @return array
     */
    public function storeInfo($store_uid)
    {
        $model = OuterStore::find()
            ->where([
                'org_id'     => $this->user->organization->id,
                'outer_uid'  => $store_uid,
                'service_id' => $this->service_id])
            ->one();

        if ($model === null) {
            return [];
        }

        return $this->prepareStore($model);
    }

    /**
     * Функция рекурсия от корневого склада
     *
     * @param OuterStore $model
     * @return array
     * */
    private function prepareStore($model)
    {
        $child = function ($model) {
            $childrens = $model->children(1)->all();
            $arReturn = [];
            if (!empty($childrens)) {
                foreach ($childrens as $children) {
                    $arReturn[] = $this->prepareStore($children);
                }
            }
            return $arReturn;
        };
        return [
            'id'          => $model->id,
            'outer_uid'   => $model->outer_uid,
            'name'        => $model->name,
            'store_type'  => $model->store_type,
            'created_at'  => $model->created_at,
            'updated_at'  => $model->updated_at,
            'is_active'   => (int)!$model->is_deleted,
            'is_category' => (bool)(!$model->isLeaf()),
            'childs'      => $model->isLeaf() ? [] : $child($model),
        ];
    }

    /**
     * Функция создания дерева для Nested Sets
     *
     * @param      $category
     * @param int  $left
     * @param null $right
     * @return array
     */
    private function createTree($category, $left = 0, $right = null)
    {
        $tree = [];
        foreach ($category as $cat => $range) {
            if (($range['left'] == $left + 1 || $range['left'] > $left) && (is_null($right) || $range['right'] < $right)) {
                $tree[$cat] = [];
                $tree[$cat] = $range;
                if ($range['right'] - $range['left'] > 1) {
                    $tree[$cat]['childs'] = $this->createTree($category, $range['left'], $range['right']);
                } else {
                    $tree[$cat]['childs'] = [];
                }
                $left = $range['right'];
            }
        }
        return array_values($tree);
    }

    /**
     * Агент. Собираем необходимые данные из модели
     *
     * @param \yii\db\ActiveRecord $model
     * @return array
     */
    private function prepareAgent(\yii\db\ActiveRecord $model)
    {
        /**@var OuterAgent $model */
        $orgModel = Organization::findOne($model->vendor_id);
        return [
            'id'            => $model->id,
            'outer_uid'     => $model->outer_uid,
            'name'          => $model->name,
            'vendor_id'     => $model->vendor_id,
            'vendor_name'   => $orgModel->name ?? null,
            'store_id'      => $model->store_id,
            'store_name'    => $model->store->name ?? null,
            'payment_delay' => $model->payment_delay,
            'is_active'     => (int)!$model->is_deleted,
            'name_waybill'  => array_map(function ($el) {
                return $el['name'];
            }, $model->nameWaybills)

        ];
    }

    /**
     * Получение списка единиц измерения
     *
     * @param $request
     * @return array
     * */
    public function unitList($request): array
    {
        $pag = $request['pagination'];
        $page = (isset($pag['page']) ? $pag['page'] : 1);
        $pageSize = (isset($pag['page_size']) ? $pag['page_size'] : 12);

        $search = OuterUnit::find()->where(['service_id' => $this->service_id]);
        $orgId = $this->user->organization->id;

        if (isset($request['search'])) {
            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $search->andWhere(['like', 'name', $request['search']['name']]);
            }

            if (isset($request['search']['business_id']) && !empty($request['search']['business_id'])) {
                $orgId = $request['search']['business_id'];
            }
        }

        $search->andWhere('org_id = :org_id', [':org_id' => $orgId]);
        $search->andWhere('is_deleted = 0');

        $dataProvider = new ActiveDataProvider([
            'query' => $search
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];

        foreach ($dataProvider->models as $model) {
            $result[] = $model->toArray();
        }

        $return = [
            'units'      => empty($result) ? null : $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Получение списка категорий
     *
     * @param $request
     * @return array
     * */
    public function categoryList($request): array
    {
        $search = OuterCategory::find()->where(['service_id' => $this->service_id, 'is_deleted' => 0]);
        $orgId = $this->user->organization->id;

        if (isset($request['search'])) {
            if (isset($request['search']['business_id']) && !empty($request['search']['business_id'])) {
                $orgId = $request['search']['business_id'];
            }

            if (isset($request['search']['name']) && !empty($request['search']['name'])) {
                $result = $this->likeQueryNestedSets('outer_category', $request['search']['name'], $orgId);
                $tree = $this->createTree($result);
                return ['categories' => empty($tree) ? null : reset($tree)];
            }

        }

        $search->andWhere('org_id = :org_id', [':org_id' => $orgId]);
        $rootModels = $search->roots()->indexBy('id')->all();

        $result = null;
        foreach ($this->iterator($rootModels) as $rootModel) {
            $result = $this->prepareCategory($rootModel);
        }
        return ['categories' => $result];
    }

    /**
     * Выбор категории для загрузки
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function categorySetSelected($request): array
    {
        $model = OuterCategory::findOne([
            'id'         => (int)$request['category_id'],
            'org_id'     => $request['business_id'] ?? $this->user->organization->id,
            'service_id' => $this->service_id
        ]);

        if (empty($model)) {
            throw new BadRequestHttpException('dictionary.category_not_found');
        }

        if (!$model->isLeaf()) {
            throw new BadRequestHttpException('dictionary.directory_cannot_be_selected');
        }

        $model->selected = (int)$request['selected'] ?? 0;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        $model->selectedParent();

        return ['selected' => (bool)$model->selected];
    }

    /**
     * Функция рекурсия от корневой категории
     *
     * @param OuterCategory $model
     * @return array
     * */
    private function prepareCategory($model)
    {
        /**
         * @param $model OuterCategory
         * @return array
         */
        $child = function ($model) {
            $childrens = $model->children(1)->all();
            $arReturn = [];
            if (!empty($childrens)) {
                foreach ($this->iterator($childrens) as $children) {
                    $arReturn[] = $this->prepareCategory($children);
                }
            }
            return $arReturn;
        };

        return [
            'id'         => $model->id,
            'outer_uid'  => $model->outer_uid,
            'name'       => $model->name,
            'selected'   => (bool)$model->selected,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
            'childs'     => $model->isLeaf() ? [] : $child($model),
        ];
    }

    /**
     * @param $items
     * @return \Generator
     */
    private function iterator($items)
    {
        foreach ($items as $item) {
            yield $item;
        }
    }

    /**
     * Универсальный запрос для получения обратного дерева от найденного листа для Nested Sets
     *
     * @param $table
     * @param $strSearch
     * @param $orgId
     * @return array
     */
    private function likeQueryNestedSets($table, $strSearch, $orgId)
    {
        return (new Query())->distinct()->select([
            'p.name',
            'p.level',
            'p.id',
            'p.outer_uid',
            'p.created_at',
            'p.updated_at',
            'p.`left`',
            'p.`right`',
            'if(p.is_deleted, 0,1) as is_active',
        ])->from($table . ' os, ' . $table . ' p')
            ->andWhere('os.`left` BETWEEN p.`left` and p.`right`')
            ->andWhere(['like', 'os.name', $strSearch])
            ->andWhere(['p.org_id' => $orgId])
            ->andWhere(['os.org_id' => $orgId])
            ->andWhere(['p.service_id' => $this->service_id])
            ->andWhere(['os.service_id' => $this->service_id])
            ->orderBy('p.`left`')
            ->all(\Yii::$app->db_api);
    }
}