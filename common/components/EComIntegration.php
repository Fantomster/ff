<?php

namespace common\components;

use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Currency;
use common\models\Order;
use common\models\OrderContent;
use common\models\Organization;
use common\models\RelationSuppRest;
use frontend\controllers\OrderController;
use mongosoft\soapclient\Client;
use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\db\Expression;

/**
 * Class for E-COM integration methods
 *
 * @author alexey.sergeev
 *
 */
class EComIntegration extends Component {


    public function handleFilesList(String $login, String $pass): void
    {
        $client = Yii::$app->siteApi;
        $object = $client->getList(['user' => ['login' => $login, 'pass' => $pass]]);
        if($object->result->errorCode != 0){
            Yii::error('EComIntegration getList Error');
            throw new ErrorException();
        }
        $list = $object->result->list ?? null;
        if(!$list){
            echo "No files";
            exit();
        }
        if(is_iterable($list)){
            foreach ($list as $fileName){
                //if (strpos($fileName, 'ricat_')){
                    $this->getDoc($client, $fileName, $login, $pass);
                //}
            }
        }else{
            $this->getDoc($client, $list, $login, $pass);
        }
    }


    private function getDoc(Client $client, String $fileName, String $login, String $pass): bool
    {
        $doc = $client->getDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $fileName]);
        $content = $doc->result->content;
        $dom = new \DOMDocument();
        $dom->loadXML($content);
        $simpleXMLElement = simplexml_import_dom($dom);
        if(strpos($content, 'PRICAT>')){
            $this->handlePriceListUpdating($simpleXMLElement);
        }
        if(strpos($content, 'ORDRSP>') || strpos($content, 'DESADV>')){
            $this->handleOrderResponse($simpleXMLElement);
        }
        $client->archiveDoc(['user' => ['login' => Yii::$app->params['e_com']['login'], 'pass' => Yii::$app->params['e_com']['pass']], 'fileName' => $fileName]);
        return true;
    }


    private function handleOrderResponse(\SimpleXMLElement $simpleXMLElement)
    {
        $orderID = $simpleXMLElement->NUMBER;
        $order = Order::findOne(['id' => $orderID]);
        if(!$order){
            Yii::error('No such order');
            return false;
        }

        $order->status = Order::STATUS_PROCESSING;
        $order->updated_at = new Expression('NOW()');
        if(isset($simpleXMLElement->DELIVERYNOTENUMBER)){
            $order->invoice_number = $simpleXMLElement->DELIVERYNOTENUMBER;
        }
        if(isset($simpleXMLElement->DELIVERYNOTEDATE)){
            $order->invoice_date = $simpleXMLElement->DELIVERYNOTEDATE;
        }

        $positions = $simpleXMLElement->HEAD->POSITION;
        $isDesadv = false;
        if(!count($positions)){
            $positions = $simpleXMLElement->HEAD->PACKINGSEQUENCE->POSITION;
            $isDesadv = true;
        }
        $positionsArray = [];
        $arr = [];
        foreach ($positions as $position){
            $contID = (int) $position->PRODUCTIDBUYER;
            $positionsArray[] = (int) $contID;
            if($isDesadv){
                $arr[$contID]['ACCEPTEDQUANTITY'] = $position->DELIVEREDQUANTITY ?? $position->ORDEREDQUANTITY;
            }else{
                $arr[$contID]['ACCEPTEDQUANTITY'] = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY;
            }
            $arr[$contID]['PRICE'] = $position->PRICE;
        }
        $summ = 0;
        $ordContArr = [];
        foreach ($order->orderContent as $orderContent){
            $ordContArr[] = $orderContent->id;
            $ordCont = OrderContent::findOne(['id' => $orderContent->id]);
            if(!$ordCont)continue;
            if(!in_array($ordCont->id, $positionsArray)){
                $ordCont->delete();
            }else{
                $ordCont->quantity = $arr[$orderContent->id]['ACCEPTEDQUANTITY'];
                $ordCont->price = $arr[$orderContent->id]['PRICE'];
                $summ+=$arr[$orderContent->id]['ACCEPTEDQUANTITY']*$arr[$orderContent->id]['PRICE'];
                $ordCont->save();
            }
        }
        foreach ($positions as $position){
            $contID = (int) $position->PRODUCTIDBUYER;
            if(!in_array($contID, $ordContArr)){
                $good = CatalogBaseGoods::findOne(['barcode' => $position->PRODUCT]);
                if($isDesadv){
                    $quan = $position->DELIVEREDQUANTITY ?? $position->ORDEREDQUANTITY;
                }else{
                    $quan = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY;
                }
                Yii::$app->db->createCommand()->insert('catalog_base_goods', [
                    'order_id' => $order->id,
                    'product_id' => $good->id,
                    'quantity' => $quan,
                    'price' => $position->PRICE,
                    'initial_quantity' => $quan,
                    'product_name' => $good->product,
                    'plan_quantity' => $quan,
                    'plan_price' => $position->PRICE,
                    'units' => $good->units,
                    'updated_at' => new Expression('NOW()'),
                ])->execute();
                $summ+=$quan*$position->PRICE;
            }
        }
        $order->total_price = $summ;
        $order->save();
        OrderController::sendOrderProcessing($order->client, $order);
    }


    private function handlePriceListUpdating(\SimpleXMLElement $simpleXMLElement): bool
    {
        $supplierGLN = $simpleXMLElement->SUPPLIER;
        $organization = Organization::findOne(['gln_code'=>$supplierGLN]);
        if(!$organization || $organization->type_id != Organization::TYPE_SUPPLIER){
            return false;
        }
        $baseCatalog = $organization->baseCatalog;
        if(!$baseCatalog){
            $baseCatalog = new Catalog();
            $baseCatalog->type = Catalog::BASE_CATALOG;
            $baseCatalog->supp_org_id = $organization->id;
            $baseCatalog->name = Yii::t('message', 'frontend.controllers.client.main_cat', ['ru' => 'Главный каталог']);;
            $baseCatalog->created_at = new Expression('NOW()');
        }
        $currency = Currency::findOne(['iso_code' => $simpleXMLElement->CURRENCY]);
        $baseCatalog->currency_id = $currency->id ?? 1;
        $baseCatalog->updated_at = new Expression('NOW()');
        $baseCatalog->save();
        $goods = $simpleXMLElement->CATALOGUE->POSITION;
        $goodsArray = [];
        $barcodeArray = [];
        foreach ($goods as $good){
            $barcode = (String) $good->PRODUCT[0];
            if(!$barcode)continue;
            $barcodeArray[] = $barcode;
            $goodsArray[$barcode]['name'] = (String) $good->PRODUCTNAME ?? '';
            $goodsArray[$barcode]['price'] = (float) $good->UNITPRICE ?? 0.0;
            $goodsArray[$barcode]['article'] = (String) $good->IDBUYER ?? null;
            $goodsArray[$barcode]['ed'] = (String) $good->QUANTITYOFCUINTUUNIT ?? 'шт';
            $goodsArray[$barcode]['units'] = (float) $good->PACKINGMULTIPLENESS ?? 0.0;
        }

        $catalog_base_goods = (new \yii\db\Query())
            ->select(['id', 'barcode'])
            ->from('catalog_base_goods')
            ->where(['cat_id' => $baseCatalog->id])
            ->andWhere('`barcode` IS NOT NULL')
            ->all();

        foreach ($catalog_base_goods as $base_good){
            if(!in_array($base_good['barcode'], $goodsArray)){
                Yii::$app->db->createCommand()->update('catalog_base_goods', ['status' => CatalogBaseGoods::STATUS_OFF], 'id='.$base_good['id'])->execute();
            }
        }

        $buyerGLN = $simpleXMLElement->BUYER;
        $rest = Organization::findOne(['gln_code' => $buyerGLN]);
        if(!$rest){
            return false;
        }

        $rel = RelationSuppRest::findOne(['rest_org_id' => $rest->id, 'supp_org_id' => $organization->id]);
        if(!$rel){
            $relationCatalogID = $this->createCatalog($organization, $currency, $rest);
        }else{
            $relationCatalogID = $rel->cat_id;
        }

        $i=0;
        foreach ($goodsArray as $barcode => $good){
            if($i>24)break;
            $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
            if (!$catalogBaseGood) {
                $res = Yii::$app->db->createCommand()->insert('catalog_base_goods', [
                    'cat_id' => $baseCatalog->id,
                    'article' => $good['article'],
                    'product' => $good['name'],
                    'status' => CatalogBaseGoods::STATUS_ON,
                    'supp_org_id' => $organization->id,
                    'price' => $good['price'],
                    'units' => $good['units'],
                    'ed' => $good['ed'],
                    'created_at' => new Expression('NOW()'),
                    'category_id' => null,
                    'deleted' => 0,
                    'barcode' => $barcode,
                ])->execute();
                if(!$res)continue;
                $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
                $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price']);
                if(!$res2)continue;
            }else{
                $catalogGood = CatalogGoods::findOne(['cat_id' => $relationCatalogID, 'base_goods_id' => $catalogBaseGood->id]);
                if(!$catalogGood){
                    $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price']);
                    if(!$res2)continue;
                }else{
                    $catalogGood->price = $good['price'];
                    $catalogGood->save();
                }
            }
            Yii::$app->db->createCommand()->update('catalog_base_goods', ['updated_at' => new Expression('NOW()'), 'status' => CatalogBaseGoods::STATUS_ON], 'id='.$catalogBaseGood->id)->execute();
            $i++;
        }
        return true;
    }


    private function createCatalog(Organization $organization, Currency $currency, Organization $rest): int
    {
        $catalog = new Catalog();
        $catalog->type = Catalog::CATALOG;
        $catalog->supp_org_id = $organization->id;
        $catalog->name = $organization->name;
        $catalog->status = Catalog::STATUS_ON;
        $catalog->created_at = new Expression('NOW()');
        $catalog->updated_at = new Expression('NOW()');
        $catalog->currency_id = $currency->id ?? 1;
        $catalog->save();
        $catalogID = $catalog->id;

        $rel = new RelationSuppRest();
        $rel->rest_org_id = $rest->id;
        $rel->supp_org_id = $organization->id;
        $rel->cat_id = $catalogID;
        $rel->invite = 1;
        $rel->created_at = new Expression('NOW()');
        $rel->updated_at = new Expression('NOW()');
        $rel->status = RelationSuppRest::CATALOG_STATUS_ON;
        $rel->save();
        return $catalogID;
    }


    private function insertGood(int $catID, int $catalogBaseGoodID, float $price): bool
    {
        $res = Yii::$app->db->createCommand()->insert('catalog_goods', [
            'cat_id' => $catID,
            'base_goods_id' => $catalogBaseGoodID,
            'created_at' => new Expression('NOW()'),
            'updated_at' => new Expression('NOW()'),
            'price' => $price,
        ])->execute();
        if($res){
            return true;
        }else{
            return false;
        }
    }


    public function sendOrderInfo(Order $order, Organization $vendor, Organization $client, bool $done = false): bool
    {
        $orderContent = OrderContent::findAll(['order_id'=>$order->id]);
        $dateArray = $this->getDateData($order);
        $string = Yii::$app->controller->renderPartial($done ? '@common/views/e_com/order_done' : '@common/views/e_com/create_order', compact('order', 'vendor', 'client', 'dateArray', 'orderContent'));
        $currentDate = date("Ymdhis");
        $fileName = $done ? 'recadv_' : 'order_';
        $remoteFile = $fileName . $currentDate . '_' . $order->id . '.xml';
        return $this->sendDoc($string, $remoteFile);
    }


    private function formatDate(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }


    private function formatTime(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('H:i');
    }


    private function getDateData(Order $order): array
    {
        $arr = [];
        $arr['created_at'] = $this->formatDate($order->created_at ?? '');
        $arr['requested_delivery_date'] = $this->formatDate($order->requested_delivery ?? '');
        $arr['requested_delivery_time'] = $this->formatTime($order->requested_delivery ?? '');
        $arr['actual_delivery_date'] = $this->formatDate($order->actual_delivery ?? '');
        $arr['actual_delivery_time'] = $this->formatTime($order->actual_delivery ?? '');
        return $arr;
    }


    private function sendDoc(String $string, String $remoteFile): bool
    {
        $client = Yii::$app->siteApi;
        $obj = $client->sendDoc(['user' => ['login' => Yii::$app->params['e_com']['login'], 'pass' => Yii::$app->params['e_com']['pass']], 'fileName' => $remoteFile, 'content' => $string]);
        if(isset($obj) && isset($obj->result->errorCode) && $obj->result->errorCode == 0){
            return true;
        }else{
            Yii::error("Ecom returns error code");
            return false;
        }
    }

}
