<?php

namespace api_web\modules\integration\models;

use api_web\components\Registry;
use common\models\IntegrationSettingValue;
use common\models\Order;
use common\models\OrderContent;
use common\models\Waybill;
use common\models\WaybillContent;

class iikoWaybill extends Waybill
{
    /**
     * @return mixed
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

        $waybillMode = IntegrationSettingValue::getSettingsByServiceId(Registry::IIKO_SERVICE_ID, $this->acquirer_id, ['auto_unload_invoice']);
        if ($waybillMode !== '0') {
            $documentNumber = $document_id . '-' . $this->outer_number_code;
        } else {
            $documentNumber = $document_id;
        }

        $datetime = new \DateTime($this->doc_date);
        $xml->addChild('documentNumber', $documentNumber);
        $xml->addChild('incomingDocumentNumber', $incomingDocumentNumber);
        $xml->addChild('invoice', $this->outer_number_additional);
        $xml->addChild('comment', $this->outer_note);
        $xml->addChild('dateIncoming', $datetime->format('d.m.Y'));
        $xml->addChild('incomingDate', $datetime->format('d.m.Y'));
        $xml->addChild('defaultStore', $this->outerStore->outer_uid);
        $xml->addChild('supplier', $this->outerAgent ? $this->outerAgent->outer_uid : null);
        $xml->addChild('status', 'NEW');

        $items = $xml->addChild('items');
        $discount = 0;
        $records = $this->waybillContents;
        if (!empty($records)) {
            foreach ($records as $i => $row) {
                /** @var WaybillContent $row */
                $item = $items->addChild('item');
                $item->addChild('amount', ($row->quantity_waybill * $row->koef));
                $item->addChild('product', $row->productOuter->outer_uid);
                $item->addChild('num', (++$i));
                $item->addChild('containerId');
                $item->addChild('amountUnit', $row->productOuter->outerUnit->name);
                $item->addChild('discountSum', $discount);
                $item->addChild('sumWithoutNds', $row->sum_without_vat);
                $item->addChild('vatPercent', $row->vat_waybill);
                $item->addChild('ndsPercent', $row->vat_waybill);
                $item->addChild('sum', $row->price_with_vat);
                $item->addChild('price', $row->sum_with_vat);
                $item->addChild('isAdditionalExpense', false);
                $item->addChild('store', $this->outerStore->outer_uid);
            }
        }
        return $xml->asXML();
    }
}