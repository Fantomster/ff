<?php

namespace api_web\classes;

use api\common\models\AllMaps;
use api_web\components\Registry;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
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
        $license = License::checkByServiceId($this->user->organization_id, $request['service_id']);
        if ($license) {
            $this->user->integration_service_id = $request['service_id'];
            $this->user->save();
            return ['result' => true];
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
        if (!isset($post['service_id'])) {
            throw new BadRequestHttpException("empty_param|service_id");
        }

        $organizationID = $this->user->organization_id;
        $acquirerID = $organizationID;
        $ediNumber = '';
        $outerAgentId = '';
        $outerStoreId = '';

        if (isset($post['order_id'])) {
            $order = Order::findOne(['id' => (int)$post['order_id'], 'client_id' => $this->user->organization_id]);

            if (!$order) {
                throw new BadRequestHttpException("order_not_found");
            }
            $outerAgent = OuterAgent::findOne(['vendor_id' => $order->vendor_id]);
            if ($outerAgent) {
                $outerAgentId = $outerAgent->id;
            }
            $outerStore = OuterStore::findOne(['org_id' => $organizationID]);
            if ($outerStore) {
                $outerStoreId = $outerStore->id;
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

        $arr['product'] = [
            'name'     => null,
            'id'       => null
        ];

        $arr['outer_product'] = [
            'name'     => null,
            'id'       => null,
            'equality' => false
        ];

        $arr['outer_store'] = [
            'name'     => null,
            'id'       => null,
            'equality' => false
        ];

        $arr['outer_unit'] = [
            'name' => null,
            'id'   => null
        ];

        $arr['vat_waybill'] = [
            'value'     => $waybillContent->vat_waybill,
            'equality'  => false
        ];

        $arr['koef'] = [
            'value'     => $waybillContent->koef,
            'equality'  => false
        ];

        //Если есть связь, с заказом
        $orderContent = OrderContent::findOne(['id' => $waybillContent->order_content_id]);
        if ($orderContent) {

            //Вернуть продукт поставщика
            $orderContentProduct = CatalogBaseGoods::findOne(['id' => $orderContent->product_id]);
            if($orderContentProduct) {
                $arr['product']['id'] = $orderContent->product_id;
                $arr['product']['name'] = $orderContentProduct->product;
            }

            //получаем из массового сопоставления
            //todo_refactor from helper with main_org mapping
            $outerProductMap = OuterProductMap::findOne(['product_id' => $orderContent->product_id]);
            if ($outerProductMap) {
                //Если отличаются продукты, надо подсвечивать на фронте
                if ($waybillContent->outer_product_id != $outerProductMap->outer_product_id) {
                    $arr['outer_product']['equality'] = true;
                }
                //Если отличаются склады, надо подсвечивать на фронте
                if ($waybillContent->waybill->outer_store_id != $outerProductMap->outer_store_id) {
                    $arr['outer_store']['equality'] = true;
                }
                //Если ставка НДС отличается, то надо подсвечивать на фронте
                $arr['vat_waybill']['equality'] = (!empty($outerProductMap->vat) &&
                    $outerProductMap->vat == $waybillContent->vat_waybill);
                //Если коэффициент отличается, то надо подсвечивать на фронте
                $arr['koef']['equality'] = (!empty($outerProductMap->coefficient) &&
                    $outerProductMap->coefficient == $waybillContent->koef);
            }
        }

        $outerProduct = OuterProduct::findOne(['id' => $waybillContent->outer_product_id]);
        if ($outerProduct) {
            $arr['outer_product'] = [
                'name'     => $outerProduct->name,
                'id'       => $outerProduct->id
            ];
        }

        $outerStore = OuterStore::findOne(['id' => $waybillContent->waybill->outer_store_id]);
        if ($outerStore) {
            $arr['outer_store'] = [
                'name'     => $outerStore->name,
                'id'       => $outerStore->id
            ];
        }

        $outerUnit = OuterUnit::findOne(['id' => $waybillContent->outer_unit_id]);
        if ($outerUnit) {
            $arr['outer_unit'] = [
                'name' => $outerUnit->name,
                'id'   => $outerUnit->id
            ];
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
        $waybillContent = WaybillContent::findOne(['id' => $post['waybill_content_id']]);
        if (!$waybillContent) {
            throw new BadRequestHttpException("waybill content not found");
        }
        //Обновим внешний продукт и ед. измерения
        if (isset($post['outer_product_id']) && !empty($post['outer_product_id'])) {
            $waybillContent->outer_product_id = $post['outer_product_id'];
            $waybillContent->outer_unit_id = $waybillContent->productOuter->outer_unit_id;
        }

        //Если один из параметров был изменен, будем делать пересчет
        $evaluteCalc = (isset($post['vat_waybill']) ||
                        isset($post['quantity_waybill']) ||
                        isset($post['price_without_vat']) ||
                        isset($post['price_with_vat']) ||
                        isset($post['sum_without_vat']) ||
                        isset($post['sum_with_vat'])
        );
        //Если собрались пересчитывать, но были присланы все суммы, считать не будем
        $evaluteCalc = !($evaluteCalc &&
                        isset($post['price_without_vat']) &&
                        isset($post['price_with_vat']) &&
                        isset($post['sum_without_vat']) &&
                        isset($post['sum_with_vat'])
        );
        //Заполнили все параметры и изменили полученные от фронта
        $wcVat =                (int)($post['vat_waybill'] ?? $waybillContent->vat_waybill);
        $wcKoef =               (float)($post['koef'] ?? $waybillContent->koef);
        $wcQuantity =           (float)($post['quantity_waybill'] ?? $waybillContent->quantity_waybill);
        $wcPrice =              (float)($post['price_without_vat'] ?? $waybillContent->price_without_vat);
        $wcPriceVat =           (float)($post['price_with_vat'] ?? $waybillContent->price_with_vat);
        $wcSum =                (float)($post['sum_without_vat'] ?? $waybillContent->sum_without_vat);
        $wcSumVat =             (float)($post['sum_with_vat'] ?? $waybillContent->sum_with_vat);

        if ($evaluteCalc) {
            if (isset($post['price_with_vat']) &&
                !isset($post['price_without_vat'])) {
                $wcPrice = ($wcPriceVat / (100 + $wcVat) * 100);
            }
            $wcPriceVat = $wcPrice / 100 * $wcVat + $wcPrice;
            $wcSum = $wcPrice * $wcQuantity;
            $wcSumVat = ($wcPrice / 100 * $wcVat + $wcPrice) * $wcQuantity;
        }

        $waybillContent->vat_waybill = $wcVat;
        $waybillContent->koef = $wcKoef;
        $waybillContent->quantity_waybill = $wcQuantity;
        $waybillContent->price_without_vat = number_format(round($wcPrice, 2), 2, '.', '');
        $waybillContent->price_with_vat = number_format(round($wcPriceVat, 2), 2, '.', '');
        $waybillContent->sum_without_vat = number_format(round($wcSum, 2), 2, '.', '');
        $waybillContent->sum_with_vat = number_format(round($wcSumVat, 2), 2, '.', '');

        try {
            //todo_refactor check model has been changed
            $waybillContent->save();
        } catch (\Exception $exception) {
            //todo_refactor change for valid exception
            throw $exception;
        }
        //Подготовим fake request
        $call = [
            'waybill_content_id'    => $waybillContent->id,
            'service_id'            => $waybillContent->waybill->service_id
        ];
        //Вернем обработанную модель деталей позиции
        return $this->showWaybillContent($call);
    }

    /**
     * @param WaybillContent $waybillContent
     * @param                $post
     * @param                $quan
     * @param                $koef
     * @throws \Exception
     * @return array
     */
    private function handleWaybillContent($waybillContent, $post, $quan, $koef)
    {
        return ['deprecated' => true];
        //DEPRECATED this suck stub
        if (!OuterProduct::find()->where(['id' => $post['outer_product_id']])->exists()) {
            throw new BadRequestHttpException('outer_product_not_found');
        }
        if (isset($post['outer_product_id'])) {
            $waybillContent->outer_product_id = $post['outer_product_id'];
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
        $this->validateRequest($post, ['waybill_id', 'outer_product_id', 'outer_unit_id']);

        $waybill = Waybill::findOne(['id' => $post['waybill_id'], 'acquirer_id' => $this->user->organization_id]);
        if (!$waybill) {
            throw new BadRequestHttpException("waybill_not_found");
        }

        $exists = WaybillContent::find()
            ->where([
                'waybill_id'       => $waybill->id,
                'outer_product_id' => $post['outer_product_id']
            ])->exists();

        if ($exists) {
            throw new BadRequestHttpException("waybill.content_exists");
        }

        $waybillContent = new WaybillContent();
        $waybillContent->waybill_id = $post['waybill_id'];
        $waybillContent->outer_product_id = $post['outer_product_id'];
        $waybillContent->outer_unit_id = (float)$post['outer_unit_id'];
        $waybillContent->vat_waybill = (int)$post['vat_waybill'] ?? 0;
        $waybillContent->quantity_waybill = $post['quantity_waybill'] ?? 1;

        if (!empty($post['price_without_vat'])) {
            $waybillContent->price_without_vat = round($post['price_without_vat'], 2);
            $waybillContent->sum_without_vat = round($post['price_without_vat'] * $waybillContent->quantity_waybill, 2);
            if ($waybillContent->vat_waybill != 0) {
                $waybillContent->price_with_vat = round(($post['price_without_vat'] + (($post['price_without_vat'] / 100) * $post['vat_waybill'])), 2);
            }
        }

        $waybillContent->sum_with_vat = round($waybillContent->price_with_vat * $waybillContent->quantity_waybill, 2);
        $waybillContent->save();

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
            throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.waibill_not_found', ['ru' => 'Накладная не найдена']));
        }

        if ($waybillCheck->service_id != $post['service_id']) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.waibill_not_relation_this_service', ['ru' => 'Накладная не связана с заданным сервисом']));
        }

        if ($waybillCheck->status_id == Registry::WAYBILL_UNLOADED) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.waibill_is_unloading', ['ru' => 'Накладная в статусе выгружена']));
        }

        $waybillContentCheck = WaybillContent::find()
            ->where(['waybill_id' => $waybillCheck->id])
            ->andWhere('order_content_id is not null')
            ->one();
        if (isset($waybillContentCheck)) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.waibill_is_relation_order', ['ru' => 'Накладная связана с заказом']));
        }

        $businessList = (new UserWebApi())->getUserOrganizationBusinessList();

        $checkOrg = false;
        foreach ($businessList['result'] as $item) {
            if ($item['id'] == $waybillCheck->acquirer_id) {
                $checkOrg = true;
                break;
            }
        }

        if (!$checkOrg) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'waybill.waibill_not_releated_current_user', ['ru' => 'Накладная не пренадлежит организациям текущего пользователя']));
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            WaybillContent::deleteAll(['waybill_id' => $waybillCheck->id]);
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
        $this->validateRequest($post, ['service_id', 'map']);

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
        $this->validateRequest($request, ['product_id']);

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