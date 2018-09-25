<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use api_web\components\WebApi;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentDone;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class VetisWaybill extends WebApi
{

    private $helper;

    public function __construct()
    {
        $this->helper = new VetisHelper();
        parent::__construct();
    }

    /**
     * Список сертифитаков сгруппированный по номеру заказа
     * @param $request
     * @return array
     */
    public function getGroupsList($request)
    {
        $reqPag = $request['pagination'] ?? [];
        $reqSearch = $request['search'] ?? [];
        $page = $this->helper->isSetDef($reqPag['page'] ?? null, 1);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'] ?? null, 12);

        $acquirer_id = null;
        if(isset($request['search']['acquirer_id'])) {
            $acquirer_id = $request['search']['acquirer_id'];
        }

        $reqSearch['acquirer_id'] = $this->helper->isSetDef($acquirer_id, $this->user->organization->id);

        /**
         * Без этого GROUP_CONCAT возвращает только 1024 символа, и режет данные
         */
        \Yii::$app->db->createCommand('SET SESSION group_concat_max_len = 1000000')->execute();

        $search = new VetisWaybillSearch();
        $params = $this->helper->set($search, $reqSearch, ['acquirer_id', 'type', 'status', 'sender_guid', 'product_name', 'date']);
        $dataProvider = $search->search($params);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);
        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[$model['group_name']]['count'] = $model['count'];
            if ($model['group_name'] != 'order_not_installed') {
                $result[$model['group_name']]['date'] = $model['created_at'];
                $result[$model['group_name']]['total_price'] = $model['total_price'];
            }
            $result[$model['group_name']]['uuids'] = explode(',', $model['uuids']);
            $result[$model['group_name']]['status'] = $this->helper->getStatusForGroup($model['statuses']);
        }
        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];
        return $return;
    }

    /**
     * Получение ВСД по uuids
     * @throws BadRequestHttpException
     * @param array $uuids
     * */
    public function getList($request)
    {
        if (!isset($request['uuids']) || empty($request['uuids'])) {
            throw new BadRequestHttpException('uuids не заполнен или пуст');
        }

        $models = MercVsd::findAll(['uuid' => $request['uuids']]);
        $result = [];
        foreach ($models as $model) {
            $result[] = [
                'uuid' => $model->uuid,
                'product_name' => $model->product_name,
                'sender_name' => $model->sender_name,
                'status' => $model->status,
                'status_text' => MercVsd::$statuses[$model->status],
                'status_date' => $model->last_update_date,
                'amount' => $model->amount,
                'unit' => $model->unit,
                'production_date' => $model->production_date,
                'date_doc' => $model->date_doc,
            ];
        }

        return ['result' => $result];
    }

    /**
     * Формирование всех фильтров
     * @return array
     * */
    public function getFilters()
    {
        return [
            'result' => [
                'vsd' => $this->getFilterVsd(),
                'statuses' => $this->getFilterStatus(),
                'sender' => $this->getSenderOrProductFilter(['search' => 'sender_name'], 'sender_name'),
                'product' => $this->getSenderOrProductFilter(['search' => 'product_name'], 'product_name'),
            ]
        ];
    }

    /**
     * Формирование массива для фильтра ВСД
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
                '' => 'Все ВСД',
            ]
        ];
    }

    /**
     * Формирование массива для фильтра статусы
     * @return array
     * */
    public function getFilterStatus()
    {
        return ['result' => array_merge(MercVsd::$statuses, ['' => 'Все'])];
    }

    /**
     * Формирование массива для фильтра "По продукции" или по "Фирма отправитель" так же выполняет "живой" поиск лайком
     * @return array
     * */
    public function getSenderOrProductFilter($request, $filterName)
    {
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        $query = MercVsd::find();
        if (isset($request['search'][$filterName])) {
            $query->andWhere(['like', $filterName, $request['search'][$filterName]]);
        }

        if ($filterName == 'product_name') {
            $arResult = $query->andWhere(['or', ['sender_guid' => $enterpriseGuid], ['recipient_guid' => $enterpriseGuid]])->groupBy('product_name')->all();
            $result = ArrayHelper::map($arResult, 'product_name', 'product_name');
        } else {
            $arResult = $query->andWhere(['recipient_guid' => $enterpriseGuid])->groupBy('sender_name')->all();
            $result = ArrayHelper::map($arResult, 'sender_guid', 'sender_name');
        }

        return ['result' => $result];
    }

    /**
     * Краткая информация о ВСД
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
     * @param $request
     * @throws BadRequestHttpException
     * @return array
     */
    public function repayVsd($request)
    {
        if (!isset($request['uuids']) || !is_array($request['uuids'])) {
            throw new BadRequestHttpException('Uuids is required and must be array');
        }
        $result = [];
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        $records = MercVsd::find()->select(['uuid', 'recipient_guid'])->where(['recipient_guid' => $enterpriseGuid])
            ->andWhere(['uuid' => $request['uuids']])->indexBy('uuid')->all();
        try {
            $api = mercuryApi::getInstance();
            foreach ($request['uuids'] as $uuid) {
                if (array_key_exists($uuid, $records)) {
                    $result[$uuid] = $api->getVetDocumentDone($uuid);
                } else {
                    $result[$uuid] = 'ВСД не принадлежит данной организации';
                }
            }
        } catch (\Throwable $t) {
            $result['error'] = $t->getMessage();
            $result['trace'] = $t->getTraceAsString();
            $result['code'] = $t->getCode();
        }

        return ['result' => $result];
    }

    /**
     * Частичное погашение ВСД
     * @param $request
     * @throws BadRequestHttpException
     * @return array
     */
    public function partialAcceptance($request)
    {
        $uuid = $request['uuid'];
        if (!isset($uuid) || !isset($request['reason'])) {
            throw new BadRequestHttpException('Uuid and reason is required and must be array');
        }
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        $record = MercVsd::find()->select(['uuid', 'recipient_guid'])->where(['recipient_guid' => $enterpriseGuid])
            ->andWhere(['uuid' => $request['uuid']])->indexBy('uuid')->all();
        if ($record) {
            throw new BadRequestHttpException('Uuid not for this organization');
        }
        $params = [
            'decision' => VetDocumentDone::PARTIALLY,
            'volume' => $request['amount'],
            'reason' => $request['reason'],
            'description' => $request['description'],
        ];

        try {
            $api = mercuryApi::getInstance();
            $result[$uuid] = $api->getVetDocumentDone($uuid, $params);
        } catch (\Throwable $t) {
            $result['error'] = $t->getMessage();
            $result['trace'] = $t->getTraceAsString();
            $result['code'] = $t->getCode();
        }

        return ['result' => $result];
    }

    /**
     * Возврат ВСД
     * @param $request
     * @throws BadRequestHttpException
     * @return array
     */
    public function returnVsd($request)
    {
        $uuid = $request['uuid'];
        if (!isset($uuid) || !isset($request['reason'])) {
            throw new BadRequestHttpException('Uuid and reason is required and must be array');
        }
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        $record = MercVsd::find()->select(['uuid', 'recipient_guid'])->where(['recipient_guid' => $enterpriseGuid])
            ->andWhere(['uuid' => $request['uuid']])->indexBy('uuid')->all();
        if ($record) {
            throw new BadRequestHttpException('Uuid not for this organization');
        }
        $params = [
            'decision' => VetDocumentDone::RETURN_ALL,
            'reason' => $request['reason'],
            'description' => $request['description'],
        ];

        try {
            $api = mercuryApi::getInstance();
            $result[$uuid] = $api->getVetDocumentDone($uuid, $params);
        } catch (\Throwable $t) {
            $result['error'] = $t->getMessage();
            $result['trace'] = $t->getTraceAsString();
            $result['code'] = $t->getCode();
        }

        return ['result' => $result];
    }
}