<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\product;

use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;
use Yii;

class productApi extends baseApi
{
    public function init()
    {
        $load = new Products();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getProductByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $product = $cache->get('Product_'.$GUID);

        if(!($product === false))
            return $this->parseResponse($product, true);

        $client = $this->getSoapClient('product');
        $request = new getProductByGuidRequest();
        $request->guid = $GUID;
        $result = $client->GetProductByGuid($request);

        if($result != null)
            $cache->add('Product_'.$GUID, $result, 60*60*24);
        return $result;
    }

    public function getSubProductByGuid ($GUID)
    {
        $cache = Yii::$app->cache;
        $subProduct = $cache->get('SubProduct_'.$GUID);

        if(!($subProduct === false))
            return $subProduct;

        $client = $this->getSoapClient('product');
        $request = new getSubProductByGuidRequest();
        $request->guid = $GUID;
        $result = $client->GetSubProductByGuid($request);

        if($result != null)
            $cache->add('subProduct_'.$GUID, $result, 60*60*24);
        return $result;
    }
}