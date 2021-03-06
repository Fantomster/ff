<?php

namespace api\common\models\iiko;

use api\common\models\AllMaps;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\classes\DocumentWebApi;
use common\helpers\DBNameHelper;
use common\models\Order;
use common\models\OrderContent;
use frontend\modules\clientintegr\components\CreateWaybillByOrderInterface;
use Yii;
use frontend\controllers\ClientController;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use api_web\components\Registry;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "iiko_waybill".
 *
 * @property integer           $id
 * @property string            $agent_uuid
 * @property integer           $org
 * @property integer           $order_id
 * @property string            $num_code
 * @property string            $text_code
 * @property integer           $readytoexport
 * @property integer           $status_id
 * @property integer           $store_id
 * @property string            $note
 * @property integer           $is_duedate
 * @property integer           $active
 * @property integer           $vat_included
 * @property string            $doc_date
 * @property string            $created_at
 * @property string            $exported_at
 * @property string            $updated_at
 * @property integer           $payment_delay_date
 * @property integer           $service_id
 * @property Order             $order;
 * @property iikoWaybillData[] $waybillData
 */
class iikoWaybill extends \yii\db\ActiveRecord implements CreateWaybillByOrderInterface
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

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
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
            $date = $arr[0] . " " . gmdate('H:i:s');
        } else {
            $date = $this->doc_date;
        }
        $doc_date = \Yii::$app->formatter->asDatetime($date, WebApiHelper::$formatDate);
        $xml->addChild('dateIncoming', $doc_date);
        $xml->addChild('incomingDate', $doc_date);

        $arr = explode(' ', $this->payment_delay_date);
        if (isset($arr[1])) {
            $date_delay = $arr[0] . " " . gmdate('H:i:s');
        } else {
            $date_delay = $this->payment_delay_date;
        }
        $doc_date_delay = \Yii::$app->formatter->asDatetime($date_delay, WebApiHelper::$formatDate);
        $xml->addChild('dueDate', $doc_date_delay);

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

        return $xml->asXML();
    }

    public function getVatList(): array
    {
        return [
            '1'    => Yii::t('message', 'frontend.views.order.all', ['ru' => 'Все']),
            '0'    => 0,
            '1000' => 10,
            '1800' => 18,
            '2000' => 20
        ];
    }

    public static function createWaybill($order_id, $service_id = Registry::IIKO_SERVICE_ID, $auto = false)
    {
        $order_id = (int)$order_id; //переписать без raw запросов
        $res = true;

        $order = \common\models\Order::findOne(['id' => $order_id]);

        if (!$order) {
            throw new NotFoundHttpException(Yii::t('error', 'api.controllers.order.not.find', ['ru' => 'Заказа с таким номером не существует.']));
        }

        // Получаем список складов, чтобы понять, сколько надо делать накладных

        $allMapTableName = DBNameHelper::getApiName() . '.' . AllMaps::tableName();
        $orderContentTableName = OrderContent::tableName();
        $client_id = self::getClientIDcondition($order->client_id, $allMapTableName . '.product_id');
        $stories = OrderContent::find()
            ->select("$allMapTableName.store_rid")
            ->leftJoin($allMapTableName, "$orderContentTableName.product_id = $allMapTableName.product_id and $allMapTableName.service_id = $service_id AND 
            $allMapTableName.org_id in ($client_id)")
            ->where("$orderContentTableName.order_id = :order_id", [':order_id' => $order_id])
            ->groupBy('store_rid')
            ->asArray()->all();

        $contra = iikoAgent::findOne(['vendor_id' => $order->vendor_id]);

        $num = (count($stories) > 1) ? 1 : '';

        foreach ($stories as $store) {
            $store = $store['store_rid'];
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
                throw new NotFoundHttpException(Yii::t('error', 'api.iiko.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную IIKO не удалось.']));
                continue;
            } else {
                $model->createWaybillData();
                $kolvo_nesopost = iikoWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
                if (($model->agent_uuid === null) or ($model->num_code === null) or ($model->text_code === null) or ($model->store_id === null)) {
                    $shapka = 0;
                } else {
                    $shapka = 1;
                }
                if ($kolvo_nesopost == 0) {
                    if ($shapka == 1) {
                        $model->readytoexport = 1;
                        $model->status_id = 4;
                    } else {
                        $model->readytoexport = 0;
                        $model->status_id = 1;
                    }
                } else {
                    if ($shapka == 1) {
                        $model->readytoexport = 0;
                        $model->status_id = 1;
                    } else {
                        $model->readytoexport = 0;
                    }
                }
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.iiko.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную IIKO не удалось.']));
                }
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
            $res = AllMaps::find()
                ->select('product_id')
                ->where("service_id = " . Registry::IIKO_SERVICE_ID . " and org_id = $client_id")
                ->asArray()->all();

            $maps = [];
            foreach ($res as $key => $value) {
                $maps[] = $value['product_id'];
            }

            $maps = implode(",", $maps);

            $client_id = "IF($product_field in ($maps), $client_id, $mainOrg)";
        }

        return $client_id;
    }

    public static function exportWaybill($order_id, $auto = false)
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

        foreach ($records as $waybill) {
            if ($auto && empty($waybill->store_id)) {
                return false;
            }
        }

        $api = iikoApi::getInstance();

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

    public function createWaybillData($service_id = Registry::IIKO_SERVICE_ID)
    {
        $dbName = DBNameHelper::getApiName();
        $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();
        $allmapTableName = $dbName . '.' . AllMaps::tableName();
        $client_id = $this->org;

        if ($waybillMode !== '0') {
            $client_id = self::getClientIDcondition($this->org, $dbName . '.all_map.product_id');
            if ($this->store_id === null) {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin($allmapTableName, OrderContent::tableName() . ".product_id = $allmapTableName.product_id and $allmapTableName.service_id = $service_id and $allmapTableName.org_id in ($client_id)")
                    ->andWhere($allmapTableName . '.store_rid is null')
                    ->all();
            } else {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin($allmapTableName, OrderContent::tableName() . ".product_id = $allmapTableName.product_id and $allmapTableName.service_id = $service_id and $allmapTableName.org_id in ($client_id)")
                    ->andWhere($allmapTableName . '.store_rid =' . $this->store_id)
                    ->all();
            }
        } else {
            $records = OrderContent::findAll(['order_id' => $this->order_id]);
        }

        $transaction = \Yii::$app->db_api->beginTransaction();
        try {
            $taxVat = (iikoDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() != null) ? iikoDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() : 2000;
            $res = AllMaps::find()
                ->select('product_id')
                ->where("service_id = " . $service_id . " and org_id = $client_id")
                ->asArray()->all();
            $maps = [];
            foreach ($res as $key => $value) {
                $maps[] = $value['product_id'];
            }

            $maps = implode(",", $maps);

            foreach ($records as $record) {
                $wdmodel = new iikoWaybillData();
                if (($record->into_quantity != null) and ($record->into_price != null) and ($record->into_price_vat != null) and ($record->into_price_sum != null) and ($record->into_price_sum_vat != null) and ($record->vat_product != null) and ($record->quantity == $record->into_quantity)) {
                    $wdmodel->quant = $record->into_quantity;
                    $wdmodel->sum = $record->into_price_sum;
                    $wdmodel->defquant = $record->into_quantity;
                    $wdmodel->defsum = $record->into_price_sum;
                    $wdmodel->vat = $record->vat_product * 100;
                } elseif (isset($record->invoiceContent)) {
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
                // New check mapping
                $client_id = $this->org;
                if ($wdmodel->org != $this->org) {
                    $client_id = "IF(product_id in ($maps), $client_id, $wdmodel->org)";
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

    /**
     * @param iikoWaybill $contributorWaybill
     * @param iikoWaybill $recipientWaybill
     * @return iikoWaybill
     */
    public static function moveContentToExistingWaybill($contributorWaybill, $recipientWaybill)
    {
        foreach ($contributorWaybill->waybillData as $position) {
            $position->waybill_id = $recipientWaybill->id;
            $position->save();
        }
        $contributorWaybill->delete();
        return $recipientWaybill;
    }

}
