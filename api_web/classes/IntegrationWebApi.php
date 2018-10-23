<?php

namespace api_web\classes;

use api\common\models\AllMaps;
use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\helpers\DBNameHelper;
use common\models\CatalogBaseGoods;
use common\models\licenses\License;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterProduct;
use common\models\OuterProductMap;
use common\models\OuterStore;
use common\models\OuterUnit;
use common\models\search\OuterProductMapSearch;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;

/**
 * Class IntegrationWebApi
 *
 * @package api_web\classes
 */
class IntegrationWebApi extends WebApi
{
    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException|\Exception
     */
    public function userServiceSet($request)
    {
        $this->validateRequest($request, ['service_id']);
        $license = License::checkByServiceId($this->user->id, $request['service_id']);
        if ($license) {
            $this->user->integration_service_id = $request['service_id'];
            $this->user->save();
            return ['result' => true];
        } else {
            throw new BadRequestHttpException('Dont have license for this service');
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
        $result = array_values(License::getAllLicense($this->user->organization_id, Registry::$integration_services));
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
        if (!isset($post)) {
            throw new BadRequestHttpException("empty_param|post");
        }

        if (!isset($post['service_id'])) {
            throw new BadRequestHttpException("empty_param|service_id");
        }

        $organizationID = $this->user->organization_id;
        $acquirerID = $organizationID;
        $ediNumber = '';
        $outerAgentUUID = '';
        $outerStoreUUID = '';

        if (isset($post['order_id'])) {
            $order = Order::findOne(['id' => (int)$post['order_id'], 'client_id' => $this->user->organization_id]);

            if (!$order) {
                throw new BadRequestHttpException("order_not_found");
            }
            $outerAgent = OuterAgent::findOne(['vendor_id' => $order->vendor_id]);
            if ($outerAgent) {
                $outerAgentUUID = $outerAgent->outer_uid;
            }
            $outerStore = OuterStore::findOne(['org_id' => $organizationID]);
            if ($outerStore) {
                $outerStoreUUID = $outerStore->outer_uid;
            }

            $orderContent = OrderContent::findOne(['order_id' => $order->id]);
            if ($orderContent->edi_number) {
                $arr = explode('-', $orderContent->edi_number);
                if (isset($arr[1])) {
                    $i = (int)$arr[1];
                    $ediNumber = $arr[0] . "-" . $i;
                } else {
                    $ediNumber = $orderContent->edi_number . "-1";
                }
            } else {
                $waybillsCount = count($order->getWaybills($post['service_id']));
                if ($waybillsCount == 0) {
                    $waybillsCount = 1;
                }
                $ediNumber = $post['order_id'] . "-" . $waybillsCount;
            }
        }

        $waybill = new Waybill();
        $waybill->service_id = (int)$post['service_id'];
        $waybill->outer_number_code = $ediNumber;
        $waybill->outer_contractor_uuid = $outerAgentUUID;
        $waybill->outer_store_uuid = $outerStoreUUID;
        $waybill->acquirer_id = $acquirerID;
        $waybill->save();

        return ['success' => true, 'waybill_id' => $waybill->id];
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
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }

        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if ($orderContent) {
            $waybillContent->quantity_waybill = $orderContent->quantity;
            $waybillContent->price_without_vat = (int)$orderContent->price;
            $waybillContent->vat_waybill = $orderContent->vat_product;
            $waybillContent->price_with_vat = (int)($orderContent->price + ($orderContent->price * $orderContent->vat_product));
            $waybillContent->sum_without_vat = (int)$orderContent->price * $orderContent->quantity;
            $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $orderContent->quantity;
            $allMap = AllMaps::findOne(['product_id' => $orderContent->product_id]);
            if ($allMap) {
                $waybillContent->outer_product_id = $allMap->serviceproduct_id;
            }
        } else {
            throw new BadRequestHttpException("order content not found");
        }

        $waybillContent->save();

        return ['success' => true];
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
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }
        $arr = $waybillContent->attributes;

        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if ($orderContent) {
            $allMap = AllMaps::findOne(['product_id' => $orderContent->product_id]);
            if ($allMap) {
                $arr['koef'] = $allMap->koef;
                $arr['serviceproduct_id'] = $allMap->serviceproduct_id;
                $arr['store_rid'] = $allMap->store_rid;
                $outerProduct = OuterProduct::findOne(['id' => $allMap->serviceproduct_id]);
                if ($outerProduct) {
                    $arr['outer_product_name'] = $outerProduct->name;
                    $arr['outer_product_id'] = $outerProduct->id;
                    $arr['product_id_equality'] = true;
                } else {
                    $arr['product_id_equality'] = false;
                }
                $outerStore = OuterStore::findOne(['outer_uid' => $allMap->store_rid]);
                if ($outerStore) {
                    $arr['outer_store_name'] = $outerStore->name;
                    $arr['outer_store_id'] = $outerStore->id;
                    $arr['store_id_equality'] = true;
                } else {
                    $arr['store_id_equality'] = false;
                }
                $outerUnit = OuterUnit::findOne(['outer_uid' => $allMap->unit_rid]);
                if ($outerUnit) {
                    $arr['outer_unit_name'] = $outerUnit->name;
                    $arr['outer_unit_id'] = $outerUnit->id;
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
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }
        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }
        if (isset($post['vat_waybill'])) {
            $waybillContent->vat_waybill = (float)$post['vat_waybill'];
        }
        if (isset($post['outer_unit_id'])) {
            $waybillContent->outer_unit_id = (float)$post['outer_unit_id'];
        }
        $koef = null;
        $quan = null;
        if (isset($post['koef'])) {
            $koef = (float)$post['koef'];
        }
        if (isset($post['quantity_waybill'])) {
            $quan = (int)$post['quantity_waybill'];
        }

        return $this->handleWaybillContent($waybillContent, $post, $quan, $koef);
    }

    /**
     * @param WaybillContent $waybillContent
     * @param                $post
     * @param                $quan
     * @param                $koef
     * @return array
     */
    private function handleWaybillContent($waybillContent, $post, $quan, $koef)
    {
        if (isset($post['outer_product_id'])) {
            $waybillContent->outer_product_id = $post['outer_product_id'];
            //TODO refactor
            // поиск должен осуществляться по орг_ид
            $allMap = AllMaps::findOne(['product_id' => $post['outer_product_id']]);
            if ($allMap) {
                $outerStore = OuterStore::findOne(['id' => $allMap->store_rid]);
                if ($outerStore) {
                    $waybill = Waybill::findOne(['id' => $waybillContent->waybill_id]);
                    if ($waybill) {
                        $waybill->outer_store_uuid = $outerStore->outer_uid;
                        $waybill->save();
                    }
                }
            }
        }
        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if (!$orderContent) {
            if (isset($post['price_without_vat'])) {
                $waybillContent->price_without_vat = (int)$post['price_without_vat'];
                if (isset($post['vat_waybill'])) {
                    $waybillContent->price_with_vat = (int)($post['price_without_vat'] + ($post['price_without_vat'] * $post['vat_waybill']));
                    if (isset($post['quantity_waybill'])) {
                        $waybillContent->sum_without_vat = (int)$post['price_without_vat'] * $post['quantity_waybill'];
                        $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $post['quantity_waybill'];
                    }
                }
            }
        } else {
            if (isset($post['quantity_waybill']) && !isset($post['koef'])) {
                $koef = $post['quantity_waybill'] / $orderContent->quantity;
            }
            if (isset($post['koef']) && !isset($post['quantity_waybill'])) {
                $quan = $orderContent->quantity * $post['koef'];
            }
        }
        $waybillContent->quantity_waybill = $quan;
        $waybillContent->koef = $koef;
        $waybillContent->save();
        return ['success' => true, 'koef' => $koef, 'quantity' => $quan];
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
        if (!isset($post['waybill_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_id");
        }

        $waybill = Waybill::findOne(['id' => $post['waybill_id']]);
        if (!$waybill) {
            throw new BadRequestHttpException("waybill not found");
        }
        if (!$waybill->order_id) {
            throw new BadRequestHttpException("empty order_id");
        }

        $waybillContent = new WaybillContent();
        if (isset($post['waybill_id'])) {
            $waybillContent->waybill_id = $post['waybill_id'];
        }
        if (isset($post['vat_waybill'])) {
            $waybillContent->vat_waybill = (float)$post['vat_waybill'];
        }
        if (isset($post['outer_unit_id'])) {
            $waybillContent->outer_unit_id = (float)$post['outer_unit_id'];
        }
        if (isset($post['quantity_waybill'])) {
            $waybillContent->quantity_waybill = (int)$post['quantity_waybill'];
        }
        if (isset($post['outer_product_id'])) {
            $waybillContent->outer_product_id = $post['outer_product_id'];
        }

        if (isset($post['price_without_vat'])) {
            $waybillContent->price_without_vat = (int)$post['price_without_vat'];
            if (isset($post['vat_waybill'])) {
                $waybillContent->price_with_vat = (int)($post['price_without_vat'] + ($post['price_without_vat'] * $post['vat_waybill']));
                if (isset($post['quantity_waybill'])) {
                    $waybillContent->sum_without_vat = (int)$post['price_without_vat'] * $post['quantity_waybill'];
                    $waybillContent->sum_with_vat = $waybillContent->price_with_vat * $post['quantity_waybill'];
                }
            }
        }

        $waybillContent->save();

        return ['success' => true, 'waybill_content_id' => $waybillContent->id];
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
        if (!isset($post['waybill_content_id'])) {
            throw new BadRequestHttpException("empty_param|waybill_content_id");
        }

        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }

        $waybillContent->delete();

        return ['success' => true];
    }

    /**
     * integration: список сопоставления со всеми связями
     *
     * @param array $post
     * @return array
     */

    public function getProductMapList(array $post): array
    {
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);
        $dataProvider = (new OuterProductMapSearch())->search($this->user->organization, $post);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareOutProductMap($model);
        }

        return [
            'products'   => $result,
            'pagination' => [
                'page'       => $dataProvider->pagination->page + 1,
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
    public function mapUpdate(array $post)
    {
        if (!isset($post['service_id'])) {
            throw new BadRequestHttpException("empty_param|service_id");
        }

        if (!isset($post['map'])) {
            throw new BadRequestHttpException("empty_param|map");
        }

        $result = [];
        foreach ($post['map'] as $item) {
            try {
                $this->editProductMap($post['service_id'], $item);
                $result[$item['product_id']] = ['success' => true];
            } catch (\Exception $e) {
                $result[$item['product_id']] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        return $result;
    }

    /**
     * Изменение атрибутов сопоставления
     *
     * @param int $service_id
     * @param     $request
     * @return array
     */
    private function editProductMap(int $service_id, $request)
    {
        if (!isset($request['product_id'])) {
            throw new BadRequestHttpException("empty_param|product__id");
        }

        //Загружаем данные по базовому и дочерним бизнесам (если бизнес главный)
        $mainOrg = OuterProductMap::getMainOrg($this->user->organization_id);
        $orgs = OuterProductMap::getChildOrgsId($this->user->organization_id);
        $orgs[] = $this->user->organization_id;

        if (isset($request['outer_product_id'])) {
            //Если бизнес не главный то менять соспоставление с продуктом нельзя
            if ($mainOrg) {
                unset($request['outer_product_id']);
            } else {
                //Проверяем что сопоставляемый продукт свзан с нашей организацией
                $check = OuterProduct::find()
                    ->where(['id' => $request['outer_product_id']])
                    ->andWhere(['org_id' => $this->user->organization_id])
                    ->one();

                if (!$check) {
                    throw new Exception('outer product not found');
                }
            }
        }

        //Проверяем что сопоставляемый склад свзан с нашей организацией
        if (isset($request['outer_store_id'])) {
            $check = OuterStore::find()->where(['id' => $request['outer_store_id']])
                ->andWhere(['org_id' => $this->user->organization_id])
                ->one();

            if (!$check) {
                throw new Exception('outer store not found');
            }
        }

        //Если меняется сопоставление с продуктом, и бищнес главнй и есть дочерние бизнесы, то обновляем соспоставление в их записях
        if (isset($request['outer_product_id']) && count($orgs) > 1 && !$mainOrg) {
            $condition = [
                'and',
                ['service_id' => $service_id],
                ['product_id' => $request['product_id']],
                ['in', 'organization_id', $orgs]
            ];

            OuterProductMap::updateAll(['outer_product_id' => $request['outer_product_id']], $condition);
        }

        //Ищем запись для редактирования
        $model = OuterProductMap::findOne(['product_id' => $request['product_id'], 'service_id' => $service_id, 'organization_id' => $this->user->organization_id]);
        if (!$mainOrg) {
            if (!$model) {
                $model = new OuterProductMap();
                $model->service_id = $service_id;
                $model->organization_id = $this->user->organization_id;
            }
        } else {
            //Создаем дубликат запииси для дочерней организации при необходимости или новую запись
            if ($model->organization_id != $this->user->organization_id) {
                $mainAttributes = $model->attributes();
                $model = new OuterProductMap();
                $model->service_id = $mainAttributes['service_id'];
                $model->organization_id = $this->user->organization_id;
                $model->vendor_id = $mainAttributes['vendor_id'];
                $model->product_id = $mainAttributes['product_id'];
                $model->outer_product_id = $mainAttributes['outer_product_id'];
                $model->outer_unit_id = $mainAttributes['outer_unit_id'];
                $model->outer_store_id = $mainAttributes['outer_store_id'];
                $model->coefficient = $mainAttributes['coefficient'];
                $model->vat = $mainAttributes['vat'];
            }
        }

        $model->attributes = $request;
        $model->outer_unit_id = $model->outerProduct->outer_unit_id;
        $model->vendor_id = $model->product->supp_org_id;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }
    }

    /**
     * Информация по сопоставлению продукта
     *
     * @param OuterProductMap $model
     * @return array
     */
    private function prepareOutProductMap(array $model)
    {
        $result = [
            "id"              => $model['id'],
            "service_id"      => $model['service_id'],
            "organization_id" => $model['organization_id'],
            "vendor_id"       => $model['vendor_id'],
        ];

        if (isset($model['product_id'])) {
            $result ["product"] = [
                "id"   => $model['product_id'],
                "name" => $model['product_name'],
            ];
            $result["unit"] = [
                "name" => $model['unit'],
            ];
        } else {
            $result ["product"] = null;
            $result["unit"] = null;
        }

        if (isset($model['outer_product_id'])) {
            $result ["outer_product"] = [
                "id"   => $model['outer_product_id'],
                "name" => $model['outer_product_name']
            ];
        } else {
            $result ["outer_product"] = null;
        }

        if (isset($model["outer_unit_id"])) {
            $result["outer_unit"] = [
                "id"   => $model['outer_unit_id'],
                "name" => $model['outer_unit_name']
            ];
        } else {
            $result["outer_unit"] = null;
        }

        if (isset($model['outer_store_id'])) {
            $result["outer_store"] = [
                "id"   => $model['outer_store_id'],
                "name" => $model['outer_store_name']
            ];
        } else {
            $result["outer_store"] = null;
        }

        $result["coefficient"] = $model['coefficient'];
        $result["vat"] = $model['vat'];
        $result["created_at"] = isset ($model['created_at']) ? date("Y-m-d H:i:s T", strtotime($model['created_at'])) : null;
        $result["updated_at"] = isset($model['updated_at']) ? date("Y-m-d H:i:s T", strtotime($model['updated_at'])) : null;
        return $result;
    }
}