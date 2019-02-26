<?php

namespace common\components\edi;

use api_web\components\notice_class\OrderNotice;
use api_web\components\Registry;
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
use yii\web\NotFoundHttpException;

/**
 * Class EDIClass
 *
 * @package common\components\edi
 */
class EDIClass extends Component
{
    const EDI_ORDERSP_GOOD_DELIVERY_WITHOUT_CHANGES = 1;
    const EDI_ORDERSP_GOOD_DELIVERY_WITH_CHANGED_QUANTITY = 2;
    const EDI_ORDERSP_GOOD_NO_DELIVERY = 3;

    public $ediDocumentType;
    public $fileName;

    /**
     * @param $content
     * @param $providerID
     * @return bool|string
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
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

    /**
     * @param       $simpleXMLElement
     * @param       $documentType
     * @param       $providerID
     * @param bool  $isAlcohol
     * @param bool  $isLeraData
     * @param array $exceptionArray
     * @return bool|string
     * @throws \Throwable
     */
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
                throw new Exception(Yii::t('error', 'common.edi.organization.not.found', ['ru' => 'Такой организации в EDI не существует.']));
            }
            $organization = Organization::findOne(['id' => $ediOrganization->organization_id]);
            if (!$organization) {
                throw new Exception(Yii::t('error', 'common.organization.not.found', ['ru' => 'Такой организации не существует.']));
            }

            if ($isLeraData) {
                $order = Order::findOne(['id' => $orderID, 'client_id' => $ediOrganization->organization_id]);
                $organization = Organization::findOne(['id' => $order->vendor_id]);
            } else {
                $order = Order::findOne(['id' => $orderID, 'vendor_id' => $ediOrganization->organization_id]);
            }
            if (!$order) {
                throw new Exception(Yii::t('error', 'common.order.not.found', ['ru' => 'Такого заказа не существует.']));
            }

            \Yii::$app->language = $order->ediOrder->lang ?? 'ru';
            $user = User::findOne(['id' => $order->created_by_id]);
            if (!$user) {
                throw new Exception(Yii::t('error', 'common.user.not.found', ['ru' => 'Такого пользователя не существует.']));
            }

            $positions = $simpleXMLElement->xpath('/ORDRSP/HEAD/POSITION') ?? null;
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
                $arr[$contID]['PRODUCTTYPE'] = (int)$position->PRODUCTTYPE ?? self::EDI_ORDERSP_GOOD_DELIVERY_WITH_CHANGED_QUANTITY;
                if ($position->PRODUCTTYPE == self::EDI_ORDERSP_GOOD_NO_DELIVERY) {
                    $arr[$contID]['ACCEPTEDQUANTITY'] = 0.00;
                }
                $totalQuantity += $arr[$contID]['ACCEPTEDQUANTITY'];
                $totalPrice += $arr[$contID]['PRICE'];
            }
            if ($totalQuantity == 0.00 || $totalPrice == 0.00) {
                $ordNotice->cancelOrder($user, $organization, $order);
                $order->status = OrderStatus::STATUS_REJECTED;
                if (!$order->save()) {
                    throw new Exception(Yii::t('error', 'common.order.not.saving', ['ru' => 'Заказ сохранить не удалось.']));
                }
                return true;
            }

            $summ = 0;
            $orderContentArr = [];
            $isPositionChanged = false;
            foreach ($order->orderContent as $orderContent) {
                $index = $orderContent->id;
                if (!isset($arr[$index])) continue;
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
                    throw new Exception(Yii::t('error', 'common.order.content.not.saving', ['ru' => 'Товарную позицию заказа сохранить не удалось.']));
                }
            }

            foreach ($positions as $position) { // цикл для каждой товарной позиции
                if ($position->PRODUCTTYPE == self::EDI_ORDERSP_GOOD_NO_DELIVERY) continue;
                $quantity = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY; // Определение количества товара
                if (!$quantity || $quantity == 0.000 || $position->PRICE == 0.00) continue; // Если количество не задано, равно нулю или нулевая цена, то цикл продолжаем со следующего товара
                $contID = (int)$position->PRODUCTIDBUYER; // Артикул товара в базе Микскарта
                if (!$contID) {
                    $contID = (int)$position->PRODUCT; // Если такого товара в базе Микскарта нет, то берётся артикул в базе поставщика
                }
                if (!$contID) continue; // Если артикула не существует, то цикл продолжаем со следующего товара
                $barcode = $position->PRODUCT; // Определяем бар-код
                if (!in_array($contID, $orderContentArr) && !in_array($barcode, $barcodeArray)) { // Если позиции нет в массиве позиций заказа и бар-кода нет в массиве бар-кодов
                    $good = CatalogBaseGoods::findOne(['barcode' => $position->PRODUCT, 'supp_org_id' => $order->vendor_id]); // Находим товар в CatalogBaseGoods по баркоду
                    if (!$good) { // Если такого товара в CatalogBaseGoods нет, то
                        $rel = RelationSuppRest::findOne(['supp_org_id' => $order->vendor_id, 'rest_org_id' => $order->client_id]);
                        if (empty($rel)) { // Если зависимости поставщика с клиентом не существует, то выдаём об этом сообщение
                            throw new Exception("Not found RelationSuppRest: supp_org_id = {$order->vendor_id} AND rest_org_id = {$order->client_id}");
                        }
                        $good = new CatalogBaseGoods(); // Создаём новый товар в CatalogBaseGoods
                        $good->cat_id = $rel->cat_id; // назначенный каталог берём из таблицы связей поставщика и клиента
                        $good->article = $position->PRODUCTIDSUPPLIER; // Артикул устанавливаем из полученного поля "Артикул в базе поставщика"
                        $good->product = $position->DESCRIPTION; // Название продукта берём из полученного поля "Описание продукта"
                        $good->status = CatalogBaseGoods::STATUS_ON; // Устанавливаем статус товара "Активен"
                        $good->supp_org_id = $organization->id; // Устанавливаем идентификатор организации-поставщика
                        $good->price = $position->PRICE; // Цену продукта устанавливаем из полученного поля "Цена товара"
                        $good->units = 0; // Количество единиц товара в товарной упаковке устанавливаем нулевым
                        $good->ed = ''; // Название единицы измерения товара устанавливаем ''
                        $good->category_id = null; // Идентификатор категории товаров устанавливаем неопределённым
                        $good->barcode = $barcode; // Штрих-код товара в Market Place устанавливаем из полученного поля "Бар-код"
                        $good->edi_supplier_article = $barcode; // Артикул товара для EDI устанавливаем из полученного поля "Бар-код"
                        if (!$good->save()) { // Остальные позиции устанавливаем по умолчанию и сохраняем
                            \Yii::error('Не удалось сохранить запись в таблице catalog_base_goods, скорее всего, из-за валидации');
                        }
                    };
                    if ($isDesadv) { // Бесполезная развилка, т.к. isDesadv = true только, если count($positions) не существует
                        $quan = $position->DELIVEREDQUANTITY ?? $position->ORDEREDQUANTITY; // Количество товара в заказе равно значению из полученного поля "Поставленное количество" (никогда не выполняется)
                    } else {
                        $quan = $position->ACCEPTEDQUANTITY ?? $position->ORDEREDQUANTITY; // Количество товара в заказе равно значению из полученного поля "Принятое количество"
                    }
                    $quan = (float)$quan; // Переменная, отвечающая за количество товара в таблице заказов, приводится к типу числа с плавающей точкой
                    $price = (float)$position->PRICE; // Переменная, отвечающая за цену товара в таблице заказов, приводится к типу числа с плавающей точкой из значения полученного поля "Цена"
                    $newOrderContent = new OrderContent(); // Создаётся новая товарная позиция в заказе
                    $newOrderContent->order_id = $order->id; // Устанавливаем номер заказа в новой товарной позиции
                    $newOrderContent->product_id = $good->id; // Устанавливаем идентификатор продукта, который мы недавно создали в таблице catalog_base_goods
                    $newOrderContent->quantity = $quan; // Устанавливаем количество товара
                    $newOrderContent->price = $price; // Устанавливаем цену товара
                    $newOrderContent->initial_quantity = $quan; // Устанавливаем первоначальное количество товара
                    $newOrderContent->product_name = $good->product; // Устанавливаем наименование товарной позиции
                    $newOrderContent->plan_quantity = $quan; // Устанавливаем изменённое количество товара
                    $newOrderContent->plan_price = $price; // Устанавливаем изменённую цену товара
                    $newOrderContent->units = $good->units; // Устанавливаем единицу измерения товароа
                    $newOrderContent->vat_product = $position->VAT ?? 0.00; // Устанавливаем ставку НДС (если есть значение в полученном поле "Налог", то его, если нет, то 0.00 ???)
                    $newOrderContent->article = $good->article; // Устанавливаем артикул товара
                    $changed[] = $newOrderContent; // Записываем новую товарную позицию в заказе в массив изменений
                    if (!$newOrderContent->save()) { // Пытаемся сохранить в заказе новую товарную позицию, если не удаётся, выдаём сообщение
                        \Yii::error('Не удалось сохранить запись в таблице order_content, скорее всего, из-за валидации');
                    }
                    $isPositionChanged = true; // Переменной, отвечающей за изменение позиции, присваиваем true
                    $total = $quan * $price; // Переменной, отвечающей за сумму товарной позиции присваиваем значение, полученное из перемножения количества и цены товара
                    $summ += $total; // К общей сумме прибавляем сумму данной товарной позиции
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
                throw new NotFoundHttpException(Yii::t('error', 'common.order.not.saving', ['ru' => 'Заказ сохранить не удалось.']));
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
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return $e->getMessage();
        }
    }

    /**
     * @param $xml
     * @param $providerID
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
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
            if (!$barcode) {
                continue;
            }
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
                $catalogBaseGood->barcode = (String)$barcode;
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

    /**
     * @param int      $catID
     * @param int      $catalogBaseGoodID
     * @param float    $price
     * @param int|null $vat
     * @return bool
     */
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

    /**
     * @param Organization $organization
     * @param              $currency
     * @param Organization $rest
     * @return int
     */
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

    /**
     * @param Order $order
     * @param       $done
     * @param       $dateArray
     * @param       $orderContent
     * @return bool|string
     */
    public function getSendingOrderContent(Order $order, $done, $dateArray, $orderContent)
    {
        $vendor = $order->vendor;
        $client = $order->client;
        if (Yii::$app instanceof \yii\console\Application) {
            $controller = new Controller("", null);
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

    /**
     * @param $arr
     * @throws \yii\db\Exception
     */
    public function insertEdiErrorData($arr): void
    {
        Yii::$app->db->createCommand()->insert(EdiFilesQueue::tableName(), $arr)->execute();
    }

    /**
     * @param $position
     * @return array
     */
    private function fillArrayData($position)
    {
        $arr = [
            'DELIVEREDQUANTITY'  => (isset($position->DELIVEREDQUANTITY)) ? (float)$position->DELIVEREDQUANTITY : 0.00,
            'PRICE'              => (float)$position->PRICE[0] ?? (float)$position->PRICE ?? 0,
            'PRICEWITHVAT'       => (isset($position->PRICEWITHVAT)) ? (float)$position->PRICEWITHVAT : 0.00,
            'TAXRATE'            => (isset($position->TAXRATE)) ? (int)$position->TAXRATE : 0,
            'BARCODE'            => $position->PRODUCT,
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

    /**
     * @param        $organizationID
     * @param null   $response
     * @param string $type
     * @param null   $userID
     */
    public static function writeEdiDataToJournal($organizationID, $response = null, $type = 'success', $userID = null)
    {
        $userID = $userID ?? Yii::$app->user->id ?? null;
        $organizationID = $organizationID ?? null;
        if ($userID && is_null($organizationID)) {
            $user = User::findOne($userID);
            if ($user) {
                $organizationID = $user->organization_id;
            }
        }

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
