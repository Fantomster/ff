<?php

namespace api\common\models\iiko;

use api\common\models\AllMaps;
use common\helpers\DBNameHelper;
use common\models\Order;
use common\models\OrderContent;
use frontend\modules\clientintegr\components\CreateWaybillByOrderInterface;
use frontend\modules\clientintegr\modules\iiko\controllers\WaybillController;
use Yii;
use frontend\controllers\ClientController;
use yii\helpers\ArrayHelper;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;

/**
 * This is the model class for table "iiko_waybill".
 *
 * @property integer $id
 * @property string $agent_uuid
 * @property integer $org
 * @property integer $order_id
 * @property string $num_code
 * @property string $text_code
 * @property integer $readytoexport
 * @property integer $status_id
 * @property integer $store_id
 * @property string $note
 * @property integer $is_duedate
 * @property integer $active
 * @property integer $vat_included
 * @property string $doc_date
 * @property string $created_at
 * @property string $exported_at
 * @property string $updated_at
 * @property integer $payment_delay_date
 * @property Order $order;
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
            [['org', 'order_id', 'readytoexport', 'status_id', 'store_id', 'is_duedate', 'active', 'vat_included'], 'integer'],
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
            $this->addError($attribute,
                'Дата отсрочки платежа не может превышать дату документа на срок более' .
                ClientController::MAX_DELAY_PAYMENT . ' дней!');
        }
    }


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'agent_uuid' => Yii::t('app', 'Контрагент'),
            'org' => Yii::t('app', 'Организация'),
            'order_id' => Yii::t('app', 'Заказ'),
            'num_code' => Yii::t('app', 'Номер документа'),
            'text_code' => Yii::t('app', 'Счет-фактура'),
            'readytoexport' => Yii::t('app', 'Readytoexport'),
            'status_id' => Yii::t('app', 'Статус'),
            'store_id' => Yii::t('app', 'Склад'),
            'note' => Yii::t('app', 'Примечание'),
            'is_duedate' => Yii::t('app', 'Is Duedate'),
            'active' => Yii::t('app', 'Active'),
            'vat_included' => Yii::t('app', 'Vat Included'),
            'doc_date' => Yii::t('app', 'Дата документа'),
            'created_at' => Yii::t('app', 'Created At'),
            'exported_at' => Yii::t('app', 'Exported At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'payment_delay_date' => Yii::t('app', 'Дата отсрочки платежа'),
        ];
    }

    public function beforeSave($insert)
    {

        if (empty($this->text_code)) {
            $this->text_code = 'mixcart';
        }
        if (empty($this->num_code)) {
            $this->num_code = $this->order_id;
        }
        return parent::beforeSave($insert);

    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert ) {
            $this->createWaybillData();
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

        $xml->addChild('comment', $model->note);
        $xml->addChild('documentNumber', $model->order_id);
        $datetime = new \DateTime($model->doc_date);
        $xml->addChild('dateIncoming', $datetime->format('d.m.Y'));
        $xml->addChild('incomingDate', $datetime->format('d.m.Y'));
        $xml->addChild('invoice', $model->text_code);
        $xml->addChild('defaultStore', $model->store->uuid);
        $xml->addChild('supplier', $model->agent->uuid);
        $xml->addChild('incomingDocumentNumber', $model->num_code);
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
            $item->addChild('price', round(($row->sum  + round($row->sum/100*$row->vat/100)) / $row->quant, 2));

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
            '1' => Yii::t('message', 'frontend.views.order.all', ['ru' => 'Все']),
            '0' => 0,
            '1000' => 10,
            '1800' => 18
        ];
    }


    public static function createWaybill($order_id)
    {

        $res = true;

        $order = \common\models\Order::findOne(['id' => $order_id]);

        if (!$order) {
            echo "Can't find order";
            return false;
        }

        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db->dsn);

        $db = Yii::$app->db_api;
        $sql = ' SELECT m.store_rid from `'.$dbName.'`.`order_content` o '.
               ' LEFT JOIN all_map m on o.product_id = m.product_id and m.service_id = 2 '.
               ' WHERE o.order_id = '.$order_id.' AND m.org_id = '.$order->client_id.
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
            $model->text_code = 'mixcart'.$order_id.'-'.$num;
               // $model->num_code
            $model->store_id = $store;
            $model->agent_uuid = isset($contra) ? $contra->uuid : null;

            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s');//date('d.m.Y', strtotime($model->doc_date));
            $model->payment_delay_date = Yii::$app->formatter->asDate($model->payment_delay_date . ' 16:00:00', 'php:Y-m-d H:i:s');

            if (!$model->save()) {
                $num++;
                $res = false;
                continue;
            } else {

                $waybillModeIiko = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();
                $model->refresh();

                if ($waybillModeIiko === '1' && $model->status_id == 4 ) { // Autosend waybill

                    $transaction = Yii::$app->db_api->beginTransaction();
                    $api = iikoApi::getInstance();

                    try {
                        if ($api->auth()) {
                            $response = $api->sendWaybill($model);
                            if ($response !== true) {
                                \Yii::error('Error during sending waybill');
                                throw new \Exception('Ошибка при отправке. ' . $response);
                            }
                            $model->status_id = 2;
                            $model->save();
                        } else {
                            \yii::error('Error during iiko auth');
                            throw new \Exception('Не удалось авторизоваться');
                        }

                        $transaction->commit();
                        $api->logout();
                        return true;

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        $api->logout();
                        \yii::error('Cant send waybill, rolled back'.$e);
                        return ['success' => false, 'error' => $e->getMessage()];
                    }
                }
            }
            $num++;
        }

        return $res;

    }

    public static function exportWaybill($waybill_id): bool
    {
        // TODO: Implement exportWaybill() method here
    }

    protected function createWaybillData()
    {
        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db_api->dsn);

        $waybillMode = iikoDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

        if ($waybillMode !== '0') {

            if ($this->store_id === null) {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin('`'.$dbName.'`.`all_map`','order_content.product_id = `'.$dbName.'`.`all_map`.`product_id` and `'.$dbName.'`.all_map.service_id = 2')
                    ->andWhere('`'.$dbName.'`.all_map.store_rid is null')
                    ->all();
            } else {
                $records = OrderContent::find()
                    ->where(['order_id' => $this->order_id])
                    ->leftJoin('`'.$dbName.'`.`all_map`','order_content.product_id = `'.$dbName.'`.`all_map`.`product_id` and `'.$dbName.'`.all_map.service_id = 2')
                    ->andWhere('`'.$dbName.'`.all_map.store_rid ='.$this->store_id)
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
                $wdmodel->waybill_id = $this->id;
                $wdmodel->product_id = $record->product_id;
                $wdmodel->quant = $record->quantity;
                $wdmodel->sum = round($record->price * $record->quantity, 2);
                $wdmodel->defquant = $record->quantity;
                $wdmodel->defsum = round($record->price * $record->quantity, 2);
                $wdmodel->vat = $taxVat;
                $obDicConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
                $obConstModel = iikoPconst::findOne(['const_id' => $obDicConstModel->id, 'org' => $this->org]);
                $wdmodel->org = !is_null($obConstModel) ? $obConstModel->value : $this->org;
                $wdmodel->koef = 1;
                // New check mapping
                $ch = AllMaps::find()
                    ->andWhere('product_id = :prod',['prod' => $record->product_id ])
                    ->andWhere('org_id = :org',['org' => $this->org])
                    ->andWhere('service_id = 2')
                    ->one();

                if ($ch) {
                    if (isset($ch->serviceproduct_id)) {
                        $wdmodel->product_rid = $ch->serviceproduct_id;
                    }

                    if (isset($ch->koef)) {
                        $wdmodel->koef = $ch->koef;
                        $wdmodel->quant = $wdmodel->quant * $ch->koef;
                    }

                    if (isset($ch->unit_rid)) {
                        $wdmodel->munit = $ch->unit_rid;
                    }

                    if (isset($ch->vat)) {
                        $wdmodel->vat = $ch->vat;
                    }
                }



                if (!$wdmodel->save()) {
                    var_dump($wdmodel->getErrors());
                    throw new \Exception();
                }
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            var_dump($ex);
            $transaction->rollback();
        }
    }
}
