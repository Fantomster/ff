<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\edi\realization;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\WaybillHelper;
use common\components\ecom\AbstractRealization;
use common\components\ecom\RealizationInterface;
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
use yii\db\Exception;
use yii\db\Expression;

/**
 * Class Realization
 *
 * @package common\components\edi\realization
 */
class Realization extends AbstractRealization implements RealizationInterface
{
    /**
     * @var \SimpleXMLElement
     */
    public $xml;

    /**
     * @param        $client
     * @param String $fileName
     * @param String $login
     * @param String $pass
     * @param int    $fileId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function getDoc($client, String $fileName, String $login, String $pass, int $fileId): bool
    {
        $this->fileId = $fileId;
        try {
            $this->updateQueue(self::STATUS_PROCESSING, '');
            try {
                $doc = $client->getDoc(['user' => ['login' => $login, 'pass' => $pass], 'fileName' => $fileName]);
            } catch (\Throwable $e) {
                $this->updateQueue(self::STATUS_ERROR, $e->getMessage());
                return false;
            }

            if (!isset($doc->result->content)) {
                $this->updateQueue(self::STATUS_ERROR, 'No such file');
                return false;
            }

            if (!$this->checkOrgIdAndOrderId($doc->result->content, $fileName)) {
                return false;
            }

            $content = $doc->result->content;
            $dom = new \DOMDocument();
            $dom->loadXML($content);
            $this->xml = simplexml_import_dom($dom);
            $this->xml->addChild('filename', $fileName);

            $success = false;
            $this->fileType = substr($fileName, 0, 6);

            if ($this->fileType == 'pricat') {
                $success = $this->handlePriceListUpdating();
            } elseif ($this->fileType == 'desadv' || $this->fileType == 'ordrsp') {
                $success = $this->handleOrderResponse();
            } elseif ($this->fileType == 'alcdes') {
                $success = $this->handleOrderResponse(true);
            }

            if ($success) {
                $client->archiveDoc(['user' => ['login' => \Yii::$app->params['e_com']['login'], 'pass' => \Yii::$app->params['e_com']['pass']], 'fileName' => $fileName]);
                $this->updateQueue(self::STATUS_HANDLED, '');
            } else {
                $this->updateQueue(self::STATUS_ERROR, 'Error handling file 1');
            }
        } catch (\Throwable $t) {
            $this->updateQueue(self::STATUS_ERROR, $t->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @param bool $isAlcohol
     * @return bool
     * @throws \Throwable
     * @throws \api_web\exceptions\ValidationException
     * @throws \yii\db\Exception
     */
    private function handleOrderResponse(bool $isAlcohol = false): bool
    {
        $orderID = $this->xml->NUMBER;
        $supplier = $this->xml->HEAD->SUPPLIER;
        $message = "";
        $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplier]);
        if (!$ediOrganization) {
            throw new Exception('Dont find any edi org with gln' . $supplier);
        }
        $order = Order::findOne(['id' => $orderID, 'vendor_id' => $ediOrganization->organization_id]);
        if (!$order) {
            throw new Exception('No such order ID: ' . $orderID);
        }

        \Yii::$app->language = $order->edi_order->lang ?? 'ru';
        $user = User::findOne(['id' => $order->created_by_id]);

        $positions = $this->xml->HEAD->POSITION;
        if (!count($positions)) {
            $positions = $this->xml->HEAD->PACKINGSEQUENCE->POSITION;
        }
        $arOrderContentBarCodes = [];
        foreach ($order->orderContent as $orderContent) {
            $arOrderContentBarCodes[$orderContent->product->barcode] = $orderContent;
        }

        $barcodeArray = [];
        $totalQuantity = 0;
        $totalPrice = 0;
        $sum = 0;
        $arUploadedContents = [];

        foreach ($positions as $position) {
            $contID = (int)($position->PRODUCTIDBUYER ?? $position->PRODUCT);
            $quantity = (float)($position->DELIVEREDQUANTITY ?? $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY);
            $price = (float)($position->PRICEWITHVAT ?? $position->PRICE);
            $barcode = (int)$position->PRODUCT;
            $barcodeArray[] = $barcode;
            $taxRate = (float)($position->TAXRATE ?? null);
            $priceWithVat = (float)($position->PRICEWITHVAT ?? $taxRate ? $position->PRICE + ($position->PRICE *
                        ($taxRate / 100)) : $price);
            $priceWithoutVat = $taxRate ? $priceWithVat / (1 + ($taxRate / 100)) : $price;
            if ($price > 0 && (float)$position->PRICEWITHVAT == (float)$position->PRICE && $position->TAXRATE > 0) {
                $price = $priceWithoutVat;
            }
            $good = CatalogBaseGoods::findOne(['barcode' => $barcode]);

            if (array_key_exists($barcode, $arOrderContentBarCodes)) {
                $ordCont = $arOrderContentBarCodes[$barcode];
            } else {
                if (!$good) {
                    continue;
                }
                $ordCont = new OrderContent();
                $ordCont->order_id = $order->id;
                $ordCont->product_id = $good->id;
                $ordCont->quantity = $quantity; //TODOO: wtf convert to decimal? *Speaking
                $ordCont->price = $price;
                $ordCont->initial_quantity = $quantity;
                $ordCont->product_name = $good->product;
                $ordCont->plan_quantity = $quantity;
                $ordCont->plan_price = $price;
                $ordCont->units = $good->units;
                $ordCont->vat_product = $taxRate;
                $ordCont->edi_number = $position->DELIVERYNOTENUMBER ?? $position->WAYBILLNUMBER ?? $orderID;
                $ordCont->merc_uuid = $position->VETIS->VETID ?? null;
                if (!$ordCont->save()) {
                    throw new ValidationException([], $ordCont->getErrorSummary(true));
                }
                $message .= \Yii::t('message', 'frontend.controllers.order.add_position', ['ru' => "Добавил товар {prod}", 'prod' => $good->product]);
            }
            if ($ordCont->quantity != $quantity) {
                $message .= \Yii::t('message', 'frontend.controllers.order.change',
                        ['ru'      => "<br/>изменил количество {prod} с {oldQuan} {ed} на ",
                         'prod'    => $ordCont->product_name,
                         'oldQuan' => $ordCont->quantity,
                         'ed'      => $good->ed]
                    ) . " $quantity" . $good->ed;
            }
            if ($ordCont->price != $price) {
                $message .= \Yii::t('message', 'frontend.controllers.order.change_price',
                        ['ru'             => "<br/>изменил цену {prod} с {productPrice} руб на ",
                         'prod'           => $ordCont->product_name,
                         'productPrice'   => $ordCont->price,
                         'currencySymbol' => $order->currency->iso_code]
                    ) . $price . " руб";

            }
            $ordCont->quantity = $quantity;
            $ordCont->price = $price;
            $ordCont->vat_product = $taxRate;
            $ordCont->edi_number = $position->DELIVERYNOTENUMBER ?? $position->WAYBILLNUMBER ?? $orderID;
            $ordCont->merc_uuid = $position->VETIS->VETID ?? null;
            if (in_array($this->fileType, ['desadv', 'alcdes'])) {
                $prop = 'edi_' . $this->fileType;
                $ordCont->{$prop} = $this->xml->filename;
            }
            if (!$ordCont->save()) {
                throw new ValidationException([], $ordCont->getErrorSummary(true));
            }

            $docType = $isAlcohol ? EdiOrderContent::ALCDES : EdiOrderContent::DESADV;
            $ediOrderContent = EdiOrderContent::findOne(['order_content_id' => $ordCont->id]);
            if (!$ediOrderContent) {
                $ediOrderContent = new EdiOrderContent();
            }
            $ediOrderContent->doc_type = $docType;
            $ediOrderContent->pricewithvat = $priceWithVat;
            $ediOrderContent->taxrate = $taxRate;
            $ediOrderContent->uuid = $position->UUID ?? null;
            $ediOrderContent->gtin = $position->GTIN ?? null;
            $ediOrderContent->waybill_date = $position->WAYBILLDATE ?? null;
            $ediOrderContent->waybill_number = $position->WAYBILLNUMBER ?? null;
            $ediOrderContent->delivery_note_date = $position->DELIVERYNOTEDATE ?? null;
            $ediOrderContent->delivery_note_number = $position->DELIVERYNOTENUMBER ?? null;
            if (!$ediOrderContent->save()) {
                throw new ValidationException([], $ediOrderContent->getErrorSummary(true));
            }
            $totalQuantity += $quantity;
            $totalPrice += $price;
            $sum += $quantity * $price;
            $arUploadedContents[$ordCont->id] = $ordCont;
        }

        if ($totalQuantity <= 0.00 || $totalPrice <= 0.00) {
            OrderController::sendOrderCanceled($order->client, $order);
            $message .= \Yii::t('message', 'frontend.controllers.order.cancelled_order_six', ['ru' => "Заказ № {order_id} отменен!", 'order_id' => $order->id]);
            OrderController::sendSystemMessage($user, $order->id, $message);
            $order->status = OrderStatus::STATUS_REJECTED;
            $order->save();
            return true;
        }

        $order->status = OrderStatus::STATUS_PROCESSING;
        $order->total_price = $sum; //TODOO:wtf decimal
        $order->service_id = Registry::EDI_SERVICE_ID;
        if ($this->fileType == 'ordrsp') {
            $order->edi_ordersp = $this->xml->filename;
        }
        $order->save();
        $ediOrder = EdiOrder::findOne(['order_id' => $order->id]);
        if ($ediOrder) {
            $ediOrder->invoice_number = $this->xml->DELIVERYNOTENUMBER ?? '';
            $ediOrder->invoice_date = $this->xml->DELIVERYNOTEDATE ?? '';
            $ediOrder->save();
        }

        $createWaybill = (new WaybillHelper())->createWaybill($order->id, $arUploadedContents,
            $ediOrganization->organization_id);

        if ($message != '') {
            OrderController::sendSystemMessage($user, $order->id, $order->vendor->name . \Yii::t('message', 'frontend.controllers.order.change_details_two', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
        }

        $systemMessage = $order->vendor->name . \Yii::t('message', 'frontend.controllers.order.confirm_order_two', ['ru' => ' подтвердил заказ!']);
        OrderController::sendSystemMessage($user, $order->id, $systemMessage);

        OrderController::sendOrderProcessing($order->client, $order);
        return true;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    private function handlePriceListUpdating(): bool
    {
        $supplierGLN = $this->xml->SUPPLIER;
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
        $currency = Currency::findOne(['iso_code' => $this->xml->CURRENCY]);
        $baseCatalog->currency_id = $currency->id ?? 1;
        $baseCatalog->updated_at = new Expression('NOW()');
        $baseCatalog->save();
        $goods = $this->xml->CATALOGUE->POSITION;
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

        $buyerGLN = $this->xml->BUYER;
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