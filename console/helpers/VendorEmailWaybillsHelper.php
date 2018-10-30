<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 30/10/2018
 * Time: 11:14
 */

namespace console\helpers;

use common\models\AllServiceOperation;
use common\models\Journal;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgentNameWaybill;

/**
 * Class VendorEmailWaybillsHelper
 *
 * @package console\helpers
 */
class VendorEmailWaybillsHelper
{
    /**
     * @var int ServiceId
     */
    private $serviceId = 3;
    
    public function processFile($invoice){
        $agentName = $invoice['invoice']['namePostav'];
        $outerAgentNameWaybill = OuterAgentNameWaybill::findOne(['name' => $agentName]);
        if ($outerAgentNameWaybill){
            $vendorId = $outerAgentNameWaybill->agent->vendor_id;
            $order = new Order();
            $order->created_at = !empty($invoice['invoice']['date']) ? date('Y-m-d', strtotime($invoice['invoice']['date'])) : null;
            $order->vendor_id = $vendorId;
            $order->client_id = $invoice['organization_id'];
            $order->service_id = $this->serviceId;
            $order->status = Order::STATUS_EDI_SENT_BY_VENDOR;




            if (!empty($invoice['invoice']['rows'])) {
                foreach ($invoice['invoice']['rows'] as $row) {
                    $content = new OrderContent([
                        'order_id' => $order->id,
//                        'row_number' => $row['num'],
                        'article' => $row['code'],
//                        'title' => $row['name'],
//                        'ed' => $row['ed'],
                        'vat_product' => ceil($row['tax_rate']),
                        'into_quantity' => $row['cnt'],
                        'quantity' => $row['cnt'],
                        'initial_quantity' => $row['cnt'],
                        'into_price' => round($row['price_without_tax'], 2),
                        'price' => round($row['price_without_tax'], 2),
                        'plan_price' => round($row['price_without_tax'], 2),


                        'price_nds' => round($row['sum_with_tax'], 2),
                        'price_without_nds' => round($row['price_without_tax'], 2),
                        'quantity' => $row['cnt'],
                        'sum_without_nds' => $row['sum_without_tax'],
                    ]);
                    if (!$content->save()) {
                        $this->addLog(implode(' ', $content->getFirstErrors()) . ' № = ' . $invoice['invoice']['number'], 'order_create');

                    }
                }
            } else {
                $this->addLog('Dont have position rows in email waybill № = ' . $invoice['invoice']['number'], 'order_create');
            }

        } else {
            $this->addLog('Dont have outer agent relation with vendor name = ' . $invoice['invoice']['namePostav'], 'order_create');
        }

        $this->integration_setting_from_email_id = $invoice['integration_setting_from_email_id'];
        $this->organization_id = $invoice['organization_id'];
        $this->email_id = $invoice['email_id'];
        $this->file_mime_type = $invoice['file_mime_type'];
        $this->file_content = $invoice['file_content'];
        $this->file_hash_summ = $invoice['file_hash_summ'];
        $this->number = $invoice['invoice']['number'];
        $this->date = (!empty($invoice['invoice']['date']) ? date('Y-m-d', strtotime($invoice['invoice']['date'])) : null);
        $this->total_sum_withtax = $invoice['invoice']['price_with_tax_sum'];
        $this->total_sum_withouttax = $invoice['invoice']['price_without_tax_sum'];
        $this->name_postav = $invoice['invoice']['namePostav'];
        $this->inn_postav = $invoice['invoice']['innPostav'];
        $this->kpp_postav = $invoice['invoice']['kppPostav'];
        $this->consignee = $invoice['invoice']['nameConsignee'];

        if ($this->date == '1970-01-01') {
            $this->date = null;
        }

        if (!$this->save()) {
            throw new Exception(implode(' ', $this->getFirstErrors()));
        }

        if (!empty($invoice['invoice']['rows'])) {
            foreach ($invoice['invoice']['rows'] as $row) {
                $content = new IntegrationInvoiceContent([
                    'invoice_id' => $this->id,
                    'row_number' => $row['num'],
                    'article' => $row['code'],
                    'title' => $row['name'],
                    'ed' => $row['ed'],
                    'percent_nds' => ceil($row['tax_rate']),
                    'price_nds' => round($row['sum_with_tax'], 2),
                    'price_without_nds' => round($row['price_without_tax'], 2),
                    'quantity' => $row['cnt'],
                    'sum_without_nds' => $row['sum_without_tax'],
                ]);
                if (!$content->save()) {
                    throw new Exception(implode(' ', $content->getFirstErrors()));
                }
            }
        } else {
            throw new Exception('Error: empty rows');
        }

        return $this->id;
    }

    /**
     * @param $denom
     * @return AllServiceOperation|null
     * @throws \Exception
     */
    private function getServiceOperation($denom)
    {
        $operation = AllServiceOperation::findOne(['service_id' => $this->serviceId, 'denom' => $denom]);

        if($operation != null)
            return $operation;
        throw new \Exception('Operation - ' . $denom . ' dont exists');
    }

    /**
     * @param $response
     * @param $denom
     * @param $type
     * @throws \Exception
     */
    public function addLog($response, $denom, $type = 'error')
    {
        $operation = $this->getServiceOperation($denom);
        $journal = new Journal();
        $journal->service_id = $this->serviceId;
        $journal->operation_code = (string)$operation->code;
        $journal->log_guide = $denom;
        $journal->type = $type;
        $journal->response = $response;

        $journal->save();

//        $journal->getErrors();
        //$this->addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml);

    }

}