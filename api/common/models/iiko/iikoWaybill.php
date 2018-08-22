<?php

namespace api\common\models\iiko;

use common\models\Order;
use common\models\OrderContent;
use Yii;

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
 * @property Order $order;
 */
class iikoWaybill extends \yii\db\ActiveRecord
{
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
            [['doc_date', 'created_at', 'exported_at', 'updated_at', 'num_code'], 'safe'],
            [['org', 'store_id', 'agent_uuid'], 'required'],
            [['agent_uuid'], 'string', 'max' => 36],
            [['text_code', 'num_code'], 'string', 'max' => 128],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
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
        ];
    }

    public function beforeSave($insert)
    {
        //if (parent::beforeSave($insert)) {

//            if ($this->doc_date) {
//                $this->doc_date = Yii::$app->formatter->asDate($this->doc_date, 'yyyy-MM-dd H:i:s');
//            } else {
//                $this->doc_date = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd H:i:s');
//            }

            if (empty($this->text_code)) {
                $this->text_code = 'mixcart';
            }

            if (empty($this->num_code)) {
                $this->num_code = $this->order_id;
            }

            return parent::beforeSave($insert);
        //}
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $records = OrderContent::findAll(['order_id' => $this->order_id]);
            $transaction = \Yii::$app->db_api->beginTransaction();
            try {
                $taxVat = (iikoDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() != null) ? iikoDicconst::findOne(['denom' => 'taxVat'])->getPconstValue() : 1800;
                foreach ($records as $record) {
                    $wdmodel = new iikoWaybillData();
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
                    // Check previous
                    $ch = iikoWaybillData::find()
                        ->andWhere('product_id = :prod', ['prod' => $wdmodel->product_id])
                        ->andWhere('org = :org', ['org' => $wdmodel->org])
                        ->andWhere('product_rid is not null')
                        ->orderBy(['linked_at' => SORT_DESC])
                        ->one();
                    if ($ch) {
                        $wdmodel->product_rid = $ch->product_rid;
                        $wdmodel->munit = $ch->munit;
                        $wdmodel->koef = $ch->koef;
                        $wdmodel->vat = $ch->vat;
                        $wdmodel->quant = $wdmodel->quant * $ch->koef;
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
            $item->addChild('price', round($row->sum / $row->quant, 2));
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
}
