<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use api_web\classes\UserWebApi;
use api_web\components\WebApi;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentDone;
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
     * @throws \yii\web\BadRequestHttpException
     * @return array
     */
    public function getGroupsList($request)
    {
        $reqPag = $request['pagination'] ?? [];
        $reqSearch = $request['search'] ?? [];
        $page = $this->helper->isSetDef($reqPag['page'] ?? null, 0);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'] ?? null, 12);

        //Поиск ВСД
        $search = new VetisWaybillSearch();
        $params = $this->helper->set($search, $reqSearch, ['acquirer_id', 'type', 'status', 'sender_guid', 'product_name', 'date']);
        $arResult = $search->search($params, $page, $pageSize);

        foreach ($arResult['groups'] as $group_id => &$v) {
            $info = $this->helper->getGroupInfo((int)$group_id, array_keys($arResult['uuids']));
            $v = $info;
        }

        //Строим результат
        $result = [
            'items'  => $this->getList($arResult['uuids']),
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
        return $return;
    }

    /**
     * Получение ВСД по uuids
     *
     * @param array $uuids
     * @return array
     * */
    public function getList($uuids): array
    {
        $result = $uuids;
        $models = MercVsd::findAll(['uuid' => array_keys($uuids)]);
        foreach ($models as $model) {
            $result[$model->uuid] = [
                'uuid'            => $model->uuid,
                'document_id'     => $uuids[$model->uuid],
                'product_name'    => $model->product_name,
                'sender_name'     => $model->sender_name,
                'status'          => $model->status,
                'status_text'     => MercVsd::$statuses[$model->status],
                'status_date'     => $model->last_update_date,
                'amount'          => $model->amount,
                'unit'            => $model->unit,
                'production_date' => $model->production_date,
                'date_doc'        => $model->date_doc,
            ];
        }

        return array_values($result);
    }

    /**
     * Формирование всех фильтров
     *
     * @return array
     * */
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
        if (isset($request['acquirer_id']) && !empty($request['acquirer_id'])) {
            $enterpriseGuids = mercDicconst::getSetting('enterprise_guid', $request['acquirer_id']);
        } else {
            $enterpriseGuids = $this->helper->getEnterpriseGuids();
        }
        $query = MercVsd::find();
        if (isset($request['search'][$filterName]) && !empty($request['search'][$filterName])) {
            $query->andWhere(['like', $filterName, $request['search'][$filterName]]);
        }

        if ($filterName == 'product_name') {
            $arResult = $query->andWhere(['or',
                ['sender_guid' => $enterpriseGuids],
                ['recipient_guid' => $enterpriseGuids]])
                ->groupBy('product_name')->all();
            $result = ArrayHelper::map($arResult, 'product_name', 'product_name');
        } else {
            $arResult = $query->andWhere(['recipient_guid' => $enterpriseGuids])->groupBy('sender_name')->all();
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
     * @throws BadRequestHttpException
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
        if (!isset($request['uuids']) || !is_array($request['uuids'])) {
            throw new BadRequestHttpException('Uuids is required and must be array');
        }
        $result = [];
        $records = $this->helper->getAvailableVsd($request['uuids']);
        try {
            $api = mercuryApi::getInstance();
            $arVsd = [];
            foreach ($request['uuids'] as $uuid) {
                if (array_key_exists($uuid, $records)) {
                    $api->getVetDocumentDone($uuid);
                } else {
                    throw new BadRequestHttpException('ВСД не принадлежит данной организации: ' . $uuid);
                }
                $arVsd[$uuid] = null;
            }
            $result = $this->getList($arVsd);
        } catch (\Throwable $t) {
            if ($t->getCode() == 600) {
                $result['error'] = 'Заявка отклонена';
            } else {
                $result['error'] = $t->getMessage();
                $result['trace'] = $t->getTraceAsString();
                $result['code'] = $t->getCode();
            }
        }

        return ['result' => $result];
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
        ];

        try {
            $api = mercuryApi::getInstance();
            $api->getVetDocumentDone($uuid, $params);
            $result = $this->getList([$uuid => null]);
        } catch (\Throwable $t) {
            if ($t->getCode() == 600) {
                $result['error'] = 'Заявка отклонена';
            } else {
                $result['error'] = $t->getMessage();
                $result['trace'] = $t->getTraceAsString();
                $result['code'] = $t->getCode();
            }
        }

        return ['result' => $result];
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
        $record = $this->helper->getAvailableVsd($request['uuid']);
        if (!$record) {
            throw new BadRequestHttpException('Uuid not for this organization');
        }
        $params = [
            'decision'    => VetDocumentDone::RETURN_ALL,
            'reason'      => $request['reason'],
            'description' => $request['description'],
        ];

        try {
            $api = mercuryApi::getInstance();
            $api->getVetDocumentDone($uuid, $params);
            $result = $this->getList([$uuid => null]);
        } catch (\Throwable $t) {
            if ($t->getCode() == 600) {
                $result['error'] = 'Заявка отклонена';
            } else {
                $result['error'] = $t->getMessage();
                $result['trace'] = $t->getTraceAsString();
                $result['code'] = $t->getCode();
            }
        }

        return ['result' => $result];
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function getNotConfirmedVsd($request)
    {
        $enterpraiseGuid = null;
        $orgId = $request['org_id'] ?? null;
        if ($orgId){
            $enterpraiseGuid = mercDicconst::getSetting('enterprise_guid', $orgId);
        }

        return [
            'result' => $this->helper->getNotConfirmedVsd($enterpraiseGuid),
        ];
    }

    /**
     * Получение ВСД в PDF
     *
     * @param $request
     * @throws BadRequestHttpException
     * @return string
     */
    public function getVsdPdf($request)
    {
        if (!isset($request['uuid'])) {
            throw new BadRequestHttpException('Uuid is required');
        }
        
        $vsdHttp = $this->helper->generateVsdHttp();
        $check = $vsdHttp->checkAuthData();
        
        if (!$check['result']) {
            throw new BadRequestHttpException('Vetis authorization failed');
        }
        
        $data = $vsdHttp->getPdfData($request['uuid']);
        $base64 = (isset($post['base64_encode']) && $post['base64_encode'] == 1 ? true : false);
        return ($base64 ? base64_encode($data) : $data);
    }
}