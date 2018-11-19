<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 30/10/2018
 * Time: 11:14
 */

namespace console\helpers;

use api_web\components\Registry;
use common\models\AllServiceOperation;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\IntegrationInvoice;
use common\models\Journal;
use common\models\Order;
use common\models\OrderContent;
use common\models\OuterAgent;
use common\models\OuterAgentNameWaybill;
use common\models\RelationSuppRest;
use yii\db\Query;

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
    private $serviceId = Registry::VENDOR_DOC_MAIL_SERVICE_ID;

    /**
     * @var
     */
    public $orgId;

    /**
     * @var
     */
    public $userId;

    /**
     * @param $invoice
     * @return bool
     * @throws \Exception
     */
    public function processFile($invoice)
    {
        $arAgentName = $this->prepareAgentName($invoice['invoice']['namePostav']);
        $outerAgentNameWaybill = OuterAgentNameWaybill::find()
            ->leftJoin(OuterAgent::tableName() .' oa', 'oa.id='. OuterAgentNameWaybill::tableName() . '.agent_id')
            ->where([OuterAgentNameWaybill::tableName() . '.name' => $arAgentName, 'oa.org_id' => $this->orgId])->one();
        if ($outerAgentNameWaybill) {
            $vendorId = $outerAgentNameWaybill->agent->vendor_id;
            $catRelation = RelationSuppRest::findOne([
                'rest_org_id' => $invoice['organization_id'],
                'supp_org_id' => $vendorId,
                'invite'      => 1,
                'status'      => 1,
                'deleted'     => 0]);
            if ($catRelation) {
                $catalog = Catalog::findOne($catRelation->cat_id);
                $catIndex = $catalog->main_index;
            } else {
                $catalog = Catalog::findOne(['supp_org_id' => $vendorId, 'type' => 1]);
                $catIndex = $catalog->main_index;
            }
            if (!$catIndex) {
                $this->addLog('VendorId = ' . $vendorId . ' dont have main_index in catalog', 'order_create');
                return false;
            }
            $transaction = \Yii::$app->db->beginTransaction();
            $order = new Order();
            $order->created_at = !empty($invoice['invoice']['date']) ? date('Y-m-d', strtotime($invoice['invoice']['date'])) : null;
            $order->vendor_id = $vendorId;
            $order->client_id = $invoice['organization_id'];
            $order->service_id = $this->serviceId;
            $order->status = Order::STATUS_EDI_SENT_BY_VENDOR;
            if (!$order->save()) {
                $this->addLog(implode(' ', $order->getFirstErrors()), 'order_create');
                return false;
            }

            if (!empty($invoice['invoice']['rows'])) {
                $cntErrors = 0;
                foreach ($invoice['invoice']['rows'] as $row) {
                    if ($catIndex == 'article' && (!isset($row['code']) || empty($row['code']))) {
                        $cntErrors++;
                        continue;
                    }
                    $strSearch = ['article' => $row['code'], 'product' => $row['name']];
                    $product = CatalogBaseGoods::findOne([$catIndex => $strSearch[$catIndex], 'supp_org_id' => $vendorId]);
                    if (!$product) {
                        $product = new CatalogBaseGoods();
                        $product->product = $row['name'];
                        $product->cat_id = $catalog->id;
                        $product->status = 1;
                        $product->article = $row['code'];
                        $product->deleted = 0;
                        $product->supp_org_id = $vendorId;
                        $product->price = round($row['price_without_tax'], 2);
                        $product->units = 1;
                        $product->ed = $row['ed'];
                        if (!$product->save()) {
                            $this->addLog(implode(' ', $product->getFirstErrors()) . ' Название продукта = ' . $row['name'], 'product_create');
                            continue;
                        }
                    }

                    $content = new OrderContent([
                        'order_id'           => $order->id,
                        'article'            => $row['code'],
                        'vat_product'        => ceil($row['tax_rate']),
                        'into_quantity'      => $row['cnt'],
                        'quantity'           => $row['cnt'],
                        'plan_quantity'      => $row['cnt'],
                        'into_price'         => round($row['price_without_tax'], 2),
                        'price'              => round($row['price_without_tax'], 2),
                        'plan_price'         => round($row['price_without_tax'], 2),
                        'into_price_vat'     => round($row['price_with_tax'], 2),
                        'into_price_sum'     => $row['sum_without_tax'],
                        'into_price_sum_vat' => round($row['sum_with_tax'], 2),
                        'product_id'         => $product->id,
                        'product_name'       => $row['name'],
                        'units'              => $product->units,
                    ]);
                    if (!$content->save()) {
                        $this->addLog(implode(' ', $content->getFirstErrors()) . ' № = ' . $invoice['invoice']['number'], 'order_create');
                    }
                }

                if (count($invoice['invoice']['rows']) == $cntErrors) {
                    $transaction->rollBack();
                    return false;
                } else {
                    $this->addLog('Заказ успешно создан, №=' . $order->id, 'order_create', 'success');
                }
                $order->calculateTotalPrice();

                $model = new IntegrationInvoice();
                $model->integration_setting_from_email_id = $invoice['integration_setting_from_email_id'];
                $model->organization_id = $invoice['organization_id'];
                $model->email_id = $invoice['email_id'];
                $model->file_mime_type = $invoice['file_mime_type'];
                $model->file_content = $invoice['file_content'];
                $model->file_hash_summ = $invoice['file_hash_summ'];
                $model->number = $invoice['invoice']['number'];
                $model->date = (!empty($invoice['invoice']['date']) ? date('Y-m-d', strtotime($invoice['invoice']['date'])) : null);
                $model->total_sum_withtax = $invoice['invoice']['price_with_tax_sum'];
                $model->total_sum_withouttax = $invoice['invoice']['price_without_tax_sum'];
                $model->name_postav = $invoice['invoice']['namePostav'];
                $model->inn_postav = $invoice['invoice']['innPostav'];
                $model->kpp_postav = $invoice['invoice']['kppPostav'];
                $model->consignee = $invoice['invoice']['nameConsignee'];

                if ($model->date == '1970-01-01') {
                    $model->date = null;
                }

                if (!$model->save()) {
                    $this->addLog(implode(' ', $model->getFirstErrors()) . ' № = ' . $invoice['invoice']['number'], 'order_create');
                }

                $transaction->commit();
            } else {
                $this->addLog('Dont have position rows in email waybill № = ' . $invoice['invoice']['number'], 'order_create');
            }

        } else {
            $this->addLog('Dont have outer agent relation with vendor name = ' . $invoice['invoice']['namePostav'], 'order_create');
        }

        return true;
    }

    /**
     * @param $denom
     * @return AllServiceOperation|null
     * @throws \Exception
     */
    private function getServiceOperation($denom)
    {
        $operation = AllServiceOperation::findOne(['service_id' => $this->serviceId, 'denom' => $denom]);

        if ($operation != null) {
            return $operation;
        }
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
        $journal->organization_id = $this->orgId;

        $journal->save();

//        $journal->getErrors();
        //$this->addInternalLog($response, $method, $localTransactionId, $request_xml, $response_xml);

    }

    /**
     * @param $name
     * @return array
     */
    private function prepareAgentName($name){
        $result = (new Query())->select('*')->from('ooo')->all();
        foreach ($result as $item) {
            if (strpos($name, $item['name_short']) === 0){
                $newAgentName = str_replace($item['name_short'], $item['name_long'], $name);
                return [$name, $newAgentName];
            }
        }
        return [$name];
    }
}