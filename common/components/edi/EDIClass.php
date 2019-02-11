<?php

namespace common\components\edi;

use api_web\components\notice_class\OrderNotice;
use api_web\components\Registry;
use common\models\AllService;
use common\models\edi\EdiFilesQueue;
use common\models\Journal;
use common\models\OuterUnit;
use common\models\RelationUserOrganization;
use common\models\Role;
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
            $organization = Organization::findOne(['id' => $ediOrganization->organization_id]);
            if (!$organization) {
                throw new Exception('no organization found');
            }

            if ($isLeraData) {
                $order = Order::findOne(['id' => $orderID, 'client_id' => $ediOrganization->organization_id]);
                $organization = Organization::findOne(['id' => $order->vendor_id]);
            } else {
                $order = Order::findOne(['id' => $orderID, 'vendor_id' => $ediOrganization->organization_id]);
            }
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
            $changed = [];
            $deleted = [];
            $ordNotice = new OrderNotice();

            foreach ($positions as $position) {
                if (!isset($position->PRODUCT)) continue;
                $productIDBuyer = (int)$position->PRODUCTIDBUYER;
                $productOrderContent = OrderContent::findOne(['order_id' => $order->id, 'product_id' => $productIDBuyer]);
                if (!$productOrderContent) continue;
                $contID = $productOrderContent->id;
                $positionsArray[] = (int)$contID;
                $arr[$contID] = $this->fillArrayData($position);
                if ($isDesadv) {
                    $arr[$contID]['ACCEPTEDQUANTITY'] = (float)$position->DELIVEREDQUANTITY ?? (float)$position->ORDEREDQUANTITY;
                } else {
                    $arr[$contID]['ACCEPTEDQUANTITY'] = (float)$position->ACCEPTEDQUANTITY ?? (float)$position->ORDEREDQUANTITY;
                }
                $totalQuantity += $arr[$contID]['ACCEPTEDQUANTITY'];
                $totalPrice += $arr[$contID]['PRICE'];
            }
            if ($totalQuantity == 0.00 || $totalPrice == 0.00) {
                $ordNotice->cancelOrder($user, $organization, $order);
                $order->status = OrderStatus::STATUS_REJECTED;
                if (!$order->save()) {
                    throw new Exception('Error saving order');
                }
                return true;
            }

            $summ = 0;
            $orderContentArr = [];
            $isPositionChanged = false;
            foreach ($order->orderContent as $orderContent) {
                $index = $orderContent->id;
                $orderContentArr[] = $orderContent->id;
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

                $orderContent->setOldAttributes($orderContent->attributes);
                if ($oldQuantity != $newQuantity) {
                    if (!$newQuantity || $newQuantity == 0.000) {
                        $deleted[] = $orderContent;
                        $orderContent->delete();
                        continue;
                    }
                }
                $newPrice = (float)$arr[$index]['PRICE'];
                if ($orderContent->price != $newPrice || $orderContent->quantity != $newQuantity) {
                    $isPositionChanged = true;
                }
                $summ += $newQuantity * $newPrice;
                $orderContent->price = $newPrice;
                $orderContent->quantity = $newQuantity;
                $orderContent->vat_product = isset($arr[$index]['TAXRATE']) ? (int)$arr[$index]['TAXRATE'] : 0;
                $orderContent->into_quantity = isset($arr[$index]['DELIVEREDQUANTITY']) ? $arr[$index]['DELIVEREDQUANTITY'] : null;
                $orderContent->into_price = $newPrice;
                $orderContent->into_price_vat = isset($arr[$index]['PRICEWITHVAT']) ? $arr[$index]['PRICEWITHVAT'] : null;
                $orderContent->into_price_sum = isset($arr[$index]['AMOUNT']) ? $arr[$index]['AMOUNT'] : null;
                $orderContent->into_price_sum_vat = isset($arr[$index]['AMOUNTWITHVAT']) ? $arr[$index]['AMOUNTWITHVAT'] : null;
                $orderContent->edi_number = $simpleXMLElement->DELIVERYNOTENUMBER ?? null;
                $orderContent->merc_uuid = $arr[$index]['UUID'] ?? null;
                if ($documentType == 1) {
                    $orderContent->edi_recadv = $this->fileName;
                }
                if ($documentType == 2) {
                    $orderContent->edi_desadv = $this->fileName;
                }
                if ($documentType == 3) {
                    $orderContent->edi_alcdes = $this->fileName;
                }
                $clone = clone $orderContent;
                $changed[] = $clone;
                if (!$orderContent->save()) {
                    throw new Exception('Error saving order content');
                }
            }

            $rel = RelationSuppRest::findOne(['supp_org_id' => $order->vendor_id, 'rest_org_id' => $ediOrganization->organization_id]);

            if (empty($rel)) {
                throw new Exception("Not found RelationSuppRest: supp_org_id = {$order->vendor_id} AND rest_org_id = {$ediOrganization->organization_id}");
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
                    if (!$good) {
                        $good = new CatalogBaseGoods();
                        $good->cat_id = $rel->cat_id;
                        $good->article = $position->PRODUCTIDSUPPLIER;
                        $good->product = $position->DESCRIPTION;
                        $good->status = CatalogBaseGoods::STATUS_ON;
                        $good->supp_org_id = $organization->id;
                        $good->price = $position->PRICE;
                        $good->units = 0;
                        $good->ed = 0;
                        $good->category_id = null;
                        $good->barcode = $barcode;
                        $good->edi_supplier_article = $barcode;
                        $good->save();
                    };
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
                    $newOrderContent->vat_product = $position->VAT ?? 0.00;
                    $changed[] = $newOrderContent;
                    if (!$newOrderContent->save()) {
                        throw new Exception('Error saving order content');
                    }
                    $isPositionChanged = true;
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
            $order->waybill_number = (int)$simpleXMLElement->DELIVERYNOTENUMBER ?? (int)$simpleXMLElement->NUMBER ?? '';
            $order->edi_ordersp = $this->ediDocumentType;
            $order->service_id = Registry::EDI_SERVICE_ID;
            $order->edi_ordersp = $this->fileName ?? $order->id;
            $order->edi_doc_date = $simpleXMLElement->DELIVERYNOTEDATE ?? null;
            $deliveryDate = isset($simpleXMLElement->DELIVERYDATE) ? \Yii::$app->formatter->asDate($simpleXMLElement->DELIVERYDATE, 'yyyy.MM.dd HH:mm:ss') : null;
            $order->actual_delivery = $deliveryDate;
            $order->ediProcessor = 1;
            $managerAssociate = $organization->getAssociatedManagers($organization->id, true);
            $acceptedByID = 1;
            if ($managerAssociate) {
                $acceptedByID = $managerAssociate->id;
            } else {
                $relUserOrg = RelationUserOrganization::findOne(['organization_id' => $organization->id, 'is_active' => true, 'role_id' => [Role::ROLE_ADMIN, Role::ROLE_RESTAURANT_MANAGER, Role::ROLE_SUPPLIER_MANAGER, Role::ROLE_SUPPLIER_EMPLOYEE, Role::ROLE_RESTAURANT_EMPLOYEE]]);
                if ($relUserOrg) {
                    $acceptedByID = $relUserOrg->user_id;
                }
            }
            $order->accepted_by_id = $acceptedByID;
            if (!$order->save()) {
                throw new Exception('Error saving order');
            }

            if ($isPositionChanged) {
                $ordNotice->sendOrderChange($organization, $order, $changed, $deleted);
            } else {
                $ordNotice->processingOrder($order, $user, $organization, $isDesadv);
            }

            $action = ($isDesadv) ? " " . Yii::t('app', 'отправил заказ!') : Yii::t('message', 'frontend.controllers.order.confirm_order_two', ['ru' => ' подтвердил заказ!']);
            $systemMessage = $order->vendor->name . '' . $action;
            OrderController::sendSystemMessage($user, $order->id, $systemMessage);
            self::writeEdiDataToJournal($order->client_id, Yii::t('app', 'По заказу {order} получен файл {file}', ['order' => $order->id, 'file' => $this->fileName]), 'success', $user->id);
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
            return $e->getMessage();
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

        if ($ediOrganization->pricat_action_attribute_rule == Registry::EDI_PRICAT_ACTION_RULE_DELETE_NOT_EXISTS) {
            $isFollowActionRule = false;
        } else {
            $isFollowActionRule = true;
        }

        $organization = Organization::findOne(['id' => $ediOrganization->organization_id]);

        if (!$organization || $organization->type_id != Organization::TYPE_SUPPLIER) {
            \Yii::error('No such organization');
            return false;
        }
        $ediRest = EdiOrganization::findOne(['gln_code' => $buyerGLN, 'provider_id' => $providerID]);
        if (!$ediRest) {
            \Yii::error("No EDI organization(rest) org_id:{$organization->id}, gln_code: {$buyerGLN}, provider_id:$providerID");
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
            $goodsArray[$barcode] = [
                'ed'                   => $ed,
                'name'                 => (String)$good->PRODUCTNAME ?? '',
                'price'                => (float)$good->UNITPRICE ?? 0.0,
                'article'              => $barcode,
                'units'                => (float)$good->MINORDERQUANTITY ?? (float)$good->QUANTITYOFCUINTU ?? (float)$good->PACKINGMULTIPLENESS,
                'edi_supplier_article' => (isset($good->IDSUPPLIER) && $good->IDSUPPLIER != '') ? (String)$good->IDSUPPLIER : $barcode,
                'vat'                  => (int)$good->TAXRATE ?? null,
                'action'               => (isset($good->ACTION) && $good->ACTION > 0) ? (int)$good->ACTION : null,
            ];
        }
        $catalog_base_goods = (new \yii\db\Query())
            ->select(['id', 'barcode'])
            ->from(CatalogBaseGoods::tableName())
            ->where(['cat_id' => $baseCatalog->id])
            ->andWhere('barcode IS NOT NULL')
            ->all();
        foreach ($catalog_base_goods as $base_good) {
            if (!in_array($base_good['barcode'], $barcodeArray) && !$isFollowActionRule) {
                \Yii::$app->db->createCommand()->delete(CatalogGoods::tableName(), ['base_goods_id' => $base_good['id'], 'cat_id' => $relationCatalogID, 'service_id' => Registry::EDI_SERVICE_ID])->execute();
            }
        }

        foreach ($goodsArray as $barcode => $good) {
            $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
            if (!$catalogBaseGood && $good['action'] != Registry::EDI_PRICAT_ACTION_TYPE_DELETE) {
                $catalogBaseGood = new CatalogBaseGoods();
                $catalogBaseGood->cat_id = $baseCatalog->id;
                $catalogBaseGood->article = $good['article'];
                $catalogBaseGood->product = $good['name'];
                $catalogBaseGood->status = CatalogBaseGoods::STATUS_ON;
                $catalogBaseGood->supp_org_id = $organization->id;
                $catalogBaseGood->price = $good['price'];
                $catalogBaseGood->units = $good['units'];
                $catalogBaseGood->ed = ($good['ed'] == '') ? "кг" : $good['ed'];
                $catalogBaseGood->category_id = null;
                $catalogBaseGood->barcode = $barcode;
                $catalogBaseGood->edi_supplier_article = $good['edi_supplier_article'];
                $res = $catalogBaseGood->save();

                if (!$res) continue;
                $catalogBaseGood = CatalogBaseGoods::findOne(['cat_id' => $baseCatalog->id, 'barcode' => $barcode]);
                $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price'], $good['vat']);
                if (!$res2) continue;
            } else {
                $catalogGood = CatalogGoods::findOne(['cat_id' => $relationCatalogID, 'base_goods_id' => $catalogBaseGood->id]);
                if ($good['action'] == Registry::EDI_PRICAT_ACTION_TYPE_DELETE && $catalogGood && $catalogGood->service_id == Registry::EDI_SERVICE_ID) {
                    $catalogGood->delete();
                    continue;
                }
                if (!$isFollowActionRule
                    || $good['action'] == Registry::EDI_PRICAT_ACTION_TYPE_SECOND_UPDATE
                    || $good['action'] == Registry::EDI_PRICAT_ACTION_TYPE_FIRST_UPDATE) {
                    if (!$catalogGood) {
                        $res2 = $this->insertGood($relationCatalogID, $catalogBaseGood->id, $good['price'], $good['vat']);
                        if (!$res2) continue;
                    } else {
                        $catalogGood->price = $good['price'];
                        $catalogGood->service_id = Registry::EDI_SERVICE_ID;
                        $catalogGood->save();
                    }
                    $catalogBaseGood->units = $good['units'];
                    $catalogBaseGood->product = $good['name'];
                    $catalogBaseGood->article = $good['article'];
                    $catalogBaseGood->ed = $good['ed'];
                    $catalogBaseGood->edi_supplier_article = $good['edi_supplier_article'];
                }

                $catalogBaseGood->deleted = CatalogBaseGoods::DELETED_OFF;
                $catalogBaseGood->status = CatalogBaseGoods::STATUS_ON;
                if (!$catalogBaseGood->save()) continue;
            }
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
        $catalogGood->service_id = Registry::EDI_SERVICE_ID;
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
        $catalog->name = $rest->name . " " . date('d.m.Y');
        $catalog->status = Catalog::STATUS_ON;
        $catalog->currency_id = $currency->id ?? 1;
        $catalog->save();
        $catalogID = $catalog->id;
        return $catalogID;
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
        if (!$glnArray) {
            Yii::error('Empty GLN');
            return false;
        }
        $string = $controller->renderPartial($done ? '@common/views/e_com/order_done' : '@common/views/e_com/create_order', compact('order', 'glnArray', 'dateArray', 'orderContent'));
        return $string;
    }

    public function insertEdiErrorData($arr): void
    {
        Yii::$app->db->createCommand()->insert(EdiFilesQueue::tableName(), $arr)->execute();
    }

    private function fillArrayData($position)
    {
        $arr = [
            'DELIVEREDQUANTITY'  => (isset($position->DELIVEREDQUANTITY)) ? (float)$position->DELIVEREDQUANTITY : 0.00,
            'PRICE'              => (float)$position->PRICE[0] ?? (float)$position->PRICE ?? 0,
            'PRICEWITHVAT'       => (isset($position->PRICEWITHVAT)) ? (float)$position->PRICEWITHVAT : 0.00,
            'TAXRATE'            => (isset($position->TAXRATE)) ? (int)$position->TAXRATE : 0,
            'BARCODE'            => (int)$position->PRODUCT,
            'WAYBILLNUMBER'      => isset($position->WAYBILLNUMBER) ? $position->WAYBILLNUMBER : null,
            'WAYBILLDATE'        => isset($position->WAYBILLDATE) ? $position->WAYBILLDATE : null,
            'DELIVERYNOTENUMBER' => isset($position->DELIVERYNOTENUMBER) ? $position->DELIVERYNOTENUMBER : null,
            'DELIVERYNOTEDATE'   => isset($position->DELIVERYNOTEDATE) ? $position->DELIVERYNOTEDATE : null,
            'GTIN'               => isset($position->GTIN) ? $position->GTIN : null,
            'UUID'               => isset($position->UUID) ? $position->UUID : null,
            'AMOUNT'             => isset($position->AMOUNT) ? $position->AMOUNT : null,
            'AMOUNTWITHVAT'      => isset($position->AMOUNTWITHVAT) ? $position->AMOUNTWITHVAT : null,
        ];
        return $arr;
    }

    public static function writeEdiDataToJournal($organizationID, $response = null, $type = 'success', $userID = null)
    {
        $userID = $userID ?? Yii::$app->user->id ?? null;
        $organizationID = $organizationID ?? Yii::$app->user->identity->organization_id ?? null;
        $journal = new Journal();
        $journal->user_id = $userID;
        $journal->organization_id = $organizationID;
        $journal->service_id = Registry::EDI_SERVICE_ID;
        $journal->response = $response;
        $journal->type = $type;
        $journal->operation_code = '0';
        $journal->save();
    }
}
