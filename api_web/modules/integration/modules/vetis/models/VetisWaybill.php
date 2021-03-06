<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\mercLog;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use api_web\modules\integration\modules\vetis\api\mercury\CreatePrepareOutgoingConsignmentRequest;
use api_web\modules\integration\modules\vetis\api\mercury\CreateRegisterProductionRequest;
use api_web\modules\integration\modules\vetis\api\mercury\Mercury;
use common\models\search\MercStockEntrySearch;
use api_web\components\Registry;
use api_web\components\ValidateRequest;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use api_web\modules\integration\modules\vetis\api\mercury\mercuryApi;
use api_web\modules\integration\modules\vetis\api\mercury\VetDocumentDone;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
use common\models\licenses\LicenseOrganization;
use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisIngredients;
use common\models\vetis\VetisPackingType;
use common\models\vetis\VetisProductByType;
use common\models\vetis\VetisProductItem;
use common\models\vetis\VetisPurpose;
use common\models\vetis\VetisRussianEnterprise;
use common\models\vetis\VetisSubproductByProduct;
use common\models\vetis\VetisUnit;
use common\models\vetis\VetisTransport;
use frontend\modules\clientintegr\modules\merc\models\createStoreEntryForm;
use frontend\modules\clientintegr\modules\merc\models\expiryDate;
use frontend\modules\clientintegr\modules\merc\models\productForm;
use frontend\modules\clientintegr\modules\merc\models\productionDate;
use frontend\modules\clientintegr\modules\merc\models\rejectedForm;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class VetisWaybill
 *
 * @package api_web\modules\integration\modules\vetis\models
 */
class VetisWaybill extends WebApi
{
    /**
     * @var \api_web\modules\integration\modules\vetis\helpers\VetisHelper
     */
    private $helper;

    /**
     * VetisWaybill constructor.
     */
    public function __construct()
    {
        $this->helper = new VetisHelper();
        parent::__construct();
    }

    /**
     * Список сертифитаков сгруппированный по номеру заказа
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function getGroupsList($request)
    {
        $license = LicenseOrganization::getLicenseForOrganizationService($this->user->organization_id, Registry::MERC_SERVICE_ID);
        if (!isset($license)) {
            throw new BadRequestHttpException('vetis.active_license_not_found');
        }

        $reqPag = $request['pagination'] ?? [];
        $reqSearch = $request['search'] ?? [];
        $page = $this->helper->isSetDef($reqPag['page'] ?? null, 0);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'] ?? null, 12);

        //Поиск ВСД
        $search = new VetisWaybillSearch();
        $params = $this->helper->set($search, $reqSearch, ['acquirer_id', 'type', 'status', 'sender_guid', 'product_name', 'date']);
        $arResult = $search->search($params, $page, $pageSize);

        //Строим результат
        $result = [
            'items'  => $arResult['items'],
            'groups' => $arResult['groups']
        ];
        //Ответ для АПИ
        $return = [
            'result'     => $result,
            'pagination' => [
                'page'        => $page,
                'page_size'   => $pageSize,
                'total_count' => $arResult['count'],
            ]
        ];

        if ($page == 1) {
            $result = License::getAllLicense($arResult['org_ids'], Registry::MERC_SERVICE_ID, true);
            foreach ($result as $license) {
                try {
                    $this->sendRequestToUpdate($license['org_id']);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return $return;
    }

    /**
     * Отправка запроса на обновление
     *
     * @param $org_id
     */
    private function sendRequestToUpdate($org_id)
    {
        $enterpriseGuid = IntegrationSettingValue::getSettingsByServiceId(Registry::MERC_SERVICE_ID, $org_id, ['enterprise_guid']);
        if (!empty($enterpriseGuid) && strlen($enterpriseGuid) >= 36) {
            MercVsd::getUpdateData($org_id, $enterpriseGuid);
        }
    }

    /**
     * Получение ВСД по uuids
     *
     * @param array $uuids
     * @param array $arIncOut
     * @throws \Exception
     * @return array
     * */
    public function getList($uuids, $arIncOut = []): array
    {
        $result = $uuids;
        $models = MercVsd::findAll(['uuid' => array_keys($uuids)]);
        foreach ($models as $model) {
            $result[$model->uuid] = [
                'uuid'                => $model->uuid,
                'document_id'         => $uuids[$model->uuid],
                'product_name'        => $model->product_name,
                'sender_name'         => $model->sender_name,
                'status'              => $model->status,
                'status_text'         => MercVsd::$statuses[$model->status],
                'status_date'         => WebApiHelper::asDatetime($model->last_update_date),
                'amount'              => $model->amount,
                'unit'                => $model->unit,
                'production_date'     => WebApiHelper::asDatetime($model->production_date),
                'date_doc'            => WebApiHelper::asDatetime($model->date_doc),
                'vsd_direction'       => $arIncOut[$model->uuid] ?? null,
                'last_error'          => $model->last_error,
                'user_status'         => $model->user_status,
                'r13nСlause'          => (bool)$model->r13nClause,
                'location_prosperity' => (bool)!MercVsd::parsingLocationProsperity($model->location_prosperity),
            ];
        }

        return array_values($result);
    }

    /**
     * Формирование всех фильтров
     *
     * @return array
     * @throws \Exception
     */
    public function getFilters()
    {
        return [
            'result' => [
                'vsd'      => $this->getFilterVsd(),
                'statuses' => $this->getFilterStatus(),
                'sender'   => $this->getSenderOrProductFilter(['search' => 'sender_name'], 'sender_name'),
                'product'  => $this->getSenderOrProductFilter(['search' => 'product_name'], 'product_name'),
            ]
        ];
    }

    /**
     * Формирование массива для фильтра ВСД
     *
     * @return array
     * */
    public function getFilterVsd()
    {
        $inc = MercVsd::DOC_TYPE_INCOMMING;
        $out = MercVsd::DOC_TYPE_OUTGOING;
        $types = MercVsd::$types;
        return [
            'result' => [
                $inc  => $types[$inc],
                $out  => $types[$out],
                'all' => 'Все ВСД',
            ]
        ];
    }

    /**
     * Формирование массива для фильтра статусы
     *
     * @return array
     * */
    public function getFilterStatus()
    {
        return ['result' => array_merge(MercVsd::$statuses, ['all' => 'Все'])];
    }

    /**
     * Формирование массива для фильтра "По продукции" или по "Фирма отправитель" так же выполняет "живой" поиск лайком
     *
     * @param $request
     * @param $filterName
     * @return array
     * @throws \Exception
     */
    public function getSenderOrProductFilter($request, $filterName)
    {
        if (isset($request['search']['acquirer_id']) && !empty($request['search']['acquirer_id'])) {
            $businesses['result'] = array_fill_keys((!is_array($request['search']['acquirer_id'])) ? [$request['search']['acquirer_id']] : $request['search']['acquirer_id'], "");
            ValidateRequest::avaliableBusinessList(array_keys($businesses['result']), $this->user->id);
            $enterpriseGuides = $this->helper->getEnterpriseGuids($businesses);
        } else {
            $enterpriseGuides = $this->helper->getEnterpriseGuids();
        }

        $query = MercVsd::find()->select($filterName)->distinct();
        if (isset($request['search'][$filterName]) && !empty($request['search'][$filterName])) {
            $query->andWhere(['like', $filterName, $request['search'][$filterName]]);
        }

        if ($filterName == 'product_name') {
            $result = $query->andWhere(['or',
                ['sender_guid' => $enterpriseGuides],
                ['recipient_guid' => $enterpriseGuides]])
                ->indexBy('product_name')
                ->column();
        } else {
            $query->addSelect('sender_guid');
            $arResult = $query->andWhere(['recipient_guid' => $enterpriseGuides])->groupBy('sender_name')->all();
            $result = ArrayHelper::map($arResult, 'sender_guid', 'sender_name');
        }

        return ['result' => $result];
    }

    /**
     * Краткая информация о ВСД
     *
     * @param $request
     * @throws BadRequestHttpException
     * @return array
     */
    public function getShortInfoAboutVsd($request)
    {
        if (!isset($request['uuid'])) {
            throw new BadRequestHttpException('Uuid is required');
        }
        $obInfo = (new VetisHelper())->getShortInfoVsd($request['uuid']);

        return ['result' => $obInfo];
    }

    /**
     * Полная информация о ВСД
     *
     * @param $request
     * @throws BadRequestHttpException|\Exception
     * @return array
     */
    public function getFullInfoAboutVsd($request)
    {
        if (!isset($request['uuid'])) {
            throw new BadRequestHttpException('Uuid is required');
        }
        $obInfo = (new VetisHelper())->getFullInfoVsd($request['uuid']);

        return ['result' => $obInfo];
    }

    /**
     * Погашение ВСД
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function repayVsd($request)
    {
        if (!isset($request['uuid']) && empty($request['uuid'])) {
            throw new BadRequestHttpException('Uuid is required and must be array');
        }
        $records = $this->helper->getAvailableVsd($request['uuid']);
        try {
            $api = \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi::getInstance();
            if (array_key_exists($request['uuid'], $records)) {
                if ($api->getVetDocumentDone($request['uuid'])) {
                    $this->helper->setMercVsdUserStatus(MercVsd::USER_STATUS_EXTINGUISHED, $request['uuid']);
                }
            } else {
                throw new BadRequestHttpException('VSD does not belong to this organization|' . $request['uuid']);
            }
        } catch (\Throwable $t) {
            $error = $t->getMessage();
            $model = mercLog::findOne($error);
            if ($model) {
                $error = $model->description;
            }
            $this->helper->setLastError($error, $request['uuid']);
        }
        $vsd_direction = $this->helper->getVsdDirection($request['uuid'], $this->user->organization_id);

        return ['result' => $this->getList([$request['uuid'] => null], [$request['uuid'] => $vsd_direction])];
    }

    /**
     * Частичное погашение ВСД
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function partialAcceptance($request)
    {
        $uuid = $request['uuid'];
        if (!isset($uuid) || !isset($request['reason'])) {
            throw new BadRequestHttpException('Uuid and reason is required and must be array');
        }
        $record = $this->helper->getAvailableVsd($request['uuid']);
        if (!$record) {
            throw new BadRequestHttpException('Uuid not for this organization');
        }
        $params = [
            'decision'    => VetDocumentDone::PARTIALLY,
            'volume'      => $request['amount'],
            'reason'      => $request['reason'],
            'description' => $request['description'],
            'conditions'  => $request['conditions'] ?? null
        ];

        try {
            $api = \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi::getInstance();
            if ($api->getVetDocumentDone($uuid, $params)) {
                $this->helper->setMercVsdUserStatus(MercVsd::USER_STATUS_PARTIALLY_ACCEPTED, $uuid);
            }
        } catch (\Throwable $t) {
            $error = $t->getMessage();
            $model = mercLog::findOne($error);
            if ($model) {
                $error = $model->description;
            }
            $this->helper->setLastError($error, $uuid);
        }
        $vsd_direction = $this->helper->getVsdDirection($uuid, $this->user->organization_id);

        return ['result' => $this->getList([$uuid => null], [$uuid => $vsd_direction])];
    }

    /**
     * Возврат ВСД
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function returnVsd($request)
    {
        $uuid = $request['uuid'];
        if (!isset($uuid) || !isset($request['reason'])) {
            throw new BadRequestHttpException('Uuid and reason is required and must be array');
        }
        $record = $this->helper->getAvailableVsd($uuid);
        if (!$record) {
            throw new BadRequestHttpException('Uuid not for this organization');
        }
        $params = [
            'decision'    => VetDocumentDone::RETURN_ALL,
            'reason'      => $request['reason'],
            'description' => $request['description'],
            'conditions'  => $request['conditions'] ?? null
        ];

        try {
            $api = \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi::getInstance();
            if ($api->getVetDocumentDone($uuid, $params)) {
                $this->helper->setMercVsdUserStatus(MercVsd::USER_STATUS_RETURNED, $uuid);
            }
        } catch (\Throwable $t) {
            $error = $t->getMessage();
            $model = mercLog::findOne($error);
            if ($model) {
                $error = $model->description;
            }
            $this->helper->setLastError($error, $uuid);
        }
        $vsd_direction = $this->helper->getVsdDirection($uuid, $this->user->organization_id);

        return ['result' => $this->getList([$uuid => null], [$uuid => $vsd_direction])];
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function getNotConfirmedVsd($request)
    {
        $array = $this->helper->getEnterpriseGuids($request['org_id']);
        return [
            'result' => $this->helper->getNotConfirmedVsd($array),
        ];
    }

    /**
     * Получение ВСД в PDF
     *
     * @param $request
     * @throws \Exception
     * @return string
     */
    public function getVsdPdf($request)
    {
        if (!isset($request['uuid'])) {
            throw new BadRequestHttpException('Uuid is required');
        }

        $vsdHttp = $this->helper->generateVsdHttp();
        $check = $vsdHttp->checkAuthData();

        if (!$check['success']) {
            throw new BadRequestHttpException($check['error']);
        }

        $data = $vsdHttp->getPdfData($request['uuid'], $request['full']);
        $base64 = (isset($request['base64_encode']) && $request['base64_encode'] == 1 ? true : false);
        return ($base64 ? base64_encode($data) : $data);
    }

    /**
     * Погашение ВСД
     *
     * @param $request
     * @throws \Exception
     * @return array
     */
    public function getRegionalizationInfo($request)
    {
        if (!isset($request['uuid']) && empty($request['uuid'])) {
            throw new BadRequestHttpException('Uuid is required and must be array');
        }
        $records = $this->helper->getAvailableVsd([$request['uuid']]);
        try {
            $api = mercuryApi::getInstance();
            if (array_key_exists($request['uuid'], $records)) {
                $vsd = $records[$request['uuid']];
                $conditions = $api->getRegionalizationConditions($vsd['recipient_guid'], $vsd['sender_guid'], $vsd['sub_product_guid']);
                $result = ['relocation'             => true,
                           'reason_for_prohibition' => null];

                if (isset($conditions)) {
                    $result['relocation'] = false;
                    if (array_key_exists('reason_for_prohibition', $conditions)) {
                        $result['reason_for_prohibition'] = $conditions['$conditions'];
                    } else {
                        $result['conditions'] = $conditions;
                    }
                }
            } else {
                throw new BadRequestHttpException('VSD does not belong to this organization|' . $request['uuid']);
            }
        } catch (\Throwable $t) {
            $error = $t->getMessage();
            $model = mercLog::findOne($error);
            if ($model) {
                $error = $model->description;
            }
            $this->helper->setLastError($error, $request['uuid']);
            throw new BadRequestHttpException('Error getting data on regionalization');
        }

        return $result;
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidArgumentException
     */
    public function getProductItemList($request)
    {
        $reqPag = $request['pagination'] ?? [];
        $page = $this->helper->isSetDef($reqPag['page'] ?? null, 1);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'] ?? null, 12);
        $orgId = $request['business_id'] ?? $this->user->organization_id;
        $enterpriseGuid = $this->helper->getEnterpriseGuid($orgId);
        $query = VetisProductItem::find()->select(['name', 'uuid', 'guid', 'productType', 'code', 'globalID', 'gost', 'active'])
            ->where(['producer_guid' => $enterpriseGuid, 'active' => 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        $result = [];

        /**@var VetisProductItem $model */
        foreach ($dataProvider->models as $model) {
            $result[] = [
                'name'    => $model->name,
                'uuid'    => $model->uuid,
                'guid'    => $model->guid,
                'form'    => VetisHelper::$vetis_product_types[$model->productType],
                'article' => $model->code,
                'gtin'    => $model->globalID,
                'gost'    => $model->gost,
                'active'  => $model->active,
            ];
        }

        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * @param $request
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getProductSubtypeList($request)
    {
        $this->validateRequest($request, ['type_id']);
        $models = VetisProductByType::find()->select(['name', 'guid'])->distinct()->where(['productType' => $request['type_id']])->all();

        return $models;
    }

    /**
     * @param $request
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getProductFormList($request)
    {
        $this->validateRequest($request, ['guid']);
        $query = VetisSubproductByProduct::find()->select(['name', 'uuid', 'guid'])
            ->where(['productGuid' => $request['guid']]);
        if (isset($request['search']['name']) && !empty($request['search']['name'])) {
            $query->andWhere(['like', 'name', $request['search']['name'] . '%', false]);
        }

        return $query->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUnitList()
    {
        return VetisUnit::find()->select(['name', 'uuid', 'guid'])->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getPackingTypeList()
    {
        return VetisPackingType::find()->select(['name', 'uuid', 'guid'])->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getRussianEnterpriseList()
    {
        $issueId = $this->helper->getIssuerId($this->user->organization_id);

        return VetisRussianEnterprise::find()->select(['name', 'uuid', 'guid'])
            ->where(['owner_guid' => $issueId])->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     * @throws BadRequestHttpException
     */
    public function getBusinessEntity()
    {
        $issueId = $this->helper->getIssuerId($this->user->organization_id);

        return VetisBusinessEntity::find()->select(['name', 'uuid', 'guid'])
            ->where(['guid' => $issueId])->one();
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function getIngredientList($request)
    {
        $query = MercStockEntry::find()->select(['product_name'])->distinct()
            ->where(['owner_guid' => $this->helper->getEnterpriseGuid($this->user->organization_id)]);
        if (isset($request['search']['name']) && !empty($request['search']['name'])) {
            $query->andWhere(['like', 'product_name', $request['search']['name'] . '%', false]);
        }

        return $query->column();
    }

    /**
     * @param $request
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getProductIngredientList($request)
    {
        $this->validateRequest($request, ['guid']);

        return VetisIngredients::find()->select(['product_name', 'amount', 'id'])
            ->where(['guid' => $request['guid']])->all();
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function getProductInfo($request)
    {
        $this->validateRequest($request, ['guid']);
        /**@var VetisProductItem $model */
        $model = VetisProductItem::find()->joinWith(['subProduct', 'unit'])
            ->where(['vetis_product_item.guid' => $request['guid']])->one();
        if (!$model) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury();
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\products\Products();
        $attributes = unserialize($model->data);
        if (isset($attributes->producing->location->guid)) {
            $productionName = VetisRussianEnterprise::find()->select(['name', 'uuid', 'guid'])
                ->where(['guid' => $attributes->producing->location->guid])->one();
        }

        return [
            'form'             => $model->subProduct->name ?? null,
            'name'             => $model->name,
            'uuid'             => $model->uuid,
            'guid'             => $model->guid,
            'article'          => $model->code,
            'gtin'             => $model->globalID,
            'gost'             => $model->gost,
            'active'           => $model->active,
            'package_type'     => $model->unit->name ?? null,
            'package_quantity' => $model->packagingQuantity ?? null,
            'package_volume'   => $model->packagingVolume ?? null,
            'package_unit'     => $model->packingType->name ?? null,
            'producer_name'    => $this->getBusinessEntity()->name ?? null,
            'production_name'  => $productionName ?? null,
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @TODO_: refactoring duble code with getProductInfo method
     */
    public function getProductFullInfo($request)
    {
        $this->validateRequest($request, ['guid']);
        /**@var VetisProductItem $model */
        $model = VetisProductItem::find()->joinWith(['subProduct', 'unit'])
            ->where(['vetis_product_item.guid' => $request['guid']])->one();
        if (!$model) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury();
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\products\Products();
        $_ = new Mercury();
        $attributes = unserialize($model->data);
        if (isset($attributes->producing)) {
            $guids = [];
            foreach ($attributes->producing as $item) {
                $guids[] = $item->location->guid ?? $item->guid;
            }
            $productionName = VetisRussianEnterprise::find()->select(['name', 'uuid', 'guid'])
                ->where(['guid' => $guids])->all();
        }

        return [
            'product_type_lvl1' => VetisHelper::$vetis_product_types[$model->productType],
            'product_type_lvl2' => $model->product->name ?? null,
            'form'              => $model->subProduct->name ?? null,
            'name'              => $model->name,
            'uuid'              => $model->uuid,
            'guid'              => $model->guid,
            'article'           => $model->code,
            'gtin'              => $model->globalID,
            'gost'              => $model->gost,
            'active'            => $model->active,
            'package_type'      => $model->packingType->name ?? null,
            'package_quantity'  => $model->packagingQuantity ?? null,
            'package_volume'    => $model->packagingVolume ?? null,
            'package_unit'      => $model->unit->name ?? null,
            'producer_name'     => $this->getBusinessEntity()->name ?? null,
            'production_name'   => $productionName ?? null,
            'perishable'        => $model->perishable,
            'expiration_date'   => $model->expiration_date,
            'ingredients'       => $model->ingredients,
        ];
    }

    /**
     * @param array  $request
     * @param string $operation
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidArgumentException
     */
    public function createProductItem($request, $operation)
    {
        $uuid = null;
        if ($operation == 'UPDATE') {
            $this->validateRequest($request, ['uuid']);
            $uuid = $request['uuid'];
            $product = VetisProductItem::findOne(['uuid' => $uuid, 'last' => true, 'active' => true]);
            if (!$product) {
                throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
            }
        }
        $this->validateRequest($request, ['name', 'product_type', 'form_guid', 'subtype_guid']);
        $model = new productForm();

        $model->name = $request['name'];
        $model->productType = $request['product_type'];
        $model->product_guid = $request['form_guid'];
        $model->subproduct_guid = $request['subtype_guid'];
        $model->code = $request['article'];
        $model->globalID = $request['gtin'];
        $model->correspondsToGost = (int)$request['has_gost'];
        $model->gost = $request['gost'];
        $model->packagingType_guid = $request['package']['type_uuid'];
        $model->packagingVolume = $request['package']['volume'];
        $model->packagingQuantity = $request['package']['amount'];
        $model->unit_guid = $request['package']['unit_guid'];
        $model->producers = $request['producers'];

        if ($model->validate()) {
            try {
                $result = mercuryApi::getInstance()->modifyProducerStockListOperation($operation, $uuid, $model);
                if (!isset($result)) {
                    throw new \Exception('Error create Product');
                }
                $productItem = $result->application->result->any['modifyProducerStockListResponse']->productItemList->productItem;
                if (isset($request['ingredients']) && !empty($request['ingredients'])) {
                    $this->addIngredients($productItem->guid, $request['ingredients']);
                }
            } catch (\Throwable $e) {
                $this->helper->writeInJournal($e->getMessage(), $this->user->id, $this->user->organization_id, 'error', 'CreateVetisProductItem');
                return ['result' => false];
            }
        } else {
            throw new ValidationException($model->errors);
        }

        return ['result' => true];
    }

    /**
     * @param $guid
     * @param $ingredients
     * @throws ValidationException
     */
    private function addIngredients($guid, $ingredients)
    {
        foreach ($ingredients as $ingredient) {
            $model = VetisIngredients::findOne(['guid' => $guid, 'product_name' => $ingredient['name']]);
            if (!$model) {
                $model = new VetisIngredients();
                $model->guid = $guid;
                $model->product_name = $ingredient['name'];
            }
            $model->amount = $ingredient['amount'];
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteIngredient($request)
    {
        $this->validateRequest($request, ['id']);
        $model = VetisIngredients::findOne($request['id']);
        if (!$model) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }

        return ['result' => (bool)$model->delete()];
    }

    /**
     * @param $request
     * @return array
     * @throws ValidationException
     * @throws BadRequestHttpException
     */
    public function createTransport($request)
    {
        $orgId = $request['org_id'] ?? $this->user->organization_id;
        $this->validateOrgId($orgId);
        $model = new VetisTransport();
        $model->org_id = $orgId;
        $model->vehicle_number = $request['vehicle_number'] ?? null;
        $model->trailer_number = $request['trailer_number'] ?? null;
        $model->container_number = $request['container_number'] ?? null;
        $model->transport_storage_type = $request['transport_storage_type'] ?? null;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }

        return ['result' => true];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteTransport($request)
    {
        $this->validateRequest($request, ['id']);
        $orgId = $request['org_id'] ?? $this->user->organization_id;
        $this->validateOrgId($orgId);
        $model = VetisTransport::findOne($request['id']);
        if (!$model) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }

        return ['result' => (bool)$model->delete()];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidArgumentException
     */
    public function getStockEntryList($request)
    {
        $reqPag = $request['pagination'] ?? [];
        $sort = $request['sort'] ?? null;
        $reqSearch = $request['search'] ?? null;
        $page = $this->helper->isSetDef($reqPag['page'] ?? null, 1);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'] ?? null, 12);
        $enterpriseGuid = $this->helper->getEnterpriseGuid($this->user->organization_id);
        $search = new MercStockEntrySearch();
        $dataProvider = $search->search($reqSearch, $enterpriseGuid);

        $arSortFields = [
            'product_name',
            'create_date',
            'expiry_date',
        ];

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        $result = [];
        if ($sort && in_array(ltrim($sort, '-'), $arSortFields)) {
            $sortDirection = SORT_ASC;
            if (strpos($sort, '-') !== false) {
                $sortDirection = SORT_DESC;
            }
            $dataProvider->query->orderBy([ltrim($sort, '-') => $sortDirection]);
        } else {
            $dataProvider->query->orderBy('id DESC');
        }

        /**@var MercStockEntry $model */
        foreach ($dataProvider->models as $model) {
            $result[] = [
                'number'          => $model->entryNumber,
                'id'              => $model->id,
                'name'            => $model->product_name,
                'uuid'            => $model->uuid,
                'guid'            => $model->guid,
                'producer'        => $model->producer_name,
                'country'         => $model->producer_country,
                'balance'         => $model->amount,
                'unit'            => $model->unit,
                'created_at'      => $model->create_date,
                'production_date' => $model->production_date,
                'expiry_date'     => $model->expiry_date,
            ];
        }

        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getProductionJournalProducerFilter()
    {
        $query = MercStockEntry::find()->select(['producer_name', 'producer_guid'])->distinct()
            ->where(['owner_guid' => $this->helper->getEnterpriseGuid($this->user->organization_id)])
            ->andWhere(['not', ['producer_guid' => null]])->orderBy('producer_name');

        return $query->all();
    }

    /**
     * @return array
     */
    public function getProductionJournalSort()
    {
        return [
            'product_name'  => \Yii::t('api_web', 'production_journal.product_name'),
            '-product_name' => \Yii::t('api_web', 'production_journal.-product_name'),
            'create_date'   => \Yii::t('api_web', 'production_journal.create_date'),
            '-create_date'  => \Yii::t('api_web', 'production_journal.-create_date'),
            'expiry_date'   => \Yii::t('api_web', 'production_journal.expiry_date'),
            '-expiry_date'  => \Yii::t('api_web', 'production_journal.-expiry_date'),
        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function getProductionJournalShortInfo($request)
    {
        $this->validateRequest($request, ['uuid']);
        $model = MercStockEntry::findOne(['uuid' => $request['uuid']]);
        if (!$model) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury();
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\products\Products();
        $attributes = unserialize($model->raw_data);
        if (isset($attributes->batch->subProduct->guid)) {
            $productionName = VetisSubproductByProduct::find()->select(['name'])
                ->where(['guid' => $attributes->batch->subProduct->guid])->one();
        }

        return [
            'product_form' => $productionName->name ?? null,
            'batch_id'     => $model->batch_id,
            'packing'      => $attributes->batch->packageList->package->packingType->name ?? null,

        ];
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function getProductionJournalFullInfo($request)
    {
        $this->validateRequest($request, ['uuid']);
        $model = MercStockEntry::findOne(['uuid' => $request['uuid']]);
        if (!$model) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury();
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\products\Products();
        $attributes = unserialize($model->raw_data);
        if (isset($attributes->batch->subProduct->guid)) {
            $productionForm = VetisSubproductByProduct::find()->select(['name'])
                ->where(['guid' => $attributes->batch->subProduct->guid])->one();
        }
        if (isset($attributes->batch->product->guid)) {
            $production2lvl = VetisProductByType::find()->select(['name'])
                ->where(['guid' => $attributes->batch->product->guid])->one();
        }
        $arResearch = [];
        if (isset($attributes->vetEventList->laboratoryResearch)) {
            foreach ($attributes->vetEventList->laboratoryResearch as $research) {
                if (isset($research->referencedDocument)) {
                    $arResearch[] = [
                        'issue_id'      => $research->referencedDocument->issueNumber ?? null,
                        'expertise_id'  => $research->expertiseID ?? null,
                        'laboratory'    => $research->operator->name ?? null,
                        'method'        => $research->method->name ?? null,
                        'indicator'     => $research->indicator->name ?? null,
                        'result_date'   => $research->actualDateTime ?? null,
                        'research_date' => $research->referencedDocument->issueDate ?? null,
                        'result'        => $research->result ?? null,
                        'conclusion'    => $research->conclusion ?? null,
                    ];
                }
            }
        }

        return [
            'name'                 => $model->product_name,
            'product_type'         => VetisHelper::$vetis_product_types[$model->product_type],
            'production_type_lvl2' => $production2lvl['name'] ?? null,
            'product_form'         => $productionForm->name ?? null,
            'number'               => $model->entryNumber,
            'create_date'          => $model->create_date,
            'batch_id'             => $model->batch_id,
            'balance'              => $model->amount,
            'production_date'      => $model->production_date,
            'expiry_date'          => $model->expiry_date,
            'packing'              => $attributes->batch->packageList->package->packingType->name ?? null,
            'enterprise'           => $attributes->batch->origin->producer->enterprise->name ?? null,
            'country'              => $model->producer_country,
            'research'             => $arResearch
        ];
    }

    /**
     * Метод полного списания все продукции по id
     *
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function resolveDiscrepancy($request)
    {
        $this->validateRequest($request, ['id']);
        if (!is_array($request['id'])) {
            throw new ValidationException([], 'common.param_must_be_an_array');
        }
        try {
            $datas = [];
            foreach ($request['id'] as $id) {
                $model = MercStockEntry::findOne(['id' => $id]);
                if ($model) {
                    $datas[] = $model->raw_data;
                }
            }

            $form = new createStoreEntryForm();
            $result = mercuryApi::getInstance()->resolveDiscrepancyOperation($form, createStoreEntryForm::INV_PRODUCT_ALL, $datas);
            if (!isset($result)) {
                throw new \Exception(\Yii::t('api_web', 'vetis.error_resolve_discrepancy'));
            }
        } catch (\Throwable $t) {
            $this->helper->writeInJournal($t->getMessage(), $this->user->id, $this->user->organization_id, 'resolveDiscrepancyOperation');
            return ['result' => false];
        }

        return ['result' => true];
    }

    /**
     * Метод фактического списания продукции по id, устанавливает кол-во == amount в Меркурии
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\InvalidArgumentException
     */
    public function resolveDiscrepancyPartial($request)
    {
        $this->validateRequest($request, ['id', 'amount', 'reason']);
        $model = new rejectedForm();
        $model->volume = $request['amount'];
        $model->reason = $request['reason'];
        $model->description = $request['description'];
        $data = MercStockEntry::findOne(['id' => $request['id']]);
        if (!$data) {
            throw new BadRequestHttpException(\Yii::t('api_web', 'model_not_found'));
        }

        if ($model->validate()) {
            try {
                $form = new createStoreEntryForm();
                $form->attributes = $model->attributes;
                $result = mercuryApi::getInstance()->resolveDiscrepancyOperation($form, createStoreEntryForm::INV_PRODUCT, [$data->raw_data]);
                if (!isset($result)) {
                    throw new \Exception('vetis.error_resolve_discrepancy_partial');
                }
            } catch (\Throwable $t) {
                $this->helper->writeInJournal($t->getMessage(), $this->user->id, $this->user->organization_id, 'error', 'resolveDiscrepancyPartialOperation');
                return ['result' => false];
            }
        } else {
            throw new ValidationException($model->errors);
        }

        return ['result' => true];
    }

    /**
     * @param array $request
     * @return VetisPurpose[]
     */
    public function getPurposeList($request)
    {
        $models = VetisPurpose::find()->where(['active' => 1, 'last' => 1]);
        if (isset($request['name']) && !empty($request['name'])) {
            $models->andWhere(['like', 'name', $request['name']]);
        }
        $arModels = [];
        foreach ($models->all() as $model) {
            $arModels[] = [
                'name' => $model->name,
                'guid' => $model->guid,
                'uuid' => $model->uuid,
            ];
        }

        return $arModels;
    }

    /**
     * @param $request
     * @return array
     * @throws BadRequestHttpException|\Exception
     * @throws ValidationException
     */
    public function createTransportVsd($request)
    {
        $this->validateRequest($request, ['hc_guid', 'recipient', 'products', 'purpose_guid']);
        $params = [
            'type'               => 1,
            'type_name'          => $request['type_name'] ?? 'Автомобильный',
            'car_number'         => $request['car_number'] ?? '',
            'trailer_number'     => $request['trailer_number'] ?? '',
            'container_number'   => $request['container_number'] ?? '',
            'storage_type'       => array_key_exists($request['storage_type'], VetisHelper::$transport_storage_types)
                ? $request['storage_type'] : null,
            'mode'               => 1,
            'org_id'             => $this->user->organization_id,
            'recipient'          => $request['recipient'], //guid
            'hc_guid'            => $request['hc_guid'], //guid
            'inn'                => $request['recipient_inn'] ?? '',
            'products'           => $request['products'], // ['select_amount','product_name', 'id']
            'purpose'            => $request['purpose_guid'], // purpose guid
            'cargoExpertized'    => $request['cargoExpertized'],
            'locationProsperity' => $request['locationProsperity'],
            'isTTN'              => $request['isTTN'] ?? false,
            'seriesTTN'          => $request['seriesTTN'] ?? null,
            'numberTTN'          => $request['numberTTN'] ?? null,
            'dateTTN'            => $request['dateTTN'] ?? null,
            'typeTTN'            => $request['typeTTN'] ?? null,

        ];
        $mercRequest = new CreatePrepareOutgoingConsignmentRequest();
        $mercRequest->params = $params;
        $mercRequest->checkShipmentRegionalizationOperation();
        $mercRequest->conditions = $request['conditions'] ?? null;
        if (isset($mercRequest->conditionsDescription) && is_null($mercRequest->conditions)) {
            return ['result' => 'need_confirm_regionalization', 'conditions' => $mercRequest->conditionsDescription];
        }

        try {
            $result = mercuryApi::getInstance()->prepareOutgoingConsignmentOperation($mercRequest);
            if (!isset($result)) {
                throw new BadRequestHttpException(\Yii::t('api_web', 'vetis.error_create_transport_vsd'));
            }
        } catch (\Throwable $t) {
            $this->helper->writeInJournal($t->getMessage() . PHP_EOL . $t->getTraceAsString(), $this->user->id, $this->user->organization_id, 'error', 'createTransportVsd');
            return ['result' => false];
        }

        return ['result' => true];
    }

    /**
     * @param $request
     * @return array
     * @throws ValidationException
     */
    public function conversion($request, $flagForUseIngredients = false)
    {
        $productionDate = new productionDate();
        $productionDate->first_date = $request['production_first_date'];
        $productionDate->second_date = $request['production_second_date'];
        $expiryDate = new expiryDate();
        $expiryDate->first_date = $request['expiry_first_date'];
        $expiryDate->second_date = $request['expiry_second_date'];
        $expiryDate->production_date = !empty($productionDate->second_date) ? $productionDate->second_date : $productionDate->first_date;

        $params = [
            'products'         => $request['products'], // ['id', 'select_amount']
            'product_guid'     => $request['product_guid'],
            'dateOfProduction' => $productionDate,
            'expiryDate'       => $expiryDate,
            'volume'           => $request['volume'],
            'unit'             => $request['unit'],
            'batchID'          => $request['batchID'],
            'country'          => "1",
            'producer'         => "1",
            'vsd_issueNumber'  => "1",
            'vsd_issueDate'    => $request['vsd_issueDate'],
        ];
        $request = new CreateRegisterProductionRequest();
        $request->params = $params;
        $request->flagForUseIngredients = $flagForUseIngredients;

        try {
            $result = mercuryApi::getInstance()->registerProductionOperation($request);
            if (!isset($result)) {
                throw new BadRequestHttpException(\Yii::t('api_web', 'vetis.register_production_operation'));
            }
        } catch (\Throwable $t) {
            $this->helper->writeInJournal($t->getMessage() . PHP_EOL . $t->getTraceAsString(), $this->user->id, $this->user->organization_id, 'error', 'registerProductionOperation');
            return ['result' => false];
        }

        return ['result' => true];
    }
}
