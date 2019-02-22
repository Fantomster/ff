<?php

namespace api_web\modules\integration\models;

use api_web\components\Registry;
use api_web\helpers\WebApiHelper;
use common\models\IntegrationSettingValue;
use common\models\Order;
use common\models\OrderContent;
use api_web\modules\integration\classes\documents\Waybill;
use common\models\WaybillContent;

class iikoWaybill extends Waybill
{
    public $service = Registry::IIKO_SERVICE_ID;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getXmlDocument()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><document></document>');
        /** @var Order $order */
        $order = $this->order;

        if (!empty($order)) {
            /** @var OrderContent $number */
            $number = $order->getOrderContent()->andWhere('edi_number is not null')->one();
            $document_id = $order->id;
            $incomingDocumentNumber = $number->edi_number ?? $document_id . ' - ' . $this->outer_number_code;
        } else {
            $document_id = 'W-' . $this->id;
            $incomingDocumentNumber = $this->outer_number_code;
        }

        $waybillMode = IntegrationSettingValue::getSettingsByServiceId($this->service, $this->acquirer_id, ['auto_unload_invoice']);
        if ($waybillMode !== '0') {
            $documentNumber = $document_id . '-' . $this->outer_number_code;
        } else {
            $documentNumber = $document_id;
        }

        $doc_date = \gmdate('Y-m-d H:i:s', strtotime($this->doc_date));
        $doc_date = WebApiHelper::asDatetime($doc_date);

        //Учетный номер документа
        $xml->addChild('documentNumber', $documentNumber);
        //Входящий номер внешнего документа
        $xml->addChild('incomingDocumentNumber', $incomingDocumentNumber);
        //Номер счет-фактуры
        $xml->addChild('invoice', $this->outer_number_additional);
        //Комментарий
        $xml->addChild('comment', $this->outer_note);
        //Дата входящего документа
        $xml->addChild('dateIncoming', $doc_date);
        #Входящая дата внешнего документа     !!!Не реализовано в iiko!!!
        $xml->addChild('incomingDate', $doc_date);
        //Дата отсрочки платежа
        if (!empty($this->payment_delay_date)) {
            $xml->addChild('dueDate', WebApiHelper::asDatetime(\gmdate('Y-m-d H:i:s', strtotime($this->payment_delay_date))));
        }
        //Склад. Если указан, то в каждой позиции накладной нужно указать этот же склад.
        $xml->addChild('defaultStore', $this->outerStore->outer_uid);
        //Поставщик
        $xml->addChild('supplier', $this->outerAgent ? $this->outerAgent->outer_uid : null);
        //Статус накладной
        $xml->addChild('status', 'NEW');

        $items = $xml->addChild('items');
        $discount = 0;
        $records = $this->waybillContents;
        if (!empty($records)) {
            foreach ($records as $i => $row) {
                /** @var WaybillContent $row */
                $item = $items->addChild('item');
                $item->addChild('num', (++$i));
                $item->addChild('store', $this->outerStore->outer_uid);
                $item->addChild('amount', ($row->quantity_waybill * $row->koef));
                $item->addChild('product', $row->productOuter ? $row->productOuter->outer_uid : null);
                $item->addChild('amountUnit', $row->productOuter ? $row->productOuter->outerUnit->name : null);
                $item->addChild('sum', $row->sum_with_vat);
                $item->addChild('price', $row->price_with_vat);
                $item->addChild('sumWithoutNds', $row->sum_without_vat);
                $item->addChild('vatPercent', $row->vat_waybill);
                $item->addChild('ndsPercent', $row->vat_waybill);
                $item->addChild('discountSum', $discount);
                $item->addChild('isAdditionalExpense', false);
                $item->addChild('containerId');
            }
        }

        return $xml->asXML();
    }
}
