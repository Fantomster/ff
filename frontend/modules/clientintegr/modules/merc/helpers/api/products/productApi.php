<?php
namespace frontend\modules\clientintegr\modules\merc\helpers\api\products;

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
        $product = \common\models\vetis\VetisProductByType::findOne(['guid' => $GUID]);

        if(!empty($product)) {
            return $product;
        }

        $client = $this->getSoapClient('product');
        $request = new getProductByGuidRequest();
        $request->guid = $GUID;
        $result = $client->GetProductByGuid($request);

        return $result;
    }

    public function getSubProductByGuid ($GUID)
    {
        $subProduct = \common\models\vetis\VetisSubproductByProduct::findOne(['guid' => $GUID]);

        if(!empty($subProduct)) {
            return $subProduct;
        }

        $client = $this->getSoapClient('product');
        $request = new getSubProductByGuidRequest();
        $request->guid = $GUID;
        $result = $client->GetSubProductByGuid($request);

        return $result;
    }

    public function getProductByTypeList ($type)
    {
        $client = $this->getSoapClient('product');
        $request = new getProductByTypeListRequest();
        $request->productType = $type;
        $result = $client->GetProductByTypeList($request);
        return $result;
    }

    public function getSubProductByProductList ($guid)
    {
        $client = $this->getSoapClient('product');
        $request = new getSubProductByProductListRequest();
        $request->productGuid = $guid;
        $result = $client->GetSubProductByProductList ($request);
        return $result;
    }

    public function getProductItemList  ($productType, $product_guid, $subproduct_guid)
    {
        $client = $this->getSoapClient('product');
        $request = new getProductItemListRequest();
        $request->productType = $productType;
        $request->product = new Product();
        $request->product->guid = $product_guid;
        $request->subProduct = new SubProduct();
        $request->subProduct->guid = $subproduct_guid;
        $result = $client->GetProductItemList ($request);
        return $result;
    }
}