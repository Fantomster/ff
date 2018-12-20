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

        $waybillMode = IntegrationSettingValue::getSettingsByServiceId(Registry::IIKO_SERVICE_ID, $this->acquirer_id, ['auto_unload_invoice']);
        if ($waybillMode !== '0') {
            $documentNumber = $document_id . '-' . $this->outer_number_code;
        } else {
            $documentNumber = $document_id;
        }

        $doc_date = \Yii::$app->formatter->asDatetime($this->doc_date, WebApiHelper::$formatDate);
        $xml->addChild('documentNumber', $documentNumber);
        $xml->addChild('incomingDocumentNumber', $incomingDocumentNumber);
        $xml->addChild('invoice', $this->outer_number_additional);
        $xml->addChild('comment', $this->outer_note);
        $xml->addChild('dateIncoming', $doc_date);
        $xml->addChild('incomingDate', $doc_date);
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