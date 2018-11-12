<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\edi\realization;

use common\components\edi\AbstractRealization;
use common\components\edi\EDIClass;
use common\components\edi\RealizationInterface;
use common\models\EdiFilesQueue;
use common\models\CatalogBaseGoods;
use common\models\EdiOrder;
use common\models\edi\EdiOrganization;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use common\models\User;
use frontend\controllers\OrderController;
use yii\base\Exception;
use Yii;
use yii\db\Expression;

/**
 * Class Realization
 *
 * @package common\components\edi\realization
 */
class LeradataRealization extends AbstractRealization implements RealizationInterface
{
    /**
     * @var \SimpleXMLElement
     */
    public $xml;
    private $edi;
    public $fileName;

    public function __construct()
    {
        $this->edi = new EDIClass();
        $this->edi->fileName = $this->fileName;
    }

    public function parseFile($content, $providerID)
    {
        return $this->edi->parseFile($content, $providerID);
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function handlePriceListUpdating($key, $xml, $providerID): bool
    {
        return $this->edi->handlePriceListUpdating($xml, $providerID);
    }

    protected function insertGood(int $catID, int $catalogBaseGoodID, float $price): bool
    {
        return $this->edi->insertGood($catID, $catalogBaseGoodID, $price);
    }

    public function handleOrderResponse($simpleXMLElement, $documentType, $isAlcohol = false, $exceptionArray, $providerID)
    {
        try {
            $simpleXMLElement = json_decode(json_encode($simpleXMLElement, JSON_UNESCAPED_UNICODE));
            $orderID = $simpleXMLElement->NUMBER;
            $head = $simpleXMLElement->HEAD[0];
            $supplier = $head->BUYER;
            $ediOrganization = EdiOrganization::findOne(['gln_code' => $supplier, 'provider_id' => $providerID]);
            if (!$ediOrganization) {
                throw new Exception('no EDI organization found');
            }
            $order = Order::findOne(['id' => $orderID, 'client_id' => $ediOrganization->organization_id]);

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
                $seq = $head->PACKINGSEQUENCE[0];
                $positions = $seq->POSITION;
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
                $arr[$contID]['PRICE'] = (float)$position->PRICE ?? 0;
                $arr[$contID]['PRICEWITHVAT'] = (float)$position->PRICEWITHVAT ?? 0.00;
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
                            if ($good->ed) {
                                $measure = $good->ed;
                            } else {
                                $measure = '';
                            }
                            $message .= Yii::t('message', 'frontend.controllers.order.change', ['ru' => "<br/>изменил количество {prod} с {oldQuan} {ed} на ", 'prod' => $ordCont->product_name, 'oldQuan' => $oldQuantity, 'ed' => $measure]) . " $newQuantity " . $measure;
                        }
                    }

                    $oldPrice = (float)$ordCont->price;
                    $newPrice = (float)$arr[$index]['PRICE'];
                    if ($oldPrice != $newPrice) {
                        if ($newPrice == 0) {
                            $ordCont->delete();
                            $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $orderContent->product_name]);
                        } else {
                            $change = " <br/>" . Yii::t('message', 'frontend.controllers.order.change_price', ['ru' => "<br/>изменил цену {prod} с {productPrice} руб на ", 'prod' => $orderContent->product_name, 'productPrice' => $oldPrice, 'currencySymbol' => $order->currency->iso_code]) . " " . $newPrice . " руб";
                            $message .= $change;
                        }
                    }
                    $summ += $newQuantity * $newPrice;
                    Yii::$app->db->createCommand()->update('order_content', ['price' => $newPrice, 'quantity' => $newQuantity, 'updated_at' => new Expression('NOW()')], 'id=' . $ordCont->id)->execute();

                    $orderContent->vat_product = $arr[$index]['TAXRATE'] ?? 0.00;
                    $orderContent->edi_number = $simpleXMLElement->DELIVERYNOTENUMBER ?? null;
                    $orderContent->edi_shipment_quantity = $arr[$index]['DELIVEREDQUANTITY'] ?? $arr[$index]['ACCEPTEDQUANTITY'] ?? $orderContent->quantity;
                    $orderContent->merc_uuid = $arr[$index]['UUID'] ?? null;
                    if ($documentType == 2) {
                        $orderContent->edi_desadv = $exceptionArray['file_id'];
                    }
                    if ($documentType == 3) {
                        $orderContent->edi_alcdes = $exceptionArray['file_id'];
                    }

                    if (!$orderContent->save()) {
                        throw new Exception('Error saving order content');
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
                if (!$ediOrder->save()) {
                    throw new Exception('Error saving edi order');
                }
            }
            $order->waybill_number = $simpleXMLElement->DELIVERYNOTENUMBER ?? '';
            $order->edi_ordersp = $exceptionArray['file_id'];
            $order->service_id = 6;
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
        } catch (Exception $e) {
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
            $this->edi->insertEdiErrorData($arr);
        }
    }

    public function getSendingOrderContent($order, $done, $dateArray, $orderContent)
    {
        return $this->edi->getSendingOrderContent($order, $done, $dateArray, $orderContent);
    }

    /**
     * @return array
     */
    public function getFileList(): array
    {
        return $this->edi->getFileList();
    }

    public function array_to_object($array)
    {
        $obj = new \stdClass();
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }
}