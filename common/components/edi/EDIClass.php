<?php

namespace common\components\edi;

use api_web\components\Registry;
use common\models\edi\EdiFilesQueue;
use common\models\OuterUnit;
use yii\base\Component;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Currency;
use common\models\EdiOrder;
use common\models\EdiOrderContent;
use common\models\edi\EdiOrganization;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\User;
use frontend\controllers\OrderController;
use yii\base\Controller;
use yii\base\Exception;
use yii\db\Expression;
use Yii;

class EDIClass extends Component
{
    public $ediDocumentType;
    public $fileName;

    public function parseFile($content, $providerID)
    {
        if (!$content) {
            return false;
        }
        $dom = new \DOMDocument();
        $dom->loadXML($content);
        $simpleXMLElement = simplexml_import_dom($dom);

        $success = false;
        if (strpos($content, 'PRICAT>')) {
            $success = $this->handlePriceListUpdating($simpleXMLElement, $providerID);
        } elseif (strpos($content, 'ORDRSP>')) {
            $this->ediDocumentType = 'ORDRSP';
            $success = $this->handleOrderResponse($simpleXMLElement, 1, $providerID, false);
        } elseif (strpos($content, 'DESADV>')) {
            $this->ediDocumentType = 'DESADV';
            $success = $this->handleOrderResponse($simpleXMLElement, 2, $providerID, false);
        } elseif (strpos($content, 'ALCDES>')) {
            $this->ediDocumentType = 'ALCDES';
            $success = $this->handleOrderResponse($simpleXMLElement, 3, $providerID, true);
        }
        return $success;
    }

    public function handleOrderResponse($simpleXMLElement, $documentType, $providerID, $isAlcohol = false, $isLeraData = false, $exceptionArray = [])
    {
        try {
            $orderID = $simpleXMLElement->ORDERNUMBER;
            if ($isLeraData) {
                $head = $simpleXMLElement->HEAD[0];
                $supplier = $head->BUYER;
            } else {
                $head = $simpleXMLElement->HEAD;
                $supplier = $head->SUPPLIER;
            }

            $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplier, 'provider_id' => $providerID]);
            if (!$ediOrganization) {
                throw new Exception('no EDI organization found');
            }

            if ($isLeraData) {
                $order = Order::findOne(['id' => $orderID, 'client_id' => $ediOrganization->organization_id]);
            } else {
                $order = Order::findOne(['id' => $orderID, 'vendor_id' => $ediOrganization->organization_id]);
            }

            $message = "";
            if (!$order) {
                throw new Exception('No such order');
            }

            \Yii::$app->language = $order->edi_order->lang ?? 'ru';
            $user = User::findOne(['id' => $order->created_by_id]);
            if (!$user) {
                throw new Exception('No such user');
            }

            $positions = $head->POSITION ?? null;
            $isDesadv = false;

            if (!count($positions)) {
                if ($isLeraData) {
                    $seq = $head->PACKINGSEQUENCE[0];
                    $positions = $seq->POSITION;
                } else {
                    $positions = $head->PACKINGSEQUENCE->POSITION;
                }
                $isDesadv = true;
            }

            $positionsArray = [];
            $arr = [];
            $barcodeArray = [];
            $totalQuantity = 0;
            $totalPrice = 0;

            foreach ($positions as $position) {
                if (!isset($position->PRODUCT)) continue;
                $contID = (int)$position->PRODUCTIDBUYER;
                $positionsArray[] = (int)$contID;
                if ($isDesadv) {
                    $arr[$contID]['ACCEPTEDQUANTITY'] = (float)$position->DELIVEREDQUANTITY ?? (float)$position->ORDEREDQUANTITY;
                } else {
                    $arr[$contID]['ACCEPTEDQUANTITY'] = (float)$position->ACCEPTEDQUANTITY ?? (float)$position->ORDEREDQUANTITY;
                }
                $arr[$contID]['DELIVEREDQUANTITY'] = (isset($position->DELIVEREDQUANTITY)) ? (float)$position->DELIVEREDQUANTITY : 0.00;
                $arr[$contID]['PRICE'] = (float)$position->PRICE[0] ?? (float)$position->PRICE ?? 0;
                $arr[$contID]['PRICEWITHVAT'] = (isset($position->PRICEWITHVAT)) ? (float)$position->PRICEWITHVAT : 0.00;;
                $arr[$contID]['TAXRATE'] = (isset($position->VAT)) ? (float)$position->VAT : 0.00;
                $arr[$contID]['BARCODE'] = (int)$position->PRODUCT;
                $arr[$contID]['WAYBILLNUMBER'] = isset($position->WAYBILLNUMBER) ? $position->WAYBILLNUMBER : null;
                $arr[$contID]['WAYBILLDATE'] = isset($position->WAYBILLDATE) ? $position->WAYBILLDATE : null;
                $arr[$contID]['DELIVERYNOTENUMBER'] = isset($position->DELIVERYNOTENUMBER) ? $position->DELIVERYNOTENUMBER : null;
                $arr[$contID]['DELIVERYNOTEDATE'] = isset($position->DELIVERYNOTEDATE) ? $position->DELIVERYNOTEDATE : null;
                $arr[$contID]['GTIN'] = isset($position->GTIN) ? $position->GTIN : null;
                $arr[$contID]['UUID'] = isset($position->UUID) ? $position->UUID : null;
                $totalQuantity += $arr[$contID]['ACCEPTEDQUANTITY'];
                $totalPrice += $arr[$contID]['PRICE'];
            }
            if ($totalQuantity == 0.00 || $totalPrice == 0.00) {
                OrderController::sendOrderCanceled($order->client, $order);
                $message .= Yii::t('message', 'frontend.controllers.order.cancelled_order_six', ['ru' => "Заказ № {order_id} отменен!", 'order_id' => $order->id]);
                OrderController::sendSystemMessage($user, $order->id, $message);
                $order->status = OrderStatus::STATUS_REJECTED;
                if (!$order->save()) {
                    throw new Exception('Error saving order');
                }
                return true;
            }

            $summ = 0;
            $orderContentArr = [];
            foreach ($order->orderContent as $orderContent) {
                $index = $orderContent->id;
                $orderContentArr[] = $orderContent->id;
                if (!in_array($index, $positionsArray)) {
                    $orderContent->delete();
                    $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
                    continue;
                }
                if (!isset($arr[$index]['BARCODE'])) {
                    if (isset($orderContent->ediOrderContent)) {
                        $index = $orderContent->ediOrderContent->barcode;
                        $orderContentArr[] = $index;
                    } else {
                        continue;
                    }
                }
                $good = CatalogBaseGoods::findOne(['barcode' => $arr[$index]['BARCODE']]);
                if (!$good) continue;
                $barcodeArray[] = $good->barcode;

                $oldQuantity = (float)$orderContent->quantity;
                $newQuantity = (float)$arr[$index]['ACCEPTEDQUANTITY'];

                if ($oldQuantity != $newQuantity) {
                    if (!$newQuantity || $newQuantity == 0.000) {
                        $orderContent->delete();
                        $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
                        continue;
                    } else {
                        $message .= Yii::t('message', 'frontend.controllers.order.change', ['ru' => "<br/>изменил количество {prod} с {oldQuan} {ed} на ", 'prod' => $orderContent->product_name, 'oldQuan' => $oldQuantity, 'ed' => $good->ed]) . " $newQuantity" . $good->ed;
                    }
                }
                $oldPrice = (float)$orderContent->price;
                $newPrice = (float)$arr[$index]['PRICE'];
                if ($oldPrice != $newPrice) {
                    if ($newPrice == 0) {
                        $orderContent->delete();
                        $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
                        continue;
                    } else {
                        $change = " <br/>" . Yii::t('message', 'frontend.controllers.order.change_price', ['ru' => "<br/>изменил цену {prod} с {productPrice} руб на ", 'prod' => $orderContent->product_name, 'productPrice' => $oldPrice, 'currencySymbol' => $order->currency->iso_code]) . " " . $newPrice . " руб";
                        $message .= $change;
                    }
                }
                $summ += $newQuantity * $newPrice;
                $orderContent->price = $newPrice;
                $orderContent->quantity = $newQuantity;
                $orderContent->vat_product = $arr[$index]['TAXRATE'] ?? 0.00;
                $orderContent->edi_number = $simpleXMLElement->DELIVERYNOTENUMBER ?? null;
                $orderContent->edi_shipment_quantity = $arr[$index]['DELIVEREDQUANTITY'];
                $orderContent->merc_uuid = $arr[$index]['UUID'] ?? null;
                if ($documentType == 2) {
                    $orderContent->edi_desadv = $this->fileName;
                }
                if ($documentType == 3) {
                    $orderContent->edi_alcdes = $this->fileName;
                }
                if (!$orderContent->save()) {
                    throw new Exception('Error saving order content');
                }
            }
            foreach ($positions as $position) {
                $quantity = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY;
                if (!$quantity || $quantity == 0.000 || $position->PRICE == 0.00) continue;
                $contID = (int)$position->PRODUCTIDBUYER;
                if (!$contID) {
                    $contID = (int)$position->PRODUCT;
                }
                if (!$contID) continue;
                $barcode = (int)$position->PRODUCT;
                if (!in_array($contID, $orderContentArr) && !in_array($barcode, $barcodeArray)) {
                    $good = CatalogBaseGoods::findOne(['barcode' => $position->PRODUCT]);
                    if (!$good) continue;
                    if ($isDesadv) {
                        $quan = $position->DELIVEREDQUANTITY ?? $position->ORDEREDQUANTITY;
                    } else {
                        $quan = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY;
                    }
                    $quan = (float)$quan;
                    $price = (float)$position->PRICE;
                    $newOrderContent = new OrderContent();
                    $newOrderContent->order_id = $order->id;
                    $newOrderContent->product_id = $good->id;
                    $newOrderContent->quantity = $quan;
                    $newOrderContent->price = $price;
                    $newOrderContent->initial_quantity = $quan;
                    $newOrderContent->product_name = $good->product;
                    $newOrderContent->plan_quantity = $quan;
                    $newOrderContent->plan_price = $price;
                    $newOrderContent->units = $good->units;
                    if (!$newOrderContent->save()) {
                        throw new Exception('Error saving order content');
                    }
                    $message .= " <br/>";
                    $message .= Yii::t('message', 'frontend.controllers.order.add_position', ['ru' => "Добавил товар {prod}", 'prod' => $good->product]);
                    $total = $quan * $price;
                    $summ += $total;
                }
            }

            if ($isDesadv) {
                $orderStatus = OrderStatus::STATUS_EDI_SENT_BY_VENDOR;
            } else {
                $orderStatus = OrderStatus::STATUS_PROCESSING;
            }

            $order->status = $orderStatus;
            $order->total_price = $summ;
            $order->waybill_number = $simpleXMLElement->DELIVERYNOTENUMBER ?? $simpleXMLElement->NUMBER ?? '';
            $order->edi_ordersp = $this->ediDocumentType;
            $order->service_id = 6;
            $order->edi_ordersp = $this->fileName;
            $order->edi_doc_date = $simpleXMLElement->DELIVERYNOTEDATE ?? null;
            $deliveryDate = isset($simpleXMLElement->DELIVERYDATE) ? \Yii::$app->formatter->asDate($simpleXMLElement->DELIVERYDATE, 'yyyy.MM.dd HH:mm:ss') : null;
            $order->actual_delivery = $deliveryDate;
            $order->ediProcessor = 1;
            if (!$order->save()) {
                throw new Exception('Error saving order');
            }

            if ($message != '') {
                OrderController::sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_two', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
            }

            $action = ($isDesadv) ? " " . Yii::t('app', 'отправил заказ!') : Yii::t('message', 'frontend.controllers.order.confirm_order_two', ['ru' => ' подтвердил заказ!']);
            $systemMessage = $order->vendor->name . '' . $action;
            OrderController::sendSystemMessage($user, $order->id, $systemMessage);

            OrderController::sendOrderProcessing($order->client, $order);
            return true;
        } catch (Exception $e) {
            if ($isLeraData) {
                if ($ediOrganization) {
                    $orgID = $ediOrganization->organization_id;
                } else {
                    $orgID = substr($supplier, 0, 8);
                }
                $arr = [
                    'name'            => (String)$exceptionArray['file_id'],
                    'organization_id' => $orgID,
                    'status'          => $exceptionArray['status'],
                    'error_text'      => (String)$e->getMessage(),
                    'json_data'       => $exceptionArray['json_data']
                ];
                $this->insertEdiErrorData($arr);
            }
        }
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function handlePriceListUpdating($xml, $providerID): bool
    {
        $supplierGLN = $xml->SUPPLIER;
        $buyerGLN = $xml->BUYER;
        $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplierGLN, 'provider_id' => $providerID]);
        if (!$ediOrganization) {
            \Yii::error('No EDI organization');
            return false;
        }
        $organization = Organization::findOne(['id' => $ediOrganization->organization_id]);

        if (!$organization || $organization->type_id != Organization::TYPE_SUPPLIER) {
            \Yii::error('No such organization');
            return false;
        }
        $ediRest = EdiOrganization::findOne(['gln_code' => $buyerGLN, 'provider_id' => $providerID]);
        if (!$ediRest) {
            \Yii::error('No EDI organization(rest)');
            return false;
        }
        $rest = Organization::findOne(['id' => $ediRest->organization_id]);
        if (!$rest) {
            \Yii::error('No such organization(rest)');
            return false;
        }
        $currency = Currency::findOne(['iso_code' => $xml->CURRENCY]);
        if (!$currency) {
            $currency = Currency::findOne(['iso_code' => 'RUB']);
        }

        $rel = RelationSuppRest::findOne(['rest_org_id' => $rest->id, 'supp_org_id' => $organization->id]);
        if (!$rel) {
            \Yii::error('No relation');
            return false;
        } else {
            $relationCatalogID = $rel->cat_id;
            $cat = Catalog::findOne(['id' => $relationCatalogID]);
            if (!$relationCatalogID || $cat->type == Catalog::BASE_CATALOG) {
                $relationCatalogID = $this->createCatalog($organization, $currency, $rest);
                $rel->cat_id = $relationCatalogID;
                $rel->status = Catalog::STATUS_ON;
                $rel->save();
            }
        }

        $baseCatalog = $organization->baseCatalog;
        if (!$baseCatalog) {
            $baseCatalog = new Catalog();
            $baseCatalog->type = Catalog::BASE_CATALOG;
            $baseCatalog->supp_org_id = $organization->id;
            $baseCatalog->name = \Yii::t('message', 'frontend.controllers.client.main_cat', ['ru' => 'Главный каталог']);
            $baseCatalog->created_at = new Expression('NOW()');
        }

        $baseCatalog->currency_id = $currency->id ?? 1;
        $baseCatalog->updated_at = new Expression('NOW()');
        $baseCatalog->save();
        $goods = $xml->CATALOGUE->POSITION ?? $xml->CATALOGUE[0]->POSITION;

        $goodsArray = [];
        $barcodeArray = [];
        foreach ($goods as $good) {
            $barcode = (is_array($good->PRODUCT)) ? $good->PRODUCT[0] : $good->PRODUCT;
            $barcode = (String)$barcode;
            if (!$barcode) continue;
            $barcodeArray[] = $barcode;
            $ed = (String)$good->UNIT ?? (String)$good->QUANTITYOFCUINTUUNIT;
            $ed = OuterUnit::getInnerName($ed, Registry::EDI_SERVICE_ID);
            $goodsArray[$barcode]['name'] = (String)$good->PRODUCTNAME ?? '';
            $goodsArray[$barcode]['price'] = (float)$good->UNITPRICE ?? 0.0;
            $goodsArray[$barcode]['article'] = (isset($good->IDBUYER) && $good->IDBUYER != '') ? (String)$good->IDBUYER : $barcode;
            $goodsArray[$barcode]['ed'] = $ed;
            $goodsArray[$barcode]['units'] = (float)$good->QUANTITYOFCUINTU ?? (float)$good->PACKINGMULTIPLENESS ?? $good->MINORDERQUANTITY;
            $goodsArray[$barcode]['edi_supplier_article'] = (isset($good->IDSUPPLIER) && $good->IDSUPPLIER != '') ? (String)$good->IDSUPPLIER : $barcode;
            $goodsArray[$barcode]['vat'] = (int)$good->TAXRATE ?? null;
        }

        $catalog_base_goods = (new \yii\db\Query())
            ->select(['id', 'barcode'])
            ->from(CatalogBaseGoods::tableName())
            ->where(['cat_id' => $baseCatalog->id])
            ->andWhere('`barcode` IS NOT NULL')
            ->all();

        foreach ($catalog_base_goods as $base_good) {
            if (!in_array($base_good['barcode'], $goodsArray)) {
                \Yii::$app->db->createCommand()->delete(CatalogGoods::tableName(), ['base_goods_id' => $base_good['id'], 'cat_id' => $relationCatalogID])->execute();
            }
        }
        foreach ($goodsArray as $barcode => $good) {
            $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
            if (!$catalogBaseGood) {
                $catalogBaseGood = new CatalogBaseGoods();
                $catalogBaseGood->cat_id = $baseCatalog->id;
                $catalogBaseGood->article = $good['article'];
                $catalogBaseGood->product = $good['name'];
                $catalogBaseGood->status = CatalogBaseGoods::STATUS_ON;
                $catalogBaseGood->supp_org_id = $organization->id;
                $catalogBaseGood->price = $good['price'];
                $catalogBaseGood->units = $good['units'];
                $catalogBaseGood->ed = $good['ed'];
                $catalogBaseGood->category_id = null;
                $catalogBaseGood->deleted = 0;
                $catalogBaseGood->barcode = $barcode;
                $catalogBaseGood->edi_supplier_article = $good['edi_supplier_article'];
                $res = $catalogBaseGood->save();
                if (!$res) continue;
                $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
                $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price'], $good['vat']);
                if (!$res2) continue;
            } else {
                $catalogGood = CatalogGoods::findOne(['cat_id' => $relationCatalogID, 'base_goods_id' => $catalogBaseGood->id]);
                if (!$catalogGood) {
                    $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price'], $good['vat']);
                    if (!$res2) continue;
                } else {
                    $catalogGood->price = $good['price'];
                    $catalogGood->save();
                }
                $catalogBaseGood->units = $good['units'];
                $catalogBaseGood->product = $good['name'];
                $catalogBaseGood->article = $good['article'];
                $catalogBaseGood->ed = $good['ed'];
                $catalogBaseGood->edi_supplier_article = $good['edi_supplier_article'];
            }
            $catalogBaseGood->status = CatalogBaseGoods::STATUS_ON;
            if (!$catalogBaseGood->save()) continue;
        }
        return true;
    }

    public function insertGood(int $catID, int $catalogBaseGoodID, float $price, int $vat = null): bool
    {
        $catalogGood = new CatalogGoods();
        $catalogGood->cat_id = $catID;
        $catalogGood->base_goods_id = $catalogBaseGoodID;
        $catalogGood->price = $price;
        $catalogGood->vat = $vat;
        $res = $catalogGood->save();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    private function createCatalog(Organization $organization, $currency, Organization $rest): int
    {
        $catalog = new Catalog();
        $catalog->type = Catalog::CATALOG;
        $catalog->supp_org_id = $organization->id;
        $catalog->name = $organization->name;
        $catalog->status = Catalog::STATUS_ON;
        $catalog->currency_id = $currency->id ?? 1;
        $catalog->save();
        $catalogID = $catalog->id;
        return $catalogID;
    }

    /**
     * @return array
     */
    public function getFileList(): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from(EdiFilesQueue::tableName())
            ->where(['status' => [AbstractRealization::STATUS_NEW, AbstractRealization::STATUS_ERROR]])
            ->all();
    }

    public function getSendingOrderContent($order, $done, $dateArray, $orderContent)
    {
        $vendor = $order->vendor;
        $client = $order->client;
        if (Yii::$app instanceof \yii\console\Application) {
            $controller = new Controller("", "");
        } else {
            $controller = Yii::$app->controller;
        }

        $glnArray = $client->getGlnCodes($client->id, $vendor->id);
        $string = $controller->renderPartial($done ? '@common/views/e_com/order_done' : '@common/views/e_com/create_order', compact('order', 'glnArray', 'dateArray', 'orderContent'));
        return $string;
    }

    public function insertEdiErrorData($arr): void
    {
        Yii::$app->db->createCommand()->insert(EdiFilesQueue::tableName(), $arr)->execute();
    }
}