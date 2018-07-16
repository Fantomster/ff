<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\ikar;

use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use Yii;

class ikarApi extends baseApi
{
    public function init()
    {
        $load = new Ikar();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getCountryByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $country = $cache->get('Country_'.$GUID);
        
        if(!($country === false))
            return $country;

        $client = $this->getSoapClient('ikar');
        $request = new getCountryByGuidRequest();
        $request->guid = $GUID;

        try {
            $result = $client->GetCountryByGuid($request);
        } catch (\SoapFault $e)
        {
            var_dump($e->detail); die();
        }

        if($result != null)
            $cache->add('Country_'.$GUID, $result, 60*60*24*7);

        return $result;
    }
}