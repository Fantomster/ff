<?php

namespace common\components;

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
use mongosoft\soapclient\Client;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Class for E-COM integration methods
 *
 * @author alexey.sergeev
 */
class EComIntegration extends Component
{

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_ERROR = 3;
    const STATUS_HANDLED = 4;

    /**
     * get distinct organization
     * */
    private function getOrganizations()
    {
        return EdiOrganization::find()->where(['and', ['not', ['gln_code' => null]], ['not', ['gln_code' => '']]])
            ->andWhere(['and', ['not', ['login' => null]], ['not', ['login' => '']]])
            ->andWhere(['and', ['not', ['pass' => null]], ['not', ['pass' => '']]])
            ->groupBy('login')->distinct()->all();
    }

    public function handleFilesList(): void
    {
        $ediOrganizations = $this->getOrganizations();

        if (is_iterable($ediOrganizations)) {
            foreach ($ediOrganizations as $ediOrganization) {
                $login = $ediOrganization['login'];
                $pass = $ediOrganization['pass'];

                $client = Yii::$app->siteApi;
                try {
                    $object = $client->getList(['user' => ['login' => $login, 'pass' => $pass]]);
                } catch (\Throwable $e) {
                    Yii::error($e->getMessage());
                    continue;
                }
                if ($object->result->errorCode != 0) {
                    Yii::error('EComIntegration getList Error №' . $object->result->errorCode);
                    continue;
                }
                $list = $object->result->list ?? null;

                if (!$list) {
                    Yii::error('No files for ' . $ediOrganization['login']);
                    continue;
                }
                if (!empty($list)) {
                    $this->insertFilesInQueue($list);
                }
            }
        }
    }

    /**
     * @throw \Exception
     * */
    private function insertFilesInQueue(array $list): void
    {
        $batch = [];
        $files = (new \yii\db\Query())
            ->select(['name'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('name')
            ->all();

        foreach ($list as $name) {
            if (!array_key_exists($name, $files)) {
                $batch[] = [$name];
            }
        }

        if (!empty($batch)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                Yii::$app->db->createCommand()->batchInsert('edi_files_queue', ['name'], $batch)->execute();
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollback();
                Yii::error($e);
            }
        }
    }

    public function handleFilesListQueue(): void
    {
        $rows = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('edi_files_queue')
            ->where(['status' => [self::STATUS_NEW, self::STATUS_ERROR]])
            ->all();
        $client = Yii::$app->siteApi;
        $ediOrganizations = $this->getOrganizations();

        foreach ($ediOrganizations as $ediOrganization) {
            foreach ($rows as $item) {
                $this->getDoc($client, $item['name'], $ediOrganization['login'], $ediOrganization['pass'], $item['id']);
            }
        }
    }

    private function getDoc(Client $client, String $fileName, String $login, String $pass, int $ediFilesQueueID): bool
    {
        try {
            $this->updateQueue($ediFilesQueueID, self::STATUS_PROCESSING, '');
            try {
                $doc = $client->getDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $fileName]);
            } catch (\Throwable $e) {
                $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, $e->getMessage());
                Yii::error($e->getMessage());
                return false;
            }

            if (!isset($doc->result->content)) {
                $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, 'No such file');
                return false;
            }

            if (!$this->checkOrgIdAndOrderId($doc->result->content, $ediFilesQueueID, $fileName)) {
                return false;
            }

            $content = $doc->result->content;
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

            if ($success) {
                $client->archiveDoc(['user' => ['login' => Yii::$app->params['e_com']['login'], 'pass' => Yii::$app->params['e_com']['pass']], 'fileName' => $fileName]);
                $this->updateQueue($ediFilesQueueID, self::STATUS_HANDLED, '');
            } else {
                $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, 'Error handling file 1');
            }
        } catch (\Exception $e) {
            Yii::error($e);
            $this->updateQueue($ediFilesQueueID, self::STATUS_ERROR, 'Error handling file 2');
            return false;
        }
        return true;
    }

    /**
     * add org id to file in queue table
     *
     * @var integer $id
     * @var string  $glnCode
     * @return boolean
     * */
    private function addOrgIdToFile($id, $glnCode)
    {
        $orgId = (new \yii\db\Query())
            ->select(['organization_id'])
            ->from('edi_organization')
            ->where(['gln_code' => $glnCode])
            ->one();

        if (!$orgId) {
            return false;
        }

        try {
            Yii::$app->db->createCommand()->update('edi_files_queue', ['organization_id' => $orgId['organization_id']], 'id=' . $id)->execute();
        } catch (\Throwable $t) {
            Yii::error($t->getMessage() . 'error on pdate id=' . $id . 'gln = ' . $glnCode, __METHOD__);
        }
        return true;
    }

    private function updateQueue(int $ediFilesQueueID, int $status, String $errorText): void
    {
        Yii::$app->db->createCommand()->update('edi_files_queue', ['updated_at' => new Expression('NOW()'), 'status' => $status, 'error_text' => $errorText], 'id=' . $ediFilesQueueID)->execute();
    }

    private function handleOrderResponse(\SimpleXMLElement $simpleXMLElement, $isAlcohol = false)
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
            $arr[$contID]['PRICE'] = (float)$position->PRICEWITHVAT ?? (float)$position->PRICE;
            $arr[$contID]['PRICEWITHVAT'] = (float)$position->PRICEWITHVAT ?? 0.00;
            $arr[$contID]['TAXRATE'] = (float)$position->TAXRATE ?? 0.00;
            $arr[$contID]['BARCODE'] = $position->PRODUCT;
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
            $index = $orderContent->id;
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
        if (!$isDesadv) {
            foreach ($positions as $position) {
                if ($position->ACCEPTEDQUANTITY == 0.00 || $position->PRICE == 0.00) continue;
                $contID = (int)$position->PRODUCTIDBUYER;
                if (!$contID) {
                    $contID = (int)$position->PRODUCT;
                }
                if (!$contID) continue;
                $barcode = $position->PRODUCT;
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

    private function handlePriceListUpdating(\SimpleXMLElement $simpleXMLElement): bool
    {
        $supplierGLN = $simpleXMLElement->SUPPLIER;
        $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplierGLN]);
        if (!$ediOrganization) {
            Yii::error('No EDI organization');
            return false;
        }
        $organization = Organization::findOne(['id' => $ediOrganization->organization_id]);
        if (!$organization || $organization->type_id != Organization::TYPE_SUPPLIER) {
            Yii::error('No such organization');
            return false;
        }
        $baseCatalog = $organization->baseCatalog;
        if (!$baseCatalog) {
            $baseCatalog = new Catalog();
            $baseCatalog->type = Catalog::BASE_CATALOG;
            $baseCatalog->supp_org_id = $organization->id;
            $baseCatalog->name = Yii::t('message', 'frontend.controllers.client.main_cat', ['ru' => 'Главный каталог']);
            $baseCatalog->created_at = new Expression('NOW()');
        }
        $currency = Currency::findOne(['iso_code' => $simpleXMLElement->CURRENCY]);
        $baseCatalog->currency_id = $currency->id ?? 1;
        $baseCatalog->updated_at = new Expression('NOW()');
        $baseCatalog->save();
        $goods = $simpleXMLElement->CATALOGUE->POSITION;
        $goodsArray = [];
        $barcodeArray = [];
        foreach ($goods as $good) {
            $barcode = (String)$good->PRODUCT[0];
            if (!$barcode) {
                continue;
            }
            $barcodeArray[] = $barcode;
            $goodsArray[$barcode]['name'] = (String)$good->PRODUCTNAME ?? '';
            $goodsArray[$barcode]['price'] = (float)$good->UNITPRICE ?? 0.0;
            $goodsArray[$barcode]['article'] = (String)$good->IDBUYER ?? null;
            $goodsArray[$barcode]['ed'] = (String)$good->QUANTITYOFCUINTUUNIT ?? 'шт';
            $goodsArray[$barcode]['units'] = (float)$good->PACKINGMULTIPLENESS ?? 0.0;
            $goodsArray[$barcode]['edi_supplier_article'] = $good->IDSUPPLIER ?? null;
        }

        $catalog_base_goods = (new \yii\db\Query())
            ->select(['cbg.id', 'cbg.barcode'])
            ->from('catalog_base_goods cbg')
            ->where(['cat_id' => $baseCatalog->id])
            ->andWhere('cbg.barcode IS NOT NULL')
            ->all();

        foreach ($catalog_base_goods as $base_good) {
            if (!in_array($base_good['barcode'], $goodsArray)) {
                Yii::$app->db->createCommand()->update('catalog_base_goods', ['status' => CatalogBaseGoods::STATUS_OFF], 'id=' . $base_good['id'])->execute();
            }
        }

        $buyerGLN = $simpleXMLElement->BUYER;
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
                $res = Yii::$app->db->createCommand()->insert('catalog_base_goods', [
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
            Yii::$app->db->createCommand()->update('catalog_base_goods', ['updated_at' => new Expression('NOW()'), 'status' => CatalogBaseGoods::STATUS_ON], 'id=' . $catalogBaseGood->id)->execute();
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

    public function sendOrderInfo(Order $order, Organization $vendor, Organization $client, String $login, String $pass, bool $done = false): bool
    {
        $transaction = Yii::$app->db_api->beginTransaction();
        $result = false;
        try {
            $ediOrder = EdiOrder::findOne(['order_id' => $order->id]);
            if (!$ediOrder) {
                Yii::$app->db->createCommand()->insert('edi_order', [
                    'order_id' => $order->id,
                    'lang'     => Yii::$app->language ?? 'ru'
                ])->execute();
            }
            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            foreach ($orderContent as $one) {
                $catGood = CatalogBaseGoods::findOne(['id' => $one->product_id]);
                if ($catGood) {
                    $ediOrderContent = EdiOrderContent::findOne(['order_content_id' => $one->id]);
                    if (!$ediOrderContent) {
                        Yii::$app->db->createCommand()->insert('edi_order_content', [
                            'order_content_id'     => $one->id,
                            'edi_supplier_article' => $catGood->edi_supplier_article ?? null,
                            'barcode'              => $catGood->barcode ?? null
                        ])->execute();
                    }
                }
            }
            $orderContent = OrderContent::findAll(['order_id' => $order->id]);
            $dateArray = $this->getDateData($order);
            if (!count($orderContent)) {
                Yii::error("Empty order content");
                $transaction->rollback();
                return $result;
            }
            $string = Yii::$app->controller->renderPartial($done ? '@common/views/e_com/order_done' : '@common/views/e_com/create_order', compact('order', 'vendor', 'client', 'dateArray', 'orderContent'));
            $currentDate = date("Ymdhis");
            $fileName = $done ? 'recadv_' : 'order_';
            $remoteFile = $fileName . $currentDate . '_' . $order->id . '.xml';
            $result = $this->sendDoc($vendor, $string, $remoteFile, $login, $pass);
            $transaction->commit();
        } catch (Exception $e) {
            Yii::error($e);
            $transaction->rollback();
        }
        return $result;
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

    private function sendDoc(Organization $vendor, String $string, String $remoteFile, String $login, String $pass): bool
    {
        $client = Yii::$app->siteApi;
        $obj = $client->sendDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $remoteFile, 'content' => $string]);
        if (isset($obj) && isset($obj->result->errorCode) && $obj->result->errorCode == 0) {
            return true;
        } else {
            Yii::error("Ecom returns error code");
            return false;
        }
    }

    public function archiveFiles()
    {
        Yii::$app->db->createCommand()->delete('edi_files_queue', 'updated_at <= DATE_SUB(CURDATE(),INTERVAL 30 DAY) AND updated_at IS NOT NULL')->execute();
    }

    /**
     * check gln code for organization and check orderId if file dont have pricat prefix
     *
     * @var string  $content
     * @var integer $fileId
     * @var string  $fileName
     * @return boolean
     */
    private function checkOrgIdAndOrderId($content, $fileId, $fileName)
    {
        $supplier = $this->getStringBetweenTags($content, '<SUPPLIER>', '</SUPPLIER>');
        $updateResult = $this->addOrgIdToFile($fileId, $supplier);
        if (!$updateResult) {
            $this->updateQueue($fileId, self::STATUS_ERROR, 'Dont find organization with gln = ' . $supplier);
            return false;
        }
        if (strpos($fileName, 'pricat') !== 0) {
            $orderNumber = $this->getStringBetweenTags($content, '<ORDERNUMBER>', '</ORDERNUMBER>');
            if (is_numeric($orderNumber)) {
                $order = Order::findOne(['id' => $orderNumber]);
                if (is_null($order) || !$order) {
                    $this->updateQueue($fileId, self::STATUS_ERROR, 'Dont find order with id = ' . $orderNumber);
                    return false;
                }
            } else {
                $this->updateQueue($fileId, self::STATUS_ERROR, 'Number dont numeric with id = ' . $orderNumber);
                return false;
            }
        }

        return true;
    }

    /**
     * Return string between $startTag and $endTag
     *
     * @var string $startTag
     * @var string $endTag
     * @return string
     */
    private function getStringBetweenTags($string, $startTag, $endTag)
    {
        $start = strpos($string, $startTag) + strlen($startTag);
        $end = strpos($string, $endTag);
        if (!$start || !$end) {
            return false;
        }
        return substr($string, $start, $end - $start);
    }
}