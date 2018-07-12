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

        $result = $client->GetCountryByGuid($request);

        if($result != null)
            $cache->add('Country_'.$GUID, $result, 60*60*24*7);

        return $result;
    }

    public function getAllCountryList ()
    {
        $client = $this->getSoapClient('ikar');
        $request = new getAllCountryListRequest();
        $result = $client->GetAllCountryList($request);
        return $result;
    }
}