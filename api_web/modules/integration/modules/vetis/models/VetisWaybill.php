<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\MercVsd;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class VetisWaybill
{

    public function __construct()
    {
        $this->helper = new VetisHelper();
    }

    /**
     * Список сертифитаков сгруппированный по номеру заказа
     * @param $request
     * @return array
     */
    public function getGroupsList($request)
    {
        $reqPag = $request['pagination'];
        $reqSearch = $request['search'];
        $page = $this->helper->isSetDef($reqPag['page'], 1);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'], 12);

        $search = new VetisWaybillSearch();
        if (isset($reqSearch)) {
            $params = $this->helper->set($search, $reqSearch, ['acquirer_id', 'type', 'status', 'sender_guid', 'product_name', 'date']);
            $dataProvider = $search->search($params);
        } else {
            $dataProvider = new ActiveDataProvider([
                'query' => $this->helper->getOrdersQueryVetis(),
            ]);
        }

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
                'uuid'            => $model->uuid,
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
                'vsd'      => $this->getFilterVsd(),
                'statuses' => $this->getFilterStatus(),
                'sender'   => $this->getSenderOrProductFilter(['search' => 'sender_name'], 'sender_name'),
                'product'  => $this->getSenderOrProductFilter(['search' => 'product_name'], 'product_name'),
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
                ''   => 'Все ВСД',
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
        $query = $this->helper->getQueryByUuid();
        if (isset($request['search'][$filterName])) {
            $query->andWhere(['like', $filterName, $request['search'][$filterName]]);
        }
        $arResult = $query->groupBy('sender_guid')->all();
        if ($filterName == 'product_name') {
            $result = ArrayHelper::map($arResult, 'product_name', 'product_name');
        } else {
            $result = ArrayHelper::map($arResult, 'sender_guid', 'sender_name');
        }

        return ['result' => $result];
    }
}