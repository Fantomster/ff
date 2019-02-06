<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\mercLog;
use api\common\models\merc\MercStockEntry;
use api\common\models\merc\MercVsd;
use api_web\components\Registry;
use api_web\components\ValidateRequest;
use api_web\components\WebApi;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use api_web\modules\integration\modules\vetis\api\mercury\mercuryApi;
use api_web\modules\integration\modules\vetis\api\mercury\VetDocumentDone;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
use common\models\licenses\LicenseOrganization;
use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisPackingType;
use common\models\vetis\VetisProductByType;
use common\models\vetis\VetisProductItem;
use common\models\vetis\VetisRussianEnterprise;
use common\models\vetis\VetisSubproductByProduct;
use common\models\vetis\VetisUnit;
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
                $inc => $types[$inc],
                $out => $types[$out],
                ''   => 'Все ВСД',
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
        return ['result' => array_merge(MercVsd::$statuses, ['' => 'Все'])];
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
            $api = mercuryApi::getInstance();
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
            $api = mercuryApi::getInstance();
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
            $api = mercuryApi::getInstance();
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
                'form'    => Registry::$vetis_product_types[$model->productType],
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
        $models = VetisProductByType::find()->select(['name', 'guid'])->where(['productType' => $request['type_id']])->all();

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

}
