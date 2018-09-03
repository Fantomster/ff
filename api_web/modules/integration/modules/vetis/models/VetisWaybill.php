<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\MercVsd;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use yii\helpers\ArrayHelper;

class VetisWaybill
{
    
    public function __construct()
    {
        $this->helper = new VetisHelper();
    }
    
    /**
     * Список сертифитаков
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $reqPag = $request['pagination'];
        $reqSearch = $request['search'];
        $page = $this->helper->isSetDef($reqPag['page'], 1);
        $pageSize = $this->helper->isSetDef($reqPag['page_size'], 12);
    
        $search = new VetisWaybillSearch();
        if (isset($reqSearch)) {
            $params = $this->helper->set($search, $reqSearch, ['acquirer_id', 'type', 'status', 'sender_guid', 'product_name', 'date']);
            $request = $search->search($params);
            
        }
        return ['result' => $request];
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
                $inc  => $types[$inc],
                $out  => $types[$out],
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