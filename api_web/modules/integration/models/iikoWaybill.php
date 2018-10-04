<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/26/2018
 * Time: 1:37 PM
 */

namespace api_web\modules\integration\models;

use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoWaybillData;
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
        $wbContent = $this->waybillContents;
        $wc = reset($wbContent);
        $orderCon = OrderContent::findOne(['id' => $wc->order_content_id]);
        $order_id = $orderCon->order_id;
        $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();
        $doc_num = (Order::findOne($order_id))->waybill_number;

        if ($waybillMode !== '0') {
            $xml->addChild('documentNumber', $order_id . '-' . $this->outer_number_code);
            $xml->addChild('invoice', $this->outer_number_additional);

            if (!empty($doc_num)) {
                $xml->addChild('incomingDocumentNumber', $doc_num);
            } else {
                $xml->addChild('incomingDocumentNumber', $order_id . '-' . $this->outer_number_code);
            }

        } else {
            $xml->addChild('documentNumber', $order_id);
            $xml->addChild('invoice', $this->outer_number_additional);

            if (!empty($doc_num)) {
                $xml->addChild('incomingDocumentNumber', $doc_num);
            } else {
                $xml->addChild('incomingDocumentNumber', $this->outer_number_code);
            }

        }

        $xml->addChild('comment', $this->outer_note);
        $datetime = new \DateTime($this->doc_date);
        $xml->addChild('dateIncoming', $datetime->format('d.m.Y'));
        $xml->addChild('incomingDate', $datetime->format('d.m.Y'));
        $xml->addChild('defaultStore', $this->outer_store_uuid);
        $xml->addChild('supplier', $this->outer_contractor_uuid);
        $xml->addChild('status', 'NEW');

        $items = $xml->addChild('items');
        /**
         * @var $row WaybillContent
         */
        $records = WaybillContent::findAll(['waybill_id' => $this->id, 'unload_status' => 1]);
        $discount = 0;

        foreach($records as $i => $row) {
            $item = $items->addChild('item');

            $item->addChild('amount', $row->quantity_waybill);
            $item->addChild('product', $row->productOuter->outer_uid);
            $item->addChild('num', (++$i));
            $item->addChild('containerId');
            $item->addChild('amountUnit', $row->productOuter->outerUnit->name);
            $item->addChild('discountSum', $discount);
            $item->addChild('sumWithoutNds', $row->sum_without_vat);
            $item->addChild('vatPercent', $row->vat_waybill / 100);
            $item->addChild('ndsPercent', $row->vat_waybill / 100);

            $item->addChild('sum', $row->price_with_vat);
            //  $item->addChild('price', round($row->sum / $row->quant, 2));
            $item->addChild('price', $row->sum_with_vat);

            $item->addChild('isAdditionalExpense', false);
            $item->addChild('store', $this->outer_store_uuid);

        }

//        var_dump($xml);
//        die();

        return $xml->asXML();
    }

}