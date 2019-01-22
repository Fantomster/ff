<?php

namespace api_web\classes;

use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\helpers\OuterProductMapHelper;
use api_web\helpers\WaybillHelper;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\classes\OuterProductMapper;
use common\models\AllService;
use common\models\CatalogBaseGoods;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterProduct;
use common\models\OuterStore;
use common\models\OuterUnit;
use common\models\search\OuterProductMapSearch;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\base\InvalidArgumentException;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class IntegrationWebApi
 *
 * @package api_web\classes
 */
class IntegrationWebApi extends WebApi
{

    /**
     * @var OuterProductMapHelper
     */
    public $helper;

    /**
     * IntegrationWebApi constructor.
     */
    function __construct()
    {
        $this->helper = new OuterProductMapHelper();
        parent::__construct();
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException|\Exception
     */
    public function userServiceSet($request)
    {
        $this->validateRequest($request, ['service_id']);
        $license = License::checkByServiceId($this->user->organization_id, $request['service_id']);
        if ($license) {
            return ['result' => $this->user->setIntegrationServiceId($request['service_id'])];
        } else {
            throw new BadRequestHttpException('Dont have active license for this service');
        }
    }

    /**
     * Список интеграторов и лицензий
     *
     * @return array
     * @throws \Exception
     */
    public function list()
    {
        $result = array_values(AllService::getAllServiceAndLicense($this->user->organization_id, Registry::$integration_services));
        $user_service_id = $this->user->integration_service_id;
        foreach ($result as &$item) {
            $item['is_default'] = $user_service_id == $item['id'] ? true : false;
            $item['is_integration_service'] = in_array($item['id'], Registry::$waybill_services);
        }
        return [
            'services' => $result
        ];
    }

    /**
     * integration: Создание накладной к заказу
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function handleWaybill(array $post): array
    {
        $this->validateRequest($post, ['service_id']);

        $acquirerID = $organizationID = $this->user->organization_id;
        $outerAgentId = $outerStoreId = $ediNumber = '';

        if (isset($post['order_id'])) {
            $order = Order::findOne(['id' => (int)$post['order_id'], 'client_id' => $this->user->organization_id]);

            if (!$order) {
                throw new BadRequestHttpException("order_not_found");
            }
            $outerAgent = OuterAgent::findOne(['vendor_id' => $order->vendor_id]);
            if ($outerAgent) {
                $outerAgentId = $outerAgent->id;
            }
            $outerStoreId = null;
            if (isset($post['service_id'])) {
                $outerStore = OuterStore::find()->where(['org_id' => $organizationID, 'service_id' => $post['service_id'], 'is_deleted' => OuterStore::OUTER_STORE_IS_DELETED_FALSE])->andWhere('outer_store.right - outer_store.left = 1')->orderBy('outer_store.left')->one();
                if ($outerStore) {
                    $outerStoreId = $outerStore->id;
                }
            }

            $orderContent = OrderContent::findOne(['order_id' => $order->id]);
            if ($orderContent->edi_number) {
                $tmp_ed_num = $orderContent->edi_number;
                $existWaybill = Waybill::find()->where(['like', 'outer_number_code', $tmp_ed_num])
                    ->andWhere(['service_id' => $post['service_id']])
                    ->orderBy(['outer_number_code' => SORT_DESC])->limit(1)->one();
                if ($existWaybill && $existWaybill->outer_number_code) {
                    $ediNumber = WaybillHelper::getLastEdiNumber($existWaybill->outer_number_code, $tmp_ed_num);
                } else {
                    $ediNumber = $orderContent->edi_number . "-1";
                }
            } else {
                $waybillsCount = count($order->getWaybills($post['service_id']));
                if ($waybillsCount == 0) {
                    $waybillsCount = 1;
                } else {
                    $waybillsCount++;
                }
                $ediNumber = $post['order_id'] . "-" . $waybillsCount;
            }
        }

        $waybill = new \api_web\modules\integration\classes\documents\Waybill();
        $waybill->service_id = (int)$post['service_id'];
        $waybill->status_id = Registry::WAYBILL_FORMED;
        $waybill->outer_number_code = $ediNumber;
        $waybill->outer_agent_id = $outerAgentId;
        $waybill->outer_store_id = $outerStoreId;
        $waybill->acquirer_id = $acquirerID;
        $waybill->doc_date = \gmdate('Y-m-d H:i:s');

        if (!$waybill->save()) {
            throw new ValidationException($waybill->getFirstErrors());
        }

        return ['result' => $waybill->prepare()];
    }

    /**
     * integration: Сброс данных позиции, на значения из заказа
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function resetWaybillContent(array $post): array
    {
        $this->validateRequest($post, ['waybill_content_id']);

        $waybillContent = WaybillContent::find()
            ->joinWith('waybill')
            ->where([
                WaybillContent::tableName() . '.id'   => $post['waybill_content_id'],
                Waybill::tableName() . '.acquirer_id' => $this->user->organization_id
            ])->one();

        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill.content_not_found");
        }

        $orderContent = $waybillContent->orderContent;
        if (!empty($orderContent)) {
            $result = (new Query())->select(['quantity', 'price'])->from('order_content')
                ->where(['id' => $orderContent->id])->one();
            //Поиск в массовом сопоставлении
            $outerProductMap = $this->helper->getMapForOrder($orderContent->order, $waybillContent->waybill->service_id, $orderContent->product_id);
            if (!empty($outerProductMap)) {
                $outerProductMap = (object)current($outerProductMap);
                $outerProduct = OuterProduct::findOne($outerProductMap->outer_product_id);
                $waybillContent->outer_product_id = $outerProduct->id;
                $waybillContent->outer_unit_id = $outerProduct->outer_unit_id;
                $waybillContent->vat_waybill = $outerProductMap->vat;
                $waybillContent->koef = $outerProductMap->coefficient ?? 1;
            } else {
                $waybillContent->vat_waybill = $orderContent->vat_product;
            }
            $waybillContent->quantity_waybill = $result['quantity'];
            $waybillContent->price_without_vat = (int)$result['price'];
            $waybillContent->price_with_vat = (int)($result['price'] + ($result['price'] * $orderContent->vat_product));
            $waybillContent->sum_without_vat = (int)$result['price'] * $result['quantity'];
            $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $result['quantity'];
        } else {
            throw new BadRequestHttpException("order content not found");
        }

        if (!$waybillContent->save()) {
            throw new ValidationException($waybillContent->getFirstErrors());
        }

        return ['result' => $waybillContent];
    }

    /**
     * integration: Позиция накладной - Детальная информация
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function showWaybillContent(array $post): array
    {
        $this->validateRequest($post, ['waybill_content_id']);

        /**@var WaybillContent $waybillContent */
        $waybillContent = WaybillContent::find()
            ->joinWith('waybill')
            ->where([
                'waybill_content.id'  => (int)$post['waybill_content_id'],
                'waybill.acquirer_id' => $this->user->organization_id
            ])->one();

        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill_content_not_found");
        }

        $arr = $waybillContent->attributes;
        $arr['product'] = null;
        $arr['outer_product'] = null;
        $arr['outer_store'] = null;
        $arr['outer_unit'] = null;
        $arr['vat_waybill'] = [
            'value'    => $waybillContent->vat_waybill,
            'equality' => true
        ];
        $arr['koef'] = [
            'value'    => $waybillContent->koef,
            'equality' => true
        ];

        $outerProduct = OuterProduct::findOne(['id' => $waybillContent->outer_product_id, 'service_id' => $waybillContent->waybill->service_id]);
        if ($outerProduct) {
            $arr['outer_product']['id'] = $outerProduct->id;
            $arr['outer_product']['name'] = $outerProduct->name;
            $arr['outer_product']['equality'] = true;
        }

        $outerStore = OuterStore::findOne(['id' => $waybillContent->waybill->outer_store_id, 'service_id' => $waybillContent->waybill->service_id]);
        if ($outerStore) {
            $arr['outer_store']['id'] = $outerStore->id;
            $arr['outer_store']['name'] = $outerStore->name;
            $arr['outer_store']['equality'] = true;
        }

        $outerUnit = OuterUnit::findOne(['id' => $waybillContent->outer_unit_id]);
        if ($outerUnit) {
            $arr['outer_unit']['id'] = $outerUnit->id;
            $arr['outer_unit']['name'] = $outerUnit->name;
            $arr['outer_unit']['equality'] = true;
        }

        //Если есть связь, с заказом
        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if ($orderContent) {
            //Вернуть продукт поставщика
            $orderContentProduct = CatalogBaseGoods::findOne(['id' => $orderContent->product_id]);
            if ($orderContentProduct) {
                $arr['product']['id'] = $orderContent->product_id;
                $arr['product']['name'] = $orderContentProduct->product;
            }
            //получаем из массового сопоставления
            $outerProductMap = $this->helper->getMapForOrder($orderContent->order, $waybillContent->waybill->service_id, $orderContent->product_id);
            if (!empty($outerProductMap)) {
                $outerProductMap = (object)current($outerProductMap);
                //Если отличаются продукты, надо подсвечивать на фронте
                if ($outerProductMap->outer_product_id != $waybillContent->outer_product_id && $outerProduct) {
                    $arr['outer_product']['equality'] = false;
                }
                //Если отличаются склады, надо подсвечивать на фронте
                if ($outerProductMap->outer_store_id != $waybillContent->waybill->outer_store_id && $outerStore) {
                    $arr['outer_store']['equality'] = false;
                }
                //Если ставка НДС отличается, то надо подсвечивать на фронте
                if ($outerProductMap->vat != $waybillContent->vat_waybill) {
                    $arr['vat_waybill']['equality'] = false;
                }
                //Если коэффициент отличается, то надо подсвечивать на фронте
                if ($outerProductMap->coefficient != $waybillContent->koef) {
                    $arr['koef']['equality'] = false;
                }
            }
        }

        return $arr;
    }

    /**
     * integration: Накладные - Обновление детальной информации позиции накладной
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function updateWaybillContent(array $post): array
    {
        $this->validateRequest($post, ['waybill_content_id']);
        $waybillContent = WaybillContent::find()
            ->joinWith('waybill')
            ->where([
                'waybill_content.id'  => (int)$post['waybill_content_id'],
                'waybill.acquirer_id' => $this->user->organization_id
            ])->one();

        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill.content_not_found");
        }

        //Обновим внешний продукт и ед. измерения
        if (isset($post['outer_product_id']) && !empty($post['outer_product_id'])) {
            //Поиск, есть ли такой продукт в у.с. у этого ресторана
            $outerProduct = OuterProduct::findOne([
                'id'         => (int)$post['outer_product_id'],
                'service_id' => $waybillContent->waybill->service_id,
                'org_id'     => $this->user->organization_id
            ]);

            if (empty($outerProduct)) {
                throw new BadRequestHttpException("waybill.outer_product_not_found");
            }
            $waybillContent->outer_product_id = $outerProduct->id;
            $waybillContent->outer_unit_id = $outerProduct->outer_unit_id;
        }

        $params = ["koef", "quantity_waybill", "price_without_vat", "vat_waybill", "sum_without_vat"];
        foreach ($params as $key => $attributeName) {
            if (isset($post[$attributeName]) && (!empty($post[$attributeName]) || $post[$attributeName] === 0)) {
                $waybillContent->setAttribute($attributeName, $post[$attributeName]);
            }
        }

        //Пересчет сумм происходит в beforeSave() модели
        if (!$waybillContent->save()) {
            throw new ValidationException($waybillContent->getFirstErrors());
        }
        //Подготовим fake request
        $call = [
            'waybill_content_id' => $waybillContent->id,
            'service_id'         => $waybillContent->waybill->service_id
        ];
        //Вернем обработанную модель деталей позиции
        return $this->showWaybillContent($call);
    }

    /**
     * integration: Накладная (привязана к заказу) - Добавление позиции
     *
     * @param array $post
     * @throws \Exception
     * @return array
     */
    public function createWaybillContent(array $post): array
    {
        $this->validateRequest($post, ['waybill_id', 'outer_product_id']);

        //Поиск накладной
        $waybill = Waybill::findOne(['id' => (int)$post['waybill_id'], 'acquirer_id' => $this->user->organization_id]);
        if (!$waybill) {
            throw new BadRequestHttpException("waybill_not_found");
        }

        //Найдем продукт у.с.
        $outerProduct = OuterProduct::findOne([
            'id'         => (int)$post['outer_product_id'],
            'service_id' => $waybill->service_id
        ]);
        if (empty($outerProduct)) {
            throw new BadRequestHttpException('waybill.outer_product_not_found');
        }

        //Проверяем, нет ли в накладной этого продукта
        $exists = WaybillContent::find()->where([
            'waybill_id'       => $waybill->id,
            'outer_product_id' => $outerProduct->id
        ])->exists();
        if ($exists) {
            throw new BadRequestHttpException("waybill.content_exists");
        }

        //Создаем новую запись
        $waybillContent = new WaybillContent();
        $waybillContent->waybill_id = $waybill->id;
        $waybillContent->outer_product_id = $outerProduct->id;
        $waybillContent->outer_unit_id = $outerProduct->outer_unit_id;
        $waybillContent->vat_waybill = (int)$post['vat_waybill'] ?? 0;
        $waybillContent->quantity_waybill = $post['quantity_waybill'] ?? 1;
        $waybillContent->koef = $post['koef'] ?? 1;

        if (!empty($post['price_without_vat'])) {
            $waybillContent->price_without_vat = round($post['price_without_vat'], 2);
        }

        if (!$waybillContent->save()) {
            throw new ValidationException($waybillContent->getFirstErrors());
        }

        return ['success' => true, 'waybill_content_id' => $waybillContent->id];
    }

    /**
     * integration: Накладная - Удалить
     *
     * @param array $post
     * @throws \Exception|\Throwable
     * @return array
     */
    public function deleteWaybill(array $post): array
    {
        $this->validateRequest($post, ['waybill_id', 'service_id']);

        $waybillCheck = Waybill::findOne(['id' => $post['waybill_id']]);
        if (!isset($waybillCheck)) {
            throw new BadRequestHttpException('waybill.waibill_not_found');
        }

        $businessList = (new UserWebApi())->getUserOrganizationBusinessList();
        $checkOrg = in_array($waybillCheck->acquirer_id, ArrayHelper::map($businessList['result'] ?? [], 'id', 'id')) ?? false;

        if (!$checkOrg) {
            throw new BadRequestHttpException('waybill.waibill_not_releated_current_user');
        }

        if ($waybillCheck->service_id != $post['service_id']) {
            throw new BadRequestHttpException('waybill.waibill_not_relation_this_service');
        }

        if ($waybillCheck->status_id == Registry::WAYBILL_UNLOADED) {
            throw new BadRequestHttpException('waybill.waibill_is_unloading');
        }

        $waybillContentCheck = WaybillContent::find()
            ->where(['waybill_id' => $waybillCheck->id])
            ->andWhere('order_content_id is not null')
            ->one();
        if (isset($waybillContentCheck)) {
            throw new BadRequestHttpException('waybill.waibill_is_relation_order');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $waybillCheck->delete();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return ['success' => true];
    }

    /**
     * integration: Накладная - Удалить/Убрать позицию
     *
     * @param array $post
     * @throws \Exception|\Throwable
     * @return array
     */
    public function deleteWaybillContent(array $post): array
    {
        $this->validateRequest($post, ['waybill_content_id']);

        $waybillContent = WaybillContent::find()
            ->joinWith('waybill')
            ->where([
                WaybillContent::tableName() . '.id'   => $post['waybill_content_id'],
                Waybill::tableName() . '.acquirer_id' => $this->user->organization_id
            ])->one();

        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill.content_not_found");
        }

        return ['success' => $waybillContent->delete()];
    }

    /**
     * integration: список сопоставления со всеми связями
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException|InvalidArgumentException
     */
    public function getProductMapList(array $post): array
    {
        $this->validateRequest($post, ['business_id']);

        $page = (!empty($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (!empty($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $searchBusiness = (int)$post['business_id'];
        $businessList = (new UserWebApi())->getUserOrganizationBusinessList();
        $checkOrg = in_array($searchBusiness, ArrayHelper::map($businessList['result'] ?? [], 'id', 'id')) ?? false;
        if (!$checkOrg) {
            return [];
        } else {
            $client = \common\models\Organization::findOne($searchBusiness);
        }

        /** @var SqlDataProvider $dataProvider */
        $dataProvider = (new OuterProductMapSearch())->search($client, $post);
        $pagination = new \yii\data\Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        $models = $dataProvider->getModels();

        if (IntegrationSettingValue::getSettingsByServiceId($post['service_id'], $client->id, ['main_org'])) {
            $isChildOrganization = true;
        } else {
            $isChildOrganization = false;
        }

        if (!empty($models)) {
            $result = new \SplObjectStorage();
            foreach (WebApiHelper::generator($models) as $model) {
                $result->attach((object)$this->prepareOutProductMap($model, $isChildOrganization));
            }
        } else {
            $result = [];
        }

        return [
            'products'   => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
    }

    /**
     * Редактирование записи сопоставления
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function mapUpdate(array $post): array
    {
        $this->validateRequest($post, ['business_id', 'service_id', 'map']);
        $result = [];
        foreach ($post['map'] as $item) {
            try {
                $this->editProductMap($post['service_id'], $item, $post['business_id']);
                $result[$item['product_id']] = ['success' => true];
            } catch (\Exception $e) {
                $result[$item['product_id']] = ['success' => false, 'error' => \Yii::t('api_web', $e->getMessage()) . $e->getTraceAsString()];
            }
        }
        return $result;
    }

    /**
     * Изменение атрибутов сопоставления
     *
     * @param int  $service_id
     * @param      $request
     * @param null $business_id
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function editProductMap(int $service_id, $request, $business_id = null)
    {
        $this->validateRequest($request, ['product_id']);
        //Загружаем данные по базовому и дочерним бизнесам (если бизнес главный)
        $mapper = new OuterProductMapper($business_id, $service_id);
        $mapper->loadRequest($request);
        if (isset($request['outer_product_id']) && !is_null($request['outer_product_id'])) {
            $mapper->updateChildesMap();
        }
        $mapper->updateModel();
    }

    /**
     * Информация по сопоставлению продукта
     *
     * @param array $model
     * @param bool  $isChild
     * @return array
     */
    private function prepareOutProductMap(array $model, $isChild = false)
    {
        $result = [
            "id"                            => $model['id'],
            "service_id"                    => (int)$model['service_id'],
            "organization_id"               => (int)$model['organization_id'],
            "product"                       => null,
            "unit"                          => null,
            "vendor"                        => null,
            "outer_product"                 => null,
            "outer_unit"                    => null,
            "outer_store"                   => null,
            "coefficient"                   => !empty($model['coefficient']) ? round($model['coefficient'], 10) : 1,
            "vat"                           => (int)$model['vat'] ?? 0,
            "created_at"                    => WebApiHelper::asDatetime($model['created_at'] ?? null),
            "updated_at"                    => WebApiHelper::asDatetime($model['updated_at'] ?? null),
            "is_child_organization_for_map" => $isChild,
        ];

        $fields = ['vendor', 'product', 'outer_product', 'outer_unit', 'outer_store'];

        foreach ($fields as $name) {
            if (isset($model[$name . '_id'])) {
                $result[$name] = [
                    "id"   => (int)$model[$name . '_id'],
                    "name" => $model[$name . '_name']
                ];
            }
        }

        return $result;
    }
}
