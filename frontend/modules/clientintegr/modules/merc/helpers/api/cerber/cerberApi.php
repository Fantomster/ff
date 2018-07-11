<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\cerber;

use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use Yii;

class cerberApi extends baseApi
{
    public function init()
    {
        $load = new Cerber();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getActivityLocationList ()
    {
        $client = $this->getSoapClient('cerber');
        $request = new getActivityLocationListRequest();
        $request->businessEntity = new BusinessEntity();
        $request->businessEntity->guid = $this->issuerID;
        return $client->GetActivityLocationList($request);
    }

    public function getEnterpriseByUuid ($UUID)
    {
        $cache = Yii::$app->cache;
        $enterprise = $cache->get('Enterprise_'.$UUID);
        if($UUID == null){
            return null;}
        if(!($enterprise === false))
            return $enterprise;

        $client = $this->getSoapClient('cerber');

        $request = new getEnterpriseByUuidRequest();
        $request->uuid = $UUID;

        $result = $client->GetEnterpriseByUuid($request);

        if(isset($result))
            $cache->add('Enterprise_'.$UUID, $result, 60*60*24);

        return $result;
    }

    public function getBusinessEntityByUuid ($UUID)
    {
        $cache = Yii::$app->cache;
        $business = $cache->get('Business_'.$UUID);

        if(!($business === false))
            return $business;

        $client = $this->getSoapClient('cerber');
        $request = new getBusinessEntityByUuidRequest();
        $request->uuid = $UUID;

        $result = $client->GetBusinessEntityByUuid($request);

        if(isset($result))
            $cache->add('Business_'.$UUID, $result, 60*60*24);
        return $result;
    }
}