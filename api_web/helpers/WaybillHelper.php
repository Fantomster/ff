<?php /** @noinspection PhpWrongStringConcatenationInspection */

/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/29/2018
 * Time: 1:11 PM
 */

namespace api_web\helpers;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\models\User;
use api_web\modules\integration\classes\SyncServiceFactory;
use common\helpers\DBNameHelper;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use common\models\Journal;
use common\models\licenses\License;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterProduct;
use common\models\Waybill;
use common\models\WaybillContent;
use yii\db\Query;
use yii\db\Transaction;
use yii\web\BadRequestHttpException;

/**
 * Waybills class for generate\update\delete\ actions
 * */
class WaybillHelper
{
    /**
     * @var OuterProductMapHelper
     */
    private $helper;

    /**
     * @var array настройки огранизации по всем сервисам
     */
    public $settings;

    /**
     * @var User Пользователь текущего заказа
     */
    public $user;

    /**
     * @var
     */
    public $orgId;

    const EDI_DEFAULT_SEPARATOR = '-';
    /**
     * Разделители для генерации номеров входящего документа
     */
    const EDI_NUMBER_CODE_SEPARATORS = [
    ];

    /**
     * Разделители для генерации номеров счета фактуры
     */
    const EDI_NUMBER_ADDITIONAL_SEPARATORS = [
        Registry::RK_SERVICE_ID => 0
    ];

    /**
     * WaybillHelper constructor.
     */
    public function __construct()
    {
        $this->helper = new OuterProductMapHelper();
    }

    /**
     * @param       $order_id
     * @param null  $arOrderContentForCreate С EDI может приходить несколькими файлами orderContent для одного заказа
     * @param null  $supplierOrgId
     * @param array $arExcludedService
     * @return mixed
     * @throws BadRequestHttpException | \Exception
     * @throws ValidationException
     */
    public function createWaybill($order_id, $arOrderContentForCreate = null, $supplierOrgId = null, $arExcludedService = [])
    {
        $order = Order::findOne($order_id);
        if (!$order) {
            throw new BadRequestHttpException('order_not_found');
        }
        $this->user = $order->createdBy;
        if (!$this->user) {
            throw new BadRequestHttpException('user_not_found');
        }
        if (is_null($arOrderContentForCreate)) {
            $arOrderContentForCreate = $order->orderContent;
        }
        if (!$arOrderContentForCreate) {
            throw new BadRequestHttpException('waybill.you_dont_have_order_content');
        }
        $licenses = License::getAllLicense($order->client_id, Registry::$waybill_services, true);
        if (!$licenses) {
            throw new BadRequestHttpException('waybill.you_dont_have_licenses_for_services');
        }

        $waybillModels = [];
        foreach ($licenses as $license) {
            //Счетчики для выброса Exception
            $mapCount = 0;
            $skipCount = 0;
            $skipByStore = 0;
            $arMappedForStores = [];
            $serviceId = $license['service_id'];
            if (!empty($arExcludedService) && in_array($serviceId, $arExcludedService)) {
                continue;
            }

            $waybillContents = WaybillContent::find()
                ->leftJoin('waybill w', 'w.id=waybill_content.waybill_id')
                ->where(['order_content_id' => array_keys($order->orderContent), 'w.service_id' => $serviceId])
                ->indexBy('order_content_id')->all();
            $notInWaybillContent = array_diff_key($arOrderContentForCreate, $waybillContents);

            if (empty($notInWaybillContent)) {
                $this->throwException($serviceId, $order->client_id, 'waybill.you_dont_have_order_content_for_waybills');
                continue;
            }

            try {
                $rows = $this->helper->getMapForOrder($order, $serviceId);
            } catch (\Throwable $t) {
                \Yii::error($t->getMessage(), 'waybill_create');
                $this->writeInJournal($t->getMessage(), $serviceId, $order->client_id, 'error');
            }

            if (empty($rows)) {
                $this->throwException($serviceId, $order->client_id, 'waybill.you_dont_have_mapped_products');
                continue;
            }

            //Склад по умолчанию, у контрагента
            $defaultStoreAgent = null;
            if ($supplierOrgId) {
                $vendorId = $supplierOrgId;
            } else {
                $vendorId = $order->vendor_id;
            }
            $agent = OuterAgent::findOne(['vendor_id' => $vendorId, 'org_id' => $order->client_id, 'service_id' => $serviceId, 'is_deleted' => 0]);
            if ($agent && !empty($agent->store_id)) {
                $defaultStoreAgent = $agent->store_id;
            }
            //Склад по умолчанию в настройках
            $defaultStoreConfig = IntegrationSettingValue::getSettingsByServiceId($serviceId, $order->client_id, ['defStore']);

            // Remap for 1 store = 1 waybill
            foreach ($rows as $row) {
                $arMappedForStores[$row['outer_store_id']][$row['product_id']] = $row;
            }
            foreach ($arMappedForStores as $storeId => $storeProducts) {
                //Пытаемся найти хоть какой то склад
                if (!$storeId) {
                    $storeId = $defaultStoreAgent ?? $defaultStoreConfig ?? null;
                }
                if (!$storeId) {
                    $skipByStore++;
                    continue;
                }
                $mapCount++;
                $arOuterMappedProducts = $this->prepareStoreProducts($storeProducts, $notInWaybillContent);
                if (!empty($arOuterMappedProducts)) {
                    $waybillModels[] = $this->createWaybillAndContent($arOuterMappedProducts, $order->client_id,
                        $storeId, $serviceId, $agent);
                } else {
                    $skipCount++;
                }
            }

            // Если количество маппингов = числу пропусков, бросаем throw
            if ($mapCount === $skipCount) {
                $this->throwException($serviceId, $order->client_id, 'waybill.no_map_for_create_waybill');

            }
            // Если количество складов = числу пропусков, бросаем throw
            if (count($arMappedForStores) === $skipByStore) {
                $this->throwException($serviceId, $order->client_id, 'waybill.no_store_for_create_waybill');
            }
        }

        return $waybillModels;
    }

    /**
     * @param int $orgId
     * @return \common\models\Waybill
     */
    private function buildWaybill($orgId)
    {
        $model = new Waybill();
        $model->acquirer_id = $orgId;
        $model->doc_date = \gmdate('Y-m-d H:i:s');
        return $model;
    }

    /**
     * @param            $arOuterMappedProducts
     * @param            $orgId
     * @param null       $outerStoreId
     * @param null       $serviceId
     * @param OuterAgent $agent
     * @return Waybill
     * @throws \Exception
     */
    private function createWaybillAndContent($arOuterMappedProducts, $orgId, $outerStoreId, $serviceId, $agent)
    {
        $model = $this->buildWaybill($orgId);
        $model->outer_store_id = $outerStoreId;
        $model->outer_agent_id = $agent->id ?? null;
        $model->payment_delay = $agent->payment_delay ?? null;
        $model->service_id = $serviceId;
        $model->status_id = Registry::WAYBILL_COMPARED;
        //для каждого может быть разный
        $model->outer_number_code = $this->generateEdiNumber($arOuterMappedProducts, $serviceId);
        $model->outer_number_additional = $this->generateEdiNumber($arOuterMappedProducts, $serviceId, true);

        /** @var Transaction $transaction */
        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            if (!$model->save()) {
                throw new ValidationException($model->getErrors());
            }

            foreach ($arOuterMappedProducts as $mappedProduct) {
                /**@var OrderContent $ordCont */
                $ordCont = $mappedProduct['orderContent'];
                $price = $ordCont->price;
                $quantity = $ordCont->quantity;
                $taxRate = $mappedProduct['vat'];
                $priceWithVat = (float)($price + ($price * ($taxRate / 100)));
                $sumWithVat = $quantity * $priceWithVat;
                $sumWithoutVat = $quantity * $price;
                if (in_array($ordCont->order->service_id, Registry::$edo_documents) && !is_null($ordCont->vat_product)) {
                    $taxRate = $ordCont->vat_product ?? $taxRate;
                    $sumWithVat = $ordCont['into_price_sum_vat'] ?? $sumWithVat;
                    $sumWithoutVat = $ordCont['into_price_sum'] ?? $sumWithoutVat;
                    $priceWithVat = $ordCont['into_price_vat'] ?? $priceWithVat;
                }
                $modelWaybillContent = new WaybillContent();
                $modelWaybillContent->order_content_id = $ordCont->id;
                $modelWaybillContent->waybill_id = $model->id;
                $modelWaybillContent->outer_product_id = $mappedProduct['outer_product_id'];
                $modelWaybillContent->quantity_waybill = $quantity;
                $modelWaybillContent->vat_waybill = $taxRate;
                $modelWaybillContent->sum_with_vat = $sumWithVat;
                $modelWaybillContent->sum_without_vat = $sumWithoutVat;
                $modelWaybillContent->price_with_vat = $priceWithVat;
                $modelWaybillContent->price_without_vat = $price;
                $modelWaybillContent->koef = $mappedProduct['coefficient'];
                if (!$modelWaybillContent->save()) {
                    throw new ValidationException($modelWaybillContent->getErrors());
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->writeInJournal($e->getMessage(), $serviceId, $orgId, 'error');
        }
        return $model;
    }

    /**
     * Check if exist row with $uuid
     *
     * @param string $uuid
     * @return boolean
     * */
    public function checkWaybillForVsdUuid($uuid)
    {
        return WaybillContent::find()
            ->leftJoin(DBNameHelper::getMainName() . '.' . OrderContent::tableName() . ' as oc', 'oc.id = order_content_id')
            ->where(['oc.merc_uuid' => $uuid])
            ->exists();
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function createWaybillForApi($request)
    {
        if (empty($request['order_id'])) {
            throw new BadRequestHttpException('empty_param|order_id');
        }
        $result = $this->createWaybill($request['order_id']);

        return [
            'result' => $result
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function moveOrderContentToWaybill($request)
    {
        if (!isset($request['waybill_id'])) {
            throw new BadRequestHttpException('empty_param|waybill_id');
        }

        if (!isset($request['order_content_id'])) {
            throw new BadRequestHttpException('empty_param|order_content_id');
        }

        if (!isset($request['service_id'])) {
            throw new BadRequestHttpException('empty_param|service_id');
        }

        $waybill = Waybill::findOne([
            'id'         => (int)$request['waybill_id'],
            'service_id' => (int)$request['service_id'],
            'status_id'  => [
                Registry::WAYBILL_COMPARED,
                Registry::WAYBILL_ERROR,
                Registry::WAYBILL_FORMED,
            ]
        ]);
        if (!$waybill) {
            throw new BadRequestHttpException('waybill cannot adding waybill_content with id |' . $request['waybill_id']);
        }

        $orderContent = OrderContent::findOne($request['order_content_id']);
        if (!$orderContent) {
            throw new BadRequestHttpException('OrderContent dont exists with id|' . $request['order_content_id']);
        }

        $this->checkOrderForWaybillContent($waybill, $orderContent);

        $outerProduct = null;
        $outerProductMap = $this->helper->getMapForOrder($orderContent->order, $waybill->service_id, $orderContent->product_id);
        if (!empty($outerProductMap)) {
            $outerProductMap = (object)current($outerProductMap);
            $outerProduct = OuterProduct::findOne($outerProductMap->outer_product_id);
        }

        $coefficient = $outerProductMap->coefficient ?? 1;
        $quantity = $orderContent->quantity;
        $price = $orderContent->price;

        $taxRate = $outerProductMap->vat ?? $orderContent->vat_product ?? 0;
        if ($taxRate != 0) {
            $priceWithVat = $price + ($price * ($taxRate / 100));
        }

        try {
            $waybillContent = new WaybillContent();
            $waybillContent->waybill_id = $waybill->id;
            $waybillContent->order_content_id = $orderContent->id;
            $waybillContent->outer_product_id = empty($outerProduct) ? null : $outerProduct->id;
            $waybillContent->outer_unit_id = empty($outerProduct) ? null : $outerProduct->outer_unit_id;
            $waybillContent->quantity_waybill = (float)$quantity;
            $waybillContent->vat_waybill = $taxRate;
            $waybillContent->koef = $coefficient;
            $waybillContent->sum_with_vat = (isset($priceWithVat) ? round($priceWithVat * $quantity, 3) : null);
            $waybillContent->sum_without_vat = round($price * $quantity, 3);
            $waybillContent->price_with_vat = (isset($priceWithVat) ? round($priceWithVat, 3) : null);
            $waybillContent->price_without_vat = round($price, 3);
            if (!$waybillContent->validate() || !$waybillContent->save()) {
                throw new ValidationException($waybillContent->getFirstErrors());
            }
            //Если эта позиция не готова к выгрузке, меняем статус накладной
            if ($waybillContent->readyToExport === false) {
                $waybill->status_id = Registry::WAYBILL_FORMED;
                if (!$waybill->save()) {
                    throw new ValidationException($waybill->getFirstErrors());
                }
            }
        } catch (\Throwable $t) {
            \Yii::error($t->getMessage());
            throw $t;
        }

        return ['result' => true];
    }

    /**
     * Проверка на правильность добавления позиции заказа к накладной
     * Нельзя добавить к накладной, имеющей позиции из одного заказа, позиции из другой заказа
     * Так же нельзя добавить позицию заказа, уже имеющую позицию в накладной
     *
     * @param Waybill      $waybill
     * @param OrderContent $orderContent
     * @throws BadRequestHttpException
     */
    private function checkOrderForWaybillContent(Waybill $waybill, OrderContent $orderContent)
    {
        if ($orderContent->getWaybillContent()
            ->onCondition(['waybill_id' => $waybill->id])
            ->exists()
        ) {
            throw new BadRequestHttpException('waybill.order_content_allready_has_waybill_content|' . $orderContent->waybillContent->id);
        }

        $waybillContent = WaybillContent::find()
            ->where(['waybill_id' => $waybill->id])
            ->andWhere(['not', ['order_content_id' => null]])
            ->one();

        if ($waybillContent) {
            $orderContentFromWaybill = $waybillContent->orderContent;
            if ($orderContent->order_id != $orderContentFromWaybill->order_id) {
                throw new BadRequestHttpException('waybill.order_content_not_for_this_waybill');
            }
        }
    }

    /**
     * @param $storeProducts
     * @param $notInWaybillContent
     * @return array
     */
    private function prepareStoreProducts($storeProducts, $notInWaybillContent)
    {
        $arStoreProducts = [];
        foreach ($notInWaybillContent as $item) {
            /**@var OrderContent $item */
            if (array_key_exists($item->product_id, $storeProducts)) {
                $outer_product_id = $storeProducts[$item->product_id]['outer_product_id'];
                $arStoreProducts[] = [
                    'product_id'       => $item->product_id,
                    'outer_store_id'   => $storeProducts[$item->product_id]['outer_store_id'],
                    'vat'              => $storeProducts[$item->product_id]['vat'],
                    'coefficient'      => $storeProducts[$item->product_id]['coefficient'],
                    'outer_product_id' => $outer_product_id,
                    'orderContent'     => $item,
                ];
            }
        }

        return $arStoreProducts;
    }

    /**
     * @param      $arOuterStoreProducts
     * @param int  $serviceId
     * @param bool $onlyByOrderId
     * @return int|mixed|string
     */
    private function generateEdiNumber($arOuterStoreProducts, $serviceId, $onlyByOrderId = false)
    {
        /**@var OrderContent $orderContent */
        $orderContent = current($arOuterStoreProducts)['orderContent'];
        $tmp_ed_num = $orderContent->order_id;
        $waybillSearchField = $onlyByOrderId ? 'outer_number_additional' : 'outer_number_code';

        $separator = $this->getEdiNumberSeparator($serviceId, $onlyByOrderId);

        if ($orderContent->edi_number && !$onlyByOrderId) {
            $tmp_ed_num = $orderContent->edi_number;
        }
        $ed_num = $tmp_ed_num . $separator . '1';

        $existOrderContent = OrderContent::find()
            ->where(['like', 'edi_number', $tmp_ed_num])
            ->andWhere(['order_id' => $orderContent->order_id])
            ->orderBy(['edi_number' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($existOrderContent) {
            $existWaybill = Waybill::find()
                ->where(['like', $waybillSearchField, $tmp_ed_num])
                ->andWhere(['service_id' => $serviceId])
                ->orderBy([$waybillSearchField => SORT_DESC])
                ->limit(1)
                ->one();
            $ediNumber = $existWaybill->{$waybillSearchField} ?? $existOrderContent->edi_number;
            $ed_num = $this->getLastEdiNumber($ediNumber, $tmp_ed_num, $separator);
        } else {
            $existWaybill = Waybill::find()->where(['like', $waybillSearchField, $tmp_ed_num])
                ->andWhere(['service_id' => $serviceId])
                ->orderBy([$waybillSearchField => SORT_DESC])
                ->limit(1)
                ->one();
            if ($existWaybill) {
                $ed_num = $this->getLastEdiNumber($existWaybill->{$waybillSearchField}, $tmp_ed_num, $separator);
            }
        }
        return $ed_num;
    }

    /**
     * @param        $ediNumber
     * @param        $tmp_ed_num
     * @param string $separator
     * @return string
     */
    public function getLastEdiNumber($ediNumber, $tmp_ed_num, $separator = self::EDI_DEFAULT_SEPARATOR)
    {
        $postfix = substr($ediNumber, (strlen($tmp_ed_num) + strlen($separator)));
        $i = (int)filter_var($postfix, FILTER_VALIDATE_INT);
        return $tmp_ed_num . $separator . ++$i;
    }

    /**
     * @param $request
     * @throws ValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function sendWaybillAsync($request)
    {
        /** @var \Redis $redis */
        $redis = \Yii::$app->get('redis');
        //Имя строки блокировки если уже идет обработка по этим параметрам
        $lockName = implode('-', ['lock-start', $request['action_id'], $request['order_id'], $request['vendor_id']]);
        //Проверяем блокировку
        $run = $redis->get($lockName);
        //Если нет, запускаем
        if (is_null($run)) {
            //Блокируем обработку этого заказа
            $redis->set($lockName, 1);
            $order = Order::findOne($request['order_id']);
            $this->orgId = $order->client_id;
            $this->user = $order->createdBy;

            try {
                $this->createWaybill($request['order_id'], null, $request['vendor_id'], $this->getExcludedServices());
            } catch (\Throwable $e) {
                //Запись ошибки в журнал, здесь нет service_id
                $this->writeInJournal($e->getMessage(), 0, $this->orgId, 'error');
            }
            $waybillToService = [];
            $query = $this->createQueryWaybillToOrder($request['order_id']);
            $dbResult = $query->andWhere(['status_id' => [Registry::WAYBILL_ERROR, Registry::WAYBILL_COMPARED]])
                ->all(\Yii::$app->db_api);

            foreach ($dbResult as $row) {
                $waybillToService[$row['service_id']][] = $row['id'];
            }
            /**
             * Отправка накладных
             *
             * @var Transaction $t
             **/
            try {
                foreach ($waybillToService as $serviceId => $ids) {
                    $scenario = IntegrationSettingValue::getSettingsByServiceId(
                        $serviceId,
                        $this->orgId,
                        ['auto_unload_invoice']
                    );
                    if ($scenario == 1) {
                        $t = \Yii::$app->db_api->beginTransaction();
                        try {
                            #Отправка накладных
                            $factory = (new SyncServiceFactory($serviceId, [], SyncServiceFactory::TASK_SYNC_GET_LOG))->factory($serviceId);
                            $message = $factory->sendWaybill([
                                'service_id' => $serviceId,
                                'ids'        => $ids,
                            ]);
                            $this->writeInJournal($message, $serviceId, $this->orgId);
                            $t->commit();
                        } catch (\Throwable $e) {
                            $t->rollBack();
                            $this->writeInJournal($e->getMessage(), $serviceId, $this->orgId, 'error');
                        }
                    }
                }
            } catch (\Throwable $e) {
                //Запись ошибки в журнал, здесь нет service_id
                $this->writeInJournal($e->getMessage(), 0, $this->orgId, 'error');
            } finally {
                //Снятие лока с обработки
                $redis->del($lockName);
            }
        }
    }

    /**
     * Запись в журнал
     *
     * @param        $message
     * @param        $service_id
     * @param int    $orgId
     * @param string $type
     * @throws ValidationException
     */
    private function writeInJournal($message, $service_id, int $orgId = 0, $type = 'success'): void
    {
        $journal = new Journal();
        $journal->response = is_array($message) ? json_encode($message) : $message;
        $journal->service_id = (int)$service_id;
        $journal->type = $type;
        $journal->log_guide = 'CreateWaybill';
        $journal->organization_id = $orgId;
        $journal->user_id = (\Yii::$app instanceof \Yii\web\Application && isset($this->user->id)) ? $this->user->id : null;
        $journal->operation_code = (string)(Registry::$operation_code_send_waybill[$service_id] ?? 0);
        if (!$journal->save()) {
            throw new ValidationException($journal->getFirstErrors());
        }
    }

    /**
     * Создает запрос для выборки всех накладных для одного заказа
     *
     * @param $orderId
     * @return Query
     */
    public function createQueryWaybillToOrder($orderId)
    {
        return (new Query())->distinct()->select(['w.id', 'w.service_id'])
            ->from('waybill w')
            ->leftJoin('waybill_content wc', 'w.id=wc.waybill_id')
            ->leftJoin(DBNameHelper::getMainName() . '.' . OrderContent::tableName() . ' as oc', 'oc.id=wc.order_content_id')
            ->where(['oc.order_id' => $orderId]);
    }

    /**
     * Установить свойство settings для всех сервисов, если будут нужны еще где то,
     * перенести вызов из sendWaybillAsync() в метод __construct()
     */
    public function setAutoInvoiceSettings(): void
    {
        $this->settings = (new Query())->select(['iss.service_id', 'isv.value', 'iss.name', 'isv.id'])
            ->from(IntegrationSettingValue::tableName() . ' as isv')
            ->leftJoin(IntegrationSetting::tableName() . ' as iss', 'iss.id = isv.setting_id')
            ->where([
                'isv.org_id' => $this->orgId,
                'iss.name'   => 'auto_unload_invoice',
            ])->all(\Yii::$app->db_api);
    }

    /**
     * Получить сервисы по которым не надо создавать накладные
     *
     * @return array
     */
    public function getExcludedServices()
    {
        $this->setAutoInvoiceSettings();
        $arExcludedServices = [];
        foreach ($this->settings as $setting) {
            if ($setting['value'] == 0) {
                $arExcludedServices[] = $setting['service_id'];
            }
        }

        return $arExcludedServices;
    }

    /**
     * @param $serviceId
     * @param $orgId
     * @param $error
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function throwException($serviceId, $orgId, $error)
    {
        $this->writeInJournal(\Yii::t('api_web', $error), $serviceId, $orgId, 'error');
        if (\Yii::$app instanceof \Yii\web\Application) {
            if (isset($this->user) && $this->user->integration_service_id == $serviceId) {
                throw new BadRequestHttpException($error);
            }
        }
    }

    /**
     * Разделитель между номером поставки и порядковым номером накладной
     *
     * @param string $serviceId
     * @param bool   $number_additional
     * @return mixed
     */
    public function getEdiNumberSeparator($serviceId, $number_additional = false)
    {
        $separators = $number_additional ? self::EDI_NUMBER_ADDITIONAL_SEPARATORS : self::EDI_NUMBER_CODE_SEPARATORS;
        return $separators[$serviceId] ?? self::EDI_DEFAULT_SEPARATOR;
    }
}
