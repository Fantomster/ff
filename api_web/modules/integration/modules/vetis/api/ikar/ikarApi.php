<?php

namespace api_web\modules\integration\modules\vetis\api\ikar;

use common\models\vetis\VetisCountry;
use api_web\modules\integration\modules\vetis\api\baseApi;

/**
 * Class ikarApi
 *
 * @package api_web\modules\integration\modules\vetis\api\ikar
 */
class ikarApi extends baseApi
{

    /**
     *
     */
    public function init()
    {
        $this->system = 'ikar';
        $this->wsdlClassName = Ikar::class;
        parent::init();
    }

    //Получние страны по GUID

    /**
     * @param $GUID
     * @return mixed|null
     */
    public function getCountryByGuid($GUID)
    {
       VetisCountry::getUpdateData($this->org_id);
       $country = VetisCountry::findOne(['guid' => $GUID]);
        
        if (!empty($country)) {
            return unserialize($country->data);
        }

        return null;
    }

    /**
     * Получение списка стран
     * @return \common\models\vetis\VetisUnit[]|null
     */
    public function getAllCountryList()
    {
        VetisCountry::getUpdateData($this->org_id);
        $countries = VetisCountry::findAll(['active' => 1, 'last' => 1]);

        if (!empty($countries)) {
            $list = [];
            foreach ($countries as $item)
            {
                $list = unserialize($item->data);
            }

            return $list;
        }

        return [];
    }

    /**
     * Составление запроса на загрузку справочника стран
     * @param $options
     * @return getCountryChangesListRequest
     * @throws \Exception
     */
    public function getCountryChangesList($options)
    {
        require_once (__DIR__ ."/Ikar.php");
        $request = new getCountryChangesListRequest();
        if (array_key_exists('listOptions', $options)) {
            $request->listOptions = $options['listOptions'];
        }

        if (!array_key_exists('listOptions', $options)) {
            throw new \Exception('startDate field is not specified');
        }

        $request->updateDateInterval = new DateInterval();
        $request->updateDateInterval->beginDate = date('Y-m-d', strtotime($options['startDate'])) . 'T' . date('H:i:s', strtotime($options['startDate']));
        $request->updateDateInterval->endDate = date('Y-m-d') . 'T' . date('H:i:s') . '+03:00';

        return $request;
    }

}
