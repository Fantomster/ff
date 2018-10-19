<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\edi\realization;

use common\components\edi\AbstractRealization;
use common\components\edi\RealizationInterface;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Currency;
use common\models\EdiOrder;
use common\models\EdiOrderContent;
use common\models\EdiOrganization;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\User;
use frontend\controllers\OrderController;
use yii\db\Expression;
use Yii;

/**
 * Class Realization
 *
 * @package common\components\edi\realization
 */
class EcomRealization extends AbstractRealization implements RealizationInterface
{
    /**
     * @var \SimpleXMLElement
     */
    public $xml;

    public function parseFile($content)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($content);
        $simpleXMLElement = simplexml_import_dom($dom);

        $success = false;
        if (strpos($content, 'PRICAT>')) {
            $success = $this->handlePriceListUpdating($simpleXMLElement);
        } elseif (strpos($content, 'ORDRSP>') || strpos($content, 'DESADV>')) {
            $success = $this->handleOrderResponse($simpleXMLElement);
        } elseif (strpos($content, 'ALCDES>')) {
            $success = $this->handleOrderResponse($simpleXMLElement, true);
        }
        return $success;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function handlePriceListUpdating($xml): bool
    {
        $supplierGLN = $xml->SUPPLIER;
        $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplierGLN]);
        if (!$ediOrganization) {
            \Yii::error('No EDI organization');
            return false;
        }
        $organization = Organization::findOne(['id' => $ediOrganization->organization_id]);
        if (!$organization || $organization->type_id != Organization::TYPE_SUPPLIER) {
            \Yii::error('No such organization');
            return false;
        }
        $baseCatalog = $organization->baseCatalog;
        if (!$baseCatalog) {
            $baseCatalog = new Catalog();
            $baseCatalog->type = Catalog::BASE_CATALOG;
            $baseCatalog->supp_org_id = $organization->id;
            $baseCatalog->name = \Yii::t('message', 'frontend.controllers.client.main_cat', ['ru' => 'Главный каталог']);
            $baseCatalog->created_at = new Expression('NOW()');
        }
        $currency = Currency::findOne(['iso_code' => $xml->CURRENCY]);
        $baseCatalog->currency_id = $currency->id ?? 1;
        $baseCatalog->updated_at = new Expression('NOW()');
        $baseCatalog->save();
        $goods = $xml->CATALOGUE->POSITION;
        $goodsArray = [];
        $barcodeArray = [];
        foreach ($goods as $good) {
            $barcode = (String)$good->PRODUCT[0];
            if (!$barcode) continue;
            $barcodeArray[] = $barcode;
            $goodsArray[$barcode]['name'] = (String)$good->PRODUCTNAME ?? '';
            $goodsArray[$barcode]['price'] = (float)$good->UNITPRICE ?? 0.0;
            $goodsArray[$barcode]['article'] = (String)$good->IDBUYER ?? null;
            $goodsArray[$barcode]['ed'] = (String)$good->QUANTITYOFCUINTUUNIT ?? 'шт';
            $goodsArray[$barcode]['units'] = (float)$good->PACKINGMULTIPLENESS ?? 0.0;
            $goodsArray[$barcode]['edi_supplier_article'] = $good->IDSUPPLIER ?? null;
        }
        $catalog_base_goods = (new \yii\db\Query())
            ->select(['id', 'barcode'])
            ->from('catalog_base_goods')
            ->where(['cat_id' => $baseCatalog->id])
            ->andWhere('`barcode` IS NOT NULL')
            ->all();

        foreach ($catalog_base_goods as $base_good) {
            if (!in_array($base_good['barcode'], $goodsArray)) {
                \Yii::$app->db->createCommand()->update('catalog_base_goods', ['status' => CatalogBaseGoods::STATUS_OFF], 'id=' . $base_good['id'])->execute();
            }
        }

        $buyerGLN = $xml->BUYER;
        $ediRest = EdiOrganization::findOne(['gln_code' => $buyerGLN]);
        if (!$ediRest) {
            return false;
        }
        $rest = Organization::findOne(['id' => $ediRest->organization_id]);
        if (!$rest) {
            return false;
        }

        $rel = RelationSuppRest::findOne(['rest_org_id' => $rest->id, 'supp_org_id' => $organization->id]);
        if (!$rel) {
            $relationCatalogID = $this->createCatalog($organization, $currency, $rest);
        } else {
            $relationCatalogID = $rel->cat_id;
        }

        foreach ($goodsArray as $barcode => $good) {
            $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
            if (!$catalogBaseGood) {
                $res = \Yii::$app->db->createCommand()->insert('catalog_base_goods', [
                    'cat_id'               => $baseCatalog->id,
                    'article'              => $good['article'],
                    'product'              => $good['name'],
                    'status'               => CatalogBaseGoods::STATUS_ON,
                    'supp_org_id'          => $organization->id,
                    'price'                => $good['price'],
                    'units'                => $good['units'],
                    'ed'                   => $good['ed'],
                    'created_at'           => new Expression('NOW()'),
                    'category_id'          => null,
                    'deleted'              => 0,
                    'barcode'              => $barcode,
                    'edi_supplier_article' => $good['edi_supplier_article']
                ])->execute();
                if (!$res) continue;
                $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
                $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price']);
                if (!$res2) continue;
            } else {
                $catalogGood = CatalogGoods::findOne(['cat_id' => $relationCatalogID, 'base_goods_id' => $catalogBaseGood->id]);
                if (!$catalogGood) {
                    $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price']);
                    if (!$res2) continue;
                } else {
                    $catalogGood->price = $good['price'];
                    $catalogGood->save();
                }
            }
            \Yii::$app->db->createCommand()->update('catalog_base_goods', ['updated_at' => new Expression('NOW()'), 'status' => CatalogBaseGoods::STATUS_ON], 'id=' . $catalogBaseGood->id)->execute();
        }
        return true;
    }

    protected function insertGood(int $catID, int $catalogBaseGoodID, float $price): bool
    {
        $res = Yii::$app->db->createCommand()->insert('catalog_goods', [
            'cat_id'        => $catID,
            'base_goods_id' => $catalogBaseGoodID,
            'created_at'    => new Expression('NOW()'),
            'updated_at'    => new Expression('NOW()'),
            'price'         => $price,
        ])->execute();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    protected function handleOrderResponse(\SimpleXMLElement $simpleXMLElement, $isAlcohol = false)
    {
        $orderID = $simpleXMLElement->NUMBER;
        $supplier = $simpleXMLElement->HEAD->SUPPLIER;
        $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplier]);
        if (!$ediOrganization) {
            return false;
        }
        $order = Order::findOne(['id' => $orderID, 'vendor_id' => $ediOrganization->organization_id]);
        $message = "";
        if (!$order) {
            Yii::error('No such order ID: ' . $orderID);
            return false;
        }
        \Yii::$app->language = $order->edi_order->lang ?? 'ru';
        $user = User::findOne(['id' => $order->created_by_id]);

        $positions = $simpleXMLElement->HEAD->POSITION;
        $isDesadv = false;
        if (!count($positions)) {
            $positions = $simpleXMLElement->HEAD->PACKINGSEQUENCE->POSITION;
            $isDesadv = true;
        }
        $positionsArray = [];
        $arr = [];
        $barcodeArray = [];
        $totalQuantity = 0;
        $totalPrice = 0;
        foreach ($positions as $position) {
            $contID = (int)$position->PRODUCTIDBUYER;
            if (!$contID) {
                $contID = (int)$position->PRODUCT;
            }
            $positionsArray[] = (int)$contID;
            if ($isDesadv) {
                $arr[$contID]['ACCEPTEDQUANTITY'] = (float)$position->DELIVEREDQUANTITY ?? (float)$position->ORDEREDQUANTITY;
            } else {
                $arr[$contID]['ACCEPTEDQUANTITY'] = (float)$position->ACCEPTEDQUANTITY ?? (float)$position->ORDEREDQUANTITY;
            }
            $arr[$contID]['PRICE'] = (float)$position->PRICE[0] ?? (float)$position->PRICEWITHVAT;
            $arr[$contID]['PRICEWITHVAT'] = (float)$position->PRICEWITHVAT ?? 0.00;
            $arr[$contID]['TAXRATE'] = (float)$position->TAXRATE ?? 0.00;
            $arr[$contID]['BARCODE'] = (int)$position->PRODUCT;
            $arr[$contID]['WAYBILLNUMBER'] = $position->WAYBILLNUMBER ?? null;
            $arr[$contID]['WAYBILLDATE'] = $position->WAYBILLDATE ?? null;
            $arr[$contID]['DELIVERYNOTENUMBER'] = $position->DELIVERYNOTENUMBER ?? null;
            $arr[$contID]['DELIVERYNOTEDATE'] = $position->DELIVERYNOTEDATE ?? null;
            $arr[$contID]['GTIN'] = $position->GTIN ?? null;
            $arr[$contID]['UUID'] = $position->UUID ?? null;
            $totalQuantity += $arr[$contID]['ACCEPTEDQUANTITY'];
            $totalPrice += $arr[$contID]['PRICE'];
        }
        if ($totalQuantity == 0.00 || $totalPrice == 0.00) {
            OrderController::sendOrderCanceled($order->client, $order);
            $message .= Yii::t('message', 'frontend.controllers.order.cancelled_order_six', ['ru' => "Заказ № {order_id} отменен!", 'order_id' => $order->id]);
            OrderController::sendSystemMessage($user, $order->id, $message);
            $order->status = OrderStatus::STATUS_REJECTED;
            $order->save();
            return true;
        }

        $summ = 0;
        $ordContArr = [];
        foreach ($order->orderContent as $orderContent) {
            $index = $orderContent->article;
            $ordContArr[] = $orderContent->id;
            if (!isset($arr[$index]['BARCODE'])) {
                if (isset($orderContent->ediOrderContent)) {
                    $index = $orderContent->ediOrderContent->barcode;
                    $ordContArr[] = $index;
                } else {
                    continue;
                }
            }
            if (!isset($arr[$index]['BARCODE'])) continue;
            $good = CatalogBaseGoods::findOne(['barcode' => $arr[$index]['BARCODE']]);
            if (!$good) continue;
            $barcodeArray[] = $good->barcode;

            $ordCont = OrderContent::findOne(['id' => $orderContent->id]);
            if (!$ordCont) continue;
            if (!in_array($index, $positionsArray)) {
                $ordCont->delete();
                $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
            } else {
                $oldQuantity = (float)$ordCont->quantity;
                $newQuantity = (float)$arr[$index]['ACCEPTEDQUANTITY'];

                if ($oldQuantity != $newQuantity) {
                    if ($newQuantity == 0) {
                        $ordCont->delete();
                        $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
                    } else {
                        $message .= Yii::t('message', 'frontend.controllers.order.change', ['ru' => "<br/>изменил количество {prod} с {oldQuan} {ed} на ", 'prod' => $ordCont->product_name, 'oldQuan' => $oldQuantity, 'ed' => $good->ed]) . " $newQuantity" . $good->ed;
                    }
                }

                $oldPrice = (float)$ordCont->price;
                $newPrice = (float)$arr[$index]['PRICE'];
                if ($oldPrice != $newPrice) {
                    if ($newPrice == 0) {
                        $ordCont->delete();
                        $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
                    } else {
                        $message .= Yii::t('message', 'frontend.controllers.order.change_price', ['ru' => "<br/>изменил цену {prod} с {productPrice} руб на ", 'prod' => $orderContent->product_name, 'productPrice' => $oldPrice, 'currencySymbol' => $order->currency->iso_code]) . $newPrice . " руб";
                    }
                }
                $summ += $newQuantity * $newPrice;
                Yii::$app->db->createCommand()->update('order_content', ['price' => $newPrice, 'quantity' => $newQuantity, 'updated_at' => new Expression('NOW()')], 'id=' . $ordCont->id)->execute();

                $docType = ($isAlcohol) ? EdiOrderContent::ALCDES : EdiOrderContent::DESADV;
                $ediOrderContent = EdiOrderContent::findOne(['order_content_id' => $orderContent->id]);
                if ($ediOrderContent) {
                    $ediOrderContent->doc_type = $docType;
                    $ediOrderContent->pricewithvat = $arr[$index]['PRICEWITHVAT'] ?? 0.00;
                    $ediOrderContent->taxrate = $arr[$index]['TAXRATE'] ?? 0.00;
                    $ediOrderContent->uuid = $arr[$index]['UUID'];
                    $ediOrderContent->gtin = $arr[$index]['GTIN'];
                    $ediOrderContent->waybill_date = $arr[$index]['WAYBILLDATE'];
                    $ediOrderContent->waybill_number = $arr[$index]['WAYBILLNUMBER'];
                    $ediOrderContent->delivery_note_date = $arr[$index]['DELIVERYNOTEDATE'];
                    $ediOrderContent->delivery_note_number = $arr[$index]['DELIVERYNOTENUMBER'];
                    $ediOrderContent->save();
                }
            }
        }
        if (!$isDesadv) {
            foreach ($positions as $position) {
                if ($position->ACCEPTEDQUANTITY == 0.00 || $position->PRICE == 0.00) continue;
                $contID = (int)$position->PRODUCTIDBUYER;
                if (!$contID) {
                    $contID = (int)$position->PRODUCT;
                }
                if (!$contID) continue;
                $barcode = (int)$position->PRODUCT;
                if (!in_array($contID, $ordContArr) && !in_array($barcode, $barcodeArray)) {
                    $good = CatalogBaseGoods::findOne(['barcode' => $position->PRODUCT]);
                    if (!$good) continue;
                    if ($isDesadv) {
                        $quan = $position->DELIVEREDQUANTITY ?? $position->ORDEREDQUANTITY;
                    } else {
                        $quan = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY;
                    }
                    Yii::$app->db->createCommand()->insert('order_content', [
                        'order_id'         => $order->id,
                        'product_id'       => $good->id,
                        'quantity'         => $quan,
                        'price'            => (float)$position->PRICE,
                        'initial_quantity' => $quan,
                        'product_name'     => $good->product,
                        'plan_quantity'    => $quan,
                        'plan_price'       => (float)$position->PRICE,
                        'units'            => $good->units,
                        'updated_at'       => new Expression('NOW()'),
                    ])->execute();
                    $message .= Yii::t('message', 'frontend.controllers.order.add_position', ['ru' => "Добавил товар {prod}", 'prod' => $good->product]);
                    $summ += $quan * $position->PRICE;
                }
            }
        }
        Yii::$app->db->createCommand()->update('order', ['status' => OrderStatus::STATUS_PROCESSING, 'total_price' => $summ, 'updated_at' => new Expression('NOW()')], 'id=' . $order->id)->execute();
        $ediOrder = EdiOrder::findOne(['order_id' => $order->id]);
        if ($ediOrder) {
            $ediOrder->invoice_number = $simpleXMLElement->DELIVERYNOTENUMBER ?? '';
            $ediOrder->invoice_date = $simpleXMLElement->DELIVERYNOTEDATE ?? '';
            $ediOrder->save();
        }

        if ($message != '') {
            OrderController::sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_two', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
        }

        $systemMessage = $order->vendor->name . Yii::t('message', 'frontend.controllers.order.confirm_order_two', ['ru' => ' подтвердил заказ!']);
        OrderController::sendSystemMessage($user, $order->id, $systemMessage);

        OrderController::sendOrderProcessing($order->client, $order);
        return true;
    }

    public function getSendingOrderContent($order, $done, $dateArray, $orderContent)
    {
        $vendor = $order->vendor;
        $client = $order->client;
        $string = Yii::$app->controller->renderPartial($done ? '@common/views/e_com/order_done' : '@common/views/e_com/create_order', compact('order', 'vendor', 'client', 'dateArray', 'orderContent'));
        return $string;
    }

    /**
     * @return array
     */
    public function getFileList(): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('edi_files_queue')
            ->where(['status' => [AbstractRealization::STATUS_NEW, AbstractRealization::STATUS_ERROR]])
            ->all();
    }
}