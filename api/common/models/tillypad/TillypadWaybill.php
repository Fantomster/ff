<?php

namespace api\common\models\tillypad;

use api\common\models\AllMaps;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\classes\DocumentWebApi;
use common\helpers\DBNameHelper;
use common\models\Order;
use common\models\OrderContent;
use frontend\modules\clientintegr\components\CreateWaybillByOrderInterface;
use Yii;
use frontend\controllers\ClientController;
use yii\helpers\ArrayHelper;
use frontend\modules\clientintegr\modules\iiko\helpers\TillypadApi;
use api_web\components\Registry;

/**
 * This is the model class for table "iiko_waybill".
 *
 * @property integer $id
 * @property string  $agent_uuid
 * @property integer $org
 * @property integer $order_id
 * @property string  $num_code
 * @property string  $text_code
 * @property integer $readytoexport
 * @property integer $status_id
 * @property integer $store_id
 * @property string  $note
 * @property integer $is_duedate
 * @property integer $active
 * @property integer $vat_included
 * @property string  $doc_date
 * @property string  $created_at
 * @property string  $exported_at
 * @property string  $updated_at
 * @property integer $payment_delay_date
 * @property integer $service_id
 * @property Order   $order;
 */
class TillypadWaybill extends \yii\db\ActiveRecord implements CreateWaybillByOrderInterface
{

    const AUTOSTATUS_NEW = 1;
    const AUTOSTATUS_DELETED = 2;
    const AUTOSTATUS_REBORN = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'iiko_waybill';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['org', 'order_id', 'readytoexport', 'status_id', 'store_id', 'is_duedate', 'active', 'vat_included', 'service_id'], 'integer'],
            [['doc_date', 'created_at', 'exported_at', 'updated_at', 'num_code', 'payment_delay_date', 'autostatus_id'], 'safe'],
            [['org'], 'required'],
            [['org', 'agent_uuid', 'store_id'], 'required', 'on' => 'handMade'],
            [['agent_uuid'], 'string', 'max' => 36],
            [['text_code', 'num_code'], 'string', 'max' => 128],
            [['note'], 'string', 'max' => 255],
            [['payment_delay_date'], 'isPayDelayOneYearDiff'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function isPayDelayOneYearDiff($attribute, $params)
    {
        $start_date = getdate(strtotime($this->doc_date));
        $start_date = mktime(0, 0, 0, $start_date['mon'], $start_date['mday'], $start_date['year']);
        $end_date = getdate(strtotime($this->$attribute));
        $end_date = mktime(0, 0, 0, $end_date['mon'], $end_date['mday'], $end_date['year']);
        if (($end_date - $start_date) > (ClientController::MAX_DELAY_PAYMENT * 60 * 60 * 24)) {
            $this->addError($attribute, 'Дата отсрочки платежа не может превышать дату документа на срок более' .
                ClientController::MAX_DELAY_PAYMENT . ' дней!');
        }
    }

    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'agent_uuid'         => Yii::t('app', 'Контрагент'),
            'org'                => Yii::t('app', 'Организация'),
            'order_id'           => Yii::t('app', 'Заказ'),
            'num_code'           => Yii::t('app', 'Номер документа'),
            'text_code'          => Yii::t('app', 'Счет-фактура'),
            'readytoexport'      => Yii::t('app', 'Readytoexport'),
            'status_id'          => Yii::t('app', 'Статус'),
            'store_id'           => Yii::t('app', 'Склад'),
            'note'               => Yii::t('app', 'Примечание'),
            'is_duedate'         => Yii::t('app', 'Is Duedate'),
            'active'             => Yii::t('app', 'Active'),
            'vat_included'       => Yii::t('app', 'Vat Included'),
            'doc_date'           => Yii::t('app', 'Дата документа'),
            'created_at'         => Yii::t('app', 'Created At'),
            'exported_at'        => Yii::t('app', 'Exported At'),
            'updated_at'         => Yii::t('app', 'Updated At'),
            'payment_delay_date' => Yii::t('app', 'Дата отсрочки платежа'),
            'service_id'         => Yii::t('app', 'Идентификатор учётного сервиса'),
        ];
    }

    public function beforeSave($insert)
    {

        $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

        if ($waybillMode !== '2') { // Is not a manual mode
            if (empty($this->text_code)) {
                $this->text_code = 'mixcart';
            }
            if (empty($this->num_code)) {
                $this->num_code = $this->order_id;
            }
        }

        $doc_num = $this->order->waybill_number;

        if (!empty($doc_num)) {
            $this->num_code = $doc_num;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->createWaybillData($this->service_id);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(iikoAgent::className(), ['uuid' => 'agent_uuid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(iikoStore::className(), ['id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(iikoWaybillStatus::className(), ['id' => 'status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWaybillData()
    {
        return $this->hasMany(iikoWaybillData::className(), ['waybill_id' => 'id']);
    }

    /**
     * @return mixed
     */
    public function getXmlDocument()
    {
        $model = $this;
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><document></document>');

        $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

        if ($waybillMode !== '0') {
            $xml->addChild('documentNumber', $model->order_id . '-' . $model->num_code);
            $xml->addChild('invoice', $model->text_code);

            $doc_num = $this->order->waybill_number;

            if (!empty($doc_num)) {
                $xml->addChild('incomingDocumentNumber', $doc_num);
            } else {
                $xml->addChild('incomingDocumentNumber', $model->order_id . '-' . $model->num_code);
            }
        } else {
            $xml->addChild('documentNumber', $model->order_id);
            $xml->addChild('invoice', $model->text_code);

            if (!empty($doc_num)) {
                $xml->addChild('incomingDocumentNumber', $doc_num);
            } else {
                $xml->addChild('incomingDocumentNumber', $model->num_code);
            }
        }

        $xml->addChild('comment', $model->note);

        $arr = explode(' ', $this->doc_date);
        if (isset($arr[1])) {
            $date = $arr[0] . " " . date('H:i:s');
        } else {
            $date = $this->doc_date;
        }
        $doc_date = \Yii::$app->formatter->asDatetime($date, WebApiHelper::$formatDate);
        $xml->addChild('dateIncoming', $doc_date);
        $xml->addChild('incomingDate', $doc_date);

        $xml->addChild('defaultStore', $model->store->uuid);
        $xml->addChild('supplier', $model->agent->uuid);
        $xml->addChild('status', 'NEW');

        $items = $xml->addChild('items');
        /**
         * @var $row iikoWaybillData
         */
        $records = iikoWaybillData::findAll(['waybill_id' => $model->id, 'unload_status' => 1]);
        $vatPercent = 0;
        $discount = 0;
        //  $vatModel = \api\common\models\iiko\iikoDicconst::findOne(['denom' => 'taxVat']);
        //  if($vatModel) {
        //      $vatPercent = $vatModel->getPconstValue() / 100;
        //  }

        foreach ($records as $i => $row) {
            $item = $items->addChild('item');

            $item->addChild('amount', $row->quant);
            $item->addChild('product', $row->product->uuid);
            $item->addChild('num', (++$i));
            $item->addChild('containerId');
            $item->addChild('amountUnit', $row->munit);
            $item->addChild('discountSum', $discount);
            $item->addChild('sumWithoutNds', $row->sum);
            $item->addChild('vatPercent', $row->vat / 100);
            $item->addChild('ndsPercent', $row->vat / 100);

            $item->addChild('sum', round($row->sum + ($row->sum * $row->vat / 10000), 2));
            //  $item->addChild('price', round($row->sum / $row->quant, 2));
            $item->addChild('price', round(($row->sum + round($row->sum / 100 * $row->vat / 100)) / $row->quant, 2));

            $item->addChild('isAdditionalExpense', false);
            $item->addChild('store', $model->store->uuid);
        }

//        var_dump($xml);
//        die();

        return $xml->asXML();
    }

    public function getVatList(): array
    {
        return [
            '1'    => Yii::t('message', 'frontend.views.order.all', ['ru' => 'Все']),
            '0'    => 0,
            '1000' => 10,
            '1800' => 18
        ];
    }

    public static function createWaybill($order_id, $service_id = Registry::IIKO_SERVICE_ID)
    {

        $res = true;

        $order = \common\models\Order::findOne(['id' => $order_id]);

        if (!$order) {
            \Yii::error('Cant find order during sending waybill');
            throw new \Exception('Ошибка при отправке.' . $order_id);
        }

        $dbName = DBNameHelper::getMainName();

        $client_id = self::getClientIDcondition($order->client_id, 'm.product_id');

        // Получаем список складов, чтобы понять сколько надо делать накладных

        $db = Yii::$app->db_api;
        $sql = ' SELECT m.store_rid FROM ' . $dbName . '.`order_content` o ' .
            ' LEFT JOIN all_map m ON o.product_id = m.product_id AND m.service_id = ' . $service_id . ' AND m.org_id in (' . $client_id . ') ' .
            ' WHERE o.order_id = ' . $order_id .
            ' GROUP BY store_rid';

        $stories = $db->createCommand($sql)->queryAll();
        $stories = ArrayHelper::getColumn($stories, 'store_rid');

        $contra = iikoAgent::findOne(['vendor_id' => $order->vendor_id]);

        $num = (count($stories) > 1) ? 1 : '';

        foreach ($stories as $store) {
            $model = new iikoWaybill();
            $model->order_id = $order_id;
            $model->status_id = 1;
            $model->org = $order->client_id;
            $model->store_id = $store;
            $model->service_id = $service_id;
            $model->agent_uuid = isset($contra) ? $contra->uuid : null;
            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s'); //date('d.m.Y', strtotime($model->doc_date));
            $model->payment_delay_date = Yii::$app->formatter->asDate($model->payment_delay_date . ' 16:00:00', 'php:Y-m-d H:i:s');

            $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

            if ($waybillMode !== '2') {
                $model->text_code = 'mixcart' . $order_id . '-' . $num;
                $model->num_code = strval($num);
            }

            if (!$model->save()) {
                $num++;
                $res = false;
                \yii::error('Error during saving auto waybill' . print_r($model->getErrors(), true));
                continue;
            }

            $num++;
        }

        return $res;
    }

    private static function getClientIDcondition($org_id, $product_field)
    {
        $client_id = $org_id;
        $mainOrg = iikoService::getMainOrg($org_id);

        if ($mainOrg != $org_id) {
            $dbName = DBNameHelper::getApiName();
            $client_id = "IF($product_field in (select product_id from $dbName.all_map where service_id = " . Registry::IIKO_SERVICE_ID . " and org_id = $client_id), $client_id, $mainOrg)";
        }

        return $client_id;
    }

    public static function exportWaybill($order_id)
    {
        $res = true;
        $records = iikoWaybill::find()
            ->andWhere('order_id = :ord', [':ord' => $order_id])
            ->andWhere('status_id = :stat', [':stat' => 4])
            ->all();

        if (!isset($records)) {
            \Yii::error('Cant find waybills for export');
            throw new \Exception('Ошибка при экспорте накладных в авторежиме');
        }

        $api = TillypadApi::getInstance();

        if ($api->auth()) {

            foreach ($records as $model) {
                try {
                    $transaction = Yii::$app->db_api->beginTransaction();

                    $response = $api->sendWaybill($model);
                    if ($response !== true) {
                        \Yii::error('Error during sending waybill');
                        throw new \Exception('Ошибка при отправке. ' . $response);
                    } else {
                        \Yii::error('Waybill' . $model->id . 'has been exported');
                    }

                    $model->status_id = 2;
                    $model->save();
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \yii::error('Cant send waybill, rolled back' . $e);
                    $res = false;
                }
            }
            $api->logout();
        }
        return $res;
    }

    protected function createWaybillData($service_id = Registry::IIKO_SERVICE_ID)
    {
        $dbName = DBNameHelper::getApiName();

        $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

        if ($waybillMode !== '0') {
            $client_id = self::getClientIDcondition($this->org, '`' . $dbName . '`.all_map.product_id');
            if ($this->store_id === null) {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin($dbName . '.`all_map`', 'order_content.product_id = ' . $dbName . '.`all_map`.`product_id` and ' . $dbName . '.all_map.service_id = ' . $service_id . ' and ' . $dbName . '.all_map.org_id in (' . $client_id . ')')
                    ->andWhere($dbName . '.all_map.store_rid is null')
                    ->all();
            } else {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin($dbName . '.`all_map`', 'order_content.product_id = ' . $dbName . '.`all_map`.`product_id` and ' . $dbName . '.all_map.service_id = ' . $service_id . ' and ' . $dbName . '.all_map.org_id in (' . $client_id . ')')
                    ->andWhere($dbName . '.all_map.store_rid =' . $this->store_id)
                    ->all();
            }
        } else {
            $records = OrderContent::findAll(['order_id' => $this->order_id]);
        }

        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            $taxVat = (iikoDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() != null) ? iikoDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() : 1800;
            foreach ($records as $record) {
                $wdmodel = new iikoWaybillData();
                ///$wdmodel->setScenario('autoWaybill');
                if (isset($record->invoiceContent)) {
                    $wdmodel->quant = $record->invoiceContent->quantity;
                    $wdmodel->sum = $record->invoiceContent->sum_without_nds;
                    $wdmodel->defquant = $record->invoiceContent->quantity;
                    $wdmodel->defsum = $record->invoiceContent->sum_without_nds;
                    $wdmodel->vat = $record->invoiceContent->percent_nds * 100;
                } else {
                    $wdmodel->quant = $record->quantity;
                    $wdmodel->sum = round($record->price * $record->quantity, 2);
                    $wdmodel->defquant = $record->quantity;
                    $wdmodel->defsum = round($record->price * $record->quantity, 2);
                    $wdmodel->vat = $taxVat;
                }
                $wdmodel->waybill_id = $this->id;
                $wdmodel->product_id = $record->product_id;
                $wdmodel->org = iikoService::getMainOrg($this->org);
                $wdmodel->koef = 1;
                $wdmodel->created_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                $wdmodel->updated_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                // New check mapping
                $client_id = $this->org;
                if ($wdmodel->org != $this->org) {
                    $client_id = "IF(product_id in (select product_id from all_map where service_id = ' . $service_id . ' and org_id = $client_id), $client_id, $wdmodel->org)";
                }

                $ch = AllMaps::find()
                    ->andWhere('product_id = :prod', ['prod' => $record->product_id])
                    ->andWhere("org_id in ($client_id)")
                    ->andWhere('service_id = :service_id', ['service_id' => $service_id])
                    ->one();

                if ($ch) {
                    if (isset($ch->serviceproduct_id)) {
                        $wdmodel->product_rid = $ch->serviceproduct_id;
                    } else {
                        $wdmodel->product_rid = null;
                    }

                    if (isset($ch->koef)) {
                        $wdmodel->koef = $ch->koef;
                        $wdmodel->quant = $wdmodel->quant * $ch->koef;
                    }

                    if (isset($ch->unit_rid)) {
                        $wdmodel->munit = $ch->unit_rid;
                    }

                    if (isset($ch->vat) && !isset($record->invoiceContent)) {
                        $wdmodel->vat = $ch->vat;
                    }
                } else {
                    $wdmodel->product_rid = null;
                }

                if (!$wdmodel->save()) {
                    \yii::error(print_r($wdmodel->getErrors()), true);
                    throw new \Exception();
                }
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            \yii::error($ex->getTraceAsString());
            $transaction->rollback();
        }
    }

}
