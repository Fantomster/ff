<?php

namespace api_web\modules\integration\modules\vetis\models;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\getVetDocumentByUUID;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class VetisWaybill extends VetisHelper
{
    /**
     * Список сертифитаков
     * @param $request
     * @return array
     */
    public function getList($request)
    {
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
        $enterpriseGuid = mercDicconst::getSetting('enterprise_guid');
        $query = MercVsd::find()->where(['recipient_guid' => $enterpriseGuid]);
        if (isset($request['search'][$filterName])) {
            $query->andWhere(['like', $filterName, $request['search'][$filterName]]);
        }

        if ($filterName == 'product_name') {
            $arResult = $query->orWhere(['sender_guid' => $enterpriseGuid])->groupBy('product_name')->all();
            $result = ArrayHelper::map($arResult, 'product_name', 'product_name');
        } else {
            $arResult = $query->groupBy('sender_name')->all();
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
}